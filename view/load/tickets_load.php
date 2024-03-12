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
//echo $logged_in->email;


include "../../view/includes/functions.php";

date_default_timezone_set('Europe/Berlin');
if (!isset($_POST['func'])) {
	die();
}



$func = $_POST['func'];
if ($func === "load_tickettable") {
	load_tickettable();
} else if ($func === "set_swtichstatus") {
	$city = $_POST['city'];
	$status = $_POST['status'];
	set_switchstatus($city, $status);
} else if ($func === "deletecity") {
	$city = $_POST['city'];
	deletecity($city);
} else if ($func === "safe_ticket_newstate") {
	$homeid = $_POST['homeid'];
	$status = $_POST['status'];
	safe_ticket_newstate($homeid, $status);
} else if ($func === "uploadFile") {
	uploadFile();
} else if ($func === "ticket_search") {
	ticket_search();
} else if ($func === "ticket_saveNew") {
	ticket_saveNew();
} else if ($func === "ticket_fileDelete") {
	ticket_fileDelete();
} else if ($func === "ticket_loadByID") {
	ticket_loadByID();
} else if ($func === "ticket_saveComment") {
	ticket_saveComment();
} elseif ($func === "ticket_loadData") {
	ticket_loadData();
} elseif ($func === "ticket_count_data") {
	getTicketCounts();
} elseif ($func === "ticket_close") {
	ticket_close();
} elseif ($func === "ticket_pending") {
	ticket_pending();
} elseif ($func === "ticket_progress") {
	ticket_progress();
}

function ticket_progress()
{
	$ticketident = $_POST['ticketident'];

	// Connect to the database
	$conn = dbconnect();

	// Check connection
	if ($conn->connect_error) {
		$response = [
			'status' => 'error',
			'message' => 'Database connection failed: ' . $conn->connect_error
		];
		echo json_encode($response);
		exit();
	}

	// Prepare the statement
	$stmt = $conn->prepare("UPDATE ticket SET ticket_status = ?, ticket_updated = NOW() WHERE ticket_ident = ?");
	$status = 'progress';
	$stmt->bind_param("ss", $status, $ticketident);

	// Execute the statement
	if ($stmt->execute()) {
		// If you want to retrieve the updated ticket data after changing its status, you can do so here.
		// For now, I'll assume $ticketData is an empty array. Adjust this as needed.
		$ticketData = [];

		$response = [
			'status' => 'success',
			'message' => 'Ticket status and closure date updated successfully!',
			'ticketData' => $ticketData
		];
	} else {
		$response = [
			'status' => 'error',
			'message' => 'Error updating ticket: ' . $stmt->error
		];
	}

	// Close statement and connection
	$stmt->close();
	$conn->close();

	// Echo the response
	echo json_encode($response);
}

function ticket_pending()
{
	$ticketident = $_POST['ticketident'];

	// Connect to the database
	$conn = dbconnect();

	// Check connection
	if ($conn->connect_error) {
		$response = [
			'status' => 'error',
			'message' => 'Database connection failed: ' . $conn->connect_error
		];
		echo json_encode($response);
		exit();
	}

	// Prepare the statement
	$stmt = $conn->prepare("UPDATE ticket SET ticket_status = ?, ticket_updated = NOW() WHERE ticket_ident = ?");
	$status = 'pending';
	$stmt->bind_param("ss", $status, $ticketident);

	// Execute the statement
	if ($stmt->execute()) {
		// If you want to retrieve the updated ticket data after changing its status, you can do so here.
		// For now, I'll assume $ticketData is an empty array. Adjust this as needed.
		$ticketData = [];

		$response = [
			'status' => 'success',
			'message' => 'Ticket status and closure date updated successfully!',
			'ticketData' => $ticketData
		];
	} else {
		$response = [
			'status' => 'error',
			'message' => 'Error updating ticket: ' . $stmt->error
		];
	}

	// Close statement and connection
	$stmt->close();
	$conn->close();

	// Echo the response
	echo json_encode($response);
}


