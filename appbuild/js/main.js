let deferredPrompt;

// Listen for the beforeinstallprompt event
window.addEventListener('beforeinstallprompt', (e) => {
    // Prevent Chrome 67 and earlier from automatically showing the prompt
    e.preventDefault();

    // Stash the event so it can be triggered later
    deferredPrompt = e;

    // Display the install button when this event is fired
    showInstallButton();
});

function showInstallButton() {
    // Assuming you have a button with the id 'mainMenu_installPWA' in your HTML
    const installButton = document.getElementById('mainMenu_installPWA');

    if (!installButton) {
        console.error("Install button (mainMenu_installPWA) not found!");
        return;
    }

    // Display the button
    installButton.style.display = 'block';

    // Bind the click event to the button
    installButton.addEventListener('click', function () {
        // Hide the install button once it's clicked
        this.style.display = 'none';

        // If there's a deferredPrompt, show it
        if (deferredPrompt) {
            showInstallPrompt();
        } else {
            console.warn("No deferred prompt available");
        }
    });
}

function showInstallPrompt() {
    if (!deferredPrompt) {
        console.error("No deferred prompt to show");
        return;
    }

    // Show the prompt
    deferredPrompt.prompt();

    // Wait for the user to respond to the prompt
    deferredPrompt.userChoice.then((choiceResult) => {
        if (choiceResult.outcome === 'accepted') {
            console.log('User accepted the A2HS prompt');
        } else {
            console.log('User dismissed the A2HS prompt');
        }

        // Clear the deferred prompt
        deferredPrompt = null;
    });
}

function fixContentHeight() {
    var headerHeight = $('.header').outerHeight(true); // Get header height including margins
    var searchheight = $('#searchInput').outerHeight(true);
    var availableHeight = $(window).height() - headerHeight - searchheight; // Calculate available height

    // Set the height for the main content areas
    $('#appointmentsContent, #leaflet').height(availableHeight);
}

function adjustModalPosition() {
    var windowHeight = $(window).height();
    var desiredGap = windowHeight * 0.25; // for a 25% gap
    desiredGap = Math.max(50, Math.min(desiredGap, 100)); // minimum 50px and maximum 150px
    $('.modal-dialog.modal-fullscreen').css({
        'max-height': `calc(100% - ${desiredGap}px)`,
        'top': `${desiredGap}px`
    });
}

$(window).on('resize', function () {
    adjustModalPosition();
    fixContentHeight();
}).trigger('resize'); // Trigger the event on page load too



const isiOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
var canvas; // Holds Drawing canvas in survey
var signaturePad // holds the sign for the customer

function isLightboxVisible() {
    return $('#customLightbox').is(':visible');
}

function handleSwitchChange(inputElem) {
    var $this = inputElem; // current input element
    var $label = $this.next('.form-check-label');
    var $checkIcon = $label.find('.bi-check-lg');
    var $xIcon = $label.find('.bi-x-lg');

    // Toggle icons based on the checked state
    if ($this.prop('checked')) {
        $checkIcon.show();
        $xIcon.hide();
    } else {
        $checkIcon.hide();
        $xIcon.show();
    }

    // Handle ownerSwitch logic
    if ($this.attr('id') === 'ownerSwitch') {
        if ($this.prop('checked')) {
            $('#isOwnerWrapper').hide();
        } else {
            $('#isOwnerWrapper').show();
        }
    }
}


const calendarApoointmentNumbers = {
    "2023-10-23": {
        total: 22,
        done: 20,
        canceled: 2
    },
    "2023-10-24": {
        total: 17,
        done: 12,
        canceled: 5
    },
    "2023-11-01": {
        total: Math.floor(Math.random() * 30),
        done: Math.floor(Math.random() * 25),
        canceled: Math.floor(Math.random() * 5)
    },
    "2023-11-02": {
        total: Math.floor(Math.random() * 30),
        done: Math.floor(Math.random() * 25),
        canceled: Math.floor(Math.random() * 5)
    },
    // ... you can continue this pattern for each date until 20th
};


const customToken = 'YHf6Sj1fzyqBdc3G4LgeTtSsYZZgWK6uQFsl6UgtZQletTkKVtOUmQ6c0c9wgW09oHvaG1ZyUL0CwMtUMbXDgFGWmI2u1ybl4B5wv62vBpk6040ic5dyq5AbqIkVYW8O';
const serverEndpoint = 'https://app.scan4-gmbh.de/upload.php';


var mapMarkers = [];
const color_open = "#2196f3";
const color_planned = "#f3d921";
const color_pending = "#da831c";
const color_done = "#2fbb4f";
const color_stopped = "#f76464";


var scan4Icon;
let scan4Marker = null;  // We'll use this to store our custom marker


class AppManager {
    constructor() {
        this.authDb = new Dexie('AuthDatabase');
        this.initializeAuthDB();
        this.appointmentsInstance = null;
        this.username = null;

        $(document).ready(() => {
            this.initializeUI();
            this.setupLoginButtonListener();
        });
    }

    initializeAuthDB() {
        this.authDb.version(1).stores({
            authTokens: '++id, token, username' // Define your table for tokens
        });
    }

    checkForToken() {
        return this.authDb.authTokens.orderBy('id').reverse().first()
            .then(entry => !!entry); // Returns true if there is a token, false otherwise
    }

    initializeUI() {
        this.checkForToken().then(hasToken => {
            if (hasToken) {
                this.authDb.authTokens.orderBy('id').reverse().first().then(entry => {
                    this.username = entry.username; // Set the username as soon as it's available

                    if (this.isOnline()) {
                        this.validateTokenWithServer(entry.token).then(isValid => {
                            if (isValid) {
                                this.showMainContent();
                            } else {
                                this.showLoginScreen();
                            }
                        });
                    } else {
                        // If offline, assume the token is valid
                        this.showMainContent();
                    }
                });
            } else {
                this.showLoginScreen();
            }
        }).catch(error => {
            console.error("Error checking for token:", error);
            this.showLoginScreen();
        });
    }

    showLoginScreen() {
        $("#loginScreen").show().css('opacity', '1');
        $("#mainPageWrapper").hide();
    }

    showMainContent() {
        $("#loginScreen").hide().css('opacity', '0');
        $("#mainPageWrapper").show();
        customCalendar.init();
        this.appointmentsInstance = new Appointments();
        this.appointmentsInstance.Queue_processUploadAutoload();
        customCalendar.onDateSelected = (selectedDate) => {
            this.appointmentsInstance.loadAppointments(selectedDate);
        };
        initializeDocumentReadyAfterLogin();
    }

    validateTokenWithServer(token) {

        if (!this.isOnline()) {
            // Assume token is valid if offline
            return Promise.resolve(true);
        }
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '/auth.php',
                type: 'POST',
                data: { action: 'validateToken', token: token },
                dataType: 'json',
                success: response => {
                    if (response.success) {
                        console.log('Token validatet', response)
                        resolve(true);
                    } else {
                        console.log('Token validation failed', response)
                        this.authDb.authTokens.clear().then(() => resolve(false));
                    }
                },
                error: err => {
                    console.error("Error in authentication process:", err);
                    reject(err);
                }
            });
        });
    }

    setupLoginButtonListener() {
        $("#loginButton").click(() => {
            const username = $("#typeEmailX").val();
            const password = $("#typePasswordX").val();

            this.validateUserWithServer(username, password).then(isAuthenticated => {
                if (isAuthenticated) {
                    this.showMainContent();
                } else {
                    alert("Invalid login credentials");
                }
            }).catch(error => {
                console.error("Error during authentication:", error);
                alert("An error occurred during login.");
            });
        });
    }

    validateUserWithServer(username, password) {
        const self = this;
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '/auth.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'login', // Added action parameter
                    username: username,
                    password: password
                },
                success: function (response) {
                    if (response.success && response.token) {
                        self.storeTokenInIndexedDB(response.token, username).then(() => {
                            self.username = username;
                            resolve(true);
                        }).catch(error => {
                            console.error("Error storing token:", error);
                            reject(error);
                        });
                    } else {
                        console.error('Authentication failed:', response);
                        resolve(false);
                    }
                },
                error: function (xhr, status, error) {
                    reject(error);
                }
            });
        });
    }


    storeTokenInIndexedDB(token, username) {
        return this.authDb.authTokens.put({ token: token, username: username });
    }

    isOnline() {
        if (!navigator.onLine) {
            console.warn('Device is offline');
            return false; // Not online
        }

        const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        if (!connection) {
            console.log('Connection API not supported, assuming good connection');
            return true; // Assume good connection if the API is not supported
        }

        // Logging the current connection status
        console.log(`Connection Status:
            Downlink: ${connection.downlink} Mbps,
            Round-Trip Time: ${connection.rtt} ms,
            Effective Type: ${connection.effectiveType}`);

        // Define thresholds for a bad connection
        const badConnectionParameters = {
            downlink: 1, // less than 1 Mbps is considered bad
            rtt: 300, // more than 300 ms is considered bad
            effectiveType: '2g' // '2g' or worse is considered bad
        };

        // Check if the current connection is below the thresholds
        return !(connection.downlink < badConnectionParameters.downlink ||
            connection.rtt > badConnectionParameters.rtt ||
            connection.effectiveType === badConnectionParameters.effectiveType);
    }


}

// Initialize the application
const appManager = new AppManager();


