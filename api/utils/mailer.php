<?php
// api/utils/mailer.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Adjust these paths based on where you installed PHPMailer
require_once __DIR__ . '/../libs/PHPMailer/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer/SMTP.php';

class Mailer {
    public static function sendOTP($recipientEmail, $otp) {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = 'absolutecipher256@gmail.com'; // REPLACE THIS
            $mail->Password   = 'vgqp jneq oodm lrcf';    // REPLACE THIS (Generate App Password in Google)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Recipients
            $mail->setFrom('absolutecipher256@gmail.com', 'Sebastinian Showcase');
            $mail->addAddress($recipientEmail);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Verify Your Account - Sebastinian Showcase';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; color: #333; text-align: center; padding: 20px;'>
                    <h2 style='color: #800000;'>Sebastinian Showcase Verification</h2>
                    <p>Your verification code is:</p>
                    <div style='background: #f4f4f4; padding: 15px; display: inline-block; border-radius: 8px;'>
                        <h1 style='color: #D4AF37; letter-spacing: 5px; margin: 0;'>$otp</h1>
                    </div>
                    <p style='margin-top: 20px; color: #666;'>This code expires in 10 minutes.</p>
                </div>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            // error_log("Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
}
?>