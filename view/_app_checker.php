<?php

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}
if (!securePage($_SERVER['PHP_SELF'])) {
    die();
}
if (!hasPerm([2, 7, 32])) {
    die();
}
?>
<style>

</style>

<script>
    $(document).ready(function() {
        $("#_app_checker_err, #_app_checker_call").click(function() {
            // Identifizieren, welcher Button gedrückt wurde
            var buttonId = $(this).attr("id");

            // Spezifischen Pfad zur PHP-Datei angeben
            var filePath = "/view/" + buttonId + ".php";

            // PHP vom spezifischen Pfad einbinden
            $("#phpContent").load(filePath);

            // Buttons kleiner und in die obere linke Ecke verschieben
            $(".button-container button").addClass("small-button");
        });
    });
</script>

<link rel="stylesheet" type="text/css" href="view/includes/style_app.css?=v1.3">
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>Button Beispiel</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
</head>

<body>

    <div class="button-container">
        <button id="_app_checker_err"><i class="fa fa-home"></i> Fehlerprüfung</button>
        <button id="_app_checker_call"><i class="fa fa-phone"></i> Terminprüfung</button>
    </div>

    <!-- Bereich für die eingebundene PHP -->
    <div id="phpContent"></div>

</body>

</html>