<?php
    if ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_ADDR'] == '127.0.0.1') {
        $host = "localhost";
        $user = "root";
        $pass = ""; 
        $db   = "mi_proyecto";
    } else {
        require_once 'config.php';
        $host = DB_HOST; 
        $user = DB_USER;          
        $pass = DB_PASS; 
        $db   = DB_NAME;
    }

    $conexion = mysqli_connect($host, $user, $pass, $db);

    mysqli_set_charset($conexion, "utf8mb4");

    if (!$conexion) {
        die("Error de conexión: " . mysqli_connect_error());
    }
?>