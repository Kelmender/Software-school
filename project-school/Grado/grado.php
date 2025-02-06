<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Grados</title>
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
            max-width: 800px;
            margin: 0 auto;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        h2 {
            color: #2d6a4f;
            text-align: center;
            margin-bottom: 30px;
        }

        .form-container {
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 10px;
        }

        .input-group {
            margin-bottom: 15px;
        }

        .input-group label {
            display: block;
            margin-bottom: 5px;
            color: #2d6a4f;
        }

        .input-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #52b788;
            border-radius: 5px;
        }

        .btn-agregar {
            background: #52b788;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-agregar:hover {
            background: #2d6a4f;
            transform: scale(1.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #52b788;
            color: white;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .btn-eliminar {
            background: #dc3545;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-right: 5px;
        }

        .btn-eliminar:hover {
            background: #c82333;
            transform: scale(1.05);
        }

        .btn-ver {
            background: #007bff;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-ver:hover {
            background: #0056b3;
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <?php
    require_once "../db.php";
    //require_once "../Dashboard/dashboard.php"
    
    // Procesar el formulario de agregar grado
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if(isset($_POST['nombre']) && !empty($_POST['nombre'])) {
            $nombre = trim($_POST['nombre']);
            
            try {
                $stmt = $conn->prepare("INSERT INTO grados (nombre) VALUES (:nombre)");
                $stmt->bindParam(':nombre', $nombre);
                
                if($stmt->execute()) {
                    echo "<script>
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: 'Grado agregado exitosamente',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.href = '" . $_SERVER['PHP_SELF'] . "';
                        });
                    </script>";
                } else {
                    throw new PDOException("Error al ejecutar la consulta");
                }
            } catch(PDOException $e) {
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al agregar el grado: " . $e->getMessage() . "'
                    });
                </script>";
            }
        }
    }

    // Procesar eliminación mediante AJAX
    if (isset($_GET['eliminar'])) {
        $grado_id = $_GET['eliminar'];
        try {
            $stmt = $conn->prepare("DELETE FROM grados WHERE grado_id = :grado_id");
            $stmt->bindParam(':grado_id', $grado_id);
            $stmt->execute();
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: '¡Eliminado!',
                    text: 'El grado ha sido eliminado exitosamente',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = '" . $_SERVER['PHP_SELF'] . "';
                });
            </script>";
        } catch(PDOException $e) {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al eliminar el grado: " . $e->getMessage() . "'
                });
            </script>";
        }
    }

    // Obtener todos los grados
    try {
        $stmt = $conn->query("SELECT * FROM grados ORDER BY nombre");
        $grados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error: " . $e->getMessage() . "'
            });
        </script>";
    }
    ?>

    <div class="container">
        <h2>Gestión de Grados</h2>
        
        <div class="form-container">
            <form method="POST" action="">
                <div class="input-group">
                    <label for="nombre">Nombre del Grado:</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <button type="submit" class="btn-agregar">
                    <i class="fas fa-plus"></i> Agregar Grado
                </button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre del Grado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($grados as $grado): ?>
                <tr>
                    <td><?php echo htmlspecialchars($grado['grado_id']); ?></td>
                    <td><?php echo htmlspecialchars($grado['nombre']); ?></td>
                    <td>
                        <button class="btn-eliminar" onclick="eliminarGrado(<?php echo $grado['grado_id']; ?>)">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                        <button class="btn-ver" onclick="window.location.href='../Estudiantes/estudiantes.php?grado_id=<?php echo $grado['grado_id']; ?>'">
                            <i class="fas fa-search"></i> Ver Estudiantes
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        function eliminarGrado(gradoId) {
            Swal.fire({
                title: '¿Está seguro?',
                text: "¿Desea eliminar este grado?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '?eliminar=' + gradoId;
                }
            });
        }
    </script>
</body>
</html>
