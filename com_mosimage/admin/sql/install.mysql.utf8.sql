CREATE TABLE IF NOT EXISTS `#__mosimage` (
  `content_id` int unsigned NOT NULL,
  `images` mediumtext NOT NULL,
  PRIMARY KEY (`content_id`),
  CONSTRAINT `fk_mosimage_content`
    FOREIGN KEY (`content_id`)
    REFERENCES `#__content` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
