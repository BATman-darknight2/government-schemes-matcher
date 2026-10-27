# Architecture

SchemePath is a small PHP 8.1+ application for Apache/XAMPP. `public/index.php` routes requests and coordinates form actions; shared helpers and the PDO connection live in `app/bootstrap.php` and `app/Database.php`. `EligibilityEngine` contains the reusable rule evaluation logic. PHP templates render pages under `app/Views`; the browser uses static CSS and a small JavaScript file for the mobile menu and confirmation dialogs.

Every modifying form posts to the front controller with a session CSRF token. Authentication and role checks run again in the controller for each protected operation. User-specific queries are scoped to the signed-in user ID. Scheme matching reads active scheme rules from MySQL and writes a versioned assessment, a profile snapshot, and one result row per condition inside a transaction.

The database is the source of truth for schemes, categories, groups, rules, documents, sources, bookmarks, assessments, and audit events. Rule values use allowlisted attributes and operators; no SQL or PHP expression is evaluated from administrator input. This is an educational local application. It has not received a security audit and is not production-ready.
