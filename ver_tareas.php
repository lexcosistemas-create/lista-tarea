<?php
require_once __DIR__ . "/database.php";

$consulta = $pdo->query(
    "SELECT id, descripcion FROM tareas ORDER BY id DESC"
);
$tareas = $consulta->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="es">
<body>
    <h1>Tareas guardadas en SQLite</h1>

    <ul>
        <?php foreach ($tareas as $tarea): ?>
            <li><?= htmlspecialchars($tarea["descripcion"], ENT_QUOTES, "UTF-8") ?></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>