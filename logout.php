<?php
require_once __DIR__ . '/includes/init.php';
unset($_SESSION['user']);
unset($_SESSION['cart']);
set_flash('success', 'Bạn đã đăng xuất.');
redirect_to('login.php');
