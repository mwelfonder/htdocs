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
$userdetails = fetchPermissionUsers(1); // 5 = Telefonist
for ($i = 0; $i < count($userdetails); $i++) {
	$data = fetchUserDetails(null, null, $userdetails[$i]->user_id);
	$a_userlist =  array($data->username => $data->profile_pic);
}


include "../../view/includes/functions.php";

date_default_timezone_set('Europe/Berlin');
if (!isset($_POST['func'])) {
	die();
}


$func = $_POST['func'];
if ($func === "load_citycards") {
	$client = $_POST['client'];
	load_citycards($client);
} else if ($func === "load_citystats") {
	$client = $_POST['client'];
	load_citystats($client);
} else if ($func === "load_openhomeid") {
	$city = $_POST['city'];
	load_openhomeid($city);
} else if ($func === "load_tickets") {
	$client = $_POST['client'];
	load_opentickets($client);
} else if ($func === "load_timeline") {
	$homeid = $_POST['homeid'];
	load_entry_timeline($homeid);
	load_entry_relations($homeid);
	load_entry_hbgs($homeid);
} else if ($func === "safe_homeid") {
	$homeid = $_POST['homeid'];
	$reason = $_POST['reason'];
	$comment = $_POST['comment'];
	$city = $_POST['city'];
	$hbgdate = $_POST['hbgdate'];
	$hbgdurration = $_POST['hbgdurration'];
	$hbguser = $_POST['hbguser'];
	safe_homeid_call($homeid, $reason, $comment, $city, $hbgdate, $hbgdurration, $hbguser);
} else if ($func === "load_loadthishomeid") {
	$homeid = $_POST['homeid'];
	load_loadthishomeid($homeid);
} else if ($func === "load_followup") {
	$date = $_POST['date'];
	load_followup($date);
} else if ($func === "mark_followup") {
	$homeid = $_POST['homeid'];
	$date = $_POST['date'];
	$time = $_POST['time'];
	$status = $_POST['status'];
	mark_followup($homeid, $date, $time, $status);
} else if ($func === "safe_phoneclick") {
	$homeid = $_POST['homeid'];
	$phoneid = $_POST['phoneid'];
	$phonenr = $_POST['phonenr'];
	safe_phoneclick($homeid, $phoneid, $phonenr);
} else if ($func === "nextcloud_change") {
	$uid = $_POST['uid'];
	$reason = $_POST['reason'];
	nextcloud_change($uid, $reason);
} else if ($func === "change_hbg_storno") {
	$uid = $_POST['uid'];
	$comment = $_POST['comment'];
	change_hbg_storno($uid, $comment);
} else if ($func === "change_hbg_move") {
	$uid = $_POST['uid'];
	$comment = $_POST['comment'];
	$user = $_POST['user'];
	$datetime = $_POST['date'];
	change_hbg_move($uid, $comment, $user, $datetime);
} else if ($func === "safe_admin_edit") {
	$homeid = $_POST['homeid'];
	$comment = $_POST['comment'];
	$status = $_POST['status'];
	$prio = $_POST['prio'];
	$calls = $_POST['calls'];
	safe_admin_edit($homeid, $comment, $status, $prio, $calls);
} else if ($func === "load_entry_logfile") {
	$homeid = $_POST['homeid'];
	load_entry_logfile($homeid);
} else if ($func === "calendar_getall") {
	$city = $_POST['city'];
	calendar_getall($city);
} else if ($func === "calendar_getall_appt") {
	$city = $_POST['city'];
	$user = $_POST['user'];
	$date = $_POST['date'];
	calendar_getall_appt($city, $user, $date);
}






function safe_admin_edit($homeid, $comment, $status, $prio, $calls)
{
	echo 'homeid:' . $homeid;
	echo ' comment:' . $comment;
	echo ' status:' . $status;
	echo ' prio:' . $prio;
	echo ' calls:' . $calls;
	$counter = 0;

	global $currentuser;
	$conn = dbconnect();
	if ($calls === '0') {
		$query = "UPDATE `scan4_homes` SET scan4_comment =  '" . $comment . "', `scan4_status` = '" . $status . "', `priority` = '" . $prio . "', `anruf1` = NULL,`anruf2` = NULL,`anruf3` = NULL,`anruf4` = NULL,`anruf5` = NULL WHERE `homeid`= '" . $homeid . "'";
	} else if ($calls === '1') {
		$query = "UPDATE `scan4_homes` SET scan4_comment =  '" . $comment . "', `scan4_status` = '" . $status . "', `priority` = '" . $prio . "', `anruf2` = NULL,`anruf3` = NULL,`anruf4` = NULL,`anruf5` = NULL WHERE `homeid` = '" . $homeid . "'";
	} else if ($calls === '2') {
		$query = "UPDATE `scan4_homes` SET scan4_comment =  '" . $comment . "', `scan4_status` = '" . $status . "', `priority` = '" . $prio . "', `anruf3` = NULL,`anruf4` = NULL,`anruf5` = NULL WHERE `homeid` = '" . $homeid . "'";
	} else if ($calls === '3') {
		$query = "UPDATE `scan4_homes` SET scan4_comment =  '" . $comment . "', `scan4_status` = '" . $status . "', `priority` = '" . $prio . "', `anruf4` = NULL,`anruf5` = NULL WHERE `homeid` = '" . $homeid . "'";
	} else if ($calls === '4') {
		$query = "UPDATE `scan4_homes` SET scan4_comment =  '" . $comment . "', `scan4_status` = '" . $status . "', `priority` = '" . $prio . "', `anruf5` = NULL WHERE `homeid` = '" . $homeid . "'";
	} else if ($calls === '5') {
		$query = "UPDATE `scan4_homes` SET scan4_comment =  '" . $comment . "', `scan4_status` = '" . $status . "', `priority` = '" . $prio . "' ,`anruf5` = '' WHERE `homeid` = '" . $homeid . "'";
	}
	mysqli_query($conn, $query);
	if ($status !== '') {
		$query = "UPDATE `scan4_homes` SET scan4_comment =  '" . $comment . "', `scan4_status` = '" . $status . "' WHERE `homeid`= '" . $homeid . "'";
		mysqli_query($conn, $query);
	}
	if ($prio !== '') {
		$query = "UPDATE `scan4_homes` SET scan4_comment =  '" . $comment . "', `priority` = '" . $prio . "' WHERE `homeid` = '" . $homeid . "'";
		mysqli_query($conn, $query);
	}


	$query = "INSERT INTO `scan4_homes_history`(`homeid`, `col1`, `col2`, `col3`, `col4`, col5,col6) VALUES ('" . $homeid . "','editbutton','" . $currentuser . "','set scan4status to " . $status . "','comment: " . $comment . "','priority to" . $prio . "', 'calls set to " . $calls . "')";
	mysqli_query($conn, $query);
	$date = date('Y-m-d');
	$time = date('H:i:s');
	if ($status !== '') {
		$counter++;
		if ($counter > 1) $comment = '';
		$query = "INSERT INTO `scan4_calls`(`call_count`, `call_date`, `call_time`, `call_user`, `homeid`, `result`, `comment`) VALUES ('0','" . $date . "','" . $time . "','" . $currentuser . "','" . $homeid . "','edit: Scan4 Status zu " . $status . " geändert','" . $comment . "')";
		mysqli_query($conn, $query);
	}
	if ($prio !== '') {
		$counter++;
		if ($counter > 1) $comment = '';
		$query = "INSERT INTO `scan4_calls`(`call_count`, `call_date`, `call_time`, `call_user`, `homeid`, `result`, `comment`) VALUES ('0','" . $date . "','" . $time . "','" . $currentuser . "','" . $homeid . "','edit: Prio zu " . $prio . " geändert','" . $comment . "')";
		mysqli_query($conn, $query);
	}
	if ($calls !== '') {
		$counter++;
		if ($counter > 1) $comment = '';
		$query = "INSERT INTO `scan4_calls`(`call_count`, `call_date`, `call_time`, `call_user`, `homeid`, `result`, `comment`) VALUES ('0','" . $date . "','" . $time . "','" . $currentuser . "','" . $homeid . "','edit: Anrufe zu " . $calls . " geändert','" . $comment . "')";
		mysqli_query($conn, $query);
	}
	mysqli_close($conn);
}



function change_hbg_move($uid, $comment, $hbguser, $datetime)
{

	$split = explode(' ', $datetime);
	$hbgdate = $split[0];
	$hbgtime = $split[1];
	$hbgdurration = '30 min';
	$timestamp = date('Y-m-d H:i:s');

	$conn = dbconnect();

	$query = "SELECT * FROM `scan4_hbg` WHERE uid = '" . $uid . "' ORDER BY `scan4_hbg`.`id` DESC LIMIT 1";
	$result = mysqli_query($conn, $query);
	$row = mysqli_fetch_assoc($result);

	if ($row['status'] === 'STORNO') {
		echo 'already storno';
	} else if ($row['status'] === 'MOVED') {
		echo 'already moved';
	} else {
		$ident = uniqid();
		global $currentuser;
		$date = date('Y-m-d');
		$time = date('H:i:s');
		$split = explode('-', $uid);
		if (isset($split[1])) $homeid = $split[1];
		$query = "UPDATE `scan4_hbg` SET`status`='MOVED' WHERE uid = '" . $uid . "'";
		mysqli_query($conn, $query);
		$query = "INSERT INTO `scan4_calls`(`call_date`, `call_time` ,`call_user`, `homeid`,`result`,`comment`,`callid`) VALUES ('" . $date . "','" . $time . "','" . $currentuser . "','" . $homeid . "','HBG moved','" . $comment . "','" . $ident . "')";
		mysqli_query($conn, $query);

		$tstamp = gmdate("Ymd\THis\Z");
		$newuid = $tstamp . '-' . $homeid . '-RN' . rand(10000, 99999);
		$query = "INSERT INTO `scan4_hbg`(`date`,`time`, `durration` ,`homeid`, `hausbegeher`,`comment`,`status`,`username`,`created`,`ident`,`uid`) VALUES ('" . $hbgdate . "','" . $hbgtime . "','" . $hbgdurration . "','" . $homeid . "','" . $hbguser . "','" . $comment . "','PLANNED','" . $currentuser . "','" . $timestamp . "','" . $ident . "','" . $newuid . "')";
		mysqli_query($conn, $query);
		echo $query;
		$query = "UPDATE `scan4_homes` SET `scan4_status`='PLANNED',`scan4_hbgdate`='" . $hbgdate . "' WHERE homeid = '" . $homeid . "'";
		mysqli_query($conn, $query);
		$rs = nextcloud_move($uid, $newuid);
		echo 'moved response:' . $rs;

		calendar_create_hbg($newuid);
		calendar_delete($uid);
	}
	$conn->close();
}


function change_hbg_storno($uid, $comment)
{
	$conn = dbconnect();

	$query = "SELECT * FROM `scan4_hbg` WHERE uid = '" . $uid . "' ORDER BY `scan4_hbg`.`id` DESC LIMIT 1";
	$result = mysqli_query($conn, $query);
	$row = mysqli_fetch_assoc($result);

	if ($row['status'] === 'STORNO') {
		echo 'already storno';
	} else if ($row['status'] === 'MOVED') {
		echo 'already moved';
	} else {
		global $currentuser;
		$date = date('Y-m-d');
		$time = date('H:i:s');
		$split = explode('-', $uid);
		if (isset($split[1])) $homeid = $split[1];
		$query = "UPDATE `scan4_hbg` SET`status`='STORNO' WHERE uid = '" . $uid . "'";
		mysqli_query($conn, $query);
		$query = "INSERT INTO `scan4_calls`(`call_date`, `call_time` ,`call_user`, `homeid`,`result`,`comment`,`callid`) VALUES ('" . $date . "','" . $time . "','" . $currentuser . "','" . $homeid . "','HBG Storno','" . $comment . "','" . $uid . "')";
		mysqli_query($conn, $query);
		$query = "UPDATE `scan4_homes` SET `scan4_status`='OPEN',`scan4_hbgdate`='' WHERE homeid = '" . $homeid . "'";
		mysqli_query($conn, $query);

		$query = "INSERT INTO `scan4_userlog`( `homeid`,`user`, `source`,`action1`, action2) VALUES ('" . $homeid . "','" . $currentuser . "','phoner','Termin wurde storniert', '" . $uid . "')";
		mysqli_query($conn, $query) or die(mysqli_error($conn));
		$rs = nextcloud_delete($uid);
		echo $rs;
		calendar_delete($uid);
	}
	$conn->close();
}


