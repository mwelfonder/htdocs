<?php




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

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}
if (!securePage($_SERVER['PHP_SELF'])) {
    die();
}

$currentuser = $user->data()->username;

// Check if the token is valid
if ($token !== 'JfnY6UBwWunh4ffNLemwttNezV8GmYnA') {
    die('Access denied.');
}


include_once $_SERVER['DOCUMENT_ROOT'] . '/view/includes/functions.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/view/includes/cron/cron_mail.php';

date_default_timezone_set('Europe/Berlin');

// Laden Sie die benötigten Benutzerdaten
function getUsersWithSipgateCredentials()
{
    $usersWithPermission = fetchPermissionUsers(5);
    $usersData = [];

    foreach ($usersWithPermission as $user) {
        $userData = fetchUser($user->user_id);

        // Prüfe, ob Token und Token-ID vorhanden sind
        if (!empty($userData->token) && !empty($userData->tokenid)) {
            $usersData[] = [
                'user_id' => $user->user_id,
                'username' => $userData->username,
                'token' => $userData->token,
                'token_id' => $userData->tokenid
            ];
        }
    }
    return $usersData;
}


// Verbindung zur Datenbank herstellen
$conn = dbconnect();

$users = getUsersWithSipgateCredentials($conn);

foreach ($users as $user) {
    if (empty($user['token']) || empty($user['token_id'])) {
        continue; // Überspringe Benutzer ohne gültige Token-Daten
    }

    $personalAccessTokenId = $user['token_id'];
    $personalAccessToken = $user['token'];

    $lastPullDate = getSipgateLastPullDate($conn);
    $from = date('Y-m-d\TH:i:s\Z', strtotime($lastPullDate . '-2 hours'));
    $offset = 0;
    $limit = 1000;
    $totalReceived = 0;
    $dateTimeForLog = date('Y-m-d');
    $dateTimeForLogData = date('Y-m-d_H-i-s');
    $continueFetching = true;

    while ($continueFetching) {
        $credentials = base64_encode($personalAccessTokenId . ':' . $personalAccessToken);
        $url = "https://api.sipgate.com/v2/history?offset=$offset&limit=$limit&archived=false&from=$from";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 504 || curl_errno($ch)) {
            $errorMsg = "{$dateTimeForLogData} Time-out oder cURL-Fehler bei Benutzer: {$user['username']}";
            error_log($errorMsg . "\n", 3, "sipgate_error_{$dateTimeForLog}.log");
            sleep(10);
            continue;
        }

        $responseArray = json_decode($response, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            $errorMsg = 'JSON-Fehler bei Benutzer ' . $user['username'] . ': ' . json_last_error_msg();
            $errorData = 'Fehlerhafte JSON-Daten: ' . $response;
            error_log($errorMsg . "\n" . $errorData . "\n", 3, "sipgate_error_{$dateTimeForLog}.log");
        } else {
            $receivedCount = count($responseArray['items']);
            foreach ($responseArray['items'] as $item) {
                saveSipgateData($conn, $user['username'], $item);
                $totalReceived++;
            }
            if ($receivedCount === 0) {
                $continueFetching = false; // Keine weiteren Daten verfügbar
            } else {
                $offset += $receivedCount; // Aktualisiere den Offset
            }
        }

        sleep(5); // Pausieren zwischen den Abrufen
    }

    echo "{$dateTimeForLogData} Anzahl der erhaltenen Datensätze für Benutzer {$user['username']}: {$totalReceived}\n";
    error_log("{$dateTimeForLogData} Anzahl der erhaltenen Datensätze für Benutzer {$user['username']}: {$totalReceived}\n", 3, "sipgate_data_count_{$dateTimeForLog}.log");
}


// Aktualisiere das 'last pull' Datum
updateLastPullDate($conn);

$conn->close();
echo 'Daten erfolgreich in die Datenbank eingetragen.';


function getSipgateLastPullDate($conn)
{
    $result = $conn->query("SELECT sipgate_lastpull FROM scan4_sipgate_settings");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['sipgate_lastpull'];
    }
    return date('Y-m-d\T00:00:00\Z'); // Standardwert, wenn kein Datum in der Datenbank vorhanden ist
}

function saveSipgateData($conn, $userId, $item)
{
    // Prüfen, ob bereits ein Eintrag mit derselben SID existiert
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM scan4_sipgate_log WHERE sid = ?");
    $checkStmt->bind_param('s', $item['id']);
    $checkStmt->execute();

    // Initialisieren der Variable $count
    $count = 0;
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    // Wenn bereits ein Eintrag existiert, überspringen
    if ($count > 0) {
        echo "Eintrag mit SID " . $item['id'] . " existiert bereits, wird übersprungen.\n";
        return;
    }

    // SQL-Statement zum Einfügen der Daten
    $stmt = $conn->prepare("INSERT INTO scan4_sipgate_log (user, sid, source, target, sourceAlias, targetAlias, type, created, lastModified, direction, incoming, status, connectionIds) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Verarbeitung der Daten für die Speicherung
    $connectionIds = implode(',', $item['connectionIds']);
    $incoming = $item['incoming'] ? 1 : 0;

    // Binden der Parameter und Ausführung des Statements
    $stmt->bind_param('sssssssssisss', $userId, $item['id'], $item['source'], $item['target'], $item['sourceAlias'], $item['targetAlias'], $item['type'], $item['created'], $item['lastModified'], $item['direction'], $incoming, $item['status'], $connectionIds);

    if (!$stmt->execute()) {
        echo "Fehler beim Speichern der Daten: " . $stmt->error . "\n";
    } else {
        echo "Daten für SID " . $item['id'] . " erfolgreich gespeichert.\n";
    }

    // Schließen des Statements
    $stmt->close();
}


function updateLastPullDate($conn)
{
    $currentDateTime = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("UPDATE scan4_sipgate_settings SET sipgate_lastpull = ? WHERE id = 1");
    $stmt->bind_param('s', $currentDateTime);

    if (!$stmt->execute()) {
        echo "Fehler beim Aktualisieren des letzten Pull-Datums: " . $stmt->error;
    }
    $stmt->close();
}
