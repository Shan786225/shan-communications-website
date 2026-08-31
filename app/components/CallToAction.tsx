import Link from 'next/link';

export function CallToAction({ title = 'Ready to design a stronger operation?', text = 'Tell us what needs to improve, where demand is growing and what a successful outcome should look like.' }: { title?: string; text?: string }) {
  return (
    <section className="band-cta">
      <div className="shell band-cta-inner">
        <div><span className="eyebrow eyebrow-light">Let’s talk</span><h2>{title}</h2><p>{text}</p></div>
        <Link href="/contact#contact-form" scroll={false}>Start a conversation <span>↗</span></Link>
      </div>
    </section>
  );
}
