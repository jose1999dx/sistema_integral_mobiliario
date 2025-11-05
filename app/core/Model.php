<?php
/**
 * Clase Base Model
 * Proporciona funcionalidad CRUD básica para todos los modelos
 */

class Model {
    protected $table;
    protected $primaryKey = 'id';
    protected $attributes = [];
    protected $fillable = [];
    protected $db;
    
    public function __construct() {
        $this->db = $this->getDB();
    }
    
    /**
     * Obtener conexión a la base de datos
     */
    protected function getDB() {
        require_once ROOT_PATH . '/config/database.php';
        $database = new Database();
        return $database->getConnection();
    }
    
    /**
     * Magic setter para atributos
     */
    public function __set($name, $value) {
        $this->attributes[$name] = $value;
    }
    
    /**
     * Magic getter para atributos
     */
    public function __get($name) {
        return $this->attributes[$name] ?? null;
    }
    
    /**
     * Guardar modelo (insertar o actualizar)
     */
    public function save() {
        if (empty($this->attributes)) {
            throw new Exception("No hay datos para guardar");
        }
        
        // Filtrar atributos fillable
        $data = [];
        foreach ($this->attributes as $key => $value) {
            if (in_array($key, $this->fillable) || empty($this->fillable)) {
                $data[$key] = $value;
            }
        }
        
        if (empty($data)) {
            throw new Exception("No hay datos válidos para guardar");
        }
        
        if (isset($this->attributes[$this->primaryKey])) {
            // UPDATE
            return $this->update($data);
        } else {
            // INSERT
            return $this->insert($data);
        }
    }
    
    /**
     * Insertar nuevo registro
     */
    protected function insert($data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($data);
            
            // Obtener el ID insertado
            $this->attributes[$this->primaryKey] = $this->db->lastInsertId();
            return true;
            
        } catch (PDOException $e) {
            error_log("Error al insertar: " . $e->getMessage());
            throw new Exception("Error al guardar el registro");
        }
    }
    
    /**
     * Actualizar registro existente
     */
    protected function update($data) {
        $id = $this->attributes[$this->primaryKey];
        $setParts = [];
        
        foreach ($data as $column => $value) {
            if ($column !== $this->primaryKey) {
                $setParts[] = "$column = :$column";
            }
        }
        
        $setClause = implode(', ', $setParts);
        $sql = "UPDATE {$this->table} SET $setClause WHERE {$this->primaryKey} = :id";
        
        try {
            $data['id'] = $id;
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($data);
            
        } catch (PDOException $e) {
            error_log("Error al actualizar: " . $e->getMessage());
            throw new Exception("Error al actualizar el registro");
        }
    }
    
    /**
     * Eliminar registro
     */
    public function delete() {
        if (!isset($this->attributes[$this->primaryKey])) {
            throw new Exception("No se puede eliminar un registro sin ID");
        }
        
        $id = $this->attributes[$this->primaryKey];
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['id' => $id]);
            
        } catch (PDOException $e) {
            error_log("Error al eliminar: " . $e->getMessage());
            throw new Exception("Error al eliminar el registro");
        }
    }
    
    /**
     * Buscar por ID
     */
    public static function find($id) {
        $instance = new static();
        $sql = "SELECT * FROM {$instance->table} WHERE {$instance->primaryKey} = :id LIMIT 1";
        
        try {
            $stmt = $instance->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            $result = $stmt->fetch();
            
            if ($result) {
                $instance->fill($result);
                return $instance;
            }
            
            return null;
            
        } catch (PDOException $e) {
            error_log("Error al buscar: " . $e->getMessage());
            throw new Exception("Error al buscar el registro");
        }
    }
    
    /**
     * Obtener todos los registros
     */
    public static function all() {
        $instance = new static();
        $sql = "SELECT * FROM {$instance->table}";
        
        try {
            $stmt = $instance->db->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            $collection = [];
            foreach ($results as $data) {
                $model = new static();
                $model->fill($data);
                $collection[] = $model;
            }
            
            return $collection;
            
        } catch (PDOException $e) {
            error_log("Error al obtener todos: " . $e->getMessage());
            throw new Exception("Error al obtener los registros");
        }
    }
    
    /**
     * Buscar con condición WHERE
     */
    public static function where($column, $value) {
        $instance = new static();
        $sql = "SELECT * FROM {$instance->table} WHERE $column = :value";
        
        try {
            $stmt = $instance->db->prepare($sql);
            $stmt->execute(['value' => $value]);
            $result = $stmt->fetch();
            
            if ($result) {
                $instance->fill($result);
                return $instance;
            }
            
            return null;
            
        } catch (PDOException $e) {
            error_log("Error en WHERE: " . $e->getMessage());
            throw new Exception("Error en la consulta");
        }
    }
    
    /**
     * Llenar modelo con datos
     */
    public function fill($data) {
        foreach ($data as $key => $value) {
            $this->attributes[$key] = $value;
        }
        return $this;
    }
    
    /**
     * Convertir a array
     */
    public function toArray() {
        return $this->attributes;
    }
    
    /**
     * Obtener el último error
     */
    public function getLastError() {
        return $this->db->errorInfo();
    }
}