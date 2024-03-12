<?php

include "../../view/includes/functions.php";
include_once $_SERVER['DOCUMENT_ROOT'] . '/view/includes/lib/spreadsheet/spreadsheet.php';

date_default_timezone_set('Europe/Berlin');

include_once $_SERVER['DOCUMENT_ROOT'] . '/view/includes/load/mail_load.php';



//fix_nameissue();


$uid = '20230120T093913Z-HEHE003571-RN96297';


echo 'heelow';
//checkcurl();

//arrayarray();

$result = nextcloud_delete($uid);
echo $result;
echo '</br>';
echo $uid;

function arrayarray()
{	
	echo 'start';
	$homeid = 'AHE003077934001';
	$conn = dbconnect();
	//$a_timeline[] = array();
	$query = "SELECT * FROM `scan4_calls` WHERE `HomeId` = '" . $homeid . "'";
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_row()) {
			$a_timeline[] = array("date" => $row[2], "time" => substr($row[3], 0, -10), "user" => $row[4], "result" => $row[6],  "comment" => $row[7], "uid" => $row[8]);
		}
		$result->free_result();
	}


	$lenght = count($a_timeline);
	$lenght--;
	//echo "count:" . $lenght . '</br>';
	//echo print_r($a_timeline);

	$ticket = null;
	$query = "SELECT * FROM `scan4_tickets` WHERE `homeid` = '" . $homeid . "' AND status != 'closed'";
	if ($result = $conn->query($query)) {
		while ($row = mysqli_fetch_assoc($result)) {
			$ticket = $row;
		}
		$result->free_result();
	}
	$ticket_timeline = null;
	$query = "SELECT * FROM `scan4_userlog` WHERE source = 'Tickets' AND `homeid` = '" . $homeid . "'";
	if ($result = $conn->query($query)) {
		while ($row = mysqli_fetch_assoc($result)) {
			$ticket_timeline = $row;
			$s = explode(' ', $row['datetime']);
			$date = $s[0];
			$time = $s[1];
			$a_timeline[] = array("date" => $date, "time" => $time, "user" => $row['user'], "result" => 'Ticket',  "comment" => $row['action1'], "uid" => '');
		}
		$result->free_result();
	}
	arsort($a_timeline);


	echo $homeid;
	// array merge a_timeline and ticket_timeline
	//$a_timeline = array_merge($a_timeline, $ticket_timeline);
	echo '<pre>';
	print_r($a_timeline);
	echo '</pre>';

	$conn->close();
}

function nextcloud_deleteXX($uid)
{
	$conn = dbconnect();
	$query = "SELECT * FROM `scan4_hbg` WHERE uid = '" . $uid . "' ORDER BY `scan4_hbg`.`id` DESC LIMIT 1";
	$result = mysqli_query($conn, $query);
	$row = mysqli_fetch_assoc($result);
	$conn->close();

	$user = $row['hausbegeher'];
	$data = fetchUserDetails('username', $user, null);
	$userurl = $data->calendarhook;
	echo $userurl;
	echo '<br>';



	$headers = array('Content-Type: text/calendar', 'charset=utf-8');
	$userpwd = 'sys:smallusdickus';

	$url = 'https://cloud2.scan4-gmbh.de/remote.php/dav/calendars/sys/' . $userurl . '/' . $uid . '.ics';
	echo $url;
	echo '<br>';
	//$url = 'https://cloud2.scan4-gmbh.de/remote.php/dav/calendars/sys/ben-hbg_shared_by_Scan4%20GmbH/20230115T224831Z-HEHE001585-RN13148.ics';
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
	echo $result;
	//return $result . ' ' . $userurl;
}



function checkcurl()
{
	$headers = array('Content-Type: text/calendar', 'charset=utf-8');
	//$userpwd = 'bestadmin:mSMyCGIRTNPDjiqbJ5kt@HvK9BrsYzApW.2Z8lXEofV1UaOQ63';
	$userpwd = 'sys:smallusdickus';



	$url = 'https://cloud2.scan4-gmbh.de/remote.php/dav/calendars/sys/ben-hbg_shared_by_Scan4%20GmbH/20230114T174837Z-37539ACR_28__1-RN85098.ics';
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

	echo $result;
}




