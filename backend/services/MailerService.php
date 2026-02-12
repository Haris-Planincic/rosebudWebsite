<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailerService {

    public function sendBookingConfirmation($toEmail, $toName, $booking, $screening, $amount = null) {
        $mail = new PHPMailer(true);

        try {
            // SMTP (Mailtrap recommended for dev)
           $mail->isSMTP();
            $mail->Host       = Config::MAIL_HOST();
            $mail->SMTPAuth   = true;
$mail->Username   = Config::MAIL_USER();
$mail->Password   = Config::MAIL_PASS();
$mail->Port       = Config::MAIL_PORT();

$mail->CharSet = 'UTF-8';
$mail->SMTPAutoTLS = true;

            $mail->setFrom('no-reply@rosebud.com', 'Rosebud Cinema');
            $mail->addAddress($toEmail, $toName);

            $mail->isHTML(true);
            $mail->Subject = 'Your screening booking is confirmed';

            $mail->Body = "
                <h2>Booking Confirmed 🎉</h2>
                <p>Hi <strong>{$toName}</strong>,</p>

                <p>Your booking has been successfully confirmed.</p>

                <ul>
                    <li><strong>Screening:</strong> {$screening['screeningTitle']}</li>
                    <li><strong>Date & Time:</strong> {$screening['screeningTime']}</li>
                </ul>

                <p>We look forward to seeing you!</p>
                <p><strong>Rosebud Cinema</strong></p>
            ";

            $mail->AltBody =
                "Booking confirmed!\n\n" .
                "Screening: {$screening['screeningTitle']}\n" .
                "Time: {$screening['screeningTime']}\n";

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log("Email failed: " . $mail->ErrorInfo);
            return false;
        }
    }
}
