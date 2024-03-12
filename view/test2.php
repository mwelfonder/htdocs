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



ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(100000);



getalllatlong();

function getalllatlong()
{
    $successCount = 0;
    $failCount = 0;
    $batchSize = 1000;

    $start_time = microtime(true);
    $nowtime = date('H:i:s');
    echo "<br> $nowtime <br>";

    $conn = dbconnect();
    $query = "SELECT * FROM `scan4_homes` WHERE (lat IS NULL OR lat = '') ORDER BY `scan4_homes`.`id` DESC LIMIT 1000;";
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

            $address = trim($row['street']) . ' ' . trim($row['streetnumberadd']);
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

    $conn->close();
    $nowtime = date('H:i:s');
    $end_time = microtime(true);
    echo "<br> $nowtime <br>";
    $execution_time = ($end_time - $start_time);
    echo "<br> Execution time: " . $execution_time . " seconds <br>";
    echo "<br> Total successful geocoding requests: " . $successCount . "<br>";
    echo "Total failed geocoding requests: " . $failCount . "<br>";
}
