<?php




// alle bekommen den eintrag

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



include "../../view/includes/functions.php";

date_default_timezone_set('Europe/Berlin');
if (!isset($_POST['func']) && !isset($_GET['func'])) {
	echo 'Access denied';
	die();
}


//error_reporting(E_ALL);
//ini_set('display_errors', 1);



$func = $_POST['func'];
if ($func === "load_citylist") {
	$client = $_POST['client'];
	uploader_citylist($client);
} else if ($func === "set_switchcarrier") {
	$city = $_POST['city'];
	$carrier = $_POST['carrier'];
	set_switchcarrier($city, $carrier);
} else if ($func === "set_swtichstatus") {
	$city = $_POST['city'];
	$status = $_POST['status'];
	set_switchstatus($city, $status);
} else if ($func === "deletecity") {
	$city = $_POST['city'];
	deletecity($city);
} else if ($func === "deletecity") {
	$city = $_POST['city'];
	deletecity($city);
} else if ($func === "uploadFile") {
	if (isset($_FILES['file'])) {
		$fileToOutput = '/var/www/html/logfiles/output.txt';

		// Open the file in write mode, which will truncate it to zero length or create a new one if it doesn't exist
		$handle = fopen($fileToOutput, 'w');

		if ($handle) {
			// The file is now empty or a new file has been created, you can close the file handle
			fclose($handle);
		} else {
			// Handle error opening the file
			//echo "Error opening the file";
		}
		uploadFile($_FILES['file']);
	} else {
		echo 'No files uploaded';
		//echo json_encode(['status' => 'error', 'message' => 'No files uploaded!']);
	}
} else if ($func === "load_dps") {
	load_dps();
}


function load_dps()
{
	// Database connection
	$conn = dbconnect();
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	// Prepare the select query
	$stmt = $conn->prepare("SELECT dpnumber, homeid FROM scan4_homes");
	if (!$stmt) {
		die("Statement preparation failed: " . $conn->error);
	}

	// Execute the query
	$stmt->execute();

	// Bind result variables
	$stmt->bind_result($dpnumber, $homeid);

	// Fetch all rows into an array
	$results = [];
	while ($stmt->fetch()) {
		$results[] = ['dpnumber' => $dpnumber, 'homeid' => $homeid];
	}

	// Close the statement and the connection
	$stmt->close();
	$conn->close();

	// Convert data to CSV
	$csvOutput = "dpnumber,homeid\n"; // headers
	foreach ($results as $row) {
		$csvOutput .= "\"{$row['dpnumber']}\",\"{$row['homeid']}\"\n"; // encapsulating data in quotes to handle special characters
	}

	// Save CSV to a file
	$filename = "dptemp.csv"; // Consider making this unique if needed
	$directory = "/var/www/html/uploads/";
	$filePath = $directory . $filename;

	if (false === file_put_contents($filePath, $csvOutput)) {
		header("HTTP/1.0 500 Internal Server Error");
		echo "Failed to create file.";
		exit;
	}

	// Return the link (filename) to the client
	echo $filename;
}





function uploadFile($files)
{
	$upload_dir = "/var/www/html/uploads/importer_csv/" . date("Y") . "/" . date("Y_m") . "/";

	if (!is_dir($upload_dir)) {
		if (!mkdir($upload_dir, 0777, true)) {
			echo 'Failed to create directories: ' . htmlspecialchars($upload_dir);
			return;
		}
	}

	$timestamp = date("Y_m_d-H:i");
	foreach ($files['tmp_name'] as $key => $tmp_name) {
		$filename = $files['name'][$key];
		$file_tmp = $files['tmp_name'][$key];

		$file_path = $upload_dir . $timestamp . "_" . $filename;

		$response = [
			'status' => 'error',
			'messages' => []
		];

		if (move_uploaded_file($file_tmp, $file_path)) {
			upload_parsecsv($file_path);
		} else {
			// Detailed error logging
			$error_message = 'Failed to upload file ' . htmlspecialchars($filename);
			$error_message .= ' | Error Details: ';

			// Check for possible upload errors
			if (!isset($files['error'][$key]) || $files['error'][$key] != UPLOAD_ERR_OK) {
				$error_message .= 'Upload error code: ' . $files['error'][$key];
			} else {
				$error_message .= 'Unknown error occurred.';
			}

			echo $error_message;
			$response['messages'][] = $error_message;
		}
		$responses[] = $response;
	}
	// Optionally, you can return the responses if needed
	// return $responses;
}
 
   
function upload_parsecsv($file)
{
   
	$line = fgets(fopen($file, 'rb'));

	$response = [
		'status' => 'success',
		'messages' => []
	];
	$parseResponse = null;
	if (strpos($line, 'Globale ID;Gebäudetyp;Anzahl WE;Netzverteiler;Adresse;Baugebiet;Eigentümer;Kontakt;Begehungstermin;Begeher;Beauftragt')) { // => GVG found
		//echo 'found GVG';
		$parseResponse = upload_readwrite_gvg($file);
		$response['messages'] = array_merge($response['messages'], $parseResponse['messages']);
	} else if (strpos($line, 'Straße;Hausnummer;Hausnummer Zusatz;Unit;Ort;Postleitzahl;DP;WE_ID;Platzhalter;Kontaktperson;Vorname;Nachname;Telefonnummer 1;Telefonnummer 2;E-Mail;ORDER_STATUS;Erklärungsstatus;Status;Datum Hausbegehung;PLATZHALTER.1;RNCode;Projektcode Intern;HV Standort HUP')) { // => DGF found
		//echo 'found DGF';
		upload_DGFNeu($file);
	} else if (strpos($line, 'Dgpha;Hausnummer;Hausnummer Zusatz;Unit;Ort;Postleitzahl;DP;WE_ID;Platzhalter;Vorname;Nachname;Telefonnummer 1;E-Mail;ORDER_STATUS;Telefonnummer 2;Erklärungsstatus;Status;Datum Hausbegehung')) { // => DGF found
		//echo 'found DGF_pha';
		upload_readwrite_dgf_pha($file);
	} else if (strpos($line, 'Gemeinde Plan;Street;Number;Number Affix;Gebaudeteil;Municipality;Postcode;DP ID;Home ID;Address ID;Contact person;Contact phone;Contact email;ISP Order;Connection Contract;Building Connection Contract;Workorder Code;Workorder ic order code;HBG Status;')) { // => NRI found
		//echo 'found NRI';
		upload_readwrite_nri($file);
	} else if (strpos($line, 'Anruf 1;Anruf 2;Anruf 3;Anruf 4;Anruf 5')) { // => Anrufliste
		//echo '</br>';
		//echo 'found Anrufliste';
		//echo '</br>';
		$csvFile = fopen($file, 'r');
		//$getData = fgetcsv($csvFile, 1, ",");
		$getData = explode(';', $line);
		//echo '</br>data:</br>' . print_r($getData);
		if ((strpos($getData[16], 'Status') !== false) && (strpos($getData[19], 'Date') !== false) && (strpos($getData[20], 'comment') !== false)) {
			upload_readwrite_sc4($file);
		} else if (strpos($line, 'usnummer;Hausnummerzusatz;Anzahl WE;Ort;Home Id;vorname;nachname;Phone;phone 2;Begehungstermin;Begeher;Begehungsprotokoll vorhanden;Priority;HBG_Date;Scan4 comments;Anruf 1;Anruf 2;Anruf 3;Anruf 4;Anruf 5;Anruf 6;Briefcasten;email gesendet;Scan4_Date;RNC code;RNC comments;Number_Anruf;Wer ;Abbruch Grund;Abbruch 1;Abbruch 2;Abbruch 3;HBG_Status_TL;T')) { // => Anrufliste
			upload_readwrite_sc4_gvg($file);
		} else {
			//echo '<p><b>colums dont match</b><p>';
		}
		fclose($csvFile);
	} else if (strpos($line, 'Staaaraße;Hausnummer;Hausnummerzusatz;Gebäudeteil;Plz;Ort;Home Id;Vorname;Nachname;Phone;Phone2;HBGStatus;Email;ISP Order;HBGDate;Scan4 comments;Anruf 1;Anruf 2;Anruf 3;Anruf 4')) { // => Anrufliste
		//echo 'found Anrufliste GVG';
		//echo '</br>';
		// upload_readwrite_sc4_gvg($file);
	} else if (strpos($line, 'ource ID;Gebäudetyp;Anzahl WE;Netzverteiler;Adresse;Baugebiet;Eigentümer;Kontakt;Begehungstermin;Begeher;Skizze vorhanden;Begehungsprotokoll vorhanden;new status;new date;new comment')) { // => Anrufliste
		//echo 'found HBG Check';
		//echo '</br>';
		upload_readwrite_hbgcheck($file);
	} else if (strpos($line, 'prioupload;prio;comment;status new')) { // => Anrufliste
		echo 'found Prio Check';
		echo '</br>';
		upload_priocheck($file);
	} else if (strpos($line, 'homeid;status new')) { // => Anrufliste
		echo 'found Statuschange';
		echo '</br>';
		upload_statuschange($file);
	} else if (strpos($line, 'Home Id;Other contact data;SCAN4 remarks;INSYTE remarks;Responsible team')) { // => Anrufliste
		//echo 'found new Details';
		//echo '</br>';
		upload_newcontactdetails($file);
	} else if (strpos($line, 'zweibrücken;dpupdate')) { // => Anrufliste
		//echo 'found Zweibrücken DPs';
		//echo '</br>';
		upload_zweibrückencustom($file);
	} else if (strpos($line, 'reopen;homeid')) { // => Anrufliste
		//echo 'found reopenlist';
		//echo '</br>';
		upload_reopenscan4($file);
	} else if (strpos($line, 'Home_id;Addresss id;DP Id;Latitude;Longitude;Street_Name')) { // => Anrufliste
		//echo 'found reopenlist';
		//echo '</br>';
		upload_latlong($file);
	} else if (strpos($line, 'Ort;WE_ID;Telefonnummer 1;E-Mail;Telefonnummer 2;Erklärungsstatus;Status;Datum Hausbegehung;PLATZHALTER.1;RNCode;Scan4 Comment;Status new;HBG new')) { // => neuer commentar und neuer status
		//echo 'neuer commentar und neuer status';
		//echo '</br>';
		upload_newstatus($file);
	} else if (strpos($line, 'NEWDPLIST;Homeid')) { // => Reroll the DPS
		//echo 'found newdplist';
		//echo '</br>';
		upload_dpbackroll($file);
		//upload_newdplist($file);
	} else if (strpos($line, 'homeid;timelinecomment')) { // => Anrufliste
		//echo 'found Custom List';
		//echo '</br>';
		upload_customlist($file);
	} else if (strpos($line, 'reroll;scan4_status')) { // => Anrufliste
		//echo 'found reroll List';
		//echo '</br>';
		upload_reroll($file);
	} else if (strpos($line, 'MDU;short_description;u_gp_gemeinde;location;u_ncm_number_of_dwellings;u_ncm_status;u_ncm_survey_status;u_ncm_contact_name_owner;u_ncm_contact_surname_owner;u_ncm_contact_phone1_owner;u_ncm_contact_phone2_owner;u_ncm_contact_email_owner;u_ncm_dpcode_ctc')) { // => Anrufliste
		//echo 'found MDU List';
		//	echo '</br>';
		upload_mdu($file);
	} else if (strpos($line, 'W2;Street;Number;Number Affix;DP ID;Home ID;Contact person;Contact phone;Contact email;ISP Order;Workorder Code;HBG Status;')) { // => Anrufliste
		//echo 'found WaveList';
		//echo '</br>';
		upload_readwrite_nri_wave($file);
	} else if (strpos($line, 'MDU;Project name;Gemeinde;Location(location);No. Dwellings;Project Status;Survey status;Owner name;Owner surname;Owner phone 1')) { // => Anrufliste
		echo 'found MDU List';
		echo '</br>';
		upload_readwrite_MUDn($file);
	} else if (strpos($line, 'Bauauftrag-ID;Nächster Schritt;Status;Terminstatus Auskundung;Terminstatus Installation;Auftrag Typ;Fälligkeit Installation;Installation Begin;PLZ;Ort;Straße;Hausnummer;Hausnr. Z.;KLS-ID;Projekt-Id;NVT Gebiet;ASB;ONKz;Projekt Phase;Erstellungsdatum;Kundentyp;Kunden Name;Telefon;Festnetz;Email;Eigentümer;')) { // => Anrufliste
		//echo 'found GlasfaserPlus List';
		//echo '</br>';
		//upload_readwrite_glasfaserplus($file); // ALTES GF+ FORMAT
	} else if (strpos($line, 'DGF_Fördertgebiet')) { // => Anrufliste
		//echo 'found DGF_Fördertgebiet';
		//echo '</br>';
		upload_DGF_Fördertgebiet($file);
	} else if (strpos($line, 'DGFNeu')) { // => Anrufliste
		//echo 'found DGFNeu';
		//echo '</br>';

	} else if (
		strpos($line, 'KLS ID;Next Activity;Build Up Agreement;Expl. necessary;Appointment Status Expl.;Exploration Start;Exploration End;Expl. finished;Exploration Result;GfAP-Inst.-Status;Appointment') ||
		strpos($line, 'KLS Id;Nächster Schritt;Eigentümerentscheidung;Auskundung erforderlich;Auskundungs-Status;Auskundung Beginn;Auskundung Ende')
	) { // => Anrufliste
		//echo 'found DGFNeu';
		//echo '</br>';
		upload_GFPLus($file);
	} else {
		echo "<b> $file </b> konnte nicht zugeordnet werden.<br>";
		//echo $line . '<br>';
		//echo '<br>autofetch <br>';
		//upload_autofetch($file);
	}

	if ($parseResponse['status'] == 'error') {
		$response['status'] = 'error';
	}
	return $response;
}

function deletecity($city)
{
	$conn = dbconnect();
	$query = "DELETE FROM `scan4_citylist` WHERE `city` = '" . $city . "'";
	mysqli_query($conn, $query) or die(mysqli_error($conn));
	$conn->close();
}

function set_switchcarrier($city, $carrier)
{
	$conn = dbconnect();
	$query = "UPDATE `scan4_citylist` SET `carrier`= '" . $carrier . "' WHERE `city` = '" . $city . "'";
	mysqli_query($conn, $query) or die(mysqli_error($conn));
	$conn->close();
}

function set_switchstatus($city, $status)
{
	$conn = dbconnect();
	$query = "UPDATE `scan4_citylist` SET `status`= '" . $status . "' WHERE `city` = '" . $city . "'";
	mysqli_query($conn, $query) or die(mysqli_error($conn));
	$conn->close();
}



function uploader_citylist($client)
{
	/*	$conn = dbconnect();
	$lclient = strtolower($client);




	$query = "SELECT * FROM `scan4_citylist` WHERE `city` IS NOT NULL AND client = '" . $client . "'";
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_assoc()) {
			$a_data[] = $row;
		}
	}




	$html = '
	<div class="table table-city-list">
	<div class="table-title"><h3>Liste ' . $client . '</h3></div>
	<div class="import-table-scroll row-cs">
	<table id="table_' . $lclient . '" class="table citylist">
		<thead>
		<th>City</th>
		<th></th>
		<th></th>
		<th></th>
		<th>S4 OPEN</th>
		<th>Date</th>
		</thead>
		<tbody id="citylist_' . $lclient . '" class="city-list">';
	for ($i = 0; $i < count($a_data); $i++) {
		$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE `city` = '" . $a_data[$i]['city'] . "' AND client = '" . $client . "' AND scan4_status = 'OPEN'";
		$result = $conn->query($query);
		$row = $result->fetch_row();

		if ($a_data[$i]['carrier'] === "DGF") {
			$carrier = '<img src="view/images/logo_small_carrier_dgf.jpg"></img>';
		} else if ($a_data[$i]['carrier'] === "UGG") {
			$carrier = '<img src="view/images/logo_small_carrier_ugg.jpg"></img>';
		} else if ($a_data[$i]['carrier'] === "GVG") {
			$carrier = '<img src="view/images/logo_small_carrier_gvg.jpg"></img>';
		}
		if ($a_data[$i]['status'] === "aktiv") {
			$icon = '<i class="ri-checkbox-circle-line"></i>';
			$status = "aktiv";
		} else {
			$icon = '<i class="ri-close-circle-line"></i>';
			$status = "inaktiv";
		}
		if ($a_data[$i]['client'] === $client) {
			$html .= '<tr> <td>' . $a_data[$i]['city'] . '</td><td class="carrier">' . $carrier . '</td><td class="cityliststatus ' . $status . '">' . $icon . '</td><td class="citylisttrash" style="text-align:center"><i class="fa-regular fa-trash-can"></i></td><td>' . $row[0] . '</td><td class="citylistedit">' . $a_data[$i]['lastimport'] . '</td></tr>';
		}
	}
	$conn->close();
	$html .= '</tbody></table></div><div class="row-cs"><div class="table-input-wrapper">
	<div class="input-group mb-3">
	<div class="row groupwrapper">
		<div class="col">
			<input type="text" class="form-control" placeholder="Ort hinzufügen" aria-label="" aria-describedby="button-add1" id="addvalue1" >
		</div>
		<div class="col-4"> 
			<select id="citylistcarrierselect"' . $lclient . ' class="form-select carrier" aria-label="Default select example">
				<option selected>DGF</option>
				<option value="1">UGG</option>
                <option value="2">GVG</option>
			</select>
		</div>
	
		<div class="col-1">
			<div class="input-group-prepend">
				<button class="btn btn-secondary" type="button" id="button-add' . $lclient . '">Add</button>
			</div>
		</div>
	</div>
	</div>
	</div>
	</div>
	</div>';

	$html = '<div class="table-list-wrapper">' . $html . '</div>';

	echo $html;
	*/
}











// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++





function upload_mdu($file)
{
	$csvFile = fopen($file, 'r');
	$date = date('Y-m-d');
	$time = date('H:i:s');
	$conn = dbconnect();


	$colindex['client'] = 0;
	$colindex['city'] = 2;
	$colindex['homeid'] = 3;
	$colindex['hbgstatus'] = 5;
	$colindex['street'] = 13;
	$colindex['streetnumber'] = 14;
	$colindex['streetnumberadd'] = 15;
	$colindex['dpnumber'] = 12;
	$colindex['email'] = 11;
	$colindex['fname'] = 7;
	$colindex['lname'] = 8;
	$colindex['phone1'] = 9;
	$colindex['phone2'] = 10;


	$int_new = 0;
	while (($getData = fgetcsv($csvFile, 100000, ";")) !== FALSE) {

		$client = $getData[$colindex['client']];
		$city = $getData[$colindex['city']] . ' MDU';
		$homeid = $getData[$colindex['homeid']];
		$street = $getData[$colindex['street']];
		$streetnumber = $getData[$colindex['streetnumber']];
		$streetnumberadd = $getData[$colindex['streetnumberadd']];
		$dpnumber = $getData[$colindex['dpnumber']];
		$email = $getData[$colindex['email']];
		$fname = $getData[$colindex['fname']];
		$phone1 = $getData[$colindex['phone1']];
		$phone2 = $getData[$colindex['phone2']];
		$lname = $getData[$colindex['lname']];


		$dpnumber = '';

		$systemstatus = $getData[$colindex['hbgstatus']];
		$hbgstatus = $getData[$colindex['hbgstatus']];
		if ($hbgstatus === 'Scheduling Survey') {
			$hbgstatus = 'OPEN';
			$status = 'OPEN';
		} else if ($hbgstatus === 'Survey Done') {
			$hbgstatus = 'DONE';
			$status = 'DONE';
		}





		// reset all calls
		// $query = "UPDATE `scan4_homes` SET `scan4_comment`='" . $comment . "' , scan4_status = '" . $status . "',anruf1 = NULL, anruf2 = NULL, anruf3 = NULL, anruf4=NULL,anruf5=NULL WHERE `homeid` = '" . $homeid . "';";
		// reset call 3 - 5

		$int_new++;
		$query = "INSERT IGNORE INTO `scan4_homes`(`client`, carrier ,`street`, `streetnumber`, `streetnumberadd`, `city`, `dpnumber`, `homeid`, `firstname`, `lastname`, `phone1`, `phone2`, `email`, `hbg_status`,`scan4_status`,`scan4_added`,system_status) VALUES 
		('" . $client . "', 'UGG','" . $street . "','" . $streetnumber . "','" . $streetnumberadd . "','" . $city . "', '" . $dpnumber . "','" . $homeid . "','" . $fname . "','" . $lname . "','" . $phone1 . "','" . $phone2 . "', '" . $email . "','" . $hbgstatus . "', '" . $status . "' ,'" . $date . "','" . $systemstatus . "')";
		mysqli_query($conn, $query) or die(mysqli_error($conn));
		$update = "UPDATE `scan4_homes` SET `client`='$client',`carrier`='UGG',`street`='$street',`streetnumber`='$streetnumber',`streetnumberadd`='$streetnumberadd',`city`='$city',
		`dpnumber`='$dpnumber',`firstname`='$fname',`lastname`='$lname',`phone1`='$phone1',`phone2`='$phone2',`email`='$email',`isporder`='no',`hbg_status`='$hbgstatus',`scan4_added`='$date ',`system_status`='$systemstatus' WHERE homeid = '$homeid'";
		mysqli_query($conn, $update) or die(mysqli_error($conn));
	}

	fclose($csvFile);
}

function upload_autofetch($file)
{
}


function upload_customlist($file)
{

	$colindex['homeid'] = 0;
	$colindex['comment'] = 1;

	// stringreplace file a with b
	$lines = file($file);

	$inter = 0;
	$csvFile = fopen($file, 'r');
	$date = date('Y-m-d');
	$time = date('H:i:s');
	$array = array();

	$conn = dbconnect();
	fgetcsv($csvFile, 100000, ";"); // skip first row
	while (($getData = fgetcsv($csvFile, 100000, ";")) !== FALSE) {
		$homeid = '';
		$comment = '';
		$status = '';
		//echo $homeid . '<br>';


		$homeid = $getData[$colindex['homeid']];
		$comment = $getData[$colindex['comment']];
		$status = 'OPEN';


		// reset all calls
		// $query = "UPDATE `scan4_homes` SET `scan4_comment`='" . $comment . "' , scan4_status = '" . $status . "',anruf1 = NULL, anruf2 = NULL, anruf3 = NULL, anruf4=NULL,anruf5=NULL WHERE `homeid` = '" . $homeid . "';";
		// reset call 3 - 5
		//$query = "UPDATE `scan4_homes` SET `scan4_comment`='" . $comment . "' , scan4_status = '" . $status . "', anruf3 = NULL, anruf4=NULL,anruf5=NULL WHERE `homeid` = '" . $homeid . "';";
		//mysqli_query($conn, $query);


		$query = "SELECT COUNT(*) AS count FROM `scan4_calls` WHERE `homeid` = '" . $homeid . "' AND `comment` = '" . $comment . "' AND `call_date` = '" . $date . "'";
		$result = mysqli_query($conn, $query) or die(mysqli_error($conn));
		$row = mysqli_fetch_assoc($result);
		$count = $row['count'];

		if ($count == 0) {
			$query = "INSERT INTO `scan4_calls`(`call_count`, `call_date`, `call_time`, `call_user`, `homeid`, `result`, `comment`) VALUES ('0', '" . $date . "', '$time', 'System', '" . $homeid . "', 'import', '" . $comment . "')";
			mysqli_query($conn, $query) or die(mysqli_error($conn));
		} else {
			echo "Entry already exists.";
		}



		$inter++;
	}

	fclose($csvFile);

	echo 'Aktualisiert: ' . $inter . ' Datensätze';

	$conn->close();
}




