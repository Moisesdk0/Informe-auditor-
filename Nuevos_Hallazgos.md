# Nuevos Hallazgos — BuildSafe System

Registro vivo de hallazgos que **no están** en `informe_auditoria.html` (v1.0, HAL-001 a HAL-017).
Este archivo no modifica el informe; sirve para documentar, validar y luego decidir qué se incorpora.

- **Identificadores:** `NH-xxx` (Nuevo Hallazgo), para no chocar con la numeración `HAL-xxx`. La numeración es continua entre herramientas.
- **Estado:** `Observado` (visto en la herramienta) → `Verificado` (reproducido a mano) → `Incorporado` (pasado al informe).
- **Organización:** cada herramienta tiene su propia sección, con la tecnología usada y la cantidad de pruebas arriba.

## Índice de herramientas

| # | Herramienta | Tipo de prueba | Estado | Hallazgos nuevos | Confirman HAL existentes |
|---|---|---|---|---|---|
| 1 | OWASP ZAP 2.17.0 | DAST (escaneo dinámico): pasivo + spider | Hecha (activo pendiente) | 6 (NH-001 a NH-006) | 4 alertas (HAL-008, HAL-015) |
| 2 | Lighthouse 13.5.0 (CLI, Chrome 155 headless) | Auditoría de rendimiento, accesibilidad, buenas prácticas y SEO | Hecha (7 páginas, escritorio y móvil) | 5 (NH-007 a NH-011), de calidad | — |

## Índice de hallazgos nuevos

| ID | Hallazgo | Riesgo | Herramienta | Estado |
|---|---|---|---|---|
| NH-001 | Listado de directorios habilitado | Medio | OWASP ZAP | Observado |
| NH-002 | Divulgación de rutas internas por warnings de PHP | Medio | OWASP ZAP | Observado |
| NH-003 | Scripts de terceros sin atributo `integrity` (SRI) | Medio | OWASP ZAP | Observado |
| NH-004 | Cookie de sesión sin `HttpOnly` ni `SameSite` | Bajo | OWASP ZAP | Observado |
| NH-005 | Fuga de versiones en cabeceras y banner | Bajo | OWASP ZAP | Observado |
| NH-006 | Redirecciones con cuerpo de respuesta (posible falta de `exit`) | Bajo | OWASP ZAP | Observado, hipótesis |
| NH-007 | Recursos que devuelven 404 (`css2`, `JS/script.js`, `script.js`) | Bajo (calidad) | Lighthouse (+ ZAP) | Observado |
| NH-008 | Error de JavaScript `notifications is not defined` | Bajo (calidad) | Lighthouse | Observado |
| NH-009 | Problemas de accesibilidad (enlaces y `select` sin nombre, sin `main`, contraste) | Bajo (calidad) | Lighthouse | Observado |
| NH-010 | SEO: sin `meta description` y enlaces no rastreables | Informativo (calidad) | Lighthouse | Observado |
| NH-011 | Peso elevado de página y bajo rendimiento en móvil simulado | Bajo (calidad) | Lighthouse | Observado |

---

# 1. OWASP ZAP

> **Tecnología:** OWASP ZAP 2.17.0 (DAST, escaneo dinámico)
> **Pruebas realizadas:** escaneo pasivo sobre la navegación manual autenticada + Spider. **Escaneo activo: pendiente.**
> **Resultado:** 0 críticas, 0 altas, **7 medias, 8 bajas, 5 informativas** → 6 hallazgos nuevos, 4 alertas que confirman HAL existentes, 4 grupos descartados.

## 1.1 Alcance y configuración

| Dato | Valor |
|---|---|
| Fecha | 15/10/2026 |
| Objetivo | `http://localhost/buildsafe_system1/` (XAMPP local: Apache 2.4.58, PHP 8.2.12, MariaDB 10.4.32) |
| Base de datos | `test2`, importada desde `test2.sql` |
| Contexto de escaneo | Solo `buildsafe_system1/`; excluidos `destroyer.php`, `delete` y `eliminar` |
| Sesión | Administrador, iniciada a mano en el navegador de ZAP (el login tiene reCAPTCHA) |
| Reporte crudo | `2026-10-15-ZAP-Report-localhost.json` |

