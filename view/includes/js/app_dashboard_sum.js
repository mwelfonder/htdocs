
$(document).ready(function () {




	/*
	
		var options = {
			series: [{
				name: 'Termine',
				type: 'column',
				data: [21, 74, 61, 48, 67]
			}, {
				name: 'Anrufe',
				type: 'line',
				data: [246, 609, 299, 246, 179]
			}],
			chart: {
				toolbar: {
					show: false,
				},
				zoom: {
					enabled: false,
				},
				height: 300,
				type: 'line',
			},
			stroke: {
				width: [0, 2, 5],
				curve: 'smooth'
			},
			dataLabels: {
				enabled: true,
				enabledOnSeries: [1]
			},
			labels: ['Mo', 'Di', 'Mi', 'Do', 'Fr'],
			xaxis: {
				type: 'day'
			},
	
			theme: {
				mode: 'light',
				palette: 'palette10',
				monochrome: {
					enabled: true,
					color: '#a4b1c3',
					shadeTo: 'dark',
					shadeIntensity: 0.65
				},
			},
			legend: {
				show: false,
			},
	
		};
	
		var chart = new ApexCharts(document.querySelector("#chart"), options);
		chart.render();
		

	const m = document.querySelector("#chart"),
		w = {
			series: [{ name: 'Termine', type: 'column', data: [21, 74, 61, 48, 67] }, { name: 'Anrufe', type: 'area', data: [246, 609, 299, 246, 179] }],
			chart: { height: 215, parentHeightOffset: 0, parentWidthOffset: 0, toolbar: { show: !1 }, type: "area" },
			dataLabels: { enabled: !1 },
			stroke: { width: 2, curve: "smooth" },
			legend: { show: !1 },
			dataLabels: { enabled: true, enabledOnSeries: [1] },
			markers: {
				size: 6,
				colors: "transparent",
				strokeColors: "transparent",
				strokeWidth: 4,
				discrete: [{ fillColor: '#696cff', seriesIndex: 0, dataPointIndex: 7, strokeColor: '#000', strokeWidth: 1, size: 6, radius: 8 }],
				hover: { size: 7 },
			},
			colors: ['#2a9ca9'],
			fill: { type: "gradient", gradient: { shade: '#696cff', shadeIntensity: 0.6, opacityFrom: 0.5, opacityTo: 0.25, stops: [0, 95, 100] } },
			grid: { borderColor: '#e3e3e3', strokeDashArray: 3, padding: { top: -20, bottom: -8, left: -10, right: 8 } },
			xaxis: { categories: ["Mo", "Di", "Mi", "Do", "Fr"], axisBorder: { show: !1 }, axisTicks: { show: !1 }, labels: { show: !0, style: { fontSize: "13px", colors: '#000' } } },
			yaxis: { labels: { show: !1 }, tickAmount: 4 },
		};
	new ApexCharts(m, w).render();
*/



	var options = {
		series: [{
			name: 'PRODUCT A',
			data: [44, 55, 41, 67, 22, 43]
		}, {
			name: 'PRODUCT B',
			data: [13, 23, 20, 8, 13, 27]
		}, {
			name: 'PRODUCT C',
			data: [11, 17, 15, 15, 21, 14]
		}, {
			name: 'PRODUCT D',
			data: [21, 7, 25, 13, 22, 8]
		}],
		chart: {
			type: 'bar',
			height: 350,
			stacked: true,
			toolbar: {
				show: true
			},
			zoom: {
				enabled: false
			}
		},
		responsive: [{
			breakpoint: 480,
			options: {
				legend: {
					position: 'bottom',
					offsetX: -10,
					offsetY: 0
				}
			}
		}],
		plotOptions: {
			bar: {
				horizontal: false,
				borderRadius: 0,
				dataLabels: {
					total: {
						enabled: true,
						style: {
							fontSize: '13px',
							fontWeight: 900
						}
					}
				}
			},
		},
		xaxis: {
			type: 'datetime',
			categories: ['01/01/2011 GMT', '01/02/2011 GMT', '01/03/2011 GMT', '01/04/2011 GMT',
				'01/05/2011 GMT', '01/06/2011 GMT'
			],
		},
		legend: {
			position: 'right',
			offsetY: 40
		},
		fill: {
			opacity: 1
		}
	};

	var chart = new ApexCharts(document.querySelector("#chart"), options);
	chart.render();


});