<?php
    session_start();
    require_once("verificar_admin.php");
    require_once("conexion.php");
    $nivel = verificarAdmin(3);
    function obtenerTotal($conexion, $query) {
        $total = 0;
        $res = mysqli_query($conexion, $query);
        if ($res) {
            $row = mysqli_fetch_assoc($res);
            $total = $row['total'] ?? 0;
        }
        return $total;
    }
    $total_obras = obtenerTotal($conexion, 
        "SELECT COUNT(id) as total FROM obras");
    $total_artistas = obtenerTotal($conexion, 
        "SELECT COUNT(id) as total FROM usuarios WHERE rol = 'artista'");
    $total_usuarios = obtenerTotal($conexion, 
        "SELECT COUNT(id) as total FROM usuarios WHERE rol = 'usuario'");
    $total_ingresos = obtenerTotal($conexion, 
        "SELECT SUM(precio_final) as total FROM pedidos WHERE estado = 'pagado'");
    $query_ingresos = "SELECT COALESCE(SUM(precio_final),0) as total, COUNT(id) as cantidad FROM pedidos";
    $res_ingresos = mysqli_query($conexion, $query_ingresos);
    $total_plataforma = 0;
    $ticket_promedio = 0;
    if ($res_ingresos) {
        $row = mysqli_fetch_assoc($res_ingresos);
        $total_plataforma = (float) $row['total'];
        $cantidad = (int) $row['cantidad'];
        $ticket_promedio = $cantidad > 0 ? $total_plataforma / $cantidad : 0;
    }
    $query_recientes = "SELECT p.precio_final, p.fecha_pedido, o.titulo, u.nombres, u.foto_perfil FROM pedidos p JOIN obras o ON p.id_obras = o.id
        JOIN usuarios u ON p.id_usuarios = u.id where p.estado = 'pagado' ORDER BY p.fecha_pedido DESC LIMIT 6";
    $ventas_recientes = mysqli_query($conexion, $query_recientes);
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


