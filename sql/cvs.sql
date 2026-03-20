-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 07, 2026 at 06:41 AM
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
-- Database: `astoncv`
--

-- --------------------------------------------------------

--
-- Table structure for table `cvs`
--

CREATE TABLE `cvs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `keyprogramming` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `education` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `URLlinks` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `work_experience` text DEFAULT NULL,
  `view_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cvs`
--

INSERT INTO `cvs` (`id`, `name`, `email`, `password`, `keyprogramming`, `profile`, `education`, `URLlinks`, `profile_picture`, `skills`, `work_experience`, `view_count`) VALUES
(2, 'Samuel Acquah', 'samuel.acquah@aston.ac.uk', '$2y$10$Nn5P0CkhBHxclMJf52AqzuATJWcOiyPZyZod/ITtLRo8lIe9NghGG', 'C++', 'Third year Computer Science student at Aston University with a strong interest in backend development and embedded systems.', 'BSc Computer Science, Aston University (2022-2026)', 'https://github.com/samuelacquah', NULL, 'C++, Python, Java, Docker, MySQL', 'Software Intern at Zaccess Inc (2024) - worked on backend APIs and IoT device firmware.', 0),
(3, 'Mabel Adjei', 'mabel.adjei@aston.ac.uk', '$2y$10$x1DXroOnbCT5Ia2GEabWMunD6GdYKSxMaZhIRN.hGuzG2mj5pmkb6', 'Python', 'Second year Software Engineering student passionate about data science and full-stack web development.', 'BSc Software Engineering, Aston University (2023-2027)', 'https://github.com/mabeladjei', NULL, 'Python, React, MySQL, JUnit, Flask', 'Junior Developer at Zactronics Ltd (2024) - built React dashboards and Python data pipelines.', 0),
(4, 'Pamela Awuah', 'pamela.awuah@aston.ac.uk', '$2y$10$5RC.hysUhNqNw1EGi4lBWu5Z6Lyw9.xB/3NJSJ9KwWk7wssLKw/yy', 'JavaScript', 'First year Electronic Engineering and Computer Science student interested in web development and UI design.', 'BEng Electronic Engineering and Computer Science, Aston University (2025-2029)', 'https://github.com/pamelaawuah', '', 'JavaScript, HTML, CSS, React, Node.js, MySQL', '', 2),
(5, 'Calvin Mensah', 'calvin.mensah@aston.ac.uk', '$2y$10$Fv76PbCNEIvBkzrZiuL55uXWBvuOPeP4Asp6xvNEvBRhWGdgnOinK', 'Java', 'Fourth year Computer Science student specialising in enterprise software development and cloud computing.', 'BSc Computer Science, Aston University (2021-2025)', 'https://github.com/calvinmensah', NULL, 'Java, Spring Boot, MySQL, Docker, AWS', 'Graduate Software Engineer at Zactronics Ltd (2023-2024) - developed Java microservices and deployed on AWS.', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cvs`
--
ALTER TABLE `cvs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cvs`
--
ALTER TABLE `cvs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
