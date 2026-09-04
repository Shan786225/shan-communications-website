'use client';

import { FormEvent, useState } from 'react';
import { HiOutlineArrowUpTray, HiOutlineLink } from 'react-icons/hi2';
import { initialSubmissionStatus, submitWebsiteForm, type SubmissionStatus } from '../lib/forms';

const MAX_CV_SIZE = 10 * 1024 * 1024;
const ALLOWED_CV_EXTENSIONS = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

export function JobApplicationForm() {
  const [resumeError, setResumeError] = useState('');
  const [selectedFile, setSelectedFile] = useState('');
  const [status, setStatus] = useState<SubmissionStatus>(initialSubmissionStatus);

  const validateFile = (file?: File) => {
    if (!file || file.size === 0) return '';
    const extension = file.name.split('.').pop()?.toLowerCase() || '';
    if (!ALLOWED_CV_EXTENSIONS.includes(extension)) return 'Use a PDF, DOC, DOCX, JPG, JPEG or PNG file.';
    if (file.size > MAX_CV_SIZE) return 'The CV file must be 10 MB or smaller.';
    return '';
  };

  const submit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    const formElement = event.currentTarget;
    const form = new FormData(formElement);
    const value = (name: string) => String(form.get(name) || '').trim();
    const resume = form.get('resumeFile');
    const resumeFile = resume instanceof File && resume.size > 0 ? resume : undefined;
    const resumeUrl = value('resume');
    const fileError = validateFile(resumeFile);

    if (fileError) {
      setResumeError(fileError);
      formElement.querySelector<HTMLInputElement>('input[name="resumeFile"]')?.focus();
      return;
    }
    if (!resumeFile && !resumeUrl) {
      setResumeError('Upload your CV or provide a CV link to continue.');
      formElement.querySelector<HTMLInputElement>('input[name="resumeFile"]')?.focus();
      return;
    }

    setResumeError('');
    setStatus({ kind: 'submitting', message: 'Submitting your application securely…' });
    try {
      const message = await submitWebsiteForm(formElement);
      formElement.reset();
      setSelectedFile('');
      setStatus({ kind: 'success', message });
    } catch (error) {
      setStatus({ kind: 'error', message: error instanceof Error ? error.message : 'We could not submit your application. Please try again.' });
    }
  };

  return (
    <form className="contact-form job-application-form" id="job-application-form" onSubmit={submit} encType="multipart/form-data">
      <input type="hidden" name="formType" value="job" />
      <label className="form-honeypot" aria-hidden="true">Company website<input name="companyWebsite" tabIndex={-1} autoComplete="off" /></label>
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
      <label className="field-consent"><input name="consent" type="checkbox" value="accepted" required /><span>I confirm that the information is accurate and agree that Shan Communications may use it to evaluate my application and contact me about suitable opportunities.</span></label>
      <button type="submit" disabled={status.kind === 'submitting'}>{status.kind === 'submitting' ? 'Submitting application…' : 'Submit application'} <span>{status.kind === 'submitting' ? '•' : '↗'}</span></button>
      {status.kind !== 'idle' ? <p className={`form-status form-status-${status.kind}`} role="status" aria-live="polite">{status.message}</p> : null}
      <small>Your application and CV are sent directly to the Shan Communications hiring inbox.</small>
    </form>
  );
}
