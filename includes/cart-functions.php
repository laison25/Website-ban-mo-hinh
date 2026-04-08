<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function get_cart_count(): int
{
    return array_sum($_SESSION['cart']);
}

function add_to_cart(int $productId, int $quantity = 1): void
{
    if ($productId <= 0 || $quantity <= 0) {
        return;
    }

    if (!isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] = 0;
    }

    $_SESSION['cart'][$productId] += $quantity;
}

function update_cart_quantity(int $productId, int $quantity): void
{
    if ($productId <= 0) {
        return;
    }

    if ($quantity <= 0) {
        unset($_SESSION['cart'][$productId]);
        return;
    }

    $_SESSION['cart'][$productId] = $quantity;
}

function remove_from_cart(int $productId): void
{
    unset($_SESSION['cart'][$productId]);
}

function clear_cart(): void
{
    $_SESSION['cart'] = [];
}

function get_cart_items(array $products): array
{
    $items = [];

    foreach ($_SESSION['cart'] as $productId => $quantity) {
        $productId = (int) $productId;
        $quantity = (int) $quantity;

        if ($quantity <= 0 || !isset($products[$productId])) {
            continue;
        }

        $product = $products[$productId];
        $items[] = [
            'product' => $product,
            'quantity' => $quantity,
            'line_total' => $product['price'] * $quantity,
        ];
    }

    return $items;
}
