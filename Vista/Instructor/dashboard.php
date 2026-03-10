<?php
// Verificar autenticación
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'instructor') {
    header('Location: index.php?controlador=Auth&accion=login');
    exit;
}

// Valores útiles para mostrar en el perfil
$user_name = $_SESSION['user_name'] ?? $_SESSION['nombre'] ?? 'Instructor';
$user_role_db = $_SESSION['role'] ?? $_SESSION['rol'] ?? 'instructor';
// Convertir rol de base de datos a nombre amigable
$user_role = ($user_role_db === 'administrador') ? 'Coordinador' : 'Instructor';

// Iniciales para el avatar
$initials = '';
$parts = preg_split('/\s+/', trim($user_name));
if (count($parts) >= 2) {
    $initials = strtoupper(substr($parts[0],0,1) . substr($parts[1],0,1));
} else {
    $initials = strtoupper(substr($parts[0],0,1));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Instructor SENA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Toastify for notifications -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <!-- FullCalendar -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales/es.global.min.js"></script>
    <!-- Sistema de Contexto de Rol - COMENTADO TEMPORALMENTE PARA DEBUG
    <script src="../../assets/js/role_context.js" defer></script>
    -->
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
        
        [data-theme="dark"] .sidebar {
            background: var(--bg-sidebar) !important;
            border-right: 1px solid var(--border-color);
        }
        
        /* Global green button styles */
        .bg-green-500 { background-color: #10b981 !important; }
        .hover\:bg-green-600:hover { background-color: #059669 !important; }
        .hover\:bg-green-700:hover { background-color: #047857 !important; }
        .text-green-500 { color: #10b981 !important; }
        
        /* Sidebar collapse behavior */
        .sidebar { position: relative; width: 288px; transition: width 200ms ease; padding-bottom: 80px; background: var(--bg-sidebar); box-shadow: 4px 0 10px var(--card-shadow); border-right: 1px solid var(--border-color); }
        .sidebar.collapsed { width: 72px; }
        .sidebar .nav-item { display: flex; align-items: center; gap: 12px; }
        .sidebar .nav-item i { min-width: 20px; text-align: center; }
        .sidebar.collapsed .nav-item { justify-content: center; padding-left: 0; padding-right: 0; }
        .sidebar.collapsed .nav-item span { display: none; }
        .sidebar.collapsed p { display: none; }

        /* Toggle button */
        .sidebar .toggle-btn { position: absolute; right: 12px; top: 12px; z-index: 60; }
        .sidebar .toggle-btn button { background: var(--card-bg); border-radius: 9999px; padding: 8px; box-shadow: 0 4px 8px var(--card-shadow); border: 1px solid var(--border-color); display:flex; align-items:center; justify-content:center; width:36px; height:36px; }
        .sidebar .toggle-btn i { transition: transform 160ms ease; color: var(--green-500); }
        .sidebar.collapsed .toggle-btn i { transform: rotate(180deg); }
        .sidebar.collapsed .toggle-btn { right: -18px; }

        /* Menu icon background */
        .nav-item i { display:inline-block; background: var(--card-bg); padding: 10px; border-radius: 12px; box-shadow: 0 6px 18px var(--green-shadow); border: 1px solid var(--border-color); }
        .nav-item.active i { background: var(--green-500); color: white !important; box-shadow: 0 10px 30px var(--green-shadow); border-color: var(--green-500); }
        .nav-item { color: var(--text-secondary) !important; }
        .nav-item:hover { background: rgba(16,185,129,0.06); color: var(--text-primary) !important; transform: translateX(4px); }
        .nav-item.active { background: transparent; color: var(--green-500) !important; border-left: 4px solid var(--green-500); padding-left: 16px; }
        .nav-item:hover i { transform: translateY(-2px); }

        /* Logo adjustments */
        .sidebar .logo-container { transition: all 160ms ease; }
        .sidebar .logo-text { transition: opacity 160ms ease; }
        .sidebar .logo-icon { transition: all 160ms ease; }
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
        .sidebar.collapsed .logo-icon i { font-size: 20px; }

        /* Profile box */
        .profile-box { transition: all 160ms ease; }
        .sidebar .profile-box { position: absolute; left: 12px; right: 12px; bottom: 25px; }
        .sidebar .profile-box .profile-card { padding: 8px; transition: all 160ms ease; }
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

        /* Avatar styles */
        .profile-box .avatar-outer { background: var(--card-bg); padding: 6px; border-radius: 12px; box-shadow: 0 6px 18px var(--green-shadow); display:flex; align-items:center; width:44px; height:44px; justify-content:center; border: 1px solid var(--border-color); }
        .profile-avatar { background: transparent; color: var(--green-500); width:34px; height:34px; border-radius: 50%; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:16px; }

        /* Profile menu */
        #profileMenu { 
            box-shadow: 0 6px 20px rgba(0,0,0,0.12); 
            left: 16px !important; 
            bottom: 76px !important; 
            width: 220px !important;
            z-index: 100 !important;
        }
        #profileMenu a, #profileMenu button { display:block; padding: 10px 14px; text-align:left; width:100%; border:none; background:transparent; }
        #profileToggle { background: transparent; border: none; padding: 6px; border-radius: 8px; }

        /* Main content */
        main { 
            transition: margin-left 200ms ease; 
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
        }
        
        /* Hide scrollbar in sidebar */
        .sidebar .custom-scrollbar::-webkit-scrollbar { display: none; }
        .sidebar .custom-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
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
                right: auto !important;
                transform: translate(-50%, -50%) !important;
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

        /* Modal styles */
        .modal { 
            display: none; 
            position: fixed; 
            z-index: 9999; 
            left: 0; 
            top: 0; 
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0,0,0,0.75); 
        }
        .modal.active { 
            display: flex; 
            align-items: center; 
            justify-content: center; 
        }
        .modal-content { 
            background-color: var(--card-bg); 
            border-radius: 20px; 
            padding: 24px; 
            max-width: 700px; 
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
        
        /* Modal content styles - Light theme */
        .modal-content h2 {
            color: #111827 !important;
        }
        
        .modal-content label {
            color: #374151 !important;
        }
        
        .modal-content h3 {
            color: #1f2937 !important;
        }
        
        /* Dark theme modal styles */
        [data-theme="dark"] .modal-content {
            background-color: var(--card-bg);
            border-color: var(--border-color);
        }
        
        [data-theme="dark"] .modal-content h2 {
            color: var(--text-primary) !important;
        }
        
        [data-theme="dark"] .modal-content h3 {
            color: var(--text-primary) !important;
        }
        
        [data-theme="dark"] .modal-content label {
            color: var(--text-secondary) !important;
        }
        
        [data-theme="dark"] .modal-content input[type="text"],
        [data-theme="dark"] .modal-content input[type="email"],
        [data-theme="dark"] .modal-content input[type="tel"],
        [data-theme="dark"] .modal-content input[type="password"],
        [data-theme="dark"] .modal-content select,
        [data-theme="dark"] .modal-content textarea {
            background-color: var(--bg-tertiary) !important;
            color: var(--text-primary) !important;
            border-color: var(--border-color) !important;
        }
        
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
            overflow: hidden;
            height: 100vh;
        }
        
        main {
            overflow-y: auto;
            overflow-x: hidden;
            height: 100%;
        }
        
        /* Responsive Design */
        
        /* Pantallas Extra Grandes (> 1536px) */
        @media (min-width: 1536px) {
            .grid.grid-cols-1.sm\:grid-cols-2.lg\:grid-cols-4 { 
                grid-template-columns: repeat(4, 1fr) !important; 
                gap: 2rem !important;
            }
        }
        
        /* Pantallas Grandes (1280px - 1536px) */
        @media (min-width: 1280px) and (max-width: 1535px) {
            .grid.grid-cols-1.sm\:grid-cols-2.lg\:grid-cols-4 { 
                grid-template-columns: repeat(4, 1fr) !important; 
            }
        }
        
        /* Pantallas Medianas (1024px - 1279px) */
        @media (max-width: 1279px) {
            .grid.grid-cols-1.sm\:grid-cols-2.lg\:grid-cols-4 { 
                grid-template-columns: repeat(2, 1fr) !important; 
            }
            .grid.grid-cols-1.md\:grid-cols-3 { 
                grid-template-columns: repeat(2, 1fr) !important; 
            }
        }
        
        /* Tablets (768px - 1023px) */
        @media (max-width: 1023px) {
            .sidebar { width: 240px; }
            .sidebar.collapsed { width: 72px; }
            
            main { padding: 1.5rem !important; }
            
            header { gap: 1rem; }
            
            .grid.grid-cols-1.sm\:grid-cols-2.lg\:grid-cols-4 { 
                grid-template-columns: repeat(2, 1fr) !important; 
            }
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
        }
        
        /* Móviles (< 768px) */
        @media (max-width: 767px) {
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
                padding: 1rem !important;
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
            .grid.grid-cols-1.sm\:grid-cols-2.lg\:grid-cols-4,
            .grid.grid-cols-1.md\:grid-cols-4,
            .grid.grid-cols-1.md\:grid-cols-3,
            .grid.grid-cols-1.md\:grid-cols-2 { 
                grid-template-columns: 1fr !important; 
                gap: 1rem !important;
            }
            
            /* Header responsive */
            header { 
                padding: 0 !important; 
                margin-bottom: 1rem !important; 
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 0.75rem !important;
            }
            header h1 { 
                font-size: 1.5rem !important; 
                margin-bottom: 0 !important;
            }
            header .flex.items-center { 
                gap: 0.5rem !important; 
                justify-content: flex-end !important;
            }
            header input[type="text"] { display: none !important; }
            
            /* Cards más compactas en móvil */
            .bg-white.p-4.md\:p-6 { 
                padding: 1rem !important; 
                border-radius: 1rem !important;
            }
            .bg-white.p-4.md\:p-6 .w-10.h-10 { 
                width: 2.5rem !important; 
                height: 2.5rem !important; 
            }
            .text-2xl.md\:text-3xl { 
                font-size: 1.75rem !important; 
            }
            
            /* Tablas responsive */
            .overflow-x-auto { 
                overflow-x: auto; 
                -webkit-overflow-scrolling: touch; 
                margin: 0 -1rem;
                padding: 0 1rem;
            }
            table { 
                min-width: 600px; 
                font-size: 0.875rem; 
            }
            table th, table td { 
                padding: 0.5rem !important; 
                white-space: nowrap;
            }
            table th {
                font-size: 0.75rem !important;
            }
            
            /* Notificaciones responsive */
            #notifMenu { 
                width: 90vw !important; 
                max-width: 380px !important; 
            }
            
            /* Títulos de sección */
            .text-xl { font-size: 1.125rem !important; }
        }
        
        /* Móviles Pequeños (< 420px) */
        @media (max-width: 419px) {
            main { 
                padding: 0.75rem !important; 
                margin-left: 60px !important;
                width: calc(100% - 60px) !important;
            }
            
            .sidebar {
                width: 60px !important;
            }
            
            .sidebar:not(.collapsed) {
                width: 260px !important;
            }
            
            header h1 { 
                font-size: 1.25rem !important; 
            }
            
            /* Stats cards más pequeñas */
            .bg-white.p-4.md\:p-6 { 
                padding: 0.75rem !important; 
            }
            .bg-white.p-4.md\:p-6 .w-10.h-10 { 
                width: 2rem !important; 
                height: 2rem !important; 
            }
            .text-2xl.md\:text-3xl { 
                font-size: 1.5rem !important; 
            }
            .text-xs.md\:text-sm { 
                font-size: 0.7rem !important; 
            }
            
            /* Tabla más compacta */
            table { 
                font-size: 0.75rem; 
                min-width: 500px; 
            }
            table th, table td { 
                padding: 0.375rem !important; 
            }
            
            /* Botones más pequeños */
            header button {
                padding: 0.5rem !important;
            }
        }
            
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
        
        /* Botón hamburguesa (oculto por defecto) */
        .mobile-menu-btn { display: none; }
    </style>
</head>
<body class="bg-gray-50">

    <div class="flex h-screen overflow-hidden" style="background: var(--bg-primary);">

    <aside id="sidebar" class="sidebar w-72 bg-[#e2f3e4] flex flex-col border-r border-gray-200">
        <div class="p-6 flex items-center space-x-2 logo-container">
            <div class="bg-green-500 p-2 rounded-lg logo-icon">
                <i class="fas fa-user text-white text-xl"></i>
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
            <button onclick="showSection('panel')" class="nav-item active w-full flex items-center space-x-3 text-gray-600 p-3 rounded-xl transition hover:bg-white/50">
                <i class="fas fa-th-large"></i><span>Mi Panel</span>
            </button>
            
            <p class="text-xs font-bold text-gray-500 uppercase px-2 mb-2 mt-6">Gestión</p>
            <button onclick="showSection('asignaciones')" class="nav-item w-full flex items-center space-x-3 text-gray-600 p-3 rounded-xl transition hover:bg-white/50">
                <i class="fas fa-calendar-check"></i><span>Mis Asignaciones</span>
            </button>
            <button onclick="showSection('competencias')" class="nav-item w-full flex items-center space-x-3 text-gray-600 p-3 rounded-xl transition hover:bg-white/50">
                <i class="fas fa-award"></i><span>Mis Competencias</span>
            </button>
            
            <p class="text-xs font-bold text-gray-500 uppercase px-2 mb-2 mt-6">Académico</p>
            <button onclick="showSection('programas')" class="nav-item w-full flex items-center space-x-3 text-gray-600 p-3 rounded-xl transition hover:bg-white/50">
                <i class="fas fa-book"></i><span>Programas</span>
            </button>
            <button onclick="showSection('transversales')" class="nav-item w-full flex items-center space-x-3 text-gray-600 p-3 rounded-xl transition hover:bg-white/50">
                <i class="fas fa-project-diagram"></i><span>Transversales</span>
            </button>
            <button onclick="showSection('fichas')" class="nav-item w-full flex items-center space-x-3 text-gray-600 p-3 rounded-xl transition hover:bg-white/50">
                <i class="fas fa-layer-group"></i><span>Fichas</span>
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
                <a href="index.php?controlador=Instructor&accion=perfil" class="block px-4 py-2 hover:bg-gray-50 text-gray-700">
                    <i class="fas fa-user mr-2"></i>Ver Perfil
                </a>
                <a href="#" onclick="abrirModalConfiguracionInst(); document.getElementById('profileMenu').classList.add('hidden'); return false;" class="block px-4 py-2 hover:bg-gray-50 text-gray-700">
                    <i class="fas fa-cog mr-2"></i>Configuración
                </a>
                <a href="logout.php" class="block px-4 py-2 text-red-500 hover:bg-gray-50">
                    <i class="fas fa-sign-out-alt mr-2"></i>Cerrar Sesión
                </a>
            </div>
        </div>
    </aside>

    <main class="flex-1 overflow-y-auto p-4 md:p-8">
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 md:mb-8 gap-4">
            <h1 id="section-title" class="text-2xl md:text-3xl font-bold text-green-500">Mi Panel</h1>
            <div class="flex items-center space-x-2 md:space-x-4 w-full md:w-auto justify-end">
                <div class="relative hidden md:block">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    <input type="text" placeholder="Buscar..." class="pl-10 pr-4 py-2 border rounded-xl focus:ring-2 outline-none w-64" style="border-color: var(--border-color); background: var(--bg-secondary); color: var(--text-primary);">
                </div>
                <button id="themeToggle" class="p-2 bg-white border rounded-xl text-gray-500 hover:text-[#10b981] transition relative flex items-center" style="background: var(--bg-secondary); border-color: var(--border-color);" title="Cambiar tema">
                    <i class="fas fa-moon" id="themeIcon"></i>
                </button>
                <div class="relative">
                    <button id="notifBtn" class="p-2 bg-white border border-gray-200 rounded-xl text-gray-500 hover:text-[#10b981] transition relative flex items-center" aria-expanded="false" aria-haspopup="true" title="Notificaciones" style="background: var(--bg-secondary); border-color: var(--border-color);">
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
                                <!-- Notificaciones se renderizan aquí -->
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Botón Notificar Coordinador -->
                <button id="btnNotificarCoordinador" class="p-2 bg-green-500 border border-green-500 rounded-xl text-white hover:bg-green-600 transition flex items-center gap-2 px-4" title="Enviar Notificación al Coordinador">
                    <i class="fas fa-paper-plane"></i>
                    <span class="hidden md:inline text-sm font-semibold">Notificar Coordinador</span>
                </button>
            </div>
        </header>

        <!-- Panel Principal -->
        <section id="panel" class="view-section active">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6 md:mb-10">
                <div class="bg-white p-4 md:p-6 rounded-2xl md:rounded-3xl shadow-sm border border-gray-100" style="background: var(--card-bg); border-color: var(--border-color);">
                    <div class="bg-green-500 w-10 h-10 md:w-12 md:h-12 rounded-xl flex items-center justify-center text-white mb-3 md:mb-4">
                        <i class="fas fa-folder text-lg md:text-xl"></i>
                    </div>
                    <p class="text-2xl md:text-3xl font-bold text-green-500">5</p>
                    <p class="text-gray-500 text-xs md:text-sm" style="color: var(--text-tertiary);">Fichas Asignadas</p>
                </div>
                <div class="bg-white p-4 md:p-6 rounded-2xl md:rounded-3xl shadow-sm border border-gray-100" style="background: var(--card-bg); border-color: var(--border-color);">
                    <div class="bg-green-500 w-10 h-10 md:w-12 md:h-12 rounded-xl flex items-center justify-center text-white mb-3 md:mb-4">
                        <i class="fas fa-clock text-lg md:text-xl"></i>
                    </div>
                    <p class="text-2xl md:text-3xl font-bold text-green-500">120</p>
                    <p class="text-gray-500 text-xs md:text-sm" style="color: var(--text-tertiary);">Horas Programadas</p>
                </div>
                <div class="bg-white p-4 md:p-6 rounded-2xl md:rounded-3xl shadow-sm border border-gray-100" style="background: var(--card-bg); border-color: var(--border-color);">
                    <div class="bg-green-500 w-10 h-10 md:w-12 md:h-12 rounded-xl flex items-center justify-center text-white mb-3 md:mb-4">
                        <i class="fas fa-award text-lg md:text-xl"></i>
                    </div>
                    <p class="text-2xl md:text-3xl font-bold text-green-500">8</p>
                    <p class="text-gray-500 text-xs md:text-sm" style="color: var(--text-tertiary);">Competencias</p>
                </div>
                <div class="bg-white p-4 md:p-6 rounded-2xl md:rounded-3xl shadow-sm border border-gray-100" style="background: var(--card-bg); border-color: var(--border-color);">
                    <div class="bg-green-500 w-10 h-10 md:w-12 md:h-12 rounded-xl flex items-center justify-center text-white mb-3 md:mb-4">
                        <i class="fas fa-star text-lg md:text-xl"></i>
                    </div>
                    <p class="text-2xl md:text-3xl font-bold text-green-500">4.8</p>
                    <p class="text-gray-500 text-xs md:text-sm" style="color: var(--text-tertiary);">Calificación</p>
                </div>
            </div>

            <!-- Horario de la Semana -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden" style="background: var(--card-bg); border-color: var(--border-color);">
                <div class="p-6 border-b border-gray-100" style="border-color: var(--border-color);">
                    <h3 class="text-xl font-bold" style="color: var(--text-primary);">Horario de Esta Semana</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-bold">
                            <tr>
                                <th class="p-4">Día</th>
                                <th class="p-4">Hora</th>
                                <th class="p-4">Ficha</th>
                                <th class="p-4">Competencia</th>
                                <th class="p-4">Ambiente</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm">
                            <tr class="border-b border-gray-50 hover:bg-green-50 transition cursor-pointer">
                                <td class="p-4"><strong>Lunes</strong></td>
                                <td class="p-4">08:00 - 12:00</td>
                                <td class="p-4">2558963</td>
                                <td class="p-4">Programación Web</td>
                                <td class="p-4">Lab 301</td>
                            </tr>
                            <tr class="border-b border-gray-50 hover:bg-green-50 transition cursor-pointer">
                                <td class="p-4"><strong>Martes</strong></td>
                                <td class="p-4">14:00 - 18:00</td>
                                <td class="p-4">2558964</td>
                                <td class="p-4">Bases de Datos</td>
                                <td class="p-4">Lab 302</td>
                            </tr>
                            <tr class="border-b border-gray-50 hover:bg-green-50 transition cursor-pointer">
                                <td class="p-4"><strong>Miércoles</strong></td>
                                <td class="p-4">08:00 - 12:00</td>
                                <td class="p-4">2558963</td>
                                <td class="p-4">Programación Web</td>
                                <td class="p-4">Lab 301</td>
                            </tr>
                            <tr class="border-b border-gray-50 hover:bg-green-50 transition cursor-pointer">
                                <td class="p-4"><strong>Jueves</strong></td>
                                <td class="p-4">14:00 - 18:00</td>
                                <td class="p-4">2558965</td>
                                <td class="p-4">Redes</td>
                                <td class="p-4">Lab 201</td>
                            </tr>
                            <tr class="border-b border-gray-50 hover:bg-green-50 transition cursor-pointer">
                                <td class="p-4"><strong>Viernes</strong></td>
                                <td class="p-4">08:00 - 12:00</td>
                                <td class="p-4">2558963</td>
                                <td class="p-4">Programación Web</td>
                                <td class="p-4">Lab 301</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Sección Mi Perfil -->
        <section id="perfil" class="view-section hidden">
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 mb-6" style="background: var(--card-bg); border-color: var(--border-color);">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-6">
                        <div class="bg-green-500 w-24 h-24 rounded-full flex items-center justify-center text-white text-4xl font-bold">
                            <?php echo strtoupper(substr($initials, 0, 1)); ?>
                        </div>
                        <div>
                            <h2 class="text-3xl font-bold mb-2" style="color: var(--text-primary);"><?php echo htmlspecialchars($user_name); ?></h2>
                            <p class="text-gray-500 text-lg"><?php echo htmlspecialchars($user_role); ?> - SENA</p>
                        </div>
                    </div>
                    <button onclick="abrirModalEditarPerfil()" class="px-6 py-3 bg-green-500 text-white rounded-xl hover:bg-green-600 flex items-center gap-2 transition">
                        <i class="fas fa-edit"></i>
                        Editar Perfil
                    </button>
                </div>
            </div>

            <!-- Información Personal -->
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 mb-6" style="background: var(--card-bg); border-color: var(--border-color);">
                <h3 class="text-xl font-bold mb-4 flex items-center gap-2" style="color: var(--text-primary);">
                    <i class="fas fa-user text-green-500"></i>
                    Información Personal
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 bg-gray-50 rounded-xl" style="background: var(--bg-secondary);">
                        <p class="text-sm text-gray-500 mb-1">Nombre Completo</p>
                        <p class="font-semibold" style="color: var(--text-primary);"><?php echo htmlspecialchars($user_name); ?></p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-xl" style="background: var(--bg-secondary);">
                        <p class="text-sm text-gray-500 mb-1">Rol</p>
                        <p class="font-semibold" style="color: var(--text-primary);"><?php echo htmlspecialchars(ucfirst($user_role)); ?></p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-xl" style="background: var(--bg-secondary);">
                        <p class="text-sm text-gray-500 mb-1">Email</p>
                        <p class="font-semibold" style="color: var(--text-primary);"><?php echo htmlspecialchars($_SESSION['email'] ?? 'instructor@sena.edu.co'); ?></p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-xl" style="background: var(--bg-secondary);">
                        <p class="text-sm text-gray-500 mb-1">Usuario ID</p>
                        <p class="font-semibold" style="color: var(--text-primary);"><?php echo htmlspecialchars($_SESSION['usuario_id'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-xl" style="background: var(--bg-secondary);">
                        <p class="text-sm text-gray-500 mb-1">Estado</p>
                        <p class="font-semibold text-green-500">
                            <i class="fas fa-check-circle mr-1"></i>Activo
                        </p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-xl" style="background: var(--bg-secondary);">
                        <p class="text-sm text-gray-500 mb-1">Último Acceso</p>
                        <p class="font-semibold" style="color: var(--text-primary);"><?php echo date('d/m/Y H:i'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Estadísticas del Instructor -->
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100" style="background: var(--card-bg); border-color: var(--border-color);">
                <h3 class="text-xl font-bold mb-4 flex items-center gap-2" style="color: var(--text-primary);">
                    <i class="fas fa-chart-bar text-green-500"></i>
                    Estadísticas
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="p-4 bg-gray-50 rounded-xl text-center" style="background: var(--bg-secondary);">
                        <div class="bg-green-500 w-12 h-12 rounded-xl flex items-center justify-center text-white mx-auto mb-2">
                            <i class="fas fa-folder"></i>
                        </div>
                        <p class="text-2xl font-bold text-green-500">5</p>
                        <p class="text-sm text-gray-500">Fichas Asignadas</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-xl text-center" style="background: var(--bg-secondary);">
                        <div class="bg-green-500 w-12 h-12 rounded-xl flex items-center justify-center text-white mx-auto mb-2">
                            <i class="fas fa-clock"></i>
                        </div>
                        <p class="text-2xl font-bold text-green-500">120</p>
                        <p class="text-sm text-gray-500">Horas Programadas</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-xl text-center" style="background: var(--bg-secondary);">
                        <div class="bg-green-500 w-12 h-12 rounded-xl flex items-center justify-center text-white mx-auto mb-2">
                            <i class="fas fa-award"></i>
                        </div>
                        <p class="text-2xl font-bold text-green-500">8</p>
                        <p class="text-sm text-gray-500">Competencias</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-xl text-center" style="background: var(--bg-secondary);">
                        <div class="bg-green-500 w-12 h-12 rounded-xl flex items-center justify-center text-white mx-auto mb-2">
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="text-2xl font-bold text-green-500">4.8</p>
                        <p class="text-sm text-gray-500">Calificación</p>
                    </div>
                </div>
            </div>

            <!-- Historial de Cambios en el Perfil -->
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 mt-6" style="background: var(--card-bg); border-color: var(--border-color);">
                <h3 class="text-xl font-bold mb-4 flex items-center gap-2" style="color: var(--text-primary);">
                    <i class="fas fa-history text-green-500"></i>
                    Historial de Cambios
                </h3>
                <div class="space-y-3">
                    <!-- Cambio reciente 1 -->
                    <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition" style="background: var(--bg-secondary);">
                        <div class="bg-green-500 w-10 h-10 rounded-full flex items-center justify-center text-white flex-shrink-0">
                            <i class="fas fa-edit text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-1">
                                <p class="font-semibold" style="color: var(--text-primary);">Actualización de perfil</p>
                                <span class="text-xs text-gray-500">Hace 2 días</span>
                            </div>
                            <p class="text-sm text-gray-600" style="color: var(--text-secondary);">
                                Actualizaste tu especialidad a "Análisis y Desarrollo de Software"
                            </p>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs rounded-lg">
                                    <i class="fas fa-clock mr-1"></i>Pendiente de revisión
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Cambio reciente 2 -->
                    <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition" style="background: var(--bg-secondary);">
                        <div class="bg-green-500 w-10 h-10 rounded-full flex items-center justify-center text-white flex-shrink-0">
                            <i class="fas fa-phone text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-1">
                                <p class="font-semibold" style="color: var(--text-primary);">Cambio de teléfono</p>
                                <span class="text-xs text-gray-500">Hace 1 semana</span>
                            </div>
                            <p class="text-sm text-gray-600" style="color: var(--text-secondary);">
                                Actualizaste tu número de teléfono
                            </p>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-lg">
                                    <i class="fas fa-check mr-1"></i>Aprobado
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Cambio reciente 3 -->
                    <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition" style="background: var(--bg-secondary);">
                        <div class="bg-green-500 w-10 h-10 rounded-full flex items-center justify-center text-white flex-shrink-0">
                            <i class="fas fa-key text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-1">
                                <p class="font-semibold" style="color: var(--text-primary);">Cambio de contraseña</p>
                                <span class="text-xs text-gray-500">Hace 2 semanas</span>
                            </div>
                            <p class="text-sm text-gray-600" style="color: var(--text-secondary);">
                                Cambiaste tu contraseña de acceso
                            </p>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-lg">
                                    <i class="fas fa-check mr-1"></i>Aprobado
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Cambio reciente 4 -->
                    <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition" style="background: var(--bg-secondary);">
                        <div class="bg-green-500 w-10 h-10 rounded-full flex items-center justify-center text-white flex-shrink-0">
                            <i class="fas fa-envelope text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-1">
                                <p class="font-semibold" style="color: var(--text-primary);">Actualización de email</p>
                                <span class="text-xs text-gray-500">Hace 1 mes</span>
                            </div>
                            <p class="text-sm text-gray-600" style="color: var(--text-secondary);">
                                Actualizaste tu correo electrónico institucional
                            </p>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-lg">
                                    <i class="fas fa-check mr-1"></i>Aprobado
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mensaje informativo -->
                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-info-circle text-blue-500 mt-1"></i>
                        <div class="text-sm text-blue-800">
                            <p class="font-semibold mb-1">Información</p>
                            <p>Los cambios en tu perfil son revisados por el coordinador antes de ser aplicados. Recibirás una notificación cuando sean aprobados.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="asignaciones" class="view-section hidden">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100" style="background: var(--card-bg); border-color: var(--border-color);">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm mb-1">Total Asignaciones</p>
                            <p class="text-4xl font-bold text-green-500" id="totalAsignaciones">0</p>
                        </div>
                        <div class="bg-green-100 w-14 h-14 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-clipboard-list text-green-500 text-2xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100" style="background: var(--card-bg); border-color: var(--border-color);">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm mb-1">Asignaciones Activas</p>
                            <p class="text-4xl font-bold text-green-500" id="asignacionesActivas">0</p>
                        </div>
                        <div class="bg-green-100 w-14 h-14 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100" style="background: var(--card-bg); border-color: var(--border-color);">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm mb-1">Horas Semanales</p>
                            <p class="text-4xl font-bold text-green-500" id="horasSemanales">0</p>
                        </div>
                        <div class="bg-green-100 w-14 h-14 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-clock text-green-500 text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Calendario Visual -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-8" style="background: var(--card-bg); border-color: var(--border-color);">
                <div class="border-b border-gray-100 px-6 py-4 flex justify-between items-center" style="border-color: var(--border-color);">
                    <h3 class="text-xl font-bold" style="color: var(--text-primary);">
                        <i class="fas fa-calendar-alt mr-2 text-green-500"></i>Calendario de Asignaciones
                    </h3>
                    <div class="flex gap-2">
                        <button onclick="cambiarVistaCalendario('dayGridMonth')" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition text-sm">
                            <i class="fas fa-calendar mr-1"></i>Mes
                        </button>
                        <button onclick="cambiarVistaCalendario('timeGridWeek')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm">
                            <i class="fas fa-calendar-week mr-1"></i>Semana
                        </button>
                        <button onclick="cambiarVistaCalendario('timeGridDay')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm">
                            <i class="fas fa-calendar-day mr-1"></i>Día
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div id="calendarioAsignaciones"></div>
                </div>
            </div>
            
            <!-- Tabla de Asignaciones -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden" style="background: var(--card-bg); border-color: var(--border-color);">
                <div class="border-b border-gray-100 px-6 py-4 flex justify-between items-center" style="border-color: var(--border-color);">
                    <h3 class="text-xl font-bold" style="color: var(--text-primary);">Listado de Asignaciones</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full" id="tablaAsignaciones">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">FICHA</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">COMPETENCIA</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">AMBIENTE</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">DÍAS</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">HORARIO</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">FECHA INICIO</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">ESTADO</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100" id="tablaAsignacionesBody">
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

        <section id="competencias" class="view-section hidden">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100" style="background: var(--card-bg); border-color: var(--border-color);">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm mb-1">Total Competencias</p>
                            <p class="text-4xl font-bold text-green-500">2</p>
                        </div>
                        <div class="bg-green-100 w-14 h-14 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-award text-green-500 text-2xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100" style="background: var(--card-bg); border-color: var(--border-color);">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm mb-1">Horas Totales</p>
                            <p class="text-4xl font-bold text-green-500">168</p>
                        </div>
                        <div class="bg-green-100 w-14 h-14 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-clock text-green-500 text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tabla de Competencias -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden" style="background: var(--card-bg); border-color: var(--border-color);">
                <div class="border-b border-gray-100 px-6 py-4" style="border-color: var(--border-color);">
                    <h3 class="text-xl font-bold" style="color: var(--text-primary);">Mis Competencias Técnicas</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">CÓDIGO</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">NOMBRE</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">HORAS</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">TIPO</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr class="hover:bg-green-50 transition cursor-pointer">
                                <td class="px-6 py-4"><span class="text-sm font-semibold text-green-500">220501092</span></td>
                                <td class="px-6 py-4"><span class="text-sm text-gray-700">Análisis de Requisitos</span></td>
                                <td class="px-6 py-4"><span class="text-sm text-gray-600">120</span></td>
                                <td class="px-6 py-4"><span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">Técnica</span></td>
                            </tr>
                            <tr class="hover:bg-green-50 transition cursor-pointer">
                                <td class="px-6 py-4"><span class="text-sm font-semibold text-green-500">240201524</span></td>
                                <td class="px-6 py-4"><span class="text-sm text-gray-700">Ética para la vida</span></td>
                                <td class="px-6 py-4"><span class="text-sm text-gray-600">48</span></td>
                                <td class="px-6 py-4"><span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">Transversal</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section id="programas" class="view-section hidden">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100" style="background: var(--card-bg); border-color: var(--border-color);">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm mb-1">Total Programas</p>
                            <p class="text-4xl font-bold text-green-500">2</p>
                        </div>
                        <div class="bg-green-100 w-14 h-14 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-book text-green-500 text-2xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100" style="background: var(--card-bg); border-color: var(--border-color);">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm mb-1">Programas Activos</p>
                            <p class="text-4xl font-bold text-green-500">2</p>
                        </div>
                        <div class="bg-green-100 w-14 h-14 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tabla de Programas -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden" style="background: var(--card-bg); border-color: var(--border-color);">
                <div class="border-b border-gray-100 px-6 py-4" style="border-color: var(--border-color);">
                    <h3 class="text-xl font-bold" style="color: var(--text-primary);">Programas de Formación</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">CÓDIGO</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">DENOMINACIÓN</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">NIVEL</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">DURACIÓN</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">TIPO</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr class="hover:bg-green-50 transition cursor-pointer">
                                <td class="px-6 py-4"><span class="text-sm font-semibold text-green-500">228106</span></td>
                                <td class="px-6 py-4"><span class="text-sm text-gray-700">Análisis y Desarrollo de Software</span></td>
                                <td class="px-6 py-4"><span class="text-sm text-gray-600">Tecnólogo</span></td>
                                <td class="px-6 py-4"><span class="text-sm text-gray-600">27 Meses</span></td>
                                <td class="px-6 py-4"><span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">Presencial</span></td>
                            </tr>
                            <tr class="hover:bg-green-50 transition cursor-pointer">
                                <td class="px-6 py-4"><span class="text-sm font-semibold text-green-500">133100</span></td>
                                <td class="px-6 py-4"><span class="text-sm text-gray-700">Contabilidad y Finanzas</span></td>
                                <td class="px-6 py-4"><span class="text-sm text-gray-600">Técnico</span></td>
                                <td class="px-6 py-4"><span class="text-sm text-gray-600">12 Meses</span></td>
                                <td class="px-6 py-4"><span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">Presencial</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section id="transversales" class="view-section hidden">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100" style="background: var(--card-bg); border-color: var(--border-color);">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm mb-1">Total Transversales</p>
                            <p class="text-4xl font-bold text-green-500">1</p>
                        </div>
                        <div class="bg-green-100 w-14 h-14 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-project-diagram text-green-500 text-2xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100" style="background: var(--card-bg); border-color: var(--border-color);">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm mb-1">Horas Totales</p>
                            <p class="text-4xl font-bold text-green-500">48</p>
                        </div>
                        <div class="bg-green-100 w-14 h-14 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-clock text-green-500 text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tabla de Transversales -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden" style="background: var(--card-bg); border-color: var(--border-color);">
                <div class="border-b border-gray-100 px-6 py-4" style="border-color: var(--border-color);">
                    <h3 class="text-xl font-bold" style="color: var(--text-primary);">Competencias Transversales</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">CÓDIGO</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">NOMBRE</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">DESCRIPCIÓN</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">HORAS</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr class="hover:bg-green-50 transition cursor-pointer">
                                <td class="px-6 py-4"><span class="text-sm font-semibold text-green-500">240201524</span></td>
                                <td class="px-6 py-4"><span class="text-sm text-gray-700">Ética para la vida</span></td>
                                <td class="px-6 py-4"><span class="text-sm text-gray-600">Promover la interacción idónea consigo mismo, con los demás y con la naturaleza</span></td>
                                <td class="px-6 py-4"><span class="text-sm text-gray-600">48</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section id="fichas" class="view-section hidden">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100" style="background: var(--card-bg); border-color: var(--border-color);">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm mb-1">Total Fichas</p>
                            <p class="text-4xl font-bold text-green-500">2</p>
                        </div>
                        <div class="bg-green-100 w-14 h-14 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-layer-group text-green-500 text-2xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100" style="background: var(--card-bg); border-color: var(--border-color);">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm mb-1">Aprendices Totales</p>
                            <p class="text-4xl font-bold text-green-500">60</p>
                        </div>
                        <div class="bg-green-100 w-14 h-14 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-user-graduate text-green-500 text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tabla de Fichas -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden" style="background: var(--card-bg); border-color: var(--border-color);">
                <div class="border-b border-gray-100 px-6 py-4" style="border-color: var(--border-color);">
                    <h3 class="text-xl font-bold" style="color: var(--text-primary);">Fichas Asignadas</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">CÓDIGO</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">PROGRAMA</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">APRENDICES</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">FECHA INICIO</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">ESTADO</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr class="hover:bg-green-50 transition cursor-pointer">
                                <td class="px-6 py-4"><span class="text-sm font-semibold text-green-500">2504321</span></td>
                                <td class="px-6 py-4"><span class="text-sm text-gray-700">ADSO</span></td>
                                <td class="px-6 py-4"><span class="text-sm text-gray-600">28</span></td>
                                <td class="px-6 py-4"><span class="text-sm text-gray-600">15/01/2024</span></td>
                                <td class="px-6 py-4"><span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">Activa</span></td>
                            </tr>
                            <tr class="hover:bg-green-50 transition cursor-pointer">
                                <td class="px-6 py-4"><span class="text-sm font-semibold text-green-500">2619000</span></td>
                                <td class="px-6 py-4"><span class="text-sm text-gray-700">Contabilidad y Finanzas</span></td>
                                <td class="px-6 py-4"><span class="text-sm text-gray-600">32</span></td>
                                <td class="px-6 py-4"><span class="text-sm text-gray-600">01/03/2024</span></td>
                                <td class="px-6 py-4"><span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">Activa</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <!-- Modal Ver Perfil Instructor -->
    <div id="modal-ver-perfil-inst" class="modal">
        <div class="modal-content" style="max-width: 450px; width: 90%;">
            <div class="flex justify-between items-center mb-3">
                <h2 class="text-base font-bold text-slate-800">
                    <i class="fas fa-user-circle mr-2" style="color: #10b981;"></i>Mi Perfil
                </h2>
                <button onclick="cerrarModalVerPerfilInst()" class="text-gray-400 hover:text-gray-600 transition">
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
                                <p class="text-xs text-slate-800 truncate leading-tight" id="perfil-email-inst"><?php echo htmlspecialchars($_SESSION['email'] ?? 'instructor@sena.edu.co'); ?></p>
                            </div>
                        </div>
                        <div class="flex items-center py-1">
                            <div class="w-6 h-6 rounded-lg bg-green-50 flex items-center justify-center mr-2 flex-shrink-0">
                                <i class="fas fa-phone text-green-600" style="font-size: 10px;"></i>
                            </div>
                            <div class="flex-1">
                                <label class="text-xs text-gray-500 block leading-tight">TELÉFONO</label>
                                <p class="text-xs text-slate-800 leading-tight" id="perfil-telefono-inst">+57 300 123 4567</p>
                            </div>
                        </div>
                        <div class="flex items-center py-1">
                            <div class="w-6 h-6 rounded-lg bg-green-50 flex items-center justify-center mr-2 flex-shrink-0">
                                <i class="fas fa-id-card text-green-600" style="font-size: 10px;"></i>
                            </div>
                            <div class="flex-1">
                                <label class="text-xs text-gray-500 block leading-tight">DOCUMENTO</label>
                                <p class="text-xs text-slate-800 leading-tight" id="perfil-documento-inst">CC 1234567890</p>
                            </div>
                        </div>
                        <div class="flex items-center py-1">
                            <div class="w-6 h-6 rounded-lg bg-green-50 flex items-center justify-center mr-2 flex-shrink-0">
                                <i class="fas fa-hashtag text-green-600" style="font-size: 10px;"></i>
                            </div>
                            <div class="flex-1">
                                <label class="text-xs text-gray-500 block leading-tight">NÚMERO DE REGISTRO</label>
                                <p class="text-xs text-slate-800 leading-tight" id="perfil-registro-inst">REG-2024-001</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Información Académica -->
                <div>
                    <h4 class="text-xs font-bold text-gray-600 uppercase mb-1.5 flex items-center">
                        <i class="fas fa-graduation-cap mr-1 text-green-500 text-xs"></i>Información Académica
                    </h4>
                    <div class="space-y-1.5">
                        <div class="flex items-center py-1">
                            <div class="w-6 h-6 rounded-lg bg-green-50 flex items-center justify-center mr-2 flex-shrink-0">
                                <i class="fas fa-book text-green-600" style="font-size: 10px;"></i>
                            </div>
                            <div class="flex-1">
                                <label class="text-xs text-gray-500 block leading-tight">ESPECIALIDAD</label>
                                <p class="text-xs text-slate-800 leading-tight" id="perfil-especialidad-inst">ADSO</p>
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
                <button type="button" onclick="cerrarModalVerPerfilInst()" class="px-3 py-1.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-xs">
                    Cerrar
                </button>
                <button type="button" onclick="cerrarModalVerPerfilInst(); abrirModalEditarPerfil();" class="px-3 py-1.5 rounded-lg text-white hover:opacity-90 transition text-xs" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <i class="fas fa-edit mr-1"></i>Editar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Configuración Instructor -->
    <div id="modal-configuracion-inst" class="modal">
        <div class="modal-content" style="max-width: 700px;">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-slate-800">
                    <i class="fas fa-cog mr-2" style="color: #10b981;"></i>Configuración
                </h2>
                <button onclick="cerrarModalConfiguracionInst()" class="text-gray-400 hover:text-gray-600">
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
                        <button id="themeToggleConfigInst" class="w-14 h-7 bg-gray-300 rounded-full relative transition-colors duration-300">
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
                            <span class="font-medium text-slate-800" id="config-tabs-info-inst">-</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end mt-6">
                <button type="button" onclick="cerrarModalConfiguracionInst()" class="px-6 py-2 rounded-xl text-white hover:opacity-90" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Perfil -->
    <div id="modal-editar-perfil" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-slate-800">Editar Mi Perfil</h2>
                <button onclick="cerrarModalEditarPerfil()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="form-editar-perfil" class="space-y-4">
                <div class="flex justify-center mb-4">
                    <div class="relative">
                        <div class="bg-green-500 w-24 h-24 rounded-full flex items-center justify-center text-white text-4xl font-bold">
                            <?php echo strtoupper(substr($initials, 0, 1)); ?>
                        </div>
                        <button type="button" class="absolute bottom-0 right-0 bg-white border-2 border-green-500 rounded-full w-8 h-8 flex items-center justify-center text-green-500 hover:bg-green-50">
                            <i class="fas fa-camera text-sm"></i>
                        </button>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre *</label>
                        <input type="text" id="edit-nombre" name="nombre" value="<?php echo htmlspecialchars(explode(' ', $user_name)[0] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Apellido *</label>
                        <input type="text" id="edit-apellido" name="apellido" value="<?php echo htmlspecialchars(implode(' ', array_slice(explode(' ', $user_name), 1)) ?: ''); ?>" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 outline-none" required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                    <input type="email" id="edit-email" name="email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 outline-none" required>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Teléfono</label>
                        <input type="tel" id="edit-telefono" name="telefono" placeholder="3001234567" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Documento</label>
                        <input type="text" id="edit-documento" name="documento" placeholder="1234567890" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Número de Registro</label>
                    <input type="text" id="edit-registro" name="registro" placeholder="REG-2024-001" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Especialidad</label>
                    <select id="edit-especialidad" name="especialidad" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 outline-none">
                        <option value="">-- Seleccione una especialidad --</option>
                        <option value="ADSO">Análisis y Desarrollo de Software (ADSO)</option>
                        <option value="Multimedia">Diseño Multimedia</option>
                        <option value="Redes">Redes y Telecomunicaciones</option>
                        <option value="Bases de Datos">Bases de Datos</option>
                        <option value="Programación Web">Programación Web</option>
                        <option value="Diseño Gráfico">Diseño Gráfico</option>
                        <option value="Contabilidad">Contabilidad y Finanzas</option>
                        <option value="Gestión Empresarial">Gestión Empresarial</option>
                        <option value="Marketing">Marketing Digital</option>
                        <option value="Logística">Logística y Transporte</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Biografía</label>
                    <textarea id="edit-biografia" name="biografia" rows="3" placeholder="Cuéntanos sobre tu experiencia y formación..." class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 outline-none resize-none"></textarea>
                </div>

                <div class="border-t border-gray-200 pt-4 mt-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Cambiar Contraseña (Opcional)</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Contraseña Actual</label>
                            <input type="password" id="edit-password-actual" name="password_actual" placeholder="Dejar en blanco si no desea cambiar" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nueva Contraseña</label>
                            <input type="password" id="edit-password-nueva" name="password_nueva" placeholder="Mínimo 6 caracteres" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Confirmar Nueva Contraseña</label>
                            <input type="password" id="edit-password-confirmar" name="password_confirmar" placeholder="Repetir nueva contraseña" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 outline-none">
                        </div>
                    </div>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-exclamation-triangle text-yellow-500 mt-1"></i>
                        <div class="text-sm text-yellow-800">
                            <p class="font-semibold mb-1">Importante</p>
                            <p>Los cambios en tu perfil serán revisados por el coordinador antes de ser aplicados.</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="cerrarModalEditarPerfil()" class="px-6 py-2 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit" class="px-6 py-2 bg-green-500 text-white rounded-xl hover:bg-green-600 flex items-center gap-2">
                        <i class="fas fa-save"></i>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Notificar Coordinador -->
    <div id="modal-notificar-coordinador" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-slate-800">Enviar Notificación al Coordinador</h2>
                <button onclick="cerrarModalNotificarCoordinador()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="form-notificar-coordinador" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Tipo de Notificación <span class="text-red-500">*</span>
                    </label>
                    <select id="tipo-notif-coord" name="tipo" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 outline-none" required>
                        <option value="general">General</option>
                        <option value="cambio_perfil">Cambio de Perfil</option>
                        <option value="solicitud">Solicitud</option>
                        <option value="consulta">Consulta</option>
                        <option value="urgente">Urgente</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Asunto <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="titulo-notif-coord" name="titulo" placeholder="Escribe el asunto de la notificación" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 outline-none" required>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Mensaje <span class="text-red-500">*</span>
                    </label>
                    <textarea id="mensaje-notif-coord" name="mensaje" rows="5" placeholder="Escribe tu mensaje aquí..." class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 outline-none resize-none" required></textarea>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-info-circle text-blue-500 mt-1"></i>
                        <div class="text-sm text-blue-800">
                            <p class="font-semibold mb-1">Información</p>
                            <p>El coordinador recibirá esta notificación en su panel y podrá verla en tiempo real.</p>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="cerrarModalNotificarCoordinador()" class="px-6 py-2 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit" class="px-6 py-2 bg-green-500 text-white rounded-xl hover:bg-green-600 flex items-center gap-2">
                        <i class="fas fa-paper-plane"></i>
                        Enviar Notificación
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Sidebar toggle
        function toggleSidebar(){
            const sb = document.getElementById('sidebar');
            if(!sb) return;
            sb.classList.toggle('collapsed');
            localStorage.setItem('sidebar-collapsed', sb.classList.contains('collapsed'));
        }

        // Función para cerrar sidebar en móviles al hacer clic en el overlay
        function cerrarSidebarMovil(){
            const sb = document.getElementById('sidebar');
            if(sb && window.innerWidth <= 768){
                sb.classList.add('collapsed');
            }
        }
        
        // Cerrar sidebar al hacer clic fuera en móviles
        document.addEventListener('click', function(e) {
            if(window.innerWidth <= 768){
                const sb = document.getElementById('sidebar');
                const toggleBtn = sb?.querySelector('.toggle-btn');
                
                if(sb && !sb.classList.contains('collapsed')){
                    // Si el clic no fue en el sidebar ni en el botón toggle
                    if(!sb.contains(e.target) && !toggleBtn?.contains(e.target)){
                        sb.classList.add('collapsed');
                    }
                }
            }
        });

        document.addEventListener('DOMContentLoaded', function(){
            // Inicializar contexto de rol para esta pestaña - COMENTADO TEMPORALMENTE
            // const roleContext = initRoleContext('instructor');
            // window.roleContext = roleContext;
            
            // Log de información (solo en desarrollo) - COMENTADO TEMPORALMENTE
            /*
            if (window.location.hostname === 'localhost') {
                const info = roleContext.getActiveTabsInfo();
                console.log('📊 Dashboard Instructor - Pestañas activas:', info);
                
                if (info.coordinador > 0) {
                    console.log('ℹ️ Hay ' + info.coordinador + ' pestaña(s) de Coordinador abierta(s)');
                }
            }
            */
            
            const sb = document.getElementById('sidebar');
            
            // En móviles, iniciar siempre colapsado
            if(window.innerWidth <= 768){
                if(sb) sb.classList.add('collapsed');
            } else {
                // En escritorio, usar el estado guardado
                if(sb && localStorage.getItem('sidebar-collapsed') === 'true'){
                    sb.classList.add('collapsed');
                }
            }

            // Theme toggle
            const themeToggle = document.getElementById('themeToggle');
            const themeIcon = document.getElementById('themeIcon');
            const currentTheme = localStorage.getItem('theme') || 'light';
            
            if (currentTheme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
            }
            
            themeToggle.addEventListener('click', function() {
                const theme = document.documentElement.getAttribute('data-theme');
                if (theme === 'dark') {
                    document.documentElement.setAttribute('data-theme', 'light');
                    localStorage.setItem('theme', 'light');
                    themeIcon.classList.remove('fa-sun');
                    themeIcon.classList.add('fa-moon');
                } else {
                    document.documentElement.setAttribute('data-theme', 'dark');
                    localStorage.setItem('theme', 'dark');
                    themeIcon.classList.remove('fa-moon');
                    themeIcon.classList.add('fa-sun');
                }
            });

            // Profile menu toggle
            const profileToggle = document.getElementById('profileToggle');
            const profileMenu = document.getElementById('profileMenu');
            
            if (profileToggle && profileMenu) {
                profileToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    profileMenu.classList.toggle('hidden');
                });
                
                document.addEventListener('click', function(e) {
                    if (!profileMenu.contains(e.target) && !profileToggle.contains(e.target)) {
                        profileMenu.classList.add('hidden');
                    }
                });
            }

            // Notification system
            const notifBtn = document.getElementById('notifBtn');
            const notifMenu = document.getElementById('notifMenu');
            const notifList = document.getElementById('notifList');
            const notifDot = document.getElementById('notifDot');
            const markAllRead = document.getElementById('markAllRead');

            // Cargar notificaciones desde la base de datos
            let notifications = [];

            function cargarNotificaciones() {
                fetch('api/notificaciones.php?action=listar')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            notifications = data.notificaciones.map(n => ({
                                id: n.id,
                                message: n.titulo,
                                detail: n.mensaje,
                                time: formatearTiempo(n.fecha_creacion),
                                read: n.leida == 1,
                                tipo: n.tipo
                            }));
                            renderNotifications();
                            actualizarContadorNoLeidas();
                        }
                    })
                    .catch(error => console.error('Error al cargar notificaciones:', error));
            }

            function formatearTiempo(fecha) {
                const ahora = new Date();
                const fechaNotif = new Date(fecha);
                const diff = Math.floor((ahora - fechaNotif) / 1000); // segundos

                if (diff < 60) return 'Hace un momento';
                if (diff < 3600) return `Hace ${Math.floor(diff / 60)} min`;
                if (diff < 86400) return `Hace ${Math.floor(diff / 3600)} hora${Math.floor(diff / 3600) > 1 ? 's' : ''}`;
                if (diff < 604800) return `Hace ${Math.floor(diff / 86400)} día${Math.floor(diff / 86400) > 1 ? 's' : ''}`;
                return fechaNotif.toLocaleDateString();
            }

            function actualizarContadorNoLeidas() {
                fetch('api/notificaciones.php?action=contar_no_leidas')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.total > 0) {
                            notifDot.style.display = 'block';
                        } else {
                            notifDot.style.display = 'none';
                        }
                    });
            }

            function renderNotifications() {
                if (notifications.length === 0) {
                    notifList.innerHTML = '<div class="notif-item" style="font-size:13px;">No hay nuevas notificaciones</div>';
                    notifDot.style.display = 'none';
                    return;
                }

                const unreadCount = notifications.filter(n => !n.read).length;
                if (unreadCount > 0) {
                    notifDot.style.display = 'block';
                } else {
                    notifDot.style.display = 'none';
                }

                notifList.innerHTML = notifications.map(notif => `
                    <div class="notif-item" data-id="${notif.id}" style="font-size:13px; ${notif.read ? 'opacity:0.6;' : ''}">
                        <div style="display:flex; align-items:start; gap:8px;">
                            <i class="fas fa-${getIconoTipo(notif.tipo)}" style="margin-top:2px;"></i>
                            <div style="flex:1;">
                                <p style="margin:0; font-weight:${notif.read ? '400' : '600'};">${notif.message}</p>
                                ${notif.detail ? `<p style="margin:4px 0 0 0; font-size:11px; opacity:0.9;">${notif.detail}</p>` : ''}
                                <small style="opacity:0.8;">${notif.time}</small>
                            </div>
                        </div>
                    </div>
                `).join('');

                // Add click handlers to mark as read
                document.querySelectorAll('.notif-item[data-id]').forEach(item => {
                    item.addEventListener('click', function() {
                        const id = parseInt(this.getAttribute('data-id'));
                        marcarComoLeida(id);
                    });
                });
            }

            function getIconoTipo(tipo) {
                const iconos = {
                    'asignacion': 'clipboard-list',
                    'cambio_horario': 'clock',
                    'recordatorio': 'bell',
                    'general': 'envelope',
                    'urgente': 'exclamation-circle'
                };
                return iconos[tipo] || 'envelope';
            }

            function marcarComoLeida(id) {
                const formData = new FormData();
                formData.append('action', 'marcar_leida');
                formData.append('notificacion_id', id);

                fetch('api/notificaciones.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const notif = notifications.find(n => n.id === id);
                        if (notif) {
                            notif.read = true;
                            renderNotifications();
                            actualizarContadorNoLeidas();
                        }
                    }
                })
                .catch(error => console.error('Error al marcar como leída:', error));
            }

            if (notifBtn && notifMenu) {
                notifBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    notifMenu.classList.toggle('hidden');
                    
                    // Cerrar sidebar en móviles al abrir notificaciones
                    if(window.innerWidth <= 768){
                        const sb = document.getElementById('sidebar');
                        if(sb && !sb.classList.contains('collapsed')){
                            sb.classList.add('collapsed');
                        }
                    }
                });

                document.addEventListener('click', function(e) {
                    if (!notifMenu.contains(e.target) && !notifBtn.contains(e.target)) {
                        notifMenu.classList.add('hidden');
                    }
                });
            }

            if (markAllRead) {
                markAllRead.addEventListener('click', function() {
                    const formData = new FormData();
                    formData.append('action', 'marcar_todas_leidas');

                    fetch('api/notificaciones.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            notifications.forEach(n => n.read = true);
                            renderNotifications();
                            actualizarContadorNoLeidas();
                        }
                    })
                    .catch(error => console.error('Error al marcar todas como leídas:', error));
                });
            }

            // Cargar notificaciones al inicio
            cargarNotificaciones();

            // Actualizar notificaciones cada 30 segundos
            setInterval(cargarNotificaciones, 30000);
        });

        // Show section
        function showSection(sectionId) {
            // Cerrar sidebar en móviles al cambiar de sección
            if(window.innerWidth <= 768){
                const sb = document.getElementById('sidebar');
                if(sb && !sb.classList.contains('collapsed')){
                    sb.classList.add('collapsed');
                }
            }
            
            // Hide all sections
            document.querySelectorAll('.view-section').forEach(sec => {
                sec.classList.remove('active');
                sec.classList.add('hidden');
            });
            
            // Show selected section
            const section = document.getElementById(sectionId);
            if (section) {
                section.classList.remove('hidden');
                section.classList.add('active');
            }
            
            // Update nav items
            document.querySelectorAll('.nav-item').forEach(btn => btn.classList.remove('active'));
            const navBtn = Array.from(document.querySelectorAll('.nav-item')).find(btn => {
                const attr = btn.getAttribute('onclick') || '';
                return attr.indexOf(`showSection('${sectionId}')`) !== -1;
            });
            if (navBtn) navBtn.classList.add('active');
            
            // Update title
            const titles = {
                'panel': 'Mi Panel',
                'perfil': 'Mi Perfil',
                'asignaciones': 'Mis Asignaciones',
                'competencias': 'Mis Competencias',
                'programas': 'Programas de Formación',
                'transversales': 'Competencias Transversales',
                'fichas': 'Fichas Asignadas'
            };
            document.getElementById('section-title').innerText = titles[sectionId] || 'Panel Instructor';
        }

        // Sistema de notificaciones tipo toast
        function showNotification(message, type = 'success') {
            const backgroundColor = type === 'success' ? 'linear-gradient(to right, #10b981, #059669)' : 
                                   type === 'error' ? 'linear-gradient(to right, #ef4444, #dc2626)' :
                                   type === 'warning' ? 'linear-gradient(to right, #f59e0b, #d97706)' :
                                   'linear-gradient(to right, #3b82f6, #2563eb)';
            
            Toastify({
                text: message,
                duration: 3000,
                gravity: "top",
                position: "right",
                style: {
                    background: backgroundColor,
                    borderRadius: "12px",
                    padding: "16px 24px",
                    fontSize: "14px",
                    fontWeight: "500",
                    boxShadow: "0 10px 25px rgba(0,0,0,0.1)"
                },
                stopOnFocus: true
            }).showToast();
        }

        // Funciones para el modal de editar perfil
        function abrirModalEditarPerfil() {
            const modal = document.getElementById('modal-editar-perfil');
            if (modal) {
                modal.classList.add('active');
            }
        }

        function cerrarModalEditarPerfil() {
            const modal = document.getElementById('modal-editar-perfil');
            if (modal) {
                modal.classList.remove('active');
            }
        }

        // Manejar envío del formulario de editar perfil
        document.addEventListener('DOMContentLoaded', function() {
            const formEditarPerfil = document.getElementById('form-editar-perfil');
            
            if (formEditarPerfil) {
                formEditarPerfil.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Validar contraseñas si se están cambiando
                    const passwordActual = document.getElementById('edit-password-actual').value;
                    const passwordNueva = document.getElementById('edit-password-nueva').value;
                    const passwordConfirmar = document.getElementById('edit-password-confirmar').value;
                    
                    if (passwordNueva || passwordConfirmar) {
                        if (!passwordActual) {
                            showNotification('Debe ingresar su contraseña actual para cambiarla', 'error');
                            return;
                        }
                        if (passwordNueva !== passwordConfirmar) {
                            showNotification('Las contraseñas nuevas no coinciden', 'error');
                            return;
                        }
                        if (passwordNueva.length < 6) {
                            showNotification('La nueva contraseña debe tener al menos 6 caracteres', 'error');
                            return;
                        }
                    }
                    
                    // Obtener los valores del formulario
                    const nombre = document.getElementById('edit-nombre').value;
                    const apellido = document.getElementById('edit-apellido').value;
                    const email = document.getElementById('edit-email').value;
                    const telefono = document.getElementById('edit-telefono').value;
                    const documento = document.getElementById('edit-documento').value;
                    const registro = document.getElementById('edit-registro').value;
                    const especialidad = document.getElementById('edit-especialidad').value;
                    const nombreCompleto = `${nombre} ${apellido}`;
                    
                    // Crear objeto con los datos actualizados
                    const datosActualizados = {
                        nombre: nombre,
                        apellido: apellido,
                        email: email,
                        telefono: telefono,
                        documento: documento,
                        registro: registro,
                        especialidad: especialidad
                    };
                    
                    // Actualizar la información en el perfil (simulación)
                    actualizarInformacionPerfil(nombreCompleto, email, telefono, documento, registro, especialidad);
                    
                    // Enviar notificación al coordinador con los datos actualizados
                    enviarNotificacionCoordinador(nombreCompleto, datosActualizados);
                    
                    // Mostrar notificación de éxito
                    showNotification('Perfil Actualizado', 'success');
                    cerrarModalEditarPerfil();
                    
                    // En producción, aquí enviarías los datos con fetch:
                    /*
                    const formData = new FormData(this);
                    fetch('api/actualizar_perfil.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            actualizarInformacionPerfil(nombreCompleto, email, telefono, documento, especialidad);
                            showNotification('Perfil actualizado exitosamente. Los cambios serán revisados por el coordinador.', 'success');
                            cerrarModalEditarPerfil();
                        } else {
                            showNotification('Error: ' + data.error, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Error al actualizar el perfil', 'error');
                    });
                    */
                });
            }
        });

        // Función para actualizar la información del perfil en el DOM
        function actualizarInformacionPerfil(nombreCompleto, email, telefono, documento, registro, especialidad) {
            // Actualizar el nombre en el header del perfil
            const nombreHeader = document.querySelector('#perfil h2');
            if (nombreHeader) {
                nombreHeader.textContent = nombreCompleto;
            }
            
            // Actualizar la información personal
            const infoCells = document.querySelectorAll('#perfil .p-4.bg-gray-50 p.font-semibold');
            if (infoCells.length >= 6) {
                // Nombre Completo
                infoCells[0].textContent = nombreCompleto;
                
                // Email
                if (email) {
                    infoCells[2].textContent = email;
                }
            }
            
            // Actualizar el avatar con las nuevas iniciales
            const nombres = nombreCompleto.split(' ');
            let iniciales = '';
            if (nombres.length >= 2) {
                iniciales = nombres[0].charAt(0).toUpperCase() + nombres[1].charAt(0).toUpperCase();
            } else {
                iniciales = nombres[0].charAt(0).toUpperCase();
            }
            
            const avatares = document.querySelectorAll('#perfil .bg-green-500');
            avatares.forEach(avatar => {
                if (avatar.classList.contains('rounded-full')) {
                    avatar.textContent = iniciales.charAt(0);
                }
            });
            
            // Actualizar información en el modal de ver perfil
            const perfilEmailInst = document.getElementById('perfil-email-inst');
            if (perfilEmailInst && email) {
                perfilEmailInst.textContent = email;
            }
            
            const perfilTelefonoInst = document.getElementById('perfil-telefono-inst');
            if (perfilTelefonoInst && telefono) {
                perfilTelefonoInst.textContent = telefono;
            }
            
            const perfilDocumentoInst = document.getElementById('perfil-documento-inst');
            if (perfilDocumentoInst && documento) {
                perfilDocumentoInst.textContent = 'CC ' + documento;
            }
            
            const perfilRegistroInst = document.getElementById('perfil-registro-inst');
            if (perfilRegistroInst && registro) {
                perfilRegistroInst.textContent = registro;
            }
            
            const perfilEspecialidadInst = document.getElementById('perfil-especialidad-inst');
            if (perfilEspecialidadInst && especialidad) {
                perfilEspecialidadInst.textContent = especialidad;
            }
            
            const perfilBiografiaInst = document.getElementById('perfil-biografia-inst');
            const biografia = document.getElementById('edit-biografia').value;
            if (perfilBiografiaInst && biografia) {
                perfilBiografiaInst.textContent = biografia;
            }
        }

        // Función para enviar notificación al coordinador sobre cambio de perfil
        function enviarNotificacionCoordinador(nombreInstructor, datosActualizados) {
            // Crear mensaje detallado con los cambios
            const detalles = `
                Nombre: ${datosActualizados.nombre} ${datosActualizados.apellido}
                Email: ${datosActualizados.email}
                Teléfono: ${datosActualizados.telefono || 'No especificado'}
                Documento: ${datosActualizados.documento || 'No especificado'}
                Registro: ${datosActualizados.registro || 'No especificado'}
                Especialidad: ${datosActualizados.especialidad || 'No especificada'}
            `.trim();
            
            // Datos de la notificación
            const notificacionData = {
                action: 'enviar_notificacion_coordinador',
                instructor_id: <?php echo $_SESSION['usuario_id'] ?? 0; ?>,
                instructor_nombre: nombreInstructor,
                tipo: 'cambio_perfil',
                titulo: 'Solicitud de Actualización de Perfil',
                mensaje: `${nombreInstructor} ha actualizado su perfil.\n\nDatos actualizados:\n${detalles}`
            };
            
            console.log('📤 Enviando notificación al coordinador:', notificacionData);
            
            // Enviar notificación al coordinador
            fetch('api/notificaciones.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(notificacionData)
            })
            .then(response => {
                console.log('📥 Respuesta HTTP:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('📥 Respuesta del servidor:', data);
                if (data.success) {
                    console.log('✅ Notificación enviada al coordinador exitosamente');
                    console.log('📝 ID de notificación:', data.notificacion_id);
                } else {
                    console.error('❌ Error al enviar notificación:', data.error || data.message);
                }
            })
            .catch(error => {
                console.error('❌ Error en la petición:', error);
            });
        }

        // ============================================
        // FUNCIONES PARA NOTIFICAR AL COORDINADOR
        // ============================================
        
        function abrirModalNotificarCoordinador() {
            const modal = document.getElementById('modal-notificar-coordinador');
            if (modal) {
                modal.classList.add('active');
            }
        }
        
        function cerrarModalNotificarCoordinador() {
            const modal = document.getElementById('modal-notificar-coordinador');
            if (modal) {
                modal.classList.remove('active');
                // Limpiar formulario
                document.getElementById('form-notificar-coordinador').reset();
            }
        }
        
        // Event listener para el botón de notificar coordinador
        document.addEventListener('DOMContentLoaded', function() {
            const btnNotificarCoord = document.getElementById('btnNotificarCoordinador');
            if (btnNotificarCoord) {
                btnNotificarCoord.addEventListener('click', abrirModalNotificarCoordinador);
            }
            
            // Event listener para el formulario
            const formNotificarCoord = document.getElementById('form-notificar-coordinador');
            if (formNotificarCoord) {
                formNotificarCoord.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const tipo = document.getElementById('tipo-notif-coord').value;
                    const titulo = document.getElementById('titulo-notif-coord').value.trim();
                    const mensaje = document.getElementById('mensaje-notif-coord').value.trim();
                    
                    if (!titulo || !mensaje) {
                        showNotification('Por favor completa todos los campos', 'error');
                        return;
                    }
                    
                    // Deshabilitar botón de envío
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...';
                    
                    const notificacionData = {
                        action: 'enviar_notificacion_coordinador',
                        instructor_id: <?php echo $_SESSION['usuario_id'] ?? 0; ?>,
                        instructor_nombre: '<?php echo htmlspecialchars($user_name); ?>',
                        tipo: tipo,
                        titulo: titulo,
                        mensaje: mensaje
                    };
                    
                    fetch('api/notificaciones.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(notificacionData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('✓ Notificación enviada exitosamente al coordinador', 'success');
                            cerrarModalNotificarCoordinador();
                        } else {
                            showNotification('Error: ' + (data.error || 'No se pudo enviar la notificación'), 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error al enviar notificación:', error);
                        showNotification('Error de conexión al enviar la notificación', 'error');
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    });
                });
            }
        });

        // Hover effect for table rows
        document.addEventListener('DOMContentLoaded', function() {
            const tableRows = document.querySelectorAll('table tbody tr');
            tableRows.forEach(row => {
                row.style.transition = 'all 0.2s ease';
                row.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = 'rgba(16, 185, 129, 0.08)';
                });
                row.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = '';
                });
            });
        });
        
        // ============================================
        // FUNCIONES PARA VER PERFIL Y CONFIGURACIÓN
        // ============================================
        
        function abrirModalVerPerfilInst() {
            console.log('Abriendo modal ver perfil instructor');
            const modal = document.getElementById('modal-ver-perfil-inst');
            console.log('Modal encontrado:', modal);
            if (modal) {
                modal.classList.add('active');
                console.log('Clase active agregada');
            } else {
                console.error('Modal modal-ver-perfil-inst no encontrado');
            }
        }
        
        function cerrarModalVerPerfilInst() {
            const modal = document.getElementById('modal-ver-perfil-inst');
            if (modal) {
                modal.classList.remove('active');
            }
        }
        
        function abrirModalConfiguracionInst() {
            console.log('Abriendo modal configuración instructor');
            const modal = document.getElementById('modal-configuracion-inst');
            console.log('Modal encontrado:', modal);
            if (modal) {
                modal.classList.add('active');
                console.log('Clase active agregada');
                
                // Actualizar información de pestañas activas - COMENTADO TEMPORALMENTE
                /*
                if (window.roleContext) {
                    const info = window.roleContext.getActiveTabsInfo();
                    const tabsInfo = document.getElementById('config-tabs-info-inst');
                    if (tabsInfo) {
                        tabsInfo.textContent = `${info.total} (${info.coordinador} Coordinador, ${info.instructor} Instructor)`;
                    }
                }
                */
            } else {
                console.error('Modal modal-configuracion-inst no encontrado');
            }
        }
        
        function cerrarModalConfiguracionInst() {
            const modal = document.getElementById('modal-configuracion-inst');
            if (modal) {
                modal.classList.remove('active');
            }
        }
        
        // Theme toggle en configuración del instructor
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggleConfigInst = document.getElementById('themeToggleConfigInst');
            if (themeToggleConfigInst) {
                // Sincronizar con el tema actual
                const currentTheme = localStorage.getItem('theme') || 'light';
                if (currentTheme === 'dark') {
                    themeToggleConfigInst.classList.add('bg-green-500');
                    themeToggleConfigInst.classList.remove('bg-gray-300');
                    themeToggleConfigInst.querySelector('span').style.transform = 'translateX(28px)';
                }
                
                themeToggleConfigInst.addEventListener('click', function() {
                    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
                    
                    if (isDark) {
                        document.documentElement.setAttribute('data-theme', 'light');
                        localStorage.setItem('theme', 'light');
                        themeToggleConfigInst.classList.remove('bg-green-500');
                        themeToggleConfigInst.classList.add('bg-gray-300');
                        themeToggleConfigInst.querySelector('span').style.transform = 'translateX(0)';
                        
                        // Actualizar icono del header
                        const themeIcon = document.getElementById('themeIcon');
                        if (themeIcon) {
                            themeIcon.classList.remove('fa-sun');
                            themeIcon.classList.add('fa-moon');
                        }
                    } else {
                        document.documentElement.setAttribute('data-theme', 'dark');
                        localStorage.setItem('theme', 'dark');
                        themeToggleConfigInst.classList.add('bg-green-500');
                        themeToggleConfigInst.classList.remove('bg-gray-300');
                        themeToggleConfigInst.querySelector('span').style.transform = 'translateX(28px)';
                        
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

        // ============================================
        // CALENDARIO DE ASIGNACIONES
        // ============================================
        let calendarioAsignaciones = null;
        
        function inicializarCalendario() {
            const calendarEl = document.getElementById('calendarioAsignaciones');
            if (!calendarEl) return;
            
            calendarioAsignaciones = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                buttonText: {
                    today: 'Hoy',
                    month: 'Mes',
                    week: 'Semana',
                    day: 'Día'
                },
                events: function(info, successCallback, failureCallback) {
                    cargarAsignacionesCalendario(successCallback, failureCallback);
                },
                eventClick: function(info) {
                    mostrarDetalleAsignacion(info.event);
                },
                height: 'auto',
                eventColor: '#10b981',
                eventDisplay: 'block'
            });
            
            calendarioAsignaciones.render();
        }
        
        function cambiarVistaCalendario(vista) {
            if (calendarioAsignaciones) {
                calendarioAsignaciones.changeView(vista);
            }
        }
        
        function cargarAsignacionesCalendario(successCallback, failureCallback) {
            fetch('api/asignaciones.php?action=listar')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.asignaciones) {
                        const eventos = data.asignaciones.map(asig => {
                            return {
                                title: `${asig.codigo_ficha} - ${asig.competencia}`,
                                start: `${asig.fecha_inicio}T${asig.hora_inicio || '08:00:00'}`,
                                end: `${asig.fecha_fin}T${asig.hora_fin || '17:00:00'}`,
                                extendedProps: {
                                    ficha: asig.codigo_ficha,
                                    competencia: asig.competencia,
                                    ambiente: asig.nombre_ambiente,
                                    instructor: asig.instructor_nombre,
                                    dias: asig.dias_semana,
                                    estado: asig.estado
                                }
                            };
                        });
                        successCallback(eventos);
                    } else {
                        failureCallback();
                    }
                })
                .catch(error => {
                    console.error('Error cargando asignaciones:', error);
                    failureCallback();
                });
        }
        
        function mostrarDetalleAsignacion(event) {
            const props = event.extendedProps;
            Toastify({
                text: `
                    <div style="text-align: left;">
                        <strong>${event.title}</strong><br>
                        <small>Ambiente: ${props.ambiente || 'No asignado'}</small><br>
                        <small>Días: ${props.dias || 'No especificado'}</small><br>
                        <small>Estado: ${props.estado}</small>
                    </div>
                `,
                duration: 5000,
                gravity: "top",
                position: "right",
                backgroundColor: "#10b981",
                escapeMarkup: false
            }).showToast();
        }
        
        // ============================================
        // CARGAR ASIGNACIONES EN TABLA
        // ============================================
        function cargarAsignacionesTabla() {
            fetch('api/asignaciones.php?action=listar')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.asignaciones) {
                        actualizarTablaAsignaciones(data.asignaciones);
                        actualizarEstadisticasAsignaciones(data.asignaciones);
                    }
                })
                .catch(error => {
                    console.error('Error cargando asignaciones:', error);
                    document.getElementById('tablaAsignacionesBody').innerHTML = `
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-red-500">
                                <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                                <p>Error al cargar asignaciones</p>
                            </td>
                        </tr>
                    `;
                });
        }
        
        function actualizarTablaAsignaciones(asignaciones) {
            const tbody = document.getElementById('tablaAsignacionesBody');
            if (!tbody) return;
            
            if (asignaciones.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>No tienes asignaciones registradas</p>
                        </td>
                    </tr>
                `;
                return;
            }
            
            tbody.innerHTML = asignaciones.map(asig => `
                <tr class="hover:bg-green-50 transition cursor-pointer">
                    <td class="px-6 py-4">
                        <span class="text-sm font-semibold text-green-500">${asig.codigo_ficha}</span>
                        <div class="text-xs text-gray-500">${asig.programa}</div>
                    </td>
                    <td class="px-6 py-4"><span class="text-sm text-gray-700">${asig.competencia}</span></td>
                    <td class="px-6 py-4"><span class="text-sm text-gray-600">${asig.nombre_ambiente || 'Sin asignar'}</span></td>
                    <td class="px-6 py-4"><span class="text-sm text-gray-600">${asig.dias_semana || 'No especificado'}</span></td>
                    <td class="px-6 py-4"><span class="text-sm text-gray-600">${asig.hora_inicio ? asig.hora_inicio.substring(0,5) : ''} - ${asig.hora_fin ? asig.hora_fin.substring(0,5) : ''}</span></td>
                    <td class="px-6 py-4"><span class="text-sm text-gray-600">${formatearFecha(asig.fecha_inicio)}</span></td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full ${getEstadoColor(asig.estado)}">
                            ${asig.estado}
                        </span>
                    </td>
                </tr>
            `).join('');
        }
        
        function actualizarEstadisticasAsignaciones(asignaciones) {
            document.getElementById('totalAsignaciones').textContent = asignaciones.length;
            const activas = asignaciones.filter(a => a.estado === 'En Curso' || a.estado === 'Programada').length;
            document.getElementById('asignacionesActivas').textContent = activas;
            
            // Calcular horas semanales (estimado)
            let horasTotal = 0;
            asignaciones.forEach(asig => {
                if (asig.hora_inicio && asig.hora_fin) {
                    const inicio = new Date(`2000-01-01T${asig.hora_inicio}`);
                    const fin = new Date(`2000-01-01T${asig.hora_fin}`);
                    const horas = (fin - inicio) / (1000 * 60 * 60);
                    const dias = asig.dias_semana ? asig.dias_semana.split(',').length : 1;
                    horasTotal += horas * dias;
                }
            });
            document.getElementById('horasSemanales').textContent = Math.round(horasTotal);
        }
        
        function getEstadoColor(estado) {
            const colores = {
                'Programada': 'bg-blue-100 text-blue-800',
                'En Curso': 'bg-green-100 text-green-800',
                'Finalizada': 'bg-gray-100 text-gray-800',
                'Cancelada': 'bg-red-100 text-red-800'
            };
            return colores[estado] || 'bg-gray-100 text-gray-800';
        }
        
        function formatearFecha(fecha) {
            if (!fecha) return '';
            const d = new Date(fecha + 'T00:00:00');
            return d.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
        }
        
        // ============================================
        // SISTEMA DE NOTIFICACIONES
        // ============================================
        function cargarNotificaciones() {
            fetch('api/notificaciones.php?action=listar')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.notificaciones) {
                        actualizarContadorNotificaciones(data.notificaciones);
                    }
                })
                .catch(error => console.error('Error cargando notificaciones:', error));
        }
        
        function actualizarContadorNotificaciones(notificaciones) {
            const noLeidas = notificaciones.filter(n => n.leida == 0).length;
            const badge = document.querySelector('#notifBtn .absolute');
            if (badge) {
                if (noLeidas > 0) {
                    badge.classList.remove('hidden');
                    badge.textContent = noLeidas > 9 ? '9+' : noLeidas;
                } else {
                    badge.classList.add('hidden');
                }
            }
        }
        
        // Cargar notificaciones cada 30 segundos
        setInterval(cargarNotificaciones, 30000);
        
        // ============================================
        // INICIALIZACIÓN
        // ============================================
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar calendario cuando se muestre la sección de asignaciones
            const asignacionesBtn = document.querySelector('button[onclick="showSection(\'asignaciones\')"]');
            if (asignacionesBtn) {
                asignacionesBtn.addEventListener('click', function() {
                    setTimeout(() => {
                        if (!calendarioAsignaciones) {
                            inicializarCalendario();
                        }
                        cargarAsignacionesTabla();
                    }, 100);
                });
            }
            
            // Cargar notificaciones al inicio
            cargarNotificaciones();
        });
    </script>
</body>
</html>