function ticket_close()
{
	$ticketident = $_POST['ticketident'];

	// Connect to the database
	$conn = dbconnect();

	// Check connection
	if ($conn->connect_error) {
		$response = [
			'status' => 'error',
			'message' => 'Database connection failed: ' . $conn->connect_error
		];
		echo json_encode($response);
		exit();
	}

	// Prepare the statement
	$stmt = $conn->prepare("UPDATE ticket SET ticket_status = ?, ticket_closed = NOW() WHERE ticket_ident = ?");
	$status = 'closed';
	$stmt->bind_param("ss", $status, $ticketident);

	// Execute the statement
	if ($stmt->execute()) {
		// If you want to retrieve the updated ticket data after changing its status, you can do so here.
		// For now, I'll assume $ticketData is an empty array. Adjust this as needed.
		$ticketData = [];

		$response = [
			'status' => 'success',
			'message' => 'Ticket status and closure date updated successfully!',
			'ticketData' => $ticketData
		];
	} else {
		$response = [
			'status' => 'error',
			'message' => 'Error updating ticket: ' . $stmt->error
		];
	}

	// Close statement and connection
	$stmt->close();
	$conn->close();

	// Echo the response
	echo json_encode($response);
}


function ticket_loadData()
{
	$homeid = $_POST['homeid'];
	$ticket_id = $_POST['ticket_id'];

	$conn = dbconnect();

	// Prepare and execute your SQL to fetch home data by homeid
	$sql = "SELECT * FROM `scan4_homes` WHERE homeid = ?";
	$stmt = $conn->prepare($sql);

	if ($stmt) {
		$stmt->bind_param('s', $homeid);
		$stmt->execute();

		$result = $stmt->get_result();
		$homeData = array();

		if ($row = $result->fetch_assoc()) {
			$homeData = array(
				'carrier' => $row['carrier'],
				'client' => $row['client'],
				'city' => $row['city'],
				'plz' => $row['plz'],
				'street' => $row['street'],
				'streetnumber' => $row['streetnumber'],
				'streetnumberadd' => $row['streetnumberadd'],
				'firstname' => $row['firstname'],
				'lastname' => $row['lastname'],
				'unit' => $row['unit'],
				'homeid' => $row['homeid'],
				'hbg_status' => $row['hbg_status'],
				'scan4_status' => $row['scan4_status']
			);
		}

		$result->free_result();
		echo json_encode($homeData); // Return the single homeData object
	} else {
		// Handle the error appropriately here
		echo json_encode(array('error' => 'Database error.'));
	}
	$conn->close();
}



function load_tickettable()
{
	header('Content-Type: application/json');

	$userPermissions = getAllPermissions();

	// Flatten permissions for easy lookup
	$flattenedPermissions = [];
	foreach ($userPermissions as $category => $perms) {
		foreach ($perms as $name => $hasPerm) {
			if ($hasPerm) {
				$flattenedPermissions[$name] = true;
			}
		}
	}

	// Database connection
	$conn = dbconnect();
	$query = "SELECT * FROM ticket";
	$result = $conn->query($query);

	$tickets = [];

	// Prefetch all permissions
	$allPermissions = getAllPermissions();

	while ($row = $result->fetch_assoc()) {
		// Extract client and carrier from the row
		$client = $row['ticket_client'];
		$carrier = $row['ticket_carrier'];

		// Check if the user has permission for both client and carrier using the prefetched permissions
		if (
			isset($allPermissions['clients'][$client]) && $allPermissions['clients'][$client] &&
			isset($allPermissions['carriers'][$carrier]) && $allPermissions['carriers'][$carrier]
		) {
			$tickets[] = $row;
		}
	}

	echo json_encode($tickets);

	$conn->close();
}

