'use client';

import { useEffect } from 'react';
import { usePathname } from 'next/navigation';

const motionSelectors = [
  '.article-layout > aside',
  '.article-body > *',
  '.legal-content > *',
  '.faq-list details',
  '.responsibility-table > div',
  '.contact-method',
  '.contact-whatsapp-row',
  '.contact-social-row > *',
  '.impact-stats article',
  '.capability-bar-inner > *',
  '.band-cta-inner > *',
  '.footer-lead > *',
  '.footer-grid > *',
  '.footer-social > *',
].join(',');

export function RouteEffects() {
  const pathname = usePathname();

  useEffect(() => {
    const moveToId = (id: string) => {
      const target = document.getElementById(id);
      if (target) target.scrollIntoView({ block: 'start', behavior: 'instant' as ScrollBehavior });
    };
    const move = () => {
      const hash = window.location.hash.slice(1);
      if (hash) {
        moveToId(hash);
      } else {
        window.scrollTo({ top: 0, left: 0, behavior: 'auto' });
      }
    };
    const followSamePageAnchor = (event: MouseEvent) => {
      const link = (event.target as Element | null)?.closest('a[href*="#"]') as HTMLAnchorElement | null;
      if (!link) return;
      const destination = new URL(link.href, window.location.href);
      if (destination.pathname !== window.location.pathname || !destination.hash) return;
      const id = destination.hash.slice(1);
      requestAnimationFrame(() => moveToId(id));
      window.setTimeout(() => moveToId(id), 120);
    };

    const motionBlocks = Array.from(document.querySelectorAll<HTMLElement>(motionSelectors));
    motionBlocks.forEach((node, index) => {
      node.classList.add('motion-block');
      node.style.setProperty('--motion-order', String(index % 6));
    });
    const motionObserver = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          entry.target.classList.add('is-visible');
          motionObserver.unobserve(entry.target);
        });
      },
      { threshold: 0.08, rootMargin: '0px 0px -5% 0px' },
    );
    const revealMotionInView = () => {
      motionBlocks.forEach((node) => {
        const rect = node.getBoundingClientRect();
        if (rect.top < window.innerHeight * 0.96 && rect.bottom > 0) {
          node.classList.add('is-visible');
          motionObserver.unobserve(node);
        }
      });
    };
    const motionFrame = requestAnimationFrame(() => {
      motionBlocks.forEach((node) => motionObserver.observe(node));
      revealMotionInView();
    });
    const motionSettle = window.setTimeout(revealMotionInView, 340);
    window.addEventListener('scroll', revealMotionInView, { passive: true });
    window.addEventListener('resize', revealMotionInView);

    const frame = requestAnimationFrame(() => requestAnimationFrame(move));
    const settle = window.setTimeout(move, 240);
    window.addEventListener('hashchange', move);
    document.addEventListener('click', followSamePageAnchor);
    return () => {
      cancelAnimationFrame(motionFrame);
      cancelAnimationFrame(frame);
      window.clearTimeout(motionSettle);
      window.clearTimeout(settle);
      motionObserver.disconnect();
      window.removeEventListener('scroll', revealMotionInView);
      window.removeEventListener('resize', revealMotionInView);
      window.removeEventListener('hashchange', move);
      document.removeEventListener('click', followSamePageAnchor);
    };
  }, [pathname]);

  return null;
}
