-- Insert a test user
-- Username: testuser
-- Password: password (using MD5, which WP supports as legacy)
INSERT INTO `wp_users` (`user_login`, `user_pass`, `user_nicename`, `user_email`, `user_url`, `user_registered`, `user_activation_key`, `user_status`, `display_name`) 
VALUES ('testuser', MD5('password'), 'testuser', 'test@example.com', '', NOW(), '', 0, 'Test User');

SET @last_id = LAST_INSERT_ID();

-- Insert User Meta
INSERT INTO `wp_usermeta` (`user_id`, `meta_key`, `meta_value`) VALUES 
(@last_id, 'first_name', 'Test'),
(@last_id, 'last_name', 'User'),
(@last_id, 'wp_capabilities', 'a:1:{s:10:"subscriber";b:1;}'),
(@last_id, 'telephone', '699999999'),
(@last_id, 'ville', 'Douala'),
(@last_id, 'activite', 'Developpeur'),
(@last_id, 'categorie', 'Member-ships');
