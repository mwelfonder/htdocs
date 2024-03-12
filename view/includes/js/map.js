// Predefine the variables
var json_begeher;
var json_homeids;
var openLength;
var plannedLength;
var json_projects;
var json_calendar;
var json_pegman;
var json_open;
var json_pending;
var json_planned;
var json_overdue;
var json_done;
var json_stopped;

console.log('fetch data now');
$.ajax({
    url: "view/load/map_load.php",
    type: 'POST',
    data: {
        func: 'fetch_data'
    },
    dataType: 'json',
    success: function (data) {
        console.log('response init load', data);

        // Assign the values to the pre-defined variables
        json_begeher = data.begeher;
        console.log('json_begeher', json_begeher);

        json_homeids = data.homeid_array;
        console.log('json_homeids', json_homeids);

        openLength = Object.keys(json_homeids.open || {}).length;
        console.log("Open Length:", openLength);

        plannedLength = Object.keys(json_homeids.planned || {}).length;
        console.log("Planned Length:", plannedLength);

        json_projects = data.projects_array;
        console.log('json_projects', json_projects);

        json_calendar = data.calendar_array;
        console.log('json_calendar', json_calendar);

        json_pegman = data.pegmenData_array;
        console.log('json_pegman', json_pegman);

        json_open = data._open;
        console.log('json_open', json_open);

        json_pending = data._pending;
        console.log('json_pending', json_pending);

        json_planned = data._planned;
        console.log('json_planned', json_planned);

        json_overdue = data._overdue;
        console.log('json_overdue', json_overdue);

        json_done = data._done;
        console.log('json_done', json_done);

        json_stopped = data._stopped;
        console.log('json_stopped', json_stopped);

        $(document).trigger('dataFetched', [data]);
    },
    error: function (error) {
        console.log("Error:", error);
    }
});




