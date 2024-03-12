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

function getUsersWithPermission5()
{
    $usersWithPermission = fetchPermissionUsers(5);
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









if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action == 'fetch_users') {
        $users = getUsersWithPermission5();
        //error_log(print_r($users, true)); // Zum Überprüfen der Daten
        echo json_encode($users);
    } elseif ($action == 'fetch_user_log') {
        $username = $_POST['username'];
        $date = $_POST['date'];
        $userActionsData = fetchUserLog($username, $date);
        echo json_encode($userActionsData);
    } elseif ($action == 'get_user_data_day') {

        $username = $_POST['user'];
        $startDate = $_POST['date_start'];
        $endDate = $_POST['date_end'];
        $action = $_POST['action'];

        $data = getUserData($username, $startDate, $endDate, $action);
        echo json_encode($data);
    } elseif ($action == 'get_user_data_week') {

        $username = $_POST['user'];
        $startDate = $_POST['date_start'];
        $endDate = $_POST['date_end'];
        $action = $_POST['action'];

        $data = getUserData($username, $startDate, $endDate, $action);
        echo json_encode($data);
    } elseif ($action == 'get_user_data_month') {

        $username = $_POST['user'];
        $startDate = $_POST['date_start'];
        $endDate = $_POST['date_end'];
        $action = $_POST['action'];

        $data = getUserData($username, $startDate, $endDate, $action);
        echo json_encode($data);
    } elseif ($action == 'fetch_livephone_data') {
        $livePhoneData = fetch_livephone_data();
        echo json_encode($livePhoneData);
    } elseif ($action == 'fetch_participant_info') {
        $callId = $_POST['callId'];
        $participantInfo = getParticipantInfoFromWebhook($callId);
        echo json_encode($participantInfo);
    } elseif ($action == 'fetch_call_data') {
        $direction = $_POST['direction']; // 'in' oder 'out'
        $callData = fetchCallStats($direction);
        echo json_encode($callData);
    } elseif ($action == 'fetch_phone_data') {
        $username = $_POST['username'];
        $date = $_POST['date'];

        // Führe hier die Logik aus, um die Anrufdaten zu holen
        // Beispiel:
        $callData = fetchCallData($username, $date);
        echo json_encode($callData);
    } elseif ($action == 'fetch_user_interactions') {
        $username = $_POST['username'];
        $date = $_POST['date'];

        $startOfDay = $date . ' 00:00:00';
        $endOfDay = $date . ' 23:59:59';

        $conn = dbconnect();
        $query = "
            SELECT timestamp
            FROM user_interactions
            WHERE user = '$username' AND timestamp BETWEEN '$startOfDay' AND '$endOfDay'
            ORDER BY timestamp ASC
        ";

        $result = $conn->query($query);
        $interactions = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $interactions[] = $row;
            }
        }

        $conn->close();

        $hoursData = calculateWorkTime($interactions);
        echo json_encode($hoursData);
    }
}

function calculateWorkTime($interactions)
{
    $workTimes = [];
    $lastTimestamp = null;

    foreach ($interactions as $interaction) {
        $timestamp = strtotime($interaction['timestamp']);
        $hour = date('H', $timestamp);

        if ($lastTimestamp !== null && ($timestamp - $lastTimestamp) <= 60) {
            if (!isset($workTimes[$hour])) {
                $workTimes[$hour] = 0;
            }
            $workTimes[$hour] += ($timestamp - $lastTimestamp) / 60;
        }

        $lastTimestamp = $timestamp;
    }

    $formattedWorkTimes = [];
    foreach ($workTimes as $hour => $minutes) {
        $formattedWorkTimes[] = ['hour' => $hour, 'minutes' => round($minutes)];
    }

    return $formattedWorkTimes;
}




function fetchUserLog($username, $date)
{
    $conn = dbconnect(); // Stellen Sie sicher, dass Sie Ihre Datenbankverbindungsmethode verwenden
    $startOfDay = $date . ' 00:00:00';
    $endOfDay = $date . ' 23:59:59';

    // SQL-Abfrage, um Aktionen für den spezifizierten Benutzer und das Datum zu erhalten
    $query = "
        SELECT datetime, action1
        FROM scan4_userlog
        WHERE user = '$username' AND datetime BETWEEN '$startOfDay' AND '$endOfDay'
        ORDER BY datetime ASC
    ";

    // Logging der SQL-Abfrage
    //error_log("SQL Query: " . $query);

    $result = $conn->query($query);
    if (!$result) {
        // Loggen eines Fehlers, wenn die Abfrage fehlschlägt
        error_log("Database Query Error: " . $conn->error);
        return []; // Rückgabe eines leeren Arrays bei einem Fehler
    }

    $userActions = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $hour = date('H', strtotime($row['datetime']));
            $action = $row['action1'];

            if (!isset($userActions[$hour])) {
                $userActions[$hour] = [
                    'click phonenumber' => 0,
                    'create customer note' => 0,
                    'created an hbg' => 0,
                    'load homeid' => 0,
                    'moved an hbg' => 0,
                    'storno an appointment' => 0
                ];
            }

            switch ($action) {
                case 'click phonenumber':
                case 'create customer note':
                case 'created an hbg':
                case 'load homeid':
                case 'moved an hbg':
                case 'storno an appointment':
                    $userActions[$hour][$action]++;
                    break;
            }
        }
    }

    $conn->close();
    return $userActions;
}

