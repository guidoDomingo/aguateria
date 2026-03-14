# Sistema SaaS para Aguaterías - Arquitectura de Base de Datos

## 🗂️ Estructura de Tablas (34 Tablas)

### 🏢 **MÓDULO SaaS - MULTIEMPRESA**

#### 1. empresas
```sql
id (PK)
nombre
ruc
direccion
telefono
email
logo
ciudad
pais
moneda
estado (activa, suspendida, inactiva)
fecha_registro
plan_id (FK)
created_at
updated_at
```

#### 2. planes
```sql
id (PK)
nombre (Basic, Pro, Premium)
precio_mensual
max_clientes
max_usuarios
max_cobradores
caracteristicas (JSON)
estado
created_at
updated_at
```

#### 3. suscripciones
```sql
id (PK)
empresa_id (FK)
plan_id (FK)
fecha_inicio
fecha_fin
precio
estado (activa, vencida, suspendida)
metodo_pago
created_at
updated_at
```

### 👥 **MÓDULO USUARIOS Y PERMISOS**

#### 4. users
```sql
id (PK)
empresa_id (FK)
name
email
email_verified_at
password
telefono
cedula
estado (activo, inactivo)
last_login_at
remember_token
created_at
updated_at
```

#### 5. roles
```sql
id (PK)
name
guard_name
description
empresa_id (FK) - null para roles globales
created_at
updated_at
```

#### 6. permissions
```sql
id (PK)
name
guard_name
description
module (clientes, facturas, pagos, reportes)
created_at
updated_at
```

#### 7. role_has_permissions
```sql
permission_id (FK)
role_id (FK)
```

#### 8. model_has_roles
```sql
role_id (FK)
model_type
model_id
```

### 🏘️ **MÓDULO GEOGRAFÍA**

#### 9. ciudades
```sql
id (PK)
nombre
departamento
pais
codigo_postal
created_at
updated_at
```

#### 10. barrios
```sql
id (PK)
empresa_id (FK)
ciudad_id (FK)
nombre
descripcion
created_at
updated_at
```

#### 11. zonas
```sql
id (PK)
empresa_id (FK)
barrio_id (FK)
nombre
descripcion
created_at
updated_at
```

### 👨‍💼 **MÓDULO COBRADORES**

#### 12. cobradores
```sql
id (PK)
empresa_id (FK)
user_id (FK) - opcional
nombre
cedula
telefono
email
direccion
zona_id (FK)
estado (activo, inactivo)
fecha_ingreso
comision_porcentaje
created_at
updated_at
```

### 💰 **MÓDULO TARIFAS**

#### 13. tarifas
```sql
id (PK)
empresa_id (FK)
nombre (Residencial, Comercial, Industrial)
monto_mensual
genera_mora (boolean)
monto_mora
dias_vencimiento
descripcion
estado (activa, inactiva)
created_at
updated_at
```

### 👥 **MÓDULO CLIENTES**

#### 14. clientes
```sql
id (PK)
empresa_id (FK)
codigo_cliente
nombre
apellido
cedula
ruc
telefono
email
direccion
barrio_id (FK)
zona_id (FK)
tarifa_id (FK)
cobrador_id (FK)
tipo_cliente (residencial, comercial, industrial)
fecha_alta
fecha_baja
estado (activo, suspendido, retirado, cortado)
observaciones
created_at
updated_at
```

#### 15. historial_clientes
```sql
id (PK)
cliente_id (FK)
campo_modificado
valor_anterior
valor_nuevo
usuario_id (FK)
fecha_cambio
motivo
created_at
```

### 🧾 **MÓDULO FACTURACIÓN**

#### 16. periodos_facturacion
```sql
id (PK)
empresa_id (FK)
año
mes
nombre (Enero 2024)
fecha_inicio
fecha_fin
estado (abierto, cerrado, facturado)
created_at
updated_at
```

#### 17. facturas
```sql
id (PK)
empresa_id (FK)
cliente_id (FK)
periodo_id (FK)
numero_factura
subtotal
mora
descuento
total
fecha_emision
fecha_vencimiento
estado (pendiente, pagado, vencido, anulado)
observaciones
created_at
updated_at
```

#### 18. factura_detalles
```sql
id (PK)
factura_id (FK)
concepto
cantidad
precio_unitario
subtotal
tipo (servicio, mora, reconexion, otros)
created_at
```

### 💳 **MÓDULO PAGOS**

#### 19. metodos_pago
```sql
id (PK)
empresa_id (FK)
nombre (Efectivo, Transferencia, QR, Tarjeta)
requiere_referencia (boolean)
estado (activo, inactivo)
created_at
updated_at
```

#### 20. pagos
```sql
id (PK)
empresa_id (FK)
cliente_id (FK)
factura_id (FK)
cobrador_id (FK) - opcional
numero_recibo
monto_pagado
metodo_pago_id (FK)
referencia
fecha_pago
observaciones
usuario_id (FK)
created_at
updated_at
```

#### 21. recibos
```sql
id (PK)
pago_id (FK)
numero_recibo
cliente_nombre
monto
fecha
periodo_pagado
metodo_pago
observaciones
created_at
```

### ✂️ **MÓDULO CORTES Y RECONEXIONES**

