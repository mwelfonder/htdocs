

$(document).ready(function () {

	var lastSelectedIndex = null;
	var projectList = $('#projectList');

	// Function to update the last selected index
	function updateLastSelectedIndex(index) {
		lastSelectedIndex = index;
	}

	// Function to handle multi-selection with Shift and Ctrl keys
	function handleMultiSelection(clickedIndex, isCtrlKeyPressed, isShiftKeyPressed) {
		if (isShiftKeyPressed && lastSelectedIndex !== null) {
			var startIndex = Math.min(clickedIndex, lastSelectedIndex);
			var endIndex = Math.max(clickedIndex, lastSelectedIndex);
			// Select all projects between startIndex and endIndex
			projectList.children('div').slice(startIndex, endIndex + 1).addClass('selected');
		} else if (isCtrlKeyPressed) {
			// Toggle class without affecting other selected projects
			projectList.children('div').eq(clickedIndex).toggleClass('selected');
		} else {
			// Normal click, select only this project
			projectList.children('div').removeClass('selected');
			projectList.children('div').eq(clickedIndex).addClass('selected');
		}
		// Update the state of the "Select All" checkbox
		var allSelected = projectList.children('div.selected').length === projectList.children('div').length;
		$('#selectAllCheckbox').prop('checked', allSelected);
	}

	updateSubmitButtonStatus(); // disable the submit btn initial

	// Initialize Flatpickr
	var fpInstance = $("#dateRange").flatpickr({
		mode: "range",
		altInput: true,
		altFormat: "d.m.Y",
		dateFormat: "Y-m-d",
		onClose: function (selectedDates, dateStr, instance) {
			// Check if submit button should be enabled after date range changes
			updateSubmitButtonStatus(selectedDates); // Pass the selectedDates directly
		}
	});
	// Function to check if the submit button should be enabled
	function updateSubmitButtonStatus(selectedDates) {
		var isProjectSelected = $('#projectList div.selected').length > 0;
		var isDateRangeValid = selectedDates && selectedDates.length === 2; // Ensure selectedDates is defined and has 2 dates

		// Enable submit button only if both conditions are true
		$('#submitProjectCalc').prop('disabled', !(isProjectSelected && isDateRangeValid));
	}

	// Event listener for the Select All / Deselect All checkbox
	$('#selectAllCheckbox').on('change', function () {
		if (this.checked) {
			// Select all projects
			$('#projectList div').addClass('selected');
		} else {
			// Deselect all projects
			$('#projectList div').removeClass('selected');
		}
		// Update the submit button status
		updateSubmitButtonStatus(fpInstance.selectedDates);
	});


	// load the projects into the selection
	$.ajax({
		url: "view/load/dashboard_overview_load.php",
		type: 'POST',
		data: {
			func: 'load_projects',
		},
		dataType: 'json',
		success: function (response) {
			projectList.empty();

			// Loop over the response and append projects to the list
			$.each(response, function (index, project) {
				var projectDiv = $('<div>').text(project.city).attr('data-id', project.id);

				// Modified click event listener to handle multi-selection
				projectDiv.on('click', function (e) {
					handleMultiSelection(index, e.ctrlKey || e.metaKey, e.shiftKey);
					updateLastSelectedIndex(index);

					// Check if submit button should be enabled after a project is selected
					updateSubmitButtonStatus(fpInstance.selectedDates);

					// Update the state of the "Select All" checkbox
					var allSelected = projectList.children('div.selected').length === projectList.children('div').length;
					$('#selectAllCheckbox').prop('checked', allSelected);
				});

				projectList.append(projectDiv);
			});
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log('Error loading data: ' + textStatus);
		}
	});


	$('#searchInput').on('keyup', function () {
		var searchTerm = $(this).val().toLowerCase();

		// Filter the project list
		$('#projectList div').each(function () {
			var projectName = $(this).text().toLowerCase();

			// Check if project name contains the search term
			if (projectName.includes(searchTerm)) {
				$(this).show(); // or $(this).css('display', 'block');
			} else {
				$(this).hide(); // or $(this).css('display', 'none');
			}
		});
	});

	$('#submitProjectCalc').on('click', function () {
		var selectedCityNames = $('#projectList div.selected').map(function () {
			return $(this).text(); // Assuming the text is the city name
		}).get(); // Inline collection of selected city names

		var selectedDates = fpInstance.selectedDates.map(date => date.toISOString().substring(0, 10));

		console.log('selectedDates', selectedDates)
		console.log('selectedProjectIds', selectedCityNames)
		// Ensure there is a date range selected before sending the AJAX call
		if (selectedDates.length === 2) {
			// AJAX call to send the data to the server
			$.ajax({
				url: "view/load/dashboard_overview_load.php",
				type: 'POST',
				data: {
					func: 'load_doneCustomersCheck',
					cityNames: selectedCityNames,
					startDate: selectedDates[0],
					endDate: selectedDates[1]
				},
				dataType: 'json',
				success: function (response) {
					// Handle success
					console.log('Server response:', response);

					// If you already have a DataTable instance, you should destroy it first
					if ($.fn.DataTable.isDataTable('#Datatables_projectlist')) {
						$('#Datatables_projectlist').DataTable().clear().destroy();
					}

					// Create an array of column objects
					var columns = [];
					for (var key in response[0]) {
						columns.push({ data: key });
					}

					// Now initialize the DataTable with the dynamic columns
					$('#Datatables_projectlist').DataTable({
						data: response, // your JSON data from the server
						columns: columns,
						dom: 'Bfrtip', // Add the buttons extension
						buttons: [
							'excelHtml5' // Add the Excel button
						],
						headerCallback: function (thead, data, start, end, display) {
							for (var i = 0; i < columns.length; i++) {
								$(thead).find('th').eq(i).html(columns[i].data);
							}
						}
					});

				},
				error: function (jqXHR, textStatus, errorThrown) {
					// Handle error
					console.log('Error sending data: ' + textStatus);
				},
				complete: function (jqXHR, textStatus) {
					// This will always be called, regardless of success or error
					console.log('AJAX call completed with status:', textStatus);
					// If you want to inspect the raw responseText:
					console.log('Raw response:', jqXHR.responseText);
				}
			});
		} else {
			// Handle the case where the date range is not properly selected
			console.log('Please select a valid date range.');
		}
	});



});



