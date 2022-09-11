CREATE TABLE PATCHES (
    `id` INT PRIMARY KEY,
    `name` VARCHAR(64) UNIQUE NOT NULL,
    `release_date` DATE NOT NULL,
    `db_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE `media_files` RENAME COLUMN `bytes_special` TO `file_size`;

INSERT INTO PATCHES (`id`, `name`, `release_date`) VALUES (1, "patch-220625.sql", "2022-06-25");
