import Link from 'next/link';

export default function NotFound() {
  return (
    <main className="not-found-page">
      <div className="shell">
        <span className="eyebrow eyebrow-light">404 · Page not found</span>
        <h1>This page is not part of the operation.</h1>
        <p>The address may have changed, or the page may no longer exist.</p>
        <div><Link className="button button-light" href="/">Return home <span>↗</span></Link><Link className="button button-ghost" href="/contact">Contact us</Link></div>
      </div>
    </main>
  );
}
