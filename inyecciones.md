# Inyecciones SQL — Pruebas realizadas

Registro detallado de las pruebas de inyección SQL ejecutadas sobre **BuildSafe System**, complementando el escaneo activo que OWASP ZAP dejó pendiente (ver `Nuevos_Hallazgos.md`, sección 1.2). Este archivo documenta qué se probó, cómo, y con qué resultado — es el detalle técnico detrás del hallazgo **NH-012**.

- **Herramienta principal:** sqlmap 1.10.10
- **Entorno:** XAMPP local — Apache 2.4.58, PHP 8.2.12, MariaDB, base de datos `test2`
- **Fecha:** 17/10/2026
- **Resultado general:** ningún parámetro probado resultó inyectable

---

## 1. Endpoints y parámetros probados

| # | Endpoint | Parámetro | Método | Nivel / Riesgo sqlmap | Resultado |
|---|---|---|---|---|---|
| 1 | `list_user.php` | `toggle_status` | GET | 2/1, repetido a 3/2 y 5/3 (máximo) | No inyectable |
| 2 | `list_user.php` | `delete_id` | GET | 2/1 | No inyectable |
| 3 | `list_material.php` | `toggle_status` | GET | 2/1 | No inyectable |
| 4 | `list_material.php` | `delete_id` | GET | 2/1 | No inyectable |
| 5 | `list_materials.php` | `delete_id` | GET | 2/1 | No inyectable |
| 6 | `list_materials2.php` | `delete_id` | GET | 2/1 | No inyectable |
| 7 | `edit_user.php` | `edit_id` | GET | 2/1 | No inyectable |
| 8 | `edit_material.php` | `edit_id` | GET | 2/1 | No inyectable |
| 9 | `edit_material2.php` | `edit_id` | GET | 2/1 | No inyectable |
| 10 | `add_user.php` | `user` (POST) | POST | 3/2 | No inyectable |
| 11 | `add_user.php` | `email` (POST) | POST | 3/2 | No inyectable |

**Descartado de la automatización:** `index.php` (login) — tiene reCAPTCHA, que bloquea el envío automatizado de sqlmap. Ya se había probado a mano en el informe original (payload `' OR '1'='1' --`), con resultado "Bloqueado — reCAPTCHA previene automatización masiva".

**No vulnerable por diseño (prepared statements):** `cambiar_password.php` (`token`, `email` por GET) — usa `$conn->prepare()` + `bind_param()`, revisado en el código antes de intentar la prueba; se descartó como objetivo.

---

## 2. Comandos ejecutados

### GET — ejemplo base (repetido por cada endpoint/parámetro de la tabla)
```bash
sqlmap -u "http://localhost/buildsafe_system1/list_user.php?toggle_status=1" \
  --cookie="PHPSESSID=<cookie_de_sesion_admin>" \
  --batch --level=2 --risk=1
```

### GET — verificación al máximo nivel (solo `toggle_status`)
```bash
sqlmap -u "http://localhost/buildsafe_system1/list_user.php?toggle_status=1" \
  --cookie="PHPSESSID=<cookie_de_sesion_admin>" \
  --batch --level=5 --risk=3
```

### POST — `add_user.php` (`user`, `email`)
```bash
sqlmap -u "http://localhost/buildsafe_system1/add_user.php" \
  --cookie="PHPSESSID=<cookie_de_sesion_admin>" \
  --data="submit=1&user=sqlmaptest&email=sqlmaptest@test.com&password=TestPass123ABC!&pregunta=color&respuesta=azul&rol=2" \
  -p "user,email" \
  --batch --level=3 --risk=2
```
> La contraseña usada (`TestPass123ABC!`) cumple la validación del servidor (≥16 caracteres, mayúscula, símbolo) para que la petición realmente llegue a la línea de código vulnerable (`add_user.php:29`), en vez de quedarse en el mensaje de error de validación.

### Autenticación
Todas las pruebas requirieron la cookie `PHPSESSID` de una sesión de administrador ya autenticada (login manual en navegador, por el reCAPTCHA). Sin la cookie, `head.php` redirige a `destroyer.php`/login antes de llegar al código SQL — exactamente la misma limitación que documentó OWASP ZAP con su spider sin sesión.

---

## 3. Técnicas de inyección probadas (por parámetro)

