<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
include "../../view/includes/functions.php";

$logged_in = $user->data();
$currentuser = $logged_in->username;

$func = $_POST['func'];
if ($func === "load_table") {
	load_table('open');
	echo '<div class="mod-title aligncenter appt-done">Abgeschlossene HBG´s </div>';
	load_table('done');
} else if ($func === "safe_hbg_status") {
	$uid = $_POST['uid'];
	$status = $_POST['status'];
	$comment = $_POST['comment'];
	$user = $_POST['user'];
	$file = $_POST['file'];
	safe_hbg_status($uid, $status, $comment, $user, $file);
} else if ($func === "load_bugreport") {
	$homeid = $_POST['homeid'];
	load_bugreport($homeid);
}


function load_bugreport($homeid)
{
	$conn = dbconnect();
	$date = date("Y-m-d");
	$query = "SELECT * FROM `scan4_bug_reports` WHERE datetime LIKE '$date%' AND bug_homeid = '$homeid'";
	$result = mysqli_query($conn, $query);
	while ($row = mysqli_fetch_assoc($result)) {
		$data[] = $row;
		echo print_r($row);
	}
	$conn->close();
	echo count($data);
	if (count($data) > 0) {
		echo 'found';
	} else {
		echo 'not found';
	}
}


function safe_hbg_status($uid, $status, $comment, $user, $file)
{
	$conn = dbconnect();
	$date = date("Y-m-d H:i:s");
	$query = "UPDATE `scan4_hbg` SET `appt_status`='" . $status . "', appt_comment = '" . $comment . "', appt_datetime = '" . $date . "' WHERE uid='" . $uid . "'";
	mysqli_query($conn, $query) or die(mysqli_error($conn));

	// fetch assoc mysqli query
	$query = "SELECT * FROM `scan4_hbg` WHERE uid='" . $uid . "'";
	$result = mysqli_query($conn, $query);
	$row = mysqli_fetch_assoc($result);
	$homeid = $row['homeid'];

	if ($status !== 'done') {
		$query = "INSERT INTO `scan4_userlog` SET `datetime`='" . $date . "',homeid = '" . $homeid . "', `user`='" . $user . "',source = 'hbgmodul', `action1`='hbgmodul set appt status to " . $status . "', `action2`='comment: " . $comment . "' , `action3`='" . $uid . "' ";
	} else {
		$query = "INSERT INTO `scan4_userlog` SET `datetime`='" . $date . "',homeid = '" . $homeid . "', `user`='" . $user . "',source = 'hbgmodul', `action1`='hbgmodul set appt status to " . $status . " with " . $file . "', `action2`='comment: " . $comment . "' , `action3`='" . $uid . "' ";
	}
	// update userlog
	mysqli_query($conn, $query);
	$conn->close();
	echo 'imdone';
}

function get_all_appt()
{
	global $currentuser;
	$conn = dbconnect();
	$length = 0;
	$date = date("Y-m-d");

	/*
	$query = "SELECT * FROM `scan4_hbg` WHERE hausbegeher = 'BenGetschmann' AND `date` = '" . $date . "' ORDER BY `scan4_hbg`.`time` ASC";
	$result = mysqli_query($conn, $query);
	while ($obj = mysqli_fetch_assoc($result)) {
		$entry[] = $obj;
		$length++;
		//array_push($entry,$row);
	}
*/
	//	mysqli_free_result($result);
	if ($currentuser === 'BenGetschmann') $currentuser = 'MichaelWerner';
	if ($currentuser === 'DanielTschernich') {
		$query = "SELECT scan4_citylist.city_id,scan4_hbg.*,scan4_homes.client,scan4_homes.street,scan4_homes.streetnumber,scan4_homes.streetnumberadd,scan4_homes.city,scan4_homes.plz,scan4_homes.firstname,scan4_homes.lastname,scan4_homes.phone1,scan4_homes.phone2,scan4_homes.unit FROM `scan4_hbg` 
		INNER JOIN scan4_homes ON scan4_hbg.homeid=scan4_homes.homeid 
		Inner JOIN scan4_citylist ON scan4_citylist.city=scan4_homes.city
		WHERE scan4_hbg.status = 'PLANNED' AND scan4_hbg.hausbegeher = '" . $currentuser . "' AND scan4_hbg.date = '2023-05-04' ORDER BY `scan4_hbg`.`time` ASC;";
	} else {
		$query = "SELECT scan4_citylist.city_id,scan4_hbg.*,scan4_homes.client,scan4_homes.street,scan4_homes.streetnumber,scan4_homes.streetnumberadd,scan4_homes.city,scan4_homes.plz,scan4_homes.firstname,scan4_homes.lastname,scan4_homes.phone1,scan4_homes.phone2,scan4_homes.unit FROM `scan4_hbg` 
		INNER JOIN scan4_homes ON scan4_hbg.homeid=scan4_homes.homeid 
		Inner JOIN scan4_citylist ON scan4_citylist.city=scan4_homes.city
		WHERE scan4_hbg.status = 'PLANNED' AND scan4_hbg.hausbegeher = '" . $currentuser . "' AND scan4_hbg.date = '" . $date . "' ORDER BY `scan4_hbg`.`time` ASC;";
	}
	//$query = "SELECT scan4_hbg*,scan4_homes.street,scan4_homes.streetnumber,scan4_homes.streetnumberadd,scan4_homes.city,scan4_homes.plz,scan4_homes.firstname,scan4_homes.lastname,scan4_homes.phone1,scan4_homes.phone2,scan4_homes.unit FROM `scan4_hbg` INNER JOIN scan4_homes ON scan4_hbg.homeid=scan4_homes.homeid  WHERE scan4_hbg.hausbegeher = 'BenGetschmann' AND scan4_hbgdate = '" . $date . "' ORDER BY `scan4_hbg`.`time` ASC";
	$result = mysqli_query($conn, $query);
	while ($obj = mysqli_fetch_assoc($result)) {
		//$data[$obj["homeid"]] = $a;
		$data[] = $obj;
	}
	$conn->close();

	return $data;
}


