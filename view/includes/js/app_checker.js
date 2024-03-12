$(document).ready(function () {

    $('.pdf-controls').hide();
    $('#selectedDate').change(function () {
        var selectedDate = $(this).val();
        loadUsers(selectedDate);
    });

    loadUsers($('#selectedDate').val());

    $(document).click(function () {
        $("#contextMenu").hide();
    });

    // Kontextmenü für jeden Benutzereintrag
    $("#userList").on("contextmenu", ".user-item", function (e) {
        e.preventDefault();

        var username = $(this).find(".user-name").text();
        var date = $('#selectedDate').val();
        var mail = $(this).data('email');
        var namePosition = $(this).find(".user-name").position();

        // Positionieren und Anzeigen des Kontextmenüs
        $("#contextMenu").css({
            display: "block",
            left: namePosition.left + $(this).find(".user-name").outerWidth(), // Position rechts neben dem Namen
            top: namePosition.top + $(this).offset().top // Position auf der gleichen Höhe wie der Name
        });

        // Klicken auf "E-Mail senden"
        $("#sendEmail").off('click').on("click", function () {
            sendEmail(username, date, mail);
            $("#contextMenu").hide();
        });
    });
});

$(document).ready(function () {
    // Binden Sie die Event-Handler für die Lupenfunktion und den Schieberegler
    $('.magnify-toggle-btn').on('click', function () {
        magnifyEnabled = !magnifyEnabled;
        if (magnifyEnabled) {
            $('.pdf-container').on('mousemove', 'canvas', function (e) {
                showMagnifyGlass(e, $(this));
            });
        } else {
            $('.pdf-container').off('mousemove', 'canvas');
            $('#magnify-glass').remove();
        }
    });

    $('.magnify-size-slider').on('input change', function () {
        magnifyRadius = $(this).val();

        // Aktualisieren Sie die Größe der Lupe, wenn sie bereits aktiv ist
        if (magnifyEnabled) {
            $('#magnify-glass').css({
                'height': magnifyRadius * 1.5 + 'px',
                'width': magnifyRadius + 'px'
            });
            // Trigger ein Mousemove-Event, um die Position der Lupe zu aktualisieren
            $('.pdf-container canvas').trigger('mousemove');
        }
    });
});

function loadUsers(date) {
    $.ajax({
        url: 'view/load/appcheck_load.php',
        type: 'POST',
        data: { action: 'fetch_users', date: date },
        success: function (response) {
            if (response) {
                var users = JSON.parse(response);
                updateUsersList(users);
            } else {
                console.error("Leere Antwort vom Server");
            }
        },
        error: function (xhr, status, error) {
            console.error("Ein Fehler ist aufgetreten: " + error);
        }
    });
}

function updateUsersList(users) {
    console.log(users);
    var usersList = $('#userList');
    usersList.empty();

    users.forEach(function (user) {
        var completedEntries = user.entries.filter(entry => entry.err_check == 1).length;
        var totalEntries = user.entries.length;

        var userItem = $('<li class="user-item" data-email="' + user.email + '"><span class="user-name">' + user.hausbegeher + '</span> <span class="entry-count">' + completedEntries + ' / ' + totalEntries + '</span></li>');

        if (completedEntries === totalEntries) {
            userItem.addClass('completed-user');
            userItem.find('.entry-count').prepend('<i class="bi bi-check2-all"></i> ');
            userItem.css('color', 'green');
        } else {
            userItem.click(function () {
                // Entfernen Sie die Markierung von allen anderen Benutzern
                $('.user-item').removeClass('selected-user');
                // Markieren Sie den aktuell ausgewählten Benutzer
                $(this).addClass('selected-user');
                displayUserEntries(user.entries);
            });
        }

        usersList.append(userItem);
    });
}
var magnifyEnabled = false;
var magnifyRadius = 200; // Startgröße der Lupe



