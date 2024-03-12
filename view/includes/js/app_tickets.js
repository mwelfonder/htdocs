function ticket_tableload() {
	// Check if the DataTable instance exists
	if ($.fn.DataTable.isDataTable('#tickets_overview')) {
		// If the table instance already exists, destroy it
		$('#tickets_overview').DataTable().destroy();
	}

	// Now create the DataTable
	$('#tickets_overview').DataTable({
		select: true,
		"language": {
			"search": "Filter:"
		},
		"ajax": {
			"url": "view/load/tickets_load.php",
			"type": "POST",
			"data": {
				"func": "load_tickettable"
			},
			"dataSrc": "",
			"dataFilter": function (data) {
				console.log("Raw response:", data);
				return data;
			}
		},
		"columns": [
			{ "data": "ticket_status", "title": "Status" },
			{ "data": "ticket_ident", "title": "TicketID" },
			{ "data": "homeid", "title": "HomeID" },
			{ "data": "ticket_source", "title": "Source" },
			{ "data": "ticket_title", "title": "Title" },
			{ "data": "ticket_goal", "title": "Goal" },
			{ "data": "ticket_creation", "title": "Creation Date" },
			{ "data": "ticket_updated", "title": "Last Updated" },
			{ "data": "ticket_creator", "title": "Creator" },
			{ "data": "ticket_id", "title": "ID" },
			{ "data": "ticket_carrier", "title": "Carrier" },
			{ "data": "ticket_client", "title": "Client" }
		],
		"columnDefs": [
			{
				"targets": 0, // Status column
				"render": function (data, type, full, meta) {
					if (type === 'display') {
						let classToAdd = "";
						switch (data) {
							case "new":
							case "open":
								classToAdd = "blue";
								break;
							case "closed":
								classToAdd = "green";
								break;
							case "pending":
								classToAdd = "orange";
								break;
							case "progress":
								classToAdd = "yellow";
								break;
							default:
								classToAdd = ""; // Default class if any
						}

						return '<span class="searchpill ' + classToAdd + '">' + data + '</span>';
					} else {
						return data; // Return original data if not for display
					}
				}
			},
			{
				"targets": [3, 9, 10, 11], // hide the ticket_id and ticket_source for no admins
				"visible": false
			}
		],
		"order": [[6, "desc"]],
	});
}

$(document).ready(function () {

	//______________________________________________________________________//
	// ticket load table
	ticket_tableload();
	// bind the params to the tablebody
	$('#tickets_overview tbody').on('click', 'tr', function () {
		var data = $('#tickets_overview').DataTable().row(this).data();

		// Check if ticketSearchResults already have the data we need
		const homeData = ticketSearchResults?.find(item => item.homeid === data.homeid);

		if (homeData) {
			// If we have the data, call Ticket_show
			Ticket_show(data.homeid, data.ticket_id);
		} else {
			// If not, make an AJAX call to fetch it
			$.ajax({
				url: 'view/load/tickets_load.php',
				method: 'POST',
				data: { func: 'ticket_loadData', homeid: data.homeid, ticket_id: data.ticket_id },
				dataType: 'json',
				success: function (response) {
					// Depending on your server response structure, you might need to adjust the following lines:
					ticketSearchResults = [response]; // Store the fetched data
					Ticket_show(data.homeid, data.ticket_id); // Call Ticket_show
				},
				error: function (error) {
					console.error("Data loading error: ", error);
					// Handle error appropriately here
				}
			});
		}
	});

	// Reference to the DataTable
	var ticketTable = $('#tickets_overview').DataTable();

	// Filter states
	var filters = {
		'UGG': true,
		'DGF': true,
		'GVG': true,
		'GlasfaserPlus': true,
		'Moncobra': true,
		'Insyte': true
	};

	// Custom filtering function
	$.fn.dataTable.ext.search.push(
		function (settings, data, dataIndex) {
			var carrier = data[10];
			var client = data[11];

			return filters[carrier] && filters[client];
		}
	);
	function toggleFilter(element, type) {
		var id = $(element).attr("id");
		var value;

		switch (id) {
			case "btn_filter_ugg":
				value = "UGG";
				break;
			case "btn_filter_dgf":
				value = "DGF";
				break;
			case "btn_filter_gvg":
				value = "GVG";
				break;
			case "btn_filter_glasfaserplus":
				value = "GlasfaserPlus";
				break;
			case "btn_filter_moncobra":
				value = "Moncobra";
				break;
			case "btn_filter_insyte":
				value = "Insyte";
				break;
		}

		if ($(element).children().children().attr('class') === 'ri-checkbox-fill') {
			$(element).children().children().attr('class', 'ri-checkbox-blank-line');
			filters[value] = false;
		} else {
			$(element).children().children().attr('class', 'ri-checkbox-fill');
			filters[value] = true;
		}

		ticketTable.draw();  // Redraw table based on new filter
	}

	$("#btn_filter_ugg, #btn_filter_dgf, #btn_filter_gvg,#btn_filter_glasfaserplus, #btn_filter_moncobra, #btn_filter_insyte,#btn_filter_fol").click(function () {
		toggleFilter(this);
	});
});

