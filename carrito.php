<?php
    session_start();
    include("conexion.php");
    $hay_productos = isset($_SESSION['carrito']) && !empty($_SESSION['carrito']);
    $res_carrito = null;
    $total_general = 0;

    if ($hay_productos) {
        $ids = array_keys($_SESSION['carrito']);
        $lista_ids = implode(',', $ids);
        $query_carrito = "SELECT * FROM obras WHERE id IN ($lista_ids)";
        $res_carrito = mysqli_query($conexion, $query_carrito);
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu Carrito - Artrómeda</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap">
    <style>
        :root { 
            --dorado: #deb887; 
            --oscuro-card: rgba(30, 30, 30, 0.7);
            --blur-card: blur(20px);
        }

        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
            font-family: 'Segoe UI', sans-serif; 
        }

        body {
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
            backdrop-filter: blur(15px);
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

        .nav-icons {
            display: flex;
            align-items: center;
            gap: 20px;
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
            top: -2px;        
            right: -5px;    
            line-height: 1;
            padding: 0;
        }

        .carrito-wrapper {
            flex: 1;
            padding: 40px 7%;
        }

        .titulo-carrito {
            color: #faf8f8;
            text-align: center; 
            text-transform: uppercase; 
            letter-spacing: 4px; 
            margin-bottom: 50px;
            font-size: 2.5rem;
            text-shadow: 2px 4px 10px rgba(209, 104, 34, 0.67); 
        }

        .carrito-container {
            background: rgba(247, 245, 245, 0.4);
            backdrop-filter: blur(7px);
            border: 2px solid rgba(240, 141, 12, 0.4);
            border-radius: 20px;
            padding: 30px;
            max-width: 900px;
            margin: 0 auto;
        }

        .item-carrito {
            display: flex;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid rgb(241, 167, 7);
            gap: 20px;
        }

        .img-mini {
            width: 80px; 
            height: 80px;
            object-fit: cover; 
            border-radius: 10px;
            border: 1px solid var(--dorado);
        }

        .info-item { 
            flex-grow: 1; 
        }

        .info-item h3 { 
            color: #6b3e0a; 
            margin-bottom: 5px; 
        }

        .info-item p { 
            color: #080808; 
            font-size: 0.9rem; 
        }

        .precio-item { 
            color: #0c0c0c;
            font-weight: bold; 
            font-size: 1.2rem; 
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }

        .btn-quitar {
            color: #850d0d;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            transition: 0.3s;
        }

        .btn-quitar:hover { 
            transform: scale(1.2); 
        }

        .resumen-final {
            margin-top: 30px;
            text-align: right;
            padding-top: 20px;
        }

        .total-texto { 
            font-size: 2rem; 
            font-weight: bold; 
            color: #050505; 
            margin-bottom: 20px; 
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }

        .btn-comprar {
            background: transparent;
            color: #6b3e0a;
            border: 2px solid rgba(94, 59, 13, 0.76);
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: bold;
            text-transform: uppercase;
            cursor: pointer;
            transition: 0.3s;
            width: auto;
        }

        .btn-comprar:hover { 
            background: #dacda6e3;
            transform: translateY(-2px);
        }

        .btn-detalles {
            background: transparent;
            border: 2px solid rgba(240, 141, 12, 0.3);
            text-decoration:none; 
            display:inline-block;
            color: #6b3e0a; 
            padding:10px 25px;
            border-radius:8px; 
            font-weight:bold;
        }

        .btn-detalles:hover {
            background: #dacda6e3;
            transform: translateY(-2px);
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
            color: black; 
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

            .carrito-wrapper {
                padding: 30px 4%;
            }

            .titulo-carrito {
                font-size: 1.8rem;
                margin-bottom: 30px;
            }

            .carrito-container {
                padding: 15px;
            }

            .item-carrito {
                flex-direction: column;
                text-align: center;
                padding: 25px 0;
                position: relative;
            }

            .img-mini {
                width: 120px;
                height: 120px;
                margin-bottom: 10px;
            }

            .info-item {
                margin-bottom: 10px;
            }

            .precio-item {
                font-size: 1.4rem;
                margin-bottom: 15px;
            }

            .resumen-final {
                text-align: center;
            }

            .total-texto {
                font-size: 1.8rem;
            }

            .btn-comprar {
                width: 100%;
                padding: 15px 20px;
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
            <nav class="nav-list">
                <a href="index.php" class="nav-link">Inicio</a>
                <a href="obras.php" class="nav-link">Galería</a>
                <a href="todos_perfiles.php" class="nav-link">Mi Perfil</a>
                <a href="artistas_lista.php" class="nav-link">Artistas</a>
                <a href="favoritos.php" class="nav-link-icon">
                    <i class="bi bi-heart"></i>
                    <span class="fav-badge" id="fav-count" style="display: <?php echo ($cantidad_favoritos > 0) ? 'flex' : 'none'; ?>;">
                        <?php echo $cantidad_favoritos; ?>
                    </span>
                </a>
                <a href="carrito.php" class="nav-link-icon">
                    <i class="bi bi-cart3"></i>
                    <span class="cart-badge" id="cart-count" style="display: <?php echo ($cantidad_carrito > 0) ? 'flex' : 'none'; ?>;">
                        <?php echo $cantidad_carrito; ?>
                    </span>
                </a>
                <a href="logout.php" class="nav-link-icon"><i class="bi bi-box-arrow-right"></i></a>
            </nav>
        </header>

        <div class="carrito-wrapper">
            <h1 class="titulo-carrito">Tus Obras</h1>
            <div class="carrito-container">
                <?php if($hay_productos && $res_carrito): ?>
                    <?php while($item = mysqli_fetch_assoc($res_carrito)): 
                        $total_general += $item['precio'];
                    ?>
                        <div class="item-carrito">
                            <img src="img_obras/<?php echo $item['imagen']; ?>" class="img-mini">
                            <div class="info-item">
                                <h3><?php echo htmlspecialchars($item['titulo']); ?></h3>
                                <p>Obra de Arte Original</p>
                            </div>
                            <div class="precio-item">
                                $<?php echo number_format($item['precio'], 0, ',', '.'); ?>
                            </div>
                            <form action="eliminar_carrito.php" method="POST">
                                <input type="hidden" name="id_eliminar" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="btn-quitar">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                    <div class="resumen-final">
                        <p style="color: #693614; margin-bottom: 10px;">Subtotal de obras</p>
                        <div class="total-texto">$<?php echo number_format($total_general, 0, ',', '.'); ?> COP</div>
                        <button onclick="window.location.href='checkout.php'" class="btn-comprar">COMPRAR ADQUISICIÓN</button>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 60px 20px;">
                        <i class="bi bi-cart-x" style="font-size: 4rem; color: #6b3e0a;"></i>
                        <p style="margin: 20px 0; font-size: 1.2rem; color: #6b3e0a;">Tu carrito está esperando por arte.</p>
                        <a href="obras.php" class="btn-detalles">EXPLORAR GALERÍA</a>
                    </div>
                <?php endif; ?>
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
    </body>
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
</html>