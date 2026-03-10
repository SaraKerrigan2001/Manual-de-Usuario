<?php
/**
 * SCRIPT PARA ELIMINAR USUARIOS ANTERIORES Y AGREGAR SOLO EL NUEVO ADMINISTRADOR
 * Email: spiligr1@gmail.com
 * Contraseña: 12345678
 */

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Limpiar y Agregar Administrador</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h1 { color: #10b981; border-bottom: 3px solid #10b981; padding-bottom: 10px; }
        .success { color: #28a745; font-weight: bold; background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { color: #dc3545; font-weight: bold; background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #e7f3ff; padding: 15px; border-left: 4px solid #2196F3; margin: 10px 0; }
        .warning { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #10b981; color: white; }
        .btn { display: inline-block; background-color: #10b981; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background-color: #059669; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🗑️ Limpiar Usuarios y Agregar Nuevo Administrador</h1>
";

try {
    require_once 'connection.php';
    $db = Db::getConnect();
    
    if (!$db) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    echo "<div class='success'>✅ Conexión exitosa a la base de datos</div>";
    
    // Mostrar usuarios actuales
    echo "<div class='warning'>";
    echo "<h3>📋 Usuarios Actuales en la Base de Datos:</h3>";
    $stmt = $db->query("SELECT usuario_id, nombre, email, rol FROM usuarios ORDER BY usuario_id");
    $usuarios_actuales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($usuarios_actuales) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th></tr>";
        foreach ($usuarios_actuales as $user) {
            echo "<tr>";
            echo "<td>" . $user['usuario_id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['nombre']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . $user['rol'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p><strong>Total de usuarios: " . count($usuarios_actuales) . "</strong></p>";
    } else {
        echo "<p>No hay usuarios en la base de datos</p>";
    }
    echo "</div>";
    
    // Iniciar transacción
    $db->beginTransaction();
    
    try {
        // Desactivar verificación de claves foráneas temporalmente
        $db->exec("SET FOREIGN_KEY_CHECKS = 0");
        
        // Eliminar todos los registros de tablas relacionadas
        echo "<div class='info'><h3>🗑️ Eliminando datos anteriores...</h3></div>";
        
        $db->exec("DELETE FROM administradores");
        echo "<div class='success'>✅ Tabla 'administradores' limpiada</div>";
        
        $db->exec("DELETE FROM instructores");
        echo "<div class='success'>✅ Tabla 'instructores' limpiada</div>";
        
        $db->exec("DELETE FROM usuarios");
        echo "<div class='success'>✅ Tabla 'usuarios' limpiada</div>";
        
        // Reiniciar auto_increment
        $db->exec("ALTER TABLE usuarios AUTO_INCREMENT = 1");
        $db->exec("ALTER TABLE administradores AUTO_INCREMENT = 1");
        $db->exec("ALTER TABLE instructores AUTO_INCREMENT = 1");
        
        // Reactivar verificación de claves foráneas
        $db->exec("SET FOREIGN_KEY_CHECKS = 1");
        
        echo "<div class='success'>✅ Todas las tablas de usuarios limpiadas correctamente</div>";
        
        // Datos del nuevo usuario
        $nombre = 'Administrador Principal';
        $email = 'spiligr1@gmail.com';
        $password = '12345678';
        $telefono = '+57 300 999 8888';
        $rol = 'administrador';
        $documento = '1234567890';
        $cargo = 'Administrador Principal';
        
        // Generar hash de la contraseña
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        echo "<div class='info'>";
        echo "<h3>➕ Creando Nuevo Usuario Administrador:</h3>";
        echo "<table>";
        echo "<tr><th>Campo</th><th>Valor</th></tr>";
        echo "<tr><td>Nombre</td><td>$nombre</td></tr>";
        echo "<tr><td>Email</td><td>$email</td></tr>";
        echo "<tr><td>Contraseña</td><td>$password</td></tr>";
        echo "<tr><td>Teléfono</td><td>$telefono</td></tr>";
        echo "<tr><td>Rol</td><td>$rol</td></tr>";
        echo "<tr><td>Documento</td><td>$documento</td></tr>";
        echo "<tr><td>Cargo</td><td>$cargo</td></tr>";
        echo "</table>";
        echo "</div>";
        
        // Insertar en tabla usuarios
        $stmt = $db->prepare("
            INSERT INTO usuarios (nombre, email, password, telefono, rol, activo) 
            VALUES (:nombre, :email, :password, :telefono, :rol, 1)
        ");
        
        $stmt->execute([
            ':nombre' => $nombre,
            ':email' => $email,
            ':password' => $password_hash,
            ':telefono' => $telefono,
            ':rol' => $rol
        ]);
        
        $usuario_id = $db->lastInsertId();
        
        echo "<div class='success'>✅ Usuario creado en tabla 'usuarios' con ID: $usuario_id</div>";
        
        // Insertar en tabla administradores
        $stmt = $db->prepare("
            INSERT INTO administradores (usuario_id, nombre, apellido, documento, email, telefono, cargo) 
            VALUES (:usuario_id, :nombre, :apellido, :documento, :email, :telefono, :cargo)
        ");
        
        $stmt->execute([
            ':usuario_id' => $usuario_id,
            ':nombre' => 'Administrador',
            ':apellido' => 'Principal',
            ':documento' => $documento,
            ':email' => $email,
            ':telefono' => $telefono,
            ':cargo' => $cargo
        ]);
        
        echo "<div class='success'>✅ Administrador creado en tabla 'administradores'</div>";
        
        // Confirmar transacción
        $db->commit();
        
        echo "<div class='success'>";
        echo "<h3>🎉 ¡Proceso Completado Exitosamente!</h3>";
        echo "<p>Se eliminaron todos los usuarios anteriores y se creó el nuevo administrador.</p>";
        echo "</div>";
        
        // Verificar usuarios finales
        echo "<div class='info'>";
        echo "<h3>✅ Usuarios en la Base de Datos (Después de la Limpieza):</h3>";
        $stmt = $db->query("
            SELECT u.usuario_id, u.nombre, u.email, u.rol, u.activo, 
                   a.documento, a.cargo
            FROM usuarios u
            LEFT JOIN administradores a ON u.usuario_id = a.usuario_id
            ORDER BY u.usuario_id
        ");
        $usuarios_finales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($usuarios_finales) > 0) {
            echo "<table>";
            echo "<tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Activo</th><th>Documento</th><th>Cargo</th></tr>";
            foreach ($usuarios_finales as $user) {
                echo "<tr>";
                echo "<td>" . $user['usuario_id'] . "</td>";
                echo "<td>" . htmlspecialchars($user['nombre']) . "</td>";
                echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                echo "<td>" . $user['rol'] . "</td>";
                echo "<td>" . ($user['activo'] ? 'Sí' : 'No') . "</td>";
                echo "<td>" . ($user['documento'] ?? '-') . "</td>";
                echo "<td>" . ($user['cargo'] ?? '-') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "<p><strong>Total de usuarios: " . count($usuarios_finales) . "</strong></p>";
        }
        echo "</div>";
        
        echo "<div class='success'>";
        echo "<h3>🔐 Credenciales de Acceso:</h3>";
        echo "<table>";
        echo "<tr><th>Campo</th><th>Valor</th></tr>";
        echo "<tr><td>Email</td><td><strong>$email</strong></td></tr>";
        echo "<tr><td>Contraseña</td><td><strong>$password</strong></td></tr>";
        echo "<tr><td>Rol</td><td><strong>$rol</strong></td></tr>";
        echo "</table>";
        echo "</div>";
        
        echo "<div class='info'>";
        echo "<h3>🚀 Siguiente Paso:</h3>";
        echo "<p>Ya puedes iniciar sesión en el sistema con las credenciales mostradas arriba.</p>";
        echo "<a href='index.php' class='btn'>Ir al Login</a>";
        echo "</div>";
        
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "
</div>
</body>
</html>";
?>
