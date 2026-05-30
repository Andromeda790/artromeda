<?php
    session_start();
    include("conexion.php");
    if (!isset($_SESSION['usuario_id']) || !isset($_POST['id_obras'])) {
        header("Location: obras.php");
        exit();
    }
    $id_usuarios = $_SESSION['usuario_id'];
    $id_obras = mysqli_real_escape_string($conexion, $_POST['id_obras']);
    $retorno = $_POST['retorno'] ?? 'obras';
    $check = mysqli_query($conexion, "SELECT id FROM favoritos WHERE id_usuarios = '$id_usuarios' AND id_obras = '$id_obras'");
    if (mysqli_num_rows($check) > 0) {
    
        mysqli_query($conexion, "DELETE FROM favoritos WHERE id_usuarios = '$id_usuarios' AND id_obras = '$id_obras'");
    } else {
    
        mysqli_query($conexion, "INSERT INTO favoritos (id_usuarios, id_obras) VALUES ('$id_usuarios', '$id_obras')");
    }
    if ($retorno == 'favoritos') {
        header("Location: favoritos.php");
    } else {
        header("Location: obras.php");
    }
    exit();
    $origen = $_POST['origen'] ?? 'obras';
    if ($origen === 'favoritos') {
        header("Location: favoritos.php");
    } else {
        header("Location: obras.php");
    }
    exit();
?>