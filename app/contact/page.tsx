import type { Metadata } from 'next';
import { ContactForm } from '../components/ContactForm';
import { PageHero } from '../components/PageHero';
import { Reveal } from '../components/Reveal';
import { SocialLinks } from '../components/SocialLinks';
import { companyContact, companySocialLinks } from '../data/company';

export const metadata: Metadata = { title: 'Contact Us', description: 'Contact Shan Communications to discuss customer experience, BPO, healthcare operations, RPM, CCM or digital growth.' };

export default function ContactPage() {
  return (
    <main>
      <PageHero eyebrow="Contact" title="Let’s start a useful conversation." description="Tell us what you are building, where you need capacity and what a successful outcome should look like." image="/assets/shan-call-center-5.jpg" />
      <section className="contact-page section-space">
        <div className="shell contact-page-grid">
          <Reveal className="contact-information">
            <span className="eyebrow">Direct contact</span>
            <h2>Choose the channel that works for you.</h2>
            <p>Business inquiries, partnerships, campaign operations and healthcare programs are routed directly to the Shan Communications team. Job applications have their own dedicated application page.</p>
            <div className="contact-method"><span>United States</span><a href={companyContact.usPhoneHref}>{companyContact.usPhone}</a></div>
            <div className="contact-method"><span>Pakistan office</span><a href={companyContact.pakistanOfficePhoneHref}>{companyContact.pakistanOfficePhone}</a></div>
            <div className="contact-method"><span>Email</span><a href={`mailto:${companyContact.email}`}>{companyContact.email}</a><a href={`mailto:${companyContact.directEmail}`}>{companyContact.directEmail}</a></div>
            <div className="contact-method"><span>Office location</span><a href={companyContact.mapHref} target="_blank" rel="noreferrer"><address>{companyContact.office}</address></a></div>
            <div className="contact-whatsapp-row"><a href={companyContact.usWhatsAppHref} target="_blank" rel="noreferrer">US WhatsApp <strong>{companyContact.usPhone}</strong><i>↗</i></a><a href={companyContact.pakistanWhatsAppHref} target="_blank" rel="noreferrer">Pakistan WhatsApp <strong>{companyContact.pakistanWhatsApp}</strong><i>↗</i></a></div>
          </Reveal>
          <Reveal delay={90}><ContactForm /></Reveal>
        </div>
        <Reveal className="contact-social-row shell">
          <div><span className="eyebrow">Official channels</span><h3>Connect with Shan Communications.</h3></div>
          <SocialLinks links={companySocialLinks} />
        </Reveal>
      </section>
    </main>
  );
}
