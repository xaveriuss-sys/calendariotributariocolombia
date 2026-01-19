# XML-WO Design System & Style Guide
**Familia de Aplicaciones: Dataeficiencia SaaS**

Este documento define el look and feel detallado de la aplicación XML to World Office, sirviendo como referencia para desarrollar sistemas consistentes dentro de la misma familia de aplicaciones.

---

## 1. Filosofía de Diseño

### Principios Core
- **Enterprise ERP Style**: Diseño profesional y corporativo
- **Eficiencia visual**: Interfaz compacta que maximiza el espacio útil
- **Minimalismo funcional**: Elementos con propósito claro, sin ornamentos innecesarios
- **Consistencia**: Patrones repetibles en toda la aplicación

### Características Clave
- Bordes sutiles (radius mínimo: 2-4px)
- Sombras discretas para profundidad
- Transiciones rápidas (150ms)
- Iconografía Material Icons
- Fuente moderna y legible (Inter)

---

## 2. Paleta de Colores

### Colores Base

| Variable | Valor Hex | Uso |
|----------|-----------|-----|
| `--header-bg` | `#1e293b` | Fondo del header/navegación principal |
| `--header-text` | `#94a3b8` | Texto secundario en header |
| `--header-text-active` | `#ffffff` | Texto activo/destacado en header |
| `--bg-main` | `#f1f5f9` | Fondo principal de la aplicación |
| `--bg-card` | `#ffffff` | Fondo de tarjetas y paneles |

### Colores de Texto

| Variable | Valor Hex | Uso |
|----------|-----------|-----|
| `--text-primary` | `#1e293b` | Texto principal (títulos, contenido) |
| `--text-secondary` | `#64748b` | Texto secundario (labels, descripciones) |
| `--text-muted` | `#94a3b8` | Texto atenuado (placeholders, notas) |

### Colores de Acento (Brand)

| Variable | Valor Hex | Uso |
|----------|-----------|-----|
| `--accent-primary` | `#0ea5e9` | **Color primario de marca** (Sky Blue) |
| `--accent-hover` | `#0284c7` | Estado hover de elementos primarios |
| `--accent-light` | `#e0f2fe` | Fondos de elementos primarios (light) |

### Colores Semánticos

| Estado | Color | Light | Uso |
|--------|-------|-------|-----|
| Success | `#22c55e` | `#dcfce7` | Operaciones exitosas, confirmaciones |
| Warning | `#f59e0b` | `#fef3c7` | Alertas, estados pendientes |
| Danger | `#ef4444` | `#fee2e2` | Errores, elementos requeridos |

### Bordes
- `--border-color`: `#e2e8f0` — Bordes sutiles de cards, inputs, separadores

---

## 3. Tipografía

### Fuente Principal
```css
font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
```

**Google Fonts CDN:**
```html
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
```

### Escala Tipográfica

| Elemento | Tamaño | Peso | Uso |
|----------|--------|------|-----|
| **Body Base** | 13px | 400 | Texto general, contenido |
| **Labels** | 11-12px | 500 | Labels de formularios, etiquetas |
| **Page Title** | 16px | 600 | Títulos de página |
| **Card Title** | 13px | 600 | Títulos de tarjetas |
| **Auth Heading (h1)** | 20-22px | 600 | Títulos en páginas de auth |
| **Auth Subtitle** | 13px | 400 | Subtítulos en páginas de auth |
| **Table Headers** | 10px | 600 | Encabezados de tabla (uppercase) |
| **Table Cells** | 11px | 400 | Contenido de celdas |
| **Stat Value** | 18px | 700 | Valores en tarjetas de estadísticas |
| **Stat Label** | 11px | 400 | Labels de estadísticas |

### Estilos de Texto
```css
body {
    line-height: 1.4;
    -webkit-font-smoothing: antialiased;
}

/* Letter spacing negativo para títulos */
.heading {
    letter-spacing: -0.3px;
}

/* Uppercase para labels de tabla */
.table-header {
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
```

---

## 4. Espaciado y Dimensiones

### Variables de Espaciado

