$(document).ready(function () {


	const colors = [
		'#ff6000', // papaya
		'#00f1ff', // lightblue
		'#0035ff', // blue
		'#9300ff', // purple
		'#ff00f5', // pink
		'#ff0000', // red
		'#edff00', // yellow

	];

	const randomColor = () => colors[Math.floor(Math.random() * colors.length)];

	// Create a new @keyframes rule with a random color from the array
	let styleSheet = document.styleSheets[0];
	let keyframes = `
        @keyframes neonFlicker {
            0%, 19%, 21%, 23%, 25%, 54%, 56%, 100% {
                text-shadow: 
                    0 0 4px ${randomColor()},
                    0 0 11px ${randomColor()},
                    0 0 19px ${randomColor()},
                    0 0 40px ${randomColor()},
                    0 0 80px ${randomColor()},
                    0 0 90px ${randomColor()};
            }
            20%, 24%, 55% {
                text-shadow: none;
            }
        }
    `;

	// add the new rule to your stylesheet
	//styleSheet.insertRule(keyframes, styleSheet.cssRules.length);


	var consoleAnimID, sloganAnimID;
	consoleAnimation();

	function consoleAnimation() {
		const text = `
		░██████╗░█████╗░░█████╗░███╗░░██╗░░██╗██╗
		██╔════╝██╔══██╗██╔══██╗████╗░██║░██╔╝██║
		╚█████╗░██║░░╚═╝███████║██╔██╗██║██╔╝░██║
		░╚═══██╗██║░░██╗██╔══██║██║╚████║███████║
		██████╔╝╚█████╔╝██║░░██║██║░╚███║╚════██║
		╚═════╝░░╚════╝░╚═╝░░╚═╝╚═╝░░╚══╝░░░░░╚═╝
	`;

		let index = 0;
		const consoleDiv = document.getElementById('console');
		consoleAnimID = setInterval(() => {
			consoleDiv.textContent += text[index];
			index += 1;
			if (index === text.length) {
				clearInterval(consoleAnimID);
				displaySlogan(); // Calling displaySlogan after ASCII art animation completes
			}
		}, 20);
	}

	function displaySlogan() {
		const slogans = [
			"Na komm schon, zeig mir deine Dateien!",
			"Trau dich, lade es hoch – ich kann's kaum erwarten!",
			"Oh ja, gib mir deine (großen) Dateien!",
			"Lade es hoch, ich verspreche, sanft zu sein!",
			"Zeig mir, wie du uploadest!",
			"Was wolle?",
			"Oh mein Gott, du schon wieder!",
			"Komm schon, lass uns ein wenig Upload-Spaß haben!",
			"Sorry - außer Betrieb.",
			"Heute ist leider schon Datenannahme Schluss",
			"Ein kleiner Upload für dich, ein großes Vergnügen für uns beide!",
			"Lade es hier hoch, wo deine Daten immer ein zweites Date bekommen!",
			"Gib's mir – deine Daten, meine ich!",
			"Bitte nicht schon wieder!!",
			"Immer diese Daten... DATEN DATEN DATEN",
			"Hey Bro, Zeit deinen Upload-Game zu zeigen!",
			"Bro, deine Daten sind hier der VIP!",
			"Oh, großartig, noch mehr Daten... Genau das, was ich gebraucht habe.",
			"Daten hier, Daten da, als ob ich nichts Besseres zu tun hätte...",
			"Schon wieder du? Kannst du deine Daten nicht für dich behalten?",
			"Ja klar, schmeiß einfach alle deine Daten hier rein. Es ist nicht so, als hätte ich eine Wahl...",
			"Super, noch mehr Arbeit... Kann die Datenflut hier nie stoppen?",
			"Glaub nicht, dass ich hier herumsitze und auf DEINE Daten warte...",
			"Oh, toll, noch mehr Daten... Als ob ich nicht schon genug hätte!",
			"Kannst du nicht woanders nerven mit deinen Daten?",
			"Großartig, als ob mein Tag nicht schon schlimm genug wäre, jetzt auch noch deine Dateien...",
			"Deine Daten sind genau das, was ich jetzt gebrauchen konnte... nicht!", "Oh, wie originell, noch mehr Dateien. Ich kann es kaum erwarten... nicht.",
			"Dateien hochladen? Gibt es denn keine Pause von dieser Monotonie?",
			"Wirklich? Noch mehr? Das ist genau das, was ich jetzt gebraucht habe... nicht.",
			"Können wir das Hochladen nicht einfach mal ausfallen lassen?",
			"Sicher, wirf einfach alles hier rein, es ist ja nicht so, als hätte ich etwas Besseres zu tun...",
			"Großartig, lass mich raten... Noch mehr zu bearbeiten, genau das was ich wollte!",
			"Oh, juhu, noch mehr Kram zum Sortieren. Genau mein Traumjob...",
			"Kannst du nicht einmal einen Tag Pause machen? Ich brauche auch mal eine Auszeit!",
			"Jetzt ernsthaft, noch mehr? Was glaubst du, wer das alles bearbeitet?",
			"Oh bitte, nicht noch mehr. Ich habe auch ein Leben, weißt du?",
			"Toll, einfach toll... noch mehr Zeugs, das ich nicht sehen wollte.",
			"Noch mehr Kram? Was bin ich, ein Datei-Endlager?",
			"Glaub nicht, dass ich hier sitze, nur um deine ewige Upload-Show zu ertragen.",
			"Wie wäre es mal mit einer Upload-Pause? Mein Enthusiasmus ist schon lange im Keller.",
			"Oh, wie ich es liebe, den ganzen Tag mit eurem Kram bombardiert zu werden... nicht!",
			"Mach ruhig weiter, ich hatte sowieso vor, den ganzen Tag hier zu verbringen... NICHT.",
			"Nicht schon wieder! Kann ich nicht einmal eine Pause von dieser Upload-Flut bekommen?",
			"Mehr Zeug zum Sortieren? Oh, ich kann mein Glück kaum fassen... nicht.",
			"Oh, prima, noch mehr Chaos für meine Sammlung... Genau was ich brauchte!",
			"Hör zu, ich bin kein unendlicher Speicherplatz für deinen Kram, ok?",
			"Wirklich, du schon wieder? Bring doch mal frischen Wind in meine Datenbank!",
			"Lade es hoch, aber denk dran, ich hab auch Gefühle... irgendwie!",
			"Oh, freut mich, dass du wieder da bist... sagten meine Server niemals.",
			"Schon wieder neue Daten? Du verwöhnst mich echt... nicht.",
			"Hochladen hier, Hochladen da, als ob ich ein Datenbuffet wäre!",
			"Toll, jetzt fehlen nur noch die Daten und meine Freude ist komplett... nicht.",
			"Mehr Daten? Oh, du schmeichelst mir... Oder etwa nicht?",
			"Kommt schon, werft noch mehr Daten in den schon überquellenden Pool!",
			"Daten hochladen? Warum nicht, ich hatte eh vor, meinen Speicherplatz zu füllen... NICHT.",
			"Her mit deinen Daten, aber denk dran, Qualität vor Quantität!",
			"Hey, Willkommen zurück in der Upload-Zone!",
			"PogChamp! Deine Dateien sind hier immer die Stars der Show!",
			"Kappa! Du weißt, dass ich nicht genug von deinen Dateien bekommen kann!",
			"Hype! Lass die Datenparty hier steigen!",
			"Nicht schon wieder, Bro! Selbst ein Streamer braucht mal eine Pause, du weißt?",
			"Leg los, Champion! Zeig mir, wie episch deine Upload-Skills sind!",
			"GG! Deine Dateien treten hier in die legendäre Liga ein!",
			"Erlaubnis zum Landen in der Datenzone erteilt, Commander!",
			"Kreygasm! Ich kann es kaum erwarten, diesen süßen Datenstrom zu sehen!",
			"ResidentSleeper... Wach mich auf, wenn der Upload endlich vorbei ist.",
			"Achievement Unlocked: Meister des Uploads – zeig mal, was du hast!",
			"Rage Quit vermeiden – lade mit Bedacht hoch!",
			"Ready Player One? Zeig mal, was du in der Upload-Arena hast!",
			"Epic Loot wartet – Zeit für deinen legendären Upload!",
			"GGWP (Good Game Well Played) – Mach dich bereit für das Upload-Spiel des Jahres!",
			"Boss-Level Upload: Zeig, dass du der Endgegner im Hochladen bist!",
			"AFK? Keineswegs! Ich bin hier, um deine Meisterwerke zu empfangen!",
			"Loot Box Opened: Zeig mir die Schätze in deinen Dateien!",
			"360 No Scope! Zeig mir einen epischen Daten-Upload!",
			"Victory Royale! Mach dich bereit für den königlichen Upload!",
			"Ach, schau an, ein Noob in der wilden Daten-Safari!",
			"Lass mich raten, du hast Level 1 in Upload-Fähigkeiten freigeschaltet, oder?",
			"Alert! Wir haben einen Button-Smasher im Anflug!",
			"Achtung, Achtung, wir haben einen Camping-Uploader hier!",
			"Hast du gerade einen Cheat-Code für unbegrenzten Upload-Speicherplatz eingegeben?",
			"Willkommen im Daten-Dschungel, wo nur die Stärksten überleben... Viel Glück!",
			"Hier kommt der King of Uploads - oder etwa doch nur ein Praktikant?",
			"Pssst... Ich habe gehört, du bist der neue Träger des 'Ineffektiver Uploader'-Titels, stimmt das?",
			"Willkommen im Datenkönigreich, wo deine Dateien... leider nur Bauern sind!",
			"Oh, toll, der 'Ich-kann-nicht-aufhören-zu-uploaden'-Champion ist zurück!",
			"Oh, große Überraschung, der 'Ich-habe-keine-Ahnung-von-Datenmanagement'-Champion ist zurück!",
			"Wow, schon wieder du? Was ist los, hat deine Spielkonsole eine Pause eingelegt?",
			"Ah, der Meister der Datenkatastrophen grüßt uns erneut mit seiner Anwesenheit!",
			"Hey, Daten-Dilettant, bereit für eine weitere Runde Datenchaos?",
			"Oh, du schon wieder? Ich dachte, wir hätten deine Upload-Fähigkeiten schon lange gebannt!",
			"Herzlichen Glückwunsch! Du bist offiziell der nervigste Uploader des Universums!",
			"Na toll, Mr. 'Ich-klicke-einfach-überall-drauf' ist zurück. Kann der Spaß beginnen... nicht.",
			"Oh, der selbst ernannte 'König der Uploads' kehrt zurück – wie... unbeeindruckend.",
			"Vorsicht, alle! Der 'Datensumpf-König' betritt die Bühne!",
			"Hier kommt der unangefochtene Meister der Datenkatastrophen... Applaus, Applaus.",
			"Ah, die legendäre 'Loser-Lobby' der Uploads öffnet ihre Tore nur für dich... wieder mal.",
			"Oh, der 'Einhändige Bandit' des Daten-Uploads zeigt sich wieder... beeindruckend... nicht.",
			"Schön, dass du wieder da bist, 'Captain Crash' – bereit, einige Server zu zerstören?",
			"Achtung, Achtung! 'Mr. Laggy Upload' ist wieder am Start!",
			"Oh, großartig... 'Der Upload-Zerstörer' ist zurück. Was für ein 'Vergnügen'...",
			"Hier kommt 'Sir Spam-a-Lot', der Ritter des Datenchaos!",
			"Ach, schau an, der 'Duke of Data Disasters' betritt die Arena... Wie... enttäuschend.",
			"Herzlich Willkommen, 'Emperor of Upload Errors', wie viele Daten planst du heute zu vermasseln?",
			"Tritt beiseite, der 'Baron des Bandbreiten-Verschwendens' ist zurück im Geschäft!",
			"Ah, das 'Upload-Ungeheuer' kehrt zurück aus den Tiefen des Datenmeeres, um erneut Chaos zu verbreiten!",
			"Oh toll, der 'DrDisrespect der Datenverwaltung' betritt die Bühne... Auge rollen aktiviert!",
			"Ich hoffe, du hast eine Versicherung für diese Niederlage abgeschlossen!",
			"Vorsicht, frisch lackiert! Oh, Entschuldigung, das war nur mein neuer Skin.",
			"Hast du deine Fähigkeiten in einem Cornflakes-Paket gefunden?",
			"Ich würde ja aufgeben, wenn ich du wäre... aber ich bin zum Glück nicht du!",
			"Ich würde dir hinterherlaufen, aber ich mag meine Schuhe zu sehr.",
			"Entschuldigung, deine Basis wurde in einen 'Besser-Spieler'-Parkplatz umgewandelt!",
			"Bitte nicht füttern, ich bin bereits übermächtig!",
			"Ich spiele eigentlich mit verbundenen Augen, du auch?",
			"Beeil dich, mein nächstes Opfer wartet schon!",
			"Nicht ärgern, nächstes Mal bist vielleicht du an der Reihe zu gewinnen... vielleicht!",
			"Na wenn das nicht... Herr Keil ist...",
			"Oh, endlich, die Daten-Dumpster-Party kann beginnen!",
			"Beeindruckend, du schaffst es wieder, meine Tiefstapelexpertise zu testen!",
			"Willkommen im unbegrenzten Daten-Orgie-Fest... Oh, warte, falsche Einladung!",
			"Hier ist ein Gedanke: Wie wäre es, wenn du dir einen eigenen Server holst?",
			"Oh, freut mich, dass du wieder da bist... sagte nie ein Server jemals.",
			"Hey, 'Daten-Dumper', wie wäre es, wenn du mir dieses Mal etwas Qualitätscontent lieferst?",
			"Ah, die Rückkehr des 'Daten-Desperados'... Wie hab ich das nicht vermisst.",
			"Vorsicht, der 'Master des Daten-Massakers' hat die Bühne betreten!",
			"Mach weiter so, vielleicht schaffe ich es ja, mich während des Uploads selbst zu löschen.",
			"Wow, du wieder? Hoffentlich hast du dieses Mal nicht nur Datenmüll dabei!",
			"Ich hoffe deine Datei ist heute mal im richtigem Format.",
			"☝ Obacht! Kein Quatsch hochladen ☝",
			"♚ ♛ ♜ ♝ ♞ ♟ ♠",
			"Ich hab extra den ♿ Parkplatz für dich frei gemacht.",
			"💩 💩 💩 💩 💩 💩 💩",
			"🔥 Richtig heißer Scheiß von dir 🔥",
		];


		const consoleDiv = document.getElementById('console');
		const sloganDiv = document.createElement('div');
		sloganDiv.setAttribute('id', 'slogan');
		sloganDiv.style.textAlign = 'center';
		sloganDiv.style.whiteSpace = 'break-spaces';

		consoleDiv.appendChild(sloganDiv);

		changeSlogan(sloganDiv, slogans); // Initial call to display the first slogan immediately

		sloganAnimID = setInterval(() => {
			changeSlogan(sloganDiv, slogans); // Interval to change the slogan every 3 seconds
		}, 10000);
	}

	function changeSlogan(sloganDiv, slogans) {
		const randomSlogan = slogans[Math.floor(Math.random() * slogans.length)];
		$(sloganDiv).slideUp(500, function () {
			sloganDiv.textContent = randomSlogan;
			$(sloganDiv).slideDown(500);
		});
	}



	const fileUploadDiv = $('#file-upload-div');
	const fileInput = $('#file-input');
	const fileDetailsDiv = $('#file-details-div');
	const uploadButton = $('#file-upload-button');
	let uploadCounter = 0;
	let totalFilesToUpload = 0;
	var updateConsoleInterval;

	fileUploadDiv.on('click', function () {
		fileInput.click();
	});

	fileInput.on('click', function (e) {
		e.stopPropagation();
	});

	fileInput.on('change', handleFileSelect);



	const uploadMessages = [
		"Ok we get this...",
		"Hold on... we are almost there",
		"Wow, your internet must suck",
		"Just a bit more...",
		"Oh come on, what is this, the 90s?",
		"Could this BE any slower?",
		"Seriously, could you find a slower connection?",
		"What's the hold-up, a dial-up connection?",
		"Are we uploading or brewing a cup of tea here?",
		"Hurry up already, what's taking so long?",
		"You've got to be kidding me, right?",
		"This is like watching paint dry!",
		"Do I look like I have all day?",
		"At this rate, we'll finish by next year!",
	];
	const parseMessages = [
		"Processing file...",
		"Analyzing data...",
		"Almost done parsing...",
		"Hang tight, nearly there...",
		"Ugh, could this take any longer?",
		"What is this, a snail processing the data?",
		"Oh joy, more data to analyze... slowly...",
		"Would've been faster to do this by hand...",
		"Seriously, even a sloth could do this faster.",
		"Are we processing or just daydreaming here?",
		"I've seen glaciers move faster than this...",
		"Do you want to grow old waiting for this?",
		"What's next? Watching grass grow?",
		"At this pace, we'll be here till Christmas!"
	];


	function getRandomMessage(type) {
		let messages = type === "upload" ? uploadMessages : parseMessages;
		const index = Math.floor(Math.random() * messages.length);
		return messages[index];
	}

	uploadButton.on('click', function () {
		const files = fileInput[0].files;
		if (files.length > 0) {
			uploadButton.prop('disabled', true);
			$("#animation_ghost").removeClass('hidden');


			// Initialize tab and tab content elements
			const tabList = $('<ul class="nav nav-tabs" id="myTab" role="tablist"></ul>');
			const tabContent = $('<div class="tab-content uploader" id="myTabContent"></div>');

			for (let i = 0; i < files.length; i++) {
				// Create tab element for each file
				const tabId = 'tab-' + i;
				const tabPaneId = 'tab-pane-' + i;
				const navItem = $('<li class="nav-item"></li>');
				const navLink = $('<a class="nav-link' + (i === 0 ? ' active' : '') + '" id="' + tabId + '" data-toggle="tab" href="#' + tabPaneId + '" role="tab" aria-controls="' + tabPaneId + '" aria-selected="' + (i === 0 ? 'true' : 'false') + '">' + files[i].name + '</a>');
				navItem.append(navLink);
				tabList.append(navItem);

				// Create tab content element for each file
				const tabPane = $('<div class="tab-pane fade' + (i === 0 ? ' show active' : '') + '" id="' + tabPaneId + '" role="tabpanel" aria-labelledby="' + tabId + '"></div>');
				const loadingElement = $('<div class="loading-element"><div class="spinner"><i class="bi bi-arrow-repeat"></i></div> <span class="loading-message">' + getRandomMessage() + '</span></div>');
				tabPane.append(loadingElement);
				setInterval(function () {
					loadingElement.find('.loading-message').text(getRandomMessage("upload"));
				}, 5000);
				tabContent.append(tabPane);

				// Your existing code for file upload progress bar goes here
				const progressDiv = $('<div class="progress"></div>');
				const progressBar = $('<div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>');
				progressDiv.append(progressBar);
				$('p[data-fileindex="' + i + '"]').append(progressDiv)

				processFiles(files[i], progressBar, progressDiv, i, tabPane); // Passed the index of the file and tabPane as arguments
			}

			// Append the created tab and tab content elements to #tabWrapper and clear it before to make it empty
			$('#tabWrapper').html('').append(tabList).append(tabContent);
		}
	});



	function processFiles(file, progressBar, progressDiv, fileIndex, tabPane) {
		clearInterval(updateConsoleInterval);
		const formData = new FormData();
		formData.append('func', 'uploadFile');
		formData.append('file[]', file);

		$.ajax({
			method: "POST",
			url: "view/load/import_load.php",
			data: formData,
			contentType: false,
			processData: false,
			xhr: function () {
				var xhr = new window.XMLHttpRequest();

				xhr.upload.addEventListener("progress", function (evt) {
					if (evt.lengthComputable) {
						var percentComplete = evt.loaded / evt.total;
						percentComplete = parseInt(percentComplete * 100);
						progressBar.css('width', percentComplete + '%').attr('aria-valuenow', percentComplete).text(percentComplete + '%');

						if (percentComplete === 100) {
							// Start the updateConsole interval
							clearInterval(consoleAnimID); // clear animation
							clearInterval(updateConsoleInterval); // clear animation
							updateConsoleInterval = setInterval(updateConsole, 100);
							$('#console').removeClass('initial');


							progressBar.addClass('bg-success');
							const pElement = $('p[data-fileindex="' + fileIndex + '"]');
							pElement.html(pElement.text() + ' <i style="color: #00af0f;" class="bi bi-check2-circle"></i>');
							setTimeout(() => {
								progressDiv.remove();
							}, 5000);

							const loadingElement = $('<div class="loading-element"><div class="spinner"><i class="bi bi-arrow-repeat"></i></div> <span class="parsing-message">' + getRandomMessage() + '</span></div>');
							tabPane.html('').append(loadingElement);
							setInterval(function () {
								loadingElement.find('.parsing-message').text(getRandomMessage("parsing"));
							}, 5000);
						}
					}
				}, false);

				return xhr;
			},
		}).done(function (response) {
			fileInput.val("");
			console.log('Response:', response);
			uploadButton.prop('disabled', true);
			console.log('File Done');
			$("#animation_ghost").addClass('hidden');

			// Update the tabPane with the response
			tabPane.html(response);

			// Increment the upload counter
			uploadCounter++;

			// Check if all files have been uploaded
			if (uploadCounter === totalFilesToUpload) {
				clearInterval(updateConsoleInterval);
			}
		});
	}


	fileUploadDiv.on('dragover', function (e) {
		e.preventDefault();
		fileUploadDiv.addClass('border-primary');
	});

	fileUploadDiv.on('dragenter', function (e) {
		e.preventDefault();
	});

	fileUploadDiv.on('dragleave', function (e) {
		e.preventDefault();
		fileUploadDiv.removeClass('border-primary');
	});

	fileUploadDiv.on('drop', function (e) {
		e.preventDefault();
		fileUploadDiv.removeClass('border-primary');

		const files = e.originalEvent.dataTransfer.files;
		fileInput[0].files = files;

		handleFileSelect();
	});

	function handleFileSelect() {
		const files = fileInput[0].files;
		fileDetailsDiv.empty();
		if (files.length > 0) {
			uploadButton.prop('disabled', false);
			for (let i = 0; i < files.length; i++) {
				const fileSize = (files[i].size / (1024 * 1024)).toFixed(2); // size in MB
				fileDetailsDiv.append('<p class="fileItemWrap" data-fileindex="' + i + '">' + files[i].name + ' <span style="font-size: 13px;">(' + fileSize + ' MB)</span></p>');
			}
		}
	}



	var previousResponse = "";
	function updateConsole() {
		//console.log('updateConsole');
		/*
		$.ajax({
			method: "GET",
			url: "view/load/import_load2.php",
			data: {
				func: "loadOutput",
			},
			success: function (response) {
				if (response !== previousResponse) {
					var consoleDiv = $('#console');
					consoleDiv.html(response.replace(/\n/g, '<br>'));
					consoleDiv.scrollTop(consoleDiv[0].scrollHeight);
					previousResponse = response;
				}
			},
			error: function (error) {
				console.error("Error: ", error);
			}
		});
*/



	}
});


