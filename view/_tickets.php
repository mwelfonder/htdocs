<?php

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}
if (!securePage($_SERVER['PHP_SELF'])) {
    die();
}
if (!hasPerm([2, 7])) {
    die();
}

?>


<script type="text/javascript" src="view/includes/js/app_tickets.js?=v2.1"></script>

<div class="body-content-app" id="body-content-app">
    <div class="row app-phoner-wrapper">
        <div class="row app-phonerapp-topbar">
            <div class="app-phonerapp-topbar-wrapper">
                <div class="col customer-head">
                    <div class="head-info-wrapper">
                        <div class="phoner-head info"><i class="ri-profile-line"></i><span><b> HomeID </b></span>
                            <span style="cursor: pointer;" id="head-homeid">HEHE000998</span>
                        </div>
                        <div class="phoner-head info"><i class="ri-home-3-line"></i><b> AdressID </b>
                            <span id="head-adressid"></span>
                        </div>
                        <div class="phoner-head info"><i class="ri-none"></i><b> CLIENT </b>
                            <span id="head-statusnri" class="cspill blue">OPEN</span>
                        </div>
                        <div class="phoner-head info">
                            <i class="ri-none"></i><b>SCAN4 </b>
                            <span id="head-statussc4" class="cspill blue">OPEN</span>
                        </div>
                        <div class="phoner-head info">
                            <i class="ri-none"></i><b>Zuletzt geöffnet </b>
                            <span id="head-status-lastopend" class="cspill">27.11.'22</span>
                        </div>
                    </div>
                    <div class="progress-wrapper">
                        <div id="progressitem1" class="progress-item status blue"><i class="ri-bookmark-2-line"></i><span> OPEN</span></div>
                        <div id="progressitem2" class="progress-item anruf"><i class="ri-phone-line"></i><span> Anruf 1</span></div>
                        <div id="progressitem3" class="progress-item anruf"><i class="ri-phone-line"></i><span> Anruf 2</span></div>
                        <div id="progressitem4" class="progress-item anruf"><i class="ri-phone-line"></i><span> Anruf 3</span></div>
                        <div id="progressitem5" class="progress-item anruf"><i class="ri-phone-line"></i><span> Anruf 4</span></div>
                        <div id="progressitem6" class="progress-item anruf"><i class="1-phone-line"></i></i><span> Anruf 5</span></div>
                        <div id="progressitem7" class="progress-item email"><i class="ri-at-line"></i><span> Mail</span></div>
                        <div id="progressitem8" class="progress-item einwurf"><i class="ri-mail-send-line"></i><span> Einwurf</span></div>
                        <div id="progressitem9" class="progress-item hbg"><i class="ri-user-shared-line"></i><span> HBG</span></div>
                    </div>
                </div>
            </div>
            <div class="app-phonerapp-topbar-interact-wrapper">

            </div>
        </div>
        <div class="row app-ticketapp-content">
            <div class="col-3 app-phonerapp-sidebar">
                <div class="app-sidebar-content-phonerapp">
                    <div class="phoner-customer-heading">Adressdetails</div>
                    <div class="phoner-customer-detaillist">
                        <table>
                            <tbody class="phoner-clientinfo">
                                <tr>
                                    <td><b>Name: </b></td>
                                    <td id="phonerinfo_name">Gökpekin, Emre </td>
                                </tr>
                                <tr>
                                    <td><b>Straße: </b></td>
                                    <td id="phonerinfo_street">Carl-Friedrich-Goerdeler 15</td>
                                </tr>
                                <tr>
                                    <td><b>Ort: </b></td>
                                    <td id="phonerinfo_city">63150 Heusenstamm</td>
                                </tr>
                                <tr>
                                    <td><b>Unit: </b></td>
                                    <td id="phonerinfo_units">8 von 0</td>
                                </tr>
                                <tr>
                                    <td><b>Tel.: </b></td>
                                    <td id="phonerinfo_phone1"><a href="tel:+4915779789247" class="phoner-callnow">15779789247</a></td>
                                </tr>
                                <tr>
                                    <td><b>Tel.: </b></td>
                                    <td id="phonerinfo_phone2" class="hidden"></td>
                                </tr>
                                <tr></tr>
                                <tr class="phonerinfo_dp">
                                    <td><b>DP: </b></td>
                                    <td id="phonerinfo_dp"></td>
                                </tr>
                                <tr id="phonerinfo_priorow" class="hidden">
                                    <td><b>Prio:&nbsp;</b><i class="ri-star-fill"></i>&nbsp;</td>
                                    <td id="phonerinfo_priocount"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="phonerapp-sidebarspace">
                        <div id="carrierwrapper">
                            <div id="carrier-logo" class="carrier-gvg"></div>
                        </div>
                    </div>

                </div>

            </div>
            <div class="col app-phoner-main" id="app-phoner-main">
                <div class="app-phoner-city-wrapper">
                    <div class="col customer-log">
                        <div class="row">
                            <div class="col phonerapp-visualinteract">
                                <div class="phoner-timeline-wrapper">
                                    <div id="timeline_head_main" class="timeline-head timeline active"><i class="ri-history-line"></i> Timeline</div>
                                    <div id="timeline_head_relation" class="timeline-head relations disabled"><i class="ri-arrow-left-right-line"></i> Relation<span id="relationcounter" class="timelineheadcounter hidden"></span></div>
                                    <div id="timeline_head_anfrufe" class="timeline-head calls"><i class="ri-phone-line"></i> Anrufe</div>
                                    <div id="timeline_head_hbg" class="timeline-head hbg disabled"><i class="ri-calendar-check-line"></i> HBG<span id="hbgscounter" class="timelineheadcounter hidden"></span></div>
                                    <div id="timeline_head_abbruch" class="timeline-head abbruch"><i class="ri-close-circle-line"></i> Abbruch</div>
                                </div>
                                <div class="row phonerapp-visualblock-wrapper tickets">
                                    <div class="col phonerapp-visualblock">
                                        <div id="holder-relations" class="timeline-holder relations hidden"></div>
                                        <div id="holder-hbgitems" class="timeline-holder hbgitems  hidden">
                                            <div id="hbgitemsys" class="hbgitem hidden">
                                                <div id="syshbgitemtext" class="hbgitemtextwrap">
                                                    <div class="item-inner-box"><i class="fa-regular fa-image"></i></div>
                                                    <span class="hbgitemtext">HBG im System gefunden</span>
                                                </div>
                                                <div class="row imgwrapper">
                                                    <div class="itemwrapper"><a id="href-hbg-item-1" href="" target="_blank" rel="noopener noreferrer"><img class="hbgkeyitem" id="hbg-item-1" src=""></a></div>
                                                    <div class="itemwrapper"><a id="href-hbg-item-2" href="" target="_blank" rel="noopener noreferrer"><img class="hbgkeyitem" id="hbg-item-2" src=""></a></div>
                                                    <div class="itemwrapper"><a id="href-hbg-item-3" href="" target="_blank" rel="noopener noreferrer"><img class="hbgkeyitem" id="hbg-item-3" src=""></a></div>
                                                    <div class="itemwrapper"><a id="href-hbg-item-4" href="" target="_blank" rel="noopener noreferrer"><img class="hbgkeyitem" id="hbg-item-4" src=""></a></div>
                                                    <div class="itemwrapper"><a id="href-hbg-item-5" href="" target="_blank" rel="noopener noreferrer"><img class="hbgkeyitem" id="hbg-item-5" src=""></a></div>
                                                    <div class="itemwrapper"><a id="href-hbg-item-6" href="" target="_blank" rel="noopener noreferrer"><img class="hbgkeyitem" id="hbg-item-6" src=""></a></div>
                                                    <div class="itemwrapper"><a id="href-hbg-item-7" href="" target="_blank" rel="noopener noreferrer"><img class="hbgkeyitem" id="hbg-item-7" src=""></a></div>
                                                    <div class="itemwrapper"><a id="href-hbg-item-8" href="" target="_blank" rel="noopener noreferrer"><img class="hbgkeyitem" id="hbg-item-8" src=""></a></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="holder-timeline" class="timeline-holder timeline">
                                            <ul class="list-group phoner-timeline" id="timeline">
                                                <li class="list-group-item emptyentry">
                                                    <div class="notimeline"><span><i class="ri-ghost-line"></i> Zu diesem Kunden gibt es noch keine Einträge</span></div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col phonerapp-interact">

                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </div> <!-- content -->
        <div class="app-ticketapp-done row">
            <div class="ticketapp-done-animation-wrapper row hidden">
                <div class="ticketapp-done-animation col">
                    <img src="view/images/animation_ghost.gif" />
                </div>
                <div class="ticketapp-done-desc col">
                    <i class="ri-coupon-2-line"></i>Ticket wurde erstellt
                </div>
            </div>
        </div>
        <div class="app-phonerapp-ticket-wrapper row">
            <div class="app-ticket-left col">
                <ul id="app_tickets_ticketprio" class="list-group ticketswrapper">
                    <li id="ticketprio1" class="list-group-item d-flex ticketprio">
                        <span>1 - <b>Urgent</b></span>
                        <span class="badge badge-pill tickets urgent">24h</span>
                    </li>
                    <li id="ticketprio2" class="list-group-item d-flex ticketprio">
                        <span>2 - <b>Important</b></span>
                        <span class="badge badge-pill tickets important">3days</span>
                    </li>
                    <li id="ticketprio3" class="list-group-item d-flex ticketprio">
                        <span>3 - <b>Normal</b></span>
                        <span class="badge badge-pill tickets normal">7days</span>
                    </li>
                </ul>
            </div>
            <div class="app-ticket-ticket col">
                <div class="ticket-textwrapper">
                    <textarea placeholder="Ticket Beschreibung" rows="20" name="ticket_text" id="ticket_text" cols="40" class="ui-autocomplete-input" autocomplete="off" role="textbox" aria-autocomplete="list" aria-haspopup="true"></textarea>
                </div>

            </div>
            <div class="app-ticket-right col">
                <div id="ticketsubmit" class="btn-phonerapp-loadnext isset ticket">
                    <span><i class="ri-send-plane-line"></i> Absenden</span>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

 


