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

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar que el objeto pertenece al usuario
    $stmt = $conn->prepare("
        SELECT numero_documento FROM objetos_perdidos
        WHERE id_objeto = :id_objeto
    ");
    $stmt->bindParam(':id_objeto', $id_objeto);
    $stmt->execute();
    $objeto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($objeto && $objeto['numero_documento'] == $usuario['numero_documento']) {
        // Llamar al procedimiento almacenado
        $stmt = $conn->prepare("CALL sp_marcar_objeto_encontrado(?, ?, @p_resultado, @p_mensaje)");
        $stmt->bindParam(1, $id_objeto, PDO::PARAM_INT);
        $stmt->bindParam(2, $usuario['numero_documento'], PDO::PARAM_STR);
        $stmt->execute();
        
        $stmt = $conn->query("SELECT @p_resultado AS resultado, @p_mensaje AS mensaje");
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado['resultado']) {
            $_SESSION['success'] = $resultado['mensaje'];
        } else {
            $_SESSION['error'] = $resultado['mensaje'];
        }
    } else {
        $_SESSION['error'] = 'No tienes permiso para realizar esta acción';
    }
    
    header('Location: estudiante_objeto_detalle.php?id=' . $id_objeto);
    exit();
    
} catch(PDOException $e) {
    $_SESSION['error'] = "Error de conexión: " . $e->getMessage();
    header('Location: panel_objetos.php');
    exit();
}