function fetchCallData($username, $date)
{
    $conn = dbconnect(); // Verwende deine Datenbankverbindungsmethode
    $startOfDay = $date . 'T00:00:00Z';
    $endOfDay = $date . 'T23:59:59Z';



    // SQL-Abfrage, um Anrufdaten für den spezifizierten Benutzer und das Datum zu erhalten
    $query = "
        SELECT HOUR(CONVERT_TZ(created, '+00:00', @@session.time_zone)) as hour, 
               SUM(CASE WHEN incoming = 1 AND targetAlias = '' THEN 1 ELSE 0 END) as incoming,
               SUM(CASE WHEN incoming = 0 THEN 1 ELSE 0 END) as outgoing,
               SUM(CASE WHEN incoming = 1 AND status = 'NOPICKUP' AND targetAlias = '' THEN 1 ELSE 0 END) as missed_incoming,
               SUM(CASE WHEN incoming = 0 AND status = 'NOPICKUP' THEN 1 ELSE 0 END) as missed_outgoing,
               SUM(CASE WHEN incoming = 1 AND targetAlias = 'Telefonist' THEN 1 ELSE 0 END) as hotline_incoming,
               SUM(CASE WHEN incoming = 1 AND status = 'NOPICKUP' AND targetAlias = 'Telefonist' THEN 1 ELSE 0 END) as missed_hotline
        FROM scan4_sipgate_log
        WHERE user = '$username' AND created BETWEEN '$startOfDay' AND '$endOfDay'
        GROUP BY HOUR(CONVERT_TZ(created, '+00:00', @@session.time_zone))
        ORDER BY hour ASC
    ";

    $result = $conn->query($query);
    if (!$result) {
        error_log("Database Query Error: " . $conn->error);
        return []; // Rückgabe eines leeren Arrays bei einem Fehler
    }

    $callData = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $callData[] = [
                'hour' => str_pad($row['hour'], 2, '0', STR_PAD_LEFT),
                'incoming' => (int)$row['incoming'],
                'outgoing' => (int)$row['outgoing'],
                'missed_incoming' => (int)$row['missed_incoming'],
                'missed_outgoing' => (int)$row['missed_outgoing'],
                'hotline_incoming' => (int)$row['hotline_incoming'],
                'missed_hotline' => (int)$row['missed_hotline']
            ];
        }
    }

    $conn->close();
    return $callData;
}

function fetch_livephone_data()
{
    $personalAccessTokenId = "token-W5RCI6";
    $personalAccessToken = "102ea531-0582-4ce7-bf69-5a616730354d";
    $credentials = base64_encode($personalAccessTokenId . ':' . $personalAccessToken);
    // Initialisiere cURL Session
    $curl = curl_init();

    // Setze cURL Optionen
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.sipgate.com/v2/calls",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => ['Authorization: Basic ' . $credentials],
    ]);

    // Führe die Anfrage aus und speichere die Antwort
    $response = curl_exec($curl);
    $err = curl_error($curl);

    // Schließe die cURL Session
    curl_close($curl);

    // Überprüfe auf Fehler
    if ($err) {
        echo "cURL Error #:" . $err;
        return;
    } else {
        // Verarbeite die Antwort
        return process_livephone_data(json_decode($response, true));
    }
}

