<?php

// This script should send a request to the Sipgate API to end the call using the callId
// For example, using the /calls/{callId} endpoint with DELETE method
// Again, this is a simplified example

if (isset($_POST['callId'])) {
    $callId = $_POST['callId'];
    $url = 'https://api.sipgate.com/v2/calls/' . $callId;

    $headers = ['Authorization: Bearer YOUR_ACCESS_TOKEN'];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    echo $response; // Or handle the response as needed
}