<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - Artrómeda</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>

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

        .dashboard-grid {
            display:grid;
            grid-template-columns:repeat(auto-fit, minmax(220px,1fr));
            gap: 20px;
            padding: 2rem 5%;
        }

        .stat-card {
            background: rgba(247, 245, 245, 0.4);
            backdrop-filter: blur(7px);
            border: 2px solid rgba(240, 141, 12, 0.4);
            padding: 25px;
            border-radius: 15px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: default;
        }

        .stat-card i {
            font-size: 1.8rem;
            color: #e4924f;
        }

        .stat-card p {
            font-size: 0.7rem;
            color: #6b3e0a;
            letter-spacing: 1px;
        }

        .stat-card h3 {
            margin-top:10px;
            font-size:1.4rem;
        }

        .stat-card:hover {
            transform: translateY(-10px) scale(1.02);
            background: rgba(247, 244, 244, 0.8); 
            border-color: var(--dorado); 
            box-shadow: 0 10px 30px rgba(222, 184, 135, 0.2); 
        }

        .stat-card:hover i {
            transform: scale(1.2);
            transition: transform 0.3s ease;
        }

        .admin-actions {
            padding:0 5% 2rem;
            display:flex;
            gap:15px;
            flex-wrap:wrap;
        }

        .btn-admin {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            border: 1px solid var(--borde);
            background: rgba(241, 159, 104, 0.86);      
            color: black;                   
            font-size: 0.85rem;
            transition: 0.3s;
        }

        .btn-admin:hover {
            background:var(--dorado);
            color:black;
        }

        .recent-container {
            background: rgba(247, 245, 245, 0.4);
            backdrop-filter: blur(7px);
            border: 2px solid rgba(240, 141, 12, 0.4);
            margin:0 5% 3rem;
            padding:25px;
            color: #6b3e0a;
            border-radius:15px;
        }

        table {
            width:100%;
            border-collapse:collapse;
        }

        th, td {
            padding:12px;
            font-size:0.85rem;
            border-bottom:1px solid rgba(243, 112, 5, 0.36);
        }

        th {
            color: #6b3e0a;
            text-align:left;
        }

        .mini-avatar {
            width:50px;
            height:50px;
            border-radius:10%;
            object-fit:cover;
            border:1px solid var(--dorado);
            margin-right:8px;
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
                z-index: 1000; 
            }

            .nav-list {
                position: fixed;
                top: 0;
                right: -100%;
                width: 50%;
                height: 100vh;
                background: rgba(7, 7, 7, 0.94); 
                backdrop-filter: blur(20px) saturate(150%); 
                -webkit-backdrop-filter: blur(20px) saturate(150%);
                border-left: 2px solid rgba(222, 184, 135, 0.81); 
                flex-direction: column; 
                justify-content: center;
                transition: right 0.5s cubic-bezier(0.4, 0, 0.2, 1); 
                box-shadow: -10px 0 30px rgba(245, 244, 244, 0.5);
            }
            .nav-list.active { 
                right: 0; 
            }

            .nav-icons { 
                margin-left: 0; 
                margin-top: 20px; 
            }

            .recent-container {
                overflow-x: auto;
                padding: 15px;
            }
            
            table {
                min-width: 500px;
            }

            .mini-avatar {
                width: 40px;
                height: 40px;
            }

            .dashboard-grid {
                grid-template-columns: 1fr; 
                padding: 1rem 3%;
            }
            
            .stat-card {
                padding: 20px;
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
        <div class="dashboard-grid">
        <div class="stat-card">
            <i class="bi bi-cash-stack"></i>
            <p>Ingresos Totales</p>
            <h3>$<?php echo number_format($total_plataforma,0,',','.'); ?></h3>
        </div>
        <div class="stat-card">
            <i class="bi bi-graph-up"></i>
            <p>Ticket Promedio</p>
            <h3>$<?php echo number_format($ticket_promedio,0,',','.'); ?></h3>
        </div>
        <div class="stat-card">
            <i class="bi bi-people"></i>
            <p>Clientes</p>
            <h3><?php echo $total_usuarios; ?></h3>
        </div>
        <div class="stat-card">
            <i class="bi bi-brush"></i>
            <p>Artistas</p>
            <h3><?php echo $total_artistas; ?></h3>
        </div>
        <div class="stat-card">
            <i class="bi bi-image"></i>
            <p>Obras</p>
            <h3><?php echo $total_obras; ?></h3>
        </div>
        </div>
        <div class="admin-actions">
        <a href="usuarios_lista.php" class="btn-admin">
            <i class="bi bi-person-gear"></i> Gestión Usuarios
        </a>
        <a href="moderar_obras.php" class="btn-admin">
            <i class="bi bi-grid"></i> Moderar Obras
        </a>
        <a href="reportes.php" class="btn-admin">
            <i class="bi bi-file-earmark-bar-graph"></i> Reportes
        </a>
        </div>
        <?php if ($ventas_recientes): ?>
        <div class="recent-container">
            <h3>Transacciones Recientes</h3>
            <table>
                <thead>
                    <tr>
                        <th>Comprador</th>
                        <th>Obra</th>
                        <th>Monto</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($v = mysqli_fetch_assoc($ventas_recientes)): 
                    $foto = !empty($v['foto_perfil']) 
                        ? "img_perfiles/" . basename($v['foto_perfil'])
                        : "img_perfiles/default.png";
                ?>
                    <tr>
                        <td style="color: #070707;">
                            <img src="<?php echo $foto; ?>" class="mini-avatar">
                            <?php echo htmlspecialchars($v['nombres']); ?>
                        </td>
                        <td style="color: #070707;"><?php echo htmlspecialchars($v['titulo'] ?? 'Sin título'); ?></td>
                        <td style="color: #4ade80; font-weight:bold;">
                            $<?php echo number_format($v['precio_final'],0,',','.'); ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        <?php mysqli_close($conexion); ?>
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