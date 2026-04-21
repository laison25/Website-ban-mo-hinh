<?php
require_once '../includes/init.php';

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die('ID không hợp lệ');
}

$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    die('Không tìm thấy đơn hàng');
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_status = trim($_POST['order_status'] ?? 'pending');
    $payment_status = trim($_POST['payment_status'] ?? 'unpaid');
    $admin_note = trim($_POST['admin_note'] ?? '');

    $valid_order_status = ['pending', 'processing', 'shipping', 'completed', 'cancelled'];
    $valid_payment_status = ['unpaid', 'paid', 'failed', 'refunded'];

    if (!in_array($order_status, $valid_order_status, true)) {
        $error = 'Trạng thái đơn hàng không hợp lệ.';
    } elseif (!in_array($payment_status, $valid_payment_status, true)) {
        $error = 'Trạng thái thanh toán không hợp lệ.';
    } else {
        $stmt = $conn->prepare("UPDATE orders SET order_status = ?, payment_status = ?, admin_note = ? WHERE id = ?");
        $stmt->bind_param("sssi", $order_status, $payment_status, $admin_note, $id);

        if ($stmt->execute()) {
            $success = 'Cập nhật đơn hàng thành công.';
        } else {
            $error = 'Cập nhật thất bại.';
        }

        $stmt->close();

        $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa đơn hàng</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .wrap {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 24px;
            border-radius: 10px;
        }

        h1 {
            margin-top: 0;
        }

        .info {
            background: #fafafa;
            border: 1px solid #ddd;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        select, textarea {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .btn {
            display: inline-block;
            background: #007bff;
            color: #fff;
            text-decoration: none;
            border: none;
            padding: 10px 16px;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn-back {
            background: #6c757d;
        }

        .success {
            color: green;
            margin-bottom: 12px;
        }

        .error {
            color: red;
            margin-bottom: 12px;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <h1>Sửa đơn hàng #<?= (int)$order['id'] ?></h1>

        <div class="info">
            <p><strong>Khách hàng:</strong> <?= htmlspecialchars($order['customer_name'] ?? '') ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($order['customer_email'] ?? '') ?></p>
            <p><strong>SĐT:</strong> <?= htmlspecialchars($order['customer_phone'] ?? '') ?></p>
            <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($order['shipping_address'] ?? '') ?></p>
            <p><strong>Tổng tiền:</strong> <?= number_format((float)($order['total_amount'] ?? 0), 0, ',', '.') ?> đ</p>
        </div>

        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="order_status">Trạng thái đơn hàng</label>
                <select name="order_status" id="order_status">
                    <option value="pending" <?= (($order['order_status'] ?? '') === 'pending') ? 'selected' : '' ?>>pending</option>
                    <option value="processing" <?= (($order['order_status'] ?? '') === 'processing') ? 'selected' : '' ?>>processing</option>
                    <option value="shipping" <?= (($order['order_status'] ?? '') === 'shipping') ? 'selected' : '' ?>>shipping</option>
                    <option value="completed" <?= (($order['order_status'] ?? '') === 'completed') ? 'selected' : '' ?>>completed</option>
                    <option value="cancelled" <?= (($order['order_status'] ?? '') === 'cancelled') ? 'selected' : '' ?>>cancelled</option>
                </select>
            </div>

            <div class="form-group">
                <label for="payment_status">Trạng thái thanh toán</label>
                <select name="payment_status" id="payment_status">
                    <option value="unpaid" <?= (($order['payment_status'] ?? '') === 'unpaid') ? 'selected' : '' ?>>unpaid</option>
                    <option value="paid" <?= (($order['payment_status'] ?? '') === 'paid') ? 'selected' : '' ?>>paid</option>
                    <option value="failed" <?= (($order['payment_status'] ?? '') === 'failed') ? 'selected' : '' ?>>failed</option>
                    <option value="refunded" <?= (($order['payment_status'] ?? '') === 'refunded') ? 'selected' : '' ?>>refunded</option>
                </select>
            </div>

            <div class="form-group">
                <label for="admin_note">Ghi chú admin</label>
                <textarea name="admin_note" id="admin_note"><?= htmlspecialchars($order['admin_note'] ?? '') ?></textarea>
            </div>

            <a href="orders.php" class="btn btn-back">Quay lại</a>
            <button type="submit" class="btn">Lưu thay đổi</button>
        </form>
    </div>
</body>
</html>