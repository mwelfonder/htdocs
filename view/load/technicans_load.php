<?php

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}
if (!securePage($_SERVER['PHP_SELF'])) {
    die();
}

$currentuser = $user->data()->username;


include "../../view/includes/functions.php";

header('Content-Type: application/json');

// Eine einfache Router-Logik basierend auf der 'id' im POST-Request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $func = $_POST['func'] ?? '';

    switch ($func) {
        case 'loadUsers':
            echo json_encode(getUsers());
            break;
        case 'loadUserDetails':
            $userId = $_POST['userId'] ?? '';

            // Angenommen, die Funktion fetchUser existiert und holt die Benutzerdaten
            $userData = fetchUser($userId);

            echo json_encode($userData);
            break;
        case 'loadUserApp':
            $userId = $_POST['userId'] ?? '';
            $userData = fetchUser($userId);
            $username = $userData->username;

            $appointmentData = getAppointmentData($username);
            echo json_encode($appointmentData);
            break;
        case 'getAppointmentDetails':
            $date = $_POST['date'] ?? '';
            $userId = $_POST['userId'] ?? '';
            echo json_encode(getAppointmentDetails($date, $userId));
            break;

        case 'processLogDateRange':
            $userName = $_POST['user'];
            $startDate = $_POST['date_start'];
            $endDate = $_POST['date_end'];
            $period = $_POST['period'];

            // Hier rufen Sie Ihre Funktionen auf, um die Daten zu verarbeiten
            $result = processLogDateRange($userName, $startDate, $endDate, $period);
            echo json_encode($result);
            break;

        case 'fetchGPSLocation':
            $userId = $_POST['userId'] ?? '';
            $userData = fetchUser($userId);
            $car_id = $userData->gps_loc; // Angenommen, dies gibt die GPS-ID zurück

            if (!$car_id) {
                echo json_encode(["error" => "Keine GPS-ID gefunden"]);
                break;
            }

            $token = getAPIToken();
            if (!$token) {
                echo json_encode(["error" => "Fehler beim Abrufen des API-Tokens"]);
                break;
            }

            $gpsData = getGPSDataFromAPI($car_id, $token);
            if (!$gpsData) {
                echo json_encode(["error" => "Keine GPS-Daten gefunden"]);
                break;
            }

            echo json_encode(["success" => $gpsData]);
            break;
        default:
            echo json_encode(["error" => "Unbekannte Anfrage"]);
            break;
    }
}


function getUsers()
{
    $usersWithPermission = fetchPermissionUsers(6);
    $usersData = [];

    foreach ($usersWithPermission as $user) {
        $userData = fetchUser($user->user_id); // Annahme, dass fetchUser die User-Informationen zurückgibt
        $usersData[] = [
            'user_id' => $user->user_id,
            'username' => $userData ? $userData->username : null
        ];
    }

    return $usersData;
}


// Funktion in Ihrem PHP Backend
function getAppointmentData($username)
{
    $db = dbconnect(); // Stellt eine Verbindung zur Datenbank her

    $query = $db->prepare("SELECT DATE(date) as date, appt_status FROM scan4_hbg WHERE hausbegeher = ?");
    $query->bind_param("s", $username);
    $query->execute();

    $result = $query->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $date = $row['date'];
        if (!isset($data[$date])) {
            $data[$date] = ['Erledigt' => 0, 'Offen' => 0, 'Abbruch' => 0];
        }

        if ($row['appt_status'] === 'done') {
            $data[$date]['Erledigt']++;
        } elseif (empty($row['appt_status'])) {
            $data[$date]['Offen']++;
        } else {
            $data[$date]['Abbruch']++;
        }
    }

    return $data;
}

function getAppointmentDetails($date, $userId)
{
    $db = dbconnect(); // Stellt eine Verbindung zur Datenbank her

    // Informationen des Benutzers holen
    $userData = fetchUser($userId);
    $username = $userData->username;

    // Termindaten abfragen
    $query = $db->prepare("SELECT homeid, time, uid, appt_status, appt_comment, appt_file FROM scan4_hbg WHERE hausbegeher = ? AND DATE(date) = ?");
    $query->bind_param("ss", $username, $date);
    $query->execute();
    $result = $query->get_result();
    $appointments = [];
    while ($row = $result->fetch_assoc()) {
        $homeid = $row['homeid'];

        // Details von scan4_homes holen
        $homeQuery = $db->prepare("SELECT street, streetnumber, streetnumberadd FROM scan4_homes WHERE homeid = ?");
        $homeQuery->bind_param("s", $homeid);
        $homeQuery->execute();
        $homeResult = $homeQuery->get_result();
        $homeDetails = $homeResult->fetch_assoc();

        // Details von scan4_hbg_error holen
        $errorQuery = $db->prepare("SELECT error, pic FROM scan4_hbg_error WHERE uid = ?");
        $errorQuery->bind_param("s", $row['uid']);
        $errorQuery->execute();
        $errorResult = $errorQuery->get_result();
        $errors = [];
        while ($errorRow = $errorResult->fetch_assoc()) {
            // Anpassen des pic-Pfades, beginnend bei "/uploads/"
            $picPath = '';
            if ($errorRow['pic']) {
                $picParts = explode("/uploads/", $errorRow['pic']);
                $picPath = count($picParts) > 1 ? '/uploads/' . $picParts[1] : '';
            }

            $errors[] = [
                'error' => $errorRow['error'],
                'pic' => $picPath
            ];
        }

        // Zusammenstellen der Termindetails
        $appointments[] = [
            'homeDetails' => $homeDetails,
            'time' => $row['time'],
            'appt_status' => $row['appt_status'],
            'appt_comment' => $row['appt_comment'],
            'appt_file' => $row['appt_file'],
            'errors' => $errors
        ];
    }

    return $appointments;
}


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


