<?php
$usuario = $usuario ?? ['nombre_completo' => '', 'id_rol' => 1, 'numero_documento' => ''];
$error = $error ?? '';
$incidentes = $incidentes ?? [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes de Incidentes - Seguridad Universitaria</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        
        .sidebar ul ul a {
            padding-left: 30px;
            font-size: 0.9em;
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
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            font-weight: 600;
        }
        
        .badge-severidad {
            padding: 5px 10px;
            font-weight: 500;
            border-radius: 20px;
        }
        
        .severidad-baja {
            background-color: #d4edda;
            color: #155724;
        }
        
        .severidad-media {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .severidad-alta {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .severidad-critica {
            background-color: #721c24;
            color: white;
        }
        
        .badge-estado {
            padding: 5px 10px;
            font-weight: 500;
            border-radius: 20px;
        }
        
        .estado-reportado {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .estado-en_revision {
            background-color: #ffeeba;
            color: #856404;
        }
        
        .estado-resuelto {
            background-color: #d4edda;
            color: #155724;
        }
        
        .estado-archivado {
            background-color: #f8d7da;
            color: #721c24;
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
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table thead th {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }
        
        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }
        
        .btn-outline-primary {
            border-color: var(--secondary-color);
            color: var(--secondary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .btn-danger {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
            border-color: #c0392b;
        }
        
        .search-box {
            position: relative;
            margin-bottom: 20px;
        }
        
        .search-box input {
            padding-left: 40px;
            border-radius: 20px;
        }
        
        .search-box i {
            position: absolute;
            left: 15px;
            top: 10px;
            color: #6c757d;
        }
        
        .filter-buttons .btn {
            margin-right: 5px;
            margin-bottom: 5px;
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
                <p><?= $usuario['id_rol'] == 5 ? 'Administrador' : ($usuario['id_rol'] == 4 ? 'Celador' : ($usuario['id_rol'] == 3 ? 'Personal Administrativo' : ($usuario['id_rol'] == 2 ? 'Docente' : 'Estudiante'))) ?></p>
            </div>
        </div>
        
        <ul class="list-unstyled components">
            <li>
                <a href="<?php echo route('panel.' . ($usuario['id_rol'] == 5 ? 'administrador' : ($usuario['id_rol'] == 4 ? 'celador' : ($usuario['id_rol'] == 3 ? 'administrativo' : ($usuario['id_rol'] == 2 ? 'docente' : 'estudiante'))))); ?>">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>
            <li class="active">
                <a href="#reportesSubmenu" data-bs-toggle="collapse" aria-expanded="true" class="dropdown-toggle">
                    <i class="fas fa-exclamation-triangle me-2"></i> Reportes
                </a>
                <ul class="collapse show list-unstyled" id="reportesSubmenu">
                    <li class="active"><a href="<?php echo route('reportes.incidentes'); ?>"><i class="fas fa-list me-2"></i> Incidentes</a></li>
                    <?php if($usuario['id_rol'] == 5 || $usuario['id_rol'] == 4): ?>
                        <li><a href="<?php echo route('reportes.formulario'); ?>"><i class="fas fa-clock me-2"></i> Reportar incidente</a></li>
                    <?php endif; ?>
                </ul>
            </li>
            <li>
                <a href="<?php echo route('objetos.estudiante'); ?>"><i class="fas fa-box-open me-2"></i> Objetos Perdidos</a>
            </li>
            <?php if($usuario['id_rol'] == 4 || $usuario['id_rol'] == 5): ?>
                <li>
                    <a href="<?php echo route('admin.accesos'); ?>"><i class="fas fa-door-open me-2"></i> Control de Accesos</a>
                </li>
            <?php endif; ?>
            <li>
                <a href="<?php echo route('logout'); ?>"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Reportes de Incidentes</h2>
                <?php if($usuario['id_rol'] != 4): ?>
                    <a href="<?php echo route('reportes.formulario'); ?>" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Nuevo Reporte
                    </a>
                <?php endif; ?>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-list me-2"></i> Listado de Incidentes</span>
                    <div class="d-flex">
                        <div class="search-box me-3">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" class="form-control" placeholder="Buscar...">
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-filter me-2"></i>Filtrar
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                                <li><a class="dropdown-item filter-item" href="#" data-filter="all">Todos</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item filter-item" href="#" data-filter="reportado">Reportado</a></li>
                                <li><a class="dropdown-item filter-item" href="#" data-filter="en_revision">En Revisión</a></li>
                                <li><a class="dropdown-item filter-item" href="#" data-filter="resuelto">Resuelto</a></li>
                                <li><a class="dropdown-item filter-item" href="#" data-filter="archivado">Archivado</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tipo</th>
                                    <th>Severidad</th>
                                    <th>Ubicación</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <?php if($usuario['id_rol'] == 5 || $usuario['id_rol'] == 4): ?>
                                        <th>Reportado por</th>
                                    <?php endif; ?>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($incidentes as $incidente): ?>
                                    <tr data-status="<?= $incidente['estado'] ?>">
                                        <td>#<?= $incidente['id_reporte'] ?></td>
                                        <td><?= htmlspecialchars($incidente['tipo_incidente']) ?></td>
                                        <td>
                                            <span class="badge badge-severidad severidad-<?= strtolower($incidente['severidad']) ?>">
                                                <?= ucfirst($incidente['severidad']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($incidente['ubicacion']) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($incidente['fecha_incidente'])) ?></td>
                                        <td>
                                            <span class="badge badge-estado estado-<?= $incidente['estado'] ?>">
                                                <?= ucfirst(str_replace('_', ' ', $incidente['estado'])) ?>
                                            </span>
                                        </td>
                                        <?php if($usuario['id_rol'] == 5 || $usuario['id_rol'] == 4): ?>
                                            <td><?= htmlspecialchars($incidente['reportado_por']) ?></td>
                                        <?php endif; ?>
                                        <td>
                                            <a href="reporte_detalle.php?id=<?= $incidente['id_reporte'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if($usuario['id_rol'] == 5 || $usuario['id_rol'] == 4 || $incidente['numero_documento'] == $usuario['numero_documento']): ?>
                                                <a href="reporte_formulario.php?id=<?= $incidente['id_reporte'] ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($incidentes)): ?>
                                    <tr>
                                        <td colspan="<?= ($usuario['id_rol'] == 5 || $usuario['id_rol'] == 4) ? 8 : 7 ?>" class="text-center">No hay incidentes reportados</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script>
        // Filtrado de la tabla
        document.addEventListener('DOMContentLoaded', function() {
            // Filtro por búsqueda
            const searchInput = document.getElementById('searchInput');
            searchInput.addEventListener('keyup', function() {
                const filter = this.value.toLowerCase();
                const rows = document.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(filter)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
            
            // Filtro por estado
            const filterItems = document.querySelectorAll('.filter-item');
            filterItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const filter = this.getAttribute('data-filter');
                    const rows = document.querySelectorAll('tbody tr');
                    
                    rows.forEach(row => {
                        if (filter === 'all' || row.getAttribute('data-status') === filter) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>