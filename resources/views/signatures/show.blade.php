<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <span>Firmar Documento: <span class="fw-bold">{{ $document->original_filename }}</span></span>
        </div>
    </x-slot>

    <form id="signatureForm" action="{{ route('signatures.store', $uniqueLink->token) }}" method="POST">
        @csrf
        <div class="row">
            <!-- Columna del Visor de PDF -->
            <div class="col-lg-9">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body text-center" style="position: relative; overflow: auto; background-color: #525659;">
                        <div id="pdf-container" style="position: relative; display: inline-block;">
                            <canvas id="pdf-viewer"></canvas>
                            <!-- Overlay para colocar las firmas -->
                            <div id="signature-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; cursor: pointer;"></div>
                        </div>
                        <div class="mt-2">
                            <button type="button" id="prev-page" class="btn btn-sm btn-outline-light"><i class="fas fa-arrow-left"></i></button>
                            <span class="mx-2 text-white">Página <span id="page-num"></span> de <span id="page-count"></span></span>
                            <button type="button" id="next-page" class="btn btn-sm btn-outline-light"><i class="fas fa-arrow-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna de Instrucciones y Envío -->
            <div class="col-lg-3">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Tu Firma</h5>
                        <div class="alert alert-secondary small">
                            Haz clic en el documento para colocar tu firma. Luego puedes moverla, agrandarla o eliminarla.
                        </div>
                        {{-- Inputs ocultos para guardar los datos de la firma --}}
                        <input type="hidden" name="signature" id="signature-data">
                        <input type="hidden" name="signature_position" id="signature-position">
                        <div class="d-grid">
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
        {{-- Nueva librería para mover y redimensionar --}}
        <script src="https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const pdfUrl = "{{ $documentUrl }}";
                let pdfDoc = null;
                let pageNum = 1;
                const scale = 1.5;
                const canvas = document.getElementById('pdf-viewer');
                const signatureOverlay = document.getElementById('signature-overlay');
                const signatureDataInput = document.getElementById('signature-data');
                const signaturePositionInput = document.getElementById('signature-position');
                const form = document.getElementById('signatureForm');

                pdfjsLib.GlobalWorkerOptions.workerSrc = `https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.worker.min.js`;

                function renderPage(num) {
                    pdfDoc.getPage(num).then(page => {
                        const viewport = page.getViewport({ scale: scale });
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;
                        signatureOverlay.style.height = `${viewport.height}px`;
                        signatureOverlay.style.width = `${viewport.width}px`;
                        page.render({ canvasContext: canvas.getContext('2d'), viewport: viewport });
                    });
                    document.getElementById('page-num').textContent = num;
                }

                pdfjsLib.getDocument(pdfUrl).promise.then(pdfDoc_ => {
                    pdfDoc = pdfDoc_;
                    document.getElementById('page-count').textContent = pdfDoc.numPages;
                    renderPage(pageNum);
                });

                document.getElementById('prev-page').addEventListener('click', () => { if (pageNum > 1) { pageNum--; renderPage(pageNum); } });
                document.getElementById('next-page').addEventListener('click', () => { if (pdfDoc && pageNum < pdfDoc.numPages) { pageNum++; renderPage(pageNum); } });

                // --- LÓGICA DE FIRMA INTERACTIVA ---
                signatureOverlay.addEventListener('click', function(e) {
                    if (e.target.closest('.signature-item')) return;

                    const rect = signatureOverlay.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;

                    openSignatureModal().then(signatureUrl => {
                        if (signatureUrl) {
                            placeSignatureOnPdf(signatureUrl, x, y);
                        }
                    });
                });

                function openSignatureModal() {
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
                    }).then(result => result.isConfirmed ? result.value : null);
                }

                function placeSignatureOnPdf(signatureUrl, x, y) {
                    signatureOverlay.innerHTML = ''; 

                    const wrapper = document.createElement('div');
                    wrapper.className = 'signature-item';
                    wrapper.style.cssText = `position: absolute; left: ${x}px; top: ${y}px; width: 150px; height: 75px; border: 2px dashed #0d6efd; touch-action: none;`;

                    const img = document.createElement('img');
                    img.src = signatureUrl;
                    img.style.cssText = 'width: 100%; height: 100%; object-fit: contain;';

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.innerHTML = '&times;';
                    removeBtn.style.cssText = 'position:absolute; top:-10px; right:-10px; background:red; color:white; border:none; border-radius:50%; width:20px; height:20px; cursor:pointer; display:flex; align-items:center; justify-content:center;';
                    removeBtn.onclick = () => {
                        wrapper.remove();
                        signatureDataInput.value = '';
                        signaturePositionInput.value = '';
                    };

                    wrapper.appendChild(img);
                    wrapper.appendChild(removeBtn);
                    signatureOverlay.appendChild(wrapper);

                    interact(wrapper)
                        .draggable({
                            listeners: {
                                move(event) {
                                    const target = event.target;
                                    const x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
                                    const y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;
                                    target.style.transform = `translate(${x}px, ${y}px)`;
                                    target.setAttribute('data-x', x);
                                    target.setAttribute('data-y', y);
                                    updateSignaturePosition(target);
                                }
                            }
                        })
                        .resizable({
                            edges: { left: true, right: true, bottom: true, top: true },
                            listeners: {
                                move(event) {
                                    Object.assign(event.target.style, {
                                        width: `${event.rect.width}px`,
                                        height: `${event.rect.height}px`,
                                    });
                                    updateSignaturePosition(event.target);
                                }
                            }
                        });
                    
                    signatureDataInput.value = signatureUrl;
                    updateSignaturePosition(wrapper);
                }

                function updateSignaturePosition(wrapper) {
                    const rect = wrapper.getBoundingClientRect();
                    const parentRect = signatureOverlay.getBoundingClientRect();
                    const position = {
                        page: pageNum,
                        x: rect.left - parentRect.left,
                        y: rect.top - parentRect.top,
                        width: rect.width,
                        height: rect.height
                    };
                    signaturePositionInput.value = JSON.stringify(position);
                }
                
                form.addEventListener('submit', function(event) {
                    event.preventDefault(); 

                    if (!signatureDataInput.value) {
                        Swal.fire('Firma Requerida', 'Por favor, proporciona tu firma haciendo clic en el documento.', 'warning');
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

