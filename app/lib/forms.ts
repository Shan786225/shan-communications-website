import { sitePath } from './sitePath';

type SubmissionResponse = {
  success?: boolean;
  message?: string;
};

export type SubmissionStatus = {
  kind: 'idle' | 'submitting' | 'success' | 'error';
  message: string;
};

export const initialSubmissionStatus: SubmissionStatus = { kind: 'idle', message: '' };

export async function submitWebsiteForm(form: HTMLFormElement) {
  if (window.location.hostname.endsWith('github.io')) {
    throw new Error('Form submission is available on the production website at shancommunication.com.');
  }

  const response = await fetch(sitePath('/api/submit-form.php'), {
    method: 'POST',
    headers: { Accept: 'application/json' },
    body: new FormData(form),
  });

  const payload = await response.json().catch(() => ({})) as SubmissionResponse;
  if (!response.ok || !payload.success) {
    throw new Error(payload.message || 'We could not submit your information. Please try again.');
  }

  return payload.message || 'Thank you. Your information has been submitted successfully.';
}
