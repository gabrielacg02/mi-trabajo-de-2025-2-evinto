<!-- Panel de Docente HTML Estático -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Docente - Seguridad Universitaria</title>
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
        
        @media (max-width: 768px) {
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
    @include('layouts.teacher-sidebar')

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <h2 class="mb-4">Panel de Docente</h2>
            
            <!-- Estadísticas -->
            <div class="row">
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="stat-number">8</div>
                        <div class="stat-label">Incidentes Reportados</div>
                        <i class="fas fa-exclamation-triangle mt-3 text-warning fs-4"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="stat-number">3</div>
                        <div class="stat-label">Objetos Reportados</div>
                        <i class="fas fa-box-open mt-3 text-info fs-4"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="stat-number">5</div>
                        <div class="stat-label">Notificaciones</div>
                        <i class="fas fa-bell mt-3 text-success fs-4"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="stat-number">12</div>
                        <div class="stat-label">Estudiantes Asignados</div>
                        <i class="fas fa-users mt-3 text-primary fs-4"></i>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <!-- Incidentes Recientes -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-exclamation-triangle me-2"></i> Incidentes Recientes</span>
                            <a href="#" class="btn btn-sm btn-primary">Ver Todos</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Tipo</th>
                                            <th>Estudiante</th>
                                            <th>Estado</th>
                                            <th>Fecha</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>#1</td>
                                            <td>Robo</td>
                                            <td>Juan Pérez</td>
                                            <td><span class="badge bg-warning">Pendiente</span></td>
                                            <td>15/01/2024</td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>#2</td>
                                            <td>Vandalismo</td>
                                            <td>María García</td>
                                            <td><span class="badge bg-info">En revisión</span></td>
                                            <td>14/01/2024</td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>#3</td>
                                            <td>Agresión</td>
                                            <td>Carlos López</td>
                                            <td><span class="badge bg-success">Resuelto</span></td>
                                            <td>13/01/2024</td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i>
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
                                <span class="badge bg-danger ms-2">2 nuevas</span>
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
                                    <p class="mb-1">Un estudiante ha reportado un nuevo incidente...</p>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action notification-item unread">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">Objeto encontrado</h6>
                                        <small class="notification-time">16:20</small>
                                    </div>
                                    <p class="mb-1">Se encontró un objeto perdido en tu aula...</p>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action notification-item">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">Reunión programada</h6>
                                        <small class="notification-time">09:15</small>
                                    </div>
                                    <p class="mb-1">Tienes una reunión programada para mañana...</p>
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
    </script>
</body>
</html>