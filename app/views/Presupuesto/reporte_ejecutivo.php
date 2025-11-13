<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?></title>
    <style>
        /* Estilos optimizados para PDF */
        body { 
            font-family: 'Arial', sans-serif; 
            margin: 0;
            padding: 20px;
            color: #333;
            font-size: 12px;
        }
        .header {
            border-bottom: 3px solid #2c5aa0;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2c5aa0;
        }
        .report-title {
            font-size: 18px;
            color: #333;
            margin: 10px 0;
        }
        .section {
            margin: 15px 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #2c5aa0;
            margin-bottom: 10px;
        }
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin: 10px 0;
        }
        .metric-card {
            padding: 10px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            text-align: center;
        }
        .metric-value {
            font-size: 18px;
            font-weight: bold;
            margin: 5px 0;
        }
        .metric-label {
            font-size: 11px;
            color: #666;
        }
        .positive { color: #28a745; }
        .negative { color: #dc3545; }
        .neutral { color: #6c757d; }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .recommendation {
            background-color: #e8f4fd;
            padding: 15px;
            border-left: 4px solid #2c5aa0;
            margin: 15px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <!-- Encabezado -->
    <div class="header">
        <div class="company-name">SISTEMA INTEGRAL MOBILIARIO</div>
        <div class="report-title"><?= htmlspecialchars($titulo) ?></div>
        <div style="display: flex; justify-content: space-between; font-size: 11px;">
            <span>Proyecto: <?= htmlspecialchars($presupuesto['nombre_proyecto']) ?></span>
            <span>Generado: <?= $fecha_generacion ?></span>
        </div>
    </div>

    <!-- Resumen Ejecutivo -->
    <div class="section">
        <div class="section-title">📊 RESUMEN EJECUTIVO</div>
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-value <?= $rentabilidad['utilidad'] > 0 ? 'positive' : 'negative' ?>">
                    $<?= number_format($rentabilidad['utilidad'], 2) ?>
                </div>
                <div class="metric-label">UTILIDAD/PÉRDIDA</div>
            </div>
            <div class="metric-card">
                <div class="metric-value <?= $rentabilidad['roi'] > 0 ? 'positive' : 'negative' ?>">
                    <?= number_format($rentabilidad['roi'], 1) ?>%
                </div>
                <div class="metric-label">ROI</div>
            </div>
            <div class="metric-card">
                <div class="metric-value">
                    $<?= number_format($presupuesto['monto_total'], 2) ?>
                </div>
                <div class="metric-label">PRESUPUESTO</div>
            </div>
            <div class="metric-card">
                <div class="metric-value">
                    $<?= number_format($rentabilidad['gastado'], 2) ?>
                </div>
                <div class="metric-label">COSTOS REALES</div>
            </div>
        </div>
    </div>

    <!-- Rentabilidad -->
    <div class="section">
        <div class="section-title">💰 ANÁLISIS DE RENTABILIDAD</div>
        <table class="table">
            <tr>
                <th>Indicador</th>
                <th>Valor</th>
                <th>Estado</th>
            </tr>
            <tr>
                <td>Margen de Utilidad</td>
                <td><?= number_format($rentabilidad['margen_utilidad'], 1) ?>%</td>
                <td>
                    <span style="color: <?= $rentabilidad['margen_utilidad'] > 15 ? '#28a745' : '#dc3545' ?>">
                        <?= $rentabilidad['margen_utilidad'] > 15 ? '✅ Saludable' : '⚠️ Por mejorar' ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td>Nivel de Rentabilidad</td>
                <td><?= ucfirst($rentabilidad['nivel_rentabilidad']) ?></td>
                <td>
                    <span style="color: <?= $rentabilidad['es_rentable'] ? '#28a745' : '#dc3545' ?>">
                        <?= $rentabilidad['es_rentable'] ? '✅ Rentable' : '❌ No Rentable' ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td>Duración del Proyecto</td>
                <td><?= number_format($rentabilidad['roi_avanzado']['duracion_proyecto_meses'] ?? 0, 1) ?> meses</td>
                <td>📅</td>
            </tr>
        </table>
    </div>

    <!-- Análisis de Costos (Resumido) -->
    <?php if (!isset($analisis_costos['error'])): ?>
    <div class="section">
        <div class="section-title">🔍 DISTRIBUCIÓN DE COSTOS</div>
        <table class="table">
            <tr>
                <th>Tipo de Costo</th>
                <th>Presupuestado</th>
                <th>Real</th>
                <th>Desviación</th>
            </tr>
            <?php foreach ($analisis_costos['por_tipo'] as $tipo => $datos): ?>
            <tr>
                <td><?= htmlspecialchars($tipo) ?></td>
                <td>$<?= number_format($datos['presupuestado'], 2) ?></td>
                <td>$<?= number_format($datos['gastado'], 2) ?></td>
                <td style="color: <?= $datos['desviacion'] > 0 ? '#dc3545' : '#28a745' ?>">
                    $<?= number_format(abs($datos['desviacion']), 2) ?> 
                    (<?= number_format($datos['porcentaje_desviacion'], 1) ?>%)
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>

    <!-- Recomendaciones -->
    <div class="recommendation">
        <div style="font-weight: bold; margin-bottom: 8px;">💡 RECOMENDACIONES EJECUTIVAS</div>
        <div><?= htmlspecialchars($recomendacion) ?></div>
    </div>

    <!-- Pie de página -->
    <div class="footer">
        Este reporte fue generado automáticamente por el Sistema Integral Mobiliario.<br>
        Los datos son confidenciales y para uso interno de la empresa.
    </div>

    <!-- Instrucciones para imprimir como PDF -->
    <script>
        // Auto-imprimir cuando se cargue la página (opcional)
        // window.onload = function() { window.print(); }
        
        // Mensaje para el usuario
        console.log('Para guardar como PDF: Ctrl+P → Seleccionar "Guardar como PDF"');
    </script>
</body>
</html>