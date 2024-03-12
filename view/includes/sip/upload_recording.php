<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['audioFile'])) {
    $path = $_POST['path'];
    $directory = '/var/www/html/view/includes/sip/rec/' . $path; // Stellen Sie sicher, dass dieser Pfad existiert und beschreibbar ist

    if (!file_exists($directory)) {
        mkdir($directory, 0777, true); // Erstellt das Verzeichnis, wenn es nicht existiert
    }

    $filePath = $directory . '/' . $_FILES['audioFile']['name'];

    if (move_uploaded_file($_FILES['audioFile']['tmp_name'], $filePath)) {
        echo "Die Datei wurde erfolgreich hochgeladen.";
    } else {
        echo "Fehler beim Hochladen der Datei.";
    }
} else {
    echo "Keine Datei empfangen.";
}
?>