var preloadTicketLayout;
var ticketSearchResults = null;
var quill; // global scope so its reachable
var confirmBox; // store the active confirm

$(document).ready(function () {
	$.ajax({
		url: '/view/includes/layout_tickets.php',
		method: 'GET',
		success: function (response) {
			preloadTicketLayout = response;
			// Create a jQuery object from the response text
			var contentJQueryObj = $(response);

			// Find and execute scripts inside the content
			contentJQueryObj.find('script').each(function () {
				$.globalEval(this.text || this.textContent || this.innerHTML || '');
			});
		}
	});


	$(document).on('click', '#md_ticketStateDone', function () {
		const ticketident = $('#md_tck_cd_ticketID').text();
		$.ajax({
			url: 'view/load/tickets_load.php',
			method: 'POST',
			data: { func: 'ticket_close', ticketident: ticketident },
			success: function (response) {
				var parsedResponse = JSON.parse(response);
				console.log('ticket_close response', response)
				if (parsedResponse.status === 'success') {
					Ticket_disable(true)
				}
			},
			error: function (error) {
				console.error("Data loading error: ", error);
				// Handle error appropriately here
			}
		});
	});

	$(document).on('click', '#md_ticketStatePending', function () {
		const ticketident = $('#md_tck_cd_ticketID').text();
		$.ajax({
			url: 'view/load/tickets_load.php',
			method: 'POST',
			data: { func: 'ticket_pending', ticketident: ticketident },
			success: function (response) {
				var parsedResponse = JSON.parse(response);
				console.log('ticket_pending response', response)
				if (parsedResponse.status === 'success') {
					Ticket_disable(true)
				}
			},
			error: function (error) {
				console.error("Data loading error: ", error);
				// Handle error appropriately here
			}
		});
	});

	$(document).on('click', '#md_ticketStateProgress', function () {
		const ticketident = $('#md_tck_cd_ticketID').text();
		$.ajax({
			url: 'view/load/tickets_load.php',
			method: 'POST',
			data: { func: 'ticket_progress', ticketident: ticketident },
			success: function (response) {
				var parsedResponse = JSON.parse(response);
				console.log('ticket_progress response', response)
				if (parsedResponse.status === 'success') {
					Ticket_disable(true)
				}
			},
			error: function (error) {
				console.error("Data loading error: ", error);
				// Handle error appropriately here
			}
		});
	});

});


