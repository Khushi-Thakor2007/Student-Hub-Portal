# Student Management Practical

A simple student-record management module built with PHP, MySQLi prepared statements, and MySQL.

## Requirements

- XAMPP (Apache, PHP, and MySQL)
- PHP with the `mysqli` extension enabled

## Setup

1. Copy this folder into `C:\xampp\htdocs\Practical 11`.
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin`, select **Import**, and import `database.sql`.
4. If your MySQL username or password differs from the XAMPP defaults, update the connection values in `db.php`.
5. Open `http://localhost/Practical%2011/` in your browser.

The default connection in `db.php` is `localhost`, user `root`, an empty password, and database `student_management`.

## Features

- Add, view, edit, and delete student records
- Search by student name, email, or course
- Filter by exact course and gender
- Validate form fields and display success/failure messages
- Use MySQLi prepared statements for all application database queries

Required fields are full name, email, gender, and course. Email addresses must be unique.