$(document).on('dataFetched', function (event, data) {
    window.currentUser = $("#myusername").text(); // get the username revieved from php

    // wrap in function to get wrapped scope level


    window.map = L.map("leaflet", { doubleClickZoom: false }).setView([51.159328, 10.44594], 7);

    //L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    //L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
    window.leaflet_maplayer = L.tileLayer(
        "https://tile.openstreetmap.org/{z}/{x}/{y}.png",
        {
            subdomains: ["mt0", "mt1", "mt2", "mt3"],
            maxZoom: 19,
            preferCanvas: true,
            attribution:
                '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        }
    ).addTo(map);
    window.layer_gstreet = L.tileLayer(
        "https://tile.openstreetmap.org/{z}/{x}/{y}.png",
        {
            maxZoom: 19,
        }
    );
    window.layer_gsatelite = L.tileLayer(
        "https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}",
        {
            maxZoom: 19,
        }
    );


    init_pickme(); // inits the draggable possibility of pegman
    init_pickmeWeek();

    // -----------------------------------------------------------------------
    // change map layer
    $(".mpbtn.mapchange").on("click", function () {
        if ($(this).hasClass("satelite")) {
            $(this).removeClass("satelite");
            $(this).addClass("street");
            //console.log('switch to satelite')
            leaflet_maplayer.setUrl(window.layer_gsatelite._url);
        } else {
            $(this).removeClass("street");
            $(this).addClass("satelite");
            leaflet_maplayer.setUrl(window.layer_gstreet._url);
        }
    });
    // -----------------------------------------------------------------------
    // Slider Events
    // --- close on all clicks outside of the slider
    $(document).click(function (event) {
        const elementsToIgnore = ["#userplatestatsslider", ".slideclosebtn", ".jconfirm", ".context-menu-list"];

        if (elementsToIgnore.every(selector => !$(selector).is(event.target) && $(selector).has(event.target).length === 0)) {
            $("#userplatestatsslider").hide().css({ "marginLeft": "0px", "width": "0px" });
            $(".statkwwrapper").hide();
        }
    });

    // --- close on closebtn
    $(".slideclosebtn").click(function () {
        var slider = $("#userplatestatsslider");
        slider.hide();
        slider.css("marginLeft", "0px");
        slider.css("width", "0px");
        $(".statkwwrapper").each(function () {
            $(this).hide();
        });
    });
    // --- slider open, check which state we are
    $(".calweekbtn").click(function (event) {
        event.stopPropagation();
        $(".calweekbtn")
            .removeClass("selected")
            .each(function () {
                let currentText = $(this).text().trim();
                $(this).text(currentText);
            });
        var currentText = $(this).text();
        $(this).prepend('<i class="ri-arrow-right-double-line"></i>');
        $(this).addClass("selected");

        const week = $(this).text();
        const user = $(this).closest(".userbox").attr("id");
        const slider = $("#userplatestatsslider");
        const $visibleContent = $(".hiddenstats:visible");

        // If there is visible content, slide it out
        if ($visibleContent.length > 0) {
            slider.hide();
            slider.css("marginLeft", "0px");
            slider.css("width", "0px");
            $(".statkwwrapper").each(function () {
                $(this).hide();
            });
            loadKwContent(user, week, slider);
        } else {
            loadKwContent(user, week, slider);
        }
    });
    // --- find content and display this in the slider
    function loadKwContent(user, week, slider) {
        $(".statkwwrapper").each(function () {
            $(this).hide();
        });
        $(".hiddenstats").each(function () {
            let searchID = $(this).attr("id");
            const parentDiv = $(this);

            if (searchID === user) {
                $(this)
                    .find(".statkwwrapper")
                    .each(function () {
                        let element = $(this);
                        let elementID = element.attr("id");
                        if (elementID.includes(week)) {
                            $(".slideclosebtn").show();
                            slider.css("zIndex", "9");
                            parentDiv.show();
                            element.show();

                            slider.show().animate(
                                {
                                    marginLeft: "-1px",
                                    width: widthCalendarSlider,
                                },
                                "slow",
                                function () {
                                    console.log(
                                        "Element with ID: " + elementID + " has slid to the right"
                                    );
                                }
                            );
                        }
                    });
            }
        });
    }

    //____________________________________________________________________// 
    // attach ticket handler to history
    $('#infoboard_timelinewrapper').on('click', '.tickettimeline', function () {
        var homeid = $('#customer_homeid').text();
        var ticketID = $(this).data('ticketid');


        // If not, make an AJAX call to fetch it
        $.ajax({
            url: 'view/load/tickets_load.php',
            method: 'POST',
            data: { func: 'ticket_loadData', homeid: homeid, ticket_id: ticketID },
            dataType: 'json',
            success: function (response) {
                // Depending on your server response structure, you might need to adjust the following lines:
                ticketSearchResults = [response]; // Store the fetched data
                Ticket_show(homeid, ticketID); // Call Ticket_show
            },
            error: function (error) {
                console.error("Data loading error: ", error);
                // Handle error appropriately here
            }
        });

    });





    // -----------------------------------------------------------------------
    // event handler for tempEvent
    $(document).on("click", ".calevent.tempEvent", function () {
        $(".calevent.tempEvent").removeClass("active");
        var tempEventData = $(this).data('slotData');
        $(this).addClass("active");
        tempEventData.homeid = currentAktivMarker.homeid;
        console.log('tempEventData')
        console.log(tempEventData)
        createEventforUser(tempEventData);
    });

    // -----------------------------------------------------------------------
    // event handler for phoneclick
    $(document).on("click", ".phone-link", function () {
        console.log('phone action')
        var clickedClass = $(this).parent().attr("id");
        var clickedText = $(this).text();
        var homeid = $('#customer_homeid').text();

        var myActions = {
            action1: 'click phonenumber',
            action2: clickedClass,
            action3: clickedText,
            homeid: homeid,
            source: 'map',

        };

        let json = JSON.stringify({ myActions });
        $.ajax({
            method: "POST",
            url: "view/load/map_load.php",
            data: {
                func: "saveUserLog",
                data: json,
            },
        }).done(function (response) {
            console.log(response)

        });
    });

    // ---------------------------------------------------------------------------
    // fix userplates widht height, progress bar and ordering
    userplates_fix();

    // -----------------------------------------------------------------------
    // create project markers
    if (json_projects) {
        createProjectMarkers();
    }


    openLayerGroup = L.layerGroup();
    pendingLayerGroup = L.layerGroup();
    plannedLayerGroup = L.layerGroup();
    overdueLayerGroup = L.layerGroup();
    doneLayerGroup = L.layerGroup();
    stoppedLayerGroup = L.layerGroup();
    clusterGroup = L.markerClusterGroup();

    if (json_open) {
        json_open.forEach(home => {
            createMarker(home, marker_color_open, openMarkers, openLayerGroup, 'open');
        });
        console.log('@@@@@@@@@@@@@@@@@@@@@@@@@@@ json_open LOADED')
    }

    if (json_planned) {
        json_planned.forEach(home => {
            createMarker(home, marker_color_planned, plannedMarkers, plannedLayerGroup, 'planned');
        });
        console.log('@@@@@@@@@@@@@@@@@@@@@@@@@@@ json_planned LOADED')
    }

    if (json_pending) {
        json_pending.forEach(home => {
            createMarker(home, marker_color_planned, pendingMarkers, pendingLayerGroup, 'pending');
        });
        console.log('@@@@@@@@@@@@@@@@@@@@@@@@@@@ json_pending LOADED')
    }

    if (json_overdue) {
        json_overdue.forEach(home => {
            createMarker(home, marker_color_overdue, overdueMarkers, overdueLayerGroup, 'overdue');
        });
        console.log('@@@@@@@@@@@@@@@@@@@@@@@@@@@ json_overdue LOADED')
    }

    if (json_done) {
        json_done.forEach(home => {
            createMarker(home, marker_color_done, doneMarkers, doneLayerGroup, 'done');
        });
        console.log('@@@@@@@@@@@@@@@@@@@@@@@@@@@ json_done LOADED')
    }

    if (json_stopped) {
        json_stopped.forEach(home => {
            createMarker(home, marker_color_stopped, stoppedMarkers, stoppedLayerGroup, 'stopped');
        });
        console.log('@@@@@@@@@@@@@@@@@@@@@@@@@@@ json_stopped LOADED')
    }


    let clusterLayer = L.markerClusterGroup({
        disableClusteringAtZoom: 14,
    });

    // group the layers togteher with the checkbox id to loop over it and show/hide the layers
    const layerMapping = {
        openCheckbox: openLayerGroup,
        plannedCheckbox: plannedLayerGroup,
        pendingCheckbox: pendingLayerGroup,
        overdueCheckbox: overdueLayerGroup,
        doneCheckbox: doneLayerGroup,
        stoppedCheckbox: stoppedLayerGroup
    };

    // On "Select / Deselect All" checkbox change
    $('#sc4statsselector').change(function () {
        let isChecked = $(this).is(':checked');
        $.each(layerMapping, (checkboxId, layerGroup) => {
            $(`#${checkboxId}`).prop('checked', isChecked).trigger('change');
        });
    });

    // Logic to uncheck "Select / Deselect All" if any of the layer checkboxes is unchecked
    $.each(layerMapping, (checkboxId, layerGroup) => {
        $(`#${checkboxId}`).change(function () {
            if (!$(this).is(':checked')) {
                $('#sc4statsselector').prop('checked', false);
            }
        });
    });

    // Check initial checkbox status and toggle layers accordingly
    $.each(layerMapping, (checkboxId, layerGroup) => {
        if ($(`#${checkboxId}`).is(':checked')) {
            clusterLayer.addLayer(layerGroup);
        }

        // Attach event listener for individual checkboxes
        $(`#${checkboxId}`).change(function () {
            toggleLayer($(this).is(':checked'), layerGroup);
        });
    });

    clusterLayer.addTo(map);

    function toggleLayer(isChecked, layerGroup) {
        if (isChecked) {
            clusterLayer.addLayer(layerGroup);
        } else {
            clusterLayer.removeLayer(layerGroup);
        }
    }

    // ---------------------------------------------------

    loadMarkers(); // loads the pegman markers

    //-----------------------------------------------------------------------------
    calendarView_hourSlotHeight = $(".hour-slot").first().outerHeight();
    $(".homeidday").each(function () {
        let $this = $(this);
        let hourSlotPosition = $this.find('.hour-slot').first().position().top; // Gets the position of the first .hour-slot relative to its parent, .homeidday
        $this.find(".abs_overlay").css("top", hourSlotPosition);
    });
    calendar_init(); // init the calendar view
    widthCalendarSlider = $(".homeidday").outerWidth() * 5;
    $("#userplatestatsslider").css("width", "0");
    $(".hiddenstats").hide();
    $(".statkwwrapper").hide();
    $(".slideclosebtn").hide();
    getAllOpenSlots();

    // -----------------------------------------------------------------------------------
    // remove phoner interaction buttons for users without phoning rights like insyte
    if (!hasPerm(5)) { // 5 = telefonist
        $('.infoboard_userinteractions').remove();
        $('#map_userplateswrapper').remove();
        console.log('userSidbar removed, no permission')
    } else {
        console.log('userSidbar NOT removed, no permission')
    }
    if (!hasPerm(2)) {// 2 = admin
        $('#adminpannel,#mapsettingsbtn').remove();
    }

    $(".loader_wrap").hide();


    $(document).on('keydown', function (event) {
        if (event.key === "5") {
            console.log("Number 5 on the numeric keypad was pressed");
            console.log(currentAktivMarker)
        }
    });


    // -------------------------------------------------
    // Check if the elements exist
    var $leaflet = $('#leaflet');
    var $mapswitcher = $('#mapswitcher');
    var $mapswitcherOptionWrapper = $('#mapswitcher_optionwrapper');

    if ($leaflet.length && $mapswitcher.length && $mapswitcherOptionWrapper.length) {
        var leafletTop = $leaflet.offset().top;
        var mapswitcherTop = $mapswitcher.offset().top;
        var relativeTopPosition = mapswitcherTop - leafletTop;

        $mapswitcherOptionWrapper.css({
            'position': 'absolute',
            'top': relativeTopPosition + 'px'
        });
    } else {
        console.log('One or more elements are missing.');
    }
    // -------------------------------------------------
    // ticket options handler
    $('#mapticket_wrapper').on('wheel mousewheel', function (e) {
        e.stopPropagation();
    });



    var TicketdataLoaded = false;
    $(document).on("click", "#mapticket", function () {
        // hide if other wrapper are visible
        if ($('#mapfilter_wrapper').is(':visible')) {
            $('#mapfilter_wrapper').hide();
        }
        if (TicketdataLoaded) {
            $('#mapticket_wrapper').toggle();
            return;
        } else {
            TicketdataLoaded = true;
            $(this).toggleClass("rotate");
        }

        $.ajax({
            method: "POST",
            url: "view/load/map_load.php",
            data: {
                func: "fetchTickets",
            },
        }).done(function (response) {
            $('#mapticket').toggleClass("rotate");
            console.log(response);
            let data = JSON.parse(response);
            console.log(data);

            // group tickets by city
            var ticketsByCity = {};
            data.forEach(ticket => {
                var city = ticket.city;
                if (!ticketsByCity[city]) {
                    ticketsByCity[city] = [];
                }
                ticketsByCity[city].push(ticket);
            });
            var cities = Object.keys(ticketsByCity);
            cities.sort();

            let htmlContent = '';
            for (const city of cities) {
                let ticketCount = ticketsByCity[city].length;
                htmlContent += '<div class="ticketitemcity" data-city="' + city + '">';
                htmlContent += '<div class="city-header">' + city + ' (' + ticketCount + ')</div>';
                htmlContent += '<div class="tickets" style="display:none;">';
                ticketsByCity[city].forEach(ticket => {
                    var dateTimeParts = ticket.ticket_creation.split(' ');
                    // Isolate the date and time
                    var ticketDate = convertDateFormat(dateTimeParts[0]);
                    var ticketTime = dateTimeParts[1];
                    const datetimestamp = ticketDate + " - " + ticketTime;
                    htmlContent += '<div class="ticketitem" data-homeid="' + ticket.homeid + '" data-lat="' + ticket.lat + '" data-lon="' + ticket.lon + '" data-ticketid="' + ticket.ticket_id + '">';
                    htmlContent += '<div class="ticketitem_header">#' + ticket.ticket_ident + ' ' + ticket.ticket_title + '<span class="tickettimelinestatus">' + ticket.ticket_status + '</div>';
                    htmlContent += '<span>' + datetimestamp + '</span>';
                    htmlContent += '<div class="ticketitem_content">' + ticket.ticket_finaldescription + '</div>';
                    htmlContent += '<div class="ticketitem_subcontent">' + ticket.homeid + '</div>';
                    htmlContent += '</div>';
                });
                htmlContent += '</div>'; // end of tickets
                htmlContent += '</div>'; // end of city
            }

            $('#mapticket_wrapper').html(htmlContent);
            $('#mapticket_wrapper').toggle();

            $('.city-header').click(function () {
                $('.ticketitemcity').removeClass('selected');
                $('.tickets').not($(this).siblings('.tickets')).hide();

                $(this).siblings('.tickets').toggle();
                $(this).parent('.ticketitemcity').toggleClass('selected');
            });

            $(document).on('click', '.ticketitem', function () {
                $('.ticketitem').removeClass('selected');
                $(this).addClass('selected');
                var homeid = $(this).data('homeid');
                var lat = $(this).data('lat');
                var lon = $(this).data('lon');
                map.flyTo([lat, lon], 16, { duration: mapFlyDuration });
                map.once('moveend', function () {
                    setTimeout(function () {
                        for (var i = 0; i < markers.length; i++) {
                            if (markers[i].homeid == homeid) {
                                infoBoardLoadData(markers[i]);
                                markers[i].fire('click'); // fire click event on the matching marker
                                break;
                            }
                        }
                    }, 200); // delay of 1000ms
                });
            });



        });
    });

    // -------------------------------------------------
    // filter options handler
    $(document).on("click", "#mapfilter", function () {
        // hide if other wrapper are visible
        if ($('#mapticket_wrapper').is(':visible')) {
            $('#mapticket_wrapper').hide();
        }
        $('#mapfilter_wrapper').toggle();
    });

    // ---> update layer numbers
    const markersArrayMapping = {
        doneMarkers: 'doneCheckboxAll',
        openMarkers: 'openCheckboxAll',
        plannedMarkers: 'plannedCheckboxAll',
        overdueMarkers: 'overdueCheckboxAll',
        stoppedMarkers: 'stoppedCheckboxAll',
        pendingMarkers: 'pendingCheckboxAll'
    };

    $.each(markersArrayMapping, (markersArray, checkboxId) => {
        let total = window[markersArray].length;
        $(`#${checkboxId}`).text(`(${total})`);
    });

    // ---> update visible numbers for each layer in bound
    map.on('moveend', function () {
        const currentBounds = map.getBounds();

        const markersMappings = {
            openMarkers: '#openCheckboxBoundary',
            pendingMarkers: '#pendingCheckboxBoundary',
            plannedMarkers: '#plannedCheckboxBoundary',
            overdueMarkers: '#overdueCheckboxBoundary',
            doneMarkers: '#doneCheckboxBoundary',
            stoppedMarkers: '#stoppedCheckboxBoundary'
        };

        for (const [markersArray, spanId] of Object.entries(markersMappings)) {
            let count = 0;

            window[markersArray].forEach(function (marker) {
                if (currentBounds.contains(marker.getLatLng())) {
                    count++;
                }
            });

            $(spanId).text(`(${count})`);
        }
    });

    var mapoptionExportCSV_isPressed = false;
    $('#mapoptionExportCSV').click(() => {
        if (mapoptionExportCSV_isPressed) {
            // return if button is already pressed to prevent double clicks
            return;
        }
        mapoptionExportCSV_isPressed = true;
        let homeIds = getHomeIdsFromVisibleMarkers();
        $.ajax({
            method: "POST",
            url: "view/load/map_load.php",
            data: {
                func: "downloadCSV",
                data: JSON.stringify(homeIds),
            },
        }).done(function (fileUrl) {

            // Download the file
            let downloadLink = document.createElement('a');
            downloadLink.href = fileUrl;
            downloadLink.download = "homeids.csv";
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
            mapoptionExportCSV_isPressed = false;
        });
    });


    function getHomeIdsFromVisibleMarkers() {
        let homeIds = [];
        const visibleMarkers = getVisibleMarkersWithinBounds();
        visibleMarkers.forEach((marker) => {
            homeIds.push(marker.scan4.homeid);
        });

        return homeIds;
    }

    function getVisibleMarkersWithinBounds() {
        let visibleMarkers = [];

        const currentBounds = map.getBounds();
        $.each(layerMapping, (checkboxId, layerGroup) => {
            if ($(`#${checkboxId}`).is(':checked')) {
                layerGroup.eachLayer((marker) => {
                    if (currentBounds.contains(marker.getLatLng())) {
                        visibleMarkers.push(marker);
                    }
                });
            }
        });

        return visibleMarkers;
    }


    // ----------------------------------------------------------------------------
    // handle for ticket timeline
    $(document).on('click', '.timeline-content-inner.tickettimeline', function () {
        var ticketID = $(this).data('ticketid');
        console.log(ticketID);
        ticketCall(ticketID);
    });



    // ----------------------------------------------------------------------------
    // remove pageloader
    $(".loader_wrap").hide();


    (function () {
        const url = window.location.href;
        const parsedUrl = new URL(url);
        // change search term to ? instead of &
        const fixedSearchParams = parsedUrl.search.replace(/\?/g, '&').slice(1);
        const searchParams = new URLSearchParams(fixedSearchParams);
        const homeid = searchParams.get('homeid');

        if (homeid) {
            infoPlateIconClick(); // animate the infoboard
            infoBoardLoadData(homeid);
            console.log('url homeid found, looking for marker with homeid: ' + homeid);
            const marker = markers.find(marker => marker.homeid === homeid);
            if (marker) {
                console.log('marker found');
                marker.fire('click'); // Trigger click event on the matching marker
            } else {
                console.log('marker not found');
            }
        }

    })();
});
// ----------------------------- END OF DOCUMENT READY -----------------------------
// ----------------------------- END OF DOCUMENT READY -----------------------------
// ----------------------------- END OF DOCUMENT READY -----------------------------
// ----------------------------- END OF DOCUMENT READY -----------------------------
// ----------------------------- END OF DOCUMENT READY -----------------------------
$(document).on('click', function (event) {
    var target = $(event.target);

    // -----------------------------------------------------------------------------
    // remove the animation class from the planbar buttons in the infoboardwrapper
    if (!target.closest('#planen, #planen_cancel, #planen_move').length) {
        $('#planen_cancel, #planen_move').removeClass('showed');
    }
});




var widthCalendarSlider;
var calendarView_hourSlotHeight;
let dispersedMarkers = [];
let dispersedLines = [];

var markers = []; // stores all markers
var openMarkers = [];
var pendingMarkers = [];
var plannedMarkers = [];
var overdueMarkers = [];
var doneMarkers = [];
var stoppedMarkers = [];
var pegmanMarkers = []; // this store all pegman pins
var eventMarkers = []; // stores all calendar events
var projectMarkers = [];
var projectLayer;
var showProjectMarkers = false;
var markerLayer;
var clusterLayer;
var currentAktivMarker; // store a clicked marker here
var markerCircle; // create a radius around a clicked marker
var userWeekRecords = {}; // stores the lowest driving time for each kw at each user
var infoBoardvisible = false; // stores the current state of the infoboard
let InfoBoardActiveNote = "addnote"; // keep track of the current active notebox
var InfoBoardNotes = {
    // keep track of the user input in the notebox
    addnote: "",
    nohbg: "",
    followup: "",
    planappoint: "",
};


var OpenSlots = {};

const marker_color_planned = "#ffdd5f";
const marker_color_open = "#2196F3";
const marker_color_pending = "#da831c";
const marker_color_changed = "#656565";
const marker_color_active = "#12e0ea";
const marker_color_overdue = "#8628f2";
const marker_color_done = "#1da340";
const marker_color_stopped = "#f2282b";
const marker_color_prio1 = '#ff54e1';
const marker_color_prio2 = '#ff0084';
const marker_color_prio3 = '#ff0084';
const marker_color_prio4 = '#ff54e1';
const marker_color_prio5 = '#ff54e1';
const marker_color_mdu = '#ff54e1';

const color_open = "#2196f3";
const color_planned = "#f3d921";
const color_pending = "#da831c";
const color_done = "#2fbb4f";
const color_stopped = "#f76464";

const color_overdue = "#7c15a9";
const color_notset = "#8b8b8b";

const mapFlyDuration = 0.5;

var myEvents = []; // this stores all events which the user has created

console.log(getCurrentDate("YYYY-MM-DD"));


var openLayerGroup = '';
var pendingLayerGroup = '';
var plannedLayerGroup = '';
var clusterGroup = '';


const namesToHide = ['AngeloSchoen', '', '', '', ''];

function createProjectMarkers() {

    var blueIcon = L.icon({
        iconUrl: "https://crm.scan4-gmbh.de/view/images/map_marker_blue.png",
        iconSize: [25, 25],
    });

    var redIcon = L.icon({
        iconUrl: "https://crm.scan4-gmbh.de/view/images/map_marker_orange.png",
        iconSize: [25, 25],
    });

    projectLayer = L.layerGroup(); // Remove .addTo(map) from here

    for (var city in json_projects) {
        if (json_projects.hasOwnProperty(city)) {
            var project = json_projects[city];
            var lat = parseFloat(project.lat);
            var lon = parseFloat(project.lon);

            if (!isNaN(lat) && !isNaN(lon)) {
                var icon =
                    project.client == "Insyte"
                        ? blueIcon
                        : project.client == "Moncobra"
                            ? redIcon
                            : null;
                if (icon) {
                    var marker = L.marker([lat, lon], {
                        icon: icon,
                        project: city
                    }).addTo(projectLayer);

                    marker.bindPopup(getProjectPopupContent(project), {
                        className: "projectmarkerpopup",
                    });
                    projectMarkers.push(marker); // add each marker to projects group
                }
            }
        }
    }

    showProjectMarkers = false;
    if (showProjectMarkers) {
        projectLayer.addTo(map);
    }

    $("#markersprojects").on("change", function () {
        if (showProjectMarkers) {
            projectLayer.removeFrom(map);
        } else {
            projectLayer.addTo(map);
        }
        showProjectMarkers = !showProjectMarkers;
    });
}


// Function to get project popup content (assuming it is not defined elsewhere)
function getProjectPopupContent(project) {
    var popupContent = "<b>Projekt:</b> " + project.city + "<br>";
    popupContent += "<b>Open:</b> " + project.total + "<br>";
    popupContent += "<b>Client:</b> " + project.client + "<br>";
    popupContent += "<b>Carrier:</b> " + project.carrier + "<br>";
    return popupContent;
}


function onMarkerClick(marker, home) {
    if (currentAktivMarker && currentAktivMarker.setStyle && typeof currentAktivMarker.setStyle === 'function') {
        if (typeof currentAktivMarker.originalColor === 'string' && /^#[0-9A-F]{3,6}$/i.test(currentAktivMarker.originalColor)) {
            currentAktivMarker.setStyle({ color: currentAktivMarker.originalColor });
        } else {
            console.error('Invalid or undefined originalColor for currentAktivMarker');
        }
    } else {
        console.error('currentAktivMarker is undefined or does not have a setStyle method');
    }


    // Change the color of the clicked marker.
    if (marker && marker.setStyle && typeof marker.setStyle === 'function') {
        if (typeof marker_color_active === 'string' && /^#[0-9A-F]{3,6}$/i.test(marker_color_active)) {
            marker.setStyle({ color: marker_color_active });
        } else {
            console.error('Invalid color format for marker_color_active');
        }
    } else {
        console.error('Marker is undefined or does not have setStyle method');
    }
    currentAktivMarker = marker; // save this clicked marker globally
    console.log('currentaktiveMarker set to', currentAktivMarker)

    // Reset previous setup
    $(".multiboxwrapper.marginbox.userbox").show(); // Re-show all plates again
    userplates_hide(); // fix plates again
    $(".calweekbtn").removeClass("highlighted"); // Remove highlight bg on kw text
    $(".calevent.tempEvent").remove(); // Remove all tempEvents
    userWeekRecords = {}; // Reset the obj to hold the lowest driving time in circle

    let infoboard_homeid = $("#customer_homeid").text();
    if (marker.homeid === infoboard_homeid) {
        $(".infoboard_contentparttop").css("background", "#fff"); // Color the infoboard white to ensure mismatch between board an map info
    } else {
        $(".infoboard_contentparttop").css("background", "#ffcece"); // Color the infoboard red to ensure mismatch between board an map info
    }

}


// Generate popup content function
function getPopupContent(home) {
    var popupContent = "<b>Home ID:</b> " + home.homeid + "<br>";
    popupContent += "<b>Projekt:</b> " + home.city + "<br>";
    popupContent += "<b>Adresse:</b> " + home.street + ' ' + home.streetnumber + home.streetnumberadd + "<br>";
    popupContent += "<b>DP:</b> " + home.dpnumber + "<br>";
    popupContent += "<b>Client:</b> " + home.client + "<br>";
    popupContent += "<b>Carrier:</b> " + home.carrier + "<br>";
    popupContent +=
        "<b>Name:</b> " + home.lastname + " " + home.firstname + "<br>";
    if (typeof home.hausbegeher !== "undefined") {
        popupContent += "- - - - - - - - - - - - - - - - " + "<br>";
        popupContent += "<b>Begeher:</b> " + home.hausbegeher + "<br>";
        popupContent += "<b>Datum:</b> " + convertDateFormat(home.date) + "<br>";
        popupContent += "<b>Zeit:</b> " + home.time + " Uhr<br>";
    }
    popupContent +=
        '  <div class="row" style="text-align: center;flex-wrap: nowrap; margin-top: 10px;"><div class="col">Info</div></div>';
    popupContent +=
        '  <div class="row" style="text-align: center;"><div class="col"><i class="ri-file-info-line infoplate"></i></div></div>';
    return popupContent;
}


// Create marker function
function createMarker(home, markerColor, markerGroup, layerGroup, homeStatus) {
    let lat = parseFloat(home['lat']);
    let lon = parseFloat(home['lon']);

    if (isNaN(lat) || isNaN(lon)) return; // Skip if lat or lon is not a number

    if ((home.scan4_status === 'OPEN' || home.scan4_status === 'PENDING') && home.priority === '1') {
        markerColor = marker_color_prio1;
    } else if ((home.scan4_status === 'OPEN' || home.scan4_status === 'PENDING') && home.priority === '2') {
        markerColor = marker_color_prio2;
    } else if ((home.scan4_status === 'OPEN' || home.scan4_status === 'PENDING') && home.priority === '3') {
        markerColor = marker_color_prio3;
    } else if ((home.scan4_status === 'OPEN' || home.scan4_status === 'PENDING') && home.city.includes('MDU')) {
        markerColor = marker_color_mdu;
    }
    if (home.scan4_status === 'PLANNED') {
        markerColor = marker_color_planned;
    } else if (home.calls && home.calls.call_date === getCurrentDate("YYYY-MM-DD")) {
        markerColor = marker_color_changed;
    }


    var circleMarker;
    if (home.city.includes('MDU')) {
        var icon = L.divIcon({
            className: 'custom-div-icon',
            html: "<div style='text-align: center; color: #ff7800; font-size: 24px;margin-top: 40px;'><i class='bi bi-house-fill'></i></div>",
            iconSize: [30, 42], // Adjust as needed
            iconAnchor: [15, 42] // Adjust as needed
        });

        circleMarker = L.marker([lat, lon], { icon: icon });
    } else {
        circleMarker = L.circleMarker([lat, lon], {
            color: markerColor,
            fillOpacity: 0.5
        });
    }

    circleMarker.bindPopup(getPopupContent(home), {
        className: "modernmarkerpopup",
    });


    circleMarker.homeid = home['homeid']; // Store homeid in marker for later use
    circleMarker.originalColor = markerColor; // Store original color for later use
    circleMarker.scan4 = home;

    circleMarker.originalLat = lat;
    circleMarker.originalLon = lon;

    circleMarker.on(
        "popupopen",
        (function (home) {
            return function (e) {
                $(".infoplate").on("click", function () {
                    infoBoardLoadData(home);
                });
            };
        })(home)
    );
    circleMarker.on(
        "dblclick",
        (function (home) {
            return function (e) {
                $('.infoplate').click();
            };
        })(home)
    );



    circleMarker.on('click', function () {
        onMarkerClick(circleMarker, home);

        if (!dispersedMarkers.includes(circleMarker)) {
            resetUniqueMarkers();
            disperseUniqueMarkers(circleMarker);
        }
    });



    circleMarker.on('popupclose', function () {

    });

    markers.push(circleMarker);
    markerGroup.push(circleMarker);
    layerGroup.addLayer(circleMarker);
}



function disperseUniqueMarkers(markerClicked) {
    dispersedMarkers = [];
    console.log('dispersedMarkers empty', dispersedMarkers);

    markers.forEach(marker => {
        if (marker !== markerClicked &&
            marker.originalLat === markerClicked.originalLat &&
            marker.originalLon === markerClicked.originalLon) {
            console.log("Adding marker to dispersedMarkers", marker);
            console.log("Name", marker.scan4.lastname);
            dispersedMarkers.push(marker);
        }
    });

    // Including the clicked marker
    dispersedMarkers.push(markerClicked);
    console.log('dispersedMarkers ' + dispersedMarkers.length, dispersedMarkers);

    if (dispersedMarkers.length > 1) {
        let markerCount = dispersedMarkers.length;
        let radius = 0.00010;
        if (markerCount > 6) {
            radius += 0.00002 * (markerCount - 6);
        }

        let angleStep = 360 / markerCount;
        let offsetAngle = 10; // setting a fixed offset to create a more "circular" pattern

        dispersedMarkers.forEach((marker, index) => {
            let angle = index * angleStep + offsetAngle;
            let dx = radius * Math.cos(angle * (Math.PI / 180));
            let dy = radius * Math.sin(angle * (Math.PI / 180));

            let newLat = marker.originalLat + dy;
            let newLon = marker.originalLon + dx;

            let startLat = marker.getLatLng().lat;
            let startLon = marker.getLatLng().lng;
            let startTime = null;

            function animateMarker(time) {
                if (!startTime) startTime = time;
                let progress = (time - startTime) / 200; // 200ms duration
                if (progress > 1) progress = 1;

                marker.setLatLng([
                    startLat + (newLat - startLat) * progress,
                    startLon + (newLon - startLon) * progress
                ]);

                // Draw line from original position to the new position
                let polyline = L.polyline([
                    [marker.originalLat, marker.originalLon],
                    [startLat + (newLat - startLat) * progress, startLon + (newLon - startLon) * progress]
                ], {
                    color: 'grey',
                    weight: 1
                }).addTo(window.map);

                // Store the polyline reference in global array
                dispersedLines.push(polyline);

                if (progress < 1) {
                    requestAnimationFrame(animateMarker);
                }
            }

            requestAnimationFrame(animateMarker);
        });
        currentAktivMarker = markerClicked;
    }
}




function resetUniqueMarkers() {
    console.log('dispersedMarkers', dispersedMarkers);
    if (dispersedMarkers.length > 1) {
        dispersedMarkers.forEach(marker => {
            marker.setLatLng([marker.originalLat, marker.originalLon]);
        });
        // Remove lines here
        dispersedLines.forEach(line => {
            line.remove();
        });
        dispersedLines = []; // Reset the lines array
    }
}



// Function to refetch data for a marker and re-add it to the map
async function refetchMarker(homeid) {
    // Delete the existing marker

    $.ajax({
        method: "POST",
        url: "view/load/map_load.php",
        data: {
            func: "fetchHomeid",
            homeid: homeid,
        },
    }).done(function (response) {
        console.log('refetchMarker')
        console.log(response)

        const home = JSON.parse(response)[0];
        console.log(home)



        // Recreate the marker
        if (home.scan4_status === 'OPEN') {
            console.log('append new marker to OPEN')
            createMarker(home, marker_color_open, openMarkers, openLayerGroup, 'open');
        } else if (home.scan4_status === 'PLANNED') {
            console.log('append new marker to PLANNED')
            createMarker(home, marker_color_planned, plannedMarkers, plannedLayerGroup, 'planned');
        }
        console.log('@@@@@@@@@@@@@@@@@@@@')
        console.log(markers)
        console.log(currentAktivMarker);
        const marker = markers.find(marker => marker.homeid === homeid);
        if (marker) {
            console.log("Marker found:", marker);
        } else {
            console.log("Marker not found");
        }
        currentAktivMarker = marker;
        console.log(currentAktivMarker);

    });


}




function markerGetColor(colorState) {
    if (colorState === 'markerPlanned') {
        color = marker_color_planned;
    } else if (colorState === 'markerOpen') {
        color = marker_color_open;
    } else if (colorState === 'markerChanged') {
        color = marker_color_changed;
    }

    return color;

}

// -----------------------------------------------------------------------
// loads all event markers to the map but hidden to work in background
function markers_init_calendar() {
    // Check if eventMarkers array is not empty
    if (eventMarkers.length > 0) {
        // Iterate over the array and remove each marker from the map
        eventMarkers.forEach(function (marker) {
            map.removeLayer(marker);
        });

        // Empty the array
        eventMarkers = [];
    }
    Object.keys(json_calendar).forEach(function (username) {
        // Iterate through each event in the user's array of events
        json_calendar[username].forEach(function (event) {
            // Retrieve the latitude and longitude of the event
            var lat = event.lat;
            var lon = event.lon;

            // Check if latitude and longitude are not empty or null
            if (lat && lon) {
                // Create a circle marker with the specified coordinates and pink color
                var marker = L.circleMarker([lat, lon], {
                    color: "#d200ff",
                    scan4: {
                        eventObj: event // Nest the event object under marker.options.scan4.eventObj
                    },
                    opacity: 0,
                    fillOpacity: 0,
                    interactive: false
                }).addTo(map);

                // Create a string with the event information
                var popupContent = "<b>Title:</b> " + event.title +
                    "<br><b>Start Time:</b> " + event.start_time +
                    "<br><b>End Time:</b> " + event.end_time +
                    "<br><b>Description:</b> " + event.description +
                    "<br><b>Location:</b> " + event.location +
                    "<br><b>User:</b> " + event.user_name;

                // Bind the popup with the event information to the marker
                marker.bindPopup(popupContent);

                marker.on("click", function (e) {
                    console.log('marker.options.scan4.eventObj');
                    console.log(marker.options.scan4.eventObj);
                });
                eventMarkers.push(marker);
            }
        });
    });
}

// -----------------------------------------------------------------------
// string convert 2023-05-26 to 26.05.23
function convertDateFormat(dateString) {
    // console.log('convert date ' + dateString)
    var parts = dateString.split("-");
    var year = parts[0].slice(-2);
    var month = parts[1];
    var day = parts[2];

    var formattedDate = day + "." + month + "." + year;
    return formattedDate;
}
// -----------------------------------------------------------------------
// on marker click on "planen / cancel" cancel the current appointment
$(document).on("click", ".planen_move.showed", function () {

    const marker = currentAktivMarker;
    if (!marker.scan4.hausbegeher) {
        return;
    }

    $.confirm({
        backgroundDismiss: true,
        theme: "dark",
        type: "orange",
        title: 'Termin verschieben <i class="ri-error-warning-fill"></i>',
        content:
            `Sicher das dieser Termin verschoben werden soll? 
            <br><i class="ri-calendar-line"></i> ${convertDateFormat(marker.scan4.date)} um ${marker.scan4.time} 
            <br><i class="ri-user-3-line"></i> ${marker.scan4.lastname}, ${marker.scan4.firstname}
            <br><i class="ri-home-3-line"></i> ${marker.scan4.street} ${marker.scan4.streetnumber}${marker.scan4.streetnumberadd} <br><br>` +
            `<select id="cancelReason" class="form-select form-select-sm form-control" aria-label=".form-select-sm example">
                <option value="0"selected disabled>------------</option>
                <option value="1">Kundenwunsch</option>
                <option value="2">Scan4 Wunsch</option>
             </select>
             <div style="margin: 5px;"></div>` +
            '<textarea id="eventComment" name="eventComment" placeholder="Verschiebe Grund" rows="4" cols="33" class="form-control" style="height: 80px; overflow-y: auto;" maxlength="500">',
        buttons: {
            confirm: {
                text: "Ja, Termin verschieben",
                btnClass: "btn-blue",
                keys: ["enter"],
                action: function () {
                    const eventComment = this.$content.find('#eventComment').val();
                    const eventReason = this.$content.find('#cancelReason option:selected').text();
                    var eventData = {
                        uid: marker.scan4.uid,
                        comment: eventComment,
                        homeid: marker.scan4.homeid,
                        reason: eventReason,
                    };
                    planUserEvent.call($('.infoboardaction.planen').get(0), skipChecks = true, isMoved = true);

                },
                isDisabled: true, // Initially disable the confirm button
            },
            cancel: {
                text: "Abbruch",
                //btnClass: "btn-red",
                keys: ["esc"],
                action: function () {
                    //  $.alert('Something else?');
                },
            },
        },
        onOpen: function () {
            var self = this;

            // validate the input and select to enable the confirm btn if select != 0 and comment is more then 5 chars
            function validateInputs() {
                var dropdownVal = +self.$content.find('#cancelReason').val(); // Convert string to number
                var textareaVal = self.$content.find('#eventComment').val();

                if (dropdownVal !== 0 && textareaVal.length >= 5) {
                    self.buttons.confirm.enable();
                } else {
                    self.buttons.confirm.disable();
                }
            }
            this.$content.find('#cancelReason').change(validateInputs);
            this.$content.find('#eventComment').keyup(validateInputs);
        },
    });




});
// -----------------------------------------------------------------------
// on marker click on "planen / cancel" cancel the current appointment
$(document).on("click", ".planen_cancel.showed", function () {

    const marker = currentAktivMarker;
    if (!marker.scan4.hausbegeher) {
        return;
    }

    $.confirm({
        backgroundDismiss: true,
        theme: "dark",
        type: "red",
        title: 'Termin stornieren <i class="ri-error-warning-fill"></i>',
        content:
            `Sicher das du diesen Termin stornieren möchtest? 
            <br><i class="ri-calendar-line"></i> ${convertDateFormat(marker.scan4.date)} um ${marker.scan4.time} 
            <br><i class="ri-user-3-line"></i> ${marker.scan4.lastname}, ${marker.scan4.firstname}
            <br><i class="ri-home-3-line"></i> ${marker.scan4.street} ${marker.scan4.streetnumber}${marker.scan4.streetnumberadd} <br><br>` +
            `<select id="cancelReason" class="form-select form-select-sm form-control" aria-label=".form-select-sm example">
                <option value="0"selected disabled>------------</option>
                <option value="1">Kundenwunsch</option>
                <option value="2">Scan4 Wunsch</option>
             </select>
             <div style="margin: 5px;"></div>` +
            '<textarea id="eventComment" name="eventComment" placeholder="Storno Grund" rows="4" cols="33" class="form-control" style="height: 80px; overflow-y: auto;" maxlength="500">',
        buttons: {
            confirm: {
                text: "Ja, Termin stornieren",
                btnClass: "btn-blue",
                keys: ["enter"],
                action: function () {
                    const eventComment = this.$content.find('#eventComment').val();
                    const eventReason = this.$content.find('#cancelReason option:selected').text();
                    var eventData = {
                        uid: marker.scan4.uid,
                        comment: eventComment,
                        homeid: marker.scan4.homeid,
                        reason: eventReason,
                    };

                    let json = JSON.stringify({ eventData });
                    $.ajax({
                        method: "POST",
                        url: "view/load/map_load.php",
                        data: {
                            func: "cancel_appointment",
                            data: json,
                        },
                    }).done(function (response) {
                        console.log('cancel_appointment response:')
                        console.log(response)

                        if (response.includes('FAILED!')) {
                            let errorCode = null;
                            if (response.includes('ErrorCode')) {
                                errorCode = response.match(/{ErrorCode:([^}]*)}/)[1];
                            }
                            confirmBox_fail('Termin Storno', errorCode); // displays a sad confirm box with fail message
                        } else {

                            refetchMarker(marker.scan4.homeid);
                            infoBoardLoadData(currentAktivMarker); // reloads the current marker
                            confirmBox_success('Termin Storno', 'Termin wurde storniert.', 'doublecheck') // displays a confirm box with success message
                        }

                    });
                },
                isDisabled: true, // Initially disable the confirm button
            },
            cancel: {
                text: "Abbruch",
                //btnClass: "btn-red",
                keys: ["esc"],
                action: function () {
                    //  $.alert('Something else?');
                },
            },
        },
        onOpen: function () {
            var self = this;

            // validate the input and select to enable the confirm btn if select != 0 and comment is more then 5 chars
            function validateInputs() {
                var dropdownVal = +self.$content.find('#cancelReason').val(); // Convert string to number
                var textareaVal = self.$content.find('#eventComment').val();

                if (dropdownVal !== 0 && textareaVal.length >= 5) {
                    self.buttons.confirm.enable();
                } else {
                    self.buttons.confirm.disable();
                }
            }
            this.$content.find('#cancelReason').change(validateInputs);
            this.$content.find('#eventComment').keyup(validateInputs);
        },
    });




});
// -----------------------------------------------------------------------
// on marker click on "planen" button, fetch all planned markers in cirle and calculate the distance
function planUserEvent(skipChecks = false, isMoved = false) {
    if (!skipChecks) { // if its triggered by a user click, do this checks. If its called intern, skip that
        if ($(this).hasClass('waiting')) { // prevent double clicks
            return;
        }

        const marker = currentAktivMarker;
        if (
            marker &&
            marker.scan4 &&
            marker.scan4.scan4_status &&
            (marker.scan4.scan4_status === 'PLANNED' || marker.scan4.scan4_status === 'OVERDUE')
        ) {
            // exit if the appointment is in the past
            let markerDatetime = new Date(`${marker.scan4.date} ${marker.scan4.time}`.replace(/-/g, "/"));
            if (!hasPerm(24)) {
                console.log('Appointment user has not Perm24')
                if (markerDatetime < new Date()) {
                    console.log('Appointment date is in the past return false')
                    return false;
                } else {
                    console.log('Appointment check passed')
                }
            } else {
                console.log('Appointment user has Perm24')
            }

            $('.planen_cancel, .planen_move').addClass('showed');
            return;
        }
    }

    let clicked_button_html = $(this).html();
    let clicked_button = $(this);
    $(clicked_button).html('');
    $(clicked_button).addClass('waiting')
    refresh_calendar().then(function (result) {
        if (result) {
            const marker = currentAktivMarker;
            const startRadius = 6000;
            const maxRadius = 50000; // equals around 50km
            const maxHausbegeher = 6;

            let hausbegeherInCircle = [];
            let markersInFinalCircle = [];
            let radius = startRadius;

            // If a circle already exists, remove it from the map
            if (markerCircle) {
                map.removeLayer(markerCircle);
            }
            var loop = 0;
            while (radius <= maxRadius && hausbegeherInCircle.length < maxHausbegeher) {
                loop++;

                markerCircle = L.circle(marker.getLatLng(), {
                    color: "red",
                    fillColor: "#f03",
                    fillOpacity: 0,
                    opacity: 0,
                    radius: radius,
                    interactive: false,
                }).addTo(map);

                let eventMarkersInCircle = eventMarkers
                    .filter((marker) =>
                        markerCircle.getBounds().contains(marker.getLatLng())
                    );

                eventMarkersInCircle.forEach((marker) => {
                    // console.log('eventMarkersInCircle > marker')
                    // console.log(marker)
                    let hausbegeherName = marker.options.scan4.eventObj.user_name;

                    if (!hausbegeherInCircle.includes(hausbegeherName)) {
                        hausbegeherInCircle.push(hausbegeherName);
                    }
                });
                // get the current count of the hausbegeher array to check if maxHausbegehr is reached
                hausbegeherInCircle = hausbegeherInCircle.slice(0, maxHausbegeher);

                if (hausbegeherInCircle.length >= maxHausbegeher || radius == maxRadius) {
                    markersInFinalCircle = [...eventMarkersInCircle];
                }

                if (radius < maxRadius) {
                    radius = Math.min(radius + 5000, maxRadius); // Ensure radius doesn't exceed maxRadius
                } else {
                    break; // If radius has already reached maxRadius, break the loop
                }
            }
            console.log("-------------------------------");
            console.log("loop" + loop + " radius " + radius);
            console.log(markersInFinalCircle);
            console.log("Markers in final circle: " + markersInFinalCircle.length);
            console.log(hausbegeherInCircle);
            console.log("Hausbegeher in circle: " + hausbegeherInCircle.length);

            if (hausbegeherInCircle.length == 0) {
                console.log("No marker found");
            } else {
                // hide all hausbegher which are not in the circle
                $(".multiboxwrapper.marginbox.userbox").each(function () {
                    if (!hausbegeherInCircle.includes(this.id)) {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }
                });
            }
            hausbegeherInCircle.forEach((user) => {
                create_temp_event(user, markersInFinalCircle, isMoved);
            });
            let index = 0;
            const apiUrl = "https://services.scan4-gmbh.de/route";
            console.log("index " + index);
            setTimeout(function () { // timeout for smother animation
                $(clicked_button).html(clicked_button_html);
                $(clicked_button).removeClass('waiting')
            }, 1000); // delay of 1000ms

        }
    }).catch(function (error) {
        console.error('An error occurred:', error);
        $(clicked_button).html(clicked_button_html);
        $(clicked_button).removeClass('waiting')
    });

}
$(document).on("click", ".planbarplate, .infoboardaction.planen", function () {
    planUserEvent.call(this); // stick the button to the function
});




