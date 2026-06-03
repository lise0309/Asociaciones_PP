<?php
/**
 * SIDEBAR — Layout del panel
 * PP Bienes Raíces — views/layouts/sidebar.php
 * Menú lateral con opciones según rol (PHP PURO)
 */

// Incluir la conexión a la base de datos
require_once __DIR__ . '/../../config/database.php';

// Página actual para marcar activo
$pagina = basename($_SERVER['PHP_SELF']);
$rol    = $_SESSION['rol'] ?? '';

// Obtener conexión PDO
$db = Database::conectar();

// Contadores para ADMIN
$totalUsuarios = 0;
$totalPropiedades = 0;
$totalContratos = 0;

// Contadores para VENDEDOR
$totalConsultas = 0;

if ($rol === 'admin') {
    try {
        // Contar usuarios
        $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios");
        $totalUsuarios = $stmt->fetchColumn();
        
        // Contar propiedades
        $stmt = $db->query("SELECT COUNT(*) as total FROM propiedades");
        $totalPropiedades = $stmt->fetchColumn();
        
        // Contar contratos
        $stmt = $db->query("SELECT COUNT(*) as total FROM contratos");
        $totalContratos = $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error en sidebar: " . $e->getMessage());
    }
} else if ($rol === 'vendedor') {
    // Para vendedor, contar sus consultas
    $id_vendedor = $_SESSION['id_usuario'] ?? 0;
    try {
        $stmt = $db->prepare("
            SELECT COUNT(*) as total 
            FROM consultas c
            INNER JOIN propiedades p ON c.id_propiedad = p.id_propiedad
            WHERE p.id_vendedor = ?
        ");
        $stmt->execute([$id_vendedor]);
        $totalConsultas = $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error en sidebar vendedor: " . $e->getMessage());
    }
}
?>

<aside class="sidebar" id="sidebar">
    
    <!-- ── LOGO ── -->
    <div class="sidebar-logo">
        <a href="<?= $rol === 'admin' ? 'dashboardadmin.php' : 'dashboardvendedor.php' ?>">
            <img src="../assets/img/Logo.png" alt="PP Bienes Raíces" class="sidebar-logo-img">
        </a>
    </div>

    <!-- ── PERFIL USUARIO ── -->
    <div class="sidebar-perfil">
        <div class="sidebar-avatar">
            <?= strtoupper(substr($_SESSION['nombre'] ?? 'U', 0, 1) . substr($_SESSION['apellido'] ?? '', 0, 1)) ?>
        </div>
        <div class="sidebar-perfil-info">
            <span class="sidebar-perfil-nombre">
                <?= htmlspecialchars(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? '')) ?>
            </span>
            <span class="sidebar-perfil-rol">
                <?= $rol === 'admin' ? 'Administrador' : 'Vendedor' ?>
            </span>
        </div>
    </div>

    <!-- ── NAVEGACIÓN ── -->
    <nav class="sidebar-nav" aria-label="Menú principal">

        <?php if ($rol === 'admin'): ?>
        <!-- ════ MENÚ ADMINISTRADOR ════ -->

        <div class="sidebar-section-label">Principal</div>

        <a href="dashboardadmin.php" class="sidebar-link <?= $pagina === 'dashboardadmin.php' ? 'active' : '' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.293 2.293a1 1 0 011.414 0l7 7A1 1 0 0117 11h-1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-3a1 1 0 00-1-1H9a1 1 0 00-1 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-6H3a1 1 0 01-.707-1.707l7-7z" clip-rule="evenodd"/>
            </svg>
            Dashboard
        </a>

        <div class="sidebar-section-label">Gestión</div>

        <a href="usuarios.php" class="sidebar-link <?= $pagina === 'usuarios.php' ? 'active' : '' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path d="M7 8a3 3 0 100-6 3 3 0 000 6zM14.5 9a2.5 2.5 0 100-5 2.5 2.5 0 000 5zM1.615 16.428a1.224 1.224 0 01-.569-1.175 6.002 6.002 0 0111.908 0c.058.467-.172.92-.57 1.174A9.953 9.953 0 017 18a9.953 9.953 0 01-5.385-1.572zM14.5 16h-.106c.07-.297.088-.611.048-.933a7.47 7.47 0 00-1.588-3.755 4.502 4.502 0 015.874 2.575c.092.341-.051.703-.345.878A9.969 9.969 0 0114.5 16z"/>
            </svg>
            Usuarios
            <span class="sidebar-badge"><?= $totalUsuarios ?></span>
        </a>

        <a href="propiedades.php" class="sidebar-link <?= $pagina === 'propiedades.php' ? 'active' : '' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M2 10a8 8 0 1116 0 8 8 0 01-16 0zm9 3a1 1 0 11-2 0 1 1 0 012 0zm-1-5a1 1 0 00-1 1v2a1 1 0 102 0V9a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            Propiedades
            <span class="sidebar-badge"><?= $totalPropiedades ?></span>
        </a>

        <a href="contratosadmin.php" class="sidebar-link <?= $pagina === 'contratosadmin.php' ? 'active' : '' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
            </svg>
            Contratos
            <span class="sidebar-badge sidebar-badge-gold"><?= $totalContratos ?></span>
        </a>

        <a href="plantillas.php" class="sidebar-link <?= $pagina === 'plantillas.php' ? 'active' : '' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path d="M3 3.5A1.5 1.5 0 014.5 2h6.879a1.5 1.5 0 011.06.44l4.122 4.12A1.5 1.5 0 0117 7.622V16.5a1.5 1.5 0 01-1.5 1.5h-11A1.5 1.5 0 013 16.5v-13z"/>
            </svg>
            Plantillas
        </a>

        <div class="sidebar-section-label">Reportes</div>

        <a href="reportes.php" class="sidebar-link <?= $pagina === 'reportes.php' ? 'active' : '' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path d="M15.5 2A1.5 1.5 0 0014 3.5v13a1.5 1.5 0 001.5 1.5h1a1.5 1.5 0 001.5-1.5v-13A1.5 1.5 0 0016.5 2h-1zM9.5 6A1.5 1.5 0 008 7.5v9A1.5 1.5 0 009.5 18h1a1.5 1.5 0 001.5-1.5v-9A1.5 1.5 0 0010.5 6h-1zM3.5 10A1.5 1.5 0 002 11.5v5A1.5 1.5 0 003.5 18h1A1.5 1.5 0 006 16.5v-5A1.5 1.5 0 004.5 10h-1z"/>
            </svg>
            Reportes
        </a>

        <a href="bitacora.php" class="sidebar-link <?= $pagina === 'bitacora.php' ? 'active' : '' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10zm0 5.25a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75z" clip-rule="evenodd"/>
            </svg>
            Bitácora
        </a>

  <!--       <div class="sidebar-section-label">Sistema</div>

        <a href="configuracion.php" class="sidebar-link <?= $pagina === 'configuracion.php' ? 'active' : '' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M7.84 1.804A1 1 0 018.82 1h2.36a1 1 0 01.98.804l.331 1.652a6.993 6.993 0 011.929 1.115l1.598-.54a1 1 0 011.186.447l1.18 2.044a1 1 0 01-.205 1.251l-1.267 1.113a7.047 7.047 0 010 2.228l1.267 1.113a1 1 0 01.206 1.25l-1.18 2.045a1 1 0 01-1.187.447l-1.598-.54a6.993 6.993 0 01-1.929 1.115l-.33 1.652a1 1 0 01-.98.804H8.82a1 1 0 01-.98-.804l-.331-1.652a6.993 6.993 0 01-1.929-1.115l-1.598.54a1 1 0 01-1.186-.447l-1.18-2.044a1 1 0 01.205-1.251l1.267-1.114a7.05 7.05 0 010-2.227L1.821 7.773a1 1 0 01-.206-1.25l1.18-2.045a1 1 0 011.187-.447l1.598.54A6.993 6.993 0 017.51 3.456l.33-1.652zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
            </svg>
            Configuración
        </a> -->

        <?php else: ?>
        <!-- ════ MENÚ VENDEDOR ════ -->

        <div class="sidebar-section-label">Principal</div>

        <a href="dashboardvendedor.php" class="sidebar-link <?= $pagina === 'dashboardvendedor.php' ? 'active' : '' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.293 2.293a1 1 0 011.414 0l7 7A1 1 0 0117 11h-1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-3a1 1 0 00-1-1H9a1 1 0 00-1 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-6H3a1 1 0 01-.707-1.707l7-7z" clip-rule="evenodd"/>
            </svg>
            Dashboard
        </a>

        <div class="sidebar-section-label">Mis publicaciones</div>

        <a href="Mis_Propiedades.php" class="sidebar-link <?= $pagina === 'Mis_Propiedades.php' ? 'active' : '' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.293 2.293a1 1 0 011.414 0l7 7A1 1 0 0117 11h-1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-3a1 1 0 00-1-1H9a1 1 0 00-1 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-6H3a1 1 0 01-.707-1.707l7-7z" clip-rule="evenodd"/>
            </svg>
            Mis propiedades
        </a>

        <a href="propiedad_form.php" class="sidebar-link <?= $pagina === 'propiedad_form.php' ? 'active' : '' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z"/>
            </svg>
            Nueva propiedad
        </a>

       <!--  <<div class="sidebar-section-label">Clientes</div>

       <a href="consultas.php" class="sidebar-link <?= $pagina === 'consultas.php' ? 'active' : '' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 2c-2.236 0-4.43.18-6.57.524C1.993 2.755 1 4.014 1 5.426v5.148c0 1.413.993 2.67 2.43 2.902.848.137 1.705.248 2.57.331v3.443a.75.75 0 001.28.53l3.58-3.579a.78.78 0 01.527-.224 41.202 41.202 0 005.183-.5c1.437-.232 2.43-1.49 2.43-2.903V5.426c0-1.413-.993-2.67-2.43-2.902A41.289 41.289 0 0010 2zm0 7a1 1 0 100-2 1 1 0 000 2zM6 9a1 1 0 11-2 0 1 1 0 012 0zm5 1a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
            </svg>
            Consultas
            <span class="sidebar-badge sidebar-badge-gold"><?= $totalConsultas ?></span>
        </a> -->
        
        <div class="sidebar-section-label">Documentos</div>

        <a href="contratos.php" class="sidebar-link <?= $pagina === 'contratos.php' ? 'active' : '' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
            </svg>
            Contratos
        </a>

