$(document).ready(function () {
    // Flatpickr-Initialisierung
    var fp = $("#dateRangeDetail").flatpickr({
        mode: "range",
        dateFormat: "Y-m-d",
        locale: "de", // Setzt Deutsch als Sprache
        onClose: function (selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                fetchChartData(dateStr); // Funktion, die AJAX-Anfragen durchführt
            }
        }
    });

    // Öffnet Flatpickr beim Klicken auf das Icon
    $('#calendarIcon').on('click', function () {
        fp.open();
    });

    // AJAX-Anfragen und Chart-Updates
    function fetchChartData(dateRange) {
        var username = $('#userInfoTitle').text().split(' - ')[0].trim(); // Benutzername extrahieren und Leerzeichen entfernen
        console.log("Abfrage für User", username, "!");
        $.ajax({
            url: 'view/load/worktime_load.php',
            type: 'POST',
            data: {
                action: 'fetch_chart_data',
                username: username,
                dateRange: dateRange
            },
            success: function (response) {
                // Datenverarbeitung und Aktualisierung der Charts
                console.log("Antwort", response);
                let data = JSON.parse(response);
                console.log("Antwort Data", data);
                if (data.userActions && Object.keys(data.userActions).length > 0 && data.callData && Object.keys(data.callData).length > 0) {
                    updateCharts(data);
                } else {
                    console.error("Ungültige oder leere Daten empfangen:", data);
                }
            },
            error: function (error) {
                console.error("Fehler bei der Datenanfrage: ", error);
            }
        });
    }


    function updateCharts(data) {
        if (data.userActions && Object.keys(data.userActions).length > 0) {
            renderUserActionsChart(data.userActions);
        } else {
            console.error('User Actions Daten sind leer oder fehlen');
        }

        if (data.callData && Object.keys(data.callData).length > 0) {
            renderCallDataChart(data.callData);
        } else {
            console.error('Call Data Daten sind leer oder fehlen');
        }
    }


    function formatUserActionsData(userActions) {
        const dates = Object.keys(userActions);
        const actions = Object.keys(userActions[dates[0]]);

        const formattedData = {
            labels: dates,
            datasets: actions.map(action => ({
                label: action,
                data: dates.map(date => userActions[date][action]),
            })),
        };

        return formattedData;
    }

    function formatCallData(callData) {
        if (!callData || Object.keys(callData).length === 0) {
            console.error('CallData Daten sind leer oder fehlen');
            return null;
        }

        // Beispiel für eine Umformatierung
        let formattedData = [];
        for (let date in callData) {
            formattedData.push({
                date: date,
                ...callData[date]
            });
        }

        return formattedData;
    }
    function renderUserActionsChart(data) {
        let { categories, series } = formatUserActionsData(data.userActions);

        let options = {
            chart: {
                type: 'bar',
                height: 350
            },
            series: series,
            xaxis: {
                categories: categories
            },
            plotOptions: {
                bar: {
                    horizontal: false
                },
            },
        };

        let chart = new ApexCharts(document.querySelector('#userActionsChart'), options);
        chart.render();
    }

    function renderCallDataChart(data) {
        let { categories, series } = formatCallData(data.callData);

        let options = {
            chart: {
                type: 'bar',
                height: 350
            },
            series: series,
            xaxis: {
                categories: categories
            },
            plotOptions: {
                bar: {
                    horizontal: false
                },
            },
        };

        let chart = new ApexCharts(document.querySelector('#callDataChart'), options);
        chart.render();
    }









});


