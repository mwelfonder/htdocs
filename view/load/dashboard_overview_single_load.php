<?php
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}

$logged_in = $user->data();
$currentuser = $logged_in->username;
$userdetails = fetchPermissionUsers(1); // 5 = Telefonist
for ($i = 0; $i < count($userdetails); $i++) {
	$data = fetchUserDetails(null, null, $userdetails[$i]->user_id);
	$a_userlist =  array($data->username => $data->profile_pic);
}


include_once "../../view/includes/functions.php";
include_once "../../view/load/dashboard_overview_load.php";


date_default_timezone_set('Europe/Berlin');
if (!isset($_POST['func'])) {
	die();
}
/*
if (isset($_POST['city'])) {
	$city = $_POST['city'];
	$s = check_client_permission($city);
	echo 'checkis:'.$s;
}
*/

$func = $_POST['func'];
if ($func === "load_tab_overview") {
	$city = $_POST['city'];
	load_tab_overview($city);
} else if ($func === "load_tab_tickets") {
	$city = $_POST['city'];
	load_tab_ticket($city);
} else if ($func === "load_tab_activity") {
	$city = $_POST['city'];
	load_tab_activity($city);
} else if ($func === "load_tab_statistics") {
	$city = $_POST['city'];
	load_tab_statistics($city);
} else if ($func === "load_tab_files") {
	$city = $_POST['city'];
	load_tab_files($city);
}




