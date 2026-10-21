# Event management

A PHP and MySQL admin application for creating, viewing, editing, and deleting events, with validated event poster uploads.

## Requirements

- PHP 7.4 or newer with PDO MySQL and Fileinfo enabled
- MySQL or MariaDB
- Apache (for example, XAMPP)

## Setup with XAMPP

1. Copy this folder into `C:\xampp\htdocs`.
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Open phpMyAdmin at `http://localhost/phpmyadmin` and import `schema.sql`.
4. If your MySQL credentials differ from XAMPP defaults, set `EVENT_DB_HOST`, `EVENT_DB_NAME`, `EVENT_DB_USER`, and `EVENT_DB_PASS` in the PHP/Apache environment.
5. Open `http://localhost/Practical%2012/setup.php` and create the first administrator. Use a password with at least 12 characters.
6. Sign in at `http://localhost/Practical%2012/admin/login.php`.

The setup page becomes unavailable for creating accounts once an administrator exists and is restricted to localhost before setup. Poster files are stored in `uploads/posters`; make sure Apache can write to that directory.

## Poster validation

The server accepts JPEG, PNG, and WebP images up to 5 MB. The actual file MIME type is checked with PHP Fileinfo, and stored filenames are randomized. The browser's file picker restrictions are only a convenience; server-side validation is authoritative.

If uploads are rejected due to PHP's `upload_max_filesize` or `post_max_size` limits, increase those settings in `php.ini` (both must allow the 5 MB file plus multipart overhead) and restart Apache.
