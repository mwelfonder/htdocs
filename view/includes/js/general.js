// Check if we have permissions cached in localStorage
if (localStorage.getItem("json_permissions")) {
	console.log("Loaded permissions from cache");
	json_permissions = JSON.parse(localStorage.getItem("json_permissions"));
} else {
	loadPerms();
}

loadPerms();

function loadPerms() {
	$.ajax({
		type: 'POST',
		url: 'view/load/functions_load.php',
		data: {
			func: 'getPermArray',
		},
		success: function (response) { 
			console.log('getPermArray', response);
			json_permissions = JSON.parse(response);

			// Save the permissions to localStorage for future use
			localStorage.setItem("json_permissions", JSON.stringify(json_permissions));
		}
	});
}

function hasPerm(level) {
	return json_permissions[level];
}

$(document).ready(function () {


	$('body').on('click', function (event) { 
		// Get the clicked element
		var $target = $(event.target);

		var currentPage = window.location.href;
		var baseURL = "https://crm.scan4-gmbh.de"; // Define the base URL

		// Remove the base URL from the current page's URL
		var relativePath = currentPage.replace(baseURL, '');

		// Determine element type and relevant attributes
		var elementType = $target[0].nodeName.toLowerCase();
		var elementId = $target.attr('id');
		var elementClass = $target.attr('class');
		var elementHref = $target.attr('href');
		var elementText = $target.text(); // Get the text of the clicked element 
		var currentPage = window.location.href; // Get the current page URL

		// Construct data payload  
		var data = {
			type: elementType,
			id: elementId,
			class: elementClass,
			href: elementHref,
			text: elementText,
			page: relativePath
		}; 

		$.ajax({ 
			type: 'POST', 
			url: 'view/load/functions_load.php',
			data: {
				func: 'userActivity',
				data: data,
			},
			success: function (response) {
				//console.log('user_activty', response)s
			}
		});
	});
});  

function heatmap_record(interval) {
	var points = []; 
	var baseURL = "https://crm.scan4-gmbh.de"; // Define the base URL

	document.onmousemove = function (e) {
		var x = e.pageX;
		var y = e.pageY;
		var currentPage = window.location.href;
		var relativePath = currentPage.replace(baseURL, '');

		var point = {
			x: x,
			y: y,
			value: 1,
			page: relativePath  // Add the relativePath for each interaction point
		};

		points.push(point);
	};

	setInterval(function () {
		if (points.length > 0) {
			$.ajax({
				type: 'POST',
				url: 'view/load/functions_load.php',
				data: {
					func: 'recordHeatmapData',
					data: points
				},
				success: function (response) {
					console.log('recordHeatmapData', response);
				}
			});
			points = [];  // Reset points after sending
		}
	}, interval);
}
//heatmap_record(30000);  // 30 seconds




function track_clickevent(event, action, info) {
	// track clickevent
	//console.log('###GEN###' + event + ' ' + action + ' ' + info)
	$.ajax({
		method: "POST",
		url: "view/load/general_load.php",
		data: {
			func: "track_clickevent",
			source: "tracking",
			event: event,
			action: action,
			info: info,
		},
	});
}

function set_metatags() {
	const newTitle = "New Title";
	const newDescription = "New Description";

	// Update the og:title and og:description tags
	const ogTitleTag = document.querySelector('meta[property="og:title"]');
	const ogDescriptionTag = document.querySelector('meta[property="og:description"]');
	ogTitleTag.setAttribute('content', newTitle);
	ogDescriptionTag.setAttribute('content', newDescription);
}

// retrieves an obj of permissions for this user


