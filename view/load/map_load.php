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
if ($func === "load_homeid") {
    $homeid = $_POST['homeid'];
    load_homeid($homeid);
} else if ($func === "safe_call_note") {
    $homeid = $_POST['homeid'];
    $comment = $_POST['comment'];
    $state = $_POST['state'];
    $id = null;
    if ($state === 'update') {
        $id = $_POST['id'];
    }
    safe_call_note($homeid, $comment, $id);
} else if ($func === "delete_call_note") {
    $homeid = $_POST['homeid'];
    $id = $_POST['id'];
    delete_call_note($homeid, $id);
} else if ($func === "safe_nohbg") {
    $homeid = $_POST['homeid'];
    $reason = $_POST['reason'];
    $comment = $_POST['comment'];
    safe_nohbg($homeid, $comment, $reason);
} else if ($func === "save_pegman") {
    $data = $_POST['data'];
    $save = $_POST['save'];
    save_pegman($data, $save);
} else if ($func === "saveEventforUser") {
    $data = $_POST['data'];
    saveEventforUser($data);
} else if ($func === "saveUserLog") {
    $data = $_POST['data'];
    saveUserLog($data);
} else if ($func === "save_scan4phone") {
    $phonenumber = $_POST['phonenumber'];
    $homeid = $_POST['homeid'];
    $field = $_POST['field'];
    save_scan4phone($phonenumber, $homeid, $field);
} else if ($func === "save_arbeitszeit") {
    $jsonString = $_POST['data'];
    save_arbeitszeit($jsonString);
} else if ($func === "save_calEvent") {
    $jsonString = $_POST['data'];
    save_calEvent($jsonString);
} else if ($func === "refresh_calendar") {
    refresh_calendar();
} else if ($func === "delete_calEvent") {
    $eventid = $_POST['eventid'];
    delete_calEvent($eventid);
} else if ($func === "cancel_appointment") {
    $jsonString = $_POST['data'];
    cancel_appointment($jsonString);
} else if ($func === "fetchHomeid") {
    $homeid = $_POST['homeid'];
    fetchHomeid($homeid);
} else if ($func === "fetchTickets") {
    fetchTickets();
} else if ($func === "loadTicket") {
    $id = $_POST['id'];
    loadTicket($id);
} else if ($func === "downloadCSV") {
    $data = $_POST['data'];
    downloadCSV($data);
} else if ($func === "changeStatus") {
    changeStatus($data);
} else if ($func === "fetchRanking") {
    try {
        // Hier rufen Sie Ihre Funktion auf und generieren die Antwort
        $response = fetchRanking();
        // Konvertieren Sie die Antwort in JSON und senden Sie sie zurück zum Client
        echo json_encode(['success' => true, 'data' => $response]);
    } catch (Exception $e) {
        // Falls ein Fehler auftritt, senden Sie eine Fehlermeldung zurück
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else if ($func === "fetchRankingProzent") {
    try {
        // Hier rufen Sie Ihre Funktion auf und generieren die Antwort
        $response = fetchRankingProzent();
        // Konvertieren Sie die Antwort in JSON und senden Sie sie zurück zum Client
        echo json_encode(['success' => true, 'data' => $response]);
    } catch (Exception $e) {
        // Falls ein Fehler auftritt, senden Sie eine Fehlermeldung zurück
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else if ($func === "fetch_data") {
    $data = fetchdata();
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}



function fetchdata()
{
    $data = array();
    $activeCities = array();
    $citylist = array();
    $calendar = array();
    $pegmenData = array();

    $_open = array();
    $_planned = array();
    $_pending = array();
    $_overdue = array();
    $_done = array();
    $_stopped = array();

    $userPermissions = getAllPermissions();


    $conn = dbconnect();
    $startTime = microtime(true);

    // fetch citylist aktiv projects first to only show relevant markers
    $query = "SELECT city, status, lat, lon, client, carrier FROM scan4_citylist WHERE status = 'aktiv';";
    if ($result = $conn->query($query)) {

        while ($row = $result->fetch_assoc()) {
            $city = $row['city'];

            $activeCities[$city] =  $row;
            $citylist[] = $city;
        }
        $result->free_result();

        $citiesImploded = implode("','", $citylist);
        // get all homeids within aktiv citys which are OPEN
        $query = "
        SELECT 
            h.homeid, h.lat, h.lon, h.scan4_status, h.city, h.street, h.streetnumber, h.streetnumberadd, h.dpnumber, h.carrier, h.client, 
            h.firstname, h.lastname, h.phone1, h.phone2, h.phone3, h.priority,
            c.call_date, c.call_time, c.call_user, c.result, c.comment
        FROM scan4_homes h
        LEFT JOIN (
            SELECT homeid, MAX(id) as max_id
            FROM scan4_calls
            GROUP BY homeid
        ) subq ON h.homeid = subq.homeid
        LEFT JOIN scan4_calls c ON subq.max_id = c.id
        WHERE 
            h.scan4_status IN ('OPEN', 'PENDING', 'OVERDUE', 'DONE', 'STOPPED')
            AND h.city IN ('" . $citiesImploded . "')
            AND (h.client != 'Insyte' OR (h.dpnumber IS NOT NULL AND h.dpnumber != ''))
            AND (h.contractstatus IS NULL OR h.contractstatus = '' OR h.contractstatus != 'HVS')
    ";

        if ($result = $conn->query($query)) {
            $indexOpen = 0;
            $indexPending = 0;
            $indexOverdue = 0;
            $indexDone = 0;
            $indexStopped = 0;
            while ($row = $result->fetch_assoc()) {
                $homeid = $row['homeid'];

                // Extract calls data into a separate array
                $call_data = array(
                    'call_date' => $row['call_date'],
                    'call_time' => $row['call_time'],
                    'call_user' => $row['call_user'],
                    'result' => $row['result'],
                    'comment' => $row['comment'],
                );

                // Remove the calls data from the row
                unset($row['call_date'], $row['call_time'], $row['call_user'], $row['result'], $row['comment']);

                // Check the user's pre-calculated permissions
                if ($userPermissions['carriers'][$row['carrier']] && $userPermissions['clients'][$row['client']]) {
                    // Assign the remaining data (the existing structure) to $data['open'][$homeid]
                    $data['open'][$homeid] = $row;

                    // Now add the call data as a new subarray inside $data['open'][$homeid]
                    $data['open'][$homeid]['calls'] = $call_data;

                    if (isset($activeCities[$row['city']]['total']))
                        $activeCities[$row['city']]['total']++;
                    else
                        $activeCities[$row['city']]['total'] = 1;

                    if ($row['scan4_status'] === 'OPEN') {
                        $_open[$indexOpen] = $row;
                        $_open[$indexOpen]['calls'] = $call_data;
                        $indexOpen++;
                    }

                    if ($row['scan4_status'] === 'PENDING') {
                        $_pending[$indexPending] = $row;
                        $_pending[$indexPending]['calls'] = $call_data;
                        $indexPending++;
                    }
                    if ($row['scan4_status'] === 'OVERDUE') {
                        $_overdue[$indexOverdue] = $row;
                        $_overdue[$indexOverdue]['calls'] = $call_data;
                        $indexOverdue++;
                    }

                    if ($row['scan4_status'] === 'DONE') {
                        $_done[$indexDone] = $row;
                        $_done[$indexDone]['calls'] = $call_data;
                        $indexDone++;
                    }
                    if ($row['scan4_status'] === 'STOPPED') {
                        $_stopped[$indexStopped] = $row;
                        $_stopped[$indexStopped]['calls'] = $call_data;
                        $indexStopped++;
                    }
                }
            }
            $result->free_result();
        }
    }


    // fetch all hbgs and extend the data from homes
    $query = "
    SELECT 
    hbg.homeid, hbg.date, hbg.time, hbg.hausbegeher, hbg.uid, sh.lat, sh.lon, sh.client, sh.carrier, sh.dpnumber, sh.city,
    sh.street, sh.streetnumber, sh.streetnumberadd, sh.firstname, sh.lastname, sh.phone1, sh.phone2, sh.phone3, sh.scan4_status
FROM scan4_hbg AS hbg
INNER JOIN scan4_homes AS sh ON hbg.homeid = sh.homeid
INNER JOIN (
    SELECT homeid, MAX(date) AS max_date
    FROM scan4_hbg
    WHERE date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 50 DAY)
    AND status = 'PLANNED'
    GROUP BY homeid
) AS max_hbg ON hbg.homeid = max_hbg.homeid AND hbg.date = max_hbg.max_date
WHERE hbg.status = 'PLANNED';
    ";


    if ($result = $conn->query($query)) {
        $index = 0;
        while ($row = $result->fetch_assoc()) {
            if ($userPermissions['carriers'][$row['carrier']] && $userPermissions['clients'][$row['client']]) {
                $homeid = $row['homeid'];
                $data['planned'][$homeid] = $row;

                $_planned[$index] = $row;
                $index++;
            }
        }
        $result->free_result();
    }

    // fetch all calendar events to display events on the map. Calculate the current monday in sql query
    $query = "SELECT *
    FROM `calendar_events`
    WHERE start_time >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
    ORDER BY start_time ASC;
    ";
    if ($result = $conn->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $user = $row['user_name'];
            $calendar[$user][] = $row;
        }
        $result->free_result();
    }

    // fetch all pegmenData to display markers on the map. Calculate the current monday in sql query
    $query = "SELECT *
        FROM `calendar_events`
        WHERE start_time >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
        AND title = 'MapMarker'
        ORDER BY start_time ASC;
        ";
    if ($result = $conn->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $pegmenData[] = $row;
        }
        $result->free_result();
    }

    $conn->close();

    $endTime = microtime(true);
    $executionTime = $endTime - $startTime;
    //echo "Execution time: " . $executionTime . " seconds";

    return array(
        'homeid_array' => $data,
        'projects_array' => $activeCities,
        'calendar_array' => $calendar,
        'pegmenData_array' => $pegmenData,
        '_open' => $_open,
        '_pending' => $_pending,
        '_planned' => $_planned,
        '_overdue' => $_overdue,
        '_done' => $_done,
        '_stopped' => $_stopped
    );
}


function changeStatus()
{
    global $currentuser;
    $conn = dbconnect();

    // Assuming $currentuser is available. If not, you need to define it before using it.
    $currentuser = $_SESSION['currentuser'];

    $jsonString = $_POST['data'];
    $data = json_decode($jsonString, true);

    $homeid = $data['homeid'];
    $level2 = $data['level2']['value'];
    $level3 = strtoupper($data['level3']['value']); // Convert level3 to uppercase

    if ($level2 == "scan4") {
        $column = "scan4_status";
        $fields = [
            'status_scan4' => $level3,
        ];
    } elseif ($level2 == "carrier") {
        $column = "hbg_status";
        $fields = [
            'hbg_status' => $level3,  // Assuming the field key should be 'status_hbg' for hbg status
        ];
    } else {
        echo "Invalid level2 value";
        return; // exit the function early
    }
    $updatedFields = homeshistory($conn, $currentuser, $homeid, $fields);

    $updateStmt = $conn->prepare("UPDATE `scan4_homes` SET `$column` = ? WHERE `homeid` = ?");
    $updateStmt->bind_param("ss", $level3, $homeid);

    if ($updateStmt->execute()) {
        echo "Record updated successfully";
        $userlog['source'] = 'map';
        $userlog['homeid'] = $homeid;
        $userlog['action1'] = 'CONFIG changed col';
        $userlog['action2'] = "$column set to $level3";
        $userlog['action3'] = json_encode($updatedFields);
        saveUserLog($userlog); // save the actions to the userlog
    } else {
        echo "Error updating record: " . $updateStmt->error;
    }

    $updateStmt->close();
    $conn->close();
}



function homeshistory($conn, $currentuser, $homeid, $fields)
{
    // Fetch current data for the homeid
    $stmt = $conn->prepare("SELECT * FROM scan4_homes WHERE homeid = ?");
    $stmt->bind_param('s', $homeid);
    $stmt->execute();
    $result = $stmt->get_result();
    $currentData = $result->fetch_assoc();

    // Prepare the insert statement outside the loop
    $insertStmt = $conn->prepare("INSERT INTO scan4_homes_history(homeid, col1, col2, col3, col4, col5) VALUES (?, 'import', ?, ?, ?, ?)");

    $updatedFields = []; // Array to store information on updated fields

    foreach ($fields as $key => $new) {
        if ($currentData[$key] != $new) {
            $insertStmt->bind_param('sssss', $homeid, $currentuser, $key, $currentData[$key], $new);
            $insertStmt->execute();

            // Store information on the updated field
            $updatedFields[] = [
                'column' => $key,
                'old_value' => $currentData[$key],
                'new_value' => $new
            ];
        }
    }

    $stmt->close();
    $insertStmt->close();

    return $updatedFields; // Return the array with information on updated fields
}




function downloadCSV($jsonString)
{
    $data = json_decode($jsonString, true);

    $conn = dbconnect();

    $homeIds = implode(',', array_map(function ($id) use ($conn) {
        return "'" . $conn->real_escape_string($id) . "'"; // Escape and quote each string ID
    }, $data));

    $sql = "SELECT client, carrier, street, streetnumber, streetnumberadd, unit, city, plz, dpnumber, homeid, adressid, firstname, lastname,
    phone1, phone2, phone3, phone4, email, isporder, hbg_status, hbg_plandate, hbg_date, priority, scan4_comment, scan4_status, scan4_hbgdate,
    anruf1, anruf2, anruf3, anruf4, anruf5, briefkasten, emailsend, scan4_added, system_status,
    owner_name, owner_phone1, owner_phone2, owner_mail, scan4_phone1, scan4_phone2 FROM scan4_homes WHERE homeid IN ($homeIds)";
    $result = $conn->query($sql);


    // If there's an error in the query, handle it (for example, by returning or echoing an error message).
    if (!$result) {
        die("Database query error: " . $conn->error);
    }

    // Construct the CSV content
    $csvContent = "\xEF\xBB\xBF"; // UTF-8 BOM
    $headers = [];
    while ($row = $result->fetch_assoc()) {
        if (empty($headers)) {
            $headers = array_keys($row);
            $csvContent .= implode(";", $headers) . "\n";
        }
        $csvContent .= implode(";", array_map("escapeCsvValue", $row)) . "\n";
    }
    $conn->close();


    $fileName = "homeids_" . time() . ".csv";
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/tempfiles/' . $fileName;
    file_put_contents($fullPath, $csvContent);
    echo "/uploads/tempfiles/" . $fileName;
}

function escapeCsvValue($value)
{
    return '"' . str_replace('"', '""', $value) . '"';
}



function loadTicket($ticketID)
{
    $conn = dbconnect();
    $selectStmt = $conn->prepare("SELECT * FROM `scan4_tickets` WHERE id = ?");
    $selectStmt->bind_param("s", $ticketID);
    $selectStmt->execute();
    $result = $selectStmt->get_result();
    $ticketData = $result->fetch_assoc();
    $conn->close();

    echo json_encode($ticketData);
}


function fetchTickets()
{
    $conn = dbconnect();
    $query = "SELECT ticket.*, scan4_homes.city, scan4_homes.lat, scan4_homes.lon
    FROM ticket 
    INNER JOIN scan4_homes ON ticket.homeid = scan4_homes.homeid 
    WHERE ticket.ticket_status = 'new' AND LENGTH(ticket_finaldescription) > 2
    ORDER BY ticket.ticket_id DESC";

    $tickets = array();

    if ($result = $conn->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $tickets[] = $row;
        }

        $result->free_result();
    }
    $conn->close();

    echo json_encode($tickets);
}


