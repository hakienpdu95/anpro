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

    viteStaticCopy({
      targets: [{ src: 'resources/images/*', dest: 'images' }]
    })
  ],

  resolve: {
    alias: {
      '@scripts': '/resources/js',
      '@styles': '/resources/css',
      '@fonts': '/resources/fonts',
      '@images': '/resources/images',
    },
  },

  build: {
    rollupOptions: {
      output: {
        manualChunks(id) {
          if (id.includes('alpinejs')) return 'vendor-alpine';
          if (id.includes('@splidejs/splide')) return 'vendor-splide';
          if (id.includes('node_modules')) return 'vendor';
        }
      }
    },

    minify: 'esbuild',
    sourcemap: false,
    assetsInlineLimit: 4096,
    reportCompressedSize: true,
  },

  define: {
    'process.env.NODE_ENV': '"production"'
  }
});