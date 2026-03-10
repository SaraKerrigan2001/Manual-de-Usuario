<?php

class Usuario {
    private $usuario_id;
    private $nombre;
    private $email;
    private $password;
    private $rol;
    private $activo;
    
    public function __construct($usuario_id = null, $email = null, $password = null, $rol = null, $activo = 1, $nombre = null) {
        $this->usuario_id = $usuario_id;
        $this->nombre = $nombre;
        $this->email = $email;
        $this->password = $password;
        $this->rol = $rol;
        $this->activo = $activo;
    }
    
    // Getters y Setters
    public function getUsuarioId() { return $this->usuario_id; }
    public function setUsuarioId($usuario_id) { $this->usuario_id = $usuario_id; }
    
    public function getNombre() { return $this->nombre; }
    public function setNombre($nombre) { $this->nombre = $nombre; }
    
    public function getEmail() { return $this->email; }
    public function setEmail($email) { $this->email = $email; }
    
    public function getPassword() { return $this->password; }
    public function setPassword($password) { $this->password = $password; }
    
    public function getRol() { return $this->rol; }
    public function setRol($rol) { $this->rol = $rol; }
    
    public function getActivo() { return $this->activo; }
    public function setActivo($activo) { $this->activo = $activo; }
    
    /**
     * Autenticar usuario
     */
    public static function login($email, $password) {
        $db = Db::getConnect();
        
        $stmt = $db->prepare('SELECT * FROM usuarios WHERE email = :email AND activo = 1');
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        
        $userData = $stmt->fetch();
        
        if ($userData && password_verify($password, $userData['password'])) {
            // Actualizar último acceso
            $updateStmt = $db->prepare('UPDATE usuarios SET ultimo_acceso = NOW() WHERE usuario_id = :id');
            $updateStmt->bindValue(':id', $userData['usuario_id']);
            $updateStmt->execute();
            
            return new Usuario(
                $userData['usuario_id'],
                $userData['email'],
                $userData['password'],
                $userData['rol'],
                $userData['activo'],
                $userData['nombre'] ?? 'Usuario'
            );
        }
        
        return false;
    }
    
    /**
     * Registrar nuevo usuario
     */
    public static function registrar($datos) {
        $db = Db::getConnect();
        
        // Extraer datos
        $email = $datos['email'];
        $password = $datos['password'];
        $rol = $datos['rol'];
        $nombres = $datos['nombres'] ?? '';
        $apellidos = $datos['apellidos'] ?? '';
        $nombre_completo = trim($nombres . ' ' . $apellidos);
        $documento = $datos['documento'] ?? '';
        $tipo_documento = $datos['tipo_documento'] ?? 'CC';
        $telefono = $datos['telefono'] ?? '';
        $direccion = $datos['direccion'] ?? '';
        
        // Verificar si el email ya existe
        $checkStmt = $db->prepare('SELECT usuario_id FROM usuarios WHERE email = :email');
        $checkStmt->bindValue(':email', $email);
        $checkStmt->execute();
        
        if ($checkStmt->fetch()) {
            return ['success' => false, 'error' => 'El email ya está registrado'];
        }
        
        // Verificar si el documento ya existe (solo para instructores)
        if ($rol === 'instructor' && !empty($documento)) {
            $checkDocStmt = $db->prepare('SELECT id FROM instructores WHERE documento = :documento');
            $checkDocStmt->bindValue(':documento', $documento);
            $checkDocStmt->execute();
            
            if ($checkDocStmt->fetch()) {
                return ['success' => false, 'error' => 'El número de documento ya está registrado'];
            }
        }
        
        // Hash de la contraseña
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Iniciar transacción
        $db->beginTransaction();
        
        try {
            // Insertar usuario
            $stmt = $db->prepare('INSERT INTO usuarios (nombre, email, password, telefono, rol) VALUES (:nombre, :email, :password, :telefono, :rol)');
            $stmt->bindValue(':nombre', $nombre_completo);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':password', $hashedPassword);
            $stmt->bindValue(':telefono', $telefono);
            $stmt->bindValue(':rol', $rol);
            
            if (!$stmt->execute()) {
                throw new Exception('Error al crear usuario');
            }
            
            $usuario_id = $db->lastInsertId();
            
            // Si es instructor, crear registro en tabla instructores
            if ($rol === 'instructor') {
                $stmt_instructor = $db->prepare('
                    INSERT INTO instructores (nombre, apellido, documento, tipo_documento, email, telefono, fecha_ingreso) 
                    VALUES (:nombre, :apellido, :documento, :tipo_documento, :email, :telefono, CURDATE())
                ');
                $stmt_instructor->bindValue(':nombre', $nombres);
                $stmt_instructor->bindValue(':apellido', $apellidos);
                $stmt_instructor->bindValue(':documento', $documento);
                $stmt_instructor->bindValue(':tipo_documento', $tipo_documento);
                $stmt_instructor->bindValue(':email', $email);
                $stmt_instructor->bindValue(':telefono', $telefono);
                
                if (!$stmt_instructor->execute()) {
                    throw new Exception('Error al crear perfil de instructor');
                }
            }

            // Si es administrador, crear registro en tabla administradores
            if ($rol === 'administrador') {
                $stmt_admin = $db->prepare('
                    INSERT INTO administradores (usuario_id, nombre, apellido, documento, tipo_documento, email, telefono) 
                    VALUES (:usuario_id, :nombre, :apellido, :documento, :tipo_documento, :email, :telefono)
                ');
                $stmt_admin->bindValue(':usuario_id', $usuario_id);
                $stmt_admin->bindValue(':nombre', $nombres);
                $stmt_admin->bindValue(':apellido', $apellidos);
                $stmt_admin->bindValue(':documento', $documento);
                $stmt_admin->bindValue(':tipo_documento', $tipo_documento);
                $stmt_admin->bindValue(':email', $email);
                $stmt_admin->bindValue(':telefono', $telefono);
                
                if (!$stmt_admin->execute()) {
                    throw new Exception('Error al crear perfil de administrador');
                }
            }
            
            // Confirmar transacción
            $db->commit();
            
            return ['success' => true, 'usuario_id' => $usuario_id];
            
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            $db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Obtener usuario por ID
     */
    public static function obtenerPorId($id) {
        $db = Db::getConnect();
        
        $stmt = $db->prepare('SELECT * FROM usuarios WHERE usuario_id = :id');
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        
        $userData = $stmt->fetch();
        
        if ($userData) {
            return new Usuario(
                $userData['usuario_id'],
                $userData['email'],
                $userData['password'],
                $userData['rol'],
                $userData['activo']
            );
        }
        
        return null;
    }
}
