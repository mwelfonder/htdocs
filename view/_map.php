<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';

if (!(hasPerm([2, 3, 19]))) {
    die();
}



setlocale(LC_TIME, 'de_DE.utf8');

include_once $_SERVER['DOCUMENT_ROOT'] . '/view/includes/functions.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/view/includes/tickets.html';

$begeher = fetchPermissionUsers(6); // 6 = hausbegeher
$currentuser = $logged_in->username;

//echo 'testing ';
// http://49.12.77.77:8989/route?point=51.311286%2C7.461644&point=51.045265%2C7.009831&profile=car&layer=OpenStreetMap




$permLvl = hasPerm(2) ? 2 : 4;
echo '<div id="myusername" class="hidden">' . $logged_in->username . '</div>';
echo '<div id="permlvl" class="hidden">' . $permLvl . '</div>';

// generates a permission array so this exact same function can be used in js
function generatePermsArray()
{
    $permsArray = [];
    for ($i = 1; $i <= 30; $i++) {
        $permsArray[$i] = hasPerm($i);
    }
    return $permsArray;
}
$permsArray = generatePermsArray();
$json_permissions = json_encode($permsArray);


$begeher = getlist($begeher);
function getlist($begeher)
{

    // Get the current days for 3 weeks
    $now = new DateTime();
    $today = date('Y-m-d');
    $nowtime = date('H:i');

    $kw0 = date('W');
    $kw1 = date('W', strtotime('+1 week'));
    $kw2 = date('W', strtotime('+2 weeks'));
    $kw3 = date('W', strtotime('+3 weeks'));
    $kw4 = date('W', strtotime('+4 weeks'));
    $kw5 = date('W', strtotime('+5 weeks'));
    $kw6 = date('W', strtotime('+6 weeks'));
    $kw7 = date('W', strtotime('+7 weeks'));

    $fourWeeksAgo = (new DateTime())->modify('-4 weeks')->format('Y-m-d');

    $conn = dbconnect();
    $hbgdata = array();
    foreach ($begeher as $user) {
        $user_id = $user->user_id . "  ";
        $data = fetchUser($user_id);
        $username = $data->username;

        $monday_of_week = strtotime('monday this week');
        for ($i = 0; $i < 100; $i++) {  // loop through 21 days from monday this week
            $date = strtotime('+' . $i . ' days', $monday_of_week);
            $day_of_week = date('N', $date);
            if ($day_of_week <= 6) {
                $week_number = date('W', $date);
                $newdate = date('Y-m-d', $date);
                $hbgdata[$username]['kw' . $week_number][$newdate] = array();
            }
        }

        $query = "SELECT hbg.*, homes.lat, homes.lon FROM scan4_hbg AS hbg JOIN scan4_homes AS homes ON hbg.homeid = homes.homeid WHERE hbg.status = 'PLANNED' AND hbg.hausbegeher = '$username' AND hbg.date >= '$fourWeeksAgo' ORDER BY hbg.date ASC, hbg.time ASC";
        if ($result = $conn->query($query)) {
            while ($row = $result->fetch_assoc()) {
                $date = $row['date'];
                $week_number = date('W', strtotime($date));

                if ($week_number == $kw0 || $week_number == $kw1 || $week_number == $kw2 || $week_number == $kw3 || $week_number == $kw4 || $week_number == $kw5 || $week_number == $kw6 || $week_number == $kw7) {
                    if (isset($hbgdata[$username]['kw' . $week_number]['total']))
                        $hbgdata[$username]['kw' . $week_number]['total']++;
                    else
                        $hbgdata[$username]['kw' . $week_number]['total'] = 1;
                    $hbgdata[$username]['kw' . $week_number][$date][] = $row;
                }
            }
            $result->free_result();
        }
    }

    $conn->close();
    return $hbgdata;
}



