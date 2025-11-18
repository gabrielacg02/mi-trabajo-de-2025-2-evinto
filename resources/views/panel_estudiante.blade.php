<!-- Panel de Estudiante HTML Estático -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Estudiante - Seguridad Universitaria</title>
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
    @include('layouts.student-sidebar')

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <h2 class="mb-4">Panel de Estudiante</h2>
            
            <!-- Estadísticas -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card stat-card">
                        <div class="stat-number">2</div>
                        <div class="stat-label">Incidentes Reportados</div>
                        <i class="fas fa-exclamation-triangle mt-3 text-warning fs-4"></i>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card">
                        <div class="stat-number">1</div>
                        <div class="stat-label">Objetos Reportados</div>
                        <i class="fas fa-box-open mt-3 text-info fs-4"></i>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card">
                        <div class="stat-number">3</div>
                        <div class="stat-label">Notificaciones</div>
                        <i class="fas fa-bell mt-3 text-success fs-4"></i>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <!-- Incidentes Reportados -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-exclamation-triangle me-2"></i> Mis Incidentes</span>
                            <a href="#" class="btn btn-sm btn-primary">Ver Todos</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Tipo</th>
                                            <th>Estado</th>
                                            <th>Fecha</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>#1</td>
                                            <td>Robo</td>
                                            <td>
                                                <span class="badge bg-warning">Pendiente</span>
                                            </td>
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
                                            <td>
                                                <span class="badge bg-info">En revisión</span>
                                            </td>
                                            <td>14/01/2024</td>
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
                
                <!-- Objetos Reportados -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-box-open me-2"></i> Mis Objetos</span>
                            <a href="#" class="btn btn-sm btn-primary">Ver Todos</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Tipo</th>
                                            <th>Estado</th>
                                            <th>Fecha</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>#1</td>
                                            <td>Mochila</td>
                                            <td>
                                                <span class="badge bg-info">Encontrado</span>
                                            </td>
                                            <td>14/01/2024</td>
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
            </div>
            
            <div class="row mt-4">
                <!-- Notificaciones -->
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-bell me-2"></i> Notificaciones</span>
                            <a href="#" class="btn btn-sm btn-primary">Ver Todas</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <a href="#" class="list-group-item list-group-item-action notification-item unread">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">Tu reporte ha sido recibido</h6>
                                        <small class="notification-time">10:30</small>
                                    </div>
                                    <p class="mb-1">Hemos recibido tu reporte de incidente y lo estamos revisando...</p>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action notification-item unread">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">Objeto encontrado</h6>
                                        <small class="notification-time">16:20</small>
                                    </div>
                                    <p class="mb-1">Se encontró un objeto que coincide con tu reporte...</p>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action notification-item unread">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">Actualización de estado</h6>
                                        <small class="notification-time">09:15</small>
                                    </div>
                                    <p class="mb-1">Tu incidente ha cambiado de estado a "En revisión"...</p>
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