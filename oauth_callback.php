<?php
require_once __DIR__ . '/includes/init.php';

global $conn;

function oauth_http_json(string $url, array $postData = []): ?array {
    if (!function_exists('curl_init')) {
        return null;
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 12);

    if (!empty($postData)) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    }

    $response = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $status < 200 || $status >= 300) {
        return null;
    }

    $json = json_decode($response, true);
    return is_array($json) ? $json : null;
}

$provider = strtolower(trim($_GET['provider'] ?? ($_SESSION['oauth_provider'] ?? 'social')));
$name = trim($_GET['name'] ?? '');
$email = trim($_GET['email'] ?? '');
$code = trim($_GET['code'] ?? '');
$redirectUri = url('oauth_callback.php');

if ($provider === 'google' && defined('GOOGLE_REDIRECT_URI') && GOOGLE_REDIRECT_URI !== '') {
    $redirectUri = GOOGLE_REDIRECT_URI;
}

if ($provider === 'facebook' && defined('FACEBOOK_REDIRECT_URI') && FACEBOOK_REDIRECT_URI !== '') {
    $redirectUri = FACEBOOK_REDIRECT_URI;
}

if ($email === '' && $code !== '') {
    $state = trim($_GET['state'] ?? '');
    if ($state === '' || $state !== ($_SESSION['oauth_state'] ?? '')) {
        set_flash('error', 'Phiên đăng nhập mạng xã hội không hợp lệ.');
        redirect_to('login.php');
    }

    if ($provider === 'google' && GOOGLE_CLIENT_ID !== '' && GOOGLE_CLIENT_SECRET !== '') {
        $token = oauth_http_json('https://oauth2.googleapis.com/token', [
            'client_id' => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirectUri,
        ]);

        if (!empty($token['access_token'])) {
            $profile = oauth_http_json('https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . urlencode($token['access_token']));
            $name = trim($profile['name'] ?? '');
            $email = trim($profile['email'] ?? '');
        }
    }

    if ($provider === 'facebook' && FACEBOOK_APP_ID !== '' && FACEBOOK_APP_SECRET !== '') {
        $token = oauth_http_json('https://graph.facebook.com/v19.0/oauth/access_token?' . http_build_query([
            'client_id' => FACEBOOK_APP_ID,
            'client_secret' => FACEBOOK_APP_SECRET,
            'redirect_uri' => $redirectUri,
            'code' => $code,
        ]));

        if (!empty($token['access_token'])) {
            $profile = oauth_http_json('https://graph.facebook.com/me?' . http_build_query([
                'fields' => 'id,name,email',
                'access_token' => $token['access_token'],
            ]));
            $name = trim($profile['name'] ?? '');
            $email = trim($profile['email'] ?? '');
        }
    }
}

unset($_SESSION['oauth_state'], $_SESSION['oauth_provider']);

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash('error', 'Không lấy được email từ ' . ucfirst($provider) . '. Vui lòng thử lại hoặc đăng nhập bằng tài khoản thường.');
    redirect_to('login.php');
}

$stmt = $conn->prepare('SELECT id, full_name, username, email, role, status FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    $baseUsername = preg_replace('/[^a-zA-Z0-9]/', '', strstr($email, '@', true) ?: 'user');
    $baseUsername = $baseUsername !== '' ? strtolower($baseUsername) : 'user';
    $username = $baseUsername;
    $suffix = 0;

    do {
        $checkStmt = $conn->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
        $checkStmt->bind_param('s', $username);
        $checkStmt->execute();
        $exists = $checkStmt->get_result()->fetch_assoc();
        $checkStmt->close();

        if ($exists) {
            $suffix++;
            $username = $baseUsername . $suffix;
        }
    } while ($exists);

    $fullName = $name !== '' ? $name : $username;
    $passwordHash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    $role = 'customer';
    $status = 1;

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

set_flash('success', 'Đăng nhập bằng ' . ucfirst($provider) . ' thành công.');
redirect_to($user['role'] === 'admin' ? 'admin/index.php' : 'index.php');
