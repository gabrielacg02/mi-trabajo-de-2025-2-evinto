// ============================================
// FUNCIONES EQUIVALENTES A STORED PROCEDURES
// Para MongoDB - Deben ejecutarse desde la aplicación
// ============================================

use('electiva_3');

// ============================================
// sp_autenticar_usuario
// ============================================
function sp_autenticar_usuario(numero_documento, contrasena) {
  const crypto = require('crypto');
  const usuario = db.usuarios.findOne({ numero_documento: numero_documento });
  
  if (!usuario) {
    return { resultado: false, mensaje: 'Usuario no encontrado' };
  }
  
  if (usuario.estado === 'bloqueado') {
    return { resultado: false, mensaje: 'Usuario bloqueado. Contacte al administrador.' };
  }
  
  if (usuario.estado === 'inactivo') {
    return { resultado: false, mensaje: 'Usuario inactivo. Contacte al administrador.' };
  }
  
  const hash = crypto.createHash('sha256').update(contrasena).digest('hex');
  
  if (usuario.contrasena === hash) {
    db.usuarios.updateOne(
      { numero_documento: numero_documento },
      { 
        $set: { 
          intentos_fallidos: 0,
          ultimo_acceso: new Date()
        }
      }
    );
    
    db.auditoria.insertOne({
      numero_documento: numero_documento,
      accion: 'Inicio de sesión exitoso',
      tabla_afectada: 'usuarios',
      fecha_hora: new Date()
    });
    
    const rol = db.roles.findOne({ _id: usuario.id_rol });
    
    return {
      resultado: true,
      mensaje: 'Autenticación exitosa',
      id_rol: usuario.id_rol,
      nombre_completo: usuario.nombres + ' ' + usuario.apellidos
    };
  } else {
    const nuevos_intentos = usuario.intentos_fallidos + 1;
    const update = { $inc: { intentos_fallidos: 1 } };
    
    if (nuevos_intentos >= 3) {
      update.$set = { estado: 'bloqueado' };
    }
    
    db.usuarios.updateOne(
      { numero_documento: numero_documento },
      update
    );
    
    db.auditoria.insertOne({
      numero_documento: numero_documento,
      accion: nuevos_intentos >= 3 ? 
        'Usuario bloqueado por intentos fallidos' : 
        'Intento de inicio de sesión fallido',
      tabla_afectada: 'usuarios',
      fecha_hora: new Date()
    });
    
    return {
      resultado: false,
      mensaje: nuevos_intentos >= 3 ? 
        'Usuario bloqueado por múltiples intentos fallidos. Contacte al administrador.' :
        'Credenciales incorrectas'
    };
  }
}

// ============================================
// sp_registrar_usuario
// ============================================
function sp_registrar_usuario(numero_documento, tipo_documento, nombres, apellidos, correo, contrasena, id_rol) {
  const crypto = require('crypto');
  
  // Verificar si ya existe
  const existe_doc = db.usuarios.findOne({ numero_documento: numero_documento });
  if (existe_doc) {
    return { resultado: false, mensaje: 'Ya existe un usuario con este número de documento' };
  }
  
  const existe_correo = db.usuarios.findOne({ correo: correo });
  if (existe_correo) {
    return { resultado: false, mensaje: 'Ya existe un usuario con este correo electrónico' };
  }
  
  if (![1, 2, 3].includes(id_rol)) {
    return { resultado: false, mensaje: 'Solo se pueden registrar usuarios con roles de Estudiante, Docente o Personal Administrativo' };
  }
  
  const hash = crypto.createHash('sha256').update(contrasena).digest('hex');
  
  db.usuarios.insertOne({
    numero_documento: numero_documento,
    tipo_documento: tipo_documento,
    nombres: nombres,
    apellidos: apellidos,
    correo: correo,
    contrasena: hash,
    id_rol: id_rol,
    estado: 'activo',
    intentos_fallidos: 0,
    ultimo_acceso: null,
    creado_en: new Date(),
    actualizado_en: new Date()
  });
  
  const rol = db.roles.findOne({ _id: id_rol });
  
  db.auditoria.insertOne({
    numero_documento: numero_documento,
    accion: 'Registro de nuevo usuario',
    tabla_afectada: 'usuarios',
    datos_nuevos: `Rol: ${rol.nombre_rol}, Nombre: ${nombres} ${apellidos}`,
    fecha_hora: new Date()
  });
  
  return { resultado: true, mensaje: 'Usuario registrado exitosamente' };
}

