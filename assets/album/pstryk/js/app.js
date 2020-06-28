require('bootstrap/dist/css/bootstrap.css');
require('baguettebox.js/dist/baguetteBox.css');
require('@fortawesome/fontawesome-free/css/all.css');
require('fontsource-playfair-display/latin-ext.css');
require('fontsource-open-sans/latin-ext.css');
require('../scss/app.scss');

const baguetteBox = require('baguettebox.js');
global.baguetteBox = baguetteBox;

const timeAgo = require('timeago.js/lib/full');
global.timeAgo = timeAgo;

console.log('Hello Webpack Encore! Edit me in assets/pstryk/js/app.js');
