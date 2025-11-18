<!-- Panel de Registro de Accesos HTML Estático -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Accesos - Celador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
        }
        
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f8f9fa;
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
        }
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .badge-entrada {
            background-color: var(--secondary-color);
        }
        
        .badge-salida {
            background-color: var(--accent-color);
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Global -->
    @include('layouts.guard-sidebar')

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <h2 class="mb-4"><i class="fas fa-door-open me-2"></i> Registro de Accesos</h2>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Nuevo Registro</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" id="registroForm">
                                <div class="mb-3">
                                    <label class="form-label">Tipo de Persona</label>
                                    <select class="form-select" name="tipo_persona" id="tipoPersona" required>
                                        <option value="usuario">Usuario (Estudiante/Docente/Personal)</option>
                                        <option value="visitante">Visitante</option>
                                    </select>
                                </div>
                                
                                <!-- Campos para usuario -->
                                <div id="usuarioFields">
                                    <div class="mb-3">
                                        <label class="form-label">Número de Documento</label>
                                        <input type="text" class="form-control" name="numero_documento_usuario" placeholder="Documento del usuario">
                                    </div>
                                </div>
                                
                                <!-- Campos para visitante entrada -->
                                <div id="visitanteEntradaFields" style="display: none;">
                                    <div class="mb-3">
                                        <label class="form-label">Tipo de Documento</label>
                                        <select class="form-select" name="tipo_documento_visitante">
                                            <option value="CC">Cédula de Ciudadanía</option>
                                            <option value="TI">Tarjeta de Identidad</option>
                                            <option value="CE">Cédula de Extranjería</option>
                                            <option value="PA">Pasaporte</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Número de Documento</label>
                                        <input type="text" class="form-control" name="numero_documento_visitante" placeholder="Documento del visitante">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Nombres</label>
                                        <input type="text" class="form-control" name="nombres" placeholder="Nombres del visitante">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Apellidos</label>
                                        <input type="text" class="form-control" name="apellidos" placeholder="Apellidos del visitante">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Motivo de Visita</label>
                                        <input type="text" class="form-control" name="motivo_visita" placeholder="Motivo de la visita" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Contacto (opcional)</label>
                                        <input type="text" class="form-control" name="contacto" placeholder="Teléfono o correo">
                                    </div>
                                </div>
                                
                                <!-- Campos para visitante salida -->
                                <div id="visitanteSalidaFields" style="display: none;">
                                    <div class="mb-3">
                                        <label class="form-label">Número de Documento</label>
                                        <input type="text" class="form-control" name="numero_documento_visitante_salida" placeholder="Documento del visitante">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Tipo de Movimiento</label>
                                    <select class="form-select" name="tipo_movimiento" id="tipoMovimiento" required>
                                        <option value="entrada">Entrada</option>
                                        <option value="salida">Salida</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Observaciones (opcional)</label>
                                    <textarea class="form-control" name="observaciones" rows="2"></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Registrar Acceso</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Últimos Registros</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Tipo</th>
                                            <th>Movimiento</th>
                                            <th>Fecha/Hora</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Juan Pérez García</td>
                                            <td>Estudiante</td>
                                            <td><span class="badge bg-success">Entrada</span></td>
                                            <td>15/01/2024 14:30</td>
                                        </tr>
                                        <tr>
                                            <td>María González López</td>
                                            <td>Docente</td>
                                            <td><span class="badge bg-success">Entrada</span></td>
                                            <td>15/01/2024 14:25</td>
                                        </tr>
                                        <tr>
                                            <td>Carlos Rodríguez Silva</td>
                                            <td>Visitante</td>
                                            <td><span class="badge bg-danger">Salida</span></td>
                                            <td>15/01/2024 14:20</td>
                                        </tr>
                                        <tr>
                                            <td>Ana Martínez Ruiz</td>
                                            <td>Administrativo</td>
                                            <td><span class="badge bg-success">Entrada</span></td>
                                            <td>15/01/2024 14:15</td>
                                        </tr>
                                        <tr>
                                            <td>Pedro López García</td>
                                            <td>Visitante</td>
                                            <td><span class="badge bg-success">Entrada</span></td>
                                            <td>15/01/2024 14:10</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar/ocultar campos según tipo de persona y movimiento
        document.getElementById('tipoPersona').addEventListener('change', function() {
            const tipoPersona = this.value;
            const tipoMovimiento = document.getElementById('tipoMovimiento').value;
            
            document.getElementById('usuarioFields').style.display = 'none';
            document.getElementById('visitanteEntradaFields').style.display = 'none';
            document.getElementById('visitanteSalidaFields').style.display = 'none';
            
            if (tipoPersona === 'usuario') {
                document.getElementById('usuarioFields').style.display = 'block';
            } else {
                if (tipoMovimiento === 'entrada') {
                    document.getElementById('visitanteEntradaFields').style.display = 'block';
                } else {
                    document.getElementById('visitanteSalidaFields').style.display = 'block';
                }
            }
        });
        
        document.getElementById('tipoMovimiento').addEventListener('change', function() {
            const tipoPersona = document.getElementById('tipoPersona').value;
            const tipoMovimiento = this.value;
            
            if (tipoPersona === 'visitante') {
                document.getElementById('visitanteEntradaFields').style.display = 'none';
                document.getElementById('visitanteSalidaFields').style.display = 'none';
                
                if (tipoMovimiento === 'entrada') {
                    document.getElementById('visitanteEntradaFields').style.display = 'block';
                } else {
                    document.getElementById('visitanteSalidaFields').style.display = 'block';
                }
            }
        });
        
        // Prevenir envío del formulario (solo demostración)
        document.getElementById('registroForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Funcionalidad de demostración - El registro no se guardará');
        });
    </script>
</body>
</html>