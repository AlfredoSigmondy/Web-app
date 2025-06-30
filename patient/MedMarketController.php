<?php
include_once __DIR__ . '/../database/conection_db.php';

function getCategories($conn) {
    $categories = [];
    $cat_result = $conn->query("SELECT id, name FROM categories");
    while ($row = $cat_result->fetch_assoc()) {
        $categories[] = $row;
    }
    return $categories;
}
function getMedicines($conn) {
    $medicines = [];
    $sql = "SELECT m.id, m.name, m.price, m.quantity, m.expiry, m.status, m.image_path, m.grams, m.lemonsqueezy_checkout_id, c.name AS category 
            FROM medicines m 
            LEFT JOIN categories c ON m.category_id = c.id
            WHERE m.status = 'available'";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $medicines[] = $row;
    }
    return $medicines;
}
?>