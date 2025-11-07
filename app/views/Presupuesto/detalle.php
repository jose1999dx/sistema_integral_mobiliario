<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo ?? 'Detalle de Presupuesto') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; /* Slate 100 */ }
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
<?php
// --- Función Auxiliar para formatear moneda ---
function format_currency($amount) {
    // Usar number_format para asegurar formato de moneda estándar en español (ej: 1,234.56)
    return '$' . number_format($amount, 2, '.', ',');
}

// Asegurarse de que $presupuesto sea un array
if (!isset($presupuesto) || !is_array($presupuesto)) {
    echo '<div class="max-w-4xl mx-auto bg-white p-6 rounded-xl shadow-lg text-center text-red-500">Error: No se pudo cargar la información del presupuesto.</div>';
    echo '</body></html>';
    exit;
}

// Variables clave para el resumen
$monto_total = 0;
// Calcular el monto total asignado sumando los ítems
if (isset($presupuesto['items']) && is_array($presupuesto['items'])) {
    foreach ($presupuesto['items'] as $item) {
        $monto_total += (float)($item['monto'] ?? 0);
    }
}
$gastado = (float)($presupuesto['gastado'] ?? 0.00); 
$restante = $monto_total - $gastado;
$porcentaje = $monto_total > 0 ? ($gastado / $monto_total) * 100 : 0;
$color_restante = $restante >= 0 ? 'text-green-600' : 'text-red-600';

