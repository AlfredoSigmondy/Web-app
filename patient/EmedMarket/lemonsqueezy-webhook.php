<?php
// lemonsqueezy-webhook.php

// 1. Connect to your database
include_once __DIR__ . '../../../database/conection_db.php'; // adjust if needed

// 2. Get the JSON payload from Lemon Squeezy
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// 3. Optional: log raw input for debugging
file_put_contents('webhook_log.txt', $input . PHP_EOL, FILE_APPEND);

// 4. Check the event type
$eventType = $data['meta']['event_name'] ?? '';

if ($eventType === 'order_created') {
    $order = $data['data']['attributes'];

    $buyer_name = $order['user_name'] ?? 'Anonymous';
    $email = $order['user_email'] ?? '';
    $checkout_id = $order['checkout_id'] ?? '';
    $total_price = $order['total'] ?? 0;
    $currency = $order['currency'] ?? 'PHP';

    // Get product name (from relationships)
    $product_id = $data['data']['relationships']['products']['data'][0]['id'] ?? null;

    // 5. Insert into your orders table
    $stmt = $conn->prepare("INSERT INTO orders (buyer_name, email, checkout_id, total_price, currency, lemon_product_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdsd", $buyer_name, $email, $checkout_id, $total_price, $currency, $product_id);
    $stmt->execute();
    $stmt->close();

    // Respond to Lemon Squeezy
    http_response_code(200);
    echo json_encode(['message' => 'Order stored successfully.']);
} else {
    http_response_code(200);
    echo json_encode(['message' => 'Event ignored.']);
}
?>
