/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
require('../css/fontawesome.css');
require('../css/bootstrap.css');
require('../css/opensans.css');
require('../css/app.css');
require('flatpickr/dist/themes/material_green.css');

const $ = require('jquery');
global.$ = global.jQuery = $;

require('bootstrap');
const flatpickr = require('flatpickr');
const flatpickrPL = require('flatpickr/dist/l10n/pl.js').default.pl;
flatpickr.localize(flatpickrPL);

console.log('Hello Webpack Encore! Edit me in assets/zaplecze/js/app.js');