function nonbin()
{

	function nextcloud_change($uid, $reason)
	{
		$conn = dbconnect();
		$query = "SELECT scan4_hbg.*,scan4_homes.city,scan4_homes.plz,scan4_homes.street,scan4_homes.streetnumber,scan4_homes.streetnumberadd,scan4_homes.unit,scan4_homes.firstname,scan4_homes.lastname,scan4_homes.phone1,scan4_homes.phone2,scan4_homes.isporder,scan4_homes.anruf1,scan4_homes.anruf2,scan4_homes.anruf3,scan4_homes.anruf4,scan4_homes.anruf5 FROM `scan4_hbg` INNER JOIN scan4_homes ON scan4_hbg.homeid=scan4_homes.homeid WHERE scan4_hbg.uid LIKE '" . $uid . "' ORDER BY scan4_hbg.id DESC;";
		$result = $conn->query($query);
		//$row = $result->fetch_row();
		$row = mysqli_fetch_assoc($result);
		$conn->close();
		//echo $query;
		//	echo print_r($row);

		global $currentuser;
		$city = $row['city'];
		$comment = $row['comment'];
		if ($comment === "") $comment = "-";
		$team = $row['hausbegeher'];
		$hbgdate = $row['date'];
		$durration = str_replace(' min', '', $row['durration']);
		$user = $currentuser;
		$hbgdatetime = substr($hbgdate, -5);


		$hbgdate = str_replace(['-', 'T', ' '], '', $row['date']);
		$hbgdate = $hbgdate . 'T';
		$hbgtime = $row['time'];
		$endTime = strtotime("+{$durration} minutes", strtotime($hbgtime));
		$endTime = str_replace(':', '', date('H:i:s', $endTime));
		$tstart = $hbgdate . str_replace(':', '', $hbgtime) . '00';
		$tend = $hbgdate . str_replace(':', '', $endTime);

		$created = $row['created'];
		$cdate = mb_substr($created, 0, 10);
		$ctime = mb_substr($created, 11, 5);
		$cdate = date('d.m.y', strtotime($cdate));
		$created = $cdate . ' um ' . $ctime . ' Uhr';

		$data = fetchUserDetails('username', $team, null);
		$userurl = $data->calendarhook;

		$index = 1;
		$homeid = $row['homeid'];
		$street = $row['street'];
		$streetnr = $row['streetnumber'];
		$streetnr .= $row['streetnumberadd'];
		$unit = $row['unit'];
		$city = $row['city'];
		$plz = $row['plz'];
		$name = $row['lastname']; // Nachname
		$name .= ', ' . $row['firstname']; // Vorname
		if ($row['lastname'] !== "") {
			if ($comment !== "-") {
				$titel = $row['lastname'] . ' !Notiz [CRM]';
			} else {
				$titel = $row['lastname'] . ' [CRM]';
			}
		} else {
			if ($comment !== "-") {
				$titel = $row['firstname'] . ' !Notiz [CRM]';
			} else {
				$titel = $row['firstname'] . ' [CRM]';
			}
		}
		if ($reason === 'activate') $titel = '✅' . $titel;
		$phone1 =  str_replace('+49', '', $row['phone1']);
		$phone2 = str_replace('+49', '', $row['phone2']);
		$isp = $row[16];
		if (strlen($row['anruf1']) > 6) $index = 1;
		if (strlen($row['anruf2']) > 6) $index = 2;
		if (strlen($row['anruf3']) > 6) $index = 3;
		if (strlen($row['anruf4']) > 6) $index = 4;
		if (strlen($row['anruf5']) > 6) $index = 5;


		//$url = 'http://nextcloud.alphacc.de/remote.php/dav/calendars/bestadmin/benhbg/calc.ics';
		$headers = array('Content-Type: text/calendar', 'charset=utf-8');
		//$userpwd = 'bestadmin:mSMyCGIRTNPDjiqbJ5kt@HvK9BrsYzApW.2Z8lXEofV1UaOQ63';
		$userpwd = 'sys:smallusdickus';
		//$titel = 'Michelle Graf [CRM]';
		//$tstart = gmdate("Ymd\THis\Z", strtotime("+12 hours"));
		//$tend = gmdate("Ymd\THis\Z", strtotime("+13 hours"));
		$tstamp = gmdate("Ymd\THis\Z");


		//$location = 'Bahnhofstraße 22\, 34281 Gudensberg\, Deutschland';
		$location = $street . " " . $streetnr . '\, ' . $plz . ' ' . $city . '\, Deutschland';
		$description = 'Notiz: ' . $comment . '\nName: ' . $name . '\nTel.: +49' . $phone1 . '\nTel.: +49' . $phone2 . '\nUnit: ' . $unit . '\nHomeID: ' . $homeid . '\nErstellt am: ' . $created . ' \nErstellt von: ' . $user . '\nAnruf: #' . $index . '\nUID:' . $uid;

		$body = 'BEGIN:VCALENDAR
PRODID:CRMSCAN4_cURL_1
VERSION:2.0
CALSCALE:GREGORIAN
BEGIN:VEVENT
CREATED:' . $tstamp . '
DESCRIPTION:' . $description . '
DTEND;TZID=Europe/Berlin:' . $tend . '
DTSTAMP:' . $tstamp . '
DTSTART;TZID=Europe/Berlin:' . $tstart . '
LAST-MODIFIED:' . $tstamp . '
LOCATION:' . $location . '
SEQUENCE:2
STATUS:CONFIRMED
SUMMARY:' . $titel . '
TRANSP:OPAQUE
UID:' . $uid . '
END:VEVENT
END:VCALENDAR';

		$url = 'https://cloud2.scan4-gmbh.de/remote.php/dav/calendars/sys/' . $userurl . '/' . $uid . '.ics';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, $userpwd);
		//curl_setopt($ch, CURLOPT_PUT, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

		//Execute the request.
		$response = curl_exec($ch);
		curl_close($ch);
		//$response = str_replace(['\r','\n'], '', $response);
		if (preg_match('/message>(.*?)message/', $response, $match) == 1) {
			$errorhandle = str_replace('</s:', '', $match[1]);
		}
		echo $response;

		// ---------------------------------------------
		// write uid to hbg


		// Append to logfile
		$filename = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/twentytwentytwo/admin/nextcloudtermine.txt';
		$date = date('Y-m-d');
		$time = date('d-m-y H:i');
		$time = substr($time, -5);
		//$stringhandle = 'SET:' . $tstamp . '";"HomeID:' . $homeid . '";"HBGDate: ' . $date . '";"HBGTime:' . $time . '";"Durration:' . $durration . '";"Adress:' . $street . "_" . $streetnr . "_" . $plz . "_" . $city . '";"Name:' . $name  . '";"Phone:' . $phone1 . "_" . $phone2 . '";"ISP:' . $isp . '";"Creator:' . $user . '";"Worker:' . $team;
		//$stringhandle .= '";"UID:' . $uid . '";"Titel:' . $titel . '";"TStart:' . $tstart . '";"TEnd:' . $tend.'";"cURL:'.$errorhandle;
		$stringhandle = $tstamp . '";"' . $homeid . '";"' . $unit . '";"' . $date . '";"' . $time . '";"' . $durration . '";"' . $street . "_" . $streetnr . "_" . $plz . "_" . $city . '";"' . $name  . '";"' . $phone1 . "_" . $phone2 . '";"' . $isp . '";"' . $user . '";"' . $team;
		$stringhandle .= '";"' . $uid . '";"' . $titel . '";"' . $tstart . '";"' . $tend . '";"' . $errorhandle;

		$stringhandle .= "\n";

		if (is_writable($filename)) {
			if (!$fp = fopen($filename, 'a+b')) {
				//echo "Cannot open file ($filename)";
				exit;
			}
			if (fwrite($fp, $stringhandle) === FALSE) {
				//echo "Cannot write to file ($filename)";
				exit;
			}
			//echo "Success, wrote ($somecontent) to file ($filename)";
			fclose($fp);
		} else {
			// echo "The file $filename is not writable";
		}
	}
}