function process_livephone_data($data)
{
    $conn = dbconnect(); // Stellen Sie sicher, dass Sie Ihre Verbindungsmethode verwenden
    $activeCalls = 0;
    $hotlineCustomers = 0;
    $callDetails = [];

    if (isset($data['data'])) {
        foreach ($data['data'] as $call) {
            $hasEmptyParticipant = false;
            $participants = [];
            $callId = $call['callId'];

            foreach ($call['participants'] as $participant) {
                $participants[] = [
                    'participantId' => $participant['participantId'],
                    'phoneNumber' => $participant['phoneNumber'] ?? 'Warteschlange'
                ];

                if ($participant['participantId'] == "2" && empty($participant['phoneNumber'])) {
                    $hasEmptyParticipant = true;
                }
            }

            // Abfrage der `answer`-Zeit für die aktuelle callId
            $answerTime = getAnswerTimeForCallId($conn, $callId);

            if ($hasEmptyParticipant) {
                $hotlineCustomers++;
            } else {
                $activeCalls++;
            }

            $callDetails[] = [
                'callId' => $callId,
                'answerTime' => $answerTime,
                'participants' => $participants
            ];
        }
    }

    $conn->close();

    return [
        'activeCalls' => $activeCalls,
        'hotlineCustomers' => $hotlineCustomers,
        'calls' => $callDetails
    ];
}
function getAnswerTimeForCallId($conn, $callId)
{
    // Zuerst versuchen, das 'answer'-Ereignis zu finden
    $query = "SELECT datetime FROM scan4_sipgate_webhook WHERE callId = '$callId' AND event = 'answer' LIMIT 1";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['datetime']; // Gibt das Datum des 'answer'-Ereignisses zurück, falls vorhanden
    }

    // Falls kein 'answer'-Ereignis gefunden wurde, suche nach 'newCall'-Ereignis
    $query = "SELECT datetime FROM scan4_sipgate_webhook WHERE callId = '$callId' AND event = 'newCall' LIMIT 1";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['datetime']; // Gibt das Datum des 'newCall'-Ereignisses zurück, falls vorhanden
    }

    return null; // Rückgabe von null, wenn keine passenden Ereignisse gefunden wurden
}



// Beispiel für die Verwendung der Funktion
if ($action == 'fetch_call_stats') {
    $direction = $_POST['direction']; // 'in' oder 'out'
    $callStats = fetchCallStats($direction);
    echo json_encode($callStats);
}

function fetchCallStats($direction)
{
    $conn = dbconnect();
    $today = date("Y-m-d");
    $query = "
        SELECT callId, GROUP_CONCAT(event ORDER BY datetime) as events
        FROM scan4_sipgate_webhook
        WHERE direction = '$direction' AND DATE(datetime) = '$today'
        GROUP BY callId
    ";

    $result = $conn->query($query);
    $missedCalls = 0;
    $answeredCalls = 0;

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            if (strpos($row['events'], 'answer') === false) {
                $missedCalls++;
            } else {
                $answeredCalls++;
            }
        }
    }

    return [
        'missed' => $missedCalls,
        'answered' => $answeredCalls
    ];
}


if ($action == 'fetch_call_cth') {
    $direction = $_POST['direction']; // 'in' oder 'out'
    $averageDuration = calculateAverageCallDuration($direction);
    echo json_encode(['averageDuration' => $averageDuration]);
}

function calculateAverageCallDuration($direction)
{
    $conn = dbconnect(); // Ersetzen Sie dies durch Ihre Datenbankverbindungslogik
    $today = date("Y-m-d");
    $query = "
        SELECT 
            callId, 
            MIN(CASE WHEN event = 'answer' THEN datetime END) as answerTime,
            MIN(CASE WHEN event = 'hangup' THEN datetime END) as hangupTime
        FROM scan4_sipgate_webhook
        WHERE direction = '$direction' AND DATE(datetime) = '$today'
        GROUP BY callId
        HAVING answerTime IS NOT NULL AND hangupTime IS NOT NULL
    ";

    $result = $conn->query($query);
    $totalDuration = 0;
    $count = 0;

    while ($row = $result->fetch_assoc()) {
        $answerTime = strtotime($row['answerTime']);
        $hangupTime = strtotime($row['hangupTime']);
        $duration = $hangupTime - $answerTime;

        $totalDuration += $duration;
        $count++;
    }

    $conn->close();

    return $count > 0 ? ($totalDuration / $count) / 60 : 0; // Durchschnitt in Minuten
}











if ($action == 'fetch_last_call') {
    $direction = $_POST['direction']; // 'in' oder 'out'
    $lastCallTime = fetchLastCall($direction);
    echo json_encode(['lastCallTime' => $lastCallTime]);
}

function fetchLastCall($direction)
{
    $conn = dbconnect(); // Ersetzen Sie dies durch Ihre Datenbankverbindungslogik
    $today = date("Y-m-d");

    // SQL-Query, um den letzten Anruf zu finden
    $query = "
        SELECT MAX(datetime) as lastCallTime
        FROM scan4_sipgate_webhook
        WHERE direction = '$direction' AND DATE(datetime) = '$today'
          AND event = 'newCall'
    ";

    $result = $conn->query($query);
    $lastCallTimeFormatted = null;

    if ($row = $result->fetch_assoc()) {
        $lastCallTime = $row['lastCallTime'];

        // Konvertiere den String in ein DateTime-Objekt und formatiere es
        $dateTime = new DateTime($lastCallTime);
        $lastCallTimeFormatted = $dateTime->format('H:i'); // Format: Stunden:Minuten
    }

    $conn->close();

    // Gib die formatierte Zeit des letzten Anrufs zurück
    return $lastCallTimeFormatted;
}







