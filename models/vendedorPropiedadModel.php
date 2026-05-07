<?php
/**
 * MODELO PROPIEDAD PARA VENDEDOR
 * PP Bienes Raíces
 */

require_once __DIR__ . '/../config/database.php';

class VendedorPropiedadModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    private function generarUUID(): string {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    public function crear(array $datos): string|false {
        try {
            $id = $this->generarUUID();
            
            $sql = "INSERT INTO propiedades (
                id, vendedor_id, tipo_inmueble_id, tipo_negocio_id, 
                estado_publicacion_id, titulo_anuncio, descripcion_detallada,
                precio_pedido, num_habitaciones, num_banos, 
                metros_construccion, metros_terreno, tiene_estacionamiento,
                tiene_piscina, viene_amueblado, es_anuncio_destacado,
                departamento, municipio, direccion_exacta, punto_referencia,
                latitud, longitud
            ) VALUES (
                :id, :vendedor_id, :tipo_inmueble_id, :tipo_negocio_id,
                :estado_publicacion_id, :titulo_anuncio, :descripcion_detallada,
                :precio_pedido, :num_habitaciones, :num_banos,
                :metros_construccion, :metros_terreno, :tiene_estacionamiento,
                :tiene_piscina, :viene_amueblado, :es_anuncio_destacado,
                :departamento, :municipio, :direccion_exacta, :punto_referencia,
                :latitud, :longitud
            )";
            
            $stmt = $this->db->prepare($sql);
            
            $resultado = $stmt->execute([
                ':id' => $id,
                ':vendedor_id' => $datos['vendedor_id'],
                ':tipo_inmueble_id' => $datos['tipo_inmueble_id'],
                ':tipo_negocio_id' => $datos['tipo_negocio_id'],
                ':estado_publicacion_id' => $datos['estado_publicacion_id'] ?? 10,
                ':titulo_anuncio' => $datos['titulo'],
                ':descripcion_detallada' => $datos['descripcion'],
                ':precio_pedido' => $datos['precio'],
                ':num_habitaciones' => $datos['habitaciones'],
                ':num_banos' => $datos['banos'],
                ':metros_construccion' => $datos['metros_construccion'],
                ':metros_terreno' => $datos['metros_terreno'],
                ':tiene_estacionamiento' => $datos['estacionamiento'],
                ':tiene_piscina' => $datos['piscina'],
                ':viene_amueblado' => $datos['amueblado'],
                ':es_anuncio_destacado' => $datos['destacar'],
                ':departamento' => $datos['departamento'],
                ':municipio' => $datos['municipio'],
                ':direccion_exacta' => $datos['direccion'],
                ':punto_referencia' => $datos['referencia'],
                ':latitud' => $datos['latitud'],
                ':longitud' => $datos['longitud']
            ]);
            
