<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: index.php');
    exit;
}

if (isset($_GET['mark_paid'])) {
    $order_id = intval($_GET['mark_paid']);
    $result = $con->query("SELECT payment_status FROM orders WHERE id = $order_id");
    $order = $result->fetch_assoc();

    if ($order && $order['payment_status'] !== 'Paid') {
        $con->query("UPDATE orders SET payment_status = 'Paid' WHERE id = $order_id");
    }

    header("Location: view_orders.php");
    exit;
}

if (isset($_GET['delete']) && isset($_GET['status'])) {
    $order_id = intval($_GET['delete']);
    if ($_GET['status'] === 'Paid') {
        $con->query("DELETE FROM orders WHERE id = $order_id");
    }

    header("Location: view_orders.php");
    exit;
}

$orders_result = $con->query("SELECT * FROM orders ORDER BY order_date DESC");
$orders = [];
if ($orders_result && $orders_result->num_rows > 0) {
    while ($row = $orders_result->fetch_assoc()) {
        $order_id = $row['id'];
        $items_result = $con->query("SELECT * FROM order_items WHERE order_id = $order_id");
        $items = [];
        if ($items_result && $items_result->num_rows > 0) {
            while ($item = $items_result->fetch_assoc()) {
                $items[] = $item;
            }
        }
        $row['items'] = $items;
        $orders[] = $row;
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

    <style>
        body {
            background-color: #000;
            color: #fff;
            padding: 1.5rem 0;
            font-family: "Poppins", sans-serif;
        }
        .card {
            background-color: #ffff;
            border: 1px solid #333;
            border-radius: 10px;
        }
        .card-title {
            color: #ffc107;
        }
        .btn-sm {
            margin: 0.2rem;
        }
        .collapse-inner {
            background: #fff;
            color: #000;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
        }
        .item-row {
            border-bottom: 1px solid #ccc;
            padding: 6px 0;
        }
        .view-btn {
            color: #000;
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 6px 14px;
            border-radius: 8px;
            display: inline-block;
            font-weight: 600;
            transition: 0.2s ease-in-out;
        }
        .view-btn:hover {
            background-color: #f8f9fa;
            transform: scale(1.05);
        }
        .back-btn {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background-color: #fff;
            color: #000;
            border: 1px solid #ddd;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .back-btn:hover {
            background-color: #f0f0f0;
        }
        @media (max-width: 768px) {
            .card {
                margin-bottom: 1rem;
            }
            .card h5 {
                font-size: 1rem;
            }
            .collapse-inner {
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>

<div class="container">
    <div class="card mb-4">
        <div class="card-header bg-dark text-white text-center">
            <h4 class="mb-0">🧾 Customer Orders</h4>
        </div>
    </div>

    <?php if (!empty($orders)): ?>
        <div class="row g-4" id="globalOrderAccordion">
            <?php foreach ($orders as $row): ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm p-3">
                        <h5 class="text-danger"><?= htmlspecialchars($row['name']) ?></h5>
                        <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($row['email']) ?></p>
                        <p class="mb-1"><strong>Phone:</strong> <?= htmlspecialchars($row['phone']) ?></p>
                        <p class="mb-1"><strong>Address:</strong> <?= htmlspecialchars($row['address']) ?></p>
                        <p class="mb-1"><strong>Total Amount:</strong> ₱<?= number_format($row['total_amount'], 2) ?></p>
                        <p class="mb-1"><strong>Payment Method:</strong> <?= htmlspecialchars($row['payment_method']) ?></p>
                        <p class="mb-1"><strong>Status:</strong> 
                            <span class="badge bg-<?= $row['payment_status'] === 'Paid' ? 'success' : 'warning' ?>">
                                <?= $row['payment_status'] ?>
                            </span>
                        </p>
                        <p class="mb-2"><strong>Date:</strong> <?= htmlspecialchars($row['order_date']) ?></p>

                        <div class="d-flex flex-wrap justify-content-start">
                            <?php if ($row['payment_status'] !== 'Paid'): ?>
                                <a href="?mark_paid=<?= $row['id'] ?>" class="btn btn-sm btn-success">Mark Paid</a>
                            <?php else: ?>
                                <button class="btn btn-sm btn-secondary" disabled>Paid</button>
                            <?php endif; ?>

                            <?php if ($row['payment_status'] === 'Paid'): ?>
                                <a href="?delete=<?= $row['id'] ?>&status=Paid" class="btn btn-sm btn-danger" onclick="return confirm('Delete this paid order?')">Delete</a>
                            <?php else: ?>
                                <button class="btn btn-sm btn-secondary" disabled>Pending</button>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($row['items'])): ?>
                            <div class="text-center mt-3 mb-2">
                                <a class="view-btn text-decoration-none"
                                    data-bs-toggle="collapse"
                                    href="#collapse<?= $row['id'] ?>"
                                    role="button"
                                    aria-expanded="false"
                                    aria-controls="collapse<?= $row['id'] ?>">
                                    View Items
                                </a>
                            </div>

                            <div id="collapse<?= $row['id'] ?>"
                                class="collapse"
                                data-bs-parent="#globalOrderAccordion">
                                <div class="collapse-inner">
                                    <?php foreach ($row['items'] as $item): ?>
                                        <div class="item-row mb-2">
                                            <strong><?= htmlspecialchars($item['product_name']) ?></strong><br>
                                            Quantity: <?= htmlspecialchars($item['quantity']) ?> × ₱<?= number_format($item['price'], 2) ?><br>
                                            <span class="text-muted small">Subtotal: ₱<?= number_format($item['quantity'] * $item['price'], 2) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="mt-3 text-muted text-center"><em>No items found</em></p>
                        <?php endif; ?>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center text-white mt-4">No orders found.</div>
    <?php endif; ?>
    
</div>

<a href="index.php" class="btn btn-light px-4 py-2 border back-btn">← Back</a>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
