# Instructions for Humans and AI Agents

## Source of truth

Work only from this repository. Never make an undocumented production-only edit in cPanel. Commit approved source changes to `main`, then build and deploy that exact commit.

## Non-negotiable facts

- Company name: Shan Communications
- Founder and CEO: Shan Khan (Ashan Ali)
- Business inquiries go to `ceo@shancommunication.com`.
- Job applications go to `hr@shancommunication.com`.
- General support remains `support@shancommunication.com`.
- Do not invent clients, certifications, compliance status, results or statistics.
- HIPAA and PCI DSS content must describe the operating approach; it must not claim certification unless documentary evidence is added.

## Editing rules

1. Check `git status` and preserve unrelated work.
2. Update reusable facts in `app/data/` rather than duplicating them across pages.
3. Keep each service image unique, relevant to its card and correctly cropped on desktop and mobile.
4. Preserve the existing visual language, motion system, spacing and responsive behavior unless a redesign is explicitly requested.
5. Keep routes stable. If a public URL must change, add a permanent redirect to `deployment/cpanel/.htaccess` and update the sitemap.
6. Never edit generated folders: `.next/`, `.vinext/`, `dist/` or `out/`.
7. Never commit passwords, cPanel sessions, API keys, database credentials, email passwords or Google service credentials.
8. Do not delete the production backup archives or the preserved WordPress database without explicit approval.

## Required checks

- Run `pnpm build`.
- Run `pnpm lint` when source changes are made.
- Build the cPanel export with `./scripts/build-cpanel.sh` before production deployment.
- Check the homepage, every main navigation route, legacy redirects, mobile overflow, image loading and browser console errors.
- Verify business and job forms use their correct destinations.

## Production workflow

Follow `docs/CPANEL-DEPLOYMENT.md`. Production changes must be staged and reversible. GitHub Pages is the review environment; `shancommunication.com` is production.

