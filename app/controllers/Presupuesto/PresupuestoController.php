<?php
// SISTEMA_INTEGRAL_MOBILIARIO/app/controllers/Presupuesto/PresupuestoController.php
 // Referenciar el namespace de DOMPDF
    use Dompdf\Dompdf;
    use Dompdf\Options;
/**
 * Controlador de Presupuestos - Opera en el Namespace Global.
 * Usa la función global view() para cargar las vistas.
 */
class PresupuestoController {
    private $model;

    public function __construct() {
        // Asegúrate de que PresupuestoModel está cargado y disponible
        $this->model = new PresupuestoModel(); 
    }

    public function index() {
        $presupuestos = $this->model->getPresupuestosList(); 
        
        $data = [
            'titulo' => '📊 Lista de Presupuestos Creados',
            'presupuestos' => $presupuestos,
        ];
        
        view('presupuesto/listar', $data);
    }

   public function detalle($id = null) {
    // Validación básica del ID
    if ($id === null || !is_numeric($id) || (int)$id <= 0) {
        $this->setSessionMessage('error', 'ID de presupuesto inválido o no especificado.');
        $this->redirect('presupuesto/index');
        return;
    }

    $presupuesto = $this->model->getPresupuestoDetalle((int)$id);

    if ($presupuesto) {
        // ✅ NUEVO: Obtener análisis de desviaciones
        $analisis_desviaciones = $this->model->analizarDesviaciones((int)$id);
        $analisis_variaciones = $this->model->analizarVariacionesPorItem((int)$id);
         $proyecciones = $this->model->calcularProyecciones((int)$id);
        
        // Presupuesto encontrado, cargamos la vista de detalle
        $data = [
            'titulo' => 'Detalle del Presupuesto: ' . $presupuesto['nombre'],
            'presupuesto' => $presupuesto,
            'proyectos' => $this->model->getProyectos(), // Útil por si se necesita información del proyecto
            'analisis_desviaciones' => $analisis_desviaciones, // ← Nuevo dato agregado
            'proyecciones' => $proyecciones
        ];
        view('presupuesto/detalle', $data);
    } else {
        // Presupuesto no encontrado
        $this->setSessionMessage('error', 'El presupuesto solicitado no existe.');
        $this->redirect('presupuesto/index');
    }
}
    // --- MODIFICACIÓN DE MÉTODO ---
    /**
     * Muestra el formulario vacío para crear un presupuesto.
     * También sirve como base si se usa la misma vista para editar.
     */
    public function crear() {
        // Prepara los datos iniciales para la vista del formulario
        $data = [
            'titulo' => 'Creación de Presupuesto por Proyecto',
            'proyectos' => $this->model->getProyectos(),
            'errores' => [],
            'es_edicion' => false, // Bandera para saber si es edición
            'presupuesto_id' => null,
            'form_data' => [
                'nombre' => '',
                'id_proyecto' => '',
                'descripcion' => '', 
                'items' => [['descripcion' => '', 'tipo' => 'Directo', 'monto' => '']]
            ]
        ];

        view('presupuesto/crear', $data);
    }

    // --- NUEVO MÉTODO ---
    /**
     * Carga un presupuesto existente y muestra el formulario de edición.
     * Reutiliza la vista 'presupuesto/crear'.
     */
    // En tu PresupuestoController - CORREGIR el método editar:
     public function editar($id = null) {
    if ($id === null || !is_numeric($id) || (int)$id <= 0) {
        $this->setSessionMessage('error', 'ID de presupuesto inválido para edición.');
        $this->redirect('presupuesto/index');
        return;
    }

    $presupuesto = $this->model->getPresupuestoDetalle((int)$id);

    if (!$presupuesto) {
        $this->setSessionMessage('error', 'El presupuesto a editar no existe.');
        $this->redirect('presupuesto/index');
        return;
    }

    // ✅ VERIFICAR QUE ESTOS DATOS SE ESTÉN PASANDO CORRECTAMENTE:
    $data = [
        'titulo' => 'Editar Presupuesto: ' . $presupuesto['nombre'],
        'presupuesto' => $presupuesto, // ← Este debe tener los datos
        'proyectos' => $this->model->getProyectos(),
        'errores' => []
    ];
    
    // Debug temporal - ver qué contiene $presupuesto
    /*echo "<pre>";
    print_r($presupuesto);
    echo "</pre>";*/
    // exit; // ← Descomenta esto temporalmente para ver los datos
    
    view('presupuesto/editar', $data);
}
    // --- NUEVO MÉTODO ---
    /**
     * Procesa la solicitud POST para actualizar un presupuesto existente.
     */
    public function actualizar() {
        // Asegurar que solo se procesen peticiones POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('presupuesto/index'); // Redirigir a listado si no es POST
            return;
        }

