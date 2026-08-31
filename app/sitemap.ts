import type { MetadataRoute } from 'next';
import { insights, services } from './data/site';

export default function sitemap(): MetadataRoute.Sitemap {
  const base = 'https://shancommunication.com';
  const pages = ['', '/about', '/services', '/experience', '/insights', '/security', '/jobs', '/partnerships', '/faq', '/contact', '/privacy', '/terms'];
  return [
    ...pages.map((path, index) => ({ url: `${base}${path || '/'}`, lastModified: '2026-08-31', changeFrequency: index === 0 ? 'weekly' as const : 'monthly' as const, priority: index === 0 ? 1 : path === '/services' ? .9 : .7 })),
    ...services.map((service) => ({ url: `${base}/services/${service.slug}`, lastModified: '2026-08-31', changeFrequency: 'monthly' as const, priority: .8 })),
    ...insights.map((insight) => ({ url: `${base}/insights/${insight.slug}`, lastModified: '2026-08-31', changeFrequency: 'monthly' as const, priority: .65 })),
  ];
}
