<?php
session_start();
require_once 'config.php';
require_once 'gateway_payment.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-6.10.0/src/PHPMailer.php';
require 'PHPMailer-6.10.0/src/SMTP.php';
require 'PHPMailer-6.10.0/src/Exception.php';

$message = "";
$cart = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['order_data'])) {
        $cart = json_decode($_POST['order_data'], true);
        if (!is_array($cart) || count($cart) === 0) {
            $message = "Your cart is empty.";
        }
    } else {
        $message = "Your cart is empty.";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['order_data']) && !isset($_POST['name'])) {
    $cart = json_decode($_POST['order_data'], true);
    if (!is_array($cart) || count($cart) === 0) {
        $message = "Your cart is empty.";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && empty($message) && isset($_POST['name'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $phone = htmlspecialchars($_POST['phone']);
    $address = htmlspecialchars($_POST['address']);
    $payment_method = htmlspecialchars($_POST['payment_method']);
    $payment_status = 'Pending';

    $total = 0.0;
    foreach ($cart as $id => $item) {
        $total += floatval($item['price']) * intval($item['qty']);
    }

    $stmt = $con->prepare("INSERT INTO orders (name, email, phone, address, total_amount, payment_method, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $con->error);
    }
    $stmt->bind_param("ssssdss", $name, $email, $phone, $address, $total, $payment_method, $payment_status);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    $item_stmt = $con->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)");
    foreach ($cart as $product_id => $item) {
        $product_name = htmlspecialchars($item['name']); 
        $qty = intval($item['qty']);
        $price = floatval($item['price']);
        $item_stmt->bind_param("iisid", $order_id, $product_id, $product_name, $qty, $price);
        $item_stmt->execute();

        $con->query("UPDATE products SET stock = stock - {$qty} WHERE id = {$product_id}");
    }
    $item_stmt->close();

    function send_admin_email($name, $email, $phone, $address, $payment_method, $total, $cart) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = EMAIL_USER;
            $mail->Password = EMAIL_PASS;
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            $mail->setFrom(EMAIL_USER, 'OrgMerch Order');
            $mail->addAddress(EMAIL_USER, 'Admin');
            $mail->isHTML(true);
            $mail->Subject = 'New Order Received (' . ucfirst($payment_method) . ')';

            $mailBody = "
                <h2>New Order Received</h2>
                <p><strong>Name:</strong> $name</p>
                <p><strong>Email:</strong> $email</p>
                <p><strong>Phone:</strong> $phone</p>
                <p><strong>Address:</strong> $address</p>
                <p><strong>Payment Method:</strong> $payment_method</p>
                <p><strong>Total Amount:</strong> ₱" . number_format($total, 2) . "</p>
                <h4>Ordered Items:</h4>
                <ul>";
            foreach ($cart as $item) {
                $mailBody .= "<li>" . htmlspecialchars($item['name']) . " (x" . intval($item['qty']) . ") — ₱" . number_format($item['price'], 2) . "</li>";
            }
            $mailBody .= "</ul>";

            $mail->Body = $mailBody;
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email send failed: {$mail->ErrorInfo}");
            return false;
        }
    }

    if (strtolower($payment_method) === 'gcash') {
        send_admin_email($name, $email, $phone, $address, $payment_method, $total, $cart);

        $metadata = [
            'customer_name' => $name,
            'customer_email' => $email
        ];

        $checkoutUrl = create_gateway_checkout($order_id, $total, 'PHP', $metadata);
        if ($checkoutUrl) {
            header("Location: " . $checkoutUrl);
            exit;
        } else {
            $message = "❌ Failed to initialize GCash payment. Please try again or choose another method.";
        }
    } else {
        send_admin_email($name, $email, $phone, $address, $payment_method, $total, $cart);
        $message = "✅ Order placed successfully! Please pay in cash upon pickup/delivery.";
        header("Location: index.php?success=cash");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>OrgMerch</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="assets/favicon.png" type="image/png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light py-4">
<div class="container">
  <div class="card mx-auto shadow-lg" style="max-width:720px;">
    <div class="card-header bg-black text-warning text-center">
      <h4 class="mb-0">🛒 Checkout</h4>
    </div>
    <div class="card-body">
      <?php if ($message): ?>
        <div class="alert alert-info text-center"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>

      <?php if (!empty($cart)): ?>
        <form method="POST">
          <input type="hidden" name="order_data" value='<?= htmlspecialchars(json_encode($cart)) ?>'>

          <div class="mb-3">
            <label class="form-label fw-semibold">Full Name</label>
            <input type="text" name="name" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" name="email" class="form-control">
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Phone</label>
            <input type="text" name="phone" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Address</label>
            <textarea name="address" class="form-control" required></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Payment Method</label>
            <select name="payment_method" class="form-select" required>
              <option value="">-- Select --</option>
              <option value="Cash">Cash</option>
              <option value="Gcash">Gcash</option>
            </select>
          </div>

          <hr>
          <h5 class="text-center text-warning">Order Summary</h5>
          <ul class="list-group mb-3">
            <?php foreach ($cart as $item): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <?= htmlspecialchars($item['name']) ?> (x<?= $item['qty'] ?>)
                <span>₱<?= number_format($item['price'] * $item['qty'], 2) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>

          <div class="d-flex justify-content-between">
            <a href="index.php" class="btn btn-primary">← Back</a>
            <button type="submit" class="btn btn-warning">✅ Confirm Order</button>
          </div>
        </form>
      <?php else: ?>
        <p class="text-center">Your cart is empty.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
