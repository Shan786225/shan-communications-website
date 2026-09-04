# Changelog

## 2026-09-05 — Dashboard QA and Sheets delivery

- Fixed native MySQL search parameters, combined filters, filtered CSV exports and out-of-range pagination.
- Replaced clipped application popovers with dedicated responsive review pages and clear status controls, private notes and save confirmations.
- Added idempotent Sheets delivery, status updates, pending-delivery visibility and scheduled retries.
- Added dashboard regression checks and documented the staging and support workflow.

## 2026-09-05

- Added dedicated MariaDB storage, protected CV retention, Google Sheets mirroring and an authenticated operations dashboard for website submissions.
- Added dashboard search, filters, workflow notes, CSV export and authenticated CV downloads.
- Permanently removed the former WordPress files, full-account migration backup, LiteSpeed WordPress cache, WordPress metadata, database and database user from cPanel after explicit approval.
- Replaced the obsolete WordPress rollback procedure with a coded-release rollback procedure.
- Completed a full responsive QA pass across every public route at phone, tablet, laptop and desktop widths.
- Corrected narrow-phone hero clipping, strengthened mobile content gutters and removed the overlapping mobile scroll prompt.
- Corrected the collapsed desktop header track so the conversation action remains centered inside the header boundary.
- Prevented mobile content sections from remaining hidden and appearing as large blank spaces when scroll-triggered motion is delayed.
- Added a prominent Jobs hero action that takes applicants directly to the application form.
- Added a compact, polished mobile Start a conversation action and corrected anchor navigation to the contact form.
- Replaced external email-app form handling with same-domain PHP submission for business inquiries and job applications.
- Added server-side validation, spam throttling, inline status messages and validated CV file or URL submission.

## 2026-09-04

- Replaced the legacy WordPress frontend on `shancommunication.com` with the coded static website.
- Preserved the WordPress files in a dated account-level archive and retained the WordPress database during the initial cutover; both were later permanently removed on 2026-09-05 after explicit approval.
- Created and independently downloaded a complete cPanel account backup.
- Added permanent redirects from the former WordPress routes.
- Confirmed business inquiries route to the CEO mailbox and applications route to HR.
- Preserved unrelated `/shan/` and `/V1/` hosted directories.
