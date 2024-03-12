<?php

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}
if (!securePage($_SERVER['PHP_SELF'])) {
    die();
}
include_once $_SERVER['DOCUMENT_ROOT'] . '/view/includes/lib/spreadsheet/spreadsheet.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/view/includes/functions.php';

$view = $_SERVER['REQUEST_URI'];

$split = explode('report=', $view);
$city = $split[1];

if (hasPerm([3])) {
    report_excel($city);
} else {
    die();
}


//report_excel($city);
function report_excel($city)
{
    $conn = dbconnect();
    $stats = array();



    $kw = date('W');
    $lastkw = $kw - 1;
    $startdate = date('Y-m-d', strtotime('monday this week'));
    $enddate = date('Y-m-d', strtotime('sunday this week'));
    $laststartdate = date('Y-m-d', strtotime('monday last week'));
    $lastenddate = date('Y-m-d', strtotime('sunday last week'));



    //$query = "SELECT COUNT(homeid) FROM `scan4_calls` WHERE call_user LIKE '%" . $user . "%' AND call_date BETWEEN '" . $startdate . "' AND '" . $enddate . "'";
    $query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '%" . $city . "%'";
    $result = $conn->query($query);
    $row = $result->fetch_row();
    $stats['0']['total'] = $row[0];

    $query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '%" . $city . "%' AND hbg_status = 'OPEN'";
    $result = $conn->query($query);
    $row = $result->fetch_row();
    $stats['0']['nriopen'] = $row[0];

    $query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '%" . $city . "%' AND scan4_status = 'OPEN'";
    $result = $conn->query($query);
    $row = $result->fetch_row();
    $stats['0']['sc4open'] = $row[0];

    $query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '%" . $city . "%' AND scan4_added BETWEEN '" . $startdate . "' AND '" . $enddate . "'";
    $result = $conn->query($query);
    $row = $result->fetch_row();
    $stats['0']['newcustomers'] = $row[0];


    $query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND scan4_hbgdate BETWEEN '" . $laststartdate . "' AND '" . $lastenddate . "'";
    $result = $conn->query($query);
    $row = $result->fetch_row();
    $stats['0']['donelastkw'] = $row[0];


    $query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND scan4_status ='DONE CLOUD' AND (hbg_status = 'OPEN' OR hbg_status = 'PLANNED');";
    $result = $conn->query($query);
    $row = $result->fetch_row();
    $stats['0']['donecloud'] = $row[0];

    $query = "SELECT scan4_homes.homeid,scan4_homes.street,scan4_homes.streetnumber,streetnumberadd,scan4_homes.plz,scan4_homes.city,scan4_homes.scan4_comment  FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND scan4_status ='DONE CLOUD' AND (hbg_status = 'OPEN' OR hbg_status = 'PLANNED');";
    if ($result = $conn->query($query)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $a_cloud[] = $row;
        }
        $result->free_result();
    }


    $query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND (anruf5 != '' OR anruf5 IS NOT NULL) AND (emailsend != '' OR emailsend IS NOT NULL) AND (briefkasten != '' OR briefkasten IS NOT NULL);";
    $result = $conn->query($query);
    $row = $result->fetch_row();
    $stats['0']['failedcontact'] = $row[0];

    $query = "SELECT scan4_homes.homeid,scan4_homes.street,scan4_homes.streetnumber,streetnumberadd,scan4_homes.plz,scan4_homes.city,scan4_homes.scan4_comment FROM `scan4_homes` WHERE city LIKE '" . $city . "' AND (anruf5 != '' OR anruf5 IS NOT NULL) AND (emailsend != '' OR emailsend IS NOT NULL) AND (briefkasten != '' OR briefkasten IS NOT NULL);";
    if ($result = $conn->query($query)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $a_failed[] = $row;
        }
        $result->free_result();
    }


    $query = "SELECT COUNT(scan4_calls.homeid) FROM `scan4_calls` INNER JOIN scan4_homes ON scan4_calls.homeid=scan4_homes.homeid WHERE scan4_calls.result = 'Keine HBG - Kunde sagt, er habe gekündigt' AND scan4_homes.city LIKE '%" . $city . "%' ;";
    $result = $conn->query($query);
    $row = $result->fetch_row();
    $stats['0']['canceled'] = $row[0];

    $query = "SELECT scan4_calls.homeid,scan4_homes.street,scan4_homes.streetnumber,streetnumberadd,scan4_homes.plz,scan4_homes.city,scan4_homes.scan4_comment FROM `scan4_calls` INNER JOIN scan4_homes ON scan4_calls.homeid=scan4_homes.homeid WHERE scan4_calls.result = 'Keine HBG - Kunde sagt, er habe gekündigt' AND scan4_homes.city LIKE '%" . $city . "%' ;";
    if ($result = $conn->query($query)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $a_canceled[] = $row;
        }
        $result->free_result();
    }



    $query = "SELECT COUNT(scan4_calls.homeid) FROM `scan4_calls` INNER JOIN scan4_homes ON scan4_calls.homeid=scan4_homes.homeid WHERE scan4_calls.`result` LIKE 'Keine HBG - Falsche Nummer' AND scan4_homes.city LIKE '%" . $city . "%' AND (phone1 IS NULL OR phone1 = '') AND (phone2 IS NULL OR phone2 = '') ;";
    $result = $conn->query($query);
    $row = $result->fetch_row();
    $stats['0']['wrongdetails'] = $row[0];

    $query = "SELECT scan4_homes.homeid,scan4_homes.street,scan4_homes.streetnumber,streetnumberadd,scan4_homes.plz,scan4_homes.city,scan4_homes.scan4_comment FROM `scan4_calls` INNER JOIN scan4_homes ON scan4_calls.homeid=scan4_homes.homeid WHERE scan4_calls.`result` LIKE 'Keine HBG - Falsche Nummer' AND scan4_homes.city LIKE '%" . $city . "%' AND (phone1 IS NULL OR phone1 = '') AND (phone2 IS NULL OR phone2 = '') ;";
    if ($result = $conn->query($query)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $a_wrong[] = $row;
        }
        $result->free_result();
    }


    $query = "SELECT COUNT(scan4_calls.homeid) FROM `scan4_calls` INNER JOIN scan4_homes ON scan4_calls.homeid=scan4_homes.homeid WHERE scan4_calls.result = 'Keine HBG - Kunde verweigert HBG' AND scan4_homes.city LIKE '%" . $city . "%' ;";
    $result = $conn->query($query);
    $row = $result->fetch_row();
    $stats['0']['refused'] = $row[0];
    $query = "SELECT scan4_homes.homeid,scan4_homes.street,scan4_homes.streetnumber,streetnumberadd,scan4_homes.plz,scan4_homes.city,scan4_homes.scan4_comment FROM `scan4_calls` INNER JOIN scan4_homes ON scan4_calls.homeid=scan4_homes.homeid WHERE scan4_calls.result = 'Keine HBG - Kunde verweigert HBG' AND scan4_homes.city LIKE '%" . $city . "%' ;";
    if ($result = $conn->query($query)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $a_refused[] = $row;
        }
        $result->free_result();
    }


    $query = "SELECT COUNT(homeid) FROM `scan4_homes` WHERE city LIKE '%" . $city . "%' AND (dpnumber = '' OR dpnumber IS NULL) ";
    $result = $conn->query($query);
    $row = $result->fetch_row();
    $stats['0']['nodp'] = $row[0];
    $query = "SELECT scan4_homes.homeid,scan4_homes.street,scan4_homes.streetnumber,streetnumberadd,scan4_homes.plz,scan4_homes.city,scan4_homes.scan4_comment  FROM `scan4_homes` WHERE city LIKE '%" . $city . "%' AND (dpnumber = '' OR dpnumber IS NULL) ";
    if ($result = $conn->query($query)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $a_nodp[] = $row;
        }
        $result->free_result();
    }


    $dataoverview = [
        ['<center><style bgcolor="#90cbff">' . $city . '</style></center>', null],
        ['<right><style bgcolor="#c7c7bf">KW:</style></right>', '<style bgcolor="#ffa640">' . $kw . '</style>'],
        ['<right><style bgcolor="#c7c7bf">NRI Open:</style></right>', '<right><style bgcolor="#ffa640">' .  $stats['0']['nriopen'] . " / " . $stats['0']['total'] . '</right></style>'],
        ['<right><style bgcolor="#c7c7bf">Uncommented open HBGs:</style></right>', '<right><style bgcolor="#ffa640">' . $stats['0']['sc4open'] . " / " . $stats['0']['total']  . '</right></style>'],
        ['<right><style bgcolor="#c7c7bf">New customers this week:</style></right>', '<style bgcolor="#ffa640">' . $stats['0']['newcustomers'] . '</style>'],
        ['<right><style bgcolor="#c7c7bf">HBGs done week ' . $lastkw . ':</style></right>', '<style bgcolor="#ffa640">' . $stats['0']['donelastkw'] . '</style>'],
        ['<right><style bgcolor="#c7c7bf">HBGs already in the cloud, which you have not registered:</style></right>', '<style bgcolor="#ffa640">' . $stats['0']['donecloud'] . '</style>'],
        ['<right><style bgcolor="#c7c7bf">Customers we have called at least 5 times send email and dropped postcard, and you need to set as STOPPED:</style></right>', '<style bgcolor="#ffa640">' . $stats['0']['failedcontact'] . '</style>'],
        ['<right><style bgcolor="#c7c7bf">Customer who has cancelled their contract, which you must set as STOPPED:</style></right>', '<style bgcolor="#ffa640">' . $stats['0']['canceled'] . '</style>'],
        ['<right><style bgcolor="#c7c7bf">Customer with wrong/missing contact details:</style></right>', '<style bgcolor="#ffa640">' . $stats['0']['wrongdetails'] . '</style>'],
        ['<right><style bgcolor="#c7c7bf">Customer who refuses the HBG:</style></right>', '<style bgcolor="#ffa640">' . $stats['0']['refused'] . '</style>'],
        ['<right><style bgcolor="#c7c7bf">No DP number:</style></right>', '<style bgcolor="#ffa640">' . $stats['0']['nodp'] . '</style>']
    ];
    $datacanceled = [
        ['<left><style bgcolor="#63b521">Straße</style></left>', '<left><style bgcolor="#63b521">Hausnummer</style></left>', '<left><style bgcolor="#63b521">Hausnummerzusatz</style></left>', '<left><style bgcolor="#63b521">HomeID</style></left>', '<left><style bgcolor="#ffce00">Scan4 Comments</style></left>']
    ];
    for ($i = 0; $i < count($a_canceled); $i++) {
        $datacanceled[] =
            [
                '<left>' . $a_canceled[$i]['street'] . '</left>',
                '<left>' . $a_canceled[$i]['streetnumber'] . '</left>',
                '<left>' . $a_canceled[$i]['streetnumberadd'] . '</left>',
                '<left>' . $a_canceled[$i]['homeid'] . '</left>',
                '<left>' . $a_canceled[$i]['scan4_comment'] . '</left>'
            ];
    }
    $datacloud = [
        ['<left><style bgcolor="#63b521">Straße</style></left>', '<left><style bgcolor="#63b521">Hausnummer</style></left>', '<left><style bgcolor="#63b521">Hausnummerzusatz</style></left>', '<left><style bgcolor="#63b521">HomeID</style></left>', '<left><style bgcolor="#ffce00">Scan4 Comments</style></left>']
    ];
    for ($i = 0; $i < count($a_cloud); $i++) {
        $datacloud[] =
            [
                '<left>' . $a_cloud[$i]['street'] . '</left>',
                '<left>' . $a_cloud[$i]['streetnumber'] . '</left>',
                '<left>' . $a_cloud[$i]['streetnumberadd'] . '</left>',
                '<left>' . $a_cloud[$i]['homeid'] . '</left>',
                '<left>' . $a_cloud[$i]['scan4_comment'] . '</left>'
            ];
    }
    $datawrong = [
        ['<left><style bgcolor="#63b521">Straße</style></left>', '<left><style bgcolor="#63b521">Hausnummer</style></left>', '<left><style bgcolor="#63b521">Hausnummerzusatz</style></left>', '<left><style bgcolor="#63b521">HomeID</style></left>', '<left><style bgcolor="#ffce00">Scan4 Comments</style></left>']
    ];
    for ($i = 0; $i < count($a_wrong); $i++) {
        $datawrong[] =
            [
                '<left>' . $a_wrong[$i]['street'] . '</left>',
                '<left>' . $a_wrong[$i]['streetnumber'] . '</left>',
                '<left>' . $a_wrong[$i]['streetnumberadd'] . '</left>',
                '<left>' . $a_wrong[$i]['homeid'] . '</left>',
                '<left>' . $a_wrong[$i]['scan4_comment'] . '</left>'
            ];
    }
    $datafailed = [
        ['<left><style bgcolor="#63b521">Straße</style></left>', '<left><style bgcolor="#63b521">Hausnummer</style></left>', '<left><style bgcolor="#63b521">Hausnummerzusatz</style></left>', '<left><style bgcolor="#63b521">HomeID</style></left>', '<left><style bgcolor="#ffce00">Scan4 Comments</style></left>']
    ];
    for ($i = 0; $i < count($a_failed); $i++) {
        $datafailed[] =
            [
                '<left>' . $a_failed[$i]['street'] . '</left>',
                '<left>' . $a_failed[$i]['streetnumber'] . '</left>',
                '<left>' . $a_failed[$i]['streetnumberadd'] . '</left>',
                '<left>' . $a_failed[$i]['homeid'] . '</left>',
                '<left>' . $a_failed[$i]['scan4_comment'] . '</left>'
            ];
    }
    $datarefused = [
        ['<left><style bgcolor="#63b521">Straße</style></left>', '<left><style bgcolor="#63b521">Hausnummer</style></left>', '<left><style bgcolor="#63b521">Hausnummerzusatz</style></left>', '<left><style bgcolor="#63b521">HomeID</style></left>', '<left><style bgcolor="#ffce00">Scan4 Comments</style></left>']
    ];
    for ($i = 0; $i < count($a_refused); $i++) {
        $datarefused[] =
            [
                '<left>' . $a_refused[$i]['street'] . '</left>',
                '<left>' . $a_refused[$i]['streetnumber'] . '</left>',
                '<left>' . $a_refused[$i]['streetnumberadd'] . '</left>',
                '<left>' . $a_refused[$i]['homeid'] . '</left>',
                '<left>' . $a_refused[$i]['scan4_comment'] . '</left>'
            ];
    }
    $datanodp = [
        ['<left><style bgcolor="#63b521">Straße</style></left>', '<left><style bgcolor="#63b521">Hausnummer</style></left>', '<left><style bgcolor="#63b521">Hausnummerzusatz</style></left>', '<left><style bgcolor="#63b521">HomeID</style></left>', '<left><style bgcolor="#ffce00">Scan4 Comments</style></left>']
    ];
    for ($i = 0; $i < count($a_nodp); $i++) {
        $datanodp[] =
            [
                '<left>' . $a_nodp[$i]['street'] . '</left>',
                '<left>' . $a_nodp[$i]['streetnumber'] . '</left>',
                '<left>' . $a_nodp[$i]['streetnumberadd'] . '</left>',
                '<left>' . $a_nodp[$i]['homeid'] . '</left>',
                '<left>' . $a_nodp[$i]['scan4_comment'] . '</left>'
            ];
    }



    $result = Shuchkin\SimpleXLSXGen::fromArray($dataoverview, 'Overview')
        ->setDefaultFont('Calibri')
        ->setDefaultFontSize(11)
        ->setColWidth(1, 100) // 1 - num column, 35 - size in chars
        ->mergeCells('A1:B1')
        ->addSheet($datacanceled, 'cancelled Contract')
        ->setColWidth(1, 20)
        ->addSheet($datacloud, 'HBG in cloud')
        ->setColWidth(1, 20)
        ->addSheet($datawrong, 'wrong&missing details')
        ->setColWidth(1, 20)
        ->addSheet($datafailed, '5 calls+postcard')
        ->setColWidth(1, 20)
        ->addSheet($datarefused, 'refuses HBG')
        ->setColWidth(1, 20)
        ->addSheet($datanodp, 'no DP')
        ->setColWidth(1, 20)
        ->downloadAs('Report_' . $city . '.xlsx');

    Shuchkin\SimpleXLSXGen::fromArray($dataoverview)->downloadAs('datatypes.xlsx');
}
