<?php
    session_start();
    include("conexion.php");
    $id_usuarios = $_SESSION['usuario_id'] ?? null;
    if (!$id_usuarios) { header("Location: login.php"); exit(); }
    $query = mysqli_query($conexion, "SELECT * FROM usuarios WHERE id = '$id_usuarios'");
    $user = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración - Artrómeda</title>
    <style>

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            color: white;
            min-height: 100vh;
            background: url("fondo.png") no-repeat center center fixed;
            background-size: cover;
            flex-direction: column;
        }

        .config-container {
            max-width: 600px;
            margin: 50px auto;
            background: rgba(0,0,0,0.8);
            padding: 30px;
            border-radius: 20px;
            border: 1px solid #deb887;
        }

        .form-group { 
            margin-bottom: 20px; 
        }

        label { 
            color: #deb887; 
            display: block; 
            margin-bottom: 5px; 
            font-size: 0.9rem; 
        }

        input {
            width: 100%;
            padding: 12px;
            background: #222;
            border: 1px solid #444;
            color: white;
            border-radius: 8px;
            box-sizing: border-box;
        }

        .btn-guardar {
            background: #deb887;
            color: black;
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 50px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-guardar:hover { 
            background: #f5deb3; 
            transform: scale(1.02); 
        }
    </style>
</head>
    <body>
        <div class="config-container">
            <h2 style="color:#deb887; text-align:center;">CONFIGURACIÓN DE CUENTA</h2>
            <form action="actualizar_perfil.php" method="POST">
                <div class="form-group">
                    <label>NOMBRES</label>
                    <input type="text" name="nombres" value="<?php echo $user['nombres']; ?>" required>
                </div>
                <div class="form-group">
                    <label>APELLIDOS</label>
                    <input type="text" name="apellidos" value="<?php echo $user['apellidos']; ?>" required>
                </div>
                <div class="form-group">
                    <label>CORREO ELECTRÓNICO</label>
                    <input type="email" name="email" value="<?php echo $user['email']; ?>" required>
                </div>
                <div class="form-group">
                    <label>TELÉFONO</label>
                    <input type="text" name="telefono" value="<?php echo $user['telefono']; ?>">
                </div>
                <div class="form-group">
                    <label>DIRECCIÓN DE ENVÍO</label>
                    <input type="text" name="direccion" value="<?php echo $user['direccion']; ?>">
                </div>
                <hr style="border: 0.5px solid #333; margin: 30px 0;">
                <div class="form-group">
                    <label>NUEVA CONTRASEÑA</label>
                    <input type="password" name="nueva_password" placeholder="********">
                </div>
                <button type="submit" class="btn-guardar">ACTUALIZAR MIS DATOS</button>
            </form>
            <div style="text-align:center; margin-top:20px;">
                <a href="perfil.php" style="color:#888; text-decoration:none;">← Volver a mi Perfil</a>
            </div>
        </div>
    </body>
</html>