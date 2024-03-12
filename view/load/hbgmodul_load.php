<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
include "../../view/includes/functions.php";

$logged_in = $user->data();
$currentuser = $logged_in->username;
if (isset($_POST['selectedDate'])) {
	$date_selected = $_POST['selectedDate'];
} else {
	$date_selected = date("Y-m-d");
}
if (isset($_POST['selectedUser'])) {
	$selectedUser = $_POST['selectedUser'];
} else {
	$selectedUser = "Test1234";
}


$func = $_POST['func'];
if ($func === "load_table") {
	load_table('open', $date_selected);
	echo '<div class="mod-title aligncenter appt-done">Erledigte Termine 👍 Gut gemacht!</div>';
	load_table('done', $date_selected);
	load_userlog($homeid);
} else if ($func === "safe_hbg_status") {
	$uid = $_POST['uid'];
	$status = $_POST['status'];
	$comment = $_POST['comment'];
	$user = $_POST['user'];
	$file = $_POST['file'];
	safe_hbg_status($uid, $status, $comment, $user, $file);
} else if ($func === "load_bugreport") {
	$homeid = $_POST['homeid'];
	load_bugreport($homeid, $date_selected);
} else if ($func === "load_notatt") {
	$homeid = $_POST['homeid'];
	load_notatt($homeid, $date_selected);
}

function load_documents($homeid)
{
	$conn = dbconnect();
	$query = "SELECT * FROM `scan4_homes_documents` WHERE homeid = '$homeid' ORDER BY datetime DESC";


	$documents = [];
	$result = mysqli_query($conn, $query);

	while ($row = mysqli_fetch_assoc($result)) {
		$documents[] = $row;
	}

	$conn->close();
	return $documents;
}


function load_userlog($homeid)
{
	$conn = dbconnect();

	// SQL-Abfrage für scan4_userlog
	$query1 = "SELECT datetime, user, action1 as action, action2 FROM `scan4_userlog` WHERE homeid = '$homeid' AND (action1 = 'Termin wurde aktiviert' OR action1 = 'hbgmodul set appt status to done with file' OR action1 = 'created an hbg' OR action1 = 'moved an hbg' OR action1 = 'storno an appointment')";

	// SQL-Abfrage für scan4_hbgcheck
	$query2 = "SELECT datetime, user, status as action, comment FROM `scan4_hbgcheck` WHERE homeid = '$homeid'";

	// SQL-Abfrage für scan4_tickets
	$query3 = "SELECT date, time, user, object_title as action, object_content as comment FROM `scan4_tickets` WHERE homeid = '$homeid'";

	// SQL-Abfrage für scan4_hbg
	$query4 = "SELECT appt_datetime as datetime, hausbegeher as user, appt_status as action, appt_comment as comment FROM `scan4_hbg` WHERE homeid = '$homeid'";

	$data = [];

	// Ergebnisse von scan4_userlog abrufen
	$result1 = mysqli_query($conn, $query1);
	while ($row = mysqli_fetch_assoc($result1)) {
		if ($row['action'] == 'created an hbg' && !empty($row['action2'])) {
			// Split action2 at the first space to get user and date
			list($userAction2, $dateAction2) = explode(' ', $row['action2'], 2);
			$row['comment'] = "HBG erstellt für " . $userAction2 . " für " . $dateAction2;
		}
		unset($row['action2']);  // remove action2 from the final data
		$data[] = $row;
	}

	// Ergebnisse von scan4_hbgcheck abrufen
	$result2 = mysqli_query($conn, $query2);
	while ($row = mysqli_fetch_assoc($result2)) {
		$data[] = array('datetime' => $row['datetime'], 'user' => $row['user'], 'action' => $row['action'], 'comment' => $row['comment']);
	}

	// Ergebnisse von scan4_tickets abrufen und date/time kombinieren
	$result3 = mysqli_query($conn, $query3);
	while ($row = mysqli_fetch_assoc($result3)) {
		$datetime = $row['date'] . ' ' . $row['time'];  // Zusammenführen von Datum und Zeit
		$data[] = array('datetime' => $datetime, 'user' => $row['user'], 'action' => $row['action'], 'comment' => $row['comment'], 'source' => 'ticket');  // Hinzufügen der Quelle "ticket"
	}

	// Ergebnisse von scan4_hbg abrufen
	$result4 = mysqli_query($conn, $query4);
	while ($row = mysqli_fetch_assoc($result4)) {
		$data[] = array('datetime' => $row['datetime'], 'user' => $row['user'], 'action' => $row['action'], 'comment' => $row['comment']);
	}

	// Daten kombinieren und nach datetime sortieren
	usort($data, function ($a, $b) {
		return strcmp($b['datetime'], $a['datetime']);
	});

	$conn->close();
	return $data;
}

