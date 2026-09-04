import type { Metadata } from 'next';
import Link from 'next/link';
import { JobApplicationForm } from '../components/JobApplicationForm';
import { PageHero } from '../components/PageHero';
import { Reveal } from '../components/Reveal';

export const metadata: Metadata = {
  title: 'Jobs',
  description: 'Explore jobs and submit a professional application to Shan Communications.',
};

const values = [
  ['01', 'Clear expectations', 'Know the role, operating standard and what successful work looks like.'],
  ['02', 'Practical learning', 'Build capability through guidance, feedback and real operating experience.'],
  ['03', 'Respectful accountability', 'Own the work, communicate directly and improve with the team.'],
  ['04', 'Earned growth', 'Progress through consistency, skill and responsibility.'],
];

const jobAreas = [
  ['Customer operations', 'Customer support, contact-center delivery, sales support, lead handling and quality-focused roles.'],
  ['Healthcare operations', 'Non-clinical medical billing, revenue-cycle, enrollment, RPM and CCM operational support.'],
  ['Digital & technology', 'Digital marketing, reporting, data operations, automation, creative and technical support.'],
];

export default function JobsPage() {
  return (
    <main>
      <PageHero eyebrow="Jobs" title="Build experience that moves your career forward." description="Explore the work, understand our hiring process and submit a complete application to Shan Communications." image="/assets/shan-call-center.jpg" action={{ label: 'Apply now', href: '#job-application-form' }} />

      <section className="career-intro section-space">
        <div className="shell story-grid">
          <Reveal><span className="eyebrow">Jobs at Shan</span><h2>Professional growth grounded in real work.</h2></Reveal>
          <Reveal className="story-body" delay={70}><p className="large-copy">People perform better when expectations are clear, support is available and feedback leads to measurable improvement.</p><p>Applications are reviewed against current client, program and operational needs. A submitted application is not a guarantee of an interview or opening.</p><Link className="button button-dark jobs-apply-button" href="#job-application-form">Apply now <span>↗</span></Link></Reveal>
        </div>
      </section>

      <section className="jobs-areas section-soft section-space">
        <div className="shell">
          <Reveal className="editorial-heading"><div><span className="eyebrow">Opportunity areas</span><p className="section-index">Where you may contribute</p></div><h2>Different disciplines. One operating standard.</h2></Reveal>
          <div className="jobs-area-grid">
            {jobAreas.map(([title, text], index) => <Reveal key={title} delay={index * 70}><article><span>0{index + 1}</span><h3>{title}</h3><p>{text}</p></article></Reveal>)}
          </div>
        </div>
      </section>

      <section className="career-values section-dark section-space">
        <div className="shell">
          <Reveal className="editorial-heading editorial-heading-light"><div><span className="eyebrow eyebrow-light">Working here</span><p className="section-index">What to expect</p></div><h2>Structure, support and responsibility from the beginning.</h2></Reveal>
          <div className="principles-grid">{values.map(([number, title, text], index) => <Reveal key={number} delay={index * 60}><article><span>{number}</span><h3>{title}</h3><p>{text}</p></article></Reveal>)}</div>
        </div>
      </section>

      <section className="hiring-process section-space">
        <div className="shell">
          <Reveal className="editorial-heading"><div><span className="eyebrow">Hiring process</span><p className="section-index">What happens next</p></div><h2>A straightforward process for understanding fit.</h2></Reveal>
          <div className="operating-steps">
            <Reveal><article><span>01</span><i /><h3>Application review</h3><p>We review your experience, communication, availability and preferred area.</p></article></Reveal>
            <Reveal delay={60}><article><span>02</span><i /><h3>Initial conversation</h3><p>A focused discussion covers expectations and relevant experience.</p></article></Reveal>
            <Reveal delay={120}><article><span>03</span><i /><h3>Role assessment</h3><p>The next step may include an interview or practical assessment.</p></article></Reveal>
            <Reveal delay={180}><article><span>04</span><i /><h3>Offer & preparation</h3><p>Successful candidates receive role details and onboarding information.</p></article></Reveal>
          </div>
        </div>
      </section>

      <section className="career-apply section-soft section-space">
        <div className="shell jobs-apply-grid">
          <Reveal className="contact-information"><span className="eyebrow">Apply for jobs</span><h2>Tell us where you can contribute.</h2><p>Submit one complete application with your current details, relevant experience, availability and either an uploaded CV or a working CV link.</p><div className="career-note"><strong>Before applying</strong><ul><li>Use accurate and current information</li><li>Upload a supported CV file or provide an accessible link</li><li>Describe the work you can perform confidently</li><li>Do not submit sensitive identity documents</li></ul></div></Reveal>
          <Reveal delay={80}><JobApplicationForm /></Reveal>
        </div>
      </section>
    </main>
  );
}
