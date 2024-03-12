$(document).ready(function () {
    loadUsers();
    $('.sipGateWrapper').hide();
    $('#selectUserMessage').show();
    $('.button-bar').hide();
    $('.stat_con').hide();
    $('.info_con').hide();   

});

let validUserIds = [];
let currentUserId = null;



function loadUsers() {
    $.ajax({
        url: 'view/load/technicans_load.php',
        type: 'POST',
        data: { func: 'loadUsers' },
        success: function (users) {
            console.log(users); // Hier ausgeben, um die Struktur zu überprüfen
            validUserIds = users.map(user => user.user_id);
            if (Array.isArray(users)) {
                const userList = $('#userList');
                userList.empty();
                users.forEach(user => {
                    const userItem = $('<div></div>')
                        .attr('id', `user-${user.user_id}`)
                        .addClass('user-list-item')
                        .text(user.username)
                        .click(() => onUserClick(user.user_id));
                    $('#userList').append(userItem);
                });
            } else {
                console.error('Die empfangenen Daten sind kein Array');
            }
        },
        error: function (xhr, status, error) {
            console.error("Ein Fehler ist aufgetreten: " + error);
        }
    });
}

function showUserDetails(user) {
    $('#userDetails').text(`Benutzerdetails: ${user.username}`); // Erweitern Sie dies entsprechend
}


function showTab(tabName) {

    $('#appointmentData').hide();
    $('#appointmentDetails').hide();
    $('.stat_con').hide();
    $('.info_con').hide();
    $('#map').hide();

    // Entfernen Sie die 'active' Klassen und leeren Sie die Inhalte
    $('#appointmentData').removeClass('active');
    $('#appointmentDetails').removeClass('active').html('');

    // Logik für den 'appointments' Tab
    if (tabName === 'appointments') {
        loadAppointments();
        $('.stat_con').hide();
        $('.info_con').hide();
        $('#map').hide();
        $('#appointmentDetails').show();
        $('#appointmentData').show(); // Nur den 'appointments' Bereich anzeigen
    }

    if (tabName === 'information') {
        $('.info_con').show();
        loadUserDetails(currentUserId);
    }

    // Logik für den 'location' Tab
    if (tabName === 'location') {
        $('.stat_con').hide();
        $('.user-details-card').hide();
        $('#map').show();
        initMap();
        if (currentUserId) {
            fetchGPSLocation(currentUserId);
        } else {
            console.error('Kein Benutzer ausgewählt');
        }
    }


    if (tabName === 'statistic') {
        $('#selectedUsernameDisplay').text(currentUserId);
        $('.stat_con').show();
        $('.info_con').hide();
        $('#map').hide();
        $('#appointmentData').hide();
        $('#appointmentDetails').hide();
        $('#dateButtons').show();
        updateDateDisplay('day'); // Setzt das Datum auf den aktuellen Tag
        logDateRange(selectedPeriod);
    }


    // Aktiven Tab hervorheben
    $('.btn-tab').removeClass('active'); // Alle Tabs deaktivieren
    $('.btn-tab[onclick="showTab(\'' + tabName + '\')"]').addClass('active'); // Gewählten Tab hervorheben
}

function onUserClick(user_id) {
    if (validUserIds.includes(user_id)) {
        currentUserId = user_id;
        $('#selectUserMessage').hide();
        $('#appointmentData').removeClass('active');
        $('.button-bar').show();
        $('#appointmentData').show();
        $('.user-list-item').removeClass('active');
        $(`#user-${user_id}`).addClass('active');

        loadAppointments();
    } else {
        console.error('Ungültige Benutzer-ID oder Benutzer nicht in der Liste');
        // Optional können Sie hier eine Benachrichtigung an den Benutzer anzeigen
    }
}
function loadUserDetails(userId) {
    $.ajax({
        url: 'view/load/technicans_load.php',
        type: 'POST',
        data: { userId: userId, func: 'loadUserDetails' },
        success: function (user) {
            let userDetailsHTML = `
                    <div class="user-details-card">
                        <div class="detail-item"><i class="bi bi-person-fill"></i><span>${user.fname} ${user.lname}</span></div>
                        <div class="detail-item"><i class="bi bi-house-fill"></i><span>${user.home}, ${user.street}</span></div>
                        <div class="detail-item"><i class="bi bi-calendar-event"></i><span>Geboren: ${user.geb}</span></div>
                        <div class="detail-item"><i class="bi bi-geo-alt-fill"></i><span>Ort: ${user.geb_city}</span></div>
                        <div class="detail-item"><i class="bi bi-calendar-check-fill"></i><span>Eintrittsdatum: ${user.work_start}</span></div>
                        <div class="detail-item"><i class="bi bi-card-list"></i><span>Personalnummer: ${user.pers_nr}</span></div>
                        <div class="detail-item"><i class="bi bi-telephone-fill"></i><span>Private Nummer: ${user.prv_number}</span></div>
                        <div class="detail-item"><i class="bi bi-telephone-forward-fill"></i><span>Arbeits Nummer: ${user.comp_number}</span></div>
                    </div>
                `;
            $('#userDetails .user-data').html(userDetailsHTML);
        },
        error: function (xhr, status, error) {
            console.error("Ein Fehler ist aufgetreten: " + error);
        }
    });
}


