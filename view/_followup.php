<?php

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}
if (!securePage($_SERVER['PHP_SELF'])) {
    die();
}
$logged_in = $user->data();




