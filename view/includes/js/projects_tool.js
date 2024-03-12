const modal = $('#projectToolsModal');

$(document).ready(function () {
    /**
     * Project Manager sidebar Tab Navigation logic
     */
    $('#projectTabs li').on('click', function () {
        var target = $(this).data('bs-target');
        // Remove 'active' class and update button classes for all items
        $('#projectTabs li').each(function () {
            $(this).removeClass('active');
            var button = $(this).find('button');
            button.removeClass('btn-primary').addClass('btn-outline-secondary');
            button.css('color', ''); // Reset color
        });
        // Add 'active' class and update button class for clicked item
        $(this).addClass('active');
        var activeButton = $(this).find('button');
        activeButton.removeClass('btn-outline-secondary').addClass('btn-primary');
        activeButton.css('color', '#fff'); // Set color to white
        // Update tab content
        $('.tab-content .tab-pane').removeClass('show active');
        $(target).addClass('show active');
    });

    // Call updateSaveButtonState on input change 
    $('#projectNameInput, #projectIdInput, #latLonInput, #projectStartDate, #modalCarrierSelect, #modalClientSelect').on('change keyup', function () {
        updateSaveButtonState();
    });

    // Initial state update
    updateSaveButtonState();

    $('#saveProjectBtn').click(function () {
        var formData = getFormData();
        console.log('formData to send', formData)

        $.ajax({
            type: 'POST',
            url: 'view/load/projectmanager_load.php',
            func: 'project_savenew',
            data: formData,
            success: function (response) {
                // Handle success (maybe show a success message)
                console.log(response);
            },
            error: function () {
                // Handle error
                console.log('Error sending data');
            }
        });
    });

});



/**
 * Show the modal for Project Manager and activate "New Project" tab
 * 
 */
$(document).on('click', '.tmpNewCity, .nav_projects', function () {
    console.log('Show modal');
    modal.modal('show');
    // Trigger click on the New Project tab
    $('#newProjectTab').trigger('click');
});


function validateForm() {
    var projectName = $('#projectNameInput').val().trim();
    var projectId = $('#projectIdInput').val().trim();
    var latLon = $('#latLonInput').val().trim();
    var projectStartDate = $('#projectStartDate').val().trim();
    var carrier = $('#modalCarrierSelect').val();
    var client = $('#modalClientSelect').val();

    var isValidDate = projectStartDate !== '' && !isNaN(new Date(projectStartDate).getTime());
    var isCarrierSelected = carrier !== '';
    var isClientSelected = client !== '';

    return projectName !== '' && projectId !== '' && latLon !== '' &&
        isValidDate && isCarrierSelected && isClientSelected;
}

function updateSaveButtonState() {
    if (validateForm()) {
        $('#saveProjectBtn').prop('disabled', false);
    } else {
        $('#saveProjectBtn').prop('disabled', true);
    }
}
function getFormData() {
    return {
        projectName: $('#projectNameInput').val().trim(),
        projectId: $('#projectIdInput').val().trim(),
        latLon: $('#latLonInput').val().trim(),
        projectStartDate: $('#projectStartDate').val().trim(),
        carrier: $('#modalCarrierSelect').val(),
        client: $('#modalClientSelect').val()
    };
}




populateDropdowns();
/**
 * Populates two dropdown menus in a modal with data fetched from a server.
 * @returns {Promise<void>} A promise that resolves when the dropdowns are populated.
 */
async function populateDropdowns() {
    try {
        // Fetch data for both dropdowns
        const clientData = await loadData('scan4_citylist', 'client', true);
        console.log('Client Data:', clientData);
        const carrierData = await loadData('scan4_citylist', 'carrier', true);

        // Function to update a dropdown with data
        const updateDropdown = (selector, data) => {
            const dropdown = $(selector).empty();
            dropdown.append($('<option>', { text: 'Select an option', value: '' }));
            data.forEach(item => {
                dropdown.append($('<option>', { text: item, value: item }));
            });
        };

        // Updating both dropdowns in the modal
        updateDropdown('#modalClientSelect', clientData);
        updateDropdown('#modalCarrierSelect', carrierData);
    } catch (error) {
        console.error('Error populating dropdowns:', error);
        console.log('Error Details:', error.message); // Logs the text description of the error
        console.log('Stack Trace:', error.stack); // Logs the stack trace
    }

}
/**
 * Fetches data from a server using the Fetch API.
 * 
 * @param {string} table - The name of the table to fetch data from.
 * @param {string} column - The name of the column to fetch data from.
 * @param {boolean} [unique=false] - Indicates whether to fetch unique values only. Defaults to false.
 * @returns {Promise<object>} - The data fetched from the server, returned as a JSON object.
 * @throws {Error} - If there is an HTTP error during the request or response.
 */
async function loadData(table, column, unique = false) {
    try {
        const url = 'view/load/functions_load.php';
        const params = new URLSearchParams({
            func: 'dynamicDataFetch',
            table: table,
            column: column,
            unique: unique
        });

        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params.toString()
        });

        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        return await response.json();
    } catch (error) {
        console.error('Load data error:', error);
    }
}
