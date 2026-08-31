import type { Metadata } from 'next';
import Link from 'next/link';
import { CallToAction } from '../components/CallToAction';
import { PageHero } from '../components/PageHero';
import { Reveal } from '../components/Reveal';
import { services } from '../data/site';

export const metadata: Metadata = {
  title: 'Business & Healthcare Solutions',
  description: 'Explore Shan Communications customer experience, BPO, medical billing, RPM, CCM, digital growth and technology operations.',
};

export default function ServicesPage() {
  return (
    <main>
      <PageHero eyebrow="Solutions" title="Capabilities organized around real operating needs." description="Start with one focused workflow or connect multiple service lines through a single operating relationship." image="/assets/shan-call-center-1.jpg" />
      <section className="services-intro section-space">
        <div className="shell story-grid">
          <Reveal><span className="eyebrow">What we do</span><h2>Not a menu of disconnected tasks.</h2></Reveal>
          <Reveal className="story-body" delay={70}><p className="large-copy">Each solution is designed around the workflow, the people who depend on it and the decisions required to keep it moving.</p><p>We can provide a dedicated capability, support an existing internal team or combine multiple operational functions under one governance model.</p></Reveal>
        </div>
      </section>
      <section className="services-catalog section-space section-soft">
        <div className="shell services-catalog-list">
          {services.map((service, index) => (
            <Reveal key={service.slug} delay={(index % 2) * 70}>
              <Link className="catalog-card" href={`/services/${service.slug}`}>
                <div className="catalog-image"><img src={service.image} alt="" /><span>{service.number}</span></div>
                <div className="catalog-copy"><small>{service.eyebrow}</small><h2>{service.title}</h2><p>{service.summary}</p><ul>{service.capabilities.slice(0, 4).map((item) => <li key={item}>{item}</li>)}</ul><strong>Explore this solution <i>↗</i></strong></div>
              </Link>
            </Reveal>
          ))}
        </div>
      </section>
      <section className="engagement-models section-space">
        <div className="shell">
          <Reveal className="editorial-heading"><div><span className="eyebrow">Engagement models</span><p className="section-index">Built to fit</p></div><h2>Choose the operating relationship that matches the work.</h2></Reveal>
          <div className="three-column-cards">
            <Reveal><article><span>01</span><h3>Dedicated team</h3><p>A prepared team focused on a defined program, workflow or business function.</p></article></Reveal>
            <Reveal delay={70}><article><span>02</span><h3>Managed process</h3><p>End-to-end responsibility for an agreed process with clear controls and reporting.</p></article></Reveal>
            <Reveal delay={140}><article><span>03</span><h3>White-label operations</h3><p>Behind-the-brand delivery structured around partner standards and ownership boundaries.</p></article></Reveal>
          </div>
        </div>
      </section>
      <CallToAction />
    </main>
  );
}