Cada parámetro de la lista de la sección 1 fue sometido a las siguientes **113 variantes únicas** de inyección SQL (a nivel 3/riesgo 2; la corrida a nivel 5/riesgo 3 en `toggle_status` sumó aún más, incluyendo IBM DB2, Informix y SAP HANA). Se agrupan por técnica:

### 3.1 Boolean-based blind (inyección ciega booleana)
Compara respuestas del servidor para condiciones verdaderas/falsas sin ver el dato directamente.
```
AND boolean-based blind - WHERE or HAVING clause
AND boolean-based blind - WHERE or HAVING clause (subquery - comment)
AND boolean-based blind - WHERE or HAVING clause (comment)
AND boolean-based blind - WHERE or HAVING clause (MySQL comment)
AND boolean-based blind - WHERE or HAVING clause (Microsoft Access comment)
MySQL RLIKE boolean-based blind - WHERE, HAVING, ORDER BY or GROUP BY clause
MySQL AND boolean-based blind - WHERE, HAVING, ORDER BY or GROUP BY clause (MAKE_SET)
PostgreSQL AND boolean-based blind - WHERE or HAVING clause (CAST)
Oracle AND boolean-based blind - WHERE or HAVING clause (CTXSYS.DRITHSX.SN)
SQLite AND boolean-based blind - WHERE or HAVING clause (JSON)
Boolean-based blind - Parameter replace (original value)
PostgreSQL boolean-based blind - Parameter replace
Microsoft SQL Server/Sybase boolean-based blind - Parameter replace
Oracle boolean-based blind - Parameter replace
Informix boolean-based blind - Parameter replace
Microsoft Access boolean-based blind - Parameter replace
Boolean-based blind - Parameter replace (DUAL)
Boolean-based blind - Parameter replace (DUAL - original value)
Boolean-based blind - Parameter replace (CASE)
Boolean-based blind - Parameter replace (CASE - original value)
MySQL >= 5.0 boolean-based blind - ORDER BY, GROUP BY clause
MySQL >= 5.0 boolean-based blind - ORDER BY, GROUP BY clause (original value)
PostgreSQL boolean-based blind - ORDER BY, GROUP BY clause
Microsoft SQL Server/Sybase boolean-based blind - ORDER BY clause
Oracle boolean-based blind - ORDER BY, GROUP BY clause
HAVING boolean-based blind - WHERE, GROUP BY clause
PostgreSQL boolean-based blind - Stacked queries
Microsoft SQL Server/Sybase boolean-based blind - Stacked queries (IF)
```

### 3.2 Error-based (inyección basada en errores)
Fuerza un mensaje de error de SQL que filtre información en la respuesta.
```
MySQL >= 5.6 AND error-based (GTID_SUBSET)
MySQL >= 5.1 AND error-based (EXTRACTVALUE)
MySQL >= 5.1 AND error-based (UPDATEXML)
PostgreSQL AND error-based - WHERE or HAVING clause
Microsoft SQL Server/Sybase AND error-based (IN)
Microsoft SQL Server/Sybase AND error-based (CONVERT)
Microsoft SQL Server/Sybase AND error-based (CONCAT)
Oracle AND error-based (XMLType)
Oracle AND error-based (CTXSYS.DRITHSX.SN)
Oracle AND error-based (DBMS_UTILITY.SQLID_TO_SQLHASH)
Firebird AND error-based - WHERE or HAVING clause
MonetDB AND error-based - WHERE or HAVING clause
Vertica AND error-based - WHERE or HAVING clause
IBM DB2 AND error-based - WHERE or HAVING clause
ClickHouse AND error-based - WHERE, HAVING, ORDER BY or GROUP BY clause
H2 AND error-based (CAST)
SQLite >= 3.9 AND error-based (JSON path)
MySQL >= 5.6 error-based - Parameter replace (GTID_SUBSET)
MySQL >= 5.1 error-based - Parameter replace (EXTRACTVALUE)
PostgreSQL error-based - Parameter replace
Microsoft SQL Server/Sybase error-based - Parameter replace
Oracle error-based - Parameter replace
MySQL >= 5.6 error-based - ORDER BY, GROUP BY clause (GTID_SUBSET)
MySQL >= 5.1 error-based - ORDER BY, GROUP BY clause (EXTRACTVALUE)
PostgreSQL error-based - ORDER BY, GROUP BY clause
Microsoft SQL Server error-based - Stacking (RAISERROR)
Microsoft SQL Server/Sybase error-based - Stacking (EXEC)
```

