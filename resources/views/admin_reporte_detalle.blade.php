<?php
session_start();
require_once 'db_config.php';

// Verificar autenticación y rol
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Obtener ID del reporte desde la URL
$id_reporte = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_reporte <= 0) {
    header('Location: reportes_incidentes.php');
    exit();
}

// Obtener datos del usuario
$usuario = $_SESSION['usuario'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Obtener información del reporte
    $stmt = $conn->prepare("
        SELECT r.*, t.nombre AS tipo_incidente, t.severidad,
               CONCAT(u.nombres, ' ', u.apellidos) AS reportado_por, 
               u.correo AS contacto_reportante, r.nombre_rol AS rol_reportante
        FROM reportes_incidente r
        JOIN tipos_incidente t ON r.id_tipo = t.id_tipo
        JOIN usuarios u ON r.numero_documento = u.numero_documento
        JOIN roles r ON u.id_rol = r.id_rol
        WHERE r.id_reporte = :id_reporte
    ");
    $stmt->bindParam(':id_reporte', $id_reporte);
    $stmt->execute();
    $reporte = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reporte) {
        header('Location: reportes_incidentes.php');
        exit();
    }
    
    // Obtener evidencias del reporte
    $stmt = $conn->prepare("SELECT * FROM evidencias_incidente WHERE id_reporte = :id_reporte");
    $stmt->bindParam(':id_reporte', $id_reporte);
    $stmt->execute();
    $evidencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener comentarios
    $stmt = $conn->prepare("
        SELECT c.*, CONCAT(u.nombres, ' ', u.apellidos) AS nombre_completo, 
               u.id_rol AS rol_usuario, r.nombre_rol AS rol_nombre
        FROM respuestas_incidentes c
        JOIN usuarios u ON c.numero_documento = u.numero_documento
        JOIN roles r ON u.id_rol = r.id_rol
        WHERE c.id_reporte = :id_reporte
        ORDER BY c.fecha_hora DESC
    ");
    $stmt->bindParam(':id_reporte', $id_reporte);
    $stmt->execute();
    $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Procesar formulario de comentario
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comentario'])) {
        $comentario = trim($_POST['comentario']);
        
        if (!empty($comentario)) {
            $stmt = $conn->prepare("
                INSERT INTO respuestas_incidentes (id_reporte, numero_documento, respuesta)
                VALUES (:id_reporte, :numero_documento, :comentario)
            ");
            $stmt->bindParam(':id_reporte', $id_reporte);
            $stmt->bindParam(':numero_documento', $usuario['numero_documento']);
            $stmt->bindParam(':comentario', $comentario);
            $stmt->execute();
            
            // Notificar al dueño del reporte (excepto si es el mismo)
            if ($reporte['numero_documento'] != $usuario['numero_documento']) {
                $stmt = $conn->prepare("
                    INSERT INTO notificaciones (numero_documento, titulo, mensaje, tipo, id_referencia)
                    VALUES (:numero_documento, 'Nuevo comentario en tu reporte', 
                            CONCAT('Hay un nuevo comentario en tu reporte (#', :id_reporte, '): ', LEFT(:comentario, 50)), 
                            'incidente', :id_reporte)
                ");
                $stmt->bindParam(':numero_documento', $reporte['numero_documento']);
                $stmt->bindParam(':id_reporte', $id_reporte);
                $stmt->bindParam(':comentario', $comentario);
                $stmt->execute();
            }
            
            header("Location: admin_reporte_detalle.php?id=$id_reporte");
            exit();
        }
    }
    
    // Procesar cambio de estado (solo para administradores y celadores)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado']) && 
        in_array($usuario['id_rol'], [4, 5])) {
        $nuevo_estado = $_POST['nuevo_estado'];
        
        $stmt = $conn->prepare("UPDATE reportes_incidente SET estado = :estado WHERE id_reporte = :id_reporte");
        $stmt->bindParam(':estado', $nuevo_estado);
        $stmt->bindParam(':id_reporte', $id_reporte);
        $stmt->execute();
        
        // Registrar en auditoría
        $stmt = $conn->prepare("
            INSERT INTO auditoria (numero_documento, accion, tabla_afectada, id_registro_afectado, datos_nuevos)
            VALUES (:numero_documento, 'Cambio de estado de reporte', 'reportes_incidente', 
                   :id_reporte, CONCAT('Nuevo estado: ', :estado))
        ");
        $stmt->bindParam(':numero_documento', $usuario['numero_documento']);
        $stmt->bindParam(':id_reporte', $id_reporte);
        $stmt->bindParam(':estado', $nuevo_estado);
        $stmt->execute();
        
        // Notificar al usuario
        $stmt = $conn->prepare("
            INSERT INTO notificaciones (numero_documento, titulo, mensaje, tipo, id_referencia)
            VALUES (:numero_documento, 'Estado de reporte actualizado', 
                    CONCAT('El estado de tu reporte (#', :id_reporte, ') ha cambiado a: ', :estado), 
                    'incidente', :id_reporte)
        ");
        $stmt->bindParam(':numero_documento', $reporte['numero_documento']);
        $stmt->bindParam(':id_reporte', $id_reporte);
        $stmt->bindParam(':estado', $nuevo_estado);
        $stmt->execute();
        
        header("Location: admin_reporte_detalle.php?id=$id_reporte");
        exit();
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
        .evidence-images {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        .evidence-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 5px;
            cursor: pointer;
        }
        .comment {
            border-left: 3px solid #3498db;
            padding-left: 10px;
            margin-bottom: 15px;
        }
        .comment-admin {
            border-left-color: #e74c3c;
            background-color: #f8f9fa;
        }
        .comment-celador {
            border-left-color: #f39c12;
        }
        .badge-estado {
            font-size: 0.9rem;
            padding: 5px 10px;
        }
        .badge-severidad {
            font-size: 0.9rem;
            padding: 5px 10px;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container mt-4">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Detalle del Reporte #<?= $id_reporte ?></h4>
                <div>
                    <span class="badge bg-<?= $reporte['estado'] == 'reportado' ? 'warning' : ($reporte['estado'] == 'en_revision' ? 'info' : 'success') ?> badge-estado">
                        <?= ucfirst(str_replace('_', ' ', $reporte['estado'])) ?>
                    </span>
                    <span class="badge bg-<?= $reporte['severidad'] == 'baja' ? 'success' : ($reporte['severidad'] == 'media' ? 'warning' : ($reporte['severidad'] == 'alta' ? 'danger' : 'dark')) ?> badge-severidad">
                        <?= ucfirst($reporte['severidad']) ?>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Información del Incidente</h5>
                        <p><strong>Tipo:</strong> <?= htmlspecialchars($reporte['tipo_incidente']) ?></p>
                        <p><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($reporte['descripcion'])) ?></p>
                        <p><strong>Ubicación:</strong> <?= htmlspecialchars($reporte['ubicacion']) ?></p>
                        <p><strong>Fecha del incidente:</strong> <?= date('d/m/Y H:i', strtotime($reporte['fecha_incidente'])) ?></p>
                    </div>
                    <div class="col-md-6">
                        <h5>Información del Reportante</h5>
                        <p><strong>Nombre:</strong> <?= htmlspecialchars($reporte['reportado_por']) ?></p>
                        <p><strong>Rol:</strong> <?= htmlspecialchars($reporte['rol_reportante']) ?></p>
                        <p><strong>Contacto:</strong> <?= htmlspecialchars($reporte['contacto_reportante']) ?></p>
                        <p><strong>Fecha de reporte:</strong> <?= date('d/m/Y H:i', strtotime($reporte['fecha_reporte'])) ?></p>
                    </div>
                </div>
                
                <?php if (!empty($evidencias)): ?>
                    <h5 class="mt-4">Evidencias</h5>
                    <div class="evidence-images">
                        <?php foreach ($evidencias as $evidencia): ?>
                            <img src="<?= htmlspecialchars($evidencia['ruta_archivo']) ?>" 
                                 alt="<?= htmlspecialchars($evidencia['nombre_archivo']) ?>" 
                                 class="evidence-image img-thumbnail"
                                 data-bs-toggle="modal" data-bs-target="#imageModal"
                                 data-bs-img="<?= htmlspecialchars($evidencia['ruta_archivo']) ?>">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (in_array($usuario['id_rol'], [4, 5])): ?>
                    <hr>
                    <h5>Cambiar Estado</h5>
                    <form method="POST" class="row g-3">
                        <div class="col-md-6">
                            <select name="nuevo_estado" class="form-select" required>
                                <option value="reportado" <?= $reporte['estado'] == 'reportado' ? 'selected' : '' ?>>Reportado</option>
                                <option value="en_revision" <?= $reporte['estado'] == 'en_revision' ? 'selected' : '' ?>>En Revisión</option>
                                <option value="resuelto" <?= $reporte['estado'] == 'resuelto' ? 'selected' : '' ?>>Resuelto</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" name="cambiar_estado" class="btn btn-primary">Actualizar Estado</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5>Comentarios</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($comentarios)): ?>
                    <?php foreach ($comentarios as $comentario): ?>
                        <div class="comment <?= $comentario['rol_usuario'] == 5 ? 'comment-admin' : ($comentario['rol_usuario'] == 4 ? 'comment-celador' : '') ?>">
                            <div class="d-flex justify-content-between">
                                <strong><?= htmlspecialchars($comentario['nombre_completo']) ?> (<?= htmlspecialchars($comentario['rol_nombre']) ?>)</strong>
                                <small class="text-muted"><?= date('d/m/Y H:i', strtotime($comentario['fecha_hora'])) ?></small>
                            </div>
                            <p class="mb-0"><?= nl2br(htmlspecialchars($comentario['respuesta'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No hay comentarios aún.</p>
                <?php endif; ?>
                
                <hr>
                <h6>Agregar Comentario</h6>
                <form method="POST">
                    <div class="mb-3">
                        <textarea name="comentario" class="form-control" rows="3" required placeholder="Escribe tu comentario aquí..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Enviar Comentario</button>
                </form>
            </div>
        </div>
        
        <a href="reportes_incidentes.php" class="btn btn-secondary mb-4">Volver a la lista de reportes</a>
    </div>
    
    <!-- Modal para imágenes -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <img id="modalImage" src="" class="img-fluid" alt="">
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar imagen en modal
        const imageModal = document.getElementById('imageModal');
        if (imageModal) {
            imageModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const imgSrc = button.getAttribute('data-bs-img');
                const modalImage = imageModal.querySelector('#modalImage');
                modalImage.src = imgSrc;
            });
        }
    </script>
</body>
</html>