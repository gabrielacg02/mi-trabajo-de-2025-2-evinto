<?php
$usuario = $usuario ?? ['nombre_completo' => '', 'id_rol' => 1, 'numero_documento' => ''];
$error = $error ?? '';
$objeto = $objeto ?? [
    'id_objeto' => 1,
    'tipo_objeto' => 'Mochila',
    'descripcion' => 'Mochila negra marca Nike con logo blanco',
    'ubicacion_perdida' => 'Edificio A, Aula 101',
    'fecha_perdida' => date('Y-m-d H:i:s'),
    'estado' => 'perdido',
    'valor_estimado' => 50000,
    'observaciones' => 'Mochila con libros de matemáticas',
    'reportado_por' => 'Juan Pérez'
];
$imagenes = $imagenes ?? [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Objeto - Seguridad Universitaria</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        
        .object-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 10px;
        }
        
        .status-badge {
            font-size: 1.1rem;
            padding: 8px 16px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        
        .info-item i {
            color: var(--secondary-color);
            margin-right: 10px;
            width: 20px;
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
                <p>Usuario</p>
            </div>
        </div>
        
        <ul class="list-unstyled components">
            <li>
                <a href="<?php echo route('panel.' . ($usuario['id_rol'] == 5 ? 'administrador' : ($usuario['id_rol'] == 4 ? 'celador' : ($usuario['id_rol'] == 3 ? 'administrativo' : ($usuario['id_rol'] == 2 ? 'docente' : 'estudiante'))))); ?>">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="#reportesSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                    <i class="fas fa-exclamation-triangle me-2"></i> Reportes
                </a>
                <ul class="collapse list-unstyled" id="reportesSubmenu">
                    <li><a href="<?php echo route('reportes.incidentes'); ?>"><i class="fas fa-list me-2"></i> Incidentes</a></li>
                    <li><a href="<?php echo route('reportes.formulario'); ?>"><i class="fas fa-plus me-2"></i> Reportar incidente</a></li>
                </ul>
            </li>
            <li class="active">
                <a href="<?php echo route('objetos.index'); ?>"><i class="fas fa-box-open me-2"></i> Objetos Perdidos</a>
            </li>
            <li>
                <a href="<?php echo route('admin.accesos'); ?>"><i class="fas fa-door-open me-2"></i> Control de Accesos</a>
            </li>
            <li>
                <a href="<?php echo route('logout'); ?>"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-box-open me-2"></i> Detalle del Objeto</h2>
                <a href="<?php echo route('objetos.index'); ?>" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i> Volver a Objetos
                </a>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-8">
                    <!-- Imagen del Objeto -->
                    <div class="card">
                        <div class="card-body">
                            <?php if (!empty($imagenes)): ?>
                                <img src="<?= $imagenes[0]['ruta'] ?>" alt="Imagen del objeto" class="object-image">
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-image fa-5x text-muted mb-3"></i>
                                    <p class="text-muted">No hay imágenes disponibles</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Descripción -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-info-circle me-2"></i> Descripción</h5>
                        </div>
                        <div class="card-body">
                            <p><?= htmlspecialchars($objeto['descripcion']) ?></p>
                            
                            <?php if (!empty($objeto['observaciones'])): ?>
                                <h6>Observaciones:</h6>
                                <p class="text-muted"><?= htmlspecialchars($objeto['observaciones']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <!-- Información General -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-info-circle me-2"></i> Información General</h5>
                        </div>
                        <div class="card-body">
                            <div class="info-item">
                                <i class="fas fa-tag"></i>
                                <div>
                                    <strong>Tipo:</strong><br>
                                    <?= htmlspecialchars($objeto['tipo_objeto']) ?>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <div>
                                    <strong>Ubicación:</strong><br>
                                    <?= htmlspecialchars($objeto['ubicacion_perdida']) ?>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <i class="fas fa-calendar-alt"></i>
                                <div>
                                    <strong>Fecha:</strong><br>
                                    <?= date('d/m/Y H:i', strtotime($objeto['fecha_perdida'])) ?>
                                </div>
                            </div>
                            
                            <?php if ($objeto['valor_estimado']): ?>
                                <div class="info-item">
                                    <i class="fas fa-dollar-sign"></i>
                                    <div>
                                        <strong>Valor Estimado:</strong><br>
                                        $<?= number_format($objeto['valor_estimado']) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="info-item">
                                <i class="fas fa-user"></i>
                                <div>
                                    <strong>Reportado por:</strong><br>
                                    <?= htmlspecialchars($objeto['reportado_por']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Estado -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-info-circle me-2"></i> Estado</h5>
                        </div>
                        <div class="card-body text-center">
                            <span class="badge status-badge <?= $objeto['estado'] == 'perdido' ? 'bg-danger' : ($objeto['estado'] == 'encontrado' ? 'bg-success' : 'bg-info') ?>">
                                <?= ucfirst($objeto['estado']) ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Acciones -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-tools me-2"></i> Acciones</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <?php if ($usuario['id_rol'] == 1 && $objeto['estado'] == 'perdido'): ?>
                                    <button class="btn btn-success" onclick="marcarEncontrado()">
                                        <i class="fas fa-check me-2"></i> Marcar como Encontrado
                                    </button>
                                <?php endif; ?>
                                
                                <?php if ($usuario['id_rol'] == 1 && $objeto['estado'] == 'encontrado'): ?>
                                    <button class="btn btn-primary" onclick="reclamarObjeto()">
                                        <i class="fas fa-hand-holding me-2"></i> Reclamar Objeto
                                    </button>
                                <?php endif; ?>
                                
                                <button class="btn btn-outline-secondary" onclick="compartirObjeto()">
                                    <i class="fas fa-share me-2"></i> Compartir
                                </button>
                                
                                <button class="btn btn-outline-danger" onclick="reportarProblema()">
                                    <i class="fas fa-flag me-2"></i> Reportar Problema
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Galería de Imágenes -->
            <?php if (count($imagenes) > 1): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-images me-2"></i> Galería de Imágenes</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($imagenes as $imagen): ?>
                                        <div class="col-md-3 mb-3">
                                            <img src="<?= $imagen['ruta'] ?>" alt="Imagen del objeto" 
                                                 class="img-thumbnail" style="width: 100%; height: 200px; object-fit: cover;"
                                                 onclick="mostrarImagen('<?= $imagen['ruta'] ?>')">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para mostrar imagen -->
    <div class="modal fade" id="imagenModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Imagen del Objeto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="imagenModalContent" src="" alt="Imagen del objeto" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function mostrarImagen(ruta) {
            document.getElementById('imagenModalContent').src = ruta;
            new bootstrap.Modal(document.getElementById('imagenModal')).show();
        }
        
        function marcarEncontrado() {
            if (confirm('¿Está seguro de marcar este objeto como encontrado?')) {
                // Implementar lógica para marcar como encontrado
                alert('Función en desarrollo');
            }
        }
        
        function reclamarObjeto() {
            if (confirm('¿Desea reclamar este objeto?')) {
                // Implementar lógica para reclamar objeto
                alert('Función en desarrollo');
            }
        }
        
        function compartirObjeto() {
            // Implementar lógica para compartir
            alert('Función en desarrollo');
        }
        
        function reportarProblema() {
            // Implementar lógica para reportar problema
            alert('Función en desarrollo');
        }
    </script>
</body>
</html>