function displayUserEntries(entries) {
    var entriesList = $('<ul>').css({ 'user-select': 'none', 'cursor': 'pointer' }); // Textauswahl deaktivieren und Mauszeiger ändern
    $('.pdf-controls').hide(); // Verstecken Sie die Steuerelemente zu Beginn

    entries.forEach(function (entry, index) {
        // Erstellen Sie das Grundelement für jeden Eintrag
        var entryItem = $('<li class="entry-item" data-uid="' + entry.uid + '">HomeID: ' + entry.homeid + ' - Zeit: ' + entry.time + '</li>');
        // Icon für Fehlerwarnung hinzufügen
        var errorIcon = $('<i class="ri-error-warning-line"></i>').css({ 'cursor': 'pointer' });
        entryItem.append(errorIcon);

        errorIcon.on('click', function () {
            loadAndShowErrors(entry.uid);
        });

        entryItem.hover(
            function () {
                $(this).css('background-color', '#f0f0f0'); // Farbe beim Hovern
            },
            function () {
                $(this).css('background-color', ''); // Farbe zurücksetzen
            }
        );

        // Fügen Sie das `entryItem`-Element zur Liste hinzu
        entriesList.append(entryItem);

        if (entry.err_check != 1) {
            // Erstellen Sie PDF-Container und Statusinformationen für nicht abgeschlossene Einträge
            var pdfContainer = $('<div class="pdf-container" id="pdf-' + index + '"></div>').data('fileUrl', entry.appt_file);

            entryItem.click(function () {
                console.log(entry.err_check);
                // Setzen Sie die aktuelle Seite und die Gesamtseitenanzahl
                $('.page-indicator').text('Seite: ' + (currentPage[index] || 1) + ' von ' + (totalPages[index] || 1));
                // Die Gesamtzahl der Seiten wird später durch das Laden des PDF festgelegt

                // Zeigen Sie die Steuerelemente an, wenn ein Eintrag ausgewählt wird
                $('.pdf-controls').show();

                // Binden Sie die Ereignisse an die Navigationsbuttons
                $('.pdf-nav-left').off('click').on('click', function () { changePage(index, 'prev'); });
                $('.pdf-nav-right').off('click').on('click', function () { changePage(index, 'next'); });

                // Binden Sie das Ereignis an den Screenshot-Button
                $('.screenshot-btn').off('click').on('click', function () { captureScreenshot(entry, index); });


                $('.report-error-btn').off('click').on('click', function () {
                    reportErrorWithoutImage(entry.uid);
                });

                $('.all-errors-found-btn').off('click').on('click', function () {
                    setAllErrorsFound(entry.uid);
                });


                $('.pdf-container').on('mousemove', 'canvas', function (e) {
                    if (magnifyEnabled) {
                        showMagnifyGlass(e, $(this));
                    }
                });


                // Blenden Sie alle anderen PDF-Container aus und zeigen Sie den aktuellen an
                $('.pdf-container').not(pdfContainer).slideUp(function () {
                    $(this).empty();
                    $(this).next('.status-info').hide();
                });
                togglePdf(pdfContainer, entry.appt_file, index);

            });

            // Fügen Sie diese Elemente zur Liste hinzu
            entriesList.append(pdfContainer);
        } else {
            // Markieren Sie den Eintrag als abgeschlossen
            entryItem.addClass('completed-entry');
            entryItem.prepend('<i class="bi bi-check2-all"></i> '); // Check-Symbol am Anfang hinzufügen
        }
    });

    $('.rightContainer').empty().append(entriesList).show();
    // Setzen Sie die Höhe des rightContainer, um Platz für die Steuerelemente zu lassen
    $('.rightContainer').css('bottom', $('.pdf-controls').outerHeight() + 'px');
}



