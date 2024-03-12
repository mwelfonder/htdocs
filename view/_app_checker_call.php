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
<script type="text/javascript" src="view/includes/js/app_checker_call.js?v=<?php echo time(); ?>"></script>
<link rel="stylesheet" type="text/css" href="view/includes/style_app_call.css?v=<?php echo time(); ?>">
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<body>
    <div class="menu-container">

        <div class="carousel-container">
            <div class="date-selector-container">
                <input type="date" id="dateSelector" value="">
            </div>
            <div class="tiles-container">
                <!-- Die Kacheln werden hier durch JavaScript eingefügt -->
            </div>
        </div>
    </div>

    <div id="customerDetailsModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Kundendetails</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Kundendaten -->
                    <div id="customerInfo"></div>
                    <!-- Termininformationen -->
                    <div id="appointmentInfo"></div>
                    <!-- Kommentarfeld -->
                    <textarea id="commentField" placeholder="Kommentar..."></textarea>
                    <!-- Bewertung -->
                    <select id="ratingSelect">
                        <option value="">Bewertung wählen</option>
                        <option value=" ">KD kann nicht bewerten</option>
                        <option value="1">0</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                        <option value="7">7</option>
                        <option value="8">8</option>
                        <option value="9">9</option>
                        <option value="10">10</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button id="notReachedButton" type="button" class="btn btn-secondary">Nicht Erreicht</button>
                    <button id="saveButton" type="button" class="btn btn-primary">Speichern</button>
                </div>
            </div>
        </div>
    </div>
</body>