| Tamaño | Uso |
|--------|-----|
| 4px | Gaps mínimos, separación de iconos |
| 6-8px | Padding de botones pequeños, gaps |
| 10-12px | Padding de cards, inputs |
| 14-16px | Padding principal de contenido |
| 20-24px | Separación entre secciones |
| 32-40px | Padding de paneles de autenticación |

### Dimensiones Fijas
- **Header Height**: `52px`
- **Logo Icon**: 28px × 28px
- **Nav Avatar**: 28px × 28px
- **Stat Icon Container**: 36px × 36px
- **Material Icons (nav)**: 18px
- **Material Icons (stat)**: 20px

### Border Radius

| Tamaño | Variable | Uso |
|--------|----------|-----|
| 2px | `--radius-sm` | Botones, badges, inputs |
| 4px | `--radius-md` | Cards, dropzones, paneles |
| 8px | — | Modales (excepcional) |

---

## 5. Sombras

### Variables CSS
```css
--shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
--shadow-md: 0 2px 4px -1px rgb(0 0 0 / 0.1);
```

### Aplicaciones Específicas
```css
/* Auth Container (elevación dramática) */
box-shadow: 0 4px 24px rgba(0, 0, 0, 0.3);

/* Modales */
box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1),
            0 10px 10px -5px rgba(0, 0, 0, 0.04);
```

---

## 6. Transiciones

### Transición Estándar
```css
--transition: 150ms ease;

/* Aplicación */
transition: all var(--transition);
transition: background var(--transition);
transition: border-color var(--transition);
```

---

## 7. Iconografía

### Material Icons
```html
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
```

### Uso en CSS
```css
.nav-item .material-icons { font-size: 18px; }
.btn .material-icons { font-size: 16px; }
.btn-sm .material-icons { font-size: 14px; }
.stat-icon .material-icons { font-size: 20px; }
```

### Iconos Frecuentes

| Contexto | Iconos |
|----------|--------|
| **Navegación** | dashboard, business, admin_panel_settings, help_outline, logout |
| **Acciones** | upload_file, download, check_circle, close, arrow_forward |
| **Estados** | sync (loading), error, warning, info |
| **Funciones** | auto_awesome, psychology, folder_zip, credit_card |
| **Formularios** | email, lock, person |

---

## 8. Componentes UI

### 8.1 Navegación Superior (Top Nav)

```css
.top-nav {
    height: 52px;
    background: #1e293b;
    position: sticky;
    top: 0;
    z-index: 100;
}
```

**Estructura:**
- Logo + Brand Name (izquierda)
- Menú de navegación (centro)
- Créditos + Usuario + Logout (derecha)

**Estados de Nav Items:**
- Default: `color: #94a3b8`
- Hover: `background: rgba(255, 255, 255, 0.1); color: #ffffff`
- Active: `background: rgba(14, 165, 233, 0.2); color: #0ea5e9`

---

### 8.2 Tarjetas (Cards)

```css
.card {
    background: #ffffff;
    border-radius: 4px;
    border: 1px solid #e2e8f0;
}

.card-header {
    padding: 10px 14px;
    border-bottom: 1px solid #e2e8f0;
}

.card-body {
    padding: 14px;
}
```

---

### 8.3 Botones

**Primario:**
```css
.btn-primary {
    background: #0ea5e9;
    color: white;
    padding: 6px 12px;
    border-radius: 2px;
    font-size: 12px;
    font-weight: 500;
}
.btn-primary:hover { background: #0284c7; }
```

**Secundario:**
```css
.btn-secondary {
    background: #f1f5f9;
    color: #1e293b;
    border: 1px solid #e2e8f0;
}
.btn-secondary:hover { background: #e2e8f0; }
```

**Danger:**
```css
.btn-danger {
    background: #ef4444;
    color: white;
}
.btn-danger:hover { background: #dc2626; }
```

**Tamaños:**
- Default: `padding: 6px 12px; font-size: 12px`
- Small: `padding: 4px 8px; font-size: 11px`
- Large: `padding: 12px 24px; font-size: 1rem; font-weight: 600`