function fetchHomeid($homeid)
{

    $userPermissions = getAllPermissions();

    $conn = dbconnect();
    $query = "
    SELECT 
    h.homeid, h.lat, h.lon, h.scan4_status, h.city, h.street, h.streetnumber, h.streetnumberadd, h.dpnumber, h.carrier, h.client, 
    h.firstname, h.lastname, h.phone1, h.phone2, h.phone3, h.priority, 
    c.call_date, c.call_time, c.call_user, c.result, c.comment,
    hbg.date, hbg.time, hbg.hausbegeher, hbg.uid
FROM scan4_homes h
LEFT JOIN (
    SELECT homeid, MAX(id) as max_id
    FROM scan4_calls
    GROUP BY homeid
) subq ON h.homeid = subq.homeid
LEFT JOIN scan4_calls c ON subq.max_id = c.id
LEFT JOIN scan4_hbg hbg ON h.homeid = hbg.homeid AND hbg.status = 'PLANNED'
WHERE h.homeid = '$homeid';
";


    if ($result = $conn->query($query)) {
        $row = $result->fetch_assoc();

        if ($row) {
            $homeid = $row['homeid'];
            // Extract calls data into a separate array
            $call_data = array(
                'call_date' => $row['call_date'],
                'call_time' => $row['call_time'],
                'call_user' => $row['call_user'],
                'result' => $row['result'],
                'comment' => $row['comment'],
            );

            // Remove the calls data from the row
            unset($row['call_date'], $row['call_time'], $row['call_user'], $row['result'], $row['comment']);

            // Check the user's pre-calculated permissions
            if ($userPermissions['carriers'][$row['carrier']] && $userPermissions['clients'][$row['client']]) {
                $_data[0] = $row;
                $_data[0]['calls'] = $call_data;
                echo json_encode($_data);
            }
        }

        $result->free_result();
    }
    $conn->close();
}



