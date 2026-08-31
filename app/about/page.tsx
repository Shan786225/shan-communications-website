import type { Metadata } from 'next';
import Link from 'next/link';
import { CallToAction } from '../components/CallToAction';
import { PageHero } from '../components/PageHero';
import { Reveal } from '../components/Reveal';
import { SocialLinks } from '../components/SocialLinks';
import { shanKhanSocialLinks } from '../data/company';

export const metadata: Metadata = {
  title: 'About Us',
  description: 'Learn how Shan Communications combines people, process and technology across business and healthcare operations.',
};

export default function AboutPage() {
  return (
    <main>
      <PageHero eyebrow="About Shan" title="Built to take responsibility for the work." description="Shan Communications is a business and healthcare operations company focused on clear systems, prepared people and accountable delivery." image="/assets/shan-call-center-5.jpg" />

      <section className="story-section section-space">
        <div className="shell story-grid">
          <Reveal><span className="eyebrow">Our company</span><h2>More than a service provider. An operating partner.</h2></Reveal>
          <Reveal className="story-body" delay={70}>
            <p className="large-copy">We help organizations turn important recurring work into an operation people can understand, manage and improve.</p>
            <p>Our work spans customer experience, business process outsourcing, healthcare administration, connected-care enrollment, digital performance and technology-supported operations.</p>
            <p>Shan Communications is both a directly presented operating company and the parent platform for specialized businesses developed around new markets and capabilities. That structure allows partners to access focused expertise without losing the benefit of shared operating discipline.</p>
          </Reveal>
        </div>
      </section>

      <section className="mission-section section-soft">
        <div className="shell mission-grid">
          <Reveal><article><span>Our mission</span><h3>To build reliable operations that help partners serve customers, manage work and grow with greater clarity.</h3></article></Reveal>
          <Reveal delay={80}><article className="mission-dark"><span>Our vision</span><h3>To become a trusted operating platform for specialized, technology-enabled business services.</h3></article></Reveal>
        </div>
      </section>

      <section className="journey-section section-space">
        <div className="shell">
          <Reveal className="editorial-heading"><div><span className="eyebrow">Our journey</span><p className="section-index">Company development</p></div><h2>Built through experience, expanded through responsibility.</h2></Reveal>
          <div className="journey-grid">
            <Reveal><article><span>2021 onward</span><h3>Sales, transfer & lead operations</h3><p>Hands-on delivery developed across Final Expense, Medicare, solar, home services, mortgage and criteria-led claims campaigns.</p></article></Reveal>
            <Reveal delay={60}><article><span>2023–2025</span><h3>Connectivity & enrollment programs</h3><p>Experience expanded through ACP, Lifeline, broadband, telecom, home internet and customer-acquisition operations.</p></article></Reveal>
            <Reveal delay={120}><article><span>2025–2026</span><h3>Healthcare operations</h3><p>Capabilities grew into RPM, CCM, medical billing, revenue-cycle and non-clinical patient-enrollment workflows.</p></article></Reveal>
            <Reveal delay={180}><article><span>Today</span><h3>An integrated operating platform</h3><p>Shan Communications combines office-based teams, technology, QA and management functions around partner-defined outcomes.</p></article></Reveal>
          </div>
        </div>
      </section>

      <section className="leadership-section section-space" id="leadership">
        <div className="shell">
          <Reveal className="editorial-heading"><div><span className="eyebrow">Leadership</span><p className="section-index">Accountability</p></div><h2>People responsible for the direction and operating standard.</h2></Reveal>
          <div className="leadership-detail-grid">
            <Reveal className="leadership-detail-card">
              <div className="leadership-card-image"><img src="/assets/shan-khan-executive.jpg" alt="Shan Khan (Ashan Ali)" /></div>
              <SocialLinks links={shanKhanSocialLinks} compact />
              <div className="leadership-card-copy"><span>Founder & Chief Executive Officer</span><h3>Shan Khan <small>(Ashan Ali)</small></h3><p>Shan Khan is the Founder and CEO of Shan Communications. His published project record spans Remote Patient Monitoring (RPM), Chronic Care Management (CCM), Lifeline and ACP enrollment, telecom and broadband, home services, solar, ACA and Medicare, final expense, mortgage refinance, debt settlement and mass-tort campaigns. Across these programs, he has led customer acquisition, live transfers, lead generation, appointments, enrollments and technology-enabled operations—connecting business development with practical systems, partner management and accountable delivery.</p></div>
            </Reveal>
            <Reveal className="leadership-detail-card leadership-detail-card-secondary" delay={100}>
              <div className="leadership-card-image"><img src="/assets/yasir-ali.jpeg" alt="Yasir Ali" /></div>
              <div className="leadership-card-copy"><span>Co-founder & Chief Operating Officer</span><h3>Yasir Ali</h3><p>Yasir’s experience across sales, partner relationships, project management and engineering supports the systems, execution and day-to-day discipline behind delivery.</p></div>
            </Reveal>
          </div>
          <Reveal className="leadership-note"><blockquote>“Our work is successful when the partner can see what is happening, who owns the next action and how the operation is improving.”</blockquote><Link href="/contact#contact-form" scroll={false}>Speak with our team <span>↗</span></Link></Reveal>
        </div>
      </section>

      <CallToAction title="Looking for a partner who can own the operation with you?" />
    </main>
  );
}
