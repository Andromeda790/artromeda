<?php
    session_start();
    include("conexion.php");
    
    if (!isset($_SESSION['usuario_rol'])) {
        header("Location: login.php");
        exit();
    }
    
    require_once("verificar_admin.php");
    $nivel = verificarAdmin(1) ?? ($_SESSION['usuario_nivel'] ?? 1);
    $nombre_admin = htmlspecialchars($_SESSION['usuario_nombre'] ?? "Administrador");
    
    function obtenerTotal($conexion, $query) {
        $total = 0;
        $res = mysqli_query($conexion, $query);
        if ($res) {
            $row = mysqli_fetch_assoc($res);
            $total = $row['total'] ?? 0;
        }
        return $total;
    }
    
    $total_obras = obtenerTotal($conexion, "SELECT COUNT(id) as total FROM obras");
    $total_artistas = obtenerTotal($conexion, "SELECT COUNT(id) as total FROM usuarios WHERE rol = 'artista'");
    $total_clientes = ($nivel >= 2)
        ? obtenerTotal($conexion, "SELECT COUNT(id) as total FROM usuarios WHERE rol = 'usuario'")
        : 0;
    $cantidad_carrito = 0;
    if (isset($_SESSION['carrito'])) {
        $cantidad_carrito = count($_SESSION['carrito']);
    }
    
    $cantidad_favoritos = 0;
    if (isset($_SESSION['usuario_id'])) {
        $id_user_count = intval($_SESSION['usuario_id']);
        $res_favs = mysqli_query($conexion, "SELECT COUNT(*) as total FROM favoritos WHERE id_usuarios = $id_user_count");
        if ($res_favs) {
            $fila_favs = mysqli_fetch_assoc($res_favs);
            $cantidad_favoritos = $fila_favs['total'] ?? 0;
        }
    }    
    mysqli_close($conexion);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador - Artrómeda</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Inter:wght@400;500;600&display=swap">
    <style>
        :root{
            --dorado: #deb887;
            --oscuro: #111;
            --glass: rgba(0, 0, 0, 0.76);
            --borde: rgba(222, 184, 135, 0.25);
            --texto-marron: #6b3e0a;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: url("fondo.png") no-repeat center center fixed;
            background-size: cover;
            color: white;
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
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .logo-img:hover { 
            transform: scale(1.05); 
        }

        .nav-contenedor {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .nav-list {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .nav-link { 
            text-decoration: none; 
            color: white; 
            font-weight: 500; 
            font-size: 0.8rem;
            letter-spacing: 1px; 
            text-transform: uppercase; 
            position: relative;
            padding: 8px 12px; 
            transition: color 0.3s ease;
            white-space: nowrap;
        }

        .nav-link:hover { 
            color: #e4924f; 
        }

        .nav-link::after {
            content: ''; 
            position: absolute;
            width: 0; 
            height: 2px;
            bottom: 0; 
            left: 12px; 
            background-color: #e4924f; 
            transition: width 0.3s ease;
        }

        .nav-link:hover::after { 
            width: calc(100% - 24px); 
        }

        .nav-icons {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .nav-link-icon {
            color: white;
            text-decoration: none;
            font-size: 1.25rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            position: relative;
        }

        .nav-link-icon:hover {
            color: #6b3e0a;
            transform: scale(1.1);
        }

        .cart-badge, .fav-badge {
            background-color: #d4b483; 
            color: #000;              
            font-size: 9px;  
            font-weight: bold;
            width: 16px;     
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            position: absolute;
            top: -5px;        
            right: -8px;    
            line-height: 1;
        }

        .mensaje {
            text-align: center;
            padding: 50px 20px 20px;
            font-family: 'Montserrat', sans-serif;
            font-size: 1.6rem;
            letter-spacing: 3px;
            text-transform: uppercase;
            text-shadow: 2px 4px 12px rgba(209, 104, 34, 0.4);
        }

        .panel-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 25px;
            padding: 2rem 7%;
        }

        .card {
            background: rgba(247, 246, 246, 0.85);
            border: 1px solid #6b3e0a;
            border-radius: 16px;
            padding: 30px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .card:hover {
            transform: translateY(-5px);
            border-color: #6b3e0a;
            box-shadow: 0 15px 30px rgba(0,0,0,0.35);
        }

        .card h3 {
            margin-bottom: 8px;
            font-size: 0.9rem;
            letter-spacing: 1px;
            color: #6b3e0a;
            text-transform: uppercase;
        }

        .card .numero {
            color: #1e7e34;
            font-size: 2.2rem;
            font-weight: 700;
        }

        .card i {
            font-size: 1.8rem;
            color: #ddaa68;
            display: block;
            margin-bottom: 12px;
        }

        .actions {
            padding: 0 7% 3rem;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
        }

        .btn {
            background: transparent;
            color: var(--texto-marron);
            border: 2px solid #6b3e0a;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 22px;
            border-radius: 8px;
            text-decoration: none;   
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            border-color: #6b3e0a;
        }

        .menu-toggle {
            display: none;
            cursor: pointer;
            font-size: 1.8rem;
            color: white;
            transition: 0.3s;
        }

        @media (max-width: 991px) {
            .menu-toggle { 
                display: block;
                z-index: 1100; 
            }

            .nav-list {
                position: fixed;
                top: 0;
                right: -100%;
                width: 70%; 
                height: 100vh;
                background: rgba(175, 90, 11, 0.79);
                backdrop-filter: blur(7px);  
                -webkit-backdrop-filter: blur(25px);
                border-left: 1px solid #6b3e0a; 
                flex-direction: column; 
                justify-content: flex-start;
                align-items: flex-start;
                padding: 100px 40px;
                gap: 25px;
                transition: right 0.4s cubic-bezier(0.4, 0, 0.2, 1); 
                box-shadow: -5px 0 25px rgba(0,0,0,0.8);
                z-index: 1050;
            }
            
            .nav-list.active { 
                right: 0; 
            }

            .nav-link {
                font-size: 1.1rem;
                margin: 0;
                width: 100%;
                padding: 10px 0;
            }

            .nav-icons { 
                margin-left: 0; 
                margin-top: 20px; 
                width: 100%;
                justify-content: flex-start;
                gap: 30px;
                border-top: 1px solid rgb(248, 247, 245);
                padding-top: 30px;
            }

            .nav-link-icon {
                font-size: 1.5rem; 
            }
        }
    </style>
</head>
<body>
    <header>
        <a href="index.php"><img src="Logo.png" alt="Logo" class="logo-img"></a>
        
        <div class="nav-contenedor">
            <nav class="nav-list" id="nav-menu">
                <a href="index.php" class="nav-link">Inicio</a>
                <a href="obras.php" class="nav-link">Galería</a>
                <a href="todos_perfiles.php" class="nav-link">Mi Perfil</a>
                <a href="artistas_lista.php" class="nav-link">Artistas</a>
            </nav>

            <div class="nav-icons">
                <a href="favoritos.php" class="nav-link-icon" title="Mis Favoritos">
                    <i class="bi bi-heart"></i>
                    <span class="fav-badge" id="fav-count" style="display: <?php echo ($cantidad_favoritos > 0) ? 'flex' : 'none'; ?>;">
                        <?php echo $cantidad_favoritos; ?>
                    </span>
                </a>
                
                <a href="carrito.php" class="nav-link-icon" title="Mi Carrito">
                    <i class="bi bi-cart3"></i>
                    <span class="cart-badge" id="cart-count" style="display: <?php echo ($cantidad_carrito > 0) ? 'flex' : 'none'; ?>;">
                        <?php echo $cantidad_carrito; ?>
                    </span>
                </a>
                
                <a href="logout.php" class="nav-link-icon" title="Cerrar Sesión"><i class="bi bi-box-arrow-right"></i></a>
                
                <div class="menu-toggle" id="mobile-menu">
                    <i class="bi bi-list"></i>
                </div>
            </div>
        </div>
    </header>

    <div class="mensaje">
        Bienvenido, <?php echo $nombre_admin; ?>, este es tu centro de control
    </div>

    <div class="panel-grid">
        <div class="card">
            <i class="bi bi-image"></i>
            <h3>Obras en sistema</h3>
            <div class="numero"><?php echo $total_obras; ?></div>
        </div>
        
        <div class="card">
            <i class="bi bi-brush"></i>
            <h3>Artistas registrados</h3>
            <div class="numero"><?php echo $total_artistas; ?></div>
        </div>
        
        <?php if($nivel >= 2): ?>
        <div class="card">
            <i class="bi bi-people"></i>
            <h3>Clientes registrados</h3>
            <div class="numero"><?php echo $total_clientes; ?></div>
        </div>
        <?php endif; ?>
    </div>

    <div class="actions">
        <?php if($nivel >= 2): ?>
        <a href="usuarios_lista.php" class="btn">
            <i class="bi bi-person-gear"></i> Gestionar Usuarios
        </a>
        <?php endif; ?>
        
        <a href="obras.php" class="btn">
            <i class="bi bi-grid-3x3-gap"></i> Gestionar Obras
        </a>
        
        <?php if($nivel >= 3): ?>
        <a href="reportes.php" class="btn">
            <i class="bi bi-file-earmark-bar-graph"></i> Reportes Financieros
        </a>
        <?php endif; ?>
    </div>

    <script>
        const mobileMenu = document.getElementById('mobile-menu');
        const navMenu = document.getElementById('nav-menu');
        
        if(mobileMenu && navMenu) {
            mobileMenu.addEventListener('click', (e) => {
                e.stopPropagation();
                navMenu.classList.toggle('active');
                const icon = mobileMenu.querySelector('i');
                if (navMenu.classList.contains('active')) {
                    icon.classList.replace('bi-list', 'bi-x-lg');
                } else {
                    icon.classList.replace('bi-x-lg', 'bi-list');
                }
            });

            document.addEventListener('click', (e) => {
                if (!navMenu.contains(e.target) && !mobileMenu.contains(e.target)) {
                    navMenu.classList.remove('active');
                    const icon = mobileMenu.querySelector('i');
                    icon.classList.replace('bi-x-lg', 'bi-list');
                }
            });
        }
    </script>
</body>
</html>