

var date = '';
load_usersidebar();
$(document).ready(function () {

	// -------------------------------
	// Date Picker
	$("#hbgdatepick").flatpickr({
		dateFormat: "Y-m-d",
		altInput: true,
		altFormat: "F j, Y",
		locale: {
			firstDayOfWeek: 1
		},
		"disable": [
			function (date) {
				// return true to disable
				return (date.getDay() === 0 || date.getDay() === 7);

			}
		],

	});
	// on hbgdatepick change function
	$("#hbgdatepick").change(function () {

		load_usersidebar();
		date = $(this).val();
		/*let user = 'all';
		$.ajax({
			method: "POST",
			url: "view/load/hbgcheckv2_load.php",
			data: {
				func: "load_usercontent",
				user: user,
				date: date,
			},
		}).done(function (response) {
			//console.log('load content: '+response);
			$("#loaderwrapper2").addClass("hidden");
			$("#checkdisplaycontent").removeClass("hidden");
			$("#checkdisplaycontent").html(response);
		});
		*/
	});

	// -------------------------------
	// Logfile
	$(document).on("click", "#logfile", function () {
		console.log(date)
		$.ajax({
			method: "POST",
			url: "view/load/hbgcheckv2_load.php",
			data: {
				func: "load_logfile",
				date: date,
			},
		}).done(function (response) {
			console.log(response)
			$("#loaderwrapper2").addClass("hidden");
			$("#checkdisplaycontent").removeClass("hidden");

			$("#checkdisplaycontent").html(response);
		});
	});
	// -------------------------------
	// screenshot / excel click
	$(document).on("click", ".donebutton", function () {
		$(".donebutton").each(function (i, obj) {
			$(this).removeClass('selected');
		});
		$(this).addClass('selected');
	});
	// -------------------------------
	// Settings Gear button
	$(document).on("click", ".checkgearbox", function () {
		let id = $(this).parents().closest(".checkwrapperitem").attr('id');
		let homeid = $('#' + id).children().find('#homeid').val();
		$.confirm({
			closeIcon: true,
			backgroundDismiss: true,
			type: 'red',
			title: 'HBG Zurücksetzen?',
			content: 'Sicher das du diese HBG zurücksetzen willst?',
			buttons: {
				ja: {
					text: "Ja zurücksetzen",
					btnClass: 'btn-red',
					action: function () {
						console.log('reset HBG: ' + id)
						$.ajax({
							method: "POST",
							url: "view/load/hbgcheckv2_load.php",
							data: {
								func: "reset_hbgmodulitem",
								uid: id,
								homeid: homeid,
							},
						}).done(function (response) {
							// fake click on .notapproved to reload content
							console.log(response)
							$(".notapproved.selected").click();
						});
					},
				},
				nein: function () {
					//$.alert('Canceled!');
				}
			}
		});
	});

	// -------------------------------
	// Clickevent on Userlist Sidebar
	
	$("#hbguserlist").on("click", ".hbguserlist-item-wrapper", function () {
		// for each class function
		$(".notapproved").each(function (i, obj) {
			$(this).removeClass('selected');
		});
		$(this).addClass("selected");
		$("#loaderwrapper2").removeClass("hidden");
		$("#checkdisplaycontent").addClass("hidden");
		let id = $(this).attr("id");
		let arr = id.split("sidebartab_");
		let user = arr[1];
		//date = $('#hbgdatepick').val();
		console.log("+++USER " + user);
		console.log("+++DATE " + date);
		//console.log("+++USER " + user);
		$.ajax({
			method: "POST",
			url: "view/load/hbgcheckv2_load.php",
			data: {
				func: "load_usercontent",
				user: user,
				date: date,
			},
		}).done(function (response) {
			$("#loaderwrapper2").addClass("hidden");
			$("#checkdisplaycontent").html(response);
			$("#checkdisplaycontent").removeClass("hidden");
		});
	});

	// -------------------------------
	// Toogle Select for DONE
	$(document).on("click", ".btn-interact-phonerapp.secondselect.hgreen", function () {
		let id = $(this).parents().closest(".checkwrapperitem").attr('id');
		//console.log(id);
		$('#' + id).children().find('.select_stopped').addClass("hidden");
		$('#' + id).children().find('.select_open').addClass("hidden");
		$('#' + id).children().find('.donebuttons').removeClass("hidden");
		$(".btn-interact-phonerapp.secondselect").each(function (i, obj) {
			$(this).removeClass('selected');
		});
		$(".donebutton").each(function (i, obj) {
			$(this).removeClass('selected');
		});
		$(this).addClass('selected');
	});
	// -------------------------------
	// Toogle Select for PLANNED 
	$(document).on("click", ".btn-interact-phonerapp.secondselect.hblue", function () {
		let id = $(this).parents().closest(".checkwrapperitem").attr('id');
		//console.log(id);
		$('#' + id).children().find('.select_stopped').addClass("hidden");
		$('#' + id).children().find('.select_open').removeClass("hidden");
		$('#' + id).children().find('.donebuttons').addClass("hidden");
		$(".btn-interact-phonerapp.secondselect").each(function (i, obj) {
			$(this).removeClass('selected');
		});
		$(".donebutton").each(function (i, obj) {
			$(this).removeClass('selected');
		});
		$(this).addClass('selected');
	});
	// -------------------------------
	// Toogle Select for STOPPED
	$(document).on("click", ".btn-interact-phonerapp.secondselect.hred", function () {
		let id = $(this).parents().closest(".checkwrapperitem").attr('id');
		console.log(id);
		$('#' + id).children().find('.select_stopped').removeClass("hidden");
		$('#' + id).children().find('.select_open').addClass("hidden");
		$('#' + id).children().find('.donebuttons').addClass("hidden");
		$(".btn-interact-phonerapp.secondselect").each(function (i, obj) {
			$(this).removeClass('selected');
		});
		$(".donebutton").each(function (i, obj) {
			$(this).removeClass('selected');
		});
		$(this).addClass('selected');
	});
	// -------------------------------
	// State switch for save button
	$(document).on("click", ".btn-interact-phonerapp.secondselect, .donebutton", function () {
		console.log($(this).attr('id'));
		if ($(this).attr('id') !== 'hbgcheck_done') {
			let id = $(this).parents().closest(".checkwrapperitem").attr('id');
			$('#' + id).children().find('.savehbgbtn').removeClass("unset");
			$('#' + id).children().find('.savehbgbtn').addClass("isset");
		} else {
			let id = $(this).parents().closest(".checkwrapperitem").attr('id');
			$('#' + id).children().find('.savehbgbtn').removeClass("isset");
			$('#' + id).children().find('.savehbgbtn').addClass("unset");
		}
	});
	// -------------------------------
	// Save button
	$(document).on("click", ".savehbgbtn", function () {
		let id = $(this).parents().closest(".checkwrapperitem").attr('id');
		if ($(this).hasClass('isset')) {
			let source = '';
			let result = $('#' + id).children().find('.selected').attr("id");
			let sel_open = $('#' + id).children().find('.select_open').val();
			let sel_stopped = $('#' + id).children().find('.select_stopped').val();
			let comment = $('#' + id).children().find('#check_comment').val();
			let homeid = $('#' + id).children().find('#homeid').val();
			console.log('result:' + result)
			console.log('source:' + source)
			console.log('sel_open:' + sel_open)
			console.log('sel_stopped:' + sel_stopped)
			console.log('comment:' + comment)
			console.log('homeid:' + homeid)

			$('#' + id).children().find('.showprotokoll').trigger('click')
			$('#' + id).children('.checkbody').addClass('collapsed')
			$('#' + id).next().children('.checkbody').removeClass('collapsed')
			$(this).removeClass('isset');
			$(this).addClass('saved');
			$(this).html(' <i class="ri-save-3-line"></i><span> Gespeichert!</span>');
			$('html, body').animate({
				scrollTop: $('#' + id).offset().top
			}, 'slow');
			if ($('#' + id).children().find('.screenshot').hasClass("selected")) {
				source = 'screenshot';
			}
			if ($('#' + id).children().find('.excel').hasClass("selected")) {
				source = 'excel';
			}
			if ($('#' + id).children().find('.protokoll').hasClass("selected")) {
				source = 'protokoll';
			}
			if (result === 'hbgcheck_open') {
				safe_hbgcheck(homeid, id, result, sel_open, comment, source);
			} else if (result === 'hbgcheck_stopped') {
				safe_hbgcheck(homeid, id, result, sel_stopped, comment, source);
			} else {
				safe_hbgcheck(homeid, id, result, '', comment, source);
			}

		}
	});




	// -------------------------------
	// HBG wrapper item > click on header to colapse/expand
	// get the parent id and toggle the class colapsed from the child
	$(document).on("click", ".checkheader", function () {
		$(".protokoll-container").addClass("hidden");
		$('.checkbody').each(function (i, obj) {
			$(this).addClass('collapsed');
		});
		$(this).next().toggleClass('collapsed');
		$(this).next().removeAttr("style");
		// hide each protokoll-container
	});
	// -------------------------------
	// Toggle Protokoll
	$(document).on('click', '.showprotokoll', function () {
		let id = $(this).parents().closest(".checkwrapperitem").attr('id');
		// find closest child checkbody
		let container = $(this).closest(".checkwrapperitem").find(".checkbody");
		// remove custome style from container

		if (container.hasClass("collapsed")) {
			container.removeAttr("style");
			container.animate({ height: "0" }, 10);
		} else {
			container.animate({ height: "100%" }, 10);
		}
		$(".protokoll-container").toggleClass("hidden");
	});
	// -------------------------------
	// Autofill dropdown
	$(document).on('click', '.list-autofill', function () {
		let id = $(this).parents().closest(".checkwrapperitem").attr('id');
		let target = $('#' + id).children().find('#check_comment');
		let value = $(this).text();
		if (value === 'KD nicht da') {
			target.val('Kunde war nicht da, neu terminieren - Wenn der KD sagt, er war da, bitte Alex bescheid geben.');
		} else if (value === 'KD ist nicht mit dem Tiefbau einverstanden') {
			target.val('Kunde ist nicht mit dem Tiefbau einverstanden & möchte den Vertrag kündigen.');
		} else if (value === 'KD kontaktieren und nachfragen, ob er noch Interesse am Vertrag hat') {
			target.val('Kunde kontaktieren und nachfragen, ob er noch Interesse am Vertrag hat & einen Termin abmachen möchte. Wenn Ja = Termin, Wenn Nein = Kunde verweigert.');
		} else if (value === 'KD war krank & bitte neu einplanen') {
			target.val('Kunde war krank & bitte neu einplanen.');
		}
	});
	// -------------------------------
	// unblock check buttons
	$(document).on('click', '.hbgblocker ', function () {
		let id = $(this).parents().closest(".checkwrapperitem").attr('id');
		let homeid = $('#' + id).children().find('#homeid').val();
		$.confirm({
			closeIcon: true,
			backgroundDismiss: true,
			type: 'orange',
			title: 'Check reset',
			content: 'Sicher das du den Check zurücksetzen möchtest?',
			buttons: {
				ja: {
					text: "Ja zurücksetzen",
					action: function () {

						$.ajax({
							method: "POST",
							url: "view/load/hbgcheckv2_load.php",
							data: {
								func: "reset_hbgcheck",
								uid: id,
								homeid: homeid,
							},
						}).done(function (response) {
							//console.log("+++RESPONESE " + response);
							$('#' + id).removeClass("checked");
							$('#' + id).children().find('.title_isreviewed').html('');
							$('#' + id).children().find('.hbgblocker').addClass('hidden');
						});
					},
				},

			}
		});
	});


	$('.dropdown-toggle').dropdown()


});


