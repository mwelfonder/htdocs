<?php 



date_default_timezone_set('Europe/Berlin');
if (!isset($_POST['func']) && !isset($_GET['func'])) {
	echo 'Access denied';
	die();
}




if (isset($_GET['func']) && $_GET['func'] == 'loadOutput') {
	$file_path = '/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/logfiles/output.txt';
	if (file_exists($file_path)) {
		$lines = file($file_path);
		$last100Lines = array_slice($lines, -100);
		echo implode("", $last100Lines);
	} else {
		echo "File not found";
	}
}
