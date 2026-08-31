import type { Metadata } from 'next';
import Link from 'next/link';
import { notFound } from 'next/navigation';
import { CallToAction } from '../../components/CallToAction';
import { PageHero } from '../../components/PageHero';
import { Reveal } from '../../components/Reveal';
import { getService, services } from '../../data/site';

export function generateStaticParams() { return services.map((service) => ({ slug: service.slug })); }

export async function generateMetadata({ params }: { params: Promise<{ slug: string }> }): Promise<Metadata> {
  const { slug } = await params;
  const service = getService(slug);
  if (!service) return {};
  return { title: service.title, description: service.summary };
}

export default async function ServicePage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const service = getService(slug);
  if (!service) notFound();
  const related = services.filter((item) => item.slug !== service.slug).slice(0, 3);

  return (
    <main>
      <PageHero eyebrow={service.eyebrow} title={service.title} description={service.summary} image={service.image} parent={{ label: 'Solutions', href: '/services' }} />
      <section className="service-intro section-space">
        <div className="shell service-intro-grid">
          <Reveal><span className="eyebrow">The capability</span><h2>Structured around the operation—not a generic package.</h2></Reveal>
          <Reveal className="service-intro-copy" delay={70}><p className="large-copy">{service.intro}</p><Link className="button button-dark" href="/contact#contact-form" scroll={false}>Discuss this capability <span>↗</span></Link></Reveal>
        </div>
      </section>
      <section className="capability-detail section-soft section-space">
        <div className="shell capability-detail-grid">
          <Reveal className="capability-list"><span className="eyebrow">What we can support</span><h2>Capability coverage</h2>{service.capabilities.map((item, index) => <div key={item}><span>0{index + 1}</span><strong>{item}</strong></div>)}</Reveal>
          <Reveal className="outcomes-panel" delay={100}><span className="eyebrow eyebrow-light">Designed outcomes</span><h3>What the operating model is built to create.</h3><ul>{service.outcomes.map((item) => <li key={item}>{item}</li>)}</ul><p>Scope, measures and responsibilities are confirmed with each partner before launch.</p></Reveal>
        </div>
      </section>
      <section className="service-process section-space">
        <div className="shell">
          <Reveal className="editorial-heading"><div><span className="eyebrow">How it works</span><p className="section-index">Delivery path</p></div><h2>A practical path from discovery to continuous operation.</h2></Reveal>
          <div className="operating-steps">{service.process.map((step, index) => <Reveal key={step.title} delay={index * 70}><article><span>0{index + 1}</span><i /><h3>{step.title}</h3><p>{step.text}</p></article></Reveal>)}</div>
        </div>
      </section>
      <section className="fit-section section-dark section-space">
        <div className="shell fit-grid">
          <Reveal><span className="eyebrow eyebrow-light">When this fits</span><h2>Designed for operating situations that need greater clarity and capacity.</h2></Reveal>
          <Reveal className="fit-list" delay={80}>{service.fit.map((item, index) => <div key={item}><span>0{index + 1}</span><strong>{item}</strong></div>)}</Reveal>
        </div>
      </section>
      <section className="related-services section-space">
        <div className="shell"><Reveal className="editorial-heading"><div><span className="eyebrow">Related solutions</span><p className="section-index">Connected capabilities</p></div><h2>Build a wider operating model when the work crosses functions.</h2></Reveal><div className="three-column-cards related-card-list">{related.map((item, index) => <Reveal key={item.slug} delay={index * 70}><Link href={`/services/${item.slug}`}><span>{item.number}</span><h3>{item.shortTitle}</h3><p>{item.summary}</p><i>↗</i></Link></Reveal>)}</div></div>
      </section>
      <CallToAction title={`Let’s discuss ${service.shortTitle.toLowerCase()}.`} />
    </main>
  );
}
