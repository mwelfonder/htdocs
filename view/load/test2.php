<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';

if (!(hasPerm([2, 3]))) {
    die();
}
 
echo "test2.php <br>";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use scan4\Mailer;


//setlocale(LC_TIME, 'de_DE.utf8');

date_default_timezone_set('Europe/Berlin');

include_once $_SERVER['DOCUMENT_ROOT'] . '/view/includes/functions.php';

$begeher = fetchPermissionUsers(6); // 6 = hausbegeher


//findprotokolls();

//customListImport();

//pendingMail();

//findprotokolls();

getalllatlong();

//reopenAndtimelineImport();

function reopenAndtimelineImport()
{
    $homeids = <<<IDS
   
    IDS;

    $homeidArray = explode(PHP_EOL, trim($homeids));

    $conn = dbconnect(); // Assuming dbconnect() will return a mysqli connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    foreach ($homeidArray as $homeid) {

        // Update the scan4_homes table with scan4_status = 'PENDING' and priority = 1
        if (!$conn->query("UPDATE scan4_homes SET scan4_status = 'OPEN', priority = 1 WHERE homeid = '$homeid'")) {
            throw new Exception("Error updating scan4_homes table: " . $conn->error);
        }

        // Insert a new row to scan4_calls
        $comment = $conn->real_escape_string('Wurde fälschlicherweise auf DONE angezeigt. HBG muss noch oder neu gemacht werden');
        if (!$conn->query("INSERT INTO scan4_calls (call_date, call_time, call_user, homeid, result, comment) VALUES (CURDATE(), CURTIME(), 'System', '$homeid', 'import', '$comment')")) {
            throw new Exception("Error inserting into scan4_calls table: " . $conn->error);
        }
    }
}



function customListImport()
{
    $homeids = <<<IDS
    ARP000013254001
    ARP011270102001
    IDS;

    $homeidArray = explode(PHP_EOL, trim($homeids));

    $conn = dbconnect(); // Assuming dbconnect() will return a mysqli connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $homeidsGreaterEqual5 = [];
    $homeidsLessThan5 = [];

    foreach ($homeidArray as $homeid) {
        $homeid = $conn->real_escape_string($homeid); // Escaping the string for safety

        // Count rows for the given homeid in scan4_calls table
        $result = $conn->query("SELECT COUNT(*) as count FROM scan4_calls WHERE homeid = '$homeid'");
        if (!$result) {
            throw new Exception("Error executing query: " . $conn->error);
        }

        $row = $result->fetch_assoc();
        $count = $row['count'];

        if ($count >= 5) {
            // Update the scan4_homes table with scan4_status = 'PENDING' and priority = 1
            if (!$conn->query("UPDATE scan4_homes SET scan4_status = 'PENDING', priority = 1 WHERE homeid = '$homeid'")) {
                throw new Exception("Error updating scan4_homes table: " . $conn->error);
            }
            $homeidsGreaterEqual5[] = $homeid;
        } else {
            // Update the scan4_homes table with scan4_status = 'OPEN' if scan4_status is not 'STOPPED'
            if (!$conn->query("UPDATE scan4_homes SET scan4_status = 'OPEN' WHERE homeid = '$homeid' AND scan4_status <> 'STOPPED'")) {
                throw new Exception("Error updating scan4_homes table: " . $conn->error);
            }
            $homeidsLessThan5[] = $homeid;
        }

        // Insert a new row to scan4_calls
        $comment = $conn->real_escape_string('Priorität - Anschluss bereits erfolgt, die Begehung muss weiterhin ZWINGEND durchgeführt werden.');
        if (!$conn->query("INSERT INTO scan4_calls (call_date, call_time, call_user, homeid, result, comment) VALUES (CURDATE(), CURTIME(), 'System', '$homeid', 'import', '$comment')")) {
            throw new Exception("Error inserting into scan4_calls table: " . $conn->error);
        }
    }

    // Print the lists:
    echo "Homeids >= 5 <br/>";
    foreach ($homeidsGreaterEqual5 as $id) {
        echo $id . "<br/>";
    }
    echo "\nHomeids < 5<br/>";
    foreach ($homeidsLessThan5 as $id) {
        echo $id . "<br/>";
    }
}


