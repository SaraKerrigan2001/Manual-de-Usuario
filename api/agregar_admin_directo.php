<?php
/**
 * SCRIPT PARA AGREGAR USUARIO ADMINISTRADOR DIRECTAMENTE
 * Email: spiligr1@gmail.com
 * Contraseña: 12345678
 * Rol: Administrador
 */

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Agregar Usuario Administrador</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h1 { color: #10b981; border-bottom: 3px solid #10b981; padding-bottom: 10px; }
        .success { color: #28a745; font-weight: bold; background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { color: #dc3545; font-weight: bold; background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #e7f3ff; padding: 15px; border-left: 4px solid #2196F3; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #10b981; color: white; }
        .btn { display: inline-block; background-color: #10b981; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background-color: #059669; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🔐 Agregar Usuario Administrador</h1>
";

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
echo "<h3>📋 Datos del Usuario a Crear:</h3>";
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

try {
    // Conectar a la base de datos
    require_once 'connection.php';
    $db = Db::getConnect();
    
    if (!$db) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    echo "<div class='success'>✅ Conexión exitosa a la base de datos</div>";
    
    // Verificar si el usuario ya existe
    $stmt = $db->prepare("SELECT usuario_id, email FROM usuarios WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $usuario_existente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario_existente) {
        echo "<div class='error'>⚠️ El usuario con email $email ya existe en la base de datos</div>";
        echo "<div class='info'>";
        echo "<h3>Usuario Existente:</h3>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Email</th></tr>";
        echo "<tr><td>" . $usuario_existente['usuario_id'] . "</td><td>" . $usuario_existente['email'] . "</td></tr>";
        echo "</table>";
        echo "<p><strong>Puedes usar estas credenciales para iniciar sesión:</strong></p>";
        echo "<ul>";
        echo "<li>Email: <strong>$email</strong></li>";
        echo "<li>Contraseña: <strong>$password</strong></li>";
        echo "</ul>";
        echo "</div>";
    } else {
        // Iniciar transacción
        $db->beginTransaction();
        
        try {
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
            
            $nombre_partes = explode(' ', $nombre, 2);
            $nombre_admin = $nombre_partes[0];
            $apellido_admin = isset($nombre_partes[1]) ? $nombre_partes[1] : 'Principal';
            
            $stmt->execute([
                ':usuario_id' => $usuario_id,
                ':nombre' => $nombre_admin,
                ':apellido' => $apellido_admin,
                ':documento' => $documento,
                ':email' => $email,
                ':telefono' => $telefono,
                ':cargo' => $cargo
            ]);
            
            echo "<div class='success'>✅ Administrador creado en tabla 'administradores'</div>";
            
            // Confirmar transacción
            $db->commit();
            
            echo "<div class='success'>";
            echo "<h3>🎉 ¡Usuario Administrador Creado Exitosamente!</h3>";
            echo "<p><strong>Credenciales de Acceso:</strong></p>";
            echo "<table>";
            echo "<tr><th>Campo</th><th>Valor</th></tr>";
            echo "<tr><td>Email</td><td><strong>$email</strong></td></tr>";
            echo "<tr><td>Contraseña</td><td><strong>$password</strong></td></tr>";
            echo "<tr><td>Rol</td><td><strong>$rol</strong></td></tr>";
            echo "</table>";
            echo "</div>";
            
            // Verificar el usuario creado
            $stmt = $db->prepare("
                SELECT u.usuario_id, u.nombre, u.email, u.rol, u.activo, 
                       a.documento, a.cargo
                FROM usuarios u
                LEFT JOIN administradores a ON u.usuario_id = a.usuario_id
                WHERE u.email = :email
            ");
            $stmt->execute([':email' => $email]);
            $usuario_verificado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario_verificado) {
                echo "<div class='info'>";
                echo "<h3>✅ Verificación del Usuario:</h3>";
                echo "<table>";
                foreach ($usuario_verificado as $campo => $valor) {
                    echo "<tr><td>$campo</td><td>$valor</td></tr>";
                }
                echo "</table>";
                echo "</div>";
            }
            
            echo "<div class='info'>";
            echo "<h3>🚀 Siguiente Paso:</h3>";
            echo "<p>Ya puedes iniciar sesión en el sistema con las credenciales mostradas arriba.</p>";
            echo "<a href='index.php' class='btn'>Ir al Login</a>";
            echo "</div>";
            
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            $db->rollBack();
            throw $e;
        }
    }
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Error al crear el usuario:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
    
    echo "<div class='info'>";
    echo "<h3>💡 Soluciones:</h3>";
    echo "<ol>";
    echo "<li>Verifica que XAMPP esté ejecutándose (Apache y MySQL)</li>";
    echo "<li>Verifica que la base de datos 'cphpmysql' exista</li>";
    echo "<li>Importa el archivo progFormacion_v3.sql en phpMyAdmin</li>";
    echo "<li>Verifica las credenciales de MySQL en connection.php</li>";
    echo "</ol>";
    echo "<a href='http://localhost/phpmyadmin' class='btn' target='_blank'>Abrir phpMyAdmin</a>";
    echo "</div>";
}

echo "
</div>
</body>
</html>";
?>
