<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Seguridad Universitaria</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        .login-card:hover {
            transform: translateY(-5px);
        }
        .login-header {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
        }
        .login-header h2 {
            margin: 0;
            font-weight: 600;
            font-size: 1.8rem;
        }
        .login-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
            font-size: 1rem;
        }
        .login-body {
            padding: 2.5rem 2rem;
        }
        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus {
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
        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 1.5rem;
        }
        .text-decoration-none {
            color: #3498db;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .text-decoration-none:hover {
            color: #2980b9;
        }
        @media (max-width: 576px) {
            .login-container {
                padding: 15px;
            }
            .login-header {
                padding: 2rem 1.5rem;
            }
            .login-body {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2><i class="fas fa-shield-alt me-2"></i>Seguridad Universitaria</h2>
                <p class="mb-0">Iniciar Sesión</p>
            </div>
            <div class="login-body">
                @if ($errors->has('error'))
                    <div class="alert alert-danger" role="alert">
                        {{ $errors->first('error') }}
                    </div>
                @endif
                @if ($errors->any() && !$errors->has('error'))
                    <div class="alert alert-danger" role="alert">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif
                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="numero_documento" class="form-label">Número de Documento</label>
                        <input type="text" class="form-control" id="numero_documento" name="numero_documento" value="{{ old('numero_documento') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="contrasena" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">Iniciar Sesión</button>
                    </div>
                </form>
                <div class="text-center mt-3">
                    <a href="{{ route('register') }}" class="text-decoration-none">¿No tienes cuenta? Regístrate</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>