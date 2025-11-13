<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['titulo'] ?? 'Rentabilidad') ?> | SIM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; }
    </style>
</head>
<body class="p-4 sm:p-8">
<?php
// Extraer manualmente cada variable
$titulo = $data['titulo'] ?? 'Rentabilidad';
$presupuesto = $data['presupuesto'] ?? [];
$rentabilidad = $data['rentabilidad'] ?? [];
$analisis_costos = $data['analisis_costos'] ?? [];
$recomendacion = $data['recomendacion'] ?? 'Recomendación no disponible';
?>

    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div class="flex items-center">
                <a href="<?= url('presupuesto/rentabilidad') ?>" class="text-indigo-500 hover:text-indigo-700 mr-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($titulo) ?></h1>
            </div>
            <div class="flex gap-3">
                <a href="<?= url('presupuesto/detalle/' . $presupuesto['id']) ?>" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                    📋 Ver Presupuesto
                </a>
                 <!-- ✅ NUEVO BOTÓN: Generar Reporte PDF -->
    <a href="<?= url('presupuesto/generar-reporte/' . $presupuesto['id']) ?>" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
        📄 Generar Reporte PDF
    </a>
                <a href="<?= url('presupuesto/rentabilidad') ?>" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                    ← Volver a Rentabilidad
                </a>
            </div>
        </div>

        <!-- Información del Proyecto -->
        <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">📋 Información del Proyecto</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Proyecto</p>
                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($presupuesto['nombre_proyecto'] ?? 'N/A') ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Presupuesto</p>
                    <p class="font-semibold text-indigo-600">$<?= number_format($presupuesto['monto_total'] ?? 0, 2) ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Estado</p>
                    <span class="px-2 py-1 rounded-full text-xs font-semibold 
                        <?= ($presupuesto['estado'] ?? '') === 'Aprobado' ? 'bg-green-100 text-green-700' : 
                           (($presupuesto['estado'] ?? '') === 'Rechazado' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') ?>">
                        <?= htmlspecialchars($presupuesto['estado'] ?? 'N/A') ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Métricas de Rentabilidad -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-green-500">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Ingresos Estimados</h3>
                <p class="text-2xl font-bold text-green-600">$<?= number_format($rentabilidad['ingresos'] ?? 0, 2) ?></p>
                <p class="text-sm text-gray-500 mt-1">+30% sobre presupuesto</p>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-red-500">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Costos Reales</h3>
                <p class="text-2xl font-bold text-red-600">$<?= number_format($rentabilidad['gastado'] ?? 0, 2) ?></p>
                <p class="text-sm text-gray-500 mt-1">
                    <?= number_format((($rentabilidad['gastado'] ?? 0) / ($presupuesto['monto_total'] ?? 1)) * 100, 1) ?>% del presupuesto
                </p>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-blue-500">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Utilidad</h3>
                <p class="text-2xl font-bold <?= ($rentabilidad['utilidad'] ?? 0) > 0 ? 'text-blue-600' : 'text-red-600' ?>">
                    $<?= number_format($rentabilidad['utilidad'] ?? 0, 2) ?>
                </p>
                <p class="text-sm <?= ($rentabilidad['utilidad'] ?? 0) > 0 ? 'text-blue-500' : 'text-red-500' ?> mt-1">
                    Margen: <?= number_format($rentabilidad['margen_utilidad'] ?? 0, 1) ?>%
                </p>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-purple-500">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">ROI</h3>
                <p class="text-2xl font-bold <?= ($rentabilidad['roi'] ?? 0) > 0 ? 'text-purple-600' : 'text-red-600' ?>">
                    <?= number_format($rentabilidad['roi'] ?? 0, 1) ?>%
                </p>
                <p class="text-sm text-gray-500 mt-1">Retorno sobre inversión</p>
            </div>
        </div>

        <!-- Análisis de Costos Mejorado -->
<?php if (!isset($analisis_costos['error'])): ?>
<div class="bg-white p-6 rounded-xl shadow-lg mb-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">🔍 Análisis Detallado de Costos</h2>
    
    <!-- Resumen General -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
        <div class="text-center">
            <p class="text-2xl font-bold text-indigo-600">$<?= number_format($analisis_costos['total_presupuestado'], 2) ?></p>
            <p class="text-sm text-gray-600">Total Presupuestado</p>
        </div>
        <div class="text-center">
            <p class="text-2xl font-bold text-red-600">$<?= number_format($analisis_costos['total_gastado'], 2) ?></p>
            <p class="text-sm text-gray-600">Total Gastado</p>
        </div>
        <div class="text-center">
            <p class="text-2xl font-bold <?= $analisis_costos['desviacion_total'] > 0 ? 'text-red-600' : 'text-green-600' ?>">
                $<?= number_format(abs($analisis_costos['desviacion_total']), 2) ?>
            </p>
            <p class="text-sm text-gray-600">
                <?= $analisis_costos['desviacion_total'] > 0 ? 'Excedido' : 'Ahorro' ?> 
                (<?= number_format($analisis_costos['porcentaje_desviacion_total'], 1) ?>%)
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Distribución por Tipo -->
        <div>
            <h3 class="font-semibold text-gray-700 mb-3">📊 Distribución por Tipo de Costo</h3>
            <div class="space-y-3">
                <?php foreach ($analisis_costos['por_tipo'] as $tipo => $datos): ?>
                <div class="border rounded-lg p-3">
                    <div class="flex justify-between items-center mb-2">
                        <span class="font-medium text-gray-700"><?= htmlspecialchars($tipo) ?></span>
                        <span class="text-sm font-semibold <?= $datos['desviacion'] > 0 ? 'text-red-600' : 'text-green-600' ?>">
                            $<?= number_format(abs($datos['desviacion']), 2) ?>
                            (<?= number_format($datos['porcentaje_desviacion'], 1) ?>%)
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <span class="text-gray-500">Presupuestado:</span>
                            <span class="font-semibold">$<?= number_format($datos['presupuestado'], 2) ?></span>
                        </div>
                        <div>
                            <span class="text-gray-500">Gastado:</span>
                            <span class="font-semibold">$<?= number_format($datos['gastado'], 2) ?></span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-indigo-600 h-2 rounded-full" 
                                 style="width: <?= min($datos['porcentaje_presupuesto'], 100) ?>%">
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1"><?= number_format($datos['porcentaje_presupuesto'], 1) ?>% del presupuesto total</p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Análisis por Ítem -->
        <div>
            <h3 class="font-semibold text-gray-700 mb-3">📋 Análisis Detallado por Ítem</h3>
            <div class="space-y-2 max-h-96 overflow-y-auto">
                <?php foreach ($analisis_costos['items_detallados'] as $item): ?>
                <div class="border rounded p-3 <?= $item['color_estado'] === 'red' ? 'bg-red-50 border-red-200' : 
                                               ($item['color_estado'] === 'orange' ? 'bg-orange-50 border-orange-200' :
                                               ($item['color_estado'] === 'yellow' ? 'bg-yellow-50 border-yellow-200' :
                                               ($item['color_estado'] === 'green' ? 'bg-green-50 border-green-200' : 'bg-blue-50 border-blue-200'))) ?>">
                    <div class="flex justify-between items-start mb-1">
                        <span class="font-medium text-gray-700"><?= htmlspecialchars($item['descripcion']) ?></span>
                        <span class="text-xs px-2 py-1 rounded-full 
                            <?= $item['color_estado'] === 'red' ? 'bg-red-100 text-red-700' :
                               ($item['color_estado'] === 'orange' ? 'bg-orange-100 text-orange-700' :
                               ($item['color_estado'] === 'yellow' ? 'bg-yellow-100 text-yellow-700' :
                               ($item['color_estado'] === 'green' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'))) ?>">
                            <?= $item['estado'] ?>
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <span class="text-gray-500">Presupuesto:</span>
                            <span class="font-semibold">$<?= number_format($item['presupuestado'], 2) ?></span>
                        </div>
                        <div>
                            <span class="text-gray-500">Gastado:</span>
                            <span class="font-semibold">$<?= number_format($item['gastado'], 2) ?></span>
                        </div>
                    </div>
                    <?php if ($item['gastado'] > 0): ?>
                    <div class="mt-1 text-xs <?= $item['desviacion'] > 0 ? 'text-red-600' : 'text-green-600' ?>">
                        Desviación: $<?= number_format(abs($item['desviacion']), 2) ?> 
                        (<?= number_format($item['porcentaje_desviacion'], 1) ?>%)
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Resumen de Ítems Problemáticos -->
            <?php if (!empty($analisis_costos['items_problematicos'])): ?>
            <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                <h4 class="font-semibold text-red-700 mb-2">⚠️ Ítems con Desviación Significativa</h4>
                <p class="text-sm text-red-600">
                    <?= count($analisis_costos['items_problematicos']) ?> ítem(s) exceden en más del 10% su presupuesto.
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

        <!-- Nota sobre datos simulados -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <p class="text-yellow-700 text-sm">
                💡 <strong>Nota:</strong> Los ingresos son estimados basados en un margen estándar del 30%. 
                En un sistema de producción, estos datos vendrían de contratos reales y ventas.
            </p>
        </div>
    </div>
</body>
</html>