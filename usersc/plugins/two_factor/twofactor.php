<?php
require_once '../../../users/init.php';
require_once $abs_us_root . $us_url_root . 'users/includes/template/prep.php';
require_once 'assets/vendor/autoload.php';
//var_dump($user);
// echo $user->isLoggedIn()."<br />";
// die("???");
use PragmaRX\Google2FA\Google2FA;

$google2fa = new Google2FA();
?>
<?php
$errors = [];
$successes = [];
if (@$_REQUEST['err']) $errors[] = $_REQUEST['err']; // allow redirects to display a message

// $x = ($user->isLoggedIn()) ? die("eingeloggt") : die("no");


if ($user->isLoggedIn()) Redirect::to($us_url_root . 'index.php');

if (isset($_POST['twoCode'])) {

	$token = Input::get('csrf');
	if (!Token::check($token)) {
		include($abs_us_root . $us_url_root . 'usersc/scripts/token_error.php');
	}
	$twoPassed = false;
	$twoQ = $db->query("SELECT twoKey FROM users WHERE id = ? and twoEnabled = 1", [$_SESSION['twouser']]);
	if ($twoQ->count() > 0) {
		$twoKey = $twoQ->results()[0]->twoKey;
		$twoCode = trim(Input::get('twoCode'));
		if ($google2fa->verifyKey($twoKey, $twoCode)) {
			$twoPassed = true;
		}
	} else { //Two Factor is Disabled
		$twoPassed = true;
	}
	//Finish Login Process
	if ($twoPassed) {
		$sessionName = Config::get('session/session_name');
		Session::put($sessionName, $_SESSION['twouser']);
		$user = new User();
		if ($_SESSION['rememberme'] && pluginActive('rememberme')) {
			$_POST['remember'] = 'on';
			require $abs_us_root . $us_url_root . 'usersc/plugins/rememberme/hooks/login_success.php';
		}
		unset($_SESSION['twouser']);
		$dest = sanitizedDest('dest');

		if (!empty($dest)) {
			$redirect = htmlspecialchars_decode(Input::get('redirect'));
			if (!empty($redirect) || $redirect !== '') Redirect::to($us_url_root . 'route.php');
			else Redirect::to($us_url_root . 'route.php');
		} 
		elseif (file_exists($abs_us_root . $us_url_root . 'usersc/scripts/custom_login_script.php')) {
			require_once $abs_us_root . $us_url_root . 'usersc/scripts/custom_login_script.php';
		} 
		else {
			if ($dest = Config::get('homepage') || $dest = 'account.php') {
				Redirect::to($us_url_root . 'route.php');
			}
		}
	} 
	else {		
		$msg = lang("SIGNIN_FAIL");
		$msg2 = lang("SIGNIN_PLEASE_CHK");
		$errors[] = '<strong>' . $msg . '</strong> Please check your mobile device for the correct 6 digit code.';
	}
}
if (empty($dest = sanitizedDest('dest'))) {
	$dest = '';
}
$token = Token::generate();
sessionValMessages($errors, $successes, NULL);
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
<div id="page-wrapper" class="loginform-wrapper">
	<div class="container">
		<div class="row">
			<div class="col-sm-12">
				<form name="login" id="login-form" class="form-signin" action="twofactor.php" method="post" onsubmit="return validateInput()">
					<h2 class="form-signin-heading"></i>2 Faktor Authentifikation</h2>
					<input type="hidden" name="dest" value="<?= $dest ?>" />
					<div class="form-group">
						<label for="twoCode">2 Faktor Authentifikation Code eingeben.</label>
						<input type="text" class="form-control" name="twoCode" id="twoCode" placeholder="2FA Code" autocomplete="off" onkeyup="this.value=this.value.replace(/[^\d]/,'')" maxlength="6" size="6">
					</div>
					<input type="hidden" name="csrf" value="<?= $token ?>">
					<input type="hidden" name="redirect" value="<?= Input::get('redirect') ?>" />
					<button class="submit  btn  btn-primary" id="next_button" type="submit"><i class="fa fa-sign-in"></i> <?= lang("SIGNIN_BUTTONTEXT", ""); ?></button>
				</form>
			</div>
		</div>
	</div>
</div>
<?php require_once $abs_us_root . $us_url_root . 'usersc/templates/' . $settings->template . '/container_close.php'; //custom template container 
?>

<!-- footers -->
<?php require_once $abs_us_root . $us_url_root . 'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls 
?>

<!-- Place any per-page javascript here -->
<script>
	function validateInput() {
		var result = false;
		if (($('#twoCode').val().length == 6)) {
			result = true;
		}
		return result;
	}
</script>

<?php require_once $abs_us_root . $us_url_root . 'usersc/templates/' . $settings->template . '/footer.php'; //custom template footer
?>