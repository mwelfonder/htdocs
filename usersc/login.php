<?php
// This is a user-facing page
/*
UserSpice 5
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
ini_set("allow_url_fopen", 1);
require_once '../users/init.php';
require_once $abs_us_root . $us_url_root . 'users/includes/template/prep.php';
$hooks =  getMyHooks();
includeHook($hooks, 'pre');
?>
<?php

$errors = $successes = [];
if (Input::get('err') != '') {
	$errors[] = Input::get('err');
}

if ($user->isLoggedIn()) {
	Redirect::to($us_url_root . $settings->redirect_uri_after_login);
}

if (!empty($_POST)) {
	$token = Input::get('csrf');
	if (!Token::check($token)) {
		include($abs_us_root . $us_url_root . 'usersc/scripts/token_error.php');
	}

	$validate = new Validate();
	$validation = $validate->check(
		$_POST,
		array(
			'username' => array('display' => lang('GEN_UNAME'), 'required' => true),
			'password' => array('display' => lang('GEN_PASS'), 'required' => true)
		)
	);
	$validated = $validation->passed();
	// Set $validated to False to kill validation, or run additional checks, in your post hook
	$username = Input::get('username');
	$password = trim(Input::get('password'));
	$remember = false;
	includeHook($hooks, 'post');

	if ($validated) {
		//Log user in
		$user = new User();
		$login = $user->loginEmail($username, $password, $remember);
		if ($login) {
			$hooks =  getMyHooks(['page' => 'loginSuccess']);
			includeHook($hooks, 'body');
			$dest = sanitizedDest('dest');
			# if user was attempting to get to a page before login, go there
			$_SESSION['last_confirm'] = date("Y-m-d H:i:s");

			if (!empty($dest)) {
				$redirect = Input::get('redirect');
				if (!empty($redirect) || $redirect !== '') Redirect::to($redirect);
				else Redirect::to($dest);
			} elseif (file_exists($abs_us_root . $us_url_root . 'usersc/scripts/custom_login_script.php')) {

				# if site has custom login script, use it
				# Note that the custom_login_script.php normally contains a Redirect::to() call
				require_once $abs_us_root . $us_url_root . 'usersc/scripts/custom_login_script.php';
			} else {
				if (($dest = Config::get('homepage')) ||
					($dest = 'account.php')
				) {
					Redirect::to($dest);
				}
			}
		} else {
			$eventhooks =  getMyHooks(['page' => 'loginFail']);
			includeHook($eventhooks, 'body');
			logger("0", "Login Fail", "A failed login on login.php");
			$msg = lang("SIGNIN_FAIL");
			$msg2 = lang("SIGNIN_PLEASE_CHK");
			$errors[] = '<strong>' . $msg . '</strong>' . $msg2;
		}
	} else {
		$errors = $validation->errors();
	}
	sessionValMessages($errors, $successes, NULL);
}
if (empty($dest = sanitizedDest('dest'))) {
	$dest = '';
}
?>
<div class="container p-2 h-100">

	<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModal" aria-hidden="true" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<div class="row">
						<div class="loginfromheader"></div>
					</div>

				</div>
				<div class="modal-body p-4 py-5 p-md-5">
					<?php includeHook($hooks, 'body'); ?>
					<form name="login" id="login-form" class="form-signin" action="" method="post">
						<?= tokenHere(); ?>
						<div class="form-outline mb-4">
							<label class="form-label" for="username">Username</label>
							<input type="username" id="username" name="username" class="form-control form-control-lg" required autocomplete="username" />
<!-- <div>< ?php var_dump($settings); ?></div> -->
						</div>

						<div class="form-outline mb-4">
							<label class="form-label" for="password">Password</label>
							<input type="password" id="password" name="password" class="form-control form-control-lg" />

						</div>

						<?php includeHook($hooks, 'form'); ?>
						<input type="hidden" name="redirect" value="<?= Input::get('redirect') ?>" />
						<button class="submit form-control btn btn-dark rounded submit px-3" id="next_button" type="submit"><i class="fa fa-sign-in"></i> <?= lang("SIGNIN_BUTTONTEXT", ""); ?></button>
					</form>

				</div>
			</div>
		</div>
	</div>
</div>

<!-- <button type="button" class="btn btn-primary py-3 px-4"
  data-toggle="modal"
  data-bs-toggle="modal"
  data-target="#loginModal"
  data-bs-target="#loginModal">
  Launch Modal 05
</button> -->
<script>
	$(document).ready(function() {
		$("#loginModal").modal({
			backdrop: 'static',
			keyboard: false
		})
		$("#loginModal").modal('show');
		setTimeout(function() {
			$('#username').focus();
		}, 1000);
	});
</script>


<style>
	nav.navbar {
		display: none;
	}

	.modal-header {
		margin: 0 auto;
		border: none;
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
		border: none;
		margin: 0 auto;
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
		background: url(../view/images/background_scan4_login.jpg);
	}
</style>