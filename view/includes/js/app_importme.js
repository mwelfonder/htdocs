


var fileResponse = null;

var uploadStartTime;
var analyzeCustomerDataResult;


$(document).ready(function () {
    $('#uploadBtn').click(function (e) {
        e.preventDefault();

        var formData = new FormData();
        var fileInput = $('#fileInput')[0];
        formData.append('file', fileInput.files[0]);

        // Show the progress bar
        $('#progressBarContainer').show();
        $('#uploadProgressBar').css('width', '0%').attr('aria-valuenow', 0);

        $.ajax({
            url: "view/load/importme_load.php",
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            xhr: function () {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function (evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = Math.round((evt.loaded / evt.total) * 100);
                        $('#uploadProgressBar').css('width', percentComplete + '%').attr('aria-valuenow', percentComplete);

                        if (percentComplete === 100) {
                            uploadStartTime = new Date(); // Set start time
                        }
                    }
                }, false);
                return xhr;
            },
            success: function (response) {

                console.log('response', response);

                var displayMessage = '';

                if (response.success) {
                    fileResponse = response;

                    const systemCustomers = response.system.customers;
                    const systemCities = response.system.citys;
                    const processedRows = response.fileData.processedRows;
                    const firstCarrier = processedRows[0].carrier;

                    console.log('processedRows', processedRows);
                    let count = Object.keys(response.fileDetails.doubleHomeIDs).length;
                    console.log('Double homeids found: ' + count);


                    // Build and display the table with the first ten rows
                    const firstTenRows = response.fileData.rows.slice(0, 10);
                    var table = Table_buildRaw(response.fileDetails.headers, firstTenRows, response.fileDetails.identifiedColumns);
                    $('#dataTable_rawData').html(table);

                    const firstTenRowsProcessedData = processedRows.slice(0, 10);
                    var table = Table_buildPorcessed(firstTenRowsProcessedData);


                    console.log('AnalyzeCustomerData now');
                    const startTime = performance.now(); // Start the timer

                    const numberOfWorkers = 5;
                    const chunkSize = Math.ceil(processedRows.length / numberOfWorkers);
                    const chunks = [];

                    for (let i = 0; i < processedRows.length; i += chunkSize) {
                        chunks.push(processedRows.slice(i, i + chunkSize));
                    }

                    const workers = [];
                    const results = [];

                    for (let i = 0; i < numberOfWorkers; i++) {
                        workers[i] = new Worker('/view/includes/js/import_worker.js?' + new Date().getTime());

                        workers[i].onmessage = function (event) {
                            results.push(event.data);
                            if (results.length === numberOfWorkers) {
                                // All workers have finished
                                const analyzeCustomerDataResult = combineResults(results);
                                console.log('combinedResult', analyzeCustomerDataResult)

                                const jsonString = JSON.stringify(analyzeCustomerDataResult);
                                const sizeInBytes = new Blob([jsonString]).size;
                                const sizeInMegabytes = sizeInBytes / (1024 * 1024);
                                console.log('Size of data in MB:', sizeInMegabytes.toFixed(2), 'MB');

                                const endTime = performance.now();  // End the timer
                                console.log(`analyzeCustomerData took ${((endTime - startTime) / 1000).toFixed(2)} seconds to execute.`);
                                console.log('analyzeCustomerDataResult', analyzeCustomerDataResult);

                                // Process analyzeCustomerDataResult here
                                if (analyzeCustomerDataResult.newCustomers.length > 0) {
                                    addMenuItem('newCustomers', analyzeCustomerDataResult.newCustomers);
                                }

                                if (analyzeCustomerDataResult.updatedCustomers.length > 0) {
                                    addMenuItem('updatedCustomers', analyzeCustomerDataResult.updatedCustomers);
                                }

                                // sendDataToServerForExcelExport(analyzeCustomerDataResult.updatedCustomers)

                                // --------------------------------------------------------------------------------
                                // city update part
                                $('#city-updates').empty(); // Clear any existing content

                                let listGroup = $('<ul>', {
                                    'class': 'list-group',
                                    'css': {
                                        'overflow-y': 'scroll',
                                        'max-height': '70vh',
                                        'padding': '5px 20px'
                                    }
                                });

                                systemCities.forEach(cityObj => {
                                    let listItem = $('<li>', {
                                        'class': 'list-group-item d-flex justify-content-between align-items-center',
                                        text: cityObj.city
                                    });
                                    let badge = $('<span>', {
                                        'class': 'badge bg-primary rounded-pill',
                                        text: '0'
                                    });

                                    listItem.append(badge);
                                    listGroup.append(listItem);
                                });
                                $('#city-updates').append(listGroup);

                                analyzeCustomerDataResult.updatedCitiesWithNewCustomers.forEach(updatedCity => {
                                    let listItem = $('#city-updates li').filter(function () {
                                        return $(this).text().trim() === updatedCity.name;
                                    });
                                    if (listItem.length) {
                                        listItem.find('.badge').text(updatedCity.count);
                                    } else {
                                        // If the city is not in the initial list, add it
                                        let newListItem = $('<li>', {
                                            'class': 'list-group-item d-flex justify-content-between align-items-center',
                                            text: updatedCity.name
                                        });

                                        let badge = $('<span>', {
                                            'class': 'badge bg-primary rounded-pill',
                                            text: updatedCity.count
                                        });

                                        newListItem.append(badge);
                                        listGroup.append(newListItem);
                                    }
                                });

                                let $listGroup = $('#city-updates .list-group'); // Directly reference the existing list group
                                let items = $listGroup.children('.list-group-item').get();

                                items.sort(function (a, b) {
                                    let countA = parseInt($(a).find('.badge').text(), 10);
                                    let countB = parseInt($(b).find('.badge').text(), 10);
                                    return countB - countA; // This will sort in descending order
                                });

                                // Re-append the sorted items
                                $.each(items, function (idx, itm) { $listGroup.append(itm); });

                                let updateCount;
                                updateCount = analyzeCustomerDataResult.updatedCitiesWithNewCustomers.length;
                                // Update the text of the button
                                $('#city-updates-tab').text('Existing City New Customers (' + updateCount + ')');


                                $('#new-cities').empty(); // Clear existing content

                                let newListGroup = $('<ul>', {
                                    'class': 'list-group',
                                    'css': {
                                        'overflow-y': 'scroll',
                                        'max-height': '70vh'
                                    }
                                });

                                analyzeCustomerDataResult.newCitiesWithCount.forEach(cityObj => {
                                    let listItem = $('<li>', {
                                        'class': 'list-group-item d-flex justify-content-between align-items-center tmpNewCity',
                                        text: cityObj.name
                                    });
                                    let badge = $('<span>', {
                                        'class': 'badge bg-primary rounded-pill',
                                        text: cityObj.count
                                    });

                                    listItem.append(badge);
                                    newListGroup.append(listItem);
                                });

                                $('#new-cities').append(newListGroup);

                                updateCount = analyzeCustomerDataResult.newCitiesWithCount.length;
                                // Update the text of the button
                                $('#new-cities-tab').text('New Cities (' + updateCount + ')');




                                $('#sidebarItem_city').show();

                                if (analyzeCustomerDataResult.newCustomers.length > 0 || analyzeCustomerDataResult.updatedCustomers.length > 0) {
                                    // Creating menu item
                                    let menuItem = $('<div>', {
                                        'class': 'menu-item',
                                        'data-target': `#content-processdata`,
                                        html: `<i class="bi bi-gear"></i> Proccess Data`
                                    });

                                    // Appending the menu item to the sidebar
                                    $('#fileMenuSidebar').append(menuItem);
                                    // Creating the content section and appending the table
                                    let contentSection = $('<div>', {
                                        'id': `content-processdata`,
                                        'class': 'content-section',
                                        css: { display: 'none' }
                                    }).append('');

                                    // Appending the content section to the content wrapper
                                    $('#fileMenuContentWrapper').append(contentSection);
                                    // Click event for the menu item
                                    menuItem.click(function () {
                                        $.confirm({
                                            backgroundDismiss: true,
                                            theme: "dark",
                                            type: "blue",
                                            title: 'Daten verarbeiten',
                                            content: 'Sicher das du die Daten jetzt verarbeiten möchtest?',
                                            buttons: {
                                                ja: function () {
                                                    console.log('confirmed', 'clicked yes');
                                                    updateDatabaseWithCustomerData(analyzeCustomerDataResult);
                                                },
                                                nein: function () {
                                                    console.log('cancelled');
                                                    // Actions for cancellation can go here
                                                }
                                            }
                                        });
                                    });
                                } else {
                                    $.confirm({
                                        backgroundDismiss: true,
                                        theme: "dark",
                                        type: "green",
                                        title: 'Daten sind aktuell',
                                        content: 'Es wurden keine Änderungen gefunden, Daten sind aktuell.',
                                        buttons: {
                                            ok: function () {

                                            },
                                        }
                                    });
                                }
                            }
                        };

                        workers[i].postMessage({ chunk: chunks[i], systemCustomers, systemCities });
                    }

                    function combineResults(workerResults) {
                        let combined = {
                            updatedCustomers: [],
                            newCustomers: [],
                            unchangedCustomers: [],
                            invalidCustomers: [],
                            newCitiesWithCount: [],
                            updatedCitiesWithNewCustomers: []
                        };

                        workerResults.forEach(result => {
                            combined.updatedCustomers.push(...result.updatedCustomers);
                            combined.newCustomers.push(...result.newCustomers);
                            combined.unchangedCustomers.push(...result.unchangedCustomers);
                            combined.invalidCustomers.push(...result.invalidCustomers);

                            // Aggregate new cities with count
                            result.newCitiesWithCount.forEach(newCity => {
                                let existingCity = combined.newCitiesWithCount.find(city => city.name === newCity.name);
                                if (existingCity) {
                                    existingCity.count += newCity.count;
                                    existingCity.newCustomers.push(...newCity.newCustomers);
                                } else {
                                    combined.newCitiesWithCount.push(newCity);
                                }
                            });

                            // Aggregate updated cities with new customers
                            result.updatedCitiesWithNewCustomers.forEach(updatedCity => {
                                let existingCity = combined.updatedCitiesWithNewCustomers.find(city => city.name === updatedCity.name);
                                if (existingCity) {
                                    existingCity.count += updatedCity.count;
                                    existingCity.newCustomers.push(...updatedCity.newCustomers);
                                } else {
                                    combined.updatedCitiesWithNewCustomers.push(updatedCity);
                                }
                            });
                        });

                        return combined;
                    }




                    //createMenuItem('Cities', uniqueCities.length, '<i class="ri-building-line"></i>');



                    $('#dataTable_processed').html(table);

                }
                // Hide the progress bar
                $('#progressBarContainer').hide();
                if (uploadStartTime) {
                    var uploadEndTime = new Date();
                    var duration = (uploadEndTime - uploadStartTime) / 1000; // Duration in seconds
                    console.log('Time taken: ' + duration + ' seconds');
                    $('#third-col-placeholder').text(duration)
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                var errorDetails = '<span style="color: red;">An error occurred while uploading the file.</span><br>';

                // Provide additional error details
                errorDetails += '<strong>Error Status:</strong> ' + textStatus + '<br>';
                errorDetails += '<strong>Error Thrown:</strong> ' + errorThrown + '<br>';

                // If the server responded with a status code outside of the 200 range, include the response text.
                if (jqXHR.status && jqXHR.status !== 200) {
                    errorDetails += '<strong>Server Response:</strong> ' + jqXHR.responseText + '<br>';
                    errorDetails += '<strong>Status Code:</strong> ' + jqXHR.status + '<br>';
                }

                $('#response').html(errorDetails);

                // Hide the progress bar
                $('#progressBarContainer').hide();
            }

        });
    });

    $('#fileres_citiesnew').hover(function () {
        // Create the tooltip container with Bootstrap classes
        var $tooltip = $('<div>', {
            id: 'citiesTooltip',
            class: 'shadow p-3 mb-5 bg-body rounded', // Bootstrap classes for styling
            css: {
                display: 'none',
                position: 'absolute',
                background: '#fff'
            }
        });

        const cityData = fileResponse.stats.city_new;
        var cityNamesHtml = '<ul class="list-unstyled">'; // Unstyled list

        Object.keys(cityData).forEach(city => {
            var count = cityData[city].count;
            cityNamesHtml += '<li>' + city + ' <span class="badge bg-secondary" style="color: #fff;">' + count + '</span></li>';
        });
        cityNamesHtml += '</ul>';
        $tooltip.html(cityNamesHtml);

        // Append to body and position
        $('body').append($tooltip);
        var position = $(this).offset();
        $tooltip.css({
            top: position.top + $(this).outerHeight(),
            left: position.left
        }).fadeIn(100);
    }, function () {
        // Remove the tooltip on hover leave
        $('#citiesTooltip').fadeOut(100, function () { $(this).remove(); });
    });






    // General click listener for menu items
    $('#fileMenuSidebar').on('click', '.menu-item', function () {
        console.log('sidebar click registered')
        $('.content-section').hide();
        $($(this).data('target')).show();
    });

    $(document).on('click', '#csvimport', function () {
        sendTest();
    });


});


