






<script type="text/javascript" src="view/includes/js/app_dashboard_routing.js"></script>
<div class="row">
  <div class="col-2">
    <div class="navigationwrapper">

    </div>
  </div>
  <div class="col">
    <div id="map-wrapper">
      <div id="leaflet"></div>
    </div>
  </div>
</div>


<style>
  #map-wrapper {
    height: 90vh;
    width: 100%;
  }

  #leaflet {
    height: 100%;
    width: 100%;
  }

  .custom-marker-icon {
    background-color: #007bff;
    border-radius: 50%;
    text-align: center;
    color: white;
    font-size: 14px;
    font-weight: bold;
    width: 24px;
    height: 24px;
    line-height: 24px;
  }

  .users {
    list-style: none;
    padding: 0;
  }

  .user {
    cursor: pointer;
    user-select: none;
  }

  .months {
    display: none;
    list-style: none;
    padding: 0;
    margin-left: 20px;
    user-select: none;
  }

  .month {
    cursor: pointer;
  }

  .days {
    display: none;
    list-style: none;
    padding: 0;
    margin-left: 20px;
  }

  .day {
    cursor: pointer;
    user-select: none;
  }

  .dayslist {
    list-style: none;
    padding: 0;
    user-select: none;
  }
</style>




<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';

if (!(hasPerm([2, 3]))) {
  die();
}



include_once $_SERVER['DOCUMENT_ROOT'] . '/view/includes/functions.php';


?>





<?php


//die();
function fetch_all($date = null)
{

  $listhausbgeher = array();
  $conn = dbconnect();
  if ($date == null) {
    $today = date('Y-m-d');
    $monday = date('Y-m-d', strtotime('monday this week', strtotime($today)));
    $sunday = date('Y-m-d', strtotime('sunday this week', strtotime($today)));
    $query = "SELECT * FROM scan4_hbg INNER JOIN scan4_homes ON scan4_hbg.homeid=scan4_homes.homeid WHERE scan4_hbg.status = 'PLANNED' ORDER BY scan4_hbg.hausbegeher ASC,scan4_hbg.date ASC, scan4_hbg.time ASC";
  } else {
  }

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
  return $listhausbgeher;
}

$list = fetch_all();

$indexing = 0;
echo count($list);
$conn = dbconnect();
// Loop through each Hausbegeher in the $list array 


?>
<ul class="users">
  <?php

  use IntlDateFormatter as DateFormatter;

  foreach ($list as $user => $userData) {
    echo '<li class="user">' . $user . '</li>';
    $currentMonth = '';
    echo '<ul class="months">';
    foreach ($userData as $key => $val) {
      if (is_array($val)) {
        if (strtotime($key)) {
          $month = (new DateFormatter('de_DE', DateFormatter::NONE, DateFormatter::NONE, null, null, 'MMMM'))->format(strtotime($key));
          if ($month !== $currentMonth) {
            if ($currentMonth !== '') {
              echo '</ul></li>';
            }
            echo '<li class="month">' . $month . '</li><li class="days"><ul class="dayslist">';
            $currentMonth = $month;
          }
          echo '<li><input type="checkbox" class="day-checkbox"><span class="day">' . $key . '</span></li>';
        }
      }
    }
    echo '</ul></li></ul>';
  }
  ?>
</ul>



<?php


/*
echo '<br><pre>';
echo print_r($list);
echo '</pre>';
*/

$desiredUser = 'AngeloSchoen';
$desiredDate = '2023-02-03';

$output = [];

foreach ($list as $hausbegeher => $info) {
  if ($hausbegeher === $desiredUser) {
    foreach ($info as $key => $properties) {
      if ($key === $desiredDate) {
        $date = DateTime::createFromFormat('Y-m-d', $key);
        if ($date !== false) {
          if (!isset($output[$key])) {
            $output[$key] = [];
          }

          foreach ($properties as $index => $details) {
            $lat = $details['lat'];
            $lon = $details['lon'];
            if ($lat !== '' && $lat !== null) {
              $output[$key][] = [$lat, $lon];
            }
          }
        }
      }
    }
  }
}

header('Content-Type: application/json');
$jsonString = json_encode($output);
echo "stops: $jsonString<br>";



// Parse the JSON string
$waypoints = json_decode($jsonString, true);

// Create an array to store all the points
$points = [];

// Loop through each date and its associated waypoints to create the array of points
foreach ($waypoints as $date => $datePoints) {
  foreach ($datePoints as $point) {
    $points[] = implode(',', $point);
  }
}

// Create the API request URL with all the points
$url = 'https://49.12.77.77/route?point=' . implode('&point=', $points) . '&profile=car';

// Initialize the curl session
$ch = curl_init();

// Set the curl options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // disable SSL verification (for testing purposes only)
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);


// Make the API request and get the response
$response = curl_exec($ch);

// Check for any curl errors
if (curl_errno($ch)) {
  echo 'Curl error: ' . curl_error($ch);
}

// Close the curl session
curl_close($ch);


echo $response;
$jsondata = json_encode($response);



?>






<?php






foreach ($list as $hausbegeher => $info) {
  echo "<br>Hausbegeher: $hausbegeher <br>";
  echo '------------------------------ <br>';

  // Loop through each date and its properties
  foreach ($info as $key => $properties) {
    if ($key !== 'total') {

      $date = DateTime::createFromFormat('Y-m-d', $key);
      if ($date !== false) {
        echo $key . '</br>';
      }

      $previous_lat = null;
      $previous_lon = null;

      foreach ($properties as $index => $details) {
        $city = $details['city'];
        $street = $details['street'];
        $streetnumber = $details['streetnumber'];
        $streetnumberadd = $details['streetnumberadd'];
        $homeid = $details['homeid'];
        $lat = $details['lat'];
        $lon = $details['lon'];
        $address = "$street $streetnumber, $city, Deutschland";

        echo "$city ";
        echo "$street $streetnumber$streetnumberadd   $homeid   L:$lat L:$lon";
        echo "<br>";
        if ($lat === '' || $lat === null) {
          echo "no lat long<br>";
          $indexing++;
          if ($indexing < 800) {
            echo "checking: $address <br>";
            $location = get_lat_lng_from_address($address);
            if ($location) {
              echo "Latitude: " . $location["lat"] . "<br>";
              echo "Longitude: " . $location["lng"] . "<br>";
              $newlat = $location["lat"];
              $newlon = $location["lng"];
              $insertQuery = "UPDATE `scan4_homes` SET `lat`='$newlat',`lon`='$newlon' WHERE homeid = '$homeid'";
              mysqli_query($conn, $insertQuery);
              if ($conn->query($insertQuery)) {
                echo '<br>UPDATE INSERTED =================================================== <br>';
              } else {
                echo '<br>UPDATE FAILED =================================================== <br>';
              }
            } else {
              echo "<br>Geocoding failed <br>";
            }
          }
        } else {
        }
      }
    }
  }
}
$conn->close();




echo '<pre>';
echo print_r($list);
echo '</pre>';










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




?>