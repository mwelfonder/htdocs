




var hbgDataCache = {};
var user_dest;

var weekplan = {};
var weekplan_rev = {};
var eventList = ['Backoffice', 'Urlaub', 'Krank', 'Survey', 'StreetNav', 'callCenter', 'Fehlend Unbekannt', 'Feiertag']
var manipulatedUsernames = {};
var plandata = [];

var projectlist = '';
var menuItems = '';
//
var selectedItems = []; // array to keep track of selected items

getdist();

function getdist() {
    $('#loadwrapper').removeClass('hidden')

    $.ajax({
        method: "POST",
        url: "view/load/weekplan_load.php",
        data: {
            func: "load_distance",
        },
    }).done(function (response) {
        console.log(response);
        user_dest = JSON.parse(response);

    });

}





$(document).ready(function () {


    $('#printme').click(function () {
        // Hide all elements with class "plaininfo"
        $('.plaininfo').addClass('hidden');
        const week = $('#week').val();
        var htmlContent = $('#weekplanholder')[0]; // get the DOM element
        html2canvas(htmlContent).then(function (canvas) {
            // Open the screenshot in a new tab
            var newTab = window.open();
            newTab.document.body.appendChild(canvas);

            // Get the base64-encoded image data
            var imgData = canvas.toDataURL();
            $.ajax({
                method: "POST",
                url: "view/load/weekplan_load.php",
                data: {
                    func: "save_img",
                    data: imgData,
                    week: week,
                },
            }).done(function (response) {
                $('.plaininfo').removeClass('hidden');
            });
        });
    });






    init_datatable();



    projectlist = {
        insyte: [],
        moncobra: []
    };
    processTable("Tproject_Insyte", "insyte", projectlist);
    processTable("Tproject_Moncobra", "moncobra", projectlist);


    // ------------------------------------------------
    // Init the weekplan for current week
    get_weekplan()

    // ------------------------------------------------


    // --------------------------------------------------
    // context menu for the toptable to move, copy or open homeid
    // Create an object to hold the menu items
    menuItems = {
        open: {
            name: "Open",
            callback: function (key, opt) {
                const appointmentData = $(this).data('appointment');
                const appointmentLink = 'route.php?view=phonerapp?city=?homeid=' + appointmentData.homeid;

                window.open(appointmentLink, '_blank');
            }
        },
        copy: {
            name: "Copy",
            callback: function (key, opt) {
                // Get all selected rows and log their text content
                var selectedRows = $('.eventelements tr.selected');
                selectedRows.each(function (index) {
                    console.log("Selected row " + (index + 1) + ": " + $(this).text());
                });
                alert("Copy");
            }
        },
        submenu: {
            name: "Move to",
            items: {}
        },

    };

    const userlist = get_userlist();
    console.log('userlist' + userlist)
    for (var i = 0; i < userlist.length; i++) {
        var item = userlist[i];
        console.log('userlist: ' + item)
        menuItems.submenu.items[item] = {
            name: item,
            callback: function (key, opt) {
                // Get all selected rows and log their text content
                var selectedRows = $('.eventelements tr.selected');
                var uid = [];
                var username = key;
                selectedRows.each(function (index) {
                    const appointmentData = $(this).data('appointment');
                    uid.push(appointmentData.uid)
                    console.log(key + ' selected')
                    console.log("Selected row " + (index + 1) + ": " + $(this).text());
                });
                console.log(uid);

                // Add confirmation dialog using jQuery-confirm plugin
                $.confirm({
                    title: 'HBGs verschieben',
                    content: 'Sicher das du  ' + selectedRows.length + ' Termine zu ' + key + ' verschieben möchtest?',
                    buttons: {
                        confirm: {
                            text: 'Ja, verschieben',
                            btnClass: 'btn-green',
                            action: function () {
                                change_appointmentowner(username, uid);
                            }
                        },
                        cancel: {
                            text: 'Abbruch',
                            btnClass: 'btn-red',
                            action: function () {
                                // Do nothing
                            }
                        },

                    },
                    closeIcon: true,
                    closeOnClick: 'background',
                    backgroundDismiss: true,
                });

                //alert(key + "!");
            }
        };
    }
    $.contextMenu({
        selector: ".eventelements:not(.past) .selected",
        items: menuItems
    });

    // --------------------------------------------------
    // context menu for the single cells with left click e.g Urlaub, Krank...
    $('.addzone').on('click', function (e) {
        if (e.button === 0) {
            e.preventDefault();
            $(this).contextMenu({
                x: e.pageX,
                y: e.pageY
            });
        }
    });
    $.contextMenu({
        selector: '.addzone',
        trigger: 'none',
        callback: function (key, options) {
            var m = "clicked: " + key;
            console.log(m);
            var target = this;
            add_day_event(key, target)
        },
        items: {
            "urlaub": {
                name: "Urlaub",
                icon: "fas fa-plane-departure"
            },
            "krank": {
                name: "Krank",
                icon: "fas fa-user-doctor"
            },
            "survey": {
                name: "Survey",
                icon: "fas fa-person-walking"
            },
            "streetnav": {
                name: "StreetNav",
                icon: "fas fa-road"
            },
            "callCenter": {
                name: "CallCenter",
                icon: "fas fa-headphones"
            },
            "Fehlend Unbekannt": {
                name: "Fehlend Unbekannt",
                icon: "fas fa-question"
            },
            "Backoffice": {
                name: "Backoffice",
                icon: "fas fa-laptop-house"
            },
            "Feiertag": {
                name: "Feiertag",
                icon: "fas fa-caravan"
            },

        }
    });


    // ---------------------------------------
    // Listen for click events on table rows and give ability to use CTR and SHIFT
    $('.eventelements').on('click', 'tr', function (event) {
        // If the Ctrl key is held down, toggle the selection of this row
        if (event.ctrlKey) {
            $(this).toggleClass('selected');
        }
        // If the Shift key is held down, select a range of rows
        else if (event.shiftKey && lastSelectedRow != null) {
            var startIndex = $('.eventelements tr').index(lastSelectedRow);
            var endIndex = $('.eventelements tr').index(this);
            if (endIndex < startIndex) {
                var tmp = startIndex;
                startIndex = endIndex;
                endIndex = tmp;
            }
            $('.eventelements tr').removeClass('selected');
            $('.eventelements tr').slice(startIndex, endIndex + 1).addClass('selected');
        }
        // If the row is already selected, deselect it
        else if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
        }
        // Otherwise, deselect all other rows and select this row
        else {
            $('.eventelements tr').removeClass('selected');
            $(this).addClass('selected');
        }
        lastSelectedRow = this;
    });


    // ---------------------------------------
    // tab slide function 
    $('#citytab_Moncobra').slideUp();
    $('.headtab').click(function () {
        var $panel = $(this).next();
        $('.headtab').next().not($panel).slideUp();
        $panel.slideToggle();
    });


    // ---------------------------------------
    // Name click to show appointment table
    $('.dropname').click(function () {
        var username = $(this).attr('id').replace('droptable_', '');
        $('.appointments_wrapper').each(function () {
            if ($(this).attr('id') !== username) {
                $(this).addClass('hidden');
            }
        });
        $('#' + username).removeClass('hidden');
        fix_height()

        console.log('userdest')
        console.log(user_dest)

        var rows = $('.projectlist tbody tr');

        // Find the object in the array with a "user" property equal to the clicked username
        const desiredUser = user_dest.find(obj => obj.user === username);

        // If a matching object was found, loop over the rows and update the distance value for the matching city
        if (desiredUser) {
            // Loop over all rows
            $('.projectlist tbody tr').each(function (i) {
                // Get the city value in the current row
                var city = $(this).find('td:eq(0)').text().trim();

                // Loop over the properties of the "citys" object for the clicked user
                for (const cityName in desiredUser.citys) {
                    // If the property name in the "citys" object contains the city name from the current row, update the distance value in the corresponding table cell
                    if (cityName.includes(city)) {
                        var distance = Math.round(desiredUser.citys[cityName].distance);
                        var duration = desiredUser.citys[cityName].duration;
                        var $td = $(this).find('td:eq(4)');
                        $td.text(duration);
                        $td.attr('title', 'Entfernung: ' + distance + 'km');
                        break;
                    }
                }
            });
            init_datatable(); // destroy and reload the datatable to make the travel time/distance sortable
        }

    });



    // ---------------------------------------
    // map function to show the map and init the markers
    var markers = [];
    var mapInitialized = false; // Keep track of whether the map has been initialized
    var map = null; // Initialize the map variable outside the click event listener

    $('#checkit').on('click', function (e) {

        var modulWrapperPosition = $('#weekplantable').position();
        $('#map-wrapper').css({
            'position': 'absolute',
            'left': modulWrapperPosition.left + 10,
            'top': modulWrapperPosition.top,
            'width': $('#weekplanholder').width() // new line
        }).removeClass('hidden');




        if (!mapInitialized) {
            mapInitialized = true;
            // Create the map object and add a tile layer
            var map = L.map('leaflet').setView([51.159328, 10.445940], 7);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            $.when(
                $.ajax({
                    method: "POST",
                    url: "view/load/weekplan_load.php",
                    data: {
                        func: "city_positions",
                    },
                }),
                $.ajax({
                    method: "POST",
                    url: "view/load/weekplan_load.php",
                    data: {
                        func: "user_positions",
                    },
                })
            ).done(function (cityResponse, userResponse) {
                var citypos = JSON.parse(cityResponse[0]);
                var userpos = JSON.parse(userResponse[0]);
                console.log(citypos);
                console.log("userpos");
                console.log(userpos);

                // Change the color of the default marker based on client value (case-insensitive)
                var markerOptions = L.Icon.Default.prototype.options;
                markerOptions.iconUrl = 'marker-icon.png'; // Replace with your default city marker image URL
                markerOptions.iconRetinaUrl = 'marker-icon-2x.png'; // Replace with your default city marker image URL for retina displays

                var citiesWithNullClient = ['Seelbach', 'Attenhausen', 'Pohl', 'Geisig', 'Dessighofen', 'Schweighausen', 'Oberwies'];
                for (var i = 0; i < citypos.length; i++) {
                    var city = citypos[i];
                    var cityIconUrl;
                    if (citiesWithNullClient.includes(city.city)) {
                        city.client = 'null';
                    }
                    switch (city.client.toLowerCase()) { // Convert to lowercase before comparing
                        case 'insyte':
                            cityIconUrl = 'https://crm.scan4-gmbh.de/view/images/map-marker-lightblue.png'; // Replace with your custom marker image URL for InSyte client
                            break;
                        case 'moncobra':
                            cityIconUrl = 'https://crm.scan4-gmbh.de/view/images/map-marker-orange.png'; // Replace with your custom marker image URL for MonCobra client
                            break;
                        default:
                            cityIconUrl = 'https://crm.scan4-gmbh.de/view/images/map-marker-grey.png'; // Use the default city marker image for other clients
                            break;
                    }

                    var cityIcon = L.icon({
                        iconUrl: cityIconUrl,
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [0, -35]
                    });

                    console.log('cname ' + city.city)
                    var marker = L.marker([city.lat, city.lon], { icon: cityIcon }).addTo(map);
                    markers.push(marker);
                    marker.bindPopup(city.city);
                }


                // Loop through userpos array and add markers to map
                console.log('userpos length: ' + Object.keys(userpos).length);

                Object.keys(userpos).forEach(function (key) {
                    var user = userpos[key];
                    console.log('user ' + user);
                    var userIcon = L.icon({
                        iconUrl: 'https://crm.scan4-gmbh.de/view/images/map-marker-green.png', // Replace with your custom user marker image URL
                        iconSize: [25, 41], // Replace with the size of your custom marker image
                        iconAnchor: [12, 41], // Replace with the anchor point of your custom marker image
                        popupAnchor: [0, -35] // Replace with the popup anchor point of your custom marker image
                    });
                    var marker = L.marker([user.lat, user.lon], { icon: userIcon }).addTo(map);
                    markers.push(marker);
                    marker.bindPopup(user.user + "<br>" + user.home); // Add a popup with user's name and home address
                });
            });
        } else {
            $('#map-wrapper').removeClass('hidden');
        }
    });


    // ----------------------------------------
    // map search function
    $('#mapsearchinput').on('input', function (e) {
        var searchText = $(this).val().toLowerCase();
        // resetMarkers();
        if (searchText === "") {
            markers.forEach(function (marker) {
                marker.closePopup();
            });
            return;
        }

        markers.forEach(function (marker) {
            var popupText = marker.getPopup().getContent().toLowerCase();
            if (popupText.includes(searchText)) {
                marker.openPopup();
            }
        });
    });


    // ----------------------------------------
    // map close button
    $(document).on('click', '#map-close-btn', function (e) {
        $('#map-wrapper').addClass('hidden');
    });


    // ----------------------------------------
    // map close button
    $(document).on('click', '#clearall', function (e) {

    });


    // ----------------------------------------
    // undo tracker
    $('#undoall').prop('disabled', true).addClass('disabled'); // disable on init
    var undoStack = []; // stack to keep track over the last actions for the undo feature
    $('#undoall').click(function () {
        var lastAction = undoStack.pop();
        if (undoStack.length === 0) {
            $('#undoall').prop('disabled', true).addClass('disabled');
        }
        if (lastAction) {
            if (lastAction.action === 'remove') {
                $(lastAction.target).appendTo(lastAction.parent);
            } else if (lastAction.action === 'add') {
                $(lastAction.target).remove();
            }
        }
    });

    // ----------------------------------------
    // dropzone handle
    $('.droppable').droppable({
        tolerance: 'pointer',
        accept: '.draggable, .helper, .copycell',
        drop: function (event, ui) {
            $(this).removeClass('highlight');
            console.log('dropped')
            console.log(selectedItems)

            var droptarget = this;

            if ($(droptarget).find('.dayevent').length > 0) {
                console.log('droptarget contains dayevent');
                return; // skip adding new span element and move on to the next action
            }


            selectedItems.forEach(function (item) {
                let city = item.name;
                let client = item.attribute;
                console.log(city)

                // Check if the city already exists in the drop target
                if ($(droptarget).find('.dropped').filter(function () { return $(this).text() === city }).length > 0) {
                    console.log(city + ' already exists in this drop target');
                    return; // Skip this item and move on to the next one
                }

                $(droptarget).append('<span class="dropped ' + client + '">' + city + '</span>');
                $(droptarget).closest('.droppable').removeClass('addzone'); // Remove the class to remove the ability to add day events
                $('#saveall').prop('disabled', false).removeClass('disabled');
                undoStack.push({
                    action: 'add',
                    target: $(droptarget).find('.dropped').last()[0]
                });
                $('#undoall').prop('disabled', false).removeClass('disabled');
            });



            let username;
            $(this).parents().each(function () {
                if ($(this).hasClass('tableday_row')) {
                    username = $(this).attr('id')
                    username = username.replace('droprow_', ''); // Remove "droprow_" from the ID
                    return false; // Stop searching
                }
            });
            manipulatedUsernames[username] = true;

            creatcopycell(droptarget);

        },
        over: function (event, ui) {
            $(this).addClass('highlight');
        },
        out: function (event, ui) {
            $(this).removeClass('highlight');
        }
    });



    // ----------------------------------------
    // init the dragg instance and create a ghost div

    $('.draggable').on('click', function (e) {
        var $row = $(this).closest('tr'); // get parent row element
        if (e.ctrlKey) { // if ctrl key is pressed
            $row.toggleClass('selected'); // toggle selected class on row
        } else {
            $('.selected').removeClass('selected'); // deselect all other rows
            $row.addClass('selected'); // select the row
        }
    });


    $('.draggable').draggable({
        helper: function (event) {
            // create a new helper element with selected rows
            var helper = $('<div>').addClass('selected-helper draggable');
            $('.selected').each(function () {
                // only clone the text from the first td element in the row
                var text = $(this).find('td:first-child').clone().wrap('<div>').parent().html();
                helper.append(text);
            });
            $('body').append(helper);
            return helper;
        },
        appendTo: 'body', // append the helper element to the body element
        revert: 'invalid',
        start: function (event, ui) {
            // get all currently selected rows
            selectedItems = $('.selected').map(function () {
                var name = $(this).find('.draggable').text();
                var attribute = $(this).find('.draggable').attr('data-table');
                return { name: name, attribute: attribute };
            }).get();


        },
        stop: function (event, ui) {
            // clear selected rows array
            // selectedItems = [];

        }
    });


    // ----------------------------------------
    // doubleclick remove of the dropped items. Add addzone for the day event handle
    $(document).on('dblclick', '.dropped', function () {
        var droppableZone = $(this).parent('.droppable');
        let username = '';
        $(this).parents().each(function () { // find the username
            if ($(this).hasClass('tableday_row')) {
                username = $(this).attr('id')
                username = username.replace('droprow_', ''); // Remove "droprow_" from the ID
                return false; // Stop searching
            }
        });
        let isevent = false;
        if ($(this).hasClass('dayevent')) isevent = true;
        $(this).remove();
        if (droppableZone.children().length === 2) {
            droppableZone.addClass('addzone');
        }
        console.log('length: ' + droppableZone.children().length)
        droppableZone.removeClass('dayevent_cell');
        $('#saveall').prop('disabled', false).removeClass('disabled');
        undoStack.push({
            action: 'remove',
            target: this,
            parent: droppableZone
        });
        $('#undoall').prop('disabled', false).removeClass('disabled');

        manipulatedUsernames[username] = true;

    });


    //------------------------------------------------------------------------------
    // delete all items inside when trashcan clicked
    $(document).on('click', '.deletecell', function () {
        var parentDiv = $(this).parent(); // Get the parent div
        parentDiv.empty();
        parentDiv.addClass('addzone');
        $(parentDiv).removeClass('dayevent_cell')

    });

    //------------------------------------------------------------------------------
    // copy weekplan
    var copyofweekplan = []; // declare global var
    $(document).on('click', '#copykw', function () {
        var $rows = $('.tableday_row');
        $rows.each(function (index) {
            var $rowCopy = $(this).clone(true, true); // create a deep copy of the current row and all its contents
            copyofweekplan.push($rowCopy); // add the row copy to the array of copied rows
        });
        $('#pastekw').removeClass('disabled'); // enable the "paste" button

        window.onbeforeunload = function () {
            return "Achtung es gibt ungespeicherte Änderungen!";
        };
    });


    //------------------------------------------------------------------------------
    // paste weekplan
    $(document).on('click', '#pastekw', function () {
        $('.tableday_row').remove(); // remove all rows
        $.each(copyofweekplan, function (index, row) {
            var newRow = row.clone(true, true);
            $('#weekplanholder').append(newRow);
            username = $(newRow).attr('id')
            username = username.replace('droprow_', '');
            manipulatedUsernames[username] = true;
            $('#saveall').prop('disabled', false).removeClass('disabled');
            window.onbeforeunload = null;
        });

        // $('#pastekw').addClass('disabled'); // disable the "paste" button
    });


    //------------------------------------------------------------------------------
    // reload intercept unsaved changes




    // ----------------------------------------
    // this holds the ability to use the week date input and the left,right button.
    var weekInput = $('#week');
    var prevWeekButton = $('#prevWeekButton');
    var nextWeekButton = $('#nextWeekButton');
    var dateRange = $('#dateRange');
    weekInput.trigger('change');

    // Set the initial date range display
    var currentWeek = weekInput.val();
    var startDate = getStartDate(currentWeek);
    var endDate = getEndDate(currentWeek);
    dateRange.text(startDate + ' - ' + endDate);
    load_hbg_data(currentWeek);

    // Update the date range display whenever the week input field value changes
    weekInput.on('change', function () {
        var currentWeek = weekInput.val();
        var startDate = getStartDate(currentWeek);
        var endDate = getEndDate(currentWeek);
        dateRange.text(startDate + ' - ' + endDate);
        updateColumns(startDate, endDate); //  updateColumns to match the date
        load_hbg_data(currentWeek);
        checkArrayExists()
        get_weekplan(); // load data from db to the week table
    });

    prevWeekButton.on('click', function () {
        save_plan();
        var currentWeek = weekInput.val();
        var prevWeek = decrementWeek(currentWeek);
        weekInput.val(prevWeek).trigger('change');

    });

    nextWeekButton.on('click', function () {
        save_plan();
        var currentWeek = weekInput.val();
        var nextWeek = incrementWeek(currentWeek);
        weekInput.val(nextWeek).trigger('change');

    });



    // ----------------------------------------
    // fit page height
    var height = $('#modulwrapper').height();
    height = height + 400;
    $('.body_content').css('height', height + 'px');
    fix_height(); // fix toptable height to prevent jumps


    // ----------------------------------------
    // register input change and update the day table to the selected data
    $('#revisions').on('change', function () {
        var currentWeek = $('#week').val();
        var selectedRevision = $(this).val();

        // Get the data for the selected revision from weekplan_rev
        var data = weekplan_rev[currentWeek][selectedRevision];

        // Call parse_weekplan with the selected data
        parse_weekplan(data);
    });



    // ----------------------------------------
    // init the save ability
    $('#saveall').prop('disabled', true).addClass('disabled'); // disable it on pageload as default
    $('#saveall').on('click', function () {
        if ($(this).prop('disabled')) {
            return; // Exit the function if the button is disabled
        }
        $('#saveall').prop('disabled', true).addClass('disabled');
        save_plan('save');
        console.log("manipulatedUsernames")
        console.log(manipulatedUsernames)
        save_manipulated_usernames();

        // save screenshotimage to server
        $('.plaininfo').addClass('hidden'); // hide so others dont see appt number
        const week = $('#week').val();
        var htmlContent = $('#weekplanholder')[0];
        html2canvas(htmlContent).then(function (canvas) {
            var imgData = canvas.toDataURL(); // get the base64-encoded image data
            $.ajax({
                method: "POST",
                url: "view/load/weekplan_load.php",
                data: {
                    func: "save_img",
                    data: imgData,
                    week: week,
                },
            }).done(function (response) {
                $('.plaininfo').removeClass('hidden');
            });
        });
    });





    // ----------------------------------------------------------------------------------------
    // load the hbgs for the toptable if they are not already loaded




}); // end of document ready
//////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////

