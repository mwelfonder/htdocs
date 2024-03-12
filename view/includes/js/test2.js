


$(document).ready(function () {

    //------------------------------------------------------------------------------------
    // sort the appt elements in the hidden div into the correct hour slots
    $('.homeidday').each(function () {
        var slots = $(this).find('.hour-slot');
        $(this).find('.homeid-link').each(function () {
            var appointmentTime = $(this).find('.kwview').data('time');
            var hour = parseInt(appointmentTime.split(':')[0]);
            var slot = slots.filter('[data-hour="' + hour + '"]');
            slot.append($(this));
        });
    });


    //------------------------------------------------------------------------------------
    // turn route visibile on/off with the car switch
    $('.switch input').click(function () {
        if ($(this).is(':checked')) {
            // The switch is checked
            //console.log('Switch is checked');
        } else {
            // The switch is unchecked
            //console.log('Switch is unchecked');
            map_showroute('none', 'none') // pass invalid username to torn off all routes
        }
    });


    window.map = L.map('leaflet').setView([51.159328, 10.445940], 7);
    //L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    //L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
    window.leaflet_maplayer = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
        maxZoom: 19,
        preferCanvas: true,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);
    window.layer_gstreet = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
    });
    window.layer_gsatelite = L.tileLayer('https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
        maxZoom: 19,
    });

    const screenHeight = $(window).height();

    let isStatsAtBottom = false;
    const $userOverviewStats = $('#useroverviewstats');
    const normalTopOffset = $userOverviewStats.offset().top;

    $('.pagecontenttitle,.ri-map-2-line.hovericon').click(function () {
        let username = null;
        username = $(this).parents().find('.userbox').attr('id');
        //console.log('username' + username)
        toggleStatsPosition()
    });

    function toggleStatsPosition() {
        const screenHeight = $(window).height();
        const topOffset = screenHeight - 25;

        if (isStatsAtBottom) {
            $userOverviewStats.animate({
                top: normalTopOffset
            }, 500);
            isStatsAtBottom = false;
        } else {
            $userOverviewStats.css({
                position: 'absolute'
            }).animate({
                top: topOffset
            }, 500);
            isStatsAtBottom = true;
        }
    }
    // -------------------------------------------------------------------------
    // get the highest outer width and update all to the same
    var highestWidth = 0;
    $('.multiboxtypo.progressbarwrapper.small').each(function () {
        var outerWidth = $(this).outerWidth();
        if (outerWidth > highestWidth) {
            highestWidth = outerWidth;
        }
    });
    $('.multiboxtypo.progressbarwrapper.small').outerWidth(highestWidth);




    $('.multiboxopt.details.detailkw').on('click', function () {
        var col2 = $(this).parents('.userbox');
        var username = col2.attr('id');
        var kwclass = $(this).attr('class');
        var pattern = /kw\d+/;
        var match = kwclass.match(pattern);
        var kwclass = match[0];
        //console.log(username)



        var prevCol2 = col2.prevAll('.col-2.multiboxwrapper');
        var prevCol2Count = prevCol2.length;

        var pos = 0;
        if (prevCol2Count < 5) {
            pos = 4;
        } else if (prevCol2Count < 10) {
            pos = 9;
        } else if (prevCol2Count < 15) {
            pos = 14;
        } else if (prevCol2Count < 20) {
            pos = 19;
        } else if (prevCol2Count < 25) {
            pos = 24;
        } else if (prevCol2Count < 30) {
            pos = 29;
        }
        //console.log(prevCol2Count + ' & ' + pos);

        // remove all existing elements with the specified class
        var elementsToRemove = $('.multiboxwrapper.marginbox.kwdetails');
        if (elementsToRemove.length > 0) {
            elementsToRemove.slideUp(function () {
                elementsToRemove.remove();
            });
        }

        const widths = $('.multiboxwrapper.marginbox.userbox:lt(5)').map(function () {
            return $(this).outerWidth();
        }).get();

        const totalWidth = widths.reduce(function (a, b) {
            return a + b;
        });


        const col2AtIndex = $('.multiboxwrapper.userbox').eq(pos);
        const boxContents = $('.hiddenstats');
        const boxContent = boxContents.filter('#' + username).find('#kalendarweek\\ ' + kwclass).html();

        var newDiv = $('<div>').addClass('col-12 multiboxwrapper marginbox kwdetails').html(boxContent).hide().css({
            width: totalWidth + 'px',
            'max-width': totalWidth + 'px'
        });
        if (col2AtIndex.length) {
            newDiv.insertAfter(col2AtIndex);
        } else {
            var lastCol2 = $('.col-2.multiboxwrapper.userbox').last();
            newDiv.insertAfter(lastCol2);
        }
        newDiv.slideDown();




        //console.log('kwis: ' + kwclass)
        var thisdates = getDatesFromCalendarWeek(kwclass);
        //console.log(thisdates)
        let currentIndex = 0;
        for (const date of thisdates) {
            const formattedDate = formatDate(date);
            const dateBtn = $('#selectweekdays .date-btn:eq(' + currentIndex + ')');
            const originalText = dateBtn.text().substr(0, 3);
            const updatedText = originalText + formattedDate;
            dateBtn.text(updatedText);
            dateBtn.data('value', date); // store Y-m-d to the element
            dateBtn.data('user', username); // store Y-m-d to the element
            currentIndex++;
        }


        var dayIndex = slot_finddayindex(newDiv); // get the first day with openslots
        console.log('dayIndex is:' + dayIndex)
        var openSlot = slot_findslotindex(newDiv);

        var prevHomeid = {
            lat: 0,
            lon: 0
        };
        if (openSlot && openSlot.child !== undefined) {
            var childElement = openSlot.slot.find('.homeid-link');

            if (childElement.length > 0) {
                console.log('slotIndex ' + openSlot.slotIndex)
                var dataAttribute = childElement.find('p').attr('data');
                console.log(dataAttribute);

                if (dataAttribute) {
                    var dataObject = JSON.parse(dataAttribute);
                    var latitude = dataObject.lat;
                    var longitude = dataObject.lon;
                    prevHomeid.lat = latitude;
                    prevHomeid.lon = longitude;

                    console.log('Latitude:', latitude);
                    console.log('Longitude:', longitude);
                } else {
                    console.log('Invalid data attribute');
                }
            } else {
                console.log('No child element found in the open slot ' + openSlot.slotIndex);
            }
        } else {
            console.log('No open slot found between hours 8 and 20');
        }


        setTimeout(function () {
            if (dayIndex !== -1) {
                $('#selectweekdays .date-btn:eq(' + dayIndex + ')').click();
            } else {
                $('.date-btn').removeClass('selected');
            }
        }, 200);

        map_create_hbgmarker(username, kwclass);
        map_create_routings(username, kwclass);

        console.log('prevHomeid.lat ' + prevHomeid.lat + ' // ' + 'prevHomeid.lon ' + prevHomeid.lon)
        searchNearestMarker(prevHomeid.lat, prevHomeid.lon)


    });



    $('.mpbtn.mapchange').on('click', function () {
        if ($(this).hasClass('satelite')) {
            $(this).removeClass('satelite');
            $(this).addClass('street');
            //console.log('switch to satelite')
            leaflet_maplayer.setUrl(window.layer_gsatelite._url);
        } else {
            $(this).removeClass('street');
            $(this).addClass('satelite');
            leaflet_maplayer.setUrl(window.layer_gstreet._url);
        }
    });

    // -----------------------------------------------------------------------
    // add select to days
    $('.date-btn').click(function () {
        $('.date-btn').removeClass('selected');
        $(this).addClass('selected');
        let selectedDate = $(this).data('value');
        let selectedUser = $(this).data('user');
        ////console.log('datalog:' + selectedDate); // log the data value
        ////console.log('datalog:' + selectedUser); // log the data value

        map_removeallmarkers();
        map_showroute(selectedUser, selectedDate)
        showMarkersForSelectedUserAndDate(selectedUser, selectedDate);
        //
    });




    displayMarkers(json_homeids);
}); // --------------------------------------------------------------------- end of document ready

