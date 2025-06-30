<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


function sendOtpMail($toEmail, $toName, $otp) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'emedconsultation@gmail.com';
        $mail->Password   = 'wxnt xpxs orxg jidh'; // App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom(address: 'emedconsultation@gmail.com', name: 'EMedConsultation');
        $mail->addAddress(address: $toEmail, name: $toName);

        $mail->isHTML(isHtml: true);
        $mail->Subject = 'Your OTP Code for Verification';
        $mail->Body = '
            <div style="font-family: Arial, sans-serif; text-align: center; padding: 20px; border: 1px solid #ddd; border-radius: 10px; max-width: 600px; margin: auto;">
                <img src="Images/Logo.png" alt="emedconsultation Logo" style="width: 150px; margin-bottom: 20px;">
                <h2 style="color: #333;">Your OTP Code</h2>
                <p style="font-size: 16px; color: #555;">Use the following OTP to complete your verification process:</p>
                <h1 style="font-size: 32px; color: #2eb872; margin: 20px 0;">' . $otp . '</h1>
                <p style="font-size: 14px; color: #999;">This OTP is valid for 10 minutes. Please do not share it with anyone.</p>
                <hr style="margin: 20px 0;">
                <p style="font-size: 12px; color: #aaa;">If you did not request this OTP, please ignore this email.</p>
            </div>
        ';
        $mail->AltBody = 'Your OTP Code is: ' . $otp . '. This OTP is valid for 10 minutes.';

        $mail->send();
        return true;
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}
function sendMail($to, $subject, $body) {
    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username   = 'emedconsultation@gmail.com';
    $mail->Password   = 'wxnt xpxs orxg jidh';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom(address: 'emedconsultation@gmail.com', name: 'EMedConsultation');
    $mail->addAddress($to);
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $body;

    return $mail->send();
}
?>