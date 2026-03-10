<?php
// Vista/Administrador/CentroFormacion/index.php
$titulo = 'Centro de Formación - SENA';
$titulo_section = 'Gestión Académica';
$active_section = 'centroFormacion';

require_once('Vista/Layouts/admin_layout_header.php');
?>

<div class="animate__animated animate__fadeIn">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <p class="text-gray-500">Administra los recursos educativos, fichas e instructores.</p>
        </div>
        <div class="flex gap-2">
            <button class="bg-green-500/10 text-green-600 px-4 py-2 rounded-xl border border-green-500/20 hover:bg-green-500 hover:text-white transition-all flex items-center gap-2 font-bold">
                <i class="fas fa-file-pdf"></i> Reporte Académico
            </button>
        </div>
    </div>

    <!-- Grid de Gestión Premium -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
        
        <!-- Fichas -->
        <a href="?controlador=Administrador&accion=gestionFichas" class="group bg-white p-8 rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl hover:border-green-500/30 transition-all duration-300 transform hover:-translate-y-1 no-underline">
            <div class="flex items-center justify-between mb-6">
                <div class="w-16 h-16 bg-green-50 rounded-2xl flex items-center justify-center text-green-600 group-hover:bg-green-500 group-hover:text-white transition-all shadow-sm">
                    <i class="fas fa-layer-group text-2xl"></i>
                </div>
                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-50 text-gray-300 group-hover:bg-green-50 group-hover:text-green-500 transition-colors">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-green-600 transition-colors">Gestión de Fichas</h3>
            <p class="text-gray-500 text-sm leading-relaxed">Crea y administra grupos de formación, asigna sedes y controla estados de fichas activas.</p>
        </a>

        <!-- Programas -->
        <a href="?controlador=Administrador&accion=gestionProgramas" class="group bg-white p-8 rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl hover:border-green-500/30 transition-all duration-300 transform hover:-translate-y-1 no-underline">
            <div class="flex items-center justify-between mb-6">
                <div class="w-16 h-16 bg-orange-50 rounded-2xl flex items-center justify-center text-orange-600 group-hover:bg-orange-500 group-hover:text-white transition-all shadow-sm">
                    <i class="fas fa-book-open text-2xl"></i>
                </div>
                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-50 text-gray-300 group-hover:bg-orange-50 group-hover:text-orange-500 transition-colors">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-orange-600 transition-colors">Programas de Formación</h3>
            <p class="text-gray-500 text-sm leading-relaxed">Define la oferta educativa, niveles académicos, tecnologías y duraciones de cada programa.</p>
        </a>

        <!-- Competencias -->
        <a href="?controlador=Administrador&accion=gestionCompetencias" class="group bg-white p-8 rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl hover:border-green-500/30 transition-all duration-300 transform hover:-translate-y-1 no-underline">
            <div class="flex items-center justify-between mb-6">
                <div class="w-16 h-16 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-600 group-hover:bg-purple-500 group-hover:text-white transition-all shadow-sm">
                    <i class="fas fa-award text-2xl"></i>
                </div>
                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-50 text-gray-300 group-hover:bg-purple-50 group-hover:text-purple-500 transition-colors">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-purple-600 transition-colors">Competencias</h3>
            <p class="text-gray-500 text-sm leading-relaxed">Asigna y gestiona las habilidades técnicas y transversales específicas para cada programa.</p>
        </a>

        <!-- Sedes -->
        <a href="?controlador=Administrador&accion=gestionSedes" class="group bg-white p-8 rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl hover:border-green-500/30 transition-all duration-300 transform hover:-translate-y-1 no-underline">
            <div class="flex items-center justify-between mb-6">
                <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 group-hover:bg-blue-500 group-hover:text-white transition-all shadow-sm">
                    <i class="fas fa-building text-2xl"></i>
                </div>
                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-50 text-gray-300 group-hover:bg-blue-50 group-hover:text-blue-500 transition-colors">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-blue-600 transition-colors">Sedes y Centros</h3>
            <p class="text-gray-500 text-sm leading-relaxed">Administra la infraestructura física del centro, ubicaciones geográficas y datos de contacto.</p>
        </a>

        <!-- Ambientes -->
        <a href="?controlador=Administrador&accion=gestionAmbientes" class="group bg-white p-8 rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl hover:border-green-500/30 transition-all duration-300 transform hover:-translate-y-1 no-underline">
            <div class="flex items-center justify-between mb-6">
                <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 group-hover:bg-emerald-500 group-hover:text-white transition-all shadow-sm">
                    <i class="fas fa-door-open text-2xl"></i>
                </div>
                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-50 text-gray-300 group-hover:bg-emerald-50 group-hover:text-emerald-500 transition-colors">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-emerald-600 transition-colors">Ambientes y Aulas</h3>
            <p class="text-gray-500 text-sm leading-relaxed">Controla la capacidad, equipamiento tecnológico de laboratorios, talleres y aulas especializadas.</p>
        </a>

        <!-- Instructores -->
        <a href="?controlador=Administrador&accion=gestionInstructores" class="group bg-white p-8 rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl hover:border-green-500/30 transition-all duration-300 transform hover:-translate-y-1 no-underline">
            <div class="flex items-center justify-between mb-6">
                <div class="w-16 h-16 bg-rose-50 rounded-2xl flex items-center justify-center text-rose-600 group-hover:bg-rose-500 group-hover:text-white transition-all shadow-sm">
                    <i class="fas fa-chalkboard-teacher text-2xl"></i>
                </div>
                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-50 text-gray-300 group-hover:bg-rose-50 group-hover:text-rose-500 transition-colors">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-rose-600 transition-colors">Instructores</h3>
            <p class="text-gray-500 text-sm leading-relaxed">Asigna personal docente a programas, controla especialidades y vinculaciones profesionales.</p>
        </a>

    </div>
</div>

<?php require_once('Vista/Layouts/admin_layout_footer.php'); ?>
