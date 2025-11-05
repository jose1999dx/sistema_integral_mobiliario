<?php
/**
 * Controlador de Dashboard
 */

class DashboardController {
    
    public function index() {
        // Verificar autenticación
        AuthMiddleware::checkAuth();
        
        $usuario = Auth::user();
        
        $data = [
            'titulo' => 'Dashboard Principal',
            'usuario' => $usuario,
            'base_url' => BASE_URL
        ];
        
        // Intentar cargar la vista, si no existe mostrar HTML temporal
        $viewFile = APP_PATH . '/views/direccion/dashboard/index.php';
        if (file_exists($viewFile)) {
            view('direccion/dashboard/index', $data);
        } else {
            $this->mostrarDashboardTemporal($data);
        }
    }
    
    /**
     * Dashboard temporal (mientras creamos las vistas)
     */
    private function mostrarDashboardTemporal($data) {
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= htmlspecialchars($data['titulo']) ?></title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    background: #f8f9fa;
                    color: #333;
                }
                
                .header {
                    background: white;
                    padding: 1.5rem 2rem;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                    border-bottom: 3px solid #667eea;
                }
                
                .header-content {
                    display: flex;
                    justify-content: between;
                    align-items: center;
                    max-width: 1200px;
                    margin: 0 auto;
                }
                
                .logo h1 {
                    color: #667eea;
                    font-size: 1.8rem;
                }
                
                .user-info {
                    text-align: right;
                }
                
                .user-name {
                    font-weight: 600;
                    color: #333;
                }
                
                .user-role {
                    color: #666;
                    font-size: 0.9rem;
                }
                
                .nav {
                    background: #2c3e50;
                    padding: 1rem 2rem;
                }
                
                .nav-links {
                    display: flex;
                    gap: 2rem;
                    max-width: 1200px;
                    margin: 0 auto;
                    list-style: none;
                }
                
                .nav-links a {
                    color: white;
                    text-decoration: none;
                    padding: 0.5rem 1rem;
                    border-radius: 5px;
                    transition: background 0.3s;
                }
                
                .nav-links a:hover {
                    background: rgba(255,255,255,0.1);
                }
                
                .nav-links a.logout {
                    background: #e74c3c;
                    margin-left: auto;
                }
                
                .nav-links a.logout:hover {
                    background: #c0392b;
                }
                
                .main-content {
                    max-width: 1200px;
                    margin: 2rem auto;
                    padding: 0 2rem;
                }
                
                .welcome-section {
                    background: white;
                    padding: 2rem;
                    border-radius: 10px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                    margin-bottom: 2rem;
                    text-align: center;
                }
                
                .welcome-section h2 {
                    color: #667eea;
                    margin-bottom: 1rem;
                }
                