function calculateAverageTimeToPickup($direction)
{
    $conn = dbconnect(); // Ersetzen Sie dies durch Ihre Datenbankverbindungslogik
    $today = date("Y-m-d");
    $query = "
        SELECT 
            callId, 
            MIN(CASE WHEN event = 'newCall' THEN datetime END) as callTime,
            MIN(CASE WHEN event = 'answer' THEN datetime END) as answerTime
        FROM scan4_sipgate_webhook
        WHERE direction = '$direction' AND DATE(datetime) = '$today'
        GROUP BY callId
        HAVING callTime IS NOT NULL AND answerTime IS NOT NULL
    ";

    $result = $conn->query($query);
    $totalTime = 0;
    $count = 0;

    while ($row = $result->fetch_assoc()) {
        $callTime = strtotime($row['callTime']);
        $answerTime = strtotime($row['answerTime']);
        $duration = $answerTime - $callTime;

        $totalTime += $duration;
        $count++;
    }

    $conn->close();

    return $count > 0 ? ($totalTime / $count) / 60 : 0; // Durchschnitt in Minuten
}

// Einbindung in Ihre bestehenden Backend-Routinen ...
if ($action == 'fetch_call_ctp') {
    $direction = $_POST['direction'];
    $averageTimeToPickup = calculateAverageTimeToPickup($direction);
    echo json_encode(['averageTimeToPickup' => $averageTimeToPickup]);
}


function stopCall($callId)
{
    $personalAccessTokenId = "token-W5RCI6";
    $personalAccessToken = "102ea531-0582-4ce7-bf69-5a616730354d";
    $credentials = base64_encode($personalAccessTokenId . ':' . $personalAccessToken);

    // Initialisiere cURL Session
    $curl = curl_init();

    // Setze cURL Optionen
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.sipgate.com/v2/calls/" . $callId,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "DELETE",
        CURLOPT_HTTPHEADER => ['Authorization: Basic ' . $credentials],
    ]);

    // Führe die Anfrage aus und speichere die Antwort
    $response = curl_exec($curl);
    $err = curl_error($curl);

    // Schließe die cURL Session
    curl_close($curl);

    // Überprüfe auf Fehler
    if ($err) {
        return "cURL Error #:" . $err;
    } else {
        // Verarbeite die Antwort
        return $response;
    }
}

// Diese Zeile fügt die Funktion zum Backend-Skript hinzu
if ($action == 'stop_call' && isset($_POST['callId'])) {
    echo stopCall($_POST['callId']);
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'fetch_chart_data') {
    $username = $_POST['username'];
    $dateRange = explode(' to ', $_POST['dateRange']); // Annehmen, dass das Datum im Format 'YYYY-MM-DD bis YYYY-MM-DD' kommt
    $startDate = $dateRange[0];
    $endDate = $dateRange[1];

    // Funktionen zum Abrufen der Daten
    $userActions = fetchUserActionsPerDay($username, $startDate, $endDate);
    $callData = fetchCallDataPerDay($username, $startDate, $endDate);

    // Senden der Daten als JSON zurück
    echo json_encode([
        'userActions' => $userActions,
        'callData' => $callData
    ]);
}

function fetchUserActionsPerDay($username, $startDate, $endDate)
{
    $conn = dbconnect();
    $query = "
        SELECT DATE(datetime) as date, action1, COUNT(*) as count
        FROM scan4_userlog
        WHERE user = '$username' AND DATE(datetime) BETWEEN '$startDate' AND '$endDate'
        GROUP BY DATE(datetime), action1
        ORDER BY DATE(datetime) ASC
    ";
    //error_log("SQL Query for User Actions: " . $query);

    $result = $conn->query($query);
    $userActionsPerDay = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $date = $row['date'];
            $action = $row['action1'];
            $count = $row['count'];

            if (!isset($userActionsPerDay[$date])) {
                $userActionsPerDay[$date] = [
                    'click phonenumber' => 0,
                    'create customer note' => 0,
                    'created an hbg' => 0,
                    'load homeid' => 0,
                    'moved an hbg' => 0,
                    'storno an appointment' => 0
                ];
            }

            if (array_key_exists($action, $userActionsPerDay[$date])) {
                $userActionsPerDay[$date][$action] += $count;
            }
        }
    }

    $conn->close();
    return $userActionsPerDay;
}

