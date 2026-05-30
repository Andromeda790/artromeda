<?php
    session_start();
    include("conexion.php");
    if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'administrador') {
        header("Location: login.php");
        exit();
    }
    $mi_nivel = $_SESSION['nivel_permiso'] ?? 1;
    if (isset($_GET['id'])) {
        $id = mysqli_real_escape_string($conexion, $_GET['id']);
        $query = "SELECT u.*, a.nivel_acceso 
                  FROM usuarios u 
                  LEFT JOIN administrador a ON u.id = a.id_usuarios 
                  WHERE u.id = '$id'";
        
        $resultado = mysqli_query($conexion, $query);
        $user = mysqli_fetch_assoc($resultado);
        if (!$user) {
            echo "<script>alert('Usuario no encontrado'); window.location.href='usuarios_lista.php';</script>";
            exit();
        }
        if ($mi_nivel < 3 && ($user['nivel_acceso'] ?? 0) >= 3 && $user['id'] !== $_SESSION['usuario_id']) {
            echo "<script>alert('No tienes rango suficiente para editar a este súper admin'); window.location.href='usuarios_lista.php';</script>";
            exit();
        }
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $id_upd = $_POST['id'];
        $nombres = mysqli_real_escape_string($conexion, $_POST['nombres']);
        $apellidos = mysqli_real_escape_string($conexion, $_POST['apellidos']);
        $email = mysqli_real_escape_string($conexion, $_POST['email']);
        $rol = mysqli_real_escape_string($conexion, $_POST['rol']);
        $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
        $sql_update_user = "UPDATE usuarios SET 
                            nombres = '$nombres', 
                            apellidos = '$apellidos', 
                            email = '$email', 
                            rol = '$rol', 
                            telefono = '$telefono' 
                            WHERE id = '$id_upd'";

        if (mysqli_query($conexion, $sql_update_user)) {
            if ($mi_nivel >= 3 && isset($_POST['nivel_acceso'])) {
                $nuevo_nivel = mysqli_real_escape_string($conexion, $_POST['nivel_acceso']);
                $check_admin = mysqli_query($conexion, "SELECT id_usuarios FROM administrador WHERE id_usuarios = '$id_upd'");
                if (mysqli_num_rows($check_admin) > 0) {
                    mysqli_query($conexion, "UPDATE administrador SET nivel_acceso = '$nuevo_nivel' WHERE id_usuarios = '$id_upd'");
                } else if ($rol == 'administrador') {
                    mysqli_query($conexion, "INSERT INTO administrador (id_usuarios, nivel_acceso) VALUES ('$id_upd', '$nuevo_nivel')");
                }
            }
            echo "<script>alert('Cambios guardados con éxito'); window.location.href='usuarios_lista.php';</script>";
        }
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - Artrómeda</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { 
            --dorado: #deb887; 
        }

        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
            font-family: 'Segoe UI', sans-serif; 
        }

        body {
            color: white;
            min-height: 100vh;
            background: url("fondo.png") no-repeat center center fixed;
            background-size: cover;
            display: flex;
            flex-direction: column;
        }

        header {
            padding: 0.2rem 5%;
            background: rgba(0, 0, 0, 0.7);
            border-bottom: 1px solid var(--dorado);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-img { 
            height: 75px; 
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .logo-img:hover { 
            transform: scale(1.08) translateY(-3px); 
        }

        #contenedor-edit {
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(15px);
            border: 1px solid var(--dorado);
            border-radius: 20px;
            margin: 2rem auto;
            padding: 2rem;
            width: 90%;
            max-width: 500px;
        }

        h1 { 
            text-align: center; 
            margin-bottom: 1.5rem; 
            color: var(--dorado); 
            font-size: 1.5rem;
        }

        .form-group { 
            margin-bottom: 1.2rem; 
        }
        
        label { 
            display: block; 
            margin-bottom: 8px; 
            font-size: 0.85rem; 
            color: #ccc;
        }
        
        input, select {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid rgba(222, 184, 135, 0.3);
            background: rgba(255, 255, 255, 0.05);
            color: white;
            outline: none;
        }

        input:focus {
            border-color: var(--dorado); 
        }

        .btn-save {
            width: 100%;
            padding: 14px;
            background: var(--dorado);
            border: none;
            border-radius: 8px;
            color: black;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }

        .admin-section {
            background: rgba(222, 184, 135, 0.1);
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            border-left: 4px solid var(--dorado);
        }
    </style>
</head>
    <body>
        <header>
            <img src="Logo.png" alt="Logo" class="logo-img">
            <a href="usuarios_lista.php" style="color: white; text-decoration: none;">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </header>
        <div id="contenedor-edit">
            <h1><i class="bi bi-pencil-square"></i> Editar Usuario</h1>
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                <div class="form-group">
                    <label>Nombre(s)</label>
                    <input type="text" name="nombres" value="<?php echo htmlspecialchars($user['nombres']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Apellido(s)</label>
                    <input type="text" name="apellidos" value="<?php echo htmlspecialchars($user['apellidos']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Correo Electrónico</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" value="<?php echo htmlspecialchars($user['telefono']); ?>">
                </div>
                <div class="form-group">
                    <label>Rol en la Plataforma</label>
                    <select name="rol">
                        <option value="usuario" <?php echo ($user['rol'] == 'usuario') ? 'selected' : ''; ?>>Comprador</option>
                        <option value="artista" <?php echo ($user['rol'] == 'artista') ? 'selected' : ''; ?>>Artista</option>
                        <option value="administrador" <?php echo ($user['rol'] == 'administrador') ? 'selected' : ''; ?>>Administrador</option>
                    </select>
                </div>
                <?php if ($mi_nivel >= 3): ?>
                <div class="admin-section">
                    <label style="color: var(--dorado); font-weight: bold;">
                        <i class="bi bi-shield-lock"></i> Rango Administrativo
                    </label>
                    <select name="nivel_acceso">
                        <option value="2" <?php echo (($user['nivel_acceso'] ?? 2) == 2) ? 'selected' : ''; ?>>Nivel 2 - Moderador</option>
                        <option value="3" <?php echo (($user['nivel_acceso'] ?? 2) == 3) ? 'selected' : ''; ?>>Nivel 3 - Súper Admin</option>
                    </select>
                </div>
                <?php endif; ?>
                <button type="submit" class="btn-save">GUARDAR CAMBIOS</button>
            </form>
        </div>
    </body>
</html>