<?php
/**
 * VERIFICAR USUARIO EN LA BASE DE DATOS
 */

require_once 'connection.php';

try {
    $db = Db::getConnect();
    
    echo "=== USUARIOS EN LA BASE DE DATOS ===\n\n";
    
    $stmt = $db->query("
        SELECT u.usuario_id, u.nombre, u.email, u.rol, u.activo, 
               a.documento, a.cargo
        FROM usuarios u
        LEFT JOIN administradores a ON u.usuario_id = a.usuario_id
        ORDER BY u.usuario_id
    ");
    
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($usuarios) > 0) {
        foreach ($usuarios as $user) {
            echo "ID: " . $user['usuario_id'] . "\n";
            echo "Nombre: " . $user['nombre'] . "\n";
            echo "Email: " . $user['email'] . "\n";
            echo "Rol: " . $user['rol'] . "\n";
            echo "Activo: " . ($user['activo'] ? 'Sí' : 'No') . "\n";
            echo "Documento: " . ($user['documento'] ?? 'N/A') . "\n";
            echo "Cargo: " . ($user['cargo'] ?? 'N/A') . "\n";
            echo "-----------------------------------\n";
        }
        echo "\nTotal de usuarios: " . count($usuarios) . "\n";
    } else {
        echo "No hay usuarios en la base de datos\n";
    }
    
    // Verificar específicamente el usuario spiligr1@gmail.com
    echo "\n=== VERIFICACIÓN ESPECÍFICA: spiligr1@gmail.com ===\n\n";
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->execute([':email' => 'spiligr1@gmail.com']);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario) {
        echo "✅ Usuario encontrado:\n";
        foreach ($usuario as $campo => $valor) {
            echo "$campo: $valor\n";
        }
    } else {
        echo "❌ Usuario NO encontrado\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