function cancel_appointment($jsonString)
{
    global $currentuser;

    $data = json_decode($jsonString, true);
    $data = $data['eventData']; // Access nested data

    $uid = $data['uid'] ?? null;
    $comment = $data['comment'] ?? null;
    $reason = $data['reason'] ?? null;
    $homeid = $data['homeid'] ?? null;



    $conn = dbconnect();

    $query = "SELECT `status` FROM `scan4_hbg` WHERE uid = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("s", $uid);
        $stmt->execute();
        $stmt->bind_result($status);
        $stmt->fetch();
        $stmt->close();
    }
    echo "the status is $status";

    if ($status == "PLANNED") {
        $query = "UPDATE `scan4_hbg` SET `status` = 'STORNO', cancel_reason = ?, cancel_comment = ? WHERE uid = ?";
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("sss", $reason, $comment, $uid);
            if ($stmt->execute()) {
                echo "Update executed successfully.";

                // Insert into scan4_calls table
                $insertQuery = "INSERT INTO `scan4_calls` (`call_date`, `call_time`, `call_user`, `homeid`, `result`,`reason`, `comment`, `callid`)
                            VALUES (CURDATE(), CURTIME(), ?, ?, 'HBG storniert', ?, ?, ?)";
                if ($insertStmt = $conn->prepare($insertQuery)) {
                    $insertStmt->bind_param("sssss", $currentuser, $homeid, $reason, $comment, $uid);
                    if ($insertStmt->execute()) {
                        echo "Insert executed successfully.";
                    } else {
                        echo "Failed to execute insert statement. {ErrorCode:CA2055}";
                    }
                    $insertStmt->close();
                } else {
                    echo "Error preparing insert statement. {ErrorCode:CA2037}";
                }

                // start deleting record in calendar_events
                $deleteQuery = "DELETE FROM `calendar_events` WHERE uid = ?";
                if ($deleteStmt = $conn->prepare($deleteQuery)) {
                    $deleteStmt->bind_param("s", $uid);
                    if ($deleteStmt->execute()) {
                        echo "Delete executed successfully.";
                        // start updating scan4_homes table
                        $updateHomeQuery = "UPDATE `scan4_homes` SET `scan4_status` = 'OPEN' WHERE homeid = ?";
                        if ($updateHomeStmt = $conn->prepare($updateHomeQuery)) {
                            $updateHomeStmt->bind_param("s", $homeid);
                            if ($updateHomeStmt->execute()) {
                                echo "Update home status executed successfully.";
                                $userlog['source'] = 'map';
                                $userlog['homeid'] = $homeid;
                                $userlog['action1'] = 'changed scan4_status';
                                $userlog['action2'] = 'from ' . $status;
                                $userlog['action3'] = 'to OPEN';
                                saveUserLog($userlog); // save the actions to the userlog
                            } else {
                                echo "Failed to execute update home status statement. {ErrorCode:CA2041}";
                            }
                            $updateHomeStmt->close();
                        } else {
                            echo "Error preparing update home status statement. {ErrorCode:CA2075}";
                        }
                        // end updating scan4_homes table

                    } else {
                        echo "Failed to execute delete statement.";
                    }
                    $deleteStmt->close();
                } else {
                    echo "Error preparing delete statement. {ErrorCode:CA2071}";
                }
                // end deleting record in calendar_events


            } else {
                echo "Failed to execute update statement. {ErrorCode:CA2052}";
            }
            $stmt->close();
        } else {
            echo "Error preparing update statement. {ErrorCode:CA2033}";
        }
    } else {
        echo "FAILED! Status is not equal to 'PLANNED'. Update skipped. {ErrorCode:CA1045}";
        return false;
    }
    $conn->close();


    $userlog['source'] = 'map';
    $userlog['homeid'] = $homeid;
    $userlog['action1'] = 'storno an appointment';
    $userlog['action2'] = $uid;
    $userlog['action3'] = $reason;
    $userlog['action4'] = $comment;
    saveUserLog($userlog); // save the actions to the userlog

    nextcloud_delete2($uid);
}



function delete_calEvent($eventid)
{
    $conn = dbconnect();

    // Fetch the row before deleting
    $selectStmt = $conn->prepare("SELECT * FROM `calendar_events` WHERE event_id = ?");
    $selectStmt->bind_param("s", $eventid);
    $selectStmt->execute();
    $result = $selectStmt->get_result();
    $row = $result->fetch_assoc();
    $jsonOldEvent = json_encode($row);

    // Delete the row
    $deleteStmt = $conn->prepare("DELETE FROM `calendar_events` WHERE event_id = ?");
    $deleteStmt->bind_param("s", $eventid);
    $deleteStmt->execute();

    $selectStmt->close();
    $deleteStmt->close();
    $conn->close();


    $userlog['source'] = 'map';
    $userlog['homeid'] = $row['homeid'];
    $userlog['action1'] = 'deleted an calendar event';
    $userlog['action2'] = 'eventid ' . $eventid;
    $userlog['action3'] = $jsonOldEvent;

    saveUserLog($userlog); // save the actions to the userlog
}


function save_calEvent($jsonString)
{
    $data = json_decode($jsonString, true);
    $data = $data['result']; // Access nested data

    $username = $data['username'] ?? null;
    $date = $data['date'] ?? null;
    $fromInput = $data['fromInput'] ?? null; // start_time
    $toInput = $data['toInput'] ?? null; // end_time
    $title = $data['eventName'] ?? null;
    $isUnique = $data['isUnique'] ?? false;
    $customField = $data['customField'] ?? null;
    $eventID = uniqid();

    if ($customField !== null) $title = $customField; // overwrite title with userinput

    if ($username === null || $title === null || $date === null) {
        echo 'Username, EventName or Date is missing. Aborting script.';
        return false;
    }

    // append time to date
    $start_time = $date . ' ' . $fromInput . ':00'; // Format 'Y-m-d H:i:s'
    $end_time = $date . ' ' . $toInput . ':00'; // Format 'Y-m-d H:i:s'

    $conn = dbconnect();

    // Delete existing unique event for that user on that date if isUnique is true
    if ($isUnique) {
        $stmt = $conn->prepare("DELETE FROM `calendar_events` WHERE DATE(start_time) = ? AND user_name = ? AND title = ?");
        $stmt->bind_param("sss", $date, $username, $title);
        $stmt->execute();
    }

    // Delete overlapping events with specific titles for that user regardless of isUnique
    $stmt = $conn->prepare("DELETE FROM `calendar_events` WHERE user_name = ? AND title IN ('Arbeitsbeginn', 'Arbeitsende', 'Mittagspause', 'Krank', 'Urlaub') AND (start_time < ? AND end_time > ?)");
    $stmt->bind_param("sss", $username, $end_time, $start_time);
    $stmt->execute();

    // Insert the new event
    $stmt = $conn->prepare("INSERT INTO `calendar_events` (title, start_time, end_time, user_name, created, event_id) VALUES (?, ?, ?, ?, NOW(), ?)");
    $stmt->bind_param("sssss", $title, $start_time, $end_time, $username, $eventID);


    if ($stmt->execute()) {
        echo 'Data inserted successfully';
    } else {
        echo 'Error occurred while inserting data';
    }

    $stmt->close();
    $conn->close();
}




