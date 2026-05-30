<?php
    session_start();
    include("conexion.php");

    $id_logueado = (int)($_SESSION['usuario_id'] ?? 0);
    $id_perfil_a_ver = (int)($_GET['id'] ?? $id_logueado);

    if (!$id_perfil_a_ver) {
        header("Location: login.php");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['nueva_foto'])) {
        $archivo = $_FILES['nueva_foto'];
        if (!empty($archivo['name'])) {
            $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            $permitidos = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($ext, $permitidos)) {
                $nuevo_nombre = "perfil_" . $id_logueado . "_" . time() . "." . $ext;
                if (move_uploaded_file($archivo['tmp_name'], "img_perfiles/" . $nuevo_nombre)) {
                    $stmt_update = mysqli_prepare($conexion, "UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
                    mysqli_stmt_bind_param($stmt_update, "si", $nuevo_nombre, $id_logueado);
                    mysqli_stmt_execute($stmt_update);
                    mysqli_stmt_close($stmt_update);
                    
                    header("Location: perfil.php?id=" . $id_perfil_a_ver);
                    exit();
                }
            }
        }
    }

    $stmt_user = mysqli_prepare($conexion, "SELECT * FROM usuarios WHERE id = ?");
    mysqli_stmt_bind_param($stmt_user, "i", $id_perfil_a_ver);
    mysqli_stmt_execute($stmt_user);
    $res_user = mysqli_stmt_get_result($stmt_user);
    $user = mysqli_fetch_assoc($res_user);
    mysqli_stmt_close($stmt_user);

    if (!$user) {
        echo "Usuario no encontrado.";
        exit();
    }

    $query_obras = "SELECT COUNT(DISTINCT dp.id_obras) as total FROM detalles_pedidos dp JOIN pedidos p ON dp.id_pedidos = p.id WHERE p.id_usuarios = ?";
    $stmt_obras = mysqli_prepare($conexion, $query_obras);
    mysqli_stmt_bind_param($stmt_obras, "i", $id_perfil_a_ver);
    mysqli_stmt_execute($stmt_obras);
    $cant_obras = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_obras))['total'];
    mysqli_stmt_close($stmt_obras);

    $stmt_segdes = mysqli_prepare($conexion, "SELECT COUNT(*) as total FROM seguidores WHERE id_seguido = ?");
    mysqli_stmt_bind_param($stmt_segdes, "i", $id_perfil_a_ver);
    mysqli_stmt_execute($stmt_segdes);
    $cant_seguidores = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_segdes))['total'];
    mysqli_stmt_close($stmt_segdes);

    $stmt_segdos = mysqli_prepare($conexion, "SELECT COUNT(*) as total FROM seguidores WHERE id_seguidor = ?");
    mysqli_stmt_bind_param($stmt_segdos, "i", $id_perfil_a_ver);
    mysqli_stmt_execute($stmt_segdos);
    $cant_seguidos = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_segdos))['total'];
    mysqli_stmt_close($stmt_segdos);

    $ya_lo_sigo = false;
    if ($id_logueado && $id_logueado != $id_perfil_a_ver) {
        $stmt_follow = mysqli_prepare($conexion, "SELECT id FROM seguidores WHERE id_seguidor = ? AND id_seguido = ?");
        mysqli_stmt_bind_param($stmt_follow, "ii", $id_logueado, $id_perfil_a_ver);
        mysqli_stmt_execute($stmt_follow);
        $ya_lo_sigo = mysqli_num_rows(mysqli_stmt_get_result($stmt_follow)) > 0;
        mysqli_stmt_close($stmt_follow);
    }

    $cantidad_carrito = (isset($_SESSION['carrito']) && is_array($_SESSION['carrito'])) ? count($_SESSION['carrito']) : 0;
    $cantidad_favoritos = 0;

    if ($id_logueado) {
        $stmt_favs_count = mysqli_prepare($conexion, "SELECT COUNT(*) as total FROM favoritos WHERE id_usuarios = ?");
        mysqli_stmt_bind_param($stmt_favs_count, "i", $id_logueado);
        mysqli_stmt_execute($stmt_favs_count);
        $cantidad_favoritos = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_favs_count))['total'];
        mysqli_stmt_close($stmt_favs_count);
    }

    $enlace_perfil = (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'administrador') ? "administrador.php" : "perfil.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['nombres']); ?> - Artrómeda</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --dorado: #deb887;
            --oscuro: #050505;
            --blanco: #ffffff;
            --rojo: #ff4d4d;
            --gris-suave: rgba(255, 255, 255, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
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
            padding: 0.1rem 5%;
            background: transparent;
            box-shadow: 0 10px 25px rgba(5, 5, 5, 0.5);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 2000;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--dorado);
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
            width: 0; height: 2px;
            bottom: -2px; left: 0; 
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
            margin: 0 5px;
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
        }

        .profile-header {
            padding: 60px 10% 30px;
            display: flex;
            align-items: center;
            gap: 50px;
            justify-content: center;
        }

        .avatar-container {
            width: 180px;
            height: 180px;
            position: relative;
            flex-shrink: 0;
        }

        .avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 10%;
            border: 3px solid var(--dorado);
            box-shadow: 0 0 20px rgba(219, 173, 113, 0.3);
        }

        .btn-photo-delete, .btn-photo-edit {
            position: absolute;
            width: 37px;
            height: 37px;
            border-radius: 40%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            opacity: 0;
            visibility: hidden;
            background: rgba(5, 5, 5, 0.6);
        }

        .btn-photo-delete {
            top: 5px; right: 5px;
            color: #fc0303;
            border: 1px solid #f70303;
        }

        .btn-photo-edit {
            bottom: 5px; right: 5px;
            color: #04f87e;
            border: 1px solid #04f87e;
        }

        .avatar-container:hover .btn-photo-delete,
        .avatar-container:hover .btn-photo-edit {
            opacity: 1;
            visibility: visible;
        }

        .btn-photo-delete:hover { 
            background: rgba(221, 25, 25, 0.4); 
            transform: scale(1.1); 
        }

        .btn-photo-edit:hover { 
            background: rgba(28, 201, 115, 0.4); 
            transform: scale(1.1); 
        }

        .user-info-block h1 {
            color: #fffefd;
            font-size: 2.2rem;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 2px 4px 10px rgba(209, 104, 34, 0.4);
        }

        .stats-row {
            display: flex;
            gap: 50px;
        }

        .stat-item {
            display: flex;
            flex-direction: column;
        }

        .stat-item strong {
            font-size: 1.1rem;
            color: var(--dorado);
        }

        .stat-item span {
            font-size: 0.9rem;
            color: #6b3e0a; 
            opacity: 0.8;   
            text-transform: uppercase;
        }

        .tabs-wrapper {
            border-top: 1px solid #6b3e0a;
            background: rgba(148, 101, 62, 0.3);
            margin-top: 20px;
        }

        .tabs-container {
            display: flex;
            justify-content: center;
            position: relative;
            max-width: 900px;
            margin: 0 auto;
        }

        .tab-btn {
            padding: 20px 30px;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.8rem;
            color: #080808;
            transition: 0.3s;
            text-transform: uppercase;
        }

        .tab-btn.active {
            color: #6b3e0a;
        }

        .tab-indicator {
            position: absolute;
            top: -1px;
            height: 4px;
            background: #6b3e0a;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-follow {
            background: transparent;
            color: var(--dorado);
            border: 2px solid var(--dorado);
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            font-size: 0.75rem;
        }

        .btn-follow:hover {
            background: var(--dorado);
            color: black;
            transform: translateY(-2px);
        }

        .btn-follow.active {
            background: rgba(222, 184, 135, 0.2);
            color: white;
            border-color: white;
        }

        .content-section {
            display: none;
            padding: 40px 10%;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 30px;
        }

        .content-section.active {
            display: grid;
        }

        .card-obra {
            background: rgba(20, 20, 20, 0.75);
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.1);
            transition: 0.4s;
            backdrop-filter: blur(10px);
        }

        .card-obra:hover {
            transform: translateY(-10px);
            border-color: var(--dorado);
            box-shadow: 0 10px 20px rgba(0,0,0,0.5);
        }

        .img-box {
            width: 100%;
            aspect-ratio: 1/1;
            overflow: hidden;
            background: #000;
        }

        .img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .obra-info {
            padding: 20px;
            text-align: center;
        }

        .obra-info h3 {
            font-size: 0.95rem;
            margin-bottom: 5px;
            color: var(--dorado);
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

            .profile-header { 
                flex-direction: column; 
                text-align: center; 
                gap: 20px; 
            }

            .stats-row { 
                justify-content: center; 
                gap: 20px; 
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
            <a href="<?php echo $enlace_perfil; ?>" class="nav-link">Mi Perfil</a>
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

    <section class="profile-header">
        <div class="avatar-container">
            <img src="img_perfiles/<?php echo !empty($user['foto_perfil']) ? $user['foto_perfil'] : 'default.jpg'; ?>" class="avatar-img" alt="Foto de perfil">
            <?php if ($id_logueado === $id_perfil_a_ver): ?>
                <form id="form-foto" method="POST" enctype="multipart/form-data">
                    <input type="file" name="nueva_foto" id="input-foto" style="display:none;" onchange="document.getElementById('form-foto').submit();">
                    <label for="input-foto" class="btn-photo-edit"><i class="bi bi-camera"></i></label>
                </form>
                <a href="eliminar_foto.php" class="btn-photo-delete" title="Eliminar foto">
                    <i class="bi bi-trash3"></i>
                </a>
            <?php endif; ?>
        </div>
        
        <div class="user-info-block">
            <h1><?php echo htmlspecialchars($user['nombres'] . " " . $user['apellidos']); ?></h1>
            <div class="stats-row">
                <div class="stat-item">
                    <strong><?php echo $cant_obras; ?></strong>
                    <span>Obras Adquiridas</span>
                </div>
                <div class="stat-item">
                    <strong><?php echo $cant_seguidores; ?></strong>
                    <span>Seguidores</span>
                </div>
                <div class="stat-item">
                    <strong><?php echo $cant_seguidos; ?></strong>
                    <span>Seguidos</span>
                </div>
            </div>
            
            <div class="actions-block" style="margin-top: 15px;">
                <?php if ($id_logueado && $id_logueado !== $id_perfil_a_ver): ?>
                    <form action="procesar_seguimiento.php" method="POST">
                        <input type="hidden" name="id_seguido" value="<?php echo $id_perfil_a_ver; ?>">
                        <?php if ($ya_lo_sigo): ?>
                            <button type="submit" name="accion" value="unfollow" class="btn-follow active">
                                <i class="bi bi-person-check"></i> Siguiendo
                            </button>
                        <?php else: ?>
                            <button type="submit" name="accion" value="follow" class="btn-follow">
                                <i class="bi bi-person-plus"></i> Seguir
                            </button>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <div class="tabs-wrapper">
        <div class="tabs-container">
            <div class="tab-indicator" id="indicator"></div>
            <div class="tab-btn active" data-target="coleccion">COLECCIÓN</div>
            <div class="tab-btn" data-target="favoritos">MIS FAVORITOS</div>
            <div class="tab-btn" data-target="carrito-tab">MI CARRITO</div>
        </div>
    </div>

    <main id="coleccion" class="content-section active">
        <?php
        $stmt_col = mysqli_prepare($conexion, "SELECT o.* FROM detalles_pedidos dp JOIN pedidos p ON dp.id_pedidos = p.id JOIN obras o ON dp.id_obras = o.id WHERE p.id_usuarios = ?");
        mysqli_stmt_bind_param($stmt_col, "i", $id_perfil_a_ver);
        mysqli_stmt_execute($stmt_col);
        $res_col = mysqli_stmt_get_result($stmt_col);

        if(mysqli_num_rows($res_col) > 0):
            while($obra = mysqli_fetch_assoc($res_col)): ?>
                <div class="card-obra">
                    <div class="img-box"><img src="img_obras/<?php echo $obra['imagen']; ?>" alt="Obra"></div>
                    <div class="obra-info"><h3><?php echo htmlspecialchars($obra['titulo']); ?></h3></div>
                </div>
            <?php endwhile; 
        else: 
            echo "<p style='grid-column:1/-1; text-align:center; opacity:0.5;'>No se han adquirido obras todavía.</p>"; 
        endif; 
        mysqli_stmt_close($stmt_col);
        ?>
    </main>

    <main id="favoritos" class="content-section">
        <?php
        $stmt_fav = mysqli_prepare($conexion, "SELECT o.* FROM favoritos f JOIN obras o ON f.id_obras = o.id WHERE f.id_usuarios = ?");
        mysqli_stmt_bind_param($stmt_fav, "i", $id_perfil_a_ver);
        mysqli_stmt_execute($stmt_fav);
        $res_fav = mysqli_stmt_get_result($stmt_fav);

        if(mysqli_num_rows($res_fav) > 0):
            while($obra = mysqli_fetch_assoc($res_fav)): ?>
                <div class="card-obra">
                    <div class="img-box"><img src="img_obras/<?php echo $obra['imagen']; ?>" alt="Obra"></div>
                    <div class="obra-info"><h3><?php echo htmlspecialchars($obra['titulo']); ?></h3></div>
                </div>
            <?php endwhile;
        else: 
            echo "<p style='grid-column:1/-1; text-align:center; opacity:0.5;'>La lista de favoritos está vacía.</p>"; 
        endif; 
        mysqli_stmt_close($stmt_fav);
        ?>
    </main>

    <main id="carrito-tab" class="content-section">
        <?php
        if(!empty($_SESSION['carrito']) && is_array($_SESSION['carrito'])):
            $ids_filtrados = array_map('intval', array_keys($_SESSION['carrito']));
            $ids_carrito = implode(',', $ids_filtrados);
            
            if(!empty($ids_carrito)):
                $res_cart = mysqli_query($conexion, "SELECT * FROM obras WHERE id IN ($ids_carrito)");
                while($obra = mysqli_fetch_assoc($res_cart)): ?>
                    <div class="card-obra">
                        <div class="img-box"><img src="img_obras/<?php echo $obra['imagen']; ?>" alt="Obra"></div>
                        <div class="obra-info">
                            <h3><?php echo htmlspecialchars($obra['titulo']); ?></h3>
                            <p style="font-size:0.7rem; color:var(--dorado);">LISTO PARA COMPRAR</p>
                        </div>
                    </div>
                <?php endwhile;
            else:
                echo "<p style='grid-column:1/-1; text-align:center; opacity:0.5;'>El carrito está vacío.</p>";
            endif;
        else: 
            echo "<p style='grid-column:1/-1; text-align:center; opacity:0.5;'>El carrito está vacío.</p>"; 
        endif; ?>
    </main>

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

        const tabs = document.querySelectorAll('.tab-btn');
        const indicator = document.getElementById('indicator');
        const sections = document.querySelectorAll('.content-section');

        function moveIndicator(el) {
            if(el) {
                indicator.style.width = el.offsetWidth + "px";
                indicator.style.left = el.offsetLeft + "px";
            }
        }
        
        setTimeout(() => moveIndicator(document.querySelector('.tab-btn.active')), 100);

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                moveIndicator(tab);
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                
                sections.forEach(s => {
                    s.classList.remove('active');
                    if(s.id === tab.dataset.target) s.classList.add('active');
                });
            });
        });

        window.addEventListener('resize', () => moveIndicator(document.querySelector('.tab-btn.active')));
    </script>
</body>
</html>