import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import { wordpressPlugin, wordpressThemeJson } from '@roots/vite-plugin';
import { viteStaticCopy } from 'vite-plugin-static-copy';

export default defineConfig({
  base: '/wp-content/themes/anpro/public/build/',
  plugins: [
    tailwindcss(),
    laravel({
      input: [
        'resources/css/app.css',     
        'resources/css/main.scss',   
        'resources/js/app.js',
        'resources/css/editor.css',
        'resources/js/editor.js',
      ],
      refresh: true,
    }),
    wordpressPlugin(),
    wordpressThemeJson({
      disableTailwindColors: false,
      disableTailwindFonts: false,
      disableTailwindFontSizes: false,
    }),
    viteStaticCopy({ targets: [{ src: 'resources/images/*', dest: 'images' }] })
  ],

  css: {
    preprocessorOptions: {
      scss: {
        api: 'modern',
        silenceDeprecations: ['color-functions', 'global-builtin', 'import'],
      }
    }
  },

  build: {
    rollupOptions: {
      output: {
        assetFileNames: (assetInfo) => {
          if (assetInfo.name?.endsWith('.css') && assetInfo.originalName?.includes('main.scss')) {
            return 'assets/main.[hash].[ext]';
          }
          if (assetInfo.name?.endsWith('.css')) {
            return 'assets/[name].[hash].[ext]';
          }
          return 'assets/[name].[hash].[ext]';
        },
        entryFileNames: 'assets/[name].[hash].js',
        chunkFileNames: 'assets/[name].[hash].js',
      }
    },
    minify: 'esbuild',
    sourcemap: false,
    assetsInlineLimit: 4096,
    reportCompressedSize: true,
  }
});