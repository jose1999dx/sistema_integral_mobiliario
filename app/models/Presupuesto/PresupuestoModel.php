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

/**
 * Analiza desviaciones del presupuesto
 */
public function analizarDesviaciones(int $presupuesto_id): array {
    $presupuesto = $this->getPresupuestoDetalle($presupuesto_id);
    
    if (!$presupuesto) {
        return ['error' => 'Presupuesto no encontrado'];
    }
    
    $monto_total = $presupuesto['monto_total'];
    $gastado = $presupuesto['gastado'];
    
    // Cálculos
    $porcentaje_gastado = $monto_total > 0 ? ($gastado / $monto_total) * 100 : 0;
    $diferencia = $monto_total - $gastado;
    
    // Determinar nivel de alerta
    $nivel_alerta = 'success'; // Por defecto
    
    if ($porcentaje_gastado > 110) {
        $nivel_alerta = 'critical';
        $mensaje = "🚨 CRÍTICO: Excedido en " . number_format($porcentaje_gastado - 100, 1) . "%";
    } elseif ($porcentaje_gastado > 95) {
        $nivel_alerta = 'danger';
        $mensaje = "🔴 PELIGRO: Cerca del límite (" . number_format($porcentaje_gastado, 1) . "%)";
    } elseif ($porcentaje_gastado > 80) {
        $nivel_alerta = 'warning';
        $mensaje = "🟡 PRECAUCIÓN: " . number_format($porcentaje_gastado, 1) . "% gastado";
    } else {
        $nivel_alerta = 'success';
        $mensaje = "✅ DENTRO DEL PRESUPUESTO: " . number_format($porcentaje_gastado, 1) . "% gastado";
    }
    
    return [
        'nivel_alerta' => $nivel_alerta,
        'mensaje' => $mensaje,
        'porcentaje_gastado' => $porcentaje_gastado,
        'diferencia_absoluta' => $diferencia,
        'diferencia_porcentual' => $porcentaje_gastado - 100,
        'esta_sobrepresupuestado' => $porcentaje_gastado > 100
    ];
}

/**
 * Analiza variaciones por ítem del presupuesto
 */
public function analizarVariacionesPorItem(int $presupuesto_id): array {
    $presupuesto = $this->getPresupuestoDetalle((int)$presupuesto_id);
    
    if (!$presupuesto || !isset($presupuesto['items'])) {
        return ['error' => 'Presupuesto o ítems no encontrados'];
    }
    
    $analisis_items = [];
    $total_excedido = 0;
    $total_ahorrado = 0;
    
    foreach ($presupuesto['items'] as $item) {
        // Obtener gasto real por ítem
        $gasto_real_item = $this->obtenerGastoRealPorItem($item['id']);
        
        $presupuestado = (float)$item['monto'];
        $gastado = $gasto_real_item;
        $diferencia = $presupuestado - $gastado;
        $porcentaje = $presupuestado > 0 ? ($gastado / $presupuestado) * 100 : 0;
        
        // Determinar categoría
        if ($porcentaje > 100) {
            $categoria = 'excedido';
            $total_excedido += abs($diferencia);
        } elseif ($porcentaje > 80) {
            $categoria = 'precaucion';
        } else {
            $categoria = 'dentro_presupuesto';
            $total_ahorrado += $diferencia;
        }
        
        $analisis_items[] = [
            'item_id' => $item['id'],
            'descripcion' => $item['descripcion'],
            'presupuestado' => $presupuestado,
            'gastado' => $gastado,
            'diferencia' => $diferencia,
            'porcentaje' => $porcentaje,
            'categoria' => $categoria,
            'icono' => $this->getIconoPorCategoria($categoria),
            'color' => $this->getColorPorCategoria($categoria)
        ];
    }
    
    return [
        'items' => $analisis_items,
        'resumen' => [
            'total_excedido' => $total_excedido,
            'total_ahorrado' => $total_ahorrado,
            'items_excedidos' => count(array_filter($analisis_items, fn($item) => $item['categoria'] === 'excedido')),
            'items_precaucion' => count(array_filter($analisis_items, fn($item) => $item['categoria'] === 'precaucion')),
            'items_ok' => count(array_filter($analisis_items, fn($item) => $item['categoria'] === 'dentro_presupuesto'))
        ]
    ];
}

/**
 * Obtiene el gasto real asociado a un ítem específico
 */
private function obtenerGastoRealPorItem(int $item_id): float {
    try {
        $sql = "SELECT COALESCE(SUM(monto), 0) as total_gastado 
                FROM gastos_reales 
                WHERE item_id = :item_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':item_id' => $item_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (float)($result['total_gastado'] ?? 0);
    } catch (\PDOException $e) {
        error_log("Error al obtener gasto por ítem: " . $e->getMessage());
        return 0.0;
    }
}

/**
 * Helper para iconos por categoría
 */
private function getIconoPorCategoria(string $categoria): string {
    return match($categoria) {
        'excedido' => '🔴',
        'precaucion' => '🟡', 
        'dentro_presupuesto' => '🟢',
        default => '⚪'
    };
}

/**
 * Helper para colores por categoría
 */
