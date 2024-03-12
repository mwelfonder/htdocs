init_pendinglist()
$(document).ready(function () {





});









function init_pendinglist() {

	$.ajax({
		method: "POST",
		url: "view/load/mail_load.php",
		data: {
			func: "mail_load_pendings",
		},
	}).done(function (response) {
		console.log(response)
		$('#maillistcontainer').html(response);
	});
}