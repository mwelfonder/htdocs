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
if ($func === "load_table") {
	$state = $_POST['state'] ?? '';
	load_table($state);
} else if ($func === "load_table_appoint") {
	$state = $_POST['state'] ?? '';
	load_table_appoint();
} else if ($func === "change_activated") {
	$uid = $_POST['uid'];
	$state = $_POST['state'] ?? '';
	change_activated($uid, $state);
} else if ($func === "deletecity") {
	$city = $_POST['city'];
	deletecity($city);
} else if ($func === "change_datetime") {
	$date = $_POST['date'];
	$time = $_POST['time'];
	$comment = $_POST['comment'];
	$uid = $_POST['uid'];
	change_datetime($uid, $date, $time, $comment);
} else if ($_POST['func'] === 'load_activationtracker') {
	load_activationtracker();
} else if ($_POST['func'] === 'load_appointments') {
	load_appointments();
}


function load_activationtracker()
{
	if (date('N') == 5) { // Check if today is Friday
		$tomorrow = date('Y-m-d', strtotime('tomorrow'));
		$dataarray = get_table_data('0', $tomorrow); // Daten für Samstag

		$nextMonday = date('Y-m-d', strtotime('next Monday'));
		$mondayData = get_table_data('0', $nextMonday); // Daten für Montag

		$dataarray = array_merge($dataarray, $mondayData); // Kombinieren der Daten
	} else {
		$tomorrow = date('Y-m-d', strtotime('tomorrow'));
		$dataarray = get_table_data('0', $tomorrow);
	}

	$length = count($dataarray);
	echo $length; // Zeigt die Gesamtanzahl der Werte in $dataarray
}

function change_datetime($uid, $date, $time, $comment)
{
	global $currentuser;
	$first = mb_substr($date, 0, -2);
	$last = mb_substr($date, -2);
	$temp = $first . '20' . $last;
	$date = date('Y-m-d', strtotime($temp));
	$explode = explode('-', $uid);
	$homeid = $explode[1];

	$conn = dbconnect();
	$query = "SELECT * FROM `scan4_hbg` WHERE `uid` = '" . $uid . "'";
	$result = $conn->query($query);
	$olddata = mysqli_fetch_assoc($result);
	$query = "INSERT INTO `scan4_userlog`( `homeid`,`user`, `source`,`action1`, action2, action3,action4) VALUES ('" . $homeid . "','" . $currentuser . "','activation','Termin wurde geändert " . $olddata['date'] . " zu " . $date . "', '" . $olddata['time'] . ' zu ' . $time . "', '" . $olddata['comment'] . ' zu ' . $comment . "', '" . $uid . "')";
	mysqli_query($conn, $query) or die(mysqli_error($conn));

	$query = "UPDATE `scan4_hbg` SET `date`='" . $date . "', `time`= '" . $time . "', comment = '" . $comment . "' WHERE `uid` = '" . $uid . "'";
	mysqli_query($conn, $query);
	$conn->close();
	echo $query;
	//echo 'giventitle:'.$title;
}


function change_activated($uid, $state)
{
	global $currentuser;
	$explode = explode('-', $uid);
	$homeid = $explode[1];
	$conn = dbconnect();
	$query = "UPDATE `scan4_hbg` SET `activated`='1' WHERE `uid` = '" . $uid . "'";
	mysqli_query($conn, $query);
	$query = "INSERT INTO `scan4_userlog`( `homeid`,`user`, `source`,`action1`, action2) VALUES ('" . $homeid . "','" . $currentuser . "','activation','Termin wurde aktiviert', '" . $uid . "')";
	mysqli_query($conn, $query) or die(mysqli_error($conn));
	$conn->close();

	echo 'state changed';
	echo $query;
	//echo 'giventitle:'.$title;
}

