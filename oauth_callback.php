<?php
require_once __DIR__ . '/includes/init.php';

$name = trim($_GET['name'] ?? '');
$email = trim($_GET['email'] ?? '');

if ($email === '') {
    set_flash('error', 'Thiếu email đăng nhập từ Google/Facebook.');
    redirect_to('login.php');
}

$stmt = $conn->prepare('SELECT id, full_name, username, email, role, status FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    $baseUsername = preg_replace('/[^a-zA-Z0-9]/', '', strstr($email, '@', true) ?: 'user');
    $username = $baseUsername !== '' ? strtolower($baseUsername) : 'user' . rand(1000, 9999);
    $fullName = $name !== '' ? $name : $username;
    $passwordHash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    $role = 'user';
    $status = 1;

    $checkStmt = $conn->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
    $checkStmt->bind_param('s', $username);
    $checkStmt->execute();
    $exists = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();

    if ($exists) {
        $username .= rand(1000, 9999);
    }

    $insertStmt = $conn->prepare('INSERT INTO users (full_name, username, email, password_hash, role, status) VALUES (?, ?, ?, ?, ?, ?)');
    $insertStmt->bind_param('sssssi', $fullName, $username, $email, $passwordHash, $role, $status);
    $insertStmt->execute();
    $insertStmt->close();

    $stmt = $conn->prepare('SELECT id, full_name, username, email, role, status FROM users WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (!$user || (int) $user['status'] !== 1) {
    set_flash('error', 'Tài khoản không khả dụng.');
    redirect_to('login.php');
}

$_SESSION['user'] = [
    'id' => $user['id'],
    'full_name' => $user['full_name'],
    'username' => $user['username'],
    'email' => $user['email'],
    'role' => $user['role'],
];

set_flash('success', 'Đăng nhập bằng Google/Facebook thành công.');
redirect_to($user['role'] === 'admin' ? 'admin/index.php' : 'index.php');