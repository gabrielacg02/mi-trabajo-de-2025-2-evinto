<!-- Navegación Global HTML Estático para Celador -->

<div class="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-shield-alt me-2"></i>Seguridad Universitaria</h3>
    </div>
    
    <div class="user-profile">
        <img src="https://ui-avatars.com/api/?name=Carlos+Rodriguez+Silva&background=3498db&color=fff" alt="Perfil">
        <div class="user-info">
            <h5>Carlos Rodríguez Silva</h5>
            <p>Celador</p>
        </div>
    </div>
    
    <ul class="list-unstyled components">
        <li class="active">
            <a href="/panel/celador"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
        </li>
        <li>
            <a href="/celador/registro-acceso"><i class="fas fa-door-open me-2"></i> Control de Accesos</a>
        </li>
        <li>
            <a href="/celador/objetos"><i class="fas fa-box-open me-2"></i> Objetos Perdidos</a>
        </li>
        <li>
            <a href="/celador/notificaciones"><i class="fas fa-bell me-2"></i> Notificaciones</a>
        </li>
        <li>
            <a href="/logout"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a>
        </li>
    </ul>
</div>

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
        font-size: 0.9rem;
        line-height: 1.2;
    }
    
    .user-info p {
        margin-bottom: 0;
        color: rgba(255,255,255,0.7);
        font-size: 0.8em;
    }
    
    @media (max-width: 768px) {
        .sidebar {
            margin-left: -250px;
        }
        
        .sidebar.active {
            margin-left: 0;
        }
    }
</style>
