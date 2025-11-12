<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

if (isset($_POST['submit'])) {
    $product_name = mysqli_real_escape_string($con, $_POST['product_name']);
    $price = floatval($_POST['price']);
    $category = mysqli_real_escape_string($con, $_POST['category']);
    $stock = intval($_POST['stock']);
    $description = mysqli_real_escape_string($con, $_POST['description']);

    $image_url = $_FILES['image_url']['name'];
    $tmp_name = $_FILES['image_url']['tmp_name'];
    $target_dir = "uploads/" . basename($image_url);
    move_uploaded_file($tmp_name, $target_dir);

    $sql = "INSERT INTO products (product_name, price, category, stock, description, image_url)
            VALUES ('$product_name', '$price', '$category', '$stock', '$description', '$image_url')";

    if (mysqli_query($con, $sql)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($con);
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
<body class="bg-dark text-light">

<div class="container py-4">
    <div class="card shadow mx-auto" style="max-width: 720px;">
        <div class="card-header bg-primary text-white text-center p-3">
            <h4 class="mb-0">Add New Product</h4>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="product_name" class="form-label">Product Name</label>
                    <input type="text" name="product_name" id="product_name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label">Price (₱)</label>
                    <input type="number" name="price" id="price" step="0.01" min="0" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="category" class="form-label">Category</label>
                    <select name="category" id="category" class="form-select" required>
                        <option value="">Select Category</option>
                        <option value="Organization Shirts">Organization Shirts</option>
                        <option value="Esport Shirts">Esport Shirts</option>
                        <option value="Hoodies">Hoodies</option>
                        <option value="Lanyards">Lanyards</option>
                        <option value="Keychains">Keychains</option>
                        <option value="Stickers">Stickers</option>
                        <option value="Pins">Pins</option>
                        <option value="Mugs">Mugs</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="stock" class="form-label">Stock Quantity</label>
                    <input type="number" name="stock" id="stock" value="1" min="1" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="image_url" class="form-label">Upload Image</label>
                    <input type="file" name="image_url" id="image_url" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description (optional)</label>
                    <textarea name="description" id="description" rows="4" class="form-control"></textarea>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary">Back</a>
                    <button type="submit" name="submit" class="btn btn-success">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
