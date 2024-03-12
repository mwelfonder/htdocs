<?php

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}

if (!isset($user) || !$user->isLoggedIn()) {
    echo json_encode(['error' => 'Nicht authentifiziert']);
    exit;
}

// Benutzerdaten abrufen
$userId = $user->data()->id;
$db = DB::getInstance();
$userData = $db->get('users', array('id', '=', $userId))->first();

// Sicherstellen, dass die benötigten Daten vorhanden sind
if ($userData && isset($userData->sip_uri, $userData->sip_password, $userData->sip_username)) {
    echo json_encode([
        'sip_uri' => $userData->sip_uri,
        'sip_password' => $userData->sip_password,
        'sip_username' => $userData->sip_username,
        'sip_tone' => $userData->sip_tone
    ]);
} else {
    echo json_encode(['error' => 'Benutzerdaten nicht vollständig']);
}

