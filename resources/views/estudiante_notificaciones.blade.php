<?php
$usuario = $usuario ?? ['nombre_completo' => '', 'id_rol' => 1, 'numero_documento' => ''];
$error = $error ?? '';
$success = $success ?? '';
$notificaciones = $notificaciones ?? [];
$total_no_leidas = $total_no_leidas ?? 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones - Estudiante</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
        }
        
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f8f9fa;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        
        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
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
        }
        
        .notification-item {
            padding: 15px;
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
            font-size: 0.7em;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .main-content {
                margin-left: 0;
            }
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
        
    </style>
</head>
<body>
 <div class="sidebar">
        <div class="sidebar-header">
            <h3>Seguridad Universitaria</h3>
        </div>
        
        <div class="user-profile">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($usuario['nombre_completo']) ?>&background=3498db&color=fff" alt="Perfil">
            <div class="user-info">
                <h5><?= htmlspecialchars($usuario['nombre_completo']) ?></h5>
                <p>Estudiante</p>
            </div>
        </div>
        
        <ul class="list-unstyled components">
            <li>
                <a href="<?php echo route('panel.estudiante'); ?>"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
            </li>
            <li>
                <a href="<?php echo route('reportes.formulario'); ?>"><i class="fas fa-exclamation-triangle me-2"></i> Reportar Incidente</a>
            </li>
            <li>
                <a href="<?php echo route('objetos.estudiante'); ?>"><i class="fas fa-box-open me-2"></i> Objetos Perdidos</a>
            </li>
            <li class="active">
                <a href="<?php echo route('notificaciones.estudiante'); ?>"><i class="fas fa-bell me-2"></i> Notificaciones</a>
            </li>
            <li>
                <a href="<?php echo route('logout'); ?>"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a>
            </li>
        </ul>
    </div>
    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-lg-12">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Mis Notificaciones</h5>
                            <div>
                                <span class="badge bg-primary rounded-pill"><?= $total_no_leidas ?> sin leer</span>
                                <a href="<?php echo route('notificaciones.estudiante'); ?>?marcar_leidas=1" class="btn btn-sm btn-success ms-2">
                                    <i class="fas fa-check me-1"></i>Marcar todas como leídas
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($notificaciones)): ?>
                                <div class="alert alert-info m-3">No tienes notificaciones</div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($notificaciones as $notificacion): ?>
                                        <a href="<?= 
                                            $notificacion['tipo'] == 'incidente' ? route('incidentes.detalle', ['id' => $notificacion['id_referencia']]) : 
                                            ($notificacion['tipo'] == 'objeto' ? route('objetos.estudiante.detalle', ['id' => $notificacion['id_referencia']]) : '#') 
                                        ?>" class="list-group-item list-group-item-action notification-item <?= $notificacion['leida'] ? '' : 'unread' ?>">
                                            <div class="d-flex justify-content-between">
                                                <h6 class="mb-1"><?= htmlspecialchars($notificacion['titulo']) ?></h6>
                                                <small class="notification-time"><?= date('d/m/Y H:i', strtotime($notificacion['fecha_hora'])) ?></small>
                                            </div>
                                            <p class="mb-1"><?= htmlspecialchars($notificacion['mensaje']) ?></p>
                                            <?php if (!$notificacion['leida']): ?>
                                                <span class="badge bg-danger notification-badge">Nuevo</span>
                                            <?php endif; ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>