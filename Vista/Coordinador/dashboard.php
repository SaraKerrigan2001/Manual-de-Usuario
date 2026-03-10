<?php
// Mantener sesión y usuario para el dashboard
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$_SESSION['email'] = $_SESSION['email'] ?? 'coordinador@sena.edu.co';
$_SESSION['rol'] = $_SESSION['rol'] ?? 'administrador'; // Establecer rol para el API
$_SESSION['usuario_id'] = $_SESSION['usuario_id'] ?? 1; // Establecer usuario_id para el API

// Valores útiles para mostrar en el perfil
$user_name = $_SESSION['user_name'] ?? $_SESSION['nombre'] ?? 'Coordinador';
// Forzar el rol a Coordinador para consistencia visual
$user_role = 'Coordinador';
$user_sede = $_SESSION['sede'] ?? 'Sede Principal';

// Iniciales para el avatar
$initials = '';
$parts = preg_split('/\s+/', trim($user_name));
if (count($parts) >= 2) {
    $initials = strtoupper(substr($parts[0],0,1) . substr($parts[1],0,1));
} else {
    $initials = strtoupper(substr($parts[0],0,1));
}

// Cargar datos desde la base de datos
require_once('connection.php');

// Obtener instructores
$instructores = [];
try {
    $db = Db::getConnect();
    $stmt = $db->query("SELECT id, nombre, apellido, especialidad, '' as carga, 'Activo' as estado FROM instructores ORDER BY nombre");
    $instructores = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $instructores = [];
}

// Obtener fichas
$fichas = [];
try {
    $stmt = $db->query("SELECT ficha_id, codigo_ficha, programa, 0 as num_aprendices FROM fichas WHERE estado = 'Activa' ORDER BY codigo_ficha");
    $fichas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $fichas = [];
}

// Obtener sedes
$sedes = [];
try {
    $stmt = $db->query("SELECT sede_id, nombre_sede FROM sedes ORDER BY nombre_sede");
    $sedes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $sedes = [];
}

// Obtener ambientes
$ambientes = [];
try {
    $stmt = $db->query("SELECT ambiente_id, nombre_ambiente, capacidad, tipo FROM ambientes WHERE estado = 'Disponible' ORDER BY nombre_ambiente");
    $ambientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $ambientes = [];
}

// Obtener programas
$programasList = [];
try {
    $stmt = $db->query("SELECT programa_id, codigo, nombre, nivel, duracion_meses FROM programas ORDER BY nombre");
    $programasList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $programasList = [];
}

// Obtener competencias
$competenciasList = [];
try {
    $stmt = $db->query("SELECT competencia_id, codigo, descripcion, horas, tipo FROM competencias ORDER BY descripcion");
    $competenciasList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $competenciasList = [];
}

