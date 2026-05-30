<?php
    session_start();
    include("conexion.php");
    
    if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'administrador') {
        header("Location: administrador.php");
        exit();
    }
    
    $mi_nivel = intval($_SESSION['nivel_permiso'] ?? 1);

    $query = "SELECT u.id, u.nombres, u.apellidos, u.email, u.rol, u.foto_perfil, u.fecha_creacion, a.nivel_acceso 
              FROM usuarios u 
              LEFT JOIN administrador a ON u.id = a.id_usuarios 
              ORDER BY u.fecha_creacion DESC";
              
    $resultado = mysqli_query($conexion, $query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Artrómeda</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { 
            --dorado: #deb887; 
            --glass-oscuro: rgba(15, 15, 15, 0.85);
            --borde-naranja: rgba(240, 141, 12, 0.4);
            --marron-claro: #f5deb3;
        }

        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        }

        body {
            background: url("fondo.png") no-repeat center center fixed;
            background-size: cover;
            color: #fdfdfd;
            min-height: 100vh;
        }

        header {
            padding: 0.5rem 5%;
            background: transparent;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--dorado);
            position: sticky; 
            top: 0; 
            z-index: 1000;
            backdrop-filter: blur(15px);
        }

        .logo-img { 
            height: 60px; 
            transition: transform 0.3s ease;
        }
        
        .logo-img:hover { 
            transform: scale(1.05); 
        }

        .panel-link {
            color: #ffffff; 
            text-decoration: none; 
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            border: 1px solid transparent;
            padding: 8px 15px;
            border-radius: 6px;
        }

        .panel-link:hover { 
            color: var(--dorado);
            border-color: var(--dorado);
            background: rgba(255, 255, 255, 0.05);
        }

        .main-container {
            padding: 3rem 5%; 
            max-width: 1250px; 
            margin: auto;
        }

        .titulo {
            text-align: center; 
            margin-bottom: 2rem; 
            letter-spacing: 3px; 
            text-transform: uppercase;
            font-size: 2rem;
            color: #ffffff;
            text-shadow: 2px 4px 15px rgba(209, 104, 34, 0.6); 
        }

        .search-box { 
            width: 100%; 
            margin-bottom: 2rem; 
        }

        .search-box input {
            width: 100%;
            padding: 14px 25px;
            border-radius: 8px;
            background: rgba(247, 245, 245, 0.4);
            backdrop-filter: blur(7px);
            border: 2px solid #6b3e0ab9;
            color: #111;
            font-size: 1rem;
            outline: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            border-color: #643e0b;
            box-shadow: 0 4px 20px rgba(222, 184, 135, 0.4);
        }

        .search-box input::placeholder {
            color: #555; 
            font-style: italic;
        }

        .table-container {
            background: rgba(247, 245, 245, 0.4);
            backdrop-filter: blur(7px);
            border: 2px solid #6b3e0ab9;
            -webkit-backdrop-filter: blur(12px);
            border-radius: 14px;
            overflow-x: auto;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
        }

        table { 
            width: 100%; 
            border-collapse: collapse; 
        }

        th { 
            background: rgba(82, 50, 8, 0.68);
            color: var(--dorado);
            text-transform: uppercase;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 1px;
            padding: 1.2rem;
            text-align: left;
            border-bottom: 2px solid rgba(107, 68, 16, 0.3);
        }

        td { 
            padding: 1.1rem 1.2rem; 
            border-bottom: 1px solid rgba(255, 255, 255, 0.08); 
            vertical-align: middle;
        }

        tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }

        .user-info-block {
            display: flex; 
            align-items: center;
        }

        .user-img {
            width: 48px;          
            height: 48px;      
            object-fit: cover;     
            object-position: center top;  
            border-radius: 50%;
            margin-right: 15px;
            border: 2px solid var(--dorado);
        }

        .user-name {
            font-weight: 600; 
            color: #643e0b;
            font-size: 0.95rem;
        }

        .user-email {
            font-size: 0.8rem; 
            color: #030303;
            margin-top: 2px;
        }

        .badge {
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-block;
            letter-spacing: 0.5px;
        }

        .rol-admin { 
            background: #df5c58; 
            color: white; 
        }

        .rol-artista { 
            background: #afb665; 
            color: white; 
        }

        .rol-usuario { 
            background: #66b395; 
            color: white; 
        }

        .rango-txt {
            color: #faa320; 
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .fecha-txt {
            font-size: 0.85rem; 
            color: #050505;
        }

        .actions-cell {
            display: flex;
            gap: 15px;
            font-size: 1.25rem;
        }

        .btn-edit { 
            color: #050505; 
            transition: transform 0.2s;
        }

        .btn-delete { 
            color: #e74c3c; 
            transition: transform 0.2s;
        }

        .btn-edit:hover, .btn-delete:hover {
            transform: scale(1.2);
        }
    </style>
</head>
<body>
    <header>
        <a href="index.php"><img src="Logo.png" alt="Logo" class="logo-img"></a>
        <nav>
            <a href="admin_dashboard.php" class="panel-link">
                <i class="bi bi-cpu"></i> VOLVER AL PANEL
            </a>
        </nav>
    </header>

    <div class="main-container">
        <h1 class="titulo">Directorio de Usuarios</h1>
        
        <div class="search-box">
            <input type="text" id="buscador" placeholder="Buscar por nombre, correo o rol..." onkeyup="filtrarUsuarios()">
        </div>
        
        <div class="table-container">
            <table id="tablaUsuarios">
                <thead>
                    <tr>
                        <th>Identidad</th>
                        <th>Privilegios</th>
                        <th>Rango Admin</th>
                        <th>Miembro desde</th>
                        <th>Gestión</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = mysqli_fetch_assoc($resultado)): 
                        $foto_nombre = !empty($user['foto_perfil']) ? $user['foto_perfil'] : "default.png";
                        $foto_ruta = "img_perfiles/" . $foto_nombre;
                    ?>
                    <tr>
                        <td>
                            <div class="user-info-block">
                                <img src="<?php echo htmlspecialchars($foto_ruta, ENT_QUOTES, 'UTF-8'); ?>" class="user-img" alt="Perfil">
                                <div>
                                    <div class="user-name"><?php echo htmlspecialchars($user['nombres'] . " " . $user['apellidos']); ?></div>
                                    <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php 
                                $rol = $user['rol'];
                                $clase = ($rol == 'administrador') ? "rol-admin" : (($rol == 'artista') ? "rol-artista" : "rol-usuario");
                            ?>
                            <span class="badge <?php echo $clase; ?>"><?php echo htmlspecialchars($rol); ?></span>
                        </td>
                        <td>
                            <?php if($rol == 'administrador'): ?>
                                <span class="rango-txt">
                                    <i class="bi bi-shield-lock"></i> Nivel <?php echo intval($user['nivel_acceso'] ?? 2); ?>
                                </span>
                            <?php else: ?>
                                <span style="opacity: 0.25;">---</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="fecha-txt">
                                <?php echo date('d/m/Y', strtotime($user['fecha_creacion'])); ?>
                            </span>
                        </td>
                        <td>
                            <div class="actions-cell">
                                <a href="editar_usuario.php?id=<?php echo intval($user['id']); ?>" class="btn-edit" title="Editar Perfil">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                
                                <?php if ($mi_nivel >= 3): ?>
                                    <a href="eliminar_usuario.php?id=<?php echo intval($user['id']); ?>" class="btn-delete" title="Eliminar Usuario" onclick="return confirm('¿Estás completamente seguro de eliminar permanentemente a este usuario del sistema?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function filtrarUsuarios() {
            let input = document.getElementById("buscador").value.toLowerCase();
            let tr = document.getElementById("tablaUsuarios").getElementsByTagName("tr");
            
            for (let i = 1; i < tr.length; i++) {
                let texto = tr[i].innerText.toLowerCase();
                if (texto.includes(input)) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    </script>
</body>
</html>