function initializeDocumentReadyAfterLogin() {

    fixContentHeight();
    window.map = L.map('leaflet').setView([50.115815715403166, 8.69817925361276], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    scan4Icon = L.icon({
        iconUrl: 'favicon-32x32.png',
        iconSize: [32, 32],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32]
    });

    $("#datepickerTrigger").click(function () {
        $("#datepicker").click();
    });
    //---------------------------------------------------------------------------------------------------------------------
    //---------------------------------------------------------------------------------------------------------------------
    // this is used in HBG abbruch tab to deselect button groups
    // When a dropdown item is clicked
    mainpage_init();

    //________________________________________________________//
    // auto update the duct length
    var inputFields = ["#kabellangeStrasse", "#kabellangeHE", "#kabellangeHUP", "#kabellangeTA"];
    // Function to calculate and update the total
    function updateGesamtmeter() {
        var total = 0;

        // Iterate over each input field, get its value and add to the total
        inputFields.forEach(function (inputId) {
            var value = parseFloat($(inputId).val());
            if (!isNaN(value)) {  // Check to ensure the input is a number
                total += value;
            }
        });

        // Update the #gesamtmeter input with the new total
        $("#gesamtmeter").val(total.toFixed(2));  // toFixed(2) ensures the number is rounded to 2 decimal places
        // Manually trigger the input/change event for the #gesamtmeter input
        $("#gesamtmeter").trigger('input');
    }

    // Attach the updateGesamtmeter function to the change event of each input field using event delegation
    $('#surveyPage').on('input', inputFields.join(", "), updateGesamtmeter);

    //________________________________________________________//
    // setup swipe functionality to switch between survey tabs
    var startX, endX;
    var threshold = 150;
    var startY;
    var isRefreshing = false;

    $(document).on('touchstart', '#surveyPage', function (event) {
        if (isLightboxVisible()) return;  // Skip if lightbox is visible
        if ($(event.target).closest('.signature-pad').length) {
            return; // Exit the event handler if touch started from inside a signature pad
        }
        startX = event.originalEvent.changedTouches[0].pageX;
    });

    $(document).on('touchend', '#surveyPage', function (event) {
        if (isLightboxVisible()) return;  // Skip if lightbox is visible
        if ($(event.target).closest('.signature-pad').length) {
            return; // Exit the event handler if touch started from inside a signature pad
        }
        endX = event.originalEvent.changedTouches[0].pageX;
        var distance = endX - startX;

        if (Math.abs(distance) >= threshold) {
            var $currentTab = $('#surveyTabs .nav-link.active');
            if (distance > 0) {
                // Swiped right
                var $prevTab = $currentTab.closest('li').prev().find('.nav-link');
                if ($prevTab.length && $prevTab.closest('li').attr('id') !== 'exitTab') {
                    $prevTab.tab('show');
                }
            } else {
                // Swiped left
                var $nextTab = $currentTab.closest('li').next().find('.nav-link');
                if ($nextTab.length && $nextTab.closest('li').attr('id') !== 'exitTab') {
                    $nextTab.tab('show');
                }
            }
        }
    });

    $('#appointmentsContent').on('touchstart', function (e) {
        startY = e.originalEvent.touches[0].clientY;
    });

    // Determine if the touch move is from top to bottom
    $('#appointmentsContent').on('touchmove', function (e) {
        var moveY = e.originalEvent.touches[0].clientY;

        // Check if dragged downwards, dragged more than a certain threshold, and not currently refreshing
        if (moveY - startY > 150 && !isRefreshing) {
            isRefreshing = true;  // Set the flag to prevent further refreshes
            location.reload();

            // Reset the flag after 3 seconds
            setTimeout(function () {
                isRefreshing = false;
            }, 3000);
        }
    });


    //________________________________________________________//
    // Change icon visibility of swtich buttons
    $('body').on('change', '#surveyPage .form-check-input', function () {
        handleSwitchChange($(this));
    });


    //________________________________________________________//
    // Open lightbox when preview image (not the placeholder) is clicked
    // Load image onto canvas when a photo is clicked
    $(document).on('click', '.photo-container img', function () {
        console.log("Image clicked!");  // Log when an image is clicked

        $('#customLightbox').data('clickedImage', $(this));

        const src = $(this).attr('src');
        console.log("Image src:", src);  // Log the src attribute of the clicked image

        if (src === "images/noimageplaceholder.jpg") {
            console.log("Placeholder image clicked, exiting function.");  // Log when placeholder is clicked
            return; // Exit the function if it's the placeholder image
        }

        // Ensure the canvas is initialized
        if (!canvas) {
            console.log("Initializing new fabric canvas.");  // Log when initializing the canvas
            canvas = new fabric.Canvas('lightboxCanvas');
        } else {
            console.log("Canvas already initialized.");  // Log when canvas is already initialized
        }

        // Fetch the src (base64) of the clicked image
        const imgSrc = $(this).attr('src');
        console.log("Loading image from URL:", imgSrc);  // Log the image source that's being loaded

        fabric.Image.fromURL(imgSrc, function (img) {
            console.log("Image loaded into fabric.");  // Log when the image is loaded into fabric
            canvas.clear();
            console.log("Canvas cleared.");  // Log when the canvas is cleared

            // Set canvas dimensions
            var canvasWidth = 0.9 * $(window).width();
            var canvasHeight = 0.8 * $(window).height();
            console.log(`Setting canvas dimensions: Width = ${canvasWidth}, Height = ${canvasHeight}`);  // Log the dimensions being set

            canvas.setWidth(canvasWidth);
            canvas.setHeight(canvasHeight);

            // Adjust image dimensions and position
            var scaleFactor = Math.min(canvasWidth / img.width, canvasHeight / img.height);
            console.log(`Scaling image by factor: ${scaleFactor}`);  // Log the scaling factor

            img.scale(scaleFactor);
            img.selectable = false;

            // Set canvas dimensions to match scaled image dimensions
            var scaledImageWidth = img.width * scaleFactor;
            var scaledImageHeight = img.height * scaleFactor;
            console.log(`Setting canvas dimensions to image size: Width = ${scaledImageWidth}, Height = ${scaledImageHeight}`);

            canvas.setWidth(scaledImageWidth);
            canvas.setHeight(scaledImageHeight);

            canvas.add(img);
            canvas.centerObject(img);
            canvas.selection = false;
            img.setCoords();

            // Render the canvas
            console.log("Rendering canvas.");  // Log before rendering the canvas
            canvas.renderAll();

            $('#customLightbox').fadeIn();
            console.log("Lightbox faded in.");  // Log when the lightbox is shown
        });
    });

    //______________________________________________________________________________//
    // remove the image
    $(document).on('click', '#lb_remove', function () {
        console.log("Delete button clicked!"); // Log when the delete button is clicked

        // Change the src of the clicked image to the placeholder
        var clickedImage = $('#customLightbox').data('clickedImage');
        if (clickedImage) {
            clickedImage.attr('src', 'images/noimageplaceholder.jpg');
            console.log("Image src set to placeholder."); // Log the src change
        }

        // Clear the canvas
        if (canvas) {
            canvas.clear();
            console.log("Canvas cleared."); // Log when the canvas is cleared
        }

        // Close the lightbox
        $('#customLightbox').fadeOut();
        console.log("Lightbox closed."); // Log when the lightbox is closed
    });



    //______________________________________________________________________________//
    // Attach a change event listener to the checkbox
    $(document).on('change', '#trasse_HEvorhanden', function () {
        if ($(this).is(':checked')) {
            // Show 'Art der HE' section if checkbox is checked
            $('#trasse_HEart').closest('.row').show();
        } else {
            // Hide 'Art der HE' section if checkbox is unchecked
            $('#trasse_HEart').closest('.row').hide();
        }
    });
    //______________________________________________________________________________//




    // Shape selection using the icons on the sidebar
    $(document).on('click', '.sidebar .addCanvas', function () {
        var shapeType = $(this).attr('class'); // Assuming you're using different classes for different shapes

        switch (shapeType) {
            case 'addCanvas hup':
                // Load the SVG and add it to the canvas
                fabric.loadSVGFromURL('images/canvas_hup.svg', function (objects, options) {
                    var shape = fabric.util.groupSVGElements(objects, options);
                    shape.set({
                        left: 100,
                        top: 100,
                        scaleX: 0.5,
                        scaleY: 0.5
                    });
                    canvas.add(shape).renderAll();
                }, null, { ignoreStyles: true });
                break;
            case 'addCanvas nt':
                // Load the SVG and add it to the canvas
                fabric.loadSVGFromURL('images/canvas_tant.svg', function (objects, options) {
                    var shape = fabric.util.groupSVGElements(objects, options);
                    shape.set({
                        left: 100,
                        top: 100,
                        scaleX: 0.5,
                        scaleY: 0.5
                    });
                    canvas.add(shape).renderAll();
                }, null, { ignoreStyles: true });
                break;
            case 'addCanvas he':
                // Load the SVG and add it to the canvas
                fabric.loadSVGFromURL('images/canvas_he.svg', function (objects, options) {
                    var shape = fabric.util.groupSVGElements(objects, options);
                    shape.set({
                        left: 100,
                        top: 100,
                        scaleX: 0.5,
                        scaleY: 0.5
                    });
                    canvas.add(shape).renderAll();
                }, null, { ignoreStyles: true });
                break;
            case 'addCanvas arrow':
                // Load the SVG and add it to the canvas
                fabric.loadSVGFromURL('images/canvas_arrow.svg', function (objects, options) {
                    var shape = fabric.util.groupSVGElements(objects, options);
                    shape.set({
                        left: 100,
                        top: 100,
                        scaleX: 0.5,
                        scaleY: 0.5
                    });
                    canvas.add(shape).renderAll();
                }, null, { ignoreStyles: true });
                break;
            case 'addCanvas font':
                newShape = new fabric.IText('Text', {
                    left: 100,
                    top: 100,
                    fontFamily: 'Arial',
                    fill: '#f00',
                    lineHeight: 1.1,
                    fontSize: 30
                });
                canvas.add(newShape);
                canvas.setActiveObject(newShape);
                newShape.enterEditing();
                newShape.hiddenTextarea.focus();
                return;  // Exit the function early since we've handled the text case
                break;
        }

    });

    // Remove the last shape added using the #lb_undo button
    $(document).on('click', '#lb_undo', function () {
        if (canvas.getObjects().length > 0) {
            canvas.remove(canvas.item(canvas.getObjects().length - 1));
            canvas.renderAll();
        }
    });

    //________________________________________________________________________________//

    var signaturePad;

    // Function to open signature modal
    function openSignatureModal(cardId, title) {
        $.confirm({
            title: title,
            boxWidth: '80%',
            useBootstrap: false,
            content: '<canvas id="signaturePadCanvas"></canvas>',
            onOpenBefore: function () {
                var viewportWidth = $(window).width();
                var viewportHeight = $(window).height();

                // Calculate the maximum sizes
                var maxModalWidth = viewportWidth * 0.8;
                var maxModalHeight = viewportHeight * 0.8;

                // Set the modal size
                this.$content.parent().css({ 'max-width': maxModalWidth, 'max-height': maxModalHeight, 'overflow': 'hidden' });

                // Calculate and set canvas size
                var contentWidth = Math.min(this.$content.width(), maxModalWidth);
                var canvasHeight = Math.min(maxModalHeight, contentWidth / 2);
                $('#signaturePadCanvas').attr('width', contentWidth).attr('height', canvasHeight);
            },
            onContentReady: function () {
                var canvas = document.getElementById('signaturePadCanvas');
                signaturePad = new SignaturePad(canvas);
            },
            buttons: {
                clear: {
                    text: '<i class="bi bi-trash3"></i> Löschen',
                    btnClass: 'btn-red',
                    action: function () {
                        signaturePad.clear();
                        return false;
                    }
                },
                save: {
                    text: '<i class="bi bi-floppy"></i> Speichern',
                    btnClass: 'btn-green',
                    action: function () {
                        var signature = signaturePad.toDataURL();
                        var previewId = cardId === "sign_preview_technican" ? "technican_signature_preview" : "customer_signature_preview";
                        console.log('trigger save action with id', previewId)
                        $('#' + previewId).css('background-image', 'url(' + signature + ')');
                        $('#' + previewId).trigger('signatureSaved', [signature]);
                    }
                },
                cancel: {
                    text: 'Zurück'
                }
            }
        });
    }

    // Event handlers for opening the modal
    $(document).on('click', '#sign_preview_technican, #sign_preview_customer', function () {
        var cardId = $(this).attr('id');
        var title = "Unterschrift " + (cardId === "sign_preview_technican" ? "Techniker" : "Kunde");
        openSignatureModal(cardId, title);
    });

    //________________________________________________________________________________//
    //searchbar funtionality
    $(document).on('input', '#searchInput', function () {
        const searchTerm = $(this).val().toLowerCase().trim();

        $('.appointment').each(function () {
            const lastname = $(this).find('strong').text().toLowerCase();
            const address = $(this).find('.appointmentaddress').text().toLowerCase();

            if (lastname.includes(searchTerm) || address.includes(searchTerm)) {
                this.style.setProperty('display', 'flex', 'important');
            } else {
                this.style.setProperty('display', 'none', 'important');
            }
        });
    });


}

// Delegate event to a static parent, listening for change on '#trasse_HEvorhanden'
$(document).on('input change', '#trasse_HEvorhanden', function () {
    console.log('Triggered');
    if ($(this).is(':checked')) {
        console.log('Checkbox is checked');
        // Show 'Art der HE' section if checkbox is checked
        $('#row_HEartWrapper').show();
    } else {
        console.log('Checkbox is not checked');
        // Hide 'Art der HE' section if checkbox is unchecked
        $('#row_HEartWrapper').hide();
    }
});

$(document).on('input change', '#trasse_leerrohrevorhanden, #trasse_HEvorhanden', function () {
    var elementId = $(this).attr('id');
    switch (elementId) {
        case 'trasse_leerrohrevorhanden':
            console.log('Leerrohrevorhanden Triggered');
            if ($(this).is(':checked')) {
                $('#row_leerrohreHinweisWrapper').show();
            } else {
                $('#row_leerrohreHinweisWrapper').hide();
            }
            break;

        case 'trasse_HEvorhanden':
            if ($(this).is(':checked')) {
                $('#row_HEpunktWrapper').hide();
            } else {
                $('#row_HEpunktWrapper').show();
            }
            break;

        // You can add more cases for different IDs here

        default:
            console.log('Unknown element triggered the event');
            // Optional: handle the case where the element ID doesn't match any case
            break;
    }
});


const initialSurveyState = $('#surveyPage').html(); // grabs the initial skeleton of surverypage
const initialSurveyPrintState = $('#surveyPagePrint').html();

class Survey {
    constructor(db) {
        this.db = db;
        this.initEvents();

        $('#surveyPage').on('click', '#exitSurvey', this.exitSurvey.bind(this));
        this.currentHomeId = null;

        // If static property hasn't been set yet, set it
        if (!Survey.initialSurveyState) {
            Survey.initialSurveyState = $('#surveyPage').html();
        }

        // Assign the static property to the instance property
        this.initialSurveyState = Survey.initialSurveyState;
    }


    initEvents() {
        console.log('@classSurvey __ initEvents')
        $('#startSurvey').on('click', (event) => {
            if (this.currentHomeId) {
                const date = $(event.currentTarget).data('date');
                const index = $(event.currentTarget).data('index');
                // create a const appointmentIdent which holds date and index as an obj
                const appointmentIdent = { 'date': date, 'index': index };
                this.startSurvey(this.currentHomeId, appointmentIdent);
            } else {
                alert('No home selected!');
            }
        });
        $('#takePic').on('click', () => {
            this.takePicture();
        });
        $('#selectPic').on('click', () => {
            this.selectPicture();
        });
        $('#fileInput').on('change', (event) => {
            this.processSelectedPicture(event);
        });
        $('#surveyPage').on('click', '.takePic, .selectPic', (event) => {
            // this just handles the on/off of the button
            //console.log('click image selector triggered')
            const targetElement = $(event.target).closest('label');
            let selector = '';

            if (targetElement.hasClass('takePic')) {
                selector = 'input[type="file"][accept*="camera"]';
            } else if (targetElement.hasClass('selectPic')) {
                selector = 'input[type="file"]:not([accept*="camera"])';
            }

            const inputElement = targetElement.closest('.photo-section').find(selector);

            if (inputElement.prop('disabled')) {
                return; // If the input is disabled, just return and don't process further
            }

            if (selector) {
                inputElement.trigger('custom-click');  // Use a custom event instead of click
            }
        });

        $('#surveyPage').on('click', '#surveyPrint', async (event) => {

            const homeid = this.currentHomeId;
            const username = appManager.username
            try {
                const survey = await this.db.surveys.get(homeid);
                let imagesData = {};
                let modifiedImagesData = {};

                if (survey) {
                    // Retrieve original images
                    const images = await this.db.images.where({ homeid: homeid }).toArray();
                    images.forEach(image => {
                        // console.log('add Image', image);
                        imagesData[image.imgId] = image;
                    });

                    // Retrieve modified images
                    const modifiedImages = await this.db.images_modified.where({ homeid: homeid }).toArray();
                    modifiedImages.forEach(modifiedImage => {
                        console.log('add Modified Image', modifiedImage);
                        modifiedImagesData[modifiedImage.imgId] = modifiedImage;
                    });

                    const appointmentData = this.currentAppointment;
                    //console.log('@@appointmentData', appointmentData)
                    console.log('@@@surveydata send', survey.data)

                    $.ajax({
                        url: '/upload.php',
                        type: 'POST',
                        data: {
                            action: 'survey_data',
                            data: survey.data,
                            homeid: survey.homeid,
                            username: username
                        },
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader('X-Custom-Token', customToken);
                        },
                        dataType: 'json',
                        success: response => {
                            if (response.success) {
                                console.log('@@@Surveydata send', response)
                            } else {
                                console.log('@@@Surveydata send error', response)
                            }
                        },
                        error: err => {
                            console.error("@@@Error sending surveydata:", err);
                        }
                    });

                    console.log('handleImageUpload', imagesData, homeid)
                    handleImageUpload(imagesData, homeid);


                    print_populatefields(survey.data, appointmentData, imagesData, modifiedImagesData);
                } else {
                    console.log('No survey data found for the current home ID');
                }
            } catch (error) {
                console.error('Error retrieving survey data:', error);
            }
        });


        const handleInputChange = async (event) => {
            //console.log('--- handleInputChange ---', event)
            const inputElem = $(event.target);
            let updates = [];

            if (inputElem.is(':radio')) {
                const groupName = inputElem.attr('name');
                // Prepare updates for all radios in the group
                $('input[name="' + groupName + '"]').each((index, elem) => {
                    updates.push({ key: $(elem).attr('id'), value: false });
                });

                // Update the selected radio to true
                updates.find(u => u.key === inputElem.attr('id')).value = true;
            } else if (inputElem.is(':checkbox')) {
                updates.push({ key: inputElem.attr('id'), value: inputElem.prop('checked') });
            } else {
                updates.push({ key: inputElem.attr('id'), value: inputElem.val() });
            }

            // Sequentially store the data for all types of inputs
            for (let update of updates) {
                await this.storeSurveyData(update.key, update.value);
            }
        };
        $('#surveyPage').on('input change', 'input:not([type="file"]), select, textarea', handleInputChange);

        // listen to the sign field
        $('#surveyPage').on('signatureSaved', '.signPreview', (event, signature) => {
            const key = $(event.target).attr('id'); // 'technican_signature_preview' or 'customer_signature_preview'
            this.storeSurveyData(key, signature);
        });


