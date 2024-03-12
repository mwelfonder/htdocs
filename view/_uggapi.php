<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';

if (!(hasPerm([2, 3]))) {
    die();
}

echo "ugg api.php <br>";




// API endpoint and credentials for authentication
$authUrl = 'https://login.ugg.tech/ugg-iam/oidc/token';
$clientId = 'scan4_gmbh';
$clientSecret = '5f2b9534-21c6-4864-9317-918dad7934ce';

// Initialize cURL session for authentication
$ch = curl_init($authUrl);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'grant_type' => 'client_credentials',
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'scope' => 'net_api_internal'
]));

// Execute the cURL session and get the response
$response = curl_exec($ch);
curl_close($ch);

// Decode the JSON response to get the access token
$responseData = json_decode($response, true);
$accessToken = $responseData['access_token'] ?? '';

if (!$accessToken) {
    die("Error: Unable to retrieve access token.");
}

echo "Access Token: " . $accessToken . "</br>";






// API endpoint for getting catalog data
$apiUrl = 'https://api.ugg.tech/sdropec/v1/getGemeinden';

// Initialize cURL session for the API request
$ch = curl_init($apiUrl);

// Set cURL options for the API request
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken
]);

// Execute the cURL session and get the response
$apiResponse = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
}
curl_close($ch);

// Decode the API response
$apiData = json_decode($apiResponse, true);

// Check for errors or handle the data as needed...
if (isset($apiData['error'])) {
    echo "Error: " . $apiData['error']['message'] . "\n";
} else {
    print_r($apiData);
}




?>

<script>
    // Authentication details
    var authUrl = 'https://login.ugg.tech/ugg-iam/oidc/token';
    var clientId = 'scan4_gmbh';
    var clientSecret = '5f2b9534-21c6-4864-9317-918dad7934ce';

    // AJAX POST request for authentication
    $.ajax({
        url: authUrl,
        method: 'POST',
        data: {
            grant_type: 'client_credentials',
            client_id: clientId,
            client_secret: clientSecret,
            scope: 'net_api_internal'
        },
        success: function(data) {
            var accessToken = data.access_token;
            if (!accessToken) {
                console.error("Error: Unable to retrieve access token.");
                return;
            }
            console.log("Access Token: " + accessToken);

            // API endpoint for getting catalog data
            var apiUrl = 'https://api.ugg.tech/sdropec/v1/getGemeinden';

            // AJAX GET request for API data
            $.ajax({
                url: apiUrl,
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + accessToken
                },
                success: function(apiData) {
                    if (apiData.error) {
                        console.error("Error: " + apiData.error.message);
                    } else {
                        console.log(apiData);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Error:', errorThrown);
                }
            });
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('Error:', errorThrown);
        }
    });
</script>