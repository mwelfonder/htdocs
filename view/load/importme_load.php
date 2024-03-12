<?php
/*
Wenn Status Tiefbau aber Status Hausbegehung nicht Gereed OPEN
Wenn Status Hausbegehung OPEN, selbst wenn Status Hausbegehung Gereed
Wenn Status nicht Hausbegehung, Tiefbau dann DONE
Wenn Status Tiefbau und Status Hausbegehung Gereed dann DONE
Wenn GrundNA = R1,R1.1,R1.2,R1.3,R2,R3,R4,R6,R7,R9,R10,R10.1,R10.2,R10.4,R12,R18,R18.1,R18.2,R20,R22,R23,R24 Dann Stopped.
(In der Timeline zu jedem Grund ein Eintrag erstellen. - Gründe angehangen.)
*/
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}


$logged_in = $user->data();
$currentuser = $logged_in->username;



include "../../view/includes/functions.php";

header('Content-Type: text/html; charset=utf-8');




$db = dbconnect();


function isUtf8($text)
{
    return mb_detect_encoding($text, 'UTF-8', true);
}
// Function to load known header mappings from the database
function load_headerMapping($db)
{
    $mappings = [];
    $query = "SELECT header_name, mapped_name FROM import_headermapping ";
    $result = $db->query($query);
    while ($row = $result->fetch_assoc()) {
        $mappings[strtolower($row['mapped_name'])] = strtolower($row['header_name']);
    }
    return $mappings;
}


function load_customers($db)
{
    $customers = [];
    $query = "SELECT * FROM scan4_homes";

    $result = $db->query($query);

    // Check if the query was successful
    if ($result === false) {
        // Query failed, handle the error
        error_log("MySQL error: " . mysqli_error($db));
        return []; // or handle the error as appropriate
    }

    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }

    return $customers;
}


function load_cities($db)
{
    $citylist = [];
    $query = "SELECT * FROM scan4_citylist ";
    $result = $db->query($query);
    while ($row = $result->fetch_assoc()) {
        $citylist[] = $row;
    }
    return $citylist;
}


function clean_phone($rawNumber)
{
    // Normalize spaces to ensure uniformity
    $normalizedNumber = preg_replace('/\s+/', ' ', $rawNumber);

    // Determine the splitting logic based on the presence and pattern of '0049'
    if (substr_count($normalizedNumber, '0049') >= 2) {
        // When '0049' is present two or more times, split using a specific pattern for '0049'
        $numbers = preg_split('/(?<=\s|^)0049\s/', $normalizedNumber, -1, PREG_SPLIT_NO_EMPTY);
    } else {
        // Use the original splitting logic for other cases
        $numbers = preg_split('/(?<!\/)\/\//', $normalizedNumber);
    }

    $standardizedNumbers = [];

    foreach ($numbers as $number) {
        $sanitizedNumber = preg_replace('/[^\d]/', '', $number);

        // Handling for numbers starting with '49' or '0049', and standardizing the prefix
        if (substr($sanitizedNumber, 0, 2) === '49') {
            $sanitizedNumber = '0' . substr($sanitizedNumber, 2);
        } elseif (substr($sanitizedNumber, 0, 4) === '0049') {
            $sanitizedNumber = '0' . substr($sanitizedNumber, 4);
        } else {
            // Ensure the number starts with '0' if not already
            if (isset($sanitizedNumber[0]) && $sanitizedNumber[0] !== '0') {
                $sanitizedNumber = '0' . $sanitizedNumber;
            }
        }

        // Check if the sanitized number is just '0', if so, set it to null
        if ($sanitizedNumber === '0') {
            $sanitizedNumber = null;
        }

        // Add the sanitized and standardized number to the result array
        if ($sanitizedNumber !== null) {
            $standardizedNumbers[] = $sanitizedNumber;
        }
    }

    return $standardizedNumbers;
}



