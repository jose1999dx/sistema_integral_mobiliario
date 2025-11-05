<?php
// SISTEMA_INTEGRAL_MOBILIARIO/app/models/Presupuesto/PresupuestoModel.php

namespace Model\Presupuesto; // Nuevo Namespace

use Config\Database;

class PresupuestoModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection(); 
    }

    public function getProyectos() {
        $sql = "SELECT id, nombre FROM proyectos ORDER BY nombre";
        $result = mysqli_query($this->db, $sql);
        $proyectos = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $proyectos[] = $row;
            }
            mysqli_free_result($result);
        }
        return $proyectos;
    }

    public function crearPresupuestoConItems($proyecto_id, $nombre, $items) {
        // Usa Transacciones para asegurar la integridad
        mysqli_begin_transaction($this->db);
        
        try {
            // 1. Insertar Presupuesto principal
            $sql_presupuesto = "INSERT INTO presupuestos (proyecto_id, nombre, monto_total) VALUES (?, ?, 0)";
            $stmt_presupuesto = mysqli_prepare($this->db, $sql_presupuesto);
            
            if (!$stmt_presupuesto) throw new \Exception("Error preparando presupuesto: " . mysqli_error($this->db));
            
            mysqli_stmt_bind_param($stmt_presupuesto, "is", $proyecto_id, $nombre);
            mysqli_stmt_execute($stmt_presupuesto);
            $presupuesto_id = mysqli_insert_id($this->db);
            mysqli_stmt_close($stmt_presupuesto);

            $monto_total = 0;
            
            // 2. Insertar Ítems y calcular monto total
            $sql_item = "INSERT INTO items_presupuesto (presupuesto_id, descripcion, tipo, monto) VALUES (?, ?, ?, ?)";
            $stmt_item = mysqli_prepare($this->db, $sql_item);

            foreach ($items as $item) {
                $desc = trim($item['descripcion']);
                $tipo = trim($item['tipo']);
                $monto = (float)$item['monto'];
                
                if (empty($desc) || !in_array($tipo, ['Directo', 'Indirecto']) || $monto <= 0) continue; 
                if (!$stmt_item) throw new \Exception("Error preparando ítem: " . mysqli_error($this->db));
                
                mysqli_stmt_bind_param($stmt_item, "issd", $presupuesto_id, $desc, $tipo, $monto);
                mysqli_stmt_execute($stmt_item);
                
                $monto_total += $monto;
            }
            mysqli_stmt_close($stmt_item);

            // 3. Actualizar el Monto Total
            $sql_update = "UPDATE presupuestos SET monto_total = ? WHERE id = ?";
            $stmt_update = mysqli_prepare($this->db, $sql_update);
            mysqli_stmt_bind_param($stmt_update, "di", $monto_total, $presupuesto_id);
            mysqli_stmt_execute($stmt_update);
            mysqli_stmt_close($stmt_update);
            
            mysqli_commit($this->db);
            return $presupuesto_id;

        } catch (\Exception $e) {
            mysqli_rollback($this->db);
            error_log("Error al crear presupuesto: " . $e->getMessage()); 
            return false;
        }
    }

    public function getPresupuestosList() {
        $sql = "SELECT 
                    p.id, p.nombre, p.monto_total, p.estado, p.fecha_creacion, 
                    proy.nombre AS nombre_proyecto 
                FROM presupuestos p
                JOIN proyectos proy ON p.proyecto_id = proy.id
                ORDER BY p.fecha_creacion DESC";
                
        $result = mysqli_query($this->db, $sql);
        $presupuestos = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $presupuestos[] = $row;
            }
            mysqli_free_result($result);
        }
        return $presupuestos;
    }
}