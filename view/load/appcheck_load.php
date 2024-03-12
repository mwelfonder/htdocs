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


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
  $action = $_POST['action'];
  if ($action == 'fetch_users') {

    $date = $_POST['date'];
    $users = getTechnicanUser();
    $userEntries = [];

    foreach ($users as $user) {
      $entries = getEntriesForUserOnDate($user['username'], $date);
      if (!empty($entries)) {
        $userEntries[] = [
          'user_id' => $user['user_id'],
          'email' => $user['email'],
          'hausbegeher' => $user['username'],
          'entries' => $entries
        ];
      }
    }

    echo json_encode($userEntries);
  } else {
    // Senden einer leeren Antwort, wenn keine Daten gefunden wurden
    echo json_encode([]);
  }

  if ($action == 'send_uid_error') {
    // Extrahieren Sie die Daten aus dem POST-Request
    $uid = $_POST['uid'];
    $errorDescription = $_POST['error'];
    $imageData = $_POST['pic']; // Dies ist der Base64-codierte Bilddatenstring

    // Verarbeiten und Speichern des Bildes
    $imagePath = saveErrorImage($uid, $imageData);
    if ($imagePath === false) {
      echo json_encode(['error' => 'Fehler beim Speichern des Bildes.']);
      exit;
    }

    // Fehlerinformationen in der Datenbank speichern
    $result = saveErrorInfo($uid, $errorDescription, $imagePath);
    if ($result) {
      echo json_encode(['success' => 'Fehler erfolgreich gemeldet.']);
    } else {
      echo json_encode(['error' => 'Fehler beim Melden des Fehlers.']);
    }
    exit;
  }
  if ($action == 'set_all_errors_found') {
    // Extrahieren Sie die Daten aus dem POST-Request
    $uid = $_POST['uid'];
    $uid_rating = $_POST['rating'];
    closeUID($uid, $uid_rating);

    if ($result) {
      echo json_encode(['success' => 'Erfolgreich geschlossen.']);
    } else {
      echo json_encode(['error' => 'Fehler beim schließen.']);
    }
    exit;
  }
  if ($action == 'fetch_errors_for_uid') {
    $uid = $_POST['uid'];
    header('Content-Type: application/json');
    $response = fetchErrorsForUID($uid);
    error_log(print_r($response, true)); // Loggen Sie das Ergebnis
    echo json_encode($response);
    exit;
  }
  if ($action == 'delete_error') {
    $uid = $_POST['uid']; // Die UID des Fehlers
    $result = deleteError($uid);

    if ($result) {
      echo json_encode(['success' => 'Fehler erfolgreich gelöscht.']);
    } else {
      echo json_encode(['error' => 'Fehler beim Löschen des Fehlers.']);
    }
    exit;
  }
  if ($action == 'check_mail_send') {
    $username = $_POST['username'];
    $date = $_POST['date'];

    // Benutzerdaten basierend auf dem Benutzernamen abrufen
    $userDetails = fetchUserDetails('username', $username); // Abrufen von Benutzerdetails

    // Überprüfen Sie, ob Benutzerdetails erfolgreich abgerufen wurden
    if ($userDetails && !empty($userDetails->email)) {
      $userEmail = $userDetails->email;

      // Hier rufen Sie die Funktion auf, um die benötigten Daten zu sammeln
      $mailData = collectMailData($username, $date);

      if ($mailData) {
        // Senden der E-Mail
        $currentuser = $user->data()->username; // Stellen Sie sicher, dass $user hier richtig definiert ist.
        $mailSent = sendErrorReportEmail($mailData, $username, $date, $currentuser, $userEmail);
        echo json_encode(['success' => $mailSent]);
      } else {
        echo json_encode(['error' => 'Keine Daten gefunden.']);
      }
    } else {
      echo json_encode(['error' => 'Benutzerdaten konnten nicht abgerufen werden.']);
    }
    exit;
  }
}

function getTechnicanUser()
{
  $usersWithPermission = fetchPermissionUsers(6);
  $usersData = [];

  foreach ($usersWithPermission as $user) {
    $userData = fetchUser($user->user_id); // Annahme, dass fetchUser die User-Informationen zurückgibt
    if ($userData) {
      $usersData[] = [
        'user_id' => $user->user_id,
        'username' => $userData->username,
        'email' => $userData->email // Die E-Mail-Adresse wird aus dem User-Objekt geholt
      ];
    }
  }

  return $usersData;
}

