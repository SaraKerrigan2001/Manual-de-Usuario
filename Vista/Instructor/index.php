<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-people"></i> Gestión de Instructores</h2>
        <a href="?controlador=Instructor&accion=registrar" class="btn btn-sena">
            <i class="bi bi-person-plus"></i> Registrar Instructor
        </a>
    </div>

    <?php if (empty($instructores)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> No hay instructores registrados. 
            <a href="?controlador=Instructor&accion=registrar">Registrar el primero</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-success">
                    <tr>
                        <th>Nombre</th>
                        <th>Documento</th>
                        <th>Email</th>
                        <th>Especialidad</th>
                        <th>Experiencias</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($instructores as $instructor): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($instructor['nombre'] . ' ' . $instructor['apellido']); ?></td>
                            <td><?php echo htmlspecialchars($instructor['documento']); ?></td>
                            <td><?php echo htmlspecialchars($instructor['email']); ?></td>
                            <td><?php echo htmlspecialchars($instructor['especialidad'] ?? 'N/A'); ?></td>
                            <td>
                                <?php if (!empty($instructor['experiencias_lista'])): ?>
                                    <small class="text-muted"><?php echo htmlspecialchars($instructor['experiencias_lista']); ?></small>
                                <?php else: ?>
                                    <small class="text-muted">Sin experiencias</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?controlador=Instructor&accion=perfil&id=<?php echo $instructor['id']; ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
