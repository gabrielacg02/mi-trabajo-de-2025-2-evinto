<?php ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador - Seguridad Universitaria</title>
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
        
        .stat-card {
            text-align: center;
            padding: 20px;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
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
        
        .notification-item {
            padding: 10px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        
        .notification-item:hover {
            background-color: #f8f9fa;
        }
        
        .notification-item.unread {
            background-color: #f0f7ff;
        }
        
        .notification-time {
            font-size: 0.8em;
            color: #999;
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 0.7rem;
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
    <!-- Sidebar Global -->
    @include('layouts.admin-sidebar')

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <h2 class="mb-4">Panel de Administración</h2>
            
            <!-- Estadísticas -->
            <div class="row">
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="stat-number">150</div>
                        <div class="stat-label">Usuarios Registrados</div>
                        <i class="fas fa-users mt-3 text-primary fs-4"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="stat-number">25</div>
                        <div class="stat-label">Incidentes Reportados</div>
                        <i class="fas fa-exclamation-triangle mt-3 text-warning fs-4"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="stat-number">12</div>
                        <div class="stat-label">Objetos Perdidos</div>
                        <i class="fas fa-box-open mt-3 text-info fs-4"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="stat-number">45</div>
                        <div class="stat-label">Ingresos Hoy</div>
                        <i class="fas fa-door-open mt-3 text-success fs-4"></i>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <!-- Usuarios Recientes -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-users me-2"></i> Usuarios Recientes</span>
                            <a href="#" class="btn btn-sm btn-primary">Ver Todos</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Documento</th>
                                            <th>Nombre</th>
                                            <th>Correo</th>
                                            <th>Rol</th>
                                            <th>Estado</th>
                                            <th>Fecha</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>CC 12345678</td>
                                            <td>Juan Pérez García</td>
                                            <td>juan.perez@universidad.edu</td>
                                            <td>Estudiante</td>
                                            <td>
                                                <span class="badge bg-success">Activo</span>
                                            </td>
                                            <td>15/01/2024</td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>CC 87654321</td>
                                            <td>María González López</td>
                                            <td>maria.gonzalez@universidad.edu</td>
                                            <td>Docente</td>
                                            <td>
                                                <span class="badge bg-success">Activo</span>
                                            </td>
                                            <td>14/01/2024</td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>CC 11223344</td>
                                            <td>Carlos Rodríguez Silva</td>
                                            <td>carlos.rodriguez@universidad.edu</td>
                                            <td>Administrativo</td>
                                            <td>
                                                <span class="badge bg-warning">Inactivo</span>
                                            </td>
                                            <td>13/01/2024</td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Notificaciones -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fas fa-bell me-2"></i> Notificaciones
                                <span class="badge bg-danger ms-2">3 nuevas</span>
                            </span>
                            <a href="#" class="btn btn-sm btn-primary">Ver Todas</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <a href="#" class="list-group-item list-group-item-action notification-item unread">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">Nuevo incidente reportado</h6>
                                        <small class="notification-time">10:30</small>
                                    </div>
                                    <p class="mb-1">Se ha reportado un nuevo incidente de robo en el edificio A...</p>
                                    <small class="text-muted">Incidente</small>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action notification-item unread">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">Objeto encontrado</h6>
                                        <small class="notification-time">16:20</small>
                                    </div>
                                    <p class="mb-1">Se encontró un objeto perdido en la cafetería...</p>
                                    <small class="text-muted">Objeto</small>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action notification-item unread">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">Usuario registrado</h6>
                                        <small class="notification-time">09:15</small>
                                    </div>
                                    <p class="mb-1">Un nuevo usuario se ha registrado en el sistema...</p>
                                    <small class="text-muted">Usuario</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom JS -->
    <script>
        // Toggle sidebar in mobile
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            
            // Toggle sidebar when button clicked (you can add a button if needed)
            function toggleSidebar() {
                sidebar.classList.toggle('active');
                mainContent.classList.toggle('active');
            }
            
            // You can add a button to toggle sidebar in mobile
            // document.querySelector('.sidebar-toggle').addEventListener('click', toggleSidebar);
        });

        // Marcar notificación como leída al hacer clic
        $(document).ready(function() {
            $('.notification-item').click(function() {
                const notificationId = $(this).attr('href').split('=')[1];
                
                // Enviar petición AJAX para marcar como leída
                $.ajax({
                    url: 'marcar_notificacion_leida.php',
                    method: 'POST',
                    data: { id_notificacion: notificationId },
                    success: function(response) {
                        if (response.success) {
                            // Actualizar contador de notificaciones no leídas
                            const badge = $('.notification-badge');
                            const currentCount = parseInt(badge.text());
                            
                            if (currentCount > 1) {
                                badge.text(currentCount - 1);
                            } else {
                                badge.remove();
                            }
                            
                            // Actualizar contador en el encabezado
                            const newBadge = $('.badge.bg-danger.ms-2');
                            if (newBadge.length) {
                                const newCount = parseInt(newBadge.text().split(' ')[0]);
                                if (newCount > 1) {
                                    newBadge.text((newCount - 1) + ' nuevas');
                                } else {
                                    newBadge.remove();
                                }
                            }
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>