// ----------------------------------------------------------------------------------------
// create a userlist
function get_userlist() {
    var usernames = [];
    $(".tableday_row").each(function () {
        var text = $(this).attr('id');
        var username = text.replace('droprow_', ''); // Remove "droprow_" from the ID
        usernames.push(username);
    });

    return usernames;
}


// ========================================================
// create the projectlist to match citys. This is used to generate the CSS class akording to Insyte / Moncobra
function processTable(tableId, projectName, projectList) {
    const table = document.getElementById(tableId);
    for (let i = 0; i < table.rows.length; i++) {
        const cell = table.rows[i].cells[0];
        projectList[projectName].push(cell.textContent);
    }
}



// ------------------------------------------------
// Init Datatables for the Projectlist

function init_datatable() {
    $('.projecttable').DataTable().destroy();
    $('.projecttable').DataTable({
        scrollX: true,

        "bLengthChange": false,
        autoWidth: true,
        paging: false,

    });
}

function makeCopyCellDraggable() {
    $('.copycell').each(function () {
        $(this).draggable({
            helper: function (event) {
                selectedItems = []; // clear elements
                // create a new helper element with selected rows
                var $col2 = $(this).closest('.col-2'); // get parent col-2 element
                var $spans = $col2.find('span'); // get all spans inside col-2 element

                // create a new helper element with cloned spans
                var helper = $('<div>').addClass('selected-helper draggable');
                $spans.each(function () {
                    var span = $(this).clone();
                    helper.append(span);

                    // populate selectedItems with name and attribute values
                    var name = span.text();
                    var attribute = span.attr('class').replace('dropped', '').trim();
                    selectedItems.push({ name: name, attribute: attribute });
                    console.log("name " + name)
                    console.log(selectedItems)
                });

                $('body').append(helper);

                // set helper position to mouse cursor
                helper.css({
                    position: 'fixed',
                    left: event.pageX,
                    top: event.pageY
                });
                $('body').append(helper);
                return helper;
            },
            appendTo: 'body', // append the helper element to the body element
            revert: 'invalid',
        });
    });
}

