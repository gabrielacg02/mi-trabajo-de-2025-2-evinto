<?php
session_start();
require_once 'db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
    exit();
}

$id_notificacion = $_GET['id'];
$usuario = $_SESSION['usuario'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar que la notificación pertenece al usuario
    $stmt = $conn->prepare("UPDATE notificaciones SET leida = 1 WHERE id_notificacion = :id AND numero_documento = :doc");
    $stmt->bindParam(':id', $id_notificacion);
    $stmt->bindParam(':doc', $usuario['numero_documento']);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de base de datos']);
}
?>