'use client';

import { FormEvent, useState } from 'react';
import { initialSubmissionStatus, submitWebsiteForm, type SubmissionStatus } from '../lib/forms';

export function ContactForm({ career = false }: { career?: boolean }) {
  const [status, setStatus] = useState<SubmissionStatus>(initialSubmissionStatus);

  const submit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    const form = event.currentTarget;
    setStatus({ kind: 'submitting', message: 'Submitting securely…' });
    try {
      const message = await submitWebsiteForm(form);
      form.reset();
      setStatus({ kind: 'success', message });
    } catch (error) {
      setStatus({ kind: 'error', message: error instanceof Error ? error.message : 'We could not submit your information. Please try again.' });
    }
  };

  return (
    <form className="contact-form" id={career ? 'job-application-form' : 'contact-form'} onSubmit={submit} encType="multipart/form-data">
      <input type="hidden" name="formType" value={career ? 'job' : 'business'} />
      <label className="form-honeypot" aria-hidden="true">Company website<input name="companyWebsite" tabIndex={-1} autoComplete="off" /></label>
      <div className="field-grid">
        <label><span>Full name *</span><input name="name" required placeholder="Your name" /></label>
        <label><span>Email address *</span><input name="email" type="email" required placeholder="you@company.com" /></label>
        <label><span>Phone number</span><input name="phone" type="tel" placeholder="Your contact number" /></label>
        <label><span>{career ? 'Role of interest' : 'Area of interest'} *</span><select name="topic" required defaultValue=""><option value="" disabled>Select one</option>{career ? <><option>Customer operations</option><option>Business process operations</option><option>Healthcare operations</option><option>Digital operations</option><option>Leadership or management</option><option>General application</option></> : <><option>Customer Experience</option><option>Business Process Outsourcing</option><option>Medical Billing & RCM</option><option>RPM & CCM Operations</option><option>Digital Growth</option><option>Data & Technology Operations</option><option>Partnership opportunity</option></>}</select></label>
      </div>
      <label className="field-message"><span>{career ? 'Tell us about your experience *' : 'Tell us about the project *'}</span><textarea name="message" required rows={6} placeholder={career ? 'Share your experience, availability and the kind of role you are seeking.' : 'What is happening today, what needs to improve and what outcome are you working toward?'} /></label>
      <label className="field-consent"><input name="consent" type="checkbox" value="accepted" required /><span>I agree that Shan Communications may use this information to respond to my inquiry.</span></label>
      <button type="submit" disabled={status.kind === 'submitting'}>{status.kind === 'submitting' ? 'Submitting…' : career ? 'Send application' : 'Send inquiry'} <span>{status.kind === 'submitting' ? '•' : '↗'}</span></button>
      {status.kind !== 'idle' ? <p className={`form-status form-status-${status.kind}`} role="status" aria-live="polite">{status.message}</p> : null}
      <small>Please do not include medical records, payment-card data, government identifiers or other highly sensitive information in this general form.</small>
    </form>
  );
}