<!--         <div class="sidebar-section-label">Mi cuenta</div>

        <a href="perfil.php" class="sidebar-link <?= $pagina === 'perfil.php' ? 'active' : '' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 8a3 3 0 100-6 3 3 0 000 6zM3.465 14.493a1.23 1.23 0 00.41 1.412A9.957 9.957 0 0010 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 00-13.074.003z"/>
            </svg>
            Mi perfil
        </a> -->

        <?php endif; ?>

    </nav>

    <!-- ── CERRAR SESIÓN ── -->
    <div class="sidebar-footer">
        <a href="logout.php" class="sidebar-logout">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 015.25 2h5.5A2.25 2.25 0 0113 4.25v2a.75.75 0 01-1.5 0v-2a.75.75 0 00-.75-.75h-5.5a.75.75 0 00-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 00.75-.75v-2a.75.75 0 011.5 0v2A2.25 2.25 0 0110.75 18h-5.5A2.25 2.25 0 013 15.75V4.25z" clip-rule="evenodd"/>
                <path fill-rule="evenodd" d="M6 10a.75.75 0 01.75-.75h9.546l-1.048-.943a.75.75 0 111.004-1.114l2.5 2.25a.75.75 0 010 1.114l-2.5 2.25a.75.75 0 11-1.004-1.114l1.048-.943H6.75A.75.75 0 016 10z" clip-rule="evenodd"/>
            </svg>
            Cerrar sesión
        </a>
    </div>

</aside>