---

### 8.4 Formularios

**Inputs:**
```css
.form-input {
    width: 100%;
    padding: 8-10px 10-12px;
    border: 1px solid #e2e8f0;
    border-radius: 2-4px;
    font-size: 13px;
    background: white;
}

.form-input:focus {
    outline: none;
    border-color: #0ea5e9;
    box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.1); /* opcional */
}
```

**Labels:**
```css
.form-label {
    display: block;
    font-size: 11-12px;
    font-weight: 500;
    color: #1e293b;
    margin-bottom: 4-6px;
}
```

**Input con Icono:**
```css
.input-with-icon {
    position: relative;
}
.input-with-icon .material-icons {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 18px;
    color: #94a3b8;
}
.input-with-icon .form-input {
    padding-left: 38px;
}
```

---

### 8.5 Tablas

```css
.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11px;
}

.data-table th {
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    color: #64748b;
    background: #f8fafc;
    padding: 6px 8px;
    position: sticky;
    top: 0;
}

.data-table td {
    padding: 6px 8px;
    border-bottom: 1px solid #e2e8f0;
}

.data-table tbody tr:hover {
    background: #f8fafc;
}
```

---

### 8.6 Badges de Estado

```css
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 6px;
    border-radius: 2px;
    font-size: 10px;
    font-weight: 500;
}

/* Variantes */
.status-badge.success { background: #dcfce7; color: #22c55e; }
.status-badge.warning { background: #fef3c7; color: #f59e0b; }
.status-badge.danger { background: #fee2e2; color: #ef4444; }
```

---

### 8.7 Stat Cards

```css
.stat-card {
    background: white;
    border-radius: 4px;
    border: 1px solid #e2e8f0;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.stat-icon {
    width: 36px;
    height: 36px;
    border-radius: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-icon.primary { background: #e0f2fe; color: #0ea5e9; }
.stat-icon.success { background: #dcfce7; color: #22c55e; }
.stat-icon.warning { background: #fef3c7; color: #f59e0b; }

.stat-value { font-size: 18px; font-weight: 700; color: #1e293b; }
.stat-label { font-size: 11px; color: #64748b; }
```

---

### 8.8 Modales

```css
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 8px;
    padding: 32px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1),
                0 10px 10px -5px rgba(0, 0, 0, 0.04);
}
```

**Animación de Entrada:**
```css
.animate-pop {
    animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

@keyframes popIn {
    from { opacity: 0; transform: scale(0.95) translateY(10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
```

---

### 8.9 Dropzone

```css
.dropzone {
    border: 2px dashed #e2e8f0;
    border-radius: 4px;
    padding: 32px 16px;
    text-align: center;
    background: white;
    cursor: pointer;
}

.dropzone:hover,
.dropzone.dragover {
    border-color: #0ea5e9;
    background: #e0f2fe;
}

.dropzone .material-icons {
    font-size: 36px;
    color: #94a3b8;
}

.dropzone:hover .material-icons {
    color: #0ea5e9;
}
```

---

## 9. Página de Login

### Estructura
La página de login implementa un diseño **Split Panel**:

| Panel Izquierdo | Panel Derecho |
|-----------------|---------------|
| Branding + Features | Formulario de Login |
| Fondo oscuro (`#1e293b`) | Fondo claro (`#f8fafc`) |
| 50% del ancho | 50% del ancho |

### Layout General

```css
.auth-body {
    min-height: 100vh;
    background: #1e293b;
}

.auth-wrapper {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}

.auth-container {
    display: flex;
    max-width: 900px;
    width: 100%;
    background: white;
    border-radius: 4px;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.3);
    overflow: hidden;
}
```

### Panel de Branding (Izquierda)

```css
.auth-brand {
    flex: 1;
    background: #1e293b;
    color: white;
    padding: 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.auth-brand h1 {
    font-size: 22px;
    font-weight: 600;
    margin-bottom: 12px;
    letter-spacing: -0.3px;
}

.auth-brand p {
    font-size: 13px;
    color: #94a3b8;
    line-height: 1.5;
}
```

