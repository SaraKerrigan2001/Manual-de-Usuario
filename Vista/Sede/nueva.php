<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3>Nueva Sede</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['errores'])): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($_SESSION['errores'] as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php unset($_SESSION['errores']); ?>
                    <?php endif; ?>

                    <form method="POST" action="index.php?controlador=Sede&accion=guardar">
                        <div class="mb-3">
                            <label for="sede_nombre" class="form-label">Nombre de la Sede <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="sede_nombre" 
                                   name="sede_nombre" 
                                   value="<?= htmlspecialchars($_SESSION['datos_form']['sede_nombre'] ?? '') ?>"
                                   required 
                                   maxlength="45"
                                   placeholder="Ej: Centro de Biotecnología Agropecuaria">
                        </div>

                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="direccion" 
                                   name="direccion" 
                                   value="<?= htmlspecialchars($_SESSION['datos_form']['direccion'] ?? '') ?>"
                                   maxlength="200"
                                   placeholder="Ej: Calle 52 # 13-65">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="ciudad" class="form-label">Ciudad <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="ciudad" 
                                       name="ciudad" 
                                       value="<?= htmlspecialchars($_SESSION['datos_form']['ciudad'] ?? '') ?>"
                                       required 
                                       maxlength="100"
                                       placeholder="Ej: Bogotá">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="telefono" 
                                       name="telefono" 
                                       value="<?= htmlspecialchars($_SESSION['datos_form']['telefono'] ?? '') ?>"
                                       maxlength="20"
                                       placeholder="Ej: 3001234567">
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="index.php?controlador=Sede&accion=index" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Sede
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php unset($_SESSION['datos_form']); ?>