function clean_name($fullName, $skipSplitting = false)
{
    // Remove titles and unwanted characters
    $fullName = preg_replace('/\bsir\b|\bmadam\b|\bmr\b|\bmrs\b/i', '', $fullName);
    $cleanName = trim(preg_replace('/[^\p{L}\s\.\&-]/u', '', $fullName));
    $cleanName = trim($cleanName, " .");

    // Skip splitting if indicated
    if ($skipSplitting) {
        return ['fullname' => $cleanName];
    }

    // Split the name into parts
    $nameParts = preg_split('/\s+/', $cleanName, 2);
    if (count($nameParts) > 1) {
        return ['firstname' => $nameParts[0], 'lastname' => $nameParts[1]];
    } else {
        return ['firstname' => $nameParts[0], 'lastname' => ''];
    }
}


function clean_plz($plz)
{
    // Use a regular expression to find a sequence of exactly five digits
    if (preg_match('/\d{5}/', $plz, $matches)) {
        return $matches[0]; // Return the sequence of five digits
    }

    return ''; // Return an empty string if a valid PLZ is not found
}


function clean_date($dateString)
{
    if (empty($dateString)) {
        return null;
    }

    try {
        // Handle mixed format like '3.17.2022 11:00:00 pm'
        if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})/', $dateString, $matches)) {
            // Rearrange the date parts to MM.DD.YYYY if the first part is less than or equal to 12 (assumed to be month)
            if ((int)$matches[1] <= 12) {
                $dateString = $matches[2] . '.' . $matches[1] . '.' . $matches[3];
            }
        }

        // Attempt to create a DateTime object from the possibly rearranged string
        $date = new DateTime($dateString);

        return $date->format('Y-m-d');
    } catch (Exception $e) {
        return null;
    }
}
function split_full_address($fullAddress)
{
    // Pattern to match the street name, street number, and any additional suffix
    $pattern = '/^([\p{L}\s]+)\s(\d+)([a-zA-Z]?)\,/';

    // Attempt to match the pattern against the full address
    if (preg_match($pattern, $fullAddress, $matches)) {
        // Extract matches into named variables for clarity
        $street = $matches[1];
        $streetNumber = $matches[2];
        $streetNumberAdd = $matches[3] ?? ''; // May be empty if no suffix

        return [
            'street' => $street,
            'streetnumber' => $streetNumber,
            'streetnumberadd' => $streetNumberAdd,
        ];
    }

    // Return null or some default values if the pattern does not match
    return [
        'street' => null,
        'streetnumber' => null,
        'streetnumberadd' => null,
    ];
}

function ident_homeid($homeId)
{
    // Pattern for DGF: Usually contains letters followed by digits and underscores
    if (preg_match('/(\d{5}[A-Z]{3}|\d{7}[A-Z])(_\d+)?(_\d+)?(_[A-Z])?/', $homeId)) {
        return 'DGF';
    }
    // Pattern for UGG: Typically starts with letters followed by a long string of digits
    elseif (preg_match('/[A-Z]{3}\d{12}/', $homeId)) {
        return 'UGG';
    }
    // Pattern for GlasfaserPlus: Purely numeric, 7 to 8 digits long
    elseif (preg_match('/^\d{7,8}$/', $homeId)) {
        return 'GlasfaserPlus';
    }

    return 'Unknown'; // Default return if no pattern matches
}



/**
 * Determines the status of a system based on the input parameters.
 *
 * @param string $system_status1 The first system status.
 * @param string $fileCarrier The file carrier.
 * @param string|null $system_status2 The second system status.
 * @param string|null $system_status3 The third system status.
 * @return string The status of the system based on the input parameters.
 */
