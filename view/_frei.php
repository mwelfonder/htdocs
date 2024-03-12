<?php

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}
if (!securePage($_SERVER['PHP_SELF'])) {
    die();
}
if (!(hasPerm([2, 3]) || hasPerm([5]))) {
    die();
}
$username = $user->data()->username;






?>


<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>Freischalter</title>
    <link rel="stylesheet" type="text/css" href="view/includes/style_activation.css?=v3.0">
    <script src="view/includes/js/app_activationnew.js?=v2.8"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

</head>

<body>
    <div class="container">
        <div id="activationTrackerContainer">
            <div id="activationTracker">0 von 0</div>
        </div>

        <div id="freischalter">
            <button id="listMode">Liste</button>
            <button id="carouselMode">Durchlauf</button>
            <div id="dataDisplay"></div>
            <input type="date" id="customDate">
        </div>
    </div>
    <div id="popupModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="modalData">Hier erscheinen die Daten...</div>
            <div class="button-container">
                <button id="activateBtn">Aktivieren</button>
                <button id="errorBtn">Error</button>
            </div>

        </div>
    </div>
</body>

</html>



<!DOCTYPE html>
<html>

<head>