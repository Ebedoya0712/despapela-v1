// resources/js/app.js

// Esta línea carga el archivo bootstrap.js de Laravel (para Axios, etc.)
// Es importante no confundirlo con el framework Bootstrap. Déjala como está.
import './bootstrap';

// 1. Importar el JavaScript del framework Bootstrap
// Esto es necesario para que funcionen componentes como menús desplegables, modales, etc.
import * as bootstrap from 'bootstrap';

// 2. Importar SweetAlert2
// Esto activa la librería de alertas.
import Swal from 'sweetalert2';

// 3. (Opcional) Hacer SweetAlert2 accesible globalmente
// Esto es útil para que puedas probar las alertas desde la consola del navegador.
window.Swal = Swal;