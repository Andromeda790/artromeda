<?php
    session_start();
    include("conexion.php");

    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $id_usuarios_sesion = isset($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : 0;
    $query = "SELECT o.*, u.nombres, u.apellidos, u.foto_perfil 
              FROM obras o 
              JOIN usuarios u ON o.id_usuarios = u.id
              WHERE o.id = '$id'";
    $res = mysqli_query($conexion, $query);
    $obra = mysqli_fetch_assoc($res);

    if (!$obra) { 
        header("Location: obras.php"); 
        exit; 
    }

    $query_rating = "SELECT AVG(puntuacion) as promedio, COUNT(id) as total FROM calificaciones WHERE id_obras = '$id'";
    $res_rating = mysqli_query($conexion, $query_rating);
    $rating_data = mysqli_fetch_assoc($res_rating);
    $promedio_actual = round($rating_data['promedio'], 1);

    $mi_puntuacion = 0;
    $es_favorito = false;
    $cantidad_favoritos = 0; 

    if ($id_usuarios_sesion > 0) {
        $query_mi_voto = "SELECT puntuacion FROM calificaciones WHERE id_obras = '$id' AND id_usuarios = '$id_usuarios_sesion'";
        $res_mi_voto = mysqli_query($conexion, $query_mi_voto);
        if ($fila_voto = mysqli_fetch_assoc($res_mi_voto)) { 
            $mi_puntuacion = (int)$fila_voto['puntuacion']; 
        }

        $query_fav = "SELECT id FROM favoritos WHERE id_obras = '$id' AND id_usuarios = '$id_usuarios_sesion'";
        $res_fav = mysqli_query($conexion, $query_fav);
        if (mysqli_num_rows($res_fav) > 0) { 
            $es_favorito = true; 
        }

        $query_count_fav = "SELECT COUNT(*) as total FROM favoritos WHERE id_usuarios = '$id_usuarios_sesion'";
        $res_count_fav = mysqli_query($conexion, $query_count_fav);
        $data_fav = mysqli_fetch_assoc($res_count_fav);
        $cantidad_favoritos = $data_fav['total'];
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enviar_comentario'])) {
        if ($id_usuarios_sesion > 0) {
            $texto = mysqli_real_escape_string($conexion, trim($_POST['texto_comentario']));
            if (!empty($texto)) {
                $query_ins = "INSERT INTO comentarios (id_obras, id_usuarios, comentario) VALUES ('$id', '$id_usuarios_sesion', '$texto')";
                mysqli_query($conexion, $query_ins);
                header("Location: detalle_obras.php?id=$id#seccion-comentarios");
                exit;
            }
        } else {
            header("Location: login.php");
            exit;
        }
    }

    $query_comentarios = "SELECT c.*, u.nombres, u.foto_perfil 
                          FROM comentarios c 
                          JOIN usuarios u ON c.id_usuarios = u.id 
                          WHERE c.id_obras = '$id' 
                          ORDER BY c.fecha DESC";
    $res_comentarios = mysqli_query($conexion, $query_comentarios);
    $total_comentarios = mysqli_num_rows($res_comentarios);

    $cantidad_carrito = isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0;
    $enlace_perfil = isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'administrador' ? "administrador.php" : "perfil.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($obra['titulo']); ?> - Artrómeda</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --dorado: #deb887;
            --blanco: #ffffff;
            --negro-vidrio: rgba(0, 0, 0, 0.75);
            --borde-oro: rgba(222, 184, 135, 0.4);
            --sombra-oro: rgba(224, 209, 121, 0.7);
            --naranja-accent: #e4924f;
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
            line-height: 1.4;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.1rem 5%;
            background: transparent;
            box-shadow: 0 10px 25px rgba(233, 238, 192, 0.15);
            border-bottom: 1px solid var(--dorado);
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
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
            color: var(--naranja-accent); 
        }

        .nav-link::after {
            content: ''; 
            position: absolute;
            width: 0; 
            height: 2px;
            bottom: -2px; 
            left: 0; 
            background-color: var(--naranja-accent); 
            transition: width 0.3s ease;
        }

        .nav-link:hover::after { 
            width: 100%; 
        }

        .nav-icons-mobile {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .nav-icon-link {
            color: white;
            font-size: 1.2rem;
            position: relative;
            text-decoration: none;
            transition: color 0.3s, transform 0.3s;
        }

        .nav-icon-link:hover {
            color: var(--naranja-accent);
            transform: scale(1.1);
        }

        .nav-link-icon {
            color: white;
            font-size: 1.2rem;
            transition: 0.3s;
        }

        .nav-link-icon:hover {
            color: #ff4a4a;
        }

        .cart-badge {
            background-color: #d4b483; 
            color: #1a1a1a;             
            font-size: 10px;  
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

        .main-container {
            max-width: 950px;
            width: 90%;
            margin: 30px auto;
            color: #2c2c2c;
        }

        .fila-detalle {
            display: flex;
            align-items: center;
            gap: 30px;
            background: rgba(247, 245, 245, 0.4);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 2px solid rgba(240, 141, 12, 0.4);
            border-radius: 20px;
            padding: 25px 35px;
            margin-bottom: 20px;
            box-shadow: 0 20px 40px rgba(182, 209, 139, 0.4);
        }

        .col-texto {
            flex: 1.2;
        }

        .col-media {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }

        .titulo-seccion {
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #6b3e0a;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .caja-blanca {
            background: white;
            color: #1a1a1a;
            padding: 18px;
            border-radius: 12px;
            font-size: 0.95rem;
            box-shadow: inset 0 2px 5px rgba(0,0,0,0.05);
        }

        .precio-tag {
            font-size: 1.6rem;
            font-weight: 700;
            color: #6b3e0a;
        }

        .img-obra-preview {
            width: 180px;
            height: auto;
            border-radius: 10px;
            border: 1px solid rgba(0,0,0,0.1);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .stars-container {
            display: flex;
            gap: 4px;
            font-size: 1.4rem;
        }

        #stars-voter i {
            color: #ca9249;
            cursor: pointer;
            transition: color 0.2s, transform 0.2s;
        }

        #stars-voter i.active {
            color: #ffc107;
        }

        #stars-voter i:hover {
            transform: scale(1.2);
        }

        .rating-wrapper {
            background: rgba(0, 0, 0, 0.05);
            padding: 15px;
            border-radius: 12px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .rating-numero {
            font-size: 0.9rem;
            color: #333;
        }

        .img-artista-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid var(--dorado);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .acciones-obra {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .btn-accion-compacto, .btn-favorito, .btn-carrito-v2 {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            border: none;
            transition: 0.3s;
            color: white;
            background: #6b3e0a;
        }

        .btn-favorito {
            background: #e5e5e5;
            color: #333;
        }

        .btn-favorito.es-fav-activo {
            background: #ff4a4a;
            color: white;
        }

        .btn-accion-compacto:hover, .btn-favorito:hover, .btn-carrito-v2:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            width: 100%;
        }

        .bubble-comment {
            background: white;
            width: 100%;
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .bubble-comment textarea {
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 10px;
            outline: none;
            resize: none;
            min-height: 70px;
            font-size: 0.9rem;
            color: #333;
            margin-bottom: 10px;
        }

        .item-comentario {
            display: flex;
            gap: 15px;
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #4bdb76;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .foto-mini {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
        }

        .com-info h4 {
            color: #6b3e0a;
            font-size: 0.95rem;
            margin-bottom: 3px;
        }

        .com-info p {
            font-size: 0.9rem;
            color: #444;
        }

        .menu-toggle {
            display: none;
            cursor: pointer;
            font-size: 2rem;
            color: white;
            background: transparent; 
            border: none;          
            outline: none;         
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

            .fila-detalle {
                flex-direction: column;
                padding: 25px;
                text-align: center;
            }

            .acciones-obra {
                flex-direction: column;
            }

            .header-flex {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
    <body>
        <header>
            <a href="index.php"><img src="Logo.png" class="logo-img" alt="Logo"></a>
            <button class="menu-toggle" id="menu-toggle">
                <i class="bi bi-list"></i>
            </button>
            <nav class="nav-list" id="nav-menu">
                <a href="obras.php" class="nav-link">Galería</a>
                <a href="artistas_lista.php" class="nav-link">Artistas</a>
                <a href="<?php echo $enlace_perfil; ?>" class="nav-link"><i class="bi bi-person-circle"></i> Mi Perfil</a>
                <div class="nav-icons-mobile">
                    <a href="favoritos.php" class="nav-icon-link">
                        <i class="bi bi-heart"></i>
                        <?php if ($cantidad_favoritos > 0): ?>
                            <span class="cart-badge" id="fav-count"><?php echo $cantidad_favoritos; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="carrito.php" class="nav-icon-link">
                        <i class="bi bi-cart3"></i>
                        <?php if ($cantidad_carrito > 0): ?>
                            <span class="cart-badge" id="cart-count"><?php echo $cantidad_carrito; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="logout.php" class="nav-icon-link" title="Cerrar Sesión"><i class="bi bi-box-arrow-right"></i></a>
                </div>
            </nav>
        </header>

        <div class="main-container">
            <div class="fila-detalle">
                <div class="col-texto">
                    <h2 class="titulo-seccion">Descripción</h2>
                    <div class="caja-blanca">
                        <h3 style="color:#6b3e0a; margin-bottom: 10px;"><?php echo htmlspecialchars($obra['titulo']); ?></h3>
                        <?php echo nl2br(htmlspecialchars($obra['descripcion'])); ?>
                    </div>
                    <div class="acciones-obra">
                        <a href="#" class="btn-carrito-v2 btn-ajax-carrito" 
                        data-id="<?php echo $id; ?>" 
                        data-titulo="<?php echo htmlspecialchars($obra['titulo']); ?>" 
                        data-precio="<?php echo $obra['precio']; ?>">
                            <i class="bi bi-cart-plus"></i> AGREGAR AL CARRITO
                        </a> 
                        <a href="#" class="btn-favorito <?php echo $es_favorito ? 'es-fav-activo' : ''; ?>" id="btn-add-fav" data-id="<?php echo $id; ?>">
                            <i class="bi <?php echo $es_favorito ? 'bi-heart-fill' : 'bi-heart'; ?>"></i> 
                            <span><?php echo $es_favorito ? 'FAVORITO' : 'AGREGAR A FAVORITOS'; ?></span>
                        </a>
                    </div>
                </div>
                <div class="col-media">
                    <img src="img_obras/<?php echo $obra['imagen']; ?>" class="img-obra-preview" alt="Obra">
                    <span class="precio-tag">$<?php echo number_format($obra['precio'], 0, ',', '.'); ?> COP</span>
                    
                    <div class="rating-wrapper">
                        <div class="stars-container" id="stars-voter" data-id-obra="<?php echo $id; ?>">
                            <?php for ($i = 1; $i <= 5; $i++): 
                                $clase = ($i <= $mi_puntuacion) ? 'bi-star-fill active' : 'bi-star';
                            ?>
                                <i class="bi <?php echo $clase; ?>" data-value="<?php echo $i; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <div class="rating-numero">Promedio: <strong><?php echo $promedio_actual; ?></strong> / 5</div>
                    </div>
                </div>
            </div>

            <div class="fila-detalle">
                <div style="flex:0;">
                    <img src="img_perfiles/<?php echo $obra['foto_perfil'] ?: 'default.png'; ?>" class="img-artista-preview" alt="Artista">
                </div>
                <div class="col-texto">
                    <div class="header-flex">
                        <h2 class="titulo-seccion">Autoría</h2>
                        <a href="perfil_artista.php?id=<?php echo $obra['id_usuarios']; ?>" class="btn-accion-compacto">VER ARTISTA</a>
                    </div>
                    <div class="caja-blanca" style="border-left: 4px solid var(--dorado); font-style: italic;">
                        "Esta pieza única desarrollada por <strong><?php echo htmlspecialchars($obra['nombres'] . ' ' . $obra['apellidos']); ?></strong> forma parte del ecosistema exclusivo de Artrómeda."
                    </div>
                </div>
            </div>

            <div class="fila-detalle" style="flex-direction:column;" id="seccion-comentarios">
                <h2 class="titulo-seccion">Comunidad (<?php echo $total_comentarios; ?>)</h2>
                
                <form method="POST" class="bubble-comment">
                    <textarea name="texto_comentario" placeholder="Comparte tu opinión sobre esta obra..." required></textarea>
                    <div style="text-align:right;">
                        <button type="submit" name="enviar_comentario" class="btn-accion-compacto">PUBLICAR COMENTARIO</button>
                    </div>
                </form>

                <div style="width:100%;">
                    <?php if ($total_comentarios == 0): ?>
                        <p style="text-align:center; color:#666; font-size:0.9rem;">Sé el primero en dejar un comentario.</p>
                    <?php else: ?>
                        <?php while ($com = mysqli_fetch_assoc($res_comentarios)): ?>
                            <div class="item-comentario">
                                <img src="img_perfiles/<?php echo $com['foto_perfil'] ?: 'default.png'; ?>" class="foto-mini" alt="Perfil">
                                <div class="com-info">
                                    <h4><?php echo htmlspecialchars($com['nombres']); ?></h4>
                                    <p><?php echo nl2br(htmlspecialchars($com['comentario'])); ?></p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <script>
            // Menú Responsive Móvil
            const menuToggle = document.getElementById('menu-toggle');
            const navMenu = document.getElementById('nav-menu');
            menuToggle.addEventListener('click', () => {
                navMenu.classList.toggle('active');
                const icon = menuToggle.querySelector('i');
                icon.classList.toggle('bi-list');
                icon.classList.toggle('bi-x-lg');
            });

            document.querySelector('.btn-ajax-carrito').addEventListener('click', function(e) {
                e.preventDefault();
                const cartBadge = document.getElementById('cart-count');
                const fd = new FormData();
                fd.append('id', this.dataset.id);
                fd.append('titulo', this.dataset.titulo);
                fd.append('precio', this.dataset.precio);

                fetch('agregar_carrito.php', { method: 'POST', body: fd })
                .then(res => res.text())
                .then(nuevoTotal => {
                    if (cartBadge) {
                        cartBadge.innerText = nuevoTotal;
                    } else {
                        location.reload(); 
                    }
                });
            });

            document.getElementById('btn-add-fav').addEventListener('click', function(e) {
                e.preventDefault(); 
                const btn = this;
                const icono = btn.querySelector('i');
                const texto = btn.querySelector('span');
                const favBadge = document.getElementById('fav-count');
                const fd = new FormData();
                fd.append('id_obras', this.dataset.id);

                fetch('agregar_favoritos.php', { method: 'POST', body: fd })
                .then(res => res.text())
                .then(data => {
                    if (!isNaN(data.trim())) { 
                        icono.classList.replace('bi-heart', 'bi-heart-fill');
                        btn.classList.add('es-fav-activo');
                        texto.innerText = "FAVORITO";
                        if (favBadge) favBadge.innerText = data;
                    } else if (data.trim() === "exists") {
                        alert("Esta obra ya se encuentra en tus favoritos.");
                    } else if (data.trim() === "error_auth") {
                        window.location.href = "login.php";
                    }
                });
            });
            
            const estrellas = document.querySelectorAll('#stars-voter i');
            
            estrellas.forEach(star => {
                star.addEventListener('click', function() {
                    const puntuacionSeleccionada = parseInt(this.dataset.value);
                    const contenedor = document.getElementById('stars-voter');
                    
                    const fd = new FormData();
                    fd.append('id_obras', contenedor.dataset.idObra);
                    fd.append('puntuacion', puntuacionSeleccionada);
                    
                    fetch('votar_obra.php', { method: 'POST', body: fd })
                    .then(() => {
                        location.reload(); 
                    });
                });
            });
        </script>
    </body>
</html>