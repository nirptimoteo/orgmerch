<?php
require_once 'config.php';

if (isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
} elseif (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
} else {
    echo "Invalid request.";
    exit();
}

$stmt = $con->prepare("SELECT image_url FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    echo "Product not found.";
    exit();
}

if (!empty($product['image_url'])) {
    $imagePath = 'uploads/' . $product['image_url'];
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }
}

$stmt = $con->prepare("DELETE FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
if ($stmt->execute()) {
    header("Location: index.php");
    exit();
} else {
    echo "Error deleting product: " . $stmt->error;
}
?>
