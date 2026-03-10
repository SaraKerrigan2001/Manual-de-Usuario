<?php

$asignaciones = $asignaciones ?? [];

$totalAsignaciones = count($asignaciones);
$asignacionesActivas = 0; // Contar las activas si es necesario
foreach ($asignaciones as $asignacion) {
    if ($asignacion['estado'] === 'Activa') {
        $asignacionesActivas++;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Asignaciones - SENA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --sena-verde: #39A900;
            --sena-verde-hover: #2d8400;
        }
        .btn-ver { background-color: var(--sena-verde); }
        .btn-ver:hover { background-color: var(--sena-verde-hover); }
        .btn-editar { background-color: var(--sena-verde); }
        .btn-editar:hover { background-color: var(--sena-verde-hover); }
        .btn-eliminar { background-color: #dc2626; }
        .btn-eliminar:hover { background-color: #b91c1c; }
        .badge-pendiente { background-color: #fbbf24; color: #78350f; }
        .modal { display: none; position: fixed; z-index: 50; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); }
        .modal.active { display: flex; align-items: center; justify-content: center; }
        .modal-content { background-color: white; border-radius: 20px; padding: 30px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen p-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">Gestión de Asignaciones</h1>
                <p class="text-gray-500 mt-1">Gestiona las asignaciones de instructores y ambientes</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="bg-white rounded-full p-2 shadow-sm">
                        <div class="w-10 h-10 bg-[#39A900] rounded-full flex items-center justify-center text-white font-bold">
                            AS
                        </div>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-800">Administrador SENA</p>
                    <p class="text-xs text-gray-500">Administrador</p>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm mb-1">Total Asignaciones</p>
                        <p id="total-asignaciones" class="text-4xl font-bold text-pink-500"><?php echo $totalAsignaciones; ?></p>
                    </div>
                    <div class="bg-pink-100 w-14 h-14 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-clipboard-list text-pink-500 text-2xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm mb-1">Asignaciones Activas</p>
                        <p id="total-asignaciones-activas" class="text-4xl font-bold text-teal-500"><?php echo $asignacionesActivas; ?></p>
                    </div>
                    <div class="bg-teal-100 w-14 h-14 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-check-circle text-teal-500 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-4 flex justify-between items-center">
                <div class="flex gap-4">
                    <button class="px-4 py-2 text-sm font-semibold text-[#39A900] border-b-2 border-[#39A900]">
                        <i class="fas fa-list mr-2"></i>Lista
                    </button>
                    <button class="px-4 py-2 text-sm font-semibold text-gray-500 hover:text-[#39A900]">
                        <i class="fas fa-calendar mr-2"></i>Calendario
                    </button>
                </div>
                <a href="?controlador=Administrador&accion=nuevaAsignacion" class="bg-[#39A900] text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-green-700 transition">
                    <i class="fas fa-plus mr-2"></i>Nueva Asignación
                </a>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">FICHA</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">INSTRUCTOR</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">AMBIENTE</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">COMPETENCIA</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">FECHA INICIO</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">ESTADO</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="asignaciones-body" class="bg-white divide-y divide-gray-100">
                        <!-- filas generadas por JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Ver Asignación -->
    <div id="modal-ver" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-slate-800">Detalles de Asignación</h2>
                <button onclick="cerrarModal('ver')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="contenido-ver" class="space-y-4">
                <!-- Contenido dinámico -->
            </div>
            <div class="flex justify-end mt-6">
                <button onclick="cerrarModal('ver')" class="px-6 py-2 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Editar Asignación -->
    <div id="modal-editar" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-slate-800">Editar Asignación</h2>
                <button onclick="cerrarModal('editar')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="form-editar" class="space-y-4">
                <input type="hidden" id="edit-id" name="id">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Ficha</label>
                    <input type="text" id="edit-ficha" name="ficha" class="w-full px-4 py-2 border border-gray-200 rounded-xl bg-gray-100" readonly>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Instructor</label>
                    <input type="text" id="edit-instructor" name="instructor" class="w-full px-4 py-2 border border-gray-200 rounded-xl bg-gray-100" readonly>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Ambiente</label>
                    <input type="text" id="edit-ambiente" name="ambiente" class="w-full px-4 py-2 border border-gray-200 rounded-xl bg-gray-100" readonly>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Competencia</label>
                    <input type="text" id="edit-competencia" name="competencia" class="w-full px-4 py-2 border border-gray-200 rounded-xl bg-gray-100" readonly>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha Inicio</label>
                    <input type="text" id="edit-fecha" name="fecha_inicio" class="w-full px-4 py-2 border border-gray-200 rounded-xl bg-gray-100" readonly>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Estado</label>
                    <select id="edit-estado" name="estado" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#39A900] outline-none" required>
                        <option value="Pendiente">Pendiente</option>
                        <option value="Activa">Activa</option>
                        <option value="Completada">Completada</option>
                        <option value="Cancelada">Cancelada</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="cerrarModal('editar')" class="px-6 py-2 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit" class="px-6 py-2 bg-[#39A900] text-white rounded-xl hover:bg-green-700">
                        Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>

        <!-- Modal Editar Asignación -->
        <div id="modal-editar" class="modal">
        <div class="modal-content">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-slate-800">Editar Asignación</h2>
            <button onclick="cerrarModal('editar')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- FORM ORIGINAL (NO ELIMINADO) -->
        <form id="form-editar" class="space-y-4">
            <input type="hidden" id="edit-id" name="id">

            <!-- 🔹 NUEVO CONTENEDOR VISUAL IGUAL AL MODAL VER -->
            <div class="bg-gray-50 p-6 rounded-2xl">
                <div class="grid grid-cols-2 gap-6">

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Ficha</label>
                        <input type="text" id="edit-ficha" name="ficha"
                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm"
                            readonly>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Instructor</label>
                        <input type="text" id="edit-instructor" name="instructor"
                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm"
                            readonly>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Ambiente</label>
                        <input type="text" id="edit-ambiente" name="ambiente"
                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm"
                            readonly>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Competencia</label>
                        <input type="text" id="edit-competencia" name="competencia"
                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm"
                            readonly>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Fecha Inicio</label>
                        <input type="text" id="edit-fecha" name="fecha_inicio"
                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm"
                            readonly>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Estado</label>
                        <select id="edit-estado" name="estado"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#39A900] outline-none text-sm"
                            required>
                            <option value="Pendiente">Pendiente</option>
                            <option value="Activa">Activa</option>
                            <option value="Completada">Completada</option>
                            <option value="Cancelada">Cancelada</option>
                        </select>
                    </div>

                </div>
            </div>
            <!-- 🔹 FIN CONTENEDOR -->

            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="cerrarModal('editar')"
                    class="px-6 py-2 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                    Cancelar
                </button>

                <button type="submit"
                    class="px-6 py-2 bg-[#39A900] text-white rounded-xl hover:bg-green-700">
                    Actualizar
                </button>
            </div>
        </form>
    </div>
</div>


    <!-- Modal Eliminar Asignación -->
    <div id="modal-eliminar" class="modal">
        <div class="modal-content max-w-md">
            <div class="text-center mb-6">
                <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-slate-800 mb-2">¿Eliminar Asignación?</h2>
                <p class="text-gray-600">Esta acción no se puede deshacer. ¿Estás seguro de que deseas eliminar esta asignación?</p>
            </div>
            <div class="flex justify-center gap-3">
                <button onclick="cerrarModal('eliminar')" class="px-6 py-2 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                    Cancelar
                </button>
                <button onclick="confirmarEliminar()" class="px-6 py-2 bg-red-500 text-white rounded-xl hover:bg-red-600">
                    Eliminar
                </button>
            </div>
        </div>
    </div>

    <script>
        // Variable global de asignaciones cargadas desde el servidor
        let asignaciones = [];
        let asignacionActual = null;

        // muestra notificaciones breves en la esquina superior derecha
        function showNotification(message, type = 'success') {
            // simple implementación: un div flotante
            const existing = document.getElementById('notification-container');
            if (existing) existing.remove();
            const container = document.createElement('div');
            container.id = 'notification-container';
            container.style.position = 'fixed';
            container.style.top = '20px';
            container.style.right = '20px';
            container.style.zIndex = 1000;
            container.innerHTML = `<div style="padding:12px 20px; border-radius:8px; color:#fff; background:${type==='error'?'#e53e3e':'#38a169'}; box-shadow:0 2px 8px rgba(0,0,0,.15);">${message}</div>`;
            document.body.appendChild(container);
            setTimeout(() => { container.remove(); }, 3000);
        }

        // Carga la lista de asignaciones desde la API y actualiza la tabla
        async function cargarAsignaciones() {
            try {
                const resp = await fetch('api/asignaciones.php?action=listar', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await resp.json();
                if (data.success && Array.isArray(data.asignaciones)) {
                    asignaciones = data.asignaciones.map(a => ({
                        // adaptamos nombres para la vista simplificada
                        id: a.asignacion_id,
                        ficha: a.codigo_ficha || '',
                        instructor: a.instructor_nombre || '',
                        ambiente: a.nombre_ambiente || '',
                        competencia: a.competencia || '',
                        fecha_inicio: a.fecha_inicio || '',
                        estado: a.estado || ''
                    }));
                    renderizarTabla();

                    // actualizar indicadores
                    const totalSpan = document.getElementById('total-asignaciones');
                    const activasSpan = document.getElementById('total-asignaciones-activas');
                    if (totalSpan) totalSpan.textContent = asignaciones.length;
                    if (activasSpan) {
                        const activas = asignaciones.filter(a => a.estado && a.estado.toLowerCase() === 'activa').length;
                        activasSpan.textContent = activas;
                    }
                } else {
                    console.error('Respuesta inválida de la API', data);
                }
            } catch (err) {
                console.error('Error cargando asignaciones', err);
            }
        }

        // Genera las filas de la tabla a partir del arreglo "asignaciones"
        function renderizarTabla() {
            const tbody = document.getElementById('asignaciones-body');
            if (!tbody) return;
            tbody.innerHTML = asignaciones.map(asignacion => {
                // asignación de clases según estado
                let badgeClass = 'badge-pendiente';
                if (asignacion.estado.toLowerCase() === 'activa') badgeClass = 'bg-green-100 text-green-800';
                if (asignacion.estado.toLowerCase() === 'completada' || asignacion.estado.toLowerCase() === 'finalizada') badgeClass = 'bg-green-100 text-green-800';
                if (asignacion.estado.toLowerCase() === 'cancelada') badgeClass = 'bg-red-100 text-red-800';

                return `
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <span class="text-sm font-semibold text-pink-500">${asignacion.ficha}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-700">${asignacion.instructor}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-600">${asignacion.ambiente}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-600">${asignacion.competencia}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-600">${asignacion.fecha_inicio}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="${badgeClass} px-3 py-1 rounded-full text-xs font-semibold">
                                    ${asignacion.estado}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-center gap-2">
                                    <button onclick="verAsignacion(${asignacion.id})" 
                                            class="btn-ver text-white px-3 py-1 rounded-lg text-xs font-semibold hover:shadow-md transition">
                                        Ver
                                    </button>
                                    <button onclick="editarAsignacion(${asignacion.id})" 
                                            class="btn-editar text-white px-3 py-1 rounded-lg text-xs font-semibold hover:shadow-md transition">
                                        Editar
                                    </button>
                                    <button onclick="eliminarAsignacion(${asignacion.id})" 
                                            class="btn-eliminar text-white px-3 py-1 rounded-lg text-xs font-semibold hover:shadow-md transition">
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>`;
            }).join('');
        }

        // mantener el resto de funciones sin cambios...

        function verAsignacion(id) {
            const asignacion = asignaciones.find(a => a.id === id);
            if (!asignacion) return;

            const contenido = document.getElementById('contenido-ver');
            contenido.innerHTML = `
                <div class="bg-gray-50 p-4 rounded-xl">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Ficha</p>
                            <p class="text-sm font-semibold text-gray-800">${asignacion.ficha}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Instructor</p>
                            <p class="text-sm font-semibold text-gray-800">${asignacion.instructor}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Ambiente</p>
                            <p class="text-sm font-semibold text-gray-800">${asignacion.ambiente}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Competencia</p>
                            <p class="text-sm font-semibold text-gray-800">${asignacion.competencia}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Fecha Inicio</p>
                            <p class="text-sm font-semibold text-gray-800">${asignacion.fecha_inicio}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Estado</p>
                            <p class="text-sm font-semibold text-gray-800">${asignacion.estado}</p>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('modal-ver').classList.add('active');
        }

        function editarAsignacion(id) {
            console.log('editarAsignacion triggered for', id);
            const asignacion = asignaciones.find(a => a.id === id);
            if (!asignacion) {
                console.warn('no se encontró asignación con id', id);
                return;
            }

            document.getElementById('edit-id').value = asignacion.id;
            document.getElementById('edit-ficha').value = asignacion.ficha;
            document.getElementById('edit-instructor').value = asignacion.instructor;
            document.getElementById('edit-ambiente').value = asignacion.ambiente;
            document.getElementById('edit-competencia').value = asignacion.competencia;
            document.getElementById('edit-fecha').value = asignacion.fecha_inicio;
            document.getElementById('edit-estado').value = asignacion.estado;

            document.getElementById('modal-editar').classList.add('active');
        }

        function eliminarAsignacion(id) {
            asignacionActual = id;
            document.getElementById('modal-eliminar').classList.add('active');
        }

        async function confirmarEliminar() {
            if (asignacionActual) {
                try {
                    const resp = await fetch('api/asignaciones.php?action=eliminar&id=' + encodeURIComponent(asignacionActual), {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const json = await resp.json();
                    if (json.success) {
                        showNotification('Asignación eliminada exitosamente','success');
                        cerrarModal('eliminar');
                        cargarAsignaciones();
                    } else {
                        throw new Error(json.error || 'error desconocido');
                    }
                } catch (err) {
                    console.error('Error eliminando asignación', err);
                    showNotification('No se pudo eliminar la asignación: ' + err.message,'error');
                }
            }
        }

        function cerrarModal(tipo) {
            document.getElementById('modal-' + tipo).classList.remove('active');
            if (tipo === 'eliminar') {
                asignacionActual = null;
            }
        }

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
            }
        }

        // cargar datos cuando la página esté lista
        document.addEventListener('DOMContentLoaded', function() {
            cargarAsignaciones();
        });

        // Manejar submit del formulario de edición
        const formEditar = document.getElementById('form-editar');
        if (formEditar) {
            formEditar.addEventListener('submit', async function(e) {
                e.preventDefault();
                const id = document.getElementById('edit-id').value;
                const estado = document.getElementById('edit-estado').value;
                console.log('submit editar', { id, estado });
                try {
                    const resp = await fetch('api/asignaciones.php?action=actualizar', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body: JSON.stringify({ asignacion_id: id, estado: estado })
                    });
                    const json = await resp.json();
                    if (json.success) {
                        showNotification('Asignación actualizada exitosamente','success');
                        cerrarModal('editar');
                        cargarAsignaciones();
                    } else {
                        throw new Error(json.error || 'error desconocido');
                    }
                } catch (err) {
                    console.error('Error actualizando asignación', err);
                    showNotification('No se pudo actualizar la asignación: ' + err.message,'error');
                }
            });
        } else {
            console.warn('form-editar no encontrado en el DOM');
        }
    </script>
</body>
</html>