## 1.2 Limitaciones
1. **El spider corrió sin sesión.** Las páginas internas respondieron con la redirección a `destroyer.php` y con el warning `Undefined array key "id"`. Lo detectado en páginas internas proviene de la navegación manual autenticada.
2. **`enviar_token.php` falla en esta copia** (`require_once(...vendor/composer/autoload_real.php): Failed to open stream`). Falta parte de `vendor`, así que HAL-003 y HAL-004 no se pueden reproducir en este entorno. Es limitación de la copia, no del código original.
3. **Entorno local por defecto.** El listado de directorios y las cabeceras dependen de la configuración de XAMPP; en un servidor real pueden diferir.
4. **Sin escaneo activo:** no hay resultados de inyección ni XSS por ZAP.
5. Las alertas de ZAP son automáticas y pueden incluir falsos positivos; cada una requiere verificación manual antes de incorporarse.

## 1.3 Hallazgos nuevos (detectados con OWASP ZAP)

### NH-001 — Listado de directorios habilitado
- **Herramienta:** OWASP ZAP (regla 10033, escaneo pasivo)
- **Estado:** Observado (falta captura manual).
- **Riesgo ZAP:** Medio (confianza Media). **CWE-548**.
- **Dónde:** `/buildsafe_system1/CSS/`, `/assets/`, `/assets/css/`, `/assets/js/`, `/src/`.
- **Evidencia:** respuestas con `<title>Index of /buildsafe_system1/src</title>` y enlaces de ordenamiento `?C=N;O=A`, `?C=S;O=D`, etc.
- **Impacto:** expone la estructura y los archivos del proyecto; `/src/` puede contener código fuente.
- **Relación:** complementa HAL-010 (archivos sensibles en webroot), pero es una causa distinta (configuración de Apache).
- **Corrección sugerida:** `Options -Indexes` en la configuración de Apache o en `.htaccess`.
- **Pendiente:** abrir `/src/` y `/assets/` en el navegador, tomar captura y anotar qué archivos se listan.

### NH-002 — Divulgación de rutas internas por warnings de PHP
- **Herramienta:** OWASP ZAP (regla 90022, escaneo pasivo)
- **Estado:** Observado (falta captura manual).
- **Riesgo ZAP:** Medio (confianza Media). **CWE-550**. 23 instancias.
- **Dónde:** casi todas las páginas internas; también `list_user.php` y `enviar_token.php`.
- **Evidencia:**
  - `Warning: Undefined array key "id" in C:\xampp\htdocs\buildsafe_system1\head.php on line 5`
  - `Warning: Cannot modify header information - headers already sent by (output started at ...\head.php:41) in ...\list_user.php on line 79`
  - `Warning: require_once(C:\xampp\htdocs\buildsafe_system1\vendor/composer/autoload_real.php): Failed to open stream` (`enviar_token.php`)
- **Impacto:** revela rutas absolutas del servidor, nombres de archivo y números de línea. Indica `display_errors` activo.
- **Relación:** distinto de HAL-011 (errores de base de datos en `add_user.php`). Podría agruparse bajo A10 (Manejo inadecuado de condiciones excepcionales) o A02.
- **Corrección sugerida:** `display_errors = Off` y `log_errors = On` en `php.ini`; validar `$_SESSION['id']` con `isset()` en `head.php:5`.

### NH-003 — Scripts de terceros sin atributo `integrity` (SRI)
- **Herramienta:** OWASP ZAP (reglas 90003 y 10017, escaneo pasivo)
- **Estado:** Observado.
- **Riesgo ZAP:** Medio (confianza Alta). **CWE-345** (relacionada: Inclusión de JavaScript entre dominios, **CWE-829**, riesgo Bajo).
- **Dónde:** `index.php` y `dashboard.php`.
- **Evidencia:**
  - `<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>`
  - `<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">`
  - `<script src="https://www.google.com/recaptcha/api.js" async defer></script>`
  - `<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>`
