<?php
include(dirname(__FILE__) . "/users/init.php");
include_once 'view/includes/functions.php';




//require_once 'view/includes/header.php';



$view = "$_SERVER[REQUEST_URI]";


$split = explode('rel=', $view);

if (isset($split[1])) {
    $uid =  $split[1];
}




$conn = dbconnect();


$query = "SELECT * FROM `scan4_hbg` WHERE `uid` LIKE '" . $uid . "' ORDER BY scan4_hbg.id DESC;";
$result = $conn->query($query);
$row = mysqli_fetch_assoc($result);
$conn->close();


if (date('Y-m-d', strtotime($row['date'])) <= date('Y-m-d')) {
    echo 'Dieser Termin kann nicht mehr storniert werden';
    die();
} else if ($row['status'] !== 'PLANNED') {
    echo 'Dieser Termin kann nicht mehr storniert werden';
    die();
}



$conn = dbconnect();
$query = "INSERT INTO `scan4_calls`(`call_date`, `call_time`, `call_user`, `homeid`, `result`, `comment`, `callid`) VALUES ('" . date('Y-m-d') . "','" . date('H:i') . "','Kunde','" . $row['homeid'] . "','abgesagt','Termin über Mail Button selbst abgesagt','" . $uid . "')";
mysqli_query($conn, $query);


$query = "UPDATE `scan4_hbg` SET `status`='STORNO' WHERE `uid` = '".$uid."'";
mysqli_query($conn, $query);


echo 'Ihr Termin wurde abgesagt';
die();





$query = "SELECT scan4_hbg.*,scan4_homes.carrier,scan4_homes.city,scan4_homes.plz,scan4_homes.street,scan4_homes.streetnumber,scan4_homes.streetnumberadd,scan4_homes.unit,scan4_homes.firstname,scan4_homes.lastname,scan4_homes.phone1,scan4_homes.phone2,scan4_homes.isporder,scan4_homes.anruf1,scan4_homes.anruf2,scan4_homes.anruf3,scan4_homes.anruf4,scan4_homes.anruf5 FROM `scan4_hbg` INNER JOIN scan4_homes ON scan4_hbg.homeid=scan4_homes.homeid WHERE scan4_hbg.uid LIKE '" . $uid . "' ORDER BY scan4_hbg.id DESC;";
$result = $conn->query($query);
$row = mysqli_fetch_assoc($result);
$conn->close();

