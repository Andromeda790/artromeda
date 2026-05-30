<?php
    session_start();
    include("conexion.php");
    if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'administrador') {
        die("Acceso denegado.");
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $id_usuarios= mysqli_real_escape_string($conexion, $_POST['id_usuarios']);
        $nivel_acceso = mysqli_real_escape_string($conexion, $_POST['nivel_acceso']);
        $fecha = mysqli_real_escape_string($conexion, $_POST['ultimo_ingreso']);
        $check = mysqli_query($conexion, "SELECT * FROM administrador WHERE id_usuarios = '$id_usuarios'");
        if (mysqli_num_rows($check) > 0) {
            $query = "UPDATE administrador SET 
                      nivel_acceso = '$nivel_acceso', 
                      ultimo_ingreso = '$fecha' 
                      WHERE id_usuarios = '$id_usuarios'";
        } else {
            $query = "INSERT INTO administrador (id_usuarios, nivel_acceso, ultimo_ingreso) 
                      VALUES ('$id_usuarios', '$nivel_acceso', '$fecha')";
        }
        if (mysqli_query($conexion, $query)) {
            echo "<script>
                    alert('Privilegios actualizados correctamente en el sistema.');
                    window.location.href = 'administrador.php';
                  </script>";
        } else {
            echo "Error: " . mysqli_error($conexion);
        }
    }
?>