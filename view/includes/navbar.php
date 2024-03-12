<?php
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}


if (hasPerm(26)) { // ticket_create
	//include_once $_SERVER['DOCUMENT_ROOT'] . '/view/includes/tickets.php';
}

$logged_in = $user->data();
$currentuser = $logged_in->username;



if (hasPerm(2)) {
	include_once 'view/includes/templates/projetc_tools_modal.php';
	$jsVersion = filemtime($_SERVER['DOCUMENT_ROOT'] . '/view/includes/js/projects_tool.js');
	echo '<script type="text/javascript" src="view/includes/js/projects_tool.js?v=' . $jsVersion . '"></script>';
}



ob_start();
?>

<script type="text/javascript" src="view/includes/js/navbar.js?v=1.9"></script>




<nav id="navbar-wrapper" class="navbar-wrapper">
	<div class="navbar-content">
		<div class="navbar-left-wrap">
			<div class="navbar-search">
				<div class="navbar-search-content">
					<input id="navbar-search-input" placeholder="Suche..." class="form-control" autocomplete="off"></input>
				</div>
				<div id="navbar_results_wrapper" class="navbar-search-result-wrapper hidden">
					<div id="navbar_result" class="navbar-search-result-content"></div>
				</div>

			</div>
		</div>
	</div>
	</div>
</nav>

<div class="clearfix"></div>


