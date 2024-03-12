<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
include "../../view/includes/functions.php";

// Input von der AJAX-Anfrage erhalten
$homeid = $_POST['homeid'];

// Überprüfen, ob für diesen Kunden am heutigen Tag ein Dokument mit dem Status "Kunde war nicht da" hochgeladen wurde.
$query = "SELECT * FROM scan4_homes_documents WHERE homeid = ? AND status = 'Kunde war nicht da' AND DATE(datetime) = CURDATE()";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $homeid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "exists";
} else {
    echo "not_exists";
}
