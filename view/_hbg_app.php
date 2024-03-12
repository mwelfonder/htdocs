<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}

if (!securePage($_SERVER['PHP_SELF'])) {
    die();
}

$data = $user->data();
$currentuser = $data->fname;
$fname = $data->fname;
$lastname = $data->lname;
?>




<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.4.1/MarkerCluster.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.4.1/MarkerCluster.Default.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.4.1/leaflet.markercluster.js"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script type="text/javascript" src="view/includes/js/app_hbg_app.js"></script>
<link rel="stylesheet" href="view/includes/style_hbgapp.css">





<div class="content-wrapper">
    <input type="text" id="datePicker" placeholder="Datum auswählen">
    <div id="appointmentsList"></div>
</div>
<div id="map" style="height: 100vh;">
    <div class="detail-window-container"></div>
</div>



<div class="home-button-container">
    <form action="/" method="get"> 
        <button class="button home-button" type="submit">
            <i class="fa-solid fa-circle-left"></i>
        </button>
    </form>
</div>


<style>

</style>
<script>

</script>