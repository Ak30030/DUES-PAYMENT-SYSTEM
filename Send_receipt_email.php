<?php
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/mail_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function send_receipt_email($to_email, $to_name, $due_title, $level, $amount, $reference, $paid_at) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = 'awuntubafredrick@gmail.com';
        $mail->Password   = 'bzas nsqu nwlk vmng';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to_email, $to_name);

        $mail->isHTML(true);
        $mail->Subject = 'Payment Confirmation - ' . $due_title;

        $formatted_amount = number_format($amount, 2);
        $formatted_date = date('d M Y, g:i A', strtotime($paid_at));

        $mail->Body = "
            <div style='font-family:Arial,sans-serif; max-width:500px; margin:0 auto; padding:20px; border:1px solid #e2e8f0; border-radius:8px;'>
                <h2 style='color:#1b2a6b;'>Payment Confirmed</h2>
                <p>Dear {$to_name},</p>
                <p>We have received your payment for the Computer Science Department, KsTU. Here are the details:</p>
                <table style='width:100%; border-collapse:collapse; margin:15px 0;'>
                    <tr><td style='padding:8px 0; color:#64748b;'>Due</td><td style='padding:8px 0; text-align:right;'>{$due_title}</td></tr>
                    <tr><td style='padding:8px 0; color:#64748b;'>Level</td><td style='padding:8px 0; text-align:right;'>Level {$level}</td></tr>
                    <tr><td style='padding:8px 0; color:#64748b;'>Amount</td><td style='padding:8px 0; text-align:right; font-weight:bold; color:#d81f26;'>GHS {$formatted_amount}</td></tr>
                    <tr><td style='padding:8px 0; color:#64748b;'>Reference</td><td style='padding:8px 0; text-align:right;'>{$reference}</td></tr>
                    <tr><td style='padding:8px 0; color:#64748b;'>Date Paid</td><td style='padding:8px 0; text-align:right;'>{$formatted_date}</td></tr>
                </table>
                <p>You can log in to view or print your official receipt at any time.</p>
                <p style='color:#64748b; font-size:0.85rem; margin-top:20px;'>Computer Science Department, Kumasi Technical University</p>
            </div>
        ";
        $mail->AltBody = "Payment Confirmed\n\nDue: {$due_title}\nLevel: {$level}\nAmount: GHS {$formatted_amount}\nReference: {$reference}\nDate Paid: {$formatted_date}\n\nCS Department, KsTU";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Receipt email failed: ' . $mail->ErrorInfo);
        return false;
    }
}