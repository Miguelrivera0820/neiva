# 📊 Actualización del Dashboard - Integración de Datos en Tiempo Real

## 🎯 Resumen Ejecutivo

Se ha modificado el **dashboard.php** para obtener información directamente de **mis_asignaciones.php** y **mis_revisiones.php**, añadiendo un **contador de trámites pendientes por entregar** según el rol y usuario.

---

## ✨ Cambios Implementados

### 1️⃣ Nuevo Archivo: `obtener_datos_dashboard.php`

**Ubicación:** `Arbimaps/vistas/tramites/obtener_datos_dashboard.php`

**¿Qué hace?**
- Extrae automáticamente la cédula del usuario y rol de la sesión
- Consulta la base de datos para obtener:
  - Datos de **mis_asignaciones.php** (asignaciones del usuario)
  - Datos de **mis_revisiones.php** (revisiones asignadas al usuario)
- Mapea el rol del usuario a un campo de estado específico
- Cuenta automáticamente:
  - ✅ **Pendientes por entregar** (PENDIENTE)
  - ✅ **Entregados** (ENTREGADO/DEVUELTO)
  - ✅ **Vencidos** (según fecha_limite)

### 2️⃣ Modificación: `dashboard.php`

**Cambios:**
```php
// Se agregó al inicio del archivo:
<?php
require_once 'obtener_datos_dashboard.php';
?>
```

