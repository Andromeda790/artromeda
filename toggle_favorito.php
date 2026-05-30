<?php
    session_start();
    include("conexion.php"); 
    if (isset($_POST['id']) && isset($_SESSION['usuario_id'])) {
        $id_obras = (int)$_POST['id'];
        $id_usuarios = (int)$_SESSION['usuario_id'];
        $check = "SELECT id FROM favoritos WHERE id_obras = '$id_obras' AND id_usuarios = '$id_usuarios'";
        $res = mysqli_query($conexion, $check);
        if (mysqli_num_rows($res) > 0) {
            $delete = "DELETE FROM favoritos WHERE id_obras = '$id_obras' AND id_usuarios = '$id_usuarios'";
            if (mysqli_query($conexion, $delete)) {
                echo "removed";
            }
        } else {
            $insert = "INSERT INTO favoritos (id_obras, id_usuarios) VALUES ('$id_obras', '$id_usuarios')";
            if (mysqli_query($conexion, $insert)) {
                echo "added";
            }
        }
    } else {
        echo "error_auth"; 
    }
    exit;
?>