        // 1. Recolección y saneamiento de datos, incluyendo el ID
        $presupuesto_id = (int)($_POST["presupuesto_id"] ?? 0);
        $nombre = trim($_POST["nombre"] ?? '');
        $proyecto_id = (int)($_POST["id_proyecto"] ?? 0); 
        $descripcion = trim($_POST["descripcion"] ?? null);
        $items_raw = $_POST["item"] ?? []; 
        $errores = [];
        
        // 2. Validación del ID
        if ($presupuesto_id <= 0) {
            $errores[] = "Error: ID de presupuesto no válido para actualizar.";
        }

        // 3. Validación de datos principales (Igual que en guardar())
        if (empty($nombre)) {
            $errores[] = "El nombre del presupuesto es obligatorio.";
        }
        if ($proyecto_id <= 0) {
            $errores[] = "Debe seleccionar un proyecto válido.";
        }

        // 4. Validación y limpieza de ítems (array) (Igual que en guardar())
        $items_limpios = [];
        $hay_items_validos = false;
        
        if (!is_array($items_raw) || empty($items_raw)) {
            $errores[] = "El presupuesto debe contener al menos un ítem.";
        } else {
            foreach ($items_raw as $i => $item) {
                $monto = trim($item['monto'] ?? '');
                $desc = trim($item['descripcion'] ?? '');
                $tipo = trim($item['tipo'] ?? 'Gasto');
                
                // Validar que el ítem sea utilizable 
                if (empty($desc) || !is_numeric($monto) || (float)$monto <= 0) {
                    continue; 
                }
                
                $items_limpios[] = [
                    // Si tienes un ID de item para editar ítems individuales, lo incluirías aquí
                    'descripcion' => $desc,
                    'tipo' => $tipo,
                    'monto' => (float)$monto 
                ];
                $hay_items_validos = true;
            }

            if (!$hay_items_validos && empty($errores)) {
                $errores[] = "Ninguno de los ítems ingresados es válido (descripción no vacía y monto mayor a 0).";
            }
        }
        
        // 5. Procesamiento final (Si no hay errores de validación)
        if (empty($errores)) {
            // Llama a un nuevo método en el modelo para ACTUALIZAR
            $resultado = $this->model->actualizarPresupuestoConItems($presupuesto_id, $proyecto_id, $nombre, $items_limpios, $descripcion);

            if ($resultado) {
                // Éxito
                $this->setSessionMessage('success', 'Presupuesto actualizado con éxito.');
                $this->redirect('presupuesto/detalle/' . $presupuesto_id); // Redirigir al detalle actualizado
                return;
            } else {
                // Error de Base de Datos
                $errores[] = "Error al actualizar el presupuesto en el sistema. Revise el log.";
            }
        }

