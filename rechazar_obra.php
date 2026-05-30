<?php
    session_start();
    require_once("verificar_admin.php");
    require_once("conexion.php");
    verificarAdmin(2);
    if (isset($_GET['id']) && isset($_GET['motivo'])) {
        $id = mysqli_real_escape_string($conexion, $_GET['id']);
        $res = mysqli_query($conexion, "SELECT titulo, id_usuarios FROM obras WHERE id = '$id'");
        $obra = mysqli_fetch_assoc($res);
        if ($obra) {
            $id_usuarios = $obra['id_usuarios'];
            $titulo = mysqli_real_escape_string($conexion, $obra['titulo']);
            $motivo = mysqli_real_escape_string($conexion, $_GET['motivo']);
            $query = "UPDATE obras SET estado = 'rechazado', motivo_rechazo = '$motivo' WHERE id = '$id'";
            if (mysqli_query($conexion, $query)) {
                $msg_texto = "Tu obra '$titulo' ha sido rechazada. Motivo: $motivo";
                $msg_escapado = mysqli_real_escape_string($conexion, $msg_texto);
                $query_notif = "INSERT INTO notificaciones (id_usuarios, mensaje) VALUES ('$id_usuarios', '$msg_escapado')";
                if(mysqli_query($conexion, $query_notif)){
                    header("Location: moderar_obras.php?res=rechazada");
                    exit();
                } else {
                    echo "Error en notificación: " . mysqli_error($conexion);
                }
            } else {
                echo "Error al actualizar: " . mysqli_error($conexion);
            }
        } else {
            echo "Obra no encontrada.";
        }
    } else {
        header("Location: moderar_obras.php");
    }
    exit();
?>