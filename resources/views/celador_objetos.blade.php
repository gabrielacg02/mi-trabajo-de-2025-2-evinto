<!-- Panel de Objetos Perdidos HTML Estático -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Objetos Perdidos - Seguridad Universitaria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        
        .badge-perdido {
            background-color: var(--warning-color);
        }
        
        .badge-encontrado {
            background-color: var(--info-color);
        }
        
        .badge-devuelto {
            background-color: var(--success-color);
        }
        
        .object-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-box-open me-2"></i> Objetos Perdidos</h2>
                <a href="#" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Registrar Objeto
                </a>
            </div>
            
            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="get" class="row g-3">
                        <div class="col-md-4">
                            <label for="estado" class="form-label">Estado</label>
                            <select id="estado" name="estado" class="form-select">
                                <option value="">Todos</option>
                                <option value="perdido">Perdido</option>
                                <option value="encontrado">Encontrado</option>
                                <option value="devuelto">Devuelto</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="tipo" class="form-label">Tipo de Objeto</label>
                            <input type="text" id="tipo" name="tipo" class="form-control" placeholder="Ej. Calculadora, Teléfono...">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter me-1"></i> Filtrar
                            </button>
                            <a href="#" class="btn btn-outline-secondary">
                                <i class="fas fa-sync-alt me-1"></i> Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Lista de Objetos -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Listado de Objetos</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th>Ubicación</th>
                                    <th>Reportado Por</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#001</td>
                                    <td>Calculadora</td>
                                    <td>Calculadora científica Casio...</td>
                                    <td>Edificio A - Aula 201</td>
                                    <td>Juan Pérez García</td>
                                    <td><span class="badge badge-perdido">Perdido</span></td>
                                    <td>15/01/2024</td>
                                    <td>
                                        <div class="d-flex">
                                            <a href="#" class="btn btn-sm btn-primary me-1">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-success" title="Marcar como encontrado">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#002</td>
                                    <td>Teléfono</td>
                                    <td>iPhone 12 Pro Max negro...</td>
                                    <td>Biblioteca Central</td>
                                    <td>María González López</td>
                                    <td><span class="badge badge-encontrado">Encontrado</span></td>
                                    <td>14/01/2024</td>
                                    <td>
                                        <div class="d-flex">
                                            <a href="#" class="btn btn-sm btn-primary me-1">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-info" title="Marcar como devuelto">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#003</td>
                                    <td>Laptop</td>
                                    <td>Laptop Dell Inspiron 15...</td>
                                    <td>Laboratorio de Computación</td>
                                    <td>Carlos Rodríguez Silva</td>
                                    <td><span class="badge badge-devuelto">Devuelto</span></td>
                                    <td>13/01/2024</td>
                                    <td>
                                        <div class="d-flex">
                                            <a href="#" class="btn btn-sm btn-primary me-1">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#004</td>
                                    <td>Mochila</td>
                                    <td>Mochila Nike negra con...</td>
                                    <td>Cafetería Principal</td>
                                    <td>Ana Martínez Ruiz</td>
                                    <td><span class="badge badge-perdido">Perdido</span></td>
                                    <td>12/01/2024</td>
                                    <td>
                                        <div class="d-flex">
                                            <a href="#" class="btn btn-sm btn-primary me-1">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-success" title="Marcar como encontrado">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#005</td>
                                    <td>Libro</td>
                                    <td>Libro de Matemáticas...</td>
                                    <td>Edificio B - Aula 105</td>
                                    <td>Pedro López García</td>
                                    <td><span class="badge badge-encontrado">Encontrado</span></td>
                                    <td>11/01/2024</td>
                                    <td>
                                        <div class="d-flex">
                                            <a href="#" class="btn btn-sm btn-primary me-1">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-info" title="Marcar como devuelto">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Prevenir envío del formulario de filtros (solo demostración)
        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Funcionalidad de filtros en modo demostración');
        });
        
        // Simular cambio de estado de objetos
        document.querySelectorAll('button[title*="Marcar como"]').forEach(button => {
            button.addEventListener('click', function() {
                const action = this.title.includes('encontrado') ? 'encontrado' : 'devuelto';
                alert(`Funcionalidad de demostración - Marcar como ${action}`);
            });
        });
    </script>
</body>
</html>