<?php 
require_once(__DIR__ . '/api/session_config.php');
require_once('connection.php');

if (isset($_GET['controlador']) && isset($_GET['accion'])) {
    $controlador = htmlspecialchars($_GET['controlador'], ENT_QUOTES, 'UTF-8');
    $accion = htmlspecialchars($_GET['accion'], ENT_QUOTES, 'UTF-8');
} else {
    
    if (!isset($_SESSION['usuario_id'])) {
        $controlador = 'Auth';
        $accion = 'login';
    } else {

        switch($_SESSION['rol']) {
            case 'administrador':
                $controlador = 'Administrador';
                $accion = 'dashboard';
                break;
            case 'coordinador':
                $controlador = 'Coordinador';
                $accion = 'dashboard';
                break;
            case 'instructor':
                $controlador = 'Instructor';
                $accion = 'dashboard';
                break;
            case 'usuario':
                $controlador = 'Usuario';
                $accion = 'dashboard';
                break;
            default:
                $controlador = 'Auth';
                $accion = 'login';
        }
    }
}

require_once('Config/routing.php');
?>