function upload_newstatus($file)
{
	// stringreplace file a with b
	$lines = file($file);

	$inter = 0;
	$csvFile = fopen($file, 'r');

	$array = array();

	$conn = dbconnect();
	fgetcsv($csvFile, 100000, ";"); // skip first row
	while (($getData = fgetcsv($csvFile, 100000, ";")) !== FALSE) {
		$homeid = '';
		$comment = '';
		$status = '';

		$homeid = $getData[1];
		$comment = $getData[10];
		$status = $getData[11];


		$date = date('Y-m-d');

		if ($status === 'OPEN') {
			$query = "UPDATE `scan4_homes` SET `scan4_comment`='" . $comment . "' , scan4_status = '" . $status . "',anruf1 = NULL, anruf2 = NULL, anruf3 = NULL, anruf4=NULL,anruf5=NULL WHERE `homeid` = '" . $homeid . "';";
			mysqli_query($conn, $query);
		} else {
			$query = "UPDATE `scan4_homes` SET `scan4_comment`='" . $comment . "' , scan4_status = '" . $status . "' WHERE `homeid` = '" . $homeid . "';";
			mysqli_query($conn, $query);
		}


		$query = "INSERT INTO `scan4_calls`(`call_count`, `call_date`, `call_time`, `call_user`, `homeid`, `result`, `comment`) VALUES ('0','" . $date . "','00-00-00','Admin','" . $homeid . "','import','" . $comment . "')";
		mysqli_query($conn, $query) or die(mysqli_error($conn));


		$inter++;
	}
	fclose($csvFile);

	echo 'Aktualisiert: ' . $inter . ' Datensätze';
	//echo '<pre>';
	//echo print_r($array);
	//echo '</pre>';
	$conn->close();
}



function upload_latlong($file)
{
	// stringreplace file a with b
	$lines = file($file);

	$inter = 0;
	$csvFile = fopen($file, 'r');

	$array = array();

	$conn = dbconnect();
	fgetcsv($csvFile, 100000, ";"); // skip first row
	while (($getData = fgetcsv($csvFile, 100000, ";")) !== FALSE) {


		$homeid = $getData[0];
		$lat = $getData[3];
		$lon = $getData[4];

		$date = date('Y-m-d');

		$query = "UPDATE `scan4_homes` SET `lat`='" . $lat . "' , lon = '" . $lon . "' WHERE `homeid` = '" . $homeid . "';";
		mysqli_query($conn, $query);


		$inter++;
	}
	fclose($csvFile);

	echo 'Aktualisiert: ' . $inter . ' Datensätze';
	//echo '<pre>';
	//echo print_r($array);
	//echo '</pre>';
	$conn->close();
}



function upload_reopenscan4($file)
{
	// stringreplace file a with b
	$lines = file($file);




	$inter = 0;
	$csvFile = fopen($file, 'r');

	$array = array();

	$conn = dbconnect();
	fgetcsv($csvFile, 100000, ";"); // skip first row
	while (($getData = fgetcsv($csvFile, 100000, ";")) !== FALSE) {


		$homeid = $getData[0];
		$array[] = $homeid;

		$date = date('Y-m-d');


		$query = "UPDATE `scan4_homes` SET `scan4_status`='OPEN', anruf3 = NULL,anruf4 = NULL,anruf5 = NULL WHERE `homeid` = '" . $homeid . "';";
		mysqli_query($conn, $query);

		//$query = "INSERT INTO `scan4_calls`(`call_count`, `call_date`, `call_time`, `call_user`, `homeid`, `result`, `comment`) VALUES ('0','" . $date . "','00-00-00','Admin','" . $homeid . "','import','Anrufe zurückgesetzt und Status auf OPEN gesetzt.')";
		//mysqli_query($conn, $query) or die(mysqli_error($conn));
		$inter++;
	}
	fclose($csvFile);

	echo 'Aktualisiert: ' . $inter . ' Datensätze';
	//echo '<pre>';
	//echo print_r($array);
	//echo '</pre>';
	$conn->close();
}



function upload_zweibrückencustom($file)
{
	// stringreplace file a with b
	$lines = file($file);
	$lines = str_replace(';', ',', $lines);



	$inter = 0;
	$csvFile = fopen($file, 'r');
	$conn = dbconnect();
	$array = array();
	fgetcsv($csvFile, 100000, ";"); // skip first row
	while (($getData = fgetcsv($csvFile, 100000, ";")) !== FALSE) {


		$street = $getData[0];
		$newdp = $getData[1];


		$query = "UPDATE `scan4_homes` SET dpnumber = '" . $newdp . "' WHERE city = 'Zweibrücken' AND street = '" . $street . "';";
		mysqli_query($conn, $query) or die(mysqli_error($conn));
		$inter++;
		//$query = "UPDATE `scan4_homes` SET anruf1 = NULL,  anruf2 = NULL,  anruf3 = NULL,  anruf4 = NULL,  anruf5 = NULL,  `scan4_comment`='" . $getData[2] . "',`scan4_status`='" . $status . "',`priority`= '" . $prio . "' WHERE homeid = '" . $homeid . "' AND  scan4_status = 'STOPPED';";
		//mysqli_query($conn, $query) or die(mysqli_error($conn));
	}
	fclose($csvFile);
	$conn->close();
	echo 'Aktualisiert: ' . $inter . ' Datensätze';
	//echo '<pre>';
	//echo print_r($array);
	//echo '</pre>';
}



function upload_newdplist($file)
{
	global $currentuser;
	// stringreplace file a with b
	$lines = file($file);
	$lines = str_replace(';', ',', $lines);



	$inter = 0;
	$csvFile = fopen($file, 'r');
	$conn = dbconnect();

	$today = date('Y-m-d');
	$time = date('H:i');

	fgetcsv($csvFile, 100000, ";"); // skip first row
	while (($getData = fgetcsv($csvFile, 100000, ";")) !== FALSE) {
		$newdp = $getData[0];
		$homeid = $getData[1];
		$prio = $getData[2];

		// Check if a scan4_calls entry with the same comment already exists on the current day
		$existingQuery = "SELECT COUNT(*) as count FROM `scan4_calls` WHERE `call_date` = '$today' AND `comment` = 'Neuer DP und Prio gesetzt'";
		$existingResult = $conn->query($existingQuery);
		$existingRow = $existingResult->fetch_assoc();
		$existingCount = $existingRow['count'];

		if ($existingCount == 0) {
			$query = "INSERT INTO `scan4_calls`(`call_date`, `call_time`, `call_user`, `homeid`, `result`, `comment`) 
			VALUES ('$today','$time','System','$homeid','import','Neuer DP und Prio gesetzt')";
			$conn->query($query);
		}

		if (!empty($newdp)) {
			$query = "UPDATE `scan4_homes` SET dpnumber = '$newdp' WHERE homeid = '$homeid';";
			$conn->query($query);

			$fields = [
				'dpnumber' => $newdp,
			];
			homeshistory($conn, $currentuser, $homeid, $fields);
		}

		$query = "UPDATE `scan4_homes` SET priority = '$prio' WHERE homeid = '$homeid';";
		$conn->query($query);
		$fields = [
			'priority' => $prio,
		];
		homeshistory($conn, $currentuser, $homeid, $fields);

		$inter++;
	}
	fclose($csvFile);
	$conn->close();
	echo 'Aktualisiert: ' . $inter . ' Datensätze';
	//echo '<pre>';
	//echo print_r($array);
	//echo '</pre>';
}

function upload_dpbackroll($file)
{
	// stringreplace file a with b
	$lines = file($file);
	$lines = str_replace(';', ',', $lines);



	$inter = 0;
	$csvFile = fopen($file, 'r');
	$conn = dbconnect();

	$today = date('Y-m-d');
	$time = date('H:i');

	fgetcsv($csvFile, 100000, ";"); // skip first row
	while (($getData = fgetcsv($csvFile, 100000, ";")) !== FALSE) {

		$homeid = $getData[1];
		$newdp = $getData[0];
		$prio = $getData[2];
		$query = "UPDATE `scan4_homes` SET `dpnumber`='$newdp', `priority`='$prio' WHERE homeid = '$homeid';";
		$conn->query($query);
		echo $query . '<br>';
		$inter++;
	}
	fclose($csvFile);
	$conn->close();
	echo 'Aktualisiert: ' . $inter . ' Datensätze';
	//echo '<pre>';
	//echo print_r($array);
	//echo '</pre>';
}



function upload_newcontactdetails($file)
{
	// stringreplace file a with b
	$lines = file($file);
	$lines = str_replace(';', ',', $lines);



	$inter = 0;
	$csvFile = fopen($file, 'r');
	$conn = dbconnect();
	$array = array();
	fgetcsv($csvFile, 100000, ";"); // skip first row
	while (($getData = fgetcsv($csvFile, 100000, ";")) !== FALSE) {
		$array[] = $getData;
		$info = '';
		$split = '';
		$comment = '';
		$todaydate = date('Y-m-d');
		$homeid = $getData[0];
		$info = $getData[1];
		$comment = $getData[3];



		$info = str_replace(['-', 'GEE Info', 'NRI Info', 'HBO Info', ' ', 'GEE INFO', 'GEE INFO.', 'GEEINFO', ':'], '', $info);



		$split = explode('//', $info);
		$phone1 = '';
		$phone2 = '';
		$mail = '';
		$val = '';
		if (isset($split[0])) {
			$val = $split[0];
			if (strlen($val) > 5) {
				$val = str_replace([' '], '', $val);
				if (strlen($val) > 5 && strpos($val, '@') !== false) {
					$mail = $val;
				} else {
					if ($phone1 === '') {
						$phone1 = $val;
					} else {
						$phone2 = $val;
					}
				}
			}
		}
		if (isset($split[1])) {
			$val = $split[1];
			if (strlen($val) > 5) {
				$val = str_replace([' '], '', $val);
				if (strlen($val) > 5 && strpos($val, '@') !== false) {
					$mail = $val;
				} elseif (strpos($val, '@') !== true) {
					if ($phone1 === '') {
						$phone1 = $val;
					} else {
						$phone2 = $val;
					}
				}
			}
		}
		if (isset($split[2])) {
			$val = $split[2];
			if (strlen($val) > 5) {
				$val = str_replace([' '], '', $val);
				if (strlen($val) > 5 && strpos($val, '@') !== false) {
					$mail = $val;
				} elseif (strpos($val, '@') !== true) {
					if ($phone1 === '') {
						$phone1 = $val;
					} else {
						$phone2 = $val;
					}
				}
			}
		}

		/*
		echo '<pre>';
		print_r($split);
		echo '</pre>';
		echo $comment;
		echo '</br>';
		echo $homeid;
		echo '</br>';
		echo $phone1;
		echo '</br>';
		echo $phone2;
		echo '</br>';
		echo $mail;
		echo '</br>';
		echo '------';
		echo '</br>';
*/


		/*
		if (strlen($comment) > 3) {
			$query = "INSERT INTO `scan4_calls`(`call_count`, `call_date`, `call_time`, `call_user`, `homeid`, `result`, `comment`) VALUES ('0','" . $todaydate . "','00-00-00','Insyte Report','" . $homeid . "','import','" . $comment . "')";
			mysqli_query($conn, $query) or die(mysqli_error($conn));
		}*/

		$query = "UPDATE `scan4_homes` SET scan4_status='OPEN', scan4_comment = '" . $comment . "', priority = '1', anruf1 = NULL,  anruf2 = NULL,  anruf3 = NULL,  anruf4 = NULL,  anruf5 = NULL, `phone3`='" . $phone1 . "', `phone4`= '" . $phone2 . "', mail2 = '" . $mail . "' WHERE homeid = '" . $homeid . "' AND (`scan4_status` LIKE 'STOPPED' OR `scan4_status` LIKE 'OPEN');";
		mysqli_query($conn, $query) or die(mysqli_error($conn));
		$inter++;
		//$query = "UPDATE `scan4_homes` SET anruf1 = NULL,  anruf2 = NULL,  anruf3 = NULL,  anruf4 = NULL,  anruf5 = NULL,  `scan4_comment`='" . $getData[2] . "',`scan4_status`='" . $status . "',`priority`= '" . $prio . "' WHERE homeid = '" . $homeid . "' AND  scan4_status = 'STOPPED';";
		//mysqli_query($conn, $query) or die(mysqli_error($conn));
	}
	fclose($csvFile);
	$conn->close();
	echo 'Aktualisiert: ' . $inter . ' Datensätze';
	//echo '<pre>';
	//echo print_r($array);
	//echo '</pre>';
}

function upload_statuschange($file)
{
	global $currentuser;
	$csvFile = fopen($file, 'r');
	$conn = dbconnect();
	$array = array();
	fgetcsv($csvFile, 100000, ";"); // skip first row
	while (($getData = fgetcsv($csvFile, 100000, ";")) !== FALSE) {
		$array[] = $getData;

		$todaydate = date('Y-m-d');
		$homeid = $getData[0];
		$status = $getData[1];

		$query = "UPDATE `scan4_homes` SET scan4_status = '" . $status . "' WHERE homeid = '" . $homeid . "';";
		mysqli_query($conn, $query) or die(mysqli_error($conn));
	}
	fclose($csvFile);
	$conn->close();

	echo '<pre>';
	echo print_r($array);
	echo '</pre>';
}


function upload_priocheck($file)
{
	global $currentuser;
	$csvFile = fopen($file, 'r');
	$conn = dbconnect();
	$array = array();
	fgetcsv($csvFile, 100000, ";"); // skip first row
	while (($getData = fgetcsv($csvFile, 100000, ";")) !== FALSE) {
		$array[] = $getData;

		$todaydate = date('Y-m-d');
		$homeid = $getData[0];
		$prio = $getData[1];
		$status = $getData[3];

		/*
		if (strlen($getData[2]) > 3) {
			$query = "INSERT INTO `scan4_calls`(`call_count`, `call_date`, `call_time`, `call_user`, `homeid`, `result`, `comment`) VALUES ('0','" . $todaydate . "','00-00-00','Anrufliste','" . $homeid . "','import','" . $getData[2] . "')";
			echo $query;
			mysqli_query($conn, $query) or die(mysqli_error($conn));
		}
		*/
		$query = "UPDATE `scan4_homes` SET `scan4_comment`='" . $getData[2] . "', `priority`= '" . $prio . "' WHERE homeid = '" . $homeid . "';";
		mysqli_query($conn, $query) or die(mysqli_error($conn));
		if (strlen($status > 3)) {
			$query = "UPDATE `scan4_homes` SET anruf1 = NULL,  anruf2 = NULL,  anruf3 = NULL,  anruf4 = NULL,  anruf5 = NULL,  `scan4_comment`='" . $getData[2] . "',`scan4_status`='" . $status . "',`priority`= '" . $prio . "' WHERE homeid = '" . $homeid . "' AND  scan4_status = 'STOPPED';";
			mysqli_query($conn, $query) or die(mysqli_error($conn));
			$query = "INSERT INTO `scan4_homes_history`(`homeid`, `col1`, `col2`, `col3`, `col4`, col5,col6) VALUES ('" . $homeid . "','import','" . $currentuser . "','set scan4status to " . $status . "','comment: " . $getData[2] . "','priority to" . $prio . "', 'calls set to 0')";
			mysqli_query($conn, $query);
		} else {
			$query = "UPDATE `scan4_homes` SET anruf1 = NULL,  anruf2 = NULL,  anruf3 = NULL,  anruf4 = NULL,  anruf5 = NULL,  `scan4_comment`='" . $getData[2] . "',`priority`= '" . $prio . "' WHERE homeid = '" . $homeid . "' AND  scan4_status = 'STOPPED';";
			mysqli_query($conn, $query) or die(mysqli_error($conn));
			$query = "INSERT INTO `scan4_homes_history`(`homeid`, `col1`, `col2`, `col3`, `col4`, col5) VALUES ('" . $homeid . "','import','" . $currentuser . "','comment: " . $getData[2] . "','set priority to " . $prio . "', 'calls set to 0')";
			mysqli_query($conn, $query);
		}
	}
	fclose($csvFile);
	$conn->close();

	//echo '<pre>';
	//echo print_r($array);
	//echo '</pre>';
}


function upload_readwrite_gvg($file)
{
	$conn = dbconnect();
	// Open uploaded CSV file with read-only mode
	$csvFile = fopen($file, 'r');
	// Setup variables
	$a_homeid = array();
	$a_citylist = array();
	$int_new = 0;
	$int_update = 0;
	$a_homeidlist = get_all_homeids();
	$a_cityupdate = array();
	global $currentuser;
	/*
	echo "<pre>";
	print_r($a_homeidlist);
	echo "</pre>";
	*/
	// Parse data from CSV file line by line
	ob_start();
	$inter = 0;
	while (($getData = fgetcsv($csvFile, 100000, ';')) !== FALSE) {
		// Get row data
		$inter++;
		$streetNuN = "";
		$streetNumber = "";
		$city = "";
		$street = "";
		$streetNumber = "";
		$streetNuN = "";
		$unit = "";
		$plz = "";
		$dp = "";
		$homeid = "";
		$vorname = "";
		$nachname = "";
		$phone1 = "";
		$mail = "";
		$phone2 = "";
		$status = "";
		$sc4status = "";
		$hbgstatus = "";
		$hbgdate = "";
		$client = 'Insyte';
		$carrier = 'GVG';


		if ($inter != 1) {
			// echo "<pre>";
			//print_r($getData);
			//echo "</pre>";
			//echo "<pre>";
			$datum = date("Y-m-d");


			$addressParts = explode(", ", $getData[4]);
			$streetParts = explode(" ", $addressParts[0]);
			$cityParts = explode(" ", $addressParts[1]);

			// Extract relevant information
			$city = $cityParts[1];
			$plz = $cityParts[0];
			$streetNumber = array_pop($streetParts);
			$street = implode(" ", $streetParts);

			// Clean up street and street number
			if (mb_substr($street, -1) === ' ') {
				$street = mb_substr($street, 0, -1);
			}

			if (!is_numeric(mb_substr($streetNumber, -1))) {
				$streetNuN = mb_substr($streetNumber, -1);
				$streetNumber = mb_substr($streetNumber, 0, -1);
				$street = str_replace($streetNumber . $streetNuN, '', $street);
			}



			$homeid = $getData[0];

			$unit = $getData[2];

			$a_name = explode(" ", $getData[6]);
			$nachname = $a_name[array_key_last($a_name)];
			$vorname = str_replace($nachname, "", $getData[6]);
			$phone1 = "";
			$phone2 = "";
			$phone = '';
			$phone = $getData[7];
			$phone = str_replace(["\n", "\r", '/', '^', '-'], '',  $phone);
			if (mb_substr($phone, 0, 2) === '00') {
				$a_phone = explode("0049", $phone);
				if (isset($a_phone[1])) {
					$phone1 = $a_phone[1];
					$phone1 = preg_replace('/\s+/', '', $phone1);
				}
				if (isset($a_phone[2])) {
					$phone2 = $a_phone[2];
					$phone2 = preg_replace('/\s+/', '', $phone2);
				}
				$phone1 = str_replace([' ', "\n", "\r", '/', '^'], '',  $phone1);
				$phone2 = str_replace([' ', "\n", "\r", '/', '^'], '', $phone2);
			} else {
				if (strpos($phone, ' ') !== false) {
					$a_phone = explode(" ", $phone);
					if (isset($a_phone[0])) {
						$phone1 = $a_phone[0];
						$phone1 = preg_replace('/\s+/', '', $phone1);
					}
					if (isset($a_phone[1])) {
						$phone2 = $a_phone[1];
						$phone2 = preg_replace('/\s+/', '', $phone2);
					}
				} else {
					$phone1 = $phone;
					$phone1 = str_replace([' ', "\n", "\r", '/', '^'], '',  $phone1);
				}
				$phone1 = ltrim($phone1, '0');
				$phone2 = ltrim($phone2, '0');
				if (strlen($phone1) <= 4) {
					$phone1 .= $phone2;
					$phone2 = '';
				}
			}



			$a_homeid[] = $getData[0];
			if (!(in_array($city, $a_citylist))) {
				$a_citylist[] = $city;
			}

			$planned = $getData[8];
			if ($getData[12] === "Ja") {
				$sc4status = "DONE";
				$hbgstatus = "DONE";
			} elseif ($getData[8] = "- Begehungstermin fehlt -") {
				$sc4status = "OPEN";
				$hbgstatus = "OPEN";
				$planned = "";
			} else {
				$sc4status = "OPEN";
				$hbgstatus = "PLANNED";
			}

			$hbgdate = $getData[8];
			$dateObject = DateTime::createFromFormat('Y-m-d H:i:s', $hbgdate);
			if ($dateObject) {
				$hbgdate = $dateObject->format('Y-m-d');
			} else {
				$hbgdate = NULL;
			}

			if (!in_array($city, $a_cityupdate)) $a_cityupdate[] = $city; // add city for update date in city table
			if (!in_array($homeid, $a_homeidlist)) { // Check if homeid is new
				$int_new++;
				$query = "INSERT IGNORE INTO `scan4_homes`(`client`, `carrier`, `street`, `streetnumber`, `streetnumberadd`, `unit`, `city`, `plz`, `homeid`, `firstname`, `lastname`, `phone1`, `phone2`, `hbg_status`, `hbg_plandate`, `hbg_plandate`, `scan4_status`, `scan4_added`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

				$stmt = mysqli_prepare($conn, $query);
				mysqli_stmt_bind_param($stmt, 'ssssssssssssssssss', $client, $carrier, $street, $streetNumber, $streetNuN, $unit, $city, $plz, $homeid, $vorname, $nachname, $phone1, $phone2, $hbgstatus, $planned, $hbgdate, $sc4status, $datum);
				mysqli_stmt_execute($stmt) or die(mysqli_error($conn));
			} else {


				$fields = [
					'firstname'       => $vorname,
					'lastname'        => $nachname,
					'street'          => $street,
					'streetnumber'    => $streetNumber,
					'streetnumberadd' => $streetNuN,
					'unit'            => $unit,
					'phone1'          => $phone1,
					'phone2'          => $phone2,
					'hbg_status'      => $hbgstatus,
					'email'           => $mail,
					'dpnumber'        => $dp,

				];

				homeshistory($conn, $currentuser, $homeid, $fields);


				$query = "UPDATE `scan4_homes` SET `firstname`=?, `lastname`=?, `street`=?, `streetnumber`=?, `streetnumberadd`=?, `unit`=?, `phone1`=?, `phone2`=?, `hbg_status`=?, `hbg_plandate`=?, `hbg_date`=? WHERE `homeid`=?";

				$stmt = mysqli_prepare($conn, $query);
				mysqli_stmt_bind_param($stmt, 'ssssssssssss', $vorname, $nachname, $street, $streetNumber, $streetNuN, $unit, $phone1, $phone2, $hbgstatus, $planned, $hbgdate, $homeid);
				mysqli_stmt_execute($stmt) or die(mysqli_error($conn));

				$int_update++;
			}
		}
	} // ende while loop
	foreach ($a_cityupdate as $city) {
		upd_uploaddate($city);
	}
	// check if city is not in the list and list them
	$html = '<div class="table-wrapper">
	<table class="table table-nocity">
			<thead>
				<tr>
					<th>Nicht zugeordnet</th>
				</tr>                
			</thead>
			<tbody class="table-body-font-size">';
	$int = 0;
	foreach ($a_citylist as $value) {
		if (!(in_array($value, $a_citylist))) {
			$html .= '<tr><td> ' . $value . '</td></tr>';
			$int++;
		}
	}
	$html .= ' </tbody></table></div>';
	echo $html;
	if ($int === 1) {
		echo "<p> Es wurde <b>$int</b> Ort gefunden der <b>nicht</b> zugeordnet werden konnte! </p>";
	} elseif ($int > 1) {
		echo "<p> Es wurden <b>$int</b> Orte gefunden die <b>nicht</b> zugeordnet werden konnten! </p>";
	}
	echo "<p> Neue Einträge GVG - Insyte: <b>$int_new</b></p>";
	echo "<p> Einträge aktualisiert GVG - Insyte: <b>$int_update</b></p>";


	// Close opened CSV file
	fclose($csvFile);
	//dbmcleanup();

}






