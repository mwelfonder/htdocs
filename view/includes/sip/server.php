<?php
// server.php - PHP Backend für das SIP-Telefon

class SipPhoneBackend {
    private $sip_server;
    private $sip_user;
    private $sip_password;

    public function __construct($server, $user, $password) {
        $this->sip_server = $server;
        $this->sip_user = $user;
        $this->sip_password = $password;
    }

    // Weitere Funktionen zur Handhabung von SIP-Anfragen hier hinzufügen
}

// SIP-Daten
$sip_server = 'sipgate.de';
$sip_user = '3463746e0';
$sip_password = '8wmBAbR4VSY7';

$sipPhone = new SipPhoneBackend($sip_server, $sip_user, $sip_password);

// Logik für die Handhabung eingehender Anfragen hier hinzufügen
?>
