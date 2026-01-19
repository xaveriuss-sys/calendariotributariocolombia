# Sesiones de Trabajo - Calendario Tributario 2026

## Sesión 1 - 19 de Enero de 2026

### Participantes
- Desarrollador Backend (Asistente AI)

### Objetivos
- [x] Crear la estructura inicial del proyecto
- [x] Implementar base de datos MySQL con fechas tributarias 2026
- [x] Desarrollar formulario frontend con validación de NIT
- [x] Implementar lógica de negocio para periodicidades
- [x] Generar archivo ICS con alarmas
- [x] Crear documentación del proyecto
- [x] Reestructurar para producción con /public
- [x] Crear wizard de instalación automática

### Estructura Final del Proyecto
```
CalendarioTributario/
├── public/              ← Document Root para Ploi.io
│   ├── index.php        ← Punto de entrada (redirige a install si no configurado)
│   ├── install.php      ← Wizard de instalación (3 pasos)
│   ├── generator.php    ← Generador ICS
│   └── styles.css       ← Estilos CSS
├── src/                 ← Código fuente (no accesible desde web)
│   ├── config.php       ← Configuración + Funciones NIT
│   └── database.php     ← Funciones MySQL + SQL embebido
├── storage/             ← Configuración generada
│   └── config.json      ← Credenciales (creado por wizard)
├── .gitignore           ← Excluye config.json
└── README_description.md
```

### Características del Wizard de Instalación
1. **Paso 1**: Solicita credenciales MySQL (host, usuario, contraseña, nombre BD)
2. **Paso 2**: Muestra resumen y confirma creación de tablas
3. **Paso 3**: Confirmación de instalación exitosa

El wizard:
- Crea la base de datos automáticamente si no existe
- Instala las tablas `tax_rules` y `tax_deadlines_2026`
- Inserta ~70 fechas tributarias para 2026
- Guarda las credenciales en `storage/config.json`

### Notas Técnicas
- Valor UVT 2026 proyectado: $49.799 COP
- Umbral IVA bimestral: 92.000 UVT (~$4.581M)
- Umbral ICA Bogotá bimestral: 391 UVT (~$19.4M)

### Próximos Pasos
- Desplegar en calendariotributariocolombia.dataeficiencia.com
- Configurar Document Root como /public en Ploi.io
- Ejecutar wizard de instalación en producción