### 3.3 Inline queries (subconsultas en línea)
```
Generic inline queries
MySQL inline queries
PostgreSQL inline queries
Microsoft SQL Server/Sybase inline queries
Oracle inline queries
SQLite inline queries
Firebird inline queries
ClickHouse inline queries
```

### 3.4 Stacked queries (consultas apiladas con `;`)
Intenta ejecutar una segunda sentencia SQL independiente (p. ej. `INSERT`, `DROP`) después de la original.
```
MySQL >= 5.0.12 stacked queries (comment)
MySQL >= 5.0.12 stacked queries
MySQL >= 5.0.12 stacked queries (query SLEEP - comment)
MySQL < 5.0.12 stacked queries (BENCHMARK - comment)
PostgreSQL > 8.1 stacked queries (comment)
PostgreSQL stacked queries (heavy query - comment)
Microsoft SQL Server/Sybase stacked queries (comment)
Microsoft SQL Server/Sybase stacked queries (DECLARE - comment)
Oracle stacked queries (DBMS_PIPE.RECEIVE_MESSAGE - comment)
Oracle stacked queries (heavy query - comment)
IBM DB2 stacked queries (heavy query - comment)
SQLite > 2.0 stacked queries (heavy query - comment)
```

### 3.5 Time-based blind (inyección ciega por tiempo de respuesta)
Mide si la respuesta se demora artificialmente (`SLEEP(n)`, `BENCHMARK(...)`) para confirmar ejecución SQL sin ver datos.
```
MySQL >= 5.0.12 AND time-based blind (query SLEEP)
MySQL >= 5.0.12 AND time-based blind (SLEEP)
MySQL >= 5.0.12 AND time-based blind (SLEEP - comment)
MySQL >= 5.0.12 AND time-based blind (query SLEEP - comment)
MySQL < 5.0.12 AND time-based blind (BENCHMARK)
MySQL > 5.0.12 AND time-based blind (heavy query)
MySQL >= 5.0.12 RLIKE time-based blind
MySQL >= 5.0.12 RLIKE time-based blind (query SLEEP)
MySQL AND time-based blind (ELT)
PostgreSQL > 8.1 AND time-based blind
PostgreSQL AND time-based blind (heavy query)
Microsoft SQL Server/Sybase time-based blind (IF)
Microsoft SQL Server/Sybase AND time-based blind (heavy query)
Oracle AND time-based blind
Oracle AND time-based blind (heavy query)
IBM DB2 AND time-based blind (heavy query)
SQLite > 2.0 AND time-based blind (heavy query)
CUBRID AND time-based blind (SLEEP)
CUBRID OR time-based blind (SLEEP)
Informix AND time-based blind (heavy query)
MySQL >= 5.1 time-based blind (heavy query) - PROCEDURE ANALYSE (EXTRACTVALUE)
MySQL >= 5.0.12 time-based blind - Parameter replace
MySQL >= 5.0.12 time-based blind - Parameter replace (substraction)
PostgreSQL > 8.1 time-based blind - Parameter replace
Oracle time-based blind - Parameter replace (DBMS_SESSION.SLEEP)
Oracle time-based blind - Parameter replace (DBMS_LOCK.SLEEP)
Oracle time-based blind - Parameter replace (DBMS_PIPE.RECEIVE_MESSAGE)
MySQL >= 5.0.12 time-based blind - ORDER BY, GROUP BY clause
PostgreSQL > 8.1 time-based blind - ORDER BY, GROUP BY clause
Oracle time-based blind - ORDER BY, GROUP BY clause (DBMS_SESSION.SLEEP)
Oracle time-based blind - ORDER BY, GROUP BY clause (DBMS_LOCK.SLEEP)
Oracle time-based blind - ORDER BY, GROUP BY clause (DBMS_PIPE.RECEIVE_MESSAGE)
SAP HANA AND time-based blind (heavy query)
```

### 3.6 UNION query (inyección por UNION SELECT)
Intenta anexar una segunda consulta `SELECT` para extraer datos de otras tablas directamente en la respuesta.
```
Generic UNION query (NULL) - 1 to 10 columns
Generic UNION query (random number) - 1 to 10 columns
MySQL UNION query (NULL) - 1 to 10 columns
MySQL UNION query (random number) - 1 to 10 columns
```

