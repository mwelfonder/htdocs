window.onload = function () {
	heatmap_init('phoner_mask');
};
saved = false;
selected = 0;

// 1 = nicht erreicht 
// 2 = keine HBG
// 3 = erreicht mit Termin

reason = "";
comment = "";
hbgdate = "";
hbgdurration = "";
hbguser = "";


var flatpickrselectedDate = null; // stores the selected date in flatpickr
var flatpickrselectedUser = null; // stores the selected date in flatpickr
var flatpicker_moving = false; // stores the bol if the appt is to move
var flatpicker_moving_uid = null;

$(document).ready(function () {
	// $('#phonerapp_hbgset').hide();
	

	changed = false;

	let url = window.location.href;
	//if (url.includes('ref=search')) $('#phonerapp-loadnext').addClass('hidden'); // remove next btn if entered throug search

	let splitticket = url.split("city=");
	if (typeof splitticket[1] !== 'undefined') {
		//console.log(splitticket[1]);
		if (splitticket[1].includes("tickets_Insyte") || splitticket[1].includes("tickets_Moncobra")) {
			//console.log('load tickets');
			app_phoner_load_ticket();
		} else {
			//console.log('load no tickets');
			let a_split = url.split("homeid=");
			//console.log(a_split)
			if (typeof a_split[1] !== 'undefined') {
				a_split = a_split[1].split("?");
				//console.log('split at ?')
				//console.log(a_split)
				if (!(a_split[0] === "" || a_split[0] === "%")) {
					app_phoner_read_homeid(a_split[0]); // homeid is set load request
				} else {
					app_phoner_load_homeid(); // homeid is unset find a new one
				}
			} else {
				app_phoner_load_homeid();
			}
		}
	} else {

	}


	/*
	
		$("#datetimepicker").flatpickr({
			enableTime: true,
			dateFormat: "Y-m-d H:i",
			altInput: true,
			altFormat: "F j, Y",
			minTime: "07:00",
			maxTime: "20:00",
			time_24hr: true,
			minuteIncrement: 15,
			minDate: "today",
			locale: {
				firstDayOfWeek: 1
			},
			"disable": [
				function (date) {
					// return true to disable
					return (date.getDay() === 0 || date.getDay() === 7);
	
				}
			],
	
			onReady: function (selectedDates, dateStr, instance) {
				// Save the instance in the global variable
				flatpickrInstance = instance;
			}
		});
		*/
	$("#datetimepicker_followup").flatpickr({
		enableTime: true,
		dateFormat: "Y-m-d H:i",
		altInput: true,
		altFormat: "F j, Y",
		minTime: "07:00",
		maxTime: "20:00",
		time_24hr: true,
		minuteIncrement: 15,
		minDate: "today",
	});


	// -------------------------------------------------------------------------------------------
	// create a popu on calender element click
	$(document).on('click', '.event-wrapper:not(.draggable-event)', function (event) {
		// Remove any existing popups
		$('.event-wrapper-popup').remove();

		// Get the position of the clicked div
		var offset = $(this).offset();

		// Create a new div for the popup
		var popup = $('<div>')
			.addClass('event-wrapper-popup')
			.text($(this).data('location'))
			.css({
				top: offset.top,
				left: offset.left + $(this).width() + 10 // Position the popup to the right of the clicked div
			})
			.appendTo('body');

		// Remove the popup if user clicks anywhere else
		$(document).on('mousedown', function (event) {
			if (!$(event.target).closest('.event-wrapper-popup').length) {
				$('.event-wrapper-popup').remove();
			}
		});

		// Prevent the click event from bubbling up to parent elements
		event.stopPropagation();
	});


	/// >>>>>>>>>>>>>>>>>>>>>>
	/// ========================
	/// Record click on phone number
	$('#phonerinfo_phone1, #phonerinfo_phone2, #phonerinfo_phone3, #phonerinfo_phone4').click(function () {
		homeid = $('#head-homeid').html();
		let phoneid = $(this).attr('id');
		phoneid = phoneid.replace('phonerinfo_', '');
		let phonenr = $(this).text();
		$.ajax({
			method: "POST",
			url: "view/load/phoner_load.php",
			data: {
				func: "safe_phoneclick",
				homeid: homeid,
				phoneid: phoneid,
				phonenr: phonenr,
			},
		});
	});

	/// Interact switch/select Buttons
	$('.btn-interact-phonerapp').click(function () {
		id = $(this).attr("id");
		if (!(id === 'phonerapp_hbgset' && $(this).hasClass('disabled'))) {
			// ==============
			/// safe button
			if (id !== "phonerapp_safe" && id !== 'phonerapp_hbgset') {
				$('#phonerapp_safe').html(' <i class="ri-save-3-line"></i><span> Speichern</span>');
				$('#phonerapp_safe').removeClass('unset isset saved');
				$('#phonerapp_safe').addClass('isset');
			}
			// >>>>>>>>>>>>>>
			// ==============
			// other buttons
			$('.btn-interact-phonerapp').each(function (i, obj) {
				$(this).removeClass('active');
			});
			$(this).addClass('active');
			$('#phonerapp_interactfield_nohbg').addClass('hidden');
			$('#phonerapp_interactfield_sethbg').addClass('hidden');
			if (id === "phonerapp_nohbg") {
				selected = 2;
				$('#phonerapp_interactfield_nohbg').removeClass('hidden');
				flatpicker_moving = false;
			} else if (id === "phonerapp_hbgset") {
				selected = 3;
				$('#phonerapp_interactfield_sethbg').removeClass('hidden');
				flatpicker_moving = false;
			} else if (id === "phonerapp_notreached") {
				selected = 1;
				flatpicker_moving = false;
			}
		}
	});
	/// >>>>>>>>>>>>>>>>>>>>>>
	/// ========================
	/// Toggle followup datepicker on wiedervorlage select
	$('#interact_nohbgselect').on('change', function () {
		//console.log($(this).val());
		if ($(this).val() === 'Wiedervorlage') {
			$('#datetimepicker_followup').next().removeClass('hidden');
		} else {
			$('#datetimepicker_followup').next().addClass('hidden');
		}
		reason = $(this).val();
	});
	/// ========================
	/// >>>>> Timeline Header switch function
	$(".timeline-head").click(function () {
		id = $(this).attr("id");
		$('.timeline-head').each(function (i, obj) {
			$(this).removeClass('active');
		});
		$(this).addClass('active');

		$('.timeline-holder').each(function (i, obj) {
			$(this).addClass('hidden');
		});

		if (id === "timeline_head_main") {
			$('#holder-timeline').removeClass('hidden');
		} else if (id === "timeline_head_relation") {
			$('#holder-relations').removeClass('hidden');
		} else if (id === "timeline_head_hbg") {
			$('#holder-hbgitems').removeClass('hidden');
		} else if (id === "timeline_head_logfile") {
			$('#holder-logfile').removeClass('hidden');
		}
	});
	// >>>>>>>>>>>>>>>>>>>
	/// ==================
	/// >>>>> Button Load next
	$("#phonerapp-loadnext").click(function () {
		//console.log(changed);
		if ($('#phonerapp_notreached,#phonerapp_nohbg,#phonerapp_hbgset').hasClass('active')) {
			$.confirm({
				closeIcon: true,
				title: 'Achtung!',
				content: 'Es gibt ungespeicherte änderungen!\nMöchtest du die änderungen übernehmen?',
				type: 'red',
				buttons: {
					speichern: {
						text: "speichern",
						btnClass: 'btn-primary green',
						keys: ['enter'],
						action: function () {
							$('#phonerapp_safe').click(); // fake click on safe button
						}
					},
					verwerfen: {
						text: "verwerfen",
						btnClass: 'btn-red',
						keys: ['esc'],
						action: function () {
							app_phoner_load_homeid();
						}
					},
				}
			});
		} else {
			if ($(this).hasClass('isset')) {
				$(this).removeClass('isset')
				app_phoner_load_homeid();
			}
		}

	});

	$(document).on("change input", "#movereasoncomment", function () {
		var input = $(this).val();
		if (input.length > 5) {
			$(".btn-movenow").prop("disabled", false);
			$(".btn-movenow").removeClass("disabled");
		}

	});
	/// ==================
	/// >>>>> Button Safe Entry
	$("#phonerapp_safe").click(function () {
		reason = "";
		comment = "";
		hbgdate = "";
		hbgdurration = "";
		hbguser = "";
		console.log('flatpicker_moving: ' + flatpicker_moving)
		if ($(this).hasClass('unset')) {

		} else if ($(this).hasClass('isset')) {
			if (flatpicker_moving === true) { // if the move appt is triggered intercept the safe for a confirm
				flatpicker_moving = false;
				const pickedusername = $('.picker_select_item.selected').text();
				const pickedcomment = $('#movereasoncomment').val();
				const pickeddate = $('#datetimepicker').val();
				const date = new Date(pickeddate);
				const formattedDate = `${date.getDate()}.${date.getMonth() + 1}.${date.getFullYear()}`;
				const formattedTime = `${date.getHours()}:${("0" + date.getMinutes()).slice(-2)} Uhr`;

				console.log('pickedusername ' + pickedusername)
				$.confirm({
					closeIcon: true,
					title: 'Verschieben?',
					content: 'Der Termin wird zu <b>' + pickedusername + '</b><br>auf den <b>' + formattedDate + "</b><br> um <b>" + formattedTime + '</b> verschoben <br><input id="movereasoncomment" placeholder="Grund...">',
					type: 'orange',
					buttons: {
						speichern: {
							text: "Ja, verschieben",
							btnClass: 'btn-primary orange disabled btn-movenow',
							keys: ['enter'],
							isDisabled: true,
							action: function () {
								console.log('flatpicker_moving_uid ' + flatpicker_moving_uid)
								console.log('pickedusername ' + pickedusername)
								console.log('pickeddate ' + pickeddate)
								$.ajax({
									method: "POST",
									url: "view/load/phoner_load.php",
									data: {
										func: "change_hbg_move",
										uid: flatpicker_moving_uid,
										user: pickedusername,
										comment: pickedcomment,
										date: pickeddate,
									},
								}).done(function (response) {
									console.log(response);
									if (response === 'already storno') {
										$.alert({
											title: "Fehler",
											content: "HBG wurde bereits storniert",
											type: "red",
										});
									} else {
										homeid = $("#head-homeid").html();
										app_phoner_load_timeline(homeid);
									}

								});
							},

						},

						verwerfen: {
							text: "schließen",
							btnClass: '',
							keys: ['esc'],
							action: function () {

							}
						},
					},
					onDestroy: function () {
						$('#pickcalendar_wrapper, #eventOverlay').animate({ left: '-15vw' }, 500);
					},
				});
				return;
			}

			let save = false;
			if (selected === 1) {
				////console.log("nicht erreicht");
				reason = "nicht erreicht";
				save = true;
			} else if (selected === 2) {
				reason = $('#interact_nohbgselect').val();
				comment = $('#interact_nohbgselect_comment').val();
				// //console.log("get:" + reason);
				////console.log("get:" + comment);
				////console.log("keine hbg");
				if (reason != null && comment.length > 10) {
					save = true;
					hbgdate = $('#datetimepicker_followup').val();
				} else if (comment.length <= 10) {
					$.confirm({
						closeIcon: true,
						title: 'Keinen Kommentar!',
						content: 'Kommentar darf nicht leer sein und muss mindestens 10 Zeichen lang sein',
						type: 'red',
						buttons: {
							ok: {
								text: "ok",
								btnClass: 'btn-red',
								keys: ['esc'],
								action: function () {
								}
							},
						}
					});
				} else {
					$.confirm({
						closeIcon: true,
						title: 'Keinen Grund gewählt!',
						content: 'Ohne Grund kann nicht gespeichert werden',
						type: 'red',
						buttons: {
							ok: {
								text: "ok",
								btnClass: 'btn-red',
								keys: ['esc'],
								action: function () {
								}
							},
						}
					});
				}
				$('#phonerapp_nohbg').click(); // fake click on option 2
			} else if (selected === 3) {
				homeid = $("#head-homeid").html();
				reason = "HBG erstellt";
				hbgdate = $('#datetimepicker').val();
				comment = $('#select_hbg_comment').val();
				hbgdurration = $('#select_hbgdurration').val();
				hbguser = $('#select_hbguser option:selected').text();
				if (hbgdate.length <= 0 || hbguser === "Hausbegeher wählen...") {
					$.confirm({
						closeIcon: true,
						title: 'Unvolltändig',
						content: 'Kein Datum oder Hausbegeher gewählt!',
						type: 'red',
						buttons: {
							ok: {
								text: "ok",
								btnClass: 'btn-red',
								keys: ['esc'],
								action: function () {
								}
							},
						}
					});
					$('#phonerapp_hbgset').click(); // fake click on option 3
					save = false;
				} else {
					save = true;
				}
			}
			if (save === true) {


				////console.log(reason);
				//       //console.log(comment)
				//      //console.log(hbgdate)
				//      //console.log(hbgdurration)
				//       //console.log(hbguser)
				//        //console.log(selected)
				console.log('loader start')
				$('#loaderwrapper2').removeClass('hidden')
				$('#pickcalendar_wrapper, #eventOverlay').animate({ left: '-15vw' }, 500);
				$(this).removeClass('isset');
				$(this).addClass('saved');
				$(this).html(' <i class="ri-save-3-line"></i><span> Gespeichert!</span>');
				homeid = $("#head-homeid").html();
				saved = true;
				selected = 0;
				app_phoner_safe_homeid();
			}

		}
	});
	/// ==================
	//		Ticket overlay
	/// ==================
	$('#phonerapp_ticket').click(function () {
		$('#overlapp_wrapper_tickets').toggleClass('closed');
	});
	$('.groupitem-wrapper.ticket.priowrapper').click(function () {
		$('.groupitem-wrapper.ticket.priowrapper').each(function (i, obj) {
			$(this).removeClass('selected');
		});
		$(this).addClass('selected');
	});
	/// Submit ticket
	$("#ticketsubmit").click(function () {
		homeid = $('#head-homeid').html();
		text = $('#ticket_text').val();
		carrier = $('#phonerinfo_carrier').html();
		client = $('#phonerinfo_client').html();
		title = $('#ticket_titel').val();
		//console.log(title)
		if ($('#ticketprio1').hasClass('selected')) {
			option = 1;
		} else if ($('#ticketprio2').hasClass('selected')) {
			option = 2;
		} else if ($('#ticketprio3').hasClass('selected')) {
			option = 3;
		} else {
			option = 0;
		}
		if (option === 0) {
			$.confirm({
				closeIcon: true,
				title: 'Achtung!',
				content: 'Priorität wurde nicht gesetzt',
				type: 'red',
				buttons: {
					ok: {
						text: "ok",
						btnClass: 'btn-primary',
						keys: ['enter'],
					},
				}
			});
		}
		if (text.length === 0) {
			$.confirm({
				closeIcon: true,
				title: 'Achtung!',
				content: 'Ticket darf nicht leer sein',
				type: 'red',
				buttons: {
					ok: {
						text: "ok",
						btnClass: 'btn-primary',
						keys: ['enter'],
					},
				}
			});
		}
		if (option != 0 && text.length != 0) {

			$.ajax({
				method: "POST",
				url: "view/load/tickets_load.php",
				data: {
					func: "safe_newticket",
					text: text,
					option: option,
					homeid: homeid,
					title: title,
				},
			}).done(function (response) {
				//console.log('response:' + response);
				$('.groupitem-wrapper.ticket.priowrapper').each(function (i, obj) {
					$(this).removeClass('selected');
				});
				$('#ticket_text').val('');
				$('#ticket_titel').val('');
				$('#phonerapp_ticket').click();
				//$('#timeline').html('<li class="list-group-item"> <div class="list-inner-wrapper ticket"> <div class="item-inner-box blue ticket"><i class="ri-coupon-line"></i></div> <div class="item-inner-values"> <div class="item-inner-row tickethead"><b>Ticket vom '+date+'</b></div> <div class="item-inner-row tickettitle">' + title + '</div> <div class="item-inner-row tickettext">' + text + '</div> </div> </div> </li>');
				app_phoner_load_timeline(homeid)
			});
		}
	});
	// ---------------------------------------------------
	// Mark Ticket as done
	$(document).on("click", ".ticketbuttonstate", function () {
		$.confirm({
			closeIcon: true,
			backgroundDismiss: true,
			title: 'Ticket',
			content: 'Wurde das Ticket erledigt?',
			type: 'orange',
			buttons: {
				ok: {
					text: "Ja",
					btnClass: 'btn-primary',
					keys: ['enter'],
					action: function () {
						homeid = $("#head-homeid").html();
						$.ajax({
							method: "POST",
							url: "view/load/tickets_load.php",
							data: {
								func: "safe_ticket_newstate",
								status: 'pending',
								homeid: homeid,
							},
						}).done(function (response) {
							//console.log('response:' + response);
							app_phoner_load_timeline(homeid)
						});
					}
				},
			}
		});

	});


	/// ==================
	//		Change HBG
	/// ==================
	$(document).on("click", ".hbgicon", function () {
		const clicktarget = this;
		var uid = $(this).attr('id');
		$.confirm({
			closeIcon: true,
			backgroundDismiss: true,
			title: 'HBG bearbeiten',
			content: 'Verschieben oder Stornieren?',
			type: 'yellow',
			keys: ["esc"],
			buttons: {
				move: {
					text: "verschieben",
					btnClass: 'btn-yellow',
					action: function () {
						flatpicker_moving = true; // used in the save button
						flatpicker_moving_uid = uid;
						fakeFlatpickrClick(clicktarget);
						/*
												$.confirm({
													boxWidth: '25%',
													height: 'fit-content',
													useBootstrap: false,
													closeIcon: true,
													backgroundDismiss: true,
													title: 'HBG verschieben',
													content: appt_move_content(uid) + '</br><div class="aligncenter"><input type="text" id="moved_reason" placeholder="Grund" /></div>',
													type: 'yellow',
													keys: ["esc"],
													onOpen: function () {
														
													},
													buttons: {
														safe: {
															text: "verschieben",
															btnClass: 'btn-yellow',
															action: function () {
																newdate = $('#picker_' + uid).val()
																hbguser = $('#select_newhbguser option:selected').text();
																comment = $('#moved_reason').val();
																//console.log(uid)
																//console.log(newdate)
																//console.log(hbguser)
																//console.log(comment)
																if (hbguser === 'Hausbegeher wählen...') {
																	$.alert({
																		title: "Fehler",
																		content: "Hausbegeher darf nicht leer sein",
																		type: "red",
																	});
																} else if (comment.length < 5) {
																	$.alert({
																		title: "Fehler",
																		content: "Kommentar darf nicht leer sein",
																		type: "red",
																	});
																} else {
																	$.ajax({
																		method: "POST",
																		url: "view/load/phoner_load.php",
																		data: {
																			func: "change_hbg_move",
																			uid: uid,
																			user: hbguser,
																			comment: comment,
																			date: newdate,
																		},
																	}).done(function (response) {
																		//console.log(response);
																		if (response === 'already storno') {
																			$.alert({
																				title: "Fehler",
																				content: "HBG wurde bereits storniert",
																				type: "red",
																			});
																		} else {
																			homeid = $("#head-homeid").html();
																			app_phoner_load_timeline(homeid);
																		}
						
																	});
																}
															}
															//keys: ['enter'],
														},
						
													}
												});
						//console.log('test')
						setTimeout(function () {
							let flat = $('#picker_' + uid).flatpickr({
								enableTime: true,
								inline: true,
								dateFormat: "Y-m-d H:i",
								altInput: true,
								altFormat: "F j, Y",
								minTime: "07:00",
								maxTime: "20:00",
								time_24hr: true,
								minuteIncrement: 15,
								minDate: "today",
								defaultHour: '07',
								defaultDate: 'today',
								locale: {
									firstDayOfWeek: 1
								},
								"disable": [
									function (date) {
										// return true to disable
										return (date.getDay() === 0 || date.getDay() === 7);
									}
								],
							}); //flatpickr ends
							let select = $(".app-phoner-select-user-wrap").html();
							// id="select_hbguser"
							// replace select with new one
							select = select.replace('id="select_hbguser"', 'id="select_newhbguser"')
							$('#selectnewhbg').html(select);
						}, 100);
						*/

					}
					//keys: ['enter'],
				},
				cancel: {
					text: "stornieren",
					btnClass: 'btn-red',
					action: function () {
						$.confirm({
							useBootstrap: true,
							closeIcon: true,
							backgroundDismiss: true,
							title: 'HBG stornieren',
							content: 'Sicher das die HBG storniert werden soll?</br><input type="text" id="storno_reason" placeholder="Grund" />',
							type: 'yellow',
							keys: ["esc"],
							buttons: {
								yescancel: {
									text: "ja, stornieren",
									btnClass: 'btn-red',
									action: function () {
										var reason = $('#storno_reason').val();
										//console.log('cancel' + uid)
										//console.log(reason)
										if (reason.length < 5) {
											alert('Grund angeben!');
										} else {
											$.ajax({
												method: "POST",
												url: "view/load/phoner_load.php",
												data: {
													func: "change_hbg_storno",
													uid: uid,
													comment: reason,
												},
											}).done(function (response) {
												//console.log(response);
												if (response === 'already storno') {
													$.alert({
														title: "Fehler",
														content: "HBG wurde bereits storniert",
														type: "red",
													});
												} else {
													homeid = $("#head-homeid").html();
													app_phoner_load_timeline(homeid);
												}
											});
										}
									}
									//keys: ['enter'],
								},

							}
						});
					},
					//keys: ['enter'],
				},
			}
		});

	});

}); ////// Document ready ends




