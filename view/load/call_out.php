<?php

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}
if (!securePage($_SERVER['PHP_SELF'])) {
    die();
}



include "../../view/includes/functions.php";

date_default_timezone_set('Europe/Berlin');



$phoneNumber = isset($_POST['number']) ? $_POST['number'] : null;
if (!$phoneNumber) {
    echo json_encode(['error' => 'No phone number provided']);
    exit;
}

$url = 'https://api.sipgate.com/v2/sessions/calls';
$data = [
    'deviceId' => 'e3', // Replace with your device ID from Sipgate
    'callerId' => '+4915792454484', // Replace with your caller ID (verified number on Sipgate)
    'callee' => $phoneNumber
];
$headers = ['Authorization: Bearer c4ac93db-652f-4916-afa9-5f4211841700', 'Content-Type: application/json'];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

// After curl_exec
if (!$response) {
    echo json_encode(['error' => 'Curl error: ' . curl_error($ch)]);
    exit;
}

$responseArray = json_decode($response, true);

if (isset($responseArray['callId'])) {
    echo json_encode(['callId' => $responseArray['callId']]);
} else {
    // Log the entire response to understand the error
    echo json_encode(['error' => 'Call initiation failed', 'details' => $responseArray]);
}