function safe_phoneclick($homeid, $phoneid, $phonenr)
{
	$conn = dbconnect();
	global $currentuser;
	$query = "INSERT INTO `scan4_userlog`( `homeid`,`user`, source,`action1`,`action2`,`action3`) VALUES ('" . $homeid . "','" . $currentuser . "','phoner','click phonenumber','" . $phoneid . "', '" . $phonenr . "')";
	mysqli_query($conn, $query);
	$conn->close();
}

function mark_followup($homeid, $date, $time, $status)
{
	$conn = dbconnect();
	global $currentuser;
	$date = date('Y-m-d');
	if ($status === 'done') {
		$query = "UPDATE `scan4_followup` SET `active`='0' WHERE homeid = '" . $homeid . "' AND date LIKE '" . $date . "' AND time LIKE '%" . $time . "%'";
	} else {
		$query = "UPDATE `scan4_followup` SET `active`='1' WHERE homeid = '" . $homeid . "' AND date LIKE '" . $date . "' AND time LIKE '%" . $time . "%'";
	}
	echo $query;
	mysqli_query($conn, $query);
	$conn->close();
}

function load_followup($date)
{
	global $currentuser;
	ob_start();
	if ($date === 'today') {
		$date = date('Y-m-d');
	}
	$conn = dbconnect();
	$query = "SELECT * FROM `scan4_followup` WHERE `user`= '" . $currentuser . "' AND `date` = '" . $date . "' ORDER BY `scan4_followup`.`time` DESC";
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_row()) {
			$entry[] = $row;
		}
		$result->free_result();
	}
	//echo print_r($entry);
	//echo 'countist:'.count($entry);
	if (isset($entry) && is_countable($entry)) {
		$length = count($entry);
		for ($i = 0; $i < $length; $i++) {
			//echo $entry[$i][4];
			$query = "SELECT city,street,streetnumber,streetnumberadd,firstname,lastname FROM `scan4_homes` WHERE homeid = '" . $entry[$i][4] . "'";
			if ($result = $conn->query($query)) {
				while ($row = $result->fetch_row()) {
					if ($entry[$i][7] === "0") $followstatus = '<i class="ri-checkbox-line"></i>';
					if ($entry[$i][7] === "1") $followstatus = '<i class="ri-checkbox-blank-line"></i>';
?>
					<tr id="followid_<?php echo $entry[$i][4] ?>" class="followup-row">
						<td class="followup-status"><?php echo $followstatus ?></td>
						<td class="followup-city"><?php echo $row[0] ?></td>
						<td class="followup-adress"><?php echo $row[1] . ' ' . $row[2] . ' ' . $row[3] ?></td>
						<td class="followup-name"><?php echo $row[4] . ' ' . $row[5] ?></td>
						<td><a class="list-entry-hrefhomeid" href="route.php?view=phonerapp?homeid=<?php echo $entry[$i][4] ?>" target="_blank"><b style="cursor:pointer;"><?php echo $entry[$i][4] ?></b></a></td>
						<td class="followup-time"><?php echo mb_substr($entry[$i][2], 0, -3) ?></td>
						<td class="followup-comment"><?php echo $entry[$i][5] ?></td>
					</tr>
				<?php
				}
				$result->free_result();
			}
		}
	}
	$conn->close();
	//echo $list;
	$output = ob_get_contents();
	ob_clean();
	ob_flush();
	if (!empty($output)) {
		echo $output;
	} else {
		echo 'string:empty';
	}
}


function load_loadthishomeid($homeid)
{
	$conn = dbconnect();
	$query = "SELECT * FROM `scan4_homes` WHERE homeid = '" . $homeid . "'";
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_row()) {
			if (hasPerm(8) && $row[1] === 'Insyte') { // 8 = Insyte
				$entry = $row;
			}
			if (hasPerm(9) && $row[1]  === 'Moncobra') { // 9 = Moncobra
				$entry = $row;
			}
		}
		$result->free_result();
	}
	$conn->close();
	safe_homeid_log($entry[10], $entry[7]);
	echo json_encode($entry);
}


function safe_homeid($homeid, $user, $reason, $comment, $city)
{
	$conn = dbconnect();
	$query = "INSERT INTO `scan4_log`(`homeid`, `user`, `result`, `comment`, `city`) VALUES ('" . $homeid . "','" . $user . "','" . $reason . "','" . $comment . "','" . $city . "')";
	mysqli_query($conn, $query);
	$conn->close();
}



function safe_homeid_log($homeid, $city)
{
	global $currentuser;
	$a = "test";
	$conn = dbconnect();
	$query = "INSERT INTO `scan4_log`(`homeid`, `user`, `result`, `city`) VALUES ('" . $homeid . "','" . $currentuser . "','OPEND','" . $city . "')";
	mysqli_query($conn, $query);
	$conn->close();
}


function load_openhomeid($city)
{
	$conn = dbconnect();
	$query = "SELECT * FROM `scan4_citylist` WHERE 1";
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_assoc()) {
			if (!hasPerm(8) && $row['city'] === 'Insyte') { // 8 = Insyte
				die();
			}
			if (!hasPerm(9) && $row['city'] === 'Moncobra') { // 9 = Moncobra
				die();
			}
		}
		$result->free_result();
	}


	//$user = myusername();

	//$date = date("Y-m-d H:i:s");
	//$date = date('Y-m-d H:i:s', strtotime('-15 minutes', strtotime($date)));


	$date = date("Y-m-d");



	$a_array = array();
	$query = "SELECT * FROM `scan4_log` WHERE `timestamp` LIKE '%" . $date . "%' AND result = 'OPEND'";
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_row()) {
			if (!(in_array($row[2], $a_array))) {
				$a_array[] = $row[2];
			}
		}
		$result->free_result();
	}

	$entry = "";
	$query = "SELECT * FROM `scan4_homes` WHERE `city` LIKE '" . $city . "' AND (priority LIKE '1' OR priority LIKE '2' OR priority LIKE '3' OR priority LIKE '4' OR priority LIKE '5') AND `scan4_status` LIKE 'OPEN' AND (`phone1` != '' or `phone2` != '') ORDER BY `scan4_homes`.`dpnumber` DESC";
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_row()) {
			if ($row[1] === 'Insyte' && strlen($row[9]) < 3) {
				continue;
			} else {
				if (!(in_array($row[10], $a_array))) {
					$entry = $row;
					break;
				}
			}
		}
		$result->free_result();
	}

	if ($entry === "") { // no prios found > start looking 0 calls
		// echo "----- No PRIOS found -----\n";
		$query = "SELECT * FROM `scan4_homes` WHERE `city` LIKE '" . $city . "' AND `anruf1` IS NULL AND `scan4_status` LIKE 'OPEN' AND (`phone1` != '' or `phone2` != '') ORDER BY `scan4_homes`.`dpnumber` DESC";
		if ($result = $conn->query($query)) {
			while ($row = $result->fetch_row()) {
				if ($row[1] === 'Insyte' &&  strlen($row[9]) < 3) {
					continue;
				} else {
					if (!(in_array($row[10], $a_array))) {
						$entry = $row;
						break;
					}
				}
			}
			$result->free_result();
		}
	}
	if ($entry === "") { // no prios found > start looking 2 calls
		// echo "----- No PRIOS found -----\n";
		$query = "SELECT * FROM `scan4_homes` WHERE `city` LIKE '" . $city . "' AND `anruf1` IS NULL AND `anruf2` IS NULL AND `scan4_status` LIKE 'OPEN' AND (`phone1` != '' or `phone2` != '') ORDER BY `scan4_homes`.`dpnumber` DESC";
		if ($result = $conn->query($query)) {
			while ($row = $result->fetch_row()) {
				if ($row[1] === 'Insyte' &&  strlen($row[9]) < 3) {
					continue;
				} else {
					if (!(in_array($row[10], $a_array))) {
						$entry = $row;
						break;
					}
				}
			}
			$result->free_result();
		}
	}
	if ($entry === "") { // no prios found > start looking 3 calls
		// echo "----- No PRIOS found -----\n";
		$query = "SELECT * FROM `scan4_homes` WHERE `city` LIKE '" . $city . "' AND `anruf1` IS NULL AND `anruf2` IS NULL AND `anruf3` IS NULL AND `scan4_status` LIKE 'OPEN' AND (`phone1` != '' or `phone2` != '') ORDER BY `scan4_homes`.`dpnumber` DESC";
		if ($result = $conn->query($query)) {
			while ($row = $result->fetch_row()) {
				if ($row[1] === 'Insyte' &&  strlen($row[9]) < 3) {
					continue;
				} else {
					if (!(in_array($row[10], $a_array))) {
						$entry = $row;
						break;
					}
				}
			}
			$result->free_result();
		}
	}
	if ($entry === "") { // no new entrys found > start looking open entrys
		// echo "----- No NEW ENTRYS found -----\n";
		$entry = "";
		$query = "SELECT * FROM `scan4_homes` WHERE `city` LIKE '" . $city . "' AND `scan4_status` LIKE 'OPEN' AND (`phone1` != '' OR `phone2` != '' OR phone3 != '' OR phone4 != '') ORDER BY `scan4_homes`.`dpnumber` DESC";
		if ($result = $conn->query($query)) {
			while ($row = $result->fetch_row()) {
				if ($row[1] === 'Insyte' &&  strlen($row[9]) < 3) {
					continue;
				} else {
					if (!(in_array($row[10], $a_array))) {
						$entry = $row;
						break;
					}
				}
				//break;
			}
			$result->free_result();
		}
	}
	$conn->close();

	if ($entry === "") {
		echo json_encode('entry:empty');
	} else {
		safe_homeid_log($entry[10], $entry[7]);
		//echo "\n" . "homeid timeline: " . $entry[9] . "\n";
		echo json_encode($entry);

		/*
        echo implode(";", $entry);
        echo ";rowend;timelinestart;";
        //echo "----- DIV -----\n";
        echo get_timeline($entry[10]);
        echo ";timelineend;unitcount;";
        echo get_unit_count($entry[10], $entry[11]);
        echo ";relations;";
        echo db_get_relations($entry[10], $entry[11], $entry[14]);
        dblogentry($entry[10], $user, "OPEND", "", $city);
        */
	}
}