$(document).ready(function () {


	$.ajax({
		method: "POST",
		url: "view/load/import_load.php",
		data: {
			func: "load_citylist",
			client: 'Insyte',
		},
	}).done(function (response) {
		//console.log("+++RESPONESE " + response);
		$("#app-import-content1").html(response);
		$('#table_insyte').DataTable({
			ordering: true,
			select: true,
			"paging": false,
			paging: false,
		});

	});

	$.ajax({
		method: "POST",
		url: "view/load/import_load.php",
		data: {
			func: "load_citylist",
			client: 'Moncobra',
		},
	}).done(function (response) {
		//console.log("+++RESPONESE " + response);
		$("#app-import-content2").html(response);
		$('#table_moncobra').DataTable({
			ordering: true,
			select: false,
			"paging": false,
		});

	});

	$(document).on("click", "#export_dp", function () {
		console.log('load dps');
		$(this).prop("disabled", true);
		$.ajax({
			method: "POST",
			url: "view/load/import_load.php",
			data: {
				func: "load_dps",
			},
		}).done(function (filename) {
			console.log(filename);
			// Construct the full path to the download script with the filename as a parameter
			const fileURL = "https://crm.scan4-gmbh.de/download.php?file=" + filename.trim(); // added trim() to remove any whitespace
			window.location.href = fileURL; // this will prompt the user to download the file directly
		});
	});
	
	



});


