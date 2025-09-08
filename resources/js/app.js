// Importación del bootstrap.js de Laravel
import './bootstrap';

// Importar Bootstrap 5
import * as bootstrap from 'bootstrap';

// Importar SweetAlert2
import Swal from 'sweetalert2';
window.Swal = Swal;

// --- DATA TABLES ---

// 1. Importar jQuery
import $ from 'jquery';
window.$ = window.jQuery = $;

// 2. Importar DataTables (core e integración con Bootstrap 5)
import DataTable from 'datatables.net-bs5';
window.DataTable = DataTable;

// 3. Importar la extensión de Botones y sus módulos
import 'datatables.net-buttons-bs5';
import 'datatables.net-buttons/js/buttons.colVis.js';
import 'datatables.net-buttons/js/buttons.html5.js';
import 'datatables.net-buttons/js/buttons.print.js';

// 4. Importar librerías para exportar (en el orden correcto)
import JSZip from 'jszip';
import pdfMake from 'pdfmake/build/pdfmake';
import 'pdfmake/build/vfs_fonts.js';

// 5. Configurar las librerías para que DataTables las encuentre
window.JSZip = JSZip;
// La siguiente línea ya no es necesaria y causaba el error. ¡Elimínala!
// pdfMake.vfs = pdfFonts.pdfMake.vfs;