function Ticket_createNew(homeid) {
	if (preloadTicketLayout) {
		$.confirm({
			title: 'Confirm!',
			content: preloadTicketLayout,
			boxWidth: '80vw',
			boxHeight: '90vh',
			columnClass: 'mod_wrapper custom_z_index_class',
			useBootstrap: false,
			containerFluid: true,  // to use percentages in width/height
			buttons: {
				"Create Ticket": {
					text: 'Create Ticket', // Set the button text
					btnClass: 'btn-blue',  // Apply a class to the button
					isDisabled: true, // The button is disabled by default
					action: function () {
						var contentHtml = quill.root.innerHTML;
						var fileIds = $('.task_listfileitem').map(function () {
							return $(this).data('file-id');
						}).get();
						var fileIdsJson = JSON.stringify(fileIds);

						// Get values from the dropdowns
						var selectedGoal = $("#md_tck_goal").data('value');  // assuming you've stored value using .data("value")
						var selectedPriority = $("#md_tck_prio").data('value');  // assuming you've stored value using .data("value")

						// Get the ticket title and ticket ID
						var ticketTitle = $('#ticketTitle').text();  // assuming it's a text container, otherwise use .val()
						var ticketIdent = $('#md_tck_cd_ticketID').text();

						$.ajax({
							url: 'view/load/tickets_load.php',
							method: 'POST',
							data: {
								func: 'ticket_saveNew',
								htmlContent: contentHtml,
								homeid: homeid,
								fileIds: fileIdsJson,
								goal: selectedGoal,
								priority: selectedPriority,
								title: ticketTitle,
								ticket_ident: ticketIdent,
							},
							success: function (response) {
								var parsedResponse = JSON.parse(response);
								if (parsedResponse.status === 'success') {
									confirmBox_success('Ticket Created', `Ticket was succefull created with <span style="white-space: nowrap;">Ticket ID: ${ticketIdent}</span>`, 'confetti')
									console.log(parsedResponse.message);
									ticket_tableload();
								} else if (parsedResponse.status === 'error') {
									confirmBox_fail('Ticket Creation failed', null);
									console.log(parsedResponse.message);
								}
							},
							error: function (error) {
								confirmBox_fail('Ticket Creation failed', null);
							}
						});

					}
				},
				cancel: {
					text: 'Cancel',
					action: function () {

					}
				}
			},
			onOpen: function () {

			},
			onContentReady: function () {
				$('.jconfirm').css('zIndex', '99999');
				$('.jconfirm-title-c').hide();
				$('.jconfirm-box').css('padding', '0');
				$('.jconfirm-content-pane').css('margin-bottom', '0px');
				$('.jconfirm-content').css('overflow-x', 'hidden');

				$('#task_inf_ticketHistoryWrapper').hide() // hide the ticket timeline which is empty anyway on creation
				$('#task_inf_ticketContent').css('flex-grow', '1') // grow the textarea to fullsize to have enough space to write a descritpion
				$('#md_ticketStateToggle').hide();
				$('#md_ticketStateDone').hide();
				$('#md_ticketStatePending').hide();
				$('#md_ticketStateProgress').hide();

				div_GrowUp('#task_inf_ticketContent')

				this.buttons["Create Ticket"].disable();
				var isTextValid = false; // to track if Quill text is valid
				var isGoalValid = false; // to track if Goal selection is valid

				quill = new Quill('#task_inf_ticketContent', { // init wysywig editor
					theme: 'snow',
					placeholder: 'Please describe the ticket as detailed as possible...'
				});
				quill.on('text-change', function () {
					var textLength = quill.getLength();
					isTextValid = textLength > 50; // Update validation flag
					toggleCreateButton(); // Check if "Create Ticket" should be enabled or disabled
				});

				// Listen for Goal dropdown change
				$("#md_tck_goal").on('click', function () {
					var selectedGoal = $(this).text();
					isGoalValid = selectedGoal !== 'Select a Goal'; // Update validation flag
					toggleCreateButton(); // Check if "Create Ticket" should be enabled or disabled
				});

				// Function to enable or disable the "Create Ticket" button
				var toggleCreateButton = () => {
					if (isTextValid && isGoalValid) {
						this.buttons["Create Ticket"].enable();
					} else {
						this.buttons["Create Ticket"].disable();
					}
				}


				if (ticketSearchResults) {
					console.log('ticketSearchResults', ticketSearchResults)
					const homeData = ticketSearchResults.find(item => item.homeid === homeid);
					console.log('homedata', homeData)
					var name = homeData.lastname + ', ' + homeData.firstname
					var streetnumberadd = (typeof homeData.streetnumberadd === 'number') ? '/' + homeData.streetnumberadd : homeData.streetnumberadd;
					var address = homeData.city + ', ' + homeData.street + ' ' + homeData.streetnumber + streetnumberadd;

					$('#md_tck_cd_homeid').text(homeData.homeid);
					$('#md_tck_cd_name').text(name)
					$('#md_tck_cd_address').text(address)
					$('#md_tck_cd_phone1').text(homeData.phone1)
					$('#md_tck_cd_phone2').text(homeData.phone2)
					$('#md_tck_cd_mail').text(homeData.mail)


					$('.task_inf_createdby').text(`Created by ${currentuser}`)

					var now = new Date();
					var formattedDate = now.toISOString().split('T')[0];
					var formattedTime = now.toTimeString().substring(0, 5);
					$('.task_inf_createdat').text(`Created at ${formattedDate} ${formattedTime}`);

					var uniqueID = generateTicketID(homeData.city, homeData.street, homeData.client);
					$('#md_tck_cd_ticketID').text(uniqueID);

				}

			}
		});
	}
}


function Ticket_stateupdate() {

}

function Ticket_disable(disable = true) {
	if (disable) {
		$('#md_ticketStateToggle').hide();
		$('#md_ticketStateDone').hide();
		$('#md_ticketStatePending').hide();
		$('#md_ticketStateProgress').hide();
		$('#task_inf_newCommentBtn').hide();
		$('#dropArea').hide();

	} else {
		$('#md_ticketStateToggle').show();
		$('#md_ticketStateDone').show();
		$('#md_ticketStatePending').show();
		$('#md_ticketStateProgress').show();
		$('#task_inf_newCommentBtn').show();
		$('#dropArea').show();
	}

}

function Ticket_permissions() {
	if (!hasPerm(2) && !hasPerm(26)) {
		Ticket_disable(true)
	} else {
		Ticket_disable(false)
	}
}

