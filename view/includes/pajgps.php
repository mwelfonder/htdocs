<?php


ini_set('display_errors', 'On');
set_time_limit(6000); // Setzt die maximale Ausführungszeit auf 60 Sekunden

// ##############################################
// #                                            #
// #                                            #
// #                                            #
// #                                            #
// #                                            #
// #                                            #
// #                                            #
// #   !!!!!   THIS IS A PUBLIC PAGE   !!!!!    #
// #                                            #
// #                                            #
// #   Only reachable with the token below      #
// #                                            #
// #                                            #
// #                                            #
// #                                            #
// #                                            #
// #                                            #
// #                                            #
// ##############################################


$currentURL = "http" . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
$urlParts = explode('?to=', $currentURL);
if (count($urlParts) > 1) {
    $token = $urlParts[1];
} else {
    $token = null;
}


// Check if the token is valid
if ($token !== 'JfnY6UBwWunh4ffNLemwttNezV8GmYnA') {
    die('Access denied.');
}


include_once $_SERVER['DOCUMENT_ROOT'] . '/view/includes/functions.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/view/includes/cron/cron_mail.php';

date_default_timezone_set('Europe/Berlin');


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use scan4\Mailer;




function getAPIToken()
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "https://connect.paj-gps.de/api/login?email=kfz%40scan4-gmbh.de&password=Ads23Agfxsfhg");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['accept: application/json']);

    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        die("Curl-Fehler: " . curl_error($curl));
    }
    curl_close($curl);

    $data = json_decode($response, true);
    return $data['success']['token'] ?? null;
}

function getLastPullDate($mysqli)
{
    $result = $mysqli->query("SELECT lastpull FROM scan4_gps_settings");
    if ($row = $result->fetch_assoc()) {
        return $row['lastpull'];
    }
    return null;
}

function updateLastPullDate($mysqli)
{
    // Setzt das aktuelle Datum und die aktuelle Uhrzeit als Unix-Timestamp
    $currentUnixTimestamp = time();

    // Führen Sie das Update in der Datenbank durch
    $query = "UPDATE scan4_gps_settings SET lastpull = $currentUnixTimestamp";
    $mysqli->query($query);
}



function fetchTrackerData($deviceId, $startDate, $endDate, $token)
{
    $curl = curl_init();
    $url = "https://connect.paj-gps.de/api/v1/trackerdata/" . $deviceId . "/date_range?dateStart=" . $startDate . "&dateEnd=" . $endDate;

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'accept: application/json',
        "Authorization: Bearer $token"
    ]);

    $response = curl_exec($curl);
    if ($response === false) {
        die("Curl-Fehler: " . curl_error($curl));
    }
    curl_close($curl);

    return json_decode($response, true); // Gibt die gesamte Antwort zurück
}



function saveDistanceDataToDB($mysqli, $pajId, $gpsLoc, $username, $distance, $date)
{
    $formattedDate = date('Y-m-d', $date); // Formatieren des Datums für die Datenbank

    // SQL-Anweisung mit ON DUPLICATE KEY UPDATE
    $query = "INSERT INTO scan4_gps_distance (paj_id, gps_loc, username, distance, date) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE distance = distance + VALUES(distance)";

    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("sisds", $pajId, $gpsLoc, $username, $distance, $formattedDate);
        $stmt->execute();
        $stmt->close();
    }
}



function saveTrackerDataToDB($mysqli, $trackerData, $gps_loc, $username)
{
    if (!is_array($trackerData) || empty($trackerData)) {
        echo "Keine Daten zum Speichern für Benutzer $username.\n";
        return;
    }

    foreach ($trackerData as $data) {
        if (!isset($data['id'], $data['lat'], $data['lng'], $data['dateunix'])) {
            echo "Einige erforderliche Daten fehlen in diesem Datensatz für Benutzer $username.\n";
            continue; // Überspringt diesen Datensatz
        }

        $pajId = $data['id']; // Stellen Sie sicher, dass dies als String behandelt wird
        $gpsLoc = $gps_loc;
        $lat = $data['lat'];
        $lon = $data['lng'];
        $datetimeUnix = $data['dateunix'];

        if (!is_numeric($datetimeUnix)) {
            echo "Ungültiger Unix-Timestamp für 'dateunix': $datetimeUnix.\n";
            continue; // Überspringt diesen Datensatz
        }

        $datetime = date("Y-m-d H:i:s", $datetimeUnix);
        $imported = date("Y-m-d H:i:s"); // Aktuelles Datum und Uhrzeit

        $query = "INSERT INTO scan4_gps_loc (paj_id, gps_loc, username, lat, lon, datetime, imported) VALUES (?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $mysqli->prepare($query)) {
            $stmt->bind_param("sisddss", $pajId, $gpsLoc, $username, $lat, $lon, $datetime, $imported);
            $stmt->execute();
            $stmt->close();
        }
    }
}



$mysqli = dbconnect(); // Verbindung zur Datenbank herstellen
$token = getAPIToken();
$users = fetchAllUsers();
$lastPullDate = getLastPullDate($mysqli);

foreach ($users as $user) {
    if (isset($user->gps_loc) && !empty($user->gps_loc)) {
        $date = date("Y-m-d", $lastPullDate);
        $endDate = strtotime($date . " 23:59:59");

        $trackerDataResponse = fetchTrackerData($user->gps_loc, $lastPullDate, $endDate, $token);

        echo "<script>console.log('Daten für Benutzer: " . $user->username . "', " . json_encode($trackerDataResponse) . ");</script>";

        // Speichern der Tracker-Daten in der Datenbank
        if (isset($trackerDataResponse['success'])) {
            saveTrackerDataToDB($mysqli, $trackerDataResponse['success'], $user->gps_loc, $user->username);
        }

        // Speichern der Distanzdaten
        if (isset($trackerDataResponse['distance'])) {
            saveDistanceDataToDB($mysqli, $user->gps_loc, $user->gps_loc, $user->username, $trackerDataResponse['distance'], $lastPullDate);
        }

        updateLastPullDate($mysqli);
    } else {
        echo "Benutzer " . $user->username . " hat keine gültige gps_loc.\n";
    }
}




// Aktualisieren des lastpull-Datums in der Datenbank

$mysqli->close();