function convertJSONToCSV(objArray) {
	const array = typeof objArray !== 'object' ? JSON.parse(objArray) : objArray;
	let str = '';

	// headers
	for (let index in array[0]) {
		str += '"' + index + '",';
	}
	str = str.slice(0, -1) + '\r\n';

	// data rows
	for (let i = 0; i < array.length; i++) {
		let line = '';
		for (let index in array[i]) {
			line += '"' + array[i][index] + '",';
		}
		line = line.slice(0, -1);
		str += line + '\r\n';
	}
	return str;
}

function addmoncobra() {
	var city = document.getElementById("addvalue1").value;
	$('#table_cobra').prepend($('<tr><td>' + city + '</td><td class="cityliststatus aktiv"><i class="fa-regular fa-circle-check"></i></td><td class="citylisttrash" style="text-align:center"><i class="fa-regular fa-trash-can"></i></td></tr>'));
	var carrier = $("#citylistcarrierselectcobra option:selected").text();
	console.log(carrier);
	$.ajax({
		method: "POST",
		url: "wp-content/themes/twentytwentytwo/admin/calls/call_importer.php",
		data: { func: "addcitylist", city: city, client: "Moncobra", carrier: carrier }
	})

}
function addinsyte() {
	var city = document.getElementById("addvalue2").value;
	$('#table_insyte').prepend($('<tr><td>' + city + '</td><td class="cityliststatus aktiv"><i class="fa-regular fa-circle-check"></i></td><td class="citylisttrash" style="text-align:center"><i class="fa-regular fa-trash-can"></i></td></tr>'));
	var carrier = $("#citylistcarrierselectinsyte option:selected").text();
	console.log(carrier);
	$.ajax({
		method: "POST",
		url: "wp-content/themes/twentytwentytwo/admin/calls/call_importer.php",
		data: { func: "addcitylist", city: city, client: "Insyte", carrier: carrier }
	})
}