            return $resultado ? $id : false;
        } catch (PDOException $e) {
            error_log("Error al crear propiedad: " . $e->getMessage());
            return false;
        }
    }

    public function actualizar(string $id, array $datos): bool {
        try {
            $sql = "UPDATE propiedades SET 
                tipo_inmueble_id = :tipo_inmueble_id,
                tipo_negocio_id = :tipo_negocio_id,
                titulo_anuncio = :titulo_anuncio,
                descripcion_detallada = :descripcion_detallada,
                precio_pedido = :precio_pedido,
                num_habitaciones = :num_habitaciones,
                num_banos = :num_banos,
                metros_construccion = :metros_construccion,
                metros_terreno = :metros_terreno,
                tiene_estacionamiento = :tiene_estacionamiento,
                tiene_piscina = :tiene_piscina,
                viene_amueblado = :viene_amueblado,
                es_anuncio_destacado = :es_anuncio_destacado,
                departamento = :departamento,
                municipio = :municipio,
                direccion_exacta = :direccion_exacta,
                punto_referencia = :punto_referencia,
                latitud = :latitud,
                longitud = :longitud
                WHERE id = :id AND vendedor_id = :vendedor_id";
            
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                ':id' => $id,
                ':vendedor_id' => $datos['vendedor_id'],
                ':tipo_inmueble_id' => $datos['tipo_inmueble_id'],
                ':tipo_negocio_id' => $datos['tipo_negocio_id'],
                ':titulo_anuncio' => $datos['titulo'],
                ':descripcion_detallada' => $datos['descripcion'],
                ':precio_pedido' => $datos['precio'],
                ':num_habitaciones' => $datos['habitaciones'],
                ':num_banos' => $datos['banos'],
                ':metros_construccion' => $datos['metros_construccion'],
                ':metros_terreno' => $datos['metros_terreno'],
                ':tiene_estacionamiento' => $datos['estacionamiento'],
                ':tiene_piscina' => $datos['piscina'],
                ':viene_amueblado' => $datos['amueblado'],
                ':es_anuncio_destacado' => $datos['destacar'],
                ':departamento' => $datos['departamento'],
                ':municipio' => $datos['municipio'],
                ':direccion_exacta' => $datos['direccion'],
                ':punto_referencia' => $datos['referencia'],
                ':latitud' => $datos['latitud'],
                ':longitud' => $datos['longitud']
            ]);
        } catch (PDOException $e) {
            error_log("Error al actualizar propiedad: " . $e->getMessage());
            return false;
        }
    }

    public function getById(string $id, string $vendedorId): ?array {
        $sql = "SELECT p.*, 
                       ti.nombre_opcion as tipo_inmueble_nombre,
                       tn.nombre_opcion as tipo_negocio_nombre,
                       ep.nombre_opcion as estado_nombre
                FROM propiedades p
                LEFT JOIN opciones_sistema ti ON p.tipo_inmueble_id = ti.id
                LEFT JOIN opciones_sistema tn ON p.tipo_negocio_id = tn.id
                LEFT JOIN opciones_sistema ep ON p.estado_publicacion_id = ep.id
                WHERE p.id = :id AND p.vendedor_id = :vendedor_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id, ':vendedor_id' => $vendedorId]);
        return $stmt->fetch() ?: null;
    }

    public function getFotos(string $propiedadId): array {
        $sql = "SELECT * FROM fotos_propiedad WHERE propiedad_id = :propiedad_id ORDER BY numero_orden ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':propiedad_id' => $propiedadId]);
        return $stmt->fetchAll();
    }

    public function guardarFoto(string $propiedadId, string $urlOriginal, string $urlMiniatura, int $orden = 0, bool $esPortada = false): bool {
        try {
            $sql = "INSERT INTO fotos_propiedad (id, propiedad_id, url_foto_original, url_foto_miniatura, numero_orden, es_foto_portada)
                    VALUES (:id, :propiedad_id, :url_original, :url_miniatura, :orden, :portada)";
            
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                ':id' => $this->generarUUID(),
                ':propiedad_id' => $propiedadId,
                ':url_original' => $urlOriginal,
                ':url_miniatura' => $urlMiniatura,
                ':orden' => $orden,
                ':portada' => $esPortada ? 1 : 0
            ]);
        } catch (PDOException $e) {
            error_log("Error al guardar foto: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarFoto(string $fotoId, string $propiedadId): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM fotos_propiedad WHERE id = :id AND propiedad_id = :propiedad_id");
            return $stmt->execute([':id' => $fotoId, ':propiedad_id' => $propiedadId]);
        } catch (PDOException $e) {
            error_log("Error al eliminar foto: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarFotos(string $propiedadId): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM fotos_propiedad WHERE propiedad_id = :id");
            return $stmt->execute([':id' => $propiedadId]);
        } catch (PDOException $e) {
            error_log("Error al eliminar fotos: " . $e->getMessage());
            return false;
        }
    }
}
?>