<?php
/**
 * Sistema de Autenticación - Corregido
 */

class Auth {
    /**
     * Iniciar sesión de usuario
     */
    public static function login($usuario) {
        $_SESSION['usuario'] = [
            'id' => $usuario->id,
            'username' => $usuario->username,
            'nombre' => $usuario->nombre,
            'rol' => $usuario->rol,
            'login_time' => time()
        ];
        
        // Actualizar último login
        if (property_exists($usuario, 'ultimo_login')) {
            $usuario->ultimo_login = date('Y-m-d H:i:s');
            $usuario->save();
        }
    }
    
    /**
     * Cerrar sesión
     */
    public static function logout() {
        session_unset();
        session_destroy();
        session_start(); // Reiniciar sesión vacía
    }
    
    /**
     * Verificar si usuario está autenticado
     */
    public static function check() {
        return isset($_SESSION['usuario']);
    }
    
    /**
     * Obtener usuario actual
     */
    public static function user() {
        return $_SESSION['usuario'] ?? null;
    }
    
    /**
     * Obtener ID del usuario actual
     */
    public static function id() {
        return $_SESSION['usuario']['id'] ?? null;
    }
    
    /**
     * Verificar si usuario tiene un rol específico
     */
    public static function hasRole($rol) {
        if (!self::check()) {
            return false;
        }
        
        return $_SESSION['usuario']['rol'] === $rol;
    }
    
    /**
     * Verificar timeout de sesión
     */
    public static function checkSessionTimeout() {
        if (!isset($_SESSION['usuario']['login_time'])) {
            return false;
        }
        
        $timeout = 7200; // 2 horas
        $currentTime = time();
        $loginTime = $_SESSION['usuario']['login_time'];
        
        if (($currentTime - $loginTime) > $timeout) {
            self::logout();
            return false;
        }
        
        // Renovar tiempo de sesión en actividad
        $_SESSION['usuario']['login_time'] = $currentTime;
        return true;
    }
}