- **Impacto:** si el CDN se compromete, se puede inyectar código en el sistema (cadena de suministro).
- **Relación con el informe:** el informe marca **A03** y **A08** como "No aplicable". Este hallazgo sugiere revisar esa conclusión (A03 en OWASP Top 10 2025 es *Fallas en la cadena de suministro de software*).
- **Nota:** `reCAPTCHA` no admite SRI por diseño (el script cambia); aplicaría a sweetalert2 y jQuery.
- **Corrección sugerida:** añadir `integrity` + `crossorigin`, o alojar las librerías localmente con versión fija.

### NH-004 — Cookie de sesión sin `HttpOnly` ni `SameSite`
- **Herramienta:** OWASP ZAP (reglas 10010 y 10054, escaneo pasivo)
- **Estado:** Observado.
- **Riesgo ZAP:** Bajo (confianza Media). **CWE-1004** y **CWE-1275**.
- **Dónde:** cookie `PHPSESSID` (`Set-Cookie: PHPSESSID`).
- **Impacto:** JavaScript puede leer la cookie (agrava un eventual XSS) y se envía en peticiones entre sitios (agrava CSRF).
- **Relación:** complementa HAL-001 (sin regeneración de sesión) y HAL-008 (sin CSRF).
- **Corrección sugerida:** `session.cookie_httponly=1`, `session.cookie_samesite=Lax` (o `Strict`) y, con HTTPS, `session.cookie_secure=1`.

### NH-005 — Fuga de versiones en cabeceras y banner
- **Herramienta:** OWASP ZAP (reglas 10036, 10037 y 10009, escaneo pasivo)
- **Estado:** Observado.
- **Riesgo ZAP:** Bajo (confianza Alta/Media). **CWE-497**.
- **Evidencia:**
  - `Server: Apache/2.4.58 (Win64) OpenSSL/3.1.3 PHP/8.2.12`
  - `X-Powered-By: PHP/8.2.12`
  - Cadena `Apache/2.4.58` en el cuerpo de respuestas.
- **Impacto:** facilita buscar vulnerabilidades específicas de esas versiones.
- **Relación:** podría integrarse en HAL-015 (cabeceras de seguridad) como un punto adicional.
- **Corrección sugerida:** `ServerTokens Prod`, `ServerSignature Off` y `expose_php = Off`.

### NH-006 — Redirecciones con cuerpo de respuesta (posible falta de `exit`)
- **Herramienta:** OWASP ZAP (regla 10044, escaneo pasivo)
- **Estado:** Observado, **hipótesis sin verificar**.
- **Riesgo ZAP:** Bajo (confianza Media). **CWE-201**. 17 instancias.
- **Evidencia:** `binnacle.php`, `dashboard.php`, `edit_user.php`, `list_user.php`, `list_material.php` y otras redirigen a `destroyer.php` pero devuelven un cuerpo de 636 bytes (el warning de `head.php`).
- **Impacto:** si el código no hace `exit` tras `header('Location: ...')`, el resto de la página podría ejecutarse aunque el usuario sea redirigido.
- **Relación:** posible vínculo con HAL-016 (falta de verificación de rol en cambio de estado) y HAL-002.
- **Pendiente:** revisar el código de `head.php` y de las páginas afectadas para confirmar si falta `exit`/`die`.

## 1.4 Alertas de ZAP que confirman hallazgos existentes

| Alerta ZAP | Riesgo ZAP | Hallazgo del informe | Qué aporta |
|---|---|---|---|
| Ausencia de tokens Anti-CSRF (CWE-352) | Medio | **HAL-008** | Evidencia automática: sin token en login, `edit_material.php`, `edit_material2.php` |
| Cabecera CSP no configurada (CWE-693) | Medio | **HAL-015** | Detalle de la cabecera ausente |
| Falta cabecera Anti-Clickjacking (CWE-1021) | Medio | **HAL-015** | Detalle de la cabecera ausente |
| Falta `X-Content-Type-Options` (CWE-693) | Bajo | **HAL-015** | Detalle de la cabecera ausente |

