<?php
$filename = $_GET['file'];
$directory = "/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/uploads/";
$filePath = $directory . $filename;


if (!file_exists($filePath)) {
    die("Error: File does not exist at expected path.");
}

if (strpos($filename, '..') !== false) {
    die("Error: Invalid filename.");
}

if (file_exists($filePath) && strpos($filename, '..') === false) {
    // Set headers to force download
    header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
    exit;
} else {
    header("HTTP/1.0 404 Not Found");
    echo "File not found. 123";
}
