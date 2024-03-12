<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
include "../../view/includes/functions.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $conn = dbconnect();  // Ihre Datenbankverbindung

    // Überprüfung, ob notwendige Daten gesetzt sind
    if (empty($_POST['username'])) {
        die("Fehler: Benutzer ist nicht angegeben.");
    }

    if (empty($_POST['homeid'])) {
        die("Fehler: homeid ist nicht angegeben.");
    }

    if (empty($_POST['status'])) {
        die("Fehler: status ist nicht angegeben.");
    }

    if (empty($_FILES['document']['name'])) {
        die("Fehler: Kein Dokument ausgewählt.");
    }

    $ULcomment = $_POST['comment'];
    $ULuser = $_POST['username'];
    $ULhomeid = $_POST['homeid'];
    $ULstatus = $_POST['status'];


    // Abfrage des letzten Uploads für den spezifischen Benutzer
    $query = $conn->prepare("SELECT datetime FROM scan4_homes_documents WHERE user = ? ORDER BY datetime DESC LIMIT 1");
    $query->bind_param('s', $ULuser);
    $query->execute();
    $result = $query->get_result();
    $lastUploadTime = $result->fetch_assoc();

    if ($lastUploadTime) {
        $lastUploadTimestamp = strtotime($lastUploadTime['datetime']);
        $currentTimestamp = time();

        // Überprüfen Sie, ob seit dem letzten Upload 10 Sekunden vergangen sind
        if ($currentTimestamp - $lastUploadTimestamp < 10) {
            die;
        }
    }

    $uploadDirectory = $_SERVER['DOCUMENT_ROOT'] . '/uploads/documents/' . date('Y') . '/' . date('m') . '/' . date('d') . '/';

    // Setzen Sie den Dateinamen wie gewünscht
    $fileExtension = pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION);
    $datetimeFormatted = date("Y-m-d_H-i-s");
    $status = str_replace(' ', '_', $_POST['status']);
    $filename = $ULuser . '_' . $_POST['homeid'] . '_' . $datetimeFormatted . '_' . $status . '.' . $fileExtension;


    $uploadPath = $uploadDirectory . $filename;

    // Erstellen Sie das Verzeichnis, falls es nicht existiert
    if (!is_dir($uploadDirectory)) {
        mkdir($uploadDirectory, 0777, true);
    }





    if (move_uploaded_file($_FILES['document']['tmp_name'], $uploadPath)) {
        // Datei erfolgreich hochgeladen, nun in der Datenbank speichern
        $stmt = $conn->prepare("INSERT INTO scan4_homes_documents (datetime, user, homeid, location, filename, comment, status, DateTimeOriginal) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        $datetime = date("Y-m-d H:i:s");

        $homeid = $_POST['homeid'];
        $status = $_POST['status'];
        $comment = $_POST['comment'] ?? ''; // Nehmen Sie den Standardwert '', wenn kein Kommentar angegeben ist

        // Überprüfen, ob die Datei ein JPEG ist (typisches Format für EXIF-Daten)
        if (strtolower($fileExtension) === 'jpg' || strtolower($fileExtension) === 'jpeg') {
            $exif_data = exif_read_data($uploadPath);

            if ($exif_data && isset($exif_data['DateTimeOriginal'])) {
                $dateTimeOriginal = $exif_data['DateTimeOriginal'];
            } else {
                $dateTimeOriginal = "NoDate";  // Oder setzen Sie einen Standardwert, wenn es keine EXIF-Daten gibt
            }
        } else {
            $dateTimeOriginal = "NoDate";  // Oder setzen Sie einen Standardwert für nicht-JPEG-Dateien
        }

        $stmt->bind_param('ssssssss', $datetime, $ULuser, $homeid, $uploadPath, $filename, $comment, $status, $dateTimeOriginal);

        if ($stmt->execute()) {
            echo "Dokument erfolgreich gespeichert!";
            $userlog['source'] = 'hbgmodul';
            $userlog['homeid'] = $homeid;
            $userlog['action1'] = 'document create';
            $userlog['action2'] = $status  . ' ' . $comment;
            $userlog['action3'] = $uploadPath;
            saveUserLog($userlog); // save the actions to the userlog
        } else {
            echo "Fehler beim Speichern des Dokuments in der Datenbank: " . $conn->error;
        }
    } else {
        echo "Fehler beim Hochladen des Dokuments.";
    }
} else {
    echo "Fehler: Anforderungsmethode nicht unterstützt oder kein Dokument gesendet.";
}
