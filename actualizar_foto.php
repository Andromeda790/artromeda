<?php
    session_start();
    include("conexion.php");
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['foto'])) {
        $id = $_SESSION['usuario_id'];
        $archivo = $_FILES['foto'];
        $directorio = "uploads/";
        $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $permitidos = array("jpg", "jpeg", "png", "webp");
        if (in_array($ext, $permitidos)) {
            $nuevo_nombre = "perfil_" . $id . "_" . time() . "." . $ext;
            $ruta_completa = $directorio . $nuevo_nombre;
            $res_vieja = mysqli_query($conexion, "SELECT foto_perfil FROM usuarios WHERE id = '$id'");
            $user_viejo = mysqli_fetch_assoc($res_vieja);
            $foto_vieja = $user_viejo['foto_perfil'];
            if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
                $update = mysqli_query($conexion, "UPDATE usuarios SET foto_perfil = '$nuevo_nombre' WHERE id = '$id'");
                if ($update) {
                    if ($foto_vieja != 'default.jpg' && file_exists($directorio . $foto_vieja)) {
                        unlink($directorio . $foto_vieja);
                    }
                    header("Location: perfil.php?success=foto");
                }
            } else {
                echo "Error al subir el archivo.";
            }
        } else {
            echo "<script>alert('Formato no permitido'); window.location.href='perfil.php';</script>";
        }
    }
?>