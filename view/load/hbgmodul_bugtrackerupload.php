<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
include "../../view/includes/functions.php";

$logged_in = $user->data();
$currentuser = $logged_in->username;





if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_FILES['bug_file'])) {
        $bugFile = $_FILES['bug_file'];
        $bugAddress = $_POST['bug_address'];
        $bugHomeid = $_POST['bug_homeid'];
        $bugComment = $_POST['bug_comment'];



        // var_dump($_POST['bug_infos']);
        //var_dump(json_decode($_POST['bug_infos'], true));

        // PHP code in process.php
        $bug_infos = json_decode($_POST['bug_infos'], true);
        //echo print_r($bug_infos);
        $bug_lat = $bug_infos['lat'];
        $bug_long = $bug_infos['long'];
        $device_type = $bug_infos['deviceType'];
        $os_version = $bug_infos['osVersion'];
        $browser_name = $bug_infos['browserName'];
        $browser_version = $bug_infos['browserVersion'];



        $fileName = date('Y_m_d_H_i_s') . '_' . $bugHomeid . '_'  . $bugFile['name'];
        $fileSize = $bugFile['size'];
        $fileTmpName = $bugFile['tmp_name'];
        $fileType = $bugFile['type'];
        $target_dir = '/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/uploads/bugtracker/' . date('Y_m') . '/' . date('Y_m_d') . '/';
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $target_file = $target_dir .  $fileName;
        $upload_ok = move_uploaded_file($_FILES['bug_file']['tmp_name'], $target_file);

        if ($upload_ok) {
            $conn = dbconnect();
            $sql = "INSERT INTO scan4_bug_reports (source,user, bug_lat, bug_long, device_type, os_version, browser_name, browser_version, file_name, file_size, file_tmp_name, bug_address, bug_comment,bug_homeid,file_target) 
        VALUES ('hbgmodul_bugreport','$currentuser', '$bug_lat', '$bug_long', '$device_type', '$os_version', '$browser_name', '$browser_version', '$fileName', '$fileSize', '$fileTmpName', '$bugAddress', '$bugComment','$bugHomeid','$target_file')";
            $result = mysqli_query($conn, $sql);
            $query = "INSERT INTO `scan4_userlog`(homeid,`user`, `source`,`action1`) VALUES ('" . $homeid . "','" . $currentuser . "','hbgmodul_bugreport','App bug reported')";
            mysqli_query($conn, $query);
            // close conn
            mysqli_close($conn);
        } else {
            //echo 'File upload failed!';
        }
    } else {
       // echo 'No file uploaded!';
    }
} else {
    //echo 'Invalid request!';
}
