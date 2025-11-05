<?php
/**
 * SISTEMA INTEGRAL MOBILIARIO
 * Front Controller - Versión Mejorada con Autoload Completo
 */

// =============================================
// CONFIGURACIÓN INICIAL
// =============================================

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración de entorno
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Constantes del sistema
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', __DIR__);
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');
define('BASE_URL', 'http://localhost/SISTEMA_INTEGRAL_MOBILIARIO/public');

// =============================================
// AUTOLOAD MEJORADO - CARGA TODAS LAS CLASES
// =============================================

// Función de autoload mejorada
spl_autoload_register(function($className) {
    // Convertir namespace a ruta de archivo
    $classPath = str_replace('\\', '/', $className);
    
    // Todas las posibles ubicaciones de clases
    $possiblePaths = [
        // Clases core
        APP_PATH . '/core/' . $className . '.php',
        
        // Modelos base y por módulos
        APP_PATH . '/models/' . $className . '.php',
        APP_PATH . '/models/recursos_humanos/' . $className . '.php',
        APP_PATH . '/models/presupuesto/' . $className . '.php',
        APP_PATH . '/models/ingenieria/' . $className . '.php',
        APP_PATH . '/models/proyectos/' . $className . '.php',
        APP_PATH . '/models/calidad/' . $className . '.php',
        APP_PATH . '/models/sistemas/' . $className . '.php',
        APP_PATH . '/models/produccion_madera/' . $className . '.php',
        APP_PATH . '/models/produccion_metal/' . $className . '.php',
        APP_PATH . '/models/compras/' . $className . '.php',
        APP_PATH . '/models/transportes/' . $className . '.php',
        APP_PATH . '/models/residencias/' . $className . '.php',
        APP_PATH . '/models/almacen/' . $className . '.php',
        APP_PATH . '/models/mantenimiento/' . $className . '.php',
        APP_PATH . '/models/diseño/' . $className . '.php',
        APP_PATH . '/models/contabilidad/' . $className . '.php',
        APP_PATH . '/models/vigilancia/' . $className . '.php',
        APP_PATH . '/models/planeacion/' . $className . '.php',
        
        // Controladores por módulos
        APP_PATH . '/controllers/' . $className . '.php',
        APP_PATH . '/controllers/auth/' . $className . '.php',
        APP_PATH . '/controllers/recursos_humanos/' . $className . '.php',
        APP_PATH . '/controllers/presupuesto/' . $className . '.php',
        APP_PATH . '/controllers/ingenieria/' . $className . '.php',
        APP_PATH . '/controllers/proyectos/' . $className . '.php',
        APP_PATH . '/controllers/calidad/' . $className . '.php',
        APP_PATH . '/controllers/sistemas/' . $className . '.php',
        APP_PATH . '/controllers/produccion_madera/' . $className . '.php',
        APP_PATH . '/controllers/produccion_metal/' . $className . '.php',
        APP_PATH . '/controllers/compras/' . $className . '.php',
        APP_PATH . '/controllers/transportes/' . $className . '.php',
        APP_PATH . '/controllers/residencias/' . $className . '.php',
        APP_PATH . '/controllers/almacen/' . $className . '.php',
        APP_PATH . '/controllers/mantenimiento/' . $className . '.php',
        APP_PATH . '/controllers/diseño/' . $className . '.php',
        APP_PATH . '/controllers/contabilidad/' . $className . '.php',
        APP_PATH . '/controllers/vigilancia/' . $className . '.php',
        APP_PATH . '/controllers/planeacion/' . $className . '.php',
        APP_PATH . '/controllers/direccion/' . $className . '.php',
        
        // Middleware
        APP_PATH . '/middleware/' . $className . '.php',
        
        // Rutas alternativas
        APP_PATH . '/' . $classPath . '.php',
        ROOT_PATH . '/' . $classPath . '.php'
    ];
    
    // Intentar cargar desde cada ubicación posible
    foreach ($possiblePaths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
    
    // Log para debugging
    error_log("❌ Clase no encontrada: {$className}");
    error_log("🔍 Buscada en: " . implode(', ', array_slice($possiblePaths, 0, 5)) . "...");
});

// =============================================
// CARGAR CONFIGURACIÓN Y CLASES CORE MANUALMENTE
// =============================================

// Cargar configuración de base de datos
require_once ROOT_PATH . '/config/database.php';

// Cargar configuración de la aplicación
$config = [];
if (file_exists(ROOT_PATH . '/config/app.php')) {
    $config = require_once ROOT_PATH . '/config/app.php';
}

// Cargar clases core manualmente para asegurar
$coreClasses = [
    '/core/Model.php',
    '/core/Auth.php', 
    '/core/Database.php',
    '/middleware/AuthMiddleware.php'
];

foreach ($coreClasses as $class) {
    $file = APP_PATH . $class;
    if (file_exists($file)) {
        require_once $file;
    } else {
        error_log("⚠️ Archivo core no encontrado: {$file}");
    }
}

// =============================================
// MANEJO DE ERRORES
// =============================================

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("Error [$errno]: $errstr en $errfile línea $errline");
    
    if (ini_get('display_errors')) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px;'>";
        echo "<strong>Error:</strong> $errstr<br>";
        echo "<small>Archivo: $errfile (Línea: $errline)</small>";
        echo "</div>";
    }
    
    return true;
});

