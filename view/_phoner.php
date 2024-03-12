<?php



if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}
/*
Vorgehensweise:
Nur 1 KD am Tag anrufen und speichern!! Beispiel 37539AEN_2__1

Kunden reporten welche sagen begehung hat schon stattgefunden
*/




if (!hasPerm([2, 3, 10])) {
	die();
}




?>
<script type="text/javascript" src="view/includes/js/app_phoner.js"></script>
<div class="body-content-app">
	<div class="row app-phoner-wrapper panel_w">

		<div id="loaderwrapper" class="fullwidth aligncenter">
			<div class="appt-loader loader"></div>
		</div>

		<div style="user-select: none;" class="col-2 app-phoner-sidebar" id="app-phoner-sidebar">
			<div class="app-sidebar-content-phoner">
				<?php if (hasPerm(2) || hasPerm(3) || hasPerm(8)) { ?>
					<div id="phoner_sidebar_insyte" class="btn-menulink active">
						<div class="btn-wrapper-menulink">
							<span class="btn-menulink-icon">
								<i class="ri-building-line"></i>
							</span>
							<span class="btn-text-holder">
								<span class="btn-title">Insyte</span>
								<span class="btn-subtitle"><span id="phoner_projects_sum_insyte">0</span> Projekte</span>
							</span>
						</div>
					</div>
				<?php } ?>
				<?php if (hasPerm(2) || hasPerm(3) || hasPerm(9)) { ?>
					<div id="phoner_sidebar_moncobra" class="btn-menulink">
						<div class="btn-wrapper-menulink">
							<span class="btn-menulink-icon">
								<i class="ri-building-line"></i>
							</span>
							<span class="btn-text-holder">
								<span class="btn-title">Moncobra</span>
								<span id="btn_subtitle_projects2" class="btn-subtitle"><span id="phoner_projects_sum_moncobra">0</span> Projekte</span>
							</span>
						</div>
					</div>
				<?php } ?>
				<?php if (hasPerm(2) || hasPerm(3) || hasPerm(17)) { ?>
					<div id="phoner_sidebar_fol" class="btn-menulink">
						<div class="btn-wrapper-menulink">
							<span class="btn-menulink-icon">
								<i class="ri-building-line"></i>
							</span>
							<span class="btn-text-holder">
								<span class="btn-title">FOL</span>
								<span id="btn_subtitle_projects2" class="btn-subtitle"><span id="phoner_projects_sum_fol">0</span> Projekte</span>
							</span>
						</div>
					</div>
				<?php } ?>
			</div>


			<div class="app-sidebar-spacer">
				<div class="app-sidebar-filter">
					<span>Filtern:</span>
				</div>
				<div class="app-sidebar-filterselect">
					<ul>
						<?php if (hasPerm(2) || hasPerm(3) || hasPerm(10)) { ?>
							<li>
								<span id="btn_filter_ugg" class="btn-filter" data-carrier="UGG">
									<span class="btnfilter-check ugg"><i class="ri-checkbox-fill"></i></span>
									<span class="btnfilter-text">UGG</span>
								</span>
							</li>
						<?php } ?>
						<?php if (hasPerm(2) || hasPerm(3) || hasPerm(11)) { ?>
							<li>
								<span id="btn_filter_dgf" class="btn-filter" data-carrier="DGF">
									<span class="btnfilter-check dgf"><i class="ri-checkbox-fill"></i></span>
									<span class="btnfilter-text">DGF</span>
								</span>
							</li>
						<?php } ?>
						<?php if (hasPerm(2) || hasPerm(3) || hasPerm(12)) { ?>
							<li>
								<span id="btn_filter_gvg" class="btn-filter" data-carrier="GVG">
									<span class="btnfilter-check gvg"><i class="ri-checkbox-fill"></i></span>
									<span class="btnfilter-text">GVG</span>
								</span>
							</li>
						<?php } ?>
						<?php if (hasPerm(2) || hasPerm(3) || hasPerm(18)) { ?>
							<li>
								<span id="btn_filter_glasfaserplus" class="btn-filter" data-carrier="GlasfaserPlus">
									<span class="btnfilter-check glasfaserplus"><i class="ri-checkbox-fill"></i></span>
									<span class="btnfilter-text">GlasfaserPlus</span>
								</span>
							</li>
						<?php } ?>
					</ul>
				</div>


			</div>
			<!--
			<div class="app-sidebar-box-wrapper">
				<div id="followupbtn" class="btn-wrapper-menulink followup greensub">
					<span class="btn-menulink-icon"><i class="ri-history-line"></i></span>
					<span class="btn-text-holder"> Wiedervorlage</span>
				</div>
			</div>
-->
		</div>
		<div class="col app-phoner-main" id="app-phoner-main">
			<div class="app-phoner-city-wrapper">
				<div class="app-phoner-input-wrapper" style="margin-bottom:4px;">
					<input id="phoner_search_input" placeholder="Filtern" class="form-control" autocomplete="off"></input>
				</div>
				<div id="app-phoner-city-wrapper-insyte" class="row app-phoner-citys">

				</div>
				<div id="app-phoner-city-wrapper-moncobra" class="row app-phoner-citys hidden">

				</div>
				<div id="app-phoner-city-wrapper-fol" class="row app-phoner-citys hidden">

				</div>
			</div>
		</div>
	</div>
</div>