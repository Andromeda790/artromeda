<?php
    session_start();
    include("conexion.php");
    if (isset($_POST['id_obras']) && isset($_SESSION['usuario_id'])) {
        $id_obras = mysqli_real_escape_string($conexion, $_POST['id_obras']);
        $id_usuarios = $_SESSION['usuario_id'];
        $check = mysqli_query($conexion, "SELECT id FROM favoritos WHERE id_obras = '$id_obras' AND id_usuarios = '$id_usuarios'");
        if (mysqli_num_rows($check) == 0) {
            $query = "INSERT INTO favoritos (id_obras, id_usuarios) VALUES ('$id_obras', '$id_usuarios')";
            if (mysqli_query($conexion, $query)) {
                $res_count = mysqli_query($conexion, "SELECT COUNT(*) as total FROM favoritos WHERE id_usuarios = '$id_usuarios'");
                $data = mysqli_fetch_assoc($res_count);
                echo $data['total'];
            }
        } else {
            echo "exists";
        }
    } else {
        echo "error_auth";
    }
?>