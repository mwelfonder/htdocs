<?php



// GOOGLE API 
// AIzaSyDPJeMWQPm1tc5990MJtanoTQNsxCF_Yyk
// AIzaSyDuCnUvUOHME0DkORbc875Q5gWdMwW9Ykc

require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';

if (!(hasPerm([2, 3, 16]))) {
  die();
}

//phpinfo();


include_once $_SERVER['DOCUMENT_ROOT'] . '/view/includes/functions.php';

use scan4\StatisticsHOMES;
use scan4\StatisticsHBG;



list($all, $listhausbgeher) = fetch_all();



// Get all users with permission level 6
$users = fetchPermissionUsers(6); // 6 = Hausbegeher
for ($i = 0; $i < count($users); $i++) {
  $username = echousername($users[$i]->user_id);
  if (!array_key_exists($username, $listhausbgeher)) {
    $listhausbgeher[$username] = array();
  }
}
ksort($listhausbgeher); // sort the array by names

/*
echo '<pre>';
echo print_r($listhausbgeher);
echo '</pre>';
*/







$date = date('Y-m-d');
$homes = new StatisticsHOMES();
$arrayopen = $homes->count_homestatus('scan4_status', 'PLANNED', true);
$arraypending = $homes->count_homestatus('scan4_status', 'PENDING', true);

$hbg = new StatisticsHBG();
$hbg_citylist = $hbg->hbg_appt_citylist($date);


foreach ($hbg_citylist as &$city) {
  $key = $city['city'] ?? '';
  if (isset($arrayopen[$key])) {
    $city['OPEN'] = $arrayopen[$key];
  }
  if (isset($arraypending[$key])) {
    $city['PENDING'] = $arraypending[$key];
  }
}


function fetch_all($date = null)
{

  $listhausbgeher = array();
  $conn = dbconnect();
  if ($date == null) {
    $today = date('Y-m-d');
    $monday = date('Y-m-d', strtotime('monday this week', strtotime($today)));
    $sunday = date('Y-m-d', strtotime('sunday this week', strtotime($today)));
    $query = "SELECT * FROM scan4_hbg INNER JOIN scan4_homes ON scan4_hbg.homeid=scan4_homes.homeid WHERE scan4_hbg.status = 'PLANNED' AND scan4_hbg.date BETWEEN '$monday' AND '$sunday' ORDER BY scan4_hbg.hausbegeher,scan4_hbg.time ASC";
  } else {
  }

  //$query = "SELECT * FROM scan4_hbg WHERE date BETWEEN '2023-01-02' AND '$today' AND status = 'PLANNED';";
  if ($result = $conn->query($query)) {
    while ($row = $result->fetch_assoc()) {
      $data[] = $row;
      $city = $row['city'];
      $date = $row['date'];
      $time = $row['time'];

      if (!array_key_exists($row['hausbegeher'], $listhausbgeher)) {
        $listhausbgeher[$row['hausbegeher']] = array();
      }

      if (isset($listhausbgeher[$row['hausbegeher']]['total']))
        $listhausbgeher[$row['hausbegeher']]['total']++;
      else
        $listhausbgeher[$row['hausbegeher']]['total'] = 1;

      if (isset($listhausbgeher[$row['hausbegeher']][$city]))
        $listhausbgeher[$row['hausbegeher']][$city]++;
      else
        $listhausbgeher[$row['hausbegeher']][$city] = 1;

      $listhausbgeher[$row['hausbegeher']][$date][] = $time . ' ' . $city;
    }
    $result->free_result();
  }
  $conn->close();
  $sort = array_column($data, 'date');
  // Sort the $counts array in descending order by 'done' value


  return array($data, $listhausbgeher);
}