function app_phoner_safe_homeid() {
	////console.log("safeit now")

	homeid = $("#head-homeid").html();
	city = $("#phonerinfo_city").html();
	city = city.slice(6, 40);
	var city = clear_string(city);
	////console.log(reason);
	////console.log(hbgdate);
	$.ajax({
		method: "POST",
		url: "view/load/phoner_load.php",
		data: {
			func: "safe_homeid",
			homeid: homeid,
			reason: reason,
			city: city,
			comment: comment,
			hbgdate: hbgdate,
			hbgdurration: '30 min',
			hbguser: flatpickrselectedUser,
		},
	}).done(function (response) {
		//console.log(response)

		app_phoner_init_homeid(response);
		app_phoner_load_followup('today');
	});
}

function app_phoner_load_homeid() {

	var url = window.location.href;
	//let position = url.search(/cityy/i);
	url = url.replace(/%20/g, " "); // replace %20 with space
	let a_split = url.split("city=");
	////console.log(a_split[1]);
	a_split = a_split[1].split("?");
	////console.log(a_split)
	let city = a_split[0].replace(/%C3%9F/g, "ß");
	city = city.replace(/%C3%BC/, "ü");
	city = city.replace(/%C3%B6/, "ö");
	city = city.replace(/%C3%84/, "Ä");
	city = city.replace(/%C3%A4/, "ä");
	city = city.replace(/%C3%96/, "Ö");
	city = city.replace(/%C3%B6/, "ö");
	city = city.replace(/%C3%9C/, "Ü");
	city = city.replace(/%C3%BC/, "ü");
	city = city.replace(/%C3%A4/, "ä");
	city = city.replace(/%20/g, " ");

	console.log('loading homeid from city:' + city);
	$.ajax({
		method: "POST",
		url: "view/load/phoner_load.php",
		data: {
			func: "load_openhomeid",
			city: city,
		},
	}).done(function (response) {
		////console.log('///////////////////////////////////////')
		////console.log(response);
		if (response === '"entry:empty"') {
			app_phoner_endoflist();
		} else {
			app_phoner_init_homeid(response);
		}
	});
}


