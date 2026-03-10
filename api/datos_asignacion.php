<?php
/**
 * API simplificada para obtener datos de asignaciones
 * Sin dependencias complejas
 */

// Iniciar sesión
session_start();

// Configurar headers
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Verificar autenticación básica
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

// Obtener acción
$action = $_GET['action'] ?? '';

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
        case 'listar_instructores':
            $stmt = $db->query("
                SELECT id, nombre, apellido, email, especialidad, registro 
                FROM instructores 
                ORDER BY nombre, apellido
            ");
            $instructores = $stmt->fetchAll();
            echo json_encode(['success' => true, 'instructores' => $instructores]);
            break;
            
        case 'listar_fichas':
            $stmt = $db->query("
                SELECT ficha_id, codigo_ficha, programa, fecha_inicio, fecha_fin, estado 
                FROM fichas 
                WHERE estado = 'Activa'
                ORDER BY codigo_ficha
            ");
            $fichas = $stmt->fetchAll();
            echo json_encode(['success' => true, 'fichas' => $fichas]);
            break;
            
        case 'listar_ambientes':
            $stmt = $db->query("
                SELECT ambiente_id, nombre_ambiente, capacidad, tipo, estado 
                FROM ambientes 
                WHERE estado = 'Disponible'
                ORDER BY nombre_ambiente
            ");
            $ambientes = $stmt->fetchAll();
            echo json_encode(['success' => true, 'ambientes' => $ambientes]);
            break;
            
        case 'listar_experiencias':
            $stmt = $db->query("
                SELECT experiencia_id, nombre_experiencia, descripcion, duracion_horas, nivel 
                FROM experiencias 
                ORDER BY nombre_experiencia
            ");
            $experiencias = $stmt->fetchAll();
            echo json_encode(['success' => true, 'experiencias' => $experiencias]);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Acción no válida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
