<?php
date_default_timezone_set('Europe/Berlin');

// Expected custom token for verification


$expectedToken = 'YHf6Sj1fzyqBdc3G4LgeTtSsYZZgWK6uQFsl6UgtZQletTkKVtOUmQ6c0c9wgW09oHvaG1ZyUL0CwMtUMbXDgFGWmI2u1ybl4B5wv62vBpk6040ic5dyq5AbqIkVYW8O';

// Retrieve the custom token from the request headers
$receivedToken = isset(getallheaders()['X-Custom-Token']) ? getallheaders()['X-Custom-Token'] : null;

// Validate the received token
if ($receivedToken === null || $receivedToken !== $expectedToken) {
    header('Content-Type: application/json');
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'message' => 'Unauthorized - Invalid or missing token']);
    exit;
}

header('Content-Type: application/json');


// Database Connection Function
function dbconnect()
{
    $servername = "db.scan4-gmbh.com";
    $username = "SC4_CRM";
    $password = "1*eA7uxr.XAEg*cH";
    $dbname = "SC4_CRM_2";
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}
function dbconnect_DEV()
{
    $servername = "db.scan4-gmbh.com";
    $username = "SC4_CRM";
    $password = "1*eA7uxr.XAEg*cH";
    $dbname = "SC4_CRM_DEV_2";
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// Main Processing Function
function processRequest()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(["success" => false, "message" => "Invalid request method"]);
        return;
    }


    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'appointmentData':
                processHBGRequest();
                break;
            case 'survey_data':
                process_SurveyDataRequest();
                break;
            case 'upload_image':
                if (isset($_FILES['image']) && isset($_POST['imgId']) && isset($_POST['homeid'])) {
                    process_surveyImages($_FILES['image'], $_POST['imgId'], $_POST['homeid']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Invalid image upload parameters']);
                }
                break;
            default:
                header('Content-Type: application/json');
                http_response_code(400); // Bad Request
                echo json_encode(['success' => false, 'message' => 'Unknown action']);
                break;
        }
    } else {
        // Handle the case where 'action' is not set in the POST request
        header('Content-Type: application/json');
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'message' => 'No action specified']);
    }
}


function processHBGRequest()
{
    // Initialize response
    $response = ["success" => true, "message" => "Data processed successfully"];
    $conn = dbconnect_DEV();

    // Extract form data
    $comment = $_POST['comment'] ?? null;
    $homeid = $_POST['homeid'] ?? null;
    $city = $_POST['city'] ?? null;
    $reason = $_POST['reason'] ?? null;
    $id = $_POST['id'] ?? null;
    $filePath = null;

    // Initialize image file path
    $imageFilePath = null;

    // Check if image data is sent
    if (isset($_POST['image'])) {
        // Extract the base64 data
        $base64_string = $_POST['image'];
        // Separate the metadata from the image data
        list($type, $data) = explode(';', $base64_string);
        list(, $data) = explode(',', $data);

        $imageFilePath = processImageData($homeid, $data);
    }


    if ($reason === 'excel') {
        $reason = 'done';
        $isExcel = true;
    } else {
        $isExcel = false;
    }

    // Process file upload if present
    if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
        $filePath = processFileUpload($homeid, $city);
        if (!$filePath) {
            $response = ["success" => false, "message" => "Failed to move uploaded file"];
            echo json_encode($response);
            return;
        }
    }

    // Update database
    if ($id) {
        $stmt = $conn->prepare("UPDATE scan4_hbg SET appt_status = ?,appt_protokoll = ?, appt_comment = ?, appt_file = ?, appt_datetime = NOW() WHERE id = ?");
        $stmt->bind_param("ssssi", $reason, $isExcel, $comment, $filePath, $id);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            $response = ["success" => false, "message" => "Failed to update database"];
        }

        $stmt->close();
    }

    $conn->close();
    echo json_encode($response);
}

