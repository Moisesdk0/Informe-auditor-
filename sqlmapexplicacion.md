# ¿Cómo funciona sqlmap? — Explicación sencilla

Este archivo explica, en términos simples, cómo funciona sqlmap y qué se hizo exactamente en las pruebas de inyección SQL sobre BuildSafe System (ver detalle técnico completo en `inyecciones.md`).

---

## ¿sqlmap escanea solo, o uno mete comandos de inyección ya hechos?

**Ambas cosas, pero principalmente lo primero.** sqlmap no es "una lista de comandos que tú copias y pegas uno por uno" — es un programa que **automáticamente prueba cientos de variantes de inyección por ti**. Tú solo le dices:

1. **Dónde atacar** (la URL y qué parámetro probar)
2. **Cómo entrar** (la cookie de sesión, si hace falta login)
3. **Qué tan agresivo ser** (`--level` y `--risk`)

Y sqlmap se encarga de generar, mandar y analizar automáticamente todos los payloads.

---

## Cómo funciona por dentro, paso a paso

Imagina que el parámetro es `toggle_status=1`. sqlmap hace algo así:

**Paso 1 — Manda el valor normal** (`toggle_status=1`) y guarda cómo responde el servidor (como "foto de referencia").

**Paso 2 — Prueba una comilla sola** (`toggle_status=1'`) para ver si el servidor se "rompe" (error de sintaxis SQL) o responde raro. Si eso pasa, ya hay indicio de vulnerabilidad.

**Paso 3 — Si hay indicio, prueba categorías de ataque, una por una:**
- *"¿Puedo hacer que la consulta sea verdadera o falsa a mi antojo?"* → prueba booleanos (`AND 1=1`, `AND 1=2`) y compara si la respuesta cambia.
- *"¿Puedo forzar un error que me revele información?"* → manda payloads que rompen la sintaxis de forma controlada para que el mensaje de error filtre datos.
- *"¿Puedo hacer que el servidor se demore a propósito?"* → manda `SLEEP(5)` y mide si la respuesta tarda 5 segundos de más (eso confirma que el código SQL se ejecutó, aunque no veas nada en pantalla).
- *"¿Puedo anexar otra consulta con UNION?"* → intenta pegar un `SELECT` extra para sacar datos de otra tabla.

Cada una de esas categorías tiene decenas de variantes porque **MySQL, PostgreSQL, SQL Server, Oracle, etc. tienen sintaxis distinta** — sqlmap no sabe de antemano qué motor de base de datos hay detrás, así que prueba las variantes de todos los motores conocidos, por si acaso.

**Paso 4 — Si ninguna funciona, concluye "no inyectable"** para ese parámetro.

---

## Qué se hizo exactamente en este proyecto

1. **Identificación de objetivos** — se leyó el código PHP (`list_user.php`, `add_user.php`, etc.) para saber qué parámetros tocan la base de datos sin usar *prepared statements* — esos son los candidatos lógicos a probar.

2. **Cookie de sesión** — la app exige login, y sqlmap no puede resolver el reCAPTCHA solo, así que se usó la cookie `PHPSESSID` de una sesión ya autenticada como administrador para que sqlmap "se hiciera pasar" por un usuario logueado.

3. **Ejecución de sqlmap contra cada parámetro**, por ejemplo:
   ```bash
   sqlmap -u "http://localhost/buildsafe_system1/list_user.php?toggle_status=1" --cookie="PHPSESSID=..." --batch --level=2 --risk=1
   ```
   Esto hizo que sqlmap automáticamente mandara ~113 variantes de ataque contra ese único parámetro, sin escribir cada payload a mano.

4. **Subida del nivel de agresividad** (`--level=5 --risk=3`, el máximo) en uno de los parámetros, para asegurarse de que no quedara ningún tipo de prueba sin intentar.

5. **Verificación manual con `curl`** — se mandó a mano un payload (`toggle_status=1' OR '1'='1`) para comprobar con pruebas propias, sin depender solo de la herramienta, que el servidor respondía igual que con un valor normal.

6. **Lectura del código fuente** para entender la causa: se encontró que `intval()` y `htmlspecialchars()` neutralizaban los payloads antes de que llegaran a la base de datos.

---

## En una frase

sqlmap es como un probador de cerraduras automático: tú le dices "prueba esta puerta" y él prueba cientos de ganzúas distintas por ti, mucho más rápido de lo que se podría hacer a mano — en este caso solo se eligió qué puertas probar y en qué orden, y los resultados se confirmaron con pruebas manuales propias.

---

## Ver también
- `inyecciones.md` — detalle técnico completo: endpoints probados, comandos exactos, las 113 técnicas de inyección agrupadas por categoría, y la causa raíz en el código.
- `Nuevos_Hallazgos.md` — hallazgo NH-012, donde se documentan estos resultados dentro del registro general de hallazgos nuevos (ZAP, Lighthouse, sqlmap).