var markersOpenHomeid = [];
function displayMarkers(jsonData) { // display all openhomeids on map
    console.log('called')
    var maprenderer = L.canvas({
        padding: 0.5
    });
    Object.values(jsonData).forEach(function (element) {
        var lat = parseFloat(element.lat);
        var lon = parseFloat(element.lon);
        if (!isNaN(lat) && !isNaN(lon)) {
            var latlng = [lat, lon];

            var circleMarker = L.circleMarker(latlng, {
                renderer: maprenderer,
                color: '#2196F3',
                markerDate: element.date
            }).addTo(map);
            let popupText = element.homeid;
            circleMarker.bindPopup(popupText);
            markersOpenHomeid.push(circleMarker);
        }
    });
}

function searchNearestMarker(lat, lon) {
    var nearestMarker = null;
    var nearestDistance = Infinity;

    // Iterate through each marker in the mapMarkers array
    markersOpenHomeid.forEach(function (marker) {
        var markerLatLng = marker.getLatLng();
        var distance = markerLatLng.distanceTo([lat, lon]);

        if (distance < nearestDistance) {
            nearestMarker = marker;
            nearestDistance = distance;
        }
    });

    if (nearestMarker) {
        // Create a custom icon for the new marker
        var newIcon = L.icon({
            iconUrl: 'https://crm.scan4-gmbh.de/view/images/map-marker-orange.png', // Specify the path to your custom image
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [0, -35]
        });

        // Create a new marker with the custom icon
        var newMarker = L.marker([lat, lon], { icon: newIcon }).addTo(map);

        // Bind a popup for the new marker
        newMarker.bindPopup('Start');

        // Create a marker for the found nearest lat lon
        var nearestLatLng = nearestMarker.getLatLng();
        var nearestMarker = L.marker(nearestLatLng).addTo(map);

        // Bind a popup for the nearest marker
        nearestMarker.bindPopup('Nearest Marker');

        return nearestMarker;
    }

    return null; // No existing markers found
}



