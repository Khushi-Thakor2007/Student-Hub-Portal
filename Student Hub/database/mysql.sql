-- ============================================
-- STUDENT HUB PORTAL DATABASE
-- ============================================

CREATE DATABASE IF NOT EXISTS student_hub
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE student_hub;
-- ============================================
-- 1. EVENTS TABLE
-- ============================================

CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    event_date DATE,
    event_time TIME,
    venue VARCHAR(255),
    description TEXT
) ENGINE=InnoDB;
-- ============================================
-- 2. MATERIAL TABLE
-- ============================================

CREATE TABLE IF NOT EXISTS material (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_name VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    icon VARCHAR(255)
) ENGINE=InnoDB;


-- ============================================
-- 3. NOTIFICATION TABLE
-- ============================================

CREATE TABLE IF NOT EXISTS notification (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    time_label VARCHAR(100)
) ENGINE=InnoDB;


-- ============================================
-- 4. PROFILE TABLE
-- ============================================

CREATE TABLE IF NOT EXISTS profile (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    college VARCHAR(255),
    student_id VARCHAR(50),
    department VARCHAR(100),
    semester VARCHAR(50),
    mobile VARCHAR(15),
    email VARCHAR(150),
    address TEXT,
    institute VARCHAR(255)
) ENGINE=InnoDB;


-- ============================================
-- 5. RESULT TABLE
-- ============================================

CREATE TABLE IF NOT EXISTS result (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject VARCHAR(255) NOT NULL,
    grade VARCHAR(10),
    credits INT,
    status VARCHAR(50)
) ENGINE=InnoDB;


-- ============================================
-- 6. STUDENTS TABLE
-- Used for student and event registration records
-- ============================================

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    student_id VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    mobile VARCHAR(15),
    department VARCHAR(100),
    semester VARCHAR(30),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- ============================================
-- 7. REGISTRATIONS TABLE
-- Connects students with events
-- ============================================

CREATE TABLE IF NOT EXISTS registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    event_id INT NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT unique_student_event
        UNIQUE (student_id, event_id),

    CONSTRAINT fk_registration_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_registration_event
        FOREIGN KEY (event_id)
        REFERENCES events(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;


-- ============================================
-- 8. USERS TABLE
-- Used by existing PHP login and registration
-- Student ID is the unique login identifier
-- ============================================

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    student_id VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    mobile VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- ============================================
-- DATABASE SETUP COMPLETED
-- ============================================