function ident_systemstatus(string $system_status1, string $fileCarrier, ?string $system_status2 = null, ?string $system_status3 = null): string
{
    $status = strtoupper($system_status1);

    // New General Logic
    switch ($status) {
        case 'AWAITING NE4 DESIGN APPROVAL':
        case 'COMPANY ASSIGNMENT':
        case 'SCHEDULING SURVEY':
        case 'WAITING FOR DP INFO':
        case 'WAITING FOR LLD':
            return 'OPEN';
        case 'CANCEL WORK':
            return 'STOPPED';
        case 'SCHEDULING WORK':
        case 'SURVEY DONE':
            return 'DONE';
        case 'SURVEY SCHEDULED':
            return 'PLANNED';
    }

    // UGG Logic
    if ($fileCarrier === 'UGG') {
        if (empty($system_status1) && in_array(strtoupper($system_status2), ['BLOCKED BY MDU', 'CANCELLED'])) {
            return 'STOPPED';
        }

        return strtoupper($system_status1);
    }

    // DGF Logic
    if ($fileCarrier === 'DGF') {
        $status1 = strtoupper(str_replace(['ß', 'ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü'], ['SS', 'AE', 'OE', 'UE', 'AE', 'OE', 'UE'], trim($system_status1)));
        $status2 = strtoupper(trim($system_status2));
        $status3 = strtoupper(trim($system_status3));


        $doneStatuses = ['TIEFBAU', 'SPLEISSE', 'EINBLASEN', 'ABLIEFERN', 'HAUSANSCHLUSS', 'ARBEITSVORBEREITUNG'];

        if ($status1 === 'TIEFBAU') {
            if ($status3 === 'GEREED') {
                return 'DONE'; // Return 'DONE' only if TIEFBAU and GEREED
            } else {
                return 'OPEN'; // Return 'OPEN' for TIEFBAU if not GEREED
            }
        }

        // Handle other done statuses
        if (in_array($status1, $doneStatuses)) {
            return 'DONE';
        }

        if ($status1 === 'HAUSBEGEHUNG') {
            return 'OPEN';
        }

        $stoppedStatuses = ['R1', 'R2', 'R3', 'R4', 'R5', 'R6', 'R7', 'R8', 'R9', 'R10', 'R12', 'R18', 'R20', 'R22', 'R23', 'R24'];
        if (in_array($status2, $stoppedStatuses)) {
            return 'STOPPED';
        }

        return 'UNKNOWN';
    }

    if ($fileCarrier === 'GVG') {
        $status1 = strtolower(trim($system_status1));
        $status2 = strtolower(trim($system_status2));

        // Check if both conditions are met
        if ($status1 === '- begehungstermin fehlt -' && $status2 === 'nein') {
            return "OPEN";
        }
        if (strtotime($status1) !== false && $status2 === 'ja') {
            return "DONE";
        }
    }


    // GlasfaserPlus Logic
    if ($fileCarrier === 'GlasfaserPlus') {
        if (in_array($status, ['PLAN SITE SURVEY', 'COMPLETE SITE SURVEY', 'SOLVE WAITING REASON', 'PLAN GFAP-INSTALLATION'])) {
            return 'OPEN';
        } else {
            return 'OPEN';
        }
    }

    return 'UNKNOWN';
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Handle different actions
        switch ($_POST['action']) {
            case 'safe_newMappingHeader':
                $response = safe_newMappingHeader($db);
                break;
            case 'safe_customerData':
                $response = safe_customerData($db);
                break;
            case 'safe_csvdata':
                $response = safe_csvdata($db);
            case 'safe_test':
                $response = safe_test();
            default:

                break;
        }
    } elseif (isset($_FILES['file'])) {
        // Initialize the global $fileData object
        $fileData = [
            'processedRows' => [],
            'rows' => [],
            'metadata' => [
                'mapping' => [],
                'columns' => [],
                'identifiedColumns' => [],
                'unidentifiedColumns' => []
            ]
        ];

        // Setup the initial response structure
        $response = [
            'requestMethod' => $_SERVER['REQUEST_METHOD'],
            'fileReceived' => isset($_FILES['file']),
            'success' => false,
            'message' => '',
            'fileDetails' => [],
            'fileIdentifier' => null
        ];

        $file = $_FILES['file'];
        $file_path = $file['tmp_name'];
        $fileIdentifier = uniqid('file_', true);
        $response['fileIdentifier'] = $fileIdentifier;

        if (($handle = fopen($file_path, 'r')) !== FALSE) {
            // Get the headers first
            $headers = fgetcsv($handle, 0, ";");
            $fileData['metadata']['columns'] = array_map(function ($header) {
                return isUtf8($header) ? $header : mb_convert_encoding($header, 'UTF-8', 'ISO-8859-1');
            }, $headers);

            $rowCount = 0;

            while (($row = fgetcsv($handle, 0, ";")) !== FALSE) {
                // Convert the encoding of each cell to UTF-8 if necessary
                $convertedRow = array_map(function ($cell) {
                    return isUtf8($cell) ? $cell : mb_convert_encoding($cell, 'UTF-8', 'ISO-8859-1');
                }, $row);

                $fileData['rows'][] = $convertedRow;
                $rowCount++;
            }
            fclose($handle);


            // Load header mappings
            $knownMappings = load_headerMapping($db);
            $customers = load_customers($db);
            $citylist = load_cities($db);

            $fileData['metadata']['mapping'] = $knownMappings;


            // Check for each known header in the file
            foreach ($fileData['metadata']['columns'] as $index => $header) {
                // Clean the header to remove BOM and other non-printable characters
                $header = preg_replace('/[^\P{C}\n]+/u', '', trim(strtolower($header)));

                if (array_key_exists($header, $knownMappings)) {
                    $mappedName = $knownMappings[$header];
                    $fileData['metadata']['identifiedColumns'][$mappedName] = [
                        'position' => $index,
                        'match' => $header
                    ];
                } else {
                    $fileData['metadata']['unidentifiedColumns'][] = $header;
                }
            }

            $homeIdTracker = [];
            $cleanedData = [];
            $allCities = [];
            $checkedVal = [];
            // Carrier identification
            $fileCarrier = 'Unknown';
            $fileClient = 'Unknown';
            $fileProjectType = null;

            $cityColumnName = $fileData['metadata']['identifiedColumns']['city']['position'] ?? null;
            if ($cityColumnName !== null) {
                foreach ($fileData['rows'] as $row) {
                    $cityValue = trim($row[$cityColumnName]);
                    $cityValue = str_replace('HE_HE_', '', $cityValue);
                    $cityValue = strtolower($cityValue);

                    foreach ($citylist as $cityInfo) {
                        if (strtolower(trim($cityInfo['city'])) === $cityValue) {
                            // Once a match is found, set fileCarrier and fileClient
                            $fileCarrier = $cityInfo['carrier'];
                            $fileClient = $cityInfo['client'];
                            break 2; // Break out of both loops as we've found our match
                        }
                    }
                }
            }


            $rowCount = 0;

            // Check for project type in the first 10 rows
            foreach ($fileData['rows'] as $row) {
                if (++$rowCount > 10) {
                    break; // Exit the loop after checking 10 rows
                }

                $projectType = $row[$fileData['metadata']['identifiedColumns']['project_type']['position']] ?? null;
                if (in_array($projectType, ['MDU NE3', 'MDU NE5'])) {
                    $fileProjectType = 'MDU';
                    break; // Exit the loop as we've identified the project type
                }
            }

            foreach ($fileData['rows'] as $row) {
                $cleanedRow = [];
                foreach ($fileData['metadata']['identifiedColumns'] as $columnName => $columnDetails) {
                    $cellValue = $row[$columnDetails['position']];
                    $checkedVal[] = $cellValue;

                    switch ($columnName) {
                        case 'dpnumber':
                            if ($fileProjectType === 'MDU' && ($cellValue === null || $cellValue === '')) {
                                $cleanedRow['dpnumber'] = $fileProjectType;
                            } else {
                                // Handle other cases or simply assign $cellValue to dpnumber
                                $cleanedRow['dpnumber'] = $cellValue;
                            }
                            break;
                        case 'phone1':
                            $phoneNumbers = clean_phone($cellValue);
                            $cleanedRow['phone1'] = $phoneNumbers[0] ?? null; // First number or null if not present
                            $cleanedRow['phone2'] = $phoneNumbers[1] ?? null; // Second number or null if not present
                            break;
                        case 'phone2':
                            $phoneNumbers = clean_phone($cellValue);
                            $cleanedRow['phone3'] = $phoneNumbers[0] ?? null; // First number or null if not present
                            $cleanedRow['phone4'] = $phoneNumbers[1] ?? null; // Second number or null if not present
                            break;
                        case 'name':
                            // Split and clean the full name
                            $nameParts = clean_name($cellValue);
                            $cleanedRow['firstname'] = $nameParts['firstname'];
                            $cleanedRow['lastname'] = $nameParts['lastname'];
                            break;
                        case 'firstname':
                            // Clean the first name
                            $cleanedRow['firstname'] = clean_name($cellValue, true)['fullname'];
                            break;
                        case 'lastname':
                            // Clean the last name
                            $cleanedRow['lastname'] = clean_name($cellValue, true)['fullname'];
                            break;
                        case 'homeid':
                            $cleanedRow['homeid'] = $cellValue;
                            $cleanedRow['carrier'] = $fileCarrier;
                            $cleanedRow['client'] = $fileClient;
                            $cleanedRow['unit'] = substr($cellValue, -1);

                            // Track the home ID for duplicates
                            if (isset($homeIdTracker[$cellValue])) {
                                $homeIdTracker[$cellValue][] = $row;
                            } else {
                                $homeIdTracker[$cellValue] = [$row];
                            }
                            break;
                        case 'city':
                            $cellValue = str_replace('HE_HE_', '', trim($row[$cityColumnName]));
                            $cleanedRow[$columnName] = isset($fileProjectType) ? $cellValue . ' ' . $fileProjectType : $cellValue;
                            break;
                        case 'plz':
                            $cleanedRow[$columnName] = clean_plz($cellValue);
                            break;
                        case 'fulladdress':
                            // Directly integrate address splitting here
                            $addressComponents = split_full_address($cellValue);
                            // Assign the components to respective keys in cleanedRow
                            $cleanedRow['street'] = $addressComponents['street'];
                            $cleanedRow['streetnumber'] = $addressComponents['streetnumber'];
                            $cleanedRow['streetnumberadd'] = $addressComponents['streetnumberadd'];
                            break;
                        case 'hbg_date':
                        case 'hbg_plandate':
                        case 'hc_plandate':
                            $cleanedRow[$columnName] = clean_date($cellValue);
                            break;
                        case 'system_status':
                            $cleanedRow[$columnName] = $cellValue; // write the original value to the system_status
                            $system_status2 = $row[$fileData['metadata']['identifiedColumns']['system_status2']['position']] ?? null;
                            $system_status3 = $row[$fileData['metadata']['identifiedColumns']['system_status3']['position']] ?? null;
                            $finalStatus = ident_systemstatus($cellValue, $fileCarrier, $system_status2, $system_status3);
                            $cleanedRow['hbg_status'] = $finalStatus;
                            break;
                        default:
                            $cleanedRow[$columnName] = $cellValue;
                            break;
                    }

                    // Check if both city and zip are identified and populate $allCities
                    if (isset($fileData['metadata']['identifiedColumns']['city']['position']) && isset($fileData['metadata']['identifiedColumns']['plz']['position'])) {
                        $cityIndex = $fileData['metadata']['identifiedColumns']['city']['position'];
                        $zipIndex = $fileData['metadata']['identifiedColumns']['plz']['position'];
                        $cityWithZipKey = $row[$cityIndex] . '-' . $row[$zipIndex];

                        // Only add the combination if it's not already in the array
                        if (!array_key_exists($cityWithZipKey, $allCities)) {
                            $allCities[$cityWithZipKey] = ['city' => $row[$cityIndex], 'plz' => $row[$zipIndex]];
                        }
                    }
                }
                $cleanedData[] = $cleanedRow;
            }
            $fileData['processedRows'] = $cleanedData;

            // remove all single homeids to keep only the doubbles
            $doubleHomeIds = array_filter($homeIdTracker, function ($rows) {
                return count($rows) > 1;
            });

            // Populate the response with details
            $response['success'] = true;
            $response['fileDetails'] = [
                'numOfColumns' => count($fileData['metadata']['columns']),
                'headers' => $fileData['metadata']['columns'],
                'identifiedColumns' => $fileData['metadata']['identifiedColumns'],
                'unidentifiedColumns' => $fileData['metadata']['unidentifiedColumns'],
                'rowCount' => $rowCount,
                'doubleHomeIDs' => $doubleHomeIds,
            ];
            $response['fileDetails']['cities'] = [
                'total' => count(array_keys($allCities)),
                'unique' => array_values($allCities) // Array of unique city and zip combinations
            ];
            $response['fileData'] = $fileData;
            $response['headerMapping'] = $knownMappings;
            $response['system']['customers'] = $customers;
            $response['system']['citys'] = $citylist;
        } else {
            $error = error_get_last();
            $response['message'] = 'Error opening the file: ' . $error['message'];
        }
    } else {
        $response['message'] = 'No action or file provided';
    }
} else {
    $response['message'] = 'Error with request.';
}