function Table_buildRaw(headers, rows, identifiedColumns) {
    var table = '<div class="table-responsive" style="overflow: auto;"><table class="table table-smaller table-bordered table-hover"><thead class="thead-dark">';

    // First row for identified headers
    table += '<tr class="headerMapping_displaytext">';
    headers.forEach(function (header, index) {
        var identified = false;
        for (var key in identifiedColumns) {
            if (identifiedColumns.hasOwnProperty(key) && identifiedColumns[key].position === index) {
                table += '<th scope="col" class="headerHit">' + key + '</th>'; // Use the key (mapped name)
                identified = true;
                break;
            }
        }
        if (!identified) {
            table += '<th scope="col" class="headernoHit"></th>'; // Add empty header if not identified
        }
    });
    table += '</tr>';

    // Second row for normal headers
    const extraBtn = '<span class="headerXtras"><i class="bi bi-caret-right-fill"></i></span>';
    table += '<tr>';
    headers.forEach(function (header) {
        table += '<th scope="col">' + header.trim() + extraBtn + '</th>'; // Add the normal header
    });
    table += '</tr></thead><tbody>';

    // Build the table body
    rows.forEach(function (row) {
        table += '<tr>';
        row.forEach(function (cell) {
            table += '<td>' + cell.trim() + '</td>'; // Trim each cell value
        });
        table += '</tr>';
    });

    // Close the table and return
    table += '</tbody></table></div>';
    return table;
}


