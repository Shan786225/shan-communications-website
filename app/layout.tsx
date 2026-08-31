import type { Metadata } from 'next';
import { Geist, Geist_Mono } from 'next/font/google';
import { SiteFooter } from './components/SiteFooter';
import { SiteHeader } from './components/SiteHeader';
import { RouteEffects } from './components/RouteEffects';
import { companyContact, companySocialLinks } from './data/company';
import './globals.css';

const geistSans = Geist({
  variable: '--font-geist-sans',
  subsets: ['latin'],
});

const geistMono = Geist_Mono({
  variable: '--font-geist-mono',
  subsets: ['latin'],
});

export const metadata: Metadata = {
  metadataBase: new URL('https://shancommunication.com'),
  title: {
    default: 'Shan Communications | Business & Healthcare Operations',
    template: '%s | Shan Communications',
  },
  description:
    'Shan Communications delivers scalable BPO, customer experience, healthcare operations and performance marketing solutions.',
  openGraph: {
    title: 'Shan Communications | Business & Healthcare Operations',
    description: 'Accountable business process, customer experience, healthcare and growth operations.',
    type: 'website',
    images: [{ url: '/og.png', width: 1200, height: 630, alt: 'Shan Communications — Business & Healthcare Operations' }],
  },
  twitter: {
    card: 'summary_large_image',
    title: 'Shan Communications | Business & Healthcare Operations',
    description: 'Accountable business process, customer experience, healthcare and growth operations.',
    images: ['/og.png'],
  },
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en">
      <body
        className={`${geistSans.variable} ${geistMono.variable} antialiased`}
      >
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{
            __html: JSON.stringify({
              '@context': 'https://schema.org',
              '@type': 'Organization',
              name: 'Shan Communications',
              url: 'https://shancommunication.com',
              logo: 'https://shancommunication.com/assets/shan-logo-clean.png',
              email: companyContact.email,
              telephone: companyContact.usPhone,
              address: {
                '@type': 'PostalAddress',
                streetAddress: '62 Murree Rd, A Block, Satellite Town',
                addressLocality: 'Rawalpindi',
                addressRegion: 'Punjab',
                postalCode: '46300',
                addressCountry: 'PK',
              },
              description: 'Business process, customer experience, healthcare and growth operations.',
              sameAs: companySocialLinks.map((link) => link.href),
            }),
          }}
        />
        <SiteHeader />
        <RouteEffects />
        {children}
        <SiteFooter />
      </body>
    </html>
  );
}
