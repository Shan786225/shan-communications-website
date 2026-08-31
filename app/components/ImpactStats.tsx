'use client';

import { useEffect, useRef, useState } from 'react';
import { impactStats } from '../data/company';

function AnimatedMetric({ value, suffix }: { value: number; suffix: string }) {
  const [display, setDisplay] = useState(0);
  const ref = useRef<HTMLSpanElement>(null);

  useEffect(() => {
    const node = ref.current;
    if (!node) return;
    let frame = 0;
    const observer = new IntersectionObserver(([entry]) => {
      if (!entry.isIntersecting) return;
      const start = performance.now();
      const duration = 1100;
      const update = (time: number) => {
        const progress = Math.min((time - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        setDisplay(Math.round(value * eased));
        if (progress < 1) frame = requestAnimationFrame(update);
      };
      frame = requestAnimationFrame(update);
      observer.disconnect();
    }, { threshold: .45 });
    observer.observe(node);
    return () => { observer.disconnect(); cancelAnimationFrame(frame); };
  }, [value]);

  if (suffix === '/7') return <span>24<em>/7</em></span>;
  return <span ref={ref}>{display}<em>{suffix}</em></span>;
}

export function ImpactStats() {
  return (
    <section className="impact-stats" aria-label="Shan Communications experience at a glance">
      <div className="shell impact-stats-grid">
        {impactStats.map((stat) => (
          <article key={stat.label}>
            <AnimatedMetric value={stat.value} suffix={stat.suffix} />
            <strong>{stat.label}</strong>
            <small>{stat.detail}</small>
          </article>
        ))}
      </div>
    </section>
  );
}
