<?php
    session_start();
    require_once("conexion.php");
    require_once("verificar_admin.php");
    $nivel = verificarAdmin(3);
    $mensaje = "";
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
        $email = mysqli_real_escape_string($conexion, $_POST['email']);
        $password = $_POST['password'];
        $nivel_acceso = (int) $_POST['nivel_acceso'];
        if ($nombre && $email && $password && in_array($nivel_acceso, [1,2,3])) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $query_usuario = "INSERT INTO usuarios (nombres, email, password, rol) VALUES ('$nombre', '$email', '$hash', 'administrador')";
            if (mysqli_query($conexion, $query_usuario)) {
                $id_usuarios = mysqli_insert_id($conexion);
                $query_admin = "INSERT INTO administrador (nivel_acceso, id_usuarios) VALUES ($nivel_acceso, $id_usuarios)";
                if (mysqli_query($conexion, $query_admin)) {
                    $mensaje = "Admin creado correctamente con nivel $nivel_acceso.";
                } else {
                    $mensaje = "Error al crear registro de admin: " . mysqli_error($conexion);
                }
            } else {
                $mensaje = "Error al crear usuario: " . mysqli_error($conexion);
            }
        } else {
            $mensaje = "Todos los campos son obligatorios y el nivel debe ser 1, 2 o 3.";
        }
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear Admin - Artrómeda</title>
    <style>
        body{
            font-family:sans-serif;
            background:#111;
            color:white;
            padding:2rem;
        }

        input, select{
            padding:5px;
            margin:5px 0;
            width:100%;
        }

        button{
            padding:10px;
            margin-top:10px;
            background:#deb887;
            border:none;
            color:black;
            cursor:pointer;
        }

        button:hover{
            background:#c9a66b;
        }

        .message{
            margin:10px 0;
            color:#4ade80;
        }
    </style>
</head>
    <body>
        <h2>Crear nuevo administrador</h2>
        <?php if($mensaje) echo "<div class='message'>$mensaje</div>"; ?>
        <form method="POST">
            <label>Nombre completo:</label>
            <input type="text" name="nombre" required>
            <label>Email:</label>
            <input type="email" name="email" required>
            <label>Contraseña:</label>
            <input type="password" name="password" required>
            <label>Nivel de acceso:</label>
            <select name="nivel_acceso" required>
                <option value="1">Nivel 1</option>
                <option value="2">Nivel 2</option>
                <option value="3">Nivel 3</option>
            </select>
            <button type="submit">Crear Admin</button>
        </form>
    </body>
</html>