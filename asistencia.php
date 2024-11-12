<?php

$host = 'DESKTOP-CMJN79U\SQLEXPRESS';
$db = 'Asistencia_alumnos';
$user = 'proyecto';
$pass = '12345';

try {
    $pdo = new PDO("sqlsrv:Server=$host;Database=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['registrar'])) {
        if (!empty($_POST['alumno_id']) && !empty($_POST['estado'])) {
            $alumno_id = $_POST['alumno_id'];
            $estado = $_POST['estado'];
            $fecha = date('Y-m-d');

            $sql = "INSERT INTO asistencia (alumno_id, fecha, estado) VALUES (:alumno_id, :fecha, :estado)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['alumno_id' => $alumno_id, 'fecha' => $fecha, 'estado' => $estado]);

            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $message = "Por favor, selecciona un alumno y un estado.";
        }
    } elseif (isset($_POST['actualizar'])) {
        if (!empty($_POST['id']) && !empty($_POST['estado'])) {
            $id = $_POST['id'];
            $estado = $_POST['estado'];

            $sql = "UPDATE asistencia SET estado = :estado WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['estado' => $estado, 'id' => $id]);

            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    } elseif (isset($_POST['eliminar'])) {
        if (!empty($_POST['id'])) {
            $id = $_POST['id'];

            $sql = "DELETE FROM asistencia WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);

            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    } elseif (isset($_POST['crear_alumno'])) {
        if (!empty($_POST['nombre'])) {
            $nombre = $_POST['nombre'];

            $sql = "INSERT INTO alumnos (nombre) VALUES (:nombre)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['nombre' => $nombre]);

            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $message = "Por favor, ingresa un nombre para el alumno.";
        }
    }
}

$sql = "SELECT * FROM alumnos";
$alumnos = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT a.id, a.fecha, al.nombre, a.estado 
        FROM asistencia a 
        JOIN alumnos al ON a.alumno_id = al.id 
        ORDER BY a.fecha DESC";
$asistencias = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Asistencia</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e0f7e0;
            color: #333;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        h1 {
            margin-bottom: 20px;
        }
        .form-container {
            display: flex;
            justify-content: space-between;
            width: 100%;
            max-width: 900px;
        }
        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 10px;
            width: 45%;
        }
        label {
            display: block;
            margin: 10px 0 5px;
        }
        select, input[type="text"], input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .message {
            color: red;
            font-weight: bold;
            margin-top: 20px;
        }
        table {
            margin-top: 20px;
            border-collapse: collapse;
            width: 80%;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        .action-buttons {
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>
    <h1>Registro de Asistencia</h1>

    <?php if (isset($message)): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <div class="form-container">
        <form method="post">
            <h2>Crear Alumno</h2>
            <label for="nombre">Nombre del Alumno:</label>
            <input type="text" name="nombre" id="nombre" required>
            <input type="submit" name="crear_alumno" value="Crear Alumno">
        </form>

        <form method="post">
            <h2>Registrar Asistencia</h2>
            <label for="alumno_id">Seleccionar Alumno:</label>
            <select name="alumno_id" id="alumno_id">
                <option value="">Seleccione un alumno</option>
                <?php foreach ($alumnos as $alumno): ?>
                    <option value="<?= $alumno['id'] ?>"><?= $alumno['nombre'] ?></option>
                <?php endforeach; ?>
            </select>

            <label for="estado">Estado:</label>
            <select name="estado" id="estado">
                <option value="Presente">Presente</option>
                <option value="Ausente">Ausente</option>
            </select>

            <input type="submit" name="registrar" value="Registrar Asistencia">
        </form>
    </div>

    <h2>Asistencia Registrada</h2>
    <table>
        <tr>
            <th>Fecha</th>
            <th>Alumno</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
        <?php foreach ($asistencias as $asistencia): ?>
            <tr>
                <td><?= $asistencia['fecha'] ?></td>
                <td><?= $asistencia['nombre'] ?></td>
                <td><?= $asistencia['estado'] ?></td>
                <td>
                    <div class="action-buttons">
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $asistencia['id'] ?>">
                            <select name="estado">
                                <option value="Presente" <?= $asistencia['estado'] == 'Presente' ? 'selected' : '' ?>>Presente</option>
                                <option value="Ausente" <?= $asistencia['estado'] == 'Ausente' ? 'selected' : '' ?>>Ausente</option>
                            </select>
                            <input type="submit" name="actualizar" value="Actualizar">
                        </form>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $asistencia['id'] ?>">
                            <input type="submit" name="eliminar" value="Eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar este registro?');">
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<!-- Code injected by live-server -->
<script>
	// <![CDATA[  <-- For SVG support
	if ('WebSocket' in window) {
		(function () {
			function refreshCSS() {
				var sheets = [].slice.call(document.getElementsByTagName("link"));
				var head = document.getElementsByTagName("head")[0];
				for (var i = 0; i < sheets.length; ++i) {
					var elem = sheets[i];
					var parent = elem.parentElement || head;
					parent.removeChild(elem);
					var rel = elem.rel;
					if (elem.href && typeof rel != "string" || rel.length == 0 || rel.toLowerCase() == "stylesheet") {
						var url = elem.href.replace(/(&|\?)_cacheOverride=\d+/, '');
						elem.href = url + (url.indexOf('?') >= 0 ? '&' : '?') + '_cacheOverride=' + (new Date().valueOf());
					}
					parent.appendChild(elem);
				}
			}
			var protocol = window.location.protocol === 'http:' ? 'ws://' : 'wss://';
			var address = protocol + window.location.host + window.location.pathname + '/ws';
			var socket = new WebSocket(address);
			socket.onmessage = function (msg) {
				if (msg.data == 'reload') window.location.reload();
				else if (msg.data == 'refreshcss') refreshCSS();
			};
			if (sessionStorage && !sessionStorage.getItem('IsThisFirstTime_Log_From_LiveServer')) {
				console.log('Live reload enabled.');
				sessionStorage.setItem('IsThisFirstTime_Log_From_LiveServer', true);
			}
		})();
	}
	else {
		console.error('Upgrade your browser. This Browser is NOT supported WebSocket for Live-Reloading.');
	}
	// ]]>
</script>
</body>
</html>

