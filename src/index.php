<?php
session_start();
require_once 'config.php';

$admin_password = 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_password'])) {
    if ($_POST['admin_password'] === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: index.php");
        exit;
    } else {
        $error = "Incorrect password.";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

$sql = "SELECT * FROM products ORDER BY created_at DESC";
$result = mysqli_query($con, $sql);
if (!$result) die("SQL Error: " . mysqli_error($con));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>OrgMerch</title>
<link rel="icon" href="assets/favicon.png" type="image/png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
html, body {
  height: 100%;
  margin: 0;
}

body { 
  background-color: #f8f9fa; 
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.masonry {
  flex-grow: 1;
  column-count: 1;
  column-gap: 1rem;
  min-height: 250px;
}

.masonry-item { break-inside: avoid; margin-bottom: 1rem; }

@media (min-width: 576px) { .masonry { column-count: 2; } }
@media (min-width: 992px) { .masonry { column-count: 3; } }
@media (min-width: 1200px) { .masonry { column-count: 4; } }

.product-card { transition: transform .2s; cursor: pointer; }
.product-card:hover { transform: scale(1.02); }

.admin-panel {
  background: #fff; 
  border: 1px solid #ddd; 
  padding: 10px 15px; 
  border-radius: 8px; 
  margin-bottom: 25px; 
  display: flex; 
  justify-content: center; 
  gap: 10px; 
  flex-wrap: wrap; 
}

.filter-panel { 
  margin-bottom: 25px; 
  display: flex; 
  gap: 10px; 
  flex-wrap: wrap; 
  justify-content: center; 
}

.no-results {
  text-align: center;
  color: #777;
  padding: 50px 0;
  font-size: 1.1rem;
  flex-grow: 1;
}

.cart-panel {
  position: fixed;
  top: 0;
  right: 0;
  width: 350px;
  height: 100vh;
  background: #fff;
  border-left: 2px solid #ddd;
  box-shadow: -3px 0 8px rgba(0,0,0,0.2);
  transform: translateX(100%);
  transition: transform 0.4s ease;
  z-index: 1050;
  display: flex;
  flex-direction: column;
}

.cart-panel.open { transform: translateX(0); }

.cart-header {
  background: #343a40;
  color: #fff;
  padding: 15px;
}

.cart-body { flex: 1; overflow-y: auto; padding: 15px; }

.cart-footer {
  border-top: 1px solid #ddd;
  padding: 15px;
  background: #f8f9fa;
}

.cart-item {
  border-bottom: 1px solid #ddd;
  padding: 8px 0;
}
.cart-item:last-child { border-bottom: none; }

.expand-btn {
  position: fixed;
  right: 10px;
  bottom: 15px;
  background: #0d6efd;
  color: #fff;
  border: none;
  border-radius: 50%;
  width: 60px;
  height: 60px;
  font-size: 30px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
  z-index: 1100;
}
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark p-3">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold text-uppercase" href="#">OrgMerch</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
      aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarContent">
      <div class="ms-auto d-flex align-items-center mt-3 mt-lg-0">
        <?php if (!isset($_SESSION['admin_logged_in'])): ?>
        <form method="POST" class="d-flex">
          <input type="password" name="admin_password" class="form-control form-control-sm me-2"
            placeholder="Admin Password" required>
          <button type="submit" class="btn btn-primary btn-sm">Login</button>
        </form>
        <?php else: ?>
        <a href="?logout=true" class="btn btn-outline-light btn-sm">Logout</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<div class="main-content container py-4">
  <h2 class="text-center mb-4 fw-bold">Product Dashboard</h2>

  <?php if (isset($error)): ?>
    <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="filter-panel">
    <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search products..." style="max-width:250px;">
    <select id="categoryFilter" class="form-select form-select-sm" style="max-width:200px;">
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

  <?php if (isset($_SESSION['admin_logged_in'])): ?>
  <div class="admin-panel bg-light p-3 rounded shadow-sm d-flex gap-2 align-items-center mb-3 border">
    <a href="add_product.php" class="btn btn-success btn-sm px-3 fw-semibold">➕ Add Product</a>
    <a href="view_orders.php" class="btn btn-primary btn-sm px-3 fw-semibold">🧾 View Orders</a>
  </div>
  <?php endif; ?>

  <div class="masonry" id="productContainer">
  <?php while($row = mysqli_fetch_assoc($result)) { ?>
    <div class="masonry-item" data-name="<?= htmlspecialchars(strtolower($row['product_name'])) ?>" data-category="<?= htmlspecialchars($row['category']) ?>">
      <div class="card product-card shadow-sm">
        <img src="uploads/<?= htmlspecialchars($row['image_url']) ?>" class="card-img-top" style="width:100%;object-fit:cover;">
        <div class="card-body d-flex flex-column">
          <h5 class="card-title"><?= htmlspecialchars($row['product_name']) ?></h5>
          <p class="text-muted small mb-1"><?= htmlspecialchars($row['category']) ?></p>
          <p class="mb-2"><?= htmlspecialchars($row['description']) ?></p>
          <div class="mt-auto">
            <h6 class="fw-bold mb-2">₱<?= number_format($row['price'], 2) ?></h6>
            <p class="small text-muted">Stock: <?= $row['stock'] ?></p>
            <?php if (isset($_SESSION['admin_logged_in'])): ?>
            <div class="d-flex justify-content-between mt-2">
              <a href="edit_product.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
              <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?')">🗑️ Delete</a>
            </div>
            <?php else: ?>
           <div class="d-flex justify-content-between w-100">
            <button class="btn btn-outline-danger btn-sm w-50 me-1" onclick="updateCart(<?= $row['id'] ?>, 'minus')">−</button>
            <button class="btn btn-outline-success btn-sm w-50 ms-1" onclick="updateCart(<?= $row['id'] ?>, 'plus')">+</button>
          </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  <?php } ?>
  </div>
  <div id="noResults" class="no-results" style="display:none;">
    
  </div>
</div>

<footer class="footer bg-black text-white pt-5 pb-3 mt-auto">
  <div class="container">
    <div class="row text-start text-md-start align-items-start">
      
      <div class="col-md-6 mb-4">
        <h2 class="h5 fw-bold text-uppercase">OrgMerch</h2>
        <p class="text-light small mb-0">
          Providing quality organization merchandise crafted with care and professionalism. 
          Your satisfaction is our top priority.
        </p>
      </div>

      <div class="col-md-6 mb-4">
        <h3 class="h6 fw-semibold text-uppercase text-warning">Contact Us</h3>
        <ul class="list-unstyled small mb-0">
          <li>Email: <a href="mailto:admin@orgmerch.com" class="text-white text-decoration-none">admin@orgmerch.com</a></li>
          <li>Phone: <a href="tel:+639123456789" class="text-white text-decoration-none">+63 912 345 6789</a></li>
          <li>Location: Lontoc Road, Taguig, Metro Manila</li>
        </ul>
      </div>

    </div>

    <div class="border-top border-secondary pt-3 mt-3 text-center small text-light">
      &copy; <?php echo date("Y"); ?> OrgMerch. All rights reserved.
    </div>
  </div>
</footer>

<button class="expand-btn" onclick="toggleCart()">🛒</button>

<div id="cartPanel" class="cart-panel">
  <div class="cart-header d-flex justify-content-between align-items-center">
    <h5 class="m-0">Checkout List</h5>
    <button class="btn btn-sm btn-light" onclick="toggleCart()">✕</button>
  </div>

  <div id="cartBody" class="cart-body"></div>

  <div class="cart-footer">
    <div class="d-flex justify-content-between mb-2">
      <strong>Total:</strong>
      <span id="cartTotal">₱0.00</span>
    </div>

    <form id="cartForm" method="POST" action="checkout.php">
      <input type="hidden" name="order_data" id="orderData">
      
      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-success w-50" onclick="prepareCart()">Confirm</button>
        <button type="button" class="btn btn-secondary w-50" onclick="clearCart()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
let cart = {};

function toggleCart() {
  const cartPanel = document.getElementById('cartPanel');
  const cartButton = document.querySelector('.expand-btn');

  const isOpen = cartPanel.classList.toggle('open');

  if (isOpen) {
    cartButton.style.opacity = '0';
    setTimeout(() => (cartButton.style.display = 'none'), 300);
  } else {
    cartButton.style.display = 'block';
    setTimeout(() => (cartButton.style.opacity = '1'), 50);
  }
}


function updateCart(id, action){
  const card = document.querySelector(`[onclick="updateCart(${id}, 'plus')"]`).closest('.card');
  const name = card.querySelector('.card-title').innerText.trim();
  const price = parseFloat(card.querySelector('.fw-bold').innerText.replace('₱','').replace(',',''));

  if(!cart[id]) cart[id] = { name, qty: 0, price };

  if(action==='plus') cart[id].qty++;
  if(action==='minus' && cart[id].qty>0) cart[id].qty--;

  if(cart[id].qty===0) delete cart[id];
  renderCart();
}


function renderCart(){
  const body = document.getElementById('cartBody');
  body.innerHTML = '';
  let total = 0;
  for(let id in cart){
    const item = cart[id];
    const subtotal = item.qty * item.price;
    total += subtotal;
    body.innerHTML += `<div class="cart-item d-flex justify-content-between align-items-center">
      <div><strong>${item.name}</strong><br>Qty: ${item.qty} × ₱${item.price.toFixed(2)}</div>
      <div><strong>₱${subtotal.toFixed(2)}</strong></div></div>`;
  }
  document.getElementById('cartTotal').innerText = '₱' + total.toFixed(2);
}

function clearCart(){
  if(confirm("Cancel all items?")){
    cart = {};
    renderCart();
  }
}

function prepareCart(){
  if(Object.keys(cart).length === 0){
    alert("Your cart is empty.");
    event.preventDefault();
    return false;
  }
  document.getElementById('orderData').value = JSON.stringify(cart);
}

const searchInput=document.getElementById('searchInput');
const categoryFilter=document.getElementById('categoryFilter');
const productContainer=document.getElementById('productContainer');

function filterProducts() {
  const search = searchInput.value.toLowerCase();
  const category = categoryFilter.value;
  let visibleCount = 0;

  Array.from(productContainer.children).forEach(card => {
    const name = card.dataset.name;
    const cat = card.dataset.category;
    const match = name.includes(search) && (category === '' || cat === category);
    card.style.display = match ? '' : 'none';
    if (match) visibleCount++;
  });

  document.getElementById('noResults').style.display = visibleCount === 0 ? 'block' : 'none';
}

if(searchInput) searchInput.addEventListener('input',filterProducts);
if(categoryFilter) categoryFilter.addEventListener('change',filterProducts);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
