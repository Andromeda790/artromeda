<?php
    session_start();
    require_once("verificar_admin.php");
    require_once("conexion.php");
    verificarAdmin(2);
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $res = mysqli_query($conexion, "
            SELECT titulo, id_usuarios 
            FROM obras 
            WHERE id = $id
        ");
        if ($res && mysqli_num_rows($res) > 0) {
            $obra = mysqli_fetch_assoc($res);
            $id_usuarios = $obra['id_usuarios'];
            $titulo = mysqli_real_escape_string($conexion, $obra['titulo']);
            $query_update = "
                UPDATE obras 
                SET 
                    estado = 'aprobada',
                    motivo_rechazo = NULL
                WHERE id = $id
            ";
            if (mysqli_query($conexion, $query_update)) {
                $msg_texto = "¡Felicidades! Tu obra '$titulo' ha sido aprobada y ya está en la galería.";
                $msg_escapado = mysqli_real_escape_string($conexion, $msg_texto);
                $query_notif = "
                    INSERT INTO notificaciones (id_usuarios, mensaje)
                    VALUES ('$id_usuarios', '$msg_escapado')
                ";
                mysqli_query($conexion, $query_notif);
                header("Location: moderar_obras.php?res=aprobada");
                exit();
            } else {
                echo "Error al actualizar la obra: " . mysqli_error($conexion);
            }
        } else {
            echo "Obra no encontrada.";
        }
    } else {
        echo "ID de obra no válido.";
    }
?>