function load_tab_files($city)
{

	global $currentuser;
	$a_files = stats_filelist($city);
	$length = count($a_files);

	ob_start();

?>

	<div class="row panel_w">
		<div class="row">
			<div class="col-12">
				<span class="tabname-title"><i class="ri-history-line"></i> Files</span>
			</div>
		</div>
		<div class="spacer10"></div>

	</div>

	<div class="row panel_w">
		<div class="col-12 panel_b ">
			<table id="tprojectoverview_files" style="width:100%">
				<thead>
					<th><i class="ri-checkbox-blank-line"></i></th>
					<th><i class="ri-download-cloud-2-line"></i></th>
					<th><i class="ri-external-link-line"></i></th>
					<th>Name</th>
					<th>Size</th>
					<th>Last Modified</th>
				</thead>
				<tbody>
					<?php for ($i = 0; $i < $length; $i++) {
						$filename = $a_files[$i]['appt_file'];
						$split = explode('-', $filename, 4);
						if (isset($split[1])) {
							$homeid = $split[1];
						} else {
							$homeid = "undefind";
						}
						$split = explode('_', $filename, 2);
						if (isset($split[1])) {
							$project = $split[0];
							//$filename = $split[1];
						} else {
							$project = "undefind";
						}
						$isexcel = true;
						$year = date("Y");
						$ficon = "https://crm.scan4-gmbh.de/view/images/icon_small_excel_color.png";
						$source = '/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/uploads/hbgprotokolle/begehungen/excel/' . $year . '/' . $project . '/' . $filename;
						$sourceurl = 'https://crm.scan4-gmbh.de/uploads/hbgprotokolle/begehungen/excel/' . $year . '/' . $project . '/' . $filename;
						if (!file_exists($source)) {
							$isexcel = false;
							$ficon = "https://crm.scan4-gmbh.de/view/images/icon_small_pdf_color.png";
							$source = '/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/uploads/hbgprotokolle/begehungen/screenshots/' . $year . '/' . $project . '/' . $filename;
							$sourceurl = 'https://crm.scan4-gmbh.de/uploads/hbgprotokolle/begehungen/screenshots/' . $year . '/' . $project . '/' . $filename;
						}

						// get last modified date of source
						$last_modified = date("Y-m-d", filemtime($source));

					?>
						<tr>
							<td><i class="ri-checkbox-blank-line"></i></td>
							<?php if ((hasPerm(2)) || (!hasPerm([2]) && $isexcel === true) || (hasPerm([8]))) { // Insyte 8 ?> 
								<td><a target="_blank" rel="noreferrer noopener" href="<?php echo $sourceurl ?>" download><i class="ri-download-cloud-2-line"></i></a></td>
								<td><a target="_blank" rel="noreferrer noopener" href="<?php echo $sourceurl ?>"><i class="ri-external-link-line"></i></a></td>
							<?php } else { ?>
								<td></td>
								<td></td>
							<?php } ?>
							<td>
								<p class="nomargin flex"><img class="smalltableicon" src="<?php echo $ficon ?>" /><?php echo $filename ?></p>
							</td>
							<td><?php echo formatSizeUnits(filesize($source))  ?></td>
							<td><?php echo $last_modified ?></td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	</div>

	<script>
		$('#tprojectoverview_files').DataTable({
			ordering: true,
			select: true,
			"pageLength": 15,
		});
		var table = $('#tprojectoverview_files').DataTable();
		table
			.column('0:visible')
			.order('desc')
			.draw();
		$($.fn.dataTable.tables(true)).DataTable().columns.adjust();
	</script>


<?php

}

function load_tab_statistics($city)
{

	global $currentuser;
	$a_calls = load_overview_a_callshorts($city, '', '', 'all', 'intern');
	$a_stats = stats_scan4homes($city);
	$a_projekt = stats_projektinfo($city);
	$a_hbg = stats_hbg($city);





	ob_start();
?>
	<div class="row panel_w">

		<div class="col-6">
			<div class="row">
				<div class="col-6">
					<div class="row panel_b">
						<div class="col">
							<h4 class="project-headline">Carrier</h4>
						</div>
						<div class="col coltoright aligncenter">
							<h4 class="project-headline-icon"><i class="ri-pie-chart-2-line"></i></h4>
						</div>
						<div class="row">
							<div class="col textaligncenter">
								<div class="projectoverview-statsblock-shortstats">
									<p class="projectoverview-shortstats-h">Open</p>
									<p class="projectlist-stats-s"><?php echo $a_stats[0]['carrieropen'] ?></p>
								</div>
							</div>
							<div class="col textaligncenter">
								<div class="projectoverview-statsblock-shortstats">
									<p class="projectoverview-shortstats-h">Done</p>
									<p class="projectlist-stats-s"><?php echo $a_stats[0]['carrierdone'] ?></p>
								</div>
							</div>
							<div class="col textaligncenter">
								<div class="projectoverview-statsblock-shortstats">
									<p class="projectoverview-shortstats-h">Stopped</p>
									<p class="projectlist-stats-s"><?php echo $a_stats[0]['carrierstopped'] ?></p>
								</div>
							</div>
						</div>
						<div class="col-12">
							<?php
							$pb1 = round(($a_stats[0]['carrieropen'] / $a_stats[0]['total']) * 100, 2);
							$pb1_int = round(($a_stats[0]['carrieropen'] / $a_stats[0]['total']) * 100);
							$pb2 = round(($a_stats[0]['carrierdone'] / $a_stats[0]['total']) * 100, 2);
							$pb2_int = round(($a_stats[0]['carrierdone'] / $a_stats[0]['total']) * 100);
							$pb3 = round(($a_stats[0]['carrierstopped'] / $a_stats[0]['total']) * 100, 2);
							$pb3_int = round(($a_stats[0]['carrierstopped'] / $a_stats[0]['total']) * 100);
							?>
							<div class="miniBar minibar_fullheight textaligncenter" style="font-size:12px; color:#fff;">
								<div class="miniBarProgress" style="left: 0%; width: <?php echo $pb1_int ?>%; background-color: var(--statopen);"><?php echo $pb1 ?>%</div>
								<div class="miniBarProgress" style="left: <?php echo $pb1_int ?>%; width: <?php echo $pb2_int ?>%; background-color: var(--statdone);"><?php echo $pb2 ?>%</div>
								<div class="miniBarProgress" style="left: <?php echo $pb1_int + $pb2_int ?>%; width: <?php echo $pb3_int ?>%; background-color: var(--statstopped);"><?php echo $pb3 ?>%</div>
							</div>
							<div class="spacer10"></div>
						</div>
					</div>
				</div>
				<div class="col-6">
					<div class="row panel_b">
						<div class="col">
							<h4 class="project-headline">Scan4</h4>
						</div>
						<div class="col coltoright aligncenter">
							<h4 class="project-headline-icon"><i class="ri-pie-chart-2-line"></i></h4>
						</div>
						<div class="row">
							<div class="col textaligncenter">
								<div class="projectoverview-statsblock-shortstats">
									<p class="projectoverview-shortstats-h">Open</p>
									<p class="projectlist-stats-s"><?php echo $a_stats[0]['open'] ?></p>
								</div>
							</div>
							<div class="col textaligncenter">
								<div class="projectoverview-statsblock-shortstats">
									<p class="projectoverview-shortstats-h">Done</p>
									<p class="projectlist-stats-s"><?php echo $a_stats[0]['done'] ?></p>
								</div>
							</div>
							<div class="col textaligncenter">
								<div class="projectoverview-statsblock-shortstats">
									<p class="projectoverview-shortstats-h">Stopped</p>
									<p class="projectlist-stats-s"><?php echo $a_stats[0]['stopped'] ?></p>
								</div>
							</div>

						</div>
						<div class="col-12">
							<?php
							$pb1 = round(($a_stats[0]['open'] / $a_stats[0]['total']) * 100, 2);
							$pb1_int = round(($a_stats[0]['open'] / $a_stats[0]['total']) * 100);
							$pb2 = round(($a_stats[0]['done'] / $a_stats[0]['total']) * 100, 2);
							$pb2_int = round(($a_stats[0]['done'] / $a_stats[0]['total']) * 100);
							$pb3 = round(($a_stats[0]['stopped'] / $a_stats[0]['total']) * 100, 2);
							$pb3_int = round(($a_stats[0]['stopped'] / $a_stats[0]['total']) * 100);
							?>
							<div class="miniBar minibar_fullheight textaligncenter" style="font-size:12px; color:#fff;">
								<div class="miniBarProgress" style="left: 0%; width: <?php echo $pb1_int ?>%; background-color: var(--statopen);"><?php echo $pb1 ?>%</div>
								<div class="miniBarProgress" style="left: <?php echo $pb1_int ?>%; width: <?php echo $pb2_int ?>%; background-color: var(--statdone);"><?php echo $pb2 ?>%</div>
								<div class="miniBarProgress" style="left: <?php echo $pb1_int + $pb2_int ?>%; width: <?php echo $pb3_int ?>%; background-color: var(--statstopped);"><?php echo $pb3 ?>%</div>
							</div>
							<div class="spacer10"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-12">
				<div class="row panel_b">
					<div class="col">
						<h4 class="project-headline">Scan4 Statistics</h4>
					</div>
					<div class="col coltoright aligncenter">
						<h4 class="project-headline-icon"><i class="ri-pie-chart-2-line"></i></h4>
					</div>
					<div class="row">
						<div class="col textaligncenter">
							<div class="projectoverview-statsblock-shortstats">
								<p class="projectoverview-shortstats-h">Open</p>
								<p class="projectlist-stats-s"><?php echo $a_stats[0]['open'] ?></p>
							</div>
						</div>
						<div class="col textaligncenter">
							<div class="projectoverview-statsblock-shortstats">
								<p class="projectoverview-shortstats-h">Planned</p>
								<p class="projectlist-stats-s"><?php echo $a_stats[0]['planned'] ?></p>
							</div>
						</div>
						<div class="col textaligncenter">
							<div class="projectoverview-statsblock-shortstats">
								<p class="projectoverview-shortstats-h">Done</p>
								<p class="projectlist-stats-s"><?php echo $a_stats[0]['done'] ?></p>
							</div>
						</div>
						<div class="col textaligncenter">
							<div class="projectoverview-statsblock-shortstats">
								<p class="projectoverview-shortstats-h">Done Excel</p>
								<p class="projectlist-stats-s"><?php echo $a_stats[0]['doneexcel'] ?></p>
							</div>
						</div>
						<div class="col textaligncenter">
							<div class="projectoverview-statsblock-shortstats">
								<p class="projectoverview-shortstats-h">Stopped</p>
								<p class="projectlist-stats-s"><?php echo $a_stats[0]['stopped'] ?></p>
							</div>
						</div>
					</div>
					<div class="col-12">
						<?php
						$pb1 = round(($a_stats[0]['open'] / $a_stats[0]['total']) * 100, 2);
						$pb1_int = round(($a_stats[0]['open'] / $a_stats[0]['total']) * 100);
						$pb2 = round(($a_stats[0]['done'] / $a_stats[0]['total']) * 100, 2);
						$pb2_int = round(($a_stats[0]['done'] / $a_stats[0]['total']) * 100);
						$pb3 = round(($a_stats[0]['stopped'] / $a_stats[0]['total']) * 100, 2);
						$pb3_int = round(($a_stats[0]['stopped'] / $a_stats[0]['total']) * 100);
						?>
						<div class="miniBar minibar_fullheight textaligncenter" style="font-size:12px; color:#fff;">
							<div class="miniBarProgress" style="left: 0%; width: <?php echo $pb1_int ?>%; background-color: var(--statopen);"><?php echo $pb1 ?>%</div>
							<div class="miniBarProgress" style="left: <?php echo $pb1_int ?>%; width: <?php echo $pb2_int ?>%; background-color: var(--statdone);"><?php echo $pb2 ?>%</div>
							<div class="miniBarProgress" style="left: <?php echo $pb1_int + $pb2_int ?>%; width: <?php echo $pb3_int ?>%; background-color: var(--statstopped);"><?php echo $pb3 ?>%</div>
						</div>
						<div class="spacer10"></div>
					</div>
					<div class="row">
						<div class="col textaligncenter">
							<div class="projectoverview-statsblock-shortstats">
								<p class="projectoverview-shortstats-h">Pending</p>
								<p class="projectlist-stats-s"><?php echo $a_stats[0]['pending'] ?></p>
							</div>
						</div>
						<div class="col textaligncenter">
							<div class="projectoverview-statsblock-shortstats">
								<p class="projectoverview-shortstats-h">Post</p>
								<p class="projectlist-stats-s"><?php echo $a_stats[0]['post'] ?></p>
							</div>
						</div>
						<div class="col textaligncenter">
							<div class="projectoverview-statsblock-shortstats">
								<p class="projectoverview-shortstats-h">Mail</p>
								<p class="projectlist-stats-s"><?php echo $a_stats[0]['mail'] ?></p>
							</div>
						</div>
						<div class="col textaligncenter">
							<div class="projectoverview-statsblock-shortstats">
								<p class="projectoverview-shortstats-h">Failed</p>
								<p class="projectlist-stats-s"><?php echo $a_stats[0]['failed'] ?></p>
							</div>
						</div>
						<div class="col textaligncenter">
							<div class="projectoverview-statsblock-shortstats">
								<p class="projectoverview-shortstats-h">No Phone</p>
								<p class="projectlist-stats-s"><?php echo $a_stats[0]['nophone'] ?></p>
							</div>
						</div>
					</div>
					<div class="col-12">
						<div class="miniBar minibar_fullheight textaligncenter" style="font-size:12px; color:#fff;">
							<div class="miniBarProgress" style="left: 0%; width: 20%; background-color: grey;"></div>
							<div class="miniBarProgress" style="left: 20%; width: 20%; background-color: grey;"></div>
							<div class="miniBarProgress" style="left: 40%; width: 20%; background-color: grey;"></div>
							<div class="miniBarProgress" style="left: 60%; width: 20%; background-color: grey;"></div>
							<div class="miniBarProgress" style="left: 80%; width: 20%; background-color: grey;"></div>
						</div>
						<div class="spacer10"></div>
					</div>
				</div>
			</div>




		</div>
	</div>
	<div class="row panel_w">
		<div class="col-6 ">
			<div class="row panel_b">
				<div class="col">
					<h4 class="project-headline">Call Statistics</h4>
				</div>
				<div class="col aligncenter">
					<p class="projectoverview-shortstats-h">Total Calls: <?php echo $a_calls[0]['calls'] ?></p>
				</div>
				<div class="col coltoright aligncenter">
					<h4 class="project-headline-icon"><i class="ri-phone-line"></i></h4>
				</div>
				<?php
				$call_p_missed = round(($a_calls[0]['missed'] / $a_calls[0]['calls']) * 100, 2);
				$call_p_missed_int = round(($a_calls[0]['missed'] / $a_calls[0]['calls']) * 100);
				$call_p_nohbg = round(($a_calls[0]['nohbg'] / $a_calls[0]['calls']) * 100, 2);
				$call_p_nohbg_int = round(($a_calls[0]['nohbg'] / $a_calls[0]['calls']) * 100);
				$call_p_sethbg = round(($a_calls[0]['hbgset'] / $a_calls[0]['calls']) * 100, 2);
				$call_p_sethbg_int = round(($a_calls[0]['hbgset'] / $a_calls[0]['calls']) * 100);

				?>
				<div class="row">
					<div class="col textaligncenter">
						<div class="projectoverview-statsblock-shortstats">
							<p class="projectoverview-shortstats-h">Nicht erreicht</p>
							<p class="projectlist-stats-s"><?php echo $a_calls[0]['missed'] ?></p>
						</div>
					</div>
					<div class="col textaligncenter">
						<div class="projectoverview-statsblock-shortstats">
							<p class="projectoverview-shortstats-h">no HBG</p>
							<p class="projectlist-stats-s"><?php echo $a_calls[0]['nohbg'] ?></p>
						</div>
					</div>
					<div class="col textaligncenter">
						<div class="projectoverview-statsblock-shortstats">
							<p class="projectoverview-shortstats-h">set HBG</p>
							<p class="projectlist-stats-s"><?php echo $a_calls[0]['hbgset'] ?></p>
						</div>
					</div>
					<div class="col textaligncenter">
						<?php $cta = round((($a_calls[0]['hbgset'] / $a_calls[0]['calls']) * 100), 2); ?>
						<div class="projectoverview-statsblock-shortstats">
							<p class="projectoverview-shortstats-h">CTA</p>
							<p class="projectlist-stats-s"><?php echo $cta ?>%</p>
						</div>
					</div>
				</div>
				<div class="col-12">
					<div class="miniBar minibar_fullheight textaligncenter" style="font-size:12px; color:#fff;">
						<div class="miniBarProgress" style="left: 0%; width: <?php echo $call_p_missed_int ?>%; background-color: grey;"><?php echo $call_p_missed ?>%</div>
						<div class="miniBarProgress" style="left: <?php echo $call_p_missed_int ?>%;width: <?php echo $call_p_nohbg_int ?>%; background-color: var(--statstopped);"><?php echo $call_p_nohbg ?>%</div>
						<div class="miniBarProgress" style="left: <?php echo $call_p_missed_int + $call_p_nohbg_int ?>%; width: <?php echo $call_p_sethbg_int ?>%; background-color: var(--statdone);"><?php echo $call_p_sethbg ?>%</div>
					</div>
					<div class="spacer10"></div>
				</div>
				<div class="row">
					<div class="col textaligncenter">
						<div class="projectoverview-statsblock-shortstats">
							<p class="projectoverview-shortstats-h">PLANNED</p>
							<p class="projectlist-stats-s"><?php echo $a_hbg[0]['planned'] ?></p>
						</div>
					</div>
					<div class="col textaligncenter">
						<div class="projectoverview-statsblock-shortstats">
							<p class="projectoverview-shortstats-h">MOVED</p>
							<p class="projectlist-stats-s"><?php echo $a_hbg[0]['moved'] ?></p>
						</div>
					</div>
					<div class="col textaligncenter">
						<div class="projectoverview-statsblock-shortstats">
							<p class="projectoverview-shortstats-h">STORNO</p>
							<p class="projectlist-stats-s"><?php echo $a_hbg[0]['storno'] ?></p>
						</div>
					</div>
				</div>
				<div class="col-12">
					<?php
					$pb1 = round(($a_hbg[0]['planned'] / $a_hbg[0]['all']) * 100, 2);
					$pb1_int = round(($a_hbg[0]['planned'] / $a_hbg[0]['all']) * 100);
					$pb2 = round(($a_hbg[0]['moved'] / $a_hbg[0]['all']) * 100, 2);
					$pb2_int = round(($a_hbg[0]['moved'] / $a_hbg[0]['all']) * 100);
					$pb3 = round(($a_hbg[0]['storno'] / $a_hbg[0]['all']) * 100, 2);
					$pb3_int = round(($a_hbg[0]['storno'] / $a_hbg[0]['all']) * 100);
					?>
					<div class="miniBar minibar_fullheight textaligncenter" style="font-size:12px; color:#fff;">
						<div class="miniBarProgress" style="left: 0%; width: <?php echo $pb1_int ?>%; background-color: grey;"><?php echo $pb1 ?>%</div>
						<div class="miniBarProgress" style="left: <?php echo $pb1_int ?>%; width: <?php echo $pb2_int ?>%; background-color: grey;"><?php echo $pb2 ?>%</div>
						<div class="miniBarProgress" style="left: <?php echo $pb1_int + $pb2_int ?>%; width: <?php echo $pb3_int ?>%; background-color: grey;"><?php echo $pb3 ?>%</div>
					</div>
					<div class="spacer10"></div>
				</div>
				<div class="col-12">
					<div id="statistics_apexcalls"></div>
				</div>
			</div>
		</div>
		<div class="col-6">
			<div class="row panel_b">
				<div class="col">
					<h4 class="project-headline">HBG Statistics</h4>
				</div>
				<div class="col aligncenter">
					<p class="projectoverview-shortstats-h">Total HBG: <?php echo $a_hbg[0]['hbg_totals'] ?></p>
				</div>
				<div class="col coltoright aligncenter">
					<h4 class="project-headline-icon"><i class="ri-pie-chart-2-line"></i></h4>
				</div>
				<div class="row">
					<div class="col textaligncenter">
						<div class="projectoverview-statsblock-shortstats">
							<p class="projectoverview-shortstats-h">Done</p>
							<p class="projectlist-stats-s"><?php echo $a_hbg[0]['hbgdone'] ?></p>
						</div>
					</div>
					<div class="col textaligncenter">
						<div class="projectoverview-statsblock-shortstats">
							<p class="projectoverview-shortstats-h">Abbruch</p>
							<p class="projectlist-stats-s"><?php echo $a_hbg[0]['hbg_nocustomer'] + $a_hbg[0]['hbg_nobegeher'] + $a_hbg[0]['hbg_notpossible'] ?></p>
						</div>
					</div>
				</div>
				<div class="col-12">
					<?php
					$pb1 = round(($a_hbg[0]['hbgdone'] / $a_hbg[0]['hbg_totals']) * 100, 2);
					$pb1_int = round(($a_hbg[0]['hbgdone'] / $a_hbg[0]['hbg_totals']) * 100);
					$pb2 = round((($a_hbg[0]['hbg_nocustomer'] + $a_hbg[0]['hbg_nobegeher'] + $a_hbg[0]['hbg_notpossible']) / $a_hbg[0]['hbg_totals']) * 100, 2);
					$pb2_int = round((($a_hbg[0]['hbg_nocustomer'] + $a_hbg[0]['hbg_nobegeher'] + $a_hbg[0]['hbg_notpossible']) / $a_hbg[0]['hbg_totals']) * 100);
					?>
					<div class="miniBar minibar_fullheight textaligncenter" style="font-size:12px; color:#fff;">
						<div class="miniBarProgress" style="left: 0%; width: <?php echo $pb1_int ?>%; background-color: var(--statdone);"><?php echo $pb1 ?>%</div>
						<div class="miniBarProgress" style="left: <?php echo $pb1_int ?>%; width: <?php echo $pb2_int ?>%; background-color: var(--statstopped);"><?php echo $pb2 ?>%</div>
					</div>
					<div class="spacer10"></div>
				</div>
				<div class="row">
					<div class="col textaligncenter">
						<div class="projectoverview-statsblock-shortstats">
							<p class="projectoverview-shortstats-h">Kunde nicht da</p>
							<p class="projectlist-stats-s"><?php echo $a_hbg[0]['hbg_nocustomer'] ?></p>
						</div>
					</div>
					<div class="col textaligncenter">
						<div class="projectoverview-statsblock-shortstats">
							<p class="projectoverview-shortstats-h">Begeher nicht da</p>
							<p class="projectlist-stats-s"><?php echo $a_hbg[0]['hbg_nobegeher'] ?></p>
						</div>
					</div>
					<div class="col textaligncenter">
						<div class="projectoverview-statsblock-shortstats">
							<p class="projectoverview-shortstats-h">HBG nicht</p>
							<p class="projectlist-stats-s"><?php echo $a_hbg[0]['hbg_notpossible'] ?></p>
						</div>
					</div>
				</div>
				<div class="col-12">
					<?php
					$pb1 = round(($a_hbg[0]['hbg_nocustomer'] / $a_hbg[0]['hbg_totals']) * 100, 2);
					$pb1_int = round(($a_hbg[0]['hbg_nocustomer'] / $a_hbg[0]['hbg_totals']) * 100);
					$pb2 = round(($a_hbg[0]['hbg_nobegeher']  / $a_hbg[0]['hbg_totals']) * 100, 2);
					$pb2_int = round(($a_hbg[0]['hbg_nobegeher'] / $a_hbg[0]['hbg_totals']) * 100);
					$pb3 = round(($a_hbg[0]['hbg_notpossible']  / $a_hbg[0]['hbg_totals']) * 100, 2);
					$pb3_int = round(($a_hbg[0]['hbg_notpossible']  / $a_hbg[0]['hbg_totals']) * 100);
					?>
					<div class="miniBar minibar_fullheight textaligncenter" style="font-size:12px; color:#fff;">
						<div class="miniBarProgress" style="left: 0%; width: 20%; background-color: grey;"></div>
						<div class="miniBarProgress" style="left: 20%; width: 20%; background-color: grey;"></div>
						<div class="miniBarProgress" style="left: 40%; width: 20%; background-color: grey;"></div>
						<div class="miniBarProgress" style="left: 60%; width: 20%; background-color: grey;"></div>
						<div class="miniBarProgress" style="left: 80%; width: 20%; background-color: grey;"></div>
					</div>
					<div class="spacer10"></div>

					<div class="spacer10"></div>
				</div>
				<div class="row">
					<div class="col-12">
						<div id="statistics_apex_hbgocurr"></div>
					</div>
				</div>

				<div id='calltest'></div>
			</div>
		</div>


	</div>

	<script>
		var callactions = 0;
		var calls = 0;
		var missed = 0;
		var nohbg = 0;
		var hbgset = 0;
		var wiedervorlage = 0;
		var wrongperson = 0;
		var canceldcontract = 0;
		var wrongadress = 0;
		var numbernotset = 0;
		var wrongnumber = 0;
		var customreason = 0;
		var refused = 0;

		callactions = "<?php echo $a_calls[0]['callactions']; ?>";
		calls = "<?php echo $a_calls[0]['calls']; ?>";
		missed = "<?php echo $a_calls[0]['missed']; ?>";
		nohbg = "<?php echo $a_calls[0]['nohbg']; ?>";
		hbgset = "<?php echo $a_calls[0]['hbgset']; ?>";
		wiedervorlage = "<?php echo $a_calls[0]['wiedervorlage']; ?>";
		wrongperson = "<?php echo $a_calls[0]['wrongperson']; ?>";
		canceldcontract = "<?php echo $a_calls[0]['canceldcontract']; ?>";
		wrongadress = "<?php echo $a_calls[0]['wrongadress']; ?>";
		numbernotset = "<?php echo $a_calls[0]['numbernotset']; ?>";
		wrongnumber = "<?php echo $a_calls[0]['wrongnumber']; ?>";
		customreason = "<?php echo $a_calls[0]['customreason']; ?>";
		refused = "<?php echo $a_calls[0]['refused']; ?>";


		var options = {
			series: [{
				data: [customreason, refused, wrongnumber, wrongadress, canceldcontract]
			}],
			chart: {
				type: 'bar',
				height: 200
			},
			plotOptions: {
				bar: {
					borderRadius: 4,
					horizontal: true,
				}
			},
			dataLabels: {
				enabled: true,
			},
			xaxis: {
				categories: ['Besonderer Grund', 'Kunde verweigert HBG', 'Nummer nicht vergeben', 'Falsche Adresse', 'Kunde hat gekündigt'],
			}
		};

		var chart = new ApexCharts(document.querySelector("#statistics_apexcalls"), options);
		chart.render();





		callactions = "<?php echo $a_calls[0]['callactions']; ?>";
		calls = "<?php echo $a_calls[0]['calls']; ?>";
		missed = "<?php echo $a_calls[0]['missed']; ?>";
		nohbg = "<?php echo $a_calls[0]['nohbg']; ?>";
		hbgset = "<?php echo $a_calls[0]['hbgset']; ?>";
		wiedervorlage = "<?php echo $a_calls[0]['wiedervorlage']; ?>";
		wrongperson = "<?php echo $a_calls[0]['wrongperson']; ?>";
		canceldcontract = "<?php echo $a_calls[0]['canceldcontract']; ?>";
		wrongadress = "<?php echo $a_calls[0]['wrongadress']; ?>";
		numbernotset = "<?php echo $a_calls[0]['numbernotset']; ?>";
		wrongnumber = "<?php echo $a_calls[0]['wrongnumber']; ?>";
		customreason = "<?php echo $a_calls[0]['customreason']; ?>";
		refused = "<?php echo $a_calls[0]['refused']; ?>";


		var options = {
			series: [{
				data: [customreason, refused, wrongnumber, wrongadress, canceldcontract]
			}],
			chart: {
				type: 'bar',
				height: 200
			},
			plotOptions: {
				bar: {
					borderRadius: 4,
					horizontal: true,
				}
			},
			dataLabels: {
				enabled: true,
			},
			xaxis: {
				categories: ['Besonderer Grund', 'Kunde verweigert HBG', 'Nummer nicht vergeben', 'Falsche Adresse', 'Kunde hat gekündigt'],
			}
		};

		var chart = new ApexCharts(document.querySelector("#statistics_apex_hbgocurr"), options);
		chart.render();
	</script>


<?php
}



function load_tab_activity($city)
{

	$a_timeline = stats_timeline($city);
	$length = count($a_timeline);

	// array merge a_timeline and ticket_timeline
	//$a_timeline = array_merge($a_timeline, $ticket_timeline);

	//echo $length;
	//echo '<pre>';
	//print_r($a_timeline);
	//echo '</pre>';





	$length = count($a_timeline);

	ob_start();
?>
	<div class="row panel_w">
		<div class="row">
			<div class="col-12">
				<span class="tabname-title"><i class="ri-history-line"></i> Activity</span>
			</div>
		</div>
		<div class="spacer10"></div>

	</div>

	<div class="row panel_w">
		<div class="col-12 panel_b scrollwrapper projectoverview">
			<table id="projecttable_tickets" style="">
				<thead style="text-align:center;">
					<th></th>
					<th></th>
				</thead>
				<tbody>
					<?php
					for ($i = 0; $i < 100; $i++) {
					?>
						<tr class="projectoverviewtrtr">
							<td class="projectoverviewtrtd">
								<div class="trwrapper projectoverview">
									<p class="projectoverview timeline datetime"><?php echo $a_timeline[$i]['date'] . ' ' . substr($a_timeline[$i]['time'], 0, 5) ?></p>
									<p><?php echo $a_timeline[$i]['result'] ?></p>
									<p><?php echo $a_timeline[$i]['reason'] ?></p>
									<?php if (!hasPerm(2)) { ?>
										<p></p>
									<?php } else { ?>
										<p><?php echo $a_timeline[$i]['comment'] ?></p>
									<?php } ?>
									<?php if (!hasPerm(2)) { ?>
										<p></p>
									<?php } else { ?>
										<p class="projectoverview timeline user"><?php echo $a_timeline[$i]['user'] ?></p>
									<?php } ?>

								</div>
							</td>
							<td class="">
								<?php
								$datetime_string = $a_timeline[$i]['date'] . ' ' . substr($a_timeline[$i]['time'], 0, 5);

								$datetime = DateTime::createFromFormat('Y-m-d H:i', $datetime_string);
								$now = new DateTime();
								$interval = $now->diff($datetime);
								$output = '';
								if ($interval->d > 0) {
									$output .= $interval->format('%a days ago');
								} elseif ($interval->h > 0) {
									$output .= $interval->format('%h hours ago');
								} elseif ($interval->i > 0) {
									$output .= $interval->format('%i minutes ago');
								}
								?>
								<p style="margin:0;"><?php echo $output ?></p>
								<p style="margin:0;"><?php echo $a_timeline[$i]['homeid'] ?></p>
								<p style="margin:0;"><?php echo $a_timeline[$i]['street'] . ' ' . $a_timeline[$i]['streetnr'] . $a_timeline[$i]['streetnradd'] ?></p>
								<p style="margin:0;"><?php echo $a_timeline[$i]['dpnumber'] ?></p>
							</td>
						</tr>
					<?php
					}
					if ($length > 100) {
					?>
						<tr class="projectoverviewtrtr">
							<td style="text-align:center;"><span class="projectoverview loadmorebtn"><i class="ri-arrow-down-line"></i> Load more <i class="ri-arrow-down-line"></i></span></td>
						</tr>

					<?php
					}
					?>

				</tbody>

			</table>

		</div>
	</div>


<?php
}


function load_tab_ticket($city)
{
	if (!hasPerm(2)) {
		die();
	}
	$a_tickets = stats_ticketstats($city);



	ob_start();
?>
	<div class="row panel_w">
		<div class="row">
			<div class="col-12">
				<span class="tabname-title"><i class="ri-file-text-line"></i> Tickets</span>
			</div>
		</div>
		<div class="spacer10"></div>
		<div class="row">
			<div class="col-4">
				<div class="row ">
					<div class="col tickettab_w">
						<span class="tickettab counter" id="tickettab_open"><?php echo $a_tickets[0]['new'] ?></span>
						<span class="tickettab text" style="color:#03b0f5">Open</span>
					</div>
					<div class="col tickettab_w">
						<span class="tickettab counter" id="tickettab_pending"><?php echo $a_tickets[0]['pending'] ?></span>
						<span class="tickettab text" style="color:#cdb336">Pending</span>
					</div>
					<div class="col tickettab_w">
						<span class="tickettab counter" id="tickettab_done"><?php echo $a_tickets[0]['done'] ?></span>
						<span class="tickettab text" style="color:#80c55e">Done</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row panel_w">
		<div class="col-12 panel_b">
			<table id="projecttable_tickets" style="width:100%">
				<thead style="text-align:center;">
					<th>#</th>
					<th>Status</th>
					<th>Subject</th>
					<th>Prio</th>
					<th>HomeID</th>
					<th>Date</th>
					<th>Time</th>
					<th>User</th>
					<th>Last edit</th>
				</thead>
				<tbody><?php echo stats_tickettable($city) ?></tbody>
			</table>

		</div>
	</div>


<?php
}






function load_tab_overview($city)
{
	$conn = dbconnect();
	$output = ob_start();
	$query = "SELECT `date` FROM `scan4_hbg` INNER JOIN scan4_homes ON scan4_hbg.homeid=scan4_homes.homeid WHERE `date` != '' AND `date`IS NOT NULL AND scan4_homes.city = '" . $city . "' AND scan4_hbg.status = 'PLANNED' ORDER BY `scan4_hbg`.`date` ASC LIMIT 1";
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_row()) {
			$firsthbg = $row[0];
		}
	}
	$query = "SELECT `date` FROM `scan4_hbg` INNER JOIN scan4_homes ON scan4_hbg.homeid=scan4_homes.homeid WHERE `date` != '' AND `date`IS NOT NULL AND scan4_homes.city = '" . $city . "' AND scan4_hbg.status = 'PLANNED' ORDER BY `scan4_hbg`.`date` DESC LIMIT 1";
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_row()) {
			$lasthbg = $row[0];
		}
	}
	// close conn
	$conn->close();
	$customeradded = stats_customers_initial($city);
	$oldest_date = min(array_column($customeradded, 0));
	$oldest_date_count = array_count_values(array_column($customeradded, 0))[$oldest_date];

	//echo "The oldest date is $oldest_date and it appears $oldest_date_count times.";


	$a_timeline = stats_timeline($city);
	$counter = 0;
	global $currentuser;
	$a_calls = load_overview_a_callshorts($city, '', '', 'all', 'intern');
	$a_stats = stats_scan4homes($city);
	$a_projekt = stats_projektinfo($city);

	$progress_all = $a_stats[0]['total'] * 50;
	$progress_done = ($a_stats[0]['done'] + $a_stats[0]['doneexcel']) * 50;
	$progress_stopped = ($a_stats[0]['stopped']) * 50;
	$progress_call1 = ($a_stats[0]['open4calls'] * 1) * 10;
	$progress_call2 = ($a_stats[0]['open3calls'] * 2) * 10;
	$progress_call3 = ($a_stats[0]['open2calls'] * 3) * 10;
	$progress_call4 = ($a_stats[0]['open1calls'] * 4) * 10;
	$progress_call5 = ($a_stats[0]['open0calls'] * 5) * 10;
	$progress_summery = $progress_all - $progress_done - $progress_stopped - $progress_call1 - $progress_call2 - $progress_call3 - $progress_call4 - $progress_call5;
	$progress_cr = round(($progress_summery / $progress_all) * 100, 2);
	$progress_crr = (100 - $progress_cr);

	ob_start();
