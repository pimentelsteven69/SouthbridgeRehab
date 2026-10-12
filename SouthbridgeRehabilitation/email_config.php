<?php
// Southbridge Rehabilitation - Email / SMTP settings.
// Choose: gmail, outlook, microsoft365, or icloud.
// Replace the placeholders for your chosen provider.
//
// Gmail/iCloud typically require an app-specific password.
// Microsoft 365 SMTP AUTH may need to be enabled by your administrator.

$email_provider = 'outlook','gmail';

$email_configs = [
    'gmail' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'encryption' => 'tls',
        'username' => 'YOUR_EMAIL@gmail.com',
        'password' => 'YOUR_16_CHARACTER_APP_PASSWORD',
        'from_email' => 'southbridgerehabsupport@gmail.com',
        'from_name' => 'Southbridge Rehabilitation',
    ],
    'outlook' => [
        'host' => 'smtp-mail.outlook.com',
        'port' => 587,
        'encryption' => 'tls',
        'username' => 'southbridge.rehab.ops.731@outlook.com',
        'password' => 'YOUR_PASSWORD_OR_APP_PASSWORD',
        'from_email' => 'southbridge.rehab.ops.731@outlook.com',
        'from_name' => 'Southbridge Rehabilitation',
    ],
    'microsoft365' => [
        'host' => 'smtp.office365.com',
        'port' => 587,
        'encryption' => 'tls',
        'username' => 'YOUR_EMAIL@YOURDOMAIN.COM',
        'password' => 'YOUR_PASSWORD',
        'from_email' => 'YOUR_EMAIL@YOURDOMAIN.COM',
        'from_name' => 'Southbridge Rehabilitation',
    ],
    'icloud' => [
        'host' => 'smtp.mail.me.com',
        'port' => 587,
        'encryption' => 'tls',
        'username' => 'YOUR_EMAIL@icloud.com',
        'password' => 'YOUR_ICLOUD_APP_SPECIFIC_PASSWORD',
        'from_email' => 'YOUR_EMAIL@icloud.com',
        'from_name' => 'Southbridge Rehabilitation',
    ],
];

if (!isset($email_configs[$email_provider])) {
    throw new RuntimeException('Invalid email provider in email_config.php.');
}

return $email_configs[$email_provider];
