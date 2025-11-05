<?php
/**
 * Redirección temporal - Usar mientras se soluciona mod_rewrite
 */

$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$basePath = '/SISTEMA_INTEGRAL_MOBILIARIO/public';

// Determinar a dónde redirigir
if (strpos($requestUri, $basePath . '/') !== false) {
    $path = str_replace($basePath, '', $requestUri);
    $path = trim($path, '/');
    
    if (empty($path)) {
        $path = 'login';
    }
    
    header("Location: {$basePath}/index.php?url={$path}");
} else {
    header("Location: {$basePath}/index.php");
}
exit;