<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: white;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo h1 {
            color: #333;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .logo p {
            color: #666;
            font-size: 0.9rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background: #d1edff;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .demo-credentials {
            margin-top: 1.5rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 0.8rem;
            color: #666;
        }
        
        .demo-credentials h4 {
            margin-bottom: 0.5rem;
            color: #333;
        }

        .loading {
            display: none;
            text-align: center;
            margin-top: 10px;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            display: inline-block;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>🏭 Sistema Integral</h1>
            <p>Mobiliario y Producción</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <?php
                $mensajesError = [
                    'credenciales_invalidas' => '❌ Usuario o contraseña incorrectos.',
                    'campos_requeridos' => '⚠️ Por favor, complete todos los campos.',
                    'error_sistema' => '💥 Error del sistema. Intente más tarde.',
                    'metodo_no_permitido' => '🚫 Método no permitido.',
                    'debes_iniciar_sesion' => '🔐 Debe iniciar sesión para acceder.',
                    'sesion_expirada' => '⏰ Su sesión ha expirado. Ingrese nuevamente.'
                ];
                echo $mensajesError[$error] ?? '❌ Error desconocido.';
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <?php
                $mensajesSuccess = [
                    'sesion_cerrada' => '✅ Sesión cerrada exitosamente.'
                ];
                echo $mensajesSuccess[$success] ?? '✅ Operación exitosa.';
                ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?= BASE_URL ?>/index.php?url=login" id="loginForm">
            <div class="form-group">
                <label for="username">Usuario:</label>
                <input type="text" id="username" name="username" required 
                       placeholder="Ingrese su usuario" value="admin" autocomplete="username">
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required 
                       placeholder="Ingrese su contraseña" value="admin123" autocomplete="current-password">
            </div>
            
            <button type="submit" class="btn-login" id="btnLogin">
                🔑 Iniciar Sesión
            </button>
            
            <div class="loading" id="loading">
                <div class="spinner"></div>
                <span style="margin-left: 10px;">Iniciando sesión...</span>
            </div>
        </form>
        
        <!-- Credenciales de demo -->
        <div class="demo-credentials">
            <h4>💡 Credenciales de Demo:</h4>
            <p><strong>Usuario:</strong> admin</p>
            <p><strong>Contraseña:</strong> admin123</p>
            <p><em>Nota: Estas son credenciales temporales para pruebas</em></p>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btnLogin = document.getElementById('btnLogin');
            const loading = document.getElementById('loading');
            
            // Mostrar loading
            btnLogin.style.display = 'none';
            loading.style.display = 'block';
            
            // Opcional: prevenir múltiples envíos
            setTimeout(() => {
                this.submit();
            }, 500);
        });

        // Focus en el campo de usuario al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });

        // Limpiar mensajes de error después de 5 segundos
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>