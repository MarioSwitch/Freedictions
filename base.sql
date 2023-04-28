-- phpMyAdmin
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Server version: 5.7.23-23
-- PHP Version: 7.3.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `yoshiwe1_twitchpredi`
--

-- --------------------------------------------------------

--
-- Table structure for table `predictions`
--

CREATE TABLE `predictions` (
  `id` int(11) NOT NULL,
  `title` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `author` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `endDate` timestamp DEFAULT NULL,
  `correctAnswer` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `predictionsChoices`
--

CREATE TABLE `predictionsChoices` (
  `id` int(11) NOT NULL,
  `prediction` int(11) DEFAULT NULL,
  `choice` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `username` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `nickname` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `hash_pwd` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `bio` varchar(5000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `points` bigint(20) NOT NULL,
  `isAdmin` tinyint(1) NOT NULL,
  `lastConnection` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usersChoices`
--

CREATE TABLE `usersChoices` (
  `username` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `prediction` int(11) NOT NULL,
  `choice` int(11) DEFAULT NULL,
  `pointsSpent` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for tables
--

--
-- Indexes for table `predictions`
--
ALTER TABLE `predictions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `predictionsChoices`
--
ALTER TABLE `predictionsChoices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `usersChoices`
--
ALTER TABLE `usersChoices`
  ADD PRIMARY KEY (`username`,`prediction`);

--
-- AUTO_INCREMENT for tables
--

--
-- AUTO_INCREMENT for table `predictions`
--
ALTER TABLE `predictions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `predictionsChoices`
--
ALTER TABLE `predictionsChoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
