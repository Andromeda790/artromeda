<?php
    session_start();
    include("conexion.php");
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!isset($_SESSION['usuario_id'])) {
            die("Error: Usuario no autenticado.");
        }
        $id_usuarios = $_SESSION['usuario_id'];
        $titulo = mysqli_real_escape_string($conexion, $_POST['titulo']);
        $categoria = mysqli_real_escape_string($conexion, $_POST['categoria']);
        $precio = mysqli_real_escape_string($conexion, $_POST['precio']);
        $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
        if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] != 0) {
            die("Error al cargar la imagen.");
        }
        $nombre_imagen = $_FILES['imagen']['name'];
        $ruta_temporal = $_FILES['imagen']['tmp_name'];
        $carpeta_destino = "img_obras/";
        if (!file_exists($carpeta_destino)) {
            mkdir($carpeta_destino, 0777, true);
        }
        $extension = pathinfo($nombre_imagen, PATHINFO_EXTENSION);
        $nombre_archivo_final = time() . "_" . uniqid() . "." . $extension;
        $ruta_final = $carpeta_destino . $nombre_archivo_final;
        if (move_uploaded_file($ruta_temporal, $ruta_final)) {
            $sql = "INSERT INTO obras 
            (id_usuarios, titulo, categoria, precio, descripcion, imagen, estado)
            VALUES 
            ('$id_usuarios', '$titulo', '$categoria', '$precio', '$descripcion', '$nombre_archivo_final', 'pendiente')";
            if (mysqli_query($conexion, $sql)) {
                echo "<script>
                        alert('Obra subida correctamente y enviada para aprobación.');
                        window.location.href='artista.php';
                    </script>";
            } else {
                echo "Error al guardar en la base de datos: " . mysqli_error($conexion);
            }
        } else {
            echo "Error al subir la imagen al servidor.";
        }
    }
?>