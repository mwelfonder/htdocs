<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
include "../../view/includes/functions.php";
$logged_in = $user->data();
$currentuser = $logged_in->username;
$func = $_POST['func'];
if ($func === "load_usersidebar") {
	$date = $_POST['date'] ?? '';
	load_usersidebar($date);
} else if ($func === "load_usercontent") {
	$user = $_POST['user'];
	$date = $_POST['date'];
	load_usercontent($user, $date);
} else if ($func === "load_logfile") {
	$date = $_POST['date'];
	load_logfile($date);
} else if ($func === "safe_hbgcheck") {
	$homeid = $_POST['homeid'];
	$result = $_POST['result'];
	$selection = $_POST['selection'];
	$comment = $_POST['comment'];
	$uid = $_POST['uid'];
	$source = $_POST['source'];
	safe_hbgcheck($homeid, $result, $selection, $comment, $uid, $source);
} else if ($func === "reset_hbgcheck") {
	$uid = $_POST['uid'];
	$homeid = $_POST['uid'];
	reset_hbgcheck($uid, $homeid);
} else if ($func === "reset_hbgmodulitem") {
	$uid = $_POST['uid'];
	$homeid = $_POST['uid'];
	reset_hbgmodulitem($uid, $homeid);
}


function reset_hbgmodulitem($uid, $homeid)
{
	global $currentuser;
	$conn = dbconnect();
	$datetime = date("Y-m-d H:i:s");
	$query = "INSERT INTO `scan4_userlog` SET `datetime`='" . $datetime . "', `user`='" . $currentuser . "',homeid = '" . $homeid . "',source = 'hbgcheck', `action1`='delete_hbgcheck', `action2`='" . $uid . "'";
	mysqli_query($conn, $query);
	$query = "UPDATE `scan4_hbg` SET `reviewed` = NULL, appt_status = NULL, appt_comment = NULL, appt_file = NULL, appt_datetime = NULL WHERE uid = '" . $uid . "';";
	mysqli_query($conn, $query);
	echo $query;
	$conn->close();
}

function reset_hbgcheck($uid, $homeid)
{

	global $currentuser;
	$conn = dbconnect();
	$datetime = date("Y-m-d H:i:s");
	$query = "INSERT INTO `scan4_userlog` SET `datetime`='" . $datetime . "', `user`='" . $currentuser . "',homeid = '" . $homeid . "',source = 'hbgcheck', `action1`='reset hbgcheck', `action2`='" . $uid . "'";
	mysqli_query($conn, $query);
	$query = "UPDATE `scan4_hbg` SET `reviewed` = NULL WHERE uid = '" . $uid . "';";
	mysqli_query($conn, $query);
	echo $query;
	$conn->close();
}


