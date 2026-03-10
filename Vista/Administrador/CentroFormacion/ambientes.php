<?php
// Vista/Administrador/CentroFormacion/ambientes.php
$titulo = 'Gestión de Ambientes - Centro de Formación';
$titulo_section = 'Ambientes y Aulas';
$active_section = 'centroFormacion';

require_once('Vista/Layouts/admin_layout_header.php');
?>

<div class="animate__animated animate__fadeIn">
    <!-- Breadcrumbs -->
    <nav class="flex mb-6 text-gray-400 text-sm bg-white/50 p-3 rounded-xl border border-gray-100 w-fit" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-1 md:space-x-3">
            <li><a href="?controlador=Administrador&accion=centroFormacion" class="hover:text-green-500 transition-colors flex items-center gap-2"><i class="fas fa-university"></i> Centro de Formación</a></li>
            <li><div class="flex items-center"><i class="fas fa-chevron-right text-[10px] mx-1"></i> <span class="text-gray-600 font-bold">Ambientes</span></div></li>
        </ol>
    </nav>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <p class="text-gray-500">Administra los espacios físicos de aprendizaje y su capacidad técnica.</p>
        </div>
        <button onclick="openModal('modalAmbiente')" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl shadow-lg shadow-indigo-600/20 hover:bg-indigo-700 transition-all flex items-center gap-2 font-bold transform hover:-translate-y-0.5">
            <i class="fas fa-plus"></i> Nuevo Ambiente
        </button>
    </div>

    <!-- Tabla de Ambientes Premium -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider">Nombre Ambiente</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider">Tipo</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider">Capacidad</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider">Sede</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if (empty($ambientes)): ?>
                        <tr>
                            <td colspan="5" class="px-8 py-16 text-center text-gray-400 italic">
                                <div class="flex flex-col items-center gap-3">
                                    <i class="fas fa-door-open text-4xl text-gray-200"></i>
                                    <p>No hay ambientes registrados actualmente</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ambientes as $amb): ?>
                        <tr class="hover:bg-indigo-50/30 transition-all cursor-default">
                            <td class="px-8 py-5 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold shadow-sm">
                                        <i class="fas fa-door-closed text-xs"></i>
                                    </div>
                                    <span class="font-bold text-gray-800"><?= htmlspecialchars($amb->getNombreAmbiente()) ?></span>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-indigo-50 text-indigo-600 border border-indigo-100">
                                    <?= htmlspecialchars($amb->getTipo() ?? 'Aula') ?>
                                </span>
                            </td>
                            <td class="px-8 py-5">
                                <div class="text-sm font-bold text-gray-700 flex items-center gap-2">
                                    <i class="fas fa-users text-indigo-400 text-xs"></i>
                                    <?= htmlspecialchars($amb->getCapacidad()) ?> Pers.
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <div class="text-sm font-semibold text-gray-600 flex items-center gap-2">
                                    <i class="fas fa-building text-gray-300 text-xs"></i>
                                    <?= htmlspecialchars($amb->getSedeId()) ?>
                                </div>
                            </td>
                            <td class="px-8 py-5 whitespace-nowrap text-sm font-medium">
                                <div class="flex gap-2">
                                    <button class="text-blue-500 hover:text-white p-2.5 rounded-xl hover:bg-blue-500 transition-all border border-blue-100/50 hover:border-blue-500 shadow-sm"><i class="fas fa-pen text-xs"></i></button>
                                    <button onclick="confirmDelete('?controlador=Administrador&accion=eliminarAmbiente&id=<?= $amb->getAmbienteId() ?>')" class="text-rose-500 hover:text-white p-2.5 rounded-xl hover:bg-rose-500 transition-all border border-rose-100/50 hover:border-rose-500 shadow-sm">
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