function weekBlock($username, $weekOffset, $val, $kw)
{
    $weekNumber = date('W', strtotime("+{$weekOffset} week"));
    $year = date('Y');
    $pegman_data = ['name' => $username, 'week' => $weekNumber];
    for ($day = 1; $day <= 6; $day++) { // Monday to Saturday, skipping Sunday
        $date = date('Y-m-d', strtotime("{$year}-W{$weekNumber}-{$day}"));
        $pegman_data['date' . ($day - 1)] = $date;
    }
    $json_pegman = json_encode($pegman_data);
    $calc_result = 80 - ($val['kw' . $kw]['total'] ?? 0);
    $percentage = ($calc_result / 80) * 100;
    ob_start();
?>
    <div class="multiboxlayout justify">
        <div class="multiboxlayout box">
            <span class="multiboxtypo light calweekbtn">kw<?php echo $weekNumber; ?></span>
        </div>
        <div class="pickmeup weekman" data-json='<?php echo $json_pegman; ?>'></div>
        <div class="multiboxlayout box textright">
            <div class="">
                <p style="margin: 0;padding:0;">
                    <span class="multiboxtypo strong small">
                        <?php
                        echo ($val['kw' . $kw]['total'] ?? 0) . " / 80 = ";
                        echo '<span class="kwslotsopen">' . $calc_result . '</span>';
                        ?>
                    </span>
                </p>

                <p style="margin: 0;padding:0;">
                    <span class="multiboxtypo progressbarwrapper small">
                        <span style="transform:translateX(-<?php echo $percentage ?>%);" class="multiboxtypo progressbar"></span>
                    </span>
                </p>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}



$kw0 = date('W');
$kw1 = date('W', strtotime('+1 week'));
$kw2 = date('W', strtotime('+2 weeks'));
$kw3 = date('W', strtotime('+3 weeks'));
$kw4 = date('W', strtotime('+4 weeks'));
$kw5 = date('W', strtotime('+5 weeks'));
$kw6 = date('W', strtotime('+6 weeks'));
$kw7 = date('W', strtotime('+7 weeks'));

?>



<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/leaflet.markercluster.js" integrity="sha512-OFs3W4DIZ5ZkrDhBFtsCP6JXtMEDGmhl0QPlmWYBJay40TT1n3gt2Xuw8Pf/iezgW9CdabjkNChRqozl/YADmg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.Default.min.css" integrity="sha512-fYyZwU1wU0QWB4Yutd/Pvhy5J1oWAwFXun1pt+Bps04WSe4Aq6tyHlT4+MHSJhD8JlLfgLuC4CbCnX5KHSjyCg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js" integrity="sha512-ozq8xQKq6urvuU6jNgkfqAmT7jKN2XumbrX1JiB3TnF7tI48DPI4Gy1GXKD/V3EExgAs1V+pRO7vwtS1LHg0Gw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script type="text/javascript" src="view/includes/js/map.js?v=5.4"></script>
<link rel="stylesheet" type="text/css" href="view/includes/styles_map.css?v=2.9">
<script type="text/javascript" src="view/includes/js/app_tickets.js"></script>

<style>
    .loader_wrap {
        position: fixed;
        /* Ändern Sie 'absolute' zu 'fixed' */
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 1);
        /* Leicht transparenter Hintergrund */
        z-index: 99999999;
        display: flex;
        justify-content: center;
        align-items: center;
        text-align: center;
    }

    .no-scroll {
        overflow: hidden;
    }

</style>




