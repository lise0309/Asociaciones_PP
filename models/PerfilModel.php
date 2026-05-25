<?php
/**
 * MODELO PERFIL — PP Bienes Raíces
 */
require_once __DIR__ . '/../config/database.php';

class PerfilModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    public function getUsuarioById(string $id): ?array {
        $stmt = $this->db->prepare("
            SELECT id, nombre, apellido, correo, telefono,
                   foto_perfil, cuenta_verificada, fecha_registro, rol
            FROM usuarios WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function actualizarPerfil(string $id, array $datos): bool {
        $stmt = $this->db->prepare("
            UPDATE usuarios SET nombre=:nombre, apellido=:apellido, telefono=:telefono
            WHERE id=:id
        ");
        return $stmt->execute([
            ':id'       => $id,
            ':nombre'   => $datos['nombre'],
            ':apellido' => $datos['apellido'],
            ':telefono' => $datos['telefono'],
        ]);
    }

    public function cambiarPassword(string $id, string $nuevaPassword): bool {
        $hash = password_hash($nuevaPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE usuarios SET clave_cifrada=:p WHERE id=:id");
        return $stmt->execute([':id' => $id, ':p' => $hash]);
    }

    public function verificarPassword(string $id, string $passwordActual): bool {
        $stmt = $this->db->prepare("SELECT clave_cifrada FROM usuarios WHERE id=:id");
        $stmt->execute([':id' => $id]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        return $u ? password_verify($passwordActual, $u['clave_cifrada']) : false;
    }

    public function guardarFotoPerfil(string $id, string $ruta): bool {
        $stmt = $this->db->prepare("UPDATE usuarios SET foto_perfil=:f WHERE id=:id");
        return $stmt->execute([':id' => $id, ':f' => $ruta]);
    }

    /**
     * Propiedades vendidas — busca por nombre del estado, no por ID hardcodeado
     */
    public function getPropiedadesVendidas(string $vendedor_id): array {
        $stmt = $this->db->prepare("
            SELECT
                c.id            AS contrato_id,
                c.fecha_generacion,
                c.monto_acordado,
                c.moneda,
                c.nombre_comprador,
                c.correo_comprador,
                c.dui_comprador,
                p.titulo_anuncio,
                p.direccion_exacta,
                p.municipio,
                p.departamento,
                p.num_habitaciones,
                p.num_banos,
                p.metros_construccion,
                p.metros_terreno,
                e.nombre_opcion AS estado
            FROM contratos c
            JOIN propiedades     p ON p.id  = c.propiedad_id
            JOIN opciones_sistema e ON e.id  = c.estado_contrato_id
            WHERE c.vendedor_id = :vid
              AND e.nombre_opcion IN ('Firmado','Aprobado')
              AND e.categoria    = 'estado_contrato'
            ORDER BY c.fecha_generacion DESC
        ");
        $stmt->execute([':vid' => $vendedor_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Resumen de ventas — mismo fix por nombre
     */
    public function getResumenVentas(string $vendedor_id): array {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*)                    AS total_ventas,
                COALESCE(SUM(c.monto_acordado), 0) AS monto_total,
                COUNT(DISTINCT c.propiedad_id)     AS propiedades_vendidas
            FROM contratos c
            JOIN opciones_sistema e ON e.id = c.estado_contrato_id
            WHERE c.vendedor_id = :vid
              AND e.nombre_opcion IN ('Firmado','Aprobado')
              AND e.categoria    = 'estado_contrato'
        ");
        $stmt->execute([':vid' => $vendedor_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total_ventas'        => 0,
            'monto_total'         => 0,
            'propiedades_vendidas'=> 0,
        ];
    }
}