function Table_buildPorcessed(rows) {
    var table = '<div class="table-responsive" style="overflow: auto;"><table class="table table-smaller table-bordered table-hover"><thead class="thead-dark">';

    // Check if there are any rows
    if (rows.length === 0) {
        return '<p>No data available</p>';
    }

    // Generate headers from the first row's keys
    table += '<tr>';
    Object.keys(rows[0]).forEach(function (key) {
        table += '<th scope="col">' + key.trim() + '</th>';
    });
    table += '</tr></thead><tbody>';

    // Build the table body
    rows.forEach(function (row) {
        table += '<tr>';
        Object.keys(row).forEach(function (key) {
            table += '<td>' + (row[key] ? row[key].toString().trim() : '') + '</td>';
        });
        table += '</tr>';
    });

    // Close the table and return
    table += '</tbody></table></div>';
    return table;
}

function addMenuItem(type, customers) {
    let menuDisplayName;
    if (type === 'newCustomers') {
        menuDisplayName = 'New';
    } else if (type === 'updatedCustomers') {
        menuDisplayName = 'Updates';
    }
    // Creating the menu item
    let menuItem = $('<div>', {
        'class': 'menu-item',
        'data-target': `#content-${type}`,
        html: `<i class="bi bi-person"></i> ${menuDisplayName} (${customers.length})`
    });

    // Appending the menu item to the sidebar
    $('#fileMenuSidebar').append(menuItem);

    // Creating the table
    let table = $('<table>', {
        'class': 'display',
        'id': `table-${type}`
    });

    // Variables for columns and headers
    let columns;
    let headers = [];

    // Determining columns and headers based on customer type
    if (type === 'newCustomers' && customers.length > 0) {
        columns = Object.keys(customers[0]).map(key => ({
            title: key.charAt(0).toUpperCase() + key.slice(1),
            data: key
        }));
        headers = Object.keys(customers[0]);
    } else if (type === 'updatedCustomers' && customers.length > 0) {
        // Add 'homeid' as the first column
        columns = [{
            title: 'Homeid',
            data: 'homeid'
        }];

        // Add columns for each key in the 'changes' object
        let changeKeys = Object.keys(customers[0].changes);
        changeKeys.forEach(key => {
            columns.push({
                title: key.charAt(0).toUpperCase() + key.slice(1),
                data: `changes.${key}`,
                render: function (data) {
                    if (data && data.changed) {
                        let oldValue = data.old === undefined ? 'None' : data.old;
                        let newValue = data.new === undefined ? 'None' : data.new;
                        return `${oldValue} => ${newValue}`;
                    }
                    return ''; // Empty string for unchanged data
                }
            });
        });
    }

    // Creating thead element with 'thead-dark' class
    let thead = $('<thead>').addClass('thead-dark');
    let tr = $('<tr>');
    headers.forEach(key => {
        tr.append($('<th>').text(key.charAt(0).toUpperCase() + key.slice(1)));
    });
    thead.append(tr);
    table.append(thead);

    // Wrapping the table in a 'table-responsive' div
    let responsiveTableDiv = $('<div>', {
        'class': 'table-responsive'
    }).append(table);

    // Creating the content section and appending the table
    let contentSection = $('<div>', {
        'id': `content-${type}`,
        'class': 'content-section',
        css: { display: 'none' }
    }).append(responsiveTableDiv);

    // Appending the content section to the content wrapper
    $('#fileMenuContentWrapper').append(contentSection);

    // Initializing the DataTable with defined columns
    $(`#table-${type}`).DataTable({
        data: customers,
        columns: columns,
        responsive: true,  // Keep the table responsive
        dom: 'Bfrtip',     // Set buttons' position
        buttons: [
            {
                extend: 'excelHtml5',
                text: 'Export to Excel',
                titleAttr: 'Excel',
                className: 'btn btn-success'
            }
        ]
    });

    // Click event for the menu item
    menuItem.click(function () {
        $('.content-section').hide();
        $($(this).data('target')).show();
    });
}


