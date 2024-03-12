<?php

set_time_limit(10000);
ini_set('display_errors', 'On');

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



//#####################################################################################
// Move files to FTP Server
// hbgProtokollTOFTP();

//pendingMail();
//ticketStatUpdate();

latlongcalendar(); //temporary fix to get lat lon in callender from old to now system



//#####################################################################################
// Mail Cron
bugreport_send(); // Fetch data from database, generate xlsx and zipfile then send mail

//#####################################################################################
// Auto finish the HBG reports KD not there and Im not there and set them to OPEN
hbg_check();

//#####################################################################################
// Get missing lat long for all homeids from adress infos
getalllatlong();

//#####################################################################################
// Fix wrong clients in homes client column. Match the client from citylist to all rows in scan4_homes
fixclient();


//#####################################################################################
// this crosscheck relations between adress and phone. And remove all Musterhomes to clean relations
updateRelations();
removeMusterPPL();


//#####################################################################################
// switch overdues
switchOverdues();



function logEvent($message, $logFile)
{
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "$timestamp - $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}
function updateJsonLog($data, $jsonLogFile)
{
    $fp = fopen($jsonLogFile, 'c+');
    if (flock($fp, LOCK_EX)) { // acquire an exclusive lock
        ftruncate($fp, 0); // truncate file
        fwrite($fp, json_encode($data, JSON_PRETTY_PRINT)); // write the new data
        fflush($fp); // flush output before releasing the lock
        flock($fp, LOCK_UN); // release the lock
    } else {
        echo "Couldn't get the lock!";
    }
    fclose($fp);
}
function hbgProtokollTOFTP()
{
    $localDir = '/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/uploads/hbgprotokolle/begehungen';

    $ftpServer = 'u375865.your-storagebox.de';
    $ftpUsername = 'u375865-sub1';
    $ftpPassword = 'GA73iXeMQt8NjG9e';
    $ftpRemoteBaseDir = '/uploads/hbgprotokolle/begehungen';
    $jsonLogFile = '/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/uploads/hbgprotokolle/begehungen/transfer_log.json';
    $generalLogFile = '/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/uploads/hbgprotokolle/begehungen/general_log.txt';


    $connId = ftp_connect($ftpServer) or die(logEvent("Could not connect to $ftpServer", $generalLogFile));
    $login = ftp_login($connId, $ftpUsername, $ftpPassword);
    if (!$login) {
        logEvent('Could not log in to the FTP server. Check credentials.', $generalLogFile);
        die();
    }
    logEvent('FTP connection established.', $generalLogFile);
    ftp_pasv($connId, true);

    $transferredFiles = file_exists($jsonLogFile) ? json_decode(file_get_contents($jsonLogFile), true) : [];

    $directory = new RecursiveDirectoryIterator($localDir, RecursiveDirectoryIterator::SKIP_DOTS);
    $iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);

    $counter = 0; // File processing counter
    foreach ($iterator as $fileInfo) {
        if ($fileInfo->isFile()) {
            $fileName = $fileInfo->getFilename();
            $localFilePath = $fileInfo->getPathname();
            $relativePath = substr($localFilePath, strlen($localDir) + 1);
            $remoteFilePath = $ftpRemoteBaseDir . '/' . $relativePath;

            if (!isset($transferredFiles[$fileName]) || !$transferredFiles[$fileName]['transferred']) {
                // Ensure remote directory exists
                $remoteDirPath = dirname($remoteFilePath);
                if (!@ftp_chdir($connId, $remoteDirPath)) {
                    // Create directory recursively if needed
                    $parts = explode('/', $remoteDirPath);
                    $path = '';
                    foreach ($parts as $part) {
                        if (!$part) continue;
                        $path .= '/' . $part;
                        if (!@ftp_chdir($connId, $path)) {
                            ftp_mkdir($connId, $path);
                        }
                    }
                }

                if (ftp_put($connId, $remoteFilePath, $localFilePath, FTP_BINARY)) {
                    echo "Successfully uploaded $localFilePath\n";
                    logEvent("Successfully uploaded $localFilePath", $generalLogFile);
                    $transferredFiles[$fileName] = [
                        'transferred' => true,
                        'timestamp' => date('Y-m-d H:i:s')
                    ];
                    updateJsonLog($transferredFiles, $jsonLogFile);
                } else {
                    logEvent("There was a problem while uploading $localFilePath", $generalLogFile);

                    echo "There was a problem while uploading $localFilePath\n";
                }
            }

            if (++$counter >= 20) {
                //  break; // Stop after processing 20 files
            }
        }
    }

    ftp_close($connId);
    logEvent('FTP connection closed.', $generalLogFile);
}