// Clase de color para el estado
$estado = $presupuesto['estado'] ?? 'Pendiente';
$estado_color_map = [
    'Aprobado' => 'bg-green-100 text-green-700',
    'Rechazado' => 'bg-red-100 text-red-700',
    'Pendiente' => 'bg-yellow-100 text-yellow-700'
];
$estado_color = $estado_color_map[$estado] ?? 'bg-gray-100 text-gray-700';
?>

    <div class="max-w-6xl mx-auto">
        <!-- Título y Regreso -->
        <h1 class="text-3xl font-extrabold text-gray-900 mb-6 flex items-center">
            <!-- 
                Aseguramos que la ruta base sea correcta.
                Si usas un helper de URL, cámbialo aquí: url('presupuesto/index') 
                Dejamos la ruta absoluta '/Presupuesto/index' que lleva al listado.
            -->
            <a href="presupuesto/index" class="text-indigo-500 hover:text-indigo-700 transition duration-150 mr-3">
                <!-- Icono de flecha izquierda (SVG) -->
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <?= htmlspecialchars($titulo ?? 'Detalle del Presupuesto') ?>
        </h1>

        <!-- Contenedor principal de los detalles -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Columna Principal: Resumen y Detalle de Ítems -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Tarjeta de Información General -->
                <div class="bg-white p-6 rounded-xl shadow-lg border-t-4 border-indigo-500">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4"><?= htmlspecialchars($presupuesto['nombre'] ?? 'N/A') ?></h2>
                    <p class="text-gray-600 mb-4 p-3 bg-gray-50 border rounded-lg"><?= nl2br(htmlspecialchars($presupuesto['descripcion'] ?? 'Sin descripción.')) ?></p>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div class="p-3 bg-indigo-50 rounded-lg">
                            <p class="text-xs text-gray-500 font-medium">Proyecto</p>
                            <p class="font-semibold text-gray-800"><?= htmlspecialchars($presupuesto['nombre_proyecto'] ?? 'No Asignado') ?></p>
                        </div>
                        <div class="p-3 bg-indigo-50 rounded-lg">
                            <p class="text-xs text-gray-500 font-medium">Estado</p>
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold <?= $estado_color ?>"><?= htmlspecialchars($estado) ?></span>
                        </div>
                        <div class="p-3 bg-indigo-50 rounded-lg">
                            <p class="text-xs text-gray-500 font-medium">Fecha Creación</p>
                            <p class="font-semibold text-gray-800"><?= htmlspecialchars($presupuesto['fecha_creacion'] ?? 'N/A') ?></p>
                        </div>
                        <div class="p-3 bg-indigo-50 rounded-lg">
                            <p class="text-xs text-gray-500 font-medium">Creado por</p>
                            <p class="font-semibold text-gray-800"><?= htmlspecialchars($presupuesto['creado_por'] ?? 'Usuario del Sistema') ?></p>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Ítems Presupuestados (Detalle) -->
                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex justify-between items-center">
                        Detalle de Ítems 
                        <span class="text-sm font-normal text-gray-500"><?= count($presupuesto['items'] ?? []) ?> ítems</span>
                    </h2>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ÍTEM / DESCRIPCIÓN</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">TIPO</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">MONTO ASIGNADO</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (isset($presupuesto['items']) && !empty($presupuesto['items'])): ?>
                                    <?php foreach ($presupuesto['items'] as $item): ?>
                                        <tr>
                                            <td class="px-4 py-3 whitespace-normal text-sm font-medium text-gray-900"><?= htmlspecialchars($item['descripcion'] ?? 'Ítem sin descripción') ?></td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($item['tipo'] ?? 'N/A') ?></td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-semibold text-indigo-600"><?= format_currency((float)($item['monto'] ?? 0)) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr class="bg-indigo-50 font-bold border-t-2 border-indigo-200">
                                        <td class="px-4 py-3 text-right text-base" colspan="2">TOTAL PRESUPUESTADO:</td>
                                        <td class="px-4 py-3 text-right text-base text-indigo-700"><?= format_currency($monto_total) ?></td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="px-4 py-4 text-center text-sm text-gray-500">Este presupuesto no tiene ítems detallados.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        
                    </div>
                </div>

                <!-- SECCIÓN: Análisis de Variaciones -->
                <?php if (isset($analisis_variaciones) && !isset($analisis_variaciones['error']) && !empty($analisis_variaciones['items'])): ?>
                <div class="bg-white p-6 rounded-xl shadow-lg border-t-4 border-blue-500">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        📊 Análisis de Variaciones por Ítem
                        <span class="ml-2 text-sm font-normal text-gray-500">
                            (<?= count($analisis_variaciones['items']) ?> ítems analizados)
                        </span>
                    </h2>

                    <!-- Resumen General -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 p-4 bg-blue-50 rounded-lg">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600"><?= $analisis_variaciones['resumen']['items_ok'] ?></div>
                            <div class="text-sm text-gray-600">Dentro presupuesto</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-yellow-600"><?= $analisis_variaciones['resumen']['items_precaucion'] ?></div>
                            <div class="text-sm text-gray-600">En precaución</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-red-600"><?= $analisis_variaciones['resumen']['items_excedidos'] ?></div>
                            <div class="text-sm text-gray-600">Excedidos</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold <?= $analisis_variaciones['resumen']['total_excedido'] > 0 ? 'text-red-600' : 'text-green-600' ?>">
                                $<?= number_format($analisis_variaciones['resumen']['total_excedido'], 2) ?>
                            </div>
                            <div class="text-sm text-gray-600">Total excedido</div>
                        </div>
                    </div>

                    <!-- Lista de Ítems por Categoría -->
                    <div class="space-y-4">
                        <!-- Ítems Excedidos -->
                        <?php $items_excedidos = array_filter($analisis_variaciones['items'], fn($item) => $item['categoria'] === 'excedido'); ?>
                        <?php if (!empty($items_excedidos)): ?>
                        <div class="border-l-4 border-red-400 bg-red-50 p-4 rounded">
                            <h3 class="font-semibold text-red-800 mb-2 flex items-center">
                                🔴 Ítems Excedidos del Presupuesto
                            </h3>
                            <div class="space-y-2">
                                <?php foreach ($items_excedidos as $item): ?>
                                <div class="flex justify-between items-center p-2 bg-white rounded border">
                                    <div class="flex-1">
                                        <span class="font-medium"><?= htmlspecialchars($item['descripcion']) ?></span>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm text-red-600 font-semibold">
                                            $<?= number_format($item['gastado'], 2) ?> / $<?= number_format($item['presupuestado'], 2) ?>
                                        </div>
                                        <div class="text-xs text-red-500">
                                            Excedido: $<?= number_format(abs($item['diferencia']), 2) ?> 
                                            (<?= number_format($item['porcentaje'], 1) ?>%)
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Ítems en Precaución -->
                        <?php $items_precaucion = array_filter($analisis_variaciones['items'], fn($item) => $item['categoria'] === 'precaucion'); ?>
                        <?php if (!empty($items_precaucion)): ?>
                        <div class="border-l-4 border-yellow-400 bg-yellow-50 p-4 rounded">
                            <h3 class="font-semibold text-yellow-800 mb-2 flex items-center">
                                🟡 Ítems en Precaución
                            </h3>
                            <div class="space-y-2">
                                <?php foreach ($items_precaucion as $item): ?>
                                <div class="flex justify-between items-center p-2 bg-white rounded border">
                                    <div class="flex-1">
                                        <span class="font-medium"><?= htmlspecialchars($item['descripcion']) ?></span>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm text-yellow-600 font-semibold">
                                            $<?= number_format($item['gastado'], 2) ?> / $<?= number_format($item['presupuestado'], 2) ?>
                                        </div>
                                        <div class="text-xs text-yellow-500">
                                            <?= number_format($item['porcentaje'], 1) ?>% utilizado
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Ítems Dentro del Presupuesto -->
                        <?php $items_ok = array_filter($analisis_variaciones['items'], fn($item) => $item['categoria'] === 'dentro_presupuesto'); ?>
                        <?php if (!empty($items_ok)): ?>
                        <div class="border-l-4 border-green-400 bg-green-50 p-4 rounded">
                            <h3 class="font-semibold text-green-800 mb-2 flex items-center">
                                🟢 Ítems Dentro del Presupuesto
                            </h3>
                            <div class="space-y-2">
                                <?php foreach ($items_ok as $item): ?>
                                <div class="flex justify-between items-center p-2 bg-white rounded border">
                                    <div class="flex-1">
                                        <span class="font-medium"><?= htmlspecialchars($item['descripcion']) ?></span>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm text-green-600 font-semibold">
                                            $<?= number_format($item['gastado'], 2) ?> / $<?= number_format($item['presupuestado'], 2) ?>
                                        </div>
                                        <div class="text-xs text-green-500">
                                            Restante: $<?= number_format($item['diferencia'], 2) ?> 
                                            (<?= number_format($item['porcentaje'], 1) ?>%)
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Resumen de Impacto -->
                    <?php if ($analisis_variaciones['resumen']['total_excedido'] > 0): ?>
                    <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <h4 class="font-semibold text-red-800 mb-2">📈 Impacto Total de las Desviaciones</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-red-600 font-medium">Total excedido:</span>
                                <span class="ml-2">$<?= number_format($analisis_variaciones['resumen']['total_excedido'], 2) ?></span>
                            </div>
                            <div>
                                <span class="text-green-600 font-medium">Total ahorrado:</span>
                                <span class="ml-2">$<?= number_format($analisis_variaciones['resumen']['total_ahorrado'], 2) ?></span>
                            </div>
                        </div>
                        <?php if ($analisis_variaciones['resumen']['total_excedido'] > $analisis_variaciones['resumen']['total_ahorrado']): ?>
                        <p class="text-red-600 text-sm mt-2 font-medium">
                            ⚠️ Neto negativo: El exceso supera los ahorros en otros ítems
                        </p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            </div>
            
            <!-- Columna Lateral: Resumen de Montos y Acciones -->
            <div class="space-y-6">
                
                <!-- Tarjeta de Resumen Financiero -->
                <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-yellow-500">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Resumen de Fondos</h2>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Presupuestado:</span>
                            <span class="font-bold text-indigo-600"><?= format_currency($monto_total) ?></span>
                        </div>
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-gray-600">Gastado:</span>
                            <span class="font-bold text-red-600"><?= format_currency($gastado) ?></span>
                        </div>
                        <div class="flex justify-between pt-2">
                            <span class="text-lg font-semibold text-gray-700">Restante:</span>
                            <span class="text-lg font-bold <?= $color_restante ?>"><?= format_currency($restante) ?></span>
                        </div>
                    </div>
                    
                    <!-- Barra de Progreso -->
                    <div class="mt-4">
                        <p class="text-xs text-gray-500 mb-1">Gasto: <?= number_format($porcentaje, 1) ?>%</p>
                        <div class="h-2 bg-gray-200 rounded-full">
                            <div class="h-full rounded-full transition-all duration-500" 
                                style="width: <?= min($porcentaje, 100) ?>%; background-color: <?= $porcentaje < 80 ? '#3b82f6' : ($porcentaje < 100 ? '#f59e0b' : '#ef4444') ?>">
                            </div>
                        </div>
                    </div>
                    <!-- ALERTA DE DESVIACIONES -->
