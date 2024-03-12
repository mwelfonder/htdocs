<?php


require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';

if (!securePage($_SERVER['PHP_SELF'])) {
    die();
}

$logged_in = $user->data();
$currentuser = $logged_in->username;


include_once "../../view/includes/functions.php";

date_default_timezone_set('Europe/Berlin');
if (!isset($_POST['func'])) {
    die();
}

$func = $_POST['func'];
if ($func === "load_hbg_data") {
    $date = $_POST['date'];
    load_hbg_data($date);
} else if ($func === "load_distance") {
    userdist();
} else if ($func === "load_weekplan") {
    $week = $_POST['week'];
    load_weekplan($week);
} else if ($func === "safe_weekplan") {
    $week = $_POST['week'];
    $data = json_decode($_POST['data'], true);
    safe_weekplan($week, $data);
} else if ($func === "geo_loadDuct") {
    geo_loadDuct();
}



function geo_loadDuct()
{

    $dir = "/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/uploads/geojson";
    $files = array_diff(scandir($dir), array('..', '.'));
    echo json_encode($files);
}


function safe_weekplan($week  = null, $data)
{
    global $currentuser;
    echo 'week: ' . $week . "\n";


    $conn = dbconnect();

    foreach ($data as $name => $days) {
        // Reset the values for each day for the current user
        $montag = NULL;
        $dienstag = NULL;
        $mittwoch = NULL;
        $donnerstag = NULL;
        $freitag = NULL;
        $samstag = NULL;

        foreach ($days as $dayIndex => $day) {
            $week = $week; // The week value that you already have
            $username = $name;
            $dayname = getDayName($dayIndex);
            $dayValue = implode(";", $day['spans']);

            // Set the value for the corresponding day of the week
            if ($dayname == 'Montag') {
                $montag = empty($day['spans']) ? NULL : $dayValue;
            } elseif ($dayname == 'Dienstag') {
                $dienstag = empty($day['spans']) ? NULL : $dayValue;
            } elseif ($dayname == 'Mittwoch') {
                $mittwoch = empty($day['spans']) ? NULL : $dayValue;
            } elseif ($dayname == 'Donnerstag') {
                $donnerstag = empty($day['spans']) ? NULL : $dayValue;
            } elseif ($dayname == 'Freitag') {
                $freitag = empty($day['spans']) ? NULL : $dayValue;
            } elseif ($dayname == 'Samstag') {
                $samstag = empty($day['spans']) ? NULL : $dayValue;
            }
        }

        // Construct the SQL query string and execute the query
        $query = "INSERT INTO wochenplan (week, creator, username, montag, dienstag, mittwoch, donnerstag, freitag, samstag) VALUES ('$week', '$currentuser', '$username', '$montag', '$dienstag', '$mittwoch', '$donnerstag', '$freitag', '$samstag')";
        mysqli_query($conn, $query);
    }

    // Close the database connection
    mysqli_close($conn);








    //$query = "INSERT INTO wochenplan (week, creator, username, montag, dienstag, mittwoch, donnerstag, freitag, samstag) VALUES ('$week', '$creator', '$username', '$values[0]', '$values[1]', '$values[2]', '$values[3]', '$values[4]', '$values[5]')";
    //echo "$query\n";
    //mysqli_query($conn, $query);


    // close the connection
    $conn->close();


    //echo print_r($data);




    $conn->close();
}


function load_weekplan($week = null)
{
    $conn = dbconnect();


    $query = "SELECT *
              FROM wochenplan
              WHERE week = '$week' AND id IN (
                  SELECT MAX(id)
                  FROM wochenplan
                  WHERE week = '$week'
                  GROUP BY username
              )";

    $data = array();
    if ($result = $conn->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $result->free_result();
    }
    $conn->close();

    echo json_encode($data);
}





function load_hbg_data($date = null)
{

    $listhausbgeher = array();
    $conn = dbconnect();
    if ($date == null) {
        $today = date('Y-m-d');
        $monday = date('Y-m-d', strtotime('monday this week', strtotime($today)));
        $sunday = date('Y-m-d', strtotime('sunday this week', strtotime($today)));
        $query = "SELECT * FROM scan4_hbg INNER JOIN scan4_homes ON scan4_hbg.homeid=scan4_homes.homeid WHERE scan4_hbg.status = 'PLANNED' AND scan4_hbg.date BETWEEN '$monday' AND '$sunday' ORDER BY scan4_hbg.date ASC, scan4_hbg.time ASC";
    } else { // pass the date from js like this '2023-W12'
        $year = substr($date, 0, 4);
        $week = substr($date, 6);

        $monday = date('Y-m-d', strtotime($year . "W" . $week . "1"));
        $sunday = date('Y-m-d', strtotime($year . "W" . $week . "7"));
        $query = "SELECT * FROM scan4_hbg INNER JOIN scan4_homes ON scan4_hbg.homeid=scan4_homes.homeid WHERE scan4_hbg.status = 'PLANNED' AND scan4_hbg.date BETWEEN '$monday' AND '$sunday' ORDER BY scan4_hbg.date ASC, scan4_hbg.time ASC";
    }

    //$query = "SELECT * FROM scan4_hbg WHERE date BETWEEN '2023-01-02' AND '$today' AND status = 'PLANNED';";
    if ($result = $conn->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
            $city = $row['city'];
            $date = $row['date'];
            $time = $row['time'];

            if (!array_key_exists($row['hausbegeher'], $listhausbgeher)) {
                $listhausbgeher[$row['hausbegeher']] = array();
            }
            $listhausbgeher[$row['hausbegeher']]['total']++;
            $listhausbgeher[$row['hausbegeher']][$city]++;
            $listhausbgeher[$row['hausbegeher']][$date][] = $time . ' ' . $city;
        }
        $result->free_result();
    }
    $conn->close();
    ksort($listhausbgeher);


    echo json_encode($listhausbgeher);
}








