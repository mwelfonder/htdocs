
const currentUrl = window.location.href;
console.log(currentUrl);

$(document).ready(function () {
	init_tables();

	// --------------------------------
	// on date/time/comment change color save button
	$(document).on("change", ".inputfaded", function (e) {
		$(this).closest("tr").find(".savechanges").addClass("inputchanged");
		//alert( "Handler for .change() called." );
	});
	$(document).on("click", ".inputchanged", function (e) {
		$(this).removeClass("inputchanged");
		$(this).closest("tr").find(".uid").addClass("thisf");
		let newdate = $(this).closest("tr").find(".datechange").val();
		let newtime = $(this).closest("tr").find(".timechange").val();
		let newcomment = $(this).closest("tr").find(".commentchange").val();
		//let newuser = $(this).closest("tr").find(".td-user").text();
		let uid = $(this).closest("tr").find(".uid").attr("id");
		// console.log(newdate);
		// console.log(newtime);
		change_appoint(newdate, newtime, newcomment, uid);
	});

	//====================================
	// Activate UID
	$("#tbody_active>tbody").on("click", "tr", function (e) {
		$("#tbody_active>tbody tr").each(function (i, obj) {
			$(this).removeClass("selected");
		});
		$(this).addClass("selected");
		console.log("tar:" + $(e.target).attr("class"));
		homeid = $(this).children(".td-homeid").text();
		if ($(e.target).hasClass("ri-checkbox-blank-line")) {
			ident = $(e.target).parent().parent().attr("id");
			// console.log(ident);
			if (ident.length !== 0) {
				city = $(this).children(".td-city").text();
				street = $(this).children(".td-street").text();
				user = $(this).children(".td-user").text();
				uid = $(this).children().attr("id");

				$.confirm({
					closeIcon: true,
					backgroundDismiss: true,
					title: "Speichern?",
					content:
						city + ", " + street + " wurde für " + user + " freigeschaltet?",
					type: "green",
					buttons: {
						ok: {
							text: "ja",
							btnClass: "btn-green",
							keys: ["enter"],
							action: function () {
								changestate();
								change_nextcloud(uid, "activate");
								sendmail(uid);
							},
						},
					},
				});
			}
		} // target = checkbox
		//===============================
		// Copy homeid on td click
		if ($(e.target).hasClass("td-homeid")) {
			homeid = $(this).children(".td-homeid").text();
			myclip(homeid);
		}
		//console.log("tr clicked ");
	});

	////=============================
	/// Filter switch UGG DGF GVG
	$("#btn_filter_ugg, #btn_filter_dgf, #btn_filter_gvg, #btn_filter_glasfaserplus").click(function () {
		id = $(this).attr("id");
		let count = 0;
		if (id === "btn_filter_ugg") {
			var carrier = "UGG";
		} else if (id === "btn_filter_dgf") {
			var carrier = "DGF";
		} else if (id === "btn_filter_gvg") {
			var carrier = "GVG";
		} else if (id === "btn_filter_glasfaserplus") {
			var carrier = "glasfaserplus";
		}
		if ($(this).children().children().attr("class") === "ri-checkbox-fill") {
			// remove checked
			$(this).children().children().attr("class", "ri-checkbox-blank-line");
			$("#tbody_active>tbody tr").each(function (i, obj) {
				if ($(this).hasClass(carrier)) {
					$(this).addClass("hidden");
				}
				if (!$(this).hasClass("hidden")) {
					count++;
				}
			});
		} else {
			// set checked
			$(this).children().children().attr("class", "ri-checkbox-fill");
			$("#tbody_active>tbody tr").each(function (i, obj) {
				if ($(this).hasClass(carrier)) {
					$(this).removeClass("hidden");
				}
				if (!$(this).hasClass("hidden")) {
					count++;
				}
			});
		}
	});


	/// Filter switch Insyte Moncobra
	$("#btn_filter_moncobra, #btn_filter_insyte, #btn_filter_fol").click(function () {
		let count = 0;
		let clientcount = 0;
		id = $(this).attr("id");
		if (id === "btn_filter_moncobra") {
			var client = "Moncobra";
		} else if (id === "btn_filter_insyte") {
			var client = "Insyte";
		} else if (id === "btn_filter_fol") {
			var client = "FOL";
		}
		if ($(this).children().children().attr("class") === "ri-checkbox-fill") {
			// remove checked
			$(this).children().children().attr("class", "ri-checkbox-blank-line");
			$("#tbody_active>tbody tr").each(function (i, obj) {
				if ($(this).hasClass(client)) {
					$(this).addClass("hidden");
				}
			});
		} else {
			// set checked
			$(this).children().children().attr("class", "ri-checkbox-fill");
			$("#tbody_active>tbody tr").each(function (i, obj) {
				count++;
				if ($(this).hasClass(client)) {
					$(this).removeClass("hidden");
				}
			});
		}
		count = $("#tbody_active>tbody tr:visible").length;
	});

	/// Filter switch Aktiv Inaktiv
	$("#btn_filter_inaktiv").click(function () {
		let count = 0;
		let clientcount = 0;
		if ($(this).children().children().attr("class") === "ri-checkbox-fill") {
			// remove checked
			$(this).children().children().attr("class", "ri-checkbox-blank-line");
			$("#tbody_active>tbody tr").each(function (i, obj) {
				if ($(this).hasClass("statusinaktiv")) {
					$(this).addClass("statushidden");
				}
			});
		} else {
			// set checked
			$(this).children().children().attr("class", "ri-checkbox-fill");
			$("#tbody_active>tbody tr").each(function (i, obj) {
				if ($(this).hasClass("statusinaktiv")) {
					$(this).removeClass("statushidden");
				}
			});
		}
	});
});

