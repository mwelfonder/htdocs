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


echo '$sql;;';
// connect to the MySQL database using mysqli
$conn = dbconnect(); // assuming that dbconnect() returns a valid mysqli connection object

// Retrieve the JSON data from the request body
$json = file_get_contents('php://input');
$data = json_decode($json, true);


// Loop through the heatmap data and save it in the database
foreach ($data as $point) {
	$x = $point['x'];
	$y = $point['y'];
	$time = $point['time'];
	$source = $point['source'];
	$instance = $point['id'];

	$sql = "INSERT INTO heatmap_data (x, y, time,user,source,instance) VALUES ('" . $x . "', '" . $y . "', '" . $time . "','" . $currentuser . "','" . $source . "','" . $instance . "')";
	if ($conn->query($sql) !== TRUE) {
		echo "Error: " . $sql . "<br>" . $conn->error;
	}
}

$conn->close();