// Function to check if a customer is valid
function isValidCustomer(customer) {
    return customer.lastname && customer.street && customer.streetnumber && customer.city;
}


function sanitizeValue(value) {
    // Convert specific string representations of null to actual null
    if (value === 'null' || value === 'NULL' || value === '') return null;

    return value;
}


// remove the mapping div
$(document).on('click', function (e) {
    // Check if the clicked area is not the .headerMappingWrapper or a descendant of .headerMappingWrapper
    if (!$(e.target).closest('.headerMappingWrapper').length) {
        // Remove the .headerMappingWrapper if it exists
        $('.headerMappingWrapper').remove();
    }
});


// create the mapping div
$(document).on('click', '.headerXtras', function (e) {
    e.stopPropagation(); // Prevents the document click event from being immediately triggered

    console.log('globalfileResponse', fileResponse)
    // Remove any existing divs created by previous clicks
    $('.headerMappingWrapper').remove();

    // Create the outer div
    var $wrapper = $('<div/>', {
        'class': 'headerMappingWrapper shadow p-3 mb-5 bg-body rounded',
        'id': 'headerMappingWrapper',
    });

    // Create a container div for the two inner divs
    var $containerDiv = $('<div/>', {
        'class': 'containerDiv',
        'css': {
            'display': 'flex'
        }
    });

    // Create the first inner div
    var $firstInnerDiv = $('<div/>', {
        'class': 'headerMappingInner',
        'id': 'headerMappingLeft',
    });

    // Create the second inner div
    var $secondInnerDiv = $('<div/>', {
        'class': 'headerMappingInner',
        'id': 'headerMappingRight',
    });

    // Append the two inner divs to the container div
    $containerDiv.append($firstInnerDiv, $secondInnerDiv);

    // Append the container div to the outer div
    $wrapper.append($containerDiv);

    // Calculate position
    var posX = e.pageX;
    var posY = e.pageY;

    // Set CSS for the outer div
    $wrapper.css({
        'position': 'absolute',
        'top': posY + 'px',
        'left': posX + 'px',
    });

    // Append the outer div to the body
    $('body').append($wrapper);

    // Use a timeout to allow the browser to render the $wrapper
    setTimeout(function () {
        var wrapperRightEdge = $wrapper.offset().left + $wrapper.outerWidth();
        var windowWidth = $(window).width();

        if (wrapperRightEdge > windowWidth) {
            console.log('Wrapper is outside the screen, adjust it');
            var excessWidth = wrapperRightEdge - windowWidth;
            $wrapper.css('left', (posX - excessWidth) + 'px');
        } else {
            console.log('Wrapper is inside the screen');
        }
    }, 0);

    // Determine which header was clicked
    var clickedHeader = $(this).closest('th');
    var clickedHeaderIndex = clickedHeader.index();
    var clickedHeaderText = clickedHeader.text().trim();
    $wrapper.data('clickedHeaderText', clickedHeaderText);
    $wrapper.data('clickedHeaderIndex', clickedHeaderIndex);

    clickedHeaderText = clickedHeaderText.toLowerCase();


    // Populate the left div with identified headers
    var identifiedHeadersHtml = '';
    var identifiedHeadersSet = new Set(); // Define the set to store identified headers

    for (var key in fileResponse.fileData.metadata.identifiedColumns) {
        var matchedHeader = fileResponse.fileData.metadata.identifiedColumns[key].match.toLowerCase(); // Convert to lowercase
        identifiedHeadersHtml += '<div ' + ((matchedHeader === clickedHeaderText) ? 'class="mappedHeader highlight"' : 'class="mappedHeader"') + '>' + key + '</div>';
        identifiedHeadersSet.add(key); // Add to the set
    }
    $('#headerMappingLeft').html(identifiedHeadersHtml);

    // Populate the right div with mapping headers, filtering out headers that are in #headerMappingLeft
    var mappingHeadersHtml = '';
    var uniqueHeadersSet = new Set(); // Create a Set to store unique headers
    for (var key in fileResponse.fileData.metadata.mapping) {
        var header = fileResponse.fileData.metadata.mapping[key];
        // Check if the header is not in the set of identified headers and is not already in the uniqueHeadersSet
        if (!identifiedHeadersSet.has(header) && !uniqueHeadersSet.has(header)) {
            uniqueHeadersSet.add(header); // Add the header to the Set to mark it as seen
            mappingHeadersHtml += '<div class="mappedHeader toSelect">' + header + '</div>';
        }
    }
    $('#headerMappingRight').html(mappingHeadersHtml);

    console.log("$wrapper: ", $wrapper);

});


