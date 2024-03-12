<?php
include "../../view/includes/functions.php";

// Verbindung zur Datenbank herstellen (Sie können auch Ihre eigene dbconnect Funktion nutzen)
$conn = dbconnect();

// Überprüfen Sie, ob der Stadtnamenparameter übergeben wurde
if(isset($_GET['city'])) {
    $cityName = $_GET['city'];

    // Datenbankabfrage
    $query = "
    SELECT h.city,h.firstname, h.lastname,h.scan4_status,h.emailsend, h.street, h.streetnumber, h.streetnumberadd, h.plz, h.anruf1, h.anruf2, h.anruf3, h.anruf4, h.anruf5, h.emailsend, h.briefkasten, h.email, h.carrier
    FROM scan4_homes h 
    WHERE h.city = ? 
    AND h.scan4_status = 'PENDING' 
    AND h.anruf5 IS NOT NULL 
    AND h.emailsend IS NULL;
";

    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $cityName);
    $stmt->execute();
    $result = $stmt->get_result();

    // Daten als JSON zurückgeben
    if($result->num_rows > 0) {
        $data = [];
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
    } else {
        echo json_encode(['error' => 'Keine Daten gefunden.']);
    }
} else {
    echo json_encode(['error' => 'Stadtnamenparameter fehlt.']);
}