function ticket_loadByID()
{
	global $currentuser;
	$ticket_id = $_POST['ticket_id'];
	$conn = dbconnect();

	$query = "SELECT * FROM ticket WHERE ticket_id = ?";
	$stmt = $conn->prepare($query);

	if ($stmt === false) {
		handleError($conn->error);
		return;
	}

	$stmt->bind_param("s", $ticket_id);

	if (!$stmt->execute()) {
		handleError($stmt->error);
		return;
	}

	$result = $stmt->get_result();
	if ($result->num_rows === 0) {
		handleError('No matching ticket found');
		return;
	}

	// Fetch the ticket data
	$ticketData = $result->fetch_assoc();

	// Get ticket_files column and explode to get the document IDs
	$docIDs = explode(',', $ticketData['ticket_files']);

	// Retrieve ticket_documents details for the IDs
	$docQuery = "SELECT * FROM ticket_documents WHERE ticket_doc_id IN (" . implode(',', array_fill(0, count($docIDs), '?')) . ")";
	$docStmt = $conn->prepare($docQuery);

	if ($docStmt === false) {
		handleError($conn->error);
		return;
	}

	$types = str_repeat("i", count($docIDs));  // Assume ticket_doc_id is an integer
	$docStmt->bind_param($types, ...$docIDs);

	if (!$docStmt->execute()) {
		handleError($docStmt->error);
		return;
	}

	$docResult = $docStmt->get_result();
	$documents = [];
	while ($doc = $docResult->fetch_assoc()) {
		$documents[] = $doc;
	}

	// Attach documents to ticketData
	$ticketData['files'] = $documents;

	// Retrieve ticket comments
	$commentQuery = "SELECT * FROM ticket_comments WHERE ticket_id = ? ORDER BY comment_creation ASC"; // Adjust column names as per your schema
	$commentStmt = $conn->prepare($commentQuery);

	if ($commentStmt === false) {
		handleError($conn->error);
		return;
	}

	$commentStmt->bind_param("s", $ticket_id);

	if (!$commentStmt->execute()) {
		handleError($commentStmt->error);
		return;
	}

	$commentResult = $commentStmt->get_result();
	$comments = [];
	while ($commentRow = $commentResult->fetch_assoc()) {
		$comments[] = $commentRow;
	}

	// Attach comments to ticketData
	$ticketData['comments'] = $comments;

	$response = [
		'status' => 'success',
		'message' => 'Ticket data retrieved successfully',
		'ticketData' => $ticketData
	];
	echo json_encode($response);

	$stmt->close();
	$docStmt->close();
	$commentStmt->close();
	$conn->close();
}

function handleError($message)
{
	$response = [
		'status' => 'error',
		'message' => $message
	];
	echo json_encode($response);
}

function ticket_fileDelete()
{
	global $currentuser;

	$response = [
		'success' => false,
		'message' => 'An error occurred',
	];

	if (isset($_POST['file_id'])) {
		$file_id = $_POST['file_id'];
		$conn = dbconnect();

		// Retrieve the file information from the database using the file_id
		$stmt = $conn->prepare("SELECT file_dir, file_name, file_ticket FROM ticket_documents WHERE ticket_doc_id = ?");
		$stmt->bind_param("s", $file_id);
		$stmt->execute();
		$result = $stmt->get_result();
		$file = $result->fetch_assoc();

		if ($file) {
			// Delete the file from the file system
			if (file_exists($file['file_dir'])) {
				unlink($file['file_dir']);
			}

			// Remove the record from the database
			$stmt = $conn->prepare("DELETE FROM ticket_documents WHERE ticket_doc_id = ?");
			$stmt->bind_param("i", $file_id);
			if ($stmt->execute()) {
				// Retrieve ticket_files from the tickets table for the ticket associated with the file
				$ticketID = $file['file_ticket'];
				$stmtTicket = $conn->prepare("SELECT ticket_files FROM ticket WHERE ticket_id = ?");
				$stmtTicket->bind_param("i", $ticketID);
				$stmtTicket->execute();
				$resultTicket = $stmtTicket->get_result();
				$ticket = $resultTicket->fetch_assoc();
				$existingFiles = explode(',', $ticket['ticket_files']);

				// Remove the file ID from the list
				$existingFiles = array_diff($existingFiles, [$file_id]);

				// Update ticket_files in the tickets table
				$updatedFiles = implode(',', $existingFiles);
				$stmtUpdate = $conn->prepare("UPDATE ticket SET ticket_files = ? WHERE ticket_id = ?");
				$stmtUpdate->bind_param("si", $updatedFiles, $ticketID);
				$stmtUpdate->execute();

				$stmtTicket->close();
				$stmtUpdate->close();

				$response['success'] = true;
				$response['message'] = 'File deleted successfully';
			} else {
				$response['message'] = 'Failed to delete record from database';
			}
		} else {
			$response['message'] = 'File not found in database';
		}

		$stmt->close();
	} else {
		$response['message'] = 'File ID not provided';
	}

	echo json_encode($response);
}

