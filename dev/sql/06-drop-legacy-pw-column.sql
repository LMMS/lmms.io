-- Drop the legacy `pw` column. It was never used by the application code and
-- has been superseded by the widened `password` column (see 05).
ALTER TABLE users DROP COLUMN `pw`;
