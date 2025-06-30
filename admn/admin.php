<?php
session_start();
include_once __DIR__ . '/../database/conection_db.php';
include_once __DIR__ . '/../Mailer.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

// Handle status update for doctors
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['medic_id'], $_POST['action'])) {
    $medic_id = intval($_POST['medic_id']);
    $new_status = ($_POST['action'] === 'accept') ? 'Approved' : 'Rejected';

    $stmt = $conn->prepare("SELECT Email, Username FROM doctors WHERE MedicID = ?");
    $stmt->bind_param("i", $medic_id);
    $stmt->execute();
    $stmt->bind_result($email, $username);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare("UPDATE doctors SET Status = ? WHERE MedicID = ?");
    $stmt->bind_param("si", $new_status, $medic_id);
    $stmt->execute();
    $stmt->close();

    if ($new_status === 'Approved' && !empty($email)) {
        $subject = "Your Doctor Account Has Been Approved";
        $body = "Hello $username,<br><br>Your doctor account has been approved by the admin. You can now log in and use the system.<br><br>Thank you!";
        sendMail($email, $subject, $body);
    }

    header("Location: admin.php");
    exit;
}

// Handle product CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new product
    if (isset($_POST['add_product'])) {
        $name = $_POST['name'];
        $category_id = intval($_POST['category_id']);
        $price = floatval($_POST['price']);
        $quantity = intval($_POST['quantity']);
        $grams = intval($_POST['grams']);
        $expiry = $_POST['expiry'];
        $checkout_id = $_POST['checkout_id'];
        
        // Handle image upload
        $image_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../Images/';
            $file_name = basename($_FILES['image']['name']);
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
                $image_path = 'Images/' . $file_name;
            }
        }
        
        $stmt = $conn->prepare("INSERT INTO medicines (name, category_id, price, quantity, grams, expiry, status, image_path, lemonsqueezy_checkout_id) VALUES (?, ?, ?, ?, ?, ?, 'Available', ?, ?)");
        $stmt->bind_param("sidiisss", $name, $category_id, $price, $quantity, $grams, $expiry, $image_path, $checkout_id);
        $stmt->execute();
        $stmt->close();
    }
    
    // Update product
    if (isset($_POST['update_product'])) {
        $id = intval($_POST['id']);
        $name = $_POST['name'];
        $category_id = intval($_POST['category_id']);
        $price = floatval($_POST['price']);
        $quantity = intval($_POST['quantity']);
        $grams = intval($_POST['grams']);
        $expiry = $_POST['expiry'];
        $checkout_id = $_POST['checkout_id'];
        
        // Handle image update
        $image_path = $_POST['current_image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../Images/';
            $file_name = basename($_FILES['image']['name']);
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
                $image_path = 'Images/' . $file_name;
                // Delete old image if exists
                if (!empty($_POST['current_image'])) {
                    @unlink(__DIR__ . '/../' . $_POST['current_image']);
                }
            }
        }
        
        $stmt = $conn->prepare("UPDATE medicines SET name=?, category_id=?, price=?, quantity=?, grams=?, expiry=?, image_path=?, lemonsqueezy_checkout_id=? WHERE id=?");
        $stmt->bind_param("sidiisssi", $name, $category_id, $price, $quantity, $grams, $expiry, $image_path, $checkout_id, $id);
        $stmt->execute();
        $stmt->close();
    }
    
    // Delete product
    if (isset($_POST['delete_product'])) {
        $id = intval($_POST['id']);
        
        // Get image path to delete
        $stmt = $conn->prepare("SELECT image_path FROM medicines WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($image_path);
        $stmt->fetch();
        $stmt->close();
        
        // Delete image if exists
        if (!empty($image_path)) {
            @unlink(__DIR__ . '/../' . $image_path);
        }
        
        // Delete product
        $stmt = $conn->prepare("DELETE FROM medicines WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch all pending doctors
$pending_doctors = $conn->query("SELECT MedicID, Username, Email, Specialization, ContactNumber, Status FROM doctors WHERE Status = 'Pending'");

// Fetch all products
$products = $conn->query("SELECT m.*, c.name as category_name FROM medicines m LEFT JOIN categories c ON m.category_id = c.id");

// Fetch all categories
$categories = $conn->query("SELECT * FROM categories");

// Get statistics
$doctor_count = $conn->query("SELECT COUNT(*) FROM doctors WHERE Status = 'Approved'")->fetch_row()[0];
$patient_count = $conn->query("SELECT COUNT(*) FROM patient")->fetch_row()[0];
$order_stats = $conn->query("SELECT 
    COUNT(*) as total_orders, 
    SUM(total_price) as total_revenue,
    DATE_FORMAT(order_date, '%Y-%m') as month 
    FROM orders 
    GROUP BY DATE_FORMAT(order_date, '%Y-%m') 
    ORDER BY month DESC LIMIT 12");
$order_data = [];
while ($row = $order_stats->fetch_assoc()) {
    $order_data[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - eMedConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background: #f8fafc; }
        .sidebar { 
            background: #43c97e; 
            color: white;
            min-height: 100vh;
            position: fixed;
            width: 250px;
        }
        .sidebar .nav-link { color: white; }
        .sidebar .nav-link:hover { background: rgba(255,255,255,0.1); }
        .main-content { margin-left: 250px; padding: 20px; }
        .card { border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .card-header { border-radius: 10px 10px 0 0 !important; }
        .table thead { background: #43c97e; color: #fff; }
        .btn-accept { background: #43c97e; color: #fff; }
        .btn-reject { background: #e74c3c; color: #fff; }
        .stat-card { transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar p-3">
        <h4 class="text-center mb-4">Admin Panel</h4>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="#dashboard" data-bs-toggle="tab">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#doctor-approval" data-bs-toggle="tab">
                    <i class="bi bi-person-check me-2"></i>Doctor Approvals
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#product-management" data-bs-toggle="tab">
                    <i class="bi bi-capsule me-2"></i>Product Management
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#statistics" data-bs-toggle="tab">
                    <i class="bi bi-graph-up me-2"></i>Statistics
                </a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link text-danger" href="admin_logout.php">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content flex-grow-1">
        <div class="tab-content">
            <!-- Dashboard Tab -->
            <div class="tab-pane active" id="dashboard">
                <h2 class="mb-4">Admin Dashboard</h2>
                
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Approved Doctors</h5>
                                        <h2 class="mb-0"><?= $doctor_count ?></h2>
                                    </div>
                                    <i class="bi bi-person-check fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Registered Patients</h5>
                                        <h2 class="mb-0"><?= $patient_count ?></h2>
                                    </div>
                                    <i class="bi bi-people fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Total Products</h5>
                                        <h2 class="mb-0"><?= $products->num_rows ?></h2>
                                    </div>
                                    <i class="bi bi-capsule fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <p>No recent activity to display.</p>
                    </div>
                </div>
            </div>
            
            <!-- Doctor Approval Tab -->
            <div class="tab-pane" id="doctor-approval">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Pending Doctor Registrations</h2>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <?php if ($pending_doctors && $pending_doctors->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>MedicID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Specialization</th>
                                            <th>Contact</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $pending_doctors->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['MedicID']) ?></td>
                                                <td><?= htmlspecialchars($row['Username']) ?></td>
                                                <td><?= htmlspecialchars($row['Email']) ?></td>
                                                <td><?= htmlspecialchars($row['Specialization']) ?></td>
                                                <td><?= htmlspecialchars($row['ContactNumber']) ?></td>
                                                <td><?= htmlspecialchars($row['Status']) ?></td>
                                                <td>
                                                    <form method="post" style="display:inline;">
                                                        <input type="hidden" name="medic_id" value="<?= $row['MedicID'] ?>">
                                                        <button type="submit" name="action" value="accept" class="btn btn-accept btn-sm">Accept</button>
                                                    </form>
                                                    <form method="post" style="display:inline;">
                                                        <input type="hidden" name="medic_id" value="<?= $row['MedicID'] ?>">
                                                        <button type="submit" name="action" value="reject" class="btn btn-reject btn-sm">Reject</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">No pending doctors to approve.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Product Management Tab -->
            <div class="tab-pane" id="product-management">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Product Management</h2>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="bi bi-plus-lg me-1"></i>Add Product
                    </button>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Grams</th>
                                        <th>Expiry</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($products && $products->num_rows > 0): ?>
                                        <?php while ($product = $products->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= $product['id'] ?></td>
                                                <td>
                                                    <?php if (!empty($product['image_path'])): ?>
                                                        <img src="<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="height: 50px; width: auto;">
                                                    <?php else: ?>
                                                        <span class="text-muted">No image</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($product['name']) ?></td>
                                                <td><?= htmlspecialchars($product['category_name'] ?? 'N/A') ?></td>
                                                <td>₱<?= number_format($product['price'], 2) ?></td>
                                                <td><?= $product['quantity'] ?></td>
                                                <td><?= $product['grams'] ?? '-' ?></td>
                                                <td><?= $product['expiry'] ?? '-' ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $product['status'] === 'Available' ? 'success' : 'danger' ?>">
                                                        <?= $product['status'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editProductModal"
                                                            data-id="<?= $product['id'] ?>"
                                                            data-name="<?= htmlspecialchars($product['name']) ?>"
                                                            data-category="<?= $product['category_id'] ?>"
                                                            data-price="<?= $product['price'] ?>"
                                                            data-quantity="<?= $product['quantity'] ?>"
                                                            data-grams="<?= $product['grams'] ?>"
                                                            data-expiry="<?= $product['expiry'] ?>"
                                                            data-image="<?= htmlspecialchars($product['image_path']) ?>"
                                                            data-checkout="<?= htmlspecialchars($product['lemonsqueezy_checkout_id']) ?>">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                        <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                                        <button type="submit" name="delete_product" class="btn btn-sm btn-danger">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="10" class="text-center text-muted">No products found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Statistics Tab -->
            <div class="tab-pane" id="statistics">
                <h2 class="mb-4">System Statistics</h2>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5>User Registrations</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="userStatsChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5>Monthly Sales</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="salesChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5>Recent Orders</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($order_data)): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Month</th>
                                            <th>Total Orders</th>
                                            <th>Total Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($order_data as $order): ?>
                                            <tr>
                                                <td><?= $order['month'] ?></td>
                                                <td><?= $order['total_orders'] ?></td>
                                                <td>₱<?= number_format($order['total_revenue'], 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No order data available.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php while ($category = $categories->fetch_assoc()): ?>
                                    <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="price" class="form-label">Price (₱)</label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                        </div>
                        <div class="col-md-4">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" required>
                        </div>
                        <div class="col-md-4">
                            <label for="grams" class="form-label">Grams</label>
                            <input type="number" class="form-control" id="grams" name="grams">
                        </div>
                        <div class="col-md-6">
                            <label for="expiry" class="form-label">Expiry Date</label>
                            <input type="date" class="form-control" id="expiry" name="expiry">
                        </div>
                        <div class="col-md-6">
                            <label for="checkout_id" class="form-label">Lemon Squeezy Checkout ID</label>
                            <input type="text" class="form-control" id="checkout_id" name="checkout_id">
                        </div>
                        <div class="col-12">
                            <label for="image" class="form-label">Product Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_product" class="btn btn-success">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edit_id">
                <input type="hidden" name="current_image" id="current_image">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_category_id" class="form-label">Category</label>
                            <select class="form-select" id="edit_category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php 
                                $categories->data_seek(0); // Reset pointer to beginning
                                while ($category = $categories->fetch_assoc()): ?>
                                    <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="edit_price" class="form-label">Price (₱)</label>
                            <input type="number" step="0.01" class="form-control" id="edit_price" name="price" required>
                        </div>
                        <div class="col-md-4">
                            <label for="edit_quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="edit_quantity" name="quantity" required>
                        </div>
                        <div class="col-md-4">
                            <label for="edit_grams" class="form-label">Grams</label>
                            <input type="number" class="form-control" id="edit_grams" name="grams">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_expiry" class="form-label">Expiry Date</label>
                            <input type="date" class="form-control" id="edit_expiry" name="expiry">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_checkout_id" class="form-label">Lemon Squeezy Checkout ID</label>
                            <input type="text" class="form-control" id="edit_checkout_id" name="checkout_id">
                        </div>
                        <div class="col-12">
                            <label for="edit_image" class="form-label">Product Image</label>
                            <input type="file" class="form-control" id="edit_image" name="image" accept="image/*">
                            <div class="mt-2" id="current_image_container"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_product" class="btn btn-primary">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Initialize edit modal with product data
document.getElementById('editProductModal').addEventListener('show.bs.modal', function(event) {
    const button = event.relatedTarget;
    const modal = this;
    
    modal.querySelector('#edit_id').value = button.getAttribute('data-id');
    modal.querySelector('#edit_name').value = button.getAttribute('data-name');
    modal.querySelector('#edit_category_id').value = button.getAttribute('data-category');
    modal.querySelector('#edit_price').value = button.getAttribute('data-price');
    modal.querySelector('#edit_quantity').value = button.getAttribute('data-quantity');
    modal.querySelector('#edit_grams').value = button.getAttribute('data-grams');
    modal.querySelector('#edit_expiry').value = button.getAttribute('data-expiry');
    modal.querySelector('#edit_checkout_id').value = button.getAttribute('data-checkout');
    
    const imagePath = button.getAttribute('data-image');
    modal.querySelector('#current_image').value = imagePath;
    
    const imageContainer = modal.querySelector('#current_image_container');
    if (imagePath) {
        imageContainer.innerHTML = `<p class="mb-1">Current Image:</p>
                                   <img src="${imagePath}" alt="Current Product Image" style="max-height: 100px;">`;
    } else {
        imageContainer.innerHTML = '<p class="text-muted">No current image</p>';
    }
});

// Charts
document.addEventListener('DOMContentLoaded', function() {
    // User Stats Chart
    const userCtx = document.getElementById('userStatsChart').getContext('2d');
    const userChart = new Chart(userCtx, {
        type: 'doughnut',
        data: {
            labels: ['Doctors', 'Patients'],
            datasets: [{
                data: [<?= $doctor_count ?>, <?= $patient_count ?>],
                backgroundColor: ['#43c97e', '#3498db'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'User Distribution'
                }
            }
        }
    });
    
    // Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(salesCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($order_data, 'month')) ?>,
            datasets: [{
                label: 'Total Revenue (₱)',
                data: <?= json_encode(array_column($order_data, 'total_revenue')) ?>,
                backgroundColor: '#43c97e',
                borderColor: '#43c97e',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Monthly Revenue'
                }
            }
        }
    });
});
</script>
</body>
</html>