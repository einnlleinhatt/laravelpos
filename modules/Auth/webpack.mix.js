const mix = require('laravel-mix');
require('laravel-mix-merge-manifest');
const tailwindcss = require('tailwindcss');
const purgecss = require('@fullhuman/postcss-purgecss');

mix.setPublicPath('../../public').mergeManifest();

mix
  .js(__dirname + '/Resources/js/app.js', 'js/auth.js')
  .postCss(__dirname + '/Resources/css/app.css', 'css/auth.css')
  .options({
    postCss: [
      tailwindcss('tailwind.config.js'),
      ...(mix.inProduction()
        ? [
            purgecss({
              content: [__dirname + '/Resources/views/**/*.blade.php'],
              defaultExtractor: content => content.match(/[\w-/:.]+(?<!:)/g) || [],
              whitelistPatternsChildren: [],
            }),
          ]
        : []),
    ],
  });