/*

// set local not working => workaround
$termininfo =  date('D, d.m.y', strtotime($row['date']));
$termininfo = str_replace("Mon", "Montag", $termininfo);
$termininfo = str_replace("Tue", "Dienstag", $termininfo);
$termininfo = str_replace("Wed", "Mittwoch", $termininfo);
$termininfo = str_replace("Thu", "Donnerstag", $termininfo);
$termininfo = str_replace("Fri", "Freitag", $termininfo);
$termininfo = str_replace("Sat", "Samstag", $termininfo);
$termininfo = str_replace("Sun", "Sonntag", $termininfo);
$termininfo = str_replace(',', ' den ', $termininfo);


$street = $row['street'] . ' ' . $row['streetnumber']  . $row['streetnumberadd'];
$city = $row['plz'] . ' ' . $row['city'];

?>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Termin Glasfaser Hausbegehung</title>
</head>

<body style="width: 100%; height: 100%; margin: 0px; padding: 0px; font-family: Arial, sans-serif;font-size: 12px; color: #000000; line-height: 18px; background-color:#f5f5f5; ">
    <table width="560" align="center" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF" style="font-size: 12px; line-height: 18px; color: #000000; font-family: Arial, sans-serif; width: 560px; margin: 0px auto; padding: 0px 0px 0px 0px; background-color: #ffffff;">
        <tbody>
          
            <tr>
                <td width="30"></td>
                <td width="500" style="padding-top:10px;">
                    <!-- BODY - Start -->
                    <p></p>
                    <!-- Greeting - Start -->
                    <p style="font-family: Arial, sans-serif; font-size: 16px; line-height: 24px; padding-bottom: 8px; padding-top: 30px;">
                       Objektdaten:</p>
                    <!-- Table - Start -->
                    <table role="presentation" align="center" bgcolor="#1086fc" style="font-family: Arial; width: 500px; background: white; font-size: 14px; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th colspan="2" style="border-bottom: 2px solid #1086fc;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="background: #f7f7f7;">
                                <td colspan="2" width="230" style="text-align: left; border-bottom: 1px solid #e6e5e5; padding: 10px; vertical-align: top;">
                                    <?php echo $street ?>
                                </td>
                            </tr>
                            <tr style="background: #f7f7f7;">
                                <td colspan="2" width="230" style="text-align: left; border-bottom: 1px solid #e6e5e5; padding: 10px; vertical-align: top;">
                                    <?php echo $city ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <!-- Table - End -->
                    <p style="font-family: Arial, sans-serif; font-size: 16px; line-height: 24px; padding-bottom: 8px; padding-top: 30px;">
                       Termindetails:</p>
                    <table role="presentation" align="center" bgcolor="#1086fc" style="font-family: Arial; width: 500px; background: white; font-size: 14px; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th colspan="2" style="border-bottom: 2px solid #1086fc;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="background: #f7f7f7;">
                                <td colspan="2" width="230" style="text-align: left; border-bottom: 1px solid #e6e5e5; padding: 10px; vertical-align: top;">
                                    <?php echo $termininfo ?> um <?php echo $row['time'] ?>Uhr
                                </td>
                            </tr>
                           
                        </tbody>
                    </table>

                    <!-- Goodbye - Start -->
                    <p style="font-family: Arial, sans-serif; font-size: 16px; line-height: 24px; padding-bottom: 8px; padding-top: 30px;">
                        Wenn Sie den vereinbarten Termin stornieren wollen, bestätigen Sie bitte hier:
                    </p>
                    <p style="font-family: Arial, sans-serif; font-size: 16px; line-height: 24px; padding-bottom: 8px; padding-top: 30px;">
                       <button id="">Bestätigen</button>
                    </p>
                   
                    <!-- BODY - End -->
                </td>
                <td width="30"></td>
            </tr>
            <tr>
                <td width="500" colspan="3" style="font-size: 11px; text-align: center; line-height: 18px; padding: 0px; height: 10px;
width: 560px; background-color: #fff; font-family: Arial, sans-serif; color: #000000; margin: 0px;" width="560" height="10"></td>
            </tr>
            <tr>
                <td width="500" colspan="3" style="font-size: 11px; text-align: center; line-height: 18px; padding: 0px; height: 10px; width: 560px; background-color: #fff; font-family: Arial, sans-serif; color: #000000; margin: 0px;" width="560" height="10"> </td>
            </tr>
            <tr>
                <td width="500" colspan="3" style="font-size: 11px; line-height: 0px; padding: 0px; height: 10px; width: 500px; background-color: #ffffff; font-family: Arial, sans-serif; color: #000000; margin: 0px;" width="560" height="10"></td>
            </tr>

            <tr>
                <td width="500" colspan="3" style="font-size: 11px; line-height: 0px; padding: 0px; height: 10px; width: 500px; background-color: #ffffff; font-family: Arial, sans-serif; color: #000000; margin: 0px;" width="560" height="10">
                </td>
            </tr>
            <tr>
                <td colspan="3" width="500" style="font-size: 11px; background: #1a1919; color: #ffffff; padding: 17px 35px 17px 35px; width: 530px;">
                    <p>
                        <b>Sie ben&ouml;tigen Hilfe, haben Fragen oder wollen uns Feedback geben?</b><br>
                        Telefon: <a href="tel:+49 721 8648 4804" style="color: #fff; text-decoration: none;">+49 721 8648 4804</a> | E-Mail: <a style="color: #fff; text-decoration: none;" href="mailto:info@scan4-gmbh.de">info@scan4-gmbh.de</a><br>
                    </p>
                    <p style="padding-top: 15px;">
                        <b>Scan4 GmbH</b>, Seboldstr. 1, 76227 Karlsruhe, Deutschland<br>
                    </p>
                </td>

            </tr>
            <tr style="font-family: Arial; background: #1a1919;">
                <td colspan="3" style="padding: 0px 35px 17px 35px;">
                    <img width="85" src="https://tucrm.scan4-gmbh.de/view/images/logo_scan4_white.png" alt="" style="padding: 0px; margin: 0px;" />
                </td>
            </tr>
        </tbody>
    </table>
    </td>
    </tr>
    </tbody>
    </table>
</body>

</html>