function showMagnifyGlass(e, canvas) {
    var magnifyGlass = $('#magnify-glass');
    if (magnifyGlass.length === 0) {
        magnifyGlass = $('<div id="magnify-glass"></div>').css({
            'border': '1px solid black',
            'cursor': 'none',
            'height': magnifyRadius * 1.5 + 'px',
            'width': magnifyRadius + 'px',
            'position': 'absolute',
            'pointer-events': 'none',
            'z-index': 10000,
            'overflow': 'hidden',
            'border-radius': '0'
        });
        $('body').append(magnifyGlass);
    }

    var canvasOffset = canvas.offset();
    var canvasWidth = canvas.width();
    var canvasHeight = canvas.height();

    var offsetX = e.pageX - canvasOffset.left;
    var offsetY = e.pageY - canvasOffset.top;

    // Verhindern, dass die Lupe über den Rand des Canvas hinausgeht
    var leftPos = e.pageX - magnifyRadius / 2;
    var topPos = e.pageY - magnifyRadius * 1.5 / 2;

    // Anpassung an die Canvas-Grenzen
    leftPos = Math.max(canvasOffset.left, Math.min(leftPos, canvasOffset.left + canvasWidth - magnifyRadius));
    topPos = Math.max(canvasOffset.top, Math.min(topPos, canvasOffset.top + canvasHeight - magnifyRadius * 1.5));

    magnifyGlass.css({
        'left': leftPos + 'px',
        'top': topPos + 'px',
        'background-image': 'url(' + canvas.get(0).toDataURL() + ')',
        'background-repeat': 'no-repeat',
        'background-size': (canvasWidth * 2) + 'px ' + (canvasHeight * 2) + 'px',
        'background-position': '-' + (offsetX * 2 - magnifyRadius / 2) + 'px -' + (offsetY * 2 - magnifyRadius * 1.5 / 2) + 'px'
    });
}






var currentPage = [];
var totalPages = [];



function getRelativePath(fileUrl) {
    var basePath1 = '/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de';
    var basePath2 = '/var/www/html';
    console.log('ASDASDASDASDASD');
    if (fileUrl.includes(basePath1)) {
        return fileUrl.replace(basePath1, '');
    } else if (fileUrl.includes(basePath2)) {
        return fileUrl.replace(basePath2, '');
    } else {
        return fileUrl; // Zurückgeben des Originalpfads, falls keine Übereinstimmung
    }
}

function togglePdf(container, fileUrl, index) {
    if (typeof pdfjsLib === "undefined") {
        pdfjsLib = window['pdfjs-dist/build/pdf'];
    }

    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://mozilla.github.io/pdf.js/build/pdf.worker.js';

    $('.pdf-container').not(container).slideUp(function () {
        $(this).empty();
    });

    if (container.is(':empty')) {
        var relativePath = getRelativePath(fileUrl);

        var loadingTask = pdfjsLib.getDocument(relativePath);
        loadingTask.promise.then(function (pdf) {
            console.log('PDF loaded');
            totalPages[index] = pdf.numPages;
            currentPage[index] = 1;
            renderPage(pdf, currentPage[index], container);

        }, function (reason) {
            console.error(reason);
        });
    } else {
        container.slideToggle(function () {
            if (!container.is(':visible')) {
                container.empty();
            }
        });
    }
}

function changePage(index, direction) {
    var container = $('#pdf-' + index);
    var fileUrl = container.data('fileUrl');
    var relativePath = getRelativePath(fileUrl);
    if (!fileUrl) return;

    var loadingTask = pdfjsLib.getDocument(relativePath);
    loadingTask.promise.then(function (pdf) {
        if (direction === 'next' && currentPage[index] < totalPages[index]) {
            currentPage[index]++;
        } else if (direction === 'prev' && currentPage[index] > 1) {
            currentPage[index]--;
        }

        renderPage(pdf, currentPage[index], container);

        // Aktualisieren des Seitenindikators nach dem Wechseln der Seite
        container.siblings('.pdf-navigation-container').find('.page-indicator')
            .text('Seite: ' + currentPage[index] + ' von ' + totalPages[index]);
    });
}







function renderPage(pdf, pageNumber, container) {
    pdf.getPage(pageNumber).then(function (page) {
        console.log('Page loaded');

        // Definieren Sie die maximale Breite und Höhe
        var maxWidth = 1200; // Maximale Breite in Pixeln
        var maxHeight = 1000; // Maximale Höhe in Pixeln

        // Ursprüngliche Größe der PDF-Seite
        var viewport = page.getViewport({ scale: 1 });

        // Skalierung basierend auf der maximalen Breite und Höhe berechnen
        var scale = Math.min(maxWidth / viewport.width, maxHeight / viewport.height);

        // Skalierten Viewport erhalten
        viewport = page.getViewport({ scale: scale });

        var canvas = document.createElement('canvas');
        var context = canvas.getContext('2d');
        canvas.height = viewport.height;
        canvas.width = viewport.width;

        var renderContext = {
            canvasContext: context,
            viewport: viewport
        };
        var renderTask = page.render(renderContext);
        renderTask.promise.then(function () {
            console.log('Page rendered');
        });

        container.empty().append(canvas);
    });
}



