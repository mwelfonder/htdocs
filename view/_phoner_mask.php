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
$permlevel = $logged_in->permissions;
//echo 'permission: ' . $permlevel;
echo '<div id="myusername" class="hidden">' . $logged_in->username . '</div>';

$perm_telefonist = fetchPermissionUsers(5); // 5 = Telefonist
$perm_hausbegeher = fetchPermissionUsers(6); // 6 = Hausbegeher
for ($i = 0; $i < count($perm_hausbegeher); $i++) {
	$begeher[] = echousername($perm_hausbegeher[$i]->user_id);
}
sort($begeher);
/*
for ($i = 0; $i < count($perm_telefonist); $i++) {
	echo echousername($perm_telefonist[$i]->user_id);
}
*/
//HEHE000446
//HEHE003066
//ABW002812240002


/*
$array_citys = array();

$conn = dbconnect();
$query = "SELECT * FROM `scan4_homes` WHERE 1";
if ($result = $conn->query($query)) {
    while ($row = $result->fetch_row()) {
        $string = str_replace(['00',' ', '+49', "\r", "\n"], '', $row[14]);
        $string2 = str_replace(['00',' ', '+49', "\r", "\n"], '', $row[15]);
        $array_citys[] = array("homeid" => $row[10], "phone1" => $string, "phone2" => $string2);
    }
    $result->free_result();
}

$lenght = count($array_citys);

for ($i=0; $i < $lenght;$i++) {
    
    $query = "UPDATE `scan4_homes` SET `phone1`='".$array_citys[$i]["phone1"]."',`phone2`='".$array_citys[$i]["phone2"]."' WHERE HomeId='". $array_citys[$i]["homeid"] ."'";
    mysqli_query($conn, $query) or die(mysqli_error($conn));
    $query = "UPDATE `scan4_homes` SET `scan4_status`='DONE' WHERE HomeId='". $array_citys[$i]["homeid"] ."' AND HBG_Status='DONE'";
    mysqli_query($conn, $query) or die(mysqli_error($conn));

}
*/


?>
<script type="text/javascript" src="view/includes/js/app_phoner_mask.js?v=23"></script>