function pendingMail()
{
    $pendingCity = 'Eschbach';
    // Setze die Zeitzone auf "Europe/Berlin"
    date_default_timezone_set('Europe/Berlin');

    // Überprüfung der aktuellen Zeit
    //if (date('H') < 09) {
    //    echo "Es ist vor 16 Uhr (Berlin Zeit). Funktion pendingMail() wird nicht ausgeführt.<br>";
    //    return;
    //}
    echo "Funktion pendingMail() gestartet.<br>";

    $conn = dbconnect();
    if (!$conn) {
        echo "Verbindungsfehler: Keine Verbindung zur Datenbank.<br>";
        return;
    } else {
        echo "Verbindung zur Datenbank hergestellt.<br>";
    }

    // Anfrage an die Datenbank
    $query = "
    SELECT 
        email, carrier, scan4_status, homeid, street, streetnumber, plz, city, streetnumberadd, firstname, lastname 
    FROM scan4_homes 
    WHERE 
        scan4_status = 'PENDING' 
        AND client = 'Insyte' 
        AND city = '$pendingCity' 
        AND (emailsend IS NULL OR emailsend = '') 
        AND (priority IN (1, 2, 3, 4, 5))
    ORDER BY anruf5 ASC 
    LIMIT 1000;";

    $stmt = $conn->prepare($query);

    if (!$stmt) {
        echo "Fehler beim Vorbereiten der Anfrage: " . $conn->error . "<br>";
        return;
    }

    if (!$stmt->execute()) {
        die("Fehler bei der Ausführung: " . $stmt->error . "<br>");
    }

    $result = $stmt->get_result();
    if (!$result) {
        die("Fehler beim Abrufen der Ergebnisse: " . $conn->error . "<br>");
    }
    $customers = $result->fetch_all(MYSQLI_ASSOC);

    echo "Abfrage erfolgreich. Kundendaten abgerufen.<br>";

    // Pfad für die Log-Datei
    $flagfile = $_SERVER['DOCUMENT_ROOT'] . '/view/includes/mail/files/' . date('Y_m') . '/' .  'insyte/' .  date('Y_m_d') . '_flag_pendingmail.txt';

    // Überprüfung, ob die Datei bereits existiert
    if (file_exists($flagfile)) {
        echo 'Datei existiert bereits. Verlassen der Funktion.<br>';
        return; // Frühzeitiges Verlassen der Funktion, da die Datei bereits existiert
    }

    $noMailCustomers = []; // Array für Kunden ohne E-Mail
    foreach ($customers as $customer) {
        if (empty($customer['email'])) {
            $noMailCustomers[] = $customer['email'];
        } else {
            echo "Verarbeitung der E-Mail für " . $customer['email'] . ".<br>";

            $mailto = $customer['email'];
            $mailtocc = 'hbg-support@insytedeutschland.de, kundenservice@scan4-gmbh.de, c.floeter@scan4-gmbh.de';

            if ($customer['carrier'] == 'DGF') {
                $customer['carrier'] = 'Deutsche Glasfaser';
            } elseif ($customer['carrier'] == 'UGG') {
                $customer['carrier'] = 'Unsere Grüne Glasfaser';
            }

            $body_message = '
            <!doctype html>
            <html>
              <head>
                <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                <title>Simple Transactional Email</title>
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
              <span class="preheader">Hausbegehung für Glasfasertrasse.</span>
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body">
                  <tr>
                      <td>&nbsp;</td>
                      <td class="container">
                          <div class="content">
          
                              <!-- START CENTERED WHITE CONTAINER -->
                              <table role="presentation" class="main">
          
                                  <!-- START MAIN CONTENT AREA -->
                                  <tr>
                                      <td class="wrapper">
                                          <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                              <tr>
                                                  <td>
                                                      <div class="email-container">
                                                          <p class="greeting">Sehr geehrte/r Frau / Herr ' . $customer['lastname'] . ',</p>
                                                          <p>Im Auftrag Ihres Glasfaseranbieters ' . $customer['carrier'] . ' und der zugehörigen Tiefbaufirma möchten wir gerne eine Hausbegehung zur Festlegung der Glasfasertrasse durchführen. Da wir Sie telefonisch leider nicht erreichen konnten, möchten wir Sie bitten, sich mit uns unter der Telefonnummer <a href="tel:015906723657" class="contact">+49 1590 6723657</a> in Verbindung zu setzen, um einen passenden Termin zu koordinieren.</p>
                                                          <p> Wir sind zwischen 10:00 und 16:00 Uhr von Montag bis Freitag für Sie erreichbar. </p>
                                                          <p class="property-details">Es handelt sich um folgende Immobilie:<br>
                                                          ' . $customer['street'] . ' ' . $customer['streetnumber'] . $customer['streetnumberadd'] . '<br>
                                                          ' . $customer['plz'] . ', ' . $customer['city'] . '.
                                                          </p>
                                                          <p class="signature">Mit freundlichen Grüßen,<br>Carsten Flöter</p>
                                                      </div>
                                                  </td>
                                              </tr>
                                          </table>
                                      </td>
                                  </tr>
          
                                  <!-- END MAIN CONTENT AREA -->
                              </table>
                              <!-- END CENTERED WHITE CONTAINER -->
          
                              <!-- START FOOTER -->
                              <div class="footer">
                              <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                              <tr>
                                  <td class="content-block impressum">
                                      <h4 style="font-size: 14px;">Impressum</h4>
                                      <p style="font-size: 10px; line-height: 1.2;">Angaben gem. §5 TMG:<br>
                                      Scan4 GmbH<br>
                                      Karl-Weysser-Straße 17, 76227 Karlsruhe, Deutschland<br>
                                      Tel.: <a href="tel:+4972198191547">+49 721 981 915 47</a><br>
                                      Email: <a href="mailto:info@scan4-gmbh.de">info@scan4-gmbh.de</a><br>
                                      www.scan4-gmbh.de<br>
                                      Vertretungsberechtigte GF: Jens Kohl / Sergio Jimenez<br>
                                      Registergericht Mannheim, HRB 736121<br>
                                      USt-ID gem. §27a UStG Nr. DE329242883</p>
                                  </td>
                              </tr>
                                      <tr>
                                          <td class="content-block powered-by">
                                              Powered by Scan4 GmbH</a>.
                                          </td>
                                      </tr>
                                  </table>
                              </div>
                              <!-- END FOOTER -->
          
                          </div>
                      </td>
                      <td>&nbsp;</td>
                  </tr>
              </table>
          </body>
          </html>
            ';



            // Send email with attachment
            $mailer = new Mailer();
            $success =  $mailer->send('c.floeter@scan4-gmbh.de', 'Scan4', $mailto, 'c.floeter@scan4-gmbh.de', 'Scan4 GmbH', $mailtocc, '', $customer['carrier'] . ' Hausbegehung - ' . $customer['street'] . ' ' . $customer['streetnumber'] . $customer['streetnumberadd'], $body_message);
            if ($success) {
                // Stellen Sie sicher, dass das Verzeichnis existiert, bevor Sie Dateien schreiben
                $directory = dirname($flagfile);
                if (!is_dir($directory)) {
                    mkdir($directory, 0777, true); // rekursives Erstellen
                }

                // Verwenden Sie den 'a'-Modus, um an die Datei anzuhängen, nicht den 'w'-Modus
                $file = fopen($flagfile, "a");
                // Nur relevante Informationen loggen
                fwrite($file, "Email sent successfully to " . $customer['email'] . " on " . date('Y_m_d H:i:s') . "\n");
                fclose($file);

                echo "E-Mail erfolgreich an " . $customer['email'] . " gesendet.<br>";
            }
        }
        // Datum in 'emailsend' nach erfolgreichem Versenden aktualisieren
        $updateDateQuery = "UPDATE scan4_homes SET emailsend = NOW() WHERE email = ? AND city = '$pendingCity'";
        $updateDateStmt = $conn->prepare($updateDateQuery);
        $updateDateStmt->bind_param("s", $customer['email']);
        if (!$updateDateStmt->execute()) {
            echo "Fehler beim Datum-Update: " . $updateDateStmt->error;
        }
        $updateDateStmt->close();
    }

    // UPDATE für alle Kunden ohne E-Mail in einem Durchgang
    if (!empty($noMailCustomers)) {
        echo "Aktualisiere Kunden ohne E-Mail.<br>";
        $placeholders = implode(',', array_fill(0, count($noMailCustomers), '?'));
        $types = str_repeat("s", count($noMailCustomers));
        $updateNoMailQuery = "UPDATE scan4_homes SET emailsend = 'NO_MAIL' WHERE email IN ($placeholders)";
        $updateNoMailStmt = $conn->prepare($updateNoMailQuery);
        $updateNoMailStmt->bind_param($types, ...$noMailCustomers);
        if (!$updateNoMailStmt->execute()) {
            echo "Fehler beim No-Mail-Update: " . $updateNoMailStmt->error;
        }
        $updateNoMailStmt->close();
        echo "Update für Kunden ohne E-Mail abgeschlossen.<br>";
    }
    $stmt->close();
    $conn->close();

    echo "Verbindung zur Datenbank geschlossen.<br>";

    $directory = dirname($flagfile);
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true); // rekursives Erstellen
        echo "Verzeichnis erstellt.<br>";
    }

    // Erstellen der Datei
    file_put_contents($flagfile, "Mail pending flag for " . date('Y_m_d'));
    echo "Flag-Datei für ausstehende Mail erstellt.<br>";

    echo "Funktion pendingMail() abgeschlossen.<br>";
}











