<?php
$usuario = $usuario ?? ['nombre_completo' => '', 'id_rol' => 5, 'numero_documento' => ''];
$error = $error ?? '';
$objetos = $objetos ?? [];
$notificaciones = $notificaciones ?? ['total' => 0];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Objetos Perdidos - Seguridad Universitaria</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --info-color: #2980b9;
        }
        
        body {
            font-family: 'Open Sans', sans-serif;
            color: #333;
            background-color: #f8f9fa;
        }
        
        .sidebar {
            background-color: var(--primary-color);
            color: white;
            height: 100vh;
            position: fixed;
            width: 250px;
            transition: all 0.3s;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 20px;
            background-color: rgba(0,0,0,0.2);
        }
        
        .sidebar ul.components {
            padding: 20px 0;
        }
        
        .sidebar ul li a {
            padding: 10px 20px;
            color: rgba(255,255,255,0.8);
            display: block;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar ul li a:hover {
            color: white;
            background-color: rgba(0,0,0,0.2);
        }
        
        .sidebar ul li.active > a {
            color: white;
            background-color: rgba(0,0,0,0.2);
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .card:hover {
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            transform: translateY(-5px);
        }
        
        .badge-primary {
            background-color: var(--secondary-color);
        }
        
        .badge-success {
            background-color: var(--success-color);
        }
        
        .badge-warning {
            background-color: var(--warning-color);
        }
        
        .badge-danger {
            background-color: var(--accent-color);
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            padding: 15px;
            background-color: rgba(0,0,0,0.1);
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .user-profile img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
        }
        
        .user-info h5 {
            margin-bottom: 0;
        }
        
        .user-info p {
            margin-bottom: 0;
            color: rgba(255,255,255,0.7);
            font-size: 0.9em;
        }
        
        .object-card {
            transition: all 0.3s;
            border-left: 4px solid var(--secondary-color);
        }
        
        .object-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        
        .object-card.perdido {
            border-left-color: var(--accent-color);
        }
        
        .object-card.encontrado {
            border-left-color: var(--success-color);
        }
        
        .object-card.devuelto {
            border-left-color: var(--info-color);
        }
        
        .search-box {
            position: relative;
            margin-bottom: 20px;
        }
        
        .search-box input {
            padding-left: 40px;
            border-radius: 20px;
            border: 1px solid #ddd;
        }
        
        .search-box i {
            position: absolute;
            left: 15px;
            top: 12px;
            color: #999;
        }
        
        .filter-buttons .btn {
            margin-right: 5px;
            margin-bottom: 5px;
            border-radius: 20px;
        }
        
        .filter-buttons .btn.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
            }
            
            .sidebar.active {
                margin-left: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .main-content.active {
                margin-left: 250px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Seguridad Universitaria</h3>
        </div>
        
        <div class="user-profile">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($usuario['nombre_completo']) ?>&background=3498db&color=fff" alt="Perfil">
            <div class="user-info">
                <h5><?= htmlspecialchars($usuario['nombre_completo']) ?></h5>
                <p>Administrador</p>
            </div>
        </div>
        
        <ul class="list-unstyled components">
            <li>
                <a href="<?php echo route('panel.administrador'); ?>"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
            </li>
            <li>
                <a href="#usuariosSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                    <i class="fas fa-users me-2"></i> Usuarios
                </a>
                <ul class="collapse list-unstyled" id="usuariosSubmenu">
                    <li><a href="<?php echo route('admin.usuarios'); ?>"><i class="fas fa-list me-2"></i> Listar Usuarios</a></li>
                    <li><a href="<?php echo route('admin.usuarios.registrar'); ?>"><i class="fas fa-user-plus me-2"></i> Registrar Usuario</a></li>
                </ul>
            </li>
            <li>
                <a href="#reportesSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                    <i class="fas fa-exclamation-triangle me-2"></i> Reportes
                </a>
                <ul class="collapse list-unstyled" id="reportesSubmenu">
                    <li><a href="<?php echo route('reportes.incidentes'); ?>"><i class="fas fa-list me-2"></i> Incidentes</a></li>
                    <li><a href="<?php echo route('reportes.pendientes'); ?>"><i class="fas fa-clock me-2"></i> Pendientes</a></li>
                </ul>
            </li>
            <li class="active">
                <a href="#objetosSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                    <i class="fas fa-box-open me-2"></i> Objetos
                </a>
                <ul class="collapse list-unstyled show" id="objetosSubmenu">
                    <li><a href="<?php echo route('objetos.index'); ?>"><i class="fas fa-list me-2"></i> Objetos</a></li>
                    <li><a href="<?php echo route('objetos.estudiante.reportar'); ?>"><i class="fas fa-clock me-2"></i> Reportar objeto</a></li>
                </ul>
            </li>
            <li>
                <a href="<?php echo route('admin.accesos'); ?>"><i class="fas fa-door-open me-2"></i> Control de Accesos</a>
            </li>
            <li>
                <a href="<?php echo route('admin.auditoria'); ?>"><i class="fas fa-clipboard-list me-2"></i> Auditoría</a>
            </li>
            <li>
                <a href="<?php echo route('admin.configuracion'); ?>"><i class="fas fa-cog me-2"></i> Configuración</a>
            </li>
            <li>
                <a href="<?php echo route('logout'); ?>"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-box-open me-2"></i> Objetos Perdidos</h2>
                <a href="<?php echo route('objetos.estudiante.reportar'); ?>" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i> Reportar Objeto
                </a>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" class="form-control" id="searchInput" placeholder="Buscar objetos...">
                    </div>
                </div>
                <div class="col-md-6 filter-buttons">
                    <button class="btn btn-outline-primary filter-btn active" data-filter="all">Todos</button>
                    <button class="btn btn-outline-danger filter-btn" data-filter="perdido">Perdidos</button>
                    <button class="btn btn-outline-success filter-btn" data-filter="encontrado">Encontrados</button>
                    <button class="btn btn-outline-info filter-btn" data-filter="devuelto">Devueltos</button>
                </div>
            </div>
            
            <div class="row" id="objectsContainer">
                <?php if (empty($objetos)): ?>
                    <div class="col-12">
                        <div class="alert alert-info">No hay objetos reportados</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($objetos as $objeto): ?>
                        <div class="col-md-6 col-lg-4 mb-4 object-item" data-status="<?= $objeto['estado'] ?>">
                            <div class="card object-card <?= $objeto['estado'] ?>">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="card-title mb-0"><?= htmlspecialchars($objeto['tipo_objeto']) ?></h5>
                                        <span class="badge <?= $objeto['estado'] == 'perdido' ? 'bg-danger' : ($objeto['estado'] == 'encontrado' ? 'bg-success' : 'bg-info') ?>">
                                            <?= ucfirst($objeto['estado']) ?>
                                        </span>
                                    </div>
                                    <p class="card-text text-muted small mb-2">
                                        <i class="fas fa-map-marker-alt me-2"></i>
                                        <?= htmlspecialchars($objeto['ubicacion_perdida']) ?>
                                    </p>
                                    <p class="card-text text-muted small mb-2">
                                        <i class="fas fa-calendar-alt me-2"></i>
                                        <?= date('d/m/Y', strtotime($objeto['fecha_perdida'])) ?>
                                    </p>
                                    <p class="card-text mb-3">
                                        <?= htmlspecialchars(substr($objeto['descripcion'], 0, 100)) ?>...
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <?= htmlspecialchars($objeto['reportado_por']) ?>
                                        </small>
                                        <a href="<?php echo route('objetos.detalle'); ?>" class="btn btn-sm btn-primary">
                                            Ver detalles
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script>
        // Filtrar objetos
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                aplicarFiltros();
            });
        });
        
        // Buscar objetos
        document.getElementById('searchInput').addEventListener('keyup', aplicarFiltros);
        
        function aplicarFiltros() {
            const filter = document.querySelector('.filter-btn.active').getAttribute('data-filter');
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            
            const items = document.querySelectorAll('.object-item');
            
            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                const status = item.getAttribute('data-status');
                
                // Aplicar ambos filtros (estado y búsqueda)
                const pasaFiltroEstado = filter === 'all' || status === filter;
                const pasaFiltroBusqueda = text.includes(searchTerm);
                
                if (pasaFiltroEstado && pasaFiltroBusqueda) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }
        
        // Toggle sidebar in mobile
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            
            // You can add a button to toggle sidebar in mobile
            // document.querySelector('.sidebar-toggle').addEventListener('click', toggleSidebar);
        });
    </script>
</body>
</html>