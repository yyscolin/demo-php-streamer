ALTER TABLE `media_files` CHANGE COLUMN `file_size` `file_size` BIGINT UNSIGNED;
ALTER TABLE `media_files` CHANGE COLUMN `iv_key` `iv_key` BINARY(16);
ALTER TABLE `media_files` CHANGE COLUMN `ver_id` `ver_id` TINYINT UNSIGNED NOT NULL DEFAULT 4;
