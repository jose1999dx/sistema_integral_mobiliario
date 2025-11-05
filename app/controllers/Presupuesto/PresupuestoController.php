<?php
// SISTEMA_INTEGRAL_MOBILIARIO/app/controllers/Presupuesto/PresupuestoController.php

namespace Controller\Presupuesto; // Nuevo Namespace

use Model\Presupuesto\PresupuestoModel; // Usa el nuevo Namespace del Modelo

class PresupuestoController {
    private $model;

    public function __construct() {
        $this->model = new PresupuestoModel();
    }

    public function index() {
        $presupuestos = $this->model->getPresupuestosList(); 
        
        $data = [
            'titulo' => '📊 Lista de Presupuestos Creados',
            'presupuestos' => $presupuestos,
        ];
        
        $this->renderView('presupuesto/listar', $data);
    }
    
    public function crear() {
        $data = [
            'titulo' => 'Creación de Presupuesto por Proyecto',
            'proyectos' => $this->model->getProyectos(),
            'errores' => [],
            'nombre' => '',
            'proyecto_id' => '',
            'items' => [['descripcion' => '', 'tipo' => 'Directo', 'monto' => '']] // Fila inicial
        ];

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $nombre = trim($_POST["nombre"] ?? '');
            $proyecto_id = $_POST["proyecto_id"] ?? null;
            $items = $_POST["item"] ?? []; 

            // Lógica de Validación
            if (empty($nombre)) $data['errores']['nombre'] = "El nombre es obligatorio.";
            if (empty($proyecto_id) || !is_numeric($proyecto_id)) $data['errores']['proyecto'] = "Debe seleccionar un proyecto.";
            
            $items_validos = [];
            foreach ($items['descripcion'] as $key => $desc) {
                if (!empty(trim($desc)) && isset($items['tipo'][$key]) && is_numeric($items['monto'][$key]) && $items['monto'][$key] > 0) {
                    $items_validos[] = [
                        'descripcion' => trim($desc),
                        'tipo' => $items['tipo'][$key],
                        'monto' => (float)$items['monto'][$key],
                    ];
                }
            }
            if (empty($items_validos)) $data['errores']['items'] = "Debe ingresar al menos un ítem de presupuesto válido.";

            if (empty($data['errores'])) {
                $nuevo_id = $this->model->crearPresupuestoConItems($proyecto_id, $nombre, $items_validos);
                
                if ($nuevo_id !== false) {
                    // Éxito: Redirigir al detalle
                    header("Location: /presupuesto/detalle/{$nuevo_id}"); 
                    exit();
                } else {
                    $data['errores']['general'] = "Error al guardar el presupuesto en el sistema.";
                }
            }
            
            $data['nombre'] = $nombre;
            $data['proyecto_id'] = $proyecto_id;
            $data['items'] = $this->reformatItemsForView($items);
        }

        $this->renderView('presupuesto/crear', $data);
    }

    // Función auxiliar para mantener ítems en la vista después de un error de validación
    private function reformatItemsForView($postItems) {
        $reformatted = [];
        if (!empty($postItems['descripcion'])) {
            foreach ($postItems['descripcion'] as $key => $desc) {
                $reformatted[] = [
                    'descripcion' => $desc,
                    'tipo' => $postItems['tipo'][$key] ?? 'Directo',
                    'monto' => $postItems['monto'][$key] ?? '',
                ];
            }
        }
        return $reformatted;
    }

    // Función de ayuda para renderizar la vista
    private function renderView($viewPath, $data = []) {
        extract($data); 
        
        require_once VIEWS_PATH . 'includes/header.php';
        require_once VIEWS_PATH . $viewPath . '.php';
        require_once VIEWS_PATH . 'includes/footer.php';
    }
}