function save_arbeitszeit($jsonString)
{
    $data = json_decode($jsonString, true);
    $data = $data['result']; // Access nested data

    $username = $data['username'] ?? null;
    $date = $data['date'] ?? null;

    if ($username === null) {
        echo 'Username is missing. Aborting script.';
        return false;
    }

    $conn = dbconnect();

    // Define the event details
    $events = [
        [
            'title' => 'Arbeitsbeginn',
            'startTime' => date('H:i', strtotime($data['fromInput'] . '-1 hour')),
            'endTime' => $data['fromInput'],
        ],
        [
            'title' => 'Arbeitsende',
            'startTime' => $data['toInput'],
            'endTime' => date('H:i', strtotime($data['toInput'] . '+1 hour'))
        ]
    ];

    if (isset($data['breakTime']) && $data['breakTime'] > 0) {
        $events[] = [
            'title' => 'Mittagspause',
            'startTime' => $data['breakSlot'],
            'endTime' => date('H:i', strtotime($data['breakSlot'] . '+1 hour'))
        ];
    }

    // Delete events for the specific date
    $stmtDelete = $conn->prepare("DELETE FROM `calendar_events` WHERE user_name = ? AND DATE(start_time) = ? AND title IN ('Arbeitsbeginn', 'Arbeitsende', 'Mittagspause')");
    $stmtDelete->bind_param("ss", $username, $date);
    $stmtDelete->execute();
    $stmtDelete->close();

    // Insert events for the specific date
    foreach ($events as $event) {
        $startTime = $date . ' ' . $event['startTime'];
        $endTime = $date . ' ' . $event['endTime'];
        $eventID = uniqid();

        $stmt = $conn->prepare("INSERT INTO `calendar_events` (title, start_time, end_time, user_name, created, event_id) VALUES (?, ?, ?, ?, NOW(), ?)");
        $stmt->bind_param("sssss", $event['title'], $startTime, $endTime, $username, $eventID);

        if ($stmt->execute()) {
            echo $event['title'] . ' saved successfully.<br>';
        } else {
            echo 'Failed to save ' . $event['title'] . '.<br>';
        }
        $stmt->close();
    }

    // Check if eventRepeat is set to "weekly" or "daily"
    if (isset($data['eventRepeat']) && ($data['eventRepeat'] === 'weekly' || $data['eventRepeat'] === 'daily')) {
        $endOfYear = date('Y-12-31');
        $currentDate = $date; // Start from the selected date
        $dayOfTheWeek = date('N', strtotime($date)); // Day of the week for the initial date (for weekly repeat)

        while ($currentDate <= $endOfYear) {
            $dayOfWeek = date('N', strtotime($currentDate));

            // Check for weekly repeat and if the day matches the initial day of the week
            if ($data['eventRepeat'] === 'weekly' && $dayOfWeek != $dayOfTheWeek) {
                $currentDate = date('Y-m-d', strtotime($currentDate . '+1 day'));
                continue;
            }

            // Skip weekends for daily repeat
            if ($data['eventRepeat'] === 'daily' && $dayOfWeek >= 6) {
                $currentDate = date('Y-m-d', strtotime($currentDate . '+1 day'));
                continue;
            }

            // Delete events for the current date
            $stmtDelete = $conn->prepare("DELETE FROM `calendar_events` WHERE user_name = ? AND DATE(start_time) = ? AND title IN ('Arbeitsbeginn', 'Arbeitsende', 'Mittagspause')");
            $stmtDelete->bind_param("ss", $username, $currentDate);
            $stmtDelete->execute();
            $stmtDelete->close();

            // Insert events for the current date
            foreach ($events as $event) {
                $startTime = $currentDate . ' ' . $event['startTime'];
                $endTime = $currentDate . ' ' . $event['endTime'];
                $eventID = uniqid();

                $stmt = $conn->prepare("INSERT INTO `calendar_events` (title, start_time, end_time, user_name, created, event_id) VALUES (?, ?, ?, ?, NOW(), ?)");
                $stmt->bind_param("sssss", $event['title'], $startTime, $endTime, $username, $eventID);

                if ($stmt->execute()) {
                    echo $event['title'] . ' saved successfully on ' . $currentDate . '.<br>';
                } else {
                    echo 'Failed to save ' . $event['title'] . ' on ' . $currentDate . '.<br>';
                }
                $stmt->close();
            }

            // Increment date by 7 days for weekly, 1 day for daily
            $repeatInterval = ($data['eventRepeat'] === 'weekly') ? '+7 days' : '+1 day';
            $currentDate = date('Y-m-d', strtotime($currentDate . $repeatInterval));
        }
    }
    $conn->close();
    // Check if any errors occurred during the insert operations
    if ($conn->errno !== 0) {
        echo 'An error occurred while saving Arbeitszeit: ' . $conn->error . '<br>';
        return false;
    }

    echo 'Arbeitszeit saved successfully.<br>';

    // Add a return statement if needed
    return true;
}



