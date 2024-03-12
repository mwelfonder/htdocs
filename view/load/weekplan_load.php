<?php


require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';



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
} else if ($func === "change_appointments") {
    $user = $_POST['user'];
    $data = $_POST['data'];
    change_appointments($user, $data);
} else if ($func === "load_weekplan_rev") {
    $week = $_POST['week'];
    load_weekplan_rev($week);
} else if ($func === "safe_distance") {
    $date = $_POST['date'];
    $user = $_POST['user'];
    $time = $_POST['time'];
    $location = $_POST['location'];
    delete_anfahrt($user, $date);
    safe_anfahrt($date, $user, $time, $location);
} else if ($func === "city_positions") {
    city_positions();
} else if ($func === "user_positions") {
    user_positions();
} else if ($func === "load_cluster") {
    load_cluster();
} else if ($func === "load_citynames") {
    load_citynames();
} else if ($func === "save_cluster") {
    $data = $_POST['data'];
    save_cluster($data);
} else if ($func === "save_img") {
    $data = $_POST['data'];
    $week = $_POST['week'];
    save_img($data, $week);
}


function save_img($imgData, $week)
{

    $imgData = str_replace('data:image/png;base64,', '', $imgData);
    $imgData = str_replace(' ', '+', $imgData);
    $imgData = base64_decode($imgData);
    $fileName = 'image_' . $week . '.png'; // generate a unique file name
    file_put_contents('/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/view/tmp/weekplan/' . $fileName, $imgData);
    echo 'Image saved successfully';
}


function save_cluster($data)
{
    global $currentuser;
    $date = date('Y-m-d H:i:s');
    $conn = dbconnect();
    $query = "UPDATE `settings` SET `setting_values`='$data',`setting_username`='$currentuser',`setting_created`='$date' WHERE setting_option = 'weekplan_cluster'";
    mysqli_query($conn, $query);
    mysqli_close($conn);
    echo print_r($data);
}

function load_citynames()
{
    $conn = dbconnect();
    $query = "SELECT * FROM `scan4_citylist` WHERE 1";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    mysqli_close($conn);
    echo json_encode($data);
}



function load_cluster()
{
    $conn = dbconnect();
    $query = "SELECT * FROM `settings` WHERE setting_option LIKE 'weekplan_cluster'";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row['setting_values'];
    }
    mysqli_close($conn);
    echo json_encode($data);
}


function safe_anfahrt($date, $user, $time, $location = null)
{

    $conn = dbconnect();
    $query = "SELECT COUNT(*) as count FROM calendar_events WHERE title LIKE '%Anfahrt%' AND start_time LIKE '$date%' AND user_name = '$user'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $conn->close();
    $count = $row['count'];
    global $currentuser;
    $conn = dbconnect();


    $nowdate = date('Y-m-d');
    $start_time = "08:00";

    list($hours, $minutes) = sscanf($time, "%d:%dh");
    $dateTime = new DateTime($start_time);
    $dateInterval = new DateInterval(sprintf("PT%dH%dM", $hours, $minutes));
    $dateTime->add($dateInterval);
    $end_time =  $dateTime->format('H:i');


    // Check if there is already an event with the given parameters
    if ($count > 0) {
        echo "There is already an event named 'anfahrt' for user '$user' on date '$date'";
        return null;
    } else {
        $event = uniqid();
        echo "There is no event named 'anfahrt' for user '$user' on date '$date'";
        $query = "INSERT INTO `calendar_events`(`title`, `start_time`, `end_time`, `all_day`, `description`, `location`, `color`, `creator`,  `created`, `user_id`, `user_name`, `event_id`) 
        VALUES ('🏎️ Anfahrt','$date 08:00','$date $end_time','false','Anfahrt zum zugewiesenem Projekt. Geschätzt: $time','$location','','$currentuser','$nowdate','','$user','$event')";
        mysqli_query($conn, $query);

        echo nextcloud_event($event);
    }

    $conn->close();
}




