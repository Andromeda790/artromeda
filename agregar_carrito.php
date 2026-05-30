<?php
    session_start();
    if (isset($_POST['id'])) {
        $id = $_POST['id'];
        $titulo = $_POST['titulo'];
        $precio = $_POST['precio'];
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }
        $_SESSION['carrito'][$id] = [
            'titulo' => $titulo,
            'precio' => $precio,
            'cantidad' => 1
        ];
        echo count($_SESSION['carrito']);
    }
?>