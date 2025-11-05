<?php
/**
 * Controlador de Empleados - CRUD Real con BD
 */

class EmpleadoController {
    
    // ... (mantener los métodos existentes y agregar estos)
    
    /**
     * Listar empleados con datos reales de BD
     */
    public function index() {
        AuthMiddleware::checkAuth();
        
        // Obtener filtros
        $filtros = [
            'departamento_id' => $_GET['departamento_id'] ?? null,
            'search' => $_GET['search'] ?? null
        ];
        
        // Obtener datos para filtros
        $departamentos = $this->obtenerDepartamentos();
        $empleados = Empleado::buscar($filtros);
        $estadisticas = Empleado::obtenerEstadisticas();
        
        $data = [
            'titulo' => 'Gestión de Empleados',
            'usuario' => Auth::user(),
            'base_url' => BASE_URL,
            'empleados' => $empleados,
            'departamentos' => $departamentos,
            'estadisticas' => $estadisticas,
            'filtros' => $filtros
        ];
        
        view('recursos_humanos/empleados/index', $data);
    }
    
    /**
     * Mostrar formulario para crear empleado
     */
    public function crear() {
        AuthMiddleware::checkAuth();
        
        $data = [
            'titulo' => 'Nuevo Empleado',
            'usuario' => Auth::user(),
            'base_url' => BASE_URL,
            'departamentos' => $this->obtenerDepartamentos(),
            'puestos' => $this->obtenerPuestos(),
            'codigo_empleado' => Empleado::generarCodigo(),
            'errors' => []
        ];
        
        view('recursos_humanos/empleados/crear', $data);
    }
    
    /**
     * Guardar nuevo empleado
     */
    public function guardar() {
        AuthMiddleware::checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/rh/empleados?error=metodo_no_permitido');
        }
        
        try {
            $empleado = new Empleado();
            
            // Asignar datos
            $empleado->codigo_empleado = $_POST['codigo_empleado'];
            $empleado->nombre = trim($_POST['nombre']);
            $empleado->apellidos = trim($_POST['apellidos']);
            $empleado->email = trim($_POST['email']);
            $empleado->telefono = trim($_POST['telefono']);
            $empleado->departamento_id = $_POST['departamento_id'];
            $empleado->puesto_id = $_POST['puesto_id'];
            $empleado->fecha_contratacion = $_POST['fecha_contratacion'];
            $empleado->salario_base = $_POST['salario_base'];
            $empleado->tipo_contrato = $_POST['tipo_contrato'];
            $empleado->activo = 1;
            
            // Guardar en BD
            if ($empleado->save()) {
                $this->redirect('/rh/empleados?success=empleado_creado');
            } else {
                $this->redirect('/rh/empleados?error=error_guardar');
            }
            
        } catch (Exception $e) {
            error_log("Error al guardar empleado: " . $e->getMessage());
            $this->redirect('/rh/empleados?error=error_sistema');
        }
    }
    
    /**
     * Mostrar formulario para editar empleado
     */
    public function editar($id) {
        AuthMiddleware::checkAuth();
        
        $empleado = Empleado::find($id);
        if (!$empleado) {
            $this->redirect('/rh/empleados?error=empleado_no_encontrado');
        }
        
        $data = [
            'titulo' => 'Editar Empleado',
            'usuario' => Auth::user(),
            'base_url' => BASE_URL,
            'empleado' => $empleado,
            'departamentos' => $this->obtenerDepartamentos(),
            'puestos' => $this->obtenerPuestos(),
            'errors' => []
        ];
        
        view('recursos_humanos/empleados/editar', $data);
    }
    
    /**
     * Actualizar empleado existente
     */
    public function actualizar($id) {
        AuthMiddleware::checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/rh/empleados?error=metodo_no_permitido');
        }
        
        try {
            $empleado = Empleado::find($id);
            if (!$empleado) {
                $this->redirect('/rh/empleados?error=empleado_no_encontrado');
            }
            
            // Actualizar datos
            $empleado->nombre = trim($_POST['nombre']);
            $empleado->apellidos = trim($_POST['apellidos']);
            $empleado->email = trim($_POST['email']);
            $empleado->telefono = trim($_POST['telefono']);
            $empleado->departamento_id = $_POST['departamento_id'];
            $empleado->puesto_id = $_POST['puesto_id'];
            $empleado->fecha_contratacion = $_POST['fecha_contratacion'];
            $empleado->salario_base = $_POST['salario_base'];
            $empleado->tipo_contrato = $_POST['tipo_contrato'];
            
            if ($empleado->save()) {
                $this->redirect('/rh/empleados?success=empleado_actualizado');
            } else {
                $this->redirect("/rh/empleados/editar/$id?error=error_actualizar");
            }
            
        } catch (Exception $e) {
            error_log("Error al actualizar empleado: " . $e->getMessage());
            $this->redirect("/rh/empleados/editar/$id?error=error_sistema");
        }
    }
    
    /**
     * Eliminar empleado (soft delete)
     */
    public function eliminar($id) {
        AuthMiddleware::checkAuth();
        
        try {
            $empleado = Empleado::find($id);
            if (!$empleado) {
                $this->redirect('/rh/empleados?error=empleado_no_encontrado');
            }
            
            $empleado->activo = 0;
            if ($empleado->save()) {
                $this->redirect('/rh/empleados?success=empleado_eliminado');
            } else {
                $this->redirect('/rh/empleados?error=error_eliminar');
            }
            
        } catch (Exception $e) {
            error_log("Error al eliminar empleado: " . $e->getMessage());
            $this->redirect('/rh/empleados?error=error_sistema');
        }
    }
    
    /**
     * Obtener lista de departamentos
     */
    private function obtenerDepartamentos() {
        try {
            $db = (new Database())->getConnection();
            $sql = "SELECT * FROM departamentos WHERE activo = 1 ORDER BY nombre";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener departamentos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener lista de puestos
     */
    private function obtenerPuestos() {
        try {
            $db = (new Database())->getConnection();
            $sql = "SELECT * FROM puestos WHERE activo = 1 ORDER BY nombre";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener puestos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Redirección
     */
    private function redirect($path) {
        $url = BASE_URL . '/index.php?url=' . ltrim($path, '/');
        header("Location: $url");
        exit;
    }
}