#### 22. cortes_servicio
```sql
id (PK)
empresa_id (FK)
cliente_id (FK)
motivo (mora, solicitud_cliente, mantenimiento)
fecha_corte
usuario_id (FK)
observaciones
estado (cortado, reconectado)
created_at
updated_at
```

#### 23. reconexiones
```sql
id (PK)
empresa_id (FK)
cliente_id (FK)
corte_id (FK)
fecha_reconexion
costo_reconexion (decimal)
usuario_id (FK)
observaciones
created_at
updated_at
```

### 📊 **MÓDULO REPORTES Y AUDITORÍA**

#### 24. logs_sistema
```sql
id (PK)
empresa_id (FK)
user_id (FK)
accion
modulo
descripcion
ip_address
user_agent
datos_anteriores (JSON)
datos_nuevos (JSON)
created_at
```

#### 25. auditorias
```sql
id (PK)
user_type
user_id
event
auditable_type
auditable_id
old_values (JSON)
new_values (JSON)
url
ip_address
user_agent
tags
created_at
updated_at
```

### 📧 **MÓDULO NOTIFICACIONES**

#### 26. notificaciones
```sql
id (PK)
empresa_id (FK)
cliente_id (FK) - opcional
tipo (pago_vencido, corte_programado, reconexion)
titulo
mensaje
canal (email, sms, whatsapp, sistema)
estado (pendiente, enviado, fallido)
fecha_programada
fecha_enviado
intentos
created_at
updated_at
```

#### 27. avisos_mora
```sql
id (PK)
empresa_id (FK)
cliente_id (FK)
factura_id (FK)
dias_vencido
monto_deuda
fecha_aviso
tipo_aviso (primer_aviso, segundo_aviso, ultimo_aviso)
enviado (boolean)
metodo_envio
created_at
```

### 📈 **MÓDULO CONFIGURACIÓN**

#### 28. configuraciones
```sql
id (PK)
empresa_id (FK)
clave
valor
tipo (string, integer, boolean, json)
descripcion
categoria (general, facturacion, notificaciones)
created_at
updated_at
```

#### 29. numeracion_comprobantes
```sql
id (PK)
empresa_id (FK)
tipo_comprobante (factura, recibo, nota_credito)
serie
numero_actual
numero_desde
numero_hasta
estado (activo, inactivo)
created_at
updated_at
```

### 📅 **MÓDULO PROGRAMACIÓN**

#### 30. tareas_programadas
```sql
id (PK)
empresa_id (FK)
nombre
comando
descripcion
periodicidad (diaria, semanal, mensual)
hora_ejecucion
activo (boolean)
ultima_ejecucion
proxima_ejecucion
created_at
updated_at
```

#### 31. ejecuciones_tareas
```sql
id (PK)
tarea_id (FK)
fecha_inicio
fecha_fin
estado (ejecutando, completado, fallido)
resultado
mensaje_error
created_at
```

### 🎯 **MÓDULO METAS Y ESTADÍSTICAS**

#### 32. metas_cobranza
```sql
id (PK)
empresa_id (FK)
cobrador_id (FK)
año
mes
meta_monto
monto_cobrado
meta_clientes
clientes_cobrados
porcentaje_cumplimiento
created_at
updated_at
```

#### 33. estadisticas_mensuales
```sql
id (PK)
empresa_id (FK)
año
mes
total_clientes
clientes_activos
clientes_morosos
ingresos_mes
gastos_mes
utilidad_mes
created_at
updated_at
```

### 💾 **MÓDULO RESPALDOS**

#### 34. respaldos_bd
```sql
id (PK)
empresa_id (FK)
nombre_archivo
ruta_archivo
tamaño_mb
tipo (automatico, manual)
fecha_respaldo
estado (completado, fallido)
observaciones
created_at
```

## 🔗 Relaciones Principales

### Empresa como Tenant Principal
- `empresa_id` en casi todas las tablas para aislamiento
- Un usuario pertenece a una empresa
- Cada empresa tiene sus propios clientes, facturas, pagos

### Cliente en el Centro
```
Cliente -> Tarifa (many-to-one)
Cliente -> Barrio/Zona (many-to-one)  
Cliente -> Cobrador (many-to-one)
Cliente -> Facturas (one-to-many)
Cliente -> Pagos (one-to-many)
Cliente -> Cortes (one-to-many)
```

### Facturación Completa
```
Periodo -> Facturas -> Detalles
Factura -> Pagos -> Recibos
```

### Auditoría Total
- Logs en cada acción importante
- Historial de cambios en clientes
- Auditoría completa con Laravel Auditing

---

## 📋 Índices Recomendados

```sql
-- Rendimiento para queries principales
INDEX idx_empresa_cliente (empresa_id, cliente_id)
INDEX idx_factura_estado (empresa_id, estado)
INDEX idx_cliente_estado (empresa_id, estado)
INDEX idx_pago_fecha (empresa_id, fecha_pago)
INDEX idx_barrio_zona (barrio_id, zona_id)
```

Esta arquitectura está diseñada para:
✅ **Escalabilidad** - Soporta miles de clientes por empresa
✅ **Multi-tenancy** - Aislamiento completo entre empresas  
✅ **Auditoría** - Registro completo de todas las acciones
✅ **Reportes** - Datos estructurados para analytics
✅ **Automatización** - Tareas programadas y notificaciones