<?php

class Competencia {
    private $codigo;
    private $descripcion;
    private $horas;
    private $tipo;

    public function __construct($codigo, $descripcion, $horas = null, $tipo = null) {
        $this->codigo = $codigo;
        $this->descripcion = $descripcion;
        $this->horas = $horas;
        $this->tipo = $tipo;
    }

    public function getCodigo() { return $this->codigo; }
    public function getDescripcion() { return $this->descripcion; }
    public function getHoras() { return $this->horas; }
    public function getTipo() { return $this->tipo; }

    public static function crear($datos) {
        $db = Db::getConnect();
        $stmt = $db->prepare('INSERT INTO competencias (codigo, descripcion, horas, tipo) VALUES (:codigo, :descripcion, :horas, :tipo)');
        $stmt->bindValue(':codigo', $datos['codigo']);
        $stmt->bindValue(':descripcion', $datos['descripcion']);
        $stmt->bindValue(':horas', $datos['horas'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':tipo', $datos['tipo'] ?? null);
        return $stmt->execute();
    }

    public static function all() {
        $db = Db::getConnect();
        $stmt = $db->query('SELECT * FROM competencias ORDER BY codigo');
        $lista = [];
        if ($stmt) {
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) {
                $lista[] = new Competencia($c['codigo'], $c['descripcion'], $c['horas'], $c['tipo']);
            }
        }
        return $lista;
    }

    public static function eliminar($codigo) {
        $db = Db::getConnect();
        $stmt = $db->prepare('DELETE FROM competencias WHERE codigo = :codigo');
        $stmt->bindValue(':codigo', $codigo);
        return $stmt->execute();
    }
}
