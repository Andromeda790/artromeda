<?php
    session_start();
    include("conexion.php");
    $id_usuarios = $_SESSION['usuario_id'];
    mysqli_query($conexion, "UPDATE notificaciones SET leido = 1 WHERE id_usuarios = '$id_usuarios'");
    $res_notif = mysqli_query($conexion, "SELECT * FROM notificaciones WHERE id_usuarios = '$id_usuarios' ORDER BY fecha DESC");
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Mis Notificaciones - Artrómeda</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <style>
            body { 
                background: #0a0a0a; 
                color: white; 
                font-family: sans-serif; 
                padding: 50px; 
            }

            .notif-card { 
                background: #111; 
                border-left: 4px solid #deb887; 
                padding: 15px; 
                margin-bottom: 10px; 
                border-radius: 0 5px 5px 0;
            }

            .fecha { 
                font-size: 0.7rem; 
                color: #888; 
                margin-top: 5px; 
            }
        </style>
    </head>
    <body>
        <h1><i class="bi bi-bell"></i> Notificaciones</h1>
        <hr style="border-color: #333; margin-bottom: 20px;">
        <?php if(mysqli_num_rows($res_notif) > 0): ?>
            <?php while($n = mysqli_fetch_assoc($res_notif)): ?>
                <div class="notif-card">
                    <p><?php echo htmlspecialchars($n['mensaje']); ?></p>
                    <div class="fecha"><?php echo date('d/m/Y H:i', strtotime($n['fecha'])); ?></div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="color: #666;">No tienes notificaciones nuevas.</p>
        <?php endif; ?>
        <br>
        <a href="perfil_artista.php" style="color: #deb887; text-decoration: none;">&larr; Volver al Panel</a>
    </body>
</html>