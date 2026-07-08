/**
 * Build dos assets (CSS/JS) com esbuild.
 * Node.js é usado SOMENTE aqui, em desenvolvimento.
 * Os arquivos finais são versionados em public/assets/ e
 * publicados prontos na hospedagem (sem Node em produção).
 */
import * as esbuild from 'esbuild';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const root = path.dirname(path.dirname(fileURLToPath(import.meta.url)));
const watch = process.argv.includes('--watch');

const options = {
  entryPoints: [
    { in: path.join(root, 'resources/css/main.css'), out: 'css/main' },
    { in: path.join(root, 'resources/css/admin.css'), out: 'css/admin' },
    { in: path.join(root, 'resources/js/main.js'), out: 'js/main' },
    { in: path.join(root, 'resources/js/admin.js'), out: 'js/admin' },
  ],
  outdir: path.join(root, 'public/assets'),
  bundle: true,
  minify: true,
  target: ['es2019', 'chrome90', 'firefox90', 'safari14'],
  loader: { '.woff2': 'copy' },
  external: ['../fonts/*'],
  legalComments: 'none',
  logLevel: 'info',
};

if (watch) {
  const ctx = await esbuild.context(options);
  await ctx.watch();
  console.log('Observando alterações…');
} else {
  await esbuild.build(options);
  console.log('Build concluído: public/assets/');
}
