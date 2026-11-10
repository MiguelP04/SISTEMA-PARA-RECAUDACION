# AGENTS.md

## Qué es este repo

- **SISCOIR**: PHP 8.2+ para la División de Recaudación de la Aduana Principal Las Piedras - Paraguaná.
- No hay framework, router, Composer, npm, build, CI ni suite de tests.
- Los archivos PHP de la raíz son controladores; la URL apunta al archivo.
- Flujo habitual:
  1. Controlador en la raíz.
  2. `require_once __DIR__ . '/src/bootstrap.php'`.
  3. `require_once __DIR__ . '/src/auth.php'` o `src/password_reset.php`.
  4. `require` de una vista parcial en `views/`.
- `views/` contiene HTML/PHP parcial, no un template engine.
- CSS separado: `assets/css/base.css` compartido; `login.css`, `recovery.css`, `layout.css` y `dashboard.css` por zona.

## Comandos

No inventes comandos de Composer/npm/testing: no existen.

```bash
# Verificar conexión y datos de la base
php -r '$conn = require "config/database.php"; $r = $conn->query("SELECT COUNT(*) AS total FROM usuarios"); echo $r->fetch_assoc()["total"] . PHP_EOL;'

# Servidor local
php -S 127.0.0.1:8080 -t .

# Lint PHP completo
find . -name '*.php' -not -path './.git/*' -print0 | xargs -0 -n1 php -l

# Lint del único JS del proyecto
node --check assets/js/login.js

# Comprobación de whitespace/conflictos en el diff
git diff --check
```

- No hay tests automatizados. La verificación funcional es manual en el navegador; el diseño actual es de escritorio y se revisa a **1440px**.
- Rama de trabajo actual: `login`. Mensajes de commit en español.

## Base de datos y sesión

- `config/database.php` tiene credenciales reales y está en `.gitignore`: nunca commitearlo.
- Plantilla versionada: `config/database.example.php`.
- Conexión: `127.0.0.1` (no `localhost`), base `contabilidad`, `mysqli`, charset `utf8`.
- `src/bootstrap.php` inicia la sesión y crea `$conn`; debe ejecutarse antes de imprimir HTML o usar `$_SESSION`.
- Cookie de sesión: `SISCOIR_SESSID`; `HttpOnly`, `SameSite=Lax`, `session.use_strict_mode=1`, `session.use_only_cookies=1`.
- Páginas protegidas llaman `requiereLogin()` después de cargar `src/auth.php`.
- Recuperación de contraseña: preguntas de seguridad con respuestas hasheadas; no hay email.

## Reglas de datos

Tabla principal: `planillas_emitidas`.

- Pagada: `fecha_cancelacion > '0000-00-00'`.
- Pendiente: `fecha_cancelacion IS NULL` o `fecha_cancelacion = '0000-00-00'`.
- Excluir anuladas en métricas operativas: `anulada <> 1`.
- Monto: `monto2`.
- Formato monetario en español: `number_format($valor, 2, ',', '.')`.
- Fechas visibles: `d/m/Y`.
- Consultas con prepared statements; nunca concatenar entrada del usuario.
- Salida de datos con `htmlspecialchars($valor, ENT_QUOTES, 'UTF-8')`.
- Textos de UI y comentarios en español.

Para portar reportes o lógica de planillas, consultar el sistema anterior en el directorio hermano `SISTEMA INTERNO RECAUDACION` cuando exista; ahí están las consultas PDF/Excel y el comportamiento esperado.

## Interfaz

- Escritorio solamente: no agregar meta `viewport`, menú hamburguesa ni breakpoints responsive.
- Único media query permitido salvo necesidad explícita: `@media (prefers-reduced-motion: reduce)`.
- Tipografía: Georama local en `assets/fonts/`.
- Tokens de color en `:root` de `assets/css/base.css`; reutilizar esas variables.
- La franja oficial se arma con `navbar-pieces/ministerio-left.png`, `seniat-logo.png` y `navbar-pieces/badge-right.png` dentro de `.official-bar__group`; no volver a una sola imagen aplanada.
- `assets/img/navbar.png` es solo la versión 2048×96 de respaldo/comparación; no usarla como franja principal ni reemplazarla por imágenes viejas o de baja resolución.
- `assets/img/seniat-logo-navbar.png` es el logo transparente de la segunda barra azul.

## Vacíos importantes

- No hay tokens CSRF, recuperación por correo ni límite de intentos de login/recuperación.
- No asumir que esos controles existen; agregarlos solo cuando se solicite.
- No hacer push ni commits salvo petición explícita.
