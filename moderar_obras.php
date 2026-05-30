<?php
    session_start();
    require_once("verificar_admin.php");
    require_once("conexion.php");
    $nivel = verificarAdmin(2);
    $query = "SELECT o.*, u.nombres, u.apellidos
            FROM obras o
            JOIN usuarios u ON o.id_usuarios = u.id
            WHERE o.estado = 'pendiente'
            ORDER BY o.id DESC";
    $res_obras = mysqli_query($conexion, $query);
    if (!$res_obras) {
        die("Error en la consulta: " . mysqli_error($conexion));
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Moderar Obras</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { 
            background: url("fondo.png") no-repeat center center fixed;
            color:white; 
            font-family:Segoe UI; 
            padding:40px; 
            min-height: 100vh;
            background-size: cover;
        }

        .h1 {
            text-align: center;
            padding: 40px 20px 10px;
            font-size: 2.0rem;
            margin-bottom: 40px;
            letter-spacing: 4px;
            text-transform: uppercase;
            text-shadow: 2px 4px 10px rgba(209, 104, 34, 0.67);
        }

        .mensaje {
            text-align: center;
            padding: 40px 20px 10px;
            font-size: 1.5rem;
            margin-bottom: 40px;
            letter-spacing: 4px;
            text-shadow: 2px 4px 10px rgb(87, 109, 9);
        }
        
        .card {
            background:#1c1c1c;
            padding:20px;
            margin-bottom:20px;
            border-radius:8px;
            display:flex;
            gap:20px;
            align-items:center;
        }

        .btn-volver {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #e4924f;
            text-decoration: none;
            font-weight: bold;
            margin-bottom: 20px;
            transition: 0.3s;
            font-size: 1.3rem;
        }

        .btn-volver:hover {
            color: white;
            transform: translateX(-5px);
        }

        img { 
            width:120px; 
            border-radius:8px; 
        }

        button {
            padding:8px 15px;
            border:none;
            border-radius:6px;
            cursor:pointer;
            font-weight:bold;
        }
        .aprobar { 
            background:#28a745; 
            color:white; 
        }

        .rechazar { 
            background:#dc3545; 
            color:white; 
        }
    </style>
</head>
    <body>
        <a href="admin_dashboard.php" class="btn-volver">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <h1 class="h1">Obras Pendientes</h1>
        <?php if(isset($_GET['res'])): ?>
            <div style="padding: 10px; margin-bottom: 20px; border-radius: 5px; background: <?php echo $_GET['res'] == 'aprobada' ? '#28a745' : '#dc3545'; ?>;">
                Obra <?php echo $_GET['res'] == 'aprobada' ? 'aprobada y publicada con éxito.' : 'rechazada exitosamente.'; ?>
            </div>
        <?php endif; ?>
        <?php if(mysqli_num_rows($res_obras) > 0): ?>
            <?php while($obra = mysqli_fetch_assoc($res_obras)): ?>
                <div class="card">
                    <img src="img_obras/<?php echo $obra['imagen']; ?>">
                    <div style="flex:1;">
                        <h3><?php echo htmlspecialchars($obra['titulo']); ?></h3>
                        <p>Artista: <?php echo htmlspecialchars($obra['nombres']." ".$obra['apellidos']); ?></p>
                        <p>Precio: $<?php echo number_format($obra['precio'],0,',','.'); ?></p>
                    </div>
                    <div>
                        <a href="aprobar_obra.php?id=<?php echo $obra['id']; ?>">
                            <button class="aprobar"><i class="bi bi-check-lg"></i> Aprobar</button>
                        </a>
                        <button class="rechazar" onclick="rechazarConMotivo(<?php echo $obra['id']; ?>)">
                            <i class="bi bi-x-lg"></i> Rechazar
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="mensaje">No hay obras pendientes</p>
        <?php endif; ?>
        <script>
        function rechazarConMotivo(id) {
            let motivo = prompt("Escribe la razón del rechazo (ej: Foto de baja calidad o incumplimiento de normas):");
            if (motivo != null && motivo.trim() !== "") {
                window.location.href = "rechazar_obra.php?id=" + id + "&motivo=" + encodeURIComponent(motivo);
            }
        }
        </script>
    </body>
</html>