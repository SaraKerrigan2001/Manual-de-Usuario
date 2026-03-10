<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-journal-text"></i> Gestión de Transversales</h2>
        <a href="?controlador=Administrador&accion=nuevoPrograma" class="btn btn-sena">
            <i class="bi bi-plus-circle"></i> Crear Nuevo Transversal
        </a>
    </div>

    <?php if (empty($transversales)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> No hay transversales registrados. 
            <a href="?controlador=Administrador&accion=nuevoPrograma">Crear el primero</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($transversales as $transversal): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><?php echo htmlspecialchars($transversal['nombre']); ?></h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text">
                                <strong>Duración:</strong> <?php echo $transversal['duracion']; ?> horas<br>
                                <strong>Modalidad:</strong> <?php echo htmlspecialchars($transversal['modalidad']); ?><br>
                                <strong>Programa:</strong> <?php echo htmlspecialchars($transversal['programa']); ?>
                            </p>
                            <?php if (!empty($transversal['objetivo'])): ?>
                                <p class="text-muted small">
                                    <?php echo htmlspecialchars(substr($transversal['objetivo'], 0, 100)); ?>
                                    <?php echo strlen($transversal['objetivo']) > 100 ? '...' : ''; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-transparent">
                            <small class="text-muted">
                                Creado: <?php echo date('d/m/Y', strtotime($transversal['fecha_creacion'])); ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
