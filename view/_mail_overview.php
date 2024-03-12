<?php

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}
$username = $user->data()->username;
?>
<script type="text/javascript" src="view/includes/js/app_mail.js"></script>

<div class="body-content-app" id="body-content-app">
	<div class="row app-content-wrapper">
		<div class="row ticketapp-headerwrapper">
			<div class="h4"> <i class="ri-mail-line"></i>Mail Übersicht</div>

			<div class="filter-wrapper ticketapp">
				<span id="btn_filter_ugg">
					<span class="btnfilter-check ugg"><i class="ri-checkbox-fill"></i></span>
					<span class="btnfilter-text">UGG</span>
				</span>
				<span id="btn_filter_dgf">
					<span class="btnfilter-check dgf"><i class="ri-checkbox-fill"></i></span>
					<span class="btnfilter-text">DGF</span>
				</span>
				<span id="btn_filter_gvg">
					<span class="btnfilter-check gvg"><i class="ri-checkbox-fill"></i></span>
					<span class="btnfilter-text">GVG</span>
				</span>
			</div>
			<div class="filter-wrapper ticketapp carrier">
				<span id="btn_filter_moncobra">
					<span class="btnfilter-check moncobra"><i class="ri-checkbox-fill"></i></span>
					<span class="btnfilter-text">Moncobra</span>
				</span>
				<span id="btn_filter_insyte">
					<span class="btnfilter-check insyte"><i class="ri-checkbox-fill"></i></span>
					<span class="btnfilter-text">Insyte</span>
				</span>
			</div>
		</div>
		<div class="row app-tickets-tablewrapper">
			<div class="app-tickets-tableview">
				<table style="list-style:none;">
					<thead class="maillistheader">
							<th><b>Sc4Status</b></th>
							<th><b>MailStatus</b></th>
							<th><b>HomeID</b></th>
							<th><b>Grund</b></th>
							<th><b>Carrier</b></th>
							<th><b>Client</b></th>
							<th><b>Datum</b></th>
							<th><b>Uhr</b></th>
							<th><b>User</b></th>
							<th><b>Edit</b></th>
					</thead>
				<tbody id="maillistcontainer" style="list-style:none;">

				</table>
			</div>
		</div>
	</div>
</div>