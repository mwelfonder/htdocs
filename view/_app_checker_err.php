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
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.3.2/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.6.347/pdf.min.js" integrity="sha512-Z8CqofpIcnJN80feS2uccz+pXWgZzeKxDsDNMD/dJ6997/LSRY+W4NmEt9acwR+Gt9OHN0kkI1CTianCwoqcjQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.6.347/pdf.min.js" integrity="sha512-Z8CqofpIcnJN80feS2uccz+pXWgZzeKxDsDNMD/dJ6997/LSRY+W4NmEt9acwR+Gt9OHN0kkI1CTianCwoqcjQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.6.347/pdf.worker.min.js" integrity="sha512-lHibs5XrZL9hXP3Dhr/d2xJgPy91f2mhVAasrSbMkbmoTSm2Kz8DuSWszBLUg31v+BM6tSiHSqT72xwjaNvl0g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.6.347/pdf_viewer.min.css" integrity="sha512-5cOE2Zw/F4SlIUHR/xLTyFLSAR0ezXsra+8azx47gJyQCilATjazEE2hLQmMY7xeAv/RxxZhs8w8zEL7dTsvnA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<!-- Magnific Popup CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/4.3.0/fabric.min.js"></script>

<!-- Magnific Popup JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js"></script>


<script type="text/javascript" src="view/includes/js/app_checker.js"></script>
<link rel="stylesheet" type="text/css" href="view/includes/style_app.css">


<div class="leftContainer">
    <div class="calendar-container">
        <input type="date" id="selectedDate" value="<?php echo date('Y-m-d'); ?>">
    </div>
    <ul id="userList">
        <!-- Benutzer-Items werden hier aufgelistet -->
    </ul>
    <div id="contextMenu" class="context-menu" style="display: none;">
        <ul>
            <li id="sendEmail">E-Mail senden</li>
        </ul>
    </div>


</div>
<div class="rightContainer">
    <!-- Hier werden die geladenen Werte angezeigt -->
</div>
<!-- Platzierung nach Ihrem vorhandenen rightContainer -->
<div class="pdf-controls">
    <button class="magnify-toggle-btn"><i class="bi bi-search"></i> Lupe aktivieren</button>
    <input type="range" class="magnify-size-slider" min="100" max="300" value="200">
    <div class="pdf-nav-left"><i class="bi bi-chevron-compact-left"></i></div>
    <span class="page-indicator">Seite: 1 von 1</span>
    <div class="pdf-nav-right"><i class="bi bi-chevron-compact-right"></i></div>
    <button class="screenshot-btn"><i class="bi bi-image"></i> Screenshot machen</button>
    <button class="report-error-btn"><i class="bi bi-flag-fill"></i> Fehler ohne Foto melden</button>
    <button class="all-errors-found-btn"><i class="bi bi-send-fill"></i> Alle Fehler gefunden</button>

</div>