function creatcopycell(target) {
    createdeletecell(target)
    $(target).find('.copycell').remove();
    let copycell = $('<div class="copycell"><i class="ri-file-copy-line"></i></div>');  // create and add new "copycell" element to the drop zone
    $(target).prepend(copycell);
    makeCopyCellDraggable(copycell); // make the new "copycell" element draggable
}

function createdeletecell(target) {
    $(target).find('.deletecell').remove();
    let copycell = $('<div class="deletecell"><i class="ri-delete-bin-line"></i></i></div>');  // create and add new "copycell" element to the drop zone
    $(target).prepend(copycell);
}

// ----------------------------------------------------------------------
// change the hbg to another username. This gets called from the contextmenu
function change_appointmentowner(user, data) {
    $.ajax({
        method: "POST",
        url: "view/load/weekplan_load.php",
        data: {
            func: "change_appointments",
            user: user,
            data: data,
        },
    }).done(function (response) {
        console.log('owner changed')
        console.log(response)
        currentWeek = $('#week').val();
        hbgDataCache[currentWeek] = undefined;
        load_hbg_data(currentWeek);
    });
}


// ----------------------------------------
// Add day event like urlaub, krank to the cell
function add_day_event(event, target) {
    let username = $(target).parent().attr('id'); // Get the ID of the parent element
    username = username.replace('droprow_', ''); // Remove "droprow_" from the ID
    target.addClass('dayevent_cell')
    target.append('<span class="dayevent dropped">' + event.charAt(0).toUpperCase() + event.slice(1) + '</span>');
    target.removeClass('addzone'); // Remove the class to remove the ability to add day events
    $('#saveall').prop('disabled', false).removeClass('disabled');
    manipulatedUsernames[username] = true;
    creatcopycell(target);
}

