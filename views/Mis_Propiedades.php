<?php
/**
 * MIS PROPIEDADES — Vista del Vendedor
 * PP Bienes Raíces
 * views/mis_propiedades.php
 */

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../models/Mis_Propiedades_Model.php';

$model      = new PropiedadModel();
$vendedorId = $_SESSION['usuario_id'];
$rolUsuario = $_SESSION['rol'] ?? 'vendedor';

/* ── Paginación ── */
$pagina    = max(1, (int)($_GET['pagina'] ?? 1));
$porPagina = 12;

/* ── Filtros ── */
$filtroEstado = $_GET['estado'] ?? '';
$filtroTipo   = $_GET['tipo']   ?? '';
$filtroBuscar = trim($_GET['q'] ?? '');

/* ── Obtener propiedades ── */
// Si es admin, ve todas; si es vendedor, solo las suyas
$propiedades = ($rolUsuario === 'admin')
    ? $model->listarTodas($pagina, $porPagina, $filtroEstado, $filtroTipo, $filtroBuscar)
    : $model->listarPorVendedor($vendedorId, $pagina, $porPagina, $filtroEstado, $filtroTipo, $filtroBuscar);

$total       = ($rolUsuario === 'admin')
    ? $model->contarTodas($filtroEstado, $filtroTipo, $filtroBuscar)
    : $model->contarPorVendedor($vendedorId, $filtroEstado, $filtroTipo, $filtroBuscar);

$totalPaginas = max(1, (int)ceil($total / $porPagina));

/* ── Contadores rápidos ── */
$contadores = ($rolUsuario === 'admin')
    ? $model->contadoresAdmin()
    : $model->contadoresVendedor($vendedorId);

/* ── Helpers ── */
function badgeEstado(string $nombre, string $color): string {
    return '<span class="badge" style="--badge-color:' . htmlspecialchars($color) . '">'
         . htmlspecialchars($nombre) . '</span>';
}

function formatPrecio(float $precio): string {
    return '$' . number_format($precio, 0, '.', ',');
}