function showMarkersForSelectedUserAndDate(selectedUser, selectedDate) {
    // Loop through all userGroups
    let flownToMarker = false; // Add this flag variable
    for (var user in userGroups) {
        if (user === selectedUser) {
            userGroups[user].eachLayer(function (kwLayer) {
                // Loop through each calendar week layer in the user group
                kwLayer.eachLayer(function (dateLayer) {
                    // Check if any markers inside the dateLayer match the selectedDate
                    let foundLayer = false;
                    dateLayer.eachLayer(function (marker) {
                        if (marker.options.markerDate === selectedDate) {
                            foundLayer = true;
                            // Fly to the first found marker only
                            if (!flownToMarker) {
                                map.flyTo(marker.getLatLng(), 12, {
                                    animate: true,
                                    duration: 0.5
                                });
                                flownToMarker = true;
                            }
                        }
                    });
                    if (foundLayer) {
                        dateLayer.addTo(map);
                    } else {
                        dateLayer.remove();
                    }
                });
            });
        }
    }
}



function map_removeallmarkers() { // loop through all marker layers and remove them from the map
    for (var user in userGroups) {
        userGroups[user].eachLayer(function (kwLayer) {
            kwLayer.eachLayer(function (dateLayer) {
                // Remove individual markers
                dateLayer.eachLayer(function (marker) {
                    marker.remove();
                });

                // Remove dateLayer
                dateLayer.remove();
            });
        });
    }
}


function isValidDate(dateString) {
    const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
    return dateRegex.test(dateString);
}


const routePolylines = {}; // store polylines
const routeMarkers = {}; // store route markers
var processedRoutes = {}; // store processed routes for user and kw


function map_create_routings(selectedUser, kwclass) {
    //console.log('processedRoutes')
    //console.log(processedRoutes)
    const userKwPair = `${selectedUser}_${kwclass}`;

    // Check if the user and kwclass pair has already been processed
    if (processedRoutes.hasOwnProperty(userKwPair)) {
        //console.log(`Route for user ${selectedUser} and kwclass ${kwclass} has already been processed.`);
        return; // Exit the function
    } else {
        //console.log('userKwPair not found ' + userKwPair)
        processedRoutes[userKwPair] = true;
    }
    //console.log('lallala------------------------kaksdakdka')
    const userData = json_begeher[selectedUser][kwclass];
    for (const date in userData) {
        if (userData.hasOwnProperty(date) && isValidDate(date)) {
            const elements = userData[date];
            const latLonArray = [];
            for (const element of elements) {
                const {
                    lat,
                    lon
                } = element;
                latLonArray.push({
                    lat,
                    lon
                });
                ////console.log(`Latitude: ${lat}, Longitude: ${lon}`);
            }
            getRouteInformation(latLonArray, date, selectedUser);
        }
    }
}


