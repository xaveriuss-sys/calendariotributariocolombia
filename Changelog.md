# Changelog

## [1.1.0] - 2026-01-20
### Agregado
- **Arquitectura de Archivos Estáticos:** Los calendarios ahora se guardan físicamente en `public/calendarios/empresa_{NIT}_{AÑO}.ics` para mejorar la compatibilidad y permitir caché.
- **Soporte Google Calendar:** Nueva integración mediante URL pública directa y botón "Suscribirse" compatible.
- **Envío por Correo:** Nueva funcionalidad (`send_email.php`) para enviar el calendario generado al email del usuario.
- **Configuración de Bots:** Archivo `.htaccess` específico para permitir el indexado correcto por parte de los bots de Google Calendar.
- **Cache Busting:** Implementación de timestamps en URLs para forzar la actualización de calendarios en clientes externos.

### Seguridad
- **Eliminado:** Se eliminó el script `reset.php` del repositorio y servidor tras su uso en producción.
- **Permisos:** Se endurecieron los permisos de generación de archivos a `0644` explícito.

### Corregido
- Solucionado error 500 en `generator.php` debido a bloques de código incompletos.
- Corregida la validación de URLs en `result.php`.

 - Calendario Tributario 2026

Todos los cambios notables en este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/).

---

## [1.0.0] - 2026-01-19

### Añadido
- **Base de Datos MySQL**
  - Tabla `tax_rules` con 13 reglas tributarias (Nacional + Municipal + Laboral)
  - Tabla `tax_deadlines_2026` con 70+ fechas de vencimiento
  - Índices optimizados para consultas por NIT, fecha y código

- **Frontend**
  - Formulario HTML5 con validación del dígito de verificación del NIT
  - Campo condicional de ICA para Bogotá
  - Formateo de moneda en tiempo real
  - Diseño responsive siguiendo Design System Dataeficiencia
  - Integración con SweetAlert2 para notificaciones

- **Backend PHP**
  - Conexión PDO a MySQL con manejo de errores
  - Lógica de periodicidad IVA (bimestral/cuatrimestral según 92.000 UVT)
  - Lógica de periodicidad ICA Bogotá (bimestral/anual según 391 UVT)
  - Generación de archivo ICS con formato VCALENDAR estándar
  - Alarmas configuradas 2 días y 1 día antes de cada vencimiento
  - Categorización de eventos por tipo de impuesto

- **Obligaciones Tributarias Incluidas**
  - Renta Personas Jurídicas (2 cuotas)
  - IVA Bimestral y Cuatrimestral
  - Retención en la Fuente (Mensual)
  - ICA Bogotá (Bimestral y Anual)
  - ICA Medellín (Bimestral)
  - ICA Cali (Bimestral)
  - Obligaciones Laborales (Cesantías, Primas, Reducción Jornada)

- **Documentación**
  - README_description.md con arquitectura y guía de instalación
  - Sessions.md para historial de trabajo
  - Important.md con notas críticas
  - Changelog.md (este archivo)

### Configuración
- Valor UVT 2026: $49.799 COP (proyectado)
- Umbral IVA Bimestral: 92.000 UVT ($4.581.508.000)
- Umbral ICA Bogotá Bimestral: 391 UVT ($19.461.409)

---

## [Próximas Versiones]

### Planificado
- [ ] Agregar más ciudades con calendario ICA
- [ ] Opción de seleccionar tipos de impuestos específicos
- [ ] Versión para API REST
- [ ] Panel administrativo para actualizar fechas
