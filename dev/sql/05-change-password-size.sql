-- Increase `password` size so it can hold a bcrypt hash (60 chars).
ALTER TABLE users MODIFY `password` varchar(255) NOT NULL DEFAULT '';
