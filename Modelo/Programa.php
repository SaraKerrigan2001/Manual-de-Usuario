<?php

class Programa {
    private $codigo;
    private $nombre;
    private $nivel;
    private $duracion;
    private $estado;

    public function __construct($codigo, $nombre, $nivel = null, $duracion = null, $estado = 'Activo') {
        $this->codigo = $codigo;
        $this->nombre = $nombre;
        $this->nivel = $nivel;
        $this->duracion = $duracion;
        $this->estado = $estado;
    }

    public function getCodigo() { return $this->codigo; }
    public function getNombre() { return $this->nombre; }
    public function getNivel() { return $this->nivel; }
    public function getDuracion() { return $this->duracion; }
    public function getEstado() { return $this->estado; }

    public static function crear($datos) {
        $db = Db::getConnect();
        $stmt = $db->prepare('INSERT INTO programas (codigo, nombre, nivel, duracion_meses, estado) VALUES (:codigo, :nombre, :nivel, :duracion, :estado)');
        $stmt->bindValue(':codigo', $datos['codigo']);
        $stmt->bindValue(':nombre', $datos['nombre']);
        $stmt->bindValue(':nivel', $datos['nivel'] ?? null);
        $stmt->bindValue(':duracion', $datos['duracion'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':estado', $datos['estado'] ?? 'Activo');
        return $stmt->execute();
    }

    public static function all() {
        $db = Db::getConnect();
        $stmt = $db->query('SELECT * FROM programas ORDER BY codigo');
        $lista = [];
        if ($stmt) {
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $p) {
                // Map duracion_meses to $duracion
                $lista[] = new Programa($p['codigo'], $p['nombre'], $p['nivel'], $p['duracion_meses'], $p['estado']);
            }
        }
        return $lista;
    }

    public static function eliminar($codigo) {
        $db = Db::getConnect();
        $stmt = $db->prepare('DELETE FROM programas WHERE codigo = :codigo');
        $stmt->bindValue(':codigo', $codigo);
        return $stmt->execute();
    }
}