// -----------------------------------------------------------------------
// fix userplates width
function userplates_fix() {
    // fix widht of plates, plateswrapper
    var maxWidth = 0;
    $(".multiboxwrapper.marginbox.userbox").each(function () {
        var outerWidth = $(this).outerWidth();
        if (outerWidth > maxWidth) {
            maxWidth = outerWidth;
        }
    });
    $(".multiboxwrapper.marginbox.userbox").css("width", maxWidth);
    $("#userplates").css("width", maxWidth);
    $("#map_userplateswrapper").css({ width: maxWidth, "max-width": maxWidth });

    // fix progressbars
    var maxWidth = 0;
    $("span.multiboxtypo.progressbarwrapper.small").each(function () {
        var width = $(this).outerWidth();
        if (width > maxWidth) {
            maxWidth = width;
        }
    });
    $("span.multiboxtypo.progressbarwrapper.small").css("width", maxWidth + "px");

    // sort the plates by current kw lowest to highest
    var wrapper = $('#plateswrapperinner');
    var boxes = wrapper.children('.userbox').detach();

    boxes.sort(function (a, b) {
        var an = parseInt($(a).find('.kwslotsopen').first().text(), 10),
            bn = parseInt($(b).find('.kwslotsopen').first().text(), 10);

        return bn - an;
    });

    boxes.appendTo(wrapper);

    userplates_hide();

}

function userplates_hide() {
    console.log('hide is called now');
    $('#plateswrapperinner > .userbox').each(function () {
        var username = $(this).attr('id');
        if (namesToHide.includes(username)) {
            $(this).hide();
        }
    });
}

// -----------------------------------------------------------------------
// parse the calendar json and creates a calendar view for each user
function calendar_init() {
    console.log('new json_calendar')
    console.log(json_calendar)
    $('.abs_overlay').empty(); // clear all calendars before loading data into it
    Object.entries(json_calendar).forEach(([username, events]) => {
        if (username !== "") {
            $(".hiddenstats").each(function () {
                const id = $(this).attr("id");
                if (username === id) {
                    const overlay = $(this).find(".abs_overlay");
                    overlay.each(function () {
                        const dateId = $(this).attr("id");
                        events.forEach((event) => {
                            const startDateTimeString = event.start_time;
                            const endDateTimeString = event.end_time;
                            const [eventdate, startTime] = startDateTimeString.split(" ");
                            const [, endTime] = endDateTimeString.split(" ");

                            if (dateId === eventdate) {
                                const [startHours, startMinutes] = startTime.split(":");
                                const [endHours, endMinutes] = endTime.split(":");

                                const startTotalHalfHours =
                                    (parseInt(startHours, 10) - 7) * 2 +
                                    parseInt(startMinutes, 10) / 30;
                                const endTotalHalfHours =
                                    (parseInt(endHours, 10) - 7) * 2 +
                                    parseInt(endMinutes, 10) / 30;

                                const topOffset = startTotalHalfHours * (calendarView_hourSlotHeight / 2);
                                const eventHeight =
                                    (endTotalHalfHours - startTotalHalfHours) *
                                    (calendarView_hourSlotHeight / 2);


                                var eventDiv = $("<div>")
                                    .addClass("calevent")
                                    .css({
                                        top: topOffset + "px",
                                        height: eventHeight + "px",
                                        position: "absolute",
                                    })
                                    .attr({
                                        eventId: event.event_id,
                                        homeid: event.homeid,
                                        start_time: event.start_time,
                                        end_time: event.end_time,
                                        location: event.location,
                                        lat: event.lat,
                                        lon: event.lon,
                                        creator: event.creator,
                                    })
                                    .text(event.title);


                                if (event.homeid === null || event.homeid === undefined) {
                                    eventDiv.addClass("userEvent");
                                }
                                if (hasPerm(2) && event.homeid === null) {
                                    eventDiv.addClass("userControleEvent");
                                }
                                // check if event start time is in the past
                                const currentDateTime = new Date();
                                const eventDateTime = new Date(eventdate + " " + startTime);

                                // if event is in the past
                                if (eventDateTime < currentDateTime) {
                                    eventDiv.addClass("past");
                                }


                                var tooltipDiv = $("#eventTooltip");

                                eventDiv.hover(
                                    function (e) {
                                        // show tooltip
                                        var event = $(this).data('event'); // Retrieve the event data

                                        tooltipDiv.html(`
                                            Title: ${event.title}<br>
                                            Home ID: ${event.homeid}<br>
                                            Location: ${event.location}<br>
                                            Start Time: ${event.start_time}<br>
                                            End Time: ${event.end_time}<br>
                                            Creator: ${event.creator}
                                        `)
                                            .css({
                                                position: 'fixed',
                                                background: 'white',
                                                padding: '5px',
                                                border: '1px solid black',
                                                borderRadius: '4px',
                                                zIndex: 9999999,
                                                display: 'block',
                                                whiteSpace: 'nowrap',
                                                top: e.clientY + 'px',
                                                left: (e.clientX + 25) + 'px'
                                            });
                                    },
                                    function () {
                                        // hide tooltip
                                        tooltipDiv.css('display', 'none');
                                    }
                                );

                                // Store the event data on the eventDiv so it can be accessed in the hover function
                                eventDiv.data('event', event);







                                var eventDiv =
                                    eventDiv.css("cursor", "pointer");
                                eventDiv.click(function () {

                                    var lat = parseFloat($(this).attr("lat"));
                                    var lon = parseFloat($(this).attr("lon"));
                                    var homeid = $(this).attr("homeid");

                                    $(".slideclosebtn").click(); // close slider
                                    map.flyTo([lat, lon], 16, { duration: mapFlyDuration });
                                    map.once('moveend', function () {
                                        setTimeout(function () {
                                            for (var i = 0; i < plannedMarkers.length; i++) {
                                                if (plannedMarkers[i].homeid == homeid) {
                                                    plannedMarkers[i].fire('click'); // fire click event on the matching marker
                                                    break;
                                                }
                                            }
                                        }, 100); // delay of 1000ms
                                    });

                                });

                                // append link and icon to eventDiv if homeid found
                                if (event.homeid) {
                                    var eventLink = $("<a>")
                                        .attr(
                                            "href",
                                            "/route.php?view=phonerapp?city=?homeid=" + event.homeid
                                        )
                                        .attr("target", "_blank")
                                        .css("z-index", "1");

                                    var icon = $("<i>").addClass("ri-external-link-line");
                                    eventLink.append(icon);
                                    eventDiv.append(eventLink);
                                }
                                if (event.creator === currentUser) {
                                    var eventStar = $("<div>").addClass("starEvent");
                                    var icon = $("<i>").addClass("ri-checkbox-blank-circle-fill");
                                    eventStar.append(icon);
                                    eventDiv.append(eventStar);
                                }

                                $(this).append(eventDiv);
                            }
                        });
                    });
                }
            });
        }
    });



}


function findOpenSlots(events, startTime, endTime) {
    let openSlots = [];
    let currentTime = new Date(startTime);

    events.forEach((event, index) => {
        let eventStart = new Date(event.start_time);
        let eventEnd = new Date(event.end_time);

        // If there's a gap of at least 30 minutes between the current time and the start of the event, add it to the open slots
        while (eventStart.getTime() - currentTime.getTime() >= 30 * 60 * 1000) {
            let endMinutes = currentTime.getMinutes() + 30;
            let endHours = currentTime.getHours();
            if (endMinutes >= 60) {
                endMinutes -= 60;
                endHours += 1;
            }
            let prevLatLon = index > 0 ? `${events[index - 1].lat},${events[index - 1].lon}` : null;
            let nextLatLon = index < events.length - 1 ? `${events[index + 1].lat},${events[index + 1].lon}` : null;

            openSlots.push({
                start_time: `${currentTime.getHours().toString().padStart(2, "0")}:${currentTime.getMinutes().toString().padStart(2, "0")}`,
                end_time: `${endHours.toString().padStart(2, "0")}:${endMinutes.toString().padStart(2, "0")}`,
                prev_homeid: index > 0 ? events[index - 1].homeid : null,
                next_homeid: index < events.length - 1 ? events[index + 1].homeid : null,
                prev_latlon: prevLatLon,
                next_latlon: nextLatLon,
            });
            currentTime.setTime(currentTime.getTime() + 30 * 60 * 1000);
        }

        // Set the current time to the end of the event
        currentTime = eventEnd;
    });

    // If there's a gap of at least 30 minutes between the end of the last event and the end time, add it to the open slots
    while (
        new Date(endTime).getTime() - currentTime.getTime() >=
        30 * 60 * 1000
    ) {
        let endMinutes = currentTime.getMinutes() + 30;
        let endHours = currentTime.getHours();
        if (endMinutes >= 60) {
            endMinutes -= 60;
            endHours += 1;
        }
        let prevLatLon = events.length > 0 ? `${events[events.length - 1].lat},${events[events.length - 1].lon}` : null;

        openSlots.push({
            start_time: `${currentTime
                .getHours()
                .toString()
                .padStart(2, "0")}:${currentTime
                    .getMinutes()
                    .toString()
                    .padStart(2, "0")}`,
            end_time: `${endHours.toString().padStart(2, "0")}:${endMinutes
                .toString()
                .padStart(2, "0")}`,
            prev_homeid: events.length > 0 ? events[events.length - 1].homeid : null, // homeId of the event before the gap
            next_homeid: null, // No event after the gap
            prev_latlon: prevLatLon,
            next_latlon: null,
        });
        currentTime.setTime(currentTime.getTime() + 30 * 60 * 1000);
    }

    return openSlots;
}



function getAllOpenSlots() {
    console.time("findOpenSlots");
    // Define the start and end times for each day
    let startHour = 5;
    let endHour = 21;

    // Get today's date
    let today = new Date();
    today.setHours(0, 0, 0, 0);

    // Loop over all users
    for (let user in json_calendar) {
        let events = json_calendar[user];

        OpenSlots[user] = [];

        // Check for open slots for the next 7 days
        for (let i = 0; i < 120; i++) {
            let day = new Date(today.getTime() + i * 24 * 60 * 60 * 1000);

            // Skip weekends
            if (day.getDay() === 0) continue;

            let startTime = new Date(day.getTime());
            startTime.setHours(startHour);
            let endTime = new Date(day.getTime());
            endTime.setHours(endHour);

            // Filter the events for the current day
            let dayEvents = events.filter((event) => {
                let eventStart = new Date(event.start_time);
                let eventEnd = new Date(event.end_time);
                return eventStart >= startTime
                    && eventEnd <= endTime
                    && event.lat !== null
                    && event.lat.toString().length >= 5; // ignore events without valid lat
            });

            let openSlots = findOpenSlots(dayEvents, startTime, endTime);

            OpenSlots[user].push({
                date: day.toLocaleDateString(),
                openSlots: openSlots,
            });
        }
    }
    console.timeEnd("findOpenSlots");
    console.log(OpenSlots);
}


