<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - SENA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .recovery-wrapper {
            width: 100%;
            max-width: 480px;
        }

        .recovery-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 48px 40px;
            animation: fadeInUp 0.4s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-container {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-badge {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.3);
        }

        .logo-badge i {
            font-size: 40px;
            color: white;
        }

        .header-title {
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }

        .header-subtitle {
            font-size: 15px;
            color: #64748b;
            line-height: 1.6;
            max-width: 360px;
            margin: 0 auto;
        }

        .form-container {
            margin-top: 36px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 10px;
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 20px;
            pointer-events: none;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            color: #0f172a;
            transition: all 0.2s;
            background: #f8fafc;
        }

        .form-input:focus {
            outline: none;
            border-color: #10b981;
            background: white;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }

        .form-input::placeholder {
            color: #cbd5e1;
        }

        .input-hint {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 8px;
            font-size: 13px;
            color: #64748b;
        }

        .input-hint i {
            font-size: 14px;
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit i {
            font-size: 18px;
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 32px 0;
            color: #94a3b8;
            font-size: 14px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e2e8f0;
        }

        .divider span {
            padding: 0 16px;
            font-weight: 500;
        }

        .back-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px;
            color: #64748b;
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            border-radius: 12px;
            transition: all 0.2s;
        }

        .back-link:hover {
            background: #f1f5f9;
            color: #10b981;
        }

        .back-link i {
            font-size: 18px;
        }

        .alert {
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
            border: 2px solid;
            font-size: 14px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert i {
            font-size: 20px;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .alert-content {
            flex: 1;
        }

        .alert-danger {
            background: #fef2f2;
            border-color: #fecaca;
            color: #991b1b;
        }

        .alert-danger i {
            color: #dc2626;
        }

        .alert-success {
            background: #f0fdf4;
            border-color: #bbf7d0;
            color: #166534;
        }

        .alert-success i {
            color: #16a34a;
        }

        .alert-info {
            background: #f0fdf4;
            border-color: #bbf7d0;
            color: #166534;
            flex-direction: column;
            align-items: stretch;
        }

        .alert-info i {
            color: #10b981;
        }

        .alert-header {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .password-box {
            background: white;
            border: 2px dashed #10b981;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin-top: 12px;
        }

        .password-value {
            font-size: 32px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: 4px;
            font-family: 'Courier New', monospace;
            user-select: all;
            margin-bottom: 12px;
        }

        .password-hint {
            font-size: 13px;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        @media (max-width: 480px) {
            .recovery-card {
                padding: 36px 24px;
                border-radius: 20px;
            }

            .header-title {
                font-size: 24px;
            }

            .password-value {
                font-size: 24px;
                letter-spacing: 2px;
            }
        }
    </style>
</head>
<body>
    <div class="recovery-wrapper">
        <div class="recovery-card">
            <div class="logo-container">
                <div class="logo-badge">
                    <i class="bi bi-key-fill"></i>
                </div>
                <h1 class="header-title">Recuperar Contraseña</h1>
                <p class="header-subtitle">
                    Ingresa tu correo electrónico y te ayudaremos a recuperar el acceso a tu cuenta
                </p>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div class="alert-content">
                        <?= htmlspecialchars($_SESSION['error']) ?>
                    </div>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['mensaje'])): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <div class="alert-content">
                        <?= htmlspecialchars($_SESSION['mensaje']) ?>
                    </div>
                </div>
                <?php unset($_SESSION['mensaje']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['password_recuperada'])): ?>
                <div class="alert alert-info">
                    <div class="alert-header">
                        <i class="bi bi-shield-check"></i>
                        <span>Contraseña Recuperada</span>
                    </div>
                    <div class="password-box">
                        <div style="margin-bottom: 16px;">
                            <div style="font-size: 13px; color: #64748b; margin-bottom: 8px; font-weight: 600;">
                                ROL DE USUARIO
                            </div>
                            <div style="font-size: 18px; font-weight: 700; color: #10b981; margin-bottom: 16px;">
                                <?= htmlspecialchars($_SESSION['rol_recuperado']) ?>
                            </div>
                        </div>
                        <div style="border-top: 2px dashed #e2e8f0; padding-top: 16px;">
                            <div style="font-size: 13px; color: #64748b; margin-bottom: 8px; font-weight: 600;">
                                CONTRASEÑA
                            </div>
                            <div class="password-value">
                                <?= htmlspecialchars($_SESSION['password_recuperada']) ?>
                            </div>
                        </div>
                        <div class="password-hint">
                            <i class="bi bi-cursor-fill"></i>
                            Haz clic para seleccionar y copiar
                        </div>
                    </div>
                    <div style="margin-top: 16px; padding: 12px; background: #fef3c7; border-radius: 8px; font-size: 13px; color: #92400e;">
                        <i class="bi bi-exclamation-triangle-fill" style="color: #f59e0b;"></i>
                        <strong>Importante:</strong> Debes seleccionar el rol <strong><?= htmlspecialchars($_SESSION['rol_recuperado']) ?></strong> al iniciar sesión
                    </div>
                </div>
                <?php 
                    unset($_SESSION['password_recuperada']); 
                    unset($_SESSION['rol_recuperado']);
                ?>
                
                <a href="index.php?controlador=Auth&accion=login" class="btn-submit">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Ir al Login
                </a>
            <?php else: ?>
                <form method="POST" action="index.php?controlador=Auth&accion=procesarRecuperacion" class="form-container">
                    <div class="form-group">
                        <label class="form-label">Correo Electrónico</label>
                        <div class="input-group">
                            <i class="bi bi-envelope-fill input-icon"></i>
                            <input 
                                type="email" 
                                name="email" 
                                class="form-input" 
                                placeholder="ejemplo@correo.com" 
                                required
                                autocomplete="email"
                            >
                        </div>
                        <div class="input-hint">
                            <i class="bi bi-info-circle-fill"></i>
                            Ingresa el correo registrado en el sistema
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="bi bi-search"></i>
                        Recuperar Contraseña
                    </button>
                </form>
            <?php endif; ?>

            <div class="divider">
                <span>o</span>
            </div>

            <a href="index.php?controlador=Auth&accion=login" class="back-link">
                <i class="bi bi-arrow-left-circle-fill"></i>
                Volver al inicio de sesión
            </a>
        </div>
    </div>
</body>
</html>
