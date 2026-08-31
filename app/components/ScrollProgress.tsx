'use client';

import { useEffect, useRef } from 'react';
import { usePathname } from 'next/navigation';

export function ScrollProgress() {
  const pathname = usePathname();
  const barRef = useRef<HTMLSpanElement>(null);

  useEffect(() => {
    let frame = 0;
    const update = () => {
      const bar = barRef.current;
      if (!bar) return;
      const available = document.documentElement.scrollHeight - window.innerHeight;
      const progress = available > 0 ? Math.min(window.scrollY / available, 1) : 0;
      bar.style.transform = `scaleX(${progress})`;
    };
    const requestUpdate = () => {
      cancelAnimationFrame(frame);
      frame = requestAnimationFrame(update);
    };

    requestUpdate();
    window.addEventListener('scroll', requestUpdate, { passive: true });
    window.addEventListener('resize', requestUpdate);
    return () => {
      cancelAnimationFrame(frame);
      window.removeEventListener('scroll', requestUpdate);
      window.removeEventListener('resize', requestUpdate);
    };
  }, [pathname]);

  return <div className="site-progress" aria-hidden="true"><span ref={barRef} /></div>;
}
