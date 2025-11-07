<?php
// SISTEMA_INTEGRAL_MOBILIARIO/app/models/Presupuesto/PresupuestoModel.php

// Asegúrate de que esta clase extiende correctamente tu clase base Model
class PresupuestoModel extends Model { 

    protected $table = 'presupuestos';
    
    // =========================================================================
    // CRUD BÁSICO Y CARGA DE DATOS
    // =========================================================================

    /**
     * Obtiene una lista de presupuestos con el nombre del proyecto asociado.
     */
    public function getPresupuestosList() {
        $sql = "
            SELECT 
                p.id, 
                p.nombre, 
                p.monto_total, 
                p.fecha_creacion,
                p.estado,
                pr.nombre AS nombre_proyecto 
            FROM {$this->table} p
            JOIN proyectos pr ON p.proyecto_id = pr.id
            ORDER BY p.fecha_creacion DESC
        ";
        
        try {
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener presupuestos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene todos los detalles de un presupuesto específico, incluyendo ítems.
     * Incluye el gasto real total para mostrar en el detalle.
     */
    public function getPresupuestoDetalle(int $id): ?array {
        // 1. Obtener datos del Presupuesto principal, Proyecto y total gastado
        $sqlPresupuesto = "
            SELECT 
                p.*, 
                pr.nombre AS nombre_proyecto,
                COALESCE((SELECT SUM(monto) FROM gastos_reales WHERE presupuesto_id = p.id), 0.00) AS gastado
            FROM 
                {$this->table} p
            INNER JOIN 
                proyectos pr ON pr.id = p.proyecto_id
            WHERE 
                p.id = :id
            LIMIT 1;
        ";
        
        try {
            $stmt = $this->db->prepare($sqlPresupuesto);
            $stmt->execute(['id' => $id]);
            $presupuesto = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener encabezado del presupuesto {$id}: " . $e->getMessage());
            return null;
        }

        if (!$presupuesto) {
            return null; 
        }
        
        // 2. Obtener los Ítems asociados a ese Presupuesto
        $sqlItems = "
            SELECT 
                * FROM 
                items_presupuesto
            WHERE 
                presupuesto_id = :presupuesto_id
            ORDER BY 
                monto DESC;
        ";
        
        try {
            $stmtItems = $this->db->prepare($sqlItems);
            $stmtItems->execute(['presupuesto_id' => $id]);
            $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener ítems del presupuesto {$id}: " . $e->getMessage());
            $items = []; 
        }

        $presupuesto['items'] = $items;
        
        return $presupuesto;
    }

    /**
     * Obtiene todos los proyectos para el dropdown.
     */
    public function getProyectos() {
        $sql = "SELECT id, nombre FROM proyectos ORDER BY nombre ASC";
        try {
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener proyectos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Actualiza el estado (Aprobado, Rechazado, Pendiente) de un presupuesto.
     */
    public function actualizarEstado(int $id, string $estado): bool {
        $sql = "
            UPDATE {$this->table} 
            SET estado = :estado 
            WHERE id = :id
        ";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':estado' => $estado,
                ':id' => $id
            ]);
        } catch (\PDOException $e) {
            error_log("Error al actualizar estado del presupuesto {$id}: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // CREACIÓN Y ACTUALIZACIÓN (TRANSACCIONAL)
    // =========================================================================

    /**
     * Crea un nuevo presupuesto y sus ítems asociados dentro de una transacción.
     * @return int|bool ID del nuevo presupuesto o false en caso de fallo.
     */
    public function crearPresupuestoConItems(int $proyecto_id, string $nombre, array $items, ?string $descripcion = null) {
        $this->db->beginTransaction();

        try {
            // 1. Calcular Monto Total
            $monto_total = array_sum(array_column($items, 'monto'));
            
            // 2. Insertar Presupuesto
            $sql_presupuesto = "
                INSERT INTO {$this->table} (proyecto_id, nombre, descripcion, monto_total, estado, fecha_creacion)
                VALUES (:proyecto_id, :nombre, :descripcion, :monto_total, 'Pendiente', NOW())
            ";
            
            $stmt = $this->db->prepare($sql_presupuesto);
            $stmt->execute([
                ':proyecto_id' => $proyecto_id,
                ':nombre' => $nombre,
                ':descripcion' => $descripcion, 
                ':monto_total' => $monto_total
            ]);
            
            $presupuesto_id = $this->db->lastInsertId();

            if (!$presupuesto_id) {
                throw new Exception("No se pudo obtener el ID del presupuesto insertado.");
            }

            // 3. Insertar Items
            $sql_item = "
                INSERT INTO items_presupuesto (presupuesto_id, descripcion, tipo, monto)
                VALUES (:presupuesto_id, :descripcion, :tipo, :monto)
            ";
            
            foreach ($items as $item) {
                $stmt_item = $this->db->prepare($sql_item);
                $stmt_item->execute([
                    ':presupuesto_id' => $presupuesto_id,
                    ':descripcion' => $item['descripcion'],
                    ':tipo' => $item['tipo'],
                    ':monto' => $item['monto']
                ]);
            }

            // 4. Confirmar Transacción
            $this->db->commit();
            return (int)$presupuesto_id;

        } catch (Exception $e) {
            // 5. Rollback
            $this->db->rollBack();
            error_log("Error en transacción de presupuesto (creación): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza un presupuesto existente y sus ítems asociados dentro de una transacción.
     * Elimina ítems antiguos y los reemplaza por los nuevos (Simplificación).
     */
    public function actualizarPresupuestoConItems(int $presupuesto_id, int $proyecto_id, string $nombre, array $items, ?string $descripcion = null): bool {
        if ($presupuesto_id <= 0 || empty($items)) {
            return false;
        }

        $this->db->beginTransaction();

        try {
            // 1. Calcular Monto Total
            $monto_total = array_sum(array_column($items, 'monto'));
            
            // 2. Actualizar Presupuesto principal
            $sql_update = "
                UPDATE {$this->table} 
                SET proyecto_id = :proyecto_id, nombre = :nombre, descripcion = :descripcion, monto_total = :monto_total
                WHERE id = :id
            ";
            $stmt_update = $this->db->prepare($sql_update);
            $stmt_update->execute([
                ':proyecto_id' => $proyecto_id,
                ':nombre' => $nombre,
                ':descripcion' => $descripcion,
                ':monto_total' => $monto_total,
                ':id' => $presupuesto_id
            ]);
            
            // 3. Eliminar Items antiguos
            $sql_delete_items = "DELETE FROM items_presupuesto WHERE presupuesto_id = :presupuesto_id";
            $stmt_delete = $this->db->prepare($sql_delete_items);
            $stmt_delete->execute([':presupuesto_id' => $presupuesto_id]);

            // 4. Insertar Items nuevos
            $sql_item = "
                INSERT INTO items_presupuesto (presupuesto_id, descripcion, tipo, monto)
                VALUES (:presupuesto_id, :descripcion, :tipo, :monto)
            ";
            
            foreach ($items as $item) {
                $stmt_item = $this->db->prepare($sql_item);
                $stmt_item->execute([
                    ':presupuesto_id' => $presupuesto_id,
                    ':descripcion' => $item['descripcion'],
                    ':tipo' => $item['tipo'],
                    ':monto' => $item['monto']
                ]);
            }

            // 5. Commit
            $this->db->commit();
            return true;

        } catch (Exception $e) {
            // 6. Rollback
            $this->db->rollBack();
            error_log("Error en transacción de actualización de presupuesto: " . $e->getMessage());
            return false;
        }
    }


    // =========================================================================
    // GASTOS REALES
    // =========================================================================

    /**
     * Registra un gasto real y lo asocia a un presupuesto y, opcionalmente, a un ítem.
     */
    public function registrarGastoReal(array $datos_gasto): bool {
    $sql = "
        INSERT INTO gastos_reales 
        (presupuesto_id, descripcion, monto, fecha_gasto)  
        VALUES (:presupuesto_id, :descripcion, :monto, :fecha_gasto)  
    ";
    
    try {
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':presupuesto_id' => $datos_gasto['presupuesto_id'],
            ':descripcion' => $datos_gasto['descripcion'],
            ':monto' => $datos_gasto['monto'],
            ':fecha_gasto' => $datos_gasto['fecha']  // ← fecha se mapea a fecha_gasto
        ]);

    } catch (\PDOException $e) {
        error_log("Error al registrar gasto real: " . $e->getMessage());
        return false;
    }
}
}