function load_table($status)
{
	$tomorrow = date('Y-m-d', strtotime('tomorrow'));
	//$tomorrow = '2023-05-19'; //workaround for Feiertag
	if (date('N') == 5) { // Check if today is Friday
		// if its friday, get saturday and append monday to the array
		$tomorrow = date('Y-m-d', strtotime('tomorrow'));
		//$tomorrow = '2023-05-19'; //workaround for Feiertag
		$dataarray = get_table_data($status, $tomorrow);
		$tomorrow = date('Y-m-d', strtotime('next monday'));
		//$tomorrow = '2023-05-19'; //workaround for Feiertag
		$tomorrow = '2023-11-21';
		$dataarray = get_table_data($status, $tomorrow);
	} else {
		$tomorrow = date('Y-m-d', strtotime('tomorrow'));
		$tomorrow = '2023-11-21';

		$dataarray = get_table_data($status, $tomorrow);
	}

	$currentclient = '';

	$row = $dataarray;
	$lenght = count($row);
	if ($status === '1') {
		$isdisabled = 'disabled';
	} else {
		$isdisabled = '';
	}
	$output = ob_start();

	echo '<div id="jsonExtContent" style="display:none;">' . json_encode($dataarray) . '</div>';

	$int = 0;
	for ($i = 0; $i < $lenght; $i++) {

		$data = fetchUserDetails('username', $row[$i]['hausbegeher']);
		if ($row[$i]['client'] !== $currentclient) {
			$currentclient = $row[$i]['client'];
			echo '<tr class="clientrow"><td></td><td colspan="11" style="text-align:left;font-size:18px;">' . $currentclient . '</td>
                </tr>';
		}
		$int++;
		if ($row[$i]['carrier'] === 'UGG') {
			$href = 'https://ugg.service-now.com/';
		} else if ($row[$i]['carrier'] === 'GVG') {
			$href = 'https://sc.netzkontor.net/';
		} else {
			$href = '';
		}
?>

		<tr class="<?php echo $row[$i]['carrier'] . ' ' . $row[$i]['client']; ?>" style="<?php
																							if (strpos($row[$i]['city'], 'MDU') !== false) {
																								echo 'background: #c34545b0; color: #fff !important;';
																							}
																							?>">
			<?php
			if ($row[$i]['activated'] === '0') {
				echo '<td id="' . $row[$i]['uid'] . '" class="uid" style="text-align:center;"><span class="activation"><i class="ri-checkbox-blank-line"></i></span></td>';
			} else {
				echo '<td class="isactivated" style="text-align:center;"><span><i class="ri-checkbox-line"></i></span></td>';
			}
			?>
			<td><span class="<?php if (strlen($href) < 2) {
									echo 'hidden';
								} ?>"><a href="<?php echo $href ?>" target="_blank"><i class="ri-external-link-line"></i></a></span></td>
			<td id="<?php echo $row[$i]['homeid'] ?>" class="td-homeid"><?php echo $row[$i]['homeid'] ?></td>
			<td class="td-username"><?php echo $row[$i]['username'] ?></td>
			<td class="td-city"><?php echo $row[$i]['city']; ?></td>
			<td class="td-street"><?php echo $row[$i]['street'] . ' ' . $row[$i]['streetnumber'] . $row[$i]['streetnumberadd'] ?></td>
			<td class="td-comment"><textarea <?php echo $isdisabled ?> class="inputfaded commentchange"><?php echo $row[$i]['comment'] ?></textarea></td>
			<td class="td-save"><span class="savechanges"><i class="ri-save-3-line"></i></span>
			<td><input <?php echo $isdisabled ?> class="inputfaded datechange" value="<?php echo date('d.m.y', strtotime($row[$i]['date'])) ?>" /></td>
			<td><input <?php echo $isdisabled ?> class="inputfaded timechange" value="<?php echo $row[$i]['time'] ?>" /></td>
			<td class="td-user"><?php echo $row[$i]['hausbegeher'] ?></td>
			<td><?php echo $data->team ?></td>

		</tr>

	<?php

	}

	if ($int === 0) {

		echo '<tr class="noentrys"><td colspan="11" style="text-align:center; padding: 50px; height: 150px;"><i class="ri-ghost-line"></i> Keine Einträge</td> </tr>';
	}


	// echo $query;
	//$output = ob_get_contents();
	//echo $output;
	//echo json_encode($entry);
}


