<?php


require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';

if (!securePage($_SERVER['PHP_SELF'])) {
    die();
}
include_once "../../view/includes/functions.php";

date_default_timezone_set('Europe/Berlin');
if (!isset($_POST['func'])) {
    die();
}

$func = $_POST['func'];
if ($func === "load_alldata") {
    load_alldata();
} else if ($func === "load_overview_newcustomers") {
    $kw = $_POST['kw'];
    $city = $_POST['city'];
    load_overview_newcustomers($city, $kw);
}





function load_alldata()
{

    $listhausbgeher = array();
    $conn = dbconnect();

    $today = date('Y-m-d');
    $query = "SELECT * FROM scan4_hbg INNER JOIN scan4_homes ON scan4_hbg.homeid=scan4_homes.homeid WHERE scan4_hbg.status = 'PLANNED' AND scan4_hbg.appt_status != 'Ich war nicht da' ORDER BY scan4_hbg.hausbegeher ASC,scan4_hbg.date ASC, scan4_hbg.time ASC";


    $index = 0;
    //$query = "SELECT * FROM scan4_hbg WHERE date BETWEEN '2023-01-02' AND '$today' AND status = 'PLANNED';";
    if ($result = $conn->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
            $city = $row['city'];
            $street = $row['street'];
            $streetnumber = $row['streetnumber'];
            $streetnumberadd = $row['streetnumberadd'];
            $date = $row['date'];
            $time = $row['time'];
            $fname = $row['firstname'];
            $lname = $row['lastname'];
            $homeid = $row['homeid'];
            $lat = $row['lat'];
            $lon = $row['lon'];


            if (!array_key_exists($row['hausbegeher'], $listhausbgeher)) {
                $listhausbgeher[$row['hausbegeher']] = array();
                $index = 0;
            }
            $listhausbgeher[$row['hausbegeher']]['total']++;
            $listhausbgeher[$row['hausbegeher']][$city]++;
            $listhausbgeher[$row['hausbegeher']][$date][$index]['time'] = $time;
            $listhausbgeher[$row['hausbegeher']][$date][$index]['city'] = $city;
            $listhausbgeher[$row['hausbegeher']][$date][$index]['street'] = $street;
            $listhausbgeher[$row['hausbegeher']][$date][$index]['streetnumber'] = $streetnumber;
            $listhausbgeher[$row['hausbegeher']][$date][$index]['streetnumberadd'] = $streetnumberadd;
            $listhausbgeher[$row['hausbegeher']][$date][$index]['fname'] = $fname;
            $listhausbgeher[$row['hausbegeher']][$date][$index]['lname'] = $lname;
            $listhausbgeher[$row['hausbegeher']][$date][$index]['homeid'] = $homeid;
            $listhausbgeher[$row['hausbegeher']][$date][$index]['lat'] = $lat;
            $listhausbgeher[$row['hausbegeher']][$date][$index]['lon'] = $lon;


            $index++;
        }
        $result->free_result();
    }
    $conn->close();
    $sort = array_column($data, 'date');
    // Sort the $counts array in descending order by 'done' value
    ksort($listhausbgeher);

    //return array($data, $listhausbgeher);
    echo json_encode($listhausbgeher);
    return $listhausbgeher;
}



function get_distance_between_coordinates($start_lat, $start_lng, $end_lat, $end_lng)
{
    $url = "http://49.12.77.77:8080/ors/v2/directions/driving-car?&start={$start_lng},{$start_lat}&end={$end_lng},{$end_lat}";
    echo $url;

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

    // Execute cURL session and get the response
    $response = curl_exec($ch);

    // Close cURL session
    curl_close($ch);

    $response_data = json_decode($response, true);
    if (isset($response_data['routes'][0]['segments'][0]['distance'])) {
        return $response_data['routes'][0]['segments'][0]['distance'];
    }
    return null;
}






function get_lat_lng_from_address($address)
{
    // Google Maps Geocoding API endpoint
    $endpoint = "https://maps.googleapis.com/maps/api/geocode/json";

    // API key (optional)
    $apiKey = "AIzaSyBsm1lDPd_kC4PUv-Rr86j5VMafeZbkkrU";

    // Build URL with query parameters
    $url = $endpoint . "?address=" . urlencode($address) . "&key=" . $apiKey;

    // Send HTTP request to Google Maps Geocoding API
    $response = file_get_contents($url);

    // Parse JSON response
    $result = json_decode($response);

    // Check if the API request was successful
    if ($result->status == "OK") {
        // Retrieve latitude and longitude from response
        $lat = $result->results[0]->geometry->location->lat;
        $lng = $result->results[0]->geometry->location->lng;

        // Return latitude and longitude as an array
        return array("lat" => $lat, "lng" => $lng);
    } else {
        // Handle API error
        return false;
    }
}
