<?php
$usuario = $usuario ?? ['nombre_completo' => ''];
$busqueda = $busqueda ?? '';
$rol_filtro = $rol_filtro ?? '';
$estado_filtro = $estado_filtro ?? '';
$usuarios_por_pagina = $usuarios_por_pagina ?? 10;
$pagina_actual = $pagina_actual ?? 1;
$usuarios = $usuarios ?? [];
$total_usuarios = $total_usuarios ?? 0;
$total_paginas = $total_paginas ?? 0;
$roles = $roles ?? [];
$error = $error ?? null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Seguridad Universitaria</title>
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
        
        .badge-warning {
            background-color: var(--warning-color);
        }
        
        .badge-danger {
            background-color: var(--accent-color);
        }
        
        .search-box {
            position: relative;
            margin-bottom: 20px;
        }
        
        .search-box input {
            padding-left: 40px;
            border-radius: 20px;
            border: 1px solid #ddd;
        }
        
        .search-box i {
            position: absolute;
            left: 15px;
            top: 12px;
            color: #999;
        }
        
        .filter-section {
            background-color: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .pagination .page-link {
            color: var(--primary-color);
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
    <?php $activeSection = 'usuarios.listar'; ?>
    @include('layouts.admin-sidebar')

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="fas fa-users me-2"></i> Gestión de Usuarios</h2>
                <a href="<?php echo route('admin.usuarios.registrar'); ?>" class="btn btn-primary">
                    <i class="fas fa-user-plus me-2"></i> Nuevo Usuario
                </a>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <!-- Filtros y Búsqueda -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="filter-section">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <div class="search-box">
                                    <i class="fas fa-search"></i>
                                    <input type="text" name="busqueda" class="form-control" placeholder="Buscar por nombre, documento o correo..." value="<?= htmlspecialchars($busqueda) ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select name="rol" class="form-select">
                                    <option value="">Todos los roles</option>
                                    <?php foreach ($roles as $rol): ?>
                                        <option value="<?= $rol['id_rol'] ?>" <?= ($rol_filtro == $rol['id_rol'] ? 'selected' : '') ?>>
                                            <?= htmlspecialchars($rol['nombre_rol']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="estado" class="form-select">
                                    <option value="">Todos los estados</option>
                                    <option value="activo" <?= ($estado_filtro == 'activo' ? 'selected' : '') ?>>Activo</option>
                                    <option value="inactivo" <?= ($estado_filtro == 'inactivo' ? 'selected' : '') ?>>Inactivo</option>
                                    <option value="bloqueado" <?= ($estado_filtro == 'bloqueado' ? 'selected' : '') ?>>Bloqueado</option>
                                </select>
                            </div>
                            <div class="col-md-12 text-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-filter me-2"></i>Filtrar
                                </button>
                                <a href="<?php echo route('admin.usuarios'); ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-sync-alt me-2"></i>Limpiar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Tabla de Usuarios -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-list me-2"></i> Listado de Usuarios</span>
                    <div class="text-muted">
                        <?= isset($total_usuarios) ? $total_usuarios : '0' ?> usuarios encontrados
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Usuario</th>
                                    <th>Documento</th>
                                    <th>Correo</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th>Último Acceso</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($usuarios)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">No se encontraron usuarios</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($usuarios as $index => $user): ?>
                                        <tr>
                                            <td><?= ($pagina_actual - 1) * $usuarios_por_pagina + $index + 1 ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['nombres'] . ' ' . $user['apellidos']) ?>&background=<?= substr(md5($user['id_rol']), 0, 6) ?>&color=fff" 
                                                         alt="Avatar" class="user-avatar me-2">
                                                    <div>
                                                        <strong><?= htmlspecialchars($user['nombres'] . ' ' . $user['apellidos']) ?></strong>
                                                        <div class="text-muted small">Registrado: <?= date('d/m/Y', strtotime($user['creado_en'])) ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($user['tipo_documento']) ?> <?= htmlspecialchars($user['numero_documento']) ?></td>
                                            <td><?= htmlspecialchars($user['correo']) ?></td>
                                            <td><?= htmlspecialchars($user['nombre_rol']) ?></td>
                                            <td>
                                                <span class="badge <?= $user['estado'] == 'activo' ? 'bg-success' : ($user['estado'] == 'inactivo' ? 'bg-warning' : 'bg-danger') ?>">
                                                    <?= ucfirst($user['estado']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= $user['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($user['ultimo_acceso'])) : 'Nunca' ?>
                                            </td>
                                            <td>
    <div class="d-flex gap-2">
        <a href="admin_editar_usuario.php?id=<?= $user['numero_documento'] ?>" class="btn btn-sm btn-primary" title="Editar">
            <i class="fas fa-edit"></i>
        </a>
        
        <?php if ($user['estado'] == 'activo'): ?>
            <a href="#" class="btn btn-sm btn-warning btn-desactivar" title="Desactivar">
                <i class="fas fa-user-minus"></i>
            </a>
            <a href="#" class="btn btn-sm btn-danger btn-bloquear" title="Bloquear">
                <i class="fas fa-user-slash"></i>
            </a>
        <?php elseif ($user['estado'] == 'inactivo'): ?>
            <a href="#" class="btn btn-sm btn-success btn-activar" title="Activar">
                <i class="fas fa-user-check"></i>
            </a>
            <a href="#" class="btn btn-sm btn-danger btn-bloquear" title="Bloquear">
                <i class="fas fa-user-slash"></i>
            </a>
        <?php else: // estado bloqueado ?>
            <a href="#" class="btn btn-sm btn-success btn-activar" title="Activar">
                <i class="fas fa-user-check"></i>
            </a>
            <a href="#" class="btn btn-sm btn-secondary btn-desactivar" title="Desactivar">
                <i class="fas fa-user-minus"></i>
            </a>
        <?php endif; ?>
        
        <a href="#" class="btn btn-sm btn-dark btn-eliminar" title="Eliminar">
            <i class="fas fa-trash-alt"></i>
        </a>
    </div>
</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginación -->
                    <?php if (isset($total_paginas) && $total_paginas > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= $pagina_actual <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina_actual - 1])) ?>" tabindex="-1" aria-disabled="true">Anterior</a>
                            </li>
                            
                            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                <li class="page-item <?= $i == $pagina_actual ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $i])) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?= $pagina_actual >= $total_paginas ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina_actual + 1])) ?>">Siguiente</a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
   <script>
document.addEventListener('DOMContentLoaded', function() {
    // Función para manejar errores de fetch
    function handleFetchError(error) {
        console.error('Error:', error);
        return { success: false, message: 'Error de conexión: ' + error.message };
    }

    // Función para enviar acciones al servidor
    async function sendUserAction(action, documento) {
        try {
            const response = await fetch('acciones_usuario.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ accion: action, documento: documento })
            });
            
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            
            return await response.json();
        } catch (error) {
            return handleFetchError(error);
        }
    }

    // Función para mostrar mensaje al usuario
    function showMessage(message, isSuccess) {
        const alertType = isSuccess ? 'alert-success' : 'alert-danger';
        const alertHTML = `
            <div class="alert ${alertType} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        // Insertar al inicio del main-content
        const mainContent = document.querySelector('.main-content');
        mainContent.insertAdjacentHTML('afterbegin', alertHTML);
        
        // Desaparecer después de 5 segundos
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }

    // Manejar clic en botones de estado
    document.querySelectorAll('.btn-activar, .btn-desactivar, .btn-bloquear, .btn-eliminar').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            const row = this.closest('tr');
            const documento = row.querySelector('td:nth-child(3)').textContent.trim().split(' ')[1];
            
            let accion, mensaje, confirmMsg;
            
            if (this.classList.contains('btn-activar')) {
                accion = 'activar';
                confirmMsg = '¿Estás seguro de que deseas activar este usuario?';
            } else if (this.classList.contains('btn-desactivar')) {
                accion = 'desactivar';
                confirmMsg = '¿Estás seguro de que deseas desactivar este usuario?';
            } else if (this.classList.contains('btn-bloquear')) {
                accion = 'bloquear';
                confirmMsg = '¿Estás seguro de que deseas bloquear este usuario?';
            } else {
                accion = 'eliminar';
                confirmMsg = '¿Estás seguro de que deseas eliminar este usuario? Esta acción no se puede deshacer.';
            }
            
            if (confirm(confirmMsg)) {
                const result = await sendUserAction(accion, documento);
                
                if (result.success) {
                    showMessage(result.message, true);
                    
                    // Recargar la página después de 1 segundo para ver los cambios
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showMessage(result.message, false);
                }
            }
        });
    });
});
</script>
</body>
</html>