<?php



if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}
include_once "../../view/includes/functions.php";

date_default_timezone_set('Europe/Berlin');
if (!isset($_POST['func'])) {
	die();
}

$func = $_POST['func'];
if ($func === "load_overview_city") {
	$city = $_POST['city'];
	load_overview_city($city);
} else if ($func === "load_overview_newcustomers") {
	$kw = $_POST['kw'];
	$city = $_POST['city'];
	load_overview_newcustomers($city, $kw);
} else if ($func === "load_overview_stats") {
	$city = $_POST['city'];
	$stat = $_POST['stat'];
	$array = $_POST['array'];
	load_overview_stats($stat, $city, $array);
	// =============================
	// =============================
} else if ($func === "load_overview_a_callshorts") {
	$kw = $_POST['kw'];
	$user = $_POST['user'];
	$city = $_POST['city'];
	$day = $_POST['day'];
	load_overview_a_callshorts($city, $day, $kw, $user, 'extern');
} else if ($func === "load_overview_a_callscoreboard") {
	load_overview_a_callscoreboard();
} else if ($func === "search_callshortsproject") {
	search_callshortsproject();
} else if ($func === "load_doneCustomersCheck") {
	load_doneCustomersCheck();
} else if ($func === "load_projects") {
	load_projects();
}




function load_projects()
{
	$conn = dbconnect();
	// Prepare the SQL statement
	$stmt = $conn->prepare("SELECT `id`, `city`, `carrier` FROM `scan4_citylist` WHERE 1");
	$stmt->execute();
	$result = $stmt->get_result();

	// Fetch the results
	$data = [];
	while ($row = $result->fetch_assoc()) {
		$data[] = $row;
	}

	// Return data as JSON
	header('Content-Type: application/json');
	echo json_encode($data);
	// close conn
	$conn->close();
}


function load_doneCustomersCheck()
{
	$conn = dbconnect();

	// Grab the data from the POST request
	$cityNames = isset($_POST['cityNames']) ? $_POST['cityNames'] : [];
	$startDate = isset($_POST['startDate']) ? $_POST['startDate'] : '';
	$endDate = isset($_POST['endDate']) ? $_POST['endDate'] : '';

	// Prepare your query
	$placeholders = implode(',', array_fill(0, count($cityNames), '?'));
	$query = "SELECT * FROM `scan4_homes` WHERE `city` IN ($placeholders) AND `hbg_date` BETWEEN ? AND ?";

	$stmt = $conn->prepare($query);

	// You need to dynamically bind the parameters based on the number of cityNames
	$types = str_repeat('s', count($cityNames)) . 'ss'; // 's' for string type
	$params = array_merge($cityNames, [$startDate, $endDate]);
	$stmt->bind_param($types, ...$params);

	$stmt->execute();
	$result = $stmt->get_result();

	// Fetch the results
	$data = [];
	while ($row = $result->fetch_assoc()) {
		$data[] = $row;
	}

	// Return data as JSON
	header('Content-Type: application/json');
	echo json_encode($data);

	// Close the statement and connection
	$stmt->close();
	$conn->close();
}





function load_overview_a_callscoreboard()
{

	$perm_telefonist = fetchPermissionUsers(5); // 5 = Telefonist

	for ($i = 0; $i < count($perm_telefonist); $i++) {
		$data = fetchUserDetails(null, null, $perm_telefonist[$i]->user_id);
		$userlist[] =  array('username' => $data->username, 'pic' => $data->profile_pic);
	}
	sort($userlist);
	for ($i = 0; $i < count($userlist); $i++) {
		$user = $userlist[$i]['username'];
		$stats[] = load_overview_a_callshorts('', '', 'this', $user, 'intern');
	}
	//$stats = load_overview_a_callshorts('this','all', 'intern');
	//echo print_r($stats);
	echo json_encode($stats);
}

function search_callshortsproject()
{
	$conn = dbconnect();
	$term = $_POST['term'];
	$query = "SELECT city FROM `scan4_citylist` WHERE city LIKE '%" . $term . "%' ORDER BY `city` ASC";
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_row()) {
			echo '<div id="' . $row[0] . '" class="callshortsresitem">' . $row[0] . '</div>';
		}
		$result->free_result();
	}
	$conn->close();
}


