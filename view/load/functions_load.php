<?php



if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}
if (!securePage($_SERVER['PHP_SELF'])) {
    die();
}



$logged_in = $user->data();
$currentuser = $logged_in->username;
$userdetails = fetchPermissionUsers(1); // 5 = Telefonist
for ($i = 0; $i < count($userdetails); $i++) {
    $data = fetchUserDetails(null, null, $userdetails[$i]->user_id);
    $a_userlist =  array($data->username => $data->profile_pic);
}


include "../../view/includes/functions.php";



date_default_timezone_set('Europe/Berlin');
if (!isset($_POST['func'])) {
    die();
}


$func = $_POST['func'];
if ($func === "userActivity") {
    userActivity();
} else if ($func === 'recordHeatmapData') {
    recordHeatmapData();
} else if ($func === 'getPermArray') {
    generatePermsArray();
}

function userActivity()
{
    global $currentuser;
    $conn = dbconnect();

    if ($conn->connect_errno) {
        $errorMsg = "Connection failed: " . $conn->connect_error;
        echo $errorMsg;
        error_log($errorMsg);
        return;
    }

    $data = isset($_POST['data']) ? $_POST['data'] : null;

    if (!$data) {
        $errorMsg = "No data received in userActivity.";
        echo $errorMsg;
        error_log($errorMsg);
        return;
    }

    $type = $conn->real_escape_string($data['type']);
    $id = $conn->real_escape_string($data['id']);
    $class = $conn->real_escape_string($data['class']);
    $href = $conn->real_escape_string($data['href']);
    $text = $conn->real_escape_string($data['text']);
    $page = $conn->real_escape_string($data['page']);
    $user = $conn->real_escape_string($currentuser);

    $stmt = $conn->prepare("INSERT INTO user_interactions (user, type, element_id, class, href, text, page) VALUES (?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        $errorMsg = "Prepare failed at line " . __LINE__ . ": (" . $conn->errno . ") " . $conn->error;
        echo $errorMsg;
        error_log($errorMsg);
        return;
    }

    if (!$stmt->bind_param("sssssss", $user, $type, $id, $class, $href, $text, $page)) {
        $errorMsg = "Binding parameters failed at line " . __LINE__ . ": (" . $stmt->errno . ") " . $stmt->error;
        echo $errorMsg;
        error_log($errorMsg);
        return;
    }

    if (!$stmt->execute()) {
        $errorMsg = "Execute failed at line " . __LINE__ . ": (" . $stmt->errno . ") " . $stmt->error;
        echo $errorMsg;
        error_log($errorMsg);
        return;
    }

    $stmt->close();
}


function recordHeatmapData()
{
    global $currentuser;
    $conn = dbconnect();

    $interactions = $_POST['data'];

    // Convert interactions into a JSON string
    $jsonInteractions = json_encode($interactions);

    // Prepare the insert statement
    $stmt = $conn->prepare("INSERT INTO heatmap_data (user, interactions) VALUES (?, ?)");

    // Bind the parameters and execute
    $stmt->bind_param("ss", $currentuser, $jsonInteractions);
    $stmt->execute();

    // Close the statement
    $stmt->close();
}



function generatePermsArray()
{
    $permsArray = [];
    for ($i = 1; $i <= 30; $i++) {
        $permsArray[$i] = hasPerm($i);
    }
    $json_permissions = json_encode($permsArray);
    echo $json_permissions;
    return $permsArray;
}
