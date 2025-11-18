-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 15-09-2025 a las 18:50:06
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `electiva_3`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_actualizar_estado_incidente` (IN `p_id_reporte` INT, IN `p_estado` ENUM('reportado','en_revision','resuelto','archivado'), IN `p_actualizado_por` VARCHAR(20), OUT `p_resultado` BOOLEAN, OUT `p_mensaje` VARCHAR(255))   BEGIN
    DECLARE v_id_rol INT;
    DECLARE v_numero_documento VARCHAR(20);
    
    -- Obtener tipo de usuario y quien reportó el incidente
    SELECT id_rol INTO v_id_rol FROM usuarios WHERE numero_documento = p_actualizado_por;
    SELECT numero_documento INTO v_numero_documento FROM reportes_incidente WHERE id_reporte = p_id_reporte;
    
    -- Verificar permisos (solo celadores, administradores o quien reportó el incidente pueden actualizar)
    IF v_id_rol NOT IN (4, 5) AND v_numero_documento != p_actualizado_por THEN
        SET p_resultado = FALSE;
        SET p_mensaje = 'No tiene permisos para actualizar estados de incidentes';
    ELSEIF NOT EXISTS (SELECT 1 FROM reportes_incidente WHERE id_reporte = p_id_reporte) THEN
        SET p_resultado = FALSE;
        SET p_mensaje = 'Reporte no encontrado';
    ELSE
        -- Actualizar estado
        UPDATE reportes_incidente
        SET estado = p_estado
        WHERE id_reporte = p_id_reporte;
        
        SET p_resultado = TRUE;
        SET p_mensaje = 'Estado del incidente actualizado exitosamente';
        
        -- Registrar en auditoría
        INSERT INTO auditoria (numero_documento, accion, tabla_afectada, id_registro_afectado, datos_nuevos)
        VALUES (p_actualizado_por, 'Actualización de estado de incidente', 'reportes_incidente', 
               p_id_reporte, CONCAT('Nuevo estado: ', p_estado));
        
        -- Notificar al usuario que reportó el incidente
        INSERT INTO notificaciones (numero_documento, titulo, mensaje, tipo, id_referencia)
        VALUES (v_numero_documento, 
               CONCAT('Estado de reporte actualizado (#', p_id_reporte, ')'), 
               CONCAT('El estado de su reporte ha cambiado a: ', p_estado), 
               'incidente', 
               p_id_reporte);
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_agregar_evidencia_incidente` (IN `p_id_reporte` INT, IN `p_tipo_archivo` VARCHAR(50), IN `p_nombre_archivo` VARCHAR(255), IN `p_ruta_archivo` VARCHAR(255), IN `p_subido_por` VARCHAR(20), OUT `p_resultado` BOOLEAN, OUT `p_mensaje` VARCHAR(255))   BEGIN
    -- Verificar si el reporte existe
    IF NOT EXISTS (SELECT 1 FROM reportes_incidente WHERE id_reporte = p_id_reporte) THEN
        SET p_resultado = FALSE;
        SET p_mensaje = 'Reporte no encontrado';
    ELSE
        -- Agregar la evidencia
        INSERT INTO evidencias_incidente (id_reporte, tipo_archivo, nombre_archivo, ruta_archivo)
        VALUES (p_id_reporte, p_tipo_archivo, p_nombre_archivo, p_ruta_archivo);
        
        SET p_resultado = TRUE;
        SET p_mensaje = 'Evidencia agregada exitosamente';
        
        -- Registrar en auditoría
        INSERT INTO auditoria (numero_documento, accion, tabla_afectada, id_registro_afectado, datos_nuevos)
        VALUES (p_subido_por, 'Agregar evidencia a incidente', 'evidencias_incidente', 
               LAST_INSERT_ID(), CONCAT('Reporte ID: ', p_id_reporte));
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_agregar_imagen_objeto` (IN `p_id_objeto` INT, IN `p_tipo_archivo` VARCHAR(50), IN `p_nombre_archivo` VARCHAR(255), IN `p_ruta_archivo` VARCHAR(255), IN `p_subido_por` VARCHAR(20), OUT `p_resultado` BOOLEAN, OUT `p_mensaje` VARCHAR(255))   BEGIN
    -- Verificar si el objeto existe
    IF NOT EXISTS (SELECT 1 FROM objetos_perdidos WHERE id_objeto = p_id_objeto) THEN
        SET p_resultado = FALSE;
        SET p_mensaje = 'Objeto no encontrado';
    ELSE
        -- Agregar la imagen
        INSERT INTO imagenes_objeto (id_objeto, tipo_archivo, nombre_archivo, ruta_archivo)
        VALUES (p_id_objeto, p_tipo_archivo, p_nombre_archivo, p_ruta_archivo);
        
        SET p_resultado = TRUE;
        SET p_mensaje = 'Imagen agregada exitosamente';
        
        -- Registrar en auditoría
        INSERT INTO auditoria (numero_documento, accion, tabla_afectada, id_registro_afectado, datos_nuevos)
        VALUES (p_subido_por, 'Agregar imagen a objeto perdido', 'imagenes_objeto', 
               LAST_INSERT_ID(), CONCAT('Objeto ID: ', p_id_objeto));
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_aprobar_reporte` (IN `p_id_reporte` INT, IN `p_id_objeto` INT, IN `p_aprobado_por` VARCHAR(20), IN `p_estado` ENUM('aprobado','rechazado'), IN `p_comentarios` TEXT, OUT `p_resultado` BOOLEAN, OUT `p_mensaje` VARCHAR(255))   BEGIN
    DECLARE v_id_rol INT;
    DECLARE v_numero_documento VARCHAR(20);
    
    -- Verificar que el aprobador sea administrador
    SELECT id_rol INTO v_id_rol FROM usuarios WHERE numero_documento = p_aprobado_por;
    
    IF v_id_rol != 5 THEN
        SET p_resultado = FALSE;
        SET p_mensaje = 'Solo los administradores pueden aprobar reportes';
    ELSEIF p_id_reporte IS NOT NULL AND NOT EXISTS (SELECT 1 FROM reportes_incidente WHERE id_reporte = p_id_reporte) THEN
        SET p_resultado = FALSE;
        SET p_mensaje = 'Reporte no encontrado';
    ELSEIF p_id_objeto IS NOT NULL AND NOT EXISTS (SELECT 1 FROM objetos_perdidos WHERE id_objeto = p_id_objeto) THEN
        SET p_resultado = FALSE;
        SET p_mensaje = 'Objeto no encontrado';
    ELSE
        -- Actualizar estado de aprobación
        UPDATE aprobaciones_reportes
        SET estado = p_estado,
            aprobado_por = p_aprobado_por,
            comentarios = p_comentarios
        WHERE (id_reporte = p_id_reporte OR id_objeto = p_id_objeto);
        
        -- Actualizar estado del reporte u objeto
        IF p_id_reporte IS NOT NULL THEN
            UPDATE reportes_incidente
            SET estado = CASE WHEN p_estado = 'aprobado' THEN 'reportado' ELSE 'archivado' END
            WHERE id_reporte = p_id_reporte;
            
            -- Obtener quien reportó el incidente
            SELECT numero_documento INTO v_numero_documento FROM reportes_incidente WHERE id_reporte = p_id_reporte;
            
            -- Notificar al usuario
            INSERT INTO notificaciones (numero_documento, titulo, mensaje, tipo, id_referencia)
            VALUES (v_numero_documento, 
                   CONCAT('Reporte #', p_id_reporte, ' ', UPPER(p_estado)), 
                   CONCAT('Tu reporte ha sido ', p_estado, '. ', IFNULL(p_comentarios, '')), 
                   'incidente', 
                   p_id_reporte);
        ELSE
            UPDATE objetos_perdidos
            SET estado = CASE WHEN p_estado = 'aprobado' THEN 'perdido' ELSE 'archivado' END
            WHERE id_objeto = p_id_objeto;
            
            -- Obtener quien reportó el objeto
            SELECT numero_documento INTO v_numero_documento FROM objetos_perdidos WHERE id_objeto = p_id_objeto;
            
            -- Notificar al usuario
            INSERT INTO notificaciones (numero_documento, titulo, mensaje, tipo, id_referencia)
            VALUES (v_numero_documento, 
                   CONCAT('Objeto #', p_id_objeto, ' ', UPPER(p_estado)), 
                   CONCAT('Tu objeto reportado ha sido ', p_estado, '. ', IFNULL(p_comentarios, '')), 
                   'objeto', 
                   p_id_objeto);
        END IF;
        
        SET p_resultado = TRUE;
        SET p_mensaje = CONCAT('Reporte ', p_estado, ' exitosamente');
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_autenticar_usuario` (IN `p_numero_documento` VARCHAR(20), IN `p_contrasena` VARCHAR(255), OUT `p_resultado` BOOLEAN, OUT `p_mensaje` VARCHAR(255), OUT `p_id_rol` INT, OUT `p_nombre_completo` VARCHAR(201))   BEGIN
    DECLARE v_estado VARCHAR(20);
    DECLARE v_intentos INT;
    DECLARE v_contrasena_db VARCHAR(255);
    
    SELECT u.estado, u.intentos_fallidos, u.contrasena, r.id_rol, CONCAT(u.nombres, ' ', u.apellidos)
    INTO v_estado, v_intentos, v_contrasena_db, p_id_rol, p_nombre_completo
    FROM usuarios u
    JOIN roles r ON u.id_rol = r.id_rol
    WHERE u.numero_documento = p_numero_documento;
    
    IF v_estado IS NULL THEN
        SET p_resultado = FALSE;
        SET p_mensaje = 'Usuario no encontrado';
    ELSEIF v_estado = 'bloqueado' THEN
        SET p_resultado = FALSE;
        SET p_mensaje = 'Usuario bloqueado. Contacte al administrador.';
    ELSEIF v_estado = 'inactivo' THEN
        SET p_resultado = FALSE;
        SET p_mensaje = 'Usuario inactivo. Contacte al administrador.';
    ELSEIF v_contrasena_db = SHA2(p_contrasena, 256) THEN
        -- Autenticación exitosa
        UPDATE usuarios 
        SET intentos_fallidos = 0, 
            ultimo_acceso = NOW() 
        WHERE numero_documento = p_numero_documento;
        
        SET p_resultado = TRUE;
        SET p_mensaje = 'Autenticación exitosa';
        
        -- Registrar en auditoría
        INSERT INTO auditoria (numero_documento, accion, tabla_afectada, ip_origen)
        VALUES (p_numero_documento, 'Inicio de sesión exitoso', 'usuarios', @ip_origen);
    ELSE
        -- Contraseña incorrecta
        SET p_resultado = FALSE;
        SET p_mensaje = 'Credenciales incorrectas';
        
        -- Incrementar intentos fallidos
        UPDATE usuarios 
        SET intentos_fallidos = intentos_fallidos + 1 
        WHERE numero_documento = p_numero_documento;
        
        -- Bloquear usuario si supera los intentos
        IF v_intentos + 1 >= 3 THEN
            UPDATE usuarios 
            SET estado = 'bloqueado'
            WHERE numero_documento = p_numero_documento;
            
            SET p_mensaje = 'Usuario bloqueado por múltiples intentos fallidos. Contacte al administrador.';
            
            -- Registrar en auditoría
            INSERT INTO auditoria (numero_documento, accion, tabla_afectada, ip_origen)
            VALUES (p_numero_documento, 'Usuario bloqueado por intentos fallidos', 'usuarios', @ip_origen);
        END IF;
        
        -- Registrar en auditoría
        INSERT INTO auditoria (numero_documento, accion, tabla_afectada, ip_origen)
        VALUES (p_numero_documento, 'Intento de inicio de sesión fallido', 'usuarios', @ip_origen);
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_marcar_objeto_devuelto` (IN `p_id_objeto` INT, IN `p_numero_documento` VARCHAR(20), OUT `p_resultado` BOOLEAN, OUT `p_mensaje` VARCHAR(255))   BEGIN
    DECLARE v_id_rol INT;
    DECLARE v_estado VARCHAR(20);
    
    -- Obtener tipo de usuario y estado actual del objeto
    SELECT id_rol INTO v_id_rol FROM usuarios WHERE numero_documento = p_numero_documento;
    SELECT estado INTO v_estado FROM objetos_perdidos WHERE id_objeto = p_id_objeto;
    
    -- Verificar permisos (solo celadores y administradores pueden marcar como devuelto)
    IF v_id_rol NOT IN (4, 5) THEN
        SET p_resultado = FALSE;
        SET p_mensaje = 'No tiene permisos para marcar objetos como devueltos';
    ELSEIF NOT EXISTS (SELECT 1 FROM objetos_perdidos WHERE id_objeto = p_id_objeto) THEN
        SET p_resultado = FALSE;
        SET p_mensaje = 'Objeto no encontrado';
    ELSEIF v_estado != 'encontrado' THEN
        SET p_resultado = FALSE;
        SET p_mensaje = 'El objeto debe estar marcado como encontrado antes de marcarlo como devuelto';
    ELSE
        -- Actualizar estado
        UPDATE objetos_perdidos
        SET estado = 'devuelto'
        WHERE id_objeto = p_id_objeto;
        
        SET p_resultado = TRUE;
        SET p_mensaje = 'Objeto marcado como devuelto exitosamente';
        
        -- Registrar en auditoría
        INSERT INTO auditoria (numero_documento, accion, tabla_afectada, id_registro_afectado, datos_nuevos)
        VALUES (p_numero_documento, 'Marcar objeto como devuelto', 'objetos_perdidos', 
               p_id_objeto, 'Estado: devuelto');
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_marcar_objeto_encontrado` (IN `p_id_objeto` INT, IN `p_numero_documento` VARCHAR(20), OUT `p_resultado` BOOLEAN, OUT `p_mensaje` VARCHAR(255))   BEGIN
    DECLARE v_id_rol INT;
    DECLARE v_numero_documento_reportante VARCHAR(20);
    
    -- Obtener tipo de usuario y quien reportó el objeto
    SELECT id_rol INTO v_id_rol FROM usuarios WHERE numero_documento = p_numero_documento;
    SELECT numero_documento INTO v_numero_documento_reportante FROM objetos_perdidos WHERE id_objeto = p_id_objeto;
    
    -- Verificar permisos (solo celadores, administradores o quien reportó el objeto pueden marcarlo como encontrado)
    IF v_id_rol NOT IN (4, 5) AND v_numero_documento_reportante != p_numero_documento THEN
        SET p_resultado = FALSE;
        SET p_mensaje = 'No tiene permisos para marcar este objeto como encontrado';
    ELSEIF NOT EXISTS (SELECT 1 FROM objetos_perdidos WHERE id_objeto = p_id_objeto) THEN
        SET p_resultado = FALSE;
        SET p_mensaje = 'Objeto no encontrado';
    ELSE
        -- Actualizar estado
        UPDATE objetos_perdidos
        SET estado = 'encontrado'
        WHERE id_objeto = p_id_objeto;
        
        SET p_resultado = TRUE;
        SET p_mensaje = 'Objeto marcado como encontrado exitosamente';
        
        -- Registrar en auditoría
        INSERT INTO auditoria (numero_documento, accion, tabla_afectada, id_registro_afectado, datos_nuevos)
        VALUES (p_numero_documento, 'Marcar objeto como encontrado', 'objetos_perdidos', 
               p_id_objeto, 'Estado: encontrado');
        
        -- Notificar al usuario que reportó el objeto perdido
        INSERT INTO notificaciones (numero_documento, titulo, mensaje, tipo, id_referencia)
        VALUES (v_numero_documento_reportante, 
               CONCAT('Objeto encontrado (#', p_id_objeto, ')'), 
               'El objeto que reportaste como perdido ha sido encontrado', 
               'objeto', 
               p_id_objeto);
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_registrar_acceso` (IN `p_numero_documento` VARCHAR(20), IN `p_tipo_movimiento` ENUM('entrada','salida'), IN `p_registrado_por` VARCHAR(20), IN `p_observaciones` TEXT, OUT `p_resultado` BOOLEAN, OUT `p_mensaje` VARCHAR(255))   BEGIN
    DECLARE v_ultimo_movimiento ENUM('entrada', 'salida');
    DECLARE v_id_rol INT;
    
    -- Verificar que el registrador sea celador o administrador
    SELECT id_rol INTO v_id_rol FROM usuarios WHERE numero_documento = p_registrado_por;
    
    IF v_id_rol NOT IN (4, 5) THEN
        SET p_resultado = FALSE;
        SET p_mensaje = 'Solo celadores y administradores pueden registrar accesos';
    ELSEIF NOT EXISTS (SELECT 1 FROM usuarios WHERE numero_documento = p_numero_documento) THEN
        SET p_resultado = FALSE;
        SET p_mensaje = 'Usuario no encontrado';
    ELSE
        -- Obtener el último movimiento del usuario
        SELECT tipo_movimiento INTO v_ultimo_movimiento
        FROM registros_acceso
        WHERE numero_documento = p_numero_documento
        ORDER BY fecha_hora DESC
        LIMIT 1;
        
        -- Validar que no se registre dos veces seguidas el mismo tipo de acceso
        IF v_ultimo_movimiento = p_tipo_movimiento THEN
            SET p_resultado = FALSE;
            SET p_mensaje = CONCAT('No se puede registrar ', p_tipo_movimiento, ' porque el último registro fue ', v_ultimo_movimiento);
        ELSE
            -- Registrar el acceso
            INSERT INTO registros_acceso (numero_documento, tipo_movimiento, fecha_hora, registrado_por, observaciones)
            VALUES (p_numero_documento, p_tipo_movimiento, NOW(), p_registrado_por, p_observaciones);
            
            SET p_resultado = TRUE;
            SET p_mensaje = CONCAT('Registro de ', p_tipo_movimiento, ' exitoso');
            
            -- Registrar en auditoría
            INSERT INTO auditoria (numero_documento, accion, tabla_afectada, id_registro_afectado, datos_nuevos)
            VALUES (p_registrado_por, CONCAT('Registro de ', p_tipo_movimiento, ' de usuario'), 'registros_acceso', 
                   LAST_INSERT_ID(), CONCAT('Usuario: ', p_numero_documento));
        END IF;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_registrar_acceso_visitante` (IN `p_tipo_documento` ENUM('CC','TI','CE','PA'), IN `p_numero_documento` VARCHAR(20), IN `p_nombres` VARCHAR(100), IN `p_apellidos` VARCHAR(100), IN `p_motivo_visita` VARCHAR(255), IN `p_contacto` VARCHAR(100), IN `p_tipo_movimiento` ENUM('entrada','salida'), IN `p_registrado_por` VARCHAR(20), IN `p_observaciones` TEXT, IN `p_placa_vehiculo` VARCHAR(20), OUT `p_resultado` BOOLEAN, OUT `p_mensaje` VARCHAR(255), OUT `p_id_visitante` INT)   BEGIN
    DECLARE v_visitante_id INT;
    DECLARE v_ultimo_movimiento ENUM('entrada', 'salida');
    DECLARE v_id_rol INT;
    DECLARE sql_state_code VARCHAR(5);
    DECLARE error_message TEXT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1
            sql_state_code = RETURNED_SQLSTATE,
            error_message = MESSAGE_TEXT;
        ROLLBACK;
        SET p_resultado = FALSE;
        SET p_mensaje = CONCAT('Error al registrar el acceso: ', sql_state_code, ' - ', error_message);
    END;

    START TRANSACTION;

    -- Verificar que el registrador sea celador o administrador
    SELECT id_rol INTO v_id_rol FROM usuarios WHERE numero_documento = p_registrado_por;

    IF v_id_rol NOT IN (4, 5) THEN
        SET p_resultado = FALSE;
        SET p_mensaje = 'Solo celadores y administradores pueden registrar accesos';
        SET p_id_visitante = NULL;
    ELSEIF p_tipo_movimiento = 'entrada' THEN
        -- Registrar nuevo visitante (o actualizar si ya existe para entrada)
        -- Si el visitante ya existe, no se actualizan los nombres/apellidos aquí
        -- Deberías considerar si quieres actualizar los datos del visitante en cada entrada
        INSERT INTO visitantes (tipo_documento, numero_documento, nombres, apellidos, motivo_visita, contacto)
        VALUES (p_tipo_documento, p_numero_documento, p_nombres, p_apellidos, p_motivo_visita, p_contacto)
        ON DUPLICATE KEY UPDATE
            nombres = p_nombres,
            apellidos = p_apellidos,
            motivo_visita = p_motivo_visita,
            contacto = p_contacto;

        SELECT id_visitante INTO v_visitante_id FROM visitantes WHERE numero_documento = p_numero_documento;
        SET p_id_visitante = v_visitante_id;

        -- Registrar entrada
        INSERT INTO registros_acceso (id_visitante, tipo_movimiento, fecha_hora, registrado_por, observaciones, placa_vehiculo)
        VALUES (v_visitante_id, 'entrada', NOW(), p_registrado_por, p_observaciones, p_placa_vehiculo); -- Se guarda la placa

        SET p_resultado = TRUE;
        SET p_mensaje = 'Registro de entrada de visitante exitoso';

    ELSE -- p_tipo_movimiento = 'salida'
        -- Buscar visitante activo (con entrada pero sin salida)
        SELECT ra.id_visitante INTO v_visitante_id
        FROM registros_acceso ra
        JOIN visitantes v ON ra.id_visitante = v.id_visitante
        WHERE v.numero_documento = p_numero_documento
        AND ra.tipo_movimiento = 'entrada'
        AND NOT EXISTS (
            SELECT 1 FROM registros_acceso ra2
            WHERE ra2.id_visitante = ra.id_visitante
            AND ra2.tipo_movimiento = 'salida'
            AND ra2.fecha_hora > ra.fecha_hora
        )
        ORDER BY ra.fecha_hora DESC
        LIMIT 1;

        IF v_visitante_id IS NULL THEN
            SET p_resultado = FALSE;
            SET p_mensaje = 'No se encontró un visitante con entrada registrada pero sin salida';
            SET p_id_visitante = NULL;
        ELSE
            -- Registrar salida
            INSERT INTO registros_acceso (id_visitante, tipo_movimiento, fecha_hora, registrado_por, observaciones, placa_vehiculo)
            VALUES (v_visitante_id, 'salida', NOW(), p_registrado_por, p_observaciones, p_placa_vehiculo); -- Se guarda la placa

            SET p_resultado = TRUE;
            SET p_mensaje = 'Registro de salida de visitante exitoso';
            SET p_id_visitante = v_visitante_id;

        END IF;
    END IF;

    COMMIT;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_registrar_usuario` (IN `p_numero_documento` VARCHAR(20), IN `p_tipo_documento` ENUM('CC','TI','CE','PA'), IN `p_nombres` VARCHAR(100), IN `p_apellidos` VARCHAR(100), IN `p_correo` VARCHAR(100), IN `p_contrasena` VARCHAR(255), IN `p_id_rol` INT, OUT `p_resultado` BOOLEAN, OUT `p_mensaje` VARCHAR(255))   BEGIN
    DECLARE v_existe_documento INT;
    DECLARE v_existe_correo INT;
    
    SELECT COUNT(*) INTO v_existe_documento FROM usuarios WHERE numero_documento = p_numero_documento;
    SELECT COUNT(*) INTO v_existe_correo FROM usuarios WHERE correo = p_correo;
    
    IF v_existe_documento > 0 THEN
        SET p_resultado = FALSE;
        SET p_mensaje = 'Ya existe un usuario con este número de documento';
    ELSEIF v_existe_correo > 0 THEN
        SET p_resultado = FALSE;
        SET p_mensaje = 'Ya existe un usuario con este correo electrónico';
    ELSEIF p_id_rol NOT IN (1, 2, 3) THEN
        SET p_resultado = FALSE;
        SET p_mensaje = 'Solo se pueden registrar usuarios con roles de Estudiante, Docente o Personal Administrativo';
    ELSE
        INSERT INTO usuarios (numero_documento, tipo_documento, nombres, apellidos, correo, contrasena, id_rol)
        VALUES (p_numero_documento, p_tipo_documento, p_nombres, p_apellidos, p_correo, SHA2(p_contrasena, 256), p_id_rol);
        
        SET p_resultado = TRUE;
        SET p_mensaje = 'Usuario registrado exitosamente';
        
        -- Registrar en auditoría
        INSERT INTO auditoria (numero_documento, accion, tabla_afectada, datos_nuevos)
        VALUES (p_numero_documento, 'Registro de nuevo usuario', 'usuarios', 
                CONCAT('Rol: ', (SELECT nombre_rol FROM roles WHERE id_rol = p_id_rol), ', Nombre: ', p_nombres, ' ', p_apellidos));
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_reportar_incidente` (IN `p_numero_documento` VARCHAR(20), IN `p_id_tipo` INT, IN `p_descripcion` TEXT, IN `p_ubicacion` VARCHAR(100), IN `p_fecha_incidente` DATETIME, OUT `p_resultado` BOOLEAN, OUT `p_mensaje` VARCHAR(255), OUT `p_id_reporte` INT)   BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SET p_resultado = FALSE;
        SET p_mensaje = 'Error al reportar el incidente';
        SET p_id_reporte = NULL;
        ROLLBACK;
    END;
    
    -- Verificar si el usuario existe
    IF NOT EXISTS (SELECT 1 FROM usuarios WHERE numero_documento = p_numero_documento) THEN
        SET p_resultado = FALSE;
        SET p_mensaje = 'Usuario no encontrado';
        SET p_id_reporte = NULL;
    ELSE
        -- Iniciar transacción
        START TRANSACTION;
        
        -- Registrar el incidente
        INSERT INTO reportes_incidente (numero_documento, id_tipo, descripcion, ubicacion, fecha_incidente, estado)
        VALUES (p_numero_documento, p_id_tipo, p_descripcion, p_ubicacion, p_fecha_incidente, 'pendiente');
        
        -- Obtener el ID del reporte recién insertado
        SET p_id_reporte = LAST_INSERT_ID();
        
        -- Crear registro de aprobación pendiente
        INSERT INTO aprobaciones_reportes (id_reporte, estado)
        VALUES (p_id_reporte, 'pendiente');
        
        -- Confirmar transacción
        COMMIT;
        
        SET p_resultado = TRUE;
        SET p_mensaje = 'Incidente reportado exitosamente. Esperando aprobación del administrador.';
        
        -- Registrar en auditoría
        INSERT INTO auditoria (numero_documento, accion, tabla_afectada, id_registro_afectado, datos_nuevos)
        VALUES (p_numero_documento, 'Reporte de incidente (pendiente)', 'reportes_incidente', p_id_reporte, 
               CONCAT('Tipo: ', (SELECT nombre FROM tipos_incidente WHERE id_tipo = p_id_tipo), ', Ubicación: ', p_ubicacion));
        
        -- Notificar a administradores
        INSERT INTO notificaciones (numero_documento, titulo, mensaje, tipo, id_referencia)
        SELECT numero_documento, 
               'Nuevo incidente pendiente de aprobación', 
               CONCAT('Tipo: ', (SELECT nombre FROM tipos_incidente WHERE id_tipo = p_id_tipo), 
                      ', Ubicación: ', p_ubicacion), 
               'incidente', 
               p_id_reporte
        FROM usuarios
        WHERE id_rol = 5; -- Solo Administradores
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_reportar_objeto_objeto` (IN `p_numero_documento` VARCHAR(20), IN `p_tipo_objeto` VARCHAR(100), IN `p_descripcion` TEXT, IN `p_ubicacion` VARCHAR(255), IN `p_fecha_evento` DATE, IN `p_tipo_reporte` ENUM('perdida','hallazgo'), IN `p_imagen_url` VARCHAR(255), OUT `p_resultado` BOOLEAN, OUT `p_mensaje` VARCHAR(255), OUT `p_id_objeto` INT)   BEGIN
    DECLARE sql_state_code VARCHAR(5);
    DECLARE error_message TEXT;

    -- Manejador de errores para capturar excepciones SQL
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1
            sql_state_code = RETURNED_SQLSTATE,
            error_message = MESSAGE_TEXT;
        ROLLBACK;
        SET p_resultado = FALSE;
        SET p_mensaje = CONCAT('Error SQL en SP: ', sql_state_code, ' - ', error_message);
        SET p_id_objeto = NULL;
    END;

    START TRANSACTION;

    -- ¡¡¡CAMBIO CRUCIAL AQUÍ: INSERTAR EN objetos_perdidos !!!
    INSERT INTO objetos_perdidos ( 
        numero_documento_reporta,
        tipo_objeto,
        descripcion,
        ubicacion,
        fecha_perdida,      -- Columna de la tabla (usando fecha_perdida como en tu BD)
        tipo_reporte,
        fecha_reporte,
        estado,
        imagen_url          
    ) VALUES (
        p_numero_documento,
        p_tipo_objeto,
        p_descripcion,
        p_ubicacion,
        p_fecha_evento,     -- Valor del parámetro (se mapea a fecha_perdida)
        p_tipo_reporte,
        NOW(),              -- Fecha de reporte es el momento actual
        'pendiente',        -- Estado inicial
        p_imagen_url        -- Valor del nuevo parámetro
    );

    SET p_id_objeto = LAST_INSERT_ID();
    SET p_resultado = TRUE;
    SET p_mensaje = 'Objeto reportado exitosamente.';

    COMMIT;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_reportar_objeto_perdido` (IN `p_numero_documento` VARCHAR(20), IN `p_tipo_objeto` VARCHAR(100), IN `p_descripcion` TEXT, IN `p_ubicacion` VARCHAR(255), IN `p_fecha_evento` DATE, IN `p_tipo_reporte` ENUM('perdida','hallazgo'), OUT `p_resultado` BOOLEAN, OUT `p_mensaje` VARCHAR(255), OUT `p_id_objeto` INT)   BEGIN
    -- Copia aquí el contenido de sp_reportar_objeto_objeto
    DECLARE sql_state_code VARCHAR(5);
    DECLARE error_message TEXT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1
            sql_state_code = RETURNED_SQLSTATE,
            error_message = MESSAGE_TEXT;
        ROLLBACK;
        SET p_resultado = FALSE;
        SET p_mensaje = CONCAT('Error SQL en SP: ', sql_state_code, ' - ', error_message);
        SET p_id_objeto = NULL;
    END;

    START TRANSACTION;

    INSERT INTO objetos_perdidos ( 
        numero_documento,
        tipo_objeto,
        descripcion,
        ubicacion_perdida,
        fecha_perdida,
        tipo_reporte,
        fecha_reporte,
        estado
    ) VALUES (
        p_numero_documento,
        p_tipo_objeto,
        p_descripcion,
        p_ubicacion,
        p_fecha_evento,
        p_tipo_reporte,
        NOW(),
        'pendiente'
    );

    SET p_id_objeto = LAST_INSERT_ID();
    SET p_resultado = TRUE;
    SET p_mensaje = 'Objeto reportado exitosamente.';

    COMMIT;
END$$

--
-- Funciones
--
CREATE DEFINER=`root`@`localhost` FUNCTION `fn_antiguedad_objeto` (`p_id_objeto` INT) RETURNS VARCHAR(50) CHARSET utf8mb4 COLLATE utf8mb4_general_ci READS SQL DATA BEGIN
    DECLARE v_dias INT;
    
    SELECT DATEDIFF(NOW(), fecha_reporte) INTO v_dias
    FROM objetos_perdidos
    WHERE id_objeto = p_id_objeto;
    
    RETURN CONCAT(v_dias, ' días');
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `fn_contar_incidentes` (`p_numero_documento` VARCHAR(20), `p_estado` VARCHAR(20)) RETURNS INT(11) READS SQL DATA BEGIN
    DECLARE v_total INT;
    
    IF p_numero_documento IS NULL THEN
        SELECT COUNT(*) INTO v_total 
        FROM reportes_incidente 
        WHERE estado = p_estado;
    ELSE
        SELECT COUNT(*) INTO v_total 
        FROM reportes_incidente 
        WHERE numero_documento = p_numero_documento AND estado = p_estado;
    END IF;
    
    RETURN v_total;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `fn_contar_notificaciones_no_leidas` (`p_numero_documento` VARCHAR(20)) RETURNS INT(11) READS SQL DATA BEGIN
    DECLARE v_total INT;
    
    SELECT COUNT(*) INTO v_total 
    FROM notificaciones 
    WHERE numero_documento = p_numero_documento AND leida = 0;
    
    RETURN v_total;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `fn_contar_objetos` (`p_numero_documento` VARCHAR(20), `p_estado` VARCHAR(20)) RETURNS INT(11) READS SQL DATA BEGIN
    DECLARE v_total INT;
    
    IF p_numero_documento IS NULL THEN
        SELECT COUNT(*) INTO v_total 
        FROM objetos_perdidos 
        WHERE estado = p_estado;
    ELSE
        SELECT COUNT(*) INTO v_total 
        FROM objetos_perdidos 
        WHERE numero_documento = p_numero_documento AND estado = p_estado;
    END IF;
    
    RETURN v_total;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `fn_encriptar_contrasena` (`p_contrasena` VARCHAR(255)) RETURNS VARCHAR(255) CHARSET utf8mb4 COLLATE utf8mb4_general_ci DETERMINISTIC BEGIN
    RETURN SHA2(p_contrasena, 256);
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `fn_estadisticas_seguridad` () RETURNS TEXT CHARSET utf8mb4 COLLATE utf8mb4_general_ci READS SQL DATA BEGIN
    DECLARE v_resumen TEXT;
    DECLARE v_incidentes_abiertos INT;
    DECLARE v_objetos_perdidos INT;
    DECLARE v_ingresos_hoy INT;
    
    SET v_incidentes_abiertos = fn_contar_incidentes(NULL, 'reportado') + fn_contar_incidentes(NULL, 'en_revision');
    SET v_objetos_perdidos = fn_contar_objetos(NULL, 'perdido');
    
    SELECT COUNT(*) INTO v_ingresos_hoy
    FROM registros_acceso
    WHERE DATE(fecha_hora) = CURDATE() AND tipo_movimiento = 'entrada';
    
    SET v_resumen = CONCAT(
        'Estadísticas de seguridad:\n',
        '- Incidentes abiertos: ', v_incidentes_abiertos, '\n',
        '- Objetos perdidos: ', v_objetos_perdidos, '\n',
        '- Ingresos hoy: ', v_ingresos_hoy
    );
    
    RETURN v_resumen;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `fn_es_dueño_objeto` (`p_numero_documento` VARCHAR(20), `p_id_objeto` INT) RETURNS TINYINT(1) READS SQL DATA BEGIN
    DECLARE v_dueño VARCHAR(20);
    
    SELECT numero_documento INTO v_dueño 
    FROM objetos_perdidos 
    WHERE id_objeto = p_id_objeto;
    
    IF v_dueño = p_numero_documento THEN
        RETURN TRUE;
    ELSE
        RETURN FALSE;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `fn_persona_en_campus` (`p_numero_documento` VARCHAR(20)) RETURNS TINYINT(1) READS SQL DATA BEGIN
    DECLARE v_ultimo_movimiento VARCHAR(10);
    
    SELECT tipo_movimiento INTO v_ultimo_movimiento
    FROM registros_acceso
    WHERE numero_documento = p_numero_documento
    ORDER BY fecha_hora DESC
    LIMIT 1;
    
    IF v_ultimo_movimiento = 'entrada' THEN
        RETURN TRUE;
    ELSE
        RETURN FALSE;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `fn_resumen_usuario` (`p_numero_documento` VARCHAR(20)) RETURNS TEXT CHARSET utf8mb4 COLLATE utf8mb4_general_ci READS SQL DATA BEGIN
    DECLARE v_resumen TEXT;
    DECLARE v_nombre_completo VARCHAR(201);
    DECLARE v_incidentes_reportados INT;
    DECLARE v_objetos_reportados INT;
    DECLARE v_notificaciones INT;
    
    SELECT CONCAT(nombres, ' ', apellidos) INTO v_nombre_completo
    FROM usuarios
    WHERE numero_documento = p_numero_documento;
    
    SET v_incidentes_reportados = fn_contar_incidentes(p_numero_documento, NULL);
    SET v_objetos_reportados = fn_contar_objetos(p_numero_documento, NULL);
    SET v_notificaciones = fn_contar_notificaciones_no_leidas(p_numero_documento);
    
    SET v_resumen = CONCAT(
        'Resumen para ', v_nombre_completo, ':\n',
        '- Incidentes reportados: ', v_incidentes_reportados, '\n',
        '- Objetos reportados: ', v_objetos_reportados, '\n',
        '- Notificaciones no leídas: ', v_notificaciones
    );
    
    RETURN v_resumen;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `fn_severidad_incidente` (`p_id_reporte` INT) RETURNS VARCHAR(20) CHARSET utf8mb4 COLLATE utf8mb4_general_ci READS SQL DATA BEGIN
    DECLARE v_severidad VARCHAR(20);
    
    SELECT ti.severidad INTO v_severidad
    FROM reportes_incidente ri
    JOIN tipos_incidente ti ON ri.id_tipo = ti.id_tipo
    WHERE ri.id_reporte = p_id_reporte;
    
    RETURN v_severidad;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `fn_tiempo_desde_reporte` (`p_id_reporte` INT) RETURNS VARCHAR(50) CHARSET utf8mb4 COLLATE utf8mb4_general_ci READS SQL DATA BEGIN
    DECLARE v_tiempo_transcurrido VARCHAR(50);
    DECLARE v_minutos INT;
    DECLARE v_horas INT;
    DECLARE v_dias INT;
    
    SELECT TIMESTAMPDIFF(MINUTE, fecha_reporte, NOW()) INTO v_minutos
    FROM reportes_incidente
    WHERE id_reporte = p_id_reporte;
    
    IF v_minutos < 60 THEN
        SET v_tiempo_transcurrido = CONCAT(v_minutos, ' minutos');
    ELSEIF v_minutos < 1440 THEN
        SET v_horas = FLOOR(v_minutos / 60);
        SET v_tiempo_transcurrido = CONCAT(v_horas, ' horas');
    ELSE
        SET v_dias = FLOOR(v_minutos / 1440);
        SET v_tiempo_transcurrido = CONCAT(v_dias, ' días');
    END IF;
    
    RETURN v_tiempo_transcurrido;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `fn_ultima_notificacion` (`p_numero_documento` VARCHAR(20)) RETURNS VARCHAR(255) CHARSET utf8mb4 COLLATE utf8mb4_general_ci READS SQL DATA BEGIN
    DECLARE v_titulo VARCHAR(100);
    DECLARE v_fecha_hora TIMESTAMP;
    
    SELECT titulo, fecha_hora INTO v_titulo, v_fecha_hora
    FROM notificaciones
    WHERE numero_documento = p_numero_documento
    ORDER BY fecha_hora DESC
    LIMIT 1;
    
    IF v_titulo IS NOT NULL THEN
        RETURN CONCAT(v_titulo, ' (', DATE_FORMAT(v_fecha_hora, '%d/%m/%Y %H:%i'), ')');
    ELSE
        RETURN 'No tienes notificaciones';
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `fn_ultimo_movimiento` (`p_numero_documento` VARCHAR(20)) RETURNS VARCHAR(100) CHARSET utf8mb4 COLLATE utf8mb4_general_ci READS SQL DATA BEGIN
    DECLARE v_resultado VARCHAR(100);
    DECLARE v_tipo_movimiento VARCHAR(10);
    DECLARE v_fecha_hora DATETIME;
    
    SELECT tipo_movimiento, fecha_hora INTO v_tipo_movimiento, v_fecha_hora
    FROM registros_acceso
    WHERE numero_documento = p_numero_documento
    ORDER BY fecha_hora DESC
    LIMIT 1;
    
    IF v_tipo_movimiento IS NOT NULL THEN
        SET v_resultado = CONCAT('Último movimiento: ', v_tipo_movimiento, ' el ', DATE_FORMAT(v_fecha_hora, '%d/%m/%Y a las %H:%i'));
    ELSE
        SET v_resultado = 'No se encontraron registros de acceso';
    END IF;
    
    RETURN v_resultado;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `fn_usuario_activo` (`p_numero_documento` VARCHAR(20)) RETURNS TINYINT(1) DETERMINISTIC BEGIN
    DECLARE v_estado VARCHAR(20);
    
    SELECT estado INTO v_estado FROM usuarios WHERE numero_documento = p_numero_documento;
    
    IF v_estado = 'activo' THEN
        RETURN TRUE;
    ELSE
        RETURN FALSE;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `fn_validar_documento` (`p_tipo_documento` ENUM('CC','TI','CE','PA'), `p_numero` VARCHAR(20)) RETURNS TINYINT(1) DETERMINISTIC BEGIN
    -- Validaciones básicas según tipo de documento
    IF p_tipo_documento = 'CC' AND p_numero REGEXP '^[0-9]{8,10}$' THEN -- Cédula colombiana
        RETURN TRUE;
    ELSEIF p_tipo_documento = 'TI' AND p_numero REGEXP '^[0-9]{7,10}$' THEN -- Tarjeta de identidad
        RETURN TRUE;
    ELSEIF p_tipo_documento = 'CE' AND p_numero REGEXP '^[A-Za-z0-9]{6,12}$' THEN -- Cédula extranjería
        RETURN TRUE;
    ELSEIF p_tipo_documento = 'PA' AND p_numero REGEXP '^[A-Za-z]{1,2}[0-9]{4,8}$' THEN -- Pasaporte
        RETURN TRUE;
    ELSE
        RETURN FALSE;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `fn_validar_email` (`p_email` VARCHAR(100)) RETURNS TINYINT(1) DETERMINISTIC BEGIN
    -- Expresión regular simple para validar email
    IF p_email REGEXP '^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,4}$' THEN
        RETURN TRUE;
    ELSE
        RETURN FALSE;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `fn_validar_placa` (`p_placa` VARCHAR(10)) RETURNS TINYINT(1) DETERMINISTIC BEGIN
    -- Formato antiguo: AAA-123 o AAA123
    -- Formato nuevo: ABC-12D o ABC12D
    IF p_placa REGEXP '^[A-Za-z]{3}[ -]?[0-9]{3}$' OR 
       p_placa REGEXP '^[A-Za-z]{3}[ -]?[0-9]{2}[A-Za-z]$' THEN
        RETURN TRUE;
    ELSE
        RETURN FALSE;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `fn_verificar_permiso` (`p_numero_documento` VARCHAR(20), `p_permiso_necesario` INT) RETURNS TINYINT(1) DETERMINISTIC BEGIN
    DECLARE v_id_rol INT;
    
    SELECT id_rol INTO v_id_rol FROM usuarios WHERE numero_documento = p_numero_documento;
    
    -- 1: Estudiante, 2: Docente, 3: Personal Administrativo, 4: Celador, 5: Administrador
    IF p_permiso_necesario = 1 THEN -- Permiso básico (todos los usuarios)
        RETURN TRUE;
    ELSEIF p_permiso_necesario = 2 AND v_id_rol IN (2, 3, 4, 5) THEN -- Docente y superior
        RETURN TRUE;
    ELSEIF p_permiso_necesario = 3 AND v_id_rol IN (3, 4, 5) THEN -- Personal administrativo y superior
        RETURN TRUE;
    ELSEIF p_permiso_necesario = 4 AND v_id_rol IN (4, 5) THEN -- Celador y administrador
        RETURN TRUE;
    ELSEIF p_permiso_necesario = 5 AND v_id_rol = 5 THEN -- Solo administrador
        RETURN TRUE;
    ELSE
        RETURN FALSE;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `fn_visitante_en_campus` (`p_id_visitante` INT) RETURNS TINYINT(1) READS SQL DATA BEGIN
    DECLARE v_ultimo_movimiento VARCHAR(10);
    
    SELECT tipo_movimiento INTO v_ultimo_movimiento
    FROM registros_acceso
    WHERE id_visitante = p_id_visitante
    ORDER BY fecha_hora DESC
    LIMIT 1;
    
    IF v_ultimo_movimiento = 'entrada' THEN
        RETURN TRUE;
    ELSE
        RETURN FALSE;
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aprobaciones_reportes`
--

