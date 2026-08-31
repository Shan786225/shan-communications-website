import Link from 'next/link';
import { ImpactStats } from './components/ImpactStats';
import { Reveal } from './components/Reveal';
import { SocialLinks } from './components/SocialLinks';
import { experiencePrograms, shanKhanSocialLinks } from './data/company';
import { services } from './data/site';
import { sitePath } from './lib/sitePath';

export default function Home() {
  return (
    <main>
      <section className="home-hero">
        <img className="home-hero-image" src={sitePath('/assets/shan-call-center-1.jpg')} alt="Shan Communications operations floor" />
        <div className="home-hero-overlay" />
        <div className="hero-visual-field" aria-hidden="true">
          <i /><i /><i />
          <span /><span /><span />
          <b />
        </div>
        <div className="shell home-hero-content">
          <div className="hero-intro">
            <span className="hero-kicker">Business process · Healthcare · Growth operations</span>
            <h1 aria-label="Operations built to move business forward.">
              <span className="hero-title-line" aria-hidden="true">Operations built</span>
              <span className="hero-title-line" aria-hidden="true">to move business</span>
              <span className="hero-title-line" aria-hidden="true"><em>forward.</em></span>
            </h1>
            <p>Shan Communications designs and operates customer, business and healthcare workflows through prepared teams, clear ownership and practical technology.</p>
            <div className="hero-actions">
              <Link className="button button-light" href="/services">Explore our solutions <span>↗</span></Link>
              <Link className="button button-ghost" href="/contact#contact-form" scroll={false}>Discuss a project</Link>
            </div>
            <div className="hero-proof" aria-label="Company highlights">
              <div><strong>6+</strong><span>Years of operating experience</span></div>
              <div><strong>50+</strong><span>Successful stories</span></div>
              <div><strong>300+</strong><span>Total experiences</span></div>
            </div>
          </div>
        </div>
        <div className="hero-rail">
          <span>01</span><i /><p>Accountable delivery<br />across every handoff</p>
        </div>
        <Link className="hero-scroll" href="#overview">Scroll to explore <span>↓</span></Link>
      </section>

      <section className="capability-bar" aria-label="Core capabilities">
        <div className="shell capability-bar-inner">
          <span>Customer experience</span><i />
          <span>Business process</span><i />
          <span>Healthcare operations</span><i />
          <span>Digital growth</span>
        </div>
      </section>

      <ImpactStats />

      <section className="home-overview section-space" id="overview">
        <div className="shell">
          <Reveal className="editorial-heading">
            <div><span className="eyebrow">Who we are</span><p className="section-index">01 / Company</p></div>
            <h2>A direct operating company—and a platform for specialized businesses.</h2>
          </Reveal>
          <div className="overview-grid">
            <Reveal className="overview-copy">
              <p className="large-copy">Shan Communications brings people, process and technology together around the work partners rely on every day.</p>
              <p>We support organizations across customer experience, managed business processes, healthcare administration, connected-care enrollment and growth operations. Every engagement begins with the operating context—not a generic staffing package.</p>
              <Link className="text-link" href="/about">Discover Shan Communications <span>↗</span></Link>
            </Reveal>
            <Reveal className="overview-image" delay={100}>
              <img src={sitePath('/assets/shan-bpo.jpg')} alt="Professionals working together" />
              <div><span>Our standard</span><strong>Clarity before scale.</strong></div>
            </Reveal>
            <Reveal className="proof-panel" delay={160}>
              <article><strong>Direct delivery</strong><p>Shan Communications is presented directly to partners and operates the work.</p></article>
              <article><strong>Parent platform</strong><p>New specialized business lines can grow with shared operational support.</p></article>
              <article><strong>Defined ownership</strong><p>Queues, handoffs and decisions are designed to remain visible.</p></article>
            </Reveal>
          </div>
        </div>
      </section>

      <section className="home-services section-space section-dark">
        <div className="shell">
          <Reveal className="editorial-heading editorial-heading-light">
            <div><span className="eyebrow eyebrow-light">Solutions</span><p className="section-index">02 / Capabilities</p></div>
            <h2>One operating relationship. Multiple ways to create capacity.</h2>
          </Reveal>
          <div className="service-showcase">
            {services.map((service, index) => (
              <Reveal key={service.slug} delay={index * 45}>
                <Link className="service-showcase-card" href={`/services/${service.slug}`}>
                  <div className="service-card-image"><img src={sitePath(service.image)} alt="" /><span>{service.number}</span></div>
                  <div className="service-card-copy"><small>{service.eyebrow}</small><h3>{service.shortTitle}</h3><p>{service.summary}</p><i>Explore solution ↗</i></div>
                </Link>
              </Reveal>
            ))}
          </div>
          <Reveal className="services-footer-link"><Link href="/services">See the complete solutions portfolio <span>↗</span></Link></Reveal>
        </div>
      </section>

      <section className="campaign-record section-space section-soft">
        <div className="shell">
          <Reveal className="editorial-heading">
            <div><span className="eyebrow">Documented experience</span><p className="section-index">Profile-backed record</p></div>
            <h2>Experience developed across real campaigns, markets and operating models.</h2>
          </Reveal>
          <div className="campaign-record-grid">
            {experiencePrograms.map((area, index) => (
              <Reveal key={area.number} delay={index * 65}>
                <article>
                  <span>{area.number}</span>
                  <h3>{area.title}</h3>
                  <p>{area.description}</p>
                  <div>{area.programs.map((program) => <small key={program}>{program}</small>)}</div>
                </article>
              </Reveal>
            ))}
          </div>
          <Reveal className="profile-source-note">
            <p>Campaign history reflects the projects published on Shan Khan’s professional profile and Shan Communications’ operating portfolio.</p>
            <a href="https://www.linkedin.com/in/shan-khan-682a7b249" target="_blank" rel="noreferrer">View Shan Khan on LinkedIn <span>↗</span></a>
          </Reveal>
        </div>
      </section>

      <section className="leadership-preview section-space">
        <div className="shell leadership-preview-grid">
          <Reveal className="leadership-preview-copy">
            <span className="eyebrow">Leadership</span>
            <h2>Accountability starts with the people leading the operation.</h2>
            <p>Shan Communications combines business, technology, sales and operational experience to build partnerships around clear outcomes.</p>
            <Link className="text-link" href="/about#leadership">Meet the leadership team <span>↗</span></Link>
          </Reveal>
          <Reveal className="leader-card" delay={80}>
            <div className="leader-portrait"><img src={sitePath('/assets/shan-khan-executive.jpg')} alt="Shan Khan (Ashan Ali)" /><div className="leader-card-copy"><span>Founder & Chief Executive Officer</span><strong>Shan Khan <small>(Ashan Ali)</small></strong></div></div>
            <SocialLinks links={shanKhanSocialLinks} compact />
          </Reveal>
          <Reveal className="leader-card leader-card-offset" delay={140}>
            <div className="leader-portrait"><img src={sitePath('/assets/yasir-ali.jpeg')} alt="Yasir Ali" /><div className="leader-card-copy"><span>Co-founder & Chief Operating Officer</span><strong>Yasir Ali</strong></div></div>
          </Reveal>
        </div>
      </section>
    </main>
  );
}
