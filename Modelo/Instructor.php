<?php 

class Instructor
{
	private $id;
	private $nombre;
	private $apellido;
	private $documento;
	private $tipo_documento;
	private $email;
	private $telefono;
	private $especialidad;
	private $fecha_ingreso;

	
	function __construct($id, $nombre, $apellido, $documento, $tipo_documento, $email, $telefono, $especialidad, $fecha_ingreso)
	{
		$this->setId($id);
		$this->setNombre($nombre);
		$this->setApellido($apellido);
		$this->setDocumento($documento);
		$this->setTipoDocumento($tipo_documento);
		$this->setEmail($email);
		$this->setTelefono($telefono);
		$this->setEspecialidad($especialidad);
		$this->setFechaIngreso($fecha_ingreso);		
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

	public function getApellido(){
		return $this->apellido;
	}

	public function setApellido($apellido){
		$this->apellido = $apellido;
	}

	public function getDocumento(){
		return $this->documento;
	}

	public function setDocumento($documento){
		$this->documento = $documento;
	}

	public function getTipoDocumento(){
		return $this->tipo_documento;
	}

	public function setTipoDocumento($tipo_documento){
		$this->tipo_documento = $tipo_documento;
	}

	public function getEmail(){
		return $this->email;
	}

	public function setEmail($email){
		$this->email = $email;
	}

	public function getTelefono(){
		return $this->telefono;
	}

	public function setTelefono($telefono){
		$this->telefono = $telefono;
	}

	public function getEspecialidad(){
		return $this->especialidad;
	}

	public function setEspecialidad($especialidad){
		$this->especialidad = $especialidad;
	}

	public function getFechaIngreso(){
		return $this->fecha_ingreso;
	}

	public function setFechaIngreso($fecha_ingreso){
		$this->fecha_ingreso = $fecha_ingreso;
	}

	public static function crear($datos){
		$db=Db::getConnect();

		$insert=$db->prepare('INSERT INTO instructores (nombre, apellido, documento, tipo_documento, email, telefono, especialidad, fecha_ingreso) VALUES (:nombre,:apellido,:documento,:tipo_documento,:email,:telefono,:especialidad,:fecha_ingreso)');
		$insert->bindValue('nombre',$datos['nombre']);
		$insert->bindValue('apellido',$datos['apellido']);
		$insert->bindValue('documento',$datos['documento']);
		$insert->bindValue('tipo_documento',$datos['tipo_documento'] ?? 'CC');
		$insert->bindValue('email',$datos['email']);
		$insert->bindValue('telefono',$datos['telefono']);
		$insert->bindValue('especialidad',$datos['especialidad']);
		$insert->bindValue('fecha_ingreso',$datos['fecha_ingreso'] ?? date('Y-m-d'));
		
		if ($insert->execute()) {
			return ['success' => true, 'id' => $db->lastInsertId()];
		}
		return ['success' => false, 'errores' => ['Error al insertar en la base de datos']];
	}

	public static function obtenerTodos(){
		return self::all();
	}

	public static function all(){
		$db=Db::getConnect();
		$listaInstructores=[];

		$select=$db->query('SELECT * FROM instructores order by id');

		foreach($select->fetchAll() as $instructor){
			$listaInstructores[]=new Instructor($instructor['id'],$instructor['nombre'],$instructor['apellido'],$instructor['documento'],$instructor['tipo_documento'],$instructor['email'],$instructor['telefono'],$instructor['especialidad'],$instructor['fecha_ingreso']);
		}
		return $listaInstructores;
	}

	public static function obtenerPorId($id){
		return self::searchById($id);
	}

	public static function searchById($id){
		$db=Db::getConnect();
		$select=$db->prepare('SELECT * FROM instructores WHERE id=:id');
		$select->bindValue('id',$id);
		$select->execute();

		$instructorDb=$select->fetch();

		$instructor = new Instructor ($instructorDb['id'],$instructorDb['nombre'], $instructorDb['apellido'], $instructorDb['documento'], $instructorDb['tipo_documento'], $instructorDb['email'], $instructorDb['telefono'], $instructorDb['especialidad'], $instructorDb['fecha_ingreso']);

		return $instructor;

	}

	public static function actualizar($id, $datos){
		$db=Db::getConnect();
		$update=$db->prepare('UPDATE instructores SET nombre=:nombre, apellido=:apellido, documento=:documento, tipo_documento=:tipo_documento, email=:email, telefono=:telefono, especialidad=:especialidad, fecha_ingreso=:fecha_ingreso WHERE id=:id');
		$update->bindValue('nombre', $datos['nombre']);
		$update->bindValue('apellido',$datos['apellido']);
		$update->bindValue('documento',$datos['documento'] ?? '');
		$update->bindValue('tipo_documento',$datos['tipo_documento'] ?? 'CC');
		$update->bindValue('email',$datos['email']);
		$update->bindValue('telefono',$datos['telefono']);
		$update->bindValue('especialidad',$datos['especialidad']);
		$update->bindValue('fecha_ingreso',$datos['fecha_ingreso'] ?? date('Y-m-d'));
		$update->bindValue('id',$id);
		
		return ['success' => $update->execute()];
	}

	public static function update($instructor){
		$db=Db::getConnect();
		$update=$db->prepare('UPDATE instructores SET nombre=:nombre, apellido=:apellido, documento=:documento, tipo_documento=:tipo_documento, email=:email, telefono=:telefono, especialidad=:especialidad, fecha_ingreso=:fecha_ingreso WHERE id=:id');
		$update->bindValue('nombre', $instructor->getNombre());
		$update->bindValue('apellido',$instructor->getApellido());
		$update->bindValue('documento',$instructor->getDocumento());
		$update->bindValue('tipo_documento',$instructor->getTipoDocumento());
		$update->bindValue('email',$instructor->getEmail());
		$update->bindValue('telefono',$instructor->getTelefono());
		$update->bindValue('especialidad',$instructor->getEspecialidad());
		$update->bindValue('fecha_ingreso',$instructor->getFechaIngreso());
		$update->bindValue('id',$instructor->getId());
		$update->execute();
	}

	public static function delete($id){
		$db=Db::getConnect();
		$delete=$db->prepare('DELETE  FROM instructores WHERE id=:id');
		$delete->bindValue('id',$id);
		$delete->execute();		
	}
}

?>