function Ticket_show(homeid, ticketID) {
	if (preloadTicketLayout) {
		confirmBox = $.confirm({
			title: '',
			content: preloadTicketLayout,
			boxWidth: '80vw',
			boxHeight: '95vh',
			columnClass: 'mod_wrapper custom_z_index_class',
			useBootstrap: false,
			containerFluid: true,  // to use percentages in width/height
			buttons: {
				cancel: {
					text: 'Close',
					action: function () {

					}
				}
			},
			onOpen: function () {
				console.log('looking for ticketID:' + ticketID)
				$.ajax({
					url: 'view/load/tickets_load.php',
					method: 'POST',
					data: {
						func: 'ticket_loadByID',
						ticket_id: ticketID
					},
					success: function (response) {
						console.log('response', response)
						var parsedResponse = JSON.parse(response);
						if (parsedResponse.status === 'success') {
							console.log(parsedResponse.message);
							var ticketData = parsedResponse.ticketData;
							console.log('ticketData', ticketData)

							$('#md_tck_cd_ticketID').text(ticketData.ticket_ident);
							$('#md_tck_cd_ticketID_internal').text(ticketData.ticket_id);
							$('#mod_title_heading').text(ticketData.ticket_title)
							$('.task_inf_createdby').text(`Created by ${ticketData.ticket_creator}`)
							$('.task_inf_createdat').text(ticketData.ticket_creation)
							$('.md_tck_datestart').text(ticketData.ticket_creation)
							$('#md_tck_dateedit').text(ticketData.ticket_updated)
							$('#md_tck_dateend').text(ticketData.ticket_closed)

							$('#md_tck_prio').next('.dropdown-menu').find('a').each(function () {
								if ($(this).attr('data-value') === ticketData.ticket_priority) {
									$('#md_tck_prio').text($(this).text());
									$(this).trigger("click");
								}
							});

							// Set the Goal dropdown
							$('#md_tck_goal').next('.dropdown-menu').find('a').each(function () {
								if ($(this).attr('data-value') === ticketData.ticket_goal) {
									$('#md_tck_goal').text($(this).text());
									$(this).trigger("click");
								}
							});


							const homeData = ticketSearchResults.find(item => item.homeid === homeid);
							console.log('homedata', homeData)
							var name = homeData.lastname + ', ' + homeData.firstname
							var streetnumberadd = (typeof homeData.streetnumberadd === 'number') ? '/' + homeData.streetnumberadd : homeData.streetnumberadd;
							var address = homeData.city + ', ' + homeData.street + ' ' + homeData.streetnumber + streetnumberadd;

							$('#md_tck_cd_homeid').text(homeData.homeid);
							$('#md_tck_cd_name').text(name)
							$('#md_tck_cd_address').text(address)
							$('#md_tck_cd_phone1').text(homeData.phone1)
							$('#md_tck_cd_phone2').text(homeData.phone2)
							$('#md_tck_cd_mail').text(homeData.mail)

							if (ticketData.ticket_state === 'private') {
								$('#md_ticketState').text('Private Ticket');
								$('#md_ticketStateToggle').show();
							} else {
								$('#md_ticketState').text('Public Ticket');
								$('#md_ticketStateToggle').hide();
							}
							if (!hasPerm(2)) {
								$('#md_ticketStateDone').hide();
								$('#md_ticketStatePending').hide();
								$('#md_ticketStateProgress').hide();
							}


							// ___________________________________________ //
							// Load History
							loadTicketHistory(ticketData);

							// ___________________________________________ //
							// Clear existing files and images
							$('.file_list').empty();
							$('.image_list').empty();
							if (parsedResponse.ticketData.files && parsedResponse.ticketData.files.length > 0) {
								parsedResponse.ticketData.files.forEach((fileInfo) => {
									const fileType = fileInfo.file_name.split('.').pop().toLowerCase();
									const isImage = ['jpg', 'jpeg', 'png'].includes(fileType);
									const fileCreation = fileInfo.file_creation.split(' ')[0];
									const filePath = fileInfo.file_dir.replace('/var/www/html', '');

									const fileItemContent = `
									<span class="task_listfiledetails">${fileCreation}</span>
									<span class="task_info_binfotextvalues fileitem">${fileInfo.file_name}</span>
									<span class="task_editfile" style="display:none;"><i class="ri-edit-line" style="color: #2b6aff;"></i></span>
									<span class="task_deletefile"><i class="ri-delete-bin-line"></i></span>
									`;

									let fileItem;
									if (isImage) {
										fileItem = `
										<div class="task_listfileitem" data-file-id="${fileInfo.ticket_doc_id}" style="cursor:pointer;">
											<a data-fancybox="gallery" data-src="${filePath}">
												${fileItemContent}
												<div class="image_preview">
													<img src="${filePath}" alt="${fileInfo.file_name}">
												</div>
											</a>
										</div>
									`;
									} else {
										fileItem = `
										<div class="task_listfileitem" data-file-id="${fileInfo.ticket_doc_id}">
											<a href="${filePath}" target="_blank">
												${fileItemContent}
											</a>
										</div>
									`;
									}

									$(isImage ? '.image_section .image_list' : '.file_section .file_list').append(fileItem);
								});

								// Bind FancyBox to the dynamically created elements
								Fancybox.bind('[data-fancybox="gallery"]', {});
							}
							div_GrowUp('#task_inf_ticketHistoryWrapper')
							$('.jconfirm-content-pane').css({
								'margin-bottom': '0px',
								'height': 'auto !important',
								'max-height': 'auto !important',
							});


							if (ticketData.ticket_status === 'closed') {
								Ticket_disable(true)
								console.log('disable ticket fields')
								$('#md_ticketState').text('Ticket Closed')
							} else {
								Ticket_disable(false)
								console.log('enable ticket fields')
							}
							Ticket_permissions();


						} else if (parsedResponse.status === 'error') {
							console.log(parsedResponse.message);
						}
					},
					error: function (error) {
						// error
					}
				});
			},
			onContentReady: function () {
				$('.jconfirm').css('zIndex', '99999');
				$('.jconfirm-title-c').hide();
				$('.jconfirm-box').css('padding', '0');
				$('.jconfirm-content-pane').css({
					'margin-bottom': '0px',
					'height': 'auto !important',
					'max-height': 'auto !important',
				});
				$('.jconfirm-content').css('overflow-x', 'hidden');

				$('#task_inf_ticketContent').hide();


			}
		});
	}

}


