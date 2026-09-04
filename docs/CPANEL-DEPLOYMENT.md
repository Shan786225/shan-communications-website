# cPanel Deployment and Rollback

## Build

1. Confirm the approved source commit is checked out and the worktree is clean.
2. Run `pnpm build` and `pnpm lint`.
3. Run `./scripts/build-cpanel.sh`.
4. Package the contents of `out/`, including the generated `.htaccess` copied from `deployment/cpanel/.htaccess`.

The production account must run PHP 7.4 or newer, permit PHP `mail()`, allow at least a 12 MB POST request and provide Fileinfo or `mime_content_type()` for CV validation. The form endpoint is tracked at `public/api/submit-form.php` and must be deployed with the static export.

## Safe deployment sequence

1. Create a complete cPanel account backup including the home directory, databases and email configuration.
2. Download the backup to an independent machine and verify that the gzip/tar archive is readable.
3. Upload the new release to a non-production directory.
4. Test the staged homepage, main routes, assets, responsive layout, the PHP form endpoint, form success/error states and mailbox delivery.
5. Move the currently deployed coded release into a dated archive outside `public_html`.
6. Keep `.well-known`, `cgi-bin`, `/shan/`, `/V1/` and any other unrelated hosted applications in place.
7. Extract the validated release into `public_html`.
8. Check production headers, all main routes, the sitemap, robots file, form destinations, legacy redirects and a genuine 404.
9. Remove the temporary public staging directory after successful verification.

## Legacy redirects

The deployment configuration preserves these routes:

| Previous URL | Current URL |
| --- | --- |
| `/about-us/` | `/about/` |
| `/our-services/` | `/services/` |
| `/b2b-affiliation/` | `/partnerships/` |
| `/careers/` | `/jobs/` |
| `/announcements/` | `/insights/` |
| `/blog/` | `/insights/` |
| `/gallery/` | `/about/` |
| `/contact-us/` | `/contact/` |

## Rollback

If a production-blocking problem appears:

1. Move the failed coded release out of `public_html` into a dated failed-release directory.
2. Restore the previous validated coded release into `public_html`.
3. Confirm `.htaccess`, `index.html`, the static assets and `api/submit-form.php` are restored.
4. Purge the LiteSpeed cache and test the homepage, main routes, forms and a genuine 404.

The former WordPress files, full-account migration backup, LiteSpeed WordPress cache, WordPress metadata, database and database user were permanently removed from cPanel on 2026-09-05 after explicit approval. They are not a rollback source. Retain at least one validated coded-site release archive outside `public_html` for future rollback.