        // Handle image input changes
        $('#surveyPage').on('change', 'input[type="file"]', this.handleImageInput.bind(this));
        $(document).on('click', '#lightboxClose', this.UpdateImage.bind(this));



    }

    UpdateImage() {
        // Convert the canvas content to a data URL (base64 format)
        var dataURL = canvas.toDataURL();

        // Retrieve the clicked image's reference and update its src
        var $clickedImage = $('#customLightbox').data('clickedImage');
        $clickedImage.attr('src', dataURL);

        // Extract the context and imageId from the clicked image
        const context = $clickedImage.closest('.row').data('context');
        const imgId = $clickedImage.attr('id');

        // Save the modified image to the 'images_modified' store in the database
        this.db.images_modified.put({
            homeid: this.currentHomeId,
            imgId: imgId,  // Use the image's id for differentiation
            context: context,
            data: dataURL
        })
            .then(() => {
                console.log('Modified image saved successfully.');
            })
            .catch(error => {
                console.error("Error updating modified image:", error);
            });

        $('#customLightbox').fadeOut();
    }

    handleImageInput(event) {
        console.log('handleImageInput called');
        const fileInput = event.target;
        const context = $(fileInput).data('context') || "base";

        if (fileInput.files && fileInput.files[0]) {
            const img = this.getEmptyPreviewBox(context);
            if (!img) {
                return;
            }

            const imgId = img.attr('id');  // Retrieve the ID of the placeholder image

            const reader = new FileReader();
            reader.onload = (e) => {
                const imageData = e.target.result;
                img.attr('src', imageData);

                // Check if all placeholders are filled after setting new image
                const unfilledPlaceholders = $(`#surveyPage .photo-section:has(input[data-context="${context}"]) .row img[src="images/noimageplaceholder.jpg"]`).length;

                if (unfilledPlaceholders === 0) {
                    // Disable further uploads for this group
                    $(fileInput).closest('.photo-options').find('input[type="file"]').prop('disabled', true);
                    $(fileInput).closest('.photo-options').find('.takePic, .selectPic').addClass('disabled');
                }

                // Save the image data to the database
                this.db.images.put({
                    homeid: this.currentHomeId,
                    imgId: imgId,  // Store the image under its own ID
                    data: imageData
                }).then(() => {
                    console.log('Image saved successfully.');
                    return this.db.surveys.get(this.currentHomeId);
                }).then(survey => {
                    if (!survey) {
                        return this.db.surveys.put({
                            homeid: this.currentHomeId,
                            data: {}
                        });
                    }
                }).then(() => {
                    console.log('Survey marked as started.');
                }).catch(error => {
                    console.error("Error processing image input:", error);
                });
            };
            reader.readAsDataURL(fileInput.files[0]);
        }
    }

    getEmptyPreviewBox(context) {
        const availablePreviews = $(`#surveyPage .photo-section:has(input[data-context="${context}"]) .row img`).length;
        for (let i = 1; i <= availablePreviews; i++) {
            let img = $(`#${context}_preview${i}`);
            if (img.attr('src') === "images/noimageplaceholder.jpg") {
                return img;  // return the first empty img element found
            }
        }
        return null; // return null if all are filled
    }

    storeSurveyData(key, value) {
        if (!this.currentHomeId) return Promise.resolve();

        // Return the promise chain
        return this.db.surveys.get(this.currentHomeId).then(survey => {
            if (!survey) {
                survey = {
                    homeid: this.currentHomeId,
                    data: {}
                };
            }
            survey.data[key] = value;

            return this.db.surveys.put(survey);
        }).then(() => {
            console.log(`Data saved successfully for key: ${key} and value ${value}`);
        }).catch(error => {
            console.error(`Error saving data for key: ${key}`, error);
        });
    }

    startSurvey(homeid, appointmentIdent) {
        if (!homeid) {
            console.error('Home ID is not provided.');
            return;
        }
        $('#infoModal').modal('hide');
        this.currentHomeId = homeid;

        this.db.surveys.get(homeid).then(storedSurvey => {
            if (storedSurvey) {
                console.log('survey found for ' + homeid, storedSurvey);
                this.loadSurveyData(storedSurvey, appointmentIdent);  // if a survey is found, check with the user if they want to continue or start a new one
            } else {
                console.log('no survey found for ' + homeid + ' starting new survey', appointmentIdent);
                this.initNewSurvey(homeid, false, appointmentIdent);
                $('#surveyPage').show();
            }
        }).catch(error => {
            console.error("Error accessing local database:", error);
        });
    }

    loadSurveyData(storedSurvey, appointmentIdent) {
        let self = this; // Capture the class instance into 'self'

        $.confirm({
            title: '',
            content: 'Zwischengespeicherte Begehung laden?',
            theme: 'supervan',
            closeIcon: true,
            buttons: {
                'Daten laden': function () {
                    self.loadSurvey(self.currentHomeId, appointmentIdent); // Use 'self' here
                },
                'Neustarten': function () {
                    let counter = 2;
                    $.confirm({
                        title: '<i class="bi bi-exclamation-diamond" style="color:#e74c3c;"></i> Achtung <i class="bi bi-exclamation-diamond" style="color:#e74c3c;"></i>',
                        content: 'Alle gemachten Änderungen werden verworfen!',
                        theme: 'supervan',
                        type: 'red',
                        buttons: {
                            'delete': {
                                text: `Daten löschen (${counter})`,
                                btnClass: 'btn-red',
                                action: function () {
                                    self.initNewSurvey(self.currentHomeId, true, appointmentIdent);
                                }
                            },
                            'cancel': {
                                text: `Zurück`,
                                action: function () {
                                    self.loadSurveyData(storedSurvey, appointmentIdent); // Return to the first level confirm
                                }
                            }
                        },
                        onOpenBefore: function () {
                            // This will be executed before the modal is displayed
                            this.buttons.delete.disable();
                        },
                        onContentReady: function () {
                            let deleteButton = this.buttons.delete;

                            let interval = setInterval(() => {
                                counter--;
                                if (counter <= 0) {
                                    clearInterval(interval);
                                    deleteButton.setText('Daten löschen');
                                    deleteButton.enable();
                                } else {
                                    deleteButton.setText(`Daten löschen (${counter})`);
                                }
                            }, 1000);
                        }
                    });
                }
            }
        });
    }

    loadSurvey(homeid, appointmentIdent) {
        console.log('function -> loadSurvey', homeid);
        this.db.surveys.get(homeid).then(survey => {
            if (survey) {
                this.resetSurveyPage();
                // Load all original images
                this.db.images.where('homeid').equals(homeid).each(imageRecord => {
                    const imgId = imageRecord.imgId;
                    const img = $("#" + imgId);
                    if (img) {
                        img.attr('src', imageRecord.data);
                    }
                }).then(() => {
                    // Overwrite with modified images if they exist
                    return this.db.images_modified.where('homeid').equals(homeid).each(modifiedImageRecord => {
                        const imgId = modifiedImageRecord.imgId;
                        const img = $("#" + imgId);
                        if (img) {
                            img.attr('src', modifiedImageRecord.data);
                        }
                    });
                }).then(() => {
                    // Load other fields from the survey
                    if (survey.data) {
                        for (let key in survey.data) {
                            const elem = $(`#${key}`);
                            //console.log('handle key now', key)
                            if (elem.is(':checkbox')) {  // Check if the element is a checkbox
                                elem.prop('checked', survey.data[key]);  // Set checked property based on value from database
                                // Handle visual update for your switch, if needed
                                if (survey.data[key]) {
                                    elem.siblings('label').find('.bi-check-lg').show();
                                    elem.siblings('label').find('.bi-x-lg').hide();
                                } else {
                                    elem.siblings('label').find('.bi-check-lg').hide();
                                    elem.siblings('label').find('.bi-x-lg').show();
                                }
                            } else if (key.includes('_signature_preview')) {
                                elem.css('background-image', 'url(' + survey.data[key] + ')');
                            } if (elem.is(':radio')) {
                                if (survey.data[key] === true) {
                                    elem.prop('checked', true);
                                }
                            } else {
                                elem.val(survey.data[key]);
                            }
                        }
                        $('#surveyPage .form-check-input').each(function () {
                            handleSwitchChange($(this));
                        });
                    }
                    this.prepareSurveyPage(appointmentIdent);
                    $('#surveyPage').show();
                });

            } else {
                console.log('no survey found');
            }
        });
    }

    prepareSurveyPage(appointmentIdent) {
        const date = appointmentIdent.date;
        const index = appointmentIdent.index;

        appManager.appointmentsInstance.fetchAppointmentFromDexie(date, index)
            .then(appointment => {
                console.log('Fetched appointment:', appointment);

                const $page = $('#tab_pane_first');

                const suffix = /^\d+$/.test(appointment.streetnumberadd) ? `/${appointment.streetnumberadd}` : appointment.streetnumberadd;
                const street = `${appointment.street} ${appointment.streetnumber}${suffix}`;

                $page.find('#address').text(address);
                $page.find('#firstname').text(appointment.firstname);
                $page.find('#lastname').text(appointment.lastname);
                $page.find('#street').text(street);
                $page.find('#city').text(appointment.plz + ' ' + appointment.city);
                $page.find('#phone1').text(appointment.phone1);
                $page.find('#phone2').text(appointment.phone2);
                $page.find('#email').text(appointment.mail);
                $page.find('#homeid').text(appointment.homeid);
                $page.find('#adressid').text(appointment.adressid);
                $page.find('#dpnumber').text(appointment.dpnumber);
                $page.find('#unit').text(appointment.unit);
                $page.find('#isporder').text(appointment.isporder);

            })
            .catch(error => {
                console.error('Error fetching appointment:', error);
            });
    }

    initNewSurvey(homeid, clearExisting = false, appointmentIdent) {
        if (clearExisting) {
            Promise.all([
                this.db.surveys.delete(homeid),
                this.db.images.where('homeid').equals(homeid).delete(),
                this.db.images_modified.where('homeid').equals(homeid).delete()
            ])
                .then(() => {
                    console.log(`Deleted existing survey and related images for homeid: ${homeid}`);
                })
                .catch(error => {
                    console.error("Error deleting existing data:", error);
                });
        }
        this.prepareSurveyPage(appointmentIdent);
        this.resetSurveyPage();
        $('#surveyPage').show();
    }


    resetSurveyPage() {
        $('#surveyPage').html(this.initialSurveyState);
        //initializeTooltips();
        canvas = new fabric.Canvas('lightboxCanvas');

        // Signature pad initialization
        var signatureCanvas = document.getElementById('sign_customer');
        if (signatureCanvas) {
            console.log('init the signpad');
            var signaturePad = new SignaturePad(signatureCanvas);
            console.log('signpad initialized');
        } else {
            console.log('signpad already init');
        }

        // Bind the clear button event
        $('#clear').click(function () {
            signaturePad.clear();
        });
    }

    exitSurvey() {
        $('#surveyPage').hide(); // Hide the survey
    }

    takePicture() {
        const fileInput = document.getElementById('fileInput');
        if (fileInput) {
            // Setting capture attribute to camera to directly open the camera on supported devices
            fileInput.setAttribute('capture', 'camera');
            fileInput.click();
        }
    }

    selectPicture() {
        const fileInput = document.getElementById('fileInput');
        if (fileInput) {
            fileInput.removeAttribute('capture');
            fileInput.click();
        }
    }

    processSelectedPicture(event) {
        const file = event.target.files[0];
        if (file) {
            // For now, just alerting. Later you can process, display or upload the image.
            alert('Picture selected: ' + file.name);
        }
    }
}


class Appointments {
    constructor() {
        this.db = new Dexie('Appointments');
        this.db.version(3).stores({
            appointments: 'date, data',
            surveys: 'homeid',
            images: '[homeid+imgId]',
            images_modified: '[homeid+imgId]',
            imageLayers: '[homeid+context+layerIndex]',
            userAppointmentsData: '[date+index], data, latestUploadStatus',
            uploadQueue: '++id, date, index, uploadStatus' // 'id' is auto-incremented
        });
        this.currentAppointment = { date: null, index: null, homeid: null };
        this.appManager = appManager;
        this.surveyInstance = new Survey(this.db);

        $(document).ready(() => {
            this.loadAppointments();

            $('#close-tab').on('click', (event) => {
                this.ModalClose();
            });

            $('#mainMenu_clearCach').click(() => {
                this.confirmAndClearCache();
            });

            $('#mainMenu_sync').click(() => {
                const $button = $('#mainMenu_sync'); // Cache the button selector
                $button.prop('disabled', true); // Disable the button immediately when clicked

                this.Queue_ProcessUpload();

                // Set a timeout to re-enable the button after 60 seconds
                setTimeout(() => {
                    $button.prop('disabled', false);
                }, 60000); // 60 seconds
            });


            // 5sec after page is loaded and all is (hopefully) set up, we process the qeue
            setTimeout(() => {
                this.Queue_ProcessUpload();
            }, 5000); // 5 seconds

        });
    }

    confirmAndClearCache() {
        $.confirm({
            title: 'Cach löschen',
            content: 'Sicher das du das Cach löschen willst?',
            buttons: {
                confirm: {
                    text: 'Ja, löschen', // Text for the confirm button
                    btnClass: 'btn-red', // Class for the confirm button
                    action: () => {
                        this.db.delete().then(() => {
                            console.log("Database deleted successfully");
                            window.location.reload();
                        }).catch((error) => {
                            console.error("Failed to delete database", error);
                        });
                    }
                },
                cancel: {
                    text: 'Abbruch', // Text for the cancel button
                    // Optional: Add additional styling or classes for the cancel button
                    action: () => { /* Do nothing on cancel */ }
                }
            }
        });
    }

    loadAppointments(targetDate = this.todayDate()) {
        this.resetDisplay(targetDate); // Reset the map and sidebar
        this.updateStatusIndicator("Checking...");

        this.db.appointments.get(targetDate).then(storedData => {
            if (storedData && storedData.data) {
                this.displayAppointments(storedData.data);
            }

            if (navigator.onLine) {
                // Fetch and validate token
                this.appManager.authDb.authTokens.orderBy('id').reverse().first().then(entry => {
                    if (entry) {
                        this.appManager.validateTokenWithServer(entry.token).then(isValid => {
                            if (isValid) {
                                this.updateStatusIndicator("Updating...");
                                this.fetchAppointmentsFromServer(targetDate);
                            } else {
                                this.updateStatusIndicator("Login Required");
                                this.appManager.showLoginScreen();
                            }
                        });
                    } else {
                        this.updateStatusIndicator("Login Required");
                        this.appManager.showLoginScreen();
                    }
                }).catch(error => {
                    console.error("Error validating token:", error);
                    this.updateStatusIndicator("Error");
                    // Optionally handle the error more specifically
                });
            } else {
                this.updateStatusIndicator("Offline");
            }
        }).catch(error => {
            console.error(`Error accessing local database for ${targetDate}:`, error);
            this.updateStatusIndicator("Error");
        });
    }

    updateStatusIndicator(status) {
        let indicatorEl = document.getElementById("statusIndicator");
        switch (status) {
            case "Online":
                indicatorEl.innerText = "Status: Online";
                indicatorEl.classList.remove("bg-secondary", "bg-danger");
                indicatorEl.classList.add("bg-success");
                break;
            case "Offline":
                indicatorEl.innerText = "Status: Offline";
                indicatorEl.classList.remove("bg-success", "bg-danger");
                indicatorEl.classList.add("bg-secondary");
                break;
            case "Updating":
                indicatorEl.innerText = "Status: Updating...";
                indicatorEl.classList.remove("bg-success", "bg-secondary");
                indicatorEl.classList.add("bg-warning");
                break;
            case "Error":
                indicatorEl.innerText = "Status: Error";
                indicatorEl.classList.remove("bg-success", "bg-secondary");
                indicatorEl.classList.add("bg-danger");
                break;
            default:
                indicatorEl.innerText = `Status: ${status}`;
                break;
        }
    }