?>
	<div class="row panel_w hidden">
		<div class="col-6 panel_b">
			<div class="col-12">
				progress_all<?php echo $progress_all; ?> </br>
				progress_done<?php echo $progress_done; ?></br>
				progress_stopped<?php echo $progress_stopped; ?></br>
				progress_call1<?php echo $progress_call1; ?></br>
				progress_call2<?php echo $progress_call2; ?></br>
				progress_call3<?php echo $progress_call3; ?></br>
				progress_call4<?php echo $progress_call4; ?></br>
				progress_call5<?php echo $progress_call5; ?></br>
				progress_summery<?php echo $progress_summery; ?></br>
				progress_cr<?php echo $progress_cr; ?></br>
				progress_crr<?php echo $progress_crr; ?></br>
			</div>
		</div>
	</div>

	<div class="row panel_w">
		<div class="col-6 ">
			<div class="col-12 panel_b">
				<div clas="row">
					<div class="col-12">
						<p class="project-info">Project Progress <span id="s_project_progresspercent"><?php echo $progress_crr ?>%</span></p>

						<div class="progress progress-bar-small">
							<div id="bar_project_progress" class="progress-bar progress-bar-success no-percent-text not-dynamic" role="progressbar" aria-valuenow="<?php echo $progress_crr ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $progress_crr ?>%;" data-percent=" <?php echo $progress_crr ?>">
							</div>
						</div>
						<hr class="hrpanel-seperator">
					</div>
					<div class="col-12">
						<h4 class="project-headline">Overview</h4>
						<div class="row">
							<div class="col-6">
								<div class="projectoverview-statsblock">
									<p class="projectlist-stats-h">Projekt #</p>
									<p class="projectlist-stats-s"><?php echo $a_projekt[0]['city_id'] ?></p>
								</div>
								<div class="projectoverview-statsblock">
									<p class="projectlist-stats-h">Date Created</p>
									<p class="projectlist-stats-s"><?php echo $a_projekt[0]['date'] ?></p>
								</div>
								<div class="projectoverview-statsblock">
									<p class="projectlist-stats-h">Start Date</p>
									<p class="projectlist-stats-s">2023-01-01</p>
								</div>
								<div class="projectoverview-statsblock">
									<p class="projectlist-stats-h">First HBG</p>
									<p class="projectlist-stats-s"><?php echo $firsthbg ?></p>
								</div>
								<div class="projectoverview-statsblock">
									<p class="projectlist-stats-h">Last HBG</p>
									<p class="projectlist-stats-s"><?php echo $lasthbg ?></p>
								</div>
							</div>
							<div class="col-6">
								<div class="projectoverview-statsblock">
									<p class="projectlist-stats-h">Status</p>
									<?php if ($a_projekt[0]['status'] === 'aktiv') {
										echo '<p class="projectlist-stats-s"><span style="">aktiv<span></p>';
									} else {
										echo '<p class="projectlist-stats-s"><span style="">inaktiv<span></p>';
									} ?>
								</div>
								<div class="projectoverview-statsblock">
									<p class="projectlist-stats-h">Initial Customers Date</p>
									<p class="projectlist-stats-s"><?php echo $oldest_date ?></p>
								</div>
								<?php if (hasPerm(2)) { ?>
									<div class="projectoverview-statsblock">
										<p class="projectlist-stats-h">Initial Customers</p>
										<p class="projectlist-stats-s"><?php echo $oldest_date_count ?></p>
									</div>
								<?php	} ?>


								<div class="projectoverview-statsblock">
									<p class="projectlist-stats-h">Total Customers</p>
									<p class="projectlist-stats-s"><?php echo $a_stats[0]['total'] ?></p>
								</div>
								<div class="projectoverview-statsblock">
									<p class="projectlist-stats-h">Last Import</p>
									<p class="projectlist-stats-s"><?php echo $a_projekt[0]['lastimport'] ?></p>
								</div>

							</div>
						</div>
						<div style="padding-bottom:80px;"></div>
						<div class="row">
							<div class="col-6">
								<div class="projectoverview-statsblock">
									<?php if ($a_projekt[0]['carrier'] === 'DGF') $classname = 'carrier-dgf'; ?>
									<?php if ($a_projekt[0]['carrier'] === 'GVG') $classname = 'carrier-gvg'; ?>
									<?php if ($a_projekt[0]['carrier'] === 'UGG') $classname = 'carrier-ugg'; ?>
									<div id="carrierwrapper">
										<div id="carrier_logo" class="carrier_logo <?php echo $classname ?>"></div>
									</div>
								</div>
							</div>
							<div class="col-6">
								<div class="projectoverview-statsblock">
									<?php if ($a_projekt[0]['client'] === 'Insyte') $classname = 'client-insyte'; ?>
									<?php if ($a_projekt[0]['client'] === 'Moncobra') $classname = 'client-moncobra'; ?>
									<div id="carrierwrapper">
										<div id="client_logo" class="client_logo <?php echo $classname ?>"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>



		</div>
		<div class="col-6">
			<div class="row">
				<div style="max-width:49%;margin-right: 2%;" class="col-6 panel_b">
					<div class="row ">
						<div class="col">
							<h4 class="project-headline">Carrier</h4>
						</div>
						<div class="col coltoright aligncenter">
							<h4 class="project-headline-icon"><i class="ri-pie-chart-2-line"></i></h4>
						</div>
						<div class="row">
							<div class="col textaligncenter">
								<div class="projectoverview-statsblock-shortstats">
									<p class="projectoverview-shortstats-h">Open</p>
									<p class="projectlist-stats-s"><?php echo $a_stats[0]['carrieropen'] ?></p>
								</div>
							</div>
							<div class="col textaligncenter">
								<div class="projectoverview-statsblock-shortstats">
									<p class="projectoverview-shortstats-h">Done</p>
									<p class="projectlist-stats-s"><?php echo $a_stats[0]['carrierdone'] ?></p>
								</div>
							</div>
							<div class="col textaligncenter">
								<div class="projectoverview-statsblock-shortstats">
									<p class="projectoverview-shortstats-h">Stopped</p>
									<p class="projectlist-stats-s"><?php echo $a_stats[0]['carrierstopped'] ?></p>
								</div>
							</div>
						</div>
						<div class="col-12">
							<?php
							$pb1 = round(($a_stats[0]['carrieropen'] / $a_stats[0]['total']) * 100, 2);
							$pb1_int = round(($a_stats[0]['carrieropen'] / $a_stats[0]['total']) * 100);
							$pb2 = round(($a_stats[0]['carrierdone'] / $a_stats[0]['total']) * 100, 2);
							$pb2_int = round(($a_stats[0]['carrierdone'] / $a_stats[0]['total']) * 100);
							$pb3 = round(($a_stats[0]['carrierstopped'] / $a_stats[0]['total']) * 100, 2);
							$pb3_int = round(($a_stats[0]['carrierstopped'] / $a_stats[0]['total']) * 100);
							?>
							<div class="miniBar minibar_fullheight textaligncenter" style="font-size:12px; color:#fff;">
								<div class="miniBarProgress" style="left: 0%; width: <?php echo $pb1_int ?>%; background-color: var(--statopen);"><?php echo $pb1 ?>%</div>
								<div class="miniBarProgress" style="left: <?php echo $pb1_int ?>%; width: <?php echo $pb2_int ?>%; background-color: var(--statdone);"><?php echo $pb2 ?>%</div>
								<div class="miniBarProgress" style="left: <?php echo $pb1_int + $pb2_int ?>%; width: <?php echo $pb3_int ?>%; background-color: var(--statstopped);"><?php echo $pb3 ?>%</div>
							</div>
							<div class="spacer10"></div>
						</div>
					</div>
				</div>
				<div style="max-width:49%" class="col-6 panel_b">
					<div class="row ">
						<div class="col">
							<div>
								<span class="h5 project-headline">Scan4</span>
								<div class="cstooltip"><i class="ri-information-fill"></i>
									<span class="cstooltiptext ">All customers who are not 'Done' and not 'Stopped' are counted as open in this short view</span>
								</div>
							</div>

						</div>
						<div class="col coltoright aligncenter">
							<h4 class="project-headline-icon"><i class="ri-pie-chart-2-line"></i></h4>
						</div>
						<div class="row">
							<div class="col textaligncenter">
								<div class="projectoverview-statsblock-shortstats">
									<p class="projectoverview-shortstats-h">Open</p>
									<p class="projectlist-stats-s"><?php echo $a_stats[0]['scan4_sumopen']  ?></p>
								</div>
							</div>
							<div class="col textaligncenter">
								<div class="projectoverview-statsblock-shortstats">
									<p class="projectoverview-shortstats-h">Done</p>
									<p class="projectlist-stats-s"><?php echo $a_stats[0]['done'] ?></p>
								</div>
							</div>
							<div class="col textaligncenter">
								<div class="projectoverview-statsblock-shortstats">
									<p class="projectoverview-shortstats-h">Stopped</p>
									<p class="projectlist-stats-s"><?php echo $a_stats[0]['stopped'] ?></p>
								</div>
							</div>

						</div>
						<div class="col-12">
							<?php
							$pb1 = round(($a_stats[0]['open'] / $a_stats[0]['total']) * 100, 2);
							$pb1_int = round(($a_stats[0]['open'] / $a_stats[0]['total']) * 100);
							$pb2 = round(($a_stats[0]['done'] / $a_stats[0]['total']) * 100, 2);
							$pb2_int = round(($a_stats[0]['done'] / $a_stats[0]['total']) * 100);
							$pb3 = round(($a_stats[0]['stopped'] / $a_stats[0]['total']) * 100, 2);
							$pb3_int = round(($a_stats[0]['stopped'] / $a_stats[0]['total']) * 100);
							?>
							<div class="miniBar minibar_fullheight textaligncenter" style="font-size:12px; color:#fff;">
								<div class="miniBarProgress" style="left: 0%; width: <?php echo $pb1_int ?>%; background-color: var(--statopen);"><?php echo $pb1 ?>%</div>
								<div class="miniBarProgress" style="left: <?php echo $pb1_int ?>%; width: <?php echo $pb2_int ?>%; background-color: var(--statdone);"><?php echo $pb2 ?>%</div>
								<div class="miniBarProgress" style="left: <?php echo $pb1_int + $pb2_int ?>%; width: <?php echo $pb3_int ?>%; background-color: var(--statstopped);"><?php echo $pb3 ?>%</div>
							</div>
							<div class="spacer10"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-12 panel_b">
				<div class="row ">
					<div class="col">
						<h4 class="project-headline">Scan4 Statistics</h4>
					</div>
					<div class="col coltoright aligncenter">
						<h4 class="project-headline-icon"><i class="ri-pie-chart-2-line"></i></h4>
					</div>
					<div class="row">
						<div class="col-12">
							<div id="overview_apex_sc4stats"></div>
						</div>
					</div>
					<div class="col-12">

					</div>
					<div class="row">
						<div class="col-12">
							<div id="overview_apex_sc4stats_misc"></div>
						</div>
						<div class="spacer10"></div>
					</div>

				</div>
			</div>




		</div>
	</div>
	<?php
	$callslfettotal = ($a_stats[0]['open4calls'] * 1) + ($a_stats[0]['open3calls'] * 2) + ($a_stats[0]['open2calls'] * 3) + ($a_stats[0]['open1calls'] * 4) + ($a_stats[0]['open0calls'] * 5);
	?>
	<div class="row panel_w">
		<div class="col-6">
			<div class="row panel_b" style="height: 35vh;">
				<div class="col">
					<h4 class="project-headline">Calls</h4>
				</div>
				<div class="col aligncenter">

				</div>
				<div class="col coltoright aligncenter">
					<h4 class="project-headline-icon"><i class="ri-phone-line"></i></h4>
				</div>
				<div class="row">
					<div class="col-6">
						<div>
							<span class="projectoverview-shortstats-h">Total Calls left: <?php echo $callslfettotal ?></span>
							<div class="cstooltip"><i class="ri-information-fill"></i>
								<span class="cstooltiptext ">This chart represents the executed calls for the Scan4 open Home IDs.</span>
							</div>
						</div>
						<div id="overview_apex_opencalls"></div>
					</div>

					<div class="col-6">

					</div>
				</div>
			</div>
		</div>

		<div class="col-6">
			<div class="row">
				<div class="row panel_b" style="max-height: 35vh;overflow-y:hidden;">
					<div class="col">
						<h4 class="project-headline">Activity</h4>
					</div>
					<div class="col aligncenter">
						<p class="projectoverview-shortstats-h"></p>
					</div>
					<div class="col coltoright aligncenter">
						<h4 class="project-headline-icon"><i class="ri-file-list-line"></i></h4>
					</div>
					<div class="col-12">
						<div id="overview_apex_opencalls"></div>
					</div>

					<div class="col-12  scrollwrapper scrollwrapershort projectoverview" style="max-height: 31vh;">

						<table id="projecttable_tickets" style="">
							<thead style="text-align:center;">
								<th></th>
								<th></th>
							</thead>
							<tbody>
								<?php
								for ($i = 0; $i < 10; $i++) {
								?>
									<tr class="projectoverviewtrtr">
										<td class="projectoverviewtrtd">
											<div class="trwrapper projectoverview">
												<p class="projectoverview timeline datetime"><?php echo $a_timeline[$i]['date'] . ' ' . substr($a_timeline[$i]['time'], 0, 5) . 'Uhr'; ?></p>
												<p><?php echo $a_timeline[$i]['result'] ?></p>
												<p><?php echo $a_timeline[$i]['reason'] ?></p>
												<p><?php echo $a_timeline[$i]['comment'] ?></p>
												<p class="projectoverview timeline user"><?php echo $a_timeline[$i]['user'] ?></p>
											</div>
										</td>
										<td class="">
											<?php
											$datetime_string = $a_timeline[$i]['date'] . ' ' . substr($a_timeline[$i]['time'], 0, 5);

											$datetime = DateTime::createFromFormat('Y-m-d H:i', $datetime_string);
											$now = new DateTime();
											$interval = $now->diff($datetime);
											$output = '';
											if ($interval->d > 0) {
												$output .= $interval->format('%a days ago');
											} elseif ($interval->h > 0) {
												$output .= $interval->format('%h hours ago');
											} elseif ($interval->i > 0) {
												$output .= $interval->format('%i minutes ago');
											}
											?>
											<p style="margin:0;"><?php echo $output ?></p>
											<p style="margin:0;"><?php echo $a_timeline[$i]['homeid'] ?></p>
											<p style="margin:0;"><?php echo $a_timeline[$i]['street'] . ' ' . $a_timeline[$i]['streetnr'] . $a_timeline[$i]['streetnradd'] ?></p>
											<p style="margin:0;"><?php echo $a_timeline[$i]['dpnumber'] ?></p>
										</td>
									</tr>
								<?php
								}
								?>

							</tbody>

						</table>
					</div>
				</div>
			</div>
		</div>


	</div>

	<?php


	?>
	<script>
		var v0 = 0;
		var v1 = 0;
		var v2 = 0;
		var v3 = 0;
		var v4 = 0;
		var v5 = 0;

		v0 = parseInt("<?php echo $a_stats[0]['open4calls']; ?>");
		v1 = parseInt("<?php echo $a_stats[0]['open3calls']; ?>");
		v2 = parseInt("<?php echo $a_stats[0]['open2calls']; ?>");
		v3 = parseInt("<?php echo $a_stats[0]['open1calls']; ?>");
		v4 = parseInt("<?php echo $a_stats[0]['open0calls']; ?>");

		var options = {
			series: [v0, v1, v2, v3, v4],
			chart: {
				width: 380,
				type: 'pie',
			},
			labels: ['4 calls', '3 calls', '2calls', '1call', '0calls'],

			colors: ['#9e3f71', '#9a4175', '#5d58ac', '#2b6bd9', '#007bff'],
			responsive: [{
				breakpoint: 480,
				options: {
					chart: {
						width: 200
					},
					legend: {
						position: 'bottom'
					}
				}
			}]
		};

		var chart = new ApexCharts(document.querySelector("#overview_apex_opencalls"), options);
		chart.render();
		chart.addEventListener('dataPointSelection', function(event, chartContext, config) {
			// get the selected data point
			var selectedDataPoint = config.dataPointIndex;

			// log the selected data point to the console
			console.log("Selected data point: " + selectedDataPoint);
		});



		v0 = "<?php echo $a_stats[0]['open']; ?>";
		v1 = "<?php echo $a_stats[0]['planned']; ?>";
		v2 = "<?php echo $a_stats[0]['done']; ?>";
		v3 = "<?php echo $a_stats[0]['donecloud']; ?>";
		v4 = "<?php echo $a_stats[0]['stopped']; ?>";
		v5 = parseInt("<?php echo $a_stats[0]['pending']; ?>");


		var options = {
			series: [{
				name: 'Series',
				data: [v0, v1, v2, v3, v4, v5]
			}],

			chart: {
				type: 'bar',
				height: 200
			},
			plotOptions: {
				bar: {
					distributed: true,
					borderRadius: 0,
					horizontal: true,
				}
			},
			colors: ['#007bff', '#c1a748', '#69b761', '#69b761', '#d54c4c', '#d0925d'],
			tooltip: {
				enabled: true,
			},
			dataLabels: {
				enabled: true,
			},
			xaxis: {
				categories: ['open', 'planned', 'done', 'done cloud', 'stopped', 'pending'],
			}
		};

		var chart = new ApexCharts(document.querySelector("#overview_apex_sc4stats"), options);
		chart.render();









		v0 = parseInt("<?php echo $a_stats[0]['post'] ?>");
		v1 = parseInt("<?php echo $a_stats[0]['mail']; ?>");
		v2 = parseInt("<?php echo $a_stats[0]['failed']; ?>");
		v3 = parseInt("<?php echo $a_stats[0]['nophone']; ?>");


		var options = {
			series: [{
				name: 'Series',
				data: [v0, v1, v2, v3]
			}],
			colors: ['#9d9d9d'],
			chart: {
				type: 'bar',
				height: 200
			},
			plotOptions: {
				bar: {
					distributed: true,
					borderRadius: 0,
					horizontal: true,
				}
			},
			tooltip: {
				enabled: true,
			},
			dataLabels: {
				enabled: true,
			},
			xaxis: {
				categories: ['Post', 'Mail', 'Failed', 'NoPhone'],
			}
		};

		var chart = new ApexCharts(document.querySelector("#overview_apex_sc4stats_misc"), options);
		chart.render();
	</script>
	<?php
}

