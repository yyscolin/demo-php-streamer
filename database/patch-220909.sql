ALTER TABLE `movies_stars` ADD COLUMN `status` TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER `star_id`;

INSERT INTO `patches` (`id`, `name`, `release_date`) VALUES (2, "patch-220909.sql", "2022-09-09");
