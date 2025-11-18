<?php

use Illuminate\Support\Facades\Route;

//Ejemplo en clase
use App\Http\Controllers\SaludoController;

// Home
Route::get('/', function () {
    return view('index');
})->name('home');

// Prueba de base de datos MongoDB
Route::get('/pruebadb', function () {
    
    $usuario = new \App\Models\Usuario(); 
    
    $usuario->numero_documento = "123456789";
    $usuario->tipo_documento = "CC";
    $usuario->nombres = "Juan";
    $usuario->apellidos = "Perez";
    $usuario->correo = "juan.perez@gmail.com";
    $usuario->contrasena = \Illuminate\Support\Facades\Hash::make("123456");
    $usuario->id_rol = 1;
    $usuario->estado = "activo";
    $usuario->intentos_fallidos = 0;
    $usuario->ultimo_acceso = null;
    $usuario->creado_en = now();
    $usuario->actualizado_en = now();
    
    echo $usuario->save();
});

// vistas 
Route::view('/login', 'login')->name('login');
Route::post('/login', function () {
    // Redirigir directamente al panel de administrador
    return redirect()->route('panel.administrador');
})->name('login.attempt');
Route::view('/register', 'registro')->name('register');

// Paneles por rol 
Route::view('/panel/estudiante', 'panel_estudiante')->name('panel.estudiante');
Route::view('/panel/docente', 'panel_docente')->name('panel.docente');
Route::view('/panel/administrativo', 'panel_administrativo')->name('panel.administrativo');
Route::view('/panel/celador', 'panel_celador')->name('panel.celador');
Route::view('/panel/administrador', 'panel_administrador')->name('panel.administrador');

// Vistas directas clave 
Route::view('/reportes/incidentes', 'reportes_incidentes')->name('reportes.incidentes');
Route::view('/reportes/pendientes', 'reportes_pendientes')->name('reportes.pendientes');
Route::view('/reportes/aprobar', 'aprobar_reporte')->name('reportes.aprobar');
Route::view('/reportes/detalle', 'reporte_detalle')->name('reportes.detalle');
Route::view('/reportes/formulario', 'reporte_formulario')->name('reportes.formulario');


Route::view('/objetos', 'panel_objetos')->name('objetos.index');
Route::view('/objetos/estudiante', 'panel_objetos_estudiante')->name('objetos.estudiante');
Route::view('/objetos/detalle', 'objeto_detalle')->name('objetos.detalle');
Route::view('/objetos/estudiante/detalle', 'estudiante_objeto_detalle')->name('objetos.estudiante.detalle');
Route::view('/objetos/estudiante/reportar', 'reportar_objeto')->name('objetos.estudiante.reportar');
Route::view('/objetos/estudiante/reclamar', 'estudiante_reclamar_objeto')->name('objetos.estudiante.reclamar');
Route::view('/objetos/estudiante/marcar', 'estudiante_marcar_encontrado')->name('objetos.estudiante.marcar');
Route::view('/admin/objetos/pendientes', 'admin_objetos_pendientes')->name('admin.objetos.pendientes');
Route::view('/admin/objeto/detalle', 'admin_objeto_detalle')->name('admin.objeto.detalle');

Route::view('/notificaciones', 'estudiante_notificaciones')->name('notificaciones.estudiante');
Route::view('/notificaciones/admin', 'notificaciones_adm')->name('notificaciones.admin');
Route::view('/notificaciones/ver', 'ver_notificacion')->name('notificaciones.ver');
Route::view('/notificaciones/marcar', 'marcar_notificacion_leida')->name('notificaciones.marcar');
Route::view('/notificaciones/marcar-todas', 'marcar_notificaciones_leidas')->name('notificaciones.marcar_todas');

Route::view('/admin/usuarios', 'admin_usuarios')->name('admin.usuarios');
Route::view('/admin/usuarios/registrar', 'admin_registrar_usuario')->name('admin.usuarios.registrar');
Route::view('/admin/usuarios/editar', 'admin_editar_usuario')->name('admin.usuarios.editar');

Route::view('/admin/accesos', 'admin_accesos')->name('admin.accesos');
Route::view('/admin/auditoria', 'admin_auditoria')->name('admin.auditoria');
Route::view('/admin/configuracion', 'admin_configuracion')->name('admin.configuracion');

// logout
Route::view('/logout', 'logout')->name('logout');

// Otras vistas 
Route::view('/welcome', 'welcome')->name('welcome');


// Celador
Route::view('/celador/notificaciones', 'celador_notificaciones')->name('celador.notificaciones');
Route::view('/celador/objetos', 'celador_objetos')->name('celador.objetos');
Route::view('/celador/registro-acceso', 'celador_registro_acceso')->name('celador.registro_acceso');

// Estudiante acciones
Route::view('/estudiante/reportar-incidente', 'estudiante_reportar_incidente')->name('estudiante.reportar_incidente');

// Admin notificaciones y reportes
Route::view('/admin/notificaciones', 'admin_notificaciones')->name('admin.notificaciones');
Route::view('/admin/reporte/detalle', 'admin_reporte_detalle')->name('admin.reporte.detalle');

// Acciones/utilidades varias
Route::view('/acciones-usuario', 'acciones_usuario')->name('acciones.usuario');
Route::view('/incidentes/detalle', 'detalle_incidente')->name('incidentes.detalle');
Route::view('/evidencias/eliminar', 'eliminar_evidencia')->name('evidencias.eliminar');
Route::view('/db-config', 'db_config')->name('db.config');

// Compatibilidad con rutas 
Route::redirect('/login.php', '/login');
Route::redirect('/reportes/login.php', '/login');
Route::redirect('/reportes_incidentes.php', '/reportes/incidentes');
Route::get("/registrar-datos-{nombre}-{codigoestudiante}",[SaludoController::class,'registrar']);

// Rutas para imágenes estáticas
Route::get('/images/{filename}', function ($filename) {
    $path = public_path('images/' . $filename);
    
    if (!file_exists($path)) {
        abort(404);
    }
    
    return response()->file($path);
})->where('filename', '.*');

