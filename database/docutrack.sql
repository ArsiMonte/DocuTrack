-- DocuTrack Database Schema
-- A Web-Based Document Request Monitoring System for School Registrar Services

CREATE DATABASE IF NOT EXISTS `docutrack_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `docutrack_db`;

-- 1. Users Table (Students & Registrar Admins)
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` VARCHAR(50) NULL UNIQUE, -- e.g. 2023-00123 (null for pure staff)
    `full_name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `contact_number` VARCHAR(30) NULL,
    `course_section` VARCHAR(100) NULL, -- e.g. BSIT 3-A
    `role` ENUM('student', 'admin') NOT NULL DEFAULT 'student',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Document Types Catalog
CREATE TABLE IF NOT EXISTS `document_types` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `description` TEXT NULL,
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `processing_days` INT NOT NULL DEFAULT 3,
    `requirements` TEXT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Document Requests Table
CREATE TABLE IF NOT EXISTS `document_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tracking_code` VARCHAR(50) NOT NULL UNIQUE,
    `user_id` INT NOT NULL,
    `document_type_id` INT NOT NULL,
    `purpose` VARCHAR(255) NOT NULL,
    `copies` INT NOT NULL DEFAULT 1,
    `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('Pending', 'Payment Verification', 'Processing', 'Ready for Pickup', 'Released', 'Rejected') NOT NULL DEFAULT 'Pending',
    `payment_proof` VARCHAR(255) NULL,
    `admin_remarks` TEXT NULL,
    `target_release_date` DATE NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_requests_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_requests_doc_type` FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Status History Logs (Audit Trail for Monitoring)
CREATE TABLE IF NOT EXISTS `status_history` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `request_id` INT NOT NULL,
    `status` VARCHAR(50) NOT NULL,
    `remarks` TEXT NULL,
    `changed_by` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_history_request` FOREIGN KEY (`request_id`) REFERENCES `document_requests` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_history_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================================
-- Seed Initial Data
-- ========================================================

-- Insert Default Admin (Password: admin123) and Demo Student (Password: student123)
-- Hash generated via password_hash('...', PASSWORD_DEFAULT)
INSERT INTO `users` (`id`, `student_id`, `full_name`, `email`, `password`, `contact_number`, `course_section`, `role`) VALUES
(1, 'REG-001', 'Registrar Admin', 'admin@docutrack.edu', '$2y$10$.a1tAxcIn0XEmwMt/G84IeK8Fo3zMBfb60rNjqTKL6.RSLdCQ/Su2', '09123456789', 'Registrar Office', 'admin'),
(2, '2023-10045', 'Juan Dela Cruz', 'student@docutrack.edu', '$2y$10$itztwvRIP2q/0cJXIVzQhuHihjpQbQOBX/hMLan64319sF0qQ1Idm', '09987654321', 'BS Information Technology 3-A', 'student')
ON DUPLICATE KEY UPDATE `email`=`email`;

-- Insert Common Registrar Document Types
INSERT INTO `document_types` (`id`, `name`, `description`, `price`, `processing_days`, `requirements`, `is_active`) VALUES
(1, 'Official Transcript of Records (TOR)', 'Official academic record of subjects and grades taken.', 150.00, 7, '2x2 ID picture, Clearance form', 1),
(2, 'Certificate of Good Moral Character', 'Certification attesting to the student conduct and character.', 50.00, 3, 'Valid Student ID', 1),
(3, 'Certificate of Grades (COG)', 'Semester evaluation of grades earned.', 50.00, 2, 'Latest Registration Card', 1),
(4, 'Certificate of Registration (COR / Duplicate)', 'Official proof of current enrollment and load.', 50.00, 1, 'Clearance from Accounting', 1),
(5, 'Honorable Dismissal / Transfer Credentials', 'Official release form for transferring to another institution.', 200.00, 10, 'Complete University Clearance, Library Clearance', 1),
(6, 'Certified True Copy of Diploma', 'Authenticated photocopy of graduation diploma.', 100.00, 5, 'Original Diploma presentation', 1)
ON DUPLICATE KEY UPDATE `name`=`name`;
