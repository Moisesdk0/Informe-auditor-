<div align="center">

<img src="logo-uptag.png" alt="UPTAG" height="90">

# 🛡️ Informe de Auditoría de Seguridad
### BuildSafe System — CONFALVI C.A.

*Auditoría de seguridad informática del sistema de gestión de inventario de materiales*

![Riesgo global](https://img.shields.io/badge/Riesgo_global-7.0%2F10_ALTO-d35400?style=for-the-badge)
![Hallazgos](https://img.shields.io/badge/Hallazgos-17-1e5799?style=for-the-badge)
![OWASP](https://img.shields.io/badge/OWASP_Top_10-2025-2e86c1?style=for-the-badge)
![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MariaDB](https://img.shields.io/badge/MariaDB-003545?style=for-the-badge&logo=mariadb&logoColor=white)
![Estado](https://img.shields.io/badge/Versi%C3%B3n-1.0_Final-1e8449?style=for-the-badge)

</div>

---

## 📑 Tabla de contenido

- [Acerca del proyecto](#-acerca-del-proyecto)
- [Ficha de la auditoría](#-ficha-de-la-auditoría)
- [Resultados](#-resultados)
- [Hallazgos por módulo](#-hallazgos-por-módulo)
- [Mapeo OWASP Top 10 – 2025](#-mapeo-owasp-top-10--2025)
- [Documentos del repositorio](#-documentos-del-repositorio)
- [Estructura del repositorio](#-estructura-del-repositorio)
- [Ejecutar el sistema auditado](#-ejecutar-el-sistema-auditado)
- [Recomendaciones prioritarias](#-recomendaciones-prioritarias)
- [Equipo](#-equipo)

---

## 📌 Acerca del proyecto

Este repositorio reúne el **informe final de auditoría de seguridad** realizado sobre **BuildSafe System**, una aplicación web en PHP para la gestión de inventario de materiales de la empresa **CONFALVI C.A.**, junto con el código fuente auditado y las evidencias del análisis.

El trabajo se desarrolló como parte del **PNF en Informática – UPTAG**. La revisión fue **estática**: se analizó el código de **27 archivos PHP**, aplicando la metodología **OWASP Top 10 – 2025**.

> ⚠️ **El sistema no debe exponerse a producción pública** sin corregir, como mínimo, los hallazgos críticos y altos.

### Funcionalidades del sistema auditado

| Módulo | Descripción |
|---|---|
| 🔐 Autenticación | Inicio de sesión con reCAPTCHA y control de sesión por inactividad |
| ✉️ Recuperación de contraseña | Envío de token por correo (PHPMailer) y cambio de clave |
| 👥 Gestión de usuarios | Alta, edición, listado y baja de usuarios con roles |
| 📦 Materiales y movimientos | Registro de materiales, categorías, unidades y movimientos de inventario |
| 📝 Bitácora | Registro de acciones de los usuarios |

---

## 🧾 Ficha de la auditoría

| | |
|---|---|
| **Tipo de auditoría** | Seguridad informática |
| **Metodología** | OWASP Top 10 – 2025 |
| **Alcance** | 27 archivos PHP del núcleo funcional |
| **Tecnología** | PHP 8.2 · MariaDB · PHPMailer |
| **Fecha** | Junio 2026 |
| **Versión del informe** | 1.0 — Final |
| **Clasificación** | Confidencial |

---

## 📊 Resultados

**17 hallazgos** · Riesgo global **7.0 / 10 — ALTO**

| Severidad | Cantidad | Plazo de corrección sugerido |
|---|:---:|---|
| 🔴 Crítica | **2** | Inmediato, antes de cualquier despliegue |
| 🟠 Alta | **5** | Corto plazo (máx. 2 semanas) |
| 🟡 Media | **7** | Mediano plazo (1 mes) |
| 🟢 Baja | **3** | Próximo ciclo de mantenimiento |

```text
Crítica  ██                 2
Alta     █████              5
Media    ███████            7
Baja     ███                3
```

---

## 🔎 Hallazgos por módulo

### 1 · Autenticación
| ID | Hallazgo | Archivo |
|---|---|---|
| HAL-001 | Sin regeneración de ID de sesión tras autenticarse (*Session Fixation*) | `index.php` |
| HAL-002 | Error lógico en la verificación de rol y falta de `exit()` tras redirección | `head.php` |

### 2 · Recuperación de contraseña
| ID | Hallazgo | Archivo |
|---|---|---|
| HAL-003 | 🔴 Credenciales SMTP expuestas en el código | `enviar_token.php` |
| HAL-004 | 🔴 Depuración SMTP activa en producción | `enviar_token.php` |
| HAL-005 | Sin límite de intentos | `recuperar.php` |

### 3 · Gestión de usuarios
| ID | Hallazgo | Archivo |
|---|---|---|
| HAL-006 | Construcción insegura de consulta SQL en el registro | `add_user.php` |
| HAL-007 | Operaciones destructivas mediante GET | `list_user.php` |
| HAL-008 | Sin protección CSRF | formularios de usuarios |

### 4 · Materiales y movimientos
| ID | Hallazgo | Archivo |
|---|---|---|
| HAL-009 | Consultas SQL sin *prepared statements* | módulos de materiales |

### 5 · Inspección del código fuente
| ID | Hallazgo | Ubicación |
|---|---|---|
| HAL-010 | Archivos sensibles en el webroot | raíz del proyecto |
| HAL-011 | Errores de base de datos expuestos al usuario | `add_user.php`, `list_user.php`, `edit_user.php` |
| HAL-012 | Contraseña sin hash en base de datos | `test2.sql` |
| HAL-013 | Ausencia de control de versiones | proyecto |
| HAL-014 | Clave secreta de reCAPTCHA incrustada en el código | `index.php` |
| HAL-015 | Sin cabeceras de seguridad HTTP | `head.php` |
| HAL-016 | Falta de verificación de rol en cambio de estado | `list_user.php` |
| HAL-017 | Ausencia de documentación técnica | proyecto |

> El detalle completo de cada hallazgo (descripción, evidencia, código vulnerable, corrección y ubicación exacta) está en el informe final.

---

## 🗺️ Mapeo OWASP Top 10 – 2025

| Categoría | Hallazgos |
|---|---|
| A01 · Control de acceso roto | HAL-007, HAL-008, HAL-016 |
| A02 · Configuración de seguridad incorrecta | HAL-010, HAL-014, HAL-015 |
| A03 · Fallas en la cadena de suministro | No aplicable en esta revisión |
| A04 · Fallas criptográficas | HAL-012 |
| A05 · Inyección | HAL-006, HAL-009 |
| A06 · Diseño inseguro | HAL-005, HAL-013, HAL-017 |
| A07 · Fallas de autenticación | HAL-001, HAL-002, HAL-003 |
| A08 · Fallas de integridad de software y datos | No aplicable en esta revisión |
| A09 · Fallas en registro y alertas | HAL-004 |
| A10 · Manejo inadecuado de condiciones excepcionales | HAL-011 |

---

## 📚 Documentos del repositorio

| Documento | Descripción |
|---|---|
| [`informe_auditoria.html`](informe_auditoria.html) | Informe final de auditoría en HTML (fuente del PDF) |
| [`Informe_Auditoria_BuildSafe.pdf`](Informe_Auditoria_BuildSafe.pdf) | Informe final en PDF |
| [`INFORME FINAL DE AUDITORIA.pdf`](INFORME%20FINAL%20DE%20AUDITORIA.pdf) | Versión del informe final |
| [`Informe Técnico de Auditoría_Proyecto.pdf`](Informe%20T%C3%A9cnico%20de%20Auditor%C3%ADa_Proyecto.pdf) | Informe técnico del proyecto |
| [`auditoria_seguridad.html`](auditoria_seguridad.html) | Versión previa del informe |
| [`Prompt_Maestro_Auditoria_Seguridad_Unificado.docx`](Prompt_Maestro_Auditoria_Seguridad_Unificado.docx) | Guía de trabajo de la auditoría |
| [`file/Manual de usuario.pdf`](file/Manual%20de%20usuario.pdf) | Manual de usuario del sistema |
| [`capturas_informe_v3/`](capturas_informe_v3) | Páginas del informe como imágenes (versión actual) |

---

## 🗂️ Estructura del repositorio

```text
.
├── informe_auditoria.html        # Informe final (HTML)
├── *.pdf                         # Informes en PDF
├── capturas_informe*/            # Capturas de las páginas del informe
├── index.php                     # Inicio de sesión
├── recuperar.php · enviar_token.php · cambiar_password.php · actualizar_password.php
├── add_user.php · edit_user.php · list_user.php · user.php
├── add_material.php · edit_material.php · list_material(s).php
├── register_movement.php · binnacle.php
├── head.php · head2.php · dashboard.php · destroyer.php
├── conexion.php                  # Conexión a MariaDB/MySQL
├── test2.sql                     # Esquema y datos de la base de datos
├── assets/ · CSS/ · src/         # Recursos estáticos
├── file/                         # Manual de usuario
└── composer.json · vendor/       # Dependencias (PHPMailer)
```

Tablas de la base de datos (`test2`): `action`, `binnacle`, `categoria`, `material`, `movement`, `rol`, `unidad`, `user`.

---

## 🚀 Ejecutar el sistema auditado

> Solo para **entorno local de pruebas**. No lo publiques en Internet sin aplicar las correcciones.

**Requisitos:** PHP 8.2+, MariaDB/MySQL (por ejemplo XAMPP) y Composer.

```bash
# 1. Instalar dependencias
composer install

# 2. Crear la base de datos e importar el esquema
mysql -u root -e "CREATE DATABASE test2"
mysql -u root test2 < test2.sql

# 3. Ajustar credenciales en conexion.php (por defecto root sin contraseña)

# 4. Servir el proyecto
php -S localhost:8000
```

Abre <http://localhost:8000/index.php>.

Para la recuperación de contraseña configura tu propio correo SMTP y tu clave de reCAPTCHA, de preferencia por **variables de entorno** y no dentro del código.

---

## ✅ Recomendaciones prioritarias

1. **Revocar y rotar** las credenciales expuestas (contraseña de aplicación SMTP y clave secreta de reCAPTCHA) y moverlas a variables de entorno.
2. Desactivar `SMTPDebug` y no mostrar errores de base de datos al usuario.
3. Usar **consultas preparadas** en todos los accesos a la base de datos.
4. Llamar a `session_regenerate_id(true)` al iniciar sesión y agregar `exit()` tras cada redirección.
5. Reemplazar las acciones destructivas por **POST con token CSRF** y verificar el rol en cada operación.
6. Guardar todas las contraseñas con `password_hash()`.
7. Añadir **límite de intentos** en login y recuperación.
8. Agregar cabeceras HTTP de seguridad y sacar del webroot los archivos sensibles.
9. Mantener el proyecto bajo control de versiones y documentarlo.

---

## 👥 Equipo

| Integrante |
|---|
| Leonel Guerrero |
| Michael Ordóñez |
| Angel Acosta |
| Nicson Pimentel |

<div align="center">

**UPTAG · PNF Informática** · Documento confidencial — uso interno exclusivo

</div>