function load_opentickets($client)
{

	if (strpos($client, "Insyte") !== false) {
		$client = "Insyte";
	}
	if (strpos($client, "Moncobra") !== false) {
		$client = "Moncobra";
	}


	$exclude_ugg = '';
	$exclude_dgf = '';
	$exclude_gvg = '';
	if (!hasPerm(10)) {
		$exclude_ugg = 'UGG';
	}
	if (!hasPerm(11)) {
		$exclude_dgf = 'DGF';
	}
	if (!hasPerm(12)) {
		$exclude_gvg = 'GVG';
	}
	//$user = myusername();
	$conn = dbconnect();
	$date = date("Y-m-d");
	$calls = array();


	$query = "SELECT homeid FROM `scan4_calls` WHERE `call_date` LIKE '%" . $date . "%'";
	if ($result = $conn->query($query)) {
		# while fetch row assoc
		while ($row = mysqli_fetch_assoc($result)) {
			if (!(in_array($row['homeid'], $calls))) {
				$calls[] = $row['homeid'];
			}
		}
		$result->free_result();
	}
	$query = "SELECT homeid FROM `scan4_hbg` WHERE `date` >= '" . $date . "'";
	if ($result = $conn->query($query)) {
		# while fetch row assoc
		while ($row = mysqli_fetch_assoc($result)) {
			if (!(in_array($row['homeid'], $calls))) {
				$calls[] = $row['homeid'];
			}
		}
		$result->free_result();
	}

	$entry = "";
	$query = "SELECT * FROM `scan4_tickets` WHERE client LIKE '" . $client . "' AND status LIKE 'new'";
	if ($result = $conn->query($query)) {
		while ($row = mysqli_fetch_assoc($result)) {
			if ($row['status'] === 'new') {
				$tickets[] = $row['homeid'];
			}
		}
		$result->free_result();
	}

	$query = "SELECT * FROM `scan4_homes` WHERE `client` LIKE '" . $client . "' AND carrier != '" . $exclude_gvg . "' AND carrier != '" . $exclude_dgf . "' AND carrier != '" . $exclude_ugg . "'";
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_row()) {
			if ((in_array($row[10], $tickets))) {
				if (!(in_array($row[10], $calls))) {
					$entry = $row;
					break;
				}
			}
		}
		$result->free_result();
	}



	$conn->close();

	if ($entry === "") {
		echo json_encode('entry:empty');
	} else {
		safe_homeid_log($entry[10], $entry[7]);
		//echo "\n" . "homeid timeline: " . $entry[9] . "\n";
		echo json_encode($entry);

		/*
        echo implode(";", $entry);
        echo ";rowend;timelinestart;";
        //echo "----- DIV -----\n";
        echo get_timeline($entry[10]);
        echo ";timelineend;unitcount;";
        echo get_unit_count($entry[10], $entry[11]);
        echo ";relations;";
        echo db_get_relations($entry[10], $entry[11], $entry[14]);
        dblogentry($entry[10], $user, "OPEND", "", $city);
        */
	}
}



function load_entry_hbgs($homeid)
{

	$conn = dbconnect();
	$results = 0;
	ob_start();
	echo "@@relations@@";
	echo '<ul class="list-group phoner-hbg" id="hbglist">';
	// Fetch relations Between Adress. Show other units on same adress
	$query = "SELECT * FROM `scan4_hbgcheck` WHERE `homeid` = '" . $homeid . "' ORDER BY `id` DESC";
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_row()) {
			if ($row[4] === 'DONE CLOUD' || $row[4] === 'WRONG') {
				$results++;
				$folder = substr($row[8], 0, 4);
				$split = explode('_', $row[8], 2);
				if (isset($split[1])) {
					$filename = $split[1];
					$folder = $split[0];
				} else {
					$project = "undefind";
				}
				?>
				<li class="list-group-item">
					<div class="list-inner-wrapper">
						<div class="item-inner-box blue"><i class="ri-file-cloud-line"></i></i></div>
						<div class="item-inner-values">
							<div class="item-inner-row"><b>Grund</b> <?php echo $row[6] ?> / <b>Kommentar</b> <?php echo $row[7] ?> / <a target="_blank" href="https://crm.scan4-gmbh.de/uploads/hbgprotokolle/<?php echo $folder . '/'  . $filename ?>">Protokoll öffnen</a><span class="phoner-list-item-date"> - <?php echo date('d.m.Y', strtotime($row[9])) ?></span></div>
						</div>
					</div>
				</li>
		<?php
			}
		}
		$result->free_result();
	}
	echo '</ul><span id="hbgitemsfound" class="hidden">' . $results . '</span>';
}

function load_entry_relations($homeid)
{
	$html = "@@relations@@";
	$conn = dbconnect();
	// Fetch relations Between Adress. Show other units on same adress
	$query = "SELECT * FROM `scan4_homes` WHERE `homeid` = '" . $homeid . "' ";
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_row()) {
			$entry = $row;
		}
		$result->free_result();
	}
	$int = 0;
	$results = 0;
	//// Fetch relations under same adress
	$query = "SELECT * FROM `scan4_homes` WHERE homeid != '" . $homeid . "' AND ((city='" . $entry[7] . "' AND street = '" . $entry[3] . "' AND streetnumber = '" . $entry[4] . "' AND streetnumberadd = '" . $entry[5] . "') OR adressid = '" . $entry[11] . "')";
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_row()) {
			if ($int === 0) {
				$html .= '<div class="list-groupitem-relation-wrapper"> <div class="list-groupitem-header relations"><div class="item-inner-box-rel"><i class="ri-home-smile-2-line"></i></div> <b>Unter der Adresse gefundene Units:</b></div>';
			}
			$int++;
			$results++;
			if ($row[24] === "OPEN") {
				$status_scan4 = "blue";
			} else if ($row[24] === "CLOSED" || $row[24] === "STOPPED") {
				$status_scan4 = "red";
			} else if ($row[24] === "PLANNED") {
				$status_scan4 = "yellow";
			} else if ($row[24] === "DONE") {
				$status_scan4 = "green";
			}
			if ($row[19] === "OPEN") {
				$status_sys = "blue";
			} else if ($row[19] === "CLOSED" || $row[19] === "STOPPED") {
				$status_sys = "red";
			} else if ($row[19] === "PLANNED") {
				$status_sys = "yellow";
			} else if ($row[19] === "DONE") {
				$status_sys = "green";
			}
			$html .= '<div class="row relationsitems_item_wrapper"><div class="row list-entry-item-wrapper">
            <div class="col relation-item-iconspace"><i class="ri-share-forward-line"></i></div>
                <div class="col relation-item-content-info">
                <div class="row list-entry-item-content">
                    <span>' . $row[7] . ',</span>
                    <span>' . $row[3] . " " . $row[4] . $row[5] . ',</span>
                    <span><a id="list-entry-hrefhomeid" href="route.php?view=phonerapp?city=?homeid=' . $row[10] . '" target="_blank" ><b style="cursor:pointer;">' . $row[10] . '</b></a> Unit: ' . $row[6] . '</span>
                </div>
                <div class="row list-entry-item-content">
                    <span>' . $row[13] . " " . $row[12] .  '</span>';
			if (strlen($entry[14] > 3)) {
				$html .= '<span class="list-entry-phone"><a class="phonecall" href="tel:' . $row[14] . '"><i class="ri-phone-fill"></i>' . $row[14] . '</a></span>';
			}
			if (strlen($entry[15] > 3)) {
				$html .= '<span class="list-entry-phone"><a class="phonecall" href="tel:' . $row[15] . '"><i class="ri-phone-fill"></i>' . $row[15] . '</a></span>';
			}
			$html .= '  <span title="Status System" id="spanrelationitemstatus" class="relationsitems_item_status ' . $status_sys . '">' . $row[19] . '</span>
			<span title="Status Scan4" id="spanrelationitemstatussc4" class="relationsitems_item_status ' . $status_scan4 . '">' . $row[24] . '</span>
				</div> 
				</div>
                </div>
                </div>';
		}
		$html .= '</div>';
		$result->free_result();
	}
	if (strlen($entry[14] > 3)) {
		$phone1 = $entry[14];
	} else {
		$phone1 = "123123123123";
	}
	if (strlen($entry[15] > 3)) {
		$phone2 = $entry[15];
	} else {
		$phone2 = "123123123123";
	}
	//// Fetch relations under same phone number
	$int = 0;
	$query = "SELECT * FROM `scan4_homes` WHERE `homeid` != '" . $homeid . "' AND (`phone1` LIKE '" . $phone1 . "' OR `phone2` LIKE '" . $phone2 . "')";
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_row()) {
			if ($int === 0) {
				$html .= '<div class="list-groupitem-relation-wrapper"> <div class="list-groupitem-header relations"><div class="item-inner-box-rel"><i class="ri-phone-find-line"></i></div> <b>Unter der Rufnummer gefundene Units:</b></div>';
			}
			$int++;
			$results++;
			if ($row[24] === "OPEN") {
				$status_scan4 = "blue";
			} else if ($row[24] === "CLOSED" || $row[24] === "STOPPED") {
				$status_scan4 = "red";
			} else if ($row[24] === "PLANNED") {
				$status_scan4 = "yellow";
			} else if ($row[24] === "DONE") {
				$status_scan4 = "green";
			}
			if ($row[19] === "OPEN") {
				$status_sys = "blue";
			} else if ($row[19] === "CLOSED" || $row[24] === "STOPPED") {
				$status_sys = "red";
			} else if ($row[19] === "PLANNED") {
				$status_sys = "yellow";
			} else if ($row[19] === "DONE") {
				$status_sys = "green";
			}
			$html .= '<div class="row relationsitems_item_wrapper"><div class="row list-entry-item-wrapper">
            <div class="col relation-item-iconspace"><i class="ri-share-forward-line"></i></div>
                <div class="col relation-item-content-info">
                <div class="row list-entry-item-content">
                    <span>' . $row[7] . ',</span>
                    <span>' . $row[3] . " " . $row[4] . $row[5] . ',</span>
                    <span><a id="list-entry-hrefhomeid" href="route.php?view=phonerapp?city=?homeid=' . $row[10] . '" target="_blank" ><b style="cursor:pointer;">' . $row[10] . '</b></a> Unit: ' . $row[6] . '</span>
                </div>
                <div class="row list-entry-item-content">
                    <span>' . $row[13] . " " . $row[12] .  '</span>';
			if (strlen($entry[14] > 3)) {
				$html .= '<span class="list-entry-phone"><a class="phonecall" href="tel:' . $row[14] . '"><i class="ri-phone-fill"></i>' . $row[14] . '</a></span>';
			}
			if (strlen($entry[15] > 3)) {
				$html .= '<span class="list-entry-phone"><a class="phonecall" href="tel:' . $row[15] . '"><i class="ri-phone-fill"></i>' . $row[15] . '</a></span>';
			}
			$html .= '  <span title="Status System" id="spanrelationitemstatus" class="relationsitems_item_status ' . $status_sys . '">' . $row[19] . '</span>
			<span title="Status Scan4" id="spanrelationitemstatussc4" class="relationsitems_item_status ' . $status_scan4 . '">' . $row[24] . '</span></div></div>
                </div>
                </div>';
		}
		$html .= '</div>';
		$result->free_result();
	}
	$conn->close();
	$html .= '@@relations@@' . $results;
	echo $html;
}


function sortByUid($a, $b)
{
	return strcmp($a['uid'], $b['uid']);
}