function delete_anfahrt($username, $date)
{
    echo "\input_date is $date\n";
    $monday = date('Y-m-d', strtotime('last monday', strtotime($date)));
    $sunday = date('Y-m-d', strtotime('next sunday', strtotime($date)));

    $conn = dbconnect();
    $query = "SELECT * FROM `calendar_events` WHERE user_name = '$username' AND title LIKE '%Anfahrt%' AND start_time BETWEEN '$monday 00:00:00' AND '$sunday 23:59:59' ORDER BY `id` DESC";
    echo "$query\n";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        echo print_r($row);


        $data = fetchUserDetails('username', $username, null);
        $userurl = $data->calendarhook;

        $event = $row['event_id'];

        echo "Delete entry for $username and event $event\n";

        $headers = array('Content-Type: text/calendar', 'charset=utf-8');
        $userpwd = 'sys:smallusdickus';

        $url = 'https://cloud2.scan4.pro/remote.php/dav/calendars/sys/' . $userurl . '/' . $event . '.ics';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $userpwd);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        echo '//remove ' . $event . ' from ' . $userurl . '//';
        echo '//remove response: ' . $result . '//';

        writetonextcloudlogfile($result, 'delete', $event);
        // Delete the entry from the database
        $delete_query = "DELETE FROM `calendar_events` WHERE `event_id` = '$event'";
        mysqli_query($conn, $delete_query);
        echo "\n $delete_query";
    }
    $conn->close();
}

function change_appointments($user, $data)
{
    global $currentuser;

    $conn = dbconnect();

    foreach ($data as $key) {
        echo nextcloud_move($key, $key, $user); // move old uid -> new uid. in this case both the same
        echo "$key\n";
        $query = "SELECT * FROM `scan4_hbg` WHERE uid = '$key'";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);
        $olduser = $row['hausbegeher'];
        $homeid = $row['homeid'];
        $hbg_date = $row['date'];
        $hbg_time = $row['time'];
        $hbg_datetime = "$hbg_date $hbg_time:00";

        $query = "UPDATE `scan4_hbg` SET `hausbegeher`='$user' WHERE uid = '$key'";
        mysqli_query($conn, $query);
        $query = "INSERT INTO `scan4_userlog`( `homeid`,`user`, `source`,`action1`, action2) VALUES ('" . $homeid . "','" . $currentuser . "','wochenplan','Hausbegeher von $olduser zu $user geändert', '" . $key . "')";
        mysqli_query($conn, $query);
        $query = "UPDATE `calendar_events` SET `user_name` = '$user' WHERE start_time = '$hbg_datetime' AND homeid = '$homeid' AND user_name = '$olduser';";
        mysqli_query($conn, $query);

    }


    // Close the database connection
    mysqli_close($conn);


    $conn->close();
}


function safe_weekplan($week, $data)
{
    global $currentuser;
    echo 'week: ' . $week . "\n";


    $version = get_latest_version();
    $version++;
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
        $query = "INSERT INTO wochenplan (week, creator, username, montag, dienstag, mittwoch, donnerstag, freitag, samstag,version) VALUES ('$week', '$currentuser', '$username', '$montag', '$dienstag', '$mittwoch', '$donnerstag', '$freitag', '$samstag', '$version')";
        mysqli_query($conn, $query);
    }

    // Close the database connection
    mysqli_close($conn);

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