function fetchCallDataPerDay($username, $startDate, $endDate)
{

    $startOfDay = $startDate . 'T00:00:00Z';
    $endOfDay = $endDate . 'T23:59:59Z';

    $conn = dbconnect();
    $query = "
        SELECT HOUR(CONVERT_TZ(created, '+00:00', @@session.time_zone)) as hour, 
               SUM(CASE WHEN incoming = 1 AND targetAlias = '' THEN 1 ELSE 0 END) as incoming,
               SUM(CASE WHEN incoming = 0 THEN 1 ELSE 0 END) as outgoing,
               SUM(CASE WHEN incoming = 1 AND status = 'NOPICKUP' AND targetAlias = '' THEN 1 ELSE 0 END) as missed_incoming,
               SUM(CASE WHEN incoming = 0 AND status = 'NOPICKUP' THEN 1 ELSE 0 END) as missed_outgoing,
               SUM(CASE WHEN incoming = 1 AND targetAlias = 'Telefonist' THEN 1 ELSE 0 END) as hotline_incoming,
               SUM(CASE WHEN incoming = 1 AND status = 'NOPICKUP' AND targetAlias = 'Telefonist' THEN 1 ELSE 0 END) as missed_hotline
        FROM scan4_sipgate_log
        WHERE user = '$username' AND created BETWEEN '$startOfDay' AND '$endOfDay'
        GROUP BY HOUR(CONVERT_TZ(created, '+00:00', @@session.time_zone))
        ORDER BY hour ASC
    ";
    //error_log("SQL Query for Call Actions: " . $query);

    $result = $conn->query($query);
    $callDataPerDay = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $date = $row['date'];
            $callDataPerDay[$date] = [
                'hour' => str_pad($row['hour'], 2, '0', STR_PAD_LEFT),
                'incoming' => (int)$row['incoming'],
                'outgoing' => (int)$row['outgoing'],
                'missed_incoming' => (int)$row['missed_incoming'],
                'missed_outgoing' => (int)$row['missed_outgoing'],
                'hotline_incoming' => (int)$row['hotline_incoming'],
                'missed_hotline' => (int)$row['missed_hotline']
            ];
        }
    }

    $conn->close();
    return $callDataPerDay;
}

function getParticipantInfoFromWebhook($callId)
{
    $conn = dbconnect();
    $query = "SELECT * FROM scan4_sipgate_webhook WHERE callId = '$callId' AND event = 'newCall' LIMIT 1";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return [
            'phoneNumber' => $row['from_number'],
            'status' => $row['event'] === 'newCall' ? 'Hotline wartend' : 'Unbekannt'
        ];
    } else {
        return ['status' => 'Unbekannt'];
    }
}









function getUserData($username, $startDate, $endDate, $action)
{
    if ($action == 'get_user_data_day') {
        // Aggregiert pro Stunde
        return [
            'sipgateData' => getSipgateDataDay($username, $startDate, $endDate),
            'worktimeData' => getWorktimeDataDay($username, $startDate, $endDate),
            'appData' => getAppDataPerHour($username, $startDate, $endDate)
        ];
    } else if ($action == 'get_user_data_week' || $action == 'get_user_data_month') {
        // Aggregiert pro Tag
        return [
            'sipgateData' => getSipgateDataWeekMonth($username, $startDate, $endDate),
            'worktimeData' => getWorktimeDataWeekMonth($username, $startDate, $endDate),
            'appData' => getAppDataPerDay($username, $startDate, $endDate)
        ];
    }
}









function calculateWorkTimePerDay($interactions)
{
    $workTimesPerDay = [];
    $lastTimestampPerDay = [];

    foreach ($interactions as $interaction) {
        $timestamp = strtotime($interaction);
        $day = date('Y-m-d', $timestamp);

        // Initialisieren Sie den ersten Timestamp des Tages
        if (!isset($lastTimestampPerDay[$day])) {
            $lastTimestampPerDay[$day] = $timestamp;
            if (!isset($workTimesPerDay[$day])) {
                $workTimesPerDay[$day] = 0;
            }
            continue;
        }

        // Berechnen Sie die Zeitdifferenz in Sekunden
        $timeDifference = $timestamp - $lastTimestampPerDay[$day];

        // Wenn die Zeitdifferenz 60 Sekunden oder weniger beträgt, fügen Sie sie zur Arbeitszeit des Tages hinzu
        if ($timeDifference <= 60) {
            $workTimesPerDay[$day] += $timeDifference / 60; // Konvertieren Sie Sekunden in Minuten
        }

        // Aktualisieren Sie den letzten Timestamp für den Tag
        $lastTimestampPerDay[$day] = $timestamp;
    }

    // Formatieren Sie die Arbeitszeiten für jeden Tag
    $formattedWorkTimesPerDay = [];
    foreach ($workTimesPerDay as $day => $minutes) {
        $formattedWorkTimesPerDay[] = ['date' => $day, 'minutes' => round($minutes, 2)];
    }

    return $formattedWorkTimesPerDay;
}