function saveEventforUser($jsonString)
{
    global $currentuser;

    $data = json_decode($jsonString, true);
    $data = $data['eventData']; // direclty access the array
    $username = isset($data['username']) ? $data['username'] : null;
    $eventDate = isset($data['date']) ? $data['date'] : null;
    $eventStartTime = isset($data['start_time']) ? $data['start_time'] : null;
    $eventEndTime = isset($data['end_time']) ? $data['end_time'] : null;
    $prevDrivingTime = isset($data['prevDrivingTime']) ? $data['prevDrivingTime'] : null;
    $nextDrivingTime = isset($data['nextDrivingTime']) ? $data['nextDrivingTime'] : null;
    $homeid = isset($data['homeid']) ? $data['homeid'] : null;
    $eventComment = isset($data['eventComment']) ? $data['eventComment'] : null;
    $isMoved = isset($data['isMoved']) ? $data['isMoved'] : null;

    $userlog['source'] = 'map';
    $userlog['homeid'] = $homeid;
    $userlog['action1'] = 'saveEventforUser';
    $userlog['action2'] = $username;
    $userlog['action3'] = json_encode($data);
    saveUserLog($userlog); // save the actions to the userlog

    $eventStart = "$eventDate $eventStartTime:00";
    $eventEnd = "$eventDate $eventEndTime:00";


    $today = date('Y-m-d');

    $existingEntry = false;

    $conn = dbconnect();
    $query = "SELECT * FROM `calendar_events` WHERE user_name = ? AND start_time = ? LIMIT 1";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("ss", $username, $eventStart);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $existingEntry = true;
        }

        $stmt->close();
    }

    if ($existingEntry) {
        echo "An entry already exists for $username at $eventStart.";
        // stop the script or perform any necessary actions
        exit();
    } else {
        echo "No entry exists for $username at $eventStart. Create a new event now";
    }

    //---------------------------------------------------------------------------
    // delete the old event before creating a new one
    if ($isMoved) {
        $query = "SELECT * FROM `scan4_hbg` WHERE homeid = ?  ORDER BY id DESC LIMIT 1";
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("s", $homeid);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $uid = $row['uid'];

                // set the HBG status to MOVED
                $updateQuery = "UPDATE `scan4_hbg` SET status = 'MOVED' WHERE uid = ?";
                if ($updateStmt = $conn->prepare($updateQuery)) {
                    $updateStmt->bind_param("s", $uid);
                    $updateStmt->execute();
                    if ($updateStmt->affected_rows > 0) {
                        echo "Row updated successfully!";
                        $userlog['source'] = 'map';
                        $userlog['homeid'] = $homeid;
                        $userlog['action1'] = 'isMoved updating updated';
                        $userlog['action2'] = 'uid ' . $uid;
                        saveUserLog($userlog); // save the actions to the userlog
                    } else {
                        echo "Error updating row.";
                        $userlog['source'] = 'map';
                        $userlog['homeid'] = $homeid;
                        $userlog['action1'] = 'isMoved updating failed e:2';
                        $userlog['action2'] = 'uid ' . $uid;
                        saveUserLog($userlog); // save the actions to the userlog
                    }

                    $updateStmt->close();
                } else {
                    $userlog['source'] = 'map';
                    $userlog['homeid'] = $homeid;
                    $userlog['action1'] = 'isMoved updating failed e:1';
                    $userlog['action2'] = 'uid ' . $uid;
                    saveUserLog($userlog); // save the actions to the userlog
                }

                // fetch now the event_id to delete calendar and nextcloud event together to prevent issues
                $query = "SELECT * FROM `calendar_events` WHERE uid = ? ORDER BY id DESC LIMIT 1";
                if ($stmt = $conn->prepare($query)) {
                    $stmt->bind_param("s", $uid);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($row = $result->fetch_assoc()) {
                        $eventID = $row['event_id'];
                        $jsonOldEvent = json_encode($row);

                        $userlog['source'] = 'map';
                        $userlog['homeid'] = $homeid;
                        $userlog['action1'] = 'moved an hbg';
                        $userlog['action2'] = $jsonOldEvent;
                        saveUserLog($userlog); // save the actions to the userlog

                        delete_calEvent($eventID);
                        nextcloud_delete2($uid);
                    } else {
                        echo "error finding calendar eventid. {ErrorCode:MVA2063}";
                        exit();
                    }

                    $stmt->close();
                }
            } else {
                echo "error finding hbg uid. {ErrorCode:MVA1015}";
                exit();
            }

            $stmt->close();
        }
    }


    $query = "SELECT * FROM `scan4_homes` WHERE homeid = ? LIMIT 1";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("s", $homeid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $customer_data = $row;
        }

        $stmt->close();
    }


    $customer_name = $customer_data['lastname'] . ', ' . $customer_data['firstname'];
    $anrufIndex = 1;
    for ($i = 1; $i <= 5; $i++) {
        $columnName = 'anruf' . $i;
        if (!is_null($customer_data[$columnName])) {
            $anrufIndex = $i;
        }
    }
    $tstamp = gmdate("Ymd\THis\Z");
    $uid = $tstamp . '-' . $homeid . '-RN' . rand(10000, 99999);
    $eventID = uniqid();

    if (strlen($eventComment) > 1) {
        $title = $customer_data['lastname'] . ' !Notiz [CRM]';
    } else {
        $title = $customer_data['lastname'] . ' [CRM]';
    }


    // ----------------------------------------------------
    // check for possible ticket
    $query = "SELECT * FROM `scan4_tickets` WHERE homeid = ? AND (status = 'new' OR status = 'pending') ORDER BY id DESC LIMIT 1";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("s", $homeid);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $ticket = $row;
            $query = "UPDATE `scan4_tickets` SET `status` = 'pending' WHERE id = ?";
            if ($stmt = $conn->prepare($query)) {
                $stmt->bind_param("s", $ticket['id']);
                $stmt->execute();
                $stmt->close();

                $userlog['source'] = 'map';
                $userlog['homeid'] = $homeid;
                $userlog['action1'] = 'ticked set to pending';
                $userlog['action2'] = 'set by system';
                saveUserLog($userlog); // save the actions to the userlog
            }
        }
    }
    $ticketID = isset($ticket['id']) ? $ticket['id'] : null;
    if ($ticketID) {
        $description = '⚠ Ticket ⚠ \nNotiz: ' . $eventComment . '\nName: ' . $customer_name . '\nTel.: +49' . $customer_data['phone1'] . '\nTel.: +49' . $customer_data['phone2']
            . '\nUnit: ' . $customer_data['unit'] . '\nHomeID: ' . $homeid . '\nClient: ' . $customer_data['client'] . '\nErstellt am: ' . date('d.m.y H:i')
            . 'Uhr \nErstellt von: ' . $currentuser . '\nAnruf: #' . $anrufIndex . '\nUID:' . $uid;
    } else {
        $description = 'Notiz: ' . $eventComment . '\nName: ' . $customer_name . '\nTel.: +49' . $customer_data['phone1'] . '\nTel.: +49' . $customer_data['phone2']
            . '\nUnit: ' . $customer_data['unit'] . '\nHomeID: ' . $homeid . '\nClient: ' . $customer_data['client'] . '\nErstellt am: ' . date('d.m.y H:i')
            . 'Uhr \nErstellt von: ' . $currentuser . '\nAnruf: #' . $anrufIndex . '\nUID:' . $uid;
    }


    $customer_city = str_replace(['MDU', 'W2', 'W3', 'W4'], '', $customer_data['city']);
    $eventLocation = $customer_data['street'] . ' ' . $customer_data['streetnumber'] . $customer_data['streetnumberadd'] . ', ' . $customer_data['plz'] . ' ' . $customer_city . ', Deutschland'; // used for intern cal
    $location = $customer_data['street'] . " " . $customer_data['streetnumber'] . " " . $customer_data['streetnumberadd'] . '\, ' . $customer_data['plz'] . ' ' . $customer_city . '\, Deutschland'; // used for iPhone 

    // -------------------------------------------------------------------------------------------------------------------
    // create calendar event and log
    calendar_create($title, $eventStart, $eventEnd, $description, $eventLocation, $homeid, $uid, $username, $eventID, $customer_data['lat'], $customer_data['lon']);

    $userlog['source'] = 'map';
    $userlog['homeid'] = $homeid;
    $userlog['action1'] = 'created an calendar event';
    $userlog['action2'] = 'eventid ' . $eventID;
    $userlog['action3'] = $jsonString;
    saveUserLog($userlog); // save the actions to the userlog

    // --------------------------------------------------------------------------------------------------------------------
    // update homes table call column only when the appointment is not moved
    if (!$isMoved) {
        $alreadyUpdatedToday = false;
        $oldStatus = $customer_data['scan4_status']; //Store old status

        for ($i = 1; $i <= 5; $i++) {
            $anrufField = "anruf$i";
            if ($customer_data[$anrufField] === $today) {
                $alreadyUpdatedToday = true;
                break;
            }
        }

        if (!$alreadyUpdatedToday) {
            for ($i = 1; $i <= 5; $i++) {
                $anrufField = "anruf$i";
                if (empty($customer_data[$anrufField])) {
                    $query = "UPDATE `scan4_homes` SET `$anrufField` = ? WHERE homeid = ?";
                    if ($stmt = $conn->prepare($query)) {
                        $stmt->bind_param("ss", $today, $homeid);
                        $stmt->execute();
                        $stmt->close();
                    }
                    break;
                }
            }
        }

        $userlog['source'] = 'map';
        $userlog['homeid'] = $homeid;
        $userlog['action1'] = 'changed scan4_status';
        $userlog['action2'] = 'from ' . $customer_data['scan4_status'];
        $userlog['action3'] = 'to PLANNED';
        saveUserLog($userlog); // save the actions to the userlog
    }

    // Set status to 'PLANNED' and HBG date
    $query = "UPDATE `scan4_homes` SET `scan4_status` = 'PLANNED', `scan4_comment` = ?, `scan4_hbgdate`= ? WHERE homeid = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("sss", $eventComment, $eventDate, $homeid);
        if ($stmt->execute()) {
            error_log("scan4_homes update successful for homeid: $homeid");
        } else {
            error_log("scan4_homes update failed for homeid: $homeid - Error: " . $stmt->error);
        }
        $stmt->close();
    } else {
        error_log("Failed to prepare statement for scan4_homes update - Error: " . $conn->error);
    }
    // --------------------------------------------------------------------------------------------------------------------
    // create hbg event
    $query = "INSERT INTO `scan4_hbg` (`date`, `time`, `homeid`, `hausbegeher`, `comment`, `status`, `username`, `created`, `ident`, `uid`)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    echo $query . "<br>";  // Output the query for debugging purposes
    if ($stmt = $conn->prepare($query)) {
        $status = "PLANNED";  // Assign the status value to a variable
        $stmt->bind_param("ssssssssss", $eventDate, $eventStartTime, $homeid, $username, $eventComment, $status, $currentuser, date('Y-m-d H:i:s'), $eventID, $uid);
        if ($stmt->execute()) {
            error_log("scan4_hbg insertion successful for eventID: $eventID");
            echo "scan4_hbg Insertion successful.";
        } else {
            error_log("scan4_hbg insertion failed for eventID: $eventID - Error: " . $stmt->error);
            echo "scan4_hbg Insertion failed.";
            echo "<br>Error: " . $stmt->error;  // Output the error message for debugging purposes
        }
        $stmt->close();
    } else {
        error_log("Failed to prepare statement for scan4_hbg insertion - Error: " . $conn->error);
        echo "Prepare statement failed.";
        echo "<br>Error: " . $conn->error;  // Output the error message for debugging purposes
    }

    $userlog['source'] = 'map';
    $userlog['homeid'] = $homeid;
    $userlog['action1'] = 'created an hbg';
    $userlog['action2'] = $username . ' ' . $eventDate . ' ' . $eventStartTime;
    $userlog['action3'] = $jsonString;
    if ($isMoved) {
        $userlog['action4'] = 'this is moved';
    }
    saveUserLog($userlog); // save the actions to the userlog
    // --------------------------------------------------------------------------------------------------------------------
    // create a call
    if ($isMoved) {
        $query = "INSERT INTO `scan4_calls` (`call_date`, `call_time`, `call_user`, `homeid`, `result`, `comment`, `callid`)
          VALUES (CURDATE(), CURTIME(), ?, ?, 'HBG verschoben', ?, ?)";
    } else {
        $query = "INSERT INTO `scan4_calls` (`call_date`, `call_time`, `call_user`, `homeid`, `result`, `comment`, `callid`)
        VALUES (CURDATE(), CURTIME(), ?, ?, 'HBG erstellt', ?, ?)";
    }
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("ssss", $currentuser, $homeid, $eventComment, $eventID);
        if ($stmt->execute()) {
            error_log("scan4_calls insertion successful for homeid: $homeid");
        } else {
            error_log("scan4_calls insertion failed for homeid: $homeid - Error: " . $stmt->error);
        }
        $stmt->close();
    } else {
        error_log("Failed to prepare statement for scan4_calls insertion - Error: " . $conn->error);
    }
    $conn->close();



    $eventData['homeid'] = $homeid;
    $eventData['username'] = $username;
    $eventData['eventDate'] = $eventDate;
    $eventData['eventStartTime'] = $eventStartTime;
    $eventData['eventEndTime'] = $eventEndTime;
    $eventData['prevDrivingTime'] = $prevDrivingTime;
    $eventData['nextDrivingTime'] = $nextDrivingTime;
    $eventData['eventComment'] = $eventComment;
    $eventData['eventLocation'] = $location;
    $eventData['anrufIndex'] = $anrufIndex;
    $eventData['uid'] = $uid;
    $eventData['description'] = $description;
    $eventData['eventTitle'] = $title;
    $eventData['eventDurration'] = '30';
    $eventData['customerData'] = $customer_data; // bind the full scan4_homes row here


    nextcloud_create($eventData);
}





