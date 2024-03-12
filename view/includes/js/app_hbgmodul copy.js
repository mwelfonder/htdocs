uid = '';
load_table();

$(document).ready(function () {
	var loaded = false;
	console.log("+++LOAD TABLE");
	// ========================
	// 		Search Filter
	$("#search_input").on("input", function () {
		var term = $("#search_input").val();
		if (term.length > 2) {
			console.log(term);
			$(".appt-item-wrapper").each(function (i, obj) {
				let appt_name = $(this).children().find("#appt_info_name").text();
				let appt_adress = $(this).children().find("#appt_info_adress").text();

				//console.log(appt_name);
				//console.log(appt_adress);
				if (appt_name.includes(term) || appt_adress.includes(term)) {
					let id = $(this).attr('id');
					//console.log(appt_name);
					//console.log(appt_adress);
					$(this).addClass("key");
					$(".appt-item-wrapper").each(function (i, obj) {
						if ($(this).hasClass("key") == false) {
							$(this).addClass("opt");
						}
					});
				}
			});
		} else {
			$(".appt-item-wrapper").each(function (i, obj) {
				$(this).removeClass("opt");
				$(this).removeClass("key");
			});
		}
	});
	// ========================
	// 		Open/Close Appt Wrapper
	$(document).on("click", ".appt-item-wrapper", function (e) {
		if ($(e.target).closest('.btnhbginfo').length) {
			return; // Wenn ja, beenden Sie die Funktion frühzeitig
		}
		if ($(e.target).closest('.toggle-address').length) {
			return; // Wenn ja, beenden Sie die Funktion frühzeitig
		}
		//let id = $(this).attr('id');
		isset = false;
		//console.log(e.target)
		if ($(this).find(".apptitem-body").hasClass("colapsed")) {
			isset = true;
		}
		$(".apptitem-body").each(function (i, obj) {
			$(this).addClass("colapsed");
			$(this).children().find(".col-1.modul.icon").removeClass("colapsed");
		});

		$(this).children().find(".col-1.modul.icon").addClass("colapsed");
		$(this).find(".apptitem-body").removeClass("colapsed");
	});
	// ========================
	// 		Close Appt Headerclick
	$(document).on("click", ".apptitem-header", function (e) {
		//console.log($(this).next())
		tar = $(this).next();
		currenttar = $(this);
		if (!$(this).next().hasClass("colapsed")) {
			setTimeout(function () {
				$(tar).addClass("colapsed");
				$(currenttar)
					.children()
					.find(".col-1.modul.icon")
					.removeClass("colapsed");
			}, 100);
		}
	});
	// ========================
	// 		Safe Appt event Yes
	$(document).on("click", ".btnhbginfo.yes", function (e) {
		let ident = $(this).parent().attr("id");
		uid = $(this).parent().attr("id");
		homeid = $(this).parent().find(".thishomeid").attr('id');
		city = $(this).parent().find(".thiscity").attr('id').replace(/ /g, '');
		cityid = $(this).parent().find(".thiscityid").attr('id');
		comment = $(this).parent().find(".apptcomment").val();
		//console.log(ident);
		$.confirm({
			closeIcon: true,
			backgroundDismiss: true,
			title: "HBG erledigt",
			content: "Die HBG wurde durchgeführt & das Protokoll ausgewählt?",
			type: "green",
			buttons: {
				ja: {
					text: "ja - HBG speichern",
					btnClass: "btn-green",
					keys: ["enter"],
					action: function () {

						if ($("#fileupload" + uid).val().length !== 0) {
							safe_hbg_status(uid, comment, "done", "file");
							console.log("file selected");
							filepost(uid, city, cityid, homeid);
							setTimeout(function () {
								$("#loaderwrapper").removeClass("hidden");
								load_table();
							}, 5000);
						} else {
							safe_hbg_status(uid, comment, "done", "no file");
							console.log("no file selected");
							$("#loaderwrapper").removeClass("hidden");
							load_table();
						}

						//
						//app_phoner_load_homeid();
					},
				},
				nein: {
					text: "",
					keys: ["esc"],
					btnClass: "hidden",
					action: function () {
						//app_phoner_load_homeid();
					},
				},
			},
		});
	});
	// ========================
	// 		Safe Appt event No
	$(document).on("click", ".btnhbginfo.no", function (e) {
		let ident = $(this).parent().attr("id");
		uid = $(this).parent().attr("id");
		homeid = $(this).parent().find(".thishomeid").attr('id');
		city = $(this).parent().find(".thiscity").attr('id');
		cityid = $(this).parent().find(".thiscityid").attr('id');
		let client = $(this).parent().find(".thiscityclient").attr('id');
		comment = $(this).parent().find(".apptcomment").val();
		//console.log('thisclient is:' + client)
		var content = "" +
			'<div class="flexrows">' +
			"" +
			'<div class="hbgwrapbtn red"><input type="radio" class="btn-check" name="options" id="option1" autocomplete="off"> <label class="" for="option1">Kunde war nicht da</label></div>' +
			'<div class="hbgwrapbtn "><input type="radio" class="btn-check" name="options" id="option2" autocomplete="off"> <label class="" for="option2">Ich war nicht da</label></div>' +
			'<div class="hbgwrapbtn "><input type="radio" class="btn-check" name="options" id="option3" autocomplete="off"> <label class="" for="option3">HBG nicht durchführbar</label></div>' +
			"</div>";
		if (client === 'Insyte') {
			content = "" +
				'<div class="flexrows">' +
				"" +
				'<div class="hbgwrapbtn red"><input type="radio" class="btn-check" name="options" id="option1" autocomplete="off"> <label class="" for="option1">Kunde war nicht da</label></div>' +
				'<div class="hbgwrapbtn "><input type="radio" class="btn-check" name="options" id="option2" autocomplete="off"> <label class="" for="option2">Ich war nicht da</label></div>' +
				'<div class="hbgwrapbtn "><input type="radio" class="btn-check" name="options" id="option3" autocomplete="off"> <label class="" for="option3">HBG nicht durchführbar</label></div>' +
				'<div class="hbgwrapbtn "><input type="radio" class="btn-check" name="options" id="option4" autocomplete="off"> <label class="" for="option4">App error</label></div>' +
				"</div>";
		}
		$.confirm({
			closeIcon: true,
			backgroundDismiss: true,
			title: "Abbruch",
			content: content,
			type: "orange",
			buttons: {
				r_kd: {
					text: "Abbruch speichern",
					btnClass: "btn-orange",
					keys: ["enter"],
					action: function () {

						if ($("#option1").prop("checked")) {
							console.log("kunde war nicht da");
							cancelstate = 'Kunde war nicht da'
						}
						if ($("#option2").prop("checked")) {
							console.log("ich war nicht da");
							if (comment.length == 0) {
								$.alert({
									title: "Fehler",
									content: "Kommentar darf nicht leer sein!",
									type: "red",
								});
								return false;
							} else {
								cancelstate = 'Ich war nicht da'
							}

						}
						if ($("#option3").prop("checked")) {
							console.log("hbg nicht durchführbar");
							if (comment.length == 0) {
								$.alert({
									title: "Fehler",
									content: "Kommentar darf nicht leer sein!",
									type: "red",
								});
								return false;
							} else {
								cancelstate = 'HBG nicht durchführbar'
							}
						}
						if ($("#option4").prop("checked")) {
							console.log("App error");
							$.ajax({
								method: "POST",
								url: "view/load/hbgmodul_load.php",
								data: {
									func: "load_bugreport",
								},
							}).done(function (response) {
								console.log('response' + response)
								if (response === 'found') {
								} else {
									$.alert({
										title: "Error Report",
										content: 'Bugreport ist <b>verpflichtend!</b><br>Zu dieser HomeID wurde kein Fehler hochgeladen',
										type: "red",
									});
									return false;
								}
							});
							if (comment.length == 0) {
								$.alert({
									title: "Fehler",
									content: "Kommentar darf nicht leer sein!",
									type: "red",
								});
								return false;
							} else {
								cancelstate = 'App error'
							}
						}
						safe_hbg_status(uid, comment, cancelstate, 'no file');
						$("#loaderwrapper").removeClass("hidden");
						load_table();
						//app_phoner_load_homeid();
					},
				},
				nein: {
					text: "",
					keys: ["esc"],
					btnClass: "hidden",
					action: function () {
						//app_phoner_load_homeid();
					},
				},
			},
		});
	});

	// ==========================
	// Collapse all Appts on focus lose
	$(document).mouseup(function (e) {
		var container1 = $(".appt-item-wrapper");
		var container2 = $(".jconfirm");
		// if the target of the click isn't the container nor a descendant of the container
		if (
			!container1.is(e.target) &&
			container1.has(e.target).length === 0 &&
			!container2.is(e.target) &&
			container2.has(e.target).length === 0
		) {
			$(this).find(".apptitem-body").addClass("colapsed");
		}
	});
	// ==============================
	// 		Click on Bugtracker
	$(document).on("click", ".btnbugreport", function (e) {
		getGeoData();
		bugtracker_open();
	});
	// ==============================
	// 		Click on Bugtracker add file
	/*
	$(document).on("click", ".addfilewrapper", function (e) {
		$(this).parent().html(bugtracker_content('upload'))
		$('.bugfilewrapper').parent().append('<div class="col-12">' + bugtracker_content('uploadbox') + '</div>')
	});
*/
	// ==============================
	// 		Bugtracker listen to upload, comment and select if all are filled enable send button
	$(document).on("change input", "#bug_upload0, #bugselect_adress, #bugcomment", function () {
		let is_select = false;
		let is_comment = false;
		let is_upload = false;

		if ($('#bugselect_adress').val() !== null && $('#bugselect_adress').val() !== '-----') {
			is_select = true;
		}
		if ($('#bugcomment').val().length > 3) {
			is_comment = true;
		}
		if ($('#bug_upload0').prop('files').length > 0) {
			is_upload = true;
		}
		console.log('select:' + is_select + '  comment' + is_comment + '  upload' + is_upload)
		if (is_select && is_comment && is_upload) {
			$(".bugtrackerbtnsend").prop("disabled", false);
		}

		console.log(JSON.stringify(getDeviceInfo()));


	});
});

