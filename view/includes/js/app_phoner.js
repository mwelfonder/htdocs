window.onload = function () {
	heatmap_init('phoner');
};



app_phoner_load_citycards();
$(document).ready(function () {


	//console.log("done");
	//app_phoner_load_citystats('Insyte');

	console.log('init')

	$(document).on("click", '.phonercards', function (e) {

		console.log('click')
		city = $(this).attr('id');
		count = $(this).children().find('.toopen').text();
		if (count !== "0") {
			$.confirm({
				title: 'Projekt öffnen',
				content: 'Sicher das du <b>' + city + " </b>öffnen möchtest?",
				buttons: {
					öffnen: function () {
						window.location.href = "route.php?view=phonerapp?city=" + city + '?homeid=';
					},
					cancel: function () {
						//$.alert('Canceled!');
					}
				}
			});
		} else {

		}
	})
	//app_phoner_load_citystats('Moncobra');



	/// Sidebar swithc Insyte / Moncobra
	$("#phoner_sidebar_insyte, #phoner_sidebar_moncobra, #phoner_sidebar_fol").click(function () {
		$(this).addClass("active").siblings().removeClass("active");
		var id = $(this).attr("id");

		if (id === "phoner_sidebar_insyte") {
			$('#app-phoner-city-wrapper-moncobra').addClass("hidden");
			$('#app-phoner-city-wrapper-insyte').removeClass("hidden");
			$('#app-phoner-city-wrapper-fol').addClass("hidden");
			$('#phoner_sidebar_moncobra, #phoner_sidebar_fol').children().removeClass("active");
		} else if (id === "phoner_sidebar_moncobra") {
			$('#app-phoner-city-wrapper-moncobra').removeClass("hidden");
			$('#app-phoner-city-wrapper-insyte').addClass("hidden");
			$('#app-phoner-city-wrapper-fol').addClass("hidden");
			$('#phoner_sidebar_insyte, #phoner_sidebar_fol').children().removeClass("active");
		} else if (id === "phoner_sidebar_fol") {
			$('#app-phoner-city-wrapper-moncobra').addClass("hidden");
			$('#app-phoner-city-wrapper-insyte').addClass("hidden");
			$('#app-phoner-city-wrapper-fol').removeClass("hidden");
			$('#phoner_sidebar_insyte, #phoner_sidebar_moncobra').children().removeClass("active");
		}
	});
	/// Filter switch UGG DGF GVG
	$(".btn-filter").click(function () {
		var $this = $(this);
		var carrier = $this.data("carrier");

		if ($this.children().children().hasClass("ri-checkbox-fill")) {
			$this.children().children().removeClass("ri-checkbox-fill").addClass("ri-checkbox-blank-line");
			$('.phonercards.' + carrier).addClass("hidden");
		} else {
			$this.children().children().removeClass("ri-checkbox-blank-line").addClass("ri-checkbox-fill");
			$('.phonercards.' + carrier).removeClass("hidden");
		}
	});
	// ===============================
	// 		Search Input and Filter
	$('#phoner_search_input').on('input', function () {
		var term = $(this).val();
		if (term.length >= 2) {
			$('#app-phoner-city-wrapper-insyte,#app-phoner-city-wrapper-moncobra').children().each(function (i, obj) {

				city = $(this).attr('id');
				city = city.toLowerCase();
				term = term.toLowerCase();
				console.log(this)
				console.log(city)
				$(this).removeClass('searchhidden')
				if (!(city.includes(term))) {
					$(this).addClass('searchhidden')
				}
			});
		} else {
			$('#app-phoner-city-wrapper-insyte,#app-phoner-city-wrapper-moncobra').children().each(function (i, obj) {
				$(this).removeClass('searchhidden')
			});
		}
	});


});

$(document).click(function (event) {
	var isclicked = (event.target.id);
	var isclickedclass = (event.target.className);
	console.log(isclickedclass);
	console.log(isclicked);

	/// ================
	// hide searchbar results 

});

function app_phoner_load_mask(city) {
	$.confirm({
		title: 'Projekt ' + city,
		content: 'Sicher das du <b>' + city + " </b>öffnen möchtest?",
		buttons: {
			öffnen: function () {
				$("#infobox_projekt").html(city);
				$("#cityselection").addClass("hidden");
				$("#phonmask").removeClass("hidden");
				$("#phonernextbtn").addClass("disabled");
				loadnextentry();
			},
			cancel: function () {
				//$.alert('Canceled!');
			}
		}
	});
}
async function app_phoner_load_citycards() {

	function loadCityCards(client) {
		return new Promise(function (resolve, reject) {
			$.ajax({
				method: "POST",
				url: "view/load/phoner_load.php",
				data: {
					func: "load_citycards",
					client: client,
				},
				success: function (response) {
					resolve({client, response});
				},
				error: function (xhr, status, error) {
					reject({client, error});
				}
			});
		});
	}

	let clients = ['Insyte', 'Moncobra', 'FOL'];

	for (let client of clients) {
		loadCityCards(client)
			.then(({client, response}) => {
				$(`#app-phoner-city-wrapper-${client.toLowerCase()}`).html(response);
				$(`#phoner_projects_sum_${client.toLowerCase()}`).html($(`#${client}cardcount`).text());
				$(`#loaderwrapper_${client.toLowerCase()}`).hide();
				$('#loaderwrapper').hide();
			})
			.catch(({client, error}) => {
				console.log(`Error loading ${client} city cards: ${error}`);
				$('#loaderwrapper').hide();
			});
	}
}





function app_phoner_load_citystats(client) {

	$.ajax({
		method: "POST",
		url: "view/load/phoner_load.php",
		data: {
			func: "load_citystats",
			client: client,
		},
	}).done(function (response) {
		//console.log(response)
		placeholder = '<li class="key-listitem s-prio opacityzero"> <span class="key-entry-icon prio"><i class="ri-star-line"></i></span> <span class="key-entry-text"><span id="keyentryprio" class="key-entry-subtext prio blured">0</span></span> <span class="key-entry-int"></span> </li>';
		array_citys = JSON.parse(response);
		console.log(array_citys);
		client = client.toLowerCase();
		//city = split[0].replace(/ /g, "__");
		//console.log("RESPONESE city:" + split[0]+" open:" + split[1]);
		//$("#keyentryopen" + split[0]).text(split[1]);
		$('#app-phoner-city-wrapper-' + client).children('.phonercards').each(function (i, obj) {
			var city = $(this).attr('id');
			//console.log(i + ' ' + city);
			$(this).find('.key-entry-subtext.open').html(array_citys[i].open).removeClass('blured');
			$(this).find('.key-entry-subtext.opend').html(array_citys[i].locked).removeClass('blured');
			$(this).find('.key-entry-subtext.toopen').html(array_citys[i].toopen).removeClass('blured');
			if (array_citys[i].toopen === 0) {
				$(this).addClass('closed');
				//console.log('itsempty')
			}
			if (array_citys[i].prio !== '0') {
				$(this).find('.key-entry-subtext.prio').html(array_citys[i].prio).removeClass('blured');
				$(this).find('.key-listitem.s-prio').removeClass('hidden');
			} else {
				$(this).find('.key-listitem.s-prio').html(placeholder).removeClass('hidden')
			}
			//console.log(array_citys[i].city);
		});
	});


}


