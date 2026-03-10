<?php
// Vista/Administrador/CentroFormacion/programas.php
$titulo = 'Gestión de Programas - Centro de Formación';
$titulo_section = 'Programas de Formación';
$active_section = 'centroFormacion';

require_once('Vista/Layouts/admin_layout_header.php');
?>

<div class="animate__animated animate__fadeIn">
    <!-- Breadcrumbs -->
    <nav class="flex mb-6 text-gray-400 text-sm bg-white/50 p-3 rounded-xl border border-gray-100 w-fit" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-1 md:space-x-3">
            <li><a href="?controlador=Administrador&accion=centroFormacion" class="hover:text-green-500 transition-colors flex items-center gap-2"><i class="fas fa-university"></i> Centro de Formación</a></li>
            <li><div class="flex items-center"><i class="fas fa-chevron-right text-[10px] mx-1"></i> <span class="text-gray-600 font-bold">Programas</span></div></li>
        </ol>
    </nav>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <p class="text-gray-500">Administra la oferta académica institucional y sus detalles técnicos.</p>
        </div>
        <button onclick="openModal('modalPrograma')" class="bg-orange-500 text-white px-6 py-3 rounded-2xl shadow-lg shadow-orange-500/20 hover:bg-orange-600 transition-all flex items-center gap-2 font-bold transform hover:-translate-y-0.5">
            <i class="fas fa-plus"></i> Nuevo Programa
        </button>
    </div>

    <!-- Tabla de Programas Premium -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider">Código</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider">Nombre del Programa</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider">Nivel</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider">Duración</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if (empty($programas)): ?>
                        <tr>
                            <td colspan="5" class="px-8 py-16 text-center text-gray-400 italic">
                                <div class="flex flex-col items-center gap-3">
                                    <i class="fas fa-book-open text-4xl text-gray-200"></i>
                                    <p>No hay programas registrados actualmente</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($programas as $prog): ?>
                        <tr class="hover:bg-orange-50/30 transition-all cursor-default">
                            <td class="px-8 py-5 whitespace-nowrap"><span class="font-bold text-orange-600 bg-orange-50 px-3 py-1.5 rounded-lg border border-orange-100"><?= htmlspecialchars($prog->getCodigo()) ?></span></td>
                            <td class="px-8 py-5">
                                <div class="text-sm font-bold text-gray-800"><?= htmlspecialchars($prog->getNombre()) ?></div>
                            </td>
                            <td class="px-8 py-5">
                                <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-blue-100 text-blue-600 border border-blue-200 shadow-sm">
                                    <?= htmlspecialchars($prog->getNivel()) ?>
                                </span>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-2 text-sm font-bold text-gray-600">
                                    <i class="far fa-clock text-gray-300"></i>
                                    <?= htmlspecialchars($prog->getDuracion() ?? '0') ?> meses
                                </div>
                            </td>
                            <td class="px-8 py-5 whitespace-nowrap text-sm font-medium">
                                <div class="flex gap-2">
                                    <button class="text-blue-500 hover:text-white p-2.5 rounded-xl hover:bg-blue-500 transition-all border border-blue-100/50 hover:border-blue-500 shadow-sm"><i class="fas fa-pen text-xs"></i></button>
                                    <button onclick="confirmDelete('?controlador=Administrador&accion=eliminarPrograma&id=<?= $prog->getCodigo() ?>')" class="text-rose-500 hover:text-white p-2.5 rounded-xl hover:bg-rose-500 transition-all border border-rose-100/50 hover:border-rose-500 shadow-sm">
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

<!-- Modal Nuevo Programa Premium -->
<div id="modalPrograma" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden z-[9999] items-center justify-center p-4">
    <div class="bg-white rounded-[2.5rem] w-full max-w-lg overflow-hidden shadow-2xl animate__animated animate__zoomIn animate__faster border border-gray-100">
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 p-8 text-white flex justify-between items-center relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 blur-2xl"></div>
            <div class="relative">
                <h3 class="text-2xl font-bold flex items-center gap-3"><i class="fas fa-book-open"></i> Nuevo Programa</h3>
                <p class="text-orange-50/80 text-sm mt-1">Define la estructura del nuevo programa académico</p>
            </div>
            <button onclick="closeModal('modalPrograma')" class="text-white/80 hover:text-white transition-all bg-white/10 w-10 h-10 rounded-full flex items-center justify-center hover:rotate-90"><i class="fas fa-times"></i></button>
        </div>
        <form action="?controlador=Administrador&accion=addPrograma" method="POST" class="p-8 space-y-6">
            <div class="space-y-4">
                <div class="bg-gray-50 p-6 rounded-3xl space-y-4 border border-gray-100">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 px-1">Código del Programa *</label>
                        <div class="relative">
                            <i class="fas fa-barcode absolute left-4 top-1/2 -translate-y-1/2 text-gray-300"></i>
                            <input type="text" name="codigo" placeholder="Ej: PROG-001" class="w-full pl-12 pr-4 py-3.5 rounded-2xl border border-gray-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 transition-all outline-none font-bold text-gray-700 bg-white" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 px-1">Nombre Completo *</label>
                        <div class="relative">
                            <i class="fas fa-heading absolute left-4 top-1/2 -translate-y-1/2 text-gray-300"></i>
                            <input type="text" name="nombre" placeholder="Ej: Análisis y Desarrollo de Software" class="w-full pl-12 pr-4 py-3.5 rounded-2xl border border-gray-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 transition-all outline-none font-bold text-gray-700 bg-white" required>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-6 rounded-3xl border border-gray-100">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Nivel Académico</label>
                        <div class="relative">
                            <select name="nivel" class="w-full px-4 py-3.5 rounded-2xl border border-gray-200 focus:border-orange-500 bg-white outline-none font-bold text-gray-700 appearance-none" required>
                                <option value="">-- Nivel --</option>
                                <option value="Técnico">Técnico</option>
                                <option value="Tecnólogo">Tecnólogo</option>
                                <option value="Especialización">Especialización</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none"></i>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-6 rounded-3xl border border-gray-100">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Duración (Meses)</label>
                        <div class="relative">
                            <i class="fas fa-calendar-alt absolute left-4 top-1/2 -translate-y-1/2 text-gray-300"></i>
                            <input type="number" name="duracion" placeholder="24" class="w-full pl-12 pr-4 py-3.5 rounded-2xl border border-gray-200 focus:border-orange-500 bg-white outline-none font-bold text-gray-700" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4 flex gap-4">
                <button type="button" onclick="closeModal('modalPrograma')" class="flex-1 px-8 py-4 rounded-2xl border border-gray-200 font-bold text-gray-500 hover:bg-gray-50 transition-all flex items-center justify-center gap-2">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 px-8 py-4 rounded-2xl bg-orange-500 text-white font-bold hover:bg-orange-600 transition-all shadow-xl shadow-orange-500/20 flex items-center justify-center gap-2 transform active:scale-95">
                    Registrar Programa
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
