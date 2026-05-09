<?php
/**
 * FORMULARIO DE PROPIEDAD (Crear/Editar)
 * PP Bienes Raíces — Vendedor
 */

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$es_edicion = isset($_GET['id']) && !empty($_GET['id']);
$propiedad_id = $_GET['id'] ?? '';
$titulo_pagina = $es_edicion ? 'Editar propiedad' : 'Nueva propiedad';

$propiedad = null;
$fotos_existentes = [];
if ($es_edicion) {
    require_once __DIR__ . '/../models/vendedorPropiedadModel.php';
    $model = new VendedorPropiedadModel();
    $propiedad = $model->getById($propiedad_id, $_SESSION['usuario_id']);
    $fotos_existentes = $model->getFotos($propiedad_id);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo_pagina; ?> | PP Bienes Raíces</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/panel.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/propiedad_form.css">
</head>
<body>

<div class="panel-layout">
    <?php include 'layouts/sidebar.php'; ?>

    <div class="panel-main">
        <?php include 'layouts/headerpanel.php'; ?>

        <div class="panel-content">
            <div class="form-propiedad">

                <!-- Header -->
                <div class="form-header">
                    <div class="form-header-left">
                        <h1><?php echo $es_edicion ? 'Editar propiedad' : 'Propiedad'; ?></h1>
                        <?php if ($es_edicion && $propiedad): ?>
                            <div class="propiedad-ref"><?php echo htmlspecialchars($propiedad['titulo_anuncio'] ?? ''); ?> · #<?php echo strtoupper(substr($propiedad_id, 0, 4)); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-header-actions">
                        <button type="button" class="btn-header-secondary" id="btnCancelarHeader">Cancelar</button>
                        <button type="button" class="btn-header-primary" id="btnGuardarHeader">
                            <?php echo $es_edicion ? 'Guardar y publicar' : 'Guardar y publicar'; ?>
                        </button>
                    </div>
                </div>

                <form id="propiedadForm" method="POST" action="../controllers/vendedorPropiedadController.php" enctype="multipart/form-data">
                    <input type="hidden" name="accion" value="<?php echo $es_edicion ? 'editar' : 'crear'; ?>">
                    <input type="hidden" name="propiedad_id" id="propiedad_id" value="<?php echo $propiedad_id; ?>">

                    <div class="two-columns">
                        <!-- COLUMNA IZQUIERDA -->
                        <div class="col-left">

                            <!-- SECCIÓN: Información básica -->
                            <div class="form-section">
                                <div class="section-title">Información básica</div>
                                <div class="section-body">

                                    <div class="form-group">
                                        <label>NOMBRE <span class="required">*</span></label>
                                        <input type="text" class="form-control" id="titulo" name="titulo"
                                               value="<?php echo htmlspecialchars($propiedad['titulo_anuncio'] ?? ''); ?>"
                                               placeholder="Finca de Café con vivienda y beneficio húmedo" required>
                                    </div>

                                    <div class="form-row-2">
                                        <div class="form-group">
                                            <label>TIPO <span class="required">*</span></label>
                                            <select class="form-control" id="tipo_inmueble" name="tipo_inmueble" required>
                                                <option value="">Seleccionar...</option>
                                                <option value="1" <?php echo (($propiedad['tipo_inmueble_id'] ?? '') == 1) ? 'selected' : ''; ?>>Casa</option>
                                                <option value="2" <?php echo (($propiedad['tipo_inmueble_id'] ?? '') == 2) ? 'selected' : ''; ?>>Apartamento</option>
                                                <option value="3" <?php echo (($propiedad['tipo_inmueble_id'] ?? '') == 3) ? 'selected' : ''; ?>>Local comercial</option>
                                                <option value="4" <?php echo (($propiedad['tipo_inmueble_id'] ?? '') == 4) ? 'selected' : ''; ?>>Terreno</option>
                                                <option value="5" <?php echo (($propiedad['tipo_inmueble_id'] ?? '') == 5) ? 'selected' : ''; ?>>Finca</option>
                                                <option value="6" <?php echo (($propiedad['tipo_inmueble_id'] ?? '') == 6) ? 'selected' : ''; ?>>Bodega</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>MODALIDAD <span class="required">*</span></label>
                                            <div class="toggle-group">
                                                <label class="toggle-option">
                                                    <input type="radio" name="tipo_negocio" value="6"
                                                        <?php echo (!isset($propiedad) || ($propiedad['tipo_negocio_id'] ?? '') == 6 || empty($propiedad['tipo_negocio_id'])) ? 'checked' : ''; ?> required>
                                                    <span>Venta</span>
                                                </label>
                                                <label class="toggle-option">
                                                    <input type="radio" name="tipo_negocio" value="7"
                                                        <?php echo (($propiedad['tipo_negocio_id'] ?? '') == 7) ? 'checked' : ''; ?>>
                                                    <span>Renta</span>
                                                </label>
                                                <label class="toggle-option">
                                                    <input type="radio" name="tipo_negocio" value="8"
                                                        <?php echo (($propiedad['tipo_negocio_id'] ?? '') == 8) ? 'checked' : ''; ?>>
                                                    <span>Ambas</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row-2">
                                        <div class="form-group">
                                            <label>PRECIO (USD) <span class="required">*</span></label>
                                            <input type="number" class="form-control" id="precio" name="precio"
                                                   value="<?php echo htmlspecialchars($propiedad['precio_pedido'] ?? ''); ?>"
                                                   step="0.01" placeholder="298,000" required>
                                        </div>
                                        <div class="form-group">
                                            <label>ESTADO</label>
                                            <select class="form-control" id="condicion" name="condicion">
                                                <option value="Excelente estado" <?php echo (($propiedad['condicion'] ?? '') == 'Excelente estado') ? 'selected' : ''; ?>>Excelente estado</option>
                                                <option value="Buen estado" <?php echo (($propiedad['condicion'] ?? '') == 'Buen estado') ? 'selected' : ''; ?>>Buen estado</option>
                                                <option value="Requiere reparaciones" <?php echo (($propiedad['condicion'] ?? '') == 'Requiere reparaciones') ? 'selected' : ''; ?>>Requiere reparaciones</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>DESCRIPCIÓN <span class="required">*</span></label>
                                        <textarea class="form-control" id="descripcion" name="descripcion" rows="4"
                                                  placeholder="Describe tu propiedad detalladamente..." required><?php echo htmlspecialchars($propiedad['descripcion_detallada'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- SECCIÓN: Fotos -->
                            <div class="form-section">
                                <div class="section-title">
                                    Fotos
                                    <span class="section-action" id="btnReordenar">Reordenar</span>
                                </div>
                                <div class="section-body">
                                    <div id="fotosGrid" class="fotos-grid">
                                        <?php foreach ($fotos_existentes as $foto): ?>
                                            <div class="foto-item <?php echo $foto['es_foto_portada'] ? 'is-portada' : ''; ?>" data-foto-id="<?php echo $foto['id']; ?>">
                                                <img src="../<?php echo htmlspecialchars($foto['url_foto_miniatura'] ?: $foto['url_foto_original']); ?>" alt="Foto propiedad">
                                                <?php if ($foto['es_foto_portada']): ?>
                                                    <div class="foto-badge">Principal</div>
                                                <?php endif; ?>
                                                <button type="button" class="foto-delete" onclick="eliminarFotoExistente(this, '<?php echo $foto['id']; ?>')" title="Eliminar">
                                                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                                </button>
                                            </div>
                                        <?php endforeach; ?>

                                        <!-- Botón agregar foto -->
                                        <div class="foto-add" id="btnAgregarFoto">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                <rect x="3" y="3" width="18" height="18" rx="3"/>
                                                <path d="M12 8v8M8 12h8"/>
                                            </svg>
                                            <span>Agregar foto</span>
                                        </div>
                                    </div>
                                    <input type="file" name="fotos[]" id="fotosInput" multiple accept="image/*" style="display:none;">
                                    <p class="foto-hint">Arrastra para reordenar · Máx. 20 fotos</p>
                                </div>
                            </div>

                        </div>

                        <!-- COLUMNA DERECHA -->
                        <div class="col-right">

                            <!-- SECCIÓN: Ubicación -->
                            <div class="form-section">
                                <div class="section-title">Ubicación</div>
                                <div class="section-body">
                                    <div class="form-row-2">
                                        <div class="form-group">
                                            <label>DEPARTAMENTO <span class="required">*</span></label>
                                            <select class="form-control" id="departamento" name="departamento" required>
                                                <option value="">Seleccionar...</option>
                                                <?php
                                                $departamentos = ['San Salvador','Santa Ana','San Miguel','La Libertad','Sonsonate','Usulután','La Unión','Morazán','Chalatenango','Cuscatlán','La Paz','Cabañas','San Vicente','Ahuachapán'];
                                                foreach ($departamentos as $dep):
                                                    $sel = (($propiedad['departamento'] ?? '') == $dep) ? 'selected' : '';
                                                ?>
                                                    <option value="<?php echo $dep; ?>" <?php echo $sel; ?>><?php echo $dep; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>MUNICIPIO <span class="required">*</span></label>
                                            <input type="text" class="form-control" id="municipio" name="municipio"
                                                   value="<?php echo htmlspecialchars($propiedad['municipio'] ?? ''); ?>"
                                                   placeholder="Jayaque" required>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>DIRECCIÓN <span class="required">*</span></label>
                                        <input type="text" class="form-control" id="direccion" name="direccion"
                                               value="<?php echo htmlspecialchars($propiedad['direccion_exacta'] ?? ''); ?>"
                                               placeholder="Km 32, carretera a Jayaque" required>
                                    </div>

                                    <!-- Mapa embed -->
                                    <div class="mapa-preview" id="mapaPreview">
                                        <div class="mapa-inner">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                                            </svg>
                                            <p>Seleccionar en mapa</p>
                                            <small id="coordenadasTexto">
                                                <?php echo ($propiedad['latitud'] ?? '') ? $propiedad['latitud'] . '° N, ' . abs($propiedad['longitud']) . '° O' : '13.6721° N, 89.4532° O'; ?>
                                            </small>
                                        </div>
                                        <button type="button" class="mapa-overlay-btn" id="btnMapa">Seleccionar en mapa</button>
                                    </div>

                                    <input type="hidden" id="latitud" name="latitud" value="<?php echo $propiedad['latitud'] ?? ''; ?>">
                                    <input type="hidden" id="longitud" name="longitud" value="<?php echo $propiedad['longitud'] ?? ''; ?>">

                                    <div class="form-group" style="margin-top: 16px;">
                                        <label>REFERENCIA</label>
                                        <input type="text" class="form-control" id="referencia" name="referencia"
                                               value="<?php echo htmlspecialchars($propiedad['punto_referencia'] ?? ''); ?>"
                                               placeholder="Cerca de...">
                                    </div>
                                </div>
                            </div>

                            <!-- SECCIÓN: Características -->
                            <div class="form-section">
                                <div class="section-title">Medidas y características</div>
                                <div class="section-body">
                                    <div class="form-row-3">
                                        <div class="form-group">
                                            <label>Habitaciones</label>
                                            <input type="number" class="form-control" id="habitaciones" name="habitaciones"
                                                   value="<?php echo $propiedad['num_habitaciones'] ?? 0; ?>" min="0">
                                        </div>
                                        <div class="form-group">
                                            <label>Baños</label>
                                            <input type="number" class="form-control" id="banos" name="banos"
                                                   value="<?php echo $propiedad['num_banos'] ?? 0; ?>" min="0">
                                        </div>
                                        <div class="form-group">
                                            <label>Parqueos</label>
                                            <input type="number" class="form-control" id="estacionamiento" name="estacionamiento"
                                                   value="<?php echo $propiedad['tiene_estacionamiento'] ?? 0; ?>" min="0">
                                        </div>
                                    </div>

                                    <div class="form-row-2">
                                        <div class="form-group">
                                            <label>M² construcción</label>
                                            <input type="number" class="form-control" id="metros_construccion" name="metros_construccion"
                                                   value="<?php echo $propiedad['metros_construccion'] ?? ''; ?>" step="0.01" placeholder="0.00">
                                        </div>
                                        <div class="form-group">
                                            <label>M² terreno</label>
                                            <input type="number" class="form-control" id="metros_terreno" name="metros_terreno"
                                                   value="<?php echo $propiedad['metros_terreno'] ?? ''; ?>" step="0.01" placeholder="0.00">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Comodidades</label>
                                        <div class="check-group">
                                            <label class="check-option">
                                                <input type="checkbox" name="piscina" value="1" <?php echo ($propiedad['tiene_piscina'] ?? 0) ? 'checked' : ''; ?>>
                                                <span>Piscina</span>
                                            </label>
                                            <label class="check-option">
                                                <input type="checkbox" name="amueblado" value="1" <?php echo ($propiedad['viene_amueblado'] ?? 0) ? 'checked' : ''; ?>>
                                                <span>Amueblado</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECCIÓN: Publicación -->
                            <div class="form-section">
                                <div class="section-title">Configuración de publicación</div>
                                <div class="section-body">
                                    <div class="form-group">
                                        <label>VISIBILIDAD</label>
                                        <div class="toggle-group">
                                            <label class="toggle-option">
                                                <input type="radio" name="visibilidad" value="publica"
                                                    <?php echo (!isset($propiedad) || ($propiedad['estado_publicacion_id'] ?? 0) != 12) ? 'checked' : ''; ?>>
                                                <span>Pública</span>
                                            </label>
                                            <label class="toggle-option">
                                                <input type="radio" name="visibilidad" value="privada"
                                                    <?php echo (($propiedad['estado_publicacion_id'] ?? 0) == 12) ? 'checked' : ''; ?>>
                                                <span>Privada</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group destacar-group">
                                        <label>DESTACAR</label>
                                        <div class="toggle-group">
                                            <label class="toggle-option">
                                                <input type="radio" name="destacar_radio" value="1" <?php echo ($propiedad['es_anuncio_destacado'] ?? 0) ? 'checked' : ''; ?>>
                                                <span>Sí</span>
                                            </label>
                                            <label class="toggle-option">
                                                <input type="radio" name="destacar_radio" value="0" <?php echo !($propiedad['es_anuncio_destacado'] ?? 0) ? 'checked' : ''; ?>>
                                                <span>No</span>
                                            </label>
                                        </div>
                                        <input type="hidden" name="destacar" id="destacarHidden" value="<?php echo ($propiedad['es_anuncio_destacado'] ?? 0) ? '1' : '0'; ?>">
                                        <p class="field-hint">Aparecerá en la sección destacada del inicio</p>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                   
                    <!-- Botones de acción inferiores -->
                        <div class="form-actions">
                            <?php if ($es_edicion): ?>
                                <button type="button" class="btn-danger" id="btnEliminar" onclick="confirmarEliminar('<?php echo $propiedad_id; ?>', '<?php echo htmlspecialchars($propiedad['titulo_anuncio'] ?? ''); ?>')">
                                    🗑️ Eliminar propiedad
                                </button>
                            <?php endif; ?>
                            <button type="button" class="btn-secondary" id="btnCancelar">Cancelar</button>
                            <button type="submit" class="btn-primary" id="btnGuardar">
                                <?php echo $es_edicion ? 'Guardar y publicar' : 'Guardar y publicar'; ?>
                            </button>
                        </div>
                </form>
            </div>
        </div>

        <?php include 'layouts/footer.php'; ?>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Modal Mapa -->
<div class="modal-overlay" id="modalMapaOverlay">
    <div class="modal-mapa">
        <div class="modal-header">
            <h3>Seleccionar ubicación</h3>
            <button class="modal-close" id="modalMapaCerrar">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div id="map" style="height: 420px; width: 100%;"></div>
            <p class="mapa-hint">Haz clic en el mapa o arrastra el marcador para seleccionar la ubicación exacta</p>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" id="btnCerrarMapa">Cancelar</button>
            <button class="btn-primary" id="btnConfirmarMapa">Confirmar ubicación</button>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="toast-wrap" id="toastWrap"></div>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB41DRUbKWJHPxaFjMAwdrzWzbVKartNGg&callback=initMap&libraries=geometry&v=weekly" async defer></script>
<script>
    const es_edicion = <?php echo $es_edicion ? 'true' : 'false'; ?>;
    const propiedad_id = '<?php echo $propiedad_id; ?>';
</script>
<script>
function confirmarEliminar(id, titulo) {
    if (confirm('¿Estás segura de que deseas eliminar la propiedad "' + titulo + '"?\n\nEsta acción no se puede deshacer y eliminará todas las fotos asociadas.')) {
        window.location.href = '../controllers/vendedorPropiedadController.php?action=eliminar&id=' + id;
    }
}
</script>
<script src="../assets/js/propiedad_form.js"></script>
</body>
</html>