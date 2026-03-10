<?php
$titulo = 'Gestión de Asignaciones - SENA';
$titulo_section = 'Gestión de Asignaciones';
$active_section = 'asignaciones';

require_once('Vista/Layouts/admin_layout_header.php');

// Obtener asignaciones existentes
$db = Db::getConnect();
$stmt = $db->prepare("
    SELECT 
        a.asignacion_id,
        a.fecha_inicio,
        a.fecha_fin,
        a.hora_inicio,
        a.hora_fin,
        a.dias_semana,
        a.estado,
        f.codigo_ficha,
        f.programa,
        CONCAT(i.nombre, ' ', i.apellido) as instructor_nombre,
        amb.nombre_ambiente,
        exp.nombre_experiencia as competencia
    FROM asignaciones a
    INNER JOIN fichas f ON a.ficha_id = f.ficha_id
    INNER JOIN instructores i ON a.instructor_id = i.id
    LEFT JOIN ambientes amb ON a.ambiente_id = amb.ambiente_id
    INNER JOIN experiencias exp ON a.experiencia_id = exp.experiencia_id
    ORDER BY a.fecha_creacion DESC
");
$stmt->execute();
$asignaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="view-section active">
    <!-- Botón para crear nueva asignación -->
    <div class="mb-6">
        <button onclick="openModal('modalNuevaAsignacion')" class="bg-green-500 text-white px-6 py-3 rounded-xl font-bold hover:bg-green-600 transition shadow-md">
            <i class="fas fa-plus mr-2"></i>Nueva Asignación
        </button>
    </div>

    <!-- Tabla de asignaciones -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6">
            <h3 class="text-xl font-bold text-slate-800 mb-6">Asignaciones Registradas</h3>
            
            <?php if (empty($asignaciones)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-calendar-times text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">No hay asignaciones registradas</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ficha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Instructor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Competencia</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ambiente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha Inicio</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha Fin</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Horario</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($asignaciones as $asignacion): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($asignacion['codigo_ficha']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($asignacion['programa']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($asignacion['instructor_nombre']); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?php echo htmlspecialchars($asignacion['competencia']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($asignacion['nombre_ambiente'] ?? 'Sin asignar'); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo date('d/m/Y', strtotime($asignacion['fecha_inicio'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo date('d/m/Y', strtotime($asignacion['fecha_fin'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php 
                                        if ($asignacion['hora_inicio'] && $asignacion['hora_fin']) {
                                            echo date('H:i', strtotime($asignacion['hora_inicio'])) . ' - ' . date('H:i', strtotime($asignacion['hora_fin']));
                                        } else {
                                            echo 'No especificado';
                                        }
                                        ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php 
                                            echo match($asignacion['estado']) {
                                                'Programada' => 'bg-blue-100 text-blue-800',
                                                'En Curso' => 'bg-green-100 text-green-800',
                                                'Finalizada' => 'bg-gray-100 text-gray-800',
                                                'Cancelada' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                            ?>">
                                            <?php echo htmlspecialchars($asignacion['estado']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="?controlador=Administrador&accion=eliminarAsignacion&id=<?php echo $asignacion['asignacion_id']; ?>" 
                                           onclick="return confirm('¿Está seguro de eliminar esta asignación?')"
                                           class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Modal Nueva Asignación -->
<div id="modalNuevaAsignacion" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-8 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-bold text-slate-800">Nueva Asignación</h3>
            <button onclick="closeModal('modalNuevaAsignacion')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        
        <form method="POST" action="?controlador=Administrador&accion=crearAsignacion" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ficha *</label>
                    <select name="ficha_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">-- Seleccione una ficha --</option>
                        <?php foreach ($fichas as $ficha): ?>
                            <option value="<?php echo $ficha->getFichaId(); ?>">
                                <?php echo htmlspecialchars($ficha->getCodigoFicha() . ' - ' . $ficha->getPrograma()); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Instructor *</label>
                    <select name="instructor_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">-- Seleccione un instructor --</option>
                        <?php foreach ($instructores as $instructor): ?>
                            <option value="<?php echo $instructor->getId(); ?>">
                                <?php echo htmlspecialchars($instructor->getNombre() . ' ' . $instructor->getApellido()); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Competencia *</label>
                    <select name="experiencia_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">-- Seleccione una competencia --</option>
                        <?php foreach ($experiencias as $exp): ?>
                            <option value="<?php echo $exp->getId(); ?>">
                                <?php echo htmlspecialchars($exp->getNombre()); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ambiente *</label>
                    <select name="ambiente_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">-- Seleccione un ambiente --</option>
                        <?php foreach ($ambientes as $ambiente): ?>
                            <option value="<?php echo $ambiente->getAmbienteId(); ?>">
                                <?php echo htmlspecialchars($ambiente->getNombreAmbiente()); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Fin</label>
                    <input type="date" name="fecha_fin" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Hora Inicio</label>
                    <input type="time" name="hora_inicio" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Hora Fin</label>
                    <input type="time" name="hora_fin" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Días de la Semana</label>
                    <input type="text" name="dias_semana" placeholder="Ej: Lunes, Miércoles, Viernes" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                    <select name="estado" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="Programada">Programada</option>
                        <option value="En Curso">En Curso</option>
                        <option value="Finalizada">Finalizada</option>
                        <option value="Cancelada">Cancelada</option>
                    </select>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeModal('modalNuevaAsignacion')" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" class="px-6 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                    <i class="fas fa-save mr-2"></i>Guardar Asignación
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
    document.getElementById(modalId).classList.add('flex');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
    document.getElementById(modalId).classList.remove('flex');
}
</script>

<?php require_once('Vista/Layouts/admin_layout_footer.php'); ?>
