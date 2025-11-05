<?php
require_once 'config/database.php';

echo "<h1>🧪 Test de Conexión a Base de Datos</h1>";
echo "<style>body { font-family: Arial; padding: 20px; } .success { color: green; } .error { color: red; }</style>";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "<p class='success'>✅ Conexión a MySQL exitosa</p>";
    
    // Test de consultas
    $tests = [
        "SELECT COUNT(*) as total FROM departamentos" => "Departamentos",
        "SELECT COUNT(*) as total FROM puestos" => "Puestos",
        "SELECT COUNT(*) as total FROM empleados" => "Empleados", 
        "SELECT COUNT(*) as total FROM usuarios" => "Usuarios",
        "SELECT COUNT(*) as total FROM nominas" => "Nóminas"
    ];
    
    foreach ($tests as $sql => $label) {
        $stmt = $conn->query($sql);
        $result = $stmt->fetch();
        echo "<p>📊 {$label}: <strong>{$result['total']}</strong></p>";
    }
    
    // Test de usuario admin
    $stmt = $conn->query("SELECT username, nombre, rol FROM usuarios WHERE username = 'admin'");
    $user = $stmt->fetch();
    if ($user) {
        echo "<p class='success'>✅ Usuario admin encontrado: {$user['nombre']} ({$user['rol']})</p>";
    }
    
    echo "<p class='success'>🎉 ¡Base de datos configurada correctamente!</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}