function getEntriesForUserOnDate($hausbegeher, $date)
{
  $conn = dbconnect();
  if (!$conn) {
    error_log("Datenbankverbindung fehlgeschlagen: " . mysqli_connect_error());
    return [];
  }
  $sql = "SELECT homeid, uid, time, appt_file, err_check FROM scan4_hbg WHERE hausbegeher = ? AND DATE(date) = ? AND appt_status = 'done' ORDER BY time";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ss", $hausbegeher, $date);
  $stmt->execute();
  $result = $stmt->get_result();
  $entries = [];

  while ($row = $result->fetch_assoc()) {
    $entries[] = [
      'homeid' => $row['homeid'],
      'uid' => $row['uid'],
      'username' => $row['hausbegeher'],
      'time' => $row['time'],
      'appt_file' => $row['appt_file'],
      'err_check' => $row['err_check']
    ];
  }

  $stmt->close();
  $conn->close();
  error_log("Einträge gefunden: " . count($entries));
  return $entries;
}

// Funktion zum Speichern des Bildes
function saveErrorImage($uid, $base64ImageData)
{
  if (empty($base64ImageData)) {
    // Kein Bild vorhanden, also geben Sie einen leeren Pfad zurück
    return '';
  }

  $errorDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/app_error/' . $uid . '/' . date('Y-m-d');
  if (!file_exists($errorDir)) {
    mkdir($errorDir, 0777, true);
  }

  $imagePath = $errorDir . '/' . uniqid('error_') . '.png';
  $base64ImageData = explode(',', $base64ImageData)[1]; // Entfernen Sie den Base64-Header
  $decodedImageData = base64_decode($base64ImageData);
  if (file_put_contents($imagePath, $decodedImageData) === false) {
    return false;
  }

  return $imagePath;
}


// Funktion zum Speichern der Fehlerinformationen in der Datenbank
function saveErrorInfo($uid, $errorDescription, $imagePath)
{
  $conn = dbconnect();
  $stmt = $conn->prepare("INSERT INTO scan4_hbg_error (uid, error, pic) VALUES (?, ?, ?)");
  $stmt->bind_param("sss", $uid, $errorDescription, $imagePath);
  $stmt->execute();
  $isSaved = $stmt->affected_rows > 0;
  $stmt->close();
  $conn->close();
  return $isSaved;
}

function closeUID($uid, $uid_rating)
{
  $conn = dbconnect();

  // SQL-Update-Anweisung mit Platzhaltern
  $sql = "UPDATE scan4_hbg SET err_check = 1, hbg_rating = ? WHERE uid = ?";

  if ($stmt = $conn->prepare($sql)) {
    // Binden der Parameter (zuerst den Bewertungswert, dann die UID)
    $stmt->bind_param("is", $uid_rating, $uid); // 'i' steht für Integer-Typ, 's' für String-Typ

    // Ausführen des Prepared Statements
    $stmt->execute();

    // Überprüfen, ob Zeilen betroffen sind
    $isSaved = $stmt->affected_rows > 0;

    // Schließen des Prepared Statements
    $stmt->close();
  } else {
    // Fehlerbehandlung, falls das Prepared Statement nicht erstellt werden konnte
    $isSaved = false;
  }

  // Schließen der Datenbankverbindung
  $conn->close();

  return $isSaved;
}

function collectMailData($hausbegeher, $date)
{
  $conn = dbconnect();

  // SQL-Abfrage, um die benötigten Daten zu erhalten
  $sql = "SELECT h.homeid, h.uid, e.error, e.pic, hm.street, hm.streetnumber, hm.streetnumberadd
            FROM scan4_hbg h
            LEFT JOIN scan4_hbg_error e ON h.uid = e.uid
            LEFT JOIN scan4_homes hm ON h.homeid = hm.homeid
            WHERE h.hausbegeher = ? AND DATE(h.date) = ? AND h.err_check = 1";

  $stmt = $conn->prepare($sql);
  if (!$stmt) {
    // Das Prepare-Statement ist fehlgeschlagen
    error_log("Prepare-Statement fehlgeschlagen: " . $conn->error);
    // Weitere Fehlerbehandlung hier...
  } else {
    $stmt->bind_param("ss", $hausbegeher, $date);
    $stmt->execute();
    $result = $stmt->get_result();
  }
  $mailData = [];



  while ($row = $result->fetch_assoc()) {
    $mailData[$row['homeid']][] = [
      'uid' => $row['uid'],
      'error' => $row['error'],
      'pic' => $row['pic'],
      'street' => $row['street'],
      'streetnumber' => $row['streetnumber'],
      'streetnumber_add' => $row['streetnumber_add']
    ];
  }

  $stmt->close();
  $conn->close();
  return $mailData;
}