**Nueva Tarjeta Agregada:**
- **Nombre:** "Pendientes por Entregar"
- **Color:** Rojo (#FF6B6B)
- **Ícono:** Triángulo de advertencia ⚠️
- **Información mostrada:** Número total de trámites que el usuario aún no ha entregado

---

## 📈 Variables Disponibles en el Dashboard

Después de incluir `obtener_datos_dashboard.php`, estas variables están disponibles:

| Variable | Descripción | Ejemplo |
|----------|-------------|---------|
| `$total_rad` | Total de trámites radicados | 150 |
| `$total_asignaciones` | Total de asignaciones del usuario | 45 |
| `$total_pendientes` | **⭐ Trámites sin entregar** | 12 |
| `$total_entregados` | Trámites ya entregados | 33 |
| `$total_vencidas` | Trámites vencidos | 8 |
| `$tramites` | Array con todos los trámites | [...] |
| `$conteos` | Distribución por responsable | [...] |

---

## 🔄 Flujo de Datos

```
┌────────────────────────────────────────────────────┐
│         USUARIO ACCEDE AL DASHBOARD                │
│   (URL: index.php?page=tramites/dashboard)        │
└────────────────────┬─────────────────────────────┘
                     ↓
┌────────────────────────────────────────────────────┐
│  dashboard.php                                     │
│  require_once 'obtener_datos_dashboard.php'       │
└────────────────────┬─────────────────────────────┘
                     ↓
┌────────────────────────────────────────────────────┐
│  obtener_datos_dashboard.php                       │
│                                                    │
│  1. Lee $_SESSION['cedula_usuario']               │
│  2. Lee $_SESSION['rol_usuario']                  │
│  3. Consulta asignacion_tramite + historial       │
│  4. Consulta entrega_asignacion + historial       │
│  5. Mapea rol → campo de estado                   │
│  6. Cuenta PENDIENTE vs ENTREGADO/DEVUELTO        │
│  7. Retorna variables al dashboard                │
└────────────────────┬─────────────────────────────┘
                     ↓
┌────────────────────────────────────────────────────┐
│  Dashboard muestra:                                │
│  ✓ Total de trámites radicados                   │
│  ✓ Trámites asignados (porcentaje)               │
│  ✓ ⭐ PENDIENTES POR ENTREGAR (NEW!)             │
│  ✓ Trámites vencidos                             │
│  ✓ Tabla de distribución de trabajo              │
│  ✓ Gráficas de mutaciones                        │
└────────────────────────────────────────────────────┘
```

---

## 🗂️ Archivos Creados/Modificados

| Archivo | Estado | Descripción |
|---------|--------|-------------|
| `Arbimaps/vistas/tramites/obtener_datos_dashboard.php` | ✅ CREADO | Extrae datos de mis_asignaciones y mis_revisiones |
| `Arbimaps/vistas/tramites/dashboard.php` | 🔄 MODIFICADO | Incluye el nuevo archivo y la tarjeta de pendientes |
| `Arbimaps/vistas/tramites/prueba_dashboard.php` | ✅ CREADO | Archivo para pruebas (OPCIONAL) |
| `Arbimaps/CAMBIOS_DASHBOARD_v1.md` | ✅ CREADO | Documentación técnica detallada |

---

## 🎨 Tarjeta Visual: "Pendientes por Entregar"

```
╔══════════════════════════════════════╗
║  ⚠️                       ↓ [12]     ║  (Rojo #FF6B6B)
║                                      ║
║  PENDIENTES POR ENTREGAR             ║
║  12                      [Ver]       ║
╚══════════════════════════════════════╝
```

- Se posiciona después de "Trámites Asignados"
- El número se actualiza automáticamente según:
  - El usuario logueado
  - Su rol
  - Los estados en las tablas de historial

---

## 🔍 ¿Cómo Funciona el Contador?

### Paso 1: Identificar el rol y campo de estado
```php
$rol_usuario = 'editor'
// Se mapea a: $campo_estado = 'est_edicion'
```

### Paso 2: Revisar historial_asignacion
```php
WHERE est_edicion = 'PENDIENTE' → Suma +1 a $total_pendientes
WHERE est_edicion = 'ENTREGADO' → Suma +1 a $total_entregados
```

### Paso 3: Revisar historial_revision
```php
WHERE est_edicion = 'PENDIENTE' → Suma +1 a $total_pendientes
WHERE est_edicion = 'ENTREGADO' → Suma +1 a $total_entregados
```

### Paso 4: Mostrar en la tarjeta
```php
<?php echo $total_pendientes; ?> // Muestra el número
```

---

## 📋 Mapa Completo de Roles

El sistema reconoce automáticamente estos roles y sus campos de estado:

```
COORDINACION_TECNICA  → est_conservacion
LIDER_JURIDICO        → est_lider_juridico
CONTROL_CALIDAD       → est_control_calidad
LIDER_ECONOMICO       → est_lider_economico
CONSOLIDACION         → est_consolidacion
EDITOR / EDICION      → est_edicion
AVALUOS               → est_avaluos
RECONOCEDOR           → est_reconocimiento
DIRECTOR              → est_director
VENTANILLA_CATASTRAL  → est_ventanilla
PROCEDENCIA_JURIDICA  → est_procedencia
```

---

## ✅ Requisitos para Funcionar

1. ✅ Usuario debe estar autenticado (`$_SESSION['cedula_usuario']` debe existir)
2. ✅ Usuario debe tener un rol válido (`$_SESSION['rol_usuario']` debe existir)
3. ✅ Conexión a BD debe estar disponible (`$mysqli`)
4. ✅ Tablas necesarias:
   - `asignacion_tramite`
   - `entrega_asignacion`
   - `tramite_radicacion`
   - `historial_asignacion`
   - `historial_revision`
   - `estados_tramite`

---

## 🧪 Pruebas Recomendadas

### Test 1: Verificar contador de pendientes
```
1. Crear un trámite en estado PENDIENTE
2. Verificar que aparezca en el contador
3. Cambiar estado a ENTREGADO
4. Verificar que desaparezca del contador
```

### Test 2: Validar filtrado por rol
```
1. Loguear con usuario rol "editor"
2. Verificar que solo cuente pendientes en est_edicion
3. Loguear con usuario rol "lider_juridico"
4. Verificar que solo cuente pendientes en est_lider_juridico
```

### Test 3: Verificar datos combinados
```
1. Verificar que se cuenten asignaciones Y revisiones
2. Total = pendientes_asignaciones + pendientes_revisiones
```

---

## 🚀 Cómo Usar

### Para el usuario final:
1. Inicia sesión en el dashboard
2. Verás la nueva tarjeta **"Pendientes por Entregar"**
3. El número se actualiza automáticamente
4. Haz clic en "Ver" para ir a tus asignaciones

### Para el desarrollador:
```php
// En cualquier archivo que hereda del dashboard:
<?php
require_once 'vistas/tramites/obtener_datos_dashboard.php';

echo "Tengo " . $total_pendientes . " trámites por entregar";
echo "Y " . $total_entregados . " ya entregados";
echo "De un total de " . $total_tramites . " trámites";
?>
```

---

## 📝 Notas Importantes

⚠️ **El archivo `obtener_datos_dashboard.php` debe incluirse AL INICIO del dashboard.php**
- Esto asegura que las variables estén disponibles para toda la página

⚠️ **No incluir en otras vistas sin verificar antes**
- El archivo asume que `$_SESSION` y `$mysqli` están configuradas

⚠️ **Los estados deben ser exactamente: PENDIENTE, ENTREGADO, DEVUELTO**
- El código es sensible a mayúsculas/minúsculas

---

## 🔧 Soporte Técnico

Si el contador no aparece:
1. Verifica que el usuario esté autenticado
2. Verifica que tenga un rol válido (revisar base de datos)
3. Verifica que existan registros en historial_asignacion o historial_revision
4. Revisa el archivo `prueba_dashboard.php` para depuración

---

## 📞 Contacto / Cambios Futuros

Si necesitas:
- Agregar más roles ➜ Edita el mapa en `obtener_datos_dashboard.php`
- Cambiar colores ➜ Edita el color #FF6B6B en `dashboard.php`
- Modificar el contador ➜ Edita la lógica de conteo en `obtener_datos_dashboard.php`

---

**Versión:** 1.0  
**Fecha:** 28 de enero de 2026  
**Estado:** ✅ Producción
