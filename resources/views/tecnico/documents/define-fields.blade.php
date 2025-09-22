<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <span>Definir Campos para: <span class="fw-bold">{{ $document->original_filename }}</span></span>
            <div>
                <button id="saveFieldsBtn" class="btn btn-sm btn-primary"><i class="fas fa-save me-1"></i> Guardar Campos</button>
                <a href="{{ route('tecnico.documents.index') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left me-1"></i> Volver</a>
            </div>
        </div>
    </x-slot>

    <div class="alert alert-info small"><i class="fas fa-info-circle me-1"></i> Haz clic en el documento para añadir un campo. Arrastra las esquinas para redimensionar y el centro para mover.</div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body text-center" style="position: relative; overflow: auto; background-color: #525659;">
                    <div id="pdf-container" style="position: relative; display: inline-block;">
                        <canvas id="pdf-viewer"></canvas>
                        <div id="fields-container" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; cursor: crosshair;"></div>
                    </div>
                    <div class="mt-2">
                        <button id="prev-page" class="btn btn-sm btn-outline-light"><i class="fas fa-arrow-left"></i></button>
                        <span class="mx-2 text-white">Página <span id="page-num"></span> de <span id="page-count"></span></span>
                        <button id="next-page" class="btn btn-sm btn-outline-light"><i class="fas fa-arrow-right"></i></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <h5 class="card-title mb-3">Campos Añadidos</h5>
                    <div id="fields-list">
                        <p class="text-muted small">Aún no has añadido ningún campo.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    {{-- Librerías requeridas --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // --- ESTILOS CSS ---
            const styles = `
                .field-box {
                    position: absolute; border: 2px dashed #0d6efd; background-color: rgba(13, 110, 253, 0.2);
                    cursor: move; z-index: 10; display: flex; align-items: center; justify-content: center; overflow: hidden;
                    user-select: none;
                }
                .field-box img { width: 100%; height: 100%; object-fit: contain; pointer-events: none; }
                .field-box.selected { border-style: solid; border-color: #ffc107; background-color: rgba(255, 193, 7, 0.3); }
                .delete-btn {
                    position: absolute; top: -10px; right: -10px; width: 22px; height: 22px; background-color: #dc3545; color: white;
                    border: 2px solid white; border-radius: 50%; display: none; align-items: center; justify-content: center;
                    line-height: 18px; font-size: 14px; font-weight: bold; cursor: pointer; z-index: 12; box-shadow: 0 2px 5px rgba(0,0,0,0.2);
                }
                .field-box.selected .delete-btn { display: flex; }
                .field-placeholder { color: #6c757d; font-style: italic; font-size: 12px; }
                .field-label {
                    position: absolute; top: -22px; left: -2px; background-color: #0d6efd; color: white; padding: 2px 5px;
                    font-size: 10px; border-radius: 3px; white-space: nowrap;
                }
                .resizer { width: 10px; height: 10px; background: white; border: 1px solid #0d6efd; position: absolute; z-index: 11; }
                .resizer.se { cursor: se-resize; right: -5px; bottom: -5px; }
            `;
            const styleSheet = document.createElement("style");
            styleSheet.innerText = styles;
            document.head.appendChild(styleSheet);
            
            // --- CONFIGURACIÓN E INICIALIZACIÓN ---
            const pdfUrl = "{{ $documentUrl }}";
            const existingFields = @json($existingFields);
            const tags = @json($tags);
            let fields = [];
            let pdfDoc = null;
            let pageNum = 1;
            let selectedFieldId = null;
            const canvas = document.getElementById('pdf-viewer');
            const fieldsContainer = document.getElementById('fields-container');

            pdfjsLib.GlobalWorkerOptions.workerSrc = `https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.worker.min.js`;

            pdfjsLib.getDocument(pdfUrl).promise.then(pdfDoc_ => {
                pdfDoc = pdfDoc_;
                document.getElementById('page-count').textContent = pdfDoc.numPages;
                fields = existingFields.map(field => ({
                    id: field.id, name: field.name, type: field.type, value: field.value, ...field.coordinates
                }));
                renderPage(pageNum);
                renderFieldsList();
            });

            // --- EVENTOS DE BOTONES Y ACCIONES ---

            // Lógica para el botón "Firmar como técnico" eliminada

            fieldsContainer.addEventListener('click', function(e) {
                if (e.target === fieldsContainer) {
                    if (selectedFieldId) {
                        selectedFieldId = null;
                        drawFieldsForPage(pageNum);
                    } else {
                        openCreateFieldModal(e);
                    }
                }
            });

            document.getElementById('saveFieldsBtn').addEventListener('click', function() {
                const saveData = fields.map(f => ({
                    name: f.name, type: f.type, page: f.page,
                    x: f.x, y: f.y, width: f.width, height: f.height,
                    value: f.value || null
                }));
                fetch("{{ route('tecnico.documents.saveFields', $document->id) }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ fields: saveData })
                })
                .then(response => {
                    if (!response.ok) { return response.json().then(err => { throw err; }); }
                    return response.json();
                })
                .then(data => {
                    if(data.success) { 
                        Swal.fire('¡Guardado!', 'Los campos se han guardado con éxito.', 'success');
                    } 
                })
                .catch(error => {
                    let errorMessage = 'Ocurrió un error inesperado.';
                    if (error.message) { errorMessage = error.message; }
                    if (error.errors) { errorMessage = Object.values(error.errors).join('\n'); }
                    Swal.fire('Error', errorMessage, 'error');
                });
            });
            
            document.getElementById('prev-page').addEventListener('click', () => { if (pageNum > 1) { pageNum--; renderPage(pageNum); } });
            document.getElementById('next-page').addEventListener('click', () => { if (pdfDoc && pdfDoc.numPages && pageNum < pdfDoc.numPages) { pageNum++; renderPage(pageNum); } });
            

            // --- FUNCIONES AUXILIARES (renderPage, drawFieldsForPage, etc.) ---
            function renderPage(num) {
                if (!pdfDoc) return;
                pdfDoc.getPage(num).then(page => {
                    const viewport = page.getViewport({ scale: 1.5 });
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;
                    fieldsContainer.style.height = `${viewport.height}px`;
                    fieldsContainer.style.width = `${viewport.width}px`;
                    page.render({ canvasContext: canvas.getContext('2d'), viewport: viewport }).promise.then(() => {
                        drawFieldsForPage(num);
                    });
                });
                document.getElementById('page-num').textContent = num;
            }

            function drawFieldsForPage(page) {
                fieldsContainer.innerHTML = '';
                fields.filter(f => f.page === page).forEach(field => {
                    const fieldDiv = document.createElement('div');
                    fieldDiv.className = 'field-box';
                    fieldDiv.dataset.id = field.id;
                    if (field.id === selectedFieldId) fieldDiv.classList.add('selected');
                    
                    fieldDiv.style.left = `${field.x}px`;
                    fieldDiv.style.top = `${field.y}px`;
                    fieldDiv.style.width = `${field.width}px`;
                    fieldDiv.style.height = `${field.height}px`;
                    
                    const label = document.createElement('span');
                    label.className = 'field-label';
                    label.textContent = field.name;
                    fieldDiv.appendChild(label);

                    if (field.type === 'signature') {
                        if (field.value) {
                            const img = document.createElement('img');
                            img.src = field.value;
                            fieldDiv.appendChild(img);
                        } else {
                            const placeholder = document.createElement('span');
                            placeholder.className = 'field-placeholder';
                            placeholder.textContent = 'Espacio para firma del trabajador';
                            fieldDiv.appendChild(placeholder);
                        }
                    } else {
                        const placeholder = document.createElement('span');
                        placeholder.className = 'field-placeholder';
                        placeholder.textContent = `Campo: ${field.name}`;
                        fieldDiv.appendChild(placeholder);
                    }
                    
                    const deleteBtn = document.createElement('button');
                    deleteBtn.className = 'delete-btn';
                    deleteBtn.innerHTML = '&times;';
                    deleteBtn.onclick = (e) => {
                        e.stopPropagation();
                        fields = fields.filter(f => f.id !== field.id);
                        selectedFieldId = null;
                        renderFieldsList();
                        drawFieldsForPage(pageNum);
                    };
                    fieldDiv.appendChild(deleteBtn);

                    makeResizableAndDraggable(fieldDiv, field);
                    fieldsContainer.appendChild(fieldDiv);
                });
            }

            function renderFieldsList() {
                const list = document.getElementById('fields-list');
                list.innerHTML = fields.length === 0 ? '<p class="text-muted small">Aún no has añadido ningún campo.</p>' : '';
                fields.forEach(field => {
                    const item = document.createElement('div');
                    item.className = 'd-flex justify-content-between align-items-center mb-2 p-2 border rounded small';
                    item.innerHTML = `<div><strong>${field.name}</strong></div><button class="btn btn-sm btn-outline-danger py-0 px-1">&times;</button>`;
                    item.querySelector('button').onclick = () => {
                        fields = fields.filter(f => f.id !== field.id);
                        selectedFieldId = null;
                        renderFieldsList();
                        drawFieldsForPage(pageNum);
                    };
                    list.appendChild(item);
                });
            }
            
            function openCreateFieldModal(e) {
                const tagOptions = tags.map(tag => `<option value="${tag.name}">${tag.name}</option>`).join('');
                Swal.fire({
                    title: 'Añadir Nuevo Campo',
                    html: `<select id="swal-tag-name" class="swal2-select"><option value="" disabled selected>-- Elige un campo --</option>${tagOptions}</select>`,
                    preConfirm: () => document.getElementById('swal-tag-name').value || Swal.showValidationMessage('Debes seleccionar un campo')
                }).then(result => {
                    if (!result.isConfirmed || !result.value) return;
                    const tagName = result.value;
                    
                    if (tagName === 'CAMPO DE FIRMA DIBUJADO') {
                        showSignatureTypeModal().then(choice => {
                            if (choice === 'worker') {
                                createField({ name: 'CAMPO DE FIRMA', type: 'signature' }, e, null);
                            } else if (choice === 'technician') {
                                openSignaturePadModal().then(signatureDataUrl => {
                                    if (signatureDataUrl) createField({ name: 'FIRMA (Técnico)', type: 'signature' }, e, signatureDataUrl);
                                });
                            }
                        });
                    } else {
                        createField({ name: tagName, type: 'text' }, e);
                    }
                });
            }

            function showSignatureTypeModal() {
                return Swal.fire({
                    title: 'Tipo de Campo de Firma',
                    text: '¿Para quién es este campo de firma?',
                    icon: 'question',
                    showDenyButton: true,
                    confirmButtonText: 'Para el Trabajador',
                    denyButtonText: 'Para mí (Técnico)',
                }).then((result) => {
                    if (result.isConfirmed) {
                        return 'worker';
                    } else if (result.isDenied) {
                        return 'technician';
                    }
                    return null;
                });
            }

            function createField(fieldData, event, fieldValue = null) {
                const newField = {
                    id: Date.now(),
                    name: fieldData.name,
                    type: fieldData.type,
                    value: fieldValue,
                    page: pageNum,
                    x: event.offsetX, y: event.offsetY,
                    width: fieldValue ? 200 : 180, 
                    height: fieldValue ? 80 : 40,
                };
                fields.push(newField);
                selectedFieldId = newField.id;
                renderFieldsList();
                drawFieldsForPage(pageNum);
            }

            function openSignaturePadModal() {
                return Swal.fire({
                    title: 'Añadir Mi Firma (Técnico)',
                    html: `
                        <div style="width: 100%; height: 250px;">
                            <canvas id="signature-canvas" style="border: 1px solid black; width: 100%; height: 100%;"></canvas>
                        </div>
                        <small class="text-muted">Dibuja tu firma o sube una imagen.</small>
                        <input type="file" id="signature-upload" accept="image/png" class="form-control form-control-sm mt-2">`,
                    showCancelButton: true,
                    confirmButtonText: 'Guardar Firma',
                    width: '600px',
                    didOpen: () => {
                        const canvas = document.getElementById('signature-canvas');
                        const signaturePad = new SignaturePad(canvas, { backgroundColor: 'rgb(255, 255, 255)' });
                        document.getElementById('signature-upload').addEventListener('change', function(event) {
                            const file = event.target.files[0];
                            if (file) {
                                const reader = new FileReader();
                                reader.onload = (e) => signaturePad.fromDataURL(e.target.result, { width: canvas.width, height: canvas.height });
                                reader.readAsDataURL(file);
                            }
                        });
                        Swal.getPopup().signaturePad = signaturePad;
                    },
                    preConfirm: () => {
                        const signaturePad = Swal.getPopup().signaturePad;
                        if (signaturePad.isEmpty()) {
                            Swal.showValidationMessage('La firma no puede estar vacía');
                            return false;
                        }
                        return signaturePad.toDataURL('image/png');
                    }
                }).then(result => result.isConfirmed ? result.value : null);
            }
            
            function makeResizableAndDraggable(element, field) {
                // Lógica para seleccionar y mover
                element.addEventListener('mousedown', function(e) {
                    if (selectedFieldId !== field.id) {
                        selectedFieldId = field.id;
                        drawFieldsForPage(pageNum);
                        e.stopPropagation(); 
                        return;
                    }
                    if (e.target.classList.contains('resizer') || e.target.classList.contains('delete-btn')) return;
                    e.preventDefault();
                    let prevX = e.clientX; let prevY = e.clientY;
                    function mousemove(e) {
                        let newX = prevX - e.clientX; let newY = prevY - e.clientY;
                        element.style.left = (element.offsetLeft - newX) + "px";
                        element.style.top = (element.offsetTop - newY) + "px";
                        prevX = e.clientX; prevY = e.clientY;
                    }
                    function mouseup() {
                        field.x = element.offsetLeft; field.y = element.offsetTop;
                        window.removeEventListener('mousemove', mousemove);
                        window.removeEventListener('mouseup', mouseup);
                    }
                    window.addEventListener('mousemove', mousemove);
                    window.addEventListener('mouseup', mouseup);
                });
                // Lógica para redimensionar
                const resizer = document.createElement('div');
                resizer.className = 'resizer se';
                element.appendChild(resizer);
                resizer.addEventListener('mousedown', function(e) {
                    e.stopPropagation();
                    let prevX = e.clientX; let prevY = e.clientY;
                    function mousemove(e) {
                        const rect = element.getBoundingClientRect();
                        element.style.width = rect.width - (prevX - e.clientX) + "px";
                        element.style.height = rect.height - (prevY - e.clientY) + "px";
                        prevX = e.clientX; prevY = e.clientY;
                    }
                    function mouseup() {
                        field.width = parseInt(element.style.width);
                        field.height = parseInt(element.style.height);
                        window.removeEventListener('mousemove', mousemove);
                        window.removeEventListener('mouseup', mouseup);
                    }
                    window.addEventListener('mousemove', mousemove);
                    window.addEventListener('mouseup', mouseup);
                });
            }
        });
    </script>
    @endpush
</x-app-layout>