// ----------------------------------------
// calc the toptable height to prevent jumps
function fix_height() {
    var $elements = $('.appointments_wrapper');
    var maxHeight = 0;
    $elements.each(function () {
        var height = $(this).height();
        //console.log('height: ' + height)
        if (height > maxHeight) {
            maxHeight = height;
        }
    });
    $('#toptable').height(maxHeight);
}


function incrementWeek(weekString) {
    var year = parseInt(weekString.substring(0, 4));
    var week = parseInt(weekString.substring(6));
    if (week == 52) {
        year++;
        week = 1;
    } else {
        week++;
    }
    return year + '-W' + week.toString().padStart(2, '0');
}

function decrementWeek(weekString) {
    var year = parseInt(weekString.substring(0, 4));
    var week = parseInt(weekString.substring(6));
    if (week == 1) {
        year--;
        week = 52;
    } else {
        week--;
    }
    return year + '-W' + week.toString().padStart(2, '0');
}

function getStartDate(weekString) {
    var year = parseInt(weekString.substring(0, 4));
    var week = parseInt(weekString.substring(6));
    var jan1 = new Date(year, 0, 1);
    var dayOffset = (jan1.getDay() + 6) % 7;
    var startDate = new Date(year, 0, 1 + (7 - dayOffset));
    startDate.setDate(startDate.getDate() + (week - 1) * 7);
    return ('0' + startDate.getDate()).slice(-2) + '.' + ('0' + (startDate.getMonth() + 1)).slice(-2) + '.' + startDate.getFullYear();
}

