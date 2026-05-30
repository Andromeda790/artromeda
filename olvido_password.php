<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Artrómeda</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --dorado: #deb887;
            --oscuro-trans: rgba(0, 0, 0, 0.7);
            --blur: blur(15px);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            color: white;
            min-height: 100vh;
            background: url("fondo.png") no-repeat center center fixed;
            background-size: cover;
            display: flex;
            flex-direction: column;
        }

        #contenedor {
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: var(--blur);
            border: 1px solid rgba(222, 184, 135, 0.3);
            border-radius: 20px;
            margin: auto; 
            padding: 2.5rem;
            width: 95%;
            max-width: 450px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            text-align: center;
        }

        h1 { 
            font-size: 1.8rem; 
            text-transform: uppercase; 
            margin-bottom: 1rem; 
            letter-spacing: 2px;
        }

        p {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 2rem;
            line-height: 1.5;
        }

        .input-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        label {
            display: block;
            font-size: 0.8rem;
            text-transform: uppercase;
            margin-bottom: 8px;
            color: var(--dorado);
        }

        input {
            width: 100%;
            padding: 1rem;
            border-radius: 10px;
            border: 1px solid rgba(222, 184, 135, 0.2);
            background: rgba(255, 255, 255, 0.05);
            color: white;
            outline: none;
            transition: 0.3s;
        }

        input:focus {
            border-color: var(--dorado);
            background: rgba(255, 255, 255, 0.1);
        }

        input::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        button {
            background: var(--dorado);
            color: #000;
            border: none;
            padding: 1rem;
            width: 100%;
            font-weight: bold;
            border-radius: 10px;
            cursor: pointer;
            text-transform: uppercase;
            transition: 0.4s;
        }

        button:hover {
            background: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(222, 184, 135, 0.4);
        }

        .back-link {
            margin-top: 1.5rem;
            display: block;
            color: white;
            text-decoration: none;
            font-size: 0.85rem;
            opacity: 0.7;
        }

        .back-link:hover { 
            opacity: 1; 
            text-decoration: underline; 
        }
    </style>
</head>
<body>
    <div id="contenedor">
        <i class="bi bi-shield-lock" style="font-size: 3rem; color: var(--dorado);"></i>
        <h1>¿Olvidaste tu contraseña?</h1>
        <p>No te preocupes. Ingresa tu correo electrónico y te enviaremos las instrucciones para restablecerla.</p>
        <form action="procesar_recuperacion.php" method="POST">
            <div class="input-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" placeholder="ejemplo@correo.com" required>
            </div>
            <button type="submit">Enviar</button>
        </form>
        <a href="login.php" class="back-link">
            <i class="bi bi-arrow-left"></i> Volver al inicio de sesión
        </a>
    </div>
</body>
</html>