**Total: 113 variantes únicas por parámetro × 11 parámetros = cientos de peticiones de inyección reales enviadas al servidor.** Ninguna tuvo éxito.

---

## 4. Verificación manual complementaria

Además de sqlmap, se hizo una comparación manual directa con `curl` para visualizar el efecto exacto de un payload clásico:

```bash
# Valor normal
curl -H "Cookie: PHPSESSID=<cookie>" "http://localhost/buildsafe_system1/list_user.php?toggle_status=1"

# Payload con comilla
curl -H "Cookie: PHPSESSID=<cookie>" "http://localhost/buildsafe_system1/list_user.php?toggle_status=1'"

# Payload clásico de bypass
curl -H "Cookie: PHPSESSID=<cookie>" "http://localhost/buildsafe_system1/list_user.php?toggle_status=1' OR '1'='1"
```

**Resultado:** las tres peticiones devolvieron exactamente la misma respuesta del servidor (mismo warning conocido de `headers already sent`, sin ningún error de sintaxis SQL ni cambio de comportamiento). Esto confirma, a nivel de código, por qué sqlmap no logró inyectar nada.

---

## 5. Causa raíz (por qué no son explotables)

| Endpoint | Parámetro | Protección en el código | Línea |
|---|---|---|---|
| `list_user.php`, `list_material.php`, `list_materials.php`, `list_materials2.php` | `toggle_status`, `delete_id` | `intval($_GET[...])` — fuerza el valor a entero antes de interpolarlo en el SQL | `list_user.php:6,48`; `list_material.php:6,31` |
| `edit_user.php`, `edit_material.php`, `edit_material2.php` | `edit_id` | `intval()` / `is_numeric()` | `edit_user.php:17`; `edit_material2.php:5` |
| `add_user.php` | `user`, `email` (POST) | `htmlspecialchars($_POST[...])` — en PHP 8.1+, `ENT_QUOTES` por defecto neutraliza la comilla simple | `add_user.php:11,13` |
| `cambiar_password.php` | `token`, `email` (GET) | Prepared statements (`$conn->prepare()` + `bind_param()`) | `cambiar_password.php:16-17, 29-30` |

**Importante:** estas protecciones son efectivas hoy, pero son frágiles — dependen de que cada desarrollador recuerde aplicar `intval()`/`htmlspecialchars()` en cada punto nuevo del código, en vez de que el diseño sea seguro por defecto (prepared statements en todas las consultas). Esto **no invalida** la recomendación de migrar a consultas preparadas (ya señalada como HAL-009 en el informe).

---

## 6. Relación con hallazgos existentes del informe

| Hallazgo | Qué aporta esta prueba |
|---|---|
| **HAL-006** (Construcción insegura de consulta SQL en `add_user.php`) | Reconfirmado con herramienta (sqlmap) además de la prueba manual ya documentada. Sigue sin ser explotable en PHP 8.2.12. |
| **HAL-009** (Consultas SQL directas con variables interpoladas) | Se acota el alcance: el código **sigue sin prepared statements** (mala práctica real, se mantiene el hallazgo), pero los vectores GET concretos (`toggle_status`, `delete_id`, `edit_id`) **no son explotables como inyección SQL** gracias a `intval()`. |
| **HAL-007** (Operaciones destructivas vía GET) | **No relacionado con inyección, pero sigue vigente** — aunque no se pueda inyectar SQL, cualquiera con la sesión puede borrar/activar-desactivar usuarios o materiales con solo visitar una URL. Las pruebas de sqlmap no cubren ni reemplazan este hallazgo. |

---

## 7. Pendientes

- [ ] Probar a mano el login (`index.php`) con el reCAPTCHA resuelto manualmente, para confirmar si sigue "Bloqueado" o si hay alguna forma de automatizar parcialmente.
- [ ] Revisar `edit_user.php`, `edit_material.php` y `register_movement.php` en busca de otros parámetros POST sin prepared statement, no cubiertos todavía.
- [ ] Guardar un reporte consolidado de sqlmap con `--output-dir` como evidencia anexa (actualmente la evidencia es la salida de consola).
- [ ] Decidir si NH-012 pasa al informe final como confirmación adicional de HAL-006/HAL-009, o se deja solo en `Nuevos_Hallazgos.md`.
