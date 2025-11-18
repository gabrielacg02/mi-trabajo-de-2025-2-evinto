<?php
session_start();
require_once 'db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

if (!isset($_POST['id_evidencia'])) {
    echo json_encode(['success' => false, 'message' => 'ID de evidencia no proporcionado']);
    exit();
}

$id_evidencia = $_POST['id_evidencia'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Primero obtener la información de la evidencia para verificar permisos
    $stmt = $conn->prepare("SELECT ei.*, ri.numero_documento 
                           FROM evidencias_incidente ei
                           JOIN reportes_incidente ri ON ei.id_reporte = ri.id_reporte
                           WHERE ei.id_evidencia = :id_evidencia");
    $stmt->bindParam(':id_evidencia', $id_evidencia);
    $stmt->execute();
    $evidencia = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$evidencia) {
        echo json_encode(['success' => false, 'message' => 'Evidencia no encontrada']);
        exit();
    }
    
    // Verificar permisos (solo administrador, celador o el dueño del reporte puede eliminar)
    $usuario = $_SESSION['usuario'];
    if ($usuario['id_rol'] != 5 && $usuario['id_rol'] != 4 && $evidencia['numero_documento'] != $usuario['numero_documento']) {
        echo json_encode(['success' => false, 'message' => 'No tienes permisos para eliminar esta evidencia']);
        exit();
    }
    
    // Eliminar el archivo físico
    if (file_exists($evidencia['ruta_archivo'])) {
        unlink($evidencia['ruta_archivo']);
    }
    
    // Eliminar el registro de la base de datos
    $stmt = $conn->prepare("DELETE FROM evidencias_incidente WHERE id_evidencia = :id_evidencia");
    $stmt->bindParam(':id_evidencia', $id_evidencia);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Evidencia eliminada correctamente']);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
}