<?php
if (file_exists("install/index.php")) {
	//perform redirect if installer files exist
	//this if{} block may be deleted once installed
	header("Location: install/index.php");
}

require_once './users/init.php';

require_once $abs_us_root . $us_url_root . './users/includes/template/prep.php';
if (isset($user) && $user->isLoggedIn()) {
}

if ($user->isLoggedIn()) {
	echo '<script type="text/JavaScript"> 
			window.location.replace("route.php");
			</script>';

?>

<?php } else {
	echo '<script type="text/JavaScript"> 
			window.location.replace("route.php");
			</script>'; ?>

<?php }
?> 

<?php


?>



<!-- Place any per-page javascript here -->