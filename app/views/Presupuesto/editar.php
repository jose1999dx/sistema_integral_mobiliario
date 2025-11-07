<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo ?? 'Editar Presupuesto') ?> | SIM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; }
    </style>
</head>
<body class="p-4 sm:p-8">
    <?php
// Mostrar mensajes flash
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['message'])): 
    $message = $_SESSION['message'];
    $bg_color = $message['type'] === 'success' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200';
    $text_color = $message['type'] === 'success' ? 'text-green-800' : 'text-red-800';
    $border_color = $message['type'] === 'success' ? 'border-green-400' : 'border-red-400';
?>
    <div class="<?= $bg_color ?> border-l-4 <?= $border_color ?> p-4 mb-6 mx-4 mt-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <?php if ($message['type'] === 'success'): ?>
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                <?php else: ?>
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                <?php endif; ?>
            </div>
            <div class="ml-3">
                <p class="text-sm <?= $text_color ?>"><?= htmlspecialchars($message['text']) ?></p>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<div class="max-w-6xl mx-auto">
    <!-- Título y Regreso -->
    <h1 class="text-3xl font-extrabold text-gray-900 mb-6 flex items-center">
        <a href="/presupuesto/detalle/<?= $presupuesto['id'] ?>" class="text-indigo-500 hover:text-indigo-700 transition duration-150 mr-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <?= htmlspecialchars($titulo ?? 'Editar Presupuesto') ?>
    </h1>

    <!-- Mostrar errores si existen -->
    <?php if (!empty($errores)): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
            <strong class="font-bold">Error de Validación:</strong>
            <ul class="list-disc list-inside mt-2">
                <?php foreach ($errores as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Formulario de Edición -->
    <div class="bg-white p-6 rounded-xl shadow-lg">
        <form action="<?= url('presupuesto/actualizar') ?>" method="POST">
            <input type="hidden" name="presupuesto_id" value="<?= $presupuesto['id'] ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre del Presupuesto:</label>
                    <input type="text" name="nombre" value="<?= htmlspecialchars($presupuesto['nombre']) ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Proyecto:</label>
                    <select name="id_proyecto" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        <option value="">Seleccionar proyecto...</option>
                        <?php foreach ($proyectos as $proyecto): ?>
                            <option value="<?= $proyecto['id'] ?>" 
                                <?= $proyecto['id'] == $presupuesto['proyecto_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($proyecto['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Descripción:</label>
                <textarea name="descripcion" rows="3" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"><?= htmlspecialchars($presupuesto['descripcion'] ?? '') ?></textarea>
            </div>

            <!-- Ítems del Presupuesto -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Ítems del Presupuesto</h3>
                
                <div id="items-container">
                    <?php foreach ($presupuesto['items'] as $index => $item): ?>
                    <div class="item-row grid grid-cols-1 md:grid-cols-12 gap-4 mb-4 p-4 border border-gray-200 rounded-lg">
                        <div class="md:col-span-5">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción:</label>
                            <input type="text" name="item[<?= $index ?>][descripcion]" 
                                   value="<?= htmlspecialchars($item['descripcion']) ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                        
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo:</label>
                            <select name="item[<?= $index ?>][tipo]" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="Directo" <?= $item['tipo'] == 'Directo' ? 'selected' : '' ?>>Directo</option>
                                <option value="Indirecto" <?= $item['tipo'] == 'Indirecto' ? 'selected' : '' ?>>Indirecto</option>
                            </select>
                        </div>
                        
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Monto ($):</label>
                            <input type="number" name="item[<?= $index ?>][monto]" step="0.01" min="0.01"
                                   value="<?= htmlspecialchars($item['monto']) ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                        
                        <div class="md:col-span-1 flex items-end">
                            <button type="button" onclick="removeItem(this)" 
                                    class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition duration-150">
                                ✕
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <button type="button" onclick="addItem()" 
                        class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition duration-150">
                    + Agregar Ítem
                </button>
            </div>

            <!-- Botones de acción -->
            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
               <a href="<?= url('presupuesto/detalle/' . $presupuesto['id']) ?>"
                   class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition duration-150">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-150">
                    Actualizar Presupuesto
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let itemIndex = <?= count($presupuesto['items']) ?>;

function addItem() {
    const container = document.getElementById('items-container');
    const newItem = document.createElement('div');
    newItem.className = 'item-row grid grid-cols-1 md:grid-cols-12 gap-4 mb-4 p-4 border border-gray-200 rounded-lg';
    newItem.innerHTML = `
        <div class="md:col-span-5">
            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción:</label>
            <input type="text" name="item[${itemIndex}][descripcion]" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
        </div>
        
        <div class="md:col-span-3">
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo:</label>
            <select name="item[${itemIndex}][tipo]" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="Directo">Directo</option>
                <option value="Indirecto">Indirecto</option>
            </select>
        </div>
        
        <div class="md:col-span-3">
            <label class="block text-sm font-medium text-gray-700 mb-1">Monto ($):</label>
            <input type="number" name="item[${itemIndex}][monto]" step="0.01" min="0.01"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
        </div>
        
        <div class="md:col-span-1 flex items-end">
            <button type="button" onclick="removeItem(this)" 
                    class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition duration-150">
                ✕
            </button>
        </div>
    `;
    container.appendChild(newItem);
    itemIndex++;
}

function removeItem(button) {
    const itemRow = button.closest('.item-row');
    if (document.querySelectorAll('.item-row').length > 1) {
        itemRow.remove();
    } else {
        alert('Debe haber al menos un ítem en el presupuesto.');
    }
}
</script>

</body>
</html>