function getRouteInformation(latLonArray, date, selectedUser) {
    const coordinates = latLonArray.map(({ lat, lon }) => `${lat},${lon}`).join('&point=');

    const apiUrl = `http://49.12.77.77:8989/route?point=${coordinates}&profile=car&layer=OpenStreetMap`;
    fetch(apiUrl)
        .then(response => response.json())
        .then(data => {
            const { paths } = data;
            console.log(data)
            if (paths && paths.length > 0) {
                const encodedPolyline = paths[0].points;
                const routedistance = paths[0].distance / 1000;
                console.log('routedistance ' + routedistance)
                const coordinates = polyline.toGeoJSON(encodedPolyline).coordinates;
                const latLngs = coordinates.map(coord => L.latLng(coord[1], coord[0]));
                const route = L.polyline(latLngs, { color: 'blue' });

                const key = `${selectedUser}_${date}`;
                routePolylines[key] = route; // store polyline to obj holder

                const markers = [];
                const markerOffsets = {}; // Store marker offsets by position

                latLonArray.forEach(({ lat, lon }, index) => {
                    let offset = 0; // Offset for the current marker

                    // Check if there is already a marker at this position
                    if (markerOffsets.hasOwnProperty(`${lat}_${lon}`)) {
                        offset = (markerOffsets[`${lat}_${lon}`] + 1) * 10; // Adjust the offset
                        markerOffsets[`${lat}_${lon}`] += 1; // Increment the offset counter
                    } else {
                        markerOffsets[`${lat}_${lon}`] = 0; // Initialize the offset counter
                    }

                    const divIcon = L.divIcon({
                        html: `<div class="routeindexmarker" style="transform: translate(${offset}px, ${offset}px)">${index + 1}</div>`,
                        className: 'custom-icon',
                    });
                    const marker = L.marker([lat, lon], { icon: divIcon });
                    markers.push(marker);
                });

                routeMarkers[key] = markers;

                // Initially hide the route polyline and markers
                route.setStyle({ opacity: 0 });
                markers.forEach(marker => marker.setOpacity(0)); 
                route.addTo(map);

                route.bindPopup(`Route for ${date} <br> Distance ${routedistance}`);
            } else {
                console.error('No route found for date:', date);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}



function map_showroute(username, date) {
    //console.log('showroute for ' + username + ' ' + date);
    const key = `${username}_${date}`;

    for (const routeKey in routePolylines) { // loop over all polylines
        if (routePolylines.hasOwnProperty(routeKey)) {
            const route = routePolylines[routeKey];
            const markers = routeMarkers[routeKey];

            if (routeKey === key) {
                route.setStyle({
                    opacity: 1
                });
                route.addTo(map);

                if (markers) {
                    for (const marker of markers) {
                        marker.setOpacity(1);
                        marker.addTo(map);
                    }
                }

                route.bindPopup(`Route for ${date}`);
            } else {
                // hide or remove the other route polylines
                route.setStyle({
                    opacity: 0
                });
                route.removeFrom(map);

                // hide or remove the markers associated with the other route polylines
                if (markers) {
                    for (const marker of markers) {
                        marker.setOpacity(0);
                        marker.removeFrom(map);
                    }
                }
            }
        }
    }
}




// Create an object to store the layer groups
var userGroups = {};

function map_create_hbgmarker(selectedUser, kwclass) {
    map_removeallmarkers()
    var maprenderer = L.canvas({
        padding: 0.5
    });

    // Loop through each user
    for (var user in json_begeher) {
        if (json_begeher.hasOwnProperty(user)) {
            // Create a layer group for the user
            var userGroup = L.layerGroup().addTo(map);
            userGroups[user] = userGroup;

            // Loop through each calendar week for the user
            for (var kw in json_begeher[user]) {
                if (json_begeher[user].hasOwnProperty(kw)) {
                    // Create a layer group for the calendar week
                    var kwGroup = L.layerGroup();
                    userGroup.addLayer(kwGroup);

                    // Loop through each date for the calendar week
                    for (var date in json_begeher[user][kw]) {
                        if (json_begeher[user][kw].hasOwnProperty(date)) {
                            // Create a layer group for the date
                            var dateGroup = L.layerGroup();
                            kwGroup.addLayer(dateGroup);
                            var firstMarkerLatLng = null;
                            // Loop through each marker for the date
                            var markers = json_begeher[user][kw][date];
                            if (Array.isArray(markers)) {
                                markers.forEach(function (element) {
                                    var latlng = [element.lat, element.lon];
                                    var circleMarker = L.circleMarker(latlng, {
                                        renderer: maprenderer,
                                        color: '#ecd57f',
                                        markerDate: element.date
                                    });
                                    dateGroup.addLayer(circleMarker);
                                    let popupText = element.hausbegeher + '<br>' + element.date + ' ' + element.time + '<br>' + latlng;
                                    circleMarker.bindPopup(popupText);
                                    ////console.log(circleMarker)
                                    if (firstMarkerLatLng == null && user == selectedUser && kw == kwclass) {
                                        firstMarkerLatLng = latlng;
                                    }
                                });
                            }
                            if (firstMarkerLatLng) {
                                ////console.log('flyto')
                                map.flyTo(firstMarkerLatLng, 10, {
                                    animate: true,
                                    duration: 1
                                });
                            }
                        }
                    }
                }
            }
        }
    }

    // Hide all user layers except the selected user
    for (var user in userGroups) {
        userGroups[user].remove();
        if (user !== selectedUser) {
            // userGroups[user].remove();
        }
    }
}


// --------------------------------------------------------------
// parse string kw19 to Y-m-d elements returnin as obj
function getDatesFromCalendarWeek(kwclass) {

    const kw = parseInt(kwclass.substring(2), 10);

    const januaryFourth = new Date(new Date().getFullYear(), 0, 4);
    const daysOffset = (januaryFourth.getDay() > 0 ? januaryFourth.getDay() - 1 : 6);
    const monday = new Date(new Date().getFullYear(), 0, (kw - 1) * 7 + 1 + daysOffset);

    const dates = [];
    for (let i = 0; i < 7; i++) {
        const date = new Date(monday.getFullYear(), monday.getMonth(), monday.getDate() + i);
        const dateString = date.toISOString().substring(0, 10);
        dates.push(dateString);
    }

    return dates;
}


// --------------------------------------------------------------
// formats Y-m-d to d.m.
function formatDate(inputDate) {
    const parts = inputDate.split('-');
    const day = parts[2].padStart(2, '0');
    const month = parts[1].padStart(2, '0');
    const formattedDate = `${day}.${month}.`;
    return formattedDate;
}





function slot_finddayindex(searchdiv) {

    const dayContents = searchdiv.find('.homeidday').map(function () {
        const parent = $(this);
        const child = parent.find('.homeid-link');
        return {
            parent: parent,
            child: child
        };
    }).get();

    //console.log(dayContents);

    let index = -1;
    const childCap = 16; // 16 slots per day hardcap

    for (let i = 0; i < dayContents.length; i++) {
        if (dayContents[i].child.length >= 0 && dayContents[i].child.length < childCap) {
            index = i;
            //console.log(dayContents[i].child.length)
            break;
        }
    }

    if (index !== -1) {
        // //console.log('First parent with children within child cap:', index);
        return index;
    } else {
        // //console.log('No parent found with children within child cap');
        return -1;
    }

}

function slot_findslotindex(div) {
    var hourSlots = $(div).find('.hour-slot');

    for (var i = 1; i < hourSlots.length; i++) {
        var slot = $(hourSlots[i]);
        var prevSlot = $(hourSlots[i - 1]);
        var childrenCount = prevSlot.children('.homeid-link').length;
        console.log('i: ' + i + '  ' + slot.data('hour'))
        console.log('childrenCount' + childrenCount)

        if (slot.data('hour') > 8 && childrenCount > 0 && childrenCount < 2) {
            console.log('openslot')
            var childElement = prevSlot.children('.homeid-link').first();
            return {
                slot: prevSlot,
                child: childElement,
                slotIndex: prevSlot.data('hour'),
            };
        }
    }

    return null; // No open slot found
}