<?php

class SedeController {
    
    /**
     * Página principal - listar sedes
     */
    public function index() {
        $db = Db::getConnect();
        $sede = new Sede($db);
        $stmt = $sede->obtenerTodas();
        $sedes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require_once('Vista/Layouts/header.php');
        require_once('Vista/Sede/listar.php');
        require_once('Vista/Layouts/footer.php');
    }
    
    /**
     * Formulario para nueva sede
     */
    public function nueva() {
        require_once('Vista/Layouts/header.php');
        require_once('Vista/Sede/nueva.php');
        require_once('Vista/Layouts/footer.php');
    }
    
    /**
     * Guardar nueva sede
     */
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controlador=Sede&accion=nueva');
            exit;
        }
        
        $db = Db::getConnect();
        $sede = new Sede($db);
        
        $sede->sede_nombre = trim($_POST['sede_nombre'] ?? '');
        $sede->direccion = trim($_POST['direccion'] ?? '');
        $sede->ciudad = trim($_POST['ciudad'] ?? '');
        $sede->telefono = trim($_POST['telefono'] ?? '');
        
        // Validaciones
        $errores = [];
        if (empty($sede->sede_nombre)) {
            $errores[] = 'El nombre de la sede es obligatorio';
        }
        if (empty($sede->ciudad)) {
            $errores[] = 'La ciudad es obligatoria';
        }
        
        if (!empty($errores)) {
            $_SESSION['errores'] = $errores;
            $_SESSION['datos_form'] = $_POST;
            header('Location: index.php?controlador=Sede&accion=nueva');
            exit;
        }
        
        if ($sede->crear()) {
            $_SESSION['mensaje'] = 'Sede creada exitosamente';
            $_SESSION['tipo_mensaje'] = 'success';
            header('Location: index.php?controlador=Sede&accion=index');
        } else {
            $_SESSION['mensaje'] = 'Error al crear la sede';
            $_SESSION['tipo_mensaje'] = 'danger';
            header('Location: index.php?controlador=Sede&accion=nueva');
        }
        exit;
    }
    
    /**
     * Editar sede existente
     */
    public function editar() {
        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        if (!$id) {
            header('Location: index.php?controlador=Sede&accion=index');
            exit;
        }
        
        $db = Db::getConnect();
        $sede = new Sede($db);
        $sede->sede_id = $id;
        
        if (!$sede->obtenerPorId()) {
            $_SESSION['mensaje'] = 'Sede no encontrada';
            $_SESSION['tipo_mensaje'] = 'danger';
            header('Location: index.php?controlador=Sede&accion=index');
            exit;
        }
        
        require_once('Vista/Layouts/header.php');
        require_once('Vista/Sede/editar.php');
        require_once('Vista/Layouts/footer.php');
    }
    
    /**
     * Actualizar sede
     */
    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controlador=Sede&accion=index');
            exit;
        }
        
        $db = Db::getConnect();
        $sede = new Sede($db);
        
        $sede->sede_id = filter_var($_POST['sede_id'] ?? 0, FILTER_VALIDATE_INT);
        $sede->sede_nombre = trim($_POST['sede_nombre'] ?? '');
        $sede->direccion = trim($_POST['direccion'] ?? '');
        $sede->ciudad = trim($_POST['ciudad'] ?? '');
        $sede->telefono = trim($_POST['telefono'] ?? '');
        
        // Validaciones
        $errores = [];
        if (empty($sede->sede_nombre)) {
            $errores[] = 'El nombre de la sede es obligatorio';
        }
        if (empty($sede->ciudad)) {
            $errores[] = 'La ciudad es obligatoria';
        }
        
        if (!empty($errores)) {
            $_SESSION['errores'] = $errores;
            header('Location: index.php?controlador=Sede&accion=editar&id=' . $sede->sede_id);
            exit;
        }
        
        if ($sede->actualizar()) {
            $_SESSION['mensaje'] = 'Sede actualizada exitosamente';
            $_SESSION['tipo_mensaje'] = 'success';
        } else {
            $_SESSION['mensaje'] = 'Error al actualizar la sede';
            $_SESSION['tipo_mensaje'] = 'danger';
        }
        
        header('Location: index.php?controlador=Sede&accion=index');
        exit;
    }
    
    /**
     * Eliminar sede (soft delete)
     */
    public function eliminar() {
        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        if (!$id) {
            header('Location: index.php?controlador=Sede&accion=index');
            exit;
        }
        
        $db = Db::getConnect();
        $sede = new Sede($db);
        $sede->sede_id = $id;
        
        if ($sede->eliminar()) {
            $_SESSION['mensaje'] = 'Sede eliminada exitosamente';
            $_SESSION['tipo_mensaje'] = 'success';
        } else {
            $_SESSION['mensaje'] = 'Error al eliminar la sede';
            $_SESSION['tipo_mensaje'] = 'danger';
        }
        
        header('Location: index.php?controlador=Sede&accion=index');
        exit;
    }
    
    /**
     * Buscar sedes
     */
    public function buscar() {
        $termino = trim($_GET['q'] ?? '');
        
        $db = Db::getConnect();
        $sede = new Sede($db);
        $stmt = $sede->buscar($termino);
        $sedes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require_once('Vista/Layouts/header.php');
        require_once('Vista/Sede/listar.php');
        require_once('Vista/Layouts/footer.php');
    }
}

?>