function safe_hbgcheck($homeid, $result, $selection, $comment, $uid, $source)
{

	global $currentuser;
	$conn = dbconnect();
	$datetime = date("Y-m-d H:i:s");
	$query = "INSERT INTO `scan4_userlog` SET `datetime`='" . $datetime . "', `user`='" . $currentuser . "',homeid = '" . $homeid . "',source = 'hbgcheck', `action1`='" . $result . "', `action2`='" . $selection . "' , `action3`='" . $comment . "', `action4`='" . $uid . "'";
	mysqli_query($conn, $query);

	if ($result === 'hbgcheck_done' && ($source === 'screenshot' || $source === 'protokoll')) {
		$status = 'DONE';
		// file move

	}
	if ($result === 'hbgcheck_done' && $source === 'excel') {
		$status = 'DONE EXCEL';

		$query = "SELECT * FROM scan4_hbg WHERE `uid` = '" . $uid . "'";
		$result = mysqli_query($conn, $query);
		$row = $result->fetch_assoc();
		$filename = $row['appt_file'];

		$year = date("Y");

		$split = explode('_', $filename, 2);
		$project = $split[0];

		$source = '/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/uploads/hbgprotokolle/begehungen/screenshots/' . $year . '/' . $project . '/' . $filename;
		if (!file_exists('/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/uploads/hbgprotokolle/begehungen/excel/' . $year . '/' . $project)) {
			mkdir('/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/uploads/hbgprotokolle/begehungen/excel/' . $year . '/' . $project, 0755, true);
		}

		$target = '/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/uploads/hbgprotokolle/begehungen/excel/' . $year . '/' . $project . '/' . $filename;
		if (!rename($source, $target)) {
			echo "File can't be moved!";
		} else {
			echo "File has been moved!";
		}
	}
	if ($result === 'hbgcheck_stopped') {
		$status = 'STOPPED';
	}

	if (strpos($selection, 'nicht da') !== false || strpos($selection, 'Unzureichend') !== false) {
		$status = 'OPEN';
	}
	if (strpos($selection, 'Protokoll') !== false) {
		$status = 'WRONG';
	}


	$query = "INSERT INTO `scan4_hbgcheck` SET `datetime`='" . $datetime . "', `user`='" . $currentuser . "',homeid = '" . $homeid . "',ident = '" . $uid . "', `status`='" . $status . "', `reason`='" . $selection . "' , `comment`='" . $comment . "'";
	mysqli_query($conn, $query);

	$query = "UPDATE `scan4_hbg` SET `reviewed`='1' WHERE `uid` = '" . $uid . "'";
	mysqli_query($conn, $query);

	$query = "UPDATE `scan4_homes` SET `scan4_status`='" . $status . "', scan4_comment = '" . $row['appt_status'] . "' WHERE `homeid` = '" . $homeid . "'";
	mysqli_query($conn, $query);




	mysqli_close($conn);
}



function load_logfile($date)
{
	if ($date === '' || $date === null) {
		$date = date("Y-m-d");
	}
	$conn = dbconnect();
	$query = "SELECT * FROM scan4_userlog WHERE `datetime` LIKE '" . $date . "%' AND action1 LIKE 'hbgmodul%' ORDER BY `id` DESC;";
	$result = mysqli_query($conn, $query);
	$length = mysqli_num_rows($result);
	$logfile = array();
	ob_start();
?>
	<table>
		<tbody style="font-size:14px;">
			<?php
			while ($obj = mysqli_fetch_assoc($result)) {
				$logfile[] = $obj;
			?>
				<tr>
					<td><?php echo $obj['datetime'] ?></td>
					<td><?php echo $obj['homeid'] ?></td>
					<td><?php echo $obj['user'] ?></td>
					<td><?php echo $obj['action1'] ?></td>
					<td><?php echo $obj['action2'] ?></td>
				</tr>

			<?php
			}
			?>
		</tbody>
	</table>
	<?php
}


