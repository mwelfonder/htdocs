<?php
include(dirname(__FILE__) . "/users/init.php");

require_once 'view/includes/header.php';


// remove actitity name
// crm app for abbruch
// export singleview
/*





fix comment stopped




	if (!hasPerm(2)) { 
?>
		<div style="text-align: center;">
			<div style="margin-top: 50px;"></div>
			<h1>Wartungsarbeiten</h1>
			<div><img src="https://thumbs.gfycat.com/LivelyOddElephantbeetle-max-1mb.gif"></div>
		</div>

	<?php
		die();
	} 
 




 
*/

// DP Sortierung

$dir = $_SERVER['PHP_SELF'];
//echo $dir . "</br>";
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}
if (isset($user) && !$user->isLoggedIn()) {
	die();
}

$myusername = $user->data()->username;
$myuserid = $user->data()->id;

$profile_pic_name = $user->data()->profile_pic;
$profile_pic_base_url = "https://crm.scan4-gmbh.de/usersc/plugins/profile_pic/files/";

if (empty($profile_pic_name)) {
	$profile_pic_url = "https://t3.ftcdn.net/jpg/05/16/27/58/360_F_516275801_f3Fsp17x6HQK0xQgDQEELoTuERO4SsWV.jpg";
} 
else {
	$profile_pic_url = $profile_pic_base_url . $profile_pic_name;
}

if ($myusername === 'JoseLuisRosillo') die();

if ($myusername === 'MichaelWinter') {
	Redirect::to('http://hc.scan4-gmbh.de');
	exit();
}


global $us_url_root;
$sessionName = Config::get('session/session_name');
$userID = Session::get($sessionName);
$db = DB::getInstance();
$twoEnabled = $db->query('SELECT twoEnabled FROM users WHERE id = ?', [$userID])->first()->twoEnabled;

if (!$twoEnabled) {
	Redirect::to($us_url_root . "usersc/plugins/two_factor/enable2FA.php");
}


include_once 'view/includes/functions.php';

$view = Input::get('view');
//echo $view;
$nav_active = $view;
$userdata = $user->data();
$currentusername = $userdata->username;
$currentpermission = fetchUserPermissions($userdata->id);


/*
echo '<pre>';
echo print_r($currentpermission = fetchUserPermissions($userdata->id));
echo '</pre>';
*/





//echo 'false';
if (hasPerm([2]) && $view === '') {
	Redirect::to($us_url_root . "route.php?view=dashboard_projects");
} else if (hasPerm([5]) && $view === '') {
	Redirect::to($us_url_root . "route.php?view=map");
} else if (hasPerm([8, 9]) && $view === '') {
	Redirect::to($us_url_root . "route.php?view=dashboard_projects");
} else if (hasPerm([27]) && $view === '') {
	Redirect::to($us_url_root . "route.php?view=hbgmodul");
} else if ($view === '') {
	Redirect::to($us_url_root . "route.php?view=map");
}

//