function captureScreenshot(entry, index) {
    var overlay = $('<div class="screenshot-overlay"></div>').appendTo('body');
    var selection = $('<div class="screenshot-selection"></div>').appendTo(overlay);
    var confirmIcon = $('<i class="bi bi-check-circle confirm-icon"></i>').hide().appendTo('body');
    var cancelIcon = $('<i class="bi bi-x-circle cancel-icon"></i>').hide().appendTo('body');
    var startX, startY, endX, endY;

    overlay.on('mousedown', function (e) {
        startX = e.pageX;
        startY = e.pageY;
        selection.css({
            top: startY,
            left: startX,
            width: 0,
            height: 0
        }).show();
        confirmIcon.hide();
        cancelIcon.hide();
        overlay.on('mousemove', onMouseMove);
    });

    $(document).on('mouseup', function (e) {
        overlay.off('mousemove', onMouseMove);
        endX = e.pageX;
        endY = e.pageY;

        // Berechnung der tatsächlichen Start- und Endkoordinaten
        var actualStartX = Math.min(startX, endX);
        var actualStartY = Math.min(startY, endY);
        var actualEndX = Math.max(startX, endX);
        var actualEndY = Math.max(startY, endY);

        var rectWidth = actualEndX - actualStartX;
        var rectHeight = actualEndY - actualStartY;

        var pdfContainer = $('#pdf-' + index);
        if (!isSelectionInsidePdfContainer(actualStartX, actualStartY, rectWidth, rectHeight, pdfContainer)) {
            alert("Außerhalb des PDF-Bereiches");
            removeScreenshotElements();
            return; // Beenden der Funktion, da die Auswahl außerhalb des PDFs liegt
        }


        if (rectWidth && rectHeight) {
            positionIcons(actualEndX, actualEndY);
            setupIconClickHandlers(actualStartX, actualStartY, rectWidth, rectHeight);
        }
    });

    function onMouseMove(e) {
        var width = e.pageX - startX;
        var height = e.pageY - startY;
        selection.css({
            width: Math.abs(width),
            height: Math.abs(height)
        });
        if (width < 0) {
            selection.css({ left: e.pageX });
        }
        if (height < 0) {
            selection.css({ top: e.pageY });
        }
    }

    function positionIcons(x, y) {
        confirmIcon.css({
            top: y,
            left: x
        }).show();

        cancelIcon.css({
            top: y,
            left: x - 30
        }).show();
    }

    function setupIconClickHandlers(startX, startY, rectWidth, rectHeight) {
        confirmIcon.off('click').on('click', function () {
            removeScreenshotElements();
            setTimeout(function () {
                html2canvas(document.body, {
                    x: startX + window.scrollX,
                    y: startY + window.scrollY,
                    width: rectWidth,
                    height: rectHeight,
                    useCORS: true,  // CORS-Einstellungen explizit angeben
                    willReadFrequently: true  // Verbesserung der Leistung für getImageData-Aufrufe
                }).then(canvas => {
                    openConfirmationBox(canvas.toDataURL(), entry.homeid, entry.uid);
                }).catch(error => {
                    console.error('Fehler bei der Bildverarbeitung:', error);  // Fehlerbehandlung hinzugefügt
                });
            }, 500);
        });
        cancelIcon.off('click').on('click', function () {
            removeScreenshotElements();
        });
    }

    function removeScreenshotElements() {
        overlay.remove();
        confirmIcon.hide();
        cancelIcon.hide();
        $(document).off('mouseup');
    }
}

