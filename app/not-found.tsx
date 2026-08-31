import Link from 'next/link';
import { sitePath } from './lib/sitePath';

export default function NotFound() {
  return (
    <main className="not-found-page" style={{ backgroundImage: `linear-gradient(125deg, rgb(6 18 35 / 96%), rgb(16 42 80 / 91%)), url('${sitePath('/assets/shan-call-center-1.jpg')}')` }}>
      <div className="shell">
        <span className="eyebrow eyebrow-light">404 · Page not found</span>
        <h1>This page is not part of the operation.</h1>
        <p>The address may have changed, or the page may no longer exist.</p>
        <div><Link className="button button-light" href="/">Return home <span>↗</span></Link><Link className="button button-ghost" href="/contact">Contact us</Link></div>
      </div>
    </main>
  );
}
