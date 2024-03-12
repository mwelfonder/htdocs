<?php


include(dirname(__FILE__) . "/users/init.php");


// DP Sortierung

$dir = $_SERVER['PHP_SELF'];
//echo $dir . "</br>";


if (!$user->isLoggedIn()) {
    Redirect::to($us_url_root . "forbidden.php");
} else {

    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $_SERVER['REQUEST_URI'])) {
        // Open the file for reading
        $fp = fopen($_SERVER['DOCUMENT_ROOT'] . $_SERVER['REQUEST_URI'], 'r');

        // Set mime type to header
        header('Content-type: ' . mime_content_type($_SERVER['DOCUMENT_ROOT'] . $_SERVER['REQUEST_URI']));

        // Send the contents of the file the browser
        fpassthru($fp);
        fclose($fp);
    } else {
        // File not found
        die('File not found');
    }
}

//echo 'auth';
