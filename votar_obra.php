<?php
    session_start();
    include("conexion.php");
    if (!isset($_SESSION['usuario_id'])) {
        echo "error_login";
        exit;
    }
    $id_usuarios = $_SESSION['usuario_id'];
    $id_obras = isset($_POST['id_obras']) ? intval($_POST['id_obras']) : 0;
    $puntuacion = isset($_POST['puntuacion']) ? intval($_POST['puntuacion']) : 0;
    if ($id_obras <= 0 || $puntuacion < 1 || $puntuacion > 5) {
        echo "error_datos";
        exit;
    }
    $query = "INSERT INTO calificaciones (id_obras, id_usuarios, puntuacion) 
            VALUES ('$id_obras', '$id_usuarios', '$puntuacion') 
            ON DUPLICATE KEY UPDATE puntuacion = '$puntuacion'";
    if (mysqli_query($conexion, $query)) {
        $query_promedio = "SELECT AVG(puntuacion) as promedio, COUNT(id) as total 
                        FROM calificaciones WHERE id_obras = '$id_obras'";
        $res_promedio = mysqli_query($conexion, $query_promedio);
        $datos = mysqli_fetch_assoc($res_promedio);
        echo json_encode([
            "status" => "success",
            "promedio" => round($datos['promedio'], 1),
            "total" => $datos['total']
        ]);
    } else {
        echo "error_db";
    }
    mysqli_close($conexion);
?>