

$(document).ready(function () {


	var rowCount = $('#tbody_active tr').length;

	console.log('Number of rows in table: ' + rowCount);
	$('#count_all').text(rowCount);


});