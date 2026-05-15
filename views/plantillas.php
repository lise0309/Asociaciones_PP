<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') { header('Location: login.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plantillas | PP Bienes Raíces</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                <div class="contratos-layout" style="grid-template-columns:380px 1fr;">
                    <!-- Subir -->
                    <div class="contrato-form-card">
                        <div class="card-head">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M9.25 13.25a.75.75 0 001.5 0V4.636l2.955 3.129a.75.75 0 001.09-1.03l-4.25-4.5a.75.75 0 00-1.09 0l-4.25 4.5a.75.75 0 101.09 1.03L9.25 4.636v8.614z"/><path d="M3.5 12.75a.75.75 0 00-1.5 0v2.5A2.75 2.75 0 004.75 18h10.5A2.75 2.75 0 0018 15.25v-2.5a.75.75 0 00-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5z"/></svg>
                            Subir plantilla
                        </div>
                        <div class="card-body">
                            <div class="alert-info" style="margin-bottom:20px;">
                                Sube plantillas <strong>.docx</strong> con variables como <code>{{nombre_comprador}}</code>, <code>{{propiedad}}</code>, <code>{{monto}}</code>, <code>{{fecha}}</code>
                            </div>
                            <form id="formPlantilla" enctype="multipart/form-data">
                                <div class="fg"><label>NOMBRE <span class="req">*</span></label><input type="text" name="nombre_plantilla" id="nombrePlantilla" placeholder="Ej: Contrato de Compraventa v1" required></div>
                                <div class="fg">
                                    <label>ARCHIVO (.docx, .doc, .pdf) <span class="req">*</span></label>
                                    <div class="upload-area" id="uploadArea">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>
                                        <p id="uploadText">Haz clic o arrastra aquí</p>
                                        <small>.docx, .doc, .pdf — máx. 10MB</small>
                                    </div>
                                    <input type="file" name="plantilla" id="plantillaInput" accept=".docx,.doc,.pdf" style="display:none;" required>
                                </div>
                                <button type="submit" class="btn-crear" id="btnSubir">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M9.25 13.25a.75.75 0 001.5 0V4.636l2.955 3.129a.75.75 0 001.09-1.03l-4.25-4.5a.75.75 0 00-1.09 0l-4.25 4.5a.75.75 0 101.09 1.03L9.25 4.636v8.614z"/><path d="M3.5 12.75a.75.75 0 00-1.5 0v2.5A2.75 2.75 0 004.75 18h10.5A2.75 2.75 0 0018 15.25v-2.5a.75.75 0 00-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5z"/></svg>
                                    Subir plantilla
                                </button>
                            </form>
                        </div>
                    </div>
                    <!-- Lista -->
                    <div class="contrato-list-card">
                        <div class="card-head">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z" clip-rule="evenodd"/></svg>
                            Plantillas disponibles
                        </div>
                        <div id="listaPlantillas" class="lista-body">
                            <div class="loading-state"><div class="loading-spinner"></div>Cargando...</div>
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
<script>window.MODO='plantillas';</script>
<script src="../assets/js/contratos.js"></script>
</body>
</html>