function load_table_appoint()
{
	$dataarray = get_table_data_created();
	$currentclient = '';

	$row = $dataarray;
	$lenght = count($row);
	$isdisabled = '';

	$output = ob_start();

	$int = 0;
	for ($i = 0; $i < $lenght; $i++) {

		$data = fetchUserDetails('username', $row[$i]['hausbegeher']);
		if ($row[$i]['client'] !== $currentclient) {
			$currentclient = $row[$i]['client'];
			echo '<tr class="clientrow"><td></td><td colspan="11" style="text-align:left;font-size:18px;">' . $currentclient . '</td>
                </tr>';
		}
		$int++;
		if ($row[$i]['carrier'] === 'UGG') {
			$href = 'https://ugg.service-now.com/';
		} else if ($row[$i]['carrier'] === 'GVG') {
			$href = 'https://sc.netzkontor.net/';
		} else {
			$href = '';
		}
	?>

		<tr class="<?php echo $row[$i]['carrier'] . ' ' . $row[$i]['client'] ?>">
			<?php
			if ($row[$i]['activated'] === '0') {
				echo '<td id="' . $row[$i]['uid'] . '" class="uid" style="text-align:center;"><span class="activation"><i class="ri-checkbox-blank-line"></i></span></td>';
			} else {
				echo '<td class="isactivated" style="text-align:center;"><span><i class="ri-checkbox-line"></i></span></td>';
			}
			?>
			<td><span class="<?php if (strlen($href) < 2) {
									echo 'hidden';
								} ?>"><a href="<?php echo $href ?>" target="_blank"><i class="ri-external-link-line"></i></a></span></td>
			<td id="<?php echo $row[$i]['homeid'] ?>" class="td-homeid"><?php echo $row[$i]['homeid'] ?></td>
			<td class="td-username"><?php echo $row[$i]['username'] ?></td>
			<td class="td-city"><?php echo $row[$i]['city'] ?></td>
			<td class="td-street"><?php echo $row[$i]['street'] . ' ' . $row[$i]['streetnumber'] . $row[$i]['streetnumberadd'] ?></td>
			<td class="td-comment"><textarea <?php echo $isdisabled ?> class="inputfaded commentchange"><?php echo $row[$i]['comment'] ?></textarea></td>
			<td class="td-save"><span class="savechanges"><i class="ri-save-3-line"></i></span>
			<td><input <?php echo $isdisabled ?> class="inputfaded datechangee" value="<?php echo date('d.m.y', strtotime($row[$i]['date'])) ?>" /></td>
			<td><input <?php echo $isdisabled ?> class="inputfaded timechangee" value="<?php echo $row[$i]['time'] ?>" /></td>
			<td class="td-user"><?php echo $row[$i]['hausbegeher'] ?></td>
			<td><?php echo $data->team ?></td>

		</tr>

		<?php

	}

	if ($int === 0) {
		echo '<tr class="noentrys"><td colspan="11" style="text-align:center; padding: 50px; height: 150px;"><i class="ri-ghost-line"></i> Keine Einträge</td> </tr>';
	}
	// echo $query;
	//$output = ob_get_contents();
	//echo $output;
	//echo json_encode($entry);
}




