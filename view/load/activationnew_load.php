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




$action = $_POST['action'];
$mode = $_POST['mode'];
$selectedDate = $_POST['date'] ?? null; // Datum von der Kalenderauswahl

switch ($action) {
    case 'load':
        if ($mode == 'carousel') {
            echo loadCarouselData($selectedDate);
        }
        break;
    case 'activate':
        if (isset($_POST['uid'])) {
            $conn = dbconnect(); // Stellen Sie sicher, dass diese Zeile die Datenbankverbindung korrekt herstellt
            echo activateUid($_POST['uid'], $conn);
        }
        break;
    case 'activateerror':
        if (isset($_POST['uid'])) {
            $conn = dbconnect(); // Stellen Sie sicher, dass diese Zeile die Datenbankverbindung korrekt herstellt
            echo activateErrorUid($_POST['uid'], $conn);
        }
        break;
    case 'load_activationtracker':
        loadActivationTracker($selectedDate);
        break;
}


function loadActivationTracker($selectedDate)
{
    updateActivatedForDeutscheGlasfaser();
    updateActivatedForScan4();
    updateActivatedForMeridiam();
    $conn = dbconnect();

    if (!$selectedDate) {
        $today = date('w');
        $daysToAdd = ($today == 5) ? 3 : 1;
        $selectedDate = date('Y-m-d', strtotime("+{$daysToAdd} days"));
    }

    $totalQuery = "SELECT COUNT(*) FROM scan4_hbg WHERE date = '$selectedDate' AND status ='PLANNED'";
    $totalResult = $conn->query($totalQuery);
    $total = $totalResult ? $totalResult->fetch_row()[0] : 0;

    $activatedQuery = "SELECT COUNT(*) FROM scan4_hbg WHERE (activated = 1 OR activated = 2 OR activated = 3) AND date = '$selectedDate' AND status ='PLANNED'";
    $activatedResult = $conn->query($activatedQuery);
    $activated = $activatedResult ? $activatedResult->fetch_row()[0] : 0;

    echo "$activated von $total"; // Verwenden Sie echo anstelle von return
}


function loadCarouselData($selectedDate)
{
    $conn = dbconnect();
    $dateCondition = '';

    // Überprüfen, ob ein Datum übermittelt wurde
    if (!$selectedDate) {
        // Bestimmen des nächsten Tags (Freitag -> Montag)
        $today = date('w');
        $daysToAdd = ($today == 5) ? 3 : 1; // Freitag: +3 Tage, sonst: +1 Tag
        $nextDay = date('Y-m-d', strtotime("+{$daysToAdd} days"));
        $dateCondition = "date = '$nextDay'";
    } else {
        // Ein Datum wurde gewählt
        $dateCondition = "date = '$selectedDate'";
    }

    
    $query = "SELECT h.*, hm.client, hm.city, hm.carrier, hm.street, hm.streetnumber, hm.streetnumberadd 
    FROM scan4_hbg h 
    INNER JOIN scan4_homes hm ON h.homeid = hm.homeid 
    WHERE h.activated = 0 
      AND (
        (hm.carrier != 'GlasfaserPlus' AND $dateCondition)
        OR (hm.carrier = 'GlasfaserPlus' AND $dateCondition)
        OR (hm.city LIKE '%MDU')
      )
      AND status = 'PLANNED'
    ORDER BY 
      CASE 
        WHEN hm.city LIKE '%MDU' AND hm.client = 'Insyte' THEN 1
        WHEN hm.city LIKE '%MDU' AND hm.client = 'Moncobra' THEN 2
        WHEN hm.client = 'Moncobra' THEN 3 
        WHEN hm.client = 'Insyte' THEN 4 
        ELSE 5 
      END,
      CASE hm.carrier 
        WHEN 'DGF' THEN 1 
        WHEN 'UGG' THEN 2 
        WHEN 'GVG' THEN 3 
        WHEN 'GlasfaserPlus' THEN 4 
        ELSE 5 
      END";
    

    /* QUERY GUT - G+ UND MDU ALLES VORRAUS
    $query = "SELECT h.*, hm.client, hm.city, hm.carrier, hm.street, hm.streetnumber, hm.streetnumberadd 
    FROM scan4_hbg h 
    INNER JOIN scan4_homes hm ON h.homeid = hm.homeid 
    WHERE h.activated = 0 
      AND (
        (hm.carrier != 'GlasfaserPlus' AND $dateCondition)
        OR (hm.carrier = 'GlasfaserPlus')
        OR (hm.city LIKE '%MDU')
      )
      AND status = 'PLANNED'
    ORDER BY 
      CASE 
        WHEN hm.city LIKE '%MDU' AND hm.client = 'Insyte' THEN 1
        WHEN hm.city LIKE '%MDU' AND hm.client = 'Moncobra' THEN 2
        WHEN hm.client = 'Moncobra' THEN 3 
        WHEN hm.client = 'Insyte' THEN 4 
        ELSE 5 
      END,
      CASE hm.carrier 
        WHEN 'DGF' THEN 1 
        WHEN 'UGG' THEN 2 
        WHEN 'GVG' THEN 3 
        WHEN 'GlasfaserPlus' THEN 4 
        ELSE 5 
      END";
*/


    /*
        $query = "SELECT h.*, hm.client, hm.carrier, hm.street, hm.streetnumber, hm.streetnumberadd 
    FROM scan4_hbg h 
    INNER JOIN scan4_homes hm ON h.homeid = hm.homeid 
    WHERE h.activated = 0 
      AND (
        (hm.carrier != 'GlasfaserPlus' AND $dateCondition) 
        OR (hm.carrier = 'GlasfaserPlus')
      )
      AND status ='PLANNED'
    ORDER BY 
      CASE 
        WHEN hm.client = 'Insyte' THEN 1 
        WHEN hm.client = 'Moncobra' THEN 2 
        ELSE 3 
      END,
      CASE hm.carrier 
        WHEN 'DGF' THEN 1 
        WHEN 'UGG' THEN 2 
        WHEN 'GVG' THEN 3 
        WHEN 'GlasfaserPlus' THEN 4 
        ELSE 5 
      END
    LIMIT 1";
    */

    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Hausbegeher-Details mit Userspice-Funktion fetchUserDetails abrufen
        $hausbegeherDetails = fetchUserDetails('username', $row['hausbegeher']);
        $team = $hausbegeherDetails ? $hausbegeherDetails->team : 'Unbekannt';

        // Datenformatierung und Anzeige
        $dataHtml = "<div class='header'> {$row['client']} - {$row['carrier']}</div>
                     <div><strong>Address:</strong> <span onclick='copyToClipboard(\"{$row['street']}\")'>{$row['street']}</span>, {$row['streetnumber']}{$row['streetnumberadd']}, {$row['city']}</div>
                     <div><strong>HomeID:</strong> <span onclick='copyToClipboard(\"{$row['homeid']}\")'>{$row['homeid']}</span></div>
                     <div><strong>Datum:</strong> {$row['date']}</div>
                     <div><strong>Uhrzeit:</strong> {$row['time']}</div>
                     <div><strong>Hausbegeher:</strong> {$row['hausbegeher']}</div>
                     <div><strong>Kommentar:</strong> {$row['comment']}</div>
                     <div><strong>Telefonist:</strong> {$row['username']}</div>
                     <div><strong>Erstellt:</strong> {$row['created']}</div>
                     <div><strong>Team:</strong> $team</div>
                     <input type='hidden' id='uid' name='uid' value='{$row['uid']}'>"; // Verstecktes Feld für die UID

        return $dataHtml;
    } else {
        // Keine Daten gefunden
        error_log("Keine Termine gefunden für das Datum: " . $selectedDate);
        return '<div class="no-data"><i class="ri-ghost-fill"></i> Es gibt keine Termine zum Anzeigen.</div>';
    }
}
function activateUid($uid, $conn)
{
    $query = "UPDATE scan4_hbg SET activated = 1 WHERE uid = ?";
    $stmt = $conn->prepare($query);

    // Binden des Parameters als String
    $stmt->bind_param("s", $uid);

    if ($stmt->execute()) {
        return "Aktivierung erfolgreich";
    } else {
        return "Fehler bei der Aktivierung: " . $stmt->error;
    }
}

