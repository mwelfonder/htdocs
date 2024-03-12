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
	$start_time = microtime(true);

	$substr_term = substr($term, 0, 3);
	if (is_numeric($substr_term)) {
		$term = str_replace('-', '', $term);
	}

	// Fetch all permissions at the start
	$all_permissions = getAllPermissions();
	$statusClasses = [
		"OPEN" => "blue", "DONE" => "green", "STOPPED" => "red",
		"PLANNED" => "yellow", "DONE CLOUD" => "green", "OVERDUE" => "lila",
		"WRONG" => "red", "PENDING" => "orange", "MISSING" => "red",
	];


	$conn = dbconnect();
	$html_parts = [];

	$stmt = $conn->stmt_init();
	$select_columns = "homeid, city, street, streetnumber, streetnumberadd, unit, lastname, firstname, hbg_status, scan4_status, priority, client, carrier, lat, lon, phone1, phone2, phone3, phone4";

	// Remove all spaces from the term for phone number check
	$sanitized_term = str_replace(' ', '', $term);
	$phone_regex = '/^\+?\d[\d -]{8,14}\d$/';
	if (preg_match($phone_regex, $sanitized_term)) { // check if the sanitized term is a phone number
		$stmt->prepare("SELECT $select_columns FROM `scan4_homes` WHERE REPLACE(REPLACE(REPLACE(phone1, ' ', ''), '-', ''), '+', '') LIKE CONCAT('%', ?, '%') OR REPLACE(REPLACE(REPLACE(phone2, ' ', ''), '-', ''), '+', '') LIKE CONCAT('%', ?, '%') OR REPLACE(REPLACE(REPLACE(phone3, ' ', ''), '-', ''), '+', '') LIKE CONCAT('%', ?, '%') OR REPLACE(REPLACE(REPLACE(phone4, ' ', ''), '-', ''), '+', '') LIKE CONCAT('%', ?, '%') ORDER BY `scan4_homes`.`streetnumber` ASC LIMIT 50");
		$stmt->bind_param('ssss', $sanitized_term, $sanitized_term, $sanitized_term, $sanitized_term);
	} elseif (preg_match('/\s/', $term)) { // check if whitespace is found
		$a_split = explode(" ", $term);
		if (isset($a_split[2]) && is_numeric($a_split[2]) === true) {
			$stmt->prepare("SELECT $select_columns FROM `scan4_homes` WHERE city LIKE CONCAT('%', ?, '%') AND street LIKE CONCAT('%', ?, '%') AND streetnumber LIKE CONCAT('%', ?, '%') ORDER BY `scan4_homes`.`streetnumber` ASC LIMIT 50");
			$stmt->bind_param('sss', $a_split[0], $a_split[1], $a_split[2]);
		} elseif (is_numeric($a_split[1]) === true) {
			$stmt->prepare("SELECT $select_columns FROM `scan4_homes` WHERE street LIKE CONCAT('%', ?, '%') AND streetnumber LIKE CONCAT('%', ?, '%') ORDER BY `scan4_homes`.`streetnumber` ASC LIMIT 50");
			$stmt->bind_param('ss', $a_split[0], $a_split[1]);
		} elseif (is_numeric($a_split[1]) === false) {
			$stmt->prepare("SELECT $select_columns FROM `scan4_homes` WHERE city LIKE CONCAT('%', ?, '%') AND street LIKE CONCAT('%', ?, '%') ORDER BY `scan4_homes`.`streetnumber` ASC LIMIT 50");
			$stmt->bind_param('ss', $a_split[0], $a_split[1]);
		} else {
			$stmt->prepare("SELECT $select_columns FROM `scan4_homes` WHERE `city` LIKE CONCAT('%', ?, '%') OR `street` LIKE CONCAT('%', ?, '%') OR homeid LIKE CONCAT('%', ?, '%') OR lastname LIKE CONCAT('%', ?, '%') OR phone1 LIKE CONCAT('%', ?, '%') OR phone2 LIKE CONCAT('%', ?, '%') ORDER BY `scan4_homes`.`streetnumber` ASC LIMIT 50");
			$stmt->bind_param('ssssss', $term, $term, $term, $term, $term, $term);
		}
	} else {
		$stmt->prepare("SELECT $select_columns FROM `scan4_homes` WHERE `city` LIKE CONCAT('%', ?, '%') OR `street` LIKE CONCAT('%', ?, '%') OR homeid LIKE CONCAT('%', ?, '%') OR lastname LIKE CONCAT('%', ?, '%') OR phone1 LIKE CONCAT('%', ?, '%') OR phone2 LIKE CONCAT('%', ?, '%') ORDER BY `scan4_homes`.`streetnumber` ASC LIMIT 50");
		$stmt->bind_param('ssssss', $term, $term, $term, $term, $term, $term);
	}


	if ($stmt->execute()) {
		$result = $stmt->get_result();
		while ($row = $result->fetch_assoc()) {
			// Check permissions
			$client_allowed = isset($all_permissions['clients'][$row['client']]) && $all_permissions['clients'][$row['client']];
			$carrier_allowed = isset($all_permissions['carriers'][$row['carrier']]) && $all_permissions['carriers'][$row['carrier']];

			if (!$client_allowed || !$carrier_allowed) {
				continue; // Skip this row if permission is missing
			}


			$status1 = '<span class="searchpill ' . ($statusClasses[$row['hbg_status']] ?? '') . '">' . $row['hbg_status'] . '</span>';
			$status2 = '<span class="searchpill ' . ($statusClasses[$row['scan4_status']] ?? '') . '">' . $row['scan4_status'] . '</span>';

			$isprio = $row['priority'] != "" ? '<span class="searchpill prio"><i class="fa-solid fa-star"></i> PRIO</span>' : '';

			$html_parts[] = "<div id='{$row['homeid']}' lat='{$row['lat']}' lon='{$row['lon']}' class='phonersearchresult_s'>
                <div class='result-wrap-adress'><i class='ri-map-pin-2-line'></i>&nbsp;{$row['city']} {$row['street']} {$row['streetnumber']} {$row['streetnumberadd']}<i class='ri-settings-line'></i>Unit {$row['unit']}</div>
                <div class='result-wrap-name'><i class='ri-user-line'></i> {$row['lastname']} {$row['firstname']}</div>
                <div class='result-wrap-status'><i class='ri-flag-line'></i> {$status1} / {$status2}{$isprio}</div>
                <div class='search_mapselector' style='margin-left:10px;'><i class='ri-road-map-line'></i><div></div></div></div>";
		}
		$result->free_result();
	}

	$end_time = microtime(true);
	$execution_time = $end_time - $start_time;
	if (hasPerm(2)) {
		$html_parts[] = "<div style='margin-top: 20px; font-size: 0.8em;'>Execution time: {$execution_time} seconds</div>";
	}

	echo implode('', $html_parts);
}


