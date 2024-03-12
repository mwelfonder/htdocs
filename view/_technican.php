<?php

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}
if (!securePage($_SERVER['PHP_SELF'])) {
    die();
}
if (!hasPerm([35])) {
    die();
}

?>
<script src="/view/includes/js/app_technicans.js?v=<?php echo time(); ?>"></script> <!-- JavaScript-Datei -->
<link rel="stylesheet" href="/view/includes/style_technicans.css?v=<?php echo time(); ?>"> <!-- CSS-Datei -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>



<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
</head>

<body>
    <div id="userList" class="user-list"></div>

    <div id="lightbox" class="lightbox" style="display:none;">
        <span class="close" onclick="closeLightbox()">&times;</span>
        <img class="lightbox-content" id="lightboxImg">
    </div>


    <div id="userDetails" class="user-details">
        <div id="selectUserMessage" class="select-user-message" style="display: none;">
            <i class="ri-ghost-line"></i>
            <span>Bitte wähle einen Techniker</span>
        </div>
        <div class="button-bar">
            <button class="btn-tab active" onclick="showTab('appointments')"><i class="bi bi-calendar"></i> Termine</button>
            <button class="btn-tab" onclick="showTab('location')"><i class="bi bi-geo-alt"></i> Location</button>
            <button class="btn-tab" onclick="showTab('statistic')"><i class="ri-bar-chart-2-fill"></i> Statistken</button>
            <button class="btn-tab" onclick="showTab('information')"><i class="ri-bar-chart-2-fill"></i> Informationen</button>
            <button class="btn-tab" onclick="showTab('workingHours')"><i class="bi bi-clock"></i> Arbeitszeiten</button>
        </div>
        <div class="appointments-container">
            <div id="appointmentData" class="appointment-data">
                <!-- Hier werden die Termin-Daten angezeigt -->
            </div>
            <div id="appointmentDetails" class="appointment-details">
                <!-- Hier werden die Details des ausgewählten Termins angezeigt -->
            </div>
        </div>
        <div id="map"></div>
        <div class="stat_con">
            <!-- <div id="selectedUsernameDisplay"></div>-->
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

            <div class="statistics-tiles">
                <div class="tile" id="averageRating">Durchschnittliche Bewertung: <span>0</span></div>
                <div class="tile" id="done">Erledigt: <span>0</span></div>
                <div class="tile" id="missing">Fehlend: <span>0</span></div>
                <div class="tile" id="abbruch">Abbruch: <span>0</span></div>
                <div class="tile" id="totalDistance">Gesamtdistanz: <span>0</span> km</div>
            </div>
            <div class="chart-container">
                <div class="chart-box">
                    <h2>Hbg Stats</h2>
                    <div id="hbgStatsChart"></div>
                </div>
                <div class="chart-box">
                    <h2>Average Rating</h2>
                    <div id="averageRatingChart"></div>
                </div>
                <div class="chart-box">
                    <h2>Total Distance</h2>
                    <div id="totalDistanceChart"></div>
                </div>
            </div>







            <!--<div id="tilesContainer">
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

                        </div>
                    </div>
                </div>
            </div>-->
        </div>
        <div class="info_con">
            <div class="user-data">
                <!-- Dynamisch geladene Benutzerdetails werden hier angezeigt -->
            </div>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-date">01. Januar 2024</div>
                    <div class="timeline-content">
                        <h3>Eintragstitel 1</h3>
                        <p>Beschreibung des Ereignisses oder der Aktivität.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-date">02. Februar 2024</div>
                    <div class="timeline-content">
                        <h3>Eintragstitel 2</h3>
                        <p>Weitere Details über das Ereignis oder Aktivitäten.</p>
                    </div>
                </div>
                <!-- Weitere Timeline-Einträge hier -->
            </div>
        </div>



    </div>


</body>

</html>