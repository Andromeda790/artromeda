<?php
    session_start();
    include("conexion.php");
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = mysqli_real_escape_string($conexion, $_POST['email']);
        $sql = "SELECT * FROM usuarios WHERE email = '$email'";
        $resultado = mysqli_query($conexion, $sql);

        if (mysqli_num_rows($resultado) > 0) {
            header("Location: confirmacion_envio.php?status=success&email=" . urlencode($email));
            exit();
        } else {
            header("Location: confirmacion_envio.php?status=error");
            exit();
        }
    } else {
        header("Location: olvido_password.php");
        exit();
    }
?>