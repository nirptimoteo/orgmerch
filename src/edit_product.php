<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    die("Invalid product ID");
}

$product = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM products WHERE id = $id"));
if (!$product) {
    die("Product not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = mysqli_real_escape_string($con, $_POST['product_name']);
    $price = floatval($_POST['price']);
    $category = mysqli_real_escape_string($con, $_POST['category']);
    $stock = intval($_POST['stock']);
    $description = mysqli_real_escape_string($con, $_POST['description']);

    $image_url = $product['image_url'];
    if (!empty($_FILES['image_url']['name'])) {
        $filename = basename($_FILES['image_url']['name']);
        $tmp_path = $_FILES['image_url']['tmp_name'];
        $target = "uploads/" . $filename;

        if (move_uploaded_file($tmp_path, $target)) {
            $image_url = $filename;
        }
    }

  $stmt = $con->prepare("UPDATE products SET product_name=?, price=?, category=?, stock=?, description=?, image_url=? WHERE id=?");
  $stmt->bind_param("sdssssi", $product_name, $price, $category, $stock, $description, $image_url, $id);
  $stmt->execute();


    header("Location: index.php");
    exit();
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
    <div class="card shadow mx-auto" style="max-width: 720px;">
      <div class="card-header bg-primary text-white text-center p-3">
        <h4 class="mb-0">Edit Product</h4>
      </div>

      <div class="card-body">
        <form method="POST" enctype="multipart/form-data">

          <div class="mb-3">
            <label class="form-label">Product Name</label>
            <input type="text" name="product_name" class="form-control" value="<?= htmlspecialchars($product['product_name']) ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Price (₱)</label>
            <input type="number" step="0.01" name="price" class="form-control" value="<?= $product['price'] ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category" class="form-select" required>
              <option value="" <?= $product['category']==='' ? 'selected' : '' ?>>Select Category</option>
              <option value="Organization Shirts" <?= $product['category']==='Organization Shirts' ? 'selected' : '' ?>>Organization Shirts</option>
              <option value="Esport Shirts" <?= $product['category']==='Esport Shirts' ? 'selected' : '' ?>>Esport Shirts</option>
              <option value="Hoodies" <?= $product['category']==='Hoodies' ? 'selected' : '' ?>>Hoodies</option>
              <option value="Lanyards" <?= $product['category']==='Lanyards' ? 'selected' : '' ?>>Lanyards</option>
              <option value="Keychains" <?= $product['category']==='Keychains' ? 'selected' : '' ?>>Keychains</option>
              <option value="Stickers" <?= $product['category']==='Stickers' ? 'selected' : '' ?>>Stickers</option>
              <option value="Pins" <?= $product['category']==='Pins' ? 'selected' : '' ?>>Pins</option>
              <option value="Mugs" <?= $product['category']==='Mugs' ? 'selected' : '' ?>>Mugs</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Stock Quantity</label>
            <input type="number" name="stock" class="form-control" value="<?= $product['stock'] ?>" min="1" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($product['description']) ?></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Product Image</label>
            <div class="row align-items-center">
              <div class="col-6">
                <input type="file" name="image_url" class="form-control">
              </div>
              <div class="col-6 text-center">
                <?php if ($product['image_url']): ?>
                  <img src="uploads/<?= htmlspecialchars($product['image_url']) ?>" 
                       alt="Product Image" 
                       class="img-thumbnail" 
                       style="max-height:150px;">
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-between">
            <a href="index.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-success">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
