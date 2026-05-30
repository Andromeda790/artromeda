<?php
    session_start();
    include("conexion.php");
    $buscar = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : "";
    $id_usuarios_filtro = isset($_GET['artista']) ? mysqli_real_escape_string($conexion, $_GET['artista']) : "";
    $query = "SELECT o.*, u.nombres, u.apellidos 
            FROM obras o 
            JOIN usuarios u ON o.id_usuarios = u.id
            WHERE o.estado = 'aprobada'";
    if ($id_usuarios_filtro != "") {
        $query .= " AND o.id_usuarios = '$id_usuarios_filtro'";
        $res_nombre = mysqli_query($conexion, "SELECT nombres, apellidos FROM usuarios WHERE id = '$id_usuarios_filtro'");
        $artista_nom = mysqli_fetch_assoc($res_nombre);
    } elseif ($buscar != "") {
        $query .= " AND (o.titulo LIKE '%$buscar%' OR u.nombres LIKE '%$buscar%')";
    }
    $query .= " ORDER BY o.fecha_subida DESC";
    $res_obras = mysqli_query($conexion, $query);
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
    $query_titulos = "SELECT titulo AS sugerencia FROM obras";
    $query_artistas = "SELECT DISTINCT nombres AS sugerencia FROM usuarios WHERE rol IN ('artista', 'usuario')";
    $res_titulos = mysqli_query($conexion, $query_titulos);
    $res_artistas = mysqli_query($conexion, $query_artistas);
    $sugerencias = [];
    while($fila = mysqli_fetch_assoc($res_titulos)) { $sugerencias[] = $fila['sugerencia']; }
    while($fila = mysqli_fetch_assoc($res_artistas)) { $sugerencias[] = $fila['sugerencia']; }
    $sugerencias = array_unique($sugerencias);
    $enlace_perfil = "perfil.php"; 
    if (isset($_SESSION['usuario_rol'])) {
        $rol = $_SESSION['usuario_rol'];
        $nivel = isset($_SESSION['usuario_nivel']) ? $_SESSION['usuario_nivel'] : 0;
        if ($rol === 'administrador' || $rol === 'admin') {
            if ($nivel == 3) {
                $enlace_perfil = "admin_dashboard.php";
            } 
            else {
                $enlace_perfil = "administrador.php";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galería de Arte - Artrómeda</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap">
    <style>
        :root { 
            --dorado: #deb887; 
            --oscuro-card: rgba(30, 30, 30, 0.6);
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
            font-size: 0.85rem;
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

        .fav-badge, .cart-badge {
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
            top: -6px;
            right: -10px;
            line-height: 1;
        }

        .cart-badge {
            background: var(--dorado);
            color: black;
        }

        .titulo-galeria {
            text-align: center;
            padding: 40px 20px 10px;
            font-size: 2.5rem;
            margin-bottom: 20px;
            letter-spacing: 4px;
            text-transform: uppercase;
            text-shadow: 2px 4px 10px rgba(209, 104, 34, 0.67);
        }

        .info-filtro {
            text-align: center;
            margin-bottom: 25px;
            padding: 0 20px;
        }

        .info-filtro .texto-artista {
            font-size: 1.1rem;
            margin-bottom: 8px;
        }

        .info-filtro .ver-todas {
            color: var(--dorado);
            text-decoration: none;
            font-size: 0.9rem;
            border-bottom: 1px dashed var(--dorado);
        }

        .search-container { 
            max-width: 500px; 
            margin: 0 auto 40px; 
            padding: 0 20px; 
        }

        .search-form { 
            display: flex; 
            gap: 10px; 
        }

        .search-input {
            background: rgba(247, 245, 245, 0.4);
            backdrop-filter: blur(7px);
            border: 2px solid rgb(107, 66, 13);
            flex: 1; 
            padding: 7px 20px; 
            border-radius: 7px;
            color: black; 
            outline: none; 
            font-size: 1rem;
        }

        .search-button { 
            background: rgba(247, 245, 245, 0.4);
            backdrop-filter: blur(7px);
            border: 2px solid rgb(97, 60, 11);
            padding: 0 22px; 
            border-radius: 7px; 
            cursor: pointer; 
            color: black; 
            font-size: 1rem;
            transition: 0.3s;
        }
        .search-button:hover {
            background: transparent;
            color: #6b3e0a;
        }

        .gallery-container {
            padding: 20px 7%;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); 
            gap: 40px;
        }

        .obra-card {
            background: var(--oscuro-card);
            backdrop-filter: var(--blur-card);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94), box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .obra-card:hover {
            transform: scale(1.02);
            border-color: var(--dorado); 
            box-shadow: 0 10px 25px rgba(190, 206, 101, 0.75);
        }

        .obra-img-container { 
            width: 100%;
            aspect-ratio: 1 / 1; 
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #111;
        }

        .obra-img { 
            width: 100%;      
            height: 100%;  
            object-fit: cover; 
            object-position: center;  
        }

        .obra-info { 
            padding: 20px; 
            flex-grow: 1; 
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .obra-titulo { 
            font-size: 1.2rem; 
            color: #fff; 
            margin-bottom: 5px; 
        }

        .obra-artista { 
            font-size: 0.9rem; 
            color: #bbb; 
            margin-bottom: 15px; 
        }

        .footer-card { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-top: auto;
        }

        .obra-precio { 
            font-size: 1.2rem; 
            font-weight: bold; 
            color: #fff; 
        }

        .btn-detalles {
            background: var(--dorado); 
            color: black; 
            text-decoration: none;
            padding: 8px 16px; 
            border-radius: 6px; 
            font-size: 0.8rem;
            font-weight: bold; 
            text-transform: uppercase; 
            transition: 0.3s;
        }

        .btn-detalles:hover {
            background: transparent;
            color: var(--dorado);
            box-shadow: inset 0 0 0 2px var(--dorado);
        }

        .main-footer {
            background: transparent;
            backdrop-filter: blur(10px);
            border-top: 1px solid var(--dorado);
            padding: 25px 7%;
            margin-top: 60px;
        }

        .footer-content { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            color: #0a0a0a; 
            font-size: 0.9rem;
        }

        .btn-whatsapp {
            background: #25d366; 
            color: white; 
            text-decoration: none;
            padding: 10px 20px; 
            border-radius: 7px; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-whatsapp:hover {
            background: #20ba5a;
            transform: translateY(-2px);
        }

        .menu-toggle {
            display: none;
            cursor: pointer;
            font-size: 1.8rem;
            color: white;
            transition: 0.3s;
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

            .titulo-galeria {
                font-size: 1.8rem;
                padding-top: 25px;
                letter-spacing: 2px;
            }

            .footer-content {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .footer-content p {
                order: 2; 
                font-size: 0.85rem;
                opacity: 0.7;
            }

            .btn-whatsapp {
                order: 1; 
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 400px) {
            .gallery-container {
                padding: 15px 4%;
                gap: 25px;
            }
            .nav-list {
                width: 85%;
            }
        }
    </style>
</head>
    <body>
        <header>
            <a href="index.php">
                <img src="Logo.png" alt="Logo" class="logo-img">
            </a>
            <div class="menu-toggle" id="mobile-menu">
                <i class="bi bi-list"></i>
            </div>
            <nav class="nav-list">
                <a href="obras.php" class="nav-link">Galería</a>
                <a href="artistas_lista.php" class="nav-link">Artistas</a>
                <a href="<?php echo $enlace_perfil; ?>" class="nav-link">
                    <i class="bi bi-person-circle"></i> 
                    <?php 
                        if(isset($_SESSION['usuario_nombre'])){
                            echo htmlspecialchars($_SESSION['usuario_nombre']);
                            if($_SESSION['usuario_rol'] === 'administrador') echo " (Admin)";
                        } else {
                            echo "MI PERFIL";
                        }
                    ?>
                </a>
                <div class="nav-icons">
                    <a href="favoritos.php" class="nav-link-icon">
                        <i class="bi bi-heart"></i>
                        <span class="fav-badge" id="fav-count" style="display: <?php echo ($cantidad_favoritos > 0) ? 'flex' : 'none'; ?>;">
                            <?php echo $cantidad_favoritos; ?>
                        </span>
                    </a>
                    <a href="carrito.php" class="nav-link-icon">
                        <i class="bi bi-cart3"></i>
                        <?php if($cantidad_carrito > 0): ?><span class="cart-badge"><?php echo $cantidad_carrito; ?></span><?php endif; ?>
                    </a>
                    <a href="logout.php" class="nav-link-icon"><i class="bi bi-box-arrow-right"></i></a>
                </div>
            </nav>
        </header>

        <h1 class="titulo-galeria">Galería de Arte</h1>

        <?php if ($id_usuarios_filtro != "" && isset($artista_nom)): ?>
            <div class="info-filtro">
                <p class="texto-artista">Colección privada de: <strong><?php echo htmlspecialchars($artista_nom['nombres']." ".$artista_nom['apellidos']); ?></strong></p>
                <a href="obras.php" class="ver-todas">Ver todas las obras</a>
            </div>
        <?php endif; ?>

        <div class="search-container">
            <form action="obras.php" method="GET" class="search-form">
                <input type="text" name="buscar" class="search-input" placeholder="Buscar por obra o artista..." value="<?php echo htmlspecialchars($buscar); ?>" list="sugerencias">
                <datalist id="sugerencias">
                    <?php foreach($sugerencias as $s): ?><option value="<?php echo htmlspecialchars($s); ?>"><?php endforeach; ?>
                </datalist>
                <button type="submit" class="search-button"><i class="bi bi-search"></i></button>
            </form>
        </div>

        <div class="gallery-container">
            <?php if(mysqli_num_rows($res_obras) > 0): ?>
                <?php while($obra = mysqli_fetch_assoc($res_obras)): ?>
                    <div class="obra-card">
                        <div class="obra-img-container">
                            <img src="img_obras/<?php echo $obra['imagen']; ?>" class="obra-img" alt="Obra">
                        </div>
                        <div class="obra-info">
                            <div>
                                <h3 class="obra-titulo"><?php echo htmlspecialchars($obra['titulo']); ?></h3>
                                <p class="obra-artista">Por: <?php echo htmlspecialchars($obra['nombres'] . " " . $obra['apellidos']); ?></p>
                            </div>
                            <div class="footer-card">
                                <span class="obra-precio">$<?php echo number_format($obra['precio'], 0, ',', '.'); ?></span>
                                <a href="detalle_obras.php?id=<?php echo $obra['id']; ?>" class="btn-detalles">DETALLES</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-results" style="grid-column: 1 / -1; padding: 40px 0;">
                    <i class="bi bi-emoji-frown" style="font-size: 3rem; display: block; text-align: center; color: var(--dorado);"></i>
                    <p style="text-align: center; margin-top: 10px;">No se encontraron obras disponibles.</p>
                </div>
            <?php endif; ?>
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
            if (mobileMenu) {
                mobileMenu.addEventListener('click', function() {
                    navList.classList.toggle('active');
                    const icon = this.querySelector('i');
                    icon.classList.toggle('bi-list');
                    icon.classList.toggle('bi-x-lg');
                });
            }
        </script>   
    </body>
</html>