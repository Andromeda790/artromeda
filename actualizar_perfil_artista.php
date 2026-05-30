<?php
    session_start();
    include("conexion.php");

    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit();
    }

    $id_usuario = intval($_SESSION['usuario_id']);
    $query = "SELECT * FROM usuarios WHERE id = $id_usuario";
    $resultado = mysqli_query($conexion, $query);
    $datos = mysqli_fetch_assoc($resultado);
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nombres = mysqli_real_escape_string($conexion, $_POST['nombres']);
        $apellidos = mysqli_real_escape_string($conexion, $_POST['apellidos']);
        $email = mysqli_real_escape_string($conexion, $_POST['email']);
        $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
        $especializacion = mysqli_real_escape_string($conexion, $_POST['especializacion']);
        $biografia = mysqli_real_escape_string($conexion, $_POST['biografia']);
        $foto = $datos['foto_perfil'];

        if (!empty($_FILES['foto']['name'])) {
            $nombreFoto = time() . "_" . basename($_FILES['foto']['name']);
            // Es buena idea validar que la carpeta exista o el archivo sea una imagen real en producción
            if (move_uploaded_file($_FILES['foto']['tmp_name'], "img_perfiles/" . $nombreFoto)) {
                $foto = $nombreFoto;
            }
        }
        
        $update = "UPDATE usuarios SET 
                    nombres='$nombres',
                    apellidos='$apellidos',
                    email='$email',
                    telefono='$telefono',
                    especializacion='$especializacion',
                    biografia='$biografia',
                    foto_perfil='$foto'
                    WHERE id = $id_usuario";
                    
        mysqli_query($conexion, $update);
        
        if (!empty($_POST['password'])) {
            $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
            mysqli_query($conexion, "UPDATE usuarios SET password='$pass' WHERE id = $id_usuario");
        }
        
        header("Location: artista.php?perfil=actualizado");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - Artrómeda</title>
    <style>
        body {
            background: url("fondo.png") no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #6b3e0a;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 40px 10px;
            margin: 0;
            box-sizing: border-box;
        }

        .card {
            background: rgba(247, 245, 245, 0.4);
            backdrop-filter: blur(7px);
            border: 2px solid rgba(105, 66, 14, 0.66);
            padding: 40px;
            border-radius: 20px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        h2 {
            color: #6b3e0a;
            margin-bottom: 25px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            font-size: 13px;
            font-weight: 600;
            color: #6b3e0a;
            display: block;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input, textarea {
            width: 100%;
            padding: 12px;
            background: #faf8f8;
            border: 1px solid #6b3e0a;
            border-radius: 8px;
            color: black;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: #deb887;
            background: #fff;
            box-shadow: 0 0 8px rgba(240, 141, 12, 0.2);
        }

        textarea {
            resize: none;
            height: 90px;
        }

        .foto-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .foto {
            width: 100px;
            height: 100px;
            border-radius: 30%;
            object-fit: cover;
            border: 3px solid #6b3e0ac9;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }

        .botones {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }

        .btn {
            flex: 1;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 0.9rem;
            cursor: pointer;
            text-transform: uppercase;
            transition: all 0.3s ease;
        }

        .guardar {
            background: transparent;
            color: #6b3e0a;
            border: 2px solid #6b3e0a;
        }

        .cancelar {
            background: transparent;
            color: #6b3e0a;
            border: 2px solid #6b3e0a;
        }

        .guardar:hover, .cancelar:hover {
            background: #dacda6e3;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Editar Perfil</h2>
        <form method="POST" enctype="multipart/form-data">
            
            <div class="foto-container">
                <img src="img_perfiles/<?php echo htmlspecialchars($datos['foto_perfil']); ?>" class="foto" alt="Foto de perfil">
            </div>
            
            <div class="form-group">
                <label>Cambiar foto de perfil</label>
                <input type="file" name="foto" accept="image/*">
            </div>
            
            <div class="form-group">
                <label>Nombres</label>
                <input type="text" name="nombres" value="<?php echo htmlspecialchars($datos['nombres']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Apellidos</label>
                <input type="text" name="apellidos" value="<?php echo htmlspecialchars($datos['apellidos']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Correo Electrónico</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($datos['email']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" name="telefono" value="<?php echo htmlspecialchars($datos['telefono']); ?>">
            </div>
            
            <div class="form-group">
                <label>Especialización Artística</label>
                <input type="text" name="especializacion" value="<?php echo htmlspecialchars($datos['especializacion']); ?>" placeholder="Ej. Pintura al óleo, Escultura, Arte Digital">
            </div>
            
            <div class="form-group">
                <label>Biografía</label>
                <textarea name="biografia" placeholder="Cuéntale al mundo sobre tu trayectoria artística..."><?php echo htmlspecialchars($datos['biografia']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Nueva contraseña</label>
                <input type="password" name="password" placeholder="Dejar en blanco para mantener la actual">
            </div>
            
            <div class="botones">
                <button type="submit" class="btn guardar">Guardar Cambios</button>
                <button type="button" class="btn cancelar" onclick="window.location.href='artista.php'">Cancelar</button>
            </div>
        </form>
    </div>
</body>
</html>