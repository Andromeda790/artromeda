<?php 
    session_start();
    include("conexion.php");
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = mysqli_real_escape_string($conexion, $_POST['email']);
        $password_ingresado = $_POST['password'];
        $sql = "SELECT * FROM usuarios WHERE email = '$email'";
        $resultado = mysqli_query($conexion, $sql);
        if (mysqli_num_rows($resultado) > 0) {
            $usuario = mysqli_fetch_assoc($resultado);
            if (password_verify($password_ingresado, $usuario['password'])) {
                $login_valido = true;
            } 
            elseif ($password_ingresado === $usuario['password']) {
                $nuevoHash = password_hash($password_ingresado, PASSWORD_DEFAULT);
                mysqli_query($conexion, "UPDATE usuarios SET password='$nuevoHash' WHERE id=" . $usuario['id']);
                $login_valido = true;
            } else {
                $login_valido = false;
            }
            if ($login_valido) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombres'];
                $_SESSION['usuario_rol'] = $usuario['rol'];
                if ($usuario['rol'] === 'administrador') {
                    $_SESSION['usuario_nivel'] = (int)$usuario['nivel'] ?? 1;
                    if ($_SESSION['usuario_nivel'] === 3) {
                        header("Location: admin_dashboard.php"); 
                    } else {
                        header("Location: administrador.php");
                    }
                } elseif ($usuario['rol'] === 'artista') {
                    header("Location: perfil_artista.php?id=" . $usuario['id']);
                } else {
                    header("Location: perfil.php");
                }
                exit();
            } else {
                $error_login = "El correo o la contraseña son incorrectos.";
            }
        } else {
            $error_login = "El correo o la contraseña son incorrectos.";
        }
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar - Artrómeda</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --dorado: #deb887;
            --dorado-brillante: #e4924f;
            --oscuro-trans: rgba(0,0,0,0.7);
            --blur: blur(15px);
            --error: #ff4d4d;
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
            padding: 0.2rem 5%;
            background: transparent;
            box-shadow: 0 10px 25px rgb(233, 238, 192);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--dorado);
            backdrop-filter: var(--blur);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo-img {
            height: 72px;
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .logo-img:hover {
            transform: scale(1.08) translateY(-3px);
        }

        .nav-list {
            list-style: none;
            display: flex;
            gap: 25px;
            align-items: center;
        }

        .nav-list li a {
            text-decoration: none;
            color: white;
            font-weight: 500;
            font-size: 0.8rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            position: relative;
            padding: 5px 0;
            transition: color 0.3s ease;
        }

        .nav-list li a:hover {
            color: var(--dorado-brillante);
        }

        .nav-list li a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: var(--dorado-brillante);
            transition: width 0.3s ease;
        }

        .nav-list li a:hover::after {
            width: 100%;
        }

        .menu-toggle {
            display: none;
            font-size: 2.2rem;
            color: white;
            cursor: pointer;
            z-index: 1001;
        }

        #contenedor {
            background: rgba(247, 245, 245, 0.4);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 2px solid #6b3e0a;
            border-radius: 25px;
            margin: 3rem auto;
            padding: 3rem 2.5rem;
            width: 90%;
            max-width: 480px;
            box-shadow: 0 20px 40px rgba(182, 209, 139, 0.4);
        }

        h1 {
            color: #faf9f8;
            text-align: center;
            margin-bottom: 1.5rem;
            letter-spacing: 3px;
            text-transform: uppercase;
            font-size: 2.2rem;
            text-shadow: 2px 4px 10px rgba(209, 104, 34, 0.84);
        }

        form ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        label {
            color: #6b3e0a;
            font-size: 0.85rem;
            margin-bottom: 8px;
            display: block;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 1px;
        }

        input {
            width: 100%;
            padding: 1rem;
            border-radius: 12px;
            border: 1px solid #6b3e0a;
            background: rgba(248, 246, 246, 0.4);
            color: #020202;
            outline: none;
            font-size: 1rem;
            transition: 0.3s;
        }

        input::placeholder {
            color: #272726;
            font-style: italic;
            opacity: 0.6;
        }

        input:focus {
            border-color: #dd580bcc;
            background: rgba(247, 244, 244, 0.6);
            box-shadow: 0 0 10px rgb(219, 128, 8);
        }

        .mensaje-error {
            background-color: rgba(255, 77, 77, 0.25);
            color: #700707;
            border: 1px solid var(--error);
            padding: 12px;
            border-radius: 10px;
            text-align: center;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            transition: opacity 0.4s ease;
            animation: fadeIn 0.4s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #e4924f;
            font-size: 1.3rem;
        }

        button {
            background: transparent;
            color: #6b3e0a;
            border: 2px solid #6b3e0a;
            padding: 1rem;
            font-weight: bold;
            border-radius: 12px;
            cursor: pointer;
            width: 100%;
            margin-top: 0.5rem;
            text-transform: uppercase;
            font-size: 1rem;
            transition: 0.3s;
        }

        button:hover {
            background: #dacda6e3;
            transform: translateY(-2px);
        }

        .login-footer-links {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            margin-top: 25px;
        }

        .login-footer-links a {
            color: #1e4620;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: 0.3s;
        }

        .login-footer-links a:hover {
            text-decoration: underline;
            color: #2e6930;
        }

        .main-footer {
            background: transparent;
            backdrop-filter: blur(10px);
            border-top: 1px solid var(--dorado); 
            padding: 20px 7%; 
            margin-top: auto;
        }

        .footer-content { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            color: #020202; 
            font-size: 0.9rem; 
        }

        .btn-whatsapp {
            background: #25d366; 
            color: white; 
            text-decoration: none;
            padding: 10px 20px; 
            border-radius: 7px; 
            font-weight: bold;
            display: flex; 
            align-items: center; 
            gap: 10px; 
            transition: 0.3s;
            text-shadow: none;
        }

        .btn-whatsapp:hover {
            background: #20ba5a;
            transform: translateY(-2px);
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

            #contenedor {
                padding: 2rem 1.5rem;
                margin: 2rem auto;
            }

            h1 {
                font-size: 1.8rem;
            }

            .footer-content { 
                flex-direction: column; 
                gap: 15px; 
                text-align: center; 
            }
        }
    </style>