    fetchAppointmentsFromServer(targetDate = this.todayDate(), retryCount = 0) {
        const username = this.appManager.username
        console.log('this.appManager.username', username)


        console.log('getServerIp call')
        $.ajax({
            url: '/load.php',
            type: 'GET',
            headers: {
                'X-Custom-Token': customToken,
                'X-Username': username
            },
            data: { action: 'getServerIp' },
            dataType: 'json',
            success: response => {
                console.log('getServerIp responese', response)
                if (response.status === 'success') {

                } else {
                    console.error('Error from server:', response.message);

                }
            },
            error: err => {
                console.error(`Failed to fetch get ServerIp`, err);
                if (retryCount < 3) {

                } else {
                }
            }
        });



        $.ajax({
            url: '/load.php',
            type: 'GET',
            headers: {
                'X-Custom-Token': customToken,
                'X-Username': username
            },
            data: { action: 'appointments_load', date: targetDate },
            dataType: 'json',
            success: response => {
                console.log('fetchedAppointmentsFromServer', response)
                if (response.status === 'success') {
                    console.log(`Fetched data from server for ${targetDate}:`, response.data);
                    this.afterFetchingData(response.data, targetDate);
                } else {
                    console.error('Error from server:', response.message);
                    if (retryCount < 3) {
                        setTimeout(() => this.fetchAppointmentsFromServer(targetDate, retryCount + 1), 10000);
                    } else {
                        this.updateStatusIndicator("Error");
                    }
                }
            },
            error: err => {
                console.error(`Failed to fetch appointments from server for ${targetDate}:`, err);
                if (retryCount < 3) {
                    console.log('Retrying fetch in 10 seconds...');
                    setTimeout(() => this.fetchAppointmentsFromServer(targetDate, retryCount + 1), 10000);
                } else {
                    this.updateStatusIndicator("Error");
                }
            }
        });
    }


    afterFetchingData(data, targetDate) {
        this.displayAppointments(data);
        this.db.appointments.put({
            date: targetDate,
            data: data
        }).then(() => {
            console.log('Stored fresh data in local database.');
            if (navigator.onLine) {
                this.updateStatusIndicator("Online");
            } else {
                this.updateStatusIndicator("Offline");
            }
        }).catch(error => {
            console.error("Error storing data in local database:", error);
            this.updateStatusIndicator("Error"); // <-- Handle local storage error
        });
    }

    resetDisplay(selectedDate) {
        if (!selectedDate) {
            selectedDate = new Date();
        }
        console.log('selectedDate', selectedDate)

        let displayDate = convertDateFormat(selectedDate);
        $('#appointmentsContent').html(`<div class="noappointment">Keine Termine für den ${displayDate}</div>`);

        // Clear the Map
        mapMarkers.forEach(marker => {
            map.removeLayer(marker);
        });
        mapMarkers = [];

        // Reset the Map View to the center of Germany
        map.setView([48.999350820270635, 8.476834222447211], 30);

        if (!scan4Marker) {
            scan4Marker = L.marker([48.999350820270635, 8.476834222447211], { icon: scan4Icon }).addTo(map);
            scan4Marker.bindPopup(`
                <strong>Scan4 GmbH</strong><br>
                Karl-Weysser-Straße 17<br>
                76227 Karlsruhe<br>
                Deutschland<br><br>
                
                Tel.: +49 721 981 915 470<br>
                Email: info[at]scan4-gmbh.de<br>
                <a href="http://www.scan4-gmbh.de" target="_blank">www.scan4-gmbh.de</a>
            `).openPopup();

        }
    }



    displayAppointments(dataArray) {
        // this.resetDisplay(selectedDate); // reset the map and sidebar
        console.log('dataArray', dataArray)
        if (dataArray.length === 0) {
            return;
        }
        let htmlContent = '';
        dataArray.forEach((appointment, index) => {
            // Check if the streetnumberadd is numeric
            const suffix = /^\d+$/.test(appointment.streetnumberadd)
                ? `/${appointment.streetnumberadd}`
                : appointment.streetnumberadd;

            const currentdate = this.todayDate();
            console.log(appointment, index, appointment.homeid);

            const carrierLogos = {
                'DGF': 'logo_carrier_dgf.png',
                'GlasfaserPlus': 'logo_carrier_gfp.png',
                'GVG': 'logo_carrier_gvg.png',
                'UGG': 'logo_carrier_ugg.png'
            };

            const appLogo = carrierLogos[appointment.carrier] || '';

            const appointmentStatuses = {
                'done': {
                    class: 'appt_status done',
                    icon: '<i class="bi bi-check"></i>'
                },
                'open': {
                    class: 'appt_status open',
                    icon: ''
                },
                'canceled': {
                    class: 'appt_status canceled',
                    icon: '<i class="bi bi-x"></i>'
                }
            };

            let apptStatus;
            let apptStatusInfo;
            if (appointment.appt_status === 'done') {
                apptStatusInfo = appointmentStatuses['done'];
                apptStatus = 'done';
            } else if (appointment.appt_status === '' || appointment.appt_status === null) {
                apptStatusInfo = appointmentStatuses['open'];
                apptStatus = 'open';
            } else {
                apptStatusInfo = appointmentStatuses['canceled'];
                apptStatus = 'canceled';
            }

            let classToAdd = '';
            let iconHTML = '';
            switch (apptStatus) {
                case 'complete - done':
                case 'done':
                    classToAdd = 'appt_status done';
                    iconHTML = '<i class="bi bi-check"></i>';
                    break;
                case 'complete - canceled':
                case 'canceled':
                    classToAdd = 'appt_status canceled';
                    iconHTML = '<i class="bi bi-x"></i>';
                    break;
                default:
                    classToAdd = 'appt_status open'; // Default case for 'open'
                    iconHTML = '';
                    break;
            }

            let activationIcon;
            switch (appointment.activated) {
                case 0:
                    activationIcon = '<i style="-webkit-text-stroke: 0.5px;" class="bi bi-bookmark-dash"></i>';
                    break;
                case 1:
                    activationIcon = '<i style="color:#2fa500;-webkit-text-stroke: 0.5px;" class="bi bi-bookmark-check"></i>';
                    break;
                case 2:
                    activationIcon = '<i style="color:#d50000;-webkit-text-stroke: 0.5px;" class="bi bi-bookmark-x"></i>';
                    break;
                default:
                    activationIcon = ''; // or any default icon
                    break;
            }

            const appointmentAdress = `${appointment.street}+${appointment.streetnumber}${suffix},+${appointment.plz}+${appointment.city}`;
            const mapLink = isiOS
                ? `http://maps.apple.com/?q=${appointmentAdress}`
                : `http://maps.google.com/?q=${appointmentAdress}`;

            htmlContent += `
                <div class='row d-flex justify-content-between appointment ms-1 me-1 mb-2 ${classToAdd}' 
                data-homeid="${appointment.homeid}" data-date="${appointment.date}" data-time="${appointment.time}" 
                data-index="${index}" data-apptstatus="${apptStatus}" 
                data-status="${apptStatus}">
                <!-- Textblock with Icon -->
                <div class='col-7 d-flex align-items-center appointmentinfos ps-0'>
                    <div class="icon-container">${activationIcon}</div>
                    <div>
                        <strong>${appointment.lastname}</strong><br/>
                        <div class="">
                            <a class="appointmentaddress" style="color: #181818; text-decoration: unset;" href="${mapLink}" target="_blank">
                                ${appointment.street} ${appointment.streetnumber}${suffix}, ${appointment.plz} ${appointment.city}
                            </a>
                        </div>
                    </div>
                </div>       
                <!-- Indicator -->
                <div class='col-1 d-flex justify-content-center align-items-center'>
                    <span style="display:none;"><i class="bi bi-exclamation-octagon-fill"></i></span>
                </div>
                <div class='col-3 d-flex justify-content-center align-items-center flex-column appointmentsubinfo'>
                    <p><b>${appointment.time}</b></p>
                    <img src="/images/${appLogo}" class="img-fluid">
                </div>
                <div class="appointment_indicator ${classToAdd}">${iconHTML}</div>
                <div class="progress" style="height:10px;display:none;">
                    <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                </div>
            </div>
            `;


            if (map && appointment.lat && appointment.lon) {
                let marker = L.circleMarker([appointment.lat, appointment.lon], {
                    color: color_open,
                    fillOpacity: 0.5
                }).addTo(map);
                let popupContent = `
                    <strong>${appointment.firstname} ${appointment.lastname}</strong><br/>
                    ${appointment.street} ${appointment.streetnumber}${suffix}, ${appointment.city}<br/>
                    <strong>Termin:</strong> ${appointment.time}
                    `;
                // Bind the popup to the marker
                marker.bindPopup(popupContent);
                mapMarkers.push(marker); // Add marker to the array
            }

        });

        // Set map view to the bounds of all markers
        if (mapMarkers.length > 0) {
            let group = L.featureGroup(mapMarkers);
            map.fitBounds(group.getBounds());
        }


        $(document).off('click', '.appointmentsubinfo').on('click', '.appointmentsubinfo', (event) => {
            $('.appointment').removeClass('selected');
            $(event.currentTarget).closest('.appointment').addClass('selected');

            const date = $(event.currentTarget).closest('.appointment').data('date');
            const index = $(event.currentTarget).closest('.appointment').data('index');
            const homeid = $(event.currentTarget).closest('.appointment').data('index');
            this.currentAppointment.date = date;
            this.currentAppointment.index = index;
            this.currentAppointment.homeid = homeid;

            console.log('clicked appointment date: ' + date, 'clicked index: ' + index)

            this.ModalPrepare();

            this.fetchAppointmentFromDexie(date, index).then(appointment => {
                console.log('loaded appointment', appointment);
                this.currentAppointment.appointment = appointment;
                const homeid = appointment.homeid;
                this.surveyInstance.currentHomeId = homeid;
                this.surveyInstance.currentAppointment = appointment;
                this.currentHomeId = homeid;

                const $modal = $('#infoModal');
                const suffix = /^\d+$/.test(appointment.streetnumberadd) ? `/${appointment.streetnumberadd}` : appointment.streetnumberadd;
                const address = `${appointment.street} ${appointment.streetnumber}${suffix}, ${appointment.city}`;
                const visit = 'Am ' + convertDateFormat(appointment.date) + ' um ' + appointment.time;
                const phone1 = appointment.phone1;
                const phone2 = appointment.phone2;
                const phoneLink1 = phone1 && phone1.length >= 5 ? `<a href="tel:${phone1.replace(/\s+/g, '')}">${phone1}</a>` : '';
                const phoneLink2 = phone2 && phone2.length >= 5 ? `<a href="tel:${phone2.replace(/\s+/g, '')}">${phone2}</a>` : '';
                const phoneLinks = phoneLink1 && phoneLink2 ? `${phoneLink1} ${phoneLink2}` : phoneLink1 + phoneLink2;
                const formattedCreationDate = 'Am ' + convertDateFormat(appointment.created.split(' ')[0]) + ' um ' + appointment.created.split(' ')[1];
                const formattedComment = "<p><b>Kommentar für den Hausbegeher:</b></p>" + appointment.comment;
                const activation = appointment.activated;

                $modal.find('#appthomeid').text(appointment.homeid);
                $modal.find('#apptTechnicanNote').html(formattedComment);
                if (appointment.comment.length > 2) {
                    $modal.find('#apptTechnicanNoteWrapper').show();
                } else {
                    $modal.find('#apptTechnicanNoteWrapper').hide();
                }

                $modal.find('#appointment').text(visit);
                $modal.find('#name').text(appointment.lastname + ' ' + appointment.firstname);
                $modal.find('#address').text(address);
                $modal.find('#aptphone').html(phoneLinks);
                $modal.find('#aptdate').text(formattedCreationDate);
                $modal.find('#aptuser').text(appointment.username);

                $modal.find('#startSurvey').data('date', date).data('index', index);
                $modal.find('#cancelSurvey').data('date', date).data('index', index);

                // Format the date as 'Y-m-d'
                const currentDate = new Date();
                const today = currentDate.getFullYear() + '-' +
                    String(currentDate.getMonth() + 1).padStart(2, '0') + '-' +
                    String(currentDate.getDate()).padStart(2, '0');
                if (date > today) {
                    $('#tabNav_hbgstatus').hide();
                    $('#tabNav_apperror').hide();
                } else {
                    $('#tabNav_hbgstatus').show();
                    $('#tabNav_apperror').show();
                }

                if (activation == '3') { // show hbg survey tab if activation is not succefull eg 2
                    $('#tabNav_hbgsurvey').show();
                } else {
                    $('#tabNav_hbgsurvey').hide();
                }

                $('#infoModal').modal('show');
                setTimeout(function () {
                    var homeTab = document.getElementById('home-tab');
                    if (homeTab) {
                        homeTab.click();
                    } else {
                        console.log('Home tab not found');
                    }
                }, 100);

            }).catch(error => {
                console.error('Error handling the fetched appointment:', error);
            });
        });

        // Triggered when .appointmentinfos is clicked
        $(document).off('click', '.appointmentinfos').on('click', '.appointmentinfos', (event) => {
            $('.appointment').removeClass('selected');
            $(event.currentTarget).closest('.appointment').addClass('selected');

            // Fetch the appointment details as you did in the previous code
            const date = $(event.currentTarget).closest('.appointment').data('date');
            const index = $(event.currentTarget).closest('.appointment').data('index');
            this.currentAppointment.date = date;
            this.currentAppointment.index = index;

            this.fetchAppointmentFromDexie(date, index).then(appointment => {
                map.flyTo([appointment.lat, appointment.lon], 16, { duration: 0.5 });
                // Search the marker in mapMarkers array based on lat and lon
                const isCloseEnough = (a, b, threshold = 0.00001) => Math.abs(a - b) < threshold;

                const targetMarker = mapMarkers.find(marker => {
                    const markerLatLng = marker.getLatLng();
                    return isCloseEnough(markerLatLng.lat, appointment.lat) && isCloseEnough(markerLatLng.lng, appointment.lon);
                });

                // If the marker is found, open its popup
                if (targetMarker) {
                    targetMarker.openPopup();
                }

            }).catch(error => {
                console.error('Error handling the fetched appointment:', error);
            });
        });



        $('#appointmentsContent').html(htmlContent);
        this.updateAppointmentStyling();

        // add timegaps
        const $appointments = $('.appointment'); // Assuming '.appointment' is your appointment class
        $appointments.each(function (index) {
            if (index < $appointments.length - 1) {
                const currentTime = $(this).data('time');
                const nextTime = $($appointments[index + 1]).data('time');

                const currentDateTime = new Date(`1970/01/01 ${currentTime}`);
                const nextDateTime = new Date(`1970/01/01 ${nextTime}`);

                let diff = (nextDateTime - currentDateTime) / 60000; // Difference in minutes

                while (diff > 30) { // For each 30-minute interval
                    diff -= 30;
                    const gapElement = $('<div class="time-gap row d-flex justify-content-center ms-1 me-1 mb-2">').text('- Lücke -');
                    $(this).after(gapElement); // Insert the gap element after the current appointment
                }
            }
        });
    }