function getGeoData() {
	return new Promise((resolve, reject) => {
		if ('geolocation' in navigator) {
			navigator.geolocation.getCurrentPosition(position => {
				const latitude = position.coords.latitude;
				const longitude = position.coords.longitude;
				resolve({ lat: latitude, long: longitude });
			}, error => {
				console.log('Error getting location: ' + error.message);
				reject({ lat: 'Unknown', long: 'Unknown' });
			});
		} else {
			console.log('Geolocation is not supported by this browser');
			reject({ lat: 'Unknown', long: 'Unknown' });
		}
	});
}




function getDeviceInfo() {
	const userAgent = navigator.userAgent;
	console.log(userAgent)
	$('#console').text(userAgent);
	// Get device type and OS version
	let deviceType, osVersion;

	if (/iPhone|iPad|iPod/i.test(userAgent)) {
		deviceType = 'iOS';
		osVersion = userAgent.match(/OS (\d+)_(\d+)_?(\d+)?/);
		osVersion = osVersion ? `${osVersion[1]}.${osVersion[2]}.${osVersion[3] || 0}` : 'Unknown';
	} else if (/Android/i.test(userAgent)) {
		deviceType = 'Android';
		osVersion = userAgent.match(/Android (\d+)\.?(\d+)?\.?(\d+)?/);
		osVersion = osVersion ? `${osVersion[1]}.${osVersion[2] || 0}.${osVersion[3] || 0}` : 'Unknown';
	} else {
		deviceType = 'Desktop';
		osVersion = userAgent.match(/Windows NT (\d+\.\d+)/);
		osVersion = osVersion ? `Windows ${osVersion[1]}` : 'Unknown';
	}

	// Get browser information
	let browserName, browserVersion;

	if (/Firefox/i.test(userAgent)) {
		browserName = 'Mozilla Firefox';
		browserVersion = userAgent.match(/Firefox\/([0-9.]+)/);
		browserVersion = browserVersion ? browserVersion[1] : 'Unknown';
	} else if (/Chrome/i.test(userAgent)) {
		browserName = 'Google Chrome';
		browserVersion = userAgent.match(/Chrome\/([0-9.]+)/);
		browserVersion = browserVersion ? browserVersion[1] : 'Unknown';
	} else if (/Safari/i.test(userAgent)) {
		browserName = 'Apple Safari';
		browserVersion = userAgent.match(/Version\/([0-9.]+)/);
		browserVersion = browserVersion ? browserVersion[1] : 'Unknown';
	} else if (/Edge/i.test(userAgent)) {
		browserName = 'Microsoft Edge';
		browserVersion = userAgent.match(/Edge\/([0-9.]+)/);
		browserVersion = browserVersion ? browserVersion[1] : 'Unknown';
	} else if (/Opera|OPR\//i.test(userAgent)) {
		browserName = 'Opera';
		browserVersion = userAgent.match(/(?:Opera|OPR)\/([0-9.]+)/);
		browserVersion = browserVersion ? browserVersion[1] : 'Unknown';
	} else if (/Trident/i.test(userAgent)) {
		browserName = 'Microsoft Internet Explorer';
		browserVersion = userAgent.match(/rv:([0-9.]+)/);
		browserVersion = browserVersion ? browserVersion[1] : 'Unknown';
	} else {
		browserName = 'Unknown';
		browserVersion = 'Unknown';
	}

	return {
		deviceType,
		osVersion,
		browserName,
		browserVersion
	};
}

