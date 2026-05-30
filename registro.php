<?php
    session_start();
    include("conexion.php");
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nombres = mysqli_real_escape_string($conexion, $_POST['nombres']);
        $apellidos = mysqli_real_escape_string($conexion, $_POST['apellidos']);
        $direccion = mysqli_real_escape_string($conexion, $_POST['direccion']);
        $email = mysqli_real_escape_string($conexion, $_POST['email']);
        $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
        $password_plano = $_POST['password'];
        $password = password_hash($password_plano, PASSWORD_DEFAULT);
        $rol_final = mysqli_real_escape_string($conexion, $_POST['rol']);
        $nivel = 1;
        if($rol_final == 'artista'){
            $nivel = 2;
        }
        $foto_perfil = "default.jpg";
        $fecha_creacion = date("Y-m-d H:i:s");
        $sql = "INSERT INTO usuarios (nombres, apellidos, direccion, email, telefono, password, rol, nivel, foto_perfil, fecha_creacion) 
        VALUES ('$nombres', '$apellidos', '$direccion', '$email', '$telefono', '$password', '$rol_final', '$nivel', '$foto_perfil', '$fecha_creacion')";
        if (mysqli_query($conexion, $sql)) {
            echo "<script>alert('¡Registro exitoso como $rol_final! Ahora puedes iniciar sesión.'); window.location='login.php';</script>";
        } else {
            echo "<script>alert('Error al registrar: " . mysqli_error($conexion) . "');</script>";
        }
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Únete a Artrómeda - Registro</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --dorado: #deb887;
            --dorado-brillante: #e4924f;
            --oscuro-trans: rgba(0, 0, 0, 0.7);
            --blur: blur(15px);
            --error: #ff4d4d;
        }

        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }

        body {
            font-family: 'Montserrat', sans-serif;
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
            cursor: pointer;
            font-size: 2.2rem;
            color: white;
            z-index: 1001; 
        }

        #contenedor {
            background: rgba(247, 245, 245, 0.4);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 2px solid rgba(112, 70, 14, 0.62);
            border-radius: 25px;
            margin: 3rem auto;
            padding: 3rem 2.5rem;
            width: 90%;
            max-width: 520px;
            box-shadow: 0 20px 40px rgba(182, 209, 139, 0.4);
        }

        h1 { 
            color: #f8f7f7;
            text-align: center; 
            margin-bottom: 0.5rem; 
            font-size: 2rem; 
            letter-spacing: 2px; 
            text-transform: uppercase;
            text-shadow: 2px 4px 10px rgba(209, 104, 34, 0.76); 
        }

        .header-registro {
            border-bottom: 1px solid rgba(222, 184, 135, 0.4);
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            text-align: center;
        }

        .required-info {
            font-size: 0.8rem;
            color: #6b3e0a;
            margin-bottom: 0.5rem;
            font-style: italic;
            font-weight: 500;
        }

        .mensaje-obligatorio {
            font-size: 0.8rem;
            color: #6b3e0a;
            margin: 0;
            font-weight: 500;
        }

        .required-star {
            color: #ff4d4d;
            font-weight: bold;
        }

        form ul { 
            list-style: none; 
            display: flex; 
            flex-direction: column; 
            gap: 1.2rem; 
        }

        li {
            display: flex;
            flex-direction: column;
        }

        label { 
            font-weight: bold; 
            font-size: 0.85rem; 
            margin-bottom: 8px; 
            display: block; 
            color: #6b3e0a; 
            text-transform: uppercase; 
            letter-spacing: 1px;
        }

        input, select {
            width: 100%; 
            padding: 1rem; 
            border-radius: 12px;
            border: 1px solid rgba(100, 63, 15, 0.52);
            background: rgba(248, 246, 246, 0.4);
            color: #030303; 
            outline: none; 
            font-size: 1rem;
            transition: 0.3s;
        }

        input::placeholder { 
            color: #272726;
            font-style: italic;
            opacity: 0.6;
        }

        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 40px #c2a369b0 inset;
            -webkit-text-fill-color: #6b3e0a;
            transition: background-color 5000s ease-in-out 0s;
        }

        input:focus, select:focus {
            border-color: #dd580bcc;
            background: rgba(253, 253, 253, 0.6);
            box-shadow: 0 0 10px rgb(219, 128, 8);
        }

        select {
            accent-color: var(--dorado);
            cursor: pointer;
            background-repeat: no-repeat;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23deb887' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 12 15 18 9'></polyline></svg>");
            background-position: right 15px center;
            background-size: 18px;
            padding-right: 45px;
        }

        select option {
            background-color: #1a1a1a;
            color: var(--dorado);   
            padding: 10px;
        }

        button {
            background: transparent;
            color: #6b3e0a;
            border: 2px solid rgba(114, 69, 10, 0.56);
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

        .password-container { 
            position: relative; 
            display: flex; 
            align-items: center; 
            width: 100%;
        }

        .password-container input { 
            padding-right: 45px; 
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

        .grid-inputs { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 15px; 
        }

        .links { 
            text-align: center; 
            margin-top: 1.5rem; 
        }

        .links a { 
            color: #1e4620; 
            text-decoration: none; 
            font-size: 0.9rem; 
            font-weight: 600;
            transition: 0.3s; 
        }

        .links a:hover { 
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
            color: #030303; 
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

            .grid-inputs { 
                grid-template-columns: 1fr; 
                gap: 1.2rem;
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
                    <li><a href="login.php">Iniciar Sesión</a></li>
                </ul>
            </nav>
            <div class="menu-toggle" id="mobile-menu">
                <i class="bi bi-list"></i>
            </div>
        </header>

        <div id="contenedor">
            <h1>Registro</h1>
            <div class="header-registro">
                <p class="required-info">Completa tus datos para unirte a la comunidad.</p>
                <p class="mensaje-obligatorio">
                    Los campos marcados con <span class="required-star">*</span> son obligatorios
                </p>
            </div>
            <form method="post">
                <ul>
                    <div class="grid-inputs">
                        <li>
                            <label>Nombres <span class="required-star">*</span></label>
                            <input name="nombres" type="text" placeholder="Tu nombre" required />
                        </li>
                        <li>
                            <label>Apellidos <span class="required-star">*</span></label>
                            <input name="apellidos" type="text" placeholder="Tu apellido" required />
                        </li>
                    </div>
                    <li>
                        <label>¿Quién eres? <span class="required-star">*</span></label>
                        <select name="rol" required>
                            <option value="" disabled selected>Selecciona tu perfil</option>
                            <option value="usuario">Soy un usuario</option>
                            <option value="artista">Soy un artista</option>
                        </select>
                    </li>
                    <li>
                        <label>Dirección de Envío <span class="required-star">*</span></label>
                        <input name="direccion" type="text" placeholder="Calle, Carrera, Ciudad" required />
                    </li>
                    <li>
                        <label>Correo electrónico <span class="required-star">*</span></label>
                        <input name="email" type="email" placeholder="ejemplo@correo.com" required />
                    </li>
                    <div class="grid-inputs">
                        <li>
                            <label>Teléfono <span class="required-star">*</span></label>
                            <input name="telefono" type="tel" placeholder="300..." required />
                        </li>
                        <li>
                            <label>Contraseña <span class="required-star">*</span></label>
                            <div class="password-container">
                                <input id="password" name="password" type="password" placeholder="••••••••" required />
                                <i class="bi bi-eye toggle-password" id="eyeIcon"></i>
                            </div>
                        </li>
                    </div>        
                    <li><button type="submit">Crear Cuenta</button></li>
                </ul>
            </form>
            <div class="links">
                <a href="login.php">¿Ya tienes cuenta? Inicia sesión aquí</a>
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
            const mobileMenu = document.getElementById('mobile-menu');
            const navList = document.querySelector('.nav-list');
            
            if(mobileMenu && navList) {
                const icon = mobileMenu.querySelector('i');
                mobileMenu.addEventListener('click', () => {
                    navList.classList.toggle('active');
                    if (navList.classList.contains('active')) {
                        icon.classList.replace('bi-list', 'bi-x-lg');
                    } else {
                        icon.classList.replace('bi-x-lg', 'bi-list');
                    }
                });
            }

            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            if (passwordInput && eyeIcon) {
                eyeIcon.addEventListener('click', function() {
                    const isPassword = passwordInput.type === 'password';
                    passwordInput.type = isPassword ? 'text' : 'password';
                    this.classList.toggle('bi-eye', !isPassword);
                    this.classList.toggle('bi-eye-slash', isPassword);
                });
            }
        </script>
    </body>
</html>