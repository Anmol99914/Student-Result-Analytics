-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Dec 31, 2025 at 06:22 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sra`
--

-- --------------------------------------------------------

--
-- Table structure for table `administrator`
--

CREATE TABLE `administrator` (
  `admin_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `administrator`
--

INSERT INTO `administrator` (`admin_id`, `name`, `email`, `password`) VALUES
(1, 'Anmol', 'admin@gmail.com', '$2y$10$C0VaausqK4AkkLhZELd9we9.h9vioxUJ0nllLBqYN2v3XnG.Ilwni');

-- --------------------------------------------------------

--
-- Table structure for table `class`
--

CREATE TABLE `class` (
  `class_id` int(11) NOT NULL,
  `faculty` varchar(50) NOT NULL,
  `semester` int(11) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `batch_year` year(4) NOT NULL DEFAULT year(curdate())
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class`
--

INSERT INTO `class` (`class_id`, `faculty`, `semester`, `status`, `created_at`, `batch_year`) VALUES
(42, 'BCA', 1, 'active', '2025-12-28 03:50:30', '2025'),
(43, 'BCA', 2, 'active', '2025-12-28 03:50:45', '2025'),
(44, 'BCA', 3, 'active', '2025-12-28 03:50:55', '2025'),
(45, 'BCA', 4, 'active', '2025-12-28 03:51:08', '2025'),
(46, 'BCA', 5, 'active', '2025-12-28 03:51:25', '2025'),
(47, 'BCA', 6, 'active', '2025-12-28 03:51:35', '2025'),
(48, 'BCA', 7, 'active', '2025-12-28 03:53:49', '2025'),
(49, 'BCA', 8, 'active', '2025-12-28 03:54:26', '2025'),
(50, 'BBM', 1, 'inactive', '2025-12-28 13:57:21', '2025'),
(51, 'BBM', 2, 'active', '2025-12-28 13:57:21', '2025'),
(52, 'BBM', 3, 'active', '2025-12-28 17:19:25', '2025'),
(53, 'BBM', 4, 'active', '2025-12-29 06:04:03', '2025'),
(54, 'BBM', 5, 'active', '2025-12-29 08:03:10', '2025');

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `faculty_id` int(11) NOT NULL,
  `faculty_code` varchar(10) NOT NULL,
  `faculty_name` varchar(100) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty`
--

INSERT INTO `faculty` (`faculty_id`, `faculty_code`, `faculty_name`, `status`) VALUES
(1, 'BCA', 'Bachelor of Computer Applications', 'active'),
(2, 'BBM', 'Bachelor of Business Management', 'active'),
(3, 'BIM', 'Bachelor of Information Management', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` varchar(10) NOT NULL,
  `user_type` enum('student','teacher','admin') NOT NULL,
  `message` text NOT NULL,
  `type` enum('result_submitted','result_verified','result_rejected','payment','system') DEFAULT 'system',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `related_id` int(11) DEFAULT NULL,
  `related_type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `student_id` varchar(10) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `due_amount` decimal(10,2) GENERATED ALWAYS AS (`total_amount` - `amount_paid`) STORED,
  `payment_status` enum('Paid','Partial','Unpaid') DEFAULT 'Unpaid',
  `payment_date` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`payment_id`, `student_id`, `total_amount`, `amount_paid`, `payment_status`, `payment_date`) VALUES
(1, 'BCA001', 50000.00, 50000.00, 'Paid', '2024-12-01'),
(2, 'BCA002', 50000.00, 45000.00, 'Partial', '2024-12-05'),
(3, 'BBM001', 50000.00, 50000.00, 'Paid', '2024-12-10'),
(4, 'BBM002', 50000.00, 30000.00, 'Partial', '2024-12-15');

-- --------------------------------------------------------

--
-- Table structure for table `result`
--

CREATE TABLE `result` (
  `result_id` int(11) NOT NULL,
  `student_id` varchar(10) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `marks_obtained` int(11) NOT NULL,
  `total_marks` int(11) NOT NULL,
  `percentage` decimal(5,2) GENERATED ALWAYS AS (`marks_obtained` / `total_marks` * 100) STORED,
  `grade` varchar(2) DEFAULT NULL,
  `status` enum('draft','submitted','verified','published') DEFAULT 'draft',
  `semester_id` int(11) NOT NULL,
  `entered_by_teacher_id` int(11) DEFAULT NULL,
  `verified_by_admin_id` int(11) DEFAULT NULL,
  `verification_status` enum('pending','verified','rejected') DEFAULT 'pending',
  `published_date` date DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `result`
--

INSERT INTO `result` (`result_id`, `student_id`, `subject_id`, `marks_obtained`, `total_marks`, `grade`, `status`, `semester_id`, `entered_by_teacher_id`, `verified_by_admin_id`, `verification_status`, `published_date`, `class_id`) VALUES
(1, 'BCA001', 16, 85, 100, 'A', 'published', 1, 1, NULL, 'verified', '2024-12-20', NULL),
(2, 'BCA001', 17, 78, 100, 'B+', 'published', 1, 1, NULL, 'verified', '2024-12-20', NULL),
(3, 'BCA001', 18, 92, 100, 'A+', 'published', 1, 2, NULL, 'verified', '2024-12-20', NULL),
(4, 'BCA001', 19, 88, 100, 'A', 'published', 1, 3, NULL, 'verified', '2024-12-20', NULL),
(5, 'BCA001', 20, 75, 100, 'B+', 'published', 1, 4, NULL, 'verified', '2024-12-20', NULL),
(6, 'BCA002', 16, 65, 100, 'B', 'published', 1, 1, NULL, 'verified', '2024-12-20', NULL),
(7, 'BCA002', 17, 72, 100, 'B+', 'published', 1, 1, NULL, 'verified', '2024-12-20', NULL),
(8, 'BCA002', 18, 81, 100, 'A', 'published', 1, 2, NULL, 'verified', '2024-12-20', NULL),
(9, 'BCA002', 19, 69, 100, 'B', 'published', 1, 3, NULL, 'verified', '2024-12-20', NULL),
(10, 'BCA002', 20, 58, 100, 'C+', 'published', 1, 4, NULL, 'verified', '2024-12-20', NULL),
(11, 'BBM001', 56, 88, 100, 'A', 'published', 1, 5, NULL, 'verified', '2024-12-22', NULL),
(12, 'BBM001', 57, 81, 100, 'A', 'published', 1, 6, NULL, 'verified', '2024-12-22', NULL),
(13, 'BBM001', 63, 92, 100, 'A+', 'published', 1, 7, NULL, 'verified', '2024-12-22', NULL),
(14, 'BBM001', 64, 85, 100, 'A', 'published', 1, 8, NULL, 'verified', '2024-12-22', NULL),
(15, 'BBM001', 139, 79, 100, 'B+', 'published', 1, 9, NULL, 'verified', '2024-12-22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `result_history`
--

CREATE TABLE `result_history` (
  `history_id` int(11) NOT NULL,
  `result_id` int(11) NOT NULL,
  `old_status` enum('draft','submitted','verified','published') DEFAULT NULL,
  `new_status` enum('draft','submitted','verified','published') NOT NULL,
  `old_verification_status` enum('pending','verified','rejected') DEFAULT NULL,
  `new_verification_status` enum('pending','verified','rejected') NOT NULL,
  `changed_by` int(11) NOT NULL COMMENT 'teacher_id or admin_id',
  `user_type` enum('teacher','admin') NOT NULL,
  `comments` text DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `semester`
--

CREATE TABLE `semester` (
  `semester_id` int(11) NOT NULL,
  `semester_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `semester`
--

INSERT INTO `semester` (`semester_id`, `semester_name`) VALUES
(1, 'First Semester'),
(2, 'Second Semester'),
(3, 'Third Semester'),
(4, 'Fourth Semester'),
(5, 'Fifth Semester'),
(6, 'Sixth Semester'),
(7, 'Seventh Semester'),
(8, 'Eighth Semester');

-- --------------------------------------------------------

--
-- Table structure for table `semester_progress`
--

CREATE TABLE `semester_progress` (
  `progress_id` int(11) NOT NULL,
  `student_id` varchar(20) DEFAULT NULL,
  `semester_number` int(11) DEFAULT NULL,
  `academic_year` int(11) DEFAULT NULL,
  `status` enum('ongoing','completed','failed') DEFAULT NULL,
  `result_published` tinyint(1) DEFAULT 0,
  `fee_paid` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `student_id` varchar(10) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `class_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `phone_number` varchar(10) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `admission_year` int(11) DEFAULT 2025,
  `batch_code` varchar(20) DEFAULT '2025-2029'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`student_id`, `student_name`, `email`, `password`, `class_id`, `semester_id`, `phone_number`, `is_active`, `created_at`, `updated_at`, `admission_year`, `batch_code`) VALUES
('BBM001', 'Sagun Giri', 'bbm001@gmail.com', 'pass123', 50, 1, '9800000103', 1, '2025-12-28 14:07:53', '2025-12-28 14:07:53', 2025, '2025-2029'),
('BBM002', 'Shanti Giri', 'bbm002@gmail.com', 'pass123', 50, 1, '9800000104', 1, '2025-12-28 14:07:53', '2025-12-28 14:07:53', 2025, '2025-2029'),
('BCA001', 'Sachin Giri', 'bca001@gmail.com', '$2y$10$zglGVbWkrgNJvsaHFON2S.a3KmiuNzmuLYgmxxqm/0OV2.myiSyt.', 42, 1, '9800000001', 1, '2025-12-28 13:45:15', '2025-12-30 15:25:06', 2025, '2025-2029'),
('BCA002', 'Amir Giri', 'bca002@gmail.com', 'pass123', 42, 1, '9800000002', 1, '2025-12-28 13:45:15', '2025-12-28 13:45:15', 2025, '2025-2029');

--
-- Triggers `student`
--
DELIMITER $$
CREATE TRIGGER `prevent_invalid_dates` BEFORE UPDATE ON `student` FOR EACH ROW BEGIN
    IF NEW.updated_at < OLD.created_at THEN
        SET NEW.updated_at = OLD.updated_at; -- Keep old value
        -- Or: SET NEW.updated_at = NOW(); -- Set to current
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `student_subject_enrollment`
--

CREATE TABLE `student_subject_enrollment` (
  `enrollment_id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `academic_year` year(4) NOT NULL DEFAULT 2025,
  `status` enum('enrolled','completed') DEFAULT 'enrolled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_subject_enrollment`
--

INSERT INTO `student_subject_enrollment` (`enrollment_id`, `student_id`, `subject_id`, `class_id`, `academic_year`, `status`) VALUES
(4, 'BCA001', 16, 42, '2025', 'enrolled'),
(5, 'BCA001', 17, 42, '2025', 'enrolled'),
(6, 'BCA001', 18, 42, '2025', 'enrolled'),
(7, 'BCA002', 16, 42, '2025', 'enrolled'),
(8, 'BCA002', 17, 42, '2025', 'enrolled'),
(12, 'BBM001', 56, 50, '2025', 'enrolled'),
(13, 'BBM001', 57, 50, '2025', 'enrolled'),
(14, 'BBM001', 58, 51, '2025', 'enrolled');

-- --------------------------------------------------------

--
-- Table structure for table `subject`
--

CREATE TABLE `subject` (
  `subject_id` int(11) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `subject_code` varchar(20) DEFAULT NULL,
  `faculty_id` int(11) NOT NULL,
  `semester` int(11) DEFAULT NULL,
  `credits` int(11) DEFAULT 3,
  `is_elective` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `is_active` tinyint(1) DEFAULT 1,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subject`
--

INSERT INTO `subject` (`subject_id`, `subject_name`, `created_at`, `subject_code`, `faculty_id`, `semester`, `credits`, `is_elective`, `status`, `is_active`, `description`) VALUES
(16, 'Computer Fundamentals and Applications', '2025-12-27 08:07:31', 'BCA101', 1, 1, 3, 0, 'active', 1, '0'),
(17, 'Society and Technology', '2025-12-27 08:07:31', 'BCA102', 1, 1, 3, 0, 'active', 1, '0'),
(18, 'English I', '2025-12-27 08:07:31', 'BCA103', 1, 1, 4, 0, 'active', 1, '0'),
(19, 'Mathematics I', '2025-12-27 08:07:31', 'BCA104', 1, 1, 2, 1, 'active', 1, '0'),
(20, 'Digital Logic', '2025-12-27 08:07:31', 'BCA105', 1, 1, 2, 0, 'active', 1, NULL),
(21, 'C Programming', '2025-12-27 08:07:31', 'BCA201', 1, 2, 4, 0, 'active', 1, NULL),
(22, 'Financial Accounting', '2025-12-27 08:07:31', 'BCA202', 1, 2, 4, 0, 'active', 1, NULL),
(23, 'English II', '2025-12-27 08:07:31', 'BCA203', 1, 2, 4, 0, 'active', 1, '0'),
(24, 'Mathematics II', '2025-12-27 08:07:31', 'BCA204', 1, 2, 2, 0, 'active', 1, NULL),
(25, 'MicroProcessor and Architecture', '2025-12-27 08:07:31', 'BCA205', 1, 2, 3, 0, 'active', 1, NULL),
(26, 'Data Structures and Algorithms', '2025-12-27 08:07:32', 'BCA301', 1, 3, 4, 0, 'active', 1, ''),
(27, 'Probability and Statistics', '2025-12-27 08:07:32', 'BCA302', 1, 3, 4, 0, 'active', 1, NULL),
(28, 'System Analysis and Design', '2025-12-27 08:07:32', 'BCA303', 1, 3, 3, 0, 'active', 1, NULL),
(29, 'OOP in Java', '2025-12-27 08:07:32', 'BCA304', 1, 3, 2, 0, 'active', 1, NULL),
(30, 'Web Technology', '2025-12-27 08:07:32', 'BCA305', 1, 3, 3, 0, 'active', 1, NULL),
(31, 'Operating Systems', '2025-12-27 08:07:32', 'BCA401', 1, 4, 4, 0, 'active', 1, NULL),
(32, 'Numerical Methods', '2025-12-27 08:07:32', 'BCA402', 1, 4, 4, 0, 'active', 1, NULL),
(33, 'Software Engineering', '2025-12-27 08:07:32', 'BCA403', 1, 4, 3, 0, 'active', 1, NULL),
(34, 'Scripting Language', '2025-12-27 08:07:32', 'BCA404', 1, 4, 3, 0, 'active', 1, NULL),
(35, 'Database Management System', '2025-12-27 08:07:32', 'BCA405', 1, 4, 3, 0, 'active', 1, NULL),
(36, 'MIS and E-Business', '2025-12-27 08:07:32', 'BCA501', 1, 5, 4, 0, 'active', 1, NULL),
(37, 'DotNet Technology', '2025-12-27 08:07:32', 'BCA502', 1, 5, 4, 0, 'active', 1, NULL),
(38, 'Computer Networking', '2025-12-27 08:07:32', 'BCA503', 1, 5, 3, 0, 'active', 1, NULL),
(39, 'Introduction to Management', '2025-12-27 08:07:33', 'BCA504', 1, 5, 3, 0, 'active', 1, NULL),
(40, 'Computer Graphics and Animation', '2025-12-27 08:07:33', 'BCA505', 1, 5, 3, 0, 'active', 1, NULL),
(41, 'Mobile Programming', '2025-12-27 08:07:33', 'BCA601', 1, 6, 4, 0, 'active', 1, NULL),
(42, 'Distributed System', '2025-12-27 08:07:33', 'BCA602', 1, 6, 4, 0, 'active', 1, NULL),
(43, 'Applied Economics', '2025-12-27 08:07:33', 'BCA603', 1, 6, 3, 0, 'active', 1, NULL),
(44, 'Advanced Java Programming', '2025-12-27 08:07:33', 'BCA604', 1, 6, 3, 0, 'active', 1, NULL),
(45, 'Network Programming', '2025-12-27 08:07:33', 'BCA605', 1, 6, 4, 0, 'active', 1, NULL),
(46, 'Cyber Law and Professional Ethics', '2025-12-27 08:07:34', 'BCA701', 1, 7, 4, 0, 'active', 1, NULL),
(47, 'Cloud Computing', '2025-12-27 08:07:34', 'BCA702', 1, 7, 4, 0, 'active', 1, NULL),
(48, 'Elective I: Internet of Things', '2025-12-27 08:07:34', 'BCA703', 1, 7, 3, 1, 'active', 1, NULL),
(49, 'Elective II: Cyber Security', '2025-12-27 08:07:34', 'BCA704', 1, 7, 3, 1, 'active', 1, NULL),
(50, 'Elective III: Data Mining', '2025-12-27 08:07:34', 'BCA705', 1, 7, 4, 1, 'active', 1, NULL),
(51, 'Machine Learning', '2025-12-27 08:07:34', 'BCA801', 1, 8, 4, 0, 'active', 1, NULL),
(52, 'Elective I: Big Data Analytics', '2025-12-27 08:07:34', 'BCA802', 1, 8, 4, 1, 'active', 1, NULL),
(53, 'Elective II: Blockchain Technology', '2025-12-27 08:07:34', 'BCA803', 1, 8, 3, 1, 'active', 1, NULL),
(54, 'Elective III: Image Processing', '2025-12-27 08:07:34', 'BCA804', 1, 8, 5, 1, 'active', 1, NULL),
(55, 'Network Administration', '2025-12-27 08:07:34', 'BCA805', 1, 8, 2, 0, 'active', 1, NULL),
(56, 'Principles of Management', '2025-12-28 13:51:34', 'BBM101', 2, 1, 3, 0, 'active', 1, NULL),
(57, 'Business Communication', '2025-12-28 13:51:34', 'BBM102', 2, 1, 3, 0, 'active', 1, NULL),
(58, 'Marketing Management', '2025-12-28 13:51:34', 'BBM201', 2, 2, 4, 0, 'active', 1, NULL),
(63, 'Microeconomics', '2025-12-29 13:54:26', 'BBM103', 2, 1, 4, 0, 'active', 1, NULL),
(64, 'Business Mathematics', '2025-12-29 13:54:26', 'BBM104', 2, 1, 4, 0, 'active', 1, NULL),
(65, 'Financial Accounting', '2025-12-29 13:54:26', 'BBM202', 2, 2, 4, 0, 'active', 1, NULL),
(66, 'Business Statistics', '2025-12-29 13:54:26', 'BBM203', 2, 2, 3, 0, 'active', 1, NULL),
(67, 'Organizational Behavior', '2025-12-29 13:54:26', 'BBM204', 2, 2, 3, 0, 'active', 1, NULL),
(68, 'IT Applications in Business', '2025-12-29 13:54:26', 'BBM205', 2, 2, 3, 0, 'active', 1, NULL),
(69, 'Cost Accounting', '2025-12-29 13:54:26', 'BBM301', 2, 3, 4, 0, 'active', 1, NULL),
(70, 'Business Law', '2025-12-29 13:54:26', 'BBM302', 2, 3, 3, 0, 'active', 1, NULL),
(71, 'Macroeconomics', '2025-12-29 13:54:26', 'BBM303', 2, 3, 4, 0, 'active', 1, NULL),
(72, 'Management Accounting', '2025-12-29 13:54:26', 'BBM304', 2, 3, 3, 0, 'active', 1, NULL),
(73, 'Business Research Methods', '2025-12-29 13:54:26', 'BBM305', 2, 3, 3, 0, 'active', 1, NULL),
(74, 'Human Resource Management', '2025-12-29 13:54:26', 'BBM401', 2, 4, 3, 0, 'active', 1, NULL),
(75, 'Taxation', '2025-12-29 13:54:26', 'BBM402', 2, 4, 4, 0, 'active', 1, NULL),
(76, 'Financial Management', '2025-12-29 13:54:26', 'BBM403', 2, 4, 4, 0, 'active', 1, NULL),
(77, 'Operations Management', '2025-12-29 13:54:26', 'BBM404', 2, 4, 3, 0, 'active', 1, NULL),
(78, 'Business Environment', '2025-12-29 13:54:26', 'BBM405', 2, 4, 3, 0, 'active', 1, NULL),
(79, 'Strategic Management', '2025-12-29 13:54:26', 'BBM501', 2, 5, 4, 0, 'active', 1, NULL),
(80, 'Entrepreneurship Development', '2025-12-29 13:54:26', 'BBM502', 2, 5, 3, 0, 'active', 1, NULL),
(81, 'International Business', '2025-12-29 13:54:26', 'BBM503', 2, 5, 4, 0, 'active', 1, NULL),
(82, 'Consumer Behavior', '2025-12-29 13:54:26', 'BBM504', 2, 5, 3, 0, 'active', 1, NULL),
(83, 'E-Business', '2025-12-29 13:54:26', 'BBM505', 2, 5, 3, 0, 'active', 1, NULL),
(84, 'Project Management', '2025-12-29 13:54:26', 'BBM601', 2, 6, 4, 0, 'active', 1, NULL),
(85, 'Business Ethics', '2025-12-29 13:54:26', 'BBM602', 2, 6, 3, 0, 'active', 1, NULL),
(86, 'Investment Management', '2025-12-29 13:54:26', 'BBM603', 2, 6, 4, 0, 'active', 1, NULL),
(87, 'Services Marketing', '2025-12-29 13:54:26', 'BBM604', 2, 6, 3, 0, 'active', 1, NULL),
(88, 'Supply Chain Management', '2025-12-29 13:54:26', 'BBM605', 2, 6, 3, 0, 'active', 1, NULL),
(89, 'Internship/Project Work', '2025-12-29 13:54:26', 'BBM701', 2, 7, 6, 0, 'active', 1, NULL),
(90, 'Elective I: Digital Marketing', '2025-12-29 13:54:26', 'BBM702E1', 2, 7, 3, 1, 'active', 1, NULL),
(91, 'Elective I: Financial Markets', '2025-12-29 13:54:26', 'BBM702E2', 2, 7, 3, 1, 'active', 1, NULL),
(92, 'Elective II: HR Analytics', '2025-12-29 13:54:26', 'BBM703E1', 2, 7, 3, 1, 'active', 1, NULL),
(93, 'Elective II: International Finance', '2025-12-29 13:54:26', 'BBM703E2', 2, 7, 3, 1, 'active', 1, NULL),
(94, 'Comprehensive Project', '2025-12-29 13:54:26', 'BBM801', 2, 8, 8, 0, 'active', 1, NULL),
(95, 'Elective III: Business Analytics', '2025-12-29 13:54:26', 'BBM802E1', 2, 8, 3, 1, 'active', 1, NULL),
(96, 'Elective III: Risk Management', '2025-12-29 13:54:26', 'BBM802E2', 2, 8, 3, 1, 'active', 1, NULL),
(97, 'Elective IV: Leadership Studies', '2025-12-29 13:54:26', 'BBM803E1', 2, 8, 3, 1, 'active', 1, NULL),
(98, 'Elective IV: Corporate Governance', '2025-12-29 13:54:26', 'BBM803E2', 2, 8, 3, 1, 'active', 1, NULL),
(99, 'Introduction to Information Systems', '2025-12-29 13:55:24', 'BIM101', 3, 1, 3, 0, 'active', 1, NULL),
(100, 'Programming Fundamentals', '2025-12-29 13:55:24', 'BIM102', 3, 1, 4, 0, 'active', 1, NULL),
(101, 'Business Mathematics', '2025-12-29 13:55:24', 'BIM103', 3, 1, 4, 0, 'active', 1, NULL),
(102, 'Principles of Management', '2025-12-29 13:55:24', 'BIM104', 3, 1, 3, 0, 'active', 1, NULL),
(103, 'English Communication', '2025-12-29 13:55:24', 'BIM105', 3, 1, 3, 0, 'active', 1, NULL),
(104, 'Database Management Systems', '2025-12-29 13:55:24', 'BIM201', 3, 2, 4, 0, 'active', 1, NULL),
(105, 'Data Structures', '2025-12-29 13:55:24', 'BIM202', 3, 2, 4, 0, 'active', 1, NULL),
(106, 'Financial Accounting', '2025-12-29 13:55:24', 'BIM203', 3, 2, 3, 0, 'active', 1, NULL),
(107, 'Microeconomics', '2025-12-29 13:55:24', 'BIM204', 3, 2, 4, 0, 'active', 1, NULL),
(108, 'Digital Logic', '2025-12-29 13:55:24', 'BIM205', 3, 2, 3, 0, 'active', 1, NULL),
(109, 'Object Oriented Programming', '2025-12-29 13:55:24', 'BIM301', 3, 3, 4, 0, 'active', 1, NULL),
(110, 'Web Technology', '2025-12-29 13:55:24', 'BIM302', 3, 3, 3, 0, 'active', 1, NULL),
(111, 'Organizational Behavior', '2025-12-29 13:55:24', 'BIM303', 3, 3, 3, 0, 'active', 1, NULL),
(112, 'Business Statistics', '2025-12-29 13:55:24', 'BIM304', 3, 3, 4, 0, 'active', 1, NULL),
(113, 'System Analysis and Design', '2025-12-29 13:55:24', 'BIM305', 3, 3, 3, 0, 'active', 1, NULL),
(114, 'Operating Systems', '2025-12-29 13:55:24', 'BIM401', 3, 4, 4, 0, 'active', 1, NULL),
(115, 'Computer Networks', '2025-12-29 13:55:24', 'BIM402', 3, 4, 4, 0, 'active', 1, NULL),
(116, 'Marketing Management', '2025-12-29 13:55:24', 'BIM403', 3, 4, 3, 0, 'active', 1, NULL),
(117, 'Management Accounting', '2025-12-29 13:55:24', 'BIM404', 3, 4, 3, 0, 'active', 1, NULL),
(118, 'Software Engineering', '2025-12-29 13:55:24', 'BIM405', 3, 4, 3, 0, 'active', 1, NULL),
(119, 'Enterprise Resource Planning', '2025-12-29 13:55:24', 'BIM501', 3, 5, 4, 0, 'active', 1, NULL),
(120, 'E-Commerce', '2025-12-29 13:55:24', 'BIM502', 3, 5, 3, 0, 'active', 1, NULL),
(121, 'Human Resource Management', '2025-12-29 13:55:24', 'BIM503', 3, 5, 3, 0, 'active', 1, NULL),
(122, 'Research Methodology', '2025-12-29 13:55:24', 'BIM504', 3, 5, 3, 0, 'active', 1, NULL),
(123, 'Java Programming', '2025-12-29 13:55:24', 'BIM505', 3, 5, 4, 0, 'active', 1, NULL),
(124, 'Data Warehousing and Mining', '2025-12-29 13:55:24', 'BIM601', 3, 6, 4, 0, 'active', 1, NULL),
(125, 'Project Management', '2025-12-29 13:55:24', 'BIM602', 3, 6, 3, 0, 'active', 1, NULL),
(126, 'Strategic Management', '2025-12-29 13:55:24', 'BIM603', 3, 6, 4, 0, 'active', 1, NULL),
(127, 'Cloud Computing', '2025-12-29 13:55:24', 'BIM604', 3, 6, 3, 0, 'active', 1, NULL),
(128, 'Mobile Application Development', '2025-12-29 13:55:24', 'BIM605', 3, 6, 4, 0, 'active', 1, NULL),
(129, 'Information Security', '2025-12-29 13:55:24', 'BIM701', 3, 7, 4, 0, 'active', 1, NULL),
(130, 'Business Intelligence', '2025-12-29 13:55:24', 'BIM702', 3, 7, 3, 0, 'active', 1, NULL),
(131, 'Internship/Project Work I', '2025-12-29 13:55:24', 'BIM703', 3, 7, 6, 0, 'active', 1, NULL),
(132, 'Elective I: Digital Marketing', '2025-12-29 13:55:24', 'BIM704E1', 3, 7, 3, 1, 'active', 1, NULL),
(133, 'Elective I: Financial Management', '2025-12-29 13:55:24', 'BIM704E2', 3, 7, 3, 1, 'active', 1, NULL),
(134, 'IT Project Management', '2025-12-29 13:55:24', 'BIM801', 3, 8, 4, 0, 'active', 1, NULL),
(135, 'Comprehensive Project', '2025-12-29 13:55:24', 'BIM802', 3, 8, 8, 0, 'active', 1, NULL),
(136, 'Elective II: Business Analytics', '2025-12-29 13:55:24', 'BIM803E1', 3, 8, 3, 1, 'active', 1, NULL),
(137, 'Elective II: Entrepreneurship', '2025-12-29 13:55:24', 'BIM803E2', 3, 8, 3, 1, 'active', 1, NULL),
(138, 'Professional Ethics', '2025-12-29 13:55:24', 'BIM804', 3, 8, 3, 0, 'active', 1, NULL),
(139, 'IT Fundamentals', '2025-12-29 13:58:08', 'BBM105', 2, 1, 3, 0, 'active', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `teacher`
--

CREATE TABLE `teacher` (
  `teacher_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher`
--

INSERT INTO `teacher` (`teacher_id`, `name`, `email`, `password`, `status`, `created_at`) VALUES
(1, 'Ram Sharma', 'ram@college.com', '$2y$10$UShCXsoHuROIk2/vscxk4Ogkw.proI7SkMzZBaERMmoeeIbrCwxke', 'active', '2025-12-20 04:17:15'),
(2, 'Sita Karki', 'sita@college.com', '$2y$10$yPTtRniEnAyRkZWvZilote0yIP5E2DENGEBE7rDssFD8xql25koee', 'active', '2025-12-20 04:17:15'),
(3, 'Hari Thapa', 'harithapa895@gmail.com', '$2y$10$w/LzDjl8EreAtEnZeph/leNjfH8h61z.uGGCpu8lRT1IZWcoA2ejG', 'inactive', '2025-12-20 04:17:15'),
(4, 'Gita Rai', 'gita@college.com', '$2y$10$YCTNng5z54CWD7x98MF6gOViS0oQfw8FYINVOu4iXFcLsKc6am3ZC', 'inactive', '2025-12-20 04:17:15'),
(5, 'Samragya Sharma', 'samragyacollege@gmail.com', '$2y$10$leK3zdH5bmVFljMHSAJvPeOn5UtrlI0JtbyFGRAW8qtxucN.lKQRG', 'active', '2025-12-23 05:54:03'),
(6, 'Sagun Giri', 'sagungiri123@gmail.com', '$2y$10$or19b1dfsn2LAj73s7cBhuG6TEqRWjdPIsArnqRGXu9ew5dst.3Cm', 'active', '2025-12-25 12:18:12'),
(7, 'Ramesh Sharma', 'rameshsharma456@gmail.com', '$2y$10$C85o28jmgacv0.Z/eQZIEeFAKBqpfkHdMJtU/pgBVRElSxLCVoIdi', 'active', '2025-12-25 12:27:15'),
(8, 'Shanti Giri', 'shantigiri951@gmail.com', '$2y$10$Nl7agCX9sDscfGb35TtDAeC7ERcPi3K6BHQA4o88PVxvq.69e0Nxe', 'active', '2025-12-25 12:33:41'),
(9, 'Shyam Shah', 'shyamshah890@gmail.com', '$2y$10$indg2XEL3r6jKZpWQPIChefnIpsevq.M5kyk/CUM7vxEInZVeYFMm', 'active', '2025-12-25 12:34:53'),
(10, 'Lara Jean', 'larajean568@gmail.com', '$2y$10$j2L3G1y2S.eFHPZx/y2bPeN2DRIJyddlgtrvnIQVPj0RbkFzEPFua', 'active', '2025-12-27 15:02:16'),
(11, 'Samaya Gurung', 'samayagurung324@gmail.com', '$2y$10$dYYURem0Io4uypdmtZEFguQnEeFV06P7UxaFq9Sqb8YE0WJeRdW/u', 'active', '2025-12-27 15:20:25'),
(12, 'Nirmal Basnet', 'nirmalbasnet123@gmail.com', '$2y$10$Wkigu0x5p1ejBvpdMPTNleUAc31gzHoRncl3OXf0TJMJKC3ige.0u', 'active', '2025-12-27 15:25:16'),
(13, 'Nimesh Thapa', 'nimeshthapa123@gmail.com', '$2y$10$UGThPvMzCbf0O8uoNdy43u1avs7CYuU/4D50UrQehaqyAov3JDL8C', 'active', '2025-12-27 16:15:27'),
(14, 'Tanka Budhathoki', 'tankabudhathoki123@gmail.com', '$2y$10$KBu8naFL6xnaIeEjkmRAsePYfJTfec5C16hM5TXqvPUflqmAl9.tS', 'inactive', '2025-12-29 07:57:28'),
(15, 'Sima Rai', 'simarai134@gmail.com', '$2y$10$AooDrwhwGMjtghT.5b5ywusuZh6ncj74iqiN0QBlo0wPh4IqUhva.', 'inactive', '2025-12-29 08:09:19'),
(16, 'Nathan Frazer', 'nathanfrazer445@gmail.com', '$2y$10$5TX4DImOnKPHlwcmHzZTH.lGXt/fe21EUwT.O0iQL8cmodO7oSDa6', 'inactive', '2025-12-29 08:13:46');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_class_assignments`
--

CREATE TABLE `teacher_class_assignments` (
  `assignment_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `assigned_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_class_assignments`
--

INSERT INTO `teacher_class_assignments` (`assignment_id`, `teacher_id`, `class_id`, `assigned_date`) VALUES
(32, 10, 42, '2025-12-28 03:55:41'),
(33, 13, 42, '2025-12-28 03:55:41'),
(34, 12, 42, '2025-12-28 03:55:41'),
(35, 1, 42, '2025-12-28 03:55:41'),
(36, 7, 42, '2025-12-28 03:55:41');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_subject_assignment`
--

CREATE TABLE `teacher_subject_assignment` (
  `assignment_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `academic_year` year(4) NOT NULL DEFAULT 2025,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','completed') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_subject_assignment`
--

INSERT INTO `teacher_subject_assignment` (`assignment_id`, `teacher_id`, `subject_id`, `class_id`, `academic_year`, `start_date`, `end_date`, `status`) VALUES
(2, 1, 16, 42, '2025', '2025-01-01', NULL, 'active'),
(3, 1, 17, 42, '2025', '2025-01-01', NULL, 'active'),
(4, 2, 16, 42, '2025', '2025-07-01', NULL, 'active'),
(6, 3, 16, 42, '2025', '2025-01-01', NULL, 'active'),
(8, 3, 56, 50, '2025', '2025-01-01', NULL, 'active'),
(9, 4, 57, 50, '2025', '2025-01-01', NULL, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','teacher') NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `status`) VALUES
(1, 'samragyacollege@gmail.com', '$2y$10$leK3zdH5bmVFljMHSAJvPeOn5UtrlI0JtbyFGRAW8qtxucN.lKQRG', 'teacher', 'active'),
(2, 'sagungiri123@gmail.com', '$2y$10$or19b1dfsn2LAj73s7cBhuG6TEqRWjdPIsArnqRGXu9ew5dst.3Cm', 'teacher', 'active'),
(3, 'rameshsharma456@gmail.com', '$2y$10$x2pnmEi0FQkPTqUAuLGwoOGbXFu.9iM1qyH4nJ.Wf68jfSKk4FClS', 'teacher', 'active'),
(4, 'shantigiri951@gmail.com', '$2y$10$Nl7agCX9sDscfGb35TtDAeC7ERcPi3K6BHQA4o88PVxvq.69e0Nxe', 'teacher', 'active'),
(5, 'shyamshah890@gmail.com', '$2y$10$indg2XEL3r6jKZpWQPIChefnIpsevq.M5kyk/CUM7vxEInZVeYFMm', 'teacher', 'active'),
(6, 'larajean568@gmail.com', '$2y$10$j2L3G1y2S.eFHPZx/y2bPeN2DRIJyddlgtrvnIQVPj0RbkFzEPFua', 'teacher', 'active'),
(7, 'samayagurung324@gmail.com', '$2y$10$dYYURem0Io4uypdmtZEFguQnEeFV06P7UxaFq9Sqb8YE0WJeRdW/u', 'teacher', 'active'),
(8, 'nirmalmagar123@gmail.com', '$2y$10$Wkigu0x5p1ejBvpdMPTNleUAc31gzHoRncl3OXf0TJMJKC3ige.0u', 'teacher', 'active'),
(9, 'nimeshthapa123@gmail.com', '$2y$10$UGThPvMzCbf0O8uoNdy43u1avs7CYuU/4D50UrQehaqyAov3JDL8C', 'teacher', 'active'),
(11, 'ram@college.com', '$2y$10$UShCXsoHuROIk2/vscxk4Ogkw.proI7SkMzZBaERMmoeeIbrCwxke', 'teacher', 'active'),
(12, 'sita@college.com', '$2y$10$yPTtRniEnAyRkZWvZilote0yIP5E2DENGEBE7rDssFD8xql25koee', 'teacher', 'active'),
(13, 'harithapa895@gmail.com', '$2y$10$w/LzDjl8EreAtEnZeph/leNjfH8h61z.uGGCpu8lRT1IZWcoA2ejG', 'teacher', 'active'),
(14, 'gita@college.com', '$2y$10$YCTNng5z54CWD7x98MF6gOViS0oQfw8FYINVOu4iXFcLsKc6am3ZC', 'teacher', 'inactive');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `administrator`
--
ALTER TABLE `administrator`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `class`
--
ALTER TABLE `class`
  ADD PRIMARY KEY (`class_id`),
  ADD UNIQUE KEY `unique_bca_class` (`faculty`,`semester`,`batch_year`);

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`faculty_id`),
  ADD UNIQUE KEY `faculty_code` (`faculty_code`),
  ADD UNIQUE KEY `faculty_code_2` (`faculty_code`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_user` (`user_id`,`user_type`),
  ADD KEY `idx_unread` (`is_read`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `fk_payment_student` (`student_id`);

--
-- Indexes for table `result`
--
ALTER TABLE `result`
  ADD PRIMARY KEY (`result_id`),
  ADD KEY `fk_result_student` (`student_id`),
  ADD KEY `fk_result_subject` (`subject_id`),
  ADD KEY `fk_result_semester` (`semester_id`);

--
-- Indexes for table `result_history`
--
ALTER TABLE `result_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `idx_result` (`result_id`);

--
-- Indexes for table `semester`
--
ALTER TABLE `semester`
  ADD PRIMARY KEY (`semester_id`);

--
-- Indexes for table `semester_progress`
--
ALTER TABLE `semester_progress`
  ADD PRIMARY KEY (`progress_id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_student_class` (`class_id`),
  ADD KEY `fk_student_semester` (`semester_id`);

--
-- Indexes for table `student_subject_enrollment`
--
ALTER TABLE `student_subject_enrollment`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `subject`
--
ALTER TABLE `subject`
  ADD PRIMARY KEY (`subject_id`),
  ADD UNIQUE KEY `subject_code` (`subject_code`);

--
-- Indexes for table `teacher`
--
ALTER TABLE `teacher`
  ADD PRIMARY KEY (`teacher_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `teacher_class_assignments`
--
ALTER TABLE `teacher_class_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD UNIQUE KEY `unique_assignment` (`teacher_id`,`class_id`),
  ADD KEY `idx_teacher` (`teacher_id`),
  ADD KEY `idx_class` (`class_id`);

--
-- Indexes for table `teacher_subject_assignment`
--
ALTER TABLE `teacher_subject_assignment`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `administrator`
--
ALTER TABLE `administrator`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `class`
--
ALTER TABLE `class`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `faculty`
--
ALTER TABLE `faculty`
  MODIFY `faculty_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `result`
--
ALTER TABLE `result`
  MODIFY `result_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `result_history`
--
ALTER TABLE `result_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `semester`
--
ALTER TABLE `semester`
  MODIFY `semester_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `semester_progress`
--
ALTER TABLE `semester_progress`
  MODIFY `progress_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_subject_enrollment`
--
ALTER TABLE `student_subject_enrollment`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `subject`
--
ALTER TABLE `subject`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=140;

--
-- AUTO_INCREMENT for table `teacher`
--
ALTER TABLE `teacher`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `teacher_class_assignments`
--
ALTER TABLE `teacher_class_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `teacher_subject_assignment`
--
ALTER TABLE `teacher_subject_assignment`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `fk_payment_student` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `result`
--
ALTER TABLE `result`
  ADD CONSTRAINT `fk_result_semester` FOREIGN KEY (`semester_id`) REFERENCES `semester` (`semester_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_result_student` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_result_subject` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`subject_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `result_history`
--
ALTER TABLE `result_history`
  ADD CONSTRAINT `result_history_ibfk_1` FOREIGN KEY (`result_id`) REFERENCES `result` (`result_id`) ON DELETE CASCADE;

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `fk_student_class` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_student_semester` FOREIGN KEY (`semester_id`) REFERENCES `semester` (`semester_id`) ON UPDATE CASCADE;

--
-- Constraints for table `student_subject_enrollment`
--
ALTER TABLE `student_subject_enrollment`
  ADD CONSTRAINT `student_subject_enrollment_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`),
  ADD CONSTRAINT `student_subject_enrollment_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`subject_id`),
  ADD CONSTRAINT `student_subject_enrollment_ibfk_3` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`);

--
-- Constraints for table `teacher_class_assignments`
--
ALTER TABLE `teacher_class_assignments`
  ADD CONSTRAINT `teacher_class_assignments_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`teacher_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_class_assignments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher_subject_assignment`
--
ALTER TABLE `teacher_subject_assignment`
  ADD CONSTRAINT `teacher_subject_assignment_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`teacher_id`),
  ADD CONSTRAINT `teacher_subject_assignment_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`subject_id`),
  ADD CONSTRAINT `teacher_subject_assignment_ibfk_3` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
