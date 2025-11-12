<?php
require_once 'config.php';
session_start();

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : null;
$status = $_GET['status'] ?? null;

if (!$order_id) {
    die('<div class="flex h-screen items-center justify-center bg-gray-100">
            <div class="text-center">
                <h2 class="text-2xl font-semibold text-red-600 mb-3">⚠️ Missing Order ID</h2>
                <a href="index.php" class="text-blue-600 hover:underline">Return to Store</a>
            </div>
         </div>');
}

if ($status === 'success') {
    $stmt = $con->prepare("UPDATE orders SET payment_status = 'Paid' WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    $title = "✅ Payment Successful!";
    $message = "Thank you for your payment. Your order has been confirmed.";
    $color = "green";
} else {
    $stmt = $con->prepare("UPDATE orders SET payment_status = 'Failed' WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    $title = "❌ Payment Failed or Cancelled";
    $message = "Your payment was not completed. Please try again or use another method.";
    $color = "red";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Payment Status</title>
  <link rel="icon" href="assets/favicon.png" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100 font-sans">

  <div class="bg-white shadow-lg rounded-2xl p-8 w-11/12 max-w-md text-center">
    <div class="mb-4">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mx-auto 
        <?php echo $color === 'green' ? 'text-green-500' : 'text-red-500'; ?>" 
        fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <?php if ($color === 'green'): ?>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
          d="M5 13l4 4L19 7" />
        <?php else: ?>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
          d="M6 18L18 6M6 6l12 12" />
        <?php endif; ?>
      </svg>
    </div>

    <h2 class="text-2xl font-bold text-gray-800 mb-2"><?php echo $title; ?></h2>
    <p class="text-gray-600 mb-6"><?php echo $message; ?></p>

    <a href="index.php" 
       class="inline-block px-6 py-2 bg-<?php echo $color; ?>-500 text-white rounded-lg hover:bg-<?php echo $color; ?>-600 transition">
       Return to Store
    </a>
  </div>

</body>
</html>
