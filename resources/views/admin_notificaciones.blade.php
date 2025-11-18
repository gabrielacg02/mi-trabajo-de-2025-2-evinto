<?php
session_start();
require_once 'db_config.php';

// Verificar autenticación y rol
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['usuario']['id_rol'] != 5) { // 5 = Administrador
    header('Location: index.php');
    exit();
}

$usuario = $_SESSION['usuario'];

// Paginación
$por_pagina = 10;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$inicio = ($pagina > 1) ? ($pagina * $por_pagina - $por_pagina) : 0;

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Obtener notificaciones
    $stmt = $conn->prepare("
        SELECT SQL_CALC_FOUND_ROWS n.* 
        FROM notificaciones n
        WHERE n.numero_documento = :numero_documento
        ORDER BY n.fecha_hora DESC
        LIMIT :inicio, :por_pagina
    ");
    $stmt->bindParam(':numero_documento', $usuario['numero_documento']);
    $stmt->bindParam(':inicio', $inicio, PDO::PARAM_INT);
    $stmt->bindParam(':por_pagina', $por_pagina, PDO::PARAM_INT);
    $stmt->execute();
    $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener total de notificaciones para paginación
    $total_notificaciones = $conn->query("SELECT FOUND_ROWS()")->fetchColumn();
    $total_paginas = ceil($total_notificaciones / $por_pagina);
    
} catch(PDOException $e) {
    $error = "Error de conexión: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones - Seguridad Universitaria</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        /* Mantén los mismos estilos que en panel_administrador.php */
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
        
        /* ... (mantén todos los estilos del sidebar y layout) ... */
        
        .notification-item {
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
        
        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .pagination .page-link {
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <!-- Sidebar (igual que en panel_administrador.php) -->
    <div class="sidebar">
        <div class="sidebar-header">
           <h5><i class="fas fa-shield-alt"></i> Seguridad Universitaria</h5>
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
                <a href="panel_administrador.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
            </li>
            <!-- ... (resto del menú igual que en panel_administrador.php) ... -->
            <li>
                <a href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Mis Notificaciones</h2>
                <div>
                    <a href="panel_administrador.php" class="btn btn-outline-primary me-2">
                        <i class="fas fa-arrow-left me-2"></i> Volver
                    </a>
                    <button id="marcar-todas" class="btn btn-primary">
                        <i class="fas fa-check-circle me-2"></i> Marcar todas como leídas
                    </button>
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body p-0">
                    <?php if (empty($notificaciones)): ?>
                        <div class="alert alert-info m-3">No tienes notificaciones</div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($notificaciones as $notificacion): ?>
                                <a href="ver_notificacion.php?id=<?= $notificacion['id_notificacion'] ?>" 
                                   class="list-group-item list-group-item-action notification-item <?= $notificacion['leida'] ? '' : 'unread' ?>">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1"><?= htmlspecialchars($notificacion['titulo']) ?></h6>
                                        <small class="notification-time"><?= date('d/m/Y H:i', strtotime($notificacion['fecha_hora'])) ?></small>
                                    </div>
                                    <p class="mb-1"><?= htmlspecialchars(substr($notificacion['mensaje'], 0, 100)) ?>...</p>
                                    <small class="text-muted">
                                        <?= ucfirst($notificacion['tipo']) ?>
                                        <?= $notificacion['leida'] ? '' : '<span class="badge bg-danger ms-2">Nueva</span>' ?>
                                    </small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Paginación -->
                        <nav class="mt-4 px-3">
                            <ul class="pagination justify-content-center">
                                <?php if ($pagina > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?pagina=<?= $pagina - 1 ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                    <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                                        <a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($pagina < $total_paginas): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?pagina=<?= $pagina + 1 ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
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
        // Marcar todas las notificaciones como leídas
        $('#marcar-todas').click(function() {
            $.ajax({
                url: 'marcar_notificaciones_leidas.php',
                method: 'POST',
                data: { usuario: '<?= $usuario['numero_documento'] ?>' },
                success: function(response) {
                    if (response.success) {
                        // Actualizar la interfaz
                        $('.notification-item').removeClass('unread');
                        $('.badge.bg-danger').remove();
                        $('#marcar-todas').prop('disabled', true);
                    }
                },
                error: function() {
                    alert('Error al marcar las notificaciones como leídas');
                }
            });
        });
    </script>
</body>
</html>