function hbg_check()
{

    $date = date('Y-m-d');
    $conn = dbconnect();
    //$query = "SELECT * FROM `scan4_bug_reports` WHERE datetime LIKE '" . $date . "%' ORDER BY `datetime` DESC";
    $query = "SELECT 
    scan4_hbg.*, 
    scan4_homes.carrier 
FROM 
    scan4_hbg
INNER JOIN 
    scan4_homes ON scan4_hbg.homeid = scan4_homes.homeid 
WHERE 
    scan4_hbg.date BETWEEN '2023-01-01' AND CURDATE() 
    AND scan4_hbg.reviewed IS NULL AND (appt_status LIKE 'Kunde war nicht da' OR appt_status LIKE 'Ich war nicht da')
ORDER BY 
    scan4_hbg.date DESC;
";


    //echo $query;
    if ($result = $conn->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $result->free_result();
    }

    $datetime = date("Y-m-d H:i:s");


    foreach ($data as $row) {
        // write to userlog
        $query = "INSERT INTO `scan4_userlog` SET `datetime`='" . $datetime . "', `user`='System',homeid = '" . $row['homeid'] . "',source = 'hbgcheck', `action1`='cronjob found " . $row['appt_status'] . "', `action2`='cronjob set to OPEN' , `action3`='" . $row['appt_comment'] . "', `action4`='" . $row['uid'] . "'";
        mysqli_query($conn, $query);
        // mark as reviewed
        $query = "UPDATE `scan4_hbg` SET `reviewed`='1' WHERE `uid` = '" . $row['uid'] . "'";
        mysqli_query($conn, $query);
        // create new hbgcheck
        if ($row['appt_status'] === 'Kunde war nicht da') {
            $query = "INSERT INTO `scan4_hbgcheck` SET `datetime`='" . $datetime . "', `user`='System',homeid = '" . $row['homeid'] . "',ident = '" . $row['uid'] . "', `status`='OPEN', `reason`='" . $row['appt_status'] . "' , `comment`='Kunde war nicht da, neu terminieren - Wenn der KD sagt, er war da, bitte Alex bescheid geben.'";
            mysqli_query($conn, $query);
            // update home status
            $query = "UPDATE `scan4_homes` SET `scan4_status`='OPEN', scan4_comment = '" . $row['appt_status'] . "' WHERE `homeid` = '" . $row['homeid'] . "'";
            mysqli_query($conn, $query);
        } elseif ($row['appt_status'] === 'Ich war nicht da') {
            $query = "INSERT INTO `scan4_hbgcheck` SET `datetime`='" . $datetime . "', `user`='System',homeid = '" . $row['homeid'] . "',ident = '" . $row['uid'] . "', `status`='OPEN', `reason`='" . $row['appt_status'] . "' , `comment`='HBGer nicht vor Ort, Alex informieren, wenn der Kunde nicht informiert wurde'";
            mysqli_query($conn, $query);
            // update home status
            $query = "UPDATE `scan4_homes` SET `scan4_status`='OPEN', scan4_comment = '" . $row['appt_status'] . "' WHERE `homeid` = '" . $row['homeid'] . "'";
            mysqli_query($conn, $query);
        } elseif ($row['appt_status'] === '' && $row['scan4_status'] === 'OVERDUE') {
            //$query = "INSERT INTO `scan4_hbgcheck` SET `datetime`='" . $datetime . "', `user`='System',homeid = '" . $row['homeid'] . "',ident = '" . $row['uid'] . "', `status`='DONE', `reason`='" . $row['appt_status'] . "' , `comment`='Overdue reset.'";
            //mysqli_query($conn, $query);
            // $updateQuery = "UPDATE `scan4_homes` SET `scan4_status`='OPEN' WHERE `homeid` = '" . $row['homeid'] . "'";
            //mysqli_query($conn, $updateQuery);
        }
    }

    mysqli_close($conn);
}



