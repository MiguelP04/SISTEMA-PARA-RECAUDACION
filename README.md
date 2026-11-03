# SISTEMA PARA RECAUDACION

## Configuración local

1. Copia `config/database.example.php` como `config/database.php`.
2. Completa el usuario y la contraseña de MySQL en ese archivo.
3. Verifica la conexión desde la terminal:

```bash
php -r '$conn = require "config/database.php"; $r = $conn->query("SELECT COUNT(*) AS total FROM usuarios"); echo $r->fetch_assoc()["total"] . PHP_EOL;'
```

`config/database.php` está ignorado por Git para no subir credenciales reales.