</head>
    <body>
        <header>
            <a href="index.php"><img src="Logo.png" alt="Logo" class="logo-img"></a>
            <nav>
                <ul class="nav-list">
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="obras.php">Galería</a></li>
                    <li><a href="registro.php">Registrarse</a></li>
                </ul>
            </nav>
            <div class="menu-toggle" id="mobile-menu">
                <i class="bi bi-list"></i>
            </div>
        </header>

        <div id="contenedor">
            <h1>Iniciar Sesión</h1>
            <?php if (!empty($error_login)): ?>
                <div class="mensaje-error" id="error-box">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error_login; ?>
                </div>
            <?php endif; ?>
            <form method="post" action="login.php">
                <ul>
                    <li>
                        <label for="email">Correo electrónico</label>
                        <input id="email" name="email" type="email" placeholder="ejemplo@correo.com" required />
                    </li>
                    <li>
                        <label for="password">Contraseña</label>
                        <div class="password-container">
                            <input id="password" name="password" type="password" placeholder="••••••••" required />
                            <i class="bi bi-eye toggle-password" id="eyeIcon"></i>
                        </div>
                    </li>
                    <li>
                        <button type="submit">Ingresar</button>
                    </li>
                </ul>
            </form>
            <div class="login-footer-links">
                <a href="olvido_password.php">¿Olvidaste tu contraseña?</a>
                <a href="registro.php">¿No tienes cuenta? Regístrate aquí</a>
            </div>
        </div>

        <footer class="main-footer">
            <div class="footer-content">
                <p>&copy; 2026 Artrómeda - Galería de Arte Digital</p>
                <a href="https://wa.me/573197889132" class="btn-whatsapp" target="_blank">
                    <i class="bi bi-whatsapp"></i> Contáctanos
                </a>
            </div>
        </footer>

        <script>
            const menuBtn = document.getElementById('mobile-menu');
            const nav = document.querySelector('.nav-list');
            
            if (menuBtn && nav) {
                const icon = menuBtn.querySelector('i');
                menuBtn.addEventListener('click', () => {
                    nav.classList.toggle('active');
                    if (nav.classList.contains('active')) {
                        icon.classList.replace('bi-list', 'bi-x-lg');
                    } else {
                        icon.classList.replace('bi-x-lg', 'bi-list');
                    }
                });
            }

            const eyeIcon = document.getElementById('eyeIcon');
            const passInput = document.getElementById('password');
            if (eyeIcon && passInput) {
                eyeIcon.addEventListener('click', () => {
                    const isPassword = passInput.type === 'password';
                    passInput.type = isPassword ? 'text' : 'password';
                    eyeIcon.classList.toggle('bi-eye', !isPassword);
                    eyeIcon.classList.toggle('bi-eye-slash', isPassword);
                });
            }

            const errorBox = document.getElementById('error-box');
            const inputs = document.querySelectorAll('#contenedor input');
            if (errorBox) {
                inputs.forEach(input => {
                    input.addEventListener('input', () => {
                        errorBox.style.opacity = '0';
                        setTimeout(() => {
                            errorBox.style.display = 'none';
                        }, 400);
                    });
                });
            }
        </script>
    </body>
</html>