    ModalPrepare() {
        // Reset the modal
        $('#infoModal').find('input[type="text"], textarea').val('');
        $('#fileDropArea_done').val(''); // clear file attachements
        $('#pic_kdabbruch').val('');
        $('#pdf-viewer').hide();

        // Reset the dropdown selections to their initial state
        $('#cancelReasons .btn-group .dropdown-toggle').each(function () {
            var initialText = $(this).data('initial-text');
            $(this).text(initialText)
                .removeData('selected-value')
                .removeClass('btn-danger2')
                .addClass('btn-outline-danger2');
        });
        $('.alertArea').hide(); // hide all alert areas

        // Fetch and populate user data from Dexie
        const { date, index, homeid } = this.currentAppointment;
        let isSurveyFound = false; // Initially setting it to false

        if (homeid != null) {
            this.db.surveys.get({ homeid: homeid }).then(surveyData => {
                isSurveyFound = !!surveyData; // Set to true if surveyData exists
                if (isSurveyFound) {
                    $('#surveryIsStarted').text('Die Hausbegehung wurde gestartet')
                }
            }).catch(error => {
                console.error('Error checking surveys table in Dexie:', error);
            });
        }

        if (date && index != null) {
            this.db.userAppointmentsData.get({ date, index }).then(userData => {
                if (userData && userData.data) {
                    for (const [elementId, value] of Object.entries(userData.data)) {
                        const element = $('#' + elementId);


                        if (element.is('input[type="text"], textarea')) {
                            element.val(value);
                        } else if (elementId === 'fileDropArea_done' && value) {
                            $('#pdf-object').attr('data', value);
                            $('#pdf-viewer').show();
                        } else if (element.is('.dropdown-toggle') && value) {
                            const selectedItemText = element.closest('.btn-group').find(`.dropdown-item[data-value='${value}']`).text();
                            element.text(selectedItemText)
                                .data('selected-value', value)
                                .removeClass('btn-outline-danger2')
                                .addClass('btn-danger2');
                        }
                        if (elementId === 'selectedTab') {
                            if (value === 'hbgdone') {
                                // Reflect that 'HBG Erledigt' was the last selected tab
                                $('#hbgStatusDropdown').html('<i style="color: #1ab300;" class="bi bi-check2-square"></i> HBG Erledigt');
                            } else if (value === 'hbgcancel') {
                                // Reflect that 'HBG Abbruch' was the last selected tab
                                $('#hbgStatusDropdown').html('<i style="color: #bf6300;" class="bi bi-x-square"></i> HBG Abbruch');
                            }
                        }
                    }
                }
            }).catch(error => {
                console.error('Error fetching user data from Dexie:', error);
            });
        }
    }


    ModalClose() {
        const { date, index } = this.currentAppointment;
        console.log('Closing modal for appointment:', date, index);

        if (!date || index == null) {
            console.error('Selected appointment not found');
            return;
        }

        // Collect user data
        let userData = {
            'commentField_done': $('#commentField_done').val(),
            'commentField_cancel': $('#commentField_cancel').val().trim(),
            'selectedTab': $('#hbgStatusDropdown').data('currentTab'),
            'cancelReason': '',
            'status': 'untouched',
            'apperror_comment': null,
            'apperror_file': null,
            'pic_kdabbruch_file': null
        };
        console.log('Collected user data:', userData);

        // Find and set the cancelReason based on button selection
        $('#cancelReasons .btn-group .dropdown-toggle').each(function () {
            const buttonId = $(this).attr('id');
            const selectedValue = $(this).data('selected-value');
            userData[buttonId] = selectedValue || '';
            if (selectedValue) {
                userData.cancelReason = selectedValue; // Set the cancelReason
            }
        });

        //colletct files
        const fileInput = $('#fileDropArea_done')[0];
        const file = fileInput.files[0];
        let fileExistsInInput = file && file.type === "application/pdf";
        console.log('File exists in input:', fileExistsInInput);

        userData.apperror_comment = $('#commentField_apperror').val().trim();
        const fileInputAppError = $('#fileDropArea_apperror')[0];
        const fileAppError = fileInputAppError.files[0];
        if (fileAppError && (fileAppError.type === "application/pdf" || fileAppError.type.startsWith("image/"))) {
            const readerAppError = new FileReader();
            readerAppError.onload = (event) => {
                // Convert file content to Base64 and store in userData
                userData.apperror_file = event.target.result;
            };
            readerAppError.onerror = (event) => {
                console.error("Error reading file from apperror tab:", event);
                userData.apperror_file = null; // Handle error in file reading
            };
            readerAppError.readAsDataURL(fileAppError);
        }

        // collect abbruch beweis pic and reduce resolution
        const fileInputKdabbruch = $('#pic_kdabbruch')[0];
        const fileKdabbruch = fileInputKdabbruch.files[0];

        if (fileKdabbruch && fileKdabbruch.type.startsWith("image/")) {
            const readerKdabbruch = new FileReader();

            readerKdabbruch.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    // Set the desired width and height for the thumbnail
                    const thumbWidth = 800; // Example width, adjust as needed
                    const scaleFactor = thumbWidth / img.width;
                    const thumbHeight = img.height * scaleFactor;

                    // Create an off-screen canvas
                    const canvas = document.createElement('canvas');
                    canvas.width = thumbWidth;
                    canvas.height = thumbHeight;

                    // Draw the image on canvas
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, thumbWidth, thumbHeight);

                    // Convert the canvas to a data URL
                    userData.pic_kdabbruch_file = canvas.toDataURL('image/jpeg', 0.7); // Adjust quality as needed
                };

                img.onerror = () => {
                    console.error('Error loading image for resizing');
                    userData.pic_kdabbruch_file = null;
                };

