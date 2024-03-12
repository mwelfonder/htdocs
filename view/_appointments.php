<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';

if (!securePage($_SERVER['PHP_SELF'])) {
    die();
}


$logged_in = $user->data();
$currentuser = $logged_in->username;
?>

<script type="text/javascript" src="view/includes/js/app_activation.js"></script>
<div class="body-content-app" id="body-content-app">
    <div id="activation_wrapper" class="row app-wrapper activation ">
        <div id="loaderwrapper" class="fullwidth aligncenter hidden">
            <div class="appt-loader loader"></div>
        </div>
        <div class="row app-content-wrapper scrollwrapper">
            <div class="row ticketapp-headerwrapper">
                <div class="h4">Erstellte Termine</div>

                <div class="filter-wrapper ticketapp">
                    <span id="btn_filter_ugg">
                        <span class="btnfilter-check ugg"><i class="ri-checkbox-fill"></i></span>
                        <span class="btnfilter-text">UGG</span>
                    </span>
                    <span id="btn_filter_dgf">
                        <span class="btnfilter-check dgf"><i class="ri-checkbox-fill"></i></span>
                        <span class="btnfilter-text">DGF</span>
                    </span>
                    <span id="btn_filter_gvg">
                        <span class="btnfilter-check gvg"><i class="ri-checkbox-fill"></i></span>
                        <span class="btnfilter-text">GVG</span>
                    </span>
                </div>
                <div class="filter-wrapper ticketapp carrier">
                    <span id="btn_filter_moncobra">
                        <span class="btnfilter-check moncobra"><i class="ri-checkbox-fill"></i></span>
                        <span class="btnfilter-text">Moncobra</span>
                    </span>
                    <span id="btn_filter_insyte">
                        <span class="btnfilter-check insyte"><i class="ri-checkbox-fill"></i></span>
                        <span class="btnfilter-text">Insyte</span>
                    </span>
                </div>
            </div>
            <div class="row app-tickets-tablewrapper activetable scrollwrapper">
                <div class="tablewrapper active fullwidth">
                    <table id="tbody_active" class="activation-table" style="width:100%">
                        <thead style="text-align:left;">
                            <th style="display:none;" class="ticket-head left">Status</th>
                            <th style="display:none;" class="ticket-head left"><i class="ri-external-link-line"></i></th>
                            <th class="ticket-head left">HomeID</th>
                            <th class="ticket-head left">User</th>
                            <th class="ticket-head left">Ort</th>
                            <th class="ticket-head left">Straße</th>
                            <th class="ticket-head left">Kommentar</th>
                            <th class="ticket-head left"><i class="ri-save-3-line"></i></th>
                            <th class="ticket-head left">Datum</th>
                            <th class="ticket-head left">Uhrzeit</th>
                            <th class="ticket-head left">Wer</th>
                            <th class="ticket-head left">Team</th>
                        </thead>
                        <tbody class="">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div id="hbgcheck_empty" class="row app-wrapper hbgcheck _empty hidden">
        <div class="hbgcheckempty">Keine HBGs zum Überprüfen gefunden</div>
    </div>
</div>


<script>
    // hide the activation and link column after ajax success
    $(document).on('ajaxSuccess', function() {
        if ($('#tbody_active').is(':visible')) {
            $('#tbody_active tr').each(function() {
                $(this).find('td:eq(0), td:eq(1)').hide();
            });
        }
    });
</script>