function sendErrorReportEmail($mailData, $username, $date, $currentuser, $mailhbg)
{
  // Mailer-Instanz erstellen
  $mailer = new Mailer();

  // E-Mail-Details festlegen

  $mailto = $mailhbg;
  $subject = "HBG Überprüfung - $username - $date";
  $mailfrom = 'no_reply@scan4-gmbh.de';
  $mailfromname = 'Scan4 GmbH';
  $mailtocc = 'services@scan4-gmbh.de, d.cencelewicz@scan4-gmbh.de, j.kohl@scan4-gmbh.de, c.frank@scan4-gmbh.de';

  // HTML-Body mit Styles und Layout erstellen
  $body_message = "<!DOCTYPE html>
<html>
<head>
  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/>
  <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
  <title>HBG Überprüfung - $username - $date</title>
  <style>
  /* -------------------------------------
      GLOBAL RESETS
  ------------------------------------- */
  
  /*All the styling goes here*/
  
  img {
    border: none;
    -ms-interpolation-mode: bicubic;
    max-width: 100%; 
  }

  body {
    background-color: #f6f6f6;
    font-family: sans-serif;
    -webkit-font-smoothing: antialiased;
    font-size: 14px;
    line-height: 1.4;
    margin: 0;
    padding: 0;
    -ms-text-size-adjust: 100%;
    -webkit-text-size-adjust: 100%; 
  }

  table {
    border-collapse: separate;
    mso-table-lspace: 0pt;
    mso-table-rspace: 0pt;
    width: 100%; }
    table td {
      font-family: sans-serif;
      font-size: 14px;
      vertical-align: top; 
  }

  /* -------------------------------------
      BODY & CONTAINER
  ------------------------------------- */

  .body {
    background-color: #f6f6f6;
    width: 100%; 
  }

  /* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */
  .container {
    display: block;
    margin: 0 auto !important;
    /* makes it centered */
    max-width: 580px;
    padding: 10px;
    width: 580px; 
  }

  /* This should also be a block element, so that it will fill 100% of the .container */
  .content {
    box-sizing: border-box;
    display: block;
    margin: 0 auto;
    max-width: 580px;
    padding: 10px; 
  }

  /* -------------------------------------
      HEADER, FOOTER, MAIN
  ------------------------------------- */
  .main {
    background: #ffffff;
    border-radius: 3px;
    width: 100%; 
  }

  .wrapper {
    box-sizing: border-box;
    padding: 20px; 
  }

  .content-block {
    padding-bottom: 10px;
    padding-top: 10px;
  }

  .footer {
    clear: both;
    margin-top: 10px;
    text-align: center;
    width: 100%; 
  }
    .footer td,
    .footer p,
    .footer span,
    .footer a {
      color: #999999;
      font-size: 12px;
      text-align: center; 
  }

  /* -------------------------------------
      TYPOGRAPHY
  ------------------------------------- */
  h1,
  h2,
  h3,
  h4 {
    color: #000000;
    font-family: sans-serif;
    font-weight: 400;
    line-height: 1.4;
    margin: 0;
    margin-bottom: 30px; 
  }

  h1 {
    font-size: 35px;
    font-weight: 300;
    text-align: center;
    text-transform: capitalize; 
  }

  p,
  ul,
  ol {
    font-family: sans-serif;
    font-size: 14px;
    font-weight: normal;
    margin: 0;
    margin-bottom: 15px; 
  }
    p li,
    ul li,
    ol li {
      list-style-position: inside;
      margin-left: 5px; 
  }

  a {
    color: #3498db;
    text-decoration: underline; 
  }

  /* -------------------------------------
      BUTTONS
  ------------------------------------- */
  .btn {
    box-sizing: border-box;
    width: 100%; }
    .btn > tbody > tr > td {
      padding-bottom: 15px; }
    .btn table {
      width: auto; 
  }
    .btn table td {
      background-color: #ffffff;
      border-radius: 5px;
      text-align: center; 
  }
    .btn a {
      background-color: #ffffff;
      border: solid 1px #3498db;
      border-radius: 5px;
      box-sizing: border-box;
      color: #3498db;
      cursor: pointer;
      display: inline-block;
      font-size: 14px;
      font-weight: bold;
      margin: 0;
      padding: 12px 25px;
      text-decoration: none;
      text-transform: capitalize; 
  }

  .btn-primary table td {
    background-color: #3498db; 
  }

  .btn-primary a {
    background-color: #3498db;
    border-color: #3498db;
    color: #ffffff; 
  }

  /* -------------------------------------
      OTHER STYLES THAT MIGHT BE USEFUL
  ------------------------------------- */
  .last {
    margin-bottom: 0; 
  }

  .first {
    margin-top: 0; 
  }

  .align-center {
    text-align: center; 
  }

  .align-right {
    text-align: right; 
  }

  .align-left {
    text-align: left; 
  }

  .clear {
    clear: both; 
  }

  .mt0 {
    margin-top: 0; 
  }

  .mb0 {
    margin-bottom: 0; 
  }

  .preheader {
    color: transparent;
    display: none;
    height: 0;
    max-height: 0;
    max-width: 0;
    opacity: 0;
    overflow: hidden;
    mso-hide: all;
    visibility: hidden;
    width: 0; 
  }

  .powered-by a {
    text-decoration: none; 
  }

  hr {
    border: 0;
    border-bottom: 1px solid #f6f6f6;
    margin: 20px 0; 
  }

  /* -------------------------------------
      RESPONSIVE AND MOBILE FRIENDLY STYLES
  ------------------------------------- */
  @media only screen and (max-width: 620px) {
    table.body h1 {
      font-size: 28px !important;
      margin-bottom: 10px !important; 
    }
    table.body p,
    table.body ul,
    table.body ol,
    table.body td,
    table.body span,
    table.body a {
      font-size: 16px !important; 
    }
    table.body .wrapper,
    table.body .article {
      padding: 10px !important; 
    }
    table.body .content {
      padding: 0 !important; 
    }
    table.body .container {
      padding: 0 !important;
      width: 100% !important; 
    }
    table.body .main {
      border-left-width: 0 !important;
      border-radius: 0 !important;
      border-right-width: 0 !important; 
    }
    table.body .btn table {
      width: 100% !important; 
    }
    table.body .btn a {
      width: 100% !important; 
    }
    table.body .img-responsive {
      height: auto !important;
      max-width: 100% !important;
      width: auto !important; 
    }
  }

  /* -------------------------------------
      PRESERVE THESE STYLES IN THE HEAD
  ------------------------------------- */
  @media all {
    .ExternalClass {
      width: 100%; 
    }
    .ExternalClass,
    .ExternalClass p,
    .ExternalClass span,
    .ExternalClass font,
    .ExternalClass td,
    .ExternalClass div {
      line-height: 100%; 
    }
    .apple-link a {
      color: inherit !important;
      font-family: inherit !important;
      font-size: inherit !important;
      font-weight: inherit !important;
      line-height: inherit !important;
      text-decoration: none !important; 
    }
    #MessageViewBody a {
      color: inherit;
      text-decoration: none;
      font-size: inherit;
      font-family: inherit;
      font-weight: inherit;
      line-height: inherit;
    }
    .btn-primary table td:hover {
      background-color: #34495e !important; 
    }
    .btn-primary a:hover {
      background-color: #34495e !important;
      border-color: #34495e !important; 
    } 
  }

