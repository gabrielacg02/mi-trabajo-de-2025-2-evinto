<?php
$usuario = $usuario ?? ['nombre_completo' => '', 'id_rol' => 1, 'numero_documento' => ''];
$error = $error ?? '';
$success = $success ?? '';
$tipos_incidente = $tipos_incidente ?? [];
$incidente = $incidente ?? null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportar Incidente - Seguridad Universitaria</title>
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
        
        .form-section {
            background: linear-gradient(135deg, var(--secondary-color), var(--info-color));
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .form-section h5 {
            color: white;
            margin-bottom: 15px;
        }
        
        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
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
                    <li class="active"><a href="<?php echo route('reportes.formulario'); ?>"><i class="fas fa-plus me-2"></i> Reportar incidente</a></li>
                </ul>
            </li>
            <li>
                <a href="<?php echo route('objetos.estudiante'); ?>"><i class="fas fa-box-open me-2"></i> Objetos Perdidos</a>
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
                <h2><i class="fas fa-exclamation-triangle me-2"></i> Reportar Incidente</h2>
                <a href="<?php echo route('reportes.incidentes'); ?>" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i> Volver a Incidentes
                </a>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8">
                        <!-- Información del Incidente -->
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-info-circle me-2"></i> Información del Incidente</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tipo_incidente" class="form-label">Tipo de Incidente *</label>
                                            <select class="form-select" id="tipo_incidente" name="tipo_incidente" required>
                                                <option value="">Seleccionar tipo...</option>
                                                <?php foreach ($tipos_incidente as $tipo): ?>
                                                    <option value="<?= $tipo['id_tipo'] ?>" <?= ($incidente && $incidente['id_tipo'] == $tipo['id_tipo']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($tipo['nombre']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="fecha_incidente" class="form-label">Fecha del Incidente *</label>
                                            <input type="datetime-local" class="form-control" id="fecha_incidente" name="fecha_incidente" 
                                                   value="<?= $incidente ? date('Y-m-d\TH:i', strtotime($incidente['fecha_incidente'])) : date('Y-m-d\TH:i') ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="ubicacion" class="form-label">Ubicación *</label>
                                    <input type="text" class="form-control" id="ubicacion" name="ubicacion" 
                                           value="<?= $incidente ? htmlspecialchars($incidente['ubicacion']) : '' ?>" 
                                           placeholder="Ej: Edificio A, Aula 101" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="descripcion" class="form-label">Descripción del Incidente *</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="5" 
                                              placeholder="Describe detalladamente lo que ocurrió..." required><?= $incidente ? htmlspecialchars($incidente['descripcion']) : '' ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="personas_involucradas" class="form-label">Personas Involucradas</label>
                                    <textarea class="form-control" id="personas_involucradas" name="personas_involucradas" rows="3" 
                                              placeholder="Describe las personas involucradas, si las hay..."><?= $incidente ? htmlspecialchars($incidente['personas_involucradas']) : '' ?></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Evidencias -->
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-camera me-2"></i> Evidencias</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="evidencias" class="form-label">Subir Archivos</label>
                                    <input type="file" class="form-control" id="evidencias" name="evidencias[]" multiple 
                                           accept="image/*,.pdf,.doc,.docx">
                                    <div class="form-text">Puedes subir imágenes, PDFs o documentos. Máximo 5 archivos.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="observaciones_adicionales" class="form-label">Observaciones Adicionales</label>
                                    <textarea class="form-control" id="observaciones_adicionales" name="observaciones_adicionales" rows="3" 
                                              placeholder="Cualquier información adicional que consideres relevante..."><?= $incidente ? htmlspecialchars($incidente['observaciones_adicionales']) : '' ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <!-- Información del Usuario -->
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-user me-2"></i> Información del Reportante</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Nombre Completo</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['nombre_completo']) ?>" readonly>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Número de Documento</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['numero_documento']) ?>" readonly>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Fecha de Reporte</label>
                                    <input type="text" class="form-control" value="<?= date('d/m/Y H:i') ?>" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Prioridad -->
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-exclamation-circle me-2"></i> Prioridad</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="prioridad" class="form-label">Nivel de Prioridad</label>
                                    <select class="form-select" id="prioridad" name="prioridad">
                                        <option value="baja" <?= ($incidente && $incidente['prioridad'] == 'baja') ? 'selected' : '' ?>>Baja</option>
                                        <option value="media" <?= ($incidente && $incidente['prioridad'] == 'media') ? 'selected' : '' ?>>Media</option>
                                        <option value="alta" <?= ($incidente && $incidente['prioridad'] == 'alta') ? 'selected' : '' ?>>Alta</option>
                                        <option value="critica" <?= ($incidente && $incidente['prioridad'] == 'critica') ? 'selected' : '' ?>>Crítica</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="urgente" name="urgente" 
                                               <?= ($incidente && $incidente['urgente']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="urgente">
                                            Marcar como Urgente
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Botones de Acción -->
                        <div class="card">
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button type="submit" name="guardar_borrador" class="btn btn-outline-secondary">
                                        <i class="fas fa-save me-2"></i> Guardar Borrador
                                    </button>
                                    <button type="submit" name="enviar_reporte" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-2"></i> Enviar Reporte
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validación del formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            const tipoIncidente = document.getElementById('tipo_incidente').value;
            const fechaIncidente = document.getElementById('fecha_incidente').value;
            const ubicacion = document.getElementById('ubicacion').value;
            const descripcion = document.getElementById('descripcion').value;
            
            if (!tipoIncidente || !fechaIncidente || !ubicacion || !descripcion) {
                e.preventDefault();
                alert('Por favor, completa todos los campos obligatorios marcados con *');
                return false;
            }
            
            // Validar que la fecha no sea futura
            const fechaInc = new Date(fechaIncidente);
            const ahora = new Date();
            
            if (fechaInc > ahora) {
                e.preventDefault();
                alert('La fecha del incidente no puede ser futura');
                return false;
            }
        });
        
        // Limitar archivos a 5
        document.getElementById('evidencias').addEventListener('change', function(e) {
            if (e.target.files.length > 5) {
                alert('Solo puedes subir máximo 5 archivos');
                e.target.value = '';
            }
        });
    </script>
</body>
</html>