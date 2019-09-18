ALTER TABLE users ADD COLUMN email varchar(255) DEFAULT '';
ALTER TABLE users ADD COLUMN is_email_verified tinyint(1) NOT NULL DEFAULT 0;
-- FK constraints require target key to be indexed
ALTER TABLE users ADD INDEX (`email`);
ALTER TABLE users ADD INDEX (`login`);
-- new table for storing email verification data
CREATE TABLE email_verifications (
    `login` varchar(20) NOT NULL DEFAULT '',
    `email` varchar(255) NOT NULL DEFAULT '',
    `hash` varchar(255) NOT NULL DEFAULT '',
    `last_sent` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `expires` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`login`, `hash`),
    INDEX(`email`),
    INDEX(`login`),
    FOREIGN KEY(`login`) REFERENCES `users`(`login`) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY(`email`) REFERENCES `users`(`email`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
-- triggers
delimiter //
CREATE TRIGGER clear_tk_on_finish BEFORE UPDATE ON users 
    FOR EACH ROW
        BEGIN 
            IF NEW.is_email_verified = 1 OR NEW.email != OLD.email THEN 
                DELETE FROM email_verifications WHERE login = NEW.login;
            END IF;
        END;//
delimiter ;