// ============================================
// sp_reportar_incidente
// ============================================
function sp_reportar_incidente(numero_documento, id_tipo, descripcion, ubicacion, fecha_incidente) {
  const usuario = db.usuarios.findOne({ numero_documento: numero_documento });
  
  if (!usuario) {
    return { resultado: false, mensaje: 'Usuario no encontrado', id_reporte: null };
  }
  
  const reporte = {
    numero_documento: numero_documento,
    id_tipo: id_tipo,
    descripcion: descripcion,
    ubicacion: ubicacion,
    fecha_incidente: new Date(fecha_incidente),
    fecha_reporte: new Date(),
    estado: 'pendiente'
  };
  
  const resultado = db.reportes_incidente.insertOne(reporte);
  const id_reporte = resultado.insertedId;
  
  // Crear registro de aprobación pendiente
  db.aprobaciones_reportes.insertOne({
    id_reporte: id_reporte,
    id_objeto: null,
    estado: 'pendiente',
    fecha_aprobacion: new Date()
  });
  
  const tipo = db.tipos_incidente.findOne({ _id: id_tipo });
  
  db.auditoria.insertOne({
    numero_documento: numero_documento,
    accion: 'Reporte de incidente (pendiente)',
    tabla_afectada: 'reportes_incidente',
    id_registro_afectado: id_reporte,
    datos_nuevos: `Tipo: ${tipo.nombre}, Ubicación: ${ubicacion}`,
    fecha_hora: new Date()
  });
  
  // Notificar a administradores
  const administradores = db.usuarios.find({ id_rol: 5, estado: 'activo' }).toArray();
  const notificaciones = administradores.map(admin => ({
    numero_documento: admin.numero_documento,
    titulo: 'Nuevo incidente pendiente de aprobación',
    mensaje: `Tipo: ${tipo.nombre}, Ubicación: ${ubicacion}`,
    tipo: 'incidente',
    id_referencia: id_reporte,
    leida: 0,
    fecha_hora: new Date()
  }));
  
  if (notificaciones.length > 0) {
    db.notificaciones.insertMany(notificaciones);
  }
  
  return {
    resultado: true,
    mensaje: 'Incidente reportado exitosamente. Esperando aprobación del administrador.',
    id_reporte: id_reporte
  };
}

// ============================================
// sp_reportar_objeto_objeto
// ============================================
function sp_reportar_objeto_objeto(numero_documento, tipo_objeto, descripcion, ubicacion, fecha_evento, tipo_reporte, imagen_url) {
  const objeto = {
    numero_documento_reporta: numero_documento,
    tipo_objeto: tipo_objeto,
    descripcion: descripcion,
    ubicacion: ubicacion,
    ubicacion_perdida: ubicacion,
    fecha_perdida: new Date(fecha_evento),
    tipo_reporte: tipo_reporte,
    fecha_reporte: new Date(),
    estado: 'pendiente',
    imagen_url: imagen_url
  };
  
  const resultado = db.objetos_perdidos.insertOne(objeto);
  const id_objeto = resultado.insertedId;
  
  // Notificar a usuarios activos
  const usuarios = db.usuarios.find({ 
    estado: 'activo', 
    numero_documento: { $ne: numero_documento } 
  }).toArray();
  
  const notificaciones = usuarios.map(usuario => ({
    numero_documento: usuario.numero_documento,
    titulo: `Nuevo objeto ${tipo_reporte} reportado (#${id_objeto})`,
    mensaje: `Tipo: ${tipo_objeto}, Ubicación: ${ubicacion}`,
    tipo: 'objeto',
    id_referencia: id_objeto,
    leida: 0,
    fecha_hora: new Date()
  }));
  
  if (notificaciones.length > 0) {
    db.notificaciones.insertMany(notificaciones);
  }
  
  // Notificar al usuario que reportó
  db.notificaciones.insertOne({
    numero_documento: numero_documento,
    titulo: `Objeto ${tipo_reporte} reportado (#${id_objeto})`,
    mensaje: 'Tu reporte ha sido registrado y está siendo revisado',
    tipo: 'objeto',
    id_referencia: id_objeto,
    leida: 0,
    fecha_hora: new Date()
  });
  
  return {
    resultado: true,
    mensaje: 'Objeto reportado exitosamente.',
    id_objeto: id_objeto
  };
}