/* 

foreach ($listhausbgeher as $user => $val) {
  $userdata = fetchUserDetails('username', $user);
  $location_start = $userdata->home;

  echo '<br>';

  $location_list = array();
  if (strlen($location_start) > 5) {
    foreach ($hbg_citylist as $city) {
      $location_end = $city['plz'] . ' ' . $city['city'];
      // echo 'true';
      //echo '<br>';
      // echo $location_start . ' => ' . $location_end;
      if (!in_array($location_end, $location_list)) {
        $location_list[] = $location_end;
      }
    }
    echo '<pre>';
    echo print_r($location_list);
    echo '</pre>';

    $counter = 0;
    $conn = dbconnect();
    $sql = "SELECT * FROM distance WHERE loc_from = '$location_start' AND loc_to IN ('" . implode("','", $location_list) . "')";
    echo $sql;
    if ($result = $conn->query($query)) {
      while ($row = $result->fetch_assoc()) {
        $counter++;
      }
      $result->free_result();
    }

    echo '</br>counter: ' . $counter;
    echo '</br>';
    $conn->close();
  }
}


*/



date_default_timezone_set('Europe/Berlin');
$formatter = new IntlDateFormatter('de_DE', IntlDateFormatter::FULL, IntlDateFormatter::NONE);

$today = date('Y-m-d');
$monday = date('Y-m-d', strtotime('monday this week', strtotime($today)));

?>
<script type="text/javascript" src="view/includes/js/app_weekplan.js"></script>