function create_temp_event(username, markersInFinalCircle, isMoved = false) {
    // get the open slots for the user
    const openSlots = OpenSlots[username];
    console.log('openSlots for ' + username)
    console.log(openSlots)
    console.log("markeris");
    console.log(currentAktivMarker);

    // get the latlon from the markers in the final circle
    const latlonsInFinalCircle = markersInFinalCircle.map(
        (marker) => `${marker.getLatLng().lat},${marker.getLatLng().lng}`
    );
    console.log('latlonsInFinalCircle')
    console.log(latlonsInFinalCircle)

    const currentDate = new Date();
    // for each open slot, create a temporary event
    openSlots.forEach((slot) => {
        slot.openSlots.forEach((openSlot) => {
            const [day, month, year] = slot.date.split(".");
            const formattedSlotDate = `${year}-${month.padStart(
                2,
                "0"
            )}-${day.padStart(2, "0")}`;
            //console.log("formattedSlotDate" + formattedSlotDate);

            const slotDateTimeStart = new Date(
                `${formattedSlotDate}T${openSlot.start_time}`
            );
            const slotDateTimeEnd = new Date(
                `${formattedSlotDate}T${openSlot.end_time}`
            );
            // check if the slot is in the future
            if (slotDateTimeStart > currentDate && slotDateTimeEnd > currentDate) {
                // check if the open slot's prev_latlon or next_latlon is in the final circle
                if (
                    (openSlot.prev_latlon &&
                        latlonsInFinalCircle.includes(openSlot.prev_latlon)) ||
                    (openSlot.next_latlon &&
                        latlonsInFinalCircle.includes(openSlot.next_latlon))
                ) {
                    const startDateTimeString = openSlot.start_time;
                    const endDateTimeString = openSlot.end_time;
                    const [startHours, startMinutes] = startDateTimeString.split(":");
                    const [endHours, endMinutes] = endDateTimeString.split(":");

                    const startTotalHalfHours =
                        (parseInt(startHours, 10) - 7) * 2 +
                        parseInt(startMinutes, 10) / 30;
                    const endTotalHalfHours =
                        (parseInt(endHours, 10) - 7) * 2 + parseInt(endMinutes, 10) / 30;

                    const topOffset = startTotalHalfHours * (calendarView_hourSlotHeight / 2);
                    const eventHeight =
                        (endTotalHalfHours - startTotalHalfHours) * (calendarView_hourSlotHeight / 2);

                    var slotData = {
                        start_time: openSlot.start_time,
                        end_time: openSlot.end_time,
                        prev_latlon: openSlot.prev_latlon,
                        next_latlon: openSlot.next_latlon,
                        username: username,
                        date: formattedSlotDate,
                        isMoved: isMoved
                    }
                    var tempEventDiv = $("<div>")
                        .addClass("calevent tempEvent")
                        .css({
                            top: topOffset + "px",
                            height: eventHeight + "px",
                            position: "absolute",
                            background: "lightgrey",
                        })
                        .data("slotData", slotData);
                    // find the correct user and date
                    $(".hiddenstats").each(function () {
                        const id = $(this).attr("id");
                        if (username === id) {
                            $(this)
                                .find(".abs_overlay")
                                .each(function () {
                                    const dateId = $(this).attr("id");
                                    if (dateId === formattedSlotDate) {
                                        // append the temporary event to the calendar view for the user
                                        $(this).append(tempEventDiv);
                                    }
                                });
                        }
                    });

                    // get the driving time from currentAktivMarker to prev_latlon
                    if (openSlot.prev_latlon) {
                        const apiUrl = "https://services.scan4-gmbh.de/route";
                        const startPoint = `${currentAktivMarker.getLatLng().lat},${currentAktivMarker.getLatLng().lng}`;
                        const endPoint = openSlot.prev_latlon;
                        const queryString =
                            "point=" +
                            encodeURIComponent(startPoint) +
                            "&point=" +
                            encodeURIComponent(endPoint) +
                            "&profile=car&layer=OpenStreetMap";
                        const finalUrl = apiUrl + "?" + queryString;

                        // call graphopper and get data
                        $.get(finalUrl, function (data) {
                            const drivingTimeMinutes = Math.floor(
                                data.paths[0].time / 60000
                            );
                            slotData.prevDrivingTime = drivingTimeMinutes;
                            let backgroundColor = getBackgroundColor(drivingTimeMinutes);
                            const drivingTimeSpan = $("<div>")
                                .addClass("fromEvent")
                                .css("background", backgroundColor)
                                .text(drivingTimeMinutes + "m");
                            tempEventDiv.append(drivingTimeSpan);
                            tempEventDiv.data("slotData", slotData);

                            const weekNumber = getWeekNumber(formattedSlotDate);
                            saveDrivingTimeToRecords(
                                username,
                                weekNumber,
                                parseFloat(drivingTimeMinutes)
                            );
                        });
                    }
                    if (openSlot.next_latlon) {
                        const apiUrl = "https://services.scan4-gmbh.de/route";
                        const startPoint = `${currentAktivMarker.getLatLng().lat},${currentAktivMarker.getLatLng().lng}`;
                        const endPoint = openSlot.next_latlon;
                        const queryString =
                            "point=" +
                            encodeURIComponent(startPoint) +
                            "&point=" +
                            encodeURIComponent(endPoint) +
                            "&profile=car&layer=OpenStreetMap";
                        const finalUrl = apiUrl + "?" + queryString;

                        // call graphopper and get data
                        $.get(finalUrl, function (data) {
                            const drivingTimeMinutes = Math.floor(
                                data.paths[0].time / 60000
                            );
                            slotData.nextDrivingTime = drivingTimeMinutes;
                            //console.log("driving time minutes " + drivingTimeMinutes);
                            let backgroundColor = getBackgroundColor(drivingTimeMinutes);
                            const drivingTimeSpan = $("<div>")
                                .addClass("toEvent")
                                .css("background", backgroundColor)
                                .text(drivingTimeMinutes + "m");
                            tempEventDiv.append(drivingTimeSpan);
                            tempEventDiv.data("slotData", slotData);
                        });
                    }
                }
            }
        });
    });

    console.log("userWeekRecords");
    console.log(userWeekRecords);

    setTimeout(() => {
        // this delay is need to w8 till the data is apllied to get the correct data
        applyStyleToUserBoxes();
    }, 500);
}


function getBackgroundColor(drivingTimeMinutes) {
    let backgroundColor;
    if (drivingTimeMinutes < 5) {
        backgroundColor = "#58e43a";
    } else if (drivingTimeMinutes < 10) {
        backgroundColor = "#169f49";
    } else if (drivingTimeMinutes < 15) {
        backgroundColor = "#3054d5";
    } else if (drivingTimeMinutes < 30) {
        backgroundColor = "#ff8a8a"; // red
    } else {
        backgroundColor = "#c12525"; // darkred
    }

    return backgroundColor;
}

function saveDrivingTimeToRecords(username, weekNumber, drivingTimeMinutes) {
    if (!username) {
        console.warn("Attempted to save driving time record with empty username");
        return;
    }
    if (!userWeekRecords[username]) {
        userWeekRecords[username] = {};
    }
    if (!userWeekRecords[username][weekNumber]) {
        userWeekRecords[username][weekNumber] = drivingTimeMinutes; // set the driving time as it is the first one
    } else {
        // update value with a lower one
        userWeekRecords[username][weekNumber] = Math.min(
            userWeekRecords[username][weekNumber],
            drivingTimeMinutes
        );
    }
}

function getWeekNumber(dateString) {
    const date = new Date(dateString);
    const tempDate = new Date(date.valueOf());
    tempDate.setDate(tempDate.getDate() + 3 - ((tempDate.getDay() + 6) % 7));
    const yearStart = new Date(tempDate.getFullYear(), 0, 4);
    return (
        1 +
        Math.round(
            ((tempDate - yearStart) / 86400000 - 3 + ((yearStart.getDay() + 6) % 7)) /
            7
        )
    );
}

function applyStyleToUserBoxes() {
    $(".userbox").each(function () {
        let user = $(this).attr("id");
        if (user && user in userWeekRecords) {
            //console.log("user found");
            for (let week in userWeekRecords[user]) {
                let minTime = userWeekRecords[user][week];
                let backgroundColor = getBackgroundColor(minTime);
                // console.log("search " + user + " week " + week);
                // Find the corresponding DOM elements within this userbox and apply the styles
                $(this)
                    .find(`.calweekbtn:contains('kw${week}')`)
                    .addClass("highlighted");
                $(this)
                    .find(`.calweekbtn:contains('kw${week}')`)
                    .css("--highlight-color", backgroundColor);
            }
        }
    });
}


function createEventforUser(eventData) {
    const convertedDate = convertDateFormat(eventData.date);

    var prevPlaceholder = "";
    var nextPlaceholder = "";
    if (eventData.prevDrivingTime != undefined) {
        prevPlaceholder = eventData.prevDrivingTime;
    } else {
        prevPlaceholder = " - ";
    }
    if (eventData.nextDrivingTime != undefined) {
        nextPlaceholder = eventData.nextDrivingTime;
    } else {
        nextPlaceholder = " - ";
    }


    $.confirm({
        backgroundDismiss: true,
        theme: "dark",
        title: "Termin eintragen",
        content:
            'Soll dieser Termin gespeichert werden?<br><i class="ri-calendar-line"></i> ' +
            convertedDate +
            '<br><i class="ri-time-line"></i> ' +
            eventData.start_time +
            '<br><i class="ri-user-3-line"></i> ' +
            eventData.username +
            '<br><i class="ri-pin-distance-line"></i> Von ' +
            prevPlaceholder +
            "min" +
            '<br><i class="ri-pin-distance-line"></i> Zu ' +
            nextPlaceholder +
            'min<br>' +
            '<textarea id="eventComment" name="eventComment" placeholder="Kommentar zum Termin" rows="4" cols="33" class="form-control" style="height: 80px; overflow-y: auto;" maxlength="500">',
        buttons: {
            confirm: {
                text: "Bestätigen",
                btnClass: "btn-green",
                keys: ["enter"],
                action: function () {
                    var eventComment = this.$content.find('#eventComment').val();
                    eventData.eventComment = eventComment;
                    saveEventforUser(eventData);
                },
            },
            cancel: {
                text: "Abbruch",
                btnClass: "btn-red",
                keys: ["esc"],
                action: function () {
                    //  $.alert('Something else?');
                },
            },
        },
    });


    // --------------------------------------
    // sends the event data to the db, check if already exist and/or if the slot is free and save the event
    function saveEventforUser(eventData) {
        let json = JSON.stringify({ eventData });
        console.log(json)
        $.ajax({
            method: "POST",
            url: "view/load/map_load.php",
            data: {
                func: "saveEventforUser",
                data: json,
            },
        }).done(function (response) {
            console.log('Termin eintragung')
            console.log(response)

            if (response.includes('An entry already exists')) {
                confirmBox_fail('Termin eintragen'); // displays a sad confirm box with fail message
            } else if (response.includes('ErrorCode')) {
                let errorCode = null;
                if (response.includes('ErrorCode')) {
                    errorCode = response.match(/{ErrorCode:([^}]*)}/)[1];
                }
                if (eventData.isMoved === true) {
                    confirmBox_fail('Termin verschieben', errorCode); // displays a sad confirm box with fail message
                } else {
                    confirmBox_fail('Termin eintragen', errorCode); // displays a sad confirm box with fail message
                }

            } else {
                $('.calevent.tempEvent').remove(); // remove ALL tempevents from DOM
                refresh_calendar();
                infoBoardLoadData(currentAktivMarker); // reloads the current marker
                //currentAktivMarker.originalColor = marker_color_planned; // set the color to green


                refetchMarker(eventData.homeid); // reloads the marker from db to map
                currentAktivMarker.setStyle({ color: marker_color_planned });
                currentAktivMarker.markerColor = 'markerPlanned';

                if (eventData.isMoved === true) {
                    confirmBox_success('Termin verschieben', 'Termin wurde verschoben.', 'confetti')
                } else {
                    confirmBox_success('Termin eintragen', 'Termin wurde erstellt.', 'confetti')
                }
            }

        });

    }

}


// -----------------------------------------------------------------------
// store infoboard standart template for resets
const InfoBoardDataInitial = {
    customer_homeid: $("#customer_homeid").text(),
    customer_unit: $("#customer_unit").text(),
    customer_status_carrier: $("#customer_status_carrier").text(),
    customer_status_scan4: $("#customer_status_scan4").text(),
    customer_name: $("#customer_name").text(),
    customer_street: $("#customer_street").text(),
    customer_tel1: $("#customer_tel1").text(),
    customer_tel2: $("#customer_tel2").text(),
    customer_tel3: $("#customer_tel3").text(),
    customer_tel4: $("#customer_tel4").text(),
    customer_telSc1: $("#add_scan4_btn1").html(),
    customer_telSc2: $("#add_scan4_btn2").html(),
    customer_mail: $("#customer_mail").html(),
    customer_owner_phone1: $("#customer_owner_tel1").html(),
    customer_owner_phone2: $("#customer_owner_tel2").html(),
    customer_owner_mail: $("#customer_owner_mail").html(),
    customer_owner_name: $("#customer_owner_name").html(),
};
const InfoBoardColorInitial = {
    background: $(".infoboard_anrufhisto").css("background-color"),
    border: $(".infoboard_anrufhisto").css("border-color"),
    color: $(".infoboard_anrufhisto").css("color"),
};

function infoBoardLoadData(home) {
    if (home.hasOwnProperty('homeid')) { // check if a marker is passed, if not take the passed homeid
        var homeid = home.homeid;
    } else {
        var homeid = home;
    }
    console.log('homeid is: ' + homeid)

    console.time("infoBoardLoadData"); // Start the timer
    $.ajax({
        method: "POST",
        url: "view/load/map_load.php",
        data: {
            func: "load_homeid",
            homeid: homeid,
        },
    }).done(function (response) {
        var data = JSON.parse(response);
        console.log(data);
        infoBoardParseData(data);
    });
}


function confirmBox_fail(title = null, errorcode = null) {
    $.confirm({
        backgroundDismiss: true,
        theme: "dark",
        title: title,
        content: '<div style="text-align:center;font-size:40px;"><i class="ri-emotion-sad-line"></i></div>' +
            '<div style="text-align:center;">Das hat leider nicht geklappt.</div>' +
            `<div style="text-align: center; margin-top: 11px; font-size: 12px;"><i class="ri-information-line"></i> ErrorCode: ${errorcode}</div>`,
        buttons: {
            confirm: {
                text: "Ok",
                btnClass: "btn-blue",
                keys: ["enter"],
                action: function () {
                    //
                },
            },

        },
    });
}

function confirmBox_success(title = null, text = null, icon = null) {
    if (icon === null || icon === 'confetti') { icon = '<img style="max-height: 150px;" src="https://crm.scan4-gmbh.de/view/images/animation_confetti_blue.gif">'; }
    if (icon === 'check') { icon = '<img style="max-height: 150px;" src="https://crm.scan4-gmbh.de/view/images/icon_check_blueblue.png">'; }
    if (icon === 'doublecheck') { icon = '<i style="font-size:50px;" class="ri-check-double-line"></i>'; }
    $.confirm({
        backgroundDismiss: true,
        theme: "dark",
        title: title,
        content: `<div style="text-align:center;">${icon}</div>
            <div style="text-align:center;">${text}</div>`,
        buttons: {
            confirm: {
                text: "Ok",
                btnClass: "btn-blue",
                keys: ["enter"],
                action: function () {
                    //
                },
            },

        },
    });
}


