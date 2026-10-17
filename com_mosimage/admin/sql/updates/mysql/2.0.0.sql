-- Umstellung auf utf8mb4 (Joomla 4+). Bestehende Tabellen aus Joomla 3 werden konvertiert.
ALTER TABLE `#__mosimage` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__mosimage` MODIFY `images` mediumtext NOT NULL;