function app_phoner_load_ticket() {
	var url = window.location.href;

	let splitticket = url.split("city=");
	var client = '';
	if (typeof splitticket[1] !== 'undefined') {
		//console.log(splitticket[1]);
		if (splitticket[1].includes("tickets_Insyte") || splitticket[1].includes("tickets_Moncobra")) {
			//console.log('yes');
			client = splitticket[1].split('?')
			//console.log(client[0])

		} else {
			//console.log('no');
		}
	}

	$.ajax({
		method: "POST",
		url: "view/load/phoner_load.php",
		data: {
			func: "load_tickets",
			client: client[0],
		},
	}).done(function (response) {
		////console.log('///////////////////////////////////////')
		////console.log(response);
		if (response === '"entry:empty"') {
			app_phoner_endoflist();
		} else {
			//console.log('///ticket response///')
			//console.log(response);
			app_phoner_init_homeid(response);
		}
	});
}

function app_phoner_read_homeid(homeid) {
	$.ajax({
		method: "POST",
		url: "view/load/phoner_load.php",
		data: {
			func: "load_loadthishomeid",
			homeid: homeid,
		},
	}).done(function (response) {
		console.log("getmethis")
		console.log(response);
		app_phoner_init_homeid(response);
	});
}


function app_phoner_resetlayout() {
	////console.log('saved: ' + saved)
	if (saved === false) {
		$('#phonerapp_safe').html(' <i class="ri-save-3-line"></i><span> Speichern</span>');
		$('#phonerapp_safe').removeClass('unset isset saved');
		$('#phonerapp_safe').addClass('unset');
	}


	$('.timeline-head').each(function (i, obj) {
		$(this).removeClass('active');
		$('.timeline-holder').addClass('hidden');

	});
	$('#timeline_head_main').addClass('active');
	$('#holder-timeline').removeClass('hidden');


	$('.btn-interact-phonerapp.hblue').each(function (i, obj) {
		$(this).removeClass('active');
		$('#phonerapp_interactfield_sethbg').addClass('hidden');
		$('#phonerapp_interactfield_nohbg').addClass('hidden');
	});

	$('#body-content-app').removeClass('loading');


	$('#phonerapp-loadnext').removeClass('unset isset');
	$('#phonerapp-loadnext').addClass('unset');
	if (saved === false) {
		setTimeout(function () {
			$('#phonerapp-loadnext').removeClass('unset isset');
			$('#phonerapp-loadnext').addClass('isset');
		}, 5000);
	} else {
		$('#phonerapp-loadnext').removeClass('unset isset');
		$('#phonerapp-loadnext').addClass('isset');
	}
	saved = false;
	changed = false;
	reason = "";
	comment = "";
	hbgdate = "";
	hbgdurration = "";
	hbguser = "";
}


