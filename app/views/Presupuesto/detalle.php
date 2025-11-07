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