function load_usersidebar($date)
{
	$conn = dbconnect();
	if ($date === '' || $date === null) $date = date("Y-m-d");

	$perm = fetchPermissionUsers(6); // 5 = Telefonist
	$begeher = [];
	for ($i = 0; $i < count($perm); $i++) {
		$data = fetchUserDetails(null, null, $perm[$i]->user_id);
		if ($data->permissions === '1') {
			$begeher[] =  array('username' => $data->username, 'fname' => $data->fname, 'lname' => $data->lname, 'done' => 0, 'canceld' => 0, 'open' => 0);
		}
	}
	$length = count($begeher);
	asort($begeher);
	$begeher = array_values($begeher);

	for ($i = 0; $i < $length; $i++) {

		/*
		$query = "SELECT scan4_citylist.city_id,scan4_hbg.*,scan4_homes.street,scan4_homes.streetnumber,scan4_homes.streetnumberadd,scan4_homes.city,scan4_homes.plz,scan4_homes.firstname,scan4_homes.lastname,scan4_homes.phone1,scan4_homes.phone2,scan4_homes.unit FROM `scan4_hbg` 
	INNER JOIN scan4_homes ON scan4_hbg.homeid=scan4_homes.homeid 
	Inner JOIN scan4_citylist ON scan4_citylist.city=scan4_homes.city
	WHERE scan4_hbg.hausbegeher = '" . $begeher[$i]['username'] . "' AND scan4_hbgdate = '" . $date . "' ORDER BY `scan4_hbg`.`time` ASC;";
*/

		$total = '';
		$query = "SELECT COUNT(id) FROM scan4_hbg WHERE `date` = '" . $date . "' AND hausbegeher = '" . $begeher[$i]['username'] . "' AND appt_status = 'done' AND appt_file IS NOT NULL";
		$result = mysqli_query($conn, $query);
		while ($row = $result->fetch_row()) {
			$begeher[$i]['done'] = $row[0];
		}


		$query = "SELECT COUNT(s4hbg.id) 
		FROM scan4_hbg AS s4hbg
		INNER JOIN scan4_homes AS s4homes ON s4hbg.homeid = s4homes.homeid
		WHERE s4hbg.`date` = '" . $date . "' 
		AND s4hbg.hausbegeher = '" . $begeher[$i]['username'] . "' 
		AND s4hbg.appt_status != 'done'";

		$result = mysqli_query($conn, $query);

		while ($row = $result->fetch_row()) {
			$begeher[$i]['canceld'] = $row[0];
		}



		$query = "SELECT COUNT(id) FROM scan4_hbg WHERE `date` = '" . $date . "' AND hausbegeher = '" . $begeher[$i]['username'] . "' AND appt_status IS NULL AND status = 'PLANNED'";
		$result = mysqli_query($conn, $query);
		$row = $result->fetch_row();
		$begeher[$i]['open'] = $row[0];


		$query = "SELECT COUNT(id) FROM scan4_hbg WHERE `date` = '" . $date . "' AND hausbegeher = '" . $begeher[$i]['username'] . "' AND reviewed = '1'";
		$result = mysqli_query($conn, $query);
		$row = $result->fetch_row();
		$begeher[$i]['checked'] = $row[0];


		$totalNotChecked = 0; // Anfangswert für nicht geprüfte

		// Abfrage, um die Gesamtzahl der nicht überprüften 'begeher' für das gegebene Datum zu erhalten
		$query = "SELECT COUNT(id) FROM scan4_hbg WHERE `date` = '" . $date . "' AND reviewed IS NULL";
		$result = mysqli_query($conn, $query);
		$row = $result->fetch_row();
		$totalNotChecked = $row[0];

		$total = $begeher[$i]['open'] + $begeher[$i]['canceld'] + $begeher[$i]['done'];

		$state = '';
		if ($total === '0') {
			$state = 'hidden';
		}
		if ($begeher[$i]['done'] !== '0' || $begeher[$i]['canceld'] !== '0') {
			$aproving = 'notapproved';
		} else {
			$aproving = '';
		}
		ob_start();

		if ($i === 0) {
	?>

			<li class="">
				<div id="sidebartab_total" class="hbguserlist-item-wrapper notapproved">
					<div class="hbguserlist-inner-header">Total</div>
					<div class="hbguserlist-inner-body">
						<div class="row flexcentered">
							<span id="mystattotaldone" class="mystat stattotal mystat_done"><i class="ri-checkbox-circle-line"></i> 0</span>
							<span id="mystattotalcanceld" class="mystat stattotal mystat_canceld"><i class="ri-close-circle-line"></i> 0</span>
							<span id="mystattotalopen" class="mystat stattotal mystat_open"><i class="ri-question-line"></i> 0</span>
							<span id="mystattotaltotal" class="mystat stattotal mystat_total"><i class="ri-hashtag"></i> 0</span>
							<span id="mystattotaltotal" class="mystat stattotal mystat_checked"><i class="ri-check-double-line"></i> 0</span>
						</div>
					</div>
				</div>
			</li>
		<?php
		}
		?>
		<li class="<?php echo $state ?>">
			<div id="sidebartab_<?php echo $begeher[$i]['username'] ?>" class="hbguserlist-item-wrapper <?php echo $aproving ?>">
				<div class="hbguserlist-inner-header"><?php echo $begeher[$i]['username'] ?></div>
				<div class="hbguserlist-inner-body">
					<div class="row flexcentered">
						<span class="mystat mystat_done"><i class="ri-checkbox-circle-line"></i> <?php echo $begeher[$i]['done'] ?></span>
						<span class="mystat mystat_canceld"><i class="ri-close-circle-line"></i> <?php echo $begeher[$i]['canceld'] ?></span>
						<span class="mystat mystat_open"><i class="ri-question-line"></i> <?php echo $begeher[$i]['open'] ?></span>
						<span class="mystat mystat_total"><i class="ri-hashtag"></i> <?php echo $begeher[$i]['open'] + $begeher[$i]['canceld'] + $begeher[$i]['done'] ?></span>
						<span id="mystattotaltotal" class="mystat mystat_checked"><i class="ri-check-double-line"></i> <?php echo $begeher[$i]['checked'] ?></span>
					</div>
				</div>
			</div>
		</li>


		<?php





	}
	// update userlog
	//mysqli_query($conn, $query);
	$conn->close();
}


