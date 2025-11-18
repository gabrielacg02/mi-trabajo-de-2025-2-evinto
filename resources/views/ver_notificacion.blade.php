<?php
session_start();
require_once 'db_config.php';

// Verificar autenticación
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Verificar que se haya proporcionado un ID de notificación
if (!isset($_GET['id'])) {
    header('Location: panel_administrador.php');
    exit();
}

$id_notificacion = $_GET['id'];
$usuario = $_SESSION['usuario'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Obtener la notificación
    $stmt = $conn->prepare("
        SELECT n.* 
        FROM notificaciones n
        WHERE n.id_notificacion = :id_notificacion AND n.numero_documento = :numero_documento
    ");
    $stmt->bindParam(':id_notificacion', $id_notificacion);
    $stmt->bindParam(':numero_documento', $usuario['numero_documento']);
    $stmt->execute();
    $notificacion = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$notificacion) {
        header('Location: panel_administrador.php');
        exit();
    }
    
    // Marcar como leída si no lo está
    if (!$notificacion['leida']) {
        $stmt = $conn->prepare("
            UPDATE notificaciones 
            SET leida = 1 
            WHERE id_notificacion = :id_notificacion
        ");
        $stmt->bindParam(':id_notificacion', $id_notificacion);
        $stmt->execute();
    }
    
} catch(PDOException $e) {
    $error = "Error de conexión: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificación - Seguridad Universitaria</title>
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
                <h2>Notificación</h2>
                <a href="admin_notificaciones.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-2"></i> Volver a Notificaciones
                </a>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><?= htmlspecialchars($notificacion['titulo']) ?></h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">
                            <?= date('d/m/Y H:i', strtotime($notificacion['fecha_hora'])) ?>
                        </small>
                    </div>
                    
                    <div class="alert alert-info">
                        <?= htmlspecialchars($notificacion['mensaje']) ?>
                    </div>
                    
                    <?php if ($notificacion['id_referencia'] && $notificacion['tipo']): ?>
                        <div class="mt-4">
                            <h6>Acciones:</h6>
                            <?php if ($notificacion['tipo'] == 'incidente'): ?>
                                <a href="detalle_incidente.php?id=<?= $notificacion['id_referencia'] ?>" class="btn btn-primary">
                                    Ver Incidente
                                </a>
                            <?php elseif ($notificacion['tipo'] == 'objeto'): ?>
                                <a href="detalle_objeto.php?id=<?= $notificacion['id_referencia'] ?>" class="btn btn-primary">
                                    Ver Objeto
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>