function loadAppointments() {

    $.ajax({
        url: 'view/load/technicans_load.php',
        type: 'POST',
        data: { userId: currentUserId, func: 'loadUserApp' },
        success: function (appointmentData) {
            displayAppointments(appointmentData);
        },
        error: function (xhr, status, error) {
            console.log(currentUserId);
            console.error("Ein Fehler ist aufgetreten: " + error);
        }
    });
}

function displayAppointments(data) {
    let today = new Date();
    today.setHours(0, 0, 0, 0); // Setzt die Zeit auf Mitternacht

    let sortedDates = Object.keys(data).sort((a, b) => new Date(b) - new Date(a));
    sortedDates = sortedDates.filter(date => new Date(date) <= today);

    let appointmentsHTML = sortedDates.map(date => {
        return `
        <div class="appointment-card" data-date="${date}">
                <div class="appointment-date">${date}</div>
                <p>Erledigt: ${data[date].Erledigt}</p>
                <p>Offen: ${data[date].Offen}</p>
                <p>Abbruch: ${data[date].Abbruch}</p>
            </div>
        `;
    }).join('');

    $('#appointmentData').html(appointmentsHTML);
    $('#appointmentData').removeClass('active');
    $('#appointmentDetails').removeClass('active').html('');
    // Event-Handler nach dem Hinzufügen der Elemente binden
    $('.appointment-card').click(function () {
        $('#appointmentData').toggleClass('active');
        let date = $(this).data('date');
        loadAppointmentDetails(date, currentUserId);
    });
}


function loadAppointmentDetails(date, userId) {
    console.log('Load App Details');
    $.ajax({
        url: 'view/load/technicans_load.php',
        type: 'POST',
        data: { date: date, userId: userId, func: 'getAppointmentDetails' },
        success: function (details) {
            console.log(details);
            $('#appointmentData').addClass('active');
            $('#appointmentDetails').addClass('active');
            displayAppointmentDetails(details);
        },
        error: function (xhr, status, error) {
            console.error("Ein Fehler ist aufgetreten: " + error);
        }
    });
}

function displayAppointmentDetails(details) {
    let detailsHTML = '<div class="appointment-details-container">';

    details.forEach(detail => {
        detailsHTML += `
            <div class="appointment-detail-card">
                <h5>${detail.homeDetails.street} ${detail.homeDetails.streetnumber}${detail.homeDetails.streetnumberadd ? ', ' + detail.homeDetails.streetnumberadd : ''}</h5>
                <p>Zeit: ${detail.time}</p>
                <p>Status: ${detail.appt_status || 'N/A'}</p>
                <p>Kommentar: ${detail.appt_comment || 'Kein Kommentar'}</p>
                ${detail.appt_file ? `<a href="${detail.appt_file}" target="_blank">Protokoll anzeigen</a>` : ''}
                <div class="errors-container">
                    ${detail.errors.map(error => `
                        <div class="error-card">
                            <p>${error.error}</p>
                            ${error.pic ? `<img src="${error.pic}" alt="Fehlerbild" style="max-width:100%;height:auto;" />` : ''}
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    });

    detailsHTML += '</div>';
    $('#appointmentDetails').html(detailsHTML);
    $('#appointmentData').addClass('active');
    $('#appointmentDetails').addClass('active');

    $('#appointmentDetails').on('click', '.error-card img', function () {
        openLightbox(this.src);
    });
}

function openLightbox(src) {
    document.getElementById('lightboxImg').src = src;
    document.getElementById('lightbox').style.display = 'block';
}

function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
}


