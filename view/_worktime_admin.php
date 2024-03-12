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

?>


<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>Worki Worki</title>
    <link rel="stylesheet" type="text/css" href="view/includes/worktime_admin.css?=v1.1">

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>


</head>

<body>

    <script src="view/includes/js/app_worktime.js?=v1.996"></script>
    <script src="view/includes/js/app_worktime_tiles.js?=v1.0"></script>
    <div class="leftContainer">
        <button id="liveDataButton" class="live-data-btn">
            <i class="bi bi-x-diamond"></i> Call Data
        </button>
        <div id="userListContainer">
            <ul id="userList"><!-- Nutzer werden hier aufgelistet --></ul>
        </div>
    </div>

    <div class="rightContainer">
        <div class="statusContainer">
            <div class="statusBox" data-toggle="tooltip" data-placement="bottom" title="Anzahl der aktuell aktiven Anrufe.">
                <i class="ri-phone-fill"></i>
                <div class="statusInfo">
                    <span id="activeCalls">0</span>
                    <p>Aktive Anrufe</p>
                </div>
            </div>
            <div class="statusBox" data-toggle="tooltip" data-placement="bottom" title="Anzahl der Anrufe in der Hotline. (Angenommen oder wartend.)">
                <i class="ri-time-fill"></i>
                <div class="statusInfo">
                    <span id="hotlineQueue">0</span>
                    <p>Hotline Queue</p>
                </div>
            </div>
            <div class="statusBox" data-toggle="tooltip" data-placement="bottom" title="Last outgoing Call - Wann wurde die letzte Person angerufen.">
                <i class="ri-time-fill"></i>
                <div class="statusInfo">
                    <span id="LoC">00:00</span>
                    <p>LoC</p>
                </div>
            </div>
            <div class="statusBox" data-toggle="tooltip" data-placement="bottom" title="Last incoming Call - Wann hat die letzte Person angerufen.">
                <i class="ri-time-fill"></i>
                <div class="statusInfo">
                    <span id="LiC">00:00</span>
                    <p>LiC</p>
                </div>
            </div>
            <div class="statusBox" data-toggle="tooltip" data-placement="bottom" title="Call to Hangup Incoming. Wie viel Zeit vergeht, von Anruf angenommen bis aufgelegt.">
                <i class="ri-time-fill"></i>
                <div class="statusInfo">
                    <span id="CTHi">0min</span>
                    <p>CTHi</p>
                </div>
            </div>
            <div class="statusBox" data-toggle="tooltip" data-placement="bottom" title="Call to Hangup Outgoing. Wie viel Zeit vergeht, von Anruf angenommen bis aufgelegt.">
                <i class="ri-time-fill"></i>
                <div class="statusInfo">
                    <span id="CTHo">0min</span>
                    <p>CTHo</p>
                </div>
            </div>
            <div class="statusBox" data-toggle="tooltip" data-placement="bottom" title="Call to Pickup Incoming. Wie viel Zeit vergeht, bis ein Anruf angenommen wird.">
                <i class="ri-time-fill"></i>
                <div class="statusInfo">
                    <span id="CTPi">0min</span>
                    <p>CTPi</p>
                </div>
            </div>
            <div class="statusBox" data-toggle="tooltip" data-placement="bottom" title="Call to Pickup Outgoing. Wie viel Zeit vergeht, bis ein Anruf angenommen wird.">
                <i class="ri-time-fill"></i>
                <div class="statusInfo">
                    <span id="CTPo">0min</span>
                    <p>CTPo</p>
                </div>
            </div>
        </div>
        <div id="callTilesContainer"></div>
        <div id="userInfoContainer">
            <div id="selectedUsernameDisplay">Wähle einen Benutzer</div>
            <div id="dateDisplayContainer">
                <i class="bi bi-caret-left-fill" id="prevDate"></i>
                <div id="dateDisplay">Datum</div>
                <i class="bi bi-caret-right-fill" id="nextDate"></i>
            </div>

            <div id="dateButtonsContainer">
                <button id="dayButton">Tag</button>
                <button id="weekButton">Woche</button>
                <button id="monthButton">Monat</button>
            </div>
            <div id="dataContainer">
            </div>
            <div id="tilesContainer">
                <div class="Chart1">
                    <div class="spinner-grow text-primary" role="status"><span class="sr-only">Laden...</span></div>
                </div>
                <div class="Chart2">
                    <div class="spinner-grow text-primary" role="status"><span class="sr-only">Laden...</span></div>
                </div>
                <div class="Chart3">
                    <div class="spinner-grow text-primary" role="status"><span class="sr-only">Laden...</span></div>
                </div>
                <div class="Chart4">
                    <div class="spinner-grow text-primary" role="status"><span class="sr-only">Laden...</span></div>
                </div>
                <div class="Chart5">
                    <div class="spinner-grow text-primary" role="status"><span class="sr-only">Laden...</span></div>
                </div>
                <div class="Chart6">
                    <div id="summaryContainer">
                        <h3>Im gewählten Zeitraum:</h3>
                        <div id="summaryContent">
                            <!-- Hier werden die zusammengefassten Daten eingefügt -->
                        </div>
                    </div>
                </div>
            </div>

        </div>



    </div>

</body>

</html>