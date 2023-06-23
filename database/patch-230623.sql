DROP TABLE `patches`;

-- Add insert_timestamp to tables
ALTER TABLE `attributes` ADD COLUMN `insert_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `directors` RENAME COLUMN `db_timestamp` TO `insert_timestamp`;
ALTER TABLE `genres` RENAME COLUMN `db_timestamp` TO `insert_timestamp`;
ALTER TABLE `labels` RENAME COLUMN `db_timestamp` TO `insert_timestamp`;
ALTER TABLE `media_files` ADD COLUMN `insert_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `movies` RENAME COLUMN `db_timestamp` TO `insert_timestamp`;
ALTER TABLE `movies_directors` RENAME COLUMN `db_timestamp` TO `insert_timestamp`;
ALTER TABLE `movies_genres` RENAME COLUMN `db_timestamp` TO `insert_timestamp`;
ALTER TABLE `movies_labels` RENAME COLUMN `db_timestamp` TO `insert_timestamp`;
ALTER TABLE `movies_media` ADD COLUMN `insert_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `movies_stars` ADD COLUMN `insert_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `movies_studios` RENAME COLUMN `db_timestamp` TO `insert_timestamp`;
ALTER TABLE `stars` RENAME COLUMN `db_timestamp` TO `insert_timestamp`;
ALTER TABLE `stars_attributes` RENAME COLUMN `db_timestamp` TO `insert_timestamp`;
ALTER TABLE `studios` RENAME COLUMN `db_timestamp` TO `insert_timestamp`;

-- Add update_timestamp to tables
ALTER TABLE `attributes` ADD COLUMN `update_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE `directors` ADD COLUMN `update_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE `genres` ADD COLUMN `update_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE `labels` ADD COLUMN `update_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE `media_files` ADD COLUMN `update_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE `movies` ADD COLUMN `update_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE `movies_directors` ADD COLUMN `update_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE `movies_genres` ADD COLUMN `update_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE `movies_labels` ADD COLUMN `update_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE `movies_media` ADD COLUMN `update_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE `movies_stars` ADD COLUMN `update_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE `movies_studios` ADD COLUMN `update_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE `stars` ADD COLUMN `update_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE `stars_attributes` ADD COLUMN `update_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE `studios` ADD COLUMN `update_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