<style>
    .mod_wrapper {
        width: 80vw;
        height: 90vh;
        border-top: 8px solid #4db5ff;
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
        background: #fff;
        margin-left: auto;
        margin-right: auto;
        overflow-y: scroll;
        display: flex;
        flex-direction: column;
    }

    .mod_header {
        padding: 15px;
        border-bottom: 1px solid #e9e9e9;
    }

    .mod_title_header {
        display: flex;
        flex-direction: column;
    }

    .mod_title_heading {
        font-size: 18px;
        font-weight: 600;
    }

    .mod_title_sm_header {
        font-size: 14px;
        color: #89939d;
        margin: 0;
    }

    span#md_tck_cd_ticketID {
        font-size: 16px;
        margin-left: 10px;
    }

    span.mod_toggleprivatestate {
        color: #004ea1;
        border: 1px solid #004ea1;
        padding: 2px 4px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 12px;
    }

    .col-md-8.task-single-col-left {
        margin-top: 10px;
        margin-bottom: 30px;
    }

    .task_inf_bodyheader {
        font-weight: 700;
        font-size: 16px;
    }

    .task_inf_desceditbtn {
        cursor: pointer;
        color: #2c65ec;
    }

    .taks_inf_bodydesctext {
        padding: 15px 0px;
    }

    .col-md-4.task-single-col-right {
        background: #f8fafc;
        padding: 10px 15px;
    }

    .task_inf_createdby {
        margin: 0;
    }

    .tck_statusfield {
        border: 2px solid;
        border-radius: 4px;
        padding: 2px 4px;
        font-size: 12px;
        text-transform: uppercase;
        background: #65a1cd;
        color: #fff;
    }

    .linespacer {
        width: 100%;
        border-bottom: 1px solid #e7e7e7;
        padding: 5px 0px;
        margin-bottom: 5px;
    }

    img.taks_inf_descimg {
        cursor: pointer;
    }

    img.taks_inf_descimg.descimgX1 {
        max-width: 10%;
    }

    img.taks_inf_descimg.descimgX2 {
        max-width: 15%;
    }

    img.taks_inf_descimg.descimgX3 {
        max-width: 20%;
    }

    .centered-image {
        display: block;
        margin-left: auto;
        margin-right: auto;
        max-height: 80vh;
    }

    #originalImageLink {
        color: #007bff;
        text-align: center;
    }

    .task_inf_bodydescimgtoggle {
        color: #2c65ec;
        font-size: 13px;
        cursor: pointer;
        margin-bottom: 6px;
        user-select: none;
    }

    .task_inf_bodydescimgconfwrapper {
        display: flex;
    }

    .task_inf_bodydescimgtotal {
        margin-left: 15px;
        font-size: 13px;
        font-weight: 500;
    }

    .infoboard_timelinewrapper {
        padding: 8px;
        margin: 0;
        margin-top: 15px;
        border: 1px solid #dadce0;
        border-radius: 4px;
        overflow-y: scroll;
        height: 95%;
        background: #f5f5f5;
    }

    .tast_info_binfotextvalues.fileitem {
        color: #0081f4;
        cursor: pointer;
    }

    .dropAreaLabel {
        border: 2px dashed #2c6aee;
        padding: 10px;
        text-align: center;
        background: #ffff;
        margin: 12px 0px;
        border-radius: 8px;
        cursor: pointer;
        width: 100%;
    }

    .task_listfiledetails {
        color: #959595;
        font-size: 12px;
    }

    .tastk_listfileitem {
        cursor: pointer;
        width: fit-content;
    }

    .mod_layerbg {
        background: hsl(0deg 0% 16% / 70%);
        position: fixed;
        top: 0px;
        left: 0px;
        width: 100%;
        height: 100%;
        z-index: 2147483647;
        padding-top: 2%;
    }


    .dropAreaLabel {
        border: 2px dashed #2c6aee;
        padding: 10px;
        text-align: center;
        background: #ffff;
        margin: 12px 0px;
        border-radius: 8px;
        cursor: pointer;
        width: 100%;
    }

    .uploadText {
        display: flex;
        justify-content: space-between;
    }


    .task_deletefile {
        opacity: 0;
    }

    .tastk_listfileitem:hover .task_deletefile,
    .task_deletefile:hover {
        opacity: 1;
    }

    .tastk_listfileitem {
        position: relative;
    }

    .image_preview {
        display: none;
        position: absolute;
        top: 0;
        left: 100%;
        border: 1px solid #ccc;
        padding: 5px;
        background: #fff;
        border-radius: 5px;
        z-index: 100;
    }

    .image_preview img {
        max-width: 200px;
        max-height: 200px;
    }

    .dynamicTicketBody {
        background: #c7c7c7;
        border-radius: 3px;
        min-height: 30vh;
        padding: 5px 10px;
    }

    #dynamicContent {
        position: absolute;
        top: 100%;
        border: 1px solid rgb(204, 204, 204);
        background: rgb(255, 255, 255);
        width: 100%;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 400;
    }

    #dynamicContent.shadowVisible {
        box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
    }

    .dynamicLoadItem_searchbar {
        display: flex;
        justify-content: space-between;
        padding: 5px;
        border-bottom: 1px solid #f1f1f1;
        cursor: pointer;
    }

    .dynamicLoadItem_searchbar:hover {
        background: #e1e1e1;
    }

    mark {
        background-color: #ffef3b;
        color: black;
        padding: 0;
    }

    .dynamicLoadItem_searchbar>div {
        white-space: nowrap;
        display: flex;
        align-items: center;
        justify-content: flex-start;
    }

    .dynamicLoadTicketItem:hover {
        background: #f1f1f1;
        box-shadow: none;
    }

    .dynamicLoadTicketItem {
        padding: 10px;
        margin-bottom: 10px;
        background: #fff;
        border-radius: 3px;
        border-top: 5px solid #3d9ddd;
        border-top-left-radius: 6px;
        border-top-right-radius: 6px;
        font-size: 13px;
        box-shadow: rgba(0, 0, 0, 0.15) 1.95px 1.95px 2.6px;
        cursor: pointer;
        transition: color 0.2s ease;
    }

    .dynamicLoadTicketEmpty>i {
        font-weight: 500;
        font-size: 34px;
    }

    .dynamicLoadTicketEmpty {
        text-align: center;
        font-size: 16px;
        font-weight: 600;
    }

    .dynamicTicketBody.centered {
        background: #c7c7c7;
        border-radius: 3px;
        min-height: 30vh;
        padding: 5px 10px;
        align-items: center;
        display: flex;
        justify-content: center;
    }

    .ticket-info,
    .ticket-date-info {
        display: flex;
        align-items: center;
    }

    .ticket-info>div,
    .ticket-date-info>div {
        flex: 1;
    }
</style>