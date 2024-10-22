-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 22, 2024 at 08:02 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `web_lec`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'ray', 'ray@gmail.com', '$2y$10$vCFPPlVqSlPsbApH3J0n7eI4wEGJ9FteXl1RfcxrFeV16fCZCtEe.', 'admin', '2024-10-11 07:57:02'),
(2, 'rafpo', 'rafpo@a.com', '$2y$10$KdW/6vmWCgeW8ihM1.x/TO2134LyU4rA2P0dOTaRKzT/y.8BWt5m2', 'user', '2024-10-11 08:57:46'),
(3, 'rafpo2', 'rafpo2@rafpo2.com', '$2y$10$lpsRjLby4iX9iD3K9n9reO4OeDI2cyXYSkp2Y5G/KhVMyUpUiV1Tq', 'user', '2024-10-12 07:59:06'),
(4, 'asd', 'asd@asd', '$2y$10$7/VGuBMDl05/2UIQcr2AAutCal7Ko6GDI7m33PYEN6Xykm1Drhfju', 'user', '2024-10-20 05:35:58'),
(5, 'bry', 'bry@bry.com', '$2y$10$qGEz8suQxZAfXtPUwTZGFugCQSQwhH0Bb4u07YqzM4jK6pW/tF.rq', 'user', '2024-10-22 04:52:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
