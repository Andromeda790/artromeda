<?php
    session_start();
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_eliminar'])) {
        $id = $_POST['id_eliminar'];
        if (isset($_SESSION['carrito'][$id])) {
            unset($_SESSION['carrito'][$id]);
        }
        header("Location: carrito.php");
        exit;
    } else {
        header("Location: obras.php");
        exit;
    }
?>