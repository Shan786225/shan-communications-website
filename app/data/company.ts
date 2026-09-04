export const companyContact = {
  email: 'support@shancommunication.com',
  businessInquiryEmail: 'ceo@shancommunication.com',
  careersEmail: 'hr@shancommunication.com',
  directEmail: 'alishankhan77751@gmail.com',
  usPhone: '+1 701 204 4063',
  usPhoneHref: 'tel:+17012044063',
  pakistanOfficePhone: '+92 51 844 2808',
  pakistanOfficePhoneHref: 'tel:+92518442808',
  pakistanWhatsApp: '+92 310 093 2808',
  pakistanWhatsAppHref: 'https://wa.me/923100932808',
  usWhatsAppHref: 'https://wa.me/17012044063',
  office: '62 Murree Rd, A Block, Satellite Town, Rawalpindi, Punjab 46300',
  mapHref: 'https://maps.google.com/?q=62+Murree+Rd+A+Block+Satellite+Town+Rawalpindi+Punjab+46300',
} as const;

export const companySocialLinks = [
  { label: 'LinkedIn', icon: 'linkedin', href: 'https://www.linkedin.com/company/shan-communications/' },
  { label: 'Facebook', icon: 'facebook', href: 'https://www.facebook.com/Shancommunications/' },
  { label: 'Instagram', icon: 'instagram', href: 'https://www.instagram.com/shancommunications/' },
] as const;

export const shanKhanSocialLinks = [
  { label: 'LinkedIn', icon: 'linkedin', href: 'https://www.linkedin.com/in/shan-khan-682a7b249' },
  { label: 'Facebook', icon: 'facebook', href: 'https://www.facebook.com/share/1EfpJFsu79/?mibextid=wwXIfr' },
  { label: 'Instagram', icon: 'instagram', href: 'https://www.instagram.com/shan786225/' },
  { label: 'US WhatsApp', icon: 'whatsapp', badge: 'US', href: companyContact.usWhatsAppHref },
  { label: 'Pakistan WhatsApp', icon: 'whatsapp', badge: 'PK', href: companyContact.pakistanWhatsAppHref },
  { label: 'Business email', icon: 'business-email', href: `mailto:${companyContact.email}` },
  { label: 'Personal email', icon: 'personal-email', href: `mailto:${companyContact.directEmail}` },
] as const;

export const impactStats = [
  { value: 50, suffix: '+', label: 'Success stories', detail: 'Completed client and campaign outcomes' },
  { value: 205, suffix: 'K+', label: 'Telecom sales', detail: 'Connectivity and home-service programs' },
  { value: 40, suffix: 'K+', label: 'Insurance sales', detail: 'Insurance acquisition and transfer programs' },
  { value: 25, suffix: 'K+', label: 'Healthcare sales', detail: 'Healthcare, enrollment and patient programs' },
  { value: 300, suffix: '+', label: 'Total experiences', detail: 'Projects, campaigns and operating engagements' },
] as const;

export const experiencePrograms = [
  {
    number: '01',
    title: 'Healthcare, insurance & enrollment',
    description: 'Operational experience across patient outreach, enrollment, eligibility and lead-transfer workflows.',
    programs: ['Remote Patient Monitoring (RPM)', 'CCM enrollment support', 'Medicare and ACA', 'ACP and Lifeline', 'Final Expense'],
  },
  {
    number: '02',
    title: 'Telecom, connectivity & home services',
    description: 'Campaign delivery spanning consumer connectivity, appointment setting and home-service acquisition.',
    programs: ['Broadband and home internet', 'Telecom and home services', 'Home improvement', 'Solar appointments and leads', 'Mortgage refinance'],
  },
  {
    number: '03',
    title: 'Claims & legal-intake campaigns',
    description: 'Structured qualification and live-transfer experience across sensitive, criteria-led consumer campaigns.',
    programs: ['Motor vehicle accident claims', 'Camp Lejeune', 'Roundup', 'AFFF', 'Mass-tort intake support'],
  },
  {
    number: '04',
    title: 'Sales, data & contact-center operations',
    description: 'The operating systems behind campaigns: prepared teams, list management, dialer support, QA and reporting.',
    programs: ['Lead generation', 'Live transfers', 'Appointment setting', 'Hosted dialer operations', 'Quality assurance and reporting'],
  },
] as const;
