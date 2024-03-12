<?php

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}
if (!securePage($_SERVER['PHP_SELF'])) {
    die();
}
if (!(hasPerm([31]))) {
    die();
}

$username = $user->data()->username;
// In einer Ihrer PHP-Dateien
if ((hasPerm([2, 3]))) {  
    include 'view/_worktime_admin.php';
    exit;
} 

?>


<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>Worki Worki</title>


    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>


</head>

<body>
    <?php if (hasPerm([5]) && !hasPerm([2, 3])) { ?>
        <link rel="stylesheet" type="text/css" href="view/includes/worktime.css?=v1.1">
        <script src="view/includes/js/app_worktime_tele.js?=v1.996"></script>
        <div class="leftContainer">
            <div id="userListContainer">
                <input type="date" id="calendarInput" />
                <ul id="userList">
                    <li class="user-item">
                        <span class="username"><?php echo htmlspecialchars($username); ?></span>
                    </li>
                </ul>
            </div>
        </div>




    <div class="rightContainer">
        <div id="detailsContainer">
            <h2 id="selectedUsername"></h2>
            <div id="initialState" class="initial-state">
                <i class="ri-time-line"></i> <!-- Stunden-Icon -->
                <i class="ri-phone-line"></i> <!-- Telefon-Icon -->
                <i class="ri-home-line"></i> <!-- Haus-Icon -->
                <!-- <i class="ri-live-line"></i>-->
            </div>
            <!-- Chart Container -->
            <div id="loadingSpinner" class="text-center" style="display: none; height: 50px;">
                <i class="fas fa-spinner fa-spin fa-3x"></i> <!-- Hier wird die Größe festgelegt -->
                <p>Laden...</p>
            </div>
            <div id="userWorkChart"></div>
            <div id="userActionChart"></div>
            <div id="noDataMessage">
                <i class="ri-sad-line"></i><br>
                <p style="font-size: 1.5em;">Bitte wähle deine Anfrage.</p>
            </div>
            <p id="totalWorkTime">Insgesamte Arbeitszeit an diesem Tag: </p>
            <!-- Weitere Details können hier hinzugefügt werden -->

            <div id="noUserSelected">
                <i class="ri-ghost-line"></i>
                <p>Kein Benutzer ausgewählt.</p>
            </div>
        </div>
    </div>
    <?php } ?>
</body>

</html>