## 1.5 Alertas de ZAP descartadas o solo informativas

| Alerta ZAP | Motivo |
|---|---|
| Atributo HTML controlable por el usuario (XSS potencial), 20 instancias | Informativo, confianza baja. Solo marca puntos a revisar; no hay explotación demostrada. Revisar a mano o con el escaneo activo. |
| Comentarios sospechosos | Provienen de librerías de terceros (`quill.js`, `perfect-scrollbar.min.js`, etc.). Falsos positivos. |
| Información en localStorage | Informativa, sin acción. |
| Petición de autenticación / respuesta de gestión de sesión identificadas | Informativas, sin acción. |

## 1.6 Pendientes de ZAP
- [ ] Capturas manuales de NH-001 (`/src/`, `/assets/`) y NH-002 (warnings en pantalla).
- [ ] Revisar el código para confirmar o descartar NH-006.
- [ ] Lanzar el **escaneo activo** (con respaldo de `test2` ya hecho) y generar otro reporte; documentar lo nuevo en esta sección.
- [ ] Repetir el spider **con sesión** (o agregar a mano las rutas autenticadas) para ampliar la cobertura.

---

# 2. Lighthouse

> **Tecnología:** Lighthouse 13.5.0 por línea de comandos (CLI) sobre Chrome 155 en modo headless. Es el mismo motor que el panel Lighthouse de DevTools que usó el informe de Comedor UPTAG (81/100), pero el empaquetado es distinto: **método propio, no el de los otros informes.**
> **Pruebas realizadas:** 16 mediciones (8 páginas × 2 modos). 14 completadas, 2 fallidas (`file.php`).
> **Resultado:** rendimiento 80–97 en escritorio y 41–63 en móvil simulado; accesibilidad 84–100; buenas prácticas 96 en todas; SEO 82–91. 5 hallazgos de calidad (NH-007 a NH-011).

## 2.1 Alcance y configuración

| Dato | Valor |
|---|---|
| Fecha | 15/10/2026 |
| Herramienta | Lighthouse 13.5.0 (CLI), Chrome 155.0.0.0 headless, instalado con `npm install lighthouse` |
| Objetivo | `http://localhost/buildsafe_system1/` (XAMPP local) |
| Páginas auditadas | `login` (público, sin cookie), `dashboard.php`, `list_materials.php`, `list_materials2.php`, `list_material.php`, `list_user.php`, `binnacle.php` (con sesión de administrador) |
| Excluidas | `file.php` (falló), `destroyer.php`, y cualquier URL con `toggle_status` o `delete_id` (cambian datos o cierran la sesión) |
| Sesión | Cookie `PHPSESSID` de la sesión iniciada a mano en Playwright, enviada como cabecera extra. Se verificó que cada medición terminó en su URL y no en el login |
| Modos | **Escritorio** (preset desktop: RTT 40 ms, 10 Mbps, sin ralentizar CPU) y **móvil** (por defecto de Lighthouse: RTT 150 ms, 1,6 Mbps, CPU 4× más lenta, simulado) |
| Categorías | Rendimiento, Accesibilidad, Buenas prácticas, SEO |
| Informes | Carpeta `lighthouse/` del proyecto (`<página>_<modo>.report.html` y `.json`) y script `lighthouse/run_all.sh` |

## 2.2 Puntuaciones

### Escritorio

| Página | Rendimiento | Accesibilidad | Buenas prácticas | SEO | FCP | LCP | TBT | CLS | Speed Index |
|---|---|---|---|---|---|---|---|---|---|
| login | 97 | 100 | 96 | 91 | 0,7 s | 0,8 s | 0 ms | 0,016 | 1,7 s |
| dashboard | 90 | 84 | 96 | 91 | 0,9 s | 2,0 s | 0 ms | 0 | 1,2 s |
| list_materials | 84 | 90 | 96 | 82 | 0,8 s | 2,5 s | 60 ms | 0 | 1,6 s |
| list_materials2 | 80 | 90 | 96 | 82 | 0,8 s | 3,0 s | 80 ms | 0 | 1,6 s |
| list_material | 83 | 87 | 96 | 82 | 0,8 s | 2,5 s | 70 ms | 0 | 1,6 s |
| list_user | 86 | 87 | 96 | 82 | 0,9 s | 2,3 s | 0 ms | 0 | 1,2 s |
| binnacle | 86 | 90 | 96 | 91 | 1,0 s | 2,3 s | 0 ms | 0,001 | 1,2 s |

