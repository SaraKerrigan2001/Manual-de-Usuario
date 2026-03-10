<?php

// modelos utilizados por el coordinador
require_once('Modelo/Sede.php');
require_once('Modelo/Instructor.php');
require_once('Modelo/Ficha.php');
require_once('Modelo/Aprendiz.php');
require_once('Modelo/Ambiente.php');

class CoordinadorController {
    
    /**
     * Dashboard del administrador
     */
    public function dashboard() {
        // Incluir sistema de contexto de rol
        require_once __DIR__ . '/../api/role_context.php';
        
        // Verificar autenticación
        if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'coordinador') {
            header('Location: index.php?controlador=Auth&accion=login');
            exit;
        }
        
        // Inicializar contexto de coordinador
        inicializarContextoRol('coordinador');
        
        // Obtener información del usuario para este rol
        $infoUsuario = obtenerInfoUsuarioRol('coordinador');

        // cargar datos para los modales (sedes, fichas, instructores)
        $db = Db::getConnect();
        

        // SEDES
        $sedes = [];
        $stmt = $db->query('SELECT sede_id, nombre_sede FROM sedes ORDER BY nombre_sede');
        if ($stmt) {
            $sedes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // FICHAS
        $fichas = [];
        // prefijar la columna ficha_id para evitar ambigüedad entre tablas
        $stmt = $db->query('SELECT f.ficha_id, f.codigo_ficha, f.programa, COUNT(DISTINCT ap.aprendiz_id) as num_aprendices FROM fichas f LEFT JOIN aprendices ap ON f.ficha_id = ap.ficha_id GROUP BY f.ficha_id ORDER BY f.codigo_ficha');
        if ($stmt) {
            $fichas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // AMBIENTES
        $ambientes = [];
        // la tabla no tiene columna "descripcion", ajustar a las columnas reales
        $stmt = $db->query('SELECT ambiente_id, sede_id, nombre_ambiente, capacidad, tipo, equipamiento, estado FROM ambientes ORDER BY nombre_ambiente');
        if ($stmt) {
            $ambientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // INSTRUCTORES
        // include specialty and allow later computation of workload/estado
        $stmt = $db->query('SELECT id, nombre, apellido, especialidad FROM instructores ORDER BY nombre');
        if ($stmt) {
            $instructores = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // compute carga horaria/estado per instructor
            foreach ($instructores as &$ins) {
                $ins['carga'] = 0;
                $ins['estado'] = 'Sin asignaciones';
                $s2 = $db->prepare("SELECT SUM(TIMESTAMPDIFF(HOUR, hora_inicio, hora_fin)) as horas FROM asignaciones WHERE instructor_id = ?");
                if ($s2->execute([$ins['id']])) {
                    $row = $s2->fetch(PDO::FETCH_ASSOC);
                    if ($row && $row['horas']) {
                        $ins['carga'] = $row['horas'];
                        $ins['estado'] = 'Activo';
                    }
                }
            }
            unset($ins);
        }

        // obtener programas y competencias para posibles listas
        require_once('Modelo/Programa.php');
        require_once('Modelo/Competencia.php');
        $programasList = Programa::all();
        $competenciasList = Competencia::all();
        
        require_once('Vista/Coordinador/dashboard.php');
    }
    
    /**
     * Página principal del coordinador
     */
    public function index() {
        require_once('Vista/Coordinador/index.php');
    }
    
    /**
     * Mostrar formulario para crear nuevo programa/transversal
     */
    public function nuevoPrograma() {
        require_once('Vista/Layouts/header.php');
        require_once('Vista/Coordinador/nuev_progr.php');
        require_once('Vista/Layouts/footer.php');
    }

    
    /**
     * Mostrar formulario para nueva asignación
     */
    public function nuevaAsignacion() {
        // Obtener datos necesarios para el formulario
        $transversales = Coordinador::obtenerTransversales();
        $instructores = Coordinador::obtenerInstructores();
        $fichas = Coordinador::obtenerFichas();
        
        require_once('Vista/Layouts/header.php');
        require_once('Vista/Coordinador/nuev_asign.php');
        require_once('Vista/Layouts/footer.php');
    }
    
    /**
     * Guardar nuevo programa/transversal
     */
    public function guardarPrograma() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controlador=Coordinador&accion=nuevoPrograma');
            exit;
        }
        
        // Sanitizar datos
        $datos = [
            'nombre_transversal' => trim($_POST['nombre_transversal'] ?? ''),
            'duracion' => filter_var($_POST['duracion'] ?? 0, FILTER_VALIDATE_INT),
            'modalidad' => $_POST['modalidad'] ?? '',
            'programa_base' => $_POST['programa_base'] ?? '',
            'objetivo' => trim($_POST['objetivo'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'competencias' => $_POST['competencias'] ?? []
        ];
        
        $resultado = Coordinador::crearTransversal($datos);
        
        if ($resultado['success']) {
            $_SESSION['mensaje'] = 'Transversal creado exitosamente';
            $_SESSION['tipo_mensaje'] = 'success';
            header('Location: index.php?controlador=Experiencia&accion=verTransversales');
        } else {
            $_SESSION['errores'] = $resultado['errores'];
            $_SESSION['datos_form'] = $datos;
            header('Location: index.php?controlador=Coordinador&accion=nuevoPrograma');
        }
        exit;
    }
    
    /**
     * Guardar nueva asignación
     */
    public function guardarAsignacion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controlador=Coordinador&accion=nuevaAsignacion');
            exit;
        }
        
        // Sanitizar datos
        $datos = [
            'id_transversal' => filter_var($_POST['id_transversal'] ?? 0, FILTER_VALIDATE_INT),
            'id_instructor' => filter_var($_POST['id_instructor'] ?? 0, FILTER_VALIDATE_INT),
            'id_ficha' => filter_var($_POST['id_ficha'] ?? 0, FILTER_VALIDATE_INT),
            'inicio' => $_POST['inicio'] ?? '',
            'fin' => $_POST['fin'] ?? ''
        ];
        
        $resultado = Coordinador::asignar($datos);
        
        if ($resultado['success']) {
            $_SESSION['mensaje'] = 'Asignación creada exitosamente';
            $_SESSION['tipo_mensaje'] = 'success';
            header('Location: index.php?controlador=Coordinador&accion=seguimientoFicha');
        } else {
            $_SESSION['errores'] = $resultado['errores'];
            $_SESSION['datos_form'] = $datos;
            header('Location: index.php?controlador=Coordinador&accion=nuevaAsignacion');
        }
        exit;
    }
    
