require('./bootstrap');
window.$ = window.jQuery = require('jquery');
require( '../../node_modules/datatables.net/js/jquery.dataTables.js' );
require( '../../node_modules/datatables.net-bs4/js/dataTables.bootstrap4.js' );




import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