var date_callshorts = 'this';
var day_callshorts = 'all';
var user_callshorts = 'all';
var city_callshorts = 'all';

$(document).ready(function () {

	init_shorts();

	// Listen on input for 'select kw input' -> load new customers of given KW
	$(document).on('input', '#selectnewcustomers', function () {
		if (term.length === 2) {
			$.ajax({
				method: "POST",
				url: "view/load/dashboard_overview_load.php",
				data: {
					func: "load_overview_a_callshorts",
					kw: term,
					city: city,
				},
			}).done(function (response) {
				//console.log(response)
				$('#shownewcustomers').html(response);
			});
		}
	});
	// ===================================
	//		Call Shorts select date
	$('#callshorts_w').children('.tabbar').click(function (e) {

		date_callshorts = $(this).attr('id');
		split = date_callshorts.split('_');
		date_callshorts = split[1];

		day_callshorts = $(this).attr('id');
		split = day_callshorts.split('_');
		day_callshorts = split[1];
		user = $('#callshorts_user').val();
		console.log(user);
		console.log('dateis' + date_callshorts);

		$('#callshorts_w').children('.tabbar').each(function (i, obj) {
			$(this).removeClass('activetab');
		});
		$(this).addClass('activetab');

		if (date_callshorts !== 'this' && date_callshorts !== 'last') {
			$('#callshorts_d').children('.tabbar').each(function (i, obj) {
				if (i === 0) {
					$(this).addClass('activetab');
				}
				else {
					$(this).addClass('disabled');
					$(this).removeClass('activetab');
				}
			});
		} else {
			$('#callshorts_d').children('.tabbar').each(function (i, obj) {
				$(this).removeClass('disabled');
			});
		}
		init_shorts(date_callshorts, day_callshorts, user)

	});	// ===================================
	//		Call Shorts select day
	$('#callshorts_d').children('.tabbar').click(function (e) {
		if (!$(this).hasClass('disabled')) {
			date_callshorts = $('#callshorts_w').children('.activetab').attr('id');
			split = date_callshorts.split('_');
			date_callshorts = split[1];

			day_callshorts = $(this).attr('id');
			split = day_callshorts.split('_');
			day_callshorts = split[1];
			if (day_callshorts === 'alldays') day_callshorts = 'all';
			user = $('#callshorts_user').val();
			console.log('user' + user);
			console.log('day' + day_callshorts);
			console.log('week' + date_callshorts);
			$('#callshorts_d').children('.tabbar').each(function (i, obj) {
				$(this).removeClass('activetab');
			});
			$(this).addClass('activetab');
			init_shorts(date_callshorts, day_callshorts, user)
		}
	});
	// ===================================
	//		Call Shorts select user
	$('#callshorts_user').on('change', function () {
		user_callshorts = this.value;
		init_shorts(date_callshorts, user_callshorts)
	});
	// ===================================
	//		Call Shorts search project
	$('#callshorts_city').on('input', function () {
		var term = $('#callshorts_city').val();
		console.log(term)
		if (term.length >= 2) {
			$.ajax({
				method: "POST",
				url: "view/load/dashboard_overview_load.php",
				data: {
					func: "search_callshortsproject",
					term: term,
				},
			}).done(function (response) {
				//console.log(response)
				console.log(response)
				$('#callshorts_city_results').html(response);
				$("#callshorts_city_results").removeClass("hidden");
			});
		} else {
			$('#callshorts_city_results').html('');
			$("#callshorts_city_results").addClass("hidden");
			city_callshorts = 'all';
			init_shorts();
		}
	});
	// ===================================
	//		Call Shorts search project >> clickevent
	$('#callshorts_city_results').on("click", ".callshortsresitem", function () {
		console.log($(this).attr('id'));
		city_callshorts = $(this).attr('id');
		$('#callshorts_city_results').addClass('hidden');
		init_shorts()
	});
	// ===================================
	//		Call Shorts expand nested table
	$(".callshorts_nested").click(function (e) {
		$(this).toggleClass('colapsed')
		if ($(this).hasClass('colapsed')) {
			$('#callshorts_nested').removeClass('hidden')
		} else {
			$('#callshorts_nested').addClass('hidden')
		}
	});

});