// Return the response as JSON
header('Content-Type: application/json');
$jsonResponse = json_encode($response);
if ($jsonResponse === false) {
    error_log("json_encode error: " . json_last_error_msg());
}
echo $jsonResponse;



function safe_newMappingHeader($db)
{
    // Setup the initial response structure
    $response = [
        'action' => 'safe_newMappingHeader',
        'success' => false,
        'message' => '',
    ];

    $header = $_POST['header'];
    $mapped = $_POST['mapped'];

    // Prepare the SQL statement
    $stmt = $db->prepare("INSERT INTO `import_headermapping` (`header_name`, `mapped_name`) VALUES (?, ?)");

    if ($stmt === false) {
        // Handle error in statement preparation
        $response['message'] = 'Error preparing statement: ' . $db->error;
        return $response;
    }

    // Bind parameters to the prepared statement
    $stmt->bind_param("ss", $header, $mapped);

    // Execute the statement
    if ($stmt->execute()) {
        // Success
        $response['success'] = true;
        $response['message'] = 'Mapping header saved successfully.';
    } else {
        // Error in execution
        $response['message'] = 'Error executing statement: ' . $stmt->error;
    }

    // Close the statement
    $stmt->close();

    return $response;
}


function safe_customerData($db)
{
    $updatedCustomers = !empty($_POST['updatedCustomers']) ? json_decode($_POST['updatedCustomers'], true) : [];
    $newCustomers = !empty($_POST['newCustomers']) ? json_decode($_POST['newCustomers'], true) : [];

    $insertResponse = ['success' => true, 'successfulInserts' => 0, 'failedInserts' => 0, 'errors' => []];
    $updateResponse = ['success' => true, 'message' => '', 'errors' => []];

    // Debug: Log the count of new and updated customers received
    error_log("Received " . count($newCustomers) . " new customers for processing.");
    error_log("Received " . count($updatedCustomers) . " updated customers for processing.");

    // Load city list once
    $cityListRows = load_cities($db);
    $cityNames = array_column($cityListRows, 'city');
    error_log("CityNames loaded #" . count($cityNames));

    // Process newCustomers if they are provided
    if (!empty($newCustomers)) {
        $originalCount = count($newCustomers);
        $newCustomers = array_filter($newCustomers, function ($customer) use ($cityNames) {
            $isValid = in_array($customer['city'], $cityNames);
            if (!$isValid) {
                // Debug: Log the reason for filtering out a customer
                error_log("Filtering out customer due to city mismatch: " . json_encode($customer));
            }
            return $isValid;
        });
        $filteredCount = count($newCustomers);

        // Debug: Log the count before and after filtering
        error_log("Customers before filtering: $originalCount, after filtering: $filteredCount");

        $insertResponse = insertNewCustomers($newCustomers, $db);
    }

    // Process updatedCustomers if they are provided
    if (!empty($updatedCustomers)) {
        $updateResponse = updateExistingCustomers($updatedCustomers, $db);
    }

    // Construct a detailed message
    $messages = [];
    if (!empty($newCustomers)) {
        $insertMessage = "New customers - Successful: {$insertResponse['successfulInserts']}, Failed: {$insertResponse['failedInserts']}.";
        $messages[] = $insertMessage;
    }
    if (!empty($updatedCustomers)) {
        $messages[] = $updateResponse['message'];
    }
    if (empty($messages)) {
        $messages[] = 'No customers were added or updated.';
    }
    $message = implode(" ", $messages);

    // Consolidate responses and errors
    $response = [
        'success' => $insertResponse['success'] && $updateResponse['success'],
        'message' => $message,
        'errors' => array_merge($insertResponse['errors'], $updateResponse['errors'])
    ];

    return $response;
}

