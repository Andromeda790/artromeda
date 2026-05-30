<?php
    session_start();
    include("conexion.php");
    $query_artistas = "SELECT nombres, apellidos, foto_perfil, biografia, especializacion, id 
                    FROM usuarios
                    WHERE rol = 'artista'";
    $res_artistas = mysqli_query($conexion, $query_artistas);

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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuestros Artistas - GA ARTRÓMEDA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { 
            --dorado: #deb887; 
            --dorado-brillante: #e4924f;
            --blur: blur(15px);
        }

        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: url("fondo.png") no-repeat center center fixed;
            background-size: cover;
            color: white;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            padding: 0.2rem 5%;
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
            height: 72px; 
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .logo-img:hover { 
            transform: scale(1.08) translateY(-3px); 
        }

        .nav-list {
            display: flex;
            align-items: center;
            gap: 15px;
            list-style: none;
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
            color: var(--dorado-brillante); 
        }

        .nav-link::after {
            content: ''; 
            position: absolute;
            width: 0; 
            height: 2px;
            bottom: -2px; 
            left: 0; 
            background-color: var(--dorado-brillante); 
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
            margin: 0 5px;
        }

        .nav-link-icon:hover {
            color: var(--dorado-brillante);
            transform: scale(1.1);
        }

        .cart-badge, .fav-badge {
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
            top: -4px;        
            right: -8px;    
            line-height: 1;
            padding: 0;
        }

        .container { 
            padding: 60px 5%; 
            flex-grow: 1;
        }
        
        .titulo-seccion {
            color: #faf8f8;
            text-align: center; 
            text-transform: uppercase; 
            letter-spacing: 4px; 
            margin-bottom: 60px;
            font-size: 2.5rem;
            text-shadow: 2px 4px 10px rgba(209, 104, 34, 0.67);           
        }

        .artistas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 40px;
        }

        .artista-card {
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(230, 137, 17, 0.3);
            border-radius: 25px;
            padding: 35px;
            text-align: center;
            transition: 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .artista-card:hover {
            transform: translateY(-10px);
            border-color: var(--dorado);
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
        }

        .avatar-perfil {
            width: 125px;
            height: 125px;
            border-radius: 20%;
            margin: 0 auto 20px;
            border: 2.5px solid var(--dorado);
            overflow: hidden;
            background: #1a1a1a;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-perfil img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .artista-card h3 { 
            color: var(--dorado); 
            margin-bottom: 5px;
            font-size: 1.4rem;
        }

        .especialidad { 
            font-size: 0.8rem; 
            font-weight: bold; 
            text-transform: uppercase; 
            color: #dacda6; 
            letter-spacing: 1px;
            margin-bottom: 20px; 
        }

        .biografia { 
            font-size: 0.95rem; 
            color: #eee; 
            line-height: 1.6; 
            margin-bottom: 25px; 
        }

        .btn-ver-obras {
            text-decoration: none; 
            color: #000; 
            background: var(--dorado);
            padding: 12px; 
            border-radius: 10px; 
            font-weight: bold; 
            font-size: 0.85rem;
            transition: 0.3s;
            text-transform: uppercase;
            display: block;
        }

        .btn-ver-obras:hover { 
            background: white; 
            letter-spacing: 1px;
        }

        .main-footer {
            background: transparent;
            backdrop-filter: blur(10px);
            border-top: 1px solid var(--dorado);
            padding: 20px 7%;
            margin-top: 50px;
        }

        .footer-content { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            color: #070707; 
            font-size: 0.9rem;  
        }

        .btn-whatsapp {
            background: #25d366; 
            color: white; 
            text-decoration: none;
            padding: 10px 20px; 
            border-radius: 7px; 
            font-weight: bold;
            display: flex; 
            align-items: center; 
            gap: 10px; 
            transition: 0.3s;
        }

        .btn-whatsapp:hover {
            background: #20ba5a;
            transform: translateY(-2px);
        }

        .menu-toggle {
            display: none; 
            cursor: pointer;
            font-size: 2.2rem;
            color: white;
            z-index: 1001;
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
                margin: 10px 0;
            }
            
            .cart-badge, .fav-badge {
                top: -2px;
                right: -10px;
            }

            .titulo-seccion {
                font-size: 2rem;
                margin-bottom: 40px;
            }
            
            .artistas-grid {
                gap: 25px;
            }

            .footer-content { 
                flex-direction: column; 
                gap: 15px; 
                text-align: center; 
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
            <nav>
                <ul class="nav-list">
                    <li><a href="index.php" class="nav-link">Inicio</a></li>
                    <li><a href="obras.php" class="nav-link">Galería</a></li>
                    <li><a href="todos_perfiles.php" class="nav-link">Mi Perfil</a></li>
                    <li><a href="artistas_lista.php" class="nav-link">Artistas</a></li>
                    <li>
                        <a href="favoritos.php" class="nav-link-icon">
                            <i class="bi bi-heart"></i>
                            <span class="fav-badge" id="fav-count" style="display: <?php echo ($cantidad_favoritos > 0) ? 'flex' : 'none'; ?>;">
                                <?php echo $cantidad_favoritos; ?>
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="carrito.php" class="nav-link-icon">
                            <i class="bi bi-cart3"></i>
                            <span class="cart-badge" id="cart-count" style="display: <?php echo ($cantidad_carrito > 0) ? 'flex' : 'none'; ?>;">
                                <?php echo $cantidad_carrito; ?>
                            </span>
                        </a>
                    </li>
                    <li><a href="logout.php" class="nav-link-icon"><i class="bi bi-box-arrow-right"></i></a></li>
                </ul>
            </nav>
        </header>

        <div class="container">
            <h1 class="titulo-seccion">Artistas</h1>
            <div class="artistas-grid">
                <?php while($art = mysqli_fetch_assoc($res_artistas)): ?>
                    <div class="artista-card">
                        <div>
                            <div class="avatar-perfil">
                                <?php if (!empty($art['foto_perfil'])): ?>
                                    <img src="img_perfiles/<?php echo $art['foto_perfil']; ?>" alt="Artista">
                                <?php else: ?>
                                    <i class="bi bi-person-circle" style="font-size: 4rem; color: var(--dorado);"></i>
                                <?php endif; ?>
                            </div>
                            <h3><?php echo htmlspecialchars($art['nombres'] . " " . $art['apellidos']); ?></h3>
                            <p class="especialidad"><?php echo !empty($art['especializacion']) ? htmlspecialchars($art['especializacion']) : 'Artista Independiente'; ?></p>
                            <p class="biografia">
                                <?php 
                                    $bio = !empty($art['biografia']) ? $art['biografia'] : 'Explorando nuevas formas de expresión en GA ARTRÓMEDA.';
                                    echo htmlspecialchars((strlen($bio) > 120) ? substr($bio, 0, 117) . "..." : $bio);
                                ?>
                            </p>
                        </div>
                        <a href="perfil_artista.php?id=<?php echo $art['id']; ?>" class="btn-ver-obras">VER PERFIL</a>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <footer class="main-footer">
            <div class="footer-content">
                <p>&copy; 2026 Artrómeda - Galería de Arte Digital</p>
                <a href="https://wa.me/573197889132" class="btn-whatsapp" target="_blank">
                    <i class="bi bi-whatsapp"></i> Contáctanos
                </a>
            </div>
        </footer>

        <script>
            const mobileMenu = document.getElementById('mobile-menu');
            const navList = document.querySelector('.nav-list');
            
            if(mobileMenu && navList) {
                const icon = mobileMenu.querySelector('i');
                mobileMenu.addEventListener('click', () => {
                    navList.classList.toggle('active');
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