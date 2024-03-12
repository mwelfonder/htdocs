<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}
if (!hasPerm([2, 7, 15, 27])) {
	die();
}


$username = $user->data()->username;
?>





<script type="text/javascript" src="view/includes/js/app_hbgcheckv2.js?v=1.0"></script>




<!-- Zahnrad mit Icons -->
<div class="gear-container">
	<div class="gear-icon-container"> <!-- Ein separater Container für das Zahnrad-Icon -->
		<i class="ri-settings-line gear-icon"></i>
	</div>
	<div class="icons">
		<div class="icon"><i class="ri-checkbox-circle-line"></i></div>
		<div class="icon"><i class="ri-check-double-line"></i></div>
		<div class="icon"><i class="ri-question-line"></i></div>
		<div class="icon"><i class="ri-close-circle-line"></i></div>
	</div>
</div>


<div class="body-content-app" id="body-content-app">
	<div id="hbgcheck" class="row app-wrapper activation">
		<div id="loaderwrapper" class="fullwidth aligncenter hidden">
			<div class="appt-loader loader"></div>
		</div>
		<div class="row app-content-wrapper hbgcheck">

			<div class="row">
				<div class="col-md-auto checksidebar">
					<div class="row app-check-header-wrapper">
						<div class="checkerheader-content">
							<div class="header-inner">
								<div class="row">
									<div class="col">
										<span id="logfile" class="headerinner-item form-control">Logfile</span>
									</div>

									<div class="col">
										<input class="form-control" id="hbgdatepick" type="text" placeholder="Datum">
									</div>
								</div>
							</div>
						</div>
					</div>
					<ul id="hbguserlist" class="hbguserlist"></ul>
				</div>
				<div class="col checkcontent">

					<div id="loaderwrapper2" class="fullwidth aligncenter hidden">
						<div class="appt-loader loader"></div>
					</div>
					<div id="checkdisplaycontent" class="fullwidth"></div>




				</div>
			</div>
			<div id="hbgcheck_empty" class="row app-wrapper hbgcheck _empty hidden">
				<div class="hbgcheckempty">Keine HBGs zum Überprüfen gefunden</div>
			</div>
		</div>
	</div>
</div>


<style>
	.swal2-icon {
		position: absolute;
		background: #fff;
		margin: 0 auto;
		left: 50%;
	}

	/*
	.checkwrapperitem.hbgdone
	.checked,
	.checkwrapperitem.hiadden.hbgopen.
	.checkwrapperitem.hbgfailed.checked 

	}*/




	.col.checkcontent {
		position: relative;
		/* Damit das Zahnrad und die Icons relativ zu diesem Container positioniert werden */
	}

	.gear-container {
		position: absolute;
		top: 3px;
		right: 10px;
		z-index: 1000;
		display: flex;
		flex-direction: column;
		align-items: flex-end;
	}

	.gear-icon-container {
		cursor: pointer;
		padding: 25px 10px 15px 10px;
		background-color: transparent;
	}

	.gear-icon {
		padding: 25px 10px 15px 10px;
		font-size: 24px;
	}

	.icons {
		flex-direction: column;
		gap: 5px;
		align-items: center;
	}

	.icon {
		color: #90EE90;
		padding: 10px;
		font-size: 20px;
		transform: translateY(-15px);
		opacity: 0;
		transition: transform 0.5s, opacity 0.5s;
	}

	.icon.active {
		color: green;
	}

	.icon:not(.active) {
		color: red;
	}
</style>


<script>
	document.querySelector(".gear-icon").addEventListener("click", function() {
		const icons = document.querySelectorAll(".icon");
		icons.forEach((icon, index) => {
			setTimeout(() => {
				if (icon.style.opacity === "0") {
					icon.style.transform = `translateY(${(index + 1) * 40}px)`;
					icon.style.opacity = "1";
				} else {
					icon.style.transform = "translateY(-30px)";
					icon.style.opacity = "0";
				}
			}, index * 100);
		});
	});

	document.addEventListener('DOMContentLoaded', function() {
		const icons = document.querySelectorAll('.icon');
		const associatedClasses = [
			".checkwrapperitem.hbgdone",
			".checked",
			".checkwrapperitem.hiadden.hbgopen",
			".checkwrapperitem.hbgfailed"
		];

		icons.forEach((icon, index) => {
			// Setze die Icons standardmäßig als "aktiv"
			icon.classList.add('active');

			icon.addEventListener('click', function() {
				const items = document.querySelectorAll(associatedClasses[index]);
				if (icon.classList.contains('active')) {
					items.forEach(item => {
						item.style.display = 'none';
					});
					icon.classList.remove('active');
				} else {
					items.forEach(item => {
						item.style.display = 'block';
					});
					icon.classList.add('active');
				}
			});
		});
	});
</script>