function stats_projektinfo($city)
{
	$conn = dbconnect();
	$query = "SELECT * FROM `scan4_citylist` WHERE city LIKE '" . $city . "'";
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_assoc()) {
			$array[] = $row;
		}
		$result->free_result();
	}
	$conn->close();

	return $array;
}

function stats_scan4homes($city)
{
	$conn = dbconnect();
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['total'] = $row[0];
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND (hbg_status = 'OPEN' OR hbg_status = 'PLANNED')";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['carrieropen'] = $row[0];
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND hbg_status = 'DONE'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['carrierdone'] = $row[0];
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND hbg_status = 'STOPPED'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['carrierstopped'] = $row[0];
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND scan4_status = 'OPEN'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['open'] = $row[0];
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND scan4_status = 'OVERDUE'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['overdue'] = $row[0];
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND scan4_status = 'PLANNED'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['planned'] = $row[0];
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND scan4_status = 'DONE CLOUD'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['donecloud'] = $row[0];
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND scan4_status = 'DONE EXCEL'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['doneexcel'] = $row[0];
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND scan4_status = 'DONE'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['done'] = $row[0];
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND scan4_status = 'STOPPED'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['stopped'] = $row[0];
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND scan4_status = 'PENDING'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['pending'] = $row[0];
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND (anruf5 != '' OR anruf5 IS NOT NULL)";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['5calls'] = $row[0];
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND scan4_status != 'DONE' AND scan4_status != 'STOPPED' ";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['scan4_sumopen'] = $row[0];

	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND (anruf5 = '' OR anruf5 IS NULL) AND (anruf4 != '' OR anruf4 IS NOT NULL)";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['4calls'] = $row[0];
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND (anruf5 = '' OR anruf5 IS NULL) AND (anruf4 = '' OR anruf4 IS NULL) AND (anruf3 != '' OR anruf3 IS NOT NULL)";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['3calls'] = $row[0];
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND (anruf5 = '' OR anruf5 IS NULL) AND (anruf4 = '' OR anruf4 IS NULL) AND (anruf3 = '' OR anruf3 IS NULL) AND (anruf2 != '' OR anruf2 IS NOT NULL)";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['2calls'] = $row[0];
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND (anruf5 = '' OR anruf5 IS NULL) AND (anruf4 = '' OR anruf4 IS NULL) AND (anruf3 = '' OR anruf3 IS NULL) AND (anruf2 = '' OR anruf2 IS NULL) AND (anruf1 != '' OR anruf1 IS NOT NULL)";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['1calls'] = $row[0];
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND (anruf5 = '' OR anruf5 IS NULL) AND (anruf4 = '' OR anruf4 IS NULL) AND (anruf3 = '' OR anruf3 IS NULL) AND (anruf2 = '' OR anruf2 IS NULL) AND (anruf1 = '' OR anruf1 IS NULL)";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['0calls'] = $row[0];
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND scan4_status = 'OPEN' AND (anruf5 = '' OR anruf5 IS NULL) AND (anruf4 != '' OR anruf4 IS NOT NULL)";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['open4calls'] = $row[0];
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND scan4_status = 'OPEN' AND (anruf5 = '' OR anruf5 IS NULL) AND (anruf4 = '' OR anruf4 IS NULL) AND (anruf3 != '' OR anruf3 IS NOT NULL)";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['open3calls'] = $row[0];
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND scan4_status = 'OPEN' AND (anruf5 = '' OR anruf5 IS NULL) AND (anruf4 = '' OR anruf4 IS NULL) AND (anruf3 = '' OR anruf3 IS NULL) AND (anruf2 != '' OR anruf2 IS NOT NULL)";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['open2calls'] = $row[0];
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND scan4_status = 'OPEN' AND (anruf5 = '' OR anruf5 IS NULL) AND (anruf4 = '' OR anruf4 IS NULL) AND (anruf3 = '' OR anruf3 IS NULL) AND (anruf2 = '' OR anruf2 IS NULL) AND (anruf1 != '' OR anruf1 IS NOT NULL)";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['open1calls'] = $row[0];
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND scan4_status = 'OPEN' AND (anruf5 = '' OR anruf5 IS NULL) AND (anruf4 = '' OR anruf4 IS NULL) AND (anruf3 = '' OR anruf3 IS NULL) AND (anruf2 = '' OR anruf2 IS NULL) AND (anruf1 = '' OR anruf1 IS NULL)";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['open0calls'] = $row[0];

	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND (briefkasten != '' OR briefkasten IS NOT NULL)";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['post'] = $row[0];
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND (emailsend != '' OR emailsend IS NOT NULL)";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['mail'] = $row[0];
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND (anruf5 != '' OR anruf5 IS NOT NULL) AND (emailsend != '' OR emailsend IS NOT NULL) AND (briefkasten != '' OR briefkasten IS NOT NULL)";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['failed'] = $row[0];
	$query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND (phone1 = '' OR phone1 IS NULL) AND (phone2 = '' OR phone2 IS NULL) AND (phone3 = '' OR phone3 IS NULL) AND (phone4 = '' OR phone4 IS NULL)";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['nophone'] = $row[0];


	$conn->close();
	return $array;
}



