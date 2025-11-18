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

// Obtener datos del usuario
$usuario = $_SESSION['usuario'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Obtener objetos pendientes de aprobación
    $stmt = $conn->query("
        SELECT o.id_objeto, o.numero_documento, CONCAT(u.nombres, ' ', u.apellidos) AS reportado_por, 
               o.tipo_objeto, o.descripcion, o.ubicacion_perdida, o.fecha_perdida, o.fecha_reporte,
               o.estado, o.tipo_reporte
        FROM objetos_perdidos o
        JOIN usuarios u ON o.numero_documento = u.numero_documento
        JOIN aprobaciones_reportes a ON o.id_objeto = a.id_objeto
        WHERE a.estado = 'pendiente'
        ORDER BY o.fecha_reporte DESC
    ");
    $objetos_pendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener notificaciones no leídas
    $stmt = $conn->prepare("
        SELECT n.* 
        FROM notificaciones n
        WHERE n.numero_documento = :numero_documento AND n.leida = 0
        ORDER BY n.fecha_hora DESC
        LIMIT 5
    ");
    $stmt->bindParam(':numero_documento', $usuario['numero_documento']);
    $stmt->execute();
    $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Error de conexión: " . $e->getMessage();
}

// Procesar aprobación/rechazo
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion'])) {
    $id_objeto = $_POST['id_objeto'];
    $accion = $_POST['accion'];
    $comentarios = $_POST['comentarios'] ?? '';
    
    try {
        $stmt = $conn->prepare("CALL sp_aprobar_reporte(NULL, :id_objeto, :aprobado_por, :estado, :comentarios, @resultado, @mensaje)");
        $stmt->bindParam(':id_objeto', $id_objeto);
        $stmt->bindParam(':aprobado_por', $usuario['numero_documento']);
        $stmt->bindParam(':estado', $accion);
        $stmt->bindParam(':comentarios', $comentarios);
        $stmt->execute();
        
        // Obtener resultado del procedimiento almacenado
        $stmt = $conn->query("SELECT @resultado AS resultado, @mensaje AS mensaje");
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado['resultado']) {
            $mensaje_exito = $resultado['mensaje'];
            // Recargar la página para ver los cambios
            header("Location: admin_objetos_pendientes.php?success=" . urlencode($mensaje_exito));
            exit();
        } else {
            $error = $resultado['mensaje'];
        }
    } catch(PDOException $e) {
        $error = "Error al procesar la solicitud: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Objetos Pendientes - Seguridad Universitaria</title>
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
        
        .descripcion-limitada {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
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
                <p>Administrador</p>
            </div>
        </div>
        
        <ul class="list-unstyled components">
            <li>
                <a href="panel_administrador.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
            </li>
            <li>
                <a href="#usuariosSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                    <i class="fas fa-users me-2"></i> Usuarios
                </a>
                <ul class="collapse list-unstyled" id="usuariosSubmenu">
                    <li><a href="admin_usuarios.php"><i class="fas fa-list me-2"></i> Listar Usuarios</a></li>
                    <li><a href="admin_registrar_usuario.php"><i class="fas fa-user-plus me-2"></i> Registrar Usuario</a></li>
                </ul>
            </li>
            <li>
                <a href="#reportesSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                    <i class="fas fa-exclamation-triangle me-2"></i> Reportes
                </a>
                <ul class="collapse list-unstyled" id="reportesSubmenu">
                    <li><a href="reportes_incidentes.php"><i class="fas fa-list me-2"></i> Incidentes</a></li>
                    <li><a href="reportes_pendientes.php"><i class="fas fa-clock me-2"></i> Pendientes</a></li>
                </ul>
            </li>
            <li class="active">
                <a href="#objetosSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                    <i class="fas fa-box-open me-2"></i> Objetos
                </a>
                <ul class="collapse list-unstyled show" id="objetosSubmenu">
                    <li><a href="panel_objetos.php"><i class="fas fa-list me-2"></i> Objetos Perdidos</a></li>
                    <li><a href="admin_objetos_pendientes.php" class="active"><i class="fas fa-clock me-2"></i> Pendientes</a></li>
                </ul>
            </li>
            <li>
                <a href="admin_accesos.php"><i class="fas fa-door-open me-2"></i> Control de Accesos</a>
            </li>
            <li>
                <a href="admin_auditoria.php"><i class="fas fa-clipboard-list me-2"></i> Auditoría</a>
            </li>
            <li>
                <a href="admin_configuracion.php"><i class="fas fa-cog me-2"></i> Configuración</a>
            </li>
            <li>
                <a href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Objetos Pendientes de Aprobación</h2>
                <a href="panel_objetos.php" class="btn btn-primary">
                    <i class="fas fa-box-open me-2"></i> Ver Todos los Objetos
                </a>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <!-- Tabla de Objetos Pendientes -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-clock me-2"></i> Objetos Pendientes de Revisión
                </div>
                <div class="card-body">
                    <?php if (empty($objetos_pendientes)): ?>
                        <div class="alert alert-info">No hay objetos pendientes de aprobación</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Reportado Por</th>
                                        <th>Tipo</th>
                                        <th>Ubicación</th>
                                        <th>Fecha</th>
                                        <th>Descripción</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($objetos_pendientes as $objeto): ?>
                                        <tr>
                                            <td>#<?= $objeto['id_objeto'] ?></td>
                                            <td><?= htmlspecialchars($objeto['reportado_por']) ?></td>
                                            <td>
                                                <span class="badge <?= $objeto['tipo_reporte'] == 'perdida' ? 'bg-warning' : 'bg-info' ?>">
                                                    <?= ucfirst($objeto['tipo_reporte']) ?>
                                                </span>
                                                <br>
                                                <?= htmlspecialchars($objeto['tipo_objeto']) ?>
                                            </td>
                                            <td><?= htmlspecialchars($objeto['ubicacion_perdida']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($objeto['fecha_perdida'])) ?></td>
                                            <td class="descripcion-limitada"><?= htmlspecialchars($objeto['descripcion']) ?></td>
                                            <td>
                                                <div class="d-flex">
                                                    <a href="admin_objeto_detalle.php?id=<?= $objeto['id_objeto'] ?>" class="btn btn-sm btn-primary me-2" title="Ver Detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-success me-2" data-bs-toggle="modal" data-bs-target="#aprobarModal<?= $objeto['id_objeto'] ?>" title="Aprobar">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rechazarModal<?= $objeto['id_objeto'] ?>" title="Rechazar">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                                
                                                <!-- Modal Aprobar -->
                                                <div class="modal fade" id="aprobarModal<?= $objeto['id_objeto'] ?>" tabindex="-1" aria-labelledby="aprobarModalLabel<?= $objeto['id_objeto'] ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="aprobarModalLabel<?= $objeto['id_objeto'] ?>">Aprobar Objeto #<?= $objeto['id_objeto'] ?></h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form method="POST" action="">
                                                                <div class="modal-body">
                                                                    <p>¿Estás seguro que deseas aprobar este objeto?</p>
                                                                    <div class="mb-3">
                                                                        <label for="comentariosAprobar<?= $objeto['id_objeto'] ?>" class="form-label">Comentarios (opcional):</label>
                                                                        <textarea class="form-control" id="comentariosAprobar<?= $objeto['id_objeto'] ?>" name="comentarios" rows="3"></textarea>
                                                                    </div>
                                                                    <input type="hidden" name="id_objeto" value="<?= $objeto['id_objeto'] ?>">
                                                                    <input type="hidden" name="accion" value="aprobado">
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                    <button type="submit" class="btn btn-success">Aprobar</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Modal Rechazar -->
                                                <div class="modal fade" id="rechazarModal<?= $objeto['id_objeto'] ?>" tabindex="-1" aria-labelledby="rechazarModalLabel<?= $objeto['id_objeto'] ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="rechazarModalLabel<?= $objeto['id_objeto'] ?>">Rechazar Objeto #<?= $objeto['id_objeto'] ?></h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form method="POST" action="">
                                                                <div class="modal-body">
                                                                    <p>¿Estás seguro que deseas rechazar este objeto?</p>
                                                                    <div class="mb-3">
                                                                        <label for="comentariosRechazar<?= $objeto['id_objeto'] ?>" class="form-label">Comentarios (opcional):</label>
                                                                        <textarea class="form-control" id="comentariosRechazar<?= $objeto['id_objeto'] ?>" name="comentarios" rows="3"></textarea>
                                                                    </div>
                                                                    <input type="hidden" name="id_objeto" value="<?= $objeto['id_objeto'] ?>">
                                                                    <input type="hidden" name="accion" value="rechazado">
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                    <button type="submit" class="btn btn-danger">Rechazar</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
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
        });
    </script>
</body>
</html>