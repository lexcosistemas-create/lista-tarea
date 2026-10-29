<?php

require_once __DIR__ . "/database.php";
$titulo = "Agendas de Tareas";
function escaparHtml(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, "UTF-8");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nuevaTarea = trim($_POST["pendiente"] ?? "");
    $accion = $_POST["accion"] ?? "agregar";

    if ($accion === "eliminar") {
        $id = (int) ($_POST["id"] ?? 0);
        $consulta = $pdo->prepare("DELETE FROM tareas WHERE id = ?");
        $consulta->execute([$id]);
    } elseif ($accion === "completar") {
        $id = (int) ($_POST["id"] ?? 0);
        $consulta = $pdo->prepare(
            "UPDATE tareas SET completada = 1 WHERE id = ?"
        );
        $consulta->execute([$id]);
        } elseif ($accion === "pendiente") {
    $id = (int) ($_POST["id"] ?? 0);
    $consulta = $pdo->prepare(
        "UPDATE tareas SET completada = 0 WHERE id = ?"
    );
    $consulta->execute([$id]);
       } elseif ($accion === "actualizar_fecha") {
    $id = (int) ($_POST["id"] ?? 0);

    $fechaTarea = $_POST["fecha_tarea"] ?? "";
    $fechaTarea = $fechaTarea !== "" ? $fechaTarea : null;

    $tipoFecha = $_POST["tipo_fecha"] ?? "unica";
$fechaFin = null;

if ($tipoFecha === "rango") {
    $fechaFin = $_POST["fecha_fin"] ?? "";
    $fechaFin = $fechaFin !== "" ? $fechaFin : null;
}

    $consulta = $pdo->prepare(
        "UPDATE tareas
         SET fecha_tarea = ?, fecha_fin = ?
         WHERE id = ?"
    );
    $consulta->execute([$fechaTarea, $fechaFin, $id]);
    } elseif ($accion === "editar") {
        $id = (int) ($_POST["id"] ?? 0);
        $descripcion = trim($_POST["descripcion"] ?? "");

        if ($descripcion !== "") {
            $consulta = $pdo->prepare(
                "UPDATE tareas SET descripcion = ? WHERE id = ?"
            );
            $consulta->execute([$descripcion, $id]);
        }
    }

    if ($nuevaTarea !== "") {
        $tipoFechaNueva = $_POST["tipo_fecha"] ?? "unica";

$fechaTareaNueva = $_POST["fecha_tarea"] ?? "";
$fechaTareaNueva = $fechaTareaNueva !== "" ? $fechaTareaNueva : null;

$fechaFinNueva = null;

if ($tipoFechaNueva === "rango") {
    $fechaFinNueva = $_POST["fecha_fin"] ?? "";
    $fechaFinNueva = $fechaFinNueva !== "" ? $fechaFinNueva : null;
}

$consulta = $pdo->prepare(
    "INSERT INTO tareas (descripcion, fecha_creacion, fecha_tarea, fecha_fin)
     VALUES (?, datetime('now', 'localtime'), ?, ?)"
);
$consulta->execute([
    $nuevaTarea,
    $fechaTareaNueva,
    $fechaFinNueva
]);
    }
}
$estado = $_GET["estado"] ?? "todas";
$busqueda = trim($_GET["buscar"] ?? "");

if ($estado === "pendientes") {
    $sql = "SELECT id, descripcion, completada, fecha_creacion, fecha_tarea, fecha_fin FROM tareas
            WHERE completada = 0 AND descripcion LIKE ?
            ORDER BY id DESC";
} elseif ($estado === "completadas") {
    $sql = "SELECT id, descripcion, completada, fecha_creacion, fecha_tarea, fecha_fin FROM tareas
            WHERE completada = 1 AND descripcion LIKE ?
            ORDER BY id DESC";
} else {
    $sql = "SELECT id, descripcion, completada, fecha_creacion, fecha_tarea, fecha_fin FROM tareas
            WHERE descripcion LIKE ?
            ORDER BY id DESC";
}

$consulta = $pdo->prepare($sql);
$consulta->execute(["%" . $busqueda . "%"]);
$tareas = $consulta->fetchAll(PDO::FETCH_ASSOC);
$consultaPendientes = $pdo->query(
    "SELECT COUNT(*) FROM tareas WHERE completada = 0"
);
$consultaCompletadas = $pdo->query(
    "SELECT COUNT(*) FROM tareas WHERE completada = 1"
);
$totalPendientes = $consultaPendientes->fetchColumn();
$totalCompletadas = $consultaCompletadas->fetchColumn();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $titulo ?></title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body>
    <h1><?= $titulo ?></h1>
    <p>PHP está funcionando. Este texto se genera desde una variable.</p>

    <h2>Mis primeras tareas</h2>
    <p>Tareas pendientes: <?= $totalPendientes ?></p>
    <p>Tareas Completadas: <?= $totalCompletadas ?></p>
 <h2>Agregar una tarea</h2>
    <form method="post" class="form-fecha-nueva">
        <label for="tarea">Escribe una tarea:</label>
        <input type="text" id="tarea" name="pendiente" required>
       <label>
    Tipo de fecha:
    <select name="tipo_fecha" class="tipo-fecha">
        <option value="unica">Fecha única</option>
        <option value="rango">Rango</option>
    </select>
</label>

<label>
    Fecha(s):
    <input
        type="text"
        class="calendario-rango"
        placeholder="Selecciona en el calendario"
        readonly
    >
</label>

