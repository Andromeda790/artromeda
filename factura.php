<?php
    session_start();
    require('fpdf/fpdf.php');
    include("conexion.php");
    function txt($texto) {
        return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
    }
    $id_usuarios = $_SESSION['usuario_id'] ?? null;
    $id_pedido = $_GET['id'] ?? null;
    if (!$id_usuarios || !$id_pedido) {
        die("Acceso denegado o pedido no encontrado.");
    }
    $query = "SELECT p.*, u.nombres, u.email, u.direccion, u.telefono 
              FROM pedidos p 
              JOIN usuarios u ON p.id_usuarios = u.id 
              WHERE p.id = '$id_pedido' AND p.id_usuarios = '$id_usuarios'";
    $res = mysqli_query($conexion, $query);
    $pedido = mysqli_fetch_assoc($res);
    if (!$pedido) die("Pedido no válido.");
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetMargins(20, 20, 20);
    if (file_exists('Logo.png')) {
        $pdf->Image('Logo.png', 150, 15, 40); 
    }
    $pdf->SetY(20);
    $pdf->SetFont('Arial', 'B', 22);
    $pdf->SetTextColor(40, 40, 40);
    $pdf->Cell(0, 15, txt('FACTURA DE VENTA'), 0, 1, 'L');
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 6, txt('N.º ') . str_pad($pedido['id'], 6, "0", STR_PAD_LEFT), 0, 1, 'L');
    $pdf->Ln(10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(90, 7, txt('VENDIDO A:'), 0, 0);
    $pdf->Cell(0, 7, txt('DETALLES DE FECHA:'), 0, 1);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(90, 6, txt($pedido['nombres']), 0, 0);
    $pdf->Cell(0, 6, txt('Fecha de emisión: ') . date('d/m/Y', strtotime($pedido['fecha_pedido'])), 0, 1);
    $pdf->Cell(90, 6, 'Email: ' . $pedido['email'], 0, 0);
    $pdf->Cell(0, 6, txt('Método: Pago Electrónico'), 0, 1);
    $pdf->Cell(90, 6, txt('Dirección: ') . txt($pedido['direccion'] ?? 'N/A'), 0, 1);
    $pdf->Ln(12);
    $pdf->SetFillColor(245, 245, 245);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(100, 10, txt(' DESCRIPCIÓN DE LA OBRA'), 0, 0, 'L', true);
    $pdf->Cell(30, 10, 'CANTIDAD', 0, 0, 'C', true);
    $pdf->Cell(40, 10, 'SUBTOTAL ', 0, 1, 'R', true);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Ln(2);
    $detalles = mysqli_query($conexion, "SELECT dp.*, o.titulo 
                                        FROM detalles_pedidos dp 
                                        JOIN obras o ON dp.id_obras = o.id 
                                        WHERE dp.id_pedidos = '$id_pedido'");
    while($item = mysqli_fetch_assoc($detalles)) {
        $pdf->Cell(100, 10, txt('  ' . $item['titulo']), 'B', 0, 'L');
        $pdf->Cell(30, 10, $item['cantidad'], 'B', 0, 'C');
        $pdf->Cell(40, 10, '$' . number_format($item['precio_unitario'], 0, ',', '.') . '  ', 'B', 1, 'R');
    }
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(130, 12, 'TOTAL NETO A PAGAR: ', 0, 0, 'R');
    $pdf->SetTextColor(184, 134, 11); 
    $pdf->Cell(40, 12, '$' . number_format($pedido['precio_final'], 0, ',', '.') . ' COP', 0, 1, 'R');
    $pdf->SetY(-50);
    $pdf->SetTextColor(120, 120, 120);
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->Cell(0, 5, txt('Gracias por confiar en Artrómeda para expandir tu colección personal.'), 0, 1, 'C');
    $pdf->Cell(0, 5, txt('Este documento es un soporte válido de adquisición de derechos digitales.'), 0, 1, 'C');
    $pdf->SetDrawColor(222, 184, 135);
    $pdf->Line(70, $pdf->GetY() + 5, 140, $pdf->GetY() + 5);
    $pdf->SetY(-35);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 10, txt('ARTRÓMEDA - GALERÍA DIGITAL'), 0, 0, 'C');
    $pdf->Output('I', 'Factura_Artromeda_' . $id_pedido . '.pdf');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Adquisición<?php echo $id_pedido; ?></title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            color: #333;
            padding: 40px;
            background: #f9f9f9;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }

        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #deb887;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #000;
            letter-spacing: 2px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .info-table th {
            background: #f2f2f2;
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .info-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .total {
            text-align: right;
            font-size: 20px;
            margin-top: 20px;
            font-weight: bold;
            color: #deb887;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #999;
            line-height: 1.5;
        }

        .btn-print {
            padding: 10px 20px;
            cursor: pointer;
            background: #deb887;
            border: none;
            font-weight: bold;
            border-radius: 5px;
            transition: 0.3s;
        }

        .btn-print:hover {
            background: #c5a076;
        }

        @media print {
            .btn-print {
                display: none;
            }
            body {
                background: none;
                padding: 0;
            }
            .invoice-box {
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>
    <body>
        <div class="invoice-box">
            <div class="header">
                <div class="logo">ARTRÓMEDA</div>
                <div>
                    <strong>Factura #<?php echo $id_pedido; ?></strong><br>
                    Fecha: <?php echo date('d/m/Y', strtotime($datos_p['fecha_pedido'])); ?>
                </div>
            </div>
            <div style="margin-bottom: 30px;">
                <p><strong>Cliente:</strong> <?php echo htmlspecialchars($datos_p['nombre']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($datos_p['email']); ?></p>
            </div>
            <table class="info-table">
                <thead>
                    <tr>
                        <th>Obra Adquirida</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($item = mysqli_fetch_assoc($res_items)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['titulo']); ?></td>
                        <td>1</td>
                        <td>$<?php echo number_format($item['precio_unitario'], 0, ',', '.'); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <div class="total">
                TOTAL: $<?php echo number_format($datos_p['precio_final'], 0, ',', '.'); ?> COP
            </div>
            <div class="footer">
                Este es un comprobante oficial de Artrómeda Galería Digital.
                <br>
                Gracias por apoyar el arte independiente y formar parte de nuestra colección.
            </div>
            <div style="margin-top: 30px; text-align: center;">
                <button onclick="window.print()" class="btn-print">
                    Imprimir / Guardar PDF
                </button>
            </div>
        </div>
    </body>
</html>