<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';

if (!(hasPerm([2, 3]))) {
    die();
}

echo "test2.php <br>";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use scan4\Mailer;



include_once $_SERVER['DOCUMENT_ROOT'] . '/view/includes/functions.php';


// Deine Sipgate-Zugangsdaten
$tokenID = 'token-IX7WIN'; // Ersetze mit deiner Personal-Access-Token-ID
$token = '6541cb08-afa2-46d4-a157-cb45fd02e617'; // Ersetze mit deinem Personal-Access-Token

// Die SMS-Details
$smsId = 's0';
$recipient = '+4917628445879'; // Ersetze mit der Telefonnummer des Empfängers
$message = 'Deine Nachricht'; // Ersetze mit deiner Nachricht
$sender = 'Scan4 GmbH'; // Absendernamen
// Erstelle die POST-Daten
$data = [
    'smsId' => $smsId,
    'recipient' => $recipient,
    'message' => $message
];

// Initialisiere cURL
$ch = curl_init('https://api.sipgate.com/v2/sessions/sms');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Basic ' . base64_encode($tokenID . ':' . $token)
]);

// Führe die Anfrage aus
$response = curl_exec($ch);
curl_close($ch);

// Gib die Antwort aus
echo $response;
?>