function bugtracker_waiting() {
	// play waiting snail while uploading
	imgsrc = 'https://crm.scan4-gmbh.de/view/images/animation_snail.gif';
	var loadconfirm = $.confirm({
		theme: 'modern',
		title: '',
		content: '<img src="' + imgsrc + '"/>',
		type: 'yellow',
		typeAnimated: true,
		closeIcon: false,
		backgroundDismiss: false,
		buttons: false,
		onOpen: function () {
			// Remove the buttons from the modal
			$(loadconfirm).find('.jconfirm-buttons').addClass('hidden');

			// Hide the close icon using CSS
			$(loadconfirm).find('.jconfirm-closeIcon').css('display', 'none');
		}
	});

	return loadconfirm;

}


function bugtracker_content(content) {
	let tmp = '';
	if (content === 'selectbox') {
		tmp = $('#selectwrapper').html();
		tmp = tmp.replace('hidden', '')
		tmp = tmp.replace('id="select_', 'id="bugselect_adress"')
	}
	if (content === 'commentbox') {
		tmp = '<input style="width:100%" type="text" id="bugcomment" class="form-select condition-box form-control" placeholder="Kommentar" />'
	}
	if (content === 'upload') {
		let count = $('.bug_upload').length;
		tmp = '<input id="bug_upload' + count + '" class="bug_upload" type="file">'
		console.log('countof files:' + count)
	}
	if (content === 'uploadbox') {
		tmp = '<div class="addfilewrapper"><i style="color:green;" class="ri-add-box-line"></i></div>'
	}
	return tmp;
}


