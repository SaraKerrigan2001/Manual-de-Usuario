<?php

require_once('connection.php');
require_once('Modelo/Usuario.php');

class Administrador extends Usuario {
    
    public function __construct($usuario_id = null, $email = null, $password = null, $rol = 'administrador', $activo = 1, $nombre = null) {
        parent::__construct($usuario_id, $email, $password, $rol, $activo, $nombre);
    }

    /**
     * Obtener perfil del administrador por usuario_id
     */
    public static function obtenerPerfil($usuario_id) {
        $db = Db::getConnect();
        $stmt = $db->prepare('SELECT * FROM administradores WHERE usuario_id = :id');
        $stmt->bindValue(':id', $usuario_id);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Obtener todas las sedes
     */
    public static function obtenerSedes() {
        $db = Db::getConnect();
        $select = $db->query('SELECT * FROM sedes ORDER BY nombre_sede');
        return $select->fetchAll();
    }

    /**
     * Obtener todos los instructores
     */
    public static function obtenerInstructores() {
        $db = Db::getConnect();
        $select = $db->query('SELECT * FROM instructores ORDER BY nombre');
        return $select->fetchAll();
    }

    /**
     * Obtener todas las fichas
     */
    public static function obtenerFichas() {
        $db = Db::getConnect();
        $select = $db->query('SELECT * FROM fichas ORDER BY codigo_ficha');
        return $select->fetchAll();
    }

    /**
     * Obtener todos los aprendices
     */
    public static function obtenerAprendices() {
        $db = Db::getConnect();
        $select = $db->query('SELECT a.*, f.codigo_ficha FROM aprendices a LEFT JOIN fichas f ON a.ficha_id = f.ficha_id ORDER BY a.nombre');
        return $select->fetchAll();
    }

    /**
     * Métodos CRUD genéricos o específicos para el Administrador
     */
    
    // SEDES
    public static function crearSede($datos) {
        $db = Db::getConnect();
        $insert = $db->prepare('INSERT INTO sedes (nombre_sede, direccion, telefono, ciudad, departamento) VALUES (:nombre, :direccion, :telefono, :ciudad, :departamento)');
        return $insert->execute([
            'nombre' => $datos['nombre'],
            'direccion' => $datos['direccion'],
            'telefono' => $datos['telefono'] ?? null,
            'ciudad' => $datos['ciudad'] ?? null,
            'departamento' => $datos['departamento'] ?? null
        ]);
    }

    public static function editarSede($id, $datos) {
        $db = Db::getConnect();
        $update = $db->prepare('UPDATE sedes SET nombre_sede = :nombre, direccion = :direccion, telefono = :telefono, ciudad = :ciudad, departamento = :departamento WHERE sede_id = :id');
        return $update->execute([
            'id' => $id,
            'nombre' => $datos['nombre'],
            'direccion' => $datos['direccion'],
            'telefono' => $datos['telefono'] ?? null,
            'ciudad' => $datos['ciudad'] ?? null,
            'departamento' => $datos['departamento'] ?? null
        ]);
    }

    public static function eliminarSede($id) {
        $db = Db::getConnect();
        $delete = $db->prepare('DELETE FROM sedes WHERE sede_id = :id');
        return $delete->execute(['id' => $id]);
    }

    // INSTRUCTORES
    public static function eliminarInstructor($id) {
        $db = Db::getConnect();
        $delete = $db->prepare('DELETE FROM instructores WHERE id = :id');
        return $delete->execute(['id' => $id]);
    }

    // FICHAS
    public static function eliminarFicha($id) {
        $db = Db::getConnect();
        $delete = $db->prepare('DELETE FROM fichas WHERE ficha_id = :id');
        return $delete->execute(['id' => $id]);
    }

    // APRENDICES
    public static function eliminarAprendiz($id) {
        $db = Db::getConnect();
        $delete = $db->prepare('DELETE FROM aprendices WHERE aprendiz_id = :id');
        return $delete->execute(['id' => $id]);
    }

    // AMBIENTES
    public static function eliminarAmbiente($id) {
        $db = Db::getConnect();
        $stmt = $db->prepare('DELETE FROM ambientes WHERE ambiente_id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Actualizar sede
     */
    public static function actualizarSede($id, $nombre, $direccion = '', $encargado = '', $telefono = '') {
        $db = Db::getConnect();
        $stmt = $db->prepare('UPDATE sedes SET nombre_sede = :nombre, direccion = :direccion, encargado = :encargado, telefono = :telefono WHERE sede_id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':nombre', $nombre);
        $stmt->bindValue(':direccion', $direccion);
        $stmt->bindValue(':encargado', $encargado);
        $stmt->bindValue(':telefono', $telefono);
        return $stmt->execute();
    }

    /**
     * Actualizar aprendiz
     */
    public static function actualizarAprendiz($id, $nombre, $apellido, $documento, $ficha_id, $tipo_doc = '', $email = '') {
        $db = Db::getConnect();
        $stmt = $db->prepare('UPDATE aprendices SET nombre = :nombre, apellido = :apellido, documento = :documento, ficha_id = :ficha, tipo_documento = :tipo, email = :email WHERE aprendiz_id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':nombre', $nombre);
        $stmt->bindValue(':apellido', $apellido);
        $stmt->bindValue(':documento', $documento);
        $stmt->bindValue(':ficha', $ficha_id, PDO::PARAM_INT);
        $stmt->bindValue(':tipo', $tipo_doc);
        $stmt->bindValue(':email', $email);
        return $stmt->execute();
    }

    /**
     * Actualizar instructor
     */
    public static function actualizarInstructor($id, $nombre, $apellido, $especialidad = '', $email = '') {
        $db = Db::getConnect();
        $stmt = $db->prepare('UPDATE instructores SET nombre = :nombre, apellido = :apellido, especialidad = :especialidad, email = :email WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':nombre', $nombre);
        $stmt->bindValue(':apellido', $apellido);
        $stmt->bindValue(':especialidad', $especialidad);
        $stmt->bindValue(':email', $email);
        return $stmt->execute();
    }

    /**
     * Actualizar ficha
     */
    public static function actualizarFicha($id, $codigo, $programa, $sede_id, $inicio = null, $fin = null, $aprendices = 0) {
        $db = Db::getConnect();
        $stmt = $db->prepare('UPDATE fichas SET codigo_ficha = :codigo, programa = :programa, sede_id = :sede, fecha_inicio = :inicio, fecha_fin_lectiva = :fin, num_aprendices = :aprendices WHERE ficha_id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':codigo', $codigo);
        $stmt->bindValue(':programa', $programa);
        $stmt->bindValue(':sede', $sede_id, PDO::PARAM_INT);
        $stmt->bindValue(':inicio', $inicio);
        $stmt->bindValue(':fin', $fin);
        $stmt->bindValue(':aprendices', $aprendices, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Actualizar ambiente
     */
    public static function actualizarAmbiente($id, $nombre, $sede_id, $capacidad = 0, $tipo = '') {
        $db = Db::getConnect();
        $stmt = $db->prepare('UPDATE ambientes SET nombre_ambiente = :nombre, sede_id = :sede, capacidad = :capacidad, tipo = :tipo WHERE ambiente_id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':nombre', $nombre);
        $stmt->bindValue(':sede', $sede_id, PDO::PARAM_INT);
        $stmt->bindValue(':capacidad', $capacidad, PDO::PARAM_INT);
        $stmt->bindValue(':tipo', $tipo);
        return $stmt->execute();
    }
}
?>
