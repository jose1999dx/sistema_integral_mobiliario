<?php
// SISTEMA_INTEGRAL_MOBILIARIO/index.php (Router Principal)

// 1. Definiciones de Rutas
define('ROOT_PATH', __DIR__ . '/');
define('APP_PATH', ROOT_PATH . 'app/');
define('VIEWS_PATH', ROOT_PATH . 'views/');

// 2. Incluir archivos base (ajustando a la nueva estructura)
require_once ROOT_PATH . 'config/database.php';
require_once APP_PATH . 'models/Presupuesto/PresupuestoModel.php'; 
require_once APP_PATH . 'controllers/Presupuesto/PresupuestoController.php';

// 3. Simple Router
$request_uri = trim($_SERVER['REQUEST_URI'], '/');
$base_path_segment = 'sistema_integral_mobiliario'; 
if (substr($request_uri, 0, strlen($base_path_segment)) == $base_path_segment) {
    $request_uri = trim(substr($request_uri, strlen($base_path_segment)), '/');
}

$segments = explode('/', $request_uri);
$module_name = !empty($segments[0]) ? ucfirst($segments[0]) : 'Presupuesto'; // Ej: Presupuesto
$action_name = !empty($segments[1]) ? $segments[1] : 'index'; // Ej: crear
$id = !empty($segments[2]) ? $segments[2] : null;

// Ejecución
try {
    // 1. Construir el nombre de la clase con su namespace completo
    // Ej: \Controller\Presupuesto\PresupuestoController
    $controller_class = "\\Controller\\" . $module_name . "\\" . $module_name . "Controller";
    
    if (!class_exists($controller_class)) {
        throw new Exception("Controlador no encontrado en el módulo: {$module_name}");
    }

    $controller = new $controller_class();
    
    if (method_exists($controller, $action_name)) {
        $controller->$action_name($id);
    } else {
        throw new Exception("Acción no encontrada: {$action_name}");
    }

} catch (Exception $e) {
    http_response_code(404);
    echo "<h1>Error 404 - No Encontrado</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}

\Config\Database::closeConnection(); 
?>