function phoner_adminarea() {
	// ----------------------------------
	// ----------------------------------
	// 				Edit Area
	// ----------------------------------
	// ----------------------------------

	var admin_ischanged = false;
	var admin_callcount = '';
	if ($('#progressitem6').hasClass('blue')) {
		admin_callcount = '5';
	} else if ($('#progressitem5').hasClass('blue')) {
		admin_callcount = '4';
	} else if ($('#progressitem4').hasClass('blue')) {
		admin_callcount = '3';
	} else if ($('#progressitem3').hasClass('blue')) {
		admin_callcount = '2';
	} else if ($('#progressitem2').hasClass('blue')) {
		admin_callcount = '1';
	}
	var admin_status = $('#head-statussc4').text();
	var admin_prio = $('#phonerinfo_priocount').text();
	if (admin_prio === null || admin_prio === '') {
		admin_prio = '0';
	}
	$('#admin_select_status').val(admin_status);
	$('#admin_select_calls').val(admin_callcount);
	$('#admin_select_prio').val(admin_prio);

	$('#phonerapp_edit').click(function () {
		$('#overlapp_wrapper_edit').toggleClass('closed');
		// reset values
		$('#admin_select_status').val(admin_status);
		$('#admin_select_calls').val(admin_callcount);
		$('#admin_select_prio').val(admin_prio);
		$("#admin_comment").val('');
	});
	$('#admin_edit_save').click(function () { // save button
		if ($('#admin_edit_save').hasClass('isset')) {
			let admin_callcount_val = $('#admin_select_calls').val();
			let admin_status_val = $('#admin_select_status').val();
			let admin_prio_val = $('#admin_select_prio').val();
			let admin_comment_val = $('#admin_comment').val();

			if (admin_comment_val.length < 5) {
				$.alert({
					closeIcon: true,
					backgroundDismiss: true,
					title: "Fehler",
					content: "Kommentar darf nicht leer sein",
					type: "red",
				});
			} else if ((admin_callcount == admin_callcount_val && admin_status == admin_status_val && admin_prio == admin_prio_val)) {
				$.alert({
					closeIcon: true,
					backgroundDismiss: true,
					title: "Fehler",
					content: "Keine änderung gefunden",
					type: "red",
				});
			} else if (admin_ischanged === true) {
				admin_comment_val = $("#admin_comment").val();
				homeid = $("#head-homeid").html();
				admin_callcount_div = '';
				admin_status_div = '';
				admin_prio_div = '';
				if (admin_callcount !== admin_callcount_val) {
					admin_callcount_div = 'Anrufe: ' + admin_callcount + ' > ' + admin_callcount_val;
				} else {
					admin_callcount_val = '';
				}
				if (admin_status !== admin_status_val) {
					admin_status_div = 'Status: ' + admin_status + ' > ' + admin_status_val;
				} else {
					admin_status_val = '';
				}
				if (admin_prio !== admin_prio_val) {
					admin_prio_div = 'Prio: ' + admin_prio + ' > ' + admin_prio_val;
				} else {
					admin_prio_val = '';
				}
				$.confirm({
					closeIcon: true,
					backgroundDismiss: true,
					title: "Änderungen speichern?",
					content: 'Diese Änderungen speichern?</br>' + admin_callcount_div + "<br>" + admin_status_div + "<br>" + admin_prio_div,
					type: "blue",
					buttons: {
						ja: {
							text: "Speichern",
							btnClass: "btn-primary blue",
							keys: ["enter"],
							action: function () {
								//console.log(admin_callcount_div)
								//console.log(admin_status_div)
								//console.log(admin_prio_div)
								//console.log(admin_comment_val)
								$.ajax({
									method: "POST",
									url: "view/load/phoner_load.php",
									data: {
										func: "safe_admin_edit",
										homeid: homeid,
										status: admin_status_val,
										calls: admin_callcount_val,
										prio: admin_prio_val,
										comment: admin_comment_val,
									},
								}).done(function (response) {
									//console.log(response);
									window.location.reload();
								});
							},
						},
						nein: {
							text: "nein",
							keys: ["esc"],
						},
					},
				});
			}
		}
	});

	// on change of select jquery
	$('#admin_select_status').change(function () {
		$('#admin_edit_save').addClass('isset');
		admin_ischanged = true;
	});
	$('#admin_select_calls').change(function () {
		$('#admin_edit_save').addClass('isset');
		admin_ischanged = true;
	});
	$('#admin_select_prio').change(function () {
		$('#admin_edit_save').addClass('isset');
		admin_ischanged = true;
	});
	// on admin_comment input jquery function
	$("#admin_comment").on("input", function () {
		var comment = $("#admin_comment").val();
		if (comment.length > 5) {
			$('#admin_edit_save').addClass('isset');
			admin_ischanged = true;
		}
	});



}


