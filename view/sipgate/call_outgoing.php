<?php





function SpoofNumber()
{
    // Create new DOM Document for the response
    $dom = new DOMDocument('1.0', 'UTF-8');

    // Add response child
    $response = $dom->createElement('Response');
    $dom->appendChild($response);

    // Add dial command as child in response
    $dial = $dom->createElement('Dial');

    // Create a new attribute for the callerId element
    $callerId = $dom->createAttribute('callerId');
    //$anonymous = $dom->createAttribute('anonymous');

    // set callerId - you should change that to your desired number
    $callerId->value = '491571111111';
    //$anonymous->value = 'true';

    $number = $dom->createElement('Number', '+4915254582743');

    $dial->appendChild($callerId);
    //$dial->appendChild($anonymous);
    $dial->appendChild($number);

    $response->appendChild($dial);

    header('Content-type: application/xml');
    echo $dom->saveXML();
}

include "../includes/functions.php";

function generateXMLResponse($event)
{
    header('Content-type: application/xml');
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<Response ';
    if ($event === 'newCall') {
        echo 'onAnswer="https://crm.scan4-gmbh.de/view/sipgate/call_outgoing.php" ';
        echo 'onHangup="https://crm.scan4-gmbh.de/view/sipgate/call_outgoing.php"';
    }
    echo '/>';
}
// Daten aus dem Webhook empfangen
$raw_data = file_get_contents('php://input');
$webhook_data = array();

// Den URL-kodierten String in ein assoziatives Array umwandeln
parse_str($raw_data, $webhook_data);




if (isset($webhook_data['event'])) {
    switch ($webhook_data['event']) {
        case 'newCall':
            // Generieren einer XML-Antwort für das newCall-Ereignis
            generateXMLResponse('newCall');
            // Protokollierung der empfangenen Daten
            file_put_contents('webhook_log.txt', json_encode($webhook_data, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
            $mysqli = dbconnect(); // Annahme: connect_to_db() gibt ein mysqli-Objekt zurück

            // Die empfangenen Daten in die Datenbank einfügen
            $datetime = date('Y-m-d H:i:s');
            $event = $webhook_data['event'];
            $direction = $webhook_data['direction'];
            $callId = $webhook_data['callId'];
            $origCallId = $webhook_data['origCallId'];
            $from = $webhook_data['from'];
            $to = $webhook_data['to'];
            $user = $webhook_data['user'][0];
            $userId = $webhook_data['userId'][0];
            $fullUserId = $webhook_data['fullUserId'][0];
            $xcid = $webhook_data['xcid'];

            try {
                // Vorbereiten und Ausführen des SQL-Statements
                $stmt = $mysqli->prepare("INSERT INTO scan4_sipgate_webhook (datetime, event, direction, callId, origCallId, from_number, to_number, user_name, userId, fullUserId, xcid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssssssss", $datetime, $event, $direction, $callId, $origCallId, $from, $to, $user, $userId, $fullUserId, $xcid);
                $stmt->execute();
                echo "Daten wurden erfolgreich in die Datenbank geschrieben.";
                $stmt->close();
            } catch (Exception $e) {
                die("Fehler beim Einfügen der Daten: " . $e->getMessage());
            }
            break;
        case 'answer':
            $mysqli = dbconnect(); // Annahme: connect_to_db() gibt ein mysqli-Objekt zurück

            // Die empfangenen Daten in die Datenbank einfügen
            $datetime = date('Y-m-d H:i:s');
            $event = $webhook_data['event'];
            $direction = $webhook_data['direction'];
            $callId = $webhook_data['callId'];
            $origCallId = $webhook_data['origCallId'];
            $from = $webhook_data['from'];
            $to = $webhook_data['to'];
            $xcid = $webhook_data['xcid'];

            try {
                // Vorbereiten und Ausführen des SQL-Statements
                $stmt = $mysqli->prepare("INSERT INTO scan4_sipgate_webhook (datetime, event, direction, callId, origCallId, from_number, to_number, xcid) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssssss", $datetime, $event, $direction, $callId, $origCallId, $from, $to, $xcid);
                $stmt->execute();
                echo "Daten wurden erfolgreich in die Datenbank geschrieben.";
                $stmt->close();
            } catch (Exception $e) {
                die("Fehler beim Einfügen der Daten: " . $e->getMessage());
            }
            break;

        case 'hangup':
            $mysqli = dbconnect(); // Annahme: connect_to_db() gibt ein mysqli-Objekt zurück

            // Die empfangenen Daten in die Datenbank einfügen
            $datetime = date('Y-m-d H:i:s');
            $event = $webhook_data['event'];
            $direction = $webhook_data['direction'];
            $callId = $webhook_data['callId'];
            $origCallId = $webhook_data['origCallId'];
            $from = $webhook_data['from'];
            $to = $webhook_data['to'];
            $xcid = $webhook_data['xcid'];

            try {
                // Vorbereiten und Ausführen des SQL-Statements
                $stmt = $mysqli->prepare("INSERT INTO scan4_sipgate_webhook (datetime, event, direction, callId, origCallId, from_number, to_number, xcid) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssssss", $datetime, $event, $direction, $callId, $origCallId, $from, $to, $xcid);
                $stmt->execute();
                echo "Daten wurden erfolgreich in die Datenbank geschrieben.";
                $stmt->close();
            } catch (Exception $e) {
                die("Fehler beim Einfügen der Daten: " . $e->getMessage());
            }
            break;

            // Fügen Sie hier Fälle für weitere Ereignisse hinzu, falls benötigt

        default:
            // Standardfall für ungültige oder unbekannte Ereignisse
            file_put_contents('webhook_error_log.txt', json_encode($webhook_data, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
            http_response_code(400);
            echo 'Bad Request';
            break;
    }
} else {
    // Kein Ereignis im Webhook-Daten gefunden
    http_response_code(400);
    echo 'Bad Request';
}
