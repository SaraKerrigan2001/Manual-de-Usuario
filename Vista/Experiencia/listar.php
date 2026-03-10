<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-award"></i> Experiencias y Especialidades</h2>
        <a href="?controlador=Experiencia&accion=nueva" class="btn btn-sena">
            <i class="bi bi-plus-circle"></i> Nueva Experiencia
        </a>
    </div>

    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> Las experiencias definen las especialidades que pueden tener los instructores y se relacionan con programas específicos.
    </div>

    <?php if (empty($experiencias)): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i> No hay experiencias registradas. 
            <a href="?controlador=Experiencia&accion=nueva">Crear la primera</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($experiencias as $exp): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><?php echo htmlspecialchars($exp['nombre']); ?></h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($exp['area'])): ?>
                                <p class="mb-2">
                                    <strong><i class="bi bi-tag"></i> Área:</strong> 
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($exp['area']); ?></span>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($exp['descripcion'])): ?>
                                <p class="text-muted small">
                                    <?php echo htmlspecialchars(substr($exp['descripcion'], 0, 100)); ?>
                                    <?php echo strlen($exp['descripcion']) > 100 ? '...' : ''; ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php 
                            $programas = json_decode($exp['programas_relacionados'] ?? '[]', true);
                            if (!empty($programas)): 
                            ?>
                                <p class="mb-0">
                                    <strong><i class="bi bi-book"></i> Programas:</strong><br>
                                    <?php foreach ($programas as $programa): ?>
                                        <span class="badge bg-success me-1 mb-1"><?php echo htmlspecialchars($programa); ?></span>
                                    <?php endforeach; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <?php echo date('d/m/Y', strtotime($exp['fecha_creacion'])); ?>
                            </small>
                            <a href="?controlador=Experiencia&accion=editar&id=<?php echo $exp['id']; ?>" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i> Editar
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
