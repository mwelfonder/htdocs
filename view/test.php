<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';

if (!(hasPerm([2, 3]))) {
    die();
}



echo 'test.php 187 <br>';
getlatestCommentscan4();


// FTP configuration
$ftpHost     = 'u375865.your-storagebox.de';
$ftpUsername = 'u375865-sub1';
$ftpPassword = 'GA73iXeMQt8NjG9e';

// File to be uploaded
$localFile = '/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/uploads/uploads.zip'; // Path to the zipped file on your current server
$remoteFile = '/uploads.zip'; // Path where you want to store the file on the remote server

// Establish connection
$ftpConnection = ftp_connect($ftpHost) or die("Could not connect to $ftpHost");

// Login to FTP server
if (@ftp_login($ftpConnection, $ftpUsername, $ftpPassword)) {
    echo "Connected as $ftpUsername@$ftpHost\n";

    // Enable passive mode
    ftp_pasv($ftpConnection, true);

    // Upload the file
    if (ftp_put($ftpConnection, $remoteFile, $localFile, FTP_BINARY)) {
        echo "Successfully uploaded $localFile\n";
    } else {
        echo "Error uploading $localFile\n";
    }

    // Close the connection
    ftp_close($ftpConnection);
} else {
    echo "Could not log in as $ftpUsername\n";
}



die();
//getalllatlong();

function getalllatlong()
{
    $successCount = 0;
    $failCount = 0;
    $batchSize = 200;

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
            $wordsToRemove = array('MDU', 'W2', 'W3', 'W4', 'Dgpha');
            $city = str_replace($wordsToRemove, '', $city);

            $address = trim($row['street']) . ' ' . trim($row['streetnumber']) . trim($row['streetnumberadd']);
            $address .= ',' . $row['plz'] . ' ' . $city;

            $url_address = urlencode($address);
            $url = 'https://services.scan4-gmbh.de/api?q=' . $url_address;

            $curl_array[$row['homeid']] = curl_init($url);
            curl_setopt($curl_array[$row['homeid']], CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl_array[$row['homeid']], CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl_array[$row['homeid']], CURLOPT_SSL_VERIFYPEER, 0);
            curl_multi_add_handle($mh, $curl_array[$row['homeid']]);
        }

        $running = NULL;
        do {
            curl_multi_exec($mh, $running);
        } while ($running > 0);

        // Collect responses
        foreach ($data as $row) {
            $response = curl_multi_getcontent($curl_array[$row['homeid']]);
            $response_data[$row['homeid']] = json_decode($response, true);
            curl_multi_remove_handle($mh, $curl_array[$row['homeid']]);
        }

        curl_multi_close($mh);

        // Process responses and update the database
        foreach ($response_data as $homeid => $response) {
            if (isset($response['features'][0]['geometry']['coordinates'])) {
                $latitude = $response['features'][0]['geometry']['coordinates'][1];
                $longitude = $response['features'][0]['geometry']['coordinates'][0];
                $update_query = "UPDATE `scan4_homes` SET `lat` = '$latitude', `lon` = '$longitude' WHERE `homeid` = '$homeid'";
                $conn->query($update_query);

                $successCount++;  // Increment the success counter
            } else {
                $failCount++;  // Increment the failure counter

                // Print the failed address and any error information for troubleshooting
                echo "Failed address: " . array_search($curl_array[$homeid], $curl_array) . "<br>";
                if (empty($response)) {
                    echo "cURL error: " . curl_error($curl_array[$homeid]) . "<br>";
                } else {
                    echo "Response: " . json_encode($response) . "<br>";
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







function getlatestCommentscan4()
{
    $data = array();

    $conn = dbconnect();

    $query = "SELECT s.*
    FROM `scan4_calls` s
    JOIN (
        SELECT `homeid`, MAX(`id`) AS `max_id`
        FROM `scan4_calls`
        GROUP BY `homeid`
    ) t ON s.`homeid` = t.`homeid` AND s.`id` = t.`max_id`
    ORDER BY s.`id` ASC;
    ";
    if ($result = $conn->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $data[$row['homeid']][] = $row;
        }
        $result->free_result();
    }


    $query = "SELECT s.*
    FROM `scan4_hbg` s
    JOIN (
        SELECT `homeid`, MAX(`id`) AS `max_id`
        FROM `scan4_hbg`
        GROUP BY `homeid`
    ) t ON s.`homeid` = t.`homeid` AND s.`id` = t.`max_id`
    ORDER BY s.`id` DESC;
    ";
    if ($result = $conn->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $data[$row['homeid']][] = $row;
        }
        $result->free_result();
    }



    $query = "SELECT s.*
    FROM `scan4_hbgcheck` s
    JOIN (
        SELECT `homeid`, MAX(`id`) AS `max_id`
        FROM `scan4_hbgcheck`
        GROUP BY `homeid`
    ) t ON s.`homeid` = t.`homeid` AND s.`id` = t.`max_id`
    ORDER BY s.`id` DESC;
    ";
    if ($result = $conn->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $data[$row['homeid']][] = $row;
        }
        $result->free_result();
    }


    foreach ($data as $homeid => $entries) {
        echo "Home ID: $homeid\n";
        $call_date = '';
        $hbg_date = '';
        $comment1 = '';
        $comment2 = '';
        $comment = '';

        $flag_check = false;
        foreach ($entries as $entry) {
            // Access each field of the entry using $entry['fieldname']
            $homeid = $entry['homeid'];
            if (isset($entry['call_date']) && $entry['call_date'] !== '') {
                $call_date = $entry['call_date'];
                $comment1 = $entry['result'] . '::' . $entry['comment'];
            }
            if (isset($entry['date']) && $entry['date'] !== '') {
                $hbg_date = $entry['date'];
                if ($entry['appt_status'] === 'done') {
                    $comment2 = 'HBG durchgeführt';
                } elseif ($entry['appt_status'] === 'HBG nicht durchführbar') {
                    $flag_check = true;
                } else {
                    $comment2 = $entry['appt_status'] . '::' . $entry['appt_comment'];
                }
            }
            if ($flag_check === true)  $comment2 = $entry['reason'] . '::' . $entry['comment'];
        }
        if ($call_date > $hbg_date) {
            $newest_date = $call_date;
            $comment = $comment1;
        } else {
            $newest_date = $hbg_date;
            $comment = $comment2;
        }

        if ($comment1 === '::' && $comment2 !== '') {
            $comment = $comment2;
        } elseif ($comment2 === '::' && $comment1 !== '') {
            $comment = $comment1;
        }
        $newest_date = max($call_date, $hbg_date);
        $newcomment = "[" . $newest_date . ":" . $comment . "]";

        echo "ID: $homeid, call_date: $call_date, hbg_date: $hbg_date Newest Date: $newest_date<br>";
        echo $homeid . ' ' . $newcomment . '<br>';


        $update_query = "UPDATE scan4_homes SET scan4_comment = '$newcomment' WHERE `homeid` = '$homeid'";
        echo $update_query . '<br>';
        if ($conn->query($update_query) === TRUE) {
            // Query was successful
            $rows_affected = $conn->affected_rows;
            echo "Query updated $rows_affected rows<br>";
            echo $update_query . '<br>';
        } else {
            // Query failed
            echo "Error updating record: " . $conn->error . '<br>';
            echo $update_query . '<br>';
        }

        // echo "\n";
    }
    $conn->close();


    //echo '<pre>';
    //echo print_r($alldata);
    //echo '</pre>';
}