function Ticket_Listing() {
	confirmBox = $.confirm({
		theme: 'material',
		draggable: false,
		type: 'blue',
		closeIcon: true,
		title: '<div style="position: relative;">' +
			'<input type="text" class="form-control form-control-sm" id="searchInput" style="width:100%;" placeholder="Type to search a Customer...">' +
			'<div id="dynamicContent" style="display:none;">' +
			'</div>' +
			'</div>' +
			'<div id="dynamicCustomerSelected" style="font-size:13px;font-weight:500;"></div>',
		content: '<div id="dynamicTicketBody" class="dynamicTicketBody centered"></div>',
		columnClass: 'col-md-12',
		buttons: {
			newTicket: {
				text: 'New Ticket',
				btnClass: 'btn-blue',
				action: function () {
					var homeid = $('#dynamicCustomerSelected #homeid').text();
					console.log('homeIdText', homeid);
					if (!homeid) {
						alert('Please select a customer first');
					} else {
						Ticket_createNew(homeid);
					}


				},
				isDisabled: true // The button is disabled by default
			},
			close: {
				text: 'Close',
				action: function () {
					// Close the box
					this.close();
				}
			},
		},
		onOpenBefore: function () {
			$('.jconfirm-title').css('width', '100%');
		},
		onContentReady: function () {
			var self = this;
			$('#searchInput').focus();

			$('#dynamicContent').hide();
			$('#searchInput').on('click', function () {
				if ($('#dynamicContent').children().length > 0) {
					$('#dynamicContent').show();
				}
			});
			$('#searchInput').on('input', debounce(function (event) {
				var query = $(event.target).val();  // Changed this line to use event.target instead
				if (query.length >= 3) {
					$.ajax({
						url: 'view/load/tickets_load.php', // your server-side script's URL here
						method: 'POST',
						data: { func: 'ticket_search', term: query },
						dataType: 'json',
						success: function (response) {
							ticketSearchResults = response;
							console.log(response)
							if (response && response.length > 0) {
								var content = '';
								var query = $('#searchInput').val();
								for (var i = 0; i < response.length; i++) {
									var homeid = highlightMatches(response[i].homeid, query);
									var streetnumberadd = (typeof response[i].streetnumberadd === 'number') ? '/' + response[i].streetnumberadd : response[i].streetnumberadd;
									var address = highlightMatches(response[i].city + ', ' + response[i].street + ' ' + response[i].streetnumber + streetnumberadd, query);
									var fullname = highlightMatches(response[i].lastname + ' ' + response[i].firstname, query);
									var hbg_status_pill = getStatusPill(response[i].hbg_status);
									var scan4_status_pill = getStatusPill(response[i].scan4_status);
									var ticketLabel = '';
									if (response[i].tickets) {
										ticketLabel = '<i class="ri-coupon-line"></i>' + response[i].tickets.length;
									}
									content += '<div class="dynamicLoadItem_searchbar" style="display: flex; align-items: center;" data-index="' + i + '">';
									content += '<div style="flex: 2;" id="homeid"><i class="bi bi-hash"></i>' + homeid + '</div>';
									content += '<div style="flex: 3;"><i class="ri-map-pin-2-line"></i> ' + address + '</div>';
									content += '<div style="flex: 3;"><i class="ri-user-line"></i> ' + fullname + '</div>';
									content += '<div style="flex: 1;"><i class="bi bi-house-gear"></i> ' + response[i].unit + '</div>';
									content += '<div style="flex: 2;"><i class="ri-flag-line"></i> ' + hbg_status_pill + ' / ' + scan4_status_pill + '</div>';
									content += '<div style="flex: 1;">' + ticketLabel + '</div>';
									content += '</div>';
								}
								$('#dynamicContent').html(content).show();

								// Attach the click event handler here
								$('#dynamicContent').on('click', '.dynamicLoadItem_searchbar', function () {
									self.buttons.newTicket.enable();
									var outerHtml = this.outerHTML;
									var $htmlContent = $(outerHtml);
									$htmlContent.find('mark').contents().unwrap();
									$htmlContent.addClass('selected');
									outerHtml = $htmlContent.prop('outerHTML');
									$('#dynamicCustomerSelected').html(outerHtml);
									$('#dynamicContent').hide();
									const index = $(this).data('index');
									const tickets = response[index].tickets;
									let ticketContent = '';

									if (tickets) {
										$('#dynamicTicketBody').removeClass('centered');
										ticketContent = tickets.map(generateTicketContent).join('');
									} else {
										ticketContent = '<div class="dynamicLoadTicketEmpty centered"><i class="ri-ghost-line"></i><br />No tickets existing</div>';
										$('#dynamicTicketBody').addClass('centered');
									}

									$('#dynamicTicketBody').html(ticketContent);
								});
							} else {
								$('#dynamicContent').html('').hide();
							}
						},

						error: function (jqXHR, textStatus, errorThrown) {
							$('#dynamicContent').html('Error occurred: ' + textStatus).show();
						}
					});
				} else {
					$('#dynamicContent').hide();
				}
			}, 500));
		}
	});
}

