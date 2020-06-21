/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
require('bootstrap/dist/css/bootstrap.css');
require('flatpickr/dist/themes/material_green.css');
require('magnific-popup/dist/magnific-popup.css');
require('@fortawesome/fontawesome-free/css/all.css');
require('fontsource-open-sans/latin-ext.css');
require('../css/app.css');

const $ = require('jquery');
global.$ = global.jQuery = $;

const Cookies = require('js-cookie');
global.Cookies = Cookies;

require('bootstrap');
require('jqueryui');
require('symfony-collection');
require('magnific-popup');

const flatpickr = require('flatpickr');
const flatpickrPL = require('flatpickr/dist/l10n/pl.js').default.pl;
flatpickr.localize(flatpickrPL);

console.log('Hello Webpack Encore! Edit me in assets/admin/js/app.js');
