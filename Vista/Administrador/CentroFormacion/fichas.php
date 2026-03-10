<?php
// Vista/Administrador/CentroFormacion/fichas.php
$titulo = 'Gestión de Fichas - Centro de Formación';
$titulo_section = 'Gestión de Fichas';
$active_section = 'centroFormacion';

require_once('Vista/Layouts/admin_layout_header.php');
?>

<div class="animate__animated animate__fadeIn">
    <!-- Breadcrumbs -->
    <nav class="flex mb-6 text-gray-400 text-sm bg-white/50 p-3 rounded-xl border border-gray-100 w-fit" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-1 md:space-x-3">
            <li><a href="?controlador=Administrador&accion=centroFormacion" class="hover:text-green-500 transition-colors flex items-center gap-2"><i class="fas fa-university"></i> Centro de Formación</a></li>
            <li><div class="flex items-center"><i class="fas fa-chevron-right text-[10px] mx-1"></i> <span class="text-gray-600 font-bold">Fichas</span></div></li>
        </ol>
    </nav>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <p class="text-gray-500">Administra los grupos de formación y sus asignaciones actuales.</p>
        </div>
        <button onclick="openModal('modalFicha')" class="bg-green-500 text-white px-6 py-3 rounded-2xl shadow-lg shadow-green-500/20 hover:bg-green-600 transition-all flex items-center gap-2 font-bold transform hover:-translate-y-0.5">
            <i class="fas fa-plus"></i> Nueva Ficha
        </button>
    </div>

    <!-- Tabla de Fichas Premium -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider">Código Ficha</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider">Programa</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider">Estado</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider">Sede</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if (empty($fichas)): ?>
                        <tr>
                            <td colspan="6" class="px-8 py-16 text-center text-gray-400 italic">
                                <div class="flex flex-col items-center gap-3">
                                    <i class="fas fa-folder-open text-4xl text-gray-200"></i>
                                    <p>No hay fichas registradas actualmente</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($fichas as $ficha): ?>
                        <tr class="hover:bg-green-50/30 transition-all cursor-default">
                            <td class="px-8 py-5 whitespace-nowrap"><span class="font-bold text-green-600 bg-green-50 px-3 py-1.5 rounded-lg border border-green-100"><?= htmlspecialchars($ficha->getCodigoFicha()) ?></span></td>
                            <td class="px-8 py-5">
                                <div class="text-sm font-bold text-gray-800"><?= htmlspecialchars($ficha->getPrograma()) ?></div>
                            </td>
                            <td class="px-8 py-5">
                                <span class="px-3 py-1 rounded-full text-xs font-extrabold <?= $ficha->getEstado() === 'Activa' ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600' ?>">
                                    <?= htmlspecialchars($ficha->getEstado()) ?>
                                </span>
                            </td>
                            <td class="px-8 py-5 text-sm uppercase font-bold text-gray-600">
                                <i class="fas fa-building text-gray-300 mr-2"></i>
                                <?= htmlspecialchars($ficha->getSedeId() ?? 'Sin asignar') ?>
                            </td>
                            <td class="px-8 py-5 whitespace-nowrap text-sm font-medium">
                                <div class="flex gap-2">
                                    <button class="text-blue-500 hover:text-white p-2.5 rounded-xl hover:bg-blue-500 transition-all border border-blue-100/50 hover:border-blue-500 shadow-sm"><i class="fas fa-pen text-xs"></i></button>
                                    <button onclick="confirmDelete('?controlador=Administrador&accion=eliminarFicha&id=<?= $ficha->getFichaId() ?>')" class="text-rose-500 hover:text-white p-2.5 rounded-xl hover:bg-rose-500 transition-all border border-rose-100/50 hover:border-rose-500 shadow-sm">
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

<!-- Modal Nueva Ficha Premium -->
<div id="modalFicha" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden z-[9999] items-center justify-center p-4">
    <div class="bg-white rounded-[2.5rem] w-full max-w-lg overflow-hidden shadow-2xl animate__animated animate__zoomIn animate__faster border border-gray-100">
        <div class="bg-gradient-to-r from-green-500 to-green-600 p-8 text-white flex justify-between items-center relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 blur-2xl"></div>
            <div class="relative">
                <h3 class="text-2xl font-bold flex items-center gap-3"><i class="fas fa-layer-group"></i> Nueva Ficha</h3>
                <p class="text-green-50/80 text-sm mt-1">Completa los datos para el nuevo grupo</p>
            </div>
            <button onclick="closeModal('modalFicha')" class="text-white/80 hover:text-white transition-all bg-white/10 w-10 h-10 rounded-full flex items-center justify-center hover:rotate-90"><i class="fas fa-times"></i></button>
        </div>
        <form action="?controlador=Administrador&accion=addFicha" method="POST" class="p-8 space-y-5">
            <div class="space-y-4">
                <div class="bg-gray-50 p-6 rounded-3xl space-y-4 border border-gray-100">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 px-1">Código de Ficha *</label>
                        <div class="relative">
                            <i class="fas fa-hashtag absolute left-4 top-1/2 -translate-y-1/2 text-gray-300"></i>
                            <input type="text" name="codigo" placeholder="Ej: 2558888" class="w-full pl-12 pr-4 py-3.5 rounded-2xl border border-gray-200 focus:border-green-500 focus:ring-4 focus:ring-green-500/10 transition-all outline-none font-bold text-gray-700 bg-white" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 px-1">Programa Académico *</label>
                        <div class="relative">
                            <i class="fas fa-book-open absolute left-4 top-1/2 -translate-y-1/2 text-gray-300"></i>
                            <input type="text" name="programa" placeholder="Ej: ADSO" class="w-full pl-12 pr-4 py-3.5 rounded-2xl border border-gray-200 focus:border-green-500 focus:ring-4 focus:ring-green-500/10 transition-all outline-none font-bold text-gray-700 bg-white" required>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-4 rounded-3xl border border-gray-100">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Fecha Inicio</label>
                        <input type="date" name="inicio" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-green-500 bg-white outline-none font-semibold text-gray-700" required>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-3xl border border-gray-100">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Fecha Fin</label>
                        <input type="date" name="fin" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-green-500 bg-white outline-none font-semibold text-gray-700" required>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="bg-gray-50 p-6 rounded-3xl border border-gray-100">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 px-1">Sede Responsable</label>
                        <div class="relative">
                            <i class="fas fa-building absolute left-4 top-1/2 -translate-y-1/2 text-gray-300"></i>
                            <select name="sede_id" class="w-full pl-12 pr-4 py-3.5 rounded-2xl border border-gray-200 focus:border-green-500 bg-white outline-none font-bold text-gray-700">
                                <option value="">-- Seleccione Sede --</option>
                                <?php foreach ($sedes as $sede): ?>
                                    <option value="<?= $sede['sede_id'] ?? $sede->getSedeId() ?>"><?= htmlspecialchars($sede['nombre_sede'] ?? $sede->getNombreSede()) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-6 flex gap-4">
                <button type="button" onclick="closeModal('modalFicha')" class="flex-1 px-8 py-4 rounded-2xl border border-gray-200 font-bold text-gray-500 hover:bg-gray-50 transition-all flex items-center justify-center gap-2">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 px-8 py-4 rounded-2xl bg-green-500 text-white font-bold hover:bg-green-600 transition-all shadow-xl shadow-green-500/20 flex items-center justify-center gap-2 transform active:scale-95">
                    Registrar Ficha
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
