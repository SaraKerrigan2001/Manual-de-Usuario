<?php 

class Sede
{
	private $sede_id;
	private $sede_nombre;
	private $direccion;
	private $ciudad;
	private $telefono;

	
	function __construct($sede_id, $sede_nombre, $direccion, $ciudad, $telefono)
	{
		$this->setSedeId($sede_id);
		$this->setSedeNombre($sede_nombre);
		$this->setDireccion($direccion);
		$this->setCiudad($ciudad);
		$this->setTelefono($telefono);		
	}

	public function getSedeId(){
		return $this->sede_id;
	}

	public function setSedeId($sede_id){
		$this->sede_id = $sede_id;
	}

	public function getSedeNombre(){
		return $this->sede_nombre;
	}

	public function setSedeNombre($sede_nombre){
		$this->sede_nombre = $sede_nombre;
	}

	public function getDireccion(){
		return $this->direccion;
	}

	public function setDireccion($direccion){
		$this->direccion = $direccion;
	}

	public function getCiudad(){
		return $this->ciudad;
	}

	public function setCiudad($ciudad){
		$this->ciudad = $ciudad;
	}

	public function getTelefono(){
		return $this->telefono;
	}

	public function setTelefono($telefono){
		$this->telefono = $telefono;
	}

	public static function crear($datos){
		$db=Db::getConnect();

		$insert=$db->prepare('INSERT INTO sedes (nombre_sede, direccion, ciudad, telefono) VALUES (:nombre_sede, :direccion, :ciudad, :telefono)');
		$insert->bindValue('nombre_sede',$datos['nombre']);
		$insert->bindValue('direccion',$datos['direccion']);
		$insert->bindValue('ciudad',$datos['ciudad'] ?? 'No especificada');
		$insert->bindValue('telefono',$datos['telefono']);
		
		if ($insert->execute()) {
			return ['success' => true, 'id' => $db->lastInsertId()];
		}
		return ['success' => false, 'errores' => ['Error al insertar sede']];
	}

	public static function save($sede){
		$db=Db::getConnect();

		$insert=$db->prepare('INSERT INTO sedes VALUES (NULL, :nombre_sede,:direccion,:ciudad,:telefono)');
		$insert->bindValue('nombre_sede',$sede->getSedeNombre());
		$insert->bindValue('direccion',$sede->getDireccion());
		$insert->bindValue('ciudad',$sede->getCiudad());
		$insert->bindValue('telefono',$sede->getTelefono());
		$insert->execute();
	}

	public static function obtenerTodos(){
		return self::all();
	}

	public static function all(){
		$db=Db::getConnect();
		$listaSedes=[];

		$select=$db->query('SELECT * FROM sedes order by sede_id');

		foreach($select->fetchAll() as $sede){
			$listaSedes[]=new Sede($sede['sede_id'],$sede['nombre_sede'],$sede['direccion'],$sede['ciudad'],$sede['telefono']);
		}
		return $listaSedes;
	}

	public static function obtenerPorId($id){
		return self::searchById($id);
	}

	public static function searchById($id){
		$db=Db::getConnect();
		$select=$db->prepare('SELECT * FROM sedes WHERE sede_id=:sede_id');
		$select->bindValue('sede_id',$id);
		$select->execute();

		$sedeDb=$select->fetch();

		$sede = new Sede ($sedeDb['sede_id'],$sedeDb['nombre_sede'], $sedeDb['direccion'], $sedeDb['ciudad'], $sedeDb['telefono']);

		return $sede;

	}

	public static function update($sede){
		$db=Db::getConnect();
		$update=$db->prepare('UPDATE sedes SET nombre_sede=:nombre_sede, direccion=:direccion, ciudad=:ciudad, telefono=:telefono WHERE sede_id=:sede_id');
		$update->bindValue('nombre_sede', $sede->getSedeNombre());
		$update->bindValue('direccion',$sede->getDireccion());
		$update->bindValue('ciudad',$sede->getCiudad());
		$update->bindValue('telefono',$sede->getTelefono());
		$update->bindValue('sede_id',$sede->getSedeId());
		$update->execute();
	}

	public static function delete($id){
		$db=Db::getConnect();
		$delete=$db->prepare('DELETE  FROM sedes WHERE sede_id=:sede_id');
		$delete->bindValue('sede_id',$id);
		$delete->execute();		
	}
}

?>
