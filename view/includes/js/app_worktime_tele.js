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
                    $('#userWorkChart').css('height', '350px');
                    $('#userWorkChart').show();
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
                    $('#userActionChart').show();
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
                    $('#userActionChart').show();
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
    }




});