CREATE TABLE `aprobaciones_reportes` (
  `id_aprobacion` int(11) NOT NULL,
  `id_reporte` int(11) NOT NULL,
  `id_objeto` int(11) DEFAULT NULL,
  `aprobado_por` varchar(20) NOT NULL,
  `estado` enum('pendiente','aprobado','rechazado') NOT NULL DEFAULT 'pendiente',
  `comentarios` text DEFAULT NULL,
  `fecha_aprobacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria`
--

CREATE TABLE `auditoria` (
  `id_auditoria` int(11) NOT NULL,
  `numero_documento` varchar(20) DEFAULT NULL,
  `accion` varchar(100) NOT NULL,
  `tabla_afectada` varchar(50) NOT NULL,
  `id_registro_afectado` int(11) DEFAULT NULL,
  `datos_anteriores` text DEFAULT NULL,
  `datos_nuevos` text DEFAULT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_origen` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `auditoria`
--

INSERT INTO `auditoria` (`id_auditoria`, `numero_documento`, `accion`, `tabla_afectada`, `id_registro_afectado`, `datos_anteriores`, `datos_nuevos`, `fecha_hora`, `ip_origen`) VALUES
(1, '1078458186', 'Creación de nuevo usuario', 'usuarios', NULL, NULL, 'Rol: Estudiante, Nombre: Gabriela Cordoba Gonzalez', '2025-05-16 20:01:41', NULL),
(2, '1078458186', 'Registro de nuevo usuario', 'usuarios', NULL, NULL, 'Rol: Estudiante, Nombre: Gabriela Cordoba Gonzalez', '2025-05-16 20:01:41', NULL),
(3, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-16 20:01:50', NULL),
(4, '1078458186', 'Cambio de rol de usuario', 'usuarios', NULL, 'Rol anterior: Estudiante', 'Nuevo rol: Administrador', '2025-05-16 20:15:12', NULL),
(5, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-16 20:15:35', NULL),
(6, '1078456675', 'Creación de nuevo usuario', 'usuarios', NULL, NULL, 'Rol: Estudiante, Nombre: Andres Martinez Palacios', '2025-05-16 20:23:48', NULL),
(7, '1078456675', 'Registro de nuevo usuario', 'usuarios', NULL, NULL, 'Rol: Estudiante, Nombre: Andres Martinez Palacios', '2025-05-16 20:23:48', NULL),
(8, '1078456675', 'Cambio de rol de usuario', 'usuarios', NULL, 'Rol anterior: Estudiante', 'Nuevo rol: Celador', '2025-05-16 20:24:12', NULL),
(9, '1078456675', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-16 20:24:26', NULL),
(10, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-16 20:30:08', NULL),
(11, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-16 20:34:47', NULL),
(12, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-17 16:44:54', NULL),
(13, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-17 18:25:11', NULL),
(14, '1045667449', 'Creación de nuevo usuario', 'usuarios', NULL, NULL, 'Rol: Estudiante, Nombre: KAROL GIHAN SALINAS GONZALEZ', '2025-05-17 20:29:05', NULL),
(15, '1045667449', 'Registro de nuevo usuario', 'usuarios', NULL, NULL, 'Rol: Estudiante, Nombre: KAROL GIHAN SALINAS GONZALEZ', '2025-05-17 20:29:05', NULL),
(16, '1045667449', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-17 20:30:09', NULL),
(17, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-17 20:31:22', NULL),
(18, '1045667449', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-17 20:39:09', NULL),
(19, '1045667449', 'Reporte de objeto perdido (pendiente)', 'objetos_perdidos', 1, NULL, 'Tipo: Calculadora, Ubicación: Segundo piso, salón 801', '2025-05-17 20:41:35', NULL),
(20, '1045667449', 'Reporte de objeto perdido (pendiente)', 'objetos_perdidos', 2, NULL, 'Tipo: Calculadora, Ubicación: Segundo piso, salón 801', '2025-05-17 20:53:27', NULL),
(21, '1045667449', 'Reporte de objeto perdido (pendiente)', 'objetos_perdidos', 3, NULL, 'Tipo: Calculadora, Ubicación: Segundo piso, salón 801', '2025-05-17 20:53:33', NULL),
(22, '1045667449', 'Reporte de objeto perdido (pendiente)', 'objetos_perdidos', 4, NULL, 'Tipo: Calculadora, Ubicación: Segundo piso, salón 801', '2025-05-17 20:53:47', NULL),
(23, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-17 20:56:53', NULL),
(24, '1738946573', 'Creación de nuevo usuario', 'usuarios', NULL, NULL, 'Rol: Personal Administrativo, Nombre: Mathias Menendez Piedraita', '2025-05-17 21:02:23', NULL),
(25, '1738946573', 'Registro de nuevo usuario', 'usuarios', NULL, NULL, 'Rol: Personal Administrativo, Nombre: Mathias Menendez Piedraita', '2025-05-17 21:02:23', NULL),
(26, '1078456675', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-17 21:03:07', NULL),
(27, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-17 21:03:53', NULL),
(29, '1078458186', 'Reporte de objeto perdido (pendiente)', 'objetos_perdidos', 5, NULL, 'Tipo: Calculadora, Ubicación: Segundo piso, salón 801', '2025-05-17 21:13:51', NULL),
(30, '1078456675', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-17 21:33:43', NULL),
(31, '1738946573', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-17 21:34:35', NULL),
(32, '1738946573', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-17 21:35:17', NULL),
(33, '1738946573', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-17 21:35:32', NULL),
(34, '1045667449', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-17 21:36:13', NULL),
(35, '1045667449', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-17 21:39:30', NULL),
(36, '1078456675', 'Intento de inicio de sesión fallido', 'usuarios', NULL, NULL, NULL, '2025-05-17 21:40:23', NULL),
(37, '1078456675', 'Intento de inicio de sesión fallido', 'usuarios', NULL, NULL, NULL, '2025-05-17 21:40:44', NULL),
(38, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-25 16:46:59', NULL),
(39, '1078458186', 'Actualización de usuario', 'usuarios', 1738946573, NULL, 'Nombre: Mathias Menendez Piedraita, Correo: mathias123@gmail.com, Rol: 3, Estado: activo', '2025-05-25 16:48:12', NULL),
(40, '1078456675', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-25 17:26:51', NULL),
(41, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-26 15:02:42', NULL),
(42, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-28 21:01:01', NULL),
(43, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-29 20:42:49', NULL),
(44, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-29 21:14:02', NULL),
(45, '1078458186', 'Registro de entrada', 'registros_acceso', 1, NULL, 'Persona: Gabriela Cordoba Gonzalez, Movimiento: entrada', '2025-05-30 07:55:51', NULL),
(46, '1078458186', 'Registro de entrada de usuario', 'registros_acceso', 1, NULL, 'Usuario: 1078458186', '2025-05-30 07:55:51', NULL),
(48, '1078458186', 'Cambio de estado de objeto', 'objetos_perdidos', 1, NULL, 'Nuevo estado: perdido', '2025-05-30 08:19:18', NULL),
(49, '1078458186', 'Cambio de estado de objeto', 'objetos_perdidos', 1, NULL, 'Nuevo estado: perdido', '2025-05-30 08:19:25', NULL),
(50, '1078458186', 'Cambio de estado de objeto', 'objetos_perdidos', 1, NULL, 'Nuevo estado: perdido', '2025-05-30 08:19:26', NULL),
(51, '1045667449', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-30 08:22:43', NULL),
(52, '1738946573', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-30 08:24:47', NULL),
(53, '1738946573', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-30 08:24:51', NULL),
(54, '1738946573', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-30 08:25:50', NULL),
(55, '1738946573', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-30 08:26:16', NULL),
(56, '1045667449', 'Intento de inicio de sesión fallido', 'usuarios', NULL, NULL, NULL, '2025-05-30 08:26:56', NULL),
(57, '1045667449', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-30 08:27:12', NULL),
(58, '1045667449', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-30 08:44:23', NULL),
(59, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-30 08:46:39', NULL),
(60, '1045667449', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-30 08:48:24', NULL),
(61, '1078456675', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-30 08:50:35', NULL),
(62, '1045667449', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-30 08:52:37', NULL),
(63, '1078456675', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-30 09:20:00', NULL),
(64, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-30 09:21:42', NULL),
(65, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-30 09:27:26', NULL),
(66, '1078458186', 'Intento de inicio de sesión fallido', 'usuarios', NULL, NULL, NULL, '2025-05-30 10:43:06', NULL),
(67, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-30 10:43:18', NULL),
(68, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-30 12:50:01', NULL),
(69, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-30 13:02:30', NULL),
(70, '1078458186', 'Actualización de usuario', 'usuarios', 1738946573, NULL, 'Nombre: Mathias Luis Menendez Piedraita, Correo: mathias123@gmail.com, Rol: 3, Estado: activo', '2025-05-30 13:03:02', NULL),
(71, '1078458186', 'Intento de inicio de sesión fallido', 'usuarios', NULL, NULL, NULL, '2025-05-30 13:08:52', NULL),
(72, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-30 13:09:01', NULL),
(73, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-30 13:23:49', NULL),
(74, '1078458186', 'Actualización de usuario', 'usuarios', 1738946573, NULL, 'Nombre: Mathias Carlos Menendez Piedraita, Correo: mathias123@gmail.com, Rol: 3, Estado: activo', '2025-05-30 13:24:41', NULL),
(75, '1078458186', 'Registro de entrada', 'registros_acceso', 2, NULL, 'Persona: KAROL GIHAN SALINAS GONZALEZ, Movimiento: entrada', '2025-05-30 13:26:20', NULL),
(76, '1078458186', 'Registro de entrada de usuario', 'registros_acceso', 2, NULL, 'Usuario: 1045667449', '2025-05-30 13:26:20', NULL),
(77, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-31 04:13:35', NULL),
(78, '1078458186', 'Intento de inicio de sesión fallido', 'usuarios', NULL, NULL, NULL, '2025-05-31 19:49:20', NULL),
(79, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-05-31 19:49:49', NULL),
(80, '1078458186', 'Eliminar tipo de incidente', 'tipos_incidente', NULL, 'Tipo eliminado: Otro', NULL, '2025-05-31 19:58:42', NULL),
(81, '1078458186', 'Eliminar tipo de incidente', 'tipos_incidente', NULL, 'Tipo eliminado: ', NULL, '2025-05-31 19:58:48', NULL),
(82, '1078458186', 'Agregar tipo de incidente', 'tipos_incidente', NULL, NULL, 'Nombre: Otro, Severidad: alta', '2025-05-31 19:59:43', NULL),
(83, '1078458186', 'Agregar tipo de incidente', 'tipos_incidente', NULL, NULL, 'Nombre: Otro, Severidad: alta', '2025-05-31 19:59:47', NULL),
(84, '1078458186', 'Eliminar tipo de incidente', 'tipos_incidente', NULL, 'Tipo eliminado: Otro', NULL, '2025-05-31 19:59:57', NULL),
(85, '1078458186', 'Eliminar tipo de incidente', 'tipos_incidente', NULL, 'Tipo eliminado: ', NULL, '2025-05-31 20:00:00', NULL),
(86, '1078458186', 'Actualizar configuración del sistema', 'sistema', NULL, NULL, NULL, '2025-05-31 20:00:29', NULL),
(87, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 13:39:14', NULL),
(88, '1078458186', 'Reporte de objeto perdido (pendiente)', 'objetos_perdidos', 6, NULL, 'Tipo: Calculadora, Ubicación: calle', '2025-06-02 15:40:14', NULL),
(89, '1078458186', 'Cambio de estado de objeto', 'objetos_perdidos', 6, NULL, 'Nuevo estado: encontrado', '2025-06-02 15:40:45', NULL),
(90, '1045667449', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 16:11:53', NULL),
(91, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 16:12:49', NULL),
(92, '1078458187', 'Creación de nuevo usuario', 'usuarios', NULL, NULL, 'Rol: Docente, Nombre: Marlen Mena Mena', '2025-06-02 16:15:40', NULL),
(93, '1078458187', 'Registro de nuevo usuario', 'usuarios', NULL, NULL, 'Rol: Docente, Nombre: Marlen Mena Mena', '2025-06-02 16:15:40', NULL),
(94, '1078458187', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 16:16:00', NULL),
(95, '1078458187', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 16:16:03', NULL),
(96, '1078456675', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 16:17:38', NULL),
(97, '1045667449', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 16:20:35', NULL),
(98, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 16:28:10', NULL),
(99, '1045667449', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 16:32:08', NULL),
(100, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 16:34:36', NULL),
(101, '1078458186', 'Cambio de estado de objeto', 'objetos_perdidos', 5, NULL, 'Nuevo estado: perdido', '2025-06-02 16:35:12', NULL),
(102, '1045667449', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 16:35:31', NULL),
(103, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 16:35:59', NULL),
(104, '1078458140', 'Creación de nuevo usuario', 'usuarios', NULL, NULL, 'Rol: Estudiante, Nombre: Mercy Perea Gutierrez', '2025-06-02 16:37:32', NULL),
(105, '1078458140', 'Registro de nuevo usuario', 'usuarios', NULL, NULL, 'Rol: Estudiante, Nombre: Mercy Perea Gutierrez', '2025-06-02 16:37:32', NULL),
(106, '1078458140', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 16:37:41', NULL),
(107, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 16:40:52', NULL),
(108, '1078458187', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 16:48:03', NULL),
(109, '1078458187', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 16:49:26', NULL),
(110, '1078458187', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 17:36:55', NULL),
(111, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 17:43:52', NULL),
(112, '1078458140', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 17:56:27', NULL),
(113, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 17:59:13', NULL),
(114, '1045667449', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 18:01:15', NULL),
(115, '1078458140', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 18:02:24', NULL),
(116, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 18:03:55', NULL),
(117, '1078458140', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 18:04:36', NULL),
(118, '1078458140', 'Reporte de objeto perdido (pendiente)', 'objetos_perdidos', 7, NULL, 'Tipo: Cecular, Ubicación: Sala 1', '2025-06-02 18:05:26', NULL),
(119, '1078458140', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 18:18:10', NULL),
(120, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 18:20:26', NULL),
(121, '1078458140', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 19:11:15', NULL),
(122, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 20:29:07', NULL),
(123, '1078458140', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 20:29:56', NULL),
(124, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 20:32:53', NULL),
(125, '1078458140', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 23:06:55', NULL),
(126, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-02 23:30:56', NULL),
(127, '1078458745', 'Creación de nuevo usuario', 'usuarios', NULL, NULL, 'Rol: Personal Administrativo, Nombre: Melissa Ocampo Aguirre', '2025-06-03 02:17:54', NULL),
(128, '1078458745', 'Registro de nuevo usuario', 'usuarios', NULL, NULL, 'Rol: Personal Administrativo, Nombre: Melissa Ocampo Aguirre', '2025-06-03 02:17:54', NULL),
(129, '1078458745', 'Cambio de estado de usuario', 'usuarios', NULL, 'Estado anterior: activo', 'Nuevo estado: inactivo', '2025-06-03 02:29:55', NULL),
(130, '1078458186', 'Actualización de usuario', 'usuarios', 1078458745, NULL, 'Nombre: Melissa Ocampo Aguirre, Correo: melissa@gmail.com, Rol: 3, Estado: inactivo', '2025-06-03 02:29:55', NULL),
(131, '1078458745', 'Cambio de estado de usuario', 'usuarios', NULL, 'Estado anterior: inactivo', 'Nuevo estado: activo', '2025-06-03 02:30:08', NULL),
(132, '1078458186', 'Actualización de usuario', 'usuarios', 1078458745, NULL, 'Nombre: Melissa Ocampo Aguirre, Correo: melissa@gmail.com, Rol: 3, Estado: activo', '2025-06-03 02:30:08', NULL),
(133, '1078458186', 'Cambio de estado de objeto', 'objetos_perdidos', 6, NULL, 'Nuevo estado: devuelto', '2025-06-03 02:33:13', NULL),
(134, '1078458186', 'Cambio de estado de objeto', 'objetos_perdidos', 7, NULL, 'Nuevo estado: encontrado', '2025-06-03 02:33:33', NULL),
(135, '1078458186', 'Cambio de estado de objeto', 'objetos_perdidos', 7, NULL, 'Nuevo estado: encontrado', '2025-06-03 02:33:35', NULL),
(137, '1078458186', 'Eliminación de usuario', 'usuarios', NULL, NULL, 'Usuario eliminado: CC 1078458745', '2025-06-03 04:20:12', NULL),
(138, '1078458186', 'Desactivación de usuario', 'usuarios', 0, NULL, 'Usuario desactivado', '2025-06-03 04:27:23', NULL),
(139, '1078458186', 'Eliminación de usuario', 'usuarios', 0, NULL, 'Usuario eliminado', '2025-06-03 04:27:28', NULL),
(140, '1078458186', 'Desactivación de usuario', 'usuarios', 0, NULL, 'Usuario desactivado', '2025-06-03 04:31:52', NULL),
(141, '1078458186', 'Bloqueo de usuario', 'usuarios', 0, NULL, 'Usuario bloqueado', '2025-06-03 04:31:58', NULL),
(142, '1078458186', 'Eliminación de usuario', 'usuarios', 0, NULL, 'Usuario eliminado', '2025-06-03 04:32:05', NULL),
(143, '1078458140', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 04:59:15', NULL),
(144, '1078458187', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 05:00:34', NULL),
(145, '1078458187', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 05:00:52', NULL),
(146, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 05:01:15', NULL),
(147, '1045667449', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 05:01:44', NULL),
(148, '1078456675', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 05:02:09', NULL),
(149, '1738946573', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 05:02:40', NULL),
(150, '1078458140', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 05:05:01', NULL),
(151, '1078458140', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 05:05:16', NULL),
(152, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 05:05:22', NULL),
(153, '1078458140', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 05:28:36', NULL),
(154, '1078458140', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 05:42:02', NULL),
(155, '1045667449', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 05:42:18', NULL),
(156, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 06:26:23', NULL),
(158, '1078458186', 'Eliminación de usuario', 'usuarios', 0, NULL, 'Usuario eliminado', '2025-06-03 06:40:07', NULL),
(161, '1078458745', 'Cambio de estado de usuario', 'usuarios', NULL, 'Estado anterior: activo', 'Nuevo estado: inactivo', '2025-06-03 06:48:42', NULL),
(162, '1078458186', 'Acción de usuario: desactivar', 'usuarios', NULL, NULL, 'Documento afectado: 1078458745', '2025-06-03 06:48:42', NULL),
(163, '1078458745', 'Cambio de estado de usuario', 'usuarios', NULL, 'Estado anterior: inactivo', 'Nuevo estado: bloqueado', '2025-06-03 06:49:28', NULL),
(164, '1078458186', 'Acción de usuario: bloquear', 'usuarios', NULL, NULL, 'Documento afectado: 1078458745', '2025-06-03 06:49:28', NULL),
(165, '1078458745', 'Cambio de estado de usuario', 'usuarios', NULL, 'Estado anterior: bloqueado', 'Nuevo estado: inactivo', '2025-06-03 06:49:46', NULL),
(166, '1078458186', 'Acción de usuario: desactivar', 'usuarios', NULL, NULL, 'Documento afectado: 1078458745', '2025-06-03 06:49:46', NULL),
(167, '1078458745', 'Cambio de estado de usuario', 'usuarios', NULL, 'Estado anterior: inactivo', 'Nuevo estado: activo', '2025-06-03 06:49:51', NULL),
(168, '1078458186', 'Acción de usuario: activar', 'usuarios', NULL, NULL, 'Documento afectado: 1078458745', '2025-06-03 06:49:51', NULL),
(170, '1078458140', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 07:12:23', NULL),
(171, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 07:13:14', NULL),
(172, '1078458140', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 07:21:02', NULL),
(173, '1078456675', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 07:22:20', NULL),
(178, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 07:51:19', NULL),
(179, '1078456675', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 07:54:21', NULL),
(181, '1078456675', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 08:00:20', NULL),
(183, '1826354673', 'Registro de nuevo visitante', 'visitantes', 11, NULL, 'Nombre: Potter Wisly, Motivo: Proceso matricula', '2025-06-03 08:03:36', NULL),
(184, '1078456675', 'Registro de entrada de visitante', 'registros_acceso', 3, NULL, 'Persona: Potter Wisly, Movimiento: entrada', '2025-06-03 08:03:36', NULL),
(185, '1738946573', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 08:29:15', NULL),
(186, '1738946573', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 08:29:19', NULL),
(187, '1738946573', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 08:29:40', NULL),
(188, '1078458745', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 08:31:17', NULL),
(189, '1078458745', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 08:38:18', NULL),
(190, '1078458745', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 08:39:36', NULL),
(191, '1078456675', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 08:46:29', NULL),
(192, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 08:52:34', NULL),
(193, '1078458186', 'Cambio de estado de objeto', 'objetos_perdidos', 7, NULL, 'Nuevo estado: encontrado', '2025-06-03 09:19:22', NULL),
(194, '1078458186', 'Cambio de estado de objeto', 'objetos_perdidos', 7, NULL, 'Nuevo estado: encontrado', '2025-06-03 09:19:23', NULL),
(195, '1078458140', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 13:22:12', NULL),
(196, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 13:23:00', NULL),
(197, '1078458140', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 16:20:54', NULL),
(198, '1078458140', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 16:25:08', NULL),
(199, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 16:40:11', NULL),
(200, '1078458140', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 16:40:26', NULL),
(201, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 16:47:01', NULL),
(202, '1078456675', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 16:53:25', NULL),
(203, '1078458140', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 16:55:30', NULL),
(204, '1078456675', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 16:56:48', NULL),
(205, '1078456675', 'Marcar objeto como devuelto', 'objetos_perdidos', 7, NULL, 'Estado: devuelto', '2025-06-03 16:57:54', NULL),
(206, '1078458140', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 17:07:12', NULL),
(207, '1078458140', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 17:28:36', NULL),
(208, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 17:50:59', NULL),
(209, '1078456675', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 17:51:38', NULL),
(210, '1078456675', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 18:31:35', NULL),
(211, '1045667449', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 18:55:24', NULL),
(212, '1078456675', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 19:02:01', NULL),
(213, '1045667449', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 19:02:15', NULL),
(214, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 19:07:57', NULL),
(215, '1078458186', 'Cambio de estado de objeto', 'objetos_perdidos', 26, NULL, 'Nuevo estado: perdido', '2025-06-03 19:08:44', NULL),
(216, '1045667449', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 19:09:06', NULL),
(217, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 19:10:13', NULL),
(218, '1078456675', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 19:13:15', NULL),
(219, '1045667449', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 19:23:51', NULL),
(220, '1045667449', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 19:24:25', NULL),
(221, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 19:26:54', NULL),
(222, '1045667449', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 19:31:25', NULL),
(223, '1078456675', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 19:31:40', NULL),
(224, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 19:32:48', NULL),
(225, '1078456675', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 19:33:22', NULL),
(226, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 19:36:14', NULL),
(227, '1045667449', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 19:42:21', NULL),
(228, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 19:46:26', NULL),
(229, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 19:48:07', NULL),
(230, '1045667449', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 19:49:30', NULL),
(231, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 19:51:12', NULL),
(232, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 19:53:42', NULL),
(233, '1045667449', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 19:58:06', NULL),
(234, '1045667449', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 20:04:50', NULL),
(235, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 20:06:24', NULL),
(236, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 20:07:42', NULL),
(237, '1045667449', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 20:08:07', NULL),
(238, '1078458140', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-06-03 20:09:38', NULL),
(239, '1045667449', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-09-11 21:21:25', NULL),
(240, '1078456675', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-09-12 16:36:23', NULL),
(241, '1078456675', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-09-12 19:21:28', NULL),
(242, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-09-15 01:07:13', NULL),
(243, '1045667449', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-09-15 01:07:21', NULL),
(244, '1078458186', 'Inicio de sesión exitoso', 'usuarios', NULL, NULL, NULL, '2025-09-15 01:08:25', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentarios_objetos`
--

CREATE TABLE `comentarios_objetos` (
  `id_comentario` int(11) NOT NULL,
  `id_objeto` int(11) NOT NULL,
  `numero_documento` varchar(20) NOT NULL,
  `comentario` text NOT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evidencias_incidente`
--

CREATE TABLE `evidencias_incidente` (
  `id_evidencia` int(11) NOT NULL,
  `id_reporte` int(11) NOT NULL,
  `tipo_archivo` varchar(50) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `subido_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `evidencias_incidente`
--

INSERT INTO `evidencias_incidente` (`id_evidencia`, `id_reporte`, `tipo_archivo`, `nombre_archivo`, `ruta_archivo`, `subido_en`) VALUES
(1, 2, 'jpg', 'objeto-transicional.jpg', 'uploads/evidencias/683e9f4939e7f_objeto_transicional.jpg', '2025-06-03 07:07:53'),
(2, 4, 'jpg', '683e9f4939e7f_objeto_transicional.jpg', 'uploads/evidencias/683f4c63d5815_683e9f4939e7f_objeto_transicional.jpg', '2025-06-03 19:26:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imagenes_objeto`
--

CREATE TABLE `imagenes_objeto` (
  `id_imagen` int(11) NOT NULL,
  `id_objeto` int(11) NOT NULL,
  `tipo_archivo` varchar(50) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `subido_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `imagenes_objeto`
--

INSERT INTO `imagenes_objeto` (`id_imagen`, `id_objeto`, `tipo_archivo`, `nombre_archivo`, `ruta_archivo`, `subido_en`) VALUES
(1, 23, 'image/jpeg', '683e9f4939e7f_objeto_transicional.jpg', 'uploads/objetos/683f26c3219cf.jpg', '2025-06-03 16:45:55'),
(2, 27, 'image/jpeg', '683e9f4939e7f_objeto_transicional.jpg', 'uploads/objetos/683f475026c4e.jpg', '2025-06-03 19:04:48'),
(3, 28, 'image/jpeg', 'sombrilla.jpg', 'uploads/objetos/683f48741e5ff.jpg', '2025-06-03 19:09:40'),
(4, 31, 'image/jpeg', '683e9f4939e7f_objeto_transicional.jpg', 'uploads/objetos/683f558a60f1a.jpg', '2025-06-03 20:05:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes_objetos`
--

CREATE TABLE `mensajes_objetos` (
  `id_mensaje` int(11) NOT NULL,
  `id_objeto` int(11) NOT NULL,
  `numero_documento` varchar(20) NOT NULL,
  `mensaje` text NOT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mensajes_objetos`
--

INSERT INTO `mensajes_objetos` (`id_mensaje`, `id_objeto`, `numero_documento`, `mensaje`, `fecha_hora`) VALUES
(1, 5, '1078458186', 'Creo haber visto una parecida en el primer piso', '2025-06-02 15:39:20'),
(2, 5, '1078458140', 'El usuario   está interesado en reclamar este objeto. Por favor contacta con él para verificar la propiedad.', '2025-06-02 17:57:55'),
(3, 5, '1078458140', 'Detras esta llena de figuritas ', '2025-06-02 17:59:00'),
(4, 5, '1045667449', 'El usuario   está interesado en reclamar este objeto. Por favor contacta con él para verificar la propiedad.', '2025-06-03 20:08:28'),
(5, 5, '1045667449', 'es mio', '2025-06-03 20:08:41');

--
-- Disparadores `mensajes_objetos`
--
DELIMITER $$
CREATE TRIGGER `tr_mensajes_objetos_after_insert` AFTER INSERT ON `mensajes_objetos` FOR EACH ROW BEGIN
    DECLARE v_reportante VARCHAR(20);
    
    -- Obtener quien reportó el objeto
    SELECT numero_documento INTO v_reportante FROM objetos_perdidos WHERE id_objeto = NEW.id_objeto;
    
    -- Notificar al reportante sobre el nuevo mensaje (si no es el mismo)
    IF v_reportante != NEW.numero_documento THEN
        INSERT INTO notificaciones (numero_documento, titulo, mensaje, tipo, id_referencia)
        VALUES (v_reportante, 
               CONCAT('Nuevo mensaje sobre objeto (#', NEW.id_objeto, ')'), 
               CONCAT('Tienes un nuevo mensaje sobre tu objeto reportado: ', LEFT(NEW.mensaje, 50), '...'), 
               'objeto', 
               NEW.id_objeto);
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL,
  `numero_documento` varchar(20) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `mensaje` text NOT NULL,
  `tipo` enum('incidente','objeto','seguridad','sistema') NOT NULL,
  `id_referencia` int(11) DEFAULT NULL,
  `leida` tinyint(1) DEFAULT 0,
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id_notificacion`, `numero_documento`, `titulo`, `mensaje`, `tipo`, `id_referencia`, `leida`, `fecha_hora`) VALUES
(19, '123456789', 'Nuevo objeto perdido reportado (#5)', 'Tipo: Calculadora, Ubicación: Segundo piso, salón 801', 'objeto', 5, 0, '2025-05-17 21:13:51'),
(20, '1078458186', 'Objeto perdido reportado (#5)', 'Tu reporte de objeto perdido ha sido registrado', 'objeto', 5, 0, '2025-05-17 21:13:51'),
(21, '1045667449', 'Estado de objeto actualizado (#1)', 'El estado de tu objeto reportado ha cambiado a: perdido', 'objeto', 1, 0, '2025-05-30 08:19:18'),
(22, '1078456675', 'Nuevo incidente reportado (#1)', 'Tipo: Accidente, Ubicación: calle', 'incidente', 1, 0, '2025-05-30 13:03:43'),
(23, '1078458186', 'Nuevo incidente reportado (#1)', 'Tipo: Accidente, Ubicación: calle', 'incidente', 1, 0, '2025-05-30 13:03:43'),
(24, '123456789', 'Nuevo incidente reportado (#1)', 'Tipo: Accidente, Ubicación: calle', 'incidente', 1, 0, '2025-05-30 13:03:43'),
(25, '1078458186', 'Incidente reportado (#1)', 'Tu reporte de incidente ha sido registrado y está siendo revisado', 'incidente', 1, 0, '2025-05-30 13:03:43'),
(26, '1078456675', 'Nuevo objeto perdido reportado (#6)', 'Tipo: Calculadora, Ubicación: calle', 'objeto', 6, 0, '2025-06-02 15:40:14'),
(27, '1078458186', 'Nuevo objeto perdido reportado (#6)', 'Tipo: Calculadora, Ubicación: calle', 'objeto', 6, 0, '2025-06-02 15:40:14'),
(28, '123456789', 'Nuevo objeto perdido reportado (#6)', 'Tipo: Calculadora, Ubicación: calle', 'objeto', 6, 0, '2025-06-02 15:40:14'),
(29, '1078458186', 'Objeto perdido reportado (#6)', 'Tu reporte de objeto perdido ha sido registrado', 'objeto', 6, 0, '2025-06-02 15:40:14'),
(30, '1078458186', 'Estado de objeto actualizado (#6)', 'El estado de tu objeto reportado ha cambiado a: encontrado', 'objeto', 6, 0, '2025-06-02 15:40:45'),
(31, '1078458186', 'Estado de objeto actualizado (#5)', 'El estado de tu objeto reportado ha cambiado a: perdido', 'objeto', 5, 0, '2025-06-02 16:35:12'),
(32, '1078458186', 'Nuevo mensaje sobre objeto (#5)', 'Tienes un nuevo mensaje sobre tu objeto reportado: El usuario   está interesado en reclamar este obje...', 'objeto', 5, 0, '2025-06-02 17:57:55'),
(33, '1078458186', 'Reclamación de objeto', 'El usuario   está interesado en reclamar este objeto. Por favor contacta con él para verificar la propiedad.', 'objeto', 5, 0, '2025-06-02 17:57:55'),
(34, '1078458186', 'Nuevo mensaje sobre objeto (#5)', 'Tienes un nuevo mensaje sobre tu objeto reportado: Detras esta llena de figuritas ...', 'objeto', 5, 0, '2025-06-02 17:59:00'),
(35, '1078456675', 'Nuevo objeto perdido reportado (#7)', 'Tipo: Cecular, Ubicación: Sala 1', 'objeto', 7, 0, '2025-06-02 18:05:26'),
(36, '1078458186', 'Nuevo objeto perdido reportado (#7)', 'Tipo: Cecular, Ubicación: Sala 1', 'objeto', 7, 0, '2025-06-02 18:05:26'),
(37, '123456789', 'Nuevo objeto perdido reportado (#7)', 'Tipo: Cecular, Ubicación: Sala 1', 'objeto', 7, 0, '2025-06-02 18:05:26'),
(38, '1078458140', 'Objeto perdido reportado (#7)', 'Tu reporte de objeto perdido ha sido registrado', 'objeto', 7, 0, '2025-06-02 18:05:26'),
(83, '1078458186', 'Estado de objeto actualizado (#6)', 'El estado de tu objeto reportado ha cambiado a: devuelto', 'objeto', 6, 1, '2025-06-03 04:25:35'),
(84, '1078458140', 'Estado de objeto actualizado (#7)', 'El estado de tu objeto reportado ha cambiado a: encontrado', 'objeto', 7, 0, '2025-06-03 02:33:33'),
(117, '1045667449', 'Nuevo incidente reportado (#2)', 'Tipo: Comportamiento inusual, Ubicación: calle', 'incidente', 2, 0, '2025-06-03 07:07:53'),
(118, '1078456675', 'Nuevo incidente reportado (#2)', 'Tipo: Comportamiento inusual, Ubicación: calle', 'incidente', 2, 0, '2025-06-03 07:07:53'),
(119, '1078458140', 'Nuevo incidente reportado (#2)', 'Tipo: Comportamiento inusual, Ubicación: calle', 'incidente', 2, 0, '2025-06-03 07:07:53'),
(120, '1078458187', 'Nuevo incidente reportado (#2)', 'Tipo: Comportamiento inusual, Ubicación: calle', 'incidente', 2, 0, '2025-06-03 07:07:53'),
(121, '1078458745', 'Nuevo incidente reportado (#2)', 'Tipo: Comportamiento inusual, Ubicación: calle', 'incidente', 2, 0, '2025-06-03 07:07:53'),
(122, '123456789', 'Nuevo incidente reportado (#2)', 'Tipo: Comportamiento inusual, Ubicación: calle', 'incidente', 2, 0, '2025-06-03 07:07:53'),
(123, '1738946573', 'Nuevo incidente reportado (#2)', 'Tipo: Comportamiento inusual, Ubicación: calle', 'incidente', 2, 0, '2025-06-03 07:07:53'),
(124, '1078458186', 'Incidente reportado (#2)', 'Tu reporte ha sido registrado y está siendo revisado', 'incidente', 2, 1, '2025-06-03 09:19:02'),
(133, '1045667449', 'Nuevo objeto perdida reportado (#21)', 'Tipo: Chaqueta, Ubicación: ', 'objeto', 21, 0, '2025-06-03 09:38:48'),
(134, '1078456675', 'Nuevo objeto perdida reportado (#21)', 'Tipo: Chaqueta, Ubicación: ', 'objeto', 21, 0, '2025-06-03 09:38:48'),
(135, '1078458140', 'Nuevo objeto perdida reportado (#21)', 'Tipo: Chaqueta, Ubicación: ', 'objeto', 21, 0, '2025-06-03 09:38:48'),
(136, '1078458187', 'Nuevo objeto perdida reportado (#21)', 'Tipo: Chaqueta, Ubicación: ', 'objeto', 21, 0, '2025-06-03 09:38:48'),
(137, '1078458745', 'Nuevo objeto perdida reportado (#21)', 'Tipo: Chaqueta, Ubicación: ', 'objeto', 21, 0, '2025-06-03 09:38:48'),
(138, '123456789', 'Nuevo objeto perdida reportado (#21)', 'Tipo: Chaqueta, Ubicación: ', 'objeto', 21, 0, '2025-06-03 09:38:48'),
(139, '1738946573', 'Nuevo objeto perdida reportado (#21)', 'Tipo: Chaqueta, Ubicación: ', 'objeto', 21, 0, '2025-06-03 09:38:48'),
(140, '1078458186', 'Objeto perdida reportado (#21)', 'Tu reporte ha sido registrado y está siendo revisado', 'objeto', 21, 0, '2025-06-03 09:38:48'),
(141, '1045667449', 'Nuevo objeto perdida reportado (#22)', 'Tipo: camisa, Ubicación: ', 'objeto', 22, 0, '2025-06-03 09:45:27'),
(142, '1078456675', 'Nuevo objeto perdida reportado (#22)', 'Tipo: camisa, Ubicación: ', 'objeto', 22, 0, '2025-06-03 09:45:27'),
(143, '1078458140', 'Nuevo objeto perdida reportado (#22)', 'Tipo: camisa, Ubicación: ', 'objeto', 22, 0, '2025-06-03 09:45:27'),
(144, '1078458187', 'Nuevo objeto perdida reportado (#22)', 'Tipo: camisa, Ubicación: ', 'objeto', 22, 0, '2025-06-03 09:45:27'),
(145, '1078458745', 'Nuevo objeto perdida reportado (#22)', 'Tipo: camisa, Ubicación: ', 'objeto', 22, 0, '2025-06-03 09:45:27'),
(146, '123456789', 'Nuevo objeto perdida reportado (#22)', 'Tipo: camisa, Ubicación: ', 'objeto', 22, 0, '2025-06-03 09:45:27'),
(147, '1738946573', 'Nuevo objeto perdida reportado (#22)', 'Tipo: camisa, Ubicación: ', 'objeto', 22, 0, '2025-06-03 09:45:27'),
(148, '1078458186', 'Objeto perdida reportado (#22)', 'Tu reporte ha sido registrado y está siendo revisado', 'objeto', 22, 1, '2025-06-03 14:19:32'),
(149, '1045667449', 'Nuevo objeto perdida reportado (#23)', 'Tipo: Peluche, Ubicación: salon', 'objeto', 23, 0, '2025-06-03 16:45:55'),
(150, '1078456675', 'Nuevo objeto perdida reportado (#23)', 'Tipo: Peluche, Ubicación: salon', 'objeto', 23, 0, '2025-06-03 16:45:55'),
(151, '1078458186', 'Nuevo objeto perdida reportado (#23)', 'Tipo: Peluche, Ubicación: salon', 'objeto', 23, 0, '2025-06-03 16:45:55'),
(152, '1078458187', 'Nuevo objeto perdida reportado (#23)', 'Tipo: Peluche, Ubicación: salon', 'objeto', 23, 0, '2025-06-03 16:45:55'),
(153, '1078458745', 'Nuevo objeto perdida reportado (#23)', 'Tipo: Peluche, Ubicación: salon', 'objeto', 23, 0, '2025-06-03 16:45:55'),
(154, '123456789', 'Nuevo objeto perdida reportado (#23)', 'Tipo: Peluche, Ubicación: salon', 'objeto', 23, 0, '2025-06-03 16:45:55'),
(155, '1738946573', 'Nuevo objeto perdida reportado (#23)', 'Tipo: Peluche, Ubicación: salon', 'objeto', 23, 0, '2025-06-03 16:45:55'),
(156, '1078458140', 'Objeto perdida reportado (#23)', 'Tu reporte ha sido registrado y está siendo revisado', 'objeto', 23, 0, '2025-06-03 16:45:55'),
(157, '1078458140', 'Estado de objeto actualizado (#7)', 'El estado de tu objeto reportado ha cambiado a: devuelto', 'objeto', 7, 0, '2025-06-03 16:57:54'),
(158, '1078456675', 'Nuevo objeto hallazgo reportado (#24)', 'Tipo: Lapicero, Ubicación: Auditorio ', 'objeto', 24, 0, '2025-06-03 19:01:00'),
(159, '1078458140', 'Nuevo objeto hallazgo reportado (#24)', 'Tipo: Lapicero, Ubicación: Auditorio ', 'objeto', 24, 0, '2025-06-03 19:01:00'),
(160, '1078458186', 'Nuevo objeto hallazgo reportado (#24)', 'Tipo: Lapicero, Ubicación: Auditorio ', 'objeto', 24, 0, '2025-06-03 19:01:00'),
(161, '1078458187', 'Nuevo objeto hallazgo reportado (#24)', 'Tipo: Lapicero, Ubicación: Auditorio ', 'objeto', 24, 0, '2025-06-03 19:01:00'),
(162, '1078458745', 'Nuevo objeto hallazgo reportado (#24)', 'Tipo: Lapicero, Ubicación: Auditorio ', 'objeto', 24, 0, '2025-06-03 19:01:00'),
(163, '123456789', 'Nuevo objeto hallazgo reportado (#24)', 'Tipo: Lapicero, Ubicación: Auditorio ', 'objeto', 24, 0, '2025-06-03 19:01:00'),
(164, '1738946573', 'Nuevo objeto hallazgo reportado (#24)', 'Tipo: Lapicero, Ubicación: Auditorio ', 'objeto', 24, 0, '2025-06-03 19:01:00'),
(165, '1045667449', 'Objeto hallazgo reportado (#24)', 'Tu reporte ha sido registrado y está siendo revisado', 'objeto', 24, 0, '2025-06-03 19:01:00'),
(166, '1078456675', 'Nuevo objeto hallazgo reportado (#25)', 'Tipo: Lapicero, Ubicación: Auditorio ', 'objeto', 25, 0, '2025-06-03 19:01:05'),
(167, '1078458140', 'Nuevo objeto hallazgo reportado (#25)', 'Tipo: Lapicero, Ubicación: Auditorio ', 'objeto', 25, 0, '2025-06-03 19:01:05'),
(168, '1078458186', 'Nuevo objeto hallazgo reportado (#25)', 'Tipo: Lapicero, Ubicación: Auditorio ', 'objeto', 25, 0, '2025-06-03 19:01:05'),
(169, '1078458187', 'Nuevo objeto hallazgo reportado (#25)', 'Tipo: Lapicero, Ubicación: Auditorio ', 'objeto', 25, 0, '2025-06-03 19:01:05'),
(170, '1078458745', 'Nuevo objeto hallazgo reportado (#25)', 'Tipo: Lapicero, Ubicación: Auditorio ', 'objeto', 25, 0, '2025-06-03 19:01:05'),
(171, '123456789', 'Nuevo objeto hallazgo reportado (#25)', 'Tipo: Lapicero, Ubicación: Auditorio ', 'objeto', 25, 0, '2025-06-03 19:01:05'),
(172, '1738946573', 'Nuevo objeto hallazgo reportado (#25)', 'Tipo: Lapicero, Ubicación: Auditorio ', 'objeto', 25, 0, '2025-06-03 19:01:05'),
(173, '1045667449', 'Objeto hallazgo reportado (#25)', 'Tu reporte ha sido registrado y está siendo revisado', 'objeto', 25, 0, '2025-06-03 19:01:05'),
(174, '1078456675', 'Nuevo objeto perdida reportado (#26)', 'Tipo: lapicero, Ubicación: salon', 'objeto', 26, 0, '2025-06-03 19:04:00'),
(175, '1078458140', 'Nuevo objeto perdida reportado (#26)', 'Tipo: lapicero, Ubicación: salon', 'objeto', 26, 0, '2025-06-03 19:04:00'),
(176, '1078458186', 'Nuevo objeto perdida reportado (#26)', 'Tipo: lapicero, Ubicación: salon', 'objeto', 26, 0, '2025-06-03 19:04:00'),
(177, '1078458187', 'Nuevo objeto perdida reportado (#26)', 'Tipo: lapicero, Ubicación: salon', 'objeto', 26, 0, '2025-06-03 19:04:00'),
(178, '1078458745', 'Nuevo objeto perdida reportado (#26)', 'Tipo: lapicero, Ubicación: salon', 'objeto', 26, 0, '2025-06-03 19:04:00'),
(179, '123456789', 'Nuevo objeto perdida reportado (#26)', 'Tipo: lapicero, Ubicación: salon', 'objeto', 26, 0, '2025-06-03 19:04:00'),
(180, '1738946573', 'Nuevo objeto perdida reportado (#26)', 'Tipo: lapicero, Ubicación: salon', 'objeto', 26, 0, '2025-06-03 19:04:00'),
(181, '1045667449', 'Objeto perdida reportado (#26)', 'Tu reporte ha sido registrado y está siendo revisado', 'objeto', 26, 0, '2025-06-03 19:04:00'),
(182, '1078456675', 'Nuevo objeto perdida reportado (#27)', 'Tipo: casa, Ubicación: casa', 'objeto', 27, 0, '2025-06-03 19:04:48'),
(183, '1078458140', 'Nuevo objeto perdida reportado (#27)', 'Tipo: casa, Ubicación: casa', 'objeto', 27, 0, '2025-06-03 19:04:48'),
(184, '1078458186', 'Nuevo objeto perdida reportado (#27)', 'Tipo: casa, Ubicación: casa', 'objeto', 27, 0, '2025-06-03 19:04:48'),
(185, '1078458187', 'Nuevo objeto perdida reportado (#27)', 'Tipo: casa, Ubicación: casa', 'objeto', 27, 0, '2025-06-03 19:04:48'),
(186, '1078458745', 'Nuevo objeto perdida reportado (#27)', 'Tipo: casa, Ubicación: casa', 'objeto', 27, 0, '2025-06-03 19:04:48'),
(187, '123456789', 'Nuevo objeto perdida reportado (#27)', 'Tipo: casa, Ubicación: casa', 'objeto', 27, 0, '2025-06-03 19:04:48'),
(188, '1738946573', 'Nuevo objeto perdida reportado (#27)', 'Tipo: casa, Ubicación: casa', 'objeto', 27, 0, '2025-06-03 19:04:48'),
(189, '1045667449', 'Objeto perdida reportado (#27)', 'Tu reporte ha sido registrado y está siendo revisado', 'objeto', 27, 0, '2025-06-03 19:04:48'),
(190, '1045667449', 'Estado de objeto actualizado (#26)', 'El estado de tu objeto reportado ha cambiado a: perdido', 'objeto', 26, 0, '2025-06-03 19:08:44'),
(191, '1078456675', 'Nuevo objeto perdida reportado (#28)', 'Tipo: sombrilla, Ubicación: entrada', 'objeto', 28, 0, '2025-06-03 19:09:40'),
(192, '1078458140', 'Nuevo objeto perdida reportado (#28)', 'Tipo: sombrilla, Ubicación: entrada', 'objeto', 28, 0, '2025-06-03 19:09:40'),
(193, '1078458186', 'Nuevo objeto perdida reportado (#28)', 'Tipo: sombrilla, Ubicación: entrada', 'objeto', 28, 0, '2025-06-03 19:09:40'),
(194, '1078458187', 'Nuevo objeto perdida reportado (#28)', 'Tipo: sombrilla, Ubicación: entrada', 'objeto', 28, 0, '2025-06-03 19:09:40'),
(195, '1078458745', 'Nuevo objeto perdida reportado (#28)', 'Tipo: sombrilla, Ubicación: entrada', 'objeto', 28, 0, '2025-06-03 19:09:40'),
(196, '123456789', 'Nuevo objeto perdida reportado (#28)', 'Tipo: sombrilla, Ubicación: entrada', 'objeto', 28, 0, '2025-06-03 19:09:40'),
(197, '1738946573', 'Nuevo objeto perdida reportado (#28)', 'Tipo: sombrilla, Ubicación: entrada', 'objeto', 28, 0, '2025-06-03 19:09:40'),
(198, '1045667449', 'Objeto perdida reportado (#28)', 'Tu reporte ha sido registrado y está siendo revisado', 'objeto', 28, 0, '2025-06-03 19:09:40'),
(207, '1078456675', 'Nuevo incidente reportado (#4)', 'Tipo: Accidente, Ubicación: me cai', 'incidente', 4, 0, '2025-06-03 19:26:27'),
(208, '1078458140', 'Nuevo incidente reportado (#4)', 'Tipo: Accidente, Ubicación: me cai', 'incidente', 4, 0, '2025-06-03 19:26:27'),
(209, '1078458186', 'Nuevo incidente reportado (#4)', 'Tipo: Accidente, Ubicación: me cai', 'incidente', 4, 1, '2025-06-03 19:27:13'),
(210, '1078458187', 'Nuevo incidente reportado (#4)', 'Tipo: Accidente, Ubicación: me cai', 'incidente', 4, 0, '2025-06-03 19:26:27'),
(211, '1078458745', 'Nuevo incidente reportado (#4)', 'Tipo: Accidente, Ubicación: me cai', 'incidente', 4, 0, '2025-06-03 19:26:27'),
(212, '123456789', 'Nuevo incidente reportado (#4)', 'Tipo: Accidente, Ubicación: me cai', 'incidente', 4, 0, '2025-06-03 19:26:27'),
(213, '1738946573', 'Nuevo incidente reportado (#4)', 'Tipo: Accidente, Ubicación: me cai', 'incidente', 4, 0, '2025-06-03 19:26:27'),
(214, '1045667449', 'Incidente reportado (#4)', 'Tu reporte ha sido registrado y está siendo revisado', 'incidente', 4, 0, '2025-06-03 19:26:27'),
(215, '1045667449', 'Nuevo objeto perdida reportado (#29)', 'Tipo: skjsakjskja, Ubicación: sakjsakjsak', 'objeto', 29, 0, '2025-06-03 19:46:55'),
(216, '1078456675', 'Nuevo objeto perdida reportado (#29)', 'Tipo: skjsakjskja, Ubicación: sakjsakjsak', 'objeto', 29, 0, '2025-06-03 19:46:55'),
(217, '1078458140', 'Nuevo objeto perdida reportado (#29)', 'Tipo: skjsakjskja, Ubicación: sakjsakjsak', 'objeto', 29, 0, '2025-06-03 19:46:55'),
(218, '1078458187', 'Nuevo objeto perdida reportado (#29)', 'Tipo: skjsakjskja, Ubicación: sakjsakjsak', 'objeto', 29, 0, '2025-06-03 19:46:55'),
(219, '1078458745', 'Nuevo objeto perdida reportado (#29)', 'Tipo: skjsakjskja, Ubicación: sakjsakjsak', 'objeto', 29, 0, '2025-06-03 19:46:55'),
(220, '123456789', 'Nuevo objeto perdida reportado (#29)', 'Tipo: skjsakjskja, Ubicación: sakjsakjsak', 'objeto', 29, 0, '2025-06-03 19:46:55'),
(221, '1738946573', 'Nuevo objeto perdida reportado (#29)', 'Tipo: skjsakjskja, Ubicación: sakjsakjsak', 'objeto', 29, 0, '2025-06-03 19:46:55'),
(222, '1078458186', 'Objeto perdida reportado (#29)', 'Tu reporte ha sido registrado y está siendo revisado', 'objeto', 29, 0, '2025-06-03 19:46:55'),
(223, '1045667449', 'Nuevo objeto perdida reportado (#30)', 'Tipo: l,lkllklk, Ubicación: lklkl', 'objeto', 30, 0, '2025-06-03 19:51:56'),
(224, '1078456675', 'Nuevo objeto perdida reportado (#30)', 'Tipo: l,lkllklk, Ubicación: lklkl', 'objeto', 30, 0, '2025-06-03 19:51:56'),
(225, '1078458140', 'Nuevo objeto perdida reportado (#30)', 'Tipo: l,lkllklk, Ubicación: lklkl', 'objeto', 30, 0, '2025-06-03 19:51:56'),
(226, '1078458187', 'Nuevo objeto perdida reportado (#30)', 'Tipo: l,lkllklk, Ubicación: lklkl', 'objeto', 30, 0, '2025-06-03 19:51:56'),
(227, '1078458745', 'Nuevo objeto perdida reportado (#30)', 'Tipo: l,lkllklk, Ubicación: lklkl', 'objeto', 30, 0, '2025-06-03 19:51:56'),
(228, '123456789', 'Nuevo objeto perdida reportado (#30)', 'Tipo: l,lkllklk, Ubicación: lklkl', 'objeto', 30, 0, '2025-06-03 19:51:56'),
(229, '1738946573', 'Nuevo objeto perdida reportado (#30)', 'Tipo: l,lkllklk, Ubicación: lklkl', 'objeto', 30, 0, '2025-06-03 19:51:56'),
(230, '1078458186', 'Objeto perdida reportado (#30)', 'Tu reporte ha sido registrado y está siendo revisado', 'objeto', 30, 0, '2025-06-03 19:51:56'),
(231, '1078456675', 'Nuevo objeto hallazgo reportado (#31)', 'Tipo: chaqueta, Ubicación: Sala 1', 'objeto', 31, 0, '2025-06-03 20:05:30'),
(232, '1078458140', 'Nuevo objeto hallazgo reportado (#31)', 'Tipo: chaqueta, Ubicación: Sala 1', 'objeto', 31, 0, '2025-06-03 20:05:30'),
(233, '1078458186', 'Nuevo objeto hallazgo reportado (#31)', 'Tipo: chaqueta, Ubicación: Sala 1', 'objeto', 31, 0, '2025-06-03 20:05:30'),
(234, '1078458187', 'Nuevo objeto hallazgo reportado (#31)', 'Tipo: chaqueta, Ubicación: Sala 1', 'objeto', 31, 0, '2025-06-03 20:05:30'),
(235, '1078458745', 'Nuevo objeto hallazgo reportado (#31)', 'Tipo: chaqueta, Ubicación: Sala 1', 'objeto', 31, 0, '2025-06-03 20:05:30'),
(236, '123456789', 'Nuevo objeto hallazgo reportado (#31)', 'Tipo: chaqueta, Ubicación: Sala 1', 'objeto', 31, 0, '2025-06-03 20:05:30'),
(237, '1738946573', 'Nuevo objeto hallazgo reportado (#31)', 'Tipo: chaqueta, Ubicación: Sala 1', 'objeto', 31, 0, '2025-06-03 20:05:30'),
(238, '1045667449', 'Objeto hallazgo reportado (#31)', 'Tu reporte ha sido registrado y está siendo revisado', 'objeto', 31, 0, '2025-06-03 20:05:30'),
(239, '1045667449', 'Nuevo objeto hallazgo reportado (#32)', 'Tipo: Calculadora, Ubicación: sala', 'objeto', 32, 0, '2025-06-03 20:07:09'),
(240, '1078456675', 'Nuevo objeto hallazgo reportado (#32)', 'Tipo: Calculadora, Ubicación: sala', 'objeto', 32, 0, '2025-06-03 20:07:09'),
(241, '1078458140', 'Nuevo objeto hallazgo reportado (#32)', 'Tipo: Calculadora, Ubicación: sala', 'objeto', 32, 0, '2025-06-03 20:07:09'),
(242, '1078458187', 'Nuevo objeto hallazgo reportado (#32)', 'Tipo: Calculadora, Ubicación: sala', 'objeto', 32, 0, '2025-06-03 20:07:09'),
(243, '1078458745', 'Nuevo objeto hallazgo reportado (#32)', 'Tipo: Calculadora, Ubicación: sala', 'objeto', 32, 0, '2025-06-03 20:07:09'),
(244, '123456789', 'Nuevo objeto hallazgo reportado (#32)', 'Tipo: Calculadora, Ubicación: sala', 'objeto', 32, 0, '2025-06-03 20:07:09'),
(245, '1738946573', 'Nuevo objeto hallazgo reportado (#32)', 'Tipo: Calculadora, Ubicación: sala', 'objeto', 32, 0, '2025-06-03 20:07:09'),
(246, '1078458186', 'Objeto hallazgo reportado (#32)', 'Tu reporte ha sido registrado y está siendo revisado', 'objeto', 32, 0, '2025-06-03 20:07:09'),
(247, '1078458186', 'Nuevo mensaje sobre objeto (#5)', 'Tienes un nuevo mensaje sobre tu objeto reportado: El usuario   está interesado en reclamar este obje...', 'objeto', 5, 0, '2025-06-03 20:08:28'),
(248, '1078458186', 'Reclamación de objeto', 'El usuario   está interesado en reclamar este objeto. Por favor contacta con él para verificar la propiedad.', 'objeto', 5, 0, '2025-06-03 20:08:28'),
(249, '1078458186', 'Nuevo mensaje sobre objeto (#5)', 'Tienes un nuevo mensaje sobre tu objeto reportado: es mio...', 'objeto', 5, 0, '2025-06-03 20:08:41');

--
-- Disparadores `notificaciones`
--
DELIMITER $$
CREATE TRIGGER `tr_notificaciones_before_update` BEFORE UPDATE ON `notificaciones` FOR EACH ROW BEGIN
    IF NEW.leida = 1 AND OLD.leida = 0 THEN
        SET NEW.fecha_hora = NOW(); -- Actualizar fecha cuando se marca como leída
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `objetos_perdidos`
--

CREATE TABLE `objetos_perdidos` (
  `id_objeto` int(11) NOT NULL,
  `numero_documento` varchar(20) NOT NULL,
  `tipo_objeto` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `ubicacion_perdida` varchar(100) NOT NULL,
  `fecha_perdida` date NOT NULL,
  `fecha_reporte` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('perdido','encontrado','devuelto') DEFAULT 'perdido',
  `tipo_reporte` enum('perdida','hallazgo') NOT NULL DEFAULT 'perdida',
  `imagen_url` varchar(255) DEFAULT NULL,
  `numero_documento_reporta` varchar(20) DEFAULT NULL,
  `ubicacion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `objetos_perdidos`
--

INSERT INTO `objetos_perdidos` (`id_objeto`, `numero_documento`, `tipo_objeto`, `descripcion`, `ubicacion_perdida`, `fecha_perdida`, `fecha_reporte`, `estado`, `tipo_reporte`, `imagen_url`, `numero_documento_reporta`, `ubicacion`) VALUES
(1, '1045667449', 'Calculadora', 'Se me perdió una calculadora color azul, es muy importante, la necesito para un parcial.', 'Segundo piso, salón 801', '2025-05-17', '2025-05-17 20:41:35', 'perdido', 'perdida', NULL, NULL, ''),
(5, '1078458186', 'Calculadora', 'Encontré esta calculadora ', 'Segundo piso, salón 801', '2025-05-17', '2025-05-17 21:13:51', 'perdido', 'hallazgo', NULL, NULL, ''),
(6, '1078458186', 'Calculadora', 'Una verde', 'calle', '2025-06-02', '2025-06-02 15:40:14', 'devuelto', 'hallazgo', NULL, NULL, ''),
(7, '1078458140', 'Cecular', 'Xiomi no 13', 'Sala 1', '2025-06-02', '2025-06-02 18:05:26', 'devuelto', 'perdida', NULL, NULL, ''),
(21, '1078458186', 'Chaqueta', 'nada', '', '2025-06-03', '2025-06-03 09:38:48', '', 'perdida', 'uploads/683ec2a82723c_1748943528.png', NULL, 'Cafeteria'),
(22, '1078458186', 'camisa', 'camisa de rayas vieja', '', '2025-06-02', '2025-06-03 09:45:27', '', 'perdida', 'uploads/683ec4379a8bd_1748943927.png', NULL, 'calle'),
(23, '1078458140', 'Peluche', 'mia tiene atras', 'salon', '2025-06-03', '2025-06-03 16:45:55', '', 'perdida', NULL, NULL, ''),
(24, '1045667449', 'Lapicero', 'Lapicero que tiene nombre de Maria al lado', 'Auditorio ', '2025-06-03', '2025-06-03 19:01:00', '', 'hallazgo', NULL, NULL, ''),
(25, '1045667449', 'Lapicero', 'Lapicero que tiene nombre de Maria al lado', 'Auditorio ', '2025-06-03', '2025-06-03 19:01:05', '', 'hallazgo', NULL, NULL, ''),
(26, '1045667449', 'lapicero', 'lapicero', 'salon', '2025-06-03', '2025-06-03 19:04:00', 'perdido', 'perdida', NULL, NULL, ''),
(27, '1045667449', 'casa', 'casa', 'casa', '2025-06-03', '2025-06-03 19:04:48', '', 'perdida', NULL, NULL, ''),
(28, '1045667449', 'sombrilla', 'sombrilla', 'entrada', '2025-06-03', '2025-06-03 19:09:40', '', 'perdida', NULL, NULL, ''),
(29, '1078458186', 'skjsakjskja', 'kjsakjskajksa', 'sakjsakjsak', '2025-06-03', '2025-06-03 19:46:55', '', 'perdida', NULL, NULL, ''),
(30, '1078458186', 'l,lkllklk', 'ukkkjkj', 'lklkl', '2025-06-03', '2025-06-03 19:51:56', '', 'perdida', NULL, NULL, ''),
(31, '1045667449', 'chaqueta', 'nada', 'Sala 1', '2025-06-03', '2025-06-03 20:05:30', '', 'hallazgo', NULL, NULL, ''),
(32, '1078458186', 'Calculadora', 'cisa', 'sala', '2025-06-03', '2025-06-03 20:07:09', '', 'hallazgo', NULL, NULL, '');

--
-- Disparadores `objetos_perdidos`
--
DELIMITER $$
CREATE TRIGGER `tr_objetos_perdidos_after_insert` AFTER INSERT ON `objetos_perdidos` FOR EACH ROW BEGIN
    -- Notificar a todos los usuarios activos (excepto al que reportó)
    INSERT INTO notificaciones (numero_documento, titulo, mensaje, tipo, id_referencia)
    SELECT numero_documento, 
           CONCAT('Nuevo objeto ', NEW.tipo_reporte, ' reportado (#', NEW.id_objeto, ')'), 
           CONCAT('Tipo: ', NEW.tipo_objeto, ', Ubicación: ', NEW.ubicacion_perdida), 
           'objeto', 
           NEW.id_objeto
    FROM usuarios
    WHERE estado = 'activo' AND numero_documento != NEW.numero_documento;
    
    -- Notificar al usuario que reportó el objeto
    INSERT INTO notificaciones (numero_documento, titulo, mensaje, tipo, id_referencia)
    VALUES (NEW.numero_documento, 
           CONCAT('Objeto ', NEW.tipo_reporte, ' reportado (#', NEW.id_objeto, ')'), 
           'Tu reporte ha sido registrado y está siendo revisado', 
           'objeto', 
           NEW.id_objeto);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_objetos_perdidos_after_update` AFTER UPDATE ON `objetos_perdidos` FOR EACH ROW BEGIN
    IF OLD.estado != NEW.estado THEN
        -- Notificar al usuario que reportó el objeto sobre el cambio de estado
        INSERT INTO notificaciones (numero_documento, titulo, mensaje, tipo, id_referencia)
        VALUES (NEW.numero_documento, 
               CONCAT('Estado de objeto actualizado (#', NEW.id_objeto, ')'), 
               CONCAT('El estado de tu objeto reportado ha cambiado a: ', NEW.estado), 
               'objeto', 
               NEW.id_objeto);
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registros_acceso`
--

CREATE TABLE `registros_acceso` (
  `id_registro` int(11) NOT NULL,
  `numero_documento` varchar(20) DEFAULT NULL,
  `id_visitante` int(11) DEFAULT NULL,
  `placa_vehiculo` varchar(20) DEFAULT NULL,
  `tipo_movimiento` enum('entrada','salida') NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `registrado_por` varchar(20) NOT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `registros_acceso`
--

INSERT INTO `registros_acceso` (`id_registro`, `numero_documento`, `id_visitante`, `placa_vehiculo`, `tipo_movimiento`, `fecha_hora`, `registrado_por`, `observaciones`) VALUES
(1, '1078458186', NULL, NULL, 'entrada', '2025-05-30 02:55:51', '1078458186', 'ninguna'),
(2, '1045667449', NULL, NULL, 'entrada', '2025-05-30 08:26:20', '1078458186', 'Va a entregar un cuaderno'),
(3, NULL, 11, NULL, 'entrada', '2025-06-03 03:03:36', '1078456675', 'Nada');

--
-- Disparadores `registros_acceso`
--
DELIMITER $$
CREATE TRIGGER `tr_registros_acceso_after_insert` AFTER INSERT ON `registros_acceso` FOR EACH ROW BEGIN
    DECLARE v_nombre_completo VARCHAR(201);
    
    -- Obtener nombre del usuario o visitante
    IF NEW.numero_documento IS NOT NULL THEN
        SELECT CONCAT(nombres, ' ', apellidos) INTO v_nombre_completo FROM usuarios WHERE numero_documento = NEW.numero_documento;
    ELSE
        SELECT CONCAT(nombres, ' ', apellidos) INTO v_nombre_completo FROM visitantes WHERE id_visitante = NEW.id_visitante;
    END IF;
    
    -- Registrar en auditoría usando el documento del registrador (celador/administrador)
    INSERT INTO auditoria (numero_documento, accion, tabla_afectada, id_registro_afectado, datos_nuevos)
    VALUES (NEW.registrado_por, 
           CONCAT('Registro de ', NEW.tipo_movimiento, ' de ', IF(NEW.numero_documento IS NOT NULL, 'usuario', 'visitante')), 
           'registros_acceso', 
           NEW.id_registro, 
           CONCAT('Persona: ', IFNULL(v_nombre_completo, 'Visitante'), 
           ', Movimiento: ', NEW.tipo_movimiento));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_registros_acceso_before_insert` BEFORE INSERT ON `registros_acceso` FOR EACH ROW BEGIN
    DECLARE v_ultimo_movimiento ENUM('entrada', 'salida');
    DECLARE v_error_msg VARCHAR(255);
    
    -- Obtener último movimiento del usuario/visitante
    IF NEW.numero_documento IS NOT NULL THEN
        SELECT tipo_movimiento INTO v_ultimo_movimiento
        FROM registros_acceso
        WHERE numero_documento = NEW.numero_documento
        ORDER BY fecha_hora DESC
        LIMIT 1;
    ELSE
        SELECT tipo_movimiento INTO v_ultimo_movimiento
        FROM registros_acceso
        WHERE id_visitante = NEW.id_visitante
        ORDER BY fecha_hora DESC
        LIMIT 1;
    END IF;
    
    -- Validar que no se repita el mismo tipo de movimiento
    IF v_ultimo_movimiento = NEW.tipo_movimiento THEN
        IF NEW.numero_documento IS NOT NULL THEN
            SET v_error_msg = CONCAT('No se puede registrar ', NEW.tipo_movimiento, 
                                    ' porque el último registro para este usuario fue ', v_ultimo_movimiento);
        ELSE
            SET v_error_msg = CONCAT('No se puede registrar ', NEW.tipo_movimiento, 
                                    ' porque el último registro para este visitante fue ', v_ultimo_movimiento);
        END IF;
        
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_error_msg;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registros_vehiculos`
--

CREATE TABLE `registros_vehiculos` (
  `id_registro_vehiculo` int(11) NOT NULL,
  `id_vehiculo` int(11) NOT NULL,
  `tipo_movimiento` enum('entrada','salida') NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `registrado_por` varchar(20) NOT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Disparadores `registros_vehiculos`
--
DELIMITER $$
CREATE TRIGGER `tr_registros_vehiculos_after_insert` AFTER INSERT ON `registros_vehiculos` FOR EACH ROW BEGIN
    DECLARE v_placa VARCHAR(10);
    DECLARE v_propietario VARCHAR(201);
    
    -- Obtener datos del vehículo
    SELECT v.placa, CONCAT(u.nombres, ' ', u.apellidos) 
    INTO v_placa, v_propietario
    FROM vehiculos v
    JOIN usuarios u ON v.numero_documento = u.numero_documento
    WHERE v.id_vehiculo = NEW.id_vehiculo;
    
    -- Registrar en auditoría
    INSERT INTO auditoria (numero_documento, accion, tabla_afectada, id_registro_afectado, datos_nuevos)
    VALUES (NEW.registrado_por, 
           CONCAT('Registro de ', NEW.tipo_movimiento, ' de vehículo'), 
           'registros_vehiculos', 
           NEW.id_registro_vehiculo, 
           CONCAT('Placa: ', v_placa, ', Propietario: ', v_propietario, 
                  ', Movimiento: ', NEW.tipo_movimiento));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes_incidente`
--

CREATE TABLE `reportes_incidente` (
  `id_reporte` int(11) NOT NULL,
  `numero_documento` varchar(20) NOT NULL,
  `id_tipo` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `ubicacion` varchar(100) NOT NULL,
  `fecha_incidente` datetime NOT NULL,
  `fecha_reporte` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('reportado','en_revision','resuelto','archivado') DEFAULT 'reportado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reportes_incidente`
--

INSERT INTO `reportes_incidente` (`id_reporte`, `numero_documento`, `id_tipo`, `descripcion`, `ubicacion`, `fecha_incidente`, `fecha_reporte`, `estado`) VALUES
(1, '1078458186', 7, 'en moro', 'calle', '2025-05-30 15:03:00', '2025-05-30 13:03:43', 'reportado'),
(2, '1078458186', 4, 'Nada no se', 'calle', '2025-06-03 09:07:00', '2025-06-03 07:07:53', 'reportado'),
(4, '1045667449', 7, 'cicla', 'me cai', '2025-06-03 21:25:00', '2025-06-03 19:26:27', 'reportado');

--
-- Disparadores `reportes_incidente`
--
DELIMITER $$
CREATE TRIGGER `tr_reportes_incidente_after_insert` AFTER INSERT ON `reportes_incidente` FOR EACH ROW BEGIN
    -- Notificar a todos los usuarios activos (excepto al que reportó)
    INSERT INTO notificaciones (numero_documento, titulo, mensaje, tipo, id_referencia)
    SELECT numero_documento, 
           CONCAT('Nuevo incidente reportado (#', NEW.id_reporte, ')'), 
           CONCAT('Tipo: ', (SELECT nombre FROM tipos_incidente WHERE id_tipo = NEW.id_tipo), 
                  ', Ubicación: ', NEW.ubicacion), 
           'incidente', 
           NEW.id_reporte
    FROM usuarios
    WHERE estado = 'activo' AND numero_documento != NEW.numero_documento;
    
    -- Notificar al usuario que reportó el incidente
    INSERT INTO notificaciones (numero_documento, titulo, mensaje, tipo, id_referencia)
    VALUES (NEW.numero_documento, 
           CONCAT('Incidente reportado (#', NEW.id_reporte, ')'), 
           'Tu reporte ha sido registrado y está siendo revisado', 
           'incidente', 
           NEW.id_reporte);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_reportes_incidente_after_update` AFTER UPDATE ON `reportes_incidente` FOR EACH ROW BEGIN
    IF OLD.estado != NEW.estado THEN
        -- Notificar al usuario que reportó el incidente sobre el cambio de estado
        INSERT INTO notificaciones (numero_documento, titulo, mensaje, tipo, id_referencia)
        VALUES (NEW.numero_documento, 
               CONCAT('Estado de reporte actualizado (#', NEW.id_reporte, ')'), 
               CONCAT('El estado de tu reporte ha cambiado a: ', NEW.estado), 
               'incidente', 
               NEW.id_reporte);
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuestas_incidentes`
--

CREATE TABLE `respuestas_incidentes` (
  `id_respuesta` int(11) NOT NULL,
  `id_reporte` int(11) NOT NULL,
  `numero_documento` varchar(20) NOT NULL,
  `respuesta` text NOT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre_rol`, `descripcion`, `creado_en`) VALUES
(1, 'Estudiante', 'Usuarios estudiantes de la universidad', '2025-05-16 16:42:12'),
(2, 'Docente', 'Profesores y docentes de la universidad', '2025-05-16 16:42:12'),
(3, 'Personal Administrativo', 'Personal administrativo no relacionado con seguridad', '2025-05-16 16:42:12'),
(4, 'Celador', 'Personal encargado de la seguridad y control de acceso', '2025-05-16 16:42:12'),
(5, 'Administrador', 'Administradores del sistema con todos los privilegios', '2025-05-16 16:42:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_incidente`
--

CREATE TABLE `tipos_incidente` (
  `id_tipo` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `severidad` enum('baja','media','alta','critica') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipos_incidente`
--

INSERT INTO `tipos_incidente` (`id_tipo`, `nombre`, `descripcion`, `severidad`) VALUES
(1, 'Robo', 'Sustracción ilegal de pertenencias', 'alta'),
(2, 'Hurto', 'Sustracción sin violencia', 'media'),
(3, 'Objeto sospechoso', 'Objeto que genera sospechas de peligro', 'critica'),
(4, 'Comportamiento inusual', 'Conducta sospechosa de personas', 'media'),
(5, 'Emergencia médica', 'Situación que requiere atención médica', 'alta'),
(6, 'Daño a propiedad', 'Destrucción o daño a instalaciones', 'media'),
(7, 'Accidente', 'Incidente accidental', 'baja'),
(9, 'Otro', 'Actividad en la que se debe describir', 'alta');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `numero_documento` varchar(20) NOT NULL,
  `tipo_documento` enum('CC','TI','CE','PA') NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `estado` enum('activo','inactivo','bloqueado') DEFAULT 'activo',
  `intentos_fallidos` int(11) DEFAULT 0,
  `ultimo_acceso` datetime DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`numero_documento`, `tipo_documento`, `nombres`, `apellidos`, `correo`, `contrasena`, `id_rol`, `estado`, `intentos_fallidos`, `ultimo_acceso`, `creado_en`, `actualizado_en`) VALUES
('1045667449', 'CC', 'KAROL GIHAN', 'SALINAS GONZALEZ', 'karol@gmail.com', '86f2f4278dc1843c2d02dd70e2346440603e09f231aeb2f8a5bbaea7044852d1', 1, 'activo', 0, '2025-09-14 20:07:21', '2025-05-17 20:29:05', '2025-09-15 01:07:21'),
('1078456675', 'CC', 'Andres', 'Martinez Palacios', 'andres@gmail.com', 'be99b09687f01286be192d2a91f900525abc632edcc17a81d84962a852dfdb3c', 4, 'activo', 0, '2025-09-12 14:21:28', '2025-05-16 20:23:48', '2025-09-12 19:21:28'),
('1078458140', 'CC', 'Mercy', 'Perea Gutierrez', 'mercy@gmail.com', '613aba39d1baac9bbfd96a2f0baaeba2bc062633d72cc4ef526dff0f2920753a', 1, 'activo', 0, '2025-06-03 15:09:38', '2025-06-02 16:37:32', '2025-06-03 20:09:38'),
('1078458186', 'CC', 'Gabriela', 'Cordoba Gonzalez', 'gabriela@gmail.com', 'f3eee7d35026cb68f540800bd59fa287ad3e4437b39b0c2ab1177382873ba5f2', 5, 'activo', 0, '2025-09-14 20:08:25', '2025-05-16 20:01:41', '2025-09-15 01:08:25'),
('1078458187', 'CC', 'Marlen', 'Mena Mena', 'marlen@gmail.com', '009d5397cba7ca2222c68612e09c869e9c6200ec85fd34445df01ca1a36a9567', 2, 'activo', 0, '2025-06-03 00:00:52', '2025-06-02 16:15:40', '2025-06-03 05:00:52'),
('1078458745', 'CC', 'Melissa', 'Ocampo Aguirre', 'melissa@gmail.com', 'b399e48b8f66eb21eb8baec7b77b3f8f3cd598259f2b11436bc6cdabe1156b05', 3, 'activo', 0, '2025-06-03 03:39:36', '2025-06-03 02:17:54', '2025-06-03 08:39:36'),
('123456789', 'CC', 'Admin', 'Principal', 'admin@universidad.edu', '3b612c75a7b5048a435fb6ec81e52ff92d6d795a8b5a9c17070f6a63c97a53b2', 5, 'activo', 0, NULL, '2025-05-16 16:42:13', '2025-05-16 16:42:13'),
('1738946573', 'CC', 'Mathias Carlos', 'Menendez Piedraita', 'mathias123@gmail.com', '018a98af2fd448949c0f8bed2cebd4a0c3406b95e17d317b662a1c240fbc0d6f', 3, 'activo', 0, '2025-06-03 03:29:40', '2025-05-17 21:02:23', '2025-06-03 08:29:40');

--
-- Disparadores `usuarios`
--
DELIMITER $$
CREATE TRIGGER `tr_usuarios_after_insert` AFTER INSERT ON `usuarios` FOR EACH ROW BEGIN
    INSERT INTO auditoria (numero_documento, accion, tabla_afectada, datos_nuevos)
    VALUES (NEW.numero_documento, 'Creación de nuevo usuario', 'usuarios', 
            CONCAT('Rol: ', (SELECT nombre_rol FROM roles WHERE id_rol = NEW.id_rol), 
                   ', Nombre: ', NEW.nombres, ' ', NEW.apellidos));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_usuarios_after_update` AFTER UPDATE ON `usuarios` FOR EACH ROW BEGIN
    IF OLD.estado != NEW.estado THEN
        INSERT INTO auditoria (numero_documento, accion, tabla_afectada, id_registro_afectado, datos_anteriores, datos_nuevos)
        VALUES (NEW.numero_documento, 'Cambio de estado de usuario', 'usuarios', NULL, 
                CONCAT('Estado anterior: ', OLD.estado), 
                CONCAT('Nuevo estado: ', NEW.estado));
    END IF;
    
    IF OLD.id_rol != NEW.id_rol THEN
        INSERT INTO auditoria (numero_documento, accion, tabla_afectada, id_registro_afectado, datos_anteriores, datos_nuevos)
        VALUES (NEW.numero_documento, 'Cambio de rol de usuario', 'usuarios', NULL, 
                CONCAT('Rol anterior: ', (SELECT nombre_rol FROM roles WHERE id_rol = OLD.id_rol)), 
                CONCAT('Nuevo rol: ', (SELECT nombre_rol FROM roles WHERE id_rol = NEW.id_rol)));
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_usuarios_after_update_intentos` AFTER UPDATE ON `usuarios` FOR EACH ROW BEGIN
    IF NEW.intentos_fallidos > OLD.intentos_fallidos AND NEW.intentos_fallidos >= 3 AND NEW.estado != 'bloqueado' THEN
        INSERT INTO auditoria (numero_documento, accion, tabla_afectada, ip_origen)
        VALUES (NEW.numero_documento, 'Usuario bloqueado por intentos fallidos', 'usuarios', @ip_origen);
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_usuarios_before_delete` BEFORE DELETE ON `usuarios` FOR EACH ROW BEGIN
    INSERT INTO auditoria (numero_documento, accion, tabla_afectada, datos_anteriores)
    VALUES (OLD.numero_documento, 'Eliminación de usuario', 'usuarios', 
            CONCAT('Usuario eliminado: ', OLD.nombres, ' ', OLD.apellidos, 
                   ', Rol: ', (SELECT nombre_rol FROM roles WHERE id_rol = OLD.id_rol)));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculos`
--

CREATE TABLE `vehiculos` (
  `id_vehiculo` int(11) NOT NULL,
  `numero_documento` varchar(20) NOT NULL,
  `placa` varchar(10) NOT NULL,
  `tipo` enum('carro','moto') NOT NULL,
  `marca` varchar(50) DEFAULT NULL,
  `modelo` varchar(50) DEFAULT NULL,
  `color` varchar(30) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `visitantes`
--

CREATE TABLE `visitantes` (
  `id_visitante` int(11) NOT NULL,
  `tipo_documento` enum('CC','TI','CE','PA') NOT NULL,
  `numero_documento` varchar(20) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `motivo_visita` varchar(255) NOT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `visitantes`
--

INSERT INTO `visitantes` (`id_visitante`, `tipo_documento`, `numero_documento`, `nombres`, `apellidos`, `motivo_visita`, `contacto`, `creado_en`) VALUES
(11, 'CC', '1826354673', 'Potter', 'Wisly', 'Proceso matricula', '3264738454', '2025-06-03 08:03:36');

--
-- Disparadores `visitantes`
--
DELIMITER $$
CREATE TRIGGER `tr_visitantes_after_insert` AFTER INSERT ON `visitantes` FOR EACH ROW BEGIN
    INSERT INTO auditoria (numero_documento, accion, tabla_afectada, id_registro_afectado, datos_nuevos)
    VALUES (NEW.numero_documento, 'Registro de nuevo visitante', 'visitantes', NEW.id_visitante, 
            CONCAT('Nombre: ', NEW.nombres, ' ', NEW.apellidos, ', Motivo: ', NEW.motivo_visita));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_visitantes_after_update` AFTER UPDATE ON `visitantes` FOR EACH ROW BEGIN
    IF OLD.motivo_visita != NEW.motivo_visita OR OLD.contacto != NEW.contacto THEN
        INSERT INTO auditoria (numero_documento, accion, tabla_afectada, id_registro_afectado, 
                              datos_anteriores, datos_nuevos)
        VALUES (NEW.numero_documento, 'Actualización de datos de visitante', 'visitantes', NEW.id_visitante, 
                CONCAT('Motivo anterior: ', OLD.motivo_visita, ', Contacto anterior: ', OLD.contacto),
                CONCAT('Nuevo motivo: ', NEW.motivo_visita, ', Nuevo contacto: ', NEW.contacto));
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_notificaciones`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_notificaciones` (
`id_notificacion` int(11)
,`titulo` varchar(100)
,`mensaje` text
,`tipo` enum('incidente','objeto','seguridad','sistema')
,`id_referencia` int(11)
,`leida` tinyint(1)
,`fecha_hora` timestamp
,`usuario` varchar(201)
,`numero_documento` varchar(20)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_objetos_perdidos`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_objetos_perdidos` (
`id_objeto` int(11)
,`tipo_objeto` varchar(100)
,`descripcion` text
,`ubicacion_perdida` varchar(100)
,`fecha_perdida` date
,`fecha_reporte` timestamp
,`estado` enum('perdido','encontrado','devuelto')
,`reportado_por` varchar(201)
,`numero_documento` varchar(20)
,`total_imagenes` bigint(21)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_panel_administracion`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_panel_administracion` (
`usuarios_activos` bigint(21)
,`ingresos_hoy` bigint(21)
,`incidentes_pendientes` bigint(21)
,`incidentes_en_revision` bigint(21)
,`objetos_perdidos` bigint(21)
,`objetos_encontrados` bigint(21)
,`visitantes_activos` bigint(21)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_panel_celador`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_panel_celador` (
`numero_documento` varchar(20)
,`nombre_completo` varchar(201)
,`registros_hoy` bigint(21)
,`incidentes_pendientes` bigint(21)
,`objetos_perdidos` bigint(21)
,`notificaciones_no_leidas` bigint(21)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_panel_usuario`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_panel_usuario` (
`numero_documento` varchar(20)
,`nombre_completo` varchar(201)
,`rol` varchar(50)
,`total_incidentes` bigint(21)
,`total_objetos` bigint(21)
,`notificaciones_no_leidas` bigint(21)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_registros_acceso`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_registros_acceso` (
`id_registro` int(11)
,`numero_documento` varchar(20)
,`nombre_completo` varchar(201)
,`tipo_persona` varchar(50)
,`tipo_movimiento` enum('entrada','salida')
,`fecha_hora` datetime
,`observaciones` text
,`registrado_por` varchar(201)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_reportes_incidente`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_reportes_incidente` (
`id_reporte` int(11)
,`tipo_incidente` varchar(50)
,`severidad` enum('baja','media','alta','critica')
,`descripcion` text
,`ubicacion` varchar(100)
,`fecha_incidente` datetime
,`fecha_reporte` timestamp
,`estado` enum('reportado','en_revision','resuelto','archivado')
,`reportado_por` varchar(201)
,`numero_documento` varchar(20)
,`total_evidencias` bigint(21)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_resumen_administracion`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_resumen_administracion` (
`total_usuarios` bigint(21)
,`visitantes_activos` bigint(21)
,`incidentes_reportados` bigint(21)
,`incidentes_en_revision` bigint(21)
,`objetos_perdidos` bigint(21)
,`objetos_encontrados` bigint(21)
,`ingresos_hoy` bigint(21)
,`vehiculos_ingresados_hoy` bigint(21)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_notificaciones`
--
DROP TABLE IF EXISTS `vw_notificaciones`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_notificaciones`  AS SELECT `n`.`id_notificacion` AS `id_notificacion`, `n`.`titulo` AS `titulo`, `n`.`mensaje` AS `mensaje`, `n`.`tipo` AS `tipo`, `n`.`id_referencia` AS `id_referencia`, `n`.`leida` AS `leida`, `n`.`fecha_hora` AS `fecha_hora`, concat(`u`.`nombres`,' ',`u`.`apellidos`) AS `usuario`, `u`.`numero_documento` AS `numero_documento` FROM (`notificaciones` `n` join `usuarios` `u` on(`n`.`numero_documento` = `u`.`numero_documento`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_objetos_perdidos`
--
DROP TABLE IF EXISTS `vw_objetos_perdidos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_objetos_perdidos`  AS SELECT `op`.`id_objeto` AS `id_objeto`, `op`.`tipo_objeto` AS `tipo_objeto`, `op`.`descripcion` AS `descripcion`, `op`.`ubicacion_perdida` AS `ubicacion_perdida`, `op`.`fecha_perdida` AS `fecha_perdida`, `op`.`fecha_reporte` AS `fecha_reporte`, `op`.`estado` AS `estado`, concat(`u`.`nombres`,' ',`u`.`apellidos`) AS `reportado_por`, `u`.`numero_documento` AS `numero_documento`, (select count(0) from `imagenes_objeto` where `imagenes_objeto`.`id_objeto` = `op`.`id_objeto`) AS `total_imagenes` FROM (`objetos_perdidos` `op` join `usuarios` `u` on(`op`.`numero_documento` = `u`.`numero_documento`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_panel_administracion`
--
DROP TABLE IF EXISTS `vw_panel_administracion`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_panel_administracion`  AS SELECT (select count(0) from `usuarios` where `usuarios`.`estado` = 'activo') AS `usuarios_activos`, (select count(0) from `registros_acceso` where cast(`registros_acceso`.`fecha_hora` as date) = curdate() and `registros_acceso`.`tipo_movimiento` = 'entrada') AS `ingresos_hoy`, (select count(0) from `reportes_incidente` where `reportes_incidente`.`estado` = 'reportado') AS `incidentes_pendientes`, (select count(0) from `reportes_incidente` where `reportes_incidente`.`estado` = 'en_revision') AS `incidentes_en_revision`, (select count(0) from `objetos_perdidos` where `objetos_perdidos`.`estado` = 'perdido') AS `objetos_perdidos`, (select count(0) from `objetos_perdidos` where `objetos_perdidos`.`estado` = 'encontrado') AS `objetos_encontrados`, (select count(0) from `visitantes` where `visitantes`.`id_visitante` in (select `registros_acceso`.`id_visitante` from `registros_acceso` where `registros_acceso`.`tipo_movimiento` = 'entrada' and !(`registros_acceso`.`id_visitante` in (select `registros_acceso`.`id_visitante` from `registros_acceso` where `registros_acceso`.`tipo_movimiento` = 'salida')))) AS `visitantes_activos` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_panel_celador`
--
DROP TABLE IF EXISTS `vw_panel_celador`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_panel_celador`  AS SELECT `u`.`numero_documento` AS `numero_documento`, concat(`u`.`nombres`,' ',`u`.`apellidos`) AS `nombre_completo`, (select count(0) from `registros_acceso` where `registros_acceso`.`registrado_por` = `u`.`numero_documento` and cast(`registros_acceso`.`fecha_hora` as date) = curdate()) AS `registros_hoy`, (select count(0) from `reportes_incidente` where `reportes_incidente`.`estado` = 'reportado') AS `incidentes_pendientes`, (select count(0) from `objetos_perdidos` where `objetos_perdidos`.`estado` = 'perdido') AS `objetos_perdidos`, (select count(0) from `notificaciones` where `notificaciones`.`numero_documento` = `u`.`numero_documento` and `notificaciones`.`leida` = 0) AS `notificaciones_no_leidas` FROM `usuarios` AS `u` WHERE `u`.`id_rol` in (4,5) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_panel_usuario`
--
DROP TABLE IF EXISTS `vw_panel_usuario`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_panel_usuario`  AS SELECT `u`.`numero_documento` AS `numero_documento`, concat(`u`.`nombres`,' ',`u`.`apellidos`) AS `nombre_completo`, `r`.`nombre_rol` AS `rol`, (select count(0) from `reportes_incidente` where `reportes_incidente`.`numero_documento` = `u`.`numero_documento`) AS `total_incidentes`, (select count(0) from `objetos_perdidos` where `objetos_perdidos`.`numero_documento` = `u`.`numero_documento`) AS `total_objetos`, (select count(0) from `notificaciones` where `notificaciones`.`numero_documento` = `u`.`numero_documento` and `notificaciones`.`leida` = 0) AS `notificaciones_no_leidas` FROM (`usuarios` `u` join `roles` `r` on(`u`.`id_rol` = `r`.`id_rol`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_registros_acceso`
--
DROP TABLE IF EXISTS `vw_registros_acceso`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_registros_acceso`  AS SELECT `ra`.`id_registro` AS `id_registro`, coalesce(`u`.`numero_documento`,`v`.`numero_documento`) AS `numero_documento`, coalesce(concat(`u`.`nombres`,' ',`u`.`apellidos`),concat(`v`.`nombres`,' ',`v`.`apellidos`)) AS `nombre_completo`, coalesce(`r`.`nombre_rol`,'Visitante') AS `tipo_persona`, `ra`.`tipo_movimiento` AS `tipo_movimiento`, `ra`.`fecha_hora` AS `fecha_hora`, `ra`.`observaciones` AS `observaciones`, concat(`admin`.`nombres`,' ',`admin`.`apellidos`) AS `registrado_por` FROM ((((`registros_acceso` `ra` left join `usuarios` `u` on(`ra`.`numero_documento` = `u`.`numero_documento`)) left join `visitantes` `v` on(`ra`.`id_visitante` = `v`.`id_visitante`)) left join `roles` `r` on(`u`.`id_rol` = `r`.`id_rol`)) join `usuarios` `admin` on(`ra`.`registrado_por` = `admin`.`numero_documento`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_reportes_incidente`
--
DROP TABLE IF EXISTS `vw_reportes_incidente`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_reportes_incidente`  AS SELECT `ri`.`id_reporte` AS `id_reporte`, `ti`.`nombre` AS `tipo_incidente`, `ti`.`severidad` AS `severidad`, `ri`.`descripcion` AS `descripcion`, `ri`.`ubicacion` AS `ubicacion`, `ri`.`fecha_incidente` AS `fecha_incidente`, `ri`.`fecha_reporte` AS `fecha_reporte`, `ri`.`estado` AS `estado`, concat(`u`.`nombres`,' ',`u`.`apellidos`) AS `reportado_por`, `u`.`numero_documento` AS `numero_documento`, (select count(0) from `evidencias_incidente` where `evidencias_incidente`.`id_reporte` = `ri`.`id_reporte`) AS `total_evidencias` FROM ((`reportes_incidente` `ri` join `tipos_incidente` `ti` on(`ri`.`id_tipo` = `ti`.`id_tipo`)) join `usuarios` `u` on(`ri`.`numero_documento` = `u`.`numero_documento`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_resumen_administracion`
--
DROP TABLE IF EXISTS `vw_resumen_administracion`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_resumen_administracion`  AS SELECT (select count(0) from `usuarios`) AS `total_usuarios`, (select count(0) from `visitantes` where `visitantes`.`id_visitante` in (select `registros_acceso`.`id_visitante` from `registros_acceso` where `registros_acceso`.`tipo_movimiento` = 'entrada' and !(`registros_acceso`.`id_visitante` in (select `registros_acceso`.`id_visitante` from `registros_acceso` where `registros_acceso`.`tipo_movimiento` = 'salida')))) AS `visitantes_activos`, (select count(0) from `reportes_incidente` where `reportes_incidente`.`estado` = 'reportado') AS `incidentes_reportados`, (select count(0) from `reportes_incidente` where `reportes_incidente`.`estado` = 'en_revision') AS `incidentes_en_revision`, (select count(0) from `objetos_perdidos` where `objetos_perdidos`.`estado` = 'perdido') AS `objetos_perdidos`, (select count(0) from `objetos_perdidos` where `objetos_perdidos`.`estado` = 'encontrado') AS `objetos_encontrados`, (select count(0) from `registros_acceso` where cast(`registros_acceso`.`fecha_hora` as date) = curdate() and `registros_acceso`.`tipo_movimiento` = 'entrada') AS `ingresos_hoy`, (select count(0) from `registros_vehiculos` where cast(`registros_vehiculos`.`fecha_hora` as date) = curdate() and `registros_vehiculos`.`tipo_movimiento` = 'entrada') AS `vehiculos_ingresados_hoy` ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `aprobaciones_reportes`
--
ALTER TABLE `aprobaciones_reportes`
  ADD PRIMARY KEY (`id_aprobacion`),
  ADD KEY `id_reporte` (`id_reporte`),
  ADD KEY `id_objeto` (`id_objeto`),
  ADD KEY `aprobado_por` (`aprobado_por`);

--
-- Indices de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`id_auditoria`),
  ADD KEY `numero_documento` (`numero_documento`);

--
-- Indices de la tabla `comentarios_objetos`
--
ALTER TABLE `comentarios_objetos`
  ADD PRIMARY KEY (`id_comentario`),
  ADD KEY `id_objeto` (`id_objeto`),
  ADD KEY `numero_documento` (`numero_documento`);

--
-- Indices de la tabla `evidencias_incidente`
--
ALTER TABLE `evidencias_incidente`
  ADD PRIMARY KEY (`id_evidencia`),
  ADD KEY `id_reporte` (`id_reporte`);

--
-- Indices de la tabla `imagenes_objeto`
--
ALTER TABLE `imagenes_objeto`
  ADD PRIMARY KEY (`id_imagen`),
  ADD KEY `id_objeto` (`id_objeto`);

--
-- Indices de la tabla `mensajes_objetos`
--
ALTER TABLE `mensajes_objetos`
  ADD PRIMARY KEY (`id_mensaje`),
  ADD KEY `id_objeto` (`id_objeto`),
  ADD KEY `numero_documento` (`numero_documento`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `numero_documento` (`numero_documento`);

--
-- Indices de la tabla `objetos_perdidos`
--
ALTER TABLE `objetos_perdidos`
  ADD PRIMARY KEY (`id_objeto`),
  ADD KEY `numero_documento` (`numero_documento`);

--
-- Indices de la tabla `registros_acceso`
--
ALTER TABLE `registros_acceso`
  ADD PRIMARY KEY (`id_registro`),
  ADD KEY `numero_documento` (`numero_documento`),
  ADD KEY `id_visitante` (`id_visitante`),
  ADD KEY `registrado_por` (`registrado_por`);

--
-- Indices de la tabla `registros_vehiculos`
--
ALTER TABLE `registros_vehiculos`
  ADD PRIMARY KEY (`id_registro_vehiculo`),
  ADD KEY `id_vehiculo` (`id_vehiculo`),
  ADD KEY `registrado_por` (`registrado_por`);

--
-- Indices de la tabla `reportes_incidente`
--
ALTER TABLE `reportes_incidente`
  ADD PRIMARY KEY (`id_reporte`),
  ADD KEY `numero_documento` (`numero_documento`),
  ADD KEY `id_tipo` (`id_tipo`);

--
-- Indices de la tabla `respuestas_incidentes`
--
ALTER TABLE `respuestas_incidentes`
  ADD PRIMARY KEY (`id_respuesta`),
  ADD KEY `id_reporte` (`id_reporte`),
  ADD KEY `numero_documento` (`numero_documento`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `tipos_incidente`
--
ALTER TABLE `tipos_incidente`
  ADD PRIMARY KEY (`id_tipo`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`numero_documento`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `id_rol` (`id_rol`);

--
-- Indices de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD PRIMARY KEY (`id_vehiculo`),
  ADD UNIQUE KEY `placa` (`placa`),
  ADD KEY `numero_documento` (`numero_documento`);

--
-- Indices de la tabla `visitantes`
--
ALTER TABLE `visitantes`
  ADD PRIMARY KEY (`id_visitante`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `aprobaciones_reportes`
--
ALTER TABLE `aprobaciones_reportes`
  MODIFY `id_aprobacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `id_auditoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=245;

--
-- AUTO_INCREMENT de la tabla `comentarios_objetos`
--
ALTER TABLE `comentarios_objetos`
  MODIFY `id_comentario` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `evidencias_incidente`
--
ALTER TABLE `evidencias_incidente`
  MODIFY `id_evidencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `imagenes_objeto`
--
ALTER TABLE `imagenes_objeto`
  MODIFY `id_imagen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `mensajes_objetos`
--
ALTER TABLE `mensajes_objetos`
  MODIFY `id_mensaje` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=250;

--
-- AUTO_INCREMENT de la tabla `objetos_perdidos`
--
ALTER TABLE `objetos_perdidos`
  MODIFY `id_objeto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `registros_acceso`
--
ALTER TABLE `registros_acceso`
  MODIFY `id_registro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `registros_vehiculos`
--
ALTER TABLE `registros_vehiculos`
  MODIFY `id_registro_vehiculo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reportes_incidente`
--
ALTER TABLE `reportes_incidente`
  MODIFY `id_reporte` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `respuestas_incidentes`
--
ALTER TABLE `respuestas_incidentes`
  MODIFY `id_respuesta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `tipos_incidente`
--
ALTER TABLE `tipos_incidente`
  MODIFY `id_tipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  MODIFY `id_vehiculo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `visitantes`
--
ALTER TABLE `visitantes`
  MODIFY `id_visitante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `aprobaciones_reportes`
--
ALTER TABLE `aprobaciones_reportes`
  ADD CONSTRAINT `aprobaciones_reportes_ibfk_1` FOREIGN KEY (`id_reporte`) REFERENCES `reportes_incidente` (`id_reporte`),
  ADD CONSTRAINT `aprobaciones_reportes_ibfk_2` FOREIGN KEY (`id_objeto`) REFERENCES `objetos_perdidos` (`id_objeto`),
  ADD CONSTRAINT `aprobaciones_reportes_ibfk_3` FOREIGN KEY (`aprobado_por`) REFERENCES `usuarios` (`numero_documento`);

--
-- Filtros para la tabla `comentarios_objetos`
--
ALTER TABLE `comentarios_objetos`
  ADD CONSTRAINT `comentarios_objetos_ibfk_1` FOREIGN KEY (`id_objeto`) REFERENCES `objetos_perdidos` (`id_objeto`),
  ADD CONSTRAINT `comentarios_objetos_ibfk_2` FOREIGN KEY (`numero_documento`) REFERENCES `usuarios` (`numero_documento`);

--
-- Filtros para la tabla `evidencias_incidente`
--
ALTER TABLE `evidencias_incidente`
  ADD CONSTRAINT `evidencias_incidente_ibfk_1` FOREIGN KEY (`id_reporte`) REFERENCES `reportes_incidente` (`id_reporte`);

--
-- Filtros para la tabla `imagenes_objeto`
--
ALTER TABLE `imagenes_objeto`
  ADD CONSTRAINT `imagenes_objeto_ibfk_1` FOREIGN KEY (`id_objeto`) REFERENCES `objetos_perdidos` (`id_objeto`);

--
-- Filtros para la tabla `mensajes_objetos`
--
ALTER TABLE `mensajes_objetos`
  ADD CONSTRAINT `mensajes_objetos_ibfk_1` FOREIGN KEY (`id_objeto`) REFERENCES `objetos_perdidos` (`id_objeto`),
  ADD CONSTRAINT `mensajes_objetos_ibfk_2` FOREIGN KEY (`numero_documento`) REFERENCES `usuarios` (`numero_documento`);

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`numero_documento`) REFERENCES `usuarios` (`numero_documento`);

--
-- Filtros para la tabla `objetos_perdidos`
--
ALTER TABLE `objetos_perdidos`
  ADD CONSTRAINT `objetos_perdidos_ibfk_1` FOREIGN KEY (`numero_documento`) REFERENCES `usuarios` (`numero_documento`);

--
-- Filtros para la tabla `registros_acceso`
--
ALTER TABLE `registros_acceso`
  ADD CONSTRAINT `registros_acceso_ibfk_1` FOREIGN KEY (`numero_documento`) REFERENCES `usuarios` (`numero_documento`),
  ADD CONSTRAINT `registros_acceso_ibfk_2` FOREIGN KEY (`id_visitante`) REFERENCES `visitantes` (`id_visitante`),
  ADD CONSTRAINT `registros_acceso_ibfk_3` FOREIGN KEY (`registrado_por`) REFERENCES `usuarios` (`numero_documento`);

--
-- Filtros para la tabla `registros_vehiculos`
--
ALTER TABLE `registros_vehiculos`
  ADD CONSTRAINT `registros_vehiculos_ibfk_1` FOREIGN KEY (`id_vehiculo`) REFERENCES `vehiculos` (`id_vehiculo`),
  ADD CONSTRAINT `registros_vehiculos_ibfk_2` FOREIGN KEY (`registrado_por`) REFERENCES `usuarios` (`numero_documento`);

--
-- Filtros para la tabla `reportes_incidente`
--
ALTER TABLE `reportes_incidente`
  ADD CONSTRAINT `reportes_incidente_ibfk_1` FOREIGN KEY (`numero_documento`) REFERENCES `usuarios` (`numero_documento`),
  ADD CONSTRAINT `reportes_incidente_ibfk_2` FOREIGN KEY (`id_tipo`) REFERENCES `tipos_incidente` (`id_tipo`);

--
-- Filtros para la tabla `respuestas_incidentes`
--
ALTER TABLE `respuestas_incidentes`
  ADD CONSTRAINT `respuestas_incidentes_ibfk_1` FOREIGN KEY (`id_reporte`) REFERENCES `reportes_incidente` (`id_reporte`),
  ADD CONSTRAINT `respuestas_incidentes_ibfk_2` FOREIGN KEY (`numero_documento`) REFERENCES `usuarios` (`numero_documento`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);

--
-- Filtros para la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD CONSTRAINT `vehiculos_ibfk_1` FOREIGN KEY (`numero_documento`) REFERENCES `usuarios` (`numero_documento`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
