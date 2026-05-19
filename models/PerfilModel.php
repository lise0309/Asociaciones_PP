<?php
/**
 * MODELO PERFIL
 * PP Bienes Raíces
 */

require_once __DIR__ . '/../config/database.php';

class PerfilModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    public function getUsuarioById(string $id): ?array {
        $sql = "SELECT id, nombre, apellido, correo, telefono, descripcion_personal, 
                       foto_perfil, cuenta_verificada, fecha_registro, rol
                FROM usuarios 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function actualizarPerfil(string $id, array $datos): bool {
        $sql = "UPDATE usuarios SET 
                    nombre = :nombre,
                    apellido = :apellido,
                    telefono = :telefono
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':nombre' => $datos['nombre'],
            ':apellido' => $datos['apellido'],
            ':telefono' => $datos['telefono']
        ]);
    }

    public function cambiarPassword(string $id, string $nuevaPassword): bool {
        $passwordHash = password_hash($nuevaPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET clave_cifrada = :password WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id, ':password' => $passwordHash]);
    }

    public function verificarPassword(string $id, string $passwordActual): bool {
        $sql = "SELECT clave_cifrada FROM usuarios WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) return false;
        return password_verify($passwordActual, $usuario['clave_cifrada']);
    }
    /**
 * Obtener propiedades vendidas por el vendedor (contratos firmados)
 */
public function getPropiedadesVendidas(string $vendedor_id): array {
    $sql = "SELECT 
                c.id as contrato_id,
                c.fecha_generacion,
                c.monto_acordado,
                c.moneda,
                p.titulo_anuncio,
                p.direccion_exacta,
                p.municipio,
                p.departamento,
                p.num_habitaciones,
                p.num_banos,
                p.metros_construccion,
                p.metros_terreno,
                c.nombre_comprador,
                c.correo_comprador,
                c.dui_comprador,
                e.nombre_opcion as estado
            FROM contratos c
            JOIN propiedades p ON c.propiedad_id = p.id
            JOIN opciones_sistema e ON c.estado_contrato_id = e.id
            WHERE c.vendedor_id = ? 
            AND c.estado_contrato_id IN (18, 19)
            ORDER BY c.fecha_generacion DESC";
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$vendedor_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtener resumen de ventas (totales)
 */
public function getResumenVentas(string $vendedor_id): array {
    $sql = "SELECT 
                COUNT(*) as total_ventas,
                SUM(c.monto_acordado) as monto_total,
                COUNT(DISTINCT c.propiedad_id) as propiedades_vendidas
            FROM contratos c
            WHERE c.vendedor_id = ? 
            AND c.estado_contrato_id IN (18, 19)";
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$vendedor_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total_ventas' => 0, 'monto_total' => 0, 'propiedades_vendidas' => 0];
}

    public function getDocumentosByUsuario(string $usuarioId): array {
        return [
            ['id' => '1', 'nombre' => 'Cédula de identidad', 'tipo' => 'cedula', 'estado' => 'verified'],
            ['id' => '2', 'nombre' => 'Licencia inmobiliaria', 'tipo' => 'licencia', 'estado' => 'verified'],
            ['id' => '3', 'nombre' => 'NIT / Registro tributario', 'tipo' => 'nit', 'estado' => 'expiring']
        ];
    }
    public function guardarFotoPerfil($usuarioId, $rutaFoto) {
    try {
        $sql = "UPDATE usuarios SET foto_perfil = :foto_perfil WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':foto_perfil' => $rutaFoto,
            ':id' => $usuarioId
        ]);
    } catch (PDOException $e) {
        error_log("Error guardar foto perfil: " . $e->getMessage());
        return false;
    }
    
}

    
}
?>