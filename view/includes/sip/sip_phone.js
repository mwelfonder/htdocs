(async function () {

    var socket = new JsSIP.WebSocketInterface('wss://sip.sipgate.de');
    var configuration = {};
    var isRingtoneMuted = false;
    var currentSessionId = null;
    let outgoingCallLogged = false;

    try {
        const response = await fetch('/view/includes/sip/sip_settings.php');
        const data = await response.json();
        //onsole.log(data);

        if (data.error) {
            console.error('#WXR1120 - Fehler beim Abrufen der Benutzerdaten: ', data.error);
            return;
        }

        // Aktualisieren der globalen Konfiguration
        configuration.sockets = [socket];
        configuration.uri = data.sip_uri;
        configuration.password = data.sip_password;
        configuration.username = data.sip_username;
        configuration.register = true;
        var incomingCallAudioPath = data.sip_tone || '/view/includes/sip/tone/nokia.mp3'; // Default path if sip_tone is not provided
        var incomingCallAudio = new window.Audio(incomingCallAudioPath);
    } catch (error) {
        console.error('Fehler: ', error);
        return;
    }
    incomingCallAudio.loop = true;
    incomingCallAudio.crossOrigin = "anonymous";
    var remoteAudio = new window.Audio();
    remoteAudio.autoplay = true;
    remoteAudio.crossOrigin = "anonymous";

    var callOptions = {
        mediaConstraints: { audio: true, video: false }
    };

    var phone;
    if (configuration.uri && configuration.password) {
        //JsSIP.debug.enable();
        //JsSIP.debug.enable('JsSIP:*'); // more detailed debug output
        phone = new JsSIP.UA(configuration);
        phone.on('registrationFailed', function (ev) {
            alert('Registering on SIP server failed with error: ' + ev.cause);
            configuration.uri = null;
            configuration.password = null;
            updateUI();
        });
        phone.on('newRTCSession', function (ev) {
            var newSession = ev.session;
            var callId = newSession.id;
            if (session && newSession.direction === 'incoming') {

                console.log('Bereits in einem Anruf, neuer eingehender Anruf wird abgelehnt');
                newSession.terminate(); // Neuen eingehenden Anruf ablehnen
                return;
            }

            newSession.on('progress', function (e) {
                if (newSession.direction === 'outgoing' && !outgoingCallLogged) {
                    callId = newSession.id; // ID des ausgehenden Anrufs
                    var dest = $('#toField').val();
                    sendCallData('newCall', 'outgoing', dest, configuration.username, callId);
                    outgoingCallLogged = true; // Verhindern, dass der Status erneut geloggt wird
                }
            });

            newSession.on('accepted', function () {
                if (newSession.direction === 'outgoing') {
                    sendCallData('pickup', 'outgoing', $('#toField').val(), configuration.username, callId);
                }
            });

            newSession.on('ended', function () {
                if (newSession.direction === 'outgoing') {
                    sendCallData('hangup', 'outgoing', $('#toField').val(), configuration.username, callId);
                }
            });

            newSession.on('failed', function (data) {
                if (newSession.direction === 'incoming') {
                    var callId = newSession.id; // ID des eingehenden Anrufs
                    sendCallData('hangup', 'incoming', configuration.username, newSession.remote_identity.uri.user, callId);
                }
                showErrorNotification(data);
            });

            // Handler für eingehende Anrufe
            if (newSession.direction === 'incoming') {
                console.log('Eingehender Anruf von:', newSession.remote_identity.uri.user);

                callId = newSession.id; // ID des eingehenden Anrufs
                sendCallData('newCall', 'incoming', configuration.username, newSession.remote_identity.uri.user, callId);

                newSession.on('accepted', function () {
                    sendCallData('pickup', 'incoming', configuration.username, newSession.remote_identity.uri.user, callId);
                });

                newSession.on('ended', function () {
                    sendCallData('hangup', 'incoming', configuration.username, newSession.remote_identity.uri.user, callId);
                });
            }



            session = newSession;
            currentSessionId = Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            var completeSession = function () {
                //  if (session.direction === 'incoming') {
                //      sendCallData('hangup', 'incoming', configuration.username, session.remote_identity.uri.user, callId);
                //  } else {
                //      sendCallData('hangup', 'outgoing', session.remote_identity.uri.user, configuration.username, callId);
                //  }
                console.log(`%c
                ____  __.___________.___ .____      __________   ___ ___  ________    _______   _____________________ 
                |    |/ _|\_   _____/|   ||    |     \______   \ /   |   \ \_____  \   \      \  \_   _____/\______   \
                |      <   |    __)_ |   ||    |      |     ___//    ~    \ /   |   \  /   |   \  |    __)_  |       _/
                |    |  \  |        \|   ||    |___   |    |    \    Y    //    |    \/    |    \ |        \ |    |   \
                |____|__ \/_______  /|___||_______ \  |____|     \___|_  / \_______  /\____|__  //_______  / |____|_  /
                        \/        \/              \/                   \/          \/         \/         \/         \/ 
                `, "font-family: monospace");

                console.log("War eine Freude, über mir zu telefonieren");
                currentSessionId = null;
                session = null;
                updateUI();
            };

            session.on('ended', completeSession);
            session.on('failed', completeSession);
            session.on('accepted', updateUI);
            session.on('confirmed', function () {
                //  if (session.direction === 'incoming') {
                //      sendCallData('pickup', session.direction, configuration.username, session.remote_identity.uri.user, callId);
                //  } else {
                //       sendCallData('pickup', session.direction, session.remote_identity.uri.user, configuration.username, callId);
                //  }


                var localStream = session.connection.getLocalStreams()[0];
                var dtmfSender = session.connection.createDTMFSender(localStream.getAudioTracks()[0])
                session.sendDTMF = function (tone) {
                    dtmfSender.insertDTMF(tone);
                };
                updateUI();
            });
            session.on('peerconnection', (e) => {
                //console.log('peerconnection', e);
                let logError = '';
                const peerconnection = e.peerconnection;

                peerconnection.onaddstream = function (e) {
                    //console.log('addstream', e);
                    // set remote audio stream (to listen to remote audio)
                    // remoteAudio is <audio> element on pag
                    remoteAudio.srcObject = e.stream;
                    remoteAudio.play();
                };

                var remoteStream = new MediaStream();
                //console.log(peerconnection.getReceivers());
                peerconnection.getReceivers().forEach(function (receiver) {
                    //console.log(receiver);
                    remoteStream.addTrack(receiver.track);
                });
            });

            if (session.direction === 'incoming') {
                if (isRingtoneMuted) {
                    incomingCallAudio.pause();
                } else {
                    incomingCallAudio.play();
                }
            } else {
                //console.log('con', session.connection)
                session.connection.addEventListener('addstream', function (e) {
                    incomingCallAudio.pause();
                    remoteAudio.srcObject = e.stream;
                });
            }
            updateUI();
        });
        phone.start();
    }

    var session;
    updateUI();

    let callLogged = false;
    $('#connectCall').click(function () {
        outgoingCallLogged = false;
        callLogged = false; // Reset des Flags bei jedem neuen Klick
        var dest = $('#toField').val();
        phone.call(dest, callOptions);
        $('#menu').hide();
        updateUI();
    });


    $('#answer').click(function () {
        $('#menu').hide();
        if (session) {
            console.log('Versuch, Anruf zu beantworten, Session-Status:', session.status);
            try {
                session.answer(callOptions);
            } catch (e) {
                console.error('Fehler beim Beantworten des Anrufs:', e);
            }
        } else {
            console.error('Keine Session vorhanden.');
        }
    });


    $('#muteRingtone').click(function (event) {
        event.stopPropagation();  // Verhindert, dass das Klick-Ereignis an übergeordnete Elemente weitergegeben wird.

        isRingtoneMuted = !isRingtoneMuted; // Umschalten des Stummschaltungsstatus
        if (isRingtoneMuted) {
            incomingCallAudio.pause();
            console.log("Klingelton Stumm geschaltet.");
            $(this).html('<i class="bi bi-volume-mute-fill" style="color: #A90002;"></i>'); // Icon Rot für stummgeschaltet
        } else {
            console.log("Klingelton aktiviert.");
            $(this).html('<i class="bi bi-volume-down-fill" style="color: #28a745;"></i>'); // Icon Grün für aktiv
        }
    });



    var hangup = function () {
        session.terminate();
    };

    $('#hangUp').click(hangup);
    $('#reject').click(hangup);

    $('#mute').click(function () {
        console.log('MUTE CLICKED');
        if (session.isMuted().audio) {
            session.unmute({ audio: true });
        } else {
            session.mute({ audio: true });
        }
        updateUI();
    });
    $('#toField').keypress(function (e) {
        if (e.which === 13) {//enter
            $('#connectCall').click();
        }
    });
    $('#inCallButtons').on('click', '.dialpad-char', function (e) {
        var $target = $(e.target);
        var value = $target.data('value');
        session.sendDTMF(value.toString());
    });
    function updateUI() {
        if (configuration.uri && configuration.password) {
            $('#errorMessage').hide();
            $('#wrapper').show();

            if (session) {
                if (session.isInProgress()) {
                    if (session.direction === 'incoming') {
                        $('#incomingCallNumber').html(session.remote_identity.uri.user);
                        $('#incomingCall').show();
                        $('#callControl').hide()
                        $('#incomingCall').show();
                        $('#toggleMenu').hide()
                    } else {
                        $('#callInfoText').html('Wähle...');
                        $('#callInfoNumber').html(session.remote_identity.uri.user);
                        $('#callStatus').show();
                        $('#toggleMenu').hide()
                    }

                } else if (session.isEstablished()) {
                    $('#callStatus').show();
                    $('#incomingCall').hide();
                    $('#callInfoText').html('Im Gespräch');
                    $('#callInfoNumber').html(session.remote_identity.uri.user);
                    $('#inCallButtons').show();
                    $('#toggleMenu').hide()
                    incomingCallAudio.pause();
                }
                $('#callControl').hide();
            } else {
                $('#incomingCall').hide();
                $('#callControl').show();
                $('#callStatus').hide();
                $('#toggleMenu').show()
                $('#inCallButtons').hide();
                incomingCallAudio.pause();
            }
            //microphone mute icon
            if (session && session.isMuted().audio) {
                $('#muteIcon').addClass('fa-microphone-slash');
                $('#muteIcon').removeClass('fa-microphone');
            } else {
                $('#muteIcon').removeClass('fa-microphone-slash');
                $('#muteIcon').addClass('fa-microphone');
            }
        } else {
            $('#wrapper').hide();
            $('#errorMessage').hide();
            console.error('#WXR3200 - Telefonfunktion nicht initialisiert - SIP Daten Fehlerhaft');
        }
    }


    document.addEventListener('click', function (e) {
        var element = e.target;

        // Überprüfen, ob das Element ein Link ist und ob es mit 'tel:' beginnt
        if (element.tagName === 'A' && element.href.startsWith('tel:')) {
            e.preventDefault(); // Verhindern, dass der Standard-Link geöffnet wird

            var phoneNumber = element.href.split(':')[1]; // Extrahieren der Telefonnummer
            startCall(phoneNumber); // Funktion zum Starten des Anrufs
        }
    });

    function startCall(phoneNumber) {
        if (phone) {
            phone.call(phoneNumber, callOptions);
            updateUI();
        } else {
            console.error('#WXR3212 - Telefonfunktion nicht initialisiert');
        }
    }




})();


