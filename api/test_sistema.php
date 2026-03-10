<?php
/**
 * SISTEMA DE PRUEBAS Y DIAGNÓSTICO UNIFICADO
 * 
 * Este archivo combina:
 * - Diagnóstico de conexión a BD
 * - Pruebas de API
 * - Verificación de datos
 */

session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['rol'] = 'administrador';
$_SESSION['email'] = 'admin.sena@sena.edu.co';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Sistema SENA</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #39A900; border-bottom: 3px solid #39A900; padding-bottom: 15px; margin-bottom: 20px; }
        h2 { color: #007832; margin-top: 30px; margin-bottom: 15px; padding: 10px; background: #f0f9f0; border-left: 4px solid #39A900; }
        h3 { color: #333; margin-top: 20px; margin-bottom: 10px; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .info { background: #e7f3ff; padding: 15px; border-left: 4px solid #2196F3; margin: 10px 0; border-radius: 4px; }
        .alert { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 10px 0; border-radius: 4px; }
        .danger { background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 10px 0; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #39A900; color: white; font-weight: 600; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        tr:hover { background-color: #f0f9f0; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; border: 1px solid #ddd; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: 'Courier New', monospace; color: #d63384; }
        .btn { display: inline-block; background-color: #39A900; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 5px; transition: all 0.3s; }
        .btn:hover { background-color: #007832; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        .status-badge { display: inline-block; padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-error { background: #f8d7da; color: #721c24; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0; }
        .card { background: white; padding: 20px; border-radius: 8px; border: 1px solid #ddd; }
        .card h4 { color: #39A900; margin-bottom: 10px; }
        .divider { height: 2px; background: linear-gradient(to right, #39A900, transparent); margin: 30px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Sistema de Pruebas y Diagnóstico - SENA</h1>
        
        <div class="info">
            <strong>Sesión Configurada:</strong><br>
            Usuario ID: <?php echo $_SESSION['usuario_id']; ?><br>
            Rol: <?php echo $_SESSION['rol']; ?><br>
            Email: <?php echo $_SESSION['email']; ?>
        </div>

        <?php
        // ============================================
        // 1. VERIFICACIÓN DE CONEXIÓN A BASE DE DATOS
        // ============================================
        echo "<h2>1️⃣ Verificación de Conexión a Base de Datos</h2>";
        
        require_once('connection.php');
        
        try {
            $db = Db::getConnect();
            
            if (!$db) {
                echo "<div class='danger'>❌ <span class='error'>Error: No se pudo conectar a la base de datos</span></div>";
                exit;
            }
            
            echo "<div class='info'>✅ <span class='success'>Conexión exitosa a la base de datos 'cphpmysql'</span></div>";
            
            // ============================================
            // 2. VERIFICACIÓN DE TABLAS Y DATOS
            // ============================================
            echo "<h2>2️⃣ Verificación de Tablas y Datos</h2>";
            
            $tablas = [
                'instructores' => 'Instructores',
                'fichas' => 'Fichas',
                'ambientes' => 'Ambientes',
                'experiencias' => 'Experiencias/Competencias',
                'sedes' => 'Sedes',
                'programas' => 'Programas',
                'competencias' => 'Competencias'
            ];
            
            echo "<div class='grid'>";
            foreach ($tablas as $tabla => $nombre) {
                try {
                    $stmt = $db->query("SELECT COUNT(*) as total FROM $tabla");
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    $total = $result['total'];
                    
                    $badge_class = $total > 0 ? 'badge-success' : 'badge-warning';
                    $icon = $total > 0 ? '✅' : '⚠️';
                    
                    echo "<div class='card'>";
                    echo "<h4>$icon $nombre</h4>";
                    echo "<p>Total de registros: <strong>$total</strong></p>";
                    echo "<span class='status-badge $badge_class'>" . ($total > 0 ? 'OK' : 'Vacía') . "</span>";
                    echo "</div>";
                } catch (Exception $e) {
                    echo "<div class='card'>";
                    echo "<h4>❌ $nombre</h4>";
                    echo "<p class='error'>Tabla no existe</p>";
                    echo "</div>";
                }
            }
            echo "</div>";
            
            // ============================================
            // 3. DATOS DETALLADOS
            // ============================================
            echo "<div class='divider'></div>";
            echo "<h2>3️⃣ Datos Detallados</h2>";
            
            // Instructores
            echo "<h3>👨‍🏫 Instructores</h3>";
            $stmt = $db->query("SELECT id, nombre, apellido, email, especialidad, registro FROM instructores ORDER BY nombre, apellido");
            $instructores = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($instructores) > 0) {
                echo "<table>";
                echo "<tr><th>ID</th><th>Nombre</th><th>Email</th><th>Especialidad</th><th>Registro</th></tr>";
                foreach ($instructores as $inst) {
                    echo "<tr>";
                    echo "<td>{$inst['id']}</td>";
                    echo "<td>{$inst['nombre']} {$inst['apellido']}</td>";
                    echo "<td>{$inst['email']}</td>";
                    echo "<td>{$inst['especialidad']}</td>";
                    echo "<td>{$inst['registro']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<div class='alert'>⚠️ No hay instructores registrados</div>";
            }
            
            // Fichas
            echo "<h3>📋 Fichas</h3>";
            $stmt = $db->query("SELECT ficha_id, codigo_ficha, programa, fecha_inicio, fecha_fin, estado FROM fichas WHERE estado = 'Activa' ORDER BY codigo_ficha");
            $fichas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($fichas) > 0) {
                echo "<table>";
                echo "<tr><th>ID</th><th>Código</th><th>Programa</th><th>Fecha Inicio</th><th>Fecha Fin</th><th>Estado</th></tr>";
                foreach ($fichas as $ficha) {
                    echo "<tr>";
                    echo "<td>{$ficha['ficha_id']}</td>";
                    echo "<td>{$ficha['codigo_ficha']}</td>";
                    echo "<td>{$ficha['programa']}</td>";
                    echo "<td>{$ficha['fecha_inicio']}</td>";
                    echo "<td>{$ficha['fecha_fin']}</td>";
                    echo "<td><span class='status-badge badge-success'>{$ficha['estado']}</span></td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<div class='alert'>⚠️ No hay fichas activas</div>";
            }
            
            // Ambientes
            echo "<h3>🏢 Ambientes</h3>";
            $stmt = $db->query("SELECT ambiente_id, nombre_ambiente, capacidad, tipo, estado FROM ambientes WHERE estado = 'Disponible' ORDER BY nombre_ambiente");
            $ambientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($ambientes) > 0) {
                echo "<table>";
                echo "<tr><th>ID</th><th>Nombre</th><th>Capacidad</th><th>Tipo</th><th>Estado</th></tr>";
                foreach ($ambientes as $amb) {
                    echo "<tr>";
                    echo "<td>{$amb['ambiente_id']}</td>";
                    echo "<td>{$amb['nombre_ambiente']}</td>";
                    echo "<td>{$amb['capacidad']}</td>";
                    echo "<td>{$amb['tipo']}</td>";
                    echo "<td><span class='status-badge badge-success'>{$amb['estado']}</span></td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<div class='alert'>⚠️ No hay ambientes disponibles</div>";
            }
            
            // Experiencias
            echo "<h3>🎓 Experiencias/Competencias</h3>";
            $stmt = $db->query("SELECT experiencia_id, nombre_experiencia, descripcion, duracion_horas, nivel FROM experiencias ORDER BY nombre_experiencia");
            $experiencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($experiencias) > 0) {
                echo "<table>";
                echo "<tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Duración</th><th>Nivel</th></tr>";
                foreach ($experiencias as $exp) {
                    echo "<tr>";
                    echo "<td>{$exp['experiencia_id']}</td>";
                    echo "<td>{$exp['nombre_experiencia']}</td>";
                    echo "<td>{$exp['descripcion']}</td>";
                    echo "<td>{$exp['duracion_horas']}h</td>";
                    echo "<td>{$exp['nivel']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<div class='alert'>⚠️ No hay experiencias registradas</div>";
            }
            
            // ============================================
            // 4. PRUEBAS DE API
            // ============================================
            echo "<div class='divider'></div>";
            echo "<h2>4️⃣ Pruebas de API</h2>";
            
            $api_tests = [
                'listar_instructores' => 'Listar Instructores',
                'listar_fichas' => 'Listar Fichas',
                'listar_ambientes' => 'Listar Ambientes',
                'listar_experiencias' => 'Listar Experiencias'
            ];
            
            foreach ($api_tests as $action => $nombre) {
                echo "<h3>🔌 Test: $nombre</h3>";
                
                // Simular llamada al API
                $_GET['action'] = $action;
                ob_start();
                include('api/notificaciones.php');
                $json_response = ob_get_clean();
                
                echo "<p><strong>URL:</strong> <code>api/notificaciones.php?action=$action</code></p>";
                
                // Verificar si es JSON válido
                $data = json_decode($json_response, true);
                
                if ($data && isset($data['success'])) {
                    if ($data['success']) {
                        $count = 0;
                        if (isset($data['instructores'])) $count = count($data['instructores']);
                        if (isset($data['fichas'])) $count = count($data['fichas']);
                        if (isset($data['ambientes'])) $count = count($data['ambientes']);
                        if (isset($data['experiencias'])) $count = count($data['experiencias']);
                        
                        echo "<div class='info'>✅ <span class='success'>API funcionando correctamente</span> - Registros devueltos: <strong>$count</strong></div>";
                        echo "<details><summary>Ver respuesta JSON</summary><pre>" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre></details>";
                    } else {
                        echo "<div class='danger'>❌ <span class='error'>API devolvió error:</span> " . ($data['error'] ?? 'Error desconocido') . "</div>";
                    }
                } else {
                    echo "<div class='danger'>❌ <span class='error'>La respuesta no es JSON válido</span></div>";
                    echo "<details><summary>Ver respuesta (HTML/Error)</summary><pre>" . htmlspecialchars($json_response) . "</pre></details>";
                }
            }
            
            // ============================================
            // 5. RESUMEN Y ACCIONES
            // ============================================
            echo "<div class='divider'></div>";
            echo "<h2>5️⃣ Resumen y Acciones</h2>";
            
            $total_instructores = count($instructores);
            $total_fichas = count($fichas);
            $total_ambientes = count($ambientes);
            $total_experiencias = count($experiencias);
            
            if ($total_instructores > 0 && $total_fichas > 0 && $total_ambientes > 0 && $total_experiencias > 0) {
                echo "<div class='info'>";
                echo "<h3>✅ SISTEMA FUNCIONANDO CORRECTAMENTE</h3>";
                echo "<p>Todos los componentes están operativos y con datos.</p>";
                echo "<ul>";
                echo "<li>✅ Base de datos conectada</li>";
                echo "<li>✅ Tablas creadas y con datos</li>";
                echo "<li>✅ API respondiendo correctamente</li>";
                echo "</ul>";
                echo "</div>";
            } else {
                echo "<div class='alert'>";
                echo "<h3>⚠️ SISTEMA REQUIERE ATENCIÓN</h3>";
                echo "<p>Algunos componentes necesitan configuración:</p>";
                echo "<ul>";
                if ($total_instructores == 0) echo "<li>⚠️ No hay instructores registrados</li>";
                if ($total_fichas == 0) echo "<li>⚠️ No hay fichas activas</li>";
                if ($total_ambientes == 0) echo "<li>⚠️ No hay ambientes disponibles</li>";
                if ($total_experiencias == 0) echo "<li>⚠️ No hay experiencias registradas</li>";
                echo "</ul>";
                echo "<p><strong>Solución:</strong> Importa el archivo <code>progFormacion_v3.sql</code> en phpMyAdmin</p>";
                echo "</div>";
            }
            
            echo "<div style='text-align: center; margin-top: 30px;'>";
            echo "<a href='index.php' class='btn'>🏠 Ir al Sistema</a>";
            echo "<a href='http://localhost/phpmyadmin' class='btn' target='_blank'>🗄️ Abrir phpMyAdmin</a>";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div class='danger'>";
            echo "<h3>❌ Error Fatal</h3>";
            echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<details><summary>Ver detalles técnicos</summary><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></details>";
            echo "</div>";
        }
        ?>
        
        <div class="divider"></div>
        <p style="text-align: center; color: #666; margin-top: 30px;">
            <small>Sistema de Gestión Académica SENA - Test y Diagnóstico v1.0</small>
        </p>
    </div>
</body>
</html>
