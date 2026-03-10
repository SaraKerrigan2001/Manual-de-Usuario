<?php
// Recuperar datos del formulario si hay errores
$datos = $_SESSION['datos_form'] ?? [];
$errores = $_SESSION['errores'] ?? [];
unset($_SESSION['datos_form'], $_SESSION['errores']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Transversal - SENA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Crear Nuevo Transversal / Programa</h4>
                <a href="?controlador=Experiencia&accion=verTransversales" class="btn-close" aria-label="Close"></a>
            </div>
            
            <div class="card-body">
                <?php if (!empty($errores)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <strong>Errores encontrados:</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach ($errores as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form action="?controlador=Administrador&accion=guardarPrograma" method="POST" id="formPrograma">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold">Nombre del Transversal *</label>
                            <input type="text" name="nombre_transversal" class="form-control" 
                                   placeholder="Ej: Ética y Transformación del Entorno" 
                                   value="<?php echo htmlspecialchars($datos['nombre_transversal'] ?? ''); ?>"
                                   minlength="3" maxlength="200" required>
                            <div class="form-text">Mínimo 3 caracteres</div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Duración (horas) *</label>
                            <input type="number" name="duracion" class="form-control" 
                                   placeholder="40" 
                                   value="<?php echo htmlspecialchars($datos['duracion'] ?? ''); ?>"
                                   min="1" max="500" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Modalidad *</label>
                            <select name="modalidad" class="form-select" required>
                                <option value="">-- Seleccione --</option>
                                <option value="Presencial" <?php echo (isset($datos['modalidad']) && $datos['modalidad'] == 'Presencial') ? 'selected' : ''; ?>>Presencial</option>
                                <option value="Virtual" <?php echo (isset($datos['modalidad']) && $datos['modalidad'] == 'Virtual') ? 'selected' : ''; ?>>Virtual</option>
                                <option value="Híbrida" <?php echo (isset($datos['modalidad']) && $datos['modalidad'] == 'Híbrida') ? 'selected' : ''; ?>>Híbrida</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Programa *</label>
                            <select name="programa_base" class="form-select" required>
                                <option value="">-- Seleccione --</option>
                                <option value="Todos" <?php echo (isset($datos['programa_base']) && $datos['programa_base'] == 'Todos') ? 'selected' : ''; ?>>Todos los programas</option>
                                <option value="ADSO" <?php echo (isset($datos['programa_base']) && $datos['programa_base'] == 'ADSO') ? 'selected' : ''; ?>>ADSO</option>
                                <option value="Sistemas" <?php echo (isset($datos['programa_base']) && $datos['programa_base'] == 'Sistemas') ? 'selected' : ''; ?>>Técnico en Sistemas</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Objetivo Formativo *</label>
                        <textarea name="objetivo" class="form-control" rows="2" 
                                  placeholder="Describe el objetivo principal del transversal..." 
                                  maxlength="500" required><?php echo htmlspecialchars($datos['objetivo'] ?? ''); ?></textarea>
                        <div class="form-text">Máximo 500 caracteres</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3" 
                                  placeholder="Descripción detallada del transversal..." 
                                  maxlength="1000"><?php echo htmlspecialchars($datos['descripcion'] ?? ''); ?></textarea>
                        <div class="form-text">Opcional - Máximo 1000 caracteres</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold d-flex justify-content-between">
                            Competencias 
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAgregarCompetencia">+ Agregar</button>
                        </label>
                        <div id="competenciasContainer">
                            <input type="text" name="competencias[]" class="form-control mb-2" 
                                   placeholder="Competencia 1" maxlength="200">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="?controlador=Experiencia&accion=verTransversales" class="btn btn-light border">Cancelar</a>
                        <button type="submit" class="btn btn-success">Crear Transversal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Agregar más campos de competencias dinámicamente
        let competenciaCount = 1;
        document.getElementById('btnAgregarCompetencia').addEventListener('click', function() {
            if (competenciaCount < 10) {
                competenciaCount++;
                const container = document.getElementById('competenciasContainer');
                const newInput = document.createElement('input');
                newInput.type = 'text';
                newInput.name = 'competencias[]';
                newInput.className = 'form-control mb-2';
                newInput.placeholder = 'Competencia ' + competenciaCount;
                newInput.maxLength = 200;
                container.appendChild(newInput);
            } else {
                alert('Máximo 10 competencias permitidas');
            }
        });

        // Validación del formulario
        document.getElementById('formPrograma').addEventListener('submit', function(e) {
            const nombre = document.querySelector('[name="nombre_transversal"]').value.trim();
            const duracion = document.querySelector('[name="duracion"]').value;
            
            if (nombre.length < 3) {
                e.preventDefault();
                alert('El nombre debe tener al menos 3 caracteres');
                return false;
            }
            
            if (duracion < 1 || duracion > 500) {
                e.preventDefault();
                alert('La duración debe estar entre 1 y 500 horas');
                return false;
            }
        });
    </script>
</body>
</html>