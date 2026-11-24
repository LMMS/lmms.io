## README

These SQL schema files are for bootstrapping LSP database. Needs newer versions of MySQL or compatibles whose Foreign Key Constraints are properly supported.

For local dev

> [!NOTE]
> The below is not for production use

1. Create LSP temp directory
    ```bash
    mkdir -p tmp
    ```
1. Start mysql service if not running
2. Create database for testing:
    ```sql
    -- Create the database
    CREATE DATABASE IF NOT EXISTS somedatabase CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    
    -- Create the user and set the password
    CREATE USER IF NOT EXISTS 'someuser'@'localhost' IDENTIFIED BY 'P@SSW0RD';
    
    -- Grant privileges on the database to the user
    GRANT ALL PRIVILEGES ON somedatabase.* TO 'someuser'@'localhost';
    
    -- Apply changes
    FLUSH PRIVILEGES;
    ```

3. Configure base table layout:
    ```bash
    mysql -u someuser -p'P@SSW0RD' somedatabase < dev/sql/00-base-schema.sql
    mysql -u someuser -p'P@SSW0RD' somedatabase < dev/sql/01-default-data.sql
    mysql -u someuser -p'P@SSW0RD' somedatabase < dev/sql/02-upgrade-to-innodb.sql
    mysql -u someuser -p'P@SSW0RD' somedatabase < dev/sql/03-create-indexes.sql
    mysql -u someuser -p'P@SSW0RD' somedatabase < dev/sql/04-convert-encoding.sql
    ```
4. The LSP should now load at http://localhost:8000/lsp
Install classic hashing for SHA1 support:
    ```sql
    INSTALL COMPONENT 'file://component_classic_hashing';
    ```
5. Add the first admin user
    ```sql
    INSERT INTO users (login, password, pw, realname, is_admin, loginFailureCount) VALUES ('admin',LOWER(SHA1('admin')),'','Testing Admin',1,0);
    ```