                .stats-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                    gap: 1.5rem;
                    margin-bottom: 2rem;
                }
                
                .stat-card {
                    background: white;
                    padding: 1.5rem;
                    border-radius: 10px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                    text-align: center;
                    border-left: 4px solid #667eea;
                }
                
                .stat-icon {
                    font-size: 2rem;
                    margin-bottom: 0.5rem;
                }
                
                .stat-number {
                    font-size: 2rem;
                    font-weight: bold;
                    color: #667eea;
                    margin-bottom: 0.5rem;
                }
                
                .stat-label {
                    color: #666;
                    font-size: 0.9rem;
                }
                
                .modules-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                    gap: 1.5rem;
                }
                
                .module-card {
                    background: white;
                    padding: 1.5rem;
                    border-radius: 10px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                    border: 2px solid #e9ecef;
                    transition: transform 0.3s, border-color 0.3s;
                }
                
                .module-card:hover {
                    transform: translateY(-5px);
                    border-color: #667eea;
                }
                
                .module-icon {
                    font-size: 2.5rem;
                    margin-bottom: 1rem;
                }
                
                .module-title {
                    font-size: 1.2rem;
                    font-weight: 600;
                    margin-bottom: 0.5rem;
                    color: #333;
                }
                
                .module-description {
                    color: #666;
                    margin-bottom: 1rem;
                    line-height: 1.5;
                }
                
                .module-link {
                    display: inline-block;
                    padding: 0.5rem 1rem;
                    background: #667eea;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                    transition: background 0.3s;
                }
                
                .module-link:hover {
                    background: #5a6fd8;
                }
                
                .development-notice {
                    background: #fff3cd;
                    border: 1px solid #ffeaa7;
                    color: #856404;
                    padding: 1rem;
                    border-radius: 5px;
                    margin-top: 2rem;
                    text-align: center;
                }
            </style>
        </head>
        <body>
            <!-- Header -->
            <header class="header">
                <div class="header-content">
                    <div class="logo">
                        <h1>🏭 Sistema Integral Mobiliario</h1>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?= htmlspecialchars($data['usuario']['nombre']) ?></div>
                        <div class="user-role"><?= htmlspecialchars($data['usuario']['rol']) ?></div>
                    </div>
                </div>
            </header>
            
            <!-- Navigation -->
            <nav class="nav">
                <ul class="nav-links">
                    <li><a href="<?= $data['base_url'] ?>/index.php?url=dashboard">📊 Dashboard</a></li>
                    <li><a href="<?= $data['base_url'] ?>/index.php?url=rh/empleados">👥 Empleados</a></li>
                    <li><a href="#">🏭 Producción</a></li>
                    <li><a href="#">📦 Inventario</a></li>
                    <li><a href="#">💰 Ventas</a></li>
                    <li><a href="<?= $data['base_url'] ?>/index.php?url=logout" class="logout">🚪 Cerrar Sesión</a></li>
                </ul>
            </nav>
            
            <!-- Main Content -->
            <main class="main-content">
                <!-- Welcome Section -->
                <section class="welcome-section">
                    <h2>¡Bienvenido al Sistema Integral Mobiliario! 🎉</h2>
                    <p>Estás accediendo al panel de control principal del sistema.</p>
                </section>
                
                <!-- Statistics -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-number">0</div>
                        <div class="stat-label">Empleados Activos</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🏭</div>
                        <div class="stat-number">0</div>
                        <div class="stat-label">Órdenes Producción</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">💰</div>
                        <div class="stat-number">0</div>
                        <div class="stat-label">Ventas del Mes</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📦</div>
                        <div class="stat-number">0</div>
                        <div class="stat-label">Productos en Stock</div>
                    </div>
                </div>
                
                <!-- Modules Grid -->
                <div class="modules-grid">
                    <div class="module-card">
                        <div class="module-icon">👥</div>
                        <h3 class="module-title">Recursos Humanos</h3>
                        <p class="module-description">
                            Gestión completa de empleados, nóminas, permisos y portal del empleado.
                        </p>
                        <a href="<?= $data['base_url'] ?>/index.php?url=rh/empleados" class="module-link">
                            Gestionar Empleados
                        </a>
                    </div>
                    
                    <div class="module-card">
                        <div class="module-icon">🏭</div>
                        <h3 class="module-title">Producción Madera</h3>
                        <p class="module-description">
                            Control de órdenes de producción, seguimiento y gestión de materiales.
                        </p>
                        <a href="#" class="module-link">
                            Próximamente
                        </a>
                    </div>
                    
                    <div class="module-card">
                        <div class="module-icon">📦</div>
                        <h3 class="module-title">Inventario</h3>
                        <p class="module-description">
                            Gestión de stock, entradas/salidas y control de materiales.
                        </p>
                        <a href="#" class="module-link">
                            Próximamente
                        </a>
                    </div>
                    
                    <div class="module-card">
                        <div class="module-icon">💰</div>
                        <h3 class="module-title">Ventas & CRM</h3>
                        <p class="module-description">
                            Gestión de clientes, ventas, cotizaciones y seguimiento comercial.
                        </p>
                        <a href="#" class="module-link">
                            Próximamente
                        </a>
                    </div>
                    
                    <div class="module-card">
                        <div class="module-icon">📊</div>
                        <h3 class="module-title">Reportes</h3>
                        <p class="module-description">
                            Reportes ejecutivos, estadísticas y análisis de negocio.
                        </p>
                        <a href="#" class="module-link">
                            Próximamente
                        </a>
                    </div>
                    
                    <div class="module-card">
                        <div class="module-icon">⚙️</div>
                        <h3 class="module-title">Configuración</h3>
                        <p class="module-description">
                            Configuración del sistema, usuarios y parámetros generales.
                        </p>
                        <a href="#" class="module-link">
                            Próximamente
                        </a>
                    </div>
                </div>
                
                <!-- Development Notice -->
                <div class="development-notice">
                    <strong>🚧 Sistema en Desarrollo</strong>
                    <p>Esta es una versión temporal del dashboard. Los módulos se irán implementando progresivamente.</p>
                    <p><strong>Credenciales de prueba:</strong> admin / admin123</p>
                </div>
            </main>
            
            <script>
                // Efectos simples de interacción
                document.addEventListener('DOMContentLoaded', function() {
                    // Animación de números (simulada)
                    const statNumbers = document.querySelectorAll('.stat-number');
                    statNumbers.forEach(stat => {
                        let target = parseInt(stat.textContent);
                        let current = 0;
                        let increment = target / 50;
                        
                        let timer = setInterval(() => {
                            current += increment;
                            if (current >= target) {
                                current = target;
                                clearInterval(timer);
                            }
                            stat.textContent = Math.floor(current);
                        }, 30);
                    });
                });
            </script>
        </body>
        </html>
        <?php
    }
}