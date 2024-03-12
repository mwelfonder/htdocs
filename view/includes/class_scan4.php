<?php

namespace scan4;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Mailer
{
    private $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);

        //Server settings
        //$this->mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $this->mail->SMTPDebug = 0;
        $this->mail->isSMTP();                                            //Send using SMTP
        $this->mail->Host       = 'scan4-gmbh.de';                     //Set the SMTP server to send through
        $this->mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $this->mail->Username   = 'b.getschmann@scan4-gmbh.de';                     //SMTP username
        $this->mail->Password   = 'EmailIstSendStehtDaDasGehtAllesSagtBen!=123ss';                               //SMTP password
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $this->mail->Port       = 465;                                  //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        $this->mail->CharSet = 'UTF-8';
        $this->mail->Encoding = 'base64';
    }

    public function send($from, $fromPlaceholder, $to, $replyTo, $replyToPlaceholder, $cc, $bcc, $subject, $body, $attachments = [])
    {
        try {
            //Recipients
            $this->mail->setFrom($from, $fromPlaceholder);
            $this->mail->addReplyTo($replyTo, $replyToPlaceholder);

            if (!empty($to)) {
                if (strpos($to, ',') !== false) {
                    $toArr = explode(',', $to);
                    foreach ($toArr as $email) {
                        $this->mail->addAddress(trim($email));
                    }
                } else {
                    $this->mail->addAddress(trim($to));
                }
            }


            if ($cc !== "") {
                if (strpos($cc, ',') !== false) {
                    $ccArr = explode(',', $cc);
                    foreach ($ccArr as $email) {
                        $this->mail->addCC(trim($email));
                    }
                } else {
                    $this->mail->addCC(trim($cc));
                }
            }

            if ($bcc !== "") {
                $this->mail->addBCC($bcc);
            }

            // Attach the specified files to the email
            foreach ($attachments as $attachment) {
                $filePath = $attachment['path'];
                $fileName = $attachment['name'];
                $attachmentResult = $this->mail->addAttachment($filePath, $fileName);
                if (!$attachmentResult) {
                    throw new Exception("Failed to attach file to email: " . $this->mail->ErrorInfo);
                }
            }

            //Content
            $this->mail->isHTML(true); //Set email format to HTML
            $this->mail->Subject = $subject;
            $this->mail->Body    = $body;
            $this->mail->AltBody = $body;

            $this->mail->send();

            $this->logError('Message sent successfully!', true, $from, $to, $subject, $body, $attachments);

            echo 'Message has been sent';
            return true;
        } catch (Exception $e) {
            $this->logError($e->getMessage(), false, $from, $to, $subject, $body, $attachments);
            return $e->getMessage();
        }
    }



    public function addEmbeddedImage($path, $cid)
    {
        try {
            $this->mail->addEmbeddedImage($path, $cid);
            return $cid;
        } catch (Exception $e) {
            // Fehlerbehandlung
            error_log("Fehler beim Einbetten des Bildes: " . $e->getMessage());
            return null;
        }
    }

    private function logError($errorMessage, $success = false, $from = '', $to = '', $subject = '', $body = '', $attachments = [])
    {
        $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/logfiles/mailer/' . date('Y_m') . '/';
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0700, true);
        }

        $logText = date('Y-m-d H:i:s') . "\n";
        if ($from) {
            $logText .= "From: " . $from . "\n";
        }
        if ($to) {
            $logText .= "To: " . $to . "\n";
        }
        if ($subject) {
            $logText .= "Subject: " . $subject . "\n";
        }
        if ($body) {
            $bodySummary = substr($body, 0, 150) . '...';
            $logText .= "Content: " . $bodySummary . "\n";
        }
        if ($attachments) {
            $logText .= "Attachments:\n";
            foreach ($attachments as $attachment) {
                $logText .= "- " . $attachment['name'] . " (" . pathinfo($attachment['path'], PATHINFO_EXTENSION) . ")\n";
            }
        }
        $logText .= $errorMessage . "\n" . '###############################################' . "\n";

        $filename = $target_dir . ($success ? 'mail_success.txt' : 'mail_errors.txt');

        if (!file_exists($filename)) {
            $fp = fopen($filename, 'w');
            fclose($fp);
        }

        if (is_writable($filename)) {
            if (!$fp = fopen($filename, 'a+b')) {
                exit;
            }

            if (fwrite($fp, $logText) === FALSE) {
                exit;
            }

            fclose($fp);
        }
    }
}



class StatisticsHBG
{
    private $db;

    public function __construct()
    {
        $this->db = dbconnect();
    }

