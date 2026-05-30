<?php
    session_start();
    include("conexion.php");
    $query_recientes = "SELECT o.*, u.nombres 
                        FROM obras o 
                        JOIN usuarios u ON o.id_usuarios = u.id
                        WHERE o.estado = 'aprobada'
                        ORDER BY o.fecha_subida DESC 
                        LIMIT 3";
    $res_recientes = mysqli_query($conexion, $query_recientes);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenidos a Artrómeda - Galería Digital</title>
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
            background: #0a0a0a; 
            color: white; 
            overflow-x: hidden; 
        }

        .hero {
            min-height: 100vh; 
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 40px 20px; 
            overflow: hidden;
            background: #000;
        }

        .hero-carousel {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .hero-carousel::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(34, 33, 33, 0.55), rgba(243, 202, 149, 0.41));
            z-index: 1;
        }

        .carousel-item {
            position: absolute;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center; 
            opacity: 0;
            transform: translateX(-100%);
            animation: slideRight 20s infinite;
        }

        .carousel-item:nth-child(1) { animation-delay: 0s; }
        .carousel-item:nth-child(2) { animation-delay: 5s; }
        .carousel-item:nth-child(3) { animation-delay: 10s; }
        .carousel-item:nth-child(4) { animation-delay: 15s; }

        @keyframes slideRight {
            0% { opacity: 0; transform: translateX(-100%); }
            5% { opacity: 1; transform: translateX(0%); }
            25% { opacity: 1; transform: translateX(0%); }
            30% { opacity: 0; transform: translateX(100%); }
            100% { opacity: 0; transform: translateX(100%); }
        }

        .hero-logo, .hero h1, .hero p, .hero-buttons {
            position: relative;
            z-index: 2;
        }

        .hero-logo { 
            height: auto;
            max-height: 300px;
            width: 80%;
            max-width: 300px;
            margin-top: 20px;
            margin-bottom: 20px;
            filter: drop-shadow(0 0 15px rgb(73, 51, 4));
            transition: transform 0.3s ease;
        }

        .hero-logo:hover {
            transform: scale(1.05);
        }

        .hero p { 
            color: #f8f8f7;
            font-size: 1.1rem; 
            max-width: 600px;
            width: 90%;  
            margin: 15px auto; 
            opacity: 0.9; 
            line-height: 1.5;
            text-shadow: 2px 4px 10px rgb(8, 8, 8);
        }

        .hero-buttons {
            display: flex;
            flex-wrap: wrap; 
            align-items: center;
            justify-content: center;
            gap: 15px;
            width: 100%;
            margin-top: 10px;
        }

        .btn-explorar, .btn-unirse {
            display: inline-block;
            padding: 10px 20px;          
            color: #000;
            background: rgba(247, 245, 245, 0.4);
            backdrop-filter: blur(7px);
            border: 2px solid rgb(87, 56, 15);       
            text-decoration: none;      
            font-weight: bold;
            border-radius: 7px;        
            text-transform: uppercase;
            font-size: 0.85rem;
            transition: 0.3s ease;
            text-align: center;
        }

        .btn-explorar:hover, .btn-unirse:hover {
            border-color: #422207;
            color: #222;
            background: #dacda6e3;
            transform: translateY(-2px);
        }

        .destacados { 
            padding: 4rem 5%; 
            background: #0f0f0f; 
            text-align: center;
        }

        .destacados h2 { 
            margin-bottom: 2.5rem; 
            font-size: 1.8rem; 
            border-bottom: 2px solid var(--dorado); 
            display: inline-block; 
            padding-bottom: 10px; 
        }
        
        .grid-destacados {
            padding: 12px 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 2rem;
        }

        .card-destacada {
            position: relative;
            height: 380px; 
            border-radius: 12px; 
            overflow: hidden;
            border: 1px solid rgba(222, 184, 135, 0.3); 
            background: #111; 
        }

        .card-destacada img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover;
            transition: transform 0.6s cubic-bezier(0.25, 1, 0.5, 1); 
        }

        .card-destacada:hover img { 
            transform: scale(1.05); 
        }

        .overlay {
            position: absolute;
            bottom: 0; 
            width: 100%;
            padding: 1.5rem;
            background: linear-gradient(transparent, rgba(0,0,0,0.95));
            text-align: left;
        }

        .overlay h3 {
            color: white; 
            margin-bottom: 5px;
            font-size: 1.2rem;
        }

        .categoria-tag {
            color: var(--dorado);
            font-size: 0.7rem; 
            letter-spacing: 2px; 
            text-transform: uppercase; 
            margin-bottom: 5px; 
            font-weight: bold;
        }

        .artista-nombre {
            font-size: 0.85rem; 
            opacity: 0.8;
        }

        footer { 
            padding: 2rem; 
            text-align: center; 
            background: #050505; 
            border-top: 1px solid #222; 
        }

        footer p {
            opacity: 0.5;
            font-size: 0.9rem;
        }

        @media (max-height: 500px) {
            .hero {
                height: auto; 
                padding: 30px 20px;
            }
            .hero-logo {
                max-height: 200px;
                max-width: 200px;
                margin-top: 10px;
                margin-bottom: 10px;
            }
            .hero p {
                font-size: 0.95rem;
                margin: 10px auto;
            }
        }
    </style>
</head>
<body>
    <section class="hero">
        <div class="hero-carousel">
            <div class="carousel-item" style="background-image: url('carrusel/fondo1.jpg');"></div>
            <div class="carousel-item" style="background-image: url('carrusel/fondo2.jpg');"></div>
            <div class="carousel-item" style="background-image: url('carrusel/fondo3.jpg');"></div>
            <div class="carousel-item" style="background-image: url('carrusel/fondo4.jpg');"></div>
        </div>
        <img src="Logo.png" alt="Logo" class="hero-logo">
        <p>Espacio dedicado al arte contemporáneo más exclusivo.<br>Ven y vive la belleza en cada rincón.</p>
        <div class="hero-buttons">
            <a href="obras.php" class="btn-explorar">EXPLORAR GALERÍA</a>
            
            <?php if(!isset($_SESSION['usuario_id'])): ?>
                <a href="registro.php" class="btn-unirse">UNIRSE A ARTRÓMEDA</a>
            <?php endif; ?>
        </div>
    </section>

    <section class="destacados">
        <h2>Adquisiciones Recientes</h2>
        <div class="grid-destacados">
            <?php while($destacada = mysqli_fetch_assoc($res_recientes)): ?>
                <div class="card-destacada">
                    <img src="img_obras/<?php echo $destacada['imagen']; ?>" alt="Obra">
                    <div class="overlay">
                        <h3><?php echo $destacada['titulo']; ?></h3>
                        <p class="categoria-tag"><?php echo $destacada['categoria']; ?></p>
                        <p class="artista-nombre">Por: <?php echo $destacada['nombres']; ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <footer>
        <p>&copy; 2026 GA ARTRÓMEDA. Todos los derechos reservados.</p>
    </footer>
</body>
</html>