function urlFoto(?string $mini, ?string $orig): string {
    $ruta = $mini ?: $orig;
    if (!$ruta) return '../assets/img/no-photo.svg';
    return '../' . htmlspecialchars($ruta);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis propiedades | PP Bienes Raíces</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/panel.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/mis_propiedades.css">
</head>
<body>

<div class="panel-layout">
    <?php include 'layouts/sidebar.php'; ?>

    <div class="panel-main">
        <?php include 'layouts/headerpanel.php'; ?>

        <div class="panel-content">

            <!-- ── Page Header ── -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Mis propiedades</h1>
                    <p class="page-subtitle"><?php echo number_format($total); ?> propiedad<?php echo $total !== 1 ? 'es' : ''; ?> en total</p>
                </div>
                <a href="propiedad_form.php" class="btn-nueva">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                    Nueva propiedad
                </a>
            </div>

            <!-- ── Stats rápidas ── -->
            <div class="stats-row">
                <a href="mis_propiedades.php" class="stat-card <?php echo !$filtroEstado ? 'active' : ''; ?>">
                    <div class="stat-num"><?php echo $contadores['total'] ?? 0; ?></div>
                    <div class="stat-label">Total</div>
                </a>
                <a href="mis_propiedades.php?estado=11" class="stat-card <?php echo $filtroEstado === '11' ? 'active' : ''; ?>">
                    <div class="stat-dot" style="background:#22c55e"></div>
                    <div class="stat-num"><?php echo $contadores['activas'] ?? 0; ?></div>
                    <div class="stat-label">Activas</div>
                </a>
                <a href="mis_propiedades.php?estado=10" class="stat-card <?php echo $filtroEstado === '10' ? 'active' : ''; ?>">
                    <div class="stat-dot" style="background:#f59e0b"></div>
                    <div class="stat-num"><?php echo $contadores['pendientes'] ?? 0; ?></div>
                    <div class="stat-label">Pendientes</div>
                </a>
                <a href="mis_propiedades.php?estado=12" class="stat-card <?php echo $filtroEstado === '12' ? 'active' : ''; ?>">
                    <div class="stat-dot" style="background:#f97316"></div>
                    <div class="stat-num"><?php echo $contadores['pausadas'] ?? 0; ?></div>
                    <div class="stat-label">Pausadas</div>
                </a>
                <a href="mis_propiedades.php?estado=9" class="stat-card <?php echo $filtroEstado === '9' ? 'active' : ''; ?>">
                    <div class="stat-dot" style="background:#6b7280"></div>
                    <div class="stat-num"><?php echo $contadores['borradores'] ?? 0; ?></div>
                    <div class="stat-label">Borradores</div>
                </a>
            </div>

            <!-- ── Filtros y búsqueda ── -->
            <div class="toolbar">
                <form method="GET" action="" class="toolbar-form" id="filtroForm">
                    <!-- Búsqueda -->
                    <div class="search-wrap">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                        </svg>
                        <input type="text" name="q" class="search-input"
                               value="<?php echo htmlspecialchars($filtroBuscar); ?>"
                               placeholder="Buscar por nombre o dirección...">
                    </div>

                    <!-- Filtro tipo -->
                    <select name="tipo" class="filter-select" onchange="this.form.submit()">
                        <option value="">Todos los tipos</option>
                        <option value="1" <?php echo $filtroTipo === '1' ? 'selected' : ''; ?>>Casa</option>
                        <option value="2" <?php echo $filtroTipo === '2' ? 'selected' : ''; ?>>Apartamento</option>
                        <option value="3" <?php echo $filtroTipo === '3' ? 'selected' : ''; ?>>Local comercial</option>
                        <option value="4" <?php echo $filtroTipo === '4' ? 'selected' : ''; ?>>Terreno</option>
                        <option value="5" <?php echo $filtroTipo === '5' ? 'selected' : ''; ?>>Finca</option>
                        <option value="6" <?php echo $filtroTipo === '6' ? 'selected' : ''; ?>>Bodega</option>
                    </select>

                    <!-- Mantener estado y pagina si están activos -->
                    <?php if ($filtroEstado): ?>
                        <input type="hidden" name="estado" value="<?php echo htmlspecialchars($filtroEstado); ?>">
                    <?php endif; ?>

                    <button type="submit" class="btn-buscar">Buscar</button>

                    <?php if ($filtroBuscar || $filtroTipo || $filtroEstado): ?>
                        <a href="mis_propiedades.php" class="btn-limpiar">Limpiar</a>
                    <?php endif; ?>
                </form>

                <!-- Toggle vista -->
                <div class="vista-toggle">
                    <button class="toggle-btn active" id="btnGrid" title="Vista en cuadrícula">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    </button>
                    <button class="toggle-btn" id="btnList" title="Vista en lista">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 4a1 1 0 000 2h14a1 1 0 100-2H3zm0 4a1 1 0 000 2h14a1 1 0 100-2H3zm0 4a1 1 0 100 2h14a1 1 0 100-2H3z" clip-rule="evenodd"/></svg>
                    </button>
                </div>
            </div>

            <!-- ── Grid de propiedades ── -->
            <?php if (empty($propiedades)): ?>

                <div class="empty-state">
                    <div class="empty-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                    <?php if ($filtroBuscar || $filtroTipo || $filtroEstado): ?>
                        <h3>Sin resultados</h3>
                        <p>No encontramos propiedades con los filtros seleccionados.</p>
                        <a href="mis_propiedades.php" class="btn-nueva" style="margin-top:16px">Ver todas</a>
                    <?php else: ?>
                        <h3>Aún no tienes propiedades</h3>
                        <p>Publica tu primera propiedad y empieza a recibir consultas.</p>
                        <a href="propiedad_form.php" class="btn-nueva" style="margin-top:16px">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                            Nueva propiedad
                        </a>
                    <?php endif; ?>
                </div>

            <?php else: ?>

                <div class="propiedades-grid" id="propiedadesGrid">
                    <?php foreach ($propiedades as $p):
                        $fotoSrc     = urlFoto($p['foto_portada'] ?? null, null);
                        $estadoNombre = $p['estado_nombre']  ?? 'Sin estado';
                        $estadoColor  = $p['estado_color']   ?? '#6b7280';
                        $tipoNombre   = $p['tipo_nombre']    ?? '—';
                        $precio       = formatPrecio((float)($p['precio_pedido'] ?? 0));
                        $ubicacion    = htmlspecialchars(($p['municipio'] ?? '') . ', ' . ($p['departamento'] ?? ''));
                        $esDestacada  = (bool)($p['es_anuncio_destacado'] ?? 0);
                    ?>
                    <div class="prop-card <?php echo $esDestacada ? 'is-destacada' : ''; ?>">

                        <!-- Foto -->
                        <div class="prop-foto">
                            <img src="<?php echo $fotoSrc; ?>"
                                 alt="<?php echo htmlspecialchars($p['titulo_anuncio']); ?>"
                                 loading="lazy"
                                 onerror="this.src='../assets/img/no-photo.svg'">

                            <!-- Badges sobre la foto -->
                            <div class="prop-badges">
                                <?php echo badgeEstado($estadoNombre, $estadoColor); ?>
                                <?php if ($esDestacada): ?>
                                    <span class="badge-destacada">⭐ Destacada</span>
                                <?php endif; ?>
                            </div>

                            <!-- Tipo de negocio -->
                            <div class="prop-negocio">
                                <?php echo htmlspecialchars($p['tipo_negocio_nombre'] ?? '—'); ?>
                            </div>
                        </div>

                        <!-- Contenido -->
                        <div class="prop-body">
                            <div class="prop-tipo"><?php echo htmlspecialchars($tipoNombre); ?></div>
                            <h3 class="prop-titulo">
                                <?php echo htmlspecialchars($p['titulo_anuncio']); ?>
                            </h3>
                            <div class="prop-ubicacion">
                                <svg viewBox="0 0 16 16" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8 1.5a4.5 4.5 0 100 9 4.5 4.5 0 000-9zM2 6a6 6 0 1110.89 3.477l3.816 3.816a.75.75 0 01-1.06 1.06l-3.816-3.816A6 6 0 012 6z" clip-rule="evenodd"/>
                                </svg>
                                <?php echo $ubicacion; ?>
                            </div>

                            <!-- Características -->
                            <div class="prop-specs">
                                <?php if ($p['num_habitaciones']): ?>
                                <span class="spec-item">
                                    <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/></svg>
                                    <?php echo (int)$p['num_habitaciones']; ?> hab.
                                </span>
                                <?php endif; ?>
                                <?php if ($p['num_banos']): ?>
                                <span class="spec-item">
                                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h14a1 1 0 001-1V5a1 1 0 00-1-1H3zm0 2h14v8H3V6z" clip-rule="evenodd"/></svg>
                                    <?php echo (int)$p['num_banos']; ?> baños
                                </span>
                                <?php endif; ?>
                                <?php if ($p['metros_terreno']): ?>
                                <span class="spec-item">
                                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm3 1h6v4H7V5zm8 8v2h1v1H4v-1h1v-2a1 1 0 011-1h8a1 1 0 011 1z" clip-rule="evenodd"/></svg>
                                    <?php echo number_format((float)$p['metros_terreno'], 0); ?> m²
                                </span>
                                <?php endif; ?>
                            </div>

                            <!-- Precio y acciones -->
                            <div class="prop-footer">
                                <div class="prop-precio"><?php echo $precio; ?></div>
                                <div class="prop-acciones">
                                    <a href="propiedad_form.php?id=<?php echo $p['id']; ?>"
                                       class="accion-btn accion-editar" title="Editar">
                                        <svg viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                                    </a>
                                    <button type="button"
                                            class="accion-btn accion-eliminar"
                                            title="Eliminar"
                                            onclick="confirmarEliminar('<?php echo $p['id']; ?>', '<?php echo htmlspecialchars(addslashes($p['titulo_anuncio'])); ?>')">
                                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Fecha -->
                        <div class="prop-fecha">
                            Actualizada: <?php echo date('d/m/Y', strtotime($p['fecha_actualizacion'])); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- ── Paginación ── -->
                <?php if ($totalPaginas > 1): ?>
                <div class="paginacion">
                    <?php if ($pagina > 1): ?>
                        <a href="?pagina=<?php echo $pagina - 1; ?>&q=<?php echo urlencode($filtroBuscar); ?>&tipo=<?php echo urlencode($filtroTipo); ?>&estado=<?php echo urlencode($filtroEstado); ?>" class="pag-btn">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        </a>
                    <?php endif; ?>

                    <?php
                    $inicio = max(1, $pagina - 2);
                    $fin    = min($totalPaginas, $pagina + 2);
                    for ($p2 = $inicio; $p2 <= $fin; $p2++):
                    ?>
                        <a href="?pagina=<?php echo $p2; ?>&q=<?php echo urlencode($filtroBuscar); ?>&tipo=<?php echo urlencode($filtroTipo); ?>&estado=<?php echo urlencode($filtroEstado); ?>"
                           class="pag-btn <?php echo $p2 === $pagina ? 'active' : ''; ?>">
                            <?php echo $p2; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($pagina < $totalPaginas): ?>
                        <a href="?pagina=<?php echo $pagina + 1; ?>&q=<?php echo urlencode($filtroBuscar); ?>&tipo=<?php echo urlencode($filtroTipo); ?>&estado=<?php echo urlencode($filtroEstado); ?>" class="pag-btn">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                        </a>
                    <?php endif; ?>

                    <span class="pag-info"><?php echo $pagina; ?> de <?php echo $totalPaginas; ?></span>
                </div>
                <?php endif; ?>

            <?php endif; ?>

        </div><!-- /panel-content -->

        <?php include 'layouts/footer.php'; ?>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ── Modal de confirmación de eliminar ── -->
<div class="modal-overlay" id="modalEliminar">
    <div class="modal-confirm">
        <div class="modal-confirm-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
            </svg>
        </div>
        <h3>¿Eliminar propiedad?</h3>
        <p id="modalEliminarTexto">Esta acción no se puede deshacer.</p>
        <div class="modal-confirm-btns">
            <button type="button" class="btn-secondary" onclick="cerrarModal()">Cancelar</button>
            <form id="formEliminar" method="POST" action="../controllers/propiedadController.php">
                <input type="hidden" name="accion" value="eliminar">
                <input type="hidden" name="propiedad_id" id="eliminarId">
                <button type="submit" class="btn-danger">Sí, eliminar</button>
            </form>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="toast-wrap" id="toastWrap"></div>

<script src="../assets/js/mis_propiedades.js"></script>
</body>
</html>