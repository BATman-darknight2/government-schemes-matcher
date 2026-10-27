# XAMPP installation

## Requirements

- Windows 10/11, XAMPP with Apache and MySQL/MariaDB, PHP 8.1+ with `pdo_mysql` and `mbstring` enabled, and Composer for unit tests.
- No production credentials are included. This application has not had a security audit.

## Install

1. Copy `GovernmentSchemesMatcher` into `C:\xampp\htdocs\GovernmentSchemesMatcher`.
2. Start Apache and MySQL in the XAMPP Control Panel.
3. In phpMyAdmin, import `database/migrations/001_initial_schema.sql`, then import `database/seeds/001_demo.sql` for fictional demo records.
4. Copy `config/database.example.php` to `config/database.local.php`. Set the local host, database, username, and password. The migration creates the database `government_schemes`.
5. Browse to `http://localhost/GovernmentSchemesMatcher/public/`.
6. If the base URL differs, set `base_path` in `config/app.php` to the URL path prefix, such as `/GovernmentSchemesMatcher/public`.
7. Create an administrator from a terminal:

   ```powershell
   cd C:\xampp\htdocs\GovernmentSchemesMatcher
   C:\xampp\php\php.exe scripts\create_admin.php admin@example.test "Project Administrator"
   ```

   The script prompts for a password. Do not publish credentials or expose the database to the internet.

8. To run unit tests, install dev dependencies and run PHPUnit:

   ```powershell
   cd C:\xampp\htdocs\GovernmentSchemesMatcher
   composer install
   composer test
   ```

## CLI database import option

If MySQL client tools are installed, import the schema and seed SQL with:

```powershell
mysql -u root -p < database\migrations\001_initial_schema.sql
mysql -u root -p government_schemes < database\seeds\001_demo.sql
```

For development reset only, drop the local `government_schemes` database in phpMyAdmin, then repeat steps 3 and 4. This destroys all application data. Set `https_only` to true only after configuring HTTPS and configure a deployment-grade secret/password before any public deployment. Keep `config/database.local.php` private and outside version control.

## Troubleshooting

- **Could not connect:** check that MySQL is running and local config credentials match phpMyAdmin.
- **PDO driver missing:** enable `pdo_mysql` in the PHP configuration used by Apache and restart Apache.
- **Blank/500 page:** review the Apache/PHP error log; browser errors intentionally omit SQL details.
- **Access denied:** recreate the admin with the CLI script, or verify the signed-in account role directly in the local database.
- **Asset 404:** serve from the documented `public/` directory and confirm Apache URL/path configuration.