function getEndDate(weekString) {
    var year = parseInt(weekString.substring(0, 4));
    var week = parseInt(weekString.substring(6));
    var jan1 = new Date(year, 0, 1);
    var dayOffset = (jan1.getDay() + 6) % 7;
    var endDate = new Date(year, 0, 1 + (7 - dayOffset));
    endDate.setDate(endDate.getDate() + (week - 1) * 7 + 6);
    return ('0' + endDate.getDate()).slice(-2) + '.' + ('0' + (endDate.getMonth() + 1)).slice(-2) + '.' + endDate.getFullYear();
}


// ----------------------------------------
// this calls the db to retrieve the latest information about each username and currentweek for the weekplan
function get_weekplan() {
    var currentWeek = $('#week').val();

    if (weekplan[currentWeek]) {
        parse_weekplan(weekplan[currentWeek]);  // Data for the current week is already present in weekplan, so call parse_weekplan with the existing data
    } else {

        // Data for the current week is not present in weekplan, so make an AJAX request to get it
        $.ajax({
            method: "POST",
            url: "view/load/weekplan_load.php",
            data: {
                func: "load_weekplan_rev",
                week: currentWeek
            },
        }).done(function (response) {
            drop_tabledays(); // clean all cells to start with clean cells
            //console.log('@@@@@@@@' + response);
            weekplan_rev[currentWeek] = JSON.parse(response)
            console.log('rev')
            console.log(weekplan_rev)
            data = weekplan_rev[currentWeek];

            var revisionsSelect = $('#revisions');
            revisionsSelect.empty();

            // sort the keys in descending order
            var sortedKeys = Object.keys(data).sort(function (a, b) {
                return new Date('1970/01/01 ' + b) - new Date('1970/01/01 ' + a);
            });

            // create and append options in sorted order with weekname
            sortedKeys.forEach(function (key, index) {
                var optionText = currentWeek + ' // ' + key;
                var option = $('<option>').val(key).text(optionText);
                revisionsSelect.append(option);
                // add the latest array to weekplan
                if (index === 0) {
                    weekplan[currentWeek] = data[key];
                    console.log('pushed weekplan')
                    console.log(weekplan[currentWeek])
                    parse_weekplan(weekplan[currentWeek]);
                }
            });
        });
    }
}