function fix_nameissue()
{
	$conn = dbconnect();

	$query = "SELECT lastname,homeid  FROM `scan4_homes` WHERE lastname LIKE '%. % '";
	if ($result = $conn->query($query)) {
		while ($row = mysqli_fetch_assoc($result)) {
			$array[] = $row;
		}
		$result->free_result();
	}


	// close connection

	for ($i = 0; $i < count($array); $i++) {
		echo $array[$i]['lastname'] . '<br>';
		$lastname = $array[$i]['lastname'];
		$homeid = $array[$i]['homeid'];
		$lastname = str_replace(' .', '', $lastname);
		echo $lastname . '<br>';
		$query = "UPDATE `scan4_homes` SET `lastname`='" . $lastname . "' WHERE homeid='" . $homeid . "'";
		// mysqli_query($conn, $query);
		echo $query . '<br>';
	}



	$conn->close();


	echo count($array);
	echo '<pre>';
	print_r($array);
	echo '</pre>';
}





function report_excel($city)
{
	$conn = dbconnect();
	$stats = array();


	$kw = date('W');
	$lastkw = $kw - 1;
	$startdate = date('Y-m-d', strtotime('monday this week'));
	$enddate = date('Y-m-d', strtotime('sunday this week'));
	$laststartdate = date('Y-m-d', strtotime('monday last week'));
	$lastenddate = date('Y-m-d', strtotime('sunday last week'));



	$city = 'Gudensberg';
	//$query = "SELECT COUNT(homeid) FROM `scan4_calls` WHERE call_user LIKE '%" . $user . "%' AND call_date BETWEEN '" . $startdate . "' AND '" . $enddate . "'";
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '%" . $city . "%'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$stats['0']['total'] = $row[0];

	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '%" . $city . "%' AND hbg_status = 'OPEN'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$stats['0']['nriopen'] = $row[0];

	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '%" . $city . "%' AND scan4_status = 'OPEN'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$stats['0']['sc4open'] = $row[0];

	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '%" . $city . "%' AND scan4_added BETWEEN '" . $startdate . "' AND '" . $enddate . "'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$stats['0']['newcustomers'] = $row[0];


	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND scan4_hbgdate BETWEEN '" . $laststartdate . "' AND '" . $lastenddate . "'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$stats['0']['donelastkw'] = $row[0];


	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND scan4_status ='DONE CLOUD' AND (hbg_status = 'OPEN' OR hbg_status = 'PLANNED');";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$stats['0']['donecloud'] = $row[0];

	$query = "SELECT scan4_homes.homeid,scan4_homes.street,scan4_homes.streetnumber,streetnumberadd,scan4_homes.plz,scan4_homes.city,scan4_homes.scan4_comment  FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND scan4_status ='DONE CLOUD' AND (hbg_status = 'OPEN' OR hbg_status = 'PLANNED');";
	if ($result = $conn->query($query)) {
		while ($row = mysqli_fetch_assoc($result)) {
			$a_cloud[] = $row;
		}
		$result->free_result();
	}


	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND (anruf5 != '' OR anruf5 IS NOT NULL) AND (emailsend != '' OR emailsend IS NOT NULL) AND (briefkasten != '' OR briefkasten IS NOT NULL);";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$stats['0']['failedcontact'] = $row[0];

	$query = "SELECT scan4_homes.homeid,scan4_homes.street,scan4_homes.streetnumber,streetnumberadd,scan4_homes.plz,scan4_homes.city,scan4_homes.scan4_comment FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND (anruf5 != '' OR anruf5 IS NOT NULL) AND (emailsend != '' OR emailsend IS NOT NULL) AND (briefkasten != '' OR briefkasten IS NOT NULL);";
	if ($result = $conn->query($query)) {
		while ($row = mysqli_fetch_assoc($result)) {
			$a_failed[] = $row;
		}
		$result->free_result();
	}


	$query = "SELECT COUNT(scan4_calls.homeid) FROM `scan4_calls` INNER JOIN scan4_homes ON scan4_calls.homeid=scan4_homes.homeid WHERE scan4_calls.result = 'Keine HBG - Kunde sagt, er habe gekündigt' AND scan4_homes.city LIKE '%" . $city . "%' ;";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$stats['0']['canceled'] = $row[0];

	$query = "SELECT scan4_calls.homeid,scan4_homes.street,scan4_homes.streetnumber,streetnumberadd,scan4_homes.plz,scan4_homes.city,scan4_homes.scan4_comment FROM `scan4_calls` INNER JOIN scan4_homes ON scan4_calls.homeid=scan4_homes.homeid WHERE scan4_calls.result = 'Keine HBG - Kunde sagt, er habe gekündigt' AND scan4_homes.city LIKE '%" . $city . "%' ;";
	if ($result = $conn->query($query)) {
		while ($row = mysqli_fetch_assoc($result)) {
			$a_canceled[] = $row;
		}
		$result->free_result();
	}



	$query = "SELECT COUNT(scan4_calls.homeid) FROM `scan4_calls` INNER JOIN scan4_homes ON scan4_calls.homeid=scan4_homes.homeid WHERE scan4_calls.`result` LIKE 'Keine HBG - Falsche Nummer' AND scan4_homes.city LIKE '%" . $city . "%' AND (phone1 IS NULL OR phone1 = '') AND (phone2 IS NULL OR phone2 = '') ;";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$stats['0']['wrongdetails'] = $row[0];

	$query = "SELECT scan4_homes.homeid,scan4_homes.street,scan4_homes.streetnumber,streetnumberadd,scan4_homes.plz,scan4_homes.city,scan4_homes.scan4_comment FROM `scan4_calls` INNER JOIN scan4_homes ON scan4_calls.homeid=scan4_homes.homeid WHERE scan4_calls.`result` LIKE 'Keine HBG - Falsche Nummer' AND scan4_homes.city LIKE '%" . $city . "%' AND (phone1 IS NULL OR phone1 = '') AND (phone2 IS NULL OR phone2 = '') ;";
	if ($result = $conn->query($query)) {
		while ($row = mysqli_fetch_assoc($result)) {
			$a_wrong[] = $row;
		}
		$result->free_result();
	}


	$query = "SELECT COUNT(scan4_calls.homeid) FROM `scan4_calls` INNER JOIN scan4_homes ON scan4_calls.homeid=scan4_homes.homeid WHERE scan4_calls.result = 'Keine HBG - Kunde verweigert HBG' AND scan4_homes.city LIKE '%" . $city . "%' ;";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$stats['0']['refused'] = $row[0];
	$query = "SELECT scan4_homes.homeid,scan4_homes.street,scan4_homes.streetnumber,streetnumberadd,scan4_homes.plz,scan4_homes.city,scan4_homes.scan4_comment FROM `scan4_calls` INNER JOIN scan4_homes ON scan4_calls.homeid=scan4_homes.homeid WHERE scan4_calls.result = 'Keine HBG - Kunde verweigert HBG' AND scan4_homes.city LIKE '%" . $city . "%' ;";
	if ($result = $conn->query($query)) {
		while ($row = mysqli_fetch_assoc($result)) {
			$a_refused[] = $row;
		}
		$result->free_result();
	}


	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '%" . $city . "%' AND (dpnumber = '' OR dpnumber IS NULL) ";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$stats['0']['nodp'] = $row[0];
	$query = "SELECT scan4_homes.homeid,scan4_homes.street,scan4_homes.streetnumber,streetnumberadd,scan4_homes.plz,scan4_homes.city,scan4_homes.scan4_comment  FROM `scan4_homes` WHERE city LIKE '%" . $city . "%' AND (dpnumber = '' OR dpnumber IS NULL) ";
	if ($result = $conn->query($query)) {
		while ($row = mysqli_fetch_assoc($result)) {
			$a_nodp[] = $row;
		}
		$result->free_result();
	}


	$dataoverview = [
		['<center><style bgcolor="#90cbff">' . $city . '</style></center>', null],
		['<right><style bgcolor="#c7c7bf">KW:</style></right>', '<style bgcolor="#ffa640">' . $kw . '</style>'],
		['<right><style bgcolor="#c7c7bf">NRI Open:</style></right>', '<right><style bgcolor="#ffa640">' .  $stats['0']['nriopen'] . " / " . $stats['0']['total'] . '</right></style>'],
		['<right><style bgcolor="#c7c7bf">Uncommented open HBGs:</style></right>', '<right><style bgcolor="#ffa640">' . $stats['0']['sc4open'] . " / " . $stats['0']['total']  . '</right></style>'],
		['<right><style bgcolor="#c7c7bf">New customers this week:</style></right>', '<style bgcolor="#ffa640">' . $stats['0']['newcustomers'] . '</style>'],
		['<right><style bgcolor="#c7c7bf">HBGs done week ' . $lastkw . ':</style></right>', '<style bgcolor="#ffa640">' . $stats['0']['donelastkw'] . '</style>'],
		['<right><style bgcolor="#c7c7bf">HBGs already in the cloud, which you have not registered:</style></right>', '<style bgcolor="#ffa640">' . $stats['0']['donecloud'] . '</style>'],
		['<right><style bgcolor="#c7c7bf">Customers we have called at least 5 times send email and dropped postcard, and you need to set as STOPPED:</style></right>', '<style bgcolor="#ffa640">' . $stats['0']['failedcontact'] . '</style>'],
		['<right><style bgcolor="#c7c7bf">Customer who has cancelled their contract, which you must set as STOPPED:</style></right>', '<style bgcolor="#ffa640">' . $stats['0']['canceled'] . '</style>'],
		['<right><style bgcolor="#c7c7bf">Customer with wrong/missing contact details:</style></right>', '<style bgcolor="#ffa640">' . $stats['0']['wrongdetails'] . '</style>'],
		['<right><style bgcolor="#c7c7bf">Customer who refuses the HBG:</style></right>', '<style bgcolor="#ffa640">' . $stats['0']['refused'] . '</style>'],
		['<right><style bgcolor="#c7c7bf">No DP number:</style></right>', '<style bgcolor="#ffa640">' . $stats['0']['nodp'] . '</style>']
	];
	$datacanceled = [
		['<left><style bgcolor="#63b521">Straße</style></left>', '<left><style bgcolor="#63b521">Hausnummer</style></left>', '<left><style bgcolor="#63b521">Hausnummerzusatz</style></left>', '<left><style bgcolor="#63b521">HomeID</style></left>', '<left><style bgcolor="#ffce00">Scan4 Comments</style></left>']
	];
	for ($i = 0; $i < count($a_canceled); $i++) {
		$datacanceled[] =
			[
				'<left>' . $a_canceled[$i]['street'] . '</left>',
				'<left>' . $a_canceled[$i]['streetnumber'] . '</left>',
				'<left>' . $a_canceled[$i]['streetnumberadd'] . '</left>',
				'<left>' . $a_canceled[$i]['homeid'] . '</left>',
				'<left>' . $a_canceled[$i]['scan4_comment'] . '</left>'
			];
	}
	$datacloud = [
		['<left><style bgcolor="#63b521">Straße</style></left>', '<left><style bgcolor="#63b521">Hausnummer</style></left>', '<left><style bgcolor="#63b521">Hausnummerzusatz</style></left>', '<left><style bgcolor="#63b521">HomeID</style></left>', '<left><style bgcolor="#ffce00">Scan4 Comments</style></left>']
	];
	for ($i = 0; $i < count($a_cloud); $i++) {
		$datacloud[] =
			[
				'<left>' . $a_cloud[$i]['street'] . '</left>',
				'<left>' . $a_cloud[$i]['streetnumber'] . '</left>',
				'<left>' . $a_cloud[$i]['streetnumberadd'] . '</left>',
				'<left>' . $a_cloud[$i]['homeid'] . '</left>',
				'<left>' . $a_cloud[$i]['scan4_comment'] . '</left>'
			];
	}
	$datawrong = [
		['<left><style bgcolor="#63b521">Straße</style></left>', '<left><style bgcolor="#63b521">Hausnummer</style></left>', '<left><style bgcolor="#63b521">Hausnummerzusatz</style></left>', '<left><style bgcolor="#63b521">HomeID</style></left>', '<left><style bgcolor="#ffce00">Scan4 Comments</style></left>']
	];
	for ($i = 0; $i < count($a_wrong); $i++) {
		$datawrong[] =
			[
				'<left>' . $a_wrong[$i]['street'] . '</left>',
				'<left>' . $a_wrong[$i]['streetnumber'] . '</left>',
				'<left>' . $a_wrong[$i]['streetnumberadd'] . '</left>',
				'<left>' . $a_wrong[$i]['homeid'] . '</left>',
				'<left>' . $a_wrong[$i]['scan4_comment'] . '</left>'
			];
	}
	$datafailed = [
		['<left><style bgcolor="#63b521">Straße</style></left>', '<left><style bgcolor="#63b521">Hausnummer</style></left>', '<left><style bgcolor="#63b521">Hausnummerzusatz</style></left>', '<left><style bgcolor="#63b521">HomeID</style></left>', '<left><style bgcolor="#ffce00">Scan4 Comments</style></left>']
	];
	for ($i = 0; $i < count($a_failed); $i++) {
		$datafailed[] =
			[
				'<left>' . $a_failed[$i]['street'] . '</left>',
				'<left>' . $a_failed[$i]['streetnumber'] . '</left>',
				'<left>' . $a_failed[$i]['streetnumberadd'] . '</left>',
				'<left>' . $a_failed[$i]['homeid'] . '</left>',
				'<left>' . $a_failed[$i]['scan4_comment'] . '</left>'
			];
	}
	$datarefused = [
		['<left><style bgcolor="#63b521">Straße</style></left>', '<left><style bgcolor="#63b521">Hausnummer</style></left>', '<left><style bgcolor="#63b521">Hausnummerzusatz</style></left>', '<left><style bgcolor="#63b521">HomeID</style></left>', '<left><style bgcolor="#ffce00">Scan4 Comments</style></left>']
	];
	for ($i = 0; $i < count($a_refused); $i++) {
		$datarefused[] =
			[
				'<left>' . $a_refused[$i]['street'] . '</left>',
				'<left>' . $a_refused[$i]['streetnumber'] . '</left>',
				'<left>' . $a_refused[$i]['streetnumberadd'] . '</left>',
				'<left>' . $a_refused[$i]['homeid'] . '</left>',
				'<left>' . $a_refused[$i]['scan4_comment'] . '</left>'
			];
	}
	$datanodp = [
		['<left><style bgcolor="#63b521">Straße</style></left>', '<left><style bgcolor="#63b521">Hausnummer</style></left>', '<left><style bgcolor="#63b521">Hausnummerzusatz</style></left>', '<left><style bgcolor="#63b521">HomeID</style></left>', '<left><style bgcolor="#ffce00">Scan4 Comments</style></left>']
	];
	for ($i = 0; $i < count($a_nodp); $i++) {
		$datanodp[] =
			[
				'<left>' . $a_nodp[$i]['street'] . '</left>',
				'<left>' . $a_nodp[$i]['streetnumber'] . '</left>',
				'<left>' . $a_nodp[$i]['streetnumberadd'] . '</left>',
				'<left>' . $a_nodp[$i]['homeid'] . '</left>',
				'<left>' . $a_nodp[$i]['scan4_comment'] . '</left>'
			];
	}



	$result = Shuchkin\SimpleXLSXGen::fromArray($dataoverview, 'Overview')
		->setDefaultFont('Calibri')
		->setDefaultFontSize(11)
		->setColWidth(1, 100) // 1 - num column, 35 - size in chars
		->mergeCells('A1:B1')
		->addSheet($datacanceled, 'cancelled Contract')
		->setColWidth(1, 20)
		->addSheet($datacloud, 'HBG in cloud')
		->setColWidth(1, 20)
		->addSheet($datawrong, 'wrong&missing details')
		->setColWidth(1, 20)
		->addSheet($datafailed, '5 calls+postcard')
		->setColWidth(1, 20)
		->addSheet($datarefused, 'refuses HBG')
		->setColWidth(1, 20)
		->addSheet($datanodp, 'no DP')
		->setColWidth(1, 20)
		->downloadAs('Report_' . $city . '.xlsx');

	Shuchkin\SimpleXLSXGen::fromArray($dataoverview)->downloadAs('datatypes.xlsx');
}




