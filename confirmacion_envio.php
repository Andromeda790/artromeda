<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Recuperación - Artrómeda</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --dorado: #deb887;
            --oscuro-trans: rgba(0, 0, 0, 0.7);
            --blur: blur(15px);
            --exito: #2ecc71;
            --error: #e74c3c;
        }

        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            color: white;
            min-height: 100vh;
            background: url("fondo.png") no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        #contenedor {
            background: var(--oscuro-trans);
            backdrop-filter: var(--blur);
            border: 1px solid rgba(222, 184, 135, 0.3);
            border-radius: 20px;
            padding: 3rem;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            text-align: center;
        }

        .icon-success { 
            color: var(--exito); 
            font-size: 4.5rem; 
            margin-bottom: 1rem; 
            display: block; 
        }

        .icon-error { 
            color: var(--error); 
            font-size: 4.5rem; 
            margin-bottom: 1rem; 
            display: block; 
        }

        h1 {
            font-size: 1.6rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 1.5rem;
        }

        p {
            font-size: 1rem;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
        }

        strong { 
            color: var(--dorado); 
        }

        .btn-volver {
            display: inline-block;
            background: var(--dorado);
            color: #000;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: bold;
            text-transform: uppercase;
            transition: 0.4s;
            font-size: 0.9rem;
        }

        .btn-volver:hover {
            background: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(222, 184, 135, 0.4);
        }
    </style>
</head>
<body>
    <div id="contenedor">
        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <i class="bi bi-envelope-check icon-success"></i>
            <h1>¡Enlace Enviado!</h1>
            <p>Si el correo <strong><?php echo htmlspecialchars($_GET['email']); ?></strong> Estás registrado, recibirás instrucciones en breve.</p>
        <?php else: ?>
            <i class="bi bi-exclamation-octagon icon-error"></i>
            <h1>Error de Usuario</h1>
            <p>Lo sentimos, no pudimos encontrar ninguna cuenta asociada a ese correo electrónico.</p>
        <?php endif; ?>
        <a href="login.php" class="btn-volver">Volver al Inicio</a>
    </div>
</body>
</html>