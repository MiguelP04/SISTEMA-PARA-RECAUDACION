# SISCOIR - Sistema Interno de Gestión

Sistema para la División de Recaudación de la Aduana Principal Las Piedras - Paraguaná.

## Estructura del proyecto

```text
index.php                    Punto de entrada y vista del login
recuperar.php                Identificación y preguntas de seguridad
restablecer.php              Cambio de contraseña autorizado
dashboard.php                Panel principal protegido
logout.php                   Cierre de sesión
views/auth/login.php         Estructura HTML del formulario
views/auth/recuperar.php     Formulario de recuperación
views/auth/restablecer.php   Formulario de nueva contraseña
views/layouts/app-header.php Encabezado oficial y navegación
views/layouts/app-footer.php Pie de página institucional
views/layouts/profile-menu.php Menú del usuario autenticado
views/dashboard/cards.php    Tarjetas del panel principal
assets/css/base.css          Fuentes, variables y estilos generales
assets/css/layout.css        Encabezado, navegación y pie de página
assets/css/login.css         Estilos exclusivos del login
assets/css/recovery.css      Estilos de recuperación de contraseña
assets/css/dashboard.css     Estilos del panel principal
assets/js/login.js           Mostrar u ocultar contraseñas
assets/fonts/                Fuentes Georama
assets/img/                  Logos e imágenes institucionales
config/database.php          Credenciales reales (ignorado por Git)
config/database.example.php  Plantilla de conexión
src/bootstrap.php            Inicio de sesión y conexión a la base
src/auth.php                 Autenticación, sesión y cierre de sesión
src/password_reset.php       Funciones de recuperación de contraseña
```

## Configuración local

1. Copia `config/database.example.php` como `config/database.php`.
2. Completa el usuario y la contraseña de MySQL en ese archivo.
3. Verifica la conexión desde la terminal:

```bash
php -r '$conn = require "config/database.php"; $r = $conn->query("SELECT COUNT(*) AS total FROM usuarios"); echo $r->fetch_assoc()["total"] . PHP_EOL;'
```

`config/database.php` está ignorado por Git para no subir credenciales reales.

## Requisitos

- XAMPP/LAMPP con PHP 8.2 y MySQL/MariaDB.
- La base de datos `contabilidad` debe existir y contener la tabla `usuarios`.