function app_phoner_init_homeid(response) {
	$('#loaderwrapper2').removeClass('hidden')
	////console.log('safestate_' + saved)
	//console.log(response)
	$('#body-content-app').addClass('loading');
	let parse = JSON.parse(response);
	console.log(parse);
	var carrier = parse[2];
	var client = parse[1];
	var homeid = parse[10];
	var adressid = parse[11];
	var dpnumber = parse[9];
	var statusnri = parse[19];
	var statussc4 = parse[24];
	var name = parse[13] + ", " + parse[12];
	var street = parse[3] + " " + parse[4] + parse[5];
	var adress = parse[8] + " " + parse[7];
	var cityname = parse[7];
	var phone1 = parse[14];
	var phone2 = parse[15];
	var phone3 = parse[47];
	var phone4 = parse[48];
	var email = parse[16];
	var unit = parse[6];
	var prio = parse[22];
	var anruf1 = parse[27];
	var anruf2 = parse[28];
	var anruf3 = parse[29];
	var anruf4 = parse[30];
	var anruf5 = parse[31];
	var mail = parse[33];
	var brief = parse[32];
	var timeline = parse[42];
	var unitsfrom = parse[43];
	var unitsrelation = parse[44];
	var unitsrelationcount = parse[45];
	var hbgfile = [];
	hbgfile[0] = parse[37];
	hbgfile[1] = parse[38];
	hbgfile[2] = parse[39];
	hbgfile[3] = parse[40];
	hbgfile[4] = parse[41];
	hbgfile[5] = parse[42];
	hbgfile[6] = parse[43];
	hbgfile[7] = parse[44];

	calendar_update(parse[7]);

	var url = window.location.href;
	//let position = url.search(/cityy/i);
	let a_split = url.split("city=");
	////console.log(a_split[1]);
	if (typeof a_split[1] !== 'undefined') {
		a_split = a_split[1].split("?");
		//console.log(a_split)
		let city = a_split[0].replace(/%C3%9F/g, "ß");
		city = city.replace(/%C3%BC/, "ü");
		city = city.replace(/%C3%B6/, "ö");
		city = city.replace(/%C3%84/, "Ä");
		city = city.replace(/%C3%A4/, "ä");
		city = city.replace(/%C3%96/, "Ö");
		city = city.replace(/%C3%B6/, "ö");
		city = city.replace(/%C3%9C/, "Ü");
		city = city.replace(/%C3%BC/, "ü");
		city = city.replace(/%C3%A4/, "ä");
		let base = url.split("homeid=");
		let newUrl = base[0] + 'homeid=' + homeid;
		let stateObj = { city: city, homeid: homeid };
		let title = city + ' ' + homeid;
		window.history.pushState(stateObj, title, newUrl);
	}

	$('#phonerinfo_cityname').html(cityname)
	$("#head-homeid").html(homeid);
	$("#head-adressid").html(adressid);
	$("#head-statusnri").html(statusnri);
	$("#head-statussc4").html(statussc4);
	$("#phonerinfo_name").html(name);
	$("#phonerinfo_street").html(street);
	$("#phonerinfo_city").html(adress);
	$("#phonerinfo_phone1").html('<a href="tel:' + phone1 + '" class="phoner-callnow">' + phone1 + '</a>');
	if (phone2 !== "" && phone2 !== null) {
		$("#phonerinfo_phone2").html('<a href="tel:' + phone2 + '" class="phoner-callnow">' + phone2 + '</a>');
	} else {
		$("#phonerinfo_phone2").html('');
	}
	if (phone3 !== "" && phone3 !== null) {
		$("#phonerinfo_phone3").html('<a href="tel:' + phone3 + '" class="phoner-callnow">' + phone3 + '</a>');
	} else {
		$("#phonerinfo_phone3").html('');
	}
	if (phone4 !== "" && phone4 !== null) {
		$("#phonerinfo_phone4").html('<a href="tel:' + phone4 + '" class="phoner-callnow">' + phone4 + '</a>');
	} else {
		$("#phonerinfo_phone4").html('');
	}


	/*
	if ((phone3 !== "" && phone3 !== null) || (phone4 !== "" && phone4 !== null)) {
		$("#phonerinfo_phone1").html(phone1)
		$("#phonerinfo_phone2").html(phone2)
		}
	*/

	$("#phonerinfo_email").html(email);
	$("#phonerinfo_units").html(unit);
	$("#holder-relations").html(unitsrelation);
	if (unitsrelationcount === "0") {
		$("#relationcounter").removeClass("show");
		$("#relationcounter").addClass("hidden");
	} else {
		$("#relationcounter").addClass("show");
		$("#relationcounter").removeClass("hidden");
		$("#relationcounter").html(unitsrelationcount);
	}
	$("#phonerinfo_dp").html(dpnumber);
	$("#phonerinfo_client").html(client);
	$("#phonerinfo_carrier").html(carrier);
	if (carrier === "UGG") {
		$("#carrier-logo").removeClass();
		$("#carrier-logo").addClass("carrier-ugg")
	}
	if (carrier === "GVG") {
		$("#carrier-logo").removeClass();
		$("#carrier-logo").addClass("carrier-gvg")
	}
	if (carrier === "DGF") {
		$("#carrier-logo").removeClass();
		$("#carrier-logo").addClass("carrier-dgf")
	}

	////console.log("prio:" + prio);
	if (prio === null) {
		$("#phonerinfo_priorow").addClass("hidden");
		$("#phonerinfo_priocount").html(prio);
	} else {
		$("#phonerinfo_priorow").removeClass("hidden");
		$("#phonerinfo_priocount").html(prio);
	}



	progressbar_update(statusnri, statussc4, anruf1, anruf2, anruf3, anruf4, anruf5, mail, brief);
	app_phoner_load_timeline(homeid);
	$('#loaderwrapper2').addClass('hidden')
	setTimeout(function () {
		app_phoner_resetlayout();
		phoner_adminarea();
	}, 200); // How long you want the delay to be, measured in milliseconds.

	check_isschedulable();
	activationtracker();
	set_metatags();
}

function app_phoner_load_timeline(homeid) {
	////console.log("loadtimeline:" + homeid);
	$.ajax({
		method: "POST",
		url: "view/load/phoner_load.php",
		data: {
			func: "load_timeline",
			homeid: homeid,
		},
	}).done(function (response) {
		////console.log("response:" + response);
		let a_split = response.split("@@relations@@");
		////console.log(a_split);
		$("#timeline").html(a_split[0]);
		$("#holder-relations").html(a_split[1]);
		////console.log('a_split[3]:' + a_split[3]);
		if (typeof a_split[3] !== 'undefined') {
			$("#holder-hbgitems").html(a_split[3]);
			let hbgitems = $("#hbgitemsfound").text();
			$("#hbgscounter").text(hbgitems);
			$("#hbgscounter").removeClass('zero');
			if (hbgitems === "0") {
				$("#hbgscounter").addClass('zero');
			}
		} else {
			$("#hbgscounter").addClass('zero');
		}
		if (a_split[2] === "0") {
			$("#relationcounter").removeClass('zero');
			$("#relationcounter").addClass('zero');
		} else {
			$("#relationcounter").html(a_split[2]);
			$("#relationcounter").removeClass("zero");
		}
	});

	$.ajax({
		method: "POST",
		url: "view/load/phoner_load.php",
		data: {
			func: "load_entry_logfile",
			homeid: homeid,
		},
	}).done(function (response) {
		//console.log("response:" + response);
		$("#logfile").html(response);
	});
}


