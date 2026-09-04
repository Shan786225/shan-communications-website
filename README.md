# Shan Communications Website

This repository is the source of truth for the Shan Communications corporate website.

- Production: https://shancommunication.com
- Review: https://shan786225.github.io/shan-communications-website/
- Framework: Next.js static export with React and TypeScript
- Production hosting: Namecheap cPanel / LiteSpeed
- Source branch: `main`
- GitHub Pages output branch: `gh-pages`

## Local development

Requirements: Node.js 22.13 or newer and pnpm.

```bash
pnpm install
pnpm dev
```

The local site is normally available at `http://localhost:3000`.

## Where to make changes

- Company details, contact information, social links and statistics: `app/data/company.ts`
- Services, case studies and insight content: `app/data/site.ts`
- Page content: `app/<route>/page.tsx`
- Shared header, footer and forms: `app/components/`
- Global design, responsive behavior and motion: `app/globals.css`
- Images and brand assets: `public/assets/`
- SEO metadata: `app/layout.tsx`, `app/sitemap.ts` and `app/robots.ts`
- Production redirect and security rules: `deployment/cpanel/.htaccess`

Do not edit `.next/`, `.vinext/`, `dist/` or `out/`. They are generated output.

## Form routing

- Business inquiries and **Start a conversation**: `ceo@shancommunication.com`
- Job applications: `hr@shancommunication.com`
- General public support: `support@shancommunication.com`

The public forms submit inside the website through `public/api/submit-form.php`. Business inquiries are stored and emailed to the CEO mailbox; job applications are stored and emailed to HR. Uploaded CVs are kept outside the public document root and are available only to an authenticated dashboard session. The handler validates fields and attachments, limits repeated submissions and never exposes infrastructure credentials to the browser.

The authenticated operations dashboard is served from `/dashboard/`. Production credentials, database access and Google Sheets webhook configuration live outside `public_html`. See `docs/BACKEND-OPERATIONS.md` for the data flow, security boundaries and maintenance procedure.

## Validation

```bash
pnpm build
pnpm lint
```

For the cPanel-ready static export:

```bash
./scripts/build-cpanel.sh
```

The finished deployment files will be in `out/`.

## Deployment safety

Read `docs/CPANEL-DEPLOYMENT.md` before changing production. Always create and verify a complete account backup, stage the release, preserve unrelated hosted directories, and keep a tested rollback copy.
