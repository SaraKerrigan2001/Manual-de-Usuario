<?php
// API para gestionar notificaciones entre Coordinador e Instructor

// Iniciar buffer de salida para capturar cualquier salida no deseada
ob_start();

// Usar configuración de sesiones optimizada
require_once(__DIR__ . '/../session_config.php');

// Limpiar cualquier salida previa
ob_clean();

// Establecer header JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Conexión MySQLi con manejo de errores mejorado
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'cphpmysql';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception('Error de conexión');
    }
    
    $conn->set_charset('utf8mb4');
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos', 'success' => false]);
    exit;
}

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado', 'success' => false]);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Si no hay action en GET o POST, intentar obtenerlo del body JSON
if (empty($action)) {
    $json_data = json_decode(file_get_contents('php://input'), true);
    $action = $json_data['action'] ?? '';
}

$usuario_id = $_SESSION['usuario_id'];
$rol = $_SESSION['rol'] ?? '';

try {
    switch ($action) {
        case 'enviar':
            // Solo coordinadores pueden enviar notificaciones
            if ($rol !== 'administrador') {
                throw new Exception('No tienes permisos para enviar notificaciones');
            }
            
            $instructor_id = $_POST['instructor_id'] ?? null;
            $tipo = $_POST['tipo'] ?? 'general';
            $titulo = $_POST['titulo'] ?? '';
            $mensaje = $_POST['mensaje'] ?? '';
            $datos_extra = $_POST['datos_extra'] ?? null;
            
            if (!$instructor_id || !$titulo || !$mensaje) {
                throw new Exception('Faltan datos requeridos');
            }
            
            $stmt = $conn->prepare("
                INSERT INTO notificaciones_instructor 
                (instructor_id, coordinador_id, tipo, titulo, mensaje, datos_extra, fecha_creacion) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->bind_param("iissss", $instructor_id, $usuario_id, $tipo, $titulo, $mensaje, $datos_extra);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Notificación enviada correctamente',
                    'notificacion_id' => $stmt->insert_id
                ]);
            } else {
                throw new Exception('Error al enviar notificación');
            }
            break;
            
        case 'listar':
            // Instructores ven sus notificaciones
            if ($rol === 'instructor') {
                // Primero obtener el instructor_id desde la tabla instructores usando el email del usuario
                $stmt_instructor = $conn->prepare("
                    SELECT i.id 
                    FROM instructores i
                    INNER JOIN usuarios u ON i.email = u.email
                    WHERE u.usuario_id = ?
                ");
                $stmt_instructor->bind_param("i", $usuario_id);
                $stmt_instructor->execute();
                $result_instructor = $stmt_instructor->get_result();
                $instructor_row = $result_instructor->fetch_assoc();
                
                if (!$instructor_row) {
                    echo json_encode([
                        'success' => true,
                        'notificaciones' => [],
                        'message' => 'No se encontró perfil de instructor vinculado'
                    ]);
                    exit;
                }
                
                $instructor_id = $instructor_row['id'];
                
                $stmt = $conn->prepare("
                    SELECT n.*, u.email as coordinador_email, u.nombre as coordinador_nombre
                    FROM notificaciones_instructor n
                    LEFT JOIN usuarios u ON n.coordinador_id = u.usuario_id
                    WHERE n.instructor_id = ?
                    ORDER BY n.fecha_creacion DESC
                    LIMIT 50
                ");
                $stmt->bind_param("i", $instructor_id);
            } 
            // Coordinadores ven notificaciones enviadas
            else if ($rol === 'administrador') {
                $stmt = $conn->prepare("
                    SELECT n.*, i.nombre, i.apellido, i.email as instructor_email
                    FROM notificaciones_instructor n
                    LEFT JOIN instructores i ON n.instructor_id = i.id
                    WHERE n.coordinador_id = ?
                    ORDER BY n.fecha_creacion DESC
                    LIMIT 50
                ");
                $stmt->bind_param("i", $usuario_id);
            } else {
                throw new Exception('Rol no válido');
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $notificaciones = [];
            
            while ($row = $result->fetch_assoc()) {
                $notificaciones[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'notificaciones' => $notificaciones
            ]);
            break;
            
        case 'marcar_leida':
            $notificacion_id = $_POST['notificacion_id'] ?? null;
            
            if (!$notificacion_id) {
                throw new Exception('ID de notificación requerido');
            }
            
            // Solo instructores pueden marcar como leídas
            if ($rol === 'instructor') {
                // Obtener el instructor_id desde la tabla instructores
                $stmt_instructor = $conn->prepare("
                    SELECT i.id 
                    FROM instructores i
                    INNER JOIN usuarios u ON i.email = u.email
                    WHERE u.usuario_id = ?
                ");
                $stmt_instructor->bind_param("i", $usuario_id);
                $stmt_instructor->execute();
                $result_instructor = $stmt_instructor->get_result();
                $instructor_row = $result_instructor->fetch_assoc();
                
                if (!$instructor_row) {
                    throw new Exception('No se encontró perfil de instructor vinculado');
                }
                
                $instructor_id = $instructor_row['id'];
                
                $stmt = $conn->prepare("
                    UPDATE notificaciones_instructor 
                    SET leida = 1, fecha_lectura = NOW() 
                    WHERE id = ? AND instructor_id = ?
                ");
                $stmt->bind_param("ii", $notificacion_id, $instructor_id);
            } else {
                throw new Exception('Solo instructores pueden marcar notificaciones como leídas');
            }
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Notificación marcada como leída'
                ]);
            } else {
                throw new Exception('Error al actualizar notificación');
            }
            break;
            
        case 'marcar_todas_leidas':
            // Solo instructores
            if ($rol === 'instructor') {
                // Obtener el instructor_id desde la tabla instructores
                $stmt_instructor = $conn->prepare("
                    SELECT i.id 
                    FROM instructores i
                    INNER JOIN usuarios u ON i.email = u.email
                    WHERE u.usuario_id = ?
                ");
                $stmt_instructor->bind_param("i", $usuario_id);
                $stmt_instructor->execute();
                $result_instructor = $stmt_instructor->get_result();
                $instructor_row = $result_instructor->fetch_assoc();
                
                if (!$instructor_row) {
                    throw new Exception('No se encontró perfil de instructor vinculado');
                }
                
                $instructor_id = $instructor_row['id'];
                
                $stmt = $conn->prepare("
                    UPDATE notificaciones_instructor 
                    SET leida = 1, fecha_lectura = NOW() 
                    WHERE instructor_id = ? AND leida = 0
                ");
                $stmt->bind_param("i", $instructor_id);
                
                if ($stmt->execute()) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Todas las notificaciones marcadas como leídas',
                        'affected_rows' => $stmt->affected_rows
                    ]);
                } else {
                    throw new Exception('Error al actualizar notificaciones');
                }
            } else {
                throw new Exception('Solo instructores pueden marcar notificaciones');
            }
            break;
            
        case 'contar_no_leidas':
            // Solo instructores
            if ($rol === 'instructor') {
                // Obtener el instructor_id desde la tabla instructores
                $stmt_instructor = $conn->prepare("
                    SELECT i.id 
                    FROM instructores i
                    INNER JOIN usuarios u ON i.email = u.email
                    WHERE u.usuario_id = ?
                ");
                $stmt_instructor->bind_param("i", $usuario_id);
                $stmt_instructor->execute();
                $result_instructor = $stmt_instructor->get_result();
                $instructor_row = $result_instructor->fetch_assoc();
                
                if (!$instructor_row) {
                    echo json_encode(['success' => true, 'total' => 0]);
                    exit;
                }
                
                $instructor_id = $instructor_row['id'];
                
                $stmt = $conn->prepare("
                    SELECT COUNT(*) as total 
                    FROM notificaciones_instructor 
                    WHERE instructor_id = ? AND leida = 0
                ");
                $stmt->bind_param("i", $instructor_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                
                echo json_encode([
                    'success' => true,
                    'total' => (int)$row['total']
                ]);
            } else {
                echo json_encode(['success' => true, 'total' => 0]);
            }
            break;
            
        case 'listar_instructores':
            // Coordinadores y administradores
            if ($rol !== 'administrador' && $rol !== 'coordinador') {
                throw new Exception('No tienes permisos');
            }
            
            $stmt = $conn->prepare("
                SELECT id, nombre, apellido, email, especialidad, registro 
                FROM instructores 
                ORDER BY nombre, apellido
            ");
            $stmt->execute();
            $result = $stmt->get_result();
            $instructores = [];
            
            while ($row = $result->fetch_assoc()) {
                $instructores[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'instructores' => $instructores
            ]);
            break;
        
        case 'listar_fichas':
            // Coordinadores y administradores
            if ($rol !== 'administrador' && $rol !== 'coordinador') {
                throw new Exception('No tienes permisos');
            }
            
            $stmt = $conn->prepare("
                SELECT ficha_id, codigo_ficha, programa, fecha_inicio, fecha_fin, estado 
                FROM fichas 
                WHERE estado = 'Activa'
                ORDER BY codigo_ficha
            ");
            $stmt->execute();
            $result = $stmt->get_result();
            $fichas = [];
            
            while ($row = $result->fetch_assoc()) {
                $fichas[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'fichas' => $fichas
            ]);
            break;
        
        case 'listar_ambientes':
            // Coordinadores y administradores
            if ($rol !== 'administrador' && $rol !== 'coordinador') {
                throw new Exception('No tienes permisos');
            }
            
            $stmt = $conn->prepare("
                SELECT ambiente_id, nombre_ambiente, capacidad, tipo, estado 
                FROM ambientes 
                WHERE estado = 'Disponible'
                ORDER BY nombre_ambiente
            ");
            $stmt->execute();
            $result = $stmt->get_result();
            $ambientes = [];
            
            while ($row = $result->fetch_assoc()) {
                $ambientes[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'ambientes' => $ambientes
            ]);
            break;
        
        case 'listar_experiencias':
            // Coordinadores y administradores
            if ($rol !== 'administrador' && $rol !== 'coordinador') {
                throw new Exception('No tienes permisos');
            }
            
            $stmt = $conn->prepare("
                SELECT experiencia_id, nombre_experiencia, descripcion, duracion_horas, nivel 
                FROM experiencias 
                ORDER BY nombre_experiencia
            ");
            $stmt->execute();
            $result = $stmt->get_result();
            $experiencias = [];
            
            while ($row = $result->fetch_assoc()) {
                $experiencias[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'experiencias' => $experiencias
            ]);
            break;
        
        case 'enviar_notificacion_coordinador':
            // Instructores envían notificaciones al coordinador
            $data = json_decode(file_get_contents('php://input'), true);
            
            $usuario_id = $data['instructor_id'] ?? $usuario_id;
            $instructor_nombre = $data['instructor_nombre'] ?? '';
            $tipo = $data['tipo'] ?? 'cambio_perfil';
            $titulo = $data['titulo'] ?? 'Solicitud de Actualización de Perfil';
            $mensaje = $data['mensaje'] ?? '';
            
            if (!$titulo || !$mensaje) {
                throw new Exception('Faltan datos requeridos');
            }
            
            // Buscar el instructor_id en la tabla instructores basándose en el email del usuario
            $stmt_instructor = $conn->prepare("
                SELECT i.id 
                FROM instructores i
                INNER JOIN usuarios u ON i.email = u.email
                WHERE u.usuario_id = ?
            ");
            $stmt_instructor->bind_param("i", $usuario_id);
            $stmt_instructor->execute();
            $result_instructor = $stmt_instructor->get_result();
            $instructor_row = $result_instructor->fetch_assoc();
            
            if (!$instructor_row) {
                throw new Exception('No se encontró el instructor vinculado a este usuario. Por favor, complete su perfil de instructor primero.');
            }
            
            $instructor_id = $instructor_row['id'];
            
            // Buscar el coordinador_id (primer administrador)
            $stmt_coord = $conn->prepare("SELECT usuario_id FROM usuarios WHERE rol = 'administrador' LIMIT 1");
            $stmt_coord->execute();
            $result_coord = $stmt_coord->get_result();
            $coord_row = $result_coord->fetch_assoc();
            
            if (!$coord_row) {
                throw new Exception('No se encontró un coordinador en el sistema');
            }
            
            $coordinador_id = $coord_row['usuario_id'];
            
            // Insertar notificación
            $stmt = $conn->prepare("
                INSERT INTO notificaciones_coordinador 
                (instructor_id, coordinador_id, tipo, titulo, mensaje, fecha_creacion) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->bind_param("iisss", $instructor_id, $coordinador_id, $tipo, $titulo, $mensaje);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Notificación enviada al coordinador',
                    'notificacion_id' => $stmt->insert_id,
                    'debug' => [
                        'instructor_id' => $instructor_id,
                        'coordinador_id' => $coordinador_id,
                        'instructor_nombre' => $instructor_nombre,
                        'tipo' => $tipo
                    ]
                ]);
            } else {
                throw new Exception('Error al enviar notificación al coordinador: ' . $stmt->error);
            }
            break;
        
        case 'listar_notificaciones_coordinador':
            // Solo coordinadores pueden ver estas notificaciones
            if ($rol !== 'administrador') {
                throw new Exception('No tienes permisos');
            }
            
            $stmt = $conn->prepare("
                SELECT * FROM notificaciones_coordinador 
                ORDER BY leida ASC, fecha_creacion DESC 
                LIMIT 50
            ");
            $stmt->execute();
            $result = $stmt->get_result();
            
            $notificaciones = [];
            while ($row = $result->fetch_assoc()) {
                $notificaciones[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'notificaciones' => $notificaciones
            ]);
            break;
        
        case 'marcar_leida_coordinador':
            // Solo coordinadores
            if ($rol !== 'administrador') {
                throw new Exception('No tienes permisos');
            }
            
            $notificacion_id = $_POST['notificacion_id'] ?? null;
            
            if (!$notificacion_id) {
                throw new Exception('ID de notificación requerido');
            }
            
            $stmt = $conn->prepare("UPDATE notificaciones_coordinador SET leida = 1 WHERE id = ?");
            $stmt->bind_param("i", $notificacion_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Notificación marcada como leída']);
            } else {
                throw new Exception('Error al marcar notificación');
            }
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();

// Limpiar buffer y enviar salida
ob_end_flush();
