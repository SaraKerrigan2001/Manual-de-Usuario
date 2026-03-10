<?php

class UsuarioController {
    
    /**
     * Dashboard del usuario
     */
    public function dashboard() {
        // Verificar autenticación
        if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'usuario') {
            header('Location: index.php?controlador=Auth&accion=login');
            exit;
        }
        
        require_once('Vista/Usuario/dashboard.php');
    }
    
    /**
     * Ver perfil del usuario
     */
    public function perfil() {
        // Verificar autenticación
        if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'usuario') {
            header('Location: index.php?controlador=Auth&accion=login');
            exit;
        }
        
        require_once('Vista/Usuario/perfil.php');
    }
}
