<?php
/**
 * PLANTILLAS — Vista admin
 * PP Bienes Raíces — views/plantillas.php
 */
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plantillas | PP Bienes Raíces</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/panel.css">
    <link rel="stylesheet" href="../assets/css/plantillas.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
</head>
<body>
<div class="panel-layout">
    <?php include 'layouts/sidebar.php'; ?>
    <div class="panel-main">
        <?php include 'layouts/headerpanel.php'; ?>
        <div class="panel-content">
            <div class="plantillas-wrap">

                <div class="plantillas-layout">

                    <!-- Subir plantilla -->
                    <div class="plantilla-form-card">
                        <div class="card-head">
                            <i class="fas fa-upload"></i> Subir plantilla
                        </div>
                        <div class="card-body">

                            <div class="alert-info">
                                <i class="fas fa-info-circle"></i>
                                Sube plantillas <strong>.docx</strong> con variables como
                                <code>{{nombre_comprador}}</code>, <code>{{propiedad}}</code>, <code>{{monto}}</code>
                            </div>

                            <form id="formPlantilla" enctype="multipart/form-data">
                                <div class="fg">
                                    <label><i class="fas fa-tag"></i> Nombre <span class="req">*</span></label>
                                    <input type="text" name="nombre_plantilla" id="nombrePlantilla"
                                           placeholder="Ej: Contrato de Compraventa v1" required>
                                </div>
                                <div class="fg">
                                    <label><i class="fas fa-file-word"></i> Archivo <span class="req">*</span></label>
                                    <div class="upload-area" id="uploadArea">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <p id="uploadText">Haz clic o arrastra aquí</p>
                                        <small>.docx, .doc, .pdf — máx. 10MB</small>
                                    </div>
                                    <input type="file" name="plantilla" id="plantillaInput"
                                           accept=".docx,.doc,.pdf" style="display:none;" required>
                                </div>
                                <button type="submit" class="btn-subir" id="btnSubir">
                                    <i class="fas fa-upload"></i> Subir plantilla
                                </button>
                            </form>

                            <!-- Variables disponibles -->
                            <div class="variables-box">
                                <div class="variables-box-title">
                                    <i class="fas fa-code"></i> Variables disponibles en la plantilla
                                </div>
                                <div class="variables-list">
                                    <span class="variable-tag">{{nombre_comprador}}</span>
                                    <span class="variable-tag">{{dui_comprador}}</span>
                                    <span class="variable-tag">{{correo_comprador}}</span>
                                    <span class="variable-tag">{{monto}}</span>
                                    <span class="variable-tag">{{monto_palabras}}</span>
                                    <span class="variable-tag">{{moneda}}</span>
                                    <span class="variable-tag">{{propiedad}}</span>
                                    <span class="variable-tag">{{municipio}}</span>
                                    <span class="variable-tag">{{departamento}}</span>
                                    <span class="variable-tag">{{direccion}}</span>
                                    <span class="variable-tag">{{vendedor}}</span>
                                    <span class="variable-tag">{{correo_vendedor}}</span>
                                    <span class="variable-tag">{{telefono_vendedor}}</span>
                                    <span class="variable-tag">{{tipo_contrato}}</span>
                                    <span class="variable-tag">{{fecha}}</span>
                                    <span class="variable-tag">{{id_contrato}}</span>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Lista plantillas -->
                    <div class="plantilla-list-card">
                        <div class="card-head">
                            <i class="fas fa-list"></i> Plantillas disponibles
                            <button class="btn-refresh" id="btnRefresh" title="Actualizar">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                        <div id="listaPlantillas" class="lista-body">
                            <div class="loading-state">
                                <div class="loading-spinner"></div> Cargando...
                            </div>
                        </div>
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
<script src="../assets/js/plantillas.js"></script>
</body>
</html>