function updateExistingCustomers($updatedCustomers, $db)
{
    global $currentuser;
    $currentTimestamp = date('Y-m-d H:i:s');
    $reason = 'import';

    $successfulUpdates = 0;
    $failedUpdates = 0;
    $errors = [];

    // Prepare the history insert statement once outside the loops
    $historySql = "INSERT INTO homes_history (homeid, field_changed, old_value, new_value, user, reason, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $historyStmt = $db->prepare($historySql);
    if (!$historyStmt) {
        error_log("Error preparing history statement: " . $db->error);
        // Handle this error appropriately
    }

    foreach ($updatedCustomers as $customer) {
        $changes = $customer['changes'];
        $updateFields = [];
        $updateValues = [];

        foreach ($changes as $field => $change) {
            if (in_array($field, ['system_status2', 'system_status3', 'project_type', 'address'])) {
                continue; // Skip these fields
            }

            if ($change['changed'] && isset($change['new'])) {
                $updateFields[] = "$field = ?";
                $updateValues[] = $change['new'];

                // Record this change in the history table
                if ($historyStmt) {
                    $oldValue = isset($change['old']) ? $change['old'] : null;
                    $newValue = $change['new'];

                    $historyStmt->bind_param("sssssss", $customer['homeid'], $field, $oldValue, $newValue, $currentuser, $reason, $currentTimestamp);
                    if (!$historyStmt->execute()) {
                        $errors[] = "Error inserting into history table for homeid {$customer['homeid']} field $field: " . $historyStmt->error;
                        error_log("Error inserting into history table for homeid {$customer['homeid']} field $field: " . $historyStmt->error);
                    }
                }
            }
        }

        if (!empty($updateFields)) {
            $sql = "UPDATE scan4_homes SET " . implode(", ", $updateFields) . " WHERE id = ?";
            $updateValues[] = $customer['id'];

            $stmt = $db->prepare($sql);
            if (!$stmt) {
                $errors[] = "Error preparing update statement: " . $db->error;
                $failedUpdates++;
                continue;
            }

            $stmt->bind_param(str_repeat('s', count($updateValues)), ...$updateValues);
            if (!$stmt->execute()) {
                error_log("Error executing update statement for ID {$customer['id']}: " . $stmt->error);
                $errors[] = "Error executing update statement for ID {$customer['id']}: " . $stmt->error;
                $failedUpdates++;
            } else {
                $successfulUpdates += ($stmt->affected_rows > 0) ? 1 : 0;
                if ($stmt->affected_rows == 0) {
                    $errors[] = "No rows updated for ID: {$customer['id']}";
                    $failedUpdates++;
                }
            }
        }
    }

    return [
        'success' => $failedUpdates === 0,
        'message' => "Successful updates: $successfulUpdates, Failed updates: $failedUpdates",
        'errors' => $errors
    ];
}




