<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';

if (!(hasPerm([2, 3]))) {
    die();
}

echo "sipgate.php <br>";



//setlocale(LC_TIME, 'de_DE.utf8');

date_default_timezone_set('Europe/Berlin');

include_once $_SERVER['DOCUMENT_ROOT'] . '/view/includes/functions.php';

?>


<!DOCTYPE html>
<html>

<head>
    <title>Sipgate API Test</title>
</head>

<body>
    <!-- Buttons for calling and hanging up -->
    <a href="tel:+4915254582743" id="makeCall">Make Call</a>
    <button id="endCall" disabled>End Call</button>
</body>


</html>

<script>
    $(document).ready(function() {
        async function fetchUsers() {
            const url = 'https://api.sipgate.com/v2/w3/devices';
            const tokenId = 'token-MKPXNT'; // Replace with your Token ID
            const token = 'c4ac93db-652f-4916-afa9-5f4211841700'; // Replace with your Access Token

            try {
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Authorization': 'Basic ' + btoa(tokenId + ':' + token),
                        'Content-Type': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const data = await response.json();
                console.log(data);
            } catch (error) {
                console.error('Fetch error:', error);
            }
        }

        fetchUsers();




        let callId = null;

        $('#makeCall').click(function(event) {
            event.preventDefault(); // Prevent the default action of the link
            let phoneNumber = $(this).attr('href').split(':')[1]; // Extract the phone number from the href
            console.log('calling number ' + phoneNumber)
            // Make an AJAX call to your backend to initiate a call
            $.post('view/load/call_out.php', {
                number: phoneNumber
            }, function(data) {
                console.log('data received of call out', data)
                callId = data.callId; // Assuming the backend returns the callId
                $('#endCall').prop('disabled', false);
            });
        });

        $('#endCall').click(function() {
            if (callId) {
                // Make an AJAX call to your backend to end the call
                console.log('hang up callid: ' + callId)
                $.post('view/load/call_end.php', {
                    callId: callId
                }, function(data) {
                    console.log('data received of hangup', data)
                    $('#endCall').prop('disabled', true);
                });
            }
        });
    });
</script>


<?php


?>