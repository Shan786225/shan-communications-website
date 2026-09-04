# cPanel Deployment and Rollback

## Build

1. Confirm the approved source commit is checked out and the worktree is clean.
2. Run `pnpm build` and `pnpm lint`.
3. Run `./scripts/build-cpanel.sh`.
4. Package the contents of `out/`, including the generated `.htaccess` copied from `deployment/cpanel/.htaccess`.

## Safe deployment sequence

1. Create a complete cPanel account backup including the home directory, databases and email configuration.
2. Download the backup to an independent machine and verify that the gzip/tar archive is readable.
3. Upload the new release to a non-production directory.
4. Test the staged homepage, main routes, assets, responsive layout, forms and error handling.
5. Move the existing WordPress files into a dated archive outside `public_html`. Do not delete them during cutover.
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

1. Move the coded-site files out of `public_html` into a dated failed-release directory.
2. Move the archived WordPress files back into `public_html`.
3. Confirm `wp-config.php`, `.htaccess`, `index.php`, `wp-admin`, `wp-content` and `wp-includes` are restored.
4. Confirm the retained WordPress database is reachable.
5. Purge the LiteSpeed cache and test the former homepage and administrative login.

Do not delete the rollback archive or WordPress database until the coded site and its future backend have remained stable and a separate retention decision is approved.

