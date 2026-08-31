import { FaFacebookF, FaInstagram, FaLinkedinIn, FaWhatsapp } from 'react-icons/fa6';
import { HiOutlineEnvelope, HiOutlineEnvelopeOpen } from 'react-icons/hi2';

type SocialIcon = 'linkedin' | 'facebook' | 'instagram' | 'whatsapp' | 'business-email' | 'personal-email';
type SocialLink = { label: string; icon: SocialIcon; href: string; badge?: string };

function LinkIcon({ icon }: { icon: SocialIcon }) {
  if (icon === 'linkedin') return <FaLinkedinIn />;
  if (icon === 'facebook') return <FaFacebookF />;
  if (icon === 'instagram') return <FaInstagram />;
  if (icon === 'whatsapp') return <FaWhatsapp />;
  if (icon === 'business-email') return <HiOutlineEnvelope />;
  return <HiOutlineEnvelopeOpen />;
}

export function SocialLinks({ links, compact = false }: { links: readonly SocialLink[]; compact?: boolean }) {
  return (
    <div className={`social-links${compact ? ' social-links-compact' : ''}`}>
      {links.map((link) => (
        <a href={link.href} target={link.href.startsWith('http') ? '_blank' : undefined} rel={link.href.startsWith('http') ? 'noreferrer' : undefined} key={link.label} aria-label={`Open ${link.label}`} title={link.label}>
          <span aria-hidden="true"><LinkIcon icon={link.icon} /></span>{link.badge ? <small className="social-icon-badge">{link.badge}</small> : null}<strong>{link.label}</strong><i>↗</i>
        </a>
      ))}
    </div>
  );
}
