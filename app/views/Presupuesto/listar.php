<?php 
// views/presupuesto/listar.php
// $titulo y $presupuestos están disponibles
?>
<div class="container">
    <h1><?php echo htmlspecialchars($titulo); ?></h1>
    
    <div style="margin-bottom: 20px;">
        <a href="/presupuesto/crear" class="btn-primary">➕ Crear Nuevo Presupuesto</a>
    </div>

    <?php if (empty($presupuestos)): ?>
        <p>No se encontraron presupuestos. Comience creando uno nuevo.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre del Presupuesto</th>
                    <th>Proyecto</th>
                    <th>Monto Total</th>
                    <th>Estado</th>
                    <th>Fecha Creación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($presupuestos as $p): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['id']); ?></td>
                    <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($p['nombre_proyecto']); ?></td>
                    <td>$<?php echo number_format($p['monto_total'], 2, '.', ','); ?></td>
                    <td>
                        <span class="estado-<?php echo strtolower($p['estado']); ?>">
                            <?php echo htmlspecialchars($p['estado']); ?>
                        </span>
                    </td>
                    <td><?php echo date('d/m/Y', strtotime($p['fecha_creacion'])); ?></td>
                    <td>
                        <a href="/presupuesto/detalle/<?php echo $p['id']; ?>" class="btn-secondary">Ver</a>
                        <?php if ($p['estado'] == 'Pendiente'): ?>
                            <a href="/presupuesto/aprobar/<?php echo $p['id']; ?>" class="btn-approve">Aprobar</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<style>
/* CSS para estados (idealmente en style.css) */
.estado-pendiente { color: orange; font-weight: bold; }
.estado-aprobado { color: green; font-weight: bold; }
.estado-rechazado { color: red; font-weight: bold; }
.btn-approve { background-color: #28a745; color: white; padding: 5px 8px; border-radius: 4px; text-decoration: none; }
</style>