<?php
/**
 * TEST DIRECTO DEL API DE CALENDAR EVENTS
 * Prueba el API sin necesidad de navegador
 */

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Test Calendar API</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h1 { color: #10b981; border-bottom: 3px solid #10b981; padding-bottom: 10px; }
        h2 { color: #059669; margin-top: 30px; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .info { background: #e7f3ff; padding: 15px; border-left: 4px solid #2196F3; margin: 10px 0; }
        .warning { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 10px 0; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🧪 Test del API Calendar Events</h1>
";

// Iniciar sesión simulada
session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['rol'] = 'administrador';

echo "<div class='info'>✅ Sesión iniciada: Usuario ID = 1, Rol = administrador</div>";

// Test 1: Verificar conexión a BD
echo "<div class='test-section'>";
echo "<h2>Test 1: Conexión a Base de Datos</h2>";
try {
    require_once 'connection.php';
    $db = Db::getConnect();
    if ($db) {
        echo "<div class='success'>✅ Conexión exitosa a la base de datos</div>";
    } else {
        echo "<div class='error'>❌ No se pudo conectar a la base de datos</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}
echo "</div>";

// Test 2: Verificar tabla events
echo "<div class='test-section'>";
echo "<h2>Test 2: Verificar Tabla Events</h2>";
try {
    $stmt = $db->query("SHOW TABLES LIKE 'events'");
    $exists = $stmt->fetch();
    if ($exists) {
        echo "<div class='success'>✅ Tabla 'events' existe</div>";
        
        // Mostrar estructura
        $stmt = $db->query("DESCRIBE events");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<h3>Estructura de la tabla:</h3>";
        echo "<pre>";
        foreach ($columns as $col) {
            echo $col['Field'] . " - " . $col['Type'] . "\n";
        }
        echo "</pre>";
    } else {
        echo "<div class='warning'>⚠️ Tabla 'events' no existe, se creará automáticamente</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}
echo "</div>";

// Test 3: Verificar datos de asignaciones
echo "<div class='test-section'>";
echo "<h2>Test 3: Verificar Datos de Asignaciones</h2>";
try {
    $stmt = $db->query("SELECT COUNT(*) as total FROM asignaciones");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<div class='info'>Total de asignaciones: <strong>" . $result['total'] . "</strong></div>";
    
    if ($result['total'] > 0) {
        echo "<h3>Primeras 5 asignaciones:</h3>";
        $stmt = $db->query("
            SELECT 
                a.asignacion_id,
                f.codigo_ficha,
                CONCAT(i.nombre, ' ', i.apellido) as instructor,
                exp.nombre_experiencia,
                a.fecha_inicio,
                a.fecha_fin,
                a.estado
            FROM asignaciones a
            INNER JOIN fichas f ON a.ficha_id = f.ficha_id
            INNER JOIN instructores i ON a.instructor_id = i.id
            INNER JOIN experiencias exp ON a.experiencia_id = exp.experiencia_id
            LIMIT 5
        ");
        $asignaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>";
        print_r($asignaciones);
        echo "</pre>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}
echo "</div>";

// Test 4: Simular llamada GET al API
echo "<div class='test-section'>";
echo "<h2>Test 4: Simular GET Request al API</h2>";
try {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
    
    // Capturar la salida del API
    ob_start();
    include 'api/calendar_events.php';
    $output = ob_get_clean();
    
    echo "<h3>Respuesta del API:</h3>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    // Intentar decodificar JSON
    $json = json_decode($output, true);
    if ($json !== null) {
        echo "<div class='success'>✅ JSON válido</div>";
        echo "<div class='info'>Total de eventos: <strong>" . count($json) . "</strong></div>";
        
        if (count($json) > 0) {
            echo "<h3>Primeros 3 eventos:</h3>";
            echo "<pre>";
            print_r(array_slice($json, 0, 3));
            echo "</pre>";
        }
    } else {
        echo "<div class='error'>❌ JSON inválido - Error: " . json_last_error_msg() . "</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}
echo "</div>";

// Test 5: Verificar archivos necesarios
echo "<div class='test-section'>";
echo "<h2>Test 5: Verificar Archivos del Sistema</h2>";
$files = [
    'api/calendar_events.php' => 'API de eventos del calendario',
    'api/asignaciones.php' => 'API de asignaciones',
    'api/datos_asignacion.php' => 'API de datos para asignación',
    'api/session_config.php' => 'Configuración de sesiones',
    'connection.php' => 'Conexión a base de datos'
];

foreach ($files as $file => $desc) {
    if (file_exists($file)) {
        echo "<div class='success'>✅ $desc ($file)</div>";
    } else {
        echo "<div class='error'>❌ $desc ($file) - NO EXISTE</div>";
    }
}
echo "</div>";

echo "
    <div class='info' style='margin-top: 30px;'>
        <h3>📋 Resumen</h3>
        <p>Si todos los tests pasaron correctamente, el sistema está listo para guardar asignaciones en el calendario.</p>
        <p><strong>Siguiente paso:</strong> Prueba crear una asignación desde el dashboard del coordinador.</p>
    </div>
</div>
</body>
</html>";
?>
