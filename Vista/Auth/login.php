<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - SENA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            overflow-y: auto;
            background-color: #f3f4f6;
        }

        .login-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Lado izquierdo con imagen de fondo */
        .left-side {
            flex: 1;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(0, 0, 0, 0.6)), 
                        url('assets/img/login-bg.jpeg') center/cover no-repeat;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            padding: 60px;
            position: relative;
            overflow: hidden;
            animation: fadeIn 1s ease-out;
        }

        .left-side::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: inherit;
            filter: blur(2px);
            z-index: -1;
            transform: scale(1.1);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .sena-logo {
            width: 110px;
            height: 110px;
            margin-bottom: 25px;
            filter: drop-shadow(0 8px 12px rgba(0,0,0,0.3));
            transition: transform 0.5s ease;
        }

        .sena-logo:hover {
            transform: scale(1.05) rotate(5deg);
        }

        .sena-logo svg {
            width: 100%;
            height: 100%;
            fill: white;
        }

        .left-side h1 {
            font-size: 32px;
            font-weight: 800;
            text-align: center;
            margin-bottom: 15px;
            line-height: 1.1;
            text-shadow: 0 4px 8px rgba(0,0,0,0.5);
            letter-spacing: -1px;
        }

        .left-side p {
            font-size: 16px;
            text-align: center;
            max-width: 450px;
            line-height: 1.5;
            opacity: 0.9;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            font-weight: 300;
        }

        /* Lado derecho con formulario */
        .right-side {
            flex: 0 0 420px;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
            position: relative;
        }

        .login-card {
            width: 100%;
            max-width: 340px;
            animation: slideInRight 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideInRight {
            from { transform: translateX(20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .login-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .login-header h2 {
            color: #10b981;
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 5px;
            letter-spacing: -1px;
        }

        .login-header p {
            color: #6b7280;
            font-size: 14px;
            font-weight: 400;
        }

        .form-group {
            margin-bottom: 18px;
            position: relative;
        }

        .form-label {
            color: #374151;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
            display: block;
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 16px;
            transition: color 0.3s;
        }

        .form-control {
            width: 100%;
            padding: 11px 14px 11px 42px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.2s;
            background: #f9fafb;
            color: #111827;
        }

        .form-select {
            padding-left: 42px;
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            background-size: 16px;
        }

        .form-control:focus {
            outline: none;
            border-color: #10b981;
            background: white;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }

        .form-control:focus + i {
            color: #10b981;
        }

        .form-check {
            display: flex;
            align-items: center;
            margin: 20px 0;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            border-radius: 5px;
            border: 1.5px solid #d1d5db;
            margin-top: 0;
            margin-right: 10px;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: #10b981;
            border-color: #10b981;
        }

        .form-check-label {
            color: #4b5563;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
        }

        .btn-ingresar {
            width: 100%;
            padding: 13px;
            background: #10b981;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2);
        }

        .btn-ingresar:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 12px -3px rgba(16, 185, 129, 0.3);
            filter: brightness(1.05);
        }

        .forgot-password {
            text-align: center;
            margin-top: 15px;
        }

        .forgot-password a {
            color: #6b7280;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
            transition: color 0.2s;
        }

        .forgot-password a:hover {
            color: #10b981;
            text-decoration: underline;
        }

        .register-link {
            text-align: center;
            margin-top: 25px;
            font-size: 13px;
            color: #6b7280;
            padding-top: 15px;
            border-top: 1px solid #f3f4f6;
        }

        .register-link a {
            color: #10b981;
            text-decoration: none;
            font-weight: 700;
        }

        .support-info {
            display: flex;
            gap: 6px;
            align-items: center;
            margin-top: 6px;
            color: #9ca3af;
            font-size: 11px;
            line-height: 1.3;
        }

        @media (max-width: 1024px) {
            .right-side {
                flex: 0 0 450px;
            }
        }

        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column;
            }

            .left-side {
                flex: 0 0 auto;
                padding: 40px 20px;
                min-height: 250px;
            }

            .right-side {
                flex: 1;
                width: 100%;
                padding: 30px 20px;
                background-color: #ffffff;
            }

            .left-side h1 {
                font-size: 26px;
                margin-bottom: 10px;
            }

            .left-side p {
                font-size: 14px;
                max-width: 300px;
            }

            .sena-logo {
                width: 80px;
                height: 80px;
                margin-bottom: 15px;
            }
        }

        @media (max-width: 480px) {
            .left-side {
                padding: 30px 15px;
                min-height: 200px;
            }
            
            .left-side p {
                display: none; /* Ocultar descripción larga en móviles muy pequeños */
            }

            .right-side {
                padding: 20px 15px;
            }

            .login-card {
                max-width: 100%;
            }

            .login-header h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <!-- Lado Izquierdo -->
        <div class="left-side">
            <div class="sena-logo">
                <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="100" cy="40" r="20" fill="white"/>
                    <path d="M 60 80 L 100 120 L 140 80 L 100 160 Z" fill="white"/>
                    <rect x="85" y="120" width="30" height="60" fill="white"/>
                </svg>
            </div>
            <h1>Sistema de Gestión de<br>Bienes</h1>
            <p>Plataforma integral para el control y administración de activos institucionales</p>
        </div>

        <!-- Lado Derecho -->
        <div class="right-side">
            <div class="login-card">
                <div class="login-header">
                    <h2>Bienvenido</h2>
                    <p>Inicia sesión en tu cuenta</p>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['mensaje'])): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> <?= htmlspecialchars($_SESSION['mensaje']) ?>
                    </div>
                    <?php unset($_SESSION['mensaje']); ?>
                <?php endif; ?>

                <form method="POST" action="index.php?controlador=Auth&accion=procesarLogin">
                    <div class="form-group">
                        <label class="form-label">Rol de Usuario</label>
                        <div class="input-with-icon">
                            <i class="bi bi-person-badge"></i>
                            <select class="form-select form-control" name="rol" required>
                                <option value="">Seleccione su rol</option>
                                <option value="administrador">Administrador</option>
                                <option value="coordinador">Coordinador</option>
                                <option value="instructor">Instructor</option>
                            </select>
                        </div>
                        <div class="support-info">
                            Selecciona el rol correspondiente a tu cuenta
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Correo Institucional</label>
                        <div class="input-with-icon">
                            <i class="bi bi-envelope-at"></i>
                            <input type="email" name="email" class="form-control" placeholder="ejemplo@sena.edu.co" required autocomplete="email">
                        </div>
                        <div class="support-info">
                            Usa tu correo registrado en el sistema
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Contraseña</label>
                        <div class="input-with-icon">
                            <i class="bi bi-shield-lock"></i>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
                        </div>
                        <div class="support-info">
                            Introduce tu clave de acceso
                        </div>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="recordarme">
                        <label class="form-check-label" for="recordarme">
                            Mantener sesión iniciada
                        </label>
                    </div>

                    <button type="submit" class="btn-ingresar">
                        Iniciar Sesión Segura
                    </button>

                    <div class="forgot-password">
                        <a href="index.php?controlador=Auth&accion=olvidoPassword">
                            ¿Olvidaste tu contraseña? Recupérala aquí
                        </a>
                    </div>
                </form>

                <div class="register-link">
                    ¿No tienes cuenta? <a href="index.php?controlador=Auth&accion=registro">Regístrate aquí</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