function stats_tickettable($city)
{

	$conn = dbconnect();
	$output = ob_start();
	$query = "SELECT * FROM `scan4_tickets` WHERE 1 ORDER BY `scan4_tickets`.`id` DESC";
	$query = "SELECT * FROM `scan4_tickets` INNER JOIN scan4_homes ON scan4_tickets.homeid=scan4_homes.homeid WHERE scan4_homes.city = '" . $city . "' ORDER BY `scan4_tickets`.`id` DESC;";
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_assoc()) {
			if ($row['status'] === "new") $css_status = "statusoutline green";
			if ($row['status'] === "pending") $css_status = "statusoutline yellow";

			if ($row['priority'] === "1") {
				$css_prio = "ticketpriolvl red";
				$css_icon = '<i class="ri-24-hours-line"></i>';
			}
			if ($row['priority'] === "2") {
				$css_prio = "ticketpriolvl orange";
				$css_icon = '<i class="ri-fire-line"></i>';
			}
			if ($row['priority'] === "3") {
				$css_prio = "ticketpriolvl blue";
				$css_icon = '<i class="ri-check-double-line"></i>';
			}
			if ($row['carrier'] === "DGF") {
				$carrier = '<img src="view/images/logo_small_carrier_dgf.jpg"></img>';
			} else if ($row['carrier'] === "UGG") {
				$carrier = '<img src="view/images/logo_small_carrier_ugg.jpg"></img>';
			} else if ($row['carrier'] === "GVG") {
				$carrier = '<img src="view/images/logo_small_carrier_gvg.jpg"></img>';
			}
	?>

			<tr class="<?php echo $row['carrier'] . ' ' . $row['client'] . ' ' . $row['status'] ?>">
				<td style="text-align:center;"><?php echo $row['id'] ?></td>
				<td class="<?php echo $css_status ?>"><span><?php echo $row['status'] ?></span></td>
				<td><?php echo $row['object_title'] ?></td>
				<td class="<?php echo $css_prio ?>"><span><?php echo $row['priority'] . '&nbsp;' . $css_icon ?></span></td>
				<td><?php echo $row['homeid'] ?></td>
				<td><?php echo $row['date'] ?></td>
				<td><?php echo mb_substr($row['time'], 0, -3) ?></td>
				<td><?php echo $row['user'] ?></td>
				<td><?php echo mb_substr($row['last_edit'], 0, -3) ?></td>
			</tr>

<?php

		}
		$result->free_result();
	}
	$conn->close();

	//echo $output;
	//echo json_encode($entry);
}



