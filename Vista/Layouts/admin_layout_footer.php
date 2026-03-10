    </main>

    <!-- Panel de Notificaciones -->
    <div id="notifPanel" class="hidden fixed right-4 top-20 w-96 bg-white rounded-2xl shadow-2xl border border-gray-200 z-50 max-h-[600px] overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="font-bold text-gray-900">Notificaciones</h3>
            <button onclick="closeNotifications()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="overflow-y-auto max-h-[500px]">
            <div class="p-4 space-y-3">
                <!-- Notificación 1 -->
                <div class="p-3 bg-green-50 rounded-xl border border-green-100 hover:bg-green-100 transition cursor-pointer">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white flex-shrink-0">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900">Sistema Actualizado</p>
                            <p class="text-xs text-gray-600 mt-1">La base de datos ha sido actualizada correctamente</p>
                            <p class="text-xs text-gray-400 mt-2">Hace 5 minutos</p>
                        </div>
                    </div>
                </div>

                <!-- Notificación 2 -->
                <div class="p-3 bg-blue-50 rounded-xl border border-blue-100 hover:bg-blue-100 transition cursor-pointer">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white flex-shrink-0">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900">Nuevo Instructor</p>
                            <p class="text-xs text-gray-600 mt-1">Se ha registrado un nuevo instructor en el sistema</p>
                            <p class="text-xs text-gray-400 mt-2">Hace 1 hora</p>
                        </div>
                    </div>
                </div>

                <!-- Notificación 3 -->
                <div class="p-3 bg-yellow-50 rounded-xl border border-yellow-100 hover:bg-yellow-100 transition cursor-pointer">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center text-white flex-shrink-0">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900">Asignación Pendiente</p>
                            <p class="text-xs text-gray-600 mt-1">Hay 3 asignaciones pendientes de aprobación</p>
                            <p class="text-xs text-gray-400 mt-2">Hace 2 horas</p>
                        </div>
                    </div>
                </div>

                <!-- Notificación 4 -->
                <div class="p-3 bg-purple-50 rounded-xl border border-purple-100 hover:bg-purple-100 transition cursor-pointer">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center text-white flex-shrink-0">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900">Evento Próximo</p>
                            <p class="text-xs text-gray-600 mt-1">Reunión de coordinación mañana a las 10:00 AM</p>
                            <p class="text-xs text-gray-400 mt-2">Hace 3 horas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="p-3 border-t border-gray-200 text-center">
            <a href="#" class="text-sm text-green-500 hover:text-green-600 font-semibold">Ver todas las notificaciones</a>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const icon = document.querySelector('.toggle-btn i');
            sidebar.classList.toggle('collapsed');
        }

        document.getElementById('profileToggle')?.addEventListener('click', (e) => {
            e.stopPropagation();
            document.getElementById('profileMenu').classList.toggle('hidden');
        });

        document.addEventListener('click', () => {
            const menu = document.getElementById('profileMenu');
            if (menu) menu.classList.add('hidden');
        });

        // Manejo de notificaciones
        document.getElementById('notifBtn')?.addEventListener('click', (e) => {
            e.stopPropagation();
            const panel = document.getElementById('notifPanel');
            panel.classList.toggle('hidden');
        });

        function closeNotifications() {
            document.getElementById('notifPanel').classList.add('hidden');
        }

        document.addEventListener('click', (e) => {
            const panel = document.getElementById('notifPanel');
            const btn = document.getElementById('notifBtn');
            if (panel && !panel.contains(e.target) && !btn.contains(e.target)) {
                panel.classList.add('hidden');
            }
        });

        document.getElementById('themeToggle')?.addEventListener('click', () => {
            const body = document.body;
            const icon = document.querySelector('#themeToggle i');
            if (body.getAttribute('data-theme') === 'dark') {
                body.removeAttribute('data-theme');
                icon.className = 'fas fa-moon';
                localStorage.setItem('theme', 'light');
            } else {
                body.setAttribute('data-theme', 'dark');
                icon.className = 'fas fa-sun';
                localStorage.setItem('theme', 'dark');
            }
        });

        // Cargar tema guardado
        document.addEventListener('DOMContentLoaded', () => {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.body.setAttribute('data-theme', 'dark');
                const icon = document.querySelector('#themeToggle i');
                if (icon) icon.className = 'fas fa-sun';
            }
        });

        function confirmDelete(url) {
            if (confirm('¿Estás seguro de que deseas eliminar este registro? Esta acción no se puede deshacer.')) {
                window.location.href = url;
            }
        }
    </script>
</body>
</html>
