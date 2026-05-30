<?php
    session_start();
    include("conexion.php");
    
    if (empty($_SESSION['carrito'])) { 
        header("Location: carrito.php"); 
        exit(); 
    }
    
    $ids = array_map('intval', array_keys($_SESSION['carrito']));
    $lista_ids = implode(',', $ids);
    
    $res = mysqli_query($conexion, "SELECT SUM(precio) as total FROM obras WHERE id IN ($lista_ids)");
    $dato = mysqli_fetch_assoc($res);
    $total_a_pagar = $dato['total'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Artrómeda</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --dorado: #deb887;
            --oscuro-puro: #000000;
            --gris-input: #222222;
            --borde-suave: rgba(222, 184, 135, 0.3);
            --marron-texto: #6b3e0a;
        }

        body {
            background: url("fondo.png") no-repeat center center fixed;
            background-size: cover;
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 40px 20px;
            line-height: 1.6;
        }

        .checkout-container {
            max-width: 850px;
            margin: 0 auto;
            background: rgba(247, 245, 245, 0.4);
            backdrop-filter: blur(7px);
            border: 2px solid rgba(240, 141, 12, 0.4);
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(182, 209, 139, 0.94);
        }

        h2, h3 {
            color: var(--marron-texto);
            text-transform: uppercase;
            letter-spacing: 2px;
            text-align: center;
            margin-bottom: 30px;
        }

        .grid-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        label {
            display: block;
            font-size: 0.8rem;
            font-weight: bold;
            margin-bottom: 8px;
            color: var(--marron-texto);
            text-transform: uppercase;
        }

        input, select {
            width: 100%;
            padding: 14px;
            border-radius: 10px;
            border: 1px solid #7c4715;
            background: white;
            color: #000000;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        input:focus {
            border-color: #6b3e0a;
            outline: none;
            background: #f8f6f6;
            box-shadow: 0 0 10px rgba(235, 135, 6, 0.2);
        }

        .resumen {
            color: #070707;
            font-size: 1.2rem;
            font-weight: bold;
            margin-top: 25px;
            text-align: center;
        }

        .resumen strong {
            color: #080808;
        }

        .payment-methods {
            display: flex;
            gap: 15px;
            margin: 30px 0;
        }

        .method-card {
            flex: 1;
            color: #020202;
            background: rgba(54, 54, 54, 0.05);
            border: 1px solid #6d4b18;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .method-card i {
            font-size: 2rem;
            display: block;
            margin-bottom: 10px;
            color: #7c4715;
        }

        .method-card span {
            font-size: 0.9rem;
            font-weight: 600;
        }

        .method-card:hover {
            transform: translateY(-5px);
            border-color: #6b3e0a;
        }

        .method-card.active {
            background: rgba(222, 184, 135, 0.15);
            border: 2px solid var(--marron-texto);
        }

        .method-card.active i {
            color: #924803;
        }

        #campos-tarjeta {
            background: rgba(255, 255, 255, 0.03);
            padding: 25px;
            border-radius: 15px;
            border: 1px dashed #555;
            margin-top: 20px;
        }

        .btn-pagar {
            display: block;
            width: 100%;
            background: transparent;
            color: #6b3e0a;
            border: 2px solid rgba(117, 71, 11, 0.76);
            padding: 18px;
            border-radius: 10px;
            font-size: 1.0rem;
            font-weight: 800;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 30px;
            transition: 0.3s;
        }

        .btn-pagar:hover {
            background: #dacda6e3;
            transform: translateY(-2px);
        }

        @media (max-width: 767px) {
            .grid-form {
                grid-template-columns: 1fr;
            }
            .grid-form div[style*="span 2"] {
                grid-column: span 1 !important;
            }
            .payment-methods {
                flex-direction: column;
            }
        }
    </style>
</head>
    <body>
        <div class="checkout-container">
            <h2>Finalizar Adquisición</h2>
            <form action="procesar_pago.php" method="POST">
                
                <div class="grid-form">
                    <div>
                        <label>Nombre Completo</label>
                        <input type="text" name="nombre_pago" required placeholder="Como aparece en su documento">
                    </div>
                    <div>
                        <label>Correo Electrónico</label>
                        <input type="email" name="email_pago" required placeholder="correo@ejemplo.com">
                    </div>
                    <div style="grid-column: span 2;">
                        <label>Dirección de Envío (Si aplica)</label>
                        <input type="text" name="direccion" required placeholder="Calle, Ciudad, Depto">
                    </div>
                </div>

                <h3 style="margin-top:25px;">Método de Pago</h3>
                <div class="payment-methods">
                    <div class="method-card active" onclick="selectMethod('tarjeta', this)">
                        <i class="bi bi-credit-card"></i> <span>Tarjeta</span>
                    </div>
                    <div class="method-card" onclick="selectMethod('pse', this)">
                        <i class="bi bi-bank"></i> <span>PSE</span>
                    </div>
                    <div class="method-card" onclick="selectMethod('efectivo', this)">
                        <i class="bi bi-cash-stack"></i> <span>Efectivo</span>
                    </div>
                </div>

                <input type="hidden" name="metodo_pago" id="metodo_pago" value="tarjeta">

                <div id="campos-tarjeta">
                    <input type="text" name="tarjeta_numero" class="input-tarjeta" placeholder="Número de Tarjeta" maxlength="16" required>
                    <div style="display:flex; gap:10px; margin-top:10px;">
                        <input type="text" name="tarjeta_fecha" class="input-tarjeta" placeholder="MM/AA" maxlength="5" required>
                        <input type="text" name="tarjeta_cvv" class="input-tarjeta" placeholder="CVV" maxlength="3" required>
                    </div>
                </div>

                <div class="resumen">
                    Total a pagar: <strong>$<?php echo number_format($total_a_pagar, 0, ',', '.'); ?> COP</strong>
                </div>

                <button type="submit" class="btn-pagar">CONFIRMAR Y PAGAR</button>
            </form>
        </div>

        <script>
            function selectMethod(method, element) {
                document.querySelectorAll('.method-card').forEach(m => m.classList.remove('active'));
                element.classList.add('active');
                
                document.getElementById('metodo_pago').value = method;
                
                const camposTarjeta = document.getElementById('campos-tarjeta');
                const inputsTarjeta = document.querySelectorAll('.input-tarjeta');

                if (method === 'tarjeta') {
                    camposTarjeta.style.display = 'block';
                    inputsTarjeta.forEach(input => input.setAttribute('required', 'true'));
                } else {
                    camposTarjeta.style.display = 'none';
                    inputsTarjeta.forEach(input => input.removeAttribute('required'));
                }
            }
        </script>
    </body>
</html>