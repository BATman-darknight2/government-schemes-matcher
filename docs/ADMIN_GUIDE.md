# Administrator guide

Create the first administrator from the command line using `scripts/create_admin.php`; never assign an admin role through a browser form. Sign in, open **Admin**, and create a scheme with accurate content, a source URL, review status/date, categories, and application details. Use **DEMO ONLY** for fictional records. Do not mark information verified until an administrator has checked the official source.

Add one or more rule groups. Conditions within a group use that group's AND or OR operator; groups combine using the scheme-level AND or OR selector. Add rules using the allowlisted profile attribute/operator forms. Use `min|max` for inclusive numeric ranges and `item1|item2` for membership lists. Add a human-readable explanation. Existing rules and documents can be edited from their edit links; sources can be updated inline. Add required/optional document descriptions. Deactivate or archive outdated schemes rather than deleting them.

The application does not provide rule preview against test profiles or source deletion. Audit history is available under **Audit log**.