function load_entry_timeline($homeid)
{
	$conn = dbconnect();
	global $currentuser;
	//$a_timeline[] = array();
	$query = "SELECT * FROM `scan4_calls` WHERE `homeid` = '" . $homeid . "'";
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_row()) {
			$a_timeline[] = array("date" => $row[2], "time" => $row[3], "user" => $row[4], 'source' => 'calls', "result" => $row[6], 'reason' => '', "comment" => $row[7], "uid" => $row[8]);
		}
		$result->free_result();
	}


	$query = "SELECT * FROM `scan4_userlog` WHERE `homeid` = '" . $homeid . "' AND source = 'hbgmodul' ";
	if ($result = $conn->query($query)) {
		while ($row = mysqli_fetch_assoc($result)) {
			$s = explode(' ', $row['datetime']);
			$date = $s[0];
			$time = $s[1];
			if (strpos($row['action1'], 'file passed') !== false || strpos($row['action1'], 'war nicht da') !== false || strpos($row['action1'], 'HBG nicht durchführbar') !== false || strpos($row['action1'], 'STOPPED') !== false) {
				$a_timeline[] = array("date" => $date, "time" => $time, "user" => $row['user'], 'source' => 'userlog', "result" => $row['action1'], 'reason' => '',  "comment" => $row['action2'], "uid" => $row['action3']);
			}
		}
		$result->free_result();
	}
	$query = "SELECT * FROM `scan4_hbgcheck` WHERE `homeid` = '" . $homeid . "'";
	if ($result = $conn->query($query)) {
		while ($row = mysqli_fetch_assoc($result)) {
			$s = explode(' ', $row['datetime']);
			$date = $s[0];
			$time = $s[1];
			$a_timeline[] = array("date" => $date, "time" => $time, "user" => $row['user'], 'source' => 'hbgcheck', "result" => $row['status'], 'reason' => $row['reason'],  "comment" => $row['comment'], "uid" => $row['ident']);
		}
		$result->free_result();
	}

	$ticket = null;
	$query = "SELECT * FROM `scan4_tickets` WHERE `homeid` = '" . $homeid . "'";
	if ($result = $conn->query($query)) {
		while ($row = mysqli_fetch_assoc($result)) {
			//$a_timeline[] = array("date" => $row['date'], "time" => $row['time'], "user" => $row['user'], 'source' => 'ticket', "result" => 'ticket erstellt', 'reason' => $row['object_title'],  "comment" => $row['object_content']);
			$a_timeline[] = array("date" => $row['date'], "time" => $row['time'], "user" => $row['user'], 'source' => 'ticket', "result" => 'ticket erstellt', 'reason' => $row['object_title'],  "comment" => $row['object_content'], "uid" => "");

			if ($row['status'] !== 'closed') {
				$ticket = $row;
			}
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
			$a_timeline[] = array("date" => $date, "time" => $time, "user" => $row['user'], 'source' => 'tickets', "result" => 'Ticket', 'reason' => '',  "comment" => $row['action1'], "uid" => '');
		}
		$result->free_result();
	}
	arsort($a_timeline);
	$a_timeline = array_values($a_timeline);

	$length = count($a_timeline);

	// array merge a_timeline and ticket_timeline
	//$a_timeline = array_merge($a_timeline, $ticket_timeline);
	echo '<div class="hidden">';
	echo $length;
	echo '<pre>';
	print_r($a_timeline);
	echo '</pre>';
	echo '</div>';






	$conn->close();
	$length = count($a_timeline);
	ob_start();

	if (is_array($ticket)) {
		if ($ticket['priority'] === "1") $priolevel = '<span class="ticketpriolvl red"><i class="ri-24-hours-line"></i>&nbsp;Urgent</span>';
		if ($ticket['priority'] === "2") $priolevel = '<span class="ticketpriolvl orange"><i class="ri-fire-line"></i>&nbsp;Important</span>';
		if ($ticket['priority'] === "3") $priolevel = '<span class="ticketpriolvl blue"><i class="ri-check-double-line"></i>&nbsp;ToDo</span>';
		if ($ticket['status'] === "pending") $ticketstate = 'pending';
		if ($ticket['status'] === "new") $ticketstate = 'new';

		?>

		<li class="list-group-item">
			<div class="list-inner-wrapper ticket ">
				<div class="item-inner-box blue ticket "><i class="ri-coupon-line"></i></div>
				<div class="item-inner-values">
					<div class="item-inner-row tickethead"><b>Ticket vom <?php echo date('d.m.Y', strtotime($ticket['date'])) ?></b><?php echo $priolevel ?><span class="ticketbuttonstate">Bearbeiten</span><span class="ticket_state_<?php echo $ticket['status'] ?>"><?php echo $ticket['status'] ?></span></div>
					<div class="item-inner-row tickettitle"><?php echo $ticket['object_title'] ?></div>
					<div class="item-inner-row tickettext"><?php echo $ticket['object_content'] ?></div>
				</div>
			</div>
		</li>
	<?php
	}
	?>



	<?php
	if ($length === 0) {
	?>
		<li class="list-group-item emptyentry">
			<div class="notimeline"><span><i class="ri-ghost-line"></i> Zu diesem Kunden gibt es noch keine Einträge</span></div>
		</li>
		<?php
	} else {
		$hbgcounter = 0;
		for ($i = 0; $i < $length; $i++) {
			if ($a_timeline[$i]["result"] === "") {
			} else {
				// get the first 5 chars of the time

				$a_timeline[$i]["time"] = substr($a_timeline[$i]["time"], 0, 5);

		?>
				<li id="<?php echo $a_timeline[$i]["uid"] ?>" class="list-group-item <?php echo $a_timeline[$i]["source"] ?>">
					<div class="list-inner-wrapper">
						<?php if ($a_timeline[$i]["result"] === "nicht erreicht") { // result = equal to nicht erreicht
						?>
							<div class="item-inner-box blue"><i class="ri-separator"></i></div>
							<div class="item-inner-values">
								<div class="item-inner-row"><?php echo $a_timeline[$i]["user"] ?> hat den Kunden <b>nicht erreicht</b><span class="phoner-list-item-date"> - <?php echo date('d.m.Y', strtotime($a_timeline[$i]["date"])) ?> - <?php echo $a_timeline[$i]["time"] ?> Uhr</span></div>
							</div>
							<?php } elseif ($a_timeline[$i]["source"] === 'hbgcheck') {

							if (strpos($a_timeline[$i]["result"], 'DONE') !== false) {
							?>
								<div class="item-inner-box green"><i class="ri-check-line"></i></div>
								<div class="item-inner-values">
									<div class="item-inner-row"><?php echo $a_timeline[$i]["user"] ?> HBG Check Ergebnis <span class="cspill green"><?php echo $a_timeline[$i]["result"] ?></span> <span class="phoner-list-item-date"> - <?php echo date('d.m.Y', strtotime($a_timeline[$i]["date"])) ?> - <?php echo $a_timeline[$i]["time"] ?> Uhr</span></div>
									<?php if (strlen($a_timeline[$i]["comment"]) > 2) { ?><div class="item-inner-comment show"><i class="ri-message-3-line"></i> <?php echo $a_timeline[$i]["comment"] ?></div><?php } ?>
								</div>
							<?php
							} else if ($a_timeline[$i]["result"] === "OPEN") {
							?>
								<div class="item-inner-box blue"><i class="ri-arrow-go-forward-line"></i></div>
								<div class="item-inner-values">
									<div class="item-inner-row"><?php echo $a_timeline[$i]["user"] ?> HBG Check Ergebnis <span class="cspill blue"><?php echo $a_timeline[$i]["result"] ?></span> <span class="phoner-list-item-date"> - <?php echo date('d.m.Y', strtotime($a_timeline[$i]["date"])) ?> - <?php echo $a_timeline[$i]["time"] ?> Uhr</span></div>
									<?php if (strlen($a_timeline[$i]["comment"]) > 2) { ?><div class="item-inner-comment show"><i class="ri-message-3-line"></i> <?php echo $a_timeline[$i]["comment"] ?></div><?php } ?>
								</div>
							<?php
							} else if ($a_timeline[$i]["result"] === "MISSING") {
							?>
								<div class="item-inner-box blue"><i class="ri-arrow-go-forward-line"></i></div>
								<div class="item-inner-values">
									<div class="item-inner-row"><?php echo $a_timeline[$i]["user"] ?> HBG Check Ergebnis <span class="cspill red"><?php echo $a_timeline[$i]["result"] ?></span> <span class="phoner-list-item-date"> - <?php echo date('d.m.Y', strtotime($a_timeline[$i]["date"])) ?> - <?php echo $a_timeline[$i]["time"] ?> Uhr</span></div>
									<?php if (strlen($a_timeline[$i]["comment"]) > 2) { ?><div class="item-inner-comment show"><i class="ri-message-3-line"></i> <?php echo $a_timeline[$i]["comment"] ?></div><?php } ?>
								</div>
							<?php
							} else if ($a_timeline[$i]["result"] === "STOPPED") {
								$tempresult = str_replace("hbgmodul set appt status to ", "", $a_timeline[$i]["result"]);
							?>
								<div class="item-inner-box red"><i class="ri-close-line"></i></div>
								<div class="item-inner-values">
									<div class="item-inner-row"><?php echo $a_timeline[$i]["user"] ?> HBG Check Ergebnis <span class="cspill red"><?php echo $a_timeline[$i]["result"] ?></span> <span class="phoner-list-item-date"> - <?php echo date('d.m.Y', strtotime($a_timeline[$i]["date"])) ?> - <?php echo $a_timeline[$i]["time"] ?> Uhr</span></div>
									<?php if (strlen($a_timeline[$i]["comment"]) > 2) { ?><div class="item-inner-comment show"><i class="ri-message-3-line"></i> <?php echo $a_timeline[$i]["comment"] ?></div><?php } ?>
								</div>
								<?php
							}
						} else if (strpos($a_timeline[$i]["result"], 'file passed') !== false) {
							foreach ($a_timeline as $key => $sub_arr) {
								if ($sub_arr["uid"] === $a_timeline[$i]["uid"]) {
									if (strpos($sub_arr["result"], 'DONE') !== false) {
								?>
										<div class="sub-itemwrapper">
											<div class="item-inner-box green"><i class="ri-check-line"></i></div>
											<div class="item-inner-values">
												<div class="item-inner-row"><?php echo $sub_arr["user"] ?> HBG Check Ergebnis <span class="cspill green"><?php echo $sub_arr["result"] ?></span> <span class="phoner-list-item-date"> - <?php echo date('d.m.Y', strtotime($sub_arr["date"])) ?> - <?php echo substr($sub_arr["time"], 0, 5) ?> Uhr</span></div>
												<?php if (strlen($sub_arr["comment"]) > 2) { ?><div class="item-inner-comment show"><i class="ri-message-3-line"></i> <?php echo $sub_arr["comment"] ?></div><?php } ?>
											</div>
										</div>
									<?php } else if ($sub_arr["result"] === "OPEN") {
									?>
										<div class="sub-itemwrapper">
											<div class="item-inner-box blue"><i class="ri-arrow-go-forward-line"></i></div>
											<div class="item-inner-values">
												<div class="item-inner-row"><?php echo $sub_arr["user"] ?> HBG Check Ergebnis <span class="cspill blue"><?php echo $sub_arr["result"] ?></span> <span class="phoner-list-item-date"> - <?php echo date('d.m.Y', strtotime($sub_arr["date"])) ?> - <?php echo substr($sub_arr["time"], 0, 5) ?> Uhr</span></div>
												<?php if (strlen($sub_arr["comment"]) > 2) { ?><div class="item-inner-comment show"><i class="ri-message-3-line"></i> <?php echo $sub_arr["comment"] ?></div><?php } ?>
											</div>
										</div>
							<?php
									}
									break;
								}
							}
							?>
							<div class="item-inner-box grey"><i class="ri-list-check"></i></div>
							<div class="item-inner-values">
								<div class="item-inner-row"><?php echo $a_timeline[$i]["user"] ?> eine HBG erfolgreich abgeschlossen <span class="phoner-list-item-date"> - <?php echo date('d.m.Y', strtotime($a_timeline[$i]["date"])) ?> - <?php echo $a_timeline[$i]["time"] ?> Uhr</span></div>
							</div>
							<?php
						} else if (strpos($a_timeline[$i]["result"], 'nicht da') !== false || strpos($a_timeline[$i]["result"], 'HBG nicht durchführbar') !== false) {
							$tempresult = str_replace("hbgmodul set appt status to ", "", $a_timeline[$i]["result"]);
							foreach ($a_timeline as $key => $sub_arr) {
								if ($sub_arr["uid"] === $a_timeline[$i]["uid"]) {
									if (strpos($sub_arr["result"], 'DONE') !== false) {
							?>
										<div class="sub-itemwrapper">
											<div class="item-inner-box green"><i class="ri-check-line"></i></div>
											<div class="item-inner-values">
												<div class="item-inner-row"><?php echo $sub_arr["user"] ?> HBG Check Ergebnis <span class="cspill green"><?php echo $sub_arr["result"] ?></span> <span class="phoner-list-item-date"> - <?php echo date('d.m.Y', strtotime($sub_arr["date"])) ?> - <?php echo substr($sub_arr["time"], 0, 5) ?> Uhr</span></div>
												<?php if (strlen($sub_arr["comment"]) > 2) { ?><div class="item-inner-comment show"><i class="ri-message-3-line"></i> <?php echo $sub_arr["comment"] ?></div><?php } ?>
											</div>
										</div>
									<?php } else if ($sub_arr["result"] === "OPEN") {
									?>
										<div class="sub-itemwrapper">
											<div class="item-inner-box blue"><i class="ri-arrow-go-forward-line"></i></div>
											<div class="item-inner-values">
												<div class="item-inner-row"><?php echo $sub_arr["user"] ?> HBG Check Ergebnis <span class="cspill blue"><?php echo $sub_arr["result"] ?></span> <span class="phoner-list-item-date"> - <?php echo date('d.m.Y', strtotime($sub_arr["date"])) ?> - <?php echo substr($sub_arr["time"], 0, 5) ?> Uhr</span></div>
												<?php if (strlen($sub_arr["comment"]) > 2) { ?><div class="item-inner-comment show"><i class="ri-message-3-line"></i> <?php echo $sub_arr["comment"] ?></div><?php } ?>
											</div>
										</div>
							<?php
									}
									break;
								}
							}
							?>
							<div class="item-inner-box grey"><i class="ri-list-check"></i></div>
							<div class="item-inner-values">
								<div class="item-inner-row"><?php echo $a_timeline[$i]["user"] ?> hat eine HBG abgebrochen: <?php echo $tempresult ?> <span class="phoner-list-item-date"> - <?php echo date('d.m.Y', strtotime($a_timeline[$i]["date"])) ?> - <?php echo $a_timeline[$i]["time"] ?> Uhr</span></div>
								<?php if (strlen($a_timeline[$i]["comment"]) > 2) { ?><div class="item-inner-comment show"><i class="ri-message-3-line"></i> <?php echo $a_timeline[$i]["comment"] ?></div><?php } ?>
							</div>
						<?php
						} elseif (strpos($a_timeline[$i]["result"], 'edit:') !== false) {
							$a_timeline[$i]["result"] = str_replace('edit:', '', $a_timeline[$i]["result"]);
						?>
							<div class="item-inner-box blue"><i class="ri-settings-4-line"></i></div>
							<div class="item-inner-values">
								<div class="item-inner-row"><?php echo $a_timeline[$i]["user"] ?> hat <?php echo $a_timeline[$i]["result"] ?><span class="phoner-list-item-date"> - <?php echo date('d.m.Y', strtotime($a_timeline[$i]["date"])) ?> - <?php echo $a_timeline[$i]["time"] ?> Uhr</span></div>
								<?php if (strlen($a_timeline[$i]["comment"]) > 2) { ?><div class="item-inner-comment show"><i class="ri-chat-1-line"></i> <?php echo $a_timeline[$i]["comment"] ?></div><?php } ?>
							</div>
						<?php } elseif ($a_timeline[$i]["result"] === "Ticket") {
						?>
							<div class="item-inner-box blue"><i class="ri-coupon-line"></i></div>
							<div class="item-inner-values">
								<div class="item-inner-row"><?php echo $a_timeline[$i]["user"] ?> hat den <?php echo $a_timeline[$i]["comment"] ?><span class="phoner-list-item-date"> - <?php echo date('d.m.Y', strtotime($a_timeline[$i]["date"])) ?> - <?php echo $a_timeline[$i]["time"] ?> Uhr</span></div>

							</div>
						<?php } elseif ($a_timeline[$i]["result"] === "import") {
						?>
							<div class="item-inner-box blue"><i class="ri-upload-cloud-line"></i></div>
							<div class="item-inner-values">
								<div class="item-inner-row"><?php echo $a_timeline[$i]["user"] ?> import // <?php echo $a_timeline[$i]["comment"] ?><span class="phoner-list-item-date"> - <?php echo date('d.m.Y', strtotime($a_timeline[$i]["date"])) ?> - <?php echo $a_timeline[$i]["time"] ?> Uhr</span></div>

							</div>
						<?php } elseif ($a_timeline[$i]["result"] === "system") {
						?>
							<div class="item-inner-box blue"><i class="ri-message-2-line"></i></div>
							<div class="item-inner-values">
								<div class="item-inner-row"><?php echo $a_timeline[$i]["user"] ?> // <?php echo $a_timeline[$i]["comment"] ?><span class="phoner-list-item-date"> - <?php echo date('d.m.Y', strtotime($a_timeline[$i]["date"])) ?> - <?php echo $a_timeline[$i]["time"] ?> Uhr</span></div>

							</div>
						<?php } elseif ($a_timeline[$i]["result"] === "Wiedervorlage") {
						?>
							<div class="item-inner-box blue"><i class="ri-history-line"></i></div>
							<div class="item-inner-values">
								<div class="item-inner-row"><?php echo $a_timeline[$i]["user"] ?> hat eine <b>Wiedervorlage</b> erstellt<span class="phoner-list-item-date"> - <?php echo date('d.m.Y', strtotime($a_timeline[$i]["date"])) ?> - <?php echo $a_timeline[$i]["time"] ?> Uhr</span></div>
								<div class="item-inner-comment show"><i class="ri-chat-1-line"></i> <?php echo $a_timeline[$i]["comment"] ?></div>
							</div>
						<?php } elseif ($a_timeline[$i]["result"] === "ticket erstellt") {
						?>
							<div class="item-inner-box blue"><i class="ri-coupon-line"></i></div>
							<div class="item-inner-values">
								<div class="item-inner-row"><?php echo $a_timeline[$i]["user"] ?> hat ein <b>Ticket</b> erstellt<span class="phoner-list-item-date"> - <?php echo date('d.m.Y', strtotime($a_timeline[$i]["date"])) ?> - <?php echo $a_timeline[$i]["time"] ?> Uhr</span></div>
								<div class="item-inner-comment show"><i class="ri-chat-1-line"></i> <?php echo $a_timeline[$i]["comment"] ?></div>
							</div>
						<?php
							// =================================///
							//          keine HBG  //
							// =================================///
						} elseif ($a_timeline[$i]["result"] === "Falscher Ansprechpartner") {
						?>
							<div class="item-inner-box red"><i class="ri-user-unfollow-line"></i></div>
							<div class="item-inner-values">
								<div class="item-inner-row"><?php echo $a_timeline[$i]["user"] ?> hat einen <b>Falscher Ansprechpartner</b> erreicht<span class="phoner-list-item-date"> - <?php echo date('d.m.Y', strtotime($a_timeline[$i]["date"])) ?> - <?php echo $a_timeline[$i]["time"] ?> Uhr</span></div>
								<?php if (strlen($a_timeline[$i]["comment"]) > 2) { ?><div class="item-inner-comment show"><i class="ri-chat-1-line"></i> <?php echo $a_timeline[$i]["comment"] ?></div><?php } ?>
							</div>
						<?php
						} elseif ($a_timeline[$i]["result"] === "Keine HBG - Besonderer Grund") {
						?>
							<div class="item-inner-box red"><i class="ri-close-line"></i></div>
							<div class="item-inner-values">
								<div class="item-inner-row"><?php echo $a_timeline[$i]["user"] ?>, <b>Keine HBG</b> - Besonderer Grund<span class="phoner-list-item-date"> - <?php echo date('d.m.Y', strtotime($a_timeline[$i]["date"])) ?> - <?php echo $a_timeline[$i]["time"] ?> Uhr</span></div>
								<?php if (strlen($a_timeline[$i]["comment"]) > 2) { ?><div class="item-inner-comment show"><i class="ri-chat-1-line"></i> <?php echo $a_timeline[$i]["comment"] ?></div><?php } ?>
							</div>
						<?php
						} elseif ($a_timeline[$i]["result"] === "Keine HBG - Kunde verweigert HBG") {
						?>
							<div class="item-inner-box red"><i class="ri-close-line"></i></div>
							<div class="item-inner-values">
								<div class="item-inner-row"><?php echo $a_timeline[$i]["user"] ?>, <b>Keine HBG</b> - Kunde verweigert HBG<span class="phoner-list-item-date"> - <?php echo date('d.m.Y', strtotime($a_timeline[$i]["date"])) ?> - <?php echo $a_timeline[$i]["time"] ?> Uhr</span></div>
								<?php if (strlen($a_timeline[$i]["comment"]) > 2) { ?><div class="item-inner-comment show"><i class="ri-chat-1-line"></i> <?php echo $a_timeline[$i]["comment"] ?></div><?php } ?>
							</div>
						<?php
						} elseif ($a_timeline[$i]["result"] === "Keine HBG - Falsche Nummer") {
						?>
							<div class="item-inner-box red"><i class="ri-close-line"></i></div>
							<div class="item-inner-values">
								<div class="item-inner-row"><?php echo $a_timeline[$i]["user"] ?>, <b>Keine HBG</b> - Falsche Nummer<span class="phoner-list-item-date"> - <?php echo date('d.m.Y', strtotime($a_timeline[$i]["date"])) ?> - <?php echo $a_timeline[$i]["time"] ?> Uhr</span></div>
								<?php if (strlen($a_timeline[$i]["comment"]) > 2) { ?><div class="item-inner-comment show"><i class="ri-chat-1-line"></i> <?php echo $a_timeline[$i]["comment"] ?></div><?php } ?>
							</div>
						<?php
						} elseif ($a_timeline[$i]["result"] === "Keine HBG - Nummer nicht vergeben") {
						?>
							<div class="item-inner-box red"><i class="ri-close-line"></i></div>
							<div class="item-inner-values">
								<div class="item-inner-row"><?php echo $a_timeline[$i]["user"] ?>, <b>Keine HBG</b> - Nummer nicht vergeben<span class="phoner-list-item-date"> - <?php echo date('d.m.Y', strtotime($a_timeline[$i]["date"])) ?> - <?php echo $a_timeline[$i]["time"] ?> Uhr</span></div>
								<?php if (strlen($a_timeline[$i]["comment"]) > 2) { ?><div class="item-inner-comment show"><i class="ri-chat-1-line"></i> <?php echo $a_timeline[$i]["comment"] ?></div><?php } ?>
							</div>
						<?php
						} elseif ($a_timeline[$i]["result"] === "Keine HBG - Keine Telefonnummer") {
						?>
							<div class="item-inner-box red"><i class="ri-close-line"></i></div>
							<div class="item-inner-values">
								<div class="item-inner-row"><?php echo $a_timeline[$i]["user"] ?>, <b>Keine HBG</b> - Keine Telefonnummer<span class="phoner-list-item-date"> - <?php echo date('d.m.Y', strtotime($a_timeline[$i]["date"])) ?> - <?php echo $a_timeline[$i]["time"] ?> Uhr</span></div>
								<?php if (strlen($a_timeline[$i]["comment"]) > 2) { ?><div class="item-inner-comment show"><i class="ri-chat-1-line"></i> <?php echo $a_timeline[$i]["comment"] ?></div><?php } ?>
							</div>
						<?php
						} elseif ($a_timeline[$i]["result"] === "Keine HBG - Nummer nicht vergeben") {
						?>
							<div class="item-inner-box red"><i class="ri-close-line"></i></div>
							<div class="item-inner-values">
								<div class="item-inner-row"><?php echo $a_timeline[$i]["user"] ?>, <b>Keine HBG</b> - Nummer nicht vergeben<span class="phoner-list-item-date"> - <?php echo date('d.m.Y', strtotime($a_timeline[$i]["date"])) ?> - <?php echo $a_timeline[$i]["time"] ?> Uhr</span></div>
								<?php if (strlen($a_timeline[$i]["comment"]) > 2) { ?><div class="item-inner-comment show"><i class="ri-chat-1-line"></i> <?php echo $a_timeline[$i]["comment"] ?></div><?php } ?>
							</div>
						<?php
						} elseif ($a_timeline[$i]["result"] === "Keine HBG - Falsche Adresse") {
						?>
							<div class="item-inner-box red"><i class="ri-close-line"></i></div>
							<div class="item-inner-values">
								<div class="item-inner-row"><?php echo $a_timeline[$i]["user"] ?>, <b>Keine HBG</b> - Falsche Adresse<span class="phoner-list-item-date"> - <?php echo date('d.m.Y', strtotime($a_timeline[$i]["date"])) ?> - <?php echo $a_timeline[$i]["time"] ?> Uhr</span></div>
								<?php if (strlen($a_timeline[$i]["comment"]) > 2) { ?><div class="item-inner-comment show"><i class="ri-chat-1-line"></i> <?php echo $a_timeline[$i]["comment"] ?></div><?php } ?>
							</div>
						<?php
						} elseif ($a_timeline[$i]["result"] === "Keine HBG - Falsche Daten") {
						?>
							<div class="item-inner-box red"><i class="ri-close-line"></i></div>
							<div class="item-inner-values">
								<div class="item-inner-row"><?php echo $a_timeline[$i]["user"] ?>, <b>Keine HBG</b> - Falsche Daten<span class="phoner-list-item-date"> - <?php echo date('d.m.Y', strtotime($a_timeline[$i]["date"])) ?> - <?php echo $a_timeline[$i]["time"] ?> Uhr</span></div>
								<?php if (strlen($a_timeline[$i]["comment"]) > 2) { ?><div class="item-inner-comment show"><i class="ri-chat-1-line"></i> <?php echo $a_timeline[$i]["comment"] ?></div><?php } ?>
							</div>
						<?php
						} elseif ($a_timeline[$i]["result"] === "Keine HBG - Kunde sagt, er habe gekündigt") {
						?>
							<div class="item-inner-box red"><i class="ri-close-line"></i></div>
							<div class="item-inner-values">
								<div class="item-inner-row"><?php echo $a_timeline[$i]["user"] ?>, <b>Keine HBG</b> - Kunde sagt, er habe gekündigt<span class="phoner-list-item-date"> - <?php echo date('d.m.Y', strtotime($a_timeline[$i]["date"])) ?> - <?php echo $a_timeline[$i]["time"] ?> Uhr</span></div>
								<?php if (strlen($a_timeline[$i]["comment"]) > 2) { ?><div class="item-inner-comment show"><i class="ri-chat-1-line"></i> <?php echo $a_timeline[$i]["comment"] ?></div><?php } ?>
							</div>
						<?php
							// =================================================///
							//          HBG  
							// =================================================///

						} elseif ($a_timeline[$i]["result"] === "HBG Storno") {
							$a_timeline_hbg = get_timelinehbg($a_timeline[$i]["uid"]);
						?>
							<div class="item-inner-box"><i class="ri-close-circle-line"></i></div>
							<div class="item-inner-body">
								<div class="item-inner-values"><?php echo $a_timeline[$i]["user"] ?> hat eine <b>HBG storniert</b><span class="phoner-list-item-date"> - <?php echo date('d.m.Y', strtotime($a_timeline[$i]["date"])) ?> - <?php echo $a_timeline[$i]["time"] ?> Uhr</span></div>
								<div class="item-inner-box-hbgwrapper">
									<i class="fa-solid fa-arrow-turn-up"></i>
									<div class="item-inner-inner-values hbg">Am <b><?php echo $a_timeline_hbg[0]["date"] ?></b> um <b><?php echo $a_timeline_hbg[0]["time"] ?></b> für <b><?php echo $a_timeline_hbg[0]["hausbegeher"] ?></b>.</div>
								</div>
							</div>
						<?php } elseif ($a_timeline[$i]["result"] === "HBG moved") {
							$hbgcounter++;
							$a_timeline_hbg = get_timelinehbg($a_timeline[$i]["uid"]);
						?>
							<div id="<?php echo $a_timeline_hbg[0]["uid"] ?>" class="item-inner-box yellow 
							<?php
							$current_date_time = date('Y-m-d H:i:s');
							$appt_date_time = $a_timeline_hbg[0]['date'] . ' ' . $a_timeline_hbg[0]['time'] . ':00';
							if (($hbgcounter === 1 &&
								$a_timeline_hbg[0]['status'] === 'PLANNED' &&
								strlen($a_timeline_hbg[0]['appt_status']) === 0 &&
								$current_date_time < $appt_date_time) || (hasPerm(2)) || $currentuser === 'LenaGolembowskiA') {
								echo 'hbgicon';
							}
							?>"><i class="ri-calendar-todo-line"></i></div>
							<div class="item-inner-body">
								<div class="item-inner-values"><?php echo $a_timeline[$i]["user"] ?> hat eine <b>HBG verschoben</b><span class="phoner-list-item-date"> - <?php echo date('d.m.Y', strtotime($a_timeline[$i]["date"])) ?> - <?php echo $a_timeline[$i]["time"] ?> Uhr</span></div>
								<div class="item-inner-box-hbgwrapper">
									<i class="fa-solid fa-arrow-turn-up"></i>
									<div class="item-inner-inner-values hbg"><span class="hidden"><?php echo strlen($a_timeline_hbg[0]['appt_status']) ?></span>Am <b><?php echo $a_timeline_hbg[0]["date"] ?></b> um <b><?php echo $a_timeline_hbg[0]["time"] ?></b> für <b><?php echo $a_timeline_hbg[0]["hausbegeher"] ?></b>.</div>
								</div>
							</div>
						<?php
						} elseif ($a_timeline[$i]["result"] === "HBG erstellt") {
							$hbgcounter++;
							$a_timeline_hbg = get_timelinehbg($a_timeline[$i]["uid"]);
							echo '<span class="hidden">hbgcounterin:' . $hbgcounter . ' status:' . $a_timeline_hbg[0]['status'] . '</span>';
						?>

							<div id="<?php echo $a_timeline_hbg[0]["uid"] ?>" class="item-inner-box yellow 
							<?php
							$current_date_time = date('Y-m-d H:i:s');
							$appt_date_time = $a_timeline_hbg[0]['date'] . ' ' . $a_timeline_hbg[0]['time'] . ':00';
							if (($hbgcounter === 1 &&
								$a_timeline_hbg[0]['status'] === 'PLANNED' &&
								strlen($a_timeline_hbg[0]['appt_status']) === 0 &&
								$current_date_time < $appt_date_time) || (hasPerm(2)) || $currentuser === 'LenaGolembowskiA') {
								echo 'hbgicon';
							}
							?>"><i class="ri-calendar-todo-line"></i></div>
							<div class="item-inner-body">
								<div class="item-inner-values">
									<div class="item-inner-row"><?php echo $a_timeline[$i]["user"] ?> hat eine <b>HBG erstellt</b><span class="phoner-list-item-date"> - <?php echo date('d.m.Y', strtotime($a_timeline[$i]["date"])) ?> - <?php echo $a_timeline[$i]["time"] ?> Uhr</span></div>
									<?php if (strlen($a_timeline[$i]["comment"]) > 2) { ?><div class="item-inner-comment show"><i class="ri-chat-1-line"></i> <?php echo $a_timeline[$i]["comment"] ?></div><?php } ?>
								</div>

								<div class="item-inner-box-hbgwrapper">
									<i class="fa-solid fa-arrow-turn-up"></i>

									<div class="item-inner-inner-values hbg">Am <b><?php echo $a_timeline_hbg[0]["date"] ?></b> um <b><?php echo $a_timeline_hbg[0]["time"] ?></b> für <b><?php echo $a_timeline_hbg[0]["hausbegeher"] ?></b>.</div>
								</div>
							</div>

						<?php } ?>


					</div>
				</li>
		<?php
			}
		}
	}

	ob_get_contents();
	//echo $output;
}

