<?php
    session_start();
    include("conexion.php");
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit();
    }
    $id_usuarios = $_SESSION['usuario_id'];
    $query_foto = "SELECT foto_perfil FROM usuarios WHERE id = '$id_usuarios'";
    $res_foto = mysqli_query($conexion, $query_foto);
    if ($res_foto && mysqli_num_rows($res_foto) > 0) {
        $user_data = mysqli_fetch_assoc($res_foto);
        $nombre_archivo = $user_data['foto_perfil'];
        if (!empty($nombre_archivo) && $nombre_archivo != "icono.jpg") {
            $ruta_foto = "img_perfiles/" . $nombre_archivo;
            if (file_exists($ruta_foto)) {
                unlink($ruta_foto); 
            }
        }
    }
    $query_delete = "DELETE FROM usuarios WHERE id = '$id_usuarios'";
    $res_delete = mysqli_query($conexion, $query_delete);
    if ($res_delete) {
        session_destroy();
        header("Location: index.php?mensaje=cuenta_eliminada");
        exit();
    } else {
        echo "Error al intentar eliminar la cuenta: " . mysqli_error($conexion);
    }
?>