// =============================================
// FUNCIONES AUXILIARES
// =============================================

/**
 * Redireccionar a una URL
 */
function redirect($url) {
    $fullUrl = BASE_URL . '/index.php?url=' . ltrim($url, '/');
    if (headers_sent()) {
        echo "<script>window.location.href = '{$fullUrl}';</script>";
        echo "<noscript><meta http-equiv='refresh' content='0;url={$fullUrl}'></noscript>";
    } else {
        header("Location: {$fullUrl}");
    }
    exit;
}

/**
 * Cargar una vista
 */
function view($viewPath, $data = []) {
    // Extraer variables
    extract($data);
    
    // Buscar la vista en diferentes ubicaciones
    $viewFile = APP_PATH . "/views/{$viewPath}.php";
    
    if (file_exists($viewFile)) {
        require_once $viewFile;
    } else {
        // Vista no encontrada - mostrar error amigable
        http_response_code(500);
        echo "<div style='text-align: center; padding: 50px; font-family: Arial, sans-serif;'>";
        echo "<h1>🧩 Vista No Encontrada</h1>";
        echo "<p>La vista <strong>{$viewPath}</strong> no existe.</p>";
        echo "<p><small>Archivo buscado: {$viewFile}</small></p>";
        echo "<a href='" . BASE_URL . "/index.php?url=dashboard' style='color: #007bff;'>Volver al Dashboard</a>";
        echo "</div>";
        error_log("Vista no encontrada: {$viewFile}");
    }
}

/**
 * Obtener URL completa
 */
function url($path = '') {
    return BASE_URL . '/index.php?url=' . ltrim($path, '/');
}

/**
 * Obtener ruta de asset
 */
function asset($path) {
    return BASE_URL . '/' . ltrim($path, '/');
}

// =============================================
// PROCESAMIENTO DE RUTAS
// =============================================

// Obtener la URL solicitada
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

// Debug información
if (ini_get('display_errors')) {
    error_log("=== 🌐 ROUTING DEBUG ===");
    error_log("📨 Request URI: {$requestUri}");
    error_log("📜 Script Name: {$scriptName}");
}

// Determinar la ruta actual
$currentRoute = '';

// Caso 1: Acceso directo a index.php (sin parámetros)
if ($requestUri === BASE_URL . '/' || $requestUri === BASE_URL . '/index.php' || $requestUri === '/SISTEMA_INTEGRAL_MOBILIARIO/public/') {
    $currentRoute = '';
} 
// Caso 2: Acceso con parámetros GET
elseif (isset($_GET['url'])) {
    $currentRoute = trim($_GET['url'], '/');
}
// Caso 3: Intentando acceso con mod_rewrite (fallback)
else {
    $basePath = dirname($scriptName);
    $currentRoute = $requestUri;
    
    if ($basePath !== '/' && strpos($requestUri, $basePath) === 0) {
        $currentRoute = substr($requestUri, strlen($basePath));
    }
    
    $currentRoute = trim($currentRoute, '/');
    
    // Remover BASE_URL de la ruta si está presente
    if (strpos($currentRoute, trim(BASE_URL, '/')) === 0) {
        $currentRoute = substr($currentRoute, strlen(trim(BASE_URL, '/')));
        $currentRoute = trim($currentRoute, '/');
    }
}

// Limpiar la ruta final
$currentRoute = explode('?', $currentRoute)[0];
$currentRoute = trim($currentRoute, '/');