function load_entry_logfile($homeid)
{

	$conn = dbconnect();
	//$a_timeline[] = array();


	$query = "SELECT * FROM `scan4_homes_history` WHERE `homeid` = '" . $homeid . "'";
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_assoc()) {
			$t = explode(' ', $row['timestamp']);
			$date = '';
			$time = '';
			$date = $t[0];
			$time = substr($t[1], 0, 5);
			$log[] = array("date" => $date, "time" => $time, "user" => $row['col2'], 'source' => $row['col1'], "action" => $row['col3'], 'comment' => $row['col4'], "set1" => $row['col5'], "set2" => $row['col6'], "set3" => $row['col7'], "set4" => $row['col8']);
		}
		$result->free_result();
	}
	$query = "SELECT * FROM `scan4_userlog` WHERE `homeid` = '" . $homeid . "'";
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_assoc()) {
			$t = explode(' ', $row['datetime']);
			$date = '';
			$time = '';
			$date = $t[0];
			$time = substr($t[1], 0, 8);
			$log[] = array("date" => $date, "time" => $time, "user" => $row['user'], 'source' => $row['source'], "action" => $row['action1'], 'comment' => $row['action2'], "set1" => $row['action3'], "set2" => $row['action4']);
		}
		$result->free_result();
	}





	arsort($log);
	$log = array_values($log);
	$length = count($log);



	ob_start();
	for ($i = 0; $i < $length; $i++) {
		// if $i is equal to even echo this
		if ($i % 2 == 0) {
			$rowcolor = 'even';
		} else {
			$rowcolor = 'odd';
		}
		?>
		<li class="logfileitem <?php echo $rowcolor ?>">
			<div class="logitem-wrapper">
				<div class="logitem-body row smallfont">
					<div class="col-auto"><?php echo $log[$i]['date'] ?></div>
					<div class="col-auto"><?php echo $log[$i]['time'] ?></div>
				</div>
				<div class="logitem-body row">
					<div class="col-auto"><?php echo $log[$i]['user'] ?></div>
					<div class="col-auto"><?php echo $log[$i]['source'] ?></div>
					<div class="col-auto"><?php echo $log[$i]['action'] ?></div>
					<div class="col-auto"><?php echo $log[$i]['comment'] ?></div>
					<div class="col-auto"><?php echo $log[$i]['set1'] ?></div>
					<div class="col-auto"><?php echo $log[$i]['set2'] ?></div>
					<div class="col-auto"><?php echo $log[$i]['set3'] ?></div>
				</div>
		</li>

	<?php
	}
	?>




	<?php

	// array merge a_timeline and ticket_timeline
	//$a_timeline = array_merge($a_timeline, $ticket_timeline);
	echo '<div class="hidden">';
	echo $length;
	echo '<pre>';
	print_r($log);
	echo '</pre>';
	echo '</div>';

	$conn->close();

	ob_get_contents();
}




