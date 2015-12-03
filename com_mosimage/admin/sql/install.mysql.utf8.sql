CREATE TABLE IF NOT EXISTS `#__mosimage` (
  `content_id` int(10) unsigned  NOT NULL,
  `images` text NOT NULL,
  PRIMARY KEY (`content_id`),
  CONSTRAINT fk_mosimage_content
  FOREIGN KEY (`content_id`) 
  REFERENCES `#__content`(id)
  ON DELETE CASCADE
) DEFAULT CHARSET=utf8 ;

