import.meta.glob([
  '../images/**',
  '../fonts/**',
]);

// === ALPINE.JS ===
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// === SPLIDE.JS ===
import Splide from '@splidejs/splide';
window.Splide = Splide;

console.log('✅ Sage + Alpine.js + Splide.js loaded successfully!');