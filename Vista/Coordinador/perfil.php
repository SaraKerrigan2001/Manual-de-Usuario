<?php
// Verificar autenticación
if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] !== 'coordinador' && $_SESSION['rol'] !== 'administrador')) {
    header('Location: index.php?controlador=Auth&accion=login');
    exit;
}

// Valores del usuario
$user_name = $_SESSION['user_name'] ?? $_SESSION['nombre'] ?? 'Coordinador';
$user_email = $_SESSION['email'] ?? 'coordinador@sena.edu.co';
$user_role_db = $_SESSION['role'] ?? $_SESSION['rol'] ?? 'administrador';

// Convertir rol de base de datos a nombre amigable
$user_role = ($user_role_db === 'administrador') ? 'Coordinador' : ucfirst($user_role_db);

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
    <title>Mi Perfil - SENA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen p-4 md:p-8">
    <div class="max-w-4xl mx-auto">
        <header class="mb-8 flex items-center gap-4">
            <button onclick="window.history.back()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-100 text-slate-600 hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-arrow-left"></i>
            </button>
            <div>
                <h1 class="text-3xl font-bold text-slate-800">Mi Perfil</h1>
                <p class="text-gray-500">Gestiona tu información y preferencias</p>
            </div>
        </header>

        <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-8 mb-10">
                <!-- Avatar -->
                <div class="w-32 h-32 bg-[#39A900] rounded-3xl flex items-center justify-center text-white text-4xl font-bold shadow-lg shadow-green-100">
                    <?php echo $initials; ?>
                </div>

                <!-- Basic Info -->
                <div class="flex-1 text-center md:text-left pt-2">
                    <h2 class="text-3xl font-bold text-slate-900 mb-0"><?php echo htmlspecialchars($user_name); ?></h2>
                    <p class="text-[#39A900] font-bold text-xl mb-2"><?php echo $user_role; ?></p>
                    <p class="text-gray-500 text-lg"><?php echo htmlspecialchars($user_email); ?></p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-10 gap-x-12 px-2">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nombre Completo</p>
                    <p class="text-lg font-semibold text-slate-800"><?php echo htmlspecialchars($user_name); ?></p>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Correo Electrónico</p>
                    <p class="text-lg font-semibold text-slate-800"><?php echo htmlspecialchars($user_email); ?></p>
                </div>
            </div>

            <div class="mt-12 flex justify-start">
                <button class="px-8 py-3 rounded-2xl bg-[#39A900] text-white font-bold hover:bg-[#2d8000] transition shadow-lg shadow-green-100">
                    Editar Perfil
                </button>
            </div>
        </div>
    </div>
</body>
</html>
