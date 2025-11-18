<?php
session_start();
require_once 'db_config.php';

// Verificar autenticación
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

$usuario = $_SESSION['usuario'];
$error = '';
$reporte = null;
$evidencias = [];

if (!isset($_GET['id'])) {
    header('Location: reportes_incidentes.php');
    exit();
}

$id_reporte = $_GET['id'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener detalles del reporte
    $stmt = $conn->prepare("
        SELECT ri.*, ti.nombre AS tipo_incidente, ti.severidad, 
               CONCAT(u.nombres, ' ', u.apellidos) AS reportado_por,
               u.correo AS correo_reportante
        FROM reportes_incidente ri
        JOIN tipos_incidente ti ON ri.id_tipo = ti.id_tipo
        JOIN usuarios u ON ri.numero_documento = u.numero_documento
        WHERE ri.id_reporte = :id_reporte
    ");
    $stmt->bindParam(':id_reporte', $id_reporte);
    $stmt->execute();
    
    $reporte = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reporte) {
        $error = "Reporte no encontrado";
    } else {
        // Verificar permisos (solo el reportante, administradores o celadores pueden ver)
        if ($reporte['numero_documento'] != $usuario['numero_documento'] && 
            $usuario['id_rol'] != 5 && $usuario['id_rol'] != 4) {
            header('Location: reportes_incidentes.php');
            exit();
        }
        
        // Obtener evidencias del reporte
        $stmt = $conn->prepare("
            SELECT * FROM evidencias_incidente
            WHERE id_reporte = :id_reporte
        ");
        $stmt->bindParam(':id_reporte', $id_reporte);
        $stmt->execute();
        
        $evidencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Detalle de Reporte - Seguridad Universitaria</title>
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
        
        .evidencia-item {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
        }
        
        .evidencia-img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
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
                <a href="panel_<?= $usuario['id_rol'] == 5 ? 'administrador' : ($usuario['id_rol'] == 4 ? 'celador' : ($usuario['id_rol'] == 3 ? 'administrativo' : ($usuario['id_rol'] == 2 ? 'docente' : 'estudiante'))) ?>.php">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="#reportesSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                    <i class="fas fa-exclamation-triangle me-2"></i> Reportes
                </a>
                <ul class="collapse list-unstyled" id="reportesSubmenu">
                    <li><a href="reportes_incidentes.php"><i class="fas fa-list me-2"></i> Incidentes</a></li>
                    <?php if($usuario['id_rol'] == 5 || $usuario['id_rol'] == 4): ?>
                        <li><a href="reportes_pendientes.php"><i class="fas fa-clock me-2"></i> Pendientes</a></li>
                    <?php endif; ?>
                </ul>
            </li>
            <li>
                <a href="objetos_perdidos.php"><i class="fas fa-box-open me-2"></i> Objetos Perdidos</a>
            </li>
            <?php if($usuario['id_rol'] == 4 || $usuario['id_rol'] == 5): ?>
                <li>
                    <a href="control_accesos.php"><i class="fas fa-door-open me-2"></i> Control de Accesos</a>
                </li>
            <?php endif; ?>
            <li>
                <a href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Detalle del Reporte #<?= $id_reporte ?></h2>
                <a href="reportes_incidentes.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Volver
                </a>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php elseif (!$reporte): ?>
                <div class="alert alert-warning">Reporte no encontrado</div>
            <?php else: ?>
                <div class="row">
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Información del Incidente</span>
                                <div>
                                    <span class="badge-severidad severidad-<?= strtolower($reporte['severidad']) ?> me-2">
                                        <?= ucfirst($reporte['severidad']) ?>
                                    </span>
                                    <span class="badge-estado estado-<?= $reporte['estado'] ?>">
                                        <?= ucfirst(str_replace('_', ' ', $reporte['estado'])) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <h6 class="text-muted">Tipo de Incidente</h6>
                                        <p><?= htmlspecialchars($reporte['tipo_incidente']) ?></p>
                                    </div>
                                    <div class="col-md-4">
                                        <h6 class="text-muted">Ubicación</h6>
                                        <p><?= htmlspecialchars($reporte['ubicacion']) ?></p>
                                    </div>
                                    <div class="col-md-4">
                                        <h6 class="text-muted">Fecha del Incidente</h6>
                                        <p><?= date('d/m/Y H:i', strtotime($reporte['fecha_incidente'])) ?></p>
                                    </div>
                                </div>
                                
                                <h6 class="text-muted">Descripción</h6>
                                <p><?= nl2br(htmlspecialchars($reporte['descripcion'])) ?></p>
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                <span>Evidencias</span>
                            </div>
                            <div class="card-body">
                                <?php if (empty($evidencias)): ?>
                                    <div class="alert alert-info">No hay evidencias adjuntas</div>
                                <?php else: ?>
                                    <div class="row">
                                        <?php foreach ($evidencias as $evidencia): ?>
                                            <div class="col-md-6 mb-3">
                                                <div class="evidencia-item">
                                                    <?php if (strpos($evidencia['tipo_archivo'], 'image') !== false): ?>
                                                        <img src="<?= htmlspecialchars($evidencia['ruta_archivo']) ?>" class="evidencia-img mb-2" alt="Evidencia">
                                                    <?php else: ?>
                                                        <div class="d-flex align-items-center mb-2">
                                                            <i class="fas fa-file-alt fa-3x me-3"></i>
                                                            <div>
                                                                <h6><?= htmlspecialchars($evidencia['nombre_archivo']) ?></h6>
                                                                <small class="text-muted"><?= htmlspecialchars($evidencia['tipo_archivo']) ?></small>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                    <a href="<?= htmlspecialchars($evidencia['ruta_archivo']) ?>" class="btn btn-sm btn-outline-primary" download>
                                                        <i class="fas fa-download me-2"></i>Descargar
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                <span>Información del Reportante</span>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($reporte['reportado_por']) ?>&background=6c757d&color=fff" 
                                         alt="Reportante" class="rounded-circle me-3" width="50">
                                    <div>
                                        <h5><?= htmlspecialchars($reporte['reportado_por']) ?></h5>
                                        <small class="text-muted"><?= htmlspecialchars($reporte['correo_reportante']) ?></small>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="text-muted">Fecha de Reporte</h6>
                                    <p><?= date('d/m/Y H:i', strtotime($reporte['fecha_reporte'])) ?></p>
                                </div>
                                
                                <?php if($usuario['id_rol'] == 5 || $usuario['id_rol'] == 4): ?>
                                    <hr>
                                    <h6 class="text-muted">Acciones</h6>
                                    <div class="d-flex gap-2">
                                        <?php if($reporte['estado'] == 'reportado' || $reporte['estado'] == 'en_revision'): ?>
                                            <button class="btn btn-sm btn-success btn-cambiar-estado" data-estado="resuelto">
                                                <i class="fas fa-check me-2"></i>Marcar como Resuelto
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn btn-sm btn-danger btn-cambiar-estado" data-estado="archivado">
                                            <i class="fas fa-archive me-2"></i>Archivar
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cambiar estado del reporte
            document.querySelectorAll('.btn-cambiar-estado').forEach(btn => {
                btn.addEventListener('click', function() {
                    const nuevoEstado = this.getAttribute('data-estado');
                    const confirmacion = confirm(`¿Estás seguro de que deseas cambiar el estado a "${nuevoEstado}"?`);
                    
                    if (confirmacion) {
                        fetch('cambiar_estado_reporte.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `id_reporte=<?= $id_reporte ?>&estado=${nuevoEstado}`
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
                    }
                });
            });
        });
    </script>
</body>
</html>