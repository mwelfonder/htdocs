activationtracker();
var term = "";
var timeout = false;
$(document).ready(function () {
	$("#navbar-search-input").on("input", function () {
		short_code = false;
		term = $("#navbar-search-input").val();
		//////////////////////////////////////
		//		 Ticket shortcode
		/////////////////////////////////////

		//////////////////////////////////////
		if (term.length >= 3 && short_code == false) {
			term = $.trim(term); // remove leading and trailing space
			$.ajax({
				method: "POST",
				url: "view/load/search_load_hbg.php",
				data: {
					func: "search",
					term: term,
				},
			}).done(function (response) {
				//console.log(response)
				$("#navbar_result").html(response);
				$("#navbar_results_wrapper").removeClass("hidden");
				$(".content").addClass("overlay");
			});
		}
	});
	//////////////////////////////////////
	// ========================
	// keyboard navigation for search results
	let currentIndex = -1; // Index to keep track of the currently selected search result

	$(document).on('keydown', function (e) {
		let results = $('.phonersearchresult_s'); // Get all search results

		if (e.key === "ArrowDown" && currentIndex < results.length - 1) {
			currentIndex++;
			highlightResult();
		} else if (e.key === "ArrowUp" && currentIndex > 0) {
			currentIndex--;
			highlightResult();
		} else if (e.key === "Enter" && currentIndex !== -1) {  // Check for the Enter key
			let selectedResult = results.eq(currentIndex)[0].outerHTML; // Get the outerHTML of the currently selected div
			opencus(selectedResult);
		}
	});


	function highlightResult() {
		$('.phonersearchresult_s').removeClass('selected'); // Remove the 'selected' class from all search results

		let currentResult = $('.phonersearchresult_s').eq(currentIndex); // Get the current search result based on the currentIndex
		currentResult.addClass('selected'); // Add the 'selected' class to the current search result

		// Scroll into view (if your results are in a scrollable container)
		currentResult[0].scrollIntoView({ behavior: "smooth", block: "nearest" });
	}

	
	//////////////////////////////////////
	// ========================
	// Searchbar clickevent for reports
	$("#navbar_results_wrapper").on("click", ".reportitem", function () {
		console.log("report clicked");
		console.log($(this).attr("id"));
		let id = $(this).attr("id");
		$.ajax({
			method: "POST",
			url: "view/includes/functions.php",
			data: {
				func: "excel_report",
				city: id,
			},
		}).done(function (response) {
			console.log(response)

		});
	});
	// ========================
	// Searchbar clickevent
	// =======================
	//		Followup row select
	$("#followup_table_today_body").on("click", ".followup-row", function () {
		$("#followup_table_today_body tr").each(function (i, obj) {
			$(this).removeClass("selected");
		});
		$(this).toggleClass("selected");
	});
	$("#followup_table_today_body").on(
		"click",
		".followup-status>i",
		function () {
			let checked = false;
			console.log($(this).attr("class"));
			if ($(this).hasClass("ri-checkbox-blank-line")) {
				$(this).removeClass();
				$(this).addClass("ri-checkbox-line");
				checked = true;
			} else {
				$(this).removeClass();
				$(this).addClass("ri-checkbox-blank-line");
				checked = false;
			}
			let homeid = $(this).closest("tr").attr("id");
			homeid = homeid.substring(9);
			let time = $(this).closest("tr").children(".followup-time").text();
			console.log(homeid);
			console.log(time);
			if (checked === true) {
				$.ajax({
					method: "POST",
					url: "view/load/phoner_load.php",
					data: {
						func: "mark_followup",
						homeid: homeid,
						date: "date",
						time: time,
						status: "done",
					},
				}).done(function (response) {
					console.log(response);
				});
			}
		}
	);

	// ===============================
	// Alarmbox click sleep 3min
	$(".appoverlay-alarmbox-sleep").click(function () {
		timeout = true;
		$("#appoverlay_alarmbox-wrapper").addClass("colapsed");
		setTimeout(function () {
			timeout = false;
		}, 180 * 1000);
	});
	// =======================
	//		Toggle Profile Menu
	$("#nav_profile").click(function () {
		$("#overlay_profile").toggleClass("colapsed");
	});
	// =======================
	//		Toggle weekplan Menu
	$("#nav_weekplan").click(function () {
		$("#appoverlay_weekplanwrapper").toggleClass("colapsed");
		$(".content-wrapper").toggleClass("bgcolor");

		setTimeout(function () {
			var contentWrapperWidth = $(".content-wrapper").outerWidth();
			$(".content-wrapper.bgcolor:before").css("width", contentWrapperWidth);
		}, 500);

		$("#appoverlay_weekplanwrapper").blur(function () {
			$(this).removeClass("colapsed");
			$(".content-wrapper").removeClass("bgcolor");
		});
	});
});

// ==========================
// 		Followup wrapper lose focus to collapse
$(document).mouseup(function (e) {
	var container1 = $("#appoverlay_followup_wrapper");
	var container2 = $("#nav_notification");
	// if the target of the click isn't the container nor a descendant of the container
	if (
		!container1.is(e.target) &&
		container1.has(e.target).length === 0 &&
		!container2.is(e.target) &&
		container2.has(e.target).length === 0
	) {
		//container.hide();
		$(".appoverlay-followup-wrapper").addClass("colapsed");
		$("#nav_notification").removeClass("active");
	}
});

