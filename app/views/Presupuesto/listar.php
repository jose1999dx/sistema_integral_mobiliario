<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titulo); ?> | SIM</title>
    
    <style>
        /* Variables y Colores basados en tu Dashboard (Azul, Verde, Gris) */
        :root {
            --color-primary: #4e73df; /* Azul principal */
            --color-success: #1cc88a; /* Verde para Aprobado */
            --color-danger: #e74a3b;  /* Rojo para Rechazado */
            --color-warning: #f6c23e; /* Amarillo para Pendiente */
            --color-text-dark: #858796;
            --color-bg-light: #f8f9fc;
            --color-card-border: #e3e6f0;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: var(--color-bg-light);
            margin: 0;
            padding: 20px;
        }

        .container-fluid {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* --- TÍTULO Y BOTONES --- */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        /* Contenedor para agrupar los botones de acción */
        .header-actions {
            display: flex;
            gap: 10px; /* Espacio entre botones */
            align-items: center;
        }

        h1 {
            font-size: 1.5rem;
            color: #5a5c69; /* Gris oscuro */
            margin: 0;
            padding-left: 10px;
            border-left: 4px solid var(--color-primary); /* Línea lateral azul */
            font-weight: 700;
        }

        /* --- NUEVOS ESTILOS: Botón Volver al Dashboard --- */
        .btn-back-dashboard {
            /* Estilo para el botón de regreso */
            display: inline-flex;
            align-items: center;
            padding: 8px 15px;
            background-color: #6c757d; /* Gris secundario/neutral */
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 0.9rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s;
        }

        .btn-back-dashboard:hover {
            background-color: #5a6268; /* Gris más oscuro en hover */
        }
        
        .btn-back-dashboard svg {
            width: 16px; 
            height: 16px;
            margin-right: 5px;
            stroke-width: 2.5;
        }
        /* Fin de estilos del botón Volver al Dashboard */

        /* Botón Crear */
        .btn-create {
            display: inline-flex;
            align-items: center;
            padding: 8px 15px;
            background-color: var(--color-primary);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 0.9rem; /* Uniformidad de tamaño */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s;
        }

        .btn-create:hover {
            background-color: #3b5cb0; /* Azul más oscuro */
        }
        
        .btn-create span {
            margin-right: 5px;
        }

        /* --- TARJETA Y TABLA --- */
        .card {
            background-color: white;
            border: 1px solid var(--color-card-border);
            border-radius: 8px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); /* Sombra similar a Bootstrap */
            margin-bottom: 30px;
        }

        .card-header {
            padding: 15px 20px;
            border-bottom: 1px solid var(--color-card-border);
            background-color: var(--color-bg-light);
            font-weight: bold;
            color: var(--color-primary);
            font-size: 1rem;
        }

        .card-body {
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        th, td {
            padding: 12px 15px;
            border: 1px solid var(--color-card-border);
            text-align: left;
        }

        th {
            background-color: #eaecf4; /* Gris claro de encabezado */
            color: #333;
            font-weight: 600;
        }

        tr:nth-child(even) {
            background-color: #f7f7f9; /* Rayado de filas */
        }

        tr:hover {
            background-color: #e9ecef;
        }

        /* --- ESTILOS DE ESTADO (Badges) --- */
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: bold;
            text-align: center;
        }

        .status-aprobado {
            background-color: var(--color-success);
            color: white;
        }

        .status-rechazado {
            background-color: var(--color-danger);
            color: white;
        }

        .status-pendiente {
            background-color: var(--color-warning);
            color: #333;
        }

        /* --- BOTONES DE ACCIÓN (Tabla) --- */
        .btn-action {
            display: inline-block;
            padding: 6px 10px;
            margin-right: 5px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-size: 0.85rem;
            font-weight: 600;
            transition: opacity 0.3s;
        }
        
        .btn-action:hover {
            opacity: 0.8;
        }

        .btn-view { background-color: #36b9cc; } /* Info/Cyan */
        .btn-approve { background-color: var(--color-success); }
        .btn-reject { background-color: var(--color-danger); }
        
        .action-completed {
            font-size: 0.9em; 
            color: var(--color-text-dark);
        }

    </style>
    </head>
<body>

<div class="container-fluid">

    <?php 
    // Los mensajes de alerta (usando estilos puros)
    if (isset($_GET['msg'])): ?>
        <div style="padding: 15px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 20px;">
            <?php echo htmlspecialchars($_GET['msg']); ?>
        </div>
    <?php endif; 
    if (isset($_GET['error'])): ?>
        <div style="padding: 15px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 20px;">
            <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>

    <div class="header-section">
        <h1><?php echo htmlspecialchars($titulo); ?></h1>
        
        <div class="header-actions">
            
            <a href="<?php echo BASE_URL; ?>/public/index.php?url=dashboard" class="btn-back-dashboard">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Volver al Dashboard
            </a>
            
            <a href="<?php echo BASE_URL; ?>/index.php?url=presupuesto/crear" class="btn-create">
                <span>➕</span> Crear Nuevo Presupuesto
            </a>
        </div>
        
    </div>

    <?php if (empty($presupuestos)): ?>
        <div class="card">
            <div class="card-body">
                <p>No se encontraron presupuestos. Comience creando uno nuevo.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header">
                Listado General de Presupuestos
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Proyecto</th>
                                <th>Monto Total</th>
                                <th>Estado</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($presupuestos as $p): 
                                $estado = $p['estado'] ?? 'Pendiente';
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($p['id'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($p['nombre'] ?? 'Sin Nombre'); ?></td>
                                <td><?php echo htmlspecialchars($p['nombre_proyecto'] ?? 'N/A'); ?></td>
                                
                                <td>$<?php echo number_format($p['monto_total'] ?? 0, 2, '.', ','); ?></td>
                                
                                <td>
                                    <?php 
                                         $clase_estado = 'status-' . strtolower($estado);
                                    ?>
                                    <span class="status-badge <?php echo $clase_estado; ?>">
                                        <?php echo htmlspecialchars($estado); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($p['fecha_creacion'] ?? 'now')); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/index.php?url=presupuesto/detalle/<?php echo $p['id'] ?? 0; ?>" class="btn-action btn-view" title="Ver Detalle">
                                        👁️ Ver
                                    </a>
                                    
                                    <?php if ($estado == 'Pendiente'): ?>
                                        <a href="<?php echo BASE_URL; ?>/index.php?url=presupuesto/aprobar/<?php echo $p['id'] ?? 0; ?>" class="btn-action btn-approve" title="Aprobar" onclick="return confirm('¿Confirmar aprobación?');">
                                            ✔️ Aprobar
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>/index.php?url=presupuesto/rechazar/<?php echo $p['id'] ?? 0; ?>" class="btn-action btn-reject" title="Rechazar" onclick="return confirm('¿Confirmar rechazo?');">
                                            ❌ Rechazar
                                        </a>
                                    <?php else: ?>
                                        <span class="action-completed">Acción completada.</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

</body>
</html>