function get_searchbar_old($term)
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
				$status1 = '';
				$status2 = '';
				$isprio = '';
				if ($row['hbg_status'] === "OPEN") {
					$status1 = '<span class="searchpill blue">OPEN</span>';
				}
				if ($row['hbg_status'] === "DONE") {
					$status1 = '<span class="searchpill green">DONE</span>';
				}
				if ($row['hbg_status'] === "STOPPED") {
					$status1 = '<span class="searchpill red">STOPPED</span>';
				}
				if ($row['hbg_status'] === "PLANNED") {
					$status1 = '<span class="searchpill yellow">PLANNED</span>';
				}


				if ($row['scan4_status'] === "OPEN") {
					$status2 = '<span class="searchpill blue">OPEN</span>';
				}
				if ($row['scan4_status'] === "DONE") {
					$status2 = '<span class="searchpill green">DONE</span>';
				}
				if ($row['scan4_status'] === "DONE CLOUD") {
					$status2 = '<span class="searchpill green">DONE CLOUD</span>';
				}
				if ($row['scan4_status'] === "STOPPED") {
					$status2 = '<span class="searchpill red">STOPPED</span>';
				}
				if ($row['scan4_status'] === "PLANNED") {
					$status2 = '<span class="searchpill yellow">PLANNED</span>';
				}
				if ($row['scan4_status'] === "OVERDUE") {
					$status2 = '<span class="searchpill lila">OVERDUE</span>';
				}
				if ($row['scan4_status'] === "WRONG") {
					$status2 = '<span class="searchpill red">WRONG</span>';
				}
				if ($row['scan4_status'] === "PENDING") {
					$status2 = '<span class="searchpill orange">PENDING</span>';
				}
				if ($row['scan4_status'] === "MISSING") {
					$status2 = '<span class="searchpill red">MISSING</span>';
				}

				if ($row['priority'] != "") {
					$isprio = '<span class="searchpill prio"><i class="fa-solid fa-star"></i> PRIO</span>';
				}
				$html .= "<div id='{$row['homeid']}' lat='{$row['lat']}' lon='{$row['lon']}' class='phonersearchresult_s'>
    			<div class='result-wrap-adress'><i class='ri-map-pin-2-line'></i>&nbsp;{$row['city']} {$row['street']} {$row['streetnumber']} {$row['streetnumberadd']}<i class='ri-settings-line'></i>Unit {$row['unit']}</div>
    			<div class='result-wrap-name'><i class='ri-user-line'></i> {$row['lastname']} {$row['firstname']}</div>
    			<div class='result-wrap-status'><i class='ri-flag-line'></i> {$status1} / {$status2}{$isprio}</div>
    			<div class='search_mapselector' style='margin-left:10px;'><i class='ri-road-map-line'></i><div></div></div></div>";


				if ($index === 100) {
					break;
				}
			}
		}
		$result->free_result();
	}
	echo $html;
}