function fetchGPSLocation(userId) {
    $.ajax({
        url: 'view/load/technicans_load.php',
        type: 'POST',
        data: { userId: userId, func: 'fetchGPSLocation' },
        success: function (response) {
            if (response.success) {
                updateMapWithGPSData(response.success.lat, response.success.lng);
            } else {
                console.error('Keine GPS-Daten gefunden');
            }
        },
        error: function (xhr, status, error) {
            console.error("Ein Fehler ist aufgetreten: " + error);
        }
    });
}


function updateMapWithGPSData(lat, lng) {
    if (window.myMap) {
        L.marker([lat, lng]).addTo(window.myMap)
            .bindPopup('GPS-Standort')
            .openPopup();

        window.myMap.setView([lat, lng], 17);
    }
}

function initMap() {
    if (!window.myMap) {
        window.myMap = L.map('map').setView([51.505, -0.09], 17); // Standardkoordinaten

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(window.myMap);
    }
}







// Stats Buttons


var currentDate = new Date();
var selectedPeriod = 'day'; // Initial auf 'Tag' gesetzt
let debounceTimer;

function changeDate(step) {
    console.log('Debug: changeDate step' + step);
    if (selectedPeriod === 'day') {
        currentDate.setDate(currentDate.getDate() + step);
    } else if (selectedPeriod === 'week') {
        currentDate.setDate(currentDate.getDate() + (step * 7));
    } else if (selectedPeriod === 'month') {
        // Wenn wir uns im Monatsmodus befinden und vor oder zurück gehen, müssen wir den Monat ändern
        if (step > 0) {
            currentDate.setMonth(currentDate.getMonth() + step, 1); // Setze auf den ersten Tag des nächsten Monats
        } else {
            currentDate.setMonth(currentDate.getMonth() + step); // Setze auf den ersten Tag des aktuellen Monats
            currentDate.setDate(0); // Setze auf den letzten Tag des vorherigen Monats
        }
    }
    updateDateDisplay(selectedPeriod);


    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(function () {
        logDateRange(selectedPeriod);
        console.log('Debug: logDateRange exec selectedPeriod' + selectedPeriod);
        disableButtons();
        console.log('Debug: disableButtons');
    }, 1500); // Wartezeit in Millisekunden
}



function getFirstAndLastDayOfWeek(date) {
    console.log('Debug: getFirstAndLastDayOfWeek date' + date);
    let firstDay = new Date(date);
    firstDay.setDate(firstDay.getDate() - firstDay.getDay() + (firstDay.getDay() === 0 ? -6 : 1)); // Montag als erster Tag der Woche

    let lastDay = new Date(firstDay);
    lastDay.setDate(lastDay.getDate() + 6); // Sonntag als letzter Tag der Woche

    return { firstDay, lastDay };
}

function getFirstAndLastDayOfMonth(date) {
    console.log('Debug: getFirstAndLastDayOfMonth date' + date);
    let firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
    let lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);

    return { firstDay, lastDay };
}



function getWeekNumber(d) {
    console.log('Debug: getWeekNumber date' + d);

    d = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
    d.setUTCDate(d.getUTCDate() + 4 - (d.getUTCDay() || 7));
    var yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
    var weekNo = Math.ceil(((d - yearStart) / 86400000 + 1) / 7);
    return weekNo;
}

function updateDateDisplay(period) {
    console.log('Debug: updateDateDisplay Period' + period);
    var dateString;
    var options;

    switch (period) {
        case 'day':
            options = { weekday: 'short' };
            dateString = currentDate.toLocaleString('de-DE', options) + ', ' + currentDate.toLocaleDateString();
            break;
        case 'week':
            var weekNo = getWeekNumber(currentDate);
            dateString = 'KW ' + weekNo;
            break;
        case 'month':
            dateString = currentDate.toLocaleString('de-DE', { month: 'long' }) + ' ' + currentDate.getFullYear();
            break;
    }

    $('#dateDisplay').text(dateString);
}


$(document).ready(function () {
    $('#dayButton, #weekButton, #monthButton').click(function () {
        console.log('Day/Week/Month Button clicked');
        let period = $(this).attr('id').replace('Button', '');
        setActiveButton(period); // Aktualisiere den ausgewählten Zeitraum
        updateDateDisplay(period);
        logDateRange(period);
        disableButtons();
    });

    $('#prevDate').click(function () {
        console.log('PrevDate');
        changeDate(-1); // Geht einen Tag, eine Woche oder einen Monat zurück
    });

    $('#nextDate').click(function () {
        console.log('NextDate');
        changeDate(1); // Geht einen Tag, eine Woche oder einen Monat vorwärts
    });

});

