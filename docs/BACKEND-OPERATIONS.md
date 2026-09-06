# Backend Operations

## Production components

- Dashboard: `https://shancommunication.com/dashboard/`
- Form handler: `public/api/submit-form.php`
- Shared PHP backend: `public/api/_backend.php`
- Schema source: `public/api/_schema.php` and `deployment/database/schema.sql`
- Protected production configuration: `/home/comshan979/shan_config/backend.php`
- Protected CV storage: `/home/comshan979/shan_data/cv/`
- Dedicated MariaDB database and user: managed through cPanel and referenced only by the protected configuration file
- Google Sheets webhook source: `deployment/google-apps-script/Code.gs`

The protected configuration and CV storage must remain outside `public_html`. Never commit passwords, hashes, webhook URLs or secrets.

## Submission flow

1. The website validates the business inquiry or job application.
2. The PHP handler stores the record in MariaDB.
3. An uploaded CV is stored outside `public_html` and can only be downloaded through an authenticated dashboard session.
4. The existing email notification is sent to `ceo@shancommunication.com` for business inquiries or `hr@shancommunication.com` for job applications.
5. The record is mirrored into the matching Google Sheets tab.
6. Email and Sheets delivery states are recorded in the database and displayed in the dashboard.

If email or Sheets delivery fails after database storage, the dashboard still retains the submission. The public form must not expose infrastructure errors or any secret.

## Dashboard

The dashboard provides search, type/status filters, workflow updates, internal notes, authenticated CV downloads and CSV export. Sessions expire after 30 minutes of inactivity. The dashboard and its files are excluded from search indexing.

Choose **Review** to open a dedicated submission page. Choose a status, enter internal notes, then **Save changes**. A confirmation appears after saving; refreshing does not submit the update again. Returning to results preserves the search, filters and page. Concurrent edits show a conflict instead of silently overwriting another session's work.

Search covers name, email, phone, submission ID, job role and service. Search and CSV export share the same filters. Each SQL search field has a distinct parameter because native MySQL prepared statements cannot reuse a named parameter. Dates and the Today count use Asia/Karachi (UTC+5); database timestamps and Sheet timestamps remain UTC.

To change the administrator password, generate a new PHP-compatible bcrypt hash and replace only `dashboard.password_hash` in `/home/comshan979/shan_config/backend.php`. Do not store the plain password in the repository.

## Database changes

The PHP backend runs idempotent `CREATE TABLE IF NOT EXISTS` statements from `_schema.php`. For future changes:

1. Update `deployment/database/schema.sql` as the readable schema source.
2. Update `_schema.php` with an idempotent migration.
3. Test locally or in a staging database.
4. Commit the source before deploying the exact build.
5. Back up the active coded-site database before any destructive migration.

## Google Sheets

The spreadsheet contains `Business Inquiries` and `Job Applications` tabs. The Apps Script web application must execute as the owner and validate `SHAN_WEBHOOK_SECRET` from Script Properties. Store `SHAN_SPREADSHEET_ID` and `SHAN_WEBHOOK_SECRET` in Script Properties, deploy a web application URL, and place the same URL and secret only in the protected cPanel configuration.

Do not make the spreadsheet publicly editable. Changing spreadsheet headers or tab names requires matching changes in `Code.gs` and a new verified Apps Script deployment.

The webhook updates or inserts by Submission ID inside a script lock. Repeated delivery and status updates therefore keep one row per submission. Internal dashboard notes and CV file contents are not copied to Sheets. The response must acknowledge the same submission ID before it is marked synced.

Submission rows use plain-text cell formatting so phone-number leading zeros and identifiers are preserved. Formula-like input is escaped. A failed manual batch stops after its first service failure; remaining records stay queued for scheduled retry.

Install `deployment/cpanel/sync-sheets.php` at `/home/comshan979/shan_config/sync-sheets.php`. In cPanel Cron Jobs, run `/usr/local/bin/php /home/comshan979/shan_config/sync-sheets.php` every five minutes. Keep exactly one matching job. Pending, failed and previously disabled records are retried without re-sending notification emails. The dashboard shows outstanding deliveries and also offers **Retry pending sync**.

## Regression checks and staging

Run `php tests/dashboard-query.php`, `node --test tests/sheets-webhook.test.mjs`, and PHP syntax checks alongside the required frontend builds. Before publishing, stage the exact committed dashboard and API files together. A staging-only Apache `SetEnv SHAN_DASHBOARD_BASE /dashboard/<staging>/dashboard/` makes navigation and styles resolve within that staging copy; do not set this override in production. Protect staging with the same administrator session. Verify search, combined filters, empty results, filtered export, CV access, status save, refresh, and mobile layout against actual PHP/MySQL.
