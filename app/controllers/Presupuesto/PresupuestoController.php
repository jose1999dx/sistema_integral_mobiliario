<?php
// SISTEMA_INTEGRAL_MOBILIARIO/app/controllers/Presupuesto/PresupuestoController.php

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
            // Presupuesto encontrado, cargamos la vista de detalle
            $data = [
                'titulo' => 'Detalle del Presupuesto: ' . $presupuesto['nombre'],
                'presupuesto' => $presupuesto,
                'proyectos' => $this->model->getProyectos(), // Útil por si se necesita información del proyecto
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
}