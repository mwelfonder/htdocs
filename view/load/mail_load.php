<?php
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}
include "../../view/includes/functions.php";



date_default_timezone_set('Europe/Berlin');
$func = $_POST['func'];
if ($func === "send_mail") {
	$uid = $_POST['uid'];
	
} else if ($func === "mail_load_pendings") {

	mail_load_pendings();
}





function mail_load_pendings()
{
	$conn = dbconnect();
	ob_start();
	$query = "SELECT * FROM `scan4_homes`WHERE scan4_status LIKE 'PENDING' ORDER BY anruf5 DESC;";
	if ($result = $conn->query($query)) {
		while ($row = mysqli_fetch_assoc($result)) {
			if (strlen($row['anruf5']) > 2) {
				$reason = 'Anruf 5';
				$reasondate = $row['anruf5'];
				$work = 'Nicht erreichbar';
			}
			if ($row['carrier'] === 'UGG') $src = 'view/images/logo_small_carrier_ugg.jpg';
			if ($row['carrier'] === 'DGF') $src = 'view/images/logo_small_carrier_dgf.jpg';
			if ($row['carrier'] === 'GVG') $src = 'view/images/logo_small_carrier_gvg.jpg';

?>
			<tr class="maillistitem" id="<?php echo $row['homeid'] ?>">
				<td><?php echo $row['scan4_status'] ?></td>
				<td class="mailitem mstate">none</td>
				<td class="mailitem mdate">none</td>
				<td class="mailitem mreason"><?php echo $reason ?></td>
				<td class="mailitem mreasondate"><?php echo $reasondate ?></td>
				<td class="mailitem mreasondate"><?php echo $work ?></td>
				<td><?php echo $row['client'] ?></td>
				<td class="mailitem carrier"><img src="<?php echo $src ?>" /></td>
				<td><?php echo $row['homeid'] ?></td>
			</tr>


	<?php
		}
		$result->free_result();
	}
	$conn->close();
	$output = ob_get_clean();
	echo $output;
}












