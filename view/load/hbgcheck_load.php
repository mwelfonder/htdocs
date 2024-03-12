<?php

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}

$logged_in = $user->data();
$currentuser = $logged_in->username;



include "../../view/includes/functions.php";

date_default_timezone_set('Europe/Berlin');
if (!isset($_POST['func'])) {
	die();
}









$func = $_POST['func'];
if ($func === "load_checknext") {
	load_checknext();
} else if ($func === "safe_hbgcheck") {
	$homeid = $_POST['homeid'];
	$comment = $_POST['comment'];
	$result = $_POST['reason'];
	$file = $_POST['file'];
	safe_hbgcheck($homeid, $result, $comment, $file);
} else if ($func === "load_openhomeid") {
	$city = $_POST['city'];
	load_openhomeid($city);
} else if ($func === "load_timeline") {
	$homeid = $_POST['homeid'];
	load_entry_timeline($homeid);
	load_entry_relations($homeid);
}





function safe_hbgcheck($homeid, $reason, $comment, $file)
{
	global $currentuser;
	if ($file === '') $timestamp = '';
	$date = date('Y-m-d H:i:s');
	if ($reason === 'excel' || $reason === 'screenshot') {
		$status = 'DONE CLOUD';
	} else if ($reason === 'wrong') {
		$status = 'WRONG';
	} else if ($reason === 'missing') {
		$status = 'MISSING';
	} else if ($reason === 'Falsche Adresse' || $reason === 'Keine HBG - KD verweigert HBG' || $reason === 'Kein Gebäude' || $reason === 'Keine HBG - Technisch nicht möglich') {
		$status = 'STOPPED';
	} else if ($reason === 'Kunde nicht da' || $reason === 'Begeher nicht da' || $reason === 'Unzureichender Grund') {
		$status = 'OPEN';
	}

	$conn = dbconnect();
	$query = "INSERT IGNORE INTO `scan4_hbgcheck`(`homeid`,`user`, `status`, `review`, `reason`, `comment`, `file`,`datetime`) VALUES ('" . $homeid . "','" . $currentuser . "','" . $status . "',NULL,'" . $reason . "','" . $comment . "','" . $file . "', '" . $date . "')";
	mysqli_query($conn, $query);
	$query = "UPDATE `scan4_homes` SET `scan4_status`='" . $status . "', scan4_comment = '" . $comment . "' WHERE homeid='" . $homeid . "'";
	mysqli_query($conn, $query);
	$conn->close();
	echo $query;
}



function load_checknext()
{
	$conn = dbconnect();
	/*
	$date = date('Y-m-d');
	$date = date('Y-m-d', strtotime($date . ' -2 days'));

	$query = "SELECT * FROM `scan4_hbg` WHERE `date` LIKE '" . $date . "' AND (reviewed IS NULL OR reviewed = '0') ORDER BY `scan4_hbg`.`id` DESC";
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_row()) {
			$entry = $row;
			break;
		}
		$result->free_result();
	}
	*/
//	$query = "SELECT scan4_homes.*,scan4_citylist.city_id FROM `scan4_homes` INNER JOIN scan4_citylist ON scan4_citylist.city=scan4_homes.city WHERE scan4_homes.scan4_status LIKE 'OVERDUE' ORDER BY scan4_homes.id DESC;";



	$query = "SELECT scan4_homes.*,scan4_citylist.city_id FROM `scan4_homes` INNER JOIN scan4_citylist ON scan4_citylist.city=scan4_homes.city WHERE scan4_homes.hbg_status != 'DONE' AND scan4_homes.scan4_status LIKE 'OVERDUE' ORDER BY scan4_homes.id DESC;";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$conn->close();
	//echo $query;
	if ($result !== '') {
		echo json_encode($row);
	} else {
		echo 'empty';
	}


	//echo $query;

}