function upload_readwrite_dgf($file)
{
	global $currentuser;
	$conn = dbconnect();
	// Open uploaded CSV file with read-only mode
	$csvFile = fopen($file, 'r');
	// Setup variables
	$a_citylist = get_all_citys();
	//echo "<pre>";
	//print_r($a_citylist);
	//echo "</pre>";
	$a_citynew = array();
	$a_cityupdate = array();
	$int_new = 0;
	$int_update = 0;
	$a_homeidlist = get_all_homeids();
	/*
	echo "<pre>";
	print_r($a_citylist);
	echo "</pre>";
	echo 'Parse data from CSV file line by line';
	*/
	$count = 0;
	$inter = 0;
	$datum = date("Y-m-d");
	fgetcsv($csvFile, 100000, ";"); // skip first row
	while (($getData = fgetcsv($csvFile, 100000, ";")) !== FALSE) {
		// Get row data
		$inter++;
		$city = "";
		$street = "";
		$streetNumber = "";
		$streetNuN = "";
		$unit = "";
		$plz = "";
		$dp = "";
		$homeid = "";
		$vorname = "";
		$nachname = "";
		$phone1 = "";
		$mail = "";
		$phone2 = "";
		$status = "";
		$sc4status = "";
		$hbgstatus = "";
		$hbgdate = "";
		$systemstatus = '';


		if (!($getData[10] === "" || $getData[20] === "")) {
			$count++;

			$city = $getData[4];
			$street = $getData[0];
			$streetNumber = $getData[1];
			$streetNuN = $getData[2];
			$unit = $getData[3];
			$plz = mb_substr($getData[5], 0, 5);
			$dp = $getData[6];
			$homeid = $getData[7];
			$nachname = $getData[10];



			$a_name = explode(" ", $getData[10], 2);
			$vorname = $a_name[0];
			$nachname = str_replace($vorname, '', $nachname);



			if ($count < 5) {
				echo $plz . '</br>';
			}
			if ($street === 'Hallweg') {
				echo '"' . $plz . '"' . '</br>';
			}
			$phone1 = $getData[11];
			$mail = $getData[12];
			$phone2 = $getData[14];
			$phone1 = str_replace([' ', "\n", "\r", '/', '^'], '',  $phone1);
			$phone2 = str_replace([' ', "\n", "\r", '/', '^'], '', $phone2);
			$getData[16] = str_replace(' ', '', $getData[16]);
			$systemstatus = $getData[16];
			if (($getData[16] === "Tiefbau") && ($getData[19] === "R0" || $getData[19] === "R20" || strlen($getData[21]) > 0)) {
				$sc4status = "DONE";
				$hbgstatus = "DONE";
			} else if ($getData[16] === "Einblasen" || $getData[16] === "Spleiße" || $getData[16] === "Hausanschluss" || $getData[16] === "Arbeitsvorbereitung") {
				$sc4status = "DONE";
				$hbgstatus = "DONE";
			} else if (($getData[16] === "Hausbegehung") && ($getData[19] === "R0" || $getData[19] === "R20")) {
				$sc4status = "OPEN";
				$hbgstatus = "OPEN";
			}
			if (($getData[16] === "Denied") || ($getData[19] !== "R0" && $getData[19] !== "R20")) {
				$sc4status = "STOPPED";
				$hbgstatus = "STOPPED";
			}

			$pic1 = $getData[25];
			$pic2 = $getData[26];
			$pic3 = $getData[27];
			$pic4 = $getData[28];
			$pic5 = $getData[29];
			$pic6 = $getData[30];
			$pic7 = $getData[31];
			$pic8 = $getData[32];

			$lat = $getData[36];
			$lon = $getData[37];


			if (in_array($city, array_column($a_citylist, 'city'))) { // Add City to Cityarray 
				if (!in_array($city, $a_cityupdate)) $a_cityupdate[] = $city; // add city for update date in city table
				if (!(in_array($homeid, $a_homeidlist))) { // Check if homeid is new
					$int_new++;
					$query = "INSERT IGNORE INTO `scan4_homes`(`client`, carrier ,`street`, `streetnumber`, `streetnumberadd`, `unit`, `city`, `plz`, `dpnumber`, `homeid`, `firstname`, `lastname`, `phone1`, `phone2`, `email`, `hbg_status`,`scan4_status`,`scan4_added`, `foto1`, `foto2`, `foto3`, `foto4`, `foto5`, `foto6`, `foto7`, `foto8`,lat,lon,system_status) VALUES ('Insyte', 'DGF','" . $street . "','" . $streetNumber . "','" . $streetNuN . "','" . $unit . "','" . $city . "','" . $plz . "', '" . $dp . "','" . $homeid . "','" . $vorname . "','" . $nachname . "','" . $phone1 . "','" . $phone2 . "', '" . $mail . "','" . $hbgstatus . "', '" . $sc4status . "' ,'" . $datum . "','" . $pic1 . "','" . $pic2 . "','" . $pic3 . "','" . $pic4 . "','" . $pic5 . "','" . $pic6 . "','" . $pic7 . "','" . $pic8 . "','','', '" . $systemstatus . "')";
					//echo $query . "</br>";
					mysqli_query($conn, $query) or die(mysqli_error($conn));
				} else {


					$fields = [
						'firstname'       => $vorname,
						'lastname'        => $nachname,
						'street'          => $street,
						'streetnumber'    => $streetNumber,
						'streetnumberadd' => $streetNuN,
						'unit'            => $unit,
						'phone1'          => $phone1,
						'phone2'          => $phone2,
						'hbg_status'      => $hbgstatus,
						'email'           => $mail,
						'dpnumber'        => $dp,

					];

					homeshistory($conn, $currentuser, $homeid, $fields);

					$vorname = mysqli_real_escape_string($conn, $vorname);
					$nachname = mysqli_real_escape_string($conn, $nachname);
					$street = mysqli_real_escape_string($conn, $street);



					$query = "UPDATE `scan4_homes` SET `firstname` ='" . $vorname . "', `lastname` ='" . $nachname . "', `street`='" . $street . "',`streetnumber`='" . $streetNumber . "',`streetnumberadd`='" . $streetNuN . "',`unit`='" . $unit . "', `dpnumber`='" . $dp . "',`phone1`='" . $phone1 . "',`phone2`='" . $phone2 . "',`email`='" . $mail . "',`hbg_status`='" . $hbgstatus . "',`foto1`='" . $pic1 . "',`foto2`='" . $pic2 . "',`foto3`='" . $pic3 . "',`foto4`='" . $pic5 . "',`foto6`='" . $pic6 . "',`foto7`='" . $pic6 . "',`foto7`='" . $pic7 . "',`foto8`='" . $pic8 . "', system_status = '" . $systemstatus . "' WHERE `homeid` = '" . $homeid . "'";
					//echo $query . "</br>";
					mysqli_query($conn, $query) or die(mysqli_error($conn));

					$int_update++;
				}
			} else {
				if (!(in_array($city, $a_citynew))) {
					$a_citynew[] = $city;
				}
			}
		}
	} // ende while loop

	// check if city is not in the list and list them
	echo "<pre>";
	print_r($a_cityupdate);
	echo "</pre>";
	foreach ($a_cityupdate as $city) {
		upd_uploaddate($city);
	}
	$html = '<div class="table-wrapper">
	<table class="table table-nocity">
			<thead>
				<tr>
					<th>Nicht zugeordnet</th>
				</tr>                
			</thead>
			<tbody class="table-body-font-size">';
	$int = 0;
	foreach ($a_citynew as $value) {

		if ($value !== "" && !(in_array($value, $a_citylist))) {
			$html .= '<tr><td> ' . $value . '</td></tr>';
			$int++;
		}
	}
	$html .= ' </tbody></table></div>';
	echo $html;
	if ($int === 1) {
		echo "<p> Es wurde <b>$int</b> Ort gefunden der <b>nicht</b> zugeordnet werden konnte! </p>";
	} elseif ($int > 1) {
		echo "<p> Es wurden <b>$int</b> Orte gefunden die <b>nicht</b> zugeordnet werden konnten! </p>";
	}
	echo "<p> Neue Einträge DeutscheGlasfaser: <b>$int_new</b></p>";
	echo "<p> Einträge aktualisiert DeutscheGlasfaser: <b>$int_update</b></p>";

	//echo "full: " . $count;
	// Close opened CSV file
	fclose($csvFile);

	//echo $inter;


}










function upload_readwrite_dgf_pha($file)
{
	global $currentuser;
	$conn = dbconnect();
	// Open uploaded CSV file with read-only mode
	$csvFile = fopen($file, 'r');
	// Setup variables
	$a_citylist = get_all_citys();
	//echo "<pre>";
	//print_r($a_citylist);
	//echo "</pre>";
	$a_citynew = array();
	$a_cityupdate = array();
	$int_new = 0;
	$int_update = 0;
	$a_homeidlist = get_all_homeids();
	/*
	echo "<pre>";
	print_r($a_citylist);
	echo "</pre>";
	echo 'Parse data from CSV file line by line';
	*/
	$count = 0;
	$inter = 0;
	$datum = date("Y-m-d");
	fgetcsv($csvFile, 100000, ";"); // skip first row
	while (($getData = fgetcsv($csvFile, 100000, ";")) !== FALSE) {

		// Get row data
		$inter++;
		$city = "";
		$street = "";
		$streetNumber = "";
		$streetNuN = "";
		$unit = "";
		$plz = "";
		$dp = "";
		$homeid = "";
		$vorname = "";
		$nachname = "";
		$phone1 = "";
		$mail = "";
		$phone2 = "";
		$status = "";
		$sc4status = "";
		$hbgstatus = "";
		$hbgdate = "";
		$systemstatus = '';

		$col_index['street'] = 0;
		$col_index['streetnumber'] = 1;
		$col_index['streetnumberadd'] = 2;
		$col_index['city'] = 4;
		$col_index['plz'] = 5;
		$col_index['unit'] = 3;
		$col_index['dpnumber'] = 6;
		$col_index['homeid'] = 7;
		$col_index['lname'] = 10;
		$col_index['fname'] = 0;
		$col_index['phone'] = 11;
		$col_index['email'] = 12;
		$col_index['phone2'] = 14;
		$col_index['status'] = 16;
		$col_index['operator'] = 24;
		$col_index['RNcode'] = 19;

		$count++;

		$city = $getData[$col_index['city']];
		$street = $getData[$col_index['street']];
		$streetNumber = $getData[$col_index['streetnumber']];
		$streetNuN = $getData[$col_index['streetnumberadd']];
		$unit = $getData[$col_index['unit']];
		$plz = mb_substr($getData[$col_index['plz']], 0, 5);
		$dp = $getData[$col_index['dpnumber']];
		$homeid = $getData[$col_index['homeid']];
		$nachname = $getData[$col_index['lname']];
		$operator = $getData[$col_index['operator']];



		if (strpos($operator, "pha") !== false) {
			$city = $city . ' DGpha';
		}



		$split = explode(' ', $nachname);
		$nachname = array_pop($split); // Set the last name to the last word in the array
		$vorname = implode(' ', $split); 	// Set the first name(s) to the remaining words in the array


		$phone1 = $getData[$col_index['phone']];
		$mail = $getData[$col_index['email']];
		$phone2 = $getData[$col_index['phone2']];
		$phone1 = str_replace([' ', "\n", "\r", '/', '^'], '',  $phone1);
		$phone2 = str_replace([' ', "\n", "\r", '/', '^'], '', $phone2);

		$systemstatus = $getData[$col_index['status']];


		if (($systemstatus === "Denied") || ($getData[$col_index['RNcode']] !== "R0" && $getData[$col_index['RNcode']] !== "R20")) {
			$sc4status = "STOPPED";
			$hbgstatus = "STOPPED";
		}

		if (($systemstatus === "Tiefbau") && ($getData[$col_index['RNcode']] === "R0" || $getData[$col_index['RNcode']] === "R20")) {
			$sc4status = "DONE";
			$hbgstatus = "DONE";
		} else if ($systemstatus === "Einblasen" || $systemstatus === "Spleiße" || $systemstatus === "Hausanschluss" || $systemstatus === "Arbeitsvorbereitung") {
			$sc4status = "DONE";
			$hbgstatus = "DONE";
		} else if (($systemstatus === "Hausbegehung")) {
			$sc4status = "OPEN";
			$hbgstatus = "OPEN";
		}



		$pic1 = $getData[25];
		$pic2 = $getData[26];
		$pic3 = $getData[27];
		$pic4 = $getData[28];
		$pic5 = $getData[29];
		$pic6 = $getData[30];
		$pic7 = $getData[31];
		$pic8 = $getData[32];


		if (in_array($city, array_column($a_citylist, 'city'))) { // Add City to Cityarray 
			if (!in_array($city, $a_cityupdate)) $a_cityupdate[] = $city; // add city for update date in city table
			if (!(in_array($homeid, $a_homeidlist))) { // Check if homeid is new
				$int_new++;
				$query = "INSERT IGNORE INTO `scan4_homes`(`client`, carrier ,`street`, `streetnumber`, `streetnumberadd`, `unit`, `city`, `plz`, `dpnumber`, `homeid`, `firstname`, `lastname`, `phone1`, `phone2`, `email`, `hbg_status`,`scan4_status`,`scan4_added`, `foto1`, `foto2`, `foto3`, `foto4`, `foto5`, `foto6`, `foto7`, `foto8`,system_status) VALUES ('Insyte', 'DGF','" . $street . "','" . $streetNumber . "','" . $streetNuN . "','" . $unit . "','" . $city . "','" . $plz . "', '" . $dp . "','" . $homeid . "','" . $vorname . "','" . $nachname . "','" . $phone1 . "','" . $phone2 . "', '" . $mail . "','" . $hbgstatus . "', '" . $sc4status . "' ,'" . $datum . "','" . $pic1 . "','" . $pic2 . "','" . $pic3 . "','" . $pic4 . "','" . $pic5 . "','" . $pic6 . "','" . $pic7 . "','" . $pic8 . "', '" . $systemstatus . "')";
				//echo $query . "</br>";
				mysqli_query($conn, $query) or die(mysqli_error($conn));
			} else {


				$fields = [
					'firstname'       => $vorname,
					'lastname'        => $nachname,
					'street'          => $street,
					'streetnumber'    => $streetNumber,
					'streetnumberadd' => $streetNuN,
					'unit'            => $unit,
					'phone1'          => $phone1,
					'phone2'          => $phone2,
					'hbg_status'      => $hbgstatus,
					'email'           => $mail,
					'dpnumber'        => $dp,

				];

				homeshistory($conn, $currentuser, $homeid, $fields);


				$query = "UPDATE `scan4_homes` SET `firstname` ='" . $vorname . "', `lastname` ='" . $nachname . "', `street`='" . $street . "',`streetnumber`='" . $streetNumber . "',`streetnumberadd`='" . $streetNuN . "',`unit`='" . $unit . "', `dpnumber`='" . $dp . "',`phone1`='" . $phone1 . "',`phone2`='" . $phone2 . "',`email`='" . $mail . "',`hbg_status`='" . $hbgstatus . "',`foto1`='" . $pic1 . "',`foto2`='" . $pic2 . "',`foto3`='" . $pic3 . "',`foto4`='" . $pic5 . "',`foto6`='" . $pic6 . "',`foto7`='" . $pic6 . "',`foto7`='" . $pic7 . "',`foto8`='" . $pic8 . "', system_status = '" . $systemstatus . "' WHERE `homeid` = '" . $homeid . "'";
				mysqli_query($conn, $query) or die(mysqli_error($conn));

				$int_update++;
			}
		} else {
			if (!(in_array($city, $a_citynew))) {
				$a_citynew[] = $city;
			}
		}
	} // ende while loop

	// check if city is not in the list and list them
	echo "<pre>";
	print_r($a_cityupdate);
	echo "</pre>";
	foreach ($a_cityupdate as $city) {
		upd_uploaddate($city);
	}
	$html = '<div class="table-wrapper">
	<table class="table table-nocity">
			<thead>
				<tr>
					<th>Nicht zugeordnet</th>
				</tr>                
			</thead>
			<tbody class="table-body-font-size">';
	$int = 0;
	foreach ($a_citynew as $value) {

		if ($value !== "" && !(in_array($value, $a_citylist))) {
			$html .= '<tr><td> ' . $value . '</td></tr>';
			$int++;
		}
	}
	$html .= ' </tbody></table></div>';
	echo $html;
	if ($int === 1) {
		echo "<p> Es wurde <b>$int</b> Ort gefunden der <b>nicht</b> zugeordnet werden konnte! </p>";
	} elseif ($int > 1) {
		echo "<p> Es wurden <b>$int</b> Orte gefunden die <b>nicht</b> zugeordnet werden konnten! </p>";
	}
	echo "<p> Neue Einträge DeutscheGlasfaser: <b>$int_new</b></p>";
	echo "<p> Einträge aktualisiert DeutscheGlasfaser: <b>$int_update</b></p>";


	fclose($csvFile);
}

/*
function upload_emerg($file)
{
	$conn = dbconnect();
	// Open uploaded CSV file with read-only mode
	$csvFile = fopen($file, 'r');
	// Setup variables

	$int_new = 0;
	$int_update = 0;

	//echo "<pre>";
	//print_r($a_citylist);
	//echo "</pre>";
	// Parse data from CSV file line by line
	$count = 0;
	$inter = 0;
	$datum = date("Y-m-d");
	fgetcsv($csvFile, 100000, ","); // skip first row
	while (($getData = fgetcsv($csvFile, 100000, ";")) !== FALSE) {
		// Get row data
		if (!($getData[0] === "" && $getData[1] === "" && $getData[2] === "")) {
			$hbgdate = "";
			$homeid = "";
			$homeid = $getData[7];
			if (strlen($getData[19]) > 2) {
				$hbgdate = date("Y-m-d", strtotime($getData[19]));
			}
			if (strlen($hbgdate) > 2) {
				$query = "UPDATE `scan4_homes` SET scan4_hbgdate = '" . $hbgdate . "' WHERE `HomeId` = '" . $homeid . "'";
			} else {
				$query = "UPDATE `scan4_homes` SET scan4_hbgdate = NULL WHERE `HomeId` = '" . $homeid . "'";
			}
			mysqli_query($conn, $query) or die(mysqli_error($conn));
		}
	}
}
*/


