<?php
session_start();
require_once 'db_config.php';

// Verificar autenticación y rol de administrador
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] != 5) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit();
}

// Obtener datos de la solicitud
$data = json_decode(file_get_contents('php://input'), true);
$accion = isset($data['accion']) ? $data['accion'] : '';
$documento = isset($data['documento']) ? $data['documento'] : '';

if (empty($accion) || empty($documento)) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit();
}

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $resultado = false;
    $mensaje = '';
    
    switch ($accion) {
        case 'activar':
            $stmt = $conn->prepare("UPDATE usuarios SET estado = 'activo', intentos_fallidos = 0 WHERE numero_documento = ?");
            $stmt->execute([$documento]);
            $resultado = true;
            $mensaje = 'Usuario activado correctamente';
            break;
            
        case 'desactivar':
            $stmt = $conn->prepare("UPDATE usuarios SET estado = 'inactivo' WHERE numero_documento = ?");
            $stmt->execute([$documento]);
            $resultado = true;
            $mensaje = 'Usuario desactivado correctamente';
            break;
            
        case 'bloquear':
            $stmt = $conn->prepare("UPDATE usuarios SET estado = 'bloqueado' WHERE numero_documento = ?");
            $stmt->execute([$documento]);
            $resultado = true;
            $mensaje = 'Usuario bloqueado correctamente';
            break;
            
        case 'eliminar':
            // Verificar que el usuario a eliminar no sea el mismo que está ejecutando la acción
            if ($documento == $_SESSION['usuario']['numero_documento']) {
                $mensaje = 'No puedes eliminarte a ti mismo';
            } else {
                $stmt = $conn->prepare("DELETE FROM usuarios WHERE numero_documento = ?");
                $stmt->execute([$documento]);
                $resultado = true;
                $mensaje = 'Usuario eliminado correctamente';
            }
            break;
            
        default:
            $mensaje = 'Acción no válida';
    }
    
    // Registrar en auditoría
    if ($resultado) {
        $auditoria = $conn->prepare("INSERT INTO auditoria (numero_documento, accion, tabla_afectada, datos_nuevos) 
                                   VALUES (?, ?, 'usuarios', ?)");
        $auditoria->execute([
            $_SESSION['usuario']['numero_documento'],
            'Acción de usuario: ' . $accion,
            'Documento afectado: ' . $documento
        ]);
    }
    
    echo json_encode(['success' => $resultado, 'message' => $mensaje]);
    
} catch(PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>