        // 6. Mostrar la vista de edición con errores
        if (!empty($errores)) {
            $proyectos = $this->model->getProyectos();

            $data = [
                'titulo' => 'Editar Presupuesto (Errores)',
                'proyectos' => $proyectos,
                'errores' => $errores,
                'es_edicion' => true,
                'presupuesto_id' => $presupuesto_id,
                'form_data' => [
                    'nombre' => $nombre,
                    'id_proyecto' => $proyecto_id,
                    'descripcion' => $descripcion,
                    'items' => $items_raw 
                ]
            ];
            
            view('presupuesto/crear', $data);
        }
    }


    public function guardar() {
        // Asegurar que solo se procesen peticiones POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('presupuesto/crear');
            return;
        }

        // 1. Recolección y saneamiento de datos
        $nombre = trim($_POST["nombre"] ?? '');
        $proyecto_id = (int)($_POST["id_proyecto"] ?? 0); 
        $descripcion = trim($_POST["descripcion"] ?? null);
        $items_raw = $_POST["item"] ?? []; 
        $errores = [];

        // 2. Validación de datos principales
        if (empty($nombre)) {
            $errores[] = "El nombre del presupuesto es obligatorio.";
        }
        if ($proyecto_id <= 0) {
            $errores[] = "Debe seleccionar un proyecto válido.";
        }

        // 3. Validación y limpieza de ítems (array)
        $items_limpios = [];
        $hay_items_validos = false;
        
        if (!is_array($items_raw) || empty($items_raw)) {
            $errores[] = "El presupuesto debe contener al menos un ítem.";
        } else {
            foreach ($items_raw as $i => $item) {
                $monto = trim($item['monto'] ?? '');
                $desc = trim($item['descripcion'] ?? '');
                $tipo = trim($item['tipo'] ?? 'Gasto');
                
                // Validar que el ítem sea utilizable (descripción no vacía y monto numérico > 0)
                if (empty($desc) || !is_numeric($monto) || (float)$monto <= 0) {
                    continue; 
                }
                
                $items_limpios[] = [
                    'descripcion' => $desc,
                    'tipo' => $tipo,
                    'monto' => (float)$monto 
                ];
                $hay_items_validos = true;
            }

            if (!$hay_items_validos && empty($errores)) {
                $errores[] = "Ninguno de los ítems ingresados es válido (descripción no vacía y monto mayor a 0).";
            }
        }
        
        // 4. Procesamiento final (Si no hay errores de validación)
        if (empty($errores)) {
            $presupuesto_id = $this->model->crearPresupuestoConItems($proyecto_id, $nombre, $items_limpios, $descripcion);

            if ($presupuesto_id) {
                // Éxito
                $this->setSessionMessage('success', 'Presupuesto creado con éxito.');
                $this->redirect('presupuesto/index');
            } else {
                // Error de Base de Datos
                $errores[] = "Error al guardar el presupuesto en el sistema. Por favor, revise el log de errores de PHP para el detalle.";
            }
        }

        // 5. Mostrar la vista de creación con errores
        if (!empty($errores)) {
            $proyectos = $this->model->getProyectos();

            $data = [
                'titulo' => 'Crear Nuevo Presupuesto',
                'proyectos' => $proyectos,
                'errores' => $errores,
                'es_edicion' => false,
                'presupuesto_id' => null,
                'form_data' => [
                    'nombre' => $nombre,
                    'id_proyecto' => $proyecto_id,
                    'descripcion' => $descripcion,
                    'items' => $items_raw 
                ]
            ];
            
            view('presupuesto/crear', $data);
        }
    }
    public function registrarGastoReal(array $datos_gasto): bool {
    $sql = "
        INSERT INTO gastos_reales 
        (presupuesto_id, descripcion, monto, fecha_gasto)  // ← Columnas REALES de tu tabla
        VALUES (:presupuesto_id, :descripcion, :monto, :fecha_gasto)  // ← Parámetros corregidos
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

    // --- MODIFICACIÓN DE MÉTODO (Preparación para Gasto Real) ---
   public function gastosReales($id = null) {
    if ($id === null || !is_numeric($id) || (int)$id <= 0) {
        $this->setSessionMessage('error', 'ID de presupuesto inválido para registrar gasto.');
        $this->redirect('presupuesto/index');
        return;
    }

    $presupuesto = $this->model->getPresupuestoDetalle((int)$id);

    if (!$presupuesto) {
        $this->setSessionMessage('error', 'El presupuesto no existe.');
        $this->redirect('presupuesto/index');
        return;
    }

    $data = [
        'titulo' => 'Registrar Gasto Real para Presupuesto #' . $id,
        'presupuesto' => $presupuesto,
        'errores' => []
    ];
    
    view('presupuesto/gastos_reales_form', $data);
}
    
    // --- NUEVO MÉTODO (Necesario para procesar el formulario de Gasto Real) ---
    public function guardarGastoReal() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('presupuesto/index');
            return;
        }
        
        // 1. Recolección y validación de datos del gasto
        $presupuesto_id = (int)($_POST['presupuesto_id'] ?? 0);
        $item_id = (int)($_POST['item_id'] ?? 0); // O el ID del ítem presupuestado
        $monto = trim($_POST['monto'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $fecha = trim($_POST['fecha'] ?? date('Y-m-d'));
        $errores = [];
        
        if ($presupuesto_id <= 0) { $errores[] = "ID de presupuesto inválido."; }
        if (empty($descripcion)) { $errores[] = "La descripción del gasto es obligatoria."; }
        if (!is_numeric($monto) || (float)$monto <= 0) { $errores[] = "El monto debe ser un número positivo."; }
        
        // 2. Procesar (Modelo)
        if (empty($errores)) {
            // Llama a un método del modelo para guardar el gasto real y actualizar el total 'gastado'
            $datos_gasto = [
                'presupuesto_id' => $presupuesto_id,
                'item_id' => $item_id, // Si aplicas la lógica de ítems
                'monto' => (float)$monto,
                'descripcion' => $descripcion,
                'fecha' => $fecha
            ];
            
            if ($this->model->registrarGastoReal($datos_gasto)) {
                $this->setSessionMessage('success', 'Gasto real registrado con éxito.');
                $this->redirect('presupuesto/detalle/' . $presupuesto_id);
                return;
            } else {
                $errores[] = "Error al registrar el gasto real en la base de datos.";
            }
        }
        
        // 3. Recargar vista con errores si falla
        // Si hay errores, se necesita el presupuesto para recargar la vista del formulario
        if (!empty($errores)) {
             $presupuesto = $this->model->getPresupuestoDetalle($presupuesto_id);
             $data = [
                 'titulo' => 'Registrar Gasto Real (Error)',
                 'presupuesto' => $presupuesto,
                 'errores' => $errores,
                 // Aquí se cargarían los datos del formulario que se intentó enviar para mantenerlos
             ];
             view('presupuesto/gastos_reales_form', $data);
        }
    }

    // El resto de tus métodos se mantienen...
    
    public function aprobar($id = null) {
        if ($id === null || !is_numeric($id)) {
            $this->redirect("/presupuesto/index?error=id_invalido");
        }
        
        if ($this->model->actualizarEstado((int)$id, 'Aprobado')) {
            $this->setSessionMessage('success', 'Presupuesto Aprobado');
            $this->redirect('presupuesto/index');
        } else {
            $this->setSessionMessage('error', 'Fallo al cambiar el estado del presupuesto.');
            $this->redirect('presupuesto/index');
        }
    }
    
    public function rechazar($id = null) {
        if ($id === null || !is_numeric($id)) {
            $this->redirect("/presupuesto/index?error=id_invalido");
        }
        
        if ($this->model->actualizarEstado((int)$id, 'Rechazado')) {
            $this->setSessionMessage('success', 'Presupuesto Rechazado');
            $this->redirect('presupuesto/index');
        } else {
            $this->setSessionMessage('error', 'Fallo al cambiar el estado del presupuesto.');
            $this->redirect('presupuesto/index');
        }
    }
    
    /**
     * Función auxiliar para Redirección, usando la función global redirect()
     */
    private function redirect($path) {
        if (function_exists('redirect')) {
            redirect($path);
        } else {
             // Fallback si la función global no está disponible
            $url = BASE_URL . '/index.php?url=' . ltrim($path, '/');
            header("Location: $url");
            exit;
        }
    }
    
    /**
     * Función auxiliar para Mensajes de Sesión
     */
    private function setSessionMessage($type, $message) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['message'] = ['type' => $type, 'text' => $message];
    }

    public function rentabilidad() {
    // Obtener todos los presupuestos para el análisis
    $presupuestos = $this->model->getPresupuestosList();
    
    // Calcular métricas de rentabilidad
    $metricas_rentabilidad = $this->calcularMetricasRentabilidad($presupuestos);
    
    $data = [
        'titulo' => '📊 Reportes de Rentabilidad',
        'presupuestos' => $presupuestos,
        'metricas' => $metricas_rentabilidad,
        'proyectos_rentables' => $this->filtrarProyectosRentables($presupuestos),
        'proyectos_no_rentables' => $this->filtrarProyectosNoRentables($presupuestos)
    ];
    
    view('presupuesto/rentabilidad', $data);
}

