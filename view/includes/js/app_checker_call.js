// script.js
$(document).ready(function () {

    var currentIndex = 0; // Aktueller Index der Hauptkachel
    var carouselData = []; // Daten für das Karussell

    var currentData = []; // Dies speichert die geladenen Daten
    var currentDataIndex = 0; // Dies speichert den Index des aktuellen Eintrags

    $('#dateSelector').val(new Date().toISOString().substring(0, 10));

    $('#dateSelector').change(function () {
        const selectedDate = $(this).val();
        fetchDataForDate(selectedDate);
        console.log(selectedDate);
    });

    fetchDataForDate($('#dateSelector').val());


    function fetchDataForDate(date) {
        console.log("FetdchDataForDate");
        $.ajax({
            url: '/view/load/appcheck_call_load.php',
            type: 'POST',
            data: {
                dateSelectDate: date
            },
            success: function (response) {
                console.log(response);
                const data = JSON.parse(response).data;
                carouselData = data; // Speichern der Daten für die Navigation
                updateTiles(currentIndex); // Initialisiere Kacheln mit erstem Element
            },
            error: function (xhr, status, error) {
                console.error("Ein Fehler ist aufgetreten: " + error);
            }
        });
    }


    function updateTiles(index) {
        const tilesContainer = $('.tiles-container');
        tilesContainer.empty(); // Vorherige Kacheln entfernen

        // Vorherige, aktuelle und nächste Kachel hinzufügen, wenn verfügbar
        if (index > 0) {
            addTile(carouselData[index - 1], 'previous-tile');
        }
        addTile(carouselData[index], 'main-tile');
        if (index < carouselData.length - 1) {
            addTile(carouselData[index + 1], 'next-tile');
        }
    }

    function addTile(item, className) {
        const tile = $(`<div class="tile ${className}">${item.hausbegeher}<br>Total: ${item.total_entries}<br>Checks: ${item.call_check_true}</div>`);
        $('.tiles-container').append(tile);
    }

    // Klick-Handler für die Navigation im Karussell
    $(document).on('click', '.previous-tile, .next-tile', function () {
        // Bestimmen Sie, ob vorherige oder nächste Kachel geklickt wurde
        var direction = $(this).hasClass('previous-tile') ? -1 : 1;
        currentIndex += direction;
        updateTiles(currentIndex);
    });

    $(document).on('click', '.tile', function () {
        // Extrahieren des Hausbegeher-Namens aus dem Kacheltext
        var selectedHausbegeher = $(this).text().match(/^(.*?)Total/)[1].trim(); // Nimmt alles vor dem Wort "Total"
        var selectedDate = $('#dateSelector').val();

        // Details-Container anzeigen
        $('.details-container').show();

        // AJAX-Anfrage, um die Termine und Kundendaten für den ausgewählten Hausbegeher zu erhalten
        fetchAppointmentsAndCustomerData(selectedHausbegeher, selectedDate);
    });




    // Ergänzung innerhalb des $(document).ready(...)
    function fetchAppointmentsAndCustomerData(hausbegeher, selectedDate) {
        $.ajax({
            url: '/view/load/appcheck_call_load.php', // Pfad zu Ihrem PHP-Backend-Script
            type: 'POST',
            data: {
                func: 'fetchCustomerDetails', // Zusätzlicher Parameter, um die Art der Anfrage zu unterscheiden
                username: hausbegeher,
                date: selectedDate
            },
            success: function (response) {
                console.log(response);
                const responseData = JSON.parse(response);
                if (responseData.success) {
                    currentData = responseData.data;
                    if (currentData.length > 0) {
                        displayCustomerDetails(currentData[currentDataIndex]);
                    } else {
                        console.log('Keine Daten gefunden');
                        // Fügen Sie hier Code hinzu, um mit dem Fall "Keine Daten gefunden" umzugehen
                    }
                } else {
                    // Fehlerbehandlung
                    alert('Fehler beim Laden der Kundendaten');
                }
            },
            error: function (xhr, status, error) {
                console.error("Ein Fehler ist aufgetreten: " + error);
            }
        });
    }

    function generatePhoneLinks(data) {
        // Liste aller möglichen Telefonnummern
        const phoneFields = ['phone1', 'phone2', 'phone3', 'phone4', 'scan4_phone1', 'scan4_phone2'];
        let phoneLinks = '';

        phoneFields.forEach(field => {
            if (data[field]) {
                // Füge den Telefonlink hinzu, wenn das Feld nicht leer ist
                phoneLinks += `<a href="tel:${data[field]}">${data[field]}</a><br>`;
            }
        });

        return phoneLinks;
    }

    function displayCustomerDetails(data) {
        $('#customerInfo').html(`
          <strong>Name:</strong> ${data.firstname} ${data.lastname}<br>
          <strong>Adresse:</strong> ${data.street} ${data.streetnumber}${data.streetnumberadd}, ${data.plz} ${data.city}<br>
          <strong>HomeID:</strong> ${data.homeid}<br>
          <strong>Telefon:</strong> <br>${generatePhoneLinks(data)}
        `);
        $('#appointmentInfo').html(`
          <strong>Termin:</strong> ${data.date} um ${data.time}, ${data.status}<br>
          <strong>Mitarbeiter:</strong> ${data.username}<br>
          <strong>Termin Kommentar:</strong> ${data.appt_comment || "Kein Termin Kommentar."}
        `);

        // Initialisiere das Kommentarfeld und die Bewertungsauswahl
        $('#commentField').val('');
        $('#ratingSelect').val('');

        // Zeige die Modal
        $('#customerDetailsModal').modal('show');

        // Speichern-Button Event
        $('#saveButton').off('click').on('click', function () {
            const comment = $('#commentField').val();
            const rating = $('#ratingSelect').val();
            const uid = data.uid; // Stellen Sie sicher, dass `uid` korrekt aus `data` extrahiert wird.

            if (!comment || !rating) {
                // Zeige eine Warnmeldung an, wenn Kommentar oder Bewertung fehlen
                alert('Bitte geben Sie einen Kommentar und eine Bewertung ein.');
                return; // Verhindert das Fortfahren zum nächsten Datensatz
            }

            console.log('Gespeicherte Daten:', { uid, comment, rating });
            saveCheckData(uid, rating, comment);
            navigateToNextData();
        });

        $('#notReachedButton').off('click').on('click', function () {
            const comment = "Nicht erreicht";
            const rating = $('#ratingSelect').val(); // Optional, falls Bewertung auch bei "Nicht erreicht" relevant
            const uid = data.uid; // Stellen Sie sicher, dass `uid` korrekt aus `data` extrahiert wird.
            console.log('Nicht erreicht für:', { uid, comment, rating });
            saveCheckData(uid, rating, comment);
            navigateToNextData();
        });
    }



    function navigateToNextData() {
        currentDataIndex += 1; // Aktualisiere den Index um zum nächsten Eintrag zu gelangen

        // Überprüfen, ob wir das Ende der Daten erreicht haben
        if (currentDataIndex < currentData.length) {
            displayCustomerDetails(currentData[currentDataIndex]);
        } else {
            alert('Ende der Daten erreicht.');
            // Optional: Logik zum Zurücksetzen oder Verbergen der Anzeige implementieren
            currentDataIndex = 0; // Zurücksetzen des Index, um von vorne zu beginnen oder andere Logik
        }
    }





    function saveCheckData(uid, rating, comment) {
        $.ajax({
            url: '/view/load/appcheck_call_load.php', // Pfad zu Ihrem PHP-Backend-Script
            type: 'POST',
            data: {
                func: 'saveRatingData', // Zusätzlicher Parameter, um die Art der Anfrage zu unterscheiden
                uid: uid,
                rating: rating,
                comment: comment

            },
            success: function (response) {
                console.log(response);
                fetchDataForDate($('#dateSelector').val());

            },
            error: function (xhr, status, error) {
                console.error("Ein Fehler ist aufgetreten: " + error);
            }
        });
    }







});
