import Link from 'next/link';
import { companyContact, companySocialLinks } from '../data/company';
import { services } from '../data/site';
import { SocialLinks } from './SocialLinks';

export function SiteFooter() {
  return (
    <footer className="site-footer">
      <div className="footer-lead shell">
        <div>
          <span className="eyebrow eyebrow-light">Start a conversation</span>
          <h2>Bring us the operational challenge. We’ll help shape the way forward.</h2>
        </div>
        <Link className="circle-link" href="/contact#contact-form" scroll={false} aria-label="Start a conversation with Shan Communications">↗</Link>
      </div>
      <div className="footer-grid shell">
        <div className="footer-brand">
          <img src="/assets/shan-logo-clean.png" alt="Shan Communications" />
          <p>Business process, customer experience, healthcare and growth operations built around accountable delivery.</p>
          <a href={`mailto:${companyContact.email}`}>{companyContact.email}</a>
          <a href={companyContact.usPhoneHref}>{companyContact.usPhone}</a>
          <a href={companyContact.pakistanOfficePhoneHref}>{companyContact.pakistanOfficePhone}</a>
          <address>{companyContact.office}</address>
        </div>
        <div>
          <h3>Company</h3>
          <Link href="/about">About</Link><Link href="/about#leadership">Leadership</Link><Link href="/experience">Experience</Link><Link href="/jobs">Jobs</Link>
        </div>
        <div>
          <h3>Solutions</h3>
          {services.slice(0, 4).map((service) => <Link href={`/services/${service.slug}`} key={service.slug}>{service.shortTitle}</Link>)}
        </div>
        <div>
          <h3>Resources</h3>
          <Link href="/insights">Insights</Link><Link href="/security">Security & Compliance</Link><Link href="/faq">FAQ</Link><Link href="/contact">Contact</Link><Link href="/partnerships">Partnerships</Link>
        </div>
      </div>
      <div className="footer-social shell">
        <span>Official company channels</span>
        <SocialLinks links={companySocialLinks} compact />
      </div>
      <div className="footer-bottom shell">
        <span>© 2026 Shan Communications. All rights reserved.</span>
        <div><Link href="/privacy">Privacy</Link><Link href="/terms">Terms</Link><Link href="/security">Security</Link></div>
      </div>
    </footer>
  );
}
