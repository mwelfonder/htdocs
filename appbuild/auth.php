<?php

function connectDB() {
    $servername = "db.scan4-gmbh.com";
	$username = "SC4_CRM";
	$password = "4Amp!w!V0(9VBL9n:;)5{4&M(";
    $dbname = "SC4_CRM";
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

function generateToken($mysqli, $user_id) {
    $salt = 'n.rZt9p!Xql+'; 
    $timestamp = time();
    $token = hash('sha256', $user_id . $salt . $timestamp);

    $stmt = $mysqli->prepare("INSERT INTO user_tokens (user_id, token) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $token);
    $stmt->execute();
    $stmt->close();

    return $token;
}

function validateToken($mysqli, $token) {
    $stmt = $mysqli->prepare("SELECT user_id FROM user_tokens WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        return true;
    }
    return false;
}

$mysqli = connectDB();

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'login':
        if (isset($_POST['username']) && isset($_POST['password'])) {
            $username = $_POST['username'];
            $password = $_POST['password'];

            $stmt = $mysqli->prepare("SELECT id, password FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                if (password_verify($password, $row['password'])) {
                    $user_id = $row['id'];
                    $token = generateToken($mysqli, $user_id);
                    echo json_encode(['success' => true, 'token' => $token]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Invalid login credentials']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid login credentials']);
            }

            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Missing username or password']);
        }
        break;

    case 'validateToken':
        if (isset($_POST['token'])) {
            $token = $_POST['token'];
            $isValid = validateToken($mysqli, $token);
            echo json_encode(['success' => $isValid]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Missing token']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

$mysqli->close();
?>
