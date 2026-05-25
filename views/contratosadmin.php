<?php
/**
 * CONTRATOS ADMIN — Vista administrador
 * PP Bienes Raíces — views/contratosadmin.php
 */
session_start();
if (!isset($_SESSION['usuario_id']) || strtolower($_SESSION['rol'] ?? '') !== 'admin') {
    header('Location: login.php'); exit;
}

require_once __DIR__ . '/../config/database.php';
$db = Database::conectar();

$tipos      = $db->query("SELECT id, nombre_opcion FROM opciones_sistema WHERE categoria='tipo_contrato' AND disponible=1")->fetchAll();
$vendedores = $db->query("SELECT id, CONCAT(nombre,' ',apellido) AS nombre_completo FROM usuarios WHERE rol='vendedor' AND cuenta_activa=1 ORDER BY nombre")->fetchAll();

// KPIs
$kpis = $db->query("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN oe.nombre_opcion='Borrador'    THEN 1 ELSE 0 END) AS borradores,
        SUM(CASE WHEN oe.nombre_opcion='Enviado'     THEN 1 ELSE 0 END) AS enviados,
        SUM(CASE WHEN oe.nombre_opcion='En revisión' THEN 1 ELSE 0 END) AS en_revision,
        SUM(CASE WHEN oe.nombre_opcion='Aprobado'    THEN 1 ELSE 0 END) AS aprobados,
        SUM(CASE WHEN oe.nombre_opcion='Rechazado'   THEN 1 ELSE 0 END) AS rechazados,
        SUM(c.monto_acordado) AS monto_total
    FROM contratos c
    JOIN opciones_sistema oe ON oe.id = c.estado_contrato_id
")->fetch();

// Pendientes = Enviados + En revisión
$pendientes = (int)$kpis['enviados'] + (int)$kpis['en_revision'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contratos — Admin | PP Bienes Raíces</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/panel.css">
    <link rel="stylesheet" href="../assets/css/contratos.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
</head>
<body>
<div class="panel-layout">
    <?php include 'layouts/sidebar.php'; ?>
    <div class="panel-main">
        <?php include 'layouts/headerpanel.php'; ?>
        <div class="panel-content">
            <div class="contratos-wrap">

                <!-- KPIs flat -->
                <div class="admin-kpis">
                    <div class="kpi-card">
                        <div class="kpi-icon kpi-icon-navy"><i class="fas fa-file-contract"></i></div>
                        <div class="kpi-data"><div class="kpi-label">Total</div><div class="kpi-valor"><?= (int)$kpis['total'] ?></div></div>
                    </div>
                    <?php if ($pendientes > 0): ?>
                    <div class="kpi-card kpi-card-alerta">
                        <div class="kpi-icon kpi-icon-red"><i class="fas fa-bell"></i></div>
                        <div class="kpi-data"><div class="kpi-label">Pendientes</div><div class="kpi-valor" style="color:var(--gold-dark);"><?= $pendientes ?></div></div>
                    </div>
                    <?php else: ?>
                    <div class="kpi-card">
                        <div class="kpi-icon kpi-icon-gold"><i class="fas fa-paper-plane"></i></div>
                        <div class="kpi-data"><div class="kpi-label">Enviados</div><div class="kpi-valor"><?= (int)$kpis['enviados'] ?></div></div>
                    </div>
                    <?php endif; ?>
                    <div class="kpi-card">
                        <div class="kpi-icon kpi-icon-green"><i class="fas fa-check-double"></i></div>
                        <div class="kpi-data"><div class="kpi-label">Aprobados</div><div class="kpi-valor"><?= (int)$kpis['aprobados'] ?></div></div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon kpi-icon-navy"><i class="fas fa-dollar-sign"></i></div>
                        <div class="kpi-data"><div class="kpi-label">Monto total</div><div class="kpi-valor" style="font-size:1.1rem;">$<?= number_format((float)$kpis['monto_total'], 0, '.', ',') ?></div></div>
                    </div>
                </div>

                <!-- Alerta si hay pendientes -->
                <?php if ($pendientes > 0): ?>
                <div class="alerta-pendientes">
                    <i class="fas fa-exclamation-circle"></i>
                    Tienes <strong><?= $pendientes ?></strong> contrato<?= $pendientes > 1 ? 's' : '' ?> pendiente<?= $pendientes > 1 ? 's' : '' ?> de revisión.
                    <button onclick="filtrarPendientes()">Ver pendientes <i class="fas fa-arrow-right"></i></button>
                </div>
                <?php endif; ?>

                <!-- Filtros flat -->
                <div class="filtros-bar-admin">
                    <div class="filtro-g">
                        <label><i class="fas fa-user-tie"></i> Vendedor</label>
                        <select id="filtroVendedor">
                            <option value="">Todos</option>
                            <?php foreach ($vendedores as $v): ?>
                                <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['nombre_completo']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filtro-g">
                        <label><i class="fas fa-tag"></i> Estado</label>
                        <select id="filtroEstado">
                            <option value="">Todos</option>
                            <option value="Borrador">Borrador</option>
                            <option value="Enviado">Enviado</option>
                            <option value="En revisión">En revisión</option>
                            <option value="Aprobado">Aprobado</option>
                            <option value="Rechazado">Rechazado</option>
                            <option value="Firmado">Firmado</option>
                            <option value="Anulado">Anulado</option>
                        </select>
                    </div>
                    <div class="filtro-g">
                        <label><i class="fas fa-file-alt"></i> Tipo</label>
                        <select id="filtroTipo">
                            <option value="">Todos</option>
                            <?php foreach ($tipos as $t): ?>
                                <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nombre_opcion']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filtro-g">
                        <label><i class="fas fa-search"></i> Buscar</label>
                        <input type="text" id="filtroBuscar" placeholder="Comprador, DUI...">
                    </div>
                    <div class="filtro-btns">
                        <button class="btn-filtrar-admin" id="btnFiltrar">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        <button class="btn-limpiar-admin" id="btnLimpiar">
                            <i class="fas fa-times"></i> Limpiar
                        </button>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="tabla-wrap-admin">
                    <div class="tabla-head-admin">
                        <span><i class="fas fa-list"></i> Todos los contratos</span>
                        <span class="tabla-contador" id="tablaContador">Cargando...</span>
                        <button class="btn-refresh tabla-refresh" id="btnRefreshTabla" title="Actualizar">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <div class="tabla-scroll">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Comprador</th>
                                    <th>Propiedad</th>
                                    <th>Vendedor</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaBody">
                                <tr><td colspan="8">
                                    <div class="loading-state"><div class="loading-spinner"></div>Cargando...</div>
                                </td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
        <?php include 'layouts/footer.php'; ?>
    </div>
</div>

<!-- Modal rechazo -->
<div class="modal-rechazo-overlay" id="modalRechazo">
    <div class="modal-rechazo-box">
        <div class="modal-rechazo-head">
            <span><i class="fas fa-ban"></i> Rechazar contrato</span>
            <button onclick="cerrarModalRechazo()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-rechazo-body">
            <label class="modal-rechazo-label"><i class="fas fa-comment-alt"></i> Motivo (opcional)</label>
            <textarea id="motivoRechazo" rows="3" placeholder="Explica al vendedor por qué se rechaza..."></textarea>
        </div>
        <div class="modal-rechazo-footer">
            <button class="modal-rechazo-cancel" onclick="cerrarModalRechazo()">Cancelar</button>
            <button class="modal-rechazo-confirm" onclick="confirmarRechazo()"><i class="fas fa-ban"></i> Rechazar</button>
        </div>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="toast-wrap" id="toastWrap"></div>

<script>
var MODO = 'contratos';
var ROL  = 'admin';
</script>
<script src="../assets/js/panel.js"></script>
<script src="../assets/js/contratos.js"></script>
<script>
/* ══════════════════════════════════════
   LÓGICA ESPECÍFICA ADMIN — tabla + flujo
══════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    cargarTabla();
    document.getElementById('btnFiltrar')?.addEventListener('click', cargarTabla);
    document.getElementById('btnRefreshTabla')?.addEventListener('click', cargarTabla);
    document.getElementById('btnLimpiar')?.addEventListener('click', () => {
        document.getElementById('filtroVendedor').value = '';
        document.getElementById('filtroEstado').value  = '';
        document.getElementById('filtroTipo').value    = '';
        document.getElementById('filtroBuscar').value  = '';
        cargarTabla();
    });
    document.getElementById('filtroBuscar')?.addEventListener('keydown', e => {
        if (e.key === 'Enter') cargarTabla();
    });
});

// Filtrar solo pendientes (Enviado + En revisión)
function filtrarPendientes() {
    document.getElementById('filtroEstado').value = 'Enviado';
    cargarTabla();
}

async function cargarTabla() {
    const tbody = document.getElementById('tablaBody');
    tbody.innerHTML = '<tr><td colspan="8"><div class="loading-state"><div class="loading-spinner"></div>Cargando...</div></td></tr>';

    const params = new URLSearchParams({
        action:   'listar',
        vendedor: document.getElementById('filtroVendedor').value,
        estado:   document.getElementById('filtroEstado').value,
        tipo:     document.getElementById('filtroTipo').value,
        buscar:   document.getElementById('filtroBuscar').value,
    });

    try {
        const res  = await fetch(`../controllers/contratocontroller.php?${params}`);
        const data = await res.json();
        const lista = data.contratos || [];
        document.getElementById('tablaContador').textContent = `${lista.length} contrato(s)`;

        if (!lista.length) {
            tbody.innerHTML = `<tr><td colspan="8">
                <div class="empty-state"><i class="fas fa-file-contract"></i><p>No hay contratos con esos filtros.</p></div>
            </td></tr>`;
            return;
        }

        tbody.innerHTML = lista.map(c => {
            const color     = c.estado_color || '#6B7280';
            const monto     = new Intl.NumberFormat('en-US',{style:'currency',currency:c.moneda||'USD'}).format(c.monto_acordado);
            const fecha     = new Date(c.fecha_generacion).toLocaleDateString('es-SV',{day:'2-digit',month:'short',year:'numeric'});
            const num       = c.id.substring(0,8).toUpperCase();
            const estado    = c.estado_nombre || '';
            const esAnulado = estado === 'Anulado';

            // Botones del flujo para admin
            let botonesFlujjo = '';
            if (estado === 'Enviado') {
                botonesFlujjo += `<button class="btn-sm btn-revisar" onclick="marcarEnRevision('${c.id}')" title="Marcar en revisión">
                    <i class="fas fa-eye"></i>
                </button>`;
            }
            if (estado === 'Enviado' || estado === 'En revisión') {
                botonesFlujjo += `
                <button class="btn-sm btn-aprobar" onclick="aprobarContrato('${c.id}')" title="Aprobar">
                    <i class="fas fa-check"></i>
                </button>
                <button class="btn-sm btn-rechazar" onclick="abrirRechazo('${c.id}')" title="Rechazar">
                    <i class="fas fa-times"></i>
                </button>`;
            }

            // Resaltar fila si está pendiente
            const trClass = (estado === 'Enviado' || estado === 'En revisión') ? 'tr-pendiente' : '';

            return `<tr class="${trClass}">
                <td><span class="td-num">#${num}</span></td>
                <td>
                    <div class="td-comprador">${esc(c.nombre_comprador)}</div>
                    <div class="td-sub">${esc(c.dui_comprador)}</div>
                </td>
                <td><div class="td-propiedad" title="${esc(c.titulo_anuncio)}">${esc(c.titulo_anuncio)}</div></td>
                <td><div class="td-vendedor">${esc(c.vendedor_nombre ?? '—')}</div></td>
                <td><span class="td-monto">${monto}</span></td>
                <td><span class="badge-estado">${esc(estado)}</span></td>
                <td><span class="td-fecha">${fecha}</span></td>
                <td>
                    <div class="td-acciones">
                        <button class="btn-sm btn-ver" onclick="verContrato('${c.id}')" title="Ver detalle">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn-sm btn-descargar" onclick="generarDocumento('${c.id}')" title="Descargar">
                            <i class="fas fa-download"></i>
                        </button>
                        ${botonesFlujjo}
                        ${!esAnulado ? `
                        <button class="btn-sm btn-eliminar" onclick="eliminarContratoAdmin('${c.id}','${esc(estado)}')" title="${estado==='Borrador'?'Eliminar':'Anular'}">
                            <i class="fas fa-trash"></i>
                        </button>` : ''}
                    </div>
                </td>
            </tr>`;
        }).join('');

    } catch(e) {
        tbody.innerHTML = '<tr><td colspan="8"><div class="empty-state"><p>Error al cargar contratos</p></div></td></tr>';
    }
}

async function eliminarContratoAdmin(id, estado) {
    const accion = estado === 'Borrador' ? 'eliminar' : 'anular';
    const ok = await modalConfirmar(`¿Deseas <strong>${accion}</strong> este contrato?`, accion === 'eliminar' ? 'Eliminar' : 'Anular', '#ef4444');
    if (!ok) return;
    const res  = await fetch(`../controllers/contratocontroller.php?action=eliminar&id=${id}`);
    const data = await res.json();
    toast(data.ok ? `Contrato ${accion}do` : (data.msg || 'Error'), data.ok ? 'ok' : 'error');
    if (data.ok) cargarTabla();
}
</script>
</body>
</html>