function init_shorts() {
	/*
	console.log('==============================')
	console.log(date_callshorts)
	console.log(day_callshorts)
	console.log(city_callshorts)
	console.log(user_callshorts)
	*/
	$.ajax({
		method: "POST",
		url: "view/load/dashboard_overview_load.php",
		data: {
			func: "load_overview_a_callshorts",
			kw: date_callshorts,
			day: day_callshorts,
			city: city_callshorts,
			user: user_callshorts,
		},
	}).done(function (response) {
		console.log(response)
		let obj = JSON.parse(response)
		console.log(obj)
		$('#set_callaction').html(obj['0']['callactions']);
		$('#set_calls').html(obj['0']['calls']);
		$('#set_missed').html(obj['0']['missed']);
		$('#set_nohbg').html(obj['0']['nohbg']);
		$('#set_wrongperson').html(obj['0']['wrongperson']);
		$('#set_wiedervorlage').html(obj['0']['wiedervorlage']);
		$('#set_customreason').html(obj['0']['customreason']);
		$('#set_refused').html(obj['0']['refused']);
		$('#set_wrongnumber').html(obj['0']['wrongnumber']);
		$('#set_numbernotset').html(obj['0']['numbernotset']);
		$('#set_wrongadress').html(obj['0']['wrongadress']);
		$('#set_canceldcontract').html(obj['0']['canceldcontract']);
		$('#set_hbgset').html(obj['0']['hbgset']);

		let cta = (obj['0']['hbgset'] / obj['0']['calls']) * 100;
		cta = cta.toFixed(2);
		$('#set_cta').html(cta + '%');
	});
}

function init_scoreboard() {
	$.ajax({
		method: "POST",
		url: "view/load/dashboard_overview_load.php",
		data: {
			func: "load_overview_a_callscoreboard",
			kw: 'this',
			day: 'all',
			city: 'all',
			user: 'all',
		},
	}).done(function (data) {
		var array = JSON.parse(data)
		//console.log('scoreboard')
		//console.log(array)
		var int = 0;
		$('#callscore_board tr').each(function (i, obj) {

			//	console.log(this)
			//	console.log(int)
			if (i !== 0) {
				let cta = (array[int]['0']['hbgset'] / array[int]['0']['calls']) * 100;
				cta = cta.toFixed(2);
				if (cta === 'NaN') cta = 0;
				$(this).find('.scoreboard_calls').text(array[int]['0']['calls']);
				$(this).find('.scoreboard_hbg').text(array[int]['0']['hbgset']);
				$(this).find('.scoreboard_cta').text(cta);
				int++;
			}

		});
		$('#callscore_board').DataTable({
			ordering: true,
			select: true,
			"paging": false,
			"lengthChange": false,
			"searching": false,
			"info": false,
			order: [[1, 'desc']],
		});
	});
}


// --------------------------------------------------------------------------------------------------------------------- //
// --------------------------------------------------------------------------------------------------------------------- //
// --------------------------------------------------------------------------------------------------------------------- // 