function mail_setup($uid)
{


	$conn = dbconnect();
	$query = "SELECT scan4_hbg.*,scan4_homes.carrier,scan4_homes.city,scan4_homes.plz,scan4_homes.street,scan4_homes.streetnumber,scan4_homes.streetnumberadd,scan4_homes.unit,scan4_homes.firstname,scan4_homes.lastname,scan4_homes.phone1,scan4_homes.phone2,scan4_homes.isporder,scan4_homes.anruf1,scan4_homes.anruf2,scan4_homes.anruf3,scan4_homes.anruf4,scan4_homes.anruf5 FROM `scan4_hbg` INNER JOIN scan4_homes ON scan4_hbg.homeid=scan4_homes.homeid WHERE scan4_hbg.uid LIKE '" . $uid . "' ORDER BY scan4_hbg.id DESC;";
	$result = $conn->query($query);
	//$row = $result->fetch_row();
	$row = mysqli_fetch_assoc($result);
	$conn->close();



	$street = 'Musterweg 3a';
	$city = '01234 Musterstadt';
	$namekd = 'Mustermann, Klaus';
	// remove unecessary items
	$namekd = str_replace([',', '.'], "", $namekd);
	$homeid = 'HOME12345';
	$namebegeher = 'MichelleSchellenberg';
	$telbegeher = '0151 75398';
	$nameterminierung = 'Mahim Sikder';
	$date = '2023-01-18';
	$termintime = '14:00';




	$street = $row['street'] . ' ' . $row['streetnumber']  . $row['streetnumberadd'];
	$city = $row['plz'] . ' ' . $row['city'];
	$namekd = $row['firstname'] . ' ' . $row['lastname'];
	$homeid = $row['homeid'];
	$namebegeher = $row['hausbegeher'];
	$nameterminierung = $row['username'];
	$date = $row['date'];
	$termintime = $row['time'];
	$namekd = str_replace([',', '.'], "", $namekd);

	// split begeher username with space
	preg_match_all('/[A-Z]/', $namebegeher, $matches, PREG_OFFSET_CAPTURE);
	if (isset($matches[0][1][1])) {
		$begeher_fname = mb_substr($namebegeher, 0, $matches[0][1][1]);
		$begeher_lname = mb_substr($namebegeher, $matches[0][1][1], 30);
		$namebegeher = $begeher_fname . ' ' . $begeher_lname;
	}
	// split terminierer username with space
	preg_match_all('/[A-Z]/', $nameterminierung, $matches, PREG_OFFSET_CAPTURE);
	if (isset($matches[0][1][1])) {
		$user_fname = mb_substr($nameterminierung, 0, $matches[0][1][1]);
		$user_lname = mb_substr($nameterminierung, $matches[0][1][1], 30);
		$namebegeher = $user_fname . ' ' . $user_lname;
	}

	// set local not working => workaround
	$termininfo =  date('D, d.m.y', strtotime($date));
	$termininfo = str_replace("Mon", "Montag", $termininfo);
	$termininfo = str_replace("Tue", "Dienstag", $termininfo);
	$termininfo = str_replace("Wed", "Mittwoch", $termininfo);
	$termininfo = str_replace("Thu", "Donnerstag", $termininfo);
	$termininfo = str_replace("Fri", "Freitag", $termininfo);
	$termininfo = str_replace("Sat", "Samstag", $termininfo);
	$termininfo = str_replace("Sun", "Sonntag", $termininfo);

	if ($row['carrier'] === 'UGG') {
		$src = 'https://tucrm.scan4-gmbh.de/view/images/logo_carrier_ugg.png';
		$alt = 'UnsereGrüneGlasfaser';
	} else 	if ($row['carrier'] === 'DGF') {
		$src = 'https://tucrm.scan4-gmbh.de/view/images/logo_carrier_dgf.png';
		$alt = 'DeutscheGlasfaser';
	}


	ob_start();

	?>

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
						<table role="presentation" align="center" bgcolor="#1086fc" style="font-family: Arial; width: 500px; background: white; font-size: 14px; border-collapse: collapse;">
							<thead>
								<tr>
									<th colspan="2" style=""></th>
								</tr>
							</thead>
							<tbody>
								<tr style="background: #fff;">
									<td colspan="1" width="230" style="text-align: left;padding: 10px;vertical-align: top;">
										Im Auftrag von
									</td>
									<td colspan="1" width="230" style="text-align: left; padding: 10px; vertical-align: top;">
										<img width="85" src="<?php echo $src ?>" alt="<?php echo $src ?>" style="padding: 0px; margin: 0px;" />
									</td>
								</tr>
							</tbody>
						</table>
					</td>
					<td width="30"></td>
				</tr>
				<tr>
					<td width="30"></td>
					<td width="500" style="padding-top:10px;">
						<!-- BODY - Start -->
						<p></p>
						<!-- Greeting - Start -->
						<p style="font-family: Arial, sans-serif; font-size: 16px; line-height: 24px; padding-bottom: 8px; padding-top: 30px;">
							Sehr geehrte/r Frau/Herr <?php echo $namekd ?>,
						</p>
						<p style="font-family: Arial, sans-serif; font-size: 16px; line-height: 24px; padding-bottom: 8px; padding-top: 30px;">
							gerne bestätigen wir Ihren Termin für die Hausbegehung für Ihren Glasfaser Anschluss am Objekt:</p>
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
							Unser Techniker <?php echo $namebegeher ?> wird Sie an diesem Tag besuchen. Nachfolgend Ihre Termindetails:</p>
						<table role="presentation" align="center" bgcolor="#1086fc" style="font-family: Arial; width: 500px; background: white; font-size: 14px; border-collapse: collapse;">
							<thead>
								<tr>
									<th colspan="2" style="border-bottom: 2px solid #1086fc;"></th>
								</tr>
							</thead>
							<tbody>
								<tr style="background: #f7f7f7;">
									<td colspan="2" width="230" style="text-align: left; border-bottom: 1px solid #e6e5e5; padding: 10px; vertical-align: top;">
										<?php echo $termininfo ?> um <?php echo $termintime ?>Uhr
									</td>
								</tr>
								<tr style="background: #f7f7f7;">
									<td colspan="2" width="230" style="text-align: left; border-bottom: 1px solid #e6e5e5; padding: 10px; vertical-align: top;">
										Bitte planen Sie für den Termin ca. 30 Minuten ein.
									</td>
								</tr>
								<tr style="background: #f7f7f7;">
									<td colspan="2" width="230" style="text-align: left; border-bottom: 1px solid #e6e5e5; padding: 10px; vertical-align: top;">
										<b>Hinweis: </b>Im Laufe des Tages kann es vereinzelt zu Verzögerungen (z. B. durch Verkehr) kommen, daher kann es passieren, dass unser Techniker +/-15 Minuten früher oder später zum Termin erscheint.
									</td>
								</tr>
							</tbody>
						</table>

						<!-- Goodbye - Start -->
						<p style="font-family: Arial, sans-serif; font-size: 16px; line-height: 24px; padding-bottom: 8px; padding-top: 30px;">
							Fall Sie vorab Fragen haben oder unseren Techniker kurzfristig kontaktieren möchten, haben wir hier seine Nummer:<br>
							Tel.:<a href="tel:<?php echo $telbegeher ?>"><?php echo $telbegeher ?></a>
						</p>
						<p style="font-family: Arial, sans-serif; font-size: 16px; line-height: 24px; padding-bottom: 8px; padding-top: 30px;">
							Sie möchten den Termin verschieben oder absagen? Dann melden Sie sich bitte unter der <a href="tel:+49 721 8648 4804">+49 721 8648 4804</a>
						</p>
						<p style="font-family: Arial, sans-serif; font-size: 16px; line-height: 24px; padding-bottom: 8px;">
							Oder drücken Sie hier, um den Termin zu stornieren:</br>
							<a href="https://crm.scan4-gmbh.de/meintermin.php?rel=<?php echo $uid ?>" rel="noreferrer noopener" target="_blank"><button>Termin stornieren</button></a>
						</p>
						<p style="font-family: Arial, sans-serif; font-size: 16px; line-height: 24px; padding-bottom: 8px; padding-top: 30px;">
							Viele Gr&uuml;&szlig;e aus Karlsruhe<br /> <?php echo $nameterminierung ?>
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

<?php
	$output = ob_get_contents();
	return $output;
}
