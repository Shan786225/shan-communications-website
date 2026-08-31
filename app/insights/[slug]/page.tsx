import type { Metadata } from 'next';
import Link from 'next/link';
import { notFound } from 'next/navigation';
import { CallToAction } from '../../components/CallToAction';
import { getInsight, insightBodies, insights } from '../../data/site';

export function generateStaticParams() { return insights.map((insight) => ({ slug: insight.slug })); }
export async function generateMetadata({ params }: { params: Promise<{ slug: string }> }): Promise<Metadata> { const { slug } = await params; const insight = getInsight(slug); return insight ? { title: insight.title, description: insight.excerpt } : {}; }

export default async function InsightPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const insight = getInsight(slug);
  const body = insightBodies[slug];
  if (!insight || !body) notFound();
  return <main>
    <article>
      <header className="article-hero"><div className="shell"><div className="breadcrumbs breadcrumbs-dark"><Link href="/">Home</Link><span>/</span><Link href="/insights">Insights</Link><span>/</span><b>{insight.category}</b></div><span className="eyebrow">{insight.category}</span><h1>{insight.title}</h1><div className="article-meta"><span>{insight.date}</span><span>{insight.readTime}</span><span>Shan Communications</span></div></div></header>
      <div className="shell article-layout"><aside><span>In this insight</span>{body.sections.map((section, index) => <a href={`#section-${index + 1}`} key={section.title}>0{index + 1} · {section.title}</a>)}<Link href="/contact#contact-form" scroll={false}>Discuss your operation ↗</Link></aside><div className="article-body"><p className="article-intro">{body.intro}</p>{body.sections.map((section, index) => <section id={`section-${index + 1}`} key={section.title}><span>0{index + 1}</span><h2>{section.title}</h2>{section.paragraphs.map((paragraph) => <p key={paragraph}>{paragraph}</p>)}</section>)}<div className="article-note"><strong>Practical next step</strong><p>Choose one recurring workflow and document its owner, completion signal, exception route and review measure. That single exercise often reveals where the operation needs attention first.</p></div></div></div>
    </article>
    <CallToAction title="Need help turning this thinking into an operating model?" />
  </main>;
}