function bugtracker_check() {

	/*
	getGeoData().then(deviceGeo => {
		console.log(deviceGeo.lat); // The latitude of the device, or "Unknown"
		console.log(deviceGeo.long); // The longitude of the device, or "Unknown"
	}).catch(error => {
		console.error(error);
	});

	const deviceInfo = getDeviceInfo();
	console.log(deviceInfo.deviceType); // "iOS", "Android", or "Desktop"
	console.log(deviceInfo.osVersion); // The version number of the operating system, or "Unknown"
	console.log(deviceInfo.browserName); // "Mozilla Firefox", "Google Chrome", "Apple Safari", "Microsoft Edge", "Opera", or "Microsoft Internet Explorer"
	console.log(deviceInfo.browserVersion); // The version number of the browser, or "Unknown"
*/
	const formData = new FormData();
	let jsonString;
	const waitingsnail = bugtracker_waiting()

	Promise.all([getDeviceInfo()])
		.then(([deviceInfo]) => {
			const data = {
				deviceType: deviceInfo.deviceType,
				osVersion: deviceInfo.osVersion,
				browserName: deviceInfo.browserName,
				browserVersion: deviceInfo.browserVersion,
			};
			jsonString = JSON.stringify(data);
			console.log('jsonString:', jsonString);

			// create a new FormData object
			const formData = new FormData();

			// append the selected file to the FormData object
			const fileInput = $('#bug_upload0');
			if (fileInput.prop('files').length > 0) {
				const file = fileInput.prop('files')[0];
				let fileName = $('#bugselect_adress').val().trim().replace(/[\r\n\s()]+/g, '').replace(/ä/g, 'ae').replace(/ü/g, 'ue').replace(/ö/g, 'oe').replace(/ß/g, 'ss');
				const fileExtension = file.name.split('.').pop();
				fileName += '.' + fileExtension;
				formData.append('bug_file', file, fileName);
			}

			// append the other form data to the FormData object
			formData.append('bug_address', $('#bugselect_adress').val());
			formData.append('bug_homeid', $('#bugselect_adress option:selected').attr('id'));
			formData.append('bug_comment', $('#bugcomment').val());
			formData.append('bug_infos', jsonString);

			// send the form data to the server using jQuery's ajax method
			$.ajax({

				url: "view/load/hbgmodul_bugtrackerupload.php",
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function (response) {
					// close the snail
					waitingsnail.close();
					console.log(response)
					// display success message
					$.confirm({
						theme: 'modern',
						title: '<div style="text-align:center;" class="row"><span style="display: block; text-align: center; font-size: 24px;"><i style="color:green;" class="ri-checkbox-circle-line"></i></span></div>',
						content: 'Report&nbsp;gespeichert!',
						type: 'green',
						typeAnimated: true,
						backgroundDismiss: true,
						buttons: {
							ok: {
								text: 'OK',
								btnClass: 'btn-green',
								action: function () {
									// handle OK button click
								}
							}
						}
					});
				},
				error: function (xhr, status, error) {
					// close the snail
					waitingsnail.close();
					// display error message
					$.confirm({
						title: '<span style="display: block; text-align: center;"><i style="color:red;font-size: 24px" class="ri-emotion-sad-line"></i> Woops</span>',
						content: 'Etwas ist schiefgegangen',
						type: 'red',
						typeAnimated: true,
						backgroundDismiss: true,
						buttons: {
							ok: {
								text: 'OK',
								btnClass: 'btn-red',
								action: function () {
									// handle OK button click
								}
							}
						}
					});
				}
			});
		})
		.catch(error => {
			jsonString = JSON.stringify({ error: error.message });
			console.error(error);
		});





}