function load_citycards($client)
{
	if (!hasPerm(8) && $client === 'Insyte') { // 8 = Insyte
		die();
	}
	if (!hasPerm(9) && $client === 'Moncobra') { // 9 = Moncobra
		die();
	}
	if (!hasPerm(17) && $client === 'FOL') { // 9 = Moncobra
		die();
	}
	load_ticketcard($client);
	$conn = dbconnect();
	$count_card = 0;

	$date = date("Y-m-d");
	$datetime = date('Y-m-d H:i:s', strtotime('-10 minutes'));

	$array_citys = load_citystats($client);
	$array_log = array();
	$query = "SELECT * FROM `scan4_log` WHERE timestamp >= '" . $datetime . "' ORDER BY `scan4_log`.`id` DESC";
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_row()) {
			$array_log += [$row[3] => $row[6]];
		}
		$result->free_result();
	}
	// close conn
	$conn->close();
	//echo '<pre>';
	//echo print_r($array_log);
	//echo '</pre>';

	for ($i = 0; $i < count($array_citys); $i++) {
		// check permission to see the carrier
		if (!hasPerm(10) && $array_citys[$i]["carrier"] === 'UGG') {
			$allow = false;
			continue;
		}
		if (!hasPerm(11) && $array_citys[$i]["carrier"] === 'DGF') {
			$allow = false;
			continue;
		}
		if (!hasPerm(12) && $array_citys[$i]["carrier"] === 'GVG') {
			$allow = false;
			continue;
		}
		if (!hasPerm(18) && $array_citys[$i]["carrier"] === 'GlasfaserPlus') {
			$allow = false;
			continue;
		}


		ob_start();

		$count_card++;
		//$cityid = str_replace(" ","__",$row[1]);
		$cityid = $array_citys[$i]["city"];
		// zero ?! for opacity prevent uneven divs in phoner
		if ($array_citys[$i]["prio"] === "0") {
			$prioclass = 'zero';
		} else {
			$prioclass = '';
		}
		if ($array_citys[$i]["neu"] === "0") {
			$newclass = 'zero';
		} else {
			$newclass = '';
		}
		if ($array_citys[$i]["toopen"] === 0) {
			$closedclass = 'closed';
		} else {
			$closedclass = '';
		}

	?>
		<div id="<?php echo $cityid; ?>" class="col-3 phonercards <?php echo $array_citys[$i]["carrier"] . ' ' . $closedclass ?>">
			<div class="phoner-card-body">
				<div class="phonercard-titel">
					<span><?php echo $cityid; ?></span>
				</div>
				<div class="phoner-keys">
					<ul>
						<li class="key-listitem s-open">
							<span class="key-entry-icon open"><i class="ri-book-open-line"></i></span>
							<span class="key-entry-text">Offen: <span id="keyentryopen<?php echo $cityid; ?>" class="key-entry-subtext open"><?php echo $array_citys[$i]["open"] ?></span></span>
							<span class="key-entry-int"></span>
						</li>
						<li class="key-listitem s-toopen">
							<span class="key-entry-icon toopen"><i class="ri-phone-line"></i></span>
							<span class="key-entry-text">Verfügbar: <span id="keyentrytoopen<?php echo $cityid; ?> " class="key-entry-subtext toopen"><?php echo $array_citys[$i]["toopen"] ?></span></span>
							<span class="key-entry-int"></span>
						</li>
						<li class="key-listitem s-neu <?php echo $newclass ?>">
							<span class="key-entry-icon neu"><i class="ri-add-circle-line"></i></span>
							<span class="key-entry-text">Neu: <span id="keyentryneu<?php echo $cityid; ?> " class="key-entry-subtext neu"><?php echo $array_citys[$i]["neu"] ?></span></span>
							<span class="key-entry-int"></span>
						</li>
						<li class="key-listitem s-prio <?php echo $prioclass ?>">
							<span class="key-entry-icon prio"><i class="ri-star-line"></i></span>
							<span class="key-entry-text">Prio: <span id="keyentryprio<?php echo $cityid; ?> " class="key-entry-subtext prio"><?php echo $array_citys[$i]["prio"] ?></span></span>
							<span class="key-entry-int"></span>
						</li>
					</ul>
				</div>

				<div class="phoner-card-footer">
					<div class="col floatleft">
						<?php foreach ($array_log as $key => $value) {
							if ($value === $array_citys[$i]["city"]) {
								//echo $key;
								//echo $value;
								$data = fetchUserDetails('username', $key);
								echo '<img title="' . $key . '" class="footer-profilepic" src="/usersc/plugins/profile_pic/files/' . $data->profile_pic . '" />';
							}
						} ?>
					</div>
					<div class="col floatright">
						<?php if ($array_citys[$i]["carrier"] === "UGG") $imgsrc = "view/images/logo_carrier_ugg_smallwide.png"; ?>
						<?php if ($array_citys[$i]["carrier"] === "DGF") $imgsrc = "view/images/logo_carrier_dgf_smallwide.png"; ?>
						<?php if ($array_citys[$i]["carrier"] === "GVG") $imgsrc = "view/images/logo_carrier_gvg_smallwide.png"; ?>
						<?php if ($array_citys[$i]["carrier"] === "GlasfaserPlus") $imgsrc = "view/images/logo_carrier_glasfaserplus_smallwide.png"; ?>
						<img class="footer-img" src="<?php echo $imgsrc; ?>" />
					</div>
				</div>
			</div>
		</div>
	<?php
	}

	echo '<span id="' . $client . 'cardcount" class="hidden">' . $count_card . '</span>';
}

