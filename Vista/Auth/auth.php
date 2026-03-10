<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - SENA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --sena-verde: #39A900;
            --sena-naranja: #FF5800;
            --sena-verde-oscuro: #2d8400;
            --sena-naranja-oscuro: #cc4600;
        }
        
        body {
            background: linear-gradient(135deg, var(--sena-verde) 0%, var(--sena-verde-oscuro) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .auth-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            margin: 20px;
        }
        
        .auth-tabs {
            display: flex;
            background: #f8f9fa;
        }
        
        .auth-tab {
            flex: 1;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            border: none;
            background: transparent;
            font-weight: 600;
            color: #6c757d;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
        }
        
        .auth-tab.active {
            color: var(--sena-verde);
            border-bottom-color: var(--sena-verde);
            background: white;
        }
        
        .auth-content {
            padding: 40px;
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo-sena {
            width: 120px;
            height: 120px;
            margin: 0 auto 20px;
            background: var(--sena-verde);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
        }
        
        .auth-header h2 {
            color: var(--sena-verde);
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .auth-header p {
            color: #6c757d;
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--sena-verde);
            box-shadow: 0 0 0 0.2rem rgba(57, 169, 0, 0.25);
        }
        
        .btn-sena {
            background: var(--sena-naranja);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 10px;
            color: white;
            width: 100%;
            transition: all 0.3s;
            margin-top: 20px;
        }
        
        .btn-sena:hover {
            background: var(--sena-naranja-oscuro);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 88, 0, 0.3);
            color: white;
        }
        
        .role-selector {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .role-card {
            padding: 20px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
        }
        
        .role-card:hover {
            border-color: var(--sena-verde);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(57, 169, 0, 0.2);
        }
        
        .role-card.selected {
            border-color: var(--sena-verde);
            background: rgba(57, 169, 0, 0.05);
        }
        
        .role-card input[type="radio"] {
            display: none;
        }
        
        .role-icon {
            font-size: 36px;
            color: var(--sena-verde);
            margin-bottom: 10px;
        }
        
        .role-card.selected .role-icon {
            color: var(--sena-naranja);
        }
        
        .role-name {
            font-weight: 600;
            color: #495057;
            margin: 0;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .alert-danger {
            background: #ffe5e5;
            color: #c41e3a;
        }
        
        .alert-success {
            background: #e5ffe5;
            color: var(--sena-verde-oscuro);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            border-left: 4px solid var(--sena-verde);
        }
        
        .info-box small {
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-tabs">
            <button class="auth-tab active" onclick="switchTab('login')">
                <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
            </button>
            <button class="auth-tab" onclick="switchTab('register')">
                <i class="bi bi-person-plus"></i> Registrarse
            </button>
        </div>

        <div class="auth-content">
            <!-- LOGIN TAB -->
            <div id="login-tab" class="tab-content active">
                <div class="auth-header">
                    <div class="logo-sena">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                    <h2>Bienvenido al SENA</h2>
                    <p>Ingresa tus credenciales para acceder al sistema</p>
                </div>

                <?php if (isset($_SESSION['error']) && !isset($_GET['tab'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['mensaje']) && !isset($_GET['tab'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle"></i> <?= htmlspecialchars($_SESSION['mensaje']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['mensaje']); ?>
                <?php endif; ?>

                <form method="POST" action="index.php?controlador=Auth&accion=procesarLogin">
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-envelope"></i> Correo Electrónico
                        </label>
                        <input type="email" name="email" class="form-control" placeholder="correo@ejemplo.com" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-lock"></i> Contraseña
                        </label>
                        <input type="password" name="password" class="form-control" placeholder="Ingresa tu contraseña" required>
                    </div>

                    <button type="submit" class="btn btn-sena">
                        <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                    </button>
                </form>

                <!-- Usuarios de prueba eliminado -->
            </div>

            <!-- REGISTER TAB -->
            <div id="register-tab" class="tab-content">
                <div class="auth-header">
                    <div class="logo-sena">
                        <i class="bi bi-person-plus-fill"></i>
                    </div>
                    <h2>Crear Cuenta</h2>
                    <p>Regístrate en el sistema SENA</p>
                </div>

                <?php if (isset($_SESSION['error']) && isset($_GET['tab']) && $_GET['tab'] === 'register'): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <form method="POST" action="index.php?controlador=Auth&accion=procesarRegistro">
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="bi bi-person-badge"></i> Selecciona tu Rol
                        </label>
                        <div class="role-selector">
                            <label class="role-card" onclick="selectRole(this)">
                                <input type="radio" name="rol" value="coordinador" required>
                                <div class="role-icon">
                                    <i class="bi bi-person-gear"></i>
                                </div>
                                <p class="role-name">Coordinador</p>
                            </label>
                            
                            <label class="role-card" onclick="selectRole(this)">
                                <input type="radio" name="rol" value="instructor" required>
                                <div class="role-icon">
                                    <i class="bi bi-person-workspace"></i>
                                </div>
                                <p class="role-name">Instructor</p>
                            </label>
                            
                            <label class="role-card" onclick="selectRole(this)">
                                <input type="radio" name="rol" value="usuario" required>
                                <div class="role-icon">
                                    <i class="bi bi-person"></i>
                                </div>
                                <p class="role-name">Usuario</p>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-envelope"></i> Correo Electrónico
                        </label>
                        <input type="email" name="email" class="form-control" placeholder="correo@ejemplo.com" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-lock"></i> Contraseña
                        </label>
                        <input type="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-lock-fill"></i> Confirmar Contraseña
                        </label>
                        <input type="password" name="password_confirm" class="form-control" placeholder="Repite tu contraseña" required>
                    </div>

                    <button type="submit" class="btn btn-sena">
                        <i class="bi bi-person-plus"></i> Registrarse
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function switchTab(tab) {
            // Update tabs
            document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
            event.target.closest('.auth-tab').classList.add('active');
            
            // Update content
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.getElementById(tab + '-tab').classList.add('active');
        }
        
        function selectRole(card) {
            document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            card.querySelector('input[type="radio"]').checked = true;
        }
        
        // Check if we need to show register tab
        <?php if (isset($_GET['tab']) && $_GET['tab'] === 'register'): ?>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.auth-tab')[1].click();
            });
        <?php endif; ?>
    </script>
</body>
</html>
