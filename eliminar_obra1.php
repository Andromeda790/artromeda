<?php
    session_start();
    include("conexion.php");
    if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'artista' && isset($_GET['id'])) {
        $id_obras = mysqli_real_escape_string($conexion, $_GET['id']);
        $id_usuarios_sesion = $_SESSION['usuario_id'];
        $query_artista = "SELECT id FROM artista WHERE id_usuarios = '$id_usuarios_sesion' LIMIT 1";
        $res_artista = mysqli_query($conexion, $query_artista);
        $datos_artista = mysqli_fetch_assoc($res_artista);
        if (!$datos_artista) {
            die("Error: Perfil de artista no encontrado.");
        }
        $id_usuarios_real = $datos_artista['id'];
        $query_img = "SELECT imagen FROM obras WHERE id = '$id_obras' AND id_usuarios = '$id_usuarios_real'";
        $res_img = mysqli_query($conexion, $query_img);
        if ($row = mysqli_fetch_assoc($res_img)) {
            $nombre_imagen = $row['imagen'];
            $ruta_imagen = "img_obras/" . $nombre_imagen;
            $sql_delete = "DELETE FROM obras WHERE id = '$id_obras' AND id_usuarios = '$id_usuarios_real'";
            if (mysqli_query($conexion, $sql_delete)) {
                if (!empty($nombre_imagen) && file_exists($ruta_imagen)) {
                    unlink($ruta_imagen);
                }
                header("Location: artista.php?msj=obra_eliminada");
                exit();
            } else {
                echo "Error al eliminar en la base de datos: " . mysqli_error($conexion);
            }
        } else {
            echo "No tienes permiso para eliminar esta obra o la obra no existe con tu ID de artista ($id_usuarios_real).";
        }
    } else {
        header("Location: artista.php");
        exit();
    }
?>