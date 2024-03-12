<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}



include "../../view/includes/functions.php";

date_default_timezone_set('Europe/Berlin');
if (!isset($_POST['func'])) {
	die();
}


$logged_in = $user->data();
$currentuser = $logged_in->username;




$func = $_POST['func'];

if ($func === "search") {
	$term = $_POST['term'];
	if (isset($_POST['code'])) {
		$code = $_POST['code'];

		// if code is equal to report then trigger new function
		if ($code === "report") {
			get_report($term);
		}
	} else {

		get_searchbar($term);
	}
}


function get_report($term)
{
	$conn = dbconnect();
	$int = 0;
	$query = "SELECT * FROM `scan4_citylist` WHERE city LIKE '%" . $term . "%'";
	if ($result = $conn->query($query)) {
		while ($row = mysqli_fetch_assoc($result)) {
			$int++;
			$array[] = $row;
			// if int is equal to 10 then break
			if ($int === 10) {
				break;
			}
		}
		$result->free_result();
	}
	//close connection
	$conn->close();

	ob_start();
	for ($i = 0; $i < count($array); $i++) {
?>
		<div id="<?php echo $array[$i]['city'] ?>" class="searchbar reportitem"><a class="reportitemlink" href="https://crm.scan4-gmbh.de/view/reports.php?report=<?php echo $array[$i]['city'] ?>"><span><i class="ri-file-excel-2-line"></i> <?php echo $array[$i]['city'] ?></span><span><?php echo $array[$i]['client'] ?></span><span><?php echo $array[$i]['carrier'] ?></span></a></div>
<?php
	}
}

function get_searchbar($term)
{

	if (is_numeric(substr($term, 0, 3))) {
		$term = str_replace('-', '', $term);
	}


	$conn = dbconnect();
	$index = 0;
	$html = "";
	if (preg_match('/\s/', $term)) {
		$a_split = explode(" ", $term);
		// 3 strings and last is digit == City + Street + Number
		if (isset($a_split[2]) && is_numeric($a_split[2]) === true) {
			$query = "SELECT * FROM `scan4_homes` WHERE city LIKE '%" . $a_split[0] . "%' AND street LIKE '%" . $a_split[1] . "%' AND streetnumber LIKE '%" . $a_split[2] . "%' ORDER BY `scan4_homes`.`streetnumber` ASC LIMIT 100";
			// 2 strings and last is digit == Street + Number
		} elseif (is_numeric($a_split[1]) === true) {
			$query = "SELECT * FROM `scan4_homes` WHERE street LIKE '%" . $a_split[0] . "%' AND streetnumber LIKE '%" . $a_split[1] . "%' ORDER BY `scan4_homes`.`streetnumber` ASC LIMIT 100";
			// 2 string and last ISNOT digit == City + Street
		} elseif (is_numeric($a_split[1]) === false) {
			$query = "SELECT * FROM `scan4_homes` WHERE city LIKE '%" . $a_split[0] . "%' AND street LIKE '%" . $a_split[1] . "%'  ORDER BY `scan4_homes`.`streetnumber` ASC LIMIT 100";
			// 2 strings error prevention
		} else {
			$query = "SELECT * FROM `scan4_homes` WHERE `city`LIKE '%" . $term . "%' OR `street` LIKE '%" . $term . "%' OR homeid LIKE '%" . $term . "%' OR lastname LIKE '%" . $term . "%' OR phone1 LIKE '%" . $term . "%' OR phone2 LIKE '%" . $term . "%' ORDER BY `scan4_homes`.`streetnumber` ASC LIMIT 100";
		}
	} else {
		$query = "SELECT * FROM `scan4_homes` WHERE `city`LIKE '%" . $term . "%' OR `street` LIKE '%" . $term . "%' OR homeid LIKE '%" . $term . "%' OR lastname LIKE '%" . $term . "%' OR phone1 LIKE '%" . $term . "%' OR phone2 LIKE '%" . $term . "%' ORDER BY `scan4_homes`.`streetnumber` ASC LIMIT 100";
	}
	// Loop through Log and create array
	//echo $query;
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_assoc()) {
			$allow = true;
			if (!hasPerm(8) && $row['client'] === 'Insyte') {
				$allow = false;
			}
			if (!hasPerm(9) && $row['client'] === 'Moncobra') {
				$allow = false;
			}
			if (!hasPerm(17) && $row['client'] === 'FOL') {
				$allow = false;
			}
			// check permission to see the carrier
			if (!hasPerm(10) && $row['carrier'] === 'UGG') {
				$allow = false;
			}
			if (!hasPerm(11) && $row['carrier'] === 'DGF') {
				$allow = false;
			}
			if (!hasPerm(12) && $row['carrier'] === 'GVG') {
				$allow = false;
			}
			if (!hasPerm(18) && $row['carrier'] === 'GlasfaserPlus') {
				$allow = false;
			}

			//$allow = true;
			if ($allow == true) {
				$index++;
				$status2 = '';


				if ($row['scan4_status'] === "OPEN") {
					$status2 = '<span class="searchpill blue">OFFEN</span>';
				}
				if ($row['scan4_status'] === "DONE") {
					$status2 = '<span class="searchpill green">ERLEDIGT</span>';
				}
				if ($row['scan4_status'] === "DONE CLOUD") {
					$status2 = '<span class="searchpill green">ERLEDIGT</span>';
				}
				if ($row['scan4_status'] === "STOPPED") {
					$status2 = '<span class="searchpill red">GESTOPPT</span>';
				}
				if ($row['scan4_status'] === "PLANNED") {
					$status2 = '<span class="searchpill yellow">GEPLANT</span>';
				}
				if ($row['scan4_status'] === "OVERDUE") {
					$status2 = '<span class="searchpill lila">ERLEDIGT</span>';
				}
				if ($row['scan4_status'] === "WRONG") {
					$status2 = '<span class="searchpill red">ERLEDIGT</span>';
				}
				if ($row['scan4_status'] === "PENDING") {
					$status2 = '<span class="searchpill orange">OFFEN</span>';
				}
				if ($row['scan4_status'] === "MISSING") {
					$status2 = '<span class="searchpill red">OFFEN</span>';
				}

				if ($row['priority'] != "") {
					$isprio = '<span class="searchpill prio"><i class="fa-solid fa-star"></i> PRIO</span>';
				}
				$html .= "<div id='{$row['homeid']}' lat='{$row['lat']}' lon='{$row['lon']}' class='phonersearchresult_s'>
    			<div class='result-wrap-adress'><i class='ri-map-pin-2-line'></i>&nbsp;{$row['city']} {$row['street']} {$row['streetnumber']} {$row['streetnumberadd']}<i class='ri-settings-line'></i>Unit {$row['unit']}</div>
    			<div class='result-wrap-name'><i class='ri-user-line'></i> {$row['lastname']} {$row['firstname']}</div>
    			<div class='result-wrap-status'><i class='ri-flag-line'></i> {$status2}</div>
    			<div></div></div>";


				if ($index === 100) {
					break;
				}
			}
		}
		$result->free_result();
	}
	echo $html;
}
