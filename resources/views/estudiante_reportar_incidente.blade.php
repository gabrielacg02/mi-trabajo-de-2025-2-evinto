<?php
session_start();
require_once 'db_config.php';

// Verificar autenticación y rol
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] != 1) {
    header('Location: login.php');
    exit();
}

$usuario = $_SESSION['usuario'];
$error = '';
$success = '';

// Obtener tipos de incidente para el select
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $conn->query("SELECT id_tipo, nombre FROM tipos_incidente");
    $tipos_incidente = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Procesar formulario
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id_tipo = $_POST['id_tipo'];
        $descripcion = $_POST['descripcion'];
        $ubicacion = $_POST['ubicacion'];
        $fecha_incidente = $_POST['fecha_incidente'] . ' ' . $_POST['hora_incidente'];
        
        // Validaciones
        if (empty($id_tipo) || empty($descripcion) || empty($ubicacion) || empty($fecha_incidente)) {
            $error = 'Todos los campos son obligatorios';
        } else {
            // Llamar al procedimiento almacenado
            $stmt = $conn->prepare("CALL sp_reportar_incidente(?, ?, ?, ?, ?, @p_resultado, @p_mensaje, @p_id_reporte)");
            $stmt->bindParam(1, $usuario['numero_documento'], PDO::PARAM_STR);
            $stmt->bindParam(2, $id_tipo, PDO::PARAM_INT);
            $stmt->bindParam(3, $descripcion, PDO::PARAM_STR);
            $stmt->bindParam(4, $ubicacion, PDO::PARAM_STR);
            $stmt->bindParam(5, $fecha_incidente, PDO::PARAM_STR);
            $stmt->execute();
            
            // Obtener resultados del procedimiento
            $stmt = $conn->query("SELECT @p_resultado AS resultado, @p_mensaje AS mensaje, @p_id_reporte AS id_reporte");
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado['resultado']) {
                $success = $resultado['mensaje'];
                $_POST = array(); // Limpiar formulario
            } else {
                $error = $resultado['mensaje'];
            }
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
    <title>Reportar Incidente - Estudiante</title>
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
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="text-center py-3">
            <h4>Seguridad Universitaria</h4>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link text-white" href="panel_estudiante.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white active" href="estudiante_reportar_incidente.php"><i class="fas fa-exclamation-triangle me-2"></i>Reportar Incidente</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="panel_objetos_estudiante.php"><i class="fas fa-box-open me-2"></i>Objetos Perdidos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="estudiante_notificaciones.php"><i class="fas fa-bell me-2"></i>Notificaciones</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Reportar Incidente</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($error): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                            <?php endif; ?>
                            
                            <?php if ($success): ?>
                                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="id_tipo" class="form-label">Tipo de Incidente</label>
                                    <select class="form-select" id="id_tipo" name="id_tipo" required>
                                        <option value="">Seleccione un tipo</option>
                                        <?php foreach ($tipos_incidente as $tipo): ?>
                                            <option value="<?= $tipo['id_tipo'] ?>" <?= isset($_POST['id_tipo']) && $_POST['id_tipo'] == $tipo['id_tipo'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($tipo['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required><?= $_POST['descripcion'] ?? '' ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="ubicacion" class="form-label">Ubicación</label>
                                    <input type="text" class="form-control" id="ubicacion" name="ubicacion" value="<?= $_POST['ubicacion'] ?? '' ?>" required>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="fecha_incidente" class="form-label">Fecha</label>
                                        <input type="date" class="form-control" id="fecha_incidente" name="fecha_incidente" value="<?= $_POST['fecha_incidente'] ?? date('Y-m-d') ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="hora_incidente" class="form-label">Hora</label>
                                        <input type="time" class="form-control" id="hora_incidente" name="hora_incidente" value="<?= $_POST['hora_incidente'] ?? date('H:i') ?>" required>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-2"></i>Reportar Incidente
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>