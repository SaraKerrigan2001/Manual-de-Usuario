<?php

class ExperienciaController {
    
    /**
     * Página principal - redirige a listar
     */
    public function index() {
        header('Location: index.php?controlador=Experiencia&accion=listar');
        exit;
    }
    
    /**
     * Listar todas las experiencias/especialidades
     */
    public function listar() {
        $experiencias = Experiencia::obtenerTodas();
        
        require_once('Vista/Layouts/header.php');
        require_once('Vista/Experiencia/listar.php');
        require_once('Vista/Layouts/footer.php');
    }
    
    /**
     * Formulario para nueva experiencia
     */
    public function nueva() {
        require_once('Vista/Layouts/header.php');
        require_once('Vista/Experiencia/nueva.php');
        require_once('Vista/Layouts/footer.php');
    }
    
    /**
     * Guardar nueva experiencia
     */
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controlador=Experiencia&accion=nueva');
            exit;
        }
        
        $datos = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'area' => $_POST['area'] ?? '',
            'programas_relacionados' => $_POST['programas_relacionados'] ?? []
        ];
        
        $resultado = Experiencia::crear($datos);
        
        if ($resultado['success']) {
            $_SESSION['mensaje'] = 'Experiencia creada exitosamente';
            $_SESSION['tipo_mensaje'] = 'success';
            header('Location: index.php?controlador=Experiencia&accion=listar');
        } else {
            $_SESSION['errores'] = $resultado['errores'];
            $_SESSION['datos_form'] = $datos;
            header('Location: index.php?controlador=Experiencia&accion=nueva');
        }
        exit;
    }
    
    /**
     * Editar experiencia existente
     */
    public function editar() {
        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        if (!$id) {
            header('Location: index.php?controlador=Experiencia&accion=listar');
            exit;
        }
        
        $experiencia = Experiencia::obtenerPorId($id);
        if (!$experiencia) {
            $_SESSION['mensaje'] = 'Experiencia no encontrada';
            $_SESSION['tipo_mensaje'] = 'danger';
            header('Location: index.php?controlador=Experiencia&accion=listar');
            exit;
        }
        
        require_once('Vista/Layouts/header.php');
        require_once('Vista/Experiencia/editar.php');
        require_once('Vista/Layouts/footer.php');
    }
}

?>
