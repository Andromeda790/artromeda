<?php
    session_start();
    include("conexion.php");
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['usuario_id'])) {
        $id = $_SESSION['usuario_id'];
        $nombres   = mysqli_real_escape_string($conexion, $_POST['nombres'] ?? $_SESSION['usuario_nombre']);
        $apellidos = mysqli_real_escape_string($conexion, $_POST['apellidos'] ?? '');
        $telefono  = mysqli_real_escape_string($conexion, $_POST['telefono'] ?? '');
        $direccion = mysqli_real_escape_string($conexion, $_POST['direccion'] ?? '');
        $sql = "UPDATE usuarios SET 
                nombres = '$nombres', 
                apellidos = '$apellidos', 
                telefono = '$telefono', 
                direccion = '$direccion' 
                WHERE id = '$id'";
        if (mysqli_query($conexion, $sql)) {
            if ($_SESSION['usuario_rol'] == 'artista') {
                $bio = mysqli_real_escape_string($conexion, $_POST['biografia'] ?? '');
                $esp = mysqli_real_escape_string($conexion, $_POST['especializacion'] ?? '');
                $query_artista = "UPDATE artista SET 
                                biografia = '$bio', 
                                especializacion = '$esp' 
                                WHERE id_usuarios = '$id'";
                mysqli_query($conexion, $query_artista);
            }
            $_SESSION['usuario_nombre'] = $nombres;
            $redireccion = ($_SESSION['usuario_rol'] == 'artista') ? 'artista.php' : 'perfil.php';
            echo "<script>
                    alert('Perfil actualizado con éxito'); 
                    window.location.href='$redireccion';
                  </script>";
        } else {
            echo "Error: " . mysqli_error($conexion);
        }
    }
?>