<?php
header('Access-Control-Allow-Origin: *'); // Allow all origins for CORS
header('Access-Control-Allow-Methods: GET, POST, OPTIONS'); // Allowed methods
header('Access-Control-Allow-Headers: X-Custom-Token, Content-Type'); // Allowed headers including your custom token header

// Handle OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // Just exit with 200 OK if the request method is OPTIONS
    http_response_code(200);
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
setlocale(LC_TIME, 'de_DE.utf8');

$expectedToken = 'YHf6Sj1fzyqBdc3G4LgeTtSsYZZgWK6uQFsl6UgtZQletTkKVtOUmQ6c0c9wgW09oHvaG1ZyUL0CwMtUMbXDgFGWmI2u1ybl4B5wv62vBpk6040ic5dyq5AbqIkVYW8O'; // Replace with your actual token
$receivedToken = isset(getallheaders()['X-Custom-Token']) ? getallheaders()['X-Custom-Token'] : null;

$technicianName = isset($_SERVER['HTTP_X_USERNAME']) ? $_SERVER['HTTP_X_USERNAME'] : '';



if ($receivedToken === null) {
    error_log("No token provided");
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'No token provided']);
    exit;
}

if ($receivedToken != $expectedToken) {
    error_log("Token mismatch");
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized - Token Mismatch']);
    exit;
}

// Store the original DOCUMENT_ROOT
$originalDocumentRoot = $_SERVER['DOCUMENT_ROOT'];

// Set the correct DOCUMENT_ROOT for functions.php to load all as it should
$_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . '/..';
include_once $_SERVER['DOCUMENT_ROOT'] . '/view/includes/functions.php';
// Restore the original DOCUMENT_ROOT
$_SERVER['DOCUMENT_ROOT'] = $originalDocumentRoot;



// Initialize default response
$response = [
    'status' => 'error',
    'message' => 'Unknown request.',
    'data' => []
];

$func = isset($_GET['action']) ? $_GET['action'] : null;

// Check the function request and execute the corresponding code
switch ($func) {
    case 'appointments_load':
        $response = getAppointments();
        break;
    case 'getServerIp':
        $response = []; // Initialize the array
        $response['status'] = 'success';
        $response['message'] = 'Server IP: ' . $_SERVER['SERVER_ADDR'];
        break;
    default:
        $response['message'] = 'Unknown function request.';
        break;
}

header('Content-Type: application/json');
echo json_encode($response);
exit;


// Get today's appointments
function getAppointments()
{
    $conn = dbconnect();

    $response = [
        'status' => 'error',
        'message' => 'Unknown error occurred.',
        'data' => []
    ];

    // Get the date from the GET request, or default to today's date
    $targetDate = isset($_GET['date']) ? $_GET['date'] : date("Y-m-d");

    // The technician's name
    global $technicianName;
    //$technicianName = 'PauloStascheit';

    // Check if the connection is successful
    if ($conn->connect_error) {
        $response['message'] = "Connection failed.";
        return $response;
    }

    $query = "SELECT 
                    scan4_hbg.*,
                    scan4_homes.client,
                    scan4_homes.carrier,
                    scan4_homes.street,
                    scan4_homes.streetnumber,
                    scan4_homes.streetnumberadd,
                    scan4_homes.city,
                    scan4_homes.plz,
                    scan4_homes.firstname,
                    scan4_homes.lastname,
                    scan4_homes.adressid,
                    scan4_homes.phone1,
                    scan4_homes.phone2,
                    scan4_homes.phone3,
                    scan4_homes.phone4,
                    scan4_homes.unit,
                    scan4_homes.lat,
                    scan4_homes.lon
                FROM scan4_hbg
                INNER JOIN scan4_homes ON scan4_hbg.homeid = scan4_homes.homeid
                WHERE scan4_hbg.status = 'PLANNED'
                AND scan4_hbg.hausbegeher = ?
                AND scan4_hbg.date = ?
                ORDER BY scan4_hbg.time ASC;
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $technicianName, $targetDate);

    if ($stmt->execute()) {
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $response['data'][] = $row;
        }
        $response['status'] = 'success';
        $response['$technicianName'] = $technicianName;
        $response['message'] = 'Data loaded successfully.';
    } else {
        $response['message'] = "Error fetching data.";
    }

    $stmt->close();
    $conn->close();

    return $response;
}
