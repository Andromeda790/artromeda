<?php
    session_start();
    include("conexion.php");
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit();
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id_seguidor = $_SESSION['usuario_id'];
        $id_seguido = mysqli_real_escape_string($conexion, $_POST['id_seguido']);
        $accion = $_POST['accion'];
        if ($id_seguidor == $id_seguido) {
            header("Location: perfil_artista.php?id=$id_seguido");
            exit();
        }
        if ($accion === 'follow') {
            $query = "INSERT INTO seguidores (id_seguidor, id_seguido) VALUES ('$id_seguidor', '$id_seguido')";
            mysqli_query($conexion, $query);
        } else if ($accion === 'unfollow') {
            $query = "DELETE FROM seguidores WHERE id_seguidor = '$id_seguidor' AND id_seguido = '$id_seguido'";
            mysqli_query($conexion, $query);
        }
        header("Location: perfil_artista.php?id=$id_seguido");
        exit();
    } else {
        header("Location: index.php");
        exit();
    }
?>