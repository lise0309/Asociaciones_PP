<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$titulo_pagina = 'Todos los Contratos';
require_once __DIR__ . '/../models/ContratoModel.php';
require_once __DIR__ . '/../config/database.php';

$model = new ContratoModel();
$db = Database::conectar();

// Obtener TODOS los contratos
$sql = "SELECT c.*, p.titulo_anuncio, 
               u.nombre as vendedor_nombre, u.apellido as vendedor_apellido,
               e.nombre_opcion as estado_nombre
        FROM contratos c
        JOIN propiedades p ON c.propiedad_id = p.id
        JOIN usuarios u ON c.vendedor_id = u.id
        JOIN opciones_sistema e ON c.estado_contrato_id = e.id
        ORDER BY c.fecha_generacion DESC";

$contratos = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$mensaje = $_SESSION['mensaje'] ?? '';
$error = $_SESSION['error_mensaje'] ?? '';
unset($_SESSION['mensaje'], $_SESSION['error_mensaje']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Todos los Contratos | PP Bienes Raíces</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/panel.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <style>
        .admin-contratos-container { padding: 20px; }
        .filtros { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .filtro-btn { padding: 8px 16px; border: none; border-radius: 20px; cursor: pointer; background: var(--white); border: 1px solid var(--border); }
        .filtro-btn.activo { background: var(--navy); color: var(--gold); }
        .tabla-contratos { width: 100%; border-collapse: collapse; }
        .tabla-contratos th, .tabla-contratos td { padding: 12px; text-align: left; border-bottom: 1px solid var(--border); }
        .tabla-contratos th { background: var(--navy); color: var(--gold); font-weight: 600; }
        .tabla-contratos tr:hover { background: var(--navy-soft); }
        .estado { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .estado-borrador { background: #e0e0e0; color: #666; }
        .estado-enviado { background: #3B82F6; color: white; }
        .estado-firmado { background: #22C55E; color: white; }
        .estado-anulado { background: #EF4444; color: white; }
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000; }
        .modal-content { background: white; border-radius: 12px; width: 500px; max-width: 90%; max-height: 80%; overflow: auto; }
        .modal-header, .modal-footer { padding: 15px; border-bottom: 1px solid #eee; }
        .modal-footer { border-bottom: none; border-top: 1px solid #eee; text-align: right; }
        .modal-close { float: right; font-size: 24px; cursor: pointer; background: none; border: none; }
    </style>
</head>
<body>

<div class="panel-layout">
    <?php include 'layouts/sidebar.php'; ?>

    <div class="panel-main">
        <?php include 'layouts/headerpanel.php'; ?>

        <div class="panel-content">
            <div class="admin-contratos-container">

                <h1>📄 Todos los Contratos</h1>
                <p>Gestión completa de contratos del sistema</p>

                <?php if ($mensaje): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="filtros">
                    <button class="filtro-btn activo" data-filtro="todos">Todos</button>
                    <button class="filtro-btn" data-filtro="17">Borrador</button>
                    <button class="filtro-btn" data-filtro="18">Enviado</button>
                    <button class="filtro-btn" data-filtro="19">Firmado</button>
                    <button class="filtro-btn" data-filtro="21">Anulado</button>
                </div>

                <div class="tabla-responsive">
                    <table class="tabla-contratos" id="tablaContratos">
                        <thead>
                            <tr>
                                <th>N° Contrato</th>
                                <th>Comprador</th>
                                <th>Propiedad</th>
                                <th>Vendedor</th>
                                <th>Monto</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contratos as $c): 
                                $estadoClass = '';
                                $estadoNombre = $c['estado_nombre'] ?? 'Borrador';
                                if ($c['estado_contrato_id'] == 17) $estadoClass = 'estado-borrador';
                                elseif ($c['estado_contrato_id'] == 18) $estadoClass = 'estado-enviado';
                                elseif ($c['estado_contrato_id'] == 19) $estadoClass = 'estado-firmado';
                                elseif ($c['estado_contrato_id'] == 21) $estadoClass = 'estado-anulado';
                            ?>
                            <tr data-estado="<?= $c['estado_contrato_id'] ?>">
                                <td><?= htmlspecialchars($c['numero_contrato'] ?? substr($c['id'], 0, 8)) ?></td>
                                <td><?= htmlspecialchars($c['nombre_comprador']) ?><br><small><?= htmlspecialchars($c['dui_comprador']) ?></small></td>
                                <td><?= htmlspecialchars($c['titulo_anuncio']) ?></td>
                                <td><?= htmlspecialchars($c['vendedor_nombre'] . ' ' . $c['vendedor_apellido']) ?></td>
                                <td><?= number_format($c['monto_acordado'], 2) ?> <?= $c['moneda'] ?></td>
                                <td><?= date('d/m/Y', strtotime($c['fecha_generacion'])) ?></td>
                                <td><span class="estado <?= $estadoClass ?>"><?= $estadoNombre ?></span></td>
                                <td>
                                    <a href="../controllers/ContratoController.php?action=generar&id=<?= $c['id'] ?>" class="btn-panel btn-panel-outline btn-panel-sm">📄 Word</a>
                                    <button class="btn-panel btn-panel-primary btn-panel-sm" onclick="verHistorial('<?= $c['id'] ?>')">📜 Historial</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (empty($contratos)): ?>
                    <div class="loading">No hay contratos registrados</div>
                <?php endif; ?>

            </div>
        </div>

        <?php include 'layouts/footer.php'; ?>
    </div>
</div>

<!-- Modal Historial -->
<div class="modal-overlay" id="modalHistorial">
    <div class="modal-content">
        <div class="modal-header">
            <h3>📜 Historial del Contrato</h3>
            <button class="modal-close" onclick="cerrarModal()">&times;</button>
        </div>
        <div class="modal-body" id="historialBody">
            Cargando...
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="cerrarModal()">Cerrar</button>
        </div>
    </div>
</div>

<script>
function filtrarContratos() {
    const filtro = document.querySelector('.filtro-btn.activo').dataset.filtro;
    const filas = document.querySelectorAll('#tablaContratos tbody tr');
    
    filas.forEach(fila => {
        if (filtro === 'todos') {
            fila.style.display = '';
        } else {
            const estado = fila.dataset.estado;
            fila.style.display = estado === filtro ? '' : 'none';
        }
    });
}

document.querySelectorAll('.filtro-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('activo'));
        this.classList.add('activo');
        filtrarContratos();
    });
});

async function verHistorial(contratoId) {
    const modal = document.getElementById('modalHistorial');
    const body = document.getElementById('historialBody');
    modal.style.display = 'flex';
    body.innerHTML = 'Cargando...';
    
    try {
        const response = await fetch(`../api/contrato_api.php?action=historial&id=${contratoId}`);
        const data = await response.json();
        
        if (data.success && data.historial && data.historial.length > 0) {
            let html = '<ul style="list-style:none; padding:0;">';
            data.historial.forEach(h => {
                html += `<li style="padding:10px; border-bottom:1px solid #eee;">
                            <strong>${h.fecha_accion}</strong><br>
                            ${h.accion_realizada}<br>
                            <small>Por: ${h.quien_lo_hizo} | IP: ${h.ip_accion || 'N/A'}</small>
                         </li>`;
            });
            html += '</ul>';
            body.innerHTML = html;
        } else {
            body.innerHTML = '<p>No hay historial registrado</p>';
        }
    } catch (error) {
        body.innerHTML = '<p>Error al cargar el historial</p>';
    }
}

function cerrarModal() {
    document.getElementById('modalHistorial').style.display = 'none';
}
</script>

</body>
</html>