function disableButtons() {
    console.log('Deaktivere Buttons');
    $('#dayButton, #weekButton, #monthButton, #prevDate, #nextDate').addClass('disabled-button');
}

function enableButtons() {
    console.log('Aktiviere Buttons');
    $('#dayButton, #weekButton, #monthButton, #prevDate, #nextDate').removeClass('disabled-button');
}



// Initialer Aufruf für das heutige Datum
updateDateDisplay(selectedPeriod);

function setActiveButton(period) {
    console.log('Debug: setActiveButton Period' + period);
    $('#dayButton').removeClass('activeButton');
    $('#weekButton').removeClass('activeButton');
    $('#monthButton').removeClass('activeButton');
    selectedPeriod = period;
    switch (period) {
        case 'day':
            $('#dayButton').addClass('activeButton');
            break;
        case 'week':
            $('#weekButton').addClass('activeButton');
            break;
        case 'month':
            $('#monthButton').addClass('activeButton');
            break;
    }
}


function logDateRange(period) {
    console.log('Debug: logDateRange Period' + period);
    let firstDay, lastDay, logEntry;
    let userName = currentUserId;

    switch (period) {
        case 'day':
            logEntry = {
                user: userName,
                date_start: currentDate.toLocaleDateString(),
                date_end: currentDate.toLocaleDateString(),
                case: period
            };
            break;
        case 'week':
            ({ firstDay, lastDay } = getFirstAndLastDayOfWeek(currentDate));
            logEntry = {
                user: userName,
                date_start: firstDay.toLocaleDateString(),
                date_end: lastDay.toLocaleDateString(),
                case: period
            };
            break;
        case 'month':
            ({ firstDay, lastDay } = getFirstAndLastDayOfMonth(currentDate));
            logEntry = {
                user: userName,
                date_start: firstDay.toLocaleDateString(),
                date_end: lastDay.toLocaleDateString(),
                case: period
            };
            break;
    }
    console.log(`user: ${logEntry.user}, date_start: ${logEntry.date_start}, date_end: ${logEntry.date_end}, case: ${logEntry.case}`);
    sendLogDateRangeData(logEntry.user, logEntry.date_start, logEntry.date_end, period);
    setTimeout(function () {
        enableButtons();
    }, 1000); // Wartezeit von 1 Sekunde


}

function removeBackendPath(backendPath) {
    // Entfernen des spezifizierten Pfadteils aus dem Backend-Pfad
    return backendPath.replace('/var/www/vhosts/scan4-gmbh.de/', 'https://');
}

/*
function sendLogDateRangeData(userName, startDate, endDate, period) {
    $.ajax({
        url: 'view/load/technicans_load.php', // Pfad zu Ihrem PHP-Backend-Skript
        type: 'POST',
        data: {
            user: userName,
            date_start: startDate,
            date_end: endDate,
            period: period,
            func: 'processLogDateRange' // Name der Funktion, die im Backend aufgerufen werden soll
        },
        success: function (response) {
            console.log("Antwort vom Backend:", response);

            // Setzen der Daten in die entsprechenden HTML-Elemente
            $('#averageRating span').text(response.average_hbg_rating.toFixed(2));
            $('#abbruch span').text(response.hbg_abbruch);
            $('#done span').text(response.hbg_done);
            $('#missing span').text(response.hbg_missing);

            // Löschen der vorherigen Fehlerdetails
            $('.errorDetails').empty();

            // Hinzufügen der Fehlerdetails
            response.error_details.forEach(function (detail) {
                var errorsHtml = detail.errors.map(function (error) {
                    var errorText = error.error || 'Kein Fehler angegeben';
                    var picHtml = '';
                    if (error.pic) {
                        var adjustedPath = removeBackendPath(error.pic);
                        picHtml = '<br/><img src="' + adjustedPath + '" alt="Fehlerbild" style="max-width:100%; height:auto;">';
                    }
                    return 'Fehler: ' + errorText + picHtml;
                }).join('<br/>');

                var detailHtml = '<div><strong>UID: ' + detail.uid + '</strong><br/>' + errorsHtml + '</div>';
                $('.errorDetails').append(detailHtml);
            });
        },
        error: function (xhr, status, error) {
            console.error("Ein Fehler ist aufgetreten: " + error);
        }
    });
}*/