private function getColorPorCategoria(string $categoria): string {
    return match($categoria) {
        'excedido' => 'text-red-600',
        'precaucion' => 'text-yellow-600',
        'dentro_presupuesto' => 'text-green-600',
        default => 'text-gray-600'
    };
}
/**
 * Calcula proyecciones financieras basadas en el gasto histórico
 */
public function calcularProyecciones(int $presupuesto_id): array {
    $presupuesto = $this->getPresupuestoDetalle((int)$presupuesto_id);
    
    if (!$presupuesto) {
        return ['error' => 'Presupuesto no encontrado'];
    }
    
    $monto_total = $presupuesto['monto_total'];
    $gastado = $presupuesto['gastado'];
    $restante = $monto_total - $gastado;
    
    // Obtener historial de gastos para calcular tasa mensual
    $tasa_mensual = $this->calcularTasaGastoMensual($presupuesto_id);
    
    // Si no hay gastos históricos, usar proyección simple
    if ($tasa_mensual === 0.0) {
        $meses_transcurridos = $this->calcularMesesTranscurridos($presupuesto['fecha_creacion']);
        if ($meses_transcurridos > 0) {
            $tasa_mensual = $gastado / $meses_transcurridos;
        } else {
            $tasa_mensual = $gastado; // Si es el primer mes
        }
    }
    
    // Cálculos de proyección
    $meses_restantes = $tasa_mensual > 0 ? $restante / $tasa_mensual : 0;
    $fecha_agotamiento = $this->calcularFechaAgotamiento($presupuesto['fecha_creacion'], $meses_restantes);
    $proyeccion_final = $gastado + ($tasa_mensual * $this->calcularMesesRestantesAnio());
    $porcentaje_proyeccion = $monto_total > 0 ? ($proyeccion_final / $monto_total) * 100 : 0;
    
    // Determinar nivel de alerta de la proyección
    $nivel_alerta_proyeccion = 'success';
    if ($porcentaje_proyeccion > 110) {
        $nivel_alerta_proyeccion = 'critical';
    } elseif ($porcentaje_proyeccion > 100) {
        $nivel_alerta_proyeccion = 'danger';
    } elseif ($porcentaje_proyeccion > 90) {
        $nivel_alerta_proyeccion = 'warning';
    }
    
    return [
        'tasa_mensual' => $tasa_mensual,
        'meses_restantes' => $meses_restantes,
        'fecha_agotamiento' => $fecha_agotamiento,
        'proyeccion_final' => $proyeccion_final,
        'porcentaje_proyeccion' => $porcentaje_proyeccion,
        'nivel_alerta_proyeccion' => $nivel_alerta_proyeccion,
          $meses_transcurridos = $this->calcularMesesTranscurridos($presupuesto['fecha_creacion']),
        'es_proyectable' => $tasa_mensual > 0 && $meses_transcurridos >= 1
    ];
}

/**
 * Calcula la tasa de gasto mensual promedio
 */
private function calcularTasaGastoMensual(int $presupuesto_id): float {
    try {
        $sql = "
            SELECT 
                MONTH(fecha_gasto) as mes,
                YEAR(fecha_gasto) as anio,
                SUM(monto) as total_mensual
            FROM gastos_reales 
            WHERE presupuesto_id = :presupuesto_id
            GROUP BY YEAR(fecha_gasto), MONTH(fecha_gasto)
            HAVING COUNT(*) > 0
            ORDER BY anio, mes
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':presupuesto_id' => $presupuesto_id]);
        $gastos_mensuales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($gastos_mensuales)) {
            return 0.0;
        }
        
        // Calcular promedio mensual
        $total = 0;
        foreach ($gastos_mensuales as $gasto) {
            $total += (float)$gasto['total_mensual'];
        }
        
        return $total / count($gastos_mensuales);
        
    } catch (\PDOException $e) {
        error_log("Error al calcular tasa mensual: " . $e->getMessage());
        return 0.0;
    }
}

/**
 * Calcula meses transcurridos desde la creación del presupuesto
 */
private function calcularMesesTranscurridos(string $fecha_creacion): int {
    $fecha_creacion = new DateTime($fecha_creacion);
    $fecha_actual = new DateTime();
    $diferencia = $fecha_actual->diff($fecha_creacion);
    
    return ($diferencia->y * 12) + $diferencia->m + ($diferencia->d > 0 ? 1 : 0);
}

/**
 * Calcula meses restantes en el año
 */
private function calcularMesesRestantesAnio(): int {
    $mes_actual = (int)date('n');
    return 12 - $mes_actual;
}

/**
 * Calcula fecha estimada de agotamiento
 */
private function calcularFechaAgotamiento(string $fecha_creacion, float $meses_restantes): string {
    if ($meses_restantes <= 0) {
        return "Presupuesto agotado";
    }
    
    $fecha_base = new DateTime($fecha_creacion);
    $meses_enteros = (int)floor($meses_restantes);
    $dias_extra = (int)(($meses_restantes - $meses_enteros) * 30);
    
    $fecha_agotamiento = clone $fecha_base;
    $fecha_agotamiento->modify("+{$meses_enteros} months +{$dias_extra} days");
    
    return $fecha_agotamiento->format('d/m/Y');
}
}