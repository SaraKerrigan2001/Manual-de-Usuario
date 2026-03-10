<?php
// admin_header.php - Reusable premium layout header
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Verificar autenticación centralizada
if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] !== 'administrador' && $_SESSION['rol'] !== 'coordinador')) {
    header('Location: index.php?controlador=Auth&accion=login');
    exit;
}

// Valores útiles para mostrar en el perfil
$user_name = $_SESSION['user_name'] ?? $_SESSION['nombre'] ?? 'Administrador';
$user_role_db = $_SESSION['rol'] ?? 'administrador';
$user_role = ($user_role_db === 'administrador') ? 'Administrador' : 'Coordinador';

// Iniciales para el avatar
$initials = '';
$parts = preg_split('/\s+/', trim($user_name));
if (count($parts) >= 2) {
    $initials = strtoupper(substr($parts[0],0,1) . substr($parts[1],0,1));
} else {
    $initials = strtoupper(substr($parts[0],0,1));
}

// Detectar sección activa para el menú
$active_section = $active_section ?? 'panel';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo ?? 'Panel SENA'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <style>
        :root { 
            --green-400: #34d399;
            --green-500: #10b981;
            --green-600: #059669;
            --green-700: #047857;
            
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
        
        [data-theme="dark"] .bg-white { background-color: var(--card-bg) !important; }
        [data-theme="dark"] .bg-gray-50 { background-color: var(--bg-tertiary) !important; }
        [data-theme="dark"] .text-slate-800, [data-theme="dark"] .text-gray-800, [data-theme="dark"] .text-gray-900 { color: var(--text-primary) !important; }
        [data-theme="dark"] .text-gray-700, [data-theme="dark"] .text-gray-600 { color: var(--text-secondary) !important; }
        [data-theme="dark"] .text-gray-500 { color: var(--text-tertiary) !important; }
        [data-theme="dark"] .border-gray-100, [data-theme="dark"] .border-gray-200 { border-color: var(--border-color) !important; }
        [data-theme="dark"] .shadow-sm { box-shadow: 0 1px 2px 0 var(--card-shadow) !important; }
        [data-theme="dark"] .sidebar { background: var(--bg-sidebar) !important; border-right: 1px solid var(--border-color); }
        
        .bg-green-500 { background-color: #10b981 !important; }
        .text-green-500 { color: #10b981 !important; }
        
        .sidebar { position: relative; width: 288px; transition: width 200ms ease; padding-bottom: 80px; background: #e2f3e4; box-shadow: 4px 0 10px var(--card-shadow); border-right: 1px solid var(--border-color); }
        .sidebar.collapsed { width: 72px; }
        .sidebar .nav-item { display: flex; align-items: center; gap: 12px; }
        .sidebar .nav-item i { min-width: 20px; text-align: center; }
        .sidebar.collapsed .nav-item { justify-content: center; padding-left: 0; padding-right: 0; }
        .sidebar.collapsed .nav-item span, .sidebar.collapsed p, .sidebar.collapsed .logo-text { display: none; }

        .sidebar .toggle-btn { position: absolute; right: 12px; top: 12px; z-index: 60; }
        .sidebar .toggle-btn button { background: var(--card-bg); border-radius: 9999px; padding: 8px; box-shadow: 0 4px 8px var(--card-shadow); border: 1px solid var(--border-color); display:flex; align-items:center; justify-content:center; width:36px; height:36px; }
        .sidebar .toggle-btn i { transition: transform 160ms ease; color: var(--green-500); }
        .sidebar.collapsed .toggle-btn i { transform: rotate(180deg); }
        .sidebar.collapsed .toggle-btn { right: -18px; }

        .nav-item i { display:inline-block; background: var(--card-bg); padding: 10px; border-radius: 12px; box-shadow: 0 6px 18px var(--green-shadow); border: 1px solid var(--border-color); }
        .nav-item.active i { background: var(--green-500); color: white !important; box-shadow: 0 10px 30px var(--green-shadow); border-color: var(--green-500); }
        .nav-item { color: var(--text-secondary) !important; font-size: 0.875rem; font-weight: 500; text-align: left; }
        .nav-item:hover { background: rgba(16,185,129,0.06); color: var(--text-primary) !important; transform: translateX(4px); }
        .nav-item.active { background: transparent; color: var(--green-500) !important; border-left: 4px solid var(--green-500); padding-left: 16px; }

        .profile-box { position: absolute; left: 12px; bottom: 25px; z-index: 50; }
        .profile-card { background: white; padding: 8px; border-radius: 16px; box-shadow: 0 4px 12px var(--card-shadow); border: 1px solid var(--border-color); width: 215px; }
        .sidebar.collapsed .profile-box { left: 8px; right: 8px; }
        .sidebar.collapsed .profile-card { background: transparent !important; box-shadow: none !important; border: none !important; padding: 0 !important; }
        .sidebar.collapsed .profile-info, .sidebar.collapsed #profileToggle { display: none; }
        .sidebar.collapsed .avatar-outer { padding: 0 !important; width: 44px !important; height: 44px !important; }

        .avatar-outer { background: var(--card-bg); padding: 6px; border-radius: 12px; box-shadow: 0 4px 12px var(--green-shadow); border: 1px solid var(--border-color); width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; }
        .profile-avatar { background: transparent; color: var(--green-500); width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 16px; }

        main { flex: 1; overflow-y: auto; height: 100vh; overflow-x: hidden; }
        
        ::-webkit-scrollbar { display: none; }
        * { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden" style="background: var(--bg-primary);">

    <aside id="sidebar" class="sidebar flex flex-col">
        <div class="p-6 flex items-center space-x-3 logo-container">
            <div class="bg-green-500 p-2 rounded-lg logo-icon">
                <i class="fas fa-shield-alt text-white text-xl"></i>
            </div>
            <span class="text-xl font-bold logo-text" style="color: var(--text-primary);"><?php echo $user_role; ?></span>
        </div>

        <div class="toggle-btn">
            <button onclick="toggleSidebar()"><i class="fas fa-chevron-left"></i></button>
        </div>

        <nav class="flex-1 px-4 space-y-1 mt-4 overflow-y-auto">
            <p class="text-xs font-bold text-gray-500 uppercase px-2 mb-2">Administración</p>
            <a href="?controlador=Administrador&accion=dashboard" class="nav-item w-full p-3 rounded-xl transition no-underline block <?php echo ($active_section === 'panel') ? 'active' : ''; ?>">
                <i class="fas fa-th-large"></i><span>Panel de Control</span>
            </a>


            <p class="text-xs font-bold text-gray-500 uppercase px-2 mb-2 mt-6">Centro de Formación</p>
            <a href="?controlador=Administrador&accion=centroFormacion" class="nav-item w-full p-3 rounded-xl transition no-underline block <?php echo ($active_section === 'centroFormacion') ? 'active' : ''; ?>">
                <i class="fas fa-university"></i><span>Gestión Académica</span>
            </a>

            <p class="text-xs font-bold text-gray-500 uppercase px-2 mb-2 mt-6">Talento Humano</p>

            <button onclick="window.location.href='?controlador=Administrador&accion=gestionInstructores'" class="nav-item w-full p-3 rounded-xl transition <?php echo ($active_section === 'instructores') ? 'active' : ''; ?>">
                <i class="fas fa-chalkboard-teacher"></i><span>Instructores</span>
            </button>

            <p class="text-xs font-bold text-gray-500 uppercase px-2 mb-2 mt-6">Coordinación</p>
            <button onclick="window.location.href='?controlador=Administrador&accion=gestionFichas'" class="nav-item w-full p-3 rounded-xl transition <?php echo ($active_section === 'fichas') ? 'active' : ''; ?>">
                <i class="fas fa-layer-group"></i><span>Gestionar Fichas</span>
            </button>
            <button onclick="window.location.href='?controlador=Administrador&accion=gestionAsignaciones'" class="nav-item w-full p-3 rounded-xl transition <?php echo ($active_section === 'asignaciones') ? 'active' : ''; ?>">
                <i class="fas fa-calendar-check"></i><span>Asignaciones</span>
            </button>
            
            <p class="text-xs font-bold text-gray-500 uppercase px-2 mb-2 mt-6">Infraestructura</p>
            <button onclick="window.location.href='?controlador=Administrador&accion=gestionSedes'" class="nav-item w-full p-3 rounded-xl transition <?php echo ($active_section === 'sedes') ? 'active' : ''; ?>">
                <i class="fas fa-map-marker-alt"></i><span>Sedes y Ambientes</span>
            </button>
        </nav>

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
                <button id="profileToggle" class="text-gray-400 hover:text-gray-600 ml-2"><i class="fas fa-ellipsis-h"></i></button>
            </div>
            
            <div id="profileMenu" class="hidden absolute left-4 bottom-20 w-48 bg-white rounded-xl shadow-lg z-50 overflow-hidden border">
                <a href="?controlador=<?php echo ucfirst($_SESSION['rol']); ?>&accion=perfil" class="block px-4 py-2 hover:bg-gray-50 text-gray-700 no-underline">
                    <i class="fas fa-user mr-2"></i>Ver Perfil
                </a>
                <a href="logout.php" class="block px-4 py-2 text-red-500 hover:bg-gray-50 no-underline">
                    <i class="fas fa-sign-out-alt mr-2"></i>Cerrar Sesión
                </a>
            </div>
        </div>
    </aside>

    <main class="p-4 md:p-8">
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <h1 id="section-title" class="text-2xl md:text-3xl font-bold text-green-500"><?php echo $titulo_section ?? 'Panel de Control'; ?></h1>
            <div class="flex items-center space-x-4 w-full md:w-auto justify-end">
                <button id="themeToggle" class="p-2 bg-white border rounded-xl text-gray-500 hover:text-green-500 shadow-sm">
                    <i class="fas fa-moon"></i>
                </button>
                <div class="relative">
                    <button id="notifBtn" class="p-2 bg-white border rounded-xl text-gray-500 hover:text-green-500 shadow-sm relative">
                        <i class="far fa-bell"></i>
                    </button>
                </div>
                <a href="?controlador=Administrador&accion=centroFormacion" class="bg-green-500 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-green-600 transition shadow-md no-underline">
                    <i class="fas fa-university mr-2"></i>Centro Formación
                </a>
            </div>
        </header>
