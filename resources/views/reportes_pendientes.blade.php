<?php
$usuario = $usuario ?? ['nombre_completo' => '', 'id_rol' => 5, 'numero_documento' => ''];
$error = $error ?? '';
$reportes_pendientes = $reportes_pendientes ?? [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes Pendientes - Seguridad Universitaria</title>
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
        
        .badge-pendiente {
            background-color: #fff3cd;
            color: #856404;
            padding: 5px 10px;
            font-weight: 500;
            border-radius: 20px;
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
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
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
                <p><?= $usuario['id_rol'] == 5 ? 'Administrador' : 'Celador' ?></p>
            </div>
        </div>
        
        <ul class="list-unstyled components">
            <li>
                <a href="<?php echo route('panel.' . ($usuario['id_rol'] == 5 ? 'administrador' : 'celador')); ?>">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>
            <li class="active">
                <a href="#reportesSubmenu" data-bs-toggle="collapse" aria-expanded="true" class="dropdown-toggle">
                    <i class="fas fa-exclamation-triangle me-2"></i> Reportes
                </a>
                <ul class="collapse show list-unstyled" id="reportesSubmenu">
                    <li><a href="<?php echo route('reportes.incidentes'); ?>"><i class="fas fa-list me-2"></i> Incidentes</a></li>
                    <li class="active"><a href="<?php echo route('reportes.pendientes'); ?>"><i class="fas fa-clock me-2"></i> Pendientes</a></li>
                </ul>
            </li>
            <li>
                <a href="<?php echo route('objetos.index'); ?>"><i class="fas fa-box-open me-2"></i> Objetos Perdidos</a>
            </li>
            <li>
                <a href="<?php echo route('admin.accesos'); ?>"><i class="fas fa-door-open me-2"></i> Control de Accesos</a>
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
                <h2>Reportes Pendientes de Aprobación</h2>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-clock me-2"></i> Listado de Pendientes</span>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" class="form-control" placeholder="Buscar...">
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Reportado por</th>
                                    <th>Tipo</th>
                                    <th>Ubicación</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reportes_pendientes as $reporte): ?>
                                    <tr>
                                        <td>#<?= $reporte['id_reporte'] ?></td>
                                        <td><?= htmlspecialchars($reporte['reportado_por']) ?></td>
                                        <td><?= htmlspecialchars($reporte['tipo_incidente']) ?></td>
                                        <td><?= htmlspecialchars($reporte['ubicacion']) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($reporte['fecha_reporte'])) ?></td>
                                        <td>
                                            <span class="badge-pendiente">Pendiente</span>
                                        </td>
                                        <td>
                                            <a href="reporte_detalle.php?id=<?= $reporte['id_reporte'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button class="btn btn-sm btn-success btn-aprobar" data-id="<?= $reporte['id_reporte'] ?>">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger btn-rechazar" data-id="<?= $reporte['id_reporte'] ?>">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($reportes_pendientes)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No hay reportes pendientes de aprobación</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirmar Acción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que deseas <span id="actionText">aprobar</span> este reporte?
                    <div class="mt-3" id="comentarioContainer">
                        <label for="comentario" class="form-label">Comentario (opcional):</label>
                        <textarea class="form-control" id="comentario" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="confirmAction">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
            let currentReportId = null;
            let currentAction = null;
            
            // Filtro de búsqueda
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
            
            // Botones de aprobar/rechazar
            document.querySelectorAll('.btn-aprobar').forEach(btn => {
                btn.addEventListener('click', function() {
                    currentReportId = this.getAttribute('data-id');
                    currentAction = 'aprobar';
                    document.getElementById('actionText').textContent = 'aprobar';
                    document.getElementById('confirmAction').className = 'btn btn-success';
                    confirmModal.show();
                });
            });
            
            document.querySelectorAll('.btn-rechazar').forEach(btn => {
                btn.addEventListener('click', function() {
                    currentReportId = this.getAttribute('data-id');
                    currentAction = 'rechazar';
                    document.getElementById('actionText').textContent = 'rechazar';
                    document.getElementById('confirmAction').className = 'btn btn-danger';
                    confirmModal.show();
                });
            });
            
            // Confirmar acción
            document.getElementById('confirmAction').addEventListener('click', function() {
                const comentario = document.getElementById('comentario').value;
                
                fetch('aprobar_reporte.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id_reporte=${currentReportId}&accion=${currentAction}&comentario=${encodeURIComponent(comentario)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ocurrió un error al procesar la solicitud');
                });
                
                confirmModal.hide();
            });
            
            // Limpiar modal al cerrar
            document.getElementById('confirmModal').addEventListener('hidden.bs.modal', function () {
                document.getElementById('comentario').value = '';
                currentReportId = null;
                currentAction = null;
            });
        });
    </script>
</body>
</html>