function bugreport_send()
{

    $excelfile_name = date('Y_m_d', strtotime('-1 day')) . '_' . 'app_errors';
    $zipfile_name = date('Y_m_d', strtotime('-1 day')) . '_' . 'app_errors';
    $data = bugreport_fetch();

    // split the data to the carrier
    $data_insyte = array();
    $data_moncobra = array();
    $data_count['insyte'] = 0;
    $data_count['moncobra'] = 0;
    foreach ($data as $row) {

        if ($row['client'] === 'Insyte') {
            $data_insyte[] = $row;
            $data_count['insyte']++;
        } elseif ($row['client'] === 'Moncobra') {
            $data_moncobra[] = $row;
            $data_count['moncobra']++;
        } else {
            // If carrier is empty, copy to both arrays
            $data_insyte = $row;
            $data_moncobra = $row;
            $data_count['insyte']++;
            $data_count['moncobra']++;
        }
    }


    /*
    echo '<pre>';
    print_r($data);
    echo '</pre>';
*/

    for ($i = 0; $i < 2; $i++) {

        if ($i == 0) {
            $client = 'insyte';
            $mailto = 'acreal@insyteinstalaciones.es, pmorales@insyteinstalaciones.es';
            $mailtocc = 'tavalverde@insyteinstalaciones.es, agcampo@insyteinstalaciones.es, it@scan4-gmbh.de, services@scan4-gmbh.de';

            if ($data_count['insyte'] === 0) continue; // skip to the next loop. No data for this carrier 
        } else if ($i == 1) {
            $client = 'moncobra';
            $mailto = 'consuelo.jara@grupocobra.com, daisy.perez@grupocobra.com';
            $mailtocc = 'it@scan4-gmbh.de, services@scan4-gmbh.de';

            if ($data_count['moncobra'] === 0) continue; // skip to the next loop. No data for this carrier
        }

        $flagfile = $_SERVER['DOCUMENT_ROOT'] . '/view/includes/mail/files/' . date('Y_m') . '/' . $client . '/' .  date('Y_m_d') . '_flag_mail.txt';
        if (file_exists($flagfile)) {
            echo 'File already exist';
            continue; // skip to the next loop. This file already exist = mail is send
        }




        if ($i == 0) {
            $excelfile = bugreport_excel($data_insyte, $excelfile_name, $client);
            $zipFile = bugreport_zip($data_insyte, $zipfile_name, $client);
        } else if ($i == 1) {
            $excelfile = bugreport_excel($data_moncobra, $excelfile_name, $client);
            $zipFile = bugreport_zip($data_moncobra, $zipfile_name, $client);
        }


        if ($zipFile === null) {
            echo "<br>Error: No files to add to zip archive";
        } else {
            echo "<br>Zip file created: " . $zipFile;
        }


        echo '<br>' . $zipFile . '<br>';


        $attachments = [];
        if ($zipFile !== null) {
            $attachments[] = [
                'path' => $zipFile,
                'name' => $zipfile_name
            ];
        }
        $attachments[] = [
            'path' => $excelfile,
            'name' => $excelfile_name
        ];

        echo '<pre>';
        print_r($attachments);
        echo '</pre>';

        $body_message = 'Hello Team ' . ucfirst($client) . ', <br><br>attached you will find the error reports of the last 24 hours. <br><br>Kind regards, <br>Scan4 Team<br><br>This is an automatically generated email. Please do not reply to this email.';
        // Send email with attachment
        $mailer = new Mailer();
        $success =  $mailer->send('no_reply@scan4-gmbh.de', 'Scan4', $mailto, 'no_reply@scan4-gmbh.de', 'Scan4 GmbH', $mailtocc, '', 'App Errors from ' . date('Y_m_d', strtotime('-1 day')), $body_message, $attachments);
        // create flagfile and write the content inside
        if ($success) {
            $file = fopen($flagfile, "w");
            fwrite($file, $body_message . "\n" . $success . "\n");

            if (!empty($attachments)) {
                fwrite($file, "Attachments:\n");
                foreach ($attachments as $attachment) {
                    fwrite($file, "- " . $attachment['name'] . "\n");
                }
            }
            fclose($file);
            // write to userlog
            $conn = dbconnect();
            $query = "INSERT INTO `scan4_userlog`(`user`, `source`,`action1`,`action2`) VALUES ('system','cronjob','Email send to " . $client . "', 'App errors from " . date('Y_m_d', strtotime('-1 day')) . " error count: " . $data_count[$client] . "')";
            mysqli_query($conn, $query);
            // close conn
            mysqli_close($conn);
        }
    }
}


