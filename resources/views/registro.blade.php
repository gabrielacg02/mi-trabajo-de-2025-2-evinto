<!-- Página de Registro HTML Estática -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Seguridad Universitaria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .register-container {
            width: 100%;
            max-width: 600px;
            padding: 20px;
        }
        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        .register-card:hover {
            transform: translateY(-5px);
        }
        .register-header {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
        }
        .register-header h2 {
            margin: 0;
            font-weight: 600;
            font-size: 1.8rem;
        }
        .register-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
            font-size: 1rem;
        }
        .register-body {
            padding: 2.5rem 2rem;
        }
        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.15);
            outline: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #3498db, #2980b9);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #2980b9, #1f5f8b);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }
        .text-decoration-none {
            color: #3498db;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .text-decoration-none:hover {
            color: #2980b9;
        }
        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 1.5rem;
        }
        @media (max-width: 576px) {
            .register-container {
                padding: 15px;
            }
            .register-header {
                padding: 2rem 1.5rem;
            }
            .register-body {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h2><i class="fas fa-shield-alt me-2"></i>Seguridad Universitaria</h2>
                <p class="mb-0">Registro de Estudiante</p>
            </div>
            <div class="register-body">
                <form id="registerForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipo_documento" class="form-label">Tipo de Documento</label>
                            <select class="form-select" id="tipo_documento" name="tipo_documento" required>
                                <option value="">Seleccione...</option>
                                <option value="CC">Cédula de Ciudadanía</option>
                                <option value="TI">Tarjeta de Identidad</option>
                                <option value="CE">Cédula de Extranjería</option>
                                <option value="PA">Pasaporte</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="numero_documento" class="form-label">Número de Documento</label>
                            <input type="text" class="form-control" id="numero_documento" name="numero_documento" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombres" class="form-label">Nombres</label>
                            <input type="text" class="form-control" id="nombres" name="nombres" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="apellidos" class="form-label">Apellidos</label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="correo" name="correo" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="contrasena" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirmar_contrasena" class="form-label">Confirmar Contraseña</label>
                            <input type="password" class="form-control" id="confirmar_contrasena" name="confirmar_contrasena" required>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">Registrarse</button>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <a href="{{ route('login') }}" class="text-decoration-none">¿Ya tienes cuenta? Inicia Sesión</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Prevenir envío del formulario (solo demostración)
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validar contraseñas
            const contrasena = document.getElementById('contrasena').value;
            const confirmarContrasena = document.getElementById('confirmar_contrasena').value;
            
            if (contrasena !== confirmarContrasena) {
                alert('Las contraseñas no coinciden');
                return;
            }
            
            // Simular registro exitoso
            alert('Registro exitoso (modo demostración). Serás redirigido al login.');
            
            // Redirigir al login después de 2 segundos
            setTimeout(() => {
                window.location.href = '{{ route("login") }}';
            }, 2000);
        });
    </script>
</body>
</html>