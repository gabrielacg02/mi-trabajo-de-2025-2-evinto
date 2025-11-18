<?php
$usuario = $usuario ?? ['nombre_completo' => '', 'id_rol' => 5, 'numero_documento' => ''];
$error = $error ?? '';
$auditoria = $auditoria ?? [];
$fecha_inicio = $fecha_inicio ?? date('Y-m-01');
$fecha_fin = $fecha_fin ?? date('Y-m-d');
$accion = $accion ?? '';
$usuario_filtro = $usuario_filtro ?? '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoría - Seguridad Universitaria</title>
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
    <?php $activeSection = 'auditoria'; ?>
    @include('layouts.admin-sidebar')

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-clipboard-list me-2"></i> Auditoría del Sistema</h2>
                <button class="btn btn-primary" onclick="exportarAuditoria()">
                    <i class="fas fa-download me-2"></i> Exportar
                </button>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <!-- Filtros -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-filter me-2"></i> Filtros de Búsqueda</h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-3">
                                    <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?= $fecha_inicio ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="fecha_fin" class="form-label">Fecha Fin</label>
                                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?= $fecha_fin ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="accion" class="form-label">Acción</label>
                                    <select class="form-select" id="accion" name="accion">
                                        <option value="">Todas las acciones</option>
                                        <option value="CREATE" <?= $accion == 'CREATE' ? 'selected' : '' ?>>Crear</option>
                                        <option value="UPDATE" <?= $accion == 'UPDATE' ? 'selected' : '' ?>>Actualizar</option>
                                        <option value="DELETE" <?= $accion == 'DELETE' ? 'selected' : '' ?>>Eliminar</option>
                                        <option value="LOGIN" <?= $accion == 'LOGIN' ? 'selected' : '' ?>>Inicio de sesión</option>
                                        <option value="LOGOUT" <?= $accion == 'LOGOUT' ? 'selected' : '' ?>>Cierre de sesión</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="usuario_filtro" class="form-label">Usuario</label>
                                    <input type="text" class="form-control" id="usuario_filtro" name="usuario_filtro" placeholder="Buscar por usuario..." value="<?= htmlspecialchars($usuario_filtro) ?>">
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i> Buscar
                                    </button>
                                    <a href="<?php echo route('admin.auditoria'); ?>" class="btn btn-secondary">
                                        <i class="fas fa-refresh me-2"></i> Limpiar
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Registros de Auditoría -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-history me-2"></i> Registros de Auditoría</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($auditoria)): ?>
                                <div class="alert alert-info">No hay registros de auditoría para los filtros seleccionados</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Fecha/Hora</th>
                                                <th>Usuario</th>
                                                <th>Acción</th>
                                                <th>Tabla</th>
                                                <th>Registro ID</th>
                                                <th>Valores Anteriores</th>
                                                <th>Valores Nuevos</th>
                                                <th>IP</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($auditoria as $registro): ?>
                                                <tr>
                                                    <td><?= date('d/m/Y H:i:s', strtotime($registro['fecha_hora'])) ?></td>
                                                    <td><?= htmlspecialchars($registro['nombre_usuario']) ?></td>
                                                    <td>
                                                        <span class="badge <?= $registro['accion'] == 'CREATE' ? 'bg-success' : ($registro['accion'] == 'UPDATE' ? 'bg-warning' : 'bg-danger') ?>">
                                                            <?= $registro['accion'] ?>
                                                        </span>
                                                    </td>
                                                    <td><?= htmlspecialchars($registro['tabla']) ?></td>
                                                    <td><?= htmlspecialchars($registro['registro_id']) ?></td>
                                                    <td>
                                                        <?php if ($registro['valores_anteriores']): ?>
                                                            <button class="btn btn-sm btn-outline-info" onclick="mostrarValores('<?= htmlspecialchars($registro['valores_anteriores']) ?>')">
                                                                Ver
                                                            </button>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($registro['valores_nuevos']): ?>
                                                            <button class="btn btn-sm btn-outline-success" onclick="mostrarValores('<?= htmlspecialchars($registro['valores_nuevos']) ?>')">
                                                                Ver
                                                            </button>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($registro['ip_address']) ?></td>
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

    <!-- Modal para mostrar valores -->
    <div class="modal fade" id="valoresModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalles del Registro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <pre id="valoresContent" class="bg-light p-3 rounded"></pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function mostrarValores(valores) {
            document.getElementById('valoresContent').textContent = valores;
            new bootstrap.Modal(document.getElementById('valoresModal')).show();
        }
        
        function exportarAuditoria() {
            // Implementar exportación de auditoría
            alert('Función de exportación en desarrollo');
        }
    </script>
</body>
</html>