function calculateWorkTimePerHour($interactions)
{
    $workTimesPerHour = [];
    $lastTimestampPerHour = [];

    foreach ($interactions as $interaction) {
        $timestamp = strtotime($interaction);
        $hourKey = date('Y-m-d H:00', $timestamp); // Stunde in 'YYYY-MM-DD HH:00' Format

        if (!isset($lastTimestampPerHour[$hourKey])) {
            $lastTimestampPerHour[$hourKey] = $timestamp;
            continue; // Überspringen, da es der erste Timestamp der Stunde ist
        }

        // Prüfen, ob der aktuelle Zeitstempel innerhalb von 60 Sekunden nach dem letzten Zeitstempel liegt
        if (($timestamp - $lastTimestampPerHour[$hourKey]) <= 60) {
            if (!isset($workTimesPerHour[$hourKey])) {
                $workTimesPerHour[$hourKey] = 0;
            }
            $workTimesPerHour[$hourKey] += ($timestamp - $lastTimestampPerHour[$hourKey]) / 60; // Minuten
        }

        $lastTimestampPerHour[$hourKey] = $timestamp; // Aktualisieren Sie den letzten Timestamp für diese Stunde
    }

    $formattedWorkTimesPerHour = [];
    foreach ($workTimesPerHour as $hourKey => $minutes) {
        $hour = substr($hourKey, 11, 2); // Extrahieren der Stunde aus dem Schlüssel 'YYYY-MM-DD HH:00'
        $formattedWorkTimesPerHour[] = ['hour' => $hour, 'minutes' => round($minutes)];
    }

    return $formattedWorkTimesPerHour;
}







function getSipgateDataDay($username, $startDate, $endDate)
{
    // Umwandeln von deutschen Datumsformaten in MySQL-kompatible Formate
    $startDateObj = DateTime::createFromFormat('d.m.Y', $startDate);
    $endDateObj = DateTime::createFromFormat('d.m.Y', $endDate);

    if ($startDateObj === false || $endDateObj === false) {
        // Fehlerbehandlung, falls das Datum nicht korrekt umgewandelt werden kann
        error_log("Fehler bei der Datumsformatierung: Startdatum - $startDate, Enddatum - $endDate");
        return []; // Leeres Array zurückgeben oder andere Fehlerbehandlung durchführen
    }

    $startOfDay = $startDateObj->format('Y-m-d') . 'T00:00:00Z';
    $endOfDay = $endDateObj->format('Y-m-d') . 'T23:59:59Z';
    $db = dbconnect(); // Verwende deine Datenbankverbindungsmethode

    $query = "SELECT HOUR(CONVERT_TZ(created, '+00:00', @@session.time_zone)) as hour, 
       SUM(CASE WHEN incoming = 1 AND targetAlias = '' THEN 1 ELSE 0 END) as incoming,
       SUM(CASE WHEN incoming = 0 THEN 1 ELSE 0 END) as outgoing,
       SUM(CASE WHEN incoming = 1 AND status = 'NOPICKUP' AND targetAlias = '' THEN 1 ELSE 0 END) as missed_incoming,
       SUM(CASE WHEN incoming = 0 AND status = 'NOPICKUP' THEN 1 ELSE 0 END) as missed_outgoing,
       SUM(CASE WHEN incoming = 1 AND targetAlias = 'Telefonist' THEN 1 ELSE 0 END) as hotline_incoming,
       SUM(CASE WHEN incoming = 1 AND status = 'NOPICKUP' AND targetAlias = 'Telefonist' THEN 1 ELSE 0 END) as missed_hotline
FROM scan4_sipgate_log
WHERE user = ? AND created BETWEEN ? AND ?
GROUP BY HOUR(CONVERT_TZ(created, '+00:00', @@session.time_zone))
ORDER BY hour ASC
";
    $stmt = $db->prepare($query);
    error_log("SQL Query: " . $query);
    error_log("Parameters: Username - $username, Start - $startOfDay, End - $endOfDay");
    $stmt->bind_param("sss", $username, $startOfDay, $endOfDay);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'hour' => str_pad($row['hour'], 2, '0', STR_PAD_LEFT),
            'incoming' => (int)$row['incoming'],
            'outgoing' => (int)$row['outgoing'],
            'missed_incoming' => (int)$row['missed_incoming'],
            'missed_outgoing' => (int)$row['missed_outgoing'],
            'hotline_incoming' => (int)$row['hotline_incoming'],
            'missed_hotline' => (int)$row['missed_hotline']
        ];
    }
    return $data;
}