function isSelectionInsidePdfContainer(startX, startY, width, height, pdfContainer) {
    var pdfPosition = pdfContainer.offset();
    var pdfWidth = pdfContainer.width();
    var pdfHeight = pdfContainer.height();

    // Überprüfen, ob die Auswahl innerhalb des PDF-Containers liegt
    return (startX >= pdfPosition.left && startY >= pdfPosition.top &&
        startX + width <= pdfPosition.left + pdfWidth &&
        startY + height <= pdfPosition.top + pdfHeight);
}


function openConfirmationBox(imageData, homeid, uid) {
    $.confirm({
        title: 'Fehler melden für HomeID ' + homeid,
        content: '' +
            '<div style="text-align: center;">' +
            '   <a href="' + imageData + '" id="errorImageLink">' +
            '       <img src="' + imageData + '" style="max-width:100%; cursor: pointer;" id="errorImage" />' +
            '   </a>' +
            '</div>' +
            '<textarea id="error-description" placeholder="Fehlerbeschreibung" style="width:100%; margin-top:10px;"></textarea>',
        onContentReady: function () {
            // Initialisieren von Magnific Popup
            $('#errorImageLink').magnificPopup({
                type: 'image',
                closeOnContentClick: true,
                mainClass: 'mfp-img-mobile',
                image: {
                    verticalFit: true
                }
            });
        },
        buttons: {
            bestätigen: function () {
                var errorDescription = $('#error-description').val();
                if (errorDescription.trim() === '') {
                    alert('Bitte geben Sie eine Fehlerbeschreibung ein.');
                    return false;
                }

                // Vorbereiten der Daten für das Backend
                var postData = {
                    action: 'send_uid_error',
                    uid: uid,
                    error: errorDescription,
                    pic: imageData
                };

                // Senden der Daten an das Backend
                $.post('view/load/appcheck_load.php', postData, function (response) {
                    console.log(response);
                    loadErrorsForEntry(uid);
                }).fail(function (jqXHR, textStatus, errorThrown) {
                    console.error('Fehler beim Senden der Anfrage: ' + textStatus, errorThrown);
                });
            },
            abbrechen: function () {
                // Logik für das Abbrechen der Aktion
            }
        }
    });
}


function reportErrorWithoutImage(uid) {
    $.confirm({
        title: 'Fehler melden',
        content: '' +
            '<form action="">' +
            '   <div class="form-group">' +
            '       <label>Fehlerbeschreibung</label>' +
            '       <textarea id="error-description" class="error-description" placeholder="Beschreiben Sie den Fehler" required></textarea>' +
            '   </div>' +
            '</form>',
        buttons: {
            bestätigen: function () {
                var errorDescription = this.$content.find('.error-description').val().trim();
                if (!errorDescription) {
                    $.alert('Bitte geben Sie eine Fehlerbeschreibung ein.');
                    return false;
                }
                // Senden der Daten an das Backend
                $.post('view/load/appcheck_load.php', {
                    action: 'send_uid_error',
                    uid: uid,
                    error: errorDescription,
                    pic: '' // Kein Bild
                }, function (response) {
                    // Logik nach dem Senden
                    loadErrorsForEntry(uid);
                    console.log(response);
                });
            },
            abbrechen: function () {
                // Abbruchlogik
            }
        }
    });
}

function refreshUserList(date) {
    $.ajax({
        url: 'view/load/appcheck_load.php',
        type: 'POST',
        data: { action: 'fetch_users', date: date },
        success: function (response) {
            if (response) {
                var users = JSON.parse(response);
                updateUsersList(users);
            }
        },
        error: function (xhr, status, error) {
            console.error("Ein Fehler ist aufgetreten: " + error);

        }
    });
}

