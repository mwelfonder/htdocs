<?php
require_once('../../../users/init.php');
require_once $abs_us_root . $us_url_root . 'users/includes/template/prep.php';
require_once('assets/vendor/autoload.php');

if (!isset($user) || !$user->isLoggedIn()) {
	
	Redirect::to($us_url_root . 'users/login.php');
}


use PragmaRX\Google2FA\Google2FA;

$google2fa = new Google2FA();

if ($user->data()->twoKey == NULL) {
	$user->update(["twoKey" => $google2fa->generateSecretKey()], $user->data()->id);
	Redirect::to($us_url_root . 'usersc/plugins/two_factor/enable2FA.php'); //It has to update so refresh the page
}

if ($user->data()->twoEnabled == 1) {
	Redirect::to($us_url_root . "users/account.php");
}

$siteName = $db->query("SELECT site_name FROM settings")->first()->site_name;

$google2fa_url = $google2fa->getQRCodeUrl(
	$siteName,
	$user->data()->email,
	$user->data()->twoKey
);
?>

<style>
	nav.navbar {
    display: none;
}
	.body.loginpage {
		height: 100vh;
		display: flex;
		align-items: center;
		justify-content: center;
		background: #2c2c2c;
	}

	.loginform-wrapper {
		background: #fff;
		padding: 50px 25px;
		border-radius: 4px;
	}

	.loginfromheader {
		background: url(../view/images/logo_scan4scrm_grey.png);
		width: 305px;
		height: 100px;
		background-size: contain;
		background-repeat: no-repeat;
		margin-bottom: 25px;
	}

	.btn.login {
		border: 2px solid #5e76df;
		background: #5e76df;
		color: #fff;
		font-weight: 600;
		width: 100%;
	}

	.btn.login:hover {
		border: 2px solid #5e76df;
		background: #fff;
		color: #5e76df;
	}

	button#next_button {
		margin-top: 20px;
	}

	.body.loginpage {
		height: 100vh;
		display: flex;
		align-items: center;
		justify-content: center;
		background: #2c2c2c;
		background: url(https://crm.scan4-gmbh.de/view/images/background_scan4_login.jpg);
	}

	div#qrcode {
		display: flex;
		justify-content: center;
	}

	.justifyrowcenter {
		display: flex;
	}
</style>
<script type="text/javascript" src="<?php echo $us_url_root . "usersc/plugins/two_factor/assets/qrcode.min.js" ?>"></script>
<div id="page-wrapper" class="loginform-wrapper">
	<section class="cid-qABkfm0Pyl mbr-fullscreen mbr-parallax-background" id="header2-0" data-rv-view="1854">

		<div class="mbr-overlay" style="opacity: 0.4; background-color: rgb(40, 0, 60);"></div>
		<div class="container">
			<div style="text-align:center;" class="col">
				<h1>Two Factor Authentication</h1>
				<?php if ($user->data()->twofaforced == 1 || $settings->forcetwofa == 1) {
				} ?>
				<p>Scanne diesen QR Code mit deiner Authentikator App.</br>
					Authentifizierungscode: <b><?php echo $user->data()->twoKey; ?></b></p>
				<p>
				<div id="qrcode"></div>
				</p>
				<p>Gib deinen 6 stelligen Code hier ein:</p>
				<p class="justifyrowcenter">
					<input class="form-control" placeholder="2FA Code" type="text" name="twoCode" id="twoCode" size="10">
					<button id="twoBtn" class="btn btn-primary">Verifizieren</button>
				</p>
			</div>
		</div>
	</section>
</div>
<!-- footers -->


<!-- Place any per-page javascript here -->
<script type="text/javascript">
	new QRCode(document.getElementById("qrcode"), "<?php echo $google2fa_url ?>");
</script>
<script>
	$(document).ready(function() {
		$("#twoBtn").click(function(e) {
			e.preventDefault();
			$.ajax({
				type: "POST",
				url: "<?= $us_url_root; ?>usersc/plugins/two_factor/assets/verify.php",
				data: {
					action: "verify2FA",
					twoCode: $("#twoCode").val()
				},
				success: function(result) {
					if (!result.error) {
						alert('2FA verified and enabled.');
						window.location.href = '<?= $us_url_root ?>users/account.php';
					} else {
						alert('Incorrect 2FA code.');
					}
				},
				error: function(result) {
					alert('There was a problem verifying 2FA.');
				}
			});
		});
	});
</script>