function load_usersidebar() {
	date = $('#hbgdatepick').val();
	$.ajax({
		method: "POST",
		url: "view/load/hbgcheckv2_load.php",
		data: {
			func: "load_usersidebar",
			date: date,
		},
	}).done(function (response) {
		//console.log("+++RESPONESE " + response);
		$("#hbguserlist").html(response);
		var int_done = 0;
		var int_cancel = 0;
		var int_open = 0;
		var int_total = 0;
		$(".mystat_done:visible").each(function (i, obj) {
			int_done = int_done + parseInt($(this).text());
		});
		$(".mystat_canceld:visible").each(function (i, obj) {
			int_cancel = int_cancel + parseInt($(this).text());
		});
		$(".mystat_open:visible").each(function (i, obj) {
			int_open = int_open + parseInt($(this).text());
		});
		$(".mystat_total:visible").each(function (i, obj) {
			int_total = int_total + parseInt($(this).text());
		});

		$('#mystattotaldone').html('<i class="ri-checkbox-circle-line"></i> ' + int_done)
		$('#mystattotalcanceld').html('<i class="ri-close-circle-line"></i> ' + int_cancel)
		$('#mystattotalopen').html('<i class="ri-question-line"></i> ' + int_open)
		$('#mystattotaltotal').html('<i class="ri-hashtag"></i> ' + int_total)
	});
}

function safe_hbgcheck(homeid, id, result, selection, comment, source) {
	console.log('safe_hbgcheck')

	$.ajax({
		method: "POST",
		url: "view/load/hbgcheckv2_load.php",
		data: {
			func: "safe_hbgcheck",
			homeid: homeid,
			result: result,
			selection: selection,
			comment: comment,
			uid: id,
			source: source,
		},
	}).done(function (response) {
		console.log("+++RESPONESE " + response)
		load_usersidebar();
		$('#' + id).addClass("checked");
		$('#' + id).children().find('.title_isreviewed').html('<i style="color:rgb(26, 169, 26);" class="ri-check-double-line"></i> checked');
		$('#' + id).children().find('.hbgblocker').removeClass('hidden');

		$('.swal2-success').removeClass("hidden")
		setTimeout(function () {
			$(".swal2-success").addClass("hidden");
		}, 2000);

	});
}

