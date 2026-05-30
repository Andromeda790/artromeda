<?php
    session_start();
    
    if (!isset($_GET['id_pedidos'])) {
        header("Location: index.php");
        exit();
    }
    
    $id_pedido = intval($_GET['id_pedidos']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Compra Exitosa! - Artrómeda</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --marron-texto: #6b3e0a;
            --dorado-borde: rgba(240, 141, 12, 0.4);
            --borde-btn: rgba(240, 141, 12, 0.3);
            --hover-bg: rgba(218, 205, 166, 0.25);
        }

        body {
            background: url("fondo.png") no-repeat center center fixed;
            background-size: cover;
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .success-card {
            background: rgba(247, 245, 245, 0.4);
            backdrop-filter: blur(7px);
            border: 2px solid #6b3e0a;
            border-radius: 30px;
            padding: 50px;
            text-align: center;
            max-width: 500px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.6);
            animation: fadeIn 0.8s ease-out;
        }

        .check-icon {
            font-size: 5rem;
            color: #630707;
            margin-bottom: 20px;
            display: inline-block;
            animation: scaleUp 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        h1 {
            color: var(--marron-texto);
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 10px;
            font-size: 2rem;
        }

        p {
            color: var(--marron-texto);
            font-size: 1.1rem;
            margin-bottom: 35px;
        }

        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .btn {
            padding: 15px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            text-transform: uppercase;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
            box-sizing: border-box;
            background: transparent;
            color: var(--marron-texto);
            border: 2px solid #6b3e0a;
        }

        .btn:hover {
            border-color: var(--marron-texto);
            background-color: var(--hover-bg);
            transform: translateY(-2px);
        }

        @keyframes fadeIn {
            from { 
                opacity: 0; 
                transform: translateY(20px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }

        @keyframes scaleUp {
            from { 
                transform: scale(0); 
            }
            to { 
                transform: scale(1); 
            }
        }

        @media (max-width: 576px) {
            .success-card {
                padding: 30px 20px;
                margin: 20px;
                border-radius: 20px;
            }
            h1 {
                font-size: 1.6rem;
            }
        }
    </style>
</head>
<body>
    <div class="success-card">
        <div class="check-icon">
            <i class="bi bi-patch-check-fill"></i>
        </div>
        <h1>¡Pago Confirmado!</h1>
        <p>Gracias por tu adquisición. Tu obra digital ya está disponible en tu colección personal.</p>
        
        <div class="btn-group">
            <a href="factura.php?id=<?php echo $id_pedido; ?>" class="btn">
                <i class="bi bi-file-earmark-pdf"></i> Descargar Factura
            </a>
            <a href="historial.php" class="btn">
                <i class="bi bi-clock-history"></i> Ver mis pedidos
            </a>
            <a href="index.php" class="btn">
                <i class="bi bi-images"></i> Volver a la galería
            </a>
        </div>
    </div>
</body>
</html>