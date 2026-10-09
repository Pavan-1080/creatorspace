```php
<?php

use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/.env.php";

function sendOTPEmail($recipientEmail, $recipientName, $otp)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_EMAIL;
        $mail->Password = SMTP_APP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->CharSet = "UTF-8";
        $mail->setFrom(SMTP_EMAIL, "CreatorSpace");
        $mail->addAddress($recipientEmail, $recipientName);

        $mail->isHTML(true);
        $mail->Subject = "Your CreatorSpace Verification Code";

        $safeName = htmlspecialchars(
            $recipientName,
            ENT_QUOTES,
            "UTF-8"
        );

        $safeOtp = htmlspecialchars(
            (string) $otp,
            ENT_QUOTES,
            "UTF-8"
        );

        $mail->Body = "
            <div style='font-family:Arial,sans-serif;padding:24px;'>
                <h2>Welcome to CreatorSpace!</h2>
                <p>Hello {$safeName},</p>
                <p>Your email verification code is:</p>
                <div style='font-size:32px;font-weight:bold;letter-spacing:8px;'>
                    {$safeOtp}
                </div>
                <p>This code expires in 10 minutes.</p>
                <p>If you didn't request this code, ignore this email.</p>
            </div>
        ";

        $mail->AltBody =
            "Your CreatorSpace verification code is: " .
            $otp .
            ". It expires in 10 minutes.";

        $mail->send();

        return true;

    } catch (\Exception $e) {
        error_log("CreatorSpace email error: " . $mail->ErrorInfo);
        return false;
    }
}