function getSipgateDataWeekMonth($username, $startDate, $endDate)
{
    // Umwandeln von deutschen Datumsformaten in MySQL-kompatible Formate
    $startDateObj = DateTime::createFromFormat('d.m.Y', $startDate);
    $endDateObj = DateTime::createFromFormat('d.m.Y', $endDate);

    if ($startDateObj === false || $endDateObj === false) {
        // Fehlerbehandlung, falls das Datum nicht korrekt umgewandelt werden kann
        error_log("Fehler bei der Datumsformatierung: Startdatum - $startDate, Enddatum - $endDate");
        return []; // Leeres Array zurückgeben oder andere Fehlerbehandlung durchführen
    }

    $startOfDay = $startDateObj->format('Y-m-d') . 'T00:00:00Z';
    $endOfDay = $endDateObj->format('Y-m-d') . 'T23:59:59Z';
    $db = dbconnect(); // Verwende deine Datenbankverbindungsmethode

    $query = "SELECT DATE(created) as date, 
       SUM(CASE WHEN incoming = 1 AND targetAlias = '' THEN 1 ELSE 0 END) as incoming,
       SUM(CASE WHEN incoming = 0 THEN 1 ELSE 0 END) as outgoing,
       SUM(CASE WHEN incoming = 1 AND status = 'NOPICKUP' AND targetAlias = '' THEN 1 ELSE 0 END) as missed_incoming,
       SUM(CASE WHEN incoming = 0 AND status = 'NOPICKUP' THEN 1 ELSE 0 END) as missed_outgoing,
       SUM(CASE WHEN incoming = 1 AND targetAlias = 'Telefonist' THEN 1 ELSE 0 END) as hotline_incoming,
       SUM(CASE WHEN incoming = 1 AND status = 'NOPICKUP' AND targetAlias = 'Telefonist' THEN 1 ELSE 0 END) as missed_hotline
       FROM scan4_sipgate_log
        WHERE user = ? AND created BETWEEN ? AND ?
        GROUP BY DATE(created)
    ";

    $stmt = $db->prepare($query);
    error_log("SQL Query: " . $query);
    error_log("Parameters: Username - $username, Start - $startOfDay, End - $endOfDay");
    $stmt->bind_param("sss", $username, $startOfDay, $endOfDay);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'date' => $row['date'],
            'incoming' => (int)$row['incoming'],
            'outgoing' => (int)$row['outgoing'],
            'missed_incoming' => (int)$row['missed_incoming'],
            'missed_outgoing' => (int)$row['missed_outgoing'],
            'hotline_incoming' => (int)$row['hotline_incoming'],
            'missed_hotline' => (int)$row['missed_hotline']
        ];
    }
    return $data;
}


function getWorktimeDataDay($username, $startDate, $endDate)
{
    $startDateObj = DateTime::createFromFormat('d.m.Y', $startDate);
    $endDateObj = DateTime::createFromFormat('d.m.Y', $endDate);

    if ($startDateObj === false || $endDateObj === false) {
        error_log("Fehler bei der Datumsformatierung: Startdatum - $startDate, Enddatum - $endDate");
        return [];
    }

    $startOfDay = $startDateObj->format('Y-m-d') . ' 00:00:00';
    $endOfDay = $endDateObj->format('Y-m-d') . ' 23:59:59';

    $db = dbconnect();
    $query = "SELECT timestamp
              FROM user_interactions
              WHERE user = ? AND timestamp BETWEEN ? AND ?
              ORDER BY timestamp ASC";

    $stmt = $db->prepare($query);
    error_log("SQL Query: " . $query);
    error_log("Parameters: Username - $username, Start - $startOfDay, End - $endOfDay");
    $stmt->bind_param("sss", $username, $startOfDay, $endOfDay);
    $stmt->execute();
    $result = $stmt->get_result();

    $interactions = [];
    while ($row = $result->fetch_assoc()) {
        error_log("Row: " . print_r($row, true));
        $interactions[] = $row['timestamp'];
    }

    return calculateWorkTimePerHour($interactions); // Stündliche Aggregation

}