function activateErrorUid($uid, $conn)
{
    $query = "UPDATE scan4_hbg SET activated = 2 WHERE uid = ?";
    $stmt = $conn->prepare($query);

    // Binden des Parameters als String
    $stmt->bind_param("s", $uid);

    if ($stmt->execute()) {
        return "Aktivierung erfolgreich";
    } else {
        return "Fehler bei der Aktivierung: " . $stmt->error;
    }
}

function updateActivatedForDeutscheGlasfaser()
{
    // Verbindung zur Datenbank herstellen
    $conn = dbconnect();

    // SQL Query, um 'activated' in 'scan4_hbg' zu aktualisieren
    $sql = "UPDATE scan4_hbg 
            SET activated = 1 
            WHERE homeid IN (
                SELECT homeid 
                FROM scan4_homes 
                WHERE carrier = 'DGF'
            )";

    // Query ausführen
    if ($conn->query($sql) === TRUE) {
    } else {
        echo "Error updating record: " . $conn->error;
    }

    // Verbindung schließen
    $conn->close();
}

function updateActivatedForMeridiam()
{
    // Verbindung zur Datenbank herstellen
    $conn = dbconnect();

    // SQL Query, um 'activated' in 'scan4_hbg' zu aktualisieren
    $sql = "UPDATE scan4_hbg 
            SET activated = 3 
            WHERE homeid IN (
                SELECT homeid 
                FROM scan4_homes 
                WHERE carrier = 'MERIDIAM'
            )";

    // Query ausführen
    if ($conn->query($sql) === TRUE) {
    } else {
        echo "Error updating record: " . $conn->error;
    }

    // Verbindung schließen
    $conn->close();
}


function updateActivatedForScan4()
{
    // Verbindung zur Datenbank herstellen
    $conn = dbconnect();

    // SQL Query, um 'activated' in 'scan4_hbg' zu aktualisieren
    $sql = "UPDATE scan4_hbg 
            SET activated = 3 
            WHERE homeid IN (
                SELECT homeid 
                FROM scan4_homes 
                WHERE client = 'SCAN4'
            )";

    // Query ausführen
    if ($conn->query($sql) === TRUE) {
    } else {
        echo "Error updating record: " . $conn->error;
    }

    // Verbindung schließen
    $conn->close();
}

// Funktion aufrufen