function load_ticketcard($client)
{
	$conn = dbconnect();
	$date = date("Y-m-d");

	$query = "SELECT * FROM `scan4_tickets` WHERE `status` = 'new' AND client LIKE '" . $client . "'";
	if ($result = $conn->query($query)) {
		while ($row = mysqli_fetch_assoc($result)) {
			$array[] = $row;
		}
		$result->free_result();
	}
	// close conn
	$conn->close();
	$count = count($array);
	ob_start();
	?>
	<div id="tickets_<?php echo $client ?>" class="col-3 phonercards tickets_<?php echo $client ?>">
		<div class="phoner-card-body">
			<div class="phonercard-titel">
				<span>Tickets</span>
			</div>
			<div class="phoner-keys">
				<ul>
					<li class="key-listitem s-open">
						<span class="key-entry-icon open"><i class="ri-book-open-line"></i></span>
						<span class="key-entry-text">Offen: <span id="keyentryopen<?php echo $count; ?>" class="key-entry-subtext open"><?php echo $count ?></span></span>
						<span class="key-entry-int"></span>
					</li>
					<li class="key-listitem s-prio">
						<span class="key-entry-icon prio"><i class="ri-star-line"></i></span>
						<span class="key-entry-text">Prio: <span id="keyentryprio<?php echo $count; ?> " class="key-entry-subtext prio"><?php echo $count ?></span></span>
						<span class="key-entry-int"></span>
					</li>
				</ul>
			</div>

		</div>
	</div>
<?php
}


function load_citystats($client)
{

	$lockedtimer = date("Y-m-d H:i:s");
	$lockedtimer = date('Y-m-d H:i:s', strtotime('-15 minutes', strtotime($lockedtimer)));

	$date = date("Y-m-d");
	$conn = dbconnect();
	$array_citys = array();
	$query = "SELECT * FROM `scan4_citylist` WHERE `status` = 'aktiv' AND client = '" . $client . "' ORDER BY `city` ASC";
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_row()) {
			$array_citys[] = array("city" => $row[1], "client" => $row[2], "toopen" => 0, "open" => 0, "prio" => 0, "locked" => 0, "lockedfree" => 0, "carrier" => $row[5], 'neu' => 0);
		}
		$result->free_result();
	}
	$length = count($array_citys);


	for ($i = 0; $i < $length; $i++) {

		$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE `city` LIKE '" . $array_citys[$i]["city"] . "' AND scan4_status = 'OPEN' 
		AND (client = 'Insyte' AND dpnumber != '' OR client != 'Insyte')
		AND (phone1 != '' OR phone2 != '' OR phone3 != '' OR phone4 != '')";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		$array_citys[$i]["open"] = $row[0];

		$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE `city` LIKE '" . $array_citys[$i]["city"] . "' AND scan4_status = 'OPEN'  AND `priority` IS NOT NULL 
		AND (client = 'Insyte' AND dpnumber != '' OR client != 'Insyte')
		AND (phone1 != '' OR phone2 != '' OR phone3 != '' OR phone4 != '')";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		$array_citys[$i]["prio"] = $row[0];

		$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE `city` LIKE '" . $array_citys[$i]["city"] . "' AND `scan4_status`='OPEN' AND anruf1 IS NULL AND anruf2 IS NULL AND anruf3 IS NULL AND anruf4 IS NULL AND anruf5 IS NULL 
		AND (client = 'Insyte' AND dpnumber != '' OR client != 'Insyte')
		AND (phone1 != '' OR phone2 != '' OR phone3 != '' OR phone4 != '')";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		$array_citys[$i]["neu"] = $row[0];

		$query = "SELECT COUNT(homeid) FROM `scan4_log` WHERE `city` LIKE '" . $array_citys[$i]["city"] . "' AND  `timestamp` LIKE '%" . $date . "%' AND result = 'OPEND'";
		$query = "SELECT COUNT(DISTINCT scan4_log.homeid) FROM `scan4_log` INNER JOIN scan4_homes ON scan4_log.homeid=scan4_homes.homeid WHERE scan4_homes.city LIKE '%" . $array_citys[$i]["city"] . "%' AND scan4_log.timestamp LIKE '%" . $date . "%' AND scan4_homes.scan4_status='OPEN' AND (scan4_homes.phone1 != '' or scan4_homes.phone2 != '')";

		$result = $conn->query($query);
		$row = $result->fetch_row();
		$array_citys[$i]["locked"] = $row[0];

		$array_citys[$i]["toopen"] = $array_citys[$i]["open"] - $array_citys[$i]["locked"];

		//echo $array_citys[$i]["city"] . ";" . $array_citys[$i]["open"] . ";" . $array_citys[$i]["locked"];
	}
	$conn->close();
	return $array_citys;
	//echo $query;
	// echo print_r($array_citys);
	//echo json_encode($array_citys);
}




