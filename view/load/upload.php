<?php
//print_r($_FILES);



$func = $_POST['func'];
echo $func;
$fileName = $_FILES['file']['name'];
$fileType = $_FILES['file']['type'];
$fileError = $_FILES['file']['error'];
$fileContent = file_get_contents($_FILES['file']['tmp_name']);
$filetmpname = $_FILES['file']['tmp_name'];




date_default_timezone_set('Europe/Berlin');
if ($fileError == UPLOAD_ERR_OK) {
	echo $fileName;
	$split = explode('_', $fileName, 2);

	if (isset($split[1])) {
		$fileName = $split[1];
		$project = $split[0];
	} else {
		$project = "undefind";
	}
	if (!file_exists('/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/uploads/hbgprotokolle/' . $project)) {
		mkdir('/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/uploads/hbgprotokolle/' . $project, 0755, true);
	}
	$newfile = '/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/uploads/hbgprotokolle/' . $project . "/" . $fileName;
	//echo "Temp name: " . $_FILES['file']['tmp_name'][$i] . "<br>";
	// Upload file
	if (move_uploaded_file($_FILES['file']['tmp_name'], $newfile) === true) {
		//echo "filemoved: </br>" . $file . "</br>";
		echo $fileName.' file saved';
	} else {
		echo $fileName. " failed";
	}
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
