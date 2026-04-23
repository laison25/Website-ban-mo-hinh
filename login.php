<?php
require_once __DIR__ . '/includes/init.php';
$pageTitle = 'Đăng nhập - ' . APP_NAME;

if (is_logged_in()) {
    redirect_to(is_admin() ? 'admin/index.php' : 'index.php');
}

$error = '';
$loginValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginValue = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($loginValue === '' || $password === '') {
        $error = 'Vui lòng nhập đầy đủ tài khoản và mật khẩu.';
    } else {
        global $conn;
        $stmt = $conn->prepare('SELECT id, full_name, username, email, password_hash, role, status FROM users WHERE email = ? OR username = ? LIMIT 1');
        $stmt->bind_param('ss', $loginValue, $loginValue);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user || (int) $user['status'] !== 1 || !password_verify($password, $user['password_hash'])) {
            $error = 'Thông tin đăng nhập không đúng.';
        } else {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'full_name' => $user['full_name'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
            ];

            set_flash('success', 'Đăng nhập thành công.');
            redirect_to($user['role'] === 'admin' ? 'admin/index.php' : 'index.php');
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<main class="auth-page">
    <div class="container auth-grid">
        <div class="auth-visual large-visual">
            <img src="<?= url('assets/images/products/login-visual.jpg') ?>" alt="Login visual">
        </div>

        <div class="auth-card">
            <h1>Đăng nhập Lzon Poke</h1>
            <p>Quản lý giỏ hàng, yêu thích và lịch sử đơn hàng của bạn.</p>

            <?php if ($error): ?>
                <div class="flash-message error"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" class="form-card auth-form">
                <div class="form-group">
                    <input type="text" name="login" value="<?= e($loginValue) ?>" placeholder="Email hoặc username">
                </div>

                <div class="form-group">
                    <input type="password" name="password" placeholder="Mật khẩu">
                </div>

                <div class="login-actions-row">
                    <button class="primary-btn" type="submit">Đăng nhập</button>
                    <a class="text-link" href="<?= url('register.php') ?>">Tạo tài khoản</a>
                </div>

                <div class="demo-box">
                    <strong>Demo:</strong> admin / 123456 &nbsp; | &nbsp; user / 123456
                </div>

                <div class="social-login-box">
                    <div class="social-divider"><span>Hoặc đăng nhập nhanh</span></div>
                    <a href="<?= url('social-login.php?provider=google') ?>" class="social-login-btn google">
                        <span>G</span>
                        Đăng nhập bằng Google
                    </a>

                    <a href="<?= url('social-login.php?provider=facebook') ?>" class="social-login-btn facebook">
                        <span>f</span>
                        Đăng nhập bằng Facebook
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