<!-- Modal Nuevo Ambiente Premium -->
<div id="modalAmbiente" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden z-[9999] items-center justify-center p-4">
    <div class="bg-white rounded-[2.5rem] w-full max-w-lg overflow-hidden shadow-2xl animate__animated animate__zoomIn animate__faster border border-gray-100">
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 p-8 text-white flex justify-between items-center relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 blur-2xl"></div>
            <div class="relative">
                <h3 class="text-2xl font-bold flex items-center gap-3"><i class="fas fa-door-open"></i> Nuevo Ambiente</h3>
                <p class="text-indigo-50/80 text-sm mt-1">Registra un nuevo espacio de aprendizaje</p>
            </div>
            <button onclick="closeModal('modalAmbiente')" class="text-white/80 hover:text-white transition-all bg-white/10 w-10 h-10 rounded-full flex items-center justify-center hover:rotate-90"><i class="fas fa-times"></i></button>
        </div>
        <form action="?controlador=Administrador&accion=addAmbiente" method="POST" class="p-8 space-y-6">
            <div class="space-y-4">
                <div class="bg-gray-50 p-6 rounded-3xl space-y-5 border border-gray-100">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 px-1">Nombre del Ambiente *</label>
                        <div class="relative">
                            <i class="fas fa-door-closed absolute left-4 top-1/2 -translate-y-1/2 text-gray-300"></i>
                            <input type="text" name="nombre" placeholder="Ej: Laboratorio 1" class="w-full pl-12 pr-4 py-3.5 rounded-2xl border border-gray-200 focus:border-indigo-600 focus:ring-4 focus:ring-indigo-600/10 transition-all outline-none font-bold text-gray-700 bg-white" required>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-6 rounded-3xl border border-gray-100">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 px-1">Capacidad *</label>
                        <div class="relative">
                            <i class="fas fa-users absolute left-4 top-1/2 -translate-y-1/2 text-gray-300"></i>
                            <input type="number" name="capacidad" placeholder="30" class="w-full pl-12 pr-4 py-3.5 rounded-2xl border border-gray-200 focus:border-indigo-600 focus:ring-4 focus:ring-indigo-600/10 transition-all outline-none font-bold text-gray-700 bg-white" required>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-6 rounded-3xl border border-gray-100">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 px-1">Tipo *</label>
                        <div class="relative">
                            <select name="tipo" class="w-full px-4 py-3.5 rounded-2xl border border-gray-200 focus:border-indigo-600 bg-white outline-none font-bold text-gray-700 appearance-none" required>
                                <option value="Aula">Aula</option>
                                <option value="Laboratorio">Laboratorio</option>
                                <option value="Taller">Taller</option>
                                <option value="Virtual">Virtual</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 p-6 rounded-3xl border border-gray-100">
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 px-1">Sede *</label>
                    <div class="relative">
                        <i class="fas fa-building absolute left-4 top-1/2 -translate-y-1/2 text-gray-300"></i>
                        <select name="sede" class="w-full pl-12 pr-4 py-3.5 rounded-2xl border border-gray-200 focus:border-indigo-600 bg-white outline-none font-bold text-gray-700 appearance-none" required>
                            <option value="">-- Seleccione Sede --</option>
                            <?php foreach ($sedes as $s): ?>
                                <option value="<?= $s->getSedeId() ?>"><?= htmlspecialchars($s->getNombreSede()) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none"></i>
                    </div>
                </div>
            </div>

            <div class="pt-4 flex gap-4">
                <button type="button" onclick="closeModal('modalAmbiente')" class="flex-1 px-8 py-4 rounded-2xl border border-gray-200 font-bold text-gray-500 hover:bg-gray-50 transition-all flex items-center justify-center gap-2">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 px-8 py-4 rounded-2xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-600/20 flex items-center justify-center gap-2 transform active:scale-95">
                    Registrar Ambiente
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

window.onclick = function(event) {
    if (event.target.classList.contains('backdrop-blur-sm')) {
        event.target.classList.add('hidden');
        event.target.classList.remove('flex');
    }
}
</script>

<?php require_once('Vista/Layouts/admin_layout_footer.php'); ?>
