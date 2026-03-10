<?php
$datos = $_SESSION['datos_form'] ?? [];
$errores = $_SESSION['errores'] ?? [];
unset($_SESSION['datos_form'], $_SESSION['errores']);
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="bi bi-award"></i> Nueva Experiencia / Especialidad</h4>
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

            <form action="?controlador=Experiencia&accion=guardar" method="POST" id="formExperiencia">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nombre de la Experiencia *</label>
                    <input type="text" name="nombre" class="form-control" 
                           placeholder="Ej: Programación Backend, Ética y Valores"
                           value="<?php echo htmlspecialchars($datos['nombre'] ?? ''); ?>"
                           minlength="3" maxlength="150" required>
                    <div class="form-text">Mínimo 3 caracteres</div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Área</label>
                        <select name="area" class="form-select">
                            <option value="">-- Seleccione --</option>
                            <option value="Tecnología" <?php echo (isset($datos['area']) && $datos['area'] == 'Tecnología') ? 'selected' : ''; ?>>Tecnología</option>
                            <option value="Humanidades" <?php echo (isset($datos['area']) && $datos['area'] == 'Humanidades') ? 'selected' : ''; ?>>Humanidades</option>
                            <option value="Deportes" <?php echo (isset($datos['area']) && $datos['area'] == 'Deportes') ? 'selected' : ''; ?>>Deportes</option>
                            <option value="Gestión" <?php echo (isset($datos['area']) && $datos['area'] == 'Gestión') ? 'selected' : ''; ?>>Gestión</option>
                            <option value="Diseño" <?php echo (isset($datos['area']) && $datos['area'] == 'Diseño') ? 'selected' : ''; ?>>Diseño</option>
                            <option value="Otro" <?php echo (isset($datos['area']) && $datos['area'] == 'Otro') ? 'selected' : ''; ?>>Otro</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3" 
                              placeholder="Describe las competencias y conocimientos de esta experiencia..."
                              maxlength="500"><?php echo htmlspecialchars($datos['descripcion'] ?? ''); ?></textarea>
                    <div class="form-text">Máximo 500 caracteres</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Programas Relacionados</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="programas_relacionados[]" value="Todos" id="prog_todos">
                        <label class="form-check-label" for="prog_todos">Todos los programas</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="programas_relacionados[]" value="ADSO" id="prog_adso">
                        <label class="form-check-label" for="prog_adso">ADSO</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="programas_relacionados[]" value="Técnico en Sistemas" id="prog_sistemas">
                        <label class="form-check-label" for="prog_sistemas">Técnico en Sistemas</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="programas_relacionados[]" value="Gestión Administrativa" id="prog_gestion">
                        <label class="form-check-label" for="prog_gestion">Gestión Administrativa</label>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="?controlador=Experiencia&accion=listar" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar Experiencia</button>
                </div>
            </form>
        </div>
    </div>
</div>