function load_appointments()
{
	$dataarray = get_data_appointments_today();
	$currentclient = '';

	$row = $dataarray;
	$lenght = count($row);
	if ($lenght === 0) {
		echo '<tr class="noentrys"><td colspan="11" style="text-align:center; padding: 50px; height: 150px;"><i class="ri-ghost-line"></i> Keine Einträge</td> </tr>';
	} else {
		for ($i = 0; $i < $lenght; $i++) {
		?>
			<tr class="<?php echo $row[$i]['carrier'] . ' ' . $row[$i]['client'] ?>">
				<td class=""><?php echo '#' . ($i + 1) ?></td>
				<td id="<?php echo $row[$i]['homeid'] ?>" class="td-homeid"><?php echo $row[$i]['homeid'] ?></td>
				<td class="td-dp"><?php echo $row[$i]['dpnumber'] ?></td>
				<td class="td-city"><?php echo $row[$i]['city'] ?></td>
				<td class="td-street"><?php echo $row[$i]['street'] . ' ' . $row[$i]['streetnumber'] . $row[$i]['streetnumberadd'] ?></td>
				<?php if (hasPerm([2, 3])) { ?>
					<td style="max-width:15vw;" class="td-comment"><span><?php echo $row[$i]['comment'] ?></span></td>
				<?php } ?>
			</tr>
<?php
		}
	}

	// echo $query;
	//$output = ob_get_contents();
	//echo $output;
	//echo json_encode($entry);
}

/*Jens       INS. 1 / Monc. 1
Sergio     INS. 1 / Monc. 1
Alex:      INS. 2 / Monc. 2
Frank:     INS. 3 / Monc. 3
Timo:      INS. 3 / Monc. 3
Sebastian: INS. 4 / Monc. 4
Erdem:     INS. 4 / Monc. 4
Max:       INS. 5 / Monc. 5
Dawid:     INS. 5 / Monc. 5
Ben:       INS. 5 / Monc. 5 
Carsten:   INS. 2 / Monc. 2 
angel: INS. 2 / Monc. 2
Angelo:    INS. 7 / Monc. 7
Paulo:       INS. 7 / Monc. 7
Michelle:  INS. 8 / Monc. 8 
Robert B.  INS. 8 / Monc. 8
Jan       INS. 8 / Monc. 8 
Robert R.  INS. 9 / Monc. 9
Jonas:     INS. 9 / Monc. 9
Manfred:   INS. 10 / Monc. 10
Michaek We:INS. 10 / Monc. 10
Tizian:            INS. 10 / Monc. 10
angel:


*/


function get_table_data($status = '0', $date = null)
{
	global $currentuser;
	$conn = dbconnect();

	if ($date === null) $date = date('Y-m-d');
	if (hasPerm([2])) $currentuser = '%';

	// 	$query = "SELECT scan4_hbg.*,scan4_homes.city,scan4_homes.street,scan4_homes.streetnumber,scan4_homes.streetnumberadd,scan4_homes.carrier,scan4_homes.client  FROM `scan4_hbg` INNER JOIN scan4_homes ON scan4_hbg.homeid=scan4_homes.homeid WHERE scan4_hbg.status = 'PLANNED' AND scan4_hbg.activated = '$status' AND scan4_hbg.username LIKE '%" . $currentuser . "%' AND scan4_hbg.date LIKE '" . $date . "' ORDER BY scan4_homes.client DESC;";

	$query = "SELECT scan4_hbg.*,scan4_homes.city,scan4_homes.street,scan4_homes.streetnumber,scan4_homes.streetnumberadd,scan4_homes.carrier,scan4_homes.client  
FROM `scan4_hbg` 
INNER JOIN scan4_homes ON scan4_hbg.homeid=scan4_homes.homeid 
WHERE scan4_hbg.status = 'PLANNED' 
AND scan4_hbg.activated = '$status' 
AND scan4_homes.carrier ='UGG' 
AND scan4_hbg.date LIKE '" . $date . "' 
ORDER BY scan4_homes.client DESC, scan4_hbg.time ASC";

	$result = $conn->query($query);
	while ($row = mysqli_fetch_assoc($result)) {
		$dataarray[] = $row;
	}
	$result->free_result();

	$query = "SELECT scan4_hbg.*,scan4_homes.city,scan4_homes.street,scan4_homes.streetnumber,scan4_homes.streetnumberadd,scan4_homes.carrier,scan4_homes.client  FROM `scan4_hbg` INNER JOIN scan4_homes ON scan4_hbg.homeid=scan4_homes.homeid WHERE scan4_homes.city LIKE '%MDU%' AND scan4_hbg.status = 'PLANNED' AND scan4_hbg.activated = '$status' AND scan4_hbg.date > '" . $date . "' ORDER BY scan4_homes.client DESC, scan4_hbg.time ASC";
	$result = $conn->query($query);
	while ($row = mysqli_fetch_assoc($result)) {
		$dataarray[] = $row;
	}

	$query = "SELECT scan4_hbg.*, scan4_homes.city, scan4_homes.street, scan4_homes.streetnumber, scan4_homes.streetnumberadd, scan4_homes.carrier, scan4_homes.client  
	FROM `scan4_hbg` 
	INNER JOIN scan4_homes ON scan4_hbg.homeid = scan4_homes.homeid 
	WHERE (scan4_homes.carrier = 'GVG' OR scan4_homes.carrier = 'GlasfaserPlus') 
	AND scan4_hbg.status = 'PLANNED' 
	AND scan4_hbg.activated = '$status' 
	AND scan4_hbg.date >= CURDATE() 
	ORDER BY scan4_homes.client DESC, scan4_hbg.time ASC";


	$result = $conn->query($query);
	while ($row = mysqli_fetch_assoc($result)) {
		$dataarray[] = $row;
	}
	$conn->close();

	// Sort the data array by date
	usort($dataarray, function ($a, $b) {
		return strcmp($a['date'], $b['date']);
	});
	return $dataarray;
}


