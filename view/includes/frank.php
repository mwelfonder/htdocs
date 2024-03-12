<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/view/includes/functions.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/view/includes/cron/cron_mail.php';

date_default_timezone_set('Europe/Berlin');


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use scan4\Mailer;

function createCSVForRoutesWithLatLon() {
    $conn = dbconnect();

    // Basisadresse Koordinaten
    $baseLat = 51.68840354784571;
    $baseLon = 9.560577528209445;
    $profile = "profile=car";

    // Datumsgrenze
    $startDate = "2023-05-26";
    $endDate = date("Y-m-d");

    $query = "SELECT s4h.lat, s4h.lon, s4h.street, s4h.streetnumber, s4h.streetnumberadd, s4h.city, s4h.plz, s4b.date 
              FROM scan4_hbg s4b
              INNER JOIN scan4_homes s4h ON s4h.homeid = s4b.homeid
              WHERE s4b.hausbegeher = 'FrankKoenitz' AND s4b.date BETWEEN ? AND ? 
              ORDER BY s4b.date, s4h.street LIMIT 100";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();

    $result = $stmt->get_result();
    $addresses = $result->fetch_all(MYSQLI_ASSOC);

    $csvFileName = "routes.csv";
    $csvFile = fopen($csvFileName, "w");
    fputcsv($csvFile, ["Date", "Start Address", "End Address", "Distance (km)", "Time (hours)"]);

    $previousLat = $baseLat;
    $previousLon = $baseLon;

    foreach ($addresses as $address) {
        $currentLat = $address['lat'];
        $currentLon = $address['lon'];

        $routeURL = "https://services.scan4-gmbh.de/route?point={$previousLat},{$previousLon}&point={$currentLat},{$currentLon}&{$profile}";
        
        echo "URL: " . $routeURL . "\n";  // Ausgabe der URL

        $routeData = file_get_contents($routeURL);
        $routeJson = json_decode($routeData, true);

        if (isset($routeJson['paths']) && !empty($routeJson['paths'])) {
            $distance = $routeJson['paths'][0]['distance'] / 1000;  // in Kilometern
            $time = $routeJson['paths'][0]['time'] / 3600000;  // in Stunden
        } else {
            $distance = 0;
            $time = 0;
            echo "Fehler beim Abrufen der Daten für die Route: " . $routeURL . "\n";
        }

        $startAddress = ($previousLat == $baseLat) ? "Base Address" : $address['street'] . " " . $address['streetnumber'] . $address['streetnumberadd'] . ", " . $address['plz'] . " " . $address['city'];
        $endAddress = $address['street'] . " " . $address['streetnumber'] . $address['streetnumberadd'] . ", " . $address['plz'] . " " . $address['city'];

        fputcsv($csvFile, [$address['date'], $startAddress, $endAddress, $distance, $time]);

        $previousLat = $currentLat;
        $previousLon = $currentLon;
    }

    fclose($csvFile);

    echo "CSV-Datei erfolgreich erstellt!";
}

createCSVForRoutesWithLatLon();