// Calcular estadísticas
$stats = [
    'instructores' => ['total' => count($instructores)],
    'fichas' => ['total' => count($fichas)],
    'sedes' => ['total' => count($sedes)],
    'ambientes' => ['total' => count($ambientes)],
    'programas' => ['total' => count($programasList)],
    'competencias' => ['total' => count($competenciasList)]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Coordinador SENA - Gestión Integral</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Toastify for toasts -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <!-- FullCalendar CSS v5 (versión estable) -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css" rel="stylesheet" />
    <!-- Sistema de Contexto de Rol -->
    <script src="assets/js/role_context.js" defer></script>
    <style>
        :root { 
            /* Verde Principal */
            --green-400: #34d399;
            --green-500: #10b981;
            --green-600: #059669;
            --green-700: #047857;
            
            /* Light theme colors */
            --bg-primary: #ffffff;
            --bg-secondary: #f9fafb;
            --bg-tertiary: #f3f4f6;
            --bg-sidebar: #f9fafb;
            --text-primary: #111827;
            --text-secondary: #374151;
            --text-tertiary: #6b7280;
            --border-color: #e5e7eb;
            --border-secondary: #d1d5db;
            --card-bg: #ffffff;
            --card-shadow: rgba(0,0,0,0.04);
            --green-shadow: rgba(16, 185, 129, 0.05);
        }
        
        [data-theme="dark"] {
            /* Dark theme colors */
            --bg-primary: #000000;
            --bg-secondary: #030712;
            --bg-tertiary: #111827;
            --bg-sidebar: #030712;
            --text-primary: #ffffff;
            --text-secondary: #d1d5db;
            --text-tertiary: #9ca3af;
            --border-color: #374151;
            --border-secondary: #4b5563;
            --card-bg: #111827;
            --card-shadow: rgba(0,0,0,0.5);
            --green-shadow: rgba(16, 185, 129, 0.25);
        }
        
        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        /* Dark theme specific styles */
        [data-theme="dark"] .bg-white {
            background-color: var(--card-bg) !important;
        }
        
        [data-theme="dark"] .bg-gray-50 {
            background-color: var(--bg-tertiary) !important;
        }
        
        [data-theme="dark"] .bg-gray-100 {
            background-color: var(--bg-secondary) !important;
        }
        
        [data-theme="dark"] .text-slate-800,
        [data-theme="dark"] .text-gray-800,
        [data-theme="dark"] .text-gray-900 {
            color: var(--text-primary) !important;
        }
        
        [data-theme="dark"] .text-gray-700,
        [data-theme="dark"] .text-gray-600 {
            color: var(--text-secondary) !important;
        }
        
        [data-theme="dark"] .text-gray-500 {
            color: var(--text-tertiary) !important;
        }
        
        [data-theme="dark"] .border-gray-100,
        [data-theme="dark"] .border-gray-200 {
            border-color: var(--border-color) !important;
        }
        
        [data-theme="dark"] .shadow-sm {
            box-shadow: 0 1px 2px 0 var(--card-shadow) !important;
        }
        
        [data-theme="dark"] input,
        [data-theme="dark"] select,
        [data-theme="dark"] textarea {
            background-color: var(--bg-tertiary) !important;
            color: var(--text-primary) !important;
            border-color: var(--border-color) !important;
        }
        
        [data-theme="dark"] .sidebar {
            background: var(--bg-sidebar) !important;
            border-right: 1px solid var(--border-color);
        }
        
        [data-theme="dark"] .nav-item i {
            background: var(--bg-tertiary) !important;
        }
        
        [data-theme="dark"] .nav-item.active i {
            background: var(--green-500) !important;
        }
        
        /* Global green button styles */
        .bg-green-500 { background-color: #10b981 !important; }
        .hover\:bg-green-600:hover { background-color: #059669 !important; }
        .hover\:bg-green-700:hover { background-color: #047857 !important; }
        .text-green-500 { color: #10b981 !important; }
        .border-green-500 { border-color: #10b981 !important; }
        .focus\:ring-green-500:focus { --tw-ring-color: #10b981 !important; }
        
        /* Update old green colors to new green */
        input:focus, select:focus, textarea:focus {
            --tw-ring-color: #10b981 !important;
        }
        
        /* Sections */
            .view-section { display: none; }
        .view-section.active { display: block; }

        /* Navigation active */
        .nav-item.active { background-color: white; color: #10b981; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }

        /* Modal styles */
        .modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.75); }
        .modal.active { display: flex; align-items: center; justify-content: center; }
        .modal-content { 
            background-color: var(--card-bg); 
            border-radius: 20px; 
            padding: 24px; 
            max-width: 600px; 
            width: 90%; 
            max-height: 90vh; 
            overflow-y: auto; 
            border: 1px solid var(--border-color);
            /* Ocultar scrollbar pero mantener funcionalidad */
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE and Edge */
        }
        
        /* Ocultar scrollbar en Chrome, Safari y Opera */
        .modal-content::-webkit-scrollbar {
            display: none;
        }
        
        /* Dark theme modal styles */
        [data-theme="dark"] .modal-content {
            background-color: var(--card-bg);
            border-color: var(--border-color);
        }
        
        [data-theme="dark"] .modal-content h2 {
            color: var(--text-primary) !important;
        }
        
        [data-theme="dark"] .modal-content label {
            color: var(--text-secondary) !important;
        }
        
        [data-theme="dark"] .modal-content input[type="text"],
        [data-theme="dark"] .modal-content input[type="email"],
        [data-theme="dark"] .modal-content input[type="number"],
        [data-theme="dark"] .modal-content input[type="date"],
        [data-theme="dark"] .modal-content input[type="time"],
        [data-theme="dark"] .modal-content select,
        [data-theme="dark"] .modal-content textarea {
            background-color: var(--bg-tertiary) !important;
            color: var(--text-primary) !important;
            border-color: var(--border-color) !important;
        }
        
        [data-theme="dark"] .modal-content input[type="checkbox"] {
            background-color: var(--bg-tertiary);
            border-color: var(--border-color);
        }
        
        [data-theme="dark"] .modal-content input[type="checkbox"]:checked {
            background-color: var(--green-500);
            border-color: var(--green-500);
        }
        
        [data-theme="dark"] .modal-content .flex.items-center.gap-2 {
            border-color: var(--border-color) !important;
        }
        
        [data-theme="dark"] .modal-content .flex.items-center.gap-2:hover {
            background-color: var(--bg-tertiary) !important;
        }
        
        [data-theme="dark"] .modal-content .text-gray-500 {
            color: var(--text-tertiary) !important;
        }
        
        [data-theme="dark"] .modal-content button.border {
            background-color: var(--bg-tertiary);
            border-color: var(--border-color);
            color: var(--text-secondary);
        }
        
        [data-theme="dark"] .modal-content button.border:hover {
            background-color: var(--bg-secondary);
        }

        /* Sidebar collapse behavior */
        .sidebar { position: relative; width: 288px; transition: width 200ms ease; padding-bottom: 80px; background: var(--bg-sidebar); box-shadow: 4px 0 10px var(--card-shadow); border-right: 1px solid var(--border-color); }
        .sidebar.collapsed { width: 72px; }
        .sidebar .nav-item { display: flex; align-items: center; gap: 12px; }
        .sidebar .nav-item i { min-width: 20px; text-align: center; }
        .sidebar.collapsed .nav-item { justify-content: center; padding-left: 0; padding-right: 0; }
        .sidebar.collapsed .nav-item span { display: none; }
        .sidebar.collapsed p { display: none; }

        /* Toggle button placement and style */
        .sidebar .toggle-btn { position: absolute; right: 12px; top: 12px; z-index: 60; }
        .sidebar .toggle-btn button { background: var(--card-bg); border-radius: 9999px; padding: 8px; box-shadow: 0 4px 8px var(--card-shadow); border: 1px solid var(--border-color); display:flex; align-items:center; justify-content:center; width:36px; height:36px; }
        .sidebar .toggle-btn i { transition: transform 160ms ease; color: var(--green-500); }
        .sidebar.collapsed .toggle-btn i { transform: rotate(180deg); }
        .sidebar.collapsed .toggle-btn { right: -18px; }
        .sidebar.collapsed .toggle-btn button { width:44px; height:44px; }

        /* When collapsed, make icons more prominent */
        .sidebar.collapsed i { font-size: 18px; }

        /* Menu icon background (white pills) */
        .nav-item i { display:inline-block; background: var(--card-bg); padding: 10px; border-radius: 12px; box-shadow: 0 6px 18px var(--green-shadow); border: 1px solid var(--border-color); }
        .nav-item.active i { background: var(--green-500); color: white !important; box-shadow: 0 10px 30px var(--green-shadow); border-color: var(--green-500); }
        /* nav text color */
        .nav-item { color: var(--text-secondary) !important; }
        .nav-item:hover { background: rgba(16,185,129,0.06); color: var(--text-primary) !important; transform: translateX(4px); }
        .nav-item.active { background: transparent; color: var(--green-500) !important; border-left: 4px solid var(--green-500); padding-left: 16px; }
        .nav-item.active i { background: var(--green-500); color: white !important; box-shadow: 0 10px 30px var(--green-shadow); border-color: var(--green-500); }
        .nav-item.active-green { background: var(--green-500); color: white !important; border-left: none; padding-left: 20px; border-radius: 12px; }
        .nav-item.active-green i { background: rgba(255,255,255,0.2); color: white !important; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-color: rgba(255,255,255,0.3); }
        .nav-item:hover i { transform: translateY(-2px); }

        /* Hide/center logo text when collapsed */
        .sidebar .logo-container { transition: all 160ms ease; }
        .sidebar .logo-text { transition: opacity 160ms ease; }
        .sidebar .logo-icon { transition: all 160ms ease; }
        
        /* When collapsed: hide text, center container, make icon circular */
        .sidebar.collapsed .logo-text { opacity: 0; display: none; }
        .sidebar.collapsed .logo-container { justify-content: center; padding: 12px; gap: 0; }
        .sidebar.collapsed .logo-icon { 
            width: 44px; 
            height: 44px; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            padding: 0;
            border-radius: 50% !important;
            box-shadow: 0 6px 18px rgba(16, 185, 129, 0.25);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        .sidebar.collapsed .logo-icon i {
            font-size: 20px;
        }

        /* Profile box adjustments */
        .profile-box { transition: all 160ms ease; }
        /* Place profile box at bottom of sidebar like the screenshots */
        .sidebar .profile-box { position: absolute; left: 12px; right: 12px; bottom: 25px; }
        .sidebar .profile-box .profile-card { padding: 8px; transition: all 160ms ease; }
        
        /* When collapsed: show only avatar, hide text and menu button */
        .sidebar.collapsed .profile-box { padding: 6px; display:flex; justify-content:center; left: 8px; right: 8px; }
        .sidebar.collapsed .profile-box .profile-card { 
            min-width: auto !important; 
            padding: 0 !important; 
            background: transparent !important; 
            box-shadow: none !important; 
        }
        .sidebar.collapsed .profile-box .profile-content { gap: 0; }
        .sidebar.collapsed .profile-box .profile-info { display: none !important; }
        .sidebar.collapsed .profile-box #profileToggle,
        .sidebar.collapsed .profile-box .profile-menu-btn { display: none !important; }
        .sidebar.collapsed .profile-box .avatar-outer { 
            padding: 0 !important; 
            width: 48px !important; 
            height: 48px !important; 
            box-shadow: 0 6px 18px rgba(16, 185, 129, 0.25);
        }
        .sidebar.collapsed .profile-box .profile-avatar { 
            width: 48px !important; 
            height: 48px !important; 
            font-size: 20px !important; 
        }

        /* Avatar outer white rounded square and inner green circle */
        .profile-box .avatar-outer { background: var(--card-bg); padding: 6px; border-radius: 12px; box-shadow: 0 6px 18px var(--green-shadow); display:flex; align-items:center; width:44px; height:44px; justify-content:center; border: 1px solid var(--border-color); }
        .profile-avatar { background: transparent; color: var(--green-500); width:34px; height:34px; border-radius: 50%; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:16px; }

        /* Profile menu style (card) */
        #profileMenu { box-shadow: 0 6px 20px rgba(0,0,0,0.12); }
        #profileMenu a, #profileMenu button { display:block; padding: 10px 14px; text-align:left; width:100%; border:none; background:transparent; }

        /* Small three-dot button style */
        #profileToggle { background: transparent; border: none; padding: 6px; border-radius: 8px; }
        /* Make section title prominent and green like reference */
        #section-title { color: var(--sena-verde-principal) !important; font-size: 34px !important; font-weight: 800 !important; }

        /* Override profile menu position to align with bottom card */
        #profileMenu { left: 16px !important; bottom: 76px !important; width: 220px !important; box-shadow: 0 6px 20px rgba(0,0,0,0.12); }

        /* Avatar outer white rounded square and inner green circle (original size) */
        .avatar-outer { background: var(--card-bg); padding: 6px; border-radius: 12px; box-shadow: 0 6px 18px var(--green-shadow); display:flex; align-items:center; border: 1px solid var(--border-color); }
        .avatar { background: var(--green-500); color: white; width:40px; height:40px; border-radius: 50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:16px; }

        /* Ensure main content reflows smoothly */
        main { transition: margin-left 200ms ease; }

        /* Hide scrollbar in the sidebar navigation */
        /* Chrome, Safari and Opera */
        .sidebar .custom-scrollbar::-webkit-scrollbar { display: none; }
        /* IE, Edge */
        .sidebar .custom-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        /* Calendar container (compact & centered like PC) */
        #calendarEl { background: var(--card-bg); border-radius: 12px; padding: 12px; box-shadow: 0 2px 8px var(--card-shadow); overflow:hidden; max-width:600px; margin: 0 auto; min-height: 400px; border: 1px solid var(--border-color); }
        /* Ocultar barras de desplazamiento en el calendario */
        #calendarEl .fc-scroller { overflow: visible !important; }
        #calendarEl .fc-daygrid-body { overflow: visible !important; }
        #calendarEl .fc-scroller::-webkit-scrollbar { display: none; }
        #calendarEl .fc-scroller { -ms-overflow-style: none; scrollbar-width: none; }
        /* Asegurar que el calendario se vea completo */
        .fc { width: 100%; }
        
        /* Dark theme calendar styles */
        [data-theme="dark"] #calendarEl {
            background: var(--card-bg);
            border-color: var(--border-color);
        }
        
        [data-theme="dark"] .fc { color: var(--text-primary); }
        [data-theme="dark"] .fc .fc-toolbar-title { color: var(--text-primary); }
        [data-theme="dark"] .fc .fc-col-header-cell { background: var(--bg-tertiary); color: var(--text-secondary); border-color: var(--border-color); }
        [data-theme="dark"] .fc .fc-daygrid-day { background: var(--card-bg); border-color: var(--border-color); }
        [data-theme="dark"] .fc .fc-daygrid-day-number { color: var(--text-primary); }
        [data-theme="dark"] .fc .fc-button { background: var(--bg-tertiary); color: var(--text-primary); border-color: var(--border-color); }
        [data-theme="dark"] .fc .fc-button:hover { background: var(--bg-secondary); }
        [data-theme="dark"] .fc .fc-button-primary:not(:disabled).fc-button-active { background: var(--green-500); border-color: var(--green-500); }
        [data-theme="dark"] .fc-theme-standard td, [data-theme="dark"] .fc-theme-standard th { border-color: var(--border-color); }
        
        /* Dark theme - Sábados y domingos uniformes con el resto del calendario */
        [data-theme="dark"] .fc .fc-day-sat { background-color: var(--card-bg) !important; opacity: 1; }
        [data-theme="dark"] .fc .fc-day-sat .fc-daygrid-day-number { color: var(--text-primary) !important; }
        [data-theme="dark"] .fc .fc-day-sun { background-color: var(--card-bg) !important; opacity: 1; }
        [data-theme="dark"] .fc .fc-day-sun .fc-daygrid-day-number { color: var(--text-primary) !important; }
        
        /* Dark theme - Sábados y domingos en vista de semana y día */
        [data-theme="dark"] .fc .fc-timegrid-col.fc-day-sat { background-color: var(--card-bg) !important; }
        [data-theme="dark"] .fc .fc-timegrid-col.fc-day-sun { background-color: var(--card-bg) !important; }
        [data-theme="dark"] .fc .fc-col-header-cell.fc-day-sat { background-color: var(--bg-tertiary) !important; }
        [data-theme="dark"] .fc .fc-col-header-cell.fc-day-sun { background-color: var(--bg-tertiary) !important; }
        
        /* FullCalendar compact toolbar/title/buttons */
        .fc .fc-toolbar-title { font-size: 16px; font-weight:700; text-align:center; }
        .fc .fc-button { padding: 4px 8px; font-size: 11px; border-radius:6px; }
        .fc .fc-button.fc-prev-button, .fc .fc-button.fc-next-button { background: var(--green-500); color:#fff; border: none; }
        .fc .fc-button.fc-today-button { background: var(--green-500); color:#fff; border: none; }
        .fc .fc-daygrid-day-top { padding: 2px 4px; font-size: 11px; }
        .fc .fc-col-header-cell-cushion { padding: 3px; font-size: 11px; }
        .fc .fc-scrollgrid-section { border-radius:6px; }
        .fc .fc-daygrid-event { font-size:10px; padding:1px 3px; border-radius:3px; }
        .fc .fc-daygrid-day-frame { min-height: 45px; }
        .fc .fc-toolbar { margin-bottom: 8px; }
        .fc .fc-toolbar-chunk { display: flex; align-items: center; gap: 4px; }
        /* Atenuar sábados (rara vez hay clases) */
        .fc .fc-day-sat { background-color: #f9f9f9; opacity: 0.7; }
        .fc .fc-day-sat .fc-daygrid-day-number { color: #999; }
        /* Resaltar domingos (no hay clases) */
        .fc .fc-day-sun { background-color: #f5f5f5; opacity: 0.5; }
        .fc .fc-day-sun .fc-daygrid-day-number { color: #ccc; }
        /* Horario de negocio (días laborables) */
        .fc .fc-non-business { background-color: #f8f8f8; }
        
        /* Dark theme - Eliminar todos los fondos claros en vistas de semana/día */
        [data-theme="dark"] .fc .fc-non-business { background-color: var(--card-bg) !important; }
        [data-theme="dark"] .fc .fc-timegrid-slot { background-color: var(--card-bg) !important; }
        [data-theme="dark"] .fc .fc-timegrid-col { background-color: var(--card-bg) !important; }
        [data-theme="dark"] .fc .fc-timegrid-body { background-color: var(--card-bg) !important; }
        [data-theme="dark"] .fc .fc-scrollgrid-sync-inner { background-color: var(--card-bg) !important; }
        [data-theme="dark"] .fc .fc-timegrid-slot-lane { background-color: var(--card-bg) !important; }
        [data-theme="dark"] .fc .fc-timegrid-divider { background-color: var(--border-color) !important; }

        /* Notification drawer green style (right) */
        #notifMenu { position: absolute; right: 12px; top: 56px; z-index: 1100; width: 380px; max-height: 90vh; overflow-y: auto; }
        #notifMenu.hidden { display: none; }
        #notifMenu .notif-card { background: linear-gradient(135deg,#10b981,#059669); color: #fff; border-radius: 0; box-shadow: 0 10px 30px rgba(16,185,129,0.25); overflow: hidden; }
        #notifMenu .notif-card .head { padding: 16px 18px; display:flex; justify-content:space-between; align-items:center; font-weight:700; font-size: 16px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        #notifMenu .notif-list { max-height:400px; overflow:auto; padding: 8px; }
        #notifMenu .notif-item { background: rgba(255,255,255,0.1); margin-bottom:8px; padding:12px; border-radius:0; transition: all 0.2s; cursor: pointer; }
        #notifMenu .notif-item:hover { background: rgba(255,255,255,0.15); transform: translateX(-2px); }
        #notifMenu .notif-list::-webkit-scrollbar { width: 6px; }
        #notifMenu .notif-list::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); border-radius: 10px; }
        #notifMenu .notif-list::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }
        #notifMenu .notif-list::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.3); }
        
        /* Centrar notificaciones en móviles */
        @media (max-width: 768px) {
            #notifMenu {
                position: fixed !important;
                left: 50% !important;
                top: 50% !important;
                transform: translate(-50%, -50%) !important;
                right: auto !important;
                width: 90vw !important;
                max-width: 380px !important;
                max-height: 80vh !important;
                z-index: 1100 !important;
            }
            
            /* Overlay oscuro detrás del menú de notificaciones en móvil */
            #notifMenu:not(.hidden)::before {
                content: '';
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.6);
                z-index: -1;
            }
        }
        
        /* Table row hover effect with green */
        table tbody tr { transition: all 0.2s ease; cursor: pointer; }
        table tbody tr:hover { background-color: rgba(16, 185, 129, 0.08) !important; }
        [data-theme="dark"] table tbody tr:hover { background-color: rgba(16, 185, 129, 0.15) !important; }
        
        /* Responsive Design */
        @media (max-width: 1280px) {
            /* Ajustar grid de cards en pantallas medianas */
            .grid.grid-cols-1.md\:grid-cols-4 { grid-template-columns: repeat(2, 1fr) !important; }
            .grid.grid-cols-1.md\:grid-cols-2 { grid-template-columns: repeat(2, 1fr) !important; }
        }
        
        @media (max-width: 1024px) {
            /* Sidebar más estrecho en tablets */
            .sidebar { width: 240px; }
            .sidebar.collapsed { width: 72px; }
            
            /* Ajustar padding del main */
            main { padding: 1.5rem; }
            
            /* Header más compacto */
            header { flex-direction: column; gap: 1rem; align-items: flex-start !important; }
            header .flex.items-center.space-x-4 { width: 100%; justify-content: space-between; flex-wrap: wrap; }
            
            /* Ocultar algunos botones en tablets */
            #enviarNotifBtn span { display: none; }
            #enviarNotifBtn { padding: 0.5rem !important; }
        }
        
        @media (max-width: 768px) {
            /* En móviles, el sidebar inicia colapsado automáticamente */
            .sidebar { 
                position: fixed;
                left: 0;
                top: 0;
                height: 100vh;
                z-index: 999;
                width: 72px !important;
                transition: width 0.3s ease, box-shadow 0.3s ease;
            }
            
            /* Cuando se expande, se superpone al contenido */
            .sidebar:not(.collapsed) {
                width: 288px !important;
                box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
            }
            
            /* Main siempre con margen fijo para el sidebar colapsado */
            main { 
                margin-left: 72px !important; 
                padding: 1rem;
                width: calc(100% - 72px) !important;
            }
            
            /* Overlay oscuro cuando el sidebar está expandido */
            .sidebar:not(.collapsed)::before {
                content: '';
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: -1;
                animation: fadeIn 0.3s;
            }
            
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            
            /* Botón toggle siempre visible en móvil */
            .sidebar .toggle-btn {
                display: block !important;
                right: -18px !important;
                z-index: 1001;
            }
            
            .sidebar .toggle-btn button {
                width: 44px !important;
                height: 44px !important;
            }
            
            /* Ajustar icono del botón según estado */
            .sidebar.collapsed .toggle-btn i {
                transform: rotate(180deg);
            }
            
            /* Grid de una columna en móviles */
            .grid.grid-cols-1.md\:grid-cols-4,
            .grid.grid-cols-1.md\:grid-cols-2 { 
                grid-template-columns: 1fr !important; 
            }
            
            /* Header responsive */
            header { padding: 0; margin-bottom: 1.5rem; }
            header h1 { font-size: 1.5rem !important; }
            header .flex.items-center.space-x-4 { gap: 0.5rem; }
            header input[type="text"] { width: 100% !important; max-width: 200px; }
            
            /* Tablas responsive */
            .overflow-x-auto { overflow-x: auto; -webkit-overflow-scrolling: touch; }
            table { min-width: 800px; }
            
            /* Cards más compactas */
            .bg-white.p-6 { padding: 1rem !important; }
            .rounded-3xl { border-radius: 1rem !important; }
            
            /* Modal responsive */
            .modal-content { 
                width: 95% !important; 
                max-width: 95% !important; 
                padding: 1rem !important; 
                max-height: 85vh !important;
            }
            .modal-content h2 { font-size: 1.25rem !important; }
            .modal-content .grid.grid-cols-2 { grid-template-columns: 1fr !important; }
            
            /* Calendario más pequeño */
            #calendarEl { padding: 0.5rem; }
            .fc .fc-toolbar { flex-direction: column; gap: 0.5rem; }
            .fc .fc-toolbar-chunk { width: 100%; justify-content: center; }
            
            /* Notificaciones responsive */
            #notifMenu { 
                width: 90vw !important; 
                max-width: 320px !important; 
                right: 5vw !important; 
            }
            
            /* Ocultar texto de búsqueda en móviles pequeños */
            @media (max-width: 640px) {
                header input[type="text"]::placeholder { font-size: 0.75rem; }
            }
        }
        
        @media (max-width: 480px) {
            /* Ajustes para móviles muy pequeños */
            main { padding: 0.75rem; }
            header h1 { font-size: 1.25rem !important; }
            
            /* Stats cards más pequeñas */
            .bg-white.p-6 p.text-3xl { font-size: 1.5rem !important; }
            .bg-white.p-6 p.text-4xl { font-size: 2rem !important; }
            
            /* Botones más pequeños */
            button { font-size: 0.875rem !important; padding: 0.5rem 0.75rem !important; }
            
            /* Tabla con scroll horizontal */
            table { font-size: 0.75rem; }
            table th, table td { padding: 0.5rem !important; }
        }
    </style>
    <!-- Scripts de FullCalendar v5 (versión estable) - Cargar antes que el código del calendario -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/locales/es.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <style>
        /* Ocultar scrollbar en todo el body y main */
        body::-webkit-scrollbar,
        main::-webkit-scrollbar {
            display: none;
        }
        
        body, main {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        
        /* Asegurar que no haya overflow horizontal */
        body {
            overflow-x: hidden;
        }
        
        main {
            overflow-y: auto;
            overflow-x: hidden;
        }
    </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">

    <aside id="sidebar" class="sidebar w-72 bg-[#e2f3e4] flex flex-col border-r border-gray-200">
        <div class="p-6 flex items-center space-x-2 logo-container">
            <div class="bg-green-500 p-2 rounded-lg logo-icon">
                <i class="fas fa-graduation-cap text-white text-xl"></i>
            </div>
            <span class="text-xl font-bold logo-text" style="color: var(--text-primary);"><?php echo $user_role; ?></span>
        </div>

        <div class="toggle-btn">
            <button onclick="toggleSidebar()" title="Contraer/Expandir sidebar">
                <i class="fas fa-chevron-left" style="color: var(--green-500);"></i>
            </button>
        </div>

        <nav class="flex-1 px-4 space-y-1 mt-2 overflow-y-auto custom-scrollbar" style="padding-bottom:140px;">
            <p class="text-xs font-bold text-gray-500 uppercase px-2 mb-2 mt-4">Panel Principal</p>
            <button onclick="showSection('resumen')" class="nav-item active w-full flex items-center space-x-3 text-gray-600 p-3 rounded-xl transition hover:bg-white/50">
                <i class="fas fa-th-large"></i><span>Resumen Académico</span>
            </button>
            

            
            <p class="text-xs font-bold text-gray-500 uppercase px-2 mb-2 mt-6">Talento Humano</p>

            <button onclick="showSection('instructores')" class="nav-item w-full flex items-center space-x-3 text-gray-600 p-3 rounded-xl transition hover:bg-white/50">
                <i class="fas fa-chalkboard-teacher"></i><span>Instructores</span>
            </button>

            <p class="text-xs font-bold text-gray-500 uppercase px-2 mb-2 mt-6">Infraestructura y Programas</p>
            <button onclick="showSection('fichas')" class="nav-item w-full flex items-center space-x-3 text-gray-600 p-3 rounded-xl transition hover:bg-white/50">
                <i class="fas fa-layer-group"></i><span>Fichas</span>
            </button>
            <button onclick="showSection('sedes')" class="nav-item w-full flex items-center space-x-3 text-gray-600 p-3 rounded-xl transition hover:bg-white/50">
                <i class="fas fa-map-marker-alt"></i><span>Sedes</span>
            </button>
            <button onclick="showSection('ambientes')" class="nav-item w-full flex items-center space-x-3 text-gray-600 p-3 rounded-xl transition hover:bg-white/50">
                <i class="fas fa-door-open"></i><span>Ambientes</span>
            </button>
            <button onclick="showSection('programas')" class="nav-item w-full flex items-center space-x-3 text-gray-600 p-3 rounded-xl transition hover:bg-white/50">
                <i class="fas fa-book"></i><span>Programas</span>
            </button>
            <button onclick="showSection('competencias')" class="nav-item w-full flex items-center space-x-3 text-gray-600 p-3 rounded-xl transition hover:bg-white/50">
                <i class="fas fa-award"></i><span>Competencias</span>
            </button>
            
            <p class="text-xs font-bold text-gray-500 uppercase px-2 mb-2 mt-6">Gestión Académica</p>
            <button onclick="showSection('asignaciones')" class="nav-item w-full flex items-center space-x-3 text-gray-600 p-3 rounded-xl transition hover:bg-white/50">
                <i class="fas fa-clipboard-list"></i><span>Asignaciones</span>
            </button>
        </nav>

        <div class="mx-6 border-t border-gray-200/50 my-2"></div>
        <div class="p-3 profile-box relative">
            <div class="bg-white p-2 rounded-2xl flex items-center justify-between shadow-sm profile-card">
                <div class="flex items-center space-x-3">
                    <div class="avatar-outer">
                        <div class="profile-avatar"><?php echo $initials; ?></div>
                    </div>
                    <div class="profile-info">
                        <p class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($user_name); ?></p>
                        <p class="text-xs text-gray-500"><?php echo $user_role; ?></p>
                    </div>
                </div>
                <button id="profileToggle" class="profile-menu-btn text-gray-400 hover:text-gray-600 ml-2"><i class="fas fa-ellipsis-h"></i></button>
            </div>

            <div id="profileMenu" class="hidden absolute left-4 bottom-20 w-48 bg-white rounded-xl shadow-lg z-50 overflow-hidden">
                <a href="index.php?controlador=Coordinador&accion=perfil" class="block px-4 py-2 hover:bg-gray-50 text-gray-700">
                    <i class="fas fa-user mr-2"></i>Ver Perfil
                </a>
                <a href="#" onclick="abrirModalConfiguracion(); document.getElementById('profileMenu').classList.add('hidden'); return false;" class="block px-4 py-2 hover:bg-gray-50 text-gray-700">
                    <i class="fas fa-cog mr-2"></i>Configuración
                </a>
                <a href="logout.php" class="block px-4 py-2 text-red-500 hover:bg-gray-50">
                    <i class="fas fa-sign-out-alt mr-2"></i>Cerrar Sesión
                </a>
            </div>
        </div>
    </aside>

    <main class="flex-1 overflow-y-auto p-8">
        <?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
        <?php if (!empty($_SESSION['mensaje'])): ?>
            <div class="mb-6 p-4 rounded-xl <?php echo (isset($_SESSION['tipo_mensaje']) && $_SESSION['tipo_mensaje'] === 'success') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <?php echo htmlspecialchars($_SESSION['mensaje']); unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
            </div>
        <?php endif; ?>

        <header class="flex justify-between items-center mb-8">
            <h1 id="section-title" class="text-3xl font-bold" style="color: var(--sena-verde-principal);">Resumen Académico</h1>
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    <input type="text" placeholder="Buscar..." class="pl-10 pr-4 py-2 border rounded-xl focus:ring-2 outline-none w-64" style="border-color: var(--border-color); background: var(--bg-secondary); color: var(--text-primary); focus:ring-color: var(--sena-verde-principal);">
                </div>
                <button id="themeToggle" class="p-2 border rounded-xl text-gray-500 hover:text-[#10b981] transition relative flex items-center" style="background: var(--bg-secondary); border-color: var(--border-color); color: var(--text-tertiary);" title="Cambiar tema">
                    <i class="fas fa-moon" id="themeIcon"></i>
                </button>
                <div class="relative">
                    <button id="calendarBtn" class="p-2 border rounded-xl hover:text-[#10b981] transition relative flex items-center" aria-expanded="false" aria-haspopup="false" title="Mostrar/Ocultar Calendario" style="background: var(--bg-secondary); border-color: var(--border-color); color: var(--text-tertiary);">
                        <i class="far fa-calendar-alt text-lg"></i>
                    </button>
                </div>
                <div class="relative">
                    <button id="notifBtn" class="p-2 bg-white border border-gray-200 rounded-xl text-gray-500 hover:text-[#10b981] transition relative flex items-center" aria-expanded="false" aria-haspopup="true" title="Notificaciones">
                        <i class="far fa-bell"></i>
                        <span id="notifCount" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center" style="display:none">0</span>
                        <span id="notifDot" class="absolute -top-1 -right-1 bg-red-500 rounded-full" style="width:8px;height:8px;display:none;margin-right:12px;margin-top:3px;"></span>
                    </button>

                    <div id="notifMenu" class="hidden">
                        <div class="notif-card">
                            <div class="head">
                                <span>Notificaciones</span>
                                <button id="markAllRead" class="text-xs px-3 py-1 rounded-lg hover:bg-white hover:bg-opacity-10 transition" style="background:transparent;border:0;color:rgba(255,255,255,0.9);">
                                    <i class="fas fa-check-double mr-1"></i>Marcar todas
                                </button>
                            </div>
                            <div class="notif-list" id="notifList">
                                <!-- Notificaciones se renderizan aquí (items .notif-item) -->
                                <div class="notif-item" style="font-size:13px;">No hay nuevas notificaciones</div>
                            </div>
                        </div>
                    </div>
                </div>
                <button id="enviarNotifBtn" class="p-2 bg-green-500 border border-green-500 rounded-xl text-white hover:bg-green-600 transition flex items-center gap-2 px-4" title="Enviar Notificación a Instructor">
                    <i class="fas fa-paper-plane"></i>
                    <span class="text-sm font-semibold">Notificar Instructor</span>
                </button>
            </div>
        </header>

        <section id="resumen" class="view-section active">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                    <div class="bg-green-500 w-10 h-10 rounded-xl flex items-center justify-center text-white mb-4"><i class="fas fa-folder"></i></div>
                    <p class="text-3xl font-bold text-slate-800">84</p>
                    <p class="text-gray-500 text-sm">Fichas de Formación</p>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                    <div class="bg-green-500 w-10 h-10 rounded-xl flex items-center justify-center text-white mb-4"><i class="fas fa-award"></i></div>
                    <p class="text-3xl font-bold text-slate-800">89%</p>
                    <p class="text-gray-500 text-sm">Meta de Certificación</p>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                    <div class="bg-green-500 w-10 h-10 rounded-xl flex items-center justify-center text-white mb-4"><i class="fas fa-chart-line"></i></div>
                    <p class="text-3xl font-bold text-slate-800">2.4%</p>
                    <p class="text-gray-500 text-sm">Deserción Mensual</p>
                </div>
            </div>

            <!-- Calendar section - oculto inicialmente, se mostrará al pulsar el icono de calendario -->
            <div id="calendarSection" class="mb-8" style="display:none;">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold" style="color: var(--text-primary);">Calendario</h2>
                    <button id="openAddEvent" class="bg-green-500 text-white px-3 py-2 rounded-xl text-sm font-bold hover:bg-green-600 transition">+ Nueva Asignación</button>
                </div>
                <div id="calendarEl"></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-3xl border border-gray-100 h-64 flex items-center justify-center text-gray-400 italic">Tendencia Académica</div>
                <div class="bg-white p-6 rounded-3xl border border-gray-100 h-64 flex items-center justify-center text-gray-400 italic">Ocupación de Ambientes</div>
            </div>
        </section>

        <section id="table-view" class="view-section">
            <!-- Stats Cards Container -->
            <div id="stats-cards" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8"></div>
            
            <!-- Table Container -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center" style="border-color: var(--border-color);">
                    <h3 id="table-title" class="text-xl font-bold" style="color: var(--text-primary);">Listado</h3>
                    <button onclick="openModal()" class="bg-green-500 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-green-600 transition">
                        + Agregar Nuevo
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-bold">
                            <tr id="table-headers">
                                </tr>
                        </thead>
                        <tbody id="table-body" class="text-gray-600 text-sm">
                            </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Sección de Asignaciones -->
        <section id="asignaciones" class="view-section">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm mb-1">Total Asignaciones</p>
                            <p class="text-4xl font-bold text-green-500">3</p>
                        </div>
                        <div class="bg-green-100 w-14 h-14 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-clipboard-list text-green-500 text-2xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm mb-1">Asignaciones Activas</p>
                            <p class="text-4xl font-bold text-green-500">0</p>
                        </div>
                        <div class="bg-green-100 w-14 h-14 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs y Tabla -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="border-b border-gray-100 px-6 py-4 flex justify-between items-center" style="border-color: var(--border-color);">
                    <h3 class="text-xl font-bold" style="color: var(--text-primary);">Lista de Asignaciones</h3>
                    <button onclick="openModalAsignacion()" class="bg-green-500 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-green-600 transition">
                        <i class="fas fa-plus mr-2"></i>Nueva Asignación
                    </button>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">FICHA</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">INSTRUCTOR</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">AMBIENTE</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">COMPETENCIA</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">FECHA INICIO</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">ESTADO</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-asignaciones-body" class="bg-white divide-y divide-gray-100">
                            <!-- Las asignaciones se cargarán dinámicamente aquí -->
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                    <p>Cargando asignaciones...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </main>

    <!-- Modal Ver Asignación -->
    <div id="modal-ver-asignacion" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-slate-800">Detalles de Asignación</h2>
                <button onclick="cerrarModalAsignacion('ver')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="contenido-ver-asignacion" class="space-y-4">
                <!-- Contenido dinámico -->
            </div>
            <div class="flex justify-end mt-6">
                <button onclick="cerrarModalAsignacion('ver')" class="px-6 py-2 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Nueva Asignación -->
    <div id="modal-nueva-asignacion" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-slate-800">Nueva Asignación</h2>
                <button onclick="cerrarModalAsignacion('nueva')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="form-nueva-asignacion" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo de Ambiente</label>
                    <select id="select-tipo-ambiente" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none">
                        <option value="">-- Todos los tipos --</option>
                        <option value="Aula">Aula</option>
                        <option value="Laboratorio">Laboratorio</option>
                        <option value="Taller">Taller</option>
                        <option value="Virtual">Virtual</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Ubicación de Ambiente</label>
                    <select id="select-ubicacion-ambiente" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none">
                        <option value="">-- Todas las ubicaciones --</option>
                        <option value="Interna">Interna</option>
                        <option value="Externa">Externa</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Ambiente *</label>
                    <select id="select-ambiente" name="ambiente" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none" required>
                        <option value="">-- Seleccione un ambiente --</option>
                        <option value="Laboratorio 201" data-tipo="Laboratorio" data-ubicacion="Interna">Laboratorio 201</option>
                        <option value="Ambiente 101" data-tipo="Aula" data-ubicacion="Interna">Ambiente 101</option>
                        <option value="Aula 305" data-tipo="Aula" data-ubicacion="Interna">Aula 305</option>
                        <option value="Taller 102" data-tipo="Taller" data-ubicacion="Externa">Taller 102</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Competencia *</label>
                    <select name="competencia" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none" required>
                        <option value="">-- Seleccione una competencia --</option>
                        <option value="Gestión documental">Gestión documental</option>
                        <option value="Análisis de datos">Análisis de datos</option>
                        <option value="Desarrollo de software">Desarrollo de software</option>
                        <option value="Comunicación efectiva">Comunicación efectiva</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Instructor *</label>
                    <select name="instructor" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none" required>
                        <option value="">-- Seleccione un instructor --</option>
                        <option value="cristian contreras">Cristian Contreras</option>
                        <option value="Juan Pérez">Juan Pérez</option>
                        <option value="María García">María García</option>
                        <option value="Pedro López">Pedro López</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Ficha *</label>
                    <select id="select-ficha" name="ficha" class="w-full mt-3 px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none" required>
                        <option value="">-- Seleccione una ficha --</option>
                        <option value="2558888">2558888 - ADSO</option>
                        <option value="2558889">2558889 - Multimedia</option>
                        <option value="2558890">2558890 - Contabilidad</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha Inicio *</label>
                        <input type="date" name="fecha_inicio" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha Fin *</label>
                        <input type="date" name="fecha_fin" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none" required>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Hora Inicio *</label>
                        <select name="hora_inicio" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none" required>
                            <option value="06:00">06:00 a.m.</option>
                            <option value="06:15">06:15 a.m.</option>
                            <option value="06:30">06:30 a.m.</option>
                            <option value="06:45">06:45 a.m.</option>
                            <option value="07:00" selected>07:00 a.m.</option>
                            <option value="07:15">07:15 a.m.</option>
                            <option value="07:30">07:30 a.m.</option>
                            <option value="07:45">07:45 a.m.</option>
                            <option value="08:00">08:00 a.m.</option>
                            <option value="08:15">08:15 a.m.</option>
                            <option value="08:30">08:30 a.m.</option>
                            <option value="08:45">08:45 a.m.</option>
                            <option value="09:00">09:00 a.m.</option>
                            <option value="09:15">09:15 a.m.</option>
                            <option value="09:30">09:30 a.m.</option>
                            <option value="09:45">09:45 a.m.</option>
                            <option value="10:00">10:00 a.m.</option>
                            <option value="10:15">10:15 a.m.</option>
                            <option value="10:30">10:30 a.m.</option>
                            <option value="10:45">10:45 a.m.</option>
                            <option value="11:00">11:00 a.m.</option>
                            <option value="11:15">11:15 a.m.</option>
                            <option value="11:30">11:30 a.m.</option>
                            <option value="11:45">11:45 a.m.</option>
                            <option value="12:00">12:00 p.m.</option>
                            <option value="12:15">12:15 p.m.</option>
                            <option value="12:30">12:30 p.m.</option>
                            <option value="12:45">12:45 p.m.</option>
                            <option value="13:00">01:00 p.m.</option>
                            <option value="13:15">01:15 p.m.</option>
                            <option value="13:30">01:30 p.m.</option>
                            <option value="13:45">01:45 p.m.</option>
                            <option value="14:00">02:00 p.m.</option>
                            <option value="14:15">02:15 p.m.</option>
                            <option value="14:30">02:30 p.m.</option>
                            <option value="14:45">02:45 p.m.</option>
                            <option value="15:00">03:00 p.m.</option>
                            <option value="15:15">03:15 p.m.</option>
                            <option value="15:30">03:30 p.m.</option>
                            <option value="15:45">03:45 p.m.</option>
                            <option value="16:00">04:00 p.m.</option>
                            <option value="16:15">04:15 p.m.</option>
                            <option value="16:30">04:30 p.m.</option>
                            <option value="16:45">04:45 p.m.</option>
                            <option value="17:00">05:00 p.m.</option>
                            <option value="17:15">05:15 p.m.</option>
                            <option value="17:30">05:30 p.m.</option>
                            <option value="17:45">05:45 p.m.</option>
                            <option value="18:00">06:00 p.m.</option>
                            <option value="18:15">06:15 p.m.</option>
                            <option value="18:30">06:30 p.m.</option>
                            <option value="18:45">06:45 p.m.</option>
                            <option value="19:00">07:00 p.m.</option>
                            <option value="19:15">07:15 p.m.</option>
                            <option value="19:30">07:30 p.m.</option>
                            <option value="19:45">07:45 p.m.</option>
                            <option value="20:00">08:00 p.m.</option>
                            <option value="20:15">08:15 p.m.</option>
                            <option value="20:30">08:30 p.m.</option>
                            <option value="20:45">08:45 p.m.</option>
                            <option value="21:00">09:00 p.m.</option>
                            <option value="21:15">09:15 p.m.</option>
                            <option value="21:30">09:30 p.m.</option>
                            <option value="21:45">09:45 p.m.</option>
                            <option value="22:00">10:00 p.m.</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Hora Final *</label>
                        <select name="hora_fin" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none" required>
                            <option value="06:00">06:00 a.m.</option>
                            <option value="06:15">06:15 a.m.</option>
                            <option value="06:30">06:30 a.m.</option>
                            <option value="06:45">06:45 a.m.</option>
                            <option value="07:00">07:00 a.m.</option>
                            <option value="07:15">07:15 a.m.</option>
                            <option value="07:30">07:30 a.m.</option>
                            <option value="07:45">07:45 a.m.</option>
                            <option value="08:00">08:00 a.m.</option>
                            <option value="08:15">08:15 a.m.</option>
                            <option value="08:30">08:30 a.m.</option>
                            <option value="08:45">08:45 a.m.</option>
                            <option value="09:00">09:00 a.m.</option>
                            <option value="09:15">09:15 a.m.</option>
                            <option value="09:30">09:30 a.m.</option>
                            <option value="09:45">09:45 a.m.</option>
                            <option value="10:00">10:00 a.m.</option>
                            <option value="10:15">10:15 a.m.</option>
                            <option value="10:30">10:30 a.m.</option>
                            <option value="10:45">10:45 a.m.</option>
                            <option value="11:00">11:00 a.m.</option>
                            <option value="11:15">11:15 a.m.</option>
                            <option value="11:30">11:30 a.m.</option>
                            <option value="11:45">11:45 a.m.</option>
                            <option value="12:00">12:00 p.m.</option>
                            <option value="12:15">12:15 p.m.</option>
                            <option value="12:30">12:30 p.m.</option>
                            <option value="12:45">12:45 p.m.</option>
                            <option value="13:00">01:00 p.m.</option>
                            <option value="13:15">01:15 p.m.</option>
                            <option value="13:30">01:30 p.m.</option>
                            <option value="13:45">01:45 p.m.</option>
                            <option value="14:00">02:00 p.m.</option>
                            <option value="14:15">02:15 p.m.</option>
                            <option value="14:30">02:30 p.m.</option>
                            <option value="14:45">02:45 p.m.</option>
                            <option value="15:00">03:00 p.m.</option>
                            <option value="15:15">03:15 p.m.</option>
                            <option value="15:30">03:30 p.m.</option>
                            <option value="15:45">03:45 p.m.</option>
                            <option value="16:00">04:00 p.m.</option>
                            <option value="16:15">04:15 p.m.</option>
                            <option value="16:30">04:30 p.m.</option>
                            <option value="16:45">04:45 p.m.</option>
                            <option value="17:00">05:00 p.m.</option>
                            <option value="17:15">05:15 p.m.</option>
                            <option value="17:30">05:30 p.m.</option>
                            <option value="17:45">05:45 p.m.</option>
                            <option value="18:00" selected>06:00 p.m.</option>
                            <option value="18:15">06:15 p.m.</option>
                            <option value="18:30">06:30 p.m.</option>
                            <option value="18:45">06:45 p.m.</option>
                            <option value="19:00">07:00 p.m.</option>
                            <option value="19:15">07:15 p.m.</option>
                            <option value="19:30">07:30 p.m.</option>
                            <option value="19:45">07:45 p.m.</option>
                            <option value="20:00">08:00 p.m.</option>
                            <option value="20:15">08:15 p.m.</option>
                            <option value="20:30">08:30 p.m.</option>
                            <option value="20:45">08:45 p.m.</option>
                            <option value="21:00">09:00 p.m.</option>
                            <option value="21:15">09:15 p.m.</option>
                            <option value="21:30">09:30 p.m.</option>
                            <option value="21:45">09:45 p.m.</option>
                            <option value="22:00">10:00 p.m.</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Estado *</label>
                    <select name="estado" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none" required>
                        <option value="Pendiente" selected>Pendiente</option>
                        <option value="Activa">Activa</option>
                        <option value="Completada">Completada</option>
                        <option value="Cancelada">Cancelada</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="cerrarModalAsignacion('nueva')" class="px-6 py-2 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit" class="px-6 py-2 bg-[#10b981] text-white rounded-xl hover:bg-green-700">
                        Crear Asignación
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Editar Asignación -->
    <div id="modal-editar-asignacion" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-slate-800">Editar Asignación</h2>
                <button onclick="cerrarModalAsignacion('editar')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="form-editar-asignacion" class="space-y-4">
                <input type="hidden" id="edit-asig-id" name="id">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Ficha</label>
                    <input type="text" id="edit-asig-ficha" name="ficha" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Instructor</label>
                    <input type="text" id="edit-asig-instructor" name="instructor" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Ambiente</label>
                    <input type="text" id="edit-asig-ambiente" name="ambiente" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Competencia</label>
                    <input type="text" id="edit-asig-competencia" name="competencia" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha Inicio</label>
                    <input type="text" id="edit-asig-fecha" name="fecha_inicio" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Estado</label>
                    <select id="edit-asig-estado" name="estado" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none" required>
                        <option value="Pendiente">Pendiente</option>
                        <option value="Activa">Activa</option>
                        <option value="Completada">Completada</option>
                        <option value="Cancelada">Cancelada</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="cerrarModalAsignacion('editar')" class="px-6 py-2 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit" class="px-6 py-2 bg-[#10b981] text-white rounded-xl hover:bg-green-700">
                        Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Eliminar Asignación -->
    <div id="modal-eliminar-asignacion" class="modal">
        <div class="modal-content max-w-md">
            <div class="text-center mb-6">
                <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-slate-800 mb-2">¿Eliminar Asignación?</h2>
                <p class="text-gray-600">Esta acción no se puede deshacer. ¿Estás seguro de que deseas eliminar esta asignación?</p>
            </div>
            <div class="flex justify-center gap-3">
                <button onclick="cerrarModalAsignacion('eliminar')" class="px-6 py-2 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                    Cancelar
                </button>
                <button onclick="confirmarEliminarAsignacion()" class="px-6 py-2 bg-red-500 text-white rounded-xl hover:bg-red-600">
                    Eliminar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar Evento del Calendario -->
    <div id="modal-eliminar-evento" class="modal">
        <div class="modal-content max-w-md">
            <div class="text-center mb-6">
                <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-trash-alt text-red-500 text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-slate-800 mb-2">Eliminar Asignación</h2>
                <p class="text-gray-600 mb-4">Esta asignación tiene <span id="total-eventos-eliminar" class="font-bold text-red-500">0</span> evento(s) en el calendario.</p>
                <p class="text-gray-600">Al eliminar, se borrarán <strong>todos los eventos</strong> de esta asignación.</p>
                <p class="text-sm text-gray-500 mt-2">Esta acción no se puede deshacer.</p>
            </div>
            <div class="flex justify-center gap-3">
                <button onclick="cerrarModalEliminarEvento()" class="px-6 py-2 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                    Cancelar
                </button>
                <button onclick="confirmarEliminarEvento()" class="px-6 py-2 bg-red-500 text-white rounded-xl hover:bg-red-600 transition">
                    <i class="fas fa-trash-alt mr-2"></i>Eliminar Todo
                </button>
            </div>
        </div>
    </div>

    <!-- Modales para cada sección -->
    

    <!-- Modal Instructores -->
    <div id="modal-instructores" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-slate-800">Nuevo Instructor</h2>
                <button onclick="closeModal('instructores')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form method="POST" action="index.php?controlador=Administrador&accion=addInstructor" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nombres</label>
                        <input type="text" name="nombres" placeholder="Pedro" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Apellidos</label>
                        <input type="text" name="apellidos" placeholder="Gómez" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Especialidad</label>
                    <input type="text" name="especialidad" placeholder="ADSO, Multimedia, etc." class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Correo Electrónico</label>
                    <input type="email" name="email" placeholder="instructor@sena.edu.co" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Carga Horaria Semanal</label>
                    <input type="number" name="carga_horaria" placeholder="40" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none">
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('instructores')" class="px-6 py-2 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Cancelar</button>
                    <button type="submit" class="px-6 py-2 bg-[#10b981] text-white rounded-xl hover:bg-green-700">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Fichas -->
    <div id="modal-fichas" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-slate-800">Nueva Ficha</h2>
                <button onclick="closeModal('fichas')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form method="POST" action="index.php?controlador=Administrador&accion=addFicha" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Código de Ficha</label>
                    <input type="text" name="codigo" placeholder="2504321" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Programa de Formación</label>
                    <select name="programa" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none">
                        <option>ADSO - Análisis y Desarrollo de Software</option>
                        <option>Gestión Empresarial</option>
                        <option>Contabilidad y Finanzas</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Sede</label>
                    <select name="sede_id" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none">
                        <option value="">-- Seleccione una sede --</option>
                        <?php if (isset($sedes) && is_array($sedes)): ?>
                            <?php foreach ($sedes as $s): ?>
                                <option value="<?php echo $s['sede_id']; ?>"><?php echo htmlspecialchars($s['nombre_sede']); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha de Inicio</label>
                        <input type="date" name="inicio" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Fin Etapa Lectiva</label>
                        <input type="date" name="fin" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Número de Aprendices</label>
                    <input type="number" name="aprendices" placeholder="28" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none">
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('fichas')" class="px-6 py-2 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Cancelar</button>
                    <button type="submit" class="px-6 py-2 bg-[#10b981] text-white rounded-xl hover:bg-green-700">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Sedes -->
    <div id="modal-sedes" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-slate-800">Nueva Sede</h2>
                <button onclick="closeModal('sedes')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form method="POST" action="index.php?controlador=Administrador&accion=addSede" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre de la Sede</label>
                    <input type="text" name="nombre" placeholder="Sede Central" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Dirección</label>
                    <input type="text" name="direccion" placeholder="Calle 15 #2-30" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Encargado</label>
                    <input type="text" name="encargado" placeholder="Juan Pérez" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Teléfono</label>
                    <input type="tel" name="telefono" placeholder="3001234567" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none">
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('sedes')" class="px-6 py-2 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Cancelar</button>
                    <button type="submit" class="px-6 py-2 bg-[#10b981] text-white rounded-xl hover:bg-green-700">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Ambientes -->
    <div id="modal-ambientes" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-slate-800">Nuevo Ambiente</h2>
                <button onclick="closeModal('ambientes')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form method="POST" action="index.php?controlador=Administrador&accion=addAmbiente" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre del Ambiente</label>
                    <input type="text" name="nombre" placeholder="Laboratorio 1" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Sede</label>
                    <select name="sede" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none">
                        <option value="">-- Seleccione una sede --</option>
                        <?php if (isset($sedes) && is_array($sedes)): ?>
                            <?php foreach ($sedes as $s): ?>
                                <option value="<?php echo $s['sede_id']; ?>"><?php echo htmlspecialchars($s['nombre_sede']); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Capacidad</label>
                        <input type="number" name="capacidad" placeholder="30" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo</label>
                        <select name="tipo" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none">
                            <option>Computo</option>
                            <option>Polivalente</option>
                            <option>Taller</option>
                            <option>Laboratorio</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('ambientes')" class="px-6 py-2 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Cancelar</button>
                    <button type="submit" class="px-6 py-2 bg-[#10b981] text-white rounded-xl hover:bg-green-700">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Programas -->
    <div id="modal-programas" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-slate-800">Nuevo Programa</h2>
                <button onclick="closeModal('programas')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form method="POST" action="index.php?controlador=Administrador&accion=addPrograma" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Código del Programa</label>
                    <input type="text" name="codigo" placeholder="228106" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre del Programa</label>
                    <input type="text" name="nombre" placeholder="Análisis y Desarrollo de Software" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nivel</label>
                        <select name="nivel" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none">
                            <option>Tecnólogo</option>
                            <option>Técnico</option>
                            <option>Operario</option>
                            <option>Especialización</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Duración (Meses)</label>
                        <input type="number" name="duracion" placeholder="27" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none">
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('programas')" class="px-6 py-2 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Cancelar</button>
                    <button type="submit" class="px-6 py-2 bg-[#10b981] text-white rounded-xl hover:bg-green-700">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Competencias -->
    <div id="modal-competencias" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-slate-800">Nueva Competencia</h2>
                <button onclick="closeModal('competencias')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form method="POST" action="index.php?controlador=Administrador&accion=addCompetencia" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Código de Competencia</label>
                    <input type="text" name="codigo" placeholder="220501092" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Descripción</label>
                    <textarea name="descripcion" rows="3" placeholder="Analizar requisitos del cliente..." class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Horas</label>
                        <input type="number" name="horas" placeholder="120" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo</label>
                        <select name="tipo" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none">
                            <option>Técnica</option>
                            <option>Transversal</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('competencias')" class="px-6 py-2 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Cancelar</button>
                    <button type="submit" class="px-6 py-2 bg-[#10b981] text-white rounded-xl hover:bg-green-700">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de Confirmación para Eliminar -->
    <div id="modal-delete" class="modal">
        <div class="modal-content max-w-md">
            <div class="text-center mb-6">
                <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-slate-800 mb-2">¿Confirmar Eliminación?</h2>
                <p class="text-gray-600">Esta acción no se puede deshacer. ¿Estás seguro de que deseas eliminar este registro?</p>
            </div>
            <div class="flex justify-center space-x-3">
                <button onclick="closeDeleteModal()" class="px-6 py-2 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                    Cancelar
                </button>
                <button onclick="confirmDelete()" class="px-6 py-2 bg-red-500 text-white rounded-xl hover:bg-red-600">
                    Eliminar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de Edición (Reutilizable) -->
    <div id="modal-edit" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-6">
                <h2 id="edit-title" class="text-2xl font-bold text-slate-800">Editar Registro</h2>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="edit-form" class="space-y-4">
                <div id="edit-fields"></div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeEditModal()" class="px-6 py-2 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Cancelar</button>
                    <button type="submit" class="px-6 py-2 bg-[#10b981] text-white rounded-xl hover:bg-green-700">Actualizar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para Nueva Asignación -->
    <div id="modal-add-event" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-slate-800">Nueva Asignación</h2>
                <button onclick="closeAddEventModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="add-event-form" class="space-y-4">
                <input type="hidden" id="event-id" value="">
                
                <!-- Ambiente -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Ambiente *</label>
                    <select id="asig-ambiente" name="ambiente_id" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none" required>
                        <option value="">-- Seleccione un ambiente --</option>
                    </select>
                </div>

                <!-- Competencia -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Competencia *</label>
                    <select id="asig-competencia" name="competencia_id" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none" required>
                        <option value="">-- Seleccione una competencia --</option>
                    </select>
                </div>

                <!-- Instructor -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Instructor *</label>
                    <select id="asig-instructor" name="instructor_id" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none" required>
                        <option value="">-- Seleccione un instructor --</option>
                    </select>
                </div>

                <!-- Ficha -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Ficha *</label>
                    <select id="asig-ficha" name="ficha_id" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none" required>
                        <option value="">-- Seleccione una ficha --</option>
                    </select>
                </div>

                <!-- Días de la semana -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Días de la semana</label>
                    <div class="flex gap-2 flex-wrap">
                        <label class="flex items-center gap-2 px-3 py-2 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" id="day-1" value="1" class="w-4 h-4 text-[#10b981] rounded focus:ring-[#10b981]" checked>
                            <span class="text-sm">Lun</span>
                        </label>
                        <label class="flex items-center gap-2 px-3 py-2 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" id="day-2" value="2" class="w-4 h-4 text-[#10b981] rounded focus:ring-[#10b981]" checked>
                            <span class="text-sm">Mar</span>
                        </label>
                        <label class="flex items-center gap-2 px-3 py-2 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" id="day-3" value="3" class="w-4 h-4 text-[#10b981] rounded focus:ring-[#10b981]" checked>
                            <span class="text-sm">Mié</span>
                        </label>
                        <label class="flex items-center gap-2 px-3 py-2 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" id="day-4" value="4" class="w-4 h-4 text-[#10b981] rounded focus:ring-[#10b981]" checked>
                            <span class="text-sm">Jue</span>
                        </label>
                        <label class="flex items-center gap-2 px-3 py-2 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" id="day-5" value="5" class="w-4 h-4 text-[#10b981] rounded focus:ring-[#10b981]" checked>
                            <span class="text-sm">Vie</span>
                        </label>
                        <label class="flex items-center gap-2 px-3 py-2 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" id="day-6" value="6" class="w-4 h-4 text-[#10b981] rounded focus:ring-[#10b981]">
                            <span class="text-sm">Sáb</span>
                        </label>
                    </div>
                </div>

                <!-- Rango de fechas -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Rango de fechas</label>
                    <div class="flex gap-2 items-center">
                        <input type="date" id="event-start-range" class="flex-1 px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none" required>
                        <span class="text-gray-500">-</span>
                        <input type="date" id="event-end" class="flex-1 px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none" required>
                    </div>
                </div>

                <!-- Rango de horas -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Rango de horas</label>
                    <div class="flex gap-2 items-center">
                        <select id="event-start-time" class="flex-1 px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none" required>
                            <option value="06:00">06:00 a.m.</option>
                            <option value="06:15">06:15 a.m.</option>
                            <option value="06:30">06:30 a.m.</option>
                            <option value="06:45">06:45 a.m.</option>
                            <option value="07:00" selected>07:00 a.m.</option>
                            <option value="07:15">07:15 a.m.</option>
                            <option value="07:30">07:30 a.m.</option>
                            <option value="07:45">07:45 a.m.</option>
                            <option value="08:00">08:00 a.m.</option>
                            <option value="08:15">08:15 a.m.</option>
                            <option value="08:30">08:30 a.m.</option>
                            <option value="08:45">08:45 a.m.</option>
                            <option value="09:00">09:00 a.m.</option>
                            <option value="09:15">09:15 a.m.</option>
                            <option value="09:30">09:30 a.m.</option>
                            <option value="09:45">09:45 a.m.</option>
                            <option value="10:00">10:00 a.m.</option>
                            <option value="10:15">10:15 a.m.</option>
                            <option value="10:30">10:30 a.m.</option>
                            <option value="10:45">10:45 a.m.</option>
                            <option value="11:00">11:00 a.m.</option>
                            <option value="11:15">11:15 a.m.</option>
                            <option value="11:30">11:30 a.m.</option>
                            <option value="11:45">11:45 a.m.</option>
                            <option value="12:00">12:00 p.m.</option>
                            <option value="12:15">12:15 p.m.</option>
                            <option value="12:30">12:30 p.m.</option>
                            <option value="12:45">12:45 p.m.</option>
                            <option value="13:00">01:00 p.m.</option>
                            <option value="13:15">01:15 p.m.</option>
                            <option value="13:30">01:30 p.m.</option>
                            <option value="13:45">01:45 p.m.</option>
                            <option value="14:00">02:00 p.m.</option>
                            <option value="14:15">02:15 p.m.</option>
                            <option value="14:30">02:30 p.m.</option>
                            <option value="14:45">02:45 p.m.</option>
                            <option value="15:00">03:00 p.m.</option>
                            <option value="15:15">03:15 p.m.</option>
                            <option value="15:30">03:30 p.m.</option>
                            <option value="15:45">03:45 p.m.</option>
                            <option value="16:00">04:00 p.m.</option>
                            <option value="16:15">04:15 p.m.</option>
                            <option value="16:30">04:30 p.m.</option>
                            <option value="16:45">04:45 p.m.</option>
                            <option value="17:00">05:00 p.m.</option>
                            <option value="17:15">05:15 p.m.</option>
                            <option value="17:30">05:30 p.m.</option>
                            <option value="17:45">05:45 p.m.</option>
                            <option value="18:00">06:00 p.m.</option>
                            <option value="18:15">06:15 p.m.</option>
                            <option value="18:30">06:30 p.m.</option>
                            <option value="18:45">06:45 p.m.</option>
                            <option value="19:00">07:00 p.m.</option>
                            <option value="19:15">07:15 p.m.</option>
                            <option value="19:30">07:30 p.m.</option>
                            <option value="19:45">07:45 p.m.</option>
                            <option value="20:00">08:00 p.m.</option>
                            <option value="20:15">08:15 p.m.</option>
                            <option value="20:30">08:30 p.m.</option>
                            <option value="20:45">08:45 p.m.</option>
                            <option value="21:00">09:00 p.m.</option>
                            <option value="21:15">09:15 p.m.</option>
                            <option value="21:30">09:30 p.m.</option>
                            <option value="21:45">09:45 p.m.</option>
                            <option value="22:00">10:00 p.m.</option>
                        </select>
                        <span class="text-gray-500">-</span>
                        <select id="event-end-time" class="flex-1 px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none" required>
                            <option value="06:00">06:00 a.m.</option>
                            <option value="06:15">06:15 a.m.</option>
                            <option value="06:30">06:30 a.m.</option>
                            <option value="06:45">06:45 a.m.</option>
                            <option value="07:00">07:00 a.m.</option>
                            <option value="07:15">07:15 a.m.</option>
                            <option value="07:30">07:30 a.m.</option>
                            <option value="07:45">07:45 a.m.</option>
                            <option value="08:00">08:00 a.m.</option>
                            <option value="08:15">08:15 a.m.</option>
                            <option value="08:30">08:30 a.m.</option>
                            <option value="08:45">08:45 a.m.</option>
                            <option value="09:00">09:00 a.m.</option>
                            <option value="09:15">09:15 a.m.</option>
                            <option value="09:30">09:30 a.m.</option>
                            <option value="09:45">09:45 a.m.</option>
                            <option value="10:00">10:00 a.m.</option>
                            <option value="10:15">10:15 a.m.</option>
                            <option value="10:30">10:30 a.m.</option>
                            <option value="10:45">10:45 a.m.</option>
                            <option value="11:00">11:00 a.m.</option>
                            <option value="11:15">11:15 a.m.</option>
                            <option value="11:30">11:30 a.m.</option>
                            <option value="11:45">11:45 a.m.</option>
                            <option value="12:00">12:00 p.m.</option>
                            <option value="12:15">12:15 p.m.</option>
                            <option value="12:30">12:30 p.m.</option>
                            <option value="12:45">12:45 p.m.</option>
                            <option value="13:00">01:00 p.m.</option>
                            <option value="13:15">01:15 p.m.</option>
                            <option value="13:30">01:30 p.m.</option>
                            <option value="13:45">01:45 p.m.</option>
                            <option value="14:00">02:00 p.m.</option>
                            <option value="14:15">02:15 p.m.</option>
                            <option value="14:30">02:30 p.m.</option>
                            <option value="14:45">02:45 p.m.</option>
                            <option value="15:00">03:00 p.m.</option>
                            <option value="15:15">03:15 p.m.</option>
                            <option value="15:30">03:30 p.m.</option>
                            <option value="15:45">03:45 p.m.</option>
                            <option value="16:00">04:00 p.m.</option>
                            <option value="16:15">04:15 p.m.</option>
                            <option value="16:30">04:30 p.m.</option>
                            <option value="16:45">04:45 p.m.</option>
                            <option value="17:00">05:00 p.m.</option>
                            <option value="17:15">05:15 p.m.</option>
                            <option value="17:30">05:30 p.m.</option>
                            <option value="17:45">05:45 p.m.</option>
                            <option value="18:00" selected>06:00 p.m.</option>
                            <option value="18:15">06:15 p.m.</option>
                            <option value="18:30">06:30 p.m.</option>
                            <option value="18:45">06:45 p.m.</option>
                            <option value="19:00">07:00 p.m.</option>
                            <option value="19:15">07:15 p.m.</option>
                            <option value="19:30">07:30 p.m.</option>
                            <option value="19:45">07:45 p.m.</option>
                            <option value="20:00">08:00 p.m.</option>
                            <option value="20:15">08:15 p.m.</option>
                            <option value="20:30">08:30 p.m.</option>
                            <option value="20:45">08:45 p.m.</option>
                            <option value="21:00">09:00 p.m.</option>
                            <option value="21:15">09:15 p.m.</option>
                            <option value="21:30">09:30 p.m.</option>
                            <option value="21:45">09:45 p.m.</option>
                            <option value="22:00">10:00 p.m.</option>
                        </select>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Horario disponible: 6:00 AM - 10:00 PM</p>
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-between items-center mt-6">
                    <button type="button" id="delete-event-btn" class="px-4 py-2 text-red-600 rounded-xl hover:bg-red-50 hidden">Eliminar</button>
                    <div class="flex space-x-3">
                        <button type="button" onclick="closeAddEventModal()" class="px-6 py-2 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Cancelar</button>
                        <button type="submit" id="saveEventBtn" class="px-6 py-2 bg-[#10b981] text-white rounded-xl hover:bg-green-700">Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para Enviar Notificación a Instructor -->
    <div id="modal-enviar-notificacion-coord" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-slate-800">Enviar Notificación a Instructor</h2>
                <button onclick="cerrarModalNotificacion()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="form-enviar-notificacion-coord" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Instructor <span class="text-red-500">*</span>
                    </label>
                    <select id="notif-instructor-select" name="instructor_id" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none" required>
                        <option value="">-- Seleccione un instructor --</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Seleccione el instructor que recibirá la notificación</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Tipo de Notificación <span class="text-red-500">*</span>
                    </label>
                    <select id="notif-tipo" name="tipo" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none" required onchange="actualizarOpcionesNotificacion()">
                        <option value="general">General</option>
                        <option value="asignacion">Asignación</option>
                        <option value="cambio_horario">Cambio de Horario</option>
                        <option value="recordatorio">Recordatorio</option>
                        <option value="urgente">Urgente</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Título <span class="text-red-500">*</span>
                    </label>
                    <select id="notif-titulo-select" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none mb-2" onchange="actualizarTituloPersonalizado()">
                        <option value="">-- Seleccione un título predefinido --</option>
                    </select>
                    <input type="text" id="notif-titulo" name="titulo" placeholder="O escriba un título personalizado aquí..." class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none">
                    <p class="text-xs text-gray-500 mt-1">Puede seleccionar un título predefinido o escribir uno personalizado</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Mensaje <span class="text-red-500">*</span>
                    </label>
                    <select id="notif-mensaje-select" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none mb-2" onchange="actualizarMensajePersonalizado()">
                        <option value="">-- Seleccione un mensaje predefinido --</option>
                    </select>
                    <textarea id="notif-mensaje" name="mensaje" rows="4" placeholder="O escriba un mensaje personalizado aquí..." class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none resize-none"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Puede seleccionar un mensaje predefinido o escribir uno personalizado</p>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-info-circle text-blue-500 mt-1"></i>
                        <div class="text-sm text-blue-800">
                            <p class="font-semibold mb-1">Información</p>
                            <p>El instructor recibirá esta notificación en su panel y podrá verla en tiempo real.</p>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="cerrarModalNotificacion()" class="px-6 py-2 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit" class="px-6 py-2 bg-[#10b981] text-white rounded-xl hover:bg-green-700 flex items-center gap-2">
                        <i class="fas fa-paper-plane"></i>
                        Enviar Notificación
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Ver Perfil Coordinador -->
    <div id="modal-ver-perfil-coord" class="modal">
        <div class="modal-content" style="max-width: 450px; width: 90%;">
            <div class="flex justify-between items-center mb-3">
                <h2 class="text-base font-bold text-slate-800">
                    <i class="fas fa-user-circle mr-2" style="color: #10b981;"></i>Mi Perfil
                </h2>
                <button onclick="cerrarModalVerPerfil()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Avatar y Nombre -->
            <div class="text-center mb-4">
                <div class="w-14 h-14 rounded-full mx-auto flex items-center justify-center text-white text-lg font-bold relative" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <?php echo $initials; ?>
                    <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></div>
                </div>
                <h3 class="text-sm font-bold text-slate-800 mt-2"><?php echo htmlspecialchars($user_name); ?></h3>
                <p class="text-gray-500 text-xs">
                    <i class="fas fa-briefcase mr-1"></i><?php echo htmlspecialchars($user_role); ?>
                </p>
            </div>
            
            <!-- Información del Perfil - Estilo Formulario Compacto -->
            <div class="space-y-2.5">
                <!-- Información Personal -->
                <div>
                    <h4 class="text-xs font-bold text-gray-600 uppercase mb-1.5 flex items-center">
                        <i class="fas fa-user mr-1 text-green-500 text-xs"></i>Información Personal
                    </h4>
                    <div class="space-y-1.5">
                        <div class="flex items-center py-1">
                            <div class="w-6 h-6 rounded-lg bg-green-50 flex items-center justify-center mr-2 flex-shrink-0">
                                <i class="fas fa-envelope text-green-600" style="font-size: 10px;"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <label class="text-xs text-gray-500 block leading-tight">EMAIL</label>
                                <p class="text-xs text-slate-800 truncate leading-tight" id="perfil-email"><?php echo htmlspecialchars($_SESSION['email'] ?? 'coordinador@sena.edu.co'); ?></p>
                            </div>
                        </div>
                        <div class="flex items-center py-1">
                            <div class="w-6 h-6 rounded-lg bg-green-50 flex items-center justify-center mr-2 flex-shrink-0">
                                <i class="fas fa-phone text-green-600" style="font-size: 10px;"></i>
                            </div>
                            <div class="flex-1">
                                <label class="text-xs text-gray-500 block leading-tight">TELÉFONO</label>
                                <p class="text-xs text-slate-800 leading-tight" id="perfil-telefono">+57 300 123 4567</p>
                            </div>
                        </div>
                        <div class="flex items-center py-1">
                            <div class="w-6 h-6 rounded-lg bg-green-50 flex items-center justify-center mr-2 flex-shrink-0">
                                <i class="fas fa-id-card text-green-600" style="font-size: 10px;"></i>
                            </div>
                            <div class="flex-1">
                                <label class="text-xs text-gray-500 block leading-tight">DOCUMENTO</label>
                                <p class="text-xs text-slate-800 leading-tight" id="perfil-documento">CC 1234567890</p>
                            </div>
                        </div>
                        <div class="flex items-center py-1">
                            <div class="w-6 h-6 rounded-lg bg-green-50 flex items-center justify-center mr-2 flex-shrink-0">
                                <i class="fas fa-building text-green-600" style="font-size: 10px;"></i>
                            </div>
                            <div class="flex-1">
                                <label class="text-xs text-gray-500 block leading-tight">SEDE</label>
                                <p class="text-xs text-slate-800 leading-tight" id="perfil-sede"><?php echo htmlspecialchars($user_sede); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Información de Cuenta -->
                <div>
                    <h4 class="text-xs font-bold text-gray-600 uppercase mb-1.5 flex items-center">
                        <i class="fas fa-user-shield mr-1 text-green-500 text-xs"></i>Información de Cuenta
                    </h4>
                    <div class="space-y-1.5">
                        <div class="flex items-center py-1">
                            <div class="w-6 h-6 rounded-lg bg-green-50 flex items-center justify-center mr-2 flex-shrink-0">
                                <i class="fas fa-briefcase text-green-600" style="font-size: 10px;"></i>
                            </div>
                            <div class="flex-1">
                                <label class="text-xs text-gray-500 block leading-tight">ROL</label>
                                <p class="text-xs text-slate-800 leading-tight"><?php echo htmlspecialchars($user_role); ?></p>
                            </div>
                        </div>
                        <div class="flex items-center py-1">
                            <div class="w-6 h-6 rounded-lg bg-green-50 flex items-center justify-center mr-2 flex-shrink-0">
                                <i class="fas fa-circle text-green-600" style="font-size: 5px;"></i>
                            </div>
                            <div class="flex-1">
                                <label class="text-xs text-gray-500 block leading-tight">ESTADO</label>
                                <p class="text-xs text-green-600 font-medium leading-tight">● Activo</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end space-x-2 mt-4 pt-2.5 border-t border-gray-200">
                <button type="button" onclick="cerrarModalVerPerfil()" class="px-3 py-1.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-xs">
                    Cerrar
                </button>
                <button type="button" onclick="cerrarModalVerPerfil(); abrirModalEditarPerfilCoord();" class="px-3 py-1.5 rounded-lg text-white hover:opacity-90 transition text-xs" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <i class="fas fa-edit mr-1"></i>Editar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Editar Perfil Coordinador -->
    <div id="modal-editar-perfil-coord" class="modal">
        <div class="modal-content" style="max-width: 700px;">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-slate-800">
                    <i class="fas fa-user-edit mr-2" style="color: #10b981;"></i>Editar Perfil
                </h2>
                <button onclick="cerrarModalEditarPerfilCoord()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="form-editar-perfil-coord" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre *</label>
                        <input type="text" id="edit-nombre-coord" name="nombre" value="<?php echo htmlspecialchars($user_name); ?>" required class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                        <input type="email" id="edit-email-coord" name="email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? 'coordinador@sena.edu.co'); ?>" required class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Teléfono</label>
                        <input type="tel" id="edit-telefono-coord" name="telefono" value="+57 300 123 4567" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Documento</label>
                        <input type="text" id="edit-documento-coord" name="documento" value="1234567890" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Sede</label>
                    <select id="edit-sede-coord" name="sede" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="Sede Principal" selected>Sede Principal</option>
                        <option value="Sede Norte">Sede Norte</option>
                        <option value="Sede Sur">Sede Sur</option>
                        <option value="Sede Centro">Sede Centro</option>
                    </select>
                </div>
                
                <hr class="my-4">
                
                <h3 class="text-lg font-semibold text-slate-800 mb-3">Cambiar Contraseña (Opcional)</h3>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Contraseña Actual</label>
                    <input type="password" id="edit-password-actual-coord" name="password_actual" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nueva Contraseña</label>
                        <input type="password" id="edit-password-nueva-coord" name="password_nueva" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Confirmar Contraseña</label>
                        <input type="password" id="edit-password-confirmar-coord" name="password_confirmar" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="cerrarModalEditarPerfilCoord()" class="px-6 py-2 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit" class="px-6 py-2 rounded-xl text-white hover:opacity-90" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="fas fa-save mr-2"></i>Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Configuración -->
    <div id="modal-configuracion-coord" class="modal">
        <div class="modal-content" style="max-width: 700px;">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-slate-800">
                    <i class="fas fa-cog mr-2" style="color: #10b981;"></i>Configuración
                </h2>
                <button onclick="cerrarModalConfiguracion()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="space-y-6">
                <!-- Apariencia -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">
                        <i class="fas fa-palette mr-2"></i>Apariencia
                    </h3>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-slate-800">Tema Oscuro</p>
                            <p class="text-sm text-gray-500">Cambiar entre tema claro y oscuro</p>
                        </div>
                        <button id="themeToggleConfig" class="w-14 h-7 bg-gray-300 rounded-full relative transition-colors duration-300">
                            <span class="absolute left-1 top-1 w-5 h-5 bg-white rounded-full transition-transform duration-300"></span>
                        </button>
                    </div>
                </div>
                
                <!-- Notificaciones -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">
                        <i class="fas fa-bell mr-2"></i>Notificaciones
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-slate-800">Notificaciones de Email</p>
                                <p class="text-sm text-gray-500">Recibir notificaciones por correo</p>
                            </div>
                            <input type="checkbox" checked class="w-5 h-5 text-green-500 rounded focus:ring-green-500">
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-slate-800">Notificaciones Push</p>
                                <p class="text-sm text-gray-500">Recibir notificaciones en el navegador</p>
                            </div>
                            <input type="checkbox" checked class="w-5 h-5 text-green-500 rounded focus:ring-green-500">
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-slate-800">Sonido de Notificaciones</p>
                                <p class="text-sm text-gray-500">Reproducir sonido al recibir notificaciones</p>
                            </div>
                            <input type="checkbox" class="w-5 h-5 text-green-500 rounded focus:ring-green-500">
                        </div>
                    </div>
                </div>
                
                <!-- Privacidad -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">
                        <i class="fas fa-shield-alt mr-2"></i>Privacidad y Seguridad
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-slate-800">Sesión Activa</p>
                                <p class="text-sm text-gray-500">Mantener sesión iniciada</p>
                            </div>
                            <input type="checkbox" checked class="w-5 h-5 text-green-500 rounded focus:ring-green-500">
                        </div>
                        <button class="w-full text-left px-4 py-3 bg-white rounded-xl hover:bg-gray-100 transition-colors">
                            <i class="fas fa-key mr-2 text-green-500"></i>
                            <span class="font-medium text-slate-800">Cambiar Contraseña</span>
                        </button>
                    </div>
                </div>
                
                <!-- Información del Sistema -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">
                        <i class="fas fa-info-circle mr-2"></i>Información del Sistema
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Versión:</span>
                            <span class="font-medium text-slate-800">2.0.0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Última actualización:</span>
                            <span class="font-medium text-slate-800">Febrero 2026</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Pestañas activas:</span>
                            <span class="font-medium text-slate-800" id="config-tabs-info">-</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end mt-6">
                <button type="button" onclick="cerrarModalConfiguracion()" class="px-6 py-2 rounded-xl text-white hover:opacity-90" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    <?php
    // obtener estadísticas dinámicas para dashboard
    $db = Db::getConnect();
    $stats = [];
    if ($db) {
        $stmt = $db->query("SELECT COUNT(*) as total FROM instructores");
        $stats['instructores'] = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : ['total'=>0];
        $stmt = $db->query("SELECT COUNT(*) as total FROM fichas");
        $stats['fichas'] = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : ['total'=>0];
        $stmt = $db->query("SELECT COUNT(*) as total FROM sedes");
        $stats['sedes'] = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : ['total'=>0];
        $stmt = $db->query("SELECT COUNT(*) as total FROM ambientes");
        $stats['ambientes'] = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : ['total'=>0];
        // programas y competencias
        $stmt = $db->query("SELECT COUNT(*) as total FROM programas");
        $stats['programas'] = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : ['total'=>0];
        $stmt = $db->query("SELECT COUNT(*) as total FROM competencias");
        $stats['competencias'] = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : ['total'=>0];
    } else {
        $stats = ['instructores'=>['total'=>0],'fichas'=>['total'=>0],'sedes'=>['total'=>0],'ambientes'=>['total'=>0],'programas'=>['total'=>0],'competencias'=>['total'=>0]];
    }
    ?>
    <script>
        // Sidebar toggle: persist state in localStorage
        function toggleSidebar(){
            const sb = document.getElementById('sidebar');
            if(!sb) return;
            sb.classList.toggle('collapsed');
            localStorage.setItem('sidebar-collapsed', sb.classList.contains('collapsed'));
        }

        // Toggle sidebar en móviles
        function toggleMobileSidebar(){
            const sb = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            if(!sb || !overlay) return;
            
            sb.classList.toggle('mobile-open');
            overlay.classList.toggle('active');
            
            // Prevenir scroll del body cuando el sidebar está abierto
            if(sb.classList.contains('mobile-open')){
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }

        document.addEventListener('DOMContentLoaded', function(){
            // Inicializar contexto de rol para esta pestaña
            const roleContext = initRoleContext('coordinador');
            
            // Guardar en window para acceso global
            window.roleContext = roleContext;
            
            // Log de información (solo en desarrollo)
            if (window.location.hostname === 'localhost') {
                const info = roleContext.getActiveTabsInfo();
                console.log('📊 Dashboard Coordinador - Pestañas activas:', info);
                
                if (info.instructor > 0) {
                    console.log('ℹ️ Hay ' + info.instructor + ' pestaña(s) de Instructor abierta(s)');
                }
            }
            
            const sb = document.getElementById('sidebar');
            if(sb && localStorage.getItem('sidebar-collapsed') === 'true'){
                sb.classList.add('collapsed');
            }
        });

        let currentSection = '';
        let deleteIndex = null;
        let deleteSectionId = null;
        let editIndex = null;
        let editSectionId = null;
        
        <?php
            // preparar filas dinámicas para JS
            $jsPrograms = json_encode(array_map(function($p){
                return [$p['programa_id'], $p['codigo'],$p['nombre'],$p['nivel'],$p['duracion_meses'].' Meses'];
            }, $programasList));
            $jsCompetencies = json_encode(array_map(function($c){
                return [$c['competencia_id'], $c['codigo'],$c['descripcion'],$c['horas'],$c['tipo']];
            }, $competenciasList));
        ?>
        
        <?php
            // prepare dynamic rows for instructors using controller data
            $jsInstructors = json_encode(array_map(function($i){
                $name = trim($i['nombre'] . ' ' . $i['apellido']);
                $carga = $i['carga'] ? $i['carga'] . 'h' : '';
                return [$i['id'], $name, $i['especialidad'] ?? '', $carga, $i['estado'] ?? ''];
            }, $instructores));
            
            
            // prepare dynamic rows for fichas
            $jsFichas = json_encode(array_map(function($f){
                return [$f['ficha_id'], $f['codigo_ficha'], $f['programa'] ?? '', $f['num_aprendices'] ?? 0, ''];
            }, $fichas));
            
            // prepare dynamic rows for sedes
            $jsSedes = json_encode(array_map(function($s){
                return [$s['sede_id'], $s['nombre_sede'], '', '', 'Abierto'];
            }, $sedes));
            
            // prepare dynamic rows for ambientes
            $jsAmbientes = json_encode(array_map(function($a){
                return [$a['ambiente_id'], $a['nombre_ambiente'], '', $a['capacidad'] ?? '', $a['tipo'] ?? ''];
            }, $ambientes));
        ?>
        const contentData = {
            instructores: { 
                title: "Cuerpo de Instructores", 
                headers: ["Nombre", "Especialidad", "Carga Horaria", "Estado"], 
                rows: <?php echo $jsInstructors; ?>,
                stats: [
                    { label: "Total Instructores", value: "<?php echo $stats['instructores']['total'] ?? 0; ?>", icon: "fa-chalkboard-teacher" }
                ]
            },
            fichas: { 
                title: "Fichas de Formación", 
                headers: ["Código", "Programa", "Aprendices", "Fin Etapa Lectiva"], 
                rows: <?php echo $jsFichas; ?>,
                stats: [
                    { label: "Total Fichas", value: "<?php echo $stats['fichas']['total'] ?? 0; ?>", icon: "fa-layer-group" }
                ]
            },
            sedes: { 
                title: "Sedes del Centro", 
                headers: ["Nombre", "Dirección", "Encargado", "Estado"], 
                rows: <?php echo $jsSedes; ?>,
                stats: [
                    { label: "Total Sedes", value: "<?php echo $stats['sedes']['total'] ?? 0; ?>", icon: "fa-map-marker-alt" }
                ]
            },
            ambientes: { 
                title: "Ambientes de Aprendizaje", 
                headers: ["Nombre", "Sede", "Capacidad", "Tipo"], 
                rows: <?php echo $jsAmbientes; ?>,
                stats: [
                    { label: "Total Ambientes", value: "<?php echo $stats['ambientes']['total'] ?? 0; ?>", icon: "fa-door-open" }
                ]
            },
            programas: { 
                title: "Programas de Formación", 
                headers: ["Código", "Nombre del Programa", "Nivel", "Duración"], 
                rows: <?php echo $jsPrograms; ?>,
                stats: [
                    { label: "Total Programas", value: "<?php echo $stats['programas']['total'] ?? 0; ?>", icon: "fa-book" }
                ]
            },
            competencias: { 
                title: "Banco de Competencias", 
                headers: ["Código", "Descripción", "Horas", "Tipo"], 
                rows: <?php echo $jsCompetencies; ?>,
                stats: [
                    { label: "Total Competencias", value: "<?php echo $stats['competencias']['total'] ?? 0; ?>", icon: "fa-award" }
                ]
            }
        };

        function openModal() {
            if (!currentSection) {
                showNotification('Selecciona una sección antes de agregar', 'error');
                return;
            }
            const modalId = `modal-${currentSection}`;
            const modal = document.getElementById(modalId);
            if (modal) modal.classList.add('active');
        }

        function closeModal(section) {
            document.getElementById(`modal-${section}`).classList.remove('active');
        }

        function showSection(sectionId) {
            currentSection = sectionId;
            
            // Cerrar sidebar en móviles al cambiar de sección
            if(window.innerWidth <= 768){
                const sb = document.getElementById('sidebar');
                const overlay = document.querySelector('.sidebar-overlay');
                if(sb && overlay){
                    sb.classList.remove('mobile-open');
                    overlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            }
            
            // Lista de secciones que deben tener fondo verde cuando están activas
            const greenSections = ['aprendices', 'instructores', 'fichas', 'sedes', 'ambientes', 'programas', 'competencias', 'asignaciones'];
            
            // Actualizar UI de Navegación (no depender de `event` global)
            document.querySelectorAll('.nav-item').forEach(btn => {
                btn.classList.remove('active');
                btn.classList.remove('active-green');
            });
            
            // Buscar el botón que tiene el onclick con la sección y marcarlo
            const navBtn = Array.from(document.querySelectorAll('.nav-item')).find(btn => {
                const attr = btn.getAttribute('onclick') || '';
                return attr.indexOf(`showSection('${sectionId}')`) !== -1 || attr.indexOf(`showSection(\"${sectionId}\")`) !== -1;
            });
            
            if (navBtn) {
                if (greenSections.includes(sectionId)) {
                    navBtn.classList.add('active-green');
                } else {
                    navBtn.classList.add('active');
                }
            }

            // Ocultar todas las secciones
            document.querySelectorAll('.view-section').forEach(sec => sec.classList.remove('active'));

            if (sectionId === 'resumen') {
                document.getElementById('resumen').classList.add('active');
                document.getElementById('section-title').innerText = "Resumen Académico";
            } else if (sectionId === 'asignaciones') {
                document.getElementById('asignaciones').classList.add('active');
                document.getElementById('section-title').innerText = "Gestión de Asignaciones";
            } else {
                const data = contentData[sectionId];
                document.getElementById('section-title').innerText = data.title;
                document.getElementById('table-title').innerText = `Detalle de ${data.title}`;
                
                // Generar Tarjetas de Estadísticas
                const statsContainer = document.getElementById('stats-cards');
                if (data.stats && data.stats.length > 0) {
                    statsContainer.innerHTML = data.stats.map(stat => `
                        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 text-sm mb-1">${stat.label}</p>
                                    <p class="text-4xl font-bold text-green-500">${stat.value}</p>
                                </div>
                                <div class="bg-green-100 w-14 h-14 rounded-2xl flex items-center justify-center">
                                    <i class="fas ${stat.icon} text-green-500 text-2xl"></i>
                                </div>
                            </div>
                        </div>
                    `).join('');
                } else {
                    statsContainer.innerHTML = '';
                }
                
                // Generar Cabeceras
                const headerRow = document.getElementById('table-headers');
                headerRow.innerHTML = data.headers.map(h => `<th class="p-4">${h}</th>`).join('') + '<th class="p-4 text-center">Acciones</th>';

                // Generar Filas
                const body = document.getElementById('table-body');
                body.innerHTML = data.rows.map((row, index) => `
                    <tr class="border-b border-gray-50 hover:bg-green-50 transition cursor-pointer">
                        ${row.slice(1).map(cell => `<td class="p-4">${cell}</td>`).join('')}
                        <td class="p-4 text-center">
                            <button onclick="editItem('${sectionId}', ${index})" class="text-blue-500 hover:text-blue-700 mr-2" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteItem('${sectionId}', ${index})" class="text-red-500 hover:text-red-700" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');

                document.getElementById('table-view').classList.add('active');
            }
        }

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
            }
        }

        // Función para eliminar un registro
        function deleteItem(sectionId, index) {
            deleteSectionId = sectionId;
            deleteIndex = index;
            document.getElementById('modal-delete').classList.add('active');
        }

        function closeDeleteModal() {
            document.getElementById('modal-delete').classList.remove('active');
            deleteSectionId = null;
            deleteIndex = null;
        }

        function confirmDelete() {
            if (deleteSectionId && deleteIndex !== null) {
                const id = contentData[deleteSectionId].rows[deleteIndex][0];
                let action = '';
                
                // Mapear sección a acción del controlador
                switch(deleteSectionId) {
                    case 'sedes': action = 'eliminarSede'; break;
                    case 'ambientes': action = 'eliminarAmbiente'; break;
                    case 'instructores': action = 'eliminarInstructor'; break;
                    case 'fichas': action = 'eliminarFicha'; break;
                    case 'aprendices': action = 'eliminarAprendiz'; break;
                    case 'programas': action = 'eliminarPrograma'; break;
                    case 'competencias': action = 'eliminarCompetencia'; break;
                    default: 
                        showNotification('Acción no implementada para esta sección', 'error');
                        closeDeleteModal();
                        return;
                }
                
                // Redirigir al controlador para eliminación real
                window.location.href = `index.php?controlador=Administrador&accion=${action}&id=${id}`;
            }
        }

        // Función para editar un registro
        function editItem(sectionId, index) {
            editSectionId = sectionId;
            editIndex = index;
            
            const data = contentData[sectionId];
            const row = data.rows[index];
            const id = row[0];
            
            // Actualizar título del modal
            document.getElementById('edit-title').innerText = `Editar ${data.title.replace('Gestión de ', '').replace('Cuerpo de ', '').replace('Fichas de ', '').replace('Sedes del ', '').replace('Ambientes de ', '').replace('Programas de ', '').replace('Banco de ', '')}`;
            
            // Determinar la acción del controlador
            let action = '';
            switch(sectionId) {
                case 'sedes': action = 'actualizarSede'; break;
                case 'ambientes': action = 'actualizarAmbiente'; break;
                case 'instructores': action = 'actualizarInstructor'; break;
                case 'fichas': action = 'actualizarFicha'; break;
                case 'aprendices': action = 'actualizarAprendiz'; break;
                case 'programas': action = 'actualizarPrograma'; break;
                case 'competencias': action = 'actualizarCompetencia'; break;
            }
            
            const form = document.getElementById('edit-form');
            form.action = `index.php?controlador=Administrador&accion=${action}`;
            form.method = 'POST';
            
            // Generar campos del formulario según la sección
            const fieldsContainer = document.getElementById('edit-fields');
            fieldsContainer.innerHTML = `<input type="hidden" name="id" value="${id}">`;
            fieldsContainer.innerHTML += generateEditFields(sectionId, data.headers, row.slice(1));
            
            // Mostrar modal
            document.getElementById('modal-edit').classList.add('active');
        }

        function closeEditModal() {
            document.getElementById('modal-edit').classList.remove('active');
            editSectionId = null;
            editIndex = null;
        }

        function generateEditFields(sectionId, headers, rowValues) {
            let fields = '';
            
            // Mapeo de nombres de campos según la sección
            const fieldNames = {
                sedes: ['nombre_sede', 'direccion', 'encargado', 'telefono'],
                aprendices: ['documento', 'nombres', 'apellidos', 'ficha_id', 'email'],
                instructores: ['nombres', 'apellidos', 'especialidad', 'email'],
                fichas: ['codigo_ficha', 'programa', 'num_aprendices', 'fecha_fin_lectiva'],
                ambientes: ['nombre_ambiente', 'sede_id', 'capacidad', 'tipo'],
                programas: ['codigo', 'nombre', 'nivel', 'duracion_meses'],
                competencias: ['codigo', 'descripcion', 'horas', 'tipo']
            };
            
            const names = fieldNames[sectionId] || [];
            
            headers.forEach((header, i) => {
                const value = rowValues[i] || '';
                const name = names[i] || `field_${i}`;
                
                fields += `
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">${header}</label>
                        <input type="text" name="${name}" value="${value}" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#10b981] outline-none">
                    </div>
                `;
            });
            
            return fields;
        }

        // Eliminar el event listener antiguo que hacía actualizaciones locales
        // El formulario ahora hará un submit tradicional

        // --- Inicio: Funciones de notificaciones (campanita) ---
        // Renderiza la lista de notificaciones en el menú desplegable
        <?php
            if (session_status() === PHP_SESSION_NONE) { session_start(); }

            $serverNotifs = [];

            // Soporta un evento de login flexible en la sesión
            if (!empty($_SESSION['login_event'])) {
                $ev = $_SESSION['login_event'];
                $title = htmlspecialchars($ev); // Usar directamente el mensaje sin agregar "Bienvenido" de nuevo
                if (is_array($ev)) {
                    $title = 'Bienvenido';
                    if (!empty($ev['role'])) $title .= ' como ' . htmlspecialchars($ev['role']);
                    if (!empty($ev['user'])) $title .= ' - ' . htmlspecialchars($ev['user']);
                }
                $serverNotifs[] = ['id' => time(), 'title' => $title, 'time' => 'Ahora', 'read' => false, 'server' => true];
                unset($_SESSION['login_event']);
            }

            // Soporta una clave corta solo con el rol
            if (!empty($_SESSION['just_logged_in_role'])) {
                $role = htmlspecialchars($_SESSION['just_logged_in_role']);
                $serverNotifs[] = ['id' => time() + 1, 'title' => "Bienvenido como $role", 'time' => 'Ahora', 'read' => false, 'server' => true];
                unset($_SESSION['just_logged_in_role']);
            }

            // Notificaciones por defecto en cliente
            $defaultNotifs = [
                ['id' => 1, 'title' => 'Bienvenido al panel', 'time' => 'Ahora', 'read' => false],
                ['id' => 2, 'title' => 'Tienes 3 fichas nuevas', 'time' => '2h', 'read' => false],
                ['id' => 3, 'title' => 'Registro actualizado correctamente', 'time' => '1d', 'read' => true]
            ];

            $allNotifs = array_merge($serverNotifs, $defaultNotifs);
        ?>
        const _notifications = <?php echo json_encode($allNotifs, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;

        function renderNotifications() {
            const list = document.getElementById('notifList');
            const count = document.getElementById('notifCount');
            const dot = document.getElementById('notifDot');
            
            console.log('Renderizando notificaciones...', _notifications);
            
            if (!list || !count) {
                console.error('Elementos no encontrados:', { list, count });
                return;
            }

            list.innerHTML = '';
            const unread = _notifications.filter(n => !n.read).length;
            
            console.log('Notificaciones no leídas:', unread);
            
            if (unread > 0) {
                count.style.display = 'flex';
                count.textContent = unread;
            } else {
                count.style.display = 'none';
            }

            // Mostrar punto rojo si hay notificaciones del servidor sin leer
            if (dot) {
                const serverUnread = _notifications.some(n => n.server && !n.read);
                dot.style.display = serverUnread ? 'block' : 'none';
            }

            if (_notifications.length === 0) {
                list.innerHTML = '<div class="notif-item" style="font-size:13px;">No hay nuevas notificaciones</div>';
                return;
            }

            _notifications.forEach(n => {
                const item = document.createElement('div');
                item.className = 'notif-item';
                item.style.fontSize = '13px';
                item.innerHTML = `
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <p class="font-semibold mb-1">${n.title}</p>
                            <p class="text-xs opacity-70">${n.time}</p>
                        </div>
                        <div class="ml-2">
                            ${n.read ? '<span class="text-xs opacity-60">Leído</span>' : '<span class="text-xs bg-white bg-opacity-20 px-2 py-1 rounded">Nuevo</span>'}
                        </div>
                    </div>
                `;
                item.addEventListener('click', () => {
                    n.read = true;
                    renderNotifications();
                });
                list.appendChild(item);
            });
            
            console.log('Notificaciones renderizadas:', list.children.length);
        }

        function toggleNotifMenu() {
            const btn = document.getElementById('notifBtn');
            const menu = document.getElementById('notifMenu');
            const calendarMenu = document.getElementById('calendarMenu');
            const sidebar = document.getElementById('sidebar');
            
            console.log('Toggle notif menu:', { btn, menu, calendarMenu });
            
            if (!btn || !menu) {
                console.error('Botón o menú no encontrado');
                return;
            }
            
            // Cerrar el menú del calendario si está abierto
            if (calendarMenu && !calendarMenu.classList.contains('hidden')) {
                calendarMenu.classList.add('hidden');
            }
            
            // Cerrar sidebar en móviles cuando se abre el menú de notificaciones
            if (window.innerWidth <= 768 && sidebar && !sidebar.classList.contains('collapsed')) {
                sidebar.classList.add('collapsed');
            }
            
            const isHidden = menu.classList.contains('hidden');
            console.log('Menu está oculto:', isHidden);
            
            if (isHidden) {
                menu.classList.remove('hidden');
                btn.setAttribute('aria-expanded', 'true');
                console.log('Mostrando menú de notificaciones');
                // Marcar notificaciones como leídas al abrir el menú
                try {
                    _notifications.forEach(n => n.read = true);
                } catch (e) { 
                    console.error('Error marcando notificaciones:', e);
                }
                if (typeof renderNotifications === 'function') renderNotifications();
            } else {
                menu.classList.add('hidden');
                btn.setAttribute('aria-expanded', 'false');
                console.log('Ocultando menú de notificaciones');
            }
        }

        function toggleCalendarMenu() {
            const btn = document.getElementById('calendarBtn');
            const calendarSection = document.getElementById('calendarSection');
            const notifMenu = document.getElementById('notifMenu');
            
            console.log('toggleCalendarMenu llamado', { btn, calendarSection });
            
            if (!btn || !calendarSection) {
                console.error('Elementos no encontrados:', { btn, calendarSection });
                return;
            }
            
            // Cerrar el menú de notificaciones si está abierto
            if (notifMenu && !notifMenu.classList.contains('hidden')) {
                notifMenu.classList.add('hidden');
            }
            
            // Toggle del calendario principal
            const isHidden = calendarSection.style.display === 'none' || calendarSection.style.display === '';
            console.log('Estado actual del calendario:', { display: calendarSection.style.display, isHidden });
            
            if (isHidden) {
                calendarSection.style.display = 'block';
                btn.setAttribute('aria-expanded', 'true');
                btn.classList.add('text-[#10b981]');
                
                console.log('Mostrando calendario');
                
                // Renderizar el calendario si existe
                if (window.calendar && typeof window.calendar.render === 'function') {
                    setTimeout(() => {
                        try {
                            window.calendar.render();
                            console.log('Calendario renderizado');
                        } catch(e) {
                            console.error('Error al renderizar calendario:', e);
                        }
                    }, 100);
                }
                
                // Scroll suave hacia el calendario
                setTimeout(() => {
                    calendarSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 150);
            } else {
                calendarSection.style.display = 'none';
                btn.setAttribute('aria-expanded', 'false');
                btn.classList.remove('text-[#10b981]');
                console.log('Ocultando calendario');
            }
        }

        document.addEventListener('click', function(e) {
            const notifMenu = document.getElementById('notifMenu');
            const notifBtn = document.getElementById('notifBtn');
            
            // Cerrar menú de notificaciones
            if (notifMenu && notifBtn) {
                if (!notifBtn.contains(e.target) && !notifMenu.contains(e.target)) {
                    notifMenu.classList.add('hidden');
                }
            }
        });

        document.addEventListener('DOMContentLoaded', function(){
            // Inicializar render
            renderNotifications();
            const notifBtn = document.getElementById('notifBtn');
            const calendarBtn = document.getElementById('calendarBtn');
            const markAll = document.getElementById('markAllRead');
            
            if (notifBtn) notifBtn.addEventListener('click', function(e){ e.stopPropagation(); toggleNotifMenu(); });
            if (calendarBtn) calendarBtn.addEventListener('click', function(e){ e.stopPropagation(); toggleCalendarMenu(); });
            if (markAll) markAll.addEventListener('click', function(e){
                e.stopPropagation();
                _notifications.forEach(n => n.read = true);
                renderNotifications();
            });

            // Perfil: toggle menú
            const profileToggle = document.getElementById('profileToggle');
            const profileMenu = document.getElementById('profileMenu');
            if (profileToggle && profileMenu) {
                profileToggle.addEventListener('click', function(e){ e.stopPropagation(); profileMenu.classList.toggle('hidden'); profileToggle.setAttribute('aria-expanded', profileMenu.classList.contains('hidden') ? 'false' : 'true'); });
                document.addEventListener('click', function(e){ if (!profileMenu.contains(e.target) && !profileToggle.contains(e.target)) profileMenu.classList.add('hidden'); });
            }
        });

        // --- Fin: Funciones de notificaciones (campanita) ---

        // Función para mostrar notificaciones (toast)
        function showNotification(message, type) {
            const bg = type === 'success' ? 'linear-gradient(90deg,#28a745,#2ecc71)' : 'linear-gradient(90deg,#e74c3c,#e74c3c)';
            Toastify({
                text: message,
                duration: 3000,
                close: true,
                gravity: 'top',
                position: 'right',
                style: { background: bg, color: '#fff', boxShadow: '0 6px 18px rgba(0,0,0,0.08)' }
            }).showToast();
        }

        // Mostrar toasts inmediatos para notificaciones enviadas por el servidor (ej. login)
        (function showServerLoginToasts() {
            try {
                if (typeof _notifications !== 'undefined') {
                    const serverNotifs = _notifications.filter(n => n.server);
                    serverNotifs.forEach(n => {
                        // Mostrar como success por defecto
                        showNotification(n.title, 'success');
                        // NO marcar como leída aún; el punto debe mostrarse hasta que el usuario abra el menú
                    });
                    // actualizar UI (badge/dot)
                    if (typeof renderNotifications === 'function') renderNotifications();
                }
            } catch (e) {
                // Silenciar errores para no romper la página
                console.error('Error mostrando toasts de servidor:', e);
            }
        })();
    </script>
    <script>
        // Calendar: FullCalendar init and localStorage persistence
        (function(){
            let calendar;
            const apiUrl = '/Mini-Proyecto/api/calendar_events.php';

            async function loadEvents(){
                try {
                    const r = await fetch(apiUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    const data = await r.json();
                    return Array.isArray(data) ? data : [];
                } catch(e){
                    console.error('Error al cargar eventos:', e);
                    return [];
                }
            }

            function openAddEventModal(startDate, endDate){
                const modal = document.getElementById('modal-add-event');
                if(!modal) return;
                
                // Limpiar el formulario
                document.getElementById('add-event-form').reset();
                
                // Cargar todos los datos necesarios
                cargarInstructoresAsignacion();
                cargarFichasAsignacion();
                cargarAmbientesAsignacion();
                cargarExperienciasAsignacion();
                
                if(startDate){
                    const start = startDate.toISOString ? startDate.toISOString().slice(0,10) : startDate;
                    document.getElementById('event-start-range').value = start;
                    
                    if(endDate){
                        // Restar un día a la fecha fin porque FullCalendar usa fechas exclusivas
                        const endDateObj = new Date(endDate);
                        endDateObj.setDate(endDateObj.getDate() - 1);
                        const end = endDateObj.toISOString().slice(0,10);
                        document.getElementById('event-end').value = end;
                    } else {
                        document.getElementById('event-end').value = start;
                    }
                }
                
                // Establecer horas por defecto
                document.getElementById('event-start-time').value = '07:00';
                document.getElementById('event-end-time').value = '18:00';
                
                // Marcar días laborables por defecto (Lunes a Viernes)
                for(let i = 1; i <= 5; i++) {
                    const checkbox = document.getElementById(`day-${i}`);
                    if(checkbox) checkbox.checked = true;
                }
                // Desmarcar sábado por defecto
                const satCheckbox = document.getElementById('day-6');
                if(satCheckbox) satCheckbox.checked = false;
                
                // hide delete button and clear id when opening for create
                const delBtn = document.getElementById('delete-event-btn'); if (delBtn) delBtn.classList.add('hidden');
                const idField = document.getElementById('event-id'); if (idField) idField.value = '';
                modal.classList.add('active');
            }
            
            // Función para cargar instructores en el select de asignación
            function cargarInstructoresAsignacion() {
                const selectInstructor = document.getElementById('asig-instructor');
                if (!selectInstructor) {
                    console.error('Select de instructor no encontrado');
                    return;
                }
                
                console.log('Cargando instructores...');
                fetch('api/datos_asignacion.php?action=listar_instructores')
                    .then(response => {
                        console.log('Respuesta instructores:', response);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Datos instructores:', data);
                        if (data.success && data.instructores) {
                            selectInstructor.innerHTML = '<option value="">-- Seleccione un instructor --</option>';
                            data.instructores.forEach(instructor => {
                                const option = document.createElement('option');
                                option.value = instructor.id;
                                const registroText = instructor.registro ? ` (Reg: ${instructor.registro})` : '';
                                option.textContent = `${instructor.nombre} ${instructor.apellido}${registroText}`;
                                selectInstructor.appendChild(option);
                            });
                            console.log(`${data.instructores.length} instructores cargados`);
                        } else {
                            console.error('No se encontraron instructores o error en la respuesta');
                        }
                    })
                    .catch(error => console.error('Error al cargar instructores:', error));
            }
            
            // Función para cargar fichas
            function cargarFichasAsignacion() {
                const selectFicha = document.getElementById('asig-ficha');
                if (!selectFicha) {
                    console.error('Select de ficha no encontrado');
                    return;
                }
                
                console.log('Cargando fichas...');
                fetch('api/datos_asignacion.php?action=listar_fichas')
                    .then(response => {
                        console.log('Respuesta fichas:', response);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Datos fichas:', data);
                        if (data.success && data.fichas) {
                            selectFicha.innerHTML = '<option value="">-- Seleccione una ficha --</option>';
                            data.fichas.forEach(ficha => {
                                const option = document.createElement('option');
                                option.value = ficha.ficha_id;
                                option.textContent = `${ficha.codigo_ficha} - ${ficha.programa}`;
                                selectFicha.appendChild(option);
                            });
                            console.log(`${data.fichas.length} fichas cargadas`);
                        } else {
                            console.error('No se encontraron fichas o error en la respuesta');
                        }
                    })
                    .catch(error => console.error('Error al cargar fichas:', error));
            }
            
            // Función para cargar ambientes
            function cargarAmbientesAsignacion() {
                const selectAmbiente = document.getElementById('asig-ambiente');
                if (!selectAmbiente) {
                    console.error('Select de ambiente no encontrado');
                    return;
                }
                
                console.log('Cargando ambientes...');
                fetch('api/datos_asignacion.php?action=listar_ambientes')
                    .then(response => {
                        console.log('Respuesta ambientes:', response);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Datos ambientes:', data);
                        if (data.success && data.ambientes) {
                            selectAmbiente.innerHTML = '<option value="">-- Seleccione un ambiente --</option>';
                            data.ambientes.forEach(ambiente => {
                                const option = document.createElement('option');
                                option.value = ambiente.ambiente_id;
                                option.textContent = `${ambiente.nombre_ambiente} (${ambiente.tipo})`;
                                selectAmbiente.appendChild(option);
                            });
                            console.log(`${data.ambientes.length} ambientes cargados`);
                        } else {
                            console.error('No se encontraron ambientes o error en la respuesta');
                        }
                    })
                    .catch(error => console.error('Error al cargar ambientes:', error));
            }
            
            // Función para cargar experiencias/competencias
            function cargarExperienciasAsignacion() {
                const selectExperiencia = document.getElementById('asig-competencia');
                if (!selectExperiencia) {
                    console.error('Select de competencia no encontrado');
                    return;
                }
                
                console.log('Cargando experiencias/competencias...');
                fetch('api/datos_asignacion.php?action=listar_experiencias')
                    .then(response => {
                        console.log('Respuesta experiencias:', response);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Datos experiencias:', data);
                        if (data.success && data.experiencias) {
                            selectExperiencia.innerHTML = '<option value="">-- Seleccione una competencia --</option>';
                            data.experiencias.forEach(exp => {
                                const option = document.createElement('option');
                                option.value = exp.experiencia_id;
                                option.textContent = exp.nombre_experiencia;
                                selectExperiencia.appendChild(option);
                            });
                            console.log(`${data.experiencias.length} experiencias cargadas`);
                        } else {
                            console.error('No se encontraron experiencias o error en la respuesta');
                        }
                    })
                    .catch(error => console.error('Error al cargar experiencias:', error));
            }
            
            // Hacer la función global
            window.openAddEventModal = openAddEventModal;

            window.closeAddEventModal = function(){
                const modal = document.getElementById('modal-add-event');
                if(modal) modal.classList.remove('active');
                document.getElementById('add-event-form').reset();
                const delBtn = document.getElementById('delete-event-btn'); if (delBtn) delBtn.classList.add('hidden');
                const idField = document.getElementById('event-id'); if (idField) idField.value = '';
            }

            document.addEventListener('DOMContentLoaded', function(){
                const calendarEl = document.getElementById('calendarEl');
                if (!calendarEl || typeof FullCalendar === 'undefined') {
                    console.error('FullCalendar no disponible o elemento no encontrado');
                    return;
                }

                calendar = new FullCalendar.Calendar(calendarEl, {
                    locale: 'es',
                    initialView: 'dayGridMonth',
                    height: 'auto',
                    contentHeight: 'auto',
                    aspectRatio: 1.5,
                    firstDay: 1, // Lunes como primer día
                    slotMinTime: '06:00:00', // Horario desde las 6 AM
                    slotMaxTime: '22:00:00', // Horario hasta las 10 PM
                    // businessHours deshabilitado para mantener colores uniformes en tema oscuro
                    // businessHours: {
                    //     daysOfWeek: [1, 2, 3, 4, 5], // Lunes a Viernes
                    //     startTime: '06:00',
                    //     endTime: '18:00'
                    // },
                    // selectConstraint: 'businessHours',
                    selectable: true,
                    selectMirror: true,
                    select: function(info) {
                        openAddEventModal(info.start, info.end);
                    },
                    buttonText: {
                        today: 'Hoy',
                        month: 'Mes',
                        week: 'Semana',
                        day: 'Día'
                    },
                    headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' },
                    dateClick: function(info){
                        // Si el usuario hace clic en un día que ya contiene eventos,
                        // no mostramos el formulario de creación sino un modal para
                        // eliminar únicamente las ocurrencias de esa fecha.
                        const clicked = info.date;
                        const all = calendar.getEvents();
                        const sameDay = all.filter(ev => {
                            if (!ev.start) return false;
                            // comparar sólo fecha, ignorar hora
                            return ev.start.toDateString() === clicked.toDateString();
                        });
                        if (sameDay.length > 0) {
                            // preparar objeto de eliminación similar a los usados al
                            // pulsar el botón "Eliminar" dentro del modal de edición
                            window.eventoAEliminar = {
                                id: sameDay[0].id,
                                groupId: sameDay[0].extendedProps.groupId || sameDay[0].groupId || null,
                                events: sameDay,
                                totalEvents: sameDay.length
                            };
                            document.getElementById('total-eventos-eliminar').textContent = sameDay.length;
                            document.getElementById('modal-eliminar-evento').classList.add('active');
                        } else {
                            openAddEventModal(info.date);
                        }
                    },
                    eventClick: function(info){
                        // Si el usuario hace clic directamente en un evento, sólo
                        // queremos borrar las ocurrencias de ese día, no todas las
                        // fechas de la asignación. Por eso filtramos por fecha
                        // igual que en dateClick.
                        const ev = info.event;
                        const clickedDate = ev.start ? ev.start : null;
                        const allEvents = calendar.getEvents();
                        let eventsSameDay = [];
                        if (clickedDate) {
                            eventsSameDay = allEvents.filter(x => {
                                if (!x.start) return false;
                                return x.start.toDateString() === clickedDate.toDateString();
                            });
                        }
                        if (eventsSameDay.length === 0) {
                            eventsSameDay = [ev];
                        }
                        const total = eventsSameDay.length;
                        window.eventoAEliminar = {
                            id: ev.id,
                            groupId: ev.extendedProps.groupId || ev.groupId || null,
                            events: eventsSameDay,
                            totalEvents: total
                        };
                        document.getElementById('total-eventos-eliminar').textContent = total;
                        document.getElementById('modal-eliminar-evento').classList.add('active');
                    },
                    events: function(info, successCallback, failureCallback) {
                        fetch('api/calendar_events.php')
                            .then(response => response.json())
                            .then(data => {
                                console.log('Eventos cargados:', data);
                                successCallback(data);
                            })
                            .catch(error => {
                                console.error('Error cargando eventos:', error);
                                failureCallback(error);
                            });
                    }
                });

                // Guardar referencia global
                window.calendar = calendar;
                
                // Renderizar con un pequeño delay para asegurar que el DOM esté listo
                setTimeout(() => {
                    calendar.render();
                    console.log('Calendario renderizado correctamente');
                }, 100);
                
                // cargar eventos desde API y agregarlos al calendario
                loadEvents().then(events => { 
                    if (Array.isArray(events) && events.length) {
                        calendar.addEventSource(events);
                    }
                });

                // button open modal
                const openBtn = document.getElementById('openAddEvent');
                if(openBtn) openBtn.addEventListener('click', function(){ 
                    openAddEventModal(); 
                });

                // form submit -> create or update via API
                const form = document.getElementById('add-event-form');
                form.addEventListener('submit', async function(e){
                    e.preventDefault();
                    
                    // Obtener valores de los campos del formulario
                    const ficha = document.getElementById('asig-ficha').value;
                    const instructor = document.getElementById('asig-instructor').value;
                    const ambiente = document.getElementById('asig-ambiente').value;
                    const competencia = document.getElementById('asig-competencia').value;
                    const start = document.getElementById('event-start-range').value;
                    const end = document.getElementById('event-end').value || start;
                    const startTime = document.getElementById('event-start-time').value;
                    const endTime = document.getElementById('event-end-time').value;
                    const id = document.getElementById('event-id').value || null;
                    
                    // Crear título del evento basado en los datos seleccionados
                    const fichaText = document.getElementById('asig-ficha').selectedOptions[0]?.text || 'Ficha';
                    const instructorText = document.getElementById('asig-instructor').selectedOptions[0]?.text || 'Instructor';
                    const ambienteText = document.getElementById('asig-ambiente').selectedOptions[0]?.text || 'Ambiente';
                    const title = `${fichaText} - ${instructorText} - ${ambienteText}`;
                    
                    // Obtener días seleccionados
                    const selectedDays = [];
                    for(let i = 1; i <= 6; i++) {
                        const checkbox = document.getElementById(`day-${i}`);
                        if(checkbox && checkbox.checked) {
                            selectedDays.push(i);
                        }
                    }
                    
                    // Validaciones
                    if(!ficha || !instructor || !ambiente || !competencia){ 
                        showNotification('Todos los campos marcados con * son obligatorios','error'); 
                        return; 
                    }
                    
                    if(!start){ 
                        showNotification('La fecha de inicio es obligatoria','error'); 
                        return; 
                    }
                    
                    if(selectedDays.length === 0) {
                        showNotification('Selecciona al menos un día de la semana','error');
                        return;
                    }

                    // Crear eventos para cada día seleccionado en el rango de fechas
                    const startDate = new Date(start + 'T00:00:00');
                    const endDate = new Date(end + 'T00:00:00');
                    const events = [];
                    
                    // Generar un ID de grupo único para esta asignación
                    const groupId = 'group_' + Date.now();
                    
                    let currentDate = new Date(startDate);
                    while(currentDate <= endDate) {
                        const dayOfWeek = currentDate.getDay();
                        // Convertir domingo (0) a 7 para comparar con nuestros checkboxes (1-6)
                        const adjustedDay = dayOfWeek === 0 ? 7 : dayOfWeek;
                        
                        if(selectedDays.includes(adjustedDay)) {
                                const eventDate = currentDate.toISOString().slice(0,10);
                                events.push({
                                    title: title,
                                    start: eventDate + 'T' + startTime,
                                    end: eventDate + 'T' + endTime,
                                    groupId: groupId
                                });
                            }
                            
                            currentDate.setDate(currentDate.getDate() + 1);
                        }
                        
                        if(events.length === 0) {
                            showNotification('No hay días válidos en el rango seleccionado','error');
                            return;
                        }

                        // Primero, guardar la asignación en la tabla asignaciones
                        const diasSemanaTexto = selectedDays.map(d => {
                            const dias = ['', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                            return dias[d];
                        }).join(', ');

                        const asignacionData = {
                            action: 'crear',
                            ficha_id: ficha,
                            instructor_id: instructor,
                            experiencia_id: competencia,
                            ambiente_id: ambiente,
                            fecha_inicio: start,
                            fecha_fin: end,
                            hora_inicio: startTime,
                            hora_fin: endTime,
                            dias_semana: diasSemanaTexto,
                            estado: 'Programada'
                        };

                        try {
                            // Guardar asignación
                            const asignacionResp = await fetch('api/asignaciones.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                                body: JSON.stringify(asignacionData)
                            });

                            if (!asignacionResp.ok) {
                                throw new Error('Error al guardar la asignación');
                            }

                            const asignacionJson = await asignacionResp.json();
                            if (!asignacionJson.success) {
                                throw new Error(asignacionJson.error || 'Error al guardar la asignación');
                            }

                            // Guardar todos los eventos en el calendario
                            let savedCount = 0;
                            for(const event of events) {
                                const resp = await fetch(apiUrl, {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                                    body: JSON.stringify(event)
                                });
                                
                                if (resp.ok) {
                                    const json = await resp.json();
                                    if (json && json.success && json.event) {
                                        // Agregar el groupId y asegurar formato correcto para FullCalendar
                                        const calendarEvent = {
                                            id: json.event.id,
                                            title: event.title,
                                            start: event.start,
                                            end: event.end,
                                            groupId: groupId,
                                            backgroundColor: '#10b981',
                                            borderColor: '#059669',
                                            textColor: '#ffffff'
                                        };
                                        window.calendar.addEvent(calendarEvent);
                                        savedCount++;
                                    }
                                }
                            }
                            
                            closeAddEventModal();
                            showNotification(`Asignación creada con ${savedCount} evento(s) en el calendario`,'success');
                            
                            // Recargar la tabla de asignaciones
                            cargarAsignaciones();
                            
                        } catch (err) {
                            console.error('Error al guardar asignación/eventos', err);
                            showNotification('Error al guardar: ' + (err.message || err),'error');
                        }
                });

                // delete handler
                const deleteBtn = document.getElementById('delete-event-btn');
                if (deleteBtn) deleteBtn.addEventListener('click', async function(){
                    const id = document.getElementById('event-id').value;
                    if (!id) return;
                    
                    // Obtener el evento actual para acceder a su groupId
                    const currentEvent = window.calendar.getEventById(String(id));
                    if (!currentEvent) return;
                    
                    const groupId = currentEvent.extendedProps.groupId || currentEvent.groupId;
                    
                    // Si tiene groupId, mostrar modal personalizado
                    if (groupId) {
                        const allEvents = window.calendar.getEvents();
                        const eventsInGroup = allEvents.filter(ev => {
                            const evGroupId = ev.extendedProps.groupId || ev.groupId;
                            return evGroupId === groupId;
                        });
                        
                        const totalEvents = eventsInGroup.length;
                        
                        // Guardar información para usar en la confirmación
                        window.eventoAEliminar = {
                            id: id,
                            groupId: groupId,
                            events: eventsInGroup,
                            totalEvents: totalEvents
                        };
                        
                        // Mostrar modal personalizado
                        document.getElementById('total-eventos-eliminar').textContent = totalEvents;
                        document.getElementById('modal-eliminar-evento').classList.add('active');
                        
                    } else {
                        // Si no tiene groupId, eliminar solo el evento individual con modal
                        window.eventoAEliminar = {
                            id: id,
                            groupId: null,
                            events: [currentEvent],
                            totalEvents: 1
                        };
                        
                        document.getElementById('total-eventos-eliminar').textContent = 1;
                        document.getElementById('modal-eliminar-evento').classList.add('active');
                    }
                });
            });
        })();
    </script>
    <script>
        // Función para cerrar el modal de eliminar evento
        function cerrarModalEliminarEvento() {
            document.getElementById('modal-eliminar-evento').classList.remove('active');
            window.eventoAEliminar = null;
        }
        
        // Función para confirmar la eliminación del evento
        async function confirmarEliminarEvento() {
            if (!window.eventoAEliminar) return;
            
            const { id, groupId, events, totalEvents } = window.eventoAEliminar;
            const apiUrl = '/Mini-Proyecto/api/calendar_events.php';
            
            try {
                // Eliminar todos los eventos del grupo
                let deletedCount = 0;
                for (const ev of events) {
                    try {
                        const resp = await fetch(apiUrl + '?id=' + encodeURIComponent(ev.id), { 
                            method: 'DELETE', 
                            headers: { 'X-Requested-With': 'XMLHttpRequest' } 
                        });
                        
                        if (resp.ok) {
                            const json = await resp.json();
                            if (json && json.success) {
                                ev.remove();
                                deletedCount++;
                            }
                        }
                    } catch (err) {
                        console.error('Error eliminando evento individual:', err);
                    }
                }
                
                // Cerrar modales
                cerrarModalEliminarEvento();
                const modalAddEvent = document.getElementById('modal-add-event');
                if (modalAddEvent) modalAddEvent.classList.remove('active');
                
                // Mostrar notificación
                showNotification(`${deletedCount} evento(s) eliminado(s) correctamente`, 'success');
                
                // Recargar la lista de asignaciones
                if (typeof cargarAsignaciones === 'function') {
                    cargarAsignaciones();
                }
                
            } catch (err) {
                console.error('Error al eliminar eventos:', err);
                showNotification('Error al eliminar eventos: ' + (err.message || err), 'error');
            }
        }
    </script>
    <script>
        // Gestión de Asignaciones - Cargar dinámicamente desde API
        let asignacionesData = [];
        let asignacionActual = null;

        // Función para cargar asignaciones desde la API
        async function cargarAsignaciones() {
            try {
                const response = await fetch('api/asignaciones.php?action=listar');
                const data = await response.json();
                
                if (data.success && Array.isArray(data.asignaciones)) {
                    asignacionesData = data.asignaciones;
                    renderizarTablaAsignaciones();
                } else {
                    console.error('Error al cargar asignaciones:', data.error || 'Respuesta inválida');
                    mostrarMensajeTabla('Error al cargar asignaciones');
                }
            } catch (error) {
                console.error('Error al cargar asignaciones:', error);
                mostrarMensajeTabla('Error de conexión al cargar asignaciones');
            }
        }

        // Función para renderizar la tabla de asignaciones
        function renderizarTablaAsignaciones() {
            const tbody = document.getElementById('tabla-asignaciones-body');
            
            if (!tbody) {
                console.error('No se encontró el tbody de la tabla');
                return;
            }

            if (asignacionesData.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-3xl mb-2"></i>
                            <p>No hay asignaciones registradas</p>
                            <button onclick="openModalAsignacion()" class="mt-3 bg-green-500 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-green-600 transition">
                                <i class="fas fa-plus mr-2"></i>Crear Primera Asignación
                            </button>
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = asignacionesData.map(asignacion => {
                // Determinar color del estado
                let estadoClass = 'bg-yellow-100 text-yellow-800';
                if (asignacion.estado === 'En Curso') estadoClass = 'bg-blue-100 text-blue-800';
                if (asignacion.estado === 'Finalizada') estadoClass = 'bg-green-100 text-green-800';
                if (asignacion.estado === 'Cancelada') estadoClass = 'bg-red-100 text-red-800';

                // Formatear fecha
                const fechaInicio = new Date(asignacion.fecha_inicio + 'T00:00:00').toLocaleDateString('es-CO', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });

                return `
                    <tr class="hover:bg-green-50 transition cursor-pointer">
                        <td class="px-6 py-4">
                            <span class="text-sm font-semibold text-green-500">${asignacion.codigo_ficha || 'N/A'}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-700">${asignacion.instructor_nombre || 'N/A'}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600">${asignacion.nombre_ambiente || 'Sin asignar'}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600">${asignacion.competencia || 'N/A'}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600">${fechaInicio}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="${estadoClass} px-3 py-1 rounded-full text-xs font-semibold">
                                ${asignacion.estado}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex justify-center gap-2">
                                <button onclick="verAsignacion(${asignacion.asignacion_id})" 
                                        class="bg-green-500 text-white px-3 py-1 rounded-lg text-xs font-semibold hover:bg-green-600 transition">
                                    Ver
                                </button>
                                <button onclick="editarAsignacion(${asignacion.asignacion_id})" 
                                        class="bg-green-500 text-white px-3 py-1 rounded-lg text-xs font-semibold hover:bg-green-600 transition">
                                    Editar
                                </button>
                                <button onclick="eliminarAsignacion(${asignacion.asignacion_id})" 
                                        class="bg-red-600 text-white px-3 py-1 rounded-lg text-xs font-semibold hover:bg-red-700 transition">
                                    Eliminar
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // Función para mostrar mensaje en la tabla
        function mostrarMensajeTabla(mensaje) {
            const tbody = document.getElementById('tabla-asignaciones-body');
            if (tbody) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                            <p>${mensaje}</p>
                        </td>
                    </tr>
                `;
            }
        }

        function openModalAsignacion() {
            document.getElementById('modal-nueva-asignacion').classList.add('active');
        }

        function verAsignacion(id) {
            const asignacion = asignacionesData.find(a => a.asignacion_id === id);
            if (!asignacion) return;

            // Formatear fechas
            const fechaInicio = new Date(asignacion.fecha_inicio + 'T00:00:00').toLocaleDateString('es-CO', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            const fechaFin = new Date(asignacion.fecha_fin + 'T00:00:00').toLocaleDateString('es-CO', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });

            const contenido = document.getElementById('contenido-ver-asignacion');
            contenido.innerHTML = `
                <div class="bg-gray-50 p-4 rounded-xl">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Ficha</p>
                            <p class="text-sm font-semibold text-gray-800">${asignacion.codigo_ficha || 'N/A'}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Programa</p>
                            <p class="text-sm font-semibold text-gray-800">${asignacion.programa || 'N/A'}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Instructor</p>
                            <p class="text-sm font-semibold text-gray-800">${asignacion.instructor_nombre || 'N/A'}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Ambiente</p>
                            <p class="text-sm font-semibold text-gray-800">${asignacion.nombre_ambiente || 'Sin asignar'}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Competencia</p>
                            <p class="text-sm font-semibold text-gray-800">${asignacion.competencia || 'N/A'}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Estado</p>
                            <p class="text-sm font-semibold text-gray-800">${asignacion.estado}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Fecha Inicio</p>
                            <p class="text-sm font-semibold text-gray-800">${fechaInicio}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Fecha Fin</p>
                            <p class="text-sm font-semibold text-gray-800">${fechaFin}</p>
                        </div>
                        ${asignacion.hora_inicio ? `
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Horario</p>
                            <p class="text-sm font-semibold text-gray-800">${asignacion.hora_inicio} - ${asignacion.hora_fin}</p>
                        </div>
                        ` : ''}
                        ${asignacion.dias_semana ? `
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Días</p>
                            <p class="text-sm font-semibold text-gray-800">${asignacion.dias_semana}</p>
                        </div>
                        ` : ''}
                        ${asignacion.observaciones ? `
                        <div class="col-span-2">
                            <p class="text-xs text-gray-500 mb-1">Observaciones</p>
                            <p class="text-sm font-semibold text-gray-800">${asignacion.observaciones}</p>
                        </div>
                        ` : ''}
                    </div>
                </div>
            `;
            document.getElementById('modal-ver-asignacion').classList.add('active');
        }

        function editarAsignacion(id) {
            // buscar la asignación usando la clave real devuelta por la API
            const asignacion = asignacionesData.find(a => a.asignacion_id === id);
            if (!asignacion) return;

            // rellenar el formulario con los mismos campos que se muestran en el modal "ver"
            document.getElementById('edit-asig-id').value = asignacion.asignacion_id;
            document.getElementById('edit-asig-ficha').value = asignacion.codigo_ficha || '';
            document.getElementById('edit-asig-instructor').value = asignacion.instructor_nombre || '';
            document.getElementById('edit-asig-ambiente').value = asignacion.nombre_ambiente || '';
            document.getElementById('edit-asig-competencia').value = asignacion.competencia || '';
            document.getElementById('edit-asig-fecha').value = asignacion.fecha_inicio || '';
            document.getElementById('edit-asig-estado').value = asignacion.estado || '';

            document.getElementById('modal-editar-asignacion').classList.add('active');
        }

        function eliminarAsignacion(id) {
            asignacionActual = id;
            document.getElementById('modal-eliminar-asignacion').classList.add('active');
        }

        function confirmarEliminarAsignacion() {
            if (asignacionActual) {
                showNotification('Asignación eliminada exitosamente', 'success');
                cerrarModalAsignacion('eliminar');
                asignacionActual = null;
            }
        }

        function cerrarModalAsignacion(tipo) {
            document.getElementById('modal-' + tipo + '-asignacion').classList.remove('active');
            if (tipo === 'eliminar') {
                asignacionActual = null;
            }
        }

        // Manejar submit del formulario de edición de asignaciones
        document.addEventListener('DOMContentLoaded', function() {
            // Cargar asignaciones al iniciar la página
            cargarAsignaciones();
            
            const formEditarAsig = document.getElementById('form-editar-asignacion');
            if (formEditarAsig) {
                formEditarAsig.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    const id = document.getElementById('edit-asig-id').value;
                    const estado = document.getElementById('edit-asig-estado').value;
                    try {
                        const resp = await fetch('api/asignaciones.php?action=actualizar', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                            body: JSON.stringify({ asignacion_id: id, estado: estado })
                        });
                        const json = await resp.json();
                        if (json.success) {
                            showNotification('Asignación actualizada exitosamente', 'success');
                            cerrarModalAsignacion('editar');
                            cargarAsignaciones();
                        } else {
                            throw new Error(json.error || 'error desconocido');
                        }
                    } catch (err) {
                        console.error('Error actualizando asignación', err);
                        showNotification('Error al actualizar: ' + err.message, 'error');
                    }
                });
            }

            // Manejar submit del formulario de nueva asignación
            const formNuevaAsig = document.getElementById('form-nueva-asignacion');
            if (formNuevaAsig) {
                formNuevaAsig.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Validar fechas
                    const fechaInicio = new Date(this.fecha_inicio.value);
                    const fechaFin = new Date(this.fecha_fin.value);
                    
                    if (fechaFin < fechaInicio) {
                        showNotification('La fecha de fin debe ser posterior a la fecha de inicio', 'error');
                        return;
                    }
                    
                    // Obtener datos del formulario
                    const formData = new FormData(this);
                    const nuevaAsignacion = {
                        id: asignacionesData.length + 1,
                        ficha: formData.get('ficha'),
                        instructor: formData.get('instructor'),
                        ambiente: formData.get('ambiente'),
                        competencia: formData.get('competencia'),
                        fecha_inicio: formData.get('fecha_inicio'),
                        estado: formData.get('estado')
                    };
                    
                    // Agregar a la lista (en producción esto se enviaría al servidor)
                    asignacionesData.push(nuevaAsignacion);
                    
                    showNotification('Asignación creada exitosamente', 'success');
                    cerrarModalAsignacion('nueva');
                    
                    // Limpiar formulario
                    this.reset();
                    
                    // Recargar la sección (en producción se actualizaría la tabla dinámicamente)
                    setTimeout(() => {
                        showSection('asignaciones');
                    }, 500);
                });
            }

            // Filtrado dinámico de selects: Tipo y Ubicación -> Ambiente
            const tipoAmbSelect = document.getElementById('select-tipo-ambiente');
            const ubicacionAmbSelect = document.getElementById('select-ubicacion-ambiente');
            const ambienteSelect = document.getElementById('select-ambiente');
            function filterAmbientes() {
                if (!ambienteSelect) return;
                const tipo = tipoAmbSelect ? tipoAmbSelect.value : '';
                const ubic = ubicacionAmbSelect ? ubicacionAmbSelect.value : '';
                Array.from(ambienteSelect.options).forEach(opt => {
                    if (!opt.getAttribute) return;
                    const optTipo = opt.getAttribute('data-tipo');
                    const optUbic = opt.getAttribute('data-ubicacion');
                    let hide = false;
                    if (tipo && optTipo && optTipo !== tipo && opt.value !== '') hide = true;
                    if (ubic && optUbic && optUbic !== ubic && opt.value !== '') hide = true;
                    if (hide) {
                        opt.hidden = true;
                        opt.disabled = true;
                    } else {
                        opt.hidden = false;
                        opt.disabled = false;
                    }
                });
                if (ambienteSelect.selectedOptions.length && ambienteSelect.selectedOptions[0].hidden) {
                    ambienteSelect.value = '';
                }
            }

            if (tipoAmbSelect) tipoAmbSelect.addEventListener('change', filterAmbientes);
            if (ubicacionAmbSelect) ubicacionAmbSelect.addEventListener('change', filterAmbientes);
        });
    </script>
    <script>
        // Theme Toggle Functionality
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const html = document.documentElement;
        
        // Cargar tema guardado o usar el predeterminado
        const savedTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-theme', savedTheme);
        updateThemeIcon(savedTheme);
        
        themeToggle.addEventListener('click', function() {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });
        
        function updateThemeIcon(theme) {
            if (theme === 'dark') {
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
            } else {
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
            }
        }
    </script>
    <script>
        // Sistema de Notificaciones para Coordinador
        document.addEventListener('DOMContentLoaded', function() {
            const enviarNotifBtn = document.getElementById('enviarNotifBtn');
            const modalEnviarNotif = document.getElementById('modal-enviar-notificacion-coord');
            const formEnviarNotif = document.getElementById('form-enviar-notificacion-coord');
            const selectInstructor = document.getElementById('notif-instructor-select');

            console.log('Sistema de notificaciones inicializado');
            console.log('Botón enviar:', enviarNotifBtn);
            console.log('Modal:', modalEnviarNotif);
            console.log('Select instructor:', selectInstructor);

            // Abrir modal de enviar notificación
            if (enviarNotifBtn) {
                console.log('Event listener agregado al botón');
                enviarNotifBtn.addEventListener('click', function() {
                    console.log('Botón clickeado - Abriendo modal');
                    cargarInstructores();
                    modalEnviarNotif.classList.add('active');
                });
            } else {
                console.error('No se encontró el botón enviarNotifBtn');
            }

            // Opciones predefinidas para cada tipo de notificación
            const opcionesNotificaciones = {
                general: {
                    titulos: [
                        'Información General',
                        'Comunicado Importante',
                        'Actualización del Sistema',
                        'Aviso Informativo',
                        'Mensaje de Coordinación'
                    ],
                    mensajes: [
                        'Se le informa que hay actualizaciones importantes en el sistema.',
                        'Por favor, revise la información adjunta en su panel.',
                        'Hay cambios en los procedimientos que debe conocer.',
                        'Se requiere su atención en los siguientes puntos.',
                        'Información relevante para su conocimiento.'
                    ]
                },
                asignacion: {
                    titulos: [
                        'Nueva Asignación de Ficha',
                        'Asignación de Competencia',
                        'Cambio de Asignación',
                        'Asignación de Ambiente',
                        'Actualización de Asignaciones'
                    ],
                    mensajes: [
                        'Se le ha asignado una nueva ficha. Por favor, revise los detalles en su panel.',
                        'Tiene una nueva competencia asignada. Verifique el cronograma.',
                        'Se ha modificado una de sus asignaciones. Revise los cambios.',
                        'Se le ha asignado un nuevo ambiente de formación.',
                        'Sus asignaciones han sido actualizadas. Por favor, confirme recepción.'
                    ]
                },
                cambio_horario: {
                    titulos: [
                        'Cambio de Horario',
                        'Modificación de Horario',
                        'Ajuste de Programación',
                        'Reprogramación de Clase',
                        'Actualización de Horarios'
                    ],
                    mensajes: [
                        'Se ha modificado el horario de una de sus clases. Verifique la nueva programación.',
                        'Hay un cambio en el horario de formación. Por favor, tome nota.',
                        'Se ha reprogramado una sesión. Revise los nuevos horarios.',
                        'Ajuste en la programación de sus actividades. Confirme disponibilidad.',
                        'Cambios en el cronograma de formación. Revise su calendario.'
                    ]
                },
                recordatorio: {
                    titulos: [
                        'Recordatorio Importante',
                        'No Olvide',
                        'Recordatorio de Actividad',
                        'Pendiente por Realizar',
                        'Recordatorio de Entrega'
                    ],
                    mensajes: [
                        'Recuerde completar las actividades pendientes antes de la fecha límite.',
                        'Tiene tareas pendientes que requieren su atención.',
                        'No olvide registrar la asistencia de sus aprendices.',
                        'Recordatorio: Debe entregar los informes correspondientes.',
                        'Por favor, complete la documentación requerida.'
                    ]
                },
                urgente: {
                    titulos: [
                        '¡URGENTE! Atención Inmediata',
                        '¡IMPORTANTE! Acción Requerida',
                        'Notificación Urgente',
                        '¡ATENCIÓN! Prioridad Alta',
                        'Asunto Urgente'
                    ],
                    mensajes: [
                        '¡URGENTE! Se requiere su atención inmediata. Por favor, contacte a coordinación.',
                        '¡IMPORTANTE! Debe tomar acción de inmediato. Revise los detalles.',
                        'Situación urgente que requiere su intervención inmediata.',
                        '¡ATENCIÓN! Asunto de prioridad alta. Responda lo antes posible.',
                        'Se necesita su respuesta urgente. Por favor, comuníquese con coordinación.'
                    ]
                }
            };

            // Función para actualizar opciones según el tipo de notificación
            function actualizarOpcionesNotificacion() {
                const tipoSelect = document.getElementById('notif-tipo');
                const tituloSelect = document.getElementById('notif-titulo-select');
                const mensajeSelect = document.getElementById('notif-mensaje-select');
                
                if (!tipoSelect || !tituloSelect || !mensajeSelect) return;
                
                const tipo = tipoSelect.value;
                const opciones = opcionesNotificaciones[tipo];
                
                if (!opciones) return;
                
                // Actualizar títulos
                tituloSelect.innerHTML = '<option value="">-- Seleccione un título --</option>';
                opciones.titulos.forEach(titulo => {
                    const option = document.createElement('option');
                    option.value = titulo;
                    option.textContent = titulo;
                    tituloSelect.appendChild(option);
                });
                
                // Actualizar mensajes
                mensajeSelect.innerHTML = '<option value="">-- Seleccione un mensaje --</option>';
                opciones.mensajes.forEach(mensaje => {
                    const option = document.createElement('option');
                    option.value = mensaje;
                    option.textContent = mensaje.substring(0, 60) + (mensaje.length > 60 ? '...' : '');
                    mensajeSelect.appendChild(option);
                });
                
                // Limpiar campos personalizados
                document.getElementById('notif-titulo').value = '';
                document.getElementById('notif-mensaje').value = '';
            }

            // Función para sincronizar select de título con input personalizado
            function actualizarTituloPersonalizado() {
                const tituloSelect = document.getElementById('notif-titulo-select');
                const tituloInput = document.getElementById('notif-titulo');
                
                if (!tituloSelect || !tituloInput) return;
                
                if (tituloSelect.value) {
                    tituloInput.value = tituloSelect.value;
                }
            }

            // Función para sincronizar select de mensaje con textarea personalizado
            function actualizarMensajePersonalizado() {
                const mensajeSelect = document.getElementById('notif-mensaje-select');
                const mensajeTextarea = document.getElementById('notif-mensaje');
                
                if (!mensajeSelect || !mensajeTextarea) return;
                
                if (mensajeSelect.value) {
                    mensajeTextarea.value = mensajeSelect.value;
                }
            }

            // Cargar lista de instructores
            function cargarInstructores() {
                console.log('=== INICIANDO CARGA DE INSTRUCTORES ===');
                
                if (!selectInstructor) {
                    console.error('Select instructor no encontrado');
                    return;
                }
                
                console.log('Select instructor encontrado:', selectInstructor);
                console.log('Cargando instructores desde API...');
                
                // Limpiar el select
                selectInstructor.innerHTML = '<option value="">-- Cargando instructores... --</option>';
                
                fetch('api/notificaciones.php?action=listar_instructores')
                    .then(response => {
                        console.log('Response status:', response.status);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Data recibida desde API:', data);
                        
                        if (data.success) {
                            if (data.instructores && data.instructores.length > 0) {
                                console.log('Total instructores recibidos:', data.instructores.length);
                                
                                // Limpiar y agregar opción por defecto
                                selectInstructor.innerHTML = '<option value="">-- Seleccione un instructor --</option>';
                                
                                // Agregar cada instructor al select
                                data.instructores.forEach((instructor, index) => {
                                    console.log(`Agregando instructor ${index + 1}:`, instructor.nombre, instructor.apellido);
                                    const option = document.createElement('option');
                                    option.value = instructor.id;
                                    option.textContent = `${instructor.nombre} ${instructor.apellido}`;
                                    if (instructor.especialidad) {
                                        option.textContent += ` - ${instructor.especialidad}`;
                                    }
                                    selectInstructor.appendChild(option);
                                });
                                
                                console.log('✓ Instructores cargados exitosamente en el select');
                                console.log('Total opciones en select:', selectInstructor.options.length);
                            } else {
                                console.warn('No hay instructores disponibles en la respuesta');
                                selectInstructor.innerHTML = '<option value="">No hay instructores disponibles</option>';
                            }
                        } else {
                            console.error('Error en la respuesta de la API:', data.error);
                            selectInstructor.innerHTML = '<option value="">Error al cargar instructores</option>';
                        }
                    })
                    .catch(error => {
                        console.error('Error al cargar instructores:', error);
                        console.error('Detalles del error:', error.message);
                        selectInstructor.innerHTML = '<option value="">Error de conexión</option>';
                    });
                    
                console.log('=== FIN DE FUNCIÓN CARGAR INSTRUCTORES ===');
            }

            // Hacer funciones globales para que puedan ser llamadas desde HTML
            window.actualizarOpcionesNotificacion = actualizarOpcionesNotificacion;
            window.actualizarTituloPersonalizado = actualizarTituloPersonalizado;
            window.actualizarMensajePersonalizado = actualizarMensajePersonalizado;

            // Inicializar opciones al cargar
            actualizarOpcionesNotificacion();

            // Enviar notificación
            if (formEnviarNotif) {
                formEnviarNotif.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // Obtener el instructor seleccionado
                    const instructorId = selectInstructor.value;
                    if (!instructorId) {
                        showNotification('Por favor, seleccione un instructor', 'error');
                        return;
                    }

                    // Obtener valores - priorizar input personalizado sobre select
                    const tituloInput = document.getElementById('notif-titulo').value.trim();
                    const tituloSelect = document.getElementById('notif-titulo-select').value;
                    const mensajeTextarea = document.getElementById('notif-mensaje').value.trim();
                    const mensajeSelect = document.getElementById('notif-mensaje-select').value;
                    
                    const titulo = tituloInput || tituloSelect;
                    const mensaje = mensajeTextarea || mensajeSelect;
                    
                    // Validar que haya título y mensaje
                    if (!titulo || !mensaje) {
                        showNotification('Por favor, complete el título y el mensaje', 'error');
                        return;
                    }

                    // Deshabilitar botón de envío para evitar doble clic
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...';

                    const formData = new FormData(this);
                    formData.set('titulo', titulo);
                    formData.set('mensaje', mensaje);
                    formData.set('instructor_id', instructorId);
                    formData.append('action', 'enviar');

                    fetch('api/notificaciones.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('✓ Notificación enviada exitosamente al instructor', 'success');
                            cerrarModalNotificacion();
                            formEnviarNotif.reset();
                            // Reiniciar opciones
                            actualizarOpcionesNotificacion();
                            // Limpiar el selector personalizado
                            const selectedText = document.getElementById('selected-instructor-text');
                            if (selectedText) {
                                selectedText.innerHTML = '<span class="text-gray-500">-- Seleccione un instructor --</span>';
                            }
                        } else {
                            showNotification('Error: ' + (data.error || 'No se pudo enviar la notificación'), 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error al enviar notificación:', error);
                        showNotification('Error de conexión al enviar la notificación', 'error');
                    })
                    .finally(() => {
                        // Rehabilitar botón
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    });
                });
            }
        });

        function cerrarModalNotificacion() {
            const modal = document.getElementById('modal-enviar-notificacion-coord');
            if (modal) {
                modal.classList.remove('active');
                
                // Limpiar el formulario
                const form = document.getElementById('form-enviar-notificacion-coord');
                if (form) {
                    form.reset();
                }
                
                // Limpiar el selector personalizado de instructor
                const selectedText = document.getElementById('selected-instructor-text');
                if (selectedText) {
                    selectedText.innerHTML = '<span class="text-gray-500">-- Seleccione un instructor --</span>';
                }
                
                // Limpiar campos personalizados
                const tituloInput = document.getElementById('notif-titulo');
                const mensajeTextarea = document.getElementById('notif-mensaje');
                if (tituloInput) tituloInput.value = '';
                if (mensajeTextarea) mensajeTextarea.value = '';
                
                // Reiniciar a tipo general
                const tipoSelect = document.getElementById('notif-tipo');
                if (tipoSelect) {
                    tipoSelect.value = 'general';
                    actualizarOpcionesNotificacion();
                }
            }
        }

        // ============================================
        // ============================================
        // FUNCIONES PARA VER PERFIL Y CONFIGURACIÓN
        // ============================================
        
        function abrirModalVerPerfil() {
            const modal = document.getElementById('modal-ver-perfil-coord');
            if (modal) {
                modal.classList.add('active');
            }
        }
        
        function cerrarModalVerPerfil() {
            const modal = document.getElementById('modal-ver-perfil-coord');
            if (modal) {
                modal.classList.remove('active');
            }
        }
        
        function abrirModalEditarPerfilCoord() {
            const modal = document.getElementById('modal-editar-perfil-coord');
            if (modal) {
                modal.classList.add('active');
            }
        }
        
        function cerrarModalEditarPerfilCoord() {
            const modal = document.getElementById('modal-editar-perfil-coord');
            if (modal) {
                modal.classList.remove('active');
            }
        }
        
        function abrirModalConfiguracion() {
            const modal = document.getElementById('modal-configuracion-coord');
            if (modal) {
                modal.classList.add('active');
                
                // Actualizar información de pestañas activas
                if (window.roleContext) {
                    const info = window.roleContext.getActiveTabsInfo();
                    const tabsInfo = document.getElementById('config-tabs-info');
                    if (tabsInfo) {
                        tabsInfo.textContent = `${info.total} (${info.coordinador} Coordinador, ${info.instructor} Instructor)`;
                    }
                }
            }
        }
        
        function cerrarModalConfiguracion() {
            const modal = document.getElementById('modal-configuracion-coord');
            if (modal) {
                modal.classList.remove('active');
            }
        }
        
        // Manejar envío del formulario de editar perfil
        document.addEventListener('DOMContentLoaded', function() {
            const formEditarPerfil = document.getElementById('form-editar-perfil-coord');
            
            if (formEditarPerfil) {
                formEditarPerfil.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const nombre = document.getElementById('edit-nombre-coord').value;
                    const email = document.getElementById('edit-email-coord').value;
                    const telefono = document.getElementById('edit-telefono-coord').value;
                    const documento = document.getElementById('edit-documento-coord').value;
                    const sede = document.getElementById('edit-sede-coord').value;
                    const passwordActual = document.getElementById('edit-password-actual-coord').value;
                    const passwordNueva = document.getElementById('edit-password-nueva-coord').value;
                    const passwordConfirmar = document.getElementById('edit-password-confirmar-coord').value;
                    
                    // Validar contraseñas si se están cambiando
                    if (passwordNueva || passwordConfirmar) {
                        if (!passwordActual) {
                            Toastify({
                                text: "Debes ingresar tu contraseña actual",
                                duration: 3000,
                                gravity: "top",
                                position: "right",
                                backgroundColor: "#ef4444"
                            }).showToast();
                            return;
                        }
                        
                        if (passwordNueva !== passwordConfirmar) {
                            Toastify({
                                text: "Las contraseñas no coinciden",
                                duration: 3000,
                                gravity: "top",
                                position: "right",
                                backgroundColor: "#ef4444"
                            }).showToast();
                            return;
                        }
                        
                        if (passwordNueva.length < 6) {
                            Toastify({
                                text: "La contraseña debe tener al menos 6 caracteres",
                                duration: 3000,
                                gravity: "top",
                                position: "right",
                                backgroundColor: "#ef4444"
                            }).showToast();
                            return;
                        }
                    }
                    
                    // Aquí iría la llamada al API para guardar los cambios
                    // Por ahora solo actualizamos el DOM
                    
                    // Actualizar información en el modal de ver perfil
                    document.getElementById('perfil-email').textContent = email;
                    document.getElementById('perfil-sede').textContent = sede;
                    document.getElementById('perfil-telefono').textContent = telefono;
                    document.getElementById('perfil-documento').textContent = 'CC ' + documento;
                    
                    // Actualizar nombre en el sidebar
                    const profileInfo = document.querySelector('.profile-info p.text-sm');
                    if (profileInfo) {
                        profileInfo.textContent = nombre;
                    }
                    
                    Toastify({
                        text: "Perfil actualizado exitosamente",
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#10b981"
                    }).showToast();
                    
                    cerrarModalEditarPerfilCoord();
                });
            }
            
            // Theme toggle en configuración
            const themeToggleConfig = document.getElementById('themeToggleConfig');
            if (themeToggleConfig) {
                // Sincronizar con el tema actual
                const currentTheme = localStorage.getItem('theme') || 'light';
                if (currentTheme === 'dark') {
                    themeToggleConfig.classList.add('bg-green-500');
                    themeToggleConfig.classList.remove('bg-gray-300');
                    themeToggleConfig.querySelector('span').style.transform = 'translateX(28px)';
                }
                
                themeToggleConfig.addEventListener('click', function() {
                    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
                    
                    if (isDark) {
                        document.documentElement.setAttribute('data-theme', 'light');
                        localStorage.setItem('theme', 'light');
                        themeToggleConfig.classList.remove('bg-green-500');
                        themeToggleConfig.classList.add('bg-gray-300');
                        themeToggleConfig.querySelector('span').style.transform = 'translateX(0)';
                        
                        // Actualizar icono del header
                        const themeIcon = document.getElementById('themeIcon');
                        if (themeIcon) {
                            themeIcon.classList.remove('fa-sun');
                            themeIcon.classList.add('fa-moon');
                        }
                    } else {
                        document.documentElement.setAttribute('data-theme', 'dark');
                        localStorage.setItem('theme', 'dark');
                        themeToggleConfig.classList.add('bg-green-500');
                        themeToggleConfig.classList.remove('bg-gray-300');
                        themeToggleConfig.querySelector('span').style.transform = 'translateX(28px)';
                        
                        // Actualizar icono del header
                        const themeIcon = document.getElementById('themeIcon');
                        if (themeIcon) {
                            themeIcon.classList.remove('fa-moon');
                            themeIcon.classList.add('fa-sun');
                        }
                    }
                });
            }
        });
        
        // Listener para ajustar comportamiento en resize
        window.addEventListener('resize', function(){
            // Si la ventana se hace más grande que 768px, cerrar el sidebar móvil
            if(window.innerWidth > 768){
                const sb = document.getElementById('sidebar');
                const overlay = document.querySelector('.sidebar-overlay');
                if(sb && overlay){
                    sb.classList.remove('mobile-open');
                    overlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            }
        });

        // Auto-colapsar sidebar en móviles al cargar
        window.addEventListener('DOMContentLoaded', function() {
            if (window.innerWidth <= 768) {
                const sidebar = document.getElementById('sidebar');
                if (sidebar && !sidebar.classList.contains('collapsed')) {
                    sidebar.classList.add('collapsed');
                }
            }
        });

        // Manejar cambios de tamaño de ventana
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            if (window.innerWidth <= 768) {
                // En móvil, mantener colapsado
                if (sidebar && !sidebar.classList.contains('collapsed')) {
                    sidebar.classList.add('collapsed');
                }
            }
        });

        // Cerrar sidebar automáticamente al hacer clic en una opción del menú en móviles
        document.addEventListener('DOMContentLoaded', function() {
            const navItems = document.querySelectorAll('.nav-item');
            const sidebar = document.getElementById('sidebar');
            
            navItems.forEach(item => {
                item.addEventListener('click', function() {
                    // Solo en móviles
                    if (window.innerWidth <= 768 && sidebar && !sidebar.classList.contains('collapsed')) {
                        sidebar.classList.add('collapsed');
                    }
                });
            });
        });

        // Cerrar sidebar al hacer clic en el overlay (área oscura) en móviles
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.querySelector('.toggle-btn');
            
            if (window.innerWidth <= 768 && sidebar && !sidebar.classList.contains('collapsed')) {
                // Si el clic no fue en el sidebar ni en el botón toggle
                if (!sidebar.contains(e.target) && !toggleBtn?.contains(e.target)) {
                    sidebar.classList.add('collapsed');
                }
            }
        });
    </script>
</body>
</html>