function infoBoardParseData(parsedata) {
    console.log('infoBoardParseData(parsedata)', parsedata)
    const data = parsedata;
    infoBoardResetLayout();
    console.log("active marker", currentAktivMarker);
    if (
        data.data_calls[0] &&
        data.data_calls[0].call_date === getCurrentDate("YYYY-MM-DD") &&
        currentAktivMarker &&
        typeof currentAktivMarker.setStyle === 'function'
    ) {
        currentAktivMarker.setStyle({ color: marker_color_changed });
        currentAktivMarker.markerColor = 'markerChanged';
    }


    $("#infoboardwrapper").data("lat", data.data_homes.lat);
    $("#infoboardwrapper").data("lon", data.data_homes.lon);
    $("#customer_homeid").text(data.data_homes.homeid);
    if (data.data_homes.homeid === currentAktivMarker?.homeid) {
        $(".infoboard_contentparttop").css("background", "#fff");
        $("#infoboard_planbarwrapperbuttons").parent().show();
    } else {
        $("#infoboard_planbarwrapperbuttons").parent().hide();
    }
    if (!currentAktivMarker) {
        $("#infoboard_planbarwrapperbuttons").parent().hide();
    }

    $("#customer_unit").text(data.data_homes.unit);
    const carrierStatus = data.data_homes.hbg_status.toUpperCase();
    if (carrierStatus === "OPEN") {
        $("#customer_status_carrier").text(carrierStatus);
        $("#customer_status_carrier").css("background-color", color_open);
    } else if (carrierStatus === "PLANNED") {
        $("#customer_status_carrier").text(carrierStatus);
        $("#customer_status_carrier").css("background-color", color_planned);
    } else if (carrierStatus === "DONE") {
        $("#customer_status_carrier").text(carrierStatus);
        $("#customer_status_carrier").css("background-color", color_done);
    } else if (carrierStatus === "STOPPED") {
        $("#customer_status_carrier").text(carrierStatus);
        $("#customer_status_carrier").css("background-color", color_stopped);
    } else {
        $("#customer_status_carrier").text(carrierStatus);
        $("#customer_status_carrier").removeAttr("style");
        $("#customer_status_carrier").css("color", "#000"); // change text color to black to see the else statement
    }
    const scan4Status = data.data_homes.scan4_status.toUpperCase();
    if (scan4Status === "OPEN") {
        $("#customer_status_scan4").text(scan4Status);
        $("#customer_status_scan4").css({
            "background-color": color_open,
            "color": "#fff"
        });
    } else if (scan4Status === "PLANNED") {
        $("#customer_status_scan4").text(scan4Status);
        $("#customer_status_scan4").css({
            "background-color": color_planned,
            "color": "#fff"
        });
    } else if (scan4Status === "DONE") {
        $("#customer_status_scan4").text(scan4Status);
        $("#customer_status_scan4").css({
            "background-color": color_done,
            "color": "#fff"
        });
    } else if (scan4Status === "STOPPED") {
        $("#customer_status_scan4").text(scan4Status);
        $("#customer_status_scan4").css({
            "background-color": color_stopped,
            "color": "#fff"
        });
    } else if (scan4Status === "PENDING") {
        $("#customer_status_scan4").text(scan4Status);
        $("#customer_status_scan4").css({
            "background-color": color_pending,
            "color": "#fff"
        });
    } else {
        $("#customer_status_scan4").text(scan4Status);
        $("#customer_status_scan4").removeAttr("style");
        $("#customer_status_scan4").css("color", "#000"); // change text color to black to see the else statement
    }
    $("#customer_name").text(
        data.data_homes.lastname + ", " + data.data_homes.firstname
    );



    let street = data.data_homes.street;
    let streetNumber = data.data_homes.streetnumber;
    let streetNumberAdd = data.data_homes.streetnumberadd.toLowerCase();
    let plz = data.data_homes.plz;
    let city = data.data_homes.city;

    let isNumber = !isNaN(parseInt(streetNumberAdd));

    streetNumberAdd = isNumber ? '/' + streetNumberAdd : streetNumberAdd;

    const customerAddress = `${street} ${streetNumber}${streetNumberAdd}, ${plz} ${city}`;
    $("#customer_street").text(customerAddress);
    $("#customer_mail").text(data.data_homes.email)


    $("#customer_owner_name").text(data.data_homes.owner_name);
    if (data.data_homes.owner_phone1 && data.data_homes.owner_phone1.length > 4) {
        $("#customer_owner_tel1").html(
            '<a href="tel:' +
            data.data_homes.owner_phone1 +
            '" class="phone-link">' +
            data.data_homes.owner_phone1 +
            "</a>"
        );
    } else {
        $("#customer_owner_tel2");
    }
    if (data.data_homes.owner_phone2 && data.data_homes.owner_phone2.length > 4) {
        $("#customer_owner_tel2").html(
            '<a href="tel:' +
            data.data_homes.owner_phone2 +
            '" class="phone-link">' +
            data.data_homes.owner_phone2 +
            "</a>"
        );
    } else {
        $("#customer_owner_tel2").empty();
    }
    $("#customer_owner_mail").text(data.data_homes.owner_mail);

    if (data.data_homes.owner_name && data.data_homes.owner_name.length > 4) {
        $("#infoboard_owner_wrapper").show();
    } // show the owner if its available

    console.log("data.data_homes.carrier " + data.data_homes.carrier);
    if (data.data_homes.carrier == "UGG") {
        $("#carrier_logo").addClass("carrier-ugg");
    } else if (data.data_homes.carrier == "DGF") {
        $("#carrier_logo").addClass("carrier-dgf");
    } else if (data.data_homes.carrier == "GVG") {
        $("#carrier_logo").addClass("carrier-gvg");
    } else if (data.data_homes.carrier == "GlasfaserPlus") {
        $("#carrier_logo").addClass("carrier-glasfaserplus");
    }
    console.log("carrier" + data.data_homes.carrier);
    $("#client_logo").removeClass().addClass("client_logo"); // remove all classes to remove carrier logo
    if (data.data_homes.client == "Insyte") {
        $("#client_logo").addClass("client-insyte");
    } else if (data.data_homes.client == "Moncobra") {
        $("#client_logo").addClass("client-moncobra");
    } else if (data.data_homes.client == "FOL") {
        $("#client_logo").addClass("client-fol");
    }

    // ---------- parse all phonenumbers with several checks -------------------
    var phoneNumbers = [
        data.data_homes.phone1,
        data.data_homes.phone2,
        data.data_homes.phone3,
        data.data_homes.phone4,
    ];
    var displayedNumbers = [];

    for (var i = 0; i < phoneNumbers.length; i++) {
        var phoneNumber = phoneNumbers[i];

        // Check if phoneNumber is truthy before proceeding
        if (phoneNumber) {
            // Remove all spaces or unwanted characters for the href value
            var cleanPhone = phoneNumber.replace(/[^\d\+]/g, '');

            if (
                phoneNumber !== "0" &&
                !displayedNumbers.includes(phoneNumber)
            ) {
                displayedNumbers.push(phoneNumber);
                $("#customer_tel" + (i + 1)).html(
                    '<a href="tel:' +
                    cleanPhone + // Use the cleaned phone number for href
                    '" class="phone-link">' +
                    phoneNumber + // Display the original phone number
                    "</a>"
                );
            } else {
                $("#customer_tel" + (i + 1)).empty();
            }
        } else {
            $("#customer_tel" + (i + 1)).empty();
        }
    }



    if (data.data_homes.scan4_phone1 && data.data_homes.scan4_phone1.length > 4) {
        var cleanPhone = data.data_homes.scan4_phone1.replace(/\s+/g, '');
        $("#add_scan4_btn1").html(
            '<i class="ri-add-box-line"></i>' +
            '<a href="tel:' + cleanPhone + '" class="phone-link">' +
            cleanPhone +
            "</a>"
        );
    }
    if (data.data_homes.scan4_phone2 && data.data_homes.scan4_phone2.length > 4) {
        var cleanPhone = data.data_homes.scan4_phone2.replace(/\s+/g, '');
        $("#add_scan4_btn1").html(
            '<i class="ri-add-box-line"></i>' +
            '<a href="tel:' + cleanPhone + '" class="phone-link">' +
            cleanPhone +
            "</a>"
        );
    }

    // --------------------------------------------------------------------------
    // change the anruf timeline
    for (let i = 1; i <= 5; i++) {
        const anrufValue = data.data_homes["anruf" + i];
        const anrufElement = $(
            ".infoboard_anrufhistowrapper .infoboard_anrufhisto:nth-child(" +
            (2 * i - 1) +
            ")"
        );
        const lineElement = $(
            ".infoboard_anrufhistowrapper .anrufhisoline:nth-child(" + 2 * i + ")"
        );

        if (
            anrufValue !== null &&
            anrufValue !== undefined &&
            anrufValue !== "null"
        ) {
            anrufElement.attr("title", anrufValue);
            anrufElement.css({
                "background-color": "#2196f3",
                "border-color": "#8ac4f3",
                color: "#fff",
            });

            lineElement.css("background-color", "#2196f3");
        }
    }

    // --------------------------------------------------------------------------
    // set behinderung if neccessary
    const workorderCode = data?.data_homes?.workordercode?.toUpperCase();
    const dpNumber = data?.data_homes?.dpnumber;
    const client = data?.data_homes?.client;

    let displayText = '';

    // Check for 'NE3' or 'NE5' in the workorderCode
    if (workorderCode?.includes('NE5')) {
        displayText = 'NE5 Behinderung';
    }

    // Check if dpNumber is missing or empty, but only when carrier is 'Insyte'
    if (client === 'Insyte' && !dpNumber) {
        if (displayText) {
            displayText += ' | ';  // Separator if there's already some text
        }
        displayText += 'Kein DP gesetzt';
    }

    if (displayText) {
        $("#infboard_behinderung").show();
        $("#infboard_behinderung_value").text(displayText);
        $("#infoboard_planbarwrapperbuttons").parent().hide();
    }


    // --------- create a history ---------------
    let calls = data.data_calls;
    let tickets = data.data_ticket;
    let callsWithTypes = calls.map(call => ({ ...call, type: 'call' }));
    let ticketsWithTypes = tickets.map(ticket => ({ ...ticket, type: 'ticket' }));
    let timelineItems = [...callsWithTypes, ...ticketsWithTypes];
    let hbgData = data.data_hbg;
    let hbgcheckData = data.data_hbgcheck;

    // sort the timeline by date and time
    timelineItems.sort((a, b) => {
        let datetimeA = a.type === 'call' ? new Date(`${a.call_date}T${a.call_time}`) : new Date(a.ticket_creation);
        let datetimeB = b.type === 'call' ? new Date(`${b.call_date}T${b.call_time}`) : new Date(b.ticket_creation);

        return datetimeB - datetimeA; // Sorting in descending order
    });

    console.log('Calls:', calls);
    console.log('Tickets:', tickets);
    console.log('Timeline Items:', timelineItems);



    if (calls.length === 0 && tickets.length === 0) {
        $("#infoboard_timelinewrapper").html(`
            <div class="empty_timeline" style="padding: 20px;">
                <i class="ri-ghost-line" style="font-size: 22px;"></i> 
                Zu diesem Kunden gibt es noch keine Einträge.
            </div>`);
        $("#infoboard_timelinewrapper").css("height", "fit-content");
    } else {
        let hbgDict = {}; // this will be used to find a hbg item matching with a call identifier
        let hbgCheckDict = {}; // this will be used to find a hbgcheck item matching with a hbg identifier
        hbgData.forEach((item) => {
            hbgDict[item.ident] = item;
        });
        hbgcheckData.forEach((item) => {
            hbgCheckDict[item.ident] = item;
        });

        $("#infoboard_timelinewrapper").css("height", "95%");
        let timelineHTML = '<div class="timeline">';

        timelineItems.forEach(item => {
            if (item.type === 'call') {
                let currentDate = getCurrentDate("DD.MM.YY"); // get today date as dd.mm.yyyy
                const thisdate = convertDateFormat(item.call_date);
                let timeParts = item.call_time.split(":");
                const thistime = timeParts[0] + ":" + timeParts[1];
                const datetimestamp = thisdate + " - " + thistime + " Uhr";

                // extend item by reason
                if (item.result === "HBG storniert") {
                    item.result += ' - ' + item.reason;
                }

                // Add condition to check for 'HBG erstellt' and add the class according
                let contentClass = "timeline-content-inner";
                if (item.result === "HBG erstellt" || item.result === "HBG verschoben") {
                    contentClass += " hbg-erstellt";
                } else if (item.result.includes("Keine HBG")) {
                    contentClass += " stopped";
                }
                let commentHTML = "";
                if (item.comment) {
                    if (
                        (thisdate === currentDate && item.call_user === currentUser) ||
                        hasPerm(2)
                    ) {
                        // Make comment block editable if the date of the entry is the current date
                        commentHTML = `<div class="entrie_comment editable" contenteditable="true" data-id="${item.id}">${item.comment}
                        <div class="edit-comment-wrapper" style="display: none;">
                        <button class="btn-save-comment" ><i class="ri-save-3-line"></i></button>
                        <button class="btn-delete-comment"><i class="ri-delete-bin-line"></i></button>
                        </div></div>`;
                    } else {
                        commentHTML = `<div class="entrie_comment">${item.comment}</div>`;
                    }
                }

                timelineHTML += `
            <div>
                <div class="timeline-content">
                    <p class="entrie_timestamp">${datetimestamp}</p>
                    <div class="${contentClass}">
                    <p><b>${item.result}</b></p>`;
                if ((item.result === "HBG erstellt" || item.result === "HBG verschoben") && hbgDict[item.callid]) {
                    const hbgItem = hbgDict[item.callid];
                    console.log(hbgItem);
                    let hbgdate = convertDateFormat(hbgItem.date);
                    timelineHTML += `<div class="inner_hbg_item">
                        Am <b>${hbgdate}</b> um <b>${hbgItem.time} Uhr</b><br>
                        <i class="ri-corner-down-right-line"></i> <b>${hbgItem.hausbegeher}</b>
                    </div>`;

                    // ---------------------------------------------------------------------------------------
                    // -->> hbg check check and div

                    if (hbgCheckDict[hbgItem.uid]) {
                        const hbgCheckItem = hbgCheckDict[hbgItem.uid];
                        let hbgCheckDate = convertDateFormat(hbgCheckItem.datetime);
                        let hbgCheckTime = hbgCheckItem.datetime.split(" ")[1].split(":").slice(0, 2).join(":");
                        let statusColor;
                        if (hbgCheckItem.status === "DONE") {
                            statusColor = color_done;
                        } else if (hbgCheckItem.status === "PLANNED") {
                            statusColor = color_planned;
                        } else if (hbgCheckItem.status === "STOPPED") {
                            statusColor = color_stopped;
                        } else {
                            statusColor = color_open;
                        }
                        timelineHTML += `<div class="inner_hbg_check_item">
                        <b>HBG Check</b> ${hbgCheckDate} - ${hbgCheckTime}  <br>
                        <span class="customer_status_scan4" style="background-color: ${statusColor};"><b>${hbgCheckItem.status}</b></span><br>
                        <p class="entrie_comment">${hbgCheckItem.comment}</p>
                        </div>`;
                    }
                    // ---------------------------------------------------------------------------------------
                    // -->> hbg response data and div

                    if (hbgItem.appt_datetime && hbgItem.appt_datetime.trim() !== "") {
                        let parts = hbgItem.appt_datetime.split(" ");
                        let datePart = parts[0];
                        let timePart = parts[1].split(":")[0] + ":" + parts[1].split(":")[1]; // Keep only hours and minutes
                        let formattedDate = convertDateFormat(datePart, "DD.MM.YY");
                        let apptDatetime = formattedDate + " - " + timePart + " Uhr";

                        let apptComment = hbgItem.appt_comment
                            ? hbgItem.appt_comment
                            : "";
                        let apptStatus = hbgItem.appt_status
                            ? hbgItem.appt_status
                            : "Status ist noch offen";
                        let apptHausbegeher = hbgItem.hausbegeher
                            ? hbgItem.hausbegeher
                            : "Error";

                        let apptFile = hbgItem.appt_file
                            ? hbgItem.appt_file
                            : "Keine Datei vorhanden";

                        if (hbgItem.appt_file) {
                            const uploadsPath = '/uploads/hbgprotokolle/begehungen/';
                            let tmpfile;

                            // Check if appt_file contains the specific uploads path
                            if (hbgItem.appt_file.includes(uploadsPath)) {
                                // Extract the part of the path after /uploads/
                                const relativePathIndex = hbgItem.appt_file.indexOf(uploadsPath);
                                const relativePath = hbgItem.appt_file.substring(relativePathIndex);

                                // Construct the full URL
                                tmpfile = `https://crm.scan4-gmbh.de/${relativePath}`;
                            } else {
                                // If not, use the original logic
                                const staticPath = 'uploads/hbgprotokolle/begehungen/screenshots/';

                                // Extract the year using a regex pattern
                                const yearMatch = hbgItem.appt_file.match(/_(\d{4})_/);
                                const year = yearMatch ? yearMatch[1] : 'UnknownYear';

                                // Extract the project folder, assuming it's the start of the filename
                                const projectFolder = hbgItem.appt_file.split('_')[0];

                                // Construct the relative path
                                const relativePath = `${staticPath}${year}/${projectFolder}/${hbgItem.appt_file}`;

                                // Construct the full URL
                                tmpfile = `https://crm.scan4-gmbh.de/${relativePath}`;
                            }

                            // Use the constructed path for the display text
                            const displayText = tmpfile;
                            apptFile = `<a href="${tmpfile}" target="_blank">${displayText}</a>`;
                        }

                        timelineHTML += `<div class="inner_hbg_item hbgresult">
                                <p><b>${apptDatetime} -- ${apptHausbegeher}</b></p>
                                <p><b>Ergebnis:</b> ${apptStatus}</p>
                                <p><em>${apptComment}</em></p>
                                <p><em style="word-break: break-all;">${apptFile}</em></p>
                            </div>`;




                    }
                }
                timelineHTML += commentHTML;

                timelineHTML += `<p class="entrie_creator">${item.call_user}</p>
                    </div>
                </div>
            </div>`;
            } else if (item.type === 'ticket') {
                // Split the datetime string at the space
                var dateTimeParts = item.ticket_creation.split(' ');

                // Isolate the date and time
                var ticketDate = convertDateFormat(dateTimeParts[0]);
                var ticketTime = dateTimeParts[1];
                const datetimestamp = ticketDate + " - " + ticketTime;

                timelineHTML += `
                <div>
                    <div class="timeline-content">
                        <p class="entrie_timestamp">${datetimestamp}</p>
                        <div class="timeline-content-inner tickettimeline" data-ticketid="${item.ticket_id}">
                            <div><i class="ri-coupon-3-line"></i><b>${item.ticket_title}</b><span class="tickettimelinestatus">${item.ticket_status}</span></div>
                            <p>${item.ticket_finaldescription}</p>
                            <p class="entrie_creator">${item.ticket_creator}</p>
                        </div>
                    </div>
                </div>`;

            }
        });

        timelineHTML += "</div>";
        $("#infoboard_timelinewrapper").html(timelineHTML);


    }
    // ---------------------------------------------
    parserelations(data.data_relations);
    function parserelations(data_relations) {
        // Empty the wrapper before appending new relations
        $("#infoboard_relationwrapper").empty();
        let relationsHTML = "";
        if (data_relations.length === 0) {
            // No relations, append 'empty' message
            relationsHTML =
                '<div class="empty_timeline" style="padding: 20px;"><i class="ri-ghost-line" style="font-size: 22px;"></i> Zu diesem Kunden gibt es keine Verbindungen.</div>';
        } else {

            let RelationCounter = `<div class="relationIndexing">${data_relations.length}</div>`;
            $("#tabbar_relations").append(RelationCounter);
            // If there are relations, process them
            data_relations.forEach((item, index) => {
                // Assemble the address
                let address = `${item.street} ${item.streetnumber}${!isNaN(parseInt(item.streetnumberadd)) ? '/' + item.streetnumberadd.toLowerCase() : item.streetnumberadd.toLowerCase()}, ${item.plz} ${item.city}`;

                // Compare to the address in #customer_street
                let cssClass = "rel_phone";
                if (address === $("#customer_street").text()) {
                    cssClass = "rel_adress";
                }

                let hbgStatusColor = "";
                if (item.hbg_status === "OPEN") {
                    hbgStatusColor = color_open;
                } else if (item.hbg_status === "PLANNED") {
                    hbgStatusColor = color_planned;
                } else if (item.hbg_status === "DONE") {
                    hbgStatusColor = color_done;
                } else if (item.hbg_status === "STOPPED") {
                    hbgStatusColor = color_stopped;
                } else {
                    hbgStatusColor = color_notset;
                }

                let scan4StatusColor = "";
                if (item.scan4_status === "OPEN") {
                    scan4StatusColor = color_open;
                } else if (item.scan4_status === "PLANNED") {
                    scan4StatusColor = color_planned;
                } else if (item.scan4_status === "DONE") {
                    scan4StatusColor = color_done;
                } else if (item.scan4_status === "STOPPED") {
                    scan4StatusColor = color_stopped;
                } else if (item.scan4_status === "OVERDUE") {
                    scan4StatusColor = color_overdue;
                } else {
                    scan4StatusColor = color_notset;
                }
                relationsHTML += `
                <div style="padding-bottom: 10px;margin: 10px 5px 10px 5px;">
                    <div class="timeline-content ${cssClass}">
                        <div class="timeline-content-inner">
                            <p>${item.lastname} ${item.firstname} <div style="float: right;"><div><b>${item.homeid}</b></div><span><b>Unit</b> ${item.unit}</span><div id="openRel" data-homeid="${item.homeid}"><i class="ri-contract-right-line"></i></div></div></p> 
                            <p>${address}</p>
                            <p><i class="ri-phone-fill"></i> ${item.phone1}   <i class="ri-phone-fill"></i> ${item.phone2}</p>
                            <p><span class="infoboard_relation_status" style="background-color: ${hbgStatusColor}">${item.hbg_status}</span><span class="infoboard_relation_status" style="background-color: ${scan4StatusColor}">${item.scan4_status}</span></p>
                        </div>
                    </div>
                </div>`;
            });
        }

        $("#infoboard_relationwrapper").html(relationsHTML);
    }





}

function infoBoardResetLayout() {
    // Reset text values to the initial state in the infoBoard
    $("#infoboardwrapper").removeData("lat").removeData("lon");
    $("#customer_homeid").text(InfoBoardDataInitial.customer_homeid);
    $("#customer_unit").text(InfoBoardDataInitial.customer_unit);
    $("#customer_status_carrier").text(
        InfoBoardDataInitial.customer_status_carrier
    );
    $("#customer_status_scan4").text(InfoBoardDataInitial.customer_status_scan4);
    $("#customer_name").text(InfoBoardDataInitial.customer_name);
    $("#customer_street").text(InfoBoardDataInitial.customer_street);
    $("#customer_tel1").text(InfoBoardDataInitial.customer_tel1);
    $("#customer_tel2").text(InfoBoardDataInitial.customer_tel2);
    $("#customer_tel3").text(InfoBoardDataInitial.customer_tel3);
    $("#customer_tel4").text(InfoBoardDataInitial.customer_tel4);
    $("#add_scan4_btn1").html(InfoBoardDataInitial.customer_telSc1);
    $("#add_scan4_btn2").html(InfoBoardDataInitial.customer_telSc2);
    $("#add_scan4_btn1").find("a").remove();
    $("#add_scan4_btn2").find("a").remove();
    $("#customer_owner_name").html(InfoBoardDataInitial.customer_owner_name);
    $("#customer_owner_tel1").html(InfoBoardDataInitial.customer_owner_phone1);
    $("#customer_owner_tel2").html(InfoBoardDataInitial.customer_owner_phone2);
    $("#customer_owner_mail").html(InfoBoardDataInitial.customer_owner_mail);
    $("#infoboard_owner_wrapper").hide();
    $("#carrier_logo").removeClass(); // remove all classes to remove carrier logo

    $(".infoboard_anrufhisto").css({
        "background-color": InfoBoardColorInitial.background,
        "border-color": InfoBoardColorInitial.border,
        color: InfoBoardColorInitial.color,
    });

    $(".anrufhisoline").css("background-color", InfoBoardColorInitial.background);

    // Reset tooltip and line elements
    $(".infoboard_anrufhisto").removeAttr("title");
    $(".infoboard_anrufhisto").removeAttr("style");
    $(".anrufhisoline").removeAttr("style");

    InfoBoardActiveNote = "addnote"; // reset to initial note
    InfoBoardNotes = {
        // reset all noteboxes
        addnote: "",
        nohbg: "",
        followup: "",
        planappoint: "",
    };
    $("#infoboard_customer_note").text(""); // remove note text
    $("#infoboard_customer_selectioninfo").html(""); // remove nohbg selection text
    $(".relationIndexing").remove(); // remove the relation counter
    $("#infoboard_customer_note_save")
        .prop("disabled", true)
        .addClass("disabled-button"); // disable the comment save button
    $(".infoboardaction").removeClass("active"); // remove all user interaction buttons selection
    $(".infoboardaction").show(); // remove all user interaction buttons selection
    $("#infoboard_noteblock").hide(); // hide the noteblock
    $(".infoboard_tabbar_header").eq(0).click(); // click the first tab bar to make default view element
    $("#infboard_behinderung").hide(); // hide it initialy
    $("#infboard_behinderung_value").text(''); // reset the value to nothing
    $("body").css("cursor", "default"); // reset cursor to default
}

// return the current day as a specific format back
function getCurrentDate(format = "YYYY-MM-DD") {
    let date = new Date();
    let day = String(date.getDate()).padStart(2, "0");
    let month = String(date.getMonth() + 1).padStart(2, "0"); // Months are 0-based, so we add 1
    let year = date.getFullYear();
    let shortYear = String(year).slice(-2);

    switch (format) {
        case "YYYY-MM-DD":
            return `${year}-${month}-${day}`;
        case "DD.MM.YY":
            return `${day}.${month}.${shortYear}`;
        default:
            console.log("Unsupported format " + format);
            return null;
    }

    // console.log(getCurrentDate('YYYY-MM-DD')); // Prints something like "2023-06-15"
    // console.log(getCurrentDate('DD.MM.YY')); // Prints something like "15.06.23"
}

