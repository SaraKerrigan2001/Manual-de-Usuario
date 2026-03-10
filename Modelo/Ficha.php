<?php

class Ficha {
    private $ficha_id;
    private $codigo_ficha;
    private $programa;
    private $fecha_inicio;
    private $fecha_fin;
    private $estado;
    private $sede_id;

    public function __construct($ficha_id, $codigo_ficha, $programa, $fecha_inicio = null, $fecha_fin = null, $estado = null, $sede_id = null) {
        $this->ficha_id = $ficha_id;
        $this->codigo_ficha = $codigo_ficha;
        $this->programa = $programa;
        $this->fecha_inicio = $fecha_inicio;
        $this->fecha_fin = $fecha_fin;
        $this->estado = $estado;
        $this->sede_id = $sede_id;
    }

    public function getFichaId() { return $this->ficha_id; }
    public function getCodigoFicha() { return $this->codigo_ficha; }
    public function getPrograma() { return $this->programa; }
    public function getFechaInicio() { return $this->fecha_inicio; }
    public function getFechaFin() { return $this->fecha_fin; }
    public function getEstado() { return $this->estado; }
    public function getSedeId() { return $this->sede_id; }

    public static function all() {
        $db = Db::getConnect();
        $stmt = $db->query('SELECT * FROM fichas ORDER BY codigo_ficha');
        $fichas = [];
        if ($stmt) {
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $f) {
                $fichas[] = new Ficha(
                    $f['ficha_id'],
                    $f['codigo_ficha'],
                    $f['programa'],
                    $f['fecha_inicio'],
                    $f['fecha_fin'],
                    $f['estado'],
                    $f['sede_id']
                );
            }
        }
        return $fichas;
    }

    public static function crear($datos) {
        $db = Db::getConnect();
        $stmt = $db->prepare('INSERT INTO fichas (codigo_ficha, programa, fecha_inicio, fecha_fin, estado, sede_id) VALUES (:codigo, :programa, :inicio, :fin, :estado, :sede_id)');
        $stmt->bindValue(':codigo', $datos['codigo']);
        $stmt->bindValue(':programa', $datos['programa']);
        $stmt->bindValue(':inicio', $datos['fecha_inicio'] ?? null);
        $stmt->bindValue(':fin', $datos['fecha_fin'] ?? null);
        $stmt->bindValue(':estado', $datos['estado'] ?? 'Activa');
        $stmt->bindValue(':sede_id', $datos['sede_id'] ?? null, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