                img.src = e.target.result;
            };

            readerKdabbruch.onerror = (event) => {
                console.error("Error reading file from pic_kdabbruch:", event);
                userData.pic_kdabbruch_file = null;
            };

            readerKdabbruch.readAsDataURL(fileKdabbruch);
        }






        const processClosure = () => {
            console.log('Processing closure with userData:', userData);
            // Update appointment status and other processing
            const appointmentElement = $(`[data-date='${date}'][data-index='${index}']`);
            if (userData.status === 'complete - done') {
                appointmentElement.data('status', 'done');
                appointmentElement.data('apptstatus', 'done');
            } else if (userData.status === 'complete - canceled') {
                appointmentElement.data('status', 'canceled');
                appointmentElement.data('apptstatus', 'canceled');
            }
            console.log('Updated appointment status:', userData.status);
        };

        const determineStatus = () => {
            if (userData.selectedTab === 'hbgdone') {
                if (fileExistsInInput || (userData['fileDropArea_done'] && userData['fileDropArea_done'] !== '')) {
                    return 'complete - done';
                }
            } else if (userData.selectedTab === 'hbgcancel') {
                // Check if there's at least one file in the pic_kdabbruch input
                const picKdabbruchFileExists = $('#pic_kdabbruch')[0].files.length > 0;

                // Check if comment field has adequate length
                const isCommentValid = userData['commentField_cancel'].length >= 5;
                // Retrieve the selected reason from kundenUrsacheButton
                const selectedCancelReason = $('#kundenUrsacheButton').data('value');

                // Check if the cancel reason is 'kunde_nicht_da'
                const isKundeNichtDaSelected = selectedCancelReason === 'kunde_nicht_da';

                // Check for comment validity and specific conditions for 'kunde_nicht_da'
                if (isCommentValid && (!isKundeNichtDaSelected || (isKundeNichtDaSelected && picKdabbruchFileExists))) {
                    return 'complete - canceled';
                }
            }
            return 'incomplete';
        };

        const finalize = () => {
            userData.status = determineStatus();
            console.log('Finalizing - saving data to Dexie with status:', userData.status);
            console.log('Finalizing - collected data:', userData);

            const saveThisUserdataNow = userData;

            this.db.userAppointmentsData.put({ date, index, data: saveThisUserdataNow })
                .then(() => {
                    console.log('Data saved to Dexie successfully', saveThisUserdataNow);

                    // Retrieve the data immediately after storing
                    return this.db.userAppointmentsData.get({ date, index });
                })
                .then(retrievedData => {
                    console.log('Retrieved data from Dexie:', retrievedData);
                    // Check if status is 'complete - done' or 'complete - canceled' and add to upload queue
                    if (userData.status === 'complete - done' || userData.status === 'complete - canceled') {
                        this.updateAppointmentStyling();
                        this.Queue_addUpload(date, index, userData);
                    }
                })
                .catch(error => console.error("Error storing or retrieving data in Dexie:", error))
                .finally(() => processClosure());

            $('#infomodaltabs a#home-tab').tab('show');
        };


        const checkExistingFileInDexie = () => {
            console.log('Checking existing file in Dexie');
            this.db.userAppointmentsData.get({ date, index })
                .then(existingData => {
                    if (existingData && existingData.data && existingData.data['fileDropArea_done']) {
                        userData['fileDropArea_done'] = existingData.data['fileDropArea_done'];
                    }
                    console.log('File exists in Dexie:', !!userData['fileDropArea_done']);
                    finalize();
                })
                .catch(error => console.error("Error retrieving data from Dexie:", error));
        };

        if (fileExistsInInput) {
            const reader = new FileReader();
            reader.onload = (e) => {
                userData['fileDropArea_done'] = e.target.result;
                console.log('File read from input and added to userData');
                finalize();
            };
            reader.onerror = (e) => console.error("Error reading file:", e);
            reader.readAsDataURL(file);
        } else {
            checkExistingFileInDexie();
        }
    }

    async updateAppointmentStyling() {
        // console.log('@@ STYLING @@')
        const self = this;
        const currentdate = self.todayDate();
        const currentTime = new Date();

        const appointmentElements = $('.appointment');
        for (let index = 0; index < appointmentElements.length; index++) {
            const appointmentElement = $(appointmentElements[index]);
            const date = appointmentElement.data('date');
            const time = appointmentElement.data('time');
            const iconSpan = appointmentElement.find('.col-1 span');

            // Parse appointment date and time
            const appointmentDateTime = new Date(date + ' ' + time);

            try {
                let apptstatus = '';
                let fetchedApptFile = null;

                // First, fetch data from appointments table
                const appointmentData = await self.db.appointments.get({ date });
                if (appointmentData && appointmentData.data && appointmentData.data.length > index) {
                    const fetchedApptStatus = appointmentData.data[index].appt_status;
                    fetchedApptFile = appointmentData.data[index].appt_file;
                    if (fetchedApptStatus) {
                        apptstatus = fetchedApptStatus;
                        //console.log('apptstatus' + apptstatus)
                    }
                }

                // Then, override with user-specific status if more significant ('done' or 'canceled')
                const userData = await self.db.userAppointmentsData.get({ date, index });
                const userApptStatus = userData?.data?.status; // Use optional chaining
                //console.log('userApptStatus', userApptStatus)
                if (userApptStatus === 'done' || userApptStatus === 'complete - done' || userApptStatus === 'canceled' || userApptStatus === 'complete - canceled') {
                    //console.log('overwriting apptstatus:' + apptstatus + ' with userApptstatus:' + userApptStatus)
                    apptstatus = userApptStatus;
                }

                const latestUploadStatus = userData?.latestUploadStatus;

                // Remove existing classes
                appointmentElement.removeClass('isFuture isPast appt_status done open canceled');

                // Add classes based on date
                if (date > currentdate) {
                    appointmentElement.addClass('isFuture');
                } else if (date < currentdate) {
                    appointmentElement.addClass('isPast');
                } else if (appointmentDateTime < currentTime) {
                    // Check if the appointment is at least 30 minutes in the past
                    const thirtyMinutesAgo = new Date(currentTime.getTime() - (30 * 60 * 1000));
                    if (appointmentDateTime < thirtyMinutesAgo) {
                        appointmentElement.addClass('isPast');
                    }
                }

                // Clean and update apptstatus
                apptstatus = (apptstatus === 'done' || apptstatus === 'complete - done') ? 'done' :
                    (apptstatus === null || apptstatus === '') ? 'open' : 'canceled';

                // Update classes based on appointment status
                let classToAdd = '';
                let iconHTML = '';
                //console.log('swtich appstatus ' + apptstatus)
                switch (apptstatus) {
                    case 'complete - done':
                    case 'done':
                        classToAdd = 'appt_status done';
                        iconHTML = '<i class="bi bi-check"></i>';
                        break;
                    case 'complete - canceled':
                    case 'canceled':
                        classToAdd = 'appt_status canceled';
                        iconHTML = '<i class="bi bi-x"></i>';
                        break;
                    default:
                        classToAdd = 'appt_status open'; // Default case for 'open'
                        iconHTML = '';
                        break;
                }
                appointmentElement.addClass(classToAdd);
                appointmentElement.find('.appointment_indicator').html(iconHTML).addClass(classToAdd);

                let iconContent = null;
                // First, check if fetchedApptFile is not null
                if (typeof fetchedApptFile !== 'undefined' && fetchedApptFile !== null) {
                    iconContent = '<img src="/images/cloud_check.png" style="width:25px;" alt="Upload Successful">';
                    iconSpan.html(iconContent).show();
                } else {
                    // Update icon based on upload status
                    switch (latestUploadStatus) {
                        case 'queued':
                        case 'uploading':
                            iconContent = '<img src="/images/cloud_loading.gif" style="width:35px;" alt="Uploading">';
                            break;
                        case 'done':
                        case 'canceled':
                        case 'success':
                            iconContent = '<img src="/images/cloud_check.png" style="width:25px;" alt="Upload Successful">';
                            break;
                        case 'failure':
                            iconContent = '<img src="/images/cloud_error.png" style="width:25px;" alt="Upload Failed">';
                            break;
                        default:
                            iconSpan.hide();
                            continue; // Skip further processing for this element
                    }

                    // Update the iconSpan only if fetchedApptFile is null
                    iconSpan.html(iconContent).show();
                }

            } catch (error) {
                console.error('Error fetching data from userAppointmentsData:', error);
                // Handle error or show a fallback status if needed
            }
        }
    }

    Queue_addUpload(date, index, newUserData) {
        // Fetch the existing upload queue item, if any
        console.log('Queue data received', date, index, newUserData)
        this.db.uploadQueue.get({ date, index })
            .then(existingQueueItem => {
                let queuePromise;
                if (existingQueueItem) {
                    // Compare new user data with existing data
                    const isDifferent = JSON.stringify(newUserData) !== JSON.stringify(existingQueueItem.userData);
                    if (isDifferent) {
                        // Update the existing queue item with new data and status
                        queuePromise = this.db.uploadQueue.put({
                            ...existingQueueItem,
                            userData: newUserData,
                            uploadStatus: 'queued'
                        });
                    } else {
                        console.log('No changes detected, not re-adding to upload queue');
                        return Promise.resolve();
                    }
                } else {
                    // Add new item to the queue if it doesn't exist
                    queuePromise = this.db.uploadQueue.add({ date, index, userData: newUserData, uploadStatus: 'queued' });
                }

                return queuePromise.then(() => {
                    // Update userAppointmentsData latestUploadStatus
                    return this.db.userAppointmentsData.update({ date, index }, { latestUploadStatus: 'queued' });
                });
            })
            .then(() => {
                console.log('Handled upload queue');
                // Update the DOM element
                $('.appointment').each(function () {
                    const elemDate = $(this).data('date');
                    const elemIndex = $(this).data('index');
                    if (date === elemDate && index === elemIndex) {
                        $(this).data('status', 'queued');
                        console.log('data attribute set to queued');
                    }
                });
                this.updateAppointmentStyling(); // Update the icons
                this.Queue_ProcessUpload(); // Process the queue
            })
            .catch(error => {
                console.error('Error handling the upload queue:', error);
            });
    }

    Queue_processUploadAutoload() {
        setInterval(() => {
            console.log('Autoload Processing upload queue... ');
            this.Queue_ProcessUpload();
        }, 60000); // 60000 milliseconds = 1 minute
    }

    Queue_ProcessUpload() {
        console.log('Processing upload queue...');
        this.db.uploadQueue.toArray().then(allQueueItems => {
            // Remove items with 'success' status from the queue
            const idsToRemove = allQueueItems.filter(item => item.uploadStatus === 'success').map(item => item.id);
            if (idsToRemove.length > 0) {
                this.db.uploadQueue.bulkDelete(idsToRemove).then(() => {
                    console.log(`${idsToRemove.length} 'success' items removed from the queue`);
                }).catch(error => {
                    console.error('Error removing success items from the queue:', error);
                });
            }

            // Update status to 'failed' for items that are 'uploading' for more than 5 minutes
            allQueueItems.forEach(item => {
                if (item.uploadStatus === 'uploading') {
                    let uploadTimestamp = new Date(item.timestamp);
                    let minutesDiff = (new Date() - uploadTimestamp) / 60000;
                    if (minutesDiff > 5) {
                        console.log(`Item with date: ${item.date}, index: ${item.index} was 'uploading' for more than 5 minutes. Setting status to 'failed'.`);
                        this.db.uploadQueue.update(item.id, { uploadStatus: 'failed' });
                        this.db.userAppointmentsData.update({ date: item.date, index: item.index }, { latestUploadStatus: 'failed' });
                        this.updateAppointmentStatusAttribute(item.date, item.index, 'failed');
                    }
                }
            });

            // Filter out items with 'success' status or 'uploading' without the timeout issue
            let filteredQueueItems = allQueueItems.filter(item => item.uploadStatus !== 'success' && !(item.uploadStatus === 'uploading' && (new Date() - new Date(item.timestamp)) / 60000 <= 5));

            // Sort items by date, index, and then status to prioritize queued over failed
            filteredQueueItems.sort((a, b) => {
                if (a.date === b.date) {
                    if (a.index === b.index) {
                        return a.uploadStatus.localeCompare(b.uploadStatus);
                    }
                    return a.index - b.index;
                }
                return a.date.localeCompare(b.date);
            });

            // Remove duplicates, keeping only the latest item for each date and index
            const uniqueItemsMap = new Map();
            filteredQueueItems.forEach(item => {
                const key = `${item.date}_${item.index}`;
                uniqueItemsMap.set(key, item);
            });
            const uniqueItems = Array.from(uniqueItemsMap.values());

            console.log(`Found ${uniqueItems.length} unique items in the queue to process.`);

            // Process one item at a time, starting from the last entry
            const processItem = (index) => {
                if (index < 0) return; // Stop when all items have been processed

                let item = uniqueItems[index];
                console.log(`Processing item with date: ${item.date}, index: ${item.index}`);
                this.db.userAppointmentsData.get({ date: item.date, index: item.index })
                    .then(userData => {
                        console.log(`Fetched userData for date: ${item.date}, index: ${item.index}:`, userData);

                        this.db.appointments.get({ date: item.date }).then(appointmentData => {
                            if (appointmentData && appointmentData.data && appointmentData.data.length > item.index) {
                                const appointmentItem = appointmentData.data[item.index];
                                userData.id = appointmentItem.id;
                                userData.homeid = appointmentItem.homeid;
                                userData.city = appointmentItem.city;

                                let dataToSend;
                                if (userData.data.status === 'complete - done') {
                                    dataToSend = {
                                        pdf: userData.data['fileDropArea_done'],
                                        comment: userData.data['commentField_done'],
                                        id: userData.id,
                                        homeid: userData.homeid,
                                        reason: 'done',
                                        city: userData.city
                                    };
                                } else if (userData.data.status === 'complete - canceled') {
                                    dataToSend = {
                                        reason: userData.data['cancelReason'],
                                        comment: userData.data['commentField_cancel'],
                                        image: userData.data['pic_kdabbruch_file'],
                                        id: userData.id,
                                        homeid: userData.homeid,
                                        city: userData.city
                                    };
                                } else if (userData.data.status === 'complete - excel') {
                                    dataToSend = {
                                        pdf: userData.data.survey.surveyPDF,
                                        comment: null,
                                        id: userData.id,
                                        homeid: userData.homeid,
                                        reason: 'excel',
                                        city: userData.city
                                    };
                                } else {
                                    // console.log('userdataStatus no match');
                                }
                                console.log(`Prepared data for upload:`, dataToSend);

                                this.db.uploadQueue.update(item.id, {
                                    uploadStatus: 'uploading',
                                    timestamp: new Date().toISOString()
                                }).then(() => {
                                    console.log(`Uploading data for item with date: ${item.date}, index: ${item.index}`);
                                    this.updateAppointmentStatusAttribute(item.date, item.index, 'uploading');
                                    this.uploadToServer(dataToSend, item.date, item.index).then(response => {
                                        if (response.success) {
                                            this.db.uploadQueue.update(item.id, { uploadStatus: 'success' });
                                            this.db.userAppointmentsData.update({ date: item.date, index: item.index }, { latestUploadStatus: 'success' });
                                            console.log(`Upload successful for item with date: ${item.date}, index: ${item.index}`);
                                            this.updateAppointmentStatusAttribute(item.date, item.index, 'done');
                                        } else {
                                            this.db.uploadQueue.update(item.id, { uploadStatus: 'failed' });
                                            this.db.userAppointmentsData.update({ date: item.date, index: item.index }, { latestUploadStatus: 'failed' });
                                            console.error(`Upload failed for item with date: ${item.date}, index: ${item.index}`);
                                            this.updateAppointmentStatusAttribute(item.date, item.index, 'failed');
                                        }
                                    }).catch(error => {
                                        console.error('Error uploading data:', error);
                                        this.db.uploadQueue.update(item.id, { uploadStatus: 'failed' });
                                        this.db.userAppointmentsData.update({ date: item.date, index: item.index }, { latestUploadStatus: 'failed' });
                                        this.updateAppointmentStatusAttribute(item.date, item.index, 'failed');
                                    });
                                });
                            } else {
                                console.error('Appointment item not found for the given date and index:', item.date, item.index);
                            }
                        }).catch(error => {
                            console.error('Error fetching appointment data:', error);
                        });
                    }).catch(error => {
                        console.error('Error fetching data for upload:', error);
                        this.db.uploadQueue.update(item.id, { uploadStatus: 'failed' });
                        this.db.userAppointmentsData.update({ date: item.date, index: item.index }, { latestUploadStatus: 'failed' });
                        this.updateAppointmentStatusAttribute(item.date, item.index, 'failed');

                        processItem(index - 1);
                    });
            };

            // Start processing from the last item
            processItem(uniqueItems.length - 1);
        }).catch(error => {
            console.error('Error processing upload queue:', error);
        });
    }

    uploadToServer(dataToSend, date, index) {
        console.log('uploadToServer called with dataToSend', dataToSend);
        console.log('Date:', date, 'Index:', index);

        // Create FormData object
        const formData = new FormData();

        // Append non-file data
        formData.append('comment', dataToSend.comment);
        formData.append('homeid', dataToSend.homeid);
        formData.append('city', dataToSend.city);
        formData.append('reason', dataToSend.reason);
        formData.append('id', dataToSend.id);
        formData.append('action', 'appointmentData');
        formData.append('image', dataToSend.image); // send the cancel beweis image


        // Check if PDF data exists and convert from Base64 to a Blob or not
        if (dataToSend.pdf) {
            let pdfBlob;

            // Check if dataToSend.pdf is a base64 string
            if (typeof dataToSend.pdf === 'string') {
                console.log('PDF to send found as string. Convert it to blob')
                // Convert from Base64 to a Blob
                pdfBlob = base64ToBlob(dataToSend.pdf, 'application/pdf');
            } else if (dataToSend.pdf instanceof Blob) {
                console.log('PDF to send found as blob. No convertion needed')
                // Use the Blob as it is
                pdfBlob = dataToSend.pdf;
            }

            // Append the PDF blob to FormData, using the filename if available
            formData.append('file', pdfBlob, dataToSend.filename || 'upload.pdf');
        }
        console.log('prepared dataToSend:', dataToSend);

        const selector = `.appointment[data-date="${date}"][data-index="${index}"]`;
        const element = $(selector);
        const progressBar = element.find('.progress-bar');

        console.log('Selector:', selector);

        console.log('Element found: ', element.length > 0);
        console.log('Progress bar element: ', progressBar.length > 0);

        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', serverEndpoint, true);
            xhr.setRequestHeader('X-Custom-Token', customToken);

            xhr.timeout = 60000; // Set the timeout in milliseconds

            // Show and update the progress bar
            xhr.upload.onprogress = function (event) {
                //console.log('Upload progress event: ', event);
                if (event.lengthComputable) {
                    const percentComplete = (event.loaded / event.total) * 100;
                    progressBar.css('width', percentComplete + '%').attr('aria-valuenow', percentComplete).text(Math.round(percentComplete) + '%');
                    element.find('.progress').show(); // Show progress bar
                }
            };

            // Define what happens on successful data submission
            xhr.onload = function () {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        console.log('xhr.responseText', response)
                        resolve({ success: response.success || false });
                    } catch (error) {
                        console.error('Error parsing server response:', error);
                        reject({ success: false });
                    }
                } else {
                    console.error(`HTTP error! Status: ${xhr.status}`, 'Response:', xhr.responseText);
                    reject({ success: false, message: xhr.responseText });
                }
                element.find('.progress').hide(); // Hide progress bar after completion
            };

            xhr.onerror = function () {
                console.error('Error uploading data:', xhr.statusText, 'Response:', xhr.responseText);
                reject({ success: false, message: xhr.responseText });
                element.find('.progress').hide();
            };

            xhr.ontimeout = function () {
                console.error('Request timed out');
                reject({ success: false });
                element.find('.progress').hide(); // Hide progress bar on timeout
            };

            xhr.send(formData);
        });
    }

    uploadToServer_precall(dataToSend, serverEndpoint, customToken) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', serverEndpoint, true);
            xhr.setRequestHeader('X-Custom-Token', customToken);

            const formData = new FormData();
            // Append only non-file data
            formData.append('comment', dataToSend.comment);
            formData.append('homeid', dataToSend.homeid);
            formData.append('city', dataToSend.city);
            formData.append('reason', dataToSend.reason);
            formData.append('id', dataToSend.id);

            xhr.onload = function () {
                if (xhr.status === 200) {
                    resolve();
                } else {
                    console.error(`Pre-call HTTP error! Status: ${xhr.status}`);
                    reject();
                }
            };

            xhr.onerror = function () {
                console.error('Error in pre-call');
                reject();
            };

            xhr.send(formData);
        });
    }

    updateAppointmentStatusAttribute(date, index, status) {
        console.log('try to update attribute status after server response for ' + date + ' & ' + index + ' & ' + status)
        const self = this;
        $('.appointment').each(function () {
            const elDate = $(this).data('date');
            const elIndex = $(this).data('index');
            if (elDate === date && elIndex === index) {
                $(this).data('status', status);
                console.log(`Appointment data-status updated to '${status}' for date: ${date}, index: ${index}`);
                self.updateAppointmentStyling();
                return false; // Break the loop once the correct element is found
            }
        });
    }

    storeDataInDexie(date, index, userData) {
        return this.db.userAppointmentsData.put({
            date: date,
            index: index,
            data: userData,
            status: userData.status // Store the status
        }).then(() => {
            console.log('User appointment data stored successfully in Dexie');
        }).catch((error) => {
            console.error('Error storing user appointment data in Dexie:', error);
        });
    }

    PDF_storeProtokoll(pdf, filename, homeid) {
        try {
            // Convert the PDF to a Blob synchronously
            const blob = pdf.output('blob', { type: 'application/pdf' });

            // Prepare the object to store in IndexedDB
            const pdfData = {
                surveyPDF: blob,
                filename: filename
            };
            const { date, index } = this.currentAppointment;
            let userData = {
                'commentField_done': null,
                'commentField_cancel': null,
                'selectedTab': null,
                'cancelReason': '',
                'status': 'complete - excel',
                'apperror_comment': null,
                'apperror_file': null,
                'survey': pdfData,
            };

            this.db.userAppointmentsData.put({ date, index, data: userData })
                .then(() => {
                    console.log('send pdf to queue', userData)
                    this.Queue_addUpload(date, index, userData);
                })
        } catch (error) {
            console.error('Error converting PDF to Blob:', error);
        }
    }

    fetchAppointmentFromDexie(date, index) {
        return new Promise((resolve, reject) => {
            this.db.appointments.get(date).then(dateEntry => {
                const appointment = dateEntry.data[index];
                resolve(appointment);  // Resolve the promise with the fetched appointment
            }).catch(error => {
                console.error('Error fetching appointment from Dexie:', error);
                reject(error);  // Reject the promise if there's an error
            });
        });
    }

    todayDate() {
        return new Date().toISOString().split('T')[0];
    }
}


function convertDateFormat(input) {
    if (typeof input === 'string') {
        // If the input is a string, split it assuming format 'Y-m-d'
        const [year, month, day] = input.split('-');
        return `${day}.${month}.${year}`;
    } else if (input instanceof Date) {
        // If the input is a Date object
        return `${String(input.getDate()).padStart(2, '0')}.${String(input.getMonth() + 1).padStart(2, '0')}.${input.getFullYear()}`;
    } else {
        throw new Error('Unsupported date format');
    }
}