function load_overview_a_callshorts($city, $day, $kw, $user, $process)
{
	$conn = dbconnect();

	if ($user === 'all') $user = '%';
	if ($city === 'all') $city = '%';
	if ($kw === 'this') {
		$kw = date('W');
		$startdate = date('Y-m-d', strtotime('2023W' . $kw));
		$enddate = date('Y-m-d', strtotime($startdate . ' +6 days'));
		if ($day !== 'all') {
			if ($day === 'di') {
				$startdate = date('Y-m-d', strtotime('2023W' . $kw . '+1day'));
			} else if ($day === 'mi') {
				$startdate = date('Y-m-d', strtotime('2023W' . $kw . '+2day'));
			} else if ($day === 'do') {
				$startdate = date('Y-m-d', strtotime('2023W' . $kw . '+3day'));
			} else if ($day === 'fr') {
				$startdate = date('Y-m-d', strtotime('2023W' . $kw . '+4day'));
			}
			$enddate = $startdate;
		}
	} else if ($kw === 'last') {
		$kw = date("W", strtotime("-1 week"));
		$startdate = date('Y-m-d', strtotime('2023W' . $kw));
		$enddate = date('Y-m-d', strtotime($startdate . ' +6 days'));
		if ($day !== 'all') {
			if ($day === 'di') {
				$startdate = date('Y-m-d', strtotime('2023W' . $kw . '+1day'));
			} else if ($day === 'mi') {
				$startdate = date('Y-m-d', strtotime('2023W' . $kw . '+2day'));
			} else if ($day === 'do') {
				$startdate = date('Y-m-d', strtotime('2023W' . $kw . '+3day'));
			} else if ($day === 'fr') {
				$startdate = date('Y-m-d', strtotime('2023W' . $kw . '+4day'));
			}
			$enddate = $startdate;
		}
	} else if ($kw === 'year') {
		$startdate = date('Y') . '-01-01';
		$enddate = date("Y-m-t", strtotime(date('Y')));
	} else if (strlen($kw) === 2) {
		$startdate = date('Y-m-d', strtotime(date('Y') . $kw . '01'));
		$enddate = date("Y-m-t", strtotime($startdate));
	} else {
		$startdate = date('Y-m-d', strtotime(date('Y') . '0' . $kw . '01'));
		$enddate = date("Y-m-t", strtotime($startdate));
	}


	//SELECT scan4_calls.call_date,scan4_calls.call_user,scan4_calls.homeid,scan4_calls.result,scan4_homes.city FROM `scan4_calls` INNER JOIN scan4_homes ON scan4_calls.homeid=scan4_homes.homeid WHERE scan4_homes.city = 'Brauneberg';

	$stats = array();

	if ($kw === 'all') {
		$startdate = '2020-01-01';
		$enddate = date('Y-m-d');
	}

	//$query = "SELECT COUNT(homeid) FROM `scan4_calls` WHERE call_user LIKE '$user' AND call_date BETWEEN '" . $startdate . "' AND '" . $enddate . "'";
	$query = "SELECT COUNT(scan4_userlog.homeid) FROM `scan4_userlog` INNER JOIN scan4_homes ON scan4_userlog.homeid=scan4_homes.homeid WHERE scan4_homes.city LIKE '%" . $city . "%' AND scan4_userlog.user LIKE '$user' AND scan4_userlog.datetime BETWEEN '" . $startdate . " 00:00:00' AND '" . $enddate . " 23:59:59';";
	//echo $query;
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$stats['0']['callactions'] = $row[0];

	$query = "SELECT COUNT(scan4_calls.homeid) FROM `scan4_calls` INNER JOIN scan4_homes ON scan4_calls.homeid=scan4_homes.homeid WHERE scan4_calls.call_user != 'Anrufliste' AND scan4_calls.call_user != 'admin' AND scan4_homes.city LIKE '%" . $city . "%' AND scan4_calls.call_user LIKE '$user' AND scan4_calls.call_date BETWEEN '" . $startdate . "' AND '" . $enddate . "';";
	//echo $query;
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$stats['0']['calls'] = $row[0];

	$query = "SELECT COUNT(homeid) FROM `scan4_calls` WHERE call_user LIKE '$user' AND result = 'nicht erreicht' AND call_date BETWEEN '" . $startdate . "' AND '" . $enddate . "'";
	$query = "SELECT COUNT(scan4_calls.homeid) FROM `scan4_calls` INNER JOIN scan4_homes ON scan4_calls.homeid=scan4_homes.homeid WHERE scan4_calls.result = 'nicht erreicht' AND scan4_homes.city LIKE '%" . $city . "%' AND scan4_calls.call_user LIKE '$user' AND scan4_calls.call_date BETWEEN '" . $startdate . "' AND '" . $enddate . "';";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$stats['0']['missed'] = $row[0];

	$query = "SELECT COUNT(homeid) FROM `scan4_calls` WHERE call_user LIKE '$user' AND `result` LIKE '%Keine HBG%' AND call_date BETWEEN '" . $startdate . "' AND '" . $enddate . "'";
	$query = "SELECT COUNT(scan4_calls.homeid) FROM `scan4_calls` INNER JOIN scan4_homes ON scan4_calls.homeid=scan4_homes.homeid WHERE scan4_calls.result LIKE '%Keine HBG%' AND scan4_homes.city LIKE '%" . $city . "%' AND scan4_calls.call_user LIKE '$user' AND scan4_calls.call_date BETWEEN '" . $startdate . "' AND '" . $enddate . "';";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$stats['0']['nohbg'] = $row[0];

	$query = "SELECT COUNT(homeid) FROM `scan4_calls` WHERE call_user LIKE '$user' AND result = 'HBG erstellt' AND call_date BETWEEN '" . $startdate . "' AND '" . $enddate . "'";
	$query = "SELECT COUNT(scan4_calls.homeid) FROM `scan4_calls` INNER JOIN scan4_homes ON scan4_calls.homeid=scan4_homes.homeid WHERE scan4_calls.result = 'HBG erstellt' AND scan4_homes.city LIKE '%" . $city . "%' AND scan4_calls.call_user LIKE '$user' AND scan4_calls.call_date BETWEEN '" . $startdate . "' AND '" . $enddate . "';";

	$result = $conn->query($query);
	$row = $result->fetch_row();
	$stats['0']['hbgset'] = $row[0];

	$query = "SELECT COUNT(homeid) FROM `scan4_calls` WHERE call_user LIKE '$user' AND result = 'Wiedervorlage' AND call_date BETWEEN '" . $startdate . "' AND '" . $enddate . "'";
	$query = "SELECT COUNT(scan4_calls.homeid) FROM `scan4_calls` INNER JOIN scan4_homes ON scan4_calls.homeid=scan4_homes.homeid WHERE scan4_calls.result = 'Wiedervorlage' AND scan4_homes.city LIKE '%" . $city . "%' AND scan4_calls.call_user LIKE '$user' AND scan4_calls.call_date BETWEEN '" . $startdate . "' AND '" . $enddate . "';";

	$result = $conn->query($query);
	$row = $result->fetch_row();
	$stats['0']['wiedervorlage'] = $row[0];

	$query = "SELECT COUNT(homeid) FROM `scan4_calls` WHERE call_user LIKE '$user' AND result = 'Falscher Ansprechpartner' AND call_date BETWEEN '" . $startdate . "' AND '" . $enddate . "'";
	$query = "SELECT COUNT(scan4_calls.homeid) FROM `scan4_calls` INNER JOIN scan4_homes ON scan4_calls.homeid=scan4_homes.homeid WHERE scan4_calls.result = 'Falscher Ansprechpartner' AND scan4_homes.city LIKE '%" . $city . "%' AND scan4_calls.call_user LIKE '$user' AND scan4_calls.call_date BETWEEN '" . $startdate . "' AND '" . $enddate . "';";

	$result = $conn->query($query);
	$row = $result->fetch_row();
	$stats['0']['wrongperson'] = $row[0];

	$query = "SELECT COUNT(homeid) FROM `scan4_calls` WHERE call_user LIKE '$user' AND result = 'Keine HBG - Kunde sagt, er habe gekündigt' AND call_date BETWEEN '" . $startdate . "' AND '" . $enddate . "'";
	$query = "SELECT COUNT(scan4_calls.homeid) FROM `scan4_calls` INNER JOIN scan4_homes ON scan4_calls.homeid=scan4_homes.homeid WHERE scan4_calls.result = 'Keine HBG - Kunde sagt, er habe gekündigt' AND scan4_homes.city LIKE '%" . $city . "%' AND scan4_calls.call_user LIKE '$user' AND scan4_calls.call_date BETWEEN '" . $startdate . "' AND '" . $enddate . "';";

	$result = $conn->query($query);
	$row = $result->fetch_row();
	$stats['0']['canceldcontract'] = $row[0];

	$query = "SELECT COUNT(homeid) FROM `scan4_calls` WHERE call_user LIKE '$user' AND result = 'Keine HBG - Falsche Adresse' AND call_date BETWEEN '" . $startdate . "' AND '" . $enddate . "'";
	$query = "SELECT COUNT(scan4_calls.homeid) FROM `scan4_calls` INNER JOIN scan4_homes ON scan4_calls.homeid=scan4_homes.homeid WHERE scan4_calls.result = 'Keine HBG - Falsche Adresse' AND scan4_homes.city LIKE '%" . $city . "%' AND scan4_calls.call_user LIKE '$user' AND scan4_calls.call_date BETWEEN '" . $startdate . "' AND '" . $enddate . "';";

	$result = $conn->query($query);
	$row = $result->fetch_row();
	$stats['0']['wrongadress'] = $row[0];

	$query = "SELECT COUNT(homeid) FROM `scan4_calls` WHERE call_user LIKE '$user' AND result = 'Keine HBG - Nummer nicht vergeben' AND call_date BETWEEN '" . $startdate . "' AND '" . $enddate . "'";
	$query = "SELECT COUNT(scan4_calls.homeid) FROM `scan4_calls` INNER JOIN scan4_homes ON scan4_calls.homeid=scan4_homes.homeid WHERE scan4_calls.result = 'Keine HBG - Nummer nicht vergeben' AND scan4_homes.city LIKE '%" . $city . "%' AND scan4_calls.call_user LIKE '$user' AND scan4_calls.call_date BETWEEN '" . $startdate . "' AND '" . $enddate . "';";

	$result = $conn->query($query);
	$row = $result->fetch_row();
	$stats['0']['numbernotset'] = $row[0];

	$query = "SELECT COUNT(homeid) FROM `scan4_calls` WHERE call_user LIKE '$user' AND result = 'Keine HBG - Falsche Nummer' AND call_date BETWEEN '" . $startdate . "' AND '" . $enddate . "'";
	$query = "SELECT COUNT(scan4_calls.homeid) FROM `scan4_calls` INNER JOIN scan4_homes ON scan4_calls.homeid=scan4_homes.homeid WHERE scan4_calls.result = 'Keine HBG - Falsche Nummer' AND scan4_homes.city LIKE '%" . $city . "%' AND scan4_calls.call_user LIKE '$user' AND scan4_calls.call_date BETWEEN '" . $startdate . "' AND '" . $enddate . "';";

	$result = $conn->query($query);
	$row = $result->fetch_row();
	$stats['0']['wrongnumber'] = $row[0];

	$query = "SELECT COUNT(homeid) FROM `scan4_calls` WHERE call_user LIKE '$user' AND result = 'Keine HBG - Besonderer Grund' AND call_date BETWEEN '" . $startdate . "' AND '" . $enddate . "'";
	$query = "SELECT COUNT(scan4_calls.homeid) FROM `scan4_calls` INNER JOIN scan4_homes ON scan4_calls.homeid=scan4_homes.homeid WHERE scan4_calls.result = 'Keine HBG - Besonderer Grund' AND scan4_homes.city LIKE '%" . $city . "%' AND scan4_calls.call_user LIKE '$user' AND scan4_calls.call_date BETWEEN '" . $startdate . "' AND '" . $enddate . "';";

	$result = $conn->query($query);
	$row = $result->fetch_row();
	$stats['0']['customreason'] = $row[0];

	$query = "SELECT COUNT(scan4_calls.homeid) FROM `scan4_calls` INNER JOIN scan4_homes ON scan4_calls.homeid=scan4_homes.homeid WHERE scan4_calls.result = 'Keine HBG - Kunde verweigert HBG' AND scan4_homes.city LIKE '%" . $city . "%' AND scan4_calls.call_user LIKE '$user' AND scan4_calls.call_date BETWEEN '" . $startdate . "' AND '" . $enddate . "';";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$stats['0']['refused'] = $row[0];



	$conn->close();
	if ($process === 'intern') {
		return $stats;
	} else {
		echo json_encode($stats);
	}
}



