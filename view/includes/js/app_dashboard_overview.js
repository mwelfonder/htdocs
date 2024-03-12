window.onload = function () {
	heatmap_init('dashboard_project_overview');
};




var city = '';
var cellstate = '';
$(document).ready(function () {



	// call the track_clickevent
	track_clickevent('opend page', 'dashboard_project_overview', '');


	$('#toverview').DataTable({
		ordering: true,
		select: true,
		"paging": false,
		"lengthChange": false,
		"searching": false,
		"info": false,
	});
	totalrecords()


	/// register click event on not selected row and open new tab with city
	$("#toverview>tbody tr").click(function (e) {
		$('#toverview>tbody tr').each(function (i, obj) {
			$(this).removeClass('selected');
		});
		$(this).addClass('selected');
		if (($(this).hasClass('selected')) && e.target.className === 'city') {
			city = $(this).children().find('.city').text();
			// track click event general.js
			track_clickevent('click_city ' + city, 'dashboard_overview', '');


			// href to city in new tab
			window.open("route.php?view=projectdetails&project=" + city, '_blank');

			/*
						$('.appendrow').each(function (i, obj) {
							$(this).remove();
						});
						$('body').addClass('waiting');
						city = $(this).children().find('.city').text();
						//console.log(city);
			
						var row = $(this);
						let vtotal = parseInt($(this).children().find('.celltotal').text());
						let vopen = parseInt($(this).children().find('.cellopen').text());
						let vclosed = parseInt($(this).children().find('.cellclosed').text());
						let vplanned = parseInt($(this).children().find('.cellplanned').text());
						let vdone = parseInt($(this).children().find('.celldone').text());
						let vstopped = parseInt($(this).children().find('.cellstopped').text());
						let voverdue = parseInt($(this).children().find('.celloverdue').text());
			
						$.ajax({
							method: "POST",
							url: "view/load/dashboard_overview_load.php",
							data: {
								func: "load_overview_city",
								city: city,
							},
						}).done(function (response) {
							//$('#tickets_overview_body').html(response);
							$('.appendrow').each(function (i, obj) {
								$(this).remove();
							});
							$(row).after(response);
							chart_totals(vtotal, vopen, vstopped, vplanned, vdone, voverdue);
							chart_calls(vtotal)
							$('body').removeClass('waiting');
			
						});
					*/

		}

	});
	/// register clicks on td stats -> load into loadtable
	$('#toverview>tbody tr, #overviewtotals>tbody tr').on('click', '.cellstats', function (event) {
		$('#loaderwrapper2').removeClass('hidden');

		$('#loadtablewrapper').addClass('hidden');
		city = $(this).closest('tr').children().find('.city').text();
		console.log('city:' + city)


		var clickedTableId = $(event.target).closest('table').attr('id');
		//console.log('Clicked on table with ID:', clickedTableId);
		if (clickedTableId === 'overviewtotals') {
			var citys = []; // create empty array
			$('#toverview>tbody tr:visible').each(function (i, obj) {
				let tmp = $(this).closest('tr').children().find('.city').text();
				citys.push(tmp); // add city to array
			});
			city = citys;
			console.log(city); // output array to console
		}

		var load = false;
		if ($(this).hasClass('celltotal')) {
			load = true;
			stat = 'total'
		}
		if ($(this).hasClass('cellopen')) {
			load = true;
			stat = 'open'
		}
		if ($(this).hasClass('cellsysopen')) {
			load = true;
			stat = 'sysopen'
		}
		if ($(this).hasClass('cellsysstopped')) {
			load = true;
			stat = 'sysstopped'
		}
		if ($(this).hasClass('cellclosed')) {
			load = true;
			stat = 'closed'
		}
		if ($(this).hasClass('celloverdue')) {
			load = true;
			stat = 'overdue'
		}
		if ($(this).hasClass('cellplanned')) {
			load = true;
			stat = 'planned'
		}
		if ($(this).hasClass('celldone')) {
			load = true;
			stat = 'done'
		}
		if ($(this).hasClass('cellsysdone')) {
			load = true;
			stat = 'sysdone'
		}
		if ($(this).hasClass('cellclouddone')) {
			load = true;
			stat = 'clouddone'
		}
		if ($(this).hasClass('cellstopped')) {
			load = true;
			stat = 'stopped'
		}
		if ($(this).hasClass('cell5calls')) {
			load = true;
			stat = '5calls'
		}
		if ($(this).hasClass('cellbox')) {
			load = true;
			stat = 'box'
		}
		if ($(this).hasClass('cellfailed')) {
			load = true;
			stat = 'failed'
		}
		if ($(this).hasClass('cellnophone')) {
			load = true;
			stat = 'nophone'
		}
		if ($(this).hasClass('cellpending')) {
			load = true;
			stat = 'pending'
		}
		console.log(stat)
		cellstate = stat;

		$.ajax({
			method: "POST",
			url: "view/load/dashboard_overview_load.php",
			data: {
				func: "load_overview_stats",
				city: city,
				stat: stat,

			},
		}).done(function (response) {

			//console.log(response);
			$('#loadtablewrapper').html(response);
			$('#loadtable').DataTable({
				scrollX: true,
				dom: "lBfrtip",
				//buttons: ["copyHtml5", "excelHtml5", "csvHtml5"],
				buttons: [
					{
						extend: 'copyHtml5',
						title: function () { return getExportFileName(); },
						filename: function () { return getExportFileName(); }
					},
					{
						extend: 'excelHtml5',
						title: function () { return getExportFileName(); },
						filename: function () { return getExportFileName(); }
					},
					{
						extend: 'csvHtml5',
						title: function () { return getExportFileName(); },
						filename: function () { return getExportFileName(); }
					},
				],
				autoWidth: true,
				columnDefs: [{ width: "60%", targets: 0 }],
			});
			$('#loadtablewrapper').removeClass('hidden');
			$('#loadtable').DataTable().columns.adjust();
			$("html, body").animate({
				scrollTop: $(
					'html, body').get(0).scrollHeight
			}, 500);
			// jquery get the height of #body-content-app
			var height = $('.dashboard-wrapper').height();
			height = height + 200;
			$('.body_content').css('height', height + 'px');
			$('#loaderwrapper2').addClass('hidden');
		});
		//console.log(city);
		//console.log($(this).closest('tr'))
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
			var carrier = "GlasfaserPlus";
		}

		if ($(this).children().children().attr('class') === 'ri-checkbox-fill') {
			// remove checked
			$(this).children().children().attr('class', 'ri-checkbox-blank-line');
			$('#toverview>tbody tr').each(function (i, obj) {
				if ($(this).hasClass(carrier)) {
					$(this).addClass("hidden");
				}
				if (!($(this).hasClass('hidden'))) {
					count++;
				}
			});
		} else {
			// set checked
			$(this).children().children().attr('class', 'ri-checkbox-fill');
			$('#toverview>tbody tr').each(function (i, obj) {
				if ($(this).hasClass(carrier)) {
					$(this).removeClass("hidden");

				}
				if (!($(this).hasClass('hidden'))) {
					count++;
				}
			});
		}
		count = $('#toverview>tbody tr:visible').length;
		$('.project-countercurrent').html(count);
		totalrecords();
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
		if ($(this).children().children().attr('class') === 'ri-checkbox-fill') {
			// remove checked

			$(this).children().children().attr('class', 'ri-checkbox-blank-line');
			$('#toverview>tbody tr').each(function (i, obj) {
				if ($(this).hasClass(client)) {
					$(this).addClass("hidden");
				}
			});
		} else {
			// set checked
			$(this).children().children().attr('class', 'ri-checkbox-fill');

			$('#toverview>tbody tr').each(function (i, obj) {
				count++;
				if ($(this).hasClass(client)) {
					$(this).removeClass("hidden");

				}
			});
		}
		count = $('#toverview>tbody tr:visible').length;
		$('.project-countercurrent').html(count);
		totalrecords();
	});
	/// Filter switch Aktiv Inaktiv
	$("#btn_filter_inaktiv").click(function () {
		let count = 0;
		let clientcount = 0;
		if ($(this).children().children().attr('class') === 'ri-checkbox-fill') {
			// remove checked
			$(this).children().children().attr('class', 'ri-checkbox-blank-line');
			$('#toverview>tbody tr').each(function (i, obj) {
				if ($(this).hasClass('statusinaktiv')) {
					$(this).addClass("statushidden");
				}
			});
		} else {
			// set checked
			$(this).children().children().attr('class', 'ri-checkbox-fill');
			$('#toverview>tbody tr').each(function (i, obj) {
				if ($(this).hasClass('statusinaktiv')) {
					$(this).removeClass("statushidden");
				}
			});
		}
		count = $('#toverview>tbody tr:visible').length;
		$('.project-countercurrent').html(count);
		totalrecords();
	});
	/// Filter by Search
	$('#statstablesearch').on('input', function () {
		var term = $('#statstablesearch').val();
		//console.log(term)
		if (term.length >= 2) {
			$('#toverview>tbody tr').each(function (i, obj) {
				city = $(this).children().find('.city').text();
				city = city.toLowerCase();
				term = term.toLowerCase();
				$(this).removeClass('searchhidden')
				if (!(city.includes(term))) {
					$(this).addClass('searchhidden')
				}
			});
			totalrecords();
		} else {
			$('#toverview>tbody tr').each(function (i, obj) {
				$(this).removeClass('searchhidden')
			});
			totalrecords();
		}
	});



	count = $('#toverview>tbody tr:visible').length;
	$('.project-countercurrent').html(count);
	totalrecords();





	// resize the table heading system and scan4
	var col1Width = parseInt($('#toverview thead th:nth-child(1)').css('width'));
	var col2Width = $('#toverview thead th:nth-child(2)').width();
	var col3Width = $('#toverview thead th:nth-child(3)').width();
	var col4Width = $('#toverview thead th:nth-child(4)').width();
	var col5Width = $('#toverview thead th:nth-child(5)').width();
	var col6Width = $('#toverview thead th:nth-child(6)').width();
	var totalWidth = col1Width + col2Width;
	console.log('col1 ' + col1Width + ' pixels');

	console.log('Total width of col1 and col2: ' + totalWidth + ' pixels');
	$('#placeholder').css('width', col1Width + col2Width);
	$('#subitem_system').css('width', col3Width + col4Width + col5Width + col6Width);
	$('#subitem_scan4').css('width', col6Width * 7);



});