function upload_readwrite_sc4_gvg($file)
{
	$conn = dbconnect();
	// Open uploaded CSV file with read-only mode
	$csvFile = fopen($file, 'r');
	// Setup variables

	$int_new = 0;
	$int_update = 0;


	// Parse data from CSV file line by line
	$count = 0;
	$inter = 0;
	$datum = date("Y-m-d");
	fgetcsv($csvFile, 100000, ","); // skip first row
	while (($getData = fgetcsv($csvFile, 100000, ";")) !== FALSE) {
		// Get row data
		$inter++;
		$anruf1 = "";
		$anruf2 = "";
		$anruf3 = "";
		$anruf4 = "";
		$anruf5 = "";
		$mailsend = "";
		$postsend = "";
		$code = "";
		$hbgdate = "";
		$adddate = '';



		$homeid = $getData[5];
		$anruf1 = $getData[17];
		if ($anruf1 !== "") $anruf1 = date("Y-m-d", strtotime($anruf1));
		$anruf2 = $getData[18];
		if ($anruf2 !== "") $anruf2 = date("Y-m-d", strtotime($anruf2));
		$anruf3 = $getData[19];
		if ($anruf3 !== "") $anruf3 = date("Y-m-d", strtotime($anruf3));
		$anruf4 = $getData[20];
		if ($anruf4 !== "") $anruf4 = date("Y-m-d", strtotime($anruf4));
		$anruf5 = $getData[21];
		if ($anruf5 !== "") $anruf5 = date("Y-m-d", strtotime($anruf5));
		$mailsend = $getData[23];
		$postsend = $getData[22];
		$code = $getData[25];



		$hbgstatus = $getData[12];
		if ($hbgstatus === 'Nein') {
			$hbgstatus = 'OPEN';
			$sc4status = 'OPEN';
		} else if ($hbgstatus === 'Ja') {
			$hbgstatus = 'DONE';
			$sc4status = "DONE";
		}


		$adddate = date("Y-m-d", strtotime($getData[24]));
		if (strlen($anruf5) > 2) {
			$sc4status = "STOPPED";
		}
		if (strlen($getData[14]) > 2) {
			$hbgdate = date("Y-m-d", strtotime($getData[14]));
			if (date('Y-m-d', strtotime($hbgdate . ' -2 days')) < date('Y-m-d')) {
				$sc4status = "OVERDUE";
			} else {
				$sc4status = "PLANNED";
			}
		}

		// ============================
		// UGG



		$comment = $getData[15];
		$comment = str_replace("'", '', $comment);
		if (strpos($getData[15], 'HBG Excel') !== false || strpos($getData[15], 'HBG Screen') !== false) {
			$sc4status = "DONE CLOUD";
		} else if (strpos($getData[15], 'falsche Nummer') !== false) {
			$sc4status = "STOPPED";
		} else if (strpos($getData[15], 'nummer nicht vergeben') !== false) {
			$sc4status = "STOPPED";
		} else if (strpos($getData[15], 'Keine HBG - KD kündigt') !== false) {
			$sc4status = "STOPPED";
		} else if (strpos($getData[15], 'Keine HBG - Falsche Nummer') !== false) {
			$sc4status = "STOPPED";
		} else if (strpos($getData[15], 'Keine HBG - Nummer nicht vergeben') !== false) {
			$sc4status = "STOPPED";
		} else if (strpos($getData[15], 'Keine HBG - haus steht noch nicht') !== false) {
			$sc4status = "STOPPED";
		} else if (strpos($getData[15], 'Keine HBG - Falsche Nummer') !== false) {
			$sc4status = "STOPPED";
		} else if (strpos($getData[15], 'Nummer nicht vergeben.') !== false) {
			$sc4status = "STOPPED";
		} else if (strpos($getData[15], 'Keine HBG - haus steht noch nicht') !== false) {
			$sc4status = "STOPPED";
		} else if (strpos($getData[15], 'falsche nr') !== false) {
			$sc4status = "STOPPED";
		} else if (strpos($getData[15], 'gekündigt') !== false) {
			$sc4status = "STOPPED";
		} else if (strpos($getData[15], 'Kunde Verweigert HBG') !== false) {
			$sc4status = "STOPPED";
		} else if (strpos($getData[15], 'Keine HBG -') !== false) {
			$sc4status = "STOPPED";
		} else if (strpos($getData[15], 'Keine HBG -') !== false) {
			$sc4status = "STOPPED";
		} else if (strpos($getData[15], 'Nummer nicht vergeben') !== false) {
			$sc4status = "STOPPED";
		} else if (strpos($getData[15], 'Kein HBG -') !== false) {
			$sc4status = "STOPPED";
		}

		if ($sc4status !== '') {
			$query = "UPDATE `scan4_homes` SET scan4_comment = '" . $comment . "', scan4_hbgdate = '" . $hbgdate . "',`scan4_status`='" . $sc4status . "',`anruf1` ='" . $anruf1 . "', `anruf2` ='" . $anruf2 . "', `anruf3`='" . $anruf3 . "',`anruf4`='" . $anruf4 . "',`anruf5`='" . $anruf5 . "',`briefkasten`='" . $postsend . "', `emailsend`='" . $mailsend . "',`rnc_code`='" . $code . "',`scan4_added` = '" . $adddate . "' WHERE `homeid` = '" . $homeid . "'";
			$query = "UPDATE `scan4_homes` SET scan4_comment = '" . $comment . "',";
			if ($hbgdate !== '') {
				$query .= "scan4_hbgdate = '" . $hbgdate . "',`scan4_status`='" . $sc4status . "',`anruf1` ='" . $anruf1 . "', `anruf2` ='" . $anruf2 . "', `anruf3`='" . $anruf3 . "',`anruf4`='" . $anruf4 . "',`anruf5`='" . $anruf5 . "',`briefkasten`='" . $postsend . "', `emailsend`='" . $mailsend . "',`rnc_code`='" . $code . "',`scan4_added` = '" . $adddate . "' WHERE `homeid` = '" . $homeid . "'";
			} else {
				$query .= "`scan4_status`='" . $sc4status . "',`anruf1` ='" . $anruf1 . "', `anruf2` ='" . $anruf2 . "', `anruf3`='" . $anruf3 . "',`anruf4`='" . $anruf4 . "',`anruf5`='" . $anruf5 . "',`briefkasten`='" . $postsend . "', `emailsend`='" . $mailsend . "',`rnc_code`='" . $code . "',`scan4_added` = '" . $adddate . "' WHERE `homeid` = '" . $homeid . "'";
			}
		} else {
			$query = "UPDATE `scan4_homes` SET scan4_comment = '" . $comment . "', scan4_hbgdate = '" . $hbgdate . "',`anruf1` ='" . $anruf1 . "', `anruf2` ='" . $anruf2 . "', `anruf3`='" . $anruf3 . "',`anruf4`='" . $anruf4 . "',`anruf5`='" . $anruf5 . "',`briefkasten`='" . $postsend . "', `emailsend`='" . $mailsend . "',`rnc_code`='" . $code . "',`scan4_added` = '" . $adddate . "' WHERE `homeid` = '" . $homeid . "'";
		}
		mysqli_query($conn, $query) or die(mysqli_error($conn));
		$int_update++;
	} // ende while loop



	echo "<p>gesamt: $inter</p>";
	/*
    echo "<pre>";
    print_r($a_print);
    echo "</pre>";
    /*

    echo "<pre>";
    print_r($a_citylist);
    echo "</pre>";
  */

	// check if city is not in the list and list them

	$int = 0;

	if ($int === 1) {
		echo "<p> Es wurde <b>$int</b> Ort gefunden der <b>nicht</b> zugeordnet werden konnte! </p>";
	} elseif ($int > 1) {
		echo "<p> Es wurden <b>$int</b> Orte gefunden die <b>nicht</b> zugeordnet werden konnten! </p>";
	}
	echo "<p> Einträge aktualisiert GVG: <b>$int_update</b></p>";

	//echo "full: " . $count;
	// Close opened CSV file



	fclose($csvFile);

	//echo $inter;


}


function upload_readwrite_sc4($file)
{
	$conn = dbconnect();
	// Open uploaded CSV file with read-only mode
	$csvFile = fopen($file, 'r');
	// Setup variables

	$int_new = 0;
	$int_update = 0;

	//echo "<pre>";
	//print_r($a_citylist);
	//echo "</pre>";
	// Parse data from CSV file line by line
	$count = 0;
	$inter = 0;
	$datum = date("Y-m-d");
	fgetcsv($csvFile, 100000, ","); // skip first row
	while (($getData = fgetcsv($csvFile, 100000, ";")) !== FALSE) {
		// Get row data
		$inter++;
		$anruf1 = "";
		$anruf2 = "";
		$anruf3 = "";
		$anruf4 = "";
		$anruf5 = "";
		$mailsend = "";
		$postsend = "";
		$code = "";
		$hbgdate = "";
		$adddate = '';



		$homeid = $getData[7];
		$anruf1 = $getData[21];
		if ($anruf1 !== "") $anruf1 = date("Y-m-d", strtotime($anruf1));
		$anruf2 = $getData[22];
		if ($anruf2 !== "") $anruf2 = date("Y-m-d", strtotime($anruf2));
		$anruf3 = $getData[23];
		if ($anruf3 !== "") $anruf3 = date("Y-m-d", strtotime($anruf3));
		$anruf4 = $getData[24];
		if ($anruf4 !== "") $anruf4 = date("Y-m-d", strtotime($anruf4));
		$anruf5 = $getData[25];
		if ($anruf5 !== "") $anruf5 = date("Y-m-d", strtotime($anruf5));
		$mailsend = $getData[27];
		$postsend = $getData[26];
		$code = $getData[29];


		$adddate = date("Y-m-d", strtotime($getData[28]));
		if (strlen($anruf5) > 2) {
			$sc4status = "STOPPED";
		}

		if (strlen($getData[19]) > 2) {
			$hbgdate = date("Y-m-d", strtotime($getData[19]));
			if (date('Y-m-d', strtotime($hbgdate . ' -2 days')) < date('Y-m-d')) {
				$sc4status = "OVERDUE";
			} else {
				$sc4status = "PLANNED";
			}
		} else {
			$hbgdate = '';
			$sc4status = "OPEN";
		}


		// ============================
		// UGG
		$hbgstatus = $getData[16];
		if ($hbgstatus !== '') {
			$hbgstatus = 'OPEN';
			$sc4status = '';
		} else if ($hbgstatus === 'Planned') {
			$hbgstatus = 'PLANNED';
			$sc4status = '';
		} else if ($hbgstatus === 'Done') {
			$hbgstatus = 'DONE';
			$sc4status = "DONE";
		} else if ($hbgstatus === 'HC_CANCELLED') {
			$hbgstatus = 'STOPPED';
			$sc4status = "STOPPED";
		} else if ($hbgstatus === 'HC_CANCELLED') {
			$hbgstatus = 'STOPPED';
			$sc4status = "STOPPED";
		}





		$hbgdate = $getData[19];
		if (strlen($hbgdate) > 2) {
			$hbgdate = date("Y-m-d", strtotime($hbgdate));
			if (date('Y-m-d', strtotime($hbgdate . ' -2 days')) < date('Y-m-d')) {
				$sc4status = "OVERDUE";
			}
		}


		$comment = $getData[20];
		$comment = str_replace("'", '', $comment);
		if (strpos($getData[20], 'HBG Excel') !== false || strpos($getData[20], 'HBG Screen') !== false) {
			$sc4status = "DONE CLOUD";
		} else if (strpos($getData[20], 'falsche Nummer') !== false) {
			$sc4status = "STOPPED";
		} else if (strpos($getData[20], 'nummer nicht vergeben') !== false) {
			$sc4status = "STOPPED";
		} else if (strpos($getData[20], 'Keine HBG - KD kündigt') !== false) {
			$sc4status = "STOPPED";
		} else if (strpos($getData[20], 'Keine HBG - Falsche Nummer') !== false) {
			$sc4status = "STOPPED";
		} else if (strpos($getData[20], 'Keine HBG - Nummer nicht vergeben') !== false) {
			$sc4status = "STOPPED";
		} else if (strpos($getData[20], 'Keine HBG - haus steht noch nicht') !== false) {
			$sc4status = "STOPPED";
		} else if (strpos($getData[20], 'Keine HBG - Falsche Nummer') !== false) {
			$sc4status = "STOPPED";
		} else if (strpos($getData[20], 'Nummer nicht vergeben.') !== false) {
			$sc4status = "STOPPED";
		} else if (strpos($getData[20], 'Keine HBG - haus steht noch nicht') !== false) {
			$sc4status = "STOPPED";
		} else if (strpos($getData[20], 'falsche nr') !== false) {
			$sc4status = "STOPPED";
		} else if (strpos($getData[20], 'gekündigt') !== false) {
			$sc4status = "STOPPED";
		} else if (strpos($getData[20], 'Kunde Verweigert HBG') !== false) {
			$sc4status = "STOPPED";
		} else if (strpos($getData[20], 'Keine HBG -') !== false) {
			$sc4status = "STOPPED";
		} else if (strpos($getData[20], 'Keine HBG -') !== false) {
			$sc4status = "STOPPED";
		} else if (strpos($getData[20], 'Nummer nicht vergeben') !== false) {
			$sc4status = "STOPPED";
		} else if (strpos($getData[20], 'Kein HBG -') !== false) {
			$sc4status = "STOPPED";
		}

		// ============================
		// DGF
		if (($getData[16] === "Tiefbau")) {
			$sc4status = "DONE";
			$hbgstatus = "DONE";
		} else if ($getData[16] === "Einblasen" || $getData[16] === "Spleiße" || $getData[16] === "Hausanschluss" || $getData[16] === "Arbeitsvorbereitung") {
			$sc4status = "DONE";
			$hbgstatus = "DONE";
		}
		if (($getData[13] === "Denied")) {
			$sc4status = "STOPPED";
			$hbgstatus = "STOPPED";
		}



		if ($sc4status !== '') {
			$query = "UPDATE `scan4_homes` SET scan4_comment = '" . $comment . "', scan4_hbgdate = '" . $hbgdate . "',`scan4_status`='" . $sc4status . "',`anruf1` ='" . $anruf1 . "', `anruf2` ='" . $anruf2 . "', `anruf3`='" . $anruf3 . "',`anruf4`='" . $anruf4 . "',`anruf5`='" . $anruf5 . "',`briefkasten`='" . $postsend . "', `emailsend`='" . $mailsend . "',`rnc_code`='" . $code . "',`scan4_added` = '" . $adddate . "' WHERE `homeid` = '" . $homeid . "'";
			$query = "UPDATE `scan4_homes` SET scan4_comment = '" . $comment . "',";
			if ($hbgdate !== '') {
				$query .= "scan4_hbgdate = '" . $hbgdate . "',`scan4_status`='" . $sc4status . "',`anruf1` ='" . $anruf1 . "', `anruf2` ='" . $anruf2 . "', `anruf3`='" . $anruf3 . "',`anruf4`='" . $anruf4 . "',`anruf5`='" . $anruf5 . "',`briefkasten`='" . $postsend . "', `emailsend`='" . $mailsend . "',`rnc_code`='" . $code . "',`scan4_added` = '" . $adddate . "' WHERE `homeid` = '" . $homeid . "'";
			} else {
				$query .= "`scan4_status`='" . $sc4status . "',`anruf1` ='" . $anruf1 . "', `anruf2` ='" . $anruf2 . "', `anruf3`='" . $anruf3 . "',`anruf4`='" . $anruf4 . "',`anruf5`='" . $anruf5 . "',`briefkasten`='" . $postsend . "', `emailsend`='" . $mailsend . "',`rnc_code`='" . $code . "',`scan4_added` = '" . $adddate . "' WHERE `homeid` = '" . $homeid . "'";
			}
		} else {
			$query = "UPDATE `scan4_homes` SET scan4_comment = '" . $comment . "', scan4_hbgdate = '" . $hbgdate . "',`anruf1` ='" . $anruf1 . "', `anruf2` ='" . $anruf2 . "', `anruf3`='" . $anruf3 . "',`anruf4`='" . $anruf4 . "',`anruf5`='" . $anruf5 . "',`briefkasten`='" . $postsend . "', `emailsend`='" . $mailsend . "',`rnc_code`='" . $code . "',`scan4_added` = '" . $adddate . "' WHERE `homeid` = '" . $homeid . "'";
		}
		mysqli_query($conn, $query) or die(mysqli_error($conn));
		$int_update++;
	} // ende while loop



	$int = 0;

	if ($int === 1) {
		echo "<p> Es wurde <b>$int</b> Ort gefunden der <b>nicht</b> zugeordnet werden konnte! </p>";
	} elseif ($int > 1) {
		echo "<p> Es wurden <b>$int</b> Orte gefunden die <b>nicht</b> zugeordnet werden konnten! </p>";
	}
	echo "<p> Einträge aktualisiert: <b>$int_update</b></p>";

	fclose($csvFile);
}


