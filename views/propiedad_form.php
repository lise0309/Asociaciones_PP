<?php
/**
 * FORMULARIO DE PROPIEDAD (Crear/Editar)
 * PP Bienes Raíces — views/propiedad_form.php
 */

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$es_edicion   = isset($_GET['id']) && !empty($_GET['id']);
$propiedad_id = $_GET['id'] ?? '';
$titulo_pagina = $es_edicion ? 'Editar propiedad' : 'Nueva propiedad';

$propiedad        = null;
$fotos_existentes = [];

if ($es_edicion) {
    require_once __DIR__ . '/../models/vendedorPropiedadModel.php';
    $model            = new VendedorPropiedadModel();
    $propiedad        = $model->getById($propiedad_id, $_SESSION['usuario_id']);
    $fotos_existentes = $model->getFotos($propiedad_id);
}

$departamentos = [
    'San Salvador','Santa Ana','San Miguel','La Libertad',
    'Sonsonate','Usulután','La Unión','Morazán','Chalatenango',
    'Cuscatlán','La Paz','Cabañas','San Vicente','Ahuachapán',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo_pagina ?> | PP Bienes Raíces</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="../assets/css/panel.css">
    <link rel="stylesheet" href="../assets/css/propiedad_form.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
</head>
<body>

<div class="panel-layout">
    <?php include 'layouts/sidebar.php'; ?>

    <div class="panel-main">
        <?php include 'layouts/headerpanel.php'; ?>

        <div class="panel-content">
            <div class="form-propiedad">

                <!-- ── HEADER ── -->
                <div class="form-header">
                    <div class="form-header-left">
                        <h1><?= $es_edicion ? 'Editar propiedad' : 'Nueva propiedad' ?></h1>
                        <?php if ($es_edicion && $propiedad): ?>
                            <div class="propiedad-ref">
                                <?= htmlspecialchars($propiedad['titulo_anuncio'] ?? '') ?>
                                · #<?= strtoupper(substr($propiedad_id, 0, 8)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-header-actions">
                    </div>
                </div>

                <!-- ── FORM ── -->
                <form id="propiedadForm"
                      method="POST"
                      action="../controllers/vendedorPropiedadController.php"
                      enctype="multipart/form-data">

                    <input type="hidden" name="accion"       value="<?= $es_edicion ? 'editar' : 'crear' ?>">
                    <input type="hidden" name="propiedad_id" id="propiedad_id" value="<?= $propiedad_id ?>">

                    <div class="two-columns">

                        <!-- ════ COLUMNA IZQUIERDA ════ -->
                        <div class="col-left">

                            <!-- Información básica -->
                            <div class="form-section">
                                <div class="section-title">Información básica</div>
                                <div class="section-body">

                                    <div class="form-group">
                                        <label>NOMBRE DEL ANUNCIO <span class="required">*</span></label>
                                        <input type="text" class="form-control" id="titulo" name="titulo"
                                               value="<?= htmlspecialchars($propiedad['titulo_anuncio'] ?? '') ?>"
                                               placeholder="Ej: Casa moderna con piscina en Santa Tecla" required>
                                    </div>

                                    <div class="form-row-2">
                                        <div class="form-group">
                                            <label>TIPO DE INMUEBLE <span class="required">*</span></label>
                                            <select class="form-control" id="tipo_inmueble" name="tipo_inmueble" required>
                                                <option value="">Seleccionar...</option>
                                                <?php
                                                $tipos = [1=>'Casa',2=>'Apartamento',3=>'Local comercial',4=>'Terreno',5=>'Finca',6=>'Bodega'];
                                                foreach ($tipos as $v => $l):
                                                    $sel = (($propiedad['tipo_inmueble_id'] ?? '') == $v) ? 'selected' : '';
                                                ?>
                                                    <option value="<?= $v ?>" <?= $sel ?>><?= $l ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>MODALIDAD <span class="required">*</span></label>
                                            <div class="toggle-group">
                                                <label class="toggle-option">
                                                    <input type="radio" name="tipo_negocio" value="6"
                                                        <?= (!$es_edicion || ($propiedad['tipo_negocio_id'] ?? '') == 6) ? 'checked' : '' ?> required>
                                                    <span>Venta</span>
                                                </label>
                                                <label class="toggle-option">
                                                    <input type="radio" name="tipo_negocio" value="7"
                                                        <?= (($propiedad['tipo_negocio_id'] ?? '') == 7) ? 'checked' : '' ?>>
                                                    <span>Renta</span>
                                                </label>
                                                <label class="toggle-option">
                                                    <input type="radio" name="tipo_negocio" value="8"
                                                        <?= (($propiedad['tipo_negocio_id'] ?? '') == 8) ? 'checked' : '' ?>>
                                                    <span>Ambas</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row-2">
                                        <div class="form-group">
                                            <label>PRECIO (USD) <span class="required">*</span></label>
                                            <input type="number" class="form-control" id="precio" name="precio"
                                                   value="<?= htmlspecialchars($propiedad['precio_pedido'] ?? '') ?>"
                                                   step="0.01" min="0" placeholder="298000" required>
                                        </div>
                                        <div class="form-group">
                                            <label>ESTADO DEL INMUEBLE</label>
                                            <select class="form-control" id="condicion" name="condicion">
                                                <?php foreach (['Excelente estado','Buen estado','Requiere reparaciones'] as $c): ?>
                                                    <option value="<?= $c ?>" <?= (($propiedad['condicion'] ?? '') == $c) ? 'selected' : '' ?>><?= $c ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>DESCRIPCIÓN <span class="required">*</span></label>
                                        <textarea class="form-control" id="descripcion" name="descripcion" rows="5"
                                                  placeholder="Describe los detalles de tu propiedad..." required><?= htmlspecialchars($propiedad['descripcion_detallada'] ?? '') ?></textarea>
                                    </div>

                                </div>
                            </div>

                            <!-- Fotos -->
                            <div class="form-section">
                                <div class="section-title">
                                    Fotografías
                                    <span class="section-action" id="btnReordenar">Reordenar</span>
                                </div>
                                <div class="section-body">
                                    <div id="fotosGrid" class="fotos-grid">

                                        <?php foreach ($fotos_existentes as $foto): ?>
                                            <div class="foto-item <?= $foto['es_foto_portada'] ? 'is-portada' : '' ?>"
                                                 data-foto-id="<?= $foto['id'] ?>" draggable="true">
                                                <img src="../<?= htmlspecialchars($foto['url_foto_miniatura'] ?: $foto['url_foto_original']) ?>" alt="">
                                                <?php if ($foto['es_foto_portada']): ?>
                                                    <div class="foto-badge">Principal</div>
                                                <?php endif; ?>
                                                <button type="button" class="foto-delete"
                                                        onclick="eliminarFotoExistente(this,'<?= $foto['id'] ?>')"
                                                        title="Eliminar">
                                                    <svg viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        <?php endforeach; ?>

                                        <div class="foto-add" id="btnAgregarFoto">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                <rect x="3" y="3" width="18" height="18" rx="3"/>
                                                <path d="M12 8v8M8 12h8"/>
                                            </svg>
                                            <span>Agregar</span>
                                        </div>
                                    </div>

                                    <input type="file" name="fotos[]" id="fotosInput" multiple accept="image/*" style="display:none;">
                                    <p class="foto-hint">Arrastra para reordenar · La primera foto será la portada · Máx. 20 fotos</p>
                                </div>
                            </div>

                        </div>

                        <!-- ════ COLUMNA DERECHA ════ -->
                        <div class="col-right">

                            <!-- Ubicación -->
                            <div class="form-section">
                                <div class="section-title">Ubicación</div>
                                <div class="section-body">

                                    <div class="form-row-2">
                                        <div class="form-group">
                                            <label>DEPARTAMENTO <span class="required">*</span></label>
                                            <select class="form-control" id="departamento" name="departamento" required>
                                                <option value="">Seleccionar...</option>
                                                <?php foreach ($departamentos as $dep): ?>
                                                    <option value="<?= $dep ?>"
                                                        <?= (($propiedad['departamento'] ?? '') == $dep) ? 'selected' : '' ?>>
                                                        <?= $dep ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>MUNICIPIO <span class="required">*</span></label>
                                            <input type="text" class="form-control" id="municipio" name="municipio"
                                                   value="<?= htmlspecialchars($propiedad['municipio'] ?? '') ?>"
                                                   placeholder="Jayaque" required>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>DIRECCIÓN EXACTA <span class="required">*</span></label>
                                        <input type="text" class="form-control" id="direccion" name="direccion"
                                               value="<?= htmlspecialchars($propiedad['direccion_exacta'] ?? '') ?>"
                                               placeholder="Km 32, carretera a Jayaque" required>
                                    </div>

                                    <!-- Preview mapa + botón abrir -->
                                    <div class="mapa-preview <?= ($propiedad['latitud'] ?? '') ? 'tiene-coords' : '' ?>"
                                         id="mapaPreview">
                                        <div class="mapa-inner">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                                            </svg>
                                            <p><?= ($propiedad['latitud'] ?? '') ? 'Ubicación guardada' : 'Seleccionar en mapa' ?></p>
                                            <small id="coordenadasTexto">
                                                <?php if ($propiedad['latitud'] ?? ''): ?>
                                                    <?= number_format($propiedad['latitud'],5) ?>° N,
                                                    <?= number_format(abs($propiedad['longitud']),5) ?>° O
                                                <?php else: ?>
                                                    Haz clic para abrir el mapa
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        <button type="button" class="mapa-overlay-btn" id="btnMapa">Abrir mapa</button>
                                    </div>

                                    <input type="hidden" id="latitud"  name="latitud"  value="<?= $propiedad['latitud']  ?? '' ?>">
                                    <input type="hidden" id="longitud" name="longitud" value="<?= $propiedad['longitud'] ?? '' ?>">

                                    <div class="form-group">
                                        <label>PUNTO DE REFERENCIA</label>
                                        <input type="text" class="form-control" id="referencia" name="referencia"
                                               value="<?= htmlspecialchars($propiedad['punto_referencia'] ?? '') ?>"
                                               placeholder="Cerca de la iglesia central...">
                                    </div>

                                </div>
                            </div>

                            <!-- Características -->
                            <div class="form-section">
                                <div class="section-title">Medidas y características</div>
                                <div class="section-body">

                                    <div class="form-row-3">
                                        <div class="form-group">
                                            <label>Habitaciones</label>
                                            <input type="number" class="form-control" name="habitaciones"
                                                   value="<?= $propiedad['num_habitaciones'] ?? 0 ?>" min="0">
                                        </div>
                                        <div class="form-group">
                                            <label>Baños</label>
                                            <input type="number" class="form-control" name="banos"
                                                   value="<?= $propiedad['num_banos'] ?? 0 ?>" min="0">
                                        </div>
                                        <div class="form-group">
                                            <label>Parqueos</label>
                                            <input type="number" class="form-control" name="estacionamiento"
                                                   value="<?= $propiedad['tiene_estacionamiento'] ?? 0 ?>" min="0">
                                        </div>
                                    </div>

                                    <div class="form-row-2">
                                        <div class="form-group">
                                            <label>M² construcción</label>
                                            <input type="number" class="form-control" name="metros_construccion"
                                                   value="<?= $propiedad['metros_construccion'] ?? '' ?>"
                                                   step="0.01" placeholder="0.00">
                                        </div>
                                        <div class="form-group">
                                            <label>M² terreno</label>
                                            <input type="number" class="form-control" name="metros_terreno"
                                                   value="<?= $propiedad['metros_terreno'] ?? '' ?>"
                                                   step="0.01" placeholder="0.00">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Comodidades extras</label>
                                        <div class="check-group">
                                            <label class="check-option">
                                                <input type="checkbox" name="piscina" value="1"
                                                       <?= ($propiedad['tiene_piscina'] ?? 0) ? 'checked' : '' ?>>
                                                <span>🏊 Piscina</span>
                                            </label>
                                            <label class="check-option">
                                                <input type="checkbox" name="amueblado" value="1"
                                                       <?= ($propiedad['viene_amueblado'] ?? 0) ? 'checked' : '' ?>>
                                                <span>🛋️ Amueblado</span>
                                            </label>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <!-- Publicación -->
                            <div class="form-section">
                                <div class="section-title">Configuración de publicación</div>
                                <div class="section-body">

                                    <div class="form-group">
                                        <label>VISIBILIDAD</label>
                                        <div class="toggle-group">
                                            <label class="toggle-option">
                                                <input type="radio" name="visibilidad" value="publica"
                                                    <?= (!$es_edicion || ($propiedad['estado_publicacion_id'] ?? 0) != 12) ? 'checked' : '' ?>>
                                                <span>🌐 Pública</span>
                                            </label>
                                            <label class="toggle-option">
                                                <input type="radio" name="visibilidad" value="privada"
                                                    <?= (($propiedad['estado_publicacion_id'] ?? 0) == 12) ? 'checked' : '' ?>>
                                                <span>🔒 Privada</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>DESTACAR ANUNCIO</label>
                                        <div class="toggle-group">
                                            <label class="toggle-option">
                                                <input type="radio" name="destacar_radio" value="1"
                                                    <?= ($propiedad['es_anuncio_destacado'] ?? 0) ? 'checked' : '' ?>>
                                                <span>⭐ Sí, destacar</span>
                                            </label>
                                            <label class="toggle-option">
                                                <input type="radio" name="destacar_radio" value="0"
                                                    <?= !($propiedad['es_anuncio_destacado'] ?? 0) ? 'checked' : '' ?>>
                                                <span>No destacar</span>
                                            </label>
                                        </div>
                                        <input type="hidden" name="destacar" id="destacarHidden"
                                               value="<?= ($propiedad['es_anuncio_destacado'] ?? 0) ? '1' : '0' ?>">
                                        <p class="field-hint">Las propiedades destacadas aparecen primero en el inicio</p>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ── Botones inferiores ── -->
                    <div class="form-actions">
                        <?php if ($es_edicion): ?>
                            <button type="button" class="btn-danger" id="btnEliminar"
                                    onclick="confirmarEliminar('<?= $propiedad_id ?>','<?= htmlspecialchars($propiedad['titulo_anuncio'] ?? '') ?>')">
                                🗑 Eliminar propiedad
                            </button>
                        <?php endif; ?>
                        <button type="button" class="btn-secondary" id="btnCancelar">Cancelar</button>
                        <button type="submit" class="btn-primary" id="btnGuardar">
                            <?= $es_edicion ? 'Guardar cambios' : 'Publicar propiedad' ?>
                        </button>
                    </div>

                </form>
            </div>
        </div>

        <?php include 'layouts/footer.php'; ?>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ════ MODAL MAPA LEAFLET ════ -->
<div class="modal-overlay" id="modalMapaOverlay">
    <div class="modal-mapa">
        <div class="modal-header">
            <h3>Seleccionar ubicación</h3>
            <button class="modal-close" id="modalMapaCerrar" type="button">
                <svg viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div id="mapaLeaflet"></div>
            <p class="mapa-hint">Haz clic en el mapa o arrastra el marcador para ajustar la ubicación exacta</p>
        </div>
        <div class="modal-footer">
            <div class="coords-display">
                Coordenadas: <span id="coordsDisplay">—</span>
            </div>
            <button type="button" class="btn-secondary" id="btnCerrarMapa">Cancelar</button>
            <button type="button" class="btn-primary" id="btnConfirmarMapa">
                ✓ Confirmar ubicación
            </button>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="toast-wrap" id="toastWrap"></div>

<script>
    const es_edicion  = <?= $es_edicion ? 'true' : 'false' ?>;
    const propiedad_id = '<?= $propiedad_id ?>';
</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="../assets/js/panel.js"></script>
<script src="../assets/js/propiedad_form.js"></script>
</body>
</html>