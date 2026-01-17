CREATE TABLE IF NOT EXISTS `app_contributions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT NOT NULL,
  `amount` DECIMAL(10, 2) NOT NULL,
  `currency` VARCHAR(3) DEFAULT 'XAF',
  `status` ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
  `payment_method` VARCHAR(50),
  `transaction_id` VARCHAR(100),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `app_alerts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT NOT NULL,
  `message` TEXT NOT NULL,
  `read_status` BOOLEAN DEFAULT FALSE,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);