function bugreport_excel($data, $excelname, $client)
{
    // Create a new instance of the Spreadsheet class
    $spreadsheet = new Spreadsheet();

    // Select the active worksheet
    $worksheet = $spreadsheet->getActiveSheet();

    // Set the heading for the worksheet
    $worksheet->setCellValue('A1', $excelname);

    // Merge the cells in the first row
    $worksheet->mergeCells('A1:E1');

    // Set the text alignment and font style for the heading
    $headingStyle = [
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        ],
        'font' => [
            'bold' => true,
            'size' => 14,
        ],
    ];
    $worksheet->getStyle('A1')->applyFromArray($headingStyle);

    // Set the column headings in the second row of the worksheet
    $columnHeadings = ['HomeID', 'Address', 'Date & Time', 'Comment', 'Screenshot'];
    foreach ($columnHeadings as $index => $heading) {
        $cell = chr(65 + $index) . '2'; // A2, B2, C2, D2
        $worksheet->setCellValue($cell, $heading);
        $headerStyle = [
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
        ];
        $worksheet->getStyle($cell)->applyFromArray($headerStyle);
    }

    // Loop through the array of data and write each row of data to the worksheet
    $rowIndex = 3; // Start writing from row 3
    foreach ($data as $row) {
        $worksheet->setCellValue('A' . $rowIndex, $row['bug_homeid']);
        $worksheet->setCellValue('B' . $rowIndex, $row['bug_address']);
        $worksheet->setCellValue('C' . $rowIndex, $row['datetime']);
        $worksheet->setCellValue('D' . $rowIndex, $row['bug_comment']);
        $worksheet->setCellValue('E' . $rowIndex, $row['file_name']);
        $rowIndex++;
    }

    // Auto-size the columns to fit the content
    foreach (range('A', 'D') as $column) {
        $worksheet->getColumnDimension($column)->setAutoSize(true);
    }



    $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/view/includes/mail/files/' . date('Y_m') . '/' . $client . '/';
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    // Save the spreadsheet to a file
    $writer = new Xlsx($spreadsheet);
    $filename = $target_dir . $excelname . '.xlsx';
    $writer->save($filename);
    return ($filename);
}