$(document).on('click', '.mappedHeader.toSelect', function (e) {
    var clickedHeaderText = $('.headerMappingWrapper').data('clickedHeaderText')
    var clickedHeaderIndex = $('.headerMappingWrapper').data('clickedHeaderIndex')

    var selection = $(this);
    var selectionText = selection.text();

    $.confirm({
        backgroundDismiss: true,
        theme: "dark",
        type: "red",
        title: 'Datenzuweisung ändern',
        content: 'Sicher das du</br><span style="white-space: nowrap; font-weight: 700; font-style: italic; font-size: 16px;color: #92b8ff;">' + clickedHeaderText +
            '</span></br>zum Feld</br><span style="white-space: nowrap; font-weight: 700; font-style: italic; font-size: 16px;color: #92b8ff;">' + selectionText + '</span> </br>zuweisen möchtest?',
        buttons: {
            ja: function () {
                console.log('confirmed', selectionText);
                $.ajax({
                    url: "view/load/importme_load.php",
                    type: 'POST',
                    data: {
                        action: 'safe_newMappingHeader',
                        header: selectionText,
                        mapped: clickedHeaderText,
                    },
                    dataType: 'json',
                    success: response => {
                        console.log('safe_newMappingHeader', response)
                        fileResponse.fileData.metadata.identifiedColumns[selectionText] = { position: clickedHeaderIndex, match: clickedHeaderText };
                        console.log('fileResponse', fileResponse)
                        $('.headerMapping_displaytext th').eq(clickedHeaderIndex).text(selectionText);
                        console.log('set ' + selectionText + ' on index ' + clickedHeaderIndex)

                    },
                    error: err => {

                    }
                });
            },
            nein: function () {
                console.log('cancelled');
                // Actions for cancellation can go here
            }
        }
    });
});


