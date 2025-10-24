-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Oct 24, 2025 at 05:45 PM
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
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `marks_obtained` int(11) NOT NULL,
  `total_marks` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `result`
--

INSERT INTO `result` (`result_id`, `student_id`, `subject_id`, `marks_obtained`, `total_marks`, `semester_id`) VALUES
(1, 101, 1, 78, 100, 1),
(2, 101, 2, 85, 100, 1),
(3, 101, 3, 90, 100, 1),
(4, 102, 1, 65, 100, 1),
(5, 102, 2, 72, 100, 1),
(6, 102, 3, 80, 100, 1),
(7, 103, 1, 95, 100, 1),
(8, 103, 2, 88, 100, 1),
(9, 103, 3, 92, 100, 1),
(10, 104, 4, 70, 100, 1),
(11, 104, 5, 60, 100, 1),
(12, 105, 1, 55, 100, 3),
(13, 105, 2, 45, 100, 3),
(14, 106, 3, 80, 100, 3),
(15, 106, 4, 90, 100, 3),
(16, 107, 1, 60, 100, 4),
(17, 107, 2, 75, 100, 4),
(18, 108, 3, 85, 100, 4),
(19, 108, 4, 88, 100, 4),
(20, 109, 6, 92, 100, 5),
(21, 109, 7, 89, 100, 5),
(22, 110, 6, 95, 100, 5),
(23, 111, 8, 90, 100, 5),
(24, 111, 7, 95, 100, 5),
(25, 111, 5, 92, 100, 5),
(26, 112, 4, 89, 100, 5),
(27, 112, 2, 91, 100, 5),
(28, 112, 3, 89, 100, 5);

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
  `student_id` int(11) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `class_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `phone_number` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`student_id`, `student_name`, `email`, `class_id`, `semester_id`, `phone_number`) VALUES
(101, 'Ross Geller', 'ross@gmail.com', 3, 4, '9801000001'),
(102, 'Rachel Green', 'rachel@gmail.com', 1, 1, '9801000002'),
(103, 'Sheldon Cooper', 'sheldon@gmail.com', 1, 1, '9801000003'),
(104, 'Leonard Hofstadter', 'leonard@gmail.com', 1, 1, '9801000004'),
(105, 'Barney Stinson', 'barney@gmail.com', 2, 3, '9801000005'),
(106, 'Ted Mosby', 'ted@gmail.com', 2, 3, '9801000006'),
(107, 'Jake Peralta', 'jake@gmail.com', 2, 4, '9801000007'),
(108, 'Amy Santiago', 'amy@gmail.com', 2, 4, '9801000008'),
(109, 'Tony Stark', 'tony@gmail.com', 3, 4, '9801000009'),
(110, 'Steve Rogers', 'steve@gmail.com', 3, 4, '9801000010'),
(111, 'Monica Geller', 'monica@gmail.com', 3, 4, '9812254555'),
(112, 'Chandler Bing', 'chanchanman@gmail.com', 3, 4, '9851452654');

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
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

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
  ADD CONSTRAINT `result_ibfk_1` FOREIGN KEY (`semester_id`) REFERENCES `semester` (`semester_id`),
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