function bugreport_zip($data, $zipname, $client)
{
    $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/view/includes/mail/files/' . date('Y_m') . '/' . $client . '/';
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    // Create a new zip archive
    $zip = new ZipArchive();
    $zipFile = $target_dir . $zipname . '.zip';

    // Open the zip archive
    if ($zip->open($zipFile, ZipArchive::CREATE) !== TRUE) {
        return null;
    }

    // Add each file to the zip archive
    foreach ($data as $item) {
        $filePath = $item['file_target'];
        $fileName = basename($filePath);
        if (file_exists($filePath)) {
            $zip->addFile($filePath, $fileName);
        }
    }

    // Close the zip archive
    $zip->close();

    // Check if the zip file exists
    if (!file_exists($zipFile)) {
        return null;
    }

    return $zipFile;
}


function bugreport_fetch()
{

    $date = date('Y-m-d', strtotime('-1 day'));
    $conn = dbconnect();
    //$query = "SELECT * FROM `scan4_bug_reports` WHERE datetime LIKE '" . $date . "%' ORDER BY `datetime` DESC";
    $query = "SELECT b.*, h.carrier, h.client
    FROM scan4_bug_reports b
    LEFT JOIN scan4_homes h ON b.bug_homeid = h.homeid
    WHERE b.datetime LIKE '" . $date . "%'
    ORDER BY b.datetime DESC;
    ";
    //echo $query;
    if ($result = $conn->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $result->free_result();
    }
    mysqli_close($conn);

    return $data;
}


