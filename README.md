# Government Schemes Matcher and Eligibility Tester

SchemePath is a PHP 8.1+ / MySQL educational application for browsing government-scheme information, maintaining optional profile details, and producing explainable preliminary assessments. Install it using XAMPP; see [docs/INSTALLATION.md](docs/INSTALLATION.md).

## Current implementation

- Public responsive home, catalogue search/filter, scheme details, privacy, and about pages.
- Session login/registration/logout, password hashing, a basic per-email login rate limit, profile editing, password change, and account deletion.
- PDO-backed active catalogue, category associations, bookmarks, owned assessment history, rule version/profile snapshots, and per-rule result explanations.
- Admin-only scheme/category/rule-group/rule/document/source functions, review status, activation/deactivation, and audit-log viewing.
- A reusable `EligibilityEngine` with typed operators, AND/OR groups, applicability, optional conditions, PASS/FAIL/UNKNOWN/NOT APPLICABLE/INVALID statuses, and saved result snapshots.
- Fictional `DEMO ONLY` SQL seed examples. No real schemes are asserted as verified.

## Install

Follow [docs/INSTALLATION.md](docs/INSTALLATION.md). In short: import `database/migrations/001_initial_schema.sql`, optionally import `database/seeds/001_demo.sql`, copy the example database configuration to `config/database.local.php`, launch Apache/MySQL in XAMPP, then open `http://localhost/GovernmentSchemesMatcher/public/`. Create the initial administrator with:

```powershell
C:\xampp\php\php.exe scripts\create_admin.php admin@example.test "Project Administrator"
```

The script prompts for the password. No starter admin credentials are included.

## Tests

Unit tests use PHPUnit and are defined in `tests/Unit/EligibilityEngineTest.php`. Run the same cases without Composer using `C:\xampp\php\php.exe tests\run_unit.php`. With Composer installed, run `composer install` and `composer test`. Integration and manual checks are listed in `docs/TEST_PLAN.md`.

## Documentation

- Requirements: `docs/SRS.md`
- Architecture: `docs/ARCHITECTURE.md`
- Database and reset notes: `docs/DATABASE.md`
- User/admin instructions: `docs/USER_GUIDE.md`, `docs/ADMIN_GUIDE.md`
- Test plan: `docs/TEST_PLAN.md`
- Eight editable diagrams: `docs/UML/diagrams.md`

## Submission reports

- [Project report (public copy)](docs/submission/Government_Schemes_Matcher_Project_Report_Public.pdf)
- [Software Engineering lab record](docs/submission/Software_Engineering_Lab_Record_Final.pdf)

The local database, account credentials, and intermediate report files are not part of this public repository. The project report public copy omits the student registration number.

## Limitations

The current local check used PHP 8.2.12 and MariaDB 10.4.32. PHP syntax validation passed for 35 PHP files, and all 18 zero-dependency eligibility unit cases passed. A local browser smoke test loaded the home, catalogue, and scheme detail pages; an administrator dashboard opened; a demo profile saved; and a fictional scheme assessment returned condition-level results. Composer was unavailable, so PHPUnit was not run. No email delivery, password reset flow, CSV import, rule preview, source deletion, or automated browser/database integration suite is implemented. No production security or legal compliance audit has been performed. Do not deploy this directly to the public internet.