document.getElementById('incomingCallNumber').addEventListener('click', function (event) {
    var text = this.innerText || this.textContent;
    navigator.clipboard.writeText(text).then(function () {
        var copiedGif = document.getElementById('copiedGif');

        // Warte bis das GIF geladen ist, um die tatsächliche Größe zu bekommen
        copiedGif.onload = function () {
            var gifWidth = copiedGif.offsetWidth;
            var gifHeight = copiedGif.offsetHeight;
            var gifOffsetX = event.clientX - gifWidth / 2; // Zentriere das GIF horizontal zur Mausposition
            var gifOffsetY = event.clientY - gifHeight - 60; // Positioniere das GIF 60px über der Mausposition

            copiedGif.style.left = gifOffsetX + 'px';
            copiedGif.style.top = gifOffsetY + 'px';

            copiedGif.style.display = 'block';

            setTimeout(function () {
                copiedGif.style.display = 'none';
            }, 2000); // 2000 Millisekunden = 2 Sekunden
        };

        // Setze das GIF-Quelle neu, um das Laden auszulösen
        copiedGif.src = copiedGif.src;
    }, function (err) {
        console.error('Fehler beim Kopieren: ', err);
    });
});

$(document).ready(function () {
    // Menü ein- und ausklappen
    $('#toggleMenu').click(function () {
        var menu = $('#menu');
        var isOpen = menu.is(':visible');

        if (isOpen) {
            // Menü ist sichtbar, schließe es
            menu.slideUp();
        } else {
            // Menü ist nicht sichtbar, öffne es und triggere einen Klick auf #history
            menu.slideDown(function () {
                $('#history').click(); // Simuliert einen Klick auf das History-Element
            });
        }
    });
    /*var currentPlayingAudio = null;
    // Event Delegation für Klicks auf die Knöpfe im Menü
    $('#menu').on('click', '#settings', function () {
        // Aktiven Stil setzen und andere Elemente deaktivieren
        $('#settings').addClass('active');
        $('#history').removeClass('active');

        // AJAX-Anfrage, um die Liste der Sounddateien zu erhalten
        $.ajax({
            url: '/view/includes/sip/sip_load.php',
            type: 'POST',
            data: { action: 'list_sounds' },
            dataType: 'json',
            success: function (response) {
                $('#menu-text i').hide();
                var fileListHtml = '<div id="file-list" style="overflow-y: auto; max-height: 200px; background-color: #202020; color: #fff;">';

                // Erstellen der Liste mit den Sounddateien
                response.files.forEach(function (fileName) {
                    fileListHtml += `
                    <div class="file-entry" style="display: flex; align-items: center; padding: 10px; border-bottom: 1px solid #333;">
                        <button class="play-sound" data-file="${fileName}" style="margin-right: 10px;">
                            <i class="bi bi-play-circle-fill" style="color: #007bff;"></i>
                        </button>
                        <span style="flex-grow: 1;">${fileName}</span>
                    </div>`;
                });

                fileListHtml += '</div>';

                $('#menu-text p').html(fileListHtml);

                // Initialisiere eine Variable zum Speichern des aktuell abspielenden Audios


                // Event-Handler für Play-Sound-Buttons
                $('.play-sound').click(function () {
                    let audioFile = $(this).data('file');
                    let audioPath = '/view/includes/sip/tone/' + audioFile;
                    let currentPlayingAudio = null;
                    // Stoppe den aktuellen Ton, wenn er spielt
                    if ($(this).hasClass('playing')) {
                        $(this).removeClass('playing').find('i').removeClass('bi-pause-circle-fill').addClass('bi-play-circle-fill');
                        currentPlayingAudio.pause();
                        currentPlayingAudio = null;
                    } else {
                        if (currentPlayingAudio) {
                            $('.play-sound.playing').removeClass('playing').find('i').removeClass('bi-pause-circle-fill').addClass('bi-play-circle-fill');
                            currentPlayingAudio.pause();
                        }
                        let audio = new Audio(audioPath);
                        audio.play();
                        $(this).addClass('playing').find('i').removeClass('bi-play-circle-fill').addClass('bi-pause-circle-fill');
                        currentPlayingAudio = audio;
                        audio.onended = function () {
                            $(this).removeClass('playing').find('i').removeClass('bi-pause-circle-fill').addClass('bi-play-circle-fill');
                        }.bind(this);
                    }
                });
            },
            error: function (xhr, status, error) {
                $('#menu-text p').html('Fehler beim Laden der Sounddateien.');
            }
        });
    });*/


    window.playTone = function (fileName) {
        console.log('/view/includes/sip/tone/' + fileName); // Hier würde der Pfad in die Konsole geschrieben
        // Hier könnten Sie den Code zum Abspielen des Tons und zum Senden des Signals an die Datenbank hinzufügen
        // Zum Beispiel:
        // var audio = new Audio('/view/includes/sip/tone/' + fileName);
        // audio.play();
        // ... AJAX-Anfrage zum Senden des Signals ...
    };

    $('#menu').on('click', '#history', function () {
        // Setzen des aktiven Button-Stils
        $('#history').addClass('active');
        $('#settings').removeClass('active');

        // AJAX-Anfrage, um die letzten 10 Anrufe zu holen
        $.ajax({
            url: '/view/includes/sip/sip_load.php',
            type: 'POST',
            data: { action: 'load_log' },
            dataType: 'json',
            success: function (response) {
                $('#menu-text p').empty();
                $('#menu-text i').hide();

                // Gruppieren der Anrufe nach callID
                const callsGroupedByCallId = response.calls.reduce((acc, call) => {
                    (acc[call.callId] = acc[call.callId] || []).push(call);
                    return acc;
                }, {});

                // Erstellen der Anruf-Einträge
                Object.values(callsGroupedByCallId).forEach((calls) => {
                    const lastCall = calls[calls.length - 1];
                    const callDate = new Date(lastCall.datetime);
                    const formattedTime = callDate.toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' });
                    const callIcon = lastCall.direction === 'incoming' ? 'bi-telephone-inbound' : 'bi-telephone-outbound';
                    const phoneNumber = lastCall.direction === 'incoming' ? lastCall.from_number : lastCall.to_number; // Bedingte Anzeige der Nummer
                    const callColor = calls.length === 3 ? 'green' : 'red';
                    // Erstellen des HTML für den Anruf
                    var callHtml = `
                    <div class="call-entry">
                    <i class="bi ${callIcon}" style="color: ${callColor};"></i>
                        <span class="phone-number">${phoneNumber}</span>
                        <span class="call-time">${formattedTime}</span>
                    </div>`;
                    $('#menu-text p').append(callHtml);
                });
            },
            error: function (xhr, status, error) {
                console.error('Fehler beim Laden der Anrufliste:', error);
            }
        });
    });


});