// ---------------------------------------------------------------------------------------
// this finds the nearest marker in a given group
function findNearestMarker(latLng, markers) {
    var nearestMarker = null;
    var nearestDistance = Infinity;
    markers.forEach(function (marker) {
        var markerLatLng = marker.getLatLng();
        var distance = latLng.distanceTo(markerLatLng);
        if (distance < nearestDistance) {
            nearestDistance = distance;
            nearestMarker = marker;
        }
    });
    return nearestMarker;
}



function convertDateFormat(inputDate, format = "DD.MM.YY") {
    // Convert the date string to a Date object
    let dateObj = new Date(inputDate);
    let day = String(dateObj.getDate()).padStart(2, "0");
    let month = String(dateObj.getMonth() + 1).padStart(2, "0"); // Months are 0-based, so we add 1
    let year = dateObj.getFullYear();
    let shortYear = String(year).slice(-2);

    // Format the date part
    switch (format) {
        case "YYYY-MM-DD":
            return `${year}-${month}-${day}`;
        case "DD.MM.YY":
            return `${day}.${month}.${shortYear}`;
        default:
            console.log("Unsupported format " + format);
            return null;
    }
    // let formattedDate = convertDateFormat(datePart, 'DD.MM.YY');
}

//----------------------------------------------------------------------------------------------------
// init the ability to insert a new phone number to scan4 fields. Create a popup to type in something and remove it again
$(document).ready(function () {
    var addScan4Btn1 = $("#add_scan4_btn1"),
        addScan4Btn2 = $("#add_scan4_btn2"),
        addPhonePopupContainer = $("#addPhonePopupContainer"),
        phoneInputField = $("#phoneInputField"),
        previousValue = "",
        activeButton = null;

    function showPhonePopup(btn) {
        var offset = btn.offset();
        activeButton = btn;
        btn.hide();
        addPhonePopupContainer.css({
            left: offset.left + "px",
            top: offset.top + "px",
        });

        addPhonePopupContainer.show();
        phoneInputField.val(btn.text().trim() || "");
        phoneInputField.focus();
        previousValue = phoneInputField.val();
    }

    function handleInput(event, btn, inputField) {
        var value = inputField.val().trim(),
            isDigit = /^\d*$/.test(value); // Allow empty string

        if (event.key === "Enter") {
            event.preventDefault(); // to prevent form submission
            if (value && isDigit) {
                // add a '0' at the start if not already present
                if (value[0] !== '0') {
                    value = '0' + value;
                }
                activeButton.html('<i class="ri-add-box-line"></i><a href="tel:' + value + '" class="phone-link">' + value + '</a>').show();
            } else if (value === '') {
                activeButton.html('<i class="ri-add-box-line"></i>').show();
            }

            let homeid = $('#customer_homeid').text();
            let field = activeButton.attr('id');
            if (field === 'add_scan4_btn1') field = 'scan4_phone1';
            if (field === 'add_scan4_btn2') field = 'scan4_phone2';

            // store the number
            $.ajax({
                method: "POST",
                url: "view/load/map_load.php",
                data: {
                    func: "save_scan4phone",
                    phonenumber: value,  // will be empty if user input is empty
                    homeid: homeid,
                    field: field
                },
            }).done(function (response) {
                console.log(response)
            });

            addPhonePopupContainer.hide();
            addPhonePopupContainer.css("background-color", ""); // Reset color
            previousValue = "";
            activeButton = null;
        } else {
            addPhonePopupContainer.css("background-color", isDigit ? "" : "red");
        }
    }





    addScan4Btn1.add(addScan4Btn2).on("dblclick", function (event) {
        event.stopPropagation();
        showPhonePopup($(this));
    });

    phoneInputField.on("input", function (event) {
        if (activeButton) {
            handleInput(event, activeButton, phoneInputField);
        }
    });

    phoneInputField.on("keydown", function (event) {
        if (event.key === "Enter" && activeButton) {
            handleInput(event, activeButton, phoneInputField);
        }
    });

    $(document).on("click", function (event) {
        if (
            ![
                addScan4Btn1,
                addScan4Btn2,
                addPhonePopupContainer,
                phoneInputField,
            ].some((el) => el.is(event.target))
        ) {
            addScan4Btn1.show();
            addScan4Btn2.show();
            addPhonePopupContainer.hide();
            phoneInputField.val(previousValue);
            activeButton = null;
        }
    });
});




function init_pickme() {
    var customIcon = L.icon({
        iconUrl: "https://crm.scan4-gmbh.de/view/images/pickme_stay.png",
        iconSize: [14, 20],
        iconAnchor: [12.5, 12.5],
        popupAnchor: [0, -12],
    });

    $(".pickmeup.weekday").draggable({
        delay: 100,
        helper: "clone",
        appendTo: "body",
        containment: ".pagewrapper",
        start: function (event, ui) {
            // Prevent dragging if the element already has the 'dragging' class
            if ($(this).hasClass("dragging") || !hasPerm(2)) {
                event.preventDefault();
                return;
            }

            // Original element keeps the dragging class
            $(this).addClass("dragging");
            ui.helper.css("z-index", "9999");
            $('#userplatestatsslider').hide() // fade out the calendar when dragging to see the map
        },
        stop: function (event, ui) {
            var $leaflet = $("#leaflet");

            // Check if the drop happened in leaflet
            if (
                ui.helper.offset().top > $leaflet.offset().top &&
                ui.helper.offset().left > $leaflet.offset().left &&
                ui.helper.offset().top < $leaflet.offset().top + $leaflet.height() &&
                ui.helper.offset().left < $leaflet.offset().left + $leaflet.width()
            ) {
                if (typeof map === "undefined") {
                    console.error("map is not defined.");
                    return;
                }

                var latLng = map.containerPointToLatLng(
                    map.mouseEventToContainerPoint(event)
                );

                // Get the data from the element and check if its exist
                var pegmanData = $(this).data("json");
                if (typeof pegmanData === "undefined") {
                    console.error("No data found on the dragged element.");
                    return;
                }

                // Create the marker
                var marker = L.marker(latLng, { icon: customIcon, draggable: true }).addTo(map);

                // Store the pegmanData to the marker
                marker.pegmanData = pegmanData;
                marker.originalElement = $(this); // bind the origin element to the marker
                // Update the marker and store the updated pegmanData
                marker.pegmanData = updateMarker(marker, latLng);
                savepegmanMarker(marker.pegmanData);

                // Add the moveend event listener to the marker
                marker.on('moveend', function (e) {
                    var newLatLng = e.target.getLatLng();
                    e.target.pegmanData = updateMarker(e.target, newLatLng);
                    savepegmanMarker(e.target.pegmanData); // save this data to the db
                });
                marker.on('contextmenu', function (e) {
                    var clickedMarker = this; // 'this' refers to the clicked marker
                    e.originalEvent.preventDefault(); // Prevent the default right click

                    // assign a unique id to the clicked marker, so we can refer to it in the context menu
                    var uniqueId = Math.random().toString(36).substr(2, 9);
                    clickedMarker._icon.id = uniqueId;

                    $.contextMenu({
                        theme: "dark",
                        selector: '#' + uniqueId,
                        callback: function (key, options) {
                            if (key === 'delete') {
                                map.removeLayer(clickedMarker); // Remove the marker from the map
                                clickedMarker.originalElement.removeClass("dragging"); // remove the dragging class from origin element
                                savepegmanMarker(clickedMarker.pegmanData, save = false)
                            }
                        },
                        items: {
                            "delete": { name: "Marker entfernen", icon: "delete" } // Define context menu items here
                        }
                    });

                    // Programmatically Trigger The Context Menu
                    $('#' + uniqueId).contextMenu();
                });


                console.log('append marker data');
                $(this).data("marker", marker);  // bind THIS marker to THIS element where dragging started
            } else {
                // Remove the dragging class from the original element
                $(this).removeClass("dragging");
            }
        }


    });

    $(".pickmeup.weekday").on('click', function () {
        if ($(this).data("marker")) {
            var marker = $(this).data("marker");
            var latLng = marker.getLatLng();

            map.flyTo(latLng, 16, { duration: mapFlyDuration });
            $('.slideclosebtn').click();
        }
    });
    $(".pickmeup.weekday").on('contextmenu', function (event) {
        if (!hasPerm(2)) {
            return; // Exit the function if hasPern is not equal to 2
        }
        console.log('triggered')
        var originElement = this; // 'this' refers to the clicked origin element
        event.preventDefault(); // Prevent the default right click

        // Check if element still has the 'dragging' class
        if ($(originElement).hasClass('dragging')) {
            if ($(originElement).data("marker")) {
                var clickedMarker = $(originElement).data("marker");

                // assign a unique id to the clicked marker, so we can refer to it in the context menu
                var uniqueId = Math.random().toString(36).substr(2, 9);
                clickedMarker._icon.id = uniqueId;

                $.contextMenu({
                    theme: "dark",
                    selector: '#' + uniqueId,
                    callback: function (key, options) {
                        if (key === 'delete') {
                            map.removeLayer(clickedMarker); // Remove the marker from the map
                            $(originElement).removeClass("dragging"); // remove the dragging class from origin element
                            $(originElement).removeData("marker"); // remove marker data from the origin element
                            savepegmanMarker(clickedMarker.pegmanData, save = false)
                        }
                    },
                    items: {
                        "delete": { name: "Marker entfernen", icon: "delete" } // Define context menu items here
                    },
                    position: function (opt, x, y) {
                        opt.$menu.css({ top: event.pageY, left: event.pageX });
                    }
                });

                // Programmatically Trigger The Context Menu
                $('#' + uniqueId).contextMenu();
            }
        }
    });
}
// -------------------------------------------------------------------------------------------------------
// loads pegmanData from db and display them on the map
function loadMarkers() {
    var customIcon = L.icon({
        iconUrl: "https://crm.scan4-gmbh.de/view/images/pickme_stay.png",
        iconSize: [14, 20],
        iconAnchor: [12.5, 12.5],
        popupAnchor: [0, -12],
    });
    // Iterate over all data in the json_pegman
    for (var i = 0; i < json_pegman.length; i++) {
        var markerData = JSON.parse(json_pegman[i].description);  // decode JSON string

        // Create the marker
        var latLng = L.latLng(markerData.lat, markerData.lon);
        if (hasPerm(2)) {
            var marker = L.marker(latLng, { icon: customIcon, draggable: true }).addTo(map);
        } else {
            var marker = L.marker(latLng, {
                icon: customIcon, draggable: false,
                opacity: 0,
                fillOpacity: 0,
                interactive: false
            }).addTo(map);

        }

        marker.pegmanData = markerData;

        // Associate the marker with the original HTML element.
        var originalElement = $(".pickmeup.weekday").filter(function () {
            // Get the data-json attribute from the current element and parse it to an object.
            var data = $(this).data("json");

            // Compare this data to the marker data. If they match, return true.
            return data.name === markerData.name && data.date === markerData.date;
        });

        originalElement.data("marker", marker);
        originalElement.addClass("dragging");
        updateMarker(marker, latLng);

        // Find the closest parent div with class .homeidday
        var homeiddayDiv = originalElement.closest('.homeidday');
        var assignedProjectDiv = homeiddayDiv.find('.assignedProject');
        if (assignedProjectDiv.length > 0) {
            assignedProjectDiv.text(marker.options.project);
        }
        // Bind the necessary events to the marker.
        bindMarkerEvents(marker, originalElement);
    }
}

function bindMarkerEvents(marker, originalElement) {
    // Add the moveend event listener to the marker
    marker.on('moveend', function (e) {
        var newLatLng = e.target.getLatLng();
        e.target.pegmanData = updateMarker(e.target, newLatLng);
        savepegmanMarker(e.target.pegmanData); // save this data to the db
    });

    marker.on('contextmenu', function (e) {
        var clickedMarker = this; // 'this' refers to the clicked marker
        e.originalEvent.preventDefault(); // Prevent the default right click

        // assign a unique id to the clicked marker, so we can refer to it in the context menu
        var uniqueId = Math.random().toString(36).substr(2, 9);
        clickedMarker._icon.id = uniqueId;

        $.contextMenu({
            theme: "dark",
            selector: '#' + uniqueId,
            callback: function (key, options) {
                if (key === 'delete') {
                    map.removeLayer(clickedMarker); // Remove the marker from the map

                    // Check if the original element is defined and if so, remove the 'dragging' class
                    if (clickedMarker.originalElement) {
                        clickedMarker.originalElement.removeClass("dragging"); // remove the dragging class from origin element
                    }

                    savepegmanMarker(clickedMarker.pegmanData, save = false)
                }
            },

            items: {
                "delete": { name: "Marker entfernen", icon: "delete" } // Define context menu items here
            }
        });

        // Programmatically Trigger The Context Menu
        $('#' + uniqueId).contextMenu();
    });
}
// -------------------------------------------------------------------------------------------------------
// this updates the pegmanData marker. only used for dragging feature
function updateMarker(marker, latLng) {
    var nearestMarker = findNearestMarker(latLng, markers);
    const closestProject = nearestMarker.scan4.city;
    marker.options.project = closestProject;
    var pegmanData = marker.pegmanData;
    pegmanData.project = closestProject;
    pegmanData.lat = latLng.lat;
    pegmanData.lon = latLng.lng;
    let formattedDate = convertDateFormat(pegmanData.date, 'DD.MM.YY');
    var newPopupContent =
        '<div><strong>' + pegmanData.name + '</strong></div>' +
        '<div>KW' + pegmanData.week + '</div>' +
        '<div>' + pegmanData.day + ' ' + formattedDate + '</div>' +
        '<div>Projekt: ' + closestProject;
    marker.bindPopup(newPopupContent, {
        className: "modernmarkerpopup_white",
    });

    return pegmanData;
}

// -------------------------------------------------------------------------------------------------------
// insert, update or delete this pegmanData in the table
function savepegmanMarker(pegmanData, save = true) {
    console.log('save marker with state ' + save)
    $.ajax({
        method: "POST",
        url: "view/load/map_load.php",
        data: {
            func: "save_pegman",
            data: pegmanData,
            save: save
        },
    }).done(function (response) {
        console.log(response)
        refresh_calendar();
    });
}



$(document).ready(function () {
    if (!hasPerm(2)) {
        //$('.pickmeup').hide(); // hide all pegmans
    }


    $("#myBtn1").on('click', function () {
        gif_anim('target', 'gif')
    });


    window.myCursorX = 0;
    window.myCursorY = 0;
    $(document).on('mousemove', function (e) {
        myCursorX = e.pageX;
        myCursorY = e.pageY;
    });
});



function refresh_calendar() {
    return new Promise((resolve, reject) => {
        $.ajax({
            method: "POST",
            url: "view/load/map_load.php",
            data: {
                func: "refresh_calendar",
            },
        }).done(function (response) {
            json_calendar = JSON.parse(response)
            console.log(json_calendar)
            markers_init_calendar(); // refresh the event markers
            getAllOpenSlots(); // refresh the calendar slots
            calendar_init(); // refresh the calendar view
            resolve(true);
        }).fail(function (jqXHR, textStatus, errorThrown) {
            reject(new Error(errorThrown));
        });
    });
}


function gif_anim(element, gif) {

    var img = $('<img id="loadingGif">').attr('src', 'https://crm.scan4-gmbh.de/view/images/anim_check_splash.gif');
    img.appendTo('body');
    var imgCSS = {
        'position': 'absolute',
        'zIndex': '9999999',
        'width': '65px'
    };

    if (element !== 'cursor') {
        var targetElement = $(element);
        var elementOffset = targetElement.offset();
        imgCSS.top = elementOffset.top + 'px';
        imgCSS.left = elementOffset.left + 'px';
    } else {
        imgCSS.top = myCursorY + 'px';
        imgCSS.left = myCursorX + 'px';
    }

    img.css(imgCSS);

    setTimeout(function () {
        img.remove();
    }, 1500);
}































function init_pickmeWeek() {
    var customIcon = L.icon({
        iconUrl: "https://crm.scan4-gmbh.de/view/images/pickme_stay.png",
        iconSize: [14, 20],
        iconAnchor: [12.5, 12.5],
        popupAnchor: [0, -12],
    });

    $(".pickmeup.weekman").draggable({
        delay: 100,
        helper: "clone",
        appendTo: "body",
        containment: ".pagewrapper",
        start: function (event, ui) {
            if ($(this).hasClass("dragging")) {
                event.preventDefault();
                return;
            }
            $(this).addClass("dragging");
            ui.helper.css("z-index", "9999");
            $('#userplatestatsslider').hide()
        },
        stop: function (event, ui) {
            var $leaflet = $("#leaflet");

            if (
                ui.helper.offset().top > $leaflet.offset().top &&
                ui.helper.offset().left > $leaflet.offset().left &&
                ui.helper.offset().top < $leaflet.offset().top + $leaflet.height() &&
                ui.helper.offset().left < $leaflet.offset().left + $leaflet.width()
            ) {
                if (typeof map === "undefined") {
                    console.error("map is not defined.");
                    return;
                }

                var latLng = map.containerPointToLatLng(
                    map.mouseEventToContainerPoint(event)
                );

                var weekmanData = $(this).data("json");
                if (typeof weekmanData === "undefined") {
                    console.error("No data found on the dragged element.");
                    return;
                }

                var userName = weekmanData.name; // Extract the username from weekmanData

                // Iterate over all the dates in the week
                for (var i = 0; i < 6; i++) {
                    var date = weekmanData['date' + i];
                    if (date) {
                        // Find the corresponding weekday element
                        var weekdayElements = $(".pickmeup.weekday").filter(function () {
                            var data = $(this).data("json");
                            return data.name === userName && data.date === date; // Check for the same username and date
                        });

                        if (weekdayElements.length === 0) {
                            console.error("No weekday element found for user " + userName + " and date " + date + ".");
                            continue;
                        }

                        weekdayElements.each(function () {
                            var pegmanData = $(this).data("json");
                            var marker = L.marker(latLng, { icon: customIcon, draggable: true }).addTo(map);
                            marker.pegmanData = pegmanData;
                            marker.originalElement = $(this);
                            marker.pegmanData = updateMarker(marker, latLng);
                            savepegmanMarker(marker.pegmanData);
                            bindMarkerEvents(marker, $(this));
                            $(this).data("marker", marker);
                        });
                    }
                }
            }
            else {
                $(this).removeClass("dragging");
            }
        }
    });
}


// -------------------------------------------------------------------------------------------------------------------------
// this block adds the feature to edit / update the calendar with rightclick. Add Urlaub, Arbeitszeit and custom events, delete them etc

