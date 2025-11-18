# Migración de Base de Datos MySQL a MongoDB

Este proyecto contiene la migración de la base de datos `electiva_3` de MySQL/MariaDB a MongoDB.

## Estructura de Archivos

- `electiva_3_mongodb.js` - Script principal para crear la base de datos y colecciones
- `mongodb_functions.js` - Funciones JavaScript equivalentes a los stored procedures
- `README_MONGODB.md` - Este archivo con las instrucciones

## Instalación y Configuración

### Prerrequisitos

1. MongoDB instalado (versión 4.4 o superior recomendada)
2. MongoDB Shell (`mongosh`) instalado

### Pasos para Crear la Base de Datos

1. **Iniciar MongoDB** (si no está corriendo):
   ```bash
   mongod
   ```

2. **Ejecutar el script de creación**:
   ```bash
   mongosh < electiva_3_mongodb.js
   ```
   
   O desde dentro de mongosh:
   ```javascript
   load('electiva_3_mongodb.js')
   ```

3. **Verificar la creación**:
   ```javascript
   use electiva_3
   show collections
   db.usuarios.countDocuments()
   ```

## Diferencias Principales entre MySQL y MongoDB

### 1. Estructura de Datos

| MySQL | MongoDB |
|-------|---------|
| Base de datos | Base de datos |
| Tabla | Colección |
| Fila/Registro | Documento |
| Columna | Campo |
| Foreign Key | Referencia (ObjectId o embebido) |
| AUTO_INCREMENT | ObjectId (automático) o campo manual |

### 2. Tipos de Datos

- **ENUM**: En MongoDB se almacenan como strings sin validación a nivel de BD
- **INT**: Se mantiene como Number
- **VARCHAR/TEXT**: Se mantiene como String
- **DATETIME/TIMESTAMP**: Se convierte a Date
- **BOOLEAN**: Se mantiene como Boolean

### 3. Relaciones

En lugar de Foreign Keys, MongoDB usa:
- **Referencias**: Almacenar ObjectId o identificador y hacer joins en la aplicación
- **Documentos embebidos**: Para relaciones uno-a-muchos donde el documento hijo siempre pertenece al padre

### 4. Stored Procedures y Funciones

MongoDB no tiene stored procedures nativos. Las opciones son:
- **Funciones JavaScript** almacenadas en `db.system.js` (limitadas)
- **Funciones en la aplicación** (recomendado) - Ver `mongodb_functions.js`
- **Agregaciones** para consultas complejas

### 5. Triggers

MongoDB no tiene triggers nativos. Se deben implementar:
- **Change Streams** (MongoDB 3.6+)
- **Middleware en la aplicación** (pre/post hooks)
- **Funciones en la aplicación** que se ejecuten después de operaciones

## Colecciones Creadas

1. **roles** - Roles del sistema
2. **tipos_incidente** - Tipos de incidentes
3. **usuarios** - Usuarios del sistema
4. **visitantes** - Visitantes registrados
5. **objetos_perdidos** - Objetos perdidos/encontrados
6. **imagenes_objeto** - Imágenes de objetos
7. **reportes_incidente** - Reportes de incidentes
8. **evidencias_incidente** - Evidencias de incidentes
9. **registros_acceso** - Registros de entrada/salida
10. **aprobaciones_reportes** - Aprobaciones de reportes
11. **notificaciones** - Notificaciones del sistema
12. **mensajes_objetos** - Mensajes sobre objetos
13. **comentarios_objetos** - Comentarios sobre objetos
14. **respuestas_incidentes** - Respuestas a incidentes
15. **vehiculos** - Vehículos registrados
16. **registros_vehiculos** - Registros de vehículos
17. **auditoria** - Registro de auditoría

## Índices Creados

Cada colección tiene índices apropiados para optimizar las consultas:
- Índices únicos en campos como `numero_documento`, `correo`, `placa`
- Índices en campos de búsqueda frecuente como `estado`, `tipo_reporte`, `fecha_hora`
- Índices compuestos donde sea necesario

## Uso de las Funciones

Las funciones equivalentes a stored procedures están en `mongodb_functions.js`. Para usarlas en Node.js:

```javascript
const { MongoClient } = require('mongodb');
const client = new MongoClient('mongodb://localhost:27017');

async function ejemplo() {
  await client.connect();
  const db = client.db('electiva_3');
  
  // Cargar funciones (deben implementarse en tu aplicación)
  const resultado = await sp_autenticar_usuario('1078458186', 'password123');
  console.log(resultado);
}
```

## Implementación de Triggers

Para implementar la funcionalidad de triggers (como notificaciones automáticas), puedes usar Change Streams:

```javascript
const changeStream = db.objetos_perdidos.watch();

changeStream.on('change', (change) => {
  if (change.operationType === 'insert') {
    // Crear notificaciones automáticamente
    // Similar al trigger tr_objetos_perdidos_after_insert
  }
  
  if (change.operationType === 'update') {
    // Actualizar notificaciones
    // Similar al trigger tr_objetos_perdidos_after_update
  }
});
```

## Migración de Datos Existentes

Si ya tienes datos en MySQL y quieres migrarlos:

1. Exportar datos de MySQL a JSON
2. Usar `mongoimport` o scripts personalizados
3. Ajustar referencias (IDs numéricos a ObjectIds si es necesario)

Ejemplo con mongoimport:
```bash
mongoimport --db electiva_3 --collection usuarios --file usuarios.json --jsonArray
```

## Notas Importantes

1. **Validación de Datos**: MongoDB no valida ENUMs automáticamente. Debes validar en la aplicación.

2. **Transacciones**: MongoDB soporta transacciones desde la versión 4.0, pero son más costosas que en SQL.

3. **Joins**: MongoDB no tiene JOINs nativos. Usa agregaciones (`$lookup`) o múltiples consultas.

4. **Consistencia**: MongoDB es eventualmente consistente por defecto. Para consistencia fuerte, usa `readConcern: 'majority'`.

5. **Seguridad**: Asegúrate de configurar autenticación y autorización en producción.

## Consultas de Ejemplo

### Obtener usuario con su rol
```javascript
db.usuarios.aggregate([
  {
    $lookup: {
      from: 'roles',
      localField: 'id_rol',
      foreignField: '_id',
      as: 'rol'
    }
  },
  { $unwind: '$rol' }
])
```

### Obtener objetos perdidos con imágenes
```javascript
db.objetos_perdidos.aggregate([
  {
    $lookup: {
      from: 'imagenes_objeto',
      localField: '_id',
      foreignField: 'id_objeto',
      as: 'imagenes'
    }
  }
])
```

### Contar notificaciones no leídas por usuario
```javascript
db.notificaciones.aggregate([
  { $match: { leida: 0 } },
  {
    $group: {
      _id: '$numero_documento',
      total: { $sum: 1 }
    }
  }
])
```

## Soporte

Para más información sobre MongoDB:
- [Documentación oficial de MongoDB](https://docs.mongodb.com/)
- [MongoDB University](https://university.mongodb.com/)

## Licencia

Este script es una migración de la base de datos original y mantiene la misma estructura lógica.