// ============================================
// sp_actualizar_estado_incidente
// ============================================
function sp_actualizar_estado_incidente(id_reporte, estado, actualizado_por) {
  const usuario = db.usuarios.findOne({ numero_documento: actualizado_por });
  const reporte = db.reportes_incidente.findOne({ _id: id_reporte });
  
  if (!usuario) {
    return { resultado: false, mensaje: 'Usuario no encontrado' };
  }
  
  if (!reporte) {
    return { resultado: false, mensaje: 'Reporte no encontrado' };
  }
  
  // Verificar permisos
  if (![4, 5].includes(usuario.id_rol) && reporte.numero_documento !== actualizado_por) {
    return { resultado: false, mensaje: 'No tiene permisos para actualizar estados de incidentes' };
  }
  
  db.reportes_incidente.updateOne(
    { _id: id_reporte },
    { $set: { estado: estado } }
  );
  
  db.auditoria.insertOne({
    numero_documento: actualizado_por,
    accion: 'Actualización de estado de incidente',
    tabla_afectada: 'reportes_incidente',
    id_registro_afectado: id_reporte,
    datos_nuevos: `Nuevo estado: ${estado}`,
    fecha_hora: new Date()
  });
  
  db.notificaciones.insertOne({
    numero_documento: reporte.numero_documento,
    titulo: `Estado de reporte actualizado (#${id_reporte})`,
    mensaje: `El estado de su reporte ha cambiado a: ${estado}`,
    tipo: 'incidente',
    id_referencia: id_reporte,
    leida: 0,
    fecha_hora: new Date()
  });
  
  return { resultado: true, mensaje: 'Estado del incidente actualizado exitosamente' };
}

// ============================================
// sp_marcar_objeto_encontrado
// ============================================
function sp_marcar_objeto_encontrado(id_objeto, numero_documento) {
  const usuario = db.usuarios.findOne({ numero_documento: numero_documento });
  const objeto = db.objetos_perdidos.findOne({ _id: id_objeto });
  
  if (!usuario) {
    return { resultado: false, mensaje: 'Usuario no encontrado' };
  }
  
  if (!objeto) {
    return { resultado: false, mensaje: 'Objeto no encontrado' };
  }
  
  // Verificar permisos
  if (![4, 5].includes(usuario.id_rol) && objeto.numero_documento !== numero_documento) {
    return { resultado: false, mensaje: 'No tiene permisos para marcar este objeto como encontrado' };
  }
  
  db.objetos_perdidos.updateOne(
    { _id: id_objeto },
    { $set: { estado: 'encontrado' } }
  );
  
  db.auditoria.insertOne({
    numero_documento: numero_documento,
    accion: 'Marcar objeto como encontrado',
    tabla_afectada: 'objetos_perdidos',
    id_registro_afectado: id_objeto,
    datos_nuevos: 'Estado: encontrado',
    fecha_hora: new Date()
  });
  
  db.notificaciones.insertOne({
    numero_documento: objeto.numero_documento,
    titulo: `Objeto encontrado (#${id_objeto})`,
    mensaje: 'El objeto que reportaste como perdido ha sido encontrado',
    tipo: 'objeto',
    id_referencia: id_objeto,
    leida: 0,
    fecha_hora: new Date()
  });
  
  return { resultado: true, mensaje: 'Objeto marcado como encontrado exitosamente' };
}