function progressbar_update(statusnri, statussc4, anruf1, anruf2, anruf3, anruf4, anruf5, mail, brief) {
	$("#progressitem1,#progressitem2,#progressitem3,#progressitem4,#progressitem5,#progressitem6,#progressitem7,#progressitem8").removeClass("blue red yellow green");
	$("#head-statusnri").removeClass("yellow blue red green");
	$("#head-statussc4").removeClass("yellow blue red green");
	if (anruf1 != null) {
		$("#progressitem2").addClass("blue");
	}
	if (anruf2 != null) {
		$("#progressitem3").addClass("blue");
	}
	if (anruf3 != null) {
		$("#progressitem4").addClass("blue");
	}
	if (anruf4 != null) {
		$("#progressitem5").addClass("blue");
	}
	if (anruf5 != null) {
		$("#progressitem6").addClass("blue");
	}
	if (mail != null) {
		$("#progressitem7").addClass("blue");
	}
	if (brief != null) {
		$("#progressitem8").addClass("blue");
	}
	if (statusnri === "OPEN") {
		$("#head-statusnri").addClass("blue");
	}
	if (statusnri === "DONE") {
		$("#head-statusnri").addClass("green");
	}
	if (statusnri === "PLANNED") {
		$("#head-statusnri").addClass("yellow");
	}
	if (statusnri === "CLOSED" || statusnri === "STOPPED") {
		$("#head-statusnri").addClass("red");
	}
	if (statussc4 === "OPEN") {
		$("#head-statussc4").addClass("blue");
		$("#progressitem1").addClass("blue");
		$("#progressitem1").html('<i class="ri-bookmark-2-line"></i> OPEN');
	}
	if (statussc4 === "DONE") {
		$("#head-statussc4").addClass("green");
		$("#progressitem1").addClass("green");
		$("#progressitem1").html('<i class="ri-bookmark-2-line"></i> DONE');
	}
	if (statussc4 === "DONE CLOUD") {
		$("#head-statussc4").addClass("green");
		$("#progressitem1").addClass("green");
		$("#progressitem1").html('<i class="ri-bookmark-2-line"></i> DONE');
		$("#head-statussc4").text('DONE');
	}
	if (statussc4 === "PLANNED") {
		$("#head-statussc4").addClass("yellow");
		$("#progressitem1").addClass("yellow");
		$("#progressitem1").html('<i class="ri-bookmark-2-line"></i> PLANNED');
	}
	if (statussc4 === "CLOSED") {
		$("#head-statussc4").addClass("red");
		$("#progressitem1").addClass("red");
		$("#progressitem1").html('<i class="ri-bookmark-2-line"></i> CLOSED');
	}
	if (statussc4 === "STOPPED") {
		$("#head-statussc4").addClass("red");
		$("#progressitem1").addClass("red");
		$("#progressitem1").html('<i class="ri-bookmark-2-line"></i> STOPPED');
	}
	if (statussc4 === "OVERDUE") {
		$("#head-statussc4").addClass("lila");
		$("#progressitem1").addClass("lila");
		$("#progressitem1").html('<i class="ri-bookmark-2-line"></i> OVERDUE');
	}
	if (statussc4 === "PENDING") {
		$("#head-statussc4").addClass("orange");
		$("#progressitem1").addClass("orange");
		$("#progressitem1").html('<i class="ri-bookmark-2-line"></i> PENDING');
	}

}

function clear_string(string) {
	string = string.replace(/%C3%BC/, "ü");
	string = string.replace(/%C3%B6/, "ö");
	string = string.replace(/%C3%84/, "Ä");
	string = string.replace(/%C3%A4/, "ä");
	string = string.replace(/%C3%96/, "Ö");
	string = string.replace(/%C3%B6/, "ö");
	string = string.replace(/%C3%9C/, "Ü");
	string = string.replace(/%C3%BC/, "ü");
	string = string.replace(/%C3%A4/, "ä");
	return string
}


function app_phoner_endoflist() {
	$.confirm({
		closeIcon: true,
		title: 'Ende',
		content: 'In dieser Liste wurden heute alle Kunden geöffnet',
		type: 'purple',
		buttons: {
			ok: {
				text: "zurück zur Übersicht",
				keys: ['esc'],
				action: function () {
					window.location.href = 'route.php?view=phoner';
				}
			},
		}
	});
}


function app_phoner_nextcloud(homeid, reason, hbgdate, comment, hbgdurration, hbguser) {
	$.ajax({
		method: "POST",
		url: "view/load/phoner_load.php",
		data: {
			func: "nextcloud_put",
			homeid: homeid,
			reason: reason,
			hbgdate: hbgdate,
			comment: comment,
			hbgdurration: hbgdurration,
			hbguser: hbguser,
		},
	}).done(function (response) {
		////console.log(response);
	});
}


function appt_move_content(uid) {
	let content = '<div style="padding:20px;" class="row inlinecal"> <div class="row aligncenter"> <div class="col aligncenter"> <div id="selectnewhbg"></div> <div id="picker_' + uid + '" class="apptholder moveapptflat hidden"></div> </div>  </div> </div>'
	return content;
}



function check_isschedulable() {

	var status = $('#head-statussc4').text();
	if (status === 'PLANNED') {
		$('#phonerapp_hbgset').addClass('disabled');
	} else {
		$('#phonerapp_hbgset').removeClass('disabled');
	}

}