/**
 * Calcula métricas generales de rentabilidad
 */
private function calcularMetricasRentabilidad($presupuestos) {
    $total_presupuestado = 0;
    $total_gastado = 0;
    $total_utilidad = 0;
    $proyectos_rentables = 0;
    $proyectos_analizados = 0;
    
    foreach ($presupuestos as $presupuesto) {
        // Simulamos ingresos (en un sistema real esto vendría de otra tabla)
        $ingresos_simulados = $presupuesto['monto_total'] * 1.3; // 30% de ganancia estimada
        
        $gastado = $presupuesto['gastado'] ?? 0;
        $utilidad = $ingresos_simulados - $gastado;
        
        $total_presupuestado += $presupuesto['monto_total'];
        $total_gastado += $gastado;
        $total_utilidad += $utilidad;
        
        if ($utilidad > 0) {
            $proyectos_rentables++;
        }
        $proyectos_analizados++;
    }
    
    $margen_promedio = $total_presupuestado > 0 ? ($total_utilidad / $total_presupuestado) * 100 : 0;
    $tasa_rentabilidad = $proyectos_analizados > 0 ? ($proyectos_rentables / $proyectos_analizados) * 100 : 0;
    
    return [
        'total_presupuestado' => $total_presupuestado,
        'total_gastado' => $total_gastado,
        'total_utilidad' => $total_utilidad,
        'margen_promedio' => $margen_promedio,
        'proyectos_rentables' => $proyectos_rentables,
        'proyectos_analizados' => $proyectos_analizados,
        'tasa_rentabilidad' => $tasa_rentabilidad
    ];
}

/**
 * Filtra proyectos rentables
 */
private function filtrarProyectosRentables($presupuestos) {
    return array_filter($presupuestos, function($presupuesto) {
        $ingresos_simulados = $presupuesto['monto_total'] * 1.3;
        $gastado = $presupuesto['gastado'] ?? 0;
        return ($ingresos_simulados - $gastado) > 0;
    });
}

/**
 * Filtra proyectos no rentables
 */
private function filtrarProyectosNoRentables($presupuestos) {
    return array_filter($presupuestos, function($presupuesto) {
        $ingresos_simulados = $presupuesto['monto_total'] * 1.3;
        $gastado = $presupuesto['gastado'] ?? 0;
        return ($ingresos_simulados - $gastado) <= 0;
    });
}

/**
 * Muestra análisis detallado de rentabilidad por proyecto
 */
