<!-- Panel de Notificaciones HTML Estático -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones - Panel de Celador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
            padding: 15px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s;
            position: relative;
        }
        
        .notification-item:hover {
            background-color: #f8f9fa;
        }
        
        .notification-item.unread {
            background-color: #f0f7ff;
            border-left: 4px solid var(--secondary-color);
        }
        
        .notification-time {
            font-size: 0.8em;
            color: #999;
        }
        
        .notification-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 0.7em;
        }
        
        .notification-actions {
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
        }
        
        .notification-link {
            color: var(--secondary-color);
            text-decoration: none;
        }
        
        .notification-link:hover {
            text-decoration: underline;
        }
        
        .mark-all-btn {
            margin-bottom: 20px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 15px;
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Global -->
    @include('layouts.guard-sidebar')

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-bell me-2"></i> Notificaciones</h2>
                <button type="button" class="btn btn-primary btn-sm mark-all-btn" onclick="marcarTodasLeidas()">
                    <i class="fas fa-check-circle me-1"></i> Marcar todas como leídas
                </button>
            </div>
            
            <!-- Estadísticas -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="stat-number">12</div>
                        <div class="stat-label">Registros Hoy</div>
                        <i class="fas fa-door-open mt-3 text-primary fs-4"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="stat-number">3</div>
                        <div class="stat-label">Incidentes Pendientes</div>
                        <i class="fas fa-exclamation-triangle mt-3 text-warning fs-4"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="stat-number">8</div>
                        <div class="stat-label">Objetos Perdidos</div>
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
            </div>
            
            <!-- Lista de Notificaciones -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list me-2"></i> Todas las notificaciones
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item list-group-item-action notification-item unread">
                            <span class="badge bg-primary notification-badge">Nueva</span>
                            
                            <div class="d-flex justify-content-between">
                                <h5 class="mb-1">Nuevo incidente reportado</h5>
                                <small class="notification-time">15/01/2024 14:30</small>
                            </div>
                            <p class="mb-2">Se ha reportado un nuevo incidente de robo en el edificio A. Revisar detalles.</p>
                            
                            <div class="notification-actions">
                                <div>
                                    <a href="#" class="notification-link">
                                        <i class="fas fa-exclamation-triangle me-1"></i> Ver incidente
                                    </a>
                                </div>
                                
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="marcarLeida(this)">
                                    <i class="fas fa-check me-1"></i> Marcar como leída
                                </button>
                            </div>
                        </div>
                        
                        <div class="list-group-item list-group-item-action notification-item unread">
                            <span class="badge bg-primary notification-badge">Nueva</span>
                            
                            <div class="d-flex justify-content-between">
                                <h5 class="mb-1">Objeto encontrado</h5>
                                <small class="notification-time">15/01/2024 13:45</small>
                            </div>
                            <p class="mb-2">Se ha encontrado una calculadora en el laboratorio de computación.</p>
                            
                            <div class="notification-actions">
                                <div>
                                    <a href="#" class="notification-link">
                                        <i class="fas fa-box-open me-1"></i> Ver objeto
                                    </a>
                                </div>
                                
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="marcarLeida(this)">
                                    <i class="fas fa-check me-1"></i> Marcar como leída
                                </button>
                            </div>
                        </div>
                        
                        <div class="list-group-item list-group-item-action notification-item">
                            <div class="d-flex justify-content-between">
                                <h5 class="mb-1">Acceso registrado</h5>
                                <small class="notification-time">15/01/2024 12:20</small>
                            </div>
                            <p class="mb-2">Se registró la entrada del estudiante Juan Pérez al edificio B.</p>
                            
                            <div class="notification-actions">
                                <div>
                                    <a href="#" class="notification-link">
                                        <i class="fas fa-door-open me-1"></i> Ver registro
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="list-group-item list-group-item-action notification-item unread">
                            <span class="badge bg-primary notification-badge">Nueva</span>
                            
                            <div class="d-flex justify-content-between">
                                <h5 class="mb-1">Recordatorio de seguridad</h5>
                                <small class="notification-time">15/01/2024 10:15</small>
                            </div>
                            <p class="mb-2">Recordatorio: Verificar que todas las puertas estén cerradas al final del turno.</p>
                            
                            <div class="notification-actions">
                                <div>
                                    <a href="#" class="notification-link">
                                        <i class="fas fa-info-circle me-1"></i> Ver detalles
                                    </a>
                                </div>
                                
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="marcarLeida(this)">
                                    <i class="fas fa-check me-1"></i> Marcar como leída
                                </button>
                            </div>
                        </div>
                        
                        <div class="list-group-item list-group-item-action notification-item">
                            <div class="d-flex justify-content-between">
                                <h5 class="mb-1">Objeto devuelto</h5>
                                <small class="notification-time">14/01/2024 16:30</small>
                            </div>
                            <p class="mb-2">El teléfono iPhone 12 Pro Max ha sido devuelto a su dueño.</p>
                            
                            <div class="notification-actions">
                                <div>
                                    <a href="#" class="notification-link">
                                        <i class="fas fa-box-open me-1"></i> Ver objeto
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function marcarLeida(button) {
            const notificationItem = button.closest('.notification-item');
            const badge = notificationItem.querySelector('.notification-badge');
            
            // Remover clase unread y badge
            notificationItem.classList.remove('unread');
            if (badge) {
                badge.remove();
            }
            
            // Remover el botón
            button.remove();
            
            alert('Notificación marcada como leída');
        }
        
        function marcarTodasLeidas() {
            const unreadNotifications = document.querySelectorAll('.notification-item.unread');
            
            unreadNotifications.forEach(item => {
                item.classList.remove('unread');
                const badge = item.querySelector('.notification-badge');
                if (badge) {
                    badge.remove();
                }
                const button = item.querySelector('button[onclick*="marcarLeida"]');
                if (button) {
                    button.remove();
                }
            });
            
            alert('Todas las notificaciones han sido marcadas como leídas');
        }
    </script>
</body>
</html>