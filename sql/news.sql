CREATE TABLE IF NOT EXISTS `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `author` varchar(50) NOT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Példa adatok (opcionális)
INSERT INTO `news` (`title`, `content`, `author`, `tags`, `published`) VALUES
('🎉 Szerver Megnyitás - Készen állunk!', 'Nagy örömmel jelentjük be, hogy az L2 Savior szerver végre megnyitotta kapuit! Több hónapos fejlesztés után készen állunk, hogy a legjobb Lineage 2 élményt nyújtsuk számotokra. Kiegyensúlyozott rates, stabil szerver, és egy fantasztikus közösség vár rátok!\n\nTöltsétek le a klienst, regisztráljatok és lépjetek be még ma! Köszönjük a türelmeteket és a bizalmatokat. Találkozzunk a játékban! 🎮', 'Admin', 'Esemény,Fontos', 1),
('⚔️ PvP Tournament - Hétvégi Esemény', 'Ezen a hétvégén hatalmas PvP Tournament-et szervezünk! A győztesek értékes jutalmakat nyerhetnek, beleértve exkluzív itemeket, címeket és donate coinokat is.\n\nIdőpont: December 7, Szombat 18:00 CET\nMinimum level: 76\nDíjak: 1. hely - 5000 Donate Coin + Epic Item, 2. hely - 3000 DC, 3. hely - 1500 DC', 'Admin', 'PvP,Esemény', 1);