function sendCallData(event, direction, toNumber, fromNumber, callId) {
    $.ajax({
        url: '/view/includes/sip/sip_load.php', // Ersetzen Sie dies mit dem Pfad zu Ihrem PHP-Backend-Script
        type: 'POST',
        data: {
            action: 'log_call_event', // Annahme, dass Ihr Backend diese Aktion verarbeitet
            event: event,
            direction: direction,
            to_number: toNumber,
            from_number: fromNumber,
            callID: callId
        },
        dataType: 'json',
        success: function (response) {
            console.log('Serverantwort:', response);
            // Weitere Logik nach erfolgreichem AJAX-Aufruf
        },
        error: function (xhr, status, error) {
            console.error('Fehler beim Senden der Daten:', error);
            // Fehlerbehandlung
        }
    });
}

function showErrorNotification(data) {
    console.log('Failed event:', data);
    var errorMessage;

    switch (data.cause) {
        case JsSIP.C.causes.BUSY:
        case JsSIP.C.causes.REJECTED:
            errorMessage = 'Anruf abgelehnt.';
            break;
        case JsSIP.C.causes.UNAVAILABLE:
            errorMessage = 'Teilnehmer nicht verfügbar.';
            break;
        case JsSIP.C.causes.NOT_FOUND:
            errorMessage = 'Nummer nicht gefunden.';
            break;
        case JsSIP.C.causes.ADDRESS_INCOMPLETE:
            errorMessage = 'Nummer unvollständig.';
            break;
        case JsSIP.C.causes.CANCELED:
            // Keine Aktion ausführen und die Funktion sofort verlassen
            return;
        default:
            errorMessage = 'Unbekannter Fehler: ' + data.cause;
    }

    // Überprüfen, ob eine Fehlermeldung vorhanden ist, bevor eine Benachrichtigung angezeigt wird
    if (errorMessage) {
        var errorNotification = '<div id="errorNotification" style="background-color: red; color: white; padding: 10px; margin-top: 10px; text-align: center;">' +
            'Fehler: ' + errorMessage + '</div>';

        $('#callControl').prepend(errorNotification);

        setTimeout(function () {
            $('#errorNotification').remove();
        }, 5000); // Fehlermeldung wird nach 5 Sekunden entfernt
    }
}

