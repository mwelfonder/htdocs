








var userdata;
var map;

$.ajax({
    method: "POST",
    url: "view/load/dashboard_routing_load.php",
    data: {
        func: "load_alldata",
    },
}).done(function (response) {
    //console.log(response)
    userdata = JSON.parse(response)
    console.log(userdata)

});


function route_request(user, date) {
    // Define the desired user and date
    // const desiredUser = 'AngeloSchoen';  EXAMPLE WHY WE CANT USE PLANNED
    // const desiredDate = '2023-03-02';    EXAMPLE WHY WE CANT USE PLANNED
    const desiredUser = user;
    const desiredDate = date;

    // Define an empty array to store the matching points
    const points = [];

    // Loop through the list to find the desired user and date
    Object.keys(userdata).forEach(function (hausbegeher) {
        if (hausbegeher === desiredUser) {
            const info = userdata[hausbegeher];
            Object.keys(info).forEach(function (key) {
                if (key === desiredDate) {
                    let properties = info[key];
                    if (!Array.isArray(properties)) {
                        // Convert the object to an array
                        properties = Object.values(properties);
                    }
                    console.log(properties); // Debugging statement
                    properties.forEach(function (details) {
                        const lat = details.lat;
                        const lon = details.lon;
                        if (lat && lon) {
                            points.push([lat, lon]);
                        }
                    });
                }
            });
        }
    });

    // Make the API request with the points array
    const url = 'https://49.12.77.77/route?point=' + points.map(p => p.join(',')).join('&point=') + '&profile=car';
    $.ajax({
        url: url,
        type: 'GET',
        success: function (response) {
            // Process the API response
            console.log(response)
            route_parse(response, points)
        },
        error: function (xhr, status, error) {
            console.log('Ajax error: ' + error);
        }
    });
}



function route_parse(data, route) {

    // Remove all markers and polylines from the map
    map.eachLayer(function (layer) {
        if (layer instanceof L.Marker || layer instanceof L.Polyline) {
            map.removeLayer(layer);
        }
    });


    var middleIndex = Math.floor(route.length / 2);
    var middleStop = [parseFloat(route[middleIndex][0]), parseFloat(route[middleIndex][1])];
    map.setView(middleStop, 12);



    var points = data.paths[0].points;
    var decodedPoints = polyline.decode(points, 5);
    L.polyline(decodedPoints, {
        color: 'blue'
    }).addTo(map);

    for (var i = 0; i < route.length; i++) {
        var markerIcon = L.divIcon({
            className: 'custom-marker-icon',
            html: '<div class="marker-number">' + (i + 1) + '</div>',
            iconSize: [24, 24]
        });

        L.marker([parseFloat(route[i][0]), parseFloat(route[i][1])], {
            icon: markerIcon
        }).addTo(map);
    }

    // Add event listeners to the map instance to handle user interactions
    map.on('click', function (e) {
        console.log('Map clicked at ' + e.latlng.toString());
    });

    map.on('zoomend', function (e) {
        console.log('Zoom level: ' + map.getZoom());
    });

}






$(document).ready(function () {

    map = L.map('leaflet').setView([51.159328, 10.445940], 7); // Assign the map object globally
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);



    // move user list to the correct position after creation
    $('.users').appendTo('.navigationwrapper');





    //--------------------------------------------------------------------------------
    // Navigation menu
    // Hide all months and days initially
    $('.months').hide();
    $('.days').hide();

    var selectedUser = '';

    // When clicking on a user, toggle the display of its months and store the name of the selected user
    $('.user').click(function () {
        selectedUser = $(this).text();
        $(this).next('.months').toggle();
    });

    // When clicking on a month, toggle the display of its days
    $('.month').click(function () {
        $(this).next('.days').toggle();
    });

    // When clicking on a day, toggle the corresponding checkbox, uncheck all others, and call a function with the selected user and day
    $('.day').click(function (event) {
        var checkbox = $(this).prev('.day-checkbox');
        if (event.target !== checkbox.get(0)) {
            $('.day-checkbox').not(checkbox).prop('checked', false);
            checkbox.prop('checked', !checkbox.prop('checked'));
            var selectedDay = $(this).text();
            if (selectedUser !== '' && selectedDay !== '') {

                route_request(selectedUser, selectedDay)
            }
        }
    });


});