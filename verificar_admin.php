<?php
    require_once("conexion.php");
    function verificarAdmin($nivel_requerido) {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: login.php");
            exit();
        }
        global $conexion;
        $id_usuarios = $_SESSION['usuario_id'];
        $query = "SELECT nivel_acceso 
                FROM administrador 
                WHERE id_usuarios = $id_usuarios";
        $resultado = mysqli_query($conexion, $query);
        if (!$resultado || mysqli_num_rows($resultado) == 0) {
            header("Location: index.php");
            exit();
        }
        $admin = mysqli_fetch_assoc($resultado);
        if ($admin['nivel_acceso'] < $nivel_requerido) {
            header("Location: index.php");
            exit();
        }
        return $admin['nivel_acceso'];
    }
?>