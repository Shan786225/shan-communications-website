import type { Metadata } from 'next';
import Link from 'next/link';
import { CallToAction } from '../components/CallToAction';
import { PageHero } from '../components/PageHero';
import { Reveal } from '../components/Reveal';

export const metadata: Metadata = {
  title: 'Security & Compliance',
  description: 'How Shan Communications approaches access, privacy, quality and client-defined compliance requirements across business and healthcare operations.',
};

const frameworks = [
  {
    code: 'HIPAA',
    title: 'Healthcare privacy & security',
    text: 'HIPAA-aligned administrative, physical and technical safeguards are built into approved healthcare workflows, including role-based access, minimum-necessary handling, confidentiality training and incident escalation.',
  },
  {
    code: 'PCI DSS',
    title: 'Payment-data scope',
    text: 'When a program involves payment-account data, access and technology must remain inside the client-approved cardholder-data environment. General website and inquiry systems are not used to collect card data.',
  },
  {
    code: 'Privacy',
    title: 'Data protection',
    text: 'Collection, access, retention, transfer and deletion requirements are defined for the engagement, including client instructions and applicable jurisdictional requirements such as GDPR-style data-subject controls where relevant.',
  },
  {
    code: 'Outreach',
    title: 'Consent-led campaign controls',
    text: 'Outbound programs are designed around client-approved consent evidence, suppression rules, campaign criteria, calling windows, scripts and escalation requirements, including applicable TCPA and Do-Not-Call obligations.',
  },
  {
    code: 'Healthcare',
    title: 'Clinical boundaries',
    text: 'Shan supports non-clinical administrative work. Licensed providers retain clinical eligibility, medical necessity, patient-care decisions, orders and final approvals.',
  },
  {
    code: 'Evidence',
    title: 'Quality & audit readiness',
    text: 'Documented procedures, training records, access lists, quality reviews, reporting and incident records create an evidence trail that can support client governance and authorized audits.',
  },
] as const;

export default function SecurityPage() {
  return (
    <main>
      <PageHero eyebrow="Security & compliance" title="Trust is designed into the operation." description="We align access, people, systems and evidence to the data and responsibilities included in each client-approved scope." image="/assets/service-data-operations.jpg" />

      <section className="security-intro section-space">
        <div className="shell story-grid">
          <Reveal><span className="eyebrow">Our approach</span><h2>Framework-aware. Scope-specific. Evidence-led.</h2></Reveal>
          <Reveal className="story-body" delay={70}>
            <p className="large-copy">Security requirements become meaningful when they change how work is assigned, accessed, reviewed and escalated.</p>
            <p>Every engagement begins by identifying the information involved, the systems in use, the authorized roles and the obligations retained by the client. Controls are then documented in the operating model and reinforced through training, access governance, quality monitoring and review.</p>
            <div className="compliance-qualification"><strong>Important clarification</strong><p>Framework references describe the standards and safeguards our operating model can support. They do not represent an independent certification, legal opinion or blanket compliance claim unless that status is specifically documented in writing.</p></div>
          </Reveal>
        </div>
      </section>

      <section className="frameworks-section section-soft section-space">
        <div className="shell">
          <Reveal className="editorial-heading"><div><span className="eyebrow">Standards in scope</span><p className="section-index">Control mapping</p></div><h2>Requirements addressed according to the work and information involved.</h2></Reveal>
          <div className="frameworks-grid">
            {frameworks.map((framework, index) => (
              <Reveal key={framework.code} delay={index * 50}><article><span>{framework.code}</span><h3>{framework.title}</h3><p>{framework.text}</p></article></Reveal>
            ))}
          </div>
        </div>
      </section>

      <section className="security-sources section-space">
        <div className="shell security-sources-grid">
          <Reveal><span className="eyebrow">Authoritative references</span><h2>Built against the real standards—not marketing shorthand.</h2></Reveal>
          <Reveal delay={70}><div><a href="https://www.hhs.gov/hipaa/for-professionals/security/index.html" target="_blank" rel="noreferrer"><strong>U.S. HHS — HIPAA Security Rule</strong><span>Administrative, physical and technical safeguards ↗</span></a><a href="https://www.pcisecuritystandards.org/standards/pci-dss/" target="_blank" rel="noreferrer"><strong>PCI Security Standards Council — PCI DSS</strong><span>Payment-account data security requirements ↗</span></a><Link href="/privacy"><strong>Shan Communications — Privacy</strong><span>Website information practices ↗</span></Link></div></Reveal>
        </div>
      </section>

      <CallToAction title="Need an operating model built around defined security requirements?" />
    </main>
  );
}
