'use client';

import { FormEvent } from 'react';
import { companyContact } from '../data/company';

export function ContactForm({ career = false }: { career?: boolean }) {
  const submit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    const name = String(form.get('name') || '');
    const email = String(form.get('email') || '');
    const phone = String(form.get('phone') || '');
    const topic = String(form.get('topic') || '');
    const message = String(form.get('message') || '');
    const subject = career ? `Career inquiry from ${name}` : `Website inquiry: ${topic || 'New project'}`;
    const body = [`Name: ${name}`, `Email: ${email}`, `Phone: ${phone}`, topic ? `${career ? 'Role' : 'Area of interest'}: ${topic}` : '', '', message].filter(Boolean).join('\n');
    const destinationEmail = career ? companyContact.careersEmail : companyContact.businessInquiryEmail;
    window.location.href = `mailto:${destinationEmail}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
  };

  return (
    <form className="contact-form" id={career ? 'job-application-form' : 'contact-form'} onSubmit={submit}>
      <div className="field-grid">
        <label><span>Full name *</span><input name="name" required placeholder="Your name" /></label>
        <label><span>Email address *</span><input name="email" type="email" required placeholder="you@company.com" /></label>
        <label><span>Phone number</span><input name="phone" type="tel" placeholder="Your contact number" /></label>
        <label><span>{career ? 'Role of interest' : 'Area of interest'} *</span><select name="topic" required defaultValue=""><option value="" disabled>Select one</option>{career ? <><option>Customer operations</option><option>Business process operations</option><option>Healthcare operations</option><option>Digital operations</option><option>Leadership or management</option><option>General application</option></> : <><option>Customer Experience</option><option>Business Process Outsourcing</option><option>Medical Billing & RCM</option><option>RPM & CCM Operations</option><option>Digital Growth</option><option>Data & Technology Operations</option><option>Partnership opportunity</option></>}</select></label>
      </div>
      <label className="field-message"><span>{career ? 'Tell us about your experience *' : 'Tell us about the project *'}</span><textarea name="message" required rows={6} placeholder={career ? 'Share your experience, availability and the kind of role you are seeking.' : 'What is happening today, what needs to improve and what outcome are you working toward?'} /></label>
      <label className="field-consent"><input type="checkbox" required /><span>I agree that Shan Communications may use this information to respond to my inquiry.</span></label>
      <button type="submit">{career ? 'Send application' : 'Send inquiry'} <span>↗</span></button>
      <small>Please do not include medical records, payment-card data, government identifiers or other highly sensitive information in this general form.</small>
    </form>
  );
}
