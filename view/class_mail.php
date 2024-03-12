<?php
namespace sc4_mail;


use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Mailer {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);

        //Server settings
        $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $this->mail->isSMTP();                                            //Send using SMTP
        $this->mail->Host       = 'mail.scan4-gmbh.de';                     //Set the SMTP server to send through
        $this->mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $this->mail->Username   = 'b.getschmann@scan4-gmbh.de';                     //SMTP username
        $this->mail->Password   = 'dc2e297c56HY7';                               //SMTP password
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $this->mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        $this->mail->CharSet = 'UTF-8';
        $this->mail->Encoding = 'base64';
    }

    public function send($from, $fromPlaceholder, $to, $replyTo, $replyToPlaceholder, $cc, $bcc, $subject, $body, $attachments = []) {
        try {
            //Recipients
            $this->mail->setFrom($from, $fromPlaceholder);
            $this->mail->addAddress($to);     //Add a recipient
            $this->mail->addReplyTo($replyTo, $replyToPlaceholder);
            $this->mail->addCC($cc);
            $this->mail->addBCC($bcc);

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
            $this->mail->isHTML(true);                                  //Set email format to HTML
            $this->mail->Subject = $subject;
            $this->mail->Body    = $body;
            $this->mail->AltBody = $body;

            $this->mail->send();
            echo 'Message has been sent';
            return true;
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$e->getMessage()}";
            return false;
        }
    }
}
