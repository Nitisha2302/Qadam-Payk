-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 21, 2025 at 12:35 PM
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
-- Database: `qadamapyk`
--

-- --------------------------------------------------------

--
-- Table structure for table `car_models`
--

CREATE TABLE `car_models` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `model_name` varchar(255) NOT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `seats` int(11) DEFAULT NULL,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `car_models`
--

INSERT INTO `car_models` (`id`, `model_name`, `brand`, `color`, `seats`, `features`, `created_at`, `updated_at`) VALUES
(1, 'beetle', 'volkswagen beetle', 'yellow', NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:51:14', '2025-09-20 11:51:14'),
(2, 'N', 'scorpio', 'black', NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:51:14', '2025-09-20 11:51:14'),
(3, '124 Spider', 'Abarth', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:51:14', '2025-09-20 11:51:14'),
(4, '500', 'Abarth', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:51:14', '2025-09-20 11:51:14'),
(5, '600e', 'Abarth', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:51:14', '2025-09-20 11:51:14'),
(6, '378 GT Zagato', 'AC', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:51:14', '2025-09-20 11:51:14'),
(7, 'Ace', 'AC', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:51:14', '2025-09-20 11:51:14'),
(8, 'Aceca', 'AC', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:51:14', '2025-09-20 11:51:14'),
(9, 'Cobra', 'AC', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:51:14', '2025-09-20 11:51:14'),
(10, 'ADX', 'Acura', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:51:14', '2025-09-20 11:51:14'),
(11, 'RDX', 'Acura', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:53:23', '2025-09-20 11:53:23'),
(12, 'NSX', 'Acura', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:53:23', '2025-09-20 11:53:23'),
(13, 'MDX', 'Acura', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:53:23', '2025-09-20 11:53:23'),
(14, 'Legend', 'Acura', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:53:23', '2025-09-20 11:53:23'),
(15, 'Integra', 'Acura', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:53:23', '2025-09-20 11:53:23'),
(16, 'ILX', 'Acura', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:53:23', '2025-09-20 11:53:23'),
(17, 'EL', 'Acura', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:53:23', '2025-09-20 11:53:23'),
(18, 'CSX', 'Acura', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:53:23', '2025-09-20 11:53:23'),
(19, 'CL', 'Acura', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:53:23', '2025-09-20 11:53:23'),
(20, 'CDX', 'Acura', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:53:23', '2025-09-20 11:53:23'),
(21, 'Trumpf Junior', 'Adler', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:54:09', '2025-09-20 11:54:09'),
(22, 'Diplomat', 'Adler', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:54:09', '2025-09-20 11:54:09'),
(23, 'ZDX', 'Acura', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:54:09', '2025-09-20 11:54:09'),
(24, 'TSX', 'Acura', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:54:09', '2025-09-20 11:54:09'),
(25, 'TLX', 'Acura', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:54:09', '2025-09-20 11:54:09'),
(26, 'TL', 'Acura', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:54:09', '2025-09-20 11:54:09'),
(27, 'SLX', 'Acura', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:54:09', '2025-09-20 11:54:09'),
(28, 'RSX', 'Acura', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:54:09', '2025-09-20 11:54:09'),
(29, 'RLX', 'Acura', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:54:09', '2025-09-20 11:54:09'),
(30, 'RL', 'Acura', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:54:09', '2025-09-20 11:54:09'),
(31, 'A4', 'Audi', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:55:38', '2025-09-20 11:55:38'),
(32, 'A3', 'Audi', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:55:38', '2025-09-20 11:55:38'),
(33, 'A2', 'Audi', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:55:38', '2025-09-20 11:55:38'),
(34, 'A1', 'Audi', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:55:38', '2025-09-20 11:55:38'),
(35, '920', 'Audi', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:55:38', '2025-09-20 11:55:38'),
(36, '90', 'Audi', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:55:38', '2025-09-20 11:55:38'),
(37, '80', 'Audi', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:55:38', '2025-09-20 11:55:38'),
(38, '50', 'Audi', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:55:38', '2025-09-20 11:55:38'),
(39, '200', 'Audi', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:55:38', '2025-09-20 11:55:38'),
(40, '100', 'Audi', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:55:38', '2025-09-20 11:55:38'),
(41, 'Cabriolet', 'Audi', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:57:32', '2025-09-20 11:57:32'),
(42, 'Allroad', 'Audi', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:57:32', '2025-09-20 11:57:32'),
(43, 'A8', 'Audi', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:57:32', '2025-09-20 11:57:32'),
(44, 'A7', 'Audi', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:57:32', '2025-09-20 11:57:32'),
(45, 'A7', 'Audi', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:57:32', '2025-09-20 11:57:32'),
(46, 'A6 e-tron', 'Audi', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:57:32', '2025-09-20 11:57:32'),
(47, 'A6 allroad', 'Audi', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:57:32', '2025-09-20 11:57:32'),
(48, 'A6', 'Audi', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:57:32', '2025-09-20 11:57:32'),
(49, 'A5', 'Audi', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:57:32', '2025-09-20 11:57:32'),
(50, 'A4 allroad', 'Audi', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:57:32', '2025-09-20 11:57:32');

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `city_name` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cities`
--

INSERT INTO `cities` (`id`, `city_name`, `state`, `country`, `created_at`, `updated_at`) VALUES
(1, 'Chandigarh', 'Chandigarh', 'India', '2025-09-20 11:59:48', '2025-09-20 11:59:48'),
(2, 'Bathinda', 'Punjab', 'India', '2025-09-20 11:59:48', '2025-09-20 11:59:48'),
(3, 'Душанбе', NULL, 'Тоҷикистон', '2025-09-20 11:59:48', '2025-09-20 11:59:48'),
(4, 'Хуҷанд', NULL, 'Тоҷикистон', '2025-09-20 11:59:48', '2025-09-20 11:59:48'),
(5, 'Бохтар', NULL, 'Тоҷикистон', '2025-09-20 11:59:48', '2025-09-20 11:59:48'),
(6, 'Кӯлоб', NULL, 'Тоҷикистон', '2025-09-20 11:59:48', '2025-09-20 11:59:48'),
(7, 'Истаравшан', NULL, 'Тоҷикистон', '2025-09-20 11:59:48', '2025-09-20 11:59:48'),
(8, 'Панҷакент', NULL, 'Тоҷикистон', '2025-09-20 11:59:48', '2025-09-20 11:59:48'),
(9, 'Ваҳдат', NULL, 'Тоҷикистон', '2025-09-20 11:59:48', '2025-09-20 11:59:48'),
(10, 'Ҳисор', NULL, 'Тоҷикистон', '2025-09-20 11:59:48', '2025-09-20 11:59:48'),
(11, 'Хоруғ', NULL, 'Тоҷикистон', '2025-09-20 12:00:41', '2025-09-20 12:00:41'),
(12, 'Норак', NULL, 'Тоҷикистон', '2025-09-20 12:00:41', '2025-09-20 12:00:41'),
(13, 'Гулистон', NULL, 'Тоҷикистон', '2025-09-20 12:00:41', '2025-09-20 12:00:41'),
(14, 'Истиқлол', NULL, 'Тоҷикистон', '2025-09-20 12:00:41', '2025-09-20 12:00:41'),
(15, 'Исфара', NULL, 'Тоҷикистон', '2025-09-20 12:00:41', '2025-09-20 12:00:41'),
(16, 'Ойбек', NULL, 'Тоҷикистон', '2025-09-20 12:00:41', '2025-09-20 12:00:41'),
(17, 'Тошкент', NULL, 'Узбекистон', '2025-09-20 12:00:41', '2025-09-20 12:00:41'),
(18, 'Самарқанд', NULL, 'Узбекистон', '2025-09-20 12:00:41', '2025-09-20 12:00:41'),
(19, 'Бухоро', NULL, 'Узбекистон', '2025-09-20 12:00:41', '2025-09-20 12:00:41'),
(20, 'Маскав', NULL, 'Руссия', '2025-09-20 12:00:41', '2025-09-20 12:00:41');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(7, '2014_10_12_000000_create_users_table', 1),
(8, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(9, '2019_08_19_000000_create_failed_jobs_table', 1),
(10, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(11, '2025_09_14_155146_create_cities_table', 1),
(12, '2025_09_17_141217_create_car_models_table', 2),
(13, '2025_09_19_074226_add_new_fields_to_users_table', 3),
(14, '2025_09_19_102239_remove_unique_from_phone_and_email_in_users_table', 3),
(15, '2025_09_19_153019_create_vehicles_table', 4),
(16, '2025_09_19_153349_create_rides_table', 5),
(17, '2025_09_19_164832_create_services_table', 6),
(18, '2025_09_19_172931_add_seats_to_car_models_table', 7),
(19, '2025_09_21_082029_change_government_id_to_json_in_users_table', 8);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rides`
--

CREATE TABLE `rides` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `vehicle_id` bigint(20) UNSIGNED NOT NULL,
  `pickup_location` varchar(255) NOT NULL,
  `destination` varchar(255) NOT NULL,
  `number_of_seats` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `ride_date` date NOT NULL,
  `ride_time` time NOT NULL,
  `accept_parcel` tinyint(1) NOT NULL DEFAULT 0,
  `services` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`services`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rides`
--

INSERT INTO `rides` (`id`, `user_id`, `vehicle_id`, `pickup_location`, `destination`, `number_of_seats`, `price`, `ride_date`, `ride_time`, `accept_parcel`, `services`, `created_at`, `updated_at`) VALUES
(1, 3, 2, 'City Center', 'Train Station', 4, 600.00, '2025-09-21', '16:00:00', 1, '[\"ac\",\"wifi\",\"music\"]', '2025-09-19 10:57:49', '2025-09-19 11:04:11'),
(2, 1, 1, 'Downtown', 'Airport', 3, 500.44, '2025-09-20', '15:30:00', 0, '[\"ac\",\"wifi\"]', '2025-09-19 10:57:49', '2025-09-19 10:57:49');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `service_name`, `created_at`, `updated_at`) VALUES
(1, '👩 Women only', '2025-09-20 12:25:07', '2025-09-20 12:36:05'),
(2, '🏠 Door-to-door (home pickup & drop-off)', '2025-09-20 12:25:22', '2025-09-20 12:25:22'),
(3, '❄️ Air conditioning', '2025-09-20 12:25:33', '2025-09-20 12:25:33'),
(4, '🔌 Phone charger available', '2025-09-20 12:25:43', '2025-09-20 12:25:43'),
(5, '📶 Wi-Fi available', '2025-09-20 12:25:55', '2025-09-21 01:31:16'),
(6, '🎵 Music of passenger’s choice', '2025-09-20 12:26:06', '2025-09-20 12:35:50');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `phone_number` varchar(255) DEFAULT NULL,
  `is_phone_verify` tinyint(1) NOT NULL DEFAULT 0,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(255) DEFAULT NULL,
  `otp` varchar(255) DEFAULT NULL,
  `otp_sent_at` timestamp NULL DEFAULT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `dob` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `government_id` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`government_id`)),
  `id_verified` tinyint(1) NOT NULL DEFAULT 0,
  `apple_token` varchar(255) DEFAULT NULL,
  `facebook_token` varchar(255) DEFAULT NULL,
  `google_token` varchar(255) DEFAULT NULL,
  `is_social` tinyint(1) NOT NULL DEFAULT 0,
  `device_type` varchar(255) DEFAULT NULL,
  `device_id` varchar(255) DEFAULT NULL,
  `device_token` varchar(255) DEFAULT NULL,
  `api_token` varchar(255) DEFAULT NULL,
  `vehicle_number` varchar(255) DEFAULT NULL,
  `vehicle_type` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `image`, `name`, `phone_number`, `is_phone_verify`, `email`, `password`, `role`, `otp`, `otp_sent_at`, `email_verified`, `dob`, `gender`, `government_id`, `id_verified`, `apple_token`, `facebook_token`, `google_token`, `is_social`, `device_type`, `device_id`, `device_token`, `api_token`, `vehicle_number`, `vehicle_type`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Admin', NULL, 0, 'admin@qadampayk.com', '$2y$10$WqZm2kdP7kCfvSzRKDlCaubEp/pNboZ44KEkg6Ofs15qYx8.5zi8m', '1', NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-17 08:44:06', '2025-09-17 08:44:06'),
(2, '6830439a32f7b.png', 'nitisha', '71212121212', 1, 'nitisha@gmail.com', NULL, '2', NULL, NULL, 0, NULL, NULL, NULL, 2, NULL, NULL, NULL, 0, 'dvfv', 'fvbfgb', '4tbvfbghnhg', 'Pe3uQvxNaX31RKYzTpL6z8KIiVqI2uPFrTJHKutxJBQQ5jnvb6IslRAEfKIZ', NULL, NULL, '2025-09-19 09:51:18', '2025-09-21 04:48:29'),
(3, NULL, 'anukool', '79759184877', 1, 'anukool@gmail.com', NULL, '2', NULL, NULL, 0, NULL, NULL, '[\"3_test_79759_EmxsK_certificate.png\",\"3_test_79759_goq0b_certificate.pdf\"]', 1, NULL, NULL, NULL, 0, 'dvfv', 'fvbfgb', '4tbvfbghnhg', 'fxD0ksv7NPl2OQYDF6pJI1XADge5nIZfWXpmt0ctrLvJLWpVleE6iQ3ALM7V', NULL, NULL, '2025-09-19 09:52:40', '2025-09-21 03:30:06'),
(11, NULL, 'abhi', '79759184874', 1, 'abhi@gmail.com', NULL, '2', NULL, NULL, 0, NULL, NULL, '[\"3_test_79759_EmxsK_certificate.png\",\"3_test_79759_goq0b_certificate.pdf\"]', 1, NULL, NULL, NULL, 0, 'dvfv', 'fvbfgb', '4tbvfbghnhg', 'fxD0ksv7NPl2OQYDF6pJI1XADge5nIZfWXpmt0ctrLvJLWpVleE6iQ3ALM7V', NULL, NULL, '2025-09-19 09:52:40', '2025-09-21 03:32:57');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `number_plate` varchar(255) DEFAULT NULL,
  `vehicle_image` varchar(255) DEFAULT NULL,
  `vehicle_type` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `user_id`, `brand`, `model`, `number_plate`, `vehicle_image`, `vehicle_type`, `created_at`, `updated_at`) VALUES
(1, 3, 'ford', 'thar', 'a1234er', NULL, NULL, '2025-09-19 10:20:51', '2025-09-19 10:20:51'),
(2, 3, 'tesla', 'swift', '3434ee', '3_1000035942.jpg', NULL, '2025-09-19 10:22:35', '2025-09-19 10:34:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `car_models`
--
ALTER TABLE `car_models`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `rides`
--
ALTER TABLE `rides`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rides_user_id_foreign` (`user_id`),
  ADD KEY `rides_vehicle_id_foreign` (`vehicle_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `services_service_name_unique` (`service_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicles_user_id_foreign` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `car_models`
--
ALTER TABLE `car_models`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rides`
--
ALTER TABLE `rides`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `rides`
--
ALTER TABLE `rides`
  ADD CONSTRAINT `rides_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rides_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `vehicles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
