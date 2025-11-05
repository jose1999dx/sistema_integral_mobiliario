<?php
/**
 * Vista: Crear Nuevo Empleado
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?> - Sistema Integral</title>
    <style>
        /* Mantener los mismos estilos del index.php */
        /* ... (copiar los estilos del index.php) ... */
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="page-title">
                <h1>➕ Nuevo Empleado</h1>
            </div>
            <div class="page-actions">
                <a href="<?= url('/rh/empleados') ?>" class="btn btn-secondary">
                    ← Volver a Empleados
                </a>
            </div>
        </div>
    </header>
    
    <nav class="nav">
        <ul class="nav-links">
            <li><a href="<?= url('/dashboard') ?>">📊 Dashboard</a></li>
            <li><a href="<?= url('/rh/empleados') ?>">👥 Empleados</a></li>
            <li><a href="<?= url('/rh/empleados/crear') ?>" class="active">➕ Nuevo Empleado</a></li>
        </ul>
    </nav>
    
    <main class="main-content">
        <div class="development-notice">
            <strong>🚧 Formulario en Desarrollo</strong>
            <p>El formulario completo para crear empleados estará disponible en la siguiente iteración.</p>
            <p>Actualmente estamos integrando la base de datos real.</p>
        </div>
    </main>
</body>
</html>