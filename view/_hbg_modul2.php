<?php

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}

$data = $user->data();
$currentuser = $data->fname;
$fname = $data->fname;
$lastname = $data->lname;
$lastname = mb_substr($lastname, 0, 1);


$dir = $_SERVER['DOCUMENT_ROOT'] . '/view/load/hbgmodul_load.php';
echo 'asdasd';