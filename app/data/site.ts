export type Service = {
  slug: string;
  number: string;
  eyebrow: string;
  title: string;
  shortTitle: string;
  summary: string;
  intro: string;
  image: string;
  capabilities: string[];
  outcomes: string[];
  process: { title: string; text: string }[];
  fit: string[];
};

export const services: Service[] = [
  {
    slug: 'customer-experience',
    number: '01',
    eyebrow: 'Customer operations',
    title: 'Customer Experience & Contact Center',
    shortTitle: 'Customer Experience',
    summary: 'Prepared teams supporting the conversations, queues and service moments that shape customer trust.',
    intro: 'We build customer-support operations around your service standards, escalation rules and quality expectations. The result is a team that understands the context behind every interaction—not only the script.',
    image: '/assets/service-customer-support.jpg',
    capabilities: ['Inbound customer support', 'Outbound customer engagement', 'Appointment setting', 'Sales support', 'Retention workflows', 'Quality monitoring and coaching'],
    outcomes: ['Clearer ownership across customer queues', 'More consistent service delivery', 'Actionable quality findings', 'A scalable support rhythm'],
    process: [
      { title: 'Map the journey', text: 'Define channels, customer intents, handoffs and escalation points.' },
      { title: 'Prepare the team', text: 'Build knowledge, scripts, scenarios and quality standards around the operation.' },
      { title: 'Launch with control', text: 'Start through a measured transition with visible daily reporting.' },
      { title: 'Improve continuously', text: 'Turn recurring customer signals into coaching and workflow decisions.' },
    ],
    fit: ['Growing support demand', 'New campaign launches', 'Dedicated customer-care teams', 'Overflow and extended-hours coverage'],
  },
  {
    slug: 'business-process-outsourcing',
    number: '02',
    eyebrow: 'Managed operations',
    title: 'Business Process Outsourcing',
    shortTitle: 'Business Process Outsourcing',
    summary: 'Recurring business workflows managed through defined roles, visible queues and accountable handoffs.',
    intro: 'Shan Communications brings structure to high-volume operational work. We document the workflow, assign clear ownership and establish a review rhythm so partners can scale without losing visibility.',
    image: '/assets/service-bpo-operations.jpg',
    capabilities: ['Back-office administration', 'Data entry and verification', 'Document and queue processing', 'Workflow coordination', 'Operational reporting', 'Dedicated team models'],
    outcomes: ['Less operational friction', 'Visible workload and accountability', 'Consistent process execution', 'More capacity for internal teams'],
    process: [
      { title: 'Discover', text: 'Understand the current process, exceptions, volumes and dependencies.' },
      { title: 'Document', text: 'Create practical playbooks, ownership rules and control points.' },
      { title: 'Operate', text: 'Run the workflow through prepared teams and defined service routines.' },
      { title: 'Optimize', text: 'Review output, quality and exceptions to strengthen the next cycle.' },
    ],
    fit: ['High-volume recurring work', 'Process bottlenecks', 'Distributed operations', 'New managed-service programs'],
  },
  {
    slug: 'healthcare-revenue-cycle',
    number: '03',
    eyebrow: 'Healthcare operations',
    title: 'Medical Billing & Revenue Cycle Support',
    shortTitle: 'Medical Billing & RCM',
    summary: 'Non-clinical operational support across billing, revenue-cycle and patient-access workflows.',
    intro: 'We support defined healthcare administrative workflows while keeping clinical decisions and patient care with the provider organization. Each engagement is shaped around the client’s systems, access model and operating requirements.',
    image: '/assets/healthcare-operations.webp',
    capabilities: ['Claims and billing operations', 'Accounts-receivable follow-up', 'Denial workflow support', 'Eligibility verification', 'Prior-authorization support', 'Provider credentialing coordination'],
    outcomes: ['Clearer revenue-cycle ownership', 'More organized work queues', 'Consistent administrative follow-through', 'Defined non-clinical boundaries'],
    process: [
      { title: 'Define scope', text: 'Separate administrative responsibilities from provider-owned clinical decisions.' },
      { title: 'Configure workflow', text: 'Map systems, permissions, queues, documentation and escalation paths.' },
      { title: 'Prepare operations', text: 'Train the assigned team against client-approved procedures and controls.' },
      { title: 'Review performance', text: 'Use queue reporting, exception review and quality findings to guide action.' },
    ],
    fit: ['Provider groups', 'Billing organizations', 'Healthcare operations partners', 'Programs requiring dedicated administrative support'],
  },
  {
    slug: 'rpm-ccm-operations',
    number: '04',
    eyebrow: 'Connected care support',
    title: 'RPM & CCM Enrollment Operations',
    shortTitle: 'RPM & CCM Operations',
    summary: 'Structured, non-clinical enrollment and coordination support for connected-care programs.',
    intro: 'Our role is operational: outreach, enrollment support, documentation coordination, workflow follow-through and reporting. Clinical eligibility, care decisions and medical guidance remain with the provider and its licensed clinical team.',
    image: '/assets/service-rpm-ccm.jpg',
    capabilities: ['Patient outreach support', 'Enrollment workflow coordination', 'Consent and documentation follow-up', 'Device-logistics coordination', 'Program queue management', 'Operational reporting'],
    outcomes: ['More visible enrollment handoffs', 'Consistent outreach follow-through', 'Better organized program queues', 'Clear provider and partner responsibilities'],
    process: [
      { title: 'Align responsibilities', text: 'Document what the provider, technology partner and operations team each own.' },
      { title: 'Build the journey', text: 'Map outreach, consent, enrollment, escalation and completion stages.' },
      { title: 'Operate the queue', text: 'Use defined statuses and follow-up rules to keep work visible.' },
      { title: 'Report and refine', text: 'Review conversion points, exceptions and handoff delays with program owners.' },
    ],
    fit: ['Provider-led RPM programs', 'CCM enrollment initiatives', 'Connected-care platforms', 'White-label operational partnerships'],
  },
  {
    slug: 'digital-growth',
    number: '05',
    eyebrow: 'Growth operations',
    title: 'Digital Marketing & Lead Operations',
    shortTitle: 'Digital Growth',
    summary: 'Campaign execution connected to the qualification, follow-up and operational discipline required for growth.',
    intro: 'We connect digital activity with real operational follow-through. Landing experiences, campaign support, lead workflows and reporting are organized around an agreed audience and business objective.',
    image: '/assets/service-digital-growth.jpg',
    capabilities: ['Campaign operations', 'Landing-page support', 'Lead generation workflows', 'Content coordination', 'Search and social support', 'Lead qualification and follow-up'],
    outcomes: ['A clearer path from campaign to conversation', 'Better organized lead handling', 'Aligned marketing and operations', 'More useful performance reporting'],
    process: [
      { title: 'Clarify the goal', text: 'Define the audience, offer, conversion event and operating constraints.' },
      { title: 'Build the path', text: 'Connect creative, landing experience, capture and follow-up workflows.' },
      { title: 'Run the campaign', text: 'Coordinate activity while monitoring quality and downstream response.' },
      { title: 'Learn and adjust', text: 'Use conversion and follow-up findings to guide the next iteration.' },
    ],
    fit: ['B2B growth programs', 'Lead-generation campaigns', 'New service launches', 'Teams needing marketing-to-sales coordination'],
  },
  {
    slug: 'data-technology-operations',
    number: '06',
    eyebrow: 'Operational enablement',
    title: 'Data, Dialer & Technology Operations',
    shortTitle: 'Data & Technology Operations',
    summary: 'Practical technology and data support that keeps operational teams connected, organized and productive.',
    intro: 'Technology matters when it improves how work moves. We support data preparation, hosted-dialer operations, list and workflow administration, and reporting coordination around defined business processes.',
    image: '/assets/service-data-technology.jpg',
    capabilities: ['Hosted dialer support', 'Campaign and list administration', 'Data preparation', 'Workflow configuration', 'Access and role coordination', 'Reporting support'],
    outcomes: ['Fewer operational handoff gaps', 'Cleaner workflow inputs', 'More consistent campaign setup', 'Technology aligned to the process'],
    process: [
      { title: 'Assess the environment', text: 'Review the process, platforms, access model and operational dependencies.' },
      { title: 'Configure', text: 'Organize roles, inputs, queues and reporting requirements.' },
      { title: 'Support', text: 'Maintain routine administration and issue escalation around the operation.' },
      { title: 'Strengthen', text: 'Use recurring issues and workflow findings to improve reliability.' },
    ],
    fit: ['Contact-center operations', 'Data-intensive workflows', 'Campaign teams', 'Organizations consolidating operating tools'],
  },
];

