<?php
// Limpiar cualquier salida previa
ob_start();

// Configurar headers
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Endpoint simple para manejar eventos del calendario (GET list, POST create)

// Usar configuración de sesiones optimizada
require_once __DIR__ . '/session_config.php';
require_once __DIR__ . '/../connection.php';

try {
    $db = Db::getConnect();
    
    if (!$db) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    // Asegurar tabla con soporte para horas
    $db->exec("CREATE TABLE IF NOT EXISTS events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        start_date DATETIME NOT NULL,
        end_date DATETIME DEFAULT NULL,
        user_id INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
} catch (Exception $e) {
    http_response_code(500);
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
    ob_end_flush();
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
// determine session user id when available
$uid = $_SESSION['usuario_id'] ?? $_SESSION['user_id'] ?? null;
$role = strtolower($_SESSION['role'] ?? $_SESSION['rol'] ?? '');
$isAdmin = in_array($role, ['admin','administrador','administration','root','superadmin','super-admin','coordinador']);

if ($method === 'GET') {
    try {
        // Obtener eventos directos
        if ($uid && !$isAdmin) {
            $stmt = $db->prepare('SELECT id, title, start_date AS start, IFNULL(end_date, start_date) AS end FROM events WHERE user_id = :uid ORDER BY start_date ASC');
            $stmt->execute([':uid' => $uid]);
        } else {
            $stmt = $db->prepare('SELECT id, title, start_date AS start, IFNULL(end_date, start_date) AS end FROM events ORDER BY start_date ASC');
            $stmt->execute();
        }
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener asignaciones como eventos del calendario
        try {
            $asignQuery = "
                SELECT 
                    a.asignacion_id as id,
                    CONCAT(
                        'CLASE: ',
                        exp.nombre_experiencia,
                        ' - ',
                        CONCAT(i.nombre, ' ', i.apellido),
                        ' - ',
                        amb.nombre_ambiente
                    ) as title,
                    CONCAT(a.fecha_inicio, ' ', IFNULL(a.hora_inicio, '08:00:00')) as start,
                    CONCAT(a.fecha_fin, ' ', IFNULL(a.hora_fin, '12:00:00')) as end
                FROM asignaciones a
                INNER JOIN fichas f ON a.ficha_id = f.ficha_id
                INNER JOIN instructores i ON a.instructor_id = i.id
                LEFT JOIN ambientes amb ON a.ambiente_id = amb.ambiente_id
                INNER JOIN experiencias exp ON a.experiencia_id = exp.experiencia_id
                WHERE a.estado IN ('Programada', 'En Curso')
                ORDER BY a.fecha_inicio ASC
            ";
            $asignStmt = $db->prepare($asignQuery);
            $asignStmt->execute();
            $asignaciones = $asignStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Combinar eventos y asignaciones
            $events = array_merge($events, $asignaciones);
        } catch (Exception $e) {
            // Si hay error en asignaciones, solo retornar eventos directos
            error_log('Error al obtener asignaciones: ' . $e->getMessage());
        }
        
        // Limpiar buffer antes de enviar JSON
        ob_clean();
        echo json_encode(array_values($events));
        ob_end_flush();
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        ob_clean();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        ob_end_flush();
        exit;
    }
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['title']) || empty($input['start'])) {
        http_response_code(400);
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
        ob_end_flush();
        exit;
    }

    $title = trim($input['title']);
    $start = $input['start'];
    $end = !empty($input['end']) ? $input['end'] : $start;

    // validar rango de fechas
    if (strtotime($end) < strtotime($start)) {
        http_response_code(400);
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'La fecha de fin no puede ser anterior a la de inicio']);
        ob_end_flush();
        exit;
    }

    try {
        $stmt = $db->prepare('INSERT INTO events (title, start_date, end_date, user_id) VALUES (:title, :start, :end, :uid)');
        $stmt->execute([':title' => $title, ':start' => $start, ':end' => $end, ':uid' => $uid]);
        $id = $db->lastInsertId();
        $event = ['id' => (int)$id, 'title' => $title, 'start' => $start, 'end' => $end];
        ob_clean();
        echo json_encode(['success' => true, 'event' => $event]);
        ob_end_flush();
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        ob_clean();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        ob_end_flush();
        exit;
    }
} elseif ($method === 'PUT' || $method === 'PATCH') {
    // Update event
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['id']) || empty($input['title']) || empty($input['start'])) {
        http_response_code(400);
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Parámetros inválidos para update']);
        ob_end_flush();
        exit;
    }
    $id = (int)$input['id'];
    $title = trim($input['title']);
    $start = $input['start'];
    $end = !empty($input['end']) ? $input['end'] : $start;
    // validar rango
    if (strtotime($end) < strtotime($start)) {
        http_response_code(400);
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'La fecha de fin no puede ser anterior a la de inicio']);
        ob_end_flush();
        exit;
    }

    try {
        // si hay usuario en sesión, validar que sea dueño del evento antes de actualizar
        if ($uid && !$isAdmin) {
            $check = $db->prepare('SELECT user_id FROM events WHERE id = :id');
            $check->execute([':id' => $id]);
            $row = $check->fetch(PDO::FETCH_ASSOC);
            if (!$row || ($row['user_id'] !== null && (int)$row['user_id'] !== (int)$uid)) {
                http_response_code(403);
                ob_clean();
                echo json_encode(['success' => false, 'message' => 'No autorizado para modificar este evento']);
                ob_end_flush();
                exit;
            }
        }

        $stmt = $db->prepare('UPDATE events SET title = :title, start_date = :start, end_date = :end WHERE id = :id');
        $stmt->execute([':title' => $title, ':start' => $start, ':end' => $end, ':id' => $id]);
        ob_clean();
        echo json_encode(['success' => true, 'event' => ['id' => $id, 'title' => $title, 'start' => $start, 'end' => $end]]);
        ob_end_flush();
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        ob_clean();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        ob_end_flush();
        exit;
    }
} elseif ($method === 'DELETE') {
    // Delete event, accept id via query param or JSON body
    $id = null;
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
    } else {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!empty($input['id'])) $id = (int)$input['id'];
    }
    if (!$id) {
        http_response_code(400);
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'ID requerido para eliminar']);
        ob_end_flush();
        exit;
    }
    try {
        // si hay usuario en sesión, validar permiso
        if ($uid && !$isAdmin) {
            $check = $db->prepare('SELECT user_id FROM events WHERE id = :id');
            $check->execute([':id' => $id]);
            $row = $check->fetch(PDO::FETCH_ASSOC);
            if (!$row || ($row['user_id'] !== null && (int)$row['user_id'] !== (int)$uid)) {
                http_response_code(403);
                ob_clean();
                echo json_encode(['success' => false, 'message' => 'No autorizado para eliminar este evento']);
                ob_end_flush();
                exit;
            }
        }

        $stmt = $db->prepare('DELETE FROM events WHERE id = :id');
        $stmt->execute([':id' => $id]);
        ob_clean();
        echo json_encode(['success' => true, 'id' => $id]);
        ob_end_flush();
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        ob_clean();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        ob_end_flush();
        exit;
    }
} else {
    http_response_code(405);
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    ob_end_flush();
    exit;
}
