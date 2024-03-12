<?php
/*
require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';

if (!(hasPerm([2, 3]))) {
    die();
}



use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use scan4\Mailer;



include_once $_SERVER['DOCUMENT_ROOT'] . '/view/includes/functions.php';

function getBearerToken($clientId, $clientSecret, $authUrl)
{
    $data = [
        'grant_type' => 'client_credentials',
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'scope' => 'net_api_internal'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $authUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    if (!$response) {
        return 'Fehler bei der Authentifizierung: ' . curl_error($ch);
    }

    $responseArray = json_decode($response, true);
    return $responseArray['access_token'];
}

function getGemeinden($token, $operationsUrl)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $operationsUrl . '/getGemeinden');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    if (!$response) {
        return 'Fehler beim Abrufen der Gemeindedaten: ' . curl_error($ch);
    }

    return $response;
}

// Beispielverwendung der Funktionen
$clientId = 'scan4_gmbh'; // Ersetzen Sie dies mit Ihrer Client-ID
$clientSecret = '5f2b9534-21c6-4864-9317-918dad7934ce'; // Ersetzen Sie dies mit Ihrem Client-Geheimnis
$authUrl = 'https://login.ugg.tech/ugg-iam/oidc/token'; // URL für Authentifizierung
$operationsUrl = 'https://api.ugg.tech/sdropec/v1'; // URL für Operations-API

$token = getBearerToken($clientId, $clientSecret, $authUrl);
$gemeindenData = getGemeinden($token, $operationsUrl);

echo $gemeindenData;
*/ 
?>
<!--
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>Scan4 CRM Statusüberprüfung</title>

    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #6e8efb, #a777e3);
            /* Spannender Hintergrund */
            color: white;
            /* Weiße Schriftfarbe für bessere Lesbarkeit */
            text-align: center;
            /* Zentrale Ausrichtung des Texts */
            height: 100vh;
            /* Vollbildhöhe */
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        .domain-status {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #fff;
            border-radius: 5px;
            background-color: rgba(255, 255, 255, 0.2);
            /* Leicht transparenter Hintergrund */
            box-shadow: 0 2px 4px rgba(0, 0, 0, .1);
        }

        .loading-spinner {
            width: 1rem;
            height: 1rem;
        }

        .logo {
            max-width: 150px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="header-content">
        <img src="logo.png" alt="Firmenlogo" class="logo"> 
        <h1 id="welcome-message">Willkommen auf dem Scan4 CRM</h1>
        <p id="assigning-message">Bitte einen Moment, Sie werden sofort dem schnellsten Server zugewiesen!</p>
    </div>

    <div id="domainA" class="domain-status">
        CRM Node 1: <span class="loading-spinner spinner-border spinner-border-sm text-light"></span>
    </div>
    <div id="domainB" class="domain-status">
        CRM Node 2: <span class="loading-spinner spinner-border spinner-border-sm text-light"></span>
    </div>

    <script>
        function getBrowserLanguage() {
            return navigator.language || navigator.userLanguage;
        }

        function getLocalizedText(text, lang) {
            const translations = {
                'en': {
                    'welcome': 'Welcome to Scan4 CRM',
                    'assigning': 'Please wait, you will be assigned to the fastest server shortly!',
                    'reachable': 'Reachable (Response Time: ',
                    'not_reachable': 'Not Reachable',
                    'fetch_error': 'Error fetching status'
                },
                'de': {
                    'welcome': 'Willkommen auf dem Scan4 CRM',
                    'assigning': 'Bitte einen Moment, Sie werden sofort dem schnellsten Server zugewiesen!',
                    'reachable': 'Erreichbar (Antwortzeit: ',
                    'not_reachable': 'Nicht erreichbar',
                    'fetch_error': 'Fehler beim Abrufen des Status'
                },
                'es': {
                    'welcome': 'Bienvenido a Scan4 CRM',
                    'assigning': 'Por favor, espere, ¡será asignado al servidor más rápido en breve!',
                    'reachable': 'Alcanzable (Tiempo de respuesta: ',
                    'not_reachable': 'No alcanzable',
                    'fetch_error': 'Error al obtener el estado'
                }
            };

            return translations[lang] && translations[lang][text] ? translations[lang][text] : translations['en'][text];
        }

        // Globale Variablen zur Speicherung der Antwortzeiten und URLs
        let responseTimes = {
            'domainA': {
                'url': 'https://crm.scan4-gmbh.de',
                'time': null
            },
            'domainB': {
                'url': 'https://streetnav.de',
                'time': null
            }
        };

        function updateStatus(domainId, isReachable, duration) {
            let lang = getBrowserLanguage().slice(0, 2);
            responseTimes[domainId].time = isReachable ? duration : Number.MAX_VALUE;
            $('#' + domainId).html(`CRM Node ${domainId === 'domainA' ? '1' : '2'}: ${isReachable ? getLocalizedText('reachable', lang) + duration + 'ms)' : getLocalizedText('not_reachable', lang)}`);
            redirectToFastestDomain();
        }

        function redirectToFastestDomain() {
            // Prüfen, ob beide Antwortzeiten vorhanden sind
            if (responseTimes.domainA.time !== null && responseTimes.domainB.time !== null) {
                // Überprüfen, ob eine der Seiten erreichbar ist
                if (responseTimes.domainA.time === Number.MAX_VALUE && responseTimes.domainB.time === Number.MAX_VALUE) {
                    // Keine Seite ist erreichbar
                    $('#welcome-message').text('Leider sind beide Server derzeit nicht erreichbar.');
                    $('#assigning-message').text('');
                } else {
                    // Finden der Domain mit der niedrigsten Antwortzeit
                    let fastestDomain = responseTimes.domainA.time < responseTimes.domainB.time ? 'domainA' : 'domainB';

                    // Inhalte der Seite durch einen iFrame ersetzen
                    $('body').html(`<iframe src="${responseTimes[fastestDomain].url}" style="width:100%; height:100vh; border:none;"></iframe>`);
                }
            }
        }
   

        
        function checkDomain(url, domainId, lang) {
            let startTime = Date.now();
            $.ajax({
                url: url + '/status.php',
                type: 'GET',
                dataType: 'text',
                success: function(response) {
                    let duration = Date.now() - startTime;
                    updateStatus(domainId, response === 'erreichbar', duration);
                },
                error: function() {
                    updateStatus(domainId, false, null);
                }
            });
        }

        $(document).ready(function() {
            const lang = getBrowserLanguage().slice(0, 2); // Spracheinstellung des Browsers ermitteln
            $('#welcome-message').text(getLocalizedText('welcome', lang));
            $('#assigning-message').text(getLocalizedText('assigning', lang));


            // Überprüfen, ob der Nutzer bereits weitergeleitet wurde
            if (sessionStorage.getItem('redirected')) {
                sessionStorage.removeItem('redirected'); // Zurücksetzen der Weiterleitungsinformation
                redirectToFastestDomain(); // Erneute Weiterleitung durchführen
            } else {
                // Initialer Check der Domains
                const domainAUrl = 'https://crm.scan4-gmbh.de';
                const domainBUrl = 'https://streetnav.de';
                checkDomain(domainAUrl, 'domainA', lang);
                checkDomain(domainBUrl, 'domainB', lang);
            }



        });
    </script>
</body>

</html>

-->


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

function getBearerToken($clientId, $clientSecret, $authUrl)
{
    $data = [
        'grant_type' => 'client_credentials',
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'scope' => 'net_api_internal'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $authUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    if (!$response) {
        return 'Fehler bei der Authentifizierung: ' . curl_error($ch);
    }

    $responseArray = json_decode($response, true);
    // Ausgabe des vollständigen Authentifizierungsantwort
    echo "Authentifizierungsantwort: \n";
    print_r($responseArray);

    return $responseArray['access_token'];
}

function getGemeinden($token, $operationsUrl)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $operationsUrl . '/getGemeinden');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    if (!$response) {
        return 'Fehler beim Abrufen der Gemeindedaten: ' . curl_error($ch);
    }

    return $response;
}


function getOntSyncStatus($token, $homeId, $operationsUrl)
{
    $url = $operationsUrl . '/synchro/' . $homeId;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    if (!$response) {
        return 'Fehler beim Abrufen des Synchronisationsstatus: ' . curl_error($ch);
    }

    return $response;
}


// Beispielverwendung der Funktionen
// Beispielverwendung der Funktionen
$clientId = 'scan4_gmbh';
$clientSecret = '5f2b9534-21c6-4864-9317-918dad7934ce';
$authUrl = 'https://login.ugg.tech/ugg-iam/oidc/token';
$operationsUrl = 'https://api.ugg.tech/sdropec/v1';

$token = getBearerToken($clientId, $clientSecret, $authUrl);
$gemeindenData = getGemeinden($token, $operationsUrl);
echo "Gemeinden Daten:\n";
echo $gemeindenData;

$homeId = 'ARP006802825001'; // Beispiel Home ID
$ontSyncStatus = getOntSyncStatus($token, $homeId, $operationsUrl);
echo "ONT Sync Status:\n";
echo $ontSyncStatus;






