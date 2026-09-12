<?php
$https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
$trustProxyHeaders = getenv('APP_TRUST_PROXY_HTTPS') === '1';

if ($trustProxyHeaders && ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') {
    $https = true;
}

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $https,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
require_once __DIR__ . '/config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$mensaje = null;

function textoPost(string $nombre): string
{
    $valor = $_POST[$nombre] ?? '';
    return is_string($valor) ? trim($valor) : '';
}

function longitud(string $valor): int
{
    return function_exists('mb_strlen') ? mb_strlen($valor, 'UTF-8') : strlen($valor);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        exit('Solicitud no válida.');
    }

    if (isset($_POST['insertar'])) {
        $titulo = textoPost('titulo');
        $genero = textoPost('genero');

        if ($titulo === '' || longitud($titulo) > 255 || longitud($genero) > 100) {
            $mensaje = 'El título es obligatorio y los campos tienen una longitud no válida.';
        } else {
            $stmt = null;

            try {
                $stmt = $conn->prepare('INSERT INTO series (titulo, genero) VALUES (?, ?)');
                $stmt->bind_param('ss', $titulo, $genero);
                $stmt->execute();
                $stmt->close();
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            } catch (mysqli_sql_exception $exception) {
                error_log($exception->getMessage());
                if ($stmt) {
                    $stmt->close();
                }
                $mensaje = 'No se pudo insertar la serie.';
            }
        }
    }

    if (isset($_POST['borrar'])) {
        $idBorrar = filter_input(INPUT_POST, 'id_borrar', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        if ($idBorrar === false || $idBorrar === null) {
            $mensaje = 'El ID debe ser un número positivo.';
        } else {
            $stmt = null;

            try {
                $stmt = $conn->prepare('DELETE FROM series WHERE id = ?');
                $stmt->bind_param('i', $idBorrar);
                $stmt->execute();
                $eliminadas = $stmt->affected_rows;
                $stmt->close();

                if ($eliminadas > 0) {
                    header('Location: ' . $_SERVER['PHP_SELF']);
                    exit;
                }
                $mensaje = 'No se encontró una serie con ese ID.';
            } catch (mysqli_sql_exception $exception) {
                error_log($exception->getMessage());
                if ($stmt) {
                    $stmt->close();
                }
                $mensaje = 'No se pudo borrar la serie.';
            }
        }
    }
}

$resultado = null;

try {
    $resultado = $conn->query('SELECT id, titulo, genero FROM series ORDER BY id');
} catch (mysqli_sql_exception $exception) {
    error_log($exception->getMessage());
    $mensaje = 'No se pudieron cargar las series.';
}

function escapar(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Series</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<h2>Añadir Nueva Serie</h2>
<form method="post">
    <input type="hidden" name="csrf_token" value="<?= escapar($_SESSION['csrf_token']) ?>">
    <label>Título: <input type="text" name="titulo" maxlength="255" required></label>
    <label>Género: <input type="text" name="genero" maxlength="100"></label>
    <button type="submit" name="insertar">Insertar</button>
</form>

<h2>Eliminar Serie por ID</h2>
<form method="post">
    <input type="hidden" name="csrf_token" value="<?= escapar($_SESSION['csrf_token']) ?>">
    <label>ID: <input type="number" name="id_borrar" min="1" required></label>
    <button type="submit" name="borrar">Borrar</button>
</form>

<?php if ($mensaje !== null): ?>
    <p role="alert"><?= escapar($mensaje) ?></p>
<?php endif; ?>

<h2>Listado de Series</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Título</th>
        <th>Género</th>
    </tr>

    <?php if ($resultado): ?>
        <?php while ($fila = $resultado->fetch_assoc()): ?>
            <tr>
                <td><?= escapar((string) $fila['id']) ?></td>
                <td><?= escapar($fila['titulo']) ?></td>
                <td><?= escapar($fila['genero'] ?? '') ?></td>
            </tr>
        <?php endwhile; ?>
    <?php endif; ?>
</table>

</body>
</html>
<?php $conn->close(); ?>