function upload_readwrite_nri($file)
{
	global $currentuser;
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

	$colindex['city'] = 5;
	$colindex['plz'] = 6;
	$colindex['street'] = 1;
	$colindex['streetnumber'] = 2;
	$colindex['streetnumberadd'] = 3;
	$colindex['dp'] = 7;
	$colindex['homeid'] = 8;
	$colindex['adressid'] = 9;
	$colindex['name'] = 10;
	$colindex['phone'] = 11;
	$colindex['email'] = 12;
	$colindex['workorder'] = 16;
	$colindex['hbgstatus'] = 18;
	$colindex['hbgplanned'] = 19;
	$colindex['systemstatus'] = 18;
	$colindex['hcstatus'] = 21;


	$conn = dbconnect();
	// Open uploaded CSV file with read-only mode
	$csvFile = fopen($file, 'r');



	$a_citylist = get_all_citys();
	$a_citynew = array();
	$a_cityupdate = array();
	$int_new = 0;
	$int_update = 0;
	$a_homeidlist = get_all_homeids();


	$count = 0;
	$inter = 0;
	$datum = date("Y-m-d");


	// read file line by line
	if (($handle = fopen($file, "r")) !== FALSE) {
		// read in the first row of the CSV file and discard it
		fgetcsv($handle, 100000, ",");
		// read in the rest of the CSV file to csvdata array
		while (($data = fgetcsv($handle, 100000, ";")) !== FALSE) {
			$csvdata[] = $data;
			$homeid = $data[8];
			$allhomeid[] = $homeid;
		}
		fclose($handle);
	} else {
		echo 'cant open file' . "\n";
		$lastError = error_get_last();
		echo "Error opening file: " . $lastError['message'];
	}

	$seen_ids = array();
	$duplicate_indexes = array();

	// loop through the CSV data and find duplicates
	foreach ($csvdata as $index => $item) {
		$id = $item[8];
		if (array_key_exists($id, $seen_ids)) {
			// We've seen this ID before, so we have a duplicate
			if (!array_key_exists($id, $duplicate_indexes)) {
				$duplicate_indexes[$id] = array($seen_ids[$id]);
			}
			$duplicate_indexes[$id][] = $index;
		} else {
			// This is a new ID, so we add it to our list of seen IDs
			$seen_ids[$id] = $index;
		}
	}

	$double_count = 0;
	// loop through the duplicates and extract the correct values from it
	foreach ($duplicate_indexes as $id => $indexes) {
		if (count($indexes) > 1) {
			$duplicates = array();
			foreach ($indexes as $index) {
				$csvdata[$index][$colindex['phone']] = str_replace(["\n", "\r", '/', '^', '+'], '',  $csvdata[$index][$colindex['phone']]);
				if ($csvdata[$index][$colindex['city']] !== 'null' && $csvdata[$index][$colindex['city']] !== '') {
					$summ_ids[$id]['city'] = $csvdata[$index][$colindex['city']];
				}
				if ($csvdata[$index][$colindex['street']] !== 'null' && $csvdata[$index][$colindex['street']] !== '') {
					$summ_ids[$id]['street'] = $csvdata[$index][$colindex['street']];
				}
				if ($csvdata[$index][$colindex['streetnumber']] !== 'null' && $csvdata[$index][$colindex['streetnumber']] !== '') {
					$summ_ids[$id]['streetnumber'] = $csvdata[$index][$colindex['streetnumber']];
				}
				if ($csvdata[$index][$colindex['plz']] !== 'null' && $csvdata[$index][$colindex['plz']] !== '') {
					$summ_ids[$id]['plz'] = $csvdata[$index][$colindex['plz']];
				}
				if ($csvdata[$index][$colindex['dp']] !== 'null' && $csvdata[$index][$colindex['dp']] !== '') {
					$summ_ids[$id]['dp'] = $csvdata[$index][$colindex['dp']];
				}
				if ($csvdata[$index][$colindex['homeid']] !== 'null' && $csvdata[$index][$colindex['homeid']] !== '') {
					$summ_ids[$id]['homeid'] = $csvdata[$index][$colindex['homeid']];
				}
				if ($csvdata[$index][$colindex['adressid']] !== 'null' && $csvdata[$index][$colindex['adressid']] !== '') {
					$summ_ids[$id]['adressid'] = $csvdata[$index][$colindex['adressid']];
				}
				if ($csvdata[$index][$colindex['name']] !== 'null' && $csvdata[$index][$colindex['name']] !== '') {
					$summ_ids[$id]['name'] = $csvdata[$index][$colindex['name']];
				}
				if ($csvdata[$index][$colindex['phone']] !== 'null' && $csvdata[$index][$colindex['phone']] !== '') {
					$summ_ids[$id]['phone'] = $csvdata[$index][$colindex['phone']];
				}
				if ($csvdata[$index][$colindex['email']] !== 'null' && $csvdata[$index][$colindex['email']] !== '') {
					$summ_ids[$id]['email'] = $csvdata[$index][$colindex['email']];
				}
				if ($csvdata[$index][$colindex['hbgstatus']] !== 'null' && $csvdata[$index][$colindex['hbgstatus']] !== '') {
					$summ_ids[$id]['hbgstatus'] = $csvdata[$index][$colindex['hbgstatus']];
				}
				if ($csvdata[$index][$colindex['hbgplanned']] !== 'null' && $csvdata[$index][$colindex['hbgplanned']] !== '') {
					$summ_ids[$id]['hbgplanned'] = $csvdata[$index][$colindex['hbgplanned']];
				}
				if ($csvdata[$index][$colindex['systemstatus']] !== 'null' && $csvdata[$index][$colindex['systemstatus']] !== '') {
					$summ_ids[$id]['systemstatus'] = $csvdata[$index][$colindex['systemstatus']];
				}
				if ($csvdata[$index][$colindex['hcstatus']] !== 'null' && $csvdata[$index][$colindex['hcstatus']] !== '') {
					$summ_ids[$id]['hcstatus'] = $csvdata[$index][$colindex['hcstatus']];
				}

				//echo 'name is ' . $csvdata[$index][10];
				//echo '</br>';
				$duplicates[] = $csvdata[$index];
			}
			//echo "Found " . count($duplicates) . " duplicates with ID; $id</br>";
			$double_count += count($duplicates);
		}
	}

	// remove the doubles from the csvdata
	foreach (array_keys($summ_ids) as $home_key) {
		// remove all sub-arrays from csvdata where the fourth element is equal to the home key
		foreach ($csvdata as $key => $subarray) {
			if ($subarray[$colindex['homeid']] == $home_key) {
				unset($csvdata[$key]);
			}
		}
	}

	// reindex the csvdata array
	// push the summ_ids array to the end of the csvdata array
	$count = count($csvdata);
	$int = 0;
	foreach ($summ_ids as $row) {
		$int++;
		$csvdata[$count + $int] = $row;
	}

	$carrier = 'UGG';
	$scan4Added = date('Y-m-d');


	foreach ($csvdata as $getData) {

		// Get row data
		$inter++;
		$city = "";
		$street = "";
		$streetNumber = "";
		$streetNumberAdd = "";
		$unit = "";
		$plz = "";
		$dp = "";
		$homeid = "";
		$vorname = "";
		$nachname = "";
		$phone1 = "";
		$mail = "";
		$phone2 = "";
		$status = "";
		$sc4status = "";
		$hbgstatus = "";
		$systemstatus = '';
		$workorder = '';


		$city = $getData[$colindex['city']];
		$street = $getData[$colindex['street']];
		$street = str_replace("'", "", $street);
		$streetNumber = $getData[$colindex['streetnumber']];
		$streetNumberAdd = $getData[$colindex['streetnumberadd']];
		$plz = $getData[$colindex['plz']];
		$dp = $getData[$colindex['dp']];
		$homeid = $getData[$colindex['homeid']];
		$unit = substr($homeid, -1);
		$adressid = $getData[$colindex['adressid']];
		$workorder = $getData[$colindex['workorder']];

		$name = explode(' ', $getData[10]);
		$vorname = $name[0];
		$nachname = implode(' ', array_slice($name, 1)); // This joins the remaining parts back together


		$phone = $getData[11];
		$phone = str_replace(["\n", "\r", '/', '^', '+'], '',  $phone);
		$xplode = explode(" ", $phone);
		if (isset($xplode[0])) {
			$phone1 = $xplode[0];
		} else {
			$phone1 = $getData[11];
		}
		if (isset($xplode[1])) $phone2 = $xplode[1];

		if (substr($phone1, 0, 1) !== '0') $phone1 = '0' . $phone1;
		if (substr($phone2, 0, 1) !== '0') $phone2 = '0' . $phone2;



		$mail = $getData[12];
		$ispOrder = $getData[13];
		$ordercon = $getData[14];
		$hbgstatus = $getData[18];

		$systemstatus = $getData[18];
		if ($getData[18] === "Planned") {
			$hbgstatus = "PLANNED";
			$sc4status = "OPEN";
		} else if ($getData[18] === "Done") {
			$sc4status = "DONE";
			$hbgstatus = "DONE";
		} else if ($getData[18] === "") {
			$sc4status = "OPEN";
			$hbgstatus = "OPEN";
		} else if ($getData[18] === "Stopped") {
			$sc4status = "STOPPED";
			$hbgstatus = "STOPPED";
		} else {
			$sc4status = "STOPPED @";
			$hbgstatus = "STOPPED @";
		}
		if ($getData[21] === "CANCELLED") {
			$sc4status = "STOPPED";
			$hbgstatus = "STOPPED";
		}

		if ($getData[19] !== '') {
			$hbgplanned = date("Y-m-d", strtotime($getData[19]));
		} else {
			$hbgplanned = '';
		}

		$hbgdone = $getData[20]; // This is the date in d.m.Y format

		$dateObject = DateTime::createFromFormat('d.m.Y', $hbgdone);
		if ($dateObject) {
			// Format the date to Y-m-d and assign it back to $hbgdone
			$hbgdone = $dateObject->format('Y-m-d');
		} else {
			// If the date is not valid, set $hbgdone to NULL
			$hbgdone = NULL;
		}


		if (in_array($city, array_column($a_citylist, 'city'))) {
			$carrier = $a_citylist[$city]['carrier'];
			$client = $a_citylist[$city]['client'];

			if (!in_array($city, $a_cityupdate)) $a_cityupdate[] = $city;

			if (!in_array($homeid, $a_homeidlist)) {

				$int_new++;
				//echo "INSERT IGNORE INTO `scan4_homes`(`client`, `carrier`, `street`, `streetnumber`, `streetnumberadd`, `unit`, `city`, `plz`, `dpnumber`, `homeid`, `adressid`, `firstname`, `lastname`, `phone1`, `phone2`, `email`, `isporder`, `hbg_status`, `hbg_plandate`,`hbg_date`, `scan4_status`, `scan4_added`, `workordercode`) VALUES ('{$client}', '{$carrier}', '{$street}', '{$streetNumber}', '{$streetNumberAdd}', '{$unit}', '{$city}', '{$plz}', '{$dp}', '{$homeid}', '{$adressid}', '{$vorname}', '{$nachname}', '{$phone1}', '{$phone2}', '{$mail}', '{$ispOrder}', '{$hbgstatus}', '{$hbgplanned}', '{$hbgdone}', '{$sc4status}', '{$scan4Added}', '{$workorder}')";

				$stmt = $conn->prepare("INSERT IGNORE INTO `scan4_homes`(`client`, `carrier`, `street`, `streetnumber`, `streetnumberadd`, `unit`, `city`, `plz`, `dpnumber`, `homeid`, `adressid`, `firstname`, `lastname`, `phone1`, `phone2`, `email`, `isporder`, `hbg_status`, `hbg_plandate`,`hbg_date`, `scan4_status`, `scan4_added`, `workordercode`) VALUES (?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
				$stmt->bind_param("sssssssssssssssssssssss", $client, $carrier, $street, $streetNumber, $streetNumberAdd, $unit, $city, $plz, $dp, $homeid, $adressid, $vorname, $nachname, $phone1, $phone2, $mail, $ispOrder, $hbgstatus, $hbgplanned, $hbgdone, $sc4status, $scan4Added, $workorder);
				$stmt->execute();

				// Check for errors
				if ($stmt->error) {
					echo "Error: " . $stmt->error;
				}
			} else {
				$int_update++;

				$fields = [
					'firstname'       => $vorname,
					'lastname'        => $nachname,
					'street'          => $street,
					'streetnumber'    => $streetNumber,
					'streetnumberadd' => $streetNumberAdd,
					'unit'            => $unit,
					'phone1'          => $phone1,
					'phone2'          => $phone2,
					'hbg_status'      => $hbgstatus,
					'hbg_date'     	  => $hbgdone,
					'email'           => $mail,
					'dpnumber'        => $dp,
					'workordercode'	  => $workorder,
				];

				homeshistory($conn, $currentuser, $homeid, $fields);

				//echo "UPDATE `scan4_homes` SET plz = '{$plz}', carrier = '{$carrier}', client = '{$client}', firstname = '{$vorname}', lastname = '{$nachname}', street = '{$street}', streetnumber = '{$streetNumber}', streetnumberadd = '{$streetNumberAdd}', unit = '{$unit}', dpnumber = '{$dp}', phone1 = '{$phone1}', phone2 = '{$phone2}', email = '{$mail}', hbg_status = '{$hbgstatus}', hbg_plandate = '{$hbgplanned}', hbg_date = '{$hbgdone}', system_status = '{$systemstatus}', workordercode = '{$workorder}' WHERE `homeid` = '{$homeid}'";

				$stmt = $conn->prepare("UPDATE `scan4_homes` SET plz = ?, carrier = ?, client = ?, firstname = ?, lastname = ?, street = ?, streetnumber = ?, streetnumberadd = ?, unit = ?, dpnumber = ?, phone1 = ?, phone2 = ?, email = ?, hbg_status = ?, hbg_plandate = ?, hbg_date = ?, system_status = ?, workordercode = ? WHERE `homeid` = ?");
				$stmt->bind_param("sssssssssssssssssss", $plz, $carrier, $client, $vorname, $nachname, $street, $streetNumber, $streetNumberAdd, $unit, $dp, $phone1, $phone2, $mail, $hbgstatus, $hbgplanned, $hbgdone, $systemstatus, $workorder, $homeid);
				$stmt->execute();

				$stmt = $conn->prepare("UPDATE `scan4_citylist` SET `plz` = ? WHERE city = ?");
				$stmt->bind_param("ss", $plz, $city);
				$stmt->execute();
			}
		} else {
			if (!in_array($city, $a_citynew)) {
				$a_citynew[] = $city;
			}
		}
	}


	foreach ($a_cityupdate as $city) {
		upd_uploaddate($city);
	}
	// check if city is not in the list and list them
	$html = '<div class="table-wrapper">
	<table class="table table-nocity">
			<thead>
				<tr>
					<th>Nicht zugeordnet</th>
				</tr>                
			</thead>
			<tbody class="table-body-font-size">';
	$int = 0;
	foreach ($a_citynew as $value) {

		if ($value !== "" && !(in_array($value, $a_citylist))) {
			$html .= '<tr><td> ' . $value . '</td></tr>';
			$int++;
		}
	}
	$html .= ' </tbody></table></div>';
	echo $html;
	if ($int === 1) {
		echo "<p> Es wurde <b>$int</b> Ort gefunden der <b>nicht</b> zugeordnet werden konnte! </p>";
	} elseif ($int > 1) {
		echo "<p> Es wurden <b>$int</b> Orte gefunden die <b>nicht</b> zugeordnet werden konnten! </p>";
	}
	echo "<p> Neue Einträge UGG: <b>$int_new</b></p>";
	echo "<p> Einträge aktualisiert UGG: <b>$int_update</b></p>";

	//echo "full: " . $count;
	// Close opened CSV file



	fclose($csvFile);

	//echo $inter;


}



function upload_readwrite_förder($file)
{
	$conn = dbconnect();
	// Open uploaded CSV file with read-only mode
	$csvFile = fopen($file, 'r');

	$a_citylist = get_all_citys();
	$a_citynew = array();
	$a_cityupdate = array();
	$int_new = 0;
	$int_update = 0;
	$a_homeidlist = get_all_homeids();
	//echo "<pre>";
	//print_r($a_citylist);
	//echo "</pre>";
	// Parse data from CSV file line by line
	$count = 0;
	$inter = 0;
	$datum = date("Y-m-d");

	fgetcsv($csvFile, 100000, ","); // skip first row
	while (($getData = fgetcsv($csvFile, 100000, ";")) !== FALSE) {
		// Get row data
		$inter++;
		$city = "";
		$street = "";
		$streetNumber = "";
		$streetNuN = "";
		$unit = "";
		$plz = "";
		$dp = "";
		$homeid = "";
		$vorname = "";
		$nachname = "";
		$phone1 = "";
		$mail = "";
		$phone2 = "";
		$status = "";
		$sc4status = "";
		$hbgstatus = "";

		if ($inter < 2) {
			$a_print[] = $getData;
		}

		$city = $getData[5];
		$street = $getData[1];
		$street = str_replace("'", "", $street);
		$streetNumber = $getData[2];
		$streetNuN = $getData[3];
		$plz = $getData[6];
		$dp = $getData[7];
		$homeid = $getData[8];
		$adressid = $getData[9];

		$a_name = explode(" ", $getData[10], 2);
		$vorname = $a_name[0];
		if (isset($a_name[0])) {
			$vorname = $a_name[0];
		}
		if (isset($a_name[1])) {
			$nachname = $a_name[1];
		}
		$vorname = str_replace("'", "", $vorname);
		$nachname = str_replace("'", "", $nachname);

		$phone1 = $getData[11];

		$mail = $getData[12];

		$orderisp = $getData[13];
		$ordercon = $getData[14];

		$hbgstatus = $getData[18];

		if ($getData[18] === "Planned") {
			$sc4status = "PLANNED";
			$hbgstatus = "PLANNED";
		} else if ($getData[18] === "Done") {
			$sc4status = "DONE";
			$hbgstatus = "DONE";
		} else if ($getData[18] === "") {
			$sc4status = "OPEN";
			$hbgstatus = "OPEN";
		} else if ($getData[18] === "Stopped") {
			$sc4status = "STOPPED";
			$hbgstatus = "OPEN";
		} else {
			$sc4status = "STOPPED @";
			$hbgstatus = "STOPPED @";
		}
		if ($getData[21] === "CANCELLED") {
			$sc4status = "STOPPED";
			$hbgstatus = "STOPPED";
		}

		$hbgplanned = $getData[19];
		$hbgdone = $getData[20];


		if (in_array($city, array_column($a_citylist, 'city'))) {
			$carrier = $a_citylist[$city]['client'];

			if (!in_array($city, $a_cityupdate)) $a_cityupdate[] = $city; // add city for update date in city table
			if (!(in_array($homeid, $a_homeidlist))) { // Check if homeid is new
				$int_new++;
				$query = "INSERT IGNORE INTO `scan4_homes`(`client`, carrier ,`street`, `streetnumber`, `streetnumberadd`, `unit`, `city`, `plz`, `dpnumber`, `homeid`, `adressid`, `firstname`, `lastname`, `phone1`, `phone2`, `email`,`isporder`, `hbg_status`, `hbg_plandate`,`scan4_status`,`scan4_added`) VALUES ('" . $carrier . "', 'UGG','" . $street . "','" . $streetNumber . "','" . $streetNuN . "','" . $unit . "','" . $city . "','" . $plz . "', '" . $dp . "','" . $homeid . "','" . $adressid . "','" . $vorname . "','" . $nachname . "','" . $phone1 . "','" . $phone2 . "', '" . $mail . "', '" . $orderisp . "' ,'" . $hbgstatus . "', '" . $hbgplanned . "', '" . $sc4status . "' ,'" . $datum . "')";
				//echo $query . "</br>";
				mysqli_query($conn, $query) or die(mysqli_error($conn));
			} else {
				$query = "UPDATE `scan4_homes` SET `firstname` ='" . $vorname . "', `lastname` ='" . $nachname . "', `street`='" . $street . "',`streetnumber`='" . $streetNumber . "',`streetnumberadd`='" . $streetNuN . "',`unit`='" . $unit . "', `dpnumber`='" . $dp . "',`phone1`='" . $phone1 . "',`phone2`='" . $phone2 . "',`email`='" . $mail . "',`hbg_status`='" . $hbgstatus . "' WHERE `homeid` = '" . $homeid . "'";
				mysqli_query($conn, $query) or die(mysqli_error($conn));
				$int_update++;
			}
		} else {
			if (!(in_array($city, $a_citynew))) {
				$a_citynew[] = $city;
			}
		}
	} // ende while loop

	//echo "<p>gesamt: $inter</p>";
	/*
    echo "<pre>";
    print_r($a_print);
    echo "</pre>";
  

    echo "<pre>";
    print_r($a_citylist);
    echo "</pre>";
  */
	foreach ($a_cityupdate as $city) {
		upd_uploaddate($city);
	}
	// check if city is not in the list and list them
	$html = '<div class="table-wrapper">
	<table class="table table-nocity">
			<thead>
				<tr>
					<th>Nicht zugeordnet</th>
				</tr>                
			</thead>
			<tbody class="table-body-font-size">';
	$int = 0;
	foreach ($a_citynew as $value) {

		if ($value !== "" && !(in_array($value, $a_citylist))) {
			$html .= '<tr><td> ' . $value . '</td></tr>';
			$int++;
		}
	}
	$html .= ' </tbody></table></div>';
	echo $html;
	if ($int === 1) {
		echo "<p> Es wurde <b>$int</b> Ort gefunden der <b>nicht</b> zugeordnet werden konnte! </p>";
	} elseif ($int > 1) {
		echo "<p> Es wurden <b>$int</b> Orte gefunden die <b>nicht</b> zugeordnet werden konnten! </p>";
	}
	echo "<p> Neue Einträge UGG: <b>$int_new</b></p>";
	echo "<p> Einträge aktualisiert UGG: <b>$int_update</b></p>";

	//echo "full: " . $count;
	// Close opened CSV file



	fclose($csvFile);

	//echo $inter;


}



function upload_readwrite_hbgcheck($file)
{
	$conn = dbconnect();
	// Open uploaded CSV file with read-only mode
	$csvFile = fopen($file, 'r');
	// Setup variables

	$int_new = 0;
	$int_update = 0;

	//echo "<pre>";
	//print_r($a_citylist);
	//echo "</pre>";
	// Parse data from CSV file line by line
	$count = 0;
	$inter = 0;
	$datum = date("Y-m-d");
	fgetcsv($csvFile, 100000, ","); // skip first row
	$todaydate = date('Y-m-d');
	while (($getData = fgetcsv($csvFile, 100000, ";")) !== FALSE) {
		// Get row data
		$inter++;
		$newstate = "";
		$newdate = "";
		$newcomment = "";
		$homeid = '';


		$homeid = $getData[0];
		$newstate = $getData[12];
		$newdate = $getData[13];
		$newcomment = $getData[14];


		/*
		if (strlen($newcomment) > 3) {
			$query = "INSERT INTO `scan4_calls`(`call_count`, `call_date`, `call_time`, `call_user`, `homeid`, `result`, `comment`) VALUES ('0','".$todaydate."','00-00-00','Anrufliste','" . $homeid . "','import','" . $newcomment . "')";
			mysqli_query($conn, $query) or die(mysqli_error($conn));
		} */



		if ($newdate !== '') {
			$query = "UPDATE `scan4_homes` SET scan4_hbgdate = '" . $newdate . "',`scan4_status`='" . $newstate . "',`anruf1` = NULL, `anruf2` = NULL, `anruf3`= NULL,`anruf4`= NULL,`anruf5`= NULL WHERE `homeid` = '" . $homeid . "'";
		} else {
			$query = "UPDATE `scan4_homes` SET `scan4_status`='" . $newstate . "',`anruf1` = NULL, `anruf2` = NULL, `anruf3`= NULL,`anruf4`= NULL,`anruf5`= NULL WHERE `homeid` = '" . $homeid . "'";
		}
		echo $query;
		mysqli_query($conn, $query) or die(mysqli_error($conn));
		$int_update++;
	} // ende while loop




	echo "<p> Einträge aktualisiert: <b>$int_update</b></p>";

	fclose($csvFile);
}


function upd_uploaddate($city)
{
	$date = date('Y-m-d');
	$conn = dbconnect();
	$query = "UPDATE `scan4_citylist` SET `lastimport`='" . $date . "' WHERE city = '" . $city . "'";
	mysqli_query($conn, $query);
	$conn->close();
}


function upd_tablecheck()
{
	$date = date('Y-m-d');
	$conn = dbconnect();
	$a_array = array();

	$query = "SELECT homeid,phone1,phone2,scan4_hbgdate,scan4_comment FROM `scan4_homes` WHERE 1";
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_row()) {
			$a_array[] = array("homeid" => $row[0], "phone1" => $row[1], "phone2" => $row[2], "hbgdate" => $row[3], "comment" => $row[4]);
		}
		$result->free_result();
	}

	$length = count($a_array);
	for ($i = 0; $i < $length; $i++) {
		$phone1 = '';
		$phone2 = '';
		$hbgdate = '';
		$phone1 = str_replace([' ', "\n", "\r", '/', '^', '+49'], '',  $a_array[$i]['phone1']);
		$phone2 = str_replace([' ', "\n", "\r", '/', '^', '+49'], '', $a_array[$i]['phone2']);
		if (strlen($phone1) > 2) {
			if (substr($phone1, 0, 1) === '0') $phone1 = substr($phone1, 1);
			$phone1 = '0' . $phone1;
		}
		if (strlen($phone2) > 2) {
			if (substr($phone2, 0, 1) === '0') $phone2 = substr($phone2, 1);
			$phone2 = '0' . $phone2;
		}
		if (strlen($hbgdate) > 2) {
			$hbgdate = date("Y-m-d", strtotime($hbgdate));
			if (date('Y-m-d', strtotime($hbgdate . ' -2 days')) < date('Y-m-d')) {
				$sc4status = "OVERDUE";
			}
		}

		if ($sc4status !== 'OVERDUE') {
			$query = "UPDATE `scan4_homes` SET `phone1`='" . $phone1 . "', `phone2` = '" . $phone2 . "' WHERE homeid = '" . $a_array[$i]['homeid'] . "'";
		} else {
			$query = "UPDATE `scan4_homes` SET `phone1`='" . $phone1 . "', `phone2` = '" . $phone2 . "', scan4_status = '" . $sc4status . "' WHERE homeid = '" . $a_array[$i]['homeid'] . "'";
		}
		mysqli_query($conn, $query);

		/*
		if (strlen($a_array[$i]['comment']) > 3) {
			$query = "INSERT INTO `scan4_calls`(`call_count`, `call_date`, `call_time`, `call_user`, `homeid`, `result`, `comment`) VALUES ('0','2022-12-30','00-00-00','Anrufliste','" . $a_array[$i]['homeid'] . "','import','" . $a_array[$i]['comment'] . "')";
			mysqli_query($conn, $query) or die(mysqli_error($conn));
		}
		*/
	}


	// Cleanup
	/*
	$query = "UPDATE `scan4_homes` SET `phone1`= NULL WHERE phone1 = '';";
	mysqli_query($conn, $query) or die(mysqli_error($conn));
	$query = "UPDATE `scan4_homes` SET `phone2`= NULL WHERE phone2 = '';";
	mysqli_query($conn, $query) or die(mysqli_error($conn));
	$query = "UPDATE `scan4_homes` SET `scan4_hbgdate`= NULL WHERE scan4_hbgdate = '0000-00-00';";
	mysqli_query($conn, $query) or die(mysqli_error($conn));
	$query = "UPDATE `scan4_homes` SET `scan4_status`= 'DONE' WHERE hbg_status = 'DONE';";
	mysqli_query($conn, $query) or die(mysqli_error($conn));
	$query = "UPDATE `scan4_homes` SET `anruf1`= NULL WHERE anruf1 = '0000-00-00';";
	mysqli_query($conn, $query) or die(mysqli_error($conn));
	$query = "UPDATE `scan4_homes` SET `anruf2`= NULL WHERE anruf2 = '0000-00-00';";
	mysqli_query($conn, $query) or die(mysqli_error($conn));
	$query = "UPDATE `scan4_homes` SET `anruf3`= NULL WHERE anruf3 = '0000-00-00';";
	mysqli_query($conn, $query) or die(mysqli_error($conn));
	$query = "UPDATE `scan4_homes` SET `anruf4`= NULL WHERE anruf4 = '0000-00-00';";
	mysqli_query($conn, $query) or die(mysqli_error($conn));
	$query = "UPDATE `scan4_homes` SET `anruf5`= NULL WHERE anruf5 = '0000-00-00';";
	mysqli_query($conn, $query) or die(mysqli_error($conn));
	$query = "UPDATE `scan4_homes` SET `briefkasten`= NULL WHERE briefkasten = '0000-00-00';";
	mysqli_query($conn, $query) or die(mysqli_error($conn));
	$query = "UPDATE `scan4_homes` SET `emailsend`= NULL WHERE emailsend = '0000-00-00';";
	mysqli_query($conn, $query) or die(mysqli_error($conn));
	$query = "UPDATE `scan4_homes` SET `anruf1`= NULL WHERE anruf1 = '';";
	mysqli_query($conn, $query) or die(mysqli_error($conn));
	$query = "UPDATE `scan4_homes` SET `anruf2`= NULL WHERE anruf2 = '';";
	mysqli_query($conn, $query) or die(mysqli_error($conn));
	$query = "UPDATE `scan4_homes` SET `anruf3`= NULL WHERE anruf3 = '';";
	mysqli_query($conn, $query) or die(mysqli_error($conn));
	$query = "UPDATE `scan4_homes` SET `anruf4`= NULL WHERE anruf4 = '';";
	mysqli_query($conn, $query) or die(mysqli_error($conn));
	$query = "UPDATE `scan4_homes` SET `anruf5`= NULL WHERE anruf5 = '';";
	mysqli_query($conn, $query) or die(mysqli_error($conn));
	$query = "UPDATE `scan4_homes` SET `briefkasten`= NULL WHERE briefkasten = '';";
	mysqli_query($conn, $query) or die(mysqli_error($conn));
	$query = "UPDATE `scan4_homes` SET `emailsend`= NULL WHERE emailsend = '';";;
	mysqli_query($conn, $query) or die(mysqli_error($conn));
	$query = "UPDATE `scan4_homes` SET `scan4_status`= 'PENDING' WHERE anruf5 IS NOT NULL AND scan4_status = 'OPEN';";
	mysqli_query($conn, $query) or die(mysqli_error($conn));
	$query = "UPDATE `scan4_homes` SET `scan4_status`= 'DONE' WHERE hbg_status = 'DONE';";
	mysqli_query($conn, $query) or die(mysqli_error($conn));
*/



	$conn->close();
}