//include_once 'wp-content/themes/twentytwentytwo/admin/phoner.php';

function selectcity()
{
	// report("Heusenstamm");
	spreadsheet_generate("Gudensberg");
}
/*
function report($city)
{
    $failed = 0;
    $conn = dbconnect();
    $query = 'SELECT * FROM `scan4_homes` WHERE Ort = "' . $city . '" AND anruf5 IS NOT NULL AND emailgesendet IS NOT NULL AND Briefcasten IS NOT NULL';
    if ($result = $conn->query($query)) {
        while ($row = $result->fetch_row()) {
            $failed++;
            $a_failed[] = $row;
        }
        $result->free_result();
    }

    spreadsheet_generate($city);
}
*/


function spreadsheet_generate($city)
{
	$conn = dbconnect();
	$total = 0;
	$new = 0;
	$open = 0;
	$planned = 0;
	$uncommented = 0;
	$nodp = 0;
	$failed = 0;
	$wrong = 0;
	$dateweek = date('W');
	$dateweeklast = date('W', strtotime('last week'));
	$datanull = [
		[
			'<left><style bgcolor="#63b521">Straße</style></left>',
			'<left><style bgcolor="#63b521">Hausnummer</style></left>',
			'<left><style bgcolor="#63b521">Hausnummerzusatz</style></left>',
			'<left><style bgcolor="#63b521">HomeID</style></left>',
			'<left><style bgcolor="#ffce00">Scan4 Comments</style></left>',
		]
	];


	$stats = array();


	//$query = "SELECT COUNT(homeid) FROM `scan4_calls` WHERE call_user LIKE '%" . $user . "%' AND call_date BETWEEN '" . $startdate . "' AND '" . $enddate . "'";
	$query = "SELECT COUNT(homeid) FROM `scan4_homeid` WHERE scan4_homes.city LIKE '%" . $city . "%';";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$stats['0']['total'] = $row[0];

	echo $query;


	$query = 'SELECT * FROM `scan4_homes` WHERE city = "' . $city . '" AND hbg_status = "OPEN"';
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_row()) {
			$open++;
		}
		$result->free_result();
	}
	$a_uncommented[] = array();
	$query = 'SELECT * FROM `scan4_homes` WHERE city = "' . $city . '" AND hbg_status = "OPEN" AND (scan4_comment = "" OR scan4_comment IS NULL)';
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_row()) {
			$a_uncommented[] = $row;
			$uncommented++;
		}
		$result->free_result();
	}
	$a_nodp[] = array();
	$query = 'SELECT * FROM `scan4_homes` WHERE city = "' . $city . '" AND dpnumber = "" OR dpnumber = NULL';
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_row()) {
			$nodp++;
			$a_nodp[] = $row;
		}
		$result->free_result();
	}
	$a_failed[] = array();
	$query = 'SELECT * FROM `scan4_homes` WHERE city = "' . $city . '" AND anruf5 IS NOT NULL AND emailgesendet IS NOT NULL AND briefkasten IS NOT NULL';
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_row()) {
			$failed++;
			$a_failed[] = $row;
		}
		$result->free_result();
	}
	$a_terminate[] = array();
	$query = 'SELECT * FROM `scan4_homes` WHERE city = "' . $city . '" AND scan4_comment = "Keine HBG - Kunde sagt, er habe gekündigt"';
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_row()) {
			$a_terminate[] = $row;
		}
		$result->free_result();
	}
	$a_wrong[] = array();
	$query = 'SELECT * FROM `scan4_homes` WHERE city = "' . $city . '" AND (phone1 IS NULL OR phone1 = "") AND (phone2 IS NULL or phone2 = "")';
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_row()) {
			$a_wrong[] = $row;
			$wrong++;
		}
		$result->free_result();
	}




	$year = date('Y');
	$first_week_no = date('W');
	$week_start = new DateTime();
	$week_start->setISODate($year, $first_week_no);
	$seven_day_week = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
	$week = array();
	for ($i = 0; $i < 7; $i++) {
		$day = $seven_day_week[$i];
		$week[$day] = $week_start->format('Y-m-d');
		$week_start->modify('+1 day');
		$query = 'SELECT * FROM `scan4_homes` WHERE city = "' . $city . '" AND scan4_Date = "' . $week[$day] . '" ';
		if ($result = $conn->query($query)) {
			while ($row = $result->fetch_row()) {
				$a_new[] = $row;
				$new++;
			}
			$result->free_result();
		}
	}




	$dataoverview = [
		['<center><style bgcolor="#90cbff">' . $city . '</style></center>', null],
		['<right><style bgcolor="#c7c7bf">KW:</style></right>', '<style bgcolor="#ffa640">' . $dateweek . '</style>'],
		['<right><style bgcolor="#c7c7bf">NRI Open:</style></right>', '<right><style bgcolor="#ffa640">' . $open . " / " . $total . '</right></style>'],
		['<right><style bgcolor="#c7c7bf">Uncommented open HBGs:</style></right>', '<right><style bgcolor="#ffa640">' . $uncommented . " / " . $total . '</right></style>'],
		['<right><style bgcolor="#c7c7bf">New customers this week:</style></right>', '<style bgcolor="#ffa640">' . $new . '</style>'],
		['<right><style bgcolor="#c7c7bf">Removed customers this week:</style></right>', '<style bgcolor="#ffa640">' . 'UNSET' . '</style>'],
		['<right><style bgcolor="#c7c7bf">HBGs done week ' . $dateweeklast . ':</style></right>', '<style bgcolor="#ffa640">' . 'UNSET' . '</style>'],
		['<right><style bgcolor="#c7c7bf">HBG App Bug:</style></right>', '<style bgcolor="#ffa640">' . "UNSET" . '</style>'],
		['<right><style bgcolor="#c7c7bf">HBGs already in the cloud, which you have not registered:</style></right>', '<style bgcolor="#ffa640">' . 'UNSET' . '</style>'],
		['<right><style bgcolor="#c7c7bf">Customers we have called at least 5 times send email and dropped postcard, and you need to set as STOPPED:</style></right>', '<style bgcolor="#ffa640">' . $failed . '</style>'],
		['<right><style bgcolor="#c7c7bf">Customer who has cancelled their contract, which you must set as STOPPED:</style></right>', '<style bgcolor="#ffa640">' . 'UNSET' . '</style>'],
		['<right><style bgcolor="#c7c7bf">Customer with wrong/missing contact details:</style></right>', '<style bgcolor="#ffa640">' . $wrong . '</style>'],
		['<right><style bgcolor="#c7c7bf">Customer who refuses the HBG:</style></right>', '<style bgcolor="#ffa640">' . "UNSET" . '</style>'],
		['<right><style bgcolor="#c7c7bf">No DP number:</style></right>', '<style bgcolor="#ffa640">' . $nodp . '</style>']
	];

	$datacanceled = [
		['<left><style bgcolor="#63b521">Straße</style></left>', '<left><style bgcolor="#63b521">Hausnummer</style></left>', '<left><style bgcolor="#63b521">Hausnummerzusatz</style></left>', '<left><style bgcolor="#63b521">HomeID</style></left>', '<left><style bgcolor="#ffce00">Scan4 Comments</style></left>']
	];
	for ($i = 0; $i < count($a_terminate); $i++) {
		$datacanceled[] =
			[
				'<left>' . $a_terminate[$i][3] . '</left>',
				'<left>' . $a_terminate[$i][4] . '</left>',
				'<left>' . $a_terminate[$i][5] . '</left>',
				'<left>' . $a_terminate[$i][10] . '</left>',
				'<left>' . $a_terminate[$i][23] . '</left>'
			];
	}


	$datawrong = [
		[
			'<left><style bgcolor="#63b521">Straße</style></left>',
			'<left><style bgcolor="#63b521">Hausnummer</style></left>',
			'<left><style bgcolor="#63b521">Hausnummerzusatz</style></left>',
			'<left><style bgcolor="#63b521">HomeID</style></left>',
			'<left><style bgcolor="#ffce00">Scan4 Comments</style></left>',
		]
	];
	for ($i = 0; $i < count($a_wrong); $i++) {
		$datawrong[] =
			[
				'<center>' . $a_wrong[$i][3] . '</center>',
				'<center>' . $a_wrong[$i][4] . '</center>',
				'<center>' . $a_wrong[$i][5] . '</center>',
				'<center>' . $a_wrong[$i][10] . '</center>',
				'<center>' . $a_wrong[$i][23] . '</center>'
			];
	}
	$datafailed = [
		[
			'<left><style bgcolor="#63b521">Straße</style></left>',
			'<left><style bgcolor="#63b521">Hausnummer</style></left>',
			'<left><style bgcolor="#63b521">Hausnummerzusatz</style></left>',
			'<left><style bgcolor="#63b521">HomeID</style></left>',
			'<left><style bgcolor="#ffce00">Scan4 Comments</style></left>',
			'<left><style bgcolor="#ffce00">anruf 1</style></left>',
			'<left><style bgcolor="#ffce00">anruf 2</style></left>',
			'<left><style bgcolor="#ffce00">anruf 3</style></left>',
			'<left><style bgcolor="#ffce00">anruf 4</style></left>',
			'<left><style bgcolor="#ffce00">anruf 5</style></left>',
			'<left><style bgcolor="#ffce00">Briefkasten</style></left>',
			'<left><style bgcolor="#ffce00">Email gesendet</style></left>'
		]
	];
	for ($i = 0; $i < count($a_failed); $i++) {
		$datafailed[] =
			[
				'<center>' . $a_failed[$i][3] . '</center>',
				'<center>' . $a_failed[$i][4] . '</center>',
				'<center>' . $a_failed[$i][5] . '</center>',
				'<center>' . $a_failed[$i][10] . '</center>',
				'<center>' . $a_failed[$i][23] . '</center>',
				'<center>' . $a_failed[$i][27] . '</center>',
				'<center>' . $a_failed[$i][28] . '</center>',
				'<center>' . $a_failed[$i][29] . '</center>',
				'<center>' . $a_failed[$i][30] . '</center>',
				'<center>' . $a_failed[$i][31] . '</center>',
				'<center>' . $a_failed[$i][32] . '</center>',
				'<center>' . $a_failed[$i][33] . '</center>'
			];
	}
	$datanodp = [
		['<left><style bgcolor="#63b521">Straße</style></left>', '<left><style bgcolor="#63b521">Hausnummer</style></left>', '<left><style bgcolor="#63b521">Hausnummerzusatz</style></left>', '<left><style bgcolor="#63b521">HomeID</style></left>', '<left><style bgcolor="#ffce00">Scan4 Comments</style></left>']
	];
	for ($i = 0; $i < count($a_nodp); $i++) {
		$datanodp[] =
			[
				'<left>' . $a_nodp[$i][3] . '</left>',
				'<left>' . $a_nodp[$i][4] . '</left>',
				'<left>' . $a_nodp[$i][5] . '</left>',
				'<left>' . $a_nodp[$i][10] . '</left>',
				'<left>' . $a_nodp[$i][23] . '</left>',
			];
	}


	$data = [
		['<center><style bgcolor="#90cbff">Gudensberg</style></center>', null],
		['Bold', '<b>12345.67</b>'],
		['Italic', '<i>12345.67</i>'],
		['Underline', '<u>12345.67</u>'],
		['Strike', '<s>12345.67</s>'],
		['Bold + Italic', '<b><i>12345.67</i></b>'],
		['Hyperlink', 'https://github.com/shuchkin/simplexlsxgen'],
		['Italic + Hyperlink + Anchor', '<i><a href="https://github.com/shuchkin/simplexlsxgen">SimpleXLSXGen</a></i>'],
		['Green', '<style color="#00FF00">12345.67</style>'],
		['Bold Red Text', '<b><style color="#FF0000">12345.67</style></b>'],
		['Blue Text and Yellow Fill', '<style bgcolor="#FFFF00" color="#0000FF">12345.67</style>'],
		['Left', '<left>12345.67</left>'],
		['Center', '<center>12345.67</center>'],
		['Right', '<right>Right Text</right>'],
		['Center + Bold', '<center><b>Name</b></center>'],
		['Row height', '<style height="50">Row Height = 50</style>'],
		['Top', '<style height="50"><top>Top</top></style>'],
		['Middle + Center', '<style height="50"><middle><center>Middle + Center</center></middle></style>'],
		['Bottom + Right', '<style height="50"><bottom><right>Bottom + Right</right></bottom></style>'],
		['<center>MERGE CELLS MERGE CELLS MERGE CELLS MERGE CELLS MERGE CELLS</center>', null],
		['<top>Word wrap</top>', "<wraptext>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book</wraptext>"]
	];


	echo print_r($stats);

	$result = Shuchkin\SimpleXLSXGen::fromArray($stats, 'Overview')
		->setDefaultFont('Calibri')
		->setDefaultFontSize(11)
		->setColWidth(1, 100) // 1 - num column, 35 - size in chars
		->mergeCells('A1:B1')
		->addSheet($datacanceled, 'cancelled Contract')
		->setColWidth(1, 20)
		->addSheet($datanull, 'HBG in cloud')
		->setColWidth(1, 20)
		->addSheet($datawrong, 'wrong&missing details')
		->setColWidth(1, 20)
		->addSheet($datafailed, '5 calls+postcard')
		->setColWidth(1, 20)
		->addSheet($datanull, 'refuses HBG')
		->setColWidth(1, 20)
		->addSheet($datanodp, 'no DP')
		->setColWidth(1, 20)
		->downloadAs('Report_' . $city . '.xlsx');

	echo $result;

	Shuchkin\SimpleXLSXGen::fromArray($dataoverview)->downloadAs('datatypes.xlsx');
}
