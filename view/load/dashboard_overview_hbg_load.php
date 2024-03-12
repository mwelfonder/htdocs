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
if ($func === "load_username") {
    $user = $_POST['user'];
    load_username($user);
} else if ($func === "load_overview_newcustomers") {
    $kw = $_POST['kw'];
    $city = $_POST['city'];
    load_overview_newcustomers($city, $kw);
}





function load_username($user = null)
{
    list($all, $listhausbgeher) = fetch_all();
    //echo print_r($data);

    //echo print_r($listhausbgeher);

    $data = array();

    $currentDate = new DateTime();
    $dayCount = $currentDate->diff(date_create_from_format('Y-m-d', date('Y') . '-01-01'))->days + 1;

    // Iterate through each day until the current date
    for ($day = 16; $day <= $dayCount; $day++) {

        // Get the date of the current day in "Y-m-d" format
        $date = date('Y-m-d', strtotime('1st January +' . ($day - 1) . ' days'));
      
        // Initialize the appointment count for this day to 0
        $count = 0;

        // Initialize the hausbegeher flags for this day to zero
        foreach ($listhausbgeher as $hausbegeher => $value) {
            $data[$hausbegeher]['total'][$date] = 0;
            $data[$hausbegeher]['done'][$date] = 0;
            $data[$hausbegeher]['kdnotthere'][$date] = 0;
            $data[$hausbegeher]['imnotthere'][$date] = 0;
            $data[$hausbegeher]['impossible'][$date] = 0;
            $data[$hausbegeher]['open'][$date] = 0;
        }

        // Iterate through each appointment and check if it falls on this day
        foreach ($all as $item) {

            if ($item['date'] == $date) {
                $hausbegeher = $item['hausbegeher'];
                $data[$hausbegeher]['total'][$date]++;
                if ($item['appt_status'] == 'done') {
                    $data[$hausbegeher]['done'][$date]++;
                } elseif ($item['appt_status'] == 'Kunde war nicht da') {
                    $data[$hausbegeher]['kdnotthere'][$date]++;
                } elseif ($item['appt_status'] == 'Ich war nicht da') {
                    $data[$hausbegeher]['imnotthere'][$date]++;
                } elseif ($item['appt_status'] == 'HBG nicht durchführbar') {
                    $data[$hausbegeher]['impossible'][$date]++;
                } elseif ($item['appt_status'] == '') {
                    $data[$hausbegeher]['open'][$date]++;
                }
                $count++;
            }
        }


        // Store the appointment count for this day
        $appointmentCount[$date] = $count;
    }





    echo json_encode($data);
    // return $data_total['CarstenFloeter'];
}





function fetch_all()
{
    $listhausbgeher = array();
    $today = date('Y-m-d');
    $conn = dbconnect();
    $query = "SELECT * FROM scan4_hbg WHERE date BETWEEN '2023-01-02' AND '$today' AND status = 'PLANNED';";
    if ($result = $conn->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
            if (!array_key_exists($row['hausbegeher'], $listhausbgeher)) {
                $listhausbgeher[$row['hausbegeher']] = 0;
            }
        }
        $result->free_result();
    }
    $conn->close();
    $sort = array_column($data, 'date');
    // Sort the $counts array in descending order by 'done' value
    array_multisort($sort, SORT_DESC);

    return array($data, $listhausbgeher);
}