function load_weekplan_rev($week = null)
{
    $conn = dbconnect();

    $query = "SELECT *
              FROM wochenplan
              WHERE week = '$week'
              ORDER BY `id` DESC;
              ";

    $data = array();
    if ($result = $conn->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $result->free_result();
    }
    $conn->close();



    // group the rows by hour, minute, and second
    $groups = array();
    foreach ($data as $row) {
        $datetime = new DateTime($row['created']);
        $time = $datetime->format('H:i:s');
        $date = $datetime->format('Y-m-d');
        $key = $date . ' ' . $time; // combine date and time into a composite key

        if (!isset($groups[$key])) {
            $groups[$key] = array();
        }
        $groups[$key][] = $row;
    }



    echo json_encode($groups);
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
            $street = $row['street'];
            $streetnumber = $row['streetnumber'];
            $streetnumberadd = $row['streetnumberadd'];
            $date = $row['date'];
            $time = $row['time'];
            $homeid = $row['homeid'];
            $uid = $row['uid'];
            $fname = $row['firstname'];
            $lname = $row['lastname'];

            if (!array_key_exists($row['hausbegeher'], $listhausbgeher)) {
                $listhausbgeher[$row['hausbegeher']] = array();
            }

            if (isset($listhausbgeher[$row['hausbegeher']]['total']))
                $listhausbgeher[$row['hausbegeher']]['total']++;
            else
                $listhausbgeher[$row['hausbegeher']]['total'] = 1;

            if (isset($listhausbgeher[$row['hausbegeher']][$city]))
                $listhausbgeher[$row['hausbegeher']][$city]++;
            else
                $listhausbgeher[$row['hausbegeher']][$city] = 1;

            $listhausbgeher[$row['hausbegeher']][$date][$uid]['uid'] = $uid;
            $listhausbgeher[$row['hausbegeher']][$date][$uid]['time'] = $time;
            $listhausbgeher[$row['hausbegeher']][$date][$uid]['homeid'] = $homeid;
            $listhausbgeher[$row['hausbegeher']][$date][$uid]['city'] = $city;
            $listhausbgeher[$row['hausbegeher']][$date][$uid]['street'] = $street;
            $listhausbgeher[$row['hausbegeher']][$date][$uid]['streetnumber'] = $streetnumber;
            $listhausbgeher[$row['hausbegeher']][$date][$uid]['streetnumberadd'] = $streetnumberadd;
            $listhausbgeher[$row['hausbegeher']][$date][$uid]['fname'] = $fname;
            $listhausbgeher[$row['hausbegeher']][$date][$uid]['lname'] = $lname;
        }
        $result->free_result();
    }

    ksort($listhausbgeher);

    foreach ($listhausbgeher as $key => $val) {

        $query = "SELECT * FROM calendar_events WHERE title LIKE '%Anfahrt%' AND start_time LIKE '$monday%' AND user_name = '$key'";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);

        // echo "$key\n";
        if (is_array($row)) {
            //  echo print_r($row);
            $username = $row['user_name'];
            $date = substr($row['start_time'], 0, 10);
            $start_time = date('H:i', strtotime($row['start_time']));
            $end_time = date('H:i', strtotime($row['end_time']));

            $start_datetime = new DateTime($row['start_time']);
            $end_datetime = new DateTime($row['end_time']);
            $diff = $start_datetime->diff($end_datetime);
            $timediff = $diff->format('%h:%Ih'); // Output: 4:30h

            $listhausbgeher[$username][$date]['anfahrt']['start'] = $start_time;
            $listhausbgeher[$username][$date]['anfahrt']['end'] = $end_time;
            $listhausbgeher[$username][$date]['anfahrt']['time'] = $timediff;
        }

        // echo "\n";
    }
    $conn->close();

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


function get_latest_version()
{
    $conn = dbconnect();

    $query = "SELECT MAX(version) AS latest_version FROM wochenplan";

    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $latest_version = $row['latest_version'];
        $result->free_result();
        $conn->close();
        return $latest_version;
    } else {
        $result->free_result();
        $conn->close();
        return null;
    }
}






function city_positions()
{
    $list = array();
    $index = 0;
    $conn = dbconnect();
    $query = "SELECT * FROM scan4_citylist WHERE 1";
    if ($result = $conn->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $city = $row['city'];
            $lat = $row['lat'];
            $lon = $row['lon'];
            $client = $row['client'];
            $list[$index]['city'] = $city;
            $list[$index]['lat'] = $lat;
            $list[$index]['lon'] = $lon;
            $list[$index]['client'] = $client;
            $index++;
        }
        $result->free_result();
    }
    $conn->close();
    echo json_encode($list);
}


