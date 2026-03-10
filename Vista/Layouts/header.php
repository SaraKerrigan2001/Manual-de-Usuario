<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo ?? 'Sistema SENA'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --sena-verde: #39A900;
            --sena-naranja: #FF6600;
        }
        .navbar-sena {
            background-color: var(--sena-verde);
        }
        .btn-sena {
            background-color: var(--sena-verde);
            color: white;
        }
        .btn-sena:hover {
            background-color: #2d8000;
            color: white;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-sena">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-mortarboard-fill"></i> SENA - Sistema de Gestión
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="?controlador=Administrador&accion=index">
                            <i class="bi bi-house"></i> Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?controlador=Sede&accion=index">
                            <i class="bi bi-building"></i> Sedes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?controlador=Administrador&accion=verTransversales">
                            <i class="bi bi-journal-text"></i> Transversales
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?controlador=Instructor&accion=index">
                            <i class="bi bi-people"></i> Instructores
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?controlador=Experiencia&accion=listar">
                            <i class="bi bi-award"></i> Experiencias
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="container mt-3">
            <div class="alert alert-<?php echo $_SESSION['tipo_mensaje'] ?? 'info'; ?> alert-dismissible fade show">
                <?php echo htmlspecialchars($_SESSION['mensaje']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
    <?php endif; ?>

    <main class="container-fluid py-4">
