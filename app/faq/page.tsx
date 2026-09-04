import type { Metadata } from 'next';
import Link from 'next/link';
import { CallToAction } from '../components/CallToAction';
import { PageHero } from '../components/PageHero';

export const metadata: Metadata = { title: 'Frequently Asked Questions', description: 'Answers about Shan Communications services, engagement models, healthcare operations and contact process.' };

const questions = [
  ['What does Shan Communications do?', 'Shan Communications designs and supports customer experience, business process, healthcare administrative, connected-care enrollment, digital growth and technology-enabled operations.'],
  ['Is Shan Communications a direct company or a parent company?', 'Both. Shan Communications is presented directly and can operate services itself. It also serves as the parent platform for specialized business lines developed around focused markets or capabilities.'],
  ['Can we start with one workflow?', 'Yes. Many engagements begin with a specific queue, campaign or operating process. The scope can expand after the team, controls and reporting are working reliably.'],
  ['Do you support medical billing and revenue-cycle work?', 'Yes. Shan Communications supports defined non-clinical administrative workflows such as billing operations, A/R follow-up, denial support, eligibility and credentialing coordination. Scope and access are confirmed with each partner.'],
  ['What is your role in RPM and CCM programs?', 'Our role is operational and non-clinical. It can include approved outreach, enrollment support, documentation coordination, queue management and reporting. Clinical eligibility, medical guidance and care decisions remain with the provider.'],
  ['Do you offer white-label operations?', 'Yes. White-label models can be structured around partner branding, approved procedures, defined responsibilities and an agreed governance model.'],
  ['How do you begin a new engagement?', 'The process normally begins with discovery, followed by workflow design, scope confirmation, team preparation and a controlled launch.'],
  ['How will website inquiries be managed?', 'The planned production workflow will record inquiries in the Shan database, mirror them to Google Sheets, notify the appropriate business or HR mailbox and make them available in a secure admin dashboard.'],
];

export default function FAQPage() { return <main><PageHero eyebrow="FAQ" title="Clear answers before the first conversation." description="Frequently asked questions about our company, capabilities and operating approach." image="/assets/service-data-operations.jpg" /><section className="faq-section section-space"><div className="shell faq-grid"><aside><span className="eyebrow">Questions & answers</span><h2>What partners usually want to know.</h2><p>Need an answer about a specific workflow or partnership model?</p><Link className="text-link" href="/contact">Contact our team <span>↗</span></Link></aside><div className="faq-list">{questions.map(([question, answer], index) => <details key={question}><summary><span>0{index + 1}</span><strong>{question}</strong><i>+</i></summary><p>{answer}</p></details>)}</div></div></section><CallToAction title="Still have a question about fit or scope?" /></main>; }
