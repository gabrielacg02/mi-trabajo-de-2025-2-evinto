<?php
session_start();
require_once 'db_config.php';

// Verificar autenticación y rol (solo administradores pueden aprobar reportes)
if (!isset($_SESSION['usuario'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit();
}

if ($_SESSION['usuario']['id_rol'] != 5) { // 5 = Administrador
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

// Obtener datos del POST
$id_reporte = isset($_POST['id_reporte']) ? intval($_POST['id_reporte']) : 0;
$accion = isset($_POST['accion']) ? $_POST['accion'] : '';
$comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';

// Validar datos
if ($id_reporte <= 0 || !in_array($accion, ['aprobar', 'rechazar'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit();
}

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar que el reporte existe y está pendiente
    $stmt = $conn->prepare("
        SELECT r.id_reporte, r.numero_documento, a.id_aprobacion
        FROM reportes_incidente r
        JOIN aprobaciones_reportes a ON r.id_reporte = a.id_reporte
        WHERE r.id_reporte = :id_reporte AND a.estado = 'pendiente'
    ");
    $stmt->bindParam(':id_reporte', $id_reporte);
    $stmt->execute();
    
    $reporte = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reporte) {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['success' => false, 'message' => 'Reporte no encontrado o ya fue procesado']);
        exit();
    }
    
    // Determinar el nuevo estado según la acción
    $nuevo_estado = ($accion == 'aprobar') ? 'aprobado' : 'rechazado';
    $estado_incidente = ($accion == 'aprobar') ? 'reportado' : 'archivado';
    
    // Iniciar transacción
    $conn->beginTransaction();
    
    // Actualizar la aprobación
    $stmt = $conn->prepare("
        UPDATE aprobaciones_reportes
        SET estado = :estado,
            aprobado_por = :aprobado_por,
            comentarios = :comentarios,
            fecha_aprobacion = NOW()
        WHERE id_aprobacion = :id_aprobacion
    ");
    $stmt->bindParam(':estado', $nuevo_estado);
    $stmt->bindParam(':aprobado_por', $_SESSION['usuario']['numero_documento']);
    $stmt->bindParam(':comentarios', $comentario);
    $stmt->bindParam(':id_aprobacion', $reporte['id_aprobacion']);
    $stmt->execute();
    
    // Actualizar estado del incidente
    $stmt = $conn->prepare("
        UPDATE reportes_incidente
        SET estado = :estado
        WHERE id_reporte = :id_reporte
    ");
    $stmt->bindParam(':estado', $estado_incidente);
    $stmt->bindParam(':id_reporte', $id_reporte);
    $stmt->execute();
    
    // Registrar en auditoría
    $stmt = $conn->prepare("
        INSERT INTO auditoria (numero_documento, accion, tabla_afectada, id_registro_afectado, datos_nuevos)
        VALUES (:numero_documento, :accion, 'aprobaciones_reportes', :id_registro, :datos_nuevos)
    ");
    $accion_auditoria = ($accion == 'aprobar') ? 'Aprobación de reporte' : 'Rechazo de reporte';
    $datos_nuevos = "Estado: $nuevo_estado" . ($comentario ? ", Comentario: $comentario" : "");
    $stmt->bindParam(':numero_documento', $_SESSION['usuario']['numero_documento']);
    $stmt->bindParam(':accion', $accion_auditoria);
    $stmt->bindParam(':id_registro', $reporte['id_aprobacion']);
    $stmt->bindParam(':datos_nuevos', $datos_nuevos);
    $stmt->execute();
    
    // Notificar al usuario que reportó el incidente
    $stmt = $conn->prepare("
        INSERT INTO notificaciones (numero_documento, titulo, mensaje, tipo, id_referencia)
        VALUES (:numero_documento, :titulo, :mensaje, 'incidente', :id_referencia)
    ");
    $titulo = "Reporte #$id_reporte " . strtoupper($nuevo_estado);
    $mensaje = "Tu reporte ha sido $nuevo_estado. " . ($comentario ? "Comentario: $comentario" : "");
    $stmt->bindParam(':numero_documento', $reporte['numero_documento']);
    $stmt->bindParam(':titulo', $titulo);
    $stmt->bindParam(':mensaje', $mensaje);
    $stmt->bindParam(':id_referencia', $id_reporte);
    $stmt->execute();
    
    // Commit de la transacción
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => "Reporte $nuevo_estado exitosamente"]);
    
} catch(PDOException $e) {
    // Rollback en caso de error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}
?>