function drop_tabledays() {
    $('.tableday_row').each(function () {
        const row = $(this);
        row.find('.tableday').html(null);
    });
    check_dayeventClass();
}

// ----------------------------------------------------------------------------------    // fits the received data into the cells and fix classes
function parse_weekplan(data) {

    drop_tabledays(); // drop all table cells 

    for (let i = 0; i < data.length; i++) {
        const {
            username,
            week,
            montag,
            dienstag,
            mittwoch,
            donnerstag,
            freitag,
            samstag
        } = data[i];
        if (username) {
            //console.log(`${username} - ${week} - ${montag} - ${dienstag} - ${mittwoch} - ${donnerstag} - ${freitag}`);
            const row = $('#droprow_' + username);
            row.find('.col-2:nth-child(2)').html(getSpans(montag));
            creatcopycell(row.find('.col-2:nth-child(2)'));
            row.find('.col-2:nth-child(3)').html(getSpans(dienstag));
            creatcopycell(row.find('.col-2:nth-child(3)'));
            row.find('.col-2:nth-child(4)').html(getSpans(mittwoch));
            creatcopycell(row.find('.col-2:nth-child(4)'));
            row.find('.col-2:nth-child(5)').html(getSpans(donnerstag));
            creatcopycell(row.find('.col-2:nth-child(5)'));
            row.find('.col-2:nth-child(6)').html(getSpans(freitag));
            creatcopycell(row.find('.col-2:nth-child(6)'));
            row.find('.col-2:nth-child(7)').html(getSpans(samstag));
            creatcopycell(row.find('.col-2:nth-child(7)'));
        }
    }
    check_dayeventClass();
}


// ----------------------------------------------------------------------------------
// loop over all dropcells and add / remove the correct classes
function check_dayeventClass() {
    $('.tableday_row').each(function () {
        $(this).find('.col-2').each(function () {
            // check if col-2 contains a span
            $(this).removeClass('addzone dayevent_cell');

            if ($(this).find('span').length > 0) {

                // check if the span has class "dayevent"
                if ($(this).find('span.dayevent').length > 0) {
                    $(this).addClass('dayevent_cell');
                    $(this).removeClass('addzone');
                } else {

                }

            } else {
                $(this).addClass('addzone');

            }
        });
    });
}



// -------------------------------------------------------------------------
// function to split the events passed from the db to the target spans
function getSpans(dayValue) {

    if (!dayValue) return ''; // return empty string if dayValue is undefined or empty

    let spans = [];

    if (eventList.includes(dayValue)) { // if dayValue is inside of the dayevents
        const span = `<span class="dayevent dropped">${dayValue.trim()}</span>`;
        spans.push(span);
    } else if (dayValue.includes(';')) {
        const values = dayValue.split(';'); // split values by semicolon

        values.forEach(value => {
            const span = getSpans(value.trim());
            spans.push(span);
        });
    } else {
        let className = 'dropped';
        dayValue = dayValue.trim();
        Object.keys(projectlist).forEach(projectName => {
            const projectCities = projectlist[projectName];

            if (projectCities.includes(dayValue)) {
                className += ` ${projectName.charAt(0).toUpperCase() + projectName.slice(1)}`;
            }
        });

        const span = `<span class="${className}">${dayValue.trim()}</span>`;
        spans.push(span);
    }

    return spans.join(''); // join the spans together into a string and return it
}




// ----------------------------------------------------------------------------------------
// saves all values to the db
function save_plan(state = 'temp') {


    const weekDate = $('#week').val();
    weekplan[weekDate] = undefined;
    weekplan_rev[weekDate] = undefined;

    let data = {};

    $('.tableday_row').each(function () {
        const username = $(this).attr('id').replace('droprow_', '');
        const children = $(this).children();

        if (!data[username]) {
            data[username] = {};
        }

        children.not(':first-child').each(function () {
            const dayIndex = $(this).index() - 1;
            const items = [];
            const spans = $(this).find('span');

            spans.each(function () {
                const cityName = $(this).text();
                items.push(cityName);
            });

            if (!data[username][dayIndex]) {
                data[username][dayIndex] = {
                    spans: []
                };
            }

            data[username][dayIndex].spans = items;
        });
    });
    if (state !== 'temp') {

        $.ajax({
            method: "POST",
            url: "view/load/weekplan_load.php",
            data: {
                func: "safe_weekplan",
                week: weekDate,
                data: JSON.stringify(data),
            },
        }).done(function (response) {
            get_weekplan(); // reload weekplan data ro update data like revision handler
        });
    }
}