function nextcloud_create($eventData)
{

    global $currentuser;


    $tstamp = gmdate("Ymd\THis"); // removed \Z

    $dateTime = $eventData['eventDate'] . ' ' . $eventData['eventStartTime'];
    $eventDateTime = new DateTime($dateTime);
    $tstart = $eventDateTime->format('Ymd\THis'); // removed \Z

    $eventDateTime->modify('+30 minutes');
    $tend = $eventDateTime->format('Ymd\THis'); // removed \Z

    // echo $formattedDateTime; // Output: 20230711T063000Z

    $data = fetchUserDetails('username', $eventData['username'], null);
    $userurl = $data->calendarhook;

    //$url = 'http://nextcloud.alphacc.de/remote.php/dav/calendars/bestadmin/benhbg/calc.ics';

    $headers = array('Content-Type: text/calendar', 'charset=utf-8');
    //$userpwd = 'bestadmin:mSMyCGIRTNPDjiqbJ5kt@HvK9BrsYzApW.2Z8lXEofV1UaOQ63';
    $userpwd = 'sys:smallusdickus';



    //$location = 'Bahnhofstraße 22\, 34281 Gudensberg\, Deutschland';
    $location = $eventData['eventLocation'];
    $description = $eventData['description'];
    $title = $eventData['eventTitle'];
    $uid = $eventData['uid'];

    $body = 'BEGIN:VCALENDAR
PRODID:CRMSCAN4_cURL_1
VERSION:2.0
CALSCALE:GREGORIAN
BEGIN:VEVENT
CREATED:' . $tstamp . '
DESCRIPTION:' . $description . '
DTEND;TZID=Europe/Berlin:' . $tend . '
DTSTAMP:' . $tstamp . '
DTSTART;TZID=Europe/Berlin:' . $tstart . '
LAST-MODIFIED:' . $tstamp . '
LOCATION:' . $location . '
SEQUENCE:2
STATUS:CONFIRMED
SUMMARY:' . $title . '
TRANSP:OPAQUE
UID:' . $uid . '
END:VEVENT
END:VCALENDAR';

    $url = 'https://cloud2.scan4.pro/remote.php/dav/calendars/sys/' . $userurl . '/' . $uid . '.ics';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, $userpwd);
    //curl_setopt($ch, CURLOPT_PUT, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

    //Execute the request.
    $response = curl_exec($ch);
    curl_close($ch);
    //$response = str_replace(['\r','\n'], '', $response);
    if (preg_match('/message>(.*?)message/', $response, $match) == 1) {
        $errorhandle = str_replace('</s:', '', $match[1]);
    }
    if ($response === '') {
        $response = 'created';
    }

    writetonextcloudlogfile("\n ------------------------------------ \n" . $response . "\n" . $body, 'create', $uid);

    $userlog['source'] = 'map';
    $userlog['homeid'] = $eventData['homeid'];
    $userlog['action1'] = 'nextcloud_create';
    $userlog['action2'] = $response;
    $userlog['action3'] = $userurl;
    $userlog['action4'] = json_encode($eventData, JSON_UNESCAPED_UNICODE);
    saveUserLog($userlog); // save the actions to the userlog

    return $response;
}



function nextcloud_delete2($uid)
{
    $conn = dbconnect();
    $query = "SELECT * FROM `scan4_hbg` WHERE uid = '$uid'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $conn->close();

    $user = $row['hausbegeher'];
    $homeid = $row['homeid'];
    $data = fetchUserDetails('username', $user, null);
    $userurl = $data->calendarhook;

    $headers = array('Content-Type: text/calendar', 'charset=utf-8');
    $userpwd = 'sys:smallusdickus';

    $url = 'https://cloud2.scan4.pro/remote.php/dav/calendars/sys/' . $userurl . '/' . $uid . '.ics';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, $userpwd);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
    echo '//remove ' . $uid . ' from ' . $userurl . '//';
    echo '//remove response: ' . $result . '//';

    writetonextcloudlogfile("\n ------------------------------------ \n" . $result . "\n", 'delete', $uid);

    if ($result === '') {
        $result = 'deleted';
    }

    $userlog['source'] = 'map';
    $userlog['homeid'] = $homeid;
    $userlog['action1'] = 'nextcloud_delete';
    $userlog['action2'] = $result;
    $userlog['action3'] = $userurl;
    saveUserLog($userlog); // save the actions to the userlog

    return $result;
}


function save_scan4phone($phonenumber, $homeid, $field)
{
    global $currentuser;

    $datetimestamp = date('Y-m-d H:i:s');

    $conn = dbconnect();

    // Extract the old value from the specified column
    $oldValueQuery = "SELECT $field FROM `scan4_homes` WHERE homeid = ?";
    if ($stmt = $conn->prepare($oldValueQuery)) {
        $stmt->bind_param("s", $homeid);
        if ($stmt->execute()) {
            $stmt->bind_result($oldValue);
            $stmt->fetch();
            $stmt->close();
        } else {
            echo "Error: " . $stmt->error;
            $conn->close();
            return;
        }
    } else {
        echo "Prepare statement error: " . $conn->error;
        $conn->close();
        return;
    }

    // Update the specified column with the new value
    $updateQuery = "UPDATE `scan4_homes` SET $field = ? WHERE homeid = ?";
    if ($stmt = $conn->prepare($updateQuery)) {
        $stmt->bind_param("ss", $phonenumber, $homeid);
        if ($stmt->execute()) {
            echo "Update successful!";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Prepare statement error: " . $conn->error;
    }


    $userlog['source'] = 'map';
    $userlog['homeid'] = $homeid;
    $userlog['action1'] = 'updated ' . $field;
    $userlog['action2'] = 'old value ' . $oldValue;
    $userlog['action3'] = 'new value ' . $phonenumber;
    saveUserLog($userlog); // save the actions to the userlog

    $conn->close();
}



function refresh_calendar()
{
    $conn = dbconnect();
    $query = "SELECT *
    FROM `calendar_events`
    WHERE start_time >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
    ORDER BY start_time ASC;
    ";
    if ($result = $conn->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $user = $row['user_name'];
            $calendar[$user][] = $row;
        }
        $result->free_result();
    }

    $conn->close();
    $json_calendar = json_encode($calendar);
    echo $json_calendar;
}