function stats_ticketstats($city)
{

	$conn = dbconnect();
	$output = ob_start();
	$query = "SELECT COUNT(scan4_homes.homeid) FROM `scan4_tickets` INNER JOIN scan4_homes ON scan4_tickets.homeid=scan4_homes.homeid WHERE scan4_homes.city = '" . $city . "' AND scan4_tickets.status = 'new'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['new'] = $row[0];
	$query = "SELECT COUNT(scan4_homes.homeid) FROM `scan4_tickets` INNER JOIN scan4_homes ON scan4_tickets.homeid=scan4_homes.homeid WHERE scan4_homes.city = '" . $city . "' AND scan4_tickets.status = 'pending'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['pending'] = $row[0];
	$query = "SELECT COUNT(scan4_homes.homeid) FROM `scan4_tickets` INNER JOIN scan4_homes ON scan4_tickets.homeid=scan4_homes.homeid WHERE scan4_homes.city = '" . $city . "' AND scan4_tickets.status = 'done'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['done'] = $row[0];
	$conn->close();
	return $array;
	//echo $output;
	//echo json_encode($entry);
}


function stats_hbg($city)
{

	$conn = dbconnect();
	$output = ob_start();

	$query = "SELECT COUNT(scan4_homes.homeid) FROM `scan4_hbg` INNER JOIN scan4_homes ON scan4_hbg.homeid=scan4_homes.homeid WHERE scan4_homes.city = '" . $city . "' ";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['all'] = $row[0];

	$query = "SELECT COUNT(scan4_homes.homeid) FROM `scan4_hbg` INNER JOIN scan4_homes ON scan4_hbg.homeid=scan4_homes.homeid WHERE scan4_homes.city = '" . $city . "' AND scan4_hbg.status = 'PLANNED'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['planned'] = $row[0];

	$query = "SELECT COUNT(scan4_homes.homeid) FROM `scan4_hbg` INNER JOIN scan4_homes ON scan4_hbg.homeid=scan4_homes.homeid WHERE scan4_homes.city = '" . $city . "' AND scan4_hbg.status = 'MOVED'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['moved'] = $row[0];

	$query = "SELECT COUNT(scan4_homes.homeid) FROM `scan4_hbg` INNER JOIN scan4_homes ON scan4_hbg.homeid=scan4_homes.homeid WHERE scan4_homes.city = '" . $city . "' AND scan4_hbg.status = 'STORNO'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['storno'] = $row[0];

	$query = "SELECT COUNT(scan4_homes.homeid) FROM `scan4_hbg` INNER JOIN scan4_homes ON scan4_hbg.homeid=scan4_homes.homeid WHERE scan4_homes.city = '" . $city . "' AND scan4_hbg.appt_status = 'done'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['hbgdone'] = $row[0];

	$query = "SELECT COUNT(scan4_homes.homeid) FROM `scan4_hbg` INNER JOIN scan4_homes ON scan4_hbg.homeid=scan4_homes.homeid WHERE scan4_homes.city = '" . $city . "' AND scan4_hbg.appt_status = 'Kunde war nicht da'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['hbg_nocustomer'] = $row[0];

	$query = "SELECT COUNT(scan4_homes.homeid) FROM `scan4_hbg` INNER JOIN scan4_homes ON scan4_hbg.homeid=scan4_homes.homeid WHERE scan4_homes.city = '" . $city . "' AND scan4_hbg.appt_status = 'Ich war nicht da'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['hbg_nobegeher'] = $row[0];

	$query = "SELECT COUNT(scan4_homes.homeid) FROM `scan4_hbg` INNER JOIN scan4_homes ON scan4_hbg.homeid=scan4_homes.homeid WHERE scan4_homes.city = '" . $city . "' AND scan4_hbg.appt_status = 'HBG nicht durchführbar'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	$array['0']['hbg_notpossible'] = $row[0];

	$array['0']['hbg_totals'] = $array['0']['hbgdone'] + $array['0']['hbg_notpossible'] + $array['0']['hbg_nobegeher'] + $array['0']['hbg_nocustomer'];

	$conn->close();
	return $array;
	//echo $output;
	//echo json_encode($entry);
}

