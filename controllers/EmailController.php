<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once ROOT_PATH . '/vendor/autoload.php';
require_once ROOT_PATH . '/core/config.php';

class EmailController {
    public static function sendVerificationEmail($to, $token) {
        $mail = new PHPMailer(true);
        $logFile = ROOT_PATH . '/email_debug.log'; // Log file path

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port       = SMTP_PORT;

            // Timeout settings (Fix for infinite loading)
            $mail->Timeout  = 10; // Timeout in seconds
            $mail->Timelimit = 10;

            // Recipients
            $mail->setFrom(FROM_EMAIL, FROM_NAME);
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Verify Your Email Address';
            $link = APP_URL . '/verify_email.php?token=' . $token;
            
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;'>
                    <div style='max-width: 600px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                        <h2 style='color: #333;'>Verify Your Email</h2>
                        <p style='color: #555;'>Please confirm your email address by clicking the button below:</p>
                        <p style='text-align: center;'>
                            <a href='$link' style='display: inline-block; padding: 12px 24px; background-color: #3b82f6; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Verify Email</a>
                        </p>
                        <p style='color: #999; font-size: 12px; margin-top: 20px;'>If the button doesn't work, copy and paste this link: <br> $link</p>
                    </div>
                </div>
            ";
            $mail->AltBody = "Please verify your email by visiting: $link";

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Log the error to a file for debugging
            $errorMessage = date('Y-m-d H:i:s') . " [ERROR] " . $mail->ErrorInfo . "\n";
            file_put_contents($logFile, $errorMessage, FILE_APPEND);
            return false;
        }
    }

    public static function sendEmail($to, $subject, $body) {
        $mail = new PHPMailer(true);
        $logFile = ROOT_PATH . '/email_debug.log';

        try {
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port       = SMTP_PORT;
            $mail->Timeout  = 10;
            $mail->Timelimit = 10;

            $mail->setFrom(FROM_EMAIL, FROM_NAME);
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            $errorMessage = date('Y-m-d H:i:s') . " [ERROR] " . $mail->ErrorInfo . "\n";
            file_put_contents($logFile, $errorMessage, FILE_APPEND);
            return false;
        }
    }
}