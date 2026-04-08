<?php
require_once __DIR__ . '/includes/init.php';
$pageTitle = 'Đăng ký - ' . APP_NAME;
if (is_logged_in()) {
    redirect_to(is_admin() ? 'admin/index.php' : 'index.php');
}

$form = [
    'full_name' => '',
    'username' => '',
    'email' => '',
];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['full_name'] = trim($_POST['full_name'] ?? '');
    $form['username'] = trim($_POST['username'] ?? '');
    $form['email'] = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($form['full_name'] === '' || $form['username'] === '' || $form['email'] === '' || $password === '' || $confirmPassword === '') {
        $error = 'Vui lòng nhập đầy đủ thông tin đăng ký.';
    } elseif (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không đúng định dạng.';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải từ 6 ký tự trở lên.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Mật khẩu xác nhận không khớp.';
    } else {
        $stmt = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
        $stmt->bind_param('ss', $form['username'], $form['email']);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($exists) {
            $error = 'Username hoặc email đã tồn tại.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'customer';
            $status = 1;
            $stmt = $conn->prepare('INSERT INTO users (full_name, username, email, password_hash, role, status) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('sssssi', $form['full_name'], $form['username'], $form['email'], $passwordHash, $role, $status);
            $stmt->execute();
            $userId = $stmt->insert_id;
            $stmt->close();

            $_SESSION['user'] = [
                'id' => $userId,
                'full_name' => $form['full_name'],
                'username' => $form['username'],
                'email' => $form['email'],
                'role' => 'customer',
            ];
            set_flash('success', 'Đăng ký thành công. Chào mừng bạn đến với Lzon Poke.');
            redirect_to('index.php');
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<main class="auth-page">
    <div class="container auth-grid register-grid">
        <div class="auth-visual">
            <img src="<?= url('assets/images/hero/hero-side-1.svg') ?>" alt="Register visual">
        </div>
        <div class="auth-card wide-auth">
            <h1>Create an account</h1>
            <p>Enter your details below</p>
            <?php if ($error): ?><div class="flash-message error"><?= e($error) ?></div><?php endif; ?>
            <form method="post" class="form-card auth-form">
                <div class="form-group"><input type="text" name="full_name" value="<?= e($form['full_name']) ?>" placeholder="Full Name"></div>
                <div class="form-group"><input type="text" name="username" value="<?= e($form['username']) ?>" placeholder="Username"></div>
                <div class="form-group"><input type="email" name="email" value="<?= e($form['email']) ?>" placeholder="Email"></div>
                <div class="form-grid-2">
                    <div class="form-group"><input type="password" name="password" placeholder="Password"></div>
                    <div class="form-group"><input type="password" name="confirm_password" placeholder="Confirm Password"></div>
                </div>
                <button class="primary-btn" type="submit">Create Account</button>
                <div class="subtext-row">Already have account? <a class="text-link" href="<?= url('login.php') ?>">Log in</a></div>
            </form>
        </div>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
