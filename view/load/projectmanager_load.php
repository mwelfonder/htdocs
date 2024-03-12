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

include "../../view/includes/functions.php";


echo 'loadme';

$conn = dbconnect();


$func = $_POST['func'];
if ($func === "project_savenew") { 
    project_savenew($conn);
} else if ($func === "safe_call_note") {
}


/**
 * Saves new records into the "scan4_citylist" table in the database.
 *
 * @return void
 */
function project_savenew($conn)
{
    // Get the input data from the $_POST superglobal
    $projectName = $_POST['projectName'];
    $projectId = $_POST['projectId'];
    $latLon = $_POST['latLon'];
    $projectStartDate = $_POST['projectStartDate'];
    $carrier = $_POST['carrier'];
    $client = $_POST['client'];

    // Split latLon into separate latitude and longitude variables
    list($lat, $lon) = explode(',', $latLon);

    // Validation (Optional): Add your validation logic here (e.g., check if values are empty)

    // Prepare the SQL statement
    // Ensure you have the correct number of placeholders and the table column names are correct
    $stmt = $conn->prepare("INSERT INTO scan4_citylist (city, city_id, lat, lon, date_start, carrier, client) VALUES (?, ?, ?, ?, ?, ?, ?)");

    // Check for errors in preparation
    if ($stmt === false) {
        die("Error in SQL preparation: " . $conn->error);
    }

    // Bind the parameters of the SQL statement to the corresponding variables
    // Ensure the types of the parameters are correct ('s' for string, 'i' for integer, 'd' for double)
    $stmt->bind_param("siddsss", $projectName, $projectId, $lat, $lon, $projectStartDate, $carrier, $client);

    // Execute the SQL statement to insert the new record into the database
    if (!$stmt->execute()) {
        die("Error in executing SQL: " . $stmt->error);
    }

    // Close the prepared statement
    $stmt->close();

    // Echo a success message
    echo "New records created successfully";
}




$conn->close();
