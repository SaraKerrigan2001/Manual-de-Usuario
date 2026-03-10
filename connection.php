<?php
/**
 * Clase de conexión a la base de datos con diagnóstico integrado
 * 
 * Para ver diagnóstico: connection.php?test=1
 * Para uso normal: simplemente incluir el archivo
 */

class Db
{
    private static $instance = NULL;
    
    // Configuración de la base de datos
    private static $host = 'localhost';
    private static $dbname = 'cphpmysql';
    private static $user = 'root';
    private static $pass = ''; // Sin contraseña en XAMPP por defecto
    
    private function __construct() {}

    public static function getConnect() {
        if (!isset(self::$instance)) {
            try {
                $pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
                $pdo_options[PDO::ATTR_DEFAULT_FETCH_MODE] = PDO::FETCH_ASSOC;
                $pdo_options[PDO::ATTR_EMULATE_PREPARES] = false;
                
                // Configuraciones para manejar múltiples conexiones simultáneas
                $pdo_options[PDO::ATTR_PERSISTENT] = false;
                $pdo_options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci";
                $pdo_options[PDO::ATTR_TIMEOUT] = 5;
                
                // Usar variables de entorno en producción
                $host = getenv('DB_HOST') ?: self::$host;
                $dbname = getenv('DB_NAME') ?: self::$dbname;
                $user = getenv('DB_USER') ?: self::$user;
                $pass = getenv('DB_PASS') ?: self::$pass;
                
                self::$instance = new PDO(
                    "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                    $user,
                    $pass,
                    $pdo_options
                );
                
                // Configurar el modo de transacción para evitar bloqueos
                self::$instance->setAttribute(PDO::ATTR_AUTOCOMMIT, 1);
                
            } catch (PDOException $e) {
                error_log("Error de conexión: " . $e->getMessage());
                
                // En desarrollo, mostrar error detallado
                if (isset($_SESSION)) {
                    $_SESSION['db_error'] = $e->getMessage();
                }
                
                // Si se solicita diagnóstico, mostrar página de error
                if (isset($_GET['test'])) {
                    self::showDiagnostic($e);
                    exit;
                }
                
                self::$instance = null;
            }
        } 
        return self::$instance;
    }
    
    /**
     * Cerrar la conexión explícitamente si es necesario
     */
    public static function closeConnection() {
        self::$instance = null;
    }
    
