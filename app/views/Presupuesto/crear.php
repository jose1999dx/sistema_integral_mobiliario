<?php 
// views/presupuesto/crear.php
// $titulo, $proyectos, $errores, $nombre, $proyecto_id, $items están disponibles

// Función para renderizar una fila de ítem (definida aquí por simplicidad de PHP puro)
function renderItemRow($index, $item) { 
    $tipo = htmlspecialchars($item['tipo'] ?? 'Directo');
    $desc = htmlspecialchars($item['descripcion'] ?? '');
    $monto = htmlspecialchars($item['monto'] ?? '');
    ?>
    <tr data-index="<?php echo $index; ?>">
        <td><input type="text" name="item[descripcion][]" value="<?php echo $desc; ?>" required></td>
        <td>
            <select name="item[tipo][]" required>
                <option value="Directo" <?php echo ($tipo == 'Directo') ? 'selected' : ''; ?>>Directo</option>
                <option value="Indirecto" <?php echo ($tipo == 'Indirecto') ? 'selected' : ''; ?>>Indirecto</option>
            </select>
        </td>
        <td><input type="number" name="item[monto][]" step="0.01" min="0.01" value="<?php echo $monto; ?>" required></td>
        <td><button type="button" class="btn-delete" onclick="removeItem(this)">🗑️</button></td>
    </tr>
<?php 
}
?>

<div class="container">
    <h2><?php echo htmlspecialchars($titulo); ?></h2>
    
    <?php if (!empty($errores)): ?>
        <div class="error-msg">
            <p>Por favor, corrija los siguientes errores:</p>
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="/presupuesto/crear" method="post" id="form-presupuesto">
        <div class="form-group">
            <label for="nombre">Nombre del Presupuesto:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" required>
        </div>

        <div class="form-group">
            <label for="proyecto_id">Seleccionar Proyecto:</label>
            <select id="proyecto_id" name="proyecto_id" required>
                <option value="">-- Seleccione un Proyecto --</option>
                <?php foreach ($proyectos as $proyecto): ?>
                    <option value="<?php echo $proyecto['id']; ?>"
                        <?php echo ($proyecto['id'] == $proyecto_id) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($proyecto['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <hr>

        <h3>Detalle de Costos (Directos/Indirectos)</h3>
        <table id="items-table">
            <thead>
                <tr>
                    <th>Descripción del Ítem</th>
                    <th>Tipo</th>
                    <th>Monto</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $index => $item): ?>
                    <?php renderItemRow($index, $item); ?>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <button type="button" id="add-item" class="btn-secondary">➕ Agregar Ítem</button>
        
        <div class="form-actions">
            <button type="submit" class="btn-primary">💾 Guardar Presupuesto Completo</button>
        </div>
    </form>
</div>