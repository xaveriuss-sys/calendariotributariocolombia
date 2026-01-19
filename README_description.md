# Calendario Tributario Colombia 2026

## Descripción

Aplicación web LAMP Stack que permite a empresas colombianas generar calendarios tributarios personalizados para el año fiscal 2026. El usuario ingresa los datos de su empresa (NIT, ciudad, ingresos) y descarga un archivo `.ics` compatible con Outlook, Google Calendar y otros gestores de calendario.

## Estructura del Proyecto

```
CalendarioTributario/
├── public/              ← Document Root (configurar en Ploi.io)
│   ├── index.php        ← Punto de entrada principal
│   ├── install.php      ← Wizard de instalación
│   ├── generator.php    ← Generador de archivos ICS
│   └── styles.css       ← Estilos CSS
├── src/                 ← Código fuente (no accesible desde web)
│   ├── config.php       ← Configuración y funciones
│   └── database.php     ← Funciones de base de datos
├── storage/             ← Datos de configuración
│   └── config.json      ← Credenciales (generado por installer)
├── database.sql         ← Script SQL de referencia
└── .gitignore           ← Excluye config.json
```

## Instalación en Ploi.io

### 1. Crear Sitio en Ploi
1. Crear nuevo sitio para `calendariotributariocolombia.dataeficiencia.com`
2. Configurar **Document Root**: `/public`
3. Habilitar SSL (Let's Encrypt)

### 2. Subir Archivos
```bash
# Via Git
git clone <repo-url> .

# O via SFTP
# Subir toda la carpeta del proyecto
```

### 3. Ejecutar el Wizard de Instalación
1. Navegar a `https://calendariotributariocolombia.dataeficiencia.com/install.php`
2. Ingresar credenciales de MySQL
3. El wizard creará automáticamente:
   - La base de datos (si no existe)
   - Las tablas `tax_rules` y `tax_deadlines_2026`
   - El archivo `storage/config.json` con las credenciales
   - ~70 fechas tributarias para 2026

### 4. Permisos del Directorio Storage
```bash
chmod 755 storage/
chmod 644 storage/config.json
```

## Arquitectura

```
┌─────────────────────────────────────────────────────────────┐
│                      Frontend (HTML/CSS/JS)                  │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ public/index.php - Formulario de captura de datos       ││
│  │ public/styles.css - Design System Dataeficiencia        ││
│  │ JavaScript - Validación NIT colombiano                  ││
│  └─────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                       Backend (PHP 8.0+)                     │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ public/generator.php - Lógica de negocio + ICS          ││
│  │ src/config.php - Configuración + Validación NIT         ││
│  │ src/database.php - Conexión MySQL + Setup               ││
│  └─────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                      Base de Datos (MySQL)                   │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ tax_rules - Reglas tributarias (13 reglas)              ││
│  │ tax_deadlines_2026 - Fechas de vencimiento (70+ fechas) ││
│  └─────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────┘
```

## Lógica de Negocio

### IVA - Periodicidad
| Condición | Resultado |
|-----------|-----------|
| Ingresos > 92.000 UVT (~$4.581M) | IVA Bimestral (6 períodos) |
| Ingresos ≤ 92.000 UVT | IVA Cuatrimestral (3 períodos) |

### ICA Bogotá - Periodicidad
| Condición | Resultado |
|-----------|-----------|
| Impuesto Cargo > 391 UVT (~$19.4M) | ICA Bimestral (6 períodos) |
| Impuesto Cargo ≤ 391 UVT | ICA Anual (26 Feb 2027) |

## Obligaciones Incluidas

1. **Renta Personas Jurídicas** - 2 cuotas (Mayo/Julio) según último dígito NIT
2. **IVA** - Bimestral o Cuatrimestral según ingresos
3. **Retención en la Fuente** - Mensual (12 períodos)
4. **ICA Bogotá** - Bimestral o Anual según impuesto cargo
5. **ICA Medellín** - Bimestral (6 períodos)
6. **ICA Cali** - Bimestral (6 períodos)
7. **Obligaciones Laborales** - Cesantías, Primas, Reducción Jornada

## Valor UVT 2026

| Parámetro | Valor |
|-----------|-------|
| UVT 2026 (proyectado) | $49.799 COP |
| Umbral IVA Bimestral | 92.000 UVT = $4.581.508.000 |
| Umbral ICA Bogotá Bimestral | 391 UVT = $19.461.409 |

> ⚠️ **Importante**: El valor UVT es una proyección. Actualizar en `src/config.php` cuando la DIAN publique el valor oficial.

## Reinstalación

Si necesita reinstalar la aplicación:

```bash
# Eliminar el archivo de configuración
rm storage/config.json

# Acceder nuevamente a install.php
# El wizard detectará que no está instalado
```

---

*Desarrollado por Dataeficiencia - Enero 2026*