<div id="modulwrapper">
  <div id="loadwrapper" class="fullwidth aligncenter">
    <div class="appt-loader loader"></div>
  </div>
  <div id="toptable" style="margin: 8px;" class="row boxwrapper">
    <?php
    $loopcounter = 0;
    foreach ($listhausbgeher as $user => $val) {
      $loopcounter++;
      ob_start();
    ?>

      <div id="<?php echo $user ?>" class="row appointments_wrapper <?php if ($loopcounter > 1) echo 'hidden'; ?>">
        <div class="inside_wrapper" style="width: 100%;">
          <div class="row"><?php echo '<span style="font-size:16px;font-weight:600;color: #49505c;">' . $user . '</span> Termine: <span class="user_week_totals">' . ($val['total'] ?? 0) . '</span>' ?> </div>
          <div class="row">
            <?php
            for ($i = 0; $i < 6; $i++) {
              // Calculate the date for the current day of the week
              $dayOfWeek = date('Y-m-d', strtotime('+' . $i . ' day', strtotime($monday)));
              // Get the number of events for the current day
              $eventCount = count($val[$dayOfWeek] ?? []);
            ?>
              <div class="col-2 eventelements_wrapper">
                <div class="row" style="padding: 0px;">
                  <div class="col-8" style="padding: 0px;"><span class="weekday-title" style="text-align:center;"><?php echo $formatter->format(strtotime('+' . $i . ' day', strtotime($monday))) ?> <span class="weekday_info"><?php echo date('d.m.y', strtotime('+' . $i . ' day', strtotime($monday))) . '</span>  <i class="ri-calendar-event-line"></i><span class="weekday_infocount">' . $eventCount; ?></span></span></div>

                </div>
                <table class="eventelements">
                  <?php
                  // Iterate through each event for the current day
                  foreach ($val[$dayOfWeek] ?? [] as $event) {
                    $parts = explode(' ', $event, 2);
                    echo '<tr><td>' . $parts[0] . '</td><td>' . $parts[1] . '</td></tr>';
                  }

                  ?>
                </table>
              </div>
            <?php
            }
            ?>
          </div>
        </div>
      </div>
    <?php

      // break;
      $hbgcontent[$user] = ob_get_contents();
    }

    ?>
  </div>






  <div id="weekplantable" style="margin: 8px;padding:0;background:#fff;" class="row">
    <div class="row" style="padding:0;">
      <div style="padding: 10px;" class="row"> <!-- Header -->
        <div class="col-2 input-group">
          <div class="input-group-prepend">
            <button class="btn btn-secondary" type="button" id="prevWeekButton"><i class="ri-arrow-left-s-line"></i></button>
          </div>
          <input class="form-control" type="week" id="week" name="week" value="<?php echo date('Y-\WW'); ?>" required>
          <div class="input-group-append">
            <button class="btn btn-secondary" type="button" id="nextWeekButton"><i class="ri-arrow-right-s-line"></i></button>
          </div>

        </div>

        <div class="col-auto" style="">
          <div style="text-align:center;" id="dateRange" class="input-group-text">
            <?php
            $today = new DateTime();
            $currentWeek = $today->format('Y-\WW');
            $startDate = $today->setISODate($today->format('Y'), $today->format('W'))->format('d.m.Y');
            $endDate = $today->modify('+6 days')->format('d.m.Y');
            echo $startDate . ' - ' . $endDate;
            ?>
          </div>
        </div>
        <div class="col-auto">
          <div class="btn btn-secondary" id="saveall"><i class="ri-save-3-line"></i> Speichern</div>
          <div class="btn btn-light" id="undoall"><i class="ri-arrow-go-back-line"></i></i> Undo</div>
        </div>
        <div class="col" class="checkwrapper">
          <div class="btn btn-secondary" id="clearall"><i class="ri-brackets-line"></i> Clear all</div>
          <div class="btn btn-secondary" id="checkit"><i class="ri-road-map-line"></i> Karte</div>
          <div class="btn-group btn-group-toggle" data-toggle="buttons">
            <label class="btn btn-secondary" id="copykw">
              <input type="radio" name="options" autocomplete="off" checked><i class="ri-stack-line"></i> Copy
            </label>
            <label class="btn btn-secondary disabled" id="pastekw">
              <input type="radio" name="options" autocomplete="off"><i class="ri-stack-line"></i> Paste
            </label>
          </div>
          <div class="btn btn-secondary" id="printme"><i class="ri-printer-line"></i></div>

        </div>
        <div class="col-3">
          <select name="revisions" id="revisions" class="form-control">
          </select>
        </div>


      </div>

    </div>
    <div class="row"> <!-- Body -->
      <div class="col-12" style="padding:0;">
        <div class="row">
          <div id="weekplanholder" class="col-9">
            <div class="row" id="columnHeaders">
              <div class="col">Name</div>
              <div class="col">Mo <?php echo date('d.m.', strtotime('+0 day', strtotime($monday))) ?></div>
              <div class="col">Di <?php echo date('d.m.', strtotime('+1 day', strtotime($monday))) ?></div>
              <div class="col">Mi <?php echo date('d.m.', strtotime('+2 day', strtotime($monday))) ?></div>
              <div class="col">Do <?php echo date('d.m.', strtotime('+3 day', strtotime($monday))) ?></div>
              <div class="col">Fr <?php echo date('d.m.', strtotime('+4 day', strtotime($monday))) ?></div>
              <div class="col">Sa <?php echo date('d.m.', strtotime('+5 day', strtotime($monday))) ?></div>
            </div>
            <?php // ## ## ## Hausbegeher ## ## ## // 
            ?>
            <div class="row">
              <div style="background-color: #d8e4bc;" class="col-12 rowtab">Hausbegeher</div>
            </div>
            <?php foreach ($listhausbgeher as $user => $val) { ?>
              <div style="display: flex; flex-wrap: nowrap;" class="row tableday_row" id="droprow_<?php echo $user ?>">
                <div style="cursor: pointer;" id="droptable_<?php echo $user ?>" class="col-2 dropname gspace plain cellwrap"><?php echo $user . ' <span class="plaininfo">' . ($val['total'] ?? 0) . '</span>' ?></div>
                <div class="col-2 gspace tableday plain cellwrap droppable addzone"></div>
                <div class="col-2 gspace tableday plain cellwrap droppable addzone"></div>
                <div class="col-2 gspace tableday plain cellwrap droppable addzone"></div>
                <div class="col-2 gspace tableday plain cellwrap droppable addzone"></div>
                <div class="col-2 gspace tableday plain cellwrap droppable addzone"></div>
                <div class="col-2 gspace tableday plain cellwrap droppable addzone"></div>
              </div>
            <?php } ?>

            <div class="row">

            </div>
          </div>
          <div class="col-3" style="min-height: 70vh;">
            <div class="projectwrapper">

              <?php for ($i = 0; $i < 2; $i++) {
                $client = ($i == 0) ? 'Insyte' :  'Moncobra';
              ?>
                <div id="headtab_<?php echo $client ?>" class="headtab"><?php echo $client ?></div>
                <div id="citytab_<?php echo $client ?>" class="projectlist" style="height:60vh;overflow: auto;">
                  <table id="Tproject_<?php echo $client ?>" class="projecttable datasearch-left">
                    <thead>

                      <th>Project</th>
                      <th><i class="ri-calendar-event-line" title="Planned HBG"></i></th>
                      <th><i class="ri-book-open-line" title="OPEN HomeIds"></i></th>
                      <th>Carrier</th>
                      <th>Time</th>
                    </thead>
                    <tbody>
                      <?php foreach ($hbg_citylist as $city) {
                        if ($city['client'] !== $client) {
                          continue;
                        }
                      ?>
                        <tr class="">

                          <td data-table="<?php echo $client ?>" class="draggable"><?php echo $city['city'] ?></td>
                          <td><?php echo $city['PLANNED'] ?? '' ?></td>
                          <td><?php echo $city['OPEN'] ?? '' ?></td>
                          <td><?php echo $city['carrier'] ?? '' ?></td>
                          <td></td>
                        </tr>
                      <?php } ?>
                    </tbody>
                  </table>
                </div>

              <?php  } ?>
              <div id="headtab_clusters" class="headtab">Clusters</div>
              <div id="citytab_clusters" class="projectlist" style="height:60vh;overflow: auto;"></div>
            </div>
          </div>
        </div>

      </div>
    </div> <!-- Body end -->
    <div class="row">
      <div class="col-12">

        <div class="col-6">
          <div class="row">

          </div>
        </div>
      </div>
    </div>




  </div>
