<?php

class Ambiente {
    private $ambiente_id;
    private $sede_id;
    private $nombre_ambiente;
    private $capacidad;
    private $tipo;
    private $estado;
    
    function __construct($ambiente_id, $sede_id, $nombre_ambiente, $capacidad = null, $tipo = null, $estado = null) {
        $this->setAmbienteId($ambiente_id);
        $this->setSedeId($sede_id);
        $this->setNombreAmbiente($nombre_ambiente);
        $this->setCapacidad($capacidad);
        $this->setTipo($tipo);
        $this->setEstado($estado);
    }
    
    // Getters y Setters
    public function getAmbienteId() {
        return $this->ambiente_id;
    }
    
    public function setAmbienteId($ambiente_id) {
        $this->ambiente_id = $ambiente_id;
    }
    
    public function getSedeId() {
        return $this->sede_id;
    }
    
    public function setSedeId($sede_id) {
        $this->sede_id = $sede_id;
    }
    
    public function getNombreAmbiente() {
        return $this->nombre_ambiente;
    }
    
    public function setNombreAmbiente($nombre_ambiente) {
        $this->nombre_ambiente = $nombre_ambiente;
    }
    
    public function getCapacidad() {
        return $this->capacidad;
    }
    
    public function setCapacidad($capacidad) {
        $this->capacidad = $capacidad;
    }
    
    public function getTipo() {
        return $this->tipo;
    }
    
    public function setTipo($tipo) {
        $this->tipo = $tipo;
    }
    
    public function getEstado() {
        return $this->estado;
    }
    
    public function setEstado($estado) {
        $this->estado = $estado;
    }
    
    /**
     * Guardar nuevo ambiente
     */
    public static function save($ambiente) {
        $db = Db::getConnect();
        
        $insert = $db->prepare('INSERT INTO ambientes (sede_id, nombre_ambiente, capacidad, tipo, estado) 
                                VALUES (:sede_id, :nombre, :capacidad, :tipo, :estado)');
        
        $insert->bindValue('sede_id', $ambiente->getSedeId(), PDO::PARAM_INT);
        $insert->bindValue('nombre', $ambiente->getNombreAmbiente());
        $insert->bindValue('capacidad', $ambiente->getCapacidad());
        $insert->bindValue('tipo', $ambiente->getTipo());
        $insert->bindValue('estado', $ambiente->getEstado() ?: 'Disponible');
        $insert->execute();
    }
    
    /**
     * Obtener todos los ambientes
     */
    public static function all() {
        $db = Db::getConnect();
        $listaAmbientes = [];
        
        $select = $db->query('SELECT a.*, s.nombre_sede 
                              FROM ambientes a 
                              INNER JOIN sedes s ON a.sede_id = s.sede_id 
                              ORDER BY a.ambiente_id');
        
        foreach($select->fetchAll() as $ambiente) {
            $listaAmbientes[] = new Ambiente(
                $ambiente['ambiente_id'],
                $ambiente['sede_id'],
                $ambiente['nombre_ambiente'],
                $ambiente['capacidad'],
                $ambiente['tipo'],
                $ambiente['estado']
            );
        }
        return $listaAmbientes;
    }
    
    /**
     * Obtener ambientes por sede
     */
    public static function getBySede($sede_id) {
        $db = Db::getConnect();
        $listaAmbientes = [];
        
        $select = $db->prepare('SELECT * FROM ambientes WHERE sede_id = :sede_id ORDER BY ambiente_id');
        $select->bindValue('sede_id', $sede_id, PDO::PARAM_INT);
        $select->execute();
        
        foreach($select->fetchAll() as $ambiente) {
            $listaAmbientes[] = new Ambiente(
                $ambiente['ambiente_id'],
                $ambiente['sede_id'],
                $ambiente['nombre_ambiente'],
                $ambiente['capacidad'],
                $ambiente['tipo'],
                $ambiente['estado']
            );
        }
        return $listaAmbientes;
    }
    
    /**
     * Buscar ambiente por ID
     */
    public static function searchById($id) {
        $db = Db::getConnect();
        $select = $db->prepare('SELECT * FROM ambientes WHERE ambiente_id = :id');
        $select->bindValue('id', $id, PDO::PARAM_INT);
        $select->execute();
        
        $ambienteDb = $select->fetch();
        
        if ($ambienteDb) {
            $ambiente = new Ambiente(
                $ambienteDb['ambiente_id'],
                $ambienteDb['sede_id'],
                $ambienteDb['nombre_ambiente'],
                $ambienteDb['capacidad'],
                $ambienteDb['tipo'],
                $ambienteDb['estado']
            );
            return $ambiente;
        }
        return null;
    }
    
    /**
     * Actualizar ambiente
     */
    public static function update($ambiente) {
        $db = Db::getConnect();
        $update = $db->prepare('UPDATE ambientes 
                                SET nombre_ambiente = :nombre, 
                                    sede_id = :sede_id, 
                                    capacidad = :capacidad, 
                                    tipo = :tipo, 
                                    estado = :estado 
                                WHERE ambiente_id = :ambiente_id');
        
        $update->bindValue('ambiente_id', $ambiente->getAmbienteId(), PDO::PARAM_INT);
        $update->bindValue('nombre', $ambiente->getNombreAmbiente());
        $update->bindValue('sede_id', $ambiente->getSedeId(), PDO::PARAM_INT);
        $update->bindValue('capacidad', $ambiente->getCapacidad());
        $update->bindValue('tipo', $ambiente->getTipo());
        $update->bindValue('estado', $ambiente->getEstado());
        $update->execute();
    }
    
    /**
     * Eliminar ambiente
     */
    public static function delete($id) {
        $db = Db::getConnect();
        $delete = $db->prepare('DELETE FROM ambientes WHERE ambiente_id = :id');
        $delete->bindValue('id', $id, PDO::PARAM_INT);
        $delete->execute();
    }
    
    /**
     * Verificar si el ambiente está en uso
     */
    public static function isInUse($id) {
        $db = Db::getConnect();
        $select = $db->prepare('SELECT COUNT(*) as total FROM asignaciones WHERE ambiente_id = :id');
        $select->bindValue('id', $id, PDO::PARAM_INT);
        $select->execute();
        
        $result = $select->fetch();
        return $result['total'] > 0;
    }
}

?>