function totalrecords() {
	var xtotal = 0;
	var xsysopen = 0;
	var xopen = 0;
	var xstopped = 0;
	var xplanned = 0;
	var xsysdone = 0;
	var xdone = 0;
	var xcloud = 0;
	var xoverdue = 0;
	var x5calls = 0;
	var xbox = 0;
	var xmail = 0;
	var xfailed = 0;
	var xnophone = 0;

	$('#toverview>tbody tr:visible').each(function (i, obj) {
		if ($(this).attr('id') !== 'xtotal') {
			xtotal = xtotal + parseInt($(this).children().find('.celltotal').text());
			xsysopen = xsysopen + parseInt($(this).children().find('.cellsysopen').text());
			xopen = xopen + parseInt($(this).children().find('.cellopen').text());
			xstopped = xstopped + parseInt($(this).children().find('.cellstopped').text());
			xplanned = xplanned + parseInt($(this).children().find('.cellplanned').text());
			xsysdone = xsysdone + parseInt($(this).children().find('.cellsysdone').text());
			xdone = xdone + parseInt($(this).children().find('.celldone').text());
			xcloud = xcloud + parseInt($(this).children().find('.cellclouddone').text());
			xoverdue = xoverdue + parseInt($(this).children().find('.celloverdue').text());
			x5calls = x5calls + parseInt($(this).children().find('.cell5calls').text());
			xbox = xbox + parseInt($(this).children().find('.cellbox').text());
			xmail = xmail + parseInt($(this).children().find('.cellmail').text());
			xfailed = xfailed + parseInt($(this).children().find('.cellfailed').text());
			xnophone = xnophone + parseInt($(this).children().find('.cellnophone').text());
		}
	});
	$('#xtotal').children().find('.celltotal').text(xtotal);
	$('#xtotal').children().find('.cellsysopen').text(xsysopen);
	$('#xtotal').children().find('.cellopen').text(xopen);
	$('#xtotal').children().find('.cellstopped').text(xstopped);
	$('#xtotal').children().find('.cellplanned').text(xplanned);
	$('#xtotal').children().find('.cellsysdone').text(xsysdone);
	$('#xtotal').children().find('.celldone').text(xdone);
	$('#xtotal').children().find('.cellclouddone').text(xcloud);
	$('#xtotal').children().find('.celloverdue').text(xoverdue);
	$('#xtotal').children().find('.cell5calls').text(x5calls);
	$('#xtotal').children().find('.cellbox').text(xbox);
	$('#xtotal').children().find('.cellmail').text(xmail);
	$('#xtotal').children().find('.cellfailed').text(xfailed);
	$('#xtotal').children().find('.cellnophone').text(xnophone);




	var val_total = 0;
	var val_sysopen = 0;
	var val_sysdone = 0;
	var val_sysstopped = 0;
	var val_open = 0;
	var val_planned = 0;
	var val_done = 0;
	var val_donecloud = 0;
	var val_stopped = 0;
	var val_overdue = 0;
	var val_pending = 0;

	$('#toverview tbody tr').each(function () {
		if ($(this).is(':visible')) {
			val_total += parseInt($(this).find('td:nth-child(2)').text()) || 0;
			val_sysopen += parseInt($(this).find('td:nth-child(3)').text()) || 0;
			val_sysdone += parseInt($(this).find('td:nth-child(4)').text()) || 0;
			val_sysstopped += parseInt($(this).find('td:nth-child(5)').text()) || 0;
			val_open += parseInt($(this).find('td:nth-child(6)').text()) || 0;
			val_planned += parseInt($(this).find('td:nth-child(7)').text()) || 0;
			val_done += parseInt($(this).find('td:nth-child(8)').text()) || 0;
			val_donecloud += parseInt($(this).find('td:nth-child(9)').text()) || 0;
			val_stopped += parseInt($(this).find('td:nth-child(10)').text()) || 0;
			val_overdue += parseInt($(this).find('td:nth-child(11)').text()) || 0;
			val_pending += parseInt($(this).find('td:nth-child(12)').text()) || 0;
		}
	});
	console.log('Total: ' + val_total);



	$('#overviewtotals td').each(function (index) {
		var thWidth = $('#toverview th:nth-child(' + (index + 1) + ')').width();
		$(this).width(thWidth + 25);
	});


	$('#overviewtotals td:nth-child(2) span').text(val_total);
	$('#overviewtotals td:nth-child(3) span').text(val_sysopen);
	$('#overviewtotals td:nth-child(4) span').text(val_sysdone);
	$('#overviewtotals td:nth-child(5) span').text(val_sysstopped);
	$('#overviewtotals td:nth-child(6) span').text(val_open);
	$('#overviewtotals td:nth-child(7) span').text(val_planned);
	$('#overviewtotals td:nth-child(8) span').text(val_done);
	$('#overviewtotals td:nth-child(9) span').text(val_donecloud);
	$('#overviewtotals td:nth-child(10) span').text(val_stopped);
	$('#overviewtotals td:nth-child(11) span').text(val_overdue);
	$('#overviewtotals td:nth-child(12) span').text(val_pending);


}


function getExportFileName() {
	var cityname = city;
	// check if the city is an array
	if (Array.isArray(city)) {
		// check if the array has more than one element
		if (city.length > 1) {
			cityname = 'overall';
		} else {
			cityname = city;
		}
	}

	console.log('export_city:' + cityname)
	var today = new Date();
	var dd = today.getDate();
	var mm = today.getMonth() + 1;
	var yyyy = today.getFullYear();
	if (dd < 10) {
		dd = '0' + dd;
	}
	if (mm < 10) {
		mm = '0' + mm;
	}
	today = yyyy + '_' + mm + '_' + dd;
	exportname = 'Scan4_export_' + cityname + '_' + cellstate + '_' + today;
	return exportname;
}