function upload_readwrite_nri_wave($file)
{

	global $currentuser;
	$colindex['city'] = 0;
	$colindex['street'] = 1;
	$colindex['streetnumber'] = 2;
	$colindex['streetnumberadd'] = 3;
	$colindex['dp'] = 4;
	$colindex['homeid'] = 5;
	$colindex['name'] = 6;
	$colindex['phone'] = 7;
	$colindex['email'] = 8;
	$colindex['hbgstatus'] = 11;
	$colindex['hbgplanned'] = 12;
	$colindex['systemstatus'] = 14;
	$colindex['hcstatus'] = 14;


	$conn = dbconnect();
	// Open uploaded CSV file with read-only mode
	$csvFile = fopen($file, 'r');



	$a_citylist = get_all_citys();
	$a_citynew = array();
	$a_cityupdate = array();
	$int_new = 0;
	$int_update = 0;
	$a_homeidlist = get_all_homeids();


	$count = 0;
	$inter = 0;
	$datum = date("Y-m-d");


	// read file line by line
	if (($handle = fopen($file, "r")) !== FALSE) {
		// read in the first row of the CSV file and discard it
		fgetcsv($handle, 100000, ",");
		// read in the rest of the CSV file to csvdata array
		while (($data = fgetcsv($handle, 100000, ";")) !== FALSE) {
			$csvdata[] = $data;
			$homeid = $data[8];
			$allhomeid[] = $homeid;
		}
		fclose($handle);
	} else {
		echo 'cant open file' . "\n";
		$lastError = error_get_last();
		echo "Error opening file: " . $lastError['message'];
	}


	foreach ($csvdata as $getData) {

		// Get row data
		$inter++;
		$city = "";
		$street = "";
		$streetNumber = "";
		$streetNuN = "";
		$unit = "";
		$plz = "";
		$dp = "";
		$homeid = "";
		$vorname = "";
		$nachname = "";
		$phone1 = "";
		$mail = "";
		$phone2 = "";
		$status = "";
		$sc4status = "";
		$hbgstatus = "";
		$systemstatus = '';

		if ($inter < 2) {
			$a_print[] = $getData;
		}

		$city = $getData[$colindex['city']];
		$city = preg_replace('/^[A-Z]+ - /', '', $city); // turn GUSNB - Gusenburg into Gusenburg
		$city = $city . ' W2';


		$street = $getData[$colindex['street']];
		$streetNumber = $getData[$colindex['streetnumber']];
		$streetNuN = $getData[$colindex['streetnumberadd']];
		$dp = $getData[$colindex['dp']];
		$homeid = $getData[$colindex['homeid']];
		$unit = substr($homeid, -1);


		$a_name = explode(" ", $getData[$colindex['name']], 2);
		$vorname = $a_name[0];
		if (isset($a_name[0])) {
			$vorname = $a_name[0];
		}
		if (isset($a_name[1])) {
			$nachname = $a_name[1];
		}
		$vorname = str_replace(["'", ' . ', '. ', ' .', '.'], "", $vorname);
		$nachname = str_replace(["'", ' . ', '. ', ' .', '.'], "", $nachname);


		$phone = $getData[$colindex['phone']];
		$phone = str_replace(["\n", "\r", '/', '^', '+', '-'], '',  $phone);
		$xplode = explode(" ", $phone);
		if (isset($xplode[0])) {
			$phone1 = $xplode[0];
		} else {
			$phone1 = $getData[$colindex['phone']];
		}
		if (isset($xplode[1])) $phone2 = $xplode[1];

		if (substr($phone1, 0, 1) !== '0') $phone1 = '0' . $phone1;
		if (substr($phone2, 0, 1) !== '0') $phone2 = '0' . $phone2;



		$mail = $getData[$colindex['email']];
		$hbgstatus = $getData[$colindex['hbgstatus']];

		$systemstatus = $getData[$colindex['systemstatus']];
		if ($getData[$colindex['hbgstatus']] === "Planned") {
			$hbgstatus = "PLANNED";
			$sc4status = "OPEN";
		} else if ($getData[$colindex['hbgstatus']] === "Done") {
			$sc4status = "DONE";
			$hbgstatus = "DONE";
		} else if ($getData[$colindex['hbgstatus']] === "") {
			$sc4status = "OPEN";
			$hbgstatus = "OPEN";
		} else if ($getData[$colindex['hbgstatus']] === "Stopped") {
			$sc4status = "STOPPED";
			$hbgstatus = "STOPPED";
		} else {
			$sc4status = "STOPPED @";
			$hbgstatus = "STOPPED @";
		}
		if ($getData[$colindex['hcstatus']] === "CANCELLED") {
			$sc4status = "STOPPED";
			$hbgstatus = "STOPPED";
		}

		if ($getData[$colindex['hbgplanned']] !== '') {
			$hbgplanned = date("Y-m-d", strtotime($getData[$colindex['hbgplanned']]));
		} else {
			$hbgplanned = '';
		}

		$sc4status = 'OPEN';
		if (in_array($city, array_column($a_citylist, 'city'))) {
			$carrier = $a_citylist[$city]['client'];

			if (!in_array($city, $a_cityupdate)) $a_cityupdate[] = $city; // add city for update date in city table
			if (!(in_array($homeid, $a_homeidlist))) { // Check if homeid is new
				$int_new++;
				$query = "INSERT IGNORE INTO `scan4_homes`(`client`, carrier ,`street`, `streetnumber`, `streetnumberadd`, `unit`, `city`, `plz`, `dpnumber`, `homeid`, `adressid`, `firstname`, `lastname`, `phone1`, `phone2`, `email`,`isporder`, `hbg_status`, `hbg_plandate`,`scan4_status`,`scan4_added`) 
				VALUES ('" . $carrier . "', 'UGG','" . $street . "','" . $streetNumber . "','" . $streetNuN . "','" . $unit . "','" . $city . "','" . $plz . "', '" . $dp . "','" . $homeid . "','','" . $vorname . "','" . $nachname . "','" . $phone1 . "','" . $phone2 . "', '" . $mail . "', '' ,'" . $hbgstatus . "', '" . $hbgplanned . "', '" . $sc4status . "' ,'" . $datum . "')";
				$result = mysqli_query($conn, $query);
			} else {


				$fields = [
					'firstname'       => $vorname,
					'lastname'        => $nachname,
					'street'          => $street,
					'streetnumber'    => $streetNumber,
					'streetnumberadd' => $streetNuN,
					'unit'            => $unit,
					'phone1'          => $phone1,
					'phone2'          => $phone2,
					'hbg_status'      => $hbgstatus,
					'email'           => $mail,
					'dpnumber'        => $dp,

				];

				homeshistory($conn, $currentuser, $homeid, $fields);


				$vorname = mysqli_real_escape_string($conn, $vorname);
				$nachname = mysqli_real_escape_string($conn, $nachname);
				$street = mysqli_real_escape_string($conn, $street);

				$query = "UPDATE `scan4_homes` SET city = '$city',plz = '$plz', carrier = '$carrier',`firstname` = '$vorname', `lastname` = '$nachname', `street` = '$street', `streetnumber` = '$streetNumber', `streetnumberadd` = '$streetNuN', `unit` = '$unit', `dpnumber` = '$dp', `phone1` = '$phone1', `phone2` = '$phone2', `email` = '$mail', `hbg_status` = '$hbgstatus', `hbg_plandate` = '$hbgplanned', `system_status` = '$systemstatus' WHERE `homeid` = '$homeid'";
				$result = mysqli_query($conn, $query);
				//echo "<br> $query <br>";

				$todaydate = date('Y-m-d');
				$newcomment = 'Wave2 // Gartenbohrung vorhanden. (Neue) HBG notwendig!';

				// Run a SELECT query to check if the row exists
				$checkResult = mysqli_query($conn, "SELECT * FROM `scan4_calls` WHERE `call_date` = '" . $todaydate . "' AND `homeid` = '" . $homeid . "' AND `comment` = '" . $newcomment . "'");

				// If the query returns a result, that means a row already exists
				if (mysqli_num_rows($checkResult) > 0) {
					echo "This row already exists!";
				} else {
					// If the row doesn't exist, perform the insert
					//mysqli_query($conn, "INSERT INTO `scan4_calls`(`call_count`, `call_date`, `call_time`, `call_user`, `homeid`, `result`, `comment`) VALUES ('0','" . $todaydate . "','00-00-00','Anrufliste','" . $homeid . "','import','" . $newcomment . "')") or die(mysqli_error($conn));
					//	echo "Row inserted!";
				}


				$int_update++;
			}
		} else {
			if (!(in_array($city, $a_citynew))) {
				$a_citynew[] = $city;
			}
		}
	}






	//echo "<p>gesamt: $inter</p>";
	/*
    echo "<pre>";
    print_r($a_print);
    echo "</pre>";
  

    echo "<pre>";
    print_r($a_citylist);
    echo "</pre>";
  */
	foreach ($a_cityupdate as $city) {
		upd_uploaddate($city);
	}
	// check if city is not in the list and list them
	$html = '<div class="table-wrapper">
	<table class="table table-nocity">
			<thead>
				<tr>
					<th>Nicht zugeordnet</th>
				</tr>                
			</thead>
			<tbody class="table-body-font-size">';
	$int = 0;
	foreach ($a_citynew as $value) {

		if ($value !== "" && !(in_array($value, $a_citylist))) {
			$html .= '<tr><td> ' . $value . '</td></tr>';
			$int++;
		}
	}
	$html .= ' </tbody></table></div>';
	echo $html;
	if ($int === 1) {
		echo "<p> Es wurde <b>$int</b> Ort gefunden der <b>nicht</b> zugeordnet werden konnte! </p>";
	} elseif ($int > 1) {
		echo "<p> Es wurden <b>$int</b> Orte gefunden die <b>nicht</b> zugeordnet werden konnten! </p>";
	}
	echo "<p> Neue Einträge UGG: <b>$int_new</b></p>";
	echo "<p> Einträge aktualisiert UGG: <b>$int_update</b></p>";

	//echo "full: " . $count;
	// Close opened CSV file



	fclose($csvFile);

	//echo $inter;


}








function upload_readwrite_MUDn($file)
{
	$colindex['city'] = 2;
	$colindex['plz'] = 6;
	$colindex['street'] = 13;
	$colindex['streetnumber'] = 14;
	$colindex['streetnumberadd'] = 15;
	$colindex['dp'] = 12;
	$colindex['homeid'] = 3;
	$colindex['adressid'] = 9;
	$colindex['firstname'] = 7;
	$colindex['lastname'] = 8;
	$colindex['phone1'] = 9;
	$colindex['phone2'] = 10;
	$colindex['email'] = 11;
	$colindex['hbgstatus'] = 5;
	$colindex['hbgplanned'] = 19;
	$colindex['systemstatus'] = 18;
	$colindex['hcstatus'] = 21;

	global $currentuser;

	$conn = dbconnect();
	// Open uploaded CSV file with read-only mode
	$csvFile = fopen($file, 'r');



	$a_citylist = get_all_citys();
	$a_citynew = array();
	$a_cityupdate = array();
	$int_new = 0;
	$int_update = 0;
	$a_homeidlist = get_all_homeids();


	$count = 0;
	$inter = 0;
	$datum = date("Y-m-d");


	// read file line by line
	if (($handle = fopen($file, "r")) !== FALSE) {
		// read in the first row of the CSV file and discard it
		fgetcsv($handle, 100000, ",");
		// read in the rest of the CSV file to csvdata array
		while (($data = fgetcsv($handle, 100000, ";")) !== FALSE) {
			$csvdata[] = $data;
			$homeid = $data[8];
			$allhomeid[] = $homeid;
		}
		fclose($handle);
	} else {
		echo 'cant open file' . "\n";
		$lastError = error_get_last();
		echo "Error opening file: " . $lastError['message'];
	}

	$seen_ids = array();
	$duplicate_indexes = array();

	// loop through the CSV data and find duplicates
	foreach ($csvdata as $index => $item) {
		$id = $item[8];
		if (array_key_exists($id, $seen_ids)) {
			// We've seen this ID before, so we have a duplicate
			if (!array_key_exists($id, $duplicate_indexes)) {
				$duplicate_indexes[$id] = array($seen_ids[$id]);
			}
			$duplicate_indexes[$id][] = $index;
		} else {
			// This is a new ID, so we add it to our list of seen IDs
			$seen_ids[$id] = $index;
		}
	}

	$double_count = 0;
	// loop through the duplicates and extract the correct values from it
	foreach ($duplicate_indexes as $id => $indexes) {
		if (count($indexes) > 1) {
			$duplicates = array();
			foreach ($indexes as $index) {
				$csvdata[$index][$colindex['phone']] = str_replace(["\n", "\r", '/', '^', '+'], '',  $csvdata[$index][$colindex['phone']]);
				if ($csvdata[$index][$colindex['city']] !== 'null' && $csvdata[$index][$colindex['city']] !== '') {
					$summ_ids[$id]['city'] = $csvdata[$index][$colindex['city']];
				}
				if ($csvdata[$index][$colindex['street']] !== 'null' && $csvdata[$index][$colindex['street']] !== '') {
					$summ_ids[$id]['street'] = $csvdata[$index][$colindex['street']];
				}
				if ($csvdata[$index][$colindex['streetnumber']] !== 'null' && $csvdata[$index][$colindex['streetnumber']] !== '') {
					$summ_ids[$id]['streetnumber'] = $csvdata[$index][$colindex['streetnumber']];
				}
				if ($csvdata[$index][$colindex['plz']] !== 'null' && $csvdata[$index][$colindex['plz']] !== '') {
					$summ_ids[$id]['plz'] = $csvdata[$index][$colindex['plz']];
				}
				if ($csvdata[$index][$colindex['dp']] !== 'null' && $csvdata[$index][$colindex['dp']] !== '') {
					$summ_ids[$id]['dp'] = $csvdata[$index][$colindex['dp']];
				}
				if ($csvdata[$index][$colindex['homeid']] !== 'null' && $csvdata[$index][$colindex['homeid']] !== '') {
					$summ_ids[$id]['homeid'] = $csvdata[$index][$colindex['homeid']];
				}
				if ($csvdata[$index][$colindex['adressid']] !== 'null' && $csvdata[$index][$colindex['adressid']] !== '') {
					$summ_ids[$id]['adressid'] = $csvdata[$index][$colindex['adressid']];
				}
				if ($csvdata[$index][$colindex['name']] !== 'null' && $csvdata[$index][$colindex['name']] !== '') {
					$summ_ids[$id]['name'] = $csvdata[$index][$colindex['name']];
				}
				if ($csvdata[$index][$colindex['phone']] !== 'null' && $csvdata[$index][$colindex['phone']] !== '') {
					$summ_ids[$id]['phone'] = $csvdata[$index][$colindex['phone']];
				}
				if ($summ_ids[$id]['phone'] !== $summ_ids[$id]['phone1']) {
					$summ_ids[$id]['phone2'] = $summ_ids[$id]['phone1'];
					$summ_ids[$id]['phone1'] = $csvdata[$index][$colindex['phone']];
				}
				if ($csvdata[$index][$colindex['email']] !== 'null' && $csvdata[$index][$colindex['email']] !== '') {
					$summ_ids[$id]['email'] = $csvdata[$index][$colindex['email']];
				}
				if ($summ_ids[$id]['email'] !== $summ_ids[$id]['email1']) {
					$summ_ids[$id]['email2'] = $summ_ids[$id]['email1'];
					$summ_ids[$id]['email1'] = $csvdata[$index][$colindex['email']];
				}
				if ($csvdata[$index][$colindex['hbgstatus']] !== 'null' && $csvdata[$index][$colindex['hbgstatus']] !== '') {
					$summ_ids[$id]['hbgstatus'] = $csvdata[$index][$colindex['hbgstatus']];
				}
				if ($csvdata[$index][$colindex['hbgplanned']] !== 'null' && $csvdata[$index][$colindex['hbgplanned']] !== '') {
					$summ_ids[$id]['hbgplanned'] = $csvdata[$index][$colindex['hbgplanned']];
				}
				if ($csvdata[$index][$colindex['systemstatus']] !== 'null' && $csvdata[$index][$colindex['systemstatus']] !== '') {
					$summ_ids[$id]['systemstatus'] = $csvdata[$index][$colindex['systemstatus']];
				}
				if ($csvdata[$index][$colindex['hcstatus']] !== 'null' && $csvdata[$index][$colindex['hcstatus']] !== '') {
					$summ_ids[$id]['hcstatus'] = $csvdata[$index][$colindex['hcstatus']];
				}

				echo 'name is ' . $csvdata[$index][10];
				echo '</br>';
				$duplicates[] = $csvdata[$index];
			}
			echo "Found " . count($duplicates) . " duplicates with ID; $id</br>";
			$double_count += count($duplicates);
		}
	}

	// remove the doubles from the csvdata
	foreach (array_keys($summ_ids) as $home_key) {
		// remove all sub-arrays from csvdata where the fourth element is equal to the home key
		foreach ($csvdata as $key => $subarray) {
			if ($subarray[$colindex['homeid']] == $home_key) {
				unset($csvdata[$key]);
			}
		}
	}

	// reindex the csvdata array
	// push the summ_ids array to the end of the csvdata array
	$count = count($csvdata);
	$int = 0;
	foreach ($summ_ids as $row) {
		$int++;
		$csvdata[$count + $int] = $row;
	}
	/*
	echo '<pre>';
	echo 'csvdata';
	print_r($csvdata);
	echo '</pre>';
*/

	foreach ($csvdata as $getData) {

		// Get row data
		$inter++;
		$city = "";
		$street = "";
		$streetNumber = "";
		$streetNuN = "";
		$unit = "";
		$plz = "";
		$dp = "";
		$homeid = "";
		$vorname = "";
		$nachname = "";
		$phone1 = "";
		$mail = "";
		$phone2 = "";
		$status = "";
		$sc4status = "";
		$hbgstatus = "";
		$systemstatus = '';

		if ($inter < 2) {
			$a_print[] = $getData;
		}

		$city = $getData[$colindex['city']];
		$city = $city . ' MDU';
		$street = $getData[$colindex['street']];
		$street = str_replace("'", "", $street);
		$streetNumber = $getData[$colindex['streetnumber']];
		$streetNuN = $getData[$colindex['streetnumberadd']];
		$dp = $getData[$colindex['dp']];
		$homeid = $getData[$colindex['homeid']];
		$unit = substr($homeid, -1);


		$vorname = $getData[$colindex['firstname']];
		$nachname = $getData[$colindex['lastname']];


		$phone1 = $getData[$colindex['phone1']];
		$phone2 = $getData[$colindex['phone2']];

		$mail = $getData[$colindex['email']];


		$systemstatus = $getData[$colindex['hbgstatus']];
		if ($systemstatus === "Scheduling Survey") {
			$hbgstatus = "OPEN";
			$sc4status = "OPEN";
		} else if ($systemstatus === "Survey Done" || $systemstatus === 'Survey Validation') {
			$sc4status = "DONE";
			$hbgstatus = "DONE";
		} else if ($systemstatus === "") {
			$sc4status = "OPEN";
			$hbgstatus = "OPEN";
		} else if ($systemstatus === "Survey Scheduled") {
			$sc4status = "PLANNED";
			$hbgstatus = "PLANNED";
		} else {
			$sc4status = "STOPPED @";
			$hbgstatus = "STOPPED @";
		}

		if (in_array($city, array_column($a_citylist, 'city'))) {
			$carrier = $a_citylist[$city]['carrier'];
			$client = $a_citylist[$city]['client'];

			if (!in_array($city, $a_cityupdate)) $a_cityupdate[] = $city; // add city for update date in city table
			if (!(in_array($homeid, $a_homeidlist))) { // Check if homeid is new
				$int_new++;

				$query = "INSERT IGNORE INTO `scan4_homes`(`client`, carrier ,`street`, `streetnumber`, `streetnumberadd`, `unit`, `city`, `plz`, `dpnumber`, `homeid`, `firstname`, `lastname`, `phone1`, `phone2`, `email`, `hbg_status`,`scan4_status`,`scan4_added`) 
				VALUES ('" . $carrier . "', 'UGG','" . $street . "','" . $streetNumber . "','" . $streetNuN . "','" . $unit . "','" . $city . "','" . $plz . "', '" . $dp . "','" . $homeid . "','" . $vorname . "','" . $nachname . "','" . $phone1 . "','" . $phone2 . "', '" . $mail . "','" . $hbgstatus . "', '" . $sc4status . "' ,'" . $datum . "')";
				$result = mysqli_query($conn, $query);
			} else {

				$fields = [
					'firstname'       => $vorname,
					'lastname'        => $nachname,
					'street'          => $street,
					'streetnumber'    => $streetNumber,
					'streetnumberadd' => $streetNuN,
					'unit'            => $unit,
					'phone1'          => $phone1,
					'phone2'          => $phone2,
					'hbg_status'      => $hbgstatus,
					'email'           => $mail,
					'dpnumber'        => $dp,

				];

				homeshistory($conn, $currentuser, $homeid, $fields);

				$vorname = mysqli_real_escape_string($conn, $vorname);
				$nachname = mysqli_real_escape_string($conn, $nachname);
				$street = mysqli_real_escape_string($conn, $street);


				$query = "UPDATE `scan4_homes` SET plz = '$plz', carrier = '$carrier',client = '$client',`firstname` = '$vorname', `lastname` = '$nachname', `street` = '$street', `streetnumber` = '$streetNumber', `streetnumberadd` = '$streetNuN', `unit` = '$unit', `dpnumber` = '$dp', `phone1` = '$phone1', `phone2` = '$phone2', `email` = '$mail', `hbg_status` = '$hbgstatus', `system_status` = '$systemstatus' WHERE `homeid` = '$homeid'";
				$result = mysqli_query($conn, $query);
				/*
				if (mysqli_affected_rows($conn) > 0) {
				
					echo "Query successful, " . mysqli_affected_rows($conn) . " row(s) updated. <br>";
				} else if (mysqli_affected_rows($conn) === 0) {
					echo "Query successful, but no rows were affected. <br>";
					echo $query . "<br>";
				} else {
					echo "Query failed: " . mysqli_error($conn) . '<br>';
				}
				*/

				$stmt = $conn->prepare("UPDATE `scan4_citylist` SET `plz` = ? WHERE city = ?");
				$stmt->bind_param("ss", $plz, $city);
				$stmt->execute();

				$int_update++;
			}
		} else {
			if (!(in_array($city, $a_citynew))) {
				$a_citynew[] = $city;
			}
		}
	}






	//echo "<p>gesamt: $inter</p>";
	/*
    echo "<pre>";
    print_r($a_print);
    echo "</pre>";
  

    echo "<pre>";
    print_r($a_citylist);
    echo "</pre>";
  */
	foreach ($a_cityupdate as $city) {
		upd_uploaddate($city);
	}
	// check if city is not in the list and list them
	$html = '<div class="table-wrapper">
	<table class="table table-nocity">
			<thead>
				<tr>
					<th>Nicht zugeordnet</th>
				</tr>                
			</thead>
			<tbody class="table-body-font-size">';
	$int = 0;
	foreach ($a_citynew as $value) {

		if ($value !== "" && !(in_array($value, $a_citylist))) {
			$html .= '<tr><td> ' . $value . '</td></tr>';
			$int++;
		}
	}
	$html .= ' </tbody></table></div>';
	echo $html;
	if ($int === 1) {
		echo "<p> Es wurde <b>$int</b> Ort gefunden der <b>nicht</b> zugeordnet werden konnte! </p>";
	} elseif ($int > 1) {
		echo "<p> Es wurden <b>$int</b> Orte gefunden die <b>nicht</b> zugeordnet werden konnten! </p>";
	}
	echo "<p> Neue Einträge UGG: <b>$int_new</b></p>";
	echo "<p> Einträge aktualisiert UGG: <b>$int_update</b></p>";

	//echo "full: " . $count;
	// Close opened CSV file



	fclose($csvFile);

	//echo $inter;


}








