USE l2jmobiusclassic;

-- Új oszlop hozzáadása
ALTER TABLE accounts ADD COLUMN access_level INT DEFAULT 0 AFTER password;