function load_hbg_data(date) {
    console.log('load_hbg_data from: ' + date)
    if (hbgDataCache[date]) {
        updateHtml(hbgDataCache[date]);
    } else {
        $('#loadwrapper').removeClass('hidden')
        $.ajax({
            method: "POST",
            url: "view/load/weekplan_load.php",
            data: {
                func: "load_hbg_data",
                date: date,
            },
        }).done(function (response) {
            //console.log('loaded_hbg_data')
            //console.log(response);
            var decodedResponse = JSON.parse(response);
            hbgDataCache[date] = decodedResponse;
            updateHtml(decodedResponse);
            $('#loadwrapper').addClass('hidden')
        });
    }
}

function updateHtml(data) {
    var currentWeek = $('#week').val();
    var startDate = getStartDate(currentWeek);
    $('.dropname').each(function () {
        var name = $(this).attr('id').replace('droptable_', '');
        var count = 0;
        if (data[name]) {
            count = data[name]['total'] || data[name];
        }
        $(this).find('.plaininfo').text(count);
        $(this).attr('data-count', count);

    });

    // update the element tables to match the present day
    // set the id of each table to the weekday in the format 2023-03-14
    var dateParts = startDate.split('.');
    var year = dateParts[2];
    var month = dateParts[1];
    var day = dateParts[0];
    var dateFormatted = year + '-' + month + '-' + day;
    var date = new Date(year, month - 1, day); // Note: month is 0-indexed in Date object
    $('.appointments_wrapper').each(function () {
        var tables = $(this).find('table.eventelements');
        tables.each(function (i) {
            var tableDate = new Date(date.getTime());
            tableDate.setDate(date.getDate() + i);
            var tableDateFormatted = formatDate(tableDate);
            var spanDateFormatted = ('0' + tableDate.getDate()).slice(-2) + '.' + ('0' + (tableDate.getMonth() + 1)).slice(-2) + '.' + ('' + tableDate.getFullYear()).slice(-2);
            $(this).attr('id', (i === 0) ? dateFormatted : tableDateFormatted); // Set the ID based on the index
            $(this).parent().find('.weekday_info').text(spanDateFormatted);
        });
    });

    // insert the rows to the top tables
    const dateRegExp = /^\d{4}-\d{2}-\d{2}$/;
    $('.appointments_wrapper').each(function () {
        const parent = this;
        const name = $(this).attr('id');
        const tables = $(this).find('table.eventelements');
        tables.empty();
        if (data[name]) {
            for (let date in data[name]) {
                if (dateRegExp.test(date)) {
                    const appointments = data[name][date];
                    for (let uid in appointments) { // uid equals key in date
                        const appointment = appointments[uid];


                        if (uid === 'anfahrt') {
                            const row = $('<tr>', {
                                'id': appointment.uid + '_anfahrt',
                                'class': 'draggable_appt anfahrt',
                                'data-appointment': JSON.stringify(appointment)
                            });
                            const timeCell = $('<td rowspan="2">').html(appointment.start + '<br>' + appointment.end);

                            const cityCell = $('<td rowspan="2">').text('Anfahrt ' + appointment.time);
                            row.append(timeCell);
                            row.append(cityCell);
                            const table = tables.filter('[id="' + date + '"]');
                            table.append(row);


                        } else {
                            const location = appointment.city + ', ' + appointment.street + ' ' + appointment.streetnumber + appointment.streetnumberadd;
                            const row = $('<tr>', {
                                'id': appointment.uid,
                                'class': 'draggable_appt',
                                'data-appointment': JSON.stringify(appointment)
                            });
                            const timeCell = $('<td>').text(appointment.time);
                            const cityCell = $('<td>').text(appointment.city);
                            row.append(timeCell);
                            row.append(cityCell);
                            const table = tables.filter('[id="' + date + '"]');
                            table.append(row);
                        }

                    }
                }
            }
        }
    });





    //---------------------------
    // loop over the tables to count the rows and update the daily count
    $('.eventelements_wrapper').each(function () {
        var rowCount = $(this).find('.eventelements tr').length;
        $(this).find('.weekday_infocount').text(rowCount)
    });
    //---------------------------
    // loop over all table counts to update the total of the table
    $('.appointments_wrapper').each(function () {
        var counter = 0;
        $(this).find('.weekday_infocount').each(function () {
            counter += parseInt($(this).text())
        });
        $(this).find('.user_week_totals').text(counter)
        // console.log('count:' + counter)
    });
    fix_height(); // fix toptable height to prevent jumps



    // ----------------------------------------
    // Loop over all tables to set them into the past if neccessary
    var currentDate = new Date();
    var yesterdayDate = new Date();
    yesterdayDate.setDate(currentDate.getDate() - 1);

    $(".eventelements").each(function () {
        var tableId = $(this).attr("id");
        var tableDate = new Date(tableId);
        if (tableDate < yesterdayDate) {
            $(this).addClass("past");
        } else {
            $(this).removeClass("past");
        }
    });

}





function formatDate(date) {
    var year = date.getFullYear();
    var month = ('0' + (date.getMonth() + 1)).slice(-2); // Add leading zero if necessary
    var day = ('0' + date.getDate()).slice(-2); // Add leading zero if necessary
    return year + '-' + month + '-' + day;
}

function updateColumns(startDate, endDate) {
    // Update the columns based on the selected date range
    var currentDate = new Date(startDate); // startDate === '20.03.2023'
    var daysOfWeek = ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'];
    $('#columnHeaders .col').each(function (index, element) {
        if (index > 0) { // Skip the first column (Name)
            // increment the startDate by index 
            let dateString = startDate;
            let parts = dateString.split(".");
            let date = new Date(parts[2], parts[1] - 1, parts[0]);
            date.setDate(date.getDate() + index - 1); // Increment the date by one day
            let day = date.getDate().toString().padStart(2, '0'); // Convert the day to a string and add a leading zero if necessary
            let month = (date.getMonth() + 1).toString().padStart(2, '0'); // Convert the month to a string and add a leading zero if necessary
            let newDateString = `${day}.${month}.`; // Convert the date back to a string with only day and month
            //console.log(newDateString); // Output: "04.04"
            // update the col
            let colString = daysOfWeek[index - 1] + ' ' + newDateString
            $(this).text(colString)
        }
    });


}