function save_pegman($data, $save)
{

    global $currentuser;
    $name = $data['name'];
    $week = $data['week'];
    $date = $data['date'];
    $day = $data['day'];
    $lat = $data['lat'];
    $lon = $data['lon'];

    $json_string = json_encode($data);
    $start_time = $date . ' 06:00:00';
    $end_time = $date . ' 06:30:00';

    $datetime = date('Y-m-d H:i:s');

    $conn = dbconnect();

    $select_query = "SELECT * FROM calendar_events WHERE user_name = '$name' AND start_time = '$start_time' AND title = 'MapMarker'";
    $result = $conn->query($select_query);

    $save = ($save === 'true' || $save === true) ? true : false;
    echo "Save value is: ", var_export($save, true), "\n";

    if ($result->num_rows > 0) {
        if ($save) { // this will be true if $save is true
            // update operation
            $update_query = "UPDATE calendar_events SET description = '$json_string', lat = '$lat', lon = '$lon', created = '$datetime', creator = '$currentuser', visible = '0' 
            WHERE user_name = '$name' AND start_time = '$start_time' AND title = 'MapMarker'";
            if ($conn->query($update_query) === TRUE) {
                echo "Row updated successfully.\n";
            } else {
                echo "Failed to update row: " . $conn->error;
            }
        } else {
            // delete operation
            $delete_query = "DELETE FROM calendar_events WHERE user_name = '$name' AND start_time = '$start_time' AND title = 'MapMarker'";
            if ($conn->query($delete_query) === TRUE) {
                echo "Row deleted successfully.\n";
            } else {
                echo "Failed to delete row: " . $conn->error;
            }
        }
    } else {
        if ($save) { // this will be true if $save is true
            // insert operation
            $insert_query = "INSERT INTO calendar_events (title, start_time, end_time, description, user_name, lat, lon, created, creator, visible) 
            VALUES ('MapMarker', '$start_time', '$end_time', '$json_string', '$name', '$lat', '$lon', '$datetime', '$currentuser', '0')";
            if ($conn->query($insert_query) === TRUE) {
                echo "Row inserted successfully.\n";
            } else {
                echo "Failed to insert row: " . $conn->error;
            }
        }
    }

    // Close the connection
    $conn->close();
}


function safe_nohbg($homeid, $comment, $reason)
{
    $conn = dbconnect();
    $today = date('Y-m-d');
    $time = date('H:i:s');
    global $currentuser;

    $query = "INSERT INTO `scan4_calls`(`call_date`, `call_time`, `call_user`, `homeid`, `result`, `comment`) 
    VALUES ('$today','$time','$currentuser','$homeid','$reason','$comment')";
    $conn->query($query);

    $userlog['source'] = 'map';
    $userlog['homeid'] = $homeid;
    $userlog['action1'] = 'safe call result';
    $userlog['action2'] = $reason;
    $userlog['action3'] = $comment;
    saveUserLog($userlog); // save the actions to the userlog

    $sc4Comment = "[$today:$reason::$comment]";

    $query = "SELECT * FROM `scan4_homes` WHERE homeid = '$homeid';";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();

    $alreadyUpdatedToday = false;

    for ($i = 1; $i <= 5; $i++) {
        $anrufField = "anruf$i";
        if ($row[$anrufField] === $today) {
            $alreadyUpdatedToday = true;
            break;
        }
    }

    if (!$alreadyUpdatedToday) {
        for ($i = 1; $i <= 5; $i++) {
            $anrufField = "anruf$i";
            if (empty($row[$anrufField])) {
                $query = "UPDATE `scan4_homes` SET `$anrufField` = '$today', scan4_comment = '$sc4Comment' WHERE homeid = '$homeid';";

                if ($i == 5) {
                    $query = "UPDATE `scan4_homes` SET `$anrufField` = '$today', `scan4_status` = 'PENDING', scan4_comment = '$sc4Comment' WHERE homeid = '$homeid';";
                }
                $conn->query($query);

                break;
            }
        }
    }

    if (strpos($reason, 'Keine HBG') !== false) {
        $query = "UPDATE `scan4_homes` SET `scan4_status` = 'STOPPED', scan4_comment = '$sc4Comment' WHERE homeid = '$homeid';";
        $conn->query($query);

        $query = "UPDATE `scan4_tickets` SET `status` = 'closed' WHERE homeid = ? AND (status = 'new' OR status = 'pending')";
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("s", $homeid);
            $stmt->execute();
            $stmt->close();

            $userlog['source'] = 'map';
            $userlog['homeid'] = $homeid;
            $userlog['action1'] = 'ticked set to closed';
            $userlog['action2'] = $sc4Comment;
            saveUserLog($userlog); // save the actions to the userlog
        }
    } else {
        $query = "UPDATE `scan4_homes` SET scan4_comment = '$sc4Comment' WHERE homeid = '$homeid';";
        $conn->query($query);
    }

    $conn->close();
}







function delete_call_note($homeid, $id)
{
    $conn = dbconnect();
    $date = date('Y-m-d');
    $time = date('H:i:s');
    global $currentuser;

    // get callid from the id
    $callid_query = "SELECT `callid` FROM scan4_calls WHERE `id` = '$id'";
    $result = $conn->query($callid_query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $callid = $row['callid'];

        // delete call comment
        $update_calls_query = "UPDATE scan4_calls SET `comment` = '' WHERE `id` = '$id'";
        $conn->query($update_calls_query);

        // delete hbg comment
        $update_hbg_query = "UPDATE scan4_hbg SET `comment` = '' WHERE `ident` = '$callid'";
        $conn->query($update_hbg_query);

        $userlog['source'] = 'map';
        $userlog['homeid'] = $homeid;
        $userlog['action1'] = 'delete comment';
        $userlog['action2'] = 'old comment' . $row['comment'];
        $userlog['action3'] = $callid;
        saveUserLog($userlog); // save the actions to the userlog
    }
    $conn->close();
}



function safe_call_note($homeid, $comment, $id)
{
    $conn = dbconnect();
    $date = date('Y-m-d');
    $time = date('H:i:s');
    global $currentuser;

    try {
        if ($id === null) {
            // check if the user has already saved a similar note for the home ID and date
            $check_query = "SELECT * FROM `scan4_calls` WHERE `homeid` = '$homeid' AND `call_user` = '$currentuser' AND `comment` = '$comment' AND `call_date` = '$date'";
            $result = $conn->query($check_query);

            if ($conn->error) {
                error_log("Error in check query: " . $conn->error);
                return;
            }

            if ($result->num_rows > 0) {
                $conn->close();
                return;
            }

            // No similar note found, proceed with inserting the new note
            $insert_query = "INSERT INTO `scan4_calls`(`call_date`, `call_time`, `call_user`, `homeid`, `result`, `comment`) 
                             VALUES ('$date','$time','$currentuser','$homeid','Notiz','$comment')";
            if ($conn->query($insert_query) === TRUE) {
                error_log("New note inserted for home ID $homeid");
            } else {
                error_log("Error inserting new note: " . $conn->error);
            }

            // save the actions to the user log
            $userlog = [
                'source' => 'map',
                'homeid' => $homeid,
                'action1' => 'create customer note',
                'action2' => $comment
            ];
            saveUserLog($userlog);
        } else {
            // update an existing comment
            $update_query = "UPDATE `scan4_calls` SET `comment` = '$comment' WHERE `scan4_calls`.`id` = '$id';";
            if ($conn->query($update_query) === TRUE) {
                error_log("Comment updated for ID $id");
            } else {
                error_log("Error updating comment: " . $conn->error);
            }

            // save the actions to the user log
            $userlog = [
                'source' => 'map',
                'homeid' => $homeid,
                'action1' => 'update call comment',
                'action2' => $comment
            ];
            saveUserLog($userlog);
        }

        // Update scan4_homes
        $update_homes_query = "UPDATE `scan4_homes` SET `scan4_comment` = '$comment' WHERE `homeid` = '$homeid';";
        if ($conn->query($update_homes_query) === TRUE) {
            error_log("scan4_homes updated for home ID $homeid");
        } else {
            error_log("Error updating scan4_homes: " . $conn->error);
        }
    } catch (Exception $e) {
        error_log("Caught exception in safe_call_note: " . $e->getMessage());
    } finally {
        $conn->close();
    }
}