function load_usercontent($user, $date)
{
	// Assuming dbconnect() is a function that returns a mysqli connection
	$conn = dbconnect();

	// Simplify the assignment of default values
	$date = ($date === '' || $date === null || $date === 'undefined') ? date("Y-m-d") : $date;
	$user = ($user === '' || $user === null  || $user === 'total') ? '%' : $user;

	// Use a prepared statement for better security and performance
	$query = "SELECT scan4_citylist.city_id, scan4_hbg.*, scan4_homes.street, scan4_homes.scan4_status, scan4_homes.streetnumber, scan4_homes.streetnumberadd, scan4_homes.city, scan4_homes.plz, scan4_homes.firstname, scan4_homes.lastname, scan4_homes.phone1, scan4_homes.phone2, scan4_homes.unit 
          FROM `scan4_hbg` 
          INNER JOIN scan4_homes ON scan4_hbg.homeid = scan4_homes.homeid 
          INNER JOIN scan4_citylist ON scan4_citylist.city = scan4_homes.city
          WHERE scan4_hbg.status = 'PLANNED' AND scan4_hbg.hausbegeher LIKE ? AND scan4_hbg.date = ?
          ORDER BY `scan4_hbg`.`time` ASC;";

	$stmt = $conn->prepare($query);
	$stmt->bind_param("ss", $user, $date);
	$stmt->execute();
	$result = $stmt->get_result();

	// Fetch data
	$obj = [];
	while ($row = $result->fetch_assoc()) {
		$obj[] = $row;
	}

	// Prepare to fetch user logs
	$uids = array_column($obj, 'uid');
	if (count($uids) > 0) {
		$uidsPlaceholder = implode(',', array_fill(0, count($uids), '?'));
		$userlogQuery = "SELECT * FROM scan4_userlog WHERE `datetime` LIKE CONCAT(?, '%') AND action3 IN ($uidsPlaceholder)";

		// Prepare the query
		$userlogStmt = $conn->prepare($userlogQuery);

		// Bind parameters
		$userlogParams = array_merge([$date], $uids);
		$userlogTypes = 's' . str_repeat('s', count($uids)); // string types
		array_unshift($userlogParams, $userlogTypes);
		call_user_func_array([$userlogStmt, 'bind_param'], $userlogParams);

		// Execute and fetch results
		$userlogStmt->execute();
		$userlogResult = $userlogStmt->get_result();

		// Map user logs to UIDs
		$userLogs = [];
		while ($row = $userlogResult->fetch_assoc()) {
			$userLogs[$row['action3']] = $row;
		}
	} else {
		$userLogs = [];
	}

	$reviewedHomeids = array_column(array_filter($obj, function ($item) {
		return $item['reviewed'] === '1';
	}), 'homeid');

	if (count($reviewedHomeids) > 0) {
		$homeidsPlaceholder = implode(',', array_fill(0, count($reviewedHomeids), '?'));
		$hbgcheckQuery = "SELECT * FROM (SELECT * FROM `scan4_hbgcheck` WHERE homeid IN ($homeidsPlaceholder) ORDER BY `id` DESC) as sub GROUP BY homeid";

		// Prepare the query
		$hbgcheckStmt = $conn->prepare($hbgcheckQuery);

		// Bind parameters
		$hbgcheckTypes = str_repeat('s', count($reviewedHomeids)); // string types
		$hbgcheckStmt->bind_param($hbgcheckTypes, ...$reviewedHomeids);

		// Execute and fetch results
		$hbgcheckStmt->execute();
		$hbgcheckResult = $hbgcheckStmt->get_result();

		// Map scan4_hbgcheck to homeids
		$hbgChecks = [];
		while ($row = $hbgcheckResult->fetch_assoc()) {
			$hbgChecks[$row['homeid']] = $row;
		}
	} else {
		$hbgChecks = [];
	}

	// Now, use the $userLogs array in your loop
	for ($i = 0; $i < count($obj); $i++) {
		$userlog = $userLogs[$obj[$i]['uid']] ?? null;

		if ($obj[$i]['appt_status'] === 'done' && $obj[$i]['appt_file'] !== '') {
			$state = '';
			$hbgstatus = 'hbgdone';
		} else if ($obj[$i]['appt_status'] !== 'done' && strlen($obj[$i]['appt_status'])  > 4) {
			$state = '';
			$hbgstatus = 'hbgfailed';
		} else {
			$state = 'hiadden';
			$hbgstatus = 'hbgopen';
		}
		$hbgisreviewed = '';
		$logreview = '';
		if ($obj[$i]['reviewed'] === '1') {
			$hbgisreviewed = 'checked';
			$logreview = $hbgChecks[$obj[$i]['homeid']] ?? null;
		}


		// explode appt_file with ; to get the project name
		$split = explode("_", $obj[$i]['appt_file']);
		$project = $split[0];
		ob_start();

		if ($i === 0) {
		?>
			<div class="swal2-icon swal2-success swal2-animate-success-icon hidden" style="display: flex;">
				<div class="swal2-success-circular-line-left" style="background-color: rgb(255 255 255 / 0%);"></div>
				<span class="swal2-success-line-tip"></span>
				<span class="swal2-success-line-long"></span>
				<div class="swal2-success-ring"></div>
				<div class="swal2-success-fix" style="background-color: rgb(255 255 255 / 0%);"></div>
				<div class="swal2-success-circular-line-right" style="background-color: rgb(255 255 255 / 0%);"></div>
			</div>
		<?php
		}
		?>


		<div id="<?php echo $obj[$i]['uid'] ?>" class="checkwrapperitem <?php echo $state . ' ' . $hbgstatus . ' ' . $hbgisreviewed; ?> ">
			<div class="row checkheader">
				<?php
				$minutes = '';

				$datetime1 = new DateTime($obj[$i]['time']);
				if (strlen($obj[$i]['appt_datetime']) < 2) {
					$datetime2 = '';
				} else {
					$datetime2 = new DateTime(date("H:i", strtotime($obj[$i]['appt_datetime'])));
				}

				$interval = $datetime1->diff($datetime2);
				$minutes = ($interval->h * 60) + $interval->i . ' min';

				?>
				<div class="col-2"><i class="ri-user-shared-2-line"></i> <?php echo $obj[$i]['hausbegeher'] ?></div>
				<div class="col-2"><?php echo $obj[$i]['homeid'] ?></div>
				<div class="col-2"><?php echo $obj[$i]['scan4_status'] ?></div>
				<div class="col-2">Termin: <?php echo $obj[$i]['time'] ?></div>
				<div class="col">Upload: <?php if (strlen($obj[$i]['appt_datetime']) > 2) echo date("y-m-d H:i", strtotime($obj[$i]['appt_datetime'])) ?></div>
				<div class="col">Differenz: <?php echo $minutes ?></div>
				<div class="col">Status: <?php echo $obj[$i]['appt_status'] ?></div>
				<div class="col title_isreviewed">
					<?php if ($obj[$i]['reviewed'] === '1') { ?>
						<i style="color:rgb(26, 169, 26);" class="ri-check-double-line"></i> checked
					<?php } ?>
				</div>
			</div>

			<div class="row checkbody collapsed">
				<div class="row">
					<div class="col-3">
						<p class="hbgstatustitle">Ergebnis Hausbegehung</p>
						<p class="nospace">Status: <?php echo $obj[$i]['appt_status'] ?></p>
						<p class="nospace">Uhrzeit: <?php echo $userlog['datetime'] ?></p>
						<textarea readonly class="form-control" id="appt_comment" rows="3" value=""><?php echo $obj[$i]['appt_comment'] ?></textarea>
						<div class="spacer-10"></div>
						<div class="btn-interact-phonerapp savehbgbtn hgreen unset" id="save_hbg"><i class="ri-save-3-line"></i> Speichern</div>

					</div>
					<div class="col-3">
						<p class="hbgstatustitle">Kundendaten</p>
						<p class="nospace">Termin: <?php echo $obj[$i]['time'] ?></p>
						<p class="nospace">Name</p>
						<p class="nospace"><input class="fullwidth" readonly value="<?php echo $obj[$i]['lastname'] . ', ' . $obj[$i]['firstname'] ?>" /></p>
						<p class="nospace">Adresse</p>
						<p class="nospace"><input class="fullwidth" readonly value="<?php echo $obj[$i]['street'] . ' ' . $obj[$i]['streetnumber'] . $obj[$i]['streetnumberadd'] . ', ' . $obj[$i]['plz'] . ' ' . $obj[$i]['city'] ?>" /></p>
						<p class="nospace">HomeId</p>
						<p class="nospace"><input class="fullwidth" id="homeid" readonly value="<?php echo $obj[$i]['homeid'] ?>" /></p>
					</div>
					<div class="col">
						<div class="hbgblocker <?php if ($obj[$i]['reviewed'] !== '1') echo 'hidden' ?>">
							<div><span><i style="color:rgb(26, 169, 26);" class="ri-check-double-line"></i> checked</span></div>
						</div>
						<p class="hbgstatustitle">Check <span class="checkgearbox"><i class="ri-settings-3-line"></i></span></p>
						<div class="row buttonsalign">
							<?php if ($hbgstatus !== 'hbgopen') { ?>
								<div class="col">
									<div class="btn-interact-phonerapp secondselect hgreen" id="hbgcheck_done"><i class="ri-checkbox-line"></i> DONE</div>
								</div>
								<div class="col">
									<div class="btn-interact-phonerapp secondselect hblue" id="hbgcheck_open"><i class="ri-login-circle-line"></i> OPEN</div>
								</div>
								<div class="col">
									<div class="btn-interact-phonerapp secondselect hyellow" id="hbgcheck_planned"><i class="ri-login-circle-line"></i> PLANNED</div>
								</div>
								<div class="col">
									<div class="btn-interact-phonerapp secondselect hred" id="hbgcheck_stopped"><i class="ri-checkbox-indeterminate-line"></i> STOPPED</div>
								</div>
							<?php } ?>
						</div>
						<div class="spacer-10"></div>
						<div class="row">
							<div class="col">

								<div class="donebuttons hidden">
									<div id="donebuttonscreenshot" class="donebutton screenshot"><i class="ri-image-2-fill"></i> Sreenshot</div>
									<div id="donebuttonprotokoll" class="donebutton protokoll"><i class="ri-file-text-line"></i> Protokoll</div>
									<div id="donebuttonexcel" class="donebutton excel"><i class="ri-file-excel-2-fill"></i> Excel</div>
								</div>
								<select style="margin-bottom:10px;" class="form-select condition-box form-control hidaden select_open hidden" id="select_open" aria-label="">
									<option disabled selected>Grund wählen...</option>
									<option>Protokoll beschädigt</option>
									<option>Protokoll falsch (Adresse)</option>
									<option>Protokoll falsch</option>
									<option>Unzureichender Grund</option>
									<option>Kunde nicht da</option>
									<option>Begeher nicht da</option>
									<option>Eigentümer nicht da</option>
								</select>

								<select style="margin-bottom:10px;" class="form-select condition-box form-control hidaden select_stopped hidden" id="select_stopped" aria-label="">
									<option disabled selected>Grund wählen...</option>
									<option>Keine HBG - Falsche Adresse</option>
									<option>Keine HBG - KD verweigert HBG</option>
									<option>Keine HBG - KD kündigt</option>
									<option>Keine HBG - Technisch nicht möglich</option>
									<option>Keine HBG - Falsche Kundendaten</option>
									<option>Keine HBG - Andere Gründe</option>
									<option>Keine HBG - Kein Gebäude</option>
								</select>

								<textarea class="form-control" id="check_comment" rows="5" placeholder="Begründung"><?php echo $logreview['comment'] ?></textarea>
								<div class="row">
									<div class="col">

										<div class="dropdown">
											<button class="btn btn-secondary dropdown-toggle drpdownbtn" type="button" id="dropdownMenuButton1" data-toggle="dropdown" aria-expanded="false">
												Autofill
											</button>
											<ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
												<li><a class="dropdown-item list-autofill" href="#">KD nicht da</a></li>
												<li><a class="dropdown-item list-autofill" href="#">KD ist nicht mit dem Tiefbau einverstanden</a></li>
												<li><a class="dropdown-item list-autofill" href="#">KD kontaktieren und nachfragen, ob er noch Interesse am Vertrag hat</a></li>
												<li><a class="dropdown-item list-autofill" href="#">KD war krank & bitte neu einplanen</a></li>
											</ul>
										</div>

									</div>
									<div class="col">* SC400 - Fehler im Protokoll</div>
								</div>

							</div>
						</div>


					</div>
				</div>
				<?php

				$commonPathSegment = '/uploads/hbgprotokolle/begehungen/';
				$baseURL = 'https://crm.scan4-gmbh.de' . $commonPathSegment;

				if ($obj[$i]['appt_file'] != '') {
					// Identify the position of the common path segment in appt_file
					$pos = strpos($obj[$i]['appt_file'], $commonPathSegment);

					if ($pos !== false) {
						// Extract the part of the path after the common segment
						$relativePath = substr($obj[$i]['appt_file'], $pos + strlen($commonPathSegment));

						// Construct the full URL
						$tmpfile = $baseURL . $relativePath;
					} else {
						// Handle the case where the common path segment is not found (if necessary)
						$tmpfile = $obj[$i]['appt_file'];
					}

					// Handle special characters
					$tmpfile = str_replace(["ü", "ö", "äß"], ["ue", "oe", "ss"], $tmpfile);



				?>
					<div class="row">
						<div class="col-12">
							<span class="prokoll-btn showprotokoll">Protokoll anzeigen</span><a target="_blank" href="<?php echo $tmpfile ?>"><span style="margin-left:10px;" class="prokoll-btn openprotokoll">Protokoll öffnen</span></a>
						</div>

						<div class="col-12 protokoll-container hidden">
							<div class="iframecontainer">

								<iframe src="<?php echo $tmpfile ?>" title=""></iframe>


							</div>
						</div>

					</div>

				<?php } ?>
			</div>


		</div>

<?php

	}
}
