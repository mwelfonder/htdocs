<?php


require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}
if (!(hasPerm([2, 3]) || hasPerm([13]))) {
	die();
}

// echo php info
//phpinfo();

// remove activity from overview
// replace with excel export


$a_citys = get_stats_array();


$currentuser = $logged_in->username;
echo '<div id="myusername" style="display:none;">' . $currentuser . '</div>';

?>


<script type="text/javascript" src="view/includes/js/app_dashboard_overview.js?v=1"></script>
<div style='display:none' id="heatmapContainerWrapper">
	<div style='display:none' id="heatmapContainer">
	</div>
</div>
<div class="dashboard-wrapper">
	<div class="body-content-app " id="body-content-app">
		<div id="loaderwrapper2" class="fullwidth aligncenter hidden">
			<div class="appt-loader loader"></div>
		</div>
		<div class="row bgwhite">
			<div class="row insidewrapper fullwidth">
				<div class="row overview-table">
					<div class="row ticketapp-headerwrapper">
						<div class="title-wrapper-overview">Projektübersicht 123
							<div class="projectcountwrapper"><span class="project-countercurrent">#<?php echo count($a_citys) ?></span></div>
						</div>
						<div class="filter-wrapper ticketapp">
							<?php if (hasPerm([2, 10])) { ?>
								<span id="btn_filter_ugg" data-carrier="UGG">
									<span class="btnfilter-check ugg"><i class="ri-checkbox-fill"></i></span>
									<span class="btnfilter-text">UGG</span>
								</span>
							<?php } ?>
							<?php if (hasPerm([2, 11])) { ?>
								<span id="btn_filter_dgf" data-carrier="DGF">
									<span class="btnfilter-check dgf"><i class="ri-checkbox-fill"></i></span>
									<span class="btnfilter-text">DGF</span>
								</span>
							<?php } ?>
							<?php if (hasPerm([2, 12])) { ?>
								<span id="btn_filter_gvg" data-carrier="GVG">
									<span class="btnfilter-check gvg"><i class="ri-checkbox-fill"></i></span>
									<span class="btnfilter-text">GVG</span>
								</span>
							<?php } ?>
							<?php if (hasPerm([2, 18])) { ?>
								<span id="btn_filter_glasfaserplus" data-carrier="glasfaserplus">
									<span class="btnfilter-check glasfaserplus"><i class="ri-checkbox-fill"></i></span>
									<span class="btnfilter-text">GlasfaserPlus</span>
								</span>
							<?php } ?>
						</div>

						<div class="filter-wrapper ticketapp carrier">
							<?php if (hasPerm(2) || (hasPerm([9]))) { ?>
								<span id="btn_filter_moncobra">
									<span class="btnfilter-check moncobra"><i class="ri-checkbox-fill"></i></span>
									<span class="btnfilter-text">Moncobra</span>
								</span>
							<?php } ?>
							<?php if (hasPerm(2) || (hasPerm([8]))) { ?>
								<span id="btn_filter_insyte">
									<span class="btnfilter-check insyte"><i class="ri-checkbox-fill"></i></span>
									<span class="btnfilter-text">Insyte</span>
								</span>
							<?php } ?>
							<?php if (hasPerm([2, 17])) { ?>
								<span id="btn_filter_fol">
									<span class="btnfilter-check fol"><i class="ri-checkbox-fill"></i></span>
									<span class="btnfilter-text">FOL</span>
								</span>
							<?php } ?>
						</div>

						<div class="filter-wrapper ticketapp misc">
							<span id="btn_filter_inaktiv">
								<span class="btnfilter-check inaktiv"><i class="ri-checkbox-blank-line"></i></span>
								<span class="btnfilter-text">Inaktiv</span>
							</span>
						</div>
						<div class="filter-wrapper ticketapp search">
							<input id="statstablesearch" placeholder="Search..." />
						</div>
					</div>
				</div>
			</div>
			<div class="row content-table fullwidth">
				<div class="toverview-table-wrapper">
					<div class="spacer20"></div>
					<div class="row fullwidth"><span id="placeholder"></span><span style="text-align:center;" id="subitem_system"><b>System</b></span><span style="text-align:center;" id="subitem_scan4"><b>Scan4</b></span></div>
					<table class="fullwidth">
						<table id="toverview" class="fullwidth">
							<thead>
								<th scope="col">City</th>
								<th scope="col">Total</th>
								<th scope="col">Open</th>
								<th scope="col">Done</th>
								<th scope="col">Stopped</th>
								<th scope="col">Open</th>
								<th scope="col">Planned</th>
								<th scope="col">Done</th>
								<th scope="col">Cloud</th>
								<th scope="col">Stopped</th>
								<th scope="col">Overdue</th>
								<th scope="col">Pending</th>
							</thead>
							<tbody>
								<?php
								$length = count($a_citys);
								global $currentuser;
								$cities = ['Heusenstamm', 'Hausach', 'Lautenbach', 'Hausach'];

								for ($i = 1; $i < $length; $i++) {
									if ($currentuser === 'CarstenFloeter' && in_array($a_citys[$i]["city"], $cities) || $currentuser !== 'CarstenFloeter') {

								?>
										<tr class="tr_city <?php echo $a_citys[$i]["carrier"] . ' ' .  $a_citys[$i]["client"] ?> <?php echo $a_citys[$i]["status"] ?>
							<?php if ($a_citys[$i]["status"] === "inaktiv") {
											echo " statusinaktiv statushidden";
										} ?>">
											<td class="<?php echo $a_citys[$i]["carrier"] ?> tdcityname"><span class="city"><?php echo $a_citys[$i]["city"] ?></span>
												<?php if ($a_citys[$i]["status"] === "inaktiv") {
													echo '<span class="substatus inaktiv">inaktiv</span>';
												} ?>
											</td>
											<td style="background:#;color:#000;"><span class="cellstats celltotal badges bgray"><?php echo $a_citys[$i]["total"] ?></span></td>
											<td style="background:#;color:#000;"><span class="cellstats cellsysopen badges bblue"><?php echo $a_citys[$i]["sysopen"] ?></span></td>
											<td style="background:#;color:#000;"><span class="cellstats cellsysdone badges bgreen"><?php echo $a_citys[$i]["sysdone"] ?></span></td>
											<td style="background:#;color:#000;border-right:1px solid #a6a6a6"><span class="cellstats cellsysstopped badges bred"><?php echo $a_citys[$i]["sysstopped"] ?></span></td>
											<td style="background:#;color:#000;"><span class="cellstats cellopen badges bblue"><?php echo $a_citys[$i]["open"] ?></span></td>
											<td style="background:#;color:#000;"><span class="cellstats cellplanned badges byellow"><?php echo $a_citys[$i]["planned"] ?></span></td>
											<td style="background:#;color:#000;"><span class="cellstats celldone badges bgreen"><?php echo $a_citys[$i]["done"] ?></span></td>
											<td style="background:#;color:#000;"><span class="cellstats cellclouddone badges bgreen"><?php echo $a_citys[$i]["donecloud"] ?></span></td>
											<td style="background:#;color:#000;"><span class="cellstats cellstopped badges bred"><?php echo $a_citys[$i]["stopped"] ?></span></td>
											<td style="background:#;color:#000;"><span class="cellstats celloverdue badges bpurple"><?php echo $a_citys[$i]["overdue"] ?></span></td>
											<td style="background:#;color:#000;"><span class="cellstats cellpending badges borange"><?php echo $a_citys[$i]["pending"] ?></span></td>
										</tr>
								<?php
									}
								}
								?>

							</tbody>
						</table>
				</div>

				<div style="padding-left:20px;padding-top:10px" class="row">
					<?php if ($currentuser === 'CarstenFloeter') {
						$isCarsten = 'display:none;';
					} else {
						$isCarsten = '';
					} ?>
					<table id="overviewtotals" style="<?php echo $isCarsten ?>">
						<tbody>
							<tr>
								<td style="color:#666"><span class="city">Total</span></td>
								<td style="text-align:center;cursor:pointer"><span class="cellstats celltotal badges bgray"></span></td>
								<td style="text-align:center"><span class="cellstats cellsysopen badges bblue"></td>
								<td style="text-align:center"><span class="cellstats cellsysdone badges bgreen"></td>
								<td style="text-align:center"><span class="cellstats cellsysstopped badges bred"></td>
								<td style="text-align:center"><span class="cellstats cellopen badges bblue"></td>
								<td style="text-align:center"><span class="cellstats cellplanned badges byellow"></td>
								<td style="text-align:center"><span class="cellstats celldone badges bgreen"></td>
								<td style="text-align:center"><span class="cellstats cellclouddone badges bgreen"></td>
								<td style="text-align:center"><span class="cellstats cellstopped badges bred"></td>
								<td style="text-align:center"><span class="cellstats celloverdue badges bpurple"></td>
								<td style="text-align:center"><span class="cellstats cellpending badges borange"></td>
							</tr>
						</tbody>
					</table>
				</div>

			</div>
		</div>
	</div>

	<div class="loadtable-wrapper">
		<div class="row bgwhite">
			<div id="loadtablewrapper" class="loadtable-content-wrapper hidden">

			</div>
		</div>
	</div>