<div class="body-content-app" id="body-content-app">
	<div class="row app-phoner-wrapper">
		<div class="row app-phonerapp-topbar">
			<div class="app-phonerapp-topbar-wrapper">
				<div class="col customer-head">
					<div class="head-info-wrapper">
						<div class="phoner-head info"><i class="ri-profile-line"></i><span><b> HomeID </b></span>
							<span style="cursor: pointer;" id="head-homeid">HEHE000998</span>
						</div>
						<div class="phoner-head info"><i class="ri-home-3-line"></i><b> AdressID </b>
							<span id="head-adressid"></span>
						</div>
						<div class="phoner-head info"><i class="ri-none"></i><b> CARRIER </b>
							<span id="head-statusnri" class="cspill blue">OPEN</span>
						</div>
						<div class="phoner-head info">
							<i class="ri-none"></i><b>SCAN4 </b>
							<span id="head-statussc4" class="cspill blue">OPEN</span>
						</div>
						<!--
						<div class="phoner-head info">
							<i class="ri-none"></i><b>Zuletzt geöffnet </b>
							<span id="head-status-lastopend" class="cspill">27.11.'22</span>
						</div>
						-->
					</div>
					<div class="progress-wrapper">
						<div id="progressitem1" class="progress-item status blue"><i class="ri-bookmark-2-line"></i><span> OPEN</span></div>
						<div id="progressitem2" class="progress-item anruf"><i class="ri-phone-line"></i><span> Anruf 1</span></div>
						<div id="progressitem3" class="progress-item anruf"><i class="ri-phone-line"></i><span> Anruf 2</span></div>
						<div id="progressitem4" class="progress-item anruf"><i class="ri-phone-line"></i><span> Anruf 3</span></div>
						<div id="progressitem5" class="progress-item anruf"><i class="ri-phone-line"></i><span> Anruf 4</span></div>
						<div id="progressitem6" class="progress-item anruf"><i class="ri-phone-line"></i></i><span> Anruf 5</span></div>
						<div id="progressitem7" class="progress-item email"><i class="ri-at-line"></i><span> Mail</span></div>
						<div id="progressitem8" class="progress-item einwurf"><i class="ri-mail-send-line"></i><span> Einwurf</span></div>
						<div id="progressitem9" class="progress-item hbg"><i class="ri-user-shared-line"></i><span> HBG</span></div>
					</div>
				</div>
			</div>

			<div class="app-phonerapp-topbar-interact-wrapper">
				<?php if (hasPerm(2) || hasPerm([3]) || hasPerm([5])) { // admin // TL // telefonist 
				?>
					<div id="phonerapp-loadnext" class="btn-phonerapp-loadnext isset"><span>Weiter</span></div>
				<?php } ?>

				<?php if (hasPerm(2) || hasPerm([3]) || hasPerm([7])) { // 2 Admin / 3 Teamleiter / 7 Konstrukteur
					echo '<div id="phonerapp_ticket" class="btn-app prim red"><span>Ticket erstellen</span></div>';
					echo '<div id="phonerapp_edit" class="btn-app prim yellow"><span>Kunde bearbeiten</span></div>';
				} ?>

			</div>
		</div>
		<div class="row app-phonerapp-content">
			<div class="col-3 app-phonerapp-sidebar">
				<div class="app-sidebar-content-phonerapp">
					<div class="phoner-customer-heading">Adressdetails</div>
					<div class="phoner-customer-detaillist">
						<table>
							<tbody class="phoner-clientinfo">
								<tr>
									<td><b>Name: </b></td>
									<td id="phonerinfo_name">Load...</td>
								</tr>
								<tr>
									<td><b>Straße: </b></td>
									<td id="phonerinfo_street">Load...</td>
								</tr>
								<tr>
									<td><b>Ort: </b></td>
									<td id="phonerinfo_city">Load...</td>
								</tr>
								<tr>
									<td><b>Unit: </b></td>
									<td id="phonerinfo_units">Load...</td>
								</tr>
								<tr class="hidden">
									<td><b>Email: </b></td>
									<td id="phonerinfo_email">Load...</td>
								</tr>
								<tr>
									<td><b>Tel.: </b></td>
									<td id="phonerinfo_phone1"><a href="tel:+" class="phoner-callnow">Load...</a></td>
								</tr>
								<tr>
									<td><b>Tel.: </b></td>
									<td id="phonerinfo_phone2" class=""></td>
								</tr>
								<tr>
									<td><b>Tel.: </b></td>
									<td id="phonerinfo_phone3" class=""></td>
								</tr>
								<tr>
									<td><b>Tel.: </b></td>
									<td id="phonerinfo_phone4" class=""></td>
								</tr>
								<tr></tr>
								<tr class="phonerinfo_dp">
									<td><b>DP: </b></td>
									<td id="phonerinfo_dp"></td>
								</tr>
								<tr class="phonerinfo_carrier">
									<td><b>Carrier: </b></td>
									<td id="phonerinfo_carrier"></td>
								</tr>
								<tr class="phonerinfo_client">
									<td><b>Client: </b></td>
									<td id="phonerinfo_client"></td>
								</tr>
								<tr class="phonerinfo_startdate">
									<td><b>Start: </b></td>
									<td id="phonerinfo_startdate"></td>
								</tr>
								<tr id="phonerinfo_priorow" class="hidden">
									<td><b>Prio:&nbsp;</b><i class="ri-star-fill"></i>&nbsp;</td>
									<td id="phonerinfo_priocount"></td>
								</tr>
								<tr class="hidden">
									<td id="phonerinfo_cityname"></td>
								</tr>

							</tbody>
						</table>
					</div>
					<div class="phonerapp-sidebarspace"></div>

					<div class="alignbottomcontainer">
						<div id="carrierwrapper">
							<div id="carrier-logo" class="carrier-gvg"></div>
						</div>
						<p class="scan4hotline">Scan4 GmbH Glasfaser Hotline</br><b>0721-98191540</b></p>
					</div>
				</div>

			</div>
			<div class="col app-phoner-main" id="app-phoner-main">
				<div class="app-phoner-city-wrapper">
					<div class="col customer-log">
						<div class="row">
							<div class="col phonerapp-visualinteract">
								<div class="phoner-timeline-wrapper">
									<div id="timeline_head_main" class="timeline-head timeline active"><i class="ri-history-line"></i> Timeline</div>
									<div id="timeline_head_relation" class="timeline-head relations disabled"><i class="ri-arrow-left-right-line"></i> Relation<span id="relationcounter" class="timelineheadcounter zero"></span></div>
									<div id="timeline_head_anfrufe" class="timeline-head calls hidden"><i class="ri-phone-line"></i> Anrufe</div>
									<div id="timeline_head_hbg" class="timeline-head hbg disabled "><i class="ri-calendar-check-line"></i> HBG<span id="hbgscounter" class="timelineheadcounter zero"></span></div>
									<?php if (hasPerm([2])) { // Admin 
									?>
										<div id="timeline_head_logfile" class="timeline-head logfile"><i class="ri-file-list-3-line"></i> Log</div>
									<?php } ?>
								</div>
								<div class="row phonerapp-visualblock-wrapper">
									<div id="loaderwrapper2" class="fullwidth aligncenter hidden" style="background: #e9e9f3;height: 90%;">
										<div class="appt-loader loader"></div>
									</div>
									<div class="col phonerapp-visualblock">

										<div id="holder-relations" class="timeline-holder relations hidden"></div>
										<div id="holder-hbgitems" class="timeline-holder hbgitems  hidden">
											<div id="hbgitemsys" class="hbgitem hidden">
												<div id="syshbgitemtext" class="hbgitemtextwrap">
													<div class="item-inner-box"><i class="fa-regular fa-image"></i></div>
													<span class="hbgitemtext">HBG im System gefunden</span>
												</div>
												<div class="row imgwrapper">
													<div class="itemwrapper"><a id="href-hbg-item-1" href="" target="_blank" rel="noopener noreferrer"><img class="hbgkeyitem" id="hbg-item-1" src=""></a></div>
													<div class="itemwrapper"><a id="href-hbg-item-2" href="" target="_blank" rel="noopener noreferrer"><img class="hbgkeyitem" id="hbg-item-2" src=""></a></div>
													<div class="itemwrapper"><a id="href-hbg-item-3" href="" target="_blank" rel="noopener noreferrer"><img class="hbgkeyitem" id="hbg-item-3" src=""></a></div>
													<div class="itemwrapper"><a id="href-hbg-item-4" href="" target="_blank" rel="noopener noreferrer"><img class="hbgkeyitem" id="hbg-item-4" src=""></a></div>
													<div class="itemwrapper"><a id="href-hbg-item-5" href="" target="_blank" rel="noopener noreferrer"><img class="hbgkeyitem" id="hbg-item-5" src=""></a></div>
													<div class="itemwrapper"><a id="href-hbg-item-6" href="" target="_blank" rel="noopener noreferrer"><img class="hbgkeyitem" id="hbg-item-6" src=""></a></div>
													<div class="itemwrapper"><a id="href-hbg-item-7" href="" target="_blank" rel="noopener noreferrer"><img class="hbgkeyitem" id="hbg-item-7" src=""></a></div>
													<div class="itemwrapper"><a id="href-hbg-item-8" href="" target="_blank" rel="noopener noreferrer"><img class="hbgkeyitem" id="hbg-item-8" src=""></a></div>
												</div>
											</div>
										</div>
										<div id="holder-timeline" class="timeline-holder timeline">
											<ul class="list-group phoner-timeline" id="timeline">
												<li class="list-group-item emptyentry">
													<div class="notimeline"><span><i class="ri-ghost-line"></i> Zu diesem Kunden gibt es noch keine Einträge</span></div>
												</li>
											</ul>
										</div>
										<div id="holder-logfile" class="timeline-holder logfile hidden">
											<ul style="list-style:none;" class="list-group phoner-timeline" id="logfile">
												<li class="list-group-item emptyentry">
													<div class="notimeline"><span><i class="ri-ghost-line"></i> Zu diesem Kunden gibt es noch keine Einträge</span></div>
												</li>
											</ul>
										</div>
									</div>
								</div>
							</div>
							<div class="col phonerapp-interact">
								<?php if (hasPerm(2) || hasPerm([3]) || hasPerm([5])) { // admin // TL // telefonist 
								?>
									<div class="spacer phonerapp-interact"></div>
									<div class="phonerapp-interact-wrapper">
										<ul>
											<li>
												<div class="btn-interact-phonerapp hgreen unset" id="phonerapp_safe">
													<i class="ri-save-3-line"></i><span> Speichern</span>
												</div>
											</li>
											<li>
												<div class="btn-interact-phonerapp hblue" id="phonerapp_notreached">
													<i class="ri-user-unfollow-line"></i><span> Nicht erreicht</span>
												</div>
											</li>
											<li>
												<div class="btn-interact-phonerapp hblue" id="phonerapp_nohbg">
													<i class="ri-user-unfollow-line"></i><span> keine HBG</span>
												</div>
											</li>
											<li>
												<div class="btn-interact-phonerapp hblue appointmentbtn" id="phonerapp_hbgset">
													<i class="ri-calendar-2-line"></i><span> erreicht mit Termin</span>
												</div>
											</li>
										</ul>
									</div>
								<?php } ?>
								<div class="phonerapp-interact-field-wrapper">
									<div class="phonerapp-interact-field-nohbg-wrapper hidden" id="phonerapp_interactfield_nohbg">
										<div class="row phonerapp-interact-field">
											<select class="form-select condition-box" id="interact_nohbgselect" aria-label="">
												<option id="canceloptionfirst" disabled selected>Grund wählen...</option>
												<option>Falscher Ansprechpartner</option>
												<option>Wiedervorlage</option>
												<option disabled>----------</option>
												<option>Keine HBG - Besonderer Grund</option>
												<option>Keine HBG - Kunde verweigert HBG</option>
												<option>Keine HBG - Falsche Nummer</option>
												<option>Keine HBG - Nummer nicht vergeben</option>
												<option>Keine HBG - Falsche Daten</option>
												<option>Keine HBG - Kunde sagt, er habe gekündigt</option>
											</select>
										</div>
										<div class="row">
											<input id="datetimepicker_followup" class="hidden" type="text" placeholder="Datum wählen...">
											<textarea class="form-control" id="interact_nohbgselect_comment" rows="3" placeholder="Kommentar"></textarea>
										</div>
									</div>

									<div class="phonerapp-interact-field-hbg-wrapper hidden" id="phonerapp_interactfield_sethbg">
										<div class="row">
											<div class="row app-phoner-select-datepicker-wrap">
												<input id="datetimepicker" type="text" placeholder="Datum wählen...">
												<span class="hidden" id="hbgdurration">
											</div>


										</div>
										<div class="row">
											<div class="row app-phoner-select-comment-wrap">
												<textarea class="form-control" id="select_hbg_comment" rows="3" placeholder="Kommentar zum Termin"></textarea>
											</div>
										</div>
									</div>
								</div>

							</div>

						</div>

					</div>
				</div>
			</div>
		</div>
	</div>





