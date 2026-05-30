<?php
    session_start();
    if (!isset($_SESSION['usuario_rol'])) {
        header("Location: login.php");
        exit();
    }
    $rol = trim(strtolower($_SESSION['usuario_rol']));
    $nivel = $_SESSION['usuario_nivel'] ?? 1; 
    switch ($rol) {
        case 'artista':
            header("Location: perfil_artista.php");
            break;
        case 'admin':
        case 'administrador':
            if ($nivel >= 3) {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: administrador.php");
            }
            break;
        case 'usuario':
        case 'cliente':
        default:
            header("Location: perfil.php");
            break;
    }
    exit();
?>