const customCalendar = {
    monthNames: ["Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"],
    currentDate: new Date(),
    currentMonth: null,
    currentYear: null,
    onDateSelected: null,
    selectedDate: new Date(),


    init: function () {
        this.currentMonth = this.currentDate.getMonth();
        this.currentYear = this.currentDate.getFullYear();
        this.renderCalendar(this.currentMonth, this.currentYear);
        this.setupEventListeners();
        this.setCalendarPosition();
        $("#customCalendar").hide();
        $('#selectedDatedisplay').text(convertDateFormat(new Date().toISOString().split('T')[0]))
    },

    setCalendarPosition: function () {
        const menu = $('#topbarMainMenu');
        const calendar = $('#customCalendar');

        // Check if the calendar was originally visible
        const wasVisible = calendar.is(":visible");

        // Temporarily show the calendar off-screen
        calendar.css({
            'visibility': 'hidden',
            'position': 'absolute',
            'left': '-9999px'
        }).show();

        const topPos = menu.offset().top + menu.outerHeight();
        const leftPos = ($(window).width() - calendar.outerWidth()) / 2; // Center the calendar

        // Set the position
        calendar.css({
            'top': topPos + 'px',
            'left': leftPos + 'px',
            'visibility': 'visible'
        });

        // If the calendar wasn't originally visible, hide it again
        if (!wasVisible) {
            calendar.hide();
        }
    },


    renderCalendar: function (month, year) {
        const firstDay = (new Date(year, month)).getDay();
        const adjustedFirstDay = (firstDay === 0) ? 6 : firstDay - 1;
        const daysInMonth = 32 - new Date(year, month, 32).getDate();

        const today = new Date();
        const currentDay = today.getDate();
        const currentMonth = today.getMonth();
        const currentYear = today.getFullYear();

        let date = 1;
        const tbody = $("#customCalendarBody");
        tbody.empty();

        for (let i = 0; i < 6; i++) {
            let row = $("<tr></tr>");
            for (let j = 0; j < 7; j++) {
                if (i === 0 && j < adjustedFirstDay) {
                    row.append($("<td></td>").text(""));
                } else if (date > daysInMonth) {
                    break;
                } else {
                    const cell = $("<td></td>").text(date).click((function (currentDate) {
                        return function () {
                            const selectedDay = `${year}-${String(month + 1).padStart(2, '0')}-${String(currentDate).padStart(2, '0')}`;
                            console.log('selectedDay in cell', selectedDay);
                            $('#selectedDatedisplay').text(convertDateFormat(selectedDay))
                            if (customCalendar.onDateSelected) {
                                customCalendar.onDateSelected(selectedDay);
                            }
                        }
                    })(date));

                    const cellDateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
                    const dateInfo = calendarApoointmentNumbers[cellDateStr];

                    if (dateInfo) {
                        const indicator = $("<div class='cal-indicator'></div>");
                        indicator.append($("<span class='cal-total'></span>").text(dateInfo.total));
                        indicator.append($("<span class='cal-done'></span>").text(dateInfo.done));
                        indicator.append($("<span class='cal-canceled'></span>").text(dateInfo.canceled));
                        cell.append(indicator);
                    }

                    if (date === currentDay && month === currentMonth && year === currentYear) {
                        cell.addClass('current-day');
                    }

                    row.append(cell);
                    date++;
                }
            }
            tbody.append(row);
        }

        $("#customMonthYear").text(`${this.monthNames[month]} ${year}`);
        this.setCalendarPosition();
    },


    setupEventListeners: function () {
        $("#calendarToogle").click((e) => {
            e.stopPropagation();
            $("#customCalendar").toggle();
        });

        $("#customPrevMonth").click(() => {
            this.currentYear = (this.currentMonth === 0) ? this.currentYear - 1 : this.currentYear;
            this.currentMonth = (this.currentMonth === 0) ? 11 : this.currentMonth - 1;
            this.renderCalendar(this.currentMonth, this.currentYear);
        });

        $("#customNextMonth").click(() => {
            this.currentYear = (this.currentMonth === 11) ? this.currentYear + 1 : this.currentYear;
            this.currentMonth = (this.currentMonth + 1) % 12;
            this.renderCalendar(this.currentMonth, this.currentYear);
        });

        $(document).on('click', (e) => {
            // Check if the click is outside of both the calendar and the toggle button
            if (!$(e.target).closest('#customCalendar').length && !$(e.target).is("#calendarToogle")) {
                $("#customCalendar").hide();
            }
        });

        $(window).on('resize', this.setCalendarPosition.bind(this)); // Update position on window resize

    }
};



function mainpage_init() {
    $('#cancelReasons .dropdown-menu .dropdown-item').on('click', function (e) {
        e.preventDefault(); // Prevent the default anchor behavior

        // Get the value and the text of the clicked item
        var selectedItemValue = $(this).data('value');
        var selectedItemText = $(this).text();

        // Get the button related to this dropdown
        var dropdownButton = $(this).closest('.btn-group').find('.dropdown-toggle');

        // Update the button text and store the value
        dropdownButton.text(selectedItemText)
            .data('selected-value', selectedItemValue)
            .removeClass('btn-outline-danger2')
            .addClass('btn-danger2');

        // Reset the text and style of all other dropdown buttons
        $('#cancelReasons .btn-group .dropdown-toggle').not(dropdownButton).each(function () {
            var initialText = $(this).data('initial-text');
            $(this).text(initialText)
                .removeData('selected-value')
                .removeClass('btn-danger2')
                .addClass('btn-outline-danger2');
        });
    });

    $('#fileDropArea_done').on('change', function (event) {
        // Setting the PDF worker source
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        // Extracting appointment details
        var dateindex = appManager.appointmentsInstance.currentAppointment;
        const app_date = dateindex.date;
        const app_index = dateindex.index;
        const appointment = dateindex.appointment;
        const carrier = appointment.carrier;
        const homeid = appointment.homeid;

        console.log('ocr appointment', appointment);
        console.log('perform OCR check with homeid: ' + homeid, 'ocr carrier: ' + carrier);

        var file = event.target.files[0]; // Get the file

        // Validate that the file is a PDF
        if (file.type === "application/pdf") {
            // Show loading confirm box
            // Show loading confirm box
            $.confirm({
                title: 'Checking PDF',
                content: '<img src="/images/loadingdots.gif" alt="Login" style="max-width:100%; height:auto; display:block; margin:auto;" class="mb-2">', // Replace with your spinner HTML
                closeIcon: true, // Enable the close icon
                buttons: {
                    verstanden: {
                        text: 'Verstanden',
                        action: function () {
                            // Action on clicking 'Verstanden', you can leave it empty if no action is needed
                        }
                    }
                },
                onContentReady: function () {
                    const self = this; // To reference inside FileReader onload

                    // Inline processPDF logic starts here
                    const reader = new FileReader();
                    reader.onload = function (event) {
                        const pdfData = new Uint8Array(event.target.result);
                        const loadingTask = pdfjsLib.getDocument({ data: pdfData });

                        loadingTask.promise.then(function (pdf) {
                            // Check the number of pages
                            if (pdf.numPages < 2) {
                                showAlert('Die ausgewählte Datei ist zu kurz', 'alertArea_pdfhbgdone', 'danger');
                                // Update confirm box for insufficient pages
                                self.setTitle('PDF Check');
                                self.setContent('PDF zu kurz. Die PDF muss mindestens 3 Seiten enthalten.');
                                self.setType('red');
                                $('#fileDropArea_done').val(''); // Clear the input
                                $('#pdf-viewer').hide();
                            } else {
                                // Proceed with OCR check
                                performOCR(file, homeid, carrier).then(ocrResult => {
                                    // Handle OCR result
                                    if (!ocrResult.found) {
                                        showAlert('PDF validierung schlug fehl. Bitte prüfe ob die PDF zur richtigen HomeID gehört.', 'alertArea_pdfhbgdone', 'warning');
                                        // Update confirm box for OCR failure
                                        self.setTitle('PDF Check');
                                        self.setContent('Es scheint so als stimmt die HomeID in der PDF nicht mit der des Kunden überein. <br/>Bitte stelle sicher das die richtige PDF ausgewählt wurde.');
                                        self.setType('orange');
                                    } else {
                                        // Update confirm box for success and auto-close
                                        self.setTitle('Success');
                                        self.setContent('PDF validated ✓');
                                        self.setType('green');
                                        setTimeout(() => { self.close(); }, 1000); // Auto-close after 1 sec
                                    }
                                }).catch(error => {
                                    console.error(error);
                                    // Update confirm box for OCR process failure
                                    self.setTitle('OCR Failed');
                                    self.setContent('The OCR process failed. Please proceed manually.');
                                    self.setType('red');
                                });
                            }
                        }).catch(function (error) {
                            console.error('Error reading PDF: ', error);
                            // Update confirm box for PDF reading error
                            self.setTitle('Error');
                            self.setContent('Error reading PDF.');
                            self.setType('red');
                        });
                    };
                    reader.readAsArrayBuffer(file);
                }
            });


        } else {
            alert('Please select a PDF file.');
            $('#pdf-viewer').hide();
        }
    });



    // ----------------------------------------------------------------------- //
    // alert for cancel comment

    $('#commentField_cancel').on('input', function () {
        var inputLength = $(this).val().length;
        if (inputLength < 5) {
            showAlert('Der Kommentar ist zu kurz!', 'alertArea_cancelcomment', 'warning');
        } else {
            $('#alertArea_cancelcomment').fadeOut('slow', function () {
                $(this).empty().show(); // Empty the alert area and reset its display after fadeOut completes
            });
        }
    });

    // ----------------------------------------------------------------------- //
    // Handles tab switch

    function showTabContent(tabSelector) {
        // Hide all tab content
        $('.tab-pane').removeClass('show active').addClass('fade');

        // Remove 'active' class from all tabs
        $('.nav-link').removeClass('active');

        // Show selected tab content
        $(tabSelector).removeClass('fade').addClass('show active');

        // Update dropdown items visibility and set active tab in data attribute
        if (tabSelector === '#hbgdone') {
            $('#hbgDoneOption').hide();
            $('#hbgCancelOption').show();
            $('#hbgStatusDropdown').html('<i style="color: #1ab300;" class="bi bi-check2-square"></i> HBG Erledigt');
            $('#hbgStatusDropdown').data('currentTab', 'hbgdone').addClass('active'); // Add 'active' class to dropdown link
        } else if (tabSelector === '#hbgcancel') {
            $('#hbgDoneOption').show();
            $('#hbgCancelOption').hide();
            $('#hbgStatusDropdown').html('<i style="color: #bf6300;" class="bi bi-x-square"></i> HBG Abbruch');
            $('#hbgStatusDropdown').data('currentTab', 'hbgcancel').addClass('active'); // Add 'active' class to dropdown link
        }
    }

    $('#hbgDoneOption').on('click', function (e) {
        e.preventDefault();
        showTabContent('#hbgdone');
    });

    $('#hbgCancelOption').on('click', function (e) {
        e.preventDefault();
        showTabContent('#hbgcancel');
    });

    // Hide HBG tab content when other tabs are clicked
    $('.nav-link').not('#hbgStatusDropdown').on('click', function () {
        // Hide HBG tab contents
        $('#hbgdone, #hbgcancel').removeClass('show active').addClass('fade');

        // Reset dropdown items
        $('#hbgDoneOption').show();
        $('#hbgCancelOption').show();
        $('#hbgStatusDropdown').html('HBG Status').removeClass('active');
    });

    // ----------------------------------------------------------------------- //


    $(document).on('keydown', function (event) {
        if (event.key === "5") {
            console.log("Number 5 on the numeric keypad was pressed");
            $('.appointment').each(function (i) {
                const status = $(this).data('status');
                const apptstatus = $(this).data('apptstatus');
                const index = $(this).data('index');
                console.log('status, apptstatus, index', status, apptstatus, index)
            });
        }
    });

    document.getElementById('appointmentsContent').addEventListener('touchmove', function (e) {
        // Get the current scroll position
        var scrollTop = this.scrollTop;
        var scrollHeight = this.scrollHeight;
        var offsetHeight = this.offsetHeight;
        var contentHeight = scrollHeight - offsetHeight;

        // Check if the user is at the top and attempting to scroll up
        if (scrollTop === 0 && e.touches[0].clientY > e.touches[0].screenY) {
            e.preventDefault();
        }

        // Optional: Check if the user is at the bottom and attempting to scroll down
        // This can be useful to prevent pull-to-refresh like behavior at the bottom
        if (scrollTop === contentHeight && e.touches[0].clientY < e.touches[0].screenY) {
            e.preventDefault();
        }
    }, { passive: false });


    // Show/Hide the take Picture when KundeNichtDa selected to force the user for a pic
    $('.dropdown-item').click(function () {
        // Get the value of the clicked item
        var selectedValue = $(this).attr('data-value');

        // Check if the selected value is 'kunde_nicht_da'
        if (selectedValue === 'kunde_nicht_da') {
            // Show the Take Picture div
            $('#pic_kdabbruchWrapper').show();
        } else {
            // Hide the Take Picture div
            $('#pic_kdabbruchWrapper').hide();
        }
    });

}

function showAlert(message, elementId, type = 'danger') {
    const alertHtml = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                         <i class="bi bi-exclamation-triangle"></i> ${message}
                         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>`;
    $(`#${elementId}`).html(alertHtml).show();

}

// Utility function to convert a Base64 string to a Blob
function base64ToBlob(base64, mimeType) {
    const byteCharacters = atob(base64.split(',')[1]);
    const byteArrays = [];

    for (let offset = 0; offset < byteCharacters.length; offset += 512) {
        const slice = byteCharacters.slice(offset, offset + 512);

        const byteNumbers = new Array(slice.length);
        for (let i = 0; i < slice.length; i++) {
            byteNumbers[i] = slice.charCodeAt(i);
        }

        const byteArray = new Uint8Array(byteNumbers);
        byteArrays.push(byteArray);
    }

    return new Blob(byteArrays, { type: mimeType });
}
function performOCR(file, searchString, carrier = 'UGG') {

    // Define search areas for different carriers
    const searchAreas = {
        'UGG': [
            { bbox: { x0: 1110, y0: 550, x1: 1300, y1: 630 }, matches: 6 },
            { bbox: { x0: 1117, y0: 600, x1: 1300, y1: 630 }, matches: 2 },
            // Add other UGG areas as needed
        ],
        'GlasfaserPlus': [
            // Rounded areas for GlasfaserPlus
            { bbox: { x0: 20, y0: 280, x1: 110, y1: 310 }, matches: 3 },
            // Add additional rounded GlasfaserPlus areas as needed
        ]
    };

    return new Promise(async (resolve, reject) => {
        if (!(carrier in searchAreas)) {
            resolve({
                found: true
            });
            return;
        }

        const reader = new FileReader();

        reader.onload = async function (event) {
            const pdfData = new Uint8Array(event.target.result);
            const loadingTask = pdfjsLib.getDocument({
                data: pdfData
            });

            try {
                const pdf = await loadingTask.promise;
                const page = await pdf.getPage(1);
                const scale = 2; // Adjust this scale as needed
                const viewport = page.getViewport({
                    scale: scale
                });
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                await page.render({
                    canvasContext: context,
                    viewport: viewport
                }).promise;

                // Sort search areas by match frequency for the specific carrier
                const carrierSearchAreas = searchAreas[carrier].sort((a, b) => b.matches - a.matches);

                for (const area of carrierSearchAreas) {
                    const {
                        x0,
                        y0,
                        x1,
                        y1
                    } = area.bbox;
                    const cropWidth = x1 - x0;
                    const cropHeight = y1 - y0;
                    const croppedCanvas = document.createElement('canvas');
                    const croppedCtx = croppedCanvas.getContext('2d');
                    croppedCanvas.width = cropWidth;
                    croppedCanvas.height = cropHeight;
                    croppedCtx.drawImage(canvas, x0, y0, cropWidth, cropHeight, 0, 0, cropWidth, cropHeight);

                    const imageDataUrl = croppedCanvas.toDataURL('image/png');
                    const result = await Tesseract.recognize(imageDataUrl, 'eng', {
                        oem: 2
                    });

                    for (const word of result.data.words) {
                        if (word.text.includes(searchString)) {
                            resolve({
                                found: true,
                                bbox: word.bbox
                            });
                            return;
                        }
                    }
                }
                resolve({
                    found: false
                });
            } catch (error) {
                reject('Error during OCR: ' + error);
            }
        };

        reader.onerror = function (error) {
            reject('Error reading file: ' + error);
        };

        reader.readAsArrayBuffer(file);
    });
}