function userdist()
{
    // Get all users with permission level 6
    $users = fetchPermissionUsers(6); // 6 = Hausbegeher
    for ($i = 0; $i < count($users); $i++) {
        $username = echousername($users[$i]->user_id);
        $data = fetchUser($users[$i]->user_id);

        $user_dest[$i]['user'] = $username;
        $user_dest[$i]['home'] = $data->home;
    }



    $index = 0;
    $conn = dbconnect();
    $mycityys = array();

    $query = "SELECT * FROM scan4_citylist INNER JOIN scan4_homes ON scan4_citylist.city=scan4_homes.city WHERE 1";
    if ($result = $conn->query($query)) {
        while ($row = $result->fetch_assoc()) {
            if (!in_array($row['city'], $mycityys)) {
                $mycityys[] = $row['city'];
                $mycitys[$index]['city'] = $row['city'];
                $mycitys[$index]['plz'] = $row['plz'];
                $index++;
            }
        }
        $result->free_result();
    }


    foreach ($user_dest as &$item) { // Notice the '&' to make $item a reference

        //echo $item['user'] . ' lives in ' . $item['home'] . '<br>';
        if (empty($item['home'])) {
            continue; // Skip this iteration if $item['home'] is empty
        }
        $citiesWithDistances = array();


        for ($i = 0; $i < count($mycitys); $i++) {
            $city = $mycitys[$i]['city'];
            $plz = $mycitys[$i]['plz'];
            $home = $item['home'];

            $destination = $plz . ' ' . $city;

            $query = "SELECT * FROM distance WHERE (loc_from = '$home' AND loc_to = '$destination') OR (loc_to = '$home' AND loc_from = '$destination') ";
            // echo '<br>';
            // echo $query;
            //echo '<br>';
            if ($result = $conn->query($query)) {
                if ($row = $result->fetch_assoc()) {
                    //echo 'DISTANCE FOUND ===================================================';
                    $distance = $row['distance_km'];
                    $duration = $row['distance_time'];
                    $duration_seconds = strtotime("1970-01-01 $duration UTC") - strtotime("1970-01-01 00:00:00 UTC");
                    $duration_hours = floor($duration_seconds / 3600);
                    $duration_minutes = floor(($duration_seconds / 60) % 60);

                    // Format the duration as "hours:minutesh"
                    $duration = sprintf("%d:%02dh", $duration_hours, $duration_minutes);
                } else {
                    $distanceInfo = getDistance($home, $destination);
                    $distance = $distanceInfo['distance'];
                    $duration = $distanceInfo['duration'];

                    $date = date('Y-m-d H:i:s');
                    $insertQuery = "INSERT INTO distance (created,loc_from, loc_to, distance_km, distance_time) VALUES ('$date','$home', '$destination', $distance, '$duration')";
                    // echo $insertQuery . '<br>';
                    if ($conn->query($insertQuery)) {
                        echo 'DISTANCE INSERTED ===================================================';
                    } else {
                        //   echo 'INSERT FAILED ===================================================';
                    }
                }
                $result->free_result();
            }

            // Add the city and distance to the $citiesWithDistances array
            $citiesWithDistances[$destination] = array('distance' => $distance, 'duration' => $duration);
        }

        // Update the $item array with the cities and distances
        $item['citys'] = $citiesWithDistances;
    }

    $conn->close();
    echo json_encode($user_dest);


    //echo '<pre>';
    //echo print_r($user_dest);
    //echo '</pre>';
}

function getDistance($home, $destination)
{
    $apiKey = 'AIzaSyBsm1lDPd_kC4PUv-Rr86j5VMafeZbkkrU';
    $url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins=" . urlencode($home) . "&destinations=" . urlencode($destination) . "&key=" . $apiKey;
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    $distance = $data['rows'][0]['elements'][0]['distance']['value'] / 1000; // Convert meters to kilometers
    $duration = $data['rows'][0]['elements'][0]['duration']['text'];
    return array('distance' => $distance, 'duration' => $duration);
}



function getDayName($dayIndex)
{
    $days = ['Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
    return $days[$dayIndex];
}
