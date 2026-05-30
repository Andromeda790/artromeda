<?php
    session_start();
    include("conexion.php");
    $id_usuarios = $_SESSION['usuario_id'] ?? null;
    if (!$id_usuarios) { header("Location: login.php"); exit(); }
    $metodo = $_POST['metodo_pago'];
    $direccion = mysqli_real_escape_string($conexion, $_POST['direccion']);
    $ids = array_keys($_SESSION['carrito']);
    $lista_ids = implode(',', $ids);
    $res_precios = mysqli_query($conexion, "SELECT id, precio FROM obras WHERE id IN ($lista_ids)");
    $total_compra = 0;
    $items = [];
    while ($row = mysqli_fetch_assoc($res_precios)) {
        $total_compra += $row['precio'];
        $items[] = $row;
    }
    $fecha = date("Y-m-d H:i:s");
    $sql_pedido = "INSERT INTO pedidos (precio_final, fecha_pedido, estado, id_usuarios) 
                VALUES ('$total_compra', '$fecha', 'pagado', '$id_usuarios')";
    if (mysqli_query($conexion, $sql_pedido)) {
        $id_nuevo_pedido = mysqli_insert_id($conexion);
        foreach ($items as $item) {
            $id_o = $item['id'];
            $pre = $item['precio'];
            mysqli_query($conexion, "INSERT INTO detalles_pedidos (precio_unitario, cantidad, id_pedidos, id_obras) 
            VALUES ('$pre', 1, '$id_nuevo_pedido', '$id_o')");
        }
        unset($_SESSION['carrito']);
        header("Location: confirmacion_pago.php?id_pedidos=" . $id_nuevo_pedido);
        exit();
    }
?>