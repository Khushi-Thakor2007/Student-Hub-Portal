# Campus Portal

A PHP and MySQL campus portal with student registration, event sign-ups, and an admin dashboard. Admin-only statistics and student-specific navigation are enforced on the server, not just hidden in the interface.

## Requirements

- PHP 8.0 or later with PDO MySQL enabled
- MySQL or MariaDB (XAMPP includes both)

## Setup with XAMPP

1. Copy the project into `C:\xampp\htdocs\Prcatical 14`.
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Open phpMyAdmin at `http://localhost/phpmyadmin`, import `database.sql`, and confirm the `campus_portal` database was created.
4. If your MySQL settings differ from the XAMPP defaults, set `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD` in the PHP environment before starting Apache. Defaults are `127.0.0.1`, `campus_portal`, `root`, and an empty password.
5. Visit `http://localhost/Prcatical%2014/register.php` and create the first account.
6. Promote that account to admin in phpMyAdmin's SQL tab, replacing the email with the one you registered:

   ```sql
   UPDATE campus_portal.users
   SET role = 'admin'
   WHERE email = 'your-admin@example.com';
   ```

7. Sign in at `http://localhost/Prcatical%2014/login.php`. Admin accounts are sent to the dashboard; student accounts can browse events and view their registrations.

The public registration form always creates student accounts. Promote trusted accounts to admin directly in the database. Add events in phpMyAdmin by inserting rows into `campus_portal.events`; students can register for future events from the Events page.

## Dashboard and access control

- The dashboard shows total users, students, events, and registrations, plus the latest eight registrations.
- `dashboard.php` checks the authenticated user's role and returns HTTP 403 to non-admin users.
- Navigation is rendered from the signed-in user's role. Student pages are also protected server-side.
- Passwords use PHP's `password_hash`/`password_verify`; database queries are parameterized, forms use session CSRF tokens, and output is HTML-escaped.
