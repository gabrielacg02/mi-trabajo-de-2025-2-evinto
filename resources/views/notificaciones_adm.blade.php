<?php
session_start();
require_once 'db_config.php';

// Verificar autenticación y rol
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['usuario']['id_rol'] !=  5) { // 4 = Celador
    header('Location: index.php');
    exit();
}

// Obtener datos del usuario
$usuario = $_SESSION['usuario'];
$numero_documento = $usuario['numero_documento'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Marcar notificación como leída si se especifica un ID
    if (isset($_GET['marcar_leida']) && is_numeric($_GET['marcar_leida'])) {
        $id_notificacion = $_GET['marcar_leida'];
        
        $stmt = $conn->prepare("UPDATE notificaciones SET leida = 1 WHERE id_notificacion = :id AND numero_documento = :numero_documento");
        $stmt->bindParam(':id', $id_notificacion);
        $stmt->bindParam(':numero_documento', $numero_documento);
        $stmt->execute();
        
        // Redirigir para evitar reenvío del formulario
        header('Location: celador_notificaciones.php');
        exit();
    }
    
    // Marcar todas como leídas
    if (isset($_POST['marcar_todas'])) {
        $stmt = $conn->prepare("UPDATE notificaciones SET leida = 1 WHERE numero_documento = :numero_documento AND leida = 0");
        $stmt->bindParam(':numero_documento', $numero_documento);
        $stmt->execute();
        
        // Registrar en auditoría
        $stmt = $conn->prepare("INSERT INTO auditoria (numero_documento, accion, tabla_afectada) VALUES (:numero_documento, 'Marcar todas las notificaciones como leídas', 'notificaciones')");
        $stmt->bindParam(':numero_documento', $numero_documento);
        $stmt->execute();
        
        header('Location: celador_notificaciones.php');
        exit();
    }
    
    // Obtener todas las notificaciones del usuario, ordenadas por fecha (más recientes primero)
    $stmt = $conn->prepare("
        SELECT n.*, 
               CASE 
                   WHEN n.tipo = 'incidente' THEN CONCAT('Incidente #', n.id_referencia)
                   WHEN n.tipo = 'objeto' THEN CONCAT('Objeto #', n.id_referencia)
                   ELSE 'Sistema'
               END AS referencia
        FROM notificaciones n
        WHERE n.numero_documento = :numero_documento
        ORDER BY n.fecha_hora DESC
    ");
    $stmt->bindParam(':numero_documento', $numero_documento);
    $stmt->execute();
    $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener estadísticas para el sidebar
    $stmt = $conn->prepare("SELECT * FROM vw_panel_celador WHERE numero_documento = :numero_documento");
    $stmt->bindParam(':numero_documento', $usuario['numero_documento']);
    $stmt->execute();
    $estadisticas = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Error de conexión: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones - Panel de Celador</title>
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
   
    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-bell me-2"></i> Notificaciones</h2>
                <?php if (!empty($notificaciones)): ?>
                    <form method="post" class="mark-all-btn">
                        <button type="submit" name="marcar_todas" class="btn btn-primary btn-sm">
                            <i class="fas fa-check-circle me-1"></i> Marcar todas como leídas
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <!-- Estadísticas -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="stat-number"><?= $estadisticas['registros_hoy'] ?? 0 ?></div>
                        <div class="stat-label">Registros Hoy</div>
                        <i class="fas fa-door-open mt-3 text-primary fs-4"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="stat-number"><?= $estadisticas['incidentes_pendientes'] ?? 0 ?></div>
                        <div class="stat-label">Incidentes Pendientes</div>
                        <i class="fas fa-exclamation-triangle mt-3 text-warning fs-4"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="stat-number"><?= $estadisticas['objetos_perdidos'] ?? 0 ?></div>
                        <div class="stat-label">Objetos Perdidos</div>
                        <i class="fas fa-box-open mt-3 text-info fs-4"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="stat-number"><?= $estadisticas['notificaciones_no_leidas'] ?? 0 ?></div>
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
                    <?php if (empty($notificaciones)): ?>
                        <div class="empty-state">
                            <i class="far fa-bell-slash"></i>
                            <h4>No tienes notificaciones</h4>
                            <p>Cuando recibas notificaciones, aparecerán aquí.</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($notificaciones as $notificacion): ?>
                                <div class="list-group-item list-group-item-action notification-item <?= $notificacion['leida'] ? '' : 'unread' ?>">
                                    <?php if (!$notificacion['leida']): ?>
                                        <span class="badge bg-primary notification-badge">Nueva</span>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex justify-content-between">
                                        <h5 class="mb-1"><?= htmlspecialchars($notificacion['titulo']) ?></h5>
                                        <small class="notification-time">
                                            <?= date('d/m/Y H:i', strtotime($notificacion['fecha_hora'])) ?>
                                        </small>
                                    </div>
                                    <p class="mb-2"><?= htmlspecialchars($notificacion['mensaje']) ?></p>
                                    
                                    <div class="notification-actions">
                                        <div>
                                            <?php if ($notificacion['tipo'] == 'incidente'): ?>
                                                <a href="celador_incidentes.php?id=<?= $notificacion['id_referencia'] ?>" 
                                                   class="notification-link">
                                                    <i class="fas fa-exclamation-triangle me-1"></i> Ver incidente
                                                </a>
                                            <?php elseif ($notificacion['tipo'] == 'objeto'): ?>
                                                <a href="celador_objeto_detalle.php?id=<?= $notificacion['id_referencia'] ?>" 
                                                   class="notification-link">
                                                    <i class="fas fa-box-open me-1"></i> Ver objeto
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if (!$notificacion['leida']): ?>
                                            <a href="celador_notificaciones.php?marcar_leida=<?= $notificacion['id_notificacion'] ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-check me-1"></i> Marcar como leída
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
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