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
	$split = explode('_', $filename, 2);
	if (isset($split[1])) {
		$project = $split[0];
	} else {
		$project = "undefind";
	}
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
		$totalPages = count($newfile);

		function count($newfile)
		{
			$pdf = file_get_contents($newfile);
			$number = preg_match_all("/\/Page\W/", $pdf, $dummy);
			return $number;
		}

		if ($totalPages < 3) {
			$passed = false;
			$passed_fail = 'zu wenig Seiten_' . $totalPages;
		}
	} else {
		echo $filename . " failed";
	}


	$response = false;
	$conn = dbconnect();
	if ($filesaved === true && $passed === false) {
		// delete file
		unlink($newfile);
		// update userlog
		$query = "INSERT INTO `scan4_userlog` SET `datetime`='" . $date . "', `user`='" . $_SESSION['user'] . "', `action1`='file failed', `action2`='" . $passed_fail . "' ";
		mysqli_query($conn, $query) or die(mysqli_error($conn));
	} else {
		echo $filename . " failed";

		// update appt info about file
		$date = date("Y-m-d H:i:s");
		$query = "UPDATE `scan4_hbg` SET `appt_file`='" . $filename . "' WHERE uid='" . $uid . "'";
		mysqli_query($conn, $query) or die(mysqli_error($conn));

		// update userlog
		$query = "INSERT INTO `scan4_userlog` SET `datetime`='" . $date . "', `user`='" . $_SESSION['user'] . "', `action1`='file passed', `action2`='" . $filename . "' ";
		mysqli_query($conn, $query) or die(mysqli_error($conn));
		$response = true;
	}
	$conn->close();
	return $response;
} else {
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
