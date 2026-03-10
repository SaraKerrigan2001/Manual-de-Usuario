<?php
/**
 * SISTEMA DE CONTEXTO DE ROL
 * Maneja el contexto específico de cada rol para evitar conflictos entre pestañas
 */

/**
 * Inicializar contexto de rol
 * @param string $rol El rol a inicializar (coordinador, instructor, usuario)
 */
function inicializarContextoRol($rol) {
    // Guardar el contexto actual del rol
    $_SESSION['contexto_rol'] = $rol;
    $_SESSION['contexto_timestamp'] = time();
    
    // Inicializar variables específicas del rol si no existen
    if (!isset($_SESSION['rol_data'])) {
        $_SESSION['rol_data'] = [];
    }
    
    if (!isset($_SESSION['rol_data'][$rol])) {
        $_SESSION['rol_data'][$rol] = [
            'ultima_actividad' => time(),
            'preferencias' => [],
            'cache' => []
        ];
    }
    
    // Actualizar última actividad
    $_SESSION['rol_data'][$rol]['ultima_actividad'] = time();
}

/**
 * Obtener información del usuario para un rol específico
 * @param string $rol El rol del que se quiere obtener información
 * @return array Información del usuario para ese rol
 */
function obtenerInfoUsuarioRol($rol) {
    $info = [
        'rol' => $rol,
        'usuario_id' => $_SESSION['usuario_id'] ?? null,
        'nombre' => $_SESSION['nombre'] ?? 'Usuario',
        'email' => $_SESSION['email'] ?? '',
        'rol_sistema' => $_SESSION['rol'] ?? '',
        'ultima_actividad' => $_SESSION['rol_data'][$rol]['ultima_actividad'] ?? time()
    ];
    
    return $info;
}

/**
 * Verificar si el usuario tiene acceso al rol especificado
 * @param string $rol_requerido El rol que se requiere
 * @return bool True si tiene acceso, False si no
 */
function verificarAccesoRol($rol_requerido) {
    if (!isset($_SESSION['usuario_id'])) {
        return false;
    }
    
    $rol_usuario = $_SESSION['rol'] ?? '';
    
    // Mapeo de roles del sistema a roles de contexto
    $mapeo_roles = [
        'administrador' => ['administrador'],
        'coordinador' => ['coordinador', 'administrador'], // El coordinador puede ver el hub de administrador si se requiere
        'instructor' => ['instructor'],
        'usuario' => ['usuario']
    ];
    
    if (isset($mapeo_roles[$rol_usuario])) {
        return in_array($rol_requerido, $mapeo_roles[$rol_usuario]);
    }
    
    return false;
}

/**
 * Guardar preferencia del rol
 * @param string $rol El rol
 * @param string $clave La clave de la preferencia
 * @param mixed $valor El valor de la preferencia
 */
function guardarPreferenciaRol($rol, $clave, $valor) {
    if (!isset($_SESSION['rol_data'][$rol]['preferencias'])) {
        $_SESSION['rol_data'][$rol]['preferencias'] = [];
    }
    
    $_SESSION['rol_data'][$rol]['preferencias'][$clave] = $valor;
}

/**
 * Obtener preferencia del rol
 * @param string $rol El rol
 * @param string $clave La clave de la preferencia
 * @param mixed $default Valor por defecto si no existe
 * @return mixed El valor de la preferencia o el valor por defecto
 */
function obtenerPreferenciaRol($rol, $clave, $default = null) {
    if (isset($_SESSION['rol_data'][$rol]['preferencias'][$clave])) {
        return $_SESSION['rol_data'][$rol]['preferencias'][$clave];
    }
    
    return $default;
}

/**
 * Limpiar contexto de rol (útil al cerrar sesión)
 * @param string $rol El rol a limpiar (opcional, si no se especifica limpia todos)
 */
function limpiarContextoRol($rol = null) {
    if ($rol === null) {
        // Limpiar todos los contextos
        unset($_SESSION['contexto_rol']);
        unset($_SESSION['contexto_timestamp']);
        unset($_SESSION['rol_data']);
    } else {
        // Limpiar solo el contexto especificado
        if (isset($_SESSION['rol_data'][$rol])) {
            unset($_SESSION['rol_data'][$rol]);
        }
    }
}

/**
 * Obtener el contexto actual
 * @return string|null El rol del contexto actual o null si no hay
 */
function obtenerContextoActual() {
    return $_SESSION['contexto_rol'] ?? null;
}
?>
