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
  <link rel="stylesheet" href="assets/css/navbar.css">
  <link rel="stylesheet" href="assets/css/detalle.css">
  <link rel="stylesheet" href="assets/css/footer.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
</head>
<body>

<?php include __DIR__ . '/layouts/navbar.php'; ?>

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

      <div class="det-main">
        <div class="det-header">
          <div class="det-tags"      id="detTags"></div>
          <h1 class="det-titulo"    id="detTitulo"></h1>
          <div class="det-ubicacion">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd"/></svg>
            <span id="ubicacionTexto"></span>
          </div>
          <div class="det-precio" id="detPrecio"></div>
        </div>

        <div class="det-feats" id="detFeats"></div>

        <div class="det-seccion" id="secDesc">
          <h2 class="det-seccion-titulo">Descripción</h2>
          <p class="det-desc" id="detDesc"></p>
        </div>

        <div class="det-seccion">
          <h2 class="det-seccion-titulo">Ficha técnica</h2>
          <div class="ficha-grid" id="fichaGrid"></div>
        </div>

        <div class="det-seccion" id="secMapa">
          <h2 class="det-seccion-titulo">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd"/></svg>
            Ubicación en el mapa
          </h2>
          <div class="mapa-wrap">
            <div id="mapa"></div>
            <div class="mapa-ref" id="mapaRef"></div>
          </div>
        </div>
      </div>

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
              <div class="cc-rol">Agente verificado</div>
            </div>
          </div>
          <div class="cc-stats" id="ccStats"></div>
          <div class="cc-desc"  id="ccDesc"></div>
          <div class="cc-btns">
            <a href="#" class="cc-btn cc-btn-tel"  id="btnTel">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 3.5A1.5 1.5 0 013.5 2h1.148a1.5 1.5 0 011.465 1.175l.716 3.223a1.5 1.5 0 01-1.052 1.767l-.933.267c-.41.117-.643.555-.48.95a11.542 11.542 0 006.254 6.254c.395.163.833-.07.95-.48l.267-.933a1.5 1.5 0 011.767-1.052l3.223.716A1.5 1.5 0 0118 15.352V16.5a1.5 1.5 0 01-1.5 1.5H15c-1.149 0-2.263-.15-3.326-.43A13.022 13.022 0 012.43 8.326 13.019 13.019 0 012 5V3.5z" clip-rule="evenodd"/></svg>
              Llamar al agente
            </a>
            <a href="#" class="cc-btn cc-btn-wa"   id="btnWa">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
              WhatsApp
            </a>
            <a href="#" class="cc-btn cc-btn-mail" id="btnMail">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M3 4a2 2 0 00-2 2v1.161l8.441 4.221a1.25 1.25 0 001.118 0L19 7.162V6a2 2 0 00-2-2H3z"/><path d="M19 8.839l-7.77 3.885a2.75 2.75 0 01-2.46 0L1 8.839V14a2 2 0 002 2h14a2 2 0 002-2V8.839z"/></svg>
              Enviar correo
            </a>
          </div>
          <div class="cc-aviso">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.661 2.237a.531.531 0 01.678 0 11.947 11.947 0 007.078 2.749.5.5 0 01.479.425c.069.52.104 1.05.104 1.589 0 5.162-3.26 9.563-7.834 11.256a.48.48 0 01-.332 0C5.26 16.563 2 12.162 2 7c0-.538.035-1.069.104-1.589a.5.5 0 01.48-.425 11.947 11.947 0 007.077-2.749z" clip-rule="evenodd"/></svg>
            Transacción respaldada por PP Bienes Raíces
          </div>
        </div>

        <div class="vendedor-card">
          <div class="vc-header">
            <div class="vc-avatar" id="vcAvatar"></div>
            <div class="vc-info">
              <div class="vc-nombre" id="vcNombre"></div>
              <div class="vc-badge">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.403 12.652a3 3 0 000-5.304 3 3 0 00-3.75-3.751 3 3 0 00-5.305 0 3 3 0 00-3.751 3.75 3 3 0 000 5.305 3 3 0 003.75 3.751 3 3 0 005.305 0 3 3 0 003.751-3.75zm-2.546-4.46a.75.75 0 00-1.214-.883l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                Agente verificado
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
    <div class="error-icon">🏚️</div>
    <h2>Propiedad no encontrada</h2>
    <p>Esta propiedad no está disponible o ya fue vendida.</p>
    <a href="index.php" class="btn-volver">Ver otras propiedades</a>
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