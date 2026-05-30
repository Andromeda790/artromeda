<?php
    session_start();
    include("conexion.php");
    if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit(); }
    $id_usuarios_sesion = $_SESSION['usuario_id'];
    $id_obras = mysqli_real_escape_string($conexion, $_GET['id']);
    $query_artista = "SELECT id FROM artista WHERE id_usuarios = '$id_usuarios_sesion' LIMIT 1";
    $res_artista = mysqli_query($conexion, $query_artista);
    $datos_artista = mysqli_fetch_assoc($res_artista);
    if (!$datos_artista) {
        die("Error: No tienes un perfil de artista configurado.");
    }
    $id_usuarios_real = $datos_artista['id'];
    $query = "SELECT * FROM obras WHERE id = '$id_obras' AND id_usuarios = '$id_usuarios_real'";
    $res = mysqli_query($conexion, $query);
    $obra = mysqli_fetch_assoc($res);
    if (!$obra) { 
        die("Obra no encontrada o no tienes permisos para editarla."); 
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $titulo = mysqli_real_escape_string($conexion, $_POST['titulo']);
        $precio = mysqli_real_escape_string($conexion, $_POST['precio']);
        $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
        if (!empty($_FILES['imagen']['name'])) {
            $nombre_img = time() . "_" . $_FILES['imagen']['name'];
            move_uploaded_file($_FILES['imagen']['tmp_name'], "img_obras/" . $nombre_img);
            if (file_exists("img_obras/" . $obra['imagen'])) {
                unlink("img_obras/" . $obra['imagen']);
            }
            $update_img = ", imagen = '$nombre_img'";
        } else {
            $update_img = "";
        }
        $sql_update = "UPDATE obras SET 
                        titulo = '$titulo', 
                        precio = '$precio', 
                        descripcion = '$descripcion' 
                        $update_img,
                        estado = 'pendiente',
                        motivo_rechazo = NULL
                      WHERE id = '$id_obras' AND id_usuarios = '$id_usuarios_real'";
        if (mysqli_query($conexion, $sql_update)) {
            header("Location: artista.php?msj=editado");
            exit();
        } else {
            echo "Error al actualizar: " . mysqli_error($conexion);
        }
    }
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Editar Obra - Artrómeda</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <style>
            body { 
                background: #0a0a0a; 
                color: white; 
                font-family: sans-serif; 
                padding: 5%; 
            }

            .form-edit { 
                background: #111; 
                padding: 25px; 
                border: 1px solid #deb887; 
                border-radius: 10px; 
                max-width: 600px; 
                margin: auto; 
            }

            .rechazo-box { 
                background: rgba(255,0,0,0.1); 
                border: 1px solid #ff4d4d; 
                color: #ff4d4d; 
                padding: 15px; 
                border-radius: 5px; 
                margin-bottom: 20px; 
                font-size: 0.9rem; 
            }

            input, textarea { 
                width: 100%; 
                padding: 10px; 
                margin: 10px 0; 
                background: #222; 
                border: 1px solid #444; 
                color: white; 
            }

            .btn-save { 
                background: #deb887; 
                color: black; 
                border: none; 
                padding: 12px; 
                width: 100%; 
                font-weight: bold; 
                cursor: pointer; 
            }
        </style>
    </head>
    <body>
        <div class="form-edit">
            <h2>Editar Obra</h2>
            <?php if($obra['estado'] == 'rechazado'): ?>
                <div class="rechazo-box">
                    <i class="bi bi-info-circle"></i> <strong>Motivo del rechazo:</strong><br>
                    <?php echo $obra['motivo_rechazo']; ?>
                </div>
            <?php endif; ?>
            <form action="" method="POST" enctype="multipart/form-data">
                <label>Título</label>
                <input type="text" name="titulo" value="<?php echo $obra['titulo']; ?>" required>
                <label>Precio (COP)</label>
                <input type="number" name="precio" value="<?php echo $obra['precio']; ?>" required>
                <label>Descripción</label>
                <textarea name="descripcion" rows="4"><?php echo $obra['descripcion']; ?></textarea>
                <label>Imagen (dejar vacío para mantener la actual)</label>
                <input type="file" name="imagen">
                <button type="submit" class="btn-save">GUARDAR CAMBIOS Y RE-ENVIAR A REVISIÓN</button>
                <a href="artista.php" style="display:block; text-align:center; color:#888; margin-top:15px; text-decoration:none;">Cancelar</a>
            </form>
        </div>
    </body>
</html>