function getStatusPill(status) {
	status = status.toLowerCase();
	var colorMap = {
		'open': 'blue',
		'done': 'green',
		'stopped': 'red',
		'planned': 'yellow',
		'done cloud': 'green',
		'overdue': 'lila',
		'wrong': 'red',
		'pending': 'orange',
		'missing': 'red',
	};

	return colorMap[status] ? '<span class="searchpill ' + colorMap[status] + '">' + status.toUpperCase() + '</span>' : '';
}

function highlightMatches(str, term) {
	var terms = term.split(' ').filter(function (t) { return t.length > 0; }); // this will remove empty strings
	for (var i = 0; i < terms.length; i++) {
		var highlightTerm = new RegExp("(" + escapeRegExp(terms[i]) + ")", "ig");
		str = str.replace(highlightTerm, '<mark>$1</mark>');
	}
	return str;
}

function escapeRegExp(string) {
	return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); // $& means the whole matched string
}
// Function to format date
function formatDate(dateString) {
	if (!dateString) return '';

	const date = new Date(dateString);

	if (isNaN(date.getTime())) return '';

	return ('0' + date.getDate()).slice(-2) + '.' +
		('0' + (date.getMonth() + 1)).slice(-2) + '.' +
		(date.getFullYear().toString().slice(-2)) + ' at ' +
		('0' + date.getHours()).slice(-2) + ':' +
		('0' + date.getMinutes()).slice(-2);
}

// Function to generate ticket content HTML
function generateTicketContent(ticket) {
	const format_creation = formatDate(ticket.ticket_creation);
	const format_updated = formatDate(ticket.ticket_updated);
	const format_closed = formatDate(ticket.ticket_closed);
	const ticketStatus = getStatusPill(ticket.ticket_status);

	return `
	  <div id="${ticket.ticket_id}" class="dynamicLoadTicketItem" style="display: flex;">
	  <div style="flex:2;">
		  <div class="ticket-info">
			<div><b>#${ticket.ticket_ident}</b></div>
			<div><b>Status</b> ${ticketStatus}</div>
			<div><b>Creator: </b>${ticket.ticket_creator}</div>
		  </div>
		  <strong>Title: </strong>${ticket.ticket_title}<br>
		  <div class="ticket-date-info">
			<div><b>Creation: </b>${format_creation}</div>
			<div><b>Last Update: </b>${format_updated}</div>
			<div><b>Closed: </b>${format_closed}</div>
		  </div>
		</div>
		<div style="flex:1;">
		</div>
		
	  </div>`;
}

$(document).on('click', '.dynamicLoadTicketItem', function () {
	var homeid = $('#dynamicCustomerSelected #homeid').text();
	var ticketID = $(this).attr('id');

	if (homeid) {
		confirmBox.close();
		Ticket_show(homeid, ticketID);
	}
});

function generateTicketID(city, street, client) {
	var cityChar = city.charAt(0).toUpperCase();
	var streetChar = street.charAt(0).toUpperCase();  // Added this line
	var currentDate = new Date();
	var yearDigits = currentDate.getFullYear().toString().slice(-2);
	var monthDigits = (currentDate.getMonth() + 1).toString().padStart(2, '0');
	var clientChar = client.charAt(0).toUpperCase();
	var randomDigits = Math.floor(Math.random() * 999).toString().padStart(3, '0');
	var uniqueID = `${cityChar}${streetChar}${yearDigits}${monthDigits}${clientChar}${randomDigits}`;  // Included streetChar here

	return uniqueID;
}


function Ticket_saveComment(callback) {
	const ticketID = $('#md_tck_cd_ticketID_internal').text();
	// Retrieve content from Quill
	var commentHtml = quill.root.innerHTML;
	console.log('commentHtml', commentHtml)
	console.log('ticketID', ticketID)

	property = quill.property
	console.log('property', property)



	$.ajax({
		url: 'view/load/tickets_load.php',
		method: 'POST',
		data: {
			func: 'ticket_saveComment',   // adjust this to your server-side function for saving comments
			commentContent: commentHtml,
			ticketID: ticketID,
			property: property
		},
		success: function (response) {
			console.log(response)
			var parsedResponse = JSON.parse(response);
			if (parsedResponse.status === 'success') {
				console.log(parsedResponse.message);
				quill.setContents([]);
				if (typeof callback === 'function') {
					callback();
				}
			} else if (parsedResponse.status === 'error') {
				confirmBox_fail('Comment Addition failed', null);
				console.log(parsedResponse.message);
			}
		},
		error: function (error) {
			confirmBox_fail('Comment Addition failed', null);
		}
	});

}


