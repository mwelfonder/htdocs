<?php

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}
if (!hasPerm([2, 21])) {
	die();
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/view/includes/tickets.php';
$logged_in = $user->data();
$currentuser = $logged_in->username;


?>
<script>
	const currentuser = <?php echo json_encode($currentuser); ?>;
</script>



<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="//cdn.quilljs.com/latest/quill.min.js"></script>

<script type="text/javascript" src="view/includes/js/app_tickets.js?=v2.1"></script>


<div class="ticket-dashboard">
	<?php if (hasPerm(2) || (hasPerm([9]))) { ?>
		<div class="ticket-box open-tickets" id="open-tickets">Unbearbeite Tickets: <span class="number">0</span></div>
		<div class="ticket-box total-tickets" id="total-tickets">Gesamt Tickets: <span class="number">0</span></div>
		<div class="ticket-box closed-today" id="closed-today">Heute geschlossen: <span class="number">0</span></div>
		<div class="ticket-box created-today" id="created-today">Heute erstellt: <span class="number">0</span></div>
	<?php } ?>
	<div class="ticket-box pending-tickets" id="pending-tickets">Pending Tickets: <span class="number">0</span></div>
	<div class="ticket-box progress-tickets" id="progress-tickets">Tickets in Progress: <span class="number">0</span></div>
</div>





<div class="body-content-app" id="body-content-app">
	<div class="row app-content-wrapper">
		<div class="row ticketapp-headerwrapper d-flex">
			<div class="h4">Ticket Übersicht</div>

			<div class="filter-wrapper ticketapp">
				<?php if (hasPerm([2, 10])) { ?>
					<span id="btn_filter_ugg" data-carrier="UGG">
						<span class="btnfilter-check ugg"><i class="ri-checkbox-fill"></i></span>
						<span class="btnfilter-text">UGG</span>
					</span>
				<?php } ?>
				<?php if (hasPerm([2, 11])) { ?>
					<span id="btn_filter_dgf" data-carrier="DGF">
						<span class="btnfilter-check dgf"><i class="ri-checkbox-fill"></i></span>
						<span class="btnfilter-text">DGF</span>
					</span>
				<?php } ?>
				<?php if (hasPerm([2, 12])) { ?>
					<span id="btn_filter_gvg" data-carrier="GVG">
						<span class="btnfilter-check gvg"><i class="ri-checkbox-fill"></i></span>
						<span class="btnfilter-text">GVG</span>
					</span>
				<?php } ?>
				<?php if (hasPerm([2, 18])) { ?>
					<span id="btn_filter_glasfaserplus" data-carrier="glasfaserplus">
						<span class="btnfilter-check glasfaserplus"><i class="ri-checkbox-fill"></i></span>
						<span class="btnfilter-text">GlasfaserPlus</span>
					</span>
				<?php } ?>
			</div>

			<div class="filter-wrapper ticketapp carrier">
				<?php if (hasPerm(2) || (hasPerm([9]))) { ?>
					<span id="btn_filter_moncobra">
						<span class="btnfilter-check moncobra"><i class="ri-checkbox-fill"></i></span>
						<span class="btnfilter-text">Moncobra</span>
					</span>
				<?php } ?>
				<?php if (hasPerm(2) || (hasPerm([8]))) { ?>
					<span id="btn_filter_insyte">
						<span class="btnfilter-check insyte"><i class="ri-checkbox-fill"></i></span>
						<span class="btnfilter-text">Insyte</span>
					</span>
				<?php } ?>
				<?php if (hasPerm([2, 17])) { ?>
					<span id="btn_filter_fol">
						<span class="btnfilter-check fol"><i class="ri-checkbox-fill"></i></span>
						<span class="btnfilter-text">FOL</span>
					</span>
				<?php } ?>
			</div>
			<button id="newTicket" type="button" class="btn btn-primary btn-sm ml-auto" style="margin: 5px;">
				<i class="ri-add-line"></i> New Ticket
			</button>
		</div>
		<div class="row app-tickets-tablewrapper">
			<div class="app-tickets-tableview">
				<table id="tickets_overview" class="tickets-table user-select-none pe-auto" style="width:100%">
					<thead style="text-align:center;">
					</thead>
					<tbody id="tickets_overview_body" class="user-select-none pe-auto">

					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>





<style>

</style>