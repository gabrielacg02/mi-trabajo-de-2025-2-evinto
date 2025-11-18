<?php
session_start();
require_once 'db_config.php';

// Verificar autenticación y rol
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Obtener ID del objeto desde la URL
$id_objeto = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_objeto <= 0) {
    header('Location: panel_objetos.php');
    exit();
}

// Obtener datos del usuario
$usuario = $_SESSION['usuario'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Obtener información del objeto
    $stmt = $conn->prepare("
        SELECT o.*, CONCAT(u.nombres, ' ', u.apellidos) AS reportado_por, 
               u.correo AS contacto_reportante, r.nombre_rol AS rol_reportante
        FROM objetos_perdidos o
        JOIN usuarios u ON o.numero_documento = u.numero_documento
        JOIN roles r ON u.id_rol = r.id_rol
        WHERE o.id_objeto = :id_objeto
    ");
    $stmt->bindParam(':id_objeto', $id_objeto);
    $stmt->execute();
    $objeto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$objeto) {
        header('Location: panel_objetos.php');
        exit();
    }
    
    // Obtener imágenes del objeto
    $stmt = $conn->prepare("SELECT * FROM imagenes_objeto WHERE id_objeto = :id_objeto");
    $stmt->bindParam(':id_objeto', $id_objeto);
    $stmt->execute();
    $imagenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener comentarios
    $stmt = $conn->prepare("
        SELECT c.*, CONCAT(u.nombres, ' ', u.apellidos) AS nombre_completo, 
               u.id_rol AS rol_usuario, r.nombre_rol AS rol_nombre
        FROM comentarios_objetos c
        JOIN usuarios u ON c.numero_documento = u.numero_documento
        JOIN roles r ON u.id_rol = r.id_rol
        WHERE c.id_objeto = :id_objeto
        ORDER BY c.fecha_hora DESC
    ");
    $stmt->bindParam(':id_objeto', $id_objeto);
    $stmt->execute();
    $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Procesar formulario de comentario
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comentario'])) {
        $comentario = trim($_POST['comentario']);
        
        if (!empty($comentario)) {
            $stmt = $conn->prepare("
                INSERT INTO comentarios_objetos (id_objeto, numero_documento, comentario)
                VALUES (:id_objeto, :numero_documento, :comentario)
            ");
            $stmt->bindParam(':id_objeto', $id_objeto);
            $stmt->bindParam(':numero_documento', $usuario['numero_documento']);
            $stmt->bindParam(':comentario', $comentario);
            $stmt->execute();
            
            // Notificar al dueño del objeto (excepto si es el mismo)
            if ($objeto['numero_documento'] != $usuario['numero_documento']) {
                $stmt = $conn->prepare("
                    INSERT INTO notificaciones (numero_documento, titulo, mensaje, tipo, id_referencia)
                    VALUES (:numero_documento, 'Nuevo comentario en tu objeto', 
                            CONCAT('Hay un nuevo comentario en tu objeto reportado (#', :id_objeto, '): ', LEFT(:comentario, 50)), 
                            'objeto', :id_objeto)
                ");
                $stmt->bindParam(':numero_documento', $objeto['numero_documento']);
                $stmt->bindParam(':id_objeto', $id_objeto);
                $stmt->bindParam(':comentario', $comentario);
                $stmt->execute();
            }
            
            header("Location: admin_objeto_detalle.php?id=$id_objeto");
            exit();
        }
    }
    
    // Procesar cambio de estado (solo para administradores y celadores)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado']) && 
        in_array($usuario['id_rol'], [4, 5])) {
        $nuevo_estado = $_POST['nuevo_estado'];
        
        $stmt = $conn->prepare("UPDATE objetos_perdidos SET estado = :estado WHERE id_objeto = :id_objeto");
        $stmt->bindParam(':estado', $nuevo_estado);
        $stmt->bindParam(':id_objeto', $id_objeto);
        $stmt->execute();
        
        // Registrar en auditoría
        $stmt = $conn->prepare("
            INSERT INTO auditoria (numero_documento, accion, tabla_afectada, id_registro_afectado, datos_nuevos)
            VALUES (:numero_documento, 'Cambio de estado de objeto', 'objetos_perdidos', 
                   :id_objeto, CONCAT('Nuevo estado: ', :estado))
        ");
        $stmt->bindParam(':numero_documento', $usuario['numero_documento']);
        $stmt->bindParam(':id_objeto', $id_objeto);
        $stmt->bindParam(':estado', $nuevo_estado);
        $stmt->execute();
        
        // Notificar al usuario
        $stmt = $conn->prepare("
            INSERT INTO notificaciones (numero_documento, titulo, mensaje, tipo, id_referencia)
            VALUES (:numero_documento, 'Estado de objeto actualizado', 
                    CONCAT('El estado de tu objeto (#', :id_objeto, ') ha cambiado a: ', :estado), 
                    'objeto', :id_objeto)
        ");
        $stmt->bindParam(':numero_documento', $objeto['numero_documento']);
        $stmt->bindParam(':id_objeto', $id_objeto);
        $stmt->bindParam(':estado', $nuevo_estado);
        $stmt->execute();
        
        header("Location: admin_objeto_detalle.php?id=$id_objeto");
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
    <title>Detalle de Objeto - Seguridad Universitaria</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .object-images {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        .object-image {
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
                <h4>Detalle del Objeto #<?= $id_objeto ?></h4>
                <span class="badge bg-<?= $objeto['estado'] == 'perdido' ? 'warning' : ($objeto['estado'] == 'encontrado' ? 'info' : 'success') ?> badge-estado">
                    <?= ucfirst($objeto['estado']) ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Información del Objeto</h5>
                        <p><strong>Tipo:</strong> <?= htmlspecialchars($objeto['tipo_objeto']) ?></p>
                        <p><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($objeto['descripcion'])) ?></p>
                        <p><strong>Ubicación donde se perdió/encontró:</strong> <?= htmlspecialchars($objeto['ubicacion_perdida']) ?></p>
                        <p><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($objeto['fecha_perdida'])) ?></p>
                        <p><strong>Tipo de reporte:</strong> <?= ucfirst($objeto['tipo_reporte']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <h5>Información del Reportante</h5>
                        <p><strong>Nombre:</strong> <?= htmlspecialchars($objeto['reportado_por']) ?></p>
                        <p><strong>Rol:</strong> <?= htmlspecialchars($objeto['rol_reportante']) ?></p>
                        <p><strong>Contacto:</strong> <?= htmlspecialchars($objeto['contacto_reportante']) ?></p>
                        <p><strong>Fecha de reporte:</strong> <?= date('d/m/Y H:i', strtotime($objeto['fecha_reporte'])) ?></p>
                    </div>
                </div>
                
                <?php if (!empty($imagenes)): ?>
                    <h5 class="mt-4">Imágenes del Objeto</h5>
                    <div class="object-images">
                        <?php foreach ($imagenes as $imagen): ?>
                            <img src="<?= htmlspecialchars($imagen['ruta_archivo']) ?>" 
                                 alt="<?= htmlspecialchars($imagen['nombre_archivo']) ?>" 
                                 class="object-image img-thumbnail"
                                 data-bs-toggle="modal" data-bs-target="#imageModal"
                                 data-bs-img="<?= htmlspecialchars($imagen['ruta_archivo']) ?>">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (in_array($usuario['id_rol'], [4, 5])): ?>
                    <hr>
                    <h5>Cambiar Estado</h5>
                    <form method="POST" class="row g-3">
                        <div class="col-md-6">
                            <select name="nuevo_estado" class="form-select" required>
                                <option value="perdido" <?= $objeto['estado'] == 'perdido' ? 'selected' : '' ?>>Perdido</option>
                                <option value="encontrado" <?= $objeto['estado'] == 'encontrado' ? 'selected' : '' ?>>Encontrado</option>
                                <option value="devuelto" <?= $objeto['estado'] == 'devuelto' ? 'selected' : '' ?>>Devuelto</option>
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
                            <p class="mb-0"><?= nl2br(htmlspecialchars($comentario['comentario'])) ?></p>
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
        
        <a href="panel_objetos.php" class="btn btn-secondary mb-4">Volver a la lista de objetos</a>
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