</style>
</head>
<body>
  <table role=\"presentation\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"body\">
    <tr>
      <td>&nbsp;</td>
      <td class=\"container\">
        <div class=\"content\">
          <!-- START CENTERED WHITE CONTAINER -->
          <table role=\"presentation\" class=\"main\">
            <!-- START MAIN CONTENT AREA -->
            <tr>
              <td class=\"wrapper\">
                <table role=\"presentation\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">
                  <tr>
                    <td>
                      <p>Hallo $username,</p>
                      <p>Hier sind alle Fehlermeldungen vom $date. Bitte berücksichtige diese in Zukunft.</p>";


  $errorCounter = 1;
  foreach ($mailData as $homeid => $errors) {
    $address = htmlspecialchars($errors[0]['street']) . ' ' . htmlspecialchars($errors[0]['streetnumber']);
    $address .= !empty($errors[0]['streetnumber_add']) ? ' ' . htmlspecialchars($errors[0]['streetnumber_add']) : '';
    $body_message .= "<h3>HomeID: $homeid - Adresse: $address</h3>";

    $errorCounter = 1; // Initialize error counter for each HomeID
    foreach ($errors as $error) {
      if (empty($error['error'])) {
        $body_message .= "<div class=\"error-row\"><p>Keine Fehler gefunden, gut gemacht.</p></div>";
        continue;
      }
      $imageHtml = ""; // Initialize $imageHtml as empty string
      if (!empty($error['pic'])) {
        $picPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads' . substr($error['pic'], strpos($error['pic'], '/app_error/'));
        if (file_exists($picPath)) {
          $cid = uniqid('img_');
          addEmbeddedImage($mailer, $picPath, $cid);
          $imageHtml = "<img class=\"image\" src=\"cid:$cid\" alt=\"Bild für Fehler\" height=\"180\" width=\"auto\" style=\"height:180px; width:auto;\"/>";
        }
      }

      $body_message .= "<div class=\"error-row\">";
      if (!empty($imageHtml)) {
        $body_message .= "<div class=\"error-cell image-cell\">$imageHtml</div>";
      }
      $body_message .= "<p><strong>Fehler $errorCounter:</strong> " . htmlspecialchars($error['error']) . "</p>";
      $body_message .= "</div>"; // End error-row div
      $errorCounter++;
    }
  }

  $body_message .= "          </td>
                  </tr>
                </table>
              </td>
            </tr>
            <!-- END MAIN CONTENT AREA -->
          </table>
          <!-- START FOOTER -->
          <div class=\"footer\">
            <table role=\"presentation\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">
              <tr>
                <td class=\"content-block\">
                  <strong>Danke fürs Lesen,</strong>
                  <br>bitte berücksichtige diese Fehler, damit wir in Zukunft alle eine bessere Arbeit abliefern können.
                  <br>Viele Grüße,
                  <br>$currentuser
                </td>
              </tr>
              <tr>
              <td class=\"content-block\">
                <span class=\"apple-link\">Scan4 GmbH, Karl-Weysser-Straße 17, 76227 Karlsruhe, Deutschland</span>
              </td>
            </tr>
              <tr>
                <td class=\"content-block powered-by\">
                  Powered by <a href=\"https://scan4-gmbh.de\">Scan4 GmbH</a>.
                </td>
              </tr>
            </table>
          </div>
          <!-- END FOOTER -->
          <!-- END CENTERED WHITE CONTAINER -->
        </div>
      </td>
      <td>&nbsp;</td>
    </tr>
  </table>