function loadTicketHistory(ticketData) {
	console.log(ticketData);

	const $ticketHistoryWrapper = $('#task_inf_ticketHistoryWrapper');
	$ticketHistoryWrapper.html('');

	const ticketSource = ticketData.ticket_source;

	// Load initial ticket description
	if (ticketData.ticket_description && ticketSource !== 'Intern') {

		const ticketDescriptionHtml = ticketData.ticket_description;

		let commentOptionsButtons = '';
		if (hasPerm(2)) {
			const hasCommentMessage = ticketData.comments.some(comment => comment.comment_message === true || comment.comment_message === 1);
			const messageBtn = hasCommentMessage ? '' : `<button id="taks_inf_addMessageBtn" class="btn btn-sm btn-outline-primary mr-2">Message</button>`;
			const newDescBtn = ticketData.ticket_finaldescription ? '' : `<button id="taks_inf_addnewDescBtn" class="btn btn-sm btn-outline-success mr-2">Internal Description</button>`;

			commentOptionsButtons = `${messageBtn}${newDescBtn}`;
		}

		const isCollapsed = ticketData.ticket_finaldescription ? 'collapsed' : '';
		const isRotated = ticketData.ticket_finaldescription ? 'rotated' : '';

		const ticketItemHtml = `
			<div class="task_inf_ticketHistoryItem">
				<div class="task_inf_details">${ticketData.ticket_creator} - ${ticketData.ticket_creation}</div>
				<div class="task_inf_ticketHistoryItem_body ${isCollapsed}">
					<div class="task_inf_ticketHistoryItem_bodyHeader">
						<span class="task_infticketInitDesc">Initial description</span>
						<span class="">${ticketSource}</span>
						<button class="btn p-0 float-right ${isRotated}" id="task_inf_collapsComment"><i class="ri-skip-down-line"></i></button>
					</div>
					<div class="task_inf_ticketHistoryItem_bodyContent">
						${ticketDescriptionHtml}
						<div class="task_inf_commentedit">${commentOptionsButtons}</div>
					</div>
				</div>
			</div>`;
		$ticketHistoryWrapper.append(ticketItemHtml);
	}

	// Event handlers for buttons
	$ticketHistoryWrapper.on('click', '#taks_inf_addMessageBtn', function () {
		console.log('taks_inf_addMessageBtn');
		quillHandler('Write a Message to the Author', 'message');
	});
	$ticketHistoryWrapper.on('click', '#taks_inf_addnewDescBtn', function () {
		console.log('taks_inf_addnewDescBtn');
		quillHandler('Write a new internal Description', 'desc');
	});
	$ticketHistoryWrapper.on('click', '#task_inf_newCommentBtn', function () {
		console.log('task_inf_newCommentBtn');
		quillHandler('Write a new comment', 'comment');
		$(this).closest('.task_inf_AddNewComment').hide();
		$('#task_inf_ticketHistoryWrapper').scrollTop($('#task_inf_ticketHistoryWrapper')[0].scrollHeight);
		setTimeout(function () {
			$('.jconfirm-content-pane').scrollTop($('.jconfirm-content-pane')[0].scrollHeight);
		}, 100);
	});
	$ticketHistoryWrapper.on('click', '.task_inf_commentReplybtn', function () {
		console.log('task_inf_commentReplybtn');
		$('.task_inf_ticketHistoryItem_body').removeClass('replyto');
		$(this).closest('.task_inf_ticketHistoryItem_body').addClass('replyto');
		quillHandler('Reply to comment', 'desc');
	});
	//____________________________________________________________________________//
	// Load final description
	if (ticketData.ticket_finaldescription) {

		const ticketDescriptionHtml = ticketData.ticket_finaldescription;
		const ticketItemHtml = `
            <div class="task_inf_ticketHistoryItem">
                <div class="task_inf_details">${ticketData.ticket_finaldescription_user} - ${ticketData.ticket_finaldescription_date}</div>
                <div class="task_inf_ticketHistoryItem_body">
					<div class="task_inf_ticketHistoryItem_bodyHeader">
                        <span class="task_infticketInitDesc" style="border-color: #5f00dd; color: #ffffff; background: #5f00dd;">Final description</span>
						<button class="btn p-0 float-right" id="task_inf_collapsComment"><i class="ri-skip-down-line"></i></button>
                    </div>
					<div class="task_inf_ticketHistoryItem_bodyContent">
                   		${ticketDescriptionHtml}
                    </div>
            </div>`;
		$ticketHistoryWrapper.append(ticketItemHtml);
	}
	//____________________________________________________________________________//
	// Load ticket comments
	if (ticketData.comments && ticketData.comments.length > 0) {
		ticketData.comments.forEach(comment => {
			const commentHtml = `
                <div class="task_inf_ticketHistoryItem">
                    <div class="task_inf_details">${comment.comment_author} - ${comment.comment_creation}</div>
                    <div class="task_inf_ticketHistoryItem_body">
						<div class="task_inf_ticketHistoryItem_bodyHeader">
							<button class="btn p-0 float-right" id="task_inf_collapsComment"><i class="ri-skip-down-line"></i></button>
						</div>
						<div class="task_inf_ticketHistoryItem_bodyContent">
							${comment.comment_content}
							<div class="task_inf_commentReply p-0"><button class="task_inf_commentReplybtn btn p-0" id="${comment.comment_id}"><i class="ri-reply-all-line"></i></button></div>
						</div>
                    </div>
                </div>`;

			$ticketHistoryWrapper.append(commentHtml);
		});
	}

	const newCommentItemHtml = `<div class="task_inf_AddNewComment" style="padding: 5px; margin: 25px;"><button class="btn" id="task_inf_newCommentBtn" style="border: 2px solid #6b6b6b; color: #6b6b6b;">Add new Comment</button></div>`;
	$ticketHistoryWrapper.append(newCommentItemHtml);
	$ticketHistoryWrapper.scrollTop($ticketHistoryWrapper[0].scrollHeight);
	$ticketHistoryWrapper.on('click', '#task_inf_collapsComment', function () {
		$(this).closest('.task_inf_ticketHistoryItem_body')
			.find('.task_inf_ticketHistoryItem_bodyContent')
			.toggle();
		$(this).toggleClass('rotated');
	});
}