</div>
</div>
<?php

//if ($permlevel === "1" || $permlevel === "7") {
if (hasPerm([3]) || hasPerm([7])) {
?>
	<div id="overlapp_wrapper_tickets" class="overlapp-wrapper adminwrapperbottom closed">
		<div class="row overlapp-ticket-wrapper">
			<div class="col-3 firstrow-tickets ">
				<ul id="app_tickets_ticketprio" class="list-group ticketswrapper">
					<li class="list-group-item d-flex ticketprio">
						<div id="ticketprio1" class="groupitem-wrapper ticket priowrapper">
							<div class="groupitem-leftwrap icon">
								<i class="ri-24-hours-line"></i>
							</div>
							<div class="groupitem-rightwrap">
								<div class="groupitem-heading">Urgent</div>
								<div class="groupitem-subtext">24 Stunden max!</div>
							</div>
						</div>
					</li>
					<li class="list-group-item d-flex ticketprio ">
						<div id="ticketprio2" class="groupitem-wrapper ticket priowrapper">
							<div class="groupitem-leftwrap icon">
								<i class="ri-fire-line"></i>
							</div>
							<div class="groupitem-rightwrap">
								<div class="groupitem-heading">Important</div>
								<div class="groupitem-subtext">3 Tage</div>
							</div>
						</div>
					</li>
					<li class="list-group-item d-flex ticketprio">
						<div id="ticketprio3" class="groupitem-wrapper ticket priowrapper">
							<div class="groupitem-leftwrap icon">
								<i class="ri-check-double-line"></i>
							</div>
							<div class="groupitem-rightwrap">
								<div class="groupitem-heading">ToDo</div>
								<div class="groupitem-subtext">7 Tage</div>
							</div>
						</div>
					</li>
				</ul>
			</div>
			<div class="col-7 firstrow-tickets">
				<div class="app-ticket-ticket col">
					<div class="ticket-textwrapper">
						<input type="text" id="ticket_titel" class="form-control" placeholder="Grund">
						<textarea class="form-control" id="ticket_text" name="ticket_text" rows="3" cols="50" placeholder="Ticket Beschreibung"></textarea>
					</div>
				</div>
			</div>
			<div class="col-2 firstrow-tickets">
				<div class="app-ticket-right col">
					<div id="ticketsubmit" class="btn-app primary">
						<span><i class="ri-send-plane-line"></i> Save</span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="overlapp_wrapper_edit" class="overlapp-wrapper adminwrapperbottom closed">
		<div class="row overlapp-ticket-wrapper">
			<div class="col-4">
				<div class="row">
					<div class="col">
						<div class="form-group">
							<label for="admin_select_status" class="adminlabel">Scan4 Status ändern auf</label>
							<select class="form-control" id="admin_select_status">
								<option value="OPEN">OPEN</option>
								<option value="STOPPED">STOPPED</option>
								<option value="DONE">DONE</option>
								<option value="DONE CLOUD">DONE CLOUD</option>
								<option value="PLANNED">PLANNED</option>
								<option value="OVERDUE">OVERDUE</option>
							</select>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col">
						<div class="form-group">
							<label for="admin_select_calls" class="adminlabel">Anrufe setzen auf</label>
							<select class="form-control" id="admin_select_calls">
								<option value="0">0</option>
								<option value="1">1</option>
								<option value="2">2</option>
								<option value="3">3</option>
								<option value="4">4</option>
								<option value="5">5</option>
							</select>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col">
						<div class="form-group">
							<label for="admin_select_prio" class="adminlabel">Prio setzen auf</label>
							<select class="form-control" id="admin_select_prio">
								<option value="1">Prio 1</option>
								<option value="2">Prio 2</option>
								<option value="3">Prio 3</option>
								<option value="0">Keine Prio</option>
							</select>
						</div>
					</div>
				</div>

			</div>

			<div class="col-4">
				<div class="row">
					<div class="col">
						<div class="form-group">
							<label for="admin_comment" class="adminlabel">Kommentar</label>
							<textarea class="form-control" id="admin_comment" name="admin_comment" cols="35" rows="5"></textarea>
						</div>
					</div>
				</div>
			</div>
			<div class="col-2">

			</div>
			<div class="col-2">
				<div id="admin_edit_save" class="btn-app primary adminbtn aligncenter">
					<span><i class="ri-save-3-line"></i> Save</span>
				</div>
			</div>
		</div>
	</div>

<?php } ?>


	<div id="pickcalendar_wrapper">
		<table id="pickcalendar">

			<tbody>
				<?php
				for ($hour = 7; $hour <= 20; $hour++) {
					for ($minutes = 0; $minutes < 60; $minutes += 30) {
						$time1 = sprintf("%02d:%02d", $hour, $minutes);
						$time2 = sprintf("%02d:%02d", $hour, $minutes + 30);
						$rowClass = $minutes === 0 ? 'hour-row' : '';
						$hourText = $minutes === 0 ? "$hour&nbsp;Uhr" : '';
						echo "<tr class='$rowClass'>";
						echo "<td>$hourText</td>";
						echo "<td></td>";
						echo "</tr>";
					}
				}
				?>
			</tbody>
		</table>
		<div id="pick_dropzone"></div>
		<div id="pick_confirm"><i class="ri-check-line"></i>OK</div>
	</div>
	<div id="eventOverlay"></div>


	<div id="picker_select_wrapper" style="display: none;">
		<div id="picker_select" class="picker_select"></div>
	</div>




	<style>
		#eventOverlay {
			position: absolute;
			left: -15vw;
			width: 1%;
			height: 1%;
			z-index: 999999999999999999999999;
		}

		.event-wrapper {
			position: absolute;
			background-color: #4a934d;
			color: #2b3148;
			font-weight: 500;
			padding: 1px 6px;
			box-sizing: border-box;
			border: 1px solid #2b3148;
			border-radius: 4px;
		}

		.event-cell {
			background-color: #4caf50;
			color: #ffffff;
			padding: 5px;
			border-top: 1px solid #4caf50;
		}

		.draggable-event {
			background-color: #ecd57f;
			border: 1px solid #30395f;
			color: #3f4458;
			border-radius: 4px;
			box-shadow: rgba(0, 0, 0, 0.16) 0px 1px 4px;
			user-select: none;
			cursor: grab;
		}


		.overlap {
			border: 2px solid red;
		}

		.time-text {
			font-size: 10px;
			position: absolute;
			top: -15px;
			left: 2px;
			color: #fff;
		}

		.eventsHolder {
			color: #000;
			background: #80cbc4;
			padding: 5px;
			text-align: center;
			border-radius: 2px;
			margin-top: 100px;
		}

		#pick_confirm {
			position: absolute;
			bottom: 0;
			text-align: center;
			width: 100%;
			padding: 20px;
			background: #2d3248;
			color: #fff;
			font-weight: 700;
			cursor: pointer;
			user-select: none;
		}

		#pick_confirm:hover {
			background: #33374b;
		}

		#pickcalendar td:first-child {
			width: 2px;
			padding: 2px;
		}

		#pickcalendar tr:last-child {
			border-bottom: 2px solid #383d53;
		}

		div#pickcalendar_wrapper {
			width: 15vw;
			left: -15vw;
			position: absolute;
			z-index: 9999999999999999999999;
			background: #3f4458;
			height: 100vh;
		}

		#pickcalendar {
			width: 100%;
			border-collapse: collapse;
		}

		#pickcalendar tr {
			height: 25px;
		}

		#pickcalendar th,
		#pickcalendar td {
			padding: 5px;
			text-align: left;
			vertical-align: middle;
		}

		.hour-row {
			border-top: 1px solid #2b3148;
			border-bottom: 1px solid #383d53;
			font-size: 12px;
		}

		.picker_select {
			background-color: #3f4458;
			border-radius: 4px;
			padding: 4px;
			max-height: 100%;
			overflow-y: auto;
			box-sizing: border-box;
			width: 100%;
			color: #fff;
			user-select: none;
		}

		.picker_select>div {
			padding: 2px 4px;
			cursor: pointer;
			border-radius: 2px;
			margin: 1px;
		}

		.picker_select>div:hover {
			background-color: rgb(128 203 196 / 30%);
		}

		.picker_select>div.selected {
			background-color: #80cbc4;
			color: #000;
		}

		#picker_select_wrapper {
			position: absolute;
			z-index: 999;
			background: #3f4458;
			border-radius: 4px;
			border: 1px solid #20222c;
		}

		.event-wrapper-popup {
			position: absolute;
			background-color: #3f4458;
			border: 1px solid #2b3148;
			padding: 2px 10px;
			border-radius: 4px;
			margin-left: 8px;
			color: #cdcdcd;
			font-weight: 300;
			z-index: 2;
		}

		.event-wrapper-popup:before {
			content: '';
			display: block;
			position: absolute;
			left: -5px;
			/* Position the arrow to the left of the popup */
			top: 50%;
			transform: translateY(-50%);
			border-top: 6px solid transparent;
			border-bottom: 6px solid transparent;
			border-right: 6px solid #3f4458;
			/* Set the color of the arrow to match the background of the popup */
		}

		.btn-movenow.disabled {
			cursor: not-allowed !important;
		}
	</style>
<?php


 
?>