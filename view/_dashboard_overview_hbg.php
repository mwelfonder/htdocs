<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';

if (!(hasPerm([2, 3]))) {
    die();
}



include_once $_SERVER['DOCUMENT_ROOT'] . '/view/includes/functions.php';

use scan4\StatisticsHOMES;
use scan4\StatisticsHBG;

// Get all users with permission level 6
$users = fetchPermissionUsers(6); // 6 = Hausbegeher
for ($i = 0; $i < count($users); $i++) {
    $a_begeher[] = echousername($users[$i]->user_id);
}
sort($a_begeher);



$current_year = date('Y');
$current_month = date('m');
$current_day = date('d');
$data = array();



$hbgusernames = array();

$conn = dbconnect();
$query = "SELECT DISTINCT hausbegeher FROM scan4_hbg WHERE status = 'PLANNED' ORDER BY hausbegeher ASC;";
if ($result = $conn->query($query)) {
    while ($row = $result->fetch_assoc()) {
        $hbgusernames[] = $row['hausbegeher'];
    }
    $result->free_result();
}
$conn->close();


$start_date = new DateTime('2023-01-01');
$end_date = new DateTime(); // current date

$workdays = 0;

for ($date = clone $start_date; $date <= $end_date; $date->modify('+1 day')) {
    if ($date->format('N') < 6) { // format('N') returns 1 for Monday, 2 for Tuesday, and so on.
        $workdays++;
    }
}

//echo "Number of working days from " . $start_date->format('Y-m-d') . " to " . $end_date->format('Y-m-d') . ": " . $workdays;

//echo 'workdays' . $workdays;
/*
// get the totals and caluculate average monthly appointments
$sum_total = $totals['total'];
$sum_done = $totals['done'];
$sum_abbruch = $totals['abbruch'];
$sum_open = $totals['open'];


$current_month = date('n');
$month_avg_total = round($sum_total / $current_month, 2);
$month_avg_done = round($sum_done / $current_month, 2);
$month_avg_abbruch = round($sum_abbruch / $current_month, 2);
$month_avg_open = round($sum_open / $current_month, 2);

$perc_done = round($sum_done / $sum_total * 100, 2);
$perc_abbruch = round($sum_abbruch / $sum_total * 100, 2);
$perc_open = round($sum_open / $sum_total * 100, 2);

$perc_month_done = round($month_avg_done / $month_avg_total * 100, 2);

$day_avg_total = round($sum_total / $workdays, 2);
$day_avg_done = round($sum_done / $workdays, 2);
$day_avg_abbruch = round($sum_abbruch / $workdays, 2);
$day_avg_open = round($sum_open / $workdays, 2);



$months = array();
$done = array();
$open = array();
$abbruch = array();
$kdnichtda = array();
$ichwarnichtda = array();
$hbgnichtdurch = array();


foreach ($totals['months'] as $month => $data) {
    $months[] = $month;
    $done[] = $data['done'];
    $open[] = $data['open'];
    $abbruch[] = $data['abbruch'];
    $kdnichtda[] = $data['Kunde war nicht da'];
    $ichwarnichtda[] = $data['Ich war nicht da'];
    $hbgnichtdurch[] = $data['HBG nicht durchführbar'];
}
*/

