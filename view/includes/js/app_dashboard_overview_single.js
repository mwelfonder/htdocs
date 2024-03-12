


var loader = '<div id="loaderwrapper2" class="fullwidth aligncenter"> <div class="appt-loader loader"></div> </div>';


var tab_content_overview = '';
var tab_content_statistics = '';
var tab_content_files = '';
var tab_content_tickets = '';
var tab_content_activity = '';



//var city = 'Heusenstamm';
var url = window.location.href;
var split = url.split('view=');
if (split[1].includes("project=")) {
	var s = split[1].split('project=');
	city = s[1];
}
city = decodeURI(city)


window.onload = function () {
	heatmap_init('dashboard_project_single_'+city); 
};
//city = parseurl_reverse(city);
load_tab('overview');

$(document).ready(function () {

	$('#projectoverview_projectname').html('Project: ' + city);
	//window.location.href = 'route.php?view=projectdetails&project='+city;
	$('.project_tab').click(function () {
		let id = $(this).attr('id');
		// split it to get the tabname _
		let split = id.split('_');
		let tabname = split[2];

		$('.project_tab').each(function (i, obj) {
			$(this).removeClass('activetab');
		});
		$(this).addClass('activetab');
		$('.projectcontent').each(function (i, obj) {
			$(this).addClass('hidden');
		});
		if (tabname === 'overview') {
			$('#content_wrapper_' + tabname).removeClass('hidden');
			if (tab_content_overview !== '') {
				$('#content_wrapper_' + tabname).html(tab_content_overview);
			} else {
				load_tab(tabname);
			}
		}
		if (tabname === 'statistics') {
			$('#content_wrapper_' + tabname).removeClass('hidden');
			if (tab_content_statistics !== '') {
				$('#content_wrapper_' + tabname).html(tab_content_statistics);
			} else {
				load_tab(tabname);
			}
		}
		if (tabname === 'files') {
			$('#content_wrapper_' + tabname).removeClass('hidden');
			if (tab_content_activity !== '') {
			//	console.log(tabname + ' is not empty')
				$('#content_wrapper_' + tabname).html(tab_content_files);
			} else {
				load_tab(tabname);
			//	console.log(tabname + ' is empty')
			}
		}
		if (tabname === 'tickets') {
			$('#content_wrapper_' + tabname).removeClass('hidden');
			if (tab_content_tickets !== '') {
				$('#content_wrapper_' + tabname).html(tab_content_tickets);
				init_tickettabe();
			} else {
				load_tab(tabname);
			}
		}
		if (tabname === 'activity') {
			$('#content_wrapper_' + tabname).removeClass('hidden');
			if (tab_content_activity !== '') {
			//	console.log(tabname + ' is not empty')
				$('#content_wrapper_' + tabname).html(tab_content_activity);
			} else {
				load_tab(tabname);
			//	console.log(tabname + ' is empty')
			}
		}
		// track click event general.js 
		track_clickevent('load_tab ' + tabname, 'dashboard_single_' + city, '');
		console.log('load_tab ' + tabname + ' dashboard_single_' + city)


	});


});



function load_tab(tabname) {
	$('#content_wrapper_' + tabname).html(loader);
	$.ajax({
		method: "POST",
		url: "view/load/dashboard_overview_single_load.php",
		data: {
			func: "load_tab_" + tabname,
			city: city,
		},
	}).done(function (response) {
		//console.log(response)
		$('#content_wrapper_' + tabname).html(response);
		if (tabname === 'overview') tab_content_overview = response;
		if (tabname === 'statistics') tab_content_statistics = response;
		if (tabname === 'files') tab_content_files = response;
		if (tabname === 'activity') tab_content_activity = response;

		if (tabname === 'tickets') {
			tab_content_tickets = response;
			init_tickettabe();
		}
		//console.log('#content_wrapper_' + tabname)
		let displayheight = parseInt($(window).height());
		let contenheight = parseInt($('#body-content').height()) + 100;

		if (contenheight > displayheight) {
			$('.body_content').height(contenheight);
		//	console.log('set height to:' + contenheight)
		}


	//	console.log('display:' + displayheight)
		//console.log('content:' + contenheight)

	});

}



function init_tickettabe() {
	$('#projecttable_tickets').DataTable({
		ordering: true,
		select: true,
	});
	var table = $('#projecttable_tickets').DataTable();
	table
		.column('0:visible')
		.order('desc')
		.draw();
	$($.fn.dataTable.tables(true)).DataTable().columns.adjust();
}


function parseurl_reverse(string) {
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


