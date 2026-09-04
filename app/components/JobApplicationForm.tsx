'use client';

import { FormEvent, useState } from 'react';
import { HiOutlineArrowUpTray, HiOutlineLink } from 'react-icons/hi2';
import { companyContact } from '../data/company';

const MAX_CV_SIZE = 10 * 1024 * 1024;
const ALLOWED_CV_EXTENSIONS = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

export function JobApplicationForm() {
  const [resumeError, setResumeError] = useState('');
  const [selectedFile, setSelectedFile] = useState('');

  const validateFile = (file?: File) => {
    if (!file || file.size === 0) return '';
    const extension = file.name.split('.').pop()?.toLowerCase() || '';
    if (!ALLOWED_CV_EXTENSIONS.includes(extension)) return 'Use a PDF, DOC, DOCX, JPG, JPEG or PNG file.';
    if (file.size > MAX_CV_SIZE) return 'The CV file must be 10 MB or smaller.';
    return '';
  };

  const submit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    const value = (name: string) => String(form.get(name) || '').trim();
    const resume = form.get('resumeFile');
    const resumeFile = resume instanceof File && resume.size > 0 ? resume : undefined;
    const resumeUrl = value('resume');
    const fileError = validateFile(resumeFile);

    if (fileError) {
      setResumeError(fileError);
      event.currentTarget.querySelector<HTMLInputElement>('input[name="resumeFile"]')?.focus();
      return;
    }
    if (!resumeFile && !resumeUrl) {
      setResumeError('Upload your CV or provide a CV link to continue.');
      event.currentTarget.querySelector<HTMLInputElement>('input[name="resumeFile"]')?.focus();
      return;
    }

    setResumeError('');
    const name = value('name');
    const role = value('role');
    const subject = `Job application — ${role || 'General application'} — ${name}`;
    const body = [
      `Full name: ${name}`,
      `Email: ${value('email')}`,
      `Phone / WhatsApp: ${value('phone')}`,
      `Role of interest: ${role}`,
      `Relevant experience: ${value('experience')}`,
      `Availability / notice period: ${value('availability')}`,
      `CV file selected: ${resumeFile ? `${resumeFile.name} — please attach this file before sending` : 'Not provided'}`,
      `CV / resume link: ${resumeUrl || 'Not provided'}`,
      '',
      'Experience summary:',
      value('message'),
    ].join('\n');

    window.location.href = `mailto:${companyContact.careersEmail}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
  };

  return (
    <form className="contact-form job-application-form" id="job-application-form" onSubmit={submit}>
      <div className="form-heading">
        <span>General application</span>
        <h3>Apply to join Shan Communications.</h3>
        <p>Complete every required field so our hiring team can understand your experience and availability.</p>
      </div>
      <div className="field-grid">
        <label><span>Full name *</span><input name="name" required autoComplete="name" placeholder="Your full name" /></label>
        <label><span>Email address *</span><input name="email" type="email" required autoComplete="email" placeholder="you@email.com" /></label>
        <label><span>Phone / WhatsApp *</span><input name="phone" type="tel" required autoComplete="tel" placeholder="Your contact number" /></label>
        <label><span>Role of interest *</span><select name="role" required defaultValue=""><option value="" disabled>Select an area</option><option>Customer & contact-center operations</option><option>Business process operations</option><option>Medical billing & healthcare operations</option><option>Sales & business development</option><option>Digital marketing & creative</option><option>Data, technology & automation</option><option>Team leadership & management</option><option>General application</option></select></label>
        <label><span>Relevant experience *</span><select name="experience" required defaultValue=""><option value="" disabled>Select experience</option><option>Entry level / under 1 year</option><option>1–2 years</option><option>3–5 years</option><option>6–9 years</option><option>10+ years</option></select></label>
        <label><span>Availability / notice period *</span><input name="availability" required placeholder="Immediate, 2 weeks, 30 days…" /></label>
      </div>
      <fieldset className="resume-choice" aria-describedby={resumeError ? 'resume-error' : undefined}>
        <legend>CV / résumé *</legend>
        <label className="resume-upload">
          <input
            name="resumeFile"
            type="file"
            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/jpeg,image/png"
            onChange={(event) => {
              const file = event.currentTarget.files?.[0];
              const error = validateFile(file);
              setSelectedFile(error ? '' : file?.name || '');
              setResumeError(error);
            }}
          />
          <span className="resume-choice-icon"><HiOutlineArrowUpTray aria-hidden="true" /></span>
          <span><strong>{selectedFile || 'Upload your CV'}</strong><small>PDF, DOC, DOCX, JPG, JPEG or PNG · maximum 10 MB</small></span>
        </label>
        <span className="resume-choice-or">or</span>
        <label className="resume-url">
          <span className="resume-choice-icon"><HiOutlineLink aria-hidden="true" /></span>
          <span><strong>Provide a CV link</strong><small>Google Drive, OneDrive, Dropbox or another accessible URL</small></span>
          <input name="resume" type="url" inputMode="url" placeholder="https://" onChange={() => setResumeError('')} />
        </label>
        {resumeError ? <p className="form-error" id="resume-error" role="alert">{resumeError}</p> : null}
      </fieldset>
      <label className="field-message"><span>Experience summary *</span><textarea name="message" required rows={4} placeholder="Briefly describe your relevant experience, strongest skills and the type of work you want to do." /></label>
      <label className="field-consent"><input type="checkbox" required /><span>I confirm that the information is accurate and agree that Shan Communications may use it to evaluate my application and contact me about suitable opportunities.</span></label>
      <button type="submit">Prepare application email <span>↗</span></button>
      <small>If you upload a file, attach it to the prepared email before sending.</small>
    </form>
  );
}