function user_positions()
{

    $list = array();
    // Get all users with permission level 6
    $users = fetchPermissionUsers(6); // 6 = Hausbegeher
    for ($i = 0; $i < count($users); $i++) {
        $username = echousername($users[$i]->user_id);
        $data = fetchUser($users[$i]->user_id);

        // Only include the user details if 'home' is not empty
        if (!empty($data->home)) {
            $list[$i]['user'] = $username;
            $list[$i]['home'] = $data->home;
            $list[$i]['lat'] = $data->lat;
            $list[$i]['lon'] = $data->lon;
        }
    }

    echo json_encode($list);
}

function users_getlatlon()
{
    $list = array();
    $index = 0;
    $conn = dbconnectsys();
    $query = "SELECT home FROM users WHERE home IS NOT NULL AND home != ''";
    if ($result = $conn->query($query)) {
        while ($row = $result->fetch_assoc()) {
            echo print_r($row) . '<br>';
            $city = $row['home'];
            $address = "$city, Deutschland";
            $res = get_lat_lng_from_address($address);
            $lat = $res['lat'];
            $lon = $res['lng'];

            // Update the 'lat' and 'lon' columns for the current row
            $update_query = "UPDATE users SET lat=$lat, lon=$lon WHERE home='{$row['home']}'";
            $conn->query($update_query);
        }
        $result->free_result();
    }
    $conn->close();
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


///

/*

  $listhausbgeher = array();
    $users = fetchPermissionUsers(6); // 6 = Hausbegeher
    for ($i = 0; $i < count($users); $i++) {
        $username = echousername($users[$i]->user_id);
        if (!in_array($username, $listhausbgeher)) {
            $listhausbgeher[] = $username;
        }
    }


    global $currentuser;
    $conn = dbconnect();







$nowdate = date('Y-m-d');
$start_time = "06:00";
$end_time =  "21:00";
$date = '2023-12-25';
$title = '1. Weihnachtstag (Feiertag)';
$desc = 'Weihnachten in Deutschland';

foreach ($listhausbgeher as $username) {


    
    
    $conn = dbconnect();



// ----------------------------- 
// This delets ALL events from
    $query = "SELECT * FROM `calendar_events` WHERE user_name = '$username' ORDER BY `id` DESC";
    echo "$query\n";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {

        $data = fetchUserDetails('username', $username, null);
        $userurl = $data->calendarhook;

        $event = $row['event_id'];

        echo "Delete entry for $username and event $event\n";

        $headers = array('Content-Type: text/calendar', 'charset=utf-8');
        $userpwd = 'sys:smallusdickus';

        $url = 'https://cloud2.scan4.pro/remote.php/dav/calendars/sys/' . $userurl . '/' . $event . '.ics';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $userpwd);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        echo '//remove ' . $event . ' from ' . $userurl . '//';
        echo '//remove response: ' . $result . '//';

        writetonextcloudlogfile($result, 'delete', $event);
    }











    
// ----------------------------- 
// This generates Events

    $query = "SELECT * FROM `calendar_events` WHERE `title` LIKE '$title' AND user_name = '$username' ORDER BY `id` DESC";
    $result = $conn->query($query);

    // Check if any rows were returned
    if ($result->num_rows > 0) {
        echo "Query exists for $username\n";
    } else {
        echo "Query does not exist for $username\n";
        $event = uniqid();
        $query = "INSERT INTO `calendar_events`(`title`, `start_time`, `end_time`, `all_day`, `description`, `location`, `color`, `creator`,  `created`, `user_id`, `user_name`, `event_id`) 
    VALUES ('$title','$date $start_time','$date $end_time','false','$desc','Deutschland','','$currentuser','$nowdate','','$username','$event')";
        mysqli_query($conn, $query);

        echo nextcloud_event($event);
    }



$conn->close();

*/