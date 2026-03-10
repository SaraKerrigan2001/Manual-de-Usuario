<?php

require_once('Modelo/Administrador.php');
require_once('Modelo/Sede.php');
require_once('Modelo/Instructor.php');
require_once('Modelo/Ficha.php');
require_once('Modelo/Aprendiz.php');
require_once('Modelo/Ambiente.php');

class AdministradorController {

    public function index() {
        $this->dashboard();
    }

    public function dashboard() {
        // Incluir sistema de contexto de rol
        require_once __DIR__ . '/../api/role_context.php';

        if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
            header('Location: index.php?controlador=Auth&accion=login');
            exit;
        }

        inicializarContextoRol('administrador');
        $infoUsuario = obtenerInfoUsuarioRol('administrador');

        $sedes = Administrador::obtenerSedes();
        $instructores = Administrador::obtenerInstructores();
        $fichas = Administrador::obtenerFichas();
        $aprendices = Administrador::obtenerAprendices();

        require_once('Vista/Administrador/dashboard.php');
    }

    public function centroFormacion() {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
            header('Location: index.php?controlador=Auth&accion=login');
            exit;
        }
        require_once('Vista/Administrador/CentroFormacion/index.php');
    }

    public function gestionFichas() {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
            header('Location: index.php?controlador=Auth&accion=login');
            exit;
        }
        require_once('Modelo/Ficha.php');
        $fichas = Ficha::all();
        $sedes = Administrador::obtenerSedes();
        require_once('Vista/Administrador/CentroFormacion/fichas.php');
    }

    public function gestionProgramas() {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
            header('Location: index.php?controlador=Auth&accion=login');
            exit;
        }
        require_once('Modelo/Programa.php');
        $programas = Programa::all();
        require_once('Vista/Administrador/CentroFormacion/programas.php');
    }

    public function gestionCompetencias() {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
            header('Location: index.php?controlador=Auth&accion=login');
            exit;
        }
        require_once('Modelo/Competencia.php');
        $competencias = Competencia::all();
        require_once('Vista/Administrador/CentroFormacion/competencias.php');
    }

    public function gestionSedes() {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
            header('Location: index.php?controlador=Auth&accion=login');
            exit;
        }
        require_once('Modelo/Sede.php');
        $sedes = Sede::all();
        require_once('Vista/Administrador/CentroFormacion/sedes.php');
    }

    public function gestionAmbientes() {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
            header('Location: index.php?controlador=Auth&accion=login');
            exit;
        }
        require_once('Modelo/Ambiente.php');
        $ambientes = Ambiente::all();
        $sedes = Administrador::obtenerSedes();
        require_once('Vista/Administrador/CentroFormacion/ambientes.php');
    }

    public function gestionInstructores() {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
            header('Location: index.php?controlador=Auth&accion=login');
            exit;
        }
        require_once('Modelo/Instructor.php');
        $instructores = Instructor::all();
        require_once('Vista/Administrador/CentroFormacion/instructores.php');
    }

    public function gestionAsignaciones() {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
            header('Location: index.php?controlador=Auth&accion=login');
            exit;
        }
        
        // Obtener datos necesarios para las asignaciones
        require_once('Modelo/Instructor.php');
        $instructores = Instructor::all();
        
        require_once('Modelo/Ficha.php');
        $fichas = Ficha::all();
        
        // Obtener experiencias/competencias
        require_once('Modelo/Experiencia.php');
        $experiencias = Experiencia::all();
        
        // Obtener ambientes
        require_once('Modelo/Ambiente.php');
        $ambientes = Ambiente::all();
        
        require_once('Vista/Administrador/asignaciones.php');
    }

    public function crearAsignacion() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Db::getConnect();
            
            $stmt = $db->prepare("
                INSERT INTO asignaciones 
                (ficha_id, instructor_id, experiencia_id, ambiente_id, fecha_inicio, fecha_fin, hora_inicio, hora_fin, dias_semana, estado) 
                VALUES (:ficha_id, :instructor_id, :experiencia_id, :ambiente_id, :fecha_inicio, :fecha_fin, :hora_inicio, :hora_fin, :dias_semana, :estado)
            ");
            
            $stmt->execute([
                ':ficha_id' => $_POST['ficha_id'],
                ':instructor_id' => $_POST['instructor_id'],
                ':experiencia_id' => $_POST['experiencia_id'],
                ':ambiente_id' => $_POST['ambiente_id'] ?? null,
                ':fecha_inicio' => $_POST['fecha_inicio'],
                ':fecha_fin' => $_POST['fecha_fin'],
                ':hora_inicio' => $_POST['hora_inicio'] ?? null,
                ':hora_fin' => $_POST['hora_fin'] ?? null,
                ':dias_semana' => $_POST['dias_semana'] ?? null,
                ':estado' => $_POST['estado'] ?? 'Programada'
            ]);
        }
        header('Location: index.php?controlador=Administrador&accion=gestionAsignaciones');
    }

    public function eliminarAsignacion() {
        if (isset($_GET['id'])) {
            $db = Db::getConnect();
            $stmt = $db->prepare("DELETE FROM asignaciones WHERE asignacion_id = :id");
            $stmt->execute([':id' => $_GET['id']]);
        }
        header('Location: index.php?controlador=Administrador&accion=gestionAsignaciones');
    }

    // Métodos de eliminación
    public function eliminarSede() {
        if (isset($_GET['id'])) {
            Administrador::eliminarSede($_GET['id']);
        }
        header('Location: index.php?controlador=Administrador&accion=gestionSedes');
    }

    public function eliminarInstructor() {
        if (isset($_GET['id'])) {
            Administrador::eliminarInstructor($_GET['id']);
        }
        header('Location: index.php?controlador=Administrador&accion=gestionInstructores');
    }

    public function eliminarFicha() {
        if (isset($_GET['id'])) {
            Administrador::eliminarFicha($_GET['id']);
        }
        header('Location: index.php?controlador=Administrador&accion=gestionFichas');
    }

    public function eliminarAprendiz() {
        if (isset($_GET['id'])) {
            Administrador::eliminarAprendiz($_GET['id']);
        }
        header('Location: index.php?controlador=Administrador&accion=dashboard');
    }

    public function eliminarAmbiente() {
        if (isset($_GET['id'])) {
            Administrador::eliminarAmbiente($_GET['id']);
        }
        header('Location: index.php?controlador=Administrador&accion=gestionAmbientes');
    }

    public function eliminarPrograma() {
        if (isset($_GET['id'])) {
            require_once('Modelo/Programa.php');
            Programa::eliminar($_GET['id']);
        }
        header('Location: index.php?controlador=Administrador&accion=gestionProgramas');
    }

    public function eliminarCompetencia() {
        if (isset($_GET['id'])) {
            require_once('Modelo/Competencia.php');
            Competencia::eliminar($_GET['id']);
        }
        header('Location: index.php?controlador=Administrador&accion=gestionCompetencias');
    }

    // Métodos de Creación (Add)
    public function addSede() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Administrador::crearSede($_POST);
        }
        header('Location: index.php?controlador=Administrador&accion=gestionSedes');
    }

    public function addAmbiente() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once('Modelo/Ambiente.php');
            $sede_id = $_POST['sede'] ?? $_POST['sede_id'] ?? null;
            $ambiente = new Ambiente(
                null,
                $sede_id,
                $_POST['nombre'],
                $_POST['capacidad'],
                $_POST['tipo'],
                $_POST['estado'] ?? 'Disponible'
            );
            Ambiente::save($ambiente);
        }
        header('Location: index.php?controlador=Administrador&accion=gestionAmbientes');
    }

    public function addPrograma() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once('Modelo/Programa.php');
            Programa::crear($_POST);
        }
        header('Location: index.php?controlador=Administrador&accion=gestionProgramas');
    }

    public function addCompetencia() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once('Modelo/Competencia.php');
            Competencia::crear($_POST);
        }
        header('Location: index.php?controlador=Administrador&accion=gestionCompetencias');
    }

    public function addFicha() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once('Modelo/Ficha.php');
            Ficha::crear($_POST);
        }
        header('Location: index.php?controlador=Administrador&accion=gestionFichas');
    }

    public function addInstructor() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Lógica para añadir instructor si fuera necesario desde el modelo Administratrador
        }
        header('Location: index.php?controlador=Administrador&accion=gestionInstructores');
    }

    // Métodos de Actualización (Update)
    public function actualizarSede() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            Administrador::editarSede($_POST['id'], $_POST);
        }
        header('Location: index.php?controlador=Administrador&accion=gestionSedes');
    }

    public function actualizarAmbiente() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            require_once('Modelo/Ambiente.php');
            $sede_id = $_POST['sede'] ?? $_POST['sede_id'] ?? null;
            $ambiente = new Ambiente(
                $_POST['id'],
                $sede_id,
                $_POST['nombre'],
                $_POST['capacidad'],
                $_POST['tipo'],
                $_POST['estado'] ?? 'Disponible'
            );
            Ambiente::update($ambiente);
        }
        header('Location: index.php?controlador=Administrador&accion=gestionAmbientes');
    }

    public function actualizarInstructor() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            Administrador::actualizarInstructor($_POST['id'], $_POST['nombre'], $_POST['apellido'], $_POST['especialidad'] ?? '', $_POST['email'] ?? '');
        }
        header('Location: index.php?controlador=Administrador&accion=gestionInstructores');
    }

    public function actualizarFicha() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            Administrador::actualizarFicha($_POST['id'], $_POST['codigo'], $_POST['programa'], $_POST['sede_id'], $_POST['fecha_inicio'] ?? null, $_POST['fecha_fin'] ?? null);
        }
        header('Location: index.php?controlador=Administrador&accion=gestionFichas');
    }

    public function perfil() {
        // Lógica de perfil
        require_once('Vista/Administrador/perfil.php');
    }
}
