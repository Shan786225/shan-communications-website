import type { Metadata } from 'next';
import { CallToAction } from '../components/CallToAction';
import { PageHero } from '../components/PageHero';
import { Reveal } from '../components/Reveal';

export const metadata: Metadata = {
  title: 'Partnerships',
  description: 'Explore direct, dedicated-team and white-label partnership models with Shan Communications.',
};

export default function PartnershipsPage() {
  return (
    <main>
      <PageHero eyebrow="Partnerships" title="A delivery relationship built around clear responsibility." description="Direct, dedicated-team and white-label operating models for organizations that need capable execution behind their growth." image="/assets/shan-call-center-1.jpg" />
      <section className="partnership-intro section-space">
        <div className="shell story-grid">
          <Reveal><span className="eyebrow">Ways to partner</span><h2>Designed around the role you need us to play.</h2></Reveal>
          <Reveal className="story-body" delay={70}><p className="large-copy">The right model depends on who owns the customer relationship, who controls the workflow and where final decisions must remain.</p><p>We define those responsibilities before launch so teams can move quickly without creating ambiguity for partners, customers or patients.</p></Reveal>
        </div>
      </section>
      <section className="partnership-models section-soft section-space">
        <div className="shell three-column-cards">
          <Reveal><article><span>01</span><h3>Direct delivery</h3><p>Shan Communications contracts and operates the agreed business or healthcare support workflow directly.</p></article></Reveal>
          <Reveal delay={70}><article><span>02</span><h3>Dedicated operations</h3><p>A named team works around a partner’s systems, procedures, reporting and management rhythm.</p></article></Reveal>
          <Reveal delay={140}><article><span>03</span><h3>White-label partnership</h3><p>Shan delivers behind a partner brand with explicit ownership, communication and compliance boundaries.</p></article></Reveal>
        </div>
      </section>
      <section className="responsibility-section section-space">
        <div className="shell">
          <Reveal className="editorial-heading"><div><span className="eyebrow">Responsibility model</span><p className="section-index">Before launch</p></div><h2>Every partnership begins by making ownership visible.</h2></Reveal>
          <div className="responsibility-table">
            <div className="responsibility-head"><span>Operating area</span><span>Partner organization</span><span>Shan Communications</span></div>
            <div><strong>Business and program decisions</strong><span>Defines objectives, policies and final approvals</span><span>Executes within approved scope and escalates exceptions</span></div>
            <div><strong>Systems and access</strong><span>Approves platforms, roles and access requirements</span><span>Uses approved access and follows defined controls</span></div>
            <div><strong>Workflow delivery</strong><span>Provides required information and decision support</span><span>Operates assigned queues, follow-up and reporting</span></div>
            <div><strong>Healthcare clinical decisions</strong><span>Provider retains clinical eligibility, care and medical guidance</span><span>Supports only defined non-clinical administrative work</span></div>
            <div><strong>Performance review</strong><span>Participates in decisions and scope changes</span><span>Provides operating visibility, findings and action follow-up</span></div>
          </div>
        </div>
      </section>
      <CallToAction title="Let’s define a partnership model that protects clarity." />
    </main>
  );
}
