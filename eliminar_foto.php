<?php
    session_start();
    include("conexion.php");
    if (isset($_SESSION['usuario_id'])) {
        $id = $_SESSION['usuario_id'];
        $res = mysqli_query($conexion, "SELECT foto_perfil FROM usuarios WHERE id = '$id'");
        $row = mysqli_fetch_assoc($res);
        if ($row['foto_perfil'] && $row['foto_perfil'] != 'default.jpg') {
            unlink("img_perfiles/" . $row['foto_perfil']);
        }
        mysqli_query($conexion, "UPDATE usuarios SET foto_perfil = 'default.jpg' WHERE id = '$id'");
    }
    header("Location: perfil.php");
    exit();
?>