export const insights = [
  { slug: 'building-an-accountable-outsourcing-model', category: 'Operating model', title: 'Building an accountable outsourcing model before adding headcount', excerpt: 'The roles, controls and communication rhythm that should exist before a team begins delivery.', date: 'August 2026', readTime: '6 min read' },
  { slug: 'medical-billing-workflow-ownership', category: 'Healthcare operations', title: 'Medical billing workflow ownership: where clarity changes performance', excerpt: 'A practical view of queues, exceptions and handoffs across a revenue-cycle operation.', date: 'August 2026', readTime: '7 min read' },
  { slug: 'rpm-ccm-enrollment-handoffs', category: 'Connected care', title: 'RPM and CCM enrollment: the operational handoffs that matter', excerpt: 'How providers and operational partners can make responsibilities visible from outreach to completion.', date: 'August 2026', readTime: '5 min read' },
];

export const insightBodies: Record<string, { intro: string; sections: { title: string; paragraphs: string[] }[] }> = {
  'building-an-accountable-outsourcing-model': {
    intro: 'Outsourcing succeeds when the operating model is clear before the first person is assigned. Capacity matters, but responsibility, visibility and communication determine whether that capacity becomes reliable delivery.',
    sections: [
      { title: 'Begin with ownership, not staffing', paragraphs: ['List the outcomes the operation must protect, then assign a named owner to each queue, decision and escalation. A role description is not enough if two teams can still assume the other one is responsible.', 'The strongest transition plans show who performs the work, who approves exceptions, who supplies information and who makes the final decision.'] },
      { title: 'Make the invisible workflow visible', paragraphs: ['Document inputs, status changes, handoffs and completion criteria. This does not require an enormous manual. It requires a usable map that the people doing the work can follow and improve.', 'Visibility also means agreeing on what will be reported daily, weekly and monthly before launch.'] },
      { title: 'Build a management rhythm', paragraphs: ['Operational reviews should separate immediate queue issues from recurring process problems. One keeps delivery moving; the other improves the system.', 'A clear rhythm gives partners a reliable place to make decisions instead of managing through scattered messages.'] },
    ],
  },
  'medical-billing-workflow-ownership': {
    intro: 'Revenue-cycle work crosses multiple systems, teams and decision boundaries. Small ownership gaps can become delayed claims, unresolved denials or repeated follow-up. The answer begins with a clearer operating map.',
    sections: [
      { title: 'Organize around work queues', paragraphs: ['Each queue should have entry criteria, a responsible role, an expected next action and a defined escalation path. This makes backlog and exceptions easier to see.', 'Queue ownership should also reflect the difference between administrative work and decisions that remain with the provider.'] },
      { title: 'Treat exceptions as a separate workflow', paragraphs: ['A process designed only for the standard case will fail under real operating conditions. Missing documentation, eligibility questions and payer responses need their own statuses and follow-up rules.', 'Recurring exceptions should be reviewed as patterns, not handled forever as isolated events.'] },
      { title: 'Use reporting to prompt decisions', paragraphs: ['Useful reporting explains what changed, where work is stuck and what requires a decision. Volume alone rarely provides that clarity.', 'A short, consistent review rhythm helps the organization act while the information is still operationally relevant.'] },
    ],
  },
  'rpm-ccm-enrollment-handoffs': {
    intro: 'Connected-care enrollment involves outreach, education, consent, eligibility, documentation and program activation. The experience becomes stronger when every handoff has an owner and a clear completion signal.',
    sections: [
      { title: 'Separate clinical and operational responsibilities', paragraphs: ['Providers retain clinical eligibility decisions, medical guidance and care delivery. Operational partners can support approved outreach, documentation coordination and queue follow-through.', 'Documenting this boundary protects clarity for staff and patients.'] },
      { title: 'Design the patient journey', paragraphs: ['Each stage should define the patient message, required documentation, status options and escalation rule. The goal is a consistent experience without forcing every situation into one script.', 'The journey should also account for unreachable patients, questions requiring a clinician and incomplete enrollment steps.'] },
      { title: 'Measure handoffs, not only totals', paragraphs: ['Enrollment totals matter, but stage-level visibility shows where the process is slowing down. Track movement between stages, aged work and the reasons cases cannot progress.', 'Those findings create a practical improvement agenda for the provider and operations team.'] },
    ],
  },
};

export function getService(slug: string) { return services.find((service) => service.slug === slug); }
export function getInsight(slug: string) { return insights.find((insight) => insight.slug === slug); }
