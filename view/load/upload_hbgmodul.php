<?php
//print_r($_FILES);



$func = $_POST['func'];
echo $func;
$filename = $_FILES['file']['name'];
$fileType = $_FILES['file']['type'];
$fileError = $_FILES['file']['error'];
$fileContent = file_get_contents($_FILES['file']['tmp_name']);
$filetmpname = $_FILES['file']['tmp_name'];




date_default_timezone_set('Europe/Berlin');
if ($fileError == UPLOAD_ERR_OK) {
	$passed_fail = '';
	$year = date("Y");

	// split for homeid
	$split = explode('-', $filename, 4);
	if (isset($split[1])) {
		$homeid = $split[1];
	} else {
		$homeid = "undefind";
	}
	// splitt for project and username
	$split = explode('_', $filename, 10);
	if (isset($split[1])) {
		$project = $split[0];
		$username = $split[5];
	} else {
		$project = "undefind";
	}
	// split for uid
	$split = explode('.', $filename, 3);
	if (isset($split[2])) {
		$uid = $split[2];
		$filename = $split[0] . '.' . $split[1];
		$passed = true;
	} else {
		$passed = false;
		$passed_fail = 'filename not correct';
	}


	if ($_FILES['file']['size'] < 50) {
		$passed = false;
		$passed_fail = 'file too small';
	}
	// if mymtype is not pdf
	if ($fileType != 'application/pdf') {
		$passed = false;
		$passed_fail = 'mimetype not pdf';
	}

	if (!file_exists('/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/uploads/hbgprotokolle/begehungen/screenshots/' . $year . '/' . $project)) {
		mkdir('/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/uploads/hbgprotokolle/begehungen/screenshots/' . $year . '/' . $project, 0755, true);
	}
	$newfile = '/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/uploads/hbgprotokolle/begehungen/screenshots/' . $year . '/' . $project . '/' . $filename;


	$filesaved = false;
	if (move_uploaded_file($_FILES['file']['tmp_name'], $newfile) === true) {
		echo $filename . ' file saved';
		$filesaved = true;
		$totalPages = countpages($newfile);



		if ($totalPages < 3) {
			$passed = false;
			$passed_fail = 'zu wenig Seiten_' . $totalPages;
		}
	} else {
		echo $filename . " failed";
	}


	$response = false;
	$conn = db();
	$date = date("Y-m-d H:i:s");
	if ($filesaved === true && $passed === false) {
		echo 'not passed';
		if (!file_exists('/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/uploads/hbgprotokolle/begehungen/wrongfiles/' . $year)) {
			mkdir('/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/uploads/hbgprotokolle/begehungen/wrongfiles/' . $year, 0755, true);
		}
		$copyto = '/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/uploads/hbgprotokolle/begehungen/wrongfiles/' . $year . '/' . $filename;
		if (rename($newfile, $copyto)) {
			//echo "File moved successfully.";
		}
		// update userlog
		$query = "INSERT INTO `scan4_userlog` SET `datetime`='" . $date . "', `user`='" . $username . "',source = 'hbgmodul', `action1`='hbgmodul file failed', `action2`='" . $passed_fail . "' , `action3`='" . $uid . "'";
		mysqli_query($conn, $query);
	} else if ($filesaved === false && $passed === false) {
		echo 'not passed';
		if (!file_exists('/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/uploads/hbgprotokolle/begehungen/wrongfiles/' . $year)) {
			mkdir('/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/uploads/hbgprotokolle/begehungen/wrongfiles/' . $year, 0755, true);
		}
		$copyto = '/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/uploads/hbgprotokolle/begehungen/wrongfiles/' . $year . '/' . $filename;
		if (rename($newfile, $copyto)) {
			//echo "File moved successfully.";
		}
		// update userlog
		$query = "INSERT INTO `scan4_userlog` SET `datetime`='" . $date . "', `user`='" . $username . "',source = 'hbgmodul', `action1`='hbgmodul file failed', `action2`='" . $passed_fail . "' , `action3`='" . $uid . "'";
		mysqli_query($conn, $query);
	} else {
		echo 'passed';
		// update appt info about file
		$query = "UPDATE `scan4_hbg` SET `appt_file`='" . $filename . "' WHERE uid='" . $uid . "'";
		mysqli_query($conn, $query);

		// update userlog
		$query = "INSERT INTO `scan4_userlog` SET `datetime`='" . $date . "',homeid = '" . $homeid . "', `user`='" . $username . "', source = 'hbgmodul',`action1`='hbgmodul file passed with " . $totalPages . "pages', `action2`='" . $filename . "' , `action3`='" . $uid . "' ";
		mysqli_query($conn, $query);
		$response = true;
	}
	$conn->close();
	return $response;
} else {
	$conn = db();
	$query = "INSERT INTO `scan4_userlog` SET `datetime`='" . $date . "', `user`='" . $username . "',source = 'hbgmodul', `action1`='hbgmodul file failed', `action2`='" . $passed_fail . "' , `action3`='" . $uid . "'";
	mysqli_query($conn, $query);
	$conn->close();
	switch ($fileError) {
		case UPLOAD_ERR_INI_SIZE:
			$message = 'Error al intentar subir un archivo que excede el tamaño permitido.';
			break;
		case UPLOAD_ERR_FORM_SIZE:
			$message = 'Error al intentar subir un archivo que excede el tamaño permitido.';
			break;
		case UPLOAD_ERR_PARTIAL:
			$message = 'Error: no terminó la acción de subir el archivo.';
			break;
		case UPLOAD_ERR_NO_FILE:
			$message = 'Error: ningún archivo fue subido.';
			break;
		case UPLOAD_ERR_NO_TMP_DIR:
			$message = 'Error: servidor no configurado para carga de archivos.';
			break;
		case UPLOAD_ERR_CANT_WRITE:
			$message = 'Error: posible falla al grabar el archivo.';
			break;
		case  UPLOAD_ERR_EXTENSION:
			$message = 'Error: carga de archivo no completada.';
			break;
		default:
			$message = 'Error: carga de archivo no completada.';
			break;
	}
	echo json_encode(array(
		'error' => true,
		'message' => $message
	));
}
function countpages($newfile)
{
	$pdf = file_get_contents($newfile);
	$number = preg_match_all("/\/Page\W/", $pdf, $dummy);
	return $number;
}
function db()
{
    $servername = "db.scan4-gmbh.com";
	$username = "SC4_CRM";
	$password = "4Amp!w!V0(9VBL9n:;)5{4&M(";
	$dbname = "SC4_CRM_2";
	$conn = new mysqli($servername, $username, $password, $dbname);
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
	return $conn;
}  

