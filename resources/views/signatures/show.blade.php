<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <span>Firmar Documento: <span class="fw-bold">{{ $document->original_filename }}</span></span>
        </div>
    </x-slot>

    <form id="signatureForm" action="{{ route('signatures.store', $uniqueLink->token) }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-lg-9">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body text-center" style="position: relative; overflow: auto; background-color: #525659;">
                        <div id="pdf-container" style="position: relative; display: inline-block;">
                            <canvas id="pdf-viewer"></canvas>
                            <div id="fields-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></div>
                        </div>
                        <div class="mt-2">
                            <button type="button" id="prev-page" class="btn btn-sm btn-outline-light"><i class="fas fa-arrow-left"></i></button>
                            <span class="mx-2 text-white">Página <span id="page-num"></span> de <span id="page-count"></span></span>
                            <button type="button" id="next-page" class="btn btn-sm btn-outline-light"><i class="fas fa-arrow-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Instrucciones</h5>
                        <div class="alert alert-secondary small">
                            Tus datos han sido rellenados. Por favor, haz clic en el recuadro azul punteado en el documento para añadir tu firma.
                        </div>
                        <input type="hidden" name="signature" id="signature-data">
                        <input type="hidden" name="signature_position" id="signature-position">
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary">Firmar y Enviar Documento</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
        <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js"></script>
        
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // --- CONFIGURACIÓN ---
                const pdfUrl = "{{ $documentUrl }}";
                const fieldsToRender = @json($fields ?? []);
                let pdfDoc = null;
                let pageNum = 1;
                const scale = 1.5;

                const canvas = document.getElementById('pdf-viewer');
                const overlay = document.getElementById('fields-overlay');
                const signatureDataInput = document.getElementById('signature-data');
                const signaturePositionInput = document.getElementById('signature-position');
                const form = document.getElementById('signatureForm');

                pdfjsLib.GlobalWorkerOptions.workerSrc = `https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.worker.min.js`;

                // --- RENDERIZADO DEL PDF Y CAMPOS ---
                function renderPage(num) {
                    pdfDoc.getPage(num).then(page => {
                        const viewport = page.getViewport({ scale: scale });
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;
                        overlay.style.height = `${viewport.height}px`;
                        overlay.style.width = `${viewport.width}px`;
                        
                        page.render({ canvasContext: canvas.getContext('2d'), viewport: viewport }).promise.then(() => {
                            drawFieldsForPage(num);
                        });
                    });
                    document.getElementById('page-num').textContent = num;
                }

                function drawFieldsForPage(page) {
                    overlay.innerHTML = '';
                    fieldsToRender.filter(f => f.coordinates.page === page).forEach(field => {
                        const fieldDiv = document.createElement('div');
                        fieldDiv.style.position = 'absolute';
                        fieldDiv.style.left = `${field.coordinates.x}px`;
                        fieldDiv.style.top = `${field.coordinates.y}px`;
                        fieldDiv.style.width = `${field.coordinates.width}px`;
                        fieldDiv.style.height = `${field.coordinates.height}px`;
                        fieldDiv.style.display = 'flex';
                        fieldDiv.style.alignItems = 'center';
                        fieldDiv.style.justifyContent = 'center';
                        fieldDiv.style.fontFamily = 'Arial, sans-serif';
                        fieldDiv.style.fontSize = '14px';
                        
                        if (field.type === 'signature') {
                            if (field.value) {
                                fieldDiv.innerHTML = `<img src="${field.value}" style="width:100%; height:100%; object-fit:contain;">`;
                            } else {
                                fieldDiv.id = `signature-field-container`;
                                fieldDiv.style.border = '2px dashed #0d6efd';
                                fieldDiv.style.backgroundColor = 'rgba(13, 110, 253, 0.1)';
                                fieldDiv.style.cursor = 'pointer';
                                fieldDiv.innerHTML = '<span style="color: #555; user-select: none;">Haz clic aquí para firmar</span>';
                                fieldDiv.onclick = () => openSignatureModal(fieldDiv, field);
                            }
                        } else {
                            fieldDiv.style.backgroundColor = 'rgba(230, 230, 230, 0.2)';
                            fieldDiv.style.padding = '0 5px';
                            fieldDiv.textContent = field.value || '';
                        }
                        overlay.appendChild(fieldDiv);
                    });
                }

                pdfjsLib.getDocument(pdfUrl).promise.then(pdfDoc_ => {
                    pdfDoc = pdfDoc_;
                    document.getElementById('page-count').textContent = pdfDoc.numPages;
                    renderPage(pageNum);
                });
                
                // --- LÓGICA DE FIRMA ---
                function openSignatureModal(container, field) {
    return Swal.fire({
        title: 'Tu Firma',
        html: `
            <div class="mb-2">Dibuja en el recuadro o sube una imagen.</div>
            <div class="border" style="width: 100%; height: 250px;">
                <canvas id="swal-signature-canvas"></canvas>
            </div>
            <input type="file" id="signature-upload" accept="image/png, image/jpeg" class="form-control form-control-sm mt-2">
        `,
        showCancelButton: true,
        confirmButtonText: 'Aceptar Firma',
        width: '600px',
        didOpen: () => {
            const canvas = document.getElementById('swal-signature-canvas');
            const signaturePad = new SignaturePad(canvas, { backgroundColor: 'rgb(255, 255, 255)' });
            
            function resizeSwalCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext("2d").scale(ratio, ratio);
                signaturePad.clear();
            }
            window.addEventListener('resize', resizeSwalCanvas);
            resizeSwalCanvas();

            document.getElementById('signature-upload').addEventListener('change', function(event){
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => signaturePad.fromDataURL(e.target.result, {width: canvas.width, height: canvas.height});
                    reader.readAsDataURL(file);
                }
            });
            Swal.getPopup().signaturePad = signaturePad;
        },
        preConfirm: () => {
            const signaturePad = Swal.getPopup().signaturePad;
            if (signaturePad.isEmpty()) {
                Swal.showValidationMessage('Por favor, proporciona tu firma.');
                return false;
            }
            return signaturePad.toDataURL('image/png');
        }
    }).then(result => {
        if (result.isConfirmed && result.value) {
            const signatureUrl = result.value;
            container.innerHTML = `<img src="${signatureUrl}" style="width:100%; height:100%; object-fit:contain; pointer-events:none;">`;
            container.style.border = '2px solid #198754';
            container.style.backgroundColor = 'rgba(25, 135, 84, 0.1)';
            
            // 1. DESACTIVAMOS EL CLIC PARA QUE NO VUELVA A SALIR EL MODAL
            container.onclick = null;
            //  2. CAMBIAMOS EL CURSOR PARA INDICAR QUE SE PUEDE MOVER 
            container.style.cursor = 'move';
            
            signatureDataInput.value = signatureUrl;
            updateSignaturePosition(container, field);
            
            makeSignatureInteractive(container, field);
        }
    });
}

                // 2. AÑADIMOS LAS NUEVAS FUNCIONES PARA LA INTERACTIVIDAD
                function updateSignaturePosition(element, field) {
                    const rect = element.getBoundingClientRect();
                    const parentRect = overlay.getBoundingClientRect();
                    
                    // Actualizamos el objeto 'field' original con las nuevas coordenadas
                    field.coordinates.x = rect.left - parentRect.left;
                    field.coordinates.y = rect.top - parentRect.top;
                    field.coordinates.width = rect.width;
                    field.coordinates.height = rect.height;

                    // Actualizamos el input oculto que se enviará al servidor
                    signaturePositionInput.value = JSON.stringify(field.coordinates);
                }

                function makeSignatureInteractive(element, field) {
                    interact(element)
                        .draggable({
                            listeners: {
                                move(event) {
                                    const target = event.target;
                                    const x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
                                    const y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;

                                    target.style.transform = `translate(${x}px, ${y}px)`;
                                    target.setAttribute('data-x', x);
                                    target.setAttribute('data-y', y);
                                },
                                end(event) {
                                    // Al terminar de arrastrar, actualizamos las coordenadas base del elemento
                                    const target = event.target;
                                    target.style.left = `${target.offsetLeft + (parseFloat(target.getAttribute('data-x')) || 0)}px`;
                                    target.style.top = `${target.offsetTop + (parseFloat(target.getAttribute('data-y')) || 0)}px`;
                                    target.style.transform = ''; // Reseteamos la transformación
                                    target.removeAttribute('data-x');
                                    target.removeAttribute('data-y');
                                    updateSignaturePosition(target, field);
                                }
                            }
                        })
                        .resizable({
                            edges: { left: true, right: true, bottom: true, top: true },
                            listeners: {
                                move(event) {
                                    const target = event.target;
                                    Object.assign(target.style, {
                                        width: `${event.rect.width}px`,
                                        height: `${event.rect.height}px`,
                                    });
                                },
                                end(event) {
                                    updateSignaturePosition(event.target, field);
                                }
                            }
                        });
                }

                // --- NAVEGACIÓN Y ENVÍO ---
                document.getElementById('prev-page').addEventListener('click', () => { if (pageNum > 1) { pageNum--; renderPage(pageNum); } });
                document.getElementById('next-page').addEventListener('click', () => { if (pdfDoc && pageNum < pdfDoc.numPages) { pageNum++; renderPage(pageNum); } });
                
                form.addEventListener('submit', function(event) {
                    event.preventDefault(); 
                    if (!signatureDataInput.value) {
                        Swal.fire('Firma Requerida', 'Por favor, proporciona tu firma haciendo clic en el recuadro designado.', 'warning');
                        return;
                    }
                    Swal.fire({
                        title: '¿Confirmar y Enviar?',
                        text: "Una vez enviado, el documento quedará firmado.",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, enviar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>