$(document).ready(function () {
    $(function () {
        if (!hasPerm(2)) {
            return;
        }
        // context menu for .hour-slot
        $.contextMenu({
            selector: '.hour-slot',
            trigger: 'right',
            items: {
                "Urlaub": {
                    name: "Urlaub", callback: function (key, opt) {
                        var date = $(this).closest('.homeidday').attr('id');
                        var username = $(this).closest('.hiddenstats').attr('id');

                        userControlEvent_worktime(date, username, 'event', key, function (result) {
                            $('body').css('cursor', 'wait');
                            result.username = username;
                            result.date = date;
                            result.eventName = key;
                            result.isUnique = true;
                            console.log(result);
                            let resultJson = JSON.stringify({ result });
                            $.ajax({
                                method: "POST",
                                url: "view/load/map_load.php",
                                data: {
                                    func: "save_calEvent",
                                    data: resultJson,
                                },
                            }).done(function (response) {
                                console.log(response)
                                refresh_calendar();
                                $('body').css('cursor', 'default');
                            });
                        });
                    }
                },
                "Krank": {
                    name: "Krank", callback: function (key, opt) {
                        var date = $(this).closest('.homeidday').attr('id');
                        var username = $(this).closest('.hiddenstats').attr('id');

                        userControlEvent_worktime(date, username, 'event', key, function (result) {
                            $('body').css('cursor', 'wait');
                            result.username = username;
                            result.date = date;
                            result.eventName = key;
                            result.isUnique = true;
                            console.log(result);
                            let resultJson = JSON.stringify({ result });
                            $.ajax({
                                method: "POST",
                                url: "view/load/map_load.php",
                                data: {
                                    func: "save_calEvent",
                                    data: resultJson,
                                },
                            }).done(function (response) {
                                console.log(response)
                                refresh_calendar();
                                $('body').css('cursor', 'default');
                            });
                        });
                    }
                },
                "Custom": {
                    name: "Custom", callback: function (key, opt) {
                        var date = $(this).closest('.homeidday').attr('id');
                        var username = $(this).closest('.hiddenstats').attr('id');

                        userControlEvent_worktime(date, username, 'custom', key, function (result) {
                            $('body').css('cursor', 'wait');
                            result.username = username;
                            result.date = date;
                            result.eventName = result.eventName ?? key; // only set key if eventName is null/undefined
                            result.isUnique = false;
                            console.log(result);
                            let resultJson = JSON.stringify({ result });
                            $.ajax({
                                method: "POST",
                                url: "view/load/map_load.php",
                                data: {
                                    func: "save_calEvent",
                                    data: resultJson,
                                },
                            }).done(function (response) {
                                console.log(response)
                                refresh_calendar();
                                $('body').css('cursor', 'default');
                            });
                        });
                    }
                },
                "Arbeitszeit": {
                    name: "Arbeitszeit", callback: function (key, opt) {

                        var date = $(this).closest('.homeidday').attr('id');
                        var username = $(this).closest('.hiddenstats').attr('id');
                        var eventName = 'Arbeitszeit bearbeiten';

                        userControlEvent_worktime(date, username, 'worktime', eventName, function (result) {
                            $('body').css('cursor', 'wait');
                            result.username = username;
                            result.date = date;
                            console.log('save_arbeitszeit sending result:', result);

                            let resultJson = JSON.stringify({ result });
                            $.ajax({
                                method: "POST",
                                url: "view/load/map_load.php",
                                data: {
                                    func: "save_arbeitszeit",
                                    data: resultJson,
                                },
                            }).done(function (response) {
                                console.log(response)
                                refresh_calendar();
                                $('body').css('cursor', 'default');
                            });
                        });

                    }
                }
            }
        });

        // context menu for .userControleEvent
        $.contextMenu({
            selector: '.userControleEvent',
            trigger: 'right',
            items: {
                "Delete": {
                    name: "Delete", callback: function (key, opt) {
                        const eventID = $(this).attr('eventid');
                        const eventText = $(this).text()


                        $.ajax({
                            method: "POST",
                            url: "view/load/map_load.php",
                            data: {
                                func: "delete_calEvent",
                                eventid: eventID,
                            },
                        }).done(function (response) {
                            console.log(response)
                            refresh_calendar();
                        });

                    }
                },
                /*
                "Edit": {
                    name: "Edit", callback: function (key, opt) {
                        // Get stored data
                        var elementTarget = this;
                        var parentElement = $(elementTarget).parent();
                        var UpperparentElement = $(elementTarget).parent().parent();


                        var date = $(this).data('date');
                        var username = $(this).data('username');
                        var eventName = $(this).text();

                        console.log(date)
                        console.log(username)
                        console.log(eventName)

                    }
                }
                */
            }
        });


        // ---------------------------------------------------------------------------------------------------------
        // dialog box to set the event data
        function userControlEvent_worktime(date, username, content, eventName, onConfirm) {
            const html = '<div class="custom-control custom-switch"> <input type="checkbox" class="custom-control-input" id="allday" style="display: none;"> <label class="custom-control-label" for="allday"></label> </div>';
            const formattedDate = convertDateFormat(date);

            var date = new Date(date);
            var days = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
            var dayName = days[date.getDay()];


            const btnGrpRepeat1 = '<div class="btn-group btn-group-sm repeating" role="group" aria-label="Basic example"> <button type="button" class="btn btn-secondary darkgrey selected" id="once">Einmalig</button> <button type="button" class="btn btn-secondary darkgrey" id="daily">Täglich</button> <button type="button" class="btn btn-secondary darkgrey" id="weekly">Wöchentlich</button> </div>';
            const btnGrpBreak = '<div id="breakTimeGrp" class="btn-group btn-group-sm" role="group" aria-label="Button group with nested dropdown" > <button type="button" class="btn btn-secondary darkgrey selected" id="noBreakTime" > Nein </button> <div class="btn-group btn-group-sm" role="group"> <button id="breaktTimeSelection" type="button" class="btn btn-secondary darkgrey dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" time="0" > Ja </button> <div id="breaktTime" class="dropdown-menu" aria-labelledby="btnGroupDrop1"> <a class="dropdown-item" href="#">30 min</a> <a class="dropdown-item" href="#">60 min</a> </div> </div> </div>';
            const btnGrpAllDay = '<div class="btn-group btn-group-sm repeating" role="group" aria-label="Basic example"> <button type="button" class="btn btn-secondary darkgrey selected" id="notAllDay">Nein</button> <button type="button" class="btn btn-secondary darkgrey" id="allDay"> Ja </button> </div>';


            const contentOptions = {
                worktime: `<div style="border-bottom: 1px solid #5d5d5d; padding: 8px 0px; display: flex; justify-content: space-between;"><div><i class="ri-quote-text"></i> ${dayName}</div><div>${formattedDate}</div></div>` +
                    `<div style="border-bottom: 1px solid #5d5d5d; padding: 8px 0px;"><i class="ri-user-3-line"></i> <span>${username}</span></div>` +
                    `<div style="display: flex; justify-content: space-between;padding-top: 8px;"><div>Arbeitsbeginn</div><div><input style="display:none;" type="text" id="timepickr_from"></div></div>` +
                    `<div style="display: flex; justify-content: space-between;"><div>Arbeitsende</div><div><input style="display:none;" type="text" id="timepickr_to"></div></div>` +
                    `<div style="display: flex; justify-content: space-between;padding-top: 14px;padding-bottom: 8px;border-bottom: 1px solid #5d5d5d;"><div>Stunden</div><div><input style="display:none;" type="text" id="timepickr_hours"></div></div>` +
                    `<div style="border-bottom: 1px solid #5d5d5d; padding: 8px 0px;display: flex; justify-content: space-between;"><div><i class="ri-pause-circle-line" style="font-size: 20px;"></i> <span>Pause</span> ${btnGrpBreak}</div><div><input style="display:none;" type="text" id="timepickr_break"></div></div>` +
                    `<div style="white-space: nowrap;margin: 5px 0px;"><i class="ri-24-hours-line" style="font-size: 20px;"></i> ${btnGrpRepeat1} </div>`,

                // let unused html stay in to prevent script errors. unused stuff is just hidden, zero performance issues
                event: `<div style="border-bottom: 1px solid #5d5d5d; padding: 8px 0px; display: flex; justify-content: space-between;"><div><i class="ri-quote-text"></i> ${dayName}</div><div>${formattedDate}</div></div>` +
                    `<div style="border-bottom: 1px solid #5d5d5d; padding: 8px 0px;"><i class="ri-user-3-line"></i> <span>${username}</span></div>` +
                    `<div style="display: flex; justify-content: space-between;padding-top: 8px;align-items: center;"><div>Begin</div><div><input style="display:none;" type="text" id="timepickr_from"></div></div>` +
                    `<div style="display: flex; justify-content: space-between;align-items: center;"><div>Ende</div><div><input style="display:none;" type="text" id="timepickr_to"></div></div>` +
                    `<div style="display: none; justify-content: space-between;padding-top: 14px;padding-bottom: 8px;border-bottom: 1px solid #5d5d5d;"><div>Stunden</div><div><input style="display:none;" type="text" id="timepickr_hours"></div></div>` +
                    `<div style="display: none;border-bottom: 1px solid #5d5d5d; padding: 8px 0px;; justify-content: space-between;"><div><i class="ri-pause-circle-line" style="font-size: 20px;"></i> <span>Pause</span> ${btnGrpBreak}</div><div><input style="display:none;" type="text" id="timepickr_break"></div></div>` +
                    `<div style="border-bottom: 1px solid #5d5d5d; border-top: 1px solid #5d5d5d; padding: 8px 0px; display: flex; justify-content: space-between; margin-top: 8px;"><div style="align-items: center; display: inline-flex;"><i class="ri-calendar-line"></i><span style="padding-left: 5px;">Ganztag</span> </div><div style="padding: 0px 4px;">${btnGrpAllDay}</div></div>` +
                    `<div style="display:none;white-space:nowrap;margin: 5px 0px;"><i class="ri-24-hours-line" style="font-size: 20px;"></i> ${btnGrpRepeat1} </div>`,

                custom: `<div style="border-bottom: 1px solid #5d5d5d; padding: 8px 0px; display: flex; justify-content: space-between;"><div><i class="ri-quote-text"></i> ${dayName}</div><div>${formattedDate}</div></div>` +
                    `<div style="border-bottom: 1px solid #5d5d5d; padding: 8px 0px;"><input id="custom_event_input" class="darkgreyinputfield"></div>` +
                    `<div style="border-bottom: 1px solid #5d5d5d; padding: 8px 0px;"><i class="ri-user-3-line"></i> <span>${username}</span></div>` +
                    `<div style="display: flex; justify-content: space-between;padding-top: 8px;align-items: center;"><div>Begin</div><div><input style="display:none;" type="text" id="timepickr_from"></div></div>` +
                    `<div style="display: flex; justify-content: space-between;align-items: center;"><div>Ende</div><div><input style="display:none;" type="text" id="timepickr_to"></div></div>` +
                    `<div style="display: none; justify-content: space-between;padding-top: 14px;padding-bottom: 8px;border-bottom: 1px solid #5d5d5d;"><div>Stunden</div><div><input style="display:none;" type="text" id="timepickr_hours"></div></div>` +
                    `<div style="display: none;border-bottom: 1px solid #5d5d5d; padding: 8px 0px;; justify-content: space-between;"><div><i class="ri-pause-circle-line" style="font-size: 20px;"></i> <span>Pause</span> ${btnGrpBreak}</div><div><input style="display:none;" type="text" id="timepickr_break"></div></div>` +
                    `<div style="border-bottom: 1px solid #5d5d5d; border-top: 1px solid #5d5d5d; padding: 8px 0px; display: flex; justify-content: space-between; margin-top: 8px;"><div style="align-items: center; display: inline-flex;"><i class="ri-calendar-line"></i><span style="padding-left: 5px;">Ganztag</span> </div><div style="padding: 0px 4px;">${btnGrpAllDay}</div></div>` +
                    `<div style="display:none;white-space:nowrap;margin: 5px 0px;"><i class="ri-24-hours-line" style="font-size: 20px;"></i> ${btnGrpRepeat1} </div>`,
            }

            const selectedContent = contentOptions[content];

            $.confirm({
                backgroundDismiss: true,
                theme: "dark",
                title: eventName,
                content: selectedContent,
                buttons: {
                    confirm: {
                        text: "Bestätigen",
                        btnClass: "btn-blue",
                        keys: ["enter"],
                        action: function () {
                            var fromInput = $('#timepickr_from').val();
                            var toInput = $('#timepickr_to').val();
                            var workHours = $('#timepickr_hours').val();
                            var breakSlot = $('#timepickr_break').val();
                            var breakTime = parseInt($('#breaktTimeSelection').attr('time'));
                            var eventRepeat = $('.btn-group.repeating .btn.selected').attr('id');
                            var allday = $('#notAllDay').hasClass('selected') ? false : true;
                            if (content === 'worktime') allday = false;
                            if (allday === true) {
                                fromInput = '07:00';
                                toInput = '20:00';
                            }
                            var customField = $('#custom_event_input').val();


                            var result = {
                                fromInput: fromInput,
                                toInput: toInput,
                                workHours: workHours,
                                breakSlot: breakSlot,
                                breakTime: breakTime,
                                eventRepeat: eventRepeat,
                                allday: allday,
                                customField: customField
                            };
                            onConfirm(result);
                        },
                    },
                    cancel: {
                        text: "Abbruch",
                        keys: ["esc"],
                        action: function () {
                            //  $.alert('Something else?');
                        },
                    },
                },
                onOpenBefore: function () {
                    // hours need to be init first to update this on the other inputs
                    var timepickr_hours = flatpickr("#timepickr_hours", {
                        enableTime: true,
                        noCalendar: true,
                        dateFormat: "H:i",
                        time_24hr: true,
                        inline: true,
                        static: true,
                        onReady: function (dateObj, dateStr, instance) {
                            instance.calendarContainer.classList.add('darkInline');
                            instance.calendarContainer.classList.add('intOnly');
                            $('<div>').css({
                                position: 'absolute',
                                top: 0,
                                bottom: 0,
                                left: 0,
                                right: 0,
                            }).appendTo($(instance.calendarContainer).parent());
                        }
                    });




                    var timepickr_from = flatpickr("#timepickr_from", {
                        enableTime: true,
                        noCalendar: true,
                        dateFormat: "H:i",
                        minTime: "07:00",
                        maxTime: "20:00",
                        defaultDate: "08:00",
                        time_24hr: true,
                        minuteIncrement: 5,
                        inline: true,
                        static: true,
                        onReady: function (dateObj, dateStr, instance) {
                            instance.calendarContainer.classList.add('darkInline');
                        },
                        onValueUpdate: function (selectedDates, dateStr, instance) {
                            if (timepickr_from.selectedDates[0] && timepickr_from.selectedDates[0] > timepickr_to.selectedDates[0]) { // check if end is before start and update so
                                timepickr_to.setDate(timepickr_from.selectedDates[0], false); // False indicates that onChange and onClose should not be triggered after the date is set
                            }
                            calculateTimeDiff(timepickr_from, timepickr_to, timepickr_hours); // calc diff and change hours according
                        }
                    });

                    var timepickr_to = flatpickr("#timepickr_to", {
                        enableTime: true,
                        noCalendar: true,
                        dateFormat: "H:i",
                        minTime: "07:00",
                        maxTime: "20:00",
                        defaultDate: "17:00",
                        time_24hr: true,
                        minuteIncrement: 5,
                        inline: true,
                        static: true,
                        onReady: function (dateObj, dateStr, instance) {
                            instance.calendarContainer.classList.add('darkInline'); // add css class
                        },
                        onValueUpdate: function (selectedDates, dateStr, instance) {
                            if (timepickr_from.selectedDates[0] && timepickr_from.selectedDates[0] > timepickr_to.selectedDates[0]) { // check if end is before start and update so
                                timepickr_to.setDate(timepickr_from.selectedDates[0], false); // False indicates that onChange and onClose should not be triggered after the date is set
                            }
                            calculateTimeDiff(timepickr_from, timepickr_to, timepickr_hours); // calc diff and change hours according
                        }
                    });

                    var timepickr_break = flatpickr("#timepickr_break", {
                        enableTime: true,
                        noCalendar: true,
                        dateFormat: "H:i",
                        minTime: "07:00",
                        maxTime: "20:00",
                        defaultDate: "13:00",
                        time_24hr: true,
                        minuteIncrement: 5,
                        inline: true,
                        static: true,
                        onReady: function (dateObj, dateStr, instance) {
                            instance.calendarContainer.classList.add('darkInline'); // add css class
                            instance.calendarContainer.classList.add('FlatBreaktTime'); // add css class
                        },

                    });


                    // initial calc the hours when popup opens
                    calculateTimeDiff(timepickr_from, timepickr_to, timepickr_hours); // calc diff and change hours according
                    $(document).ready(function () {

                        $(document).on('click', '.btn-group.repeating .btn', function () {
                            $('.btn-group.repeating .btn').removeClass('selected');
                            $(this).addClass('selected');
                        });

                        // check the breakTime dropdown and update the value of selection. Hide the flatpickr if no is selected
                        $('#breaktTime').on('click', '.dropdown-item', function () {
                            var selectedValue = $(this).text();
                            $('#breaktTimeSelection').text(selectedValue); // set the selected value as the new text for the dropdown button
                            var newValue = selectedValue.replace(/\s/g, "").replace("min", "");
                            $('#breaktTimeSelection').attr("time", newValue);
                            $('.flatpickr-calendar.hasTime.noCalendar.animate.inline.darkInline.FlatBreaktTime').css('opacity', 1);
                            calculateTimeDiff(timepickr_from, timepickr_to, timepickr_hours);
                        });
                        $("#breakTimeGrp .btn").click(function () {
                            $("#breakTimeGrp .btn").removeClass("selected");
                            $(this).addClass("selected");

                            if ($(this).attr('id') === 'noBreakTime') {
                                $('.flatpickr-calendar.hasTime.noCalendar.animate.inline.darkInline.FlatBreaktTime').css('opacity', 0);
                                $('#breaktTimeSelection').text('Ja');
                                $('#breaktTimeSelection').attr("time", '0');
                                calculateTimeDiff(timepickr_from, timepickr_to, timepickr_hours);
                            }

                        });

                    });
                }
            });
        }


    });


    function calculateTimeDiff(timepickr_from, timepickr_to, timepickr_hours) {
        var fromTime = timepickr_from.selectedDates[0];
        var toTime = timepickr_to.selectedDates[0];

        if (fromTime && toTime) {
            var diff = toTime - fromTime; // milliseconds between fromTime & toTime
            var diffHours = Math.floor(diff / 1000 / 60 / 60); // converting ms to hours
            var diffMinutes = Math.floor((diff / 1000 / 60) % 60); // converting ms to minutes

            var breakTime = parseInt($('#breaktTimeSelection').attr('time'));
            console.log("Break time in minutes: ", breakTime);

            diffMinutes = diffMinutes - breakTime;
            if (diffMinutes < 0) {
                diffHours = diffHours - 1;
                diffMinutes = diffMinutes + 60;
            }
            if (diffHours >= 8) {
                $('.flatpickr-calendar.hasTime.noCalendar.animate.inline.darkInline.intOnly').css('border', '1px solid #1cb900') // make the border green
            } else {
                $('.flatpickr-calendar.hasTime.noCalendar.animate.inline.darkInline.intOnly').css('border', '1px solid #d10000') // make the border red
            }

            var formattedDiff = ("0" + diffHours).slice(-2) + ":" + ("0" + diffMinutes).slice(-2); // formatting time difference
            timepickr_hours.setDate(formattedDiff, false);
        }
    }


});







$(document).ready(function () {
    $("#mapsettingsbtn, .clbtn").click(function (event) {
        $('#adminpannel').toggle();
    });
    $(".adm_header_item").click(function (event) {
        $(".adm_header_item").removeClass('selected');
        $(this).addClass('selected')
    });




});








$(document).ready(function () {
    $(document).on('keydown', function (event) {
        /*
        if (event.key === "0") {

            let html = `
            <div class="changelog_item">
                <div class="changelog_item_title"><span class="changelog_state new">NEU</span> Termine verschieben</div>
                <div>Es ist ab sofort möglich erstellte Termine zu verschieben. Diese Option steht bereit, wenn der aktuelle Termin in der Zukunft liegt. 
                <br>Die Schaltfläche "Verschieben" findet ihr mit einem Klick auf den Button "Planen". 
                Es ist zwingend notwendig, einen Grund sowie den Initiator dieser Aktion zu benennen (Kunde oder interner Grund).</div>
            </div>
            <div class="changelog_item">
                <div class="changelog_item_title"><span class="changelog_state new">NEU</span> Termine stornieren</div>
                <div>Es ist ab sofort möglich erstellte Termine zu stornieren. Diese Option steht bereit, wenn der aktuelle Termin in der Zukunft liegt. 
                <br>Die Schaltfläche "Stornieren" findet ihr mit einem Klick auf den Button "Planen". 
                Es ist zwingend notwendig, einen Grund sowie den Initiator dieser Aktion zu benennen (Kunde oder interner Grund).</div>
             </div>
            `;



            $.confirm({
                columnClass: 'customWidth',
                backgroundDismiss: false,
                theme: "dark",
                title: '<i class="ri-file-list-3-line"></i> Changelog',
                content: html,
                buttons: {
                    confirm: {
                        text: "Ok",
                        btnClass: "btn-blue",
                        keys: ["enter"],
                        action: function () {
                            // 
                        },
                    },

                },
            });
        }
        */
    });



});



