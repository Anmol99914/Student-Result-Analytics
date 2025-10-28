-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Oct 28, 2025 at 11:07 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `student_result`
--

-- --------------------------------------------------------

--
-- Table structure for table `administrator`
--

CREATE TABLE `administrator` (
  `admin_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class`
--

CREATE TABLE `class` (
  `class_id` int(11) NOT NULL,
  `class_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class`
--

INSERT INTO `class` (`class_id`, `class_name`) VALUES
(1, 'BCA 1st Year'),
(2, 'BCA 2nd Year'),
(3, 'BCA 3rd Year'),
(4, 'BCA 4th Year');

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
  `semester_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `result`
--

INSERT INTO `result` (`result_id`, `student_id`, `subject_id`, `marks_obtained`, `total_marks`, `semester_id`) VALUES
(1, 'BCA108', 1, 78, 100, 4),
(2, 'BCA108', 2, 85, 100, 4),
(3, 'BCA108', 3, 90, 100, 4),
(4, 'BCA105', 1, 65, 100, 3),
(5, 'BCA105', 2, 72, 100, 3),
(6, 'BCA105', 3, 80, 100, 3),
(7, 'BCA112', 1, 95, 100, 4),
(8, 'BCA112', 2, 88, 100, 4),
(9, 'BCA112', 3, 92, 100, 4),
(10, 'BCA104', 4, 70, 100, 1),
(11, 'BCA104', 5, 60, 100, 1),
(12, 'BCA101', 1, 55, 100, 4),
(13, 'BCA101', 2, 45, 100, 4),
(14, 'BCA103', 3, 80, 100, 1),
(15, 'BCA103', 4, 90, 100, 1),
(16, 'BCA103', 1, 60, 100, 1),
(17, 'BCA107', 2, 75, 100, 4),
(18, 'BCA107', 3, 85, 100, 4),
(19, 'BCA108', 4, 88, 100, 4),
(20, 'BCA108', 6, 92, 100, 4),
(21, 'BCA109', 7, 89, 100, 4),
(22, 'BCA109', 6, 95, 100, 4),
(23, 'BCA110', 8, 90, 100, 4),
(24, 'BCA106', 7, 95, 100, 3),
(25, 'BCA106', 5, 92, 100, 3),
(26, '0', 4, 89, 100, 5),
(27, '0', 2, 91, 100, 5),
(28, '0', 3, 89, 100, 5);

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
(1, '1st Semester'),
(2, '2nd Semester'),
(3, '3rd Semester'),
(4, '4th Semester'),
(5, '5th Semester'),
(6, '6th Semester'),
(7, '7th Semester'),
(8, '8th Semester');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `student_id` varchar(10) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `class_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `phone_number` varchar(10) NOT NULL,
  `old_numeric_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`student_id`, `student_name`, `email`, `class_id`, `semester_id`, `phone_number`, `old_numeric_id`) VALUES
('BCA101', 'Ross Geller', 'ross@gmail.com', 3, 4, '9801000001', 101),
('BCA102', 'Rachel Green', 'rachel@gmail.com', 1, 1, '9801000002', 102),
('BCA103', 'Sheldon Cooper', 'sheldon@gmail.com', 1, 1, '9801000003', 103),
('BCA104', 'Leonard Hofstadter', 'leonard@gmail.com', 1, 1, '9801000004', 104),
('BCA105', 'Barney Stinson', 'barney@gmail.com', 2, 3, '9801000005', 105),
('BCA106', 'Ted Mosby', 'ted@gmail.com', 2, 3, '9801000006', 106),
('BCA107', 'Jake Peralta', 'jake@gmail.com', 2, 4, '9801000007', 107),
('BCA108', 'Amy Santiago', 'amy@gmail.com', 2, 4, '9801000008', 108),
('BCA109', 'Tony Stark', 'tony@gmail.com', 3, 4, '9801000009', 109),
('BCA110', 'Steve Rogers', 'steve@gmail.com', 3, 4, '9801000010', 110),
('BCA111', 'Monica Geller', 'monica@gmail.com', 3, 4, '9812254555', 111),
('BCA112', 'Chandler Bing', 'chanchanman@gmail.com', 3, 4, '9851452654', 112);

-- --------------------------------------------------------

--
-- Table structure for table `subject`
--

CREATE TABLE `subject` (
  `subject_id` int(11) NOT NULL,
  `subject_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subject`
--

INSERT INTO `subject` (`subject_id`, `subject_name`) VALUES
(1, 'Mathematics I'),
(2, 'English Communication'),
(3, 'Computer Fundamentals'),
(4, 'Database Management System'),
(5, 'Digital Logic'),
(6, 'Social Studies'),
(7, 'Financial Accounting'),
(8, 'Software Engineering');

-- --------------------------------------------------------

--
-- Table structure for table `teacher`
--

CREATE TABLE `teacher` (
  `teacher_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  ADD PRIMARY KEY (`class_id`);

--
-- Indexes for table `result`
--
ALTER TABLE `result`
  ADD PRIMARY KEY (`result_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `result_ibfk_1` (`semester_id`);

--
-- Indexes for table `semester`
--
ALTER TABLE `semester`
  ADD PRIMARY KEY (`semester_id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `semester_id` (`semester_id`);

--
-- Indexes for table `subject`
--
ALTER TABLE `subject`
  ADD PRIMARY KEY (`subject_id`);

--
-- Indexes for table `teacher`
--
ALTER TABLE `teacher`
  ADD PRIMARY KEY (`teacher_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `administrator`
--
ALTER TABLE `administrator`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `class`
--
ALTER TABLE `class`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `result`
--
ALTER TABLE `result`
  MODIFY `result_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `semester`
--
ALTER TABLE `semester`
  MODIFY `semester_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `subject`
--
ALTER TABLE `subject`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `teacher`
--
ALTER TABLE `teacher`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `result`
--
ALTER TABLE `result`
  ADD CONSTRAINT `result_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`subject_id`);

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `student_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`),
  ADD CONSTRAINT `student_ibfk_2` FOREIGN KEY (`semester_id`) REFERENCES `semester` (`semester_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
