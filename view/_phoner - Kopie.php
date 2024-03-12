<?php



if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}


?>
<script type="text/javascript" src="view/includes/js/app_phoner.js"></script>
<div class="body-content-app">
	<div class="row app-phoner-wrapper">
		<div class="col-2 app-phoner-sidebar" id="app-phoner-sidebar">
			<div class="app-sidebar-content-phoner">
				<div id="phoner_sidebar_insyte" class="btn-menulink">
					<div class="btn-wrapper-menulink active">
						<span class="btn-menulink-icon">
							<i class="ri-building-line"></i>
						</span>
						<span class="btn-text-holder">
							<span class="btn-title">Insyte</span>
							<span class="btn-subtitle"><span id="phoner_projects_sum_insyte">0</span> Projekte</span>
						</span>
					</div>
				</div>
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
			</div>

			<div class="app-sidebar-spacer">
				<div class="app-sidebar-filter">
					<span>Filtern:</span>
				</div>
				<div class="app-sidebar-filterselect">
					<ul>
						<li>
							<span id="btn_filter_ugg">
								<span class="btnfilter-check ugg"><i class="ri-checkbox-fill"></i></span>
								<span class="btnfilter-text">UGG</span>
							</span>
						</li>
						<li>
							<span id="btn_filter_dgf">
								<span class="btnfilter-check dgf"><i class="ri-checkbox-fill"></i></span>
								<span class="btnfilter-text">DGF</span>
							</span>
						</li>
						<li>
							<span id="btn_filter_gvg">
								<span class="btnfilter-check gvg"><i class="ri-checkbox-fill"></i></span>
								<span class="btnfilter-text">GVG</span>
							</span>
						</li>
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
				<div id="app-phoner-city-wrapper-insyte" class="row app-phoner-citys">

				</div>
				<div id="app-phoner-city-wrapper-moncobra" class="row app-phoner-citys hidden">

				</div>
			</div>
		</div>
	</div>
</div>