function load_overview_stats($stat, $cityies, $array)
{
	// check if passed value is single or array. if not generate a single array so the implode works
	if (!is_array($cityies)) {
		$city = array($cityies);
	} else {
		$city = $cityies;
	}
	$conn = dbconnect();
	// pass the city to an array in case its not a variable
	if ($stat === 'total') {
		$query = "SELECT * FROM `scan4_homes` WHERE city IN ('" . implode("', '", $city) . "') AND (contractstatus = 'UVS' OR contractstatus IS NULL) AND firstname != ''";
	}
	if ($stat === 'sysopen') {
		$query = "SELECT * FROM `scan4_homes` WHERE city IN ('" . implode("', '", $city) . "') AND (hbg_status = 'OPEN' OR hbg_status = 'PLANNED') AND (contractstatus = 'UVS' OR contractstatus IS NULL) AND firstname != ''";
	}
	if ($stat === 'sysstopped') {
		$query = "SELECT * FROM `scan4_homes` WHERE city IN ('" . implode("', '", $city) . "') AND hbg_status = 'STOPPED' AND (contractstatus = 'UVS' OR contractstatus IS NULL) AND firstname != ''";
	}
	if ($stat === 'open') {
		$query = "SELECT * FROM `scan4_homes` WHERE city IN ('" . implode("', '", $city) . "') AND scan4_status = 'OPEN' AND (contractstatus = 'UVS' OR contractstatus IS NULL) AND firstname != ''";
	}
	if ($stat === 'closed') {
		$query = "SELECT * FROM `scan4_homes` WHERE city IN ('" . implode("', '", $city) . "') AND scan4_status = 'CLOSED' AND (contractstatus = 'UVS' OR contractstatus IS NULL) AND firstname != ''";
	}
	if ($stat === 'overdue') {
		$query = "SELECT * FROM `scan4_homes` WHERE city IN ('" . implode("', '", $city) . "') AND scan4_status = 'OVERDUE' AND (contractstatus = 'UVS' OR contractstatus IS NULL) AND firstname != ''";
	}
	if ($stat === 'planned') {
		$query = "SELECT * FROM `scan4_homes` WHERE city IN ('" . implode("', '", $city) . "') AND scan4_status = 'PLANNED' AND (contractstatus = 'UVS' OR contractstatus IS NULL) AND firstname != ''";
	}
	if ($stat === 'sysdone') {
		$query = "SELECT * FROM `scan4_homes` WHERE city IN ('" . implode("', '", $city) . "') AND hbg_status = 'DONE' AND (contractstatus = 'UVS' OR contractstatus IS NULL) AND firstname != ''";
	}
	if ($stat === 'clouddone') {
		$query = "SELECT * FROM `scan4_homes` WHERE city IN ('" . implode("', '", $city) . "') AND scan4_status = 'DONE CLOUD' AND (contractstatus = 'UVS' OR contractstatus IS NULL) AND firstname != ''";
	}
	if ($stat === 'done') {
		$query = "SELECT * FROM `scan4_homes` WHERE city IN ('" . implode("', '", $city) . "') AND scan4_status = 'DONE' AND (contractstatus = 'UVS' OR contractstatus IS NULL) AND firstname != ''";
	}
	if ($stat === 'stopped') {
		$query = "SELECT * FROM `scan4_homes` WHERE city IN ('" . implode("', '", $city) . "') AND scan4_status = 'STOPPED' AND (contractstatus = 'UVS' OR contractstatus IS NULL) AND firstname != ''";
	}
	if ($stat === '5calls') {
		$query = "SELECT * FROM `scan4_homes` WHERE city IN ('" . implode("', '", $city) . "') AND (anruf5 != '' OR anruf5 IS NOT NULL) AND (contractstatus = 'UVS' OR contractstatus IS NULL) AND firstname != ''";
	}
	if ($stat === 'box') {
		$query = "SELECT * FROM `scan4_homes` WHERE city IN ('" . implode("', '", $city) . "') AND (briefkasten != '' OR briefkasten IS NOT NULL) AND (contractstatus = 'UVS' OR contractstatus IS NULL) AND firstname != ''";
	}
	if ($stat === 'mail') {
		$query = "SELECT * FROM `scan4_homes` WHERE city IN ('" . implode("', '", $city) . "') AND (emailsend != '' OR emailsend IS NOT NULL) AND (contractstatus = 'UVS' OR contractstatus IS NULL) AND firstname != ''";
	}
	if ($stat === 'failed') {
		$query = "SELECT * FROM `scan4_homes` WHERE city IN ('" . implode("', '", $city) . "') AND (anruf5 != '' OR anruf5 IS NOT NULL) AND (emailsend != '' OR emailsend IS NOT NULL) AND (briefkasten != '' OR briefkasten IS NOT NULL)";
	}
	if ($stat === 'nophone') {
		$query = "SELECT * FROM `scan4_homes` WHERE city IN ('" . implode("', '", $city) . "') AND (phone1 = '' OR phone1 IS NULL) AND (phone2 = '' OR phone2 IS NULL) AND (contractstatus = 'UVS' OR contractstatus IS NULL) AND firstname != ''";
	}
	if ($stat === 'pending') {
		$query = "SELECT * FROM `scan4_homes` WHERE city IN ('" . implode("', '", $city) . "') AND scan4_status = 'PENDING' AND (contractstatus = 'UVS' OR contractstatus IS NULL) AND firstname != ''";
	}
	if ($stat === 'overdue') {
		$query = "SELECT * FROM `scan4_homes` WHERE city IN ('" . implode("', '", $city) . "') AND scan4_status = 'OVERDUE' AND (contractstatus = 'UVS' OR contractstatus IS NULL) AND firstname != ''";
	}
	//echo $query;


?>
	<table id="loadtable">
		<thead>
			<th>client</th>
			<th>carrier</th>
			<th>street</th>
			<th>#</th>
			<th>#</th>
			<th>unit</th>
			<th>city</th>
			<th>plz</th>
			<th>dpnumber</th>
			<th>homeid</th>
			<th>adressid</th>
			<th>firstname</th>
			<th>lastname</th>
			<th>phone1</th>
			<th>phone2</th>
			<th>email</th>
			<th>isporder</th>
			<th>ncmwdcode</th>
			<th>hbg_status</th>
			<th>hbg_plandate</th>
			<th>hbg_date</th>
			<th>priority</th>
			<th>scan4_comment</th>
			<th>scan4_status</th>
			<th>scan4_hbgdate</th>
			<th>number_anruf</th>
			<th>anruf1</th>
			<th>anruf2</th>
			<th>anruf3</th>
			<th>anruf4</th>
			<th>anruf5</th>
			<th>briefkasten</th>
			<th>emailsend</th>
			<th>scan4_added</th>
		</thead>
		<tbody>
			<?php

			if ($result = $conn->query($query)) {
				while ($row = $result->fetch_assoc()) {
			?>
					<tr>
						<td><?php echo $row['client'] ?></td>
						<td><?php echo $row['carrier'] ?></td>
						<td><?php echo $row['street'] ?></td>
						<td><?php echo $row['streetnumber'] ?></td>
						<td><?php echo $row['streetnumberadd'] ?></td>
						<td><?php echo $row['unit'] ?></td>
						<td><?php echo $row['city'] ?></td>
						<td><?php echo $row['plz'] ?></td>
						<td><?php echo $row['dpnumber'] ?></td>
						<td><?php echo $row['homeid'] ?></td>
						<td><?php echo $row['adressid'] ?></td>
						<td><?php echo $row['firstname'] ?></td>
						<td><?php echo $row['lastname'] ?></td>
						<td><?php echo $row['phone1'] ?></td>
						<td><?php echo $row['phone2'] ?></td>
						<td><?php echo $row['email'] ?></td>
						<td><?php echo $row['isporder'] ?></td>
						<td><?php echo $row['ncmwdcode'] ?></td>
						<td><?php echo $row['hbg_status'] ?></td>
						<td><?php echo $row['hbg_plandate'] ?></td>
						<td><?php echo $row['hbg_date'] ?></td>
						<td><?php echo $row['priority'] ?></td>
						<td><?php echo $row['scan4_comment'] ?></td>
						<td><?php echo $row['scan4_status'] ?></td>
						<td><?php echo $row['scan4_hbgdate'] ?></td>
						<td><?php echo $row['number_anruf'] ?></td>
						<td><?php echo $row['anruf1'] ?></td>
						<td><?php echo $row['anruf2'] ?></td>
						<td><?php echo $row['anruf3'] ?></td>
						<td><?php echo $row['anruf4'] ?></td>
						<td><?php echo $row['anruf5'] ?></td>
						<td><?php echo $row['briefkasten'] ?></td>
						<td><?php echo $row['emailsend'] ?></td>
						<td><?php echo $row['scan4_added'] ?></td>
					</tr>
			<?php
				}
				$result->free_result();
			}
			?>
		</tbody>
	</table>
<?php
	$conn->close();
}