function ticket_saveNew()
{
	global $logged_in;
	global $currentuser;
	$mail = $logged_in->email;

	$homeid = $_POST['homeid'];
	$htmlContent = $_POST['htmlContent'];
	$fileIdsArray = json_decode($_POST['fileIds'], true);
	$fileIdsString = implode(',', $fileIdsArray);

	$ticket_ident = $_POST['ticket_ident'];
	$ticket_title = $_POST['title'];
	if (isset($_POST['priority']) && !empty($_POST['priority']) && in_array($_POST['priority'], ['Low', 'Medium', 'High', 'Critical'])) {
		$ticket_priority = $_POST['priority'];
	} else {
		// Default to 'Low' if no (or an invalid) value is provided
		$ticket_priority = 'Low';
	}
	$ticket_goal = $_POST['goal'];

	$ticket_creator = $currentuser;
	$ticket_source = (strpos($mail, '@scan4-gmbh.de') !== false) ? 'Intern' : 'Extern';
	$ticket_state = 'private';
	$ticket_status = ($ticket_source == 'Intern') ? 'open' : 'new';

	$conn = dbconnect();

	// Query to fetch the client and carrier info based on homeid
	$queryInfo = "SELECT client, carrier FROM scan4_homes WHERE homeid = ?";
	$stmtInfo = $conn->prepare($queryInfo);

	if ($stmtInfo === false) {
		echo json_encode(['status' => 'error', 'message' => "Error preparing statement for client and carrier lookup: " . $conn->error]);
		return;
	}

	$stmtInfo->bind_param('s', $homeid);  // assuming homeid is a string, if integer use 'i'

	if (!$stmtInfo->execute()) {
		echo json_encode(['status' => 'error', 'message' => "Error: " . $stmtInfo->error]);
		return;
	}

	$result = $stmtInfo->get_result();
	$info = $result->fetch_assoc();
	$ticket_client = $info['client'];
	$ticket_carrier = $info['carrier'];
	$stmtInfo->close();

	$fields = [
		'ticket_ident', 'homeid', 'ticket_title',
		'ticket_description', 'ticket_priority', 'ticket_goal',
		'ticket_creator', 'ticket_files', 'ticket_source',
		'ticket_state', 'ticket_status', 'ticket_client', 'ticket_carrier'  // Added ticket_client and ticket_carrier here
	];
	$values = [
		$ticket_ident, $homeid, $ticket_title, $htmlContent,
		$ticket_priority, $ticket_goal, $ticket_creator, $fileIdsString,
		$ticket_source, $ticket_state, $ticket_status, $ticket_client, $ticket_carrier  // Added ticket_client and ticket_carrier values here
	];

	// Check for Intern source and append additional fields/values
	if ($ticket_source == 'Intern') {
		$ticket_state = 'public';
		$fields = array_merge($fields, ['ticket_finaldescription', 'ticket_finaldescription_user', 'ticket_finaldescription_date']);
		$values = array_merge($values, [$htmlContent, $ticket_creator, date("Y-m-d H:i:s")]);
	}

	$placeholders = rtrim(str_repeat('?,', count($fields)), ',');
	$bind_types = str_repeat('s', count($values));

	$query = "INSERT INTO ticket (" . implode(',', $fields) . ") VALUES ($placeholders)";
	$stmt = $conn->prepare($query);

	if ($stmt === false) {
		echo json_encode(['status' => 'error', 'message' => "Error preparing statement: " . $conn->error]);
		return;
	}

	$stmt->bind_param($bind_types, ...$values);

	if (!$stmt->execute()) {
		echo json_encode(['status' => 'error', 'message' => "Error: " . $stmt->error]);
		$stmt->close();
		$conn->close();
		return;
	}

	$insertedTicketId = $conn->insert_id;
	if (!empty($fileIdsString)) {
		$queryUpdateFiles = "UPDATE ticket_documents SET file_ticket = ? WHERE ticket_doc_id IN ($fileIdsString)";
		$stmtUpdateFiles = $conn->prepare($queryUpdateFiles);

		if ($stmtUpdateFiles === false) {
			echo json_encode(['status' => 'error', 'message' => "Error preparing statement for file update: " . $conn->error]);
			return;
		}

		$stmtUpdateFiles->bind_param('i', $insertedTicketId);

		if (!$stmtUpdateFiles->execute()) {
			echo json_encode(['status' => 'error', 'message' => 'Error associating files with ticket: ' . $stmtUpdateFiles->error]);
		} else {
			echo json_encode(['status' => 'success', 'message' => 'Ticket and associated files created successfully']);
		}
		$stmtUpdateFiles->close();
	} else {
		echo json_encode(['status' => 'success', 'message' => 'Ticket created successfully without associated files']);
	}

	$stmt->close();
	$conn->close();
}




