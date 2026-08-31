import sharp from '../node_modules/.pnpm/sharp@0.34.5/node_modules/sharp/lib/index.js';
import { fileURLToPath } from 'node:url';

const source = '/tmp/shan-logo-header-2.png';
const output = fileURLToPath(new URL('../public/assets/shan-logo-clean.png', import.meta.url));
// Remove only the small legacy tagline. The geometric mark and the complete
// SHAN COMMUNICATIONS wordmark remain pixel-for-pixel from the original file.
await sharp(source)
  .composite([
    {
      input: {
        create: {
          width: 283,
          height: 67,
          channels: 4,
          background: { r: 0, g: 0, b: 0, alpha: 1 },
        },
      },
      left: 286,
      top: 294,
      blend: 'dest-out',
    },
  ])
  .png({ compressionLevel: 9 })
  .toFile(output);
