-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 25, 2025 at 07:49 PM
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
-- Database: `tarevent_management_system_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'user',
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `club_id` bigint(20) UNSIGNED DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `program` varchar(255) DEFAULT NULL,
  `student_id` varchar(255) DEFAULT NULL,
  `interested_categories` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`interested_categories`)),
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` varchar(20) NOT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `permissions`, `club_id`, `profile_photo`, `phone`, `program`, `student_id`, `interested_categories`, `remember_token`, `created_at`, `updated_at`, `status`, `last_login_at`) VALUES
(1, 'Tang Lit Xuan', 'tanglx598@gmail.com', '2025-12-17 07:06:00', '$2y$12$KgHe7REH7nubP7VkOKT6IumQoug3IHCe0Lw1G7hJMe3qAht85FUFW', 'super_admin', NULL, NULL, 'avatars/oOEogePVprPKI5rbWOsOrA8LlF82dFP33TEvXIvK.jpg', NULL, NULL, '24PMD10367', NULL, '3fxB0i7PCrDlMxnZVuUHOXtDyoxaxJtHitj2n6lO5bOWQiOYJu3aZQD16GqK', '2025-12-17 06:15:39', '2025-12-25 17:01:27', 'active', '2025-12-25 17:01:27'),
(2, 'Tang Tang', 'tanglx-pm22@student.tarc.edu.my', '2025-12-17 07:06:00', '$2y$12$AbwErRO3n9OLvC7duqoP2usD4XKTyng5dAWmQ5cTfPkch6Xu6QudW', 'student', NULL, NULL, 'avatars/s0YlHePr6qYlsxWR6wY1iJIf8QiH139vvh7e7uds.jpg', '01135154278', 'BIBM', '24PMD10366', NULL, NULL, '2025-12-17 06:58:28', '2025-12-19 00:28:15', 'active', '2025-12-19 00:28:15'),
(5, 'Xuan Tang', 'tangxuan689@gmail.com', '2025-12-18 02:15:11', '$2y$12$7FWi14n.77/2c3Ww4gMKl.FCKU2oK994SQMtAKMfuoLi25lSASBru', 'club', NULL, NULL, NULL, NULL, 'BIBM', '24PMD10369', NULL, 'DgiXS1HnTvER9TRsv5mrwUzJcnqv9HncVzzjdBs9gfao7ThPL8ES8uV9DKpF', '2025-12-18 02:15:11', '2025-12-25 17:44:21', 'active', '2025-12-25 17:44:21'),
(6, 'Tang Tang Lit', 'tanklit689@gmail.com', '2025-12-24 05:51:04', '$2y$12$v4ax7n2nmSc3dNKo1Ekt0eTUA4NDl/rbkaBBqRVdlRvRLJFTQTqXa', 'student', NULL, NULL, 'avatars/WRh0tWeB51UGE6Bg1EvPOV3vxVQ2Hl5S1g1Rg59I.jpg', '60121312123', 'BME', '24PMD10370', NULL, 'bq1ncTDmbYncc48FHjNIGsEWyUDxEONx8A54g4bPNgAVyrNodkzCPjNwFTQ2', '2025-12-18 02:30:34', '2025-12-25 17:37:48', 'active', '2025-12-25 17:37:48'),
(8, 'GG', 'lxtang598@gmail.com', '2025-12-18 04:04:39', '$2y$12$4nDDNtEW4LZHPd7SE4S76.pTM46J8Nmp0ht3XxzTUAvuaIR/b6ELi', 'admin', '[\"view_users\",\"update_user\",\"view_user_details\",\"view_administrators\",\"delete_administrator\"]', NULL, 'avatars/rsxxMfVMQsW6Ocxgh9xz8JnW24VxM0grKi2H8ild.jpg', '601112244312', NULL, NULL, NULL, NULL, '2025-12-18 04:04:39', '2025-12-24 07:17:42', 'active', '2025-12-24 07:17:42'),
(11, 'Tang Tang Tang', 'xuannn689@gmail.com', '2025-12-19 05:01:26', '$2y$12$JrYFvS1lCIxI.MtSnuGP4ex69Y8oFKPDNcv3cMHUjI7SNjegnf80q', 'admin', '[\"view_users\",\"create_user\",\"update_user\",\"delete_user\",\"view_user_details\",\"toggle_user_status\"]', NULL, NULL, NULL, 'BSCM', '24PMD10371', NULL, 'umo5BfZur8EP2T54p8byCAcpQQz51jR9eRDzL3kRh9W7h7Z6oFXLatPTVZVI', '2025-12-19 04:59:35', '2025-12-19 06:49:36', 'active', '2025-12-19 06:49:36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD KEY `users_club_id_foreign` (`club_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_club_id_foreign` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