function ticket_search()
{
	$term = $_POST['term'];
	$conn = dbconnect();
	$words = explode(' ', $term);
	$conditions = [];
	$params = [];
	$types = '';

	$permissions = [
		8 => 'Insyte',
		9 => 'Moncobra',
		17 => 'FOL',
		10 => 'UGG',
		11 => 'DGF',
		12 => 'GVG',
		18 => 'GlasfaserPlus'
	];

	foreach ($words as $word) {
		$conditions[] = "(city LIKE ? OR street LIKE ? OR streetnumber LIKE ? OR homeid LIKE ? OR CONCAT(lastname, ' ', firstname) LIKE ? OR phone1 LIKE ? OR phone2 LIKE ?)";
		$types .= 'sssssss';
		for ($i = 0; $i < 7; $i++) {
			$params[] = "%" . $conn->real_escape_string($word) . "%";
		}
	}

	$sql = "SELECT * FROM `scan4_homes` WHERE " . implode(' AND ', $conditions) . " ORDER BY `streetnumber` ASC LIMIT 50";
	$stmt = $conn->prepare($sql);

	if ($stmt) {
		$stmt->bind_param($types, ...$params);
		$stmt->execute();

		$result = $stmt->get_result();
		$rows = array();
		$index = 0;

		while ($row = $result->fetch_assoc()) {
			$allow = true;

			foreach ($permissions as $perm => $value) {
				if (!hasPerm($perm) && (strpos($row['client'], $value) !== false || strpos($row['carrier'], $value) !== false)) {
					$allow = false;
					break;
				}
			}

			if ($allow) {
				$homeid = $row['homeid'];
				$ticket_stmt = $conn->prepare("SELECT * FROM `ticket` WHERE homeid = ? ORDER BY ticket_id DESC");
				if ($ticket_stmt === false) {
					die('Prepare failed: (' . $conn->errno . ') ' . $conn->error);
				}
				$ticket_stmt->bind_param('s', $homeid);
				$ticket_stmt->execute();
				$ticket_result = $ticket_stmt->get_result();
				$tickets = array();
				while ($ticket_row = $ticket_result->fetch_assoc()) {
					$tickets[] = $ticket_row;
				}
				$ticket_result->free_result();

				$rows[] = array(
					'carrier' => $row['carrier'],
					'client' => $row['client'],
					'city' => $row['city'],
					'plz' => $row['plz'],
					'street' => $row['street'],
					'streetnumber' => $row['streetnumber'],
					'streetnumberadd' => $row['streetnumberadd'],
					'firstname' => $row['firstname'],
					'lastname' => $row['lastname'],
					'unit' => $row['unit'],
					'homeid' => $row['homeid'],
					'hbg_status' => $row['hbg_status'],
					'scan4_status' => $row['scan4_status'],
					'tickets' => !empty($tickets) ? $tickets : null,
				);
			}
		}
		$result->free_result();
		echo json_encode($rows);
	} else {
		// Handle the error appropriately here
		echo json_encode(array('error' => 'Database error.'));
	}
	$conn->close();
}

