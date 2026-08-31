import { companyContact } from '../data/company';

export function ProfileContacts({ compact = false }: { compact?: boolean }) {
  const links = [
    { label: 'US WhatsApp', value: companyContact.usPhone, href: companyContact.usWhatsAppHref, icon: 'US' },
    { label: 'Pakistan WhatsApp', value: companyContact.pakistanWhatsApp, href: companyContact.pakistanWhatsAppHref, icon: 'PK' },
    { label: 'Business email', value: companyContact.email, href: `mailto:${companyContact.email}`, icon: '@' },
    { label: 'Direct email', value: companyContact.directEmail, href: `mailto:${companyContact.directEmail}`, icon: '↗' },
  ];

  return (
    <div className={`profile-contact-links${compact ? ' profile-contact-links-compact' : ''}`}>
      {links.map((link) => (
        <a href={link.href} target={link.href.startsWith('http') ? '_blank' : undefined} rel={link.href.startsWith('http') ? 'noreferrer' : undefined} key={link.label}>
          <span>{link.icon}</span><span><small>{link.label}</small><strong>{link.value}</strong></span><i>↗</i>
        </a>
      ))}
    </div>
  );
}