</div>
</div>


<script>

</script>



<?php



function get_stats_array()
{

	if (hasPerm([8])) {  // 8 Insyte 
		$query = "SELECT * FROM `scan4_citylist` WHERE client = 'Insyte';";
	} else if (hasPerm([9])) { // 9 Moncobra
		$query = "SELECT * FROM `scan4_citylist` WHERE client = 'Moncobra';";
	} else if (hasPerm([17])) { // 17 FOL
		$query = "SELECT * FROM `scan4_citylist` WHERE client = 'FOL';";
	}
	if (hasPerm([2,3]) ) {  // 2 Admin
		$query = "SELECT * FROM `scan4_citylist` WHERE 1;";
	}

	
	$conn = dbconnect();
	$a_array = array(array());
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_assoc()) {
			$a_array[] = array(
				"city" => $row['city'], "carrier" => $row['carrier'], "client" => $row['client'], "total" => 0, "sysopen" => 0, "sysdone" => 0, "sysstopped" => 0, "open" => 0, "done" => 0, "donecloud" => 0, "planned" => 0, "stopped" => 0, "overdue" => 0, "pending" => 0,
				"nophone" => 0, "calls0" => 0, "calls1" => 0, "calls2" => 0, "calls3" => 0, "calls4" => 0, "calls5" => 0, 'status' => $row['status']
			);
		}
		$result->free_result();
	}
	// Fetch whole homes table to count in php //
	$query = "SELECT city, hbg_status, scan4_status, anruf1, anruf2, anruf3, anruf4, anruf5, phone1, phone2, phone3, phone4 FROM scan4_homes WHERE (contractstatus = 'UVS' OR contractstatus IS NULL) AND firstname != ''";


	$result = mysqli_query($conn, $query);
	$a_fullquery = array();
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_assoc()) {
			$a_fullquery[] = $row;
		}
		$result->free_result();
	}
	/*
	echo '<pre>';
	echo 'a_fullquery: ';
	print_r($a_fullquery);
	echo '</pre>';
*/
	// loop thrue array and increment //
	$length = count($a_array);
	$x = 0;
	foreach ($a_fullquery as $value) {
		$x++;
		for ($i = 1; $i < $length; $i++) {
			if ($value["city"] === $a_array[$i]["city"]) { // incr city totals
				$a_array[$i]["total"]++;
				/// === incr status === ///
				// ========== SYSTEM ============
				if ($value['hbg_status'] === "OPEN" || $value['hbg_status'] === "PLANNED") { // check city and increment status open
					$a_array[$i]["sysopen"]++;
				}
				if ($value['hbg_status'] === "DONE") { // check city and increment status open
					$a_array[$i]["sysdone"]++;
				}
				if ($value['hbg_status'] === "STOPPED") { // check city and increment status open
					$a_array[$i]["sysstopped"]++;
				}
				// =========== SCAN4 ===========
				if ($value['scan4_status'] === "OPEN") { // check city and increment status open
					$a_array[$i]["open"]++;
				} else if ($value['scan4_status'] === "CLOSED") { // check city and increment status open
					$a_array[$i]["closed"]++;
				} else if ($value['scan4_status'] === "DONE") { // check city and increment status done
					$a_array[$i]["done"]++;
				} else if ($value['scan4_status'] === "PLANNED") { // check city and increment status done
					$a_array[$i]["planned"]++;
				} else if ($value['scan4_status'] === "STOPPED") { // check city and increment status stopped
					$a_array[$i]["stopped"]++;
				} else if ($value['scan4_status'] === "OVERDUE") { // check city and increment status stopped
					$a_array[$i]["overdue"]++;
				} else if ($value['scan4_status'] === "DONE CLOUD") { // check city and increment status stopped
					$a_array[$i]["donecloud"]++;
				} else if ($value['scan4_status'] === "PENDING") { // check city and increment status stopped
					$a_array[$i]["pending"]++;
				}
				/*
				if ($a_array[$i]["city"] === 'Contwig') {
					echo '</br>homeid/' . $value[10] . '/</br>';
					echo 'phone1/' . $value[14] . '/</br>';
					echo 'phone2/' . $value[15] . '/</br>';
				}
				*/
				if (strlen($value['phone1']) < 3 && strlen($value['phone2']) < 3) { // check phonenumber
					$a_array[$i]["nophone"]++;
					//echo '</br><b>nophone/</b></br>';
				}
				/////////// Call Counter /////////
				if (strlen($value['anruf5'] > 0)) {
					$a_array[$i]["calls5"]++;
				} elseif (strlen($value['anruf4'] > 0)) {
					$a_array[$i]["calls4"]++;
				} elseif (strlen($value['anruf3'] > 0)) {
					$a_array[$i]["calls3"]++;
				} elseif (strlen($value['anruf2'] > 0)) {
					$a_array[$i]["calls2"]++;
				} elseif (strlen($value['anruf1'] > 0)) {
					$a_array[$i]["calls1"]++;
				} else {
					$a_array[$i]["calls0"]++;
				}
			}
		}
	}

	$conn->close();
	return $a_array;
}