function load_overview_newcustomers($city, $kw)
{
	$conn = dbconnect();
	$startdate = date('Y-m-d', strtotime('2022W' . $kw));
	$enddate = date('Y-m-d', strtotime($startdate . ' +6 days'));
	$newcustomers = 0;
	$i = 1;
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE `city` LIKE '" . $city . "' AND scan4_added BETWEEN '" . $startdate . "' AND '" . $enddate . "'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$newcustomers = $row[0];
	$conn->close();

	echo $newcustomers;
}

function load_overview_city($city)
{
	$conn = dbconnect();
	// ====== get project start date
	$query = "SELECT `date` FROM `scan4_citylist` WHERE `city` LIKE '" . $city . "'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$projectstart = $row[0];
	// ====== get project closed date
	$query = "SELECT `closed` FROM `scan4_citylist` WHERE `city` LIKE '" . $city . "'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$projectclosed = $row[0];
	////////////////////////
	// ====== count 0 calls
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE `city` LIKE '" . $city . "' AND (`anruf1` = '' or `anruf1` IS NULL) AND (`anruf2` = '' or `anruf2` IS NULL) AND (`anruf3` = '' or `anruf3` IS NULL) AND (`anruf4` = '' or `anruf4` IS NULL) AND (`anruf5` = '' or `anruf5` IS NULL) ";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$call = array($row[0]);
	// ====== count 1 calls
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE `city` LIKE '" . $city . "' AND (`anruf1` != '' or `anruf1` IS NOT NULL) AND (`anruf2` = '' or `anruf2` IS NULL) AND (`anruf3` = '' or `anruf3` IS NULL) AND (`anruf4` = '' or `anruf4` IS NULL) AND (`anruf5` = '' or `anruf5` IS NULL) ";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$call[] = $row[0];
	// ====== count 2 calls
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE `city` LIKE '" . $city . "' AND (`anruf1` != '' or `anruf1` IS NOT NULL) AND (`anruf2` != '' or `anruf2` IS NOT NULL) AND (`anruf3` = '' or `anruf3` IS NULL) AND (`anruf4` = '' or `anruf4` IS NULL) AND (`anruf5` = '' or `anruf5` IS NULL) ";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$call[] = $row[0];
	// ====== count 3 calls
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE `city` LIKE '" . $city . "' AND (`anruf1` != '' or `anruf1` IS NOT NULL) AND (`anruf2` != '' or `anruf2` IS NOT NULL) AND (`anruf3` != '' or `anruf3` IS NOT NULL) AND (`anruf4` = '' or `anruf4` IS NULL) AND (`anruf5` = '' or `anruf5` IS NULL) ";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$call[] = $row[0];
	// ====== count 4 calls
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE `city` LIKE '" . $city . "' AND (`anruf1` != '' or `anruf1` IS NOT NULL) AND (`anruf2` != '' or `anruf2` IS NOT NULL) AND (`anruf3` != '' or `anruf3` IS NOT NULL) AND (`anruf4` != '' or `anruf4` IS NOT NULL) AND (`anruf5` = '' or `anruf5` IS NULL) ";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$call[] = $row[0];
	// ====== count 5 calls
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE `city` LIKE '" . $city . "' AND (`anruf1` != '' or `anruf1` IS NOT NULL) AND (`anruf2` != '' or `anruf2` IS NOT NULL) AND (`anruf3` != '' or `anruf3` IS NOT NULL) AND (`anruf4` != '' or `anruf4` IS NOT NULL) AND (`anruf5` != '' or `anruf5` IS NOT NULL) ";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$call[] = $row[0];
	// ====== count current kw calls
	// __________________________________
	// |								|
	// |	for loop not working... hu	|
	// |	uuuuuuuuge loading time		|
	// |________________________________|
	//
	$monday = date('Y-m-d', strtotime('monday this week'));
	$sunday = date('Y-m-d', strtotime('sunday this week'));
	$kwcallthis = 0;
	$i = 1;
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE `city` LIKE '" . $city . "' AND (`anruf" . $i . "` != '' or `anruf" . $i . "` IS NOT NULL) AND anruf" . $i . " BETWEEN '" . $monday . "' AND '" . $sunday . "'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$kwcallthis += $row[0];
	$i++;
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE `city` LIKE '" . $city . "' AND (`anruf" . $i . "` != '' or `anruf" . $i . "` IS NOT NULL) AND anruf" . $i . " BETWEEN '" . $monday . "' AND '" . $sunday . "'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$kwcallthis += $row[0];
	$i++;
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE `city` LIKE '" . $city . "' AND (`anruf" . $i . "` != '' or `anruf" . $i . "` IS NOT NULL) AND anruf" . $i . " BETWEEN '" . $monday . "' AND '" . $sunday . "'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$kwcallthis += $row[0];
	$i++;
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE `city` LIKE '" . $city . "' AND (`anruf" . $i . "` != '' or `anruf" . $i . "` IS NOT NULL) AND anruf" . $i . " BETWEEN '" . $monday . "' AND '" . $sunday . "'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$kwcallthis += $row[0];
	$i++;
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE `city` LIKE '" . $city . "' AND (`anruf" . $i . "` != '' or `anruf" . $i . "` IS NOT NULL) AND anruf" . $i . " BETWEEN '" . $monday . "' AND '" . $sunday . "'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$kwcallthis += $row[0];
	/////////////////////////////////////////
	// 		Last week
	////////////////////////////////////////
	$monday = date('Y-m-d', strtotime('monday last week'));
	$sunday = date('Y-m-d', strtotime('sunday last week'));
	$kwcalllast = 0;
	$i = 1;
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE `city` LIKE '" . $city . "' AND (`anruf" . $i . "` != '' or `anruf" . $i . "` IS NOT NULL) AND anruf" . $i . " BETWEEN '" . $monday . "' AND '" . $sunday . "'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$kwcalllast += $row[0];
	$i++;
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE `city` LIKE '" . $city . "' AND (`anruf" . $i . "` != '' or `anruf" . $i . "` IS NOT NULL) AND anruf" . $i . " BETWEEN '" . $monday . "' AND '" . $sunday . "'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$kwcalllast += $row[0];
	$i++;
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE `city` LIKE '" . $city . "' AND (`anruf" . $i . "` != '' or `anruf" . $i . "` IS NOT NULL) AND anruf" . $i . " BETWEEN '" . $monday . "' AND '" . $sunday . "'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$kwcalllast += $row[0];
	$i++;
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE `city` LIKE '" . $city . "' AND (`anruf" . $i . "` != '' or `anruf" . $i . "` IS NOT NULL) AND anruf" . $i . " BETWEEN '" . $monday . "' AND '" . $sunday . "'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$kwcalllast += $row[0];
	$i++;
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE `city` LIKE '" . $city . "' AND (`anruf" . $i . "` != '' or `anruf" . $i . "` IS NOT NULL) AND anruf" . $i . " BETWEEN '" . $monday . "' AND '" . $sunday . "'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$kwcalllast += $row[0];

	$conn->close();

	$callcount = $call[1] + $call[2] + $call[3] + $call[4] + $call[5];

	//echo print_r($call);

	ob_start();
?>

	<tr class="appendrow">
		<td style="padding:0px;" colspan="15">
			<div class="overview_table_details">
				<div class="row">
					<div class="overviet-details col leftcol">
						<div class="statsoverviewtitel">Started <b><?php echo $projectstart ?></b></div>
						<div class="statsoverviewtitel">Closed <b><?php if ($projectclosed != '') {
																		echo $projectclosed;
																	} else {
																		echo 'still open';
																	} ?></b></div>
						<div class="statsoverviewcontent">
							<table>
								<tbody>
									<tr>
										<td class="smalltitelstats" collspan="2">Call Stats</td>
									</tr>
									<tr>
										<td>total calls</td>
										<td><?php echo $callcount ?></td>
									</tr>
									<tr>
										<td>kw<?php echo date('W') ?></td>
										<td><?php echo $kwcallthis ?></td>
									</tr>
									<tr>
										<td>kw<?php echo date('W', strtotime('last week')) ?></td>
										<td><?php echo $kwcalllast ?></td>
									</tr>
									<tr>
										<td class="smalltitelstats" collspan="2">New Customers</td>
									</tr>
									<tr>
										<td>kw<?php echo date('W') ?></td>
										<td><?php echo load_overview_newcustomers($city, date('W')) ?></td>
									</tr>
									<tr>
										<td>kw<?php echo date('W', strtotime('last week')) ?></td>
										<td><?php echo load_overview_newcustomers($city, date('W', strtotime('last week'))) ?></td>
									</tr>
									<tr>
										<td><input maxlength="2" pattern="\d{2}" type="text" class="smallpicker" id="selectnewcustomers" placeholder="Select KW" /></td>
										<td id="shownewcustomers"></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
					<div class="overviet-details col midcol">
						<div id="donut_totals"></div>
					</div>
					<div class="overviet-details col rightcol">
						<div id="donut_calls"></div>
						<div class="hidden">
							<span id="thiscalls0"><?php echo $call[0] ?></span>
							<span id="thiscalls1"><?php echo $call[1] ?></span>
							<span id="thiscalls2"><?php echo $call[2] ?></span>
							<span id="thiscalls3"><?php echo $call[3] ?></span>
							<span id="thiscalls4"><?php echo $call[4] ?></span>
							<span id="thiscalls5"><?php echo $call[5] ?></span>
						</div>
					</div>
				</div>
			</div>
		</td>
	</tr>


<?php
}