function upload_readwrite_glasfaserplus($file)
{
	global $currentuser;

	$a_citylist = get_all_citys();
	$a_cityupdate = array();
	$a_homeidlist = get_all_homeids();
	$a_citynew = array();

	$conn = dbconnect();
	$csvFile = fopen($file, 'r');

	$int_new = 0;
	$int_update = 0;

	$today = date('Y-m-d');

	$column_headers = fgetcsv($csvFile, 100000, ';'); // read the columns headers
	$column_headers = array_map(function ($key) { // remove byte strings which are breaking the code
		return trim($key, "\xEF\xBB\xBF");
	}, $column_headers);

	// Ensure all column headers are unique
	$new_headers = [];
	foreach ($column_headers as $header) {
		$count = 0;
		while (in_array($header . ($count > 0 ? $count : ''), $new_headers)) {
			$count++;
		}
		$new_headers[] = $header . ($count > 0 ? $count : '');
	}
	$column_headers = $new_headers;

	while (($row = fgetcsv($csvFile, 100000, ';')) !== FALSE) {
		$header = array_combine($column_headers, $row);

		$homeid = $header['Bauauftrag-ID'];
		$street = $header['Straße'];
		$streetNumber = $header['Hausnummer'];
		$streetNumberAdd = $header['Hausnr. Z.'];
		$city = $header['Ort'];
		$name = $header['Kunden Name'];
		$parts = explode(' ', $name, 2); // split the name by the first space
		$firstname = $parts[0]; // first part is the first name
		$lastname = isset($parts[1]) ? $parts[1] : ''; // The second part is the last name (if it exists)
		$plz = $header['PLZ'];
		$dp = $header['NVT Gebiet'];
		$phone1 = $header['Telefon'];
		if (preg_match('/^[1-9]/', $phone1)) { // check if first char is 1-9 and add a 0 if so
			$phone1 = '0' . $phone1;
		}
		$phone2 = $header['Festnetz'];
		if (preg_match('/^[1-9]/', $phone2)) { // check if first char is 1-9 and add a 0 if so
			$phone2 = '0' . $phone2;
		}
		$mail = $header['Email'];
		$unit = ''; // unit not clear yet
		$status_system = 'OPEN'; // status not clear yet
		$status_scan4 = 'OPEN'; // status not clear yet
		$status_origin = 'OPEN'; // status not clear yet

		$owner_name =  $header['Eigentümer'];
		$owner_phone1 =  $header['Telefon1']; // in the headers we add the 1 to an existing column
		if (preg_match('/^[1-9]/', $owner_phone1)) { // check if first char is 1-9 and add a 0 if so
			$owner_phone1 = '0' . $owner_phone1;
		}

		$owner_mail =  $header['Email1']; // in the headers we add the 1 to an existing column

		$vorname = mysqli_real_escape_string($conn, $firstname);
		$nachname = mysqli_real_escape_string($conn, $lastname);
		$street = mysqli_real_escape_string($conn, $street);

		if (in_array($city, array_column($a_citylist, 'city'))) {
			$carrier = 'GlasfaserPlus';
			$client = 'FOL';

			if (!in_array($city, $a_cityupdate)) $a_cityupdate[] = $city; // add city for update date in city table
			if (!(in_array($homeid, $a_homeidlist))) { // Check if homeid is new
				$int_new++;

				$query = "INSERT IGNORE INTO scan4_homes(client, carrier, street, streetnumber, streetnumberadd, unit, city, plz, dpnumber, homeid, firstname, lastname, phone1, phone2, email, hbg_status, scan4_status, scan4_added, system_status, owner_name, owner_phone1, owner_mail)
				VALUES ('$carrier', 'UGG', '$street', '$streetNumber', '$streetNumberAdd', '$unit', '$city', '$plz', '$dp', '$homeid', '$firstname', '$lastname', '$phone1', '$phone2', '$mail', '$status_system', '$status_scan4', '$today', '$status_origin', '$owner_name', '$owner_phone1', '$owner_mail')";
				$result = mysqli_query($conn, $query);
			} else {


				$fields = [
					'firstname'       => $firstname,
					'lastname'        => $lastname,
					'street'          => $street,
					'streetnumber'    => $streetNumber,
					'streetnumberadd' => $streetNumberAdd,
					'unit'            => $unit,
					'phone1'          => $phone1,
					'phone2'          => $phone2,
					'hbg_status'      => $status_system,
					'email'           => $mail,
					'dpnumber'        => $dp,
					'owner_name'    	=> $owner_name,
					'owner_phone1' => $owner_phone1,
					'owner_mail'    	=> $owner_mail,
				];

				homeshistory($conn, $currentuser, $homeid, $fields);


				$query = "UPDATE `scan4_homes` SET plz = '$plz', carrier = '$carrier',client = '$client',`firstname` = '$vorname', `lastname` = '$nachname', `street` = '$street', `streetnumber` = '$streetNumber', `streetnumberadd` = '$streetNumberAdd',
				`unit` = '$unit', `dpnumber` = '$dp', `phone1` = '$phone1', `phone2` = '$phone2', `email` = '$mail', 
				`hbg_status` = '$status_system', `system_status` = '$status_origin', 
				owner_name = '$owner_name', owner_phone1 = '$owner_phone1', owner_mail = '$owner_mail' WHERE `homeid` = '$homeid'";
				$result = mysqli_query($conn, $query);

				//print_r($header) . '<br>';
				echo $query . '<br>';

				$stmt = $conn->prepare("UPDATE `scan4_citylist` SET `plz` = ? WHERE city = ?");
				$stmt->bind_param("ss", $plz, $city);
				$stmt->execute();

				$int_update++;
			}
		} else {
			if (!(in_array($city, $a_citynew))) {
				$a_citynew[] = $city;
			}
		}
	}


	$html = '<div class="table-wrapper">
	<table class="table table-nocity">
			<thead>
				<tr>
					<th>Nicht zugeordnet</th>
				</tr>                
			</thead>
			<tbody class="table-body-font-size">';
	$int = 0;
	foreach ($a_citynew as $value) {

		if ($value !== "" && !(in_array($value, $a_citylist))) {
			$html .= '<tr><td> ' . $value . '</td></tr>';
			$int++;
		}
	}
	$html .= ' </tbody></table></div>';
	echo $html;
	if ($int === 1) {
		echo "<p> Es wurde <b>$int</b> Ort gefunden der <b>nicht</b> zugeordnet werden konnte! </p>";
	} elseif ($int > 1) {
		echo "<p> Es wurden <b>$int</b> Orte gefunden die <b>nicht</b> zugeordnet werden konnten! </p>";
	}
	echo "<p> Neue Einträge UGG: <b>$int_new</b></p>";
	echo "<p> Einträge aktualisiert UGG: <b>$int_update</b></p>";


	//
}


function upload_DGF_Fördertgebiet($file)
{
	global $currentuser;

	$a_citylist = get_all_citys();
	$a_cityupdate = array();
	$a_homeidlist = get_all_homeids();
	$a_citynew = array();

	$conn = dbconnect();
	$csvFile = fopen($file, 'r');

	$int_new = 0;
	$int_update = 0;

	$today = date('Y-m-d');

	$column_headers = fgetcsv($csvFile, 100000, ';'); // read the columns headers
	$column_headers = array_map(function ($key) { // remove byte strings which are breaking the code
		return trim($key, "\xEF\xBB\xBF");
	}, $column_headers);

	// Ensure all column headers are unique
	$new_headers = [];
	foreach ($column_headers as $header) {
		$count = 0;
		while (in_array($header . ($count > 0 ? $count : ''), $new_headers)) {
			$count++;
		}
		$new_headers[] = $header . ($count > 0 ? $count : '');
	}
	$column_headers = $new_headers;

	while (($row = fgetcsv($csvFile, 100000, ';')) !== FALSE) {
		$header = array_combine($column_headers, $row);

		$homeid = $header['HOMEID'];
		$homeid = str_replace('--', '__', $homeid);
		$homeid = str_replace('-', '_', $homeid);
		$street = $header['STREET'];
		$streetNumber = $header['Number'];
		$streetNumberAdd = $header['Suffix'];
		$city = $header['Gemeinde'];
		$firstname = $header['FIRSTNAME'];
		$lastname = $header['NAME'];

		$plz = $header['PLZ'];
		$dp = $header['DP'];
		$phone1 = $header['PHONE'];
		if (preg_match('/^[1-9]/', $phone1)) { // check if first char is 1-9 and add a 0 if so
			$phone1 = '0' . $phone1;
		}
		$phone2 = $header['PHONE'];
		if (preg_match('/^[1-9]/', $phone2)) { // check if first char is 1-9 and add a 0 if so
			$phone2 = '0' . $phone2;
		}
		$mail = $header['MAIL'];
		$unit = $header['Unit'];
		$status_system = 'DONE';
		$status_scan4 = $header['HBG status'];
		if ($status_scan4 === '' || $status_scan4 === null) {
			$status_scan4 = 'DONE';
		} elseif ($status_scan4 === 'Pending HBG') {
			$status_scan4 = 'OPEN';
			$status_system = 'OPEN';
		}
		$status_origin = $header['HBG status']; // status not clear yet

		$owner_name =  $header['Eigentümer'];
		$owner_phone1 =  $header['Telefon1']; // in the headers we add the 1 to an existing column
		if (preg_match('/^[1-9]/', $owner_phone1)) { // check if first char is 1-9 and add a 0 if so
			$owner_phone1 = '0' . $owner_phone1;
		}

		$owner_mail =  $header['Email1']; // in the headers we add the 1 to an existing column

		$vorname = mysqli_real_escape_string($conn, $firstname);
		$nachname = mysqli_real_escape_string($conn, $lastname);
		$street = mysqli_real_escape_string($conn, $street);

		if (in_array($city, array_column($a_citylist, 'city'))) {
			$carrier = 'DGF';
			$client = 'Insyte';

			if (!in_array($city, $a_cityupdate)) $a_cityupdate[] = $city; // add city for update date in city table
			if (!(in_array($homeid, $a_homeidlist))) { // Check if homeid is new
				$int_new++;

				$query = "INSERT IGNORE INTO scan4_homes(client, carrier, street, streetnumber, streetnumberadd, unit, city, plz, dpnumber, homeid, firstname, lastname, phone1, phone2, email, hbg_status, scan4_status, scan4_added, system_status, owner_name, owner_phone1, owner_mail)
				VALUES ('$carrier', 'UGG', '$street', '$streetNumber', '$streetNumberAdd', '$unit', '$city', '$plz', '$dp', '$homeid', '$firstname', '$lastname', '$phone1', '$phone2', '$mail', '$status_system', '$status_scan4', '$today', '$status_origin', '$owner_name', '$owner_phone1', '$owner_mail')";
				$result = mysqli_query($conn, $query);
			} else {


				$fields = [
					'firstname'       => $firstname,
					'lastname'        => $lastname,
					'street'          => $street,
					'streetnumber'    => $streetNumber,
					'streetnumberadd' => $streetNumberAdd,
					'unit'            => $unit,
					'phone1'          => $phone1,
					'phone2'          => $phone2,
					'hbg_status'      => $status_system,
					'email'           => $mail,
					'dpnumber'        => $dp,
					'owner_name'    	=> $owner_name,
					'owner_phone1' => $owner_phone1,
					'owner_mail'    	=> $owner_mail,
				];

				homeshistory($conn, $currentuser, $homeid, $fields);



				$query = "UPDATE `scan4_homes` SET plz = '$plz', carrier = '$carrier',client = '$client',`firstname` = '$vorname', `lastname` = '$nachname', `street` = '$street', `streetnumber` = '$streetNumber', `streetnumberadd` = '$streetNumberAdd',
				`unit` = '$unit', `dpnumber` = '$dp', `phone1` = '$phone1', `phone2` = '$phone2', `email` = '$mail', 
				`hbg_status` = '$status_system', `system_status` = '$status_origin', 
				owner_name = '$owner_name', owner_phone1 = '$owner_phone1', owner_mail = '$owner_mail' WHERE `homeid` = '$homeid'";
				$result = mysqli_query($conn, $query);

				//print_r($header) . '<br>';
				echo $query . '<br>';

				$stmt = $conn->prepare("UPDATE `scan4_citylist` SET `plz` = ? WHERE city = ?");
				$stmt->bind_param("ss", $plz, $city);
				$stmt->execute();

				$int_update++;
			}
		} else {
			if (!(in_array($city, $a_citynew))) {
				$a_citynew[] = $city;
			}
		}
	}


	$html = '<div class="table-wrapper">
	<table class="table table-nocity">
			<thead>
				<tr>
					<th>Nicht zugeordnet</th>
				</tr>                
			</thead>
			<tbody class="table-body-font-size">';
	$int = 0;
	foreach ($a_citynew as $value) {

		if ($value !== "" && !(in_array($value, $a_citylist))) {
			$html .= '<tr><td> ' . $value . '</td></tr>';
			$int++;
		}
	}
	$html .= ' </tbody></table></div>';
	echo $html;
	if ($int === 1) {
		echo "<p> Es wurde <b>$int</b> Ort gefunden der <b>nicht</b> zugeordnet werden konnte! </p>";
	} elseif ($int > 1) {
		echo "<p> Es wurden <b>$int</b> Orte gefunden die <b>nicht</b> zugeordnet werden konnten! </p>";
	}
	echo "<p> Neue Einträge UGG: <b>$int_new</b></p>";
	echo "<p> Einträge aktualisiert UGG: <b>$int_update</b></p>";
}






function upload_reroll($file)
{


	$inter = 0;
	$csvFile = fopen($file, 'r');

	$array = array();

	$conn = dbconnect();
	fgetcsv($csvFile, 100000, ";"); // skip first row
	while (($getData = fgetcsv($csvFile, 100000, ";")) !== FALSE) {


		$homeid = $getData[0];
		$status = $getData[1];

		$query = "UPDATE `scan4_homes` SET scan4_status = '$status' WHERE `homeid` = '$homeid';";
		mysqli_query($conn, $query);
		//echo $query . '<br>';


		$inter++;
	}
	fclose($csvFile);

	echo 'Aktualisiert: ' . $inter . ' Datensätze';
	//echo '<pre>';
	//echo print_r($array);
	//echo '</pre>';
	$conn->close();
}




