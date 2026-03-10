<?php

/**
 * Sistema de enrutamiento MVC
 */

$controllers = array(
    'Auth'        => ['login', 'procesarLogin', 'registro', 'procesarRegistro', 'logout'],
    'Coordinador' => ['index', 'dashboard', 'nuevoPrograma', 'nuevaAsignacion', 'guardarPrograma', 'guardarAsignacion', 'seguimientoFicha', 'verTransversales', 'editarTransversal'],
    'Instructor'  => ['index', 'registrar', 'guardarInstructor', 'dashboard', 'perfil', 'editarPerfil'],
    'Usuario'     => ['dashboard', 'perfil'],
    'Experiencia' => ['index', 'listar', 'nueva', 'guardar', 'editar'],
    'Sede'        => ['index', 'nueva', 'guardar', 'editar', 'actualizar', 'eliminar', 'buscar']
);

// Validar que el controlador existe
if (array_key_exists($controlador, $controllers)) {
    if (in_array($accion, $controllers[$controlador])) {
        call($controlador, $accion);
    } else {
        // Acción no válida
        header("HTTP/1.0 404 Not Found");
        echo "Acción no encontrada";
    }
} else {
    // Controlador no válido
    header("HTTP/1.0 404 Not Found");
    echo "Controlador no encontrado";
}

/**
 * Función para llamar al controlador y acción correspondiente
 */
function call($controlador, $accion) {
    $controllerFile = 'Controlador/' . $controlador . 'Controller.php';
    
    if (!file_exists($controllerFile)) {
        die("Error: No se encontró el controlador $controlador");
    }
    
    require_once($controllerFile);

    switch($controlador) {
        case 'Auth':
            require_once('Modelo/Usuario.php');
            $controller = new AuthController();
            break;
        case 'Coordinador':
            require_once('Modelo/Coordinador.php');
            $controller = new CoordinadorController();
            break;
        case 'Instructor':
            if (file_exists('Modelo/Instructor.php')) {
                require_once('Modelo/Instructor.php');
            }
            $controller = new InstructorController();
            break;
        case 'Usuario':
            $controller = new UsuarioController();
            break;
        case 'Experiencia':
            if (file_exists('Modelo/Experiencia.php')) {
                require_once('Modelo/Experiencia.php');
            }
            $controller = new ExperienciaController();
            break;
        case 'Sede':
            if (file_exists('Modelo/Sede.php')) {
                require_once('Modelo/Sede.php');
            }
            $controller = new SedeController();
            break;
        default:
            die("Error: Controlador no válido");
    }
    
    if (method_exists($controller, $accion)) {
        $controller->{$accion}();
    } else {
        die("Error: La acción $accion no existe en el controlador $controlador");
    }
}

?>