// ============================================
// sp_marcar_objeto_devuelto
// ============================================
function sp_marcar_objeto_devuelto(id_objeto, numero_documento) {
  const usuario = db.usuarios.findOne({ numero_documento: numero_documento });
  const objeto = db.objetos_perdidos.findOne({ _id: id_objeto });
  
  if (!usuario) {
    return { resultado: false, mensaje: 'Usuario no encontrado' };
  }
  
  if (!objeto) {
    return { resultado: false, mensaje: 'Objeto no encontrado' };
  }
  
  if (![4, 5].includes(usuario.id_rol)) {
    return { resultado: false, mensaje: 'No tiene permisos para marcar objetos como devueltos' };
  }
  
  if (objeto.estado !== 'encontrado') {
    return { resultado: false, mensaje: 'El objeto debe estar marcado como encontrado antes de marcarlo como devuelto' };
  }
  
  db.objetos_perdidos.updateOne(
    { _id: id_objeto },
    { $set: { estado: 'devuelto' } }
  );
  
  db.auditoria.insertOne({
    numero_documento: numero_documento,
    accion: 'Marcar objeto como devuelto',
    tabla_afectada: 'objetos_perdidos',
    id_registro_afectado: id_objeto,
    datos_nuevos: 'Estado: devuelto',
    fecha_hora: new Date()
  });
  
  return { resultado: true, mensaje: 'Objeto marcado como devuelto exitosamente' };
}

// ============================================
// sp_registrar_acceso
// ============================================
function sp_registrar_acceso(numero_documento, tipo_movimiento, registrado_por, observaciones) {
  const registrador = db.usuarios.findOne({ numero_documento: registrado_por });
  const usuario = db.usuarios.findOne({ numero_documento: numero_documento });
  
  if (![4, 5].includes(registrador.id_rol)) {
    return { resultado: false, mensaje: 'Solo celadores y administradores pueden registrar accesos' };
  }
  
  if (!usuario) {
    return { resultado: false, mensaje: 'Usuario no encontrado' };
  }
  
  // Obtener último movimiento
  const ultimo_registro = db.registros_acceso.findOne(
    { numero_documento: numero_documento },
    { sort: { fecha_hora: -1 } }
  );
  
  if (ultimo_registro && ultimo_registro.tipo_movimiento === tipo_movimiento) {
    return { 
      resultado: false, 
      mensaje: `No se puede registrar ${tipo_movimiento} porque el último registro fue ${ultimo_registro.tipo_movimiento}` 
    };
  }
  
  db.registros_acceso.insertOne({
    numero_documento: numero_documento,
    id_visitante: null,
    placa_vehiculo: null,
    tipo_movimiento: tipo_movimiento,
    fecha_hora: new Date(),
    registrado_por: registrado_por,
    observaciones: observaciones
  });
  
  db.auditoria.insertOne({
    numero_documento: registrado_por,
    accion: `Registro de ${tipo_movimiento} de usuario`,
    tabla_afectada: 'registros_acceso',
    datos_nuevos: `Usuario: ${numero_documento}`,
    fecha_hora: new Date()
  });
  
  return { resultado: true, mensaje: `Registro de ${tipo_movimiento} exitoso` };
}

