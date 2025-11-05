<?php
/**
 * Controlador de Autenticación - Versión Mejorada
 * Soporte para BD real + credenciales de desarrollo
 */

class AuthController {
    
    /**
     * Mostrar formulario de login
     */
    public function login() {
        // Si ya está logueado, redirigir al dashboard
        if (Auth::check()) {
            redirect('/dashboard');
        }
        
        $data = [
            'titulo' => 'Login - Sistema Integral Mobiliario',
            'error' => $_GET['error'] ?? null,
            'success' => $_GET['success'] ?? null,
            'base_url' => BASE_URL
        ];
        
        view('auth/login', $data);
    }
    
    /**
     * Procesar login - Soporte para BD real y desarrollo
     */
    public function procesarLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/login?error=metodo_no_permitido');
        }
        
        // Validar datos
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            redirect('/login?error=campos_requeridos');
        }
        
        try {
            // PRIMERO: Intentar con credenciales de desarrollo (temporal)
            $usuario = $this->verificarCredencialesDesarrollo($username, $password);
            
            if ($usuario) {
                Auth::login($usuario);
                $this->registrarIntentoLogin($username, true, 'desarrollo');
                redirect('/dashboard');
            }
            
            // SEGUNDO: Intentar con base de datos real
            $usuario = $this->verificarCredencialesBD($username, $password);
            
            if ($usuario) {
                Auth::login($usuario);
                $this->registrarIntentoLogin($username, true, 'bd_real');
                redirect('/dashboard');
            }
            
            // Si fallan ambas opciones
            $this->registrarIntentoLogin($username, false, 'fallo');
            redirect('/login?error=credenciales_invalidas');
            
        } catch (Exception $e) {
            error_log("Error en login: " . $e->getMessage());
            $this->registrarIntentoLogin($username, false, 'error_sistema');
            redirect('/login?error=error_sistema');
        }
    }
    
    /**
     * Verificar credenciales de desarrollo (temporal)
     */
    private function verificarCredencialesDesarrollo($username, $password) {
        $credencialesDesarrollo = [
            'admin' => [
                'password' => 'admin123',
                'datos' => [
                    'id' => 1,
                    'username' => 'admin',
                    'nombre' => 'Administrador del Sistema',
                    'rol' => 'super_admin',
                    'email' => 'admin@sistemamobiliario.com'
                ]
            ],
            'carlos.martinez' => [
                'password' => 'admin123',
                'datos' => [
                    'id' => 2,
                    'username' => 'carlos.martinez',
                    'nombre' => 'Carlos Martínez López',
                    'rol' => 'director',
                    'email' => 'carlos.martinez@empresa.com'
                ]
            ],
            'ana.garcia' => [
                'password' => 'admin123', 
                'datos' => [
                    'id' => 3,
                    'username' => 'ana.garcia',
                    'nombre' => 'Ana García Rodríguez',
                    'rol' => 'gerente_rh',
                    'email' => 'ana.garcia@empresa.com'
                ]
            ],
            'luis.hernandez' => [
                'password' => 'admin123',
                'datos' => [
                    'id' => 4,
                    'username' => 'luis.hernandez',
                    'nombre' => 'Luis Hernández Pérez',
                    'rol' => 'empleado',
                    'email' => 'luis.hernandez@empresa.com'
                ]
            ]
        ];
        
        if (isset($credencialesDesarrollo[$username]) && 
            $password === $credencialesDesarrollo[$username]['password']) {
            
            $usuario = new stdClass();
            foreach ($credencialesDesarrollo[$username]['datos'] as $propiedad => $valor) {
                $usuario->$propiedad = $valor;
            }
            
            return $usuario;
        }
        
        return false;
    }
    
    /**
     * Verificar credenciales en base de datos real
     */
    private function verificarCredencialesBD($username, $password) {
        try {
            // Verificar si la clase Usuario existe
            if (!class_exists('Usuario')) {
                error_log("Clase Usuario no encontrada para autenticación BD");
                return false;
            }
            
            $usuario = Usuario::verificarCredenciales($username, $password);
            return $usuario;
            
        } catch (Exception $e) {
            error_log("Error en verificación BD: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Registrar intento de login (para debugging)
     */
    private function registrarIntentoLogin($username, $exitoso, $tipo = 'desconocido') {
        $estado = $exitoso ? 'EXITOSO' : 'FALLIDO';
        $mensaje = "🔐 Login {$estado} - Usuario: {$username} - Tipo: {$tipo}";
        
        if ($exitoso) {
            error_log("✅ " . $mensaje);
        } else {
            error_log("❌ " . $mensaje);
        }
    }
    
    /**
     * Cerrar sesión
     */
    public function logout() {
        if (Auth::check()) {
            $usuario = Auth::user();
            error_log("🚪 Logout - Usuario: {$usuario['username']}");
        }
        
        Auth::logout();
        redirect('/login?success=sesion_cerrada');
    }
    
    /**
     * Mostrar página de acceso denegado
     */
    public function accesoDenegado() {
        if (!Auth::check()) {
            redirect('/login');
        }
        
        $data = [
            'titulo' => 'Acceso Denegado',
            'mensaje' => 'No tienes permisos para acceder a esta página.',
            'usuario' => Auth::user(),
            'base_url' => BASE_URL
        ];
        
        view('auth/acceso_denegado', $data);
    }
    
    /**
     * Redirección mejorada
     */
    private function redirect($path) {
        $url = BASE_URL . '/index.php?url=' . ltrim($path, '/');
        
        if (headers_sent()) {
            echo "<script>window.location.href = '{$url}';</script>";
            echo "<noscript><meta http-equiv='refresh' content='0;url={$url}'></noscript>";
        } else {
            header("Location: {$url}");
        }
        exit;
    }
    
    /**
     * Obtener URL de redirección según rol (mejorado)
     */
    private function getRedirectUrlByRole($rol) {
        $redirecciones = [
            'super_admin' => '/dashboard',
            'director' => '/dashboard',
            'gerente_rh' => '/rh/empleados',
            'empleado' => '/portal'
        ];
        
        return $redirecciones[$rol] ?? '/dashboard';
    }
    
    /**
     * Verificar estado de la base de datos (para debugging)
     */
    public function estadoSistema() {
        // Solo accesible para administradores
        if (!Auth::check() || Auth::user()['rol'] !== 'super_admin') {
            $this->accesoDenegado();
            return;
        }
        
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Estado del Sistema</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                .card { background: white; padding: 20px; margin: 10px 0; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
                .success { border-left: 5px solid #28a745; }
                .warning { border-left: 5px solid #ffc107; }
                .error { border-left: 5px solid #dc3545; }
            </style>
        </head>
        <body>
            <h1>🔧 Estado del Sistema</h1>";
        
        // Verificar BD
        try {
            $database = new Database();
            $conn = $database->getConnection();
            echo "<div class='card success'>✅ Conexión a BD: EXITOSA</div>";
            
            // Contar registros
            $tablas = ['usuarios', 'empleados', 'departamentos', 'puestos'];
            foreach ($tablas as $tabla) {
                $stmt = $conn->query("SELECT COUNT(*) as total FROM {$tabla}");
                $result = $stmt->fetch();
                echo "<div class='card'>📊 {$tabla}: {$result['total']} registros</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='card error'>❌ Conexión a BD: FALLIDA - {$e->getMessage()}</div>";
        }
        
        // Verificar clases
        $clases = ['Auth', 'Usuario', 'Empleado', 'Database'];
        foreach ($clases as $clase) {
            if (class_exists($clase)) {
                echo "<div class='card success'>✅ Clase {$clase}: CARGADA</div>";
            } else {
                echo "<div class='card error'>❌ Clase {$clase}: NO ENCONTRADA</div>";
            }
        }
        
        echo "<div class='card warning'>
                <h3>🔐 Credenciales de Desarrollo Activas</h3>
                <p>Estas credenciales funcionan sin BD:</p>
                <ul>
                    <li><strong>admin</strong> / admin123 (Super Admin)</li>
                    <li><strong>carlos.martinez</strong> / admin123 (Director)</li>
                    <li><strong>ana.garcia</strong> / admin123 (Gerente RH)</li>
                    <li><strong>luis.hernandez</strong> / admin123 (Empleado)</li>
                </ul>
              </div>
              
              <a href='".BASE_URL."/index.php?url=dashboard' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px;'>Volver al Dashboard</a>
              
        </body>
        </html>";
    }
}