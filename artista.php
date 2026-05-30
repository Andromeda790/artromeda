<?php
    session_start();
    include("conexion.php");
    if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'artista') {
        header("Location: login.php");
        exit();
    }
    $id_usuarios_sesion = $_SESSION['usuario_id'];
    $query_artista = "SELECT nombres, apellidos, email, telefono, foto_perfil, biografia, especializacion 
                      FROM usuarios 
                      WHERE id = '$id_usuarios_sesion'";
    $res_artista = mysqli_query($conexion, $query_artista);
    $datos_artista = mysqli_fetch_assoc($res_artista);
    if (!$datos_artista) {
        echo "Error: Perfil no encontrado.";
        exit();
    }
    $query_obras = "SELECT * FROM obras WHERE id_usuarios = '$id_usuarios_sesion' ORDER BY id DESC"; 
    $res_obras = mysqli_query($conexion, $query_obras);
    $query_ventas = "SELECT p.id as id_factura, p.fecha_pedido, p.estado,
                            o.titulo, o.precio,
                            u.nombres as cliente_nombre, u.apellidos as cliente_apellido
                     FROM detalles_pedidos dp
                     JOIN pedidos p ON dp.id_pedidos = p.id
                     JOIN obras o ON dp.id_obras = o.id
                     JOIN usuarios u ON p.id_usuarios = u.id
                     WHERE o.id_usuarios = '$id_usuarios_sesion'
                     ORDER BY p.fecha_pedido DESC";
    $res_ventas = mysqli_query($conexion, $query_ventas);
    $cantidad_carrito = isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0;
    $cantidad_favoritos = 0;
    if (isset($_SESSION['usuario_id'])) {
        $id_user_count = $_SESSION['usuario_id'];
        $res_favs = mysqli_query($conexion, "SELECT COUNT(*) as total FROM favoritos WHERE id_usuarios = '$id_user_count'");
        $fila_favs = mysqli_fetch_assoc($res_favs);
        $cantidad_favoritos = $fila_favs['total'] ?? 0;
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Artista - GA ARTRÓMEDA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --dorado: #deb887;
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

        .container {
            padding: 2rem 5%;
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 2rem;
        }

        .card {
            background: rgba(247, 245, 245, 0.4);
            backdrop-filter: blur(7px);
            border: 2px solid #6b3e0a;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 20px 40px rgba(182, 209, 139, 0.94);
        }

        h2 {
            color: #6b3e0a;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            border-bottom: 1px solid rgba(248, 17, 17, 0.2);
            padding-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .galeria-grid {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .obra-item {
            display: flex;
            flex-direction: row;
            align-items: center; 
            gap: 20px;         
            padding: 15px 10px;
            border-bottom: 1px solid rgb(236, 137, 8);
            width: 100%;
        }

        .obra-img {
            width: 90px;            
            height: 90px;
            object-fit: cover;
            border-radius: 5px;
            flex-shrink: 0;
        }

        .obra-info {
            display: flex;
            flex-direction: row;    
            align-items: center;   
            justify-content: space-between; 
            flex-grow: 1;        
            gap: 20px;        
        }

        .obra-titulo {
            font-size: 1.1rem;
            color: #6b3e0a;
            margin: 0;
            word-break: break-word;      
        }

        .obra-precio {
            font-size: 1rem;
            color: #6b3e0a;
            margin: 0;
            font-weight: bold;
            white-space: nowrap;
        }

        .estado-contenedor {
            margin: 0;
            display: flex;
            flex-shrink: 0;
        }

        .estado-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: bold;
            white-space: nowrap;
        }

        .estado-pendiente {
            background: #ffd700;
            color: #000;
        }

        .estado-aprobada {
            background: #28a745;
            color: #fff;
        }

        .estado-rechazada {
            background: #dc3545;
            color: #fff;
        }

        .form-group { 
            color: #6b3e0a;
            margin-bottom: 1rem; 
        }

        select[name="categoria"] {
            background-color: transparent; 
            color: #242423d8;          
            border: 1px solid #6b3e0a;
            padding: 10px;
            border-radius: 5px;
            width: 100%;
            cursor: pointer;
        }

        select[name="categoria"] option {
            background-color: #070707; 
            color: #f8f6f4;      
        }

        .categoria, .descripción, input, textarea {
            width: 100%;
            padding: 10px; 
            background: rgba(255,255,255,0.05);
            border: 1px solid #6b3e0a; 
            color: #000; 
            border-radius: 5px;
        }

        input[type="file"] {
            color: #6b3e0a;
        }

        label { 
            display: block; 
            font-size: 0.8rem; 
            color: #915f1f; 
            margin-bottom: 5px; 
        }

        .btn-post {
            background: transparent;
            color: #6b3e0a;
            border: 2px solid #6b3e0a;
            width: 100%; 
            padding: 12px; 
            font-weight: bold; 
            cursor: pointer; 
            border-radius: 5px; 
            transition: 0.3s;
        }

        .btn-post:hover { 
            background: #cbd885e3;
            transform: translateY(-2px); 
        }

        .menu-toggle {
            display: none;
            cursor: pointer;
            font-size: 2rem;
            color: white;
        }

        @media (max-width: 991px) {
            .container {
                grid-template-columns: 1fr;
            }
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

            .obra-item {
                flex-direction: column;
                text-align: center;
                gap: 15px;
                padding: 20px 10px;
            }

            .obra-info {
                flex-direction: column;
                gap: 10px;
                width: 100%;
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

        <div class="container">
            <aside>
                <section class="card">
                    <h2><span><i class="bi bi-person"></i> Perfil</span><a href="actualizar_perfil_artista.php" class="btn-post" style="width:auto; padding:5px 10px; font-size:0.7rem; text-decoration:none;">EDITAR</a></h2>
                    <p style="color: #6b3e0a;"><strong><?php echo htmlspecialchars($datos_artista['nombres'] . " " . $datos_artista['apellidos']); ?></strong></p>
                    <p style="color: #6b3e0a; font-size:0.9rem;"><?php echo htmlspecialchars($datos_artista['especializacion'] ?? 'Artista'); ?></p>
                    <p style="font-size: 0.8rem; color: #6b3e0a; margin-top:10px; opacity:0.7;"><?php echo nl2br(htmlspecialchars($datos_artista['biografia'] ?? '')); ?></p>
                </section>

                <section class="card">
                    <h2><i class="bi bi-plus-circle"></i> Subir Nueva Obra</h2>
                    <form action="subir_obra.php" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Título de la Obra</label>
                            <input type="text" name="titulo" placeholder="Ej: Renacer" required>
                        </div>
                        <div class="form-group">
                            <label>Categoría</label>
                            <select name="categoria" required class="categoria">
                                <option value="" disabled selected>Selecciona una categoría</option>
                                <option value="Escultura">Escultura</option>
                                <option value="Pintura">Pintura</option>
                                <option value="Fotografía">Fotografía</option>
                                <option value="Arte Digital">Arte Digital</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Precio (COP)</label>
                            <input type="number" name="precio" placeholder="00.000" required>
                        </div>
                        <div class="form-group">
                            <label>Descripción de la Obra</label>
                            <textarea name="descripcion" rows="3" placeholder="Cuéntanos sobre tu creación..." required class="descripción"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Imagen de la Obra</label>
                            <input type="file" name="imagen" accept="image/*" required>
                        </div>
                        <button type="submit" class="btn-post">Publicar ahora</button>
                    </form>
                </section>
            </aside>

            <main>
                <section class="card">
                    <h2><i class="bi bi-grid-3x3-gap"></i> Mi Galería Personal</h2>
                    <div class="galeria-grid">
                        <?php if(mysqli_num_rows($res_obras) > 0): ?>
                            <?php while($obra = mysqli_fetch_assoc($res_obras)): ?>
                                <div class="obra-card">
                                    <div class="obra-item">
                                        <img src="img_obras/<?php echo htmlspecialchars($obra['imagen']); ?>" class="obra-img">
                                        <div class="obra-info">
                                            <h4 class="obra-titulo"><?php echo htmlspecialchars($obra['titulo']); ?></h4>
                                            <p class="obra-precio">
                                                $<?php echo number_format($obra['precio'], 0, ',', '.'); ?>
                                            </p>
                                            <div class="estado-contenedor">
                                                <?php if(trim(strtolower($obra['estado'])) == 'aprobada'): ?>
                                                    <span class="estado-badge estado-aprobada">
                                                        <i class="bi bi-check-circle"></i> APROBADA
                                                    </span>
                                                <?php elseif(trim(strtolower($obra['estado'])) == 'pendiente'): ?>
                                                    <span class="estado-badge estado-pendiente">
                                                        <i class="bi bi-clock"></i> PENDIENTE
                                                    </span>
                                                <?php else: ?>
                                                    <span class="estado-badge estado-rechazada">
                                                        <i class="bi bi-x-circle"></i> RECHAZADA
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div style="text-align: center; padding: 50px; opacity: 0.5; color: #6b3e0a;">
                                <i class="bi bi-image" style="font-size: 3rem;"></i>
                                <p>Aún no tienes obras en tu portafolio.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="card">
                    <h2><i class="bi bi-graph-up-arrow"></i> Mis Ventas</h2>
                    <div style="overflow-x: auto;">
                        <table style="width:100%; border-collapse: collapse; font-size: 0.85rem;">
                            <thead>
                                <tr style="color:var(--dorado); border-bottom: 2px solid var(--dorado);">
                                    <th style="padding:10px; text-align:left;">Fecha</th>
                                    <th style="padding:10px; text-align:left;">Obra</th>
                                    <th style="padding:10px; text-align:left;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(mysqli_num_rows($res_ventas) > 0): ?>
                                    <?php while($v = mysqli_fetch_assoc($res_ventas)): ?>
                                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.1); color: #050505;">
                                            <td style="padding:10px; white-space: nowrap;"><?php echo date('d/m/y', strtotime($v['fecha_pedido'])); ?></td>
                                            <td style="padding:10px;"><?php echo htmlspecialchars($v['titulo']); ?></td>
                                            <td style="padding:10px; color: #070707; font-weight: bold;">$<?php echo number_format($v['precio'], 0, ',', '.'); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" style="padding:20px; text-align:center; opacity:0.5; color: #6b3e0a;">Sin ventas todavía.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
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