<?php


if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}
if (!securePage($_SERVER['PHP_SELF'])) {
    die();
}


$dir = $_SERVER['PHP_SELF'];
if (!securePage($_SERVER['PHP_SELF'])) {
    die('access denied');
}
if (!hasPerm([2])) {
    die('access denied');
}


include_once $_SERVER['DOCUMENT_ROOT'] . '/view/includes/functions.php';

$logged_in = $user->data();
$currentuser = $logged_in->username;


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSV Importer COM</title>
</head>

<body>
    <div id="pageContent" class="p-4" style="position: relative;">
        <div class="container-fluid" style="height: 90vh;">
            <div class="row">
                <div id="fileMenuSidebar" class="col-2">
                    <!-- Sidebar content goes here -->
                    <div class="menu-item" data-target="#content1"><i class="bi bi-filetype-csv"></i> CSV Summary</div>
                    <div class="menu-item" id="sidebarItem_city" data-target="#content-cities" style="display: none;"><i class="ri-building-line"></i> Citys</div>
                    <!-- More menu items -->
                </div>
                <div class="col-10" id="fileMenuContentWrapper">
                    <!-- Main content area -->
                    <div id="content1" class="content-section">
                        <form id="uploadForm">
                            <input type="file" id="fileInput" name="file" accept=".csv, application/vnd.ms-excel">
                            <button type="button" id="uploadBtn">Upload</button>
                            <div id="progressBarContainer" style="display: none;">
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0%;" id="uploadProgressBar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </form>
                        <div class="container mt-1">
                            <div class="row">
                                <!-- First Column -->
                                <div class="col-md-4">
                                    <div><b>Columns in File:</b> <span id="fileres_cols"></span></div>
                                    <div><b>Rows in File:</b> <span id="fileres_rows"></span></div>
                                    <div><b>Double Home Ids:</b> <span id="fileres_doubles"></span></div>
                                    <div><b>Carrier guessed:</b> <span id="fileres_carrier"></span></div>
                                </div>
                                <!-- Second Column -->
                                <div class="col-md-4">
                                    <div><b>Cities found:</b> <span id="fileres_citiestotal"></span></div>
                                    <div><b>New Cities:</b> <span id="fileres_citiesnew"></span></div>
                                    <div><b>New Customers:</b> <span id="fileres_newcustomers"></span></div>
                                </div>
                                <!-- Third Column -->
                                <div class="col-md-4">
                                    <div><b>Time taken:</b> <span id="third-col-placeholder"></span></div>
                                </div>
                            </div>
                        </div>
                        <div id="response"></div>

                        <div id="dataTable_rawData" class="mb-4"></div>
                        <div id="dataTable_processed" class="mb-4"></div>

                        <div id="clippyWrapper" style="display: none;">
                            <div id="clippy"></div>
                        </div>

                        <div id="mappingWrapper">

                        </div>
                    </div>
                    <div id="content2" class="content-section" style="display: none;">
                        <!-- Content for item 2 -->
                    </div>

                    <div id="content-cities" class="content-section" style="display: none;">
                        <!-- Tab Navigation -->
                        <ul class="nav nav-tabs" id="cityTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="city-updates-tab" data-bs-toggle="tab" data-bs-target="#city-updates" type="button" role="tab" aria-controls="city-updates" aria-selected="false">City Updates (?)</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="new-cities-tab" data-bs-toggle="tab" data-bs-target="#new-cities" type="button" role="tab" aria-controls="new-cities" aria-selected="true">New Cities (?)</button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content" id="cityTabsContent">
                            <div class="tab-pane fade" id="city-updates" role="tabpanel" aria-labelledby="city-updates-tab" style="padding: 15px;">
                                <img src="https://crm.scan4-gmbh.de/view/images/animate_chicken.gif" style="padding: 15px;">
                            </div>
                            <div class="tab-pane fade show active" id="new-cities" role="tabpanel" aria-labelledby="new-cities-tab" style="padding: 15px;">
                                <img src="https://crm.scan4-gmbh.de/view/images/animate_chicken.gif" style="padding: 15px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>

    </div>

</body>
<script type="text/javascript" src="view/includes/js/app_importme.js?v=<?php echo rand(10, 99) ?>"></script>


<style>
    .content-wrapper {
        width: 100%;
        height: 100vh;
        padding: 0;
        overflow-y: scroll;
        overflow-x: hidden;
    }

    .table-smaller {
        font-size: 0.85rem;
        /* Smaller font size */
    }

    .table-smaller th,
    .table-smaller td {
        padding: 0.3rem;
        /* Smaller padding */
    }

    .table .thead-dark th {
        color: #fff;
        background-color: #343a40;
        border-color: #454d55;
        white-space: nowrap;
    }

    #fileMenuSidebar {
        display: flex;
        flex-direction: column;
        height: 100vh;
    }

    .menu-item {
        padding: 10px;
        border-bottom: 1px solid #ddd;
        cursor: pointer;
    }

    .menu-item:hover,
    .menu-item.active {
        background-color: #f0f0f0;
    }

    .menu-item i {
        margin-right: 10px;
    }

    .headerXtras {
        display: inline-block;
    }

    .headerMappingWrapper {
        background: #fff;
        padding: 5px;
        border-radius: 4px;
        border: 1px solid #a6a6a6;
    }

    .headerMappingInner {
        padding: 6px;
        border: 1px solid #9a9a9a;
        margin: 4px;
        border-radius: 2px;
    }

    .mappedHeader {
        background: #dfdfdf;
        color: #000;
        padding: 2px 6px;
        margin: 2px;
        border-radius: 2px;
        cursor: pointer;
        user-select: none;
    }

    .mappedHeader:hover {
        background: #ebebeb;
    }

    .mappedHeader.highlight {
        background: #a6afc8;
    }

    .tab-pane {
        background: #fff;
        border-radius: 0px;
        border-left: 1px solid #dee2e6;
        border-right: 1px solid #dee2e6;
        border-bottom: 1px solid #dee2e6;
    }

    span.badge.bg-primary.rounded-pill {
        color: #fff;
    }

    li.list-group-item {
        margin-bottom: 2px;
    }

    .tmpNewCity {
        cursor: pointer;
    }
</style>

<script>
    $(document).ready(function() {
        $('#fileMenuSidebar .menu-item').click(function() {
            // Remove 'active' class from all menu items
            $('.menu-item').removeClass('active');

            // Add 'active' class to the clicked menu item
            $(this).addClass('active');

            // Hide all content sections
            $('.content-section').hide();

            // Get the target content ID from data attribute and display it
            var contentId = $(this).data('target');
            console.log('hide id', contentId)
            $(contentId).show();
        });
    });
</script>

</html>