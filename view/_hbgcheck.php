<?php

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}
if (!hasPerm([2, 7, 27])) {
	die();
}

/*

hbgcheck

starten > overdue

3buttons

1> hbg vorhanden __

__ option A hbg vorhanden screenshot/pdf
>>>>>> ist file im ordner? JA / NEIN :::: SET DONE
__ option B excel pdf vorhanden
>>>>>> ist file im ordner? JA / NEIN :::: SET DONE


2> hbg abruch __

__ option A möglich 
>>>>> field abbruch grund (kd nicht zu hause) :::: SET OPEN
__ option B nicht möglich
>>>>> reasons select (falsche nummer, falsche adresse) :::: SET STOPPED

3> hbg no reason __
>>>>> :::: SET MISSING


*/

?>
<script type="text/javascript" src="view/includes/js/app_hbgcheck.js"></script>

<div class="body-content-app" id="body-content-app">
	<div id="hbgcheck_wrapper" class="row app-wrapper hbgcheck">
		<div id="loaderwrapper" class="fullwidth aligncenter">
			<div class="appt-loader loader"></div>
		</div>
		<div class="row app-phonerapp-content">
			<div class="col-3 app-phonerapp-sidebar">
				<div class="app-sidebar-content-phonerapp">
					<div class="phoner-customer-heading">Adressdetails</div>
					<div class="phoner-customer-detaillist">
						<table>
							<tbody class="phoner-clientinfo">
								<tr>
									<td><b>HomeID: </b></td>
									<td id="phonerinfo_homeid">Load...</td>
								</tr>
								<tr>
									<td><b>HBG Date: </b></td>
									<td id="phonerinfo_hbgdate">Load...</td>
								</tr>
								<tr>
									<td><b>Status Sys: </b></td>
									<td><span id="phonerinfo_statussys"></span></td>
								</tr>
								<tr>
									<td><b>Status Sc4: </b></td>
									<td><span id="phonerinfo_statussc4">Load...</span></td>
								</tr>
								<tr>
									<td><b>Name: </b></td>
									<td id="phonerinfo_name"><span id="info_lastname"></span>, <span id="info_firstname"></span></td>
								</tr>
								<tr>
									<td><b>Straße: </b></td>
									<td id="phonerinfo_street"><span id="info_street"></span> <span id="info_streetnumber"></span><span id="info_streetnumberadd"></span></td>
								</tr>
								<tr>
									<td><b>Ort: </b></td>
									<td id="phonerinfo_city"><span id="info_plz"></span> <span id="info_city"></span></td>
								</tr>
								<tr class="hidden">
									<td><b>Ort: </b></td>
									<td id="phonerinfo_project"></td>
								</tr>
								<tr class="hiddens">
									<td><b>Projectnumber: </b></td>
									<td id="phonerinfo_project_number"></td>
								</tr>
								<tr>
									<td><b>Unit: </b></td>
									<td id="phonerinfo_units">Load...</td>
								</tr>
								<tr>
									<td><b>Tel.: </b></td>
									<td id="phonerinfo_phone1"><a href="tel:+" class="phoner-callnow"></a></td>
								</tr>
								<tr>
									<td><b>Tel.: </b></td>
									<td id="phonerinfo_phone2" class="hidden"></td>
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
								<tr id="phonerinfo_priorow" class="hidden">
									<td><b>Prio:&nbsp;</b><i class="ri-star-fill"></i>&nbsp;</td>
									<td id="phonerinfo_priocount"></td>
								</tr>

							</tbody>
						</table>
					</div>
					<div class="phonerapp-sidebarspace">
						<div id="carrierwrapper">
							<div id="carrier-logo" class="carrier-gvg"></div>
						</div>
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
									<div id="timeline_head_relation" class="timeline-head relations disabled"><i class="ri-arrow-left-right-line"></i> Relation<span id="relationcounter" class="timelineheadcounter hidden"></span></div>

								</div>
								<button onclick="hideElements()">Hide Checked</button>
								<div class="row phonerapp-visualblock-wrapper hbgcheck">
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
									</div>
								</div>
								<div class="row">
									<div class="container d-flex justify-content-center pt-30">
										<div class="file-drop-area">
											<span class="choose-file-button">Datei wählen</span>
											<span class="file-message">// DRAG AND DROP</span>
											<input id="fileupload" class="file-input" type="file">
										</div>
									</div>
								</div>
							</div>
							<div class="col phonerapp-interact">
								<div class="spacer phonerapp-interact"></div>
								<div class="phonerapp-interact-wrapper">
									<ul>
										<li>
											<div class="btn-interact-phonerapp hgreen unset" id="hbg_safe">
												<i class="ri-save-3-line"></i><span> Speichern</span>
											</div>
										</li>
										<li>
											<div class="btn-interact-phonerapp firstselect hblue" id="hbg_vorhanden">
												<i class="ri-checkbox-circle-line"></i><span> Vorhanden</span>
											</div>
										</li>
										<li>
											<div class="btn-interact-phonerapp firstselect hblue" id="hbg_abbruch">
												<i class="ri-close-circle-line"></i><span> Abbruch</span>
											</div>
										</li>
										<li>
											<div class="btn-interact-phonerapp firstselect hblue" id="hbg_unbegrundet">
												<i class="ri-user-unfollow-line"></i><span> Unbegründet</span>
											</div>
										</li>
									</ul>
								</div>

								<div class="phonerapp-interact-field-wrapper">
									<div class="field-wrapper hidden" id="interactfield_vorhanden">
										<div class="row phonerapp-interact-field">
											<ul class="interactlist">
												<li>
													<div class="btn-interact-phonerapp secondselect hblue" id="vorhanden_excel"><i class="ri-file-excel-2-line"></i> Excel</div>
												</li>
												<li>
													<div class="btn-interact-phonerapp secondselect hblue" id="vorhanden_screenshot"><i class="ri-screenshot-2-line"></i> Screenshot</div>
												</li>
												<li>
													<div class="btn-interact-phonerapp secondselect hblue" id="vorhanden_wrong"><i class="ri-file-damage-line"></i> Falsch</div>
												</li>
											</ul>
										</div>

									</div>

									<div class="field-wrapper hidden" id="interactfield_abbruch">
										<div class="row phonerapp-interact-field">
											<ul class="interactlist">
												<li>
													<div class="btn-interact-phonerapp secondselect hblue" id="abbruch_possible"><i class="ri-checkbox-line"></i> möglich</div>
												</li>
												<li>
													<div class="btn-interact-phonerapp secondselect hblue" id="abbruch_impossible"><i class="ri-checkbox-indeterminate-line"></i> nicht möglich</div>
												</li>
											</ul>
											<select style="margin-bottom:10px;" class="form-select condition-box form-control hidden" id="hbgcheck_possible" aria-label="">
												<option disabled selected>Grund wählen...</option>
												<option>Kunde nicht da</option>
												<option>Begeher nicht da</option>
												<option>Unzureichender Grund</option>
											</select>
											<select style="margin-bottom:10px;" class="form-select condition-box form-control hidden" id="hbgcheck_impossible" aria-label="">
												<option disabled selected>Grund wählen...</option>
												<option>Falsche Adresse</option>
												<option>Keine HBG - KD verweigert HBG</option>
												<option>Keine HBG - Technisch nicht möglich</option>
												<option>Kein Gebäude</option>
											</select>
										</div>
									</div>
									<div class="field-wrapper " id="interactfield_unbegründet">
										<div class="row phonerapp-interact-field">
											<textarea class="form-control" id="check_comment" rows="3" placeholder="Bemerkung"></textarea>
										</div>
									</div>

								</div>
							</div>
						</div>

					</div>
				</div>
			</div>
		</div>
		<div class="row app-hbgcheck mainwrapper holder">
			<div id="hbgcheck_wrapper_step1" class="app-hbgcheck checkwrapper lower step1 hidden">
				<div id="hbgcheck_file1" class="buttonwrapper hbgcheck step file">
					<div class="buttonicon hbgcheck file1"></div>
					<div class="buttontext hbgcheck-title">Vorhanden</div>
				</div>
				<div id="hbgcheck_file2" class="buttonwrapper hbgcheck step file">
					<div class="buttonicon hbgcheck file2"></div>
					<div class="buttontext hbgcheck-title">Vorhanden</div>
				</div>
			</div>
			<div id="hbgcheck_wrapper_step2" class="app-hbgcheck checkwrapper step2 hidden">
				<div id="hbgcheck_yes" class="buttonwrapper hbgcheck step stepselect yes">
					<div class="buttonicon hbgcheck handyes"></div>
					<div class="buttontext hbgcheck-title">Möglich</div>
				</div>
				<div id="hbgcheck_no" class="buttonwrapper hbgcheck step stepselect no">
					<div class="buttonicon hbgcheck handno"></div>
					<div class="buttontext hbgcheck-title">Unmöglich</div>
				</div>
				<div id="hbgcheck_input" class="hbgcheck inputfield hidden">
					<input type="text" id="hbgcheck_reason" class="form-control hidden" placeholder="Abbruchgrund">
					<select class="form-select condition-box form-control hidden" id="hbgcheck_select" aria-label="">
						<option id="canceloptionfirst" disabled selected>Grund wählen...</option>
						<option>Keine HBG - Besonderer Grund</option>
						<option disabled>----------</option>
						<option>Keine HBG - Kunde verweigert HBG</option>
						<option>Keine HBG - Falsche Nummer</option>
						<option>Keine HBG - Nummer nicht vergeben</option>
						<option>Keine HBG - Falsche Adresse</option>
						<option>Keine HBG - Kunde sagt, er habe gekündigt</option>
					</select>
					<input type="text" id="hbgcheck_select_reason" class="form-control hidden" placeholder="Grund">
				</div>
			</div>
		</div>
	</div>
	<div id="hbgcheck_empty" class="row app-wrapper hbgcheck _empty hidden">
		<div class="hbgcheckempty">Keine HBGs zum Überprüfen gefunden</div>
	</div>

