SOUTHBRIDGE REHABILITATION - PASSWORD RESET EMAIL SETUP

1. Open email_config.php.
2. Set $provider to ONE of:
      gmail
      outlook
      microsoft365
      icloud
3. Enter the mailbox address and credential in that provider's profile.
4. Save the file.
5. Test Forgot Password from the Southbridge login page.

GMAIL
-----
Server: smtp.gmail.com
Port: 587
Security: STARTTLS
Authentication: SMTP username + Google App Password
Use a Google App Password instead of your normal Gmail password.

OUTLOOK.COM / HOTMAIL / LIVE
----------------------------
Server: smtp-mail.outlook.com
Port: 587
Security: STARTTLS
Authentication: OAuth2 / Modern Auth
IMPORTANT: Outlook.com currently requires OAuth2/Modern Auth for SMTP. The
included basic SMTP sender cannot log in to Outlook.com with a normal password.
For Outlook.com, use an OAuth2-capable mail library/relay before enabling it.

MICROSOFT 365 BUSINESS
----------------------
Server: smtp.office365.com
Port: 587
Security: STARTTLS
Authentication: SMTP AUTH (or OAuth2)
SMTP AUTH must be permitted for the mailbox/tenant. Microsoft recommends
Modern Auth/OAuth2 and is retiring Basic SMTP AUTH. If SMTP AUTH is disabled,
the PHP sender will fail even with the correct password.

ICLOUD
------
Server: smtp.mail.me.com
Port: 587
Security: STARTTLS
Authentication: full iCloud email address + Apple app-specific password
Apple requires an app-specific password for third-party mail access.

SECURITY
--------
- Never put your normal email password into source code when the provider offers
  an app password or OAuth2.
- Do not upload email_config.php containing real credentials.
- Keep email_config.php out of public repositories/backups.
- If credentials are exposed, revoke/rotate them immediately.

NOTE ABOUT OUTLOOK.COM
----------------------
The server settings are correct, but authentication is the limitation. To make
Outlook.com work directly, the application needs OAuth2/Modern Authentication.
The current Southbridge SMTP sender intentionally does not fake or bypass that
requirement.
