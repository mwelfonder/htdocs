$(document).ready(function () {
    $('#toggle-users-panel').click(function () {
        $('#users-panel').toggleClass('visible');
    });





});





$(document).ready(function () {


    var isPaused = false; // Zustandsvariable für den Countdown

    $('#countdownWrapper').click(function () {
        if (isPaused) {
            // Countdown fortsetzen
            timerId = setInterval(updateCountdown, 1000);
            $('#countdownIcon').removeClass('fa-play').addClass('fa-pause');
        } else {
            // Countdown pausieren
            clearInterval(timerId);
            $('#countdownIcon').removeClass('fa-pause').addClass('fa-play');
        }
        $(this).toggleClass('paused');
        isPaused = !isPaused; // Zustand umschalten
    });


    var map = L.map('gps_map_20240128').setView([51.1657, 10.4515], 6);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    function onLocationFound(e) {
        var radius = e.accuracy / 2;
    }

    function onLocationError(e) {
        alert(e.message);
    }

    map.on('locationfound', onLocationFound);
    map.on('locationerror', onLocationError);

    map.locate({ setView: true, maxZoom: 16 });

    var userMarkers = {}
    var timeLeft = 10;
    var timerId = setInterval(updateCountdown, 1000);
    var usersWithGpsLocation = [];

    function updateCountdown() {
        if (!isPaused) {
            timeLeft--;
            $('#countdownTimer').text(timeLeft);
            var progress = (10 - timeLeft) * 10;
            $('#countdownCircle').css('background', `conic-gradient(orange ${progress}%, white ${progress}% 100%)`);

            if (timeLeft <= 0) {
                timeLeft = 11; // Countdown neu starten
                updateUserLocations(usersWithGpsLocation.map(user => user.username));
                console.log("Countdown beendet und neugestartet!");
            }
        }
    }

    function updateUserLocations(usernames) {
        console.log("Gesendete Benutzernamen:", usernames);
        if (usernames.length === 0) {
            console.log("Keine Benutzernamen vorhanden, updateUserLocations wird übersprungen.");
            return;
        }

        $.ajax({
            url: 'view/load/gpsmap_load.php',
            type: 'POST',
            data: {
                func: 'getUserLocations',
                usernames: usernames
            },
            success: function (response) {
                console.log("Rohantwort vom Server:", response);
                for (var oldUsername in userMarkers) {
                    map.removeLayer(userMarkers[oldUsername]);
                }
                userMarkers = {}; // Leeren Sie das Objekt

                var locations = JSON.parse(response);
                for (var username in locations) {
                    var gpsLoc = locations[username];
                    var latLng = [gpsLoc.lat, gpsLoc.lon];
                    var marker = L.marker(latLng, {
                        icon: L.divIcon({
                            className: 'custom-div-icon',
                            html: "<div style='background-color: orange;' class='marker-pin'></div><i class='fa fa-car' style='color: orange; font-size: 24px;'></i>",
                            iconSize: [40, 52], // Größere Icon-Größe
                            iconAnchor: [20, 52]
                        })
                    }).addTo(map).bindPopup(username);
                    userMarkers[username] = marker;
                }
            },
            error: function (xhr, status, error) {
                console.error("Fehler bei der AJAX-Anfrage:", error);
            }
        });
    }

    $.ajax({
        url: 'view/load/gpsmap_load.php',
        type: 'POST',
        data: {
            func: 'getUsers'
        },
        success: function (response) {
            try {
                var usersObject = JSON.parse(response);
                var usersArray = Object.values(usersObject);
                usersWithGpsLocation = usersArray.filter(user => user.gps_loc);
                console.log("Benutzer mit GPS-Standort:", usersWithGpsLocation);
            } catch (e) {
                console.error("Fehler beim Parsen der Antwort:", e);
            }
        },
        error: function (xhr, status, error) {
            console.error("Fehler bei der AJAX-Anfrage:", xhr.responseText, status, error);
        }
    });
});