public function rentabilidadProyecto($id = null) {
    if ($id === null || !is_numeric($id) || (int)$id <= 0) {
        $this->setSessionMessage('error', 'ID de proyecto inválido.');
        $this->redirect('presupuesto/rentabilidad');
        return;
    }

    $presupuesto = $this->model->getPresupuestoDetalle((int)$id);

    if (!$presupuesto) {
        $this->setSessionMessage('error', 'El proyecto no existe.');
        $this->redirect('presupuesto/rentabilidad');
        return;
    }

    error_log("=== DEBUG RENTABILIDAD PROYECTO ===");
    error_log("Presupuesto ID: " . $id);
    error_log("Presupuesto encontrado: " . ($presupuesto ? 'SÍ' : 'NO'));

    // Calcular métricas de rentabilidad específicas para este proyecto
    $rentabilidad = $this->calcularRentabilidadProyecto($presupuesto);
    $analisis_costos = $this->analizarCostosProyecto($presupuesto);

     $recomendacion = $this->getRecomendacionRentabilidad($rentabilidad);

    $data = [
        'titulo' => '📊 Rentabilidad: ' . $presupuesto['nombre'],
        'presupuesto' => $presupuesto,
        'rentabilidad' => $rentabilidad,
        'analisis_costos' => $analisis_costos
    ];
    
    view('presupuesto/rentabilidad_proyecto', $data);
}

/**
 * Calcula métricas de rentabilidad para un proyecto específico
 */
private function calcularRentabilidadProyecto($presupuesto) {
    // Simular ingresos (en sistema real vendría de tabla de ventas/contratos)
    $ingresos_simulados = $presupuesto['monto_total'] * 1.3; // 30% de ganancia estimada
    $gastado = $presupuesto['gastado'] ?? 0;
    
    $utilidad = $ingresos_simulados - $gastado;
    $margen_utilidad = $ingresos_simulados > 0 ? ($utilidad / $ingresos_simulados) * 100 : 0;
    $roi = $gastado > 0 ? ($utilidad / $gastado) * 100 : 0;

    
    
    return [
        'ingresos' => $ingresos_simulados,
        'gastado' => $gastado,
        'utilidad' => $utilidad,
        'margen_utilidad' => $margen_utilidad,
        'roi' => $roi,
        'es_rentable' => $utilidad > 0,
        'nivel_rentabilidad' => $this->clasificarRentabilidad($margen_utilidad)
    ];
}

/**
 * Clasifica el nivel de rentabilidad
 */
private function clasificarRentabilidad($margen) {
    if ($margen > 25) return 'excelente';
    if ($margen > 15) return 'buena';
    if ($margen > 5) return 'moderada';
    if ($margen > 0) return 'baja';
    return 'no_rentable';
}

/**
 * Analiza la distribución de costos del proyecto
 */
/**
 * Analiza la distribución de costos del proyecto (MEJORADO)
 */
private function analizarCostosProyecto($presupuesto) {
    if (!isset($presupuesto['items']) || empty($presupuesto['items'])) {
        return ['error' => 'No hay ítems para analizar'];
    }
    
    $total_presupuestado = $presupuesto['monto_total'];
    $total_gastado = $presupuesto['gastado'] ?? 0;
    
    $analisis = [
        'por_tipo' => [],
        'porcentajes' => [],
        'items_detallados' => [],
        'total_presupuestado' => $total_presupuestado,
        'total_gastado' => $total_gastado,
        'desviacion_total' => $total_gastado - $total_presupuestado,
        'porcentaje_desviacion_total' => $total_presupuestado > 0 ? (($total_gastado - $total_presupuestado) / $total_presupuestado) * 100 : 0
    ];
    
    // Obtener gastos reales por ítem
    $gastos_por_item = $this->obtenerGastosRealesPorItem($presupuesto['id']);
    
    foreach ($presupuesto['items'] as $item) {
        $item_id = $item['id'];
        $tipo = $item['tipo'] ?? 'Sin clasificar';
        $presupuestado = (float)($item['monto'] ?? 0);
        $gastado = $gastos_por_item[$item_id] ?? 0;
        $desviacion = $gastado - $presupuestado;
        $porcentaje_desviacion = $presupuestado > 0 ? ($desviacion / $presupuestado) * 100 : 0;
        
        // Agrupar por tipo
        if (!isset($analisis['por_tipo'][$tipo])) {
            $analisis['por_tipo'][$tipo] = [
                'presupuestado' => 0,
                'gastado' => 0,
                'items_count' => 0
            ];
        }
        $analisis['por_tipo'][$tipo]['presupuestado'] += $presupuestado;
        $analisis['por_tipo'][$tipo]['gastado'] += $gastado;
        $analisis['por_tipo'][$tipo]['items_count']++;
        
        // Análisis detallado por ítem
        $analisis['items_detallados'][] = [
            'id' => $item_id,
            'descripcion' => $item['descripcion'],
            'tipo' => $tipo,
            'presupuestado' => $presupuestado,
            'gastado' => $gastado,
            'desviacion' => $desviacion,
            'porcentaje_desviacion' => $porcentaje_desviacion,
            'estado' => $this->clasificarEstadoItem($presupuestado, $gastado, $porcentaje_desviacion),
            'color_estado' => $this->getColorEstadoItem($presupuestado, $gastado, $porcentaje_desviacion)
        ];
    }
    
    // Calcular porcentajes por tipo
    foreach ($analisis['por_tipo'] as $tipo => $datos) {
        $analisis['por_tipo'][$tipo]['desviacion'] = $datos['gastado'] - $datos['presupuestado'];
        $analisis['por_tipo'][$tipo]['porcentaje_desviacion'] = $datos['presupuestado'] > 0 ? 
            (($datos['gastado'] - $datos['presupuestado']) / $datos['presupuestado']) * 100 : 0;
        $analisis['por_tipo'][$tipo]['porcentaje_presupuesto'] = $total_presupuestado > 0 ? 
            ($datos['presupuestado'] / $total_presupuestado) * 100 : 0;
    }
    
    // Identificar ítems problemáticos
    $analisis['items_problematicos'] = array_filter($analisis['items_detallados'], function($item) {
        return $item['porcentaje_desviacion'] > 10; // Más del 10% de desviación
    });
    
    $analisis['items_sobrepasados'] = array_filter($analisis['items_detallados'], function($item) {
        return $item['gastado'] > $item['presupuestado'];
    });
    
    return $analisis;
}

