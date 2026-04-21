<?php
require_once '../includes/init.php';

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$sql = "SELECT * FROM orders ORDER BY id DESC";
$result = $conn->query($sql);

$orders = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý đơn hàng</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }

        h1 {
            margin-bottom: 20px;
        }

        .wrap {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background: #222;
            color: #fff;
        }

        .btn {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
            color: #fff;
            font-size: 14px;
        }

        .btn-edit {
            background: #007bff;
        }

        .status {
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 13px;
            display: inline-block;
        }

        .pending { background: #fff3cd; color: #856404; }
        .processing { background: #cce5ff; color: #004085; }
        .shipping { background: #d1ecf1; color: #0c5460; }
        .completed { background: #d4edda; color: #155724; }
        .cancelled { background: #f8d7da; color: #721c24; }

        .paid { background: #d4edda; color: #155724; }
        .unpaid { background: #f8d7da; color: #721c24; }
        .failed { background: #f5c6cb; color: #721c24; }
        .refunded { background: #e2e3e5; color: #383d41; }
    </style>
</head>
<body>
    <div class="wrap">
        <h1>Quản lý đơn hàng</h1>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Khách hàng</th>
                    <th>SĐT</th>
                    <th>Tổng tiền</th>
                    <th>Thanh toán</th>
                    <th>Trạng thái đơn</th>
                    <th>Ngày tạo</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= (int)$order['id'] ?></td>
                            <td><?= htmlspecialchars($order['customer_name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($order['customer_phone'] ?? '') ?></td>
                            <td><?= number_format((float)($order['total_amount'] ?? 0), 0, ',', '.') ?> đ</td>
                            <td>
                                <span class="status <?= htmlspecialchars($order['payment_status'] ?? 'unpaid') ?>">
                                    <?= htmlspecialchars($order['payment_status'] ?? 'unpaid') ?>
                                </span>
                            </td>
                            <td>
                                <span class="status <?= htmlspecialchars($order['order_status'] ?? 'pending') ?>">
                                    <?= htmlspecialchars($order['order_status'] ?? 'pending') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($order['created_at'] ?? '') ?></td>
                            <td>
                                <a class="btn btn-edit" href="order-edit.php?id=<?= (int)$order['id'] ?>">Sửa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">Chưa có đơn hàng nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>