<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S.I.M. | <?php echo $titulo ?? 'Sistema de Presupuestos'; ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f7f6; }
        .container { width: 90%; max-width: 1200px; margin: 20px auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1, h2 { color: #333; }
        .btn-primary { background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 5px; text-decoration: none; cursor: pointer; display: inline-block; }
        .btn-secondary { background-color: #6c757d; color: white; padding: 5px 8px; border-radius: 4px; text-decoration: none; }
        .btn-delete { background-color: #dc3545; color: white; padding: 5px 8px; border-radius: 4px; text-decoration: none; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .error-msg { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; margin-bottom: 20px; border-radius: 5px; }
        /* Puedes añadir aquí los estilos de estado (Pendiente, Aprobado, etc.) */
    </style>
</head>
<body>