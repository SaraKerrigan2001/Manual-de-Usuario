<?php
// Recuperar datos del formulario si hay errores
$datos = $_SESSION['datos_form'] ?? [];
$errores = $_SESSION['errores'] ?? [];
unset($_SESSION['datos_form'], $_SESSION['errores']);

// Obtener datos para los selects
$transversales = $transversales ?? [];
$instructores = $instructores ?? [];
$fichas = $fichas ?? [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Asignación - SENA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h3 class="mb-0">Nueva Asignación de Transversal</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($errores)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errores as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="?controlador=Administrador&accion=guardarAsignacion" method="POST" id="formAsignacion">
                    
                    <div class="mb-3">
                        <label for="id_transversal" class="form-label fw-bold">Seleccionar Transversal: *</label>
                        <select name="id_transversal" id="id_transversal" class="form-select" required>
                            <option value="">-- Seleccione un transversal --</option>
                            <?php foreach ($transversales as $transversal): ?>
                                <option value="<?php echo $transversal['id']; ?>" 
                                    <?php echo (isset($datos['id_transversal']) && $datos['id_transversal'] == $transversal['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($transversal['nombre']); ?> 
                                    (<?php echo $transversal['duracion']; ?>h - <?php echo $transversal['modalidad']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="id_instructor" class="form-label fw-bold">Asignar Instructor: *</label>
                        <select name="id_instructor" id="id_instructor" class="form-select" required>
                            <option value="">-- Seleccione un instructor --</option>
                            <?php foreach ($instructores as $instructor): ?>
                                <option value="<?php echo $instructor['id']; ?>"
                                    <?php echo (isset($datos['id_instructor']) && $datos['id_instructor'] == $instructor['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($instructor['nombre'] . ' ' . $instructor['apellido']); ?>
                                    <?php if (!empty($instructor['especialidad'])): ?>
                                        (<?php echo htmlspecialchars($instructor['especialidad']); ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="id_ficha" class="form-label fw-bold">Ficha de Formación: *</label>
                        <select name="id_ficha" id="id_ficha" class="form-select" required>
                            <option value="">-- Seleccione una ficha --</option>
                            <?php foreach ($fichas as $ficha): ?>
                                <option value="<?php echo $ficha['id']; ?>"
                                    <?php echo (isset($datos['id_ficha']) && $datos['id_ficha'] == $ficha['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($ficha['numero_ficha']); ?> - 
                                    <?php echo htmlspecialchars($ficha['programa']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="inicio" class="form-label fw-bold">Fecha de Inicio: *</label>
                            <input type="date" name="inicio" id="inicio" class="form-control" 
                                   value="<?php echo htmlspecialchars($datos['inicio'] ?? ''); ?>" 
                                   min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fin" class="form-label fw-bold">Fecha de Fin: *</label>
                            <input type="date" name="fin" id="fin" class="form-control" 
                                   value="<?php echo htmlspecialchars($datos['fin'] ?? ''); ?>" 
                                   min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="?controlador=Administrador&accion=index" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-success">Crear Asignación</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validación de fechas
        document.getElementById('formAsignacion').addEventListener('submit', function(e) {
            const inicio = new Date(document.getElementById('inicio').value);
            const fin = new Date(document.getElementById('fin').value);
            
            if (fin <= inicio) {
                e.preventDefault();
                alert('La fecha de fin debe ser posterior a la fecha de inicio');
                return false;
            }
        });
        
        // Actualizar fecha mínima de fin cuando cambia inicio
        document.getElementById('inicio').addEventListener('change', function() {
            document.getElementById('fin').min = this.value;
        });
    </script>
</body>
</html>