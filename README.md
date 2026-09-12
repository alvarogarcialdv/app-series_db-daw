# app-series-daw

Aplicación web sencilla desarrollada en **PHP** para gestionar una colección de series almacenada en **MariaDB o MySQL**.

El proyecto tiene fines didácticos y sirve como ejemplo básico de una aplicación PHP conectada a una base de datos mediante `mysqli`.

## Funcionalidades

La aplicación permite:

* Consultar las series almacenadas.
* Añadir nuevas series indicando título y género.
* Eliminar una serie mediante su identificador.

También incorpora algunas medidas básicas de seguridad:

* Consultas preparadas.
* Validación de los datos recibidos.
* Escape de la salida HTML.
* Protección CSRF en los formularios.
* Cookies de sesión con `HttpOnly` y `SameSite=Lax`.
* Mensajes de error genéricos para evitar mostrar información sensible.

> [!IMPORTANT]
> La aplicación **no incluye autenticación de usuarios**. Está pensada principalmente con fines didácticos y no debe exponerse directamente a Internet sin añadir autenticación y revisar su seguridad para un entorno de producción.

## Requisitos

* PHP 8.0 o superior.
* Extensión PHP `mysqli`.
* MariaDB o MySQL.
* Un servidor web compatible con PHP.

La extensión `mbstring` es recomendable para trabajar correctamente con la longitud de cadenas UTF-8, aunque la aplicación puede funcionar sin ella.

**Composer no es obligatorio.** Solo es necesario si se desea utilizar un archivo `.env` mediante `vlucas/phpdotenv`.

## Estructura del proyecto

```text
app-series-daw/
├── .gitignore
├── config.local.php.example
├── db/
│   └── series.sql
├── README.md
└── src/
    ├── config.php
    ├── index.php
    └── styles.css
```

| Ruta                       | Descripción                                                          |
| -------------------------- | -------------------------------------------------------------------- |
| `src/index.php`            | Interfaz y lógica principal de la aplicación.                        |
| `src/config.php`           | Carga la configuración y establece la conexión con la base de datos. |
| `src/styles.css`           | Estilos de la interfaz.                                              |
| `db/series.sql`            | Crea la tabla `series` e inserta datos iniciales.                    |
| `config.local.php.example` | Ejemplo de archivo de configuración local.                           |

## Instalación

### 1. Obtener el proyecto

Clona el repositorio:

```bash
git clone https://github.com/alvarogarcialdv/app-series-daw.git
cd app-series-daw
```

También puedes descargar el repositorio y copiar sus archivos manualmente al servidor.

### 2. Crear la base de datos

Accede a MariaDB/MySQL con un usuario con permisos suficientes y crea la base de datos:

```sql
CREATE DATABASE app_series
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

Después importa el script incluido en el proyecto:

```bash
mysql -u usuario -p app_series < db/series.sql
```

El script crea la tabla `series` e introduce varios registros de ejemplo.

La tabla contiene los siguientes campos:

| Campo    | Tipo           | Descripción                                      |
| -------- | -------------- | ------------------------------------------------ |
| `id`     | `INT`          | Identificador, clave primaria y autoincremental. |
| `titulo` | `VARCHAR(255)` | Título de la serie. Obligatorio y único.         |
| `genero` | `VARCHAR(100)` | Género de la serie. Opcional.                    |

El título de una serie no puede repetirse debido a la restricción `UNIQUE` definida sobre ese campo.

### 3. Crear un usuario para la aplicación

Se recomienda utilizar un usuario específico para la aplicación en lugar de conectar PHP con un usuario administrador de MariaDB/MySQL.

Por ejemplo:

```sql
CREATE USER 'app_user'@'localhost'
IDENTIFIED BY 'cambia-esta-contraseña';

GRANT SELECT, INSERT, DELETE
ON app_series.*
TO 'app_user'@'localhost';
```

Estos permisos son suficientes para el funcionamiento normal de la aplicación una vez creada la base de datos.

Adapta el host del usuario a la configuración de tu entorno si PHP y MariaDB/MySQL se ejecutan en sistemas diferentes.

## Configuración

No introduzcas credenciales directamente en `src/config.php`.

La aplicación necesita cuatro parámetros:

| Variable      | Descripción                                |
| ------------- | ------------------------------------------ |
| `BD_HOST`     | Servidor donde se encuentra MariaDB/MySQL. |
| `BD_USUARIO`  | Usuario utilizado por la aplicación.       |
| `BD_PASSWORD` | Contraseña del usuario.                    |
| `BD_NOMBRE`   | Nombre de la base de datos.                |

La configuración puede proporcionarse mediante cualquiera de las siguientes opciones.

### Opción A: variables de entorno

Es la opción recomendada para servidores propios, contenedores y plataformas cloud.

```bash
export BD_HOST=127.0.0.1
export BD_USUARIO=app_user
export BD_PASSWORD='contraseña-segura'
export BD_NOMBRE=app_series
```

La forma concreta de definir las variables depende del sistema operativo, servidor o plataforma utilizada.

### Opción B: `config.local.php`

Es una opción sencilla para desarrollo local o entornos donde no sea posible definir variables de entorno.

Copia:

```text
config.local.php.example
```

como:

```text
config.local.php
```

en la raíz del proyecto y modifica sus valores:

```php
<?php