// get_offdays($hbgusernames);
// function get_offdays(array $hbgusernames)
get_offdays();
function get_offdays()
{
    $current_year = date("Y");
    $current_week = date("W");
    $data = array();

    $count_by_username = array();

    $absence_types = array(
        'urlaub' => 'Urlaub',
        'krank' => 'Krank',
        'sonderurlaub' => 'Sonderurlaub',
        'survey' => 'Survey',
        'streetnav' => 'StreetNav',
        'callcenter' => 'CallCenter',
        'fehlend_unbekannt' => 'Fehlend Unbekannt',
        'backoffice' => 'Backoffice',
    );

    // initialize an array to store the counts for each username and absence type
    $count_by_username = array();

    // iterate over each record in the array
    foreach ($data as $record) {
    // foreach ($hbgusernames as $username) {
        // extract the username from the record
        $username = $record['username'];

        // check each absence type for each day of the week and increment the count for the corresponding username
        foreach ($absence_types as $type => $search_term) {
            if (!empty($record['montag']) && strpos($record['montag'], $search_term) !== false) {
                $count_by_username[$username][$type]++;
            }
            if (!empty($record['dienstag']) && strpos($record['dienstag'], $search_term) !== false) {
                $count_by_username[$username][$type]++;
            }
            if (!empty($record['mittwoch']) && strpos($record['dienstag'], $search_term) !== false) {
                $count_by_username[$username][$type]++;
            }
            if (!empty($record['donnerstag']) && strpos($record['dienstag'], $search_term) !== false) {
                $count_by_username[$username][$type]++;
            }
            if (!empty($record['freitag']) && strpos($record['dienstag'], $search_term) !== false) {
                $count_by_username[$username][$type]++;
            }
           

        }
    }

    // output the counts for each username and absence type
    foreach ($count_by_username as $username => $counts) {
        echo "Counts for $username:<br>";
        foreach ($absence_types as $type => $search_term) {
            echo ucfirst($type) . ": " . $counts[$type] . "<br>";
        }
        echo "<br>";
    }

    echo '</div>';
}





$tooltip_worday = 'This is the average per working days. Mon-Fri from 01.01. till today. (Workdays:&nbsp;' . $workdays . ')';
$tooltip_month = 'This is the average per month. First month till the current month.';

?>
<script type="text/javascript" src="view/includes/js/app_dashboard_hbg.js"></script>
<script>
    // track pageload event general.js 
    track_clickevent('pageload', 'HBG Overview', '');