</div>


<div id="map-wrapper" class="hidden">
  <div id="map-search" class="search-field">
    <i class="ri-search-2-line"></i>
    <input id="mapsearchinput" placeholder="Suche...">
  </div>
  <div id="leaflet"></div>
  <button id="map-close-btn"><i class="ri-close-line"></i></button>
</div>









<style>
  #map-close-btn {
    position: absolute;
    top: 20px;
    right: 20px;
    background-color: #fff;
    border: none;
    border-radius: 50%;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    width: 30px;
    height: 30px;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 11111;
  }

  #map-close-btn i {
    font-size: 1.2rem;
    color: #666;
  }

  #map-wrapper {
    position: relative;
    width: 100%;
    height: 90vh;
  }

  #map-search {
    position: absolute;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    background-color: #fff;
    border-radius: 4px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    z-index: 111111111;
    width: 35%;
    height: 30px;
  }

  #map-search i {
    margin-right: 5px;
    font-size: 1.2rem;
    color: #666;
  }

  #mapsearchinput {
    border: none;
    outline: none;
    font-size: 1.2rem;
    color: #333;
    padding: 10px;
    width: 100%;
    height: 100%;
    box-sizing: border-box;
  }

  #leaflet {
    height: 100%;
    width: 100%;
  }

  .selected-helper {
    border: 1px solid #0d6efde6;
  }

  .highlight {
    background-color: #c8deff;
  }

  .copycell {
    position: absolute;
    top: 0;
    left: 0;
    cursor: pointer;
    opacity: 0;
  }

  .deletecell {
    position: absolute;
    top: 0;
    right: 5px;
    cursor: pointer;
    opacity: 0;
  }

  .copycell:hover,
  .deletecell:hover {
    opacity: 1;
  }

  #loadwrapper {
    width: 100%;
    padding: 50px;
    height: 100%;
    min-height: 500px;
    display: flex;
    position: absolute;
    z-index: 500;
    background: rgb(255 255 255 / 50%);
  }

  .btn {
    cursor: pointer;
  }

  .disabled {
    cursor: default;
  }

  .draggable {
    cursor: move;
  }

  .addzone {
    background-repeat: no-repeat;
    background-position: center;
    background-size: contain;
    padding: 10px;
  }


  .addzone:not(.dayevent_cell):hover {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2z'/%3E%3C/svg%3E");
    background-size: 0.51vw;
    cursor: pointer;
    user-select: none;
    background-repeat: no-repeat;
    background-position: center;
  }

  .gspace.plain {
    border-right: 1px solid #cbcbcb;
    border-bottom: 1px solid #cbcbcb;
    min-height: 50px;
    margin: 2px;
    border-radius: 0px;
    align-items: center;
    display: flex;
  }

  .gspace.dotted {
    border: 1px dashed;
  }

  .aligncentered {
    align-items: center;
    display: inline-flex;
    justify-content: center;
  }

  .headtab {
    padding: 12px;
    background: #f2f2f2;
    cursor: pointer;
    user-select: none;
    margin-bottom: 6px;
  }

  .closed {
    height: 0 !important;
    overflow: hidden;
  }

  .plaininfo {
    margin-left: 6px;
    font-size: 12px;
    color: #000;
    background: #ffd26e;
    padding: 5px 10px;
    border-radius: 50px;
  }

  .dropped {
    margin: 1px;
    font-size: 12px;
    color: #000;
    padding: 4px 6px;
    border-radius: 20px;
  }

  .dropped.Insyte {
    background: #35abb7;
  }

  .dropped.Moncobra {
    background: #e45b27;
  }

  .cellwrap {
    display: flex;
    flex-wrap: wrap;
  }

  .col-2.gspace.plain.cellwrap {
    max-width: 14% !important;
    flex: 0 0 14%;
  }

  span.weekday-title:after {
    content: "";
    width: 100%;
    height: 2px;
    background: #626262;
    position: absolute;
    left: 0;
    bottom: 0;
  }

  span.weekday-title>i {
    top: 3px;
    position: relative;
  }



  .eventelements {
    width: 100%;
    user-select: none;
  }

  .rowtab {
    padding: 6px;
    text-align: center;
    font-weight: 700;
  }

  .eventelements tr.selected {
    background-color: #c5c5c5;
    font-weight: 500;
  }

  span.dayevent {
    width: 100%;
    height: 100%;
    text-align: center;
    padding: 5px;
    font-weight: 600;
    color: #fff;
  }

  .dayevent_cell {
    background: #405189;
  }

  span.dayevent {
    width: 100%;
    height: 100%;
    text-align: center;
    padding: 5px;
    font-weight: 600;
    color: #fff;
    display: inline-grid;
    align-items: center;
  }

  table.eventelements.past {
    background: #ff221112;
  }

  .projectlist>div>div:nth-child(1)>div:nth-child(1) {
    display: none;
  }

  .hover_tooltip {
    position: absolute;
    top: 0;
    left: 0;
    background-color: black;
    color: white;
    padding: 5px;
    border-radius: 5px;
    font-size: 14px;
    opacity: 0;
    transition: opacity 0.3s ease-in-out;
  }

  .hover_tooltip .row {
    display: flex;
    align-items: center;
  }

  .hover_tooltip .row span {
    margin-right: 5px;
  }

  .projectwrapper {
    position: sticky;
    top: 0;
  }

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


  /* print */
  @media print {
    #weekplanholder {
      display: block !important;
      visibility: visible !important;
      opacity: 1 !important;
      position: static !important;
      height: auto !important;
      width: auto !important;
      margin: 0 !important;
      padding: 0 !important;
      overflow: visible !important;
      z-index: 999999 !important;
    }
  }
</style>