<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estudiantes por Grado</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #52b788, #2d6a4f);
            margin: 0;
            padding: 20px;
        }

        .container {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 20px;
            max-width: 1000px;
            margin: 0 auto;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.5s ease-in;
        }

        .form-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #2d6a4f;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #52b788;
            border-radius: 5px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-success {
            background: #52b788;
        }

        .btn-danger {
            background: #dc3545;
        }

        .btn:hover {
            transform: scale(1.05);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            color: #2d6a4f;
            text-align: center;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #52b788;
            color: white;
            font-weight: 600;
        }

        tr {
            transition: all 0.3s ease;
        }

        tr:hover {
            background-color: #f5f5f5;
            transform: scale(1.01);
        }

        .student-icon {
            margin-right: 10px;
            color: #2d6a4f;
        }

        .empty-message {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <?php
    require_once '../db.php';

    // Procesar formulario de agregar/editar estudiante
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if(isset($_POST['nombres']) && isset($_POST['apellidos']) && isset($_POST['grado_id'])) {
            $nombres = trim($_POST['nombres']);
            $apellidos = trim($_POST['apellidos']);
            $grado_id = $_POST['grado_id'];
            $estudiante_id = isset($_POST['estudiante_id']) ? $_POST['estudiante_id'] : null;

            try {
                $conn->beginTransaction();

                if($estudiante_id) { // Editar
                    $stmt = $conn->prepare("UPDATE estudiantes SET nombres = :nombres, apellidos = :apellidos WHERE estudiante_id = :estudiante_id");
                    $stmt->execute([':nombres' => $nombres, ':apellidos' => $apellidos, ':estudiante_id' => $estudiante_id]);
                    
                    $stmt = $conn->prepare("UPDATE estudiante_grado SET grado_id = :grado_id WHERE estudiante_id = :estudiante_id");
                    $stmt->execute([':grado_id' => $grado_id, ':estudiante_id' => $estudiante_id]);
                } else { // Agregar
                    $stmt = $conn->prepare("INSERT INTO estudiantes (nombres, apellidos) VALUES (:nombres, :apellidos)");
                    $stmt->execute([':nombres' => $nombres, ':apellidos' => $apellidos]);
                    
                    $estudiante_id = $conn->lastInsertId();
                    $stmt = $conn->prepare("INSERT INTO estudiante_grado (estudiante_id, grado_id) VALUES (:estudiante_id, :grado_id)");
                    $stmt->execute([':estudiante_id' => $estudiante_id, ':grado_id' => $grado_id]);
                }

                $conn->commit();
                echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: 'Estudiante " . ($estudiante_id ? 'actualizado' : 'agregado') . " correctamente',
                        showConfirmButton: false,
                        timer: 1500
                    });
                </script>";
            } catch(PDOException $e) {
                $conn->rollBack();
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al procesar el estudiante: " . $e->getMessage() . "'
                    });
                </script>";
            }
        }
    }

    // Procesar eliminación
    if(isset($_POST['eliminar_estudiante'])) {
        $estudiante_id = $_POST['eliminar_estudiante'];
        try {
            $conn->beginTransaction();
            
            $stmt = $conn->prepare("DELETE FROM estudiante_grado WHERE estudiante_id = :estudiante_id");
            $stmt->execute([':estudiante_id' => $estudiante_id]);
            
            $stmt = $conn->prepare("DELETE FROM estudiantes WHERE estudiante_id = :estudiante_id");
            $stmt->execute([':estudiante_id' => $estudiante_id]);
            
            $conn->commit();
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: '¡Eliminado!',
                    text: 'Estudiante eliminado correctamente',
                    showConfirmButton: false,
                    timer: 1500
                });
            </script>";
        } catch(PDOException $e) {
            $conn->rollBack();
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al eliminar el estudiante: " . $e->getMessage() . "'
                });
            </script>";
        }
    }

    // Obtener todos los grados para el select
    try {
        $stmt = $conn->query("SELECT * FROM grados ORDER BY nombre");
        $grados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al obtener los grados: " . $e->getMessage() . "'
            });
        </script>";
    }

    // Mostrar estudiantes del grado seleccionado
    if(isset($_GET['grado_id'])) {
        $grado_id = $_GET['grado_id'];
        
        try {
            $stmt_grado = $conn->prepare("SELECT nombre FROM grados WHERE grado_id = :grado_id");
            $stmt_grado->bindParam(':grado_id', $grado_id);
            $stmt_grado->execute();
            $grado = $stmt_grado->fetch(PDO::FETCH_ASSOC);

            $stmt = $conn->prepare("
                SELECT e.estudiante_id, e.nombres, e.apellidos 
                FROM estudiantes e 
                INNER JOIN estudiante_grado eg ON e.estudiante_id = eg.estudiante_id 
                WHERE eg.grado_id = :grado_id 
                ORDER BY e.apellidos, e.nombres
            ");
            $stmt->bindParam(':grado_id', $grado_id);
            $stmt->execute();
            $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
            <div class="container">
                <h2>Estudiantes de <?php echo htmlspecialchars($grado['nombre']); ?></h2>

                <div class="form-container">
                    <form method="POST" id="studentForm">
                        <div class="form-group">
                            <label for="nombres">Nombres:</label>
                            <input type="text" id="nombres" name="nombres" required>
                        </div>
                        <div class="form-group">
                            <label for="apellidos">Apellidos:</label>
                            <input type="text" id="apellidos" name="apellidos" required>
                        </div>
                        <div class="form-group">
                            <label for="grado_id">Grado:</label>
                            <select name="grado_id" id="grado_id" required>
                                <?php foreach($grados as $g): ?>
                                    <option value="<?php echo $g['grado_id']; ?>" <?php echo ($g['grado_id'] == $grado_id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($g['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-plus"></i> Agregar Estudiante
                        </button>
                    </form>
                </div>

                <?php if($estudiantes): ?>
                <table>
                    <thead>
                        <tr>
                            <th><i class="fas fa-user student-icon"></i>Nombres</th>
                            <th><i class="fas fa-user student-icon"></i>Apellidos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($estudiantes as $estudiante): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($estudiante['nombres']); ?></td>
                            <td><?php echo htmlspecialchars($estudiante['apellidos']); ?></td>
                            <td>
                                <button class="btn btn-danger" onclick="eliminarEstudiante(<?php echo $estudiante['estudiante_id']; ?>)">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div class="empty-message">
                        <i class="fas fa-info-circle"></i>
                        No hay estudiantes registrados en este grado.
                    </div>
                <?php endif; ?>
            </div>

            <script>
                function eliminarEstudiante(estudianteId) {
                    Swal.fire({
                        title: '¿Está seguro?',
                        text: "Esta acción no se puede deshacer",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.innerHTML = `<input type="hidden" name="eliminar_estudiante" value="${estudianteId}">`;
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                }
            </script>
    <?php
        } catch(PDOException $e) {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al obtener los estudiantes: " . $e->getMessage() . "'
                });
            </script>";
        }
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se especificó un grado'
            });
        </script>";
    }
    ?>
</body>
</html>