function ticket_saveComment()
{
	global $currentuser;

	$ticketID = $_POST['ticketID'];
	$commentContent = $_POST['commentContent'];
	$property = $_POST['property'];
	$conn = dbconnect();

	if ($property === 'desc') {
		// Get the current date and time in the desired format
		$currentDateTime = date('Y-m-d H:i:s');

		// Update the ticket table
		$query = "UPDATE ticket 
				  SET ticket_finaldescription = ?, 
					  ticket_finaldescription_user = ?, 
					  ticket_finaldescription_date = ? 
				  WHERE ticket_id = ?";

		$stmt = $conn->prepare($query);
		if ($stmt === false) {
			echo "Error preparing statement: " . $conn->error;
			return;
		}

		$stmt->bind_param('sssi', $commentContent, $currentuser, $currentDateTime, $ticketID);
	} else {
		// Determine if the comment_message column should be true based on the property value
		$isMessage = ($property === 'message') ? 1 : 0;

		// Insert into the ticket_comments table
		$query = "INSERT INTO ticket_comments (
                    ticket_id, 
                    comment_content, 
                    comment_author,
                    comment_message
                  ) VALUES (?, ?, ?, ?)";

		$stmt = $conn->prepare($query);
		if ($stmt === false) {
			echo "Error preparing statement: " . $conn->error;
			return;
		}

		$stmt->bind_param('sssi', $ticketID, $commentContent, $currentuser, $isMessage);
	}

	if ($stmt->execute()) {
		$response = [
			'status' => 'success',
			'message' => ($property === 'desc') ? 'Description updated successfully' : 'Comment added successfully'
		];
		echo json_encode($response);
	} else {
		$response = [
			'status' => 'error',
			'message' => 'Error: ' . $stmt->error
		];
		echo json_encode($response);
	}
	$conn->close();
	$stmt->close();
}


