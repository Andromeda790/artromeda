<?php
    session_start();
    include("conexion.php");
    if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'administrador') {
        header("Location: login.php");
        exit();
    }
    $nivel = $_SESSION['nivel_permiso'] ?? 3;
    function obtenerTotal($conexion, $query) {
        $res = mysqli_query($conexion, $query);
        return ($res) ? (mysqli_fetch_assoc($res)['total'] ?? 0) : 0;
    }
    $total_ingresos = obtenerTotal($conexion, "SELECT SUM(precio_final) as total FROM pedidos");
    $total_pedidos = obtenerTotal($conexion, "SELECT COUNT(id) as total FROM pedidos");
    $ventas_mes = obtenerTotal($conexion, "SELECT SUM(precio_final) as total FROM pedidos 
        WHERE MONTH(fecha_pedido) = MONTH(CURRENT_DATE()) AND YEAR(fecha_pedido) = YEAR(CURRENT_DATE())");
    $query_artistas = "SELECT u.nombres AS artista, SUM(p.precio_final) AS total 
                       FROM pedidos p JOIN obras o ON p.id_obras = o.id
                       JOIN usuarios u ON o.id_usuarios = u.id 
                       GROUP BY u.nombres ORDER BY total DESC LIMIT 5";
    $top_artistas_res = mysqli_query($conexion, $query_artistas);
    $top_artistas = []; $top_ingresos = [];
    while ($row = mysqli_fetch_assoc($top_artistas_res)) {
        $top_artistas[] = $row['artista'];
        $top_ingresos[] = $row['total'];
    }
    $ventas_anio = array_fill(1, 12, 0);
    $query_mensual = "SELECT MONTH(fecha_pedido) as mes, SUM(precio_final) as total 
                      FROM pedidos WHERE YEAR(fecha_pedido) = YEAR(CURRENT_DATE()) 
                      GROUP BY MONTH(fecha_pedido)";
    $res_mensual = mysqli_query($conexion, $query_mensual);
    while($row = mysqli_fetch_assoc($res_mensual)) {
        $ventas_anio[(int)$row['mes']] = (float)$row['total'];
    }
    $query_recientes = "SELECT p.precio_final, p.fecha_pedido, o.titulo, u.nombres as artista 
                         FROM pedidos p JOIN obras o ON p.id_obras = o.id 
                         JOIN usuarios u ON o.id_usuarios = u.id 
                         ORDER BY p.fecha_pedido DESC LIMIT 6";
    $pedidos_recientes = mysqli_query($conexion, $query_recientes);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REPORTES FINANCIEROS | Artrómeda</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --dorado: #e4b67b;
            --glass-bg: rgba(0, 0, 0, 0.8);
            --glass-card: rgba(3, 3, 3, 0.62);
            --blur-effect: blur(15px);
            --border-color: rgba(222, 184, 135, 0.3);
            --text-white: #ffffff;
            --text-gray: #f8f7f7;
            --success-color: #54c9a6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', 'Segoe UI', sans-serif;
        }

        body {
            background: url("fondo.png") no-repeat center center fixed;
            background-size: cover;
            color: white;
            min-height: 100vh;
        }

        header {
            padding: 0.9rem 5%;
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

        .brand-title {
            font-size: 22px;
            font-weight: 300;
            letter-spacing: 5px;
            text-transform: uppercase;
            color: #ffffff;
            text-shadow: 2px 4px 10px rgba(209, 104, 34, 0.67);
        }

        .nav-back-button {
            text-decoration: none;
            color: #ffffff;
            border: 1px solid #ffffff;
            padding: 10px 25px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 4px;
            transition: 0.3s;
            background: rgba(190, 152, 101, 0.4);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-back-button:hover {
            background: var(--dorado);
            color: #f8f7f7;
            transform: translateY(-2px);
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: rgba(247, 245, 245, 0.4);
            backdrop-filter: blur(7px);
            border: 2px solid rgba(240, 141, 12, 0.4);
            padding: 30px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            transition: 0.3s;
        }

        .stat-card:hover {
            border-color: var(--dorado);
            background: rgba(85, 84, 84, 0.7);
        }

        .stat-icon {
            font-size: 28px;
            color: var(--dorado);
            margin-right: 20px;
            width: 65px;
            height: 65px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(170, 103, 15, 0.68);
            border-radius: 50%;
            border: 1px solid var(--border-color);
        }

        .stat-info h4 {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #6b3e0a;
            margin-bottom: 5px;
        }

        .stat-info .stat-value {
            color: #54c967bd;
            font-size: 26px;
            font-weight: 700;
            display: block;
        }

        .charts-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 40px;
        }

        .chart-container {
            background: rgba(247, 245, 245, 0.4);
            backdrop-filter: blur(7px);
            border: 2px solid rgba(240, 141, 12, 0.4);
            padding: 25px;
            border-radius: 12px;
        }

        .chart-container h3 {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 25px;
            color: #6b3e0a;
            text-align: center;
        }

        .table-wrapper {
            background: rgba(247, 245, 245, 0.4);
            backdrop-filter: blur(7px);
            border: 2px solid rgba(240, 141, 12, 0.4);
            padding: 35px;
            border-radius: 12px;
            overflow-x: auto;
            margin-bottom: 50px;
        }

        .table-header-flex {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .table-header-flex h3 {
            font-weight: 300;
            font-size: 20px;
            letter-spacing: 1px;
        }

        .table-header-flex i {
            color: #54c9a6;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 18px 15px;
            color: #6b3e0a;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #6b3e0a;
            background: rgba(248, 5, 5, 0.02);
        }

        .h3 {
            color: #6b3e0a;
        }

        td {
            padding: 18px 15px;
            font-size: 14px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .td-artwork {
            color: #070707;
            font-weight: 600;
        }

        .td-price {
            color: green;
            font-weight: 700;
        }

        @media (max-width: 767px) {
            .charts-row {
                grid-template-columns: 1fr;
            }
            
            header {
                flex-direction: column;
                height: auto;
                padding: 25px;
                gap: 20px;
            }
        }
    </style>
</head>
    <body>
        <header>
            <div class="brand-title">
                REPORTES FINANCIEROS
            </div>
            <a href="admin_dashboard.php" class="nav-back-button">
                <i class="fas fa-chevron-left"></i> Volver al Panel
            </a>
        </header>
        <div class="container">
            <section class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-vault"></i></div>
                    <div class="stat-info">
                        <h4>Capital Acumulado</h4>
                        <span class="stat-value">$<?php echo number_format($total_ingresos, 2); ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="stat-info">
                        <h4>Ingresos del Mes</h4>
                        <span class="stat-value">$<?php echo number_format($ventas_mes, 2); ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-receipt"></i></div>
                    <div class="stat-info">
                        <h4>Ventas Totales</h4>
                        <span class="stat-value"><?php echo $total_pedidos; ?></span>
                    </div>
                </div>
            </section>
            <section class="charts-row">
                <div class="chart-container">
                    <h3>Top Artistas por Recaudación</h3>
                    <canvas id="canvasArtistas"></canvas>
                </div>
                <div class="chart-container">
                    <h3>Rendimiento Anual <?php echo date('Y'); ?></h3>
                    <canvas id="canvasVentas"></canvas>
                </div>
            </section>
            <section class="table-wrapper">
                <div class="table-header-flex">
                    <i class="fas fa-history"></i>
                    <h3 class="h3">HISTORIAL DE MOVIMIENTOS</h3>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Obra</th>
                            <th>Artista</th>
                            <th>Fecha</th>
                            <th>Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($pedidos_recientes)): ?>
                        <tr>
                            <td class="td-artwork"><?php echo htmlspecialchars($row['titulo']); ?></td>
                            <td style="color: #070707;"><?php echo htmlspecialchars($row['artista']); ?></td>
                            <td style="color: #070707;"><?php echo date('d/m/Y', strtotime($row['fecha_pedido'])); ?></td>
                            <td class="td-price">$<?php echo number_format($row['precio_final'], 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </section>
        </div>
        <script>
            Chart.defaults.color = 'rgb(8, 8, 8)';
            Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.05)';
            Chart.defaults.font.family = "'Inter', sans-serif";
            new Chart(document.getElementById('canvasArtistas'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($top_artistas); ?>,
                    datasets: [{
                        label: 'Ventas USD',
                        data: <?php echo json_encode($top_ingresos); ?>,
                        backgroundColor: '#e7ad61',
                        hoverBackgroundColor: '#f5e1c8',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
            new Chart(document.getElementById('canvasVentas'), {
                type: 'line',
                data: {
                    labels: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
                    datasets: [{
                        label: 'Ingresos',
                        data: <?php echo json_encode(array_values($ventas_anio)); ?>,
                        borderColor: '#d69e54',
                        backgroundColor: 'rgba(222, 184, 135, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#54c9a6',
                        pointBorderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        </script>
    </body>
</html>