result = "";
comment = "";

filesCount = "";
textbox = "";
fileName = "";
var serverfile = "";
$(document).on("change", ".file-input", function () {
	filesCount = $(this)[0].files.length;

	textbox = $(this).prev();

	if (filesCount === 1) {
		fileName = $(this).val().split("\\").pop();
		textbox.text(fileName);
	} else {
		textbox.text(filesCount + " files selected");
	}
});
function loader(state) {
	if (state === "on") {
		$("#loaderwrapper").removeClass("hidden");
	} else {
		$("#loaderwrapper").addClass("hidden");
	}
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

function filepost(iswrong) {
	loader("on");
	var formData = new FormData();
	let homeid = $("#phonerinfo_homeid").text();
	let unit = $("#phonerinfo_units").text();
	if (unit.length === 0) unit = "1";
	let city = $("#phonerinfo_project").text();
	city = city.replace(/[ ()]/g, ""); // filter spaces and symbols
	cityid = $("#phonerinfo_project_number").text();

	let extension = fileName.slice(-3);
	let nowdate = _nowdate();
	let nowtime = _nowtime();
	if (iswrong !== true) {
		serverfile =
			cityid +
			city +
			"_" +
			nowdate +
			"_" +
			nowtime +
			"_" +
			cityid +
			city +
			"_" +
			homeid +
			"_" +
			unit +
			"" +
			"." +
			extension;
	} else {
		serverfile =
			cityid +
			city +
			"_" +
			nowdate +
			"_" +
			nowtime +
			"_" +
			"wrong_" +
			cityid +
			city +
			"_" +
			homeid +
			"_" +
			unit +
			"" +
			"." +
			extension;
	}
	console.log("filename:" + serverfile);
	formData.append("file", $("#fileupload")[0].files[0], serverfile);
	console.log("FILEDATA");
	console.log(formData);
	console.log("FILEDATA");

	$.ajax({
		url: "view/load/upload.php",
		type: "POST",
		data: formData,
		processData: false, // tell jQuery not to process the data
		contentType: false, // tell jQuery not to set contentType
		success: function (data) {
			console.log(data);
			loader("off");
			return true;
		},
		error: function () {
			loader("off");
			return false;
		},
	});
}

$(document).ready(function () {
	hbgcheck_loadnext();

	console.log("turnoff");
	const fileInput = document.getElementById("fileupload");
	fileInput.onchange = () => {
		const selectedFiles = [...fileInput.files];
		console.log(selectedFiles);
	};

	$("#mailme").click(function () {
		console.log("mailme");
		$.ajax({
			method: "POST",
			url: "view/load/mail_load.php",
			data: {
				func: "send_mail",
			},
		}).done(function (response) {
			console.log("log" + response);
		});
	});

	$("#info_lastname").click(function () {
		myclip($(this).text());

	});
	$("#info_firstname").click(function () {
		myclip($(this).text());
	});
	$("#phonerinfo_street").click(function () {
		myclip($(this).text());
	});
	$("#info_streetnumber").click(function () {
		myclip($(this).text());
	});
	$("#info_streetnumberadd").click(function () {
		myclip($(this).text());
	});
	$("#info_plz").click(function () {
		myclip($(this).text());
	});
	$("#info_city").click(function () {
		myclip($(this).text());
	});

	//======================================
	/// Select switch firstbuttons
	$(".btn-interact-phonerapp.firstselect").click(function () {
		id = $(this).attr("id");
		console.log(id);
		/// hide Select fields
		$(".condition-box").each(function (i, obj) {
			$(this).addClass("hidden");
		});
		//-------------------------
		// reset safe buton
		$("#hbg_safe").addClass("unset");
		$("#hbg_safe").removeClass("isset saved");
		$("#hbg_safe").html(
			' <i class="ri-save-3-line"></i><span> Speichern</span>'
		);
		// -------------------------
		if (id !== "phonerapp_safe") {
			$("#hbg_safe").addClass("unset");
			$("#hbg_safe").removeClass("isset");
			$(".btn-interact-phonerapp").each(function (i, obj) {
				$(this).removeClass("active");
			});
			id = $(this).attr("id");
			$(this).addClass("active");
			$("#interactfield_vorhanden").addClass("hidden");
			$("#interactfield_abbruch").addClass("hidden");

			if (id === "hbg_vorhanden") {
				$("#interactfield_vorhanden").removeClass("hidden");
			} else if (id === "hbg_abbruch") {
				$("#interactfield_abbruch").removeClass("hidden");
			}
			if (id === "hbg_unbegrundet") {
				$("#hbg_safe").addClass("isset");
				$("#hbg_safe").removeClass("unset");
			}
		}
	});
	//======================================
	/// Select switch secondbuttons
	$(".secondselect").click(function () {
		id = $(this).attr("id");
		// reset safe buton
		$("#hbg_safe").addClass("unset");
		$("#hbg_safe").removeClass("isset saved");
		$("#hbg_safe").html(
			' <i class="ri-save-3-line"></i><span> Speichern</span>'
		);
		// -------------------------
		// reset second buttons
		$(".secondselect").each(function (i, obj) {
			$(this).removeClass("active");
		});
		$(this).addClass("active");
		$("#hbg_safe").addClass("isset");
		$("#hbg_safe").removeClass("unset");
		// change select fields
		$("#hbgcheck_possible").addClass("hidden");
		$("#hbgcheck_impossible").addClass("hidden");
		if (id === "abbruch_possible") {
			$("#hbgcheck_possible").removeClass("hidden");
		} else if (id === "abbruch_impossible") {
			$("#hbgcheck_impossible").removeClass("hidden");
		}
	});

	//======================================
	/// safe button
	$("#hbg_safe").click(function () {
		if ($(this).hasClass("isset")) {
			projectid = $("#phonerinfo_project_number").text();
			if (projectid.length === 0) {
				alert("Projekt ID darf nicht leer sein");
			} else {
				safecheck = false;
				isfile = $(".file-message").html();
				console.log(isfile);

				if (
					$("#hbg_vorhanden").hasClass("active") &&
					isfile !== "// DRAG AND DROP"
				) {
					safecheck = true;
					let iswrong = false;
					if ($("#vorhanden_wrong").hasClass("active")) {
						iswrong = true;
					}
					let upload = filepost(iswrong);
				} else if ($("#hbg_unbegrundet").hasClass("active")) {
					safecheck = true;
				} else if (
					$("#abbruch_possible").hasClass("active") &&
					$("#hbgcheck_possible").val() !== null
				) {
					console.log("select" + $("#hbgcheck_possible").val());
					safecheck = true;
				} else if (
					$("#abbruch_impossible").hasClass("active") &&
					$("#hbgcheck_impossible").val() !== null
				) {
					safecheck = true;
				} else if (
					$("#hbg_vorhanden").hasClass("active") &&
					isfile === "// DRAG AND DROP"
				) {
					$.confirm({
						closeIcon: true,
						backgroundDismiss: true,
						title: "Achtung!",
						content: "Keine Datei ausgewählt",
						type: "red",
						buttons: {
							ok: {
								text: "ok",
								btnClass: "",
								keys: ["esc"],
							},
						},
					});
				}

				if (safecheck === true) {
					var result = "";
					var homeid = $("#phonerinfo_homeid").text();
					var comment = $("#check_comment").val();
					$("#hbg_safe").addClass("saved");
					$("#hbg_safe").removeClass("isset");
					$(this).html(
						' <i class="ri-save-3-line"></i><span> Gespeichert!</span>'
					);
					if ($("#vorhanden_excel").hasClass("active")) {
						result = "excel";
					}
					if ($("#vorhanden_screenshot").hasClass("active")) {
						result = "screenshot";
					}
					if ($("#vorhanden_wrong").hasClass("active")) {
						result = "wrong";
					}
					if ($("#abbruch_possible").hasClass("active")) {
						result = $("#hbgcheck_possible").val();
					}
					if ($("#abbruch_impossible").hasClass("active")) {
						result = $("#hbgcheck_impossible").val();
					}
					if ($("#hbg_unbegrundet").hasClass("active")) {
						result = "missing";
					}

					console.log("result" + result);
					$.ajax({
						method: "POST",
						url: "view/load/hbgcheck_load.php",
						data: {
							func: "safe_hbgcheck",
							reason: result,
							homeid: homeid,
							comment: comment,
							file: serverfile,
						},
					}).done(function (response) {
						console.log("log" + response);
						hbgcheck_loadnext();
					});
				}
			}
		}
	});
});

function hbgcheck_set_safebtn(bol) {
	if (bol === false) {
		$("#phonerapp_safe").addClass("unset");
		$("#phonerapp_safe").removeClass("isset");
	} else {
		$("#phonerapp_safe").removeClass("unset");
		$("#phonerapp_safe").addClass("isset");
	}
}

function hbgcheck_loadnext() {
	loader("on");
	$.ajax({
		method: "POST",
		url: "view/load/hbgcheck_load.php",
		data: {
			func: "load_checknext",
		},
	}).done(function (response) {

		console.log('log' + response)
		if (response !== 'null') {
			//console.log(response);
			init_homeid(response);
		} else {
			//console.log('empty')
			$("#hbgcheck_wrapper").addClass("hidden");
			$("#hbgcheck_empty").removeClass("hidden");
		}
		loader("off");
	});
}

function init_homeid(response) {
	$("#body-content-app").addClass("loading");
	let parse = JSON.parse(response);
	console.log(parse);
	var carrier = parse[2];
	var client = parse[1];
	var homeid = parse[10];
	var adressid = parse[11];
	var dpnumber = parse[9];
	var statusnri = parse[19];
	var statussc4 = parse[24];
	var firstname = parse[12];
	var lastname = parse[13];
	var name = parse[13] + ", " + parse[12];
	var streetfull = parse[3] + " " + parse[4] + parse[5];
	var street = parse[3];
	var streetnumber = parse[4];
	var streetnumberadd = parse[5];
	var adress = parse[8] + " " + parse[7];
	var plz = parse[8];
	var city = parse[7];
	var phone1 = parse[14];
	var phone2 = parse[17];
	var unit = parse[6];
	var prio = parse[22];
	var hbgdate = parse[25];
	var anruf1 = parse[27];
	var anruf2 = parse[28];
	var anruf3 = parse[29];
	var anruf4 = parse[30];
	var anruf5 = parse[31];
	var mail = parse[33];
	var brief = parse[32];
	var timeline = parse[42];
	var unitsfrom = parse[43];
	var cityid = parse[50];
	var hbgfile = [];
	hbgfile[0] = parse[37];
	hbgfile[1] = parse[38];
	hbgfile[2] = parse[39];
	hbgfile[3] = parse[40];
	hbgfile[4] = parse[41];
	hbgfile[5] = parse[42];
	hbgfile[6] = parse[43];
	hbgfile[7] = parse[44];

	/// ------------------------
	// reset layout
	$("#hbg_safe").addClass("unset");
	$("#hbg_safe").removeClass("isset saved");
	$("#hbg_safe").html(' <i class="ri-save-3-line"></i><span> Speichern</span>');
	$(".condition-box").each(function (i, obj) {
		$(this).addClass("hidden");
	});
	$("#hbg_safe").addClass("unset");
	$("#hbg_safe").removeClass("isset");
	$(".btn-interact-phonerapp").each(function (i, obj) {
		$(this).removeClass("active");
	});
	$("#interactfield_vorhanden").addClass("hidden");
	$("#interactfield_abbruch").addClass("hidden");
	$("#fileupload").val("");
	$(".file-message").text("// DRAG AND DROP");
	$("#check_comment").val("");

	$("#phonerinfo_project_number").html(cityid);
	$("#phonerinfo_project").html(city);
	$("#phonerinfo_homeid").html(homeid);
	$("#head-adressid").html(adressid);
	$("#phonerinfo_hbgdate").html(hbgdate);
	$("#phonerinfo_statussys").html(statusnri);
	if (statusnri === "OPEN") $("#phonerinfo_statussys").addClass("cspill blue");
	if (statusnri === "PLANNED")
		$("#phonerinfo_statussys").addClass("cspill yellow");
	if (statusnri === "STOPPED")
		$("#phonerinfo_statussys").addClass("cspill red");
	if (statusnri === "OVERDUE")
		$("#phonerinfo_statussys").addClass("cspill lila");
	$("#phonerinfo_statussc4").html(statussc4);
	if (statussc4 === "OPEN") $("#phonerinfo_statussc4").addClass("cspill blue");
	if (statussc4 === "PLANNED")
		$("#phonerinfo_statussc4").addClass("cspill yellow");
	if (statussc4 === "STOPPED")
		$("#phonerinfo_statussc4").addClass("cspill red");
	if (statussc4 === "OVERDUE")
		$("#phonerinfo_statussc4").addClass("cspill lila");

	$("#info_lastname").text(lastname);
	$("#info_firstname").text(firstname);
	$("#info_street").text(street);
	$("#info_streetnumber").text(streetnumber);
	$("#info_streetnumberadd").text(streetnumberadd);
	$("#info_plz").text(plz);
	$("#info_city").text(city);


	// $("#phonerinfo_name").html(name);
	$("#phonerinfo_street").html(streetfull);
	$; //("#phonerinfo_city").html(adress);
	$("#phonerinfo_phone1").html(
		'<a href="tel:' + phone1 + '" class="phoner-callnow">' + phone1 + "</a>"
	);
	if (phone2 != "") {
		$("#phonerinfo_phone2").html(
			'<a href="tel:' + phone2 + '" class="phoner-callnow">' + phone2 + "</a>"
		);
	} else {
		$("#phonerinfo_phone2").html("");
	}
	$("#phonerinfo_units").html(unit);

	$("#phonerinfo_dp").html(dpnumber);
	$("#phonerinfo_client").html(client);
	$("#phonerinfo_carrier").html(carrier);
	if (carrier === "UGG") {
		$("#carrier-logo").removeClass();
		$("#carrier-logo").addClass("carrier-ugg");
	}
	if (carrier === "GVG") {
		$("#carrier-logo").removeClass();
		$("#carrier-logo").addClass("carrier-gvg");
	}
	if (carrier === "DGF") {
		$("#carrier-logo").removeClass();
		$("#carrier-logo").addClass("carrier-dgf");
	}

	//console.log("prio:" + prio);
	if (prio === null) {
		$("#phonerinfo_priorow").addClass("hidden");
		$("#phonerinfo_priocount").html(prio);
	} else {
		$("#phonerinfo_priorow").removeClass("hidden");
		$("#phonerinfo_priocount").html(prio);
	}

	app_phoner_load_timeline(homeid);
}

function app_phoner_load_timeline(homeid) {
	//console.log("loadtimeline:" + homeid);
	$.ajax({
		method: "POST",
		url: "view/load/phoner_load.php",
		data: {
			func: "load_timeline",
			homeid: homeid,
		},
	}).done(function (response) {
		//console.log("response:" + response);
		let a_split = response.split("@@relations@@");
		//console.log(a_split);
		$("#timeline").html(a_split[0]);
		$("#holder-relations").html(a_split[1]);
		if (a_split[2] === "0") {
			$("#relationcounter").addClass("hidden");
		} else {
			$("#relationcounter").html(a_split[2]);
			$("#relationcounter").removeClass("hidden");
		}
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
