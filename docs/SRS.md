# Software Requirements Specification

## Purpose and scope

The application helps a user browse an administratively maintained catalogue and compare optional profile details with structured scheme rules. It is a preliminary information tool, not an eligibility authority or government service.

## Actors

- **Visitor:** views public pages and active scheme details.
- **Registered user:** maintains their profile, assesses schemes, bookmarks entries, and reviews their own history.
- **Administrator:** maintains scheme content, status, categories, rules, documents, source metadata, and reviews audit history.

## Functional requirements

- FR-01 Users can register with a unique email and a password hash.
- FR-02 Users can sign in, sign out, change passwords, and request account deletion.
- FR-03 Users can save optional profile attributes with server-side validation.
- FR-04 Visitors can search and filter active schemes and open their detail pages.
- FR-05 Each scheme may be associated with multiple categories and sources.
- FR-06 Scheme rules are stored in MySQL, using allowlisted attributes, operators, and AND/OR groups.
- FR-07 An authenticated user can create a preliminary assessment against their saved profile.
- FR-08 The assessment shows PASS, FAIL, UNKNOWN, NOT APPLICABLE, or INVALID per rule.
- FR-09 Assessments store a profile snapshot and the scheme rule version.
- FR-10 Users can bookmark schemes and access only their own bookmarks and assessment history.
- FR-11 Administrators can create/update/deactivate schemes and manage categories, groups, rules, and documents.
- FR-12 The application displays a preliminary-assessment disclaimer and distinguishes demo/unverified records.
- FR-13 Administrative actions are audit logged without secrets.

## Non-functional requirements

Use PDO prepared statements, output escaping, session authentication, CSRF tokens, foreign keys, responsive semantic HTML, and pagination for catalogue/history screens. Keep eligibility evaluation in a reusable service. XAMPP local installation is the supported deployment target.

## Assumptions and limitations

There is no real government data import or email delivery. Demonstration seed records are fictional. Official records require administrator research and review before being labelled verified. No identity documents are collected. Browser workflows and live MySQL integration remain to be validated in XAMPP when available.
