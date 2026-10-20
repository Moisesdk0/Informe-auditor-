# Reporte sqlmap — Parámetros pendientes de NH-012

Complementa `Nuevos_Hallazgos.md` (sección 3, NH-012). Pruebas ejecutadas el **19/10/2026**, sobre `http://localhost/buildsafe_system1/` (XAMPP: Apache 2.4.58, PHP 8.2.12, MariaDB, base `test2`), con sqlmap **1.10.10** (clonado desde el [repositorio oficial](https://github.com/sqlmapproject/sqlmap)).

Cubre los 3 pendientes anotados en `Nuevos_Hallazgos.md` §3.3:
- Parámetros `edit_id` de `edit_user.php`, `edit_material.php`, `edit_material2.php` (no cubiertos en la corrida original de NH-012).
- Parámetro `codigo` de `register_movement.php` (POST, no cubierto antes).

## Resumen de resultados

| Endpoint | Método | Parámetro probado | `--level`/`--risk` | Resultado | Evidencia cruda |
|---|---|---|---|---|---|
| `edit_user.php` | GET | `edit_id` | 2/1 | **No inyectable** | `consola/edit_user.txt` |
| `edit_material.php` | GET | `edit_id` | 2/1 | **No inyectable** | `consola/edit_material.txt` |
| `edit_material2.php` | GET | `edit_id` | 2/1 | **No inyectable** | `consola/edit_material2.txt` |
| `register_movement.php` | POST | `codigo` | 3/2 | **No inyectable** | ver consola interactiva (abajo) |

Ningún parámetro mostró indicios de inyección (boolean-based, error-based, UNION, stacked queries, time-based): todos terminaron con
```
[WARNING] <parámetro> does not seem to be injectable
[CRITICAL] all tested parameters do not appear to be injectable.
```

## Sesión utilizada

- Cookie `PHPSESSID` de una sesión de administrador autenticada a mano en el navegador (usuario `admin2`, insertado vía `generar_hash.php` + `INSERT INTO user`), confirmada por el contenido del archivo de sesión del servidor:
  ```
  user|s:6:"admin2";id|i:23;email|s:15:"admin2@test.com";seccion|b:1;rol|i:1;
  ```

## Comandos ejecutados

```bash
# GET — edit_user.php
sqlmap.py -u "http://localhost/buildsafe_system1/edit_user.php?edit_id=1" \
  --cookie="PHPSESSID=<sesión_admin>" --batch --level=2 --risk=1 \
  --output-dir="sqlmap_reports"

# GET — edit_material.php
sqlmap.py -u "http://localhost/buildsafe_system1/edit_material.php?edit_id=1" \
  --cookie="PHPSESSID=<sesión_admin>" --batch --level=2 --risk=1 \
  --output-dir="sqlmap_reports"

# GET — edit_material2.php
sqlmap.py -u "http://localhost/buildsafe_system1/edit_material2.php?edit_id=1" \
  --cookie="PHPSESSID=<sesión_admin>" --batch --level=2 --risk=1 \
  --output-dir="sqlmap_reports"

# POST — register_movement.php (solo el campo codigo, para no generar movimientos de prueba masivos)
sqlmap.py -u "http://localhost/buildsafe_system1/register_movement.php" \
  --data="tipo=entrada&material_id=1&codigo=TEST001&cantidad=1&fecha_entrada=2026-10-20T00%3A00&fecha_expiracion=&submit=1" \
  -p codigo --cookie="PHPSESSID=<sesión_admin>" --batch --level=3 --risk=2 \
  --output-dir="sqlmap_reports"
```

## Causa (consistente con NH-012)

- `edit_id` en los tres `edit_*.php` pasa por `intval()`/casting numérico antes de llegar al SQL — mismo patrón ya documentado para `toggle_status`/`delete_id` en `inyecciones.md`.
- `codigo` en `register_movement.php` pasa por `htmlspecialchars()` (neutraliza comillas en PHP 8.1+, `ENT_QUOTES` por defecto) — mismo patrón que `user`/`email` de `add_user.php`.
- El resto de los campos de `register_movement.php` (`material_id`, `cantidad`, `fecha_entrada`, `fecha_expiracion`) usan **prepared statements** (`$stmt->bind_param(...)`), por lo que no se probaron como vector de inyección.

## Nota operativa

`register_movement.php` ejecuta un `INSERT` real en `movement` y actualiza `amount_total` en `material` en cada petición POST válida. La prueba contra `codigo` generó una fila de movimiento de prueba (`codigo = TEST001` o variantes con comillas/payloads, `material_id = 1`). Si se requiere una base de datos limpia para el informe final, reimportar desde `test2.sql`.

## Evidencia cruda completa

Archivos de consola completos (salida real de sqlmap, sin resumir) en `sqlmap_reports/consola/`:
- `edit_user.txt`
- `edit_material.txt`
- `edit_material2.txt`

Archivo de sesión/objetivo de sqlmap (`--output-dir`): `sqlmap_reports/localhost/target.txt`, `sqlmap_reports/localhost/session.sqlite`.

## Pendiente

- [ ] Decidir si este reporte se fusiona dentro de NH-012 en `Nuevos_Hallazgos.md` o queda como anexo independiente (recomendado: fusionar, actualizando la tabla de §3.2 con estos 4 parámetros adicionales).
- [ ] Repetir `register_movement.php` probando también `tipo` manualmente con `curl` (por ejemplo `tipo=entrada' OR '1'='1`), ya que `in_array()` lo filtra a nivel de PHP antes de llegar al SQL — bajo riesgo, pero no verificado con prueba manual directa como se hizo con `add_user.php`.