    /**
     * Ver seguimiento de fichas
     */
    public function seguimientoFicha() {
        require_once('Vista/Layouts/header.php');
        require_once('Vista/Coordinador/seguimiento.php');
        require_once('Vista/Layouts/footer.php');
    }
    
    /**
     * Ver listado de transversales creados por el coordinador
     */
    public function verTransversales() {
        $transversales = Coordinador::obtenerTransversales();
        
        require_once('Vista/Layouts/header.php');
        require_once('Vista/Coordinador/transversales.php');
        require_once('Vista/Layouts/footer.php');
    }


    public function addInstructor() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controlador=Coordinador&accion=dashboard');
            exit;
        }

        $nombres = trim($_POST['nombres'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $especialidad = trim($_POST['especialidad'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($nombres) || empty($apellidos) || empty($email)) {
            $_SESSION['mensaje'] = 'Datos incompletos para crear instructor';
            $_SESSION['tipo_mensaje'] = 'danger';
            header('Location: index.php?controlador=Coordinador&accion=dashboard');
            exit;
        }

        try {
            $db = Db::getConnect();
            $stmt = $db->prepare('INSERT INTO instructores (nombre, apellido, email, especialidad) VALUES (:nombre, :apellido, :email, :especialidad)');
            $stmt->bindValue(':nombre', $nombres);
            $stmt->bindValue(':apellido', $apellidos);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':especialidad', $especialidad);
            $stmt->execute();

            $_SESSION['mensaje'] = 'Instructor agregado correctamente';
            $_SESSION['tipo_mensaje'] = 'success';
        } catch (Exception $e) {
            $_SESSION['mensaje'] = 'Error al guardar instructor: ' . $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'danger';
        }

        header('Location: index.php?controlador=Coordinador&accion=dashboard');
        exit;
    }

    public function addFicha() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controlador=Coordinador&accion=dashboard');
            exit;
        }

        $codigo = trim($_POST['codigo'] ?? '');
        $programa = trim($_POST['programa'] ?? '');
        $inicio = $_POST['inicio'] ?? null;
        $fin = $_POST['fin'] ?? null;
        $sede_id = filter_var($_POST['sede_id'] ?? 0, FILTER_VALIDATE_INT);

        if (empty($codigo) || empty($programa) || empty($inicio) || empty($fin)) {
            $_SESSION['mensaje'] = 'Datos incompletos para crear ficha';
            $_SESSION['tipo_mensaje'] = 'danger';
            header('Location: index.php?controlador=Coordinador&accion=dashboard');
            exit;
        }

        try {
            $db = Db::getConnect();
            $stmt = $db->prepare('INSERT INTO fichas (codigo_ficha, programa, fecha_inicio, fecha_fin, estado, sede_id) VALUES (:codigo, :programa, :inicio, :fin, "Activa", :sede_id)');
            $stmt->bindValue(':codigo', $codigo);
            $stmt->bindValue(':programa', $programa);
            $stmt->bindValue(':inicio', $inicio);
            $stmt->bindValue(':fin', $fin);
            $stmt->bindValue(':sede_id', $sede_id ?: null, PDO::PARAM_INT);
            $stmt->execute();

            $_SESSION['mensaje'] = 'Ficha agregada correctamente';
            $_SESSION['tipo_mensaje'] = 'success';
        } catch (Exception $e) {
            $_SESSION['mensaje'] = 'Error al guardar ficha: ' . $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'danger';
        }

        header('Location: index.php?controlador=Coordinador&accion=dashboard');
        exit;
    }

    public function addSede() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controlador=Coordinador&accion=dashboard');
            exit;
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');

        if (empty($nombre) || empty($direccion)) {
            $_SESSION['mensaje'] = 'Datos incompletos para crear sede';
            $_SESSION['tipo_mensaje'] = 'danger';
            header('Location: index.php?controlador=Coordinador&accion=dashboard');
            exit;
        }

        try {
            $db = Db::getConnect();
            $stmt = $db->prepare('INSERT INTO sedes (nombre_sede, direccion, telefono) VALUES (:nombre, :direccion, :telefono)');
            $stmt->bindValue(':nombre', $nombre);
            $stmt->bindValue(':direccion', $direccion);
            $stmt->bindValue(':telefono', $telefono);
            $stmt->execute();

            $_SESSION['mensaje'] = 'Sede agregada correctamente';
            $_SESSION['tipo_mensaje'] = 'success';
        } catch (Exception $e) {
            $_SESSION['mensaje'] = 'Error al guardar sede: ' . $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'danger';
        }

        header('Location: index.php?controlador=Coordinador&accion=dashboard');
        exit;
    }

    public function addAmbiente() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controlador=Coordinador&accion=dashboard');
            exit;
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $sede_id = filter_var($_POST['sede'] ?? 0, FILTER_VALIDATE_INT);
        $capacidad = filter_var($_POST['capacidad'] ?? 0, FILTER_VALIDATE_INT);
        $tipo = trim($_POST['tipo'] ?? '');

        if (empty($nombre) || !$sede_id) {
            $_SESSION['mensaje'] = 'Datos incompletos para crear ambiente';
            $_SESSION['tipo_mensaje'] = 'danger';
            header('Location: index.php?controlador=Coordinador&accion=dashboard');
            exit;
        }

        try {
            $db = Db::getConnect();
            $stmt = $db->prepare('INSERT INTO ambientes (sede_id, nombre_ambiente, capacidad, tipo, estado) VALUES (:sede, :nombre, :capacidad, :tipo, "Disponible")');
            $stmt->bindValue(':sede', $sede_id, PDO::PARAM_INT);
            $stmt->bindValue(':nombre', $nombre);
            $stmt->bindValue(':capacidad', $capacidad, PDO::PARAM_INT);
            $stmt->bindValue(':tipo', $tipo);
            $stmt->execute();

            $_SESSION['mensaje'] = 'Ambiente agregado correctamente';
            $_SESSION['tipo_mensaje'] = 'success';
        } catch (Exception $e) {
            $_SESSION['mensaje'] = 'Error al guardar ambiente: ' . $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'danger';
        }

        header('Location: index.php?controlador=Coordinador&accion=dashboard');
        exit;
    }

    public function addCompetencia() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controlador=Coordinador&accion=dashboard');
            exit;
        }

        $datos = [
            'codigo' => trim($_POST['codigo'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'horas' => filter_var($_POST['horas'] ?? 0, FILTER_VALIDATE_INT),
            'tipo' => trim($_POST['tipo'] ?? '')
        ];

        if (empty($datos['codigo']) || empty($datos['descripcion'])) {
            $_SESSION['mensaje'] = 'Código y descripción son obligatorios para competencia';
            $_SESSION['tipo_mensaje'] = 'danger';
            header('Location: index.php?controlador=Coordinador&accion=dashboard');
            exit;
        }

        try {
            require_once('Modelo/Competencia.php');
            Competencia::crear($datos);
            $_SESSION['mensaje'] = 'Competencia agregada correctamente';
            $_SESSION['tipo_mensaje'] = 'success';
        } catch (Exception $e) {
            $_SESSION['mensaje'] = 'Error al guardar competencia: ' . $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'danger';
        }

        header('Location: index.php?controlador=Coordinador&accion=dashboard');
        exit;
    }

    public function addPrograma() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controlador=Coordinador&accion=dashboard');
            exit;
        }

        $datos = [
            'codigo' => trim($_POST['codigo'] ?? ''),
            'nombre' => trim($_POST['nombre'] ?? ''),
            'nivel' => trim($_POST['nivel'] ?? ''),
            'duracion' => filter_var($_POST['duracion'] ?? 0, FILTER_VALIDATE_INT)
        ];

        if (empty($datos['codigo']) || empty($datos['nombre'])) {
            $_SESSION['mensaje'] = 'Código y nombre son obligatorios para programa';
            $_SESSION['tipo_mensaje'] = 'danger';
            header('Location: index.php?controlador=Coordinador&accion=dashboard');
            exit;
        }

        try {
            require_once('Modelo/Programa.php');
            Programa::crear($datos);
            $_SESSION['mensaje'] = 'Programa agregado correctamente';
            $_SESSION['tipo_mensaje'] = 'success';
        } catch (Exception $e) {
            $_SESSION['mensaje'] = 'Error al guardar programa: ' . $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'danger';
        }

        header('Location: index.php?controlador=Coordinador&accion=dashboard');
        exit;
    }
    
    /**
     * Ver perfil del coordinador
     */
    public function perfil() {
        // Incluir sistema de contexto de rol
        require_once __DIR__ . '/../api/role_context.php';
        
        // Verificar autenticación
        if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'coordinador') {
            header('Location: index.php?controlador=Auth&accion=login');
            exit;
        }

        inicializarContextoRol('coordinador');
        $infoUsuario = obtenerInfoUsuarioRol('coordinador');
        
        require_once('Vista/Coordinador/perfil.php');
    }
    
    /**
     * Actualizar perfil del coordinador
     */
    public function actualizarPerfil() {
        // Verificar autenticación
        if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'coordinador') {
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
            
            // Verificar si el usuario existe
            $email = $_SESSION['email'];
            $checkStmt = $db->prepare("SELECT usuario_id FROM usuarios WHERE email = :email");
            $checkStmt->bindValue(':email', $email);
            $checkStmt->execute();
            $result = $checkStmt->fetch();
            
            if (!$result) {
                echo json_encode(['success' => false, 'message' => 'Usuario no encontrado en la base de datos']);
                exit;
            }
            
            // Actualizar datos
            $nombreCompleto = $nombre . ' ' . $apellido;
            $stmt = $db->prepare("UPDATE usuarios SET nombre = :nombre, telefono = :telefono WHERE email = :email");
            $stmt->bindValue(':nombre', $nombreCompleto);
            $stmt->bindValue(':telefono', $telefono);
            $stmt->bindValue(':email', $email);
            
            if ($stmt->execute()) {
                // Actualizar sesión
                $_SESSION['user_name'] = $nombreCompleto;
                $_SESSION['nombre'] = $nombreCompleto;
                
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
     * Editar transversal existente
     */
    public function editarTransversal() {
        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        if (!$id) {
            header('Location: index.php?controlador=Coordinador&accion=verTransversales');
            exit;
        }
        
        $transversal = Coordinador::obtenerTransversalPorId($id);
        if (!$transversal) {
            $_SESSION['mensaje'] = 'Transversal no encontrado';
            $_SESSION['tipo_mensaje'] = 'danger';
            header('Location: index.php?controlador=Coordinador&accion=verTransversales');
            exit;
        }
        
        require_once('Vista/Layouts/header.php');
        require_once('Vista/Coordinador/editar_transversal.php');
        require_once('Vista/Layouts/footer.php');
    }

    /**
     * Eliminar sede
     */
    public function eliminarSede() {
        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        if ($id) {
            require_once('Modelo/Administrador.php');
            if (Administrador::eliminarSede($id)) {
                $_SESSION['mensaje'] = 'Sede eliminada correctamente';
                $_SESSION['tipo_mensaje'] = 'success';
            } else {
                $_SESSION['mensaje'] = 'Error al eliminar sede';
                $_SESSION['tipo_mensaje'] = 'danger';
            }
        }
        header('Location: index.php?controlador=Coordinador&accion=dashboard');
        exit;
    }

    /**
     * Eliminar ambiente
     */
    public function eliminarAmbiente() {
        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        if ($id) {
            require_once('Modelo/Administrador.php');
            if (Administrador::eliminarAmbiente($id)) {
                $_SESSION['mensaje'] = 'Ambiente eliminado correctamente';
                $_SESSION['tipo_mensaje'] = 'success';
            } else {
                $_SESSION['mensaje'] = 'Error al eliminar ambiente';
                $_SESSION['tipo_mensaje'] = 'danger';
            }
        }
        header('Location: index.php?controlador=Coordinador&accion=dashboard');
        exit;
    }

    /**
     * Eliminar instructor
     */
    public function eliminarInstructor() {
        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        if ($id) {
            require_once('Modelo/Administrador.php');
            if (Administrador::eliminarInstructor($id)) {
                $_SESSION['mensaje'] = 'Instructor eliminado correctamente';
                $_SESSION['tipo_mensaje'] = 'success';
            } else {
                $_SESSION['mensaje'] = 'Error al eliminar instructor';
                $_SESSION['tipo_mensaje'] = 'danger';
            }
        }
        header('Location: index.php?controlador=Coordinador&accion=dashboard');
        exit;
    }

    /**
     * Eliminar ficha
     */
    public function eliminarFicha() {
        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        if ($id) {
            require_once('Modelo/Administrador.php');
            if (Administrador::eliminarFicha($id)) {
                $_SESSION['mensaje'] = 'Ficha eliminada correctamente';
                $_SESSION['tipo_mensaje'] = 'success';
            } else {
                $_SESSION['mensaje'] = 'Error al eliminar ficha';
                $_SESSION['tipo_mensaje'] = 'danger';
            }
        }
        header('Location: index.php?controlador=Coordinador&accion=dashboard');
        exit;
    }


    /**
     * Actualizar sede
     */
    public function actualizarSede() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
            $nombre = trim($_POST['nombre_sede'] ?? '');
            $direccion = trim($_POST['direccion'] ?? '');
            $encargado = trim($_POST['encargado'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');

            require_once('Modelo/Administrador.php');
            if (Administrador::actualizarSede($id, $nombre, $direccion, $encargado, $telefono)) {
                $_SESSION['mensaje'] = 'Sede actualizada correctamente';
                $_SESSION['tipo_mensaje'] = 'success';
            } else {
                $_SESSION['mensaje'] = 'Error al actualizar sede';
                $_SESSION['tipo_mensaje'] = 'danger';
            }
        }
        header('Location: index.php?controlador=Coordinador&accion=dashboard');
        exit;
    }

    public function actualizarInstructor() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
            $nombres = trim($_POST['nombres'] ?? '');
            $apellidos = trim($_POST['apellidos'] ?? '');
            $especialidad = trim($_POST['especialidad'] ?? '');
            $email = trim($_POST['email'] ?? '');

            require_once('Modelo/Administrador.php');
            if (Administrador::actualizarInstructor($id, $nombres, $apellidos, $especialidad, $email)) {
                $_SESSION['mensaje'] = 'Instructor actualizado correctamente';
                $_SESSION['tipo_mensaje'] = 'success';
            } else {
                $_SESSION['mensaje'] = 'Error al actualizar instructor';
                $_SESSION['tipo_mensaje'] = 'danger';
            }
        }
        header('Location: index.php?controlador=Coordinador&accion=dashboard');
        exit;
    }

    /**
     * Actualizar ficha
     */
    public function actualizarFicha() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
            $codigo = trim($_POST['codigo_ficha'] ?? '');
            $programa = trim($_POST['programa'] ?? '');
            $sede_id = filter_var($_POST['sede_id'], FILTER_VALIDATE_INT);
            $inicio = $_POST['fecha_inicio'] ?? null;
            $fin = $_POST['fecha_fin_lectiva'] ?? null;
            $aprendices = filter_var($_POST['num_aprendices'], FILTER_VALIDATE_INT);

            require_once('Modelo/Administrador.php');
            if (Administrador::actualizarFicha($id, $codigo, $programa, $sede_id, $inicio, $fin, $aprendices)) {
                $_SESSION['mensaje'] = 'Ficha actualizada correctamente';
                $_SESSION['tipo_mensaje'] = 'success';
            } else {
                $_SESSION['mensaje'] = 'Error al actualizar ficha';
                $_SESSION['tipo_mensaje'] = 'danger';
            }
        }
        header('Location: index.php?controlador=Coordinador&accion=dashboard');
        exit;
    }

    /**
     * Actualizar ambiente
     */
    public function actualizarAmbiente() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
            $nombre = trim($_POST['nombre_ambiente'] ?? '');
            $sede_id = filter_var($_POST['sede_id'], FILTER_VALIDATE_INT);
            $capacidad = filter_var($_POST['capacidad'], FILTER_VALIDATE_INT);
            $tipo = trim($_POST['tipo'] ?? '');

            require_once('Modelo/Administrador.php');
            if (Administrador::actualizarAmbiente($id, $nombre, $sede_id, $capacidad, $tipo)) {
                $_SESSION['mensaje'] = 'Ambiente actualizado correctamente';
                $_SESSION['tipo_mensaje'] = 'success';
            } else {
                $_SESSION['mensaje'] = 'Error al actualizar ambiente';
                $_SESSION['tipo_mensaje'] = 'danger';
            }
        }
        header('Location: index.php?controlador=Coordinador&accion=dashboard');
        exit;
    }

    /**
     * Eliminar programa
     */
    public function eliminarPrograma() {
        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        if ($id) {
            require_once('Modelo/Administrador.php');
            if (Administrador::eliminarPrograma($id)) {
                $_SESSION['mensaje'] = 'Programa eliminado correctamente';
                $_SESSION['tipo_mensaje'] = 'success';
            } else {
                $_SESSION['mensaje'] = 'Error al eliminar programa';
                $_SESSION['tipo_mensaje'] = 'danger';
            }
        }
        header('Location: index.php?controlador=Coordinador&accion=dashboard');
        exit;
    }

    /**
     * Actualizar programa
     */
    public function actualizarPrograma() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
            $codigo = trim($_POST['codigo'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $nivel = trim($_POST['nivel'] ?? '');
            $duracion = filter_var($_POST['duracion_meses'], FILTER_VALIDATE_INT);

            require_once('Modelo/Administrador.php');
            if (Administrador::actualizarPrograma($id, $codigo, $nombre, $nivel, $duracion)) {
                $_SESSION['mensaje'] = 'Programa actualizado correctamente';
                $_SESSION['tipo_mensaje'] = 'success';
            } else {
                $_SESSION['mensaje'] = 'Error al actualizar programa';
                $_SESSION['tipo_mensaje'] = 'danger';
            }
        }
        header('Location: index.php?controlador=Coordinador&accion=dashboard');
        exit;
    }

    /**
     * Eliminar competencia
     */
    public function eliminarCompetencia() {
        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        if ($id) {
            require_once('Modelo/Administrador.php');
            if (Administrador::eliminarCompetencia($id)) {
                $_SESSION['mensaje'] = 'Competencia eliminada correctamente';
                $_SESSION['tipo_mensaje'] = 'success';
            } else {
                $_SESSION['mensaje'] = 'Error al eliminar competencia';
                $_SESSION['tipo_mensaje'] = 'danger';
            }
        }
        header('Location: index.php?controlador=Coordinador&accion=dashboard');
        exit;
    }

    /**
     * Actualizar competencia
     */
    public function actualizarCompetencia() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
            $codigo = trim($_POST['codigo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $horas = filter_var($_POST['horas'], FILTER_VALIDATE_INT);
            $tipo = trim($_POST['tipo'] ?? '');

            require_once('Modelo/Administrador.php');
            if (Administrador::actualizarCompetencia($id, $codigo, $descripcion, $horas, $tipo)) {
                $_SESSION['mensaje'] = 'Competencia actualizada correctamente';
                $_SESSION['tipo_mensaje'] = 'success';
            } else {
                $_SESSION['mensaje'] = 'Error al actualizar competencia';
                $_SESSION['tipo_mensaje'] = 'danger';
            }
        }
        header('Location: index.php?controlador=Coordinador&accion=dashboard');
        exit;
    }
}
