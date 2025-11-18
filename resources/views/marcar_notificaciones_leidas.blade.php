<?php
session_start();
require_once 'db_config.php';

// Verificar autenticación
if (!isset($_SESSION['usuario'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit();
}

// Solo permitir peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit();
}

$usuario = $_SESSION['usuario'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Marcar todas las notificaciones del usuario como leídas
    $stmt = $conn->prepare("
        UPDATE notificaciones 
        SET leida = 1 
        WHERE numero_documento = :numero_documento AND leida = 0
    ");
    $stmt->bindParam(':numero_documento', $usuario['numero_documento']);
    $stmt->execute();
    
    // Respuesta JSON
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    
} catch(PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>