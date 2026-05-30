<?php
    session_start();
    include("conexion.php");
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit();
    }
    $id_logueado = $_SESSION['usuario_id'];
    $id_usuarios_ver = mysqli_real_escape_string($conexion, $_GET['id'] ?? $id_logueado);
    $query_info = "SELECT nombres, apellidos, foto_perfil, biografia, especializacion 
                   FROM usuarios 
                   WHERE id = '$id_usuarios_ver'";
    $res_info = mysqli_query($conexion, $query_info);
    $artista = mysqli_fetch_assoc($res_info);
    if (!$artista) {
        echo "<script>alert('El perfil solicitado no existe o no es un artista.'); window.location='artistas_lista.php';</script>";
        exit();
    }
    $cantidad_carrito = 0;
    if (isset($_SESSION['carrito'])) {
        $cantidad_carrito = count($_SESSION['carrito']);
    }
    $cantidad_favoritos = 0;
    if (isset($_SESSION['usuario_id'])) {
        $id_user_count = $_SESSION['usuario_id'];
        $res_favs = mysqli_query($conexion, "SELECT COUNT(*) as total FROM favoritos WHERE id_usuarios = '$id_user_count'");
        $fila_favs = mysqli_fetch_assoc($res_favs);
        $cantidad_favoritos = $fila_favs['total'];
    }
    $res_notif_count = mysqli_query($conexion, "SELECT COUNT(*) as total FROM notificaciones WHERE id_usuarios = '$id_logueado' AND leido = 0");
    $total_notif = mysqli_fetch_assoc($res_notif_count)['total'];
    $res_obras_count = mysqli_query($conexion, "SELECT COUNT(*) as total FROM obras WHERE id_usuarios = '$id_usuarios_ver'");
    $total_obras = mysqli_fetch_assoc($res_obras_count)['total'];
    $res_seguidores = mysqli_query($conexion, "SELECT COUNT(*) as total FROM seguidores WHERE id_seguido = '$id_usuarios_ver'");
    $total_seguidores = mysqli_fetch_assoc($res_seguidores)['total'];
    $res_seguidos = mysqli_query($conexion, "SELECT COUNT(*) as total FROM seguidores WHERE id_seguidor = '$id_usuarios_ver'");
    $total_seguidos = mysqli_fetch_assoc($res_seguidos)['total'];
    $query_check_follow = "SELECT * FROM seguidores WHERE id_seguidor = '$id_logueado' AND id_seguido = '$id_usuarios_ver'";
    $res_check = mysqli_query($conexion, $query_check_follow);
    $ya_lo_sigo = mysqli_num_rows($res_check) > 0;
    $res_obras_galeria = mysqli_query($conexion, "SELECT * FROM obras WHERE id_usuarios = '$id_usuarios_ver' ORDER BY id DESC");
    $es_dueno = ($id_logueado == $id_usuarios_ver);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($artista['nombres'] ?? 'Artista'); ?> - GA ARTRÓMEDA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --dorado: #deb887;
            --blur: blur(15px);
            --fondo-oscuro: rgba(0, 0, 0, 0.8);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: url("fondo.png") no-repeat center center fixed;
            background-size: cover;
            color: white;
            min-height: 100vh;
        }

        header {
            padding: 0.1rem 5%;
            background: transparent;
            box-shadow: 0 10px 25px rgb(233, 238, 192);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--dorado);
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: var(--blur);
        }

        .logo-img { 
            height: 70px; 
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .logo-img:hover { 
            transform: scale(1.08) translateY(-3px); 
        }

        .nav-list {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-left: auto;
        }

        .nav-link { 
            text-decoration: none; 
            color: white; 
            font-weight: 500; 
            font-size: 0.8rem;
            letter-spacing: 1px; 
            text-transform: uppercase; 
            position: relative;
            padding: 5px 0; 
            transition: color 0.3s ease;
            margin: 0 10px;
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
            bottom: -2px; 
            left: 0; 
            background-color: #e4924f; 
            transition: width 0.3s ease;
        }

        .nav-link:hover::after { 
            width: 100%; 
        }

        .nav-link-icon {
            color: white;
            text-decoration: none;
            font-size: 1.2rem;
            transition: 0.3s;
            display: flex;
            align-items: center;
            position: relative;
        }

        .nav-link-icon:hover {
            color: #e4924f;
            transform: scale(1.1);
        }

        .cart-badge, .fav-badge, .notif-badge {
            background-color: #d4b483; 
            color: #000;             
            font-size: 10px;  
            font-weight: bold;
            width: 16px;     
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            position: absolute;
            top: -2px;        
            right: -5px;    
            line-height: 1;
        }

        .notif-badge {
            background-color: #ff4d4d;
            color: white;
            border: 1px solid #000;
        }

        .profile-container {
            max-width: 1000px;
            margin: 60px auto;
            padding: 0 20px;
            text-align: center;
        }

        .profile-img {
            width: 170px;
            height: 170px;
            border-radius: 25px;
            object-fit: cover;
            border: 3px solid var(--dorado);
            box-shadow: 0 15px 30px rgba(0,0,0,0.6);
            margin-bottom: 20px;
        }

        .artist-name {
            font-size: 2.5rem;
            text-shadow: 3px 3px 6px rgba(209, 104, 34, 0.67);
            margin-bottom: 5px;
        }

        .artist-specialization {
            color: white;
            text-shadow: 2px 4px 10px rgba(209, 104, 34, 0.88);
            text-transform: uppercase;
            letter-spacing: 3px;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .stats-row { 
            display: flex; 
            justify-content: center; 
            gap: 30px; 
            margin: 25px 0; 
        }

        .stat-item strong { 
            display: block; 
            font-size: 1.4rem; 
            color: green; 
        }

        .stat-item span { 
            font-size: 0.9rem; 
            text-transform: uppercase; 
            opacity: 0.8; 
            letter-spacing: 1px;
            color: #6b3e0a;
        }

        .actions-wrapper {
            margin-bottom: 35px;
        }

        .btn-profile {
            background: transparent;
            color: #596b0a;
            border: 2px solid rgba(90, 55, 9, 0.73);
            display: inline-block;
            padding: 10px 25px;
            border-radius: 30px;
            font-size: 0.8rem;
            text-transform: uppercase;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-fill {
            background: transparent;
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            color: white;
            border-color: white;
        }

        .btn-profile:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(222, 184, 135, 0.3);
        }

        .bio-box {
            background: rgba(247, 245, 245, 0.4);
            backdrop-filter: blur(7px);
            border: 2px solid rgba(240, 141, 12, 0.4);
            color: #111;
            padding: 25px;
            border-radius: 10px;
            margin: 40px auto;
            text-align: left;
            max-width: 800px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        }

        .bio-header {
            border-bottom: 1px solid #e4924f;
            padding-bottom: 10px;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .section-title {
            text-transform: uppercase;
            color: white;
            letter-spacing: 5px;
            border-bottom: 2px solid #6b3e0a;
            padding-bottom: 8px;
            margin: 50px 0 40px;
            display: inline-block;
            text-shadow: 2px 4px 10px rgba(240, 141, 12, 0.49);
        }

        .works-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }

        .work-card {
            background: rgba(0,0,0,0.5);
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid rgba(222,184,135,0.2);
            transition: 0.4s;
            backdrop-filter: blur(5px);
        }

        .work-card:hover {
            transform: translateY(-10px);
            border-color: var(--dorado);
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
        }

        .work-img {
            width: 100%;
            aspect-ratio: 1/1;
            object-fit: cover;
            display: block;
        }

        .work-info {
            padding: 15px;
            text-align: center;
        }

        .work-title {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .btn-ver {
            display: inline-block;
            padding: 10px 20px;
            color: var(--dorado);
            text-decoration: none;
            font-weight: bold;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: 1px solid var(--dorado);
            border-radius: 5px;
            margin-top: 10px;
            transition: 0.3s;
        }

        .btn-ver:hover {
            background: var(--dorado);
            color: black;
        }

        .menu-toggle {
            display: none;
            cursor: pointer;
            font-size: 2rem;
            color: white;
        }

        @media (max-width: 767px) {
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
            <div class="menu-toggle" id="mobile-menu">
                <i class="bi bi-list"></i>
            </div>
            <nav class="nav-list">
                <a href="index.php" class="nav-link">Inicio</a>
                <a href="obras.php" class="nav-link">Galería</a>
                <a href="todos_perfiles.php" class="nav-link">Mi Perfil</a>
                <a href="artistas_lista.php" class="nav-link">Artistas</a>
                <a href="favoritos.php" class="nav-link-icon">
                    <i class="bi bi-heart"></i>
                    <?php if($cantidad_favoritos > 0): ?>
                        <span class="fav-badge" id="fav-count"><?php echo $cantidad_favoritos; ?></span>
                    <?php endif; ?>
                </a>
                <a href="mis_notificaciones.php" class="nav-link-icon">
                    <i class="bi bi-bell"></i>
                    <?php if($total_notif > 0): ?>
                        <span class="notif-badge"><?php echo $total_notif; ?></span>
                    <?php endif; ?>
                </a>
                <a href="carrito.php" class="nav-link-icon">
                    <i class="bi bi-cart3"></i>
                    <?php if($cantidad_carrito > 0): ?>
                        <span class="cart-badge" id="cart-count"><?php echo $cantidad_carrito; ?></span>
                    <?php endif; ?>
                </a>
                <a href="logout.php" class="nav-link-icon"><i class="bi bi-box-arrow-right"></i></a>
            </nav>
        </header>
        <div class="profile-container">
            <img src="img_perfiles/<?php echo $artista['foto_perfil'] ?? 'default.jpg'; ?>" class="profile-img">
            <h1 class="artist-name"><?php echo htmlspecialchars($artista['nombres'] . ' ' . $artista['apellidos']); ?></h1>
            <p class="artist-specialization"><?php echo htmlspecialchars($artista['especializacion'] ?? 'Artista Independiente'); ?></p>
            <div class="stats-row">
                <div class="stat-item">
                    <strong><?php echo $total_obras; ?></strong>
                    <span>Obras</span>
                </div>
                <div class="stat-item">
                    <strong><?php echo $total_seguidores; ?></strong>
                    <span>Seguidores</span>
                </div>
                <div class="stat-item">
                    <strong><?php echo $total_seguidos; ?></strong>
                    <span>Seguidos</span>
                </div>
            </div>
            <div class="actions-wrapper">
                <?php if($es_dueno): ?>
                    <a href="artista.php" class="btn-profile btn-fill">
                        <i class="bi bi-gear-fill"></i> Gestionar mi panel
                    </a>
                <?php else: ?>
                    <form action="procesar_seguimiento.php" method="POST" style="display: inline;">
                        <input type="hidden" name="id_seguido" value="<?php echo $id_usuarios_ver; ?>">
                        <?php if ($ya_lo_sigo): ?>
                            <button type="submit" name="accion" value="unfollow" class="btn-profile btn-outline">
                                <i class="bi bi-person-check"></i> Siguiendo
                            </button>
                        <?php else: ?>
                            <button type="submit" name="accion" value="follow" class="btn-profile btn-fill">
                                <i class="bi bi-person-plus"></i> Seguir Artista
                            </button>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>
            </div>
            <div class="bio-box">
                <h3 class="bio-header">Sobre el Artista</h3>
                <p><?php echo nl2br(htmlspecialchars($artista['biografia'] ?? 'Este artista aún no ha compartido su historia.')); ?></p>
            </div>
            <h2 class="section-title">Obras</h2>
            <div class="works-grid">
                <?php while($obra = mysqli_fetch_assoc($res_obras_galeria)): ?>
                <div class="work-card">
                    <img src="img_obras/<?php echo htmlspecialchars($obra['imagen']); ?>" class="work-img">
                    <div class="work-info">
                        <p class="work-title"><?php echo htmlspecialchars($obra['titulo']); ?></p>
                        <a href="detalle_obras.php?id=<?php echo $obra['id']; ?>" class="btn-ver">Ver Detalles</a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <script>
            const mobileMenu = document.getElementById('mobile-menu');
            const navList = document.querySelector('.nav-list');
            if(mobileMenu && navList) {
                mobileMenu.addEventListener('click', () => {
                    navList.classList.toggle('active');
                    const icon = mobileMenu.querySelector('i');
                    if (navList.classList.contains('active')) {
                        icon.classList.replace('bi-list', 'bi-x-lg');
                    } else {
                        icon.classList.replace('bi-x-lg', 'bi-list');
                    }
                });
            }
        </script>
    </body>
</html>
    