$(document).click(function (event) {
	var isclicked = event.target.id;
	var isclickedclass = event.target.className;

	/// ================
	// hide searchbar results on focus loose
	if (
		!(
			isclickedclass === "navbar-content" ||
			isclickedclass === "navbar-search-result-wrapper"
		)
	) {
		$("#navbar_results_wrapper").addClass("hidden");
		$(".content").removeClass("overlay");
	}
	// show searchbar results on focus
	if (
		isclicked === "navbar-search-input" &&
		$("#navbar-search-input").val().length >= 3
	) {
		$("#navbar_results_wrapper").removeClass("hidden");
		$(".content").addClass("overlay");
	}
});


/// ===========================================
///				Followup Wrapper
function app_phoner_load_followup(date) {
	$.ajax({
		method: "POST",
		url: "view/load/phoner_load.php",
		data: {
			func: "load_followup",
			date: date,
		},
	}).done(function (response) {
		//console.log(response);
		//let parse = JSON.parse(response);
		//console.log(parse);
		if (response === "string:empty") {
			$("#followup_table_today").addClass("hidden");
			$("#followup_today_message").removeClass("hidden");
		} else {
			$("#followup_table_today").DataTable().clear().destroy();
			$("#followup_table_today").removeClass("hidden");
			$("#followup_today_message").addClass("hidden");
			$("#followup_table_today_body").html();
			$("#followup_table_today_body").html(response);
			$("#followup_table_today").DataTable({
				ordering: true,
				select: true,
				paging: false,
				lengthChange: false,
				searching: false,
				info: false,
			});
			var rowCount = $("#followup_table_today_body tr").length;
			$("#nav_notification_count").html(rowCount);
			$("#nav_notification_count").removeClass("hidden");
		}
		//alert(_nowtime());
	});
}

function _nowtime() {
	var d = new Date(); // for now
	let h = d.getHours(); // => 9
	let m = d.getMinutes(); // =>  30
	let time = h + "" + m;
	return time;
}

// ==========================
// Check Followup event every 60 sec
setInterval(function () {
	$("#followup_table_today_body tr").each(function (i, obj) {
		//console.log($(this).find('.followup-time').text())
		var nowtime = _nowtime();
		var follow = $(this).find(".followup-time").text().replace(":", "");
		let active = false;

		console.log($(".followup-status>i").attr("class"));
		if ($(".followup-status>i").hasClass("ri-checkbox-line")) {
			activ = true;
		} else {
			activ = false;
		}
		console.log("followstate" + activ);
		console.log("follow" + follow);
		console.log("nowtime" + nowtime);
		var homeid = $(this).find(".list-entry-hrefhomeid").text();
		var comment = $(this).find(".followup-comment").text();
		var name = $(this).find(".followup-name").text();
		if (nowtime > follow && timeout === false && active === true) {
			$(".appoverlay-alarmbox-name").html(name);
			$(".appoverlay-alarmbox-comment").html(comment);
			$(".appoverlay-alarmbox-comment").html(
				"Rückruf: <b>" + $(this).find(".followup-time").text() + " Uhr </b>"
			);

			$(".appoverlay-alarmbox-footer").html(
				'<a target="_blank" href="route.php?view=phonerapp?homeid=' +
				homeid +
				'">' +
				homeid +
				"</a>"
			);
			$("#appoverlay_alarmbox-wrapper").removeClass("colapsed");
			if ($("#appoverlay_alarmbox-wrapper").hasClass("shake")) {
				//$('#appoverlay_alarmbox-wrapper').removeClass('shake');
				//console.log('remove shake');
			} else {
				$("#appoverlay_alarmbox-wrapper").addClass("shake");
				//console.log('add shake');
			}
		}
	});
}, 60 * 1000); // 60 * 1000 milsec



function activationtracker() {
	// ajax call to functions.php
	$.ajax({
		method: "POST",
		url: "view/load/activation_load.php",
		data: {
			func: "load_activationtracker",
		},
	}).done(function (response) {
		console.log('activation:' + response)
		$('#activationtracker').html(response);
	});


}



function opencus(html) {
    let selectedElement = $(html);

    let url = window.location.href;
    let city = url.slice(url.indexOf("city=") + 5, url.lastIndexOf("?"));
    if (city.includes("route.php")) city = ""; 

    let a_split = url.split("view=");
    let baseurl = a_split[0];
    var homeid = selectedElement.attr("id");
    let str = selectedElement.html();
    console.log('thenewstring is ::' + str);

    if (a_split[1] && a_split[1].startsWith('map')) {
        history.pushState({}, "", baseurl + 'view=map?homeid=' + homeid + "&ref=search");
        infoPlateIconClick();
        infoBoardLoadData(homeid);
    } else if (short_code != true) {
        $.confirm({
            closeIcon: true,
            title: "Kunden öffnen",
            content: "Möchtest du den Kunden\n" + str + "jetzt öffnen?",
            type: "blue",
            buttons: {
                öffnen: {
                    text: "öffnen",
                    btnClass: "btn-primary blue",
                    keys: ["enter"],
                    action: function () {
                        window.location.href = baseurl + 'view=map?homeid=' + homeid + "&ref=search";
                    },
                },
                nein: {
                    text: "nein",
                    keys: ["esc"],
                },
            },
        });
    } else { // For the condition when short_code is true
        window.location.href = baseurl + "view=tickgeter?homeid=" + homeid;
    }
}





// ---------------------------------------------------------------------------------
// w8 for the markers in map.js to be created
$(document).on('markersCreated', function () {
	const markers = window.markers;
	console.log('markers are created')
	console.log(markers);
});




