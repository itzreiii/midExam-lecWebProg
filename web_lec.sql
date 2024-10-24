-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 24 Okt 2024 pada 12.51
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

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
-- Struktur dari tabel `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `location` varchar(200) NOT NULL,
  `max_participants` int(11) NOT NULL,
  `current_participants` int(11) DEFAULT 0,
  `status` enum('open','closed','canceled') DEFAULT 'open',
  `image_path` varchar(255) DEFAULT NULL,
  `banner_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `events`
--

INSERT INTO `events` (`id`, `name`, `description`, `date`, `time`, `location`, `max_participants`, `current_participants`, `status`, `image_path`, `banner_path`, `created_at`, `updated_at`, `image`) VALUES
(1, 'asdz', '123', '2030-12-12', '12:12:00', 'asd', 1, 1, 'closed', NULL, NULL, '2024-10-12 08:47:01', '2024-10-24 09:15:25', '../uploads/Music is the breath of my soul, bringing every moment in my life to life. (1).png'),
(2, 'tes', 'testestesetes', '2024-10-26', '12:00:00', 'Mars', 50, 1, 'open', NULL, NULL, '2024-10-22 05:59:12', '2024-10-23 14:12:14', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `event_registrations`
--

CREATE TABLE `event_registrations` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(10) DEFAULT 'Accepted'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `event_registrations`
--

INSERT INTO `event_registrations` (`id`, `event_id`, `user_id`, `registration_date`, `status`) VALUES
(1, 1, 4, '2024-10-20 05:39:06', 'Accepted'),
(4, 2, 2, '2024-10-22 15:57:05', 'confirmed');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `account_activation_hash` varchar(64) DEFAULT NULL,
  `reset_token_hash` varchar(64) DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `account_activation_hash`, `reset_token_hash`, `reset_token_expires_at`) VALUES
(1, 'ray', 'ray@gmail.com', '$2y$10$vCFPPlVqSlPsbApH3J0n7eI4wEGJ9FteXl1RfcxrFeV16fCZCtEe.', 'admin', '2024-10-11 07:57:02', NULL, NULL, NULL),
(2, 'rafpo', 'rafpo@a.com', '$2y$10$KdW/6vmWCgeW8ihM1.x/TO2134LyU4rA2P0dOTaRKzT/y.8BWt5m2', 'user', '2024-10-11 08:57:46', NULL, NULL, NULL),
(3, 'rafpo2', 'rafpo2@rafpo2.com', '$2y$10$lpsRjLby4iX9iD3K9n9reO4OeDI2cyXYSkp2Y5G/KhVMyUpUiV1Tq', 'user', '2024-10-12 07:59:06', NULL, NULL, NULL),
(4, 'asd', 'asd@asd', '$2y$10$7/VGuBMDl05/2UIQcr2AAutCal7Ko6GDI7m33PYEN6Xykm1Drhfju', 'user', '2024-10-20 05:35:58', NULL, NULL, NULL),
(5, 'bry', 'bry@bry.com', '$2y$10$qGEz8suQxZAfXtPUwTZGFugCQSQwhH0Bb4u07YqzM4jK6pW/tF.rq', 'user', '2024-10-22 04:52:37', NULL, NULL, NULL),
(6, 'ggg', 'ggg@ggg.com', '$2y$10$fIXtr8lNijYl3KhZ/0oUduIUdABTEehub//vDFaBRKXPABsYISc.e', 'user', '2024-10-22 16:10:08', '511aaa7e077e6a4ac89e1d2d54dadd1fcaa023e27d4f841f49b765abcf9a5360', NULL, NULL),
(7, '3123123', '32131@eafdsaf.com', '$2y$10$l5PvB391Kctj/Uion62bGeS4eTyvm7StpKEqTC71sJXf/ukFW1zOq', 'user', '2024-10-22 16:23:27', '9a23c6f07da825f198c2d4b39e3b5dd3c473c1f56e649e67401272d05f247ae9', NULL, NULL),
(8, '23123', 'fdsa@gsga.com', '$2y$10$QnnwdsAn9HyaEXkQCtEr.Ou4gopE4U.Z1PCJy1XvgFG3C2ADVR0OK', 'user', '2024-10-22 16:25:40', 'cc4aee57bb0dae71b3c73a3342d907c0aae519a82dfc6db738b51cff4789aeaf', NULL, NULL),
(9, '23123', 'ggggg@gagdsg.com', '$2y$10$8ivrxUbC9cONpvEUgMKZJuqGJuSeWe.hGUsZhLPDZhIh2HrSwRe32', 'user', '2024-10-22 16:26:46', '15a853cfd9cd844c744b4ab27fe24584eb301fd80a974aa09864180877d2b9ed', NULL, NULL),
(16, 'pooooooooooooooo', 'zoom.elraffs2@gmail.com', '$2y$10$X1CKQY8kOA.XG7JL1zurOOjnM9J.QNhMp4mCR9pFtEuJ0A7hzWtXO', 'user', '2024-10-22 17:08:08', NULL, NULL, NULL),
(17, '32131', 'fdsafda@ggffffsa.com', '$2y$10$HZluF0OhbhdaGUYLWUc06Oa/DAG.flbHTMFVyXhQ1d6trDejwYxO2', 'user', '2024-10-22 17:09:43', 'd33f5769d3ff3a2751e5560248c1553e02d5eca8a87354fca09ab2a908097ac6', NULL, NULL),
(22, 'eka', 'ekandra2204@gmail.com', '$2y$10$XQ84uxC.tF4gdvGoi9m40eVnyHaF1bKDK7uujTQqCSkZOT.bfmEYy', 'user', '2024-10-23 14:10:04', NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `account_activation_hash` (`account_activation_hash`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `event_registrations`
--
ALTER TABLE `event_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD CONSTRAINT `event_registrations_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_registrations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
