'use client';

import Link from 'next/link';
import { useCallback, useEffect, useState } from 'react';
import { usePathname } from 'next/navigation';
import { companyContact } from '../data/company';
import { services } from '../data/site';

const companyLinks = [
  { label: 'About Shan', href: '/about' },
  { label: 'Leadership', href: '/about#leadership' },
  { label: 'Experience', href: '/experience' },
  { label: 'Jobs', href: '/jobs' },
];

export function SiteHeader() {
  const pathname = usePathname();
  const [open, setOpen] = useState(false);
  const [scrolled, setScrolled] = useState(false);
  const [desktopMenu, setDesktopMenu] = useState<'company' | 'solutions' | null>(null);
  const [menuPath, setMenuPath] = useState(pathname);
  const menuIsCurrent = menuPath === pathname;
  const activeDesktopMenu = menuIsCurrent ? desktopMenu : null;
  const mobileOpen = menuIsCurrent && open;

  const closeNavigation = useCallback(() => {
    setOpen(false);
    setDesktopMenu(null);
  }, []);

  const showDesktopMenu = (menu: 'company' | 'solutions') => {
    setMenuPath(pathname);
    setDesktopMenu(menu);
  };

  useEffect(() => {
    const update = () => setScrolled(window.scrollY > 24);
    update();
    window.addEventListener('scroll', update, { passive: true });
    return () => window.removeEventListener('scroll', update);
  }, []);

  useEffect(() => {
    document.body.style.overflow = mobileOpen ? 'hidden' : '';
    return () => { document.body.style.overflow = ''; };
  }, [mobileOpen]);

  useEffect(() => {
    const dismiss = (event: PointerEvent) => {
      if (!(event.target as Element | null)?.closest('.nav-group')) setDesktopMenu(null);
    };
    const escape = (event: KeyboardEvent) => {
      if (event.key === 'Escape') closeNavigation();
    };
    document.addEventListener('pointerdown', dismiss);
    document.addEventListener('keydown', escape);
    return () => {
      document.removeEventListener('pointerdown', dismiss);
      document.removeEventListener('keydown', escape);
    };
  }, [closeNavigation]);

  return (
    <header className={`site-header${scrolled ? ' is-scrolled' : ''}`}>
      <div className="utility-bar">
        <div className="shell utility-inner">
          <div className="utility-phones">
            <a href={companyContact.usPhoneHref}><span>US</span>{companyContact.usPhone}</a>
            <a href={companyContact.pakistanOfficePhoneHref}><span>Pakistan office</span>{companyContact.pakistanOfficePhone}</a>
          </div>
          <a className="utility-email" href={`mailto:${companyContact.email}`}>{companyContact.email}</a>
        </div>
      </div>

      <div className="shell main-nav">
        <Link className="brand" href="/" aria-label="Shan Communications home" onClick={closeNavigation}>
          <img src="/assets/shan-logo-clean.png" alt="Shan Communications" />
        </Link>

        <nav className="desktop-nav" aria-label="Primary navigation">
          <Link href="/" onClick={closeNavigation}>Home</Link>
          <div
            className={`nav-group${activeDesktopMenu === 'company' ? ' is-open' : ''}`}
            onMouseEnter={() => showDesktopMenu('company')}
            onMouseLeave={() => setDesktopMenu(null)}
            onFocusCapture={() => showDesktopMenu('company')}
            onBlurCapture={(event) => {
              if (!event.currentTarget.contains(event.relatedTarget as Node | null)) setDesktopMenu(null);
            }}
          >
            <Link href="/about" aria-expanded={activeDesktopMenu === 'company'} onClick={closeNavigation}>Company <span>⌄</span></Link>
            <div className="nav-dropdown compact-dropdown">
              <p>Company</p>
              {companyLinks.map((item) => <Link href={item.href} key={item.href} onClick={closeNavigation}>{item.label}<span>↗</span></Link>)}
            </div>
          </div>
          <div
            className={`nav-group${activeDesktopMenu === 'solutions' ? ' is-open' : ''}`}
            onMouseEnter={() => showDesktopMenu('solutions')}
            onMouseLeave={() => setDesktopMenu(null)}
            onFocusCapture={() => showDesktopMenu('solutions')}
            onBlurCapture={(event) => {
              if (!event.currentTarget.contains(event.relatedTarget as Node | null)) setDesktopMenu(null);
            }}
          >
            <Link href="/services" aria-expanded={activeDesktopMenu === 'solutions'} onClick={closeNavigation}>Solutions <span>⌄</span></Link>
            <div className="nav-dropdown mega-dropdown">
              <div className="mega-intro">
                <span>Solutions</span>
                <h3>Built around the work your organization needs to operate.</h3>
                <Link href="/services" onClick={closeNavigation}>View every solution <b>↗</b></Link>
              </div>
              <div className="mega-links">
                {services.map((service) => (
                  <Link href={`/services/${service.slug}`} key={service.slug} onClick={closeNavigation}>
                    <small>{service.number}</small><strong>{service.shortTitle}</strong><span>↗</span>
                  </Link>
                ))}
              </div>
            </div>
          </div>
          <Link href="/experience" onClick={closeNavigation}>Experience</Link>
          <Link href="/insights" onClick={closeNavigation}>Insights</Link>
          <Link href="/jobs" onClick={closeNavigation}>Jobs</Link>
          <Link href="/security" onClick={closeNavigation}>Security</Link>
        </nav>

        <div className="nav-actions">
          <Link className="nav-cta" href="/contact#contact-form" scroll={false} onClick={closeNavigation}>Start a conversation <span>↗</span></Link>
          <button className={`menu-toggle${mobileOpen ? ' is-open' : ''}`} type="button" onClick={() => { setMenuPath(pathname); setOpen(!mobileOpen); }} aria-expanded={mobileOpen} aria-label={mobileOpen ? 'Close menu' : 'Open menu'}>
            <i /><i /><i />
          </button>
        </div>
      </div>

      <div className={`mobile-navigation${mobileOpen ? ' is-open' : ''}`}>
        <div className="mobile-nav-scroll">
          <span className="mobile-nav-label">Explore Shan Communications</span>
          <nav>
            <Link href="/" onClick={() => setOpen(false)}>Home <span>↗</span></Link>
            <Link href="/about" onClick={() => setOpen(false)}>Company <span>↗</span></Link>
            <Link href="/services" onClick={() => setOpen(false)}>Solutions <span>↗</span></Link>
            <Link href="/experience" onClick={() => setOpen(false)}>Experience <span>↗</span></Link>
            <Link href="/insights" onClick={() => setOpen(false)}>Insights <span>↗</span></Link>
            <Link href="/security" onClick={() => setOpen(false)}>Security & Compliance <span>↗</span></Link>
            <Link href="/jobs" onClick={() => setOpen(false)}>Jobs <span>↗</span></Link>
            <Link href="/contact" onClick={() => setOpen(false)}>Contact <span>↗</span></Link>
          </nav>
          <div className="mobile-service-links">
            <p>Service lines</p>
            {services.map((service) => <Link href={`/services/${service.slug}`} key={service.slug} onClick={() => setOpen(false)}>{service.shortTitle}</Link>)}
          </div>
        </div>
      </div>
    </header>
  );
}