<div class="pagewrapper" style="position: relative;">
    <div class="loader_wrap">
        <div class="loader"></div>
    </div>

    <div class="mapwrapper">

        <div class="row">
            <div class="col-2" id="map_userplateswrapper" style="padding: 0;z-index: 1001;">
                <div class="map_userplateswrapper">

                    <div id="userplates" class="col-12">
                        <div class="row" id="plateswrapperinner">
                            <?php

                            foreach ($begeher as $username => $val) {
                            ?>
                                <div class="col-auto multiboxwrapper marginbox userbox" id="<?php echo $username; ?>">
                                    <div class="multiboxinner">
                                        <div class="multiboxtitle justifybetween"><span class="textalignleft"><?php echo $username; ?></span></div>
                                        <div class="multiboxbody">
                                            <?php
                                            echo weekBlock($username, 0, $val, $kw0);
                                            echo weekBlock($username, 1, $val, $kw1);
                                            echo weekBlock($username, 2, $val, $kw2);
                                            echo weekBlock($username, 3, $val, $kw3);
                                            echo weekBlock($username, 4, $val, $kw4);
                                            echo weekBlock($username, 5, $val, $kw5);
                                            echo weekBlock($username, 6, $val, $kw6);
                                            echo weekBlock($username, 7, $val, $kw7);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <div id="userplatestatsslider">
                        <div id="eventTooltip" style="position: absolute; display: none; background-color: white; padding: 5px; border: 1px solid black; z-index: 10000000;"></div>

                        <div class="slideclosebtn"><i class="ri-close-line"></i></div>
                        <div class="statswrapper">
                            <?php
                            foreach ($begeher as $username => $val) {

                            ?>
                                <div id="<?php echo $username ?>" class="hiddenstats">
                                    <?php
                                    foreach ($val as $key => $row) {
                                        echo '<div id="kalendarweek ' . $key . '" class="statkwwrapper">' . '<div class="row"><div class="col-12 multiboxtitle textaligncenter">' . $username . ' ' . $key . '</div>';
                                        foreach ($row as $date => $values) {
                                            if ($date !== 'total') {
                                                $day = translateToGerman(date('l', strtotime($date)));
                                                $pegman_data = [
                                                    'name' => $username,
                                                    'week' => date('W', strtotime('+2 week')),
                                                    'date' => $date,
                                                    'day' => $day
                                                ];
                                                $json_pegman = json_encode($pegman_data);
                                                echo '<div id="' . $date . '" class="col-2 textaligncenter homeidday">';
                                                echo '<div id="' . $date . '" class="abs_overlay"></div>';
                                                echo '<div class="aligncenter assignedProject"></div>';
                                                echo '<div class="col-auto weekdaytitle">' . getformatteddate($date) . '<div class="pickmeup weekday" data-json=\'' . $json_pegman . '\'></div></div>';
                                                // Add the grid slots for each hour
                                                for ($hour = 7; $hour <= 20; $hour++) { // create a hidden 6 for workstart
                                                    $class = ($hour == 6) ? 'hiddsen' : ''; // Add 'hidden' class if hour is 6
                                                    echo '<div class="hour-slot ' . $class . '" data-hour="' . $hour . '">' . "<span class='slothourindicator'>$hour" . ':00</div>';
                                                }

                                                echo '</div>'; // closer for wrapper
                                                echo '<div class="appointment_calendarwrapper">';
                                            }
                                            if ($date !== 'total') {
                                                echo '</div>';
                                            }
                                        }
                                        echo  "</div></div>";
                                    }
                                    ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col" style="padding: 0;">
                <div id="leaflet" style="height: 90vh; width: 100%;">
                    <div class="map_optionbtnwrapper">
                        <?php if (hasPerm(2)) { ?>

                        <?php } ?>
                        <?php if (hasPerm([5, 13])) { ?>
                            <div id="mapsettingsbtn" class="mapbtn"><i class="ri-settings-3-fill"></i></div>
                            <div id="mapswitcher" class="mapbtn"><i class="ri-community-line"></i></div>
                            <div id="mapswitcher_optionwrapper" style="display:none;">
                                <div class="optionmapswitcher" value="opt1"><i class="ri-user-location-line"></i> Hausbegehung</div>
                                <div class="optionmapswitcher" value="opt1"><i class="ri-home-office-line"></i> Hausanschluss</div>
                            </div>
                            <div id="mapticket" class="mapbtn"><i class="ri-coupon-3-line"></i></div>
                            <div id="mapticket_wrapper" class="mapoptionswrapper" style="display:none;"></div>
                            <div id="mapfilter" class="mapbtn"><i class="ri-equalizer-line"></i></i></div>
                        <?php } ?>

                        <div id="mapfilter_wrapper" class="mapoptionswrapper" style="display:none;">
                            <div class="mapoptionoptionwrap"><label class="mapoptionoption">
                                    <input id="sc4statsselector" type="checkbox" name="sc4statsselector" value="sc4statsselector">
                                    <span>Select / Deselect All</span>
                                </label>
                            </div>
                            <div class="mapoptionoptionwrap"><label class="mapoptionoption">
                                    <input id="openCheckbox" type="checkbox" name="openOption" value="sc4_open" checked>
                                    <div class="optInfcircle open"></div><span>Open</span>
                                    <span id="openCheckboxAll" class="statusCheckboxAll"></span>
                                    <span id="openCheckboxBoundary" class="statusCheckboxAll"></span>
                                </label>
                            </div>
                            <div class="mapoptionoptionwrap"><label class="mapoptionoption">
                                    <input id="plannedCheckbox" type="checkbox" name="openOption" value="sc4_planned" checked>
                                    <div class="optInfcircle planned"></div><span>Planned</span>
                                    <span id="plannedCheckboxAll" class="statusCheckboxAll"></span>
                                    <span id="plannedCheckboxBoundary" class="statusCheckboxAll"></span>
                                </label>
                            </div>
                            <div class="mapoptionoptionwrap"><label class="mapoptionoption">
                                    <input id="pendingCheckbox" type="checkbox" name="openOption" value="sc4_pending">
                                    <div class="optInfcircle pending"></div><span>Pending</span>
                                    <span id="pendingCheckboxAll" class="statusCheckboxAll"></span>
                                    <span id="pendingCheckboxBoundary" class="statusCheckboxAll"></span>
                                </label>
                            </div>
                            <div class="mapoptionoptionwrap"><label class="mapoptionoption">
                                    <input id="doneCheckbox" type="checkbox" name="openOption" value="sc4_done">
                                    <div class="optInfcircle done"></div><span>Done</span>
                                    <span id="doneCheckboxAll" class="statusCheckboxAll"></span>
                                    <span id="doneCheckboxBoundary" class="statusCheckboxAll"></span>
                                </label>
                            </div>
                            <div class="mapoptionoptionwrap"><label class="mapoptionoption">
                                    <input id="overdueCheckbox" type="checkbox" name="openOption" value="sc4_overdue">
                                    <div class="optInfcircle overdue"></div><span>Overdue</span>
                                    <span id="overdueCheckboxAll" class="statusCheckboxAll"></span>
                                    <span id="overdueCheckboxBoundary" class="statusCheckboxAll"></span>
                                </label>
                            </div>
                            <div class="mapoptionoptionwrap"><label class="mapoptionoption">
                                    <input id="stoppedCheckbox" type="checkbox" name="openOption" value="sc4_stopped">
                                    <div class="optInfcircle stopped"></div><span>Stopped</span>
                                    <span id="stoppedCheckboxAll" class="statusCheckboxAll"></span>
                                    <span id="stoppedCheckboxBoundary" class="statusCheckboxAll"></span>
                                </label>
                            </div>
                            <?php if (hasPerm(13)) { ?>
                                <div style="display:flex">
                                    <!--<div class="mapoptionbtngray" id="mapoptionShowData">Show Data</div>-->
                                    <div class="mapoptionbtngray" id="mapoptionExportCSV">Export CSV</div>
                                </div>
                            <?php } ?>
                        </div>


                    </div>

                    <div id="satelite" class="mpbtn mapchange satelite">
                        <div class="lmbg"></div>
                        <div style="position: absolute; bottom: 0; font-weight: 600; left: 50%; transform: translate(-50%); white-space: nowrap;"><i class="ri-stack-line"></i> Ebenen</div>
                    </div>




                    <?php if (hasPerm([34])) { ?>

                        <div id="rankingContainer" class="rankingContainer">
                            <ul id="userRanking" style="padding: 0;margin: 0;">
                            </ul>
                            <!-- <div class="pagination">
                                <span class="pageButton active"></span>
                                <span class="pageButton"></span>
                                <span class="pageButton"></span>
                                Weitere Schaltflächen je nach Anzahl der Seiten 
                            </div>-->

                        </div>

                    <?php } ?>










                </div>
            </div>
        </div>

    </div>

    <div id="infoboardwrapper" style="left:-60%">
        <div class="infoboard_contentparttop">
            <div class="row infoboardheader" style="height: 25px;">
                <?php if (hasPerm(2)) { ?>
                    <div class="infoboard_settings"><i class="ri-settings-3-fill"></i></div>
                <?php } ?>
                <div class="infoboard_zooming"><i class="ri-drag-move-line"></i></div>
                <div class="infoboard_closeme"><i class="ri-close-line"></i></div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="row">
                        <div class="col" style="padding: 0;white-space: nowrap;"><b>Carrier: </b><span id="customer_status_carrier">Test</span></div>
                        <div class="col" style="padding: 0;white-space: nowrap;"><b>Scan4: </b><span id="customer_status_scan4">Test</span></div>
                    </div>
                    <div class="row">
                        <div class="col" style="padding: 0;white-space: nowrap;cursor:pointer;"><b>HomeID: </b><span id="customer_homeid">00123</span><i id="ccthishomeid" class="ri-clipboard-line"></i></div>
                        <div class="col" style="padding: 0;white-space: nowrap;"><b>Unit: </b><span id="customer_unit">1</span></div>
                    </div>
                    <div class="col-12 infoboard_anrufhistowrapper">
                        <div class="infoboard_anrufhisto">1</div>
                        <div class="anrufhisoline"></div>
                        <div class="infoboard_anrufhisto">2</div>
                        <div class="anrufhisoline"></div>
                        <div class="infoboard_anrufhisto">3</div>
                        <div class="anrufhisoline"></div>
                        <div class="infoboard_anrufhisto">4</div>
                        <div class="anrufhisoline"></div>
                        <div class="infoboard_anrufhisto">5</div>
                    </div>

                    <div style="padding-top: 10px;"></div>
                    <div class=""> </div>
                    <div class=""><b>Name: </b><span id="customer_name">Mustermann</span></div>
                    <div class=""><b>Adresse: </b><span id="customer_street">Musterstraße</span></div>
                    <div class=""><b>Mail: </b><span id="customer_mail"></span></div>
                    <div style="padding-top: 5px;"></div>
                    <div class="row">
                        <div class="col" style="padding: 0;"><b>Tel: </b><span id="customer_tel1">+49123</span></div>
                        <div class="col" style="padding: 0;"><b>Tel: </b><span id="customer_tel2">+49123</span></div>
                    </div>
                    <div class="row">
                        <div class="col" style="padding: 0;"><b>Tel: </b><span id="customer_tel3">+49123</span></div>
                        <div class="col" style="padding: 0;"><b>Tel: </b><span id="customer_tel4">+49123</span></div>
                    </div>
                    <div id="infoboard_owner_wrapper">
                        <div class="add_scan4_phone_wrapper">
                            <div class="col" id="add_scan4_phone"><i class="ri-building-line"></i> Eigentümer</div>
                        </div>
                        <div class="row">
                            <div class="col" style="padding: 0;"><b>Name: </b><span id="customer_owner_name">+49123</span></div>
                        </div>
                        <div class="row">
                            <div class="col" style="padding: 0;"><b>Tel: </b><span id="customer_owner_tel1">+49123</span></div>
                            <div class="col" style="padding: 0;"><b>Tel: </b><span id="customer_owner_tel2">+49123</span></div>
                        </div>
                        <div class="row">
                            <div class="col" style="padding: 0;"><b>Mail: </b><span id="customer_owner_mail">mail@mail.de</span></div>
                        </div>
                    </div>
                    <div class="" id="infboard_behinderung"><b>Behinderung:</b> <span id="infboard_behinderung_value"></span></div>
                    <div class="add_scan4_phone_wrapper">
                        <div class="col" id="add_scan4_phone"><i class="ri-phone-line"></i> Scan4</div>
                    </div>
                    <div class="row">
                        <div class="col" style="padding: 0;"><b>Tel: </b><span id="add_scan4_btn1"><i class="ri-add-box-line"></i></span></div>
                        <div class="col" style="padding: 0;"><b>Tel: </b><span id="add_scan4_btn2"><i class="ri-add-box-line"></i></span></div>
                        <div id="addPhonePopupContainer" class="add-phone-popup">
                            <input type="text" id="phoneInputField">
                        </div>
                    </div>
                    <div class="row" style="margin-top: 25px;">
                        <div class="col">
                            <div id="carrier_logo" class="carrier-ugg"></div>
                        </div>
                        <div class="col">
                            <div id="client_logo" class="client_logo client-moncobra"></div>
                        </div>
                    </div>

                </div>
            </div>
            <div style="padding-top: 5px;"></div>
            <div class="row infoboard_tabbar">
                <div class="col">
                    <div class="infoboard_tabbar_header active">Übersicht</div>
                </div>
                <div class="col">
                    <div id="tabbar_relations" class="infoboard_tabbar_header">Relations</div>
                </div>
                <div class="col">
                    <div class="infoboard_tabbar_header">Dokumente</div>
                </div>
                <?php if ($permsArray[2]) { ?>
                    <div class="col ">
                        <div class="infoboard_tabbar_header">Log </div>
                    </div>
                <?php } ?>
            </div>
            <div style="padding-top: 0px;"></div>
            <div class="seperator"></div>
            <div style="padding-top: 15px;"></div>
            <div class="row infoboard_userinteractions">
                <div class="col aligncenter">
                    <div id="addnote" class="infoboardaction addnote"><i class="ri-edit-2-line iconfix"></i></div>
                    <div style="font-size: 14px;text-align:center;">Notiz</div>
                </div>
                <div class="col aligncenter">
                    <div id="nohbg" class="infoboardaction nohbg"><i class="ri-user-unfollow-line iconfix"></i></div>
                    <div style="font-size: 14px;text-align:center;white-space: nowrap;">Keine HBG</div>
                </div>
                <div class="col aligncenter">
                    <div id="followup" class="infoboardaction followup"><i class="ri-arrow-go-forward-line iconfix"></i></div>
                    <span style="font-size: 14px;text-align:center;">Wiedervorlage</span>
                </div>
                <div class="col aligncenter">
                    <div id="infoboard_planbarwrapperbuttons">
                        <div id="planen_move" class="infoboardaction planen_move"><i class="ri-arrow-left-right-line iconfix"></i></div>
                        <div id="planen_cancel" class="infoboardaction planen_cancel"><i class="ri-close-circle-fill iconfix" style="right: 1px;"></i></div>
                        <div id="planen" class="infoboardaction planen"><i class="ri-calendar-2-line iconfix planappoint"></i></div>
                    </div>
                    <span style="font-size: 14px;text-align:center;">Planen</span>
                </div>
                <div style="padding-top: 15px;"></div>
                <div class="seperator"></div>
                <div style="padding-top: 15px;"></div>
                <div id="infoboard_noteblock" class="row">
                    <div class="col">
                        <div>
                            <div id="infoboard_customer_selectioninfo" class="form-control" contenteditable="false" style="height: 30px; overflow-y: auto;"></div>
                            <textarea id="infoboard_customer_note" class="form-control" style="height: 80px; overflow-y: auto;"></textarea>
                            <button id="infoboard_customer_note_save" class="save-button"><i class="ri-save-3-line"></i> Save</button>

                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="infoboard_contentpartbot">
            <div class="row" style="height:95%">
                <div class="col infoboard_timelinewrapper" id="infoboard_timelinewrapper">
                    <div class="empty_timeline" style="padding: 20px;"><i class="ri-ghost-line" style="font-size: 22px;"></i> Zu diesem Kunden gibt es noch keine Einträge.</div>
                </div>
                <div class="col infoboard_timelinewrapper" id="infoboard_relationwrapper">
                    <div class="empty_timeline" style="padding: 20px;"><i class="ri-ghost-line" style="font-size: 22px;"></i> Zu diesem Kunden gibt es keine Verbindungen.</div>
                </div>
                <div class="col infoboard_timelinewrapper" id="infoboard_documentswrapper">
                    <div class="empty_timeline" style="padding: 20px;"><i class="ri-ghost-line" style="font-size: 22px;"></i> Zu diesem Kunden gibt es keine Einträge.</div>
                </div>
                <div class="col infoboard_timelinewrapper" id="infoboard_logwrapper">
                    <div class="empty_timeline" style="padding: 20px;"><i class="ri-ghost-line" style="font-size: 22px;"></i> Zu diesem Kunden gibt es keine Einträge.</div>
                </div>
            </div>
        </div>
    </div>
</div>




<div id="adminpannel" class="" style="display:none;">
    <div class="clbtn"><i class="ri-close-line"></i></div>
    <div class="adm_content_header_wrapper">
        <div class="adm_header_item"><i class="ri-building-line"></i> Projekte</div>
        <div class="adm_header_item"><i class="ri-user-settings-line"></i> Users</div>
        <div class="adm_header_item"><i class="ri-calendar-2-line"></i> Plan</div>
        <div class="adm_header_item"><i class="ri-spy-fill"></i> TopSecret</div>
    </div>

    <div class="adm_pagewrapper">
        <div></div>
    </div>
</div>







<?php










function getformatteddate($date)
{
    $newdate = date('D d.m.', strtotime($date));
    $newdate = str_replace(array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'), array('Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So'), $newdate);
    return $newdate;
}

function translateToGerman($day)
{
    $translations = [
        'Monday' => 'Montag',
        'Tuesday' => 'Dienstag',
        'Wednesday' => 'Mittwoch',
        'Thursday' => 'Donnerstag',
        'Friday' => 'Freitag',
        'Saturday' => 'Samstag',
        'Sunday' => 'Sonntag'
    ];

    return $translations[$day] ?? $day;
}




?>




<!-- 
        <div class="row">
            <div class="dayselector col-auto">
                <div class="multi-button" id="selectweekdays">
                <?php
                $currentDate = strtotime('today');
                ?>
                    <div class="date-btn <?php echo (strtotime('this monday', strtotime('this week'))) < $currentDate ? 'disabled' : ''; ?>">
                        Mo <?php echo date('d.m.', strtotime('this monday', strtotime('this week'))); ?>
                    </div>
                    <div class="date-btn <?php echo (strtotime('this tuesday', strtotime('this week'))) < $currentDate ? 'disabled' : ''; ?>">
                        Di <?php echo date('d.m.', strtotime('this tuesday', strtotime('this week'))); ?>
                    </div>
                    <div class="date-btn <?php echo (strtotime('this wednesday', strtotime('this week'))) < $currentDate ? 'disabled' : ''; ?>">
                        Mi <?php echo date('d.m.', strtotime('this wednesday', strtotime('this week'))); ?>
                    </div>
                    <div class="date-btn <?php echo (strtotime('this thursday', strtotime('this week'))) < $currentDate ? 'disabled' : ''; ?>">
                        Do <?php echo date('d.m.', strtotime('this thursday', strtotime('this week'))); ?>
                    </div>
                    <div class="date-btn <?php echo (strtotime('this friday', strtotime('this week'))) < $currentDate ? 'disabled' : ''; ?>">
                        Fr <?php echo date('d.m.', strtotime('this friday', strtotime('this week'))); ?>
                    </div>

                </div>
            </div>

            <div class="col-auto" style="display: flex; align-items: center;">
                <span class="switchlabel" style="padding-right: 25px;">
                    <div class="markericon open"></div>
                </span>
                <label class="switch">
                    <input id="markersopen" type="checkbox" checked>
                    <span class="slider round"></span>
                </label>
            </div>
            <div class="col-auto" style="display: flex; align-items: center;">
                <span class="switchlabel" style="padding-right: 25px;">
                    <div class="markericon planned"></div>
                </span>
                <label class="switch">
                    <input id="markersplanned" type="checkbox" checked>
                    <span class="slider round"></span>
                </label>
            </div>
            <div class="col-auto" style="display: flex; align-items: center;">
                <span class="switchlabel">
                    <div><img src="https://crm.scan4-gmbh.de/view/images/map_marker_blue.png" style="max-width:25px;"></div>
                </span>
                <label class="switch">
                    <input id="markersprojects" type="checkbox">
                    <span class="slider round"></span>
                </label>
            </div>

            <span>Scan4 GmbH Glasfaser Hotline <b>0721-98191540</b></span>
            <div id="myBtn1">BTN123</div>
        </div> -->