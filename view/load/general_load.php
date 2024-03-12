<?php

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}

$currentuser = $user->data()->username;


include "../../view/includes/functions.php";

date_default_timezone_set('Europe/Berlin');
if (!isset($_POST['func'])) {
	die();
}

$func = $_POST['func'];
if ($func === "track_clickevent") {
	$source = $_POST['source'];
	$event = $_POST['event'];
	$action = $_POST['action'];
	$info = $_POST['info'];
	track_clickevent($source, $action, $event, $info);
}


function track_clickevent($source, $action, $event, $info)
{
	global $currentuser;
	$conn = dbconnect();
	$date = date('Y-m-d H:i:s');
	$query = "INSERT INTO `scan4_userlog` SET `datetime`='" . $date . "',homeid = '', `user`='" . $currentuser . "',source = '" . $source . "', `action1`='" . $event . "', `action2`='" . $action . "' , `action3`='" . $info . "'";
	// update userlog
	mysqli_query($conn, $query);
	$conn->close();
}