// Debug final
if (ini_get('display_errors')) {
    error_log("🎯 Current Route: '{$currentRoute}'");
    error_log("=====================");
}

// =============================================
// SISTEMA DE RUTAS PRINCIPAL
// =============================================

try {
    // RUTA POR DEFECTO: MOSTRAR LOGIN
    if ($currentRoute === '') {
        if (Auth::check()) {
            redirect('/dashboard');
        } else {
            redirect('/login');
        }
        exit;
    }

    // MAPEO DE RUTAS DISPONIBLES
    $routes = [
        // ========== AUTENTICACIÓN ==========
        'login' => [
            'controller' => 'AuthController',
            'method' => 'login',
            'auth' => false,
            'post_method' => 'procesarLogin'
        ],
        'logout' => [
            'controller' => 'AuthController', 
            'method' => 'logout',
            'auth' => false
        ],
        'acceso-denegado' => [
            'controller' => 'AuthController',
            'method' => 'accesoDenegado', 
            'auth' => true
        ],
            
        // ========== DASHBOARD ==========
        'dashboard' => [
            'controller' => 'DashboardController',
            'method' => 'index', 
            'auth' => true
        ],
            
        // ========== RECURSOS HUMANOS ==========
        'rh/empleados' => [
            'controller' => 'EmpleadoController',
            'method' => 'index',
            'auth' => true
        ],
        'rh/empleados/crear' => [
            'controller' => 'EmpleadoController',
            'method' => 'crear',
            'auth' => true
        ],
        'rh/empleados/guardar' => [
            'controller' => 'EmpleadoController',
            'method' => 'guardar',
            'auth' => true
        ],
        'rh/empleados/editar/(\d+)' => [
            'controller' => 'EmpleadoController',
            'method' => 'editar',
            'auth' => true
        ],
        'rh/empleados/actualizar/(\d+)' => [
            'controller' => 'EmpleadoController', 
            'method' => 'actualizar',
            'auth' => true
        ],
        'rh/empleados/eliminar/(\d+)' => [
            'controller' => 'EmpleadoController',
            'method' => 'eliminar',
            'auth' => true
        ]
    ];

    // VERIFICAR SI LA RUTA EXISTE
    $routeFound = false;
    $routeParams = [];
    
    foreach ($routes as $routePattern => $routeConfig) {
        // Convertir patrón a regex para parámetros
        $pattern = str_replace('/', '\/', $routePattern);
        $pattern = preg_replace('/\(\\\\d\+\)/', '(\d+)', $pattern);
        $pattern = "/^{$pattern}$/";
        
        if (preg_match($pattern, $currentRoute, $matches)) {
            $routeFound = true;
            array_shift($matches); // Remover el match completo
            $routeParams = $matches;
            break;
        }
    }
    
    if ($routeFound) {
        $route = $routes[$routePattern];
        
        // Verificar autenticación si es requerida
        if ($route['auth'] && !Auth::check()) {
            redirect('/login?error=debes_iniciar_sesion');
        }
        
        // Determinar el método a ejecutar
        $method = $route['method'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($route['post_method'])) {
            $method = $route['post_method'];
        }
        
        // Determinar la carpeta del controlador
        $controllerFolder = '';
        if (strpos($routePattern, 'rh/') === 0) {
            $controllerFolder = 'recursos_humanos/';
        } elseif ($routePattern === 'login' || $routePattern === 'logout' || $routePattern === 'acceso-denegado') {
            $controllerFolder = 'auth/';
        } elseif ($routePattern === 'dashboard') {
            $controllerFolder = 'direccion/';
        }
        
        // Construir ruta del controlador
        $controllerFile = APP_PATH . '/controllers/' . $controllerFolder . $route['controller'] . '.php';
        $controllerClass = $route['controller'];
        
        // Verificar si el archivo del controlador existe
        if (!file_exists($controllerFile)) {
            throw new Exception("Controlador no encontrado: {$controllerFile}");
        }
        
        // Cargar y ejecutar el controlador
        require_once $controllerFile;
        
        if (!class_exists($controllerClass)) {
            throw new Exception("Clase {$controllerClass} no encontrada en: {$controllerFile}");
        }
        
        $controller = new $controllerClass();
        
        if (!method_exists($controller, $method)) {
            throw new Exception("Método {$method} no existe en {$controllerClass}");
        }
        
        // Llamar al método con parámetros si los hay
        call_user_func_array([$controller, $method], $routeParams);
        
    } else {
        // RUTA NO ENCONTRADA - 404
        http_response_code(404);
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>404 - Página No Encontrada</title>
            <style>
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0;
                    color: white;
                }
                .error-container {
                    background: rgba(255,255,255,0.1);
                    padding: 3rem;
                    border-radius: 20px;
                    backdrop-filter: blur(10px);
                    text-align: center;
                    max-width: 500px;
                    box-shadow: 0 15px 35px rgba(0,0,0,0.2);
                }
                h1 { font-size: 4rem; margin: 0 0 1rem 0; }
                .btn {
                    display: inline-block;
                    padding: 12px 25px;
                    background: white;
                    color: #667eea;
                    text-decoration: none;
                    border-radius: 50px;
                    font-weight: 600;
                    margin: 10px;
                    transition: transform 0.3s;
                }
                .btn:hover {
                    transform: translateY(-2px);
                }
                .debug-info {
                    margin-top: 2rem;
                    padding: 1.5rem;
                    background: rgba(0,0,0,0.2);
                    border-radius: 10px;
                    text-align: left;
                    font-family: 'Courier New', monospace;
                    font-size: 0.9rem;
                }
            </style>
        </head>
        <body>
            <div class="error-container">
                <h1>404</h1>
                <h2>Página No Encontrada</h2>
                <p>La página <strong>"<?= htmlspecialchars($currentRoute) ?>"</strong> no existe.</p>
                
                <div>
                    <a href="<?= url('/login') ?>" class="btn">📱 Ir al Login</a>
                    <a href="<?= url('/dashboard') ?>" class="btn">📊 Ir al Dashboard</a>
                </div>
                
                <?php if (ini_get('display_errors')): ?>
                <div class="debug-info">
                    <strong>🔧 Información de Debug:</strong><br>
                    <strong>URL solicitada:</strong> <?= htmlspecialchars($requestUri) ?><br>
                    <strong>Ruta procesada:</strong> <?= htmlspecialchars($currentRoute) ?><br>
                    <strong>Script:</strong> <?= htmlspecialchars($scriptName) ?><br>
                    <strong>Método:</strong> <?= htmlspecialchars($_SERVER['REQUEST_METHOD']) ?>
                </div>
                <?php endif; ?>
            </div>
        </body>
        </html>
        <?php
    }
    
} catch (Exception $e) {
    // MANEJO DE ERRORES GLOBALES
    error_log("💥 ERROR GLOBAL: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
    
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error del Sistema</title>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
                height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                margin: 0;
            }
            .error-container {
                background: rgba(255,255,255,0.1);
                padding: 3rem;
                border-radius: 20px;
                backdrop-filter: blur(10px);
                text-align: center;
                max-width: 700px;
                box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            }
            .error-icon { font-size: 4rem; margin-bottom: 1rem; }
            h1 { font-size: 2.5rem; margin-bottom: 1rem; }
            .btn {
                display: inline-block;
                padding: 12px 25px;
                background: white;
                color: #ff6b6b;
                text-decoration: none;
                border-radius: 50px;
                font-weight: 600;
                margin-top: 1rem;
                transition: transform 0.3s ease;
            }
            .btn:hover { transform: translateY(-2px); }
            .debug-details {
                background: rgba(0,0,0,0.2);
                padding: 1.5rem;
                border-radius: 10px;
                margin-top: 2rem;
                text-align: left;
                font-family: 'Courier New', monospace;
                font-size: 0.9rem;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon">💥</div>
            <h1>Error del Sistema</h1>
            <p><strong>Ha ocurrido un error inesperado:</strong></p>
            <p><?= htmlspecialchars($e->getMessage()) ?></p>
            
            <a href="<?= url('/login') ?>" class="btn">🔄 Volver al Login</a>
            
            <?php if (ini_get('display_errors')): ?>
            <div class="debug-details">
                <strong>Detalles técnicos:</strong><br>
                <strong>Archivo:</strong> <?= $e->getFile() ?> (Línea: <?= $e->getLine() ?>)<br>
                <strong>Trace:</strong><br>
                <pre style="color: white; margin-top: 10px; overflow: auto;"><?= htmlspecialchars($e->getTraceAsString()) ?></pre>
            </div>
            <?php else: ?>
            <p style="margin-top: 1rem; font-size: 0.9rem; opacity: 0.8;">
                Si el problema persiste, contacte al administrador del sistema.
            </p>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
}