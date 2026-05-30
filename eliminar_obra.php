<?php
    session_start();
    include("conexion.php");
    if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'artista' && isset($_GET['id'])) {
        $id_obras = mysqli_real_escape_string($conexion, $_GET['id']);
        $id_usuarios = $_SESSION['usuario_id'];
        $query_img = "SELECT imagen FROM obras WHERE id = '$id_obras' AND id_usuarios = '$id_usuarios'";
        $res_img = mysqli_query($conexion, $query_img);
        if ($row = mysqli_fetch_assoc($res_img)) {
            $nombre_imagen = $row['imagen'];
            $ruta_imagen = "img_obras/" . $nombre_imagen;
            $sql_delete = "DELETE FROM obras WHERE id = '$id_obras' AND id_usuarios = '$id_usuarios'";
            if (mysqli_query($conexion, $sql_delete)) {
                if (file_exists($ruta_imagen)) {
                    unlink($ruta_imagen);
                }
                header("Location: artista.php?msj=obra_eliminada");
            } else {
                echo "Error al eliminar en la base de datos: " . mysqli_error($conexion);
            }
        } else {
            echo "No tienes permiso para eliminar esta obra o no existe.";
        }
    } else {
        header("Location: artista.php");
    }
?>