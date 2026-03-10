<?php
// Test directo del API sin navegador
session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['rol'] = 'administrador';
$_SESSION['email'] = 'admin.sena@sena.edu.co';

echo "<h1>Test API Directo</h1>";
echo "<p>Sesión: usuario_id={$_SESSION['usuario_id']}, rol={$_SESSION['rol']}</p>";
echo "<hr>";

$tests = [
    'listar_instructores',
    'listar_fichas',
    'listar_ambientes',
    'listar_experiencias'
];

foreach ($tests as $action) {
    echo "<h2>Test: $action</h2>";
    
    // Hacer petición al API
    $url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/api/notificaciones.php?action=' . $action;
    
    echo "<p><strong>URL:</strong> $url</p>";
    
    // Usar cURL para hacer la petición con las cookies de sesión
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    $response = curl_exec($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $header_size);
    $body = substr($response, $header_size);
    
    curl_close($ch);
    
    echo "<h3>Headers:</h3>";
    echo "<pre>" . htmlspecialchars($headers) . "</pre>";
    
    echo "<h3>Body:</h3>";
    echo "<pre>" . htmlspecialchars($body) . "</pre>";
    
    // Intentar decodificar JSON
    $data = json_decode($body, true);
    if ($data) {
        echo "<p style='color:green'>✅ JSON válido</p>";
        if (isset($data['success']) && $data['success']) {
            echo "<p style='color:green'>✅ Success: true</p>";
            if (isset($data['instructores'])) echo "<p>Instructores: " . count($data['instructores']) . "</p>";
            if (isset($data['fichas'])) echo "<p>Fichas: " . count($data['fichas']) . "</p>";
            if (isset($data['ambientes'])) echo "<p>Ambientes: " . count($data['ambientes']) . "</p>";
            if (isset($data['experiencias'])) echo "<p>Experiencias: " . count($data['experiencias']) . "</p>";
        } else {
            echo "<p style='color:red'>❌ Success: false</p>";
            echo "<p>Error: " . ($data['error'] ?? 'Desconocido') . "</p>";
        }
    } else {
        echo "<p style='color:red'>❌ NO es JSON válido</p>";
        echo "<p>Error JSON: " . json_last_error_msg() . "</p>";
    }
    
    echo "<hr>";
}
?>
