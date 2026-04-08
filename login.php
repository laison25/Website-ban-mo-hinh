<?php
include __DIR__ . '/includes/header.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if ($email === '' || $password === '') {
        $message = 'Vui lòng nhập đầy đủ email/số điện thoại và mật khẩu.';
    } else {
        $message = 'Đây là giao diện demo. Khi nối database, bạn sẽ xử lý đăng nhập thật ở đây.';
    }
}
?>
<main class="login-page"><div class="container"><div class="login-grid"><div class="login-visual"><img src="assets/images/login-visual.svg" alt="Login visual"></div><div class="login-form-box"><h1>Log in to Exclusive</h1><p>Enter your details below</p><?php if ($message): ?><div class="alert-box"><?php echo htmlspecialchars($message); ?></div><?php endif; ?><form method="post" class="login-form"><input type="text" name="email" placeholder="Email or Phone Number"><input type="password" name="password" placeholder="Password"><div class="login-actions"><button type="submit" class="primary-btn">Log In</button><a href="#" class="text-link">Forgot Password?</a></div></form></div></div></div></main>
<?php include __DIR__ . '/includes/footer.php'; ?>
