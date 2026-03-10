<?php 

class Experiencia
{
	private $id;
	private $nombre;
	private $descripcion;
	private $area;
    private $programas_relacionados;

	
	function __construct($id, $nombre, $descripcion, $area, $programas_relacionados)
	{
		$this->setId($id);
		$this->setNombre($nombre);
		$this->setDescripcion($descripcion);
		$this->setArea($area);
		$this->setProgramasRelacionados($programas_relacionados);		
	}

	public function getId(){
		return $this->id;
	}

	public function setId($id){
		$this->id = $id;
	}

	public function getNombre(){
		return $this->nombre;
	}

	public function setNombre($nombre){
		$this->nombre = $nombre;
	}

	public function getDescripcion(){
		return $this->descripcion;
	}

	public function setDescripcion($descripcion){
		$this->descripcion = $descripcion;
	}

	public function getArea(){
		return $this->area;
	}

	public function setArea($area){
		$this->area = $area;
	}

	public function getProgramasRelacionados(){
		return $this->programas_relacionados;
	}

	public function setProgramasRelacionados($programas_relacionados){
		$this->programas_relacionados = $programas_relacionados;
	}

	public static function save($experiencia){
		$db=Db::getConnect();

		$insert=$db->prepare('INSERT INTO experiencias (nombre_experiencia, descripcion, area_conocimiento, duracion_horas, nivel) VALUES (:nombre, :descripcion, :area, :duracion, :nivel)');
		$insert->bindValue('nombre',$experiencia->getNombre());
		$insert->bindValue('descripcion',$experiencia->getDescripcion());
		$insert->bindValue('area',$experiencia->getArea());
		$insert->bindValue('duracion', 0, PDO::PARAM_INT);
		$insert->bindValue('nivel', 'Básico');
		$insert->execute();
	}

	public static function all(){
		$db=Db::getConnect();
		$listaExperiencias=[];

		$select=$db->query('SELECT * FROM experiencias ORDER BY experiencia_id');

		foreach($select->fetchAll() as $experiencia){
			$listaExperiencias[]=new Experiencia(
				$experiencia['experiencia_id'],
				$experiencia['nombre_experiencia'],
				$experiencia['descripcion'],
				$experiencia['area_conocimiento'],
				$experiencia['programas_relacionados'] ?? ''
			);
		}
		return $listaExperiencias;
	}

	public static function searchById($id){
		$db=Db::getConnect();
		$select=$db->prepare('SELECT * FROM experiencias WHERE experiencia_id=:id');
		$select->bindValue('id',$id);
		$select->execute();

		$experienciaDb=$select->fetch();

		$experiencia = new Experiencia(
			$experienciaDb['experiencia_id'],
			$experienciaDb['nombre_experiencia'],
			$experienciaDb['descripcion'],
			$experienciaDb['area_conocimiento'],
			$experienciaDb['programas_relacionados'] ?? ''
		);

		return $experiencia;

	}

	public static function update($experiencia){
		$db=Db::getConnect();
		$update=$db->prepare('UPDATE experiencias SET nombre_experiencia=:nombre, descripcion=:descripcion, area_conocimiento=:area WHERE experiencia_id=:id');
		$update->bindValue('nombre', $experiencia->getNombre());
		$update->bindValue('descripcion',$experiencia->getDescripcion());
		$update->bindValue('area',$experiencia->getArea());
		$update->bindValue('id',$experiencia->getId());
		$update->execute();
	}

	public static function delete($id){
		$db=Db::getConnect();
		$delete=$db->prepare('DELETE FROM experiencias WHERE experiencia_id=:id');
		$delete->bindValue('id',$id);
		$delete->execute();		
	}
}

?>
