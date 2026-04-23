<?php
require_once __DIR__ . '/includes/init.php';
require_login();

global $conn;
$pageTitle = 'Cài đặt tài khoản - ' . APP_NAME;
$account = get_current_user_record($conn);

if (!$account || (int) $account['status'] !== 1) {
    set_flash('error', 'Tài khoản không còn khả dụng.');
    redirect_to('logout.php');
}

$accountId = (int) $account['id'];
$form = [
    'full_name' => $account['full_name'],
    'email' => $account['email'],
];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $conn;
    $form['full_name'] = trim($_POST['full_name'] ?? '');
    $form['email'] = trim($_POST['email'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $wantsPasswordChange = $currentPassword !== '' || $newPassword !== '' || $confirmPassword !== '';

    if ($form['full_name'] === '' || $form['email'] === '') {
        $error = 'Vui lòng nhập đầy đủ họ tên và email.';
    } elseif (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không đúng định dạng.';
    } elseif ($wantsPasswordChange && !password_verify($currentPassword, $account['password_hash'])) {
        $error = 'Mật khẩu hiện tại không đúng.';
    } elseif ($wantsPasswordChange && strlen($newPassword) < 6) {
        $error = 'Mật khẩu mới phải từ 6 ký tự trở lên.';
    } elseif ($wantsPasswordChange && $newPassword !== $confirmPassword) {
        $error = 'Mật khẩu xác nhận không khớp.';
    } else {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
        $stmt->bind_param('si', $form['email'], $accountId);
        $stmt->execute();
        $emailExists = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($emailExists) {
            $error = 'Email này đã được tài khoản khác sử dụng.';
        } else {
            if ($wantsPasswordChange) {
                $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('UPDATE users SET full_name = ?, email = ?, password_hash = ? WHERE id = ?');
                $stmt->bind_param('sssi', $form['full_name'], $form['email'], $passwordHash, $accountId);
            } else {
                $stmt = $conn->prepare('UPDATE users SET full_name = ?, email = ? WHERE id = ?');
                $stmt->bind_param('ssi', $form['full_name'], $form['email'], $accountId);
            }

            $stmt->execute();
            $stmt->close();

            $_SESSION['user']['full_name'] = $form['full_name'];
            $_SESSION['user']['email'] = $form['email'];
            $account = get_current_user_record($conn);
            $success = 'Cập nhật tài khoản thành công.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<main class="section-space account-page">
    <div class="container account-layout">
        <aside class="account-summary">
            <span class="account-avatar"><?= e(strtoupper(substr($account['full_name'], 0, 1))) ?></span>
            <h1><?= e($account['full_name']) ?></h1>
            <p><?= e($account['email']) ?></p>
            <div class="account-summary-list">
                <div><strong>Tên đăng nhập</strong><span><?= e($account['username']) ?></span></div>
                <div><strong>Vai trò</strong><span><?= is_admin() ? 'Quản trị viên' : 'Khách hàng' ?></span></div>
                <div><strong>Ngày tham gia</strong><span><?= e(format_datetime($account['created_at'])) ?></span></div>
            </div>
        </aside>

        <section class="account-panel">
            <div class="section-head compact-head">
                <div>
                    <div class="section-label">Tài khoản</div>
                    <h2>Cài đặt cá nhân</h2>
                    <p class="muted">Quản lý thông tin hiển thị, email nhận đơn và mật khẩu đăng nhập.</p>
                </div>
            </div>

            <?php if ($error): ?><div class="flash-message error"><?= e($error) ?></div><?php endif; ?>
            <?php if ($success): ?><div class="flash-message success"><?= e($success) ?></div><?php endif; ?>

            <form method="post" class="form-card account-form">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label>Họ và tên</label>
                        <input type="text" name="full_name" value="<?= e($form['full_name']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= e($form['email']) ?>">
                    </div>
                </div>

                <div class="form-divider"></div>
                <h3>Đổi mật khẩu</h3>
                <p class="muted">Bỏ trống nhóm này nếu bạn chỉ muốn cập nhật thông tin cá nhân.</p>

                <div class="form-grid-3">
                    <div class="form-group">
                        <label>Mật khẩu hiện tại</label>
                        <input type="password" name="current_password" autocomplete="current-password">
                    </div>
                    <div class="form-group">
                        <label>Mật khẩu mới</label>
                        <input type="password" name="new_password" autocomplete="new-password">
                    </div>
                    <div class="form-group">
                        <label>Xác nhận mật khẩu</label>
                        <input type="password" name="confirm_password" autocomplete="new-password">
                    </div>
                </div>

                <div class="account-actions">
                    <button class="primary-btn" type="submit">Lưu thay đổi</button>
                    <a class="outline-btn" href="<?= url('order-history.php') ?>">Xem đơn hàng</a>
                </div>
            </form>
        </section>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