function quillHandler(text, property) {
	quill_init(state = 'destroy');
	quill_init(state = 'create', text, property);
	const $container = $('.mod_wrapper');
	$container.scrollTop($container[0].scrollHeight);
}


function div_GrowUp(targetDiv) {
	var rightColHeight = $('.task-single-col-right').outerHeight();
	var leftCol = $(targetDiv).closest('.task-single-col-left');
	var leftColHeight = leftCol.outerHeight();
	if (leftColHeight < rightColHeight) {
		var heightDifference = rightColHeight - leftColHeight;
		var currentWrapperHeight = $(targetDiv).outerHeight();
		$(targetDiv).height(currentWrapperHeight + heightDifference);
	}
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

function confirmBox_fail(title = null, errorcode = null) {
	$.confirm({
		backgroundDismiss: true,
		theme: "dark",
		title: title,
		content: '<div style="text-align:center;font-size:40px;"><i class="ri-emotion-sad-line"></i></div>' +
			'<div style="text-align:center;">Sorry, something went wrong</div>' +
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


function debounce(func, wait) {
	let timeout;

	return function executedFunction(...args) {
		const later = () => {
			clearTimeout(timeout);
			func(...args);
		};

		clearTimeout(timeout);
		timeout = setTimeout(later, wait);
	};
}


function quill_init(state = 'create', placeHtext = 'Create a new comment to this Ticket', property = null) {
	if (state === 'create') {
		const ticketID = $('#md_tck_cd_ticketID_internal').text();
		$('#task_inf_ticketContent').show();
		quill = null;
		quill = new Quill('#task_inf_ticketContent', { // init wysywig editor
			theme: 'snow',
			placeholder: placeHtext
		});
		quill.property = property;
		var $saveButton = $('<button>', {
			class: 'btn-green save disabled',
			css: {
				position: 'absolute',
				bottom: '10px',
				right: '10px',
				zIndex: '10'
			},
			click: function () {
				Ticket_saveComment(function () {
					$.ajax({
						url: 'view/load/tickets_load.php',
						method: 'POST',
						data: {
							func: 'ticket_loadByID',
							ticket_id: ticketID
						},
						success: function (response) {
							console.log('response', response)
							var parsedResponse = JSON.parse(response);
							if (parsedResponse.status === 'success') {
								quill_init(state = 'destroy');
								var ticketData = parsedResponse.ticketData;
								loadTicketHistory(ticketData);
							}
						}
					});
				});
			},
			disabled: true // initialize as disabled
		}).append('Save ', $('<i>', { class: 'ri-save-3-line' })); // Directly create and append the icon
		$('#task_inf_ticketContent').append($saveButton);
		let wrapperHeight = $('#task_inf_ticketHistoryWrapper').outerHeight()
		let quillHeight = $('.ql-toolbar').outerHeight()
		quillHeight += $('#task_inf_ticketContent').outerHeight()
		$('#task_inf_ticketHistoryWrapper').css('height', wrapperHeight - quillHeight)

		// Add an event listener to Quill for text changes
		quill.on('text-change', function () {
			// Check the length of the text content
			var length = quill.getText().trim().length;

			// Enable or disable the save button based on text length
			if (length >= 5) {
				$saveButton.prop('disabled', false).removeClass('disabled');
			} else {
				$saveButton.prop('disabled', true).addClass('disabled');
			}
		});
	} else if (state === 'destroy') {
		var toolbars = $('#tickettab_content_description .ql-toolbar');
		toolbars.remove();
		quill = null;
		$('#task_inf_ticketContent').html('');
		$('#task_inf_ticketContent').hide();
		div_GrowUp('#task_inf_ticketHistoryWrapper')
	}
}



$(document).ready(function () {
	$('#newTicket').on('click', function () {
		Ticket_Listing();
	});
});

$(document).ready(function () {
	$.ajax({
		url: 'view/load/tickets_load.php',
		type: 'POST',
		data: { func: 'ticket_count_data' },
		dataType: 'json',
		success: function (response) {
			console.log (response);
			$('#open-tickets .number').text(response.open_tickets);
			$('#total-tickets .number').text(response.total_tickets);
			$('#closed-today .number').text(response.closed_today);
			$('#created-today .number').text(response.created_today);
			$('#pending-tickets .number').text(response.pending_tickets);
			$('#progress-tickets .number').text(response.progress_tickets);
		}
	});
});
