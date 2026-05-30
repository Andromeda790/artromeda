<?php
    session_start();
    include("conexion.php");
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit();
    }
    $id_logueado = (int)$_SESSION['usuario_id'];
    $cantidad_carrito = 0;
    if (isset($_SESSION['carrito'])) {
        $cantidad_carrito = count($_SESSION['carrito']);
    }
    $cantidad_favoritos = 0;
    if (isset($_SESSION['favoritos'])) {
        $cantidad_favoritos = count($_SESSION['favoritos']);
    }
    $query_favs = "SELECT o.*, u.nombres, u.apellidos 
                   FROM favoritos f
                   JOIN obras o ON f.id_obras = o.id
                   JOIN usuarios u ON o.id_usuarios = u.id
                   WHERE f.id_usuarios = '$id_logueado'
                   ORDER BY f.id DESC";
    $res_favs = mysqli_query($conexion, $query_favs);
    $cantidad_favoritos = mysqli_num_rows($res_favs);
    $enlace_perfil = (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'administrador') ? "administrador.php" : "perfil.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Colección - Artrómeda</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --dorado: #deb887;
            --rojo: #ff4d4d;
            --fondo-card: rgba(0, 0, 0, 0.6);
            --borde-oro: rgba(222, 184, 135, 0.4);
        }

        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
            font-family: 'Montserrat', sans-serif; 
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
            font-size: 1.1rem;
            transition: 0.3s;
            display: flex;
            align-items: center;
            position: relative;
        }

        .nav-link-icon:hover {
            color: #e4924f;
            transform: scale(1.1);
        }

        .cart-badge {
            background: var(--dorado);
            color: black;
            font-size: 10px;
            font-weight: bold;
            width: 18px;
            height: 18px;
            display: flex; 
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            position: absolute;
            top: -5px;   
            right: -10px;
        }

        .badge-fav { 
            background: var(--dorado); 
            color: black; 
        }

        .titulo {
            text-align: center;
            padding: 40px 20px 10px;
            font-size: 2.0rem;
            margin-bottom: 40px;
            letter-spacing: 4px;
            text-transform: uppercase;
            text-shadow: 2px 4px 10px rgba(209, 104, 34, 0.67);
        }

        .mensaje {
            text-align: center; 
            padding: 100px 0;
            font-size: 1.0rem;
            margin-bottom: 40px;
            letter-spacing: 4px;
            text-transform: uppercase;
            text-shadow: 2px 4px 10px rgb(59, 31, 12);
        }

        .container-lista {
            max-width: 900px;
            margin: 20px auto 60px;
            padding: 0 20px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .fila-obra {
            display: flex;
            align-items: center;
            background: transparent;
            color: #6b3e0a;
            border: 2px solid rgba(114, 70, 13, 0.67);
            border-radius: 15px;
            padding: 20px;
            backdrop-filter: blur(15px);
            transition: 0.3s ease;
            gap: 20px;
        }

        .fila-obra:hover {
            transform: translateY(-5px);
            border-color: var(--dorado);
        }

        .img-contenedor {
            width: 120px;
            height: 120px;
            flex-shrink: 0;
            background: #000;
            border-radius: 10px;
            overflow: hidden;
        }

        .img-obra { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
        }

        .info-obra { 
            flex-grow: 1; 
            padding: 0 10px; 
        }

        .info-obra h3 { 
            color: #6b3e0a; 
            margin-bottom: 5px; 
        }

        .info-obra p {
            color: #070707;
            font-size: 0.95rem;
        }

        .info-obra p strong {
            color: #000;
        }

        .precio { 
            font-size: 1.2rem; 
            font-weight: 700; 
            color: white; 
            margin-top: 10px; 
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }

        .acciones-obra {
            display: flex; 
            flex-direction: column; 
            gap: 10px; 
            align-items: center;
        }

        .btn-ver {
            background: transparent;
            color: #6b3e0a;
            border: 2px solid rgba(95, 59, 13, 0.64);
            text-decoration: none; 
            padding: 8px 18px;
            border-radius: 20px; 
            font-weight: 700;
            font-size: 0.75rem;
            transition: 0.3s;
            text-align: center;
            white-space: nowrap;
        }

        .btn-ver:hover { 
            background: #dacda6e3;
            transform: translateY(-2px);
        }

        .icon-btn {
            background: none; 
            border: none;
            color: #5f0b0b; 
            font-size: 1.5rem;
            cursor: pointer; 
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .icon-btn:hover { 
            transform: scale(1.2); 
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

            .titulo {
                font-size: 1.6rem;
                padding-top: 25px;
                margin-bottom: 20px;
                letter-spacing: 2px;
            }

            .container-lista {
                padding: 0 15px;
                gap: 15px;
            }

            .fila-obra {
                flex-direction: column;
                text-align: center;
                padding: 20px;
            }

            .img-contenedor {
                width: 140px;
                height: 140px;
                margin-bottom: 5px;
            }

            .info-obra {
                padding: 0;
                margin-bottom: 10px;
            }

            .precio {
                font-size: 1.3rem;
                margin-top: 8px;
            }

            .acciones-obra {
                flex-direction: row;
                gap: 20px;
                justify-content: center;
                width: 100%;
                margin-top: 5px;
            }

            .btn-ver {
                padding: 10px 25px;
                font-size: 0.85rem;
                flex-grow: 1;
                max-width: 200px;
            }

            .icon-btn {
                font-size: 1.8rem;
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
                <a href="index.php" class="nav-link">Inicio</a>
                <a href="obras.php" class="nav-link">Galería</a>
                <a href="artistas_lista.php" class="nav-link">Artistas</a>
                <a href="todos_perfiles.php" class="nav-link">
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
                        <?php if($cantidad_favoritos > 0): ?>
                            <span class="cart-badge badge-fav" id="fav-count"><?php echo $cantidad_favoritos; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="carrito.php" class="nav-link-icon">
                        <i class="bi bi-cart3"></i>
                        <?php if($cantidad_carrito > 0): ?>
                            <span class="cart-badge"><?php echo $cantidad_carrito; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="logout.php" class="nav-link-icon"><i class="bi bi-box-arrow-right"></i></a>
                </div>
            </nav>
        </header>

        <div class="container-lista">
            <h2 class="titulo">MIS FAVORITOS</h2>
            <?php if($cantidad_favoritos > 0): ?>
                <?php while($obra = mysqli_fetch_assoc($res_favs)): ?>
                    <div class="fila-obra" id="obra-<?php echo $obra['id']; ?>">
                        <div class="img-contenedor">
                            <img src="img_obras/<?php echo $obra['imagen']; ?>" class="img-obra">
                        </div>
                        <div class="info-obra">
                            <h3><?php echo htmlspecialchars($obra['titulo']); ?></h3>
                            <p>Por: <strong><?php echo htmlspecialchars($obra['nombres'] . " " . $obra['apellidos']); ?></strong></p>
                            <div class="precio">$<?php echo number_format($obra['precio'], 0, ',', '.'); ?></div>
                        </div>
                        <div class="acciones-obra">
                            <a href="detalle_obras.php?id=<?php echo $obra['id']; ?>" class="btn-ver">DETALLES</a>
                            <button class="icon-btn" onclick="eliminarFav(<?php echo $obra['id']; ?>)">
                                <i class="bi bi-heart-fill"></i>
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="mensaje">
                    <p>Tu lista de favoritos está vacía.</p>
                    <br>
                    <a href="obras.php" class="btn-ver">VER GALERÍA</a>
                </div>
            <?php endif; ?>
        </div>

        <script>
            const mobileMenu = document.getElementById('mobile-menu');
            const navList = document.querySelector('.nav-list');
            if (mobileMenu && navList) {
                mobileMenu.addEventListener('click', function() {
                    navList.classList.toggle('active');
                    const icon = this.querySelector('i');
                    if (navList.classList.contains('active')) {
                        icon.classList.replace('bi-list', 'bi-x-lg');
                    } else {
                        icon.classList.replace('bi-x-lg', 'bi-list');
                    }
                });
            }

            function eliminarFav(idObra) {
                const fd = new FormData();
                fd.append('id', idObra);
                fetch('toggle_favorito.php', { method: 'POST', body: fd })
                .then(res => res.text())
                .then(data => {
                    if (data.trim() === "removed") {
                        const fila = document.getElementById('obra-' + idObra);
                        if(fila) {
                            fila.style.opacity = '0';
                            fila.style.transform = 'translateX(20px)';
                            setTimeout(() => {
                                fila.remove();
                                const badge = document.getElementById('fav-count');
                                if (badge) {
                                    let total = parseInt(badge.innerText) - 1;
                                    if (total <= 0) location.reload();
                                    else badge.innerText = total;
                                }
                            }, 300);
                        }
                    }
                });
            }
        </script>
    </body>
</html>