function load_table($state)
{
	$value = get_all_appt();

	$length = count($value);

	//echo $length;
	$a_done = array();

	ob_start();
	for ($i = 0; $i < $length; $i++) {




		$origin = $value[$i]['street'] . ' ' . $value[$i]['streetnumber'] . $value[$i]['streetnumberadd'] . ',' . $value[$i]['plz'] . ' ' . $value[$i]['city'] . ',Deutschland';
		$destination = $value[$i + 1]['street'] . ' ' . $value[$i + 1]['streetnumber'] . $value[$i + 1]['streetnumberadd'] . ',' . $value[$i + 1]['plz'] . ' ' . $value[$i + 1]['city'] . ',Deutschland';

		$distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins=' . urlencode($origin) . '&destinations=' . urlencode($destination) . '&key=AIzaSyDCl2caECotzkmMvzN9BXcamlpXbwfk7zE');
		$distance_arr = json_decode($distance_data);
		if ($distance_arr->status == 'OK') {
			$destination_addresses = $distance_arr->destination_addresses[0];
			$origin_addresses = $distance_arr->origin_addresses[0];
		} else {
			//echo "<p>The request was Invalid</p>";
			//exit();
		}
		// Get the elements as array
		$elements = $distance_arr->rows[0]->elements;
		$distance = $elements[0]->distance->text;
		$duration = $elements[0]->duration->text;
		$duration = str_replace("hours", "Std", $duration);
		$duration = str_replace("mins", "min", $duration);
		//echo "From: ".$origin_addresses."<br/> To: ".$destination_addresses."<br/> Distance: <strong>".$distance ."</strong><br/>";
		//echo $i. " Duration: <strong>" . $duration . "";

		$cdate = date("d.m.y", strtotime(mb_substr($value[$i]['created'], 0, 10)));
		$ctime = mb_substr($value[$i]['created'], 11, -10);
		$ishidden = '';
		if ($state === 'open') {
			if ($value[$i]['appt_status'] === 'done' && strlen($value[$i]['appt_file']) > 5) $ishidden = 'hidden';
			if ($value[$i]['appt_status'] !== 'done' && strlen($value[$i]['appt_status']) > 2) $ishidden = 'hidden';
		} else if ($state === 'done') {
			$ishidden = 'hidden';
			if ($value[$i]['appt_status'] !== 'done' && strlen($value[$i]['appt_status']) > 2) $ishidden = 'show';
			if ($value[$i]['appt_status'] === 'done' && strlen($value[$i]['appt_file']) > 5) $ishidden = 'show';
		}

?>
		<div id="<?php echo $value[$i]['uid'] ?>" class="appt-item-wrapper <?php echo $ishidden . ' ' . $state ?>">
			<div class="apptitem-header">
				<div class="row modul">
					<div class="col-5 modul">
						<div class="row">
							<span><span><i class="ri-time-line"></i><?php echo $value[$i]['time'] . ' Uhr ' . $value[$i]['durration'] . '</span></br><span id="appt_info_name">' . $value[$i]['lastname'] . ', ' . $value[$i]['firstname'] ?></span></span>
						</div>
					</div>
					<div class="col-5 modul flexright">
						<span id="appt_info_adress"><?php echo $value[$i]['street'] . ' ' . $value[$i]['streetnumber'] . $value[$i]['streetnumberadd'] . '</br>' . $value[$i]['city'] ?>
						</span>
					</div>
					<div class="col-1 modul icon">
						<i style="color:#2767c7;font-size: 20px;" class="ri-arrow-right-s-line"></i>
					</div>
				</div>

			</div>
			<div class="apptitem-body colapsed">
				<div class="row fullwidth">
					<a class="col-6 itemnavigation btnhbginfo" href="maps://?address=<?php echo urlencode($origin) ?>">
						<i class="ri-map-pin-2-line"></i>GO
					</a>
					<div class="col-6 homeinfo">
						<div class="linewrap">Tel.:<a href="tel:<?php echo $value[$i]['phone1'] ?>"> <?php echo $value[$i]['phone1'] ?></a></div>
						<?php if (strlen($value[$i]['phone2']) > 0) { ?>
							<div class="linewrap">Tel.:<a href="tel:<?php echo $value[$i]['phone2'] ?>"> <?php echo $value[$i]['phone2'] ?></a></div>
						<?php } else { ?>
							<div class="linewrap">Tel.: - </div>
						<?php } ?>
					</div>
					<div class="row fullwidth">
						<div class="padding-10"></div>
						<div class="row fullwidth">
							<div class="col aligncenter">
								<span>- Termininfo -</span>
							</div>
						</div>
						<div class="row fullwidth">
							<div class="appt-info-body">
								<table>
									<tbody>
										<tr>
											<td>Info:</td>
											<td><?php echo $value[$i]['comment'] ?></td>
										</tr>
										<tr>
											<td>Von:</td>
											<td><?php echo $value[$i]['username'] ?></td>
										</tr>
										<tr>
											<td>Am:</td>
											<td><?php echo $cdate . ' - ' . $ctime . ' Uhr' ?></td>
										</tr>
										<tr>
											<td>Unit:</td>
											<td><?php echo $value[$i]['unit'] ?></td>
										</tr>
										<tr>
											<td>HomeID:</td>
											<td><?php echo $value[$i]['homeid'] ?></td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
				<?php if ($state === 'open') { ?>
					<div class="row fullwidth">
						<?php
						if (($value[$i - 1]['appt_status'] === 'done' && strlen($value[$i - 1]['appt_file']) > 5) || ($value[$i - 1]['appt_status'] !== 'done' && strlen($value[$i - 1]['appt_status']) > 2) || $i === 0) {
							//if ((strlen($value[$i - 1]['appt_status']) !== 0 && strlen($value[$i - 1]['appt_file']) > 5) || $i === 0) { 
						?>
							<div id="<?php echo $value[$i]['uid'] ?>" class="bodycol hbginfo buttonwrapper aligncenter fullwidth">
								<div class="row fullwidth"><textarea placeholder="Kommentar zur Hausbegehung" class="apptcomment" name="comment" id="comment<?php echo $value[$i]['uid'] ?>" rows="2"></textarea></div>
								<span style="display:none;" class="thishomeid" id="<?php echo $value[$i]['homeid'] ?>"><?php echo $value[$i]['homeid'] ?></span>
								<span style="display:none;" class="thiscity" id="<?php echo $value[$i]['city'] ?>"><?php echo $value[$i]['city'] ?></span>
								<span style="display:none;" class="thiscityid" id="<?php echo $value[$i]['city_id'] ?>"><?php echo $value[$i]['city_id'] ?></span>
								<span style="display:none;" class="thiscityclient" id="<?php echo $value[$i]['client'] ?>"><?php echo $value[$i]['client'] ?></span>
								<button class="btnhbginfo yes"><i class="ri-check-line"></i> HBG erledigt</button>
								<button class="btnhbginfo no"><i class="ri-close-line"></i> abbruch</button>
							</div>
							<div class="bodycol hbginfo buttonwrapper aligncenter fullwidth">
								<div class="row aligncenter fullwidth aligncenter fullwidth">
									<input id="fileupload<?php echo $value[$i]['uid'] ?>" class="file-input" type="file">
								</div>
								<div class="row aligncenter fullwidth aligncenter fullwidth">
									<div class="uploadloader loader hidden"></div>
									<div class="swal2-icon swal2-success swal2-animate-success-icon hidden" style="display: flex;">
										<div class="swal2-success-circular-line-left" style="background-color: rgb(255 255 255 / 0%);"></div>
										<span class="swal2-success-line-tip"></span>
										<span class="swal2-success-line-long"></span>
										<div class="swal2-success-ring"></div>
										<div class="swal2-success-fix" style="background-color: rgb(255 255 255 / 0%);"></div>
										<div class="swal2-success-circular-line-right" style="background-color: rgb(255 255 255 / 0%);"></div>
									</div>
									<div class="swal2-icon swal2-error swal2-animate-error-icon hidden" style="display: flex;"><span class="swal2-x-mark"><span class="swal2-x-mark-line-left"></span><span class="swal2-x-mark-line-right"></span></span></div>
								</div>

							</div>



						<?php } else { ?>
							<div class="bodycol hbginfo buttonwrapper aligncenter fullwidth">

								<span>Eine vorherige HBG wurde nicht abgeschlossen</span>

							</div>
					<?php }
					} ?>
					</div>
			</div>

		</div>
		</div>
<?php if ($i < $length - 1 && $state === 'open') {
			echo '<div class="appt-distance ' .  $ishidden . '"><i class="ri-car-line"></i> ' . $duration . '</div>';
		}
	}
}
