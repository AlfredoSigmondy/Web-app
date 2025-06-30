<?php
// lemonsqueezy-webhook.php
include_once __DIR__ . '../../../database/conection_db.php'; // Adjust path as needed

// Get raw POST data
$payload = file_get_contents("php://input");
$eventData = json_decode($payload, true);

if (!$eventData || !isset($eventData['meta']['event_name'])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid webhook payload"]);
    exit;
}

$event = $eventData['meta']['event_name'];

switch ($event) {
    case 'order_created':
        $order = $eventData['data']['attributes'];

        $orderId = $eventData['data']['id'];
        $productId = $order['first_order_item']['product_id'];
        $buyerEmail = $order['user_email'];
        $buyerName = $order['user_name'];
        $totalAmount = $order['total'];
        $currency = $order['currency'];
        $status = 'paid';
        $createdAt = $order['created_at'];

        // Insert into orders table (you must create this table if not yet)
        $stmt = $conn->prepare("INSERT INTO orders (order_id, product_id, buyer_name, buyer_email, total_amount, currency, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissssss", $orderId, $productId, $buyerName, $buyerEmail, $totalAmount, $currency, $status, $createdAt);
        $stmt->execute();

        http_response_code(200);
        echo json_encode(["message" => "Order saved"]);
        break;

    case 'order_refunded':
        $order = $eventData['data']['attributes'];
        $orderId = $eventData['data']['id'];

        // Update order status to 'refunded'
        $stmt = $conn->prepare("UPDATE orders SET status = 'refunded' WHERE order_id = ?");
        $stmt->bind_param("s", $orderId);
        $stmt->execute();

        http_response_code(200);
        echo json_encode(["message" => "Order refunded"]);
        break;

    default:
        http_response_code(200);
        echo json_encode(["message" => "Event received but not processed"]);
        break;
}
?>
