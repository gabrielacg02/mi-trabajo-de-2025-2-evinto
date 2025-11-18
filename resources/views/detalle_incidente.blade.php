<?php
session_start();
require_once 'db_config.php';

// Verificar autenticación
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Verificar que se haya proporcionado un ID de incidente
if (!isset($_GET['id'])) {
    header('Location: panel_administrador.php');
    exit();
}

$id_incidente = $_GET['id'];
$usuario = $_SESSION['usuario'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Obtener el incidente
    $stmt = $conn->prepare("
        SELECT r.*, t.nombre AS tipo_incidente, t.severidad, 
               CONCAT(u.nombres, ' ', u.apellidos) AS reportado_por
        FROM reportes_incidente r
        JOIN tipos_incidente t ON r.id_tipo = t.id_tipo
        JOIN usuarios u ON r.numero_documento = u.numero_documento
        WHERE r.id_reporte = :id_incidente
    ");
    $stmt->bindParam(':id_incidente', $id_incidente);
    $stmt->execute();
    $incidente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$incidente) {
        header('Location: panel_administrador.php');
        exit();
    }
    
    // Obtener evidencias
    $stmt = $conn->prepare("
        SELECT * FROM evidencias_incidente
        WHERE id_reporte = :id_incidente
    ");
    $stmt->bindParam(':id_incidente', $id_incidente);
    $stmt->execute();
    $evidencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Error de conexión: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Incidente - Seguridad Universitaria</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        /* Mantén los mismos estilos que en panel_administrador.php */
    </style>
</head>
<body>
    <!-- Sidebar (igual que en panel_administrador.php) -->
    <div class="sidebar">
        <!-- ... -->
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Detalle de Incidente #<?= $incidente['id_reporte'] ?></h2>
                <a href="reportes_incidentes.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-2"></i> Volver a Incidentes
                </a>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Información del Incidente</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h6>Tipo de Incidente:</h6>
                                    <p><?= htmlspecialchars($incidente['tipo_incidente']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Severidad:</h6>
                                    <span class="badge <?= 
                                        $incidente['severidad'] == 'alta' ? 'bg-danger' : 
                                        ($incidente['severidad'] == 'media' ? 'bg-warning' : 'bg-info') 
                                    ?>">
                                        <?= ucfirst($incidente['severidad']) ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h6>Ubicación:</h6>
                                    <p><?= htmlspecialchars($incidente['ubicacion']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Fecha del Incidente:</h6>
                                    <p><?= date('d/m/Y H:i', strtotime($incidente['fecha_incidente'])) ?></p>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <h6>Descripción:</h6>
                                <p><?= htmlspecialchars($incidente['descripcion']) ?></p>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h6>Reportado por:</h6>
                                    <p><?= htmlspecialchars($incidente['reportado_por']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Fecha de Reporte:</h6>
                                    <p><?= date('d/m/Y H:i', strtotime($incidente['fecha_reporte'])) ?></p>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <h6>Estado:</h6>
                                <span class="badge <?= 
                                    $incidente['estado'] == 'reportado' ? 'bg-primary' : 
                                    ($incidente['estado'] == 'en_revision' ? 'bg-warning' : 
                                    ($incidente['estado'] == 'resuelto' ? 'bg-success' : 'bg-secondary')) 
                                ?>">
                                    <?= ucfirst(str_replace('_', ' ', $incidente['estado'])) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Evidencias -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Evidencias</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($evidencias)): ?>
                                <div class="alert alert-info">No hay evidencias adjuntas</div>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($evidencias as $evidencia): ?>
                                        <div class="col-md-4 mb-3">
                                            <div class="card">
                                                <?php if (strpos($evidencia['tipo_archivo'], 'image') !== false): ?>
                                                    <img src="<?= htmlspecialchars($evidencia['ruta_archivo']) ?>" class="card-img-top" alt="Evidencia">
                                                <?php else: ?>
                                                    <div class="card-body text-center">
                                                        <i class="fas fa-file-alt fa-3x mb-3"></i>
                                                        <h6><?= htmlspecialchars($evidencia['nombre_archivo']) ?></h6>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="card-footer text-center">
                                                    <a href="<?= htmlspecialchars($evidencia['ruta_archivo']) ?>" class="btn btn-sm btn-primary" download>
                                                        <i class="fas fa-download me-2"></i> Descargar
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <!-- Acciones -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Acciones</h5>
                        </div>
                        <div class="card-body">
                            <form action="procesar_incidente.php" method="post">
                                <input type="hidden" name="id_reporte" value="<?= $incidente['id_reporte'] ?>">
                                
                                <div class="mb-3">
                                    <label for="estado" class="form-label">Cambiar Estado</label>
                                    <select class="form-select" id="estado" name="estado">
                                        <option value="reportado" <?= $incidente['estado'] == 'reportado' ? 'selected' : '' ?>>Reportado</option>
                                        <option value="en_revision" <?= $incidente['estado'] == 'en_revision' ? 'selected' : '' ?>>En Revisión</option>
                                        <option value="resuelto" <?= $incidente['estado'] == 'resuelto' ? 'selected' : '' ?>>Resuelto</option>
                                        <option value="archivado" <?= $incidente['estado'] == 'archivado' ? 'selected' : '' ?>>Archivado</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="comentario" class="form-label">Comentario</label>
                                    <textarea class="form-control" id="comentario" name="comentario" rows="3"></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-save me-2"></i> Guardar Cambios
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>