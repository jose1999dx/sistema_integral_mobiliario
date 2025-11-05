<?php
/**
 * Middleware de Autenticación - Corregido
 */

class AuthMiddleware {
    
    /**
     * Verificar si usuario está autenticado
     */
    public static function checkAuth() {
        if (!Auth::check()) {
            self::redirect('/login?error=debes_iniciar_sesion');
        }
        
        // Verificar timeout de sesión
        if (!Auth::checkSessionTimeout()) {
            self::redirect('/login?error=sesion_expirada');
        }
    }
    
    /**
     * Verificar rol específico
     */
    public static function checkRole($rol) {
        self::checkAuth();
        
        if (!Auth::hasRole($rol)) {
            self::redirect('/acceso-denegado');
        }
    }
    
    /**
     * Redirección corregida
     */
    private static function redirect($path) {
        $url = BASE_URL . '/index.php?url=' . ltrim($path, '/');
        if (headers_sent()) {
            echo "<script>window.location.href = '$url';</script>";
            echo "<noscript><meta http-equiv='refresh' content='0;url=$url'></noscript>";
        } else {
            header("Location: $url");
        }
        exit;
    }
}