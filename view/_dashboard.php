<?php
/*
if (!hasPerm(4)) {
	header("Location: forbidden.php");
}
*/
$dir = $_SERVER['PHP_SELF'];
//echo $dir . "</br>";
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}

$perm_telefonist = fetchPermissionUsers(1); // 5 = Telefonist

/*
echo '<pre>';
echo print_r($perm_telefonist);
echo '</pre>';

*/

for ($i = 0; $i < count($perm_telefonist); $i++) {
	//echo echousername($perm_telefonist[$i]->user_id);
	$data = fetchUserDetails(null, null, $perm_telefonist[$i]->user_id);
	echo $data->username;
	echo $data->profile_pic;
	$userlist[]=  array($data->username =>$data->profile_pic);
	

	echo '<br/>';
}
echo '<pre>';
echo print_r($userlist); 
echo '</pre>';




 

?>

<script type="text/javascript" src="view/includes/js/app_dashboard_sum.js"></script>


<div class="body-content-app" id="body-content-app">
	<div class="dashboard-wrapper">
		<div class="row bgwhite">
			<div class="row insidewrapper fullwidth">
				<div class="row fullwidth">
					<div class="col floatleft">
						<div class="dashcard-subtitle">Report Termine</div>
					</div>
				</div>
				<div class="row fullwidth">
					<div class="col">
						<div class="cardstats title"></div>
						<div id="chart" class="stats"></div>
					</div>
					<div class="col">
						<div class="row fullwidth dashcard-subtitle">Stats</div>
						<div class="row">
							<div class="col dashcard-inner flexstat">
								<ul class="report weekly rows">
									<li>
										<div class="listwrapper report total">
											<div class="listwrapper report text">Calls</div>
											<div class="listwrapper report text">78</div>
										</div>
									</li>
									<li>
										<div class="listwrapper report total">
											<div class="listwrapper report text">Termine</div>
											<div class="listwrapper report text">78</div>
										</div>
									</li>
								</ul>
							</div>
							<div class="col dashcard-inner flexstat">

							</div>
						</div>
						<div class="col">11.84</div>
					</div>
				</div>
			</div>
		</div>





	</div>
</div>




<?php

//live();



















function live()
{
	$userurl = 'ben-hbg_shared_by_Scan4%20GmbH';
	//$url = 'http://nextcloud.alphacc.de/remote.php/dav/calendars/bestadmin/benhbg/calc.ics';

	$headers = array('Content-Type: text/calendar', 'charset=utf-8');
	//$userpwd = 'bestadmin:mSMyCGIRTNPDjiqbJ5kt@HvK9BrsYzApW.2Z8lXEofV1UaOQ63';
	$userpwd = 'sys:smallusdickus';

	$homeid = '06198ACF_8__1';
	$titel = 'Michelle Graf [CRM]';
	$tstart = gmdate("Ymd\THis\Z", strtotime("+12 hours"));
	$tend = gmdate("Ymd\THis\Z", strtotime("+13 hours"));
	$tstamp = gmdate("Ymd\THis\Z");
	$uid = $tstamp . '-' . $homeid . '-RN' . rand(10000, 99999);
	$location = 'Bahnhofstraße 22\, 34281 Gudensberg\, Deutschland';
	$description = 'Notiz: bitte vorher anrufen!\nTel.: +4959841236\nHomeID: ' . $homeid . '\nErstellt am: 27.11.22 14:38\nErstellt von: Michael Winter\nAnruf: #3\nUID:' . $uid;

	$body = 'BEGIN:VCALENDAR
PRODID:CRMSCAN4_cURL_1
VERSION:2.0
CALSCALE:GREGORIAN
BEGIN:VEVENT
CREATED:' . $tstamp . '
DESCRIPTION:' . $description . '
DTEND;TZID=Europe/Berlin:' . $tend . '
DTSTAMP:' . $tstamp . '
DTSTART;TZID=Europe/Berlin:' . $tstart . '
LAST-MODIFIED:' . $tstamp . '
LOCATION:' . $location . '
SEQUENCE:2
STATUS:CONFIRMED
SUMMARY:' . $titel . '
TRANSP:OPAQUE
UID:' . $uid . '
END:VEVENT
END:VCALENDAR';

	$url = 'https://cloud2.scan4-gmbh.de/remote.php/dav/calendars/sys/' . $userurl . '/' . $uid . '.ics';
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($ch, CURLOPT_USERPWD, $userpwd);
	//curl_setopt($ch, CURLOPT_PUT, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

	//Execute the request.
	$response = curl_exec($ch);
	curl_close($ch);

	echo $response;
}
