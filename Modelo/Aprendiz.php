<?php

class Aprendiz {
    private $aprendiz_id;
    private $ficha_id;
    private $nombre;
    private $apellido;
    private $documento;
    private $tipo_documento;
    private $email;
    private $telefono;
    private $fecha_ingreso;

    public function __construct($aprendiz_id, $ficha_id, $nombre, $apellido, $documento, $tipo_documento, $email = null, $telefono = null, $fecha_ingreso = null) {
        $this->aprendiz_id = $aprendiz_id;
        $this->ficha_id = $ficha_id;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->documento = $documento;
        $this->tipo_documento = $tipo_documento;
        $this->email = $email;
        $this->telefono = $telefono;
        $this->fecha_ingreso = $fecha_ingreso;
    }

    public static function crear($datos) {
        $db = Db::getConnect();
        $stmt = $db->prepare('INSERT INTO aprendices (ficha_id, nombre, apellido, documento, tipo_documento, email, telefono, fecha_ingreso) VALUES (:ficha, :nombre, :apellido, :documento, :tipo, :email, :telefono, :fecha)');
        $stmt->bindValue(':ficha', $datos['ficha_id'], PDO::PARAM_INT);
        $stmt->bindValue(':nombre', $datos['nombre']);
        $stmt->bindValue(':apellido', $datos['apellido']);
        $stmt->bindValue(':documento', $datos['documento']);
        $stmt->bindValue(':tipo', $datos['tipo_documento']);
        $stmt->bindValue(':email', $datos['email']);
        $stmt->bindValue(':telefono', $datos['telefono'] ?? null);
        $stmt->bindValue(':fecha', $datos['fecha_ingreso'] ?? date('Y-m-d'));
        return $stmt->execute();
    }

    public static function all() {
        $db = Db::getConnect();
        $stmt = $db->query('SELECT * FROM aprendices ORDER BY apellido, nombre');
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
}
