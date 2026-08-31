'use client';

import { useEffect, useRef, type ReactNode } from 'react';
import { usePathname } from 'next/navigation';

export function Reveal({
  children,
  className = '',
  delay = 0,
}: {
  children: ReactNode;
  className?: string;
  delay?: number;
}) {
  const pathname = usePathname();
  const ref = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const node = ref.current;
    if (!node) return;

    node.classList.remove('is-visible');

    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          node.classList.add('is-visible');
          observer.unobserve(node);
        }
      },
      { threshold: 0.08, rootMargin: '0px 0px -6% 0px' },
    );

    const revealIfVisible = () => {
      const rect = node.getBoundingClientRect();
      if (rect.top < window.innerHeight * 0.96 && rect.bottom > 0) {
        node.classList.add('is-visible');
        observer.unobserve(node);
      }
    };
    const frame = requestAnimationFrame(() => {
      observer.observe(node);
      revealIfVisible();
    });
    const settle = window.setTimeout(revealIfVisible, 320);
    window.addEventListener('scroll', revealIfVisible, { passive: true });
    window.addEventListener('resize', revealIfVisible);
    return () => {
      cancelAnimationFrame(frame);
      window.clearTimeout(settle);
      window.removeEventListener('scroll', revealIfVisible);
      window.removeEventListener('resize', revealIfVisible);
      observer.disconnect();
    };
  }, [pathname]);

  return (
    <div
      ref={ref}
      className={`reveal ${className}`.trim()}
      style={{ '--reveal-delay': `${delay}ms` } as React.CSSProperties}
    >
      {children}
    </div>
  );
}
