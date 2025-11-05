<?php
/**
 * Modelo Empleado - Con conexión a BD real
 */

class Empleado extends Model {
    protected $table = 'empleados';
    protected $primaryKey = 'id';
    protected $fillable = [
        'codigo_empleado', 'nombre', 'apellidos', 'email', 'telefono', 
        'direccion', 'fecha_nacimiento', 'genero', 'departamento_id',
        'puesto_id', 'fecha_contratacion', 'salario_base', 'tipo_contrato', 'activo'
    ];
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Obtener departamento del empleado
     */
    public function departamento() {
        $sql = "SELECT d.* FROM departamentos d WHERE d.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->departamento_id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    
    /**
     * Obtener puesto del empleado
     */
    public function puesto() {
        $sql = "SELECT p.* FROM puestos p WHERE p.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->puesto_id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    
    /**
     * Buscar empleados con filtros
     */
    public static function buscar($filtros = []) {
        $instance = new static();
        
        $sql = "SELECT e.*, d.nombre as departamento_nombre, p.nombre as puesto_nombre 
                FROM empleados e 
                LEFT JOIN departamentos d ON e.departamento_id = d.id 
                LEFT JOIN puestos p ON e.puesto_id = p.id 
                WHERE e.activo = 1";
        
        $params = [];
        
        if (!empty($filtros['departamento_id'])) {
            $sql .= " AND e.departamento_id = ?";
            $params[] = $filtros['departamento_id'];
        }
        
        if (!empty($filtros['search'])) {
            $sql .= " AND (e.nombre LIKE ? OR e.apellidos LIKE ? OR e.codigo_empleado LIKE ?)";
            $searchTerm = "%{$filtros['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY e.nombre, e.apellidos";
        
        try {
            $stmt = $instance->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $collection = [];
            foreach ($results as $data) {
                $empleado = new static();
                $empleado->fill($data);
                $collection[] = $empleado;
            }
            
            return $collection;
            
        } catch (PDOException $e) {
            error_log("Error al buscar empleados: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Generar próximo código de empleado
     */
    public static function generarCodigo() {
        $instance = new static();
        $sql = "SELECT COUNT(*) as total FROM empleados";
        $stmt = $instance->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $numero = ($result['total'] ?? 0) + 1;
        return 'EMP-' . str_pad($numero, 3, '0', STR_PAD_LEFT);
    }
    
    /**
     * Obtener estadísticas de empleados
     */
    public static function obtenerEstadisticas() {
        $instance = new static();
        
        $stats = [
            'total_empleados' => 0,
            'por_departamento' => [],
            'contratos' => [
                'Indeterminado' => 0,
                'Temporal' => 0,
                'Practicas' => 0,
                'Formacion' => 0
            ]
        ];
        
        try {
            // Total empleados
            $sql = "SELECT COUNT(*) as total FROM empleados WHERE activo = 1";
            $stmt = $instance->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_empleados'] = $result['total'] ?? 0;
            
            // Por departamento
            $sql = "SELECT d.nombre, COUNT(e.id) as total 
                    FROM empleados e 
                    JOIN departamentos d ON e.departamento_id = d.id 
                    WHERE e.activo = 1 
                    GROUP BY d.id, d.nombre";
            $stmt = $instance->db->prepare($sql);
            $stmt->execute();
            $stats['por_departamento'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Por tipo de contrato
            $sql = "SELECT tipo_contrato, COUNT(*) as total 
                    FROM empleados 
                    WHERE activo = 1 
                    GROUP BY tipo_contrato";
            $stmt = $instance->db->prepare($sql);
            $stmt->execute();
            $contratos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($contratos as $contrato) {
                if (isset($stats['contratos'][$contrato['tipo_contrato']])) {
                    $stats['contratos'][$contrato['tipo_contrato']] = $contrato['total'];
                }
            }
            
        } catch (PDOException $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
        }
        
        return $stats;
    }
}