function getalllatlong()
{
    $successCount = 0;
    $failCount = 0;
    $batchSize = 200;

    $start_time = microtime(true);
    $nowtime = date('H:i:s');
    echo "<br> $nowtime <br>";

    $conn = dbconnect();
    $query = "SELECT * FROM `scan4_homes` WHERE (lat IS NULL OR lat = '') ORDER BY `scan4_homes`.`id` DESC LIMIT 500;";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $allData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $dataBatches = array_chunk($allData, $batchSize);

    foreach ($dataBatches as $data) {

        $mh = curl_multi_init();
        $curl_array = [];
        $response_data = [];

        foreach ($data as $row) {
            $city = trim($row['city']);
            $wordsToRemove = ['MDU', 'W2', 'W3', 'W4', 'Dgpha'];
            $city = str_replace($wordsToRemove, '', $city);

            $address = trim($row['street']) . ' ' . trim($row['streetnumber']) . trim($row['streetnumberadd']);
            $address .= ',' . $row['plz'] . ' ' . $city;

            $url_address = urlencode($address);
            // Die URL muss auf Ihren Nominatim-Server zeigen
            $url = 'https://routing.scan4-gmbh.com/nominatim/search.php?format=json&addressdetails=1&q=' . $url_address;
            echo $url . '</br>';

            // Initialisieren einer neuen cURL-Session für diese Adresse
            $curl_array[$row['homeid']] = curl_init($url);
            curl_setopt($curl_array[$row['homeid']], CURLOPT_RETURNTRANSFER, true);

            // Die nächsten beiden Zeilen nur einfügen, wenn Ihr Nominatim-Server keine gültigen SSL-Zertifikate hat
            // curl_setopt($curl_array[$row['homeid']], CURLOPT_SSL_VERIFYHOST, 0);
            // curl_setopt($curl_array[$row['homeid']], CURLOPT_SSL_VERIFYPEER, 0);

            // Fügen Sie den cURL-Handle zum Multi-Handle hinzu
            curl_multi_add_handle($mh, $curl_array[$row['homeid']]);
        }

        $running = NULL;
        do {
            curl_multi_exec($mh, $running);
        } while ($running > 0);

        // Collect responses
        foreach ($data as $row) {
            $response = curl_multi_getcontent($curl_array[$row['homeid']]);
            if (empty($response)) {
                echo "cURL error for homeid " . $row['homeid'] . ": " . curl_error($curl_array[$row['homeid']]) . "<br>";
                continue;
            }
            $response_data[$row['homeid']] = json_decode($response, true);
            curl_multi_remove_handle($mh, $curl_array[$row['homeid']]);
        }

        curl_multi_close($mh);

        // Process responses and update the database
        foreach ($response_data as $homeid => $response) {
            // Überprüfen Sie, ob sowohl 'lat' als auch 'lon' in der Antwort vorhanden sind
            if (isset($response[0]['lat']) && isset($response[0]['lon'])) {
                $latitude = $response[0]['lat'];
                $longitude = $response[0]['lon'];
                $update_query = "UPDATE `scan4_homes` SET `lat` = '$latitude', `lon` = '$longitude' WHERE `homeid` = '$homeid'";
                $conn->query($update_query);

                $successCount++;  // Erfolgszähler erhöhen
            } else {
                $failCount++;  // Fehlschlagzähler erhöhen

                // Ausgabe der fehlgeschlagenen Adresse und etwaiger Fehlerinformationen zur Fehlerbehebung
                echo "Failed address for homeid $homeid<br>";
                if (empty($response)) {
                    echo "cURL error for homeid $homeid: " . curl_error($curl_array[$homeid]) . "<br>";
                } else {
                    echo "Response for homeid $homeid: " . json_encode($response) . "<br>";
                }
            }
        }
    }

    $nowtime = date('H:i:s');
    $end_time = microtime(true);
    echo "<br> $nowtime <br>";
    $execution_time = ($end_time - $start_time);
    echo "<br> Execution time: " . $execution_time . " seconds <br>";
    echo "<br> Total successful geocoding requests: " . $successCount . "<br>";
    echo "Total failed geocoding requests: " . $failCount . "<br>";
}




function fixclient()
{
    $conn = dbconnect();
    // Fetch data from scan4_citylist
    $sql = "SELECT `city`, `client` FROM `scan4_citylist`";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Output data of each row
        while ($row = $result->fetch_assoc()) {
            $city = $row["city"];
            $client = $row["client"];

            // Update the scan4_homes table with the fetched data
            $update_query = "UPDATE `scan4_homes` SET `client` = '$client' WHERE `city` = '$city'";
            if ($conn->query($update_query) === TRUE) {
                //echo "Record updated successfully";
            } else {
                //echo "Error updating record: " . $conn->error;
            }
        }
    } else {
        echo "0 results";
    }
    $conn->close();
}



