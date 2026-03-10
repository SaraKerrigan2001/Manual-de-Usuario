<?php
/**
 * CONFIGURACIÓN DE SESIONES
 * Configuración optimizada para evitar conflictos entre pestañas
 */

// Configuración de sesión segura
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

// Configuración para evitar conflictos entre pestañas con diferentes roles
ini_set('session.cookie_samesite', 'Lax');

// Tiempo de vida de la sesión (2 horas)
ini_set('session.gc_maxlifetime', 7200);
ini_set('session.cookie_lifetime', 7200);

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Regenerar ID de sesión periódicamente para seguridad
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 1800) { // Cada 30 minutos
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}