function upload_DGFneu($file)
{


	// 
	/*


alles mit straße importieren

keine straße kein import


UNIT nicht leer = UVS
unit leer = HVS
Kontakt person leer = kein vertrag
Kontakt person = vertrag



Status alles ist done / Hausbegehung ist OFFEN / tiefbau = 
Wenn Tiefbau & HV Standort HUP nicht leer = done
Wenn tiefbau & HV Standort HUP leer = Stopped



map zeigt nur UVS + relations hvs

wenn mehr als >= 5 dann UVS Stopped. Egal ob kontaktperson vorhanden oder nicht
-Comment = Keine HBG Notwenidg da mind. 5 parteien
-nur HVS anzeigen

wenn unter 5 UVS auf der Karte HVS nicht



HVS ist owner


wenn dp vorhanden = liste nicht leer überschreiben
wenn dp vorhanden = liste leer nicht überschreiben

*/

	global $currentuser;

	$fileToOutput = '/var/www/html/logfiles/output.txt';

	$a_citylist = get_all_citys();
	$a_cityupdate = array();
	$a_homeidlist = get_all_homeids();
	$a_citynew = array();


	$csvFile = fopen($file, 'r');

	$int_new = 0;
	$int_update = 0;

	$today = date('Y-m-d');

	$column_headers = fgetcsv($csvFile, 100000, ';'); // read the columns headers
	$column_headers = array_map(function ($key) { // remove byte strings which are breaking the code
		return trim($key, "\xEF\xBB\xBF");
	}, $column_headers);

	// Ensure all column headers are unique
	$new_headers = [];
	foreach ($column_headers as $header) {
		$count = 0;
		while (in_array($header . ($count > 0 ? $count : ''), $new_headers)) {
			$count++;
		}
		$new_headers[] = $header . ($count > 0 ? $count : '');
	}
	$column_headers = $new_headers;


	$conn = dbconnect();
	$conn->autocommit(FALSE);  // Start transaction

	$checkDpStmt = $conn->prepare("SELECT dpnumber FROM scan4_homes WHERE homeid = ?");
	$checkDpStmt->bind_param('s', $homeid);


	$updateDpStmt = $conn->prepare("UPDATE scan4_homes SET dpnumber = ? WHERE homeid = ?");
	$updateDpStmt->bind_param('ss', $dp, $homeid);

	$insertHomeStmt = $conn->prepare("INSERT IGNORE INTO scan4_homes(client, carrier, street, streetnumber, streetnumberadd, unit, city, plz, homeid, adressid, firstname, lastname, phone1, phone2, email, hbg_status, scan4_status, scan4_added, system_status, isporder, contractstatus, dpnumber) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
	$insertHomeStmt->bind_param("ssssssssssssssssssssss", $client, $carrier, $street, $streetNumber, $streetNumberAdd, $unit, $city, $plz, $homeid, $addressid, $firstname, $lastname, $phone1, $phone2, $mail, $status_system, $status_scan4, $today, $status_origin, $isporder, $contractStatus, $dp);

	$updateHomeStmt = $conn->prepare("UPDATE scan4_homes SET plz = ?, carrier = ?, client = ?, firstname = ?, lastname = ?, street = ?, streetnumber = ?, streetnumberadd = ?, unit = ?, phone1 = ?, phone2 = ?, email = ?, isporder = ?, contractstatus = ?, hbg_status = ?, system_status = ?, adressid = ?, dpnumber = ? WHERE homeid = ?");
	$updateHomeStmt->bind_param("sssssssssssssssssss", $plz, $carrier, $client, $firstname, $lastname, $street, $streetNumber, $streetNumberAdd, $unit, $phone1, $phone2, $mail, $isporder, $contractStatus, $status_system, $status_scan4, $addressid, $dp, $homeid);




	$updateCityListStmt = $conn->prepare("UPDATE scan4_citylist SET plz = ? WHERE city = ?");
	$updateCityListStmt->bind_param("ss", $plz, $city);



	$globalStartTime = microtime(true);
	$batchMessages = [];
	$batchSize = 20;
	$whileLoop = 0;
	while (($row = fgetcsv($csvFile, 200000, ';')) !== FALSE) {
		$roundStartTime = microtime(true);
		$whileLoop++;
		//echo "$whileLoop\n";

		$plz = null;
		$carrier = null;
		$client = null;
		$firstname = null;
		$lastname = null;
		$street = null;
		$streetNumber = null;
		$streetNumberAdd = null;
		$unit = null;
		$phone1 = null;
		$phone2 = null;
		$mail = null;
		$isporder = null;
		$contractStatus = null;
		$status_system = null;
		$status_origin = null;
		$addressid = null;
		$homeid = null;
		$city = null;
		$today = null;
		$status_scan4 = null;


		$header = array_combine($column_headers, $row);
		$street = $header['Straße'];
		// jump to the next row if the street is not valid. We only import if a street is given
		if (empty($street)) {
			continue;
		}

		$streetNumber = $header['Hausnummer'];
		$streetNumberAdd = $header['Hausnummer Zusatz'];
		$city = $header['Ort'];
		$plz = $header['Postleitzahl'];
		$plz = preg_replace('/\D/', '', $plz);
		if (strlen($plz) > 5) {
			$plz = substr($plz, 0, 5);
		}
		$dp = $header['DP'];
		$homeid = $header['WE_ID'];
		$addressid = extractAddressId($homeid);

		$unit = $header['Unit'];

		$phone1 = $header['Telefonnummer 1'];
		$phone2 = $header['Telefonnummer 2'];
		$mail = $header['E-Mail'];
		$name = $header['Kontaktperson'];

		$firstname = null;
		$lastname = null;
		// If the name contains ' und ', split it into individual contacts first
		if (strpos($name, ' und ') !== false) {
			$individualContacts = explode(' und ', $name);
			foreach ($individualContacts as $individualContact) {
				$result = splitName($individualContact);
				$firstname = $result['firstname'];
				$lastname = $result['lastname'];
			}
		} else {
			$result = splitName($name);
			$firstname = $result['firstname'];
			$lastname = $result['lastname'];
		}



		$HVStandort = $header['HV Standort HUP'];
		$status_origin =  $header['Status'];

		$status_scan4 = '';
		$status_system = '';

		if (!empty($status_origin)) {
			if ($status_origin == 'Hausbegehung') {
				$status_scan4 = 'OPEN';
				$status_system = 'OPEN';
			} elseif ($status_origin == 'Tiefbau') {
				if (!empty($HVStandort) && $HVStandort != '') {
					$status_scan4 = 'DONE';
					$status_system = 'DONE';
				} else {
					$status_scan4 = 'STOPPED';
					$status_system = 'STOPPED';
				}
			} else {
				$status_scan4 = 'DONE';
				$status_system = 'DONE';
			}
		}



		if (empty($unit)) {
			$contractStatus = 'HVS';
		} else {
			$contractStatus = 'UVS';
		}
		if (empty($name)) {
			$isporder = null;
		} else {
			$isporder = 'YES';
		}


		$firstname = mysqli_real_escape_string($conn, $firstname);
		$lastname = mysqli_real_escape_string($conn, $lastname);
		$street = mysqli_real_escape_string($conn, $street);

		if (in_array($city, array_column($a_citylist, 'city'))) {
			$carrier = 'DGF';
			$client = 'Insyte';






			if (!in_array($city, $a_cityupdate)) {
				$a_cityupdate[] = $city;
			} // add city for update date in city table
			if (!in_array($homeid, $a_homeidlist)) {
				$int_new++;

				if ($insertHomeStmt->execute() === FALSE) {
					//echo "Error inserting record: " . $conn->error . '<br>';
					//echo "Statement error: " . $insertHomeStmt->error . '<br>';

					$roundEndTime = microtime(true);
					$roundTimeTaken = $roundEndTime - $roundStartTime;
					$batchMessages[] = "Error inserting record: " . $conn->error . "\nStatement error: " . $insertHomeStmt->error . "\nTime taken for this row: " . number_format($roundTimeTaken, 10) . " seconds\n";
				} else {
					//echo "Row inserted succefull <br>";

					$roundEndTime = microtime(true);
					$roundTimeTaken = $roundEndTime - $roundStartTime;
					$batchMessages[] = "Row inserted successfully - Time taken for this row: " . number_format($roundTimeTaken, 10) . " seconds\n";
				}
				/*
				// Parameters array for the insert query
				$insertParams = array($carrier, $client, $street, $streetNumber, $streetNumberAdd, $unit, $city, $plz, $homeid, $firstname, $lastname, $phone1, $phone2, $mail, $status_system, $status_scan4, $today, $status_origin, $isporder, $contractStatus);

				// SQL string for the insert query
				$insertSql = "INSERT IGNORE INTO scan4_homes(client, carrier, street, streetnumber, streetnumberadd, unit, city, plz, homeid, firstname, lastname, phone1, phone2, email, hbg_status, scan4_status, scan4_added, system_status, isporder, contractstatus) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

				// Get the full query for insert statement
				$fullInsertQuery = getFullQuery($insertSql, $insertParams);

				// Echo the full insert query
				echo "Insert Query: " . $fullInsertQuery . '<br>';
				*/
			} else {

				$fields = [
					'firstname'       => $firstname,
					'lastname'        => $lastname,
					'street'          => $street,
					'streetnumber'    => $streetNumber,
					'streetnumberadd' => $streetNumberAdd,
					'phone1'          => $phone1,
					'phone2'          => $phone2,
					'hbg_status'      => $status_system,
					'email'           => $mail,
					'dpnumber'        => $dp,
					'status_scan4'    => $status_scan4,
				];
				homeshistory($conn, $currentuser, $homeid, $fields);

				$updateCityListStmt->execute();

				if ($updateHomeStmt->execute() === FALSE) {
					//echo "Error updating record: " . $conn->error . '<br>';
					//echo "Statement error: " . $updateHomeStmt->error . '<br>';
					// Parameters array for the update query
					//$updateParams = array($plz, $carrier, $client, $firstname, $lastname, $street, $streetNumber, $streetNumberAdd, $unit, $phone1, $phone2, $mail, $isporder, $contractStatus, $status_system, $status_origin, $homeid);

					// SQL string for the update query
					//$updateSql = "UPDATE scan4_homes SET plz = ?, carrier = ?, client = ?, firstname = ?, lastname = ?, street = ?, streetnumber = ?, streetnumberadd = ?, unit = ?, phone1 = ?, phone2 = ?, email = ?, isporder = ?, contractstatus = ?, hbg_status = ?, system_status = ? WHERE homeid = ?";

					// Get the full query for update statement
					//$fullUpdateQuery = getFullQuery($updateSql, $updateParams);

					// Echo the full update query
					//echo "Update Query: " . $fullUpdateQuery . '<br>';


					$roundEndTime = microtime(true);
					$roundTimeTaken = $roundEndTime - $roundStartTime;
					$batchMessages[] = "Error updating record: " . $conn->error . "\nStatement error: " . $updateHomeStmt->error . "\nTime taken for this row: " . number_format($roundTimeTaken, 10) . " seconds\n";
				} else {
					//echo "Row updated successfully <br>";

					$roundEndTime = microtime(true);
					$roundTimeTaken = $roundEndTime - $roundStartTime;
					$batchMessages[] = "Row updated successfully - Time taken for this row: " . number_format($roundTimeTaken, 10) . " seconds\n";
				}




				if ($whileLoop % $batchSize == 0) {


					file_put_contents($fileToOutput, implode("", $batchMessages), FILE_APPEND);
					$batchMessages = [];
				}
				$int_update++;
			}
		} else {
			if (!in_array($city, $a_citynew)) {
				$a_citynew[] = $city;
			}
		}


		$roundEndTime = microtime(true);
		$roundTimeTaken = $roundEndTime - $roundStartTime;
		//echo "Time taken for this round: " . number_format($roundTimeTaken, 10) . " seconds\n";
	}


	$globalEndTime = microtime(true);
	$totalTimeTaken = $globalEndTime - $globalStartTime;



	$batchMessages[] = "Total time taken: " . $totalTimeTaken . " seconds\n";
	$batchMessages[] = "Total row: $whileLoop\n";
	if (!empty($batchMessages)) {
		file_put_contents($fileToOutput, implode("", $batchMessages), FILE_APPEND);
	}


	$conn->commit();  // Commit transaction
	$conn->autocommit(TRUE);  // End transaction

	// Close your statements after the loop
	$checkDpStmt->close();
	$checkDpStmt->close();
	$updateDpStmt->close();
	$insertHomeStmt->close();
	$updateHomeStmt->close();
	$updateCityListStmt->close();

	$conn->close();


	echo "<p>Total time taken: " . $totalTimeTaken . " seconds</br>";
	echo "Total Rows: $whileLoop</p>";

	$html = '<div class="table-wrapper">
	<table class="table table-nocity">
			<thead>
				<tr>
					<th>Nicht zugeordnet</th>
				</tr>                
			</thead>
			<tbody class="table-body-font-size">';
	$int = 0;
	foreach ($a_citynew as $value) {

		if ($value !== "" && !(in_array($value, $a_citylist))) {
			$html .= '<tr><td> ' . $value . '</td></tr>';
			$int++;
		}
	}
	$html .= ' </tbody></table></div>';

	if ($int === 1) {
		echo "<p> Es wurde <b>$int</b> Ort gefunden der <b>nicht</b> zugeordnet werden konnte! </p>";
	} elseif ($int > 1) {
		echo "<p> Es wurden <b>$int</b> Orte gefunden die <b>nicht</b> zugeordnet werden konnten! </p>";
	}
	echo "<p> Neue Einträge DGF: <b>$int_new</b></p>";
	echo "<p> Einträge aktualisiert DGF: <b>$int_update</b></p>";

	echo $html;
}

function extractAddressId($homeid)
{
	if (empty($homeid)) {
		return "Error: Home ID cannot be empty.";
	}

	$parts = explode('_', $homeid);
	$numParts = count($parts);

	// If the last part is empty, it's already an address ID
	if (end($parts) === "") {
		return $homeid;
	}

	// If there are less than 4 parts, it's already an address ID
	if ($numParts < 4) {
		return $homeid;
	}

	// Remove the last segment which is the contract integer
	array_pop($parts);

	// If it's the case where we have double underscore, we want to make sure 
	// only one underscore is left at the end
	if (end($parts) === "") {
		array_pop($parts);
		$parts[] = "";
	}

	// Now let's rebuild the address ID
	$addressId = implode('_', $parts);

	return $addressId;
}

function splitName($name)
{
	// Remove titles if any (you can add more titles as needed)
	$name = preg_replace('/^(MADAM|SIR)\s/', '', $name);

	// Split the name into first name and last name components
	preg_match('/^(\S+)\s*(.*)$/', $name, $matches);

	// If matches are found, return the first name and last name
	if (isset($matches[1]) && isset($matches[2])) {
		return [
			'firstname' => $matches[1],
			'lastname' => $matches[2],
		];
	}

	// If no matches are found, return the whole name as the first name
	return [
		'firstname' => $name,
		'lastname' => '',
	];
}

function getFullQuery($query, $params)
{
	foreach ($params as $param) {
		$query = preg_replace('/\?/', "'" . $param . "'", $query, 1);
	}
	return $query;
}

function homeshistory($conn, $currentuser, $homeid, $fields)
{
	// Fetch current data for the homeid
	$stmt = $conn->prepare("SELECT * FROM scan4_homes WHERE homeid = ?");
	$stmt->bind_param('s', $homeid);
	$stmt->execute();
	$result = $stmt->get_result();
	$currentData = $result->fetch_assoc();

	// Prepare the insert statement outside the loop
	$insertStmt = $conn->prepare("INSERT INTO scan4_homes_history(homeid, col1, col2, col3, col4, col5) VALUES (?, 'import', ?, ?, ?, ?)");

	foreach ($fields as $key => $new) {
		if ($currentData[$key] != $new) {
			$insertStmt->bind_param('sssss', $homeid, $currentuser, $key, $currentData[$key], $new);
			$insertStmt->execute();
		}
	}

	$stmt->close();
	$insertStmt->close();
}








function upload_GFPLus($file)
{
	global $currentuser;

	$fileToOutput = '/var/www/html/logfiles/output.txt';

	$a_citylist = get_all_citys();
	$a_cityupdate = array();
	$a_homeidlist = get_all_homeids();
	$a_citynew = array();


	$csvFile = fopen($file, 'r');
	// Loop the csv to see the rows
	$TotalRows = 0;
	while (($row = fgetcsv($csvFile, 200000, ';')) !== FALSE) {
		$TotalRows++;
	}
	rewind($csvFile);  // Reset the file pointer to the beginning


	$int_new = 0;
	$int_update = 0;

	$today = date('Y-m-d');

	$column_headers = fgetcsv($csvFile, 100000, ';'); // read the columns headers
	$column_headers = array_map(function ($key) { // remove byte strings which are breaking the code
		return trim($key, "\xEF\xBB\xBF");
	}, $column_headers);

	// Ensure all column headers are unique
	$new_headers = [];
	foreach ($column_headers as $header) {
		$count = 0;
		while (in_array($header . ($count > 0 ? $count : ''), $new_headers)) {
			$count++;
		}
		$new_headers[] = $header . ($count > 0 ? $count : '');
	}
	$column_headers = $new_headers;


	$conn = dbconnect();
	$conn->autocommit(FALSE);  // Start transaction

	$checkDpStmt = $conn->prepare("SELECT dpnumber FROM scan4_homes WHERE homeid = ?");
	$checkDpStmt->bind_param('s', $homeid);


	$updateDpStmt = $conn->prepare("UPDATE scan4_homes SET dpnumber = ? WHERE homeid = ?");
	$updateDpStmt->bind_param('ss', $dp, $homeid);

	$insertHomeStmt = $conn->prepare("INSERT IGNORE INTO scan4_homes(client, carrier, street, streetnumber, streetnumberadd, unit, city, plz, homeid, adressid, firstname, lastname, phone1, phone2, email, hbg_status, scan4_status, scan4_added, system_status, isporder, contractstatus) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
	$insertHomeStmt->bind_param("sssssssssssssssssssss", $client, $carrier, $street, $streetNumber, $streetNumberAdd, $unit, $city, $plz, $homeid, $addressid, $firstname, $lastname, $phone1, $phone2, $mail, $status_system, $status_scan4, $today, $status_origin, $isporder, $contractStatus);

	$updateHomeStmt = $conn->prepare("UPDATE scan4_homes SET plz = ?, carrier = ?, client = ?, firstname = ?, lastname = ?, street = ?, streetnumber = ?, streetnumberadd = ?, unit = ?, phone1 = ?, phone2 = ?, email = ?, isporder = ?, contractstatus = ?, hbg_status = ?, system_status = ?, adressid = ? WHERE homeid = ?");
	$updateHomeStmt->bind_param("ssssssssssssssssss", $plz, $carrier, $client, $firstname, $lastname, $street, $streetNumber, $streetNumberAdd, $unit, $phone1, $phone2, $mail, $isporder, $contractStatus, $status_system, $status_scan4, $addressid, $homeid);

	$updateCityListStmt = $conn->prepare("UPDATE scan4_citylist SET plz = ? WHERE city = ?");
	$updateCityListStmt->bind_param("ss", $plz, $city);



	$globalStartTime = microtime(true);
	$batchMessages = [];
	$batchSize = 20;
	$whileLoop = 0;
	while (($row = fgetcsv($csvFile, 200000, ';')) !== FALSE) {
		$roundStartTime = microtime(true);
		$whileLoop++;
		//echo "$whileLoop\n";

		$plz = null;
		$carrier = null;
		$client = null;
		$firstname = null;
		$lastname = null;
		$street = null;
		$streetNumber = null;
		$streetNumberAdd = null;
		$unit = null;
		$phone1 = null;
		$phone2 = null;
		$mail = null;
		$isporder = null;
		$contractStatus = null;
		$status_system = null;
		$status_origin = null;
		$addressid = null;
		$homeid = null;
		$city = null;
		$today = null;
		$status_scan4 = null;


		$header = array_combine($column_headers, $row);
		$street = $header['Street'];
		// jump to the next row if the street is not valid. We only import if a street is given
		if (empty($street)) {
			continue;
		}

		$streetNumber = $header['House number'];
		$streetNumberAdd = $header['House no. App.'];
		$city = $header['City'];
		$plz = $header['Postal code'];
		$plz = preg_replace('/\D/', '', $plz);
		if (strlen($plz) > 5) {
			$plz = substr($plz, 0, 5);
		}
		$dp = $header['NVT Area'];
		$homeid = $header['KLS ID'];
		$addressid = $homeid;
		//$addressid = extractAddressId($homeid);

		$unit = null;

		$phone1 = $header['Mobile'];
		$phone2 = $header['Landline number'];
		$mail = $header['Email'];
		$name = $header['Name'];

		$firstname = null;
		$lastname = null;
		$name = trim($name);

		// Check if the name is personal based on MR/MRS
		if (strpos($name, 'MR ') === 0 || strpos($name, 'MRS ') === 0) {
			// Remove the MR or MRS prefix
			$name = trim(str_replace(array('MR', 'MRS'), '', $name));

			// Split by spaces
			$nameParts = explode(' ', $name);

			// If we have two parts, it's easy
			if (count($nameParts) == 2) {
				list($firstname, $lastname) = $nameParts;
			} else {
				// Assume the last name is the last word and the rest is the first name
				$lastname = array_pop($nameParts);
				$firstname = implode(' ', $nameParts);
			}
		} else {
			// It's a company name or something else
			$lastname = $name;
		}



		$status_origin =  $header['Next Activity'];

		$status_scan4 = '';
		$status_system = '';

		if (!empty($status_origin)) {
			if ($status_origin == 'Plan Site Survey') {
				$status_scan4 = 'OPEN';
				$status_system = 'OPEN';
			} elseif ($status_origin == 'Tiefbau') {
				if (!empty($HVStandort) && $HVStandort != '') {
					$status_scan4 = 'DONE';
					$status_system = 'DONE';
				} else {
					$status_scan4 = 'STOPPED';
					$status_system = 'STOPPED';
				}
			} else {
				//$status_scan4 = 'DONE';
				//$status_system = 'DONE';
			}
		}

		$contractStatus = NULL;
		$isporder = null;

		$firstname = mysqli_real_escape_string($conn, $firstname);
		$lastname = mysqli_real_escape_string($conn, $lastname);
		$street = mysqli_real_escape_string($conn, $street);

		if (in_array($city, array_column($a_citylist, 'city'))) {
			$carrier = 'GlasfaserPlus';
			$client = 'Insyte';


			if (!in_array($city, $a_cityupdate)) {
				$a_cityupdate[] = $city;
			} // add city for update date in city table
			if (!in_array($homeid, $a_homeidlist)) {
				$int_new++;

				if ($insertHomeStmt->execute() === FALSE) {
					//echo "Error inserting record: " . $conn->error . '<br>';
					//echo "Statement error: " . $insertHomeStmt->error . '<br>';

					$roundEndTime = microtime(true);
					$roundTimeTaken = $roundEndTime - $roundStartTime;
					$batchMessages[] = "$whileLoop/$TotalRows Error inserting record: " . $conn->error . "\nStatement error: " . $insertHomeStmt->error . "\nTime taken for this row: " . number_format($roundTimeTaken, 10) . " seconds\n";
				} else {
					//echo "Row inserted succefull <br>";

					$roundEndTime = microtime(true);
					$roundTimeTaken = $roundEndTime - $roundStartTime;
					$batchMessages[] = "$whileLoop/$TotalRows Row inserted - Time taken: " . number_format($roundTimeTaken, 10) . " seconds\n";
				}
				/*
				// Parameters array for the insert query
				$insertParams = array($carrier, $client, $street, $streetNumber, $streetNumberAdd, $unit, $city, $plz, $homeid, $firstname, $lastname, $phone1, $phone2, $mail, $status_system, $status_scan4, $today, $status_origin, $isporder, $contractStatus);

				// SQL string for the insert query
				$insertSql = "INSERT IGNORE INTO scan4_homes(client, carrier, street, streetnumber, streetnumberadd, unit, city, plz, homeid, firstname, lastname, phone1, phone2, email, hbg_status, scan4_status, scan4_added, system_status, isporder, contractstatus) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

				// Get the full query for insert statement
				$fullInsertQuery = getFullQuery($insertSql, $insertParams);

				// Echo the full insert query
				echo "Insert Query: " . $fullInsertQuery . '<br>';
				*/
			} else {

				$fields = [
					'firstname'       => $firstname,
					'lastname'        => $lastname,
					'street'          => $street,
					'streetnumber'    => $streetNumber,
					'streetnumberadd' => $streetNumberAdd,
					'phone1'          => $phone1,
					'phone2'          => $phone2,
					'hbg_status'      => $status_system,
					'email'           => $mail,
					'dpnumber'        => $dp,
					'status_scan4'    => $status_scan4,
				];
				homeshistory($conn, $currentuser, $homeid, $fields);

				$updateCityListStmt->execute();

				if ($updateHomeStmt->execute() === FALSE) {
					//echo "Error updating record: " . $conn->error . '<br>';
					//echo "Statement error: " . $updateHomeStmt->error . '<br>';
					// Parameters array for the update query
					//$updateParams = array($plz, $carrier, $client, $firstname, $lastname, $street, $streetNumber, $streetNumberAdd, $unit, $phone1, $phone2, $mail, $isporder, $contractStatus, $status_system, $status_origin, $homeid);

					// SQL string for the update query
					//$updateSql = "UPDATE scan4_homes SET plz = ?, carrier = ?, client = ?, firstname = ?, lastname = ?, street = ?, streetnumber = ?, streetnumberadd = ?, unit = ?, phone1 = ?, phone2 = ?, email = ?, isporder = ?, contractstatus = ?, hbg_status = ?, system_status = ? WHERE homeid = ?";

					// Get the full query for update statement
					//$fullUpdateQuery = getFullQuery($updateSql, $updateParams);

					// Echo the full update query
					//echo "Update Query: " . $fullUpdateQuery . '<br>';


					$roundEndTime = microtime(true);
					$roundTimeTaken = $roundEndTime - $roundStartTime;
					$batchMessages[] = "$whileLoop/$TotalRows Error updating record: " . $conn->error . "\nStatement error: " . $updateHomeStmt->error . "\nTime taken for this row: " . number_format($roundTimeTaken, 10) . " seconds\n";
				} else {
					//echo "Row updated successfully <br>";

					$roundEndTime = microtime(true);
					$roundTimeTaken = $roundEndTime - $roundStartTime;
					$batchMessages[] = "$whileLoop/$TotalRows Row updated - Time taken: " . number_format($roundTimeTaken, 10) . " seconds\n";
				}




				if ($whileLoop % $batchSize == 0) {
					file_put_contents($fileToOutput, implode("", $batchMessages), FILE_APPEND);
					$batchMessages = [];
				}
				$int_update++;
			}
		} else {
			if (!in_array($city, $a_citynew)) {
				$a_citynew[] = $city;
			}
		}


		$roundEndTime = microtime(true);
		$roundTimeTaken = $roundEndTime - $roundStartTime;
		//echo "Time taken for this round: " . number_format($roundTimeTaken, 10) . " seconds\n";
	}


	$globalEndTime = microtime(true);
	$totalTimeTaken = $globalEndTime - $globalStartTime;



	$batchMessages[] = "Total time taken: " . $totalTimeTaken . " seconds\n";
	$batchMessages[] = "Total row: $whileLoop\n";
	if (!empty($batchMessages)) {
		file_put_contents($fileToOutput, implode("", $batchMessages), FILE_APPEND);
	}


	$conn->commit();  // Commit transaction
	$conn->autocommit(TRUE);  // End transaction

	// Close your statements after the loop
	$checkDpStmt->close();
	$checkDpStmt->close();
	$updateDpStmt->close();
	$insertHomeStmt->close();
	$updateHomeStmt->close();
	$updateCityListStmt->close();

	$conn->close();


	echo "<p>Total time taken: " . $totalTimeTaken . " seconds</br>";
	echo "Total Rows: $whileLoop</p>";

	$html = '<div class="table-wrapper">
	<table class="table table-nocity">
			<thead>
				<tr>
					<th>Nicht zugeordnet</th>
				</tr>                
			</thead>
			<tbody class="table-body-font-size">';
	$int = 0;
	foreach ($a_citynew as $value) {

		if ($value !== "" && !(in_array($value, $a_citylist))) {
			$html .= '<tr><td> ' . $value . '</td></tr>';
			$int++;
		}
	}
	$html .= ' </tbody></table></div>';

	if ($int === 1) {
		echo "<p> Es wurde <b>$int</b> Ort gefunden der <b>nicht</b> zugeordnet werden konnte! </p>";
	} elseif ($int > 1) {
		echo "<p> Es wurden <b>$int</b> Orte gefunden die <b>nicht</b> zugeordnet werden konnten! </p>";
	}
	echo "<p> Neue Einträge GlasFaserPlus: <b>$int_new</b></p>";
	echo "<p> Einträge aktualisiert GlasFaserPlus: <b>$int_update</b></p>";

	echo $html;
}