/**
 * Obtiene gastos reales agrupados por ítem
 */
/**
 * Obtiene gastos reales agrupados por ítem (VERSIÓN SIMPLIFICADA)
 */
private function obtenerGastosRealesPorItem($presupuesto_id) {
    try {
        // Versión simplificada que no depende de la estructura del modelo
        $database = new Database();
        $db = $database->getConnection();
        
        $sql = "SELECT item_id, SUM(monto) as total_gastado 
                FROM gastos_reales 
                WHERE presupuesto_id = :presupuesto_id AND item_id IS NOT NULL
                GROUP BY item_id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':presupuesto_id' => $presupuesto_id]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $gastos_por_item = [];
        foreach ($resultados as $fila) {
            $gastos_por_item[$fila['item_id']] = (float)$fila['total_gastado'];
        }
        
        return $gastos_por_item;
        
    } catch (\PDOException $e) {
        error_log("Error al obtener gastos por ítem: " . $e->getMessage());
        return [];
    }
}

/**
 * Clasifica el estado de un ítem basado en su desviación
 */
private function clasificarEstadoItem($presupuestado, $gastado, $porcentaje_desviacion) {
    if ($gastado == 0) return 'Sin gastos';
    if ($porcentaje_desviacion <= -10) return 'Por debajo';
    if ($porcentaje_desviacion <= 10) return 'Dentro del presupuesto';
    if ($porcentaje_desviacion <= 25) return 'Ligeramente excedido';
    if ($porcentaje_desviacion <= 50) return 'Moderadamente excedido';
    return 'Significativamente excedido';
}

/**
 * Obtiene color para el estado del ítem
 */
private function getColorEstadoItem($presupuestado, $gastado, $porcentaje_desviacion) {
    if ($gastado == 0) return 'gray';
    if ($porcentaje_desviacion <= -10) return 'green';
    if ($porcentaje_desviacion <= 10) return 'blue';
    if ($porcentaje_desviacion <= 25) return 'yellow';
    if ($porcentaje_desviacion <= 50) return 'orange';
    return 'red';
}
/**
 * Genera recomendaciones basadas en el análisis de rentabilidad
 */
private function getRecomendacionRentabilidad($rentabilidad) {
    if (!$rentabilidad['es_rentable']) {
        return "Revisar costos y considerar ajustes en el presupuesto. Proyecto no rentable.";
    }
    
    switch ($rentabilidad['nivel_rentabilidad']) {
        case 'excelente':
            return "¡Excelente rentabilidad! Considerar replicar este modelo en otros proyectos.";
            
        case 'buena':
            return "Buena rentabilidad. Mantener el control de costos actual.";
            
        case 'moderada':
            return "Rentabilidad moderada. Buscar oportunidades para optimizar costos.";
            
        case 'baja':
            return "Rentabilidad baja. Revisar precios de venta y estructura de costos.";
            
        default:
            return "Analizar oportunidades de mejora en la gestión del proyecto.";
    }
}

/**
 * Genera reporte ejecutivo en PDF con DOMPDF
 */
