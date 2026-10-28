<?php
$pdo = new PDO("sqlite:" . __DIR__ . "/tareas.sqlite");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec(
    "CREATE TABLE IF NOT EXISTS tareas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        descripcion TEXT NOT NULL,
        completada INTEGER NOT NULL DEFAULT 0
    )"
);
$columnas = $pdo->query("PRAGMA table_info(tareas)")
    ->fetchAll(PDO::FETCH_COLUMN, 1);

if (!in_array("fecha_creacion", $columnas, true)) {
    $pdo->exec("ALTER TABLE tareas ADD COLUMN fecha_creacion TEXT");
}

if (!in_array("fecha_tarea", $columnas, true)) {
    $pdo->exec("ALTER TABLE tareas ADD COLUMN fecha_tarea TEXT");
}
if (!in_array("fecha_fin", $columnas, true)) {
    $pdo->exec("ALTER TABLE tareas ADD COLUMN fecha_fin TEXT");
}
?>