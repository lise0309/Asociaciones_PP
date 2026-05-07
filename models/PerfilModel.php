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

    public function getDocumentosByUsuario(string $usuarioId): array {
        return [
            ['id' => '1', 'nombre' => 'Cédula de identidad', 'tipo' => 'cedula', 'estado' => 'verified'],
            ['id' => '2', 'nombre' => 'Licencia inmobiliaria', 'tipo' => 'licencia', 'estado' => 'verified'],
            ['id' => '3', 'nombre' => 'NIT / Registro tributario', 'tipo' => 'nit', 'estado' => 'expiring']
        ];
    }

    public function guardarFotoPerfil(string $id, string $ruta): bool {
        $sql = "UPDATE usuarios SET foto_perfil = :foto WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id, ':foto' => $ruta]);
    }
}
?>