ob_start();
?>
<script type="text/javascript" src="view/includes/js/general.js?v=2.0"></script>
<div class="row body_content heattracker">
	<div class="container-fluid">
		<div class="row">
			<!-- Sidebar -->
			<div id="nav-bar">
				<input id="nav-toggle" type="checkbox" />
				<div id="nav-header">
					<a id="nav-title" href="/" target="_blank">SCAN4 CRM</a>
					<label for="nav-toggle"><span id="nav-toggle-burger"></span></label>
					<hr />
				</div>
				<?php include("view/includes/navmenu.php"); ?>
				<input id="nav-footer-toggle" type="checkbox" />
				<div id="nav-footer">
					<div id="nav-footer-heading">
						<div id="nav-footer-avatar">
							<img src="<?php echo htmlspecialchars($profile_pic_url); ?>" />
						</div>
						<div id="nav-footer-titlebox">
							<a id="nav-footer-title" href="https://crm.scan4-gmbh.de/users/account.php" target="_blank"><?php echo htmlspecialchars($myusername); ?></a>
							<span id="nav-footer-subtitle"></span>
						</div>
						<label for="nav-footer-toggle"><i class="fas fa-caret-up"></i></label>
					</div>
					<div id="nav-footer-content">
						<li id="logmeout-btn-route">
							<span class="nav_hoveritem"><i class="ri-login-box-line"></i> Logout</span>
						</li>
						<li id="settings-btn-route">
							<a href="https://crm.scan4-gmbh.de/users/account.php" class="nav_hoveritem"><i class="ri-settings-line"></i> Settings</a>
						</li>
						<?php
						$serverIP = $_SERVER['SERVER_ADDR'];
						if ($serverIP == '5.75.153.231') {
						?>
							2 - Scan4 CRM - v.1.4.2
						<?php
						} elseif ($serverIP == '195.201.239.74') {
						?>
							1 - Scan4 CRM - v.1.4.2
						<?php
						} else {
							echo 'Unbekannter Node';
						}
						?>
					</div>

				</div>
			</div>
			<div class="col-10 content-wrapper">
				<div class="content">
					<?php
					include("view/includes/navbar.php");
					/*
				if ($view == '' || $view == 'dashboard') {
					if (hasPerm(2)) {
						echo 'true';
						include("view/_dashboard.php");
					} else {
						include("view/_dashboard.php");
					} 
					if (!hasPerm([3])) header('Location: route.php?view=phoner');
				}
				*/
					if (strpos($view, 'dashboard_projects') !== false) {
						include("view/_dashboard_overview_projects.php");
					}
					if (strpos($view, 'projectdetails') !== false) {
						include("view/_dashboard_overview_single.php");
					}
					if (strpos($view, 'dashboard_appointments') !== false) {
						include("view/_dashboard_overview_appointments.php");
					}
					if ($view === 'dashboard_overview') {
						include("view/_dashboard_overview_all.php");
					}
					if (strpos($view, 'dashboard_overview_hbg') !== false) {
						include("view/_dashboard_overview_hbg.php");
					}
					if ($view == 'phoner') {
						include("view/_phoner.php");
					}
					if (strpos($view, 'activation') !== false) {
						include("view/_frei.php");
					}
					if (strpos($view, 'technican') !== false) {
						include("view/_technican.php");
					}
					if (strpos($view, 'appointments') !== false) {
						include("view/_appointments.php");
					}
					if (strpos($view, 'phonerapp') !== false) {
						include("view/_phoner_mask.php");
					}
					if (strpos($view, 'import') !== false) {
						include("view/_import.php");
					}
					if (strpos($view, 'aport') !== false) {
						include("view/_importme.php");
					}
					if (strpos($view, 'tickgeter') !== false) {
						include("view/_tickets_overview.php");
					}
					if (strpos($view, 'hbgcheck') !== false) {
						include("view/_hbgcheck.php");
					}
					if (strpos($view, 'dashboard_overview_all') !== false) {
						include("view/_dashboard_overview_all.php");
					}
					if (strpos($view, 'comment') !== false) {
						include("view/_testing.php");
					}
					if (strpos($view, 'hbgmodul') !== false) {
						include("view/_hbgcheckv2.php");
					}
					if (strpos($view, 'mailsender') !== false) {
						include("view/_mailsender.php");
					}
					if (strpos($view, 'cron') !== false) {
						include("view/includes/cronjob.php");
					}
					if (strpos($view, 'weekplan') !== false) {
						include("view/_weekplan.php");
					}
					if (strpos($view, 'frei2') !== false) {
						include("view/_frei.php");
					}
					if (strpos($view, 'test2') !== false) {
						include("view/test2.php");
					} elseif (strpos($view, 'test') !== false) {
						include("view/test.php");
					}
					if (strpos($view, 'map') !== false) {
						include("view/_map.php");
					}
					if (strpos($view, 'worktime') !== false) {
						include("view/_worktime.php");
					}
					if (strpos($view, 'appcheck') !== false) {
						include("view/_app_checker.php");
					}
					if (strpos($view, 'sipgate') !== false) {
						include("view/_sipgate.php");
					}
					if (strpos($view, 'uggapi') !== false) {
						include("view/_uggapi.php");
					}
					if (strpos($view, 'gps') !== false) {
						include("view/_gpsmap.php");
					}
					?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php


// y


require_once $abs_us_root . $us_url_root . 'users/includes/user_spice_ver.php';

?>