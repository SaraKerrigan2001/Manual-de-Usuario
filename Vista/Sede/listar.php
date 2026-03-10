<div class="container mt-4">
    <div class="row mb-3">
        <div class="col-md-8">
            <h2>Gestión de Sedes</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="index.php?controlador=Sede&accion=nueva" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Sede
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-<?= $_SESSION['tipo_mensaje'] ?? 'info' ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['mensaje']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
    <?php endif; ?>

    <!-- Buscador -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="index.php" class="row g-3">
                <input type="hidden" name="controlador" value="Sede">
                <input type="hidden" name="accion" value="buscar">
                <div class="col-md-10">
                    <input type="text" name="q" class="form-control" placeholder="Buscar por nombre o ciudad..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de sedes -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($sedes)): ?>
                <div class="alert alert-info">
                    No hay sedes registradas. <a href="index.php?controlador=Sede&accion=nueva">Crear la primera sede</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Dirección</th>
                                <th>Ciudad</th>
                                <th>Teléfono</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sedes as $sede): ?>
                                <tr>
                                    <td><?= htmlspecialchars($sede['sede_id']) ?></td>
                                    <td><strong><?= htmlspecialchars($sede['sede_nombre']) ?></strong></td>
                                    <td><?= htmlspecialchars($sede['direccion'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($sede['ciudad']) ?></td>
                                    <td><?= htmlspecialchars($sede['telefono'] ?? 'N/A') ?></td>
                                    <td>
                                        <a href="index.php?controlador=Sede&accion=editar&id=<?= $sede['sede_id'] ?>" 
                                           class="btn btn-sm btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="index.php?controlador=Sede&accion=eliminar&id=<?= $sede['sede_id'] ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('¿Está seguro de eliminar esta sede?')"
                                           title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <p class="text-muted">Total de sedes: <strong><?= count($sedes) ?></strong></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
