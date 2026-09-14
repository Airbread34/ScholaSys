-- ==================================================
-- ScholaSys - Complete Database Schema
-- ==================================================

-- Create database (optional – you may create manually)
-- CREATE DATABASE IF NOT EXISTS scholasys;
-- USE scholasys;

-- --------------------------------------------------
-- Users table (system users: admin / staff)
-- --------------------------------------------------
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','staff') DEFAULT 'staff',
    twofa_secret VARCHAR(255) DEFAULT NULL,
    twofa_enabled BOOLEAN DEFAULT FALSE,
    profile_image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin (password: admin123)
INSERT INTO users (username, password, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- --------------------------------------------------
-- Graduates table (core graduate records)
-- --------------------------------------------------
CREATE TABLE graduates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) DEFAULT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    contact_number VARCHAR(20),
    program VARCHAR(100) NOT NULL,
    graduation_year YEAR NOT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    survey_token VARCHAR(64) UNIQUE DEFAULT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    cv_path VARCHAR(255) DEFAULT NULL,
    show_in_directory BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_program (program),
    INDEX idx_year (graduation_year),
    INDEX idx_status (status)
);

-- --------------------------------------------------
-- Employment (current job)
-- --------------------------------------------------
CREATE TABLE employment (
    id INT PRIMARY KEY AUTO_INCREMENT,
    graduate_id INT NOT NULL,
    is_employed BOOLEAN DEFAULT FALSE,
    employer_name VARCHAR(200),
    job_title VARCHAR(150),
    employment_type ENUM('full-time','part-time','self-employed','contractual'),
    monthly_salary DECIMAL(10,2),
    work_location VARCHAR(200),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (graduate_id) REFERENCES graduates(id) ON DELETE CASCADE,
    INDEX idx_employed (is_employed)
);

-- --------------------------------------------------
-- Employment history (multiple jobs)
-- --------------------------------------------------
CREATE TABLE employment_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    graduate_id INT NOT NULL,
    employer VARCHAR(200) NOT NULL,
    job_title VARCHAR(150) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    salary DECIMAL(10,2),
    is_current BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (graduate_id) REFERENCES graduates(id) ON DELETE CASCADE,
    INDEX idx_graduate (graduate_id)
);

-- --------------------------------------------------
-- Tracer surveys (responses)
-- --------------------------------------------------
CREATE TABLE tracer_surveys (
    id INT PRIMARY KEY AUTO_INCREMENT,
    graduate_id INT NOT NULL,
    survey_date DATE NOT NULL,
    months_after_graduation INT,
    additional_education TEXT,
    feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (graduate_id) REFERENCES graduates(id) ON DELETE CASCADE
);

-- --------------------------------------------------
-- Survey reminders (track sent reminders)
-- --------------------------------------------------
CREATE TABLE survey_reminders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    graduate_id INT NOT NULL,
    months_after INT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (graduate_id) REFERENCES graduates(id) ON DELETE CASCADE,
    UNIQUE KEY unique_reminder (graduate_id, months_after)
);

-- --------------------------------------------------
-- Anniversary reminders (track sent anniversary emails)
-- --------------------------------------------------
CREATE TABLE anniversary_reminders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    graduate_id INT NOT NULL,
    anniversary_year INT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (graduate_id) REFERENCES graduates(id) ON DELETE CASCADE,
    UNIQUE KEY unique_anniversary (graduate_id, anniversary_year)
);

-- --------------------------------------------------
-- Events management
-- --------------------------------------------------
CREATE TABLE events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    location VARCHAR(200),
    capacity INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------
-- Event registrations (many-to-many between events and graduates)
-- --------------------------------------------------
CREATE TABLE event_registrations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    graduate_id INT NOT NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    attended BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (graduate_id) REFERENCES graduates(id) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (event_id, graduate_id)
);

-- --------------------------------------------------
-- Announcements (news / notices)
-- --------------------------------------------------
CREATE TABLE announcements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATE DEFAULT NULL
);

-- --------------------------------------------------
-- Newsletter history (optional log)
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS newsletter_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    recipient_group VARCHAR(50),
    recipient_count INT,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);