<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?> | SIM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; }
    </style>
</head>
<body class="p-4 sm:p-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div class="flex items-center">
                <a href="<?= url('presupuesto/index') ?>" class="text-indigo-500 hover:text-indigo-700 mr-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($titulo) ?></h1>
            </div>
            <div class="flex gap-3">
                <a href="<?= url('presupuesto/index') ?>" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                    ← Volver a Presupuestos
                </a>
            </div>
        </div>

        <!-- Métricas Principales -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-green-500">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Utilidad Total</h3>
                <p class="text-2xl font-bold text-green-600">$<?= number_format($metricas['total_utilidad'], 2) ?></p>
                <p class="text-sm text-gray-500 mt-1">Margen: <?= number_format($metricas['margen_promedio'], 1) ?>%</p>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-blue-500">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Proyectos Rentables</h3>
                <p class="text-2xl font-bold text-blue-600"><?= $metricas['proyectos_rentables'] ?>/<?= $metricas['proyectos_analizados'] ?></p>
                <p class="text-sm text-gray-500 mt-1"><?= number_format($metricas['tasa_rentabilidad'], 1) ?>% de éxito</p>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-purple-500">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Inversión Total</h3>
                <p class="text-2xl font-bold text-purple-600">$<?= number_format($metricas['total_gastado'], 2) ?></p>
                <p class="text-sm text-gray-500 mt-1">Presupuestado: $<?= number_format($metricas['total_presupuestado'], 2) ?></p>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-yellow-500">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">ROI Promedio</h3>
                <p class="text-2xl font-bold text-yellow-600"><?= number_format($metricas['margen_promedio'], 1) ?>%</p>
                <p class="text-sm text-gray-500 mt-1">Retorno sobre inversión</p>
            </div>
        </div>

        <!-- Mensaje de desarrollo -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8">
            <h2 class="text-xl font-bold text-yellow-800 mb-2">🚧 Módulo en Desarrollo</h2>
            <p class="text-yellow-700">
                Esta sección de Reportes de Rentabilidad está en desarrollo. Próximamente incluirá:
            </p>
            <ul class="list-disc list-inside text-yellow-700 mt-2">
                <li>Rentabilidad detallada por proyecto</li>
                <li>Cálculo de ROI por inversión</li>
                <li>Análisis avanzado de costos</li>
                <li>Reportes ejecutivos en PDF</li>
            </ul>
        </div>

        <!-- Proyectos Rentables -->
<div class="bg-white rounded-xl shadow-lg mb-6">
    <div class="p-6 border-b">
        <h2 class="text-xl font-bold text-green-700">✅ Proyectos Rentables</h2>
    </div>
    <div class="p-6">
        <?php if (!empty($proyectos_rentables)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($proyectos_rentables as $proyecto): ?>
                <a href="<?= url('presupuesto/rentabilidad-proyecto/' . $proyecto['id']) ?>" 
                   class="block border border-green-200 rounded-lg p-4 bg-green-50 hover:bg-green-100 transition duration-150">
                    <h3 class="font-semibold text-green-800"><?= htmlspecialchars($proyecto['nombre']) ?></h3>
                    <p class="text-sm text-green-600">Proyecto: <?= htmlspecialchars($proyecto['nombre_proyecto']) ?></p>
                    <p class="text-sm text-gray-600">Presupuesto: $<?= number_format($proyecto['monto_total'], 2) ?></p>
                    <p class="text-xs text-green-500 mt-1">📊 Click para ver análisis detallado</p>
                </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-500 text-center py-4">No hay proyectos rentables identificados.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Proyectos No Rentables -->
<div class="bg-white rounded-xl shadow-lg">
    <div class="p-6 border-b">
        <h2 class="text-xl font-bold text-red-700">❌ Proyectos No Rentables</h2>
    </div>
    <div class="p-6">
        <?php if (!empty($proyectos_no_rentables)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($proyectos_no_rentables as $proyecto): ?>
                <a href="<?= url('presupuesto/rentabilidad-proyecto/' . $proyecto['id']) ?>" 
                   class="block border border-red-200 rounded-lg p-4 bg-red-50 hover:bg-red-100 transition duration-150">
                    <h3 class="font-semibold text-red-800"><?= htmlspecialchars($proyecto['nombre']) ?></h3>
                    <p class="text-sm text-red-600">Proyecto: <?= htmlspecialchars($proyecto['nombre_proyecto']) ?></p>
                    <p class="text-sm text-gray-600">Presupuesto: $<?= number_format($proyecto['monto_total'], 2) ?></p>
                    <p class="text-xs text-red-500 mt-1">📊 Click para ver análisis detallado</p>
                </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-500 text-center py-4">¡Excelente! Todos los proyectos son rentables.</p>
        <?php endif; ?>
    </div>
</div>

        <!-- Proyectos No Rentables -->
        <div class="bg-white rounded-xl shadow-lg">
            <div class="p-6 border-b">
                <h2 class="text-xl font-bold text-red-700">❌ Proyectos No Rentables</h2>
            </div>
            <div class="p-6">
                <?php if (!empty($proyectos_no_rentables)): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($proyectos_no_rentables as $proyecto): ?>
                        <div class="border border-red-200 rounded-lg p-4 bg-red-50">
                            <h3 class="font-semibold text-red-800"><?= htmlspecialchars($proyecto['nombre']) ?></h3>
                            <p class="text-sm text-red-600">Proyecto: <?= htmlspecialchars($proyecto['nombre_proyecto']) ?></p>
                            <p class="text-sm text-gray-600">Presupuesto: $<?= number_format($proyecto['monto_total'], 2) ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-4">¡Excelente! Todos los proyectos son rentables.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>