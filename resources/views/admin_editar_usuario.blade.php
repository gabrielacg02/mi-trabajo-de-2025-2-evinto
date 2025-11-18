<?php
session_start();
require_once 'db_config.php';

// Verificar autenticación y rol de administrador
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['usuario']['id_rol'] != 5) { // 5 = Administrador
    header('Location: index.php');
    exit();
}

// Obtener datos del usuario
$usuario = $_SESSION['usuario'];

$error = '';
$success = '';

// Obtener ID del usuario a editar
$usuario_id = $_GET['id'] ?? '';
if (empty($usuario_id)) {
    header('Location: admin_usuarios.php');
    exit();
}

// Obtener información del usuario a editar
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $conn->prepare("
        SELECT u.*, r.nombre_rol 
        FROM usuarios u
        JOIN roles r ON u.id_rol = r.id_rol
        WHERE u.numero_documento = ?
    ");
    $stmt->execute([$usuario_id]);
    $usuario_editar = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario_editar) {
        header('Location: admin_usuarios.php');
        exit();
    }
    
    // Obtener roles para el formulario
    $stmt = $conn->query("SELECT * FROM roles ORDER BY nombre_rol");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Error de conexión: " . $e->getMessage();
}

// Procesar formulario de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si es un cambio de contraseña
    if (isset($_POST['nueva_contrasena']) && !empty($_POST['nueva_contrasena'])) {
        $nueva_contrasena = $_POST['nueva_contrasena'];
        $confirmar_contrasena = $_POST['confirmar_contrasena'];
        
        if ($nueva_contrasena !== $confirmar_contrasena) {
            $error = "Las contraseñas no coinciden";
        } else if (strlen($nueva_contrasena) < 8) {
            $error = "La contraseña debe tener al menos 8 caracteres";
        } else {
            try {
                $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Encriptar la nueva contraseña (SHA-256 como en sp_autenticar_usuario)
                $contrasena_hash = hash('sha256', $nueva_contrasena);
                
                $stmt = $conn->prepare("
                    UPDATE usuarios 
                    SET contrasena = ?
                    WHERE numero_documento = ?
                ");
                $stmt->execute([$contrasena_hash, $usuario_id]);
                
                $success = 'Contraseña actualizada correctamente';
                
                // Registrar en auditoría
                $stmt = $conn->prepare("
                    INSERT INTO auditoria (numero_documento, accion, tabla_afectada, id_registro_afectado, datos_nuevos)
                    VALUES (?, 'Cambio de contraseña', 'usuarios', ?, 'Contraseña cambiada por administrador')
                ");
                $stmt->execute([$usuario['numero_documento'], $usuario_id]);
                
            } catch(PDOException $e) {
                $error = "Error al actualizar contraseña: " . $e->getMessage();
            }
        }
    } else {
        // Procesar actualización de datos normales
        $nombres = $_POST['nombres'] ?? '';
        $apellidos = $_POST['apellidos'] ?? '';
        $correo = $_POST['correo'] ?? '';
        $id_rol = $_POST['id_rol'] ?? '';
        $estado = $_POST['estado'] ?? '';
        
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $conn->prepare("
                UPDATE usuarios 
                SET nombres = ?, apellidos = ?, correo = ?, id_rol = ?, estado = ?
                WHERE numero_documento = ?
            ");
            $stmt->execute([$nombres, $apellidos, $correo, $id_rol, $estado, $usuario_id]);
            
            $success = 'Usuario actualizado correctamente';
            
            // Actualizar datos del usuario en la variable
            $usuario_editar['nombres'] = $nombres;
            $usuario_editar['apellidos'] = $apellidos;
            $usuario_editar['correo'] = $correo;
            $usuario_editar['id_rol'] = $id_rol;
            $usuario_editar['estado'] = $estado;
            
            // Actualizar también el nombre del rol
            foreach ($roles as $rol) {
                if ($rol['id_rol'] == $id_rol) {
                    $usuario_editar['nombre_rol'] = $rol['nombre_rol'];
                    break;
                }
            }
            
            // Registrar en auditoría
            $stmt = $conn->prepare("
                INSERT INTO auditoria (numero_documento, accion, tabla_afectada, id_registro_afectado, datos_nuevos)
                VALUES (?, 'Actualización de usuario', 'usuarios', ?, ?)
            ");
            $datos_nuevos = "Nombre: $nombres $apellidos, Correo: $correo, Rol: $id_rol, Estado: $estado";
            $stmt->execute([$usuario['numero_documento'], $usuario_id, $datos_nuevos]);
            
        } catch(PDOException $e) {
            $error = "Error al actualizar usuario: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - Seguridad Universitaria</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --info-color: #2980b9;
        }
        
        body {
            font-family: 'Open Sans', sans-serif;
            color: #333;
            background-color: #f8f9fa;
        }
        
        .sidebar {
            background-color: var(--primary-color);
            color: white;
            height: 100vh;
            position: fixed;
            width: 250px;
            transition: all 0.3s;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 20px;
            background-color: rgba(0,0,0,0.2);
        }
        
        .sidebar ul.components {
            padding: 20px 0;
        }
        
        .sidebar ul li a {
            padding: 10px 20px;
            color: rgba(255,255,255,0.8);
            display: block;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar ul li a:hover {
            color: white;
            background-color: rgba(0,0,0,0.2);
        }
        
        .sidebar ul li.active > a {
            color: white;
            background-color: rgba(0,0,0,0.2);
        }
        
        .sidebar ul ul a {
            padding-left: 30px;
            font-size: 0.9em;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .card:hover {
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            transform: translateY(-5px);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            font-weight: 600;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            padding: 15px;
            background-color: rgba(0,0,0,0.1);
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .user-profile img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
        }
        
        .user-info h5 {
            margin-bottom: 0;
        }
        
        .user-info p {
            margin-bottom: 0;
            color: rgba(255,255,255,0.7);
            font-size: 0.9em;
        }
        
        .form-container {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .form-title {
            color: var(--primary-color);
            margin-bottom: 30px;
            position: relative;
            padding-bottom: 15px;
        }
        
        .form-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--secondary-color);
        }
        
        .form-label {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }
        
        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }
        
        .user-details-card {
            border-left: 4px solid var(--secondary-color);
        }
        
        .user-details-item {
            margin-bottom: 15px;
        }
        
        .user-details-label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .user-details-value {
            background-color: #f8f9fa;
            padding: 8px 12px;
            border-radius: 5px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
            }
            
            .sidebar.active {
                margin-left: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .main-content.active {
                margin-left: 250px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Seguridad Universitaria</h3>
        </div>
        
        <div class="user-profile">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($usuario['nombre_completo']) ?>&background=3498db&color=fff" alt="Perfil">
            <div class="user-info">
                <h5><?= htmlspecialchars($usuario['nombre_completo']) ?></h5>
                <p>Administrador</p>
            </div>
        </div>
        
        <ul class="list-unstyled components">
            <li>
                <a href="panel_administrador.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
            </li>
            <li class="active">
                <a href="#usuariosSubmenu" data-bs-toggle="collapse" aria-expanded="true" class="dropdown-toggle">
                    <i class="fas fa-users me-2"></i> Usuarios
                </a>
                <ul class="collapse list-unstyled show" id="usuariosSubmenu">
                    <li><a href="admin_usuarios.php"><i class="fas fa-list me-2"></i> Listar Usuarios</a></li>
                    <li><a href="admin_registrar_usuario.php"><i class="fas fa-user-plus me-2"></i> Registrar Usuario</a></li>
                </ul>
            </li>
            <li>
                <a href="#reportesSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                    <i class="fas fa-exclamation-triangle me-2"></i> Reportes
                </a>
                <ul class="collapse list-unstyled" id="reportesSubmenu">
                    <li><a href="admin_reportes_incidentes.php"><i class="fas fa-list me-2"></i> Incidentes</a></li>
                    <li><a href="admin_reportes_pendientes.php"><i class="fas fa-clock me-2"></i> Pendientes</a></li>
                </ul>
            </li>
            <li>
                <a href="#objetosSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                    <i class="fas fa-box-open me-2"></i> Objetos
                </a>
                <ul class="collapse list-unstyled" id="objetosSubmenu">
                    <li><a href="admin_objetos_perdidos.php"><i class="fas fa-list me-2"></i> Objetos Perdidos</a></li>
                    <li><a href="admin_objetos_pendientes.php"><i class="fas fa-clock me-2"></i> Pendientes</a></li>
                </ul>
            </li>
            <li>
                <a href="admin_accesos.php"><i class="fas fa-door-open me-2"></i> Control de Accesos</a>
            </li>
            <li>
                <a href="admin_auditoria.php"><i class="fas fa-clipboard-list me-2"></i> Auditoría</a>
            </li>
            <li>
                <a href="admin_configuracion.php"><i class="fas fa-cog me-2"></i> Configuración</a>
            </li>
            <li>
                <a href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="fas fa-user-edit me-2"></i> Editar Usuario</h2>
                <a href="admin_usuarios.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Volver al listado
                </a>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="card user-details-card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-id-card me-2"></i> Información del Usuario</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($usuario_editar['nombres'] . ' ' . $usuario_editar['apellidos']) ?>&background=<?= substr(md5($usuario_editar['id_rol']), 0, 6) ?>&color=fff" 
                                     alt="Avatar" class="rounded-circle" width="120">
                                <h4 class="mt-3"><?= htmlspecialchars($usuario_editar['nombres'] . ' ' . $usuario_editar['apellidos']) ?></h4>
                                <span class="badge bg-primary"><?= htmlspecialchars($usuario_editar['nombre_rol']) ?></span>
                            </div>
                            
                            <div class="user-details-item">
                                <div class="user-details-label">Documento</div>
                                <div class="user-details-value">
                                    <?= htmlspecialchars($usuario_editar['tipo_documento']) ?> <?= htmlspecialchars($usuario_editar['numero_documento']) ?>
                                </div>
                            </div>
                            
                            <div class="user-details-item">
                                <div class="user-details-label">Correo Electrónico</div>
                                <div class="user-details-value">
                                    <?= htmlspecialchars($usuario_editar['correo']) ?>
                                </div>
                            </div>
                            
                            <div class="user-details-item">
                                <div class="user-details-label">Estado</div>
                                <div class="user-details-value">
                                    <span class="badge <?= $usuario_editar['estado'] == 'activo' ? 'bg-success' : ($usuario_editar['estado'] == 'inactivo' ? 'bg-warning' : 'bg-danger') ?>">
                                        <?= ucfirst($usuario_editar['estado']) ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="user-details-item">
                                <div class="user-details-label">Registrado el</div>
                                <div class="user-details-value">
                                    <?= date('d/m/Y H:i', strtotime($usuario_editar['creado_en'])) ?>
                                </div>
                            </div>
                            
                            <div class="user-details-item">
                                <div class="user-details-label">Último acceso</div>
                                <div class="user-details-value">
                                    <?= $usuario_editar['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($usuario_editar['ultimo_acceso'])) : 'Nunca' ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div class="form-container">
                        <h3 class="form-title">Editar Información</h3>
                        <form method="POST">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nombres" class="form-label">Nombres</label>
                                    <input type="text" class="form-control" id="nombres" name="nombres" 
                                           value="<?= htmlspecialchars($usuario_editar['nombres']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="apellidos" class="form-label">Apellidos</label>
                                    <input type="text" class="form-control" id="apellidos" name="apellidos" 
                                           value="<?= htmlspecialchars($usuario_editar['apellidos']) ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="correo" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="correo" name="correo" 
                                       value="<?= htmlspecialchars($usuario_editar['correo']) ?>" required>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="id_rol" class="form-label">Rol del Usuario</label>
                                    <select class="form-select" id="id_rol" name="id_rol" required>
                                        <?php foreach ($roles as $rol): ?>
                                            <option value="<?= $rol['id_rol'] ?>" <?= ($usuario_editar['id_rol'] == $rol['id_rol']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($rol['nombre_rol']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="estado" class="form-label">Estado</label>
                                    <select class="form-select" id="estado" name="estado" required>
                                        <option value="activo" <?= ($usuario_editar['estado'] == 'activo') ? 'selected' : '' ?>>Activo</option>
                                        <option value="inactivo" <?= ($usuario_editar['estado'] == 'inactivo') ? 'selected' : '' ?>>Inactivo</option>
                                        <option value="bloqueado" <?= ($usuario_editar['estado'] == 'bloqueado') ? 'selected' : '' ?>>Bloqueado</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i> Guardar Cambios
                                </button>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        
                        <h5 class="mb-3"><i class="fas fa-key me-2"></i> Cambiar Contraseña</h5>
                        <form method="POST">
                            <input type="hidden" name="usuario_id" value="<?= htmlspecialchars($usuario_editar['numero_documento']) ?>">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nueva_contrasena" class="form-label">Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="nueva_contrasena" name="nueva_contrasena" minlength="8">
                                    <small class="text-muted">Mínimo 8 caracteres</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="confirmar_contrasena" class="form-label">Confirmar Contraseña</label>
                                    <input type="password" class="form-control" id="confirmar_contrasena" name="confirmar_contrasena" minlength="8">
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-key me-2"></i> Cambiar Contraseña
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Validación del formulario de cambio de contraseña
        const formCambioContrasena = document.querySelector('form[method="POST"]:last-of-type');
        
        if (formCambioContrasena) {
            formCambioContrasena.addEventListener('submit', function(e) {
                const nuevaContrasena = document.getElementById('nueva_contrasena').value;
                const confirmarContrasena = document.getElementById('confirmar_contrasena').value;
                
                if (nuevaContrasena !== confirmarContrasena) {
                    e.preventDefault();
                    alert('Las contraseñas no coinciden');
                    return false;
                }
                
                if (nuevaContrasena.length < 8) {
                    e.preventDefault();
                    alert('La contraseña debe tener al menos 8 caracteres');
                    return false;
                }
                
                return true;
            });
        }
    });
    </script>
</body>
</html>