function safe_homeid_call($homeid, $reason, $comment, $city, $hbgdate, $hbgdurration, $hbguser)
{ // Add call to table calls and update homes anruf1 - anruf5 column
	$uid = uniqid();
	global $currentuser;
	$conn = dbconnect();
	$query = "INSERT INTO `scan4_log`(`homeid`, `user`, `result`, `comment`, `city`, `callid`) VALUES ('" . $homeid . "','" . $currentuser . "','" . $reason . "','" . $comment . "','" . $city . "','" . $uid . "' )";
	mysqli_query($conn, $query);
	// INSERT call table
	$timestamp = date('Y-m-d H:i:s');
	$time = date('H:i:s');
	$date = date('Y-m-d');

	// Check Anruf Columns in Homes equal empty
	$int = 0;
	$query = "SELECT anruf1,anruf2,anruf3,anruf4,anruf5 FROM `scan4_homes` WHERE `homeid` = '" .  $homeid . "'";
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_row()) {
			for ($i = 0; $i <= 4; $i++) {
				if (strlen($row[$i]) === 0) {
					$int = $i + 1;
					break;
				} else if ($i === 4) {
					$int = 5;
				}
			}
		}
	}
	//$query = "INSERT INTO `scan4_calls`(`call_count`,`call_date`, `call_time` ,`call_user`, `homeid`, `result`, `comment`, `callid`) VALUES ('" . $int . "','" . $date . "','" . $time . "','" . $currentuser . "','" . $homeid . "','" . $result . "','" . $comment . "','" . $uid . "')";

	$query = "INSERT INTO `scan4_calls`(`call_count`,`call_date`, `call_time` ,`call_user`, `homeid`,`result`,`comment`,`callid`) VALUES ('" . $int . "','" . $date . "','" . $time . "','" . $currentuser . "','" . $homeid . "','" . $reason . "','" . $comment . "','" . $uid . "')";
	mysqli_query($conn, $query);
	// Add Anruf if empty col found
	if ($int > 0) {
		$query = "UPDATE `scan4_homes` SET `anruf" . $int . "`='" . $date . "',`number_anruf`='" . $int . "' WHERE `homeid` = '" . $homeid . "';";
		mysqli_query($conn, $query);

		$query = "SELECT COUNT(id) FROM scan4_calls WHERE homeid = '" . $homeid . "' AND call_user != 'Anrufliste'";
		$result = mysqli_query($conn, $query);
		$row = $result->fetch_row();
		$query = "INSERT INTO `scan4_homes_history`(`homeid`, `col1`, `col2`, `col3`, `col4`, col5,call6) VALUES ('" . $homeid . "','phoner','" . $currentuser . "','added a call','" . $reason . "','call_count " . $int . "','call_alls " . $row[0] . "')";
		mysqli_query($conn, $query);
		if ($reason !== "HBG erstellt") {
			if (strpos($reason, 'Keine HBG') !== false) {
				$query = "UPDATE `scan4_homes` SET `scan4_status`='STOPPED', scan4_comment = '" . $reason . ':' . $comment . "' WHERE `homeid` = '" . $homeid . "';";
				mysqli_query($conn, $query);
				$query = "INSERT INTO `scan4_homes_history`(`homeid`, `col1`, `col2`, `col3`, `col4`) VALUES ('" . $homeid . "','phoner','" . $currentuser . "','scan4status set to STOPPED','" . $reason . "')";
				mysqli_query($conn, $query);
			} else if ($int === 5) {
				$query = "UPDATE `scan4_homes` SET `scan4_status`='PENDING',scan4_comment = '5 Anrufe erreicht' WHERE `homeid` = '" . $homeid . "';";
				mysqli_query($conn, $query);
				$query = "INSERT INTO `scan4_homes_history`(`homeid`, `col1`, `col2`, `col3`, `col4`) VALUES ('" . $homeid . "','phoner','" . $currentuser . "','scan4status set to PENDING','" . $reason . "')";
				mysqli_query($conn, $query);
			} else {
			}
		}
		// Create HomeID history entry

	}

	// ======================
	//      write to HBG table 
	// ======================
	if ($reason === "HBG erstellt") {
		$tstamp = gmdate("Ymd\THis\Z");
		$uident = $tstamp . '-' . $homeid . '-RN' . rand(10000, 99999);
		$hbgtime = mb_substr($hbgdate, -5);
		$hbgdate = mb_substr($hbgdate, 0, 10);
		$query = "INSERT INTO `scan4_hbg`(`date`,`time`, `durration` ,`homeid`, `hausbegeher`,`comment`,`status`,`username`,`created`,`ident`,`uid`) VALUES ('" . $hbgdate . "','" . $hbgtime . "','" . $hbgdurration . "','" . $homeid . "','" . $hbguser . "','" . $comment . "','PLANNED','" . $currentuser . "','" . $timestamp . "','" . $uid . "','" . $uident . "')";
		mysqli_query($conn, $query);
		$query = "UPDATE `scan4_homes` SET `scan4_status`='PLANNED',`scan4_hbgdate`='" . $hbgdate . "', scan4_comment = '" . $comment . "' WHERE homeid = '" . $homeid . "'";
		mysqli_query($conn, $query);
		// Create HomeID history entry
		$query = "INSERT INTO `scan4_homes_history`(`homeid`, `col1`, `col2`, `col3`, `col4`) VALUES ('" . $homeid . "','phoner','" . $currentuser . "','scan4status set to PLANNED','" . $reason . "')";
		mysqli_query($conn, $query);
		nextcloud_put($homeid, $uident);
	}

	// ======================
	//      write to Followup table
	// ======================
	if ($reason === "Wiedervorlage") {
		$hbgtime = mb_substr($hbgdate, -5);
		$hbgdate = mb_substr($hbgdate, 0, 10);
		$query = "INSERT INTO `scan4_followup`(`date`, `time`, `user`, `homeid`, `comment`) VALUES ('" . $hbgdate . "','" . $hbgtime . "','" . $currentuser . "','" . $homeid . "','" . $comment . "')";
		mysqli_query($conn, $query);
		// >>>>>>>>>> Create HomeID history entry
		$query = "INSERT INTO `scan4_homes_history`(`homeid`, `col1`, `col2`, `col3`, `col4`, `col5`) VALUES ('" . $homeid . "','" . $uid . "','" . $currentuser . "','create followup','" . $hbgdate . "','" . $hbgtime . "')";
		mysqli_query($conn, $query);
	}

	// Reload homeid to update progressbar
	$query = "SELECT * FROM `scan4_homes` WHERE `homeid` = '" . $homeid . "' ";
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_row()) {
			$entry = $row;
		}
		$result->free_result();
	}
	echo json_encode($entry);
}





function get_timelinehbg($uid)
{
	$conn = dbconnect();
	$a_array = array();
	$query = "SELECT * FROM `scan4_hbg` WHERE `ident` = '" . $uid . "' OR `uid` = '" . $uid . "' ";
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_assoc()) {
			//while ($row = $result->fetch_row()) {
			if (!(in_array($row[2], $a_array)))
				$a_array[] = $row;
			//$a_array[] = array("hbgdate" => $row[1], "time" => $row[2], "team" => $row[5], "result" => $row[6],  "comment" => $row[6],  "status" => $row[7],  "user" => $row[8],  "created" => substr($row[9], 0, -10));
		}
		$result->free_result();
	}
	$conn->close();
	return $a_array;
}



function nextcloud_put($homeid, $uid)
{

	global $currentuser;
	$city = $_POST['city'];
	//$homeid = $_POST['homeid'];
	$comment = $_POST['comment'];
	if ($comment === "") $comment = "-";
	$team = $_POST['hbguser'];
	$hbgdate = $_POST['hbgdate'];

	$durration = str_replace(' min', '', $_POST['hbgdurration']);

	$start_time = $hbgdate; // used for calendar
	$end_time = date("Y-m-d H:i:s", strtotime($start_time) + intval($durration) * 60); // used for calendar
	$user = $currentuser;
	$date = date('Y-m-d');
	$time = substr(date('d-m-y H:i'), -5);
	$hbgdatetime = substr($hbgdate, -5);

	$hbgdate = str_replace(['-', 'T', ' '], '', $hbgdate);
	$hbgtime = mb_substr($hbgdate, -5);
	$hbgdate = mb_substr($hbgdate, 0, -5) . 'T';
	$endTime = strtotime("+{$durration} minutes", strtotime($hbgtime));
	$endTime = str_replace(':', '', date('H:i:s', $endTime));
	$tstart = $hbgdate . str_replace(':', '', $hbgtime) . '00';
	$tend = $hbgdate . str_replace(':', '', $endTime);

	$data = fetchUserDetails('username', $team, null);
	$userurl = $data->calendarhook;
	//$userurl = get_userdata(get_user_id_by_display_name($team));
	//$userurl = str_replace(['http://', 'https://'], '', $userurl->user_url);

	$index = 1;
	$conn = dbconnect();
	$query = "SELECT * FROM `scan4_homes` WHERE `homeid` = '" .  $homeid . "'";
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_row()) {
			$street = $row[3];
			$streetnr = $row[4];
			$streetnr .= $row[5];
			$unit = $row[6];
			$city = $row[7];
			$plz = $row[8];
			$client = $row[1];
			$name = $row[13]; // Nachname
			$name .= ', ' . $row[12]; // Vorname
			if ($row[13] !== "") {
				if ($comment !== "-") {
					$titel = $row[13] . ' !Notiz [CRM]';
				} else {
					$titel = $row[13] . ' [CRM]';
				}
			} else {
				if ($comment !== "-") {
					$titel = $row[12] . ' !Notiz [CRM]';
				} else {
					$titel = $row[12] . ' [CRM]';
				}
			}
			$phone1 =  str_replace('+49', '', $row[14]);
			$phone2 = str_replace('+49', '', $row[15]);
			$isp = $row[16];
			if (strlen($row[27]) > 6) $index = 1;
			if (strlen($row[28]) > 6) $index = 2;
			if (strlen($row[29]) > 6) $index = 3;
			if (strlen($row[30]) > 6) $index = 4;
			if (strlen($row[31]) > 6) $index = 5;
			break;
		}
	}
	$conn->close();

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
	$description = 'Notiz: ' . $comment . '\nName: ' . $name . '\nTel.: +49' . $phone1 . '\nTel.: +49' . $phone2 . '\nUnit: ' . $unit . '\nHomeID: ' . $homeid . '\nClient: ' . $client . '\nErstellt am: ' . date('d.m.y H:i') . 'Uhr \nErstellt von: ' . $user . '\nAnruf: #' . $index . '\nUID:' . $uid;

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


	$event = uniqid();
	calendar_create($titel, $start_time, $end_time, $description, $location, $homeid, $uid, $team, $event);


	$url = 'https://cloud2.scan4.pro/remote.php/dav/calendars/sys/' . $userurl . '/' . $uid . '.ics';
	//echo $url;


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

	writetonextcloudlogfile("\n ------------------------------------ \n" . $response . "\n $start_time // $end_time \n" . $body, 'create', $uid);
}




function nextcloud_change($uid, $reason)
{

	$conn = dbconnect();
	$query = "SELECT * FROM `calendar_events` WHERE uid = ?";
	if ($stmt = $conn->prepare($query)) {
		$stmt->bind_param("s", $uid);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($row = $result->fetch_assoc()) {
			
			
		} else {
			echo "UID not found";
			exit();
		}
		$stmt->close();
	}

	$title = $row['title'];
	$description = $row['description'];
	$homeid = $row['homeid'];
	$username = $row['user_name'];
	$eventID = $row['event_id'];
	$event_start = $row['start_time'];
	$event_end = $row['end_time'];
	$location = $row['location'];
	$location = str_replace(",", "\\,", $location); // set the address to \, to fit iPhone adress format

	if ($reason === 'activate') $title = '✅' . $title;

	$tstamp = gmdate("Ymd\THis"); // removed \Z

	$dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $event_start);
	$tstart = $dateTime->format('Ymd\THis');

	$dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $event_end);
	$tend = $dateTime->format('Ymd\THis');


	$data = fetchUserDetails('username', $username, null);
	$userurl = $data->calendarhook;

	echo "@$title \ $tstart \ $tend \ $userurl@";

	//$url = 'http://nextcloud.alphacc.de/remote.php/dav/calendars/bestadmin/benhbg/calc.ics';

	$headers = array('Content-Type: text/calendar', 'charset=utf-8');
	//$userpwd = 'bestadmin:mSMyCGIRTNPDjiqbJ5kt@HvK9BrsYzApW.2Z8lXEofV1UaOQ63';
	$userpwd = 'sys:smallusdickus';


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
SUMMARY:' . $title . '
TRANSP:OPAQUE
UID:' . $uid . '
END:VEVENT
END:VCALENDAR';

	$url = 'https://cloud2.scan4.pro/remote.php/dav/calendars/sys/' . $userurl . '/' . $uid . '.ics';

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
	if ($response === '') {
		$response = 'created';
	}

	writetonextcloudlogfile("\n ------------------------------------ \n" . $response . "\n" . $body, 'change', $uid);

    $userlog['source'] = 'freischalten';
    $userlog['homeid'] = $homeid;
    $userlog['action1'] = 'activated';
	$userlog['action2'] = $uid;
    $userlog['action3'] = 'nextcloud response:';
    $userlog['action4'] = $response;
    saveUserLog($userlog); // save the actions to the userlog

}


function calendar_getall($city)
{
	//echo 'listed';
	$conn = dbconnect();

	$current_date = new DateTime();
	$current_week = $current_date->format('Y-\WW'); // Get the current week in the "YYYY-WW" format
	$weeks = array();
	$weeks[] = $current_week;
	// Generate the next 10 weeks and add them to the array
	for ($i = 1; $i <= 10; $i++) {
		$next_date = clone $current_date;
		$next_date->modify("+{$i} week");
		$next_week = $next_date->format('Y-\WW');
		$weeks[] = $next_week;
	}


	$week_list = implode("','", $weeks); // prepare for the SQL

	$query = "SELECT *
          FROM wochenplan
          WHERE week IN ('$week_list')
          AND (week, username, id) IN (
              SELECT week, username, MAX(id)
              FROM wochenplan
              WHERE week IN ('$week_list')
              GROUP BY week, username
          )";

	//echo "$query\n";
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



function calendar_getall_appt($city, $user, $date)
{
	//echo 'listed';
	$conn = dbconnect();

	$date = substr($date, 0, 10);

	$query = "SELECT * FROM `calendar_events` WHERE user_name = '$user' AND start_time LIKE '%$date%'";

	//echo "$query\n";
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