function setAllErrorsFound(uid) {
    $.confirm({
        title: 'Bestätigung',
        content: '' +
            '<p>Sind Sie sicher, dass Sie alle Fehler für diese HomeID als gefunden markieren möchten?</p>' +
            '<div class="slider-container">' +
            '   <input type="range" id="error-rating-slider" class="custom-slider" min="0" max="10" value="0">' +
            '   <p>Bewertung: <span id="slider-value">Ungültig</span></p>' +
            '</div>',
        onContentReady: function () {
            var self = this;
            this.$content.find('#error-rating-slider').on('input change', function () {
                var value = $(this).val();
                self.$content.find('#slider-value').text(value == 0 ? 'Ungültig' : (value == 10 ? 'Perfekt' : value));
            });
        },
        buttons: {
            bestätigen: function () {
                var sliderValue = this.$content.find('#error-rating-slider').val();
                $.post('view/load/appcheck_load.php', {
                    action: 'set_all_errors_found',
                    uid: uid,
                    rating: sliderValue // Senden des Slider-Wertes
                }, function (response) {
                    // Logik nach dem Senden
                    console.log(response);
                    $('.entry-item[data-uid="' + uid + '"]').addClass('completed-entry');
                    $('.entry-item[data-uid="' + uid + '"]').prepend('<i class="bi bi-check2-all"></i>');
                    refreshUserList($('#selectedDate').val());
                });
            },
            abbrechen: function () {
                // Abbruchlogik
            }
        }
    });
}


function sendEmail(username, date, mail) {
    console.log("Send email to", username, "on date", date);
    $.ajax({
        url: 'view/load/appcheck_load.php', // Pfad zu Ihrem PHP-Script
        type: 'POST',
        data: {
            action: 'check_mail_send',
            username: username,
            mail: mail,
            date: date
        },
        success: function (response) {
            console.log(response);
            // Hier können Sie zusätzliche Logik hinzufügen, z.B. eine Benachrichtigung anzeigen
        },
        error: function (xhr, status, error) {
            console.error("Fehler beim Senden der Anfrage: " + error);
        }
    });
}


function loadAndShowErrors(uid) {
    $.ajax({
        url: 'view/load/appcheck_load.php',
        type: 'POST',
        data: { action: 'fetch_errors_for_uid', uid: uid },

        success: function (response) {
            try {
                var cleanedResponse = response.replace(/^\[\]/, '');
                var errors = JSON.parse(cleanedResponse);
                showErrorsInDialog(errors);
            } catch (e) {
                console.error('Fehler beim Parsen der JSON-Daten:', e);
            }
        },
        error: function () {
            console.error("Fehler beim Laden der Fehlerdaten");
        }
    });
}

function showErrorsInDialog(errors) {
    var errorContent = $('<div></div>');
    if (errors.length === 0) {
        errorContent.append('<p class="no-errors-message">Keine Fehler gefunden.</p>');
    } else {
        errors.forEach(function (error) {
            var errorItem = $('<div class="error-item"></div>');
            errorItem.append('<p>' + error.error + '</p>');
            if (error.pic) {
                var imagePath = error.pic.replace('/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de', '');
                errorItem.append('<img src="' + imagePath + '" alt="Fehlerbild">');
            }

            var deleteButton = $('<i class="bi bi-trash3 delete-icon"></i>'); // Ändern in ein Icon
            deleteButton.on('click', function () {
                confirmDelete(error.uid);
            });
            errorItem.append(deleteButton);

            errorContent.append(errorItem);
        });
    }

    $.confirm({
        title: 'Fehlerübersicht',
        content: errorContent,
        columnClass: 'col-md-12', // Vollbild
        animateFromElement: false,
        animation: 'none', // Deaktiviert Animationen
        buttons: {
            schließen: function () {
                // Schließlogik
            }
        }
    });
}

function editError(uid) {
    // Logik zum Bearbeiten eines Fehlers
}


function confirmDelete(uid) {
    $.confirm({
        title: 'Bestätigen Sie das Löschen',
        content: 'Sind Sie sicher, dass Sie diesen Eintrag löschen möchten?',
        buttons: {
            bestätigen: function () {
                // Senden der Lösch-Anfrage an das Backend
                $.post('view/load/appcheck_load.php', {
                    action: 'delete_error',
                    uid: uid
                }, function (response) {
                    // Logik nach dem Löschen, z.B. schließen des Dialogs oder Aktualisieren der Ansicht
                });
            },
            abbrechen: function () {
                // Abbruchlogik
            }
        }
    });
}


