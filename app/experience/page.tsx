import type { Metadata } from 'next';
import { CallToAction } from '../components/CallToAction';
import { PageHero } from '../components/PageHero';
import { Reveal } from '../components/Reveal';
import { experiencePrograms } from '../data/company';

export const metadata: Metadata = { title: 'Experience', description: 'Explore Shan Communications experience across customer, healthcare, growth and technology operations.' };

export default function ExperiencePage() {
  return <main>
    <PageHero eyebrow="Experience" title="Hands-on experience across the complete operation." description="Our capabilities connect customer-facing work with the processes, systems and management routines required to sustain it." image="/assets/shan-call-center-1.jpg" />
    <section className="experience-intro section-space"><div className="shell story-grid"><Reveal><span className="eyebrow">Capability experience</span><h2>Experience matters when it improves the operating decision.</h2></Reveal><Reveal className="story-body" delay={70}><p className="large-copy">We apply experience as a practical design input: how work should enter a queue, who should own it, where it can fail and what information leaders need to act.</p><p>Our operating record spans sales, live transfers, enrollment, connectivity, home services, claims intake, healthcare administration and the supporting data and technology systems behind delivery.</p></Reveal></div></section>
    <section className="project-portfolio section-space"><div className="shell"><Reveal className="editorial-heading"><div><span className="eyebrow">Project portfolio</span><p className="section-index">Published experience</p></div><h2>Six years of campaign learning—organized into capabilities partners can use today.</h2></Reveal><div className="project-portfolio-grid">{experiencePrograms.map((area, index) => <Reveal key={area.number} delay={index * 70}><article><span>{area.number}</span><h3>{area.title}</h3><p>{area.description}</p><ul>{area.programs.map((program) => <li key={program}>{program}</li>)}</ul></article></Reveal>)}</div><Reveal className="profile-source-note"><p>This portfolio is based on the projects published by Shan Khan and the current Shan Communications service record.</p><a href="https://www.linkedin.com/in/shan-khan-682a7b249" target="_blank" rel="noreferrer">Open the LinkedIn project record <span>↗</span></a></Reveal></div></section>
    <CallToAction title="Have a project that needs an accountable operating team?" />
  </main>;
}
