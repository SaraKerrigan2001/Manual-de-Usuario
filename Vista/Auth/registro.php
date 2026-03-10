<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - SENA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #F8F9FA;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 12px;
        }

        .register-container {
            background: white;
            border-radius: 12px;
            padding: 22px;
            width: 100%;
            max-width: 640px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.07);
        }

        .register-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .register-header h2 {
            color: #10b981;
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .register-header p {
            color: #6C757D;
            font-size: 14px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 16px;
        }

        .form-group {
            margin-bottom: 12px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            color: #495057;
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 6px;
            display: block;
        }

        .form-label .optional {
            color: #6C757D;
            font-weight: 400;
            font-size: 12px;
        }

        .form-control, .form-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #DEE2E6;
            border-radius: 6px;
            font-size: 13px;
            transition: all 0.22s;
            background: #F8F9FA;
            color: #495057;
        }

        .form-control::placeholder {
            color: #ADB5BD;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: #10b981;
            background: white;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.08);
        }

        .form-select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23495057' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 36px;
        }

        .form-text {
            color: #6C757D;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }

        .btn-registrar {
            width: 100%;
            padding: 12px;
            background: #10b981;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.22s;
            margin-top: 8px;
        }

        .btn-registrar:hover {
            background: #059669;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(16, 185, 129, 0.25);
        }

        .login-link {
            text-align: center;
            margin-top: 18px;
            font-size: 13px;
            color: #6C757D;
        }

        .login-link a {
            color: #10b981;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .alert {
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 18px;
            border: none;
            font-size: 14px;
        }

        .alert-danger {
            background: #FFF3F3;
            color: #DC3545;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            body {
                padding: 20px 10px;
            }

            .register-container {
                padding: 25px 18px;
                border-radius: 0; /* Pantalla completa en móviles */
                box-shadow: none;
            }

            .register-header h2 {
                font-size: 24px;
            }
        }

        @media (max-width: 480px) {
            .register-header p {
                font-size: 13px;
            }

            .btn-registrar {
                padding: 14px;
                font-size: 14px;
            }
        }
    </style>
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h2>Registro</h2>
            <p>Complete el formulario para crear su cuenta</p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form method="POST" action="index.php?controlador=Auth&accion=procesarRegistro">
            <div class="form-group full-width">
                <label class="form-label">Rol</label>
                <select class="form-select" name="rol" required>
                    <option value="">Seleccione su rol</option>
                    <option value="administrador">Administrador</option>
                    <option value="coordinador">Coordinador</option>
                    <option value="instructor">Instructor</option>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Tipo de Documento</label>
                    <select class="form-select" name="tipo_documento" required>
                        <option value="CC">Cédula de Ciudadanía</option>
                        <option value="CE">Cédula de Extranjería</option>
                        <option value="TI">Tarjeta de Identidad</option>
                        <option value="PAS">Pasaporte</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Número de Documento</label>
                    <input type="text" name="documento" class="form-control" placeholder="1234567890" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Nombres</label>
                    <input type="text" name="nombres" class="form-control" placeholder="Juan Carlos" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Apellidos</label>
                    <input type="text" name="apellidos" class="form-control" placeholder="Pérez García" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Correo Electrónico</label>
                    <input type="email" name="email" class="form-control" placeholder="ejemplo@correo.com" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Teléfono <span class="optional">(Opcional)</span></label>
                    <input type="tel" name="telefono" class="form-control" placeholder="3001234567">
                </div>
            </div>

            <div class="form-group full-width">
                <label class="form-label">Dirección <span class="optional">(Opcional)</span></label>
                <input type="text" name="direccion" class="form-control" placeholder="Calle 123 #45-67">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    <small class="form-text">Mínimo 6 caracteres</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirmar Contraseña</label>
                    <input type="password" name="password_confirm" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn-registrar">
                Registrarse
            </button>
        </form>

        <div class="login-link">
            ¿Ya tienes cuenta? <a href="index.php?controlador=Auth&accion=login">Inicia sesión</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
