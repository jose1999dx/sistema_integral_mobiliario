<?php
/**
 * Modelo Usuario
 */

class Usuario extends Model {
    protected $table = 'usuarios';
    protected $primaryKey = 'id';
    protected $fillable = [
        'username', 
        'email', 
        'password', 
        'nombre', 
        'rol', 
        'activo'
    ];
    
    /**
     * Verificar credenciales de usuario
     */
    public static function verificarCredenciales($username, $password) {
        $usuario = self::where('username', $username);
        
        if ($usuario && $usuario->activo) {
            if (password_verify($password, $usuario->password)) {
                return $usuario;
            }
        }
        
        return false;
    }
    
    /**
     * Crear hash de contraseña
     */
    public function setPassword($password) {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Obtener empleado asociado (si existe)
     */
    public function empleado() {
        // Esta relación se implementará después
        return null;
    }
    
    /**
     * Verificar si usuario tiene un rol específico
     */
    public function tieneRol($rol) {
        return $this->rol === $rol;
    }
    
    /**
     * Obtener usuarios por rol
     */
    public static function porRol($rol) {
        $instance = new static();
        $sql = "SELECT * FROM {$instance->table} WHERE rol = :rol AND activo = 1";
        
        try {
            $stmt = $instance->db->prepare($sql);
            $stmt->execute(['rol' => $rol]);
            $results = $stmt->fetchAll();
            
            $usuarios = [];
            foreach ($results as $data) {
                $usuario = new static();
                $usuario->fill($data);
                $usuarios[] = $usuario;
            }
            
            return $usuarios;
            
        } catch (PDOException $e) {
            error_log("Error al obtener usuarios por rol: " . $e->getMessage());
            return [];
        }
    }
}