<input type="hidden" name="fecha_tarea" value="">
<input type="hidden" name="fecha_fin" value="">
        <button type="submit">Agregar</button>
    </form>
    <form method="get">
        <select name="estado">
        <option value="todas" <?= $estado === "todas" ? "selected" : "" ?>>
    Todas
    </option>
    <option value="pendientes">Pendientes</option>
    <option value="completadas">Completadas</option>
    </select>
    <input
    type="text"
    name="buscar"
    placeholder="Buscar tarea"
    value="<?= escaparHtml($_GET["buscar"] ?? "") ?>"
>
    <button type="submit">Filtrar</button>
    </form>

     <?php if (count($tareas) === 0): ?>
    <p>No hay tareas en esta categoría.</p>
<?php endif; ?>
    <ul>
        <?php foreach ($tareas as $indice => $tarea): ?>
           <li class="<?= (int) $tarea["completada"] === 1 ? "tarea-completada" : "" ?>">
            <small class="fecha-creacion">
    Creada:
    <?= !empty($tarea["fecha_creacion"])
        ? escaparHtml(date("d/m/Y H:i", strtotime($tarea["fecha_creacion"])))
        : "Sin dato" ?>
</small>
    <span class="descripcion">
    <?= escaparHtml($tarea["descripcion"]) ?>
</span>
    <form method="post">
    <input type="hidden" name="accion" value="editar">
    <input type="hidden" name="id" value="<?= $tarea["id"] ?>">
    <input
        type="text"
        name="descripcion"
        value="<?= escaparHtml($tarea["descripcion"]) ?>"
        required
    >
    <button type="submit">Guardar cambios</button>
</form>
    <?php if ((int) $tarea["completada"] === 1): ?>
    <form method="post">
    <input type="hidden" name="accion" value="pendiente">
    <input type="hidden" name="id" value="<?= $tarea["id"] ?>">
    <button class="pendiente" type="submit">Marcar como pendiente</button>
</form>
<?php else: ?>
    <form method="post">
        <input type="hidden" name="accion" value="completar">
        <input type="hidden" name="id" value="<?= $tarea["id"] ?>">
        <button class="completar" type="submit">Marcar como completada</button>
    </form>
<?php endif; ?>

    <form method="post">
        <input type="hidden" name="accion" value="eliminar">
        <input type="hidden" name="id" value="<?= $tarea["id"] ?>">
        <button class="eliminar" type="submit">Eliminar</button>
    </form>
   <form method="post" class="fecha-tarea">
    <input type="hidden" name="accion" value="actualizar_fecha">
    <input type="hidden" name="id" value="<?= $tarea["id"] ?>">

    <label>
        Tipo:
        <select name="tipo_fecha" class="tipo-fecha">
            <option value="unica" <?= empty($tarea["fecha_fin"]) ? "selected" : "" ?>>
                Fecha única
            </option>
            <option value="rango" <?= !empty($tarea["fecha_fin"]) ? "selected" : "" ?>>
                Rango
            </option>
        </select>
    </label>

    <label>
        Fecha(s):
        <input
            type="text"
            class="calendario-rango"
            placeholder="Selecciona en el calendario"
            readonly
        >
    </label>

    <input
        type="hidden"
        name="fecha_tarea"
        value="<?= escaparHtml($tarea["fecha_tarea"] ?? "") ?>"
    >
    <input
        type="hidden"
        name="fecha_fin"
        value="<?= escaparHtml($tarea["fecha_fin"] ?? "") ?>"
    >

    <button type="submit">Guardar fecha</button>
</form>
</li>
        <?php endforeach; ?>
    </ul>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script>
document.querySelectorAll(".fecha-tarea, .form-fecha-nueva").forEach((formulario) => {
    const selector = formulario.querySelector(".tipo-fecha");
    const campoCalendario = formulario.querySelector(".calendario-rango");
    const campoInicio = formulario.querySelector('input[name="fecha_tarea"]');
    const campoFin = formulario.querySelector('input[name="fecha_fin"]');

    const fechasGuardadas = selector.value === "rango"
        ? [campoInicio.value, campoFin.value].filter(Boolean)
        : campoInicio.value || null;

    const calendario = flatpickr(campoCalendario, {
        locale: "es",
        mode: selector.value === "rango" ? "range" : "single",
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d/m/Y",
        defaultDate: fechasGuardadas,

        onChange: function (fechasElegidas) {
            campoInicio.value = fechasElegidas[0]
                ? calendario.formatDate(fechasElegidas[0], "Y-m-d")
                : "";

            campoFin.value = fechasElegidas.length === 2
                ? calendario.formatDate(fechasElegidas[1], "Y-m-d")
                : "";
        }
    });

    selector.addEventListener("change", () => {
        const fechaInicio = campoInicio.value;

        campoFin.value = "";
        calendario.set(
            "mode",
            selector.value === "rango" ? "range" : "single"
        );

        calendario.setDate(
            selector.value === "rango"
                ? (fechaInicio ? [fechaInicio] : [])
                : (fechaInicio || null),
            false
        );
    });

    formulario.addEventListener("submit", (evento) => {
        if (
            selector.value === "rango" &&
            (!campoInicio.value || !campoFin.value)
        ) {
            evento.preventDefault();
            alert("Elige la fecha de inicio y la fecha final en el calendario.");
        }
    });
});
</script>
    <footer>
  <p>&copy; 2026 Mi Sitio Web. Todos los derechos reservados.</p>
  <ul>
    <li><a href="#inicio">Inicio</a></li>
    <li><a href="#contacto">Contacto</a></li>
    <li><a href="#privacidad">Política de Privacidad</a></li>
  </ul>
</footer>

</body>
</html>