public function generarReportePDF($id = null) {
    if ($id === null || !is_numeric($id) || (int)$id <= 0) {
        $this->setSessionMessage('error', 'ID de proyecto inválido.');
        $this->redirect('presupuesto/rentabilidad');
        return;
    }

    $presupuesto = $this->model->getPresupuestoDetalle((int)$id);

    if (!$presupuesto) {
        $this->setSessionMessage('error', 'El proyecto no existe.');
        $this->redirect('presupuesto/rentabilidad');
        return;
    }

    // Calcular métricas
    $rentabilidad = $this->calcularRentabilidadProyecto($presupuesto);
    $analisis_costos = $this->analizarCostosProyecto($presupuesto);
    $recomendacion = $this->getRecomendacionRentabilidad($rentabilidad);

    // Generar PDF con DOMPDF
    $this->generarPDFConDOMPDF($presupuesto, $rentabilidad, $analisis_costos, $recomendacion);
}

/**
 * Genera el PDF usando DOMPDF
 */
/**
 * Genera el PDF usando DOMPDF (VERSIÓN CORREGIDA)
 */
/**
 * Genera el PDF usando DOMPDF (CON RUTA CORREGIDA)
 */
private function generarPDFConDOMPDF($presupuesto, $rentabilidad, $analisis_costos, $recomendacion) {
    // ✅ RUTA CORREGIDA: apuntar a la carpeta lib dentro de dom-pdfmaster
    require_once ROOT_PATH . '/libs/dompdf/autoload.inc.php';
    
    // Configurar opciones
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'Arial');
    
    // Crear instancia de DOMPDF
    $dompdf = new Dompdf($options);
    
    // Generar HTML para el PDF
    $html = $this->generarHTMLParaPDF($presupuesto, $rentabilidad, $analisis_costos, $recomendacion);
    
    // Cargar HTML
    $dompdf->loadHtml($html);
    
    // Configurar papel y orientación
    $dompdf->setPaper('A4', 'portrait');
    
    // Renderizar PDF
    $dompdf->render();
    
    // Generar nombre del archivo
    $nombre_archivo = 'reporte_ejecutivo_' . preg_replace('/[^a-zA-Z0-9]/', '_', $presupuesto['nombre']) . '_' . date('Y-m-d') . '.pdf';
    
    // Forzar descarga
    $dompdf->stream($nombre_archivo, [
        'Attachment' => true // true = descarga forzada
    ]);
    
    exit;
}
/**
 * Genera el contenido HTML para DOMPDF
 */
