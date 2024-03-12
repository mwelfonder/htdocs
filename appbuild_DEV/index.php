<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <link rel="manifest" href="/manifest.json">
    <title>Scan4 App</title>

    <!-- Preloading CSS and then toggling it to stylesheet once loaded -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11/font/bootstrap-icons.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/jquery-confirm@3.3/dist/jquery-confirm.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">

    <!-- Defer any JS that isn't immediately needed -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dexie@3.2/dist/dexie.min.js"></script>

    <!-- Local CSS -->
    <link rel="stylesheet" href="/css/style.css?v=1.5">
</head>


<body>


    <section class="vh-100 gradient-custom" id="loginScreen" style="position: relative; overflow: hidden;opacity:0;">
        <div style="position: absolute; top: 0; right: 0; bottom: 0; left: 0; background: url(/images/background_scan4_login.jpg) no-repeat center center; background-size: cover;"></div>
        <div style="position: relative; z-index: 1;">
            <div class="container py-5 h-100">
                <div class="row d-flex justify-content-center align-items-center h-100">
                    <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                        <div class="card bg-dark text-white" style="border-radius: 4px;border: none;background: #171717 !important;">
                            <div class="card-body p-5 text-center">
                                <div class="mb-md-5 mt-md-4 pb-5">

                                    <!-- Image with added CSS for responsive behavior and centering -->
                                    <img src="/images/logo_scan4scrm_white.png" alt="Login" style="max-width:100%; height:auto; display:block; margin:auto;" class="mb-2">

                                    <div class="form-outline form-white mb-4">
                                        <input type="email" id="typeEmailX" class="form-control form-control-lg" />
                                        <label class="form-label" for="typeEmailX">Username</label>
                                    </div>

                                    <div class="form-outline form-white mb-4">
                                        <input type="password" id="typePasswordX" class="form-control form-control-lg" />
                                        <label class="form-label" for="typePasswordX">Password</label>
                                    </div>

                                    <button id="loginButton" class="btn btn-outline-light btn-lg px-5" type="submit">Login</button>

                                    <div class="d-flex justify-content-center text-center mt-4 pt-1">
                                        <a href="#!" class="text-white"><i class="fab fa-facebook-f fa-lg"></i></a>
                                        <a href="#!" class="text-white"><i class="fab fa-twitter fa-lg mx-4 px-2"></i></a>
                                        <a href="#!" class="text-white"><i class="fab fa-google fa-lg"></i></a>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>


    <div id="mainPageWrapper" style="display: none;">

        <div id="topbarMainMenu" class="header bg-primary text-white d-flex justify-content-between align-items-center py-2 px-3">
            <h2 class="mb-0">Scan4 Termine</h2>

            <!-- Calendar Trigger Icon -->
            <div class="d-flex align-items-center">
                <div style="cursor:pointer;" id="calendarToogle">
                    <i class="bi bi-calendar-week"></i>
                </div>
                <span id="selectedDatedisplay" class="ms-2"></span>
            </div>


            <span id="statusIndicator" class="badge bg-secondary">Status: Checking...</span>
            <div class="dropdown mainmenu-dropdown">
                <i class="bi bi-list mainmenu-icon" role="button" id="mainmenuDropdownLink" data-bs-toggle="dropdown" aria-expanded="false"></i>
                <ul class="dropdown-menu dropdown-menu-end mainmenu-list" aria-labelledby="mainmenuDropdownLink">
                    <li><a class="dropdown-item" href="#" id="mainMenu_sync">Sync App</a></li>
                    <li><a class="dropdown-item" href="#" id="mainMenu_clearCach">Cach löschen</a></li>
                    <li><a class="dropdown-item" href="#" id="mainMenu_installPWA">App installieren</a></li>
                    <li><a class="dropdown-item" href="#" id="">- - - - -</a></li>
                    <li><a class="dropdown-item" href="#" id="mainMenu_clearCach">FAQ</a></li>
                </ul>
            </div>
        </div>



        <div class="container-fluid d-flex flex-column" style="height: 100vh;">
            <!-- Row for appointments and map -->
            <div class="row flex-grow-1"> <!-- Make the row grow to occupy the available space -->
                <!-- Appointments Container -->
                <div class="col-lg-4 col-md-4 col-sm-12 d-flex p-0 flex-column">
                    <div id="appointmentsContentfilter">
                        <div class="d-flex justify-content-between align-items-center">
                            <!-- Search Bar -->
                            <i class="bi bi-search"></i>
                            <div class="position-relative" style="width: 60%;">
                                <input type="text" class="form-control border-0" id="searchInput" placeholder="Durchsuchen" style="box-shadow: none; background-color: transparent;">
                            </div>
                        </div>
                    </div>
                    <div id="appointmentsContent" class="w-100">
                        <!-- The appointments will be loaded here -->
                    </div>
                </div>

                <!-- Map Container -->
                <div class="col-lg-8 col-md-8 col-sm-12 d-lg-block d-md-block d-sm-none d-flex p-0">
                    <div id="leaflet" class="w-100">
                        <!-- Map Placeholder -->
                    </div>
                </div>


            </div>
        </div>

        <!-- Calendar Skeleton -->
        <div id="customCalendar" class="custom-calendar-container" style="display: none;">
            <div class="custom-calendar">
                <div class="custom-calendar-header">
                    <button id="customPrevMonth" class="btn"><i class="bi bi-chevron-left"></i></button>
                    <span id="customMonthYear"></span>
                    <button id="customNextMonth" class="btn"><i class="bi bi-chevron-right"></i></button>
                </div>
                <table class="custom-calendar-table">
                    <thead>
                        <tr>
                            <th>Mo</th>
                            <th>Di</th>
                            <th>Mi</th>
                            <th>Do</th>
                            <th>Fr</th>
                            <th>Sa</th>
                            <th>So</th>
                        </tr>
                    </thead>
                    <tbody id="customCalendarBody"></tbody>
                </table>
            </div>
        </div>



        <div id="surveyPage" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: white; z-index: 10000; overflow-y: auto;">
            <?php include 'survey.php'; ?>
        </div>


        <div id="surveyPagePrint" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: white; z-index: 10000; overflow-y: auto;">
            <?php include 'surveyPrint.php'; ?>
        </div>
        <div id="surveyPagePrint_loader" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: white; z-index: 10001; overflow-y: auto;">
            <img src="/images/loadingdots.gif" alt="Login" style="max-width:100%; height:auto; display:block; margin:auto;" class="mb-2">
        </div>


        <div class="modal fade" id="infoModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-body">
                        <!-- Bootstrap Nav Tabs -->
                        <ul class="nav nav-tabs" id="infomodaltabs" role="tablist">
                            <li class="nav-item" role="presentation" id="tabNav_hometab">
                                <a class="nav-link active" id="home-tab" data-bs-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Kunde</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="history-tab" data-bs-toggle="tab" href="#history" role="tab" aria-controls="history" aria-selected="false">Historie</a>
                            </li>
                            <li class="nav-item" role="presentation" id="tabNav_hbgsurvey">
                                <a class="nav-link" id="hbgsurvey-tab" data-bs-toggle="tab" href="#hbgsurvey" role="tab" aria-controls="hbgsurvey" aria-selected="false"><i class="bi bi-door-open-fill"></i> HBG</a>
                            </li>
                            <li class="nav-item dropdown" role="presentation" id="tabNav_hbgstatus">
                                <a class="nav-link dropdown-toggle" href="#" id="hbgStatusDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    HBG Status
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="hbgStatusDropdown">
                                    <li><a class="dropdown-item" href="#" id="hbgDoneOption"><i style="color: #1ab300;" class="bi bi-check2-square"></i> HBG Erledigt</a></li>
                                    <li><a class="dropdown-item" href="#" id="hbgCancelOption"><i style="color: #bf6300;" class="bi bi-x-square"></i> HBG Abbruch</a></li>
                                </ul>
                            </li>
                            <li class="nav-item" role="presentation" id="tabNav_apperror">
                                <a class="nav-link" id="apperror-tab" data-bs-toggle="tab" href="#apperror" role="tab" aria-controls="apperror" aria-selected="false">App Error</a>
                            </li>
                            <li class="nav-item close-tab-item" role="presentation">
                                <a class="nav-link btn-close" id="close-tab" data-bs-toggle="tab" href="#closeModal" role="tab" aria-controls="extra" aria-selected="false" data-bs-dismiss="modal"></a>
                            </li>
                        </ul>
                        <!-- Tab panes -->
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                                <div class="tab-container p-4">
                                    <!-- Content for Home tab -->
                                    <div class="row align-items-center mb-1" id="apptTechnicanNoteWrapper">
                                        <div class="alert alert-warning" role="alert" id="apptTechnicanNote">
                                        </div>
                                    </div>
                                    <div class="row align-items-center mb-1">
                                        <div class="col-3 text-right">
                                            <label for="appointment" class="pr-2">Termin:</label>
                                        </div>
                                        <div class="col-9">
                                            <p id="appointment" class="placeholder-text mb-0">[Placeholder]</p>
                                        </div>
                                    </div>
                                    <div class="row align-items-center mb-1">
                                        <div class="col-3 text-right">
                                            <label for="appthomeid" class="pr-2">HomeID:</label>
                                        </div>
                                        <div class="col-9">
                                            <p id="appthomeid" class="placeholder-text mb-0">[Placeholder]</p>
                                        </div>
                                    </div>
                                    <div class="row align-items-center mb-1">
                                        <div class="col-3 text-right">
                                            <label for="name" class="pr-2">Name:</label>
                                        </div>
                                        <div class="col-9">
                                            <p id="name" class="placeholder-text mb-0">[Placeholder]</p>
                                        </div>
                                    </div>
                                    <div class="row align-items-center mb-1">
                                        <div class="col-3 text-right">
                                            <label for="address" class="pr-2">Adresse:</label>
                                        </div>
                                        <div class="col-9">
                                            <p id="address" class="placeholder-text mb-0">[Placeholder]</p>
                                        </div>
                                    </div>
                                    <div class="row align-items-center mb-1">
                                        <div class="col-3 text-right">
                                            <label for="aptdate" class="pr-2">Vereinbart am:</label>
                                        </div>
                                        <div class="col-9">
                                            <p id="aptdate" class="placeholder-text mb-0">[Placeholder]</p>
                                        </div>
                                    </div>
                                    <div class="row align-items-center mb-1">
                                        <div class="col-3 text-right">
                                            <label for="aptphone" class="pr-2">Tele:</label>
                                        </div>
                                        <div class="col-9">
                                            <p id="aptphone" class="placeholder-text mb-0">[Placeholder]</p>
                                        </div>
                                    </div>
                                    <div class="row align-items-center mb-1">
                                        <div class="col-3 text-right">
                                            <label for="aptuser" class="pr-2">Vereinbart von:</label>
                                        </div>
                                        <div class="col-9">
                                            <p id="aptuser" class="placeholder-text mb-0">[Placeholder]</p>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
                                <!-- Content for history tab -->
                            </div>

                            <div class="tab-pane fade" id="hbgsurvey" role="tabpanel" aria-labelledby="hbgsurvey-tab">
                                <div class="tab-container p-4">
                                    <!-- Content for survey tab -->
                                    <div class="mb-3">
                                        <div class="alert alert-info" id="surveryIsStarted" role="alert">Die Hausbegehung wurde noch nicht gestartet.</div>
                                    </div>
                                    <div class="mb-3">
                                        <button type="button" class="btn btn-info" id="startSurvey"> <i class="bi bi-door-open"></i> HBG Starten</button>

                                    </div>

                                </div>
                            </div>

                            <div class="tab-pane fade" id="hbgdone" role="tabpanel" aria-labelledby="hbgdone-tab">
                                <div class="tab-container p-4">
                                    <!-- Content for hbgdone tab -->
                                    <!-- Comment field -->
                                    <div class="mb-3">
                                        <label for="commentField" class="form-label">Kommentar</label>
                                        <textarea class="form-control" id="commentField_done" rows="3" placeholder="Anmerkungen zum Protokoll"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <div id="alertArea_pdfhbgdone" class="alertArea"></div>
                                    </div>
                                    <!-- File drop area -->
                                    <div class="mb-3">
                                        <label for="fileDropArea" class="form-label">Protokoll anhängen</label>
                                        <input class="form-control mb-3" type="file" id="fileDropArea_done" accept="application/pdf">
                                        <div class="mb-3" id="pdf-viewer" style="display: none;">
                                            <object id="pdf-object" type="application/pdf" width="100%" height="500px">
                                                <p>Unable to display the PDF. <a href="#" id="pdf-download-link">Click here to download the PDF</a>.</p>
                                            </object>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="hbgcancel" role="tabpanel" aria-labelledby="hbgcancel-tab">
                                <div class="tab-container p-4">
                                    <div class="btn-group mb-3" role="group" aria-label="Cancellation reasons" id="cancelReasons">
                                        <!-- Eigene Ursache Dropdown -->
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-outline-danger2 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" id="eigeneUrsacheButton" data-initial-text="Eigene Ursache" data-value="">
                                                Eigene Ursache
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" data-value="fahrzeit_zu_lang">Fahrzeit zu lang</a></li>
                                                <li><a class="dropdown-item" href="#" data-value="im_verzug">Im Verzug</a></li>
                                            </ul>
                                        </div>
                                        <!-- Kunden Ursache Dropdown -->
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-outline-danger2 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" id="kundenUrsacheButton" data-initial-text="Kunden Ursache" data-value="">
                                                Kunden Ursache
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" data-value="kunde_nicht_da">Kunde nicht da</a></li>
                                                <li><a class="dropdown-item" href="#" data-value="kunde_nicht_gefunden">Kunde nicht gefunden</a></li>
                                                <li><a class="dropdown-item" href="#" data-value="anderes_problem">Anderes Problem</a></li>
                                            </ul>
                                        </div>
                                        <!-- HBG nicht durchführbar Dropdown -->
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-outline-danger2 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" id="hbgNichtDurchfuehrbarButton" data-initial-text="HBG nicht durchführbar" data-value="">
                                                HBG nicht durchführbar
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" data-value="techn_nicht_moeglich">Techn. nicht möglich</a></li>
                                                <li><a class="dropdown-item" href="#" data-value="kunde_verweigert_hbg">Kunde verweigert HBG</a></li>
                                                <li><a class="dropdown-item" href="#" data-value="falsche_adresse">Falsche Adresse</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <!-- TakePic field -->
                                    <div id="pic_kdabbruchWrapper">
                                        <div class="mb-3">
                                            <div class="alert alert-warning" role="alert">
                                                Bitte nimm ein ein Foto des Hauses/Briefkastens des Kunden als Beweisfoto auf.
                                            </div>
                                        </div>
                                        <label class="takePic btn btn-primary mr-2">
                                            <i class="bi bi-camera"></i> Kamera<span style="color:red;">*</span>
                                            <input type="file" accept="image/*;capture=camera" capture="camera" style="display: none;" multiple data-context="pic_kdabbruch" id="pic_kdabbruch">
                                        </label>
                                    </div>
                                    <!-- Comment field -->
                                    <div class="mb-3">
                                        <div id="alertArea_cancelcomment" class="alertArea"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="commentField_cancel" class="form-label">Kommentar<span style="color:red;">*</span></label>
                                        <textarea class="form-control" id="commentField_cancel" rows="3" placeholder="Bemerkung zum Abbruch"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="apperror" role="tabpanel" aria-labelledby="apperror-tab">
                                <div class="tab-container p-4">
                                    <!-- Content for hbgdone tab -->
                                    <!-- Comment field -->
                                    <div class="mb-3">
                                        <label for="commentField" class="form-label">Beschreibung des Problems</label>
                                        <textarea class="form-control" id="commentField_apperror" rows="3" placeholder="Error Beschreibung"></textarea>
                                    </div>

                                    <!-- File drop area -->
                                    <div class="mb-3">
                                        <label for="fileDropArea" class="form-label">Screenshot oder PDF anhängen</label>
                                        <input class="form-control mb-3" type="file" id="fileDropArea_apperror" accept="application/pdf">
                                        <div class="mb-3" id="pdf-viewer" style="display: none;">
                                            <object id="pdf-object" type="application/pdf" width="100%" height="500px">
                                                <p>Unable to display the PDF. <a href="#" id="pdf-download-link">Click here to download the PDF</a>.</p>
                                            </object>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="extra" role="tabpanel" aria-labelledby="extra-tab">
                                <!-- Content for Extra tab -->
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-confirm@3.3/dist/jquery-confirm.min.js" defer></script>
    <script src="https://unpkg.com/@popperjs/core@2" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/fabric@5.3.0/dist/fabric.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8/hammer.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.6/dist/signature_pad.umd.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@2" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js" defer></script>
    <script src="https://unpkg.com/jspdf@latest/dist/jspdf.umd.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" defer></script>



    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/service-worker.js')
                .then(registration => {
                    console.log('Service Worker registered with scope:', registration.scope);
                })
                .catch(error => {
                    console.error('Service Worker registration failed:', error);
                });
        }
        // Polyfill for rel="preload" support
        if (!('onload' in document.createElement('link'))) {
            const links = document.querySelectorAll('link[rel=preload]');
            for (let i = 0; i < links.length; i++) {
                const link = links[i];
                link.rel = 'stylesheet';
            }
        }
    </script>
    <script src="/js/main.js?v=<?php echo time(); ?>"></script>

</body>

</html>