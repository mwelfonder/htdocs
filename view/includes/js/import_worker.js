// Service Worker: import_worker.js
onmessage = function (e) {
    let { chunk, systemCustomers, systemCities } = e.data;
    let result = analyzeCustomerData(chunk, systemCustomers, systemCities);
    postMessage(result);
};

function analyzeCustomerData(processedRows, systemCustomers, systemCities) {
    let updatedCustomers = [];
    let newCustomers = [];
    let unchangedCustomers = [];
    let invalidCustomers = [];
    let newCitiesWithCount = [];
    let updatedCitiesWithNewCustomers = [];

    let customerCountPerCity = {};
    let customersByHomeID = {}; // Object to track customers by homeid
    let latestUpdates = {}; // Object to track the latest updates by homeid

    processedRows.forEach(row => {
        // Skip invalid customers
        if (!isValidCustomer(row)) {
            invalidCustomers.push(row);
            return;
        }

        // Track city and customer count
        if (customerCountPerCity[row.city]) {
            customerCountPerCity[row.city]++;
        } else {
            customerCountPerCity[row.city] = 1;
        }

        // Find existing customer
        let existingCustomer = systemCustomers.find(customer => customer.homeid === row.homeid);

        // Create comparisonRow for case-insensitive comparisons
        let comparisonRow = {};
        for (let key in row) {
            let sanitizedValue = sanitizeValue(row[key]);
            comparisonRow[key] = typeof sanitizedValue === 'string' ? sanitizedValue.toLowerCase() : sanitizedValue;
        }

        if (existingCustomer) {
            let changes = {};
            for (let key in comparisonRow) {
                if (key === 'system_status2' || key === 'system_status3') continue; // Skip these keys here as well
                let comparisonValue = comparisonRow[key];
                let existingValue = typeof existingCustomer[key] === 'string' ? existingCustomer[key].toLowerCase() : existingCustomer[key];
                existingValue = sanitizeValue(existingValue);

                if (comparisonValue !== existingValue && comparisonRow[key] !== null && comparisonRow[key] !== '') {
                    changes[key] = { changed: true, old: existingCustomer[key], new: row[key] };
                } else {
                    changes[key] = { changed: false };
                }
            }

            if (Object.values(changes).some(change => change.changed)) {
                latestUpdates[existingCustomer.homeid] = { id: existingCustomer.id, homeid: row.homeid, changes };
            } else {
                unchangedCustomers.push(existingCustomer);
            }
        } else {
            // Handling new customers with unique homeid
            if (!customersByHomeID[row.homeid]) {
                customersByHomeID[row.homeid] = row;
            } else {
                // Merge new customer data with existing, ensuring not to overwrite non-null with null
                for (let key in row) {
                    if (row[key] !== null && row[key] !== '') {
                        customersByHomeID[row.homeid][key] = row[key];
                    }
                }
            }

            // Update city counts
            if (!systemCities.some(systemCity => systemCity.city === row.city)) {
                updateCityCount(newCitiesWithCount, row);
            } else {
                updateCityCount(updatedCitiesWithNewCustomers, row);
            }
        } 
    });

    newCustomers = Object.values(customersByHomeID); // Convert the object back into an array

    // Convert the latestUpdates object back to an array for updatedCustomers
    updatedCustomers = Object.values(latestUpdates);

    // Calculate new customers in existing cities
    let newCustomersInCities = {};
    systemCities.forEach(city => {
        if (customerCountPerCity[city.city] && systemCustomers.filter(c => c.city === city.city).length < customerCountPerCity[city.city]) {
            newCustomersInCities[city.city] = customerCountPerCity[city.city] - systemCustomers.filter(c => c.city === city.city).length;
        }
    });

    return {
        updatedCustomers,
        newCustomers,
        unchangedCustomers,
        invalidCustomers,
        newCitiesWithCount,
        updatedCitiesWithNewCustomers
    };
}

// Make sure to include your helper functions like isValidCustomer, sanitizeValue, and updateCityCount as needed.



// Function to check if a customer is valid
function isValidCustomer(customer) {
    return customer.lastname && customer.street && customer.streetnumber && customer.city;
}

function sanitizeValue(value) {
    // Convert specific string representations of null to actual null
    if (value === 'null' || value === 'NULL' || value === '') return null;

    return value;
}

function updateCityCount(cityArray, customer) {
    let cityIndex = cityArray.findIndex(city => city.name === customer.city);
    if (cityIndex === -1) {
        cityArray.push({ name: customer.city, newCustomers: [customer], count: 1 });
    } else {
        cityArray[cityIndex].newCustomers.push(customer);
        cityArray[cityIndex].count++;
    }
}