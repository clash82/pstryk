require('bootstrap/dist/css/bootstrap.css');
require('swiper/swiper-bundle.css');
require('fontsource-open-sans/latin-ext.css');
require('../scss/app.scss');

const swiper = require('swiper/swiper-bundle');
global.swiper = swiper;

const howler = require('howler/dist/howler');
global.howler = howler;

console.log('Hello Webpack Encore! Edit me in assets/rpfoto/js/app.js');