function bugtracker_open() {
	let content = bugtracker_content();
	$.confirm({
		closeIcon: true,
		backgroundDismiss: true,
		theme: 'material',
		columnClass: 'col-md-12',
		title: '<i class="ri-bug-fill"></i> Bug report',
		content: '<div class="row"><div class="col-12">Bitte beschreibe was/wann/wie passiert ist oder nicht funktioniert.</br><b>NUR APP FEHLER! KEINE Fehler aus dem CRM hier melden!</b></div>\
		<div class="col-12">' + bugtracker_content('selectbox') + '</div><div class="col-12">' + bugtracker_content('commentbox') + '</div>\
		<div style="padding-top:5px;" class="col-12 bugfilewrapper">' + bugtracker_content('upload') + '</div>\
		</div>',
		type: 'red',
		typeAnimated: true,
		buttons: {
			send: {
				text: 'Senden',
				id: 'bugsendbtn',
				btnClass: 'btn-red bugtrackerbtnsend',
				action: function () {
					bugtracker_check();
				},
				isDisabled: true,
			},
			close: function () {
			}
		}
	});
}



let datePicker;

document.addEventListener("DOMContentLoaded", function () {
	// Initialisieren Sie den Flatpickr und weisen Sie die Instanz der "datePicker"-Variable zu
	datePicker = flatpickr("#datePicker", {
		defaultDate: new Date(),
		dateFormat: "Y-m-d",
		onChange: function (selectedDates, dateStr, instance) {
			load_table(dateStr);
		}
	});
});


function load_table(dateStr, nameStr) {

	console.log("+++LOAD TABLE");
	$("#loaderwrapper").removeClass("hidden");
	$.ajax({
		method: "POST",
		url: "view/load/hbgmodul_load.php",
		data: {
			func: "load_table",
			selectedDate: dateStr
		},
	}).done(function (response) {
		//console.log("+++RESPONESE " + response);
		$("#hbglistbody").html(response);
		let appt_all = $(".appt-item-wrapper.open").length;
		let appt_open = $(".appt-item-wrapper.open:visible").length;
		let appt_done = $(".appt-item-wrapper.done:visible").length;
		//console.log(appt_all);
		$("#appt_all_text").text(appt_all);
		$("#appt_unset_text").text(appt_open);
		$("#appt_done_text").text(appt_done);
		$("#loaderwrapper").addClass("hidden");
	});

}

