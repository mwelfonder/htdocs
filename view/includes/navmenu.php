<?php
$dir = $_SERVER['PHP_SELF'];
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}

//perm 2 = Admin
//perm 3 = Teamleiter
//perm 5 = Telefonist
//perm 6 = Hausbegeher
//perm 7 = Kontrukteur
ob_start();
?>

<div id="nav-content">
	<?php
	$menuItems = [
		["perm" => [2, 3], "icon" => "bi bi-house", "text" => "Dashboard", "view" => "dashboard", "active" => $nav_active],
		["perm" => [2, 3, 20], "icon" => "bi bi-view-list", "text" => "Übersicht", "view" => "dashboard_overview", "active" => $nav_active],
		["perm" => [2, 3, 13], "icon" => "bi bi-kanban", "text" => "Project Overview", "view" => "dashboard_projects", "active" => $nav_active],
		["perm" => [2, 3], "icon" => "bi bi-houses", "text" => "HBG Overview", "view" => "dashboard_overview_hbg", "active" => $nav_active],
		["perm" => [2, 3], "icon" => "bi bi-calendar-check", "text" => "Appointments", "view" => "dashboard_appointments", "active" => $nav_active],
		["perm" => [2, 3], "icon" => "bi bi-upload", "text" => "Import", "view" => "import", "active" => $nav_active],
		["perm" => [31], "icon" => "bi bi-headset", "text" => "Callcenter", "view" => "worktime", "active" => $nav_active],
		["perm" => [2, 3, 5], "icon" => "bi bi-unlock", "text" => "Freischalten", "view" => "activation", "active" => $nav_active],
		["perm" => [2, 3, 19], "icon" => "bi bi-map", "text" => "Map", "view" => "map", "active" => $nav_active],
		["perm" => [32], "icon" => "bi bi-house-check", "text" => "App Checker", "view" => "appcheck", "active" => $nav_active],
		["perm" => [35], "icon" => "bi bi-binoculars", "text" => "Technican", "view" => "technican", "active" => $nav_active],
		["perm" => [2, 3, 7, 21], "icon" => "bi bi-ticket-perforated", "text" => "Ticket", "view" => "tickgeter", "active" => $nav_active],
		["perm" => [2, 3, 27, 7, 22], "icon" => "bi bi-house-heart", "text" => "HBG Module", "view" => "hbgmodul", "active" => $nav_active]
	];

	foreach ($menuItems as $item) {
		if (hasPerm($item["perm"])) {
			echo '<a href="route.php?view=' . $item["view"] . '" class="nav-button ' . ($item["active"] === $item["view"] ? 'aktive' : '') . '"><i class="' . $item["icon"] . ' nav-icon"></i><span>' . $item["text"] . '</span></a>';
		}
	}

	?>

	<hr />
	<div id="nav-content-highlight"></div>
</div>