</body>
</html>";
  // E-Mail senden
  $success = $mailer->send($mailfrom, $mailfromname, $mailto, $mailfrom, $mailfromname, $mailtocc, '', $subject, $body_message, []);

  // Erfolg oder Fehler protokollieren
  if ($success) {
    return "E-Mail erfolgreich gesendet an $mailto";
  } else {
    return "Fehler beim Senden der E-Mail";
  }
}



function addEmbeddedImage(scan4\Mailer $mailer, $imagePath, $cid)
{
  try {
    $mailer->AddEmbeddedImage($imagePath, $cid, '', 0, 'image/png');
    return $cid;
  } catch (Exception $e) {
    // Fehlerbehandlung
    error_log("Fehler beim Einbetten des Bildes: " . $e->getMessage());
    return null;
  }
}


function fetchErrorsForUID($uid)
{
  $conn = dbconnect();
  if (!$conn) {
    // Fehlerbehandlung
    return [];
  }

  $sql = "SELECT error, pic FROM scan4_hbg_error WHERE uid = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $uid);
  $stmt->execute();
  $result = $stmt->get_result();
  $errors = [];

  while ($row = $result->fetch_assoc()) {
    $errors[] = [
      'error' => $row['error'],
      'pic' => $row['pic']
    ];
  }
  error_log('Fehlerdaten für UID ' . $uid . ': ' . print_r($errors, true)); // Diese Zeile hinzufügen
  $stmt->close();
  $conn->close();
  return $errors;
}


function deleteError($uid)
{
  $conn = dbconnect();
  if (!$conn) {
    // Fehlerbehandlung
    return false;
  }

  $sql = "DELETE FROM scan4_hbg_error WHERE uid = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $uid);
  $stmt->execute();
  $isDeleted = $stmt->affected_rows > 0;
  $stmt->close();
  $conn->close();
  return $isDeleted;
}
