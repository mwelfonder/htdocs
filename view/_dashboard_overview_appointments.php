<?php


require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}
if (!(hasPerm([2]) || hasPerm([3]) || hasPerm([14]))) {
	die();
}


 
$username = $user->data()->username;
?>
<script type="text/javascript" src="view/includes/js/general.js"></script>
<script type="text/javascript" src="view/includes/js/app_dashboard_appointments.js"></script>
<div class="body-content-app" id="body-content-app">
	<div id="activation_wrapper" class="row app-wrapper activation">
		<div id="loaderwrapper" class="fullwidth aligncenter hidden">
			<div class="appt-loader loader"></div>
		</div>
		<div class="row app-content-wrapper">
			<div class="row ticketapp-headerwrapper">
				<div><span class="h4">Daily Project Appointments</span></div>

				<div class="filter-wrapper ticketapp">
					<?php if (hasPerm([2, 3]) || (hasPerm([10]))) { ?>
						<span id="btn_filter_ugg">
							<span class="btnfilter-check ugg"><i class="ri-checkbox-fill"></i></span>
							<span class="btnfilter-text">UGG</span>
						</span>
					<?php } ?>
					<?php if (hasPerm([2, 3]) || (hasPerm([11]))) { ?>
						<span id="btn_filter_dgf">
							<span class="btnfilter-check dgf"><i class="ri-checkbox-fill"></i></span>
							<span class="btnfilter-text">DGF</span>
						</span>
					<?php } ?>
					<?php if (hasPerm([2, 3]) || (hasPerm([12]))) { ?>
						<span id="btn_filter_gvg">
							<span class="btnfilter-check gvg"><i class="ri-checkbox-fill"></i></span>
							<span class="btnfilter-text">GVG</span>
						</span>
					<?php } ?>
				</div>
				<div class="filter-wrapper ticketapp carrier">
					<?php if (hasPerm([2, 3]) || (hasPerm([9]))) { ?>
						<span id="btn_filter_moncobra">
							<span class="btnfilter-check moncobra"><i class="ri-checkbox-fill"></i></span>
							<span class="btnfilter-text">Moncobra</span>
						</span>
					<?php } ?>
					<?php if (hasPerm([2, 3]) || (hasPerm([8]))) { ?>
						<span id="btn_filter_insyte">
							<span class="btnfilter-check insyte"><i class="ri-checkbox-fill"></i></span>
							<span class="btnfilter-text">Insyte</span>
						</span>
					<?php } ?>
				</div>
			</div>
			<div class="row">
				<?php echo date('Y.m.d'); ?> #<span id="count_all"></span>
			</div>
			<div class="spacer10"></div>
			<div style="max-height:80vh;" class="row app-tickets-tablewrapper activetable scrollwrapper">
				<div class="tablewrapper active fullwidth">
					<table id="tbody_active" class="activation-table" style="width:100%">
						<thead style="text-align:left;">
							<th class="ticket-head left">#</th>
							<th class="ticket-head left">HomeID</th>
							<th class="ticket-head left">DP Number</th>
							<th class="ticket-head left">City</th>
							<th class="ticket-head left">Street</th>
							<?php if (hasPerm([2, 3])) { ?>
							<th class="ticket-head left">Comment</th>
							<?php } ?>
						</thead>
						<tbody class="">

						</tbody>
					</table>
				</div>
			</div>

		</div>
	</div>
	<div id="hbgcheck_empty" class="row app-wrapper hbgcheck _empty hidden">
		<div class="hbgcheckempty">Keine HBGs zum Überprüfen gefunden</div>
	</div>

</div>