/*
		<td style="background:#d7d7d7;color:#000;"><span class="cellstats celltotal badges abgray"><?php echo $a_citys[$i]["total"] ?></span></td>
									<td style="background:#0ca1dd4d;color:#000;"><span class="cellstats cellsysopen badges abblue"><?php echo $a_citys[$i]["sysopen"] ?></span></td>
									<td style="background:#bfedba;color:#000;"><span class="cellstats cellopen badges abgreen"><?php echo $a_citys[$i]["sysdone"] ?></span></td>
									<td style="background:#b300004d;color:#000;"><span class="cellstats cellstopped badges abred"><?php echo $a_citys[$i]["sysstopped"] ?></span></td>
									<td style="background:#0ca1dd4d;color:#000;"><span class="cellstats cellplanned badges abblue"><?php echo $a_citys[$i]["open"] ?></span></td>
									<td style="background:#debb0d4d;color:#000;"><span class="cellstats cellsysdone badges abyellow"><?php echo $a_citys[$i]["planned"] ?></span></td>
									<td style="background:#bfedba;color:#000;"><span class="cellstats celldone badges abgreen"><?php echo $a_citys[$i]["done"] ?></span></td>
									<td style="background:#bfedba;color:#000;"><span class="cellstats cellclouddone badges abgreen"><?php echo $a_citys[$i]["donecloud"] ?></span></td>
									<td style="background:#b300004d;color:#000;"><span class="cellstats celloverdue badges abred"><?php echo $a_citys[$i]["stopped"] ?></span></td>
									<td style="background:#775dd04d;color:#000;"><span class="cellstats cell5calls badges abpurple"><?php echo $a_citys[$i]["overdue"] ?></span></td>
									<td style="background:#d0965d4d;color:#000;"><span class="cellstats cell5calls badges aborange"><?php echo $a_citys[$i]["pending"] ?></span></td>
									*/