function uploadFile()
{
	global $currentuser;

	$response = [
		'status' => 'error',
		'message' => [],
	];

	$upload_dir = "/var/www/html/uploads/tickets/" . date("Y") . "/" . date("Y_m") . "/";
	if (!is_dir($upload_dir)) {
		mkdir($upload_dir, 0777, true);
	}

	$conn = dbconnect();
	$timestamp = date("Y_m_d_His");
	$file_homeid = $_POST['file_homeid'];
	$file_creator = $currentuser;
	$file_creation = date("Y-m-d H:i:s");

	foreach ($_FILES['file']['name'] as $key => $filename) {
		$tmpFilePath = $_FILES['file']['tmp_name'][$key];
		if ($tmpFilePath != "") {
			$newFileName = $timestamp . '_' . basename($filename);
			if (strlen($newFileName) > 155) {
				$newFileName = substr($newFileName, 0, 155);
			}

			$newFilePath = $upload_dir . $newFileName;

			if ($_FILES['file']['error'][$key] != UPLOAD_ERR_OK) {
				$response['message'][] = [
					'filename' => $filename,
					'status' => 'error',
					'error' => 'Upload error code: ' . $_FILES['file']['error'][$key],
				];
				continue;
			}

			if (move_uploaded_file($tmpFilePath, $newFilePath)) {
				$stmt = $conn->prepare("INSERT INTO ticket_documents (file_name, file_dir, file_creation, file_creator, file_homeid) VALUES (?, ?, ?, ?, ?)");
				$stmt->bind_param("sssss", $newFileName, $newFilePath, $file_creation, $file_creator, $file_homeid);

				if ($stmt->execute()) {
					$last_id = $conn->insert_id;

					$response['message'][] = [
						'id' => $last_id,
						'filename' => $filename,
						'filepath' => $newFilePath,
						'filehomeid' => $file_homeid,
						'status' => 'success',
					];
				} else {
					$response['message'][] = [
						'filename' => $filename,
						'filehomeid' => $file_homeid,
						'status' => 'error',
						'error' => 'Database insertion failed',
					];
				}
				$stmt->close();
			} else {
				$response['message'][] = [
					'filename' => $filename,
					'status' => 'error',
					'error' => 'File could not be uploaded',
				];
			}
		}
	}

	echo json_encode($response);
}


function safe_ticket_newstate($homeid, $status)
{
	$date = date('Y-m-d');
	global $currentuser;


	$conn = dbconnect();


	$query = "UPDATE `scan4_tickets` SET status = '" . $status . "' WHERE homeid='" . $homeid . "'";
	mysqli_query($conn, $query) or die(mysqli_error($conn));

	$query = "INSERT INTO `scan4_userlog`( `homeid`,`user`, `source`,`action1`) VALUES ('" . $homeid . "','" . $currentuser . "','tickets','den Ticketstatus auf " . $status . " gesetzt')";
	mysqli_query($conn, $query) or die(mysqli_error($conn));

	$conn->close();
	//echo $query;
	//echo 'giventitle:'.$title;
}


function getTicketCounts()
{
	$db = dbconnect();
	$data = [];

	// Offene Tickets
	$result = $db->query("SELECT COUNT(*) AS open_tickets FROM ticket WHERE ticket_status = 'new'");
	$data['open_tickets'] = $result->fetch_assoc()['open_tickets'];

	// Gesamt Tickets
	$result = $db->query("SELECT COUNT(*) AS total_tickets FROM ticket");
	$data['total_tickets'] = $result->fetch_assoc()['total_tickets'];

	// Heute geschlossen
	$today = date('Y-m-d');
	$result = $db->query("SELECT COUNT(*) AS closed_today FROM ticket WHERE DATE(ticket_closed) = '$today'");
	$data['closed_today'] = $result->fetch_assoc()['closed_today'];

	// Heute erstellt
	$result = $db->query("SELECT COUNT(*) AS created_today FROM ticket WHERE DATE(ticket_creation) = '$today'");
	$data['created_today'] = $result->fetch_assoc()['created_today'];

	// Pending Tickets
	$result = $db->query("SELECT COUNT(*) AS pending_tickets FROM ticket WHERE ticket_status = 'pending'");
	$data['pending_tickets'] = $result->fetch_assoc()['pending_tickets'];

	// Progress Tickets
	$result = $db->query("SELECT COUNT(*) AS progress_tickets FROM ticket WHERE ticket_status = 'progress'");
	$data['progress_tickets'] = $result->fetch_assoc()['progress_tickets'];
	return $data;
}

if ($_POST['func'] == 'ticket_count_data') {
	echo json_encode(getTicketCounts());
}
