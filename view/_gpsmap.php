<?php
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}

$dir = $_SERVER['PHP_SELF'];
if (!securePage($_SERVER['PHP_SELF'])) {
    die();
}
if (!hasPerm([2])) {
    die();
}

$logged_in = $user->data();
$currentuser = $logged_in->username;

?>

<script type="text/javascript" src="view/includes/js/gpsmap.js?v=<?php echo time(); ?>"></script>
<link rel="stylesheet" type="text/css" href="view/includes/gpsmap.css?v=<?php echo time(); ?>">
</script>
<div id="gps_map_20240128"></div>
<div id="countdownWrapper" class="countdown-wrapper">
    <div id="countdownCircle" class="countdown-circle">
        <span id="countdownTimer" class="countdown-timer">10</span>
        <i id="countdownIcon" class="fas fa-pause countdown-icon"></i>
    </div>
</div>

<button id="toggle-users-panel" class="toggle-users-panel">
    <i class="fas fa-users"></i> <!-- Beispiel mit Font Awesome Icons -->
</button>

<div id="users-panel" class="users-panel">
    <!-- Inhalt des Fensters -->
</div>