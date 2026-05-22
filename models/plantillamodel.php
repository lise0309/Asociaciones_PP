<?php
/**
 * PLANTILLA MODEL — PP Bienes Raíces
 */
require_once __DIR__ . '/../config/database.php';

class PlantillaModel {
    private PDO $db;
    public function __construct() { $this->db = Database::conectar(); }

    private function uuid(): string {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff),
            mt_rand(0,0x0fff)|0x4000,mt_rand(0,0x3fff)|0x8000,
            mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff));
    }

    public function listar(): array {
        return $this->db->query("SELECT * FROM plantillas_contrato ORDER BY fecha_creacion DESC")->fetchAll();
    }

    public function listarActivas(): array {
        return $this->db->query("SELECT * FROM plantillas_contrato WHERE plantilla_activa=1 ORDER BY nombre_plantilla ASC")->fetchAll();
    }

    public function getById(string $id): ?array {
        $stmt=$this->db->prepare("SELECT * FROM plantillas_contrato WHERE id=:id LIMIT 1");
        $stmt->execute([':id'=>$id]);
        return $stmt->fetch()?:null;
    }

    public function crear(string $nombre, string $archivo): string|false {
        try {
            $id=$this->uuid();
            $ok=$this->db->prepare("INSERT INTO plantillas_contrato(id,nombre_plantilla,archivo_plantilla,plantilla_activa,fecha_creacion) VALUES(:id,:n,:a,1,NOW())")->execute([':id'=>$id,':n'=>$nombre,':a'=>$archivo]);
            return $ok ? $id : false;
        } catch(PDOException $e){ error_log($e->getMessage()); return false; }
    }

    public function toggleActiva(string $id): bool {
        return $this->db->prepare("UPDATE plantillas_contrato SET plantilla_activa = NOT plantilla_activa WHERE id=:id")->execute([':id'=>$id]);
    }

    public function eliminar(string $id): bool {
        return $this->db->prepare("DELETE FROM plantillas_contrato WHERE id=:id")->execute([':id'=>$id]);
    }
}