async function updateDatabaseWithCustomerData(customerData, chunkSize = 300) {
    function sendChunk(customersChunk, isNewCustomer) {
        const data = {
            action: 'safe_customerData',
            newCustomers: isNewCustomer ? JSON.stringify(customersChunk) : '',
            updatedCustomers: isNewCustomer ? '' : JSON.stringify(customersChunk)
        };

        console.log('Sending chunk', data);

        return $.ajax({
            url: "view/load/importme_load.php",
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function (response) {
                // Parse and handle the response
                if (typeof response === 'string') {
                    try {
                        response = JSON.parse(response);
                    } catch (e) {
                        console.error('Failed to parse response as JSON', response);
                        return;
                    }
                }

                if (response.success) {
                    console.log('Chunk updated successfully!', response.message);
                } else {
                    console.error('Chunk update failed', response.message, 'Errors:', response.errors);
                }
            },
            error: function (xhr, textStatus, errorThrown) {
                console.error('Failed to parse JSON:', xhr.responseText);
                console.error('AJAX error:', textStatus, errorThrown);
            }
        });
    }

    async function processChunks(chunks, isNewCustomer) {
        for (const chunk of chunks) {
            await sendChunk(chunk, isNewCustomer)
                .then(result => {
                    console.log('Processed chunk:', result);
                })
                .catch(error => {
                    console.error('Error in processing chunk:', error);
                });
        }
    }

    const newCustomersChunks = chunkArray(customerData.newCustomers, chunkSize);
    const updatedCustomersChunks = chunkArray(customerData.updatedCustomers, chunkSize);

    await processChunks(newCustomersChunks, true);
    console.log('All new customer chunks processed');

    await processChunks(updatedCustomersChunks, false);
    console.log('All updated customer chunks processed');
}


