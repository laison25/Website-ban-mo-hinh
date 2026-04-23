<?php
require_once __DIR__ . '/includes/init.php';

$provider = strtolower(trim($_GET['provider'] ?? ''));
$providers = [
    'google' => [
        'name' => 'Google',
        'demo_name' => 'Google Demo User',
        'demo_email' => 'google.demo@lzonpoke.local',
        'auth_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
        'client_id' => GOOGLE_CLIENT_ID,
        'scope' => 'openid email profile',
    ],
    'facebook' => [
        'name' => 'Facebook',
        'demo_name' => 'Facebook Demo User',
        'demo_email' => 'facebook.demo@lzonpoke.local',
        'auth_url' => 'https://www.facebook.com/v19.0/dialog/oauth',
        'client_id' => FACEBOOK_APP_ID,
        'scope' => 'email,public_profile',
    ],
];

if (!isset($providers[$provider])) {
    set_flash('error', 'Nhà cung cấp đăng nhập không hợp lệ.');
    redirect_to('login.php');
}

$providerConfig = $providers[$provider];

if (SOCIAL_LOGIN_DEMO_MODE || $providerConfig['client_id'] === '') {
    redirect_to('oauth_callback.php?provider=' . urlencode($provider)
        . '&name=' . urlencode($providerConfig['demo_name'])
        . '&email=' . urlencode($providerConfig['demo_email']));
}

$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;
$_SESSION['oauth_provider'] = $provider;

$query = http_build_query([
    'client_id' => $providerConfig['client_id'],
    'redirect_uri' => url('oauth_callback.php'),
    'response_type' => 'code',
    'scope' => $providerConfig['scope'],
    'state' => $state,
]);

header('Location: ' . $providerConfig['auth_url'] . '?' . $query);
exit;
