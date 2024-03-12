
$(document).ready(function () {
    $('.statusContainer').toggle();
    $('.statusBox').toggle();
    $('#call-tile').hide();
    $('[data-toggle="tooltip"]').tooltip({
        placement: 'bottom'
    });
    $('#totalWorkTime p').hide();
    $('#userList').on('click', '.user-item', function () {
        var userName = $(this).find('.username').text();
        $('#selectedUsername').text(userName);
        //userItemClicked(userName); // Aufruf mit dem Benutzernamen als Argument
    });
    // Wenn kein Benutzer ausgewählt ist, wird der 'Kein Benutzer ausgewählt'-Bereich angezeigt
    if ($('#userList').children().length === 0) {
    }



    // Auslesen vom backend von allen Usern mit der Telefonisten Berechtigung.
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
                $('#userList').append(userItem);
            });
        },
        error: function () {
            alert('Fehler beim Laden der Benutzer');
        }
    });
    // ÜBer mir das auslesen vom backend von allen Usern mit der Telefonisten Berechtigung.


    // Unter mir Ansicht von Monat / Tag / Woche mit der on Userclick function.

    // Benutzerklick-Event
    $('#userList').on('click', '.user-item', function () {
        var userName = $(this).find('.username').text();
        $('#selectedUsernameDisplay').text(userName);
        $('#dateButtons').show();
        updateDateDisplay('day'); // Setzt das Datum auf den aktuellen Tag
        logDateRange(selectedPeriod);
    });

    // Wenn keine Benutzerauswahl getroffen wurde
    if ($('#userList').children().length === 0) {
        $('#selectedUsernameDisplay').text('Wähle einen Benutzer');
        $('#dateDisplay').text('');
        $('#dateButtons').hide();
    } else {
        $('#dateButtons').show();
    }

    // Über mir Ansicht von Monat / Tag / Woche mit der on Userclick function.


    //Unter mir die function zum switchen der Tag/ WOche / Monat im userDate

    var currentDate = new Date();
    var selectedPeriod = 'day'; // Initial auf 'Tag' gesetzt
    let debounceTimer;

    function changeDate(step) {
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
            disableButtons();
        }, 1500); // Wartezeit in Millisekunden
    }



    function getFirstAndLastDayOfWeek(date) {
        let firstDay = new Date(date);
        firstDay.setDate(firstDay.getDate() - firstDay.getDay() + (firstDay.getDay() === 0 ? -6 : 1)); // Montag als erster Tag der Woche

        let lastDay = new Date(firstDay);
        lastDay.setDate(lastDay.getDate() + 6); // Sonntag als letzter Tag der Woche

        return { firstDay, lastDay };
    }

    function getFirstAndLastDayOfMonth(date) {
        let firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
        let lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);

        return { firstDay, lastDay };
    }


    let worktimeData = [];
    let appData = [];
    let sipgateData = [];

    function logDateRange(period) {
        let firstDay, lastDay, logEntry;
        let userName = $('#selectedUsernameDisplay').text(); // Holt den Nutzernamen aus dem Element
        destroyAllCharts();

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

        $.ajax({
            url: 'view/load/worktime_load.php',
            type: 'POST',
            data: {
                user: logEntry.user,
                date_start: logEntry.date_start,
                date_end: logEntry.date_end,
                action: `get_user_data_${logEntry.case}`
            },
            success: function (responseText) {
                enableButtons();
                const response = JSON.parse(responseText);
                console.log(responseText);

                if (response.sipgateData && response.worktimeData && response.appData) {
                    updateSummary(response.sipgateData, response.worktimeData, response.appData);
                }

                if (response.sipgateData && Array.isArray(response.sipgateData) &&
                    response.worktimeData && Array.isArray(response.worktimeData) &&
                    response.appData && Array.isArray(response.appData)) {

                    let combinedData;

                    if (logEntry.case === 'day') {
                        combinedData = combineData(response.sipgateData, response.worktimeData, response.appData);
                    } else {
                        combinedData = combineDataDate(response.sipgateData, response.worktimeData, response.appData);
                    }

                    sipgateData = response.sipgateData;
                    console.log(response.sipgateData);
                    worktimeData = response.worktimeData;
                    appData = response.appData;

                    // Diagramme generieren
                    var worktimeChartData = prepareWorktimeChartData(worktimeData);
                    var sipgateChartData = prepareSipgateDataChartData(sipgateData);

                    // Generieren der Charts
                    generateChart('.Chart1', {
                        ...worktimeChartOptions,
                        series: [{ name: 'Arbeitszeit', data: worktimeChartData }]
                    });

                    generateChart('.Chart2', {
                        ...appInteractionChartOptions,
                        series: prepareAppDataChartData(appData).appInteractionData
                    });

                    generateChart('.Chart3', {
                        ...appAppointmentChartOptions,
                        series: prepareAppDataChartData(appData).appAppointmentData
                    });

                    generateChart('.Chart4', {
                        ...sipgateChartOptions,
                        series: sipgateChartData
                    });

                    generateChart('.Chart5', {
                        ...appCreateChartOptions,
                        series: prepareAppDataChartData(appData).appCreateData
                    });



                    if (logEntry.case === 'day') {
                        displayUserData(combinedData);
                    } else {
                        displayUserDataWeekMonth(combinedData); // Anzeigen der Wochen- oder Monatsdaten
                    }
                } else {
                    console.error('Daten sind nicht im erwarteten Format:', response);
                }
            },
            error: function (xhr, status, error) {
                console.error('Ein Fehler ist aufgetreten:', error);
            }
        });


    }



    function getWeekNumber(d) {
        d = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
        d.setUTCDate(d.getUTCDate() + 4 - (d.getUTCDay() || 7));
        var yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
        var weekNo = Math.ceil(((d - yearStart) / 86400000 + 1) / 7);
        return weekNo;
    }

    function updateDateDisplay(period) {
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

    $('#dayButton, #weekButton, #monthButton').click(function () {
        let period = $(this).attr('id').replace('Button', '');
        setActiveButton(period); // Aktualisiere den ausgewählten Zeitraum
        updateDateDisplay(period);
        logDateRange(period);
        disableButtons();
    });

    $('#prevDate').click(function () {
        changeDate(-1); // Geht einen Tag, eine Woche oder einen Monat zurück
    });

    $('#nextDate').click(function () {
        changeDate(1); // Geht einen Tag, eine Woche oder einen Monat vorwärts
    });



    function disableButtons() {
        $('#dayButton, #weekButton, #monthButton, #prevDate, #nextDate').addClass('disabled-button');
    }

    function enableButtons() {
        $('#dayButton, #weekButton, #monthButton, #prevDate, #nextDate').removeClass('disabled-button');
    }



    // Initialer Aufruf für das heutige Datum
    updateDateDisplay(selectedPeriod);

    function setActiveButton(period) {
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

    $(document).ready(function () {
        updateDateDisplay(selectedPeriod);
        setActiveButton(selectedPeriod);
    });

    function combineDataDate(sipgateData, worktimeData, appData) {
        let combinedData = {};

        // Kombiniere sipgateData
        if (Array.isArray(sipgateData)) {
            sipgateData.forEach(function (data) {
                if (!combinedData[data.date]) {
                    combinedData[data.date] = {};
                }
                combinedData[data.date].sipgateData = data;
            });
        }

        // Kombiniere worktimeData
        if (Array.isArray(worktimeData)) {
            worktimeData.forEach(function (data) {
                if (!combinedData[data.date]) {
                    combinedData[data.date] = {};
                }
                combinedData[data.date].worktimeData = data.minutes;
            });
        }

        // Kombiniere appData
        if (Array.isArray(appData)) {
            appData.forEach(function (data) {
                if (!combinedData[data.date]) {
                    combinedData[data.date] = {};
                }
                combinedData[data.date].appData = data;
            });
        }

        // Umwandeln des kombinierten Objekts in ein Array
        return Object.keys(combinedData).map(date => {
            return {
                date: date,
                ...combinedData[date].sipgateData,
                worktime: combinedData[date].worktimeData,
                ...combinedData[date].appData
            };
        });
    }



    // Füllen der Tabelle mit den erhaltenend Daten vom Backend sind hier drunter
    function combineData(sipgateData, worktimeData, appData) {
        let combinedData = {};

        // Sicherstellen, dass sipgateData ein Array ist
        if (Array.isArray(sipgateData)) {
            sipgateData.forEach(function (data) {
                combinedData[data.hour] = { ...data };
            });
        }

        // Sicherstellen, dass worktimeData ein Array ist
        if (Array.isArray(worktimeData)) {
            worktimeData.forEach(function (data) {
                if (combinedData[data.hour]) {
                    combinedData[data.hour].worktime = data.minutes;
                } else {
                    combinedData[data.hour] = { hour: data.hour, worktime: data.minutes };
                }
            });
        }

        // Sicherstellen, dass appData ein Array ist
        if (Array.isArray(appData)) {
            appData.forEach(function (data) {
                if (combinedData[data.hour]) {
                    Object.assign(combinedData[data.hour], data);
                } else {
                    combinedData[data.hour] = { ...data };
                }
            });
        }

        return Object.values(combinedData);
    }


    function displayUserData(data) {
        var dataContainer = $('#dataContainer');
        dataContainer.empty(); // Bestehende Inhalte löschen

        var table = $('<table>');
        var headerRow = $('<tr>');

        // Fügt die Überschriften hinzu
        headerRow.append('<th><i class="bi bi-hourglass-split" data-toggle="tooltip" data-placement="bottom" title="Stunde"></i></th>');
        headerRow.append('<th><i class="bi bi-clock" data-toggle="tooltip" data-placement="bottom" title="Arbeitszeit (Minuten)"></i></th>');
        headerRow.append('<th><i class="bi bi-telephone-inbound-fill" data-toggle="tooltip" data-placement="bottom" title="Eingehend"></i></th>');
        headerRow.append('<th><i class="bi bi-telephone-outbound-fill" data-toggle="tooltip" data-placement="bottom" title="Ausgehend"></i></th>');
        headerRow.append('<th><i class="bi bi-telephone-inbound-fill" style="color: red;" data-toggle="tooltip" data-placement="bottom" title="Eingehend Verpasst"></i></th>');
        headerRow.append('<th><i class="bi bi-telephone-outbound-fill" style="color: red;" data-toggle="tooltip" data-placement="bottom" title="Ausgehend Verpasst"></i></th>');
        headerRow.append('<th><i class="bi bi-telephone-plus-fill" data-toggle="tooltip" data-placement="bottom" title="Hotline Eingehend"></i></th>');
        headerRow.append('<th><i class="bi bi-telephone-minus-fill" style="color: red;" data-toggle="tooltip" data-placement="bottom" title="Hotline Verpasst"></i></th>');
        headerRow.append('<th><i class="bi bi-door-open-fill" data-toggle="tooltip" data-placement="bottom" title="Kunden Geöffnet"></i></th>');
        headerRow.append('<th><i class="bi bi-123" data-toggle="tooltip" data-placement="bottom" title="Telefonnummer Geklickt"></i></th>');
        headerRow.append('<th><i class="bi bi-stickies-fill" data-toggle="tooltip" data-placement="bottom" title="Notiz Erstellt"></i></th>');
        headerRow.append('<th><i class="bi bi-house-add-fill" data-toggle="tooltip" data-placement="bottom" title="Appt Erstellt"></i></th>');
        headerRow.append('<th><i class="bi bi-house-exclamation-fill" data-toggle="tooltip" data-placement="bottom" title="Appt Verschoben"></i></th>');
        headerRow.append('<th><i class="bi bi-house-x-fill" data-toggle="tooltip" data-placement="bottom" title="Appt Storniert"></i></th>');

        table.append(headerRow);

        // Aktiviere die Tooltipps
        $('[data-toggle="tooltip"]').tooltip();
        data.sort(function (a, b) {
            // Wandelt Stunden-Strings in Zahlen um und vergleicht sie
            return parseInt(a.hour) - parseInt(b.hour);
        });
        // Jede Stunde durchlaufen und die Daten in die Tabelle einfügen
        data.forEach(function (hourData) {
            var row = $('<tr>');
            row.append('<td>' + hourData.hour + '</td>');
            row.append('<td>' + (hourData.worktime || 0) + '</td>');
            row.append('<td>' + (hourData.incoming || 0) + '</td>');
            row.append('<td>' + (hourData.outgoing || 0) + '</td>');
            row.append('<td>' + (hourData.missed_incoming || 0) + '</td>');
            row.append('<td>' + (hourData.missed_outgoing || 0) + '</td>');
            row.append('<td>' + (hourData.hotline_incoming || 0) + '</td>');
            row.append('<td>' + (hourData.missed_hotline || 0) + '</td>');

            // App-Daten hinzufügen
            row.append('<td>' + (hourData['load homeid'] || 0) + '</td>');
            row.append('<td>' + (hourData['click phonenumber'] || 0) + '</td>');
            row.append('<td>' + (hourData['create customer note'] || 0) + '</td>');
            row.append('<td>' + (hourData['created an hbg'] || 0) + '</td>');
            row.append('<td>' + (hourData['moved an hbg'] || 0) + '</td>');
            row.append('<td>' + (hourData['storno an appointment'] || 0) + '</td>');

            table.append(row);
        });


        dataContainer.append(table);
    }




    function displayUserDataWeekMonth(data) {
        var dataContainer = $('#dataContainer');
        dataContainer.empty(); // Bestehende Inhalte löschen

        var table = $('<table>');
        var headerRow = $('<tr>');

        // Fügt die Überschriften hinzu
        headerRow.append('<th><i class="bi bi-calendar-heart" data-toggle="tooltip" data-placement="bottom" title="Datum"></i></th>');
        headerRow.append('<th><i class="bi bi-clock" data-toggle="tooltip" data-placement="bottom" title="Arbeitszeit (Minuten)"></i></th>');
        headerRow.append('<th><i class="bi bi-telephone-inbound-fill" data-toggle="tooltip" data-placement="bottom" title="Eingehend"></i></th>');
        headerRow.append('<th><i class="bi bi-telephone-outbound-fill" data-toggle="tooltip" data-placement="bottom" title="Ausgehend"></i></th>');
        headerRow.append('<th><i class="bi bi-telephone-inbound-fill" style="color: red;" data-toggle="tooltip" data-placement="bottom" title="Eingehend Verpasst"></i></th>');
        headerRow.append('<th><i class="bi bi-telephone-outbound-fill" style="color: red;" data-toggle="tooltip" data-placement="bottom" title="Ausgehend Verpasst"></i></th>');
        headerRow.append('<th><i class="bi bi-telephone-plus-fill" data-toggle="tooltip" data-placement="bottom" title="Hotline Eingehend"></i></th>');
        headerRow.append('<th><i class="bi bi-telephone-minus-fill" style="color: red;" data-toggle="tooltip" data-placement="bottom" title="Hotline Verpasst"></i></th>');
        headerRow.append('<th><i class="bi bi-door-open-fill" data-toggle="tooltip" data-placement="bottom" title="Kunden Geöffnet"></i></th>');
        headerRow.append('<th><i class="bi bi-123" data-toggle="tooltip" data-placement="bottom" title="Telefonnummer Geklickt"></i></th>');
        headerRow.append('<th><i class="bi bi-stickies-fill" data-toggle="tooltip" data-placement="bottom" title="Notiz Erstellt"></i></th>');
        headerRow.append('<th><i class="bi bi-house-add-fill" data-toggle="tooltip" data-placement="bottom" title="Appt Erstellt"></i></th>');
        headerRow.append('<th><i class="bi bi-house-exclamation-fill" data-toggle="tooltip" data-placement="bottom" title="Appt Verschoben"></i></th>');
        headerRow.append('<th><i class="bi bi-house-x-fill" data-toggle="tooltip" data-placement="bottom" title="Appt Storniert"></i></th>');

        table.append(headerRow);

        // Jedes Datum durchlaufen und die Daten in die Tabelle einfügen
        data.forEach(function (dayData) {
            var row = $('<tr>');
            row.append('<td>' + dayData.date + '</td>'); // Hier das Datum einfügen
            row.append('<td>' + (dayData.worktime || 0) + '</td>');
            row.append('<td>' + (dayData.incoming || 0) + '</td>');
            row.append('<td>' + (dayData.outgoing || 0) + '</td>');
            row.append('<td>' + (dayData.missed_incoming || 0) + '</td>');
            row.append('<td>' + (dayData.missed_outgoing || 0) + '</td>');
            row.append('<td>' + (dayData.hotline_incoming || 0) + '</td>');
            row.append('<td>' + (dayData.missed_hotline || 0) + '</td>');

            // App-Daten hinzufügen
            row.append('<td>' + (dayData['load homeid'] || 0) + '</td>');
            row.append('<td>' + (dayData['click phonenumber'] || 0) + '</td>');
            row.append('<td>' + (dayData['create customer note'] || 0) + '</td>');
            row.append('<td>' + (dayData['created an hbg'] || 0) + '</td>');
            row.append('<td>' + (dayData['moved an hbg'] || 0) + '</td>');
            row.append('<td>' + (dayData['storno an appointment'] || 0) + '</td>');

            table.append(row);
        });

        dataContainer.append(table);
    }



    // Unter mir die Erstellung der Charts
    var charts = {
        Chart1: null,
        Chart2: null,
        Chart3: null,
        Chart4: null,
        Chart5: null,
        Chart6: null
    };

    // Funktion zum Zurücksetzen der Charts
    function destroyChart(chartKey) {
        if (charts[chartKey]) {
            charts[chartKey].destroy(); // Zerstört die Chart-Instanz
            charts[chartKey] = null;
        }
    }

    function destroyAllCharts() {
        Object.keys(charts).forEach(destroyChart);
    }

    // Funktion zum Generieren der Charts
    function generateChart(containerSelector, options) {
        var chartContainer = document.querySelector(containerSelector);
        // Entfernen Sie zuerst alle Spinner aus dem Container
        chartContainer.innerHTML = '';

        // Erstellen Sie dann das Chart und fügen Sie es zum Container hinzu
        var chart = new ApexCharts(chartContainer, options);
        chart.render();
        return chart;
    }
    // Funktion, um die Arbeitszeitdaten aufzubereiten
    function prepareWorktimeChartData(worktimeData) {
        return worktimeData.map(entry => {
            return {
                x: entry.date || entry.hour,
                y: entry.minutes
            };
        });
    }

    // Funktion, um die App-Daten für die ersten beiden Charts aufzubereiten
    function prepareAppDataChartData(appData) {
        let appInteractionSeries = {
            'Notiz erstellt': [],
            'Telefonnummer geklickt': [],
            'Home ID geladen': []
        };

        let appCreateSeries = {
            'Termin erstellt': []
        };

        let appAppointmentSeries = {
            'Termin verschoben': [],
            'Termin storniert': []
        };

        appData.forEach(entry => {
            let xValue = entry.date || entry.hour;

            appInteractionSeries['Notiz erstellt'].push({ x: xValue, y: entry['create customer note'] || 0 });
            appInteractionSeries['Telefonnummer geklickt'].push({ x: xValue, y: entry['click phonenumber'] || 0 });
            appInteractionSeries['Home ID geladen'].push({ x: xValue, y: entry['load homeid'] || 0 });

            appAppointmentSeries['Termin verschoben'].push({ x: xValue, y: entry['moved an hbg'] || 0 });
            appAppointmentSeries['Termin storniert'].push({ x: xValue, y: entry['storno an appointment'] || 0 });

            appCreateSeries['Termin erstellt'].push({ x: xValue, y: entry['created an hbg'] || 0 });
        });

        // Convert the object of arrays into an array of series objects for each type of interaction
        let appInteractionData = Object.keys(appInteractionSeries).map(key => {
            return { name: key, data: appInteractionSeries[key] };
        });

        let appAppointmentData = Object.keys(appAppointmentSeries).map(key => {
            return { name: key, data: appAppointmentSeries[key] };
        });

        let appCreateData = Object.keys(appCreateSeries).map(key => {
            return { name: key, data: appCreateSeries[key] };
        });

        return { appInteractionData, appAppointmentData, appCreateData };
    }


    // Funktion, um die Sipgate-Daten aufzubereiten
    function prepareSipgateDataChartData(sipgateData) {
        let seriesData = {
            'Eingehend': [],
            'Ausgehend': [],
            'Verpasst Eingehend': [],
            'Verpasst Ausgehend': [],
            'Hotline Eingehend': [],
            'Hotline Verpasst': []
        };

        sipgateData.forEach(entry => {
            let xValue = entry.date || entry.hour;
            seriesData['Eingehend'].push({ x: xValue, y: entry.incoming || 0 });
            seriesData['Ausgehend'].push({ x: xValue, y: entry.outgoing || 0 });
            seriesData['Verpasst Eingehend'].push({ x: xValue, y: entry.missed_incoming || 0 });
            seriesData['Verpasst Ausgehend'].push({ x: xValue, y: entry.missed_outgoing || 0 });
            seriesData['Hotline Eingehend'].push({ x: xValue, y: entry.hotline_incoming || 0 });
            seriesData['Hotline Verpasst'].push({ x: xValue, y: entry.missed_hotline || 0 });
        });

        // Umwandeln des Objekts in ein Array von Serien
        return Object.keys(seriesData).map(key => {
            return {
                name: key,
                data: seriesData[key]
            };
        });
    }


    // Beispiel für Chart-Optionen
    var worktimeChartOptions = {
        title: {
            text: 'Arbeitszeit',
            align: 'center',
            style: {
                fontSize: '20px',
                fontWeight: 'bold',
                color: '#333' // Sie können die Farbe nach Ihrem Geschmack anpassen
            }
        },
        chart: {
            type: 'bar',
            height: 350,
            zoom: {
                enabled: true, // Aktiviert die Zoom-Funktion
                type: 'x',     // Erlaubt das Zoomen entlang der x-Achse
                autoScaleYaxis: true // Passt die y-Achse automatisch an den vergrößerten Bereich an
            },
            toolbar: {
                autoSelected: 'zoom' // Wählt das Zoom-Tool standardmäßig aus
            }
        },
        // Weitere Optionen...
    };
    var appInteractionChartOptions = {
        title: {
            text: 'CRM Interaktionen',
            align: 'center',
            style: {
                fontSize: '20px',
                fontWeight: 'bold',
                color: '#333' // Sie können die Farbe nach Ihrem Geschmack anpassen
            }
        },
        series: [
            {
                name: 'Kundennote erstellt',
                data: appData.map(d => ({ x: d.hour || d.date, y: d['create customer note'] || 0 }))
            },
            {
                name: 'Hausbesichtigung erstellt',
                data: appData.map(d => ({ x: d.hour || d.date, y: d['created an hbg'] || 0 }))
            },
            {
                name: 'Telefonnummer geklickt',
                data: appData.map(d => ({ x: d.hour || d.date, y: d['click phonenumber'] || 0 }))
            },
            {
                name: 'Home ID geladen',
                data: appData.map(d => ({ x: d.hour || d.date, y: d['load homeid'] || 0 }))
            }
        ],
        chart: {
            type: 'area',
            height: 350,
            zoom: {
                enabled: true, // Aktiviert die Zoom-Funktion
                type: 'x',     // Erlaubt das Zoomen entlang der x-Achse
                autoScaleYaxis: true // Passt die y-Achse automatisch an den vergrößerten Bereich an
            },
            toolbar: {
                autoSelected: 'zoom' // Wählt das Zoom-Tool standardmäßig aus
            }
        },
        plotOptions: {
            bar: {
                horizontal: false
            }
        },
        // Weitere Optionen... 
    };







    var appCreateChartOptions = {
        title: {
            text: 'Termine erstellt',
            align: 'center',
            style: {
                fontSize: '20px',
                fontWeight: 'bold',
                color: '#333' // Sie können die Farbe nach Ihrem Geschmack anpassen
            }
        },
        series: [
            {
                name: 'Termin erstellt',
                data: appData.map(d => ({ x: d.hour || d.date, y: d['created an hbg'] || 0 }))
            }
        ],
        chart: {
            type: 'area',
            height: 350,
            zoom: {
                enabled: true, // Aktiviert die Zoom-Funktion
                type: 'x',     // Erlaubt das Zoomen entlang der x-Achse
                autoScaleYaxis: true // Passt die y-Achse automatisch an den vergrößerten Bereich an
            },
            toolbar: {
                autoSelected: 'zoom' // Wählt das Zoom-Tool standardmäßig aus
            }
        },
        plotOptions: {
            bar: {
                horizontal: false
            }
        },
        // Weitere Optionen... 
    };



    var appAppointmentChartOptions = {
        title: {
            text: 'Termin änderungen',
            align: 'center',
            style: {
                fontSize: '20px',
                fontWeight: 'bold',
                color: '#333' // Sie können die Farbe nach Ihrem Geschmack anpassen
            }
        },
        series: [
            {
                name: 'Termin verschoben',
                data: appData.map(d => ({ x: d.hour || d.date, y: d['moved an hbg'] || 0 }))
            },
            {
                name: 'Termin storniert',
                data: appData.map(d => ({ x: d.hour || d.date, y: d['storno an appointment'] || 0 }))
            }
        ],
        chart: {
            type: 'area',
            height: 350,
            zoom: {
                enabled: true, // Aktiviert die Zoom-Funktion
                type: 'x',     // Erlaubt das Zoomen entlang der x-Achse
                autoScaleYaxis: true // Passt die y-Achse automatisch an den vergrößerten Bereich an
            },
            toolbar: {
                autoSelected: 'zoom' // Wählt das Zoom-Tool standardmäßig aus
            }
        },
        plotOptions: {
            bar: {
                horizontal: false
            }
        },
        // Weitere Optionen... 
    };


    var sipgateChartOptions = {
        title: {
            text: 'Telefon Auswertung',
            align: 'center',
            style: {
                fontSize: '20px',
                fontWeight: 'bold',
                color: '#333' // Sie können die Farbe nach Ihrem Geschmack anpassen
            }
        },
        series: [
            {
                name: 'Eingehend',
                data: sipgateData.map(d => ({ x: d.hour || d.date, y: d.incoming }))
            },
            {
                name: 'Ausgehend',
                data: sipgateData.map(d => ({ x: d.hour || d.date, y: d.outgoing }))
            },
            {
                name: 'Verpasst Eingehend',
                data: sipgateData.map(d => ({ x: d.hour || d.date, y: d.missed_incoming }))
            },
            {
                name: 'Verpasst Ausgehend',
                data: sipgateData.map(d => ({ x: d.hour || d.date, y: d.missed_outgoing }))
            },
            {
                name: 'Hotline Eingehend',
                data: sipgateData.map(d => ({ x: d.hour || d.date, y: d.hotline_incoming }))
            },
            {
                name: 'Hotline Verpasst',
                data: sipgateData.map(d => ({ x: d.hour || d.date, y: d.missed_hotline }))
            }
        ],
        chart: {
            type: 'area',
            height: 350,
            zoom: {
                enabled: true, // Aktiviert die Zoom-Funktion
                type: 'x',     // Erlaubt das Zoomen entlang der x-Achse
                autoScaleYaxis: true // Passt die y-Achse automatisch an den vergrößerten Bereich an
            },
            toolbar: {
                autoSelected: 'zoom' // Wählt das Zoom-Tool standardmäßig aus
            }
        },

        tooltip: {
            y: {
                formatter: function (val) {
                    return val + ' Anrufe'
                }
            }
        },
        // Weitere Optionen können hier hinzugefügt werden, wie z.B. Farben, Legenden, etc.
    };


    $(document).ready(function () {
        $('#liveDataButton').click(function () {
            $('.statusContainer').toggle(); // statusContainer ein-/ausblenden
            $('.statusBox').toggle();
            // Icon im Button ändern
            if ($('.statusContainer').is(':visible')) {
                $('#liveDataButton i').removeClass('bi-x-diamond').addClass('bi-x-diamond-fill');
            } else {
                $('#liveDataButton i').removeClass('bi-x-diamond-fill').addClass('bi-x-diamond');
            }
        });
    });




    function updateSummary(sipgateData, worktimeData, appData) {
        // Initialisierung der Summen
        let totalIncoming = 0, totalOutgoing = 0, totalMissedIncoming = 0, totalMissedOutgoing = 0;
        let totalHotlineIncoming = 0, totalMissedHotline = 0;
        let totalWorkMinutes = 0;
        let totalLoadHomeId = 0, totalClickPhoneNumber = 0, totalCreateCustomerNote = 0;
        let totalCreatedAnHbg = 0, totalMovedAnHbg = 0, totalStornoAnAppointment = 0;

        // Summieren der Daten
        sipgateData.forEach(item => {
            totalIncoming += item.incoming;
            totalOutgoing += item.outgoing;
            totalMissedIncoming += item.missed_incoming;
            totalMissedOutgoing += item.missed_outgoing;
            totalHotlineIncoming += item.hotline_incoming;
            totalMissedHotline += item.missed_hotline;
        });

        worktimeData.forEach(item => {
            totalWorkMinutes += item.minutes;
        });

        appData.forEach(item => {
            totalLoadHomeId += item['load homeid'];
            totalClickPhoneNumber += item['click phonenumber'];
            totalCreateCustomerNote += item['create customer note'] || 0; // Falls nicht in jedem Item vorhanden
            totalCreatedAnHbg += item['created an hbg'] || 0;
            totalMovedAnHbg += item['moved an hbg'] || 0;
            totalStornoAnAppointment += item['storno an appointment'] || 0;
        });

        // Umwandlung der Arbeitsminuten in Stunden und Minuten
        const workHours = Math.floor(totalWorkMinutes / 60);
        const workMinutes = Math.round(totalWorkMinutes % 60);

        // Zusammenfassung in HTML-Element einfügen
        const summaryHtml = `
        <p>Gearbeitet: ${workHours}h ${workMinutes}min</p>
        <p>Eingehende Anrufe: ${totalIncoming} <span class="sub-info"><i class="bi bi-arrow-bar-right rotated-icon"></i>Davon Verpasst: ${totalMissedIncoming}</span></p>
        <p>Ausgehende Anrufe: ${totalOutgoing} <span class="sub-info"><i class="bi bi-arrow-bar-right rotated-icon"></i>Davon Verpasst: ${totalMissedOutgoing}</span></p>
        <p>Hotline eingehend: ${totalHotlineIncoming} <span class="sub-info"><i class="bi bi-arrow-bar-right rotated-icon"></i>Davon Verpasst: ${totalMissedHotline}</span></p>
        <p>Kunden geöffnet: ${totalLoadHomeId}</p>
        <p>Telefonnummern gedrückt: ${totalClickPhoneNumber}</p>
        <p>Notiz erstellt: ${totalCreateCustomerNote}</p>
        <p>Termin erstellt: ${totalCreatedAnHbg}</p>
        <p>Termin verschoben: ${totalMovedAnHbg}</p>
        <p>Termin storniert: ${totalStornoAnAppointment}</p>
    `;

        document.getElementById('summaryContent').innerHTML = summaryHtml;
    }







});