function print_populatefields(surveyData, appointmentData, imageData, modifiedImageData) {
    $('#surveyPagePrint').html(initialSurveyPrintState);
    const homeid = appointmentData.homeid;

    console.log('print_populatefields surveyData', surveyData)
    console.log('print_populatefields appointmentData', appointmentData)
    console.log('print_populatefields imageData', imageData)
    console.log('print_populatefields imageData', modifiedImageData)

    const suffix = /^\d+$/.test(appointmentData.streetnumberadd) ? `/${appointmentData.streetnumberadd}` : appointmentData.streetnumberadd;
    // prepare static fields
    $('#bp_street').text(appointmentData.street);
    $('#bp_streetnumber').text(appointmentData.streetnumber + suffix);
    $('#bp_zip').text(appointmentData.plz);
    $('#bp_city').text(appointmentData.city);

    // Getting today's date in the format dd.mm.yyyy
    var formattedDate = new Date().toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' });
    $('#bp_dateplace').html(`
        <div style="display: block; text-align: center;">${appointmentData.city}, den</div>
        <div style="display: block; text-align: center;">${formattedDate}</div>
    `);


    $.each(appointmentData, (key, value) => {
        switch (key) {
            case 'firstname':
                $('#bp_name_firstname_tenant').text(value);
                $('#bp_name_firstname_owner').text(value);
                break;
            case 'lastname':
                $('#bp_name_lastname_tenant').text(value);
                $('#bp_name_lastname_owner').text(value);
                break;
            case 'phone1':
                $('#bp_phone1_tenant').text(value);
                $('#bp_name_phone1_owner').text(value);
                break;
            case 'email':
                $('#bp_mail1_tenant').text(value);
                $('#bp_name_mail1_owner').text(value);
                break;
            default:
            // console.log(`No matching field found for key: ${key} and value ${value}`);
        }
    });

    $.each(surveyData, (key, value) => {
        console.log(`surveyData ${key} => ${value}`);
        switch (key) {
            case 'house_kind':
                if (value === 'house_kind_nobussiness') {
                    $('#bp_isbusiness').text('Nein')
                } else if (value === 'house_kind_isbussiness') {
                    $('#bp_isbusiness').text('Ja')
                }
                break;
            case 'housetyp':
                if (value === 'housetyp_MFH') {
                    $('#bp_housetyp').text('Mehrfamilienhaus')
                } else if (value === 'housetyp_EFH') {
                    $('#bp_housetyp').text('Einfamilienhaus')
                }
                break;
            case 'wohnungenDropdown':
                $('#bp_houseunits').text(value);
                break;
            case 'begehungsperson_verwalter':
                console.log(`set ${key} to ${value}`)
                $('#bp_check_verwalter').prop('checked', value);
                break;
            case 'begehungsperson_eigentumer':
                console.log(`set ${key} to ${value}`)
                $('#bp_check_eigentumer').prop('checked', value);
                break;
            case 'begehungsperson_bevollm':
                console.log(`set ${key} to ${value}`)
                $('#bp_check_bevollmaechtigter').prop('checked', value);
                break;
            case 'begehungsperson_name':
                $('#bp_durchgefuhrtmitValue').text(value);
                break;
            case 'trasse_HEvorhanden':
                $('#HE_fall_4').prop('checked', value)
                break
            case 'HE_UK':
                $('#HE_fall_1').prop('checked', value)
                break
            case 'HE_UE':
                $('#HE_fall_2').prop('checked', value)
                break
            case 'HE_OE':
                $('#HE_fall_3').prop('checked', value)
                break
            case 'kabellangeStrasse':
                $('#bp_meter_main_trasse').text(value)
                break
            case 'kabellangeHE':
                $('#bp_meter_main_tohup').text(value)
                break
            case 'kabellangeHUP':
                $('#bp_meter_sub_hupta').text(value)
                $('#bp_meter_sub_reserve').text('2')
                $('#bp_meter_sub_total').text(value + 2)
                break
            case 'gesamtmeter':
                $('#bp_meter_main_total').text(value)
                break
            case 'gesamtmeter':
                $('#bp_meter_sub_total').text(value)
                break
            case 'commentField_hbgnoteimportant':
                $('#bp_commenttohbg').text(value)
                break
            case 'technican_signature_preview':
                var imgTag = `<img src="${value}" alt="Technician's Signature" style="max-width:100%; height:auto; display:block;" />`;
                $('#bp_sign_technician').html(imgTag);
                break;
            case 'customer_signature_preview':
                var imgTag = `<img src="${value}" alt="Technician's Signature" style="max-width:100%; height:auto; display:block;" />`;
                $('#bp_sign_customer').html(imgTag);
                break;
            case 'kunde_herr':
                $('#bp_check_pronoun_herr_tenant').prop('checked', value)
                $('#bp_check_pronoun_herr').prop('checked', value)
                break
            case 'kunde_frau':
                $('#bp_check_pronoun_frau_tenant').prop('checked', value)
                $('#bp_check_pronoun_frau').prop('checked', value)
                break
            case 'owner_herr':
                $('#bp_check_pronoun_herr').prop('checked', value)
                break
            case 'owner_frau':
                $('#bp_check_pronoun_frau').prop('checked', value)
                break
            case 'newOwnerFirstname':
                $('#bp_name_firstname_owner').text(value)
                break
            case 'newOwnerLastname':
                $('#bp_name_lastname_owner').text(value)
                break
            case 'newOwnerMail':
                $('#bp_name_mail1_owner').text(value)
                break
            case 'newOwnerPhone':
                $('#bp_name_phone1_owner').text(value)
                break
            case 'customerMail':
                $('#bp_mail1_tenant').text(value)
                break
            default:
            // console.log(`No matching field found for key: ${key} and value ${value}`);
        }
    });

    const idToSelectorMap = {
        'heaussen_preview1': '#bp_img_HE_aussen > div:eq(0)',
        'heaussen_preview2': '#bp_img_HE_aussen > div:eq(1)',
        'heaussen_preview3': '#bp_img_HE_aussen > div:eq(2)',
        'heinnen_preview1': '#bp_img_HE_innen > div:eq(0)',
        'heinnen_preview2': '#bp_img_HE_innen > div:eq(1)',
        'heinnen_preview3': '#bp_img_HE_innen > div:eq(2)',
        'trassenverlauf_preview1': '#bp_img_trasse > div:eq(0)',
        'trassenverlauf_preview2': '#bp_img_trasse > div:eq(1)',
        'trassenverlauf_preview3': '#bp_img_trasse > div:eq(2)',
        'placehupnt_preview1': '#bp_img_hupnt > div:eq(0)',
        'placehupnt_preview2': '#bp_img_hupnt > div:eq(1)',
        'placehupnt_preview3': '#bp_img_hupnt > div:eq(2)',
        'privateleitungen_preview1': '#bp_img_private > div:eq(0)',
        'privateleitungen_preview2': '#bp_img_private > div:eq(1)',
        'privateleitungen_preview3': '#bp_img_private > div:eq(2)',
        'hausnummer_preview1': '#bp_img_haus > div:eq(0)',
        'hausnummer_preview2': '#bp_img_haus > div:eq(1)',
        'hausnummer_preview3': '#bp_img_haus > div:eq(2)',
        'kataster_preview1': '#bp_img_kataster > div:eq(0)',
        'kataster_preview2': '#bp_img_kataster > div:eq(1)',
        'kataster_preview3': '#bp_img_kataster > div:eq(2)',
    };

    // first fill out all stuff with the existing images
    Object.entries(imageData).forEach(([imgId, image]) => {
        if (idToSelectorMap[imgId]) {
            // Set the image as a background on the div instead of using an img tag
            $(idToSelectorMap[imgId]).css({
                'background-image': `url("${image.data}")`,
                'background-size': 'cover',
                'background-position': 'center center',
                'width': '100%', // Ensure the div has a set width
                'height': '100%' // Ensure the div has a set height
            });
        } else {
            console.log(`No matching case found for image ID: ${imgId}`);
        }
    });

    // then overwrite with existing modified images
    Object.entries(modifiedImageData).forEach(([imgId, image]) => {
        if (idToSelectorMap[imgId]) {
            // Set the image as a background on the div instead of using an img tag
            $(idToSelectorMap[imgId]).css({
                'background-image': `url("${image.data}")`,
                'background-size': 'cover',
                'background-position': 'center center',
                'width': '100%', // Ensure the div has a set width
                'height': '100%' // Ensure the div has a set height
            });
        } else {
            console.log(`No matching case found for image ID: ${imgId}`);
        }
    });

    $('#surveyPagePrint').show();
    $('#surveyPagePrint_loader').show();


    setTimeout(() => {
        PDF_generateProtokoll().then(pdf => {
            const date = new Date();
            const dateString = date.toISOString().substring(0, 10).replace(/-/g, '_'); // Format as "YYYY_MM_DD"
            const filename = `${dateString}_${appointmentData.homeid}.pdf`;
            pdf.save(filename);
            appManager.appointmentsInstance.PDF_storeProtokoll(pdf, filename, homeid);
            $('#surveyPagePrint').hide();
            $('#surveyPagePrint_loader').hide();
        }).catch(error => {
            console.error("Error generating PDF:", error);
            $('#surveyPagePrint').hide();
            $('#surveyPagePrint_loader').hide();
        });
    }, 250); // 250 milliseconds delay

}



function PDF_generateProtokoll() {
    return new Promise((resolve, reject) => {
        const pdf = new jspdf.jsPDF({
            orientation: "portrait",
            unit: "pt",
            format: "a4"
        });

        const contents = [
            document.querySelector("#bp_page_1"),
            document.querySelector("#bp_page_2"),
            document.querySelector("#bp_page_3"),
            document.querySelector("#bp_page_4")
        ];

        let currentPage = 0;

        const renderPageContent = () => {
            //console.log(`Rendering page ${currentPage + 1}`);
            if (currentPage < contents.length) {
                if (!contents[currentPage]) {
                    //console.error(`Content for page ${currentPage + 1} not found`);
                    reject(`Content for page ${currentPage + 1} not found`);
                    return;
                }

                setTimeout(() => { // Adding a delay before rendering
                    html2canvas(contents[currentPage], { scale: 2 }).then(canvas => {
                        const canvasAspectRatio = canvas.height / canvas.width;
                        const pdfWidth = pdf.internal.pageSize.getWidth() - 20;
                        const pdfHeight = pdfWidth * canvasAspectRatio;

                        if (currentPage > 0) {
                            pdf.addPage();
                        }

                        pdf.addImage(canvas.toDataURL('image/jpeg', 0.75), 'JPEG', 10, 10, pdfWidth, pdfHeight);

                        currentPage++;
                        //console.log(`Completed rendering page ${currentPage}`);

                        if (currentPage < contents.length) {
                            renderPageContent();
                        } else {
                            // Commenting out the deletePage line for testing
                            // pdf.deletePage(pdf.internal.getNumberOfPages());
                            resolve(pdf); // Resolve the promise with the pdf document
                        }
                    }).catch(error => {
                        console.error(`Error rendering page ${currentPage + 1}:`, error);
                        reject(error);
                    });
                }, 100); // Delay of 100 milliseconds
            }
        };

        renderPageContent();
    });
}



async function handleImageUpload(imagesData, homeid, customToken) {
    console.log('handleImageUpload');
    for (let imgId in imagesData) {
        const image = imagesData[imgId];
        console.log('loop image', image);
        try {
            // Pass the base64 data to processImage
            await processImage(image.data, processedBlob => {
                uploadImage(processedBlob, imgId, homeid, customToken);
            });
        } catch (error) {
            console.error('Error in processing image:', imgId, error);
        }
    }
}

function uploadImage(imageBlob, imgId, homeid) {
    console.log(`Compressed image size: ${imageBlob.size} bytes`);

    let formData = new FormData();
    formData.append('image', imageBlob);
    formData.append('imgId', imgId);
    formData.append('homeid', homeid);
    formData.append('action', 'upload_image'); // Add action for the server-side script

    $.ajax({
        url: '/upload.php', // Adjusted URL for image upload
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function (xhr) {
            xhr.setRequestHeader('X-Custom-Token', customToken); // Set custom token header
        },
        success: function (response) {
            console.log(`Image ${imgId} uploaded successfully:`, response);
        },
        error: function (err) {
            console.error(`Error uploading image ${imgId}:`, err);
        }
    });
}


function processImage(imageData, callback, maxWidth = 800, maxHeight = 600, quality = 0.7) {
    return new Promise((resolve, reject) => {
        console.log('processImage now >>');
        const img = new Image();
        img.onload = () => {
            // Create an off-screen canvas
            let canvas = document.createElement('canvas');
            let ctx = canvas.getContext('2d');

            // Calculate the new dimensions
            let ratio = Math.min(maxWidth / img.width, maxHeight / img.height);
            let newWidth = img.width * ratio;
            let newHeight = img.height * ratio;

            // Set canvas size
            canvas.width = newWidth;
            canvas.height = newHeight;

            // Draw and resize image
            ctx.drawImage(img, 0, 0, newWidth, newHeight);

            // Convert to blob
            canvas.toBlob(blob => {
                console.log(`Original image size: ${blob.size} bytes`);
                callback(blob);
                resolve(); // Resolve the promise after processing
            }, 'image/jpeg', quality);
        };
        img.onerror = () => {
            console.error('Error loading image:', imageData);
            reject('Image load error');
        };
        img.src = imageData; // Set the src to the base64 data
    });
}