// ------------------------------------------------------------------------
// create the calendar instance, fetch all user information. main function to create the calendar
function calendar_update(city) {
	usernamesByDate = {}; // clear the usernames to reset the list
	$('#datetimepicker').val('') // empty input
	$('#select_hbg_comment').val('') // empty comment field
	$.ajax({
		method: "POST",
		url: "view/load/phoner_load.php",
		data: {
			func: "calendar_getall",
			city: city,
		},
	}).done(function (response) {
		console.log('calendar call');
		console.log(response);
		let data = JSON.parse(response);
		console.log(data)

		// Get the city name from the div with id "cityname"
		//let city = $('#cityname').text().trim();

		// Create an array of dates to enable
		let datesToEnable = [];

		for (let i = 0; i < data.length; i++) {
			let record = data[i];
			console.log(record)
			console.log('searching for ' + city)

			let montagCities = record.montag ? record.montag.split(';') : [];
			let dienstagCities = record.dienstag ? record.dienstag.split(';') : [];
			let mittwochCities = record.mittwoch ? record.mittwoch.split(';') : [];
			let donnerstagCities = record.donnerstag ? record.donnerstag.split(';') : [];
			let freitagCities = record.freitag ? record.freitag.split(';') : [];
			let samstagCities = record.samstag ? record.samstag.split(';') : [];
			let sonntagCities = record.sonntag ? record.sonntag.split(';') : [];


			if (montagCities.includes(city)) {
				let date = getDateFromWeekAndDay(record.week, 1);
				datesToEnable.push(date); // Monday
				console.log('Found:' + city + ' on Monday in KW ' + record.week + ' and date ' + date);
				addUsernameToDate(date, record.username); // Add the username to the date
			}
			if (dienstagCities.includes(city)) {
				let date = getDateFromWeekAndDay(record.week, 2);
				datesToEnable.push(date); // Tuesday
				addUsernameToDate(date, record.username); // Add the username to the date
			}
			if (mittwochCities.includes(city)) {
				let date = getDateFromWeekAndDay(record.week, 3);
				datesToEnable.push(date); // Wednesday
				addUsernameToDate(date, record.username); // Add the username to the date
			}
			if (donnerstagCities.includes(city)) {
				let date = getDateFromWeekAndDay(record.week, 4);
				datesToEnable.push(date); // Thursday
				addUsernameToDate(date, record.username); // Add the username to the date
			}
			if (freitagCities.includes(city)) {
				let date = getDateFromWeekAndDay(record.week, 5);
				datesToEnable.push(date); // Friday
				addUsernameToDate(date, record.username); // Add the username to the date
			}
			if (samstagCities.includes(city)) {
				let date = getDateFromWeekAndDay(record.week, 6);
				datesToEnable.push(date); // Saturday
				addUsernameToDate(date, record.username); // Add the username to the date
			}
			if (sonntagCities.includes(city)) {
				let date = getDateFromWeekAndDay(record.week, 7);
				datesToEnable.push(date); // Sunday
				addUsernameToDate(date, record.username); // Add the username to the date
			}
		}

		if (window.flatpickrInstance) { // Destroy previous Flatpickr instance (if it exists)
			window.flatpickrInstance.destroy();
		}

		window.flatpickrInstance = $("#datetimepicker").flatpickr({ // Initialize the Flatpickr instance with the dates enabled
			// ... options ...
			//"enableTime": true,
			locale: 'de',
			dateFormat: "Y-m-d H:i",
			altInput: true,
			altFormat: "j F, H:i, Y",
			minTime: "07:00",
			maxTime: "20:00",
			time_24hr: true,
			minuteIncrement: 15,
			minDate: "today",
			clickOpens: false,
			closeOnSelect: false,
			enable: datesToEnable,
			ignoredFocusElements: [window.document.body],
			onReady: function (selectedDates, dateStr, instance) {
				$(instance._input).attr("id", "flatpickr_select");
				// Attach a click event listener to the Flatpickr's created input element
				$(instance.altInput).on("click", function (event) {
					event.stopPropagation();
					instance.open();
				});
				$(document).on("click", function (event) {
					if (!$(event.target).closest(".flatpickr-calendar, #picker_select_wrapper").length) {
						instance.close();
					}
				});
			},
			onChange: function (selectedDates, dateStr, instance) {
				if (selectedDates.length > 0) {
					console.log('show custom dropdown');
					let formattedDate = dateStr.slice(0, 10);
					showCustomDropdown(formattedDate, instance);
					flatpickrselectedDate = selectedDates[0];
				} else {
					$("#picker_select_wrapper").css("display", "none");
				}
				// reset to not saveable
				$('#phonerapp_safe').html(' <i class="ri-save-3-line"></i><span> Speichern</span>');
				$('#phonerapp_safe').removeClass('unset isset saved');
				$('#phonerapp_safe').addClass('unset');
			},
			onClose: function (selectedDates, dateStr, instance) {
				$("#picker_select_wrapper").css("display", "none");
			},
		});
		console.log('usernamelistdate')
		console.log(usernamesByDate)
		$("#pick_confirm").on("click", function () {
			$('#pickcalendar_wrapper, #eventOverlay').animate({
				left: '-15vw'
			}, 500, function () {
				// Animation complete.
			});

			//updateInputValue();
		});

	});




}

// ------------------------------------------------------------------------
// updates the calendar input value equal to the dragging event
function updateInputValue(currentDate) {

	// Get the new event's starting time
	let newEvent = $('.draggable-event');
	let startTimeText = newEvent.find('.time-text').text();


	// Parse the date and time from the text


	if (currentDate) { // Add this condition
		let [hours, minutes] = startTimeText.split(':');
		let updatedDate = new Date(currentDate.setHours(parseInt(hours), parseInt(minutes)));

		// Format the updated date for #datetimepicker
		let formattedDate = flatpickr.formatDate(updatedDate, flatpickrInstance.config.dateFormat);
		// Format the updated date for altInput
		let altFormattedDate = flatpickr.formatDate(updatedDate, flatpickrInstance.config.altFormat);

		// Update the flatpickr instance
		flatpickrInstance.setDate(formattedDate, false);
		$('#datetimepicker').val(formattedDate)
		// Update the altInput's placeholder
		$(flatpickrInstance.altInput).attr('placeholder', altFormattedDate);
		console.log('formattedDate ' + formattedDate)

		$('#phonerapp_safe').html(' <i class="ri-save-3-line"></i><span> Speichern</span>');
		$('#phonerapp_safe').removeClass('unset isset saved');
		$('#phonerapp_safe').addClass('isset');
	} else {

	}
}


// ------------------------------------------------------------------------
// this shows the hausbegeher list next to the flatpickr instance
function showCustomDropdown(date, instance) {
	var entries = usernamesByDate[date];
	var dropdown = $("#picker_select");

	// Clear the current options
	dropdown.empty();

	console.log('empty and entries:')
	console.log(entries)

	if (entries) {
		for (let entry of entries) {
			var option = $('<div class="picker_select_item">').text(entry);
			dropdown.append(option);
		}

		// Display the custom dropdown
		var calendar = instance.calendarContainer;
		var container = $("#picker_select_wrapper");
		container.css("display", "block");
		calendar.appendChild(container[0]);

	} else {
		// Hide the custom dropdown 
		$("#picker_select_wrapper").css("display", "none");
	}

	// Display the custom dropdown
	var calendar = $(instance.calendarContainer);
	var container = $("#picker_select_wrapper");
	container.css({
		"display": "block",
		"left": calendar.offset().left + 'px' // Initially set the left position to the same as the flatpickr instance
	}).animate({
		"left": calendar.offset().left - container.outerWidth() + 'px' // Animate to the final left position
	}, 100); // Adjust the duration of the animation (300ms in this case)


	// Set the position of the custom dropdown container
	container.css("top", calendar.offset().top + 'px');
	container.css("left", calendar.offset().left - container.outerWidth() + 'px');

	// Set the height of the custom dropdown container to match the Flatpickr's height
	container.css("height", calendar.outerHeight() + 'px');

	calendar.parent().append(container);

	// what happens when a user is selected
	dropdown.children().each(function () {
		$(this).on("click", function () {
			let name = $(this).text()

			dropdown.children().removeClass("selected"); // Remove "selected" class from all options
			$(this).addClass("selected");
			fetch_user_appt(name)
			$('#pick_dropzone').empty();
			let nameDiv = $('<div>').addClass('eventsHolder').text(name);
			$('#pick_dropzone').append(nameDiv);

			$('#pickcalendar_wrapper, #eventOverlay').animate({
				left: '0vw'
			}, 300, function () {
				// Animation complete.
			});
			flatpickrselectedUser = name;
		});
	});
}


// ------------------------------------------------------------------------
// retrieve all user informations from db
function fetch_user_appt(name) {

	let date = $('#datetimepicker').val();
	let city = $('#phonerinfo_cityname').text();
	console.log('fetch ' + city + ' ' + name + ' ' + date)
	$.ajax({
		method: "POST",
		url: "view/load/phoner_load.php",
		data: {
			func: "calendar_getall_appt",
			city: city,
			user: name,
			date: date,
		},
	}).done(function (response) {
		//console.log('calendar appt');
		//console.log(response);
		let data = JSON.parse(response);
		console.log(data)
		addEventsToTable(data);

	});

}

