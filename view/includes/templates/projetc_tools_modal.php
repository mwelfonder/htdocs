<?php

?>

<div class="modal fade custom-modal" id="projectToolsModal" tabindex="-1" aria-labelledby="dynamicModalLabel" aria-hidden="true" style="z-index: 9999999;">
    <div class="modal-dialog modal-lg custom-modal-size">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dynamicModalLabel">Projekt Manager</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Sidebar (1/3 width) -->
                    <div class="col-md-3" style="border-right: 1px solid #dedde0;">
                        <ul class="list-group prj_manager_group" id="projectTabs">
                            <li style="cursor:pointer;" class="list-group-item d-flex align-items-center mb-3 active" data-bs-toggle="list" data-bs-target="#allProjects" role="button">
                                <button type="button" class="btn btn-primary mr-2" style="width: 40px; height: 40px;">1</button>
                                All Projects
                            </li> 
                            <li id="newProjectTab" style="cursor:pointer;" class="list-group-item d-flex align-items-center mb-3" data-bs-toggle="list" data-bs-target="#newProject" role="button">
                                <button type="button" class="btn btn-outline-primary mr-2" style="width: 40px; height: 40px;">2</button>
                                New Project
                            </li>
                            <!-- Add more items as needed -->
                        </ul>
                    </div>
                    <!-- Main content area (2/3 width) -->
                    <div class="col-md-9 tab-content p-0">
                        <div class="tab-pane fade show active" id="allProjects" role="tabpanel" aria-labelledby="all-projects-tab">
                            <!-- Content for All Projects -->
                        </div>
                        <div class="tab-pane fade" id="newProject" role="tabpanel" aria-labelledby="new-project-tab">
                            <!-- Content for New Project -->

                            <!-- Project Name and Project ID -->
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="projectNameInput">Project Name</label>
                                    <input type="text" id="projectNameInput" class="form-control" placeholder="Enter Project Name">
                                </div>
                                <div class="col-md-6">
                                    <label for="projectIdInput">Project ID</label>
                                    <input type="number" id="projectIdInput" class="form-control" placeholder="Enter Project ID">
                                </div>
                            </div>

                            <!-- Carrier and Client Dropdowns -->
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <!-- Carrier Dropdown -->
                                    <label for="modalCarrierSelect">Carrier</label>
                                    <select id="modalCarrierSelect" class="form-control">
                                        <option value="">Select a Carrier</option>
                                        <!-- Dynamically loaded options will go here -->
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <!-- Client Dropdown -->
                                    <label for="modalClientSelect">Client</label>
                                    <select id="modalClientSelect" class="form-control">
                                        <option value="">Select a Client</option>
                                        <!-- Dynamically loaded options will go here -->
                                    </select>
                                </div>
                            </div>

                            <!-- Projekt Status and Switches -->
                            <div class="row align-items-center mt-3">
                                <div class="col-12">
                                    <label class="form-label">Projekt Status</label>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="activeToggle">
                                        <label class="form-check-label" for="activeToggle">Active</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="visibleToggle">
                                        <label class="form-check-label" for="visibleToggle">Visible</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Latitude and Longitude Input -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <label for="latLonInput" class="form-label">Latitude and Longitude</label>
                                    <input type="text" id="latLonInput" class="form-control" placeholder="Enter Latitude, Longitude">
                                </div>
                            </div>
                            <!-- Project Start Date and Save Button -->
                            <div class="row mt-3">
                                <!-- Project Start Date -->
                                <div class="col-md-6">
                                    <label for="projectStartDate">Project Start</label>
                                    <input type="date" id="projectStartDate" class="form-control">
                                </div>
                                <!-- Save Button -->
                                <div class="col-md-6 d-flex justify-content-md-start justify-content-center align-items-center">
                                    <button id="saveProjectBtn" type="button" class="btn btn-primary mt-2 mt-md-0">
                                        <i class="bi bi-floppy"></i> Add Project
                                    </button>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>



<style>
    .custom-modal-size {
        max-width: 80%;
        /* Adjust the width as per requirement */
        max-height: 80vh;
        max-width: 65vw;
    }

    .modal-dialog {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: calc(100vh - 60px);
        /* Adjusts to the viewport height minus any desired margin */
    }

    .modal-content {
        overflow-y: auto;
        /* Allows scrolling for overflowing content */
        max-height: 80vh;
        /* Matches the max-height of .custom-modal-size */
    }

    @media (max-width: 768px) {
        .custom-modal-size {
            max-width: 95%;
        }

        .modal-dialog {
            min-height: calc(100vh - 20px);
            /* Less margin on smaller screens */
        }
    }



    .project-icon {
        margin-right: 10px;
        border: 1px solid #ccc;
        padding: 5px;
        border-radius: 4px;
        background-color: #007bff82;
        color: #fff;
    }

    .prj_manager_group .list-group-item {
        border-color: unset;
        background: unset;
        color: unset;
        font-weight: unset;
        border-radius: 4px;
        padding: 5px;
        user-select: none;
        transition: background-color 0.2s ease-in-out;
    }

    .prj_manager_group .list-group-item.active {
        border-color: unset;
        background: #007bff1f;
        color: unset;
        font-weight: 600;
        border-radius: 4px;
        padding: 5px;
    }

    .prj_manager_group .list-group-item:hover {
        background: #ededed;
    }
</style>