// Helper function to chunk the array
function chunkArray(array, size) {
    let result = [];
    for (let i = 0; i < array.length; i += size) {
        let chunk = array.slice(i, i + size);
        result.push(chunk);
    }
    return result;
}


function createMenuItem(menuItemName, uniqueItemsCount, iconClass) {
    // Create and append the menu item
    let menuItem = $('<div>', {
        'class': 'menu-item',
        'data-target': `#content-${menuItemName.toLowerCase()}`,
        html: `${iconClass} ${menuItemName} (${uniqueItemsCount})`
    });
    $('#fileMenuSidebar').append(menuItem);

    // Create and append the content section
    let contentSection = $('<div>', {
        'id': `content-${menuItemName.toLowerCase()}`,
        'class': 'content-section',
        css: { display: 'none' }
    });
    $('#fileMenuContentWrapper').append(contentSection);
}

// General click listener for menu items
$('#fileMenuContentWrapper').on('click', '.menu-item', function () {
    $('.content-section').hide();
    $($(this).data('target')).show();
});


function sendDataToServerForExcelExport(customers) {
    console.log('sendDataToServerForExcelExport', customers);
    var formData = new FormData();
    formData.append('action', 'safe_csvdata');

    // Correctly stringify the customers array
    formData.append('customers', JSON.stringify(customers));

    $.ajax({
        url: "view/load/importme_load.php",
        type: 'POST',
        processData: false, // Important: don't process data
        contentType: false, // Important: don't set contentType
        data: formData,
        success: function (response) {
            console.log('Response from server:', response);
        },
        error: function (xhr, status, error) {
            console.error('Failed to send data:', error);
        }
    });
}


function sendTest() {
    console.log('file create test');
    var formData = new FormData();
    formData.append('action', 'safe_test');


    $.ajax({
        url: "view/load/importme_load.php",
        type: 'POST',
        processData: false, // Important: don't process data
        contentType: false, // Important: don't set contentType
        data: formData,
        success: function (response) {
            console.log('Response from server:', response);
        },
        error: function (xhr, status, error) {
            console.error('Failed to send data:', error);
        }
    });
}