function getGPSDataFromAPI($car_id, $token)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "https://connect.paj-gps.de/api/trackerdata/getalllastpositions");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'accept: application/json',
        "Authorization: Bearer $token",
        'Content-Type: application/json'
    ]);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(["deviceIDs" => [$car_id]]));

    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        die("Curl-Fehler: " . curl_error($curl));
    }
    curl_close($curl);

    $data = json_decode($response, true);
    return $data['success'][0] ?? null; // Rückgabe des ersten Datensatzes, wenn vorhanden
}


function processLogDateRange($userId, $startDate, $endDate)
{
    $db = dbconnect();

    // Überprüfen, ob die Datumsangaben korrekt formatiert sind
    $startDateTime = DateTime::createFromFormat('d.m.Y', $startDate);
    $endDateTime = DateTime::createFromFormat('d.m.Y', $endDate);
    if (!$startDateTime || !$endDateTime) {
        error_log("Fehler bei der Datumsumwandlung: startDate: $startDate, endDate: $endDate");
        return ["error" => "Ungültiges Datum"];
    }

    $startDateFormatted = $startDateTime->format('Y-m-d');
    $endDateFormatted = $endDateTime->format('Y-m-d');

    // Benutzerdetails abrufen
    $userDetails = fetchUser($userId);
    if (!$userDetails) {
        return ["error" => "Benutzer nicht gefunden"];
    }
    $username = $userDetails->username;

    // GPS-Daten abfragen
    $gpsDataQuery = $db->prepare("SELECT date, SUM(distance) as total_distance FROM scan4_gps_distance WHERE username = ? AND date BETWEEN ? AND ? GROUP BY date ORDER BY date ASC");
    $gpsDataQuery->bind_param("sss", $username, $startDateFormatted, $endDateFormatted);
    $gpsDataQuery->execute();
    $gpsDataResult = $gpsDataQuery->get_result();

    $gpsDataDetails = [];
    while ($gpsDataRow = $gpsDataResult->fetch_assoc()) {
        $date = DateTime::createFromFormat('Y-m-d', $gpsDataRow['date'])->format('d.m.Y'); // Umwandeln des Datumsformats für die Ausgabe
        $gpsDataDetails[] = [
            'date' => $date,
            'total_distance' => $gpsDataRow['total_distance']
        ];
    }

    // HBG-Daten abfragen
    $sql = "SELECT uid, err_check, appt_status, hbg_rating, DATE_FORMAT(date, '%Y-%m-%d') as formatted_date FROM scan4_hbg WHERE hausbegeher = ? AND date BETWEEN ? AND ? ORDER BY date ASC";
    $query = $db->prepare($sql);
    $query->bind_param("sss", $username, $startDateFormatted, $endDateFormatted);
    $query->execute();
    $result = $query->get_result();

    $dailyStats = [];
    while ($row = $result->fetch_assoc()) {
        $date = $row['formatted_date']; // Datum des aktuellen Datensatzes

        // Initialisiere den Tageseintrag, falls noch nicht geschehen
        if (!isset($dailyStats[$date])) {
            $dailyStats[$date] = [
                'hbg_done' => 0,
                'hbg_missing' => 0,
                'hbg_abbruch' => 0,
                'total_rating' => 0,
                'rating_count' => 0,
            ];
        }

        // Bewertungen und Status zählen
        if ($row['err_check'] == 1 && !empty($row['hbg_rating'])) {
            $dailyStats[$date]['total_rating'] += $row['hbg_rating'];
            $dailyStats[$date]['rating_count']++;
        }
        switch ($row['appt_status']) {
            case 'done':
                $dailyStats[$date]['hbg_done']++;
                break;
            case null:
            case '':
                $dailyStats[$date]['hbg_missing']++;
                break;
            default:
                $dailyStats[$date]['hbg_abbruch']++;
                break;
        }
    }

    // Tägliche Statistiken vorbereiten
    $dailyResults = [];
    foreach ($dailyStats as $date => $stats) {
        $averageRating = $stats['rating_count'] > 0 ? $stats['total_rating'] / $stats['rating_count'] : 0;
        $dailyResults[$date] = [
            'date' => $date,
            'average_hbg_rating' => $averageRating,
            'hbg_done' => $stats['hbg_done'],
            'hbg_missing' => $stats['hbg_missing'],
            'hbg_abbruch' => $stats['hbg_abbruch'],
        ];
    }
    // Zusammenfassen der Ergebnisse
    return [
        'daily_stats' => $dailyResults,
        'gps_data' => $gpsDataDetails,
        // Füge hier die anderen aggregierten Werte ein, die du zurückgeben möchtest
    ];
}
