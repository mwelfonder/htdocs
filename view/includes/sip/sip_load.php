<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}

if (!isset($user) || !$user->isLoggedIn()) {
    echo json_encode(['error' => 'Nicht authentifiziert']);
    exit;
}

include "../functions.php";
$currentuser = $user->data()->username;

// Benutzerdaten abrufen
$userId = $user->data()->id;
$db = DB::getInstance();
$userData = $db->get('users', array('id', '=', $userId))->first();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'log_call_event':
            logCallEvent($currentuser);
            break;
        case 'load_log':
            loadLog($currentuser);
            break;
        case 'list_sounds':
            listSounds();
            break;
            // Weitere Fälle für andere Aktionen
    }
}



function logCallEvent($currentuser)
{
    $conn = dbconnect();

    // Extrahieren und bereinigen der Daten aus der POST-Anfrage
    $event = $conn->real_escape_string($_POST['event'] ?? '');
    $direction = $conn->real_escape_string($_POST['direction'] ?? '');
    $toNumber = $conn->real_escape_string($_POST['to_number'] ?? '');
    $fromNumber = $conn->real_escape_string($_POST['from_number'] ?? '');
    $callID = $conn->real_escape_string($_POST['callID'] ?? '');
    $datetime = date('Y-m-d H:i:s'); // Aktuelles Datum und Uhrzeit

    // Überprüfen, ob bereits ein Datensatz mit der callID und dem event existiert
    $checkSql = "SELECT * FROM scan4_phoner WHERE callID = '$callID' AND event = '$event'";
    $result = $conn->query($checkSql);

    if ($result->num_rows > 0) {
        // Datensatz existiert bereits, kein neuer Eintrag nötig
        echo json_encode(['info' => 'Datensatz bereits vorhanden']);
    } else {
        // Kein Datensatz gefunden, füge neuen Eintrag hinzu
        $insertSql = "INSERT INTO scan4_phoner (datetime, event, direction, from_number, to_number, username, callID) VALUES ('$datetime', '$event', '$direction', '$fromNumber', '$toNumber', '$currentuser', '$callID')";
        if ($conn->query($insertSql) === TRUE) {
            echo json_encode(['success' => 'Daten erfolgreich gespeichert']);
        } else {
            echo json_encode(['error' => 'Fehler beim Speichern der Daten: ' . $conn->error]);
        }
    }

    $conn->close();
}


function loadLog($currentuser)
{
    $conn = dbconnect();

    // SQL-Query zum Holen der letzten 10 Anrufe
    $sql = "SELECT * FROM scan4_phoner WHERE username = '$currentuser' ORDER BY datetime DESC LIMIT 50";

    $result = $conn->query($sql);

    if ($result) {
        $calls = array();
        while ($row = $result->fetch_assoc()) {
            $calls[] = $row;
        }
        echo json_encode(['success' => 'Daten erfolgreich geladen', 'calls' => $calls]);
    } else {
        echo json_encode(['error' => 'Fehler beim Laden der Daten: ' . $conn->error]);
    }

    $conn->close();
}

function listSounds()
{
    $directory = $_SERVER['DOCUMENT_ROOT'] . '/view/includes/sip/tone/';
    $files = array();

    // Öffnen des Verzeichnisses und Lesen der Dateien
    if (is_dir($directory)) {
        if ($handle = opendir($directory)) {
            while (($file = readdir($handle)) !== false) {
                if ($file != "." && $file != ".." && strtolower(substr($file, strrpos($file, '.') + 1)) == 'mp3') {
                    $files[] = $file;
                }
            }
            closedir($handle);
        }
    }

    echo json_encode(['success' => 'Dateien erfolgreich geladen', 'files' => $files]);
}