**Features List:**
```css
.brand-features {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.feature {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    color: #94a3b8;
}

.feature .material-icons {
    font-size: 18px;
    color: #64748b;
}
```

### Panel de Formulario (Derecha)

```css
.auth-form-panel {
    flex: 1;
    padding: 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    background: #f8fafc;
}

.auth-card {
    max-width: 320px;
    margin: 0 auto;
    width: 100%;
}

.auth-card .card-header {
    text-align: center;
    margin-bottom: 24px;
    padding: 0;
    border: none;
}

.auth-card .card-header h2 {
    font-size: 20px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 4px;
}

.auth-card .card-header p {
    color: #64748b;
    font-size: 13px;
}
```

### Botón de Registro (CTA Especial)

```css
.btn-register {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 20px;
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    color: white;
    font-size: 14px;
    font-weight: 600;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(34, 197, 94, 0.3);
}

.btn-register:hover {
    background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.4);
    transform: translateY(-1px);
}
```

### Divider

```css
.auth-divider {
    display: flex;
    align-items: center;
    margin: 20px 0;
    color: #94a3b8;
    font-size: 12px;
}

.auth-divider::before,
.auth-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #e2e8f0;
}

.auth-divider span {
    padding: 0 12px;
}
```

### Loading State (Spinning)

```css
.spinning {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
```

### Responsive (Mobile)

```css
@media (max-width: 768px) {
    .auth-container {
        flex-direction: column;
    }

    .auth-brand {
        padding: 32px;
        text-align: center;
    }

    .brand-features {
        display: none;
    }

    .auth-form-panel {
        padding: 32px 24px;
    }
}
```

---

## 10. Scrollbars Personalizadas

```css
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
```

---

## 11. Alertas y Notificaciones

Se utiliza **SweetAlert2** para todas las notificaciones:

```html
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
```

**Configuración de Color:**
```javascript
confirmButtonColor: '#0ea5e9' // Usar accent-primary
```

**Tipos de Alerta:**
- `icon: 'success'` — Operaciones exitosas
- `icon: 'error'` — Errores de validación/servidor
- `icon: 'warning'` — Advertencias (ej: captcha pendiente)
- `icon: 'info'` — Información neutral

---

## 12. Logo y Branding

### Logo SVG
El logo es un ícono de Material Design modificado con el color brand:
- Color: `#2854C5` (Azul corporativo)
- Dimensiones recomendadas: 24px, 28px, 48px, 64px

```html
<!-- En navegación -->
<img src="logo.svg" alt="Logo" style="width: 24px; height: 24px;">

<!-- En página de login -->
<img src="logo.svg" alt="Logo" style="width: 64px; margin-bottom: 20px;">
```

### Nombre de Marca
**Formato completo:** "XML to WO by Dataeficiencia"
- XML to WO: Nombre del producto
- by Dataeficiencia: Identificación de la familia

---

## 13. Seguridad Visual

### Cloudflare Turnstile
Se integra en formularios de autenticación:

```html
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<div class="cf-turnstile" data-sitekey="YOUR_SITE_KEY"></div>
```

Posicionamiento: centrado en el formulario

---

## 14. Dependencias Externas

```html
<!-- Fuentes -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Cloudflare Turnstile (en páginas de auth) -->
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
```

---

## 15. Checklist para Nuevas Páginas

Al crear una nueva página en la familia de aplicaciones, asegúrese de:

- [ ] Usar la fuente Inter como base tipográfica
- [ ] Aplicar la paleta de colores definida
- [ ] Mantener el header de 52px con fondo `#1e293b`
- [ ] Usar border-radius mínimo (2-4px)
- [ ] Implementar transiciones de 150ms
- [ ] Usar Material Icons para iconografía
- [ ] Integrar SweetAlert2 para notificaciones
- [ ] Seguir la estructura de cards para contenedores
- [ ] Aplicar los estilos de formulario estándar
- [ ] Incluir el logo SVG en el header
- [ ] Mantener el patrón responsive para móviles

---

*Documento generado: Enero 2026*
*Versión del Design System: 2.0*