//getalllatlong();
function getalllatlong()
{
    $start_time = microtime(true);
    $nowtime = date('H:i:s');
    echo "<br>Starting at: $nowtime <br>";

    $data = array();
    $conn = dbconnect();
    $query = "SELECT * FROM `scan4_homes` WHERE (lat IS NULL OR lat = '') AND `city` LIKE 'Rondorf' LIMIT 1000;";
    $stmt = $conn->prepare($query);
    $stmt->execute();

    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    echo '<pre>';
    echo print_r($data);
    echo '</pre>';

    echo "<br> Number of records to process: " . count($data) . "<br>";

    $index = 0;
    foreach ($data as $row) {
        $index++;
        if ($index > 1000) {
            echo "<br>Reached 1000 records limit.<br>";
            break;
        }

        echo "<br>Processing record $index...<br>";

        $city = trim($row['city']);
        $wordsToRemove = array('MDU', 'W2', 'W3', 'W4', 'Dgpha');
        $city = str_replace($wordsToRemove, '', $city);

        $address = trim($row['street']) . ' ' . trim($row['streetnumber']) . trim($row['streetnumberadd']);
        $address .= ',' . $row['plz'] . ' ' . $city;
        $homeid = $row['homeid'];

        $geocode = get_geocode($address);
        $geocode = get_geocode($address);
        if ($geocode !== false) {
            $latitude = $geocode['latitude'];
            $longitude = $geocode['longitude'];

            // Echo out the actual coordinates
            echo "<br>Latitude for record $index: $latitude<br>";
            echo "Longitude for record $index: $longitude<br>";

            $update_query = "UPDATE `scan4_homes` SET `lat` = '$latitude', `lon` = '$longitude' WHERE `homeid` = '$homeid'";
            if ($conn->query($update_query) === TRUE) {
                echo "<br>Updated record $index successfully.<br>";
            } else {
                echo "<br>Error updating record $index: " . $conn->error . "<br>";
            }
        } else {
            echo "<br>No geocode found for record $index.<br>";
        }
    }
    echo "<br>Finished processing all records.<br>";

    $nowtime = date('H:i:s');
    $end_time = microtime(true);
    echo "<br>Ending at: $nowtime <br>";
    $execution_time = ($end_time - $start_time);
    echo "<br>Total execution time: " . $execution_time . " seconds<br>";
}


//getCitynamesfromHomeidList();
function getCitynamesfromHomeidList()
{
    $homeids = <<<IDS
AHE003236189001
AHE000326884001
ABW002926786001
ABW011364774001
IDS;


    $homeidArray = explode(PHP_EOL, $homeids);
    $homeidList = "'" . implode("','", $homeidArray) . "'";
    $conn = dbconnect();

    $query = "
SELECT 
    homeid, 
    city
FROM scan4_homes 
WHERE 
    homeid IN ($homeidList)
";

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
    $citys = $result->fetch_all(MYSQLI_ASSOC);

    // Create a dictionary for faster lookup
    $cityLookup = [];
    foreach ($citys as $entry) {
        $cityLookup[$entry['homeid']] = $entry['city'];
    }

    // Loop through the original list of home IDs and print their cities
    foreach ($homeidArray as $homeid) {
        echo isset($cityLookup[$homeid]) ? $cityLookup[$homeid] : "City not found for $homeid";
        echo '<br/>';
    }
}


