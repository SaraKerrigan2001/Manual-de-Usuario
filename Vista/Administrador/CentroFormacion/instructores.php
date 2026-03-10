<?php
// Vista/Administrador/CentroFormacion/instructores.php
$titulo = 'Gestión de Instructores - Centro de Formación';
$titulo_section = 'Cuerpo de Instructores';
$active_section = 'centroFormacion';

require_once('Vista/Layouts/admin_layout_header.php');
?>

<div class="animate__animated animate__fadeIn">
    <!-- Breadcrumbs -->
    <nav class="flex mb-6 text-gray-400 text-sm bg-white/50 p-3 rounded-xl border border-gray-100 w-fit" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-1 md:space-x-3">
            <li><a href="?controlador=Administrador&accion=centroFormacion" class="hover:text-green-500 transition-colors flex items-center gap-2"><i class="fas fa-university"></i> Centro de Formación</a></li>
            <li><div class="flex items-center"><i class="fas fa-chevron-right text-[10px] mx-1"></i> <span class="text-gray-600 font-bold">Instructores</span></div></li>
        </ol>
    </nav>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <p class="text-gray-500">Administra el personal docente, sus especialidades y perfiles de formación.</p>
        </div>
        <button onclick="openModal('modalInstructor')" class="bg-rose-500 text-white px-6 py-3 rounded-2xl shadow-lg shadow-rose-500/20 hover:bg-rose-600 transition-all flex items-center gap-2 font-bold transform hover:-translate-y-0.5">
            <i class="fas fa-user-plus"></i> Nuevo Instructor
        </button>
    </div>

    <!-- Tabla de Instructores Premium -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider">Instructor</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider">Información de Contacto</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider">Especialidad</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if (empty($instructores)): ?>
                        <tr>
                            <td colspan="4" class="px-8 py-16 text-center text-gray-400 italic">
                                <div class="flex flex-col items-center gap-3">
                                    <i class="fas fa-user-tie text-4xl text-gray-200"></i>
                                    <p>No hay instructores registrados actualmente</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($instructores as $ins): ?>
                        <tr class="hover:bg-rose-50/30 transition-all cursor-default">
                            <td class="px-8 py-5 whitespace-nowrap">
                                <div class="flex items-center gap-4">
                                    <div class="relative">
                                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-rose-400 to-rose-600 text-white flex items-center justify-center font-bold text-sm shadow-md shadow-rose-500/20">
                                            <?= strtoupper(substr($ins->getNombre(), 0, 1) . substr($ins->getApellido(), 0, 1)) ?>
                                        </div>
                                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 border-2 border-white rounded-full shadow-sm"></div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-800"><?= htmlspecialchars($ins->getNombre() . ' ' . $ins->getApellido()) ?></div>
                                        <div class="text-[10px] text-gray-400 uppercase tracking-widest font-bold mt-0.5 flex items-center gap-1">
                                            <i class="fas fa-id-card text-[8px]"></i>
                                            CC. <?= htmlspecialchars($ins->getDocumento() ?? 'N/A') ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex flex-col gap-1">
                                    <div class="text-sm font-semibold text-gray-600 flex items-center gap-2">
                                        <i class="fas fa-envelope text-gray-300 text-xs"></i>
                                        <?= htmlspecialchars($ins->getEmail()) ?>
                                    </div>
                                    <div class="text-[11px] text-gray-400 flex items-center gap-2">
                                        <i class="fas fa-clock text-gray-200 text-[10px]"></i>
                                        Socio-Educativo
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-rose-50 text-rose-600 border border-rose-100 shadow-sm">
                                    <?= htmlspecialchars($ins->getEspecialidad() ?? 'General') ?>
                                </span>
                            </td>
                            <td class="px-8 py-5 whitespace-nowrap text-sm font-medium">
                                <div class="flex gap-2">
                                    <button class="text-blue-500 hover:text-white p-2.5 rounded-xl hover:bg-blue-500 transition-all border border-blue-100/50 hover:border-blue-500 shadow-sm"><i class="fas fa-pen text-xs"></i></button>
                                    <button onclick="confirmDelete('?controlador=Administrador&accion=eliminarInstructor&id=<?= $ins->getId() ?>')" class="text-rose-500 hover:text-white p-2.5 rounded-xl hover:bg-rose-500 transition-all border border-rose-100/50 hover:border-rose-500 shadow-sm">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nuevo Instructor Premium -->