function getWorktimeDataWeekMonth($username, $startDate, $endDate)
{
    $startDateObj = DateTime::createFromFormat('d.m.Y', $startDate);
    $endDateObj = DateTime::createFromFormat('d.m.Y', $endDate);

    if ($startDateObj === false || $endDateObj === false) {
        error_log("Fehler bei der Datumsformatierung: Startdatum - $startDate, Enddatum - $endDate");
        return [];
    }

    $startOfDay = $startDateObj->format('Y-m-d') . ' 00:00:00';
    $endOfDay = $endDateObj->format('Y-m-d') . ' 23:59:59';

    $db = dbconnect();
    $query = "SELECT timestamp
              FROM user_interactions
              WHERE user = ? AND timestamp BETWEEN ? AND ?
              ORDER BY timestamp ASC";

    $stmt = $db->prepare($query);
    error_log("SQL Query: " . $query);
    error_log("Parameters: Username - $username, Start - $startOfDay, End - $endOfDay");
    $stmt->bind_param("sss", $username, $startOfDay, $endOfDay);
    $stmt->execute();
    $result = $stmt->get_result();

    $interactions = [];
    while ($row = $result->fetch_assoc()) {
        error_log("Row: " . print_r($row, true));
        $interactions[] = $row['timestamp'];
    }

    return calculateWorkTimePerDay($interactions); // Tägliche Aggregation
}



function getAppDataPerHour($username, $startDate, $endDate)
{
    $startDateObj = DateTime::createFromFormat('d.m.Y', $startDate);
    $endDateObj = DateTime::createFromFormat('d.m.Y', $endDate);

    if ($startDateObj === false || $endDateObj === false) {
        error_log("Fehler bei der Datumsformatierung: Startdatum - $startDate, Enddatum - $endDate");
        return [];
    }

    $startOfDay = $startDateObj->format('Y-m-d') . ' 00:00:00';
    $endOfDay = $endDateObj->format('Y-m-d') . ' 23:59:59';

    $db = dbconnect();
    $query = "SELECT datetime, action1
              FROM scan4_userlog
              WHERE user = ? AND datetime BETWEEN ? AND ?
              ORDER BY datetime ASC";

    $stmt = $db->prepare($query);
    $stmt->bind_param("sss", $username, $startOfDay, $endOfDay);
    $stmt->execute();
    $result = $stmt->get_result();

    $appDataPerHour = [];
    while ($row = $result->fetch_assoc()) {
        $hourKey = substr($row['datetime'], 11, 2); // "HH"
        $action = $row['action1'];

        if (in_array($action, ['click phonenumber', 'create customer note', 'created an hbg', 'load homeid', 'moved an hbg', 'storno an appointment'])) {
            if (!isset($appDataPerHour[$hourKey])) {
                $appDataPerHour[$hourKey] = ['hour' => $hourKey];
            }

            if (!isset($appDataPerHour[$hourKey][$action])) {
                $appDataPerHour[$hourKey][$action] = 0;
            }

            $appDataPerHour[$hourKey][$action]++;
        }
    }

    return array_values($appDataPerHour); // Konvertieren Sie das assoziative Array in ein numerisches Array
}


function getAppDataPerDay($username, $startDate, $endDate)
{
    $startDateObj = DateTime::createFromFormat('d.m.Y', $startDate);
    $endDateObj = DateTime::createFromFormat('d.m.Y', $endDate);

    // Überprüfen, ob das Datum korrekt formatiert ist
    if ($startDateObj === false || $endDateObj === false) {
        error_log("Fehler bei der Datumsformatierung: Startdatum - $startDate, Enddatum - $endDate");
        return [];
    }

    // Start- und Enddatum für die Abfrage festlegen
    $startOfDay = $startDateObj->format('Y-m-d') . ' 00:00:00';
    $endOfDay = $endDateObj->format('Y-m-d') . ' 23:59:59';

    // Datenbankverbindung herstellen
    $db = dbconnect();
    $query = "SELECT datetime, action1
              FROM scan4_userlog
              WHERE user = ? AND datetime BETWEEN ? AND ?
              ORDER BY datetime ASC";

    $stmt = $db->prepare($query);
    $stmt->bind_param("sss", $username, $startOfDay, $endOfDay);
    $stmt->execute();
    $result = $stmt->get_result();

    $appDataPerDay = [];
    while ($row = $result->fetch_assoc()) {
        $dayKey = substr($row['datetime'], 0, 10); // "YYYY-MM-DD"

        if (in_array($row['action1'], ['click phonenumber', 'create customer note', 'created an hbg', 'load homeid', 'moved an hbg', 'storno an appointment'])) {
            if (!isset($appDataPerDay[$dayKey])) {
                $appDataPerDay[$dayKey] = ['date' => $dayKey];
            }

            if (!isset($appDataPerDay[$dayKey][$row['action1']])) {
                $appDataPerDay[$dayKey][$row['action1']] = 0;
            }

            $appDataPerDay[$dayKey][$row['action1']]++;
        }
    }

    // Konvertiere das assoziative Array in ein numerisches Array von Objekten
    return array_values($appDataPerDay);
}
