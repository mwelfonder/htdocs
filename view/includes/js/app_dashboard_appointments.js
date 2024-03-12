

	// your code that depends on general.js goes here
	track_clickevent('opend page','dashboard_appointment','');





$.ajax({
	method: "POST",
	url: "view/load/activation_load.php",
	data: {
		func: "load_appointments",
	},
}).done(function (response) {
	//console.log(response);
	$('#tbody_active tbody').html(response);
	var rowCount = $('#tbody_active tbody tr:visible').length;
	$('#count_all').text(rowCount);
	$('#tbody_active').DataTable({
		scrollX: true,
		dom: "lBfrtip",
		"bLengthChange": false,
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
		paging: false,
	});
});


$(document).ready(function () {

	////=============================
	/// Filter switch UGG DGF GVG
	$("#btn_filter_ugg, #btn_filter_dgf, #btn_filter_gvg").click(function () {
		id = $(this).attr("id");
		let count = 0;
		if (id === "btn_filter_ugg") {
			var carrier = "UGG";
		} else if (id === "btn_filter_dgf") {
			var carrier = "DGF";
		} else if (id === "btn_filter_gvg") {
			var carrier = "GVG";
		}
		if ($(this).children().children().attr('class') === 'ri-checkbox-fill') {
			// remove checked
			$(this).children().children().attr('class', 'ri-checkbox-blank-line');
			$('#tbody_active>tbody tr').each(function (i, obj) {
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
			$('#tbody_active>tbody tr').each(function (i, obj) {
				if ($(this).hasClass(carrier)) {
					$(this).removeClass("hidden");
				}
				if (!($(this).hasClass('hidden'))) {
					count++;
				}
			});
		}
		var rowCount = $('#tbody_active tbody tr:visible').length;
		$('#count_all').text(rowCount);
	});
	/// Filter switch Insyte Moncobra
	$("#btn_filter_moncobra, #btn_filter_insyte").click(function () {
		let count = 0;
		let clientcount = 0;
		id = $(this).attr("id");
		if (id === "btn_filter_moncobra") {
			var client = "Moncobra";
		} else if (id === "btn_filter_insyte") {
			var client = "Insyte";
		}
		if ($(this).children().children().attr('class') === 'ri-checkbox-fill') {
			// remove checked
			$(this).children().children().attr('class', 'ri-checkbox-blank-line');
			$('#tbody_active>tbody tr').each(function (i, obj) {
				if ($(this).hasClass(client)) {
					$(this).addClass("hidden");
				}
			});
		} else {
			// set checked
			$(this).children().children().attr('class', 'ri-checkbox-fill');
			$('#tbody_active>tbody tr').each(function (i, obj) {
				count++;
				if ($(this).hasClass(client)) {
					$(this).removeClass("hidden");
				}
			});
		}
		var rowCount = $('#tbody_active tbody tr:visible').length;
		$('#count_all').text(rowCount);
	});



});





function getExportFileName() {

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
	exportname = 'Scan4_export_appointments_' + today;


	// call the track_clickevent
	track_clickevent('export','dashboard_appointment',exportname);
	


	return exportname;
}

function chart_totals(vtotal, vopen, vstopped, vplanned, vdone, voverdue) {
	var options = {
		series: [vopen, vdone, vplanned, vstopped, voverdue],
		chart: {
			width: 380,
			type: 'donut',
		},
		//fill: { colors: ['red', 'green', 'yellow'] },
		labels: ['Open', 'Done', 'Planned', 'Stopped', 'Overdue'],
		responsive: [{
			breakpoint: 480,
			options: {
				chart: {
					width: 200
				},
				legend: {
					position: 'bottom'
				}
			}
		}],
		plotOptions: {
			pie: {
				donut: {
					labels: {
						show: true,
						total: {
							color: '#000',
							show: true,
							label: 'Total',
							formatter: () => vtotal
						}
					}
				}
			}
		}
	};

	var chart = new ApexCharts(document.querySelector("#donut_totals"), options);
	chart.render();
}