// --------------------------------------------------------------------------------------------
// this adds all appt to the calendar by passing each one to the addeventoverlay
function addEventsToTable(events) {
	let overlay = $("#eventOverlay");
	let pickcalendar = $("#pickcalendar");
	console.log('pickcalendar.position().top' + pickcalendar.position().top)

	overlay.css({
		'position': 'absolute',
		//'top': pickcalendar.position().top,
		'left': pickcalendar.position().left,
		'width': pickcalendar.width(),
		'height': pickcalendar.height(),
	});

	// Clear the events in the overlay before adding new ones
	overlay.empty();

	events.forEach(event => {
		addEventToOverlay(event, overlay);

	});
	addDraggableEvent(overlay);
}


// --------------------------------------------------------------------------------------------
// this adds a single, existing calendar event, to the table
function addEventToOverlay(event, overlay) {
	let startTime = new Date(event.start_time);
	let endTime = new Date(event.end_time);
	let hour = startTime.getHours();
	let minutes = startTime.getMinutes();
	let durationInMinutes = (endTime - startTime) / (60 * 1000);

	let eventTitle = event.title;
	let eventColor = event.color || '#4a934d';
	let eventLocation = event.location;

	let timeColumnWidth = $("#pickcalendar > tbody > tr:first > td:first").width();
	let rowHeight = $("#pickcalendar > tbody > tr:first").outerHeight();

	let eventWrapper = $('<div>').addClass('event-wrapper')
		.attr('data-start-time', startTime)
		.attr('data-end-time', endTime)
		.attr('data-title', eventTitle)
		.attr('data-location', eventLocation)
		.attr('title', eventLocation);

	let marginLeft = calculateMarginLeft(startTime, endTime, overlay);

	let is15MinuteSlot = minutes % 30 === 15;
	let rowIndex = (hour - 7) * 2 + Math.floor(minutes / 30);



	let row = $("#pickcalendar > tbody > tr").eq(rowIndex);
	let topPosition = row.position().top;


	console.log(eventTitle + 'topPosition ' + topPosition)
	if (is15MinuteSlot) {
		let thisrowHeight = row.outerHeight() / 2
		//console.log('height is: ' + thisrowHeight)
		topPosition += thisrowHeight;
		console.log(eventTitle + ' topPosition ' + topPosition)
	}

	eventWrapper.css({
		'top': topPosition + 'px',
		'left': timeColumnWidth + marginLeft + 'px',
		'width': 'calc(100% - ' + (timeColumnWidth + marginLeft) + 'px)',
		'height': row.height() * (durationInMinutes / 30) + 'px',
	});

	eventWrapper.text(eventTitle);
	overlay.append(eventWrapper);
}



// --------------------------------------------------------------------------------------------
// this adds a new (yellow) event that is dragable across the calendar view
function addDraggableEvent(overlay) {
	let rowHeight = $("#pickcalendar > tbody > tr:first").outerHeight();
	let timeColumnWidth = $("#pickcalendar > tbody > tr:first > td:first").width();
	let eventColumnWidth = $("#pickcalendar > tbody > tr:first > td:nth-child(2)").width();
	let snapHeight = rowHeight / 2; // 15-minute increments

	let timeText = $('<div>').addClass('time-text');
	let newEvent = $('<div>').addClass('event-wrapper draggable-event').text('Neuer Termin').prepend(timeText);
	overlay.append(newEvent);

	// Calculate the bottom position
	let overlayHeight = overlay.height();
	let newEventHeight = rowHeight;
	let bottomPosition = overlayHeight - newEventHeight;

	newEvent.css({
		'width': eventColumnWidth,
		'left': timeColumnWidth + 5,
		'top': bottomPosition + 50
	});

	newEvent.draggable({
		grid: [timeColumnWidth, snapHeight],
		axis: "y",
		containment: overlay,
		scroll: false,
		collision: 'fit', // Add this line
		drag: function () {
			let rowIndex = Math.round(($(this).position().top) / snapHeight);
			let hour = Math.floor(rowIndex / 4) + 7;
			let minutes = (rowIndex % 4) * 15;
			let timeString = ('0' + hour).slice(-2) + ':' + ('0' + minutes).slice(-2);

			timeText.text(timeString);

			if (isOverlapping($(this))) {
				$(this).addClass('overlap');
			} else {
				$(this).removeClass('overlap');
			}
		},
		stop: function () {
			$(this).css('z-index', '');
			if (!$(this).hasClass('overlap')) {
				if (flatpickrselectedDate) { // Use the global variable here
					updateInputValue(flatpickrselectedDate);
				}
			} else {
				$(this).animate({ top: bottomPosition + 50 }, 200, function () {
					// This code will be executed after the animation has completed
					$('.time-text').html('') // resets the small time text
					$('#flatpickr_select').val('') // resets the input on overlapp
					$("#flatpickr_select").attr("placeholder", "");
					$('#phonerapp_safe').removeClass('unset isset saved');
					$('#phonerapp_safe').addClass('unset');
				});


			}
		}
	});


	newEvent.on('mousedown', function () {
		$(this).css('cursor', 'grabbing');
	});

	newEvent.on('mouseup', function () {
		$(this).css('cursor', 'grab');

	});
}



function isOverlapping(draggableEvent) {
	let draggableTop = draggableEvent.position().top;
	let draggableBottom = draggableTop + draggableEvent.height();

	let overlapping = false;

	$('.event-wrapper:not(.draggable-event)').each(function () {
		let staticTop = $(this).position().top;
		let staticBottom = staticTop + $(this).height();

		if (draggableTop < staticBottom && draggableBottom > staticTop) {
			overlapping = true;
			return false; // break the loop
		}
	});

	return overlapping;
}




function calculateMarginLeft(startTime, endTime, overlay) {
	let maxOverlapLevel = 0;

	overlay.find('.event-wrapper').each(function () {
		let eventStart = new Date($(this).data('start-time'));
		let eventEnd = new Date($(this).data('end-time'));

		if (eventStart < endTime && startTime < eventEnd) {
			let overlapLevel = parseInt($(this).data('overlap-level'), 10) || 0;
			maxOverlapLevel = Math.max(maxOverlapLevel, overlapLevel + 1);
		}
	});

	return maxOverlapLevel * 20; // 5px margin left for each overlapping event
}




var usernamesByDate = {};
// A helper function to add the username to the date
function addUsernameToDate(date, username) {
	if (usernamesByDate[date]) {
		usernamesByDate[date].push(username);
	} else {
		usernamesByDate[date] = [username];
	}
}

function getDateFromWeekAndDay(week, day) {
	console.log('week ' + week + ' // day ' + day)
	let year = parseInt(week.slice(0, 4));
	let weekNum = parseInt(week.slice(6));
	let date = new Date(year, 0, 1);
	let dayOfWeek = date.getDay();
	let diff = dayOfWeek <= 4 ? 1 - dayOfWeek : 8 - dayOfWeek;
	let weekStartDate = new Date(year, 0, date.getDate() + diff + (weekNum - 1) * 7);
	let resultDate = new Date(weekStartDate);
	resultDate.setDate(resultDate.getDate() + day - 1);

	// Handle timezone offset
	let timezoneOffset = resultDate.getTimezoneOffset() * 60000;
	let localDate = new Date(resultDate.getTime() - timezoneOffset);

	return localDate.toISOString().slice(0, 10);
}



function fakeFlatpickrClick(target) {
	// Delay the position update to ensure that Flatpickr has opened
	setTimeout(() => {
		$(window.flatpickrInstance.altInput).trigger("click");
		// Get the position of the input element with ID 'flatpickr_select'
		let position = $(target).offset();

		// Calculate the top and left positions for the Flatpickr calendar
		let top = position.top + $(target).outerHeight() + 2;
		let left = position.left;

		// Update the position of the Flatpickr calendar
		window.flatpickrInstance.calendarContainer.style.top = top + "px";
		window.flatpickrInstance.calendarContainer.style.left = left + "px";
	}, 10);
}
