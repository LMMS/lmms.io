BEGIN;
SET FOREIGN_KEY_CHECKS = 0;
-- convert all the UGC values
-- first convert to binary format (PHP actually stored UTF-8 data encoded in latin-1)
-- so to avoid conversion problems, we convert to binary format first
ALTER TABLE files CONVERT TO CHARACTER SET binary;
ALTER TABLE comments CONVERT TO CHARACTER SET binary;
-- convert to utf8
ALTER TABLE users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE files CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE comments CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE filetypes CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE ratings CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE categories CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE subcategories CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE licenses CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- special case for users table
UPDATE users SET `login` = CONVERT(cast(CONVERT(`login` USING latin1) AS BINARY) USING utf8mb4);
UPDATE users SET `realname` = CONVERT(cast(CONVERT(`realname` USING latin1) AS BINARY) USING utf8mb4);
UPDATE files SET `description` = CONVERT(cast(CONVERT(`description` USING latin1) AS BINARY) USING utf8mb4);
UPDATE comments SET `text` = COALESCE(CONVERT(cast(CONVERT(`text` USING latin1) AS BINARY) USING utf8mb4), '');
-- restore the text fields that were converted to binary to the original type
ALTER TABLE users MODIFY `login` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE users MODIFY `password` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE users MODIFY `pw` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE users MODIFY `realname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE files MODIFY `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE files MODIFY `hash` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE files MODIFY `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE comments MODIFY `text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
