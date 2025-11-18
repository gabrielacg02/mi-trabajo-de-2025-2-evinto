<?php
$usuario = $usuario ?? ['nombre_completo' => '', 'id_rol' => 5];
$error = $error ?? '';
$success = $success ?? '';
$roles = $roles ?? [];

// Procesar formulario de registro (legacy, sin conexión directa)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero_documento = $_POST['numero_documento'] ?? '';
    $tipo_documento = $_POST['tipo_documento'] ?? '';
    $nombres = $_POST['nombres'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';
    $confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';
    $id_rol = $_POST['id_rol'] ?? '';
    
    if ($contrasena !== $confirmar_contrasena) {
        $error = 'Las contraseñas no coinciden';
    }
}

// Roles por defecto si no vienen de controlador
if (empty($roles)) {
    $roles = [
        ['id_rol' => 1, 'nombre_rol' => 'Estudiante'],
        ['id_rol' => 2, 'nombre_rol' => 'Docente'],
        ['id_rol' => 3, 'nombre_rol' => 'Administrativo'],
        ['id_rol' => 4, 'nombre_rol' => 'Celador'],
        ['id_rol' => 5, 'nombre_rol' => 'Administrador'],
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Usuario - Seguridad Universitaria</title>
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
        
        .password-strength {
            height: 5px;
            background-color: #eee;
            margin-top: 5px;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0%;
            background-color: #e74c3c;
            transition: all 0.3s ease;
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
    <!-- Sidebar Global -->
    <?php $activeSection = 'usuarios.registrar'; ?>
    @include('layouts.admin-sidebar')

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="fas fa-user-plus me-2"></i> Registrar Nuevo Usuario</h2>
                <a href="<?php echo route('admin.usuarios'); ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Volver al listado
                </a>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="form-container">
                        <h3 class="form-title">Información del Usuario</h3>
                        <form method="POST" id="registroForm">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="tipo_documento" class="form-label">Tipo de Documento</label>
                                    <select class="form-select" id="tipo_documento" name="tipo_documento" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="CC" <?= (isset($_POST['tipo_documento']) && $_POST['tipo_documento'] == 'CC') ? 'selected' : '' ?>>Cédula de Ciudadanía</option>
                                        <option value="TI" <?= (isset($_POST['tipo_documento']) && $_POST['tipo_documento'] == 'TI') ? 'selected' : '' ?>>Tarjeta de Identidad</option>
                                        <option value="CE" <?= (isset($_POST['tipo_documento']) && $_POST['tipo_documento'] == 'CE') ? 'selected' : '' ?>>Cédula de Extranjería</option>
                                        <option value="PA" <?= (isset($_POST['tipo_documento']) && $_POST['tipo_documento'] == 'PA') ? 'selected' : '' ?>>Pasaporte</option>
                                    </select>
                                </div>
                                <div class="col-md-9">
                                    <label for="numero_documento" class="form-label">Número de Documento</label>
                                    <input type="text" class="form-control" id="numero_documento" name="numero_documento" 
                                           value="<?= htmlspecialchars($_POST['numero_documento'] ?? '') ?>" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nombres" class="form-label">Nombres</label>
                                    <input type="text" class="form-control" id="nombres" name="nombres" 
                                           value="<?= htmlspecialchars($_POST['nombres'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="apellidos" class="form-label">Apellidos</label>
                                    <input type="text" class="form-control" id="apellidos" name="apellidos" 
                                           value="<?= htmlspecialchars($_POST['apellidos'] ?? '') ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="correo" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="correo" name="correo" 
                                       value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="id_rol" class="form-label">Rol del Usuario</label>
                                <select class="form-select" id="id_rol" name="id_rol" required>
                                    <option value="">Seleccionar rol...</option>
                                    <?php foreach ($roles as $rol): ?>
                                        <option value="<?= $rol['id_rol'] ?>" <?= (isset($_POST['id_rol']) && $_POST['id_rol'] == $rol['id_rol']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($rol['nombre_rol']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="contrasena" class="form-label">Contraseña</label>
                                    <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                                    <div class="password-strength">
                                        <div class="password-strength-bar" id="passwordStrengthBar"></div>
                                    </div>
                                    <small class="text-muted">Mínimo 8 caracteres</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="confirmar_contrasena" class="form-label">Confirmar Contraseña</label>
                                    <input type="password" class="form-control" id="confirmar_contrasena" name="confirmar_contrasena" required>
                                    <div id="passwordMatch" class="mt-1"></div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i> Registrar Usuario
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
        // Validación de contraseña en tiempo real
        document.getElementById('contrasena').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrengthBar');
            let strength = 0;
            
            // Verificar longitud
            if (password.length >= 8) strength += 1;
            if (password.length >= 12) strength += 1;
            
            // Verificar caracteres especiales
            if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength += 1;
            
            // Verificar números
            if (/\d/.test(password)) strength += 1;
            
            // Verificar mayúsculas y minúsculas
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 1;
            
            // Actualizar barra de fortaleza
            let width = 0;
            let color = '#e74c3c'; // Rojo
            
            if (strength >= 4) {
                width = 100;
                color = '#27ae60'; // Verde
            } else if (strength >= 2) {
                width = 66;
                color = '#f39c12'; // Amarillo
            } else if (strength >= 1) {
                width = 33;
            }
            
            strengthBar.style.width = width + '%';
            strengthBar.style.backgroundColor = color;
        });
        
        // Verificar coincidencia de contraseñas
        document.getElementById('confirmar_contrasena').addEventListener('input', function() {
            const password = document.getElementById('contrasena').value;
            const confirmPassword = this.value;
            const matchDiv = document.getElementById('passwordMatch');
            
            if (confirmPassword === '') {
                matchDiv.innerHTML = '';
            } else if (password === confirmPassword) {
                matchDiv.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> Las contraseñas coinciden</span>';
            } else {
                matchDiv.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle"></i> Las contraseñas no coinciden</span>';
            }
        });
        
        // Validación de formulario antes de enviar
        document.getElementById('registroForm').addEventListener('submit', function(e) {
            const password = document.getElementById('contrasena').value;
            const confirmPassword = document.getElementById('confirmar_contrasena').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
                return false;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 8 caracteres');
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>