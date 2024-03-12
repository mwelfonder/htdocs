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

ob_start();
?>

<script type="text/javascript" src="view/includes/js/navbar_hbg.js"></script>
<link rel="stylesheet" href="view/includes/style_hbg.css">


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
</nav>

<div class="clearfix"></div>

