# Southbridge Rehabilitation LOTO Log — XAMPP Edition

This version converts the original HTML LOTO log into a PHP/MySQL application for XAMPP.

## Requirements
- XAMPP for Windows
- Apache
- MySQL/MariaDB
- PHP (included with XAMPP)

## Installation

1. Install/start XAMPP.
2. Open the XAMPP Control Panel.
3. Start **Apache** and **MySQL**.
4. Copy the folder `Southbridge_LOTO_XAMPP` into:
   `C:\xampp\htdocs\`
5. Open:
   `http://localhost/phpmyadmin`
6. In phpMyAdmin, click **Import**.
7. Select `database.sql` from this folder and run the import.
8. Open:
   `http://localhost/Southbridge_LOTO_XAMPP/`

## Database
The default XAMPP configuration is:
- Host: localhost
- Database: southbridge_loto
- User: root
- Password: blank

If your MySQL root account has a password, edit the `$dbPass` value near the top of `index.php`.

## What changed
- The page is now `index.php`.
- LOTO entries are stored in MySQL.
- The Month/Year, Area, Supervisor, review signature, review date, and notes are stored.
- **Save to Database** persists the current log.
- Opening the page again loads the saved entries.
- **Clear Entries** clears the database records.
- Printing and Excel export remain available.

## Important
The original page's buttons for `LOTO_Page_2.html` and `Pest_Control_Log.html` were not included because those files were not supplied with the uploaded page. They can be added later and linked from this application.

For a multi-user setup, additional authentication/user permissions can be added later.


PASSWORD FEATURES
-----------------
1. Import password_reset.sql into the southbridge_loto database.
2. Change Password is available to every logged-in user from the Home page.
3. Forgot Password is available from signin.php.
4. On XAMPP, email is normally not configured. For testing, forgot_password.php displays the generated reset link on the page.
5. In production, configure PHP mail/SMTP and remove the XAMPP testing link display.
6. Reset links expire after 1 hour and are invalidated after a successful password reset.
7. Passwords are stored using PHP password_hash/password_verify.