return [
    'host' => '127.0.0.1',
    'usuario' => 'app_user',
    'password' => 'contraseña-segura',
    'nombre' => 'app_series',
];
```

`config.local.php` está incluido en `.gitignore` y no debe almacenarse en el repositorio.

También puede utilizarse un archivo ubicado en otra ruta indicando su ubicación mediante la variable:

```text
SERIES_CONFIG_FILE
```

### Opción C: archivo `.env`

Para utilizar un archivo `.env`, instala `vlucas/phpdotenv`:

```bash
composer require vlucas/phpdotenv
```

Crea después un archivo `.env` en la raíz del proyecto:

```env
BD_HOST=127.0.0.1
BD_USUARIO=app_user
BD_PASSWORD=contraseña-segura
BD_NOMBRE=app_series
```

La aplicación cargará automáticamente `.env` cuando encuentre `vendor/autoload.php` y esté disponible `vlucas/phpdotenv`.

En un entorno de producción pueden instalarse únicamente las dependencias necesarias mediante:

```bash
composer install --no-dev
```

Si el servidor no dispone de Composer, las dependencias pueden instalarse previamente y desplegarse junto con la carpeta `vendor/`.

Los archivos `.env` y `config.local.php`, así como `vendor/`, están excluidos del repositorio mediante `.gitignore`.

### Prioridad de configuración

La aplicación carga primero, si está disponible, el archivo `.env`.

Después busca un archivo PHP de configuración en este orden:

1. Archivo indicado mediante `SERIES_CONFIG_FILE`.
2. `config.local.php` en la raíz del proyecto.
3. `src/config.local.php` como alternativa.

Finalmente, los valores definidos mediante `BD_HOST`, `BD_USUARIO`, `BD_PASSWORD` y `BD_NOMBRE` en el entorno tienen prioridad sobre los valores obtenidos de los archivos de configuración.

Si falta alguno de los cuatro parámetros obligatorios, la aplicación devuelve un error HTTP 500 y no intenta establecer la conexión con la base de datos.

## Directorio público

El único directorio que debe quedar accesible mediante el servidor web es:

```text
src/
```

Configura `src` como **directorio público** de la aplicación.

Una estructura recomendada sería:

```text
/ruta/app-series-daw/
├── config.local.php          # no público
├── db/                       # no público
├── README.md                 # no público
├── vendor/                   # no público, si se utiliza Composer
└── src/                      # directorio público
    ├── config.php
    ├── index.php
    └── styles.css
```

De esta forma quedan fuera del acceso HTTP:

* Las credenciales de conexión.
* El archivo `.env`.
* El script SQL.
* Las dependencias de Composer.
* La documentación y otros archivos del proyecto.

Si el entorno utilizado no permite establecer `src` como directorio público, deben aplicarse las medidas necesarias para impedir el acceso HTTP a los archivos y directorios sensibles.

## HTTPS y proxies inversos

La aplicación marca la cookie de sesión como `Secure` cuando detecta una conexión HTTPS.

Si PHP se ejecuta detrás de un **proxy inverso de confianza** que termina la conexión TLS, puede habilitarse el reconocimiento de `X-Forwarded-Proto` mediante:

```text
APP_TRUST_PROXY_HTTPS=1
```

Activa esta opción únicamente cuando el proxy sea de confianza y envíe correctamente:

```text
X-Forwarded-Proto: https
```

## Consideraciones de seguridad

El proyecto incluye algunas protecciones básicas adecuadas para su finalidad didáctica:

* Consultas preparadas para las operaciones parametrizadas.
* Validación de los datos recibidos.
* Escape de datos antes de mostrarlos en HTML.
* Protección CSRF en las operaciones de inserción y borrado.
* Cookies de sesión con `HttpOnly` y `SameSite=Lax`.
* Uso de `Secure` en la cookie cuando se utiliza HTTPS.
* Registro interno de errores de base de datos sin mostrarlos directamente al usuario.
* Separación entre archivos públicos y archivos de configuración.
* Uso recomendado de un usuario de base de datos con permisos limitados.

La aplicación **no incluye autenticación ni autorización**, por lo que no está preparada para exponerse directamente a Internet tal como se encuentra.

## Objetivo didáctico

El proyecto permite trabajar conceptos como:

* Aplicaciones PHP conectadas a MariaDB/MySQL.
* Uso de `mysqli`.
* Operaciones SQL `SELECT`, `INSERT` y `DELETE`.
* Consultas preparadas.
* Formularios HTML y procesamiento de peticiones `POST`.
* Validación de datos.
* Escape de salida HTML.
* Sesiones.
* Protección CSRF.
* Separación entre código y configuración.
* Variables de entorno.
* Archivos de configuración local.
* Uso opcional de `.env` y Composer.
* Separación entre directorio público y archivos internos.
* Principios básicos de despliegue y seguridad.
