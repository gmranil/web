-- Email mező hozzáadása (ha még nincs)
ALTER TABLE accounts 
ADD COLUMN email VARCHAR(100) DEFAULT NULL 
AFTER password;

-- Coin transaction history tábla
CREATE TABLE IF NOT EXISTS `coin_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `amount` int(11) NOT NULL COMMENT 'Pozitív: hozzáadás, Negatív: levonás',
  `balance_after` int(11) NOT NULL,
  `type` enum('purchase','donation','admin_add','admin_remove','refund') NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_username` (`username`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Account activity log tábla
CREATE TABLE IF NOT EXISTS `account_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `activity_type` enum('login','logout','password_change','email_change','purchase','coin_add') NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_username` (`username`),
  KEY `idx_activity_type` (`activity_type`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
