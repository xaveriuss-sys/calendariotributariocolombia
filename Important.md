# Información Importante - Calendario Tributario 2026

## ⚠️ Advertencias Críticas

### Valor UVT
> **El valor UVT 2026 ($49.799) es una proyección.** 
> 
> El valor oficial será publicado por la DIAN a finales de 2025. Cuando se publique, 
> actualizar la constante `UVT_2026` en `config.php`.

### Fechas Tributarias
> **Las fechas en `database.sql` son basadas en el calendario DIAN proyectado.**
>
> Verificar con el calendario oficial cuando sea publicado. Las fechas pueden cambiar 
> si caen en días festivos.

---

## 🔒 No Modificar

### Algoritmo de Validación NIT
El algoritmo de validación del dígito de verificación en `config.php` y `index.html` 
implementa el estándar colombiano. **No modificar los pesos ni la fórmula.**

```php
$pesos = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71];
```

### Formato ICS
El formato del archivo ICS debe mantener:
- Line endings CRLF (`\r\n`)
- Encoding UTF-8
- Headers obligatorios (VERSION, PRODID, CALSCALE)

---

## 📦 Despliegue en Ploi.io

### Configuración del Sitio
1. Crear nuevo sitio: `calendariotributariocolombia.dataeficiencia.com`
2. **Document Root**: `/public` ← ⚠️ CRÍTICO
3. PHP Version: 8.0+
4. SSL: Habilitar Let's Encrypt

### Primer Despliegue
1. Subir archivos del proyecto
2. Navegar a `https://calendariotributariocolombia.dataeficiencia.com/`
3. El sistema redirigirá automáticamente a `install.php`
4. Completar el wizard de 3 pasos

### Permisos
```bash
chmod 755 storage/
chmod 644 storage/config.json  # Después de instalación
```

---

## 🗓️ Actualización Anual

Para actualizar al año 2027:

1. Crear nueva tabla `tax_deadlines_2027`
2. Actualizar el valor UVT en `config.php`
3. Modificar las consultas en `generator.php` para usar la tabla nueva
4. Actualizar el título y metadatos en `index.html`

---

## 🐛 Problemas Conocidos

### Emojis en ICS
Algunos clientes de calendario antiguos pueden no mostrar los emojis (📋, 💰, 🏛️, 👥) 
en los títulos de eventos. Son decorativos y no afectan la funcionalidad.

### Zona Horaria
El calendario usa `America/Bogota` (UTC-5). Los eventos son de día completo 
(VALUE=DATE) por lo que la zona horaria no afecta la visualización.

---

*Última actualización: 19 de Enero de 2026*