function pendingMail()
{



    $homeids = <<<IDS
    ABW007505210001
    ABW005679498001
    ABW006739225001
    ABW009694652001
    ABW008205212001
    ABW002452164002
    ABW002903955001
    ABW001122084002
    ABW001730617001    
IDS;
    $homeidArray = explode(PHP_EOL, $homeids);
    $homeidList = "'" . implode("','", $homeidArray) . "'";







    date_default_timezone_set('Europe/Berlin');

    $conn = dbconnect();
    //__________________________________________//
    // select by searching a project

    /*
    $pendingCity = 'Ebsdorfergrund';
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
        AND LENGTH(email) > 4
        AND (contractstatus IS NULL OR contractstatus = '' OR contractstatus = 'UVS')
    LIMIT 1000;
    ";
    */

    //__________________________________________//
    // select by homeidlist

    $query = "
    SELECT 
        email, carrier, scan4_status, homeid, street, streetnumber, plz, city, streetnumberadd, firstname, lastname 
    FROM scan4_homes 
    WHERE 
        homeid IN ($homeidList)
        AND (emailsend IS NULL OR emailsend = '') 
        AND LENGTH(email) > 4
        AND (contractstatus IS NULL OR contractstatus = '' OR contractstatus = 'UVS')
    LIMIT 1000;
";


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


    echo 'Customers found: ' . count($customers);

    $flagfile = $_SERVER['DOCUMENT_ROOT'] . '/view/includes/mail/files/' . date('Y_m') . '/' .  'insyte/' .  date('Y_m_d') . '_flag_pendingmail.txt';

    // Überprüfung, ob die Datei bereits existiert
    if (file_exists($flagfile)) {
        echo 'Datei existiert bereits. Verlassen der Funktion.<br>';
        //return; // Frühzeitiges Verlassen der Funktion, da die Datei bereits existiert
    }

    $updateDateQuery = "UPDATE scan4_homes SET emailsend = NOW() WHERE homeid = ?";
    $updateDateStmt = $conn->prepare($updateDateQuery);


    $noMailCustomers = []; // Array für Kunden ohne E-Mail
    foreach ($customers as $customer) {
        if (empty($customer['email'])) {
            $noMailCustomers[] = $customer['email'];
        } else {
            echo "Verarbeitung der E-Mail für " . $customer['email'] . ".<br>";

            $mailto = $customer['email'];
            $mailtocc = 'kundenservice@scan4-gmbh.de, services@scan4-gmbh.de, hbg-support@insytedeutschland.de';

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
                                                      <p>Im Auftrag Ihres Glasfaseranbieters ' . $customer['carrier'] . ' und der zugehörigen Tiefbaufirma möchten wir gerne eine Hausbegehung zur Festlegung der Glasfasertrasse durchführen. Da wir Sie telefonisch leider nicht erreichen konnten, möchten wir Sie bitten, sich mit uns unter der Telefonnummer <a href="tel:+4915906723657" class="contact">+49 159 06723657</a> in Verbindung zu setzen, um einen passenden Termin zu koordinieren.</p>
                                                      <p>Wir sind zwischen 10:00 und 16:00 Uhr von Montag bis Freitag für Sie erreichbar.</p>
                                                      <p class="property-details">Es handelt sich um folgende Immobilie:<br>' . $customer['street'] . ' ' . $customer['streetnumber'] . $customer['streetnumberadd'] . '<br>' . $customer['plz'] . ', ' . $customer['city'] . '.</p>
                                
                                                      <p class="signature">Mit freundlichen Grüßen,<br>Team Scan4</p>
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


            //$mailto = 'a.keil@scan4-gmbh.de';
            //$mailtocc = 'b.getschmann@scan4-gmbh.de';
            // Send email with attachment
            $mailer = new Mailer();
            $success =  $mailer->send('info@scan4-gmbh.de', 'Scan4', $mailto, 'info@scan4-gmbh.de', 'Scan4 GmbH', $mailtocc, '', $customer['carrier'] . ' Hausbegehung - ' . $customer['street'] . ' ' . $customer['streetnumber'] . $customer['streetnumberadd'], $body_message);
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
                $updateDateStmt->bind_param("s", $customer['homeid']);
                if (!$updateDateStmt->execute()) {
                    echo "Fehler beim Datum-Update: " . $updateDateStmt->error;
                }
            }
        }
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



function findprotokolls()
{

    $data = <<<IDS
    06198AAA_13__1
    06198AAA_9__1
    06198AAA_9__2
    06198AAA_9__3
    06198AAA_9__4
    06198AAA_9__5
    06198ACF_8__1
    06198ACF_8__3
    06198ACF_8__4
    06198AAV_29__1
    06198AAL_11__1
    06198AFK_16__1
    06198AAH_18__1
    06198AAH_19__1
    06198AAH_30__1
    06198AAK_17__1
    06198AAL_10_A_1
    06198AAL_2__1
    06198ABY_1__1
    06198ABY_3__1
    06198ACA_25__1
    06198ACA_28__1
    06198ACA_6__1
    06198ACT_10__1
    06198ACT_9_D_1
    06198ADB_25_A_1
    06198AFK_18__1
    06198AFK_29__1
    06198AFK_51__1
    06198AFK_52__1
    06198AFK_55__1
    06198AFK_55__2
    06198AFQ_10_A_1
    06198AFQ_11__1
    06198AFQ_2__1
    06198AFQ_2__2
    06198AFQ_3__1
    06198AFT_10__1
    06198AFT_34__1
    06198AFT_52__1
    06198AFT_57__1
    06198AFT_58__1
    06198AGG_3__1
    06198AGU_1_C_1
    06198AGU_3__1
    06198AGU_9__1
    06198AHW_6__1
    06198AJT_1__1
    06198AAP_11__1
    06198AAV_14_A_1
    06198AAV_28__1
    06198ABG_10__1
    06198ABG_11__1
    06198ABI_9__1
    06198ACH_13__1
    06198ADJ_11__1
    06198ADJ_18__1
    06198ADJ_20__1
    06198ADS_12__1
    06198ADS_23__1
    06198ADT_32__1
    06198ADY_16__1
    06198ADY_19__1
    06198AEK_8__1
    06198AGD_6__1
    06198AID_1__1
    06198AID_11__1
    06198AID_18__1
    06198AIV_1__1
    06198AKP_4__1
    06198AAV_17_C_1
    06198AHM_5__1
    06198AFV_4__1
    06198AHM_9__1
    06198AGP_1__1
    
IDS;

    $ids = explode("\n", trim($data));

    $sourceDir = "/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/uploads/hbgprotokolle";
    $destDir = "/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/uploads/locatedfiles/";

    $foundIds = [];
    $notFoundIds = [];

    foreach ($ids as $id) {
        $id = trim($id);  // Ensure no unwanted whitespace
        $findCommand = "find " . escapeshellarg($sourceDir) . " -type f -iname " . escapeshellarg('*' . $id . '*') . " 2>&1"; // added 2>&1 to capture errors
        $filesFound = shell_exec($findCommand);

        if (strpos($filesFound, "Permission denied") !== false) {
            echo "Permission denied error for ID: $id<br/>";
            continue;
        }

        if (trim($filesFound) != "") {
            $foundIds[] = $id;

            $copyCommand = "echo " . escapeshellarg($filesFound) . " | xargs -I{} cp {} " . escapeshellarg($destDir);
            shell_exec($copyCommand);
        } else {
            $notFoundIds[] = $id;
        }
    }



    echo "Found " . count($foundIds) . ":<br/>";
    foreach ($foundIds as $id) {
        echo $id . "<br/>";
    }

    echo "Not Found " . count($notFoundIds) . ":<br/>";
    foreach ($notFoundIds as $id) {
        echo $id . "<br/>";
    }
}

?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/leaflet.markercluster.js" integrity="sha512-OFs3W4DIZ5ZkrDhBFtsCP6JXtMEDGmhl0QPlmWYBJay40TT1n3gt2Xuw8Pf/iezgW9CdabjkNChRqozl/YADmg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.Default.min.css" integrity="sha512-fYyZwU1wU0QWB4Yutd/Pvhy5J1oWAwFXun1pt+Bps04WSe4Aq6tyHlT4+MHSJhD8JlLfgLuC4CbCnX5KHSjyCg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js" integrity="sha512-ozq8xQKq6urvuU6jNgkfqAmT7jKN2XumbrX1JiB3TnF7tI48DPI4Gy1GXKD/V3EExgAs1V+pRO7vwtS1LHg0Gw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script src="https://cdn.jsdelivr.net/npm/@turf/turf/turf.min.js"></script>



<div id="leaflet" style="height: 90vh;">
    <div id="layer-control">
        <button id="calculate-length">Calculate Asphalt Length</button>

        <div id="layer-list"></div>
    </div>
</div>




<style>
    .custom-popup {
        background-color: #f9f9f9;
        padding: 10px;
        border-radius: 4px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    #layer-control {
        position: absolute;
        top: 10px;
        right: 10px;
        background: white;
        padding: 10px;
        z-index: 999;
    }

    .leaflet-popup-content-wrapper,
    .leaflet-popup-tip {
        background: unset;
        color: #333;
        box-shadow: none;
    }

    .polygonLabel {
        background: none !important;
        border: none !important;
        box-shadow: none !important;
        margin: 0 !important;
        padding: 0 !important;
        text-align: center;
        font-weight: bold;
        font-size: 14px;
    }
</style>



<script>
    window.map = L.map("leaflet", {
        doubleClickZoom: false
    }).setView([51.159328, 10.44594], 7);

    window.leaflet_maplayer = L.tileLayer(
        "https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
            subdomains: ["mt0", "mt1", "mt2", "mt3"],
            maxZoom: 19,
            preferCanvas: true,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        }
    ).addTo(map);

    window.layer_gstreet = L.tileLayer(
        "https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
            maxZoom: 19,
        }
    );
    window.layer_gsatelite = L.tileLayer(
        "https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}", {
            maxZoom: 19,
        }
    );


    var layers = {}; // This will hold references to each layer
    var colorPolyLine = "#ff7937"; // Blue color for lines
    var colorPolyPoint = "#3798ff"; // Green color for points
    var colorPolyPolygon = "#3798ff"; // Red color for polygons


    $.ajax({
        method: "POST",
        url: "view/load//test_load.php",
        data: {
            func: "geo_loadDuct"
        },
    }).done(function(response) {
        var files = JSON.parse(response);
        console.log('files', files);
        Object.values(files).forEach(function(file) {
            if (file.includes('Conduit') || file.includes('conduit') || file.includes('ADDRESSID') || file.includes('addressid') || file.includes('DP_Area')) {
                $.ajax({
                    url: "https://crm.scan4-gmbh.de/uploads/geojson/" + file,
                    method: "GET",
                    dataType: "json"
                }).done(function(data) {
                    var geoJsonData = typeof data === "string" ? JSON.parse(data) : data;
                    var layer = L.geoJSON(geoJsonData, {
                        style: function(feature) {
                            if (feature.geometry.type === 'Polygon' || feature.geometry.type === 'MultiPolygon') {
                                return {
                                    color: colorPolyPolygon
                                };
                            } else if (feature.geometry.type === 'LineString' || feature.geometry.type === 'MultiLineString') {
                                return {
                                    color: colorPolyLine
                                };
                            }
                        },
                        onEachFeature: function(feature, layer) {
                            if (feature.geometry.type === 'MultiPolygon') {
                                var bounds = layer.getBounds();
                                var center = bounds.getCenter();

                                var label = L.divIcon({
                                    className: 'polygonLabel',
                                    html: `<div>${feature.properties.name}</div>`,
                                    iconSize: [100, 36] // Adjust the size as necessary
                                });


                                L.marker(center, {
                                    icon: label,
                                    interactive: false
                                }).addTo(map);
                            } else {
                                if (feature.properties && feature.properties.name) {
                                    layer.bindPopup(function() {
                                        var popupContent = "<div style='font-weight: bold; font-size: 1.1em; margin-bottom: 10px;'>" + feature.properties.name + "</div>";
                                        delete feature.properties.name;
                                        Object.keys(feature.properties).forEach(function(key) {
                                            popupContent += "<div class='text-nowrap'><span style='font-weight: bold;'>" + key + ":</span> " + feature.properties[key] + "</div>";
                                        });
                                        return popupContent;
                                    }, {
                                        maxWidth: "auto",
                                        className: 'custom-popup'
                                    });
                                }
                            }
                        },
                        pointToLayer: function(feature, latlng) {
                            if (feature.geometry.type === 'Point') {
                                return L.circleMarker(latlng, {
                                    radius: 8,
                                    fillColor: colorPolyPoint,
                                    color: "#000",
                                    weight: 1,
                                    opacity: 1,
                                    fillOpacity: 0.8
                                });
                            }
                        }
                    }).addTo(map);

                    layers[file] = layer;

                    $('#layer-list').append(`
                <div>
                    <input type="checkbox" id="${file}" checked>
                    <label for="${file}">${file}</label>
                </div>
            `);

                    console.log(`Checkbox created for file: ${file}`);

                    $('#layer-list').on('change', `input:checkbox[id="${file}"]`, function() {
                        console.log(`Checkbox change detected for file: ${file}, checked status: ${this.checked}`);
                        if (this.checked) {
                            console.log(`Adding layer for file: ${file}`);
                            layers[file].addTo(map);
                        } else {
                            console.log(`Removing layer for file: ${file}`);
                            layers[file].remove();
                        }
                    });

                });
            }
        });
    }).fail(function(jqXHR, textStatus, errorThrown) {
        console.error("Error: " + textStatus, errorThrown);
    });


    function calculateAsphaltLength() {
        var totalAsphaltLength = 0;

        Object.values(layers).forEach(layer => {
            layer.eachLayer(featureLayer => {
                var feature = featureLayer.feature;
                if (feature.geometry.type === 'MultiLineString' && feature.properties.type_txt.includes('Asphalt')) {
                    var length = turf.length(feature, {
                        units: 'meters'
                    });
                    totalAsphaltLength += length;
                }
            });
        });

        console.log(`Total length of Asphalt lines: ${totalAsphaltLength.toFixed(2)} meters`);

    }

    document.getElementById('calculate-length').addEventListener('click', calculateAsphaltLength);
</script>