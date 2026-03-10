<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3>Editar Sede</h3>
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

                    <form method="POST" action="index.php?controlador=Sede&accion=actualizar">
                        <input type="hidden" name="sede_id" value="<?= htmlspecialchars($sede->sede_id) ?>">

                        <div class="mb-3">
                            <label for="sede_nombre" class="form-label">Nombre de la Sede <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="sede_nombre" 
                                   name="sede_nombre" 
                                   value="<?= htmlspecialchars($sede->sede_nombre) ?>"
                                   required 
                                   maxlength="45">
                        </div>

                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="direccion" 
                                   name="direccion" 
                                   value="<?= htmlspecialchars($sede->direccion ?? '') ?>"
                                   maxlength="200">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="ciudad" class="form-label">Ciudad <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="ciudad" 
                                       name="ciudad" 
                                       value="<?= htmlspecialchars($sede->ciudad) ?>"
                                       required 
                                       maxlength="100">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="telefono" 
                                       name="telefono" 
                                       value="<?= htmlspecialchars($sede->telefono ?? '') ?>"
                                       maxlength="20">
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <small>
                                <strong>Fecha de creación:</strong> <?= date('d/m/Y H:i', strtotime($sede->fecha_creacion)) ?><br>
                                <strong>Última actualización:</strong> <?= date('d/m/Y H:i', strtotime($sede->fecha_actualizacion)) ?>
                            </small>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="index.php?controlador=Sede&accion=index" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Actualizar Sede
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
