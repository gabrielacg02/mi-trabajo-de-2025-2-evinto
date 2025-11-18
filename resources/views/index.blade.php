<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguridad Universitaria | Sistema Integral</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">
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
            line-height: 1.6;
            background-color: #f9f9f9;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
        }
        
        .navbar {
            background-color: var(--primary-color);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            color: white !important;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8) !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            color: white !important;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem 0;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('') no-repeat center center/cover;
            opacity: 0.15;
            z-index: 0;
        }
        
        /* Reduce section spacing */
        .py-5 {
            padding-top: 1.5rem !important;
            padding-bottom: 1.5rem !important;
        }
        
        .py-5 .container {
            padding-top: 1rem !important;
            padding-bottom: 1rem !important;
        }
        
        .py-5 .row.justify-content-center.mb-5 {
            margin-bottom: 1.5rem !important;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        .feature-card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            border: none;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .feature-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: var(--secondary-color);
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .feature-header h4 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
            line-height: 1.3;
        }
        
        .section-title {
            position: relative;
            display: inline-block;
            margin-bottom: 2rem;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--secondary-color);
        }
        
        
        .video-container {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }
        
        .testimonial-card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }
        
        .testimonial-text {
            font-style: italic;
            margin-bottom: 1rem;
        }
        
        .testimonial-author {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .testimonial-role {
            color: #666;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            padding: 0.5rem 1.5rem;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }
        
        .btn-outline-light {
            border-width: 2px;
            font-weight: 500;
        }
        
        .footer {
            background-color: var(--primary-color);
            color: white;
            padding: 3rem 0;
        }
        
        .footer-links h5 {
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        
        .footer-links ul {
            list-style: none;
            padding-left: 0;
        }
        
        .footer-links li {
            margin-bottom: 0.5rem;
        }
        
        .footer-links a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .footer-links a:hover {
            color: white;
            padding-left: 5px;
        }
        
        .social-icons a {
            color: white;
            font-size: 1.2rem;
            margin-right: 1rem;
            transition: all 0.3s ease;
        }
        
        .social-icons a:hover {
            color: var(--secondary-color);
            transform: translateY(-3px);
        }
        
        .copyright {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 1.5rem;
            margin-top: 2rem;
        }
        
        .floating-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background-color: var(--accent-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            z-index: 100;
            transition: all 0.3s ease;
        }
        
        .floating-btn:hover {
            transform: scale(1.1);
            color: white;
        }
        
        /* Formulario de Registro */
        .register-form-container {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin: 1.5rem 0;
        }
        
        .register-form-container h2 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }
        
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }
        
        /* Animaciones */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fade-in {
            animation: fadeIn 1s ease forwards;
        }
        
        .delay-1 { animation-delay: 0.2s; }
        .delay-2 { animation-delay: 0.4s; }
        .delay-3 { animation-delay: 0.6s; }
        .delay-4 { animation-delay: 0.8s; }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .hero-section {
                padding: 1.5rem 0;
            }
            
            .hero-title {
                font-size: 2rem;
            }
            
            .py-5 {
                padding-top: 1rem !important;
                padding-bottom: 1rem !important;
            }
            
            .feature-header {
                flex-direction: column;
                text-align: center;
                margin-bottom: 1rem;
            }
            
            .feature-icon {
                margin-right: 0;
                margin-bottom: 0.5rem;
            }
            
            .feature-header h4 {
                font-size: 1.1rem;
            }
        }
        
        @media (max-width: 576px) {
            .feature-card {
                padding: 1.5rem;
            }
            
            .feature-icon {
                font-size: 2rem;
            }
            
            .feature-header h4 {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-shield-alt me-2"></i>Seguridad Universitaria
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Servicios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">Acerca de</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#testimonials">Testimonios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contacto</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-outline-light" href="login">Iniciar Sesión</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-primary" href="#register">Registrarse</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content animate-fade-in">
                    <h1 class="display-4 fw-bold mb-4">Seguridad Integral para tu Comunidad Universitaria</h1>
                    <p class="lead mb-4">Protegiendo a estudiantes, docentes y personal con tecnología avanzada y protocolos de seguridad efectivos.</p>
                    <div class="d-flex gap-3">
                        <a href="#features" class="btn btn-primary btn-lg">Conoce más</a>
                        <a href="#video" class="btn btn-outline-light btn-lg">Ver video</a>
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block animate-fade-in delay-1">
                    <img src="{{ asset('images/img5.png') }}" alt="Seguridad universitaria" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5 bg-light">
        <div class="container py-5">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-8 text-center">
                    <h2 class="section-title animate-fade-in">Nuestros Servicios</h2>
                    <p class="lead animate-fade-in delay-1">Ofrecemos soluciones completas para garantizar la seguridad en el campus universitario</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-3 animate-fade-in delay-1">
                    <div class="feature-card">
                        <div class="feature-header">
                            <div class="feature-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <h4>Reporte de Incidentes</h4>
                        </div>
                        <p>Reporta cualquier incidente de seguridad de manera rápida y eficiente, con seguimiento en tiempo real.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 animate-fade-in delay-2">
                    <div class="feature-card">
                        <div class="feature-header">
                            <div class="feature-icon">
                                <i class="fas fa-key"></i>
                            </div>
                            <h4>Control de Accesos</h4>
                        </div>
                        <p>Sistema de registro de entradas y salidas para estudiantes, personal y visitantes del campus.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 animate-fade-in delay-3">
                    <div class="feature-card">
                        <div class="feature-header">
                            <div class="feature-icon">
                                <i class="fas fa-box-open"></i>
                            </div>
                            <h4>Objetos Perdidos</h4>
                        </div>
                        <p>Registro centralizado de objetos perdidos y encontrados dentro del campus universitario.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 animate-fade-in delay-4">
                    <div class="feature-card">
                        <div class="feature-header">
                            <div class="feature-icon">
                                <i class="fas fa-bell"></i>
                            </div>
                            <h4>Alertas y Notificaciones</h4>
                        </div>
                        <p>Sistema de alertas en tiempo real para emergencias y comunicaciones importantes.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0 animate-fade-in">
                    <img src="https://images.unsplash.com/photo-1521791055366-0d553872125f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1469&q=80" alt="Sobre nosotros" class="img-fluid rounded shadow">
                </div>
                <div class="col-lg-6 animate-fade-in delay-1">
                    <h2 class="section-title mb-4">Sobre Nuestro Sistema</h2>
                    <p class="mb-4">Nuestro sistema de seguridad universitaria fue diseñado para brindar protección integral a toda la comunidad académica, combinando tecnología avanzada con protocolos de seguridad probados.</p>
                    <div class="d-flex mb-3">
                        <div class="me-4">
                            <i class="fas fa-check-circle text-success fs-4"></i>
                        </div>
                        <div>
                            <h5>Seguridad las 24 horas</h5>
                            <p>Monitoreo constante del campus con personal capacitado y sistemas automatizados.</p>
                        </div>
                    </div>
                    <div class="d-flex mb-3">
                        <div class="me-4">
                            <i class="fas fa-check-circle text-success fs-4"></i>
                        </div>
                        <div>
                            <h5>Respuesta rápida</h5>
                            <p>Protocolos de actuación inmediata ante cualquier incidente reportado.</p>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="me-4">
                            <i class="fas fa-check-circle text-success fs-4"></i>
                        </div>
                        <div>
                            <h5>Comunidad participativa</h5>
                            <p>Todos los miembros de la universidad pueden contribuir a mantener un entorno seguro.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Registration Form Section -->
    <section id="register" class="py-5 bg-light">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="register-form-container animate-fade-in">
                        <h2 class="text-center mb-4">Regístrate como Estudiante</h2>
                        <form action="registro.php" method="POST">
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
                            
                            <div class="text-center mt-3">
                                <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Video Section -->
    <section id="video" class="py-5 bg-light">
        <div class="container py-5">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-8 text-center">
                    <h2 class="section-title animate-fade-in">Conoce Nuestro Sistema</h2>
                    <p class="lead animate-fade-in delay-1">Descubre cómo funciona nuestra plataforma de seguridad universitaria</p>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-10 mx-auto animate-fade-in delay-1">
                    <div class="video-container">
                        <!-- Video de ejemplo (reemplazar con video real) -->
                       <iframe width="560" height="315" 
  src="https://www.youtube.com/embed/xfo6WWRqrqc" 
  frameborder="0" 
  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
  allowfullscreen>
</iframe>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="py-5">
        <div class="container py-5">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-8 text-center">
                    <h2 class="section-title animate-fade-in">Lo que dicen de nosotros</h2>
                    <p class="lead animate-fade-in delay-1">Testimonios de nuestra comunidad universitaria</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 animate-fade-in delay-1">
                    <div class="testimonial-card">
                        <div class="testimonial-text">
                            "Gracias al sistema de objetos perdidos pude recuperar mi laptop con todos mis trabajos de investigación. ¡Excelente servicio!"
                        </div>
                        <div class="testimonial-author">María González</div>
                        <div class="testimonial-role">Estudiante de Medicina</div>
                        <div class="mt-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 animate-fade-in delay-2">
                    <div class="testimonial-card">
                        <div class="testimonial-text">
                            "La rapidez con que atendieron mi reporte de un comportamiento sospechoso en la biblioteca fue impresionante. Me siento más seguro en el campus."
                        </div>
                        <div class="testimonial-author">Carlos Martínez</div>
                        <div class="testimonial-role">Docente de Ingeniería</div>
                        <div class="mt-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star-half-alt text-warning"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 animate-fade-in delay-3">
                    <div class="testimonial-card">
                        <div class="testimonial-text">
                            "Como personal administrativo, valoro mucho el control de accesos y la trazabilidad que ofrece el sistema. Ha mejorado nuestra seguridad operacional."
                        </div>
                        <div class="testimonial-author">Laura Ramírez</div>
                        <div class="testimonial-role">Personal Administrativo</div>
                        <div class="mt-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5 bg-light">
        <div class="container py-5">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-8 text-center">
                    <h2 class="section-title animate-fade-in">Contáctanos</h2>
                    <p class="lead animate-fade-in delay-1">Estamos aquí para ayudarte. Ponte en contacto con nuestro equipo de seguridad.</p>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 mb-4 mb-lg-0 animate-fade-in delay-1">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-5">
                            <h4 class="mb-4">Información de Contacto</h4>
                            <div class="d-flex mb-4">
                                <div class="me-4 text-primary">
                                    <i class="fas fa-map-marker-alt fs-4"></i>
                                </div>
                                <div>
                                    <h5>Dirección</h5>
                                    <p class="mb-0">Edificio de Seguridad, Campus Central, Universidad</p>
                                </div>
                            </div>
                            <div class="d-flex mb-4">
                                <div class="me-4 text-primary">
                                    <i class="fas fa-phone-alt fs-4"></i>
                                </div>
                                <div>
                                    <h5>Teléfono</h5>
                                    <p class="mb-0">+1 (123) 456-7890 (Emergencias)<br>+1 (123) 456-7891 (Administración)</p>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="me-4 text-primary">
                                    <i class="fas fa-envelope fs-4"></i>
                                </div>
                                <div>
                                    <h5>Email</h5>
                                    <p class="mb-0">seguridad@universidad.edu<br>emergencias@universidad.edu</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 animate-fade-in delay-2">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-5">
                            <h4 class="mb-4">Envíanos un Mensaje</h4>
                            <form>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" id="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Asunto</label>
                                    <input type="text" class="form-control" id="subject" required>
                                </div>
                                <div class="mb-3">
                                    <label for="message" class="form-label">Mensaje</label>
                                    <textarea class="form-control" id="message" rows="4" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Enviar Mensaje</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5 class="text-white mb-4">
                        <i class="fas fa-shield-alt me-2"></i>Seguridad Universitaria
                    </h5>
                    <p>Sistema integral de seguridad para la comunidad universitaria, diseñado para proteger y servir.</p>
                    <div class="social-icons mt-4">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
                    <h5 class="text-white mb-4">Enlaces Rápidos</h5>
                    <ul class="footer-links">
                        <li><a href="#">Inicio</a></li>
                        <li><a href="#features">Servicios</a></li>
                        <li><a href="#about">Acerca de</a></li>
                        <li><a href="#testimonials">Testimonios</a></li>
                        <li><a href="#contact">Contacto</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
                    <h5 class="text-white mb-4">Servicios</h5>
                    <ul class="footer-links">
                        <li><a href="#">Reporte de Incidentes</a></li>
                        <li><a href="#">Control de Accesos</a></li>
                        <li><a href="#">Objetos Perdidos</a></li>
                        <li><a href="#">Alertas de Seguridad</a></li>
                        <li><a href="#">Protocolos de Emergencia</a></li>
                        <li><a href="#">Capacitaciones</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h5 class="text-white mb-4">Horario de Atención</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">Oficina Administrativa:</li>
                        <li class="mb-2">Lunes a Viernes: 8:00 am - 6:00 pm</li>
                        <li class="mb-2">Sábados: 9:00 am - 1:00 pm</li>
                        <li class="mb-2">Emergencias: 24/7</li>
                    </ul>
                    
                </div>
            </div>
            <div class="copyright text-center text-white-50">
                <p class="mb-0">&copy; 2023 Sistema de Seguridad Universitaria. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Floating Button -->
    <a href="#" class="floating-btn animate-fade-in delay-4">
        <i class="fas fa-question"></i>
    </a>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
        
        // Animation on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const animateElements = document.querySelectorAll('.animate-fade-in');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = 1;
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });
            
            animateElements.forEach(element => {
                element.style.opacity = 0;
                element.style.transform = 'translateY(20px)';
                element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(element);
            });
        });
    </script>
</body>
</html>