function change_appoint(date, time, comment, uid) {
	$.ajax({
		method: "POST",
		url: "view/load/activation_load.php",
		data: {
			func: "change_datetime",
			date: date,
			time: time,
			comment: comment,
			uid: uid,
		},
	}).done(function (response) {
		console.log(response);
		change_nextcloud(uid, "moved");
	});
}

function int_flatpickr() {
	$("#tbody_active>tbody .timechange").each(function (i, obj) {
		$(this).flatpickr({
			enableTime: true,
			noCalendar: true,
			dateFormat: "H:i",
			time_24hr: true,
			minTime: "07:00",
			maxTime: "20:00",
			minuteIncrement: 15,
		});
	});
	$("#tbody_active>tbody .datechange").each(function (i, obj) {
		$(this).flatpickr({
			enableTime: false,
			dateFormat: "d.m.y",
			//altInput: true,
			//altFormat: "F j, Y",
			minDate: "today",
			locale: {
				firstDayOfWeek: 1,
			},
			disable: [
				function (date) {
					// return true to disable
					return date.getDay() === 0 || date.getDay() === 7;
				},
			],
		});
	});
}

function sendmail(uid) {
	$.ajax({
		method: "POST",
		url: "view/load/mail_load.php",
		data: {
			func: "send_mail",
			uid: uid,
		},
	}).done(function (response) {
		console.log("mail" + response);
	});
}

function changestate() {
	console.log("uidaaa:" + uid);
	$.ajax({
		method: "POST",
		url: "view/load/activation_load.php",
		data: {
			func: "change_activated",
			state: "1",
			uid: uid,
		},
	}).done(function (response) {
		console.log(response);
		init_tables();
	});
}

function init_tables() {

	if (currentUrl.includes("activation")) {
		$.ajax({
			method: "POST",
			url: "view/load/activation_load.php",
			data: {
				func: "load_table",
				state: "0",
			},
		}).done(function (response) {
			console.log(response);
			$("#tbody_active tbody").html(response);
		});
		$.ajax({
			method: "POST",
			url: "view/load/activation_load.php",
			data: {
				func: "load_table",
				state: "1",
			},
		}).done(function (response) {
			console.log(response);
			$("#tbody_done tbody").html(response);

		});
	}
	if (currentUrl.includes("appointments")) {
		$.ajax({
			method: "POST",
			url: "view/load/activation_load.php",
			data: {
				func: "load_table_appoint",
			},
		}).done(function (response) {
			//console.log(response);
			$("#tbody_active tbody").html(response);
			int_flatpickr();
		});
	}
}

function change_nextcloud(uid, reason) {
	$.ajax({
		method: "POST",
		url: "view/load/phoner_load.php",
		data: {
			func: "nextcloud_change",
			uid: uid,
			reason: reason,
		},
	}).done(function (response) {
		console.log('nextcloud_change')
		console.log(response);
	});
}

function myclip(input) {
	navigator.clipboard.writeText(input).then(
		function () {
			//alert("Copied to clipboard successfully!");
		},
		function (error) {
			//alert("ERROR:\n" + error);
		}
	);
}

async function CheckPermission() {
	const readPerm = await navigator.permissions.query({
		name: "clipboard-read",
		allowWithoutGesture: false,
	});

	const writePerm = await navigator.permissions.query({
		name: "clipboard-write",
		allowWithoutGesture: false,
	});

	// Will be 'granted', 'denied' or 'prompt':
	alert("Read: " + readPerm.state + "\nWrite: " + writePerm.state);
}
