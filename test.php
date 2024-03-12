<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}
if (!(hasPerm([2]) || hasPerm([3]) || hasPerm([13]))) {
	die();
}

$perm_telefonist = fetchPermissionUsers(5); // 5 = Telefonist

/*
echo '<pre>';
echo print_r($perm_telefonist);
echo '</pre>';

*/


include_once $_SERVER['DOCUMENT_ROOT'] . '/view/includes/functions.php';

use sc4_mail\Statistics;

?>
<div class="row">
	<?php
	$stats = new Statistics();
	$date = "2023-03-02"; // Replace with your desired date
	$counts = $stats->hbg_appointments($date);

	//echo '<pre>';
	//echo print_r($counts);
	//echo '</pre>';
	?>
	<table class="table table-striped">
		<thead>
			<tr>
				<th>Name</th>
				<th><span id="hbg_o_done" class="mystat stattotal mystat_done"><i class="ri-checkbox-circle-line"></i> 0</span></th>
				<th><span id="hbg_o_abbr" class="mystat stattotal mystat_canceld"><i class="ri-close-circle-line"></i> 0</span></th>
				<th><span id="hbg_o_open" class="mystat stattotal mystat_open"><i class="ri-question-line"></i> 0</span></th>
				<th><span id="hbg_o_sum" class="mystat stattotal mystat_total"><i class="ri-hashtag"></i> 0</span></th>
				<th><span id="hbg_o_checked" class="mystat stattotal mystat_checked"><i class="ri-check-double-line"></i> 0</span></th>
			</tr>
		</thead>
		<tbody>

			<?php foreach ($plannedarray as $person => $count) { ?>
				<tr>
					<td><?php echo $person ?></td>
					<td></td>
					<td></td>

					<td></td>
					<td><?php echo $count['PLANNED'] ?></td>
				</tr>
			<?php } ?>

		</tbody>
	</table>
</div>