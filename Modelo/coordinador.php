<?php 

class Coordinador
{
	private $id;
	private $nom_trans;
	private $duracion;
	private $modalidad;
    private $prog_base;
    private $objetivo;
    private $descripcion;
    private $competencias;


	
	function __construct($id, $nom_trans,$duracion, $modalidad, $prog_base, $objetivo, $descripcion, $competencias)
	{
		$this->setId($id);
        $this->setNomTrans($nom_trans);
        $this->setDuracion($duracion);
        $this->setModalidad($modalidad);
        $this->setProgBase($prog_base);
        $this->setObjetivo($objetivo);
        $this->setDescripcion($descripcion);
        $this->setCompetencias($competencias);		
	}

	 public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getNomTrans()
    {
        return $this->nom_trans;
    }

    public function setNomTrans($nom_trans)
    {
        $this->nom_trans = $nom_trans;
    }

    public function getDuracion()
    {
        return $this->duracion;
    }

    public function setDuracion($duracion)
    {
        $this->duracion = $duracion;
    }

    public function getModalidad()
    {
        return $this->modalidad;
    }

    public function setModalidad($modalidad)
    {
        $this->modalidad = $modalidad;
    }

    public function getProgBase()
    {
        return $this->prog_base;
    }

    public function setProgBase($prog_base)
    {
        $this->prog_base = $prog_base;
    }

    public function getObjetivo()
    {
        return $this->objetivo;
    }

    public function setObjetivo($objetivo)
    {
        $this->objetivo = $objetivo;
    }

    public function getDescripcion()
    {
        return $this->descripcion;
    }

    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;
    }

    public function getCompetencias()
    {
        return $this->competencias;
    }

    public function setCompetencias($competencias)
    {
        $this->competencias = $competencias;
    }

	public function setEstado($estado){
		
		if (strcmp($estado, 'on')==0) {
			$this->estado=1;
		} elseif(strcmp($estado, '1')==0) {
			$this->estado='checked';
		}elseif (strcmp($estado, '0')==0) {
			$this->estado='of';
		}else {
			$this->estado=0;
		}

	}

	public static function save($transversal){
		$db = Db::getConnect();

        $insert = $db->prepare('INSERT INTO transversales (nom_trans, duracion, modalidad, programa, objetivo, descripcion, competencias) 
                                VALUES (:nom_trans, :duracion, :modalidad, :programa, :objetivo, :descripcion, :competencias)');
        
        $insert->bindValue('nom_trans', $transversal->getNomTrans());
        $insert->bindValue('duracion', $transversal->getDuracion());
        $insert->bindValue('modalidad', $transversal->getModalidad());
        $insert->bindValue('programa', $transversal->getProgBase());
        $insert->bindValue('objetivo', $transversal->getObjetivo());
        $insert->bindValue('descripcion', $transversal->getDescripcion());
        $insert->bindValue('competencias', $transversal->getCompetencias());
        
        if ($insert->execute()) {
            return ['success' => true];
        }
        return ['success' => false, 'errores' => ['Error al guardar transversal']];
	}

    public static function crearTransversal($datos) {
        $db = Db::getConnect();
        $insert = $db->prepare('INSERT INTO transversales (nom_trans, duracion, modalidad, programa, objetivo, descripcion, competencias) 
                                VALUES (:nom_trans, :duracion, :modalidad, :programa, :objetivo, :descripcion, :competencias)');
        
        $insert->bindValue('nom_trans', $datos['nombre_transversal']);
        $insert->bindValue('duracion', $datos['duracion']);
        $insert->bindValue('modalidad', $datos['modalidad']);
        $insert->bindValue('programa', $datos['programa_base']);
        $insert->bindValue('objetivo', $datos['objetivo']);
        $insert->bindValue('descripcion', $datos['descripcion']);
        $insert->bindValue('competencias', is_array($datos['competencias']) ? implode(',', $datos['competencias']) : $datos['competencias']);
        
        if ($insert->execute()) {
            return ['success' => true];
        }
        return ['success' => false, 'errores' => ['Error al crear transversal']];
    }

    public static function obtenerTransversales() {
        $db = Db::getConnect();
        $stmt = $db->query('SELECT * FROM transversales ORDER BY nom_trans');
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public static function obtenerInstructores() {
        $db = Db::getConnect();
        $stmt = $db->query('SELECT id, nombre, apellido, especialidad FROM instructores ORDER BY nombre');
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public static function obtenerFichas() {
        $db = Db::getConnect();
        $stmt = $db->query('SELECT ficha_id, codigo_ficha, programa FROM fichas ORDER BY codigo_ficha');
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public static function asignar($datos) {
        $db = Db::getConnect();
        $stmt = $db->prepare('INSERT INTO asignaciones (id_transversal, instructor_id, ficha_id, fecha_inicio, fecha_fin) 
                              VALUES (:id_transversal, :id_instructor, :id_ficha, :inicio, :fin)');
        $stmt->bindValue(':id_transversal', $datos['id_transversal'], PDO::PARAM_INT);
        $stmt->bindValue(':id_instructor', $datos['id_instructor'], PDO::PARAM_INT);
        $stmt->bindValue(':id_ficha', $datos['id_ficha'], PDO::PARAM_INT);
        $stmt->bindValue(':inicio', $datos['inicio']);
        $stmt->bindValue(':fin', $datos['fin']);
        
        if ($stmt->execute()) {
            return ['success' => true];
        }
        return ['success' => false, 'errores' => ['Error al crear la asignación']];
    }

	public static function all(){
		$db=Db::getConnect();
		$listaCoordinadores=[];

		$select=$db->query('SELECT * FROM coordinadores order by id');

		foreach($select->fetchAll() as $coordinador){
			$listaCoordinadores[]=new Coordinador($coordinador['id'],$coordinador['nombres'],$coordinador['apellidos'],$coordinador['estado']);
		}
		return $listaCoordinadores;
	}

	public static function searchById($id){
		$db=Db::getConnect();
		$select=$db->prepare('SELECT * FROM coordinadores WHERE id=:id');
		$select->bindValue('id',$id);
		$select->execute();

		$coordinadorDb=$select->fetch();


		$coordinador = new Coordinador ($coordinadorDb['id'],$coordinadorDb['nombres'], $coordinadorDb['apellidos'], $coordinadorDb['estado']);
		//var_dump($coordinador);
		//die();
		return $coordinador;

	}

	public static function update($coordinador){
		$db=Db::getConnect();
		$update=$db->prepare('UPDATE coordinadores SET nom_trans=:nom_trans, apellidos=:apellidos, modalidad=:modalidad, estado=:estado, competencias=:competencias WHERE id=:id');
		$update->bindValue('id', $coordinador->getId());
        $update->bindValue('nom_trans', $coordinador->getNom_Trans());
        $update->bindValue('duracion',$coordinador->getDuracion());
        $update->bindValue('modalidad',$coordinador->getModalidad());
        $update->bindValue('programa',$coordinador->getPrograma());
        $update->bindValue('objetivo', $coordinador->getObjetivo());
        $update->bindValue('descripcion', $coordinador->getDescripcion());
        $update->bindValue('competencias', $coordinador->getCompetencias());
		$update->execute();
	}

	public static function delete($id){
		$db=Db::getConnect();
		$delete=$db->prepare('DELETE  FROM coordinadores WHERE id=:id');
		$delete->bindValue('id',$id);
		$delete->execute();		
	}
}

?>