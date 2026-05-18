<?php
/**
 * DETALLE — PP Bienes Raíces
 * views/detalle.php
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detalle de propiedad | PP Bienes Raíces</title>
  <base href="/Asociaciones_PP/">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,800;1,700&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <!-- Font Awesome 5 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="assets/css/navbar.css">
  <link rel="stylesheet" href="assets/css/detalle.css">
  <link rel="stylesheet" href="assets/css/footer.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
</head>
<body>

<?php include __DIR__ . '/layouts/navbar.php'; ?>

<!-- Skeleton -->
<div id="skeletonWrap" class="skeleton-wrap">
  <div class="sk sk-gallery"></div>
  <div class="det-container">
    <div class="sk sk-title"></div>
    <div class="sk sk-line"></div>
    <div class="sk sk-line sk-short"></div>
  </div>
</div>

<main id="mainContent" style="display:none;">

  <section class="galeria-section">
    <div class="galeria-grid" id="galeriaGrid"></div>
    <div class="lightbox" id="lightbox">
      <button class="lb-close" id="lbClose">✕</button>
      <button class="lb-prev"  id="lbPrev">‹</button>
      <button class="lb-next"  id="lbNext">›</button>
      <img class="lb-img" id="lbImg" src="" alt="">
      <div class="lb-counter" id="lbCounter"></div>
    </div>
  </section>

  <div class="det-container">
    <div class="det-layout">

      <!-- COLUMNA PRINCIPAL -->
      <div class="det-main">
        <div class="det-header">
          <div class="det-tags"   id="detTags"></div>
          <h1 class="det-titulo" id="detTitulo"></h1>
          <div class="det-ubicacion">
            <i class="fas fa-map-marker-alt"></i>
            <span id="ubicacionTexto"></span>
          </div>
          <div class="det-precio" id="detPrecio"></div>

          <!-- BOTONES DE ACCIÓN RÁPIDA -->
          <div class="det-acciones" id="detAcciones"></div>
        </div>

        <div class="det-feats" id="detFeats"></div>

        <div class="det-seccion" id="secDesc">
          <h2 class="det-seccion-titulo"><i class="fas fa-align-left"></i> Descripción</h2>
          <p class="det-desc" id="detDesc"></p>
        </div>

        <div class="det-seccion">
          <h2 class="det-seccion-titulo"><i class="fas fa-clipboard-list"></i> Ficha técnica</h2>
          <div class="ficha-grid" id="fichaGrid"></div>
        </div>

        <div class="det-seccion" id="secMapa">
          <h2 class="det-seccion-titulo"><i class="fas fa-map-marked-alt"></i> Ubicación en el mapa</h2>
          <div class="mapa-wrap">
            <div id="mapa"></div>
            <div class="mapa-acciones" id="mapaAcciones"></div>
            <div class="mapa-ref" id="mapaRef"></div>
          </div>
        </div>
      </div>

      <!-- SIDEBAR -->
      <aside class="det-sidebar">
        <div class="sidebar-precio-card">
          <div class="sp-precio" id="spPrecio"></div>
          <div class="sp-neg"    id="spNeg"></div>
        </div>

        <div class="contact-card">
          <div class="cc-header">
            <div class="cc-avatar" id="ccAvatar"></div>
            <div>
              <div class="cc-nombre" id="ccNombre"></div>
              <div class="cc-rol"><i class="fas fa-check-circle"></i> Agente verificado</div>
            </div>
          </div>
          <div class="cc-stats" id="ccStats"></div>
          <div class="cc-desc"  id="ccDesc"></div>
          <div class="cc-btns">
            <a href="#" class="cc-btn cc-btn-tel"  id="btnTel">
              <i class="fas fa-phone-alt"></i> Llamar al agente
            </a>
            <a href="#" class="cc-btn cc-btn-wa"   id="btnWa">
              <i class="fab fa-whatsapp"></i> WhatsApp
            </a>
            <a href="#" class="cc-btn cc-btn-mail" id="btnMail">
              <i class="fas fa-envelope"></i> Enviar correo
            </a>
          </div>
          <div class="cc-aviso">
            <i class="fas fa-shield-alt"></i>
            Transacción respaldada por PP Bienes Raíces
          </div>
        </div>

        <div class="vendedor-card">
          <div class="vc-header">
            <div class="vc-avatar" id="vcAvatar"></div>
            <div class="vc-info">
              <div class="vc-nombre" id="vcNombre"></div>
              <div class="vc-badge">
                <i class="fas fa-certificate"></i> Agente verificado
              </div>
            </div>
          </div>
          <div class="vc-stats" id="vcStats"></div>
          <div class="vc-desc"  id="vcDesc"></div>
          <div class="vc-desde" id="vcDesde"></div>
        </div>
      </aside>

    </div>
  </div>
</main>

<div id="errorWrap" style="display:none;" class="error-wrap">
  <div class="error-content">
    <div class="error-icon"><i class="fas fa-home" style="color:var(--border);"></i></div>
    <h2>Propiedad no encontrada</h2>
    <p>Esta propiedad no está disponible o ya fue vendida.</p>
    <a href="index.php" class="btn-volver"><i class="fas fa-arrow-left"></i> Ver otras propiedades</a>
  </div>
</div>

<?php include __DIR__ . '/layouts/footer.php'; ?>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
  const DETALLE_URL = 'controllers/detallecontroller.php';
  const PROP_ID     = new URLSearchParams(window.location.search).get('id') ?? '';
</script>
<script src="assets/js/detalle.js"></script>
</body>
</html>