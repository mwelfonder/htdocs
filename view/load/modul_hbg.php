<?php


include "../../view/includes/functions.php";

$func = $_POST['func'];
if ($func === "load_table") {
	load_table('open');
	echo '<div class="mod-title aligncenter appt-done">Abgeschlossene HBG´s </div>';
	load_table('done');
} else if ($func === "safe_hbg_status") {
	$ident = $_POST['ident'];
	$status = $_POST['status'];
	safe_hbg_status($ident, $status);
} 




function safe_hbg_status($ident, $status)
{
	$conn = dbconnect();
	$query = "UPDATE `scan4_hbg` SET `appt_status`='" . $status . "' WHERE ident='" . $ident . "'";
	mysqli_query($conn, $query) or die(mysqli_error($conn));
	$conn->close();
	echo 'imdone';
}
 
function get_all_appt()
{
	$conn = dbconnect();
	$length = 0; 
	$date = date("Y-m-d");
	$query = "SELECT * FROM `scan4_hbg` WHERE hausbegeher = 'BenGetschmann' AND `date` = '".$date."' ORDER BY `scan4_hbg`.`time` ASC";
	$result = mysqli_query($conn, $query);
	while ($obj = mysqli_fetch_assoc($result)) {
		$entry[] = $obj;
		$length++;
		//array_push($entry,$row);
	}

	mysqli_free_result($result);
	for ($i = 0; $i < $length; $i++) {
		$homeid = $entry[$i]['homeid'];
		$query = "SELECT street,streetnumber,streetnumberadd,city,plz,firstname,lastname,phone1,phone2,homeid,unit FROM `scan4_homes` WHERE `homeid` = '" . $homeid . "'";
		$result = mysqli_query($conn, $query);
		while ($obj = mysqli_fetch_assoc($result)) {
			$a = array_merge($obj, $entry[$i]);
			//$data[$obj["homeid"]] = $a;
			$data[] = $a;
		}
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
		if ($state === 'open' && strlen($value[$i]['appt_status']) !== 0) {
			$ishidden = 'hidden';
		} else if ($state === 'done' && strlen($value[$i]['appt_status']) === 0) {
			$ishidden = 'hidden';
		} else {
			$ishidden = 'show';
		}

?>
		<div id="<?php echo $value[$i]['ident'] ?>" class="appt-item-wrapper <?php echo $ishidden . ' ' . $state ?>">
			<div class="apptitem-header">
				<div class="row modul">
					<div class="col-5 modul">
						<div class="row">
							<span><span><i class="ri-time-line"></i><?php echo $value[$i]['time'] . ' Uhr ' . $value[$i]['durration'] . '</span></br><span id="appt_info_name">' . $value[$i]['lastname'] . ', ' . $value[$i]['firstname'] ?></span></span>
						</div>
					</div>
					<div class="col-5 modul flexright">
						<span id=" b  bvc bv "><?php echo $value[$i]['street'] . ' ' . $value[$i]['streetnumber'] . $value[$i]['streetnumberadd'] . '</br>' . $value[$i]['city'] ?>
						</span>
					</div>
					<div class="col-1 modul icon">
						<i style="color:#2767c7;font-size: 20px;" class="ri-arrow-right-s-line"></i>
					</div>
				</div>

			</div>
			<div class="apptitem-body colapsed">
				<div class="row fullwidth">
					<a class="col-6 itemnavigation" href="maps://?address=<?php echo urlencode($origin) ?>">
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
						if (strlen($value[$i - 1]['appt_status']) !== 0 || $i === 0) { ?>
							<div id="<?php echo $value[$i]['ident'] ?>" class="bodycol hbginfo buttonwrapper aligncenter fullwidth">
								<button id="" class="btnhbginfo yes"><i class="ri-check-line"></i> HBG erledigt</button>
								<button class="btnhbginfo no"><i class="ri-close-line"></i> abbruch</button>
							</div>
						<?php } else { ?>
							<div class="bodycol hbginfo buttonwrapper aligncenter fullwidth">
								<button>Eine vorherige HBG wurde nicht abgeschlossen</button>
							</div>
					<?php }
					} ?>
					</div>
			</div>

		</div>
		</div>
<?php if ($i < $length - 1 && $state === 'open') {
			echo '<div class="appt-distance ' .  $ishidden . '"><i class="ri-treasure-map-line"></i> ' . $duration . '</div>';
		}
	}
}
