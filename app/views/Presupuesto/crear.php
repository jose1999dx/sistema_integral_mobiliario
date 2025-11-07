<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titulo); ?> | SIM</title>
    
    <style>
        /* Variables y Colores */
        :root {
            --color-primary: #4e73df;
            --color-success: #1cc88a;
            --color-danger: #e74a3b;
            --color-bg-light: #f8f9fc;
            --color-card-border: #e3e6f0;
            --color-text-dark: #5a5c69;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: var(--color-bg-light);
            margin: 0;
            padding: 20px;
        }

        .container-fluid {
            max-width: 900px;
            margin: 0 auto;
        }

        /* --- ENCABEZADO Y FLECHA DE REGRESO (NUEVO) --- */
        .page-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px; /* Espacio debajo del encabezado completo */
        }
        
        .back-arrow-link {
            /* Estilo del link de flecha */
            display: inline-flex;
            align-items: center;
            color: var(--color-primary); /* Usa el color principal del tema */
            text-decoration: none;
            transition: color 0.2s ease-in-out, background-color 0.2s;
            padding: 5px; 
            border-radius: 50%;
        }

        .back-arrow-link:hover {
            color: #385aab; /* Un tono más oscuro en hover */
            background-color: rgba(78, 115, 223, 0.1); /* Ligero fondo en hover */
        }

        .back-arrow-link svg {
            width: 30px; /* Tamaño */
            height: 30px;
            stroke-width: 2.5; /* Grosor de la línea */
        }
        
        h1 {
            /* Estilo del título */
            font-size: 1.5rem;
            color: var(--color-text-dark);
            margin-bottom: 0; /* Lo movemos al contenedor principal */
            padding-left: 15px; /* Espacio después de la flecha */
            border-left: 4px solid var(--color-primary);
            font-weight: 700;
        }
        
        /* --- TARJETA DE FORMULARIO --- */
        .card {
            background-color: white;
            border: 1px solid var(--color-card-border);
            border-radius: 8px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
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
            padding: 25px;
        }

        /* --- FORMULARIO Y CAMPOS --- */
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .form-row > .form-group {
            flex: 1;
            margin-bottom: 0;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--color-text-dark);
            font-size: 0.9rem;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d3e2;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        
        input:focus, select:focus, textarea:focus {
            border-color: var(--color-primary);
            outline: none;
            box-shadow: 0 0 0 0.1rem rgba(78, 115, 223, 0.25);
        }

        /* Ítems Dinámicos */
        .item-row {
            display: flex;
            gap: 10px;
            align-items: flex-end; 
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #f0f0f0;
            border-radius: 4px;
        }

        .item-row input, .item-row select {
            padding: 8px;
            font-size: 0.9rem;
        }
        
        .item-row .item-descripcion { flex: 4; }
        .item-row .item-tipo { flex: 2; }
        .item-row .item-monto { flex: 2; }
        .item-row .item-actions { flex: 0 0 30px; margin-bottom: 5px;} 

        .btn-remove {
            background-color: var(--color-danger);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 30px;
            height: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
        }
        
        .btn-add-item {
            display: inline-block;
            padding: 8px 15px;
            background-color: #36b9cc; /* Cyan */
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            margin-top: 10px;
        }
        
        .btn-add-item:hover {
            background-color: #2c9faf;
        }


        /* --- BOTONES DE ACCIÓN --- */
        .form-actions {
            margin-top: 30px;
            border-top: 1px solid var(--color-card-border);
            padding-top: 20px;
            display: flex;
            justify-content: flex-end;
        }

        .btn-submit {
            padding: 10px 20px;
            background-color: var(--color-success);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .btn-submit:hover {
            background-color: #15a07c;
        }
        
        .btn-back {
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            margin-right: 10px;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .btn-back:hover {
            background-color: #5a6268;
        }
        
        .error-message {
            color: var(--color-danger);
            font-size: 0.85rem;
            margin-top: 5px;
        }

        .alert-error {
            padding: 15px; 
            background-color: #f8d7da; 
            color: #721c24; 
            border: 1px solid #f5c6cb; 
            border-radius: 4px; 
            margin-bottom: 20px;
        }

    </style>
</head>
<body>

<div class="container-fluid">

    <div class="page-header">
        <a href="presupuesto/index" class="back-arrow-link">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <h1>Crear Nuevo Presupuesto 💰</h1>
    </div>
    <?php 
    // Usar 'form_data' que viene del controlador para repoblar el formulario
    $errores = $errores ?? $data['errores'] ?? []; 
    $form_data = $data['form_data'] ?? [
        'nombre' => '',
        'id_proyecto' => '',
        'descripcion' => '', 
        'items' => [['descripcion' => '', 'tipo' => 'Directo', 'monto' => '']]
    ];
    $proyectos = $proyectos ?? $data['proyectos'] ?? [];

    // Mostrar errores de validación si existen (general)
    if (!empty($errores)): ?>
        <div class="alert-error">
            <p style="margin-top: 0; font-weight: bold;">Error de Validación:</p>
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            Datos Generales y Financieros
        </div>
        <div class="card-body">
            
            <form action="<?= url('presupuesto/guardar') ?>" method="POST">
                <div class="form-row">
                    <div class="form-group" style="flex: 2;">
                        <label for="nombre">Nombre del Presupuesto:</label>
                        <input type="text" id="nombre" name="nombre" required 
                                value="<?php echo htmlspecialchars($form_data['nombre'] ?? ''); ?>">
                        <?php if (isset($errores['nombre'])) echo '<p class="error-message">' . htmlspecialchars($errores['nombre']) . '</p>'; ?>
                    </div>

                    <div class="form-group" style="flex: 1;">
                        <label for="id_proyecto">Proyecto Asociado:</label>
                        <select id="id_proyecto" name="id_proyecto" required>
                            <option value="">Seleccione...</option>
                            <?php 
                            $selected_id = $form_data['id_proyecto'] ?? null;
                            if (!empty($proyectos)):
                                foreach ($proyectos as $proyecto): ?>
                                    <option value="<?php echo htmlspecialchars($proyecto['id']); ?>"
                                        <?php echo ((string)$selected_id === (string)$proyecto['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($proyecto['nombre']); ?>
                                    </option>
                                <?php endforeach;
                            else: ?>
                                <option value="" disabled>No hay proyectos disponibles</option>
                            <?php endif; ?>
                        </select>
                        <?php if (isset($errores['id_proyecto'])) echo '<p class="error-message">' . htmlspecialchars($errores['id_proyecto']) . '</p>'; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción (Detalles del alcance):</label>
                    <textarea id="descripcion" name="descripcion" rows="3"><?php echo htmlspecialchars($form_data['descripcion'] ?? ''); ?></textarea>
                </div>
                
                <div class="card-header" style="margin: 20px -25px 0 -25px; border-top: none;">
                    Detalles del Presupuesto (Ítems)
                </div>
                <div class="card-body" style="padding: 20px 0 0 0;">
                    
                    <div id="itemsContainer">
                        <?php 
                        // Obtener los ítems para repoblar si hubo un error, o el array inicial
                        $items_to_render = $form_data['items'] ?? [['descripcion' => '', 'tipo' => 'Directo', 'monto' => '']];
                        
                        foreach ($items_to_render as $index => $item):
                        ?>
                        <div class="item-row" data-index="<?php echo $index; ?>">
                            
                            <div class="item-descripcion">
                                <label>Descripción del Gasto:</label>
                                <input type="text" name="item[<?php echo $index; ?>][descripcion]" placeholder="Materia prima, mano de obra, etc." required
                                        value="<?php echo htmlspecialchars($item['descripcion'] ?? ''); ?>">
                            </div>
                            
                            <div class="item-tipo">
                                <label>Tipo:</label>
                                <select name="item[<?php echo $index; ?>][tipo]">
                                    <option value="Directo" <?php echo (($item['tipo'] ?? 'Directo') == 'Directo') ? 'selected' : ''; ?>>Directo</option>
                                    <option value="Indirecto" <?php echo (($item['tipo'] ?? 'Directo') == 'Indirecto') ? 'selected' : ''; ?>>Indirecto</option>
                                </select>
                            </div>
                            
                            <div class="item-monto">
                                <label>Monto ($):</label>
                                <input type="number" name="item[<?php echo $index; ?>][monto]" step="0.01" min="0.01" required
                                        value="<?php echo htmlspecialchars($item['monto'] ?? ''); ?>">
                            </div>

                            <div class="item-actions">
                                <button type="button" class="btn-remove" onclick="removeItem(this)">X</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <button type="button" id="addItemBtn" class="btn-add-item">+ Añadir Ítem</button>

                </div>
                <div class="form-actions">
                    <a href="<?php echo BASE_URL; ?>/index.php?url=presupuesto/index" class="btn-back">Cancelar y Volver</a>
                    <button type="submit" class="btn-submit">Guardar Presupuesto</button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
    // --- Lógica JavaScript para añadir/eliminar Ítems ---
    document.addEventListener('DOMContentLoaded', () => {
        const itemsContainer = document.getElementById('itemsContainer');
        const addItemBtn = document.getElementById('addItemBtn');
        
        // Obtener el índice inicial más alto de los ítems ya renderizados
        let itemIndex = 0;
        itemsContainer.querySelectorAll('.item-row').forEach(row => {
            const index = parseInt(row.getAttribute('data-index') || 0);
            if (index >= itemIndex) {
                itemIndex = index + 1;
            }
        });
        // Si no había ítems renderizados, itemIndex = 1 (para el primer ítem nuevo)
        if (itemIndex === 0 && itemsContainer.children.length > 0) {
             itemIndex = itemsContainer.children.length;
        } else if (itemsContainer.children.length === 0) {
            itemIndex = 0; // Si no hay nada, el primer nuevo es 0, pero esto no debería pasar si hay un ítem inicial
        }


        // Función para verificar y actualizar el estado del botón de eliminar
        function updateRemoveButtons() {
            const currentItems = itemsContainer.querySelectorAll('.item-row');
            const canRemove = currentItems.length > 1;
            currentItems.forEach(row => {
                const button = row.querySelector('.btn-remove');
                if (button) {
                    // Solo se deshabilita si es el único ítem
                    button.disabled = !canRemove;
                }
            });
        }
        
        // Función para crear la plantilla HTML de un nuevo ítem
        function createItemRow(index, desc = '', tipo = 'Directo', monto = '') {
            const newRow = document.createElement('div');
            newRow.className = 'item-row';
            newRow.setAttribute('data-index', index);
            
            // Usamos un template string con el índice dinámico
            newRow.innerHTML = `
                <div class="item-descripcion">
                    <label>Descripción del Gasto:</label>
                    <input type="text" name="item[${index}][descripcion]" placeholder="Materia prima, mano de obra, etc." required value="${desc}">
                </div>
                
                <div class="item-tipo">
                    <label>Tipo:</label>
                    <select name="item[${index}][tipo]">
                        <option value="Directo" ${tipo === 'Directo' ? 'selected' : ''}>Directo</option>
                        <option value="Indirecto" ${tipo === 'Indirecto' ? 'selected' : ''}>Indirecto</option>
                    </select>
                </div>
                
                <div class="item-monto">
                    <label>Monto ($):</label>
                    <input type="number" name="item[${index}][monto]" step="0.01" min="0.01" required value="${monto}">
                </div>

                <div class="item-actions">
                    <button type="button" class="btn-remove" onclick="removeItem(this)">X</button>
                </div>
            `;
            return newRow;
        }

        // Listener para el botón de añadir ítem
        addItemBtn.addEventListener('click', () => {
            // Usa el índice actual
            const newRow = createItemRow(itemIndex);
            itemsContainer.appendChild(newRow);
            
            // Incrementa el índice para el próximo ítem
            itemIndex++;
            updateRemoveButtons();
        });
        
        // Función global para eliminación (usada por onclick)
        window.removeItem = function(button) {
            const rowToRemove = button.closest('.item-row');
            if (itemsContainer.querySelectorAll('.item-row').length > 1) {
                rowToRemove.remove();
                updateRemoveButtons();
            } else {
                // Si solo queda un ítem, limpiar los campos para que la validación lo ignore
                const inputs = rowToRemove.querySelectorAll('input, select');
                inputs.forEach(input => {
                    if (input.type === 'text' || input.type === 'number') {
                        input.value = '';
                    } else if (input.tagName === 'SELECT') {
                        input.value = 'Directo';
                    }
                });
                console.warn("Debe haber al menos un ítem. Los campos han sido limpiados."); 
                updateRemoveButtons();
            }
        };

        // Inicializa el estado de los botones al cargar
        updateRemoveButtons();
    });
</script>

</body>
</html>