// ============================================
// sp_registrar_acceso_visitante
// ============================================
function sp_registrar_acceso_visitante(tipo_documento, numero_documento, nombres, apellidos, motivo_visita, contacto, tipo_movimiento, registrado_por, observaciones, placa_vehiculo) {
  const registrador = db.usuarios.findOne({ numero_documento: registrado_por });
  
  if (![4, 5].includes(registrador.id_rol)) {
    return { 
      resultado: false, 
      mensaje: 'Solo celadores y administradores pueden registrar accesos',
      id_visitante: null
    };
  }
  
  if (tipo_movimiento === 'entrada') {
    // Buscar o crear visitante
    let visitante = db.visitantes.findOne({ numero_documento: numero_documento });
    
    if (visitante) {
      db.visitantes.updateOne(
        { _id: visitante._id },
        {
          $set: {
            nombres: nombres,
            apellidos: apellidos,
            motivo_visita: motivo_visita,
            contacto: contacto
          }
        }
      );
    } else {
      const resultado = db.visitantes.insertOne({
        tipo_documento: tipo_documento,
        numero_documento: numero_documento,
        nombres: nombres,
        apellidos: apellidos,
        motivo_visita: motivo_visita,
        contacto: contacto,
        creado_en: new Date()
      });
      visitante = { _id: resultado.insertedId };
    }
    
    db.registros_acceso.insertOne({
      numero_documento: null,
      id_visitante: visitante._id,
      placa_vehiculo: placa_vehiculo,
      tipo_movimiento: 'entrada',
      fecha_hora: new Date(),
      registrado_por: registrado_por,
      observaciones: observaciones
    });
    
    return {
      resultado: true,
      mensaje: 'Registro de entrada de visitante exitoso',
      id_visitante: visitante._id
    };
  } else {
    // Buscar visitante activo (con entrada pero sin salida)
    const entrada = db.registros_acceso.findOne(
      {
        tipo_movimiento: 'entrada',
        id_visitante: { $exists: true }
      },
      { sort: { fecha_hora: -1 } }
    );
    
    if (!entrada) {
      return {
        resultado: false,
        mensaje: 'No se encontró un visitante con entrada registrada pero sin salida',
        id_visitante: null
      };
    }
    
    const visitante = db.visitantes.findOne({ numero_documento: numero_documento });
    
    if (!visitante || visitante._id.toString() !== entrada.id_visitante.toString()) {
      return {
        resultado: false,
        mensaje: 'No se encontró un visitante con entrada registrada pero sin salida',
        id_visitante: null
      };
    }
    
    // Verificar que no tenga salida posterior
    const salida_posterior = db.registros_acceso.findOne({
      id_visitante: visitante._id,
      tipo_movimiento: 'salida',
      fecha_hora: { $gt: entrada.fecha_hora }
    });
    
    if (salida_posterior) {
      return {
        resultado: false,
        mensaje: 'No se encontró un visitante con entrada registrada pero sin salida',
        id_visitante: null
      };
    }
    
    db.registros_acceso.insertOne({
      numero_documento: null,
      id_visitante: visitante._id,
      placa_vehiculo: placa_vehiculo,
      tipo_movimiento: 'salida',
      fecha_hora: new Date(),
      registrado_por: registrado_por,
      observaciones: observaciones
    });
    
    return {
      resultado: true,
      mensaje: 'Registro de salida de visitante exitoso',
      id_visitante: visitante._id
    };
  }
}

// ============================================
// FUNCIONES AUXILIARES (Equivalente a funciones SQL)
// ============================================

// fn_contar_incidentes
function fn_contar_incidentes(numero_documento, estado) {
  const query = {};
  if (numero_documento) query.numero_documento = numero_documento;
  if (estado) query.estado = estado;
  return db.reportes_incidente.countDocuments(query);
}

// fn_contar_objetos
function fn_contar_objetos(numero_documento, estado) {
  const query = {};
  if (numero_documento) query.numero_documento = numero_documento;
  if (estado) query.estado = estado;
  return db.objetos_perdidos.countDocuments(query);
}

// fn_contar_notificaciones_no_leidas
function fn_contar_notificaciones_no_leidas(numero_documento) {
  return db.notificaciones.countDocuments({
    numero_documento: numero_documento,
    leida: 0
  });
}

// fn_persona_en_campus
function fn_persona_en_campus(numero_documento) {
  const ultimo = db.registros_acceso.findOne(
    { numero_documento: numero_documento },
    { sort: { fecha_hora: -1 } }
  );
  return ultimo && ultimo.tipo_movimiento === 'entrada';
}

// fn_visitante_en_campus
function fn_visitante_en_campus(id_visitante) {
  const ultimo = db.registros_acceso.findOne(
    { id_visitante: id_visitante },
    { sort: { fecha_hora: -1 } }
  );
  return ultimo && ultimo.tipo_movimiento === 'entrada';
}

print("Funciones JavaScript cargadas exitosamente");
print("Estas funciones deben ser implementadas en tu aplicación Node.js");

