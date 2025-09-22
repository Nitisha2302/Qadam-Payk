INSERT INTO `users` (`name`, `email`, `password`, `role`, `created_at`, `updated_at`)
VALUES ('New User', 'admin@admin.com', MD5('password123'), '2', NOW(), NOW());
