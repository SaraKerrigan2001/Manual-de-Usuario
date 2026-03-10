<?php
// dashboard.php - Refactored to use layout
$titulo = 'Dashboard Administrador - SENA';
$titulo_section = 'Panel de Control';
$active_section = 'panel';

require_once('Vista/Layouts/admin_layout_header.php');
?>

    <section id="panel" class="view-section active">
        <!-- Grid de Stats estilo Instructor -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                <div class="bg-green-500 w-10 h-10 rounded-xl flex items-center justify-center text-white mb-4">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <p class="text-3xl font-bold text-slate-800"><?php echo count($aprendices ?? []); ?></p>
                <p class="text-gray-500 text-sm">Aprendices Registrados</p>
            </div>
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                <div class="bg-green-500 w-10 h-10 rounded-xl flex items-center justify-center text-white mb-4">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <p class="text-3xl font-bold text-slate-800"><?php echo count($instructores ?? []); ?></p>
                <p class="text-gray-500 text-sm">Cuerpo de Instructores</p>
            </div>
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                <div class="bg-green-500 w-10 h-10 rounded-xl flex items-center justify-center text-white mb-4">
                    <i class="fas fa-folder"></i>
                </div>
                <p class="text-3xl font-bold text-slate-800"><?php echo count($fichas ?? []); ?></p>
                <p class="text-gray-500 text-sm">Fichas en Formación</p>
            </div>
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                <div class="bg-green-500 w-10 h-10 rounded-xl flex items-center justify-center text-white mb-4">
                    <i class="fas fa-building"></i>
                </div>
                <p class="text-3xl font-bold text-slate-800"><?php echo count($sedes ?? []); ?></p>
                <p class="text-gray-500 text-sm">Sedes Vinculadas</p>
            </div>
        </div>

        <!-- Tabla o Calendario -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden p-6">
            <h3 class="text-xl font-bold text-slate-800 mb-6 font-primary">Actividades Recientes del Sistema</h3>
            <div class="space-y-4">
                <div class="flex items-start gap-4 p-4 rounded-2xl hover:bg-gray-50 transition">
                    <div class="w-2 h-2 mt-2 rounded-full bg-green-500"></div>
                    <div>
                        <p class="text-sm font-bold text-slate-800">Actualización de Base de Datos</p>
                        <p class="text-xs text-gray-500">Esquema actualizado para soporte de asignaciones - Hace 5 min</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php require_once('Vista/Layouts/admin_layout_footer.php'); ?>
