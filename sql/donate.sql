-- Donate coins tábla (vagy adhatod az accounts táblához is)
-- Ha az accounts táblában akarod:
ALTER TABLE accounts ADD COLUMN donate_coins INT(11) NOT NULL DEFAULT 0 AFTER accessLevel;

-- Shop items tábla
CREATE TABLE IF NOT EXISTS `shop_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(100) NOT NULL,
  `description` text,
  `item_id` int(11) NOT NULL COMMENT 'L2 item ID',
  `enchant` int(11) DEFAULT 0,
  `quantity` int(11) DEFAULT 1,
  `price` int(11) NOT NULL COMMENT 'Ár donate coinban',
  `category` varchar(50) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Shop vásárlási előzmények
CREATE TABLE IF NOT EXISTS `shop_purchases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `item_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `price` int(11) NOT NULL,
  `status` enum('pending','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `purchased_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `delivered_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Példa shop itemek
INSERT INTO `shop_items` (`item_name`, `description`, `item_id`, `enchant`, `quantity`, `price`, `category`, `available`) VALUES
('Scroll: Enchant Weapon (S-Grade)', 'Növeli a fegyver enchant értékét +1-gyel. Safe enchant: +3, Max: +16', 959, 0, 1, 150, 'Enchant', 1),
('Scroll: Enchant Armor (S-Grade)', 'Növeli a páncél enchant értékét +1-gyel. Safe enchant: +3, Max: +16', 960, 0, 1, 100, 'Enchant', 1),
('Blessed Scroll: Enchant Weapon (S)', 'Biztos enchant scroll fegyverre. Sikertelen esetén nem csökken az enchant!', 6577, 0, 1, 300, 'Enchant', 1),
('Angel Ring', 'Egyedi gyűrű: +2 INT, +1 WIT, +50 M.Atk', 6660, 0, 1, 500, 'Jewelry', 1),
('Baium Ring', 'Epic gyűrű: +1 All Stats, Speed boost', 6658, 0, 1, 1000, 'Jewelry', 1),
('Phoenix Necklace', 'Donate nyaklánc: +3 CON, +200 HP', 8191, 0, 1, 400, 'Jewelry', 1),
('30-Day Premium Account', 'VIP státusz 30 napra: +50% XP, +30% Drop, Priority login', 0, 0, 1, 800, 'Premium', 1),
('Name Change Stone', 'Karakter név megváltoztatása (1x használatos)', 0, 0, 1, 200, 'Service', 1),
('Hair Style Change', 'Külső megváltoztatás: haj, arc', 0, 0, 1, 100, 'Service', 1),
('Giant\'s Codex (Mastery)', 'Skill könyv: +1 skill level bizonyos skillekhez', 9629, 0, 1, 600, 'Books', 1);
