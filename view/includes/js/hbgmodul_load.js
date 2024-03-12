
load_table()
$(document).ready(function () {
	var loaded = false;
	console.log("+++LOAD TABLE");
	// ========================
	// 		Search Filter
	$('#search_input').on('input', function () {
		var term = $('#search_input').val();
		if (term.length > 2) {
			console.log(term)
			$('.appt-item-wrapper').each(function (i, obj) {
				let appt_name = $(this).children().find('#appt_info_name').text();
				let appt_adress = $(this).children().find('#appt_info_adress').text();
				//console.log(appt_name);
				//console.log(appt_adress);
				if (appt_name.includes(term) || appt_adress.includes(term)) {
					console.log(appt_name)
					console.log(appt_adress)
					$('.appt-item-wrapper').each(function (i, obj) {
						$(this).addClass('opt')
					});
					$(this).removeClass('opt')
				}
			});
		} else {
			$('.appt-item-wrapper').each(function (i, obj) {
				$(this).removeClass('opt')
			});
		}
	});
	// ========================
	// 		Open/Close Appt Wrapper
	$(document).on("click", '.appt-item-wrapper', function (e) {
		//let id = $(this).attr('id');
		isset = false;
		//console.log(e.target)
		if (($(this).find('.apptitem-body').hasClass('colapsed'))) { isset = true; }
		$('.apptitem-body').each(function (i, obj) {
			$(this).addClass("colapsed");
			$(this).children().find('.col-1.modul.icon').removeClass('colapsed')
		});

		$(this).children().find('.col-1.modul.icon').addClass('colapsed')
		$(this).find('.apptitem-body').removeClass('colapsed');

	})
	// ========================
	// 		Close Appt Headerclick
	$(document).on("click", '.apptitem-header', function (e) {
		//console.log($(this).next())
		tar = $(this).next();
		currenttar = $(this);
		if (!$(this).next().hasClass('colapsed')) {

			setTimeout(function () {
				$(tar).addClass('colapsed');
				$(currenttar).children().find('.col-1.modul.icon').removeClass('colapsed')

			}, 100);
		}
	})
	// ========================
	// 		Safe Appt event Yes
	$(document).on("click", '.btnhbginfo.yes', function (e) {
		let ident = $(this).parent().attr('id');
		console.log(ident);
		$.confirm({
			closeIcon: true,
			title: 'HBG erledigt',
			content: 'Die HBG wurde durchgeführt & das Protokoll hochgeladen?',
			type: 'green',
			buttons: {
				ja: {
					text: "ja - HBG speichern",
					btnClass: 'btn-primary',
					keys: ['enter'],
					action: function () {
						safe_hbg_status(ident, 'done');
						//app_phoner_load_homeid();
					}

				},
				nein: {
					text: "",
					keys: ['esc'],
					btnClass: 'hidden',
					action: function () {

						//app_phoner_load_homeid();
					}
				},
			}
		});
	})
	// ========================
	// 		Safe Appt event No
	$(document).on("click", '.btnhbginfo.no', function (e) {
		$.confirm({
			closeIcon: true,
			title: 'Abbruch',
			content: '' + '<div class="flexrows">' +
				'' +
				'<div class="hbgwrapbtn red"><input type="radio" class="btn-check" name="options" id="option1" autocomplete="off"> <label class="" for="option1">Kunde war nicht da</label></div>' +
				'<div class="hbgwrapbtn "><input type="radio" class="btn-check" name="options" id="option2" autocomplete="off"> <label class="" for="option2">Ich war nicht da</label></div>' +
				'<div class="hbgwrapbtn "><input type="radio" class="btn-check" name="options" id="option3" autocomplete="off"> <label class="" for="option3">HBG nicht durchführbar</label></div>' +



				'</div>',
			type: 'red',
			buttons: {
				r_kd: {
					text: "ja - HBG speichern",
					btnClass: 'btn-primary',
					keys: ['enter'],
				},
				nein: {
					text: "",
					keys: ['esc'],
					btnClass: 'hidden',
					action: function () {
						//app_phoner_load_homeid();
					}
				},
			}
		});
	})

	// ==========================
	// Collapse all Appts on focus lose
	$(document).mouseup(function (e) {
		var container1 = $(".appt-item-wrapper");
		var container2 = $(".jconfirm");
		// if the target of the click isn't the container nor a descendant of the container
		if ((!container1.is(e.target) && container1.has(e.target).length === 0) && (!container2.is(e.target) && container2.has(e.target).length === 0)) {
			$(this).find('.apptitem-body').addClass('colapsed');
		}
	});
});





function load_table() {
	console.log("+++LOAD TABLE");
	$('#loaderwrapper').removeClass('hidden')
	$.ajax({
		method: "POST",
		url: "view/load/modul_hbg.php",
		data: {
			func: "load_table",
		},
	}).done(function (response) {
		console.log("+++RESPONESE " + response);
		$('#hbglistbody').html(response);
		let appt_all = $('.appt-item-wrapper.open').length;
		let appt_open = $('.appt-item-wrapper.open:visible').length;
		let appt_done = $('.appt-item-wrapper.done').length;
		//console.log(appt_all);
		$('#appt_all_text').text(appt_all);
		$('#appt_unset_text').text(appt_open);
		$('#appt_done_text').text(appt_done);
		$('#loaderwrapper').addClass('hidden')
	});

}

function safe_hbg_status(ident, status) {
	console.log('safe ident')
	$.ajax({
		method: "POST",
		url: "view/load/modul_hbg.php",
		data: {
			func: "safe_hbg_status",
			ident: ident,
			status: status,
		},
	}).done(function (response) {
		console.log(response)
		load_table();
	});
}