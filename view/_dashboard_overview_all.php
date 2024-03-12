<?php



require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}
if (!(hasPerm([2]) || hasPerm([3]) || hasPerm([13]))) {
	die();
}

$perm_telefonist = fetchPermissionUsers(5); // 5 = Telefonist

/*
echo '<pre>';
echo print_r($perm_telefonist);
echo '</pre>';

*/


include_once $_SERVER['DOCUMENT_ROOT'] . '/view/includes/functions.php';

use scan4\StatisticsHBG;

for ($i = 0; $i < count($perm_telefonist); $i++) {
	//echo echousername($perm_telefonist[$i]->user_id);
	$data = fetchUserDetails(null, null, $perm_telefonist[$i]->user_id);
	//echo $data->username;
	//echo $data->profile_pic;
	$userlist[] =  array('username' => $data->username, 'pic' => $data->profile_pic);
}
sort($userlist);
/*
echo '<pre>';
echo print_r($userlist);
echo '</pre>';

*/

?>
<script type="text/javascript" src="view/includes/js/app_dashboard_overview_all.js?v=1.7"></script>
<div class="body-content-app" id="body-content-app">
	<div class="dashboard-wrapper">
		<div class="row">
			<div class="col-6 ">
				<div class="col vcard">
					<div class="row">
						<div class="col">
							<span style="font-size:18px"><i style="position: relative;top: 3px;font-size: 22px;" class="ri-community-line"></i>&nbsp;Hausbegehungen</span>
						</div>
						<div class="col">

						</div>
					</div>
					<div class="row flexcentered">
						<div class="col hidden">
							<span id="hbg_o_done" class="mystat stattotal mystat_done"><i class="ri-checkbox-circle-line"></i></span>
							<span id="hbg_o_abbr" class="mystat stattotal mystat_canceld"><i class="ri-close-circle-line"></i></span>
							<span id="hbg_o_open" class="mystat stattotal mystat_open"><i class="ri-question-line"></i></span>
							<span id="hbg_o_sum" class="mystat stattotal mystat_total"><i class="ri-hashtag"></i></span>
							<span id="hbg_o_checked" class="mystat stattotal mystat_checked"><i class="ri-check-double-line"></i></span>
						</div>
					</div>
					<div class="row">
						<div class="col-12" style="user-select: none;">
							<?php
							$stats = new StatisticsHBG();
							$date = date('Y-m-d'); // Replace with your desired date
							$apptointments = $stats->appointments($date);
							$appt_status = $stats->appt_status($date);
							$reviewed = $stats->reviewed($date);
							$merged = array();

							foreach ($apptointments as $person => $appts) {
								$status = isset($appt_status[$person]) ? $appt_status[$person] : array();
								$review = isset($reviewed[$person]) ? $reviewed[$person] : array();
								$merged[$person] = array_merge($appts, $status, $review);
							}
							?>
							<table id="hbg_users" class="table" style="user-select: text;">
								<thead>
									<tr>
										<th>Name</th>
										<th><span id="hbg_o_done" class="mystat stattotal mystat_done"><i class="ri-checkbox-circle-line"></i></span></th>
										<th><span id="hbg_o_abbr" class="mystat stattotal mystat_canceld"><i class="ri-close-circle-line"></i></span></th>
										<th><span id="hbg_o_open" class="mystat stattotal mystat_open"><i class="ri-question-line"></i></span></th>
										<th><span id="hbg_o_sum" class="mystat stattotal mystat_total"><i class="ri-hashtag"></i></span></th>
										<th><span id="hbg_o_checked" class="mystat stattotal mystat_checked"><i class="ri-check-double-line"></i></span></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($merged as $person => $count) {
										if ($person == 'TOTAL') {
											continue;
										}
									?>
										<tr>
											<td><?php echo $person ?></td>
											<td><?php echo $count['done'] ?? 0 ?></td>
											<td><?php echo $count['abbruch'] ?? 0 ?></td>
											<td><?php echo $count['NULL'] ?? 0 ?></td>
											<td><?php echo $count['PLANNED'] ?? 0 ?></td>
											<td><?php echo $count['reviewed'] ?? 0 ?></td>
										</tr>
									<?php } ?>
									<script>
										$(document).ready(function() {
											$('#hbg_users').DataTable({
												ordering: true,
												select: true,
												"paging": false,
												"lengthChange": false,
												"searching": false,
												"info": false,
											});
										});
									</script>

								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<div class="col-3">
				<div class="d_card">
					<div class="d_card_inner">
						<div id="callshorts" class="d_card_titlewrapper">
							<div id="callshorts_w"><span id="callshorts_year" class="tabbar">Jahr</span><span id="callshorts_<?php echo date('m') ?>" class="tabbar">Monat</span><span id="callshorts_last" class="tabbar">KW <?php echo date("W", strtotime("-1 week")); ?></span><span id="callshorts_this" class="tabbar activetab">KW <?php echo date('W') ?></span></div>
							<div id="callshorts_d"><span id="callshorts_alldays" class="tabbar days activetab">All</span><span id="callshorts_mo" class="tabbar days">Mo</span><span id="callshorts_di" class="tabbar days">Di</span><span id="callshorts_mi" class="tabbar days">Mi</span><span id="callshorts_do" class="tabbar days">Do</span><span id="callshorts_fr" class="tabbar days">Fr</span></div>
						</div>

						<div class="d_card_body">
							<div class="selectionwrapper">
								<select style="margin-bottom:4px;" class="newselectbox form-control" name="callshorts_user" id="callshorts_user">
									<option value="all" selected>All</option>
									<?php for ($i = 0; $i < count($userlist); $i++) {
										echo '<option value="' . $userlist[$i]['username'] . '">' . $userlist[$i]['username'] . '</option>';
									?>
									<?php } ?>
								</select>
								<input id="callshorts_city" placeholder="Project" class="newselectbox form-control" autocomplete="off"></input>
								<div id="callshorts_city_results" class="hidden"></div>
							</div>
							<table>
								<tbody>
									<tr>
										<td>Call Actions</td>
										<td id="set_callaction"></td>
									</tr>
									<tr>
										<td>all Calls</td>
										<td id="set_calls"></td>
									</tr>
									<tr>
										<td>nicht erreicht</td>
										<td id="set_missed"></td>
									</tr>
									<tr>
										<td>no HBG</td>
										<td id="set_nohbg"></td>
										<td class="callshorts_nested"><i class="ri-play-list-add-line"></i></td>
									</tr>
									<tr>
										<td>
											<table id="callshorts_nested" class="hidden">
												<tr>
													<td>Falscher Ansprechpartner</td>
													<td id="set_wrongperson"></td>
												</tr>
												<tr>
													<td>Wiedervorlage</td>
													<td id="set_wiedervorlage"></td>
												</tr>
												<tr>
													<td>Besonderer Grund</td>
													<td id="set_customreason"></td>
												</tr>
												<tr>
													<td>Kunde verweigert HBG</td>
													<td id="set_refused"></td>
												</tr>
												<tr>
													<td>Falsche Nummer</td>
													<td id="set_wrongnumber"></td>
												</tr>
												<tr>
													<td>Nummer nicht vergeben</td>
													<td id="set_numbernotset"></td>
												</tr>
												<tr>
													<td>Falsche Adresse</td>
													<td id="set_wrongadress"></td>
												</tr>
												<tr>
													<td>Kunde hat gekündigt</td>
													<td id="set_canceldcontract"></td>
												</tr>

											</table>
										</td>
									</tr>
									<tr>
										<td>HBG set</td>
										<td id="set_hbgset"></td>
									</tr>
									<tr>
										<td>CTA</td>
										<td id="set_cta"></td>
									</tr>
								</tbody>
							</table>

						</div>
					</div>
				</div>
			</div>
			<div class="col-3">

			</div>

		</div> <!-- end first row -->




		<div class="row">
			<div class="row vcard mt-3">
				<div class="row">
					<span style="font-size:18px">Get Customer by hbg_date</span>
				</div>
				<div class="col-md-4 mb-3">
					<div class="row">
						<!-- Project Selection -->
						<div class="col-12">
							<div class="row">
								<div class="col-4">
									<!-- Checkbox for Select All / Deselect All -->
									<input type="checkbox" id="selectAllCheckbox" /><label style="user-select: none;" for="selectAllCheckbox">Select All</label>
								</div>
								<div class="col-8">
									<!-- Filter Input -->
									<input class="form-select" id="searchInput" placeholder="Filter"></input>
								</div>
							</div>
							<div id="projectList" style="max-height: 30vh; overflow-y: scroll;">
								<!-- Project list will be populated here -->
							</div>
						</div>
					</div>
					<div class="row mt-3">
						<!-- Submit Button -->
						<div class="col-2">
							<button id="submitProjectCalc" class="btn btn-primary">Submit</button>
						</div>
						<div class="col">
							<input placeholder="Select Daterange" class="form-control" id="dateRange">
						</div>
					</div>

					<!-- Results Section -->
					<div id="results">
						<!-- Database query results will be displayed here -->
					</div>
				</div>
				<div class="col-md-8 mb-3" style="overflow-x: auto;">
					<!-- DataTables will enhance this basic table -->
					<table class="table" id="Datatables_projectlist">
						<thead>
							<!-- DataTables will dynamically create <th> elements here based on AJAX response -->
						</thead>
						<tbody>
							<!-- AJAX call will populate table rows here -->
						</tbody>
					</table>
				</div>

			</div>
		</div>

	</div>
</div>

<style>
	#projectList div.selected {
		background-color: #a2c3ff;
		color: #333;
		cursor: pointer;
	}

	#projectList div {
		user-select: none;
	}
</style>