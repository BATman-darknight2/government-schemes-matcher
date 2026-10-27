# Database design

Import `database/migrations/001_initial_schema.sql` into MySQL or MariaDB. It creates the `government_schemes` database and its tables.

| Table | Purpose and relationships |
|---|---|
| `users` | Login identity, role, terms acceptance; one-to-one `user_profiles`. |
| `user_profiles` | Optional eligibility attributes; deleted with the account. |
| `categories` | Unique catalogue categories. |
| `schemes` | Scheme content, publication/review state, rule version. |
| `scheme_categories` | Many-to-many scheme/category association. |
| `eligibility_rule_groups` | Group-level AND/OR and ordering. |
| `scheme_eligibility_rules` | Attribute/operator/value conditions and optional applicability. |
| `scheme_documents` | Required or optional checklist items. |
| `scheme_sources` | Source URL, review state, reviewer and review date. |
| `bookmarks` | Unique user/scheme pairs. |
| `eligibility_assessments` | Outcome, time, scheme rule version, profile JSON snapshot. |
| `eligibility_assessment_results` | Explainable condition-level outcome snapshot. |
| `audit_logs` | Administrative actions, without profile values or secrets. |
| `login_attempts` | Email-hash timestamps for a simple login rate limit. |

Foreign keys protect relationships. User deletion cascades to profiles, bookmarks and history; schemes referenced by history are restricted from physical deletion. Administrators should deactivate or archive schemes. Update `rule_version` whenever rule semantics change so old assessments remain attributable to their former version.

## Development reset

For local development only, delete the `government_schemes` database in phpMyAdmin, re-import the migration, then import `database/seeds/001_demo.sql`. This permanently removes local data. Never use this reset against a production database.