    /**
     * Mostrar diagnóstico de conexión
     */
    private static function showDiagnostic($error = null) {
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Diagnóstico de Conexión - SENA</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
                .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                h1 { color: #39A900; border-bottom: 3px solid #39A900; padding-bottom: 10px; }
                h2 { color: #007832; margin-top: 30px; }
                .success { color: #28a745; font-weight: bold; }
                .error { color: #dc3545; font-weight: bold; }
                .info { background: #e7f3ff; padding: 15px; border-left: 4px solid #2196F3; margin: 10px 0; }
                .warning { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 10px 0; }
                table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
                th { background-color: #39A900; color: white; }
                tr:nth-child(even) { background-color: #f9f9f9; }
                .btn { display: inline-block; background-color: #39A900; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
                .btn:hover { background-color: #007832; }
                code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>🔍 Diagnóstico de Conexión - Sistema SENA</h1>
                
                <h2>1. Información del Servidor</h2>
                <table>
                    <tr><th>Parámetro</th><th>Valor</th></tr>
                    <tr><td>Versión de PHP</td><td><?php echo phpversion(); ?></td></tr>
                    <tr><td>Servidor</td><td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td></tr>
                    <tr><td>Sistema Operativo</td><td><?php echo PHP_OS; ?></td></tr>
                </table>
                
                <h2>2. Extensión PDO MySQL</h2>
                <?php if (extension_loaded('pdo_mysql')): ?>
                    <div class="info">✅ <span class="success">PDO MySQL está habilitado</span></div>
                <?php else: ?>
                    <div class="warning">❌ <span class="error">PDO MySQL NO está habilitado</span></div>
                    <p>Solución: Habilita la extensión en php.ini</p>
                <?php endif; ?>
                
                <h2>3. Configuración de Conexión</h2>
                <table>
                    <tr><th>Parámetro</th><th>Valor</th></tr>
                    <tr><td>Host</td><td><?php echo self::$host; ?></td></tr>
                    <tr><td>Base de Datos</td><td><?php echo self::$dbname; ?></td></tr>
                    <tr><td>Usuario</td><td><?php echo self::$user; ?></td></tr>
                    <tr><td>Contraseña</td><td><?php echo empty(self::$pass) ? '(vacía)' : '***'; ?></td></tr>
                </table>
                
                <h2>4. Estado de la Conexión</h2>
                <?php if ($error): ?>
                    <div class="warning">
                        <p>❌ <span class="error">Error de conexión:</span></p>
                        <code><?php echo htmlspecialchars($error->getMessage()); ?></code>
                    </div>
                    
                    <h3>Posibles soluciones:</h3>
                    <ol>
                        <li>Verifica que XAMPP esté ejecutándose (Apache y MySQL)</li>
                        <li>Verifica que la base de datos <code><?php echo self::$dbname; ?></code> exista en phpMyAdmin</li>
                        <li>Importa el archivo <code>progFormacion_v3.sql</code> en phpMyAdmin</li>
                        <li>Verifica las credenciales de MySQL (usuario/contraseña)</li>
                    </ol>
                    
                    <a href="http://localhost/phpmyadmin" class="btn" target="_blank">Abrir phpMyAdmin</a>
                <?php else: ?>
                    <?php
                    try {
                        $pdo = new PDO(
                            "mysql:host=" . self::$host . ";dbname=" . self::$dbname . ";charset=utf8mb4",
                            self::$user,
                            self::$pass,
                            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                        );
                        ?>
                        <div class="info">✅ <span class="success">Conexión exitosa a la base de datos</span></div>
                        
                        <h2>5. Verificación de Tabla 'usuarios'</h2>
                        <?php
                        try {
                            $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            ?>
                            <div class="info">✅ Tabla 'usuarios' existe - Total de usuarios: <strong><?php echo $result['total']; ?></strong></div>
                            
                            <?php if ($result['total'] > 0): ?>
                                <h2>6. Usuarios Registrados</h2>
                                <table>
                                    <tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th></tr>
                                    <?php
                                    $stmt = $pdo->query("SELECT usuario_id, nombre, email, rol FROM usuarios ORDER BY usuario_id");
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr>";
                                        echo "<td>" . $row['usuario_id'] . "</td>";
                                        echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                        echo "<td>" . $row['rol'] . "</td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </table>
                                
                                <h2>7. Credenciales de Prueba</h2>
                                <table>
                                    <tr><th>Rol</th><th>Email</th><th>Contraseña</th></tr>
                                    <tr><td>Administrador</td><td>admin.sena@sena.edu.co</td><td>admin123</td></tr>
                                    <tr><td>Coordinador</td><td>maria.gonzalez@sena.edu.co</td><td>maria123</td></tr>
                                    <tr><td>Instructor</td><td>josevera@gmail.com</td><td>jose123</td></tr>
                                </table>
                                
                                <div class="info">
                                    <h3>✅ TODO ESTÁ FUNCIONANDO CORRECTAMENTE</h3>
                                    <p>El sistema está listo para usarse.</p>
                                </div>
                                
                                <a href="index.php" class="btn">Ir al Sistema de Login</a>
                            <?php else: ?>
                                <div class="warning">
                                    <p>⚠️ La tabla existe pero no hay usuarios registrados</p>
                                    <p>Importa el archivo SQL para crear los usuarios de prueba</p>
                                </div>
                            <?php endif; ?>
                            
                        <?php } catch (PDOException $e) { ?>
                            <div class="warning">
                                <p>❌ La tabla 'usuarios' no existe</p>
                                <code><?php echo htmlspecialchars($e->getMessage()); ?></code>
                                <p>Importa el archivo <code>progFormacion_v3.sql</code> en phpMyAdmin</p>
                            </div>
                            <a href="http://localhost/phpmyadmin" class="btn" target="_blank">Abrir phpMyAdmin</a>
                        <?php } ?>
                        
                    <?php } catch (PDOException $e) { ?>
                        <div class="warning">
                            <p>❌ <span class="error">Error de conexión:</span></p>
                            <code><?php echo htmlspecialchars($e->getMessage()); ?></code>
                        </div>
                    <?php } ?>
                <?php endif; ?>
                
                <hr style="margin: 30px 0;">
                <p style="text-align: center; color: #666;">
                    <small>Para volver a ver este diagnóstico: <code>connection.php?test=1</code></small>
                </p>
            </div>
        </body>
        </html>
        <?php
    }
}

// Si se accede directamente con ?test=1, mostrar diagnóstico
if (isset($_GET['test']) && $_GET['test'] == '1') {
    Db::getConnect();
}

?>