function get_table_data_created($status = '0', $date = null)
{
	global $currentuser;
	$conn = dbconnect();

	if ($date === null) $date = date('Y-m-d');
	if (hasPerm([2])) $currentuser = '%';

	$query = "SELECT scan4_hbg.*,scan4_homes.city,scan4_homes.street,scan4_homes.streetnumber,scan4_homes.streetnumberadd,scan4_homes.carrier,scan4_homes.client  FROM `scan4_hbg` INNER JOIN scan4_homes ON scan4_hbg.homeid=scan4_homes.homeid WHERE scan4_hbg.status = 'PLANNED' AND scan4_hbg.activated = '" . $status . "' AND scan4_hbg.username LIKE '%" . $currentuser . "%' AND scan4_hbg.created LIKE '" . $date . "%' ORDER BY scan4_homes.client DESC, scan4_hbg.time ASC";

	$dataarray = [];
	$result = $conn->query($query);
	while ($row = mysqli_fetch_assoc($result)) {
		$dataarray[] = $row;
	}
	$conn->close();
	return $dataarray;
}


function get_data_appointments_today()
{
	$conn = dbconnect();
	if (hasPerm([8])) $client = 'Insyte';
	if (hasPerm([9])) $client = 'Moncobra';
	if (hasPerm([2, 3])) $client = '%';
	$date = date('Y-m-d');
	$query = "SELECT scan4_homes.dpnumber,scan4_hbg.username,scan4_hbg.date,scan4_hbg.time,scan4_hbg.homeid,scan4_hbg.hausbegeher,scan4_hbg.comment,scan4_hbg.ident,scan4_hbg.activated,scan4_hbg.uid,scan4_homes.city,scan4_homes.street,scan4_homes.streetnumber,scan4_homes.streetnumberadd,scan4_homes.carrier,scan4_homes.client  FROM `scan4_hbg` INNER JOIN scan4_homes ON scan4_hbg.homeid=scan4_homes.homeid WHERE scan4_hbg.status = 'PLANNED' AND scan4_homes.client LIKE '" . $client . "' AND scan4_hbg.date LIKE '" . $date . "%' ORDER BY scan4_hbg.time DESC;";
	$result = $conn->query($query);
	while ($row = mysqli_fetch_assoc($result)) {
		$dataarray[] = $row;
	}

	$conn->close();
	return $dataarray;
}