$homeid = $_GET['homeid'];  // Stellen Sie sicher, dass dies sicher ist!

$userlogs = load_userlog($homeid);

if (count($userlogs) > 0) {
} else {
	echo 'not found';
}

function load_notatt($homeid, $date_selected)
{
	$conn = dbconnect();
	$query = "SELECT * FROM `scan4_homes_documents` WHERE datetime LIKE '$date_selected%' AND homeid = '$homeid' AND status = 'Kunde nicht da'";

	$result = mysqli_query($conn, $query);

	// Initialisierung von $data
	$data = [];

	while ($row = mysqli_fetch_assoc($result)) {
		$data[] = $row;
	}
	$conn->close();

	if (count($data) > 0) {
		echo 'found';
	} else {
		echo 'not found';
	}
}



function load_bugreport($homeid, $date_selected)
{
	$conn = dbconnect();
	$query = "SELECT * FROM `scan4_bug_reports` WHERE datetime LIKE '$date_selected%' AND bug_homeid = '$homeid'";
	$result = mysqli_query($conn, $query);

	$data = [];

	while ($row = mysqli_fetch_assoc($result)) {
		$data[] = $row;
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

function get_all_appt($date_selected)
{
	global $currentuser;
	$conn = dbconnect();
	$length = 0;
	$date = $date_selected;

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
	if ($currentuser === 'JensKohl2') $currentuser = 'SamantaThomschke';
	if ($currentuser === 'admin') {
		$query = "SELECT scan4_citylist.city_id,scan4_hbg.*,scan4_homes.client,scan4_homes_adressid,scan4_homes.street,scan4_homes.streetnumber,scan4_homes.lat,scan4_homes.lon,scan4_homes.streetnumberadd,scan4_homes.city,scan4_homes.plz,scan4_homes.firstname,scan4_homes.lastname,scan4_homes.phone1,scan4_homes.phone2,scan4_homes.unit FROM `scan4_hbg` 
		INNER JOIN scan4_homes ON scan4_hbg.homeid=scan4_homes.homeid  
		Inner JOIN scan4_citylist ON scan4_citylist.city=scan4_homes.city
		WHERE scan4_hbg.status = 'PLANNED' AND scan4_hbg.hausbegeher = '" . $currentuser . "' AND scan4_hbg.date = '" . $date . "' ORDER BY `scan4_hbg`.`time` ASC;";
	} else {
		$query = "SELECT 
		scan4_citylist.city_id,
		scan4_hbg.*,
		scan4_homes.client,
		scan4_homes.street,
		scan4_homes.streetnumber,
		scan4_homes.lat,
		scan4_homes.lon,
		scan4_homes.streetnumberadd,
		scan4_homes.city,
		scan4_homes.plz,
		scan4_homes.firstname,
		scan4_homes.lastname,
		scan4_homes.adressid,
		scan4_homes.phone1,
		scan4_homes.phone2,
		scan4_homes.unit,
		scan4_tickets.object_title -- Select all columns from scan4_tickets
	  FROM `scan4_hbg`
	  INNER JOIN scan4_homes ON scan4_hbg.homeid=scan4_homes.homeid
	  INNER JOIN scan4_citylist ON scan4_citylist.city=scan4_homes.city
	  LEFT JOIN scan4_tickets ON scan4_tickets.homeid=scan4_hbg.homeid -- Join scan4_tickets
	  WHERE scan4_hbg.status = 'PLANNED'
		AND scan4_hbg.hausbegeher = '" . $currentuser . "'
		AND scan4_hbg.date = '" . $date . "'
	  ORDER BY `scan4_hbg`.`time` ASC;";
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


function load_table($state, $date_selected)
{
	$value = get_all_appt($date_selected);
	global $currentuser;
	$length = count($value);

	//echo $length;
	$a_done = array();

	ob_start();
	for ($i = 0; $i < $length; $i++) {

		$documents = load_documents($value[$i]['homeid']);
		$origin = $value[$i]['street'] . ' ' . $value[$i]['streetnumber'] . $value[$i]['streetnumberadd'] . ',' . $value[$i]['plz'] . ' ' . $value[$i]['city'] . ',Deutschland';
		$destination = $value[$i + 1]['street'] . ' ' . $value[$i + 1]['streetnumber'] . $value[$i + 1]['streetnumberadd'] . ',' . $value[$i + 1]['plz'] . ' ' . $value[$i + 1]['city'] . ',Deutschland';
		/*
		$apiUrl = "https://services.scan4-gmbh.de/route";

		$queryString =
			"point=" . urlencode($origin) .
			"&point=" . urlencode($destination) .
			"&profile=car&layer=OpenStreetMap";

		$fullUrl = $apiUrl . "?" . $queryString;
		echo $fullUrl;
		// Get data using cURL
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $fullUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$distance_data = curl_exec($ch);
		curl_close($ch);

		$distance_arr = json_decode($distance_data, true);

		if (isset($distance_arr['paths'][0]['time'])) {
			$drivingTimeMilliseconds = $distance_arr['paths'][0]['time'];
			$drivingTimeMinutes = floor($drivingTimeMilliseconds / 60000);

			// The equivalent of "getBackgroundColor" in PHP might need to be written
			// Here we just use a placeholder function.
			$backgroundColor = getBackgroundColor($drivingTimeMinutes);

			echo "<div class='toEvent' style='background:{$backgroundColor}'>{$drivingTimeMinutes}m</div>";
		} else {
			echo "Es gab ein Problem bei der Abrufung der Fahrtzeit.";
		}
		// Get the elements as array
		$elements = $distance_arr->rows[0]->elements;
		$distance = $elements[0]->distance->text;
		$duration = $elements[0]->duration->text;
		$duration = str_replace("hours", "Std", $duration);
		$duration = str_replace("mins", "min", $duration);
		//echo "From: ".$origin_addresses."<br/> To: ".$destination_addresses."<br/> Distance: <strong>".$distance ."</strong><br/>";
		//echo $i. " Duration: <strong>" . $duration . "";
*/
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
		} else if ($state === 'late') {

			$current_time = new DateTime(); // Aktuelle Zeit
			// Überprüfen, ob die Differenz zwischen der aktuellen Uhrzeit und $value[$i]['time'] 30 Minuten oder weniger beträgt
			$appointment_time = new DateTime($value[$i]['time']);
			$interval = $current_time->diff($appointment_time);

			$minutes_difference = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
			if ($value[$i]['appt_status'] === 'done' && strlen($value[$i]['appt_file']) > 5) $ishidden = 'hidden';
			if ($value[$i]['appt_status'] !== 'done' && strlen($value[$i]['appt_status']) > 2) $ishidden = 'hidden';
			if ($minutes_difference <= 30) {
				$ishidden = 'late';
			}
			// Sie können auch weitere Aktionen oder Überprüfungen hier hinzufügen, wenn nötig.
		}

?>
		<div id="<?php echo $value[$i]['uid'] ?>" class="appt-item-wrapper <?php echo $ishidden . ' ' . $state ?>">
			<div class="apptitem-header">
				<div class="row modul" <?php
										if (($value[$i]['appt_status'] !== 'done' || empty($value[$i]['appt_status']) || is_null($value[$i]['appt_status'])) && $state !== 'open') {
											echo 'style="background-color: #ffcccc;"';
										} elseif ($value[$i]['appt_status'] === 'done') {
											echo 'style="background-color: #ccffcc;"';
										}
										?>>
					<div class="col-5 modul">
						<div class="row">
							<span>
								<span>
									<i class="ri-time-line"></i>
									<?php
									echo $value[$i]['time'] . ' Uhr ';
									echo $value[$i]['activated'] == 1 ? '&#9989;' : '&#10060;';
									echo $value[$i]['durration'];
									?>
									<?php if (!empty($value[$i]['object_title'])) : ?>
										<span style="color: blue;">Ticket vorhanden</span>
									<?php endif; ?>

								</span>
								</br>
								<span id="appt_info_name">
									<?php echo $value[$i]['lastname'] . ', ' . $value[$i]['firstname']; ?>
								</span>
							</span>
						</div>
					</div>


					<div class="col-5 modul flexright">
						<span id="appt_info_adress">
							<div class="address-text">
								<?php
								echo $value[$i]['street'] . ' ' . $value[$i]['streetnumber'] . $value[$i]['streetnumberadd'] . ', ' . $value[$i]['city'];
								?>
							</div>
							<a class="itemnavigation btnhbginfo" href="maps://?address=<?php echo urlencode($origin) ?>">
								<i class="ri-map-pin-2-line"></i>GO
							</a>
						</span>
					</div>




					<div class="col-1 modul icon">
						<i style="color:#2767c7;font-size: 20px;" class="ri-arrow-right-s-line"></i>
					</div>
				</div>

			</div>

			<div class="apptitem-body colapsed">
				<div class="container mt-5">
					<div class="row fullwidth">
						<div class="col aligncenter">
							<span>- Termininfo -</span>
						</div>
					</div>

					<div class="row">
						<!-- Linke Spalte -->
						<div class="col-lg-6 mb-4">
							<div class="p-4 border rounded">

								<!-- Telefonnummern -->
								<div class="mb-4">
									<span class="fw-bold">Tel.:</span> <a href="tel:<?php echo $value[$i]['phone1'] ?>"><?php echo $value[$i]['phone1'] ?></a><br>
									<?php if (strlen($value[$i]['phone2']) > 0) { ?>
										<span class="fw-bold">Tel.:</span> <a href="tel:<?php echo $value[$i]['phone2'] ?>"><?php echo $value[$i]['phone2'] ?></a>
									<?php } else { ?>
										<span class="fw-bold">Tel.:</span> -
									<?php } ?>
								</div>
								<div class="address-text-body">
									<?php
									echo $value[$i]['street'] . ' ' . $value[$i]['streetnumber'] . $value[$i]['streetnumberadd'] . ', ' . $value[$i]['city'];
									?>
								</div><br>
								<div class="mb-3"><span class="fw-bold">Info:</span> <?php echo $value[$i]['comment'] ?></div>
								<div class="mb-3"><span class="fw-bold">Von:</span> <?php echo $value[$i]['username'] ?></div>
								<div class="mb-3"><span class="fw-bold">Am:</span> <?php echo $cdate . ' - ' . $ctime . ' Uhr' ?></div>
								<div class="mb-3"><span class="fw-bold">Unit:</span> <?php echo $value[$i]['unit'] ?></div>
								<div class="mb-3"><span class="fw-bold">HomeID:</span> <?php echo $value[$i]['homeid'] ?></div>
								<div class="mb-3"><span class="fw-bold">AdressID:</span> <?php echo $value[$i]['adressid'] ?></div>
								<div class="mb-3"><span class="fw-bold">Freigeschaltet:</span> <?php echo $value[$i]['activated'] == 1 ? '&#9989;' : '&#10060;'; ?></div>
							</div>
						</div>

						<!-- Rechte Spalte -->
						<div class="col-lg-6">
							<div class="p-4 border rounded">



								<div class="mb-4 d-flex justify-content-between">
									<button class="btn btn-primary" onclick="toggleContent('timeline')">Timeline</button>
									<button class="btn btn-secondary" onclick="toggleContent('documents')">Dokumente</button>
									<!-- <button class="btn btn-info" id="uploadToggleBtn"><i class="ri-add-line"></i></button>-->
								</div>

								<div class="infoboard_timelinewrapper" style="max-height:300px; overflow-y:scroll;">
									<?php
									$userlogs = load_userlog($value[$i]['homeid']);
									foreach ($userlogs as $log) :
										if (empty($log['datetime'])) {
											continue;
										}
										// Zuordnung für die Actions definieren
										$actionNames = [
											"Termin wurde aktiviert" => "Termin-Aktivierung",
											"hbgmodul set appt status to done with file" => "Hausbegeher hat ein Protokoll hochgeladen",
											"created an hbg" => "HBG erstellt",
											"moved an hbg" => "HBG verschoben",
											"DONE" => "HBG überprüft - Status: Erledigt",
											"DONE EXCEL" => "HBG überprüft - Status: Erledigt, Excel",
											"done" => "Hausbegeher hat den Termin abgeschlossen",
											"OPEN" => "HBG überprüft - Status: Geöffnet",
											"STOPPED" => "HBG überprüft - Status: Gestoppt",
											"storno an appointment" => "Termin storniert"
										];
										// Farben für die Aktionen definieren
										$actionColors = [
											"Hausbegeher hat den Termin abgeschlossen" => "#CCFFCC",
											"Hausbegeher hat ein Protokoll hochgeladen" => "#CCFFCC",
											"HBG überprüft - Status: Erledigt, Excel" => "#CCFFCC",
											"HBG überprüft - Status: Erledigt" => "#CCFFCC",
											"HBG überprüft - Status: Geöffnet" => "#FFCCCC",
											"HBG überprüft - Status: Gestoppt" => "#FFCCCC'"
										];

										$backgroundColor = '#FFFFFF'; // Standardfarbe
										if (isset($log['source']) && $log['source'] === 'ticket') {
											$backgroundColor = '#e0a05c'; // Orange für Tickets
										} else {
											$backgroundColor = $actionColors[$actionNames[$log['action']]] ?? '#FFFFFF';
										}
									?>
										<div class="timeline-box" style="background-color: <?= $backgroundColor; ?>">
											<div class="timeline-content">
												<div class="timeline-content-inner">
													<p><b></b> <?= $actionNames[$log['action']] ?? $log['action']; ?></p>
													<?php if (isset($log['comment']) && !empty(trim($log['comment']))) : ?>
														<p><b></b> <?= $log['comment'] ?></p>
													<?php endif; ?>
													<div style="display: flex; justify-content: space-between;">
														<p class="entrie_timestamp" style="margin-right: 10px;"><?= date("Y-m-d", strtotime($log['datetime'])) . " - " . date("H:i", strtotime($log['datetime'])) ?> Uhr</p>
														<p class="entrie_creator"><?= $log['user'] ?></p>
													</div>
												</div>
											</div>
										</div>
									<?php endforeach; ?>
								</div>





								<div class="documents-content" style="display: none;">

									<div class="upload_wrapper" data-user="<?php echo $currentuser; ?>" data-homeid="<?php echo $value[$i]['homeid']; ?>">

										<div class="select-area upload-area">
											<label class="upload-label">
												Hier klicken zum hochladen
												<input type="file" name="document" class="file-upload" style="display: none;">
											</label>
											<p><span class="selected-filename"></span></p>

										</div>


										<div class="Upload_status_section hidden">
											<select name="status" class="form-select" style="width: 100%;">
											<option value="Kunde nicht da">-- Grund wählen --</option>
												<option value="Kunde nicht da">Kunde nicht da</option>
												<option value="MDU Datenbeweis">MDU Datenbeweis</option>
												<option value="Vertragsdaten">Vertragsdaten</option>
												<option value="Hausanschluss Informationen">Hausanschluss Informationen</option>
												<option value="Vorschaeden">Vorschäden</option>
												<option value="sonstiges">Sonstiges</option>
											</select>
										</div>

										<div class="Upload_comment_section hidden">
											<textarea name="comment" class="form-textarea" placeholder="Bemerkung zur Datei"></textarea>
										</div>

										<div class="Upload_submit_area hidden">
											<button type="button" class="form-btn Upload_btn">Hochladen</button>
										</div>

									</div>





									<?php if (count($documents) > 0) : ?>
										<div class="documentsFilesWrapper">
											<?php foreach ($documents as $document) : ?>
												<div class="mb-3 border p-3 docFileItem">

													<?php
													$path = $document['location'];
													$uploadsPos = strpos($path, '/uploads');
													if ($uploadsPos !== false) {
														$cleanPath = substr($path, $uploadsPos);
													} else {
														$cleanPath = $path;
													}
													?>
													<a href="<?php echo $cleanPath; ?>"><?php echo basename($cleanPath); ?></a>


													<?php if (!empty($document['comment'])) : ?>
														<div><?php echo $document['comment']; ?></div>
													<?php endif; ?>
													<div class="text-muted"><?php echo $document['user']; ?> | <?php echo date("H:i", strtotime($document['datetime'])); ?> Uhr</div>
												</div>
											<?php endforeach; ?>
										</div>
									<?php else : ?>
										<div class="text-center">
											<i class="ri-ghost-line" style="font-size: 3rem; color: #aaa;"></i>
											<div>Keine Daten vorhanden.</div>
										</div>
									<?php endif; ?>

								</div>







							</div>
						</div>
					</div>
				</div>
				<script>
					$(document).ready(function() {

						// On file selected
						$('.file-upload').on('change', function() {
							if ($(this).val() != "") {
								$(this).closest('.upload_wrapper').find('.Upload_status_section').removeClass('hidden');
							} else {
								$(this).closest('.upload_wrapper').find('.Upload_status_section, .Upload_comment_section, .Upload_submit_area').addClass('hidden');
							}
						});

						// On select change
						$('.form-select').on('change', function() {
							if ($(this).val() != "") {
								$(this).closest('.upload_wrapper').find('.Upload_comment_section, .Upload_submit_area').removeClass('hidden');
							} else {
								$(this).closest('.upload_wrapper').find('.Upload_comment_section, .Upload_submit_area').addClass('hidden');
							}
						});
					});

					function toggleContent(contentType) {
						if (contentType === "documents") {
							$('.infoboard_timelinewrapper').hide();
							$('.documents-content').show();
							$('.apptitem-body.colapsed').show();
							$('.documents-content').css('display', 'block');
						} else if (contentType === "timeline") {
							$('.infoboard_timelinewrapper').show();
							$('.documents-content').hide();
							$('.infoboard_timelinewrapper').css('display', 'block');
						}
					}



					$(document).on('change', '.file-upload', function() {
						var filename = $(this).val().split('\\').pop(); // Get just the file name, not the full path
						$(this).closest('.upload-area').find('.selected-filename').text(filename);
					});

					$(document).on('click', '.Upload_btn', function() {

						var btn = $(this);

						// Check if already uploading
						if (btn.hasClass('uploading')) {
							return; // exit if upload is in progress
						}

						btn.addClass('uploading'); // add uploading class
						btn.prop('disabled', true); // disable the button
						btn.text('Uploading...'); // change button text



						var wrapper = $(this).closest('.upload_wrapper');

						var wrapper = $(this).closest('.upload_wrapper');

						// Get data attributes
						var username = wrapper.data('user');
						var homeid = wrapper.data('homeid');

						// Get file, status, and comment
						var file = wrapper.find('.file-upload')[0].files[0];
						var status = wrapper.find('.form-select').val();
						var comment = wrapper.find('.form-textarea').val();

						// Create a FormData object to hold the file and other data
						var formData = new FormData();
						formData.append('document', file);
						formData.append('status', status);
						formData.append('comment', comment);
						formData.append('username', username);
						formData.append('homeid', homeid);

						// Send the FormData object to the server using AJAX
						$.ajax({
							url: 'view/load/hbgmodul_upload.php',
							type: 'POST',
							data: formData,
							processData: false, // Important! Prevents jQuery from transforming the data into a query string
							contentType: false,
							success: function(data) {
								console.log('file uploaded');
								btn.removeClass('uploading');
								btn.prop('disabled', false);
								btn.text('Hochladen'); // or another message like "Upload Successful"
								$(this).closest('.upload_wrapper').find('.Upload_status_section, .Upload_comment_section, .Upload_submit_area').addClass('hidden');

								// Identify the closest `.documentsFilesWrapper` to the `.upload_wrapper`
								var wrapper = $(this).closest('.upload_wrapper').siblings('.documentsFilesWrapper');

								// Get the current year and month
								var currentYear = (new Date()).getFullYear();
								var currentMonth = ('0' + ((new Date()).getMonth() + 1)).slice(-2); // +1 because JS months are 0-indexed

								// Construct the new div with the link to the file
								var fileLink = '/uploads/documents/' + currentYear + '/' + currentMonth + '/' + username + '_' + homeid + '_' + (new Date()).toISOString().slice(0, 19).replace("T", "_").replace(/:/g, "-") + '_' + status.replace(' ', '_') + '.pdf';

								var fileDisplayName = fileLink.split('/').pop(); // to display only the filename, not the full path

								var newDiv = `
<div class="mb-3 border p-3 docFileItem">
	<a href="${fileLink}">${fileDisplayName}</a>
	<div class="text-muted">${username} | ${(new Date()).getHours() + ':' + (new Date()).getMinutes()} Uhr</div>
</div>
`;

								// Prepend the new div to the wrapper
								wrapper.prepend(newDiv);
							},
							error: function() {
								console.log('file upload failed');
								btn.removeClass('uploading');
								btn.prop('disabled', false);
								btn.text('Retry Upload'); // or another appropriate error message
							}
						});
					});
				</script>

				<?php
				if ($state === 'open') {
				?>
					<div class="row fullwidth">
						<?php
						$previousDoneWithFile = $value[$i - 1]['appt_status'] === 'done' && strlen($value[$i - 1]['appt_file']) > 5;
						$previousNotDoneWithStatus = $value[$i - 1]['appt_status'] !== 'done' && strlen($value[$i - 1]['appt_status']) > 2;
						$isFirst = $i === 0;

						$appointmentDate = strtotime($value[$i]['date']);
						$currentDate = strtotime(date("Y-m-d"));


						if ($appointmentDate > $currentDate) {
						?>
							<div class="bodycol hbginfo buttonwrapper aligncenter fullwidth">
								<span>Der Termin liegt in der Zukunft und kann nicht bearbeitet werden. &#128151; </span>
							</div>
						<?php
						} elseif ($previousDoneWithFile || $previousNotDoneWithStatus || $isFirst) {
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
						<?php
						} else {
						?>
							<div class="bodycol hbginfo buttonwrapper aligncenter fullwidth">
								<span>Eine vorherige HBG wurde nicht abgeschlossen</span>
							</div>
						<?php
						}
						?>
					</div>
				<?php
				}
				?>
			</div>
		</div>

		</div>
		</div>

		<style>
			.upload-form .form-group {
				margin-bottom: 20px;
			}

			.upload-area {
				border: 2px dashed #ccc;
				padding: 20px;
				cursor: pointer;
				text-align: center;
			}

			.upload-area label {
				cursor: pointer;
			}

			.upload-form input[type="file"] {
				display: none;
			}

			.form-textarea {
				width: 100%;
				padding: 10px;
				border-radius: 4px;
				border: 1px solid #ccc;
				resize: vertical;
			}

			.form-btn {
				background-color: #007bff;
				color: white;
				padding: 10px 15px;
				border: none;
				border-radius: 4px;
				cursor: pointer;
			}

			.submit-area {
				text-align: right;
			}
		</style>

<?php if ($i < $length - 1 && $state === 'open') {
			//echo '<div class="appt-distance ' .  $ishidden . '"><i class="ri-car-line"></i> ' . $duration . '</div>';
		}
	}
}
