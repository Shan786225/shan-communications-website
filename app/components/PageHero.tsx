import Link from 'next/link';
import { sitePath } from '../lib/sitePath';

type PageHeroProps = {
  eyebrow: string;
  title: string;
  description: string;
  image: string;
  parent?: { label: string; href: string };
};

export function PageHero({ eyebrow, title, description, image, parent }: PageHeroProps) {
  return (
    <section className="page-hero">
      <img src={sitePath(image)} alt="" />
      <div className="page-hero-shade" />
      <div className="page-hero-atmosphere" aria-hidden="true"><i /><i /><i /></div>
      <div className="shell page-hero-content">
        <div className="breadcrumbs"><Link href="/">Home</Link><span>/</span>{parent && <><Link href={parent.href}>{parent.label}</Link><span>/</span></>}<b>{eyebrow}</b></div>
        <span className="eyebrow eyebrow-light">{eyebrow}</span>
        <h1>{title}</h1>
        <p>{description}</p>
      </div>
      <div className="page-hero-index"><span>Shan Communications</span><i /></div>
    </section>
  );
}
