<?php
$projectRoot = dirname(__DIR__);
$autoload = $projectRoot . '/vendor/autoload.php';

if (is_file($autoload)) {
        require_once $autoload;

        if (class_exists('Dotenv\\Dotenv')) {
                Dotenv\Dotenv::createImmutable($projectRoot)->safeLoad();
        }
}

$config = [];
$configFile = getenv('SERIES_CONFIG_FILE');

if ($configFile === false && isset($_ENV['SERIES_CONFIG_FILE'])) {
        $configFile = $_ENV['SERIES_CONFIG_FILE'];
}

$configFiles = [];

if (is_string($configFile) && $configFile !== '') {
        $configFiles[] = $configFile;
}

$configFiles[] = $projectRoot . '/config.local.php';
$configFiles[] = __DIR__ . '/config.local.php';

foreach ($configFiles as $file) {
        if (is_file($file) && is_readable($file)) {
                $localConfig = require $file;
                if (is_array($localConfig)) {
                        $config = array_merge($config, $localConfig);
                        break;
                }
        }
}

$environmentNames = [
        'host' => 'BD_HOST',
        'usuario' => 'BD_USUARIO',
        'password' => 'BD_PASSWORD',
        'nombre' => 'BD_NOMBRE',
];

foreach ($environmentNames as $key => $name) {
        $value = getenv($name);
        if ($value === false && isset($_ENV[$name])) {
                $value = $_ENV[$name];
        }
        if (is_string($value) && $value !== '') {
                $config[$key] = $value;
        }
}

foreach ($environmentNames as $key => $name) {
        if (!isset($config[$key]) || !is_string($config[$key]) || $config[$key] === '') {
                error_log("Falta la configuración de base de datos: {$name}");
                http_response_code(500);
                exit('La configuración de la base de datos está incompleta.');
        }
}

define('BD_HOST', $config['host']);
define('BD_USUARIO', $config['usuario']);
define('BD_PASSWORD', $config['password']);
define('BD_NOMBRE', $config['nombre']);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
        $conn = new mysqli(BD_HOST, BD_USUARIO, BD_PASSWORD, BD_NOMBRE);
        $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $exception) {
        error_log($exception->getMessage());
        http_response_code(500);
        exit('No se pudo conectar con la base de datos.');
}