<div id="modalInstructor" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden z-[9999] items-center justify-center p-4">
    <div class="bg-white rounded-[2.5rem] w-full max-w-lg overflow-hidden shadow-2xl animate__animated animate__zoomIn animate__faster border border-gray-100">
        <div class="bg-gradient-to-r from-rose-500 to-rose-600 p-8 text-white flex justify-between items-center relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 blur-2xl"></div>
            <div class="relative">
                <h3 class="text-2xl font-bold flex items-center gap-3"><i class="fas fa-user-tie"></i> Nuevo Instructor</h3>
                <p class="text-rose-50/80 text-sm mt-1">Registra los datos personales del docente</p>
            </div>
            <button onclick="closeModal('modalInstructor')" class="text-white/80 hover:text-white transition-all bg-white/10 w-10 h-10 rounded-full flex items-center justify-center hover:rotate-90"><i class="fas fa-times"></i></button>
        </div>
        <form action="?controlador=Administrador&accion=addInstructor" method="POST" class="p-8 space-y-6">
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-6 rounded-3xl border border-gray-100">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 px-1">Nombres *</label>
                        <div class="relative">
                            <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-300"></i>
                            <input type="text" name="nombres" placeholder="Juan" class="w-full pl-12 pr-4 py-3.5 rounded-2xl border border-gray-200 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 transition-all outline-none font-bold text-gray-700 bg-white" required>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-6 rounded-3xl border border-gray-100">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 px-1">Apellidos *</label>
                        <div class="relative">
                            <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-300"></i>
                            <input type="text" name="apellidos" placeholder="Pérez" class="w-full pl-12 pr-4 py-3.5 rounded-2xl border border-gray-200 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 transition-all outline-none font-bold text-gray-700 bg-white" required>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 p-6 rounded-3xl space-y-5 border border-gray-100">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 px-1">Email SENA *</label>
                        <div class="relative">
                            <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-300"></i>
                            <input type="email" name="email" placeholder="example@sena.edu.co" class="w-full pl-12 pr-4 py-3.5 rounded-2xl border border-gray-200 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 transition-all outline-none font-bold text-gray-700 bg-white" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 px-1">Especialidad Principal</label>
                        <div class="relative">
                            <i class="fas fa-graduation-cap absolute left-4 top-1/2 -translate-y-1/2 text-gray-300"></i>
                            <input type="text" name="especialidad" placeholder="Ej: Desarrollo de Software" class="w-full pl-12 pr-4 py-3.5 rounded-2xl border border-gray-200 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 transition-all outline-none font-bold text-gray-700 bg-white">
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4 flex gap-4">
                <button type="button" onclick="closeModal('modalInstructor')" class="flex-1 px-8 py-4 rounded-2xl border border-gray-200 font-bold text-gray-500 hover:bg-gray-50 transition-all flex items-center justify-center gap-2">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 px-8 py-4 rounded-2xl bg-rose-500 text-white font-bold hover:bg-rose-600 transition-all shadow-xl shadow-rose-500/20 flex items-center justify-center gap-2 transform active:scale-95">
                    Registrar Instructor
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    const modal = document.getElementById(id);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModal(id) {
    const modal = document.getElementById(id);
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Cerrar al hacer clic fuera
window.onclick = function(event) {
    if (event.target.classList.contains('backdrop-blur-sm')) {
        event.target.classList.add('hidden');
        event.target.classList.remove('flex');
    }
}
</script>

<?php require_once('Vista/Layouts/admin_layout_footer.php'); ?>
