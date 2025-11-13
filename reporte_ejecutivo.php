<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Ejecutivo - SIM</title>
</head>
<body>
    <h1>✅ REPORTE EJECUTIVO FUNCIONANDO</h1>
    <h2>Proyecto: <?= htmlspecialchars($presupuesto['nombre'] ?? 'N/A') ?></h2>
    <p>Esta es una vista básica de prueba.</p>
    <p><strong>Fecha:</strong> <?= $fecha_generacion ?? date('d/m/Y H:i:s') ?></p>
    
    <h3>Métricas Básicas:</h3>
    <ul>
        <li>Utilidad: $<?= number_format($rentabilidad['utilidad'] ?? 0, 2) ?></li>
        <li>ROI: <?= number_format($rentabilidad['roi'] ?? 0, 1) ?>%</li>
        <li>Estado: <?= ($rentabilidad['es_rentable'] ?? false) ? 'Rentable' : 'No Rentable' ?></li>
    </ul>
    
    <p><em>Para guardar como PDF: Ctrl+P → Guardar como PDF</em></p>
</body>
</html>