### Móvil (simulado)

| Página | Rendimiento | Accesibilidad | Buenas prácticas | SEO | FCP | LCP | TBT | CLS | Speed Index |
|---|---|---|---|---|---|---|---|---|---|
| login | 63 | 100 | 96 | 91 | 5,6 s | 6,3 s | 0 ms | 0 | 5,9 s |
| dashboard | 58 | 84 | 96 | 91 | 6,7 s | 13,4 s | 80 ms | 0 | 7,4 s |
| list_materials | 42 | 90 | 96 | 82 | 6,8 s | 16,8 s | 580 ms | 0 | 8,4 s |
| list_materials2 | 41 | 90 | 96 | 82 | 6,8 s | 16,7 s | 640 ms | 0 | 8,4 s |
| list_material | 44 | 87 | 96 | 82 | 6,8 s | 16,8 s | 510 ms | 0 | 8,2 s |
| list_user | 57 | 87 | 96 | 82 | 6,8 s | 13,1 s | 140 ms | 0 | 7,2 s |
| binnacle | 55 | 90 | 96 | 91 | 7,1 s | 13,4 s | 180 ms | 0 | 7,3 s |

Siglas: FCP = First Contentful Paint, LCP = Largest Contentful Paint, TBT = Total Blocking Time, CLS = Cumulative Layout Shift.

## 2.3 Hallazgos nuevos (detectados con Lighthouse)

> Son hallazgos de **calidad** (ISO/IEC 25010, que usaron otros informes), no de seguridad. Para el informe se podrían agrupar en una sección de calidad aparte, o relacionar con A10 / A02 en los casos que lo justifiquen.

### NH-007 — Recursos que devuelven 404
- **Herramienta:** Lighthouse (auditoría `errors-in-console`); coincide con las peticiones 404 vistas en OWASP ZAP.
- **Estado:** Observado.
- **Riesgo:** Bajo (calidad: fiabilidad).
- **Dónde:** `/buildsafe_system1/css2?family=Nunito:...` (fuente de Google Fonts mal referenciada como ruta relativa), `/buildsafe_system1/JS/script.js` y `/buildsafe_system1/script.js`.
- **Evidencia:** `Failed to load resource: the server responded with a status of 404 (Not Found)` (4 recursos en el dashboard).
- **Impacto:** recursos que no cargan (explica las imágenes rotas y la fuente ausente), peticiones inútiles y ruido en la consola.
- **Corrección sugerida:** corregir la URL de la fuente a `https://fonts.googleapis.com/css2?...` y quitar o corregir las referencias a `script.js` y `JS/script.js`.

### NH-008 — Error de JavaScript `notifications is not defined`
- **Herramienta:** Lighthouse (auditoría `errors-in-console`).
- **Estado:** Observado en el dashboard; las demás páginas internas también fallan `errors-in-console` (8 a 9 entradas), sin extraer el detalle de cada una.
- **Riesgo:** Bajo (calidad: fiabilidad).
- **Evidencia:** `ReferenceError: notifications is not defined` (varias veces por página).
- **Impacto:** una funcionalidad de la interfaz (notificaciones) no funciona y genera errores repetidos.
- **Corrección sugerida:** definir la variable `notifications` o eliminar el código que la usa.
- **Pendiente:** confirmar en qué archivo ocurre (abrir la consola del navegador).

