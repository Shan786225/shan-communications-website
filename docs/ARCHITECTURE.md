# Architecture

## Frontend

The website is a statically exported Next.js application. React components are rendered into HTML, CSS and JavaScript during the production build. cPanel serves those files directly through LiteSpeed; Node.js is not required on the production server.

```text
app/data/          Central content and reusable company facts
app/components/    Shared interface and form components
app/*/page.tsx     Public routes
public/assets/     Images, logos and downloadable public assets
deployment/        Hosting configuration committed with the source
scripts/           Repeatable build helpers
out/               Generated cPanel release; never edit directly
```

## Environments

- Local development: developer machine
- Review: GitHub Pages under the repository subpath
- Production: `shancommunication.com` at the cPanel `public_html` document root

`NEXT_PUBLIC_BASE_PATH` supports the GitHub Pages subpath. It must be empty for production.

## Forms

The current implementation submits forms to the same-domain PHP handler at `public/api/submit-form.php`:

- `ContactForm.tsx` submits business inquiries to the CEO mailbox without opening the visitor's email application.
- `JobApplicationForm.tsx` submits applications to the HR mailbox and accepts a validated CV attachment or accessible CV URL.
- The PHP handler validates required fields, origin, consent, CV size/type and repeated submissions. The fixed server-side sender is the support mailbox.

The next backend phase should store submissions in a dedicated MySQL database, mirror approved fields to Google Sheets, move notifications to authenticated SMTP, store any retained CV files outside the public document root, and expose an authenticated dashboard. Until that phase is deployed, documentation and website copy must not claim that submissions are stored in the database, Sheets or dashboard.

## Production data boundaries

- The legacy WordPress database is retained only for rollback and historical recovery.
- The future website submission database must be separate from the WordPress database.
- Secrets belong in server-side configuration outside `public_html` or in cPanel-managed environment settings.
- Uploaded CV files are validated by extension, MIME type and size, attached directly to the HR notification and not written into the public web directory.
