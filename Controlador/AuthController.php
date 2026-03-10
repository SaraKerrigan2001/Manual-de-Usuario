<?php

class AuthController {
    
    public function login() {
        require_once('Vista/Auth/login.php');
    }
    
    public function procesarLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controlador=Auth&accion=login');
            exit;
        }
        
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $rol_seleccionado = $_POST['rol'] ?? '';
        
        if (empty($email) || empty($password) || empty($rol_seleccionado)) {
            $_SESSION['error'] = 'Por favor complete todos los campos';
            header('Location: index.php?controlador=Auth&accion=login');
            exit;
        }
        
        $db = Db::getConnect();
        
        if (!$db) {
            $_SESSION['error'] = 'Error de conexión a la base de datos. Por favor, ejecuta instalar_bd.php primero.';
            header('Location: index.php?controlador=Auth&accion=login');
            exit;
        }

        try {
            $stmt = $db->prepare('SELECT * FROM usuarios WHERE email = :email AND activo = 1');
            $stmt->bindValue(':email', $email);
            $stmt->execute();
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Si la tabla no existe
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                $_SESSION['error'] = 'La base de datos no está instalada. Por favor, ve a: <a href="instalar_bd.php" style="color: white; text-decoration: underline;">Instalar Base de Datos</a>';
                header('Location: index.php?controlador=Auth&accion=login');
                exit;
            }
            // Otro error
            $_SESSION['error'] = 'Error al buscar el usuario. Intenta de nuevo.';
            header('Location: index.php?controlador=Auth&accion=login');
            exit;
        }
        
        if (!$userData) {
            $_SESSION['error'] = 'No se encontró ninguna cuenta activa con ese correo.';
            header('Location: index.php?controlador=Auth&accion=login');
            exit;
        }
        
        if ($userData['rol'] !== $rol_seleccionado) {
            $_SESSION['error'] = "El rol seleccionado no coincide con su cuenta.";
            header('Location: index.php?controlador=Auth&accion=login');
            exit;
        }
        
        if (!password_verify($password, $userData['password'])) {
            $_SESSION['error'] = 'La contraseña es incorrecta.';
            header('Location: index.php?controlador=Auth&accion=login');
            exit;
        }
        
        // Actualizar último acceso
        $updateStmt = $db->prepare('UPDATE usuarios SET ultimo_acceso = NOW() WHERE usuario_id = :id');
        $updateStmt->bindValue(':id', $userData['usuario_id']);
        $updateStmt->execute();
        
        // Crear sesión
        $_SESSION['usuario_id'] = $userData['usuario_id'];
        $_SESSION['user_name'] = $userData['nombre'] ?? 'Usuario';
        $_SESSION['rol'] = $userData['rol'];

        $this->redirigirDashboard($userData['rol']);
    }

    public function olvidoPassword() {
        require_once('Vista/Auth/olvido_password.php');
    }

    public function procesarRecuperacion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controlador=Auth&accion=olvidoPassword');
            exit;
        }
        
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Por favor ingrese un correo electrónico válido';
            header('Location: index.php?controlador=Auth&accion=olvidoPassword');
            exit;
        }

        // LÓGICA DE BÚSQUEDA
        $db = Db::getConnect();
        
        if (!$db) {
            $_SESSION['error'] = 'Error de conexión a la base de datos. Por favor, ejecuta instalar_bd.php primero.';
            header('Location: index.php?controlador=Auth&accion=olvidoPassword');
            exit;
        }
        
        try {
            $stmt = $db->prepare('SELECT usuario_id, email, rol FROM usuarios WHERE email = :email AND activo = 1');
            $stmt->bindValue(':email', $email);
            $stmt->execute();
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Si la tabla no existe
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                $_SESSION['error'] = 'La base de datos no está instalada. Por favor, ejecuta: http://localhost/Mini-Proyecto/instalar_bd.php';
                header('Location: index.php?controlador=Auth&accion=olvidoPassword');
                exit;
            }
            // Otro error
            $_SESSION['error'] = 'Error al buscar el usuario. Intenta de nuevo.';
            header('Location: index.php?controlador=Auth&accion=olvidoPassword');
            exit;
        }

        if ($usuario) {
            // Mostrar contraseña y rol según el email
            $passwords = [
                'admin.sena@sena.edu.co' => 'admin123',
                'maria.gonzalez@sena.edu.co' => 'maria123',
                'josevera@gmail.com' => 'jose123'
            ];
            
            // Traducir rol al español
            $roles_es = [
                'administrador' => 'Administrador',
                'coordinador' => 'Coordinador',
                'instructor' => 'Instructor'
            ];
            
            if (isset($passwords[$email])) {
                $_SESSION['password_recuperada'] = $passwords[$email];
                $_SESSION['rol_recuperado'] = $roles_es[$usuario['rol']] ?? ucfirst($usuario['rol']);
                $_SESSION['mensaje'] = 'Contraseña recuperada exitosamente';
            } else {
                $_SESSION['mensaje'] = 'Si el correo está registrado, recibirás un enlace de recuperación.';
            }
        } else {
            $_SESSION['error'] = 'No se encontró ninguna cuenta con ese correo.';
        }

        header('Location: index.php?controlador=Auth&accion=olvidoPassword');
        exit;
    }
    
    public function registro() {
        require_once('Vista/Auth/registro.php');
    }
    
    public function procesarRegistro() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controlador=Auth&accion=registro');
            exit;
        }
        
        // Obtener datos del formulario (nombres coinciden con el HTML)
        $nombres = trim($_POST['nombres'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $tipo_documento = $_POST['tipo_documento'] ?? 'CC';
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        $rol = $_POST['rol'] ?? '';
        
        // Validaciones
        if (empty($nombres) || empty($apellidos) || empty($documento) || empty($email) || empty($password) || empty($password_confirm) || empty($rol)) {
            $_SESSION['error'] = 'Por favor complete todos los campos obligatorios';
            header('Location: index.php?controlador=Auth&accion=registro');
            exit;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'El correo electrónico no es válido';
            header('Location: index.php?controlador=Auth&accion=registro');
            exit;
        }
        
        if ($password !== $password_confirm) {
            $_SESSION['error'] = 'Las contraseñas no coinciden';
            header('Location: index.php?controlador=Auth&accion=registro');
            exit;
        }
        
        if (strlen($password) < 6) {
            $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres';
            header('Location: index.php?controlador=Auth&accion=registro');
            exit;
        }
        
        $db = Db::getConnect();
        
        if (!$db) {
            $_SESSION['error'] = 'Error de conexión a la base de datos. Por favor, ejecute instalar_bd.php primero.';
            header('Location: index.php?controlador=Auth&accion=registro');
            exit;
        }
        
        // Verificar si el email ya existe
        $stmt = $db->prepare('SELECT usuario_id FROM usuarios WHERE email = :email');
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'El correo electrónico ya está registrado';
            header('Location: index.php?controlador=Auth&accion=registro');
            exit;
        }
        
        // Verificar si el documento ya existe
        $stmt = $db->prepare('SELECT usuario_id FROM usuarios WHERE documento = :documento');
        $stmt->bindValue(':documento', $documento);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'El número de documento ya está registrado';
            header('Location: index.php?controlador=Auth&accion=registro');
            exit;
        }
        
        // Crear el usuario
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $nombre_completo = $nombres . ' ' . $apellidos;
        
        try {
            $stmt = $db->prepare('INSERT INTO usuarios (nombre, email, password, telefono, rol, activo, documento, tipo_documento, direccion) VALUES (:nombre, :email, :password, :telefono, :rol, 1, :documento, :tipo_documento, :direccion)');
            $stmt->bindValue(':nombre', $nombre_completo);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':password', $passwordHash);
            $stmt->bindValue(':telefono', $telefono);
            $stmt->bindValue(':rol', $rol);
            $stmt->bindValue(':documento', $documento);
            $stmt->bindValue(':tipo_documento', $tipo_documento);
            $stmt->bindValue(':direccion', $direccion);
            
            if ($stmt->execute()) {
                $_SESSION['mensaje'] = 'Registro exitoso. Ya puedes iniciar sesión.';
                header('Location: index.php?controlador=Auth&accion=login');
            } else {
                $_SESSION['error'] = 'Error al crear la cuenta. Intenta nuevamente.';
                header('Location: index.php?controlador=Auth&accion=registro');
            }
        } catch (PDOException $e) {
            // Si el error es por tabla no existente
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                $_SESSION['error'] = 'La base de datos no está instalada. Por favor, ejecute instalar_bd.php primero.';
            } else {
                $_SESSION['error'] = 'Error al crear la cuenta: ' . $e->getMessage();
            }
            header('Location: index.php?controlador=Auth&accion=registro');
        }
        exit;
    }

    private function redirigirDashboard($rol) {
        if ($rol === 'administrador') {
            header('Location: index.php?controlador=Administrador&accion=dashboard');
        } else if ($rol === 'coordinador') {
            header('Location: index.php?controlador=Coordinador&accion=dashboard');
        } else {
            header('Location: index.php?controlador=Instructor&accion=dashboard');
        }
        exit;
    }

}