function sendLogDateRangeData(userName, startDate, endDate, period) {
    $.ajax({
        url: 'view/load/technicans_load.php',
        type: 'POST',
        data: {
            user: userName,
            date_start: startDate,
            date_end: endDate,
            period: period,
            func: 'processLogDateRange'
        },
        success: function (response) {
            console.log("Antwort vom Backend:", response);

            let totalDone = 0, totalMissing = 0, totalAbbruch = 0, totalDistance = 0, totalRating = 0, ratingCount = 0;

            // Umwandlung des Objekts in ein Array und Iteration
            Object.keys(response.daily_stats).forEach(date => {
                const day = response.daily_stats[date];
                totalDone += day.hbg_done;
                totalMissing += day.hbg_missing;
                totalAbbruch += day.hbg_abbruch;
                totalRating += day.average_hbg_rating;
                ratingCount++;
            });

            response.gps_data.forEach(day => {
                totalDistance += day.total_distance;
            });
            const averageRating = totalRating / ratingCount;

            $('#averageRating span').text(averageRating.toFixed(2));
            $('#done span').text(totalDone);
            $('#missing span').text(totalMissing);
            $('#abbruch span').text(totalAbbruch);
            $('#totalDistance span').text(totalDistance.toFixed(2));

            initCharts(response);
        },
        error: function (xhr, status, error) {
            console.error("Ein Fehler ist aufgetreten: " + error);
        }
    });
}


let hbgStatsChartInstance, averageRatingChartInstance, totalDistanceChartInstance;

function initCharts(data) {

    // Zuerst überprüfen, ob eine Instanz existiert, und falls ja, diese zerstören
    if (hbgStatsChartInstance) {
        hbgStatsChartInstance.destroy();
    }
    if (averageRatingChartInstance) {
        averageRatingChartInstance.destroy();
    }
    if (totalDistanceChartInstance) {
        totalDistanceChartInstance.destroy();
    }
    // Umwandlung der Objektdaten in Arrays für die Chart-Darstellung
    const dailyStatsKeys = Object.keys(data.daily_stats);
    const dailyStatsValues = dailyStatsKeys.map(key => {
        const day = data.daily_stats[key];
        return {
            date: key, // Nehmen Sie das Datum aus dem Schlüssel
            ...day // Verteilen Sie den Rest der Tagesdaten
        };
    });

    const hbgStatsOptions = {
        series: [{
            name: 'Erledigt',
            data: dailyStatsValues.map(day => day.hbg_done),
            color: '#00ff00'
        }, {
            name: 'Fehlend',
            data: dailyStatsValues.map(day => day.hbg_missing),
            color: '#0000ff'
        }, {
            name: 'Abbruch',
            data: dailyStatsValues.map(day => day.hbg_abbruch),
            color: '#ff0000'
        }],
        chart: {
            type: 'bar',
            height: 350
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                endingShape: 'rounded'
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        xaxis: {
            categories: dailyStatsValues.map(day => day.date)
        },
        yaxis: {
            title: {
                text: 'Anzahl'
            }
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return val + " Anzahl";
                }
            }
        }
    };

    const averageRatingOptions = {
        series: [{
            name: 'Durchschnittliche Bewertung',
            data: dailyStatsValues.map(day => day.average_hbg_rating)
        }],
        chart: {
            height: 350,
            type: 'line'
        },
        stroke: {
            curve: 'smooth'
        },
        labels: dailyStatsValues.map(day => day.date),
        xaxis: {
            type: 'date'
        },
        yaxis: {
            title: {
                text: 'Bewertung'
            },
            min: 0,
            max: 10
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return val.toFixed(2) + " Punkte";
                }
            }
        }
    };

    const totalDistanceOptions = {
        series: [{
            name: 'Gesamtdistanz',
            data: data.gps_data.map(day => day.total_distance)
        }],
        chart: {
            height: 350,
            type: 'line'
        },
        stroke: {
            curve: 'smooth'
        },
        labels: data.gps_data.map(day => day.date),
        xaxis: {
            type: 'date'
        },
        yaxis: {
            title: {
                text: 'Distanz (km)'
            }
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return val.toFixed(2) + " km";
                }
            }
        }
    };

    // Diagramme zeichnen
    hbgStatsChartInstance = new ApexCharts(document.querySelector("#hbgStatsChart"), hbgStatsOptions);
    hbgStatsChartInstance.render();
    averageRatingChartInstance = new ApexCharts(document.querySelector("#averageRatingChart"), averageRatingOptions);
    averageRatingChartInstance.render();
    totalDistanceChartInstance = new ApexCharts(document.querySelector("#totalDistanceChart"), totalDistanceOptions);
    totalDistanceChartInstance.render();
}