</div>


<script>

</script>



<style>
	.checkwrapperitem.hbgdone.checked,
	.checkwrapperitem.hbgfailed.checked {
		display: none;
	}

	.file-drop-area {
		position: relative;
		display: flex;
		align-items: center;
		width: 450px;
		max-width: 100%;
		padding: 25px;
		border: 1px dashed rgba(255, 255, 255, 0.4);
		border-radius: 3px;
		transition: 0.2s;
		border: 2px dashed;
	}

	.choose-file-button {
		flex-shrink: 0;
		background-color: rgba(255, 255, 255, 0.04);
		border: 1px solid rgba(255, 255, 255, 0.1);
		border-radius: 3px;
		padding: 8px 15px;
		margin-right: 10px;
		font-size: 12px;
		text-transform: uppercase;
	}

	.file-message {
		font-size: small;
		font-weight: 300;
		line-height: 1.4;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}

	.file-input {
		position: absolute;
		left: 0;
		top: 0;
		height: 100%;
		width: 100%;
		cursor: pointer;
		opacity: 0;

	}
</style>
<script>
	function hideElements() {
		// Finde alle Elemente mit den Klassen checkwrapperitem, hbgdone, und checked
		const elements = document.querySelectorAll('.checkwrapperitem.hbgdone.checked');

		// Schleife durch die gefundenen Elemente und setze sie auf "display: none"
		elements.forEach(el => {
			el.style.display = 'none';
		});
	}
</script>