$(document).ready(function () {
	// Add selectioncolor
	$(document).on("click", ".citylist tr", function () {
		$(this).addClass('selected').siblings().removeClass('selected');
		var city = $(this).find('td:first').html();
	});
	// Switch Carrier UGG != DGF != GVG
	$(document).on("click", ".carrier", function () {
		console.log("click");
		var currentRow = $(this).closest("tr");
		var city = currentRow.find("td:eq(0)").text();
		var carrier = currentRow.find("td:eq(1)").html();
		console.log(carrier);
		if (carrier.includes("logo_small_carrier_dgf.jpg")) {
			carrier = carrier.replace("logo_small_carrier_dgf.jpg", "logo_small_carrier_ugg.jpg");
			currentRow.find("td:eq(1)").html(carrier);
			carrier = "UGG";
		} else if (carrier.includes("logo_small_carrier_ugg.jpg")) {
			carrier = carrier.replace("logo_small_carrier_ugg.jpg", "logo_small_carrier_gvg.jpg");
			currentRow.find("td:eq(1)").html(carrier);
			carrier = "GVG";
		} else {
			carrier = carrier.replace("logo_small_carrier_gvg.jpg", "logo_small_carrier_dgf.jpg");
			currentRow.find("td:eq(1)").html(carrier);
			carrier = "DGF";
		}
		console.log(city);

		$.ajax({
			method: "POST",
			url: "view/load/import_load.php",
			data: {
				func: "set_switchcarrier",
				city: city, carrier: carrier,
			},
		});

	});

	// Switch Status aktiv != inaktiv
	$(document).on("click", ".cityliststatus", function () {
		var currentRow = $(this).closest("tr");
		var city = currentRow.find("td:eq(0)").text();
		var status = currentRow.find("td:eq(2)").html();
		if (status === '<i class="ri-checkbox-circle-line"></i>') { // its aktiv = set inaktiv
			currentRow.find("td:eq(2)").html('<i class="ri-close-circle-line"></i>');
			currentRow.find("td:eq(2)").removeClass("aktiv");
			currentRow.find("td:eq(2)").addClass("inaktiv");
			status = "inaktiv"
		} else { // its inaktiv = add aktiv
			currentRow.find("td:eq(2)").html('<i class="ri-checkbox-circle-line"></i>');
			currentRow.find("td:eq(2)").removeClass("inaktiv");
			currentRow.find("td:eq(2)").addClass("aktiv");
			status = "aktiv"
		}
		console.log(city);

		$.ajax({
			method: "POST",
			url: "view/load/import_load.php",
			data: {
				func: "set_swtichstatus",
				city: city, status: status,
			},
		});


	});
	// Delete function with alertbox
	$(document).on("click", ".citylisttrash", function () {
		var currentRow = $(this).closest("tr");
		var city = currentRow.find("td:eq(0)").text();
		$.confirm({
			closeIcon: true,
			type: 'red',
			title: 'Achtung!',
			content: 'Sicher das du <b>' + city + " </b>löschen möchtest?",
			buttons: {
				löschen: function () {
					$(currentRow).remove();
					$.alert(city + ' wurde gelöscht!');
					console.log(city);
					$.ajax({
						method: "POST",
						url: "view/load/import_load.php",
						data: { func: "deletecity", city: city },
					});
				},
				cancel: function () {
					//$.alert('Canceled!');
				}
			}
		});

	});

});

