<?php
/**
 * API para gestionar asignaciones
 */

// Iniciar sesión
session_start();

// Configurar headers
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

// Obtener datos JSON o parámetros GET
$action = '';
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
} else {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $action = $data['action'] ?? '';
}

// Conexión a base de datos
try {
    $db = new PDO(
        'mysql:host=localhost;dbname=cphpmysql;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error de conexión: ' . $e->getMessage()]);
    exit;
}

// Procesar acción
try {
    switch ($action) {
        case 'listar':
            // Listar todas las asignaciones con información completa
            $stmt = $db->prepare("
                SELECT 
                    a.asignacion_id,
                    a.fecha_inicio,
                    a.fecha_fin,
                    a.hora_inicio,
                    a.hora_fin,
                    a.dias_semana,
                    a.estado,
                    a.observaciones,
                    f.codigo_ficha,
                    f.programa as ficha_programa,
                    CONCAT(i.nombre, ' ', i.apellido) as instructor_nombre,
                    i.especialidad as instructor_especialidad,
                    amb.nombre_ambiente,
                    amb.tipo as ambiente_tipo,
                    exp.nombre_experiencia,
                    exp.duracion_horas
                FROM asignaciones a
                INNER JOIN fichas f ON a.ficha_id = f.ficha_id
                INNER JOIN instructores i ON a.instructor_id = i.id
                LEFT JOIN ambientes amb ON a.ambiente_id = amb.ambiente_id
                INNER JOIN experiencias exp ON a.experiencia_id = exp.experiencia_id
                ORDER BY a.fecha_inicio DESC, a.hora_inicio ASC
            ");
            
            $stmt->execute();
            $asignaciones = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'asignaciones' => $asignaciones,
                'total' => count($asignaciones)
            ]);
            break;
            
        case 'crear':
            // Obtener datos del POST
            if (!isset($data)) {
                $json = file_get_contents('php://input');
                $data = json_decode($json, true);
            }
            
            if (!$data) {
                echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
                break;
            }
            
            // Insertar nueva asignación
            $stmt = $db->prepare("
                INSERT INTO asignaciones 
                (ficha_id, instructor_id, experiencia_id, ambiente_id, fecha_inicio, fecha_fin, hora_inicio, hora_fin, dias_semana, estado, observaciones) 
                VALUES (:ficha_id, :instructor_id, :experiencia_id, :ambiente_id, :fecha_inicio, :fecha_fin, :hora_inicio, :hora_fin, :dias_semana, :estado, :observaciones)
            ");
            
            $stmt->execute([
                ':ficha_id' => $data['ficha_id'],
                ':instructor_id' => $data['instructor_id'],
                ':experiencia_id' => $data['experiencia_id'],
                ':ambiente_id' => $data['ambiente_id'],
                ':fecha_inicio' => $data['fecha_inicio'],
                ':fecha_fin' => $data['fecha_fin'],
                ':hora_inicio' => $data['hora_inicio'] ?? null,
                ':hora_fin' => $data['hora_fin'] ?? null,
                ':dias_semana' => $data['dias_semana'] ?? null,
                ':estado' => $data['estado'] ?? 'Programada',
                ':observaciones' => $data['observaciones'] ?? null
            ]);
            
            $asignacion_id = $db->lastInsertId();
            
            echo json_encode([
                'success' => true,
                'message' => 'Asignación creada correctamente',
                'asignacion_id' => $asignacion_id
            ]);
            break;
            
        case 'actualizar':
            // Obtener datos del POST
            if (!isset($data)) {
                $json = file_get_contents('php://input');
                $data = json_decode($json, true);
            }
            
            if (!$data) {
                echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
                break;
            }
            
            // Actualizar asignación existente
            $stmt = $db->prepare("
                UPDATE asignaciones 
                SET ficha_id = :ficha_id,
                    instructor_id = :instructor_id,
                    experiencia_id = :experiencia_id,
                    ambiente_id = :ambiente_id,
                    fecha_inicio = :fecha_inicio,
                    fecha_fin = :fecha_fin,
                    hora_inicio = :hora_inicio,
                    hora_fin = :hora_fin,
                    dias_semana = :dias_semana,
                    estado = :estado,
                    observaciones = :observaciones
                WHERE asignacion_id = :asignacion_id
            ");
            
            $stmt->execute([
                ':asignacion_id' => $data['asignacion_id'],
                ':ficha_id' => $data['ficha_id'],
                ':instructor_id' => $data['instructor_id'],
                ':experiencia_id' => $data['experiencia_id'],
                ':ambiente_id' => $data['ambiente_id'],
                ':fecha_inicio' => $data['fecha_inicio'],
                ':fecha_fin' => $data['fecha_fin'],
                ':hora_inicio' => $data['hora_inicio'] ?? null,
                ':hora_fin' => $data['hora_fin'] ?? null,
                ':dias_semana' => $data['dias_semana'] ?? null,
                ':estado' => $data['estado'] ?? 'Programada',
                ':observaciones' => $data['observaciones'] ?? null
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Asignación actualizada correctamente'
            ]);
            break;
            
        case 'eliminar':
            // Obtener datos del POST
            if (!isset($data)) {
                $json = file_get_contents('php://input');
                $data = json_decode($json, true);
            }
            
            if (!$data) {
                echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
                break;
            }
            
            // Eliminar asignación
            $stmt = $db->prepare("DELETE FROM asignaciones WHERE asignacion_id = :asignacion_id");
            $stmt->execute([':asignacion_id' => $data['asignacion_id']]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Asignación eliminada correctamente'
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Acción no válida']);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error al procesar la solicitud: ' . $e->getMessage()
    ]);
}
