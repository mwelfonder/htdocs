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

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use scan4\Mailer;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


// Annahme: dbconnect() ist bereits definiert und stellt eine mysqli-Verbindung her

// Daten abrufen
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['dateSelectDate'])) {
  $date = $_POST['dateSelectDate'];

  // Datenbankverbindung herstellen
  $conn = dbconnect();
  if (!$conn) {
    // Verbindung fehlgeschlagen
    echo json_encode(['success' => false, 'message' => 'Datenbankverbindung fehlgeschlagen']);
    exit;
  }

  // SQL-Abfrage vorbereiten
  $sql = "SELECT hausbegeher, COUNT(*) AS total_entries, SUM(call_check = 1) AS call_check_true FROM scan4_hbg WHERE date = ? AND status = 'PLANNED' GROUP BY hausbegeher";

  $stmt = $conn->prepare($sql);
  if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Fehler bei der Vorbereitung der Abfrage']);
    exit;
  }

  // Parameter binden und Abfrage ausführen
  $stmt->bind_param("s", $date);
  $stmt->execute();

  $result = $stmt->get_result();
  $data = [];
  while ($row = $result->fetch_assoc()) {
    $data[] = $row;
  }

  // Antwort zurückgeben
  echo json_encode([
    'success' => true,
    'data' => $data
  ]);

  // Schließen der Verbindung und des Statements
  $stmt->close();
  $conn->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['func'])) {
  switch ($_POST['func']) {
    case 'fetchCustomerDetails':
      $username = $_POST['username'];
      $date = $_POST['date'];
      getData($username, $date);
      break;
    case 'saveRatingData':
      $uid = $_POST['uid'];
      $comment = $_POST['comment'];
      $rating = $_POST['rating'];
      saveHBGCheck($uid, $comment, $rating);
      break;
  }
}




function getData($hausbegeher, $date)
{
  $conn = dbconnect();
  if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Datenbankverbindung fehlgeschlagen']);
    exit;
  }

  $sql = "SELECT 
  hbg.date, hbg.time, hbg.homeid, hbg.uid, hbg.comment, hbg.status, hbg.username,  
  hbg.appt_file, hbg.appt_comment, hbg.call_check, hbg.call_rating, hbg.call_text, 
  homes.firstname, homes.lastname, homes.street, homes.streetnumber, 
  homes.streetnumberadd, homes.city, homes.plz, 
  homes.phone1, homes.phone2, homes.phone3, homes.phone4, 
  homes.scan4_phone1, homes.scan4_phone2
FROM 
  scan4_hbg AS hbg
JOIN 
  scan4_homes AS homes ON hbg.homeid = homes.homeid
WHERE 
  hbg.hausbegeher = ? AND hbg.date = ? AND hbg.status = 'PLANNED'
  AND (hbg.call_check IS NULL OR hbg.call_check = '')
ORDER BY 
  hbg.uid"; // Ersetzen Sie 'hbg.uid' durch den tatsächlichen Namen der Spalte, die den Termin eindeutig identifiziert

  $stmt = $conn->prepare($sql);
  if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Fehler bei der Vorbereitung der Abfrage']);
    exit;
  }

  $stmt->bind_param("ss", $hausbegeher, $date);
  $stmt->execute();

  $result = $stmt->get_result();
  $data = [];
  while ($row = $result->fetch_assoc()) {
    $data[] = $row;
  }

  echo json_encode([
    'success' => true,
    'data' => $data
  ]);

  $stmt->close();
  $conn->close();
}


function saveHBGCheck($uid, $comment, $rating)
{
  $mysqli = dbconnect();

  $stmt = $mysqli->prepare("UPDATE scan4_hbg SET call_check = ?, call_rating = ?, call_text = ? WHERE uid = ?");

  // Überprüfen Sie, ob das Prepared Statement erfolgreich war
  if (!$stmt) {
    echo "Fehler bei der Vorbereitung der Anfrage: (" . $mysqli->errno . ") " . $mysqli->error;
    return false;
  }

  // Binden Sie die Parameter an das Prepared Statement und führen Sie es aus
  // '1' für call_check, da dies fest auf 1 gesetzt werden soll
  $callCheck = 1;
  $stmt->bind_param('iiss', $callCheck, $rating, $comment, $uid);

  // Führen Sie das Prepared Statement aus
  if (!$stmt->execute()) {
    echo "Fehler beim Ausführen der Anfrage: (" . $stmt->errno . ") " . $stmt->error;
    return false;
  }

  // Überprüfen Sie, ob die Abfrage erfolgreich war
  if ($stmt->affected_rows > 0) {
    echo "Update erfolgreich.";
    return true;
  } else {
    echo "Kein Eintrag mit der angegebenen UID gefunden oder keine Änderung notwendig.";
    return false;
  }
}
