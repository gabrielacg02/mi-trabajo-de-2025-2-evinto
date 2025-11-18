<?php
$usuario = $usuario ?? ['nombre_completo' => '', 'id_rol' => 5, 'numero_documento' => ''];
$error = $error ?? '';
$success = $success ?? '';
$tipos_incidente = $tipos_incidente ?? [];
$roles = $roles ?? [];
$configuracion_general = $configuracion_general ?? [
    'nombre_institucion' => 'Universidad de Prueba',
    'email_contacto' => 'contacto@universidad.edu',
    'telefono_contacto' => '123-456-7890'
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Seguridad Universitaria</title>
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
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table thead th {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }
        
        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .btn-warning {
            background-color: var(--warning-color);
            border-color: var(--warning-color);
        }
        
        .btn-danger {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
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
    <?php $activeSection = 'configuracion'; ?>
    @include('layouts.admin-sidebar')

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-cog me-2"></i> Configuración del Sistema</h2>
                <button class="btn btn-primary" onclick="guardarConfiguracion()">
                    <i class="fas fa-save me-2"></i> Guardar Cambios
                </button>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <!-- Configuración General -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-university me-2"></i> Configuración General</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nombre_institucion" class="form-label">Nombre de la Institución</label>
                                            <input type="text" class="form-control" id="nombre_institucion" name="nombre_institucion" value="<?= htmlspecialchars($configuracion_general['nombre_institucion']) ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email_contacto" class="form-label">Email de Contacto</label>
                                            <input type="email" class="form-control" id="email_contacto" name="email_contacto" value="<?= htmlspecialchars($configuracion_general['email_contacto']) ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="telefono_contacto" class="form-label">Teléfono de Contacto</label>
                                            <input type="text" class="form-control" id="telefono_contacto" name="telefono_contacto" value="<?= htmlspecialchars($configuracion_general['telefono_contacto']) ?>">
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" name="guardar_general" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Guardar Configuración General
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tipos de Incidente -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5><i class="fas fa-exclamation-triangle me-2"></i> Tipos de Incidente</h5>
                            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#agregarTipoModal">
                                <i class="fas fa-plus me-1"></i> Agregar Tipo
                            </button>
                        </div>
                        <div class="card-body">
                            <?php if (empty($tipos_incidente)): ?>
                                <div class="alert alert-info">No hay tipos de incidente configurados</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nombre</th>
                                                <th>Descripción</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($tipos_incidente as $tipo): ?>
                                                <tr>
                                                    <td><?= $tipo['id_tipo'] ?></td>
                                                    <td><?= htmlspecialchars($tipo['nombre']) ?></td>
                                                    <td><?= htmlspecialchars($tipo['descripcion']) ?></td>
                                                    <td>
                                                        <span class="badge <?= $tipo['activo'] ? 'bg-success' : 'bg-danger' ?>">
                                                            <?= $tipo['activo'] ? 'Activo' : 'Inactivo' ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-warning btn-sm" onclick="editarTipo(<?= $tipo['id_tipo'] ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-danger btn-sm" onclick="eliminarTipo(<?= $tipo['id_tipo'] ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Roles del Sistema -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-user-tag me-2"></i> Roles del Sistema</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($roles)): ?>
                                <div class="alert alert-info">No hay roles configurados</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nombre</th>
                                                <th>Descripción</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($roles as $rol): ?>
                                                <tr>
                                                    <td><?= $rol['id_rol'] ?></td>
                                                    <td><?= htmlspecialchars($rol['nombre']) ?></td>
                                                    <td><?= htmlspecialchars($rol['descripcion']) ?></td>
                                                    <td>
                                                        <span class="badge <?= $rol['activo'] ? 'bg-success' : 'bg-danger' ?>">
                                                            <?= $rol['activo'] ? 'Activo' : 'Inactivo' ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-warning btn-sm" onclick="editarRol(<?= $rol['id_rol'] ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Tipo de Incidente -->
    <div class="modal fade" id="agregarTipoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Tipo de Incidente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nombre_tipo" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre_tipo" name="nombre_tipo" required>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion_tipo" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion_tipo" name="descripcion_tipo" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="agregar_tipo" class="btn btn-primary">Agregar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editarTipo(id) {
            // Implementar edición de tipo
            alert('Función de edición en desarrollo para tipo ID: ' + id);
        }
        
        function eliminarTipo(id) {
            if (confirm('¿Estás seguro de que quieres eliminar este tipo de incidente?')) {
                // Implementar eliminación de tipo
                alert('Función de eliminación en desarrollo para tipo ID: ' + id);
            }
        }
        
        function editarRol(id) {
            // Implementar edición de rol
            alert('Función de edición en desarrollo para rol ID: ' + id);
        }
        
        function guardarConfiguracion() {
            // Implementar guardado de configuración
            alert('Función de guardado en desarrollo');
        }
    </script>
</body>
</html>