private function generarHTMLParaPDF($presupuesto, $rentabilidad, $analisis_costos, $recomendacion) {
    $fecha_generacion = date('d/m/Y H:i:s');
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Reporte Ejecutivo - ' . htmlspecialchars($presupuesto['nombre']) . '</title>
        <style>
            body { 
                font-family: "DejaVu Sans", "Arial", sans-serif; 
                font-size: 12px;
                line-height: 1.4;
                color: #333;
                margin: 0;
                padding: 20px;
            }
            .header { 
                border-bottom: 3px solid #2c5aa0; 
                padding-bottom: 15px; 
                margin-bottom: 20px; 
                text-align: center;
            }
            .company-name { 
                font-size: 24px; 
                font-weight: bold; 
                color: #2c5aa0; 
                margin-bottom: 10px;
            }
            .report-title { 
                font-size: 18px; 
                color: #333; 
                margin: 10px 0; 
            }
            .header-info {
                display: flex;
                justify-content: space-between;
                font-size: 11px;
                margin-top: 10px;
            }
            .section { 
                margin: 20px 0; 
                padding: 15px; 
                border: 1px solid #ddd; 
                border-radius: 5px; 
            }
            .section-title { 
                font-size: 16px; 
                font-weight: bold; 
                color: #2c5aa0; 
                margin-bottom: 15px; 
                border-bottom: 1px solid #eee;
                padding-bottom: 5px;
            }
            .metrics-grid { 
                display: table; 
                width: 100%; 
                margin: 15px 0; 
                border-collapse: collapse;
            }
            .metric-row { 
                display: table-row; 
            }
            .metric-cell { 
                display: table-cell; 
                padding: 12px; 
                border: 1px solid #e0e0e0; 
                text-align: center;
                vertical-align: middle;
            }
            .metric-value { 
                font-size: 18px; 
                font-weight: bold; 
                margin: 5px 0; 
            }
            .metric-label { 
                font-size: 11px; 
                color: #666; 
                text-transform: uppercase;
            }
            .positive { color: #28a745; }
            .negative { color: #dc3545; }
            .table { 
                width: 100%; 
                border-collapse: collapse; 
                margin: 15px 0; 
                font-size: 11px;
            }
            .table th, .table td { 
                border: 1px solid #ddd; 
                padding: 10px; 
                text-align: left; 
            }
            .table th { 
                background-color: #f8f9fa; 
                font-weight: bold; 
                color: #333;
            }
            .recommendation { 
                background-color: #e8f4fd; 
                padding: 20px; 
                border-left: 4px solid #2c5aa0; 
                margin: 20px 0; 
                font-style: italic;
            }
            .footer { 
                margin-top: 30px; 
                padding-top: 15px; 
                border-top: 1px solid #ddd; 
                text-align: center; 
                color: #666; 
                font-size: 10px; 
            }
            .text-center { text-align: center; }
            .text-right { text-align: right; }
            .mb-3 { margin-bottom: 15px; }
        </style>
    </head>
    <body>
        <!-- Encabezado -->
        <div class="header">
            <div class="company-name">SISTEMA INTEGRAL MOBILIARIO</div>
            <div class="report-title">' . htmlspecialchars($presupuesto['nombre']) . '</div>
            <div class="header-info">
                <div><strong>Proyecto:</strong> ' . htmlspecialchars($presupuesto['nombre_proyecto']) . '</div>
                <div><strong>Generado:</strong> ' . $fecha_generacion . '</div>
            </div>
        </div>

        <!-- Resumen Ejecutivo -->
        <div class="section">
            <div class="section-title">📊 RESUMEN EJECUTIVO</div>
            <div class="metrics-grid">
                <div class="metric-row">
                    <div class="metric-cell">
                        <div class="metric-value ' . ($rentabilidad['utilidad'] > 0 ? 'positive' : 'negative') . '">
                            $' . number_format($rentabilidad['utilidad'], 2) . '
                        </div>
                        <div class="metric-label">UTILIDAD/PÉRDIDA</div>
                    </div>
                    <div class="metric-cell">
                        <div class="metric-value ' . ($rentabilidad['roi'] > 0 ? 'positive' : 'negative') . '">
                            ' . number_format($rentabilidad['roi'], 1) . '%
                        </div>
                        <div class="metric-label">ROI</div>
                    </div>
                </div>
                <div class="metric-row">
                    <div class="metric-cell">
                        <div class="metric-value">
                            $' . number_format($presupuesto['monto_total'], 2) . '
                        </div>
                        <div class="metric-label">PRESUPUESTO</div>
                    </div>
                    <div class="metric-cell">
                        <div class="metric-value">
                            $' . number_format($rentabilidad['gastado'], 2) . '
                        </div>
                        <div class="metric-label">COSTOS REALES</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rentabilidad -->
        <div class="section">
            <div class="section-title">💰 ANÁLISIS DE RENTABILIDAD</div>
            <table class="table">
                <tr>
                    <th>Indicador</th>
                    <th>Valor</th>
                    <th>Estado</th>
                </tr>
                <tr>
                    <td>Margen de Utilidad</td>
                    <td>' . number_format($rentabilidad['margen_utilidad'], 1) . '%</td>
                    <td>' . ($rentabilidad['margen_utilidad'] > 15 ? '✅ Saludable' : '⚠️ Por mejorar') . '</td>
                </tr>
                <tr>
                    <td>Nivel de Rentabilidad</td>
                    <td>' . ucfirst($rentabilidad['nivel_rentabilidad']) . '</td>
                    <td>' . ($rentabilidad['es_rentable'] ? '✅ Rentable' : '❌ No Rentable') . '</td>
                </tr>
                <tr>
                    <td>Duración del Proyecto</td>
                    <td>' . number_format($rentabilidad['roi_avanzado']['duracion_proyecto_meses'] ?? 0, 1) . ' meses</td>
                    <td>📅</td>
                </tr>
            </table>
        </div>';

    // Análisis de Costos
    if (!isset($analisis_costos['error'])) {
        $html .= '
        <div class="section">
            <div class="section-title">🔍 DISTRIBUCIÓN DE COSTOS</div>
            <table class="table">
                <tr>
                    <th>Tipo de Costo</th>
                    <th>Presupuestado</th>
                    <th>Real</th>
                    <th>Desviación</th>
                </tr>';
        
        foreach ($analisis_costos['por_tipo'] as $tipo => $datos) {
            $color_class = $datos['desviacion'] > 0 ? 'negative' : 'positive';
            $html .= '
                <tr>
                    <td>' . htmlspecialchars($tipo) . '</td>
                    <td>$' . number_format($datos['presupuestado'], 2) . '</td>
                    <td>$' . number_format($datos['gastado'], 2) . '</td>
                    <td class="' . $color_class . '">
                        $' . number_format(abs($datos['desviacion']), 2) . ' 
                        (' . number_format($datos['porcentaje_desviacion'], 1) . '%)
                    </td>
                </tr>';
        }
        
        $html .= '
            </table>
        </div>';
    }

    // Recomendaciones
    $html .= '
        <div class="recommendation">
            <div style="font-weight: bold; margin-bottom: 10px;">💡 RECOMENDACIONES EJECUTIVAS</div>
            <div>' . htmlspecialchars($recomendacion) . '</div>
        </div>

        <!-- Pie de página -->
        <div class="footer">
            Este reporte fue generado automáticamente por el Sistema Integral Mobiliario.<br>
            Los datos son confidenciales y para uso interno de la empresa.
        </div>
    </body>
    </html>';

    return $html;
}

}
