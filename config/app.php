<?php
/**
 * Configuración de la Aplicación - Versión Corregida
 */

// Configuración general
return [
    'app_name' => 'Sistema Integral Mobiliario',
    'app_version' => '1.0.0',
    'environment' => 'development', // development, production
    
    // Configuración de sesión
    'session' => [
        'timeout' => 7200, // 2 horas en segundos
        'name' => 'mobiliario_sess'
    ],
    
    // Configuración de uploads
    'uploads' => [
        'max_size' => 5 * 1024 * 1024, // 5MB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']
    ],
    
    // Módulos activos
    'modules' => [
        'auth',
        'recursos_humanos', 
        'dashboard'
    ]
];