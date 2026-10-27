# Test plan

## Automated tests

`tests/Unit/EligibilityEngineTest.php` covers numeric boundaries, missing information, AND/OR behavior, inclusive ranges, unsupported operators, optional rules, applicability, malformed groups, and empty groups. Run the same cases without Composer using `C:\xampp\php\php.exe tests\run_unit.php`. With Composer installed, run `composer install` then `composer test`. Authentication, PDO, and authorization integration scenarios are listed below but require an isolated MySQL test database and are not included in the unit suite.

## Manual browser cases

The local smoke tests below were run against an isolated MariaDB demonstration database. They confirm only the listed pages and actions; complete the remaining cases in the XAMPP environment before a live demonstration.

| ID | Scenario | Expected result | Actual result |
|---|---|---|---|
| M01 | Open homepage | Public home page and disclaimer are visible. | Passed: home page loaded and displayed the preliminary-assessment disclaimer. |
| M02 | Register valid account | Account created; user can then sign in. | Pending |
| M03 | Register duplicate email | Safe error; no duplicate account. | Pending |
| M04 | Sign in with correct password | Dashboard opens and session ID rotates. | Pending |
| M05 | Sign in with incorrect password | Generic login error; attempt is rate-limited. | Pending |
| M06 | Update profile | Valid optional values save and display on reload. | Passed: demo profile saved in the isolated database. |
| M07 | Search schemes | Search filters catalogue results. | Pending |
| M08 | Filter by category | Only matching category entries appear. | Pending |
| M09 | Open scheme details | Description, benefit, documents, and review status appear. | Passed: fictional demo scheme details and review state displayed. |
| M10 | Assess with complete matching profile | Preliminary result and per-rule explanations appear. | Passed: fictional demo assessment displayed two passing rules and a preliminary-only notice. |
| M11 | Assess with failed condition | Failed condition is identified; outcome is not official. | Pending |
| M12 | Assess with missing value | UNKNOWN result explains missing profile field. | Pending |
| M13 | Save bookmark | Scheme appears in the user's saved list. | Pending |
| M14 | Remove bookmark | Scheme disappears from saved list. | Pending |
| M15 | Open assessment history | Only signed-in user's history appears with rule versions. | Pending |
| M16 | Normal user opens admin route | Server returns 403. | Pending |
| M17 | Admin creates scheme | Scheme and category associations persist. | Pending |
| M18 | Admin edits scheme | New details/status persist and audit event is recorded. | Pending |
| M19 | Admin adds a rule | Rule is saved under selected scheme/group. | Pending |
| M20 | Configure AND and OR combinations | Engine follows configured group and scheme operators. | Pending |
| M21 | Add required document | Detail and result pages show the document. | Pending |
| M22 | Change review state | Catalogue/detail pages show current state and date. | Pending |
| M23 | Deactivate scheme | Scheme no longer appears in public catalogue or assessment route. | Pending |
| M24 | Sign out | Session is cleared. | Pending |
| M25 | Revisit protected page after sign out | Redirect to sign-in. | Pending |
| M26 | Submit invalid, XSS-like, or SQL-like input | Validation rejects or treats input as text; no query bypass or script execution. | Pending |

## Isolated integration test checklist

Create a dedicated test schema and test fixtures. Verify duplicate email, ownership boundaries, non-admin authorization, active-scheme catalogue scope, and atomic assessment persistence. Do not point test runs at the demonstration database. These database tests have not yet been implemented or run.