function insertNewCustomers($newCustomers, $db)
{
    $errors = [];
    $successfulInserts = 0;
    $failedInserts = 0;
    $today = date('Y-m-d');

    error_log("Received " . count($newCustomers) . " new customers for insertion.");

    foreach ($newCustomers as $customer) {
        error_log("Processing customer: " . json_encode($customer));

        $customer['scan4_added'] = $today;
        $hbgStatus = $customer['hbg_status']; // Get hbg_status value

        $filteredCustomer = array_filter($customer, function ($key) {
            return !in_array($key, ['system_status2', 'system_status3', 'project_type', 'address']);
        }, ARRAY_FILTER_USE_KEY);

        $fields = implode(", ", array_keys($filteredCustomer)) . ", scan4_status";
        $placeholders = implode(", ", array_fill(0, count($filteredCustomer), '?')) . ", ?";
        $values = array_values($filteredCustomer);
        $values[] = $hbgStatus; // Add hbg_status value as scan4_status

        $sql = "INSERT IGNORE INTO scan4_homes ($fields) VALUES ($placeholders)";
        $stmt = $db->prepare($sql);

        if (!$stmt) {
            $errors[] = "Error preparing statement: " . $db->error;
            $failedInserts++;
            error_log("Preparation error: " . $db->error);
            continue;
        }

        $stmt->bind_param(str_repeat('s', count($values)), ...$values);
        if (!$stmt->execute()) {
            $errors[] = "Error executing statement: " . $stmt->error;
            $failedInserts++;
            error_log("Execution error: " . $stmt->error);
        } else {
            if ($stmt->affected_rows > 0) {
                $successfulInserts++;
            } else {
                $errorDetail = "No rows inserted (possible duplicate): " . implode(", ", $values);
                $errors[] = $errorDetail;
                error_log($errorDetail);
                $failedInserts++;
            }
        }
    }

    error_log("Insertion results - Successful: $successfulInserts, Failed: $failedInserts");

    return [
        'success' => $failedInserts === 0,
        'successfulInserts' => $successfulInserts,
        'failedInserts' => $failedInserts,
        'errors' => $errors
    ];
}