<?php if (isset($analisis_desviaciones) && !isset($analisis_desviaciones['error'])): 
    $alerta = $analisis_desviaciones;
    $color_classes = [
        'success' => 'bg-green-50 border-green-200 text-green-800',
        'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
        'danger' => 'bg-orange-50 border-orange-200 text-orange-800',
        'critical' => 'bg-red-50 border-red-200 text-red-800'
    ];
    $icon_classes = [
        'success' => 'text-green-400',
        'warning' => 'text-yellow-400', 
        'danger' => 'text-orange-400',
        'critical' => 'text-red-400'
    ];
?>
<div class="mt-4 p-4 border-l-4 <?= $color_classes[$alerta['nivel_alerta']] ?> rounded">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <?php if ($alerta['nivel_alerta'] === 'success'): ?>
                <svg class="h-5 w-5 <?= $icon_classes[$alerta['nivel_alerta']] ?>" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            <?php elseif ($alerta['nivel_alerta'] === 'warning'): ?>
                <svg class="h-5 w-5 <?= $icon_classes[$alerta['nivel_alerta']] ?>" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            <?php else: ?>
                <svg class="h-5 w-5 <?= $icon_classes[$alerta['nivel_alerta']] ?>" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            <?php endif; ?>
        </div>
        <div class="ml-3">
            <p class="text-sm font-medium"><?= $alerta['mensaje'] ?></p>
            <?php if ($alerta['esta_sobrepresupuestado']): ?>
                <p class="text-sm mt-1">
                    Excedido en: <strong>$<?= number_format(abs($alerta['diferencia_absoluta']), 2) ?></strong>
                    (<?= number_format($alerta['diferencia_porcentual'], 1) ?>%)
                </p>
            <?php endif; ?>
            <!-- PROYECCIONES FINANCIERAS -->
