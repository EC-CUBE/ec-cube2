import * as esbuild from 'esbuild';
import { writeFileSync } from 'fs';
import { join } from 'path';
import { tmpdir } from 'os';

const isWatch = process.argv.includes('--watch');

// jQuery UMD モジュール(jquery-ui, jquery-colorbox等)がグローバルの jQuery/$ を
// 参照するため、esbuild の inject で同一インスタンスを提供する
const jqueryShimPath = join(tmpdir(), 'esbuild-jquery-shim.js');
writeFileSync(jqueryShimPath, 'export { default as jQuery, default as $ } from "jquery";\n');

/** @type {import('esbuild').BuildOptions} */
const buildOptions = {
  entryPoints: ['data/eccube.js'],
  bundle: true,
  inject: [jqueryShimPath],
  nodePaths: ['node_modules'],
  outdir: 'html/js',
  entryNames: '[name]',
  assetNames: '[name]-[hash]',
  sourcemap: true,
  minify: true,
  target: ['es2020'],
  loader: {
    '.png': 'file',
    '.jpg': 'file',
    '.gif': 'file',
    '.svg': 'file',
    '.eot': 'file',
    '.woff': 'file',
    '.ttf': 'file',
  },
};

if (isWatch) {
  const ctx = await esbuild.context(buildOptions);
  await ctx.watch();
  console.log('Watching for changes...');
} else {
  await esbuild.build(buildOptions);
}