function safe_hbg_status(uid, comment, status, file) {
	username = $('#usernameshort').text();
	console.log("safe ident");
	$.ajax({
		method: "POST",
		url: "view/load/hbgmodul_load.php",
		data: {
			func: "safe_hbg_status",
			uid: uid,
			status: status,
			comment: comment,
			user: username,
			file: file,
		},
	}).done(function (response) {
		console.log(response);

	});
}

function filepost(uid, city, cityid, homeid) {
	console.log(uid)
	console.log(city)
	console.log(cityid)
	console.log(homeid)

	anim_upload('upload on')
	username = $('#usernameshort').text();
	var formData = new FormData();
	// get extension from file input
	let nowdate = _nowdate();
	let nowtime = _nowtime();
	var ext = $("#fileupload" + uid).val().split(".").pop().toLowerCase();
	// check if extension is pdf
	if (ext != "pdf") {
		console.log("no pdf")
		anim_upload('failed')
		return;
	}


	var specialChars = { 'ü': 'ue', 'Ü': 'Ue', 'ä': 'ae', 'Ä': 'Ae', 'ö': 'oe', 'Ö': 'Oe', 'ß': 'ss' };
	for (var char in specialChars) {
		var regex = new RegExp(char, 'g');
		city = city.replace(regex, specialChars[char]);
	}

	let filename = cityid + city + '_' + nowdate + "_" + nowtime + "_" + username + "_" + homeid + '.' + ext + '.' + uid;
	console.log(filename)

	formData.append(
		"file",
		$("#fileupload" + uid)[0].files[0],
		filename
	);
	$.ajax({
		url: "view/load/upload_hbgmodul.php",
		type: "POST",
		data: formData,
		processData: false, // tell jQuery not to process the data
		contentType: false, // tell jQuery not to set contentType
		success: function (data) {
			console.log(data);
			anim_upload('success')
			return true;
			setTimeout(function () {

			}, 100);

		},
		error: function () {
			anim_upload('failed')
			return false;

		},
	});
}


function anim_upload(state) {
	$(".file-input").each(function (i, obj) {
		if (state === 'success') {
			$(this).addClass("hidden");
			$('.uploadloader').addClass("hidden");
			$('.swal2-animate-success-icon').removeClass("hidden");
			//setTimeout(function () {
			//		$(".swal2-animate-success-icon").addClass("hidden");
			//}, 1000);
		} else if (state === 'failed') {
			$(this).removeClass("hidden");
			$('.uploadloader').addClass("hidden");
			$('.swal2-animate-error-icon').removeClass("hidden");
		} else if (state === 'upload on') {
			console.log('spinner now')
			$(this).addClass("hidden");
			$('.uploadloader').removeClass("hidden");
		} else {
			console.log('NO ANIM FOUND')
		}
	});
}

//create function to upload file and transfer data to server
function uploadFile() {
	var form = $("#fileuploadform")[0];
	var data = new FormData(form);
	console.log("FILEDATA");
	console.log(data);
	console.log("FILEDATA");
	$.ajax({
		type: "POST",
		enctype: "multipart/form-data",
		url: "view/load/upload.php",
		data: data,
		processData: false,
		contentType: false,
		cache: false,
		timeout: 600000,
		success: function (data) {
			console.log("SUCCESS : ", data);
			loader("off");
			return true;
		},
		error: function (e) {
			console.log("ERROR : ", e);
			loader("off");
			return false;
		},
	});
}

function _nowdate() {
	var dateObj = new Date();
	var month = String(dateObj.getUTCMonth() + 1); //months from 1-12
	var day = String(dateObj.getUTCDate());
	var year = String(dateObj.getUTCFullYear());

	if (day.length === 1) {
		day = "0" + day;
	}
	if (month.length === 1) {
		month = "0" + month;
	}

	newdate = year + "_" + month + "_" + day;
	return newdate;
}

function _nowtime() {
	var d = new Date(); // for now
	let h = String(d.getHours()); // => 9
	let m = String(d.getMinutes()); // =>  30
	if (h.length === 1) {
		h = "0" + h;
	}
	if (m.length === 1) {
		m = "0" + m;
	}
	let time = h + "" + m;
	return time;
}