<?php if (isset($proyecciones) && !isset($proyecciones['error']) && $proyecciones['es_proyectable']): ?>
<div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
    <h3 class="text-sm font-semibold text-blue-800 mb-2 flex items-center">
        📈 Proyección Financiera
    </h3>
    
    <div class="space-y-1 text-xs">
        <div class="flex justify-between">
            <span class="text-blue-600">Tasa mensual:</span>
            <span class="font-semibold">$<?= number_format($proyecciones['tasa_mensual'], 2) ?>/mes</span>
        </div>
        
        <?php if ($proyecciones['meses_restantes'] > 0): ?>
        <div class="flex justify-between">
            <span class="text-blue-600">Agotamiento estimado:</span>
            <span class="font-semibold"><?= $proyecciones['fecha_agotamiento'] ?></span>
        </div>
        <?php else: ?>
        <div class="flex justify-between">
            <span class="text-red-600">Estado:</span>
            <span class="font-semibold text-red-600">Presupuesto agotado</span>
        </div>
        <?php endif; ?>
        
        <div class="flex justify-between pt-1 border-t border-blue-200">
            <span class="text-blue-600">Proyección final:</span>
            <span class="font-semibold <?= $proyecciones['nivel_alerta_proyeccion'] === 'success' ? 'text-green-600' : ($proyecciones['nivel_alerta_proyeccion'] === 'warning' ? 'text-yellow-600' : 'text-red-600') ?>">
                $<?= number_format($proyecciones['proyeccion_final'], 2) ?> 
                (<?= number_format($proyecciones['porcentaje_proyeccion'], 1) ?>%)
            </span>
        </div>
        
        <?php if ($proyecciones['nivel_alerta_proyeccion'] !== 'success'): ?>
        <div class="mt-1 text-xs <?= $proyecciones['nivel_alerta_proyeccion'] === 'warning' ? 'text-yellow-600' : 'text-red-600' ?>">
            <?php if ($proyecciones['nivel_alerta_proyeccion'] === 'warning'): ?>
                ⚠️ Proyección cerca del límite
            <?php else: ?>
                🔴 Proyección excede el presupuesto
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php elseif (isset($proyecciones) && !isset($proyecciones['error']) && !$proyecciones['es_proyectable']): ?>
<div class="mt-4 p-3 bg-gray-50 border border-gray-200 rounded-lg">
    <h3 class="text-sm font-semibold text-gray-600 mb-1 flex items-center">
        📈 Proyección Financiera
    </h3>
    <p class="text-xs text-gray-500">Datos insuficientes para proyección. Se necesitan más gastos históricos.</p>
</div>
<?php endif; ?>
        </div>
    </div>
    
</div>

<?php endif; ?>


                </div>
                
                <!-- Tarjeta de Acciones -->
                <div class="bg-white p-6 rounded-xl shadow-lg space-y-3">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Acciones</h2>
                    
                    <!-- Registrar Gasto Real -->
                    <a href="<?= url('presupuesto/gastosReales/' . $presupuesto['id']) ?>" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 transition duration-150">
    <!-- Icono de añadir -->
    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
    Registrar Gasto Real
</a>

                    
                    <?php if ($estado === 'Pendiente'): ?>
                        <!-- Aprobar Presupuesto -->
                        <a href="/presupuesto/aprobar/<?= $presupuesto['id'] ?>" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-indigo-700 bg-indigo-100 hover:bg-indigo-200 transition duration-150">
                            Aprobar Presupuesto
                        </a>
                        <!-- Rechazar Presupuesto -->
                        <a href="/presupuesto/rechazar/<?= $presupuesto['id'] ?>" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-red-700 bg-red-100 hover:bg-red-200 transition duration-150">
                            Rechazar Presupuesto
                        </a>
                    <?php endif; ?>
                    
                    <!-- Editar Presupuesto -->
                   <a href="<?= url('presupuesto/editar/' . $presupuesto['id']) ?>" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 transition duration-150">
    Editar Presupuesto
</a>
                </div>
            </div>

        </div>
    </div>
</body>
</html>