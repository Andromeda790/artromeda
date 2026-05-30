<?php
    session_start();
    if (isset($_GET['id'])) {
        $id_a_quitar = $_GET['id'];
        if (!empty($_SESSION['carrito'])) {
            if (($key = array_search($id_a_quitar, $_SESSION['carrito'])) !== false) {
                unset($_SESSION['carrito'][$key]);
            }
            $_SESSION['carrito'] = array_values($_SESSION['carrito']);
        }
    }
    header("Location: carrito.php");
    exit();
?>