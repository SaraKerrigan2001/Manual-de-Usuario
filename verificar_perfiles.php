<?php
require_once(__DIR__ . '/connection.php');
require_once(__DIR__ . '/api/session_config.php');

echo "<h1>Diagnóstico de Perfiles y Usuarios</h1>";

$db = Db::getConnect();

if (!$db) {
    echo "<p style='color:red;'>Error connecting to DB</p>";
    exit;
}

$emails = [
    'admin.sena@sena.edu.co' => 'administrador',
    'maria.gonzalez@sena.edu.co' => 'coordinador',
    'josevera@gmail.com' => 'instructor'
];

echo "<h2>Verificación de Usuarios en BD:</h2><ul>";
foreach ($emails as $email => $expected_rol) {
    try {
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $status = ($user['rol'] === $expected_rol) ? "✅ OK" : "❌ Rol incorrecto (esperado $expected_rol, tiene {$user['rol']})";
            echo "<li>$email: $status</li>";
        } else {
            echo "<li style='color:red;'>$email: ❌ NO EXISTE</li>";
        }
    } catch (Exception $e) {
        echo "<li style='color:red;'>$email: Error: " . $e->getMessage() . "</li>";
    }
}
echo "</ul>";

echo "<h2>Enlaces Rápidos para Verificación de Perfiles:</h2>";
echo "<ul>";
echo "<li><a href='index.php?controlador=Administrador&accion=perfil'>Ver Perfil Administrador</a></li>";
echo "<li><a href='index.php?controlador=Coordinador&accion=perfil'>Ver Perfil Coordinador</a></li>";
echo "<li><a href='index.php?controlador=Instructor&accion=perfil'>Ver Perfil Instructor</a></li>";
echo "</ul>";

echo "<h2>Asset Check:</h2>";
$bg_path = 'assets/img/login-bg.jpeg';
if (file_exists(__DIR__ . '/' . $bg_path)) {
    echo "<p>✅ login-bg.jpeg existe en la ruta correcta.</p>";
} else {
    echo "<p style='color:red;'>❌ login-bg.jpeg NO se encuentra en $bg_path</p>";
}
?>
