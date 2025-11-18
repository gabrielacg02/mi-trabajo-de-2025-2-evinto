<?php
$usuario = $usuario ?? ['nombre_completo' => '', 'id_rol' => 5, 'numero_documento' => ''];
$error = $error ?? '';
$success = $success ?? '';
$accesos = $accesos ?? [];
$estadisticas = $estadisticas ?? [
    'total_accesos' => 0,
    'usuarios_unicos' => 0,
    'entradas' => 0,
    'salidas' => 0
];
$personas_campus = $personas_campus ?? [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Accesos - Seguridad Universitaria</title>
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
        
        .stat-card {
            text-align: center;
            padding: 20px;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
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
        
        .badge-primary {
            background-color: var(--secondary-color);
        }
        
        .badge-success {
            background-color: var(--success-color);
        }
        
        .badge-danger {
            background-color: var(--accent-color);
        }
        
        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
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
    <?php $activeSection = 'accesos'; ?>
    @include('layouts.admin-sidebar')

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-door-open me-2"></i> Control de Accesos</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registrarAccesoModal">
                    <i class="fas fa-plus me-2"></i> Registrar Acceso
                </button>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <!-- Estadísticas -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="stat-number"><?= $estadisticas['total_accesos'] ?></div>
                        <div class="stat-label">Total Accesos Hoy</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="stat-number"><?= $estadisticas['usuarios_unicos'] ?></div>
                        <div class="stat-label">Usuarios Únicos</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="stat-number"><?= $estadisticas['entradas'] ?></div>
                        <div class="stat-label">Entradas</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="stat-number"><?= $estadisticas['salidas'] ?></div>
                        <div class="stat-label">Salidas</div>
                    </div>
                </div>
            </div>
            
            <!-- Personas en el Campus -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-users me-2"></i> Personas Actualmente en el Campus</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($personas_campus)): ?>
                                <div class="alert alert-info">No hay personas registradas en el campus</div>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($personas_campus as $persona): ?>
                                        <div class="col-md-4 mb-3">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h6 class="card-title"><?= htmlspecialchars($persona['nombres'] . ' ' . $persona['apellidos']) ?></h6>
                                                    <p class="card-text small text-muted">
                                                        <i class="fas fa-id-card me-1"></i> <?= htmlspecialchars($persona['documento']) ?><br>
                                                        <i class="fas fa-user-tag me-1"></i> <?= htmlspecialchars($persona['rol']) ?><br>
                                                        <i class="fas fa-clock me-1"></i> <?= date('H:i', strtotime($persona['ultimo_movimiento'])) ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Historial de Accesos -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-history me-2"></i> Historial de Accesos</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($accesos)): ?>
                                <div class="alert alert-info">No hay registros de acceso</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Usuario</th>
                                                <th>Documento</th>
                                                <th>Rol</th>
                                                <th>Tipo</th>
                                                <th>Fecha/Hora</th>
                                                <th>Observaciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($accesos as $acceso): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($acceso['nombres'] . ' ' . $acceso['apellidos']) ?></td>
                                                    <td><?= htmlspecialchars($acceso['numero_documento']) ?></td>
                                                    <td><?= htmlspecialchars($acceso['rol']) ?></td>
                                                    <td>
                                                        <span class="badge <?= $acceso['tipo_movimiento'] == 'entrada' ? 'badge-success' : 'badge-danger' ?>">
                                                            <?= ucfirst($acceso['tipo_movimiento']) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= date('d/m/Y H:i', strtotime($acceso['fecha_hora'])) ?></td>
                                                    <td><?= htmlspecialchars($acceso['observaciones'] ?? '') ?></td>
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

    <!-- Modal Registrar Acceso -->
    <div class="modal fade" id="registrarAccesoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Acceso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="numero_documento" class="form-label">Número de Documento</label>
                            <input type="text" class="form-control" id="numero_documento" name="numero_documento" required>
                        </div>
                        <div class="mb-3">
                            <label for="tipo_movimiento" class="form-label">Tipo de Movimiento</label>
                            <select class="form-select" id="tipo_movimiento" name="tipo_movimiento" required>
                                <option value="">Seleccionar...</option>
                                <option value="entrada">Entrada</option>
                                <option value="salida">Salida</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="registrar_acceso" class="btn btn-primary">Registrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>