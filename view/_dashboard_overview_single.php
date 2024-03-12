<?php


// doppelte hbgs
// neue customers anzeigen lassen


require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}


if (!(hasPerm([2]) || hasPerm([3]) || hasPerm([13]))) {
	die();
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/view/includes/functions.php';

$browserurl = $_SERVER['REQUEST_URI'];
$browserurl = urldecode($browserurl);
$split = explode("project=", $browserurl);
$city = $split[1];
//echo $city;

if (!check_client_permission($city)) {
	//echo 'denied';
	echo client_acces_denied();
	die();
}


?>



<script type="text/javascript" src="view/includes/js/app_dashboard_overview_single.js"></script>
<div class="body-content" id="body-content">
	<div class="row projecttitleheader">
		<div class="col">
			<div class="row ">
				<span id="projectoverview_projectname" class="project-title-h">Projekt: <?php echo $city ?></span>
			</div>
		</div>
		<div class="col">
			<span></span>
		</div>
	</div>
	<div class="row panel_header">
		<ul class="panel_header_list">
			<li class="project_tab activetab" id="project_tab_overview"><i class="ri-grid-line"></i> Overview</li>
			<?php if (hasPerm(2) || (!hasPerm([8, 9]))) { ?>
				<li class="project_tab" id="project_tab_statistics"><i class="ri-line-chart-line"></i> Statistics</li>
			<?php } ?>
			<li class="project_tab" id="project_tab_files"><i class="ri-file-line"></i> Files</li>
			<?php if (hasPerm(2) || (!hasPerm([8, 9]))) { ?>
				<li class="project_tab" id="project_tab_tickets"><i class="ri-coupon-line"></i> Tickets</li>
			<?php } ?>
			<li class="project_tab" id="project_tab_activity"><i class="ri-file-list-line"></i> Activity</li>
		</ul>
	</div>
	<div id="content_wrapper_overview" class="row panel_wrapper projectcontent">
		<div id="loaderwrapper2" class="fullwidth aligncenter">
			<div class="appt-loader loader"></div>
		</div>
	</div>
	<div id="content_wrapper_statistics" class="row panel_wrapper projectcontent hidden"></div>
	<div id="content_wrapper_files" class="row panel_wrapper projectcontent hidden"></div>
	<div id="content_wrapper_tickets" class="row panel_wrapper projectcontent hidden"></div>
	<div id="content_wrapper_activity" class="row panel_wrapper projectcontent hidden"></div>

</div>