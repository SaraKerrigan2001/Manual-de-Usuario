<?php

class InstructorController {
    
    /**
     * Página principal del instructor
     */
    public function index() {
        $instructores = Instructor::obtenerTodos();
        
        require_once('Vista/Layouts/header.php');
        require_once('Vista/Instructor/index.php');
        require_once('Vista/Layouts/footer.php');
    }
    
    /**
     * Cargar formulario de nuevo instructor
     */
    public function registrar() {
        $experiencias = Experiencia::obtenerTodas();
        
        require_once('Vista/Layouts/header.php');
        require_once('Vista/Instructor/registro_instructor.php');
        require_once('Vista/Layouts/footer.php');
    }
    
    /**
     * Guardar nuevo instructor
     */
    public function guardarInstructor() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controlador=Instructor&accion=registrar');
            exit;
        }
        
        $datos = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'apellido' => trim($_POST['apellido'] ?? ''),
            'documento' => trim($_POST['documento'] ?? ''),
            'tipo_documento' => $_POST['tipo_documento'] ?? 'CC',
            'email' => trim($_POST['email'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'especialidad' => trim($_POST['especialidad'] ?? ''),
            'fecha_ingreso' => $_POST['fecha_ingreso'] ?? date('Y-m-d'),
            'experiencias' => $_POST['experiencias'] ?? []
        ];
        
        $resultado = Instructor::crear($datos);
        
        if ($resultado['success']) {
            $_SESSION['mensaje'] = 'Instructor registrado exitosamente';
            $_SESSION['tipo_mensaje'] = 'success';
            header('Location: index.php?controlador=Instructor&accion=perfil&id=' . $resultado['id']);
        } else {
            $_SESSION['errores'] = $resultado['errores'];
            $_SESSION['datos_form'] = $datos;
            header('Location: index.php?controlador=Instructor&accion=registrar');
        }
        exit;
    }

    /**
     * Ver panel de carga académica
     */
    public function dashboard() {
        // Incluir sistema de contexto de rol
        require_once __DIR__ . '/../api/role_context.php';
        
        // Verificar autenticación
        if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'instructor') {
            header('Location: index.php?controlador=Auth&accion=login');
            exit;
        }
        
        // Inicializar contexto de instructor
        inicializarContextoRol('instructor');
        
        // Obtener información del usuario para este rol
        $infoUsuario = obtenerInfoUsuarioRol('instructor');
        
        require_once('Vista/Instructor/dashboard.php');
    }
    
    /**
     * Ver perfil del instructor (propio)
     */
    public function perfil() {
        // Verificar autenticación
        if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'instructor') {
            header('Location: index.php?controlador=Auth&accion=login');
            exit;
        }
        
        require_once('Vista/Instructor/perfil.php');
    }
    
    /**
     * Ver perfil de un instructor específico (por ID)
     */
    public function verPerfil() {
        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        if (!$id) {
            header('Location: index.php?controlador=Instructor&accion=index');
            exit;
        }
        
        $instructor = Instructor::obtenerPorId($id);
        if (!$instructor) {
            $_SESSION['mensaje'] = 'Instructor no encontrado';
            $_SESSION['tipo_mensaje'] = 'danger';
            header('Location: index.php?controlador=Instructor&accion=index');
            exit;
        }
        
        require_once('Vista/Layouts/header.php');
        require_once('Vista/Instructor/perfil.php');
        require_once('Vista/Layouts/footer.php');
    }
    
    /**
     * Actualizar perfil del instructor (propio)
     */
    public function actualizarPerfil() {
        // Verificar autenticación
        if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'instructor') {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }
        
        // Sanitizar datos
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $ubicacion = trim($_POST['ubicacion'] ?? '');
        $biografia = trim($_POST['biografia'] ?? '');
        
        // Validar datos requeridos
        if (empty($nombre) || empty($apellido)) {
            echo json_encode(['success' => false, 'message' => 'El nombre y apellido son requeridos']);
            exit;
        }
        
        try {
            // Obtener conexión PDO
            $db = Db::getConnect();
            
            if (!$db) {
                echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
                exit;
            }
            
            // Verificar si el instructor existe
            $email = $_SESSION['email'];
            $checkStmt = $db->prepare("SELECT id FROM instructores WHERE email = :email");
            $checkStmt->bindValue(':email', $email);
            $checkStmt->execute();
            $result = $checkStmt->fetch();
            
            if (!$result) {
                echo json_encode(['success' => false, 'message' => 'Instructor no encontrado en la base de datos']);
                exit;
            }
            
            // Actualizar datos
            $stmt = $db->prepare("UPDATE instructores SET nombre = :nombre, apellido = :apellido, telefono = :telefono WHERE email = :email");
            $stmt->bindValue(':nombre', $nombre);
            $stmt->bindValue(':apellido', $apellido);
            $stmt->bindValue(':telefono', $telefono);
            $stmt->bindValue(':email', $email);
            
            if ($stmt->execute()) {
                // Actualizar sesión
                $_SESSION['user_name'] = $nombre . ' ' . $apellido;
                $_SESSION['nombre'] = $nombre . ' ' . $apellido;
                
                echo json_encode(['success' => true, 'message' => 'Perfil actualizado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al ejecutar la actualización']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el perfil: ' . $e->getMessage()]);
        }
        
        exit;
    }
    
    /**
     * Editar perfil del instructor
     */
    public function editarPerfil() {
        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        if (!$id) {
            header('Location: index.php?controlador=Instructor&accion=index');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'apellido' => trim($_POST['apellido'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'telefono' => trim($_POST['telefono'] ?? ''),
                'especialidad' => trim($_POST['especialidad'] ?? ''),
                'experiencias' => $_POST['experiencias'] ?? []
            ];
            
            $resultado = Instructor::actualizar($id, $datos);
            
            if ($resultado['success']) {
                $_SESSION['mensaje'] = 'Perfil actualizado exitosamente';
                $_SESSION['tipo_mensaje'] = 'success';
            } else {
                $_SESSION['errores'] = $resultado['errores'];
            }
            
            header('Location: index.php?controlador=Instructor&accion=perfil&id=' . $id);
            exit;
        }
        
        $instructor = Instructor::obtenerPorId($id);
        $experiencias = Experiencia::obtenerTodas();
        
        require_once('Vista/Layouts/header.php');
        require_once('Vista/Instructor/editar_perfil.php');
        require_once('Vista/Layouts/footer.php');
    }
}

?>
