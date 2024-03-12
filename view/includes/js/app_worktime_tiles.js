
$(document).ready(function () {
    $('#statusContainer').hide();
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

    function formatDuration(duration) {
        var minutes = Math.floor(duration);
        var seconds = Math.floor((duration - minutes) * 60);
        return minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0') + 'm';
    }

    $(document).ready(function () {
        // Funktion zum Abrufen und Anzeigen der durchschnittlichen Anrufdauer
        function fetchAndDisplayLastCallTime(direction) {
            $.ajax({
                url: 'view/load/worktime_load.php',
                type: 'POST',
                data: { action: 'fetch_last_call', direction: direction },
                dataType: 'json',
                success: function (response) {
                    //console.log(response);
                    var lastCallTime = response.lastCallTime;
                    //console.log(lastCallTime);
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


