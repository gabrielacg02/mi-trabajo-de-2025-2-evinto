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
    
    // Verificar que el objeto existe y no es del usuario
    $stmt = $conn->prepare("
        SELECT numero_documento, estado FROM objetos_perdidos
        WHERE id_objeto = :id_objeto
    ");
    $stmt->bindParam(':id_objeto', $id_objeto);
    $stmt->execute();
    $objeto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$objeto) {
        $_SESSION['error'] = 'Objeto no encontrado';
        header('Location: panel_objetos.php');
        exit();
    }
    
    if ($objeto['numero_documento'] == $usuario['numero_documento']) {
        $_SESSION['error'] = 'No puedes reclamar tu propio objeto';
        header('Location: estudiante_objeto_detalle.php?id=' . $id_objeto);
        exit();
    }
    
    if ($objeto['estado'] != 'perdido') {
        $_SESSION['error'] = 'Este objeto ya ha sido reclamado';
        header('Location: estudiante_objeto_detalle.php?id=' . $id_objeto);
        exit();
    }
    
    // Enviar mensaje al reportante
    $mensaje = "El usuario " . $usuario['nombres'] . " " . $usuario['apellidos'] . " está interesado en reclamar este objeto. Por favor contacta con él para verificar la propiedad.";
    
    $stmt = $conn->prepare("
        INSERT INTO mensajes_objetos (id_objeto, numero_documento, mensaje)
        VALUES (:id_objeto, :numero_documento, :mensaje)
    ");
    $stmt->bindParam(':id_objeto', $id_objeto);
    $stmt->bindParam(':numero_documento', $usuario['numero_documento']);
    $stmt->bindParam(':mensaje', $mensaje);
    $stmt->execute();
    
    // Notificar al reportante
    $stmt = $conn->prepare("
        INSERT INTO notificaciones (numero_documento, titulo, mensaje, tipo, id_referencia)
        VALUES (:numero_documento, 'Reclamación de objeto', :mensaje, 'objeto', :id_objeto)
    ");
    $stmt->bindParam(':numero_documento', $objeto['numero_documento']);
    $stmt->bindParam(':mensaje', $mensaje);
    $stmt->bindParam(':id_objeto', $id_objeto);
    $stmt->execute();
    
    $_SESSION['success'] = 'Se ha enviado una solicitud de reclamación al dueño del objeto';
    header('Location: estudiante_objeto_detalle.php?id=' . $id_objeto);
    exit();
    
} catch(PDOException $e) {
    $_SESSION['error'] = "Error de conexión: " . $e->getMessage();
    header('Location: panel_objetos.php');
    exit();
}