    public function appointments($date)
    {
        // returns a list of appointments for the given date for hausbegeher and count of appointments
        $query = "SELECT hausbegeher, status, COUNT(*) as count FROM scan4_hbg WHERE date = '$date' GROUP BY hausbegeher, status";
        $result = $this->db->query($query);

        $counts = array();
        while ($row = $result->fetch_assoc()) {
            $name = $row['hausbegeher'];
            $status = $row['status'];
            $count = $row['count'];
            if (!isset($counts[$name])) {
                $counts[$name] = array();
            }
            $counts[$name][$status] = $count;
        }

        // Create totals for each hausbegeher
        foreach ($counts as $name => $status_counts) {
            $total = 0;
            foreach ($status_counts as $status => $count) {
                $total += $count;
            }
            $counts[$name]['TOTAL'] = $total;
        }

        // Create a total array
        $total = array();
        foreach ($counts as $name => $status_counts) {
            foreach ($status_counts as $status => $count) {
                if (!isset($total[$status])) {
                    $total[$status] = 0;
                }
                $total[$status] += $count;
            }
        }

        // Add the total array to the counts array
        $counts['TOTAL'] = $total;

        return $counts;
    }




    public function reviewed($date)
    {
        $query = "SELECT hausbegeher, reviewed, COUNT(*) as count FROM scan4_hbg WHERE date = '$date' AND reviewed IS NOT NULL GROUP BY hausbegeher,reviewed";
        $result = $this->db->query($query);

        $counts = array();
        while ($row = $result->fetch_assoc()) {
            $name = $row['hausbegeher'];
            $reviewed = $row['reviewed'];
            $count = $row['count'];
            if (!isset($counts[$name])) {
                $counts[$name] = array();
            }
            $counts[$name]['reviewed'] = $count;
        }

        return $counts;
    }

    public function appt_status($date)
    {
        $query = "SELECT hausbegeher, appt_status, COUNT(*) as count FROM scan4_hbg WHERE date = '$date' AND status = 'PLANNED' GROUP BY hausbegeher,appt_status";
        $result = $this->db->query($query);

        $counts = array();
        while ($row = $result->fetch_assoc()) {
            $name = $row['hausbegeher'];
            $status = $row['appt_status'];
            if ($status == 'null' || $status == '') $status = 'NULL';
            $count = $row['count'];
            if (!isset($counts[$name])) {
                $counts[$name] = array();
            }
            $counts[$name][$status] = $count;
        }


        // calculate canceled count as the difference between planned and done counts
        foreach ($counts as $name => $status_counts) {
            $count_done = 0;
            $count_done += $status_counts['HBG nicht durchführbar'] ?? 0;
            $count_done += $status_counts['Ich war nicht da'] ?? 0;
            $count_done += $status_counts['Kunde war nicht da'] ?? 0;
            $counts[$name]['abbruch'] = $count_done;
        }

        return $counts;
    }

    public function hbg_appt_citylist($date)
    {
        $query = "SELECT h.city,h.client,h.carrier,h.plz, COUNT(hb.homeid) as count
        FROM scan4_homes h
        LEFT JOIN scan4_hbg hb ON h.homeid = hb.homeid AND hb.date = '$date' AND hb.status = 'PLANNED'
        GROUP BY h.city
        ORDER BY count DESC;";

        $result = $this->db->query($query);

        $array = array();
        $total = 0; // initialize total count to zero
        while ($row = $result->fetch_assoc()) {
            $plz = $row['plz'];
            $city = $row['city'];
            $client = $row['client'];
            $carrier = $row['carrier'];
            $count = $row['count'];
            $array[$city]['plz'] = $plz;
            $array[$city]['city'] = $city;
            $array[$city]['client'] = $client;
            $array[$city]['carrier'] = $carrier;
            $array[$city]['PLANNED'] = $count;
            $total += $count; // add count to total
        }

        // add total count to the array with key "Total"
        $array['Total'] = $total;

        return $array;
    }


    public function __destruct()
    {
        $this->db->close();
    }
}


class StatisticsHOMES
{
    private $db;

    public function __construct()
    {
        $this->db = dbconnect();
    }


    // gives back a count or an array of city and col = match
    public function count_homestatus($col, $match, $returnArray = false)
    {
        $query = "SELECT city, COUNT(id) as count
                  FROM scan4_homes
                  WHERE $col = '$match'
                  GROUP BY city";
        $result = $this->db->query($query);
        $array = array();
        $total_count = 0;
        while ($row = $result->fetch_assoc()) {
            $city = $row['city'];
            $count = $row['count'];
            $total_count += $count;
            if ($returnArray) {
                $array[$city] = $count;
            }
        }
        return $returnArray ? $array : $total_count;
    }




    public function __destruct()
    {
        $this->db->close();
    }


}