function safe_csvdata($db)
{
    if (!isset($_POST['customers']) || empty($_POST['customers'])) {
        return ['success' => false, 'message' => 'No customers data provided'];
    }

    // Decode the JSON string
    $customers = json_decode($_POST['customers'], true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['success' => false, 'message' => 'Invalid JSON format for customers'];
    }

    // Extract headers dynamically from the first customer's changes
    $firstCustomer = reset($customers); // Get the first customer in the array
    if (!$firstCustomer || !isset($firstCustomer['changes'])) {
        return ['success' => false, 'message' => 'Invalid customer data structure'];
    }

    $dynamicHeaders = array_keys($firstCustomer['changes']); // Extract keys from the changes array
    array_unshift($dynamicHeaders, 'Homeid'); // Ensure 'Homeid' is at the beginning of the headers

    $filename = 'customer_changes_' . date('Y-m-d_H-i-s') . '.csv';
    $filepath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/import/' . $filename;

    if (!file_exists(dirname($filepath))) {
        if (!mkdir(dirname($filepath), 0777, true)) {
            error_log("Failed to create directory: " . dirname($filepath));
            return ['success' => false, 'message' => 'Failed to create directory'];
        }
    }


    // Open the file for writing
    $fp = fopen($filepath, 'w');
    if ($fp === false) {
        return ['success' => false, 'message' => 'Failed to open file for writing'];
    }

    // Write the first row with the export date
    fputcsv($fp, ['Exported data on ' . date('d-m-Y')], ';');

    // Write the dynamically generated header row
    fputcsv($fp, $dynamicHeaders, ';');

    // Iterate through each customer
    foreach ($customers as $customer) {
        $row = ['Homeid' => $customer['homeid']]; // Initialize row with 'Homeid'
        foreach ($dynamicHeaders as $header) {
            if ($header === 'Homeid') continue; // Skip 'Homeid' as it's already handled
            $change = $customer['changes'][$header] ?? null;
            if ($change && $change['changed']) {
                $row[$header] = isset($change['old']) ? "{$change['old']} => {$change['new']}" : "=> {$change['new']}";
            } else {
                $row[$header] = ''; // No change or not applicable
            }
        }
        fputcsv($fp, $row, ';');
    }

    fclose($fp);

    return ['success' => true, 'message' => 'CSV file saved successfully', 'filename' => $filename];
}


function safe_test()
{
    $filename = 'customer_changes_' . date('Y-m-d_H-i-s') . '.csv';
    $filepath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/import/' . $filename;

    if (!file_exists(dirname($filepath))) {
        if (!mkdir(dirname($filepath), 0777, true)) {
            error_log("Failed to create directory: " . dirname($filepath));
            return ['success' => false, 'message' => 'Failed to create directory'];
        }
    }

    // Open the file for writing
    $fp = fopen($filepath, 'w');
    if ($fp === false) {
        return ['success' => false, 'message' => 'Failed to open file for writing '];
    } else {
        fclose($fp);
        return ['success' => true, 'message' => 'File created'];
    }
}


$db->close();