function stats_filelist($city)
{
	$conn = dbconnect();
	$query = "SELECT * FROM `scan4_hbg` INNER JOIN scan4_homes ON scan4_hbg.homeid=scan4_homes.homeid WHERE scan4_homes.city = '" . $city . "' AND scan4_hbg.appt_file IS NOT NULL";
	$result = $conn->query($query);
	while ($row = $result->fetch_assoc()) {
		$array[] = $row;
	}


	// close conn
	$conn->close();
	return $array;
}


function stats_timeline($city)
{
	$conn = dbconnect();
	//$a_timeline[] = array();
	$query = "SELECT * FROM `scan4_calls` INNER JOIN scan4_homes ON scan4_calls.homeid=scan4_homes.homeid WHERE scan4_homes.city = '" . $city . "' ORDER BY `scan4_calls`.`id` DESC;";

	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_assoc()) {
			$a_timeline[] = array(
				"date" => $row['call_date'], "time" => $row['call_time'], "user" => $row['call_user'], 'source' => 'calls', "result" => $row['result'], 'reason' => '', "comment" => $row['comment'], "uid" => $row['callid'],
				"homeid" => $row['homeid'], "street" => $row['street'], "streetnr" => $row['streetnumber'], "streetnradd" => $row['streetnumberadd'], "dpnumber" => $row['dpnumber']
			);
		}
		$result->free_result();
	}


	$query = "SELECT * FROM `scan4_hbgcheck` INNER JOIN scan4_homes ON scan4_hbgcheck.homeid=scan4_homes.homeid WHERE scan4_homes.city = '" . $city . "' ORDER BY `scan4_hbgcheck`.`id` DESC;";
	if ($result = $conn->query($query)) {
		while ($row = mysqli_fetch_assoc($result)) {
			$s = explode(' ', $row['datetime']);
			$date = $s[0];
			$time = $s[1];
			$a_timeline[] = array(
				"date" => $date, "time" => $time, "user" => $row['user'], 'source' => 'hbgcheck', "result" => $row['status'], 'reason' => $row['reason'],  "comment" => $row['comment'], "uid" => $row['ident'],
				"homeid" => $row['homeid'], "street" => $row['street'], "streetnr" => $row['streetnumber'], "streetnradd" => $row['streetnumberadd'], "dpnumber" => $row['dpnumber']
			);
		}
		$result->free_result();
	}


	$query = "SELECT * FROM `scan4_tickets` INNER JOIN scan4_homes ON scan4_tickets.homeid=scan4_homes.homeid WHERE scan4_homes.city = '" . $city . "' ORDER BY `scan4_tickets`.`id` DESC;";
	if ($result = $conn->query($query)) {
		while ($row = mysqli_fetch_assoc($result)) {
			//$a_timeline[] = array("date" => $row['date'], "time" => $row['time'], "user" => $row['user'], 'source' => 'ticket', "result" => 'ticket erstellt', 'reason' => $row['object_title'],  "comment" => $row['object_content']);
			$a_timeline[] = array(
				"date" => $row['date'], "time" => $row['time'], "user" => $row['user'], 'source' => 'ticket', "result" => 'ticket erstellt', 'reason' => $row['object_title'],  "comment" => $row['object_content'], "uid" => "",
				"homeid" => $row['homeid'], "street" => $row['street'], "streetnr" => $row['streetnumber'], "streetnradd" => $row['streetnumberadd'], "dpnumber" => $row['dpnumber']
			);
			if ($row['status'] !== 'closed') {
				$ticket = $row;
			}
		}
		$result->free_result();
	}
	$conn->close();

	arsort($a_timeline);
	$a_timeline = array_values($a_timeline);

	return $a_timeline;
}



function stats_customers_initial($city)
{
	$conn = dbconnect();
	$output = ob_start();
	$query = "SELECT scan4_added FROM `scan4_homes` WHERE scan4_homes.city = '" . $city . "' AND scan4_added != '1970-01-01'";
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_row()) {
			$array[] = $row;
		}
	}
	// close conn
	$conn->close();
	return $array;
}
