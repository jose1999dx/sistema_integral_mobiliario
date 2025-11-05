<?php
/**
 * Vista: Lista de Empleados
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?> - Sistema Integral</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }
        
        /* Header */
        .header {
            background: white;
            padding: 1.5rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-bottom: 3px solid #28a745;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .page-title h1 {
            color: #28a745;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .page-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        /* Botones */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 10px 20px;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: #28a745;
        }
        
        .btn-primary:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #545b62;
            transform: translateY(-2px);
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
        }
        
        .btn-edit {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-edit:hover {
            background: #e0a800;
        }
        
        .btn-view {
            background: #17a2b8;
        }
        
        .btn-view:hover {
            background: #138496;
        }
        
        .btn-danger {
            background: #dc3545;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        /* Navegación */
        .nav {
            background: #2c3e50;
            padding: 1rem 2rem;
        }
        
        .nav-links {
            display: flex;
            gap: 2rem;
            max-width: 1400px;
            margin: 0 auto;
            list-style: none;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .nav-links a:hover {
            background: rgba(255,255,255,0.1);
        }
        
        .nav-links a.active {
            background: #28a745;
        }
        
        /* Contenido Principal */
        .main-content {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        /* Tarjetas de Estadísticas */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid #28a745;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        /* Filtros y Búsqueda */
        .filters-section {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .filters-row {
            display: flex;
            gap: 1rem;
            align-items: end;
            flex-wrap: wrap;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 10px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: border-color 0.3s;
        }
        
        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #28a745;
        }
        
        /* Tabla de Empleados */
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .table-header {
            background: #343a40;
            color: white;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .table-header h2 {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .search-box {
            display: flex;
            gap: 0.5rem;
        }
        
        .search-box input {
            padding: 0.75rem 1rem;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            width: 300px;
            font-size: 0.9rem;
        }
        
        .search-box button {
            background: #28a745;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .search-box button:hover {
            background: #218838;
        }
        
        /* Tabla */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        /* Badges */
        .badge {
            padding: 0.35rem 0.65rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-secondary {
            background: #e2e3e5;
            color: #383d41;
        }
        
        /* Acciones */
        .actions {
            display: flex;
            gap: 0.5rem;
        }
        
        /* Mensajes */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-color: #bee5eb;
        }
        
        /* Notificación de Desarrollo */
        .development-notice {
            background: #fff3cd;
            border: 2px solid #ffeaa7;
            color: #856404;
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 2rem;
            text-align: center;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .nav-links {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .filters-row {
                flex-direction: column;
            }
            
            .search-box {
                flex-direction: column;
            }
            
            .search-box input {
                width: 100%;
            }
            
            .table-container {
                overflow-x: auto;
            }
            
            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="page-title">
                <h1>👥 Gestión de Empleados</h1>
            </div>
            <div class="page-actions">
                <a href="<?= url('/dashboard') ?>" class="btn btn-secondary">
                    ← Volver al Dashboard
                </a>
                <a href="<?= url('/rh/empleados/crear') ?>" class="btn btn-primary">
                    ➕ Nuevo Empleado
                </a>
            </div>
        </div>
    </header>
    
    <!-- Navigation -->
    <nav class="nav">
        <ul class="nav-links">
            <li><a href="<?= url('/dashboard') ?>">📊 Dashboard</a></li>
            <li><a href="<?= url('/rh/empleados') ?>" class="active">👥 Empleados</a></li>
            <li><a href="#">💰 Nóminas</a></li>
            <li><a href="#">📋 Asistencias</a></li>
            <li><a href="#">🎯 Evaluaciones</a></li>
            <li><a href="#">📅 Permisos</a></li>
        </ul>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Mensajes -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                ✅ <?= [
                    'empleado_creado' => 'Empleado creado exitosamente.',
                    'empleado_actualizado' => 'Empleado actualizado exitosamente.',
                    'empleado_eliminado' => 'Empleado eliminado exitosamente.'
                ][$_GET['success']] ?? 'Operación completada exitosamente.' ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                ❌ <?= [
                    'error_guardar' => 'Error al guardar el empleado.',
                    'error_actualizar' => 'Error al actualizar el empleado.',
                    'error_eliminar' => 'Error al eliminar el empleado.',
                    'empleado_no_encontrado' => 'Empleado no encontrado.',
                    'error_sistema' => 'Error del sistema. Intente más tarde.'
                ][$_GET['error']] ?? 'Ha ocurrido un error.' ?>
            </div>
        <?php endif; ?>
        
        <!-- Estadísticas -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-number"><?= $estadisticas['total_empleados'] ?? 0 ?></div>
                <div class="stat-label">Total Empleados</div>
            </div>
            <?php foreach ($estadisticas['por_departamento'] ?? [] as $depto): ?>
            <div class="stat-card">
                <div class="stat-icon">🏢</div>
                <div class="stat-number"><?= $depto['total'] ?></div>
                <div class="stat-label"><?= htmlspecialchars($depto['nombre']) ?></div>
            </div>
            <?php endforeach; ?>
            <div class="stat-card">
                <div class="stat-icon">📄</div>
                <div class="stat-number"><?= $estadisticas['contratos']['Indeterminado'] ?? 0 ?></div>
                <div class="stat-label">Contratos Indefinidos</div>
            </div>
        </div>
        
        <!-- Filtros -->
        <div class="filters-section">
            <form method="GET" action="<?= url('/rh/empleados') ?>">
                <div class="filters-row">
                    <div class="filter-group">
                        <label for="search">🔍 Buscar Empleados:</label>
                        <input type="text" id="search" name="search" 
                               placeholder="Nombre, apellidos o código..." 
                               value="<?= htmlspecialchars($filtros['search'] ?? '') ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label for="departamento_id">🏢 Departamento:</label>
                        <select id="departamento_id" name="departamento_id">
                            <option value="">Todos los departamentos</option>
                            <?php foreach ($departamentos as $depto): ?>
                            <option value="<?= $depto['id'] ?>" 
                                    <?= ($filtros['departamento_id'] ?? '') == $depto['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($depto['nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <button type="submit" class="btn btn-primary" style="margin-bottom: 0;">
                            🔍 Aplicar Filtros
                        </button>
                        <a href="<?= url('/rh/empleados') ?>" class="btn btn-secondary" style="margin-bottom: 0;">
                            🗑️ Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Tabla de Empleados -->
        <div class="table-container">
            <div class="table-header">
                <h2>📋 Lista de Empleados</h2>
                <div class="search-box">
                    <input type="text" placeholder="Buscar en la lista...">
                    <button type="button">Buscar</button>
                </div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre Completo</th>
                        <th>Departamento</th>
                        <th>Puesto</th>
                        <th>Contacto</th>
                        <th>Fecha Contratación</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($empleados)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2rem; color: #666;">
                            👻 No se encontraron empleados
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($empleados as $empleado): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($empleado->codigo_empleado ?? $empleado['codigo_empleado']) ?></strong>
                        </td>
                        <td>
                            <div style="font-weight: 600;">
                                <?= htmlspecialchars(($empleado->nombre ?? $empleado['nombre']) . ' ' . ($empleado->apellidos ?? $empleado['apellidos'])) ?>
                            </div>
                            <small style="color: #666;">
                                <?= htmlspecialchars($empleado->tipo_contrato ?? $empleado['tipo_contrato'] ?? 'N/A') ?>
                            </small>
                        </td>
                        <td><?= htmlspecialchars($empleado->departamento_nombre ?? $empleado['departamento_nombre'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($empleado->puesto_nombre ?? $empleado['puesto_nombre'] ?? 'N/A') ?></td>
                        <td>
                            <div><?= htmlspecialchars($empleado->email ?? $empleado['email']) ?></div>
                            <small style="color: #666;"><?= htmlspecialchars($empleado->telefono ?? $empleado['telefono']) ?></small>
                        </td>
                        <td>
                            <?= date('d/m/Y', strtotime($empleado->fecha_contratacion ?? $empleado['fecha_contratacion'])) ?>
                        </td>
                        <td>
                            <span class="badge badge-success">Activo</span>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="<?= url('/rh/empleados/editar/' . ($empleado->id ?? $empleado['id'])) ?>" 
                                   class="btn btn-edit btn-sm">✏️ Editar</a>
                                <a href="#" class="btn btn-view btn-sm">👁️ Ver</a>
                                <a href="<?= url('/rh/empleados/eliminar/' . ($empleado->id ?? $empleado['id'])) ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('¿Está seguro de eliminar este empleado?')">🗑️ Eliminar</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Notificación de Desarrollo -->
        <div class="development-notice">
            <strong>🚧 Módulo en Desarrollo</strong>
            <p>Esta es una versión funcional del módulo de empleados. Algunas características están en desarrollo.</p>
            <p><strong>Próximamente:</strong> Nóminas, asistencias, evaluaciones y portal del empleado.</p>
        </div>
    </main>

    <script>
        // Funcionalidad básica de búsqueda en la tabla
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('.search-box input');
            const searchButton = document.querySelector('.search-box button');
            const tableRows = document.querySelectorAll('tbody tr');
            
            function filterTable() {
                const searchTerm = searchInput.value.toLowerCase();
                
                tableRows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
            
            searchButton.addEventListener('click', filterTable);
            searchInput.addEventListener('keyup', filterTable);
            
            // Auto-ocultar mensajes después de 5 segundos
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 0.5s ease';
                    setTimeout(() => alert.remove(), 500);
                });
            }, 5000);
        });
    </script>
</body>
</html>