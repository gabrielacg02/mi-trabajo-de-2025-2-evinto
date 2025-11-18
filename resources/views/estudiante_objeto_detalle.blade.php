<?php
session_start();
require_once 'db_config.php';

// Verificar autenticación y rol
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] != 1) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: panel_objetos.php');
    exit();
}

$usuario = $_SESSION['usuario'];
$id_objeto = $_GET['id'];
$error = '';
$success = '';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Obtener información del objeto
    $stmt = $conn->prepare("
        SELECT o.*, u.nombres, u.apellidos 
        FROM objetos_perdidos o
        JOIN usuarios u ON o.numero_documento = u.numero_documento
        WHERE o.id_objeto = :id_objeto
    ");
    $stmt->bindParam(':id_objeto', $id_objeto);
    $stmt->execute();
    $objeto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$objeto) {
        $error = 'Objeto no encontrado';
    }
    
    // Obtener imágenes del objeto
    $stmt = $conn->prepare("
        SELECT * FROM imagenes_objeto
        WHERE id_objeto = :id_objeto
    ");
    $stmt->bindParam(':id_objeto', $id_objeto);
    $stmt->execute();
    $imagenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener mensajes del objeto
    $stmt = $conn->prepare("
        SELECT m.*, u.nombres, u.apellidos 
        FROM mensajes_objetos m
        JOIN usuarios u ON m.numero_documento = u.numero_documento
        WHERE m.id_objeto = :id_objeto
        ORDER BY m.fecha_hora ASC
    ");
    $stmt->bindParam(':id_objeto', $id_objeto);
    $stmt->execute();
    $mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Procesar envío de mensaje
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enviar_mensaje'])) {
        $mensaje = $_POST['mensaje'];
        
        if (empty($mensaje)) {
            $error = 'El mensaje no puede estar vacío';
        } else {
            $stmt = $conn->prepare("
                INSERT INTO mensajes_objetos (id_objeto, numero_documento, mensaje)
                VALUES (:id_objeto, :numero_documento, :mensaje)
            ");
            $stmt->bindParam(':id_objeto', $id_objeto);
            $stmt->bindParam(':numero_documento', $usuario['numero_documento']);
            $stmt->bindParam(':mensaje', $mensaje);
            $stmt->execute();
            
            $success = 'Mensaje enviado correctamente';
            $_POST['mensaje'] = '';
            
            // Actualizar lista de mensajes
            $stmt = $conn->prepare("
                SELECT m.*, u.nombres, u.apellidos 
                FROM mensajes_objetos m
                JOIN usuarios u ON m.numero_documento = u.numero_documento
                WHERE m.id_objeto = :id_objeto
                ORDER BY m.fecha_hora ASC
            ");
            $stmt->bindParam(':id_objeto', $id_objeto);
            $stmt->execute();
            $mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    // Procesar marcar como encontrado (solo si es el dueño)
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['marcar_encontrado']) && $objeto['numero_documento'] == $usuario['numero_documento']) {
        $stmt = $conn->prepare("CALL sp_marcar_objeto_encontrado(?, ?, @p_resultado, @p_mensaje)");
        $stmt->bindParam(1, $id_objeto, PDO::PARAM_INT);
        $stmt->bindParam(2, $usuario['numero_documento'], PDO::PARAM_STR);
        $stmt->execute();
        
        $stmt = $conn->query("SELECT @p_resultado AS resultado, @p_mensaje AS mensaje");
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado['resultado']) {
            $success = $resultado['mensaje'];
            // Actualizar estado del objeto
            $objeto['estado'] = 'encontrado';
        } else {
            $error = $resultado['mensaje'];
        }
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
    <title>Detalle de Objeto - Estudiante</title>
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
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        
        .badge-perdido {
            background-color: #f39c12;
        }
        
        .badge-encontrado {
            background-color: #27ae60;
        }
        
        .badge-devuelto {
            background-color: #3498db;
        }
        
        .message-container {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .message {
            border-radius: 10px;
            padding: 10px 15px;
            margin-bottom: 10px;
            max-width: 80%;
        }
        
        .message-sender {
            background-color: #e3f2fd;
            align-self: flex-start;
        }
        
        .message-receiver {
            background-color: #f1f1f1;
            align-self: flex-end;
        }
        
        .img-thumbnail {
            max-width: 200px;
            max-height: 200px;
            margin: 5px;
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
    <!-- Sidebar -->
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
                <a href="panel_estudiante.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
            </li>
            <li>
                <a href="reportes_incidentes.php"><i class="fas fa-exclamation-triangle me-2"></i> Reportar Incidente</a>
            </li>
            <li>
                <a href="panel_objetos_estudiante.php"><i class="fas fa-box-open me-2"></i> Objetos Perdidos</a>
            </li>
            <li class="active">
                <a href="estudiante_notificaciones.php"><i class="fas fa-bell me-2"></i> Notificaciones</a>
            </li>
            <li>
                <a href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a>
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
            
            <?php if ($objeto): ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Detalle del Objeto #<?= $objeto['id_objeto'] ?></h5>
                                <span class="badge <?= $objeto['estado'] == 'perdido' ? 'badge-perdido' : ($objeto['estado'] == 'encontrado' ? 'badge-encontrado' : 'badge-devuelto') ?>">
                                    <?= ucfirst($objeto['estado']) ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Tipo de Objeto:</strong> <?= htmlspecialchars($objeto['tipo_objeto']) ?></p>
                                        <p><strong>Descripción:</strong> <?= htmlspecialchars($objeto['descripcion']) ?></p>
                                        <p><strong>Ubicación:</strong> <?= htmlspecialchars($objeto['ubicacion_perdida']) ?></p>
                                        <p><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($objeto['fecha_perdida'])) ?></p>
                                        <p><strong>Reportado por:</strong> <?= htmlspecialchars($objeto['nombres'] . ' ' . $objeto['apellidos']) ?></p>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <h6>Imágenes:</h6>
                                        <?php if (empty($imagenes)): ?>
                                            <div class="alert alert-info">No hay imágenes disponibles</div>
                                        <?php else: ?>
                                            <div class="d-flex flex-wrap">
                                                <?php foreach ($imagenes as $imagen): ?>
                                                    <img src="<?= htmlspecialchars($imagen['ruta_archivo']) ?>" class="img-thumbnail" alt="Imagen del objeto">
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <?php if ($objeto['numero_documento'] == $usuario['numero_documento'] && $objeto['estado'] == 'perdido'): ?>
                                    <form method="POST" class="mt-3">
                                        <button type="submit" name="marcar_encontrado" class="btn btn-success">
                                            <i class="fas fa-check me-2"></i>Marcar como Encontrado
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Mensajes -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Mensajes</h5>
                            </div>
                            <div class="card-body">
                                <div class="message-container d-flex flex-column mb-3">
                                    <?php foreach ($mensajes as $msg): ?>
                                        <div class="message <?= $msg['numero_documento'] == $usuario['numero_documento'] ? 'message-receiver' : 'message-sender' ?>">
                                            <strong><?= htmlspecialchars($msg['nombres'] . ' ' . $msg['apellidos']) ?></strong>
                                            <p class="mb-0"><?= htmlspecialchars($msg['mensaje']) ?></p>
                                            <small class="text-muted"><?= date('d/m/Y H:i', strtotime($msg['fecha_hora'])) ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="mensaje" class="form-label">Nuevo Mensaje</label>
                                        <textarea class="form-control" id="mensaje" name="mensaje" rows="3" required><?= $_POST['mensaje'] ?? '' ?></textarea>
                                    </div>
                                    <button type="submit" name="enviar_mensaje" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-2"></i>Enviar Mensaje
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-danger">Objeto no encontrado</div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>