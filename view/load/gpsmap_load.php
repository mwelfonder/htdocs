<?php
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}
if (!securePage($_SERVER['PHP_SELF'])) {
    die();
}
$logFile = "./../error_logs/backend_log.log";
if (!file_exists(dirname($logFile))) {
    mkdir(dirname($logFile), 0755, true);
}
$logged_in = $user->data();
$currentuser = $logged_in->username;
include "../../view/includes/functions.php";


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['func']) && $_POST['func'] == 'getUsers') {
        // Rufen Sie hier die fetchAllUsers-Funktion auf
        $allUsers = fetchAllUsers();

        // Filtern Sie die Ergebnisse, um nur Benutzer mit GPS-Daten zurückzugeben
        $usersWithGpsLocation = array_filter($allUsers, function ($user) {
            return isset($user->gps_loc) && $user->gps_loc;
        });

        // Senden Sie die gefilterten Daten zurück
        echo json_encode($usersWithGpsLocation);
    }
    if (isset($_POST['func']) && $_POST['func'] == 'getUserLocations') {
        $mysqli = dbconnect();
        $usernames = $_POST['usernames'];
        error_log("Empfangene Benutzernamen: " . print_r($usernames, true) . "\n", 3, $logFile);
        $userLocations = [];

        foreach ($usernames as $username) {
            // Angenommen, Ihre Tabelle hat Spalten `lat` und `lon` für die Koordinaten
            $stmt = $mysqli->prepare("SELECT lat, lon FROM scan4_gps_loc WHERE username = ? ORDER BY datetime DESC LIMIT 1");
            if (!$stmt) {
                error_log("Prepare-Statement-Fehler: " . $mysqli->error . "\n", 3, $logFile);
                // Senden Sie eine Fehlermeldung zurück oder handeln Sie den Fehler
            }
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                // Speichern Sie die Koordinaten als Array
                $userLocations[$username] = [
                    'lat' => $row['lat'],
                    'lon' => $row['lon']
                ];
            }
        }

        echo json_encode($userLocations);
    }
}
