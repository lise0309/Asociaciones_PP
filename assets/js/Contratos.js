/**
 * contratos.js — PP Bienes Raíces
 * Lógica de contratos — cargar en contratos.php y contratosadmin.php
 *
 * CORRECCIONES APLICADAS:
 *  1. rolActual y yaFirmo calculados correctamente según ROL del usuario:
 *       - vendedor  → firma como 'Vendedor'
 *       - admin     → firma como 'Comprador'  (o el admin puede no firmar)
 *  2. yaFirmo verifica el estado real de la firma del rol correspondiente.
 *  3. Canvas de firma siempre inicializado correctamente al abrir el panel.
 *  4. initCanvas protegido contra doble inicialización con flag _init.
 *  5. Rol de firma enviado correctamente al controller.
 */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
    initContratos();
});

/* ══════════════════════════════════════
   CONTRATOS — INICIALIZACIÓN
══════════════════════════════════════ */
function initContratos() {
    cargarContratos();
    document.getElementById('btnRefresh')?.addEventListener('click', cargarContratos);

    const form = document.getElementById('formContrato');
    form?.addEventListener('submit', async e => {
        e.preventDefault();
        const btn = document.getElementById('btnCrear');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creando...';

        const fd = new FormData(form);
        fd.append('accion', 'crear');

        try {
            const res  = await fetch('../controllers/contratocontroller.php?action=crear', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.ok) {
                toast('Contrato creado exitosamente', 'ok');
                form.reset();
                cargarContratos();
            } else {
                toast(data.msg || 'Error al crear', 'error');
            }
        } catch (e) {
            toast('Error de conexión', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-plus"></i> Crear contrato';
        }
    });
}

/* ══════════════════════════════════════
   LISTAR CONTRATOS
══════════════════════════════════════ */
async function cargarContratos() {
    const el = document.getElementById('listaContratos');
    if (!el) return;
    el.innerHTML = '<div class="loading-state"><div class="loading-spinner"></div>Cargando...</div>';

    try {
        const res  = await fetch('../controllers/contratocontroller.php?action=listar');
        const data = await res.json();

        if (!data.ok || !data.contratos?.length) {
            el.innerHTML = `<div class="empty-state">
                <i class="fas fa-file-contract"></i>
                <p>No hay contratos aún. Crea uno nuevo.</p>
            </div>`;
            actualizarKpis([]);
            return;
        }

        actualizarKpis(data.contratos);

        el.innerHTML = data.contratos.map(c => {
            const monto     = new Intl.NumberFormat('en-US', { style: 'currency', currency: c.moneda || 'USD' }).format(c.monto_acordado);
            const fecha     = new Date(c.fecha_generacion).toLocaleDateString('es-SV', { day: '2-digit', month: 'short', year: 'numeric' });
            const num       = c.id.substring(0, 8).toUpperCase();
            const estado    = c.estado_nombre || '';
            const esAnulado = estado === 'Anulado';

            // ── Botones del flujo según ROL y ESTADO ──
            let botonesFlujjo = '';
            if (ROL === 'vendedor' && estado === 'Borrador') {
                botonesFlujjo = `<button class="btn-sm btn-enviar" onclick="enviarAlAdmin('${c.id}')">
                    <i class="fas fa-paper-plane"></i> Enviar al admin
                </button>`;
            } else if (ROL === 'admin') {
                if (estado === 'Enviado') {
                    botonesFlujjo = `<button class="btn-sm btn-revisar" onclick="marcarEnRevision('${c.id}')">
                        <i class="fas fa-eye"></i> Revisar
                    </button>`;
                }
                if (estado === 'Enviado' || estado === 'En revisión') {
                    botonesFlujjo += `
                    <button class="btn-sm btn-aprobar" onclick="aprobarContrato('${c.id}')">
                        <i class="fas fa-check"></i> Aprobar
                    </button>
                    <button class="btn-sm btn-rechazar" onclick="abrirRechazo('${c.id}')">
                        <i class="fas fa-times"></i> Rechazar
                    </button>`;
                }
            }

            return `
            <article class="contrato-item">
                <div class="ci-head">
                    <span class="ci-num">#${num}</span>
                    <span class="ci-badge">${esc(estado)}</span>
                    <span class="ci-fecha">${fecha}</span>
                </div>
                <div class="ci-body">
                    <div class="ci-comprador"><i class="fas fa-user"></i> ${esc(c.nombre_comprador)}</div>
                    <div class="ci-prop"><i class="fas fa-home"></i> ${esc(c.titulo_anuncio)}</div>
                    <div class="ci-tipo"><i class="fas fa-file-alt"></i> ${esc(c.tipo_nombre)} · ${esc(c.nombre_plantilla)}</div>
                    ${c.nombre_vendedor ? `<div class="ci-vendedor"><i class="fas fa-user-tie"></i> ${esc(c.nombre_vendedor)}</div>` : ''}
                    <div class="ci-monto">${monto}</div>
                </div>
                <div class="ci-actions">
                    <button class="btn-sm btn-ver" onclick="verContrato('${c.id}')">
                        <i class="fas fa-eye"></i> Ver
                    </button>
                    <button class="btn-sm btn-descargar" onclick="generarDocumento('${c.id}')">
                        <i class="fas fa-download"></i> Descargar
                    </button>
                    ${botonesFlujjo}
                    ${!esAnulado && (estado === 'Borrador' || estado === 'Rechazado') ? `
                    <button class="btn-sm btn-eliminar" onclick="eliminarContrato('${c.id}','${esc(estado)}')">
                        <i class="fas fa-trash"></i> ${estado === 'Borrador' ? 'Eliminar' : 'Anular'}
                    </button>` : ''}
                </div>
            </article>`;
        }).join('');

    } catch (e) {
        el.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error al cargar contratos</p></div>';
    }
}

function actualizarKpis(contratos) {
    const total    = contratos.length;
    const borrador = contratos.filter(c => c.estado_nombre === 'Borrador').length;
    const firmado  = contratos.filter(c => c.estado_nombre === 'Firmado' || c.estado_nombre === 'Aprobado').length;
    const monto    = contratos.reduce((s, c) => s + parseFloat(c.monto_acordado || 0), 0);
    if (document.getElementById('kpiTotal'))    document.getElementById('kpiTotal').textContent    = total;
    if (document.getElementById('kpiBorrador')) document.getElementById('kpiBorrador').textContent = borrador;
    if (document.getElementById('kpiFirmado'))  document.getElementById('kpiFirmado').textContent  = firmado;
    if (document.getElementById('kpiMonto'))    document.getElementById('kpiMonto').textContent    = '$' + monto.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}

/* ══════════════════════════════════════
   FLUJO DE APROBACIÓN
══════════════════════════════════════ */
async function enviarAlAdmin(id) {
    const ok = await modalConfirmar('¿Enviar este contrato al administrador para revisión?', 'Enviar', '#1A1953');
    if (!ok) return;
    const res = await cambiarEstadoPorNombre(id, 'Enviado');
    if (res.ok) { toast('Contrato enviado al administrador ✓', 'ok'); cargarContratos(); }
    else toast(res.msg || 'Error', 'error');
}

async function marcarEnRevision(id) {
    const res = await cambiarEstadoPorNombre(id, 'En revisión');
    if (res.ok) { toast('Marcado en revisión', 'ok'); cargarContratos(); }
    else toast(res.msg || 'Error', 'error');
}

async function aprobarContrato(id) {
    const ok = await modalConfirmar('¿Aprobar este contrato?', 'Aprobar', '#16a34a');
    if (!ok) return;
    const res = await cambiarEstadoPorNombre(id, 'Aprobado');
    if (res.ok) { toast('Contrato aprobado ✓', 'ok'); cargarContratos(); }
    else toast(res.msg || 'Error', 'error');
}

function abrirRechazo(id) {
    window._contratoRechazarId = id;
    document.getElementById('motivoRechazo').value = '';
    document.getElementById('modalRechazo').style.display = 'flex';
}

window.cerrarModalRechazo = function () {
    document.getElementById('modalRechazo').style.display = 'none';
    window._contratoRechazarId = null;
};

window.confirmarRechazo = async function () {
    const id     = window._contratoRechazarId;
    const motivo = document.getElementById('motivoRechazo').value.trim();
    if (!id) return;
    cerrarModalRechazo();
    const res = await cambiarEstadoPorNombre(id, 'Rechazado', motivo);
    if (res.ok) { toast('Contrato rechazado', 'ok'); cargarContratos(); }
    else toast(res.msg || 'Error', 'error');
};

async function cambiarEstadoPorNombre(contratoId, nombreEstado, motivo = '') {
    try {
        const resEst  = await fetch('../controllers/contratocontroller.php?action=estados');
        const dataEst = await resEst.json();
        const estado  = (dataEst.estados || []).find(e => e.nombre_opcion === nombreEstado);
        if (!estado) return { ok: false, msg: `Estado "${nombreEstado}" no encontrado en el sistema` };

        const fd = new FormData();
        fd.append('accion', 'estado');
        fd.append('id', contratoId);
        fd.append('estado_id', estado.id);
        if (motivo) fd.append('motivo', motivo);

        const res = await fetch('../controllers/contratocontroller.php?action=estado', { method: 'POST', body: fd });
        return await res.json();
    } catch (e) {
        return { ok: false, msg: 'Error de conexión' };
    }
}

/* ══════════════════════════════════════
   VER CONTRATO — MODAL
══════════════════════════════════════ */

/** Genera el HTML de la sección de firma electrónica con pestañas dibujar/subir */
function firmaSeccionHtml(cid) {
    return `
    <div class="firma-seccion" id="firmaSeccion_${cid}">
        <div class="firma-header">
            <i class="fas fa-pen-nib"></i> Firma electrónica
            <span class="firma-sub">El contrato está aprobado — pendiente de firma</span>
        </div>
        <div class="firma-estado-wrap" id="firmaEstado_${cid}">
            <div class="firma-loading"><i class="fas fa-spinner fa-spin"></i> Cargando estado de firmas...</div>
        </div>
        <div class="firma-canvas-wrap" id="firmaCanvasWrap_${cid}" style="display:none;">

            <!-- Datos del firmante -->
            <div class="firma-info-row">
                <div class="firma-fg">
                    <label>NOMBRE DEL FIRMANTE</label>
                    <input type="text" id="firmaNombre_${cid}" class="firma-input" placeholder="Nombre completo">
                </div>
                <div class="firma-fg">
                    <label>CORREO</label>
                    <input type="text" id="firmaCorreo_${cid}" class="firma-input" placeholder="correo@ejemplo.com">
                </div>
            </div>

            <!-- Pestañas de modo -->
            <div class="firma-tabs">
                <button class="firma-tab active" id="tabDibujar_${cid}" onclick="switchFirmaTab('${cid}','dibujar')">
                    <i class="fas fa-pen"></i> Dibujar
                </button>
                <button class="firma-tab" id="tabSubir_${cid}" onclick="switchFirmaTab('${cid}','subir')">
                    <i class="fas fa-image"></i> Pegar imagen
                </button>
            </div>

            <!-- Panel: dibujar -->
            <div id="panelDibujar_${cid}">
                <div class="firma-label-row">
                    <label>DIBUJA TU FIRMA</label>
                    <button onclick="limpiarCanvas('${cid}')" class="firma-btn-limpiar">
                        <i class="fas fa-eraser"></i> Limpiar
                    </button>
                </div>
                <canvas id="firmaCanvas_${cid}" class="firma-canvas" width="600" height="180"></canvas>
            </div>

            <!-- Panel: subir/pegar imagen -->
            <div id="panelSubir_${cid}" style="display:none;">
                <div class="firma-upload-area" id="firmaUploadArea_${cid}"
                     onclick="document.getElementById('firmaFileInput_${cid}').click()"
                     ondragover="event.preventDefault();this.classList.add('dragover')"
                     ondragleave="this.classList.remove('dragover')"
                     ondrop="firmaHandleDrop(event,'${cid}')">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Haz clic o arrastra tu imagen de firma aquí</p>
                    <span>PNG, JPG — máx. 2 MB</span>
                </div>
                <input type="file" id="firmaFileInput_${cid}" accept="image/png,image/jpeg,image/jpg"
                       style="display:none" onchange="firmaCargarArchivo(event,'${cid}')">
                <canvas id="firmaCanvasImg_${cid}" class="firma-canvas" width="600" height="180" style="display:none;cursor:default;"></canvas>
                <button id="firmaBtnQuitarImg_${cid}" class="firma-btn-limpiar" style="display:none;margin-top:6px;"
                        onclick="firmaQuitarImagen('${cid}')">
                    <i class="fas fa-times"></i> Quitar imagen
                </button>
            </div>

            <button class="firma-btn-guardar" onclick="guardarFirma('${cid}', '${ROL}')">
                <i class="fas fa-pen-nib"></i> Registrar mi firma
            </button>
        </div>
    </div>`;
}

/** Cambiar entre pestaña dibujar / subir imagen */
function switchFirmaTab(cid, modo) {
    document.getElementById(`tabDibujar_${cid}`).classList.toggle('active', modo === 'dibujar');
    document.getElementById(`tabSubir_${cid}`).classList.toggle('active', modo === 'subir');
    document.getElementById(`panelDibujar_${cid}`).style.display = modo === 'dibujar' ? '' : 'none';
    document.getElementById(`panelSubir_${cid}`).style.display   = modo === 'subir'   ? '' : 'none';
    if (modo === 'dibujar') initCanvas(cid);
}

/** Cargar imagen desde input file */
function firmaCargarArchivo(event, cid) {
    const file = event.target.files[0];
    if (file) firmaRenderizarImagen(file, cid);
}

/** Soltar imagen por drag & drop */
function firmaHandleDrop(event, cid) {
    event.preventDefault();
    document.getElementById(`firmaUploadArea_${cid}`).classList.remove('dragover');
    const file = event.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) firmaRenderizarImagen(file, cid);
    else toast('Solo se aceptan imágenes PNG o JPG', 'error');
}

