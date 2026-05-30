<?php
    session_start();
    include("conexion.php");
    if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'administrador' || $_SESSION['usuario_nivel'] < 2) {
        die("Acceso denegado: No tienes rango suficiente para esta acción.");
    }
    $id_actual = $_SESSION['usuario_id'];
    $mi_nivel = $_SESSION['usuario_nivel'];
    if (isset($_GET['id'])) {
        $id_a_eliminar = mysqli_real_escape_string($conexion, $_GET['id']);
        if ($id_a_eliminar == $id_actual) {
            echo "<script>
                    alert('Error: No puedes eliminar tu propia cuenta mientras estás en sesión.');
                    window.location.href = 'usuarios_lista.php';
                  </script>";
            exit();
        }
        $check_query = "SELECT nivel_permiso FROM usuarios WHERE id = '$id_a_eliminar'";
        $res_check = mysqli_query($conexion, $check_query);
        $user_a_borrar = mysqli_fetch_assoc($res_check);

        if ($user_a_borrar) {
            if ($mi_nivel < 3 && $user_a_borrar['nivel_permiso'] >= $mi_nivel) {
                echo "<script>
                        alert('Error: No tienes rango suficiente para eliminar a este administrador.');
                        window.location.href = 'usuarios_lista.php';
                      </script>";
                exit();
            }
            $query = "DELETE FROM usuarios WHERE id = '$id_a_eliminar'";
            if (mysqli_query($conexion, $query)) {
                echo "<script>
                        alert('Usuario eliminado correctamente.');
                        window.location.href = 'usuarios_lista.php';
                      </script>";
            } else {
                echo "<script>
                        alert('Error al intentar eliminar: " . mysqli_error($conexion) . "');
                        window.location.href = 'usuarios_lista.php';
                      </script>";
            }
        } else {
            header("Location: usuarios_lista.php");
        }
    } else {
        header("Location: usuarios_lista.php");
    }
?>