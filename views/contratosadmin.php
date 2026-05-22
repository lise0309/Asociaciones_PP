<?php
/**
 * CONTRATOS ADMIN — Vista administrador
 * PP Bienes Raíces — views/contratosadmin.php
 * Ve todos los contratos, puede filtrar, cambiar estado, generar
 */
session_start();
if (!isset($_SESSION['usuario_id']) || strtolower($_SESSION['rol'] ?? '') !== 'admin') {
    header('Location: login.php'); exit;
}

require_once __DIR__ . '/../config/database.php';
$db = Database::conectar();

// Tipos de contrato para el form
$tipos      = $db->query("SELECT id, nombre_opcion FROM opciones_sistema WHERE categoria='tipo_contrato' AND disponible=1")->fetchAll();
$plantillas = $db->query("SELECT id, nombre_plantilla FROM plantillas_contrato WHERE plantilla_activa=1 ORDER BY nombre_plantilla")->fetchAll();
$vendedores = $db->query("SELECT id, CONCAT(nombre,' ',apellido) AS nombre_completo FROM usuarios WHERE rol='vendedor' AND cuenta_activa=1 ORDER BY nombre")->fetchAll();
$propiedades = $db->query("SELECT id, titulo_anuncio FROM propiedades ORDER BY titulo_anuncio")->fetchAll();

