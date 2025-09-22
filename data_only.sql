INSERT INTO `car_models` (`id`, `model_name`, `brand`, `color`, `seats`, `features`, `created_at`, `updated_at`) VALUES
(1, 'beetle', 'volkswagen beetle', 'yellow', NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:51:14', '2025-09-20 11:51:14'),
(2, 'N', 'scorpio', 'black', NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:51:14', '2025-09-20 11:51:14'),
(3, '124 Spider', 'Abarth', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:51:14', '2025-09-20 11:51:14'),
...
(50, 'A4 allroad', 'Audi', NULL, NULL, '{\"feature\": \"N/A\"}', '2025-09-20 11:57:32', '2025-09-20 11:57:32');

INSERT INTO `cities` (`id`, `city_name`, `state`, `country`, `created_at`, `updated_at`) VALUES
(1, 'Chandigarh', 'Chandigarh', 'India', '2025-09-20 11:59:48', '2025-09-20 11:59:48'),
(2, 'Bathinda', 'Punjab', 'India', '2025-09-20 11:59:48', '2025-09-20 11:59:48'),
...
(20, 'Маскав', NULL, 'Руссия', '2025-09-20 12:00:41', '2025-09-20 12:00:41');

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(7, '2014_10_12_000000_create_users_table', 1),
(8, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(9, '2019_08_19_000000_create_failed_jobs_table', 1),
...
(19, '2025_09_21_082029_change_government_id_to_json_in_users_table', 8);

INSERT INTO `rides` (`id`, `user_id`, `vehicle_id`, `pickup_location`, `destination`, `number_of_seats`, `price`, `ride_date`, `ride_time`, `accept_parcel`, `services`, `created_at`, `updated_at`) VALUES
(1, 3, 2, 'City Center', 'Train Station', 4, 600.00, '2025-09-21', '16:00:00', 1, '[\"ac\",\"wifi\",\"music\"]', '2025-09-19 10:57:49', '2025-09-19 11:04:11'),
(2, 1, 1, 'Downtown', 'Airport', 3, 500.44, '2025-09-20', '15:30:00', 0, '[\"ac\",\"wifi\"]', '2025-09-19 10:57:49', '2025-09-19 10:57:49');

INSERT INTO `services` (`id`, `service_name`, `created_at`, `updated_at`) VALUES
(1, '👩 Women only', '2025-09-20 12:25:07', '2025-09-20 12:36:05'),
(2, '🏠 Door-to-door (home pickup & drop-off)', '2025-09-20 12:25:22', '2025-09-20 12:25:22'),
...
(6, '🎵 Music of passenger’s choice', '2025-09-20 12:26:06', '2025-09-20 12:35:50');

INSERT INTO `users` (`id`, `image`, `name`, `phone_number`, `is_phone_verify`, `email`, `password`, `role`, `otp`, `otp_sent_at`, `email_verified`, `dob`, `gender`, `government_id`, `id_verified`, `apple_token`, `facebook_token`, `google_token`, `is_social`, `device_type`, `device_id`, `device_token`, `api_token`, `vehicle_number`, `vehicle_type`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Admin', NULL, 0, 'admin@qadampayk.com', '$2y$10$WqZm2kdP7kCfvSzRKDlCaubEp/pNboZ44KEkg6Ofs15qYx8.5zi8m', '1', NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-17 08:44:06', '2025-09-17 08:44:06'),
(2, '6830439a32f7b.png', 'nitisha', '71212121212', 1, 'nitisha@gmail.com', NULL, '2', NULL, NULL, 0, NULL, NULL, NULL, 2, NULL, NULL, NULL, 0, 'dvfv', 'fvbfgb', '4tbvfbghnhg', 'Pe3uQvxNaX31RKYzTpL6z8KIiVqI2uPFrTJHKutxJBQQ5jnvb6IslRAEfKIZ', NULL, NULL, '2025-09-19 09:51:18', '2025-09-21 04:48:29'),
(3, NULL, 'anukool', '79759184877', 1, 'anukool@gmail.com', NULL, '2', NULL, NULL, 0, NULL, NULL, '[\"3_test_79759_EmxsK_certificate.png\",\"3_test_79759_goq0b_certificate.pdf\"]', 1, NULL, NULL, NULL, 0, 'dvfv', 'fvbfgb', '4tbvfbghnhg', 'fxD0ksv7NPl2OQYDF6pJI1XADge5nIZfWXpmt0ctrLvJLWpVleE6iQ3ALM7V', NULL, NULL, '2025-09-19 09:52:40', '2025-09-21 03:30:06'),
(11, NULL, 'abhi', '79759184874', 1, 'abhi@gmail.com', NULL, '2', NULL, NULL, 0, NULL, NULL, '[\"3_test_79759_EmxsK_certificate.png\",\"3_test_7]()