function load_homeid($homeid)
{
    $conn = dbconnect();
    $query = "SELECT * FROM `scan4_homes` WHERE homeid = '$homeid'";
    $result = mysqli_query($conn, $query);
    $data_homes = mysqli_fetch_assoc($result);
    $data_relations = array(); // initializing data_relations array

    if ($data_homes) {
        extract($data_homes); // extract the column names to usable vars

        if (!empty($relations)) {
            // splitting the home ids in 'relations' column by ;
            $relation_ids = explode(';', $relations);

            foreach ($relation_ids as $id) {
                // querying each related homeid and storing the data
                $relation_query = "SELECT * FROM `scan4_homes` WHERE homeid = '$id'";
                $relation_result = mysqli_query($conn, $relation_query);
                if ($relation_result && mysqli_num_rows($relation_result) > 0) {
                    $relation_data = mysqli_fetch_assoc($relation_result);
                    $data_relations[] = $relation_data; // storing the relation data
                }
            }
        }
    }

    $query = "SELECT * FROM `scan4_calls` WHERE homeid = '$homeid' ORDER BY `scan4_calls`.`id` DESC";
    $result = mysqli_query($conn, $query);
    $data_calls = array();
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data_calls[] = $row;
        }
    }

    $query = "SELECT * FROM `scan4_hbg` WHERE homeid = '$homeid' ORDER BY `scan4_hbg`.`id` DESC";
    $result = mysqli_query($conn, $query);
    $data_hbg = array();
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data_hbg[] = $row;
        }
    }

    $query = "SELECT * FROM `scan4_hbgcheck` WHERE homeid = '$homeid' ORDER BY `scan4_hbgcheck`.`id` DESC";
    $result = mysqli_query($conn, $query);
    $data_hbgcheck = array();
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data_hbgcheck[] = $row;
        }
    }

    $query = "SELECT * FROM `scan4_userlog` WHERE homeid = '$homeid' ORDER BY `scan4_userlog`.`id` DESC";
    $result = mysqli_query($conn, $query);
    $data_userlog = array();
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data_userlog[] = $row;
        }
    }

    $query = "SELECT * FROM `ticket` WHERE homeid = '$homeid' ORDER BY `ticket_id` DESC";
    $result = mysqli_query($conn, $query);
    $data_ticket = array();
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data_ticket[] = $row;
        }
    }

    $conn->close();

    $userlog['source'] = 'map';
    $userlog['homeid'] = $homeid;
    $userlog['action1'] = 'load homeid';
    saveUserLog($userlog); // save the actions to the userlog

    $response = array(
        'data_homes' => $data_homes,
        'data_calls' => $data_calls,
        'data_relations' => $data_relations,
        'data_hbg' => $data_hbg,
        'data_hbgcheck' => $data_hbgcheck,
        'data_userlog' => $data_userlog,
        'data_ticket' => $data_ticket
    );
    $json_response = json_encode($response);
    echo $json_response;
}





//Alex Zone ALERT

function fetchRanking()
{
    $today = date('Y-m-d');
    $query = "SELECT username, COUNT(*) as count FROM scan4_hbg 
    WHERE created >= '$today' AND status = 'PLANNED' AND username != 'admin' 
    GROUP BY username ORDER BY count DESC";


    /*    $query = "SELECT 
    ul.user, 
    SUM(CASE 
            WHEN ul.action1 = 'created an hbg' THEN 1
            WHEN ul.action1 = 'safe call result' THEN 0.1
            WHEN ul.action1 = 'storno an appointment' THEN 0.5
            WHEN ul.action1 = 'moved an hbg' THEN 0.5
            ELSE 0 
        END) as count
FROM 
    scan4_userlog ul
WHERE 
    DATE(ul.datetime) = CURDATE() AND ul.user != 'admin'
GROUP BY 
    ul.user 
HAVING 
    SUM(CASE 
            WHEN ul.action1 = 'created an hbg' THEN 1
            WHEN ul.action1 = 'safe call result' THEN 0.1
            WHEN ul.action1 = 'storno an appointment' THEN 0.5
            WHEN ul.action1 = 'moved an hbg' THEN 0.5
            ELSE 0 
        END) > 0
ORDER BY 
    count DESC;
";*/

    // Erste Datenbankverbindung
    $conn = dbconnect();
    $result = mysqli_query($conn, $query);
    $ranking = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $ranking[] = array('username' => $row['username'], 'count' => $row['count']);
    }

    // Zweite Datenbankverbindung
    $connhc = dbconnecthc();
    $resulthc = mysqli_query($connhc, $query);
    while ($rowhc = mysqli_fetch_assoc($resulthc)) {
        // Überprüfen, ob der Benutzer bereits in $ranking existiert
        $found = false;
        foreach ($ranking as $key => $value) {
            if ($value['username'] == $rowhc['username']) {
                // Aktualisiere den Zähler, wenn der Benutzer bereits vorhanden ist
                $ranking[$key]['count'] += $rowhc['count'];
                $found = true;
                break;
            }
        }
        // Füge einen neuen Eintrag hinzu, wenn der Benutzer noch nicht existiert
        if (!$found) {
            $ranking[] = array('username' => $rowhc['username'], 'count' => $rowhc['count']);
        }
    }

    // Sortiere das Ranking nach der Anzahl in absteigender Reihenfolge
    usort($ranking, function ($a, $b) {
        return $b['count'] - $a['count'];
    });

    return $ranking;
}


function fetchRankingProzent()
{
    $query = "SELECT 
    ul.user, 
    SUM(CASE 
            WHEN ul.action1 = 'created an hbg' THEN 1
            WHEN ul.action1 = 'safe call result' THEN 0.1
            WHEN ul.action1 = 'storno an appointment' THEN 0.5
            WHEN ul.action1 = 'moved an hbg' THEN 0.5
            ELSE 0 
        END) as count
FROM 
    scan4_userlog ul
WHERE 
    DATE(ul.datetime) = CURDATE() AND ul.user != 'admin'
GROUP BY 
    ul.user 
HAVING 
    SUM(CASE 
            WHEN ul.action1 = 'created an hbg' THEN 1
            WHEN ul.action1 = 'safe call result' THEN 0.1
            WHEN ul.action1 = 'storno an appointment' THEN 0.5
            WHEN ul.action1 = 'moved an hbg' THEN 0.5
            ELSE 0 
        END) > 0
ORDER BY 
    count DESC;
";

    // Erste Datenbankverbindung
    $conn = dbconnect();
    $result = mysqli_query($conn, $query);
    $ranking = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $ranking[] = array('username' => $row['username'], 'count' => $row['count']);
    }

    // Zweite Datenbankverbindung
    $connhc = dbconnecthc();
    $resulthc = mysqli_query($connhc, $query);
    while ($rowhc = mysqli_fetch_assoc($resulthc)) {
        // Überprüfen, ob der Benutzer bereits in $ranking existiert
        $found = false;
        foreach ($ranking as $key => $value) {
            if ($value['username'] == $rowhc['username']) {
                // Aktualisiere den Zähler, wenn der Benutzer bereits vorhanden ist
                $ranking[$key]['count'] += $rowhc['count'];
                $found = true;
                break;
            }
        }
        // Füge einen neuen Eintrag hinzu, wenn der Benutzer noch nicht existiert
        if (!$found) {
            $ranking[] = array('username' => $rowhc['username'], 'count' => $rowhc['count']);
        }
    }

    // Sortiere das Ranking nach der Anzahl in absteigender Reihenfolge
    usort($ranking, function ($a, $b) {
        return $b['count'] - $a['count'];
    });

    return $ranking;
}