### NH-009 — Problemas de accesibilidad
- **Herramienta:** Lighthouse (categoría Accesibilidad).
- **Estado:** Observado.
- **Riesgo:** Bajo (calidad: usabilidad/accesibilidad).
- **Dónde:** `dashboard`, `list_materials`, `list_user` y `binnacle` (detalle extraído); `list_materials2` y `list_material` con puntuación similar, sin extraer detalle.
- **Evidencia:** auditorías no superadas: `link-name` (enlaces sin texto accesible), `select-name` (`select` sin etiqueta), `landmark-one-main` (sin elemento `main`) y, solo en `list_user`, `color-contrast` (contraste insuficiente). Puntuaciones: 84 (dashboard), 87 (`list_user`, `list_material`) y 90 (resto).
- **Impacto:** el sistema es difícil de usar con lector de pantalla y para usuarios con baja visión.
- **Corrección sugerida:** añadir `aria-label` o texto a los enlaces de iconos, `<label>` a los `select`, un `<main>` por página y revisar los colores de bajo contraste.

### NH-010 — SEO: sin `meta description` y enlaces no rastreables
- **Herramienta:** Lighthouse (categoría SEO).
- **Estado:** Observado.
- **Riesgo:** Informativo (calidad). Es poco relevante para un sistema interno.
- **Evidencia:** falta `meta description` en las 5 páginas con detalle extraído (login incluido); `crawlable-anchors` (enlaces sin `href` válido) en `list_materials` y `list_user`. SEO: 82 en las listas, 91 en login, dashboard y binnacle.
- **Corrección sugerida:** añadir `<meta name="description">` y usar `href` reales en los enlaces.

### NH-011 — Peso elevado de página y bajo rendimiento en móvil simulado
- **Herramienta:** Lighthouse (categoría Rendimiento, auditorías `total-byte-weight` y `network-requests`).
- **Estado:** Observado.
- **Riesgo:** Bajo (calidad: eficiencia de desempeño).
- **Evidencia:** dashboard 2.253 KiB en 30 peticiones; `list_materials` 2.853 KiB en 39 peticiones; `list_user` 2.187 KiB en 31; `binnacle` 2.222 KiB en 28. En móvil simulado el LCP llega a 13–17 s y el rendimiento baja a 41–63.
- **Impacto:** carga lenta en conexiones limitadas. Probablemente por dependencias externas (CDN) y librerías JS grandes; no verificado qué recursos pesan más.
- **Salvedad:** medición en XAMPP local con red y CPU simuladas; no equivale a usuarios reales.
- **Corrección sugerida:** cargar solo las librerías necesarias por página, minificar y comprimir (gzip/brotli) y aplazar scripts no críticos.

## 2.4 Limitaciones y pendientes de Lighthouse

**Limitaciones**
1. **`file.php` no se pudo medir** (`net::ERR_ABORTED`, página en `about:blank`). Probablemente sirve un archivo para descargar (en ZAP respondió 1,3 MB), pero no está verificado. Los archivos `file_desktop` y `file_mobile` de la carpeta `lighthouse/` son informes de error, no mediciones.
2. **Una sola pasada por página.** Lighthouse tiene variación entre corridas; los números son orientativos.
3. **XAMPP local.** Los tiempos no representan un servidor real, y en móvil son una simulación.
4. **Método propio.** Lighthouse CLI no fue la forma que usó Comedor UPTAG (DevTools); el motor es el mismo, pero los valores pueden diferir ligeramente.
5. **La cookie de sesión se envió como cabecera en todas las peticiones de la medición**, incluidas las de CDN externos. Es una sesión de prueba local; no repetir esto con datos reales.
6. Solo se extrajo el detalle de las auditorías fallidas en login, dashboard, `list_materials`, `list_user` y `binnacle`.

**Pendientes**
- [ ] Repetir la medición con el panel Lighthouse de DevTools (como Comedor) en 2 o 3 páginas, para comparar.
- [ ] Repetir cada medición 3 veces y promediar.
- [ ] Confirmar el origen de NH-008 en la consola del navegador.
- [ ] Extraer el detalle de `list_materials2` y `list_material`.
- [ ] Decidir si NH-007 a NH-011 pasan al informe como sección de calidad (ISO/IEC 25010).