function process_surveyImages($imageData, $imgId, $homeid)
{
    $year = date("Y"); // Get the current year
    $targetDir = "/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/uploads/scan4App/surveyData/images/" . $year . "/";

    // Check if the directory for the current year exists; if not, create it
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    // Create a base file name using the current timestamp and homeid
    $timestamp = date("Y_m_d");
    $baseFileName = $timestamp . "_" . $homeid;
    $targetFile = $targetDir . $baseFileName . ".jpg";

    // Initialize a counter for appending
    $counter = 1;

    // Check if file already exists, if so, append a number to it
    while (file_exists($targetFile)) {
        $targetFile = $targetDir . $baseFileName . '_i' . $counter . ".jpg";
        $counter++;
    }

    // Check if image file is an actual image or fake image
    if (isset($imageData)) {
        // Attempt to move the uploaded file to the target directory
        if (move_uploaded_file($imageData['tmp_name'], $targetFile)) {
            echo json_encode(['success' => true, 'message' => 'The file has been uploaded.', 'filename' => basename($targetFile)]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Sorry, there was an error uploading your file.']);
        }
    }
}

function processImageData($homeid, $imageData)
{
    error_log("processImageData called with homeid: $homeid"); // Log function call
    error_log("processImageData called with imageData: $imageData");

    // Decode the image data
    $decodedImageData = base64_decode($imageData);
    if (!$decodedImageData) {
        error_log("Failed to decode image data for homeid: $homeid");
        return null;
    }

    // Create a file path
    $year = date("Y"); // Get the current year
    $directory = "/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/uploads/scan4App/abbruch/" . $year . "/";
    if (!file_exists($directory)) {
        if (!mkdir($directory, 0777, true)) {
            error_log("Failed to create directory: $directory");
            return null;
        }
    }

    $timestamp = date("Y_m_d");
    $filePath = $directory . $timestamp . '_' . $homeid . '_' . uniqid() . '.jpg';

    // Save the image data to a file
    if (file_put_contents($filePath, $decodedImageData) === false) {
        error_log("Failed to save image file to path: $filePath");
        return null;
    }

    error_log("Image saved successfully to: $filePath"); // Log success
    return $filePath;
}




function process_SurveyDataRequest()
{
    $conn = dbconnect_DEV();
    $surveyData = $_POST['data'] ?? null; // Survey data
    $homeid = $_POST['homeid'] ?? null; // Home ID
    $username = $_POST['username'] ?? null; // Username

    if ($surveyData && $homeid && $username) {
        // Convert array to JSON
        $jsonData = json_encode($surveyData);

        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO survey_data (homeid, data, user, created) VALUES (?, ?, ?, CURRENT_TIMESTAMP)");

        if ($stmt === false) {
            echo json_encode(["success" => false, "message" => "Failed to prepare statement"]);
            return;
        }

        $stmt->bind_param("sss", $homeid, $jsonData, $username);

        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Survey data stored successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to store survey data"]);
        }

        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "No survey data received"]);
    }

    $conn->close();
}


// File Upload Processing Function
function processFileUpload($homeid, $city)
{
    $specialChars = [
        'ü' => 'ue', 'Ü' => 'Ue',
        'ä' => 'ae', 'Ä' => 'Ae',
        'ö' => 'oe', 'Ö' => 'Oe',
        'ß' => 'ss'
    ];
    foreach ($specialChars as $char => $replacement) {
        $city = str_replace($char, $replacement, $city);
    }
    $city = str_replace(' ', '', $city);

    $uploadedFile = $_FILES['file']['tmp_name'];
    $newFileName = date('Y_m_d_H_i') . '_' . $homeid . '.pdf';
    $baseDir = '/var/www/vhosts/scan4-gmbh.de/crm.scan4-gmbh.de/uploads/hbgprotokolle/begehungen/screenshots/';
    $yearDir = $baseDir . date('Y');
    $cityDir = $yearDir . '/' . $city;
    $destinationPath = $cityDir . '/' . $newFileName;

    if (!is_dir($yearDir)) {
        mkdir($yearDir, 0755, true);
    }
    if (!is_dir($cityDir)) {
        mkdir($cityDir, 0755, true);
    }

    if (move_uploaded_file($uploadedFile, $destinationPath)) {
        return $destinationPath;
    }

    return null;
}

// Execute Main Function
processRequest();