/** Renderizar imagen elegida en el canvas de vista previa */
function firmaRenderizarImagen(file, cid) {
    if (file.size > 2 * 1024 * 1024) { toast('La imagen supera 2 MB', 'error'); return; }
    const reader = new FileReader();
    reader.onload = e => {
        const img = new Image();
        img.onload = () => {
            const canvas = document.getElementById(`firmaCanvasImg_${cid}`);
            const ctx    = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            // Escalar manteniendo proporción dentro del canvas
            const ratio = Math.min(canvas.width / img.width, canvas.height / img.height);
            const w = img.width * ratio, h = img.height * ratio;
            const x = (canvas.width - w) / 2, y = (canvas.height - h) / 2;
            ctx.drawImage(img, x, y, w, h);
            canvas.style.display = 'block';
            document.getElementById(`firmaUploadArea_${cid}`).style.display  = 'none';
            document.getElementById(`firmaBtnQuitarImg_${cid}`).style.display = 'inline-flex';
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

/** Quitar imagen cargada y volver al área de drop */
function firmaQuitarImagen(cid) {
    const canvas = document.getElementById(`firmaCanvasImg_${cid}`);
    canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
    canvas.style.display = 'none';
    document.getElementById(`firmaUploadArea_${cid}`).style.display   = '';
    document.getElementById(`firmaBtnQuitarImg_${cid}`).style.display = 'none';
    document.getElementById(`firmaFileInput_${cid}`).value            = '';
}

async function verContrato(id) {
    cerrarModal();
    const modal = crearModal('Detalle del contrato', '<div style="text-align:center;padding:32px;color:#6B7280;"><i class="fas fa-spinner fa-spin" style="font-size:1.5rem;"></i></div>');

    try {
        const res  = await fetch(`../controllers/contratocontroller.php?action=ver&id=${id}`);
        const data = await res.json();
        if (!data.ok) {
            modal.querySelector('.modal-body').innerHTML = `<p style="color:red">${data.msg}</p>`;
            return;
        }

        const c     = data.contrato;
        const monto = new Intl.NumberFormat('en-US', { style: 'currency', currency: c.moneda || 'USD' }).format(c.monto_acordado);
        const fecha = new Date(c.fecha_generacion).toLocaleDateString('es-SV', { day: '2-digit', month: 'long', year: 'numeric' });

        modal.querySelector('.modal-body').innerHTML = `
            <div class="modal-badges">
                <span class="modal-num">#${c.id.substring(0, 8).toUpperCase()}</span>
                <span class="modal-estado">${esc(c.estado_nombre)}</span>
            </div>
            <table class="modal-table">
                <tr><td><i class="fas fa-file-alt"></i> Tipo</td><td>${esc(c.tipo_nombre)}</td></tr>
                <tr><td><i class="fas fa-copy"></i> Plantilla</td><td>${esc(c.nombre_plantilla)}</td></tr>
                <tr><td><i class="fas fa-home"></i> Propiedad</td><td><strong>${esc(c.titulo_anuncio)}</strong></td></tr>
                <tr><td><i class="fas fa-map-marker-alt"></i> Ubicación</td><td>${esc(c.municipio)}, ${esc(c.departamento)}</td></tr>
                <tr><td><i class="fas fa-user"></i> Comprador</td><td><strong>${esc(c.nombre_comprador)}</strong></td></tr>
                <tr><td><i class="fas fa-envelope"></i> Correo</td><td>${esc(c.correo_comprador)}</td></tr>
                <tr><td><i class="fas fa-id-card"></i> DUI</td><td>${esc(c.dui_comprador)}</td></tr>
                <tr><td><i class="fas fa-dollar-sign"></i> Monto</td><td class="modal-monto">${monto}</td></tr>
                <tr><td><i class="fas fa-user-tie"></i> Vendedor</td><td>${esc(c.vendedor_nombre)}</td></tr>
                <tr><td><i class="fas fa-calendar"></i> Fecha</td><td>${fecha}</td></tr>
                ${c.archivo_generado ? `<tr><td><i class="fas fa-file-word"></i> Documento</td><td><span style="color:#16a34a;font-weight:700;"><i class="fas fa-check-circle"></i> Generado</span></td></tr>` : ''}
            </table>

            ${c.estado_nombre === 'Aprobado' ? firmaSeccionHtml(c.id) : ''}

            <div class="modal-footer-btns">
                <button class="modal-btn-cerrar" onclick="cerrarModal()"><i class="fas fa-times"></i> Cerrar</button>
                <button class="modal-btn-descargar" onclick="generarDocumento('${c.id}')">
                    <i class="fas fa-download"></i> Generar y descargar
                </button>
            </div>`;

    } catch (e) {
        modal.querySelector('.modal-body').innerHTML = '<p style="color:red">Error al cargar el contrato</p>';
    }

    // Cargar estado de firmas si el contrato está en estado Aprobado
    setTimeout(() => {
        if (document.getElementById(`firmaSeccion_${id}`)) {
            cargarEstadoFirmas(id);
        }
    }, 120);
}

/* ══════════════════════════════════════
   FIRMAS ELECTRÓNICAS
══════════════════════════════════════ */

async function cargarEstadoFirmas(contratoId) {
    const wrap       = document.getElementById(`firmaEstado_${contratoId}`);
    const canvasWrap = document.getElementById(`firmaCanvasWrap_${contratoId}`);
    if (!wrap) return;

    try {
        const res  = await fetch(`../controllers/firmacontroller.php?action=estado&contrato_id=${contratoId}`);
        const data = await res.json();
        if (!data.ok) {
            wrap.innerHTML = '<div style="color:#ef4444;font-size:.8rem;">Error al consultar firmas</div>';
            return;
        }

        const fV = data.firmas.find(f => f.rol === 'Vendedor');
        const fC = data.firmas.find(f => f.rol === 'Comprador');

        // ── Render del estado de cada firma ──
        let html = '<div class="firma-estados">';
        html += firmaStatusHtml('Vendedor', fV);
        html += firmaStatusHtml('Comprador', fC);
        html += '</div>';

        if (data.completo) {
            html += '<div class="firma-completo"><i class="fas fa-check-double"></i> Contrato firmado por ambas partes — propiedad marcada como Vendida</div>';
        }

        // ── Actualizar el estado PRIMERO antes de tocar canvasWrap ──
        wrap.innerHTML = html;

        if (data.completo) {
            if (canvasWrap) canvasWrap.style.display = 'none';
        } else {
            // vendedor → firma como Vendedor
            // admin    → firma como Comprador (en representación del comprador)
            const firmaActual = (ROL === 'admin') ? fC : fV;
            const yaFirmo     = !!firmaActual;

            if (!yaFirmo && canvasWrap) {
                canvasWrap.style.display = 'block';
                // Pre-llenar datos si están disponibles como variables PHP en la página
                const inputNombre = document.getElementById(`firmaNombre_${contratoId}`);
                if (inputNombre && !inputNombre.value && typeof USUARIO_NOMBRE !== 'undefined') {
                    inputNombre.value = USUARIO_NOMBRE;
                }
                const inputCorreo = document.getElementById(`firmaCorreo_${contratoId}`);
                if (inputCorreo && !inputCorreo.value && typeof USUARIO_CORREO !== 'undefined') {
                    inputCorreo.value = USUARIO_CORREO;
                }
                initCanvas(contratoId);
            } else if (yaFirmo && canvasWrap) {
                canvasWrap.style.display = 'none';
            }
        }

    } catch (e) {
        wrap.innerHTML = '<div style="color:#ef4444;font-size:.8rem;">Error al cargar estado de firmas</div>';
    }
}

/** Helper — genera el HTML de un estado de firma individual */
function firmaStatusHtml(label, firma) {
    if (firma) {
        const fechaFmt = new Date(firma.fecha_firma).toLocaleDateString('es-SV');
        return `<div class="firma-status firmado">
            <i class="fas fa-check-circle"></i>
            <div>
                <div class="fs-label">${label}</div>
                <div class="fs-val">${esc(firma.nombre_firmante)} · ${fechaFmt}</div>
            </div>
            ${firma.imagen_firma ? `<img src="${esc(firma.imagen_firma)}" class="firma-img-preview" title="Ver firma del ${label}">` : ''}
        </div>`;
    }
    return `<div class="firma-status pendiente">
        <i class="fas fa-clock"></i>
        <div>
            <div class="fs-label">${label}</div>
            <div class="fs-val">Pendiente de firma</div>
        </div>
    </div>`;
}

/** Inicializar el canvas de dibujo de firma */
function initCanvas(contratoId) {
    const canvas = document.getElementById(`firmaCanvas_${contratoId}`);
    if (!canvas || canvas._init) return;
    canvas._init = true;

    const ctx = canvas.getContext('2d');
    let dibujando = false, lastX = 0, lastY = 0;

    ctx.strokeStyle = '#1A1953';
    ctx.lineWidth   = 2.5;
    ctx.lineCap     = 'round';
    ctx.lineJoin    = 'round';

    function getPos(e) {
        const rect   = canvas.getBoundingClientRect();
        const scaleX = canvas.width  / rect.width;
        const scaleY = canvas.height / rect.height;
        const src    = e.touches ? e.touches[0] : e;
        return {
            x: (src.clientX - rect.left) * scaleX,
            y: (src.clientY - rect.top)  * scaleY,
        };
    }

    canvas.addEventListener('mousedown',  e => { dibujando = true; const p = getPos(e); lastX = p.x; lastY = p.y; });
    canvas.addEventListener('mousemove',  e => {
        if (!dibujando) return;
        const p = getPos(e);
        ctx.beginPath(); ctx.moveTo(lastX, lastY); ctx.lineTo(p.x, p.y); ctx.stroke();
        lastX = p.x; lastY = p.y;
    });
    canvas.addEventListener('mouseup',    () => { dibujando = false; });
    canvas.addEventListener('mouseleave', () => { dibujando = false; });

    canvas.addEventListener('touchstart', e => { e.preventDefault(); dibujando = true; const p = getPos(e); lastX = p.x; lastY = p.y; }, { passive: false });
    canvas.addEventListener('touchmove',  e => {
        e.preventDefault();
        if (!dibujando) return;
        const p = getPos(e);
        ctx.beginPath(); ctx.moveTo(lastX, lastY); ctx.lineTo(p.x, p.y); ctx.stroke();
        lastX = p.x; lastY = p.y;
    }, { passive: false });
    canvas.addEventListener('touchend', () => { dibujando = false; });
}

function limpiarCanvas(contratoId) {
    const canvas = document.getElementById(`firmaCanvas_${contratoId}`);
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
}

function canvasVacio(canvas) {
    const data = canvas.getContext('2d').getImageData(0, 0, canvas.width, canvas.height).data;
    for (let i = 3; i < data.length; i += 4) {
        if (data[i] > 0) return false;
    }
    return true;
}

async function guardarFirma(contratoId, rolUsuario) {
    const nombre = document.getElementById(`firmaNombre_${contratoId}`)?.value?.trim();
    const correo = document.getElementById(`firmaCorreo_${contratoId}`)?.value?.trim();

    if (!nombre) { toast('Ingresa el nombre del firmante', 'error'); return; }
    if (!correo) { toast('Ingresa el correo del firmante', 'error'); return; }

    // Detectar modo activo: dibujar o subir imagen
    const tabSubir  = document.getElementById(`tabSubir_${contratoId}`);
    const modoSubir = tabSubir?.classList.contains('active');

    let imagenB64 = '';
    if (modoSubir) {
        // Modo imagen: tomar del canvas de previsualización
        const canvasImg = document.getElementById(`firmaCanvasImg_${contratoId}`);
        if (!canvasImg || canvasImg.style.display === 'none' || canvasVacio(canvasImg)) {
            toast('Selecciona una imagen de firma', 'error'); return;
        }
        imagenB64 = canvasImg.toDataURL('image/png');
    } else {
        // Modo dibujo: tomar del canvas de dibujo
        const canvas = document.getElementById(`firmaCanvas_${contratoId}`);
        if (!canvas || canvasVacio(canvas)) { toast('Dibuja tu firma en el recuadro', 'error'); return; }
        imagenB64 = canvas.toDataURL('image/png');
    }

    // vendedor → firma como 'Vendedor' | admin → firma como 'Comprador'
    const rolFirma = (rolUsuario === 'admin') ? 'Comprador' : 'Vendedor';
    const fd = new FormData();
    fd.append('contrato_id',     contratoId);
    fd.append('imagen_firma',    imagenB64);
    fd.append('rol_firma',       rolFirma);
    fd.append('nombre_firmante', nombre);
    fd.append('correo_firmante', correo);

    const btn = document.querySelector(`#firmaCanvasWrap_${contratoId} .firma-btn-guardar`);
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...'; }

    try {
        const res  = await fetch('../controllers/firmacontroller.php?action=firmar', { method: 'POST', body: fd });
        const data = await res.json();

        if (data.ok) {
            toast('Firma registrada correctamente ✓', 'ok');
            if (data.ambos_firmaron) {
                toast('¡Contrato firmado por ambas partes! Propiedad marcada como Vendida.', 'ok');
                setTimeout(() => { cerrarModal(); cargarContratos(); }, 1500);
            } else {
                cargarEstadoFirmas(contratoId);
                if (btn) btn.closest('.firma-canvas-wrap')?.style && (btn.closest('.firma-canvas-wrap').style.display = 'none');
            }
        } else {
            toast(data.msg || 'Error al guardar la firma', 'error');
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-pen-nib"></i> Registrar mi firma'; }
        }
    } catch (e) {
        toast('Error de conexión', 'error');
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-pen-nib"></i> Registrar mi firma'; }
    }
}

/* ══════════════════════════════════════
   GENERAR / DESCARGAR DOCUMENTO
══════════════════════════════════════ */
function generarDocumento(contratoId) {
    toast('Generando documento...', 'ok');
    const a = document.createElement('a');
    a.href = `../controllers/generarcontratocontroller.php?id=${contratoId}`;
    a.download = '';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    setTimeout(() => cargarContratos(), 2000);
}

/* ══════════════════════════════════════
   ELIMINAR / ANULAR
══════════════════════════════════════ */
async function eliminarContrato(id, estado) {
    const accion     = estado === 'Borrador' ? 'eliminar permanentemente' : 'anular';
    const confirmado = await modalConfirmar(`¿Deseas <strong>${accion}</strong> este contrato?`, estado === 'Borrador' ? 'Eliminar' : 'Anular', '#ef4444');
    if (!confirmado) return;
    try {
        const res  = await fetch(`../controllers/contratocontroller.php?action=eliminar&id=${id}`);
        const data = await res.json();
        toast(data.ok ? 'Contrato ' + (estado === 'Borrador' ? 'eliminado' : 'anulado') : data.msg, data.ok ? 'ok' : 'error');
        if (data.ok) cargarContratos();
    } catch (e) {
        toast('Error de conexión', 'error');
    }
}

/* ══════════════════════════════════════
   MODAL HELPER
══════════════════════════════════════ */
function crearModal(titulo, bodyHtml) {
    cerrarModal();
    const overlay = document.createElement('div');
    overlay.id = 'modalContratoOverlay';
    overlay.style.cssText = 'position:fixed;inset:0;background:rgba(15,15,46,.65);backdrop-filter:blur(4px);z-index:2000;display:flex;align-items:center;justify-content:center;padding:20px;';
    overlay.innerHTML = `
        <div class="modal-contrato-box">
            <div class="modal-contrato-head">
                <span><i class="fas fa-file-contract" style="margin-right:8px;"></i>${titulo}</span>
                <button onclick="cerrarModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">${bodyHtml}</div>
        </div>`;
    overlay.addEventListener('click', e => { if (e.target === overlay) cerrarModal(); });
    document.body.appendChild(overlay);
    document.addEventListener('keydown', onEscModal);
    return overlay;
}

function cerrarModal() {
    document.getElementById('modalContratoOverlay')?.remove();
    document.removeEventListener('keydown', onEscModal);
}

function onEscModal(e) {
    if (e.key === 'Escape') cerrarModal();
}

/* ══════════════════════════════════════
   HELPERS GENERALES
══════════════════════════════════════ */
function esc(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g,  '&amp;')
        .replace(/</g,  '&lt;')
        .replace(/>/g,  '&gt;')
        .replace(/"/g,  '&quot;');
}

function toast(msg, tipo = 'ok') {
    const wrap = document.getElementById('toastWrap');
    if (!wrap) return;
    const icon = tipo === 'ok' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
    const t    = document.createElement('div');
    t.className = `toast toast-${tipo}`;
    t.innerHTML = `<i class="${icon}"></i><span>${msg}</span>`;
    wrap.appendChild(t);
    setTimeout(() => { t.classList.add('hide'); setTimeout(() => t.remove(), 280); }, 3500);
}

function modalConfirmar(mensaje, btnTexto = 'Confirmar', btnColor = '#1A1953') {
    return new Promise(resolve => {
        const overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;inset:0;background:rgba(15,15,46,.65);backdrop-filter:blur(4px);z-index:3000;display:flex;align-items:center;justify-content:center;padding:20px;';
        overlay.innerHTML = `
            <div style="background:#FFFCFB;width:100%;max-width:400px;box-shadow:6px 6px 0 rgba(0,0,0,.25);">
                <div style="padding:14px 18px;background:#1A1953;border-bottom:2px solid #FFD45A;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-exclamation-triangle" style="color:#FFD45A;font-size:.9rem;"></i>
                    <span style="font-size:.72rem;font-weight:800;color:#FFD45A;text-transform:uppercase;letter-spacing:.1em;">Confirmar acción</span>
                </div>
                <div style="padding:20px 18px;font-size:.875rem;color:#1C1C2E;line-height:1.6;border-left:4px solid #1A1953;">${mensaje}</div>
                <div style="padding:12px 18px 16px;display:flex;gap:8px;justify-content:flex-end;border-top:1px solid #CECCDF;">
                    <button id="btnNo" style="padding:8px 18px;background:transparent;border:1.5px solid #CECCDF;font-family:Inter,sans-serif;font-size:.75rem;font-weight:800;cursor:pointer;color:#5f6292;text-transform:uppercase;letter-spacing:.06em;">Cancelar</button>
                    <button id="btnSi" style="padding:8px 18px;background:${btnColor};color:#FFD45A;border:none;font-family:Inter,sans-serif;font-size:.75rem;font-weight:800;cursor:pointer;text-transform:uppercase;letter-spacing:.06em;box-shadow:3px 3px 0 rgba(0,0,0,.2);">${btnTexto}</button>
                </div>
            </div>`;
        document.body.appendChild(overlay);
        overlay.querySelector('#btnSi').addEventListener('click', () => { overlay.remove(); resolve(true); });
        overlay.querySelector('#btnNo').addEventListener('click', () => { overlay.remove(); resolve(false); });
        overlay.addEventListener('click', e => { if (e.target === overlay) { overlay.remove(); resolve(false); } });
    });
}