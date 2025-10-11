<?php
/**
 * Email Configuration
 * Settings for PHPMailer email functionality
 */

return [
    'smtp' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'encryption' => 'tls',
        'username' => 'alainfabricehirwa@gmail.com',
        'password' => 'uhpr zzwy dmrl canu',
        'from_email' => 'alainfabricehirwa@gmail.com',
        'from_name' => 'NELVINTO Liquors store'
    ],
    
    'verification' => [
        'email_subject' => 'Email Verification - NELVINTO Liquors store',
        'phone_subject' => 'Phone Verification - NELVINTO Liquors store',
        'code_expiry_minutes' => 15
    ],
    
    'templates' => [
        'email_verification' => '
            <h2>Email Verification</h2>
            <p>Thank you for registering with Wines & Liquors!</p>
            <p>Your verification code is: <strong>{code}</strong></p>
            <p>This code will expire in {expiry_minutes} minutes.</p>
            <p>If you didn\'t request this verification, please ignore this email.</p>
            <br>
            <p>Best regards,<br>Wines & Liquors Team</p>
        ',
        
        'phone_verification' => '
            <h2>Phone Verification</h2>
            <p>Thank you for registering with Wines & Liquors!</p>
            <p>Your verification code is: <strong>{code}</strong></p>
            <p>This code will expire in {expiry_minutes} minutes.</p>
            <p>If you didn\'t request this verification, please ignore this message.</p>
            <br>
            <p>Best regards,<br>Wines & Liquors Team</p>
        '
    ]
];
?>
