<?php
// logout.php - destruye la sesión y redirige al login
if (session_status() === PHP_SESSION_NONE) session_start();

// Guardar mensaje antes de destruir la sesión
$mensaje = 'Sesión cerrada exitosamente. ¡Hasta luego!';

// Limpia la sesión
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Iniciar nueva sesión para mostrar el mensaje
session_start();
$_SESSION['mensaje'] = $mensaje;
$_SESSION['tipo_mensaje'] = 'success';

// Redirigir directamente al login
header('Location: index.php?controlador=Auth&accion=login');
exit;
?>
