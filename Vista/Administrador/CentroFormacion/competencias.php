<?php
// Vista/Administrador/CentroFormacion/competencias.php
$titulo = 'Gestión de Competencias - Centro de Formación';
$titulo_section = 'Competencias Académicas';
$active_section = 'centroFormacion';

require_once('Vista/Layouts/admin_layout_header.php');
?>

<div class="animate__animated animate__fadeIn">
    <!-- Breadcrumbs -->
    <nav class="flex mb-6 text-gray-400 text-sm bg-white/50 p-3 rounded-xl border border-gray-100 w-fit" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-1 md:space-x-3">
            <li><a href="?controlador=Administrador&accion=centroFormacion" class="hover:text-green-500 transition-colors flex items-center gap-2"><i class="fas fa-university"></i> Centro de Formación</a></li>
            <li><div class="flex items-center"><i class="fas fa-chevron-right text-[10px] mx-1"></i> <span class="text-gray-600 font-bold">Competencias</span></div></li>
        </ol>
    </nav>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <p class="text-gray-500">Administra las habilidades y conocimientos técnicos de los programas de formación.</p>
        </div>
        <button onclick="openModal('modalCompetencia')" class="bg-purple-600 text-white px-6 py-3 rounded-2xl shadow-lg shadow-purple-600/20 hover:bg-purple-700 transition-all flex items-center gap-2 font-bold transform hover:-translate-y-0.5">
            <i class="fas fa-plus"></i> Nueva Competencia
        </button>
    </div>

    <!-- Tabla de Competencias Premium -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider">Código</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider">Descripción</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider">Intensidad</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider">Tipo</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if (empty($competencias)): ?>
                        <tr>
                            <td colspan="5" class="px-8 py-16 text-center text-gray-400 italic">
                                <div class="flex flex-col items-center gap-3">
                                    <i class="fas fa-award text-4xl text-gray-200"></i>
                                    <p>No hay competencias registradas actualmente</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($competencias as $comp): ?>
                        <tr class="hover:bg-purple-50/30 transition-all cursor-default">
                            <td class="px-8 py-5 whitespace-nowrap"><span class="font-bold text-purple-600 bg-purple-50 px-3 py-1.5 rounded-lg border border-purple-100"><?= htmlspecialchars($comp->getCodigo()) ?></span></td>
                            <td class="px-8 py-5">
                                <div class="text-sm font-semibold text-gray-700 max-w-md line-clamp-2"><?= htmlspecialchars($comp->getDescripcion()) ?></div>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-2 text-sm font-bold text-gray-600">
                                    <i class="far fa-hourglass text-purple-300"></i>
                                    <?= htmlspecialchars($comp->getHoras() ?? '0') ?> h
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-purple-100 text-purple-600 border border-purple-200 shadow-sm">
                                    <?= htmlspecialchars($comp->getTipo() ?? 'Técnica') ?>
                                </span>
                            </td>
                            <td class="px-8 py-5 whitespace-nowrap text-sm font-medium">
                                <div class="flex gap-2">
                                    <button class="text-blue-500 hover:text-white p-2.5 rounded-xl hover:bg-blue-500 transition-all border border-blue-100/50 hover:border-blue-500 shadow-sm"><i class="fas fa-pen text-xs"></i></button>
                                    <button onclick="confirmDelete('?controlador=Administrador&accion=eliminarCompetencia&id=<?= $comp->getCodigo() ?>')" class="text-rose-500 hover:text-white p-2.5 rounded-xl hover:bg-rose-500 transition-all border border-rose-100/50 hover:border-rose-500 shadow-sm">
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

<!-- Modal Nueva Competencia Premium -->
<div id="modalCompetencia" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden z-[9999] items-center justify-center p-4">
    <div class="bg-white rounded-[2.5rem] w-full max-w-lg overflow-hidden shadow-2xl animate__animated animate__zoomIn animate__faster border border-gray-100">
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 p-8 text-white flex justify-between items-center relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 blur-2xl"></div>
            <div class="relative">
                <h3 class="text-2xl font-bold flex items-center gap-3"><i class="fas fa-award"></i> Nueva Competencia</h3>
                <p class="text-purple-50/80 text-sm mt-1">Registra una nueva habilidad para el programa</p>
            </div>
            <button onclick="closeModal('modalCompetencia')" class="text-white/80 hover:text-white transition-all bg-white/10 w-10 h-10 rounded-full flex items-center justify-center hover:rotate-90"><i class="fas fa-times"></i></button>
        </div>
        <form action="?controlador=Administrador&accion=addCompetencia" method="POST" class="p-8 space-y-6">
            <div class="space-y-4">
                <div class="bg-gray-50 p-6 rounded-3xl space-y-4 border border-gray-100">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 px-1">Código de Competencia *</label>
                        <div class="relative">
                            <i class="fas fa-hashtag absolute left-4 top-1/2 -translate-y-1/2 text-gray-300"></i>
                            <input type="text" name="codigo" placeholder="Ej: COMP-2024" class="w-full pl-12 pr-4 py-3.5 rounded-2xl border border-gray-200 focus:border-purple-600 focus:ring-4 focus:ring-purple-600/10 transition-all outline-none font-bold text-gray-700 bg-white" required>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 px-1">Descripción de la Competencia *</label>
                    <textarea name="descripcion" rows="4" placeholder="Describe los conocimientos y habilidades..." class="w-full p-5 rounded-3xl border border-gray-200 focus:border-purple-600 focus:ring-4 focus:ring-purple-600/10 transition-all outline-none font-semibold text-gray-700 bg-gray-50 shadow-inner" required></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-6 rounded-3xl border border-gray-100">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Horas Totales</label>
                        <div class="relative">
                            <i class="fas fa-hourglass-half absolute left-4 top-1/2 -translate-y-1/2 text-gray-300"></i>
                            <input type="number" name="horas" placeholder="48" class="w-full pl-12 pr-4 py-3.5 rounded-2xl border border-gray-200 focus:border-purple-600 bg-white outline-none font-bold text-gray-700" required>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-6 rounded-3xl border border-gray-100">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Tipo</label>
                        <div class="relative">
                            <select name="tipo" class="w-full px-4 py-3.5 rounded-2xl border border-gray-200 focus:border-purple-600 bg-white outline-none font-bold text-gray-700 appearance-none" required>
                                <option value="Técnica">Técnica</option>
                                <option value="Transversal">Transversal</option>
                                <option value="Básica">Básica</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4 flex gap-4">
                <button type="button" onclick="closeModal('modalCompetencia')" class="flex-1 px-8 py-4 rounded-2xl border border-gray-200 font-bold text-gray-500 hover:bg-gray-50 transition-all flex items-center justify-center gap-2">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 px-8 py-4 rounded-2xl bg-purple-600 text-white font-bold hover:bg-purple-700 transition-all shadow-xl shadow-purple-600/20 flex items-center justify-center gap-2 transform active:scale-95">
                    Registrar Competencia
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