function infoPlateIconClick() {
    var leafletPos = $("#leaflet").offset();
    var left = leafletPos.left;
    var top = leafletPos.top;
    var height = $("#leaflet").outerHeight(); // Get the outer height of #leaflet

    var infoboardwrapper = $("#infoboardwrapper");
    infoboardwrapper.appendTo("body");

    infoboardwrapper.animate(
        {
            left: left + "px",
            top: top + "px",
            height: height + "px", // Apply the height to .infoboardwrapper
        },
        500
    );
}
// -----------------------------------------------------------------------
// Hande the infoboard
$(document).ready(function () {
    // -----------------------------------------------------------------------
    // handle the infoboard slide in / out
    $(".infoboard_closeme").click(function () {
        infoBoardvisible = false;
        $("#infoboardwrapper").animate(
            {
                left: "-60%", // Slide out to the left
            },
            500,
            function () {
                //$(this).hide(); // Hide the element after animation completes
            }
        );
    });

    $(document).on("click", ".infoplate", function () {
        infoPlateIconClick();
    });

    // -----------------------------------------------------------------------
    // infoboard tab handler
    $("#infoboard_relationwrapper").hide(); // inital hide relations till clicked
    $("#infoboard_documentswrapper").hide(); // inital hide docs till clicked
    $("#infoboard_logwrapper").hide(); // inital hide log till clicked
    var cachedTimelineContent = null; // used to store the timeline content
    var empty_timeline =
        '<div class="empty_timeline" style="padding: 20px;"><i class="ri-ghost-line" style="font-size: 22px;"></i> Zu diesem Kunden gibt es noch keine Einträge.</div>';
    $(".infoboard_tabbar_header").click(function () {
        $(".infoboard_timelinewrapper").hide(); // hide all timelines
        $(".infoboard_userinteractions").hide(); // hide all buttons

        var tabName = $(this).text();
        // Show respective div based on tab clicked
        console.log("tabName: " + tabName);
        if (tabName.includes("Relations")) {
            $("#infoboard_relationwrapper").show();
        } else if (tabName === "Dokumente") {
            $("#infoboard_documentswrapper").show();
        } else if (tabName === "Log") {
            $("#infoboard_logwrapper").show();
        } else if (tabName === "Übersicht") {
            $("#infoboard_timelinewrapper").show();
            $(".infoboard_userinteractions").show();
        }

        // Change active tab
        $(".infoboard_tabbar_header").removeClass("active");
        $(this).addClass("active");
    });
    // -----------------------------------------------------------------------
    //  infoBoard event handler buttons
    $("#infoboard_noteblock").hide(); // initial hide this block on pageload
    $(".infoboardaction").click(function (event) {
        // determine if noteblock is hidden or not
        $(".infoboardaction").removeClass("active");
        $(this).addClass("active");

        // save the current note
        InfoBoardNotes[InfoBoardActiveNote] = $("#infoboard_customer_note").val();
        console.log(InfoBoardNotes);

        // hide all and show only needed stuff
        $("#infoboard_noteblock").hide();
        $("#infoboard_customer_selectioninfo").hide();

        if ($(this).hasClass("addnote")) {
            InfoBoardActiveNote = "addnote";
            $("#infoboard_noteblock").show();
        } else if ($(this).hasClass("nohbg")) {
            InfoBoardActiveNote = "nohbg";
            $("#infoboard_customer_selectioninfo").show();
            $("#infoboard_noteblock").show();
        }

        // Load the appropriate note for the selected note type
        $("#infoboard_customer_note").val(InfoBoardNotes[InfoBoardActiveNote]);
    });
    // -----------------------------------------------------------------------
    //  infoBoard button contextmenu
    function generateContextMenu_nohbg() {
        var optionNames = [
            "nicht erreicht",
            "---------------------------",
            "Keine HBG - Falsche Adresse",
            "Keine HBG - Kunde verweigert HBG",
            "Keine HBG - Falsche Daten",
            "Keine HBG - Kunde Kündigt",
            "Keine HBG - Besonderer Grund",
        ];
        var options = optionNames.reduce(function (obj, name, index) {
            var option = "option" + (index + 1);
            if (name === "---------------------------") {
                obj[option] = {
                    name: name,
                    disabled: true, // Disable the option
                };
            } else if (name === "nicht erreicht") {
                obj[option] = {
                    name: name,
                    callback: function () {
                        console.log(name);
                        $("#infoboard_customer_selectioninfo").html(name);
                        $("#infoboard_customer_note_save")
                            .prop("disabled", false)
                            .removeClass("disabled-button"); // activate save button. no comment needed here
                    },
                };
            } else {
                obj[option] = {
                    name: name,
                    callback: function () {
                        console.log(name);
                        $("#infoboard_customer_selectioninfo").html(name);
                    },
                };
            }

            return obj;
        }, {});
        var contextMenuOptions = {
            selector: ".infoboardaction.nohbg",
            items: options,
            trigger: "left",
        };
        return contextMenuOptions;
    }
    $.contextMenu(generateContextMenu_nohbg()); // init the context menu to the DOM
    // -----------------------------------------------------------------------
    // infoBoard setting contextMenu
    if (hasPerm(2)) {
        $.contextMenu({
            selector: '.infoboard_settings',
            trigger: 'left',
            build: function ($trigger, e) {
                const currentStatusCarrier = $('#customer_status_carrier').text().trim().toLowerCase();
                const currentStatusScan4 = $('#customer_status_scan4').text().trim().toLowerCase();

                const menuStructure = [
                    {
                        name: "Change Status",
                        value: "change_status",
                        subItems: [
                            {
                                name: "Carrier",
                                value: "carrier",
                                subItems: [
                                    { name: "Done", value: "done", disabled: true },
                                    { name: "Open", value: "open", disabled: true },
                                    { name: "Stopped", value: "stopped", disabled: true }
                                ]
                            },
                            {
                                name: "Scan4",
                                value: "scan4",
                                subItems: [
                                    { name: "Done", value: "done", disabled: currentStatusScan4 === 'done' },
                                    { name: "Open", value: "open", disabled: currentStatusScan4 === 'open' },
                                    { name: "Pending", value: "pending", disabled: currentStatusScan4 === 'pending' },
                                    { name: "Stopped", value: "stopped", disabled: currentStatusScan4 === 'stopped' },
                                    { name: "Planned", value: "planned", disabled: currentStatusScan4 === 'planned' }
                                ]
                            }
                        ]
                    }
                ];

                const options = generateOptions(menuStructure); // You would define this function to replace the reduce logic currently in generateContextMenu_settings

                return {
                    items: options,
                    trigger: 'left'
                };
            }
        });
    }


    function generateOptions(menuStructure) {
        return menuStructure.reduce((obj, item, index) => {
            const optionKey = "option" + (index + 1);
            obj[optionKey] = {
                name: item.name,
                items: item.subItems.reduce((subObj, subItem, subIndex) => {
                    const subOptionKey = "subOption" + (subIndex + 1);
                    subObj[subOptionKey] = {
                        name: subItem.name,
                        items: subItem.subItems?.reduce((subSubObj, subSubItem, subSubIndex) => {
                            const subSubOptionKey = "subSubOption" + (subSubIndex + 1);
                            subSubObj[subSubOptionKey] = {
                                name: subSubItem.name,
                                disabled: subSubItem.disabled, // Adding the disabled property here
                                callback: function () {
                                    const homeid = $('#customer_homeid').text();
                                    const info = {
                                        homeid: homeid,
                                        level1: { name: item.name, value: item.value },
                                        level2: { name: subItem.name, value: subItem.value },
                                        level3: { name: subSubItem.name, value: subSubItem.value }
                                    };
                                    context_changeStatus(info);
                                }
                            };
                            return subSubObj;
                        }, {}) || null
                    };
                    return subObj;
                }, {})
            };
            return obj;
        }, {});
    }


    function context_changeStatus(info) {
        var boxTitle;
        var boxContent;

        if (info.level1.value === "change_status") {
            boxTitle = 'Status Änderung';
        }

        if (info.level2.value === "carrier" || info.level2.value === "scan4") {
            var color;
            var currentStatusText;
            var currentStatusSelector;

            if (info.level2.value === "carrier") {
                currentStatusSelector = '#customer_status_carrier';
            } else if (info.level2.value === "scan4") {
                currentStatusSelector = '#customer_status_scan4';
            }

            currentStatusText = $(currentStatusSelector).text().toLowerCase();

            const colorMap = {
                open: color_open,
                planned: color_planned,
                pending: color_pending,
                done: color_done,
                stopped: color_stopped,
                overdue: color_overdue,
                notset: color_notset
            };

            color = colorMap[info.level3.value.toLowerCase()];
            var currentStatusColor = colorMap[currentStatusText];

            boxContent = `Soll der ${info.level2.name} von <span class="status_badge" style="background-color: ${currentStatusColor};">${currentStatusText.toUpperCase()}</span> auf <span class="status_badge" style="background-color: ${color};">${info.level3.name.toUpperCase()}</span> geändert werden?`;
        }

        const homeid = info.homeid;
        $.confirm({
            backgroundDismiss: true,
            theme: "dark",
            title: boxTitle,
            content:
                boxContent,
            buttons: {
                confirm: {
                    text: '<i class="ri-delete-bin-line"></i> Ja, ändern',
                    btnClass: "btn-blue",
                    keys: ["enter"],
                    action: function () {
                        $.ajax({
                            method: "POST",
                            url: "view/load/map_load.php",
                            data: {
                                func: "changeStatus",
                                data: JSON.stringify(info),
                            },
                        }).done(function (response) {
                            console.log('changeStatus response', response)
                            //gif_anim(eventOrigin, 'anim_check_splash.gif');
                            infoBoardLoadData(currentAktivMarker); //reload current infoboard from this home
                        });

                    },
                },
                cancel: {
                    text: "Nein",
                    keys: ["esc"],
                    action: function () {
                        //  $.alert('Something else?');
                    },
                },
            },
        });
    }


    // -----------------------------------------------------------------------
    // infoboard action buttons handler
    // ---> enable / disable the save btn if the note comment is to short
    $("#infoboard_customer_note").on("keyup", function () {
        var charCount = $("#infoboard_customer_note").val().length;
        if (charCount > 5) {
            $("#infoboard_customer_note_save")
                .prop("disabled", false)
                .removeClass("disabled-button");
            console.log("currentAktivMarker " + currentAktivMarker);
            console.log(currentAktivMarker);
        } else {
            $("#infoboard_customer_note_save")
                .prop("disabled", true)
                .addClass("disabled-button");
        }
    });
    // --> show hide comment edit wrapper
    $("#infoboard_timelinewrapper").on(
        "click",
        ".entrie_comment.editable",
        function () {
            $(".edit-comment-wrapper").hide(); // hide all comment icons
            var buttonWrapper = $(this).find(".edit-comment-wrapper");
            buttonWrapper.show();
        }
    );
    // --> save the edited Notiz comment
    $("#infoboard_timelinewrapper").on("click", ".btn-save-comment", function () {
        var eventOrigin = $(this);
        var commentElement = $(this).closest(".entrie_comment.editable");
        var commentId = commentElement.data("id");
        var userComment = commentElement.text().trim();
        console.log("commentId: " + commentId);

        const homeid = $("#customer_homeid").text();
        $.ajax({
            method: "POST",
            url: "view/load/map_load.php",
            data: {
                func: "safe_call_note",
                homeid: homeid,
                comment: userComment,
                state: "update",
                id: commentId,
            },
        }).done(function (response) {
            gif_anim(eventOrigin, 'anim_check_splash.gif');
            infoBoardLoadData(currentAktivMarker); //reload current infoboard from this home
        });
    });
    // --> delete this comment
    $("#infoboard_timelinewrapper").on(
        "click",
        ".btn-delete-comment",
        function () {
            var eventOrigin = $(this);
            var commentElement = $(this).closest(".entrie_comment.editable");
            var commentId = commentElement.data("id");
            var userComment = commentElement.text();
            const homeid = $("#customer_homeid").text().trim();
            $.confirm({
                backgroundDismiss: true,
                theme: "dark",
                title: "Notiz löschen?",
                content:
                    userComment +
                    "<br><br>Soll dieser Kommentar wirklich gelöscht werden?",
                buttons: {
                    confirm: {
                        text: '<i class="ri-delete-bin-line"></i> Ja, löschen',
                        btnClass: "btn-red",
                        keys: ["enter"],
                        action: function () {
                            $.ajax({
                                method: "POST",
                                url: "view/load/map_load.php",
                                data: {
                                    func: "delete_call_note",
                                    homeid: homeid,
                                    id: commentId,
                                },
                            }).done(function (response) {
                                gif_anim(eventOrigin, 'anim_check_splash.gif');
                                infoBoardLoadData(currentAktivMarker); //reload current infoboard from this home
                            });

                        },
                    },
                    cancel: {
                        text: "Nein",
                        keys: ["esc"],
                        action: function () {
                            //  $.alert('Something else?');
                        },
                    },
                },
            });
        }
    );

    // ---> comment save button
    $("#infoboard_customer_note_save").click(function (event) {
        if ($("#infoboard_customer_note_save").prop("disabled")) {
            return false; // stop here if its disabled
        }
        $("#infoboard_customer_note_save")
            .prop("disabled", true)
            .addClass("disabled-button"); // disable it to prevent double hits
        $("body").css("cursor", "wait"); // change the cursor to waiting animation
        var userComment = $("#infoboard_customer_note").val().trim(); // get user input
        console.log("userComment " + userComment);

        const activeId = $(".infoboardaction.active").attr("id"); // get the active/selected button
        console.log("activeId " + activeId);
        const noHbgReason = $("#infoboard_customer_selectioninfo").text();
        console.log("noHbgReason " + noHbgReason);
        const homeid = $("#customer_homeid").text();

        $("#infoboard_customer_note").val(""); // empty the input
        currentAktivMarker.originalColor = marker_color_changed;
        if (activeId === "addnote") {
            $.ajax({
                method: "POST",
                url: "view/load/map_load.php",
                data: {
                    func: "safe_call_note",
                    homeid: homeid,
                    comment: userComment,
                    state: "new",
                },
            }).done(function (response) {
                infoBoardLoadData(currentAktivMarker); //reload current infoboard from this home
            });
        } else if (activeId === "nohbg") {
            $.ajax({
                method: "POST",
                url: "view/load/map_load.php",
                data: {
                    func: "safe_nohbg",
                    homeid: homeid,
                    comment: userComment,
                    reason: noHbgReason,
                },
            }).done(function (response) {
                infoBoardLoadData(currentAktivMarker); //reload current infoboard from this home
            });
        }
    });
    // -----------------------------------------------------------------------
    // click to homeid to copy to clipboard
    $("#customer_homeid, #ccthishomeid").click(async function () {
        try {
            await navigator.clipboard.writeText($('#customer_homeid').text());
            console.log('Text copied to clipboard');
            gif_anim('cursor', 'anim_check_splash.gif');
        } catch (err) {
            console.error('Failed to copy text: ', err);
        }
    });

    // -----------------------------------------------------------------------
    // infoboard select and load relation
    $(document).on("click", "#openRel", function () {
        const homeid = $(this).data('homeid');
        console.log('access relation with homeid ' + homeid)
        const marker = markers.find(marker => marker.homeid === homeid);
        if (marker) {
            console.log("Marker found:", marker);
        } else {
            console.log("Marker not found");
        }
        currentAktivMarker = marker;
        const latLng = marker.getLatLng();
        map.flyTo(latLng, 16, { duration: mapFlyDuration });
        map.once('moveend', function () {
            currentAktivMarker.fire('click');
        });
        infoBoardLoadData(currentAktivMarker);
    });
    // -----------------------------------------------------------------------
    // infoboard select and load relation
    $(document).on("click", ".infoboard_zooming", function () {
        var lat = $("#infoboardwrapper").data("lat");
        var lon = $("#infoboardwrapper").data("lon");
        map.flyTo([lat, lon], 16, { duration: mapFlyDuration });
    });

});






const ticketModalHtml = $('.mod_layerbg').html();
function ticketCall(ticketID) {

    $(document).on('click', function (event) {
        // Check if the click was outside of .mod_wrapper
        if (!$(event.target).closest('.mod_wrapper').length) {
            // Hide .mod_layerbg if the click was outside .mod_wrapper
            $('.mod_layerbg').hide();
        }
    });

    $('.mod_wrapper').on('click', function (event) {
        // Prevent the click inside .mod_wrapper from propagating to the document
        event.stopPropagation();
    });
    console.log(ticketID)
    $.ajax({
        method: "POST",
        url: "view/load/map_load.php",
        data: {
            func: "loadTicket",
            id: ticketID,
        },
    }).done(function (response) {
        console.log(response)
        var ticked_data = JSON.parse(response)
        $('.mod_layerbg').appendTo('body').toggle();


        $('.mod_title_header').text(ticked_data.object_title)
        $('.taks_inf_bodydesctext').text(ticked_data.object_content)
        $('#md_tck_datestart').text(ticked_data.date)
        $('#md_tck_dateedit').text(ticked_data.last_edit)

        $('#md_tck_cd_homeid').text($('#customer_homeid').text())
        $('#md_tck_cd_name').text($('#customer_name').text())
        $('#md_tck_cd_address').text($('#customer_street').text())
        $('#md_tck_cd_phone').text($('#customer_tel1').text())
        /*
        console.log(response)
        $.confirm({
            backgroundDismiss: true,
            theme: "dark",
            title: 'Ticket Übersicht',
            content: response,
            buttons: {
                confirm: {
                    text: "Schließen",
                    keys: ["enter"],
                    action: function () {

                    },
                },

            },
        });
*/
    });

}




let lastRanking = {};

function updateRankingList(data) {
    const rankingList = $('#userRanking');

    // Überprüfen, ob Benutzerdaten vorhanden sind
    if (!data || data.length === 0) {
        // Keine Benutzerdaten, also zeige eine Nachricht an
        rankingList.html('<div class="no-users-message">Keine Termine gemacht</div>');
        return; // Beendet die Funktion vorzeitig
    }
    let newRanking = {};

    // Store current positions
    $('.userTile').each(function () {
        const username = $(this).data('username');
        newRanking[username] = {
            element: $(this),
            oldIndex: $(this).index()
        };
    });

    // Clear the ranking list
    rankingList.empty();

    // Rebuild the ranking list
    data.forEach((user, index) => {
        let userTile = newRanking[user.username]?.element;
        let userCountColor = '';

        // Assign color based on position
        if (index === 0) {
            userCountColor = 'gold';
        } else if (index === 1) {
            userCountColor = 'silver';
        } else if (index === 2) {
            userCountColor = '#cd7f32'; // Bronze color
        }

        if (!userTile) {
            // Create new user tile
            userTile = $(`<li class="userTile" data-username="${user.username}">
                              <span class="userName">${user.username}</span>
                              <span class="userCount" style="color: ${userCountColor};">${user.count}</span>
                          </li>`);
            newRanking[user.username] = { element: userTile, oldIndex: -1 };
        } else {
            // Update user tile
            userTile.html(`<span class="userName">${user.username}</span>
                           <span class="userCount" style="background: ${userCountColor};">${user.count}</span>`);
        }

        // Add or remove flame class based on user count
        // Glow-Effekt ab 70 Count
        if (user.count >= 70 && user.count < 80) {
            userTile.find('.userName').addClass('userGlow');
        } else {
            userTile.find('.userName').removeClass('userGlow');
        }
        if (user.count >= 80 && user.count < 100) {
            userTile.find('.userName').addClass('userGlowLila');
        } else {
            userTile.find('.userName').removeClass('userGlowLila');
        }

        if (user.count >= 80 && (!lastRanking[user.username] || lastRanking[user.username].count < 80)) {
            userTile.addClass('confetti');
            setTimeout(() => userTile.removeClass('confetti'), 3000); // Konfetti-Animation dauert 3 Sekunden
        }

        // Add user tile to the list
        rankingList.append(userTile);
    });

    // Animation der Positionsänderung
    for (let username in newRanking) {
        let userTile = newRanking[username].element;
        let oldIndex = newRanking[username].oldIndex;
        let newIndex = data.findIndex(u => u.username === username);

        if (oldIndex !== newIndex) {
            let positionChange = oldIndex - newIndex;
            userTile.css('transition', 'none');
            userTile.css('transform', `translateY(${positionChange * userTile.outerHeight(true)}px)`);

            // Reset der Transformation nach der Animation
            setTimeout(() => {
                userTile.css('transition', 'transform 0.5s');
                userTile.css('transform', '');
            }, 50); // Kurze Verzögerung, um sicherzustellen, dass die CSS-Änderungen angewendet werden
        }
    }

    // Aktualisierung des letzten Rankings für den nächsten Durchgang
    lastRanking = newRanking;
}

function updateRanking() {
    $.ajax({
        url: 'view/load/map_load.php',
        type: 'POST',
        dataType: 'json',
        data: { func: 'fetchRanking' },
        success: function (response) {
            if (response.success) {
                updateRankingList(response.data);
            } else {
                console.error('Fehler beim Laden des Rankings', response);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('AJAX error in request: ' + textStatus + ', Details: ' + errorThrown);
            console.log('Response:', jqXHR.responseText);
        }
    });
}

$(document).ready(function () {
    updateRanking();
    setInterval(updateRanking, 5000); // Aktualisiert alle 5 Sekunden
});


$('#nav-toggle').on('click', function() {
    console.log('Toggle clicked');
    setTimeout(function() {
      map.invalidateSize();
      console.log('Map size invalidated');
    }, 400);
});
