</script>
<div class="row content-wrapper">
    <div class="col-12">
        <div class="row"> <!-- Header -->
            <div class="col-3">
                <div style="padding:10px;margin:10px;">
                    <select id="select_hausbegeher" class="form-control">
                        <option value="all">All</option>
                        <?php
                        foreach ($hbgusernames as $hausbegeher) {
                            echo '<option value="' . $hausbegeher . '">' . $hausbegeher . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>

        </div>
        <div class="row"> <!-- Body -->
            <div class="col-12">
                <div class="row"> <!-- first body row -->
                    <div style="padding: 5px;" class="col">
                        <div class="boxwrapper">
                            <div class="boxheader">
                                <div class="row justify-content-end">
                                    <div class="col-10 align-self-end boxheadertitel"><span>HBG Total</span></div>
                                    <div class="col-2 aligncenter boxheadericon grey"><img src="https://crm.scan4-gmbh.de/view/images/icons/icon_house_white.png" /></div>
                                </div>
                            </div>
                            <div class="boxbody">
                                <div class="row">
                                    <div style="padding:5px;" class="col">
                                        <span id="box_total_total"></span>
                                    </div>
                                    <div style="padding:5px;" class="col">
                                        <span title="<?php echo $tooltip_month ?>" id="box_total_month"></span><span class="small">/mo</span>
                                    </div>
                                    <div style="padding:5px;" class="col">
                                        <span title="<?php echo $tooltip_worday ?>" id="box_total_day"></span><span class="small">/day</span>
                                    </div>
                                </div>
                                <div class="row usertotals hidden" style="border-top: 3px dashed #dce1ef;"></div>
                                <div class="row usertotals hidden">
                                    <div style="padding:5px;" class="col">
                                        <span id="user_total_total"></span>
                                    </div>
                                    <div style="padding:5px;" class="col">
                                        <span title="<?php echo $tooltip_month ?>" id="user_total_month"></span><span class="small">/mo</span>
                                    </div>
                                    <div style="padding:5px;" class="col">
                                        <span title="<?php echo $tooltip_worday ?>" id="user_total_day"></span><span class="small">/day</span>
                                    </div>
                                </div>
                                <div class="row usertotalsA hidden" style="display:flex;align-items: center;">
                                    <div style="padding:5px;" class="col-2">
                                        <span style="font-weight:400;font-size: 14px;" id="user_total_total_average">Average</span>
                                    </div>
                                    <div class="col-10">
                                        <hr class="dashed">
                                    </div>
                                </div>
                                <div class="row usertotalsA hidden">
                                    <div style="padding:5px;" class="col">
                                        <span id="user_total_total"></span>
                                    </div>
                                    <div style="padding:5px;" class="col">
                                        <span title="<?php echo $tooltip_month ?>" id="user_total_month"></span><span class="small">/mo</span>
                                    </div>
                                    <div style="padding:5px;" class="col">
                                        <span title="<?php echo $tooltip_worday ?>" id="user_total_day"></span><span class="small">/day</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="padding: 5px;" class="col">
                        <div class="boxwrapper">
                            <div class="boxheader">
                                <div class="row justify-content-end">
                                    <div class="col-10 align-self-end boxheadertitel"><span>HBG Done</span><span id="box_perc_done" style="padding: 4px 8px;" class="light-text align-self-end"></span>
                                        <span class="usertotals hidden" style="border: 1.5px dashed #dce1ef;"></span><span style="padding: 4px 8px;" class="usertotals hidden light-text align-self-end" id="user_perc_done"></span>
                                    </div>
                                    <div class="col-2 aligncenter boxheadericon green"><img src="https://crm.scan4-gmbh.de/view/images/icons/icon_house_check_white.png" /></div>
                                </div>
                            </div>
                            <div class="boxbody">
                                <div class="row">
                                    <div style="padding:5px;" class="col">
                                        <span id="box_done_total"></span>
                                    </div>
                                    <div style="padding:5px;" class="col">
                                        <span title="<?php echo $tooltip_month ?>" id="box_done_month"></span> <span class="small">/mo</span>
                                    </div>
                                    <div style="padding:5px;" class="col">
                                        <span title="<?php echo $tooltip_worday ?>" id="box_done_day"></span> <span class="small">/day</span>
                                    </div>
                                </div>
                                <div class="row usertotals hidden" style="border-top: 3px dashed #dce1ef;"></div>
                                <div class="row usertotals hidden">
                                    <div style="padding:5px;" class="col">
                                        <span id="user_done_total"></span>
                                    </div>
                                    <div style="padding:5px;" class="col">
                                        <span title="<?php echo $tooltip_month ?>" id="user_done_month"></span> <span class="small">/mo</span>
                                    </div>
                                    <div style="padding:5px;" class="col">
                                        <span title="<?php echo $tooltip_worday ?>" id="user_done_day"></span> <span class="small">/day</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="padding: 5px;" class="col">
                        <div class="boxwrapper">
                            <div class="boxheader">
                                <div class="row justify-content-end">
                                    <div class="col-10 align-self-end boxheadertitel">HBG Abbruch<span id="box_perc_abb" style="padding: 4px 8px;" class="light-text align-self-end"></span>
                                        <span class="usertotals hidden" style="border: 1.5px dashed #dce1ef;"></span><span style="padding: 4px 8px;" class="usertotals hidden light-text align-self-end" id="user_perc_abb"></span>
                                    </div>
                                    <div class="col-2 aligncenter boxheadericon orange"><img src="https://crm.scan4-gmbh.de/view/images/icons/icon_house_canceld_white.png" /></div>
                                </div>
                            </div>
                            <div class="boxbody">
                                <div class="row">
                                    <div style="padding:5px;" class="col">
                                        <span id="box_abb_total"></span>
                                    </div>
                                    <div style="padding:5px;" class="col">
                                        <span title="<?php echo $tooltip_month ?>" id="box_abb_month"></span><span class="small">/mo</span>
                                    </div>
                                    <div style="padding:5px;" class="col">
                                        <span title="<?php echo $tooltip_worday ?>" id="box_abb_day"></span><span class="small">/day</span>
                                    </div>
                                </div>
                                <div class="row usertotals hidden" style="border-top: 3px dashed #dce1ef;"></div>
                                <div class="row usertotals hidden">
                                    <div style="padding:5px;" class="col">
                                        <span id="user_abb_total"></span>
                                    </div>
                                    <div style="padding:5px;" class="col">
                                        <span title="<?php echo $tooltip_month ?>" id="user_abb_month"></span> <span class="small">/mo</span>
                                    </div>
                                    <div style="padding:5px;" class="col">
                                        <span title="<?php echo $tooltip_worday ?>" id="user_abb_day"></span> <span class="small">/day</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="padding: 5px;" class="col">
                        <div class="boxwrapper">
                            <div class="boxheader">
                                <div class="row justify-content-end">
                                    <div class="col-10 align-self-end boxheadertitel">HBG Open<span id="box_perc_open" style="padding: 4px 8px;" class="light-text align-self-end"></span>
                                        <span class="usertotals hidden" style="border: 1.5px dashed #dce1ef;"></span><span style="padding: 4px 8px;" class="usertotals hidden light-text align-self-end" id="user_perc_open"></span>
                                    </div>
                                    <div class="col-2 aligncenter boxheadericon blue"><img src="https://crm.scan4-gmbh.de/view/images/icons/icon_house_question_white.png" /></div>
                                </div>
                            </div>
                            <div class="boxbody">
                                <div class="row">
                                    <div style="padding:5px;" class="col">
                                        <span id="box_open_total"></span>
                                    </div>
                                    <div style="padding:5px;" class="col">
                                        <span title="<?php echo $tooltip_month ?>" id="box_open_month"></span><span class="small">/mo</span>
                                    </div>
                                    <div style="padding:5px;" class="col">
                                        <span title="<?php echo $tooltip_worday ?>" id="box_open_day"></span><span class="small">/day</span>
                                    </div>
                                </div>
                                <div class="row usertotals hidden" style="border-top: 3px dashed #dce1ef;"></div>
                                <div class="row usertotals hidden">
                                    <div style="padding:5px;" class="col">
                                        <span id="user_open_total"></span>
                                    </div>
                                    <div style="padding:5px;" class="col">
                                        <span title="<?php echo $tooltip_month ?>" id="user_open_month"></span> <span class="small">/mo</span>
                                    </div>
                                    <div style="padding:5px;" class="col">
                                        <span title="<?php echo $tooltip_worday ?>" id="user_open_day"></span> <span class="small">/day</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- first body row end -->
                <div class="row boxwrapper" style="margin-top:20px;"> <!-- second body row -->
                    <div class="row">
                        <div style="padding:20px;"></div>
                    </div>
                    <div class="row">

                        <div class="col-6">
                            <div id="chart_monthlines"></div>
                        </div>
                        <div class="col-6">
                            <div id="chart_reasons"></div>
                        </div>

                    </div>
                    <div class="row">
                        <div style="padding:20px;"></div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-12">
                                    <span></span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div id="chart_hausbegeher"></div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="row">

                    </div>
                </div> <!-- second body row end -->
            </div>
        </div> <!-- Body row end -->
        <div class="row">


        </div>




    </div>
    <span id="workdays" class="hidden"><?php echo $workdays ?></span>
    <span id="workdays1" class="hidden"><?php echo $workdays ?></span>
</div>

<?php






if (isset($counts)) {
    
    $sort = array_column($counts, 'total');
    // Sort the $counts array in descending order by 'done' value
    array_multisort($sort, SORT_DESC, $counts);
    $rank = 0;

    $hbgusernames = array();

    foreach ($counts as $name => $values) {
        $rank++;
        $Ttotal = $values['total'];
        $Tdone = $values['done'];
        $Tabbruch = $values['abbruch'];
        $Topen = $values['open'];
        $data_all_total[] = $values['total'];
        $data_all_done[] = $values['done'];
        $data_all_abbruch[] = $values['abbruch'];
        $data_all_open[] = $values['open'];

        if (!in_array($name, $hbgusernames)) {
            $hbgusernames[] = $name;
        }
    }
/*
echo $data_all_total;
echo '<br>';
echo json_encode($data_all_total);
echo '<br>';
echo json_encode($done);



// total_data

echo '<pre>';
echo print_r($total_data);
echo '</pre>';
*/
}

?>





<style>
    /* custom scrollbar */
    ::-webkit-scrollbar {
        width: 20px;
    }

    ::-webkit-scrollbar-track {
        background-color: transparent;
    }

    ::-webkit-scrollbar-thumb {
        background-color: #d6dee1;
        border-radius: 20px;
        border: 6px solid transparent;
        background-clip: content-box;
    }

    ::-webkit-scrollbar-thumb:hover {
        background-color: #a8bbbf;
    }
</style>


<?php