// KPIs rápidos
$kpis = $db->query("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN oe.nombre_opcion='Borrador' THEN 1 ELSE 0 END) AS borradores,
        SUM(CASE WHEN oe.nombre_opcion='Enviado'  THEN 1 ELSE 0 END) AS enviados,
        SUM(CASE WHEN oe.nombre_opcion='Firmado'  THEN 1 ELSE 0 END) AS firmados,
        SUM(CASE WHEN oe.nombre_opcion='Anulado'  THEN 1 ELSE 0 END) AS anulados,
        SUM(c.monto_acordado) AS monto_total
    FROM contratos c
    JOIN opciones_sistema oe ON oe.id = c.estado_contrato_id
")->fetch();
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
    <link rel="stylesheet" href="../assets/css/panel.css">
    <link rel="stylesheet" href="../assets/css/contratos.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <style>
        /* Estilos extra para vista admin */
        .admin-kpis {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 14px;
            margin-bottom: 24px;
        }
        @media(max-width:1100px){ .admin-kpis { grid-template-columns: repeat(3,1fr); } }
        @media(max-width:640px) { .admin-kpis { grid-template-columns: repeat(2,1fr); } }

        .kpi-mini {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--r-md);
            padding: 14px 16px;
            box-shadow: var(--sh-xs, 0 1px 3px rgba(26,25,83,.06));
        }
        .kpi-mini-label { font-size:.68rem; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.08em; }
        .kpi-mini-val   { font-size:1.6rem; font-weight:800; color:var(--navy); margin-top:2px; font-family:var(--font-d); }
        .kpi-mini-val.green { color:#16a34a; }
        .kpi-mini-val.gold  { color:var(--gold-dark); }
        .kpi-mini-val.red   { color:#ef4444; }

        /* Filtros */
        .filtros-bar {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--r-lg);
            padding: 16px 20px;
            margin-bottom: 20px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        .filtro-group { display:flex; flex-direction:column; gap:5px; min-width:160px; flex:1; }
        .filtro-group label { font-size:.68rem; font-weight:800; color:var(--navy); text-transform:uppercase; letter-spacing:.08em; opacity:.75; }
        .filtro-group select,
        .filtro-group input  { padding:9px 12px; border:1.5px solid var(--border); border-radius:var(--r-sm,10px); font-family:var(--font-b); font-size:.82rem; outline:none; transition:border-color var(--t); }
        .filtro-group select:focus,
        .filtro-group input:focus { border-color:var(--navy); }

        .btn-filtrar {
            padding:10px 22px; background:var(--navy); color:var(--gold);
            border:none; border-radius:999px; font-weight:700; font-size:.82rem;
            cursor:pointer; transition:all var(--t); white-space:nowrap;
            font-family:var(--font-b); align-self:flex-end;
        }
        .btn-filtrar:hover { background:var(--navy-mid,#252477); transform:translateY(-1px); }

        .btn-limpiar {
            padding:10px 16px; background:transparent; color:var(--muted);
            border:1.5px solid var(--border); border-radius:999px; font-weight:600; font-size:.82rem;
            cursor:pointer; transition:all var(--t); white-space:nowrap;
            font-family:var(--font-b); align-self:flex-end;
        }
        .btn-limpiar:hover { border-color:var(--navy); color:var(--navy); }

        /* Tabla */
        .tabla-wrap {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--r-lg);
            overflow: hidden;
            box-shadow: var(--sh-sm);
        }
        .tabla-head {
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 22px;
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-mid,#252477) 100%);
        }
        .tabla-head h3 { font-family:var(--font-d); font-size:1rem; font-weight:700; color:var(--gold); margin:0; }
        .tabla-contador { font-size:.75rem; color:rgba(255,255,255,.55); font-weight:500; }

        .tabla-scroll { overflow-x: auto; }
        table { width:100%; border-collapse:collapse; font-size:.82rem; }
        thead th {
            padding:11px 16px; text-align:left;
            font-size:.68rem; font-weight:800; text-transform:uppercase;
            letter-spacing:.08em; color:var(--muted);
            border-bottom:1px solid var(--border);
            background:rgba(26,25,83,.02);
            white-space:nowrap;
        }
        tbody tr { border-bottom:1px solid var(--border); transition:background var(--t); }
        tbody tr:last-child { border-bottom:none; }
        tbody tr:hover { background:rgba(26,25,83,.03); }
        tbody td { padding:12px 16px; vertical-align:middle; }

        .td-num { font-family:monospace; font-weight:700; color:var(--navy); font-size:.75rem; }
        .td-comprador { font-weight:700; color:var(--text); }
        .td-propiedad { color:var(--muted); font-size:.78rem; max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .td-vendedor  { color:var(--muted); font-size:.78rem; }
        .td-monto     { font-weight:800; color:var(--navy); white-space:nowrap; }
        .td-fecha     { color:var(--muted); font-size:.75rem; white-space:nowrap; }

        .badge-estado {
            display:inline-block; padding:3px 10px; border-radius:999px;
            font-size:.68rem; font-weight:700; white-space:nowrap;
        }

        .td-acciones { display:flex; gap:6px; flex-wrap:nowrap; }

        .empty-tabla {
            text-align:center; padding:48px; color:var(--muted); font-size:.875rem;
        }
        .empty-tabla svg { width:36px; height:36px; color:var(--border); margin:0 auto 12px; display:block; }
    </style>
</head>
<body>
<div class="panel-layout">
    <?php include 'layouts/sidebar.php'; ?>
    <div class="panel-main">
        <?php include 'layouts/headerpanel.php'; ?>
        <div class="panel-content">
            <div class="contratos-wrap">

                <!-- KPIs -->
                <div class="admin-kpis">
                    <div class="kpi-mini">
                        <div class="kpi-mini-label">Total contratos</div>
                        <div class="kpi-mini-val"><?= (int)$kpis['total'] ?></div>
                    </div>
                    <div class="kpi-mini">
                        <div class="kpi-mini-label">Borradores</div>
                        <div class="kpi-mini-val gold"><?= (int)$kpis['borradores'] ?></div>
                    </div>
                    <div class="kpi-mini">
                        <div class="kpi-mini-label">Enviados</div>
                        <div class="kpi-mini-val" style="color:#3B82F6;"><?= (int)$kpis['enviados'] ?></div>
                    </div>
                    <div class="kpi-mini">
                        <div class="kpi-mini-label">Firmados</div>
                        <div class="kpi-mini-val green"><?= (int)$kpis['firmados'] ?></div>
                    </div>
                    <div class="kpi-mini">
                        <div class="kpi-mini-label">Monto total</div>
                        <div class="kpi-mini-val" style="font-size:1.2rem;">
                            $<?= number_format((float)$kpis['monto_total'], 0, '.', ',') ?>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="filtros-bar">
                    <div class="filtro-group">
                        <label>Vendedor</label>
                        <select id="filtroVendedor">
                            <option value="">Todos</option>
                            <?php foreach ($vendedores as $v): ?>
                                <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['nombre_completo']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filtro-group">
                        <label>Estado</label>
                        <select id="filtroEstado">
                            <option value="">Todos</option>
                            <option value="Borrador">Borrador</option>
                            <option value="Enviado">Enviado</option>
                            <option value="Firmado">Firmado</option>
                            <option value="Vencido">Vencido</option>
                            <option value="Anulado">Anulado</option>
                        </select>
                    </div>
                    <div class="filtro-group">
                        <label>Tipo</label>
                        <select id="filtroTipo">
                            <option value="">Todos</option>
                            <?php foreach ($tipos as $t): ?>
                                <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nombre_opcion']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filtro-group">
                        <label>Buscar comprador</label>
                        <input type="text" id="filtroBuscar" placeholder="Nombre o DUI...">
                    </div>
                    <button class="btn-filtrar" id="btnFiltrar">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:4px;"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/></svg>
                        Filtrar
                    </button>
                    <button class="btn-limpiar" id="btnLimpiar">Limpiar</button>
                </div>

                <!-- Tabla -->
                <div class="tabla-wrap">
                    <div class="tabla-head">
                        <h3>Todos los contratos</h3>
                        <span class="tabla-contador" id="tablaContador">Cargando...</span>
                    </div>
                    <div class="tabla-scroll">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Comprador</th>
                                    <th>Propiedad</th>
                                    <th>Vendedor</th>
                                    <th>Tipo</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaBody">
                                <tr><td colspan="9" class="empty-tabla">
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

<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="toast-wrap" id="toastWrap"></div>

<script src="../assets/js/panel.js"></script>
<script src="../assets/js/contratos.js"></script>
<script>
// ── Cargar tabla al iniciar ─────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    cargarTabla();
    document.getElementById('btnFiltrar')?.addEventListener('click', cargarTabla);
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

async function cargarTabla() {
    const tbody = document.getElementById('tablaBody');
    tbody.innerHTML = '<tr><td colspan="9" class="empty-tabla"><div class="loading-state"><div class="loading-spinner"></div>Cargando...</div></td></tr>';

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
            tbody.innerHTML = `<tr><td colspan="9" class="empty-tabla">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
                No hay contratos con esos filtros.
            </td></tr>`;
            return;
        }

        tbody.innerHTML = lista.map(c => {
            const color  = c.estado_color || '#6B7280';
            const monto  = new Intl.NumberFormat('en-US',{style:'currency',currency:c.moneda||'USD'}).format(c.monto_acordado);
            const fecha  = new Date(c.fecha_generacion).toLocaleDateString('es-SV',{day:'2-digit',month:'short',year:'numeric'});
            const num    = c.id.substring(0,8).toUpperCase();
            const esAnulado = c.estado_nombre === 'Anulado';

            return `<tr>
                <td><span class="td-num">#${num}</span></td>
                <td>
                    <div class="td-comprador">${esc(c.nombre_comprador)}</div>
                    <div style="font-size:.72rem;color:var(--muted);">${esc(c.dui_comprador)}</div>
                </td>
                <td><div class="td-propiedad" title="${esc(c.titulo_anuncio)}">${esc(c.titulo_anuncio)}</div></td>
                <td><div class="td-vendedor">${esc(c.vendedor_nombre ?? '—')}</div></td>
                <td><div style="font-size:.75rem;color:var(--muted);">${esc(c.tipo_nombre)}</div></td>
                <td><span class="td-monto">${monto}</span></td>
                <td><span class="badge-estado" style="background:${color}20;color:${color};">${esc(c.estado_nombre)}</span></td>
                <td><span class="td-fecha">${fecha}</span></td>
                <td>
                    <div class="td-acciones">
                        <button class="btn-sm btn-outline" onclick="verContrato('${c.id}')" title="Ver detalle">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z"/><path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 010-1.186A10.004 10.004 0 0110 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0110 17c-4.257 0-7.893-2.66-9.336-6.41z" clip-rule="evenodd"/></svg>
                            Ver
                        </button>
                        <button class="btn-sm btn-primary-sm" onclick="generarDocumento('${c.id}')" title="Descargar">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 2.75a.75.75 0 00-1.5 0v8.614L6.295 8.235a.75.75 0 10-1.09 1.03l4.25 4.5a.75.75 0 001.09 0l4.25-4.5a.75.75 0 00-1.09-1.03l-2.955 3.129V2.75z"/><path d="M3.5 12.75a.75.75 0 00-1.5 0v2.5A2.75 2.75 0 004.75 18h10.5A2.75 2.75 0 0018 15.25v-2.5a.75.75 0 00-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5z"/></svg>
                        </button>
                        ${!esAnulado ? `
                        <button class="btn-sm btn-danger-sm" onclick="eliminarContratoAdmin('${c.id}','${esc(c.estado_nombre)}')" title="${c.estado_nombre==='Borrador'?'Eliminar':'Anular'}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4z" clip-rule="evenodd"/></svg>
                        </button>` : ''}
                    </div>
                </td>
            </tr>`;
        }).join('');

    } catch(e) {
        tbody.innerHTML = '<tr><td colspan="9" class="empty-tabla">Error al cargar contratos</td></tr>';
    }
}

async function eliminarContratoAdmin(id, estado) {
    const accion = estado === 'Borrador' ? 'eliminar' : 'anular';
    const ok = await modalConfirmar(`¿Deseas <strong>${accion}</strong> este contrato?`);
    if (!ok) return;
    const res  = await fetch(`../controllers/contratocontroller.php?action=eliminar&id=${id}`);
    const data = await res.json();
    toast(data.ok ? `Contrato ${accion}do` : (data.msg || 'Error'), data.ok ? 'ok' : 'error');
    if (data.ok) cargarTabla();
}
</script>
</body>
</html>