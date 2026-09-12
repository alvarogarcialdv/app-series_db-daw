# app_series
Aplicación PHP para añadir, borrar y consultar series almacenadas en MariaDB.

## Requisitos

- PHP 8.0 o superior con la extensión `mysqli` (`mbstring` es recomendable).
- MariaDB o MySQL.

## Despliegue

1. Crea la base de datos:

	```sql
	CREATE DATABASE app_series CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
	```

2. Carga la tabla y los datos iniciales:

	```bash
	mysql -u usuario -p app_series < db/series.sql
	```

3. Configura la conexión usando una de estas opciones. Si se combinan, las variables de entorno tienen prioridad.

### Opción A: variables de entorno

Es la opción recomendada en servidores propios, contenedores y servicios cloud:

	```bash
	export BD_HOST=127.0.0.1
	export BD_USUARIO=usuario
	export BD_PASSWORD='contraseña'
	export BD_NOMBRE=app_series
	```

### Opción B: archivo local fuera de `src`

Es adecuada para un hosting compartido que no permita definir variables de entorno:

1. Copia `config.local.php.example` como `config.local.php` en la raíz del proyecto.
2. Edita sus valores con las credenciales reales.
3. Si es posible, coloca ese archivo fuera del directorio público o configura el sitio para que solo `src` sea público.
4. No lo subas a Git ni lo publiques como un archivo descargable.

También puedes usar una ruta personalizada mediante `SERIES_CONFIG_FILE` cuando el servidor sí permita definir esa única variable.

Si la aplicación está detrás de un proxy HTTPS, define `APP_TRUST_PROXY_HTTPS=1` en el entorno del servidor, antes de iniciar PHP, únicamente cuando el proxy sea de confianza y envíe correctamente `X-Forwarded-Proto: https`.

### Opción C: archivo `.env`

Es habitual en proyectos PHP con Composer. Instala `vlucas/phpdotenv` en la raíz del proyecto durante la preparación del despliegue:

```bash
composer require vlucas/phpdotenv
```

En el servidor, instala únicamente las dependencias de producción:

```bash
composer install --no-dev
```

Si el hosting no tiene Composer, ejecuta ambos comandos localmente y sube también la carpeta `vendor/` junto con la aplicación.

Crea `.env` con este contenido:

```env
BD_HOST=127.0.0.1
BD_USUARIO=app_user
BD_PASSWORD=contraseña-segura
BD_NOMBRE=app_series
```

La aplicación lo cargará automáticamente cuando encuentre `vendor/autoload.php`. Añade `.env` a `.gitignore` y no lo guardes en un directorio público si puedes evitarlo.

La aplicación incluye consultas preparadas, validación de entradas, escape de salida y protección CSRF. No incluye autenticación de usuarios, por lo que no debe exponerse directamente a Internet sin añadirla.
