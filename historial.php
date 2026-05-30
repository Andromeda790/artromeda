<?php
    session_start();
    include("conexion.php");
    
    $id_usuarios = $_SESSION['usuario_id'] ?? null;
    if (!$id_usuarios) {
        header("Location: login.php");
        exit();
    }

    $id_usuarios_clean = intval($id_usuarios);

    $query = "SELECT 
                p.id as id_pedidos, 
                p.fecha_pedido, 
                p.estado, 
                p.precio_final, 
                o.titulo, 
                o.imagen 
              FROM pedidos p
              JOIN detalles_pedidos dp ON p.id = dp.id_pedidos
              JOIN obras o ON dp.id_obras = o.id
              WHERE p.id_usuarios = $id_usuarios_clean
              ORDER BY p.fecha_pedido DESC";
              
    $resultado = mysqli_query($conexion, $query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Historial - Artrómeda</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { 
            --dorado: #deb887; 
            --marron-oscuro: #6b3e0a;
        }

        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        }

        body { 
            background: url("fondo.png") no-repeat center center fixed; 
            background-size: cover; 
            color: white; 
            padding: 2rem 1rem; 
        }

        .history-container { 
            max-width: 900px; 
            margin: auto; 
            background: rgba(247, 245, 245, 0.4);
            backdrop-filter: blur(7px);
            border: 2px solid #6b3e0acb;
            border-radius: 20px; 
            padding: 2.5rem 2rem; 
        }

        .titulo {
            text-align: center; 
            margin-bottom: 2rem; 
            color: #683310; 
            letter-spacing: 1px;
        }

        .pedido-card {
            display: flex;
            align-items: center;
            gap: 20px;
            background: transparent;
            margin-bottom: 1.2rem;
            padding: 1.2rem;
            border-radius: 12px;
            border-left: 4px solid #6b3e0a;
            transition: transform 0.2s ease;
        }

        .pedido-card:hover {
            transform: scale(1.01);
            background: rgba(177, 134, 94, 0.29);
        }

        .pedido-img { 
            width: 80px; 
            height: 80px; 
            object-fit: cover; 
            border-radius: 8px; 
            border: 1px solid rgba(222, 184, 135, 0.3);
        }

        .info-principal {
            flex-grow: 1;
        }

        .info-principal h3 {
            font-size: 1.2rem;
            margin-bottom: 5px;
        }

        .fecha { 
            font-size: 0.85rem; 
            opacity: 0.7;
            color: #000000; 
        }

        .bloque-acciones {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 10px;
            min-width: 140px;
        }

        .precio {
            font-weight: bold;
            font-size: 1.1rem;
            color: #0a0a0a;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .pagado, .confirmado, .exitoso { 
            background: #28a745; 
            color: white; 
        }

        .enviado { 
            background: #007bff; 
            color: white; 
        }
        
        .pendiente {
            background: #ffc107;
            color: #000;
        }

        .btn-factura {
            color: #6b3e0a;
            font-size: 0.75rem;
            text-decoration: none;
            border: 1px solid #6b3e0a;
            padding: 6px 12px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            font-weight: 600;
        }

        .btn-factura:hover {
            background-color: var(--dorado);
            color: black;
            box-shadow: 0 4px 10px rgba(222, 184, 135, 0.3);
        }

        .link-volver {
            color: #683310;
            text-decoration: none;
            transition: color 0.3s;
            font-size: 0.95rem;
        }

        .link-volver:hover {
            color: #6b3e0a;
        }

        @media (max-width: 768px) {
            .pedido-card {
                flex-direction: column;
                text-align: center;
                align-items: center;
                padding: 1.5rem;
            }
            .bloque-acciones {
                align-items: center;
                width: 100%;
                margin-top: 10px;
                border-top: 1px solid rgba(255,255,255,0.1);
                padding-top: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="history-container">
        <h1 class="titulo">Mis Adquisiciones</h1>
        
        <?php if (mysqli_num_rows($resultado) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($resultado)): ?>
                <div class="pedido-card">
                    <img src="img_obras/<?php echo htmlspecialchars($row['imagen']); ?>" class="pedido-img" alt="Obra">
                    
                    <div class="info-principal">
                        <h3 style="color: #683310;"><?php echo htmlspecialchars($row['titulo']); ?></h3>
                        <p class="fecha">Pedido #<?php echo $row['id_pedidos']; ?> • <?php echo date('d/m/Y', strtotime($row['fecha_pedido'])); ?></p>
                    </div>
                    
                    <div class="bloque-acciones">
                        <p class="precio">$<?php echo number_format($row['precio_final'], 0, ',', '.'); ?> COP</p>
                        
                        <span class="status-badge <?php echo strtolower($row['estado']); ?>">
                            <?php echo htmlspecialchars($row['estado']); ?>
                        </span>
                        
                        <a href="factura.php?id=<?php echo intval($row['id_pedidos']); ?>" target="_blank" class="btn-factura">
                            <i class="bi bi-file-earmark-pdf"></i> Factura
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align: center; opacity: 0.7; padding: 2rem 0;">Aún no has realizado ninguna adquisición artística.</p>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 2.5rem;">
            <a href="perfil.php" class="link-volver">
                <i class="bi bi-arrow-left"></i> Volver al Perfil
            </a>
        </div>
    </div>
</body>
</html>