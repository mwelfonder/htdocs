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
if ($func === "load_overview_city") {
    $city = $_POST['city'];
    load_overview_city($city);
} else if ($func === "load_overview_newcustomers") {
    $kw = $_POST['kw'];
    $city = $_POST['city'];
    load_overview_newcustomers($city, $kw);
}





function load_username($user = null)
{

    $perm_telefonist = fetchPermissionUsers(5); // 5 = Telefonist

    for ($i = 0; $i < count($perm_telefonist); $i++) {
        $data = fetchUserDetails(null, null, $perm_telefonist[$i]->user_id);
        $userlist[] =  array('username' => $data->username, 'pic' => $data->profile_pic);
    }
    sort($userlist);
    for ($i = 0; $i < count($userlist); $i++) {
        $user = $userlist[$i]['username'];
        $stats[] = load_overview_a_callshorts('', '', 'this', $user, 'intern');
    }
    //$stats = load_overview_a_callshorts('this','all', 'intern');
    //echo print_r($stats);
    echo json_encode($stats);
}
