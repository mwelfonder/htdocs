<?php

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}

$data = $user->data();
$currentuser = $data->fname;
$fname = $data->fname;
$lastname = $data->lname;
$lastname = mb_substr($lastname, 0, 1);


$dir = $_SERVER['DOCUMENT_ROOT'] . '/view/load/hbgmodul_load.php';
include("view/includes/navbar_hbg_module.php");
include_once $_SERVER['DOCUMENT_ROOT'] . '/view/load/hbgmodul_load.php';
$hbgdata = get_all_appt($date_selected);


ob_start();
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script type="text/javascript" src="view/includes/js/app_hbgmodul.js?v=2.3"></script>
<link rel="stylesheet" href="view/includes/style_modul.css?v=2.1" media="all">

<link href="https://maxcdn.bootstrapcdn.com/bootstrap/5.0.0-beta1/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/5.0.0-beta1/js/bootstrap.min.js"></script>




<?php if (strpos($fname, 'Ben') !== false) { ?>
	<span class="hidden" id="console"></span>
<?php } ?>


<div class="modulwrapper hbgmodul">
	<div id="loaderwrapper" class="fullwidth aligncenter mobile loaderwrapper">
		<div class="appt-loader loader"></div>
	</div>
	<div class="row">
		<!-- 
					<div class="col">
						<span class="hidden" id="usernameshort"><?php echo $currentuser . $lastname ?></span>
						<span class="header-text">Hallo <?php echo $fname ?>, du hast insgesamt <span id="appt_all_text"></span> Termine</br>
							Offen: <span id="appt_unset_text"></span> / Abgeschlosene: <span id="appt_done_text"></span>
					</div> 
		<div style="text-align: right;" class="col alignright">

			<span class="btnbugreport"><i class="ri-bug-fill"></i></span>
			<input type="text" id="datePicker" placeholder="Datum auswählen">
		</div>-->
	</div>
	<div class="modul-body">
		<div class="modul-toprow">
			<div class="search-wrapper">
				<input id="search_input" placeholder="Filtern" class="form-control" autocomplete="off"></input>
			</div>
		</div>
		<div class="padding-10"></div>
		<div id="hbglistbody" class="modul-content-wrapper">

		</div>
	</div>
</div>


<div class="settings-container">
	<div class="settings-menu">
		<span class="close-menu"><i class="ri-close-circle-fill" style="color: red;"></i></span>
		<div class="buttons-container"> <!-- Neu hinzugefügte Wrapper-Div -->
			<span class="btnbugreport"><i class="ri-bug-fill"></i></span>
			<span class="btn-calendar"><i class="ri-calendar-line"></i></span>
			<span class="btn-survey"><i class="ri-home-line"></i></span>
		</div>
		<input type="text" id="datePicker" placeholder="Datum auswählen" style="display: none;">
	</div>
	<span class="settings-button"><i class="ri-settings-line"></i></span>
</div>







<div id="selectwrapper">
	<select style="margin-bottom:10px;" class="form-select condition-box form-control select_open hidden" id="select_" aria-label="">
		<option disabled selected hidden>-----</option>
		<option>Generelles Problem</option>
		<?php foreach ($hbgdata as $hbg) { ?>
			<option id="<?php echo $hbg['homeid'] ?>"><?php echo $hbg['city'] . ' ' . $hbg['street'] . ' ' . $hbg['streetnumber'] . $hbg['streetnumberadd'] ?></option>
		<?php } ?>
	</select>
</div>
<script>
	$(".settings-button").on("click", function() {
		$(".settings-menu").toggle();
	});

	$(".btn-calendar").on("click", function() {
		datePicker.open();
	});

	$(".close-menu").on("click", function() {
		$(".settings-menu").hide();
	});
</script>
