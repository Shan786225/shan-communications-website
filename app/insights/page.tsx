import type { Metadata } from 'next';
import Link from 'next/link';
import { CallToAction } from '../components/CallToAction';
import { PageHero } from '../components/PageHero';
import { Reveal } from '../components/Reveal';
import { insights } from '../data/site';
import { sitePath } from '../lib/sitePath';

export const metadata: Metadata = { title: 'Insights', description: 'Practical thinking from Shan Communications on BPO, healthcare, revenue cycle, RPM, CCM and operating models.' };

export default function InsightsPage() {
  return <main>
    <PageHero eyebrow="Insights" title="Practical thinking for better operations." description="Ideas, frameworks and working principles for organizations building customer, business and healthcare operations." image="/assets/service-data-operations.jpg" />
    <section className="featured-insight section-space"><div className="shell featured-insight-grid"><Reveal className="featured-insight-image"><img src={sitePath('/assets/shan-bpo.jpg')} alt="Operational planning" /><span>Featured insight</span></Reveal><Reveal className="featured-insight-copy" delay={70}><small>{insights[0].category} · {insights[0].readTime}</small><h2>{insights[0].title}</h2><p>{insights[0].excerpt}</p><Link className="button button-dark" href={`/insights/${insights[0].slug}`}>Read the insight <span>↗</span></Link></Reveal></div></section>
    <section className="insights-library section-soft section-space"><div className="shell"><Reveal className="editorial-heading"><div><span className="eyebrow">Knowledge library</span><p className="section-index">Latest thinking</p></div><h2>Operational ideas written for practical use.</h2></Reveal><div className="insight-library-list">{insights.map((insight, index) => <Reveal key={insight.slug} delay={index * 60}><Link href={`/insights/${insight.slug}`}><span>0{index + 1}</span><div><small>{insight.category} · {insight.date}</small><h3>{insight.title}</h3><p>{insight.excerpt}</p></div><strong>{insight.readTime}</strong><i>↗</i></Link></Reveal>)}</div></div></section>
    <CallToAction title="Turn the idea into an operating plan." />
  </main>;
}