function updateRelations()
{ // this will crosscheck all phonenumbers in the db to see relations and write them into relations column
    // Get the start time
    $start_time = microtime(true);

    // Get the mysqli connection object
    $conn = dbconnect();

    // Phones part
    $query = "SELECT homeid, phone1, phone2, phone3 FROM scan4_homes";
    $result = mysqli_query($conn, $query);

    $phoneNumbers = array();
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            if (!empty($row['phone1']) && strlen($row['phone1']) >= 4) {
                $phoneNumbers[$row['phone1']][] = $row['homeid'];
            }
            if (!empty($row['phone2']) && strlen($row['phone2']) >= 4) {
                $phoneNumbers[$row['phone2']][] = $row['homeid'];
            }
            if (!empty($row['phone3']) && strlen($row['phone3']) >= 4) {
                $phoneNumbers[$row['phone3']][] = $row['homeid'];
            }
        }
    }

    foreach ($phoneNumbers as $homeids) {
        if (count($homeids) > 1) { // If more than one homeid has the same phone number
            foreach ($homeids as $homeid) {
                $unique_homeids = array_unique($homeids); // Remove any duplicate homeids
                if (($key = array_search($homeid, $unique_homeids)) !== false) {
                    unset($unique_homeids[$key]); // Remove current homeid
                }
                $relations = implode(';', $unique_homeids); // Create a string of all the homeids separated by a semicolon
                $query = "UPDATE scan4_homes SET relations = '$relations' WHERE homeid = '$homeid'";
                mysqli_query($conn, $query);
            }
        }
    }



    // Addresses part
    $query = "SELECT homeid, city, street, streetnumber, streetnumberadd FROM scan4_homes";
    $result = mysqli_query($conn, $query);

    $addresses = array();
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $address = $row['city'] . $row['street'] . $row['streetnumber'] . $row['streetnumberadd'];
            $addresses[$address][] = $row['homeid'];
        }
    }


    foreach ($addresses as $homeids) {
        if (count($homeids) > 1) { // If more than one homeid has the same address
            foreach ($homeids as $homeid) {
                // Retrieve existing relations
                $getRelations = mysqli_query($conn, "SELECT relations FROM scan4_homes WHERE homeid = '$homeid'");
                $row = mysqli_fetch_assoc($getRelations);
                $existingRelations = explode(';', $row['relations']);

                // Remove duplicates and current homeid
                $unique_homeids = array_unique($homeids);
                if (($key = array_search($homeid, $unique_homeids)) !== false) {
                    unset($unique_homeids[$key]); // Remove current homeid
                }

                // Merge, de-duplicate and sort
                $allRelations = array_unique(array_merge($existingRelations, $unique_homeids));
                sort($allRelations, SORT_NUMERIC);

                // Update with the new unique relations
                $relations = implode(';', $allRelations);
                $query = "UPDATE scan4_homes SET relations = '$relations' WHERE homeid = '$homeid'";
                mysqli_query($conn, $query);
            }
        }
    }

    // Get the end time
    $end_time = microtime(true);

    // Calculate the script execution time
    $execution_time = $end_time - $start_time;

    echo "Function updateRelations() took " . number_format($execution_time, 2) . " seconds to execute.";
}


function removeMusterPPL()
{
    $conn = dbconnect();
    $stmt = $conn->prepare("DELETE FROM `scan4_homes` WHERE `lastname` LIKE ?");
    $param = 'Muster%';
    $stmt->bind_param('s', $param);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Records deleted successfully.";
    } else {
        echo "Error deleting records: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
}


//getalllatlongcity();
function latlongcalendar() // parse all addresses with geoserver to get lat long. capped to 2k to cut execution time
{
    $start_time = microtime(true); // start time
    $nowtime = date('H:i:s');
    echo "<br> $nowtime <br>";
    $data = array();
    $conn = dbconnect();
    $query = "SELECT * FROM `calendar_events` WHERE lat IS NULL OR lat = '' ORDER BY id DESC LIMIT 1000";
    echo $query;
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    echo count($data);


    $index = 0;
    foreach ($data as $row) {
        $index++;
        if ($index > 1000) {
            break;
        }

        $address = $row['location'];
        $id = $row['id'];
        echo "$address <br>";

        $geocode = get_geocode($address); //returns array of lat long
        echo print_r($geocode) . '<br>';
        if ($geocode !== false) {
            $latitude = $geocode['latitude'];
            $longitude = $geocode['longitude'];
            $zipCode = $geocode['zip_code'];
            echo $zipCode . '<br>';

            // Update the database with the retrieved latitude and longitude
            $update_query = "UPDATE `calendar_events` SET `lat` = '$latitude', `lon` = '$longitude' WHERE `id` = '$id'";


            $conn->query($update_query);
        }
    }

    $nowtime = date('H:i:s');
    $end_time = microtime(true); // end time
    echo "<br> $nowtime <br>";
    $execution_time = ($end_time - $start_time); // execution time
    echo "<br> Execution time: " . $execution_time . " seconds <br>";
}



function switchOverdues()
{
    $conn = dbconnect();
    $stmt = $conn->prepare("UPDATE `scan4_homes` SET scan4_status = ? WHERE scan4_hbgdate < ? AND scan4_status = ?");
    $new_status = 'OVERDUE'; // Replace 'NEW_STATUS' with the new status you want to set
    $date_past = date('Y-m-d', strtotime('-1 days'));
    $old_status = 'PLANNED';
    $stmt->bind_param("sss", $new_status, $date_past, $old_status);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

