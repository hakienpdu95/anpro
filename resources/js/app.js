import.meta.glob(['../images/**', '../fonts/**']);

// === ALPINE.JS ===
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// === COMPONENTS AUTO-INIT ===
import './components/init.js';
import './components/main.js';