function checkArrayExists() {
    const weekDate = $('#week').val().trim();
    const username = 'user1'; // replace with the username you want to retrieve data for

    // retrieve saved data for the given week and user
    const savedData = plandata[weekDate] && plandata[weekDate][username] || {};

    if (Object.keys(savedData).length > 0) {
        console.log('Data already present for ' + username + ' in week ' + weekDate);

        // loop through each row and cell to replace the cell contents with the saved data
        $('.tableday_row#droprow_' + username).children().not(':first-child').each(function () {
            const childIndex = $(this).index() - 1;
            const spans = $(this).find('span');
            const savedSpanTexts = savedData[childIndex] || [];
            spans.each(function (i) {
                const savedSpanText = savedSpanTexts[i] || '';
                $(this).text(savedSpanText);
            });
        });
    } else {
        console.log('No data present for ' + username + ' in week ' + weekDate);

        // loop through each row and cell to remove the cell contents
        $('.tableday_row#droprow_' + username).children().not(':first-child').each(function () {
            const spans = $(this).find('span');
            spans.each(function () {
                $(this).text('');
            });
        });
    }
}


function getDayDate(dayIndex, week) {
    const year = parseInt(week.slice(0, 4), 10);
    const weekNumber = parseInt(week.slice(6), 10);
    const referenceDate = new Date(year, 0, 1);
    const weekOffset = (weekNumber - 1) * 7;
    const dayOffset = dayIndex + 1;

    referenceDate.setDate(referenceDate.getDate() + weekOffset + dayOffset);

    const pad = (number) => number.toString().padStart(2, '0');
    const formattedDate = `${referenceDate.getFullYear()}-${pad(referenceDate.getMonth() + 1)}-${pad(referenceDate.getDate())}`;

    return formattedDate;
}


// ----------------------------------------
// function to increment a duration in the format 1:46 to add 30mins and round to the NEXT 15min, not the nearest
function increment_duration(shortestDuration) {
    // Assuming shortestDuration is in the format "hh:mmh"
    let [hours, minutes] = shortestDuration.split(':');
    minutes = parseInt(minutes) + 0; // Add 30 minutes
    hours = parseInt(hours) + Math.floor(minutes / 60); // Add any extra hours from minutes
    minutes = minutes % 60; // Get the remaining minutes after adding extra hours
    // Round up to the next 15 minutes
    minutes = Math.ceil(minutes / 15) * 15;
    // Handle edge case where rounding causes minutes to roll over to 60
    if (minutes === 60) {
        minutes = 0;
        hours++;
    }
    // Format the result as "hh:mmh"
    const roundedDuration = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}h`;
    return roundedDuration;
}

// ----------------------------------------
// function to push the day blocker to the nextcloud
function distance_writetocal(username, day, time, location = null) {
    var currentWeek = $('#week').val();
    console.log('dayindex ' + day + ' week ' + currentWeek + ' durr rounded ' + time)
    const date = getDayDate(day, currentWeek);
    console.log(date); // should output "2023-03-28" or similar
    $.ajax({
        method: "POST",
        url: "view/load/weekplan_load.php",
        data: {
            func: "safe_distance",
            date: date,
            user: username,
            time: time,
            location: location,
        },
    }).done(function (response) {
        console.log('safe_distance')
        console.log(response);

    });

}




function save_manipulated_usernames() {
    // Loop through the manipulatedUsernames object and handle only the ones that have been manipulated
    for (let username in manipulatedUsernames) {
        if (manipulatedUsernames[username]) {
            console.log('loop manipulated user ' + username)
            let tableday_row = $('#droprow_' + username);
            let desiredUser = user_dest.find(obj => obj.user === username);
            let children = tableday_row.children();
            children.not(':first-child').each(function () {
                let dayIndex = $(this).index() - 1;
                let items = [];
                let spans = $(this).find('span');
                spans.each(function () {
                    let spanText = $(this).text();
                    let regex = /^([^0-9]*)/;
                    let match = regex.exec(spanText);
                    let cityName = match[1];
                    items.push(cityName);
                });
                if (items.length > 0 && dayIndex < 4 && !eventList.includes(items[0])) {
                    let shortestDuration = null;
                    let shortestDurationCity = null;
                    items.forEach(item => {
                        const cityNames = Object.keys(desiredUser.citys);
                        const matchingCity = cityNames.find(city => city.includes(item));
                        if (matchingCity) {
                            const cityDetails = desiredUser.citys[matchingCity];
                            if (!shortestDuration || cityDetails.duration < shortestDuration) {
                                shortestDuration = cityDetails.duration;
                                shortestDurationCity = matchingCity;
                            }
                        }
                    });
                    if (shortestDuration) {
                        console.log(`Shortest duration: ${shortestDuration} in ${shortestDurationCity}`);
                        const rounded = increment_duration(shortestDuration)
                        console.log('Rounded duration: ' + rounded)
                        distance_writetocal(username, dayIndex, rounded, shortestDurationCity);
                        // Skip the remaining code for the current user and move on to the next user
                        return false;
                    }
                } else if (items.length > 0 && eventList.includes(items[0])) {
                    console.log(username + ' Event is found')
                    console.log(items);
                }
            });
            // Reset the value to false to indicate that it has been handled
            manipulatedUsernames[username] = false;
        }
    }
}
