<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo ?? 'Registrar Gasto Real') ?> | SIM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; }
    </style>
</head>
<body class="p-4 sm:p-8">

<div class="max-w-4xl mx-auto">
    <!-- Título y Regreso -->
    <h1 class="text-3xl font-extrabold text-gray-900 mb-6 flex items-center">
        <a href="/presupuesto/detalle/<?= $presupuesto['id'] ?>" class="text-indigo-500 hover:text-indigo-700 transition duration-150 mr-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <?= htmlspecialchars($titulo ?? 'Registrar Gasto Real') ?>
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

    <!-- Información del Presupuesto -->
    <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Información del Presupuesto</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-600">Presupuesto:</p>
                <p class="font-semibold text-gray-800"><?= htmlspecialchars($presupuesto['nombre']) ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Proyecto:</p>
                <p class="font-semibold text-gray-800"><?= htmlspecialchars($presupuesto['nombre_proyecto']) ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Total Presupuestado:</p>
                <p class="font-semibold text-indigo-600">$<?= number_format($presupuesto['monto_total'], 2) ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Gastado Actual:</p>
                <p class="font-semibold text-red-600">$<?= number_format($presupuesto['gastado'], 2) ?></p>
            </div>
        </div>
    </div>

    <!-- Formulario de Gasto Real -->
    <div class="bg-white p-6 rounded-xl shadow-lg">
        <form action="<?= url('presupuesto/guardarGastoReal') ?>" method="POST">
            <input type="hidden" name="presupuesto_id" value="<?= $presupuesto['id'] ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Monto del Gasto ($):</label>
                    <input type="number" name="monto" step="0.01" min="0.01" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" 
                           placeholder="0.00" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha del Gasto:</label>
                    <input type="date" name="fecha" value="<?= date('Y-m-d') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>
            </div>

            

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Descripción del Gasto:</label>
                <textarea name="descripcion" rows="4" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" 
                          placeholder="Describe en qué se gastó el dinero..." required></textarea>
            </div>

            <!-- Botones de acción -->
            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="<?= url('presupuesto/detalle/' . $presupuesto['id']) ?>"
                   class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition duration-150">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-150">
                    Registrar Gasto
                </button>
            </div>
        </form>
    </div>
</div>

</body>
</html>