$(document).ready(function () {
    $('#ri-calendar-2-line').hide();
    $('#live-indicator').hide();

    $('#call-tile').hide();
    $('[data-toggle="tooltip"]').tooltip({
        placement: 'bottom'
    });
    $('#totalWorkTime p').hide();
    $('#userList').on('click', '.user-item', function () {
        var userName = $(this).find('.username').text();
        $('#selectedUsername').text(userName);
        $('#detailsContainer').addClass('visible');
        $('#detailsContainer').show();
        $('#noUserSelected').hide(); // Verbirgt den 'Kein Benutzer ausgewählt'-Bereich
        $('#totalWorkTime').hide();
        $('#loadingSpinner').hide();
        $('#userWorkChart').empty(); // Entferne vorherige Charts
        $('#userWorkChart').hide();
        $('#userActionChart').empty(); // Entferne vorherige Charts
        $('#userActionChart').hide();
        $('#noDataMessage i').addClass('ri-sad-line'); // Fügt das Remixicon hinzu
        $('#noDataMessage p').text('Bitte wähle deine Anfrage.'); // Setzt den Text                    
        $('#noDataMessage i').show(); // Zeige die Nachricht
        $('#noDataMessage p').show(); // Zeige die Nachricht
        $('#initialState').show();
        $('#chartsContainer').hide();
        //userItemClicked(userName); // Aufruf mit dem Benutzernamen als Argument
    });
    // Wenn kein Benutzer ausgewählt ist, wird der 'Kein Benutzer ausgewählt'-Bereich angezeigt
    if ($('#userList').children().length === 0) {
        $('#noUserSelected').show();
    }


    $('#calendarInput').on('change', function () {
        var selectedDate = $(this).val();
        console.log("Ausgewähltes Datum: " + selectedDate);
        // Hier kannst du Funktionen ausführen, die auf das ausgewählte Datum reagieren
    });

    $.ajax({
        url: 'view/load/worktime_load.php',
        type: 'post',
        data: { action: 'fetch_users' },
        dataType: 'json',
        success: function (users) {
            var userList = $('#userList');
            userList.empty();

            users.forEach(function (user) {
                var userItem = $(
                    '<li class="user-item">' +
                    '<span class="username">' + user.username + '</span>' +
                    //'<span class="live-indicator live-click" data-username="' + user.username + '"><i class="ri-calendar-2-line"></i></span>' +
                    '</li>'
                );

                userItem.on('click', function () {
                    $('#selectedUsername').text(user.username);
                    $('#detailsContainer').addClass('visible');
                    $('#detailsContainerDepth').removeClass('visible');
                    var userId = $(this).data('userId');
                });

                userItem.on('click', '.live-click', function (e) {
                    $('#noDataSelected').show();
                    $('#noDataSelected p').text('Bitte wähle deine Anfrage.'); // Setzt den Text     
                    $('#initialState').hide();
                    $('#totalWorkTime').hide();
                    $('#calendarIcon').show();
                    $('#noUserSelected').hide();
                    $('#detailsContainer').removeClass('visible');
                    $('#detailsContainerDepth').addClass('visible');
                    $('#loadingSpinner2').hide();
                    $('#loadingSpinner').hide();
                    $('#userWorkChart').empty(); // Entferne vorherige Charts
                    $('#userWorkChart').hide();
                    $('#selectedUsername').show();
                    $('#userActionChart').empty(); // Entferne vorherige Charts
                    $('#userActionChart').hide();
                    $('#noDataMessage i').hide(); // Verbirgt die Nachricht
                    $('#noDataMessage p').hide(); // Verbirgt die Nachricht
                    $('#chartsContainer').hide();
                    e.stopPropagation();

                    var username = $(this).data('username');
                    console.log("LIVE-Button für User geklickt:", username);

                    // Setze den Namen und die Detailansicht
                    $('#userInfoTitle').html('<h2>' + username + ' - Detailansicht</h2>');
                });

                $('#userList').append(userItem);
            });
        },
        error: function () {
            alert('Fehler beim Laden der Benutzer');
        }
    });

    function updateUserChart(username, hoursData) {
        // Berechnung der Gesamtstunden
        let totalMinutes = hoursData.reduce((acc, curr) => acc + curr.minutes, 0);
        let totalHours = Math.floor(totalMinutes / 60);
        let remainingMinutes = totalMinutes % 60;
        $('#totalWorkTime').text(`Insgesamte Arbeitszeit an diesem Tag: ${totalHours}h ${remainingMinutes}m`);
        console.log("Stunden Daten für Chart:", hoursData);
        // Formatierung der Daten für die ApexChart
        let chartData = hoursData.map(hour => ({
            x: hour.hour, // 'Stunde des Tages'
            y: hour.minutes // 'Gearbeitete Minuten'
        }));

        // Konfiguration der ApexChart
        let options = {
            chart: {
                type: 'bar',
                height: 350,
                toolbar: {
                    show: true,
                    tools: {
                        download: true,
                        selection: true,
                        zoom: true,
                        zoomin: true,
                        zoomout: true,
                        pan: true,
                        reset: true
                    },
                    autoSelected: 'zoom'
                },
                zoom: {
                    enabled: true,
                    type: 'x'
                }
            },
            series: [{
                name: 'Arbeitszeit',
                data: chartData
            }],
            xaxis: {
                type: 'category',
                categories: hoursData.map(hour => `${hour.hour}:00`)
            },
            yaxis: {
                title: {
                    text: 'Gearbeitete Minuten'
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        let hours = Math.floor(val / 60);
                        let minutes = val % 60;
                        return `${hours}h ${minutes}m`;
                    }
                }
            }
        };


        // Initialisierung der ApexChart
        let chart = new ApexCharts(document.querySelector('#userWorkChart'), options);
        chart.render();
        $('#userWorkChart').css('height', '350px');
        $('#userWorkChart').show();
    }

    // Funktion zum Aktualisieren des Charts für einen ausgewählten Benutzer
    function userItemClicked(username) {
        $('#userWorkChart').empty();
        $('#userWorkChart').hide();
        $('#userActionChart').empty(); // Entferne vorherige Charts
        $('#userActionChart').hide();// Entferne vorherige Charts
        var selectedDate = $('#calendarInput').val() || new Date().toISOString().split('T')[0];
        var username = $('#selectedUsername').text(); // Erhalten des aktuell ausgewählten Benutzernamens
        console.log("Sende AJAX-Anfrage mit:", username, selectedDate);
        $.ajax({
            url: 'view/load/worktime_load.php',
            type: 'POST',
            data: {
                action: 'fetch_user_interactions',
                username: username,
                date: selectedDate
            },
            beforeSend: function () {
                // Zeigen Sie die Ladeschnecke an, bevor die Anfrage gesendet wird
                $('#loadingSpinner').show();
            },
            success: function (response) {
                console.log("Empfangene Daten:", response);
                let hoursData = JSON.parse(response);
                if (isEmpty(hoursData)) {
                    // Keine Daten verfügbar, zeige den "Traurigen Remixicon Smiley"
                    $('#userWorkChart').css('height', '0');
                    $('#userWorkChart').empty(); // Entferne vorherige Charts
                    $('#userWorkChart').hide(); // Entferne vorherige Charts
                    $('#noDataMessage i').addClass('ri-sad-line'); // Fügt das Remixicon hinzu
                    $('#noDataMessage p').text('Keine Daten, wähle andere Daten.'); // Setzt den Text                    
                    $('#noDataMessage i').show(); // Zeige die Nachricht
                    $('#noDataMessage p').show(); // Zeige die Nachricht
                    $('#totalWorkTime').hide();
                    $('#initialState').show();
                    $('#chartsContainer').hide();
                } else {
                    // Daten sind verfügbar, zeige den Chart

                    $('#noDataMessage i').hide(); // Verbirgt die Nachricht
                    $('#noDataMessage p').hide(); // Verbirgt die Nachricht
                    $('#totalWorkTime').show();
                    $('#initialState').show();
                    updateUserChart(username, hoursData);
                }
            },
            complete: function () {
                // Verstecken Sie die Ladeschnecke, nachdem die Anfrage abgeschlossen ist
                $('#loadingSpinner').hide();
            },
            error: function (xhr, status, error) {
                console.error("Fehler beim Abrufen der Benutzerinteraktionen: ", status, error);
            }
        });
        console.log("Benutzer " + username + " wurde geöffnet.");
    }

    // Hilfsfunktion zum Überprüfen, ob ein Array leer ist
    function isEmpty(array) {
        return !Array.isArray(array) || array.length === 0;
    }

    $(document).ready(function () {
        $('.ri-time-line').on('click', function () {
            updateActiveIcon(this);
            onTimeIconClick();
        });

        $('.ri-phone-line').on('click', function () {
            updateActiveIcon(this);
            onPhoneIconClick();
        });

        $('.ri-home-line').on('click', function () {
            updateActiveIcon(this);
            onHomeIconClick();
        });
    });

    function updateActiveIcon(clickedIcon) {
        // Entferne die aktive Klasse von allen Icons
        $('.ri-time-line, .ri-phone-line, .ri-home-line').removeClass('icon-active');
        // Füge die aktive Klasse dem geklickten Icon hinzu
        $(clickedIcon).addClass('icon-active');
    }

    function onTimeIconClick() {
        console.log("Zeit-Icon geklickt");
        $('#userActionChart').empty(); // Entferne vorherige Charts
        $('#userActionChart').hide();
        $('#userWorkChart').empty(); // Entferne vorherige Charts
        $('#userWorkChart').hide();
        $('#noDataMessage i').hide(); // Verbirgt die Nachricht
        $('#noDataMessage p').hide(); // Verbirgt die Nachricht
        $('#loadingSpinner').hide();
        $('#loadingSpinner').show();
        $('#totalWorkTime').hide();
        // Fügen Sie hier Ihre Logik hinzu
        var userName = $(this).find('.username').text();
        userItemClicked(userName);
    }

    function onPhoneIconClick() {
        console.log("Telefon-Icon geklickt");
        $('#userActionChart').empty(); // Entferne vorherige Charts
        $('#userActionChart').hide();
        $('#userWorkChart').empty(); // Entferne vorherige Charts
        $('#userWorkChart').hide();
        $('#noDataMessage i').hide(); // Verbirgt die Nachricht
        $('#noDataMessage p').hide(); // Verbirgt die Nachricht
        $('#loadingSpinner').hide();
        $('#totalWorkTime').hide();
        var selectedDate = $('#calendarInput').val() || new Date().toISOString().split('T')[0];
        var username = $('#selectedUsername').text(); // oder eine andere Methode, um den Benutzernamen zu erhalten
        $('#noDataMessage i').hide(); // Verbirgt die Nachricht
        $('#noDataMessage p').hide(); // Verbirgt die Nachricht
        // Fügen Sie hier Ihre Logik hinzu 
        $.ajax({
            url: 'view/load/worktime_load.php', // URL deines Backend-Endpunkts
            type: 'POST',
            data: {
                action: 'fetch_phone_data', // oder ein passender Aktionsname
                username: username,
                date: selectedDate
            },
            beforeSend: function () {
                $('#userWorkChart').empty(); // Entferne vorherige Charts
                $('#userWorkChart').hide();
                $('#userActionChart').empty(); // Entferne vorherige Charts
                $('#userActionChart').hide();
                $('#noDataMessage i').hide(); // Verbirgt die Nachricht
                $('#noDataMessage p').hide(); // Verbirgt die Nachricht
                $('#loadingSpinner').hide();
                $('#loadingSpinner').show();
                $('#totalWorkTime').hide();
            },
            success: function (response) {
                // Hier die Logik, um die Daten im ApexChart darzustellen
                console.log("Erhaltene Anrufdaten:", response);
                var callData = JSON.parse(response);
                if (isEmptyOB(callData)) {
                    // Keine Daten verfügbar, zeige den "Traurigen Remixicon Smiley"
                    $('#userWorkChart').css('height', '0');
                    $('#userWorkChart').empty(); // Entferne vorherige Charts
                    $('#userWorkChart').hide(); // Entferne vorherige Charts
                    $('#noDataMessage i').addClass('ri-sad-line'); // Fügt das Remixicon hinzu
                    $('#noDataMessage p').text('Keine Daten, wähle andere Daten.'); // Setzt den Text                    
                    $('#noDataMessage i').show(); // Zeige die Nachricht
                    $('#noDataMessage p').show(); // Zeige die Nachricht
                    $('#totalWorkTime').hide();
                    $('#initialState').show();
                    $('#loadingSpinner').hide();
                } else {
                    $('#loadingSpinner').hide();

                    displayPhoneChart(callData);
                }
            },
            error: function () {
                console.error('Fehler beim Laden der Anrufdaten');
            }
        });
    }

    function onHomeIconClick() {
        var username = $('#selectedUsername').text(); // Erhalten des aktuell ausgewählten Benutzernamens
        var selectedDate = $('#calendarInput').val() || new Date().toISOString().split('T')[0];

        $.ajax({
            url: 'view/load/worktime_load.php',
            type: 'POST',
            data: {
                action: 'fetch_user_log',
                username: username,
                date: selectedDate
            },
            beforeSend: function () {
                $('#userWorkChart').empty(); // Entferne vorherige Charts
                $('#userWorkChart').hide();
                $('#userActionChart').empty(); // Entferne vorherige Charts
                $('#userActionChart').hide();
                $('#noDataMessage i').hide(); // Verbirgt die Nachricht
                $('#noDataMessage p').hide(); // Verbirgt die Nachricht
                $('#loadingSpinner').hide();
                $('#loadingSpinner').show();
                $('#totalWorkTime').hide();
            },
            success: function (response) {
                console.log("Empfangene Daten:", response);
                let userLogs = JSON.parse(response);

                if (isEmptyOB(userLogs)) {
                    // Keine Daten verfügbar, zeige den "Traurigen Remixicon Smiley"
                    $('#userWorkChart').css('height', '0');
                    $('#userWorkChart').empty(); // Entferne vorherige Charts
                    $('#userWorkChart').hide(); // Entferne vorherige Charts
                    $('#noDataMessage i').addClass('ri-sad-line'); // Fügt das Remixicon hinzu
                    $('#noDataMessage p').text('Keine Daten, wähle andere Daten.'); // Setzt den Text                    
                    $('#noDataMessage i').show(); // Zeige die Nachricht
                    $('#noDataMessage p').show(); // Zeige die Nachricht
                    $('#totalWorkTime').hide();
                    $('#initialState').show();
                    $('#loadingSpinner').hide();
                } else {

                    displayUserActionsChart(userLogs);
                }
            },
            complete: function () {
                $('#loadingSpinner').hide(); // Verbergen des Lade-Spinners
            },
            error: function (xhr, status, error) {
                console.error("Fehler beim Abrufen der Benutzerlogs: ", status, error);
            }
        });
    }

    function isEmptyOB(obj) {
        // Prüft, ob das Objekt leer ist
        return obj && Object.keys(obj).length === 0;
    }

    const actionColors = {
        'click phonenumber': '#FF5733', // Beispiel: Orange
        'create customer note': '#33FF57', // Beispiel: Grün
        'created an hbg': '#3357FF', // Beispiel: Blau
        'load homeid': '#FF33F6', // Beispiel: Pink
        'moved an hbg': '#FFFF33', // Beispiel: Gelb
        'storno an appointment': '#33FFFF' // Beispiel: Cyan
    };


    const actionTitles = {
        'click phonenumber': 'Telefonnummer geklickt',
        'create customer note': 'Notiz erstellt',
        'created an hbg': 'HBG erstellt',
        'load homeid': 'HomeID geladen',
        'moved an hbg': 'HBG verschoben',
        'storno an appointment': 'Termin storniert'
    };

    function displayUserActionsChart(userActions) {
        let { series, categories } = formatChartData(userActions);

        let options = {
            chart: {
                type: 'bar',
                height: 350,
                toolbar: {
                    show: true,
                    tools: {
                        download: true,
                        selection: true,
                        zoom: true,
                        zoomin: true,
                        zoomout: true,
                        pan: true,
                        reset: true
                    },
                    autoSelected: 'zoom'
                },
                zoom: {
                    enabled: true,
                    type: 'x',  // Zoom vom Typ 'x', 'y' oder 'xy'
                }
            },
            series: series,
            xaxis: {
                categories: categories
            },
            colors: Object.values(actionColors) // Verwenden der Farben für die Serien
        };

        let chart = new ApexCharts(document.querySelector('#userActionChart'), options);
        chart.render();
        $('#userActionChart').show();
    }


    function formatChartData(userActions) {
        let series = {};
        let categories = new Set();

        // Sammeln aller Stunden
        for (let hour in userActions) {
            categories.add(hour + ':00');
        }

        // Initialisierung der Serien
        for (let action in actionTitles) {
            series[action] = {
                name: actionTitles[action],
                data: Array.from(categories).map(() => 0) // Initialisiert alle Datenpunkte mit 0
            };
        }

        // Datenpunkte mit tatsächlichen Werten füllen
        for (let hour in userActions) {
            let hourData = userActions[hour];
            let hourIndex = Array.from(categories).indexOf(hour + ':00');

            for (let action in hourData) {
                series[action].data[hourIndex] = hourData[action];
            }
        }

        return { series: Object.values(series), categories: Array.from(categories) };
    }




    function displayPhoneChart(callData) {
        // Beispiel für das Format der callData:
        // callData = [{ hour: '08', incoming: 5, outgoing: 3 }, { hour: '09', incoming: 2, outgoing: 6 }, ...];

        let categories = callData.map(data => `${data.hour}:00`);
        let incomingSeries = callData.map(data => data.incoming);
        let outgoingSeries = callData.map(data => data.outgoing);
        let incomingMissedSeries = callData.map(data => data.missed_incoming);
        let outgoingMissedSeries = callData.map(data => data.missed_outgoing);
        let HotlineIncoming = callData.map(data => data.hotline_incoming);
        let HotlineMissed = callData.map(data => data.missed_hotline);

        let options = {
            chart: {
                type: 'bar',
                height: 350
            },
            series: [{
                name: 'Eingehende Anrufe',
                data: incomingSeries
            }, {
                name: 'Ausgehende Anrufe',
                data: outgoingSeries
            }, {
                name: 'Einghend Verpasst',
                data: incomingMissedSeries
            }, {
                name: 'Ausgehende Verpasst',
                data: outgoingMissedSeries
            }, {
                name: 'Hotline Verpasst',
                data: HotlineMissed
            }, {
                name: 'Hotline Eingehend',
                data: HotlineIncoming
            }],
            xaxis: {
                categories: categories
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    endingShape: 'rounded'
                },
            },
            dataLabels: {
                enabled: false
            },
            yaxis: {
                title: {
                    text: 'Anzahl der Anrufe'
                }
            }
        };

        let chart = new ApexCharts(document.querySelector('#userActionChart'), options);
        chart.render();
        $('#userActionChart').show();
    }




    // Funktion zum Abrufen und Anzeigen der Anrufdaten
    function fetchAndUpdateCallData() {
        $.ajax({
            url: 'view/load/worktime_load.php',
            type: 'POST',
            data: { action: 'fetch_livephone_data' },
            success: function (response) {
                var data = JSON.parse(response); // Stelle sicher, dass die Antwort geparst wird
                updateCallStatus(data); // Rufe updateCallStatus mit den empfangenen Daten auf
                updateCallTiles(data.calls);
            },
            error: function () {
                console.error('Fehler beim Laden der Anrufdaten');
            }
        });
    }


    function updateCallTiles(activeCalls) {
        var callTilesContainer = $('#callTilesContainer');
        var existingCallIds = callTilesContainer.find('.call-tile').map(function () {
            return this.id.replace('call_tile_', ''); // Entfernt 'call_tile_' vom id-String
        }).get();

        // Entferne Kacheln für Anrufe, die nicht mehr aktiv sind
        existingCallIds.forEach(function (callId) {
            if (!activeCalls.some(call => call.callId === callId)) {
                $('#call_tile_' + callId).remove();
            }
        });

        // Füge Kacheln für neue aktive Anrufe hinzu
        activeCalls.forEach(function (call) {
            if (!existingCallIds.includes(call.callId)) {
                addCallTile(call);
            }
        });
    }


    const phoneToNameMapping = {
        "4972198191541": "Mattia Bonasera",
        "4972198191542": "Michael Winter",
        "4972198191543": "Lena Golembowski",
        "4972198191544": "Ben Getschmann",
        "4972198191545": "Silvia Rodriguez",
        "4972198191546": "Charelle Stascheit",
        "4972198191547": "Jens Kohl",
        "4972198191548": "Steffi Könitz",
        "4972198191549": "Hotline",
        "4915792454480": "Dawid Cencelewicz",
        "4915792454481": "Steffi Könitz",
        "4915792454482": "Jens Kohl",
        "4915792454483": "Charelle Stascheit",
        "4915792454484": "Lena Golembowski",
        "4915792454485": "Michael Winter",
        "4915792454486": "Silvia Rodriguez",
        "4915792454487": "Doreen Chapman",
        "4915792454488": "Carsten Floeter",
        "4915792454489": "Muneeb Mustafa"
    };


    function updateCallStatus(data) {
        if (data && typeof data === 'object') {
            var activeCalls = data.activeCalls || 0;
            var hotlineCustomers = data.hotlineCustomers || 0;

            $('#activeCalls').text(activeCalls);
            $('#hotlineQueue').text(hotlineCustomers);

            data.calls.forEach(function (call) {
                var timerElement = document.querySelector('#timer_' + call.callId);
                if (timerElement) {
                    // Der Anruf ist noch aktiv, Timer läuft weiter
                    var currentAnswerTime = new Date(timerElement.dataset.answertime);
                    var newAnswerTime = new Date(call.answerTime);
                    if (currentAnswerTime.getTime() !== newAnswerTime.getTime()) {
                        // Die Antwortzeit hat sich geändert, aktualisiere das Element
                        timerElement.dataset.answertime = call.answerTime;
                        updateTimer(call.callId, newAnswerTime);
                    }
                } else {
                    // Neuer Anruf, füge die Anrufkachel hinzu und starte den Timer
                    addCallTile(call);
                    updateTimer(call.callId, new Date(call.answerTime));
                }
            });
        } else {
            console.error('Unerwartetes Antwortformat:', data);
        }
    }

    function addCallTile(call) {
        var callTilesContainer = $('#callTilesContainer');
        var participant1Name, participant2Name, conversationText;

        // Überprüfe, ob einer der Teilnehmer fehlt
        if (!call.participants[0].phoneNumber || !call.participants[1].phoneNumber) {
            // Setze Namen leer und den Text auf "HOTLINE", wenn ein Teilnehmer fehlt
            participant1Name = '';
            participant2Name = 'HOTLINE';
            conversationText = '';
        } else {
            // Weise Namen zu, wenn beide Teilnehmer vorhanden sind
            participant1Name = phoneToNameMapping[call.participants[0].phoneNumber] || call.participants[0].phoneNumber;
            participant2Name = phoneToNameMapping[call.participants[1].phoneNumber] || call.participants[1].phoneNumber;
            conversationText = "im Gespräch mit:";
        }
        // Erstelle und füge die Anrufkachel hinzu, wenn keine Informationen fehlen
        var callTileHtml = `
        <div class="call-tile" id="call_tile_${call.callId}" data-callid="${call.callId}">
                    <div class="call-icon">
                        <i class="ri-customer-service-2-fill"></i>
                    </div>
                    <div class="call-info">
                        <div class="timer-and-icon">
                            <i class="ri-time-line"></i> <span id="timer_${call.callId}" data-answertime="${call.answerTime}">00:00:00</span>
                            <i class="ri-phone-line call-end-icon" data-callid="${call.callId}"></i>
                        </div>
                        <p class="participant-number">${participant1Name}</p>
                        <p class="conversation-text">${conversationText}</p>
                        <p class="participant-number">${participant2Name}</p>
                    </div>
                </div>
            `;
        callTilesContainer.append(callTileHtml);
    }
    $('#callTilesContainer').on('click', '.call-tile', function () {
        var clickedCallId = $(this).data('callid');
        console.log('Clicked Call ID:', clickedCallId);
    });
    function fetchParticipantInfo(callId, callback) {
        $.ajax({
            url: 'view/load/worktime_load.php',
            type: 'POST',
            data: { action: 'fetch_participant_info', callId: callId },
            success: function (response) {
                var data = JSON.parse(response);
                callback(data);
            },
            error: function () {
                console.error('Fehler beim Abrufen der Teilnehmerinformationen');
            }
        });
    }

    function updateTimer(callId, answerTime) {
        var timerElement = document.querySelector('#timer_' + callId);
        if (timerElement) {
            var startTime = answerTime;
            setInterval(function () {
                var currentTime = new Date();
                var secondsElapsed = Math.floor((currentTime - startTime) / 1000);
                timerElement.textContent = formatTime(secondsElapsed);
            }, 1000);
        }
    }

    function formatTime(seconds) {
        var hours = Math.floor(seconds / 3600);
        seconds %= 3600;
        var minutes = Math.floor(seconds / 60);
        seconds %= 60;
        return hours + 'h ' + minutes + 'm ' + seconds + 's';
    }
    fetchAndUpdateCallData();
    setInterval(fetchAndUpdateCallData, 5000);

    $(document).on('click', '.call-end-icon', function () {
        var callId = $(this).data('callid');
        // Hier wird eine AJAX-Anfrage an das Backend gesendet
        $.ajax({
            url: 'view/load/worktime_load.php',
            type: 'POST',
            dataType: 'json',
            data: { action: 'stop_call', callId: callId },
            success: function (response) {
                console.log('Call beendet:', response);
            },
            error: function () {
                console.error('Fehler beim Senden der Call ID');
            }
        });
    });





    $('#sipgateOption').on('click', function () {
        console.log('Sipgate ausgewählt');
        sipgateFunction();
    });

    $('#terminOption').on('click', function () {
        console.log('Termin ausgewählt');
        terminFunction();
    });

    $('#arbeitszeitOption').on('click', function () {
        console.log('Arbeitszeit ausgewählt');
        arbeitszeitFunction();
    });

    function sipgateFunction() {
        // Logik für Sipgate
        $('#noDataSelected').hide();
        $('#loadingSpinner2').show();
    }

    function terminFunction() {
        // Logik für Termin
        $('#noDataSelected').hide();
        $('#loadingSpinner2').show();
    }

    function arbeitszeitFunction() {
        // Logik für Arbeitszeit
        $('#noDataSelected').hide();
        $('#loadingSpinner2').show();
    }

    function fetchAndDisplayCallStats(direction) {
        $.ajax({
            url: 'view/load/worktime_load.php',
            type: 'POST',
            data: { action: 'fetch_call_stats', direction: direction },
            success: function (response) {
                const callStats = JSON.parse(response);
                displayPieChart(callStats, direction);
            },
            error: function () {
                console.error('Fehler beim Laden der Anrufdaten');
            }
        });
    }

    function displayPieChart(callStats, direction) {
        const chartId = direction === 'in' ? '#cpdiChart' : '#cpdoChart';

        if ($(chartId).length === 0) {
            console.error(`Element ${chartId} nicht gefunden.`);
            return;
        }
        const options = {
            series: [callStats.answered, callStats.missed],
            chart: {
                type: 'pie',
            },
            labels: ['Angenommen', 'Verpasst'],
        };

        const chart = new ApexCharts(document.querySelector(chartId), options);
        chart.render();
    }




    $(document).ready(function () {
        $('#showChartsButton').click(function () {
            // Zeigt/Verbirgt den Container für die Charts
            $('#detailsContainerDepth').removeClass('visible');
            $('#detailsContainer').hide();
            $('#chartsContainer').toggle();

            // Lädt die Daten und zeigt die Charts an, wenn sie sichtbar sind
            if ($('#chartsContainer').is(':visible')) {
                fetchAndDisplayCallStats('in');
                fetchAndDisplayCallStats('out');
            }
        });
    });









    function formatDuration(duration) {
        var minutes = Math.floor(duration);
        var seconds = Math.floor((duration - minutes) * 60);
        return minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0') + 'm';
    }

    $(document).ready(function () {
        // Funktion zum Abrufen und Anzeigen der durchschnittlichen Anrufdauer
        function fetchAndDisplayAverageCallDuration(direction) {
            $.ajax({
                url: 'view/load/worktime_load.php',
                type: 'POST',
                data: { action: 'fetch_call_cth', direction: direction },
                dataType: 'json',
                success: function (response) {
                    var formattedDuration = formatDuration(response.averageDuration);
                    if (direction === 'in') {
                        $('#CTHi').text(formattedDuration);
                    } else {
                        $('#CTHo').text(formattedDuration);
                    }
                },
                error: function (err) {
                    console.error('Fehler beim Abrufen der Anrufdaten: ', err);
                }
            });
        }

        // Abrufen der durchschnittlichen Anrufdauer für eingehende und ausgehende Anrufe
        fetchAndDisplayAverageCallDuration('in');
        fetchAndDisplayAverageCallDuration('out');
    });


    $(document).ready(function () {
        // Funktion zum Abrufen und Anzeigen der durchschnittlichen Anrufdauer
        function fetchAndDisplayLastCallTime(direction) {
            $.ajax({
                url: 'view/load/worktime_load.php',
                type: 'POST',
                data: { action: 'fetch_last_call', direction: direction },
                dataType: 'json',
                success: function (response) {
                    console.log(response);
                    var lastCallTime = response.lastCallTime;
                    console.log(lastCallTime);
                    if (!lastCallTime) {
                        lastCallTime = '00:00';
                    }

                    // Anzeige der letzten Anrufzeit, je nach Richtung
                    if (direction === 'in') {
                        $('#LiC').text(lastCallTime);
                    } else {
                        $('#LoC').text(lastCallTime);
                    }
                },
                error: function (err) {
                    console.error('Fehler beim Abrufen der Anrufdaten: ', err);
                }
            });
        }


        // Abrufen der durchschnittlichen Anrufdauer für eingehende und ausgehende Anrufe
        fetchAndDisplayLastCallTime('in');
        fetchAndDisplayLastCallTime('out');
        setInterval(fetchAndDisplayLastCallTime('in'), 5000);
        setInterval(fetchAndDisplayLastCallTime('out'), 5000);
    });



    // Funktion zum Abrufen und Anzeigen der durchschnittlichen Zeit bis zum Annehmen eines Anrufs
    function fetchAndDisplayAverageTimeToPickup(direction) {
        $.ajax({
            url: 'view/load/worktime_load.php',
            type: 'POST',
            data: { action: 'fetch_call_ctp', direction: direction },
            dataType: 'json',
            success: function (response) {
                var formattedDuration = formatDuration(response.averageTimeToPickup);
                if (direction === 'in') {
                    $('#CTPi').text(formattedDuration);
                } else {
                    $('#CTPo').text(formattedDuration);
                }
            },
            error: function (err) {
                console.error('Fehler beim Abrufen der Daten: ', err);
            }
        });
    }

    // Abrufen der durchschnittlichen Zeit bis zum Annehmen eines Anrufs für eingehende und ausgehende Anrufe
    fetchAndDisplayAverageTimeToPickup('in');
    fetchAndDisplayAverageTimeToPickup('out');









});


