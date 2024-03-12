<?php



/// remove total for client view


if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}



$dir = $_SERVER['PHP_SELF'];
//echo $dir . "</br>";
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}
if (!hasPerm([2])) {
	die();
}


$logged_in = $user->data();
$currentuser = $logged_in->username;
$userdetails = fetchPermissionUsers(1); // 5 = Telefonist
for ($i = 0; $i < count($userdetails); $i++) {
	//$data[] = fetchUserDetails('username', 'DenisAqunio', null);
	//$a_userlist =  array('username' => $data->username, 'pic' => $data->profile_pic, 'nextcloud' => $data->calendarhook);
}
//upd_tablecheck(); // parse all phone numbers to new format
?>
<script type="text/javascript" src="view/includes/js/app_import.js"></script>
<div class="body-content-app">
	<div class="row app-import-wrapper">
		<div class="row">
			<div class="col-2 app-import-sidebar" id="app-import-sidebar">
				<div class="uploadformwrapper">
					<div class="row">
						<div id="file-upload-div" class="col-12 border p-4 text-center">
							Drag files here or click to select files
							<input type="file" name="files[]" id="file-input" multiple style="display: none;" accept=".csv" />
						</div>
					</div>
					<div class="row">
						<div id="file-details-div" class="col-12 mt-1 p-0"></div>
					</div>
					<div class="row">
						<div class="col-12">
							<button id="file-upload-button" class="btn btn-primary mt-2" type="button" style="width: 100%;" disabled>
								<i class="fa-solid fa-cloud-arrow-up"></i> Upload
							</button>
						</div>
					</div>
				</div>
			</div>
			<div class="col-5">
				<div id="tabWrapper"></div>
			</div>

			<div class="col-5">
				<h5>Console</h5>
				<div id="console" class="initial">

				</div>
			</div>
		</div>
		<div class="row">
			<div class=""><button class="btn btn-primary" id="export_dp">Export DPs</button></div>
		</div>

		<div class="row" style="display: none;">
			<div class="col app-import-main">
				<div id="app-import-content1" class="app-import-content"></div>
			</div>

			<div class="col app-import-main">
				<div id="app-import-content2" class="app-import-content"></div>
			</div>
		</div>
	</div>


</div>



<script>

</script>

<style>
	@keyframes spin {
		0% {
			transform: rotate(0deg);
		}

		100% {
			transform: rotate(360deg);
		}
	}

	.spinner {
		display: inline-block;
		animation: spin 2s linear infinite;
	}

	.nav-item:not(.active) {
		border: 1px solid #ededed;
		border-top-left-radius: 5px;
		border-top-right-radius: 5px;
	}

	#console.initial {
		background-color: #000;
		color: #c0c0c0;
		font-family: monospace;
		padding: 10px;
		height: 300px;
		max-height: 300px;
		overflow-y: auto;
		animation: neonFlicker 2.5s infinite;
		white-space: pre;
		border: 2px solid #c0c0c0;
		border-radius: 5px;
	}

	#console {
		background-color: #000;
		color: #c0c0c0;
		font-family: monospace;
		padding: 10px;
		height: 300px;
		max-height: 300px;
		overflow-y: auto;
		border: 2px solid #c0c0c0;
		border-radius: 5px;
	}

	.row.app-import-wrapper {
		max-width: 95%;
		margin: 0 auto;
		padding: 20px 10px;
		background: #fff;
		box-shadow: 0 2px 6px 0 rgb(67 89 113 / 12%);
		border-radius: 0.5rem;
		min-height: 70vh;
		height: auto;
	}

	.content-wrapper {
		width: 100%;
		min-height: 100vh;
		padding: 0;
	}

	.tab-content.uploader {
		max-height: 400px;
		overflow: auto;
	}

	.tab-content.uploader {
		max-height: 400px;
		overflow: auto;
		border: 1px solid #dee2e6;
		border-bottom-left-radius: 4px;
		border-bottom-right-radius: 4px;
		padding: 14px 4